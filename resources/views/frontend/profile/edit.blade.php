@extends('layouts.app')

@section('title', 'Edit Profile - Campus Resource Exchange')

@section('content')

<div style="margin-bottom: 40px;">
    <h1 class="page-title">Edit Your Profile</h1>
    <p class="text-muted">Update your information and preferences</p>
</div>

<!-- Profile Form Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
            <i class="bi bi-person"></i> Basic Info
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
            <i class="bi bi-lock"></i> Password
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
            <i class="bi bi-gear"></i> Preferences
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Basic Info Tab -->
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person"></i> Basic Information
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('frontend.profile.update') }}">
                            @csrf
                            @method('PATCH')

                            <!-- Name -->
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', auth()->user()->email) }}" required>
                                <small class="text-muted">Your college email address</small>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label class="form-label">Phone Number (Optional)</label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="+880...">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Bio -->
                            <div class="mb-3">
                                <label class="form-label">Bio (Optional)</label>
                                <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="4"
                                    placeholder="Tell other students about yourself...">{{ old('bio', auth()->user()->bio ?? '') }}</textarea>
                                <small class="text-muted">Max 200 characters</small>
                                @error('bio')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Account Info Sidebar -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Account Information
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Member Since</small>
                            <p style="color: #333; font-weight: 600; margin: 5px 0;">
                                {{ auth()->user()->created_at->format('F d, Y') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Email Verified</small>
                            <p style="color: #333; font-weight: 600; margin: 5px 0;">
                                @if (auth()->user()->email_verified_at)
                                    <i class="bi bi-check-circle" style="color: #28a745;"></i> Yes
                                @else
                                    <i class="bi bi-x-circle" style="color: #dc3545;"></i> No
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Account Status</small>
                            <p style="color: #333; font-weight: 600; margin: 5px 0;">
                                @if (auth()->user()->is_blocked ?? false)
                                    <span class="badge bg-danger">Blocked</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Your Statistics
                    </div>
                    <div class="card-body">
                        <div style="padding-bottom: 12px; border-bottom: 1px solid #dee2e6;">
                            <small class="text-muted">Average Rating</small>
                            <h5 style="color: #0d6efd; font-weight: 700; margin: 5px 0;">
                                {{ round(auth()->user()->averageRating(), 1) }} / 5.0
                            </h5>
                        </div>
                        <div style="padding: 12px 0; border-bottom: 1px solid #dee2e6;">
                            <small class="text-muted">Total Items</small>
                            <h5 style="color: #0d6efd; font-weight: 700; margin: 5px 0;">
                                {{ auth()->user()->items->count() }}
                            </h5>
                        </div>
                        <div style="padding: 12px 0;">
                            <small class="text-muted">Total Ratings</small>
                            <h5 style="color: #0d6efd; font-weight: 700; margin: 5px 0;">
                                {{ auth()->user()->ratingsReceived->count() }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Tab -->
    <div class="tab-pane fade" id="password" role="tabpanel">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lock"></i> Change Password
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('frontend.profile.password.update') }}">
                            @csrf
                            @method('PATCH')

                            <!-- Current Password -->
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                <small class="text-muted">At least 8 characters</small>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                                @error('password_confirmation')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-shield-check"></i> Security Tips
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="bi bi-check-circle" style="color: #28a745;"></i>
                                <strong>Use a strong password</strong>
                                <p class="text-muted small mb-0">Include uppercase, lowercase, numbers, and symbols</p>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle" style="color: #28a745;"></i>
                                <strong>Don't reuse passwords</strong>
                                <p class="text-muted small mb-0">Use a unique password for this account</p>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle" style="color: #28a745;"></i>
                                <strong>Update regularly</strong>
                                <p class="text-muted small mb-0">Change your password every 3 months</p>
                            </li>
                            <li>
                                <i class="bi bi-check-circle" style="color: #28a745;"></i>
                                <strong>Never share your password</strong>
                                <p class="text-muted small mb-0">We will never ask for your password</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preferences Tab -->
    <div class="tab-pane fade" id="preferences" role="tabpanel">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-gear"></i> Notification Preferences
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('frontend.profile.preferences.update') }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="email_notifications" class="form-check-input" id="emailNotif"
                                    {{ old('email_notifications', auth()->user()->email_notifications ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="emailNotif">
                                    Email notifications for transactions
                                </label>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="message_notifications" class="form-check-input" id="msgNotif"
                                    {{ old('message_notifications', auth()->user()->message_notifications ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="msgNotif">
                                    Email notifications for new messages
                                </label>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="rating_notifications" class="form-check-input" id="rateNotif"
                                    {{ old('rating_notifications', auth()->user()->rating_notifications ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="rateNotif">
                                    Email notifications for new ratings
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-circle"></i> Save Preferences
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-eye-slash"></i> Privacy Settings
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Profile Visibility</label>
                            <select class="form-select">
                                <option selected>Public (Visible to all students)</option>
                                <option>Private (Hidden from others)</option>
                            </select>
                            <small class="text-muted">Others can see your ratings and reviews</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Show Email</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showEmail" checked>
                                <label class="form-check-label" for="showEmail">
                                    Display email on your profile
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                Your ratings and reviews are always public to maintain trust in the community.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Danger Zone -->
<div style="margin-top: 40px;">
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle"></i> Danger Zone
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Deleting your account is permanent and cannot be undone.</p>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                <i class="bi bi-trash"></i> Delete My Account
            </button>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title" style="color: #dc3545;">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color: #666;">
                    <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
                </p>
                <p class="text-muted small mb-3">
                    You must have no active transactions or pending penalties to delete your account.
                </p>
                <label class="form-label">Type your email to confirm:</label>
                <input type="email" class="form-control" id="deleteConfirmEmail" placeholder="{{ auth()->user()->email }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('frontend.profile.delete.confirm') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" id="deleteConfirmBtn" disabled>
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Enable delete button only when email matches
    const emailInput = document.getElementById('deleteConfirmEmail');
    const deleteBtn = document.getElementById('deleteConfirmBtn');
    const userEmail = '{{ auth()->user()->email }}';

    emailInput.addEventListener('input', function() {
        deleteBtn.disabled = this.value !== userEmail;
    });
</script>
@endsection
