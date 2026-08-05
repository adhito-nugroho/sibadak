<?php

declare(strict_types=1);

class GullyPlugController
{
    private function pdo(): \PDO { return Database::connect(); }
    private function model(): GullyPlug { return new GullyPlug($this->pdo()); }

    public function index(): void
    {
        requireLogin();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $tahun = (int) ($_GET['tahun'] ?? 0);
        $q = trim((string) ($_GET['q'] ?? ''));
        $filters = [];
        if ($q !== '') $filters['q'] = $q;
        if (in_array($tahun, GullyPlug::availableYears(), true)) $filters['tahun'] = $tahun;

        $result = $this->model()->paginateIndex($page, 25, $filters);
        $pageTitle = 'Data Gully Plug';
        $activeNav = 'gully_plug';
        $filterTahun = $tahun;
        $filterQ = $q;
        $yearOptions = GullyPlug::availableYears();

        ob_start();
        require view_path('gully_plug/index.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();
        $pageTitle = 'Tambah Gully Plug';
        $activeNav = 'gully_plug';
        $gully = null;
        $yearOptions = GullyPlug::availableYears();

        ob_start();
        require view_path('gully_plug/form.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();
        $pdo = $this->pdo();

        $lokasi = req_str('lokasi');
        if ($lokasi === '') { set_flash('error', 'Lokasi wajib diisi.'); header('Location: ' . APP_URL . '/gully-plug/create'); exit; }
        $tahun = (int) req_str('tahun');
        if (!in_array($tahun, GullyPlug::availableYears(), true)) { set_flash('error', 'Tahun tidak valid.'); header('Location: ' . APP_URL . '/gully-plug/create'); exit; }

        $id = $this->model()->create([
            'sasaran' => req_str('sasaran') !== '' ? req_str('sasaran') : null,
            'lokasi' => $lokasi,
            'desa_id' => req_int_null('desa_id'),
            'jumlah_unit' => max(1, (int) req_str('jumlah_unit')),
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
            'subdas' => req_str('subdas') !== '' ? req_str('subdas') : null,
            'panjang_m' => req_str('panjang_m') !== '' ? (float) req_str('panjang_m') : null,
            'lebar_m' => req_str('lebar_m') !== '' ? (float) req_str('lebar_m') : null,
            'tinggi_m' => req_str('tinggi_m') !== '' ? (float) req_str('tinggi_m') : null,
            'tahun' => $tahun,
        ]);

        log_activity($pdo, 'gully_plug', 'create', 'Tambah Gully Plug ID ' . $id);
        set_flash('success', 'Data Gully Plug berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/gully-plug/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();
        $row = $this->model()->findWithRelations($id);
        if ($row === false) { http_response_code(404); echo 'Data tidak ditemukan'; exit; }

        $pageTitle = 'Detail Gully Plug';
        $activeNav = 'gully_plug';
        ob_start();
        require view_path('gully_plug/show.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        $row = $this->model()->findWithRelations($id);
        if ($row === false) { http_response_code(404); exit; }

        $pageTitle = 'Edit Gully Plug';
        $activeNav = 'gully_plug';
        $gully = $row;
        $yearOptions = GullyPlug::availableYears();
        ob_start();
        require view_path('gully_plug/form.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();
        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) { http_response_code(404); exit; }

        $lokasi = req_str('lokasi');
        if ($lokasi === '') { set_flash('error', 'Lokasi wajib diisi.'); header('Location: ' . APP_URL . '/gully-plug/' . $id . '/edit'); exit; }
        $tahun = (int) req_str('tahun');

        $this->model()->update($id, [
            'sasaran' => req_str('sasaran') !== '' ? req_str('sasaran') : null,
            'lokasi' => $lokasi,
            'desa_id' => req_int_null('desa_id'),
            'jumlah_unit' => max(1, (int) req_str('jumlah_unit')),
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
            'subdas' => req_str('subdas') !== '' ? req_str('subdas') : null,
            'panjang_m' => req_str('panjang_m') !== '' ? (float) req_str('panjang_m') : null,
            'lebar_m' => req_str('lebar_m') !== '' ? (float) req_str('lebar_m') : null,
            'tinggi_m' => req_str('tinggi_m') !== '' ? (float) req_str('tinggi_m') : null,
            'tahun' => $tahun,
        ]);

        log_activity($pdo, 'gully_plug', 'update', 'Update Gully Plug ID ' . $id);
        set_flash('success', 'Data Gully Plug berhasil diperbarui.');
        header('Location: ' . APP_URL . '/gully-plug/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();
        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) { http_response_code(404); exit; }

        $this->model()->delete($id);
        log_activity($pdo, 'gully_plug', 'delete', 'Hapus Gully Plug ID ' . $id);
        set_flash('success', 'Data Gully Plug berhasil dihapus.');
        header('Location: ' . APP_URL . '/gully-plug');
        exit;
    }
}
