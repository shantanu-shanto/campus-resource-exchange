<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Campus Resource Exchange</title>

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
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }

        .register-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 45px 40px;
            width: 100%;
            max-width: 520px;
        }

        .register-card h2 {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
            text-align: center;
        }

        .register-card .subtitle {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 28px;
        }

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

        .domain-hint {
            display: none;
            font-size: 0.82rem;
            color: var(--primary-blue);
            background-color: var(--light-blue);
            border-radius: 6px;
            padding: 6px 10px;
            margin-top: 6px;
        }

        .domain-hint i {
            margin-right: 4px;
        }

        .btn-register {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: #fff;
            font-weight: 600;
            padding: 11px;
            border-radius: 8px;
            width: 100%;
            font-size: 0.97rem;
            margin-top: 6px;
            transition: background-color 0.2s;
        }

        .btn-register:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
            color: #fff;
        }

        .divider {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 22px 0;
        }

        .login-link {
            text-align: center;
            font-size: 0.9rem;
            color: #666;
        }

        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .section-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #aaa;
            margin-bottom: 14px;
            margin-top: 4px;
        }

        .form-select:disabled {
            background-color: #f5f5f5;
            color: #aaa;
            cursor: not-allowed;
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 32px 22px;
            }
        }
    </style>
</head>
<body>

    <div class="register-card">

        <h2>Create Account</h2>
        <p class="subtitle">Join your campus resource community</p>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle me-1"></i> Please fix the errors below.</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li style="font-size: 0.88rem;">{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <p class="section-label">Personal Information</p>

            <div class="mb-3">
                <label class="form-label" for="name">Full Name</label>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="e.g. John Doe"
                    required
                    autofocus
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <p class="section-label" style="margin-top: 20px;">University</p>

            {{--
                FIX: added name="state_filter" so the value is submitted
                with the form and old('state_filter') works correctly
                on validation failure. The backend ignores this field
                but it is required for the JS restore logic to function.
            --}}
            <div class="mb-3">
                <label class="form-label" for="state_filter">
                    State / Region
                    <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select"
                    id="state_filter"
                    name="state_filter"
                    required
                >
                    <option value="">— Select your state —</option>
                    @foreach ($universities->pluck('state')->unique()->sort()->values() as $state)
                        <option value="{{ $state }}" {{ old('state_filter') == $state ? 'selected' : '' }}>
                            {{ $state }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text" style="font-size: 0.8rem; color: #888;">
                    Select your state first to filter universities.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="university_id">
                    University
                    <span class="text-danger">*</span>
                </label>
                <select
                    class="form-select @error('university_id') is-invalid @enderror"
                    id="university_id"
                    name="university_id"
                    disabled
                    required
                >
                    <option value="">— Select state first —</option>
                    @foreach ($universities as $uni)
                        <option
                            value="{{ $uni->id }}"
                            data-state="{{ $uni->state }}"
                            data-domain="{{ $uni->domain }}"
                            {{ old('university_id') == $uni->id ? 'selected' : '' }}
                        >
                            {{ $uni->name }} — {{ $uni->city }}
                        </option>
                    @endforeach
                </select>
                @error('university_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="domain-hint" id="domainHint">
                    <i class="bi bi-envelope-check"></i>
                    Your email must end with <strong id="domainText"></strong>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">
                    University Email
                    <span class="text-danger">*</span>
                </label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="yourname@university.edu"
                    required
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <p class="section-label" style="margin-top: 20px;">Password</p>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Min. 8 characters"
                    required
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Repeat your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-register">
                <i class="bi bi-person-check me-1"></i> Create Account
            </button>
        </form>

        <hr class="divider">

        <p class="login-link">
            Already have an account? <a href="{{ route('login') }}">Sign in here</a>
        </p>

        <p class="login-link mt-2">
            Want to register your university?
            <a href="{{ route('university.apply') }}">Apply here</a>
        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const stateFilter = document.getElementById('state_filter');
        const uniSelect   = document.getElementById('university_id');
        const domainHint  = document.getElementById('domainHint');
        const domainText  = document.getElementById('domainText');

        // Snapshot of all university options before any filtering
        const allOptions = Array.from(uniSelect.querySelectorAll('option[data-state]'));

        /**
         * Rebuild the university dropdown for a given state.
         * Extracted as a function so both the event listener
         * and the DOMContentLoaded restore can share the same logic.
         */
        function filterUniversitiesByState(selectedState) {
            uniSelect.innerHTML = '<option value="">— Select your university —</option>';
            domainHint.style.display = 'none';
            domainText.textContent   = '';

            if (!selectedState) {
                uniSelect.disabled = true;
                uniSelect.innerHTML = '<option value="">— Select state first —</option>';
                return;
            }

            const filtered = allOptions.filter(opt => opt.dataset.state === selectedState);

            if (filtered.length === 0) {
                uniSelect.innerHTML = '<option value="">No approved universities in this state</option>';
                uniSelect.disabled = true;
                return;
            }

            filtered.forEach(opt => uniSelect.appendChild(opt.cloneNode(true)));
            uniSelect.disabled = false;
        }

        /**
         * Show or hide the domain hint based on the selected university.
         */
        function showDomainHint(selectEl) {
            const selected = selectEl.options[selectEl.selectedIndex];
            const domain   = selected ? selected.dataset.domain : null;

            if (domain) {
                domainText.textContent   = '@' + domain;
                domainHint.style.display = 'block';
            } else {
                domainHint.style.display = 'none';
                domainText.textContent   = '';
            }
        }

        stateFilter.addEventListener('change', function () {
            filterUniversitiesByState(this.value);
        });

        uniSelect.addEventListener('change', function () {
            showDomainHint(this);
        });

        /**
         * Restore dropdowns on validation failure using old() values.
         *
         * FIX: correct order is:
         *   1. Set state value
         *   2. Call filterUniversitiesByState() to rebuild options
         *   3. Set university value (options now exist in the DOM)
         *   4. Call showDomainHint() to display the domain
         *
         * The old code dispatched a 'change' event then immediately set
         * uniSelect.value before the rebuilt options were in place,
         * so the university selection was never restored correctly.
         */
        document.addEventListener('DOMContentLoaded', function () {
            const oldState = "{{ old('state_filter') }}";
            const oldUni   = "{{ old('university_id') }}";

            if (oldState) {
                stateFilter.value = oldState;
                filterUniversitiesByState(oldState);

                if (oldUni) {
                    // Options are now in the DOM — safe to set value
                    uniSelect.value = oldUni;
                    showDomainHint(uniSelect);
                }
            }
        });
    </script>

</body>
</html>