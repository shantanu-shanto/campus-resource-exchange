<?php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniAdminTransactionController extends Controller
{
    private function scopedTransactions()
    {
        return Transaction::forUniversity(auth()->user()->university_id);
    }

    /**
     * List all transactions within this university.
     */
    public function index(Request $request): View
    {
        $transactions = $this->scopedTransactions()
            ->with(['item', 'borrower', 'owner'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->whereHas('item', function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'       => $this->scopedTransactions()->count(),
            'active'    => $this->scopedTransactions()->whereIn('status', ['active', 'pending'])->count(),
            'late'      => $this->scopedTransactions()->where('status', 'late')->count(),
            'completed' => $this->scopedTransactions()->where('status', 'completed')->count(),
        ];

        return view('uni-admin.transactions.index', compact('transactions', 'counts'));
    }

    /**
     * Show a single transaction with full details and penalties.
     */
    public function show(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        $transaction->load([
            'item.owner',
            'borrower',
            'owner',
            'penalties',
            'ratings.rater',
        ]);

        return view('uni-admin.transactions.show', compact('transaction'));
    }

    /**
     * Ensure transaction belongs to this university.
     */
    private function authorizeTransaction(Transaction $transaction): void
    {
        if ($transaction->item->university_id !== auth()->user()->university_id) {
            abort(403, 'You do not have permission to view this transaction.');
        }
    }
}