@extends('layouts.app')

@section('title', $user->name . '\'s Profile - Campus Resource Exchange')

@section('content')

<!-- User Header -->
<div class="card mb-4" style="border: none; background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); padding: 40px;">
    <div style="display: flex; gap: 30px; align-items: flex-start;">
        <!-- Avatar -->
        <div style="flex-shrink: 0;">
            <i class="bi bi-person-circle" style="font-size: 6rem; color: #0d6efd;"></i>
        </div>

        <!-- Info -->
        <div style="flex: 1;">
            <h1 style="color: #333; font-weight: 700; margin-bottom: 10px;">{{ $user->name }}</h1>
            <p class="text-muted mb-3">{{ $user->email }}</p>

            <!-- Stats -->
            <div class="row" style="margin-top: 20px;">
                <div class="col-6 col-md-3">
                    <small class="text-muted">Items Listed</small>
                    <h5 style="color: #333; font-weight: 700; margin: 5px 0;">
                        {{ $user->items->count() }}
                    </h5>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted">Avg Rating</small>
                    <h5 style="color: #333; font-weight: 700; margin: 5px 0;">
                        <i class="bi bi-star-fill" style="color: #ffc107;"></i> {{ round($user->averageRating(), 1) }}
                    </h5>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted">Member Since</small>
                    <h5 style="color: #333; font-weight: 700; margin: 5px 0;">
                        {{ $user->created_at->format('M Y') }}
                    </h5>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted">Total Reviews</small>
                    <h5 style="color: #333; font-weight: 700; margin: 5px 0;">
                        {{ $user->ratingsReceived->count() }}
                    </h5>
                </div>
            </div>

            <!-- Action Buttons -->
            @auth
                @if (auth()->id() !== $user->id)
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <form method="POST" action="{{ route('frontend.messages.start', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-chat-dots"></i> Send Message
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <div style="margin-top: 20px;">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Login to Message
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab">
            <i class="bi bi-bag"></i> Items ({{ $user->items->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ratings-tab" data-bs-toggle="tab" data-bs-target="#ratings" type="button" role="tab">
            <i class="bi bi-star"></i> Reviews ({{ $user->ratingsReceived->count() }})
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Items Tab -->
    <div class="tab-pane fade show active" id="items" role="tabpanel">
        @if ($user->items->count() > 0)
            <div class="row">
                @foreach ($user->items as $item)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div style="height: 200px; background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #dee2e6;">
                                @if ($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}"
                                        style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                @else
                                    <i class="bi bi-image" style="font-size: 3rem; color: #0d6efd; opacity: 0.3;"></i>
                                @endif
                            </div>
                            <div class="card-body">
                                <h5 class="card-title" style="color: #333; font-weight: 600;">
                                    {{ Str::limit($item->title, 25) }}
                                </h5>
                                <p class="card-text text-muted small">
                                    {{ Str::limit($item->description, 50) }}
                                </p>
                                @if ($item->price)
                                    <div style="color: #0d6efd; font-weight: 700; margin: 10px 0;">
                                        ৳{{ $item->price }}
                                    </div>
                                @endif
                                <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-primary btn-sm w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px;">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                <p class="text-muted">No items listed yet</p>
            </div>
        @endif
    </div>

    <!-- Ratings Tab -->
    <div class="tab-pane fade" id="ratings" role="tabpanel">
        @if ($user->ratingsReceived->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    @foreach ($user->ratingsReceived as $rating)
                        <div class="card mb-3">
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <h6 style="color: #333; font-weight: 600; margin-bottom: 3px;">
                                            {{ $rating->rater->name }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $rating->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div style="color: #ffc107;">
                                        @for ($i = 0; $i < $rating->rating; $i++)
                                            <i class="bi bi-star-fill"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if ($rating->comment)
                                    <p style="color: #666; margin: 10px 0;">{{ $rating->comment }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Rating Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 style="color: #333; font-weight: 600; margin-bottom: 15px;">Rating Summary</h6>
                            <h3 style="color: #0d6efd; font-weight: 700; margin-bottom: 5px;">
                                {{ round($user->averageRating(), 1) }} / 5.0
                            </h3>
                            <small class="text-muted d-block mb-15px;">
                                Based on {{ $user->ratingsReceived->count() }} ratings
                            </small>

                            @php
                                $distribution = [
                                    5 => $user->ratingsReceived->where('rating', 5)->count(),
                                    4 => $user->ratingsReceived->where('rating', 4)->count(),
                                    3 => $user->ratingsReceived->where('rating', 3)->count(),
                                    2 => $user->ratingsReceived->where('rating', 2)->count(),
                                    1 => $user->ratingsReceived->where('rating', 1)->count(),
                                ];
                            @endphp

                            @foreach ([5, 4, 3, 2, 1] as $stars)
                                <div class="d-flex align-items-center mb-2">
                                    <small style="width: 30px; font-weight: 600;">{{ $stars }}★</small>
                                    <div class="progress" style="flex: 1; height: 6px; margin: 0 10px;">
                                        @php
                                            $total = $user->ratingsReceived->count();
                                            $percent = $total > 0 ? ($distribution[$stars] / $total) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar" style="width: {{ $percent }}%;"></div>
                                    </div>
                                    <small style="width: 30px; text-align: right; color: #666;">
                                        {{ $distribution[$stars] }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 40px;">
                <i class="bi bi-star" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                <p class="text-muted">No ratings yet</p>
            </div>
        @endif
    </div>
</div>

@endsection
