@extends('layouts.app')

@section('title', $item->title . ' — Item Detail')

@section('extra-css')
<style>
    /* ── Back link ─────────────────────────────────────── */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 20px;
        transition: color 0.15s;
    }

    .back-link:hover { color: #0d6efd; }

    /* ── Item hero card ────────────────────────────────── */
    .item-hero {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .item-hero-inner {
        display: flex;
        gap: 0;
        flex-wrap: wrap;
    }

    /* Image panel */
    .item-hero-image {
        width: 300px;
        flex-shrink: 0;
        min-height: 240px;
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .item-hero-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-hero-image .no-img {
        font-size: 4rem;
        color: #d1d5db;
    }

    /* Detail panel */
    .item-hero-details {
        flex: 1;
        padding: 26px 28px;
        min-width: 260px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .item-hero-details h2 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0;
        line-height: 1.35;
    }

    .item-description {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.6;
        margin: 0;
    }

    /* ── Badges ────────────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 4px 11px;
        border-radius: 20px;
    }

    .badge-soft.available { background: #d1fae5; color: #065f46; }
    .badge-soft.borrowed  { background: #fef9c3; color: #854d0e; }
    .badge-soft.sold      { background: #f3f4f6; color: #6b7280; }
    .badge-soft.reserved  { background: #fee2e2; color: #991b1b; }
    .badge-soft.lend      { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell      { background: #fce7f3; color: #9d174d; }
    .badge-soft.share     { background: #d1fae5; color: #065f46; }
    .badge-soft.both      { background: #ede9fe; color: #6d28d9; }
    .badge-soft.pending   { background: #fef9c3; color: #854d0e; }
    .badge-soft.active    { background: #dbeafe; color: #1e40af; }
    .badge-soft.completed { background: #d1fae5; color: #065f46; }
    .badge-soft.late      { background: #fee2e2; color: #991b1b; }
    .badge-soft.cancelled { background: #f3f4f6; color: #6b7280; }

    /* ── Key-value pairs in hero ───────────────────────── */
    .kv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }

    .kv-item {
        background: #f9fafb;
        border-radius: 8px;
        padding: 10px 14px;
    }

    .kv-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .kv-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1a1f36;
    }

    /* ── Hero actions ──────────────────────────────────── */
    .hero-actions {
        display: flex;
        gap: 8px;
        padding: 16px 28px;
        border-top: 1px solid #f3f4f6;
        flex-wrap: wrap;
        background: #fafafa;
    }

    .btn-flag {
        background: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
        border-radius: 7px;
        padding: 7px 18px;
        font-size: 0.83rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-flag:hover { background: #ffedd5; }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 7px;
        padding: 7px 18px;
        font-size: 0.83rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-delete:hover { background: #fecaca; }

    /* ── Section card ──────────────────────────────────── */
    .section-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .section-card-header {
        padding: 14px 22px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-card-header h5 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-card-header h5 i { color: #0d6efd; }

    .section-card-header .sub-count {
        font-size: 0.78rem;
        color: #9ca3af;
    }

    /* ── Detail table ──────────────────────────────────── */
    .detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.855rem;
    }

    .detail-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 10px 20px;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }

    .detail-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s;
    }

    .detail-table tbody tr:last-child { border-bottom: none; }
    .detail-table tbody tr:hover { background: #f9fafb; }

    .detail-table td {
        padding: 12px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* ── Info rows ─────────────────────────────────────── */
    .info-row {
        display: flex;
        align-items: flex-start;
        padding: 12px 22px;
        border-bottom: 1px solid #f3f4f6;
        gap: 16px;
    }

    .info-row:last-child { border-bottom: none; }

    .info-label {
        font-size: 0.76rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        min-width: 130px;
        padding-top: 1px;
        flex-shrink: 0;
    }

    .info-value {
        font-size: 0.875rem;
        color: #1a1f36;
        font-weight: 500;
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

    /* ── Star rating ───────────────────────────────────── */
    .star-rating {
        display: inline-flex;
        gap: 2px;
        font-size: 0.85rem;
    }

    .star-filled { color: #f59e0b; }
    .star-empty  { color: #e5e7eb; }

    /* ── Rating row ────────────────────────────────────── */
    .rating-row {
        padding: 14px 22px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .rating-row:last-child { border-bottom: none; }

    .rater-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #e7f1ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .rating-comment {
        font-size: 0.82rem;
        color: #6b7280;
        margin-top: 4px;
        font-style: italic;
    }

    /* ── Empty state ───────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 36px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 1.8rem;
        display: block;
        margin-bottom: 8px;
        color: #d1d5db;
    }

    .empty-state p {
        font-size: 0.85rem;
        margin: 0;
    }

    /* ── Active borrower banner ────────────────────────── */
    .active-banner {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        font-size: 0.875rem;
        color: #78350f;
        flex-wrap: wrap;
    }

    .active-banner i { font-size: 1.1rem; color: #f59e0b; flex-shrink: 0; }

    /* ── Flagged warning ───────────────────────────────── */
    .flagged-warning {
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-left: 4px solid #dc2626;
        border-radius: 8px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        font-size: 0.875rem;
        color: #7f1d1d;
    }

    .flagged-warning i { font-size: 1.1rem; color: #dc2626; flex-shrink: 0; }

    @media (max-width: 768px) {
        .item-hero-image { width: 100%; height: 220px; }
        .item-hero-details { padding: 20px; }
        .kv-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- Back link --}}
    <a href="{{ route('uni-admin.items.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Items
    </a>

    {{-- ══════════════════════════════════════
         FLAGGED WARNING
    ══════════════════════════════════════ --}}
    @if ($item->status === 'reserved')
        <div class="flagged-warning">
            <i class="bi bi-flag-fill"></i>
            <div>
                <strong>This item has been flagged.</strong>
                It is currently hidden from listings and unavailable to users.
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         ACTIVE BORROWER BANNER
    ══════════════════════════════════════ --}}
    @if ($item->activeTransaction)
        @php $activeTxn = $item->activeTransaction; @endphp
        <div class="active-banner">
            <i class="bi bi-arrow-repeat"></i>
            <div class="flex-grow-1">
                <strong>Currently {{ $activeTxn->status === 'pending' ? 'reserved for' : 'borrowed by' }}
                {{ $activeTxn->borrower->name ?? 'Unknown' }}.</strong>
                @if ($activeTxn->due_date)
                    Due back on <strong>{{ $activeTxn->due_date->format('d M Y') }}</strong>.
                    @if ($activeTxn->isOverdue())
                        <span class="text-danger fw-bold ms-1">— OVERDUE</span>
                    @endif
                @endif
            </div>
            <a href="{{ route('uni-admin.transactions.show', $activeTxn) }}"
               class="btn btn-warning btn-sm px-3" style="font-size: 0.8rem;">
                View Transaction
            </a>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         ITEM HERO CARD
    ══════════════════════════════════════ --}}
    <div class="item-hero">
        <div class="item-hero-inner">

            {{-- Image --}}
            <div class="item-hero-image">
                @if ($item->image_path)
                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                @else
                    <div class="no-img"><i class="bi bi-box-seam"></i></div>
                @endif
            </div>

            {{-- Details --}}
            <div class="item-hero-details">
                {{-- Title + badges --}}
                <div>
                    <h2>{{ $item->title }}</h2>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="badge-soft {{ $item->status }}">
                            {{ $item->getStatusLabel() }}
                        </span>
                        <span class="badge-soft {{ $item->availability_mode }}">
                            {{ $item->getAvailabilityModeLabel() }}
                        </span>
                    </div>
                </div>

                {{-- Description --}}
                @if ($item->description)
                    <p class="item-description">{{ $item->description }}</p>
                @endif

                {{-- Key-value grid --}}
                <div class="kv-grid">
                    <div class="kv-item">
                        <div class="kv-label">Owner</div>
                        <div class="kv-value">
                            <a href="{{ route('uni-admin.users.show', $item->owner) }}"
                               class="user-chip">
                                <span class="user-chip-avatar">
                                    {{ strtoupper(substr($item->owner->name ?? '?', 0, 1)) }}
                                </span>
                                {{ $item->owner->name ?? 'Unknown' }}
                            </a>
                        </div>
                    </div>

                    <div class="kv-item">
                        <div class="kv-label">Price</div>
                        <div class="kv-value" style="color: #059669;">
                            @if ($item->isFree())
                                Free
                            @elseif ($item->price)
                                ৳{{ number_format($item->price, 2) }}
                            @else
                                —
                            @endif
                        </div>
                    </div>

                    @if ($item->lending_duration_days)
                        <div class="kv-item">
                            <div class="kv-label">Max Lend Period</div>
                            <div class="kv-value">{{ $item->lending_duration_days }} days</div>
                        </div>
                    @endif

                    @if ($item->pickup_location)
                        <div class="kv-item">
                            <div class="kv-label">Pickup Location</div>
                            <div class="kv-value">{{ $item->pickup_location }}</div>
                        </div>
                    @endif

                    <div class="kv-item">
                        <div class="kv-label">Listed On</div>
                        <div class="kv-value">{{ $item->created_at->format('d M Y') }}</div>
                    </div>

                    <div class="kv-item">
                        <div class="kv-label">Avg. Rating</div>
                        <div class="kv-value">
                            @php $avgRating = $item->averageRating(); @endphp
                            @if ($avgRating > 0)
                                {{ number_format($avgRating, 1) }} / 5
                            @else
                                No ratings yet
                            @endif
                        </div>
                    </div>

                    <div class="kv-item">
                        <div class="kv-label">Total Borrows</div>
                        <div class="kv-value">{{ $item->totalBorrowCount() }} times</div>
                    </div>
                </div>

            </div>{{-- end item-hero-details --}}
        </div>{{-- end item-hero-inner --}}

        {{-- Action bar --}}
        <div class="hero-actions">

            @if ($item->status !== 'reserved' && $item->status !== 'sold')
                <form method="POST" action="{{ route('uni-admin.items.flag', $item) }}">
                    @csrf
                    <button type="submit" class="btn-flag"
                        onclick="return confirm('Flag and hide \'{{ addslashes($item->title) }}\' from all listings?')">
                        <i class="bi bi-flag"></i> Flag Item
                    </button>
                </form>
            @else
                <button class="btn-flag" disabled style="opacity: 0.45; cursor: not-allowed;">
                    <i class="bi bi-flag"></i>
                    {{ $item->status === 'reserved' ? 'Already Flagged' : 'Item Sold' }}
                </button>
            @endif

            @if (!$item->activeTransaction)
                <form method="POST" action="{{ route('uni-admin.items.destroy', $item) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete"
                        onclick="return confirm('Permanently delete \'{{ addslashes($item->title) }}\'? All transaction history will also be removed.')">
                        <i class="bi bi-trash"></i> Delete Item
                    </button>
                </form>
            @else
                <button class="btn-delete" disabled title="Cannot delete — active transaction in progress"
                    style="opacity: 0.45; cursor: not-allowed;">
                    <i class="bi bi-trash"></i> Delete Item
                </button>
                <span style="font-size: 0.78rem; color: #9ca3af; align-self: center;">
                    <i class="bi bi-info-circle me-1"></i>Cannot delete while transaction is active.
                </span>
            @endif

        </div>
    </div>

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- LEFT — Transaction history ──────── --}}
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-arrow-left-right"></i> Transaction History</h5>
                    <span class="sub-count">{{ $item->transactions->count() }} total</span>
                </div>

                @if ($item->transactions->count())
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Borrower</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th>Penalties</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->transactions as $txn)
                                <tr>
                                    {{-- Borrower --}}
                                    <td>
                                        @if ($txn->borrower)
                                            <a href="{{ route('uni-admin.users.show', $txn->borrower) }}"
                                               class="user-chip">
                                                <span class="user-chip-avatar">
                                                    {{ strtoupper(substr($txn->borrower->name, 0, 1)) }}
                                                </span>
                                                {{ $txn->borrower->name }}
                                            </a>
                                        @else
                                            <span style="color: #9ca3af; font-size: 0.82rem;">Deleted user</span>
                                        @endif
                                    </td>

                                    {{-- Type --}}
                                    <td>
                                        <span class="badge-soft {{ $txn->type }}">
                                            {{ ucfirst($txn->type) }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <span class="badge-soft {{ $txn->status }}">
                                            {{ $txn->getStatusLabel() }}
                                        </span>
                                    </td>

                                    {{-- Dates --}}
                                    <td style="font-size: 0.78rem; color: #6b7280;">
                                        @if ($txn->start_date)
                                            <div><i class="bi bi-play-circle me-1"></i>{{ $txn->start_date->format('d M Y') }}</div>
                                        @endif
                                        @if ($txn->due_date)
                                            <div class="{{ $txn->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                                <i class="bi bi-calendar-x me-1"></i>{{ $txn->due_date->format('d M Y') }}
                                            </div>
                                        @endif
                                        @if ($txn->return_date)
                                            <div style="color: #059669;">
                                                <i class="bi bi-check-circle me-1"></i>{{ $txn->return_date->format('d M Y') }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Penalties --}}
                                    <td>
                                        @if ($txn->penalties->count() > 0)
                                            <span style="font-size: 0.78rem; color: #dc2626; font-weight: 600;">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                {{ $txn->penalties->count() }}
                                            </span>
                                        @else
                                            <span style="font-size: 0.78rem; color: #9ca3af;">—</span>
                                        @endif
                                    </td>

                                    {{-- View --}}
                                    <td>
                                        <a href="{{ route('uni-admin.transactions.show', $txn) }}"
                                           style="font-size: 0.78rem; color: #0d6efd; font-weight: 600; text-decoration: none;">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bi bi-arrow-left-right"></i>
                        <p>No transactions for this item yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT — Ratings ─────────────────── --}}
        <div class="col-lg-5">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-star"></i> Ratings & Reviews</h5>
                    <span class="sub-count">
                        @php $avgRating = $item->averageRating(); @endphp
                        @if ($avgRating > 0)
                            Avg: {{ number_format($avgRating, 1) }} / 5
                        @else
                            No ratings
                        @endif
                    </span>
                </div>

                @if ($item->ratings->count())
                    {{-- Rating summary bar --}}
                    @if ($avgRating > 0)
                        <div style="padding: 14px 22px; border-bottom: 1px solid #f3f4f6;
                            display: flex; align-items: center; gap: 14px;">
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1f36; line-height: 1;">
                                {{ number_format($avgRating, 1) }}
                            </div>
                            <div>
                                <div class="star-rating">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <i class="bi bi-star-fill {{ $s <= round($avgRating) ? 'star-filled' : 'star-empty' }}"></i>
                                    @endfor
                                </div>
                                <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 2px;">
                                    {{ $item->ratings->count() }} review{{ $item->ratings->count() !== 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Individual ratings --}}
                    @foreach ($item->ratings->take(8) as $rating)
                        <div class="rating-row">
                            <div class="rater-avatar">
                                {{ strtoupper(substr($rating->rater->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <span style="font-size: 0.85rem; font-weight: 600; color: #1a1f36;">
                                        {{ $rating->rater->name ?? 'Unknown user' }}
                                    </span>
                                    <div class="star-rating" style="font-size: 0.75rem;">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="bi bi-star-fill {{ $s <= $rating->rating ? 'star-filled' : 'star-empty' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if ($rating->comment)
                                    <p class="rating-comment">"{{ $rating->comment }}"</p>
                                @endif
                                <div style="font-size: 0.72rem; color: #9ca3af; margin-top: 4px;">
                                    {{ $rating->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if ($item->ratings->count() > 8)
                        <div style="padding: 10px 22px; font-size: 0.78rem; color: #9ca3af;
                            border-top: 1px solid #f3f4f6; text-align: center;">
                            Showing 8 of {{ $item->ratings->count() }} ratings
                        </div>
                    @endif

                @else
                    <div class="empty-state">
                        <i class="bi bi-star"></i>
                        <p>No ratings yet for this item.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- end row --}}

</div>
@endsection