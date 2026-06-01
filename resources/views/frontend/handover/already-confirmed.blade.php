@extends('layouts.app')

@section('title', 'Already Confirmed - UniShare')

@section('content')

<div style="max-width: 480px; margin: 60px auto; text-align: center;">

    <div style="width: 80px; height: 80px; background: #d4edda; border-radius: 50%;
                display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
        <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: #28a745;"></i>
    </div>

    <h2 style="font-weight: 700; color: #28a745; margin-bottom: 12px;">Already Confirmed</h2>

    <p class="text-muted mb-4">
        Both parties have already confirmed this handover.
        The transaction has been updated automatically.
    </p>

    <div class="card mb-4">
        <div class="card-body" style="font-size: 0.9rem;">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Item</span>
                <span class="fw-bold">{{ $transaction->item->title }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Status</span>
                <span class="badge bg-{{ $transaction->getStatusBadgeColor() }}">
                    {{ $transaction->getStatusLabel() }}
                </span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Handover type</span>
                <span>{{ $verification->getTypeLabel() }}</span>
            </div>
        </div>
    </div>

    <a href="{{ route('frontend.transactions.show', $transaction) }}"
       class="btn btn-success">
        <i class="bi bi-arrow-right me-2"></i>View Transaction
    </a>

</div>

@endsection