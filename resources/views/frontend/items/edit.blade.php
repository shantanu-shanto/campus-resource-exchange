@extends('layouts.app')

@section('title', 'Edit Item - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">Edit Item</h1>
    <p class="text-muted">Update your item details</p>
</div>

<!-- Form Container -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <form method="POST" action="{{ route('frontend.items.update', $item) }}" enctype="multipart/form-data" id="itemForm">
            @csrf
            @method('PATCH')

            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Basic Information
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label">Item Title *</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $item->title) }}" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                            rows="5" required>{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="textbooks" {{ old('category', $item->category) === 'textbooks' ? 'selected' : '' }}>Textbooks</option>
                            <option value="notes" {{ old('category', $item->category) === 'notes' ? 'selected' : '' }}>Study Notes</option>
                            <option value="lab-equipment" {{ old('category', $item->category) === 'lab-equipment' ? 'selected' : '' }}>Lab Equipment</option>
                            <option value="supplies" {{ old('category', $item->category) === 'supplies' ? 'selected' : '' }}>Supplies</option>
                            <option value="electronics" {{ old('category', $item->category) === 'electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="other" {{ old('category', $item->category) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Condition -->
                    <div class="mb-3">
                        <label class="form-label">Item Condition *</label>
                        <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                            <option value="new" {{ old('condition', $item->condition) === 'new' ? 'selected' : '' }}>New - Never used</option>
                            <option value="excellent" {{ old('condition', $item->condition) === 'excellent' ? 'selected' : '' }}>Excellent - Like new</option>
                            <option value="good" {{ old('condition', $item->condition) === 'good' ? 'selected' : '' }}>Good - Minor wear</option>
                            <option value="fair" {{ old('condition', $item->condition) === 'fair' ? 'selected' : '' }}>Fair - Visible wear</option>
                            <option value="poor" {{ old('condition', $item->condition) === 'poor' ? 'selected' : '' }}>Poor - Heavy damage</option>
                        </select>
                        @error('condition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Item Photo -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-image"></i> Item Photo
                </div>
                <div class="card-body">
                    <!-- Current Image -->
                    @if ($item->image_path)
                        <div class="mb-3">
                            <label class="form-label">Current Photo</label>
                            <div style="background: #f0f4ff; padding: 20px; border-radius: 8px; text-align: center;">
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}"
                                    style="max-width: 300px; max-height: 300px; border-radius: 8px;">
                            </div>
                        </div>
                    @endif

                    <!-- Upload New Image -->
                    <div class="mb-3">
                        <label class="form-label">Replace Photo (Optional)</label>
                        <div class="input-group mb-3">
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                id="imageInput" accept="image/*">
                            <label class="input-group-text" for="imageInput">
                                <i class="bi bi-cloud-upload"></i>
                            </label>
                            @error('image')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="text-muted">Leave blank to keep current image</small>
                    </div>

                    <!-- Image Preview -->
                    <div id="imagePreview" style="display: none; margin-top: 20px;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 300px; border-radius: 8px;">
                    </div>
                </div>
            </div>

            <!-- Availability & Pricing -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-tag"></i> Availability & Pricing
                </div>
                <div class="card-body">
                    <!-- Status (disabled if has active transactions) -->
                    @php
                        $hasActiveTransactions = $item->transactions->where('status', 'active')->count() > 0;
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">Item Status *</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror"
                            {{ $hasActiveTransactions ? 'disabled' : '' }}>
                            <option value="available" {{ old('status', $item->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="borrowed" {{ old('status', $item->status) === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                            <option value="sold" {{ old('status', $item->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                        @if ($hasActiveTransactions)
                            <small class="text-warning">Cannot change status while item has active transactions</small>
                        @endif
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Lending Duration -->
                    <div class="mb-3">
                        <label class="form-label">Maximum Lending Duration (days)</label>
                        <input type="number" name="lending_duration_days" class="form-control @error('lending_duration_days') is-invalid @enderror"
                            min="1" max="365" value="{{ old('lending_duration_days', $item->lending_duration_days) }}">
                        @error('lending_duration_days')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label class="form-label">Price (BDT)</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                min="0" step="10" value="{{ old('price', $item->price) }}">
                        </div>
                        @error('price')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pickup & Location -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt"></i> Pickup & Location
                </div>
                <div class="card-body">
                    <!-- Pickup Location -->
                    <div class="mb-3">
                        <label class="form-label">Pickup Location *</label>
                        <input type="text" name="pickup_location" class="form-control @error('pickup_location') is-invalid @enderror"
                            value="{{ old('pickup_location', $item->pickup_location) }}" required>
                        @error('pickup_location')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Preferred Meeting Times -->
                    <div class="mb-3">
                        <label class="form-label">Preferred Meeting Times</label>
                        <select name="meeting_times" class="form-select @error('meeting_times') is-invalid @enderror">
                            <option value="">Select preferred times</option>
                            <option value="morning" {{ old('meeting_times', $item->meeting_times) === 'morning' ? 'selected' : '' }}>Morning (6am - 12pm)</option>
                            <option value="afternoon" {{ old('meeting_times', $item->meeting_times) === 'afternoon' ? 'selected' : '' }}>Afternoon (12pm - 6pm)</option>
                            <option value="evening" {{ old('meeting_times', $item->meeting_times) === 'evening' ? 'selected' : '' }}>Evening (6pm - 10pm)</option>
                            <option value="flexible" {{ old('meeting_times', $item->meeting_times) === 'flexible' ? 'selected' : '' }}>Flexible</option>
                        </select>
                        @error('meeting_times')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clipboard-check"></i> Additional Information
                </div>
                <div class="card-body">
                    <!-- Author/Publisher -->
                    <div class="mb-3">
                        <label class="form-label">Author/Publisher</label>
                        <input type="text" name="author" class="form-control @error('author') is-invalid @enderror"
                            value="{{ old('author', $item->author) }}">
                        @error('author')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Edition/Year -->
                    <div class="mb-3">
                        <label class="form-label">Edition/Year</label>
                        <input type="text" name="edition" class="form-control @error('edition') is-invalid @enderror"
                            value="{{ old('edition', $item->edition) }}">
                        @error('edition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- ISBN -->
                    <div class="mb-3">
                        <label class="form-label">ISBN or Code</label>
                        <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror"
                            value="{{ old('isbn', $item->isbn) }}">
                        @error('isbn')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror"
                            value="{{ old('tags', $item->tags) }}" placeholder="Comma-separated">
                        @error('tags')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Item Stats -->
            <div class="card mb-4" style="background: #f8f9fa;">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <small class="text-muted">Views</small>
                            <h5 style="color: #0d6efd; font-weight: 700;">{{ $item->views ?? 0 }}</h5>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Transactions</small>
                            <h5 style="color: #0d6efd; font-weight: 700;">{{ $item->transactions->count() }}</h5>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Posted</small>
                            <h5 style="color: #0d6efd; font-weight: 700;">{{ $item->created_at->format('M d, Y') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
                <a href="{{ route('frontend.items.show', $item) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Image preview
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('previewImg').src = event.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
