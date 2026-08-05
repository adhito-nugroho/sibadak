<?php

declare(strict_types=1);

function generateLaporanPdf(
    string $jenis,
    int $bulan,
    int $tahun,
    string $bulanNama,
    \DateTimeImmutable $tgl,
    array $komoditas,
    array $rekap,
    array $lampiran,
    array $targets
): void {
    $hariList = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
                 'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    $hariNama = $hariList[$tgl->format('l')];

    $judulJenis = $jenis === 'hhk' ? 'HASIL HUTAN KAYU' : 'HASIL HUTAN BUKAN KAYU';
    $judulKomoditas = $jenis === 'hhk' ? 'Jenis Hasil Hutan Kayu' : 'Jenis Komoditas HHBK';
    $judulVolume = $jenis === 'hhk' ? 'Volume (M3)' : 'Volume (Kg/Btg)';

    // ── Init TCPDF ────────────────────────────────────────────────────────
    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('SIBADAK');
    $pdf->SetTitle('Laporan ' . strtoupper($jenis) . ' ' . $bulanNama . ' ' . $tahun);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->SetFont('helvetica', '', 9);

    // ════════════════════════════════════════════════════════════════
    // HALAMAN 1 — Berita Acara (Portrait)
    // ════════════════════════════════════════════════════════════════
    $pdf->SetPageOrientation('P');
    $pdf->AddPage('P');

    // Judul
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->MultiCell(0, 6, 'BERITA ACARA REKONSILIASI', 0, 'C');
    $pdf->MultiCell(0, 6, 'PRODUKSI ' . $judulJenis . ' YANG BERASAL DARI HUTAN HAK', 0, 'C');
    $pdf->MultiCell(0, 6, 'ANTARA BIDANG PHL DENGAN CDK WILAYAH BOJONEGORO', 0, 'C');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->MultiCell(0, 6, 'BAGIAN BULAN : ' . strtoupper($bulanNama) . ' ' . $tahun, 0, 'C');
    $pdf->Ln(6);

    // Narasi
    $tglHuruf = angkaKeHurufPdf((int)$tgl->format('j'));
    $bulanTtd = bulanIndoPdf((int)$tgl->format('n'));
    $tahunHuruf = angkaKeHurufPdf((int)$tgl->format('Y'));
    $jenisNarasi = $jenis === 'hhk' ? 'hasil hutan kayu' : 'hasil hutan bukan kayu';

    $pdf->SetFont('helvetica', '', 10);
    $narasi = "Pada hari ini, {$hariNama} tanggal {$tglHuruf} bulan {$bulanTtd} tahun {$tahunHuruf}, "
        . "telah dilaksanakan rekonsiliasi produksi {$jenisNarasi} yang berasal dari hutan hak bagian bulan {$bulanNama} "
        . "tahun {$tahunHuruf} antara Bidang Pengelolaan Hutan Lestari dengan Cabang Dinas Kehutanan (CDK) Wilayah Bojonegoro, "
        . "Wilayah Kerja : Kabupaten Bojonegoro, Kabupaten Tuban, Kabupaten Lamongan dan Kabupaten Gresik dengan hasil sebagai berikut :";
    $pdf->MultiCell(0, 6, $narasi, 0, 'J');
    $pdf->Ln(4);

    // Sub judul
    $pdf->SetFont('helvetica', 'BU', 10);
    $pdf->Cell(0, 6, ($jenis === 'hhk' ? 'Hasil Hutan Kayu' : 'Hasil Hutan Bukan Kayu'), 0, 1);
    $pdf->Ln(2);

    // Header tabel Berita Acara
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(173, 216, 230);

    if ($jenis === 'hhk') {
        $y = $pdf->GetY();
        // Row 1
        $pdf->Cell(8,  12, 'No', 1, 0, 'C', true);
        $pdf->Cell(45, 12, $judulKomoditas, 1, 0, 'C', true);
        $pdf->Cell(52, 6,  $judulVolume, 1, 0, 'C', true);
        $pdf->Cell(30, 12, 'Target DPA '.$tahun, 1, 0, 'C', true);
        $pdf->Cell(28, 12, '% Terhadap Target', 1, 0, 'C', true);
        $pdf->Cell(27, 12, 'Keterangan', 1, 0, 'C', true);

        // Row 2
        $pdf->SetXY(63, $y + 6);
        $pdf->Cell(26, 6, 'Bulan '.$bulanNama, 1, 0, 'C', true);
        $pdf->Cell(26, 6, 's/d Bulan '.$bulanNama, 1, 0, 'C', true);

        // Set cursor to start of data rows
        $pdf->SetXY(10, $y + 12);
    } else {
        $y = $pdf->GetY();
        // Row 1
        $pdf->Cell(8,  12, 'No', 1, 0, 'C', true);
        $pdf->Cell(75, 12, $judulKomoditas, 1, 0, 'C', true);
        $pdf->Cell(70, 6,  $judulVolume, 1, 0, 'C', true);
        $pdf->Cell(37, 12, 'Keterangan', 1, 0, 'C', true);

        // Row 2
        $pdf->SetXY(93, $y + 6);
        $pdf->Cell(35, 6, 'Bulan '.$bulanNama, 1, 0, 'C', true);
        $pdf->Cell(35, 6, 's/d Bulan '.$bulanNama, 1, 0, 'C', true);

        // Set cursor to start of data rows
        $pdf->SetXY(10, $y + 12);
    }

    // Data rows
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetFillColor(255, 255, 255);
    $no = 1;
    $totBulanIni = 0; $totSd = 0; $totTarget = 0;

    foreach ($komoditas as $k) {
        $nama = $k['nama'];
        $bIni = $rekap[$nama]['bulan_ini'] ?? 0;
        $sdIni = $rekap[$nama]['sd_bulan_ini'] ?? 0;
        $target = $targets[$nama] ?? 0;
        $pct = $target > 0 ? round($sdIni / $target * 100, 2) : 0;
        $totBulanIni += $bIni; $totSd += $sdIni; $totTarget += $target;

        if ($jenis === 'hhk') {
            $pdf->Cell(8, 6, $no++, 1, 0, 'C');
            $pdf->Cell(45, 6, $nama, 1, 0, 'L');
            $pdf->Cell(26, 6, $bIni > 0 ? number_format($bIni,2,',','.') : '-', 1, 0, 'R');
            $pdf->Cell(26, 6, $sdIni > 0 ? number_format($sdIni,2,',','.') : '-', 1, 0, 'R');
            $pdf->Cell(30, 6, $target > 0 ? number_format($target,2,',','.') : '', 1, 0, 'R');
            $pdf->Cell(28, 6, $pct > 0 ? number_format($pct,2,',','.') : '', 1, 0, 'R');
            $pdf->Cell(27, 6, '', 1, 1);
        } else {
            $pdf->Cell(8, 6, $no++, 1, 0, 'C');
            $pdf->Cell(75, 6, $nama, 1, 0, 'L');
            $pdf->Cell(35, 6, $bIni > 0 ? number_format($bIni,2,',','.') : '-', 1, 0, 'R');
            $pdf->Cell(35, 6, $sdIni > 0 ? number_format($sdIni,2,',','.') : '-', 1, 0, 'R');
            $pdf->Cell(37, 6, '', 1, 1);
        }
    }

    // Total row
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(242, 242, 242);
    if ($jenis === 'hhk') {
        $pdf->Cell(8, 6, '', 1, 0, 'C', true);
        $pdf->Cell(45, 6, 'Jumlah', 1, 0, 'L', true);
        $pdf->Cell(26, 6, number_format($totBulanIni,2,',','.'), 1, 0, 'R', true);
        $pdf->Cell(26, 6, number_format($totSd,2,',','.'), 1, 0, 'R', true);
        $pdf->Cell(30, 6, $totTarget > 0 ? number_format($totTarget,2,',','.') : '', 1, 0, 'R', true);
        $totPct = $totTarget > 0 ? round($totSd/$totTarget*100,2) : 0;
        $pdf->Cell(28, 6, $totPct > 0 ? number_format($totPct,2,',','.') : '', 1, 0, 'R', true);
        $pdf->Cell(27, 6, '', 1, 1, '', true);
    } else {
        $pdf->Cell(8, 6, '', 1, 0, 'C', true);
        $pdf->Cell(75, 6, 'Jumlah', 1, 0, 'L', true);
        $pdf->Cell(35, 6, number_format($totBulanIni,2,',','.'), 1, 0, 'R', true);
        $pdf->Cell(35, 6, number_format($totSd,2,',','.'), 1, 0, 'R', true);
        $pdf->Cell(37, 6, '', 1, 1, '', true);
    }

    // ════════════════════════════════════════════════════════════════
    // HALAMAN 2 — Lampiran (Landscape)
    // ════════════════════════════════════════════════════════════════
    $pdf->AddPage('L');
    $pdf->SetFont('helvetica', '', 7);

    // Judul lampiran
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(0, 5, 'REKAPITULASI PRODUKSI ' . $judulJenis . ' DARI HUTAN HAK', 0, 'C');
    $pdf->MultiCell(0, 5, 'DINAS KEHUTANAN PROVINSI JAWA TIMUR', 0, 'C');
    $pdf->MultiCell(0, 5, 'BULAN : ' . strtoupper($bulanNama) . ' ' . $tahun, 0, 'C');
    $pdf->Ln(3);

    // Hitung lebar kolom dinamis
    $pageW = $pdf->getPageWidth() - 20; // margin 10 kiri + kanan
    $fixedW = 8 + 8 + 22 + 22 + 22 + 28 + 25; // NO+CDK+KAB+KEC+DESA+KTH+PENYULUH
    $jumlahKomoditas = count($komoditas);
    $kolTotal = $jenis === 'hhk' ? 3 : 4; // kolom total di akhir
    $totalTailW = $jumlahKomoditas > 0 ? ($pageW - $fixedW - ($kolTotal * 16)) : 0;
    $colW = $jumlahKomoditas > 0 ? max(12, (int)($totalTailW / $jumlahKomoditas)) : 12;

    // Header lampiran
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetFillColor(173, 216, 230);
    $h = 10;
    $pdf->Cell(8,  $h, 'NO.',   1, 0, 'C', true);
    $pdf->Cell(8,  $h, 'CDK',   1, 0, 'C', true);
    $pdf->Cell(22, $h, 'KAB/KOTA',1, 0, 'C', true);
    $pdf->Cell(22, $h, 'KEC.',  1, 0, 'C', true);
    $pdf->Cell(22, $h, 'DESA',  1, 0, 'C', true);
    $pdf->Cell(28, $h, 'KTH',   1, 0, 'C', true);
    $pdf->Cell(25, $h, 'PENYULUH',1,0, 'C', true);
    foreach ($komoditas as $k) {
        $shortName = mb_strlen($k['nama']) > 8 ? mb_substr($k['nama'],0,7).'.' : $k['nama'];
        $pdf->Cell($colW, $h, $shortName, 1, 0, 'C', true);
    }
    if ($jenis === 'hhk') {
        $pdf->Cell(16, $h, 'JML BLN INI', 1, 0, 'C', true);
        $pdf->Cell(16, $h, 's.d. BLN LALU', 1, 0, 'C', true);
        $pdf->Cell(16, $h, 's.d. BLN INI', 1, 1, 'C', true);
    } else {
        $pdf->Cell(16, $h, 'BTG BLN INI', 1, 0, 'C', true);
        $pdf->Cell(16, $h, 'KG BLN INI', 1, 0, 'C', true);
        $pdf->Cell(16, $h, 's.d. BTG', 1, 0, 'C', true);
        $pdf->Cell(16, $h, 's.d. KG', 1, 1, 'C', true);
    }

    // Data lampiran
    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetFillColor(255, 255, 255);
    $no = 1;

    if (empty($lampiran)) {
        $colspan = 7 + $jumlahKomoditas + $kolTotal;
        $pdf->Cell(0, 7, 'Tidak ada data untuk periode ini.', 1, 1, 'C');
    } else {
        foreach ($lampiran as $item) {
            $rh = 6;
            $pdf->Cell(8,  $rh, $no++,                                    1, 0, 'C');
            $pdf->Cell(8,  $rh, 'CDK BJN',                                1, 0, 'C');
            $pdf->Cell(22, $rh, mb_strimwidth($item['kabupaten']??'',0,14,'..'), 1, 0, 'L');
            $pdf->Cell(22, $rh, mb_strimwidth($item['kecamatan']??'',0,14,'..'), 1, 0, 'L');
            $pdf->Cell(22, $rh, mb_strimwidth($item['desa']??'',0,14,'..'),      1, 0, 'L');
            $pdf->Cell(28, $rh, mb_strimwidth($item['nama_kth']??'',0,18,'..'), 1, 0, 'L');
            $pdf->Cell(25, $rh, mb_strimwidth($item['nama_penyuluh']??'',0,16,'..'), 1, 0, 'L');
            foreach ($komoditas as $k) {
                $v = $item['detail'][$k['nama']]['bulan_ini'] ?? 0;
                $pdf->Cell($colW, $rh, $v > 0 ? number_format($v,2,',','.') : '', 1, 0, 'R');
            }
            if ($jenis === 'hhk') {
                $pdf->Cell(16, $rh, number_format((float)($item['total_bulan_ini_m3']??0),2,',','.'), 1, 0, 'R');
                $pdf->Cell(16, $rh, number_format((float)($item['total_sd_bulan_lalu_m3']??0),2,',','.'), 1, 0, 'R');
                $pdf->Cell(16, $rh, number_format((float)($item['total_sd_bulan_ini_m3']??0),2,',','.'), 1, 1, 'R');
            } else {
                $pdf->Cell(16, $rh, number_format((float)($item['total_btg_bulan_ini']??0),2,',','.'), 1, 0, 'R');
                $pdf->Cell(16, $rh, number_format((float)($item['total_kg_bulan_ini']??0),2,',','.'), 1, 0, 'R');
                $pdf->Cell(16, $rh, number_format((float)($item['total_btg_sd_bulan_ini']??0),2,',','.'), 1, 0, 'R');
                $pdf->Cell(16, $rh, number_format((float)($item['total_kg_sd_bulan_ini']??0),2,',','.'), 1, 1, 'R');
            }
        }
    }

    // Output
    $filename = 'Laporan_' . strtoupper($jenis) . '_' . $bulanNama . '_' . $tahun . '.pdf';
    $pdf->Output($filename, 'D');
    exit;
}

// ── Helpers ──────────────────────────────────────────────────────────────

function bulanIndoPdf(int $b): string {
    return ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$b] ?? '';
}

function angkaKeHurufPdf(int $n): string {
    $satuan = ['','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas'];
    if ($n < 12) return $satuan[$n];
    if ($n < 20) return $satuan[$n - 10] . ' Belas';
    if ($n < 100) return $satuan[(int)($n/10)] . ' Puluh' . ($n%10 ? ' '.$satuan[$n%10] : '');
    if ($n < 200) return 'Seratus' . ($n%100 ? ' '.angkaKeHurufPdf($n%100) : '');
    if ($n < 1000) return $satuan[(int)($n/100)] . ' Ratus' . ($n%100 ? ' '.angkaKeHurufPdf($n%100) : '');
    if ($n < 2000) return 'Seribu' . ($n%1000 ? ' '.angkaKeHurufPdf($n%1000) : '');
    return $satuan[(int)($n/1000)] . ' Ribu' . ($n%1000 ? ' '.angkaKeHurufPdf($n%1000) : '');
}
