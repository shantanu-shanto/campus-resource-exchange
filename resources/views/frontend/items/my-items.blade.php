@extends('layouts.app')
@section('title', 'My Items - Campus Resource Exchange')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-box2"></i> My Items</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('frontend.items.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Item
            </a>
        </div>
    </div>

    @if ($items->count() > 0)
        <div class="row">
            @foreach ($items as $item)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <!-- Item Header -->
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ Str::limit($item->title, 30) }}</h5>
                            <span class="badge bg-{{ $item->getStatusBadgeColor() }}">
                                {{ $item->getStatusLabel() }}
                            </span>
                        </div>

                        <!-- Item Body -->
                        <div class="card-body">
                            <p class="text-muted small">{{ Str::limit($item->description, 60) }}</p>
                            
                            <div class="mb-3">
                                <span class="badge bg-info">{{ $item->getAvailabilityModeLabel() }}</span>
                                @if ($item->requiresPrice())
                                    <span class="badge bg-success">{{ $item->formatted_price }}</span>
                                @endif
                            </div>

                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block">Transactions</small>
                                    <strong>{{ $item->transactions_count }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Avg Rating</small>
                                    <strong>{{ number_format($item->averageRating(), 1) }}/5</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Item Footer -->
                        <div class="card-footer bg-light">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('frontend.items.edit', $item) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('frontend.items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Active Borrower Info -->
                        @if ($item->activeTransaction)
                            <div class="card-footer bg-warning bg-opacity-10 border-top">
                                <small class="text-muted">Currently borrowed by:</small>
                                <p class="mb-0">
                                    <strong>{{ $item->activeTransaction->borrower->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Due: {{ $item->activeTransaction->due_date->format('M d, Y') }}
                                    </small>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-12">
                {{ $items->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info text-center" role="alert">
            <i class="bi bi-inbox"></i> <strong>No items yet</strong>
            <p>Start by <a href="{{ route('frontend.items.create') }}">creating your first item</a></p>
        </div>
    @endif
</div>
@endsection
