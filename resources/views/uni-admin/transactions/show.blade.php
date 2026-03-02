@extends('layouts.app')

@section('title', 'Transaction #' . $transaction->id . ' — UniShare Admin')

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

    /* ── Status banner ─────────────────────────────────── */
    .status-banner {
        border-radius: 10px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .status-banner.banner-active    { background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #3b82f6; }
    .status-banner.banner-late      { background: #fff5f5; border: 1px solid #fecaca; border-left: 4px solid #dc2626; }
    .status-banner.banner-completed { background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; }
    .status-banner.banner-pending   { background: #fefce8; border: 1px solid #fde68a; border-left: 4px solid #ca8a04; }
    .status-banner.banner-cancelled { background: #f9fafb; border: 1px solid #e5e7eb; border-left: 4px solid #9ca3af; }

    .status-banner i  { font-size: 1.3rem; flex-shrink: 0; }
    .banner-active i    { color: #3b82f6; }
    .banner-late i      { color: #dc2626; }
    .banner-completed i { color: #16a34a; }
    .banner-pending i   { color: #ca8a04; }
    .banner-cancelled i { color: #9ca3af; }

    .status-banner .banner-text {
        flex: 1;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .banner-active .banner-text    { color: #1e3a8a; }
    .banner-late .banner-text      { color: #7f1d1d; }
    .banner-completed .banner-text { color: #14532d; }
    .banner-pending .banner-text   { color: #713f12; }
    .banner-cancelled .banner-text { color: #4b5563; }

    /* ── Main hero card ────────────────────────────────── */
    .txn-hero {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .txn-hero-header {
        padding: 20px 26px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .txn-hero-header h2 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0;
    }

    .txn-hero-header .txn-id {
        font-size: 0.78rem;
        color: #9ca3af;
        font-weight: 500;
        margin-top: 2px;
    }

    /* ── Badges ────────────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .badge-soft.pending   { background: #fef9c3; color: #854d0e; }
    .badge-soft.active    { background: #dbeafe; color: #1e40af; }
    .badge-soft.completed { background: #d1fae5; color: #065f46; }
    .badge-soft.late      { background: #fee2e2; color: #991b1b; }
    .badge-soft.cancelled { background: #f3f4f6; color: #6b7280; }
    .badge-soft.lend      { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell      { background: #fce7f3; color: #9d174d; }
    .badge-soft.share     { background: #d1fae5; color: #065f46; }
    .badge-soft.penalty-pending { background: #fef9c3; color: #854d0e; }
    .badge-soft.paid      { background: #d1fae5; color: #065f46; }
    .badge-soft.waived    { background: #dbeafe; color: #1e40af; }

    /* ── KV details grid ───────────────────────────────── */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0;
        border-top: 1px solid #f3f4f6;
    }

    .detail-cell {
        padding: 16px 24px;
        border-right: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
    }

    .detail-cell:last-child { border-right: none; }

    .detail-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1a1f36;
    }

    /* ── User card ─────────────────────────────────────── */
    .participants-row {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 16px;
        padding: 22px 26px;
        border-bottom: 1px solid #f3f4f6;
    }

    .participant-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .participant-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.08);
        color: inherit;
    }

    .participant-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .avatar-borrower { background: #e7f1ff; color: #0d6efd; }
    .avatar-owner    { background: #d1fae5; color: #059669; }

    .participant-info {}

    .participant-role {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #9ca3af;
        margin-bottom: 2px;
    }

    .participant-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1a1f36;
    }

    .participant-email {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .arrow-divider {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: #d1d5db;
        font-size: 1.2rem;
    }

    .arrow-divider span {
        font-size: 0.65rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ── Item row inside hero ──────────────────────────── */
    .item-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 26px;
        border-bottom: 1px solid #f3f4f6;
        text-decoration: none;
        color: inherit;
        transition: background 0.12s;
    }

    .item-row:hover { background: #f9fafb; }

    .item-thumb-lg {
        width: 56px;
        height: 56px;
        border-radius: 10px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #9ca3af;
        overflow: hidden;
        flex-shrink: 0;
    }

    .item-thumb-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-row-info h6 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0 0 4px;
    }

    .item-row-info p {
        font-size: 0.78rem;
        color: #9ca3af;
        margin: 0;
    }

    /* ── Timeline ──────────────────────────────────────── */
    .timeline {
        padding: 20px 26px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .timeline-item {
        display: flex;
        gap: 16px;
        padding-bottom: 20px;
        position: relative;
    }

    .timeline-item:last-child { padding-bottom: 0; }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
        z-index: 1;
    }

    .dot-done    { background: #d1fae5; color: #059669; }
    .dot-pending { background: #f3f4f6; color: #9ca3af; border: 2px dashed #d1d5db; }
    .dot-late    { background: #fee2e2; color: #dc2626; }

    .timeline-content {
        padding-top: 5px;
    }

    .timeline-content .tl-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1a1f36;
    }

    .timeline-content .tl-date {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .timeline-content .tl-pending {
        font-size: 0.8rem;
        color: #9ca3af;
        font-style: italic;
    }

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

    /* ── Penalty rows ──────────────────────────────────── */
    .penalty-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 22px;
        border-bottom: 1px solid #f3f4f6;
        flex-wrap: wrap;
    }

    .penalty-row:last-child { border-bottom: none; }

    .penalty-amount {
        font-size: 1.2rem;
        font-weight: 700;
        color: #dc2626;
        min-width: 100px;
    }

    .penalty-meta {
        flex: 1;
        font-size: 0.85rem;
        color: #374151;
    }

    .penalty-meta .pm-detail {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .penalty-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-waive {
        background: #e7f1ff;
        color: #0d6efd;
        border: none;
        border-radius: 6px;
        padding: 5px 14px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-waive:hover { background: #dbeafe; }

    .btn-mark-paid {
        background: #d1fae5;
        color: #059669;
        border: none;
        border-radius: 6px;
        padding: 5px 14px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-mark-paid:hover { background: #a7f3d0; }

    /* ── Star rating ───────────────────────────────────── */
    .star-rating {
        display: inline-flex;
        gap: 2px;
        font-size: 0.9rem;
    }

    .star-filled { color: #f59e0b; }
    .star-empty  { color: #e5e7eb; }

    /* ── Rating rows ───────────────────────────────────── */
    .rating-row {
        display: flex;
        gap: 14px;
        padding: 14px 22px;
        border-bottom: 1px solid #f3f4f6;
        align-items: flex-start;
    }

    .rating-row:last-child { border-bottom: none; }

    .rater-avatar {
        width: 36px;
        height: 36px;
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
        padding: 32px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 1.8rem;
        display: block;
        margin-bottom: 8px;
        color: #d1d5db;
    }

    .empty-state p { font-size: 0.85rem; margin: 0; }

    @media (max-width: 768px) {
        .participants-row {
            grid-template-columns: 1fr;
        }

        .arrow-divider {
            flex-direction: row;
            justify-content: center;
        }

        .details-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- Back link --}}
    <a href="{{ route('uni-admin.transactions.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Transactions
    </a>

    {{-- ══════════════════════════════════════
         STATUS BANNER
    ══════════════════════════════════════ --}}
    @php
        $bannerMap = [
            'active'    => ['icon' => 'bi-arrow-repeat',    'msg' => 'This transaction is currently <strong>active</strong>.'],
            'pending'   => ['icon' => 'bi-hourglass-split', 'msg' => 'This transaction is <strong>awaiting confirmation</strong>.'],
            'late'      => ['icon' => 'bi-alarm-fill',      'msg' => 'This item is <strong>overdue</strong> — ' . $transaction->daysOverdue() . ' day(s) past the due date.'],
            'completed' => ['icon' => 'bi-check-circle-fill','msg'=> 'This transaction was <strong>completed successfully</strong>.'],
            'cancelled' => ['icon' => 'bi-x-circle-fill',   'msg' => 'This transaction was <strong>cancelled</strong>.'],
        ];

        $banner = $bannerMap[$transaction->status] ?? null;
    @endphp

    @if ($banner)
        <div class="status-banner banner-{{ $transaction->status }}">
            <i class="bi {{ $banner['icon'] }}"></i>
            <div class="banner-text">{!! $banner['msg'] !!}</div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge-soft {{ $transaction->status }}">
                    {{ $transaction->getStatusLabel() }}
                </span>
                <span class="badge-soft {{ $transaction->type }}">
                    {{ ucfirst($transaction->type) }}
                </span>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         MAIN HERO CARD
    ══════════════════════════════════════ --}}
    <div class="txn-hero">

        {{-- Header --}}
        <div class="txn-hero-header">
            <div>
                <h2>Transaction #{{ $transaction->id }}</h2>
                <div class="txn-id">Created {{ $transaction->created_at->format('d M Y, h:i A') }}</div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge-soft {{ $transaction->type }}">
                    <i class="bi bi-{{ $transaction->type === 'lend' ? 'arrow-left-right' : ($transaction->type === 'sell' ? 'tag' : 'gift') }}"></i>
                    {{ ucfirst($transaction->type) }}
                </span>
                <span class="badge-soft {{ $transaction->status }}">
                    {{ $transaction->getStatusLabel() }}
                </span>
            </div>
        </div>

        {{-- Item row --}}
        <a href="{{ route('uni-admin.items.show', $transaction->item) }}" class="item-row">
            <div class="item-thumb-lg">
                @if ($transaction->item?->image_path)
                    <img src="{{ $transaction->item->image_url }}" alt="">
                @else
                    <i class="bi bi-box-seam"></i>
                @endif
            </div>
            <div class="item-row-info flex-grow-1">
                <h6>{{ $transaction->item->title ?? 'Deleted Item' }}</h6>
                <p>
                    <i class="bi bi-pin-map me-1"></i>
                    {{ $transaction->item->pickup_location ?? 'No pickup location' }}
                    @if ($transaction->item?->university)
                        · {{ $transaction->item->university->name }}
                    @endif
                </p>
            </div>
            <div>
                <i class="bi bi-arrow-right text-muted"></i>
            </div>
        </a>

        {{-- Participants --}}
        <div class="participants-row">
            {{-- Borrower --}}
            @if ($transaction->borrower)
                <a href="{{ route('uni-admin.users.show', $transaction->borrower) }}"
                   class="participant-card">
                    <div class="participant-avatar avatar-borrower">
                        {{ strtoupper(substr($transaction->borrower->name, 0, 1)) }}
                    </div>
                    <div class="participant-info">
                        <div class="participant-role">Borrower / Buyer</div>
                        <div class="participant-name">{{ $transaction->borrower->name }}</div>
                        <div class="participant-email">{{ $transaction->borrower->email }}</div>
                    </div>
                </a>
            @else
                <div class="participant-card" style="opacity: 0.5;">
                    <div class="participant-avatar avatar-borrower">?</div>
                    <div class="participant-info">
                        <div class="participant-role">Borrower / Buyer</div>
                        <div class="participant-name">Deleted user</div>
                    </div>
                </div>
            @endif

            {{-- Arrow --}}
            <div class="arrow-divider">
                <i class="bi bi-arrow-right"></i>
                <span>{{ ucfirst($transaction->type) }}</span>
            </div>

            {{-- Owner --}}
            @if ($transaction->owner)
                <a href="{{ route('uni-admin.users.show', $transaction->owner) }}"
                   class="participant-card">
                    <div class="participant-avatar avatar-owner">
                        {{ strtoupper(substr($transaction->owner->name, 0, 1)) }}
                    </div>
                    <div class="participant-info">
                        <div class="participant-role">Owner / Lender</div>
                        <div class="participant-name">{{ $transaction->owner->name }}</div>
                        <div class="participant-email">{{ $transaction->owner->email }}</div>
                    </div>
                </a>
            @else
                <div class="participant-card" style="opacity: 0.5;">
                    <div class="participant-avatar avatar-owner">?</div>
                    <div class="participant-info">
                        <div class="participant-role">Owner / Lender</div>
                        <div class="participant-name">Deleted user</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Key-value details grid --}}
        <div class="details-grid">
            <div class="detail-cell">
                <div class="detail-label">Start Date</div>
                <div class="detail-value">
                    {{ $transaction->start_date ? $transaction->start_date->format('d M Y') : '—' }}
                </div>
            </div>

            <div class="detail-cell">
                <div class="detail-label">Due Date</div>
                <div class="detail-value {{ $transaction->isOverdue() ? 'text-danger' : '' }}">
                    @if ($transaction->due_date)
                        {{ $transaction->due_date->format('d M Y') }}
                        @if ($transaction->isOverdue())
                            <span style="font-size: 0.72rem; font-weight: 400;">
                                ({{ $transaction->daysOverdue() }}d overdue)
                            </span>
                        @endif
                    @else
                        —
                    @endif
                </div>
            </div>

            <div class="detail-cell">
                <div class="detail-label">Return Date</div>
                <div class="detail-value" style="{{ $transaction->return_date ? 'color: #059669;' : '' }}">
                    {{ $transaction->return_date ? $transaction->return_date->format('d M Y') : '—' }}
                </div>
            </div>

            <div class="detail-cell">
                <div class="detail-label">Deposit</div>
                <div class="detail-value">
                    {{ $transaction->deposit_amount ? $transaction->formatted_deposit : '—' }}
                </div>
            </div>

            <div class="detail-cell">
                <div class="detail-label">Final Price</div>
                <div class="detail-value" style="color: #059669;">
                    {{ $transaction->final_price ? $transaction->formatted_price : '—' }}
                </div>
            </div>

            <div class="detail-cell" style="border-right: none;">
                <div class="detail-label">Avg. Rating</div>
                <div class="detail-value">
                    @php $txnRating = $transaction->averageRating(); @endphp
                    {{ $txnRating > 0 ? number_format($txnRating, 1) . ' / 5' : '—' }}
                </div>
            </div>
        </div>

    </div>{{-- end txn-hero --}}

    {{-- ══════════════════════════════════════
         TWO COLUMN: Timeline + Penalties + Ratings
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- LEFT: Timeline + Ratings ────────── --}}
        <div class="col-lg-5">

            {{-- Timeline --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-clock-history"></i> Transaction Timeline</h5>
                </div>
                <div class="timeline">

                    {{-- Created --}}
                    <div class="timeline-item">
                        <div class="timeline-dot dot-done">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="tl-label">Transaction Created</div>
                            <div class="tl-date">{{ $transaction->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>

                    {{-- Started --}}
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $transaction->start_date ? 'dot-done' : 'dot-pending' }}">
                            <i class="bi bi-play-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="tl-label">Started / Confirmed</div>
                            @if ($transaction->start_date)
                                <div class="tl-date">{{ $transaction->start_date->format('d M Y') }}</div>
                            @else
                                <div class="tl-pending">Not yet started</div>
                            @endif
                        </div>
                    </div>

                    {{-- Due --}}
                    @if ($transaction->isLending())
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $transaction->isOverdue() ? 'dot-late' : ($transaction->due_date ? 'dot-done' : 'dot-pending') }}">
                                <i class="bi bi-{{ $transaction->isOverdue() ? 'alarm' : 'calendar-x' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="tl-label">Due for Return</div>
                                @if ($transaction->due_date)
                                    <div class="tl-date {{ $transaction->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                        {{ $transaction->due_date->format('d M Y') }}
                                        @if ($transaction->isOverdue())
                                            — {{ $transaction->daysOverdue() }} days overdue
                                        @endif
                                    </div>
                                @else
                                    <div class="tl-pending">No due date set</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Returned --}}
                    @if ($transaction->isLending())
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $transaction->return_date ? 'dot-done' : 'dot-pending' }}">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="tl-label">Item Returned</div>
                                @if ($transaction->return_date)
                                    <div class="tl-date" style="color: #059669;">
                                        {{ $transaction->return_date->format('d M Y') }}
                                    </div>
                                @else
                                    <div class="tl-pending">Not yet returned</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Completed / Cancelled --}}
                    <div class="timeline-item">
                        <div class="timeline-dot
                            {{ in_array($transaction->status, ['completed']) ? 'dot-done' : '' }}
                            {{ $transaction->status === 'cancelled' ? 'dot-late' : '' }}
                            {{ !in_array($transaction->status, ['completed','cancelled']) ? 'dot-pending' : '' }}">
                            <i class="bi bi-{{ $transaction->status === 'cancelled' ? 'x-circle' : 'trophy' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="tl-label">
                                {{ $transaction->status === 'cancelled' ? 'Cancelled' : 'Completed' }}
                            </div>
                            @if (in_array($transaction->status, ['completed', 'cancelled']))
                                <div class="tl-date">{{ $transaction->updated_at->format('d M Y') }}</div>
                            @else
                                <div class="tl-pending">Pending completion</div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Ratings --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-star"></i> Ratings</h5>
                    <span class="sub-count">{{ $transaction->ratings->count() }} rating{{ $transaction->ratings->count() !== 1 ? 's' : '' }}</span>
                </div>

                @forelse ($transaction->ratings as $rating)
                    <div class="rating-row">
                        <div class="rater-avatar">
                            {{ strtoupper(substr($rating->rater->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <span style="font-weight: 600; font-size: 0.875rem; color: #1a1f36;">
                                    {{ $rating->rater->name ?? 'Unknown' }}
                                </span>
                                <div class="star-rating">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <i class="bi bi-star-fill {{ $s <= $rating->rating ? 'star-filled' : 'star-empty' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 2px;">
                                {{ $rating->isBorrowerRating() ? 'Borrower rated the owner' : 'Owner rated the borrower' }}
                            </div>
                            @if ($rating->comment)
                                <p class="rating-comment">"{{ $rating->comment }}"</p>
                            @endif
                            <div style="font-size: 0.72rem; color: #9ca3af; margin-top: 4px;">
                                {{ $rating->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-star"></i>
                        <p>
                            @if ($transaction->canBeRated())
                                No ratings submitted yet.
                            @else
                                Ratings are available after the transaction is completed.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

        </div>

        {{-- RIGHT: Penalties ────────────────── --}}
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-card-header">
                    <h5>
                        <i class="bi bi-exclamation-triangle"></i> Penalties
                    </h5>
                    <span class="sub-count">
                        {{ $transaction->penalties->count() }} total
                        @if ($transaction->penalties->where('status', 'pending')->count() > 0)
                            · <span style="color: #dc2626; font-weight: 600;">
                                ৳{{ number_format($transaction->penalties->where('status', 'pending')->sum('amount'), 2) }} outstanding
                            </span>
                        @endif
                    </span>
                </div>

                @forelse ($transaction->penalties as $penalty)
                    <div class="penalty-row">

                        {{-- Amount --}}
                        <div class="penalty-amount">
                            {{ $penalty->formatted_amount }}
                        </div>

                        {{-- Details --}}
                        <div class="penalty-meta">
                            <div>
                                <strong>{{ $penalty->days_late }} day{{ $penalty->days_late > 1 ? 's' : '' }} late</strong>
                                return penalty
                            </div>
                            <div class="pm-detail">
                                Issued {{ $penalty->created_at->format('d M Y') }}
                                · {{ $penalty->created_at->diffForHumans() }}
                            </div>
                        </div>

                        {{-- Status --}}
                        <div>
                            <span class="badge-soft {{ $penalty->status === 'pending' ? 'penalty-pending' : $penalty->status }}">
                                {{ $penalty->getStatusLabel() }}
                            </span>
                        </div>

                        {{-- Actions (only for pending) --}}
                        @if ($penalty->isPending())
                            <div class="penalty-actions">
                                <form method="POST"
                                      action="{{ route('uni-admin.penalties.mark-paid', $penalty) }}">
                                    @csrf
                                    <button type="submit" class="btn-mark-paid"
                                        onclick="return confirm('Mark this penalty as paid?')">
                                        <i class="bi bi-check-circle"></i> Paid
                                    </button>
                                </form>
                                <form method="POST"
                                      action="{{ route('uni-admin.penalties.waive', $penalty) }}">
                                    @csrf
                                    <button type="submit" class="btn-waive"
                                        onclick="return confirm('Waive this penalty of {{ $penalty->formatted_amount }}?')">
                                        <i class="bi bi-shield-check"></i> Waive
                                    </button>
                                </form>
                            </div>
                        @else
                            <div style="font-size: 0.75rem; color: #9ca3af;">
                                @if ($penalty->isPaid())
                                    <i class="bi bi-check-circle me-1 text-success"></i> Paid
                                @elseif ($penalty->isWaived())
                                    <i class="bi bi-shield-check me-1 text-primary"></i> Waived
                                @endif
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-emoji-smile" style="color: #059669;"></i>
                        <p>No penalties for this transaction.</p>
                    </div>
                @endforelse

            </div>
        </div>

    </div>{{-- end row --}}

</div>
@endsection