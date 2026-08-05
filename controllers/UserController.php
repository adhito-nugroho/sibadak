<?php

declare(strict_types=1);

class UserController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): User
    {
        return new User($this->pdo());
    }

    public function index(): void
    {
        requireLogin();
        requireRole('admin');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $q = trim((string) ($_GET['q'] ?? ''));
        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }

        $result = $this->model()->paginateIndex($page, 25, $filters);

        $pageTitle = 'Manajemen Pengguna';
        $activeNav = 'users';
        $filterQ = $q;

        ob_start();
        require view_path('users/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        requireRole('admin');

        $m = $this->model();
        $roles = $m->listRoles();
        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();

        $pageTitle = 'Tambah Pengguna';
        $activeNav = 'users';
        $user = null;

        ob_start();
        require view_path('users/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        requireRole('admin');
        verify_csrf();

        $pdo = $this->pdo();
        $m = $this->model();

        $nama = req_str('nama');
        $username = req_str('username');
        $email = req_str('email');
        $roleId = (int) req_str('role_id');
        $kabId = req_int_null('kabupaten_id');

        if ($nama === '' || $username === '' || $email === '') {
            set_flash('error', 'Nama, username, dan email wajib diisi.');
            header('Location: ' . APP_URL . '/users/create');
            exit;
        }

        if ($m->usernameExists($username)) {
            set_flash('error', 'Username sudah digunakan.');
            header('Location: ' . APP_URL . '/users/create');
            exit;
        }

        if ($m->emailExists($email)) {
            set_flash('error', 'Email sudah digunakan.');
            header('Location: ' . APP_URL . '/users/create');
            exit;
        }

        $passwordRaw = req_str('password');
        if ($passwordRaw === '') {
            $passwordRaw = User::generatePassword();
        }
        $hash = password_hash($passwordRaw, PASSWORD_BCRYPT, ['cost' => 12]);

        $id = $m->create([
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'password' => $hash,
            'role_id' => $roleId,
            'kabupaten_id' => $kabId,
            'is_active' => 1,
        ]);

        log_activity($pdo, 'user', 'create', 'Tambah user: ' . $username . ' (ID ' . $id . ')');
        set_flash('success', 'Pengguna berhasil ditambahkan. Password: ' . $passwordRaw);
        header('Location: ' . APP_URL . '/users');
        exit;
    }

    public function edit(int $id): void
    {
        requireLogin();
        requireRole('admin');

        $m = $this->model();
        $user = $m->findById($id);
        if ($user === false) {
            http_response_code(404);
            echo 'Pengguna tidak ditemukan';
            exit;
        }

        $roles = $m->listRoles();
        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();

        $pageTitle = 'Edit Pengguna';
        $activeNav = 'users';

        ob_start();
        require view_path('users/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin();
        requireRole('admin');
        verify_csrf();

        $pdo = $this->pdo();
        $m = $this->model();

        $existing = $m->findById($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        $nama = req_str('nama');
        $username = req_str('username');
        $email = req_str('email');
        $roleId = (int) req_str('role_id');
        $kabId = req_int_null('kabupaten_id');

        if ($nama === '' || $username === '' || $email === '') {
            set_flash('error', 'Nama, username, dan email wajib diisi.');
            header('Location: ' . APP_URL . '/users/' . $id . '/edit');
            exit;
        }

        if ($m->usernameExists($username, $id)) {
            set_flash('error', 'Username sudah digunakan.');
            header('Location: ' . APP_URL . '/users/' . $id . '/edit');
            exit;
        }

        if ($m->emailExists($email, $id)) {
            set_flash('error', 'Email sudah digunakan.');
            header('Location: ' . APP_URL . '/users/' . $id . '/edit');
            exit;
        }

        $m->update($id, [
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'role_id' => $roleId,
            'kabupaten_id' => $kabId,
        ]);

        log_activity($pdo, 'user', 'update', 'Update user ID ' . $id . ': ' . $username);
        set_flash('success', 'Data pengguna berhasil diperbarui.');
        header('Location: ' . APP_URL . '/users');
        exit;
    }

    public function resetPassword(int $id): void
    {
        requireLogin();
        requireRole('admin');
        verify_csrf();

        $pdo = $this->pdo();
        $m = $this->model();

        $existing = $m->findById($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        $newPw = User::generatePassword();
        $hash = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
        $m->update($id, ['password' => $hash]);

        log_activity($pdo, 'user', 'reset_password', 'Reset password user ID ' . $id . ': ' . ($existing['username'] ?? ''));
        set_flash('success', 'Password berhasil direset. Password baru: ' . $newPw);
        header('Location: ' . APP_URL . '/users');
        exit;
    }

    public function toggleActive(int $id): void
    {
        requireLogin();
        requireRole('admin');
        verify_csrf();

        $pdo = $this->pdo();
        $m = $this->model();

        $existing = $m->findById($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        // Prevent admin from deactivating themselves
        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            set_flash('error', 'Tidak dapat menonaktifkan akun sendiri.');
            header('Location: ' . APP_URL . '/users');
            exit;
        }

        $newState = $m->toggleActive($id);
        $label = $newState ? 'diaktifkan' : 'dinonaktifkan';

        log_activity($pdo, 'user', 'toggle_active', 'User ID ' . $id . ' ' . $label);
        set_flash('success', 'Pengguna berhasil ' . $label . '.');
        header('Location: ' . APP_URL . '/users');
        exit;
    }
}
