@extends('layouts.app')

@section('title', $user->name . ' — User Profile')

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

    /* ── Profile header card ───────────────────────────── */
    .profile-header {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 24px;
        flex-wrap: wrap;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.8rem;
        flex-shrink: 0;
    }

    .profile-info { flex: 1; min-width: 200px; }

    .profile-info h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1a1f36;
        margin-bottom: 4px;
    }

    .profile-info .meta {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .profile-info .meta i { margin-right: 4px; }

    .profile-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: flex-start;
    }

    /* ── Status badge ──────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .badge-soft.pending  { background: #fef9c3; color: #854d0e; }
    .badge-soft.verified { background: #d1fae5; color: #065f46; }
    .badge-soft.rejected { background: #fee2e2; color: #991b1b; }
    .badge-soft.active   { background: #dbeafe; color: #1e40af; }
    .badge-soft.late     { background: #fee2e2; color: #991b1b; }
    .badge-soft.completed{ background: #d1fae5; color: #065f46; }
    .badge-soft.cancelled{ background: #f3f4f6; color: #6b7280; }
    .badge-soft.pending-pen { background: #fef9c3; color: #854d0e; }
    .badge-soft.paid     { background: #d1fae5; color: #065f46; }
    .badge-soft.waived   { background: #dbeafe; color: #1e40af; }
    .badge-soft.lend     { background: #dbeafe; color: #1e40af; }
    .badge-soft.sell     { background: #fce7f3; color: #9d174d; }
    .badge-soft.share    { background: #d1fae5; color: #065f46; }
    .badge-soft.available{ background: #d1fae5; color: #065f46; }
    .badge-soft.borrowed { background: #fef9c3; color: #854d0e; }
    .badge-soft.sold     { background: #f3f4f6; color: #6b7280; }

    /* ── Action buttons ────────────────────────────────── */
    .btn-verify {
        background: #059669; color: #fff; border: none;
        border-radius: 7px; padding: 7px 16px; font-size: 0.82rem;
        font-weight: 600; cursor: pointer; transition: background 0.15s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-verify:hover { background: #047857; }

    .btn-reject-sm {
        background: #fee2e2; color: #dc2626; border: none;
        border-radius: 7px; padding: 7px 16px; font-size: 0.82rem;
        font-weight: 600; cursor: pointer; transition: background 0.15s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-reject-sm:hover { background: #fecaca; }

    .btn-suspend-sm {
        background: #fff7ed; color: #ea580c; border: none;
        border-radius: 7px; padding: 7px 16px; font-size: 0.82rem;
        font-weight: 600; cursor: pointer; transition: background 0.15s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-suspend-sm:hover { background: #ffedd5; }

    /* ── Stat mini cards ───────────────────────────────── */
    .mini-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }

    .mini-stat {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px 18px;
        text-align: center;
    }

    .mini-stat .ms-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1f36;
        line-height: 1;
    }

    .mini-stat .ms-label {
        font-size: 0.74rem;
        color: #6b7280;
        font-weight: 500;
        margin-top: 5px;
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

    .section-card-header .item-count {
        font-size: 0.78rem;
        color: #9ca3af;
    }

    /* ── Tables ────────────────────────────────────────── */
    .detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
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
    }

    .detail-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s;
    }

    .detail-table tbody tr:last-child { border-bottom: none; }
    .detail-table tbody tr:hover { background: #f9fafb; }

    .detail-table td {
        padding: 11px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* ── Info list (key:value pairs) ───────────────────── */
    .info-list {
        padding: 4px 0;
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        padding: 12px 22px;
        border-bottom: 1px solid #f3f4f6;
        gap: 16px;
    }

    .info-row:last-child { border-bottom: none; }

    .info-label {
        font-size: 0.78rem;
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
        word-break: break-all;
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

    .empty-state p {
        font-size: 0.85rem;
        margin: 0;
    }

    /* ── Warning box ───────────────────────────────────── */
    .warning-box {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-left: 4px solid #f97316;
        border-radius: 8px;
        padding: 12px 18px;
        font-size: 0.85rem;
        color: #92400e;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .profile-header { flex-direction: column; }
        .mini-stats { grid-template-columns: repeat(2, 1fr); }
        .info-label { min-width: 100px; }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- Back link --}}
    <a href="{{ route('uni-admin.users.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>

    {{-- ══════════════════════════════════════
         PROFILE HEADER CARD
    ══════════════════════════════════════ --}}
    <div class="profile-header">
        {{-- Avatar --}}
        <div class="profile-avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        {{-- Info --}}
        <div class="profile-info">
            <h2>{{ $user->name }}</h2>
            <div class="meta">
                <span><i class="bi bi-envelope"></i>{{ $user->email }}</span>
                <span class="mx-2">·</span>
                <span><i class="bi bi-building"></i>{{ $user->university->name ?? 'Unknown University' }}</span>
                <span class="mx-2">·</span>
                <span><i class="bi bi-calendar3"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
            </div>
            <span class="badge-soft {{ $user->status }}">
                @if ($user->status === 'pending')
                    <i class="bi bi-hourglass-split"></i>
                @elseif ($user->status === 'verified')
                    <i class="bi bi-check-circle"></i>
                @else
                    <i class="bi bi-x-circle"></i>
                @endif
                {{ $user->getStatusLabel() }}
            </span>
        </div>

        {{-- Action buttons --}}
        <div class="profile-actions">
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

            @if ($user->isVerified())
                <form method="POST" action="{{ route('uni-admin.users.suspend', $user) }}">
                    @csrf
                    <button type="submit" class="btn-suspend-sm"
                        onclick="return confirm('Suspend {{ addslashes($user->name) }}? They will lose platform access.')">
                        <i class="bi bi-pause-circle"></i> Suspend
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════
         WARNINGS
    ══════════════════════════════════════ --}}
    @if ($user->hasPendingPenalties())
        <div class="warning-box">
            <i class="bi bi-exclamation-triangle-fill" style="color: #f97316; font-size: 1.1rem;"></i>
            This user has <strong>unpaid penalties</strong>. They are blocked from initiating new transactions.
        </div>
    @endif

    @if ($user->hasOverdueItems())
        <div class="warning-box" style="border-left-color: #dc2626; background: #fff5f5; border-color: #fecaca;">
            <i class="bi bi-alarm-fill" style="color: #dc2626; font-size: 1.1rem;"></i>
            This user has <strong>overdue items</strong> that have not been returned.
        </div>
    @endif

    {{-- ══════════════════════════════════════
         MINI STAT ROW
    ══════════════════════════════════════ --}}
    <div class="mini-stats">
        <div class="mini-stat">
            <div class="ms-value">{{ $user->items->count() }}</div>
            <div class="ms-label">Items Listed</div>
        </div>
        <div class="mini-stat">
            <div class="ms-value">{{ $user->transactionsAsBorrower->count() }}</div>
            <div class="ms-label">Borrowed</div>
        </div>
        <div class="mini-stat">
            <div class="ms-value">{{ $user->transactionsAsOwner->count() }}</div>
            <div class="ms-label">Lent / Sold</div>
        </div>
        <div class="mini-stat">
            <div class="ms-value">{{ $user->penalties->count() }}</div>
            <div class="ms-label">Penalties</div>
        </div>
        <div class="mini-stat">
            @php $avgRating = $user->averageRating(); @endphp
            <div class="ms-value" style="color: {{ $avgRating >= 4 ? '#059669' : ($avgRating >= 3 ? '#ca8a04' : '#dc2626') }}">
                {{ $avgRating > 0 ? number_format($avgRating, 1) : '—' }}
            </div>
            <div class="ms-label">Avg. Rating</div>
        </div>
        <div class="mini-stat">
            <div class="ms-value" style="color: {{ $user->totalUnpaidPenalties() > 0 ? '#dc2626' : '#059669' }}">
                ৳{{ number_format($user->totalUnpaidPenalties(), 0) }}
            </div>
            <div class="ms-label">Unpaid Fines</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- ── LEFT COLUMN ─────────────────── --}}
        <div class="col-lg-4">

            {{-- Account Details --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-person-badge"></i> Account Details</h5>
                </div>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $user->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">University</span>
                        <span class="info-value">{{ $user->university->name ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Role</span>
                        <span class="info-value">{{ $user->getRoleLabel() }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge-soft {{ $user->status }}">{{ $user->getStatusLabel() }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Joined</span>
                        <span class="info-value">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Items Listed --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-grid"></i> Listed Items</h5>
                    <span class="item-count">{{ $user->items->count() }} item{{ $user->items->count() !== 1 ? 's' : '' }}</span>
                </div>
                @forelse ($user->items as $item)
                    <div class="d-flex align-items-center gap-3 px-4 py-3" style="border-bottom: 1px solid #f3f4f6;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: #f3f4f6;
                            display: flex; align-items: center; justify-content: center;
                            font-size: 0.9rem; color: #6b7280; flex-shrink: 0;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div style="font-weight: 600; font-size: 0.85rem; color: #1a1f36; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item->title }}
                            </div>
                            <div style="font-size: 0.75rem; color: #9ca3af;">
                                {{ $item->getAvailabilityModeLabel() }}
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge-soft {{ $item->status }}">{{ $item->getStatusLabel() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-box-seam"></i>
                        <p>No items listed.</p>
                    </div>
                @endforelse
            </div>

        </div>

        {{-- ── RIGHT COLUMN ────────────────── --}}
        <div class="col-lg-8">

            {{-- Transactions as Borrower --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-arrow-down-circle"></i> Borrowing History</h5>
                    <span class="item-count">{{ $user->transactionsAsBorrower->count() }} transaction{{ $user->transactionsAsBorrower->count() !== 1 ? 's' : '' }}</span>
                </div>
                @if ($user->transactionsAsBorrower->count())
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->transactionsAsBorrower->take(6) as $txn)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #1a1f36;">
                                            {{ $txn->item->title ?? 'Deleted Item' }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #9ca3af;">
                                            from {{ $txn->owner->name ?? '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-soft {{ $txn->type }}">{{ ucfirst($txn->type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-soft {{ $txn->status }}">{{ $txn->getStatusLabel() }}</span>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #6b7280;">
                                        @if ($txn->due_date)
                                            <span class="{{ $txn->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                                {{ $txn->due_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($user->transactionsAsBorrower->count() > 6)
                        <div style="padding: 10px 20px; font-size: 0.8rem; color: #9ca3af; border-top: 1px solid #f3f4f6;">
                            Showing 6 of {{ $user->transactionsAsBorrower->count() }} transactions.
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="bi bi-arrow-down-circle"></i>
                        <p>No borrowing activity yet.</p>
                    </div>
                @endif
            </div>

            {{-- Transactions as Owner/Lender --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-arrow-up-circle"></i> Lending / Selling History</h5>
                    <span class="item-count">{{ $user->transactionsAsOwner->count() }} transaction{{ $user->transactionsAsOwner->count() !== 1 ? 's' : '' }}</span>
                </div>
                @if ($user->transactionsAsOwner->count())
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->transactionsAsOwner->take(6) as $txn)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #1a1f36;">
                                            {{ $txn->item->title ?? 'Deleted Item' }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #9ca3af;">
                                            to {{ $txn->borrower->name ?? '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-soft {{ $txn->type }}">{{ ucfirst($txn->type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-soft {{ $txn->status }}">{{ $txn->getStatusLabel() }}</span>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #6b7280;">
                                        @if ($txn->due_date)
                                            {{ $txn->due_date->format('d M Y') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($user->transactionsAsOwner->count() > 6)
                        <div style="padding: 10px 20px; font-size: 0.8rem; color: #9ca3af; border-top: 1px solid #f3f4f6;">
                            Showing 6 of {{ $user->transactionsAsOwner->count() }} transactions.
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="bi bi-arrow-up-circle"></i>
                        <p>No lending or selling activity yet.</p>
                    </div>
                @endif
            </div>

            {{-- Penalties --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-exclamation-triangle"></i> Penalties</h5>
                    <span class="item-count">{{ $user->penalties->count() }} total</span>
                </div>
                @if ($user->penalties->count())
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Days Late</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->penalties as $penalty)
                                <tr>
                                    <td style="font-size: 0.85rem; font-weight: 500; color: #1a1f36;">
                                        {{ $penalty->transaction->item->title ?? '—' }}
                                    </td>
                                    <td style="font-size: 0.85rem; color: #6b7280;">
                                        {{ $penalty->days_late }} day{{ $penalty->days_late > 1 ? 's' : '' }}
                                    </td>
                                    <td style="font-weight: 700; color: #dc2626; font-size: 0.85rem;">
                                        {{ $penalty->formatted_amount }}
                                    </td>
                                    <td>
                                        <span class="badge-soft {{ $penalty->status === 'pending' ? 'pending-pen' : $penalty->status }}">
                                            {{ $penalty->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('uni-admin.penalties.show', $penalty) }}"
                                           style="font-size: 0.78rem; color: #0d6efd; font-weight: 600; text-decoration: none;">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bi bi-emoji-smile"></i>
                        <p>No penalties on record.</p>
                    </div>
                @endif
            </div>

        </div>{{-- end right col --}}

    </div>{{-- end row --}}

</div>
@endsection