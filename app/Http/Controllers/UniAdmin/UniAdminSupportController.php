<?php

// app/Http/Controllers/UniAdmin/UniAdminSupportController.php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UniAdminSupportController extends Controller
{
    // ========================================
    // Ticket Listing
    // ========================================

    /**
     * List all tickets for the uni admin's university.
     * Campus isolation enforced via university_id.
     */
    public function index(Request $request)
    {
        $admin        = Auth::user();
        $universityId = $admin->university_id;

        $query = SupportTicket::forUniversity($universityId)
            ->with([
                'user:id,name,email',
                'latestReply.author:id,name',
            ])
            ->latest();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->byCategory($category);
        }

        // Filter by priority
        if ($priority = $request->get('priority')) {
            $query->byPriority($priority);
        }

        // Search by subject or user name
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Dashboard counts for tab navigation
        $counts = [
            'all'         => SupportTicket::forUniversity($universityId)->count(),
            'open'        => SupportTicket::forUniversity($universityId)->open()->count(),
            'in_progress' => SupportTicket::forUniversity($universityId)->inProgress()->count(),
            'resolved'    => SupportTicket::forUniversity($universityId)->resolved()->count(),
            'closed'      => SupportTicket::forUniversity($universityId)->closed()->count(),
        ];

        return view('uni-admin.support.index', compact('tickets', 'counts'));
    }

    // ========================================
    // View Ticket
    // ========================================

    /**
     * Show a single ticket with its full reply thread.
     */
    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $ticket->load([
            'user:id,name,email',
            'replies.author:id,name,role',
            'transaction.item:id,title',
            'transaction.borrower:id,name',
            'transaction.owner:id,name',
            'item:id,title',
            'resolver:id,name',
        ]);

        return view('uni-admin.support.show', compact('ticket'));
    }

    // ========================================
    // Reply
    // ========================================

    /**
     * Uni admin replies to a ticket.
     * Automatically transitions status to in_progress on first reply.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if ($ticket->isClosed()) {
            return back()->with('error', 'Cannot reply to a closed ticket.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:5|max:2000',
        ]);

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'message'     => $validated['message'],
            'sender_role' => 'uni_admin',
        ]);

        // Move to in_progress on first admin reply if still open
        if ($ticket->isOpen()) {
            $ticket->markAsInProgress();
        }

        return redirect()->route('uni-admin.support.show', $ticket)
            ->with('success', 'Reply sent to user.');
    }

    // ========================================
    // Resolve
    // ========================================

    /**
     * Uni admin marks a ticket as resolved.
     * Records who resolved it and when.
     */
    public function resolve(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if (!$ticket->isActive()) {
            return back()->with('error', 'Ticket is not active.');
        }

        $ticket->markAsResolved(Auth::user());

        return redirect()->route('uni-admin.support.show', $ticket)
            ->with('success', 'Ticket marked as resolved. The user can close it or reopen if needed.');
    }

    // ========================================
    // Force Close
    // ========================================

    /**
     * Uni admin force-closes a ticket (e.g. spam, duplicate, no response from user).
     */
    public function close(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if ($ticket->isClosed()) {
            return back()->with('error', 'Ticket is already closed.');
        }

        $ticket->markAsClosed();

        return redirect()->route('uni-admin.support.index')
            ->with('success', 'Ticket closed.');
    }

    // ========================================
    // Stats (JSON — for dashboard widget)
    // ========================================

    /**
     * Quick ticket stats for the uni admin dashboard.
     * Called by AJAX on the dashboard page.
     */
    public function stats()
    {
        $universityId = Auth::user()->university_id;

        return response()->json([
            'open'        => SupportTicket::forUniversity($universityId)->open()->count(),
            'in_progress' => SupportTicket::forUniversity($universityId)->inProgress()->count(),
            'resolved'    => SupportTicket::forUniversity($universityId)->resolved()->count(),
            'high_priority_open' => SupportTicket::forUniversity($universityId)
                ->open()
                ->byPriority('high')
                ->count(),
        ]);
    }

    // ========================================
    // Authorization
    // ========================================

    /**
     * Enforce campus isolation.
     * Uni admin can only access tickets from their own university.
     */
    private function authorizeTicket(SupportTicket $ticket): void
    {
        if ($ticket->university_id !== Auth::user()->university_id) {
            abort(403, 'Unauthorized to access this ticket.');
        }
    }
}