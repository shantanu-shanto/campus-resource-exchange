@extends('layouts.app')

@section('title', 'User Growth Report - Super Admin')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-graph-up-arrow"></i> User Growth Report
        </h1>
        <p class="text-muted">Track user registration trends and growth patterns</p>
    </div>
    <div>
        <a href="{{ route('super-admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 2.5rem; color: #0d6efd;"></i>
                <h2 style="color: #0d6efd; font-weight: 700; margin: 15px 0;">
                    {{ number_format(\App\Models\User::where('role', 'user')->count()) }}
                </h2>
                <p class="text-muted mb-0">Total Users (All Time)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-month" style="font-size: 2.5rem; color: #28a745;"></i>
                <h2 style="color: #28a745; font-weight: 700; margin: 15px 0;">
                    {{ number_format(\App\Models\User::where('role', 'user')->where('created_at', '>=', now()->subDays(30))->count()) }}
                </h2>
                <p class="text-muted mb-0">New Users (Last 30 Days)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-graph-up" style="font-size: 2.5rem; color: #ffc107;"></i>
                <h2 style="color: #ffc107; font-weight: 700; margin: 15px 0;">
                    @php
                        $thisMonth = \App\Models\User::where('role', 'user')->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
                        $lastMonth = \App\Models\User::where('role', 'user')->whereYear('created_at', now()->subMonth()->year)->whereMonth('created_at', now()->subMonth()->month)->count();
                        $growth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;
                    @endphp
                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                </h2>
                <p class="text-muted mb-0">Monthly Growth Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Registration Trend -->
