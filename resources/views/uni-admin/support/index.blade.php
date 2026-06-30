@extends('layouts.app')

@section('title', 'Support Tickets - UniShare Admin')

@section('content')

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div style="margin-bottom: 32px;">
    <h1 class="page-title">Support Tickets</h1>
    <p class="text-muted">Manage and respond to support requests from your campus community</p>
</div>

{{-- ── Stats Row ────────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-circle" style="font-size: 1.8rem; color: #dc3545; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #dc3545; font-weight: 700; margin-bottom: 2px;">{{ $counts['open'] }}</h5>
                <small class="text-muted">Open</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-arrow-repeat" style="font-size: 1.8rem; color: #ffc107; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #856404; font-weight: 700; margin-bottom: 2px;">{{ $counts['in_progress'] }}</h5>
                <small class="text-muted">In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-check-circle" style="font-size: 1.8rem; color: #28a745; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #28a745; font-weight: 700; margin-bottom: 2px;">{{ $counts['resolved'] }}</h5>
                <small class="text-muted">Resolved</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-archive" style="font-size: 1.8rem; color: #6c757d; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #6c757d; font-weight: 700; margin-bottom: 2px;">{{ $counts['closed'] }}</h5>
                <small class="text-muted">Closed</small>
            </div>
        </div>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('uni-admin.support.index') }}" class="row g-2 align-items-center">

            {{-- Search --}}
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Search subject or student name..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Status --}}
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="open"        {{ request('status') === 'open'        ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved"    {{ request('status') === 'resolved'    ? 'selected' : '' }}>Resolved</option>
                    <option value="closed"      {{ request('status') === 'closed'      ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            {{-- Category --}}
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <option value="transaction_issue" {{ request('category') === 'transaction_issue' ? 'selected' : '' }}>Transaction Issue</option>
                    <option value="item_condition"    {{ request('category') === 'item_condition'    ? 'selected' : '' }}>Item Condition</option>
                    <option value="penalty_dispute"   {{ request('category') === 'penalty_dispute'   ? 'selected' : '' }}>Penalty Dispute</option>
                    <option value="user_behaviour"    {{ request('category') === 'user_behaviour'    ? 'selected' : '' }}>User Behaviour</option>
                    <option value="account_issue"     {{ request('category') === 'account_issue'     ? 'selected' : '' }}>Account Issue</option>
                    <option value="other"             {{ request('category') === 'other'             ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            {{-- Priority --}}
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="high"   {{ request('priority') === 'high'   ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low"    {{ request('priority') === 'low'    ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel"></i>
                </button>
                @if (request('search') || request('status') || request('category') || request('priority'))
                    <a href="{{ route('uni-admin.support.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Ticket Table ─────────────────────────────────────────── --}}
@if ($tickets->count() > 0)
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="width: 40px; padding: 14px 16px; color: #666; font-size: 0.82rem;">#</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;">Subject</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;">Student</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;">Category</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;">Priority</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;">Status</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;">Opened</th>
                        <th style="padding: 14px 16px; color: #666; font-size: 0.82rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td style="padding: 14px 16px; color: #888; font-size: 0.82rem; vertical-align: middle;">
                                {{ $ticket->id }}
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <div style="font-weight: 600; color: #1a1a2e; margin-bottom: 2px;">
                                    {{-- Priority indicator --}}
                                    @if ($ticket->priority === 'high')
                                        <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #dc3545; margin-right: 6px; vertical-align: middle;"></span>
                                    @elseif ($ticket->priority === 'medium')
                                        <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #ffc107; margin-right: 6px; vertical-align: middle;"></span>
                                    @else
                                        <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #adb5bd; margin-right: 6px; vertical-align: middle;"></span>
                                    @endif
                                    {{ Str::limit($ticket->subject, 50) }}
                                </div>
                                @if ($ticket->latestReply)
                                    <small class="text-muted">
                                        Last: {{ Str::limit($ticket->latestReply->message, 40) }}
                                        · {{ $ticket->latestReply->time_display }}
                                    </small>
                                @else
                                    <small class="text-muted">No replies yet</small>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <span style="font-weight: 600; color: #333;">{{ $ticket->user->name }}</span>
                                <br>
                                <small class="text-muted">{{ $ticket->user->email }}</small>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <small style="color: #555;">{{ $ticket->getCategoryLabel() }}</small>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <span class="badge bg-{{ $ticket->getPriorityBadgeColor() }}">
                                    {{ $ticket->getPriorityLabel() }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <span class="badge bg-{{ $ticket->getStatusBadgeColor() }}">
                                    {{ $ticket->getStatusLabel() }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <small class="text-muted">{{ $ticket->created_at->format('M d, Y') }}</small>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <a href="{{ route('uni-admin.support.show', $ticket) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $tickets->links() }}
    </div>

@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-ticket-perforated"
               style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 16px;"></i>
            <h5 style="color: #333; font-weight: 600; margin-bottom: 8px;">No tickets found</h5>
            <p class="text-muted mb-0">
                @if (request()->hasAny(['search', 'status', 'category', 'priority']))
                    Try adjusting your filters.
                @else
                    No support tickets have been raised by your campus community yet.
                @endif
            </p>
        </div>
    </div>
@endif

@endsection