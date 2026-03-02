@extends('layouts.app')

@section('title', 'Platform Overview - Super Admin')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-speedometer2"></i> Platform Overview
        </h1>
        <p class="text-muted">Complete platform statistics and metrics</p>
    </div>
    <div>
        <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-10">
                <label class="form-label">Activity Period (Last N Days)</label>
                <select name="period" class="form-select">
                    <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="180" {{ $period == 180 ? 'selected' : '' }}>Last 6 Months</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Apply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Platform Totals -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 2.5rem; color: #0d6efd;"></i>
                <h2 style="color: #0d6efd; font-weight: 700; margin: 15px 0;">{{ number_format($totalUsers) }}</h2>
                <p class="text-muted mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-box" style="font-size: 2.5rem; color: #28a745;"></i>
                <h2 style="color: #28a745; font-weight: 700; margin: 15px 0;">{{ number_format($totalItems) }}</h2>
                <p class="text-muted mb-0">Total Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-arrow-left-right" style="font-size: 2.5rem; color: #ffc107;"></i>
                <h2 style="color: #ffc107; font-weight: 700; margin: 15px 0;">{{ number_format($totalTransactions) }}</h2>
                <p class="text-muted mb-0">Transactions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem; color: #dc3545;"></i>
                <h2 style="color: #dc3545; font-weight: 700; margin: 15px 0;">{{ number_format($totalPenalties) }}</h2>
                <p class="text-muted mb-0">Penalties</p>
            </div>
        </div>
    </div>
</div>

<!-- Period Activity -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-activity"></i> Activity in Last {{ $period }} Days
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div style="background: #e7f1ff; padding: 20px; border-radius: 8px; text-center;">
                    <i class="bi bi-person-plus" style="font-size: 2rem; color: #0d6efd;"></i>
                    <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ number_format($newUsers) }}</h3>
                    <p class="text-muted mb-0">New Users</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div style="background: #d4edda; padding: 20px; border-radius: 8px; text-center;">
                    <i class="bi bi-box-arrow-in-right" style="font-size: 2rem; color: #28a745;"></i>
                    <h3 style="color: #28a745; font-weight: 700; margin: 10px 0;">{{ number_format($newItems) }}</h3>
                    <p class="text-muted mb-0">New Items Listed</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div style="background: #fff3cd; padding: 20px; border-radius: 8px; text-center;">
                    <i class="bi bi-arrow-repeat" style="font-size: 2rem; color: #ffc107;"></i>
                    <h3 style="color: #ffc107; font-weight: 700; margin: 10px 0;">{{ number_format($newTransactions) }}</h3>
                    <p class="text-muted mb-0">New Transactions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Type Breakdown -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-pie-chart"></i> Transaction Type Distribution
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-left-right" style="font-size: 2rem; color: #17a2b8;"></i>
                        <h3 style="color: #17a2b8; font-weight: 700; margin: 15px 0;">{{ number_format($lendCount) }}</h3>
                        <p class="text-muted mb-2">Lending Transactions</p>
                        <small class="text-muted">
                            {{ $totalTransactions > 0 ? number_format(($lendCount / $totalTransactions) * 100, 1) : 0 }}% of total
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-tag" style="font-size: 2rem; color: #dc3545;"></i>
                        <h3 style="color: #dc3545; font-weight: 700; margin: 15px 0;">{{ number_format($sellCount) }}</h3>
                        <p class="text-muted mb-2">Selling Transactions</p>
                        <small class="text-muted">
                            {{ $totalTransactions > 0 ? number_format(($sellCount / $totalTransactions) * 100, 1) : 0 }}% of total
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-gift" style="font-size: 2rem; color: #28a745;"></i>
                        <h3 style="color: #28a745; font-weight: 700; margin: 15px 0;">{{ number_format($shareCount) }}</h3>
                        <p class="text-muted mb-2">Sharing Transactions</p>
                        <small class="text-muted">
                            {{ $totalTransactions > 0 ? number_format(($shareCount / $totalTransactions) * 100, 1) : 0 }}% of total
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Bar Chart -->
        <div class="mt-4">
            <h6 class="text-muted mb-3">Transaction Distribution</h6>
            @php
                $maxCount = max($lendCount, $sellCount, $shareCount);
            @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-weight: 600; color: #17a2b8;">Lend</span>
                    <span style="font-weight: 600; color: #17a2b8;">{{ $lendCount }}</span>
                </div>
                <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #17a2b8; height: 100%; width: {{ $maxCount > 0 ? ($lendCount / $maxCount) * 100 : 0 }}%;"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-weight: 600; color: #dc3545;">Sell</span>
                    <span style="font-weight: 600; color: #dc3545;">{{ $sellCount }}</span>
                </div>
                <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #dc3545; height: 100%; width: {{ $maxCount > 0 ? ($sellCount / $maxCount) * 100 : 0 }}%;"></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-weight: 600; color: #28a745;">Share</span>
                    <span style="font-weight: 600; color: #28a745;">{{ $shareCount }}</span>
                </div>
                <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden;">
                    <div style="background: #28a745; height: 100%; width: {{ $maxCount > 0 ? ($shareCount / $maxCount) * 100 : 0 }}%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-currency-dollar"></i> Penalty Summary
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div style="background: #fff3cd; padding: 25px; border-radius: 8px; border-left: 5px solid #ffc107;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Outstanding Penalties</p>
                            <h2 style="color: #856404; font-weight: 700; margin: 0;">৳{{ number_format($outstandingPenalties, 2) }}</h2>
                        </div>
                        <i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #ffc107; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div style="background: #d4edda; padding: 25px; border-radius: 8px; border-left: 5px solid #28a745;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Collected Penalties</p>
                            <h2 style="color: #155724; font-weight: 700; margin: 0;">৳{{ number_format($collectedPenalties, 2) }}</h2>
                        </div>
                        <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <strong>Total Penalties Issued:</strong> ৳{{ number_format($outstandingPenalties + $collectedPenalties, 2) }}
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Summary -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-check"></i> Key Metrics Summary
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-people text-primary"></i> Average Users per University</span>
                        <strong>{{ $totalUsers > 0 && \App\Models\University::approved()->count() > 0 ? number_format($totalUsers / \App\Models\University::approved()->count(), 1) : 0 }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-box text-success"></i> Average Items per User</span>
                        <strong>{{ $totalUsers > 0 ? number_format($totalItems / $totalUsers, 2) : 0 }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-arrow-repeat text-warning"></i> Average Transactions per User</span>
                        <strong>{{ $totalUsers > 0 ? number_format($totalTransactions / $totalUsers, 2) : 0 }}</strong>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-percent text-info"></i> User Growth Rate ({{ $period }} days)</span>
                        <strong class="text-success">
                            @php
                                $growthRate = $totalUsers > 0 ? ($newUsers / $totalUsers) * 100 : 0;
                            @endphp
                            +{{ number_format($growthRate, 1) }}%
                        </strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-graph-up text-primary"></i> Transaction Velocity</span>
                        <strong>{{ number_format($newTransactions / $period, 1) }}/day</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-trophy text-warning"></i> Platform Health Score</span>
                        <strong class="text-success">
                            @php
                                // Simple health score based on activity
                                $healthScore = min(100, ($newUsers + $newItems + $newTransactions) / 3);
                            @endphp
                            {{ number_format($healthScore, 0) }}/100
                        </strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection