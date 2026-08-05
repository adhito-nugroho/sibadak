<?php

declare(strict_types=1);

class RhlController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): Rhl
    {
        return new Rhl($this->pdo());
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
     * Return null jika tidak ditemukan atau ada lebih dari 1 match.
     */
    private function resolveKthId(string $nama, int $kabupatenId): ?int
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('SELECT id FROM kth WHERE nama = ? AND kabupaten_id = ? AND is_active = 1');
        $stmt->execute([$nama, $kabupatenId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        // Hanya link jika tepat 1 match (hindari ambiguitas)
        return count($rows) === 1 ? (int) $rows[0] : null;
    }

    /** @return string|null */
    private function handleUpload(string $field, int $rhlId): ?string
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return null;
        }
        $f = $_FILES[$field];
        $err = (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($err !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmp = (string) ($f['tmp_name'] ?? '');
        $orig = (string) ($f['name'] ?? '');
        $size = (int) ($f['size'] ?? 0);
        if ($tmp === '' || $orig === '' || $size <= 0) {
            return null;
        }
        if ($size > 10 * 1024 * 1024) { // 10MB limit
            return null;
        }

        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['shp'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $baseDir = app_path('uploads/rhl/' . $rhlId);
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }
        if (!is_dir($baseDir)) {
            return null;
        }

        $name = 'shp-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $name;

        if (!@move_uploaded_file($tmp, $dest)) {
            return null;
        }

        return 'uploads/rhl/' . $rhlId . '/' . $name;
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $kabFilter = (int) ($_GET['kabupaten_id'] ?? 0);
        $tahun = (int) ($_GET['tahun'] ?? 0);
        $kegiatan = trim((string) ($_GET['kegiatan'] ?? ''));
        $sumberDana = trim((string) ($_GET['sumber_dana'] ?? ''));
        $q = trim((string) ($_GET['q'] ?? ''));

        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if (in_array($tahun, Rhl::availableYears(), true)) {
            $filters['tahun'] = $tahun;
        }
        if (in_array($kegiatan, Rhl::kegiatanList(), true)) {
            $filters['kegiatan'] = $kegiatan;
        }
        if (in_array($sumberDana, Rhl::sumberDanaList(), true)) {
            $filters['sumber_dana'] = $sumberDana;
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

        $pageTitle = 'Data RHL';
        $activeNav = 'rhl';
        $filterKab = $kabFilter;
        $filterTahun = $tahun;
        $filterKegiatan = $kegiatan;
        $filterSumberDana = $sumberDana;
        $filterQ = $q;
        $yearOptions = Rhl::availableYears();
        $kegiatanOptions = Rhl::kegiatanList();
        $sumberDanaOptions = Rhl::sumberDanaList();

        ob_start();
        require view_path('rhl/index.php');
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

        $pageTitle = 'Tambah RHL';
        $activeNav = 'rhl';
        $rhl = null;
        $yearOptions = Rhl::availableYears();
        $kegiatanOptions = Rhl::kegiatanList();
        $sumberDanaOptions = Rhl::sumberDanaList();

        $stmtKth = $this->pdo()->query('SELECT nama FROM kth');
        $stmtKps = $this->pdo()->query('SELECT nama_lembaga AS nama FROM kps');
        $pelaksanaOptions = array_unique(array_merge(
            $stmtKth->fetchAll(\PDO::FETCH_COLUMN),
            $stmtKps->fetchAll(\PDO::FETCH_COLUMN)
        ));
        sort($pelaksanaOptions);

        ob_start();
        require view_path('rhl/form.php');
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
            header('Location: ' . APP_URL . '/rhl/create');
            exit;
        }

        $kegiatan = req_str('kegiatan');
        $tahun = (int) req_str('tahun');
        $namaKth = req_str('nama_kth');
        $sumberDana = req_str('sumber_dana');

        if ($namaKth === '' || strlen($namaKth) > 200) {
            set_flash('error', 'Nama KTH/pelaksana wajib diisi (maks 200 karakter).');
            header('Location: ' . APP_URL . '/rhl/create');
            exit;
        }
        if (!in_array($kegiatan, Rhl::kegiatanList(), true)) {
            set_flash('error', 'Kegiatan RHL tidak valid.');
            header('Location: ' . APP_URL . '/rhl/create');
            exit;
        }
        if (!in_array($tahun, Rhl::availableYears(), true)) {
            set_flash('error', 'Tahun tidak valid.');
            header('Location: ' . APP_URL . '/rhl/create');
            exit;
        }
        if (!in_array($sumberDana, Rhl::sumberDanaList(), true)) {
            set_flash('error', 'Sumber dana tidak valid.');
            header('Location: ' . APP_URL . '/rhl/create');
            exit;
        }

        // Validate uploaded SHP file if present
        if (isset($_FILES['shp_file']) && (int) $_FILES['shp_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileError = (int) $_FILES['shp_file']['error'];
            if ($fileError !== UPLOAD_ERR_OK) {
                set_flash('error', 'Gagal mengunggah berkas .shp (error code: ' . $fileError . ').');
                header('Location: ' . APP_URL . '/rhl/create');
                exit;
            }
            $origName = (string) $_FILES['shp_file']['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if ($ext !== 'shp') {
                set_flash('error', 'Format berkas tidak valid. Hanya menerima berkas dengan ekstensi .shp');
                header('Location: ' . APP_URL . '/rhl/create');
                exit;
            }
            $size = (int) $_FILES['shp_file']['size'];
            if ($size > 10 * 1024 * 1024) {
                set_flash('error', 'Ukuran berkas .shp melebihi batas 10 MB.');
                header('Location: ' . APP_URL . '/rhl/create');
                exit;
            }
        }

        $id = $this->model()->create([
            'kth_id' => $this->resolveKthId($namaKth, $kab),
            'nama_kth' => $namaKth,
            'desa_id' => null,
            'kabupaten_id' => $kab,
            'kegiatan' => $kegiatan,
            'luas_ha' => req_str('luas_ha') !== '' ? (float) req_str('luas_ha') : null,
            'tahun' => $tahun,
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
            'sumber_dana' => $sumberDana,
            'shp_file' => null,
        ]);

        $shpFile = $this->handleUpload('shp_file', $id);
        if ($shpFile !== null) {
            $this->model()->update($id, ['shp_file' => $shpFile]);
        }

        log_activity($pdo, 'rhl', 'create', 'Tambah RHL ID ' . $id . ' - ' . $namaKth);
        set_flash('success', 'Data RHL berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/rhl/' . $id);
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

        $pageTitle = 'Detail RHL';
        $activeNav = 'rhl';
        $bibits = $this->model()->getBibit($id);

        ob_start();
        require view_path('rhl/show.php');
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

        $pageTitle = 'Edit RHL';
        $activeNav = 'rhl';
        $rhl = $row;
        $yearOptions = Rhl::availableYears();
        $kegiatanOptions = Rhl::kegiatanList();
        $sumberDanaOptions = Rhl::sumberDanaList();

        $stmtKth = $this->pdo()->query('SELECT nama FROM kth');
        $stmtKps = $this->pdo()->query('SELECT nama_lembaga AS nama FROM kps');
        $pelaksanaOptions = array_unique(array_merge(
            $stmtKth->fetchAll(\PDO::FETCH_COLUMN),
            $stmtKps->fetchAll(\PDO::FETCH_COLUMN)
        ));
        sort($pelaksanaOptions);

        ob_start();
        require view_path('rhl/form.php');
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
        $kegiatan = req_str('kegiatan');
        $tahun = (int) req_str('tahun');
        $namaKth = req_str('nama_kth');
        $sumberDana = req_str('sumber_dana');

        if ($namaKth === '' || strlen($namaKth) > 200) {
            set_flash('error', 'Nama KTH/pelaksana wajib diisi (maks 200 karakter).');
            header('Location: ' . APP_URL . '/rhl/' . $id . '/edit');
            exit;
        }
        if (!in_array($kegiatan, Rhl::kegiatanList(), true) || !in_array($tahun, Rhl::availableYears(), true)) {
            set_flash('error', 'Kegiatan atau tahun tidak valid.');
            header('Location: ' . APP_URL . '/rhl/' . $id . '/edit');
            exit;
        }
        if (!in_array($sumberDana, Rhl::sumberDanaList(), true)) {
            set_flash('error', 'Sumber dana tidak valid.');
            header('Location: ' . APP_URL . '/rhl/' . $id . '/edit');
            exit;
        }

        // Validate uploaded SHP file if present
        if (isset($_FILES['shp_file']) && (int) $_FILES['shp_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileError = (int) $_FILES['shp_file']['error'];
            if ($fileError !== UPLOAD_ERR_OK) {
                set_flash('error', 'Gagal mengunggah berkas .shp (error code: ' . $fileError . ').');
                header('Location: ' . APP_URL . '/rhl/' . $id . '/edit');
                exit;
            }
            $origName = (string) $_FILES['shp_file']['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if ($ext !== 'shp') {
                set_flash('error', 'Format berkas tidak valid. Hanya menerima berkas dengan ekstensi .shp');
                header('Location: ' . APP_URL . '/rhl/' . $id . '/edit');
                exit;
            }
            $size = (int) $_FILES['shp_file']['size'];
            if ($size > 10 * 1024 * 1024) {
                set_flash('error', 'Ukuran berkas .shp melebihi batas 10 MB.');
                header('Location: ' . APP_URL . '/rhl/' . $id . '/edit');
                exit;
            }
        }

        $updateData = [
            'kth_id' => $this->resolveKthId($namaKth, $kab),
            'nama_kth' => $namaKth,
            'kabupaten_id' => $kab,
            'kegiatan' => $kegiatan,
            'luas_ha' => req_str('luas_ha') !== '' ? (float) req_str('luas_ha') : null,
            'tahun' => $tahun,
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
            'sumber_dana' => $sumberDana,
        ];

        $shpFile = $this->handleUpload('shp_file', $id);
        if ($shpFile !== null) {
            $updateData['shp_file'] = $shpFile;
        }

        $this->model()->update($id, $updateData);

        log_activity($pdo, 'rhl', 'update', 'Update RHL ID ' . $id . ' - ' . $namaKth);
        set_flash('success', 'Data RHL berhasil diperbarui.');
        header('Location: ' . APP_URL . '/rhl/' . $id);
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
        log_activity($pdo, 'rhl', 'delete', 'Hapus RHL ID ' . $id);
        set_flash('success', 'Data RHL berhasil dihapus.');
        header('Location: ' . APP_URL . '/rhl');
        exit;
    }

    public function storeBibit(int $rhlId): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $rhl = $this->model()->findById($rhlId);
        if ($rhl === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $rhl['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $jenis = req_str('jenis_bibit');
        $jumlah = (int) req_str('jumlah_btg');

        if ($jenis === '' || $jumlah <= 0) {
            set_flash('error', 'Jenis bibit dan jumlah batang tidak valid.');
            header('Location: ' . APP_URL . '/rhl/' . $rhlId);
            exit;
        }

        $this->model()->addBibit($rhlId, $jenis, $jumlah);
        log_activity($pdo, 'rhl_bibit', 'create', 'Tambah bibit ' . $jenis . ' ke RHL ID ' . $rhlId);
        set_flash('success', 'Bibit berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/rhl/' . $rhlId);
        exit;
    }

    public function deleteBibit(int $bibitId): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $bibit = $this->model()->getBibitById($bibitId);
        if ($bibit === false) {
            http_response_code(404);
            exit;
        }
        
        $rhlId = (int) $bibit['rhl_id'];
        $rhl = $this->model()->findById($rhlId);
        if ($this->opKabId() !== null && (int) $rhl['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $this->model()->deleteBibit($bibitId);
        log_activity($pdo, 'rhl_bibit', 'delete', 'Hapus bibit ID ' . $bibitId . ' dari RHL ID ' . $rhlId);
        set_flash('success', 'Bibit berhasil dihapus.');
        header('Location: ' . APP_URL . '/rhl/' . $rhlId);
        exit;
    }
}
