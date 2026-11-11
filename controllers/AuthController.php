<?php

declare(strict_types=1);

class AuthController
{
    public function loginForm(): void
    {
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/');
            exit;
        }

        $showPublicStats = defined('SHOW_PUBLIC_STATS') ? (bool) SHOW_PUBLIC_STATS : true;

        // Angka ringkas untuk panel branding. Gagal query tidak boleh
        // menghalangi halaman login tampil.
        $stats = [
            'kabupaten' => null,
            'kth' => null,
            'kps' => null,
            'updated_at' => null,
        ];

        if ($showPublicStats) {
            try {
                $pdo = Database::connect();
                $row = $pdo->query(
                    "SELECT
                        (SELECT COUNT(*) FROM kabupaten) AS kabupaten,
                        (SELECT COUNT(*) FROM kth WHERE is_active = 1) AS kth,
                        (SELECT COUNT(*) FROM kps) AS kps,
                        GREATEST(
                            (SELECT COALESCE(MAX(updated_at), '1970-01-01') FROM kth WHERE is_active = 1),
                            (SELECT COALESCE(MAX(updated_at), '1970-01-01') FROM kps)
                        ) AS updated_at"
                )->fetch();

                if (is_array($row)) {
                    $updatedAt = (string) ($row['updated_at'] ?? '');
                    $stats = [
                        'kabupaten' => (int) ($row['kabupaten'] ?? 0),
                        'kth' => (int) ($row['kth'] ?? 0),
                        'kps' => (int) ($row['kps'] ?? 0),
                        'updated_at' => $updatedAt !== '' && $updatedAt !== '1970-01-01 00:00:00' ? $updatedAt : null,
                    ];
                }
            } catch (\Throwable) {
                // Statistik hanya pelengkap; biarkan nilai kosong.
            }
        }

        $pageTitle = 'Masuk';
        require view_path('auth/login.php');
    }

    public function login(): void
    {
        verify_csrf();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $rateKey = substr(strtolower($username), 0, 60);

        try {
            $pdo = Database::connect();
        } catch (\Throwable) {
            set_flash('error', 'Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $limiter = null;
        try {
            $limiter = new LoginAttempt($pdo);
            if ($limiter->countRecent($ipAddress, $rateKey, 60) >= 5) {
                set_flash('error', 'Terlalu banyak percobaan masuk. Coba lagi dalam 1 menit.');
                header('Location: ' . APP_URL . '/login');
                exit;
            }
        } catch (\Throwable) {
            // Tabel rate limit belum tersedia: jangan blokir login.
            $limiter = null;
        }

        if ($username === '' || $password === '') {
            if ($limiter !== null) {
                try {
                    $limiter->record($ipAddress, $rateKey);
                } catch (\Throwable) {
                }
            }

            set_flash('error', 'Username dan password wajib diisi.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        try {
            $userModel = new User($pdo);
            $user = $userModel->findActiveByUsername($username);
        } catch (\Throwable) {
            set_flash('error', 'Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if ($user === false || !password_verify($password, $user['password'])) {
            if ($limiter !== null) {
                try {
                    $limiter->record($ipAddress, $rateKey);
                } catch (\Throwable) {
                }
            }

            set_flash('error', 'Username atau password salah.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if ($limiter !== null) {
            try {
                $limiter->clear($ipAddress, $rateKey);
            } catch (\Throwable) {
            }
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role_nama'];
        $_SESSION['role_id'] = (int) $user['role_id'];
        $_SESSION['kabupaten_id'] = $user['kabupaten_id'] !== null ? (int) $user['kabupaten_id'] : null;

        $userModel->touchLastLogin((int) $user['id']);

        set_flash('success', 'Selamat datang, ' . $user['nama'] . '.');
        header('Location: ' . APP_URL . '/');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $p['path'] ?? '/',
                'domain' => $p['domain'] ?? '',
                'secure' => (bool) ($p['secure'] ?? false),
                'httponly' => (bool) ($p['httponly'] ?? true),
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        session_start();
        set_flash('success', 'Anda telah keluar.');
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}
