@extends('admin.layouts.app')

@section('page-title', 'User Management')

@section('content')

<!-- Search & Filter -->
<div class="admin-search">
    <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 10px; flex: 1;">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
            value="{{ request('search') }}">
        <select name="status" class="form-select" style="max-width: 150px;">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
        </select>
        <button type="submit" class="btn btn-admin btn-admin-primary">
            <i class="bi bi-search"></i> Search
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="bi bi-people"></i> Users ({{ $users->total() }})</h3>
    </div>
    <div class="admin-card-body">
        @if ($users->count() > 0)
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Items</th>
                            <th>Rating</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td><small>{{ $user->email }}</small></td>
                                <td>
                                    <span class="badge bg-info">{{ $user->items->count() }}</span>
                                </td>
                                <td>
                                    <small>
                                        <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                        {{ round($user->averageRating(), 1) }}
                                    </small>
                                </td>
                                <td><small>{{ $user->created_at->format('M d, Y') }}</small></td>
                                <td>
                                    @if ($user->is_blocked)
                                        <span class="badge-status bg-danger">Blocked</span>
                                    @elseif ($user->email_verified_at)
                                        <span class="badge-status bg-success">Active</span>
                                    @else
                                        <span class="badge-status bg-warning">Unverified</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-admin btn-admin-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div style="margin-top: 20px;">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <p class="text-muted text-center mb-0">No users found</p>
        @endif
    </div>
</div>

@endsection
