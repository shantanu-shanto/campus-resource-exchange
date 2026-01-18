@extends('layouts.app')

@section('title', $item->title . ' - Campus Resource Exchange')

@section('content')

<div class="row">
    <!-- Item Details Left -->
    <div class="col-lg-7 mb-4 mb-lg-0">
        <!-- Item Image -->
        <div style="background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 20px; min-height: 400px; display: flex; align-items: center; justify-content: center;">
            @if ($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}"
                    style="max-width: 100%; max-height: 100%; object-fit: contain;">
            @else
                <div>
                    <i class="bi bi-image" style="font-size: 6rem; color: #0d6efd; opacity: 0.3; display: block; margin-bottom: 20px;"></i>
                    <p class="text-muted">No image available</p>
                </div>
            @endif
        </div>

        <!-- Item Info -->
        <div class="card">
            <div class="card-body">
                <h2 style="color: #333; font-weight: 700; margin-bottom: 15px;">{{ $item->title }}</h2>

                <!-- Badges -->
                <div class="mb-3">
                    @if ($item->status === 'available')
                        <span class="badge bg-success">Available</span>
                    @elseif ($item->status === 'borrowed')
                        <span class="badge bg-warning">Borrowed</span>
                    @else
                        <span class="badge bg-secondary">Sold</span>
                    @endif

                    @if ($item->availability_mode === 'lend')
                        <span class="badge bg-info">Lend Only</span>
                    @elseif ($item->availability_mode === 'sell')
                        <span class="badge bg-danger">Sell Only</span>
                    @else
                        <span class="badge bg-primary">Lend & Sell</span>
                    @endif
                </div>

                <!-- Description -->
                <h5 style="color: #333; font-weight: 600; margin-top: 25px; margin-bottom: 10px;">Description</h5>
                <p style="color: #666; line-height: 1.8;">{{ $item->description }}</p>

                <!-- Details Grid -->
                <h5 style="color: #333; font-weight: 600; margin-top: 25px; margin-bottom: 15px;">Item Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Pickup Location</small>
                        <p style="color: #333; font-weight: 600; margin: 5px 0;">
                            <i class="bi bi-geo-alt"></i> {{ $item->pickup_location }}
                        </p>
                    </div>
                    @if ($item->lending_duration_days)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Lending Duration</small>
                            <p style="color: #333; font-weight: 600; margin: 5px 0;">
                                <i class="bi bi-calendar"></i> Up to {{ $item->lending_duration_days }} days
                            </p>
                        </div>
                    @endif
                    @if ($item->price)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Price</small>
                            <p style="color: #0d6efd; font-weight: 700; font-size: 1.2rem; margin: 5px 0;">
                                ৳{{ $item->price }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Owner & Action Right -->
    <div class="col-lg-5">


    <!-- Owner & Action Right -->
<div class="col-lg-5">
    <!-- Owner Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-person"></i> Lender Information
        </div>
        <div class="card-body">
            @if ($item->owner)
                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <i class="bi bi-person-circle" style="font-size: 3rem; color: #0d6efd;"></i>
                    <div style="flex: 1;">
                        <h5 style="color: #333; font-weight: 600; margin-bottom: 5px;">
                            {{ $item->owner->name }}
                        </h5>
                        <small class="text-muted">{{ $item->owner->email }}</small>
                        <div style="margin-top: 10px;">
                            <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                            <strong>{{ number_format($item->owner->averageRating(), 1) }} / 5.0</strong>
                            <small class="text-muted">({{ $item->owner->ratingsReceived?->count() ?? 0 }} ratings)</small>
                        </div>
                    </div>
                </div>

                <div class="row text-center mb-3">
                    <div class="col-6">
                        <small class="text-muted">Items Listed</small>
                        <h6 style="color: #333; font-weight: 700; margin: 5px 0;">
                            {{ $item->owner->items?->count() ?? 0 }}
                        </h6>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Member Since</small>
                        <h6 style="color: #333; font-weight: 700; margin: 5px 0;">
                            {{ $item->owner->created_at?->format('M Y') ?? 'N/A' }}
                        </h6>
                    </div>
                </div>

                <a href="{{ route('frontend.profile.show', $item->owner) }}" class="btn btn-outline-primary w-100">
                    <i class="bi bi-eye"></i> View Profile
                </a>
            @else
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Owner information unavailable</strong>
                    <p class="mb-0 mt-2">The item owner's profile is no longer available.</p>
                </div>
            @endif
        </div>
    </div>

        

        <!-- Action Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-hand-index"></i> Actions
            </div>
            <div class="card-body">
                @auth
                    @if (auth()->id() === $item->user_id)
                        <!-- Owner Actions -->
                        <div class="alert alert-info mb-3">
                            <small>This is your item</small>
                        </div>
                        <a href="{{ route('frontend.items.edit', $item) }}" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-pencil"></i> Edit Item
                        </a>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash"></i> Delete Item
                        </button>
                    @else
                        <!-- Borrower Actions -->
                        @if ($item->status === 'available')
                            @if ($item->availability_mode !== 'sell')
                                <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#borrowModal">
                                    <i class="bi bi-bookmark"></i> Request to Borrow
                                </button>
                            @endif
                            @if ($item->availability_mode !== 'lend')
                                <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#buyModal">
                                    <i class="bi bi-bag-check"></i> Request to Buy
                                </button>
                            @endif
                        @else
                            <div class="alert alert-warning mb-0">
                                <small>This item is not currently available</small>
                            </div>
                        @endif

                        <!-- Message Button -->
                        <form method="POST" action="{{ route('frontend.messages.start', $item->owner) }}" style="margin-top: 15px;">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-chat-dots"></i> Send Message
                            </button>
                        </form>
                    @endif
                @else
                    <div class="alert alert-info mb-3">
                        <small>Sign in to request or message</small>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-person-plus"></i> Sign Up
                    </a>
                @endauth
            </div>
        </div>

        <!-- Ratings Card -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-star"></i> Ratings & Reviews
            </div>
            <div class="card-body">
                @if ($item->ratings->count() > 0)
                    <div style="text-align: center; margin-bottom: 15px;">
                        <h4 style="color: #0d6efd; font-weight: 700; margin-bottom: 5px;">
                            {{ round($item->ratings->avg('rating'), 1) }} / 5.0
                        </h4>
                        <small class="text-muted">Based on {{ $item->ratings->count() }} ratings</small>
                    </div>

                    @foreach ($item->ratings->take(3) as $rating)
                        <div style="padding-bottom: 12px; border-bottom: 1px solid #dee2e6;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <strong style="font-size: 0.9rem; color: #333;">
                                    {{ $rating->rater->name }}
                                </strong>
                                <small style="color: #ffc107;">
                                    @for ($i = 0; $i < $rating->rating; $i++)
                                        <i class="bi bi-star-fill"></i>
                                    @endfor
                                </small>
                            </div>
                            @if ($rating->comment)
                                <small style="color: #666;">{{ Str::limit($rating->comment, 100) }}</small>
                            @endif
                        </div>
                    @endforeach

                    @if ($item->ratings->count() > 3)
                        <a href="#reviews" class="btn btn-sm btn-outline-primary w-100 mt-3">
                            View All Reviews
                        </a>
                    @endif
                @else
                    <p class="text-muted text-center mb-0">No ratings yet</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@auth
    @if (auth()->id() !== $item->user_id && $item->status === 'available')
        <!-- Borrow Modal -->
        <div class="modal fade" id="borrowModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Request to Borrow</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('frontend.items.request', $item) }}">
                        @csrf
                        <input type="hidden" name="type" value="lend">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Message to Lender</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="Explain why you need this item..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Buy Modal -->
        <div class="modal fade" id="buyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Request to Buy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('frontend.items.request', $item) }}">
                        @csrf
                        <input type="hidden" name="type" value="sell">
                        <div class="modal-body">
                            <p style="color: #666; margin-bottom: 15px;">
                                Price: <strong style="font-size: 1.2rem; color: #0d6efd;">৳{{ $item->price }}</strong>
                            </p>
                            <div class="mb-3">
                                <label class="form-label">Message to Seller</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="Any questions or negotiation terms..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif (auth()->id() === $item->user_id)
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-danger">
                        <h5 class="modal-title">Delete Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p style="color: #666;">Are you sure you want to delete this item? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="{{ route('frontend.items.destroy', $item) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth

@endsection
