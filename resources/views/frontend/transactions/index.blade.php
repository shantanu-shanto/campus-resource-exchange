@extends('layouts.app')

@section('title', 'Transactions - Campus Resource Exchange')

@section('content')

<div class="container mt-5">
    <div class="mb-4">
        <h2><i class="bi bi-swap"></i> Transactions</h2>
        <p class="text-muted">Manage your borrowing and lending activities</p>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ !$status ? 'active' : '' }}" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
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
            <button class="nav-link" id="late-tab" data-bs-toggle="tab" data-bs-target="#late" type="button" role="tab">
                <i class="bi bi-exclamation-triangle"></i> Late ({{ $lateCount }})
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
        <div class="tab-pane {{ !$status ? 'show active' : '' }}" id="all" role="tabpanel">
            @if ($allTransactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Other Party</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allTransactions as $transaction)
                                <tr>
                                    <td>{{ Str::limit($transaction->item->title, 25) }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $otherParty = auth()->id() === $transaction->item->user_id 
                                                ? $transaction->borrower 
                                                : $transaction->item->owner;
                                        @endphp
                                        {{ $otherParty->name }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->getStatusBadgeColor() }}">
                                            {{ $transaction->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($transaction->due_date)
                                            @php
                                                $daysLeft = $transaction->due_date->diffInDays(now());
                                                $isOverdue = $daysLeft < 0;
                                            @endphp
                                            <span class="{{ $isOverdue ? 'text-danger' : '' }}">
                                                {{ $transaction->due_date->format('M d, Y') }}
                                                @if ($isOverdue)
                                                    <small class="text-danger">({{ abs($daysLeft) }} days overdue)</small>
                                                @elseif ($daysLeft <= 3)
                                                    <small class="text-warning">({{ $daysLeft }} days left)</small>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-inbox"></i> <strong>No transactions yet</strong>
                </div>
            @endif
        </div>

        <!-- Pending Transactions -->
        <div class="tab-pane" id="pending" role="tabpanel">
            @php
                $pending = $allTransactions->where('status', 'pending');
            @endphp
            @if ($pending->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Requested By</th>
                                <th>Type</th>
                                <th>Requested On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pending as $transaction)
                                <tr>
                                    <td>{{ Str::limit($transaction->item->title, 25) }}</td>
                                    <td>{{ $transaction->borrower->name }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-hourglass-split"></i> No pending transactions
                </div>
            @endif
        </div>

        <!-- Active Transactions -->
        <div class="tab-pane" id="active" role="tabpanel">
            @php
                $active = $allTransactions->where('status', 'active');
            @endphp
            @if ($active->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Party</th>
                                <th>Type</th>
                                <th>Due Date</th>
                                <th>Days Left</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($active as $transaction)
                                @php
                                    $otherParty = auth()->id() === $transaction->item->user_id 
                                        ? $transaction->borrower 
                                        : $transaction->item->owner;
                                    $daysLeft = $transaction->due_date->diffInDays(now());
                                    $isOverdue = $daysLeft < 0;
                                @endphp
                                <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                    <td>{{ Str::limit($transaction->item->title, 25) }}</td>
                                    <td>{{ $otherParty->name }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->due_date->format('M d, Y') }}</td>
                                    <td>
                                        @if ($isOverdue)
                                            <span class="badge bg-danger">{{ abs($daysLeft) }} days overdue</span>
                                        @else
                                            <span class="badge bg-{{ $daysLeft <= 3 ? 'warning' : 'info' }}">{{ $daysLeft }} days</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-play-circle"></i> No active transactions
                </div>
            @endif
        </div>

        <!-- Completed Transactions -->
        <div class="tab-pane" id="completed" role="tabpanel">
            @php
                $completed = $allTransactions->where('status', 'completed');
            @endphp
            @if ($completed->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Party</th>
                                <th>Type</th>
                                <th>Completed Date</th>
                                <th>You Rated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($completed as $transaction)
                                @php
                                    $otherParty = auth()->id() === $transaction->item->user_id 
                                        ? $transaction->borrower 
                                        : $transaction->item->owner;
                                    $myRating = $transaction->ratings->where('rater_id', auth()->id())->first();
                                @endphp
                                <tr>
                                    <td>{{ Str::limit($transaction->item->title, 25) }}</td>
                                    <td>{{ $otherParty->name }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->return_date?->format('M d, Y') ?? $transaction->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        @if ($myRating)
                                            <span class="text-warning">
                                                @for ($i = 0; $i < $myRating->rating; $i++)
                                                    ⭐
                                                @endfor
                                            </span>
                                        @else
                                            <span class="text-muted">Not rated</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-check-circle"></i> No completed transactions
                </div>
            @endif
        </div>

        <!-- Late Transactions -->
        <div class="tab-pane" id="late" role="tabpanel">
            @php
                $late = $allTransactions->where('status', 'late');
            @endphp
            @if ($late->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-danger">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Party</th>
                                <th>Type</th>
                                <th>Due Date</th>
                                <th>Days Late</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($late as $transaction)
                                @php
                                    $otherParty = auth()->id() === $transaction->item->user_id 
                                        ? $transaction->borrower 
                                        : $transaction->item->owner;
                                    $daysLate = now()->diffInDays($transaction->due_date);
                                @endphp
                                <tr>
                                    <td>{{ Str::limit($transaction->item->title, 25) }}</td>
                                    <td>{{ $otherParty->name }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->due_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $daysLate }} days late</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> No late transactions
                </div>
            @endif
        </div>

        <!-- Cancelled Transactions -->
        <div class="tab-pane" id="cancelled" role="tabpanel">
            @php
                $cancelled = $allTransactions->where('status', 'cancelled');
            @endphp
            @if ($cancelled->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Party</th>
                                <th>Type</th>
                                <th>Cancelled On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cancelled as $transaction)
                                @php
                                    $otherParty = auth()->id() === $transaction->item->user_id 
                                        ? $transaction->borrower 
                                        : $transaction->item->owner;
                                @endphp
                                <tr>
                                    <td>{{ Str::limit($transaction->item->title, 25) }}</td>
                                    <td>{{ $otherParty->name }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-x-circle"></i> No cancelled transactions
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
