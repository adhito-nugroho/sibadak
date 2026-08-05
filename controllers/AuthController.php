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

        $pageTitle = 'Masuk';
        require view_path('auth/login.php');
    }

    public function login(): void
    {
        verify_csrf();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            set_flash('error', 'Username dan password wajib diisi.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        try {
            $pdo = Database::connect();
        } catch (\Throwable) {
            set_flash('error', 'Tidak dapat menghubungi database. Periksa konfigurasi .env.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $userModel = new User($pdo);
        $user = $userModel->findActiveByUsername($username);

        if ($user === false || !password_verify($password, $user['password'])) {
            set_flash('error', 'Username atau password salah.');
            header('Location: ' . APP_URL . '/login');
            exit;
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
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        session_start();
        set_flash('success', 'Anda telah keluar.');
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}
