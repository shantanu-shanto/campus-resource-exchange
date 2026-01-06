@extends('admin.layouts.app')

@section('page-title', 'Dashboard')

@section('content')

<!-- Key Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <i class="bi bi-people"></i>
            <h4>{{ $totalUsers }}</h4>
            <p>Total Users</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <i class="bi bi-bag"></i>
            <h4>{{ $totalItems }}</h4>
            <p>Active Items</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="border-left-color: #28a745;">
            <i class="bi bi-arrow-left-right" style="color: #28a745;"></i>
            <h4>{{ $totalTransactions }}</h4>
            <p>Total Transactions</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card" style="border-left-color: #dc3545;">
            <i class="bi bi-flag" style="color: #dc3545;"></i>
            <h4>{{ $openReports }}</h4>
            <p>Open Reports</p>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Recent Users -->
    <div class="col-lg-6 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-people"></i> Recent Users</h3>
            </div>
            <div class="admin-card-body">
                @if ($recentUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentUsers as $user)
                                    <tr>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td><small>{{ $user->email }}</small></td>
                                        <td><small>{{ $user->created_at->format('M d, Y') }}</small></td>
                                        <td>
                                            @if ($user->is_blocked)
                                                <span class="badge bg-danger">Blocked</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-admin btn-admin-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No recent users</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Items -->
    <div class="col-lg-6 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-bag"></i> Recent Items</h3>
            </div>
            <div class="admin-card-body">
                @if ($recentItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Posted</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentItems as $item)
                                    <tr>
                                        <td><strong>{{ Str::limit($item->title, 20) }}</strong></td>
                                        <td><small>{{ $item->owner->name }}</small></td>
                                        <td>
                                            @if ($item->status === 'available')
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-warning">{{ ucfirst($item->status) }}</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $item->created_at->format('M d, Y') }}</small></td>
                                        <td>
                                            <a href="{{ route('admin.items.show', $item) }}" class="btn btn-sm btn-admin btn-admin-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No recent items</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- System Health -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-activity"></i> System Health</h3>
            </div>
            <div class="admin-card-body">
                <div class="mb-3">
                    <small class="text-muted">Database Connection</small>
                    <span class="badge bg-success float-end">OK</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted">File Storage</small>
                    <span class="badge bg-success float-end">OK</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Cache System</small>
                    <span class="badge bg-success float-end">OK</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Disk Space</small>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 65%;"></div>
                    </div>
                    <small class="text-muted">65% used</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-lightning"></i> Quick Actions</h3>
            </div>
            <div class="admin-card-body">
                <a href="{{ route('admin.users.index') }}" class="btn btn-admin btn-admin-primary w-100 mb-2">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <a href="{{ route('admin.items.index') }}" class="btn btn-admin btn-admin-primary w-100 mb-2">
                    <i class="bi bi-bag"></i> Moderate Items
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-admin btn-admin-primary w-100 mb-2">
                    <i class="bi bi-flag"></i> View Reports
                </a>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-admin btn-admin-primary w-100">
                    <i class="bi bi-gear"></i> System Settings
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
