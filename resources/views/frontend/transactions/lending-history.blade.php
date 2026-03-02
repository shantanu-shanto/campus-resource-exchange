@extends('layouts.app')

@section('title', 'Lending History - Campus Resource Exchange')

@section('content')

<div class="container mt-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="bi bi-box-arrow-up"></i> Lending History</h2>
            <p class="text-muted">All items you have lent out and completed</p>
        </div>
        <a href="{{ route('frontend.transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> All Transactions
        </a>
    </div>

    @if ($history->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Borrowed By</th>
                        <th>Type</th>
                        <th>Returned On</th>
                        <th>Your Rating</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $transaction)
                        @php
                            $myRating = $transaction->ratings->where('rater_id', auth()->id())->first();
                        @endphp
                        <tr>
                            <td>{{ Str::limit($transaction->item->title, 30) }}</td>
                            <td>{{ $transaction->borrower->name }}</td>
                            <td>
                                <span class="badge {{ $transaction->type === 'lend' ? 'bg-info' : 'bg-success' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td>
                                {{ $transaction->return_date?->format('M d, Y') ?? $transaction->updated_at->format('M d, Y') }}
                            </td>
                            <td>
                                @if ($myRating)
                                    <span class="text-warning">
                                        @for ($i = 0; $i < $myRating->rating; $i++)
                                            ⭐
                                        @endfor
                                    </span>
                                @else
                                    <span class="text-muted small">Not rated</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('frontend.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if (!$myRating)
                                    <a href="{{ route('frontend.ratings.create', $transaction) }}" class="btn btn-sm btn-outline-warning ms-1">
                                        <i class="bi bi-star"></i> Rate
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $history->links() }}
        </div>
    @else
        <div class="alert alert-info text-center" role="alert">
            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
            <strong>No lending history yet</strong>
            <p class="text-muted mb-0 mt-1">Items you lend out and get returned will appear here.</p>
        </div>
    @endif
</div>

@endsection