@if ($monthlyUsers->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-bar-chart-line"></i> Monthly User Registrations (Last 12 Months)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Month</th>
                            <th style="width: 150px;">Registrations</th>
                            <th>Visualization</th>
                            <th style="width: 120px;">Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $maxCount = $monthlyUsers->max('count');
                            $previousCount = 0;
                        @endphp
                        @foreach ($monthlyUsers as $index => $month)
                            @php
                                $growthRate = $previousCount > 0 ? (($month->count - $previousCount) / $previousCount) * 100 : 0;
                                $previousCount = $month->count;
                            @endphp
                            <tr>
                                <td>
                                    <strong>
                                        {{ DateTime::createFromFormat('!m', $month->month)->format('F') }} {{ $month->year }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary" style="font-size: 0.9rem;">
                                        {{ $month->count }} users
                                    </span>
                                </td>
                                <td>
                                    <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden; position: relative;">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; width: {{ ($month->count / $maxCount) * 100 }}%; transition: width 0.3s ease;"></div>
                                        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-weight: 600; color: #333; font-size: 0.85rem;">
                                            {{ $month->count }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if ($index > 0)
                                        @if ($growthRate >= 0)
                                            <span class="badge bg-success">
                                                <i class="bi bi-arrow-up"></i> {{ number_format($growthRate, 1) }}%
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-down"></i> {{ number_format(abs($growthRate), 1) }}%
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8f9fa; font-weight: 700;">
                            <td>Total (12 Months)</td>
                            <td colspan="3">
                                <span class="badge bg-primary" style="font-size: 1rem;">
                                    {{ number_format($monthlyUsers->sum('count')) }} registrations
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Growth Insights -->
            <div class="alert alert-info mt-4">
                <i class="bi bi-lightbulb"></i> 
                <strong>Insights:</strong>
                @php
                    $avgMonthly = $monthlyUsers->avg('count');
                    $peakMonth = $monthlyUsers->sortByDesc('count')->first();
                @endphp
                Average monthly registrations: <strong>{{ number_format($avgMonthly, 0) }}</strong> users. 
                Peak month: <strong>{{ DateTime::createFromFormat('!m', $peakMonth->month)->format('F') }} {{ $peakMonth->year }}</strong> with {{ $peakMonth->count }} registrations.
            </div>
        </div>
    </div>
@else
    <div class="card mb-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3 mb-0">No user registration data available for the last 12 months</p>
        </div>
    </div>
@endif

<!-- Users by University -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-building"></i> Verified Users by University
    </div>
    <div class="card-body">
        @if ($usersByUniversity->where('users_count', '>', 0)->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>University</th>
                            <th>Location</th>
                            <th>Verified Users</th>
                            <th>Share</th>
                            <th>Visualization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalVerifiedUsers = $usersByUniversity->sum('users_count');
                            $maxUsers = $usersByUniversity->max('users_count');
                        @endphp
                        @foreach ($usersByUniversity->where('users_count', '>', 0) as $index => $uni)
                            <tr>
                                <td>
                                    <div style="width: 35px; height: 35px; background: {{ $index < 3 ? '#ffc107' : '#e9ecef' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: {{ $index < 3 ? '#fff' : '#666' }};">
                                        {{ $index + 1 }}
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('super-admin.universities.show', $uni) }}" style="font-weight: 600; color: #0d6efd; text-decoration: none;">
                                        {{ $uni->name }}
                                    </a>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt"></i> {{ $uni->city }}, {{ $uni->state }}
                                </td>
                                <td>
                                    <span class="badge bg-success" style="font-size: 0.9rem;">
                                        {{ number_format($uni->users_count) }} users
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $totalVerifiedUsers > 0 ? number_format(($uni->users_count / $totalVerifiedUsers) * 100, 1) : 0 }}%</strong>
                                </td>
                                <td>
                                    <div style="background: #e9ecef; height: 25px; border-radius: 4px; overflow: hidden; min-width: 200px;">
                                        <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); height: 100%; width: {{ ($uni->users_count / $maxUsers) * 100 }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8f9fa; font-weight: 700;">
                            <td colspan="3">Total Verified Users</td>
                            <td colspan="3">
                                <span class="badge bg-success" style="font-size: 1rem;">
                                    {{ number_format($totalVerifiedUsers) }} users
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3 mb-0">No verified users across universities yet</p>
            </div>
        @endif
    </div>
</div>

<!-- User Distribution Stats -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-pie-chart"></i> User Distribution Statistics
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h6 class="text-muted mb-3">Users by Status</h6>
                @php
                    $verified = \App\Models\User::where('role', 'user')->where('status', 'verified')->count();
                    $pending = \App\Models\User::where('role', 'user')->where('status', 'pending')->count();
                    $rejected = \App\Models\User::where('role', 'user')->where('status', 'rejected')->count();
                    $totalStatus = $verified + $pending + $rejected;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="color: #28a745; font-weight: 600;">Verified</span>
                        <span style="font-weight: 700;">{{ $verified }} ({{ $totalStatus > 0 ? number_format(($verified / $totalStatus) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden;">
                        <div style="background: #28a745; height: 100%; width: {{ $totalStatus > 0 ? ($verified / $totalStatus) * 100 : 0 }}%;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="color: #ffc107; font-weight: 600;">Pending</span>
                        <span style="font-weight: 700;">{{ $pending }} ({{ $totalStatus > 0 ? number_format(($pending / $totalStatus) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden;">
                        <div style="background: #ffc107; height: 100%; width: {{ $totalStatus > 0 ? ($pending / $totalStatus) * 100 : 0 }}%;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="color: #dc3545; font-weight: 600;">Rejected</span>
                        <span style="font-weight: 700;">{{ $rejected }} ({{ $totalStatus > 0 ? number_format(($rejected / $totalStatus) * 100, 1) : 0 }}%)</span>
                    </div>
                    <div style="background: #e9ecef; height: 30px; border-radius: 4px; overflow: hidden;">
                        <div style="background: #dc3545; height: 100%; width: {{ $totalStatus > 0 ? ($rejected / $totalStatus) * 100 : 0 }}%;"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <h6 class="text-muted mb-3">Key Metrics</h6>
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-percent text-success"></i> Verification Rate</span>
                        <strong class="text-success">
                            {{ $totalStatus > 0 ? number_format(($verified / $totalStatus) * 100, 1) : 0 }}%
                        </strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-building text-primary"></i> Avg Users per University</span>
                        <strong>
                            {{ \App\Models\University::approved()->count() > 0 ? number_format($totalStatus / \App\Models\University::approved()->count(), 1) : 0 }}
                        </strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-calendar-week text-info"></i> Avg Weekly Registrations</span>
                        <strong>
                            {{ $monthlyUsers->count() > 0 ? number_format($monthlyUsers->sum('count') / ($monthlyUsers->count() * 4), 1) : 0 }}
                        </strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-trophy text-warning"></i> Largest University</span>
                        <strong>
                            @if ($usersByUniversity->where('users_count', '>', 0)->first())
                                {{ $usersByUniversity->where('users_count', '>', 0)->first()->users_count }} users
                            @else
                                N/A
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Section -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-download"></i> Export Data
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Download user data for external analysis</p>
        <form method="POST" action="{{ route('super-admin.reports.export') }}">
            @csrf
            <input type="hidden" name="type" value="users">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Users as CSV
            </button>
        </form>
    </div>
</div>

@endsection