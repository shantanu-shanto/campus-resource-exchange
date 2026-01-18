<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

class MessageController extends Controller
{
    /**
     * Display all conversations for authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $conversations = Conversation::where('user_id_1', $user->id)
            ->orWhere('user_id_2', $user->id)
            ->with([
                'user1:id,name',
                'user2:id,name',
                'lastMessage'
            ])
            ->orderByDesc('updated_at')
            ->paginate(15);

        // Map conversations to show other user and last message
        $conversations->getCollection()->transform(function($conversation) use ($user) {
            $otherUser = $conversation->user_id_1 === $user->id ? $conversation->user2 : $conversation->user1;
            $unreadCount = $conversation->messages()
                ->where('receiver_id', $user->id)
                ->where('read_at', null)
                ->count();

            return [
                'id' => $conversation->id,
                'other_user' => $otherUser,
                'last_message' => $conversation->lastMessage,
                'unread_count' => $unreadCount,
                'updated_at' => $conversation->updated_at,
            ];
        });

        return view('frontend.messages.index', compact('conversations'));
    }

    /**
     * Show conversation thread with another user
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        // Authorize user is part of conversation
        $this->authorizeConversation($conversation, $user);

        // Determine other user
        $otherUser = $conversation->user_id_1 === $user->id 
            ? $conversation->user2 
            : $conversation->user1;

        // Load messages with pagination
        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->latest()
            ->paginate(20);

        // Reverse for chronological order
        $messages->getCollection()->reverse();

        // Mark all messages as read for current user
        $conversation->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $unreadCount = 0; // Just marked as read

        return view('frontend.messages.show', [
            'conversation' => $conversation,
            'user' => $otherUser,
            'messages' => $messages,
            'unreadCount' => $unreadCount,
        ]);

    }

    /**
     * Start new conversation or get existing one
     */
    /**
     * Start new conversation or get existing one
     */
    public function startConversation($userId)
    {
        $user = Auth::user();
        
        // Get the other user
        $otherUser = User::find($userId);
        
        // Validate other user exists
        if (!$otherUser) {
            return redirect()->back()->with('error', 'User not found.');
        }
        
        // Cannot message self
        if ($user->id === $otherUser->id) {
            return redirect()->back()
                ->with('error', 'Cannot message yourself.');
        }

        // Find or create conversation
        $conversation = Conversation::where(function($q) use ($user, $otherUser) {
            $q->where('user_id_1', $user->id)
                ->where('user_id_2', $otherUser->id);
        })
        ->orWhere(function($q) use ($user, $otherUser) {
            $q->where('user_id_1', $otherUser->id)
                ->where('user_id_2', $user->id);
        })
        ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id_1' => $user->id,
                'user_id_2' => $otherUser->id,  // Now guaranteed to have a value
            ]);
        }

        return redirect()->route('frontend.messages.show', $conversation);
    }


    /**
     * Send message in conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        // Authorize user is part of conversation
        $this->authorizeConversation($conversation, $user);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Determine receiver
        $receiverId = $conversation->user_id_1 === $user->id 
            ? $conversation->user_id_2 
            : $conversation->user_id_1;

        // Create message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $validated['message'],
        ]);

        // Update conversation timestamp
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

        // Only receiver can mark as read
        if ($message->receiver_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $message->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark entire conversation as read
     */
    public function markConversationAsRead(Conversation $conversation)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        $conversation->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a message (soft delete)
     */
    public function deleteMessage(Message $message)
    {
        $user = Auth::user();

        // Only sender can delete
        if ($message->sender_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $message->update(['deleted_at' => now()]);

        return back()->with('success', 'Message deleted.');
    }

    /**
     * Delete entire conversation
     */
    public function deleteConversation(Conversation $conversation)
    {
        $user = Auth::user();

        $this->authorizeConversation($conversation, $user);

        // Soft delete all messages
        $conversation->messages()->update(['deleted_at' => now()]);
        
        // Delete conversation
        $conversation->delete();

        return redirect()->route('frontend.messages.index')
            ->with('success', 'Conversation deleted.');
    }

    /**
     * Get unread message count
     */
    public function unreadCount()
    {
        $user = Auth::user();

        $count = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Search conversations by user name
     */
    public function searchConversations(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');

        if (!$search) {
            return back();
        }

        $conversations = Conversation::where('user_id_1', $user->id)
            ->orWhere('user_id_2', $user->id)
            ->with([
                'user1:id,name',
                'user2:id,name',
                'lastMessage'
            ])
            ->get()
            ->filter(function($conversation) use ($user, $search) {
                $otherUser = $conversation->user_id_1 === $user->id 
                    ? $conversation->user2 
                    : $conversation->user1;
                
                return stripos($otherUser->name, $search) !== false;
            })
            ->values();

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
            ->with([
                'user1:id,name',
                'user2:id,name',
                'lastMessage'
            ])
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->map(function($conversation) use ($user) {
                $otherUser = $conversation->user_id_1 === $user->id 
                    ? $conversation->user2 
                    : $conversation->user1;

                $unreadCount = $conversation->messages()
                    ->where('receiver_id', $user->id)
                    ->where('read_at', null)
                    ->count();

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        // Removed: 'profile_image' => $otherUser->profile_image,
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

        $page = $request->get('page', 1);
        $perPage = 20;

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'total_pages' => $messages->lastPage(),
                'per_page' => $perPage,
            ]
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
            'total_messages' => $totalMessages,
            'unread_messages' => $unreadMessages,
        ]);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Authorize user is part of conversation
     */
    private function authorizeConversation(Conversation $conversation, User $user)
    {
        if ($conversation->user_id_1 !== $user->id && $conversation->user_id_2 !== $user->id) {
            abort(403, 'Unauthorized to access this conversation.');
        }
    }
}
