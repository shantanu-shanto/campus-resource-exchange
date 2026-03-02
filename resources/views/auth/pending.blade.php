<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Pending - Campus Resource Exchange</title>

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
            padding: 20px;
        }

        .pending-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        .pending-icon-wrapper {
            width: 90px;
            height: 90px;
            background-color: var(--light-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
        }

        .pending-icon-wrapper i {
            font-size: 2.8rem;
            color: var(--primary-blue);
        }

        .pending-card h1 {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 12px;
        }

        .pending-card p.subtitle {
            color: #666;
            font-size: 0.97rem;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .info-box {
            background-color: var(--light-blue);
            border-left: 4px solid var(--primary-blue);
            border-radius: 8px;
            padding: 16px 20px;
            text-align: left;
            margin-bottom: 30px;
        }

        .info-box p {
            margin: 0;
            font-size: 0.9rem;
            color: #0b5ed7;
            line-height: 1.6;
        }

        .info-box p strong {
            display: block;
            margin-bottom: 4px;
            font-size: 0.95rem;
        }

        .steps {
            text-align: left;
            margin-bottom: 30px;
        }

        .steps h6 {
            font-weight: 700;
            color: #333;
            margin-bottom: 14px;
            font-size: 0.95rem;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .step-number {
            width: 26px;
            height: 26px;
            min-width: 26px;
            background-color: var(--primary-blue);
            color: #fff;
            border-radius: 50%;
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }

        .step-text {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.5;
        }

        .divider {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 25px 0;
        }

        .btn-back {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: #fff;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
        }

        .btn-back:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
            color: #fff;
        }

        .login-link {
            margin-top: 18px;
            font-size: 0.88rem;
            color: #888;
        }

        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .pending-card {
                padding: 35px 24px;
            }

            .pending-card h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <div class="pending-card">

        {{-- Icon --}}
        <div class="pending-icon-wrapper">
            <i class="bi bi-hourglass-split"></i>
        </div>

        {{-- Title --}}
        <h1>Registration Submitted!</h1>
        <p class="subtitle">
            Your account has been created successfully. Before you can access the platform,
            your University Admin needs to verify your identity.
        </p>

        {{-- Info box --}}
        <div class="info-box">
            <p>
                <strong><i class="bi bi-info-circle me-1"></i> What happens next?</strong>
                Your University Admin will review your registration and verify that you are
                a valid member of your campus. This usually takes <strong>1–2 business days</strong>.
            </p>
        </div>

        {{-- Steps --}}
        <div class="steps">
            <h6>Your registration journey:</h6>

            <div class="step-item">
                <div class="step-number" style="background-color: #28a745;">
                    <i class="bi bi-check" style="font-size: 0.85rem;"></i>
                </div>
                <div class="step-text">
                    <strong style="color: #28a745;">Done</strong> — You submitted your registration with your university email.
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <strong>Pending</strong> — Your University Admin reviews and verifies your account.
                </div>
            </div>

            <div class="step-item">
                <div class="step-number" style="background-color: #adb5bd;">3</div>
                <div class="step-text" style="color: #adb5bd;">
                    Access granted — You can log in and start browsing campus resources.
                </div>
            </div>
        </div>

        <hr class="divider">

        {{-- CTA --}}
        <a href="{{ route('login') }}" class="btn-back">
            <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
        </a>

        <p class="login-link">
            Registered with the wrong email?
            <a href="{{ route('register') }}">Register again</a>
        </p>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>