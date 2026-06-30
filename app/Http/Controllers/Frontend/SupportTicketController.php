<?php

// app/Http/Controllers/Frontend/SupportTicketController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    /**
     * List all tickets raised by the authenticated user.
     * STUB: returns dummy ticket list for frontend demo.
     */
    public function index(Request $request)
    {
        $counts = [
            'all'         => 4,
            'open'        => 1,
            'in_progress' => 1,
            'resolved'    => 1,
            'closed'      => 1,
        ];

        $tickets = collect([
            (object)[
                'id'               => 1,
                'subject'          => 'Item was returned in damaged condition',
                'status'           => 'open',
                'priority'         => 'high',
                'created_at'       => now()->subDays(2),
                'time_display'     => '2 days ago',
                'latestReply'      => null,
                'getStatusLabel'   => fn() => 'Open',
                'getStatusBadgeColor' => fn() => 'danger',
                'getPriorityLabel' => fn() => 'High',
                'getPriorityBadgeColor' => fn() => 'danger',
                'getCategoryLabel' => fn() => 'Item Condition',
                'isResolved'       => fn() => false,
            ],
            (object)[
                'id'               => 2,
                'subject'          => 'Penalty charged incorrectly for on-time return',
                'status'           => 'in_progress',
                'priority'         => 'medium',
                'created_at'       => now()->subDays(5),
                'time_display'     => '5 days ago',
                'latestReply'      => (object)[
                    'author'       => (object)['name' => 'University Admin'],
                    'message'      => 'We are looking into this for you.',
                    'time_display' => '3 days ago',
                ],
                'getStatusLabel'   => fn() => 'In Progress',
                'getStatusBadgeColor' => fn() => 'warning',
                'getPriorityLabel' => fn() => 'Medium',
                'getPriorityBadgeColor' => fn() => 'warning',
                'getCategoryLabel' => fn() => 'Penalty Dispute',
                'isResolved'       => fn() => false,
            ],
            (object)[
                'id'               => 3,
                'subject'          => 'Cannot log into my account after password reset',
                'status'           => 'resolved',
                'priority'         => 'medium',
                'created_at'       => now()->subDays(10),
                'time_display'     => '10 days ago',
                'latestReply'      => (object)[
                    'author'       => (object)['name' => 'University Admin'],
                    'message'      => 'Your account has been unlocked. Please try again.',
                    'time_display' => '8 days ago',
                ],
                'getStatusLabel'   => fn() => 'Resolved',
                'getStatusBadgeColor' => fn() => 'success',
                'getPriorityLabel' => fn() => 'Medium',
                'getPriorityBadgeColor' => fn() => 'warning',
                'getCategoryLabel' => fn() => 'Account Issue',
                'isResolved'       => fn() => true,
                'resolver'         => (object)['name' => 'University Admin'],
            ],
            (object)[
                'id'               => 4,
                'subject'          => 'Request to extend lending duration for textbook',
                'status'           => 'closed',
                'priority'         => 'low',
                'created_at'       => now()->subDays(20),
                'time_display'     => '20 days ago',
                'latestReply'      => null,
                'getStatusLabel'   => fn() => 'Closed',
                'getStatusBadgeColor' => fn() => 'secondary',
                'getPriorityLabel' => fn() => 'Low',
                'getPriorityBadgeColor' => fn() => 'secondary',
                'getCategoryLabel' => fn() => 'Transaction Issue',
                'isResolved'       => fn() => false,
            ],
        ]);

        // Filter by status for tab demo
        if ($status = $request->get('status')) {
            $tickets = $tickets->filter(fn($t) => $t->status === $status);
        }

        // Wrap in a simple paginator-like object the view can call ->count() and ->links() on
        // Using a plain collection — links() won't render but count() works fine
        $tickets = new \Illuminate\Pagination\LengthAwarePaginator(
            $tickets->values(),
            $counts['all'],
            10,
            1,
            ['path' => route('frontend.support.index')]
        );

        return view('frontend.support.index', compact('tickets', 'counts'));
    }

    /**
     * Show the form to raise a new ticket.
     * STUB: passes empty dropdown lists for demo.
     */
    public function create(Request $request)
    {
        $userTransactions = collect([
            (object)['id' => 101, 'type' => 'lend',  'status' => 'active',    'item' => (object)['title' => 'Engineering Mathematics Vol. 2']],
            (object)['id' => 98,  'type' => 'sell',  'status' => 'completed', 'item' => (object)['title' => 'Scientific Calculator FX-991']],
            (object)['id' => 87,  'type' => 'share', 'status' => 'completed', 'item' => (object)['title' => 'Lab Safety Goggles']],
        ]);

        $userItems = collect([
            (object)['id' => 12, 'title' => 'Data Structures Textbook'],
            (object)['id' => 15, 'title' => 'Wireless Mouse'],
        ]);

        $selectedTransaction = $request->get('transaction_id');
        $selectedItem        = $request->get('item_id');

        return view('frontend.support.create', compact(
            'userTransactions',
            'userItems',
            'selectedTransaction',
            'selectedItem'
        ));
    }

    /**
     * STUB: redirect back to index with success flash instead of saving.
     */
    public function store(Request $request)
    {
        return redirect()->route('frontend.support.index')
            ->with('success', 'Your support ticket has been submitted. Your University Admin will respond shortly.');
    }

    /**
     * Show a single ticket thread.
     * STUB: uses a hardcoded dummy ticket keyed by ID for demo variety.
     */
    public function show($ticket)
    {
        $dummyTickets = [
            1 => (object)[
                'id'             => 1,
                'subject'        => 'Item was returned in damaged condition',
                'description'    => "I borrowed a copy of Engineering Mathematics Vol. 2 from Rafi Ahmed. When I returned it, he claimed there was a torn page on chapter 5. However, the page was already torn before I borrowed it — I have photos as proof.\n\nPlease review this and ensure the penalty issued against me is waived.",
                'status'         => 'open',
                'priority'       => 'high',
                'created_at'     => now()->subDays(2),
                'resolved_at'    => null,
                'closed_at'      => null,
                'time_display'   => '2 days ago',
                'resolver'       => null,
                'getStatusLabel' => fn() => 'Open',
                'getStatusBadgeColor' => fn() => 'danger',
                'getPriorityLabel' => fn() => 'High',
                'getPriorityBadgeColor' => fn() => 'danger',
                'getCategoryLabel' => fn() => 'Item Condition',
                'isResolved'     => fn() => false,
                'isClosed'       => fn() => false,
                'isActive'       => fn() => true,
                'user'           => (object)['name' => auth()->user()->name, 'email' => auth()->user()->email],
                'transaction'    => (object)[
                    'id'      => 101,
                    'type'    => 'lend',
                    'status'  => 'late',
                    'getStatusLabel'     => fn() => 'Late',
                    'getStatusBadgeColor'=> fn() => 'danger',
                    'item'    => (object)['title' => 'Engineering Mathematics Vol. 2'],
                ],
                'item'    => null,
                'replies' => collect([]),
            ],
            2 => (object)[
                'id'             => 2,
                'subject'        => 'Penalty charged incorrectly for on-time return',
                'description'    => "I returned the item on the 14th which was within the due date. However a penalty of ৳150 has been charged on my account. I have the WhatsApp message from the owner confirming receipt on time.\n\nKindly remove this penalty.",
                'status'         => 'in_progress',
                'priority'       => 'medium',
                'created_at'     => now()->subDays(5),
                'resolved_at'    => null,
                'closed_at'      => null,
                'time_display'   => '5 days ago',
                'resolver'       => null,
                'getStatusLabel' => fn() => 'In Progress',
                'getStatusBadgeColor' => fn() => 'warning',
                'getPriorityLabel' => fn() => 'Medium',
                'getPriorityBadgeColor' => fn() => 'warning',
                'getCategoryLabel' => fn() => 'Penalty Dispute',
                'isResolved'     => fn() => false,
                'isClosed'       => fn() => false,
                'isActive'       => fn() => true,
                'user'           => (object)['name' => auth()->user()->name, 'email' => auth()->user()->email],
                'transaction'    => null,
                'item'           => null,
                'replies'        => collect([
                    (object)[
                        'author'       => (object)['name' => auth()->user()->name, 'role' => 'user'],
                        'message'      => "I have attached the WhatsApp screenshot. Please check.",
                        'sender_role'  => 'user',
                        'created_at'   => now()->subDays(4),
                        'time_display' => '4 days ago',
                        'isFromAdmin'  => fn() => false,
                    ],
                    (object)[
                        'author'       => (object)['name' => 'University Admin', 'role' => 'uni_admin'],
                        'message'      => "Thank you for raising this. We are verifying the return date with the item owner and will update you within 24 hours.",
                        'sender_role'  => 'uni_admin',
                        'created_at'   => now()->subDays(3),
                        'time_display' => '3 days ago',
                        'isFromAdmin'  => fn() => true,
                    ],
                ]),
            ],
            3 => (object)[
                'id'             => 3,
                'subject'        => 'Cannot log into my account after password reset',
                'description'    => "After resetting my password using the forgot password link, I am still unable to log in. The system shows 'Invalid credentials' every time. I have tried on two different browsers.",
                'status'         => 'resolved',
                'priority'       => 'medium',
                'created_at'     => now()->subDays(10),
                'resolved_at'    => now()->subDays(8),
                'closed_at'      => null,
                'time_display'   => '10 days ago',
                'resolver'       => (object)['name' => 'University Admin'],
                'getStatusLabel' => fn() => 'Resolved',
                'getStatusBadgeColor' => fn() => 'success',
                'getPriorityLabel' => fn() => 'Medium',
                'getPriorityBadgeColor' => fn() => 'warning',
                'getCategoryLabel' => fn() => 'Account Issue',
                'isResolved'     => fn() => true,
                'isClosed'       => fn() => false,
                'isActive'       => fn() => false,
                'user'           => (object)['name' => auth()->user()->name, 'email' => auth()->user()->email],
                'transaction'    => null,
                'item'           => null,
                'replies'        => collect([
                    (object)[
                        'author'       => (object)['name' => 'University Admin', 'role' => 'uni_admin'],
                        'message'      => "We have reset your account session manually. Please try logging in again with your new password. Let us know if the issue persists.",
                        'sender_role'  => 'uni_admin',
                        'created_at'   => now()->subDays(8),
                        'time_display' => '8 days ago',
                        'isFromAdmin'  => fn() => true,
                    ],
                ]),
            ],
        ];

        // Default to ticket 1 if ID not in dummy set
        $ticketData = $dummyTickets[$ticket] ?? $dummyTickets[1];

        return view('frontend.support.show', ['ticket' => $ticketData]);
    }

    /**
     * STUB: redirect back with success.
     */
    public function reply(Request $request, $ticket)
    {
        return redirect()->back()->with('success', 'Reply sent.');
    }

    /**
     * STUB: redirect to index with success.
     */
    public function close($ticket)
    {
        return redirect()->route('frontend.support.index')
            ->with('success', 'Ticket closed. Glad the issue was resolved!');
    }

    /**
     * STUB: redirect back with info.
     */
    public function reopen($ticket)
    {
        return redirect()->back()
            ->with('info', 'Ticket reopened. Please describe what is still unresolved in a reply.');
    }
}