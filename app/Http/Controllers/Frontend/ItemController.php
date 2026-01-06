<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Display listing of all available items (Homepage)
     */
    public function index(Request $request)
    {
        $query = Item::query()
            ->with(['owner:id,name,email', 'activeTransaction.borrower:id,name'])
            ->available();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Filter by availability mode
        $mode = $request->get('mode');
        if ($mode === 'lend') {
            $query->forLending();
        } elseif ($mode === 'sell') {
            $query->forSelling();
        }

        // Paginate and add counts
        $items = $query->withCount(['ratings', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('frontend.items.index', compact('items'));
    }

    /**
     * Show detailed view of a specific item
     */
    public function show(Item $item)
    {
        // Eager load relationships
        $item->load([
            'owner:id,name,email',
            'ratings.rater:id,name',
            'transactions' => function ($query) {
                $query->where('status', 'completed')->latest();
            }
        ]);

        $avgRating = $item->averageRating();
        $totalBorrowed = $item->totalBorrowCount();

        // Permission checks
        $isOwner = Auth::check() && Auth::id() === $item->user_id;
        $canManage = Auth::check() && Auth::user()->canManageItem($item);
        $canRequest = Auth::check() 
            && !$isOwner 
            && !Penalty::borrowerHasPending(Auth::user())
            && $item->status === 'available';

        return view('frontend.items.show', compact(
            'item',
            'avgRating',
            'totalBorrowed',
            'isOwner',
            'canManage',
            'canRequest'
        ));
    }

    /**
     * Show form to create new item
     */
    public function create()
    {
        // Check for unpaid penalties
        if (Auth::check() && Penalty::borrowerHasPending(Auth::user())) {
            return redirect()->route('frontend.items.index')
                ->with('error', 'Pay all pending penalties before listing new items.');
        }

        return view('frontend.items.create');
    }

    /**
     * Store newly created item in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:1000',
            'availability_mode' => ['required', Rule::in(['lend', 'sell', 'both'])],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                Rule::requiredIf(fn() => in_array($request->availability_mode, ['sell', 'both']))
            ],
            'lending_duration_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:30',
                Rule::requiredIf(fn() => in_array($request->availability_mode, ['lend', 'both']))
            ],
            'pickup_location' => 'required|string|max:255',
            'item_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'available';

        // Handle image upload
        if ($request->hasFile('item_image')) {
            $validated['image_path'] = $request->file('item_image')
                ->store('item-images', 'public');
        }

        Item::create($validated);

        return redirect()->route('frontend.items.index')
            ->with('success', 'Item listed successfully! It\'s now visible to other students.');
    }

    /**
     * Show edit form for an item
     */
    public function edit(Item $item)
    {
        $this->authorizeItem($item);
        return view('frontend.items.edit', compact('item'));
    }

    /**
     * Update item in database
     */
    public function update(Request $request, Item $item)
    {
        $this->authorizeItem($item);

        // Cannot edit if item is in active transaction
        if ($item->activeTransaction && $item->activeTransaction->status !== 'cancelled') {
            return back()->with('error', 'Cannot edit item with active transaction.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:1000',
            'availability_mode' => ['required', Rule::in(['lend', 'sell', 'both'])],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                Rule::requiredIf(fn() => in_array($request->availability_mode, ['sell', 'both']))
            ],
            'lending_duration_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:30',
                Rule::requiredIf(fn() => in_array($request->availability_mode, ['lend', 'both']))
            ],
            'pickup_location' => 'required|string|max:255',
            'item_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image update
        if ($request->hasFile('item_image')) {
            // Delete old image if exists
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = $request->file('item_image')
                ->store('item-images', 'public');
        }

        $item->update($validated);

        return redirect()->route('frontend.items.show', $item)
            ->with('success', 'Item updated successfully!');
    }

    /**
     * Delete an item
     */
    public function destroy(Item $item)
    {
        $this->authorizeItem($item);

        // Cannot delete if active transaction exists
        if ($item->activeTransaction) {
            return back()->with('error', 'Cannot delete item with active transaction.');
        }

        // Delete image from storage
        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('frontend.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Show user's own items (My Items page)
     */
    public function myItems()
    {
        $items = Auth::user()
            ->items()
            ->with(['activeTransaction.borrower:id,name', 'ratings'])
            ->withCount(['transactions' => fn($q) => $q->where('status', 'completed')])
            ->latest()
            ->paginate(10);

        return view('frontend.items.my-items', compact('items'));
    }

    /**
     * Request to borrow or buy an item
     */
    public function requestTransaction(Request $request, Item $item)
    {
        // Validate request can be made
        $this->validateTransactionRequest($item);

        $type = $item->isAvailableForLending() ? 'lend' : 'sell';

        // Create transaction
        $transaction = Transaction::create([
            'item_id' => $item->id,
            'borrower_id' => Auth::id(),
            'type' => $type,
            'status' => 'pending',
            'start_date' => now(),
            'due_date' => $type === 'lend' ? now()->addDays($item->lending_duration_days) : null,
            'deposit_amount' => $type === 'lend' ? ($item->price ?? 0) * 0.5 : null,
            'final_price' => $type === 'sell' ? $item->price : null,
        ]);

        // Mark item as reserved
        $item->update(['status' => 'reserved']);

        return redirect()->route('frontend.transactions.show', $transaction)
            ->with('success', "Request sent to {$item->owner->name}. Use messaging to coordinate.");
    }

    /**
     * Cancel reservation (owner only)
     */
    public function cancelReservation(Item $item)
    {
        $this->authorizeItem($item);

        $transaction = $item->activeTransaction;
        
        if ($transaction && $transaction->status === 'pending') {
            $transaction->markAsCancelled();
            $item->markAsAvailable();
            
            return back()->with('success', 'Reservation cancelled.');
        }

        return back()->with('error', 'No pending reservation to cancel.');
    }

    /**
     * Mark item as borrowed
     */
    public function markAsBorrowed(Item $item, Transaction $transaction)
    {
        $this->authorizeItem($item);

        if ($transaction->item_id !== $item->id || $transaction->status !== 'pending') {
            return back()->with('error', 'Invalid transaction.');
        }

        $transaction->markAsActive();
        $item->markAsBorrowed();

        return back()->with('success', 'Item marked as borrowed. Due date: ' . $transaction->due_date->format('M d, Y'));
    }

    /**
     * Mark item as returned
     */
    public function markAsReturned(Item $item, Transaction $transaction)
    {
        $this->authorizeItem($item);

        if ($transaction->item_id !== $item->id || $transaction->status !== 'active') {
            return back()->with('error', 'Invalid transaction.');
        }

        // Check if late and create penalty if needed
        if ($transaction->isOverdue()) {
            $daysLate = $transaction->daysOverdue();
            $penaltyAmount = Penalty::calculateAmount($daysLate);

            Penalty::create([
                'transaction_id' => $transaction->id,
                'days_late' => $daysLate,
                'amount' => $penaltyAmount,
                'status' => 'pending',
            ]);

            $transaction->markAsLate();
        } else {
            $transaction->markAsCompleted();
        }

        $item->markAsAvailable();

        return back()->with('success', 'Item marked as returned.');
    }

    /**
     * Mark item as sold
     */
    public function markAsSold(Item $item, Transaction $transaction)
    {
        $this->authorizeItem($item);

        if ($transaction->item_id !== $item->id || $transaction->type !== 'sell') {
            return back()->with('error', 'Invalid transaction.');
        }

        $transaction->markAsCompleted();
        $item->markAsSold();

        return back()->with('success', 'Item marked as sold.');
    }

    // ========================================
    // Helper & Authorization Methods
    // ========================================

    /**
     * Authorize user can manage item
     */
    private function authorizeItem(Item $item)
    {
        if (!Auth::check() || !Auth::user()->canManageItem($item)) {
            abort(403, 'Unauthorized to manage this item.');
        }
    }

    /**
     * Validate user can request transaction
     */
    private function validateTransactionRequest(Item $item)
    {
        // Check if available
        if ($item->status !== 'available') {
            abort(403, 'Item is not available.');
        }

        // Check if user is owner
        if (Auth::id() === $item->user_id) {
            abort(403, 'Cannot request your own item.');
        }

        // Check for unpaid penalties
        if (Penalty::borrowerHasPending(Auth::user())) {
            abort(403, 'Pay all penalties before requesting items.');
        }

        // Check if user already has active transaction for this item
        if (Transaction::where('item_id', $item->id)
            ->where('borrower_id', Auth::id())
            ->whereIn('status', ['pending', 'active'])
            ->exists()) {
            abort(403, 'You already have an active request for this item.');
        }
    }
}
