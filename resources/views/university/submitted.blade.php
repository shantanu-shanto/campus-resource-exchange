<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - Campus Resource Exchange</title>

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
            padding: 24px 16px;
        }

        .submitted-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        .icon-wrapper {
            width: 90px;
            height: 90px;
            background-color: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px auto;
        }

        .icon-wrapper i {
            font-size: 2.8rem;
            color: #28a745;
        }

        h1 {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 0.93rem;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .info-box {
            background-color: var(--light-blue);
            border-left: 4px solid var(--primary-blue);
            border-radius: 8px;
            padding: 14px 18px;
            text-align: left;
            margin-bottom: 28px;
            font-size: 0.88rem;
            color: #0b5ed7;
            line-height: 1.6;
        }

        .info-box strong {
            display: block;
            margin-bottom: 4px;
            font-size: 0.92rem;
        }

        .steps {
            text-align: left;
            margin-bottom: 30px;
        }

        .steps h6 {
            font-weight: 700;
            color: #333;
            margin-bottom: 14px;
            font-size: 0.93rem;
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
            font-size: 0.88rem;
            color: #555;
            line-height: 1.5;
        }

        hr {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 24px 0;
        }

        .btn-home {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: #fff;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }

        .btn-home:hover {
            background-color: var(--dark-blue);
            color: #fff;
        }

        .footer-links {
            margin-top: 18px;
            font-size: 0.85rem;
            color: #888;
        }

        .footer-links a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .submitted-card {
                padding: 35px 22px;
            }
        }
    </style>
</head>
<body>

    <div class="submitted-card">

        <div class="icon-wrapper">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <h1>Application Submitted!</h1>
        <p class="subtitle">
            Your university registration request has been received.
            The Super Admin will review it and get back to you shortly.
        </p>

        <div class="info-box">
            <strong><i class="bi bi-envelope me-1"></i> Check your inbox</strong>
            If approved, you will receive your University Admin login credentials
            at the email address you provided. This usually takes <strong>2–3 business days</strong>.
        </div>

        <div class="steps">
            <h6>What happens next:</h6>

            <div class="step-item">
                <div class="step-number" style="background-color: #28a745;">
                    <i class="bi bi-check" style="font-size: 0.85rem;"></i>
                </div>
                <div class="step-text">
                    <strong style="color: #28a745;">Done</strong> — Your application was submitted successfully.
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <strong>Under Review</strong> — The Super Admin reviews your application and verifies your university details.
                </div>
            </div>

            <div class="step-item">
                <div class="step-number" style="background-color: #adb5bd;">3</div>
                <div class="step-text" style="color: #adb5bd;">
                    Credentials Issued — You receive your University Admin login via email.
                </div>
            </div>

            <div class="step-item">
                <div class="step-number" style="background-color: #adb5bd;">4</div>
                <div class="step-text" style="color: #adb5bd;">
                    Go Live — Log in to your admin panel and start verifying students on your campus.
                </div>
            </div>
        </div>

        <hr>

        <a href="{{ route('landing') }}" class="btn-home">
            <i class="bi bi-house me-1"></i> Back to Home
        </a>

        <p class="footer-links">
            Are you a student? <a href="{{ route('register') }}">Register here</a>
            &nbsp;&bull;&nbsp;
            <a href="{{ route('login') }}">Sign in</a>
        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>