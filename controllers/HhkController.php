<?php

declare(strict_types=1);

class HhkController
{
    private function pdo(): \PDO { return Database::connect(); }
    private function model(): Hhk { return new Hhk($this->pdo()); }
    private function kthModel(): Kth { return new Kth($this->pdo()); }
    private function opKabId(): ?int { return user_role() === 'operator' ? user_kabupaten_id() : null; }

    public function index(): void
    {
        requireLogin();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $kabFilter = (int) ($_GET['kabupaten_id'] ?? 0);
        $tahun = (int) ($_GET['tahun'] ?? 0);
        $q = trim((string) ($_GET['q'] ?? ''));
        $filters = [];
        if ($q !== '') $filters['q'] = $q;
        if (in_array($tahun, Hhk::availableYears(), true)) $filters['tahun'] = $tahun;
        $opKab = $this->opKabId();
        if ($opKab !== null) $filters['operator_kab_id'] = $opKab;
        elseif ($kabFilter > 0) $filters['kabupaten_id'] = $kabFilter;

        $result = $this->model()->paginateIndex($page, 25, $filters);
        $kabupatenList = $this->kthModel()->listKabupatenForFilter();

        $pageTitle = 'Data HHK'; $activeNav = 'hhk';
        $filterKab = $kabFilter; $filterTahun = $tahun; $filterQ = $q;
        $yearOptions = Hhk::availableYears();
        ob_start(); require view_path('hhk/index.php');
        $content = ob_get_clean(); require view_path('layouts/main.php');
    }

