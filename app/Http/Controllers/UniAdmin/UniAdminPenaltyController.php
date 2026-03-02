<?php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniAdminPenaltyController extends Controller
{
    private function scopedPenalties()
    {
        return Penalty::forUniversity(auth()->user()->university_id);
    }

    /**
     * List all penalties within this university.
     */
    public function index(Request $request): View
    {
        $penalties = $this->scopedPenalties()
            ->with(['transaction.item', 'transaction.borrower'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->whereHas('transaction.borrower', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'     => $this->scopedPenalties()->count(),
            'pending' => $this->scopedPenalties()->pending()->count(),
            'paid'    => $this->scopedPenalties()->paid()->count(),
            'waived'  => $this->scopedPenalties()->waived()->count(),
        ];

        $totalOutstanding = $this->scopedPenalties()->pending()->sum('amount');

        return view('uni-admin.penalties.index', compact('penalties', 'counts', 'totalOutstanding'));
    }

    /**
     * Show a single penalty's full details.
     */
    public function show(Penalty $penalty): View
    {
        $this->authorizePenalty($penalty);

        $penalty->load([
            'transaction.item',
            'transaction.borrower',
            'transaction.owner',
        ]);

        return view('uni-admin.penalties.show', compact('penalty'));
    }

    /**
     * Waive a penalty — mark it as forgiven.
     */
    public function waive(Penalty $penalty): RedirectResponse
    {
        $this->authorizePenalty($penalty);

        if (!$penalty->isPending()) {
            return back()->with('warning', 'Only pending penalties can be waived.');
        }

        $penalty->markAsWaived();

        return back()->with('success', "Penalty of {$penalty->formatted_amount} has been waived.");
    }

    /**
     * Mark a penalty as paid (admin confirms offline payment).
     */
    public function markPaid(Penalty $penalty): RedirectResponse
    {
        $this->authorizePenalty($penalty);

        if (!$penalty->isPending()) {
            return back()->with('warning', 'Only pending penalties can be marked as paid.');
        }

        $penalty->markAsPaid();

        return back()->with('success', "Penalty of {$penalty->formatted_amount} has been marked as paid.");
    }

    /**
     * Ensure penalty belongs to this university.
     */
    private function authorizePenalty(Penalty $penalty): void
    {
        $universityId = $penalty->transaction->item->university_id ?? null;

        if ($universityId !== auth()->user()->university_id) {
            abort(403, 'You do not have permission to manage this penalty.');
        }
    }
}