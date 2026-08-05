<?php

declare(strict_types=1);

class PenyuluhKehutananController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): PenyuluhKehutanan
    {
        return new PenyuluhKehutanan($this->pdo());
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filterQ = trim((string) ($_GET['q'] ?? ''));
        $filterStatus = trim((string) ($_GET['status'] ?? ''));
        $result = $this->model()->paginateIndex($page, 25, ['q' => $filterQ, 'status' => $filterStatus]);

        $pageTitle = 'Data Penyuluh Kehutanan';
        $activeNav = 'penyuluh';

        ob_start();
        require view_path('penyuluh/index.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();

        $pageTitle = 'Tambah Penyuluh';
        $activeNav = 'penyuluh';
        $row = null;

        ob_start();
        require view_path('penyuluh/form.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $data = $this->validatedData(APP_URL . '/penyuluh/create');
        $id = $this->model()->create($data);
        log_activity($this->pdo(), 'penyuluh', 'create', 'Tambah penyuluh ID ' . $id . ' - ' . $data['nama']);
        set_flash('success', 'Data penyuluh berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/penyuluh');
        exit;
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();

        $row = $this->model()->findById($id);
        if ($row === false) {
            http_response_code(404);
            exit;
        }

        $pageTitle = 'Edit Penyuluh';
        $activeNav = 'penyuluh';

        ob_start();
        require view_path('penyuluh/form.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        if ($this->model()->findById($id) === false) {
            http_response_code(404);
            exit;
        }
        $data = $this->validatedData(APP_URL . '/penyuluh/' . $id . '/edit');
        $this->model()->update($id, $data);
        log_activity($this->pdo(), 'penyuluh', 'update', 'Update penyuluh ID ' . $id . ' - ' . $data['nama']);
        set_flash('success', 'Data penyuluh berhasil diperbarui.');
        header('Location: ' . APP_URL . '/penyuluh');
        exit;
    }

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $this->model()->update($id, ['is_active' => 0]);
        log_activity($this->pdo(), 'penyuluh', 'delete', 'Nonaktifkan penyuluh ID ' . $id);
        set_flash('success', 'Data penyuluh dinonaktifkan.');
        header('Location: ' . APP_URL . '/penyuluh');
        exit;
    }

    /** @return array{nip:string,nama:string,pangkat:string,jabatan:string,is_active:int} */
    private function validatedData(string $backUrl): array
    {
        $nip = preg_replace('/\s+/', '', req_str('nip'));
        $nama = req_str('nama');
        $pangkat = req_str('pangkat');
        $jabatan = req_str('jabatan');
        if ($nip === '' || strlen($nip) > 32 || $nama === '' || strlen($nama) > 150 || $pangkat === '' || strlen($pangkat) > 100 || $jabatan === '' || strlen($jabatan) > 255) {
            set_flash('error', 'NIP, nama, pangkat, dan jabatan wajib diisi dengan panjang valid.');
            header('Location: ' . $backUrl);
            exit;
        }

        return [
            'nip' => $nip,
            'nama' => $nama,
            'pangkat' => $pangkat,
            'jabatan' => $jabatan,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
