<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Display search page with filters
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        $filters = [
            'mode' => $request->get('mode'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'min_rating' => $request->get('min_rating'),
            'sort' => $request->get('sort', 'recent'),
            'status' => $request->get('status', 'available'),
        ];

        // Start with available items
        $itemsQuery = Item::query()->with([
            'owner:id,name,profile_image',
            'ratings'
        ])->available();

        // Search query
        if ($query) {
            $itemsQuery->search($query);
        }

        // Apply filters
        $itemsQuery = $this->applyFilters($itemsQuery, $filters);

        // Apply sorting
        $itemsQuery = $this->applySorting($itemsQuery, $filters['sort']);

        // Paginate results
        $items = $itemsQuery->paginate(12);

        // Add computed properties
        $items->getCollection()->transform(function($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'price' => $item->price,
                'availability_mode' => $item->availability_mode,
                'status' => $item->status,
                'owner' => $item->owner,
                'image_path' => $item->image_path,
                'avg_rating' => round($item->averageRating(), 2),
                'total_ratings' => $item->ratings->count(),
                'total_borrowed' => $item->totalBorrowCount(),
                'url' => route('frontend.items.show', $item),
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
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'mode' => $request->get('mode'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'min_rating' => $request->get('min_rating'),
            'owner_name' => $request->get('owner_name'),
            'location' => $request->get('location'),
            'sort' => $request->get('sort', 'recent'),
            'status' => $request->get('status', 'available'),
        ];

        $itemsQuery = Item::query()
            ->with(['owner:id,name,profile_image', 'ratings'])
            ->available();

        // Title search
        if ($filters['title']) {
            $itemsQuery->where('title', 'like', "%{$filters['title']}%");
        }

        // Description search
        if ($filters['description']) {
            $itemsQuery->where('description', 'like', "%{$filters['description']}%");
        }

        // Mode filter
        if ($filters['mode']) {
            $itemsQuery->whereIn('availability_mode', [$filters['mode'], 'both']);
        }

        // Price range
        if ($filters['min_price']) {
            $itemsQuery->where('price', '>=', $filters['min_price']);
        }
        if ($filters['max_price']) {
            $itemsQuery->where('price', '<=', $filters['max_price']);
        }

        // Owner name
        if ($filters['owner_name']) {
            $itemsQuery->whereHas('owner', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['owner_name']}%");
            });
        }

        // Location
        if ($filters['location']) {
            $itemsQuery->where('pickup_location', 'like', "%{$filters['location']}%");
        }

        // Rating filter
        if ($filters['min_rating']) {
            $itemsQuery->withAvg('ratings', 'rating')
                ->having('ratings_avg_rating', '>=', $filters['min_rating']);
        }

        // Apply sorting
        $itemsQuery = $this->applySorting($itemsQuery, $filters['sort']);

        $items = $itemsQuery->paginate(12);

        return view('frontend.search.advanced', compact('items', 'filters'));
    }

    /**
     * Search users by name
     */
    public function users(Request $request)
    {
        $query = $request->get('q');

        $usersQuery = User::query()
            ->with('ratingsReceived')
            ->withCount('items');

        if ($query) {
            $usersQuery->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        }

        $users = $usersQuery->paginate(15);

        // Transform results
        $users->getCollection()->transform(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
                'avg_rating' => round($user->averageRating(), 2),
                'items_count' => $user->items_count,
                'url' => route('frontend.profile.show', $user),
            ];
        });

        return view('frontend.search.users', compact('users', 'query'));
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $items = Item::where('title', 'like', "%{$query}%")
            ->available()
            ->distinct()
            ->pluck('title')
            ->take(5);

        $users = User::where('name', 'like', "%{$query}%")
            ->distinct()
            ->pluck('name')
            ->take(3);

        return response()->json([
            'items' => $items->map(fn($title) => [
                'type' => 'item',
                'text' => $title,
                'url' => route('frontend.search.index', ['q' => $title]),
            ]),
            'users' => $users->map(fn($name) => [
                'type' => 'user',
                'text' => $name,
                'url' => route('frontend.search.users', ['q' => $name]),
            ]),
        ]);
    }

    /**
     * Search by category/type (extensible for future)
     */
    public function byCategory(Request $request)
    {
        $category = $request->get('category');

        // This is a placeholder - extend Item model with category field
        $items = Item::query()
            ->with(['owner:id,name,profile_image', 'ratings'])
            ->available()
            ->where('title', 'like', "%{$category}%")
            ->paginate(12);

        return view('frontend.search.category', compact('items', 'category'));
    }

    /**
     * Filter by owner's rating
     */
    public function byOwnerRating(Request $request)
    {
        $minRating = $request->get('min_rating', 3);

        $items = Item::query()
            ->with(['owner:id,name,profile_image', 'ratings'])
            ->available()
            ->whereHas('owner', function($q) use ($minRating) {
                $q->withAvg('ratingsReceived', 'rating')
                  ->having('ratingsReceived_avg_rating', '>=', $minRating);
            })
            ->paginate(12);

        return view('frontend.search.by-owner-rating', compact('items', 'minRating'));
    }

    /**
     * Popular items (most borrowed/highly rated)
     */
    public function popular(Request $request)
    {
        $timeframe = $request->get('timeframe', '30'); // days

        $items = Item::query()
            ->with(['owner:id,name,profile_image', 'ratings'])
            ->available()
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('created_at', '>=', now()->subDays($timeframe))
            ->orderByDesc('ratings_count')
            ->paginate(12);

        return view('frontend.search.popular', compact('items', 'timeframe'));
    }

    /**
     * New items (recently listed)
     */
    public function new(Request $request)
    {
        $days = $request->get('days', '7');

        $items = Item::query()
            ->with(['owner:id,name,profile_image', 'ratings'])
            ->available()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('frontend.search.new', compact('items', 'days'));
    }

    /**
     * Items by specific owner/user
     */
    public function byOwner(User $user, Request $request)
    {
        $mode = $request->get('mode');

        $items = $user->items()
            ->with('ratings')
            ->available();

        if ($mode) {
            $items->whereIn('availability_mode', [$mode, 'both']);
        }

        $items = $items->paginate(12);

        return view('frontend.search.by-owner', compact('user', 'items'));
    }

    /**
     * Recommended items (based on user history)
     */
    public function recommended(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('frontend.search.popular');
        }

        // Get user's transaction history
        $userTransactions = $user->transactionsAsBorrower()
            ->where('status', 'completed')
            ->with('item:id,user_id')
            ->get();

        // Get items from similar owners
        $similarOwnerIds = $userTransactions->pluck('item.user_id')->unique();

        $items = Item::query()
            ->with(['owner:id,name,profile_image', 'ratings'])
            ->available()
            ->whereIn('user_id', $similarOwnerIds)
            ->whereNotIn('id', $userTransactions->pluck('item_id'))
            ->paginate(12);

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
     * Get search filters (JSON API)
     */
    public function getFilters(Request $request)
    {
        $minPrice = Item::min('price') ?? 0;
        $maxPrice = Item::max('price') ?? 10000;

        return response()->json([
            'modes' => [
                ['value' => 'lend', 'label' => 'Lending Only'],
                ['value' => 'sell', 'label' => 'Selling Only'],
                ['value' => 'both', 'label' => 'Lending & Selling'],
            ],
            'ratings' => [
                ['value' => 3, 'label' => '3+ Stars'],
                ['value' => 3.5, 'label' => '3.5+ Stars'],
                ['value' => 4, 'label' => '4+ Stars'],
                ['value' => 4.5, 'label' => '4.5+ Stars'],
            ],
            'price_range' => [
                'min' => floor($minPrice),
                'max' => ceil($maxPrice),
            ],
            'sort_options' => [
                ['value' => 'recent', 'label' => 'Most Recent'],
                ['value' => 'popular', 'label' => 'Most Popular'],
                ['value' => 'highest_rated', 'label' => 'Highest Rated'],
                ['value' => 'price_low', 'label' => 'Price: Low to High'],
                ['value' => 'price_high', 'label' => 'Price: High to Low'],
            ],
        ]);
    }

    /**
     * Get search statistics
     */
    public function statistics(Request $request)
    {
        $query = $request->get('q');

        $itemCount = Item::where('title', 'like', "%{$query}%")
            ->available()
            ->count();

        $userCount = User::where('name', 'like', "%{$query}%")
            ->count();

        $avgRating = Item::where('title', 'like', "%{$query}%")
            ->available()
            ->withAvg('ratings', 'rating')
            ->get()
            ->avg('ratings_avg_rating');

        return response()->json([
            'total_items' => $itemCount,
            'total_users' => $userCount,
            'avg_rating' => round($avgRating, 2),
            'query' => $query,
        ]);
    }

    /**
     * Export search results as CSV
     */
    public function exportResults(Request $request)
    {
        $query = $request->get('q');

        $items = Item::where('title', 'like', "%{$query}%")
            ->available()
            ->with('owner:id,name')
            ->get();

        $filename = "search_results_{$query}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items) {
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

    /**
     * Apply filters to query
     */
    private function applyFilters($query, $filters)
    {
        // Mode filter
        if ($filters['mode']) {
            $query->whereIn('availability_mode', [$filters['mode'], 'both']);
        }

        // Price range
        if ($filters['min_price']) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if ($filters['max_price']) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Rating filter
        if ($filters['min_rating']) {
            $query->withAvg('ratings', 'rating')
                ->having('ratings_avg_rating', '>=', $filters['min_rating']);
        }

        return $query;
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, $sort = 'recent')
    {
        return match($sort) {
            'popular' => $query->withCount('transactions')
                ->orderByDesc('transactions_count'),
            'highest_rated' => $query->withAvg('ratings', 'rating')
                ->orderByDesc('ratings_avg_rating'),
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'recent' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}
