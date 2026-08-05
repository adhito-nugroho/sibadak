<?php

declare(strict_types=1);

class KthController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): Kth
    {
        return new Kth($this->pdo());
    }

    private function opKabId(): ?int
    {
        return user_role() === 'operator' ? user_kabupaten_id() : null;
    }

    /** @return string|null */
    private function handleUpload(string $field, int $kthId): ?string
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
        if ($size > 10 * 1024 * 1024) { // 10MB
            return null;
        }

        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $baseDir = app_path('uploads/kth/' . $kthId);
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }
        if (!is_dir($baseDir)) {
            return null;
        }

        $safeBase = match ($field) {
            'link_sk_kth_file' => 'sk-kth',
            'link_sk_kades_file' => 'sk-kades',
            'link_sertifikat_file' => 'sertifikat',
            default => 'dokumen',
        };
        $name = $safeBase . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $name;

        if (!@move_uploaded_file($tmp, $dest)) {
            return null;
        }

        return 'uploads/kth/' . $kthId . '/' . $name;
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $kabFilter = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;
        $kelas = isset($_GET['kelas']) ? trim((string) $_GET['kelas']) : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if (in_array($kelas, ['Pemula', 'Madya', 'Utama'], true)) {
            $filters['kelas'] = $kelas;
        }
        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $filters['operator_kab_id'] = $opKab;
        } elseif ($kabFilter > 0) {
            $filters['kabupaten_id'] = $kabFilter;
        }

        $m = $this->model();
        $result = $m->paginateIndex($page, $perPage, $filters);

        $kabupatenList = $m->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $pageTitle = 'Data KTH';
        $activeNav = 'kth';
        $filterKab = $kabFilter;
        $filterKelas = $kelas;
        $filterQ = $q;

        ob_start();
        require view_path('kth/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();

        $m = $this->model();
        $kabupatenList = $m->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $pageTitle = 'Tambah KTH';
        $activeNav = 'kth';
        $kth = null;
        $kecamatanOptions = [];
        $desaOptions = [];

        ob_start();
        require view_path('kth/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $v = new WilayahValidate($pdo);

        $kab = (int) req_str('kabupaten_id');
        $kec = (int) req_str('kecamatan_id');
        $des = (int) req_str('desa_id');
        $kelas = req_str('kelas');

        if ($this->opKabId() !== null && $kab !== $this->opKabId()) {
            set_flash('error', 'Kabupaten tidak valid untuk akun Anda.');
            header('Location: ' . APP_URL . '/kth/create');
            exit;
        }

        if (!in_array($kelas, ['Pemula', 'Madya', 'Utama'], true)) {
            set_flash('error', 'Kelas tidak valid.');
            header('Location: ' . APP_URL . '/kth/create');
            exit;
        }

        if (!$v->isValidChain($kab, $kec, $des)) {
            set_flash('error', 'Wilayah (kabupaten / kecamatan / desa) tidak konsisten.');
            header('Location: ' . APP_URL . '/kth/create');
            exit;
        }

        $kode = req_str('kode_register');
        $nama = req_str('nama');
        if (strlen($kode) < 3 || strlen($kode) > 60) {
            set_flash('error', 'Kode register wajib 3–60 karakter.');
            header('Location: ' . APP_URL . '/kth/create');
            exit;
        }
        if (strlen($nama) < 2 || strlen($nama) > 150) {
            set_flash('error', 'Nama KTH wajib 2–150 karakter.');
            header('Location: ' . APP_URL . '/kth/create');
            exit;
        }

        $m = $this->model();
        if ($m->kodeRegisterExists($kode)) {
            set_flash('error', 'Kode register sudah digunakan.');
            header('Location: ' . APP_URL . '/kth/create');
            exit;
        }

        $data = [
            'kode_register' => $kode,
            'nama' => $nama,
            'kabupaten_id' => $kab,
            'kecamatan_id' => $kec,
            'desa_id' => $des,
            'dusun_blok' => req_str('dusun_blok') !== '' ? req_str('dusun_blok') : null,
            'kelas' => $kelas,
            'jenis_usaha' => req_str('jenis_usaha') !== '' ? req_str('jenis_usaha') : null,
            'jumlah_anggota' => max(0, (int) req_str('jumlah_anggota')),
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
            'sk_kepala_desa' => req_str('sk_kepala_desa') !== '' ? req_str('sk_kepala_desa') : null,
            'sk_kepala_dinas' => req_str('sk_kepala_dinas') !== '' ? req_str('sk_kepala_dinas') : null,
            'akta_notaris' => req_str('akta_notaris') !== '' ? req_str('akta_notaris') : null,
            'sk_kemenkumham' => req_str('sk_kemenkumham') !== '' ? req_str('sk_kemenkumham') : null,
            'link_sk_kth' => null,
            'link_sk_kades' => null,
            'link_sertifikat' => null,
            'is_active' => 1,
        ];

        $id = $m->create($data);

        $upd = [];
        $skKth = $this->handleUpload('link_sk_kth_file', $id);
        $skKades = $this->handleUpload('link_sk_kades_file', $id);
        $sert = $this->handleUpload('link_sertifikat_file', $id);
        if ($skKth !== null) {
            $upd['link_sk_kth'] = $skKth;
        }
        if ($skKades !== null) {
            $upd['link_sk_kades'] = $skKades;
        }
        if ($sert !== null) {
            $upd['link_sertifikat'] = $sert;
        }
        if ($upd !== []) {
            $m->update($id, $upd);
        }

        log_activity($pdo, 'kth', 'create', 'Tambah KTH: ' . $nama . ' (ID ' . $id . ')');
        set_flash('success', 'KTH berhasil ditambahkan.');

        header('Location: ' . APP_URL . '/kth/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();

        $m = $this->model();
        $row = $m->findWithWilayah($id);
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

        $anggota = $m->listAnggotaByKthId($id);
        $kegiatan = $m->getRelatedActivities($id, (string) $row['nama']);

        $pageTitle = 'Detail KTH';
        $activeNav = 'kth';

        ob_start();
        require view_path('kth/show.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();

        $m = $this->model();
        $row = $m->findWithWilayah($id);
        if ($row === false || (int) $row['is_active'] !== 1) {
            http_response_code(404);
            echo 'Data tidak ditemukan';
            exit;
        }

        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            echo 'Akses ditolak';
            exit;
        }

        $kabupatenList = $m->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $kecamatanOptions = $m->listKecamatanByKabupaten((int) $row['kabupaten_id']);
        $desaOptions = $m->listDesaByKecamatan((int) $row['kecamatan_id']);

        $pageTitle = 'Edit KTH';
        $activeNav = 'kth';
        $kth = $row;

        ob_start();
        require view_path('kth/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $m = $this->model();
        $existing = $m->findById($id);
        if ($existing === false || (int) $existing['is_active'] !== 1) {
            http_response_code(404);
            echo 'Data tidak ditemukan';
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $v = new WilayahValidate($pdo);

        $kab = (int) req_str('kabupaten_id');
        $kec = (int) req_str('kecamatan_id');
        $des = (int) req_str('desa_id');
        $kelas = req_str('kelas');

        if ($this->opKabId() !== null) {
            $kab = (int) $this->opKabId();
        }

        if (!in_array($kelas, ['Pemula', 'Madya', 'Utama'], true)) {
            set_flash('error', 'Kelas tidak valid.');
            header('Location: ' . APP_URL . '/kth/' . $id . '/edit');
            exit;
        }

        if (!$v->isValidChain($kab, $kec, $des)) {
            set_flash('error', 'Wilayah tidak konsisten.');
            header('Location: ' . APP_URL . '/kth/' . $id . '/edit');
            exit;
        }

        $kode = req_str('kode_register');
        $nama = req_str('nama');
        if ($m->kodeRegisterExists($kode, $id)) {
            set_flash('error', 'Kode register sudah dipakai entri lain.');
            header('Location: ' . APP_URL . '/kth/' . $id . '/edit');
            exit;
        }

        $data = [
            'kode_register' => $kode,
            'nama' => $nama,
            'kabupaten_id' => $kab,
            'kecamatan_id' => $kec,
            'desa_id' => $des,
            'dusun_blok' => req_str('dusun_blok') !== '' ? req_str('dusun_blok') : null,
            'kelas' => $kelas,
            'jenis_usaha' => req_str('jenis_usaha') !== '' ? req_str('jenis_usaha') : null,
            'jumlah_anggota' => max(0, (int) req_str('jumlah_anggota')),
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
            'sk_kepala_desa' => req_str('sk_kepala_desa') !== '' ? req_str('sk_kepala_desa') : null,
            'sk_kepala_dinas' => req_str('sk_kepala_dinas') !== '' ? req_str('sk_kepala_dinas') : null,
            'akta_notaris' => req_str('akta_notaris') !== '' ? req_str('akta_notaris') : null,
            'sk_kemenkumham' => req_str('sk_kemenkumham') !== '' ? req_str('sk_kemenkumham') : null,
        ];

        $skKth = $this->handleUpload('link_sk_kth_file', $id);
        $skKades = $this->handleUpload('link_sk_kades_file', $id);
        $sert = $this->handleUpload('link_sertifikat_file', $id);
        if ($skKth !== null) {
            $data['link_sk_kth'] = $skKth;
        }
        if ($skKades !== null) {
            $data['link_sk_kades'] = $skKades;
        }
        if ($sert !== null) {
            $data['link_sertifikat'] = $sert;
        }

        $m->update($id, $data);
        log_activity($pdo, 'kth', 'update', 'Update KTH ID ' . $id . ': ' . $nama);
        set_flash('success', 'Data KTH berhasil diperbarui.');

        header('Location: ' . APP_URL . '/kth/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $m = $this->model();
        $existing = $m->findById($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $m->softDelete($id);
        log_activity($pdo, 'kth', 'delete', 'Nonaktifkan KTH ID ' . $id . ' — ' . ($existing['nama'] ?? ''));
        set_flash('success', 'KTH telah dinonaktifkan.');

        header('Location: ' . APP_URL . '/kth');
        exit;
    }
}
