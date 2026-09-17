<?php

declare(strict_types=1);

class AepController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): Aep
    {
        return new Aep($this->pdo());
    }

    private function kthModel(): Kth
    {
        return new Kth($this->pdo());
    }

    private function opKabId(): ?int
    {
        return user_role() === 'operator' ? user_kabupaten_id() : null;
    }

    /**
     * Cari kth_id berdasarkan nama persis di kabupaten tertentu.
     */
    private function resolveKthId(string $nama, int $kabupatenId): ?int
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('SELECT id FROM kth WHERE nama = ? AND kabupaten_id = ? AND is_active = 1');
        $stmt->execute([$nama, $kabupatenId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return count($rows) === 1 ? (int) $rows[0] : null;
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $kabFilter = (int) ($_GET['kabupaten_id'] ?? 0);
        $tahun = (int) ($_GET['tahun'] ?? 0);
        $q = trim((string) ($_GET['q'] ?? ''));

        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if (in_array($tahun, Aep::availableYears(), true)) {
            $filters['tahun'] = $tahun;
        }

        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $filters['operator_kab_id'] = $opKab;
        } elseif ($kabFilter > 0) {
            $filters['kabupaten_id'] = $kabFilter;
        }

        $result = $this->model()->paginateIndex($page, $perPage, $filters);
        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($opKab !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $opKab
            ));
        }

        $pageTitle = 'Data AEP';
        $activeNav = 'aep';
        $filterKab = $kabFilter;
        $filterTahun = $tahun;
        $filterQ = $q;
        $yearOptions = Aep::availableYears();

        ob_start();
        require view_path('aep/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();

        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $stmtKth = $this->pdo()->query('SELECT nama FROM kth WHERE is_active = 1 ORDER BY nama');
        $pelaksanaOptions = $stmtKth->fetchAll(\PDO::FETCH_COLUMN);

        $pageTitle = 'Tambah AEP';
        $activeNav = 'aep';
        $aep = null;
        $yearOptions = Aep::availableYears();

        ob_start();
        require view_path('aep/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kab = (int) req_str('kabupaten_id');
        if ($this->opKabId() !== null && $kab !== $this->opKabId()) {
            set_flash('error', 'Kabupaten tidak valid untuk akun Anda.');
            header('Location: ' . APP_URL . '/aep/create');
            exit;
        }

        $namaKth = req_str('nama_kth');
        if ($namaKth === '' || strlen($namaKth) > 200) {
            set_flash('error', 'Nama KTH wajib diisi (maks 200 karakter).');
            header('Location: ' . APP_URL . '/aep/create');
            exit;
        }

        $jenisBantuan = req_str('jenis_bantuan');
        if ($jenisBantuan === '' || strlen($jenisBantuan) > 150) {
            set_flash('error', 'Jenis bantuan wajib diisi (maks 150 karakter).');
            header('Location: ' . APP_URL . '/aep/create');
            exit;
        }

        $tahun = (int) req_str('tahun');
        if (!in_array($tahun, Aep::availableYears(), true)) {
            set_flash('error', 'Tahun tidak valid.');
            header('Location: ' . APP_URL . '/aep/create');
            exit;
        }

        $id = $this->model()->create([
            'kth_id' => $this->resolveKthId($namaKth, $kab),
            'nama_kth' => $namaKth,
            'desa_id' => req_int_null('desa_id'),
            'kabupaten_id' => $kab,
            'jenis_bantuan' => $jenisBantuan,
            'jumlah' => max(1, (int) req_str('jumlah')),
            'tahun' => $tahun,
            'keterangan' => req_str('keterangan') !== '' ? req_str('keterangan') : null,
        ]);

        log_activity($pdo, 'aep', 'create', 'Tambah AEP ID ' . $id . ' - ' . $namaKth . ' (' . $jenisBantuan . ')');
        set_flash('success', 'Data AEP berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/aep/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();
        $row = $this->model()->findWithRelations($id);
        if ($row === false) {
            http_response_code(404);
            echo 'Data tidak ditemukan';
            exit;
        }
        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            echo 'Akses ditolak';
            exit;
        }

        $pageTitle = 'Detail AEP';
        $activeNav = 'aep';

        ob_start();
        require view_path('aep/show.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();

        $row = $this->model()->findWithRelations($id);
        if ($row === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $stmtKth = $this->pdo()->query('SELECT nama FROM kth WHERE is_active = 1 ORDER BY nama');
        $pelaksanaOptions = $stmtKth->fetchAll(\PDO::FETCH_COLUMN);

        $pageTitle = 'Edit AEP';
        $activeNav = 'aep';
        $aep = $row;
        $yearOptions = Aep::availableYears();

        ob_start();
        require view_path('aep/form.php');
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
        if ($existing === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $kab = (int) req_str('kabupaten_id');
        if ($this->opKabId() !== null) {
            $kab = (int) $this->opKabId();
        }

        $namaKth = req_str('nama_kth');
        if ($namaKth === '' || strlen($namaKth) > 200) {
            set_flash('error', 'Nama KTH wajib diisi (maks 200 karakter).');
            header('Location: ' . APP_URL . '/aep/' . $id . '/edit');
            exit;
        }

        $jenisBantuan = req_str('jenis_bantuan');
        if ($jenisBantuan === '' || strlen($jenisBantuan) > 150) {
            set_flash('error', 'Jenis bantuan wajib diisi (maks 150 karakter).');
            header('Location: ' . APP_URL . '/aep/' . $id . '/edit');
            exit;
        }

        $tahun = (int) req_str('tahun');
        if (!in_array($tahun, Aep::availableYears(), true)) {
            set_flash('error', 'Tahun tidak valid.');
            header('Location: ' . APP_URL . '/aep/' . $id . '/edit');
            exit;
        }

        $this->model()->update($id, [
            'kth_id' => $this->resolveKthId($namaKth, $kab),
            'nama_kth' => $namaKth,
            'desa_id' => req_int_null('desa_id'),
            'kabupaten_id' => $kab,
            'jenis_bantuan' => $jenisBantuan,
            'jumlah' => max(1, (int) req_str('jumlah')),
            'tahun' => $tahun,
            'keterangan' => req_str('keterangan') !== '' ? req_str('keterangan') : null,
        ]);

        log_activity($pdo, 'aep', 'update', 'Update AEP ID ' . $id . ' - ' . $namaKth);
        set_flash('success', 'Data AEP berhasil diperbarui.');
        header('Location: ' . APP_URL . '/aep/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $this->model()->delete($id);
        log_activity($pdo, 'aep', 'delete', 'Hapus AEP ID ' . $id);
        set_flash('success', 'Data AEP berhasil dihapus.');
        header('Location: ' . APP_URL . '/aep');
        exit;
    }

    public function bulk(): void
    {
        requireLogin();
        verify_csrf();
        $action = trim((string) ($_POST['action'] ?? ''));
        $ids = bulk_require_ids('/aep', 'AEP');
        $rows = $this->model()->findByIds($ids, $this->opKabId());
        if ($rows === []) {
            bulk_flash_redirect('/aep', 'error', 'Tidak ada data AEP yang cocok.');
        }
        if ($action !== 'export') {
            bulk_flash_redirect('/aep', 'error', 'Aksi massal tidak dikenal.');
        }
        $dataRows = [];
        foreach ($rows as $r) {
            $dataRows[] = [
                (string) ($r['nama_kth'] ?? ''),
                (string) ($r['kabupaten_nama'] ?? ''),
                (string) ($r['jenis_bantuan'] ?? ''),
                (int) ($r['jumlah'] ?? 0),
                (int) ($r['tahun'] ?? 0),
            ];
        }
        bulk_stream_xlsx(
            'Data AEP',
            ['Nama KTH', 'Kabupaten', 'Jenis Bantuan', 'Jumlah', 'Tahun'],
            $dataRows,
            'sibadak-aep',
            $this->pdo(),
            'aep'
        );
    }
}
