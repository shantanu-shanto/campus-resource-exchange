@extends('layouts.app')

@section('title', 'Ticket #{{ $ticket->id }} - UniShare Admin')

@section('content')

{{-- ── Back + Header ────────────────────────────────────────── --}}
<div style="margin-bottom: 28px;">
    <a href="{{ route('uni-admin.support.index') }}"
       style="color: var(--primary-blue); text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        <i class="bi bi-arrow-left me-1"></i> Back to All Tickets
    </a>

    <div class="d-flex justify-content-between align-items-start mt-3" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title" style="font-size: 1.5rem; margin-bottom: 6px;">
                {{ $ticket->subject }}
            </h1>
            <div class="d-flex gap-3 align-items-center" style="flex-wrap: wrap;">
                <span class="badge bg-{{ $ticket->getStatusBadgeColor() }}">
                    {{ $ticket->getStatusLabel() }}
                </span>
                <span class="badge bg-{{ $ticket->getPriorityBadgeColor() }}">
                    <i class="bi bi-flag me-1"></i>{{ $ticket->getPriorityLabel() }} Priority
                </span>
                <small class="text-muted">
                    <i class="bi bi-tag me-1"></i>{{ $ticket->getCategoryLabel() }}
                </small>
                <small class="text-muted">
                    <i class="bi bi-hash me-1"></i>Ticket #{{ $ticket->id }}
                </small>
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>Opened {{ $ticket->time_display }}
                </small>
            </div>
        </div>

        {{-- Admin actions --}}
        <div class="d-flex gap-2" style="flex-shrink: 0;">
            @if ($ticket->isActive())
                <form method="POST" action="{{ route('uni-admin.support.resolve', $ticket) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i> Mark Resolved
                    </button>
                </form>
            @endif

            @if (!$ticket->isClosed())
                <form method="POST" action="{{ route('uni-admin.support.close', $ticket) }}"
                      onsubmit="return confirm('Force-close this ticket?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Force Close
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Thread Column ───────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Closed banner --}}
        @if ($ticket->isClosed())
            <div class="alert alert-secondary mb-4">
                <i class="bi bi-archive me-2"></i>
                <strong>Ticket closed</strong>
                @if ($ticket->closed_at)
                    · {{ $ticket->closed_at->diffForHumans() }}
                @endif
            </div>
        @endif

        {{-- ── Original message ─────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-person-circle me-2"></i>
                    <strong>{{ $ticket->user->name }}</strong>
                    <small class="text-muted ms-2">· Student / Teacher</small>
                </span>
                <small class="text-muted">{{ $ticket->created_at->format('M d, Y · h:i A') }}</small>
            </div>
            <div class="card-body" style="padding: 20px; line-height: 1.7; color: #333;">
                {!! nl2br(e($ticket->description)) !!}
            </div>
        </div>

        {{-- ── Reply thread ─────────────────────────────────── --}}
        @foreach ($ticket->replies as $reply)
            @php $isAdmin = $reply->isFromAdmin(); @endphp

            <div class="card mb-3"
                 style="{{ $isAdmin ? 'border-left: 3px solid var(--primary-blue);' : 'border-left: 3px solid #dee2e6;' }}">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="{{ $isAdmin ? 'background: var(--light-blue);' : '' }}">
                    <span>
                        <i class="bi {{ $isAdmin ? 'bi-shield-check' : 'bi-person-circle' }} me-2"
                           style="{{ $isAdmin ? 'color: var(--primary-blue);' : '' }}"></i>
                        <strong>{{ $reply->author->name }}</strong>
                        @if ($isAdmin)
                            <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">You (Admin)</span>
                        @else
                            <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">Student</span>
                        @endif
                    </span>
                    <small class="text-muted">{{ $reply->created_at->format('M d, Y · h:i A') }}</small>
                </div>
                <div class="card-body" style="padding: 20px; line-height: 1.7; color: #333;">
                    {!! nl2br(e($reply->message)) !!}
                </div>
            </div>
        @endforeach

        {{-- ── Admin reply form ─────────────────────────────── --}}
        @if (!$ticket->isClosed())
            <div class="card mt-4">
                <div class="card-header" style="background: var(--light-blue);">
                    <i class="bi bi-shield-check me-2" style="color: var(--primary-blue);"></i>
                    <strong>Reply as University Admin</strong>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form method="POST" action="{{ route('uni-admin.support.reply', $ticket) }}">
                        @csrf
                        <div class="mb-3">
                            <textarea
                                name="message"
                                class="form-control @error('message') is-invalid @enderror"
                                rows="5"
                                placeholder="Write your response to the student..."
                                maxlength="2000"
                                required
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                @if ($ticket->isOpen())
                                    Sending a reply will move this ticket to <strong>In Progress</strong>.
                                @endif
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> Send Reply
                            </button>

                            {{-- Quick resolve: reply + resolve in one action --}}
                            @if ($ticket->isActive())
                                <button type="submit" name="resolve_after" value="1"
                                        class="btn btn-outline-success">
                                    <i class="bi bi-check-circle me-1"></i> Reply & Resolve
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card mt-4" style="background: #f8f9fa;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-lock" style="font-size: 2rem; color: #adb5bd; display: block; margin-bottom: 10px;"></i>
                    <p class="text-muted mb-0">This ticket is closed and locked.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <div class="col-lg-4 mt-4 mt-lg-0">

        {{-- Student info --}}
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-person me-2"></i> Raised By
            </div>
            <div class="card-body" style="padding: 18px;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                    <i class="bi bi-person-circle" style="font-size: 2.5rem; color: #0d6efd;"></i>
                    <div>
                        <p style="font-weight: 700; color: #333; margin: 0;">{{ $ticket->user->name }}</p>
                        <small class="text-muted">{{ $ticket->user->email }}</small>
                    </div>
                </div>
                <a href="{{ route('uni-admin.users.show', $ticket->user) }}"
                   class="btn btn-sm btn-outline-primary w-100">
                    View Profile
                </a>
            </div>
        </div>

        {{-- Ticket details --}}
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> Ticket Details
            </div>
            <div class="card-body" style="padding: 18px;">
                <table style="width: 100%; font-size: 0.875rem; border-collapse: separate; border-spacing: 0 8px;">
                    <tr>
                        <td style="color: #888; width: 40%;">Status</td>
                        <td>
                            <span class="badge bg-{{ $ticket->getStatusBadgeColor() }}">
                                {{ $ticket->getStatusLabel() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Priority</td>
                        <td>
                            <span class="badge bg-{{ $ticket->getPriorityBadgeColor() }}">
                                {{ $ticket->getPriorityLabel() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Category</td>
                        <td style="color: #333; font-weight: 600;">{{ $ticket->getCategoryLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Opened</td>
                        <td style="color: #333;">{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Replies</td>
                        <td style="color: #333; font-weight: 600;">{{ $ticket->replies->count() }}</td>
                    </tr>
                    @if ($ticket->resolver)
                        <tr>
                            <td style="color: #888;">Resolved by</td>
                            <td style="color: #333; font-weight: 600;">{{ $ticket->resolver->name }}</td>
                        </tr>
                    @endif
                    @if ($ticket->resolved_at)
                        <tr>
                            <td style="color: #888;">Resolved on</td>
                            <td style="color: #333;">{{ $ticket->resolved_at->format('M d, Y') }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Linked transaction --}}
        @if ($ticket->transaction)
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-arrow-left-right me-2"></i> Linked Transaction
                </div>
                <div class="card-body" style="padding: 18px;">
                    <p style="font-weight: 600; margin-bottom: 4px; color: #333;">
                        {{ Str::limit($ticket->transaction->item->title ?? 'Item', 35) }}
                    </p>
                    <small class="text-muted d-block mb-1">
                        Owner: {{ $ticket->transaction->owner->name ?? '—' }}
                    </small>
                    <small class="text-muted d-block mb-1">
                        Borrower: {{ $ticket->transaction->borrower->name ?? '—' }}
                    </small>
                    <small class="text-muted d-block mb-3">
                        Status:
                        <span class="badge bg-{{ $ticket->transaction->getStatusBadgeColor() }}">
                            {{ $ticket->transaction->getStatusLabel() }}
                        </span>
                    </small>
                    <a href="{{ route('uni-admin.transactions.show', $ticket->transaction) }}"
                       class="btn btn-sm btn-outline-primary w-100">
                        View Transaction
                    </a>
                </div>
            </div>
        @endif

        {{-- Linked item --}}
        @if ($ticket->item)
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-box me-2"></i> Linked Item
                </div>
                <div class="card-body" style="padding: 18px;">
                    <p style="font-weight: 600; margin-bottom: 8px; color: #333;">
                        {{ Str::limit($ticket->item->title, 40) }}
                    </p>
                    <a href="{{ route('uni-admin.items.show', $ticket->item) }}"
                       class="btn btn-sm btn-outline-primary w-100">
                        View Item
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection