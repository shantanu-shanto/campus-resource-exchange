@extends('layouts.app')

@section('title', $university->name . ' - Super Admin')

@section('content')

<!-- Back Button -->
<div class="mb-3">
    <a href="{{ route('super-admin.universities.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Universities
    </a>
</div>

<!-- University Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 style="color: #0d6efd; font-weight: 700; margin-bottom: 15px;">
                    {{ $university->name }}
                </h2>
                <div class="mb-2">
                    <i class="bi bi-geo-alt"></i>
                    <strong>Location:</strong> {{ $university->city }}, {{ $university->state }}, {{ $university->country }}
                </div>
                <div class="mb-2">
                    <i class="bi bi-at"></i>
                    <strong>Domain:</strong>
                    <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px;">@{{ $university->domain }}</code>
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar"></i>
                    <strong>Applied:</strong> {{ $university->created_at->format('M d, Y') }}
                </div>
                @if ($university->approved_at)
                    <div class="mb-2">
                        <i class="bi bi-check-circle"></i>
                        <strong>Approved:</strong> {{ $university->approved_at->format('M d, Y') }}
                    </div>
                @endif
            </div>
            <div class="col-md-4 text-md-end">
                @if ($university->status === 'pending')
                    <span class="badge bg-warning" style="font-size: 1.1rem; padding: 10px 20px;">
                        <i class="bi bi-clock"></i> Pending Review
                    </span>
                @elseif ($university->status === 'approved')
                    <span class="badge bg-success" style="font-size: 1.1rem; padding: 10px 20px;">
                        <i class="bi bi-check-circle"></i> Approved
                    </span>
                @else
                    <span class="badge bg-danger" style="font-size: 1.1rem; padding: 10px 20px;">
                        <i class="bi bi-x-circle"></i> Rejected
                    </span>
                @endif
            </div>
        </div>

        @if ($university->description)
            <div class="mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <strong>Description:</strong>
                <p class="mb-0 mt-2">{{ $university->description }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ $stats['total_users'] }}</h3>
                <p class="text-muted mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-person-check" style="font-size: 2rem; color: #28a745;"></i>
                <h3 style="color: #28a745; font-weight: 700; margin: 10px 0;">{{ $stats['verified_users'] }}</h3>
                <p class="text-muted mb-0">Verified Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-box" style="font-size: 2rem; color: #0d6efd;"></i>
                <h3 style="color: #0d6efd; font-weight: 700; margin: 10px 0;">{{ $stats['total_items'] }}</h3>
                <p class="text-muted mb-0">Total Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-box-arrow-in-right" style="font-size: 2rem; color: #28a745;"></i>
                <h3 style="color: #28a745; font-weight: 700; margin: 10px 0;">{{ $stats['available_items'] }}</h3>
                <p class="text-muted mb-0">Available Items</p>
            </div>
        </div>
    </div>
</div>

