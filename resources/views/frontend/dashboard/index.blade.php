@extends('layouts.app')

@section('title', 'Dashboard - Campus Resource Exchange')

@section('content')

<!-- Welcome Section -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">Welcome back, {{ auth()->user()->name }}!</h1>
    <p class="text-muted">Manage your items, transactions, and profile</p>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-bag" style="font-size: 2rem; color: #0d6efd; display: block; margin-bottom: 10px;"></i>
                <h4 style="color: #0d6efd; font-weight: 700; margin-bottom: 5px;">
                    {{ auth()->user()->items->count() }}
                </h4>
                <small class="text-muted">My Items</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-arrow-left-right" style="font-size: 2rem; color: #28a745; display: block; margin-bottom: 10px;"></i>
                <h4 style="color: #28a745; font-weight: 700; margin-bottom: 5px;">
                    {{ $activeTransactions }}
                </h4>
                <small class="text-muted">Active Transactions</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-star" style="font-size: 2rem; color: #ffc107; display: block; margin-bottom: 10px;"></i>
                <h4 style="color: #333; font-weight: 700; margin-bottom: 5px;">
                    {{ round(auth()->user()->averageRating(), 1) }}
                </h4>
                <small class="text-muted">My Rating</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #dc3545; display: block; margin-bottom: 10px;"></i>
                <h4 style="color: #dc3545; font-weight: 700; margin-bottom: 5px;">
                    {{ $pendingPenalties }}
                </h4>
                <small class="text-muted">Pending Penalties</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
            <i class="bi bi-speedometer2"></i> Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="borrowing-tab" data-bs-toggle="tab" data-bs-target="#borrowing" type="button" role="tab">
            <i class="bi bi-download"></i> Borrowing
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="lending-tab" data-bs-toggle="tab" data-bs-target="#lending" type="button" role="tab">
            <i class="bi bi-upload"></i> Lending
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab">
            <i class="bi bi-bookmark"></i> My Items
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Overview Tab -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <div class="row">
            <!-- Recent Activity -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> Recent Activity
                    </div>
                    <div class="card-body">
                        @if ($recentActivity->count() > 0)
                            @foreach ($recentActivity as $activity)
                                <div style="padding-bottom: 15px; border-bottom: 1px solid #dee2e6;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div>
                                            <p style="color: #333; font-weight: 600; margin-bottom: 5px;">
                                                {{ $activity['title'] }}
                                            </p>
                                            <small class="text-muted">{{ $activity['description'] }}</small>
                                        </div>
                                        <small class="text-muted">{{ $activity['timestamp']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center mb-0">No recent activity</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <a href="{{ route('frontend.items.create') }}" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-plus-circle"></i> List New Item
                        </a>
                        <a href="{{ route('frontend.transactions.index') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-arrow-left-right"></i> View Transactions
                        </a>
                        <a href="{{ route('frontend.messages.index') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-chat-dots"></i> Messages
                        </a>
                        <a href="{{ route('frontend.profile.edit') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-gear"></i> Edit Profile
                        </a>
                    </div>
                </div>

                <!-- Profile Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-person"></i> Profile
                    </div>
                    <div class="card-body text-center">
                        <i class="bi bi-person-circle" style="font-size: 3rem; color: #0d6efd; display: block; margin-bottom: 15px;"></i>
                        <h5 style="color: #333; font-weight: 600; margin-bottom: 3px;">
                            {{ auth()->user()->name }}
                        </h5>
                        <small class="text-muted d-block mb-3">{{ auth()->user()->email }}</small>
                        <small class="text-muted d-block mb-3">
                            Member since {{ auth()->user()->created_at->format('M Y') }}
                        </small>
                        <a href="{{ route('frontend.profile.show', auth()->user()) }}" class="btn btn-outline-primary btn-sm w-100">
                            View Public Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrowing Tab -->
    <div class="tab-pane fade" id="borrowing" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-download"></i> Items I'm Borrowing
            </div>
            <div class="card-body">
                @php
                    $borrowingTransactions = \App\Models\Transaction::where('borrower_id', auth()->id())
                        ->where('type', 'lend')
                        ->where('status', 'active')
                        ->with('item.owner:id,name')
                        ->get();
                @endphp

                @if ($borrowingTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Item</th>
                                    <th>Lender</th>
                                    <th>Due Date</th>
                                    <th>Days Remaining</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($borrowingTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($transaction->item->title, 25) }}</strong>
                                        </td>
                                        <td>{{ $transaction->item->owner->name }}</td>
                                        <td>
                                            <small>{{ $transaction->due_date->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $daysRemaining = $transaction->due_date->diffInDays(now());
                                            @endphp
                                            @if ($daysRemaining > 0)
                                                <span class="badge bg-success">{{ $daysRemaining }} days</span>
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
                    <p class="text-muted text-center mb-0">No active borrowing</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Lending Tab -->
    <div class="tab-pane fade" id="lending" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-upload"></i> Items I'm Lending
            </div>
            <div class="card-body">
                @php
                    $lendingTransactions = \App\Models\Transaction::whereHas('item', function($q) {
                        $q->where('user_id', auth()->id());
                    })
                        ->where('type', 'lend')
                        ->where('status', 'active')
                        ->with('borrower:id,name', 'item:id,title')
                        ->get();
                @endphp

                @if ($lendingTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Item</th>
                                    <th>Borrower</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lendingTransactions as $transaction)
                                    <tr>
                                        <td><strong>{{ Str::limit($transaction->item->title, 25) }}</strong></td>
                                        <td>{{ $transaction->borrower->name }}</td>
                                        <td><small>{{ $transaction->due_date->format('M d, Y') }}</small></td>
                                        <td>
                                            @if ($transaction->due_date->lt(now()))
                                                <span class="badge bg-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-success">On Time</span>
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
                    <p class="text-muted text-center mb-0">No active lending</p>
                @endif
            </div>
        </div>
    </div>

    <!-- My Items Tab -->
    <div class="tab-pane fade" id="items" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bookmark"></i> My Items
                <a href="{{ route('frontend.items.create') }}" class="btn btn-sm btn-primary float-end">
                    <i class="bi bi-plus"></i> Add Item
                </a>
            </div>
            <div class="card-body">
                @if (auth()->user()->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Title</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                    <th>Transactions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (auth()->user()->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($item->title, 30) }}</strong>
                                        </td>
                                        <td>
                                            @if ($item->availability_mode === 'lend')
                                                <span class="badge bg-info">Lend</span>
                                            @elseif ($item->availability_mode === 'sell')
                                                <span class="badge bg-danger">Sell</span>
                                            @else
                                                <span class="badge bg-primary">Both</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->status === 'available')
                                                <span class="badge bg-success">Available</span>
                                            @elseif ($item->status === 'borrowed')
                                                <span class="badge bg-warning">Borrowed</span>
                                            @else
                                                <span class="badge bg-secondary">Sold</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $item->transactions->count() }} transactions</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('frontend.items.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                            <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-sm btn-outline-secondary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">
                        No items listed yet.
                        <a href="{{ route('frontend.items.create') }}">Start listing</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Auto-refresh active transactions status
    setInterval(function() {
        // Could add AJAX to refresh transaction status
    }, 30000);
</script>
@endsection
