<?php
ob_start();
$flashData = consume_flash();
?>
<div class="auth-card-header">
    <h1 class="auth-title">Masuk ke akun Anda</h1>
    <p class="auth-subtitle">Masukkan username dan password akun SIBADAK Anda.</p>
</div>

<form
    id="login-form"
    class="auth-form"
    method="post"
    action="<?= htmlspecialchars(APP_URL . '/login', ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div
        id="login-status"
        class="auth-status"
        aria-live="polite"
        aria-atomic="true"
        <?= $flashData === null ? 'hidden' : '' ?>>
        <?php if ($flashData !== null) {
            require view_path('partials/flash.php');
        } ?>
    </div>

    <div class="auth-field">
        <label for="username" class="auth-label">Username</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M20 21a8 8 0 0 0-16 0"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </span>
            <input
                type="text"
                name="username"
                id="username"
                class="auth-input auth-input--icon"
                autocomplete="username"
                autocapitalize="none"
                spellcheck="false"
                enterkeyhint="next"
                placeholder="Masukkan username"
                aria-describedby="login-status"
                required>
        </div>
    </div>

    <div class="auth-field">
        <label for="password" class="auth-label">Password</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                </svg>
            </span>
            <input
                type="password"
                name="password"
                id="password"
                class="auth-input auth-input--icon auth-input--password"
                autocomplete="current-password"
                enterkeyhint="go"
                placeholder="Masukkan sandi"
                aria-describedby="login-status caps-lock"
                required>
            <button
                type="button"
                id="password-toggle"
                class="auth-password-toggle"
                aria-controls="password"
                aria-pressed="false"
                aria-label="Tampilkan password">
                <svg class="auth-eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="2.5"/>
                </svg>
                <svg class="auth-eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" hidden>
                    <path d="m3 3 18 18"/>
                    <path d="M10.6 6.2A9.8 9.8 0 0 1 12 6c6.5 0 10 6 10 6a17.4 17.4 0 0 1-3.2 3.8"/>
                    <path d="M6.6 6.6C3.6 8.7 2 12 2 12s3.5 6 10 6a10.8 10.8 0 0 0 4.2-.8"/>
                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                </svg>
            </button>
        </div>

        <p id="caps-lock" class="auth-caps" role="status" aria-live="polite" hidden>
            Caps Lock aktif
        </p>
    </div>

    <div class="auth-options-wrap">
        <div class="auth-options">
            <label class="auth-checkbox" for="remember">
                <input type="checkbox" name="remember" id="remember" value="1" class="auth-checkbox-input">
                <span>Ingat saya</span>
            </label>

            <button
                type="button"
                id="forgot-password-toggle"
                class="auth-help-link"
                aria-expanded="false"
                aria-controls="forgot-password-message">
                Lupa password?
            </button>
        </div>

        <p id="forgot-password-message" class="auth-help-message" role="status" hidden>
            Hubungi admin CDK Wilayah Bojonegoro untuk mereset password Anda.
        </p>
    </div>

    <button type="submit" id="login-submit" class="auth-submit">
        <span class="auth-spinner" aria-hidden="true" hidden></span>
        <span class="auth-submit-label">Masuk</span>
    </button>
</form>

<script>
(function () {
    'use strict';

    var form = document.getElementById('login-form');
    var submit = document.getElementById('login-submit');
    var submitLabel = submit ? submit.querySelector('.auth-submit-label') : null;
    var spinner = submit ? submit.querySelector('.auth-spinner') : null;
    var defaultLabel = submitLabel ? submitLabel.textContent : 'Masuk';
    var submitting = false;

    function setLoading(loading) {
        if (!submit || !submitLabel || !spinner) return;
        submit.disabled = loading;
        submit.setAttribute('aria-busy', loading ? 'true' : 'false');
        submitLabel.textContent = loading ? 'Memproses...' : defaultLabel;
        spinner.hidden = !loading;
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            if (submitting) {
                event.preventDefault();
                return;
            }
            submitting = true;
            setLoading(true);
        });
    }

    window.addEventListener('pageshow', function () {
        submitting = false;
        setLoading(false);
    });

    var password = document.getElementById('password');
    var toggle = document.getElementById('password-toggle');
    var eyeOpen = toggle ? toggle.querySelector('.auth-eye-open') : null;
    var eyeClosed = toggle ? toggle.querySelector('.auth-eye-closed') : null;

    if (password && toggle && eyeOpen && eyeClosed) {
        toggle.addEventListener('click', function () {
            var show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            eyeOpen.hidden = show;
            eyeClosed.hidden = !show;
            password.focus({ preventScroll: true });
        });
    }

    var caps = document.getElementById('caps-lock');
    if (password && caps) {
        var updateCaps = function (event) {
            if (typeof event.getModifierState !== 'function') return;
            caps.hidden = !event.getModifierState('CapsLock');
        };

        password.addEventListener('keydown', updateCaps);
        password.addEventListener('keyup', updateCaps);
        password.addEventListener('blur', function () {
            caps.hidden = true;
        });
    }

    var forgotToggle = document.getElementById('forgot-password-toggle');
    var forgotMessage = document.getElementById('forgot-password-message');
    if (forgotToggle && forgotMessage) {
        forgotToggle.addEventListener('click', function () {
            var isOpen = forgotToggle.getAttribute('aria-expanded') === 'true';
            forgotToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            forgotMessage.hidden = isOpen;
        });
    }
}());
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Masuk';
require view_path('layouts/auth.php');
