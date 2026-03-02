@extends('layouts.app')

@section('title', 'Reports — UniShare Admin')

@section('extra-css')
<style>
    /* ── Page header ───────────────────────────────────── */
    .ua-page-header {
        margin-bottom: 32px;
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

    /* ── Report cards grid ─────────────────────────────── */
    .report-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    /* ── Report card ───────────────────────────────────── */
    .report-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s, transform 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .report-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.09);
        transform: translateY(-2px);
        color: inherit;
        text-decoration: none;
    }

    .report-card-top {
        padding: 26px 26px 20px;
        flex: 1;
    }

    .report-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 18px;
    }

    .report-card-icon.blue   { background: #dbeafe; color: #1e40af; }
    .report-card-icon.green  { background: #d1fae5; color: #065f46; }
    .report-card-icon.red    { background: #fee2e2; color: #991b1b; }

    .report-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0 0 8px 0;
    }

    .report-card p {
        font-size: 0.84rem;
        color: #6b7280;
        margin: 0 0 18px 0;
        line-height: 1.55;
    }

    .report-card-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .report-tag {
        background: #f3f4f6;
        color: #374151;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .report-card-footer {
        padding: 14px 26px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafafa;
    }

    .report-card-footer .footer-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
    }

    .report-card-footer .footer-arrow {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #e7f1ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: background 0.15s;
    }

    .report-card:hover .footer-arrow {
        background: #dbeafe;
    }

    /* ── Export section ────────────────────────────────── */
    .export-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }

    .export-section-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f3f4f6;
    }

    .export-section-header h4 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0 0 2px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .export-section-header h4 i { color: #0d6efd; }

    .export-section-header p {
        font-size: 0.8rem;
        color: #9ca3af;
        margin: 0;
    }

    .export-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
        padding: 20px 24px;
    }

    .export-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 16px;
        text-decoration: none;
        color: #374151;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.15s;
    }

    .export-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #1a1f36;
        text-decoration: none;
    }

    .export-btn i {
        font-size: 1rem;
        color: #059669;
    }

    .export-btn .export-sub {
        font-size: 0.72rem;
        color: #9ca3af;
        font-weight: 400;
        display: block;
        margin-top: 1px;
    }

    @media (max-width: 576px) {
        .report-cards-grid { grid-template-columns: 1fr; }
        .export-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div style="padding: 28px 0 60px;">

    {{-- ══════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════ --}}
    <div class="ua-page-header">
        <h1><i class="bi bi-bar-chart-line me-2 text-primary"></i>Reports</h1>
        <p>View detailed analytics and export data for your university campus.</p>
    </div>

    {{-- ══════════════════════════════════════
         REPORT TYPE CARDS
    ══════════════════════════════════════ --}}
    <div class="report-cards-grid">

        {{-- Users Report --}}
        <a href="{{ route('uni-admin.reports.users') }}" class="report-card">
            <div class="report-card-top">
                <div class="report-card-icon blue">
                    <i class="bi bi-people"></i>
                </div>
                <h3>User Report</h3>
                <p>Track registrations, verification status, and monthly growth trends for your campus community.</p>
                <div class="report-card-tags">
                    <span class="report-tag">Registrations</span>
                    <span class="report-tag">Verification</span>
                    <span class="report-tag">Monthly Trends</span>
                </div>
            </div>
            <div class="report-card-footer">
                <span class="footer-label">View User Report</span>
                <span class="footer-arrow"><i class="bi bi-arrow-right"></i></span>
            </div>
        </a>

        {{-- Transactions Report --}}
        <a href="{{ route('uni-admin.reports.transactions') }}" class="report-card">
            <div class="report-card-top">
                <div class="report-card-icon green">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <h3>Transaction Report</h3>
                <p>Analyse lending, selling, and sharing activity — see active, late, and completed exchanges at a glance.</p>
                <div class="report-card-tags">
                    <span class="report-tag">Lending</span>
                    <span class="report-tag">Selling</span>
                    <span class="report-tag">Activity</span>
                </div>
            </div>
            <div class="report-card-footer">
                <span class="footer-label">View Transaction Report</span>
                <span class="footer-arrow"><i class="bi bi-arrow-right"></i></span>
            </div>
        </a>

        {{-- Penalties Report --}}
        <a href="{{ route('uni-admin.reports.penalties') }}" class="report-card">
            <div class="report-card-top">
                <div class="report-card-icon red">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3>Penalty Report</h3>
                <p>Monitor outstanding, paid, and waived penalties — track total amounts collected and still outstanding.</p>
                <div class="report-card-tags">
                    <span class="report-tag">Outstanding</span>
                    <span class="report-tag">Collections</span>
                    <span class="report-tag">Waived</span>
                </div>
            </div>
            <div class="report-card-footer">
                <span class="footer-label">View Penalty Report</span>
                <span class="footer-arrow"><i class="bi bi-arrow-right"></i></span>
            </div>
        </a>

    </div>

    {{-- ══════════════════════════════════════
         CSV EXPORT SECTION
    ══════════════════════════════════════ --}}
    <div class="export-section">
        <div class="export-section-header">
            <h4><i class="bi bi-download"></i> Export Data</h4>
            <p>Download a full CSV snapshot of any data set from your university.</p>
        </div>
        <div class="export-grid">

            <a href="{{ route('uni-admin.reports.export') }}?type=users" class="export-btn">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <div>
                    Export Users
                    <span class="export-sub">Name, email, status, date</span>
                </div>
            </a>

            <a href="{{ route('uni-admin.reports.export') }}?type=transactions" class="export-btn">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <div>
                    Export Transactions
                    <span class="export-sub">Item, borrower, type, dates</span>
                </div>
            </a>

            <a href="{{ route('uni-admin.reports.export') }}?type=penalties" class="export-btn">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <div>
                    Export Penalties
                    <span class="export-sub">Borrower, amount, days late</span>
                </div>
            </a>

        </div>
    </div>

</div>
@endsection