<?php

declare(strict_types=1);

class KbrController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function model(): Kbr
    {
        return new Kbr($this->pdo());
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
     * Cari kth_id berdasarkan nama persis + desa (jika ada) atau kabupaten via desa.
     */
    private function resolveKthId(string $nama, ?int $desaId): ?int
    {
        $pdo = $this->pdo();
        if ($desaId !== null) {
            // Cari KTH di desa yang sama
            $stmt = $pdo->prepare('SELECT id FROM kth WHERE nama = ? AND desa_id = ? AND is_active = 1');
            $stmt->execute([$nama, $desaId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if (count($rows) === 1) {
                return (int) $rows[0];
            }
        }
        // Fallback: cari by nama saja, hanya jika tepat 1 match
        $stmt = $pdo->prepare('SELECT id FROM kth WHERE nama = ? AND is_active = 1');
        $stmt->execute([$nama]);
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
        if (in_array($tahun, Kbr::availableYears(), true)) {
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

        $pageTitle = 'Data KBR';
        $activeNav = 'kbr';
        $filterKab = $kabFilter;
        $filterTahun = $tahun;
        $filterQ = $q;
        $yearOptions = Kbr::availableYears();

        ob_start();
        require view_path('kbr/index.php');
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

        $pageTitle = 'Tambah KBR';
        $activeNav = 'kbr';
        $kbr = null;
        $yearOptions = Kbr::availableYears();

        ob_start();
        require view_path('kbr/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $namaKth = req_str('nama_kth');
        if ($namaKth === '' || strlen($namaKth) > 200) {
            set_flash('error', 'Nama KTH/pelaksana wajib diisi (maks 200 karakter).');
            header('Location: ' . APP_URL . '/kbr/create');
            exit;
        }

        $desaId = req_int_null('desa_id');
        $tahun = req_int_null('tahun_tanam');
        if ($tahun !== null && !in_array($tahun, Kbr::availableYears(), true)) {
            set_flash('error', 'Tahun tanam tidak valid.');
            header('Location: ' . APP_URL . '/kbr/create');
            exit;
        }

        $id = $this->model()->create([
            'kth_id' => $this->resolveKthId($namaKth, $desaId),
            'nama_kth' => $namaKth,
            'lokasi' => req_str('lokasi') !== '' ? req_str('lokasi') : null,
            'desa_id' => $desaId,
            'subdas' => req_str('subdas') !== '' ? req_str('subdas') : null,
            'tahun_tanam' => $tahun,
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
        ]);

        log_activity($pdo, 'kbr', 'create', 'Tambah KBR ID ' . $id . ' - ' . $namaKth);
        set_flash('success', 'Data KBR berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/kbr/' . $id);
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

        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $kabId = $this->model()->getKabupatenId($id);
            if ($kabId !== null && $kabId !== $opKab) {
                http_response_code(403);
                echo 'Akses ditolak';
                exit;
            }
        }

        $pageTitle = 'Detail KBR';
        $activeNav = 'kbr';
        $tanamans = $this->model()->getTanaman($id);

        ob_start();
        require view_path('kbr/show.php');
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

        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $kabId = $this->model()->getKabupatenId($id);
            if ($kabId !== null && $kabId !== $opKab) {
                http_response_code(403);
                exit;
            }
        }

        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($opKab !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $opKab
            ));
        }

        $stmtKth = $this->pdo()->query('SELECT nama FROM kth WHERE is_active = 1 ORDER BY nama');
        $pelaksanaOptions = $stmtKth->fetchAll(\PDO::FETCH_COLUMN);

        $pageTitle = 'Edit KBR';
        $activeNav = 'kbr';
        $kbr = $row;
        $yearOptions = Kbr::availableYears();

        ob_start();
        require view_path('kbr/form.php');
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

        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $kabId = $this->model()->getKabupatenId($id);
            if ($kabId !== null && $kabId !== $opKab) {
                http_response_code(403);
                exit;
            }
        }

        $namaKth = req_str('nama_kth');
        if ($namaKth === '' || strlen($namaKth) > 200) {
            set_flash('error', 'Nama KTH/pelaksana wajib diisi (maks 200 karakter).');
            header('Location: ' . APP_URL . '/kbr/' . $id . '/edit');
            exit;
        }

        $tahun = req_int_null('tahun_tanam');
        if ($tahun !== null && !in_array($tahun, Kbr::availableYears(), true)) {
            set_flash('error', 'Tahun tanam tidak valid.');
            header('Location: ' . APP_URL . '/kbr/' . $id . '/edit');
            exit;
        }

        $desaId = req_int_null('desa_id');
        $this->model()->update($id, [
            'kth_id' => $this->resolveKthId($namaKth, $desaId),
            'nama_kth' => $namaKth,
            'lokasi' => req_str('lokasi') !== '' ? req_str('lokasi') : null,
            'desa_id' => $desaId,
            'subdas' => req_str('subdas') !== '' ? req_str('subdas') : null,
            'tahun_tanam' => $tahun,
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? (float) req_str('koordinat_ls') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? (float) req_str('koordinat_bt') : null,
        ]);

        log_activity($pdo, 'kbr', 'update', 'Update KBR ID ' . $id . ' - ' . $namaKth);
        set_flash('success', 'Data KBR berhasil diperbarui.');
        header('Location: ' . APP_URL . '/kbr/' . $id);
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

        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $kabId = $this->model()->getKabupatenId($id);
            if ($kabId !== null && $kabId !== $opKab) {
                http_response_code(403);
                exit;
            }
        }

        $this->model()->delete($id);
        log_activity($pdo, 'kbr', 'delete', 'Hapus KBR ID ' . $id);
        set_flash('success', 'Data KBR berhasil dihapus.');
        header('Location: ' . APP_URL . '/kbr');
        exit;
    }

    public function storeTanaman(int $kbrId): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kbr = $this->model()->findById($kbrId);
        if ($kbr === false) {
            http_response_code(404);
            exit;
        }

        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $kabId = $this->model()->getKabupatenId($kbrId);
            if ($kabId !== null && $kabId !== $opKab) {
                http_response_code(403);
                exit;
            }
        }

        $jenis = req_str('jenis');
        $jumlah = (int) req_str('jumlah_btg');
        $luas = req_str('luas_ha') !== '' ? (float) req_str('luas_ha') : null;

        if ($jenis === '' || $jumlah <= 0) {
            set_flash('error', 'Jenis tanaman dan jumlah batang tidak valid.');
            header('Location: ' . APP_URL . '/kbr/' . $kbrId);
            exit;
        }

        $this->model()->addTanaman($kbrId, $jenis, $jumlah, $luas);
        log_activity($pdo, 'kbr_tanaman', 'create', 'Tambah tanaman ' . $jenis . ' ke KBR ID ' . $kbrId);
        set_flash('success', 'Tanaman berhasil ditambahkan.');
        header('Location: ' . APP_URL . '/kbr/' . $kbrId);
        exit;
    }

    public function deleteTanaman(int $tanamanId): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $tanaman = $this->model()->getTanamanById($tanamanId);
        if ($tanaman === false) {
            http_response_code(404);
            exit;
        }

        $kbrId = (int) $tanaman['kbr_id'];
        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $kabId = $this->model()->getKabupatenId($kbrId);
            if ($kabId !== null && $kabId !== $opKab) {
                http_response_code(403);
                exit;
            }
        }

        $this->model()->deleteTanaman($tanamanId);
        log_activity($pdo, 'kbr_tanaman', 'delete', 'Hapus tanaman ID ' . $tanamanId . ' dari KBR ID ' . $kbrId);
        set_flash('success', 'Tanaman berhasil dihapus.');
        header('Location: ' . APP_URL . '/kbr/' . $kbrId);
        exit;
    }
}
