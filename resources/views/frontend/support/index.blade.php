@extends('layouts.app')

@section('title', 'My Support Tickets - UniShare')

@section('content')

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 class="page-title">My Support Tickets</h1>
        <p class="text-muted">Raise and track issues with your University Admin</p>
    </div>
    <a href="{{ route('frontend.support.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Raise a Ticket
    </a>
</div>

{{-- ── Status Tabs ──────────────────────────────────────────── --}}
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ !request('status') ? 'active' : '' }}"
           href="{{ route('frontend.support.index') }}">
            <i class="bi bi-ticket me-1"></i> All
            <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') === 'open' ? 'active' : '' }}"
           href="{{ route('frontend.support.index', ['status' => 'open']) }}">
            <i class="bi bi-circle me-1"></i> Open
            <span class="badge bg-danger ms-1">{{ $counts['open'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') === 'in_progress' ? 'active' : '' }}"
           href="{{ route('frontend.support.index', ['status' => 'in_progress']) }}">
            <i class="bi bi-arrow-repeat me-1"></i> In Progress
            <span class="badge bg-warning text-dark ms-1">{{ $counts['in_progress'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') === 'resolved' ? 'active' : '' }}"
           href="{{ route('frontend.support.index', ['status' => 'resolved']) }}">
            <i class="bi bi-check-circle me-1"></i> Resolved
            <span class="badge bg-success ms-1">{{ $counts['resolved'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') === 'closed' ? 'active' : '' }}"
           href="{{ route('frontend.support.index', ['status' => 'closed']) }}">
            <i class="bi bi-archive me-1"></i> Closed
            <span class="badge bg-secondary ms-1">{{ $counts['closed'] }}</span>
        </a>
    </li>
</ul>

{{-- ── Ticket List ──────────────────────────────────────────── --}}
@if ($tickets->count() > 0)
    <div class="card" style="border-radius: 10px; overflow: hidden;">
        @foreach ($tickets as $ticket)
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); transition: background 0.15s;"
                 onmouseover="this.style.background='#f8f9ff'"
                 onmouseout="this.style.background=''">

                <div class="d-flex justify-content-between align-items-start gap-3" style="flex-wrap: wrap;">

                    {{-- Left: subject + meta --}}
                    <div style="flex: 1; min-width: 200px;">
                        <div class="d-flex align-items-center gap-2 mb-1" style="flex-wrap: wrap;">
                            {{-- Priority dot --}}
                            @if ($ticket->priority === 'high')
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #dc3545; flex-shrink: 0;"></span>
                            @elseif ($ticket->priority === 'medium')
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ffc107; flex-shrink: 0;"></span>
                            @else
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #adb5bd; flex-shrink: 0;"></span>
                            @endif

                            <a href="{{ route('frontend.support.show', $ticket->id) }}"
                               style="font-weight: 700; color: #1a1a2e; text-decoration: none; font-size: 0.95rem;">
                                {{ Str::limit($ticket->subject, 60) }}
                            </a>
                        </div>

                        <div class="d-flex gap-3 align-items-center" style="flex-wrap: wrap;">
                            <small class="text-muted">
                                <i class="bi bi-tag me-1"></i>{{ $ticket->getCategoryLabel() }}
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $ticket->time_display }}
                            </small>
                            @if ($ticket->latestReply)
                                <small class="text-muted">
                                    <i class="bi bi-chat me-1"></i>
                                    Last reply by {{ $ticket->latestReply->author->name }}
                                    · {{ $ticket->latestReply->time_display }}
                                </small>
                            @endif
                        </div>
                    </div>

                    {{-- Right: status badge + action --}}
                    <div class="d-flex align-items-center gap-2" style="flex-shrink: 0;">
                        <span class="badge bg-{{ $ticket->getStatusBadgeColor() }}">
                            {{ $ticket->getStatusLabel() }}
                        </span>
                        <a href="{{ route('frontend.support.show', $ticket->id) }}"
                           class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                    </div>
                </div>

                {{-- Resolved notice --}}
                @if ($ticket->isResolved())
                    <div class="mt-2" style="background: #d4edda; border-radius: 6px; padding: 8px 12px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="bi bi-check-circle-fill" style="color: #155724;"></i>
                        <small style="color: #155724; font-weight: 600;">
                            Resolved by {{ $ticket->resolver?->name ?? 'Admin' }} · Confirm resolution or reopen below
                        </small>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $tickets->links() }}
    </div>

@else
    {{-- Empty state --}}
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-ticket-perforated"
               style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 16px;"></i>
            <h5 style="color: #333; font-weight: 600; margin-bottom: 8px;">
                @if (request('status'))
                    No {{ ucfirst(str_replace('_', ' ', request('status'))) }} tickets
                @else
                    No support tickets yet
                @endif
            </h5>
            <p class="text-muted mb-4">
                @if (request('status'))
                    Try a different status tab or raise a new ticket.
                @else
                    If you run into any issue, raise a ticket and your University Admin will help.
                @endif
            </p>
            <a href="{{ route('frontend.support.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Raise a Ticket
            </a>
        </div>
    </div>
@endif

@endsection