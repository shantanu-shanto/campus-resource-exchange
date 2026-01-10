@extends('layouts.app')

@section('title', 'Search - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">Search Results</h1>
    @if (request('q'))
        <p class="text-muted">Results for "<strong>{{ request('q') }}</strong>"</p>
    @endif
</div>

<!-- Search Form -->
<div class="row mb-4">
    <div class="col-lg-8 mx-auto">
        <form method="GET" action="{{ route('frontend.search.index') }}">
            <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control" placeholder="Search items, users, or resources..."
                    value="{{ request('q') }}" autofocus>
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Search Links -->
<div class="row mb-5">
    <div class="col-lg-8 mx-auto">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('frontend.search.popular') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-fire"></i> Popular
            </a>
            <a href="{{ route('frontend.search.new') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock"></i> New
            </a>
            <a href="{{ route('frontend.search.category') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-tag"></i> Categories
            </a>
            <a href="{{ route('frontend.search.owner-rating') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-star"></i> Top Rated
            </a>
        </div>
    </div>
</div>

<!-- Search Results -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        @if ($items && $items->count() > 0)
            <div class="row">
                @foreach ($items as $item)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <!-- Item Image -->
                            <div style="height: 180px; background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #dee2e6;">
                                @if ($item['image_path'])
                                    <img src="{{ asset('storage/' . $item['image_path']) }}" alt="{{ $item['title'] }}"
                                        style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                @else
                                    <i class="bi bi-image" style="font-size: 3rem; color: #0d6efd; opacity: 0.3;"></i>
                                @endif
                            </div>

                            <div class="card-body">
                                <!-- Badges -->
                                <div class="mb-2">
                                    @if ($item['status'] === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @endif
                                    @if ($item['availability_mode'] === 'lend')
                                        <span class="badge bg-info">Lend</span>
                                    @elseif ($item['availability_mode'] === 'sell')
                                        <span class="badge bg-danger">Sell</span>
                                    @else
                                        <span class="badge bg-primary">Both</span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h5 class="card-title" style="color: #333; font-weight: 600;">
                                    {{ Str::limit($item['title'], 25) }}
                                </h5>

                                <!-- Owner -->
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-person"></i> {{ $item['owner']->name }}
                                </small>

                                <!-- Price -->
                                @if ($item['price'])
                                    <div style="color: #0d6efd; font-weight: 700; margin-bottom: 10px;">
                                        ৳{{ $item['price'] }}
                                    </div>
                                @endif

                                <!-- Ratings -->
                                <div class="mb-2">
                                    <small class="text-warning">
                                        <i class="bi bi-star-fill"></i> {{ $item['avg_rating'] }} 
                                        <span class="text-muted">({{ $item['total_ratings'] }} reviews)</span>
                                    </small>
                                </div>

                                <!-- View Button -->
                                <a href="{{ $item['url'] }}" class="btn btn-primary w-100 btn-sm">
                                    View Item
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($items->hasPages())
                <div style="margin-top: 40px;">
                    {{ $items->links() }}
                </div>
            @endif
        @elseif (request('q'))
            <!-- No Results -->
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h4 style="color: #666; font-weight: 600;">No results found</h4>
                <p class="text-muted mb-4">Try different search terms</p>
            </div>
        @else
            <!-- Empty State -->
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h4 style="color: #666; font-weight: 600;">Start searching</h4>
                <p class="text-muted mb-4">Find the resources you need</p>
            </div>
        @endif
    </div>
</div>

@endsection
