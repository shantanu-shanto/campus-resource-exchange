@extends('layouts.app')

@section('title', 'Reports - Super Admin')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">
        <i class="bi bi-graph-up"></i> Platform Reports
    </h1>
    <p class="text-muted">Analytics and insights across the platform</p>
</div>

<!-- Report Categories -->
<div class="row">
    <!-- University Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 60px; height: 60px; background: #e7f1ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-building" style="font-size: 2rem; color: #0d6efd;"></i>
                    </div>
                    <span class="badge bg-primary">Analytics</span>
                </div>
                <h4 style="color: #333; font-weight: 700; margin-bottom: 15px;">University Report</h4>
                <p class="text-muted mb-4">
                    View university statistics, application trends, state-wise distribution, and top universities by user count.
                </p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Status breakdown</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Monthly application trends</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Geographic distribution</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Top universities ranking</li>
                </ul>
                <a href="{{ route('super-admin.reports.universities') }}" class="btn btn-primary w-100">
                    <i class="bi bi-bar-chart"></i> View University Report
                </a>
            </div>
        </div>
    </div>

    <!-- Platform Overview -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 60px; height: 60px; background: #fff3cd; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-speedometer2" style="font-size: 2rem; color: #ffc107;"></i>
                    </div>
                    <span class="badge bg-warning">Overview</span>
                </div>
                <h4 style="color: #333; font-weight: 700; margin-bottom: 15px;">Platform Overview</h4>
                <p class="text-muted mb-4">
                    Complete platform statistics including users, items, transactions, and financial metrics.
                </p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Total users & items</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Transaction breakdown</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Period activity analysis</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Penalty statistics</li>
                </ul>
                <a href="{{ route('super-admin.reports.platform-overview') }}" class="btn btn-warning w-100">
                    <i class="bi bi-speedometer2"></i> View Platform Overview
                </a>
            </div>
        </div>
    </div>

    <!-- User Growth -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 60px; height: 60px; background: #d4edda; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people" style="font-size: 2rem; color: #28a745;"></i>
                    </div>
                    <span class="badge bg-success">Growth</span>
                </div>
                <h4 style="color: #333; font-weight: 700; margin-bottom: 15px;">User Growth Report</h4>
                <p class="text-muted mb-4">
                    Track user registration trends, monthly growth patterns, and university-wise user distribution.
                </p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Monthly registrations</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Growth charts (12 months)</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Users by university</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Verification rates</li>
                </ul>
                <a href="{{ route('super-admin.reports.user-growth') }}" class="btn btn-success w-100">
                    <i class="bi bi-graph-up-arrow"></i> View User Growth
                </a>
            </div>
        </div>
    </div>

    <!-- Export Data -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 60px; height: 60px; background: #e7f1ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-download" style="font-size: 2rem; color: #0d6efd;"></i>
                    </div>
                    <span class="badge bg-info">Export</span>
                </div>
                <h4 style="color: #333; font-weight: 700; margin-bottom: 15px;">Export Data</h4>
                <p class="text-muted mb-4">
                    Download platform data as CSV files for external analysis and record-keeping.
                </p>
                <div class="d-grid gap-2 mb-3">
                    <form method="POST" action="{{ route('super-admin.reports.export') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="universities">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-building"></i> Export Universities
                        </button>
                    </form>
                    <form method="POST" action="{{ route('super-admin.reports.export') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="users">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-people"></i> Export Users
                        </button>
                    </form>
                    <form method="POST" action="{{ route('super-admin.reports.export') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="type" value="transactions">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-arrow-left-right"></i> Export Transactions
                        </button>
                    </form>
                </div>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> CSV files will download immediately
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Insights -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-lightning"></i> Quick Insights
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <p class="text-muted mb-3">
                    Reports are updated in real-time and reflect the current state of the platform. 
                    Use the export feature for historical record-keeping and compliance purposes.
                </p>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Tip:</strong> Bookmark frequently used reports for quick access. All reports support filtering by time period.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection