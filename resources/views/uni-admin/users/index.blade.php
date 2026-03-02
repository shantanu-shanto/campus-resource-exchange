@extends('layouts.app')

@section('title', 'Manage Users — UniShare Admin')

@section('extra-css')
<style>
    .ua-page-header {
        margin-bottom: 24px;
    }

    .ua-page-header h1 {
        font-size: 1.55rem;
        font-weight: 700;
        color: #1a1f36;
        margin-bottom: 4px;
    }

    .ua-page-header p {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
    }

    /* ── Tab bar ───────────────────────────────────────── */
    .filter-tabs {
        display: flex;
        gap: 4px;
        background: #f3f4f6;
        border-radius: 10px;
        padding: 4px;
        width: fit-content;
        flex-wrap: wrap;
    }

    .filter-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 7px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .filter-tab:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .filter-tab.active {
        background: #fff;
        color: #0d6efd;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }

    .filter-tab .tab-count {
        background: #e5e7eb;
        color: #6b7280;
        border-radius: 20px;
        padding: 1px 7px;
        font-size: 0.72rem;
    }

    .filter-tab.active .tab-count {
        background: #dbeafe;
        color: #1e40af;
    }

    .filter-tab.active.tab-pending .tab-count {
        background: #fef9c3;
        color: #854d0e;
    }

    /* ── Search bar ────────────────────────────────────── */
    .search-bar {
        position: relative;
    }

    .search-bar i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .search-bar input {
        padding-left: 36px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        font-size: 0.875rem;
        height: 38px;
        width: 260px;
    }

    .search-bar input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
        outline: none;
    }

    /* ── Main card ─────────────────────────────────────── */
    .users-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    /* ── Table ─────────────────────────────────────────── */
    .users-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .users-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 11px 20px;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .users-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s;
    }

    .users-table tbody tr:last-child {
        border-bottom: none;
    }

    .users-table tbody tr:hover {
        background: #f9fafb;
    }

    .users-table td {
        padding: 13px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* ── User cell ─────────────────────────────────────── */
    .u-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e7f1ff;
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .u-name {
        font-weight: 600;
        color: #1a1f36;
        font-size: 0.88rem;
    }

    .u-email {
        font-size: 0.76rem;
        color: #9ca3af;
    }

    /* ── Badges ────────────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .badge-soft.pending  { background: #fef9c3; color: #854d0e; }
    .badge-soft.verified { background: #d1fae5; color: #065f46; }
    .badge-soft.rejected { background: #fee2e2; color: #991b1b; }

    /* ── Action buttons ────────────────────────────────── */
    .action-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-verify {
        background: #059669;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-verify:hover { background: #047857; }

    .btn-reject-sm {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-reject-sm:hover { background: #fecaca; }

    .btn-suspend-sm {
        background: #fff7ed;
        color: #ea580c;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-suspend-sm:hover { background: #ffedd5; }

    .btn-view {
        color: #0d6efd;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 6px;
        background: #e7f1ff;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: background 0.15s;
    }

    .btn-view:hover { background: #dbeafe; color: #0d6efd; }

    /* ── Empty state ───────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 12px;
        color: #d1d5db;
    }

    .empty-state p {
        font-size: 0.9rem;
        margin: 0;
    }

    /* ── Pagination wrapper ────────────────────────────── */
    .pagination-wrapper {
        padding: 16px 20px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    /* ── Responsive ────────────────────────────────────── */
    @media (max-width: 768px) {
        .search-bar input { width: 100%; }
        .users-table thead th:nth-child(3),
        .users-table td:nth-child(3) { display: none; }
        .filter-tabs { width: 100%; }
    }

    @media (max-width: 576px) {
        .users-table thead th:nth-child(4),
        .users-table td:nth-child(4) { display: none; }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- ══════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════ --}}
    <div class="ua-page-header">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-person-check me-2 text-primary"></i>Manage Users</h1>
                <p>Verify, reject, or suspend student and teacher accounts at your university.</p>
            </div>
            {{-- Summary pill --}}
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-secondary border px-3 py-2" style="font-size: 0.8rem;">
                    <i class="bi bi-people me-1"></i> {{ $counts['all'] }} total
                </span>
                <span class="badge px-3 py-2" style="background: #fef9c3; color: #854d0e; font-size: 0.8rem;">
                    <i class="bi bi-hourglass-split me-1"></i> {{ $counts['pending'] }} pending
                </span>
                <span class="badge px-3 py-2" style="background: #d1fae5; color: #065f46; font-size: 0.8rem;">
                    <i class="bi bi-check-circle me-1"></i> {{ $counts['verified'] }} verified
                </span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         FILTERS + SEARCH ROW
    ══════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">

        {{-- Status tabs --}}
        <div class="filter-tabs">
            <a href="{{ route('uni-admin.users.index') }}?status=all"
               class="filter-tab {{ $status === 'all' ? 'active' : '' }}">
                All <span class="tab-count">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('uni-admin.users.index') }}?status=pending"
               class="filter-tab tab-pending {{ $status === 'pending' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split" style="font-size: 0.75rem;"></i>
                Pending <span class="tab-count">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('uni-admin.users.index') }}?status=verified"
               class="filter-tab {{ $status === 'verified' ? 'active' : '' }}">
                <i class="bi bi-check-circle" style="font-size: 0.75rem;"></i>
                Verified <span class="tab-count">{{ $counts['verified'] }}</span>
            </a>
            <a href="{{ route('uni-admin.users.index') }}?status=rejected"
               class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">
                <i class="bi bi-x-circle" style="font-size: 0.75rem;"></i>
                Rejected <span class="tab-count">{{ $counts['rejected'] }}</span>
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('uni-admin.users.index') }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="search-bar">
                <i class="bi bi-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Search name or email…"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>
        </form>

    </div>

    {{-- ══════════════════════════════════════
         USERS TABLE
    ══════════════════════════════════════ --}}
    <div class="users-card">
        @if ($users->count())
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Email Domain</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            {{-- User cell --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="u-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="u-name">{{ $user->name }}</div>
                                        <div class="u-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge-soft {{ $user->status }}">
                                    @if ($user->status === 'pending')
                                        <i class="bi bi-hourglass-split"></i>
                                    @elseif ($user->status === 'verified')
                                        <i class="bi bi-check-circle"></i>
                                    @else
                                        <i class="bi bi-x-circle"></i>
                                    @endif
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>

                            {{-- Registered --}}
                            <td style="color: #6b7280; font-size: 0.82rem;">
                                <span title="{{ $user->created_at->format('d M Y, h:i A') }}">
                                    {{ $user->created_at->format('d M Y') }}
                                </span>
                                <div style="font-size: 0.72rem; color: #9ca3af;">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </td>

                            {{-- Email domain --}}
                            <td style="color: #6b7280; font-size: 0.82rem;">
                                @php $domain = substr(strrchr($user->email, '@'), 1); @endphp
                                <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">
                                    @{{ $domain }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="action-group">
                                    {{-- View --}}
                                    <a href="{{ route('uni-admin.users.show', $user) }}" class="btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>

                                    {{-- Verify (only if pending) --}}
                                    @if ($user->isPending())
                                        <form method="POST" action="{{ route('uni-admin.users.verify', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn-verify">
                                                <i class="bi bi-check-lg"></i> Verify
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('uni-admin.users.reject', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn-reject-sm"
                                                onclick="return confirm('Reject {{ addslashes($user->name) }}\'s registration?')">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Suspend (only if verified) --}}
                                    @if ($user->isVerified())
                                        <form method="POST" action="{{ route('uni-admin.users.suspend', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn-suspend-sm"
                                                onclick="return confirm('Suspend {{ addslashes($user->name) }}? They will lose access until re-verified.')">
                                                <i class="bi bi-pause-circle"></i> Suspend
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($users->hasPages())
                <div class="pagination-wrapper">
                    <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
                        <p class="mb-0" style="font-size: 0.82rem; color: #6b7280;">
                            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
                        </p>
                        {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        @else
            <div class="empty-state">
                @if ($status === 'pending')
                    <i class="bi bi-person-check"></i>
                    <p>No pending users — all verifications are up to date!</p>
                @elseif ($status === 'verified')
                    <i class="bi bi-people"></i>
                    <p>No verified users yet.</p>
                @elseif ($status === 'rejected')
                    <i class="bi bi-person-x"></i>
                    <p>No rejected users.</p>
                @else
                    <i class="bi bi-people"></i>
                    <p>No users found{{ request('search') ? ' matching "' . e(request('search')) . '"' : '' }}.</p>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection