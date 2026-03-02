@extends('layouts.app')

@section('title', $user->name . ' - Super Admin')

@section('content')

<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<!-- User Profile Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; font-weight: 700; margin: 0 auto;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            </div>
            <div class="col-md-7">
                <h2 style="color: #0d6efd; font-weight: 700; margin-bottom: 10px;">
                    {{ $user->name }}
                </h2>
                <div class="mb-2">
                    <i class="bi bi-envelope"></i> {{ $user->email }}
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar"></i> 
                    <strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}
                </div>
                @if ($user->university)
                    <div class="mb-2">
                        <i class="bi bi-building"></i> 
                        <strong>University:</strong> 
                        <a href="{{ route('super-admin.universities.show', $user->university) }}">
                            {{ $user->university->name }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="col-md-3 text-md-end">
                @if ($user->role === 'user')
                    <span class="badge bg-primary" style="font-size: 1rem; padding: 8px 16px;">
                        <i class="bi bi-person"></i> Student/Teacher
                    </span>
                @elseif ($user->role === 'uni_admin')
                    <span class="badge bg-info" style="font-size: 1rem; padding: 8px 16px;">
                        <i class="bi bi-shield"></i> Uni Admin
                    </span>
                @else
                    <span class="badge bg-danger" style="font-size: 1rem; padding: 8px 16px;">
                        <i class="bi bi-shield-fill"></i> Super Admin
                    </span>
                @endif
                <br><br>
                @if ($user->status === 'verified')
                    <span class="badge bg-success" style="font-size: 1rem; padding: 8px 16px;">
                        <i class="bi bi-check-circle"></i> Verified
                    </span>
                @elseif ($user->status === 'pending')
                    <span class="badge bg-warning" style="font-size: 1rem; padding: 8px 16px;">
                        <i class="bi bi-clock"></i> Pending
                    </span>
                @else
                    <span class="badge bg-danger" style="font-size: 1rem; padding: 8px 16px;">
                        <i class="bi bi-x-circle"></i> Rejected
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-box" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ $user->items->count() }}</h3>
                <p class="text-muted mb-0">Items Listed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-arrow-down-circle" style="font-size: 2rem; color: #28a745;"></i>
                <h3 style="color: #28a745; font-weight: 700; margin: 10px 0;">{{ $user->transactionsAsBorrower->count() }}</h3>
                <p class="text-muted mb-0">As Borrower</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-arrow-up-circle" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ $user->transactionsAsOwner->count() }}</h3>
                <p class="text-muted mb-0">As Owner</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-star-fill" style="font-size: 2rem; color: #ffc107;"></i>
                <h3 style="color: #ffc107; font-weight: 700; margin: 10px 0;">{{ round($user->averageRating(), 1) }}</h3>
                <p class="text-muted mb-0">Average Rating</p>
            </div>
        </div>
    </div>
</div>

<!-- Penalties -->
@if ($user->penalties->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-exclamation-triangle"></i> Penalties ({{ $user->penalties->count() }})
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Days Late</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->penalties as $penalty)
                            <tr>
                                <td>{{ $penalty->transaction->item->title ?? 'N/A' }}</td>
                                <td>{{ $penalty->days_late }} days</td>
                                <td><strong>৳{{ number_format($penalty->amount, 2) }}</strong></td>
                                <td>
                                    @if ($penalty->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($penalty->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-info">Waived</span>
                                    @endif
                                </td>
                                <td>{{ $penalty->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"><strong>Total Unpaid:</strong></td>
                            <td colspan="3"><strong class="text-danger">৳{{ number_format($user->totalUnpaidPenalties(), 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Items Listed -->
@if ($user->items->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-box"></i> Items Listed ({{ $user->items->count() }})
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($user->items->take(6) as $item)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 style="color: #333; font-weight: 600;">{{ Str::limit($item->title, 30) }}</h6>
                                <p class="text-muted mb-2" style="font-size: 0.85rem;">{{ Str::limit($item->description, 50) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $item->status === 'available' ? 'success' : 'secondary' }}">
                                        {{ $item->status }}
                                    </span>
                                    @if ($item->price)
                                        <strong class="text-primary">৳{{ $item->price }}</strong>
                                    @else
                                        <strong class="text-success">Free</strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($user->items->count() > 6)
                <p class="text-center mb-0">
                    <small class="text-muted">Showing 6 of {{ $user->items->count() }} items</small>
                </p>
            @endif
        </div>
    </div>
@endif

<!-- Recent Transactions -->
@if ($user->transactionsAsBorrower->count() > 0 || $user->transactionsAsOwner->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-arrow-left-right"></i> Recent Transactions
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Role</th>
                            <th>Other Party</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allTransactions = $user->transactionsAsBorrower->merge($user->transactionsAsOwner)->sortByDesc('created_at')->take(10);
                        @endphp
                        @foreach ($allTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->item->title ?? 'N/A' }}</td>
                                <td>
                                    @if ($transaction->type === 'lend')
                                        <span class="badge bg-info">Lend</span>
                                    @elseif ($transaction->type === 'sell')
                                        <span class="badge bg-danger">Sell</span>
                                    @else
                                        <span class="badge bg-success">Share</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->borrower_id === $user->id)
                                        <span class="badge bg-primary">Borrower</span>
                                    @else
                                        <span class="badge bg-secondary">Owner</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->borrower_id === $user->id)
                                        {{ $transaction->owner->name ?? 'N/A' }}
                                    @else
                                        {{ $transaction->borrower->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif ($transaction->status === 'active')
                                        <span class="badge bg-info">Active</span>
                                    @elseif ($transaction->status === 'late')
                                        <span class="badge bg-danger">Late</span>
                                    @else
                                        <span class="badge bg-warning">{{ $transaction->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Actions -->
@if (!$user->isSuperAdmin())
    <div class="card">
        <div class="card-header">
            <i class="bi bi-lightning"></i> Actions
        </div>
        <div class="card-body">
            <div class="d-flex gap-2">
                <!-- Suspend -->
                @if ($user->status === 'verified')
                    <form method="POST" action="{{ route('super-admin.users.suspend', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Suspend {{ $user->name }}? They will lose access to the platform.')">
                            <i class="bi bi-pause-circle"></i> Suspend User
                        </button>
                    </form>
                @endif

                <!-- Delete -->
                <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('PERMANENTLY DELETE {{ $user->name }}? This cannot be undone!')">
                        <i class="bi bi-trash"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection