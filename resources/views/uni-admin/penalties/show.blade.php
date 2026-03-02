@extends('layouts.app')

@section('title', 'Penalty Detail — UniShare Admin')

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

    /* ── Penalty hero card ─────────────────────────────── */
    .penalty-hero {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .penalty-hero-header {
        padding: 22px 28px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .penalty-hero-header h2 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0 0 6px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .penalty-hero-header .hero-sub {
        font-size: 0.82rem;
        color: #6b7280;
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
        white-space: nowrap;
    }

    .badge-soft.pending  { background: #fef9c3; color: #854d0e; }
    .badge-soft.paid     { background: #d1fae5; color: #065f46; }
    .badge-soft.waived   { background: #f3f4f6; color: #6b7280; }
    .badge-soft.active   { background: #dbeafe; color: #1e40af; }
    .badge-soft.late     { background: #fee2e2; color: #991b1b; }
    .badge-soft.completed { background: #d1fae5; color: #065f46; }
    .badge-soft.lend     { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell     { background: #fce7f3; color: #9d174d; }
    .badge-soft.share    { background: #d1fae5; color: #065f46; }

    /* ── Amount display ────────────────────────────────── */
    .amount-display {
        text-align: right;
    }

    .amount-display .amount-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .amount-display .amount-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .amount-display .amount-value.pending { color: #dc2626; }
    .amount-display .amount-value.paid    { color: #059669; }
    .amount-display .amount-value.waived  { color: #9ca3af; text-decoration: line-through; }

    .amount-display .amount-sub {
        font-size: 0.75rem;
        margin-top: 4px;
    }

    .amount-display .amount-sub.pending { color: #ef4444; }
    .amount-display .amount-sub.paid    { color: #10b981; }
    .amount-display .amount-sub.waived  { color: #9ca3af; }

    /* ── Key-value grid ────────────────────────────────── */
    .kv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
        padding: 22px 28px;
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

    /* ── Hero action bar ───────────────────────────────── */
    .hero-actions {
        display: flex;
        gap: 10px;
        padding: 16px 28px;
        border-top: 1px solid #f3f4f6;
        flex-wrap: wrap;
        background: #fafafa;
        align-items: center;
    }

    .btn-mark-paid {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .btn-mark-paid:hover { background: #a7f3d0; }

    .btn-waive {
        background: #f9fafb;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .btn-waive:hover { background: #f3f4f6; }

    .btn-disabled {
        opacity: 0.45;
        cursor: not-allowed;
        border-radius: 8px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
        color: #9ca3af;
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

    /* ── Info rows ─────────────────────────────────────── */
    .info-row {
        display: flex;
        align-items: flex-start;
        padding: 13px 22px;
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
        min-width: 140px;
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

    /* ── Days-late chip ────────────────────────────────── */
    .days-late-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
    }

    /* ── Timeline ──────────────────────────────────────── */
    .timeline {
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .timeline-item {
        display: flex;
        gap: 14px;
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:last-child { padding-bottom: 0; }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: 0;
        width: 2px;
        background: #f3f4f6;
    }

    .timeline-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .timeline-dot.green  { background: #d1fae5; color: #065f46; }
    .timeline-dot.yellow { background: #fef9c3; color: #854d0e; }
    .timeline-dot.red    { background: #fee2e2; color: #991b1b; }
    .timeline-dot.gray   { background: #f3f4f6; color: #6b7280; }
    .timeline-dot.blue   { background: #dbeafe; color: #1e40af; }

    .timeline-content {
        flex: 1;
        padding-top: 5px;
    }

    .timeline-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1a1f36;
    }

    .timeline-date {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Overdue warning banner ────────────────────────── */
    .overdue-warning {
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

    .overdue-warning i { font-size: 1.1rem; color: #dc2626; flex-shrink: 0; }

    /* ── Resolved banner ───────────────────────────────── */
    .resolved-banner {
        border-radius: 8px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        font-size: 0.875rem;
    }

    .resolved-banner.paid {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .resolved-banner.waived {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        color: #6b7280;
    }

    .resolved-banner i { font-size: 1.1rem; flex-shrink: 0; }

    @media (max-width: 768px) {
        .penalty-hero-header { flex-direction: column; }
        .amount-display { text-align: left; }
        .kv-grid { grid-template-columns: repeat(2, 1fr); padding: 16px; }
        .info-label { min-width: 110px; }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- Back link --}}
    <a href="{{ route('uni-admin.penalties.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Penalties
    </a>

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
         RESOLVED / STATUS BANNER
    ══════════════════════════════════════ --}}
    @if ($penalty->status === 'paid')
        <div class="resolved-banner paid">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <strong>This penalty has been paid.</strong>
                No further action is required.
            </div>
        </div>
    @elseif ($penalty->status === 'waived')
        <div class="resolved-banner waived">
            <i class="bi bi-slash-circle"></i>
            <div>
                <strong>This penalty has been waived.</strong>
                It was forgiven and will not be collected.
            </div>
        </div>
    @else
        <div class="overdue-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Outstanding penalty — awaiting resolution.</strong>
                Mark as paid once collected, or waive if appropriate.
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         PENALTY HERO CARD
    ══════════════════════════════════════ --}}
    <div class="penalty-hero">

        {{-- Header --}}
        <div class="penalty-hero-header">
            <div>
                <h2>
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 1rem;"></i>
                    Late Return Penalty
                    <span class="badge-soft {{ $penalty->status }}">{{ ucfirst($penalty->status) }}</span>
                </h2>
                <div class="hero-sub">
                    Issued {{ $penalty->created_at->format('d M Y') }}
                    &middot; {{ $penalty->created_at->diffForHumans() }}
                    @if ($penalty->transaction->item ?? null)
                        &middot; Item: <strong>{{ $penalty->transaction->item->title }}</strong>
                    @endif
                </div>
            </div>

            {{-- Amount display --}}
            <div class="amount-display">
                <div class="amount-label">Penalty Amount</div>
                <div class="amount-value {{ $penalty->status }}">
                    ৳{{ number_format($penalty->amount, 2) }}
                </div>
                <div class="amount-sub {{ $penalty->status }}">
                    @if ($penalty->status === 'pending')
                        Awaiting payment
                    @elseif ($penalty->status === 'paid')
                        Collected
                    @else
                        Forgiven
                    @endif
                </div>
            </div>
        </div>

        {{-- Key metrics grid --}}
        <div class="kv-grid">
            <div class="kv-item">
                <div class="kv-label">Borrower</div>
                <div class="kv-value">
                    @if ($penalty->transaction->borrower ?? null)
                        <a href="{{ route('uni-admin.users.show', $penalty->transaction->borrower) }}"
                           class="user-chip">
                            <span class="user-chip-avatar">
                                {{ strtoupper(substr($penalty->transaction->borrower->name, 0, 1)) }}
                            </span>
                            {{ $penalty->transaction->borrower->name }}
                        </a>
                    @else
                        <span style="color: #9ca3af; font-size: 0.82rem;">Deleted user</span>
                    @endif
                </div>
            </div>

            <div class="kv-item">
                <div class="kv-label">Days Late</div>
                <div class="kv-value">
                    <span class="days-late-chip">
                        <i class="bi bi-clock-history" style="font-size: 0.7rem;"></i>
                        {{ $penalty->days_late }} {{ Str::plural('day', $penalty->days_late) }}
                    </span>
                </div>
            </div>

            <div class="kv-item">
                <div class="kv-label">Item Owner</div>
                <div class="kv-value">
                    @if ($penalty->transaction->owner ?? null)
                        <a href="{{ route('uni-admin.users.show', $penalty->transaction->owner) }}"
                           class="user-chip">
                            <span class="user-chip-avatar" style="background: #fce7f3; color: #9d174d;">
                                {{ strtoupper(substr($penalty->transaction->owner->name, 0, 1)) }}
                            </span>
                            {{ $penalty->transaction->owner->name }}
                        </a>
                    @else
                        <span style="color: #9ca3af; font-size: 0.82rem;">—</span>
                    @endif
                </div>
            </div>

            <div class="kv-item">
                <div class="kv-label">Due Date</div>
                <div class="kv-value" style="color: #dc2626;">
                    {{ $penalty->transaction->due_date?->format('d M Y') ?? '—' }}
                </div>
            </div>

            <div class="kv-item">
                <div class="kv-label">Returned On</div>
                <div class="kv-value" style="color: #059669;">
                    {{ $penalty->transaction->return_date?->format('d M Y') ?? 'Not yet returned' }}
                </div>
            </div>

            <div class="kv-item">
                <div class="kv-label">Transaction</div>
                <div class="kv-value">
                    <a href="{{ route('uni-admin.transactions.show', $penalty->transaction) }}"
                       style="color: #0d6efd; font-weight: 600; text-decoration: none; font-size: 0.82rem;">
                        <i class="bi bi-arrow-left-right me-1"></i>View Transaction
                    </a>
                </div>
            </div>
        </div>

        {{-- Action bar --}}
        <div class="hero-actions">
            @if ($penalty->isPending())

                {{-- Mark as Paid --}}
                <form method="POST" action="{{ route('uni-admin.penalties.mark-paid', $penalty) }}">
                    @csrf
                    <button type="submit" class="btn-mark-paid"
                        onclick="return confirm('Confirm that this penalty of ৳{{ number_format($penalty->amount, 2) }} has been received?')">
                        <i class="bi bi-check-circle"></i> Mark as Paid
                    </button>
                </form>

                {{-- Waive --}}
                <form method="POST" action="{{ route('uni-admin.penalties.waive', $penalty) }}">
                    @csrf
                    <button type="submit" class="btn-waive"
                        onclick="return confirm('Waive this penalty of ৳{{ number_format($penalty->amount, 2) }}? This action cannot be undone.')">
                        <i class="bi bi-slash-circle"></i> Waive Penalty
                    </button>
                </form>

            @else
                <button class="btn-disabled" disabled>
                    <i class="bi bi-lock"></i>
                    Penalty {{ ucfirst($penalty->status) }} — No Further Actions
                </button>
            @endif
        </div>

    </div>{{-- end penalty-hero --}}

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- LEFT — Item & Transaction Details ── --}}
        <div class="col-lg-7">

            {{-- Item Details --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-box-seam"></i> Item Details</h5>
                    @if ($penalty->transaction->item ?? null)
                        <a href="{{ route('uni-admin.items.show', $penalty->transaction->item) }}"
                           style="font-size: 0.78rem; color: #0d6efd; font-weight: 600; text-decoration: none;">
                            <i class="bi bi-arrow-up-right me-1"></i>View Item
                        </a>
                    @endif
                </div>

                @if ($penalty->transaction->item ?? null)
                    @php $item = $penalty->transaction->item; @endphp

                    <div class="info-row">
                        <span class="info-label">Title</span>
                        <span class="info-value">{{ $item->title }}</span>
                    </div>

                    @if ($item->description)
                        <div class="info-row">
                            <span class="info-label">Description</span>
                            <span class="info-value" style="color: #6b7280; font-weight: 400;">
                                {{ Str::limit($item->description, 120) }}
                            </span>
                        </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Availability</span>
                        <span class="info-value">
                            <span class="badge-soft {{ $item->availability_mode }}">
                                {{ $item->getAvailabilityModeLabel() }}
                            </span>
                        </span>
                    </div>

                    @if ($item->lending_duration_days)
                        <div class="info-row">
                            <span class="info-label">Max Lend Period</span>
                            <span class="info-value">{{ $item->lending_duration_days }} days</span>
                        </div>
                    @endif

                    @if ($item->price)
                        <div class="info-row">
                            <span class="info-label">Listed Price</span>
                            <span class="info-value" style="color: #059669; font-weight: 700;">
                                ৳{{ number_format($item->price, 2) }}
                            </span>
                        </div>
                    @endif

                    <div class="info-row">
                        <span class="info-label">Current Status</span>
                        <span class="info-value">
                            <span class="badge-soft {{ $item->status }}">{{ $item->getStatusLabel() }}</span>
                        </span>
                    </div>

                @else
                    <div style="padding: 24px 22px; color: #9ca3af; font-size: 0.875rem;">
                        <i class="bi bi-box-seam me-2"></i>Item has been deleted.
                    </div>
                @endif
            </div>

            {{-- Transaction Details --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-arrow-left-right"></i> Transaction Details</h5>
                    <a href="{{ route('uni-admin.transactions.show', $penalty->transaction) }}"
                       style="font-size: 0.78rem; color: #0d6efd; font-weight: 600; text-decoration: none;">
                        <i class="bi bi-arrow-up-right me-1"></i>Full View
                    </a>
                </div>

                @php $txn = $penalty->transaction; @endphp

                <div class="info-row">
                    <span class="info-label">Type</span>
                    <span class="info-value">
                        <span class="badge-soft {{ $txn->type }}">{{ ucfirst($txn->type) }}</span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="badge-soft {{ $txn->status }}">{{ $txn->getStatusLabel() }}</span>
                    </span>
                </div>

                @if ($txn->start_date)
                    <div class="info-row">
                        <span class="info-label">Start Date</span>
                        <span class="info-value">{{ $txn->start_date->format('d M Y') }}</span>
                    </div>
                @endif

                @if ($txn->due_date)
                    <div class="info-row">
                        <span class="info-label">Due Date</span>
                        <span class="info-value" style="color: #dc2626; font-weight: 600;">
                            {{ $txn->due_date->format('d M Y') }}
                        </span>
                    </div>
                @endif

                @if ($txn->return_date)
                    <div class="info-row">
                        <span class="info-label">Returned On</span>
                        <span class="info-value" style="color: #059669;">
                            {{ $txn->return_date->format('d M Y') }}
                            <span style="color: #6b7280; font-weight: 400; font-size: 0.78rem; margin-left: 6px;">
                                ({{ $penalty->days_late }} {{ Str::plural('day', $penalty->days_late) }} late)
                            </span>
                        </span>
                    </div>
                @endif

            </div>

        </div>{{-- end col-lg-7 --}}

        {{-- RIGHT — Borrower & Timeline ────── --}}
        <div class="col-lg-5">

            {{-- Borrower Details --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-person"></i> Borrower</h5>
                    @if ($penalty->transaction->borrower ?? null)
                        <a href="{{ route('uni-admin.users.show', $penalty->transaction->borrower) }}"
                           style="font-size: 0.78rem; color: #0d6efd; font-weight: 600; text-decoration: none;">
                            <i class="bi bi-arrow-up-right me-1"></i>View Profile
                        </a>
                    @endif
                </div>

                @if ($penalty->transaction->borrower ?? null)
                    @php $borrower = $penalty->transaction->borrower; @endphp

                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value">
                            <a href="{{ route('uni-admin.users.show', $borrower) }}" class="user-chip">
                                <span class="user-chip-avatar">
                                    {{ strtoupper(substr($borrower->name, 0, 1)) }}
                                </span>
                                {{ $borrower->name }}
                            </a>
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value" style="font-size: 0.82rem;">{{ $borrower->email }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Account Status</span>
                        <span class="info-value">
                            <span class="badge-soft {{ $borrower->status }}">{{ ucfirst($borrower->status) }}</span>
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Member Since</span>
                        <span class="info-value" style="font-size: 0.82rem;">
                            {{ $borrower->created_at->format('d M Y') }}
                        </span>
                    </div>

                @else
                    <div style="padding: 24px 22px; color: #9ca3af; font-size: 0.875rem;">
                        <i class="bi bi-person me-2"></i>User account has been deleted.
                    </div>
                @endif
            </div>

            {{-- Penalty Timeline --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-clock-history"></i> Penalty Timeline</h5>
                </div>

                <div class="timeline">

                    {{-- Transaction started --}}
                    @if ($txn->start_date)
                        <div class="timeline-item">
                            <div class="timeline-dot green">
                                <i class="bi bi-play-fill"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Transaction Started</div>
                                <div class="timeline-date">{{ $txn->start_date->format('d M Y') }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Due date --}}
                    @if ($txn->due_date)
                        <div class="timeline-item">
                            <div class="timeline-dot yellow">
                                <i class="bi bi-calendar"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Due Date</div>
                                <div class="timeline-date">{{ $txn->due_date->format('d M Y') }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Item returned (late) --}}
                    @if ($txn->return_date)
                        <div class="timeline-item">
                            <div class="timeline-dot red">
                                <i class="bi bi-arrow-return-left"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Returned
                                    <span style="font-size: 0.75rem; color: #dc2626; font-weight: 700;">
                                        ({{ $penalty->days_late }}d late)
                                    </span>
                                </div>
                                <div class="timeline-date">{{ $txn->return_date->format('d M Y') }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Penalty issued --}}
                    <div class="timeline-item">
                        <div class="timeline-dot red">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Penalty Issued (৳{{ number_format($penalty->amount, 2) }})</div>
                            <div class="timeline-date">{{ $penalty->created_at->format('d M Y') }}</div>
                        </div>
                    </div>

                    {{-- Resolution --}}
                    @if ($penalty->status === 'paid')
                        <div class="timeline-item">
                            <div class="timeline-dot green">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Marked as Paid</div>
                                <div class="timeline-date">{{ $penalty->updated_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    @elseif ($penalty->status === 'waived')
                        <div class="timeline-item">
                            <div class="timeline-dot gray">
                                <i class="bi bi-slash-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Penalty Waived</div>
                                <div class="timeline-date">{{ $penalty->updated_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="timeline-item">
                            <div class="timeline-dot blue">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title" style="color: #854d0e;">Awaiting Resolution</div>
                                <div class="timeline-date">Pending</div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>{{-- end col-lg-5 --}}

    </div>{{-- end row --}}

</div>
@endsection