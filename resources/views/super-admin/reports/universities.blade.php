@extends('layouts.app')

@section('title', 'University Report - Super Admin')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-building"></i> University Report
        </h1>
        <p class="text-muted">University statistics and trends</p>
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
                <label class="form-label">Time Period (Last N Days)</label>
                <select name="period" class="form-select">
                    <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="180" {{ $period == 180 ? 'selected' : '' }}>Last 6 Months</option>
                    <option value="365" {{ $period == 365 ? 'selected' : '' }}>Last Year</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Status Overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-building" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ $totalUniversities }}</h3>
                <p class="text-muted mb-0">Total Universities</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                <h3 style="color: #28a745; font-weight: 700; margin: 10px 0;">{{ $approvedUniversities }}</h3>
                <p class="text-muted mb-0">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-clock" style="font-size: 2rem; color: #ffc107;"></i>
                <h3 style="color: #ffc107; font-weight: 700; margin: 10px 0;">{{ $pendingUniversities }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-x-circle" style="font-size: 2rem; color: #dc3545;"></i>
                <h3 style="color: #dc3545; font-weight: 700; margin: 10px 0;">{{ $rejectedUniversities }}</h3>
                <p class="text-muted mb-0">Rejected</p>
            </div>
        </div>
    </div>
</div>

<!-- Growth Metrics -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up"></i> Growth Metrics
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div style="background: #e7f1ff; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                    <h4 style="color: #0d6efd; font-weight: 700;">{{ $newApplications }}</h4>
                    <p class="text-muted mb-0">New Applications (Last {{ $period }} days)</p>
                </div>
            </div>
            <div class="col-md-6">
                <div style="background: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                    <h4 style="color: #28a745; font-weight: 700;">
                        {{ $approvedUniversities > 0 ? number_format(($approvedUniversities / $totalUniversities) * 100, 1) : 0 }}%
                    </h4>
                    <p class="text-muted mb-0">Approval Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Universities by User Count -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-trophy"></i> Top 10 Universities by User Count
    </div>
    <div class="card-body">
        @if ($topUniversities->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Rank</th>
                            <th>University</th>
                            <th>Location</th>
                            <th>Users</th>
                            <th>Domain</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topUniversities as $index => $uni)
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
                                    <span class="badge bg-primary" style="font-size: 0.9rem;">
                                        {{ $uni->users_count }} users
                                    </span>
                                </td>
                                <td>
                                    <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px;">
                                        @{{ $uni->domain }}
                                    </code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mb-0 mt-3">No approved universities yet</p>
            </div>
        @endif
    </div>
</div>

<!-- Geographic Distribution -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-geo-alt"></i> Geographic Distribution (By State)
    </div>
    <div class="card-body">
        @if ($byState->count() > 0)
            <div class="row">
                @foreach ($byState as $state)
                    <div class="col-md-4 mb-3">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #0d6efd;">
                            <h5 style="color: #0d6efd; font-weight: 700;">{{ $state->count }}</h5>
                            <p class="text-muted mb-0">{{ $state->state }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mb-0 mt-3">No state data available</p>
            </div>
        @endif
    </div>
</div>

<!-- Monthly Application Trend -->
@if ($monthlyApplications->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-bar-chart-line"></i> Monthly Application Trend (Last 6 Months)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Applications</th>
                            <th>Visualization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $maxCount = $monthlyApplications->max('count');
                        @endphp
                        @foreach ($monthlyApplications as $month)
                            <tr>
                                <td>
                                    <strong>
                                        {{ DateTime::createFromFormat('!m', $month->month)->format('F') }} {{ $month->year }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $month->count }} applications</span>
                                </td>
                                <td>
                                    <div style="background: #e9ecef; height: 25px; border-radius: 4px; overflow: hidden;">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; width: {{ ($month->count / $maxCount) * 100 }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Export Section -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-download"></i> Export Data
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Download university data for external analysis</p>
        <form method="POST" action="{{ route('super-admin.reports.export') }}">
            @csrf
            <input type="hidden" name="type" value="universities">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export as CSV
            </button>
        </form>
    </div>
</div>

@endsection