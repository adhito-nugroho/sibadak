<?php
// Logo resmi: taruh file di static/img/logo-jatim.png (root aplikasi).
// Fallback otomatis ke teks "JATIM" bila gambar gagal dimuat.
$logoFile = app_path('static/img/logo-jatim.png');
$logoUrl = APP_URL . '/static/img/logo-jatim.png';
$heroUrl = is_file(app_path('assets/img/hero-bojonegoro.jpg'))
    ? APP_URL . '/assets/img/hero-bojonegoro.jpg'
    : '';

$renderBrandLogo = static function (string $url): void {
    echo '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        . ' alt="Logo Pemprov Jawa Timur"'
        . ' class="auth-logo-img" decoding="async"'
        . ' onerror="this.hidden=true;this.parentElement.querySelector(\'[data-logo-fallback]\').hidden=false;">';
    echo '<span class="auth-logo-fallback" data-logo-fallback hidden aria-hidden="true">JATIM</span>';
};

$stats = $stats ?? [];
$showPublicStats = isset($showPublicStats)
    ? (bool) $showPublicStats
    : (defined('SHOW_PUBLIC_STATS') ? (bool) SHOW_PUBLIC_STATS : true);
$countKabupaten = isset($stats['kabupaten']) ? (int) $stats['kabupaten'] : null;
$countKth = isset($stats['kth']) ? (int) $stats['kth'] : null;
$countKps = isset($stats['kps']) ? (int) $stats['kps'] : null;

$statDate = null;
$updatedAt = $stats['updated_at'] ?? null;
if (is_string($updatedAt) && $updatedAt !== '') {
    $timestamp = strtotime($updatedAt);
    if ($timestamp !== false) {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $statDate = (int) date('j', $timestamp) . ' '
            . $bulan[(int) date('n', $timestamp)] . ' '
            . date('Y', $timestamp);
    }
}

$formatCount = static function (?int $value): string {
    return $value === null ? '—' : format_id($value);
};

$showFacts = $showPublicStats
    && $countKabupaten !== null
    && $countKth !== null
    && $countKps !== null;

/*
 * Headline panel kiri — pilih satu:
 * 1. "Data kehutanan Bojonegoro, satu pintu."
 * 2. "Data hutan rakyat, tercatat rapi."
 * 3. "Satu data untuk hutan Bojonegoro."
 */
$headline = 'Data kehutanan Bojonegoro, satu pintu.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-name" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars(APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0F3D24">
    <title><?= htmlspecialchars(($pageTitle ?? 'Masuk') . ' — ' . APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php if (is_file($logoFile)): ?>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php else: ?>
    <link rel="icon" href="data:,">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
      :root {
        --auth-green-950: #072A18;
        --auth-green-900: #0F3D24;
        --auth-green-800: #155730;
        --auth-green-700: #1A6B3A;
        --auth-green-100: #E8F5EC;
        --auth-accent: #C9A227;
        --auth-accent-ink: #6B4A00;
        --auth-ink: #14291D;
        --auth-muted: #4A6357;
        --auth-placeholder: #5C6F63;
        --auth-bg: #F3F6F3;
        --auth-surface: #FFFFFF;
        --auth-border: #D6E0D9;
        --auth-input-border: #6F8A7B;
        --auth-focus: #1A6B3A;
        --auth-radius-lg: 20px;
        --auth-radius-md: 12px;
      }

      html { -webkit-text-size-adjust: 100%; }
      [hidden] { display: none !important; }

      .auth-body *,
      .auth-body *::before,
      .auth-body *::after {
        box-sizing: border-box;
      }

      .auth-body {
        margin: 0;
        min-height: 100vh;
        min-height: 100dvh;
        background: var(--auth-bg);
        color: var(--auth-ink);
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
      }

      .auth-shell {
        display: flex;
        min-height: 100vh;
        min-height: 100dvh;
      }

      /* ── Panel branding (desktop) ── */
      .auth-brand-panel {
        display: none;
        position: relative;
        flex: 0 0 40%;
        width: 40%;
        max-width: 40%;
        overflow: hidden;
        background-color: var(--auth-green-950);
      }

      @media (min-width: 900px) {
        .auth-brand-panel { display: block; }
      }

      .auth-brand-photo {
        position: absolute;
        inset: 0;
        background-color: var(--auth-green-900);
        background-image:
          linear-gradient(180deg, rgba(7, 42, 24, 0.92) 0%, rgba(7, 42, 24, 0.84) 45%, rgba(7, 42, 24, 0.96) 100%)<?= $heroUrl !== '' ? ', url("' . htmlspecialchars($heroUrl, ENT_QUOTES, 'UTF-8') . '")' : '' ?>;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
      }

      .auth-brand-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        min-height: 100vh;
        min-height: 100dvh;
        padding: clamp(24px, 4vh, 48px) clamp(28px, 4vw, 56px);
        color: #FFFFFF;
      }

      .auth-brand-head {
        display: flex;
        align-items: center;
        gap: 14px;
      }

      .auth-logo-box {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 52px;
        padding: 7px 10px;
        background: #FFFFFF;
        border-radius: 10px;
        box-shadow: inset 0 0 0 1px rgba(15, 61, 36, 0.08);
        flex: 0 0 auto;
      }

      .auth-logo-img {
        display: block;
        width: auto;
        height: 38px;
        max-width: 124px;
        object-fit: contain;
      }

      .auth-logo-fallback {
        display: inline-block;
        color: var(--auth-green-800);
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        line-height: 1;
      }

      .auth-brand-name {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        line-height: 1.2;
      }

      .auth-brand-sub {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.875rem;
        line-height: 1.4;
      }

      .auth-rule {
        display: block;
        width: 44px;
        height: 3px;
        margin-bottom: 20px;
        background: var(--auth-accent);
        border-radius: 99px;
      }

      .auth-brand-message {
        display: flex;
        flex-direction: column;
        gap: clamp(24px, 4vh, 40px);
        margin-top: clamp(24px, 5vh, 56px);
      }

      .auth-headline {
        margin: 0;
        max-width: 20ch;
        font-size: clamp(1.9rem, 2.3vw, 2.6rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.14;
        color: #FFFFFF;
        text-wrap: balance;
      }

      .auth-desc {
        margin: 18px 0 0;
        max-width: 38ch;
        color: rgba(255, 255, 255, 0.88);
        font-size: 1rem;
        line-height: 1.65;
      }

      .auth-facts {
        display: grid;
        grid-template-columns: 0.65fr 1.2fr 1.2fr;
        gap: 0;
        margin: 0;
        padding: 0;
      }

      .auth-fact {
        display: flex;
        flex-direction: column-reverse;
        justify-content: flex-end;
        min-width: 0;
        margin: 0;
        padding: 0 16px;
        border-left: 1px solid rgba(255, 255, 255, 0.24);
      }

      .auth-fact:first-child {
        padding-left: 0;
        border-left: 0;
      }

      .auth-fact-num {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
      }

      .auth-fact-label {
        margin: 4px 0 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.875rem;
        line-height: 1.35;
      }

      .auth-facts-date {
        margin: 14px 0 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.875rem;
        line-height: 1.4;
      }

      .auth-facts-summary {
        margin: 0;
        max-width: 36ch;
        color: rgba(255, 255, 255, 0.88);
        font-size: 1rem;
        line-height: 1.55;
      }

      .auth-brand-footer {
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, 0.22);
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.875rem;
        line-height: 1.5;
      }

      .auth-brand-footer p { margin: 0; }

      /* ── Kolom form ── */
      .auth-form-panel {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        align-items: center;
        width: 100%;
        min-width: 0;
        padding: 28px 18px 40px;
      }

      @media (min-width: 640px) {
        .auth-form-panel { padding: 40px; }
      }

      @media (min-width: 900px) {
        .auth-form-panel { padding: 48px; }
      }

      .auth-form-wrap {
        width: 100%;
        max-width: 32rem;
        min-width: 0;
        margin-block: auto;
      }

      .auth-mobile-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
      }

      @media (min-width: 900px) {
        .auth-mobile-brand { display: none; }
      }

      .auth-mobile-brand .auth-logo-box {
        min-height: 44px;
        padding: 5px 8px;
      }

      .auth-mobile-brand .auth-logo-img { height: 32px; }

      .auth-mobile-brand-name {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--auth-ink);
      }

      .auth-mobile-brand-sub {
        margin: 2px 0 0;
        font-size: 0.875rem;
        line-height: 1.4;
        color: var(--auth-muted);
      }

      .auth-card {
        width: 100%;
        min-width: 0;
        padding: 26px 22px;
        background: var(--auth-surface);
        border: 1px solid var(--auth-border);
        border-radius: var(--auth-radius-lg);
        box-shadow:
          0 24px 48px -32px rgba(7, 42, 24, 0.45),
          0 2px 8px rgba(7, 42, 24, 0.06);
      }

      @media (min-width: 640px) {
        .auth-card { padding: 40px; }
      }

      .auth-title {
        margin: 0;
        color: var(--auth-ink);
        font-size: clamp(1.55rem, 3.5vw, 2rem);
        font-weight: 700;
        letter-spacing: -0.015em;
        line-height: 1.2;
        text-wrap: balance;
      }

      .auth-subtitle {
        margin: 10px 0 0;
        color: var(--auth-muted);
        font-size: 1rem;
        line-height: 1.55;
      }

      .auth-status { margin: 0; }

      .flash {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: 1px solid;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        box-shadow: 0 1px 2px rgba(7, 42, 24, 0.05);
      }

      .flash__icon {
        flex: 0 0 auto;
        margin-top: 0.125rem;
      }

      .flash__icon svg {
        display: block;
        width: 18px;
        height: 18px;
      }

      .flash__msg { min-width: 0; }

      .flash--success {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #022c22;
      }
      .flash--success .flash__icon { color: #047857; }

      .flash--info {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #172554;
      }
      .flash--info .flash__icon { color: #1d4ed8; }

      .flash--warning {
        border-color: #fde68a;
        background: #fffbeb;
        color: #451a03;
      }
      .flash--warning .flash__icon { color: #b45309; }

      .flash--error {
        border-color: #fecaca;
        background: #fef2f2;
        color: #450a0a;
      }
      .flash--error .flash__icon { color: #b42318; }

      .auth-form {
        display: grid;
        gap: 18px;
        margin-top: 18px;
      }

      .auth-field { min-width: 0; }

      .auth-label {
        display: block;
        margin-bottom: 7px;
        color: var(--auth-ink);
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.35;
      }

      .auth-input-wrap { position: relative; }

      .auth-input-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        width: 20px;
        height: 20px;
        color: var(--auth-muted);
        transform: translateY(-50%);
        pointer-events: none;
      }

      .auth-input-icon svg {
        display: block;
        width: 20px;
        height: 20px;
      }

      .auth-input {
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        min-height: 48px;
        margin: 0;
        padding: 11px 14px;
        background: #FFFFFF;
        border: 1.5px solid var(--auth-input-border);
        border-radius: var(--auth-radius-md);
        color: var(--auth-ink);
        font: inherit;
        font-size: 1rem;
        line-height: 1.4;
        appearance: none;
        transition: border-color 0.15s ease;
      }

      .auth-input::placeholder {
        color: var(--auth-placeholder);
        opacity: 1;
      }

      .auth-input:hover { border-color: var(--auth-green-800); }

      .auth-input:focus {
        outline: 2px solid var(--auth-focus);
        outline-offset: 1px;
        border-color: var(--auth-focus);
      }

      .auth-input--icon { padding-left: 44px; }
      .auth-input--password { padding-right: 54px; }

      .auth-input:-webkit-autofill,
      .auth-input:-webkit-autofill:hover,
      .auth-input:-webkit-autofill:focus {
        -webkit-text-fill-color: var(--auth-ink);
        -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
        caret-color: var(--auth-ink);
        transition: background-color 9999s ease-out 0s;
      }

      .auth-password-toggle {
        position: absolute;
        top: 2px;
        right: 2px;
        bottom: 2px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        margin: 0;
        padding: 0;
        background: transparent;
        border: 0;
        border-radius: 9px;
        color: var(--auth-muted);
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
      }

      .auth-password-toggle:hover {
        background: var(--auth-green-100);
        color: var(--auth-green-800);
      }

      .auth-password-toggle:focus-visible {
        outline: 2px solid var(--auth-focus);
        outline-offset: 2px;
      }

      .auth-password-toggle svg {
        width: 20px;
        height: 20px;
      }

      .auth-caps {
        margin: 8px 0 0;
        color: var(--auth-accent-ink);
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.4;
      }

      .auth-options-wrap { min-width: 0; }

      .auth-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 44px;
      }

      .auth-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        color: var(--auth-ink);
        font-size: 0.875rem;
        line-height: 1.4;
        cursor: pointer;
        user-select: none;
      }

      .auth-checkbox-input {
        flex: 0 0 auto;
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: var(--auth-green-700);
      }

      .auth-checkbox-input:focus-visible {
        outline: 2px solid var(--auth-focus);
        outline-offset: 2px;
      }

      .auth-help-link {
        display: inline-flex;
        align-items: center;
        min-height: 44px;
        margin: 0;
        padding: 0;
        background: none;
        border: 0;
        color: var(--auth-green-800);
        font: inherit;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.4;
        text-decoration: underline;
        text-underline-offset: 3px;
        cursor: pointer;
        white-space: nowrap;
      }

      .auth-help-link:focus-visible {
        outline: 2px solid var(--auth-focus);
        outline-offset: 2px;
        border-radius: 4px;
      }

      .auth-help-message {
        margin: 6px 0 0;
        color: var(--auth-muted);
        font-size: 0.875rem;
        line-height: 1.5;
      }

      .auth-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 50px;
        margin-top: 4px;
        padding: 12px 20px;
        background: var(--auth-green-700);
        border: 1px solid var(--auth-green-700);
        border-radius: var(--auth-radius-md);
        color: #FFFFFF;
        font: inherit;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease;
      }

      .auth-submit:hover {
        background: var(--auth-green-800);
        border-color: var(--auth-green-800);
      }

      .auth-submit:active {
        background: var(--auth-green-900);
        border-color: var(--auth-green-900);
      }

      .auth-submit:focus-visible {
        outline: 2px solid var(--auth-green-950);
        outline-offset: 3px;
      }

      .auth-submit:disabled {
        background: var(--auth-green-800);
        border-color: var(--auth-green-800);
        cursor: progress;
      }

      .auth-spinner {
        flex: 0 0 auto;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, 0.45);
        border-top-color: #FFFFFF;
        border-radius: 50%;
        animation: auth-spin 0.7s linear infinite;
      }

      @keyframes auth-spin {
        to { transform: rotate(360deg); }
      }

      .auth-form-footer {
        display: block;
        margin-top: 18px;
        color: var(--auth-muted);
        font-size: 0.875rem;
        line-height: 1.5;
        text-align: center;
      }

      @media (min-width: 900px) {
        .auth-form-footer { display: none; }
      }

      .fade-up { animation: auth-fade 0.2s ease; }

      @keyframes auth-fade {
        from { opacity: 0; transform: translateY(-2px); }
        to { opacity: 1; transform: none; }
      }

      @media (prefers-reduced-motion: reduce) {
        .auth-body *,
        .auth-body *::before,
        .auth-body *::after {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
          scroll-behavior: auto !important;
        }

        .auth-spinner { display: none !important; }
      }
    </style>
</head>
<body class="auth-body">
    <div class="auth-shell">
        <aside class="auth-brand-panel" aria-label="Tentang SIBADAK">
            <div
                class="auth-brand-photo"
                role="img"
                aria-label="Kegiatan rehabilitasi lahan dan hutan rakyat di wilayah CDK Bojonegoro"></div>

            <div class="auth-brand-content">
                <div class="auth-brand-head">
                    <div class="auth-logo-box"><?php $renderBrandLogo($logoUrl); ?></div>
                    <div>
                        <p class="auth-brand-name"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="auth-brand-sub">CDK Wilayah Bojonegoro</p>
                    </div>
                </div>

                <div class="auth-brand-message">
                    <div>
                        <span class="auth-rule" aria-hidden="true"></span>
                        <p class="auth-headline"><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="auth-desc">
                            Kelola data kelompok tani hutan, perhutanan sosial, dan kegiatan rehabilitasi
                            di wilayah CDK Bojonegoro.
                        </p>
                    </div>

                    <div>
                        <?php if ($showFacts): ?>
                            <dl class="auth-facts">
                                <div class="auth-fact">
                                    <dt class="auth-fact-label">Kabupaten</dt>
                                    <dd class="auth-fact-num"><?= $formatCount($countKabupaten) ?></dd>
                                </div>
                                <div class="auth-fact">
                                    <dt class="auth-fact-label">Kelompok Tani Hutan (KTH)</dt>
                                    <dd class="auth-fact-num"><?= $formatCount($countKth) ?></dd>
                                </div>
                                <div class="auth-fact">
                                    <dt class="auth-fact-label">Kelompok Perhutanan Sosial (KPS)</dt>
                                    <dd class="auth-fact-num"><?= $formatCount($countKps) ?></dd>
                                </div>
                            </dl>
                            <?php if ($statDate !== null): ?>
                                <p class="auth-facts-date">
                                    Data per <?= htmlspecialchars($statDate, ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="auth-facts-summary">
                                Data kehutanan wilayah CDK Bojonegoro tersaji dalam satu sistem.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <footer class="auth-brand-footer">
                    <p>Dinas Kehutanan Provinsi Jawa Timur</p>
                    <p>© <?= date('Y') ?> · Versi 1.0</p>
                </footer>
            </div>
        </aside>

        <main class="auth-form-panel">
            <div class="auth-form-wrap">
                <div class="auth-mobile-brand">
                    <div class="auth-logo-box"><?php $renderBrandLogo($logoUrl); ?></div>
                    <div>
                        <p class="auth-mobile-brand-name"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="auth-mobile-brand-sub">CDK Wilayah Bojonegoro</p>
                    </div>
                </div>

                <div class="auth-card">
                    <?= $content ?>
                </div>

                <p class="auth-form-footer">
                    Dinas Kehutanan Provinsi Jawa Timur · © <?= date('Y') ?> · Versi 1.0
                </p>
            </div>
        </main>
    </div>
</body>
</html>
