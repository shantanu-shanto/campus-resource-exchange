@extends('layouts.app')

@section('title', 'QR Expired - UniShare')

@section('content')

<div style="max-width: 480px; margin: 60px auto; text-align: center;">

    <div style="width: 80px; height: 80px; background: #f8d7da; border-radius: 50%;
                display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
        <i class="bi bi-clock-history" style="font-size: 2.5rem; color: #dc3545;"></i>
    </div>

    <h2 style="font-weight: 700; color: #dc3545; margin-bottom: 12px;">QR Code Expired</h2>

    <p class="text-muted mb-4">
        This QR code was valid for 15 minutes and has now expired.
        The item owner or borrower needs to generate a new one.
    </p>

    <div class="card mb-4">
        <div class="card-body" style="font-size: 0.9rem;">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Transaction</span>
                <span class="fw-600">#{{ $verification->transaction_id }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Type</span>
                <span>{{ $verification->getTypeLabel() }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Expired at</span>
                <span>{{ $verification->expires_at->format('h:i A, M d') }}</span>
            </div>
        </div>
    </div>

    <a href="{{ route('frontend.transactions.show', $verification->transaction) }}"
       class="btn btn-primary">
        <i class="bi bi-arrow-left me-2"></i>Back to Transaction
    </a>

</div>

@endsection