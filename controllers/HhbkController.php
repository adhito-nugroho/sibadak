<?php

declare(strict_types=1);

class HhbkController
{
    private function pdo(): \PDO { return Database::connect(); }
    private function model(): Hhbk { return new Hhbk($this->pdo()); }
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
        if (in_array($tahun, Hhbk::availableYears(), true)) $filters['tahun'] = $tahun;
        $opKab = $this->opKabId();
        if ($opKab !== null) $filters['operator_kab_id'] = $opKab;
        elseif ($kabFilter > 0) $filters['kabupaten_id'] = $kabFilter;

        $result = $this->model()->paginateIndex($page, 25, $filters);
        $kabupatenList = $this->kthModel()->listKabupatenForFilter();

        $pageTitle = 'Data HHBK'; $activeNav = 'hhbk';
        $filterKab = $kabFilter; $filterTahun = $tahun; $filterQ = $q;
        $yearOptions = Hhbk::availableYears();
        ob_start(); require view_path('hhbk/index.php');
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
        $pageTitle = 'Tambah HHBK'; $activeNav = 'hhbk'; $hhbk = null;
        $yearOptions = Hhbk::availableYears(); $bulanOptions = Hhbk::bulanList();
        ob_start(); require view_path('hhbk/form.php');
        $content = ob_get_clean(); require view_path('layouts/main.php');
    }

    public function store(): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $kab = (int) req_str('kabupaten_id');
        if ($this->opKabId() !== null && $kab !== $this->opKabId()) {
            set_flash('error', 'Kabupaten tidak valid.'); header('Location: ' . APP_URL . '/hhbk/create'); exit;
        }
        $tahun = (int) req_str('tahun');
        $bulan = (int) req_str('bulan');
        if ($kab <= 0 || !in_array($tahun, Hhbk::availableYears(), true) || $bulan < 1 || $bulan > 12) {
            set_flash('error', 'Data wilayah/periode tidak valid.'); header('Location: ' . APP_URL . '/hhbk/create'); exit;
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
            'total_btg_bulan_ini' => 0, 'total_kg_bulan_ini' => 0,
            'total_btg_sd_bulan_lalu' => 0, 'total_kg_sd_bulan_lalu' => 0,
            'total_btg_sd_bulan_ini' => 0, 'total_kg_sd_bulan_ini' => 0,
            'keterangan' => req_str('keterangan') !== '' ? req_str('keterangan') : null,
        ]);
        log_activity($pdo, 'hhbk', 'create', 'Tambah HHBK ID ' . $id);
        set_flash('success', 'Data HHBK berhasil ditambahkan. Silakan tambahkan rincian komoditas.');
        header('Location: ' . APP_URL . '/hhbk/' . $id); exit;
    }

    public function show(int $id): void
    {
        requireLogin();
        $row = $this->model()->findWithRelations($id);
        if ($row === false) { http_response_code(404); echo 'Data tidak ditemukan'; exit; }
        if ($this->opKabId() !== null && (int) $row['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }
        $details = $this->model()->getDetail($id);
        $pageTitle = 'Detail HHBK'; $activeNav = 'hhbk';
        ob_start(); require view_path('hhbk/show.php');
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
        $pageTitle = 'Edit HHBK'; $activeNav = 'hhbk'; $hhbk = $row;
        $yearOptions = Hhbk::availableYears(); $bulanOptions = Hhbk::bulanList();
        ob_start(); require view_path('hhbk/form.php');
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
        log_activity($pdo, 'hhbk', 'update', 'Update HHBK ID ' . $id);
        set_flash('success', 'Data HHBK berhasil diperbarui.');
        header('Location: ' . APP_URL . '/hhbk/' . $id); exit;
    }

    public function delete(int $id): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) { http_response_code(404); exit; }
        if ($this->opKabId() !== null && (int) $existing['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }
        $this->model()->delete($id);
        log_activity($pdo, 'hhbk', 'delete', 'Hapus HHBK ID ' . $id);
        set_flash('success', 'Data HHBK berhasil dihapus.');
        header('Location: ' . APP_URL . '/hhbk'); exit;
    }

    public function storeDetail(int $hhbkId): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $hhbk = $this->model()->findById($hhbkId);
        if ($hhbk === false) { http_response_code(404); exit; }
        if ($this->opKabId() !== null && (int) $hhbk['kabupaten_id'] !== $this->opKabId()) { http_response_code(403); exit; }

        $volumes = $_POST['volumes'] ?? [];
        $masterKomoditas = $this->model()->getMasterKomoditas();
        $existingDetails = $this->model()->getDetail($hhbkId);

        // Map existing details by composite key: komoditas + satuan
        $existingMap = [];
        foreach ($existingDetails as $ed) {
            $key = $ed['komoditas'] . '|' . $ed['satuan'];
            $existingMap[$key] = $ed;
        }

        $pdo->beginTransaction();
        try {
            foreach ($masterKomoditas as $mk) {
                $komoditas = $mk['nama'];
                $satuan = $mk['satuan'];
                $key = $komoditas . '|' . $satuan;

                $rawVol = $volumes[$key] ?? '';
                if ($rawVol === '') {
                    $vol = 0.0;
                } else {
                    $rawVol = str_replace(',', '.', $rawVol);
                    $vol = (float) $rawVol;
                }

                $hasExisting = isset($existingMap[$key]);

                if ($vol > 0.0) {
                    $sdLalu = $this->model()->getSdBulanLalu($hhbkId, $komoditas, $satuan);
                    $sdIni  = $sdLalu + $vol;

                    if ($hasExisting) {
                        // Update
                        $stmt = $pdo->prepare('UPDATE hhbk_detail SET jumlah_bulan_ini = ?, jumlah_sd_bulan_lalu = ?, jumlah_sd_bulan_ini = ? WHERE id = ?');
                        $stmt->execute([$vol, $sdLalu, $sdIni, (int)$existingMap[$key]['id']]);
                    } else {
                        // Insert
                        $this->model()->addDetail($hhbkId, $komoditas, $satuan, $vol, $sdLalu, $sdIni);
                    }
                } else {
                    // Vol is 0 or empty. If existing, delete it.
                    if ($hasExisting) {
                        $this->model()->deleteDetail((int)$existingMap[$key]['id']);
                    }
                }
            }

            // Update totals in header
            $pdo->prepare('UPDATE hhbk SET total_btg_bulan_ini = (SELECT COALESCE(SUM(jumlah_bulan_ini),0) FROM hhbk_detail WHERE hhbk_id = ? AND satuan = "Batang"), total_btg_sd_bulan_ini = (SELECT COALESCE(SUM(jumlah_sd_bulan_ini),0) FROM hhbk_detail WHERE hhbk_id = ? AND satuan = "Batang") WHERE id = ?')->execute([$hhbkId, $hhbkId, $hhbkId]);
            $pdo->prepare('UPDATE hhbk SET total_kg_bulan_ini = (SELECT COALESCE(SUM(jumlah_bulan_ini),0) FROM hhbk_detail WHERE hhbk_id = ? AND satuan = "Kg"), total_kg_sd_bulan_ini = (SELECT COALESCE(SUM(jumlah_sd_bulan_ini),0) FROM hhbk_detail WHERE hhbk_id = ? AND satuan = "Kg") WHERE id = ?')->execute([$hhbkId, $hhbkId, $hhbkId]);

            $pdo->commit();
            log_activity($pdo, 'hhbk_detail', 'update_matrix', 'Simpan matriks detail HHBK ID ' . $hhbkId);
            set_flash('success', 'Rincian komoditas berhasil diperbarui.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            set_flash('error', 'Gagal menyimpan rincian: ' . $e->getMessage());
        }

        header('Location: ' . APP_URL . '/hhbk/' . $hhbkId); exit;
    }

    public function deleteDetail(int $detailId): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $detail = $this->model()->getDetailById($detailId);
        if ($detail === false) { http_response_code(404); exit; }
        $hhbkId = (int) $detail['hhbk_id'];
        $this->model()->deleteDetail($detailId);
        log_activity($pdo, 'hhbk_detail', 'delete', 'Hapus detail HHBK ID ' . $hhbkId);
        set_flash('success', 'Komoditas berhasil dihapus.');
        header('Location: ' . APP_URL . '/hhbk/' . $hhbkId); exit;
    }
}
