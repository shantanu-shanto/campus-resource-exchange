{{--
    resources/views/frontend/items/partials/_recommendation-section.blade.php

    Usage:
        @include('frontend.items.partials._recommendation-section', [
            'items' => $similarItems,
            'title' => 'Similar Items',
            'icon'  => 'bi-grid',
        ])

    Renders nothing if $items is empty — safe to include unconditionally.
    Card markup matches the item cards used on the homepage grid.
--}}
@if(isset($items) && $items->count())
<div style="margin-top: 32px;">
    <h5 style="font-weight: 700; color: #1a1a2e; margin-bottom: 16px;">
        <i class="bi {{ $icon ?? 'bi-stars' }} me-2" style="color: #0d6efd;"></i>{{ $title ?? 'Recommended Items' }}
    </h5>

    <div class="row">
        @foreach ($items as $recItem)
            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100" style="transition: transform 0.15s, box-shadow 0.15s; cursor: pointer;"
                    onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.12)'"
                    onmouseout="this.style.transform='';this.style.boxShadow=''">

                    {{-- Item Image --}}
                    <div style="height: 180px; background: #f0f4ff; border-radius: 8px 8px 0 0; overflow: hidden; position: relative;">
                        @if ($recItem->image_path)
                            <img
                                src="{{ asset('storage/' . $recItem->image_path) }}"
                                alt="{{ $recItem->title }}"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            >
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-image" style="font-size: 3rem; color: #0d6efd; opacity: 0.2;"></i>
                            </div>
                        @endif

                        {{-- Mode badge --}}
                        <div style="position: absolute; top: 10px; left: 10px;">
                            @if ($recItem->availability_mode === 'lend')
                                <span class="badge" style="background: #0d6efd;">
                                    <i class="bi bi-arrow-left-right me-1"></i>Borrow
                                </span>
                            @elseif ($recItem->availability_mode === 'sell')
                                <span class="badge" style="background: #28a745;">
                                    <i class="bi bi-tag me-1"></i>Buy
                                </span>
                            @elseif ($recItem->availability_mode === 'share')
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
                            {{ Str::limit($recItem->title, 40) }}
                        </h6>

                        {{-- Category --}}
                        <small class="text-muted mb-2">
                            <i class="bi bi-tag me-1"></i>{{ ucfirst(str_replace('_', ' ', $recItem->category)) }}
                        </small>

                        {{-- Price / Duration --}}
                        <div class="mb-3" style="min-height: 24px;">
                            @if ($recItem->availability_mode === 'sell' || $recItem->availability_mode === 'both')
                                <span style="color: #28a745; font-weight: 700; font-size: 0.95rem;">
                                    ৳{{ number_format($recItem->price, 0) }}
                                </span>
                            @endif
                            @if ($recItem->availability_mode === 'lend' || $recItem->availability_mode === 'both')
                                <span class="text-muted" style="font-size: 0.82rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $recItem->lending_duration_days }}d max
                                </span>
                            @endif
                            @if ($recItem->availability_mode === 'share')
                                <span style="color: #6f42c1; font-size: 0.85rem; font-weight: 600;">Free to borrow</span>
                            @endif
                        </div>

                        {{-- Stats row --}}
                        <div class="d-flex gap-3 mb-3" style="font-size: 0.8rem; color: #888;">
                            <span>
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                {{ $recItem->ratings_count > 0 ? number_format($recItem->ratings_avg_rating, 1) : '—' }}
                            </span>
                            <span>
                                <i class="bi bi-arrow-repeat me-1"></i>{{ $recItem->transactions_count }} times
                            </span>
                        </div>

                        {{-- Action --}}
                        <div class="mt-auto">
                            <a href="{{ route('frontend.items.show', $recItem) }}" class="btn btn-primary btn-sm w-100">
                                View Item
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif