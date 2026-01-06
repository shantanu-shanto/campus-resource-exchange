@extends('layouts.app')

@section('title', 'Transactions - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">Transactions</h1>
    <p class="text-muted">Manage your borrowing and selling activities</p>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
            <i class="bi bi-list"></i> All ({{ $allCount }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
            <i class="bi bi-hourglass-split"></i> Pending ({{ $pendingCount }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
            <i class="bi bi-play-circle"></i> Active ({{ $activeCount }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
            <i class="bi bi-check-circle"></i> Completed ({{ $completedCount }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
            <i class="bi bi-x-circle"></i> Cancelled ({{ $cancelledCount }})
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- All Transactions -->
    <div class="tab-pane fade show active" id="all" role="tabpanel">
        @if ($transactions->count() > 0)
            @foreach ($transactions as $transaction)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <!-- Item Info -->
                            <div class="col-md-4">
                                <div style="display: flex; gap: 12px;">
                                    <div style="width: 60px; height: 60px; background: #f0f4ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        @if ($transaction->item->image_path)
                                            <img src="{{ asset('storage/' . $transaction->item->image_path) }}" alt="{{ $transaction->item->title }}"
                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                        @else
                                            <i class="bi bi-image" style="font-size: 1.5rem; color: #0d6efd; opacity: 0.3;"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 style="color: #333; font-weight: 600; margin-bottom: 3px;">
                                            {{ Str::limit($transaction->item->title, 25) }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ ucfirst($transaction->type) }}
                                            @if ($transaction->type === 'sell')
                                                - ৳{{ $transaction->item->price }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Party Info -->
                            <div class="col-md-3">
                                @php
                                    $otherParty = auth()->id() === $transaction->item->user_id
                                        ? $transaction->borrower
                                        : $transaction->item->owner;
                                    $role = auth()->id() === $transaction->item->user_id ? 'Lender' : 'Borrower';
                                @endphp

                                <small class="text-muted d-block mb-2">
                                    {{ $role === 'Lender' ? 'Borrowed by' : 'From' }}
                                </small>
                                <p style="color: #333; font-weight: 600; margin-bottom: 3px;">
                                    {{ $otherParty->name }}
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                    {{ round($otherParty->averageRating(), 1) }}
                                </small>
                            </div>

                            <!-- Dates -->
                            <div class="col-md-2">
                                <small class="text-muted d-block mb-2">
                                    {{ $transaction->type === 'lend' ? 'Due' : 'Posted' }}
                                </small>
                                <p style="color: #333; font-weight: 600; margin-bottom: 3px;">
                                    @if ($transaction->type === 'lend')
                                        {{ $transaction->due_date->format('M d, Y') }}
                                    @else
                                        {{ $transaction->created_at->format('M d, Y') }}
                                    @endif
                                </p>
                            </div>

                            <!-- Status & Action -->
                            <div class="col-md-3 text-end">
                                <div class="mb-2">
                                    @if ($transaction->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($transaction->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($transaction->status === 'completed')
                                        <span class="badge bg-info">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @endif
                                </div>
                                <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            @if ($transactions->hasPages())
                <div style="margin-top: 30px;">
                    {{ $transactions->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h4 style="color: #666; font-weight: 600;">No transactions yet</h4>
            </div>
        @endif
    </div>

    <!-- Pending Transactions -->
    <div class="tab-pane fade" id="pending" role="tabpanel">
        @if ($pendingTransactions->count() > 0)
            <div class="row">
                @foreach ($pendingTransactions as $transaction)
                    <div class="col-lg-6 mb-4">
                        <div class="card border-warning">
                            <div class="card-header" style="background: #fff3cd; color: #856404;">
                                <i class="bi bi-hourglass-split"></i> Awaiting Response
                            </div>
                            <div class="card-body">
                                <h5 style="color: #333; font-weight: 600; margin-bottom: 10px;">
                                    {{ $transaction->item->title }}
                                </h5>
                                <p class="text-muted small mb-3">
                                    {{ ucfirst($transaction->type) }} request from {{ $transaction->borrower->name }}
                                </p>
                                <p style="color: #666; margin-bottom: 15px;">
                                    <i class="bi bi-chat-dots"></i> {{ $transaction->message ?? 'No message provided' }}
                                </p>

                                <div class="d-flex gap-2">
                                    @if (auth()->id() === $transaction->item->user_id)
                                        <form method="POST" action="{{ route('frontend.transactions.approve', $transaction) }}" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100 btn-sm">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $transaction->id }}">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    @else
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-primary btn-sm w-100">
                                            View Request
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-check-circle" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <p class="text-muted">No pending requests</p>
            </div>
        @endif
    </div>

    <!-- Active Transactions -->
    <div class="tab-pane fade" id="active" role="tabpanel">
        @if ($activeTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Party</th>
                            <th>Due Date</th>
                            <th>Days Left</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activeTransactions as $transaction)
                            <tr>
                                <td><strong>{{ Str::limit($transaction->item->title, 20) }}</strong></td>
                                <td><span class="badge bg-info">{{ ucfirst($transaction->type) }}</span></td>
                                <td>
                                    {{ auth()->id() === $transaction->item->user_id
                                        ? $transaction->borrower->name
                                        : $transaction->item->owner->name }}
                                </td>
                                <td><small>{{ $transaction->due_date->format('M d, Y') }}</small></td>
                                <td>
                                    @php
                                        $daysLeft = $transaction->due_date->diffInDays(now());
                                    @endphp
                                    @if ($daysLeft > 0)
                                        <span class="badge bg-success">{{ $daysLeft }}</span>
                                    @else
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <p class="text-muted">No active transactions</p>
            </div>
        @endif
    </div>

    <!-- Completed Transactions -->
    <div class="tab-pane fade" id="completed" role="tabpanel">
        @if ($completedTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Party</th>
                            <th>Completed</th>
                            <th>You Rated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completedTransactions as $transaction)
                            <tr>
                                <td><strong>{{ Str::limit($transaction->item->title, 20) }}</strong></td>
                                <td><span class="badge bg-info">{{ ucfirst($transaction->type) }}</span></td>
                                <td>
                                    {{ auth()->id() === $transaction->item->user_id
                                        ? $transaction->borrower->name
                                        : $transaction->item->owner->name }}
                                </td>
                                <td><small>{{ $transaction->return_date?->format('M d, Y') ?? $transaction->completed_at?->format('M d, Y') }}</small></td>
                                <td>
                                    @php
                                        $myRating = $transaction->ratings->where('rater_id', auth()->id())->first();
                                    @endphp
                                    @if ($myRating)
                                        <small style="color: #ffc107;">
                                            @for ($i = 0; $i < $myRating->rating; $i++)
                                                <i class="bi bi-star-fill"></i>
                                            @endfor
                                        </small>
                                    @else
                                        <span class="badge bg-secondary">Not rated</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-archive" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <p class="text-muted">No completed transactions</p>
            </div>
        @endif
    </div>

    <!-- Cancelled Transactions -->
    <div class="tab-pane fade" id="cancelled" role="tabpanel">
        @if ($cancelledTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Party</th>
                            <th>Cancelled</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cancelledTransactions as $transaction)
                            <tr>
                                <td><strong>{{ Str::limit($transaction->item->title, 20) }}</strong></td>
                                <td><span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span></td>
                                <td>
                                    {{ auth()->id() === $transaction->item->user_id
                                        ? $transaction->borrower->name
                                        : $transaction->item->owner->name }}
                                </td>
                                <td><small>{{ $transaction->cancelled_at?->format('M d, Y') }}</small></td>
                                <td>
                                    <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-x-circle" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <p class="text-muted">No cancelled transactions</p>
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal (for each pending transaction) -->
@foreach ($pendingTransactions as $transaction)
    @if (auth()->id() === $transaction->item->user_id)
        <div class="modal fade" id="rejectModal{{ $transaction->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-danger">
                        <h5 class="modal-title">Reject Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('frontend.transactions.reject', $transaction) }}">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted mb-3">Rejecting this request will notify the borrower.</p>
                            <label class="form-label">Optional Message</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Let them know why..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
