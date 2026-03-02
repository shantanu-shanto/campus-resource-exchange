<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to Join - Campus Resource Exchange</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-blue: #0d6efd;
            --dark-blue: #0b5ed7;
            --light-blue: #e7f1ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 16px;
        }

        .apply-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 45px 40px;
            width: 100%;
            max-width: 620px;
            margin-bottom: 40px;
        }

        /* Header */
        .apply-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .apply-header .icon-wrapper {
            width: 70px;
            height: 70px;
            background-color: var(--light-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }

        .apply-header .icon-wrapper i {
            font-size: 2rem;
            color: var(--primary-blue);
        }

        .apply-header h2 {
            font-size: 1.65rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        .apply-header p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Section labels */
        .section-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #aaa;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Form elements */
        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: #333;
            margin-bottom: 5px;
        }

        .form-control,
        .form-select {
            border: 1px solid #dde1e7;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.93rem;
            transition: border-color 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            font-size: 0.82rem;
        }

        .form-text {
            font-size: 0.8rem;
            color: #888;
        }

        /* Domain field helper */
        .domain-prefix {
            background-color: #f8f9fa;
            border: 1px solid #dde1e7;
            border-right: none;
            border-radius: 8px 0 0 8px;
            padding: 10px 12px;
            font-size: 0.88rem;
            color: #888;
        }

        .domain-prefix + .form-control {
            border-radius: 0 8px 8px 0;
        }

        /* Info alert */
        .info-box {
            background-color: var(--light-blue);
            border-left: 4px solid var(--primary-blue);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 28px;
            font-size: 0.88rem;
            color: #0b5ed7;
            line-height: 1.6;
        }

        .info-box i {
            margin-right: 6px;
        }

        /* Submit button */
        .btn-submit {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-size: 0.97rem;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
            color: #fff;
        }

        .back-link {
            text-align: center;
            font-size: 0.88rem;
            color: #666;
            margin-top: 18px;
        }

        .back-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .apply-card {
                padding: 32px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="apply-card">

        {{-- Header --}}
        <div class="apply-header">
            <div class="icon-wrapper">
                <i class="bi bi-building-add"></i>
            </div>
            <h2>Register Your University</h2>
            <p>
                Submit an application to bring your university onto the platform.
                The Super Admin will review your request and issue login credentials
                for your university's admin panel.
            </p>
        </div>

        {{-- Info box --}}
        <div class="info-box">
            <i class="bi bi-info-circle"></i>
            After submission, our team will review your application within <strong>2–3 business days</strong>.
            If approved, you will receive admin credentials via the email you provide below.
        </div>

        {{-- Error summary --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <strong><i class="bi bi-exclamation-triangle me-1"></i> Please fix the errors below.</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li style="font-size: 0.88rem;">{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('university.apply.store') }}" id="applyForm">
            @csrf

            {{-- ================================
                 SECTION 1: University Details
                 ================================ --}}
            <p class="section-label">University Information</p>

            <div class="mb-3">
                <label class="form-label" for="name">
                    University Name <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="e.g. BITS Pilani"
                    required
                    autofocus
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="domain">
                    Official Email Domain <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="domain-prefix">@</span>
                    <input
                        type="text"
                        class="form-control @error('domain') is-invalid @enderror"
                        id="domain"
                        name="domain"
                        value="{{ old('domain') }}"
                        placeholder="bits-pilani.ac.in"
                        required
                    >
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-text">
                    Students registering under your university must have an email ending with this domain.
                    Do not include the @ symbol.
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="state">
                        State / Region <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control @error('state') is-invalid @enderror"
                        id="state"
                        name="state"
                        value="{{ old('state') }}"
                        placeholder="e.g. Rajasthan"
                        required
                    >
                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="city">
                        City <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control @error('city') is-invalid @enderror"
                        id="city"
                        name="city"
                        value="{{ old('city') }}"
                        placeholder="e.g. Pilani"
                        required
                    >
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="description">
                    Brief Description <span class="text-muted">(optional)</span>
                </label>
                <textarea
                    class="form-control @error('description') is-invalid @enderror"
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Tell us a bit about your university..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================================
                 SECTION 2: Applicant Details
                 ================================ --}}
            <p class="section-label" style="margin-top: 10px;">Your Contact Information</p>
            <p style="font-size: 0.85rem; color: #777; margin-bottom: 16px;">
                This should be the person who will manage the university admin panel if approved.
            </p>

            <div class="mb-3">
                <label class="form-label" for="applicant_name">
                    Your Full Name <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    class="form-control @error('applicant_name') is-invalid @enderror"
                    id="applicant_name"
                    name="applicant_name"
                    value="{{ old('applicant_name') }}"
                    placeholder="e.g. Dr. Rahul Sharma"
                    required
                >
                @error('applicant_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="applicant_email">
                    Your Email Address <span class="text-danger">*</span>
                </label>
                <input
                    type="email"
                    class="form-control @error('applicant_email') is-invalid @enderror"
                    id="applicant_email"
                    name="applicant_email"
                    value="{{ old('applicant_email') }}"
                    placeholder="admin@bits-pilani.ac.in"
                    required
                >
                <div class="form-text">
                    Admin credentials will be sent to this email address upon approval.
                </div>
                @error('applicant_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label" for="applicant_phone">
                    Phone Number <span class="text-muted">(optional)</span>
                </label>
                <input
                    type="tel"
                    class="form-control @error('applicant_phone') is-invalid @enderror"
                    id="applicant_phone"
                    name="applicant_phone"
                    value="{{ old('applicant_phone') }}"
                    placeholder="+91 98765 43210"
                >
                @error('applicant_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-submit">
                <i class="bi bi-send me-1"></i> Submit Application
            </button>
        </form>

        <p class="back-link">
            <a href="{{ route('login') }}">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
            &nbsp;&bull;&nbsp;
            Already a student? <a href="{{ route('register') }}">Register here</a>
        </p>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /**
         * Auto-format domain field:
         * Strip any leading @ if the user types it by mistake
         */
        document.getElementById('domain').addEventListener('input', function () {
            this.value = this.value.replace(/^@+/, '').toLowerCase().trim();
        });
    </script>

</body>
</html>