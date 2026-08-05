<?php

declare(strict_types=1);

class RkpsController
{
    private function pdo(): \PDO { return Database::connect(); }
    private function model(): RkpsKps { return new RkpsKps($this->pdo()); }
    private function kpsModel(): Kps { return new Kps($this->pdo()); }
    private function opKabId(): ?int { return user_role() === 'operator' ? user_kabupaten_id() : null; }

    private function handleUpload(int $rkpsId): ?string
    {
        if (!isset($_FILES['rkps_file']) || (int) ($_FILES['rkps_file']['error'] ?? 4) !== UPLOAD_ERR_OK) return null;
        $f = $_FILES['rkps_file'];
        $size = (int) $f['size'];
        if ($size <= 0 || $size > 10 * 1024 * 1024) return null;
        $ext = strtolower(pathinfo((string) $f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) return null;
        $dir = app_path('uploads/rkps/' . $rkpsId);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $name = 'rkps-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file((string) $f['tmp_name'], $dest)) return null;
        return 'uploads/rkps/' . $rkpsId . '/' . $name;
    }

    public function index(): void
    {
        requireLogin();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $kabFilter = (int) ($_GET['kabupaten_id'] ?? 0);
        $kpsFilter = (int) ($_GET['kps_id'] ?? 0);
        $status = trim((string) ($_GET['status'] ?? ''));
        $q = trim((string) ($_GET['q'] ?? ''));

        $filters = [];
        if ($q !== '') $filters['q'] = $q;
        if ($kpsFilter > 0) $filters['kps_id'] = $kpsFilter;
        if (in_array($status, RkpsKps::statusList(), true)) $filters['status'] = $status;

        $opKab = $this->opKabId();
        if ($opKab !== null) $filters['operator_kab_id'] = $opKab;
        elseif ($kabFilter > 0) $filters['kabupaten_id'] = $kabFilter;

        $result = $this->model()->paginateIndex($page, 25, $filters);
        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();

        $pageTitle = 'Data RKPS';
        $activeNav = 'rkps';
        $filterKab = $kabFilter;
        $filterStatus = $status;
        $filterQ = $q;

        ob_start();
        require view_path('rkps/index.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();

        $kpsOptions = $this->kpsModel()->listForSelect(null, $this->opKabId());
        $selectedKpsId = (int) ($_GET['kps_id'] ?? 0);

        $pageTitle = 'Tambah RKPS';
        $activeNav = 'rkps';
        $rkps = null;

        ob_start();
        require view_path('rkps/form.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kpsId = (int) req_str('kps_id');
        $periodeAwal = (int) req_str('periode_awal');
        $periodeAkhir = (int) req_str('periode_akhir');
        $status = req_str('status');

        if ($kpsId <= 0) { set_flash('error', 'KPS wajib dipilih.'); header('Location: ' . APP_URL . '/rkps/create'); exit; }
        if ($periodeAwal < 2020 || $periodeAkhir <= $periodeAwal) { set_flash('error', 'Periode tidak valid.'); header('Location: ' . APP_URL . '/rkps/create'); exit; }
        if (!in_array($status, RkpsKps::statusList(), true)) $status = 'belum';

        if ($this->model()->periodeExists($kpsId, $periodeAwal)) {
            set_flash('error', 'RKPS untuk KPS ini dengan periode awal tersebut sudah ada.');
            header('Location: ' . APP_URL . '/rkps/create');
            exit;
        }

        $id = $this->model()->create([
            'kps_id' => $kpsId,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'status' => $status,
            'catatan' => req_str('catatan') !== '' ? req_str('catatan') : null,
            'dokumen_link' => null,
        ]);

        $doc = $this->handleUpload($id);
        if ($doc !== null) $this->model()->update($id, ['dokumen_link' => $doc]);

        log_activity($pdo, 'rkps', 'create', 'Tambah RKPS ID ' . $id . ' untuk KPS ID ' . $kpsId);
        set_flash('success', 'Data RKPS berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/rkps/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();
        $row = $this->model()->findWithKps($id);
        if ($row === false) { http_response_code(404); echo 'Data tidak ditemukan'; exit; }

        $pageTitle = 'Detail RKPS';
        $activeNav = 'rkps';

        ob_start();
        require view_path('rkps/show.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        $row = $this->model()->findWithKps($id);
        if ($row === false) { http_response_code(404); exit; }

        $kpsOptions = $this->kpsModel()->listForSelect(null, $this->opKabId());
        $selectedKpsId = (int) $row['kps_id'];

        $pageTitle = 'Edit RKPS';
        $activeNav = 'rkps';
        $rkps = $row;

        ob_start();
        require view_path('rkps/form.php');
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

        $kpsId = (int) req_str('kps_id');
        $periodeAwal = (int) req_str('periode_awal');
        $periodeAkhir = (int) req_str('periode_akhir');
        $status = req_str('status');

        if ($kpsId <= 0 || $periodeAwal < 2020 || $periodeAkhir <= $periodeAwal) {
            set_flash('error', 'Data tidak valid.');
            header('Location: ' . APP_URL . '/rkps/' . $id . '/edit');
            exit;
        }
        if (!in_array($status, RkpsKps::statusList(), true)) $status = 'belum';

        if ($this->model()->periodeExists($kpsId, $periodeAwal, $id)) {
            set_flash('error', 'RKPS dengan periode tersebut sudah ada untuk KPS ini.');
            header('Location: ' . APP_URL . '/rkps/' . $id . '/edit');
            exit;
        }

        $data = [
            'kps_id' => $kpsId,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'status' => $status,
            'catatan' => req_str('catatan') !== '' ? req_str('catatan') : null,
        ];

        $doc = $this->handleUpload($id);
        if ($doc !== null) $data['dokumen_link'] = $doc;

        $this->model()->update($id, $data);
        log_activity($pdo, 'rkps', 'update', 'Update RKPS ID ' . $id);
        set_flash('success', 'Data RKPS berhasil diperbarui.');
        header('Location: ' . APP_URL . '/rkps/' . $id);
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
        log_activity($pdo, 'rkps', 'delete', 'Hapus RKPS ID ' . $id);
        set_flash('success', 'Data RKPS berhasil dihapus.');
        header('Location: ' . APP_URL . '/rkps');
        exit;
    }
}
