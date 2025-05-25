@extends('layouts.app')

@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="login-register" style="width: 100%; margin: 0; padding: 0;">
        <div class="otp-container">
            <div class="simple-otp-card">
                <!-- Header Section -->
                <div class="otp-header">
                    <div class="text-center">
                        <h1 class="simple-title">Email Verification</h1>
                        <p class="simple-subtitle">Please check your email and enter the 6-digit code below</p>
                    </div>
                </div>

                <!-- Main Content Section -->
                <div class="otp-content">
                    <!-- Status Messages -->
                    @if (session('status'))
                    <div class="alert alert-success mb-3" role="alert">
                        ✓ {{ session('status') }}
                    </div>
                    @endif

                    @error('otp')
                    <div class="alert alert-danger mb-3" role="alert">
                        ✗ {{ $message }}
                    </div>
                    @enderror

                    <!-- Simple Form -->
                    <form method="POST" action="{{ route('otp.verify') }}" class="otp-form">
                        @csrf

                        <div class="form-group">
                            <label for="otp" class="simple-label">Verification Code:</label>
                            <input id="otp"
                                   type="text"
                                   class="simple-input @error('otp') is-invalid @enderror"
                                   name="otp"
                                   required
                                   autofocus
                                   maxlength="6"
                                   placeholder="Enter 6-digit code">
                            <small class="simple-help">Check your email for the 6-digit code</small>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="simple-verify-btn">
                                Verify Code
                            </button>

                            <div class="resend-section">
                                <p class="resend-text">Didn't receive the email?</p>
                                <a href="{{ route('otp.resend') }}" class="simple-resend-link">
                                    Send New Code
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Section -->
                <div class="otp-footer">
                    <div class="simple-notice">
                        <p><strong>Note:</strong> The code expires in 5 minutes. Check your spam folder if you don't see the email.</p>
                    </div>
                </div>
                </div>
        </div>
    </section>
</main>

<style>
/* Ensure full width layout */
.login-register {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Container for proper centering */
.otp-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    padding: 20px;
    box-sizing: border-box;
}

/* Simple, User-Friendly OTP Styles */
.simple-otp-card {
    background: #ffffff;
    border: 2px solid #ddd;
    border-radius: 0;
    padding: 40px 50px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 600px;
    height: 450px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}

/* Content Sections - Better Centering */
.otp-header {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
}

.otp-content {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.otp-footer {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 24px;
}

/* Clear Typography - Matching System Design */
.simple-title {
    font-family: "Inter", sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--Heading, #111);
    margin-bottom: 8px;
    line-height: 1.2;
}

.simple-subtitle {
    font-family: "Inter", sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: var(--Body-Text, #575864);
    line-height: 20px;
    margin-bottom: 0;
}

/* Form Structure - Perfect Centering */
.otp-form {
    width: 100%;
    max-width: 320px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
    text-align: center;
}

.button-group {
    text-align: center;
}

.resend-section {
    margin-top: 16px;
}

.resend-text {
    font-family: "Inter", sans-serif;
    font-size: 12px;
    font-weight: 400;
    color: var(--Note, #95989D);
    margin: 0 0 8px 0;
    line-height: 15px;
}

/* Simple Form Elements - Matching System Design */
.simple-label {
    font-family: "Inter", sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: var(--Heading, #111);
    margin-bottom: 6px;
    display: block;
    line-height: 20px;
    text-align: center;
}

.simple-input {
    width: 100%;
    height: 48px;
    border: 1px solid var(--Stroke, #ECF0F4);
    border-radius: 0;
    padding: 12px 16px;
    font-family: "Inter", sans-serif;
    font-size: 16px;
    font-weight: 400;
    text-align: center;
    letter-spacing: 2px;
    background: #fff;
    color: var(--Heading, #111);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.simple-input:focus {
    outline: none;
    border-color: var(--Main, #2275fc);
    box-shadow: 0 0 0 3px rgba(34, 117, 252, 0.1);
}

.simple-input.is-invalid {
    border-color: var(--Palette-Red-500, #EF4444);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.simple-input::placeholder {
    color: var(--Text-Holder, #858B93);
    font-weight: 400;
    letter-spacing: 1px;
}

.simple-help {
    font-family: "Inter", sans-serif;
    font-size: 12px;
    font-weight: 400;
    color: var(--Note, #95989D);
    margin-top: 4px;
    display: block;
    line-height: 15px;
    text-align: center;
}

/* Simple Button - Matching System Design */
.simple-verify-btn {
    width: 100%;
    height: 44px;
    background: var(--Main, #2275fc);
    border: 1px solid var(--Main, #2275fc);
    border-radius: 0;
    color: #fff;
    font-family: "Inter", sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 16px;
    transition: all 0.2s ease;
    line-height: 20px;
}

.simple-verify-btn:hover {
    background: var(--hommss-blue-hover, #1a5fd9);
    border-color: var(--hommss-blue-hover, #1a5fd9);
}

.simple-verify-btn:active {
    background: var(--hommss-blue-active, #1554c7);
    border-color: var(--hommss-blue-active, #1554c7);
}

.simple-verify-btn:disabled {
    background: var(--Icon, #CBD5E1);
    border-color: var(--Icon, #CBD5E1);
    cursor: not-allowed;
}

/* Simple Links - Matching System Design */
.simple-resend-link {
    color: var(--Main, #2275fc);
    text-decoration: none;
    font-family: "Inter", sans-serif;
    font-size: 14px;
    font-weight: 600;
    line-height: 20px;
    transition: color 0.2s ease;
}

.simple-resend-link:hover {
    color: var(--hommss-blue-hover, #1a5fd9);
    text-decoration: underline;
}

/* Simple Notice - Matching System Design */
.simple-notice {
    background: var(--bg-table-1, #F7FAFC);
    border: 1px solid var(--Stroke, #ECF0F4);
    border-radius: 0;
    padding: 12px 16px;
    text-align: center;
}

.simple-notice p {
    font-family: "Inter", sans-serif;
    font-size: 12px;
    font-weight: 400;
    color: var(--Note, #95989D);
    margin: 0;
    line-height: 15px;
}

/* Alert Improvements - Matching System Design */
.alert {
    border-radius: 0;
    border: 1px solid;
    padding: 12px 16px;
    font-family: "Inter", sans-serif;
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border-color: var(--22-c-55-e, #22C55E);
    color: var(--22-c-55-e, #22C55E);
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border-color: var(--Palette-Red-500, #EF4444);
    color: var(--Palette-Red-500, #EF4444);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .otp-container {
        min-height: 60vh;
        padding: 15px;
    }

    .simple-otp-card {
        width: calc(100vw - 30px);
        height: calc((100vw - 30px) * 0.75);
        padding: 30px 25px;
        min-height: 400px;
        max-height: 500px;
        max-width: 500px;
    }
}

/* Tablet Responsive */
@media (max-width: 992px) and (min-width: 769px) {
    .otp-container {
        min-height: 65vh;
    }

    .simple-otp-card {
        width: 480px;
        height: 360px;
        padding: 35px 40px;
    }
}

/* Mobile Text Adjustments - Matching System Design */
@media (max-width: 768px) {
    .simple-title {
        font-size: 20px;
    }

    .simple-subtitle {
        font-size: 13px;
    }

    .simple-input {
        height: 44px;
        font-size: 14px;
        letter-spacing: 1.5px;
    }

    .simple-verify-btn {
        height: 40px;
        font-size: 13px;
    }

    .simple-label {
        font-size: 13px;
    }

    .simple-resend-link {
        font-size: 13px;
    }

    .simple-notice p {
        font-size: 11px;
    }

    .alert {
        font-size: 13px;
    }
}

/* High Contrast for Better Readability */
@media (prefers-contrast: high) {
    .simple-input {
        border-width: 4px;
    }

    .simple-title {
        color: #000;
    }

    .simple-verify-btn {
        border: 3px solid #000;
    }
}

/* Large Text for Accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>

<script>
// Simple JavaScript for better usability
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const verifyBtn = document.querySelector('.simple-verify-btn');

    // Only allow numbers in the input
    otpInput.addEventListener('input', function(e) {
        // Remove any non-digit characters
        let value = e.target.value.replace(/\D/g, '');
        // Limit to 6 digits
        if (value.length > 6) {
            value = value.slice(0, 6);
        }
        e.target.value = value;
    });

    // Show loading text when form is submitted
    document.querySelector('form').addEventListener('submit', function() {
        verifyBtn.textContent = 'Verifying...';
        verifyBtn.disabled = true;
    });

    // Focus the input when page loads
    otpInput.focus();
});
</script>
@endsection