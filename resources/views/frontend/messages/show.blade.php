@extends('layouts.app')

@section('title', 'Chat with ' . $user->name . ' - Campus Resource Exchange')

@section('content')

<div class="container mt-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('frontend.messages.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Messages
        </a>
    </div>

    <!-- Chat Container -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Header -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i> {{ $user->name }}
                            <span class="badge bg-success ms-2">Online</span>
                        </h5>
                    </div>
                    <a href="{{ route('frontend.profile.show', $user) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-eye"></i> View Profile
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="card" style="height: 500px; overflow-y: auto; display: flex; flex-direction: column;">
                <div class="card-body" style="flex: 1; overflow-y: auto;">
                    @if ($messages->count() > 0)
                        @foreach ($messages as $message)
                            <div class="mb-3 d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="p-2 rounded" style="max-width: 70%; background-color: {{ $message->sender_id === auth()->id() ? '#007bff' : '#e9ecef' }}; color: {{ $message->sender_id === auth()->id() ? 'white' : 'black' }};">
                                    <p class="mb-1">{{ $message->message }}</p>
                                    <small style="opacity: 0.8;">{{ $message->created_at->format('h:i A') }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="margin-top: 150px;">
                            <i class="bi bi-chat" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No messages yet. Start the conversation!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Message Form -->
            <div class="card-footer">
                <form action="{{ route('frontend.messages.send', $conversation) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input 
                        type="text" 
                        name="message" 
                        placeholder="Type your message here..." 
                        class="form-control" 
                        required
                    >
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill"></i> Send
                    </button>
                </form>

                @if ($errors->has('message'))
                    <div class="alert alert-danger mt-2 mb-0">
                        {{ $errors->first('message') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
