<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function generateLaporanExcel(string $jenis, int $bulan, int $tahun, string $bulanNama, array $komoditas, array $rekap, array $lampiran, array $targets): void
{
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()->setTitle('Laporan ' . strtoupper($jenis) . ' ' . $bulanNama . ' ' . $tahun);

    // ── Sheet 1: Berita Acara ─────────────────────────────────────────────
    $ws1 = $spreadsheet->getActiveSheet()->setTitle('Berita Acara');
    $judulJenis = $jenis === 'hhk' ? 'HASIL HUTAN KAYU' : 'HASIL HUTAN BUKAN KAYU';
    $ws1->setCellValue('A1', 'BERITA ACARA REKONSILIASI');
    $ws1->setCellValue('A2', 'PRODUKSI ' . $judulJenis . ' YANG BERASAL DARI HUTAN HAK');
    $ws1->setCellValue('A3', 'ANTARA BIDANG PHL DENGAN CDK WILAYAH BOJONEGORO');
    $ws1->setCellValue('A4', 'BAGIAN BULAN : ' . strtoupper($bulanNama) . ' ' . $tahun);

    foreach (['A1','A2','A3','A4'] as $cell) {
        $ws1->getStyle($cell)->getFont()->setBold(true)->setSize(11);
        $ws1->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    $row = 6;
    // Header tabel
    $ws1->setCellValue('A' . $row, 'No');
    $ws1->setCellValue('B' . $row, $jenis === 'hhk' ? 'Jenis Hasil Hutan Kayu' : 'Jenis Komoditas HHBK');
    if ($jenis === 'hhk') {
        $ws1->setCellValue('C' . $row, 'Bulan ' . $bulanNama);
        $ws1->setCellValue('D' . $row, 's/d Bulan ' . $bulanNama);
        $ws1->setCellValue('E' . $row, 'Target DPA ' . $tahun);
        $ws1->setCellValue('F' . $row, '% Target');
        $ws1->setCellValue('G' . $row, 'Keterangan');
    } else {
        $ws1->setCellValue('C' . $row, 'Bulan ' . $bulanNama);
        $ws1->setCellValue('D' . $row, 's/d Bulan ' . $bulanNama);
        $ws1->setCellValue('E' . $row, 'Keterangan');
    }

    $lastCol = $jenis === 'hhk' ? 'G' : 'E';
    $ws1->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
    $ws1->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');
    $ws1->getStyle('A' . $row . ':' . $lastCol . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $no = 1;
    $totBulanIni = 0; $totSdBulanIni = 0; $totTarget = 0;
    $row++;

    foreach ($komoditas as $k) {
        $nama = $k['nama'];
        $bulanIni = $rekap[$nama]['bulan_ini'] ?? 0;
        $sdBulanIni = $rekap[$nama]['sd_bulan_ini'] ?? 0;
        $target = $targets[$nama] ?? 0;
        $pct = $target > 0 ? round($sdBulanIni / $target * 100, 2) : 0;

        $ws1->setCellValue('A' . $row, $no++);
        $ws1->setCellValue('B' . $row, $nama);
        $ws1->setCellValue('C' . $row, $bulanIni > 0 ? $bulanIni : '-');
        $ws1->setCellValue('D' . $row, $sdBulanIni > 0 ? $sdBulanIni : '-');
        if ($jenis === 'hhk') {
            $ws1->setCellValue('E' . $row, $target > 0 ? $target : '');
            $ws1->setCellValue('F' . $row, $pct > 0 ? $pct : '');
        }
        $totBulanIni += $bulanIni;
        $totSdBulanIni += $sdBulanIni;
        $totTarget += $target;
        $row++;
    }

    // Total row
    $ws1->setCellValue('A' . $row, '');
    $ws1->setCellValue('B' . $row, 'Jumlah');
    $ws1->setCellValue('C' . $row, round($totBulanIni, 2));
    $ws1->setCellValue('D' . $row, round($totSdBulanIni, 2));
    if ($jenis === 'hhk') {
        $ws1->setCellValue('E' . $row, round($totTarget, 2));
        $totPct = $totTarget > 0 ? round($totSdBulanIni / $totTarget * 100, 2) : 0;
        $ws1->setCellValue('F' . $row, $totPct);
    }
    $ws1->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
    $ws1->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');

    // Borders
    $ws1->getStyle('A6:' . $lastCol . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Auto size
    foreach (range('A', $lastCol) as $col) {
        $ws1->getColumnDimension($col)->setAutoSize(true);
    }
    $ws1->mergeCells('A1:' . $lastCol . '1');
    $ws1->mergeCells('A2:' . $lastCol . '2');
    $ws1->mergeCells('A3:' . $lastCol . '3');
    $ws1->mergeCells('A4:' . $lastCol . '4');

    // ── Sheet 2: Lampiran ─────────────────────────────────────────────────
    $ws2 = $spreadsheet->createSheet()->setTitle('Lampiran');
    $ws2->setCellValue('A1', 'REKAPITULASI PRODUKSI ' . $judulJenis . ' DARI HUTAN HAK');
    $ws2->setCellValue('A2', 'DINAS KEHUTANAN PROVINSI JAWA TIMUR');
    $ws2->setCellValue('A3', 'BULAN : ' . strtoupper($bulanNama) . ' ' . $tahun);

    // Header lampiran dinamis berdasarkan komoditas
    $headerRow = 5;
    $headers = ['NO.', 'CDK', 'KAB/KOTA', 'KEC.', 'DESA', 'KTH', 'PENYULUH'];
    foreach ($komoditas as $k) $headers[] = $k['nama'];
    $headers[] = 'JML BLN INI'; $headers[] = 'JML s.d. BLN LALU'; $headers[] = 'JML s.d. BLN INI';

    foreach ($headers as $ci => $h) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
        $ws2->setCellValue($col . $headerRow, $h);
        $ws2->getStyle($col . $headerRow)->getFont()->setBold(true)->setSize(8);
        $ws2->getStyle($col . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');
        $ws2->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $ws2->getRowDimension($headerRow)->setRowHeight(40);
    }

    $dataRow = $headerRow + 1;
    $no = 1;
    foreach ($lampiran as $item) {
        $komodList = array_column($komoditas, 'nama');
        $cells = [
            $no++,
            'CDK BJN',
            $item['kabupaten'] ?? '',
            $item['kecamatan'] ?? '',
            $item['desa'] ?? '',
            $item['nama_kth'] ?? '',
            $item['nama_penyuluh'] ?? '',
        ];
        foreach ($komodList as $kNama) {
            $cells[] = $item['detail'][$kNama]['bulan_ini'] ?? '-';
        }
        $cells[] = $item[$jenis === 'hhk' ? 'total_bulan_ini_m3' : 'total_btg_bulan_ini'] ?? 0;
        $cells[] = $item[$jenis === 'hhk' ? 'total_sd_bulan_lalu_m3' : 'total_btg_sd_bulan_lalu'] ?? 0;
        $cells[] = $item[$jenis === 'hhk' ? 'total_sd_bulan_ini_m3' : 'total_btg_sd_bulan_ini'] ?? 0;

        foreach ($cells as $ci => $val) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
            $ws2->setCellValue($col . $dataRow, $val);
            $ws2->getStyle($col . $dataRow)->getFont()->setSize(8);
        }
        $dataRow++;
    }

    // Output
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Laporan_' . strtoupper($jenis) . '_' . $bulanNama . '_' . $tahun . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    $spreadsheet->disconnectWorksheets();
    exit;
}
