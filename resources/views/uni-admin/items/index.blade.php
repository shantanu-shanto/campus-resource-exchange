@extends('layouts.app')

@section('title', 'Manage Items — UniShare Admin')

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
        padding: 0 4px;
        white-space: nowrap;
    }

    .btn-filter-reset:hover { color: #0d6efd; }

    /* ── Items grid ────────────────────────────────────── */
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 18px;
    }

    /* ── Item card ─────────────────────────────────────── */
    .item-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .item-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.09);
        transform: translateY(-2px);
    }

    /* Image / placeholder */
    .item-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        background: #f3f4f6;
    }

    .item-img-placeholder {
        width: 100%;
        height: 160px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #d1d5db;
    }

    .item-card-body {
        padding: 16px 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .item-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1f36;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        margin: 0;
    }

    .item-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #6b7280;
        flex-wrap: wrap;
    }

    .item-meta .owner-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f3f4f6;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #374151;
    }

    .item-meta .owner-avatar {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
    }

    .item-badges {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
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

    .badge-soft.available { background: #d1fae5; color: #065f46; }
    .badge-soft.borrowed  { background: #fef9c3; color: #854d0e; }
    .badge-soft.sold      { background: #f3f4f6; color: #6b7280; }
    .badge-soft.reserved  { background: #dbeafe; color: #1e40af; }
    .badge-soft.lend      { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell      { background: #fce7f3; color: #9d174d; }
    .badge-soft.share     { background: #d1fae5; color: #065f46; }
    .badge-soft.both      { background: #ede9fe; color: #6d28d9; }

    /* ── Price tag ─────────────────────────────────────── */
    .price-tag {
        font-size: 0.88rem;
        font-weight: 700;
        color: #059669;
    }

    .price-tag.free {
        color: #065f46;
        background: #d1fae5;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* ── Card footer actions ───────────────────────────── */
    .item-card-footer {
        padding: 12px 18px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-view {
        flex: 1;
        background: #e7f1ff;
        color: #0d6efd;
        border: none;
        border-radius: 7px;
        padding: 6px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: background 0.15s;
    }

    .btn-view:hover { background: #dbeafe; color: #0d6efd; }

    .btn-flag {
        background: #fff7ed;
        color: #ea580c;
        border: none;
        border-radius: 7px;
        padding: 6px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-flag:hover { background: #ffedd5; }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
    }

    .btn-delete:hover { background: #fecaca; }

    /* ── Flagged state overlay ─────────────────────────── */
    .flagged-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc2626;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.03em;
    }

    .item-img-wrap {
        position: relative;
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

    @media (max-width: 576px) {
        .items-grid { grid-template-columns: 1fr; }
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
                <h1><i class="bi bi-grid me-2 text-primary"></i>Manage Items</h1>
                <p>Oversee all listings within your university — flag or remove policy-violating items.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-secondary border px-3 py-2" style="font-size: 0.8rem;">
                    <i class="bi bi-grid me-1"></i> {{ $counts['all'] }} total
                </span>
                <span class="badge px-3 py-2" style="background: #d1fae5; color: #065f46; font-size: 0.8rem;">
                    <i class="bi bi-check-circle me-1"></i> {{ $counts['available'] }} available
                </span>
                <span class="badge px-3 py-2" style="background: #fef9c3; color: #854d0e; font-size: 0.8rem;">
                    <i class="bi bi-arrow-left-right me-1"></i> {{ $counts['borrowed'] }} borrowed
                </span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         STATUS TABS
    ══════════════════════════════════════ --}}
    <div class="mb-4">
        <div class="filter-tabs">
            @php
                $tabs = [
                    'all'       => ['label' => 'All',       'icon' => 'grid'],
                    'available' => ['label' => 'Available', 'icon' => 'check-circle'],
                    'borrowed'  => ['label' => 'Borrowed',  'icon' => 'arrow-left-right'],
                    'sold'      => ['label' => 'Sold',      'icon' => 'bag-check'],
                ];
            @endphp

            @foreach ($tabs as $key => $tab)
                <a href="{{ route('uni-admin.items.index') }}?status={{ $key }}&mode={{ request('mode') }}&search={{ request('search') }}"
                   class="filter-tab {{ request('status', 'all') === $key ? 'active' : '' }}">
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
    <form method="GET" action="{{ route('uni-admin.items.index') }}">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">

        <div class="filter-bar">
            {{-- Search --}}
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by title or description…"
                       value="{{ request('search') }}"
                       autocomplete="off">
            </div>

            {{-- Mode filter --}}
            <select name="mode" onchange="this.form.submit()">
                <option value="">All Modes</option>
                <option value="lend"  {{ request('mode') === 'lend'  ? 'selected' : '' }}>Lending</option>
                <option value="sell"  {{ request('mode') === 'sell'  ? 'selected' : '' }}>Selling</option>
                <option value="share" {{ request('mode') === 'share' ? 'selected' : '' }}>Free / Share</option>
                <option value="both"  {{ request('mode') === 'both'  ? 'selected' : '' }}>Lend & Sell</option>
            </select>

            {{-- Submit / Reset --}}
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height: 38px; font-size: 0.85rem;">
                <i class="bi bi-search me-1"></i> Search
            </button>

            @if (request('search') || request('mode'))
                <a href="{{ route('uni-admin.items.index') }}?status={{ request('status', 'all') }}"
                   class="btn-filter-reset">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ══════════════════════════════════════
         ITEMS GRID
    ══════════════════════════════════════ --}}
    @if ($items->count())

        <div class="items-grid">
            @foreach ($items as $item)
                <div class="item-card">

                    {{-- Image --}}
                    <div class="item-img-wrap">
                        @if ($item->image_path)
                            <img src="{{ $item->image_url }}"
                                 alt="{{ $item->title }}"
                                 class="item-img">
                        @else
                            <div class="item-img-placeholder">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        @endif

                        {{-- Flagged badge --}}
                        @if ($item->status === 'reserved')
                            <span class="flagged-overlay">
                                <i class="bi bi-flag-fill me-1"></i>Flagged
                            </span>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="item-card-body">

                        <h6 class="item-title">{{ $item->title }}</h6>

                        {{-- Owner chip --}}
                        <div class="item-meta">
                            <span class="owner-chip">
                                <span class="owner-avatar">
                                    {{ strtoupper(substr($item->owner->name ?? '?', 0, 1)) }}
                                </span>
                                {{ $item->owner->name ?? 'Unknown' }}
                            </span>
                        </div>

                        {{-- Badges row --}}
                        <div class="item-badges">
                            <span class="badge-soft {{ $item->status }}">
                                {{ $item->getStatusLabel() }}
                            </span>
                            <span class="badge-soft {{ $item->availability_mode }}">
                                {{ $item->getAvailabilityModeLabel() }}
                            </span>
                        </div>

                        {{-- Price + meta --}}
                        <div class="d-flex align-items-center justify-content-between" style="margin-top: auto;">
                            @if ($item->isFree())
                                <span class="price-tag free"><i class="bi bi-gift me-1"></i>Free</span>
                            @elseif ($item->price)
                                <span class="price-tag">৳{{ number_format($item->price, 2) }}</span>
                            @else
                                <span style="font-size: 0.78rem; color: #9ca3af;">No price set</span>
                            @endif

                            @if ($item->lending_duration_days)
                                <span style="font-size: 0.75rem; color: #9ca3af;">
                                    <i class="bi bi-calendar2 me-1"></i>{{ $item->lending_duration_days }}d max
                                </span>
                            @endif
                        </div>

                        {{-- Listed date --}}
                        <div style="font-size: 0.74rem; color: #9ca3af;">
                            <i class="bi bi-clock me-1"></i>Listed {{ $item->created_at->diffForHumans() }}
                        </div>

                    </div>

                    {{-- Footer actions --}}
                    <div class="item-card-footer">

                        {{-- View --}}
                        <a href="{{ route('uni-admin.items.show', $item) }}" class="btn-view">
                            <i class="bi bi-eye"></i> View
                        </a>

                        {{-- Flag (only if not already flagged/sold) --}}
                        @if (!in_array($item->status, ['reserved', 'sold']))
                            <form method="POST" action="{{ route('uni-admin.items.flag', $item) }}">
                                @csrf
                                <button type="submit" class="btn-flag"
                                    onclick="return confirm('Flag and hide \'{{ addslashes($item->title) }}\' from listings?')">
                                    <i class="bi bi-flag"></i> Flag
                                </button>
                            </form>
                        @endif

                        {{-- Delete --}}
                        @if (!$item->activeTransaction)
                            <form method="POST" action="{{ route('uni-admin.items.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete"
                                    onclick="return confirm('Permanently delete \'{{ addslashes($item->title) }}\'? This cannot be undone.')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @else
                            <button class="btn-delete" disabled title="Cannot delete — active transaction exists"
                                style="opacity: 0.4; cursor: not-allowed;">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif

                    </div>

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div class="pagination-wrap">
                <span class="pagination-info">
                    Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} items
                </span>
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif

    @else
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <p>
                @if (request('search'))
                    No items found matching "<strong>{{ request('search') }}</strong>".
                @elseif (request('mode'))
                    No items with the selected mode.
                @else
                    No items found in this category.
                @endif
            </p>
            @if (request('search') || request('mode'))
                <a href="{{ route('uni-admin.items.index') }}" class="btn btn-outline-primary btn-sm mt-3">
                    Clear Filters
                </a>
            @endif
        </div>
    @endif

</div>
@endsection