<?php

declare(strict_types=1);

class KpsAnggotaController
{
    private function pdo(): \PDO
    {
        return Database::connect();
    }

    private function kpsModel(): Kps
    {
        return new Kps($this->pdo());
    }

    private function anggotaModel(): KpsAnggota
    {
        return new KpsAnggota($this->pdo());
    }

    private function kthModel(): Kth
    {
        return new Kth($this->pdo());
    }

    private function opKabId(): ?int
    {
        return user_role() === 'operator' ? user_kabupaten_id() : null;
    }

    /** @return array<string, mixed>|false */
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

    /** @return array<string, scalar|null> */
    private function normalizePayload(int $kpsId): array
    {
        $kategori = req_str('kategori');
        $kategoriOut = in_array($kategori, KpsAnggota::kategoriList(), true) ? $kategori : 'andil_garapan';

        $tanggalRaw = req_str('tanggal');
        $tanggalOut = null;
        if ($tanggalRaw !== '') {
            $d = \DateTimeImmutable::createFromFormat('Y-m-d', $tanggalRaw);
            if ($d instanceof \DateTimeImmutable) {
                $tanggalOut = $d->format('Y-m-d');
            }
        }

        $luasRaw = req_str('luas_ha');
        $luasOut = $luasRaw !== '' ? (float) $luasRaw : null;

        $desaId = (int) req_str('desa_id');
        $kecId = (int) req_str('kecamatan_id');

        return [
            'kps_id' => $kpsId,
            'kategori' => $kategoriOut,
            'no_andil' => req_str('no_andil') !== '' ? req_str('no_andil') : null,
            'nama_penggarap' => req_str('nama_penggarap'),
            'no_kk' => req_str('no_kk') !== '' ? req_str('no_kk') : null,
            'nik' => req_str('nik') !== '' ? req_str('nik') : null,
            'koordinat_bt' => req_str('koordinat_bt') !== '' ? req_str('koordinat_bt') : null,
            'koordinat_ls' => req_str('koordinat_ls') !== '' ? req_str('koordinat_ls') : null,
            'batas_barat' => req_str('batas_barat') !== '' ? req_str('batas_barat') : null,
            'batas_utara' => req_str('batas_utara') !== '' ? req_str('batas_utara') : null,
            'batas_selatan' => req_str('batas_selatan') !== '' ? req_str('batas_selatan') : null,
            'batas_timur' => req_str('batas_timur') !== '' ? req_str('batas_timur') : null,
            'desa_id' => $desaId > 0 ? $desaId : null,
            'kecamatan_id' => $kecId > 0 ? $kecId : null,
            'pengukur' => req_str('pengukur') !== '' ? req_str('pengukur') : null,
            'tanggal' => $tanggalOut,
            'komoditas' => req_str('komoditas') !== '' ? req_str('komoditas') : null,
            'luas_ha' => $luasOut,
        ];
    }

    public function index(int $kpsId): void
    {
        requireLogin();

        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            echo 'KPS tidak ditemukan atau tidak boleh diakses';
            exit;
        }

        $rows = $this->anggotaModel()->listByKpsId($kpsId, null);
        $ruang = array_values(array_filter($rows, fn (array $r): bool => (string) $r['kategori'] === 'ruang_perlindungan_komunal'));
        $andil = array_values(array_filter($rows, fn (array $r): bool => (string) $r['kategori'] === 'andil_garapan'));

        $pageTitle = 'Anggota KPS';
        $activeNav = 'kps';
        $canMut = in_array(user_role(), ['admin', 'operator'], true);

