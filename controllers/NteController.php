<?php

declare(strict_types=1);

class NteController
{
    private function pdo(): \PDO { return Database::connect(); }
    private function model(): Nte { return new Nte($this->pdo()); }
    private function opKabId(): ?int { return user_role() === 'operator' ? user_kabupaten_id() : null; }

    public function index(): void
    {
        requireLogin();
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $kabFilter = (int) ($_GET['kabupaten_id'] ?? 0);
        $tahun     = (int) ($_GET['tahun'] ?? 0);
        $bulan     = (int) ($_GET['bulan'] ?? 0);
        $q         = trim((string) ($_GET['q'] ?? ''));

        $filters = [];
        if ($q !== '') $filters['q'] = $q;
        if (in_array($tahun, Nte::availableYears(), true)) $filters['tahun'] = $tahun;
        if ($bulan >= 1 && $bulan <= 12) $filters['bulan'] = $bulan;

        $opKab = $this->opKabId();
        if ($opKab !== null) $filters['operator_kab_id'] = $opKab;
        elseif ($kabFilter > 0) $filters['kabupaten_id'] = $kabFilter;

        $result = $this->model()->paginateIndex($page, 50, $filters);
        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();
        $rekap = $this->model()->rekap($filters);

        $pageTitle = 'Data NTE';
        $activeNav = 'nte';
        $filterKab = $kabFilter;
        $filterTahun = $tahun;
        $filterBulan = $bulan;
        $filterQ = $q;
        $yearOptions = Nte::availableYears();
        $bulanOptions = Nte::bulanList();

        ob_start();
        require view_path('nte/index.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function importForm(): void
    {
        requireLogin();
        require_can_mutate_data();

        $pageTitle = 'Import NTE dari Excel';
        $activeNav = 'nte';
        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();
        if ($this->opKabId() !== null) {
            $kabupatenList = array_values(array_filter($kabupatenList,
                fn ($r) => (int) $r['id'] === $this->opKabId()
            ));
        }

        ob_start();
        require view_path('nte/import.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function importProcess(): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();

        $pdo = $this->pdo();

        // Validasi upload
        if (!isset($_FILES['nte_file']) || (int) ($_FILES['nte_file']['error'] ?? 4) !== UPLOAD_ERR_OK) {
            set_flash('error', 'File tidak valid atau gagal diupload.');
            header('Location: ' . APP_URL . '/nte/import');
            exit;
        }

        $file = $_FILES['nte_file']['tmp_name'];
        $ext  = strtolower(pathinfo((string) $_FILES['nte_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            set_flash('error', 'Format file harus .xlsx, .xls, atau .csv');
            header('Location: ' . APP_URL . '/nte/import');
            exit;
        }

        // Ambil kabupaten list untuk lookup nama → id
        $kabRows = $pdo->query("SELECT id, UPPER(TRIM(nama)) AS nama FROM kabupaten")->fetchAll(\PDO::FETCH_ASSOC);
        $kabMap  = []; // 'BOJONEGORO' => 1
        foreach ($kabRows as $r) $kabMap[$r['nama']] = (int) $r['id'];

        // Ambil KTH list untuk resolve kth_id by nama + kabupaten_id
        $kthRows = $pdo->query("SELECT id, kabupaten_id, LOWER(TRIM(nama)) AS nama FROM kth WHERE is_active = 1")->fetchAll(\PDO::FETCH_ASSOC);
        $kthMap  = []; // 'kabId|nama_lower' => kth_id
        foreach ($kthRows as $r) $kthMap[$r['kabupaten_id'] . '|' . $r['nama']] = (int) $r['id'];

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCsv($file);
            } else {
                $rows = $this->parseXlsx($file);
            }
        } catch (\Throwable $e) {
            set_flash('error', 'Gagal membaca file: ' . $e->getMessage());
            header('Location: ' . APP_URL . '/nte/import');
            exit;
        }

        if (empty($rows)) {
            set_flash('error', 'File kosong atau tidak ada data yang bisa dibaca.');
            header('Location: ' . APP_URL . '/nte/import');
            exit;
        }

        // Parse dan validasi baris
        $toInsert = [];
        $skipped  = 0;
        $errors   = [];

        // Kolom dari Excel (0-indexed):
        // 0=No, 1=Provinsi, 2=Kab/Kota, 3=Nama Kelompok, 4=Tahun, 5=Bulan,
        // 6=Barang/Jasa, 7=Jenis Produk, 8=Jumlah Penjualan, 9=Satuan, 10=NTE KTH(Rp), 11=Penyuluh

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2; // baris ke-2 = data pertama (baris 1 = header)

            // Skip baris header atau kosong
            if (empty($row[2]) || empty($row[3])) { $skipped++; continue; }
            if (is_string($row[2]) && strtolower(trim($row[2])) === 'kab/kota') { $skipped++; continue; }

            $kabNama    = strtoupper(trim((string) ($row[2] ?? '')));
            $namaKth    = trim((string) ($row[3] ?? ''));
            $tahun      = (int) ($row[4] ?? 0);
            $bulan      = (int) ($row[5] ?? 0);
            $jenisBarang = trim((string) ($row[6] ?? ''));
            $produk     = trim((string) ($row[7] ?? ''));
            $jumlah     = $row[8] !== '' && $row[8] !== null ? (float) $row[8] : null;
            $satuan     = trim((string) ($row[9] ?? ''));
            $nilaiRp    = (int) str_replace([',', '.', ' '], '', (string) ($row[10] ?? 0));
            $penyuluh   = trim((string) ($row[11] ?? ''));

            if (!$tahun || $bulan < 1 || $bulan > 12 || $namaKth === '' || $jenisBarang === '') {
                $errors[] = "Baris $lineNum: data tidak lengkap, dilewati.";
                $skipped++;
                continue;
            }

            $kabId = $kabMap[$kabNama] ?? null;
            $kthId = null;
            if ($kabId !== null) {
                $kthId = $kthMap[$kabId . '|' . strtolower($namaKth)] ?? null;
            }

            $toInsert[] = [
                'kth_id'      => $kthId,
                'nama_kth'    => $namaKth,
                'kabupaten_id'=> $kabId,
                'tahun'       => $tahun,
                'bulan'       => $bulan,
                'jenis_barang'=> $jenisBarang,
                'produk'      => $produk !== '' ? $produk : null,
                'jumlah'      => $jumlah,
                'satuan'      => $satuan !== '' ? $satuan : null,
                'nilai_rp'    => $nilaiRp,
                'penyuluh'    => $penyuluh !== '' ? $penyuluh : null,
            ];
        }

        if (empty($toInsert)) {
            set_flash('error', 'Tidak ada data valid untuk diimport. ' . count($errors) . ' baris dilewati.');
            header('Location: ' . APP_URL . '/nte/import');
            exit;
        }

        $inserted = $this->model()->bulkInsert($toInsert, 500);

        log_activity($pdo, 'nte', 'import', 'Import NTE: ' . $inserted . ' baris berhasil, ' . $skipped . ' dilewati');

        $msg = "Berhasil mengimport $inserted transaksi.";
        if ($skipped > 0) $msg .= " $skipped baris dilewati.";
        set_flash('success', $msg);
        header('Location: ' . APP_URL . '/nte');
        exit;
    }

    /** Parse XLSX menggunakan PhpSpreadsheet */
    private function parseXlsx(string $file): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $ws = $spreadsheet->getActiveSheet();

        $rows = [];
        $maxRow = $ws->getHighestDataRow();

        // Skip header row (row 1)
        for ($row = 2; $row <= $maxRow; $row++) {
            $r = [];
            for ($col = 1; $col <= 12; $col++) {
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

    /** Parse CSV */
    private function parseCsv(string $file): array
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

    public function delete(int $id): void
    {
        requireLogin();
        require_can_mutate_data();
        verify_csrf();
        $pdo = $this->pdo();
        $existing = $this->model()->findById($id);
        if ($existing === false) { http_response_code(404); exit; }
        $this->model()->delete($id);
        log_activity($pdo, 'nte', 'delete', 'Hapus NTE ID ' . $id);
        set_flash('success', 'Data NTE berhasil dihapus.');
        header('Location: ' . APP_URL . '/nte');
        exit;
    }
}
