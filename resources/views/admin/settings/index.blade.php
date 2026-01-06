@extends('admin.layouts.app')

@section('page-title', 'System Settings')

@section('content')

<div class="row">
    <div class="col-lg-8">
        <!-- General Settings -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h3><i class="bi bi-sliders"></i> General Settings</h3>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">Platform Name</label>
                        <input type="text" name="platform_name" class="form-control" value="Campus Resource Exchange">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Platform Description</label>
                        <textarea name="platform_description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Support Phone</label>
                        <input type="tel" name="support_phone" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-admin btn-admin-primary">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Transaction Settings -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h3><i class="bi bi-arrow-left-right"></i> Transaction Settings</h3>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">Max Lending Duration (days)</label>
                        <input type="number" name="max_lending_days" class="form-control" value="30">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penalty per Day Late (BDT)</label>
                        <input type="number" name="late_penalty_amount" class="form-control" value="10" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Max Pending Requests</label>
                        <input type="number" name="max_pending_requests" class="form-control" value="5">
                    </div>

                    <button type="submit" class="btn btn-admin btn-admin-primary">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-shield-lock"></i> Security Settings</h3>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="email_verification_required" class="form-check-input" checked>
                        <label class="form-check-label">
                            Require email verification for new users
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="phone_verification_required" class="form-check-input">
                        <label class="form-check-label">
                            Require phone verification
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="item_moderation_required" class="form-check-input" checked>
                        <label class="form-check-label">
                            Require admin approval for new items
                        </label>
                    </div>

                    <button type="submit" class="btn btn-admin btn-admin-primary">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Maintenance Mode -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h3><i class="bi bi-exclamation-triangle"></i> Maintenance</h3>
            </div>
            <div class="admin-card-body">
                <p class="text-muted small mb-3">Put the platform in maintenance mode</p>
                <form method="POST" action="{{ route('admin.settings.maintenance') }}">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-tools"></i> Enable Maintenance Mode
                    </button>
                </form>
            </div>
        </div>

        <!-- Backup -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h3><i class="bi bi-cloud-download"></i> Backup</h3>
            </div>
            <div class="admin-card-body">
                <p class="text-muted small mb-3">Create a full system backup</p>
                <a href="#" class="btn btn-primary w-100">
                    <i class="bi bi-download"></i> Create Backup
                </a>
            </div>
        </div>

        <!-- System Info -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="bi bi-info-circle"></i> System Info</h3>
            </div>
            <div class="admin-card-body">
                <small class="text-muted">Laravel Version</small>
                <p style="color: #333; font-weight: 600; margin-bottom: 10px;">{{ app()->version() }}</p>

                <small class="text-muted">PHP Version</small>
                <p style="color: #333; font-weight: 600; margin-bottom: 10px;">{{ phpversion() }}</p>

                <small class="text-muted">Last Updated</small>
                <p style="color: #333; font-weight: 600;">Today</p>
            </div>
        </div>
    </div>
</div>

@endsection
