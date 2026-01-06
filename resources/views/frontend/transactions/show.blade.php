@extends('layouts.app')

@section('title', 'Transaction Details - Campus Resource Exchange')

@section('content')

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <!-- Transaction Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                    <div>
                        <h2 style="color: #333; font-weight: 700; margin-bottom: 5px;">
                            {{ $transaction->item->title }}
                        </h2>
                        <p class="text-muted">
                            {{ ucfirst($transaction->type) }} Transaction
                        </p>
                    </div>
                    <div style="text-align: right;">
                        @if ($transaction->status === 'pending')
                            <span class="badge bg-warning" style="font-size: 0.9rem; padding: 8px 12px;">Pending</span>
                        @elseif ($transaction->status === 'active')
                            <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 12px;">Active</span>
                        @elseif ($transaction->status === 'completed')
                            <span class="badge bg-info" style="font-size: 0.9rem; padding: 8px 12px;">Completed</span>
                        @else
                            <span class="badge bg-secondary" style="font-size: 0.9rem; padding: 8px 12px;">Cancelled</span>
                        @endif
                    </div>
                </div>

                <!-- Item Info -->
                <div style="display: flex; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6;">
                    <div style="width: 100px; height: 100px; background: #f0f4ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if ($transaction->item->image_path)
                            <img src="{{ asset('storage/' . $transaction->item->image_path) }}" alt="{{ $transaction->item->title }}"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        @else
                            <i class="bi bi-image" style="font-size: 2.5rem; color: #0d6efd; opacity: 0.3;"></i>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <p class="text-muted small mb-2">Item Details</p>
                        <p style="color: #333; font-weight: 600; margin-bottom: 5px;">
                            {{ $transaction->item->title }}
                        </p>
                        <small class="text-muted d-block mb-3">
                            Category: {{ ucfirst(str_replace('-', ' ', $transaction->item->category)) }}
                        </small>
                        <small class="text-muted d-block">
                            Condition: {{ ucfirst($transaction->item->condition) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Timeline
            </div>
            <div class="card-body">
                @php
                    $otherParty = auth()->id() === $transaction->item->user_id
                        ? $transaction->borrower
                        : $transaction->item->owner;
                @endphp

                <div style="position: relative; padding: 20px 0; padding-left: 40px;">
                    <!-- Requested -->
                    <div style="position: absolute; left: 0; top: 25px; width: 20px; height: 20px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                    </div>
                    <div style="margin-bottom: 30px;">
                        <p style="color: #333; font-weight: 600; margin-bottom: 3px;">Request Sent</p>
                        <small class="text-muted">{{ $transaction->created_at->format('M d, Y - h:i A') }}</small>
                    </div>

                    <!-- Approved -->
                    @if ($transaction->status !== 'pending' && $transaction->status !== 'cancelled')
                        <div style="position: absolute; left: 0; top: 105px; width: 20px; height: 20px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                        </div>
                        <div style="margin-bottom: 30px;">
                            <p style="color: #333; font-weight: 600; margin-bottom: 3px;">Request Approved</p>
                            <small class="text-muted">{{ $transaction->approved_at?->format('M d, Y - h:i A') ?? 'Processing...' }}</small>
                        </div>
                    @endif

                    <!-- Active -->
                    @if ($transaction->status !== 'pending' && $transaction->status !== 'cancelled')
                        <div style="position: absolute; left: 0; top: 185px; width: 20px; height: 20px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                        </div>
                        <div style="margin-bottom: 30px;">
                            <p style="color: #333; font-weight: 600; margin-bottom: 3px;">Transaction Started</p>
                            <small class="text-muted">{{ $transaction->start_date?->format('M d, Y - h:i A') ?? 'Processing...' }}</small>
                        </div>
                    @endif

                    <!-- Completed -->
                    @if ($transaction->status === 'completed')
                        <div style="position: absolute; left: 0; top: 265px; width: 20px; height: 20px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                        </div>
                        <div>
                            <p style="color: #333; font-weight: 600; margin-bottom: 3px;">Transaction Completed</p>
                            <small class="text-muted">{{ $transaction->return_date?->format('M d, Y - h:i A') ?? $transaction->completed_at?->format('M d, Y - h:i A') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Transaction Details
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Transaction Type</small>
                        <p style="color: #333; font-weight: 600;">{{ ucfirst($transaction->type) }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Transaction ID</small>
                        <p style="color: #333; font-weight: 600; font-family: monospace;">
                            {{ $transaction->id }}
                        </p>
                    </div>
                </div>

                @if ($transaction->type === 'lend')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Start Date</small>
                            <p style="color: #333; font-weight: 600;">
                                {{ $transaction->start_date?->format('M d, Y') ?? 'Pending' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Due Date</small>
                            <p style="color: #333; font-weight: 600;">
                                {{ $transaction->due_date->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                    @if ($transaction->return_date)
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Returned</small>
                                <p style="color: #333; font-weight: 600;">
                                    {{ $transaction->return_date->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Price</small>
                            <p style="color: #0d6efd; font-weight: 700; font-size: 1.1rem;">
                                ৳{{ $transaction->item->price }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Posted</small>
                            <p style="color: #333; font-weight: 600;">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Request Message -->
        @if ($transaction->message)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-chat-dots"></i> Request Message
                </div>
                <div class="card-body">
                    <p style="color: #666; line-height: 1.6;">
                        {{ $transaction->message }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Other Party Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person"></i>
                {{ auth()->id() === $transaction->item->user_id ? 'Borrower' : 'Lender' }}
            </div>
            <div class="card-body text-center">
                <i class="bi bi-person-circle" style="font-size: 3rem; color: #0d6efd; display: block; margin-bottom: 15px;"></i>
                <h5 style="color: #333; font-weight: 600; margin-bottom: 5px;">
                    {{ $otherParty->name }}
                </h5>
                <p class="text-muted small mb-3">{{ $otherParty->email }}</p>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <small class="text-muted d-block mb-2">Rating</small>
                    <h4 style="color: #ffc107; font-weight: 700; margin-bottom: 5px;">
                        {{ round($otherParty->averageRating(), 1) }} / 5.0
                    </h4>
                    <small class="text-muted">{{ $otherParty->ratingsReceived->count() }} ratings</small>
                </div>

                <a href="{{ route('frontend.profile.show', $otherParty) }}" class="btn btn-outline-primary w-100 btn-sm mb-2">
                    <i class="bi bi-eye"></i> View Profile
                </a>

                <form method="POST" action="{{ route('frontend.messages.start', $otherParty) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 btn-sm">
                        <i class="bi bi-chat-dots"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Actions Card -->
        @if ($transaction->status === 'pending' && auth()->id() === $transaction->item->user_id)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-hand-index"></i> Actions
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('frontend.transactions.approve', $transaction) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 btn-sm">
                            <i class="bi bi-check-circle"></i> Approve Request
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle"></i> Reject Request
                    </button>
                </div>
            </div>
        @elseif ($transaction->status === 'active')
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-hand-index"></i> Actions
                </div>
                <div class="card-body">
                    @if (auth()->id() === $transaction->borrower_id && $transaction->type === 'lend')
                        <button type="button" class="btn btn-primary w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal">
                            <i class="bi bi-box-arrow-in"></i> Return Item
                        </button>
                    @elseif (auth()->id() === $transaction->item->user_id && $transaction->type === 'lend')
                        <p class="text-muted small mb-2">Waiting for return...</p>
                    @endif
                </div>
            </div>
        @elseif ($transaction->status === 'completed')
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-star"></i> Rate This Transaction
                </div>
                <div class="card-body">
                    @php
                        $myRating = $transaction->ratings->where('rater_id', auth()->id())->first();
                    @endphp

                    @if ($myRating)
                        <div style="text-align: center;">
                            <p class="text-muted small mb-2">Your Rating</p>
                            <div style="font-size: 1.5rem; color: #ffc107; margin-bottom: 10px;">
                                @for ($i = 0; $i < $myRating->rating; $i++)
                                    <i class="bi bi-star-fill"></i>
                                @endfor
                            </div>
                            @if ($myRating->comment)
                                <p class="text-muted small">{{ $myRating->comment }}</p>
                            @endif
                        </div>
                    @else
                        <button type="button" class="btn btn-primary w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#ratingModal">
                            <i class="bi bi-star"></i> Leave Rating
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Pickup Location -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-geo-alt"></i> Pickup Location
            </div>
            <div class="card-body">
                <p style="color: #333; font-weight: 600; margin-bottom: 5px;">
                    {{ $transaction->item->pickup_location }}
                </p>
                <small class="text-muted">
                    Preferred times: {{ ucfirst($transaction->item->meeting_times ?? 'Flexible') }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('frontend.transactions.reject', $transaction) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Reason for Rejection (Optional)</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Let them know why..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Return Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('frontend.transactions.return', $transaction) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Confirm that you're returning this item in good condition.</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmReturn" required>
                        <label class="form-check-label" for="confirmReturn">
                            I confirm that this item is being returned
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate {{ $otherParty->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('frontend.transactions.rate', $transaction) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating *</label>
                        <div style="font-size: 2rem; margin-bottom: 20px;" id="ratingStars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star" style="color: #ddd; cursor: pointer; margin-right: 10px;" data-rating="{{ $i }}"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comment (Optional)</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Rating</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Star rating interaction
    const stars = document.querySelectorAll('#ratingStars i');
    const ratingValue = document.getElementById('ratingValue');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            ratingValue.value = rating;

            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.remove('bi-star');
                    s.classList.add('bi-star-fill');
                    s.style.color = '#ffc107';
                } else {
                    s.classList.remove('bi-star-fill');
                    s.classList.add('bi-star');
                    s.style.color = '#ddd';
                }
            });
        });

        star.addEventListener('mouseover', function() {
            const rating = this.dataset.rating;
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });

    document.getElementById('ratingStars').addEventListener('mouseleave', function() {
        const currentRating = document.getElementById('ratingValue').value;
        stars.forEach((star, index) => {
            if (index < currentRating) {
                star.style.color = '#ffc107';
            } else {
                star.style.color = '#ddd';
            }
        });
    });
</script>
@endsection
