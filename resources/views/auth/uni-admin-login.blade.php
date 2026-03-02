{{-- resources/views/auth/uni-admin-login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>University Admin Login — UniShare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .admin-card {
            width: 100%;
            max-width: 420px;
            background-color: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background-color: #0d6efd;
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
            color: #1a1a2e;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .admin-subtitle {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        .btn-admin {
            background-color: #0d6efd;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.65rem;
            width: 100%;
            transition: background-color 0.2s ease;
        }

        .btn-admin:hover {
            background-color: #0b5ed7;
            color: #fff;
        }

        .divider {
            border-top: 1px solid #dee2e6;
            margin: 1.5rem 0;
        }

        .back-link {
            color: #6c757d;
            font-size: 0.8rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            justify-content: center;
        }

        .back-link:hover {
            color: #0d6efd;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap .bi {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            pointer-events: none;
        }

        .input-icon-wrap .form-control {
            padding-left: 2.4rem;
        }
    </style>
</head>
<body>

<div class="admin-card">

    <div>
        <span class="admin-badge">
            <i class="bi bi-building"></i>
            University Admin
        </span>
    </div>

    <h1 class="admin-title">Admin Panel Login</h1>
    <p class="admin-subtitle">UniShare — University Administration</p>

    @if (session('status'))
        <div class="alert alert-info py-2 mb-3" style="font-size: 0.85rem;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('uni-admin.login.store') }}" novalidate>
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
                    placeholder="admin@youruniversity.ac.in"
                    autocomplete="email"
                    autofocus
                    required
                />
            </div>
            @error('email')
                <div class="invalid-feedback d-block" style="font-size: 0.8rem;">
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
                <div class="invalid-feedback d-block" style="font-size: 0.8rem;">
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
            />
            <label class="form-check-label" for="remember" style="font-size: 0.85rem; color: #6c757d;">
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