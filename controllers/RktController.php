<?php

declare(strict_types=1);

class RktController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): RktKps
    {
        return new RktKps($this->pdo());
    }

    private function kpsModel(): Kps
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
    private function handleUpload(string $field, int $rktId): ?string
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

        $baseDir = app_path('uploads/rkt/' . $rktId);
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }
        if (!is_dir($baseDir)) {
            return null;
        }

        $name = 'rkt-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($tmp, $dest)) {
            return null;
        }

        return 'uploads/rkt/' . $rktId . '/' . $name;
    }

    /** @return array<string,mixed>|false */
    private function assertKpsAccessible(int $kpsId): array|false
    {
        $row = $this->kpsModel()->findWithWilayah($kpsId);
        if ($row === false) {
            return false;
        }
        $op = $this->opKabId();
        if ($op !== null && (int) $row['kabupaten_id'] !== $op) {
            return false;
        }

        return $row;
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $kabFilter = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;
        $kpsFilter = isset($_GET['kps_id']) ? (int) $_GET['kps_id'] : 0;
        $tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;
        $status = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if ($kpsFilter > 0) {
            $filters['kps_id'] = $kpsFilter;
        }
        if (in_array($tahun, RktKps::availableYears(), true)) {
            $filters['tahun'] = $tahun;
        }
        if (in_array($status, RktKps::statusList(), true)) {
            $filters['status'] = $status;
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
            $kabupatenList = array_values(array_filter($kabupatenList, fn (array $r): bool => (int) $r['id'] === $opKab));
        }

        $kpsOptions = $this->kpsModel()->listForSelect($kabFilter > 0 ? $kabFilter : null, $opKab);
        $selectedKpsName = null;
        if ($kpsFilter > 0) {
            foreach ($kpsOptions as $opt) {
                if ((int) $opt['id'] === $kpsFilter) {
                    $selectedKpsName = (string) $opt['nama_lembaga'];
                    break;
                }
            }
        }

        $pageTitle = 'RKT per KPS';
        $activeNav = 'rkt';
        $filterKab = $kabFilter;
        $filterKps = $kpsFilter;
        $filterTahun = $tahun;
        $filterStatus = $status;
        $filterQ = $q;
        $yearOptions = RktKps::availableYears();

        ob_start();
        require view_path('rkt/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();

        $preKps = isset($_GET['kps_id']) ? (int) $_GET['kps_id'] : 0;
        if ($preKps > 0 && $this->assertKpsAccessible($preKps) === false) {
            $preKps = 0;
        }

        $kpsOptions = $this->kpsModel()->listForSelect(null, $this->opKabId());

        $pageTitle = 'Tambah RKT';
        $activeNav = 'rkt';
        $rkt = null;
        $selectedKpsId = $preKps;
        $yearOptions = RktKps::availableYears();

        ob_start();
        require view_path('rkt/form.php');
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
        $tahun = (int) req_str('tahun');
        $status = req_str('status');
        $catatan = req_str('catatan') !== '' ? req_str('catatan') : null;

        if ($kpsId <= 0 || $this->assertKpsAccessible($kpsId) === false) {
            set_flash('error', 'KPS tidak valid atau tidak boleh diakses.');
            header('Location: ' . APP_URL . '/rkt/create');
            exit;
        }

        if (!in_array($tahun, RktKps::availableYears(), true)) {
            set_flash('error', 'Tahun RKT tidak valid.');
            header('Location: ' . APP_URL . '/rkt/create');
            exit;
        }

        if (!in_array($status, RktKps::statusList(), true)) {
            set_flash('error', 'Status RKT tidak valid.');
            header('Location: ' . APP_URL . '/rkt/create');
            exit;
        }

        $m = $this->model();
        if ($m->uniqueYearExists($kpsId, $tahun)) {
            set_flash('error', 'Tahun RKT untuk KPS ini sudah ada.');
            header('Location: ' . APP_URL . '/rkt/create?kps_id=' . $kpsId);
            exit;
        }

        $id = $m->create([
            'kps_id' => $kpsId,
            'tahun' => $tahun,
            'status' => $status,
            'catatan' => $catatan,
            'dokumen_link' => null,
        ]);

        $doc = $this->handleUpload('rkt_file', $id);
        if ($doc !== null) {
            $m->update($id, ['dokumen_link' => $doc]);
        }

        log_activity($pdo, 'rkt', 'create', 'Tambah RKT KPS ID ' . $kpsId . ' tahun ' . $tahun);
        set_flash('success', 'RKT berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/rkt/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();

        $row = $this->model()->findWithKps($id);
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

        $pageTitle = 'Detail RKT';
        $activeNav = 'rkt';

        ob_start();
        require view_path('rkt/show.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();

        $row = $this->model()->findWithKps($id);
        if ($row === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $kpsOptions = $this->kpsModel()->listForSelect(null, $this->opKabId());

        $pageTitle = 'Edit RKT';
        $activeNav = 'rkt';
        $rkt = $row;
        $selectedKpsId = (int) $row['kps_id'];
        $yearOptions = RktKps::availableYears();

        ob_start();
        require view_path('rkt/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $existing = $this->model()->findWithKps($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $kpsId = (int) req_str('kps_id');
        $tahun = (int) req_str('tahun');
        $status = req_str('status');
        $catatan = req_str('catatan') !== '' ? req_str('catatan') : null;

        if ($kpsId <= 0 || $this->assertKpsAccessible($kpsId) === false) {
            set_flash('error', 'KPS tidak valid.');
            header('Location: ' . APP_URL . '/rkt/' . $id . '/edit');
            exit;
        }

        if (!in_array($tahun, RktKps::availableYears(), true) || !in_array($status, RktKps::statusList(), true)) {
            set_flash('error', 'Tahun atau status tidak valid.');
            header('Location: ' . APP_URL . '/rkt/' . $id . '/edit');
            exit;
        }

        $m = $this->model();
        if ($m->uniqueYearExists($kpsId, $tahun, $id)) {
            set_flash('error', 'Tahun RKT untuk KPS ini sudah ada.');
            header('Location: ' . APP_URL . '/rkt/' . $id . '/edit');
            exit;
        }

        $data = [
            'kps_id' => $kpsId,
            'tahun' => $tahun,
            'status' => $status,
            'catatan' => $catatan,
        ];

        $doc = $this->handleUpload('rkt_file', $id);
        if ($doc !== null) {
            $data['dokumen_link'] = $doc;
        }

        $m->update($id, $data);

        log_activity($pdo, 'rkt', 'update', 'Update RKT ID ' . $id . ' tahun ' . $tahun);
        set_flash('success', 'RKT berhasil diperbarui.');
        header('Location: ' . APP_URL . '/rkt/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $existing = $this->model()->findWithKps($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $this->model()->delete($id);
        log_activity($pdo, 'rkt', 'delete', 'Hapus RKT ID ' . $id . ' tahun ' . (int) $existing['tahun']);
        set_flash('success', 'RKT berhasil dihapus.');
        header('Location: ' . APP_URL . '/rkt');
        exit;
    }
}
