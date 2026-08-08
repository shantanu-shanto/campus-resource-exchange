@extends('layouts.app')

@section('title', 'Campus Marketplace - UniShare')

@section('content')

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div style="margin-bottom: 32px;">
    <h1 class="page-title">Campus Marketplace</h1>
    <p class="text-muted">Browse items available at {{ auth()->user()->university->name }}</p>
</div>

{{-- ── Alerts ───────────────────────────────────────────────── --}}
@if ($userStats['pending_penalties'])
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>You have pending penalties.</strong> Pay them before requesting new items.
        <a href="{{ route('frontend.transactions.index') }}" class="alert-link ms-2">View penalties →</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Quick Stats Row ──────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-grid" style="font-size: 1.8rem; color: #0d6efd; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #0d6efd; font-weight: 700; margin-bottom: 2px;">{{ $items->total() }}</h5>
                <small class="text-muted">Items Available</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-arrow-left-right" style="font-size: 1.8rem; color: #28a745; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #28a745; font-weight: 700; margin-bottom: 2px;">{{ $userStats['active_borrows'] }}</h5>
                <small class="text-muted">My Active Borrows</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-tag" style="font-size: 1.8rem; color: #6f42c1; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #6f42c1; font-weight: 700; margin-bottom: 2px;">{{ $userStats['my_listings'] }}</h5>
                <small class="text-muted">My Listings</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body py-3">
                <i class="bi bi-chat-dots" style="font-size: 1.8rem; color: #fd7e14; display: block; margin-bottom: 8px;"></i>
                <h5 style="color: #fd7e14; font-weight: 700; margin-bottom: 2px;">{{ $userStats['unread_messages'] }}</h5>
                <small class="text-muted">Unread Messages</small>
            </div>
        </div>
    </div>
</div>

{{-- ── Recommended For You ─────────────────────────────────── --}}
@include('frontend.items.partials._recommendation-section', [
    'items' => $recommendedItems,
    'title' => 'Recommended For You',
    'icon'  => 'bi-stars',
])

