<?php

declare(strict_types=1);

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        // Bedakan kunjungan pertama dari sesi yang benar-benar berakhir:
        // cookie sesi yang masih dikirim browser menandakan sesi lama.
        if (!empty($_SESSION) || isset($_COOKIE[session_name()])) {
            set_flash('info', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        }

        header('Location: ' . APP_URL . '/login');
        exit;
    }
}

function requireRole(string ...$roles): void
{
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        http_response_code(403);
        echo 'Akses ditolak';
        exit;
    }
}