<!-- Applicant Information -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person"></i> Applicant Information
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <strong>Name:</strong>
                <p class="mb-0">{{ $university->applicant_name }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <strong>Email:</strong>
                <p class="mb-0">{{ $university->applicant_email }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <strong>Phone:</strong>
                <p class="mb-0">{{ $university->applicant_phone ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     ADMIN CREDENTIALS CARD
     Only shown for approved universities.
     ============================================================ -->
@if ($university->isApproved())
    <div class="card mb-4" id="credentials-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-key"></i> University Admin Credentials</span>
            @if ($university->credentials_updated_at)
                <small class="text-muted">
                    Last updated: {{ $university->credentials_updated_at->format('M d, Y \a\t h:i A') }}
                </small>
            @endif
        </div>
        <div class="card-body">

            {{-- ── Flash messages ── --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($university->admin_email)

                {{-- ── CREDENTIALS VIEWER ── --}}
                <div class="mb-4" style="background: #f8f9fa; border-radius: 10px; padding: 20px; border: 1px solid #dee2e6;">
                    <h6 class="mb-3" style="color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                        Current Credentials
                    </h6>

                    {{-- Email row --}}
                    <div class="row align-items-center mb-3">
                        <div class="col-sm-3">
                            <label class="text-muted small fw-semibold">
                                <i class="bi bi-envelope me-1"></i> Admin Email
                            </label>
                        </div>
                        <div class="col-sm-7">
                            <div class="d-flex align-items-center gap-2">
                                <code id="admin-email-display"
                                      style="background: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #dee2e6; font-size: 0.95rem; flex: 1;">
                                    {{ $university->admin_email }}
                                </code>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="copyToClipboard('{{ $university->admin_email }}', this)"
                                        title="Copy email">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Password row --}}
                    <div class="row align-items-center">
                        <div class="col-sm-3">
                            <label class="text-muted small fw-semibold">
                                <i class="bi bi-lock me-1"></i> Password
                            </label>
                        </div>
                        <div class="col-sm-7">
                            <div class="d-flex align-items-center gap-2">
                                {{-- Masked display --}}
                                <code id="password-masked"
                                      style="background: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #dee2e6; font-size: 0.95rem; flex: 1; color: #6c757d; letter-spacing: 0.15em;">
                                    ••••••••••••
                                </code>
                                {{-- Revealed display (hidden by default) --}}
                                <code id="password-revealed"
                                      style="background: #fff3cd; padding: 6px 12px; border-radius: 6px; border: 1px solid #ffc107; font-size: 0.95rem; flex: 1; display: none;">
                                    {{ $university->admin_password_plain ?? '(not available — reset to generate new)' }}
                                </code>
                                {{-- Reveal / Hide toggle --}}
                                <button type="button"
                                        id="reveal-btn"
                                        class="btn btn-sm btn-outline-warning"
                                        onclick="togglePasswordReveal()"
                                        title="Reveal password">
                                    <i class="bi bi-eye" id="reveal-icon"></i>
                                </button>
                                {{-- Copy button --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="copyToClipboard('{{ $university->admin_password_plain }}', this)"
                                        title="Copy password">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-info-circle"></i>
                                Revealing the password is logged. Share securely with the university representative.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- ── EDIT CREDENTIALS FORM ── --}}
                <div>
                    <button class="btn btn-outline-primary btn-sm mb-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#editCredentialsForm"
                            aria-expanded="false">
                        <i class="bi bi-pencil"></i> Edit Credentials
                    </button>

                    <div class="collapse" id="editCredentialsForm">
                        <div style="background: #fff; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px;">
                            <h6 class="mb-3" style="color: #495057; font-weight: 600;">
                                <i class="bi bi-pencil-square"></i> Update Credentials
                            </h6>

                            <form method="POST"
                                  action="{{ route('super-admin.universities.update-credentials', $university) }}">
                                @csrf

                                {{-- Admin Email --}}
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label fw-semibold">
                                        Admin Email <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="admin_email"
                                        name="admin_email"
                                        class="form-control @error('admin_email') is-invalid @enderror"
                                        value="{{ old('admin_email', $university->admin_email) }}"
                                        required
                                    />
                                    <div class="form-text">
                                        Changing the email will also update the login account for this university's admin.
                                    </div>
                                    @error('admin_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- New Password --}}
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label fw-semibold">
                                        New Password
                                        <span class="text-muted fw-normal">(leave blank to keep current)</span>
                                    </label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            id="admin_password"
                                            name="admin_password"
                                            class="form-control @error('admin_password') is-invalid @enderror"
                                            placeholder="Enter new password or leave blank"
                                            minlength="8"
                                        />
                                        <button class="btn btn-outline-secondary"
                                                type="button"
                                                onclick="generatePassword()"
                                                title="Auto-generate password">
                                            <i class="bi bi-arrow-repeat"></i> Generate
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 8 characters.</div>
                                    @error('admin_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Changes
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#editCredentialsForm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── QUICK RESET BUTTON ── --}}
                <div class="mt-3 pt-3" style="border-top: 1px solid #dee2e6;">
                    <form method="POST"
                          action="{{ route('super-admin.universities.reset-credentials', $university) }}"
                          onsubmit="return confirm('Generate a new random password for {{ addslashes($university->name) }}? The current password will be replaced.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Quick Reset Password
                        </button>
                        <small class="text-muted ms-2">Generates a new random password instantly.</small>
                    </form>
                </div>

            @else

                {{-- ── NO CREDENTIALS ISSUED YET ── --}}
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    No credentials have been issued for this university yet.
                </div>

                <form method="POST"
                      action="{{ route('super-admin.universities.issue-credentials', $university) }}"
                      onsubmit="return confirm('Issue admin credentials for {{ addslashes($university->name) }}?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-key"></i> Issue Admin Credentials
                    </button>
                    <small class="text-muted ms-2">
                        Auto-generates a secure email and password for the university admin.
                    </small>
                </form>

            @endif
        </div>
    </div>
@endif

<!-- Rejection Reason -->
@if ($university->isRejected() && $university->rejection_reason)
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-x-circle"></i> Rejection Reason
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $university->rejection_reason }}</p>
            <small class="text-muted">Rejected on {{ $university->rejected_at->format('M d, Y') }}</small>
        </div>
    </div>
@endif

<!-- Actions -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-lightning"></i> Actions
    </div>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap">
            @if ($university->isPending())
                <form method="POST" action="{{ route('super-admin.universities.approve', $university) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Approve {{ addslashes($university->name) }}?')">
                        <i class="bi bi-check-circle"></i> Approve University
                    </button>
                </form>

                <button type="button" class="btn btn-danger"
                        data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle"></i> Reject Application
                </button>
            @endif

            @if ($university->isApproved())
                <form method="POST" action="{{ route('super-admin.universities.suspend', $university) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('Suspend {{ addslashes($university->name) }}? All users will lose access.')">
                        <i class="bi bi-pause-circle"></i> Suspend University
                    </button>
                </form>
            @endif

            <a href="{{ route('super-admin.users.index', ['university' => $university->id]) }}"
               class="btn btn-primary">
                <i class="bi bi-people"></i> View Users
            </a>

            <form method="POST" action="{{ route('super-admin.universities.destroy', $university) }}" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('PERMANENTLY DELETE {{ addslashes($university->name) }}? This cannot be undone!')">
                    <i class="bi bi-trash"></i> Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('super-admin.universities.reject', $university) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting <strong>{{ $university->name }}</strong>'s application:</p>
                    <textarea
                        name="rejection_reason"
                        class="form-control"
                        rows="4"
                        placeholder="E.g., Invalid domain, incomplete information, etc."
                        required
                    ></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Toggle password reveal/hide ──────────────────────────────
    function togglePasswordReveal() {
        const masked   = document.getElementById('password-masked');
        const revealed = document.getElementById('password-revealed');
        const icon     = document.getElementById('reveal-icon');
        const btn      = document.getElementById('reveal-btn');

        const isHidden = masked.style.display !== 'none';

        if (isHidden) {
            masked.style.display   = 'none';
            revealed.style.display = 'inline';
            icon.className         = 'bi bi-eye-slash';
            btn.title              = 'Hide password';
        } else {
            masked.style.display   = 'inline';
            revealed.style.display = 'none';
            icon.className         = 'bi bi-eye';
            btn.title              = 'Reveal password';
        }
    }

    // ── Copy to clipboard ────────────────────────────────────────
    function copyToClipboard(text, btn) {
        if (!text || text === '(not available — reset to generate new)') {
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML  = '<i class="bi bi-check"></i>';
            btn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 1500);
        });
    }

    // ── Auto-generate a random password into the input ──────────
    function generatePassword() {
        const chars    = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#!';
        let password   = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('admin_password').value = password;
    }
</script>
@endpush