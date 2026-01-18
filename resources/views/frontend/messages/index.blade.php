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
                        <a href="{{ route('frontend.messages.show', $conversation['id']) }}"
                            class="list-group-item list-group-item-action p-3 border-0 border-bottom conversation-item"
                            style="cursor: pointer;">
                            <div style="display: flex; gap: 10px;">
                                <i class="bi bi-person-circle" style="font-size: 2rem; color: #0d6efd; flex-shrink: 0;"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <h6 style="color: #333; font-weight: 600; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $conversation['other_user']->name }}
                                    </h6>
                                    <small class="text-muted d-block" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ Str::limit($conversation['last_message']?->message, 40) ?? 'No messages yet' }}
                                    </small>
                                    <small class="text-muted d-block mt-1">
                                        {{ $conversation['last_message']?->created_at->diffForHumans() ?? 'Just now' }}
                                    </small>
                                </div>
                                @if ($conversation['unread_count'] > 0)
                                    <span class="badge bg-primary" style="flex-shrink: 0; align-self: center;">
                                        {{ $conversation['unread_count'] }}
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
            <!-- Empty State: Select a conversation -->
            <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center;">
                    <i class="bi bi-chat-dots" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                    <h4 style="color: #666; font-weight: 600;">Select a conversation</h4>
                    <p class="text-muted">Choose someone to message from the list</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
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