        ob_start();
        require view_path('kps_anggota/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function create(int $kpsId): void
    {
        requireLogin();
        require_can_mutate_data();

        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            echo 'KPS tidak valid';
            exit;
        }

        $kabupatenList = $this->kthModel()->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter(
                $kabupatenList,
                fn (array $r): bool => (int) $r['id'] === $this->opKabId()
            ));
        }
        $kecamatanOptions = $this->kthModel()->listKecamatanByKabupaten((int) $kps['kabupaten_id']);
        $desaOptions = $this->kthModel()->listDesaByKecamatan((int) $kps['kecamatan_id']);

        $pageTitle = 'Tambah Anggota KPS';
        $activeNav = 'kps';
        $anggota = null;

        ob_start();
        require view_path('kps_anggota/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function store(int $kpsId): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            exit;
        }

        $payload = $this->normalizePayload($kpsId);

        $nama = (string) $payload['nama_penggarap'];
        if (strlen($nama) < 2 || strlen($nama) > 150) {
            set_flash('error', 'Nama penggarap wajib 2–150 karakter.');
            header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/create');
            exit;
        }

        // Validasi wilayah jika diisi: harus selaras dengan kabupaten KPS.
        $desaId = $payload['desa_id'] !== null ? (int) $payload['desa_id'] : 0;
        $kecId = $payload['kecamatan_id'] !== null ? (int) $payload['kecamatan_id'] : 0;
        if ($desaId > 0 && $kecId > 0) {
            $v = new WilayahValidate($pdo);
            if (!$v->isValidChain((int) $kps['kabupaten_id'], $kecId, $desaId)) {
                set_flash('error', 'Wilayah (kecamatan/desa) tidak konsisten dengan kabupaten KPS.');
                header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/create');
                exit;
            }
        }

        $data = $payload;
        unset($data['kps_id']);
        $id = $this->anggotaModel()->create(array_merge(['kps_id' => $kpsId], $data));
        log_activity($pdo, 'kps_anggota', 'create', 'Tambah anggota KPS ID ' . $kpsId . ' (Anggota ID ' . $id . ')');
        set_flash('success', 'Anggota KPS berhasil ditambahkan.');

        header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota');
        exit;
    }

    public function edit(int $kpsId, int $id): void
    {
        requireLogin();
        require_can_mutate_data();

        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            exit;
        }

        $row = $this->anggotaModel()->findWithKps($id);
        if ($row === false || (int) $row['kps_id'] !== $kpsId) {
            http_response_code(404);
            echo 'Data tidak ditemukan';
            exit;
        }

        if ($this->opKabId() !== null && (int) $row['kps_kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            echo 'Akses ditolak';
            exit;
        }

        $kecamatanOptions = $this->kthModel()->listKecamatanByKabupaten((int) $kps['kabupaten_id']);
        $desaOptions = $this->kthModel()->listDesaByKecamatan((int) ($row['kecamatan_id'] ?? $kps['kecamatan_id']));

        $pageTitle = 'Edit Anggota KPS';
        $activeNav = 'kps';
        $anggota = $row;

        ob_start();
        require view_path('kps_anggota/form.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function update(int $kpsId, int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            exit;
        }

        $existing = $this->anggotaModel()->findWithKps($id);
        if ($existing === false || (int) $existing['kps_id'] !== $kpsId) {
            http_response_code(404);
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kps_kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $payload = $this->normalizePayload($kpsId);
        $nama = (string) $payload['nama_penggarap'];
        if (strlen($nama) < 2 || strlen($nama) > 150) {
            set_flash('error', 'Nama penggarap wajib 2–150 karakter.');
            header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/' . $id . '/edit');
            exit;
        }

        $desaId = $payload['desa_id'] !== null ? (int) $payload['desa_id'] : 0;
        $kecId = $payload['kecamatan_id'] !== null ? (int) $payload['kecamatan_id'] : 0;
        if ($desaId > 0 && $kecId > 0) {
            $v = new WilayahValidate($pdo);
            if (!$v->isValidChain((int) $kps['kabupaten_id'], $kecId, $desaId)) {
                set_flash('error', 'Wilayah (kecamatan/desa) tidak konsisten dengan kabupaten KPS.');
                header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/' . $id . '/edit');
                exit;
            }
        }

        unset($payload['kps_id']);
        $this->anggotaModel()->update($id, $payload);
        log_activity($pdo, 'kps_anggota', 'update', 'Update anggota KPS ID ' . $kpsId . ' (Anggota ID ' . $id . ')');
        set_flash('success', 'Anggota KPS berhasil diperbarui.');

        header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota');
        exit;
    }

    public function delete(int $kpsId, int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            exit;
        }

        $existing = $this->anggotaModel()->findWithKps($id);
        if ($existing === false || (int) $existing['kps_id'] !== $kpsId) {
            http_response_code(404);
            exit;
        }

        if ($this->opKabId() !== null && (int) $existing['kps_kabupaten_id'] !== $this->opKabId()) {
            http_response_code(403);
            exit;
        }

        $this->anggotaModel()->delete($id);
        log_activity($pdo, 'kps_anggota', 'delete', 'Hapus anggota KPS ID ' . $kpsId . ' (Anggota ID ' . $id . ')');
        set_flash('success', 'Anggota KPS berhasil dihapus.');

        header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota');
        exit;
    }

    public function importForm(int $kpsId): void
    {
        requireLogin();
        require_can_mutate_data();

        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            echo 'KPS tidak valid';
            exit;
        }

        $pageTitle = 'Impor Anggota KPS';
        $activeNav = 'kps';

        ob_start();
        require view_path('kps_anggota/import.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }

    public function importPreview(int $kpsId): void
    {
        requireLogin();
        require_can_mutate_data();
        header('Content-Type: application/json; charset=utf-8');

        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'KPS tidak valid'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Gagal mengupload berkas.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $file = $_FILES['import_file']['tmp_name'];
        $origName = $_FILES['import_file']['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            echo json_encode(['success' => false, 'error' => 'Format file harus .xlsx, .xls, atau .csv'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tmpDir = app_path('uploads/tmp_import');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $tempFileName = 'import_' . $kpsId . '_' . time() . '_' . uniqid() . '.' . $ext;
        $tempFilePath = $tmpDir . '/' . $tempFileName;

        if (!move_uploaded_file($file, $tempFilePath)) {
            echo json_encode(['success' => false, 'error' => 'Gagal menyimpan file temporary.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCsvForPreview($tempFilePath);
            } else {
                $rows = $this->parseXlsxForPreview($tempFilePath);
            }
        } catch (\Throwable $e) {
            @unlink($tempFilePath);
            echo json_encode(['success' => false, 'error' => 'Gagal memproses file Excel: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($rows)) {
            @unlink($tempFilePath);
            echo json_encode(['success' => false, 'error' => 'File kosong atau tidak ada baris data.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $headers = array_shift($rows);
        $headers = array_map(fn($h) => trim((string)$h), $headers);

        echo json_encode([
            'success' => true,
            'temp_file' => $tempFileName,
            'headers' => $headers,
            'preview_rows' => array_slice($rows, 0, 5)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function parseXlsxForPreview(string $file): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $ws = $spreadsheet->getActiveSheet();

        $rows = [];
        $maxRow = min(50, $ws->getHighestDataRow());
        $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestDataColumn());

        for ($row = 1; $row <= $maxRow; $row++) {
            $r = [];
            for ($col = 1; $col <= $maxCol; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cell = $ws->getCell($colLetter . $row);
                $r[$col - 1] = $cell->getValue();
            }
            if ($row > 1 && array_filter($r, fn($v) => $v !== null && $v !== '') === []) continue;
            $rows[] = $r;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return $rows;
    }

    private function parseCsvForPreview(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        $count = 0;
        while (($data = fgetcsv($handle, 0, ',')) !== false && $count < 50) {
            $rows[] = array_values($data);
            $count++;
        }
        fclose($handle);
        return $rows;
    }

    public function importProcess(int $kpsId): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();
        $kps = $this->assertKpsAccessible($kpsId);
        if ($kps === false) {
            http_response_code(404);
            exit;
        }

        $tempFile = req_str('temp_file');
        $kategoriDefault = req_str('kategori_default');
        if (!in_array($kategoriDefault, KpsAnggota::kategoriList(), true)) {
            $kategoriDefault = 'andil_garapan';
        }

        $mappings = $_POST['mappings'] ?? [];
        if (!is_array($mappings) || empty($mappings['nama_penggarap'])) {
            set_flash('error', 'Pemetaan kolom Nama Penggarap wajib ditentukan.');
            header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/import');
            exit;
        }

        $tmpDir = app_path('uploads/tmp_import');
        $tempFilePath = $tmpDir . '/' . basename($tempFile);

        if ($tempFile === '' || !is_file($tempFilePath)) {
            set_flash('error', 'Berkas impor kadaluarsa atau tidak ditemukan.');
            header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/import');
            exit;
        }

        $ext = strtolower(pathinfo($tempFilePath, PATHINFO_EXTENSION));

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCsvForAll($tempFilePath);
            } else {
                $rows = $this->parseXlsxForAll($tempFilePath);
            }
        } catch (\Throwable $e) {
            @unlink($tempFilePath);
            set_flash('error', 'Gagal memproses file Excel: ' . $e->getMessage());
            header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/import');
            exit;
        }

        if (empty($rows)) {
            @unlink($tempFilePath);
            set_flash('error', 'Berkas kosong atau tidak ada data.');
            header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota/import');
            exit;
        }

        $kabId = (int)$kps['kabupaten_id'];
        $kecRows = $pdo->query("SELECT id, UPPER(TRIM(nama)) as nama FROM kecamatan WHERE kabupaten_id = $kabId")->fetchAll(\PDO::FETCH_ASSOC);
        $kecMap = [];
        foreach ($kecRows as $kr) {
            $kecMap[$kr['nama']] = (int)$kr['id'];
        }

        $desaMap = [];
        if (!empty($kecMap)) {
            $kecIdsStr = implode(',', array_values($kecMap));
            $desaRows = $pdo->query("SELECT id, kecamatan_id, UPPER(TRIM(nama)) as nama FROM desa WHERE kecamatan_id IN ($kecIdsStr)")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($desaRows as $dr) {
                $desaMap[$dr['kecamatan_id'] . '|' . $dr['nama']] = (int)$dr['id'];
            }
        }

        $imported = 0;
        $skipped = 0;
        $warnings = [];

        $pdo->beginTransaction();

        $sql = "INSERT INTO kps_anggota (
            kps_id, kategori, no_andil, nama_penggarap, no_kk, nik, 
            koordinat_bt, koordinat_ls, batas_barat, batas_utara, 
            batas_selatan, batas_timur, desa_id, kecamatan_id, 
            pengukur, tanggal, komoditas, luas_ha
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?, ?
        )";
        $stmt = $pdo->prepare($sql);

        $getVal = function(array $row, $field) use ($mappings) {
            $idx = $mappings[$field] ?? '';
            if ($idx === '') return null;
            $val = $row[(int)$idx] ?? null;
            return $val !== null ? trim((string)$val) : null;
        };

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2;

            $nama = $getVal($row, 'nama_penggarap');
            if ($nama === null || $nama === '') {
                $skipped++;
                continue;
            }

            $kategori = $getVal($row, 'kategori');
            if ($kategori === null || !in_array($kategori, KpsAnggota::kategoriList(), true)) {
                $kategori = $kategoriDefault;
            }

            $luasRaw = $getVal($row, 'luas_ha');
            $luas = null;
            if ($luasRaw !== null && $luasRaw !== '') {
                $luasRaw = str_replace(',', '.', $luasRaw);
                $luas = (float)$luasRaw;
            }

            $tglRaw = $getVal($row, 'tanggal');
            $tanggal = $this->parseImportDate($tglRaw);

            $kecNama = $getVal($row, 'kecamatan_id');
            $desaNama = $getVal($row, 'desa_id');

            $rowKecId = null;
            $rowDesaId = null;

            if ($kecNama !== null && $kecNama !== '') {
                $kecUpper = strtoupper(trim($kecNama));
                if (isset($kecMap[$kecUpper])) {
                    $rowKecId = $kecMap[$kecUpper];
                } else {
                    $warnings[] = "Baris $lineNum: Kecamatan '$kecNama' tidak ditemukan di database Bojonegoro.";
                }
            }

            if ($desaNama !== null && $desaNama !== '') {
                $desaUpper = strtoupper(trim($desaNama));
                if ($rowKecId !== null) {
                    $key = $rowKecId . '|' . $desaUpper;
                    if (isset($desaMap[$key])) {
                        $rowDesaId = $desaMap[$key];
                    } else {
                        $warnings[] = "Baris $lineNum: Desa '$desaNama' tidak ditemukan di Kecamatan '$kecNama'.";
                    }
                } else {
                    $matchingKecs = [];
                    foreach ($kecMap as $kName => $kId) {
                        if (isset($desaMap[$kId . '|' . $desaUpper])) {
                            $matchingKecs[] = $kId;
                        }
                    }
                    if (count($matchingKecs) === 1) {
                        $rowKecId = $matchingKecs[0];
                        $rowDesaId = $desaMap[$rowKecId . '|' . $desaUpper];
                    } else {
                        $warnings[] = "Baris $lineNum: Desa '$desaNama' tidak bisa dicocokkan tanpa Kecamatan yang valid.";
                    }
                }
            }

            if ($rowKecId === null && ($kecNama === null || $kecNama === '')) {
                $rowKecId = (int)$kps['kecamatan_id'];
            }
            if ($rowDesaId === null && ($desaNama === null || $desaNama === '')) {
                $rowDesaId = (int)$kps['desa_id'];
            }

            $params = [
                $kpsId,
                $kategori,
                $getVal($row, 'no_andil'),
                $nama,
                $getVal($row, 'no_kk'),
                $getVal($row, 'nik'),
                $getVal($row, 'koordinat_bt'),
                $getVal($row, 'koordinat_ls'),
                $getVal($row, 'batas_barat'),
                $getVal($row, 'batas_utara'),
                $getVal($row, 'batas_selatan'),
                $getVal($row, 'batas_timur'),
                $rowDesaId,
                $rowKecId,
                $getVal($row, 'pengukur'),
                $tanggal,
                $getVal($row, 'komoditas'),
                $luas
            ];

            $stmt->execute($params);
            $imported++;
        }

        $pdo->commit();
        @unlink($tempFilePath);

        log_activity($pdo, 'kps_anggota', 'import', 'Import anggota KPS: ' . $imported . ' baris berhasil, ' . $skipped . ' dilewati');

        $msg = "Berhasil mengimpor $imported anggota KPS.";
        if ($skipped > 0) $msg .= " $skipped baris dilewati karena Nama kosong.";
        set_flash('success', $msg);
        
        if (!empty($warnings)) {
            $warningsSummary = array_slice($warnings, 0, 5);
            if (count($warnings) > 5) {
                $warningsSummary[] = "...dan " . (count($warnings) - 5) . " peringatan wilayah lainnya.";
            }
            set_flash('warning', implode('<br>', $warningsSummary));
        }

        header('Location: ' . APP_URL . '/kps/' . $kpsId . '/anggota');
        exit;
    }

    private function parseXlsxForAll(string $file): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $ws = $spreadsheet->getActiveSheet();

        $rows = [];
        $maxRow = $ws->getHighestDataRow();
        $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestDataColumn());

        for ($row = 2; $row <= $maxRow; $row++) {
            $r = [];
            for ($col = 1; $col <= $maxCol; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cell = $ws->getCell($colLetter . $row);
                $r[$col - 1] = $cell->getValue();
            }
            if (array_filter($r, fn($v) => $v !== null && $v !== '') === []) continue;
            $rows[] = $r;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return $rows;
    }

    private function parseCsvForAll(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        $headerSkipped = false;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (!$headerSkipped) { $headerSkipped = true; continue; }
            $rows[] = array_values($data);
        }
        fclose($handle);
        return $rows;
    }

    private function parseImportDate($val): ?string {
        if (empty($val)) return null;
        if (is_numeric($val)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $d = \DateTimeImmutable::createFromFormat($fmt, trim((string)$val));
            if ($d instanceof \DateTimeImmutable) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }
}

