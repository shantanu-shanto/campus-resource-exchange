@extends('layouts.app')

@section('title', 'Messages - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">Messages</h1>
    <p class="text-muted">Communicate with other students</p>
</div>

<div class="row">
    <!-- Conversations List -->
    <div class="col-lg-4 mb-4 mb-lg-0">
        <div class="card" style="height: 600px; display: flex; flex-direction: column;">
            <div class="card-header">
                <i class="bi bi-chat-dots"></i> Conversations ({{ $conversations->count() }})
            </div>

            <!-- Search -->
            <div class="card-body" style="padding: 10px;">
                <input type="text" class="form-control form-control-sm" id="searchConversation" 
                    placeholder="Search conversations...">
            </div>

            <!-- Conversations -->
            <div style="flex: 1; overflow-y: auto; border-top: 1px solid #dee2e6;">
                @if ($conversations->count() > 0)
                    @foreach ($conversations as $conversation)
                        <a href="{{ route('frontend.messages.show', $conversation->otherUser()) }}"
                            class="list-group-item list-group-item-action p-3 border-0 border-bottom conversation-item"
                            style="cursor: pointer;">
                            <div style="display: flex; gap: 10px;">
                                <i class="bi bi-person-circle" style="font-size: 2rem; color: #0d6efd; flex-shrink: 0;"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <h6 style="color: #333; font-weight: 600; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $conversation->otherUser()->name }}
                                    </h6>
                                    <small class="text-muted d-block" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ Str::limit($conversation->lastMessage()?->content, 40) ?? 'No messages yet' }}
                                    </small>
                                    <small class="text-muted d-block mt-1">
                                        {{ $conversation->lastMessage()?->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                @if ($conversation->unreadCount(auth()->id()) > 0)
                                    <span class="badge bg-primary" style="flex-shrink: 0; align-self: center;">
                                        {{ $conversation->unreadCount(auth()->id()) }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @else
                    <div style="padding: 20px; text-align: center;">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                        <p class="text-muted">No conversations yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-lg-8">
        <div class="card" style="height: 600px; display: flex; flex-direction: column;">
            @if (request('user_id') || ($conversations->count() > 0 && !request('user_id')))
                @php
                    $chatUser = $selectedUser ?? $conversations->first()?->otherUser();
                @endphp

                @if ($chatUser)
                    <!-- Chat Header -->
                    <div class="card-header" style="border-bottom: 2px solid #dee2e6;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-person-circle" style="font-size: 1.5rem; color: #0d6efd;"></i>
                                <div>
                                    <h6 style="color: #333; font-weight: 600; margin: 0;">{{ $chatUser->name }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-dot" style="color: #28a745;"></i> Online
                                    </small>
                                </div>
                            </div>
                            <a href="{{ route('frontend.profile.show', $chatUser) }}" class="btn btn-sm btn-outline-primary">
                                View Profile
                            </a>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div id="messageContainer" style="flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa;">
                        @foreach ($messages as $message)
                            <div style="margin-bottom: 15px; display: flex; {{ $message->sender_id === auth()->id() ? 'justify-content: flex-end' : '' }};">
                                <div style="max-width: 70%; {{ $message->sender_id === auth()->id() ? 'background: #0d6efd; color: white' : 'background: white; color: #333' }}; padding: 10px 15px; border-radius: 12px; word-wrap: break-word;">
                                    <p style="margin: 0; font-size: 0.9rem;">{{ $message->content }}</p>
                                    <small style="opacity: 0.7; display: block; margin-top: 5px;">
                                        {{ $message->created_at->format('h:i A') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Message Input -->
                    <div style="padding: 15px; border-top: 1px solid #dee2e6;">
                        <form method="POST" action="{{ route('frontend.messages.send', $chatUser) }}" id="messageForm">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="content" class="form-control" placeholder="Type a message..."
                                    id="messageInput" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Empty State -->
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <i class="bi bi-chat-dots" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                            <h4 style="color: #666; font-weight: 600;">Select a conversation</h4>
                            <p class="text-muted">Choose someone to message</p>
                        </div>
                    </div>
                @endif
            @else
                <!-- No Conversations -->
                <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                    <div style="text-align: center;">
                        <i class="bi bi-chat-dots" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                        <h4 style="color: #666; font-weight: 600;">No messages yet</h4>
                        <p class="text-muted">Start a conversation by messaging someone</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Scroll to bottom of messages
    const messageContainer = document.getElementById('messageContainer');
    if (messageContainer) {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // Auto-refresh messages every 3 seconds
    setInterval(function() {
        // Could add AJAX to refresh messages
    }, 3000);

    // Search conversations
    document.getElementById('searchConversation')?.addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.conversation-item').forEach(item => {
            const name = item.querySelector('h6').textContent.toLowerCase();
            item.style.display = name.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endsection
