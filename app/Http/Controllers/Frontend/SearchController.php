<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Get the university_id of the authenticated user.
     * Returns null for guests (public routes).
     */
    private function currentUniversityId(): ?int
    {
        return Auth::check() ? Auth::user()->university_id : null;
    }

    /**
     * Apply campus isolation to any Item query.
     * If the user is authenticated, restrict to their university.
     * Public/guest routes call this too — if no university, no restriction.
     */
    private function scopeToUniversity($query): mixed
    {
        $universityId = $this->currentUniversityId();

        if ($universityId) {
            $query->forUniversity($universityId);
        }

        return $query;
    }

    /**
     * Display search page with filters
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        $filters = [
            'mode'       => $request->get('mode'),
            'min_price'  => $request->get('min_price'),
            'max_price'  => $request->get('max_price'),
            'min_rating' => $request->get('min_rating'),
            'sort'       => $request->get('sort', 'recent'),
            'status'     => $request->get('status', 'available'),
        ];

        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available();

        // FIX: apply campus isolation
        $this->scopeToUniversity($itemsQuery);

        if ($query) {
            $itemsQuery->search($query);
        }

        $itemsQuery = $this->applyFilters($itemsQuery, $filters);
        $itemsQuery = $this->applySorting($itemsQuery, $filters['sort']);

        $items = $itemsQuery->paginate(12);

        $items->getCollection()->transform(function ($item) {
            return [
                'id'                => $item->id,
                'title'             => $item->title,
                'description'       => $item->description,
                'price'             => $item->price,
                'availability_mode' => $item->availability_mode,
                'status'            => $item->status,
                'owner'             => $item->owner,
                'image_path'        => $item->image_path,
                'avg_rating'        => round($item->averageRating(), 2),
                'total_ratings'     => $item->ratings->count(),
                'total_borrowed'    => $item->totalBorrowCount(),
                'url'               => route('frontend.items.show', $item),
            ];
        });

        return view('frontend.search.index', compact('items', 'query', 'filters'));
    }

    /**
     * Advanced search with multiple criteria
     */
    public function advanced(Request $request)
    {
        $filters = [
            'title'       => $request->get('title'),
            'description' => $request->get('description'),
            'mode'        => $request->get('mode'),
            'min_price'   => $request->get('min_price'),
            'max_price'   => $request->get('max_price'),
            'min_rating'  => $request->get('min_rating'),
            'owner_name'  => $request->get('owner_name'),
            'location'    => $request->get('location'),
            'sort'        => $request->get('sort', 'recent'),
            'status'      => $request->get('status', 'available'),
        ];

        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available();

        // FIX: campus isolation
        $this->scopeToUniversity($itemsQuery);

        if ($filters['title']) {
            $itemsQuery->where('title', 'like', "%{$filters['title']}%");
        }

        if ($filters['description']) {
            $itemsQuery->where('description', 'like', "%{$filters['description']}%");
        }

        if ($filters['mode']) {
            $itemsQuery->whereIn('availability_mode', [$filters['mode'], 'both']);
        }

        if ($filters['min_price']) {
            $itemsQuery->where('price', '>=', $filters['min_price']);
        }

        if ($filters['max_price']) {
            $itemsQuery->where('price', '<=', $filters['max_price']);
        }

        if ($filters['owner_name']) {
            $itemsQuery->whereHas('owner', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['owner_name']}%");
            });
        }

        if ($filters['location']) {
            $itemsQuery->where('pickup_location', 'like', "%{$filters['location']}%");
        }

        if ($filters['min_rating']) {
            $itemsQuery->withAvg('ratings', 'rating')
                ->having('ratings_avg_rating', '>=', $filters['min_rating']);
        }

        $itemsQuery = $this->applySorting($itemsQuery, $filters['sort']);
        $items      = $itemsQuery->paginate(12);

        return view('frontend.search.advanced', compact('items', 'filters'));
    }

    /**
     * Search users by name — scoped to same university
     */
    public function users(Request $request)
    {
        $query = $request->get('q');

        $usersQuery = User::query()
            ->with('ratingsReceived')
            ->withCount('items')
            ->where('role', 'user');

        // FIX: restrict user search to same university
        $universityId = $this->currentUniversityId();
        if ($universityId) {
            $usersQuery->where('university_id', $universityId);
        }

        if ($query) {
            $usersQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });
        }

        $users = $usersQuery->paginate(15);

        $users->getCollection()->transform(function ($user) {
            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'avg_rating'  => round($user->averageRating(), 2),
                'items_count' => $user->items_count,
                'url'         => route('frontend.profile.show', $user),
            ];
        });

        return view('frontend.search.users', compact('users', 'query'));
    }

    /**
     * Get search suggestions (autocomplete) — scoped to university
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $itemsQuery = Item::where('title', 'like', "%{$query}%")->available();
        $this->scopeToUniversity($itemsQuery);

        $items = $itemsQuery->distinct()->pluck('title')->take(5);

        // User suggestions scoped to same university
        $usersQuery = User::where('name', 'like', "%{$query}%")->where('role', 'user');
        $universityId = $this->currentUniversityId();
        if ($universityId) {
            $usersQuery->where('university_id', $universityId);
        }
        $users = $usersQuery->distinct()->pluck('name')->take(3);

        return response()->json([
            'items' => $items->map(fn($title) => [
                'type' => 'item',
                'text' => $title,
                'url'  => route('frontend.search.index', ['q' => $title]),
            ]),
            'users' => $users->map(fn($name) => [
                'type' => 'user',
                'text' => $name,
                'url'  => route('frontend.search.users', ['q' => $name]),
            ]),
        ]);
    }

    /**
     * Search by category — scoped to university
     */
    public function byCategory(Request $request)
    {
        $category   = $request->get('category');
        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available()
            ->where('title', 'like', "%{$category}%");

        $this->scopeToUniversity($itemsQuery);

        $items = $itemsQuery->paginate(12);

        return view('frontend.search.category', compact('items', 'category'));
    }

    /**
     * Filter by owner's rating — scoped to university
     */
    public function byOwnerRating(Request $request)
    {
        $minRating  = $request->get('min_rating', 3);
        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available()
            ->whereHas('owner', function ($q) use ($minRating) {
                $q->withAvg('ratingsReceived', 'rating')
                  ->having('ratingsReceived_avg_rating', '>=', $minRating);
            });

        $this->scopeToUniversity($itemsQuery);

        $items = $itemsQuery->paginate(12);

        return view('frontend.search.by-owner-rating', compact('items', 'minRating'));
    }

    /**
     * Popular items — scoped to university
     */
    public function popular(Request $request)
    {
        $timeframe  = $request->get('timeframe', '30');
        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available()
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('created_at', '>=', now()->subDays($timeframe))
            ->orderByDesc('ratings_count');

        $this->scopeToUniversity($itemsQuery);

        $items = $itemsQuery->paginate(12);

        return view('frontend.search.popular', compact('items', 'timeframe'));
    }

    /**
     * New items — scoped to university
     */
    public function new(Request $request)
    {
        $days       = $request->get('days', '7');
        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc');

        $this->scopeToUniversity($itemsQuery);

        $items = $itemsQuery->paginate(12);

        return view('frontend.search.new', compact('items', 'days'));
    }

    /**
     * Items by specific owner/user
     */
    public function byOwner(User $user, Request $request)
    {
        $mode  = $request->get('mode');
        $items = $user->items()->with('ratings')->available();

        if ($mode) {
            $items->whereIn('availability_mode', [$mode, 'both']);
        }

        $items = $items->paginate(12);

        return view('frontend.search.by-owner', compact('user', 'items'));
    }

    /**
     * Recommended items — scoped to university
     */
    public function recommended(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('frontend.search.popular');
        }

        $userTransactions = $user->transactionsAsBorrower()
            ->where('status', 'completed')
            ->with('item:id,user_id')
            ->get();

        $similarOwnerIds = $userTransactions->pluck('item.user_id')->unique();

        $itemsQuery = Item::query()
            ->with(['owner:id,name', 'ratings'])
            ->available()
            ->whereIn('user_id', $similarOwnerIds)
            ->whereNotIn('id', $userTransactions->pluck('item_id'));

        // FIX: campus isolation
        $this->scopeToUniversity($itemsQuery);

        $items = $itemsQuery->paginate(12);

        return view('frontend.search.recommended', compact('items'));
    }

    /**
     * Saved/bookmarked items
     */
    public function saved(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // TODO: Implement saved items (requires Bookmark/Wishlist model)
        $items = collect();

        return view('frontend.search.saved', compact('items'));
    }

    /**
     * Get search filters (JSON API) — prices scoped to university
     */
    public function getFilters(Request $request)
    {
        $baseQuery    = Item::available();
        $universityId = $this->currentUniversityId();
        if ($universityId) {
            $baseQuery->forUniversity($universityId);
        }

        $minPrice = (clone $baseQuery)->min('price') ?? 0;
        $maxPrice = (clone $baseQuery)->max('price') ?? 10000;

        return response()->json([
            'modes' => [
                ['value' => 'lend', 'label' => 'Lending Only'],
                ['value' => 'sell', 'label' => 'Selling Only'],
                ['value' => 'both', 'label' => 'Lending & Selling'],
            ],
            'ratings' => [
                ['value' => 3,   'label' => '3+ Stars'],
                ['value' => 3.5, 'label' => '3.5+ Stars'],
                ['value' => 4,   'label' => '4+ Stars'],
                ['value' => 4.5, 'label' => '4.5+ Stars'],
            ],
            'price_range' => [
                'min' => floor($minPrice),
                'max' => ceil($maxPrice),
            ],
            'sort_options' => [
                ['value' => 'recent',       'label' => 'Most Recent'],
                ['value' => 'popular',      'label' => 'Most Popular'],
                ['value' => 'highest_rated', 'label' => 'Highest Rated'],
                ['value' => 'price_low',    'label' => 'Price: Low to High'],
                ['value' => 'price_high',   'label' => 'Price: High to Low'],
            ],
        ]);
    }

    /**
     * Get search statistics — scoped to university
     */
    public function statistics(Request $request)
    {
        $query        = $request->get('q');
        $universityId = $this->currentUniversityId();

        $itemQuery = Item::where('title', 'like', "%{$query}%")->available();
        if ($universityId) {
            $itemQuery->forUniversity($universityId);
        }

        $itemCount = $itemQuery->count();

        $userQuery = User::where('name', 'like', "%{$query}%")->where('role', 'user');
        if ($universityId) {
            $userQuery->where('university_id', $universityId);
        }
        $userCount = $userQuery->count();

        $avgRating = (clone $itemQuery)
            ->withAvg('ratings', 'rating')
            ->get()
            ->avg('ratings_avg_rating');

        return response()->json([
            'total_items' => $itemCount,
            'total_users' => $userCount,
            'avg_rating'  => round($avgRating ?? 0, 2),
            'query'       => $query,
        ]);
    }

    /**
     * Export search results as CSV — scoped to university
     */
    public function exportResults(Request $request)
    {
        $query        = $request->get('q');
        $universityId = $this->currentUniversityId();

        $itemsQuery = Item::where('title', 'like', "%{$query}%")
            ->available()
            ->with('owner:id,name');

        if ($universityId) {
            $itemsQuery->forUniversity($universityId);
        }

        $items    = $itemsQuery->get();
        $filename = "search_results_{$query}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Title', 'Owner', 'Mode', 'Price', 'Status', 'Location']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->title,
                    $item->owner->name,
                    $item->availability_mode,
                    $item->price ?? 'N/A',
                    $item->status,
                    $item->pickup_location,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================
    // Helper Methods
    // ========================================

    private function applyFilters($query, $filters)
    {
        if ($filters['mode']) {
            $query->whereIn('availability_mode', [$filters['mode'], 'both']);
        }

        if ($filters['min_price']) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if ($filters['max_price']) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if ($filters['min_rating']) {
            $query->withAvg('ratings', 'rating')
                ->having('ratings_avg_rating', '>=', $filters['min_rating']);
        }

        return $query;
    }

    private function applySorting($query, $sort = 'recent')
    {
        return match($sort) {
            'popular'        => $query->withCount('transactions')->orderByDesc('transactions_count'),
            'highest_rated'  => $query->withAvg('ratings', 'rating')->orderByDesc('ratings_avg_rating'),
            'price_low'      => $query->orderBy('price'),
            'price_high'     => $query->orderByDesc('price'),
            default          => $query->orderBy('created_at', 'desc'),
        };
    }
}