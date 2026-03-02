<?php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniAdminItemController extends Controller
{
    /**
     * Helper: base query scoped to this university.
     */
    private function scopedItems()
    {
        return Item::where('university_id', auth()->user()->university_id);
    }

    /**
     * List all items within this university.
     */
    public function index(Request $request): View
    {
        $items = $this->scopedItems()
            ->with(['owner', 'university'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->mode, fn($q) => $q->where('availability_mode', $request->mode))
            ->when($request->search, fn($q) => $q->search($request->search))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'       => $this->scopedItems()->count(),
            'available' => $this->scopedItems()->where('status', 'available')->count(),
            'borrowed'  => $this->scopedItems()->where('status', 'borrowed')->count(),
            'sold'      => $this->scopedItems()->where('status', 'sold')->count(),
        ];

        return view('uni-admin.items.index', compact('items', 'counts'));
    }

    /**
     * Show a single item's details and transaction history.
     */
    public function show(Item $item): View
    {
        $this->authorizeItem($item);

        $item->load([
            'owner',
            'transactions.borrower',
            'transactions.penalties',
            'ratings.rater',
        ]);

        return view('uni-admin.items.show', compact('item'));
    }

    /**
     * Flag an item as suspicious / policy-violating.
     * Marks it unavailable and notifies the owner.
     */
    public function flag(Item $item): RedirectResponse
    {
        $this->authorizeItem($item);

        $item->update(['status' => 'reserved']); // 'reserved' used as flagged/held state

        return back()->with('success', "Item '{$item->title}' has been flagged and hidden from listings.");
    }

    /**
     * Permanently remove an item from the platform.
     */
    public function destroy(Item $item): RedirectResponse
    {
        $this->authorizeItem($item);

        // Block deletion if item has active transactions
        if ($item->activeTransaction) {
            return back()->with('warning', 'Cannot delete an item with an active transaction.');
        }

        $item->delete();

        return redirect()->route('uni-admin.items.index')
            ->with('success', "Item '{$item->title}' has been removed.");
    }

    /**
     * Ensure item belongs to this uni admin's university.
     */
    private function authorizeItem(Item $item): void
    {
        if ($item->university_id !== auth()->user()->university_id) {
            abort(403, 'You do not have permission to manage this item.');
        }
    }
}