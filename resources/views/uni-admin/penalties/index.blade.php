@extends('layouts.app')

@section('title', 'Manage Penalties — UniShare Admin')

@section('extra-css')
<style>
    /* ── Page header ───────────────────────────────────── */
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

    /* ── Outstanding amount banner ─────────────────────── */
    .outstanding-banner {
        background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
        border: 1px solid #fecaca;
        border-left: 4px solid #dc2626;
        border-radius: 10px;
        padding: 16px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }

    .outstanding-banner .ob-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .outstanding-banner .ob-icon {
        width: 42px;
        height: 42px;
        background: #fee2e2;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #dc2626;
        flex-shrink: 0;
    }

    .outstanding-banner .ob-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .outstanding-banner .ob-amount {
        font-size: 1.4rem;
        font-weight: 800;
        color: #dc2626;
        line-height: 1.1;
    }

    .outstanding-banner .ob-sub {
        font-size: 0.78rem;
        color: #ef4444;
        margin-top: 2px;
    }

    /* ── Filter tabs ───────────────────────────────────── */
    .filter-tabs {
        display: flex;
        gap: 4px;
        background: #f3f4f6;
        border-radius: 10px;
        padding: 4px;
        flex-wrap: wrap;
        width: fit-content;
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

    .filter-tab:hover { background: #e5e7eb; color: #374151; }

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

    /* ── Filter bar ────────────────────────────────────── */
    .filter-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filter-bar .search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    .filter-bar .search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.88rem;
    }

    .filter-bar .search-wrap input {
        width: 100%;
        padding: 7px 12px 7px 34px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        font-size: 0.875rem;
        height: 38px;
        outline: none;
        transition: border-color 0.15s;
    }

    .filter-bar .search-wrap input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
    }

    .btn-filter-reset {
        font-size: 0.8rem;
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        padding: 0 4px;
        white-space: nowrap;
    }

    .btn-filter-reset:hover { color: #0d6efd; }

    /* ── Penalties table ───────────────────────────────── */
    .penalties-table-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .penalties-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.855rem;
    }

    .penalties-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 20px;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }

    .penalties-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s;
    }

    .penalties-table tbody tr:last-child { border-bottom: none; }
    .penalties-table tbody tr:hover { background: #f9fafb; }

    .penalties-table td {
        padding: 14px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* ── Badges ────────────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .badge-soft.pending  { background: #fef9c3; color: #854d0e; }
    .badge-soft.paid     { background: #d1fae5; color: #065f46; }
    .badge-soft.waived   { background: #f3f4f6; color: #6b7280; }

    /* ── Amount display ────────────────────────────────── */
    .amount-cell {
        font-weight: 700;
        font-size: 0.92rem;
    }

    .amount-cell.pending { color: #dc2626; }
    .amount-cell.paid    { color: #059669; }
    .amount-cell.waived  { color: #9ca3af; text-decoration: line-through; }

    /* ── Days-late chip ────────────────────────────────── */
    .days-late-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
    }

    /* ── User chip ─────────────────────────────────────── */
    .user-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f3f4f6;
        border-radius: 20px;
        padding: 3px 12px 3px 4px;
        text-decoration: none;
        color: #374151;
        font-weight: 600;
        font-size: 0.82rem;
        transition: background 0.15s;
    }

    .user-chip:hover { background: #e5e7eb; color: #374151; }

    .user-chip-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
    }

    /* ── Action buttons ────────────────────────────────── */
    .btn-waive {
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 7px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .btn-waive:hover { background: #e5e7eb; }

    .btn-paid {
        background: #d1fae5;
        color: #065f46;
        border: none;
        border-radius: 7px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .btn-paid:hover { background: #a7f3d0; }

    .btn-view-sm {
        color: #0d6efd;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: color 0.15s;
    }

    .btn-view-sm:hover { color: #0a58ca; }

    /* ── Item reference ────────────────────────────────── */
    .item-ref {
        font-size: 0.82rem;
        color: #1a1f36;
        font-weight: 500;
    }

    .item-ref-sub {
        font-size: 0.73rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Empty state ───────────────────────────────────── */
    .empty-state {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        text-align: center;
        padding: 64px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 2.8rem;
        display: block;
        margin-bottom: 14px;
        color: #d1d5db;
    }

    .empty-state p {
        font-size: 0.9rem;
        margin: 0;
    }

    /* ── Pagination ────────────────────────────────────── */
    .pagination-wrap {
        margin-top: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 0.82rem;
        color: #6b7280;
    }

    /* ── Flash messages ────────────────────────────────── */
    .flash-alert {
        border-radius: 9px;
        padding: 12px 18px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .flash-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .flash-warning {
        background: #fef9c3;
        color: #854d0e;
        border: 1px solid #fde68a;
    }

    @media (max-width: 768px) {
        .penalties-table thead { display: none; }
        .penalties-table tbody tr { display: block; padding: 12px 0; }
        .penalties-table td {
            display: flex;
            justify-content: space-between;
            padding: 6px 16px;
            border-bottom: none;
        }
        .outstanding-banner { flex-direction: column; align-items: flex-start; }
        .filter-tabs { width: 100%; }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flash-alert flash-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="flash-alert flash-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('warning') }}
        </div>
    @endif

    {{-- ══════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════ --}}
    <div class="ua-page-header">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Manage Penalties</h1>
                <p>Track and resolve late-return penalties within your university.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-secondary border px-3 py-2" style="font-size: 0.8rem;">
                    <i class="bi bi-list me-1"></i> {{ $counts['all'] }} total
                </span>
                <span class="badge px-3 py-2" style="background: #fef9c3; color: #854d0e; font-size: 0.8rem;">
                    <i class="bi bi-clock me-1"></i> {{ $counts['pending'] }} pending
                </span>
                <span class="badge px-3 py-2" style="background: #d1fae5; color: #065f46; font-size: 0.8rem;">
                    <i class="bi bi-check-circle me-1"></i> {{ $counts['paid'] }} paid
                </span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         OUTSTANDING AMOUNT BANNER
    ══════════════════════════════════════ --}}
    @if ($totalOutstanding > 0)
        <div class="outstanding-banner">
            <div class="ob-left">
                <div class="ob-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="ob-label">Total Outstanding</div>
                    <div class="ob-amount">৳{{ number_format($totalOutstanding, 2) }}</div>
                    <div class="ob-sub">{{ $counts['pending'] }} unpaid {{ Str::plural('penalty', $counts['pending']) }} awaiting resolution</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         STATUS TABS
    ══════════════════════════════════════ --}}
    <div class="mb-4">
        <div class="filter-tabs">
            @php
                $tabs = [
                    'all'     => ['label' => 'All',    'icon' => 'list'],
                    'pending' => ['label' => 'Pending', 'icon' => 'clock'],
                    'paid'    => ['label' => 'Paid',    'icon' => 'check-circle'],
                    'waived'  => ['label' => 'Waived',  'icon' => 'slash-circle'],
                ];
            @endphp

            @foreach ($tabs as $key => $tab)
                <a href="{{ route('uni-admin.penalties.index') }}?status={{ $key }}&search={{ request('search') }}"
                   class="filter-tab {{ request('status', 'pending') === $key ? 'active' : '' }}">
                    <i class="bi bi-{{ $tab['icon'] }}" style="font-size: 0.75rem;"></i>
                    {{ $tab['label'] }}
                    <span class="tab-count">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════
         FILTER BAR
    ══════════════════════════════════════ --}}
    <form method="GET" action="{{ route('uni-admin.penalties.index') }}">
        <input type="hidden" name="status" value="{{ request('status', 'pending') }}">

        <div class="filter-bar">
            {{-- Search by borrower name --}}
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by borrower name…"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary btn-sm px-3" style="height: 38px; font-size: 0.85rem;">
                <i class="bi bi-search me-1"></i> Search
            </button>

            @if (request('search'))
                <a href="{{ route('uni-admin.penalties.index') }}?status={{ request('status', 'pending') }}"
                   class="btn-filter-reset">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ══════════════════════════════════════
         PENALTIES TABLE
    ══════════════════════════════════════ --}}
    @if ($penalties->count())

        <div class="penalties-table-wrap">
            <table class="penalties-table">
                <thead>
                    <tr>
                        <th>Borrower</th>
                        <th>Item</th>
                        <th>Days Late</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Issued</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penalties as $penalty)
                        <tr>

                            {{-- Borrower --}}
                            <td>
                                @if ($penalty->transaction->borrower ?? null)
                                    <a href="{{ route('uni-admin.users.show', $penalty->transaction->borrower) }}"
                                       class="user-chip">
                                        <span class="user-chip-avatar">
                                            {{ strtoupper(substr($penalty->transaction->borrower->name, 0, 1)) }}
                                        </span>
                                        {{ $penalty->transaction->borrower->name }}
                                    </a>
                                @else
                                    <span style="font-size: 0.82rem; color: #9ca3af;">Deleted user</span>
                                @endif
                            </td>

                            {{-- Item --}}
                            <td>
                                @if ($penalty->transaction->item ?? null)
                                    <div class="item-ref">{{ $penalty->transaction->item->title }}</div>
                                    <div class="item-ref-sub">
                                        <a href="{{ route('uni-admin.transactions.show', $penalty->transaction) }}"
                                           style="color: #0d6efd; font-size: 0.73rem; text-decoration: none;">
                                            <i class="bi bi-arrow-left-right me-1"></i>View transaction
                                        </a>
                                    </div>
                                @else
                                    <span style="font-size: 0.82rem; color: #9ca3af;">Deleted item</span>
                                @endif
                            </td>

                            {{-- Days Late --}}
                            <td>
                                <span class="days-late-chip">
                                    <i class="bi bi-clock-history" style="font-size: 0.68rem;"></i>
                                    {{ $penalty->days_late }} {{ Str::plural('day', $penalty->days_late) }}
                                </span>
                            </td>

                            {{-- Amount --}}
                            <td>
                                <span class="amount-cell {{ $penalty->status }}">
                                    ৳{{ number_format($penalty->amount, 2) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge-soft {{ $penalty->status }}">
                                    @if ($penalty->status === 'pending')
                                        <i class="bi bi-clock" style="font-size: 0.65rem;"></i>
                                    @elseif ($penalty->status === 'paid')
                                        <i class="bi bi-check-circle" style="font-size: 0.65rem;"></i>
                                    @else
                                        <i class="bi bi-slash-circle" style="font-size: 0.65rem;"></i>
                                    @endif
                                    {{ ucfirst($penalty->status) }}
                                </span>
                            </td>

                            {{-- Issued date --}}
                            <td style="font-size: 0.8rem; color: #6b7280;">
                                {{ $penalty->created_at->format('d M Y') }}
                                <div style="font-size: 0.72rem; color: #9ca3af;">
                                    {{ $penalty->created_at->diffForHumans() }}
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">

                                    {{-- View detail --}}
                                    <a href="{{ route('uni-admin.penalties.show', $penalty) }}" class="btn-view-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>

                                    @if ($penalty->isPending())
                                        {{-- Mark as Paid --}}
                                        <form method="POST" action="{{ route('uni-admin.penalties.mark-paid', $penalty) }}">
                                            @csrf
                                            <button type="submit" class="btn-paid"
                                                onclick="return confirm('Confirm this penalty of ৳{{ number_format($penalty->amount, 2) }} has been paid?')">
                                                <i class="bi bi-check-circle" style="font-size: 0.75rem;"></i> Paid
                                            </button>
                                        </form>

                                        {{-- Waive --}}
                                        <form method="POST" action="{{ route('uni-admin.penalties.waive', $penalty) }}">
                                            @csrf
                                            <button type="submit" class="btn-waive"
                                                onclick="return confirm('Waive this penalty of ৳{{ number_format($penalty->amount, 2) }}? This cannot be undone.')">
                                                <i class="bi bi-slash-circle" style="font-size: 0.75rem;"></i> Waive
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

        {{-- Pagination --}}
        @if ($penalties->hasPages())
            <div class="pagination-wrap">
                <span class="pagination-info">
                    Showing {{ $penalties->firstItem() }}–{{ $penalties->lastItem() }} of {{ $penalties->total() }} penalties
                </span>
                {{ $penalties->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif

    @else
        <div class="empty-state">
            <i class="bi bi-check-circle"></i>
            <p>
                @if (request('search'))
                    No penalties found for "<strong>{{ request('search') }}</strong>".
                @elseif (request('status') === 'pending')
                    No outstanding penalties — all clear!
                @elseif (request('status') === 'paid')
                    No paid penalties recorded yet.
                @elseif (request('status') === 'waived')
                    No waived penalties recorded yet.
                @else
                    No penalties have been issued yet.
                @endif
            </p>
            @if (request('search'))
                <a href="{{ route('uni-admin.penalties.index') }}?status={{ request('status', 'pending') }}"
                   class="btn btn-outline-primary btn-sm mt-3">
                    Clear Search
                </a>
            @endif
        </div>
    @endif

</div>
@endsection