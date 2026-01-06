@extends('layouts.app')

@section('title', 'List a New Item - Campus Resource Exchange')

@section('content')

<!-- Page Header -->
<div style="margin-bottom: 40px;">
    <h1 class="page-title">List a New Item</h1>
    <p class="text-muted">Share your resources with the campus community</p>
</div>

<!-- Form Container -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <form method="POST" action="{{ route('frontend.items.store') }}" enctype="multipart/form-data" id="itemForm">
            @csrf

            <!-- Step 1: Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Step 1: Basic Information
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label">Item Title *</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            placeholder="e.g., Organic Chemistry Textbook, Physics Lab Manual"
                            value="{{ old('title') }}" required>
                        <small class="text-muted">Be specific and descriptive</small>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                            rows="5" placeholder="Describe the item condition, edition, author, etc..."
                            required>{{ old('description') }}</textarea>
                        <small class="text-muted">Provide details that help others decide. Max 500 characters.</small>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Select a category</option>
                            <option value="textbooks" {{ old('category') === 'textbooks' ? 'selected' : '' }}>Textbooks</option>
                            <option value="notes" {{ old('category') === 'notes' ? 'selected' : '' }}>Study Notes</option>
                            <option value="lab-equipment" {{ old('category') === 'lab-equipment' ? 'selected' : '' }}>Lab Equipment</option>
                            <option value="supplies" {{ old('category') === 'supplies' ? 'selected' : '' }}>Supplies</option>
                            <option value="electronics" {{ old('category') === 'electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Condition -->
                    <div class="mb-3">
                        <label class="form-label">Item Condition *</label>
                        <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                            <option value="">Select condition</option>
                            <option value="new" {{ old('condition') === 'new' ? 'selected' : '' }}>New - Never used</option>
                            <option value="excellent" {{ old('condition') === 'excellent' ? 'selected' : '' }}>Excellent - Like new</option>
                            <option value="good" {{ old('condition') === 'good' ? 'selected' : '' }}>Good - Minor wear</option>
                            <option value="fair" {{ old('condition') === 'fair' ? 'selected' : '' }}>Fair - Visible wear</option>
                            <option value="poor" {{ old('condition') === 'poor' ? 'selected' : '' }}>Poor - Heavy damage</option>
                        </select>
                        @error('condition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Step 2: Item Photo -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-image"></i> Step 2: Item Photo
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Upload Photo</label>
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
                        <small class="text-muted">JPG, PNG, GIF - Max 2MB. Clear, well-lit photos get more interest!</small>
                    </div>

                    <!-- Image Preview -->
                    <div id="imagePreview" style="display: none; margin-top: 20px;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 300px; border-radius: 8px;">
                    </div>
                </div>
            </div>

            <!-- Step 3: Availability & Pricing -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-tag"></i> Step 3: Availability & Pricing
                </div>
                <div class="card-body">
                    <!-- Availability Mode -->
                    <div class="mb-4">
                        <label class="form-label">How do you want to share this? *</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="availability_mode"
                                        value="lend" id="modeLend" {{ old('availability_mode') === 'lend' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="modeLend">
                                        <strong>Lend Only</strong>
                                        <br>
                                        <small class="text-muted">Others can borrow for a period</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="availability_mode"
                                        value="sell" id="modeSell" {{ old('availability_mode') === 'sell' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="modeSell">
                                        <strong>Sell Only</strong>
                                        <br>
                                        <small class="text-muted">Others can purchase</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="availability_mode"
                                        value="both" id="modeBoth" {{ old('availability_mode') === 'both' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="modeBoth">
                                        <strong>Lend & Sell</strong>
                                        <br>
                                        <small class="text-muted">Others can choose either option</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('availability_mode')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Lending Duration (shown if lend mode selected) -->
                    <div id="lendingSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Maximum Lending Duration (days) *</label>
                            <input type="number" name="lending_duration_days" class="form-control @error('lending_duration_days') is-invalid @enderror"
                                min="1" max="365" placeholder="e.g., 14, 30"
                                value="{{ old('lending_duration_days') }}">
                            <small class="text-muted">How long can others borrow this item?</small>
                            @error('lending_duration_days')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Price (shown if sell mode selected) -->
                    <div id="priceSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Price (BDT) *</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                    min="0" step="10" placeholder="0" value="{{ old('price') }}">
                            </div>
                            <small class="text-muted">Set a reasonable price for your item</small>
                            @error('price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Pickup & Location -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt"></i> Step 4: Pickup & Location
                </div>
                <div class="card-body">
                    <!-- Pickup Location -->
                    <div class="mb-3">
                        <label class="form-label">Pickup Location *</label>
                        <input type="text" name="pickup_location" class="form-control @error('pickup_location') is-invalid @enderror"
                            placeholder="e.g., Library, Science Building Room 204"
                            value="{{ old('pickup_location') }}" required>
                        <small class="text-muted">Where can others pick up or meet you?</small>
                        @error('pickup_location')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Preferred Meeting Times -->
                    <div class="mb-3">
                        <label class="form-label">Preferred Meeting Times (Optional)</label>
                        <select name="meeting_times" class="form-select @error('meeting_times') is-invalid @enderror">
                            <option value="">Select preferred times</option>
                            <option value="morning" {{ old('meeting_times') === 'morning' ? 'selected' : '' }}>Morning (6am - 12pm)</option>
                            <option value="afternoon" {{ old('meeting_times') === 'afternoon' ? 'selected' : '' }}>Afternoon (12pm - 6pm)</option>
                            <option value="evening" {{ old('meeting_times') === 'evening' ? 'selected' : '' }}>Evening (6pm - 10pm)</option>
                            <option value="flexible" {{ old('meeting_times') === 'flexible' ? 'selected' : '' }}>Flexible</option>
                        </select>
                        @error('meeting_times')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Step 5: Additional Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clipboard-check"></i> Step 5: Additional Information
                </div>
                <div class="card-body">
                    <!-- Author/Publisher (for books) -->
                    <div class="mb-3">
                        <label class="form-label">Author/Publisher (Optional)</label>
                        <input type="text" name="author" class="form-control @error('author') is-invalid @enderror"
                            placeholder="e.g., Richard Feynman, McGraw Hill"
                            value="{{ old('author') }}">
                        @error('author')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Edition/Year -->
                    <div class="mb-3">
                        <label class="form-label">Edition/Year (Optional)</label>
                        <input type="text" name="edition" class="form-control @error('edition') is-invalid @enderror"
                            placeholder="e.g., 3rd Edition, 2023"
                            value="{{ old('edition') }}">
                        @error('edition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- ISBN (for books) -->
                    <div class="mb-3">
                        <label class="form-label">ISBN or Code (Optional)</label>
                        <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror"
                            placeholder="e.g., 978-0-13-110362-7"
                            value="{{ old('isbn') }}">
                        @error('isbn')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div class="mb-3">
                        <label class="form-label">Tags (Optional)</label>
                        <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror"
                            placeholder="e.g., Physics, Chemistry, Engineering (comma-separated)"
                            value="{{ old('tags') }}">
                        <small class="text-muted">Help others find your item</small>
                        @error('tags')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                            name="terms" id="termsCheck" {{ old('terms') ? 'checked' : '' }} required>
                        <label class="form-check-label" for="termsCheck">
                            I agree to the <a href="#" target="_blank">Terms of Service</a> and
                            <a href="#" target="_blank">Community Guidelines</a>
                        </label>
                        @error('terms')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    <i class="bi bi-check-circle"></i> List Item
                </button>
                <a href="{{ route('frontend.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Show/hide sections based on availability mode
    function updateAvailabilityUI() {
        const mode = document.querySelector('input[name="availability_mode"]:checked')?.value;
        const lendSection = document.getElementById('lendingSection');
        const priceSection = document.getElementById('priceSection');

        // Hide both initially
        lendSection.style.display = 'none';
        priceSection.style.display = 'none';

        // Show appropriate sections
        if (mode === 'lend' || mode === 'both') {
            lendSection.style.display = 'block';
        }
        if (mode === 'sell' || mode === 'both') {
            priceSection.style.display = 'block';
        }
    }

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

    // Event listeners for availability mode
    document.querySelectorAll('input[name="availability_mode"]').forEach(radio => {
        radio.addEventListener('change', updateAvailabilityUI);
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', updateAvailabilityUI);

    // Form validation
    document.getElementById('itemForm').addEventListener('submit', function(e) {
        const mode = document.querySelector('input[name="availability_mode"]:checked');
        if (!mode) {
            e.preventDefault();
            alert('Please select an availability mode');
            return false;
        }
    });
</script>
@endsection
