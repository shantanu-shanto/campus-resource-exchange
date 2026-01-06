@extends('layouts.app')

@section('title', 'Browse Items - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">Browse Items</h1>
    <p class="text-muted">Discover resources available on campus</p>
</div>

<!-- Filters Section -->
<div class="row mb-4">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filters
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search items..." 
                            value="{{ request('search') }}">
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                            <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                    </div>

                    <!-- Mode -->
                    <div class="mb-3">
                        <label class="form-label">Availability Mode</label>
                        <select name="mode" class="form-select">
                            <option value="">All Types</option>
                            <option value="lend" {{ request('mode') === 'lend' ? 'selected' : '' }}>Lend Only</option>
                            <option value="sell" {{ request('mode') === 'sell' ? 'selected' : '' }}>Sell Only</option>
                            <option value="both" {{ request('mode') === 'both' ? 'selected' : '' }}>Lend & Sell</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label">Max Price</label>
                        <input type="number" name="max_price" class="form-control" placeholder="Enter max price"
                            value="{{ request('max_price') }}">
                    </div>

                    <!-- Sort -->
                    <div class="mb-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Newest First</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Apply Filters
                    </button>
                    <a href="{{ route('frontend.items.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                        Clear Filters
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="col-lg-9">
        @if ($items->count() > 0)
            <div class="row">
                @foreach ($items as $item)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <!-- Item Image -->
                            <div style="height: 200px; background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #dee2e6;">
                                @if ($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" 
                                        style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                @else
                                    <i class="bi bi-image" style="font-size: 4rem; color: #0d6efd; opacity: 0.3;"></i>
                                @endif
                            </div>

                            <div class="card-body">
                                <!-- Status Badge -->
                                <div class="mb-2">
                                    @if ($item->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @elseif ($item->status === 'borrowed')
                                        <span class="badge bg-warning">Borrowed</span>
                                    @else
                                        <span class="badge bg-secondary">Sold</span>
                                    @endif

                                    <!-- Mode Badge -->
                                    @if ($item->availability_mode === 'lend')
                                        <span class="badge bg-info">Lend</span>
                                    @elseif ($item->availability_mode === 'sell')
                                        <span class="badge bg-danger">Sell</span>
                                    @else
                                        <span class="badge bg-primary">Lend & Sell</span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h5 class="card-title" style="color: #333; font-weight: 600;">
                                    {{ Str::limit($item->title, 30) }}
                                </h5>

                                <!-- Description -->
                                <p class="card-text text-muted small">
                                    {{ Str::limit($item->description, 60) }}
                                </p>

                                <!-- Owner Info -->
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-person-circle" style="font-size: 1.5rem; color: #0d6efd; margin-right: 8px;"></i>
                                    <div>
                                        <small style="color: #333; font-weight: 600;">
                                            {{ $item->owner->name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                            {{ round($item->owner->averageRating(), 1) }} ({{ $item->owner->ratingsReceived->count() }})
                                        </small>
                                    </div>
                                </div>

                                <!-- Price (if selling) -->
                                @if ($item->price)
                                    <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                                        <small class="text-muted">Price</small>
                                        <h6 style="color: #0d6efd; font-weight: 700; margin: 0;">
                                            ৳{{ $item->price }}
                                        </h6>
                                    </div>
                                @endif

                                <!-- View Button -->
                                <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-primary w-100 btn-sm">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($items->hasPages())
                <div style="margin-top: 40px;">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <!-- No Items Found -->
            <div style="text-align: center; padding: 60px 20px;">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 20px;"></i>
                <h4 style="color: #666; font-weight: 600;">No items found</h4>
                <p class="text-muted mb-4">Try adjusting your filters or search terms</p>
                <a href="{{ route('frontend.items.index') }}" class="btn btn-primary">
                    Clear Filters
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
