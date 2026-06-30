@extends('layouts.app')

@section('title', 'Ticket #{{ $ticket->id }} - UniShare')

@section('content')

{{-- ── Back + Header ────────────────────────────────────────── --}}
<div style="margin-bottom: 28px;">
    <a href="{{ route('frontend.support.index') }}"
       style="color: var(--primary-blue); text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        <i class="bi bi-arrow-left me-1"></i> Back to My Tickets
    </a>

    <div class="d-flex justify-content-between align-items-start mt-3" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title" style="font-size: 1.5rem; margin-bottom: 6px;">
                {{ $ticket->subject }}
            </h1>
            <div class="d-flex gap-3 align-items-center" style="flex-wrap: wrap;">
                <span class="badge bg-{{ ($ticket->getStatusBadgeColor)() }}">
                    {{ ($ticket->getStatusLabel)() }}
                </span>
                <span class="badge bg-{{ ($ticket->getPriorityBadgeColor)() }}">
                    <i class="bi bi-flag me-1"></i>{{ ($ticket->getPriorityLabel)() }} Priority
                </span>
                <small class="text-muted">
                    <i class="bi bi-tag me-1"></i>{{ ($ticket->getCategoryLabel)() }}
                </small>
                <small class="text-muted">
                    <i class="bi bi-hash me-1"></i>Ticket #{{ $ticket->id }}
                </small>
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>Opened {{ $ticket->time_display }}
                </small>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2" style="flex-shrink: 0;">
            @if (($ticket->isResolved)())
                {{-- User can close or reopen --}}
                <form method="POST" action="{{ route('frontend.support.close', $ticket->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Mark Closed
                    </button>
                </form>
                <form method="POST" action="{{ route('frontend.support.reopen', $ticket->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reopen
                    </button>
                </form>
            @elseif (($ticket->isClosed)())
                <span style="color: #6c757d; font-size: 0.85rem; padding: 6px 0;">
                    <i class="bi bi-lock me-1"></i> This ticket is closed
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Thread Column ───────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Resolved banner --}}
        @if (($ticket->isResolved)())
            <div class="alert alert-success mb-4" style="border-left: 4px solid #28a745;">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Resolved</strong> by {{ $ticket->resolver?->name ?? 'Admin' }}
                @if ($ticket->resolved_at)
                    · {{ $ticket->resolved_at->diffForHumans() }}
                @endif
                <br>
                <small>If your issue is fixed, click <strong>Mark Closed</strong>. If not, click <strong>Reopen</strong>.</small>
            </div>
        @endif

        {{-- ── Original ticket message ─────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-person-circle me-2"></i>
                    <strong>{{ $ticket->user->name }}</strong>
                    <small class="text-muted ms-2">· You</small>
                </span>
                <small class="text-muted">{{ $ticket->created_at->format('M d, Y · h:i A') }}</small>
            </div>
            <div class="card-body" style="padding: 20px; line-height: 1.7; color: #333;">
                {!! nl2br(e($ticket->description)) !!}
            </div>
        </div>

        {{-- ── Reply thread ─────────────────────────────────── --}}
        @foreach ($ticket->replies as $reply)
            @php $isAdmin = ($reply->isFromAdmin)(); @endphp

            <div class="card mb-3"
                 style="{{ $isAdmin ? 'border-left: 3px solid var(--primary-blue);' : 'border-left: 3px solid #dee2e6;' }}">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="{{ $isAdmin ? 'background: var(--light-blue);' : '' }}">
                    <span>
                        <i class="bi {{ $isAdmin ? 'bi-shield-check' : 'bi-person-circle' }} me-2"
                           style="{{ $isAdmin ? 'color: var(--primary-blue);' : '' }}"></i>
                        <strong>{{ $reply->author->name }}</strong>
                        @if ($isAdmin)
                            <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">University Admin</span>
                        @endif
                    </span>
                    <small class="text-muted">{{ $reply->created_at->format('M d, Y · h:i A') }}</small>
                </div>
                <div class="card-body" style="padding: 20px; line-height: 1.7; color: #333;">
                    {!! nl2br(e($reply->message)) !!}
                </div>
            </div>
        @endforeach

        {{-- ── Reply form ───────────────────────────────────── --}}
        @if (!($ticket->isClosed)())
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-reply me-2"></i> Add a Reply
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form method="POST" action="{{ route('frontend.support.reply', $ticket->id) }}">
                        @csrf
                        <div class="mb-3">
                            <textarea
                                name="message"
                                class="form-control @error('message') is-invalid @enderror"
                                rows="4"
                                placeholder="Add more details or follow up on the admin's response..."
                                maxlength="2000"
                                required
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                @if (($ticket->isResolved)())
                                    <i class="bi bi-info-circle me-1"></i>
                                    Replying will reopen this ticket.
                                @endif
                            </small>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card mt-4" style="background: #f8f9fa;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-lock" style="font-size: 2rem; color: #adb5bd; display: block; margin-bottom: 10px;"></i>
                    <p class="text-muted mb-0">This ticket is closed. Raise a new ticket if the issue persists.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <div class="col-lg-4 mt-4 mt-lg-0">

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
                            <span class="badge bg-{{ ($ticket->getStatusBadgeColor)() }}">
                                {{ ($ticket->getStatusLabel)() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Priority</td>
                        <td>
                            <span class="badge bg-{{ ($ticket->getPriorityBadgeColor)() }}">
                                {{ ($ticket->getPriorityLabel)() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Category</td>
                        <td style="color: #333; font-weight: 600;">{{ ($ticket->getCategoryLabel)() }}</td>
                    </tr>
                    <tr>
                        <td style="color: #888;">Opened</td>
                        <td style="color: #333;">{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                    @if ($ticket->resolved_at)
                        <tr>
                            <td style="color: #888;">Resolved</td>
                            <td style="color: #333;">{{ $ticket->resolved_at->format('M d, Y') }}</td>
                        </tr>
                    @endif
                    @if ($ticket->closed_at)
                        <tr>
                            <td style="color: #888;">Closed</td>
                            <td style="color: #333;">{{ $ticket->closed_at->format('M d, Y') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="color: #888;">Replies</td>
                        <td style="color: #333; font-weight: 600;">{{ $ticket->replies->count() }}</td>
                    </tr>
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
                        {{ Str::limit($ticket->transaction->item->title ?? 'Item', 40) }}
                    </p>
                    <small class="text-muted d-block mb-1">
                        Type: {{ ucfirst($ticket->transaction->type) }}
                    </small>
                    <small class="text-muted d-block mb-3">
                        Status:
                        <span class="badge bg-{{ ($ticket->transaction->getStatusBadgeColor)() }}">
                            {{ ($ticket->transaction->getStatusLabel)() }}
                        </span>
                    </small>
                    <a href="{{ route('frontend.transactions.show', $ticket->transaction->id) }}"
                       class="btn btn-sm btn-outline-primary w-100">
                        View Transaction
                    </a>
                </div>
            </div>
        @endif

        {{-- Help note --}}
        <div class="card" style="background: var(--light-blue); border-color: #b8d4ff;">
            <div class="card-body" style="padding: 16px;">
                <p style="font-size: 0.82rem; color: #0d4fa0; margin: 0; line-height: 1.6;">
                    <i class="bi bi-shield-check me-1"></i>
                    Your ticket is only visible to you and your University Admin.
                    Responses typically arrive within 1–2 business days.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection