@extends('layouts.app')

@section('title', 'Super Admin Dashboard - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">
        <i class="bi bi-speedometer2"></i> Super Admin Dashboard
    </h1>
    <p class="text-muted">Platform-wide overview and statistics</p>
</div>

<!-- Stats Grid -->
<div class="row mb-4">
    <!-- Universities Stats -->
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.9rem;">Total Universities</p>
                        <h3 style="color: #0d6efd; font-weight: 700;">{{ $totalUniversities }}</h3>
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i> {{ $approvedUniversities }} Approved
                        </small>
                    </div>
                    <div style="width: 50px; height: 50px; background: #e7f1ff; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-building" style="font-size: 1.8rem; color: #0d6efd;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Applications -->
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.9rem;">Pending Applications</p>
                        <h3 style="color: #ffc107; font-weight: 700;">{{ $pendingUniversities }}</h3>
                        <small class="text-warning">
                            <i class="bi bi-clock"></i> Awaiting Review
                        </small>
                    </div>
                    <div style="width: 50px; height: 50px; background: #fff3cd; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-hourglass-split" style="font-size: 1.8rem; color: #ffc107;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.9rem;">Total Users</p>
                        <h3 style="color: #0d6efd; font-weight: 700;">{{ number_format($totalUsers) }}</h3>
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i> {{ number_format($verifiedUsers) }} Verified
                        </small>
                    </div>
                    <div style="width: 50px; height: 50px; background: #e7f1ff; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people" style="font-size: 1.8rem; color: #0d6efd;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Activity -->
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 0.9rem;">Total Transactions</p>
                        <h3 style="color: #0d6efd; font-weight: 700;">{{ number_format($totalTransactions) }}</h3>
                        @if ($lateTransactions > 0)
                            <small class="text-danger">
                                <i class="bi bi-exclamation-triangle"></i> {{ $lateTransactions }} Late
                            </small>
                        @else
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> All On Time
                            </small>
                        @endif
                    </div>
                    <div style="width: 50px; height: 50px; background: #e7f1ff; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-left-right" style="font-size: 1.8rem; color: #0d6efd;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-box" style="font-size: 2rem; color: #0d6efd; margin-bottom: 10px;"></i>
                <p class="text-muted mb-1" style="font-size: 0.9rem;">Total Items</p>
                <h4 style="color: #333; font-weight: 700;">{{ number_format($totalItems) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-person-check" style="font-size: 2rem; color: #0d6efd; margin-bottom: 10px;"></i>
                <p class="text-muted mb-1" style="font-size: 0.9rem;">Pending Users</p>
                <h4 style="color: #333; font-weight: 700;">{{ number_format($pendingUsers) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-clock-history" style="font-size: 2rem; color: #dc3545; margin-bottom: 10px;"></i>
                <p class="text-muted mb-1" style="font-size: 0.9rem;">Late Returns</p>
                <h4 style="color: #333; font-weight: 700;">{{ $lateTransactions }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar" style="font-size: 2rem; color: #ffc107; margin-bottom: 10px;"></i>
                <p class="text-muted mb-1" style="font-size: 0.9rem;">Pending Penalties</p>
                <h4 style="color: #333; font-weight: 700;">৳{{ number_format($totalPendingPenalties, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row">
    <!-- Pending University Applications -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock"></i> Pending University Applications</span>
                <a href="{{ route('super-admin.universities.index', ['status' => 'pending']) }}" class="btn btn-sm btn-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if ($pendingUniversityApplications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach ($pendingUniversityApplications as $uni)
                            <div class="list-group-item" style="border-left: 4px solid #ffc107; padding: 15px;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 style="color: #333; font-weight: 600; margin-bottom: 5px;">
                                            {{ $uni->name }}
                                        </h6>
                                        <p class="text-muted mb-1" style="font-size: 0.85rem;">
                                            <i class="bi bi-geo-alt"></i> {{ $uni->city }}, {{ $uni->state }}
                                        </p>
                                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                            <i class="bi bi-envelope"></i> {{ $uni->applicant_email }}
                                        </p>
                                        <small class="text-muted">
                                            Applied {{ $uni->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <a href="{{ route('super-admin.universities.show', $uni) }}" class="btn btn-sm btn-outline-primary">
                                        Review
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                        <p class="text-muted mb-0">No pending applications</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recently Approved Universities -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-check-circle"></i> Recently Approved Universities</span>
                <a href="{{ route('super-admin.universities.index', ['status' => 'approved']) }}" class="btn btn-sm btn-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if ($recentUniversities->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach ($recentUniversities as $uni)
                            <div class="list-group-item" style="border-left: 4px solid #28a745; padding: 15px;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 style="color: #333; font-weight: 600; margin-bottom: 5px;">
                                            {{ $uni->name }}
                                        </h6>
                                        <p class="text-muted mb-1" style="font-size: 0.85rem;">
                                            <i class="bi bi-geo-alt"></i> {{ $uni->city }}, {{ $uni->state }}
                                        </p>
                                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                            <i class="bi bi-at"></i> @{{ $uni->domain }}
                                        </p>
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> Approved {{ $uni->approved_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <a href="{{ route('super-admin.universities.show', $uni) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                        <p class="text-muted mb-0">No recently approved universities</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Recent User Registrations</span>
                <a href="{{ route('super-admin.users.index') }}" class="btn btn-sm btn-primary">
                    View All Users
                </a>
            </div>
            <div class="card-body">
                @if ($recentUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>University</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentUsers as $user)
                                    <tr>
                                        <td style="font-weight: 600;">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if ($user->university)
                                                <span class="badge bg-info">{{ $user->university->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->status === 'verified')
                                                <span class="badge bg-success">Verified</span>
                                            @elseif ($user->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('super-admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 15px;"></i>
                        <p class="text-muted mb-0">No recent user registrations</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('super-admin.universities.index', ['status' => 'pending']) }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-clock" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                            Review Applications
                            @if ($pendingUniversities > 0)
                                <span class="badge bg-warning ms-2">{{ $pendingUniversities }}</span>
                            @endif
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('super-admin.universities.index') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-building" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                            Manage Universities
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-people" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-graph-up" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                            View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        fetch('{{ route("super-admin.api.quick-stats") }}')
            .then(response => response.json())
            .then(data => {
                console.log('Stats updated:', data);
                // Update badge counts if needed
            })
            .catch(error => console.error('Error fetching stats:', error));
    }, 30000);
</script>
@endsection