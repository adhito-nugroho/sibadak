<?php

declare(strict_types=1);

class LaporanController
{
    private function pdo(): \PDO { return Database::connect(); }
    private function hhkModel(): LaporanHhk { return new LaporanHhk($this->pdo()); }
    private function hhbkModel(): LaporanHhbk { return new LaporanHhbk($this->pdo()); }

    private static function bulanIndo(int $b): string {
        return ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$b] ?? '';
    }

    private static function angkaKeHuruf(int $n): string {
        $satuan = ['','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas'];
        if ($n < 12) return $satuan[$n];
        if ($n < 20) return $satuan[$n - 10] . ' Belas';
        if ($n < 100) return $satuan[(int)($n/10)] . ' Puluh' . ($n%10 ? ' ' . $satuan[$n%10] : '');
        if ($n < 200) return 'Seratus' . ($n%100 ? ' ' . self::angkaKeHuruf($n%100) : '');
        if ($n < 1000) return $satuan[(int)($n/100)] . ' Ratus' . ($n%100 ? ' ' . self::angkaKeHuruf($n%100) : '');
        if ($n < 2000) return 'Seribu' . ($n%1000 ? ' ' . self::angkaKeHuruf($n%1000) : '');
        return $satuan[(int)($n/1000)] . ' Ribu' . ($n%1000 ? ' ' . self::angkaKeHuruf($n%1000) : '');
    }

    /** Dashboard laporan */
    public function index(): void
    {
        requireLogin();
        $hhkKomoditas = $this->hhkModel()->getKomoditas(false);
        $hhbkKomoditas = $this->hhbkModel()->getKomoditas(false);
        $kabupatenList = (new Kth($this->pdo()))->listKabupatenForFilter();
        $yearOptions = range((int)date('Y'), 2020);
        $bulanOptions = ['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni',
                         '7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];

        $pageTitle = 'Laporan HHK & HHBK';
        $activeNav = 'laporan';

        ob_start();
        require view_path('laporan/index.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    /** Kelola master komoditas HHK */
    public function komoditasHhk(): void
    {
        requireLogin();
        require_can_mutate_data();
        $komoditas = $this->hhkModel()->getKomoditas(false);
        $tahun = (int) date('Y');
        $targets = $this->hhkModel()->getTargetDpa($tahun);

        $pageTitle = 'Komoditas & Target HHK';
        $activeNav = 'laporan';
        ob_start();
        require view_path('laporan/komoditas_hhk.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function saveKomoditasHhk(): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $m = $this->hhkModel();
        $nama = trim(req_str('nama'));
        $urutan = (int) req_str('urutan');
        if ($nama === '') { set_flash('error', 'Nama komoditas wajib diisi.'); header('Location: ' . APP_URL . '/laporan/komoditas-hhk'); exit; }
        $m->saveKomoditas($nama, $urutan > 0 ? $urutan : 99);
        log_activity($pdo, 'laporan', 'komoditas_hhk', 'Tambah/update komoditas HHK: ' . $nama);
        set_flash('success', 'Komoditas berhasil disimpan.');
        header('Location: ' . APP_URL . '/laporan/komoditas-hhk'); exit;
    }

    public function toggleKomoditasHhk(int $id): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $this->hhkModel()->toggleKomoditas($id);
        header('Location: ' . APP_URL . '/laporan/komoditas-hhk'); exit;
    }

    public function saveTargetHhk(): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $m = $this->hhkModel();
        $tahun = (int) req_str('tahun');
        $komoditas = $this->hhkModel()->getKomoditas();
        foreach ($komoditas as $k) {
            $target = (float) req_str('target_' . $k['id']);
            $m->saveTargetDpa($tahun, (int) $k['id'], $target);
        }
        log_activity($pdo, 'laporan', 'target_hhk', 'Update target DPA HHK tahun ' . $tahun);
        set_flash('success', 'Target DPA berhasil disimpan.');
        header('Location: ' . APP_URL . '/laporan/komoditas-hhk'); exit;
    }

    /** Kelola master komoditas HHBK */
    public function komoditasHhbk(): void
    {
        requireLogin(); require_can_mutate_data();
        $komoditas = $this->hhbkModel()->getKomoditas(false);
        $tahun = (int) date('Y');
        $targets = $this->hhbkModel()->getTargetDpa($tahun);

        $pageTitle = 'Komoditas & Target HHBK';
        $activeNav = 'laporan';
        ob_start();
        require view_path('laporan/komoditas_hhbk.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    public function saveKomoditasHhbk(): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $nama = trim(req_str('nama'));
        $satuan = req_str('satuan');
        $urutan = (int) req_str('urutan');
        if ($nama === '' || !in_array($satuan, ['Kg','Btg'], true)) {
            set_flash('error', 'Nama dan satuan wajib diisi.'); header('Location: ' . APP_URL . '/laporan/komoditas-hhbk'); exit;
        }
        $this->hhbkModel()->saveKomoditas($nama, $satuan, $urutan > 0 ? $urutan : 99);
        log_activity($pdo, 'laporan', 'komoditas_hhbk', 'Tambah/update komoditas HHBK: ' . $nama);
        set_flash('success', 'Komoditas berhasil disimpan.');
        header('Location: ' . APP_URL . '/laporan/komoditas-hhbk'); exit;
    }

    public function toggleKomoditasHhbk(int $id): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $this->hhbkModel()->toggleKomoditas($id);
        header('Location: ' . APP_URL . '/laporan/komoditas-hhbk'); exit;
    }

    public function saveTargetHhbk(): void
    {
        requireLogin(); require_can_mutate_data(); verify_csrf();
        $pdo = $this->pdo();
        $m = $this->hhbkModel();
        $tahun = (int) req_str('tahun');
        $komoditas = $this->hhbkModel()->getKomoditas();
        foreach ($komoditas as $k) {
            $target = (float) req_str('target_' . $k['id']);
            $m->saveTargetDpa($tahun, (int) $k['id'], $target);
        }
        log_activity($pdo, 'laporan', 'target_hhbk', 'Update target DPA HHBK tahun ' . $tahun);
        set_flash('success', 'Target DPA berhasil disimpan.');
        header('Location: ' . APP_URL . '/laporan/komoditas-hhbk'); exit;
    }

    /** Preview laporan di browser */
    public function preview(): void
    {
        requireLogin();
        $jenis = (isset($_GET['jenis']) && $_GET['jenis'] === 'hhbk') ? 'hhbk' : 'hhk';
        $bulan = max(1, min(12, (int) ($_GET['bulan'] ?? date('n'))));
        $tahun = (int) ($_GET['tahun'] ?? date('Y'));
        $tanggalTtd = trim((string) ($_GET['tanggal_ttd'] ?? ''));
        if ($tanggalTtd === '') $tanggalTtd = date('Y-m-d');
        try { $tgl = new \DateTimeImmutable($tanggalTtd); } catch (\Exception $e) { $tgl = new \DateTimeImmutable(); }

        $m = $jenis === 'hhk' ? $this->hhkModel() : $this->hhbkModel();
        $komoditas = $m->getKomoditas();
        $rekap = $m->getRekap($bulan, $tahun);
        $lampiran = $m->getLampiran($bulan, $tahun);
        $targets = $m->getTargetDpa($tahun);
        $bulanNama = self::bulanIndo($bulan);

        $pageTitle = 'Preview Laporan ' . strtoupper($jenis);
        $activeNav = 'laporan';
        ob_start();
        require view_path('laporan/preview_' . $jenis . '.php');
        $content = ob_get_clean();
        require view_path('layouts/main.php');
    }

    /** Export PDF */
    public function exportPdf(): void
    {
        requireLogin();
        $jenis = (isset($_GET['jenis']) && $_GET['jenis'] === 'hhbk') ? 'hhbk' : 'hhk';
        $bulan = max(1, min(12, (int) ($_GET['bulan'] ?? date('n'))));
        $tahun = (int) ($_GET['tahun'] ?? date('Y'));
        $tanggalTtd = trim((string) ($_GET['tanggal_ttd'] ?? date('Y-m-d')));
        try { $tgl = new \DateTimeImmutable($tanggalTtd); } catch (\Exception $e) { $tgl = new \DateTimeImmutable(); }

        $m = $jenis === 'hhk' ? $this->hhkModel() : $this->hhbkModel();
        $komoditas = $m->getKomoditas();
        $rekap = $m->getRekap($bulan, $tahun);
        $lampiran = $m->getLampiran($bulan, $tahun);
        $targets = $m->getTargetDpa($tahun);
        $bulanNama = self::bulanIndo($bulan);

        require_once app_path('helpers/laporan_pdf.php');
        generateLaporanPdf($jenis, $bulan, $tahun, $bulanNama, $tgl, $komoditas, $rekap, $lampiran, $targets);
    }

    /** Export Excel */
    public function exportExcel(): void
    {
        requireLogin();
        $jenis = (isset($_GET['jenis']) && $_GET['jenis'] === 'hhbk') ? 'hhbk' : 'hhk';
        $bulan = max(1, min(12, (int) ($_GET['bulan'] ?? date('n'))));
        $tahun = (int) ($_GET['tahun'] ?? date('Y'));

        $m = $jenis === 'hhk' ? $this->hhkModel() : $this->hhbkModel();
        $komoditas = $m->getKomoditas();
        $rekap = $m->getRekap($bulan, $tahun);
        $lampiran = $m->getLampiran($bulan, $tahun);
        $targets = $m->getTargetDpa($tahun);
        $bulanNama = self::bulanIndo($bulan);

        require_once app_path('helpers/laporan_excel.php');
        generateLaporanExcel($jenis, $bulan, $tahun, $bulanNama, $komoditas, $rekap, $lampiran, $targets);
    }

    public static function bulanIndoPublic(int $b): string { return self::bulanIndo($b); }
    public static function angkaKeHurufPublic(int $n): string { return self::angkaKeHuruf($n); }
}