{{-- ── Search & Filter Bar ──────────────────────────────────── --}}
<div class="card mb-4" style="margin-top: 32px;">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('home') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input
                        type="text"
                        name="search"
                        class="form-control border-start-0"
                        placeholder="Search items..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>
            <div class="col-md-3">
                <select name="mode" class="form-select">
                    <option value="">All Types</option>
                    <option value="lend" {{ request('mode') === 'lend' ? 'selected' : '' }}>For Borrowing</option>
                    <option value="sell" {{ request('mode') === 'sell' ? 'selected' : '' }}>For Sale</option>
                    <option value="share" {{ request('mode') === 'share' ? 'selected' : '' }}>Free / Share</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request('search') || request('mode'))
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Items Grid ───────────────────────────────────────────── --}}
@if ($items->count() > 0)
    <div class="row">
        @foreach ($items as $item)
            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100" style="transition: transform 0.15s, box-shadow 0.15s; cursor: pointer;"
                    onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.12)'"
                    onmouseout="this.style.transform='';this.style.boxShadow=''">

                    {{-- Item Image --}}
                    <div style="height: 180px; background: #f0f4ff; border-radius: 8px 8px 0 0; overflow: hidden; position: relative;">
                        @if ($item->image_path)
                            <img
                                src="{{ asset('storage/' . $item->image_path) }}"
                                alt="{{ $item->title }}"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            >
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-image" style="font-size: 3rem; color: #0d6efd; opacity: 0.2;"></i>
                            </div>
                        @endif

                        {{-- Mode badge --}}
                        <div style="position: absolute; top: 10px; left: 10px;">
                            @if ($item->availability_mode === 'lend')
                                <span class="badge" style="background: #0d6efd;">
                                    <i class="bi bi-arrow-left-right me-1"></i>Borrow
                                </span>
                            @elseif ($item->availability_mode === 'sell')
                                <span class="badge" style="background: #28a745;">
                                    <i class="bi bi-tag me-1"></i>Buy
                                </span>
                            @elseif ($item->availability_mode === 'share')
                                <span class="badge" style="background: #6f42c1;">
                                    <i class="bi bi-heart me-1"></i>Free
                                </span>
                            @else
                                <span class="badge" style="background: #fd7e14;">
                                    <i class="bi bi-collection me-1"></i>Both
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column" style="padding: 16px;">

                        {{-- Title --}}
                        <h6 style="font-weight: 700; color: #1a1a2e; margin-bottom: 4px; line-height: 1.3;">
                            {{ Str::limit($item->title, 40) }}
                        </h6>

                        {{-- Owner --}}
                        <small class="text-muted mb-2">
                            <i class="bi bi-person me-1"></i>{{ $item->owner->name }}
                        </small>

                        {{-- Price / Duration --}}
                        <div class="mb-3" style="min-height: 24px;">
                            @if ($item->availability_mode === 'sell' || $item->availability_mode === 'both')
                                <span style="color: #28a745; font-weight: 700; font-size: 0.95rem;">
                                    ৳{{ number_format($item->price, 0) }}
                                </span>
                            @endif
                            @if ($item->availability_mode === 'lend' || $item->availability_mode === 'both')
                                <span class="text-muted" style="font-size: 0.82rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $item->lending_duration_days }}d max
                                </span>
                            @endif
                            @if ($item->availability_mode === 'share')
                                <span style="color: #6f42c1; font-size: 0.85rem; font-weight: 600;">Free to borrow</span>
                            @endif
                        </div>

                        {{-- Stats row --}}
                        <div class="d-flex gap-3 mb-3" style="font-size: 0.8rem; color: #888;">
                            <span>
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                {{ $item->ratings_count > 0 ? number_format($item->ratings->avg('rating'), 1) : '—' }}
                            </span>
                            <span>
                                <i class="bi bi-arrow-repeat me-1"></i>{{ $item->transactions_count }} times
                            </span>
                            <span>
                                <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($item->pickup_location, 12) }}
                            </span>
                        </div>

                        {{-- Action --}}
                        <div class="mt-auto">
                            <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-primary btn-sm w-100">
                                View Item
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-2 mb-4">
        {{ $items->links() }}
    </div>

@else
    {{-- Empty state --}}
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 16px;"></i>
            <h5 style="color: #333; font-weight: 600; margin-bottom: 8px;">
                @if (request('search') || request('mode'))
                    No items match your filters
                @else
                    No items available yet
                @endif
            </h5>
            <p class="text-muted mb-4">
                @if (request('search') || request('mode'))
                    Try adjusting your search or removing filters.
                @else
                    Be the first to list something on your campus!
                @endif
            </p>
            <div class="d-flex justify-content-center gap-2">
                @if (request('search') || request('mode'))
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </a>
                @endif
                <a href="{{ route('frontend.items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> List an Item
                </a>
            </div>
        </div>
    </div>
@endif

{{-- ── Quick Actions FAB ────────────────────────────────────── --}}
<div style="position: fixed; bottom: 28px; right: 28px; display: flex; flex-direction: column; gap: 10px; align-items: flex-end; z-index: 999;">
    @if ($userStats['unread_messages'] > 0)
        <a href="{{ route('frontend.messages.index') }}"
           style="background: #fd7e14; color: #fff; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); text-decoration: none; position: relative;"
           title="Messages">
            <i class="bi bi-chat-dots" style="font-size: 1.2rem;"></i>
            <span style="position: absolute; top: -4px; right: -4px; background: #dc3545; color: #fff; font-size: 0.65rem; font-weight: 700; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                {{ $userStats['unread_messages'] > 9 ? '9+' : $userStats['unread_messages'] }}
            </span>
        </a>
    @endif
    <a href="{{ route('frontend.items.create') }}"
       style="background: #0d6efd; color: #fff; height: 52px; padding: 0 20px; border-radius: 26px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(13,110,253,0.4); text-decoration: none; font-weight: 600; font-size: 0.9rem;"
       title="List an item">
        <i class="bi bi-plus-lg"></i> List Item
    </a>
</div>

@endsection