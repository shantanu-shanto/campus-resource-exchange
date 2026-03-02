{{-- resources/views/auth/super-admin-login.blade.php --}}
{{--
    Dedicated login page for the Super Admin.
    Intentionally styled differently from the student/teacher login
    to make it visually clear this is a restricted, high-privilege panel.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Super Admin Login — UniShare</title>

    {{-- Bootstrap CSS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    {{-- Bootstrap Icons --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    />

    <style>
        body {
            min-height: 100vh;
            background-color: #0f172a; /* deep dark navy */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-card {
            width: 100%;
            max-width: 420px;
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background-color: #dc2626;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            margin-bottom: 1.25rem;
        }

        .admin-title {
            color: #f1f5f9;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .admin-subtitle {
            color: #94a3b8;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .form-label {
            color: #cbd5e1;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-control {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: #f1f5f9;
            border-radius: 8px;
        }

        .form-control:focus {
            background-color: #0f172a;
            border-color: #dc2626;
            color: #f1f5f9;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
        }

        .form-control::placeholder {
            color: #475569;
        }

        .btn-admin {
            background-color: #dc2626;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.65rem;
            width: 100%;
            transition: background-color 0.2s ease;
        }

        .btn-admin:hover {
            background-color: #b91c1c;
            color: #fff;
        }

        .divider {
            border-top: 1px solid #334155;
            margin: 1.5rem 0;
        }

        .back-link {
            color: #64748b;
            font-size: 0.8rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            justify-content: center;
        }

        .back-link:hover {
            color: #94a3b8;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap .bi {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            pointer-events: none;
        }

        .input-icon-wrap .form-control {
            padding-left: 2.4rem;
        }
    </style>
</head>
<body>

<div class="admin-card">

    {{-- Badge --}}
    <div>
        <span class="admin-badge">
            <i class="bi bi-shield-lock-fill"></i>
            Restricted Access
        </span>
    </div>

    {{-- Title --}}
    <h1 class="admin-title">Super Admin Panel</h1>
    <p class="admin-subtitle">UniShare Platform Administration</p>

    {{-- Session Status (e.g. after logout) --}}
    @if (session('status'))
        <div class="alert alert-info py-2 mb-3" style="background:#1e3a5f; border-color:#2563eb; color:#93c5fd; font-size:0.85rem;">
            {{ session('status') }}
        </div>
    @endif

    {{-- Login Form --}}
    <form method="POST" action="{{ route('super-admin.login.store') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Admin Email</label>
            <div class="input-icon-wrap">
                <i class="bi bi-envelope"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="admin@unishare.com"
                    autocomplete="email"
                    autofocus
                    required
                />
            </div>
            @error('email')
                <div class="invalid-feedback d-block" style="color:#fca5a5; font-size:0.8rem;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-icon-wrap">
                <i class="bi bi-lock"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••••"
                    autocomplete="current-password"
                    required
                />
            </div>
            @error('password')
                <div class="invalid-feedback d-block" style="color:#fca5a5; font-size:0.8rem;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="form-check mb-4">
            <input
                class="form-check-input"
                type="checkbox"
                id="remember"
                name="remember"
                style="background-color:#0f172a; border-color:#334155;"
            />
            <label class="form-check-label" for="remember" style="color:#94a3b8; font-size:0.85rem;">
                Keep me signed in
            </label>
        </div>

        <button type="submit" class="btn btn-admin">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            Sign In to Admin Panel
        </button>
    </form>

    <div class="divider"></div>

    <a href="{{ route('landing') }}" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Back to UniShare
    </a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>