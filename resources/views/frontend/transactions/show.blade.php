@extends('layouts.app')

@section('title', 'Transaction Details - Campus Resource Exchange')

@section('content')

<div class="row">
    {{-- ── Main Content ────────────────────────────────────────── --}}
    <div class="col-lg-8 mb-4 mb-lg-0">

        {{-- Transaction Header --}}
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
                        <span class="badge bg-{{ $transaction->getStatusBadgeColor() }}"
                              style="font-size: 0.9rem; padding: 8px 12px;">
                            {{ $transaction->getStatusLabel() }}
                        </span>
                    </div>
                </div>

                {{-- Item Info --}}
                <div style="display: flex; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6;">
                    <div style="width: 100px; height: 100px; background: #f0f4ff; border-radius: 8px;
                                display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if ($transaction->item->image_path)
                            <img src="{{ asset('storage/' . $transaction->item->image_path) }}"
                                 alt="{{ $transaction->item->title }}"
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
                        <small class="text-muted d-block mb-1">
                            <i class="bi bi-geo-alt me-1"></i>{{ $transaction->item->pickup_location }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-person me-1"></i>Owner: {{ $transaction->owner->name }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- QR Handover Status Banner --}}
        {{-- Shown when transaction is in one of the two awaiting states --}}
        @if ($transaction->status === 'awaiting_handover' || $transaction->status === 'awaiting_return')
            @php
                $activeVerification = $transaction->activeHandoverVerification;
            @endphp
            <div class="card mb-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 48px; height: 48px; background: #e7f1ff; border-radius: 50%;
                                    display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-qr-code-scan" style="font-size: 1.4rem; color: #0d6efd;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: #0d6efd; margin-bottom: 2px;">
                                QR Handover In Progress
                            </div>
                            <small class="text-muted">
                                @if ($transaction->status === 'awaiting_handover')
                                    Waiting for both parties to scan the pickup QR code.
                                @else
                                    Waiting for both parties to scan the return QR code.
                                @endif
                            </small>
                        </div>
                        @if ($activeVerification)
                            <div class="text-end" style="flex-shrink: 0;">
                                <small class="text-muted d-block">Expires at</small>
                                <strong style="font-size: 0.9rem;">
                                    {{ $activeVerification->expires_at->format('h:i A') }}
                                </strong>
                            </div>
                        @endif
                    </div>

                    {{-- Show who has confirmed --}}
                    @if ($activeVerification)
                        <div class="d-flex gap-3 mt-3 pt-3" style="border-top: 1px solid #dee2e6;">
                            <div class="d-flex align-items-center gap-2">
                                @if ($activeVerification->ownerHasConfirmed())
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>{{ $transaction->owner->name }} confirmed</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>{{ $transaction->owner->name }} waiting</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if ($activeVerification->borrowerHasConfirmed())
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>{{ $transaction->borrower->name }} confirmed</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>{{ $transaction->borrower->name }} waiting</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Timeline --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Timeline
            </div>
            <div class="card-body">
                <div style="position: relative; padding: 10px 0; padding-left: 40px;">

                    {{-- Request Created --}}
                    <div style="position: relative; margin-bottom: 28px;">
                        <div style="position: absolute; left: -40px; top: 2px; width: 20px; height: 20px;
                                    background: #0d6efd; border-radius: 50%;
                                    display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                        </div>
                        <p style="color: #333; font-weight: 600; margin-bottom: 2px;">Request Sent</p>
                        <small class="text-muted">{{ $transaction->created_at->format('M d, Y - h:i A') }}</small>
                    </div>

                    {{-- Awaiting Pickup QR --}}
                    @if (in_array($transaction->status, ['awaiting_handover', 'active', 'awaiting_return', 'completed', 'late']))
                        <div style="position: relative; margin-bottom: 28px;">
                            <div style="position: absolute; left: -40px; top: 2px; width: 20px; height: 20px;
                                        background: #0d6efd; border-radius: 50%;
                                        display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="bi bi-qr-code" style="font-size: 0.6rem;"></i>
                            </div>
                            <p style="color: #333; font-weight: 600; margin-bottom: 2px;">Pickup QR Generated</p>
                            <small class="text-muted">
                                {{ $transaction->pickupVerification?->created_at?->format('M d, Y - h:i A') ?? '—' }}
                            </small>
                        </div>
                    @endif

                    {{-- Active --}}
                    @if (in_array($transaction->status, ['active', 'awaiting_return', 'completed', 'late']))
                        <div style="position: relative; margin-bottom: 28px;">
                            <div style="position: absolute; left: -40px; top: 2px; width: 20px; height: 20px;
                                        background: #0d6efd; border-radius: 50%;
                                        display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                            </div>
                            <p style="color: #333; font-weight: 600; margin-bottom: 2px;">Handover Confirmed — Item Active</p>
                            <small class="text-muted">
                                {{ $transaction->start_date?->format('M d, Y') ?? '—' }}
                                @if ($transaction->due_date)
                                    &nbsp;·&nbsp; Due: <strong>{{ $transaction->due_date->format('M d, Y') }}</strong>
                                @endif
                            </small>
                        </div>
                    @endif

                    {{-- Awaiting Return QR --}}
                    @if (in_array($transaction->status, ['awaiting_return', 'completed', 'late']))
                        <div style="position: relative; margin-bottom: 28px;">
                            <div style="position: absolute; left: -40px; top: 2px; width: 20px; height: 20px;
                                        background: #6f42c1; border-radius: 50%;
                                        display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="bi bi-qr-code" style="font-size: 0.6rem;"></i>
                            </div>
                            <p style="color: #333; font-weight: 600; margin-bottom: 2px;">Return QR Generated</p>
                            <small class="text-muted">
                                {{ $transaction->returnVerification?->created_at?->format('M d, Y - h:i A') ?? '—' }}
                            </small>
                        </div>
                    @endif

                    {{-- Completed / Late --}}
                    @if (in_array($transaction->status, ['completed', 'late']))
                        <div style="position: relative;">
                            <div style="position: absolute; left: -40px; top: 2px; width: 20px; height: 20px;
                                        background: {{ $transaction->status === 'late' ? '#dc3545' : '#28a745' }};
                                        border-radius: 50%;
                                        display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="bi bi-check" style="font-size: 0.7rem;"></i>
                            </div>
                            <p style="color: #333; font-weight: 600; margin-bottom: 2px;">
                                {{ $transaction->status === 'late' ? 'Returned Late' : 'Return Confirmed — Completed' }}
                            </p>
                            <small class="text-muted">
                                {{ $transaction->return_date?->format('M d, Y') ?? '—' }}
                            </small>
                        </div>
                    @endif

                    {{-- Cancelled --}}
                    @if ($transaction->status === 'cancelled')
                        <div style="position: relative;">
                            <div style="position: absolute; left: -40px; top: 2px; width: 20px; height: 20px;
                                        background: #6c757d; border-radius: 50%;
                                        display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="bi bi-x" style="font-size: 0.7rem;"></i>
                            </div>
                            <p style="color: #333; font-weight: 600; margin-bottom: 2px;">Cancelled</p>
                            <small class="text-muted">{{ $transaction->updated_at->format('M d, Y - h:i A') }}</small>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Transaction Details --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Transaction Details
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
                            #{{ $transaction->id }}
                        </p>
                    </div>
                </div>

                @if ($transaction->type === 'lend' || $transaction->type === 'share')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Start Date</small>
                            <p style="color: #333; font-weight: 600;">
                                {{ $transaction->start_date?->format('M d, Y') ?? 'Pending handover' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Due Date</small>
                            <p style="color: #333; font-weight: 600;">
                                {{ $transaction->due_date?->format('M d, Y') ?? '—' }}
                            </p>
                        </div>
                    </div>
                    @if ($transaction->return_date)
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Returned On</small>
                                <p style="color: #333; font-weight: 600;">
                                    {{ $transaction->return_date->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Sale Price</small>
                            <p style="color: #0d6efd; font-weight: 700; font-size: 1.1rem;">
                                ৳{{ number_format($transaction->final_price ?? $transaction->item->price, 2) }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Date</small>
                            <p style="color: #333; font-weight: 600;">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Penalties --}}
                @if ($transaction->penalties->count() > 0)
                    <div class="mt-3 pt-3" style="border-top: 1px solid #dee2e6;">
                        <small class="text-muted d-block mb-2">Penalties</small>
                        @foreach ($transaction->penalties as $penalty)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size: 0.88rem;">
                                    {{ $penalty->days_late }} day(s) late
                                </span>
                                <span class="badge bg-{{ $penalty->status === 'paid' ? 'success' : 'danger' }}">
                                    ৳{{ number_format($penalty->amount, 2) }} — {{ ucfirst($penalty->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Other Party Info --}}
        @php
            $otherParty = $isOwner ? $transaction->borrower : $transaction->owner;
        @endphp
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>{{ $isOwner ? 'Borrower' : 'Owner' }}
            </div>
            <div class="card-body text-center">
                <i class="bi bi-person-circle" style="font-size: 3rem; color: #0d6efd; display: block; margin-bottom: 15px;"></i>
                <h5 style="color: #333; font-weight: 600; margin-bottom: 5px;">{{ $otherParty->name }}</h5>
                <p class="text-muted small mb-3">{{ $otherParty->email }}</p>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <small class="text-muted d-block mb-1">Rating</small>
                    <h4 style="color: #ffc107; font-weight: 700; margin-bottom: 3px;">
                        {{ round($otherParty->averageRating(), 1) }} / 5.0
                    </h4>
                    <small class="text-muted">{{ $otherParty->ratingsReceived()->count() }} ratings</small>
                </div>

                <a href="{{ route('frontend.profile.show', $otherParty) }}"
                   class="btn btn-outline-primary w-100 btn-sm mb-2">
                    <i class="bi bi-eye me-1"></i>View Profile
                </a>
                <form method="POST" action="{{ route('frontend.messages.start', $otherParty) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 btn-sm">
                        <i class="bi bi-chat-dots me-1"></i>Send Message
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Actions Card ─────────────────────────────────────── --}}

        {{-- PENDING: Owner generates pickup QR --}}
        @if ($transaction->status === 'pending' && $isOwner)
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-qr-code-scan me-2"></i>Actions
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Generate a QR code for the physical handover.
                        Both you and <strong>{{ $transaction->borrower->name }}</strong>
                        must scan it to activate the transaction.
                    </p>
                    <form method="POST"
                          action="{{ route('frontend.handover.generate', $transaction) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-qr-code-scan me-2"></i>Generate Pickup QR
                        </button>
                    </form>
                    <hr>
                    <button type="button"
                            class="btn btn-outline-danger w-100 btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i>Cancel Request
                    </button>
                </div>
            </div>

        {{-- PENDING: Borrower waits --}}
        @elseif ($transaction->status === 'pending' && $isBorrower)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-hourglass-split me-2"></i>Status
                </div>
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split"
                       style="font-size: 2rem; color: #ffc107; display: block; margin-bottom: 12px;"></i>
                    <p class="text-muted small mb-3">
                        Waiting for <strong>{{ $transaction->owner->name }}</strong>
                        to generate the pickup QR code.
                    </p>
                    <button type="button"
                            class="btn btn-outline-danger w-100 btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i>Cancel Request
                    </button>
                </div>
            </div>

        {{-- AWAITING HANDOVER: Show QR page link to whoever generated it --}}
        @elseif ($transaction->status === 'awaiting_handover')
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-qr-code-scan me-2"></i>Pickup QR Active
                </div>
                <div class="card-body text-center">
                    <p class="text-muted small mb-3">
                        A pickup QR code has been generated.
                        Both parties need to scan it to confirm the handover.
                    </p>
                    @if ($isOwner)
                        {{-- Owner can go back to the QR page or regenerate --}}
                        <form method="POST"
                              action="{{ route('frontend.handover.generate', $transaction) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100 btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i>Regenerate QR
                            </button>
                        </form>
                    @else
                        <p class="text-muted small">
                            Ask the owner to show you the QR code to scan.
                        </p>
                    @endif
                </div>
            </div>

        {{-- ACTIVE: Borrower generates return QR --}}
        @elseif ($transaction->status === 'active' && $isBorrower && $transaction->isLending())
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-qr-code-scan me-2"></i>Ready to Return?
                </div>
                <div class="card-body">
                    @if ($transaction->isOverdue())
                        <div class="alert alert-danger py-2 mb-3" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Overdue by {{ $transaction->daysOverdue() }} day(s).</strong>
                            A penalty will be applied on return.
                        </div>
                    @endif
                    <p class="text-muted small mb-3">
                        Generate a return QR code. Both you and
                        <strong>{{ $transaction->owner->name }}</strong>
                        must scan it to confirm the return.
                    </p>
                    <form method="POST"
                          action="{{ route('frontend.handover.generate', $transaction) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-qr-code-scan me-2"></i>Generate Return QR
                        </button>
                    </form>
                </div>
            </div>

        {{-- ACTIVE: Owner waits for return QR --}}
        @elseif ($transaction->status === 'active' && $isOwner && $transaction->isLending())
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-hourglass-split me-2"></i>Waiting for Return
                </div>
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split"
                       style="font-size: 2rem; color: #0d6efd; display: block; margin-bottom: 12px;"></i>
                    <p class="text-muted small">
                        Waiting for <strong>{{ $transaction->borrower->name }}</strong>
                        to generate the return QR code.
                    </p>
                    @if ($transaction->isOverdue())
                        <div class="alert alert-warning py-2 mt-2" style="font-size: 0.85rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Item is overdue by <strong>{{ $transaction->daysOverdue() }}</strong> day(s).
                        </div>
                    @endif
                </div>
            </div>

        {{-- ACTIVE: Sell type — owner generates QR for handover --}}
        @elseif ($transaction->status === 'active' && $transaction->isSelling())
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-qr-code-scan me-2"></i>Confirm Sale Handover
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Generate a QR to confirm the physical exchange of the item.
                    </p>
                    <form method="POST"
                          action="{{ route('frontend.handover.generate', $transaction) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-qr-code-scan me-2"></i>Generate Handover QR
                        </button>
                    </form>
                </div>
            </div>

        {{-- AWAITING RETURN: Both parties scanning --}}
        @elseif ($transaction->status === 'awaiting_return')
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-qr-code-scan me-2"></i>Return QR Active
                </div>
                <div class="card-body text-center">
                    <p class="text-muted small mb-3">
                        A return QR code has been generated.
                        Both parties need to scan it to complete the transaction.
                    </p>
                    @if ($isBorrower)
                        <form method="POST"
                              action="{{ route('frontend.handover.generate', $transaction) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100 btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i>Regenerate QR
                            </button>
                        </form>
                    @else
                        <p class="text-muted small">
                            Ask the borrower to show you the QR code to scan.
                        </p>
                    @endif
                </div>
            </div>

        {{-- COMPLETED: Rate the other party --}}
        @elseif (in_array($transaction->status, ['completed', 'late']))
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-star me-2"></i>Rate This Transaction
                </div>
                <div class="card-body">
                    @if ($userRating)
                        <div style="text-align: center;">
                            <p class="text-muted small mb-2">Your Rating</p>
                            <div style="font-size: 1.5rem; color: #ffc107; margin-bottom: 10px;">
                                @for ($i = 0; $i < $userRating->rating; $i++)
                                    <i class="bi bi-star-fill"></i>
                                @endfor
                            </div>
                            @if ($userRating->comment)
                                <p class="text-muted small">{{ $userRating->comment }}</p>
                            @endif
                            <a href="{{ route('frontend.ratings.edit', $userRating) }}"
                               class="btn btn-outline-secondary btn-sm mt-2">
                                <i class="bi bi-pencil me-1"></i>Edit Rating
                            </a>
                        </div>
                    @elseif ($canRate)
                        <p class="text-muted small mb-3">
                            How was your experience with
                            <strong>{{ $otherParty->name }}</strong>?
                        </p>
                        <a href="{{ route('frontend.ratings.create', $transaction) }}"
                           class="btn btn-primary w-100">
                            <i class="bi bi-star me-2"></i>Leave a Rating
                        </a>
                    @else
                        <p class="text-muted small text-center">Rating not available for this transaction.</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Pickup Location --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-geo-alt me-2"></i>Pickup Location
            </div>
            <div class="card-body">
                <p style="color: #333; font-weight: 600; margin-bottom: 4px;">
                    {{ $transaction->item->pickup_location }}
                </p>
                <small class="text-muted">Meet here to complete the handover.</small>
            </div>
        </div>

    </div>
</div>

{{-- ── Cancel Modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    Are you sure you want to cancel this transaction?
                    The item will become available again.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                <form method="POST"
                      action="{{ route('frontend.transactions.update', $transaction) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Rating Modal (kept for backward compatibility) ──────── --}}
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate {{ $otherParty->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    You'll be taken to the rating page to submit your review.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('frontend.ratings.create', $transaction) }}"
                   class="btn btn-primary">
                    <i class="bi bi-star me-1"></i>Rate Now
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Auto-refresh page every 10 seconds when transaction is in an awaiting state
    // so confirmation status updates without manual refresh
    @if (in_array($transaction->status, ['awaiting_handover', 'awaiting_return']))
        setTimeout(function () {
            window.location.reload();
        }, 10000);
    @endif
</script>
@endsection