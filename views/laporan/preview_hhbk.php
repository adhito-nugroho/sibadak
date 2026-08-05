<?php
/** @var list<array{id:int,nama:string,satuan:string,urutan:int}> $komoditas */
/** @var array<string,array{bulan_ini:float,sd_bulan_ini:float,satuan:string}> $rekap */
/** @var list<array> $lampiran */
/** @var array<string,float> $targets */
/** @var int $bulan */
/** @var int $tahun */
/** @var string $bulanNama */
/** @var \DateTimeImmutable $tgl */
$hariList = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$hariNama = $hariList[$tgl->format('l')];
$tglHuruf = LaporanController::angkaKeHurufPublic((int)$tgl->format('j'));
$bulanTtdNama = LaporanController::bulanIndoPublic((int)$tgl->format('n'));
$tahunHuruf = LaporanController::angkaKeHurufPublic((int)$tgl->format('Y'));
$totBulanIni = 0; $totSdBulanIni = 0;
?>
<div class="max-w-4xl mx-auto space-y-5 fade-up">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800">Preview — Berita Acara HHBK</h2>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/laporan', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">← Kembali</a>
            <a href="<?= htmlspecialchars(APP_URL . '/laporan/export-excel?jenis=hhbk&bulan=' . $bulan . '&tahun=' . $tahun, ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg bg-green-700 text-white hover:bg-green-800 flex items-center gap-1"><i class="ti ti-file-spreadsheet text-sm"></i> Excel</a>
            <a href="<?= htmlspecialchars(APP_URL . '/laporan/export-pdf?jenis=hhbk&bulan=' . $bulan . '&tahun=' . $tahun . '&tanggal_ttd=' . $tgl->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"><i class="ti ti-file-type-pdf text-sm"></i> PDF</a>
        </div>
    </div>

    <!-- Berita Acara -->
    <div class="bg-white rounded-xl border border-gray-100 p-8 text-sm">
        <div class="text-center mb-6">
            <p class="font-bold">BERITA ACARA REKONSILIASI</p>
            <p class="font-bold">PRODUKSI HASIL HUTAN BUKAN KAYU YANG BERASAL DARI HUTAN HAK</p>
            <p class="font-bold">ANTARA BIDANG PHL DENGAN CDK WILAYAH BOJONEGORO</p>
            <p class="font-bold">BAGIAN BULAN : <?= strtoupper($bulanNama) . ' ' . $tahun ?></p>
        </div>
        <p class="text-justify leading-relaxed text-xs mb-6">
            Pada hari ini, <?= $hariNama ?> tanggal <?= $tglHuruf ?> bulan <?= $bulanTtdNama ?> tahun <?= $tahunHuruf ?>,
            telah dilaksanakan rekonsiliasi produksi hasil hutan bukan kayu yang berasal dari hutan hak bagian bulan <?= $bulanNama ?>
            tahun <?= $tahunHuruf ?> antara Bidang Pengelolaan Hutan Lestari dengan Cabang Dinas Kehutanan (CDK) Wilayah Bojonegoro,
            Wilayah Kerja : Kabupaten Bojonegoro, Kabupaten Tuban, Kabupaten Lamongan dan Kabupaten Gresik dengan hasil sebagai berikut :
        </p>
        <p class="font-bold underline mb-3 text-xs">Hasil Hutan Bukan Kayu</p>
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse border border-gray-400">
                <thead>
                    <tr>
                        <th class="border border-gray-400 px-2 py-1.5 text-center" rowspan="2">No</th>
                        <th class="border border-gray-400 px-2 py-1.5 text-center" rowspan="2">Jenis Komoditas HHBK</th>
                        <th class="border border-gray-400 px-2 py-1.5 text-center" colspan="2">Volume (Kg/Btg)</th>
                        <th class="border border-gray-400 px-2 py-1.5 text-center" rowspan="2">Keterangan</th>
                    </tr>
                    <tr>
                        <th class="border border-gray-400 px-2 py-1.5 text-center">Bulan <?= $bulanNama ?></th>
                        <th class="border border-gray-400 px-2 py-1.5 text-center">s/d Bulan <?= $bulanNama ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($komoditas as $k):
                    $bulanIni = $rekap[$k['nama']]['bulan_ini'] ?? 0;
                    $sdBulanIni = $rekap[$k['nama']]['sd_bulan_ini'] ?? 0;
                    $totBulanIni += $bulanIni; $totSdBulanIni += $sdBulanIni;
                ?>
                    <tr>
                        <td class="border border-gray-400 px-2 py-1 text-center"><?= $no++ ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="border border-gray-400 px-2 py-1 text-right"><?= $bulanIni > 0 ? number_format($bulanIni, 2, ',', '.') : '-' ?></td>
                        <td class="border border-gray-400 px-2 py-1 text-right"><?= $sdBulanIni > 0 ? number_format($sdBulanIni, 2, ',', '.') : '-' ?></td>
                        <td class="border border-gray-400 px-2 py-1"></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="font-bold bg-gray-50">
                        <td class="border border-gray-400 px-2 py-1"></td>
                        <td class="border border-gray-400 px-2 py-1">Jumlah</td>
                        <td class="border border-gray-400 px-2 py-1 text-right"><?= number_format($totBulanIni, 2, ',', '.') ?></td>
                        <td class="border border-gray-400 px-2 py-1 text-right"><?= number_format($totSdBulanIni, 2, ',', '.') ?></td>
                        <td class="border border-gray-400 px-2 py-1"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lampiran -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 overflow-hidden">
        <p class="text-xs font-semibold text-gray-700 mb-3">Lampiran Berita Acara Rekonsiliasi Hasil Hutan Bukan Kayu</p>
        <div class="text-center mb-3">
            <p class="text-xs font-bold">REKAPITULASI PRODUKSI HASIL HUTAN BUKAN KAYU DARI HUTAN HAK</p>
            <p class="text-xs font-bold">DINAS KEHUTANAN PROVINSI JAWA TIMUR</p>
            <p class="text-xs font-bold">BULAN : <?= strtoupper($bulanNama) . ' ' . $tahun ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[10px] border-collapse border border-gray-400">
                <thead>
                    <tr class="bg-blue-50">
                        <th class="border border-gray-400 px-1.5 py-1 text-center">NO.</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">CDK</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">KAB/KOTA</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">KEC.</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">DESA</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">KTH</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">PENYULUH</th>
                        <?php foreach ($komoditas as $k): ?>
                        <th class="border border-gray-400 px-1.5 py-1 text-center"><?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?><br><span class="text-[9px] text-gray-500"><?= $k['satuan'] ?></span></th>
                        <?php endforeach; ?>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">JML BTG BLN INI</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">JML KG BLN INI</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">JML s.d. BLN INI BTG</th>
                        <th class="border border-gray-400 px-1.5 py-1 text-center">JML s.d. BLN INI KG</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($lampiran)): ?>
                    <tr><td colspan="<?= 11 + count($komoditas) ?>" class="border border-gray-400 px-2 py-4 text-center text-gray-500">Tidak ada data untuk periode ini.</td></tr>
                <?php else: $no = 1; foreach ($lampiran as $item): ?>
                    <tr>
                        <td class="border border-gray-400 px-1.5 py-1 text-center"><?= $no++ ?></td>
                        <td class="border border-gray-400 px-1.5 py-1 text-center font-bold text-[9px]">CDK BJN</td>
                        <td class="border border-gray-400 px-1.5 py-1"><?= htmlspecialchars($item['kabupaten'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1"><?= htmlspecialchars($item['kecamatan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1"><?= htmlspecialchars($item['desa'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1"><?= htmlspecialchars($item['nama_kth'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1"><?= htmlspecialchars($item['nama_penyuluh'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <?php foreach ($komoditas as $k):
                            $v = $item['detail'][$k['nama']]['bulan_ini'] ?? 0;
                        ?>
                        <td class="border border-gray-400 px-1.5 py-1 text-right"><?= $v > 0 ? number_format($v, 2, ',', '.') : '' ?></td>
                        <?php endforeach; ?>
                        <td class="border border-gray-400 px-1.5 py-1 text-right"><?= number_format((float)($item['total_btg_bulan_ini'] ?? 0), 2, ',', '.') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1 text-right"><?= number_format((float)($item['total_kg_bulan_ini'] ?? 0), 2, ',', '.') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1 text-right"><?= number_format((float)($item['total_btg_sd_bulan_ini'] ?? 0), 2, ',', '.') ?></td>
                        <td class="border border-gray-400 px-1.5 py-1 text-right"><?= number_format((float)($item['total_kg_sd_bulan_ini'] ?? 0), 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
