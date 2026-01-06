@extends('layouts.app')

@section('title', 'Chat with ' . $user->name . ' - Campus Resource Exchange')

@section('content')

<div class="row">
    <!-- Back Button -->
    <div class="col-12 mb-3">
        <a href="{{ route('frontend.messages.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Messages
        </a>
    </div>

    <!-- Chat Card -->
    <div class="col-lg-8 mx-auto">
        <div class="card" style="height: 600px; display: flex; flex-direction: column;">
            <!-- Header -->
            <div class="card-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                        <div>
                            <h6 style="color: white; font-weight: 600; margin: 0;">{{ $user->name }}</h6>
                            <small style="opacity: 0.9;">
                                <i class="bi bi-dot" style="color: #28a745;"></i> Online
                            </small>
                        </div>
                    </div>
                    <a href="{{ route('frontend.profile.show', $user) }}" class="btn btn-sm btn-light">
                        View Profile
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="messageContainer" style="flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa;">
                @forelse ($messages as $message)
                    <div style="margin-bottom: 15px; display: flex; {{ $message->sender_id === auth()->id() ? 'justify-content: flex-end' : '' }};">
                        <div style="max-width: 70%; {{ $message->sender_id === auth()->id() ? 'background: #0d6efd; color: white; border-radius: 18px 18px 4px 18px' : 'background: white; color: #333; border-radius: 18px 18px 18px 4px' }}; padding: 12px 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <p style="margin: 0; font-size: 0.95rem; line-height: 1.4;">{{ $message->content }}</p>
                            <small style="opacity: 0.7; display: block; margin-top: 6px; font-size: 0.8rem;">
                                {{ $message->created_at->format('h:i A') }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px 20px;">
                        <i class="bi bi-chat-dots" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                        <p class="text-muted">No messages yet. Start the conversation!</p>
                    </div>
                @endforelse
            </div>

            <!-- Input Area -->
            <div style="padding: 15px; border-top: 1px solid #dee2e6; background: white;">
                <form method="POST" action="{{ route('frontend.messages.send', $user) }}" id="messageForm">
                    @csrf
                    <div class="input-group input-group-lg">
                        <input type="text" name="content" class="form-control" placeholder="Type your message here..."
                            id="messageInput" required autocomplete="off">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-send-fill"></i> Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Scroll to bottom
    const messageContainer = document.getElementById('messageContainer');
    messageContainer.scrollTop = messageContainer.scrollHeight;

    // Focus on input
    document.getElementById('messageInput').focus();

    // Auto-scroll on new message
    function scrollToBottom() {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // Form submission
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        const input = document.getElementById('messageInput');
        if (input.value.trim() === '') {
            e.preventDefault();
        }
    });

    // Auto-refresh messages
    setInterval(function() {
        // Could add AJAX to refresh messages
    }, 2000);
</script>
@endsection
