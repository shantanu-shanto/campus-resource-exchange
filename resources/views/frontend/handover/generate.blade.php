@extends('layouts.app')

@section('title', $type === 'pickup' ? 'Pickup Handover QR - UniShare' : 'Return Handover QR - UniShare')

@section('content')

<div style="max-width: 560px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <a href="{{ route('frontend.transactions.show', $transaction) }}"
           style="color: #0d6efd; text-decoration: none; font-size: 0.9rem;">
            <i class="bi bi-arrow-left me-1"></i> Back to Transaction
        </a>
        <h1 class="page-title mt-2">
            {{ $type === 'pickup' ? 'Pickup Handover QR' : 'Return Handover QR' }}
        </h1>
        <p class="text-muted">
            {{ $type === 'pickup'
                ? 'Show this QR to the borrower. Both of you must confirm to activate the transaction.'
                : 'Show this QR to the owner. Both of you must confirm to complete the return.' }}
        </p>
    </div>

    {{-- Item Summary Card --}}
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <div style="width: 52px; height: 52px; background: #e7f1ff; border-radius: 8px;
                        display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-box-seam" style="font-size: 1.6rem; color: #0d6efd;"></i>
            </div>
            <div>
                <div style="font-weight: 700; color: #1a1a2e;">{{ $transaction->item->title }}</div>
                <small class="text-muted">
                    {{ $type === 'pickup' ? 'Borrower' : 'Owner' }}:
                    {{ $type === 'pickup' ? $transaction->borrower->name : $transaction->owner->name }}
                </small>
            </div>
            <div class="ms-auto">
                <span class="badge bg-primary">{{ ucfirst($type) }}</span>
            </div>
        </div>
    </div>

    {{-- QR Code Card --}}
    <div class="card mb-4">
        <div class="card-body text-center py-4">

            {{-- Countdown Timer --}}
            <div class="mb-3">
                <span class="text-muted" style="font-size: 0.85rem;">Token expires in</span>
                <div id="countdown"
                     style="font-size: 1.6rem; font-weight: 700; color: #0d6efd; font-variant-numeric: tabular-nums;">
                    15:00
                </div>
            </div>

            {{-- QR canvas rendered by qrcode.js --}}
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <div id="qrcode"
                     style="padding: 16px; background: #fff; border: 2px solid #dee2e6;
                            border-radius: 12px; display: inline-block;">
                </div>
            </div>

            <p class="text-muted" style="font-size: 0.82rem; margin-bottom: 0;">
                <i class="bi bi-phone me-1"></i>
                The other party scans this with their phone camera
            </p>
        </div>
    </div>

    {{-- Confirmation Status Card --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-person-check me-2"></i>Confirmation Status
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle" style="font-size: 1.2rem; color: #0d6efd;"></i>
                    <span style="font-weight: 600;">
                        {{ $transaction->owner->name }}
                        @if(auth()->id() === $transaction->owner_id)
                            <span class="text-muted" style="font-weight: 400;">(you)</span>
                        @endif
                    </span>
                </div>
                <div id="owner-status">
                    @if ($verification->ownerHasConfirmed())
                        <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Confirmed</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Waiting</span>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle" style="font-size: 1.2rem; color: #6f42c1;"></i>
                    <span style="font-weight: 600;">
                        {{ $transaction->borrower->name }}
                        @if(auth()->id() === $transaction->borrower_id)
                            <span class="text-muted" style="font-weight: 400;">(you)</span>
                        @endif
                    </span>
                </div>
                <div id="borrower-status">
                    @if ($verification->borrowerHasConfirmed())
                        <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Confirmed</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Waiting</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Both confirmed banner — hidden until polling detects it --}}
    <div id="success-banner"
         class="alert alert-success text-center"
         style="display: none !important;">
        <i class="bi bi-check-circle-fill me-2" style="font-size: 1.3rem;"></i>
        <strong>Both parties confirmed!</strong>
        <p class="mb-2 mt-1">
            {{ $type === 'pickup' ? 'Transaction is now active. Good luck!' : 'Return complete. You can now rate each other.' }}
        </p>
        <a href="{{ route('frontend.transactions.show', $transaction) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-arrow-right me-1"></i> Go to Transaction
        </a>
    </div>

    {{-- Regenerate button — shown if token expires --}}
    <div id="expired-banner" style="display: none !important;" class="text-center">
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Token expired.</strong> Generate a new QR to try again.
        </div>
        <form method="POST"
              action="{{ route('frontend.handover.generate', $transaction) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise me-2"></i>Generate New QR
            </button>
        </form>
    </div>

</div>

@endsection

@section('extra-js')
{{-- qrcode.js from cdnjs — no installation needed --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // ── Generate QR ─────────────────────────────────────────────
    const scanUrl = @json($scanUrl);

    new QRCode(document.getElementById('qrcode'), {
        text:           scanUrl,
        width:          220,
        height:         220,
        colorDark:      '#0d6efd',
        colorLight:     '#ffffff',
        correctLevel:   QRCode.CorrectLevel.H,
    });

    // ── Countdown Timer ─────────────────────────────────────────
    const expiresAt  = new Date(@json($verification->expires_at->toIso8601String()));
    const countdown  = document.getElementById('countdown');
    let timerExpired = false;

    function updateCountdown() {
        const secondsLeft = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        const mins        = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
        const secs        = String(secondsLeft % 60).padStart(2, '0');

        countdown.textContent = `${mins}:${secs}`;

        if (secondsLeft <= 60) {
            countdown.style.color = '#dc3545';
        }

        if (secondsLeft === 0 && !timerExpired) {
            timerExpired = true;
            showExpired();
        }
    }

    updateCountdown();
    const timerInterval = setInterval(updateCountdown, 1000);

    // ── Status Polling ──────────────────────────────────────────
    const statusUrl     = @json(route('frontend.handover.status', $transaction));
    const transactionId = @json($transaction->id);
    let   pollInterval;

    function pollStatus() {
        fetch(statusUrl, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            updateConfirmationBadges(data);

            if (data.both_confirmed || data.status === 'completed') {
                clearInterval(pollInterval);
                clearInterval(timerInterval);
                showSuccess();
            }

            if (data.status === 'expired') {
                clearInterval(pollInterval);
                clearInterval(timerInterval);
                showExpired();
            }
        })
        .catch(() => {
            // Silent fail on network errors — polling will retry
        });
    }

    // Poll every 4 seconds
    pollInterval = setInterval(pollStatus, 4000);

    // ── DOM Update Helpers ───────────────────────────────────────
    function updateConfirmationBadges(data) {
        const confirmed = '<span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Confirmed</span>';
        const waiting   = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Waiting</span>';

        document.getElementById('owner-status').innerHTML    = data.owner_confirmed    ? confirmed : waiting;
        document.getElementById('borrower-status').innerHTML = data.borrower_confirmed ? confirmed : waiting;
    }

    function showSuccess() {
        document.getElementById('success-banner').style.setProperty('display', 'block', 'important');
        document.getElementById('expired-banner').style.setProperty('display', 'none', 'important');
    }

    function showExpired() {
        document.getElementById('expired-banner').style.setProperty('display', 'block', 'important');
        document.getElementById('success-banner').style.setProperty('display', 'none', 'important');
        document.getElementById('qrcode').style.opacity = '0.2';
        countdown.textContent = '00:00';
    }
</script>
@endsection