@extends('layouts.app')

@section('title', 'Penalty Report — UniShare Admin')

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

    /* ── Financial summary cards ───────────────────────── */
    .financial-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .fin-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px 22px;
    }

    .fin-card .fin-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fin-card .fin-value {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .fin-card .fin-sub {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .fin-card.outstanding { border-left: 3px solid #dc2626; }
    .fin-card.collected   { border-left: 3px solid #10b981; }
    .fin-card.count-total { border-left: 3px solid #6b7280; }

    /* ── Stat cards row ────────────────────────────────── */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 18px;
        text-align: center;
    }

    .stat-card .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-card .stat-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }

    .stat-card .stat-bar {
        height: 3px;
        border-radius: 2px;
        margin-top: 10px;
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
        flex-wrap: wrap;
        gap: 8px;
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

    .section-card-header .header-sub {
        font-size: 0.78rem;
        color: #9ca3af;
    }

    /* ── Chart wrap ────────────────────────────────────── */
    .chart-wrap {
        padding: 24px;
    }

    /* ── Progress bar ──────────────────────────────────── */
    .progress-row {
        padding: 16px 24px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .progress-row:last-child { border-bottom: none; }

    .progress-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        min-width: 90px;
    }

    .progress-track {
        flex: 1;
        height: 8px;
        background: #f3f4f6;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
    }

    .progress-count {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1a1f36;
        min-width: 32px;
        text-align: right;
    }

    .progress-pct {
        font-size: 0.72rem;
        color: #9ca3af;
        min-width: 38px;
        text-align: right;
    }

    /* ── Recent penalties table ────────────────────────── */
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
        padding: 10px 20px;
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
        padding: 12px 20px;
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
        padding: 3px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .badge-soft.pending { background: #fef9c3; color: #854d0e; }
    .badge-soft.paid    { background: #d1fae5; color: #065f46; }
    .badge-soft.waived  { background: #f3f4f6; color: #6b7280; }

    /* ── Amount cell ───────────────────────────────────── */
    .amount-cell { font-weight: 700; font-size: 0.88rem; }
    .amount-cell.pending { color: #dc2626; }
    .amount-cell.paid    { color: #059669; }
    .amount-cell.waived  { color: #9ca3af; text-decoration: line-through; }

    /* ── Days-late chip ────────────────────────────────── */
    .days-late-chip {
        background: #fee2e2;
        color: #991b1b;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* ── User chip ─────────────────────────────────────── */
    .user-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #f3f4f6;
        border-radius: 20px;
        padding: 2px 10px 2px 3px;
        text-decoration: none;
        color: #374151;
        font-weight: 600;
        font-size: 0.8rem;
        transition: background 0.15s;
    }

    .user-chip:hover { background: #e5e7eb; color: #374151; }

    .user-chip-avatar {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
    }

    /* ── Export button ─────────────────────────────────── */
    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        transition: all 0.15s;
    }

    .btn-export:hover {
        background: #f3f4f6;
        color: #1a1f36;
        text-decoration: none;
    }

    .btn-export i { color: #059669; }

    /* ── Empty state ───────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 2rem;
        display: block;
        margin-bottom: 10px;
        color: #d1d5db;
    }

    .empty-state p { font-size: 0.875rem; margin: 0; }

    @media (max-width: 576px) {
        .financial-row { grid-template-columns: 1fr; }
        .stat-cards { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- Back link --}}
    <a href="{{ route('uni-admin.reports.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Reports
    </a>

    {{-- ══════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════ --}}
    <div class="ua-page-header">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Penalty Report</h1>
                <p>Outstanding amounts, collection history, and penalty status breakdown.</p>
            </div>
            <a href="{{ route('uni-admin.reports.export') }}?type=penalties" class="btn-export">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         FINANCIAL SUMMARY CARDS
    ══════════════════════════════════════ --}}
    <div class="financial-row">

        <div class="fin-card outstanding">
            <div class="fin-label"><i class="bi bi-exclamation-circle" style="color: #dc2626;"></i> Outstanding</div>
            <div class="fin-value" style="color: #dc2626;">৳{{ number_format($totalOutstanding, 2) }}</div>
            <div class="fin-sub">{{ $pendingPenalties }} pending {{ Str::plural('penalty', $pendingPenalties) }}</div>
        </div>

        <div class="fin-card collected">
            <div class="fin-label"><i class="bi bi-check-circle" style="color: #10b981;"></i> Collected</div>
            <div class="fin-value" style="color: #059669;">৳{{ number_format($totalCollected, 2) }}</div>
            <div class="fin-sub">{{ $paidPenalties }} {{ Str::plural('penalty', $paidPenalties) }} paid</div>
        </div>

        <div class="fin-card count-total">
            <div class="fin-label"><i class="bi bi-list" style="color: #6b7280;"></i> Total Issued</div>
            <div class="fin-value" style="color: #374151;">{{ number_format($totalPenalties) }}</div>
            <div class="fin-sub">All time penalties</div>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         STATUS COUNT CARDS
    ══════════════════════════════════════ --}}
    <div class="stat-cards">

        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value" style="color: #d97706;">{{ number_format($pendingPenalties) }}</div>
            <div class="stat-bar" style="background: #f59e0b;"></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Paid</div>
            <div class="stat-value" style="color: #059669;">{{ number_format($paidPenalties) }}</div>
            <div class="stat-bar" style="background: #10b981;"></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Waived</div>
            <div class="stat-value" style="color: #6b7280;">{{ number_format($waivedPenalties) }}</div>
            <div class="stat-bar" style="background: #9ca3af;"></div>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- LEFT — Status Breakdown + Chart ─── --}}
        <div class="col-lg-5">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-pie-chart"></i> Status Breakdown</h5>
                    <span class="header-sub">{{ number_format($totalPenalties) }} total</span>
                </div>

                @php
                    $statusItems = [
                        ['label' => 'Pending', 'count' => $pendingPenalties, 'color' => '#f59e0b'],
                        ['label' => 'Paid',    'count' => $paidPenalties,    'color' => '#10b981'],
                        ['label' => 'Waived',  'count' => $waivedPenalties,  'color' => '#9ca3af'],
                    ];
                @endphp

                @if ($totalPenalties > 0)
                    @foreach ($statusItems as $row)
                        @php $pct = round(($row['count'] / $totalPenalties) * 100, 1); @endphp
                        <div class="progress-row">
                            <span class="progress-label">{{ $row['label'] }}</span>
                            <div class="progress-track">
                                <div class="progress-fill"
                                     style="width: {{ $pct }}%; background: {{ $row['color'] }};"></div>
                            </div>
                            <span class="progress-count">{{ number_format($row['count']) }}</span>
                            <span class="progress-pct">{{ $pct }}%</span>
                        </div>
                    @endforeach

                    <div class="chart-wrap" style="padding: 20px 24px;">
                        <canvas id="penaltyDonut" height="200"></canvas>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-check-circle"></i>
                        <p>No penalties have been issued yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT — Recent Penalties Table ──── --}}
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-clock-history"></i> Recent Penalties</h5>
                    <a href="{{ route('uni-admin.penalties.index') }}"
                       style="font-size: 0.78rem; color: #0d6efd; font-weight: 600; text-decoration: none;">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                @if ($recentPenalties->count())
                    <table class="penalties-table">
                        <thead>
                            <tr>
                                <th>Borrower</th>
                                <th>Item</th>
                                <th>Days Late</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPenalties as $penalty)
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
                                            <span style="color: #9ca3af; font-size: 0.8rem;">Deleted user</span>
                                        @endif
                                    </td>

                                    {{-- Item --}}
                                    <td>
                                        <span style="font-size: 0.82rem; font-weight: 500; color: #1a1f36;">
                                            {{ Str::limit($penalty->transaction->item->title ?? 'Deleted item', 28) }}
                                        </span>
                                    </td>

                                    {{-- Days Late --}}
                                    <td>
                                        <span class="days-late-chip">
                                            {{ $penalty->days_late }}d
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
                                        <a href="{{ route('uni-admin.penalties.show', $penalty) }}"
                                           style="text-decoration: none;">
                                            <span class="badge-soft {{ $penalty->status }}">
                                                {{ ucfirst($penalty->status) }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bi bi-check-circle"></i>
                        <p>No recent penalties to display.</p>
                    </div>
                @endif
            </div>

            {{-- Quick actions --}}
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div style="padding: 18px 22px; display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('uni-admin.penalties.index') }}"
                       class="btn btn-outline-primary btn-sm px-4" style="font-size: 0.82rem;">
                        <i class="bi bi-exclamation-triangle me-2"></i>View All Penalties
                    </a>
                    <a href="{{ route('uni-admin.penalties.index') }}?status=pending"
                       class="btn btn-sm px-4" style="font-size: 0.82rem; background: #fef9c3; color: #854d0e; border: 1px solid #fde68a;">
                        <i class="bi bi-clock me-2"></i>Pending Only
                    </a>
                    <a href="{{ route('uni-admin.reports.export') }}?type=penalties"
                       class="btn btn-sm px-4" style="font-size: 0.82rem; background: #f9fafb; color: #374151; border: 1px solid #e5e7eb;">
                        <i class="bi bi-file-earmark-spreadsheet me-2" style="color: #059669;"></i>Export CSV
                    </a>
                </div>
            </div>

        </div>{{-- end col-lg-7 --}}

    </div>{{-- end row --}}

</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Penalty status donut chart ───────────────────────
    @if ($totalPenalties > 0)
    new Chart(document.getElementById('penaltyDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Paid', 'Waived'],
            datasets: [{
                data: [{{ $pendingPenalties }}, {{ $paidPenalties }}, {{ $waivedPenalties }}],
                backgroundColor: ['#f59e0b', '#10b981', '#9ca3af'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 11 },
                        color: '#6b7280',
                        padding: 16,
                        usePointStyle: true,
                        pointStyleWidth: 8,
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} ${ctx.parsed === 1 ? 'penalty' : 'penalties'}`
                    }
                }
            }
        }
    });
    @endif

});
</script>
@endsection