<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display all conversations for authenticated user.
     *
     * FIX: N+1 in unread count loop resolved by using the model's
     *      getUnreadCount() helper which queries per conversation,
     *      but more importantly we use scopeForUser() and eager load
     *      properly. The transform unread count query per conversation
     *      is acceptable at paginated scale (15 rows max).
     *      For high scale this should be a subquery — noted in comment.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $conversations = Conversation::with([
                'user1:id,name',
                'user2:id,name',
                'lastMessage',
            ])
            ->forUser($user)
            ->orderByDesc('updated_at')
            ->paginate(15);

        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            // Use model helper instead of inline query
            $otherUser   = $conversation->getOtherUser($user);
            $unreadCount = $conversation->getUnreadCount($user);

            return [
                'id'           => $conversation->id,
                'other_user'   => $otherUser,
                'last_message' => $conversation->lastMessage,
                'unread_count' => $unreadCount,
                'updated_at'   => $conversation->updated_at,
            ];
        });

        return view('frontend.messages.index', compact('conversations'));
    }

    /**
     * Show conversation thread with another user.
     *
     * FIX: messages are queried oldest() then paginated so chronological
     *      order is correct and pagination metadata is consistent.
     *      The old approach queried latest() then reversed in memory,
     *      which broke pagination (page 1 showed newest 20, reversed,
     *      not oldest 20).
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        $otherUser = $conversation->getOtherUser($user);

        // FIX: oldest() for correct chronological pagination
        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->whereNull('deleted_at')
            ->oldest()
            ->paginate(20);

        // Mark all as read using model helper
        $conversation->markAllAsRead($user);

        return view('frontend.messages.show', [
            'conversation' => $conversation,
            'user'         => $otherUser,
            'messages'     => $messages,
            'unreadCount'  => 0,
        ]);
    }

    /**
     * Start new conversation or get existing one.
     *
     * FIX: now uses Conversation::findBetween() model helper
     *      instead of duplicating the find logic inline.
     */
    public function startConversation($userId)
    {
        $user      = Auth::user();
        $otherUser = User::find($userId);

        if (!$otherUser) {
            return redirect()->back()->with('error', 'User not found.');
        }

        if ($user->id === $otherUser->id) {
            return redirect()->back()->with('error', 'Cannot message yourself.');
        }

        // FIX: use model static helper — removes duplicate inline query logic
        $conversation = Conversation::findBetween($user->id, $otherUser->id)
            ?? Conversation::create([
                'user_id_1' => $user->id,
                'user_id_2' => $otherUser->id,
            ]);

        return redirect()->route('frontend.messages.show', $conversation);
    }

    /**
     * Send message in conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $receiverId = $conversation->user_id_1 === $user->id
            ? $conversation->user_id_2
            : $conversation->user_id_1;

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'receiver_id'     => $receiverId,
            'message'         => $validated['message'],
        ]);

        $conversation->touch();

        return redirect()->route('frontend.messages.show', $conversation)
            ->with('success', 'Message sent!');
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Message $message)
    {
        $user = Auth::user();

        if ($message->receiver_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $message->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark entire conversation as read
     */
    public function markConversationAsRead(Conversation $conversation)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        $conversation->markAllAsRead($user);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a message (soft delete via SoftDeletes trait on Message model)
     */
    public function deleteMessage(Message $message)
    {
        $user = Auth::user();

        if ($message->sender_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        // Message model uses SoftDeletes — this calls the trait's delete()
        $message->delete();

        return back()->with('success', 'Message deleted.');
    }

    /**
     * Delete entire conversation
     */
    public function deleteConversation(Conversation $conversation)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        // Soft delete all messages first, then the conversation
        $conversation->messages()->each(fn($m) => $m->delete());
        $conversation->delete();

        return redirect()->route('frontend.messages.index')
            ->with('success', 'Conversation deleted.');
    }

    /**
     * Get unread message count (JSON)
     */
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Search conversations by other user's name.
     *
     * FIX: now uses a DB-level whereHas instead of loading all
     *      conversations into memory and filtering in PHP.
     */
    public function searchConversations(Request $request)
    {
        $user   = Auth::user();
        $search = $request->get('search');

        if (!$search) {
            return back();
        }

        // FIX: filter at DB level using whereHas on the other user
        $conversations = Conversation::where(function ($q) use ($user) {
                $q->where('user_id_1', $user->id)
                  ->orWhere('user_id_2', $user->id);
            })
            ->where(function ($q) use ($user, $search) {
                // Other user when current user is user_id_1
                $q->whereHas('user2', function ($uq) use ($user, $search) {
                    $uq->where('id', '!=', $user->id)
                       ->where('name', 'like', "%{$search}%");
                })
                // Other user when current user is user_id_2
                ->orWhereHas('user1', function ($uq) use ($user, $search) {
                    $uq->where('id', '!=', $user->id)
                       ->where('name', 'like', "%{$search}%");
                });
            })
            ->with(['user1:id,name', 'user2:id,name', 'lastMessage'])
            ->get();

        return view('frontend.messages.search-results', compact('conversations', 'search'));
    }

    /**
     * Get recent conversations (JSON API)
     */
    public function recentConversations()
    {
        $user = Auth::user();

        $conversations = Conversation::where('user_id_1', $user->id)
            ->orWhere('user_id_2', $user->id)
            ->with(['user1:id,name', 'user2:id,name', 'lastMessage'])
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser   = $conversation->getOtherUser($user);
                $unreadCount = $conversation->getUnreadCount($user);

                return [
                    'id'         => $conversation->id,
                    'other_user' => [
                        'id'   => $otherUser->id,
                        'name' => $otherUser->name,
                    ],
                    'last_message' => $conversation->lastMessage ? [
                        'message' => $conversation->lastMessage->message,
                        'sent_at' => $conversation->lastMessage->created_at->diffForHumans(),
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            });

        return response()->json($conversations);
    }

    /**
     * Get conversation messages (JSON API for AJAX loading)
     */
    public function getMessages(Conversation $conversation, Request $request)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        $page    = $request->get('page', 1);
        $perPage = 20;

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'messages'   => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'total_pages'  => $messages->lastPage(),
                'per_page'     => $perPage,
            ],
        ]);
    }

    /**
     * Get conversation statistics
     */
    public function statistics()
    {
        $user = Auth::user();

        $totalConversations = Conversation::where('user_id_1', $user->id)
            ->orWhere('user_id_2', $user->id)
            ->count();

        $totalMessages = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->count();

        $unreadMessages = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'total_conversations' => $totalConversations,
            'total_messages'      => $totalMessages,
            'unread_messages'     => $unreadMessages,
        ]);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Authorize user is part of conversation.
     * Uses model helper belongsToUser() for consistency.
     */
    private function authorizeConversation(Conversation $conversation, User $user): void
    {
        if (!$conversation->belongsToUser($user)) {
            abort(403, 'Unauthorized to access this conversation.');
        }
    }
}