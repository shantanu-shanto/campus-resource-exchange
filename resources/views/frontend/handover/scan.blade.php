@extends('layouts.app')

@section('title', 'Confirm Handover - UniShare')

@section('content')

<div style="max-width: 500px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 24px; text-align: center;">
        <div style="width: 64px; height: 64px; background: #e7f1ff; border-radius: 50%;
                    display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="bi bi-qr-code-scan" style="font-size: 2rem; color: #0d6efd;"></i>
        </div>
        <h1 class="page-title">
            {{ $verification->type === 'pickup' ? 'Confirm Item Pickup' : 'Confirm Item Return' }}
        </h1>
        <p class="text-muted">
            {{ $verification->type === 'pickup'
                ? 'Confirm you are physically receiving this item right now.'
                : 'Confirm you are physically returning this item right now.' }}
        </p>
    </div>

    {{-- Item Details Card --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-box-seam me-2"></i>Item Details
        </div>
        <div class="card-body">
            <table class="table table-borderless mb-0" style="font-size: 0.9rem;">
                <tr>
                    <td style="color: #666; width: 40%;">Item</td>
                    <td style="font-weight: 600;">{{ $transaction->item->title }}</td>
                </tr>
                <tr>
                    <td style="color: #666;">Owner</td>
                    <td>{{ $transaction->owner->name }}</td>
                </tr>
                <tr>
                    <td style="color: #666;">Borrower</td>
                    <td>{{ $transaction->borrower->name }}</td>
                </tr>
                @if ($verification->type === 'pickup' && $transaction->due_date)
                <tr>
                    <td style="color: #666;">Due Back</td>
                    <td style="font-weight: 600; color: #dc3545;">
                        {{ $transaction->due_date->format('M d, Y') }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="color: #666;">Pickup Location</td>
                    <td>{{ $transaction->item->pickup_location }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Who has confirmed so far --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-person-check me-2"></i>Confirmation Status
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-weight: 600;">
                    <i class="bi bi-person-circle me-2" style="color: #0d6efd;"></i>
                    {{ $transaction->owner->name }}
                    @if ($isOwner) <span class="text-muted fw-normal">(you)</span> @endif
                </span>
                @if ($verification->ownerHasConfirmed())
                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Confirmed</span>
                @else
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Waiting</span>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span style="font-weight: 600;">
                    <i class="bi bi-person-circle me-2" style="color: #6f42c1;"></i>
                    {{ $transaction->borrower->name }}
                    @if ($isBorrower) <span class="text-muted fw-normal">(you)</span> @endif
                </span>
                @if ($verification->borrowerHasConfirmed())
                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Confirmed</span>
                @else
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Waiting</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Token expiry warning --}}
    <div class="alert alert-warning d-flex align-items-center mb-4" style="font-size: 0.88rem;">
        <i class="bi bi-clock me-2" style="font-size: 1.1rem;"></i>
        <div>
            This QR token expires at
            <strong>{{ $verification->expires_at->format('h:i A') }}</strong>.
            Confirm before it expires.
        </div>
    </div>

    {{-- Action Area --}}
    @if ($alreadyConfirmed)
        <div class="alert alert-success text-center">
            <i class="bi bi-check-circle-fill me-2" style="font-size: 1.3rem;"></i>
            <strong>You have already confirmed.</strong>
            <p class="mb-0 mt-1 text-muted" style="font-size: 0.88rem;">
                Waiting for the other party to scan and confirm.
            </p>
        </div>
    @else
        <div class="card border-primary">
            <div class="card-body text-center py-4">
                <p style="font-size: 0.9rem; color: #555; margin-bottom: 20px;">
                    By confirming, you acknowledge the physical
                    {{ $verification->type === 'pickup' ? 'handover' : 'return' }}
                    of <strong>{{ $transaction->item->title }}</strong> is happening right now.
                </p>
                <form method="POST"
                      action="{{ route('frontend.handover.confirm', $verification->token) }}">
                    @csrf
                    <button type="submit"
                            class="btn btn-primary btn-lg w-100"
                            style="font-weight: 700; padding: 14px;">
                        <i class="bi bi-check-circle me-2"></i>
                        Confirm {{ $verification->type === 'pickup' ? 'Pickup' : 'Return' }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('frontend.transactions.show', $transaction) }}"
           class="text-muted" style="font-size: 0.85rem; text-decoration: none;">
            <i class="bi bi-arrow-left me-1"></i> Back to transaction
        </a>
    </div>

</div>

@endsection