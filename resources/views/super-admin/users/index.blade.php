@extends('layouts.app')

@section('title', 'Manage Users - Super Admin')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">
        <i class="bi bi-people"></i> Manage Users
    </h1>
    <p class="text-muted">Platform-wide user management</p>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ number_format($counts['total']) }}</h3>
                <p class="text-muted mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-person-check" style="font-size: 2rem; color: #28a745;"></i>
                <h3 style="color: #28a745; font-weight: 700; margin: 10px 0;">{{ number_format($counts['verified']) }}</h3>
                <p class="text-muted mb-0">Verified</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-clock" style="font-size: 2rem; color: #ffc107;"></i>
                <h3 style="color: #ffc107; font-weight: 700; margin: 10px 0;">{{ number_format($counts['pending']) }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-shield-check" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ $counts['uni_admins'] }}</h3>
                <p class="text-muted mb-0">Uni Admins</p>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.users.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by name or email..." 
                    value="{{ request('search') }}"
                >
            </div>
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Students/Teachers</option>
                    <option value="uni_admin" {{ $role === 'uni_admin' ? 'selected' : '' }}>Uni Admins</option>
                    <option value="all" {{ $role === 'all' ? 'selected' : '' }}>All Roles</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        @if ($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>University</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; margin-right: 12px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong style="color: #333;">{{ $user->name }}</strong>
                                            <br>
                                            <small class="text-muted">ID: #{{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 'user')
                                        <span class="badge bg-primary">Student/Teacher</span>
                                    @elseif ($user->role === 'uni_admin')
                                        <span class="badge bg-info">Uni Admin</span>
                                    @else
                                        <span class="badge bg-danger">Super Admin</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->university)
                                        <a href="{{ route('super-admin.universities.show', $user->university) }}" class="text-decoration-none">
                                            {{ Str::limit($user->university->name, 25) }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
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
                                    <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('super-admin.users.show', $user) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if (!$user->isSuperAdmin())
                                            <form method="POST" action="{{ route('super-admin.users.suspend', $user) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" 
                                                        onclick="return confirm('Suspend {{ $user->name }}?')"
                                                        title="Suspend User">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h4 style="color: #666; font-weight: 600;">No users found</h4>
                <p class="text-muted">
                    @if (request('search'))
                        Try adjusting your search terms or filters
                    @else
                        No users match the selected filters
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@endsection