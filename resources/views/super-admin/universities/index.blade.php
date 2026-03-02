@extends('layouts.app')

@section('title', 'Manage Universities - Super Admin')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">
        <i class="bi bi-building"></i> Manage Universities
    </h1>
    <p class="text-muted">Review applications and manage university accounts</p>
</div>

<!-- Search & Filter Bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('super-admin.universities.index') }}" class="row g-3">
            <div class="col-md-8">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by name, state, or applicant email..." 
                    value="{{ request('search') }}"
                >
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('super-admin.universities.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('super-admin.universities.index', ['status' => 'pending']) }}">
            <i class="bi bi-clock"></i> Pending
            @if ($counts['pending'] > 0)
                <span class="badge bg-warning ms-1">{{ $counts['pending'] }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('super-admin.universities.index', ['status' => 'approved']) }}">
            <i class="bi bi-check-circle"></i> Approved
            <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('super-admin.universities.index', ['status' => 'rejected']) }}">
            <i class="bi bi-x-circle"></i> Rejected
            <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('super-admin.universities.index', ['status' => 'all']) }}">
            <i class="bi bi-list"></i> All
            <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
        </a>
    </li>
</ul>

<!-- Universities Table -->
<div class="card">
    <div class="card-body">
        @if ($universities->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>University</th>
                            <th>Location</th>
                            <th>Domain</th>
                            <th>Applicant</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($universities as $uni)
                            <tr>
                                <td>
                                    <strong style="color: #333;">{{ $uni->name }}</strong>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt"></i> {{ $uni->city }}, {{ $uni->state }}
                                </td>
                                <td>
                                    <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">
                                        @{{ $uni->domain }}
                                    </code>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem;">
                                        <div style="font-weight: 600;">{{ $uni->applicant_name }}</div>
                                        <small class="text-muted">{{ $uni->applicant_email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $uni->users_count }} users</span>
                                </td>
                                <td>
                                    @if ($uni->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($uni->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $uni->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('super-admin.universities.show', $uni) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($universities->hasPages())
                <div class="mt-4">
                    {{ $universities->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h4 style="color: #666; font-weight: 600;">No universities found</h4>
                <p class="text-muted">
                    @if (request('search'))
                        Try adjusting your search terms
                    @else
                        No {{ $status }} universities at this time
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@endsection