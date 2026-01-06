@extends('admin.layouts.app')

@section('page-title', 'Item Moderation')

@section('content')

<!-- Search & Filter -->
<div class="admin-search">
    <form method="GET" action="{{ route('admin.items.index') }}" style="display: flex; gap: 10px; flex: 1;">
        <input type="text" name="search" class="form-control" placeholder="Search by title or owner..."
            value="{{ request('search') }}">
        <select name="status" class="form-select" style="max-width: 150px;">
            <option value="">All Status</option>
            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
            <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
        </select>
        <button type="submit" class="btn btn-admin btn-admin-primary">
            <i class="bi bi-search"></i> Search
        </button>
    </form>
</div>

<!-- Items Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="bi bi-bag"></i> Items ({{ $items->total() }})</h3>
    </div>
    <div class="admin-card-body">
        @if ($items->count() > 0)
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Owner</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td><strong>{{ Str::limit($item->title, 25) }}</strong></td>
                                <td><small>{{ $item->owner->name }}</small></td>
                                <td><small>{{ ucfirst(str_replace('-', ' ', $item->category)) }}</small></td>
                                <td>
                                    @if ($item->status === 'available')
                                        <span class="badge-status bg-success">Available</span>
                                    @else
                                        <span class="badge-status bg-warning">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td><small>{{ $item->created_at->format('M d, Y') }}</small></td>
                                <td>
                                    <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-sm btn-admin btn-admin-primary" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-admin btn-admin-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div style="margin-top: 20px;">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <p class="text-muted text-center mb-0">No items found</p>
        @endif
    </div>
</div>

<!-- Delete Modals -->
@foreach ($items as $item)
    <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-danger">
                    <h5 class="modal-title">Remove Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove this item?</p>
                    <p style="color: #666;"><strong>{{ $item->title }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.items.destroy', $item) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
