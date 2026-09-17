<?php

declare(strict_types=1);

class KpsController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): Kps
    {
        return new Kps($this->pdo());
    }

    private function kthModel(): Kth
    {
        return new Kth($this->pdo());
    }

    private function opKabId(): ?int
    {
        return user_role() === 'operator' ? user_kabupaten_id() : null;
    }

    /** @return string|null */
    private function handleUpload(string $field, int $kpsId): ?string
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
        // 10MB batas aman
        if ($size > 10 * 1024 * 1024) {
            return null;
        }

        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $baseDir = app_path('uploads/kps/' . $kpsId);
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }
        if (!is_dir($baseDir)) {
            return null;
        }

        $safeBase = $field === 'rkps_file' ? 'rkps' : 'bukti-sk';
        $name = $safeBase . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $name;

        if (!@move_uploaded_file($tmp, $dest)) {
            return null;
        }

        // Simpan sebagai path relatif yang bisa diakses webserver.
        return 'uploads/kps/' . $kpsId . '/' . $name;
    }

    /** @return array<string, mixed> */
    private function normalizePayload(): array
    {
        $luas = req_str('luas_wilayah_ha');

        return [
            'kth_id' => req_int_null('kth_id'),
            'kabupaten_id' => (int) req_str('kabupaten_id'),
            'kecamatan_id' => (int) req_str('kecamatan_id'),
            'desa_id' => (int) req_str('desa_id'),
            'skema' => req_str('skema'),
            'nama_lembaga' => req_str('nama_lembaga'),
            'no_sk' => req_str('no_sk'),
            'luas_wilayah_ha' => $luas !== '' ? (float) $luas : null,
            'bukti_sk_link' => null, // akan diisi dari upload jika ada
            'nama_pendamping' => req_str('nama_pendamping') !== '' ? req_str('nama_pendamping') : null,
            'jumlah_kk' => max(0, (int) req_str('jumlah_kk')),
            'rkps_link' => null, // akan diisi dari upload jika ada
            'jumlah_kups' => max(0, min(255, (int) req_str('jumlah_kups'))),
            'penandaan_batas_areal' => req_str('penandaan_batas_areal') !== '' ? req_str('penandaan_batas_areal') : null,
            'penandaan_batas_andil' => req_str('penandaan_batas_andil') !== '' ? req_str('penandaan_batas_andil') : null,
        ];
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $kabFilter = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;
        $skema = isset($_GET['skema']) ? trim((string) $_GET['skema']) : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if (in_array($skema, Kps::skemaList(), true)) {
            $filters['skema'] = $skema;
        }
        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $filters['operator_kab_id'] = $opKab;
        } elseif ($kabFilter > 0) {
            $filters['kabupaten_id'] = $kabFilter;
        }

        $result = $this->model()->paginateIndex($page, $perPage, $filters);

        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $pageTitle = 'Data KPS';
        $activeNav = 'kps';
        $filterKab = $kabFilter;
        $filterSkema = $skema;
        $filterQ = $q;

        ob_start();
        require view_path('kps/index.php');
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

        $pageTitle = 'Tambah KPS';
        $activeNav = 'kps';
        $kps = null;
        $kthDisplay = '';
        $kecamatanOptions = [];
        $desaOptions = [];

        ob_start();
        require view_path('kps/form.php');
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
        $payload = $this->normalizePayload();

        $kab = (int) $payload['kabupaten_id'];
        $kec = (int) $payload['kecamatan_id'];
        $des = (int) $payload['desa_id'];
        $kthId = $payload['kth_id'] !== null ? (int) $payload['kth_id'] : null;

        if ($this->opKabId() !== null && $kab !== $this->opKabId()) {
            set_flash('error', 'Kabupaten tidak valid untuk akun Anda.');
            header('Location: ' . APP_URL . '/kps/create');
            exit;
        }

        $skema = (string) $payload['skema'];
        if (!in_array($skema, Kps::skemaList(), true)) {
            set_flash('error', 'Skema tidak valid.');
            header('Location: ' . APP_URL . '/kps/create');
            exit;
        }

        if (!$v->isValidChain($kab, $kec, $des)) {
            set_flash('error', 'Wilayah (kabupaten / kecamatan / desa) tidak konsisten.');
            header('Location: ' . APP_URL . '/kps/create');
            exit;
        }

        if ($kthId !== null && $kthId > 0) {
            $kthRow = $this->kthModel()->findWithWilayah($kthId);
            if ($kthRow === false || (int) $kthRow['is_active'] !== 1) {
                set_flash('error', 'KTH tidak valid.');
                header('Location: ' . APP_URL . '/kps/create');
                exit;
            }
            if ($this->opKabId() !== null && (int) $kthRow['kabupaten_id'] !== $this->opKabId()) {
                set_flash('error', 'KTH tidak boleh diakses untuk akun Anda.');
                header('Location: ' . APP_URL . '/kps/create');
                exit;
            }
        } else {
            $kthId = null;
        }

        $nama = (string) $payload['nama_lembaga'];
        $noSk = (string) $payload['no_sk'];
        if (strlen($nama) < 2 || strlen($nama) > 200) {
            set_flash('error', 'Nama lembaga wajib 2–200 karakter.');
            header('Location: ' . APP_URL . '/kps/create');
            exit;
        }
        if (strlen($noSk) < 2 || strlen($noSk) > 200) {
            set_flash('error', 'Nomor SK wajib 2–200 karakter.');
            header('Location: ' . APP_URL . '/kps/create');
            exit;
        }

        $data = [
            'kth_id' => $kthId,
            'kabupaten_id' => $kab,
            'kecamatan_id' => $kec,
            'desa_id' => $des,
            'skema' => $skema,
            'nama_lembaga' => $nama,
            'no_sk' => $noSk,
            'luas_wilayah_ha' => $payload['luas_wilayah_ha'],
            'bukti_sk_link' => null,
            'nama_pendamping' => $payload['nama_pendamping'],
            'jumlah_kk' => $payload['jumlah_kk'],
            'rkps_link' => null,
            'jumlah_kups' => $payload['jumlah_kups'],
            'penandaan_batas_areal' => $payload['penandaan_batas_areal'],
            'penandaan_batas_andil' => $payload['penandaan_batas_andil'],
        ];

        $m = $this->model();
        $id = $m->create($data);

        // Upload file setelah dapat ID KPS.
        $bukti = $this->handleUpload('bukti_sk_file', $id);
        $rkps = $this->handleUpload('rkps_file', $id);
        $upd = [];
        if ($bukti !== null) {
            $upd['bukti_sk_link'] = $bukti;
        }
        if ($rkps !== null) {
            $upd['rkps_link'] = $rkps;
        }
        if ($upd !== []) {
            $m->update($id, $upd);
        }

        log_activity($pdo, 'kps', 'create', 'Tambah KPS: ' . $nama . ' (ID ' . $id . ')');
        set_flash('success', 'Data KPS berhasil ditambahkan.');

        header('Location: ' . APP_URL . '/kps/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();

        $row = $this->model()->findWithWilayah($id);
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

        $rktModel = new RktKps($this->pdo());
        $rktSummary = $rktModel->summaryByKpsId((int) $row['id']);
        $rktRows = $rktModel->listByKpsId((int) $row['id'], 6);

        // Kegiatan terkait KPS: RHL/KBR/AEP yang nama pelaksananya = nama_lembaga KPS
        $pdo = $this->pdo();
        $namaLembaga = (string) $row['nama_lembaga'];
        $kabId = (int) $row['kabupaten_id'];

        $stmtRhl = $pdo->prepare('SELECT id, nama_kth, kegiatan, tahun, luas_ha FROM rhl WHERE nama_kth = ? AND kabupaten_id = ? ORDER BY tahun DESC, id DESC');
        $stmtRhl->execute([$namaLembaga, $kabId]);
        $relatedRhl = $stmtRhl->fetchAll(\PDO::FETCH_ASSOC);

        $stmtKbr = $pdo->prepare('SELECT id, nama_kth, lokasi, tahun_tanam, subdas FROM kbr WHERE nama_kth = ? ORDER BY tahun_tanam DESC, id DESC');
        $stmtKbr->execute([$namaLembaga]);
        $relatedKbr = $stmtKbr->fetchAll(\PDO::FETCH_ASSOC);

        $stmtAep = $pdo->prepare('SELECT id, nama_kth, jenis_bantuan, jumlah, tahun FROM aep WHERE nama_kth = ? AND kabupaten_id = ? ORDER BY tahun DESC, id DESC');
        $stmtAep->execute([$namaLembaga, $kabId]);
        $relatedAep = $stmtAep->fetchAll(\PDO::FETCH_ASSOC);

        $pageTitle = 'Detail KPS';
        $activeNav = 'kps';

        ob_start();
        require view_path('kps/show.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();

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

        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $kecamatanOptions = $this->kthModel()->listKecamatanByKabupaten((int) $row['kabupaten_id']);
        $desaOptions = $this->kthModel()->listDesaByKecamatan((int) $row['kecamatan_id']);
        $kthDisplay = '';
        if (!empty($row['kth_id'])) {
            $kthRow = $this->kthModel()->findById((int) $row['kth_id']);
            if ($kthRow !== false) {
                $kthDisplay = (string) ($kthRow['nama'] ?? '');
                $kode = (string) ($kthRow['kode_register'] ?? '');
                if ($kode !== '') {
                    $kthDisplay = $kode . ' — ' . $kthDisplay;
                }
            }
        }

        $pageTitle = 'Edit KPS';
        $activeNav = 'kps';
        $kps = $row;

        ob_start();
        require view_path('kps/form.php');
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
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $v = new WilayahValidate($pdo);
        $payload = $this->normalizePayload();

        $kab = (int) $payload['kabupaten_id'];
        $kec = (int) $payload['kecamatan_id'];
        $des = (int) $payload['desa_id'];
        $kthId = $payload['kth_id'] !== null ? (int) $payload['kth_id'] : null;

        if ($this->opKabId() !== null) {
            $kab = (int) $this->opKabId();
        }

        $skema = (string) $payload['skema'];
        if (!in_array($skema, Kps::skemaList(), true)) {
            set_flash('error', 'Skema tidak valid.');
            header('Location: ' . APP_URL . '/kps/' . $id . '/edit');
            exit;
        }

        if (!$v->isValidChain($kab, $kec, $des)) {
            set_flash('error', 'Wilayah tidak konsisten.');
            header('Location: ' . APP_URL . '/kps/' . $id . '/edit');
            exit;
        }

        if ($kthId !== null && $kthId > 0) {
            $kthRow = $this->kthModel()->findWithWilayah($kthId);
            if ($kthRow === false || (int) $kthRow['is_active'] !== 1) {
                set_flash('error', 'KTH tidak valid.');
                header('Location: ' . APP_URL . '/kps/' . $id . '/edit');
                exit;
            }
            if ($this->opKabId() !== null && (int) $kthRow['kabupaten_id'] !== $this->opKabId()) {
                set_flash('error', 'KTH tidak boleh diakses untuk akun Anda.');
                header('Location: ' . APP_URL . '/kps/' . $id . '/edit');
                exit;
            }
        } else {
            $kthId = null;
        }

        $nama = (string) $payload['nama_lembaga'];
        $noSk = (string) $payload['no_sk'];
        if (strlen($nama) < 2 || strlen($nama) > 200 || strlen($noSk) < 2 || strlen($noSk) > 200) {
            set_flash('error', 'Nama lembaga dan nomor SK wajib 2–200 karakter.');
            header('Location: ' . APP_URL . '/kps/' . $id . '/edit');
            exit;
        }

        $data = [
            'kth_id' => $kthId,
            'kabupaten_id' => $kab,
            'kecamatan_id' => $kec,
            'desa_id' => $des,
            'skema' => $skema,
            'nama_lembaga' => $nama,
            'no_sk' => $noSk,
            'luas_wilayah_ha' => $payload['luas_wilayah_ha'],
            'nama_pendamping' => $payload['nama_pendamping'],
            'jumlah_kk' => $payload['jumlah_kk'],
            'jumlah_kups' => $payload['jumlah_kups'],
            'penandaan_batas_areal' => $payload['penandaan_batas_areal'],
            'penandaan_batas_andil' => $payload['penandaan_batas_andil'],
        ];

        // Upload file (opsional). Jika tidak upload, biarkan nilai lama.
        $bukti = $this->handleUpload('bukti_sk_file', $id);
        $rkps = $this->handleUpload('rkps_file', $id);
        if ($bukti !== null) {
            $data['bukti_sk_link'] = $bukti;
        }
        if ($rkps !== null) {
            $data['rkps_link'] = $rkps;
        }

        $m->update($id, $data);
        log_activity($pdo, 'kps', 'update', 'Update KPS ID ' . $id . ': ' . $nama);
        set_flash('success', 'Data KPS berhasil diperbarui.');

        header('Location: ' . APP_URL . '/kps/' . $id);
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

        $nama = (string) ($existing['nama_lembaga'] ?? '');
        $m->delete($id);
        log_activity($pdo, 'kps', 'delete', 'Hapus KPS ID ' . $id . ' — ' . $nama);
        set_flash('success', 'Data KPS telah dihapus (termasuk data RKT terkait jika ada).');

        header('Location: ' . APP_URL . '/kps');
        exit;
    }

    public function bulk(): void
    {
        requireLogin();
        verify_csrf();
        $action = trim((string) ($_POST['action'] ?? ''));
        $ids = bulk_require_ids('/kps', 'KPS');
        $rows = $this->model()->findByIds($ids, $this->opKabId());
        if ($rows === []) {
            bulk_flash_redirect('/kps', 'error', 'Tidak ada data KPS yang cocok.');
        }
        if ($action !== 'export') {
            bulk_flash_redirect('/kps', 'error', 'Aksi massal tidak dikenal.');
        }
        $dataRows = [];
        foreach ($rows as $r) {
            $dataRows[] = [
                (string) ($r['nama_lembaga'] ?? ''),
                (string) ($r['no_sk'] ?? ''),
                (string) ($r['skema'] ?? ''),
                (string) ($r['kabupaten_nama'] ?? ''),
                (string) ($r['desa_nama'] ?? ''),
                $r['luas_wilayah_ha'] !== null ? (float) $r['luas_wilayah_ha'] : '',
                (int) ($r['jumlah_kk'] ?? 0),
            ];
        }
        bulk_stream_xlsx(
            'Data KPS',
            ['Nama lembaga', 'No. SK', 'Skema', 'Kabupaten', 'Desa', 'Luas (Ha)', 'KK'],
            $dataRows,
            'sibadak-kps',
            $this->pdo(),
            'kps'
        );
    }
}
