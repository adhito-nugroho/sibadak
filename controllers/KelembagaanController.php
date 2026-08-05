<?php

declare(strict_types=1);

class KelembagaanController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function anggotaModel(): KthAnggota
    {
        return new KthAnggota($this->pdo());
    }

    private function kthModel(): Kth
    {
        return new Kth($this->pdo());
    }

    private function opKabId(): ?int
    {
        return user_role() === 'operator' ? user_kabupaten_id() : null;
    }

    /** @param positive-int ...$kthIds */
    private function syncKthAnggotaCounts(int ...$kthIds): void
    {
        $k = $this->kthModel();
        foreach (array_unique($kthIds) as $id) {
            if ($id > 0) {
                $k->syncJumlahAnggotaFromAnggotaTable($id);
            }
        }
    }

    /**
     * Pastikan KTH ada, aktif, dan boleh diakses peran saat ini.
     *
     * @return array<string, mixed>|false
     */
    private function assertKthAccessible(int $kthId): array|false
    {
        $row = $this->kthModel()->findWithWilayah($kthId);
        if ($row === false || (int) $row['is_active'] !== 1) {
            return false;
        }
        $op = $this->opKabId();
        if ($op !== null && (int) $row['kabupaten_id'] !== $op) {
            return false;
        }

        return $row;
    }

    /** @return array<string, scalar|null> */
    private function normalizeAnggotaPayload(): array
    {
        $nikRaw = req_str('nik');
        $nik = $nikRaw !== '' ? $nikRaw : null;

        $gender = req_str('gender');
        $genderOut = in_array($gender, ['L', 'P'], true) ? $gender : null;

        $ls = req_str('koordinat_ls');
        $bt = req_str('koordinat_bt');
        $luas = req_str('luas_garapan_ha');

        return [
            'kth_id' => (int) req_str('kth_id'),
            'posisi' => req_str('posisi'),
            'nama' => req_str('nama'),
            'nik' => $nik,
            'no_kk' => req_str('no_kk') !== '' ? req_str('no_kk') : null,
            'alamat' => req_str('alamat') !== '' ? req_str('alamat') : null,
            'pekerjaan' => req_str('pekerjaan') !== '' ? req_str('pekerjaan') : null,
            'gender' => $genderOut,
            'no_telpon' => req_str('no_telpon') !== '' ? req_str('no_telpon') : null,
            'luas_garapan_ha' => $luas !== '' ? (float) $luas : null,
            'komoditi_hhbk' => req_str('komoditi_hhbk') !== '' ? req_str('komoditi_hhbk') : null,
            'komoditi_hhk' => req_str('komoditi_hhk') !== '' ? req_str('komoditi_hhk') : null,
            'htm' => req_str('htm') !== '' ? req_str('htm') : null,
            'koordinat_ls' => $ls !== '' ? (float) $ls : null,
            'koordinat_bt' => $bt !== '' ? (float) $bt : null,
        ];
    }

    public function index(): void
    {
        requireLogin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $kabFilter = isset($_GET['kabupaten_id']) ? (int) $_GET['kabupaten_id'] : 0;
        $kthFilter = isset($_GET['kth_id']) ? (int) $_GET['kth_id'] : 0;
        $posisi = isset($_GET['posisi']) ? trim((string) $_GET['posisi']) : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        $filters = [];
        if ($q !== '') {
            $filters['q'] = $q;
        }
        if (in_array($posisi, KthAnggota::posisiList(), true)) {
            $filters['posisi'] = $posisi;
        }
        $opKab = $this->opKabId();
        if ($opKab !== null) {
            $filters['operator_kab_id'] = $opKab;
        } elseif ($kabFilter > 0) {
            $filters['kabupaten_id'] = $kabFilter;
        }
        if ($kthFilter > 0) {
            $filters['kth_id'] = $kthFilter;
        }

        $result = $this->anggotaModel()->paginateIndex($page, $perPage, $filters);

        $kthM = $this->kthModel();
        $kabupatenList = $kthM->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }

        $kabForKth = $opKab !== null ? $opKab : ($kabFilter > 0 ? $kabFilter : null);
        $kthOptions = $kthM->listKthForSelect($kabForKth, $opKab);

        $pageTitle = 'Kelembagaan';
        $activeNav = 'kelembagaan';
        $filterKab = $kabFilter;
        $filterKth = $kthFilter;
        $filterPosisi = $posisi;
        $filterQ = $q;

        ob_start();
        require view_path('kelembagaan/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin();
        require_can_mutate_data();

        $preKth = isset($_GET['kth_id']) ? (int) $_GET['kth_id'] : 0;
        $preKthOk = false;
        if ($preKth > 0 && $this->assertKthAccessible($preKth) !== false) {
            $preKthOk = true;
        }

        $kthOptions = $this->kthModel()->listKthForSelect(null, $this->opKabId());

        $pageTitle = 'Tambah Anggota';
        $activeNav = 'kelembagaan';
        $anggota = null;
        $selectedKthId = $preKthOk ? $preKth : 0;

        ob_start();
        require view_path('kelembagaan/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $payload = $this->normalizeAnggotaPayload();
        $kthId = (int) $payload['kth_id'];

        if ($kthId <= 0) {
            set_flash('error', 'Pilih KTH.');
            header('Location: ' . APP_URL . '/kelembagaan/create');
            exit;
        }

        $kthRow = $this->assertKthAccessible($kthId);
        if ($kthRow === false) {
            set_flash('error', 'KTH tidak ditemukan atau tidak boleh diakses.');
            header('Location: ' . APP_URL . '/kelembagaan/create');
            exit;
        }

        $posisi = (string) $payload['posisi'];
        if (!in_array($posisi, KthAnggota::posisiList(), true)) {
            set_flash('error', 'Posisi tidak valid.');
            header('Location: ' . APP_URL . '/kelembagaan/create');
            exit;
        }

        $nama = (string) $payload['nama'];
        if (strlen($nama) < 2 || strlen($nama) > 150) {
            set_flash('error', 'Nama wajib 2–150 karakter.');
            header('Location: ' . APP_URL . '/kelembagaan/create');
            exit;
        }

        $nik = $payload['nik'] !== null ? (string) $payload['nik'] : null;
        if ($nik !== null && strlen($nik) > 20) {
            set_flash('error', 'NIK maksimal 20 karakter.');
            header('Location: ' . APP_URL . '/kelembagaan/create');
            exit;
        }
        if ($nik !== null && $this->anggotaModel()->nikExists($nik)) {
            set_flash('error', 'NIK sudah terdaftar untuk anggota lain.');
            header('Location: ' . APP_URL . '/kelembagaan/create');
            exit;
        }

        unset($payload['kth_id']);
        $data = array_merge(['kth_id' => $kthId], $payload);

        $id = $this->anggotaModel()->create($data);
        $this->syncKthAnggotaCounts($kthId);
        log_activity($pdo, 'kelembagaan', 'create', 'Tambah anggota KTH: ' . $nama . ' (ID ' . $id . ')');
        set_flash('success', 'Data anggota berhasil ditambahkan.');

        header('Location: ' . APP_URL . '/kelembagaan/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        requireLogin();

        $row = $this->anggotaModel()->findWithKth($id);
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

        $pageTitle = 'Detail Anggota';
        $activeNav = 'kelembagaan';

        ob_start();
        require view_path('kelembagaan/show.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin();
        require_can_mutate_data();

        $row = $this->anggotaModel()->findWithKth($id);
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

        $kthOptions = $this->kthModel()->listKthForSelect(null, $this->opKabId());

        $pageTitle = 'Edit Anggota';
        $activeNav = 'kelembagaan';
        $anggota = $row;
        $selectedKthId = (int) $row['kth_id'];

        ob_start();
        require view_path('kelembagaan/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $existing = $this->anggotaModel()->findWithKth($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $oldKthId = (int) $existing['kth_id'];
        $payload = $this->normalizeAnggotaPayload();
        $kthId = (int) $payload['kth_id'];

        if ($kthId <= 0) {
            set_flash('error', 'Pilih KTH.');
            header('Location: ' . APP_URL . '/kelembagaan/' . $id . '/edit');
            exit;
        }

        $kthRow = $this->assertKthAccessible($kthId);
        if ($kthRow === false) {
            set_flash('error', 'KTH tidak valid.');
            header('Location: ' . APP_URL . '/kelembagaan/' . $id . '/edit');
            exit;
        }

        $posisi = (string) $payload['posisi'];
        if (!in_array($posisi, KthAnggota::posisiList(), true)) {
            set_flash('error', 'Posisi tidak valid.');
            header('Location: ' . APP_URL . '/kelembagaan/' . $id . '/edit');
            exit;
        }

        $nama = (string) $payload['nama'];
        if (strlen($nama) < 2 || strlen($nama) > 150) {
            set_flash('error', 'Nama wajib 2–150 karakter.');
            header('Location: ' . APP_URL . '/kelembagaan/' . $id . '/edit');
            exit;
        }

        $nik = $payload['nik'] !== null ? (string) $payload['nik'] : null;
        if ($nik !== null && strlen($nik) > 20) {
            set_flash('error', 'NIK maksimal 20 karakter.');
            header('Location: ' . APP_URL . '/kelembagaan/' . $id . '/edit');
            exit;
        }
        if ($nik !== null && $this->anggotaModel()->nikExists($nik, $id)) {
            set_flash('error', 'NIK sudah dipakai anggota lain.');
            header('Location: ' . APP_URL . '/kelembagaan/' . $id . '/edit');
            exit;
        }

        unset($payload['kth_id']);
        $data = array_merge(['kth_id' => $kthId], $payload);

        $this->anggotaModel()->update($id, $data);
        $toSync = [$kthId];
        if ($oldKthId !== $kthId) {
            $toSync[] = $oldKthId;
        }
        $this->syncKthAnggotaCounts(...$toSync);

        log_activity($pdo, 'kelembagaan', 'update', 'Update anggota ID ' . $id . ': ' . $nama);
        set_flash('success', 'Data anggota diperbarui.');

        header('Location: ' . APP_URL . '/kelembagaan/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $existing = $this->anggotaModel()->findWithKth($id);
        if ($existing === false) {
            http_response_code(404);
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $kthId = (int) $existing['kth_id'];
        $nama = (string) $existing['nama'];

        $this->anggotaModel()->delete($id);
        $this->syncKthAnggotaCounts($kthId);

        log_activity($pdo, 'kelembagaan', 'delete', 'Hapus anggota ID ' . $id . ' — ' . $nama);
        set_flash('success', 'Anggota berhasil dihapus dari data kelembagaan.');

        header('Location: ' . APP_URL . '/kelembagaan');
        exit;
    }
}