    public function create(): void
    {
        requireLogin(); require_can_mutate_data();
        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter($kabupatenList, fn ($r) => (int) $r['id'] === $this->opKabId()));
        }
        $penyuluhList = (new PenyuluhKehutanan($this->pdo()))->options();
        $pageTitle = 'Tambah HHK'; $activeNav = 'hhk'; $hhk = null;
        $yearOptions = Hhk::availableYears(); $bulanOptions = Hhk::bulanList();
        ob_start(); require view_path('hhk/form.php');
        $content = ob_get_clean(); require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $kab = (int) req_str('kabupaten_id');
        if ($this->opKabId() !== null && $kab !== $this->opKabId()) {
            set_flash('error', 'Kabupaten tidak valid.'); header('Location: ' . APP_URL . '/hhk/create'); exit;
        }
        $tahun = (int) req_str('tahun');
        $bulan = (int) req_str('bulan');
        if ($kab <= 0 || !in_array($tahun, Hhk::availableYears(), true) || $bulan < 1 || $bulan > 12) {
            set_flash('error', 'Data wilayah/periode tidak valid.'); header('Location: ' . APP_URL . '/hhk/create'); exit;
        }
        $namaKth = trim(req_str('nama_kth'));
        $kthId = null;
        if ($namaKth !== '') {
            $stmt = $pdo->prepare('SELECT id, nama FROM kth WHERE nama = ? AND kabupaten_id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$namaKth, $kab]);
            $found = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($found !== false) {
                $kthId = (int) $found['id'];
                $namaKth = $found['nama'];
            }
        }
        $penyuluhId = req_int_null('penyuluh_id');
        $namaPenyuluh = null;
        if ($penyuluhId !== null) {
            $pRow = (new PenyuluhKehutanan($pdo))->findById($penyuluhId);
            if ($pRow !== false) $namaPenyuluh = $pRow['nama'];
        }
        $id = $this->model()->create([
            'kth_id' => $kthId, 'nama_kth' => $namaKth !== '' ? $namaKth : null,
            'kabupaten_id' => $kab,
            'kecamatan_id' => req_int_null('kecamatan_id'),
            'desa_id' => req_int_null('desa_id'),
            'penyuluh_id' => $penyuluhId,
            'nama_penyuluh' => $namaPenyuluh,
            'bulan' => $bulan, 'tahun' => $tahun,
            'total_bulan_ini_m3' => 0, 'total_sd_bulan_lalu_m3' => 0, 'total_sd_bulan_ini_m3' => 0,
            'keterangan' => req_str('keterangan') !== '' ? req_str('keterangan') : null,
        ]);
        log_activity($pdo, 'hhk', 'create', 'Tambah HHK ID ' . $id);
        set_flash('success', 'Data HHK berhasil ditambahkan. Silakan tambahkan rincian jenis kayu.');
        header('Location: ' . APP_URL . '/hhk/' . $id); exit;
    }

    public function show(int $id): void
    {
        requireLogin();
        $row = $this->model()->findWithRelations($id);
        if ($row === false) { http_response_code(404); echo 'Data tidak ditemukan'; exit; }
        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }
        $details = $this->model()->getDetail($id);
        $pageTitle = 'Detail HHK'; $activeNav = 'hhk';
        ob_start(); require view_path('hhk/show.php');
        $content = ob_get_clean(); require view_path('layouts/main.php');
    }

    public function edit(int $id): void
    {
        requireLogin(); require_can_mutate_data();
        $row = $this->model()->findWithRelations($id);
        if ($row === false) { http_response_code(404); exit; }
        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }
        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter($kabupatenList, fn ($r) => (int) $r['id'] === $this->opKabId()));
        }
        $penyuluhList = (new PenyuluhKehutanan($this->pdo()))->options();
        $pageTitle = 'Edit HHK'; $activeNav = 'hhk'; $hhk = $row;
        $yearOptions = Hhk::availableYears(); $bulanOptions = Hhk::bulanList();
        ob_start(); require view_path('hhk/form.php');
        $content = ob_get_clean(); require view_path('layouts/main.php');
    }

    public function update(int $id): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) { http_response_code(404); exit; }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }
        $kab = $this->opKabId() ?? (int) req_str('kabupaten_id');
        $namaKth = trim(req_str('nama_kth'));
        $kthId = null;
        if ($namaKth !== '') {
            $stmt = $pdo->prepare('SELECT id, nama FROM kth WHERE nama = ? AND kabupaten_id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$namaKth, $kab]);
            $found = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($found !== false) {
                $kthId = (int) $found['id'];
                $namaKth = $found['nama'];
            }
        }
        $penyuluhId = req_int_null('penyuluh_id');
        $namaPenyuluh = null;
        if ($penyuluhId !== null) {
            $pRow = (new PenyuluhKehutanan($pdo))->findById($penyuluhId);
            if ($pRow !== false) $namaPenyuluh = $pRow['nama'];
        }
        $this->model()->update($id, [
            'kth_id' => $kthId, 'nama_kth' => $namaKth !== '' ? $namaKth : null,
            'kabupaten_id' => $kab,
            'kecamatan_id' => req_int_null('kecamatan_id'),
            'desa_id' => req_int_null('desa_id'),
            'penyuluh_id' => $penyuluhId,
            'nama_penyuluh' => $namaPenyuluh,
            'bulan' => (int) req_str('bulan'), 'tahun' => (int) req_str('tahun'),
            'keterangan' => req_str('keterangan') !== '' ? req_str('keterangan') : null,
        ]);
        log_activity($pdo, 'hhk', 'update', 'Update HHK ID ' . $id);
        set_flash('success', 'Data HHK berhasil diperbarui.');
        header('Location: ' . APP_URL . '/hhk/' . $id); exit;
    }

    public function delete(int $id): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) { http_response_code(404); exit; }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }
        $this->model()->delete($id);
        log_activity($pdo, 'hhk', 'delete', 'Hapus HHK ID ' . $id);
        set_flash('success', 'Data HHK berhasil dihapus.');
        header('Location: ' . APP_URL . '/hhk'); exit;
    }

    public function storeDetail(int $hhkId): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $hhk = $this->model()->findById($hhkId);
        if ($hhk === false) { http_response_code(404); exit; }
        if ($this->opKabId() !== null && (int) $hhk['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }

        $volumes = $_POST['volumes'] ?? [];
        $masterKomoditas = $this->model()->getMasterKomoditas();
        $existingDetails = $this->model()->getDetail($hhkId);

        // Map existing details by jenis_kayu for easy access
        $existingMap = [];
        foreach ($existingDetails as $ed) {
            $existingMap[$ed['jenis_kayu']] = $ed;
        }

        $pdo->beginTransaction();
        try {
            foreach ($masterKomoditas as $jenis) {
                $rawVol = $volumes[$jenis] ?? '';
                if ($rawVol === '') {
                    $vol = 0.0;
                } else {
                    $rawVol = str_replace(',', '.', $rawVol);
                    $vol = (float) $rawVol;
                }

                $hasExisting = isset($existingMap[$jenis]);

                if ($vol > 0.0) {
                    $sdLalu = $this->model()->getSdBulanLalu($hhkId, $jenis);
                    $sdIni = $sdLalu + $vol;

                    if ($hasExisting) {
                        // Update
                        $stmt = $pdo->prepare('UPDATE hhk_detail SET volume_bulan_ini_m3 = ?, volume_sd_bulan_lalu_m3 = ?, volume_sd_bulan_ini_m3 = ? WHERE id = ?');
                        $stmt->execute([$vol, $sdLalu, $sdIni, (int)$existingMap[$jenis]['id']]);
                    } else {
                        // Insert
                        $this->model()->addDetail($hhkId, $jenis, $vol, $sdLalu, $sdIni);
                    }
                } else {
                    // Vol is 0 or empty. If existing, delete it to keep db clean.
                    if ($hasExisting) {
                        $this->model()->deleteDetail((int)$existingMap[$jenis]['id']);
                    }
                }
            }

            // Update totals in header
            $pdo->prepare('UPDATE hhk SET total_bulan_ini_m3 = (SELECT COALESCE(SUM(volume_bulan_ini_m3),0) FROM hhk_detail WHERE hhk_id = ?), total_sd_bulan_lalu_m3 = (SELECT COALESCE(SUM(volume_sd_bulan_lalu_m3),0) FROM hhk_detail WHERE hhk_id = ?), total_sd_bulan_ini_m3 = (SELECT COALESCE(SUM(volume_sd_bulan_ini_m3),0) FROM hhk_detail WHERE hhk_id = ?) WHERE id = ?')->execute([$hhkId, $hhkId, $hhkId, $hhkId]);

            $pdo->commit();
            log_activity($pdo, 'hhk_detail', 'update_matrix', 'Simpan matriks detail HHK ID ' . $hhkId);
            set_flash('success', 'Rincian kayu berhasil diperbarui.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            set_flash('error', 'Gagal menyimpan rincian: ' . $e->getMessage());
        }

        header('Location: ' . APP_URL . '/hhk/' . $hhkId); exit;
    }

    public function deleteDetail(int $detailId): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $detail = $this->model()->getDetailById($detailId);
        if ($detail === false) { http_response_code(404); exit; }
        $hhkId = (int) $detail['hhk_id'];
        $this->model()->deleteDetail($detailId);
        $pdo->prepare('UPDATE hhk SET total_bulan_ini_m3 = (SELECT COALESCE(SUM(volume_bulan_ini_m3),0) FROM hhk_detail WHERE hhk_id = ?), total_sd_bulan_lalu_m3 = (SELECT COALESCE(SUM(volume_sd_bulan_lalu_m3),0) FROM hhk_detail WHERE hhk_id = ?), total_sd_bulan_ini_m3 = (SELECT COALESCE(SUM(volume_sd_bulan_ini_m3),0) FROM hhk_detail WHERE hhk_id = ?) WHERE id = ?')->execute([$hhkId, $hhkId, $hhkId, $hhkId]);
        log_activity($pdo, 'hhk_detail', 'delete', 'Hapus detail HHK ID ' . $hhkId);
        set_flash('success', 'Rincian berhasil dihapus.');
        header('Location: ' . APP_URL . '/hhk/' . $hhkId); exit;
    }
}
