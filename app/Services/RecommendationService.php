<?php

// app/Services/RecommendationService.php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * "Similar items" for an item detail page.
     *
     * Matches on: same university, same category, compatible availability_mode,
     * and a price band (±30%) around the item's price. Free/share items only
     * match other free/share items.
     *
     * If the strict match doesn't return enough results, backfills with
     * same-category items regardless of price/mode so the section is never
     * empty for a category that just doesn't have many listings.
     */
    public function similarItems(Item $item, int $limit = 6): Collection
    {
        $primary = $this->similarItemsQuery($item)->take($limit)->get();

        if ($primary->count() < $limit) {
            $excludeIds = $primary->pluck('id')->push($item->id);

            $fallback = Item::query()
                ->where('university_id', $item->university_id)
                ->where('category', $item->category)
                ->where('status', 'available')
                ->whereNotIn('id', $excludeIds)
                ->with('owner:id,name')
                ->withCount(['ratings', 'transactions'])
                ->withAvg('ratings', 'rating')
                ->latest()
                ->take($limit - $primary->count())
                ->get();

            $primary = $primary->concat($fallback);
        }

        return $primary->values();
    }

    private function similarItemsQuery(Item $item)
    {
        // 'both' listings are compatible with either a lend-only or sell-only search
        $modes = $item->availability_mode === 'both'
            ? ['lend', 'sell', 'both']
            : [$item->availability_mode, 'both'];

        $query = Item::query()
            ->where('university_id', $item->university_id)
            ->where('category', $item->category)
            ->where('status', 'available')
            ->where('id', '!=', $item->id)
            ->whereIn('availability_mode', $modes)
            ->with('owner:id,name')
            ->withCount(['ratings', 'transactions'])
            ->withAvg('ratings', 'rating');

        if ($item->price !== null) {
            $min = $item->price * 0.7;
            $max = $item->price * 1.3;

            $query->where(function ($q) use ($min, $max) {
                $q->whereBetween('price', [$min, $max])
                  ->orWhereNull('price');
            });

            // Closest price first
            $query->orderByRaw('ABS(COALESCE(price, 0) - ?) asc', [$item->price]);
        } else {
            // Free/share item — only match other free/share items
            $query->whereNull('price');
            $query->latest();
        }

        return $query;
    }

    /**
     * "Recommended for you" for the homepage.
     *
     * No view-tracking table yet, so the signal comes from categories the
     * user is already active in: items they've listed themselves, plus
     * items involved in transactions where they were the borrower.
     *
     * New users with no history yet fall back to the most-listed categories
     * at their university, so the section is never empty on day one.
     */
    public function forUser(User $user, int $limit = 8): Collection
    {
        $universityId = $user->university_id;

        $ownedCategories = $user->items()->pluck('category');

        $borrowedCategories = $user->transactionsAsBorrower()
            ->with('item:id,category')
            ->get()
            ->pluck('item.category')
            ->filter();

        $categoryWeights = $ownedCategories->merge($borrowedCategories)
            ->filter()
            ->countBy();

        if ($categoryWeights->isEmpty()) {
            $categoryWeights = Item::where('university_id', $universityId)
                ->where('status', 'available')
                ->pluck('category')
                ->countBy();
        }

        $topCategories = $categoryWeights->sortDesc()->keys()->take(3);

        if ($topCategories->isEmpty()) {
            return collect();
        }

        return Item::query()
            ->where('university_id', $universityId)
            ->where('status', 'available')
            ->where('user_id', '!=', $user->id)
            ->whereIn('category', $topCategories)
            ->with('owner:id,name')
            ->withCount(['ratings', 'transactions'])
            ->withAvg('ratings', 'rating')
            ->latest()
            ->take($limit)
            ->get();
    }
}