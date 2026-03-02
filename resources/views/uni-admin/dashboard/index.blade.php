@extends('layouts.app')

@section('title', 'University Admin Dashboard — UniShare')

@section('extra-css')
<style>
    /* ── Page base ─────────────────────────────────────── */
    .ua-dashboard {
        padding: 28px 0 60px;
    }

    /* ── Page header ───────────────────────────────────── */
    .ua-page-header {
        margin-bottom: 28px;
    }

    .ua-page-header h1 {
        font-size: 1.65rem;
        font-weight: 700;
        color: #1a1f36;
        margin-bottom: 4px;
    }

    .ua-page-header p {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    .ua-page-header .uni-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e7f1ff;
        color: #0d6efd;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 10px;
    }

    /* ── Stat cards ────────────────────────────────────── */
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        transition: box-shadow 0.2s, transform 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .stat-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.09);
        transform: translateY(-2px);
        color: inherit;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .stat-icon.blue   { background: #e7f1ff; color: #0d6efd; }
    .stat-icon.green  { background: #d1fae5; color: #059669; }
    .stat-icon.yellow { background: #fef9c3; color: #ca8a04; }
    .stat-icon.red    { background: #fee2e2; color: #dc2626; }
    .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
    .stat-icon.orange { background: #ffedd5; color: #ea580c; }

    .stat-body {}
    .stat-value {
        font-size: 1.7rem;
        font-weight: 700;
        color: #1a1f36;
        line-height: 1;
    }
    .stat-label {
        font-size: 0.78rem;
        color: #6b7280;
        margin-top: 4px;
        font-weight: 500;
    }

    /* ── Alert banner (pending users) ──────────────────── */
    .pending-alert {
        background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
        border: 1px solid #fed7aa;
        border-left: 4px solid #f97316;
        border-radius: 10px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
    }

    .pending-alert i {
        font-size: 1.4rem;
        color: #f97316;
        flex-shrink: 0;
    }

    .pending-alert .pa-text {
        flex: 1;
        font-size: 0.9rem;
        color: #92400e;
        font-weight: 500;
    }

    .pending-alert .pa-text strong {
        color: #7c2d12;
    }

    /* ── Section cards ─────────────────────────────────── */
    .section-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .section-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-card-header h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-card-header h5 i {
        color: #0d6efd;
    }

    .section-card-header .view-all {
        font-size: 0.8rem;
        color: #0d6efd;
        text-decoration: none;
        font-weight: 600;
    }

    .section-card-header .view-all:hover {
        text-decoration: underline;
    }

    .section-card-body {
        padding: 0;
    }

    /* ── Tables ────────────────────────────────────────── */
    .ua-table {
        width: 100%;
        margin: 0;
        font-size: 0.875rem;
        border-collapse: collapse;
    }

    .ua-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 10px 20px;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }

    .ua-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }

    .ua-table tbody tr:last-child {
        border-bottom: none;
    }

    .ua-table tbody tr:hover {
        background: #f9fafb;
    }

    .ua-table td {
        padding: 12px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* ── User avatar ───────────────────────────────────── */
    .u-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #e7f1ff;
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .u-name {
        font-weight: 600;
        color: #1a1f36;
        font-size: 0.88rem;
    }

    .u-email {
        font-size: 0.75rem;
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
    .badge-soft.active   { background: #dbeafe; color: #1e40af; }
    .badge-soft.late     { background: #fee2e2; color: #991b1b; }
    .badge-soft.completed{ background: #d1fae5; color: #065f46; }
    .badge-soft.cancelled{ background: #f3f4f6; color: #6b7280; }
    .badge-soft.lend     { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell     { background: #fce7f3; color: #9d174d; }
    .badge-soft.share    { background: #d1fae5; color: #065f46; }

    /* ── Action buttons ────────────────────────────────── */
    .btn-verify {
        background: #059669;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-verify:hover { background: #047857; color: #fff; }

    .btn-reject-sm {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-reject-sm:hover { background: #fecaca; }

    .btn-view-sm {
        color: #0d6efd;
        font-size: 0.8rem;
        text-decoration: none;
        font-weight: 600;
    }

    .btn-view-sm:hover { text-decoration: underline; }

    /* ── Empty state ───────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 10px;
        color: #d1d5db;
    }

    .empty-state p {
        font-size: 0.87rem;
        margin: 0;
    }

    /* ── Quick stats row (penalties) ───────────────────── */
    .penalty-summary {
        display: flex;
        gap: 16px;
        padding: 16px 22px;
        background: #fefce8;
        border-bottom: 1px solid #fef08a;
    }

    .penalty-summary .ps-item {
        font-size: 0.82rem;
        color: #713f12;
    }

    .penalty-summary .ps-item strong {
        font-weight: 700;
    }

    /* ── Responsive ────────────────────────────────────── */
    @media (max-width: 768px) {
        .stat-card { padding: 16px; }
        .stat-value { font-size: 1.4rem; }
        .ua-table td, .ua-table th { padding: 10px 12px; }
        .pending-alert { flex-direction: column; gap: 8px; }
    }
</style>
@endsection

@section('content')
<div class="ua-dashboard">

    {{-- ══════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════ --}}
    <div class="ua-page-header">
        <div class="uni-badge">
            <i class="bi bi-building"></i>
            {{ auth()->user()->university->name ?? 'Your University' }}
        </div>
        <h1><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</h1>
        <p>Overview of your campus community — users, items, transactions, and penalties.</p>
    </div>

    {{-- ══════════════════════════════════════
         PENDING USERS ALERT BANNER
    ══════════════════════════════════════ --}}
    @if ($pendingUsers > 0)
        <div class="pending-alert">
            <i class="bi bi-person-exclamation"></i>
            <div class="pa-text">
                <strong>{{ $pendingUsers }} student{{ $pendingUsers > 1 ? 's' : '' }} awaiting verification.</strong>
                Review and verify their registrations so they can access the platform.
            </div>
            <a href="{{ route('uni-admin.users.index') }}?status=pending" class="btn btn-warning btn-sm fw-600 px-3">
                Review Now <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         STAT CARDS — ROW 1 (Users & Items)
    ══════════════════════════════════════ --}}
    <div class="row g-3 mb-3">

        {{-- Total Users --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('uni-admin.users.index') }}" class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-people"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
            </a>
        </div>

        {{-- Verified Users --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('uni-admin.users.index') }}?status=verified" class="stat-card">
                <div class="stat-icon green"><i class="bi bi-person-check"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $verifiedUsers }}</div>
                    <div class="stat-label">Verified</div>
                </div>
            </a>
        </div>

        {{-- Pending Users --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('uni-admin.users.index') }}?status=pending" class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-person-dash"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $pendingUsers }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </a>
        </div>

        {{-- Total Items --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('uni-admin.items.index') }}" class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-grid"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalItems }}</div>
                    <div class="stat-label">Total Items</div>
                </div>
            </a>
        </div>

        {{-- Available Items --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('uni-admin.items.index') }}?status=available" class="stat-card">
                <div class="stat-icon green"><i class="bi bi-box-seam"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $availableItems }}</div>
                    <div class="stat-label">Available</div>
                </div>
            </a>
        </div>

        {{-- Borrowed Items --}}
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('uni-admin.items.index') }}?status=borrowed" class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-arrow-left-right"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $borrowedItems }}</div>
                    <div class="stat-label">Borrowed</div>
                </div>
            </a>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         STAT CARDS — ROW 2 (Transactions & Penalties)
    ══════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Active Transactions --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('uni-admin.transactions.index') }}?status=active" class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-arrow-repeat"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $activeTransactions }}</div>
                    <div class="stat-label">Active Transactions</div>
                </div>
            </a>
        </div>

        {{-- Late Transactions --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('uni-admin.transactions.index') }}?status=late" class="stat-card">
                <div class="stat-icon red"><i class="bi bi-clock-history"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $lateTransactions }}</div>
                    <div class="stat-label">Late Returns</div>
                </div>
            </a>
        </div>

        {{-- Pending Penalties --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('uni-admin.penalties.index') }}?status=pending" class="stat-card">
                <div class="stat-icon yellow"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $pendingPenalties }}</div>
                    <div class="stat-label">Pending Penalties</div>
                </div>
            </a>
        </div>

        {{-- Total Penalty Amount --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('uni-admin.penalties.index') }}" class="stat-card">
                <div class="stat-icon red"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-body">
                    <div class="stat-value" style="font-size: 1.3rem;">৳{{ number_format($totalPenaltyAmount, 0) }}</div>
                    <div class="stat-label">Outstanding Amount</div>
                </div>
            </a>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         MAIN CONTENT GRID
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- ── LEFT COLUMN ─────────────────── --}}
        <div class="col-lg-5">

            {{-- Pending Users for Verification --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5>
                        <i class="bi bi-person-exclamation"></i>
                        Pending Verifications
                        @if ($pendingUsers > 0)
                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">{{ $pendingUsers }}</span>
                        @endif
                    </h5>
                    <a href="{{ route('uni-admin.users.index') }}?status=pending" class="view-all">View all</a>
                </div>
                <div class="section-card-body">
                    @forelse ($recentPendingUsers as $user)
                        <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom: 1px solid #f3f4f6;">
                            {{-- Avatar --}}
                            <div class="u-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>

                            {{-- Info --}}
                            <div class="flex-grow-1 min-width-0">
                                <div class="u-name text-truncate">{{ $user->name }}</div>
                                <div class="u-email text-truncate">{{ $user->email }}</div>
                                <div style="font-size: 0.72rem; color: #9ca3af; margin-top: 2px;">
                                    Registered {{ $user->created_at->diffForHumans() }}
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex gap-1 flex-shrink-0">
                                <form method="POST" action="{{ route('uni-admin.users.verify', $user) }}">
                                    @csrf
                                    <button type="submit" class="btn-verify" title="Verify">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('uni-admin.users.reject', $user) }}">
                                    @csrf
                                    <button type="submit" class="btn-reject-sm" title="Reject"
                                        onclick="return confirm('Reject {{ addslashes($user->name) }}\'s registration?')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                <a href="{{ route('uni-admin.users.show', $user) }}" class="btn-view-sm ms-1" title="View profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-person-check"></i>
                            <p>No pending verifications — all caught up!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Penalties --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5>
                        <i class="bi bi-exclamation-triangle"></i>
                        Recent Penalties
                    </h5>
                    <a href="{{ route('uni-admin.penalties.index') }}" class="view-all">View all</a>
                </div>
                <div class="section-card-body">
                    @if ($totalPenaltyAmount > 0)
                        <div class="penalty-summary">
                            <span class="ps-item">
                                <strong>{{ $pendingPenalties }}</strong> pending
                            </span>
                            <span class="ps-item">|</span>
                            <span class="ps-item">
                                <strong>৳{{ number_format($totalPenaltyAmount, 2) }}</strong> outstanding
                            </span>
                        </div>
                    @endif

                    @forelse ($recentPenalties as $penalty)
                        <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom: 1px solid #f3f4f6;">
                            <div class="u-avatar" style="background: #fee2e2; color: #dc2626;">
                                {{ strtoupper(substr($penalty->transaction->borrower->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="u-name text-truncate">
                                    {{ $penalty->transaction->borrower->name ?? 'Unknown' }}
                                </div>
                                <div class="u-email text-truncate">
                                    {{ $penalty->transaction->item->title ?? 'Unknown item' }}
                                </div>
                                <div style="font-size: 0.72rem; color: #9ca3af; margin-top: 2px;">
                                    {{ $penalty->days_late }} day{{ $penalty->days_late > 1 ? 's' : '' }} late
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div style="font-weight: 700; color: #dc2626; font-size: 0.88rem;">
                                    ৳{{ number_format($penalty->amount, 2) }}
                                </div>
                                <a href="{{ route('uni-admin.penalties.show', $penalty) }}" class="btn-view-sm">
                                    Manage
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-emoji-smile"></i>
                            <p>No pending penalties — great news!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── RIGHT COLUMN ────────────────── --}}
        <div class="col-lg-7">

            {{-- Recent Transactions --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5>
                        <i class="bi bi-arrow-left-right"></i>
                        Recent Transactions
                    </h5>
                    <a href="{{ route('uni-admin.transactions.index') }}" class="view-all">View all</a>
                </div>
                <div class="section-card-body">
                    @forelse ($recentTransactions as $txn)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom: 1px solid #f3f4f6;">

                            {{-- Type icon --}}
                            <div class="u-avatar flex-shrink-0"
                                style="
                                    background: {{ $txn->type === 'lend' ? '#dbeafe' : ($txn->type === 'sell' ? '#fce7f3' : '#d1fae5') }};
                                    color: {{ $txn->type === 'lend' ? '#1e40af' : ($txn->type === 'sell' ? '#9d174d' : '#065f46') }};
                                    border-radius: 10px;
                                ">
                                <i class="bi bi-{{ $txn->type === 'lend' ? 'arrow-left-right' : ($txn->type === 'sell' ? 'tag' : 'gift') }}"></i>
                            </div>

                            {{-- Details --}}
                            <div class="flex-grow-1 min-width-0">
                                <div class="u-name text-truncate">
                                    {{ $txn->item->title ?? 'Unknown Item' }}
                                </div>
                                <div class="u-email">
                                    <i class="bi bi-person me-1"></i>{{ $txn->borrower->name ?? '—' }}
                                    <span class="mx-1 text-muted">→</span>
                                    <i class="bi bi-person-check me-1"></i>{{ $txn->owner->name ?? '—' }}
                                </div>
                                @if ($txn->due_date)
                                    <div style="font-size: 0.72rem; margin-top: 2px;
                                        color: {{ $txn->isOverdue() ? '#dc2626' : '#9ca3af' }};">
                                        <i class="bi bi-calendar me-1"></i>
                                        Due: {{ $txn->due_date->format('d M Y') }}
                                        @if ($txn->isOverdue())
                                            <span class="text-danger fw-bold">(Overdue)</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Status & link --}}
                            <div class="text-end flex-shrink-0">
                                <span class="badge-soft {{ $txn->status }}">
                                    {{ ucfirst($txn->status) }}
                                </span>
                                <br>
                                <span class="badge-soft {{ $txn->type }}" style="margin-top: 4px;">
                                    {{ ucfirst($txn->type) }}
                                </span>
                                <br>
                                <a href="{{ route('uni-admin.transactions.show', $txn) }}" class="btn-view-sm mt-1 d-inline-block">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-arrow-left-right"></i>
                            <p>No transactions yet on this campus.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>{{-- end right col --}}

    </div>{{-- end main grid --}}

    {{-- ══════════════════════════════════════
         QUICK LINKS FOOTER ROW
    ══════════════════════════════════════ --}}
    <div class="row g-3 mt-2">
        <div class="col-12">
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 22px;">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span style="font-size: 0.8rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                        Quick Links
                    </span>
                    <a href="{{ route('uni-admin.users.index') }}?status=pending" class="btn btn-sm btn-outline-warning px-3">
                        <i class="bi bi-person-check me-1"></i> Verify Users
                    </a>
                    <a href="{{ route('uni-admin.items.index') }}" class="btn btn-sm btn-outline-primary px-3">
                        <i class="bi bi-grid me-1"></i> Manage Items
                    </a>
                    <a href="{{ route('uni-admin.transactions.index') }}?status=late" class="btn btn-sm btn-outline-danger px-3">
                        <i class="bi bi-clock-history me-1"></i> Late Returns
                    </a>
                    <a href="{{ route('uni-admin.penalties.index') }}?status=pending" class="btn btn-sm btn-outline-danger px-3">
                        <i class="bi bi-exclamation-triangle me-1"></i> Pending Penalties
                    </a>
                    <a href="{{ route('uni-admin.reports.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="bi bi-bar-chart me-1"></i> Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('extra-js')
<script>
    // Auto-confirm forms with confirm dialogs have already been handled inline.
    // Optional: highlight overdue transactions
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.badge-soft.late').forEach(el => {
            el.closest('div[style*="border-bottom"]')?.classList.add('table-danger');
        });
    });
</script>
@endsection