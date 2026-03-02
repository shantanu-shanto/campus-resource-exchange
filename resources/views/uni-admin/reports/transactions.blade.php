@extends('layouts.app')

@section('title', 'Transaction Report — UniShare Admin')

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

    /* ── Period selector ───────────────────────────────── */
    .period-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .period-bar span {
        font-size: 0.82rem;
        color: #6b7280;
        font-weight: 500;
    }

    .period-btn {
        padding: 5px 14px;
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        transition: all 0.15s;
    }

    .period-btn:hover { background: #f3f4f6; color: #374151; }

    .period-btn.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    /* ── Stat cards ────────────────────────────────────── */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 20px;
    }

    .stat-card .stat-label {
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

    .stat-card .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1a1f36;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-card .stat-sub {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .stat-card.highlight-blue   { border-left: 3px solid #3b82f6; }
    .stat-card.highlight-green  { border-left: 3px solid #10b981; }
    .stat-card.highlight-yellow { border-left: 3px solid #f59e0b; }
    .stat-card.highlight-red    { border-left: 3px solid #ef4444; }
    .stat-card.highlight-purple { border-left: 3px solid #8b5cf6; }

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

    /* ── Chart container ───────────────────────────────── */
    .chart-wrap {
        padding: 24px;
    }

    /* ── Type breakdown tiles ──────────────────────────── */
    .type-tiles {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
    }

    .type-tile {
        padding: 22px 20px;
        text-align: center;
        border-right: 1px solid #f3f4f6;
    }

    .type-tile:last-child { border-right: none; }

    .type-tile .tt-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin: 0 auto 12px;
    }

    .type-tile .tt-value {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .type-tile .tt-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .type-tile .tt-pct {
        font-size: 0.72rem;
        color: #9ca3af;
        background: #f3f4f6;
        border-radius: 20px;
        padding: 2px 8px;
        display: inline-block;
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

    /* ── Alert info ────────────────────────────────────── */
    .info-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 0.8rem;
        color: #1e40af;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
    }

    @media (max-width: 576px) {
        .stat-cards { grid-template-columns: repeat(2, 1fr); }
        .type-tiles { grid-template-columns: 1fr; }
        .type-tile { border-right: none; border-bottom: 1px solid #f3f4f6; }
        .type-tile:last-child { border-bottom: none; }
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
                <h1><i class="bi bi-arrow-left-right me-2 text-primary"></i>Transaction Report</h1>
                <p>Lending, selling, and sharing activity across your campus.</p>
            </div>
            <a href="{{ route('uni-admin.reports.export') }}?type=transactions" class="btn-export">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         PERIOD SELECTOR
    ══════════════════════════════════════ --}}
    <div class="period-bar">
        <span>New transactions in the last:</span>
        @foreach ([7, 14, 30, 60, 90] as $p)
            <a href="{{ route('uni-admin.reports.transactions') }}?period={{ $p }}"
               class="period-btn {{ (int)$period === $p ? 'active' : '' }}">
                {{ $p }}d
            </a>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════
         STAT CARDS
    ══════════════════════════════════════ --}}
    <div class="stat-cards">

        <div class="stat-card highlight-blue">
            <div class="stat-label"><i class="bi bi-collection" style="color: #3b82f6;"></i> Total</div>
            <div class="stat-value">{{ number_format($totalTransactions) }}</div>
            <div class="stat-sub">All time transactions</div>
        </div>

        <div class="stat-card highlight-yellow">
            <div class="stat-label"><i class="bi bi-arrow-repeat" style="color: #f59e0b;"></i> Active</div>
            <div class="stat-value" style="color: #d97706;">{{ number_format($activeTransactions) }}</div>
            <div class="stat-sub">Active or pending</div>
        </div>

        <div class="stat-card highlight-red">
            <div class="stat-label"><i class="bi bi-clock-history" style="color: #ef4444;"></i> Late</div>
            <div class="stat-value" style="color: #dc2626;">{{ number_format($lateTransactions) }}</div>
            <div class="stat-sub">Overdue returns</div>
        </div>

        <div class="stat-card highlight-green">
            <div class="stat-label"><i class="bi bi-check-circle" style="color: #10b981;"></i> Completed</div>
            <div class="stat-value" style="color: #059669;">{{ number_format($completedCount) }}</div>
            <div class="stat-sub">Successfully closed</div>
        </div>

        <div class="stat-card highlight-purple">
            <div class="stat-label"><i class="bi bi-plus-circle" style="color: #8b5cf6;"></i> New ({{ $period }}d)</div>
            <div class="stat-value" style="color: #7c3aed;">{{ number_format($newTransactions) }}</div>
            <div class="stat-sub">Last {{ $period }} days</div>
        </div>

    </div>

    {{-- Late transactions note --}}
    @if ($lateTransactions > 0)
        <div class="info-note">
            <i class="bi bi-info-circle"></i>
            There {{ $lateTransactions === 1 ? 'is' : 'are' }} <strong>{{ $lateTransactions }} overdue
            {{ Str::plural('transaction', $lateTransactions) }}</strong> that may have associated penalties.
            <a href="{{ route('uni-admin.transactions.index') }}?status=late"
               style="color: #1e40af; font-weight: 600; margin-left: 4px; text-decoration: none;">
                View them →
            </a>
        </div>
    @endif

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- LEFT — Status Breakdown ──────────── --}}
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-pie-chart"></i> Status Breakdown</h5>
                    <span class="header-sub">{{ number_format($totalTransactions) }} total</span>
                </div>

                @php
                    $statusItems = [
                        ['label' => 'Active/Pending', 'count' => $activeTransactions, 'color' => '#f59e0b'],
                        ['label' => 'Completed',      'count' => $completedCount,     'color' => '#10b981'],
                        ['label' => 'Late',           'count' => $lateTransactions,   'color' => '#ef4444'],
                    ];
                @endphp

                @if ($totalTransactions > 0)
                    @foreach ($statusItems as $row)
                        @php $pct = round(($row['count'] / $totalTransactions) * 100, 1); @endphp
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

                    <div style="padding: 20px 24px; border-top: 1px solid #f3f4f6;">
                        <canvas id="statusDonut" height="190"></canvas>
                    </div>
                @else
                    <div style="padding: 40px 24px; text-align: center; color: #9ca3af; font-size: 0.875rem;">
                        No transaction data yet.
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT — Transaction Type Breakdown── --}}
        <div class="col-lg-6">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-tags"></i> Type Breakdown</h5>
                    <span class="header-sub">Lend / Sell / Share</span>
                </div>

                {{-- Type tiles --}}
                @php $typeTotal = $lendCount + $sellCount + $shareCount; @endphp
                <div class="type-tiles">
                    <div class="type-tile">
                        <div class="tt-icon" style="background: #dbeafe; color: #1e40af;">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <div class="tt-value" style="color: #1e40af;">{{ number_format($lendCount) }}</div>
                        <div class="tt-label">Lending</div>
                        @if ($typeTotal > 0)
                            <span class="tt-pct">{{ round(($lendCount / $typeTotal) * 100, 1) }}%</span>
                        @endif
                    </div>
                    <div class="type-tile">
                        <div class="tt-icon" style="background: #fce7f3; color: #9d174d;">
                            <i class="bi bi-bag"></i>
                        </div>
                        <div class="tt-value" style="color: #9d174d;">{{ number_format($sellCount) }}</div>
                        <div class="tt-label">Selling</div>
                        @if ($typeTotal > 0)
                            <span class="tt-pct">{{ round(($sellCount / $typeTotal) * 100, 1) }}%</span>
                        @endif
                    </div>
                    <div class="type-tile">
                        <div class="tt-icon" style="background: #d1fae5; color: #065f46;">
                            <i class="bi bi-gift"></i>
                        </div>
                        <div class="tt-value" style="color: #065f46;">{{ number_format($shareCount) }}</div>
                        <div class="tt-label">Sharing</div>
                        @if ($typeTotal > 0)
                            <span class="tt-pct">{{ round(($shareCount / $typeTotal) * 100, 1) }}%</span>
                        @endif
                    </div>
                </div>

                {{-- Type bar chart --}}
                @if ($typeTotal > 0)
                    <div style="padding: 20px 24px; border-top: 1px solid #f3f4f6;">
                        <canvas id="typeChart" height="190"></canvas>
                    </div>
                @else
                    <div style="padding: 40px 24px; text-align: center; color: #9ca3af; font-size: 0.875rem; border-top: 1px solid #f3f4f6;">
                        No transaction type data yet.
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- end row --}}

    {{-- ══════════════════════════════════════
         QUICK LINKS
    ══════════════════════════════════════ --}}
    <div class="section-card">
        <div class="section-card-header">
            <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
        </div>
        <div style="padding: 18px 22px; display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('uni-admin.transactions.index') }}"
               class="btn btn-outline-primary btn-sm px-4" style="font-size: 0.82rem;">
                <i class="bi bi-arrow-left-right me-2"></i>View All Transactions
            </a>
            <a href="{{ route('uni-admin.transactions.index') }}?status=late"
               class="btn btn-sm px-4" style="font-size: 0.82rem; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                <i class="bi bi-clock-history me-2"></i>View Late Transactions
            </a>
            <a href="{{ route('uni-admin.reports.export') }}?type=transactions"
               class="btn btn-sm px-4" style="font-size: 0.82rem; background: #f9fafb; color: #374151; border: 1px solid #e5e7eb;">
                <i class="bi bi-file-earmark-spreadsheet me-2" style="color: #059669;"></i>Export CSV
            </a>
        </div>
    </div>

</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Status donut chart ───────────────────────────────
    @if ($totalTransactions > 0)
    new Chart(document.getElementById('statusDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Active/Pending', 'Completed', 'Late'],
            datasets: [{
                data: [{{ $activeTransactions }}, {{ $completedCount }}, {{ $lateTransactions }}],
                backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
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
                        label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                    }
                }
            }
        }
    });
    @endif

    // ── Type bar chart ───────────────────────────────────
    @php $typeTotal = $lendCount + $sellCount + $shareCount; @endphp
    @if ($typeTotal > 0)
    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: {
            labels: ['Lending', 'Selling', 'Sharing'],
            datasets: [{
                data: [{{ $lendCount }}, {{ $sellCount }}, {{ $shareCount }}],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.15)',
                    'rgba(157, 23, 77, 0.12)',
                    'rgba(16, 185, 129, 0.15)',
                ],
                borderColor: ['#3b82f6', '#9d174d', '#10b981'],
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} transactions`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#9ca3af' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 }, color: '#9ca3af' },
                    grid: { color: '#f3f4f6' }
                }
            }
        }
    });
    @endif

});
</script>
@endsection