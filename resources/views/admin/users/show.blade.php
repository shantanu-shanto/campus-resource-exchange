@extends('admin.layouts.app')

@section('page-title', 'User Details: ' . $user->name)

@section('content')

<div class="row">
    <!-- User Info -->
    <div class="col-lg-8 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-person"></i> User Information</h3>
            </div>
            <div class="admin-card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Name</label>
                        <p style="color: #333; font-weight: 600;">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <p style="color: #333; font-weight: 600;">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <p style="color: #333; font-weight: 600;">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Member Since</label>
                        <p style="color: #333; font-weight: 600;">{{ $user->created_at->format('F d, Y') }}</p>
                    </div>
                </div>

                @if ($user->bio)
                    <div class="mb-4">
                        <label class="text-muted small">Bio</label>
                        <p style="color: #333;">{{ $user->bio }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- User Activity -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-activity"></i> Activity & Stats</h3>
            </div>
            <div class="admin-card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted">Items Listed</small>
                        <h4 style="color: #0d6efd; font-weight: 700;">{{ $user->items->count() }}</h4>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted">Total Transactions</small>
                        <h4 style="color: #28a745; font-weight: 700;">{{ $user->transactionsAsBorrower->count() + $user->itemsLent()->count() }}</h4>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted">Average Rating</small>
                        <h4 style="color: #ffc107; font-weight: 700;">{{ round($user->averageRating(), 1) }}/5</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h3><i class="bi bi-shield"></i> Account Status</h3>
            </div>
            <div class="admin-card-body">
                <p class="text-muted small mb-3">Current Status:</p>
                @if ($user->is_blocked)
                    <p>
                        <span class="badge-status bg-danger">BLOCKED</span>
                    </p>
                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-admin btn-admin-primary w-100">
                            <i class="bi bi-unlock"></i> Unblock User
                        </button>
                    </form>
                @else
                    <p>
                        <span class="badge-status bg-success">ACTIVE</span>
                    </p>
                    <button type="button" class="btn btn-admin btn-admin-danger w-100" data-bs-toggle="modal" data-bs-target="#blockModal">
                        <i class="bi bi-lock"></i> Block User
                    </button>
                @endif
            </div>
        </div>

        <!-- Verification Card -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h3><i class="bi bi-check-circle"></i> Verification</h3>
            </div>
            <div class="admin-card-body">
                <p class="text-muted small mb-2">Email Verified:</p>
                @if ($user->email_verified_at)
                    <p><span class="badge bg-success">Yes</span> - {{ $user->email_verified_at->format('M d, Y') }}</p>
                @else
                    <p><span class="badge bg-warning">No</span></p>
                @endif
            </div>
        </div>

        <!-- Recent Items -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-bag"></i> Recent Items</h3>
            </div>
            <div class="admin-card-body">
                @if ($user->items->count() > 0)
                    @foreach ($user->items->take(3) as $item)
                        <div style="padding: 10px 0; border-bottom: 1px solid #dee2e6;">
                            <p style="margin: 0; font-weight: 600; font-size: 0.9rem;">
                                {{ Str::limit($item->title, 25) }}
                            </p>
                            <small class="text-muted">{{ $item->created_at->format('M d, Y') }}</small>
                        </div>
                    @endforeach
                    <a href="{{ route('admin.items.index', ['owner' => $user->id]) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                        View All Items
                    </a>
                @else
                    <p class="text-muted text-center mb-0">No items</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Block Modal -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title">Block User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.block', $user) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-danger mb-3">
                        <strong>Warning!</strong> Blocking this user will prevent them from using the platform.
                    </p>
                    <label class="form-label">Reason for Blocking</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Explain the reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Block User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
