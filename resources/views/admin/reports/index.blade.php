@extends('admin.layouts.app')

@section('page-title', 'Reports & Disputes')

@section('content')

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#open" type="button" role="tab">
            <i class="bi bi-exclamation-circle"></i> Open ({{ $openCount }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#resolved" type="button" role="tab">
            <i class="bi bi-check-circle"></i> Resolved ({{ $resolvedCount }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
            <i class="bi bi-list"></i> All ({{ $allCount }})
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Open Reports -->
    <div class="tab-pane fade show active" id="open" role="tabpanel">
        @if ($openReports->count() > 0)
            @foreach ($openReports as $report)
                <div class="admin-card mb-3">
                    <div class="admin-card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 style="color: #333; font-weight: 600; margin-bottom: 5px;">
                                    {{ $report->title }}
                                </h5>
                                <p class="text-muted small mb-2">Reported by: {{ $report->reporter->name }}</p>
                                <p style="color: #666; margin-bottom: 10px;">{{ Str::limit($report->description, 100) }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $report->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-danger mb-2 d-block">Open</span>
                                <a href="#" class="btn btn-sm btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#reportModal{{ $report->id }}">
                                    <i class="bi bi-eye"></i> View & Respond
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-muted text-center">No open reports</p>
        @endif
    </div>

    <!-- Resolved Reports -->
    <div class="tab-pane fade" id="resolved" role="tabpanel">
        @if ($resolvedReports->count() > 0)
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Reporter</th>
                            <th>Resolved By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resolvedReports as $report)
                            <tr>
                                <td><strong>{{ Str::limit($report->title, 30) }}</strong></td>
                                <td><small>{{ $report->reporter->name }}</small></td>
                                <td><small>Admin</small></td>
                                <td><small>{{ $report->resolved_at->format('M d, Y') }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center">No resolved reports</p>
        @endif
    </div>

    <!-- All Reports -->
    <div class="tab-pane fade" id="all" role="tabpanel">
        @if ($allReports->count() > 0)
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Reporter</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allReports as $report)
                            <tr>
                                <td><strong>{{ Str::limit($report->title, 30) }}</strong></td>
                                <td><small>{{ $report->reporter->name }}</small></td>
                                <td>
                                    @if ($report->status === 'open')
                                        <span class="badge bg-danger">Open</span>
                                    @else
                                        <span class="badge bg-success">Resolved</span>
                                    @endif
                                </td>
                                <td><small>{{ $report->created_at->format('M d, Y') }}</small></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-admin btn-admin-primary" data-bs-toggle="modal" data-bs-target="#reportModal{{ $report->id }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center">No reports</p>
        @endif
    </div>
</div>

<!-- Report Detail Modals -->
@foreach ($allReports as $report)
    <div class="modal fade" id="reportModal{{ $report->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $report->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Reported by:</strong> {{ $report->reporter->name }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($report->type) }}</p>
                    <p><strong>Description:</strong></p>
                    <p>{{ $report->description }}</p>
                    <p><strong>Status:</strong>
                        @if ($report->status === 'open')
                            <span class="badge bg-danger">Open</span>
                        @else
                            <span class="badge bg-success">Resolved</span>
                        @endif
                    </p>
                </div>
                <div class="modal-footer">
                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary">Mark as Resolved</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
