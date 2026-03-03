{{-- resources/views/frontend/search/category.blade.php --}}
@extends('layouts.app')

@section('title', 'Category: ' . $category)

@section('content')
<div class="container py-4">
    {{-- Page Header --}}
    <div style="margin-bottom: 32px;">
        <h1 class="page-title">Category: {{ $category }}</h1>
        <p class="text-muted">Items related to "{{ $category }}"</p>
    </div>

    {{-- Items Grid --}}
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
                                <img src="{{ asset('storage/' . $item->image_path) }}"
                                     alt="{{ $item->title }}"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-image" style="font-size: 3rem; color: #0d6efd; opacity: 0.2;"></i>
                                </div>
                            @endif
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
            {{ $items->withQueryString()->links() }}
        </div>
    @else
        {{-- Empty state --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc; display: block; margin-bottom: 16px;"></i>
                <h5 style="color: #333; font-weight: 600; margin-bottom: 8px;">
                    No items found in this category
                </h5>
                <p class="text-muted mb-4">
                    Try a different category or browse all items.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('frontend.items.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-grid me-1"></i> Browse All Items
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection