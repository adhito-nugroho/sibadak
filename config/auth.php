<?php

declare(strict_types=1);

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
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
