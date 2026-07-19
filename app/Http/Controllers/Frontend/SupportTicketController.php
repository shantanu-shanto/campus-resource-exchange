<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    // ========================================
    // Ticket Listing
    // ========================================

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = SupportTicket::forUser($user->id)
            ->with(['latestReply.author:id,name'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(10)->withQueryString();

        $counts = [
            'all'         => SupportTicket::forUser($user->id)->count(),
            'open'        => SupportTicket::forUser($user->id)->open()->count(),
            'in_progress' => SupportTicket::forUser($user->id)->inProgress()->count(),
            'resolved'    => SupportTicket::forUser($user->id)->resolved()->count(),
            'closed'      => SupportTicket::forUser($user->id)->closed()->count(),
        ];

        return view('frontend.support.index', compact('tickets', 'counts'));
    }

    // ========================================
    // Create Ticket
    // ========================================

    public function create(Request $request)
    {
        $user = Auth::user();

        $userTransactions = $user->transactionsAsBorrower()
            ->with('item:id,title')
            ->latest()
            ->limit(10)
            ->get(['id', 'type', 'status', 'item_id'])
            ->merge(
                $user->transactionsAsOwner()
                    ->with('item:id,title')
                    ->latest()
                    ->limit(10)
                    ->get(['id', 'type', 'status', 'item_id'])
            )
            ->unique('id')
            ->sortByDesc('id')
            ->take(20);

        $userItems = $user->items()
            ->latest()
            ->limit(20)
            ->get(['id', 'title']);

        $selectedTransaction = $request->get('transaction_id');
        $selectedItem        = $request->get('item_id');

        return view('frontend.support.create', compact(
            'userTransactions',
            'userItems',
            'selectedTransaction',
            'selectedItem'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'subject'        => 'required|string|max:255',
            'description'    => 'required|string|min:10|max:5000',
            'category'       => ['required', Rule::in([
                'transaction_issue', 'item_condition', 'penalty_dispute',
                'user_behaviour', 'account_issue', 'other',
            ])],
            'priority'       => ['required', Rule::in(['low', 'medium', 'high'])],
            'transaction_id' => 'nullable|exists:transactions,id',
            'item_id'        => 'nullable|exists:items,id',
        ]);

        SupportTicket::create([
            'user_id'        => $user->id,
            'university_id'  => $user->university_id,
            'subject'        => $validated['subject'],
            'description'    => $validated['description'],
            'category'       => $validated['category'],
            'priority'       => $validated['priority'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'item_id'        => $validated['item_id'] ?? null,
        ]);

        return redirect()->route('frontend.support.index')
            ->with('success', 'Your support ticket has been submitted. Your University Admin will respond shortly.');
    }

    // ========================================
    // View Ticket
    // ========================================

    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $ticket->load([
            'user:id,name,email',
            'replies.author:id,name',
            'transaction.item:id,title',
            'item:id,title',
            'resolver:id,name',
        ]);

        return view('frontend.support.show', compact('ticket'));
    }

    // ========================================
    // Reply
    // ========================================

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if ($ticket->isClosed()) {
            return back()->with('error', 'This ticket is closed and cannot receive replies.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:5|max:2000',
        ]);

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'message'     => $validated['message'],
            'sender_role' => 'user',
        ]);

        if ($ticket->isResolved()) {
            $ticket->reopen();
        }

        return redirect()->back()->with('success', 'Reply sent.');
    }

    // ========================================
    // Close
    // ========================================

    public function close(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if (!$ticket->isResolved()) {
            return back()->with('error', 'Only resolved tickets can be closed.');
        }

        $ticket->markAsClosed();

        return redirect()->route('frontend.support.index')
            ->with('success', 'Ticket closed. Glad the issue was resolved!');
    }

    // ========================================
    // Reopen
    // ========================================

    public function reopen(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if (!$ticket->isResolved()) {
            return back()->with('error', 'Only resolved tickets can be reopened.');
        }

        $ticket->reopen();

        return redirect()->back()
            ->with('info', 'Ticket reopened. Please describe what is still unresolved in a reply.');
    }

    // ========================================
    // Authorization
    // ========================================

    private function authorizeTicket(SupportTicket $ticket): void
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to access this ticket.');
        }
    }
}
