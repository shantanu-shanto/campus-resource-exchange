@extends('layouts.app')

@section('title', 'Borrowing Dashboard - Campus Resource Exchange')

@section('content')

<div style="margin-bottom: 40px;">
    <h1 class="page-title">Borrowing Dashboard</h1>
    <p class="text-muted">Items you are borrowing from others</p>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
            <i class="bi bi-hourglass-split"></i> Active ({{ $activeCount }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab">
            <i class="bi bi-exclamation-triangle"></i> Overdue ({{ $overdueCount }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
            <i class="bi bi-check-circle"></i> Completed ({{ $completedCount }})
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Active Borrowing -->
    <div class="tab-pane fade show active" id="active" role="tabpanel">
        @if ($activeTransactions->count() > 0)
            <div class="row">
                @foreach ($activeTransactions as $transaction)
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <!-- Item Info -->
                                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                                    <div style="width: 80px; height: 80px; background: #f0f4ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        @if ($transaction->item->image_path)
                                            <img src="{{ asset('storage/' . $transaction->item->image_path) }}" alt="{{ $transaction->item->title }}"
                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                        @else
                                            <i class="bi bi-image" style="font-size: 2rem; color: #0d6efd; opacity: 0.3;"></i>
                                        @endif
                                    </div>
                                    <div style="flex: 1;">
                                        <h5 style="color: #333; font-weight: 600; margin-bottom: 5px;">
                                            {{ $transaction->item->title }}
                                        </h5>
                                        <small class="text-muted">From: {{ $transaction->item->owner->name }}</small>
                                        <br>
                                        <small class="text-muted">Borrowed: {{ $transaction->start_date->format('M d, Y') }}</small>
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                    @php
                                        $totalDays = $transaction->start_date->diffInDays($transaction->due_date);
                                        $passedDays = now()->diffInDays($transaction->start_date);
                                        $remainingDays = $transaction->due_date->diffInDays(now());
                                        $percent = min(($passedDays / $totalDays) * 100, 100);
                                    @endphp

                                    <div class="progress" style="height: 8px; margin-bottom: 10px;">
                                        <div class="progress-bar" style="width: {{ $percent }}%;"></div>
                                    </div>

                                    <small class="text-muted">
                                        Due: <strong>{{ $transaction->due_date->format('M d, Y') }}</strong>
                                        ({{ $remainingDays }} days left)
                                    </small>
                                </div>

                                <!-- Action -->
                                <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-primary w-100 btn-sm">
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
                <p class="text-muted">No active borrowing</p>
            </div>
        @endif
    </div>

    <!-- Overdue Borrowing -->
    <div class="tab-pane fade" id="overdue" role="tabpanel">
        @if ($overdueTransactions->count() > 0)
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle"></i> You have {{ $overdueTransactions->count() }} overdue items!
            </div>
            <div class="row">
                @foreach ($overdueTransactions as $transaction)
                    <div class="col-lg-6 mb-4">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h5 style="color: #dc3545; font-weight: 600; margin-bottom: 10px;">
                                    {{ $transaction->item->title }}
                                </h5>
                                <p class="text-muted small mb-2">From: {{ $transaction->item->owner->name }}</p>
                                <p style="color: #dc3545; font-weight: 600;">
                                    <i class="bi bi-exclamation-circle"></i>
                                    Overdue by {{ now()->diffInDays($transaction->due_date) }} days
                                </p>
                                <p class="text-muted small mb-3">Due date: {{ $transaction->due_date->format('M d, Y') }}</p>

                                <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-danger btn-sm w-100">
                                    Return Item
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px;">
                <i class="bi bi-check-circle" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                <p class="text-muted">No overdue items</p>
            </div>
        @endif
    </div>

    <!-- Completed Borrowing -->
    <div class="tab-pane fade" id="completed" role="tabpanel">
        @if ($completedTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th>Item</th>
                            <th>Lender</th>
                            <th>Returned</th>
                            <th>Rating</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completedTransactions as $transaction)
                            <tr>
                                <td><strong>{{ Str::limit($transaction->item->title, 25) }}</strong></td>
                                <td>{{ $transaction->item->owner->name }}</td>
                                <td><small>{{ $transaction->return_date->format('M d, Y') }}</small></td>
                                <td>
                                    @php
                                        $rating = $transaction->ratings->where('rater_id', auth()->id())->first();
                                    @endphp
                                    @if ($rating)
                                        <small style="color: #ffc107;">
                                            @for ($i = 0; $i < $rating->rating; $i++)
                                                <i class="bi bi-star-fill"></i>
                                            @endfor
                                        </small>
                                    @else
                                        <span class="badge bg-secondary">Not rated</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!$rating)
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            Rate
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px;">
                <i class="bi bi-archive" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                <p class="text-muted">No completed borrowing</p>
            </div>
        @endif
    </div>
</div>

@endsection
