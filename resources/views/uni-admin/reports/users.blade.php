@extends('layouts.app')

@section('title', 'User Report — UniShare Admin')

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
    .stat-card.highlight-gray   { border-left: 3px solid #9ca3af; }

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

    /* ── Status breakdown ──────────────────────────────── */
    .status-breakdown {
        padding: 20px 24px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 14px;
    }

    .breakdown-item {
        background: #f9fafb;
        border-radius: 10px;
        padding: 14px 16px;
        text-align: center;
    }

    .breakdown-item .bi-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 5px;
    }

    .breakdown-item .bi-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .breakdown-item .bi-bar {
        height: 4px;
        border-radius: 2px;
        margin-top: 10px;
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
        min-width: 80px;
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
        transition: width 0.6s ease;
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

    @media (max-width: 576px) {
        .stat-cards { grid-template-columns: repeat(2, 1fr); }
        .status-breakdown { grid-template-columns: repeat(2, 1fr); }
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
                <h1><i class="bi bi-people me-2 text-primary"></i>User Report</h1>
                <p>Registrations, verifications, and campus membership activity.</p>
            </div>
            <a href="{{ route('uni-admin.reports.export') }}?type=users" class="btn-export">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         PERIOD SELECTOR
    ══════════════════════════════════════ --}}
    <div class="period-bar">
        <span>Show new users in the last:</span>
        @foreach ([7, 14, 30, 60, 90] as $p)
            <a href="{{ route('uni-admin.reports.users') }}?period={{ $p }}"
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
            <div class="stat-label"><i class="bi bi-people" style="color: #3b82f6;"></i> Total Users</div>
            <div class="stat-value">{{ number_format($totalUsers) }}</div>
            <div class="stat-sub">All registered campus members</div>
        </div>

        <div class="stat-card highlight-green">
            <div class="stat-label"><i class="bi bi-check-circle" style="color: #10b981;"></i> Verified</div>
            <div class="stat-value" style="color: #059669;">{{ number_format($verifiedUsers) }}</div>
            <div class="stat-sub">Active platform access</div>
        </div>

        <div class="stat-card highlight-yellow">
            <div class="stat-label"><i class="bi bi-clock" style="color: #f59e0b;"></i> Pending</div>
            <div class="stat-value" style="color: #d97706;">{{ number_format($pendingUsers) }}</div>
            <div class="stat-sub">Awaiting verification</div>
        </div>

        <div class="stat-card highlight-red">
            <div class="stat-label"><i class="bi bi-x-circle" style="color: #ef4444;"></i> Rejected</div>
            <div class="stat-value" style="color: #dc2626;">{{ number_format($rejectedUsers) }}</div>
            <div class="stat-sub">Registration denied</div>
        </div>

        <div class="stat-card highlight-blue">
            <div class="stat-label"><i class="bi bi-plus-circle" style="color: #3b82f6;"></i> New ({{ $period }}d)</div>
            <div class="stat-value" style="color: #2563eb;">{{ number_format($newUsers) }}</div>
            <div class="stat-sub">Last {{ $period }} days</div>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- LEFT — Monthly Chart ─────────────── --}}
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-bar-chart"></i> Monthly Registrations</h5>
                    <span class="header-sub">Last 6 months</span>
                </div>
                <div class="chart-wrap">
                    @if ($monthlyRegistrations->count())
                        <canvas id="registrationsChart" height="220"></canvas>
                    @else
                        <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                            <i class="bi bi-bar-chart" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #d1d5db;"></i>
                            <p style="font-size: 0.875rem; margin: 0;">No registration data for the last 6 months.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT — Status Breakdown ─────────── --}}
        <div class="col-lg-5">
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="bi bi-pie-chart"></i> Status Breakdown</h5>
                    <span class="header-sub">{{ number_format($totalUsers) }} total</span>
                </div>

                @php
                    $progressItems = [
                        ['label' => 'Verified',  'count' => $verifiedUsers,  'color' => '#10b981'],
                        ['label' => 'Pending',   'count' => $pendingUsers,   'color' => '#f59e0b'],
                        ['label' => 'Rejected',  'count' => $rejectedUsers,  'color' => '#ef4444'],
                    ];
                @endphp

                @if ($totalUsers > 0)
                    @foreach ($progressItems as $row)
                        @php $pct = round(($row['count'] / $totalUsers) * 100, 1); @endphp
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
                @else
                    <div style="padding: 32px 24px; text-align: center; color: #9ca3af; font-size: 0.875rem;">
                        No users registered yet.
                    </div>
                @endif

                {{-- Donut chart --}}
                @if ($totalUsers > 0)
                    <div style="padding: 20px 24px; border-top: 1px solid #f3f4f6;">
                        <canvas id="statusDonut" height="180"></canvas>
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- end row --}}

</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Monthly registrations bar chart ─────────────────
    @if ($monthlyRegistrations->count())
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    const regLabels = @json($monthlyRegistrations->map(fn($r) => monthNames[$r->month - 1] . ' ' . $r->year));
    const regData   = @json($monthlyRegistrations->pluck('count'));

    new Chart(document.getElementById('registrationsChart'), {
        type: 'bar',
        data: {
            labels: regLabels,
            datasets: [{
                label: 'New Registrations',
                data: regData,
                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                borderColor: '#3b82f6',
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
                        label: ctx => ` ${ctx.parsed.y} new registrations`
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
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 },
                        color: '#9ca3af'
                    },
                    grid: { color: '#f3f4f6' }
                }
            }
        }
    });
    @endif

    // ── Status donut chart ───────────────────────────────
    @if ($totalUsers > 0)
    new Chart(document.getElementById('statusDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Verified', 'Pending', 'Rejected'],
            datasets: [{
                data: [{{ $verifiedUsers }}, {{ $pendingUsers }}, {{ $rejectedUsers }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
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
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} users`
                    }
                }
            }
        }
    });
    @endif

});
</script>
@endsection