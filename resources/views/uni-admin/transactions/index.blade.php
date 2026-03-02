@extends('layouts.app')

@section('title', 'Transactions — UniShare Admin')

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

    .filter-tab.tab-late.active { color: #dc2626; }
    .filter-tab.tab-late.active .tab-count { background: #fee2e2; color: #991b1b; }

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

    .search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    .search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.88rem;
    }

    .search-wrap input {
        width: 100%;
        padding: 7px 12px 7px 34px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        font-size: 0.875rem;
        height: 38px;
        outline: none;
        transition: border-color 0.15s;
    }

    .search-wrap input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
    }

    .filter-bar select {
        padding: 7px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        font-size: 0.85rem;
        height: 38px;
        color: #374151;
        outline: none;
        background: #fff;
        cursor: pointer;
    }

    .filter-bar select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
    }

    .btn-filter-reset {
        font-size: 0.8rem;
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        white-space: nowrap;
    }

    .btn-filter-reset:hover { color: #0d6efd; }

    /* ── Main table card ───────────────────────────────── */
    .table-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    /* ── Table ─────────────────────────────────────────── */
    .txn-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.855rem;
    }

    .txn-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 11px 20px;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .txn-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s;
    }

    .txn-table tbody tr:last-child { border-bottom: none; }
    .txn-table tbody tr:hover { background: #f9fafb; }
    .txn-table tbody tr.row-late { background: #fff5f5; }
    .txn-table tbody tr.row-late:hover { background: #fee2e2; }

    .txn-table td {
        padding: 13px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* ── Item cell ─────────────────────────────────────── */
    .item-thumb {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #9ca3af;
        flex-shrink: 0;
        overflow: hidden;
    }

    .item-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-title-text {
        font-weight: 600;
        color: #1a1f36;
        font-size: 0.875rem;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ── User chips ────────────────────────────────────── */
    .user-chip-sm {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.78rem;
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        transition: color 0.15s;
    }

    .user-chip-sm:hover { color: #0d6efd; }

    .u-avatar-xs {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #e7f1ff;
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ── Badges ────────────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .badge-soft.pending   { background: #fef9c3; color: #854d0e; }
    .badge-soft.active    { background: #dbeafe; color: #1e40af; }
    .badge-soft.completed { background: #d1fae5; color: #065f46; }
    .badge-soft.late      { background: #fee2e2; color: #991b1b; }
    .badge-soft.cancelled { background: #f3f4f6; color: #6b7280; }
    .badge-soft.lend      { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell      { background: #fce7f3; color: #9d174d; }
    .badge-soft.share     { background: #d1fae5; color: #065f46; }

    /* ── Overdue pill ──────────────────────────────────── */
    .overdue-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.68rem;
        font-weight: 700;
        color: #dc2626;
        background: #fee2e2;
        border-radius: 20px;
        padding: 2px 8px;
        margin-top: 3px;
        white-space: nowrap;
    }

    /* ── View button ───────────────────────────────────── */
    .btn-view {
        color: #0d6efd;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        padding: 5px 12px;
        border-radius: 6px;
        background: #e7f1ff;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: background 0.15s;
        white-space: nowrap;
    }

    .btn-view:hover { background: #dbeafe; color: #0d6efd; }

    /* ── Late transactions alert ───────────────────────── */
    .late-alert {
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-left: 4px solid #dc2626;
        border-radius: 10px;
        padding: 13px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        font-size: 0.875rem;
        color: #7f1d1d;
        flex-wrap: wrap;
    }

    .late-alert i { font-size: 1.1rem; color: #dc2626; flex-shrink: 0; }

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

    .empty-state p { font-size: 0.9rem; margin: 0; }

    /* ── Pagination ────────────────────────────────────── */
    .pagination-wrap {
        padding: 16px 20px;
        border-top: 1px solid #f3f4f6;
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

    /* ── Responsive ────────────────────────────────────── */
    @media (max-width: 992px) {
        .txn-table thead th:nth-child(4),
        .txn-table td:nth-child(4) { display: none; }
    }

    @media (max-width: 768px) {
        .txn-table thead th:nth-child(3),
        .txn-table td:nth-child(3) { display: none; }
        .filter-tabs { width: 100%; }
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
                <h1><i class="bi bi-arrow-left-right me-2 text-primary"></i>Transactions</h1>
                <p>Monitor all lending, selling, and sharing activity within your university.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge px-3 py-2" style="background: #dbeafe; color: #1e40af; font-size: 0.8rem;">
                    <i class="bi bi-arrow-repeat me-1"></i> {{ $counts['active'] }} active
                </span>
                <span class="badge px-3 py-2" style="background: #fee2e2; color: #991b1b; font-size: 0.8rem;">
                    <i class="bi bi-clock-history me-1"></i> {{ $counts['late'] }} late
                </span>
                <span class="badge px-3 py-2" style="background: #d1fae5; color: #065f46; font-size: 0.8rem;">
                    <i class="bi bi-check-circle me-1"></i> {{ $counts['completed'] }} completed
                </span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         LATE RETURNS ALERT
    ══════════════════════════════════════ --}}
    @if ($counts['late'] > 0)
        <div class="late-alert">
            <i class="bi bi-alarm-fill"></i>
            <div class="flex-grow-1">
                <strong>{{ $counts['late'] }} overdue return{{ $counts['late'] > 1 ? 's' : '' }}</strong>
                — these borrowers have not returned items past their due date.
            </div>
            <a href="{{ route('uni-admin.transactions.index') }}?status=late"
               class="btn btn-danger btn-sm px-3" style="font-size: 0.8rem;">
                View Late Returns
            </a>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         STATUS TABS
    ══════════════════════════════════════ --}}
    <div class="mb-4">
        <div class="filter-tabs">
            @php
                $currentStatus = request('status', 'all');
                $tabs = [
                    'all'       => ['label' => 'All',       'icon' => 'list-ul'],
                    'active'    => ['label' => 'Active',    'icon' => 'arrow-repeat'],
                    'late'      => ['label' => 'Late',      'icon' => 'alarm'],
                    'completed' => ['label' => 'Completed', 'icon' => 'check-circle'],
                    'cancelled' => ['label' => 'Cancelled', 'icon' => 'x-circle'],
                ];
            @endphp

            @foreach ($tabs as $key => $tab)
                <a href="{{ route('uni-admin.transactions.index') }}?status={{ $key }}&type={{ request('type') }}&search={{ request('search') }}"
                   class="filter-tab {{ $key === 'late' ? 'tab-late' : '' }} {{ $currentStatus === $key ? 'active' : '' }}">
                    <i class="bi bi-{{ $tab['icon'] }}" style="font-size: 0.75rem;"></i>
                    {{ $tab['label'] }}
                    <span class="tab-count">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════
         FILTER BAR
    ══════════════════════════════════════ --}}
    <form method="GET" action="{{ route('uni-admin.transactions.index') }}">
        <input type="hidden" name="status" value="{{ $currentStatus }}">
        <div class="filter-bar">

            {{-- Search by item title --}}
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by item title…"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>

            {{-- Type filter --}}
            <select name="type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="lend"  {{ request('type') === 'lend'  ? 'selected' : '' }}>Lending</option>
                <option value="sell"  {{ request('type') === 'sell'  ? 'selected' : '' }}>Selling</option>
                <option value="share" {{ request('type') === 'share' ? 'selected' : '' }}>Free / Share</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm px-3" style="height: 38px; font-size: 0.85rem;">
                <i class="bi bi-search me-1"></i> Search
            </button>

            @if (request('search') || request('type'))
                <a href="{{ route('uni-admin.transactions.index') }}?status={{ $currentStatus }}"
                   class="btn-filter-reset">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ══════════════════════════════════════
         TRANSACTIONS TABLE
    ══════════════════════════════════════ --}}
    <div class="table-card">
        @if ($transactions->count())
            <table class="txn-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Borrower</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $txn)
                        <tr class="{{ $txn->status === 'late' ? 'row-late' : '' }}">

                            {{-- Item --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="item-thumb">
                                        @if ($txn->item?->image_path)
                                            <img src="{{ $txn->item->image_url }}" alt="">
                                        @else
                                            <i class="bi bi-box-seam"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="item-title-text">
                                            {{ $txn->item->title ?? 'Deleted Item' }}
                                        </div>
                                        @if ($txn->item)
                                            <div style="font-size: 0.72rem; color: #9ca3af;">
                                                <i class="bi bi-pin-map me-1"></i>
                                                {{ $txn->item->pickup_location ?? 'No location set' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Borrower --}}
                            <td>
                                @if ($txn->borrower)
                                    <a href="{{ route('uni-admin.users.show', $txn->borrower) }}"
                                       class="user-chip-sm">
                                        <span class="u-avatar-xs">
                                            {{ strtoupper(substr($txn->borrower->name, 0, 1)) }}
                                        </span>
                                        {{ $txn->borrower->name }}
                                    </a>
                                @else
                                    <span style="color: #9ca3af; font-size: 0.8rem;">Deleted user</span>
                                @endif
                            </td>

                            {{-- Owner --}}
                            <td>
                                @if ($txn->owner)
                                    <a href="{{ route('uni-admin.users.show', $txn->owner) }}"
                                       class="user-chip-sm">
                                        <span class="u-avatar-xs" style="background: #d1fae5; color: #065f46;">
                                            {{ strtoupper(substr($txn->owner->name, 0, 1)) }}
                                        </span>
                                        {{ $txn->owner->name }}
                                    </a>
                                @else
                                    <span style="color: #9ca3af; font-size: 0.8rem;">—</span>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td>
                                <span class="badge-soft {{ $txn->type }}">
                                    <i class="bi bi-{{ $txn->type === 'lend' ? 'arrow-left-right' : ($txn->type === 'sell' ? 'tag' : 'gift') }}"></i>
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge-soft {{ $txn->status }}">
                                    {{ $txn->getStatusLabel() }}
                                </span>
                            </td>

                            {{-- Due date --}}
                            <td style="font-size: 0.82rem;">
                                @if ($txn->due_date)
                                    <div class="{{ $txn->isOverdue() ? 'text-danger fw-bold' : 'text-secondary' }}">
                                        {{ $txn->due_date->format('d M Y') }}
                                    </div>
                                    @if ($txn->isOverdue())
                                        <div class="overdue-pill">
                                            <i class="bi bi-alarm"></i>
                                            {{ $txn->daysOverdue() }}d overdue
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #9ca3af;">—</span>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td style="font-size: 0.85rem;">
                                @if ($txn->final_price)
                                    <span style="font-weight: 600; color: #059669;">
                                        {{ $txn->formatted_price }}
                                    </span>
                                @elseif ($txn->deposit_amount)
                                    <div style="font-weight: 600; color: #374151;">
                                        {{ $txn->formatted_deposit }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #9ca3af;">deposit</div>
                                @else
                                    <span style="color: #9ca3af;">—</span>
                                @endif
                            </td>

                            {{-- View --}}
                            <td>
                                <a href="{{ route('uni-admin.transactions.show', $txn) }}"
                                   class="btn-view">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($transactions->hasPages())
                <div class="pagination-wrap">
                    <span class="pagination-info">
                        Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
                        of {{ $transactions->total() }} transactions
                    </span>
                    {{ $transactions->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @else
            <div class="empty-state">
                @if (request('search'))
                    <i class="bi bi-search"></i>
                    <p>No transactions found matching "<strong>{{ request('search') }}</strong>".</p>
                @elseif ($currentStatus === 'late')
                    <i class="bi bi-check-circle" style="color: #059669;"></i>
                    <p>No late returns — everything is on time!</p>
                @elseif ($currentStatus === 'active')
                    <i class="bi bi-arrow-repeat"></i>
                    <p>No active transactions right now.</p>
                @else
                    <i class="bi bi-arrow-left-right"></i>
                    <p>No transactions found.</p>
                @endif
                @if (request('search') || request('type'))
                    <a href="{{ route('uni-admin.transactions.index') }}?status={{ $currentStatus }}"
                       class="btn btn-outline-primary btn-sm mt-3">
                        Clear Filters
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection