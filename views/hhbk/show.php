<?php
/** @var array<string,mixed> $row */
/** @var list<array<string,mixed>> $details */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$bulanList = Hhbk::bulanList();
?>
<div class="max-w-3xl mx-auto space-y-5 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Hasil Hutan Bukan Kayu</p>
            <h2 class="text-sm font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) ($row['nama_kth'] ?? 'Tidak disebutkan'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-[10px] text-gray-500 mt-0.5"><?= htmlspecialchars((string) ($row['kabupaten_nama'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($row['kecamatan_nama']) ? ' · ' . htmlspecialchars((string) $row['kecamatan_nama'], ENT_QUOTES, 'UTF-8') : '' ?><?= !empty($row['desa_nama']) ? ' · ' . htmlspecialchars((string) $row['desa_nama'], ENT_QUOTES, 'UTF-8') : '' ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/hhbk', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/hhbk/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg bg-forest-800 text-white hover:bg-forest-700">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 text-xs">
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Penyuluh</dt><dd class="text-gray-800 mt-0.5"><?= htmlspecialchars((string) ($row['nama_penyuluh'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Periode</dt><dd class="text-gray-800 mt-0.5"><?= $bulanList[(int) $row['bulan']] ?> <?= (int) $row['tahun'] ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Total Btg Bln Ini</dt><dd class="text-gray-800 mt-0.5 font-medium"><?= format_id((float) $row['total_btg_bulan_ini'], 2) ?> btg</dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Total Kg Bln Ini</dt><dd class="text-gray-800 mt-0.5 font-medium"><?= format_id((float) $row['total_kg_bulan_ini'], 2) ?> kg</dd></div>
        </dl>
        <?php if (!empty($row['keterangan'])): ?>
        <p class="text-[10px] text-gray-500 mt-3 pt-3 border-t border-gray-100"><?= nl2br(htmlspecialchars((string) $row['keterangan'], ENT_QUOTES, 'UTF-8')) ?></p>
        <?php endif; ?>
    </div>

    <!-- Rincian Komoditas -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-xs font-semibold text-gray-800 font-display">Rincian Komoditas (Matriks Input)</h3>
        </div>
        
        <?php if ($canMut): ?>
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/hhbk/' . (int) $row['id'] . '/detail/store', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-medium">
                        <th class="px-4 py-2.5 text-left">Komoditas</th>
                        <th class="px-4 py-2.5 text-left">Satuan</th>
                        <th class="px-4 py-2.5 text-right w-44">Jumlah Bulan Ini</th>
                        <th class="px-4 py-2.5 text-right">s.d. Bln Lalu</th>
                        <th class="px-4 py-2.5 text-right">s.d. Bln Ini</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                <?php
                $masterKomoditas = (new Hhbk(Database::connect()))->getMasterKomoditas();
                // Map existing details by composite key: komoditas|satuan
                $existingMap = [];
                foreach ($details as $d) {
                    $key = $d['komoditas'] . '|' . $d['satuan'];
                    $existingMap[$key] = $d;
                }

                $totBulanBtg = 0.0; $totLaluBtg = 0.0; $totIniBtg = 0.0;
                $totBulanKg = 0.0; $totLaluKg = 0.0; $totIniKg = 0.0;

                foreach ($masterKomoditas as $mk):
                    $komoditas = $mk['nama'];
                    $satuan = $mk['satuan'];
                    $key = $komoditas . '|' . $satuan;

                    $d = $existingMap[$key] ?? null;
                    $valBln = $d ? (float)$d['jumlah_bulan_ini'] : 0.0;
                    $valLalu = $d ? (float)$d['jumlah_sd_bulan_lalu'] : (new Hhbk(Database::connect()))->getSdBulanLalu((int)$row['id'], $komoditas, $satuan);
                    $valIni = $valLalu + $valBln;

                    if ($satuan === 'Batang') {
                        $totBulanBtg += $valBln;
                        $totLaluBtg += $valLalu;
                        $totIniBtg += $valIni;
                    } else {
                        $totBulanKg += $valBln;
                        $totLaluKg += $valLalu;
                        $totIniKg += $valIni;
                    }
                ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-2.5 font-medium text-gray-800"><?= htmlspecialchars($komoditas, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-2.5 text-gray-600"><?= htmlspecialchars($satuan, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-2 text-right">
                            <?php if ($canMut): ?>
                                <input type="text" name="volumes[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]" 
                                    value="<?= $valBln > 0 ? (float)$valBln : '' ?>" 
                                    placeholder="0.00" 
                                    inputmode="decimal" 
                                    class="w-full text-xs text-right px-2 py-1 rounded border border-gray-200 outline-none focus:border-forest-500">
                            <?php else: ?>
                                <span class="text-gray-700"><?= $valBln > 0 ? format_id($valBln, 2) : '—' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-500"><?= $valLalu > 0 ? format_id($valLalu, 2) : '—' ?></td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-800"><?= $valIni > 0 ? format_id($valIni, 2) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="bg-gray-50/80 font-semibold text-gray-800">
                        <td class="px-4 py-2.5" colspan="2">Total (Batang)</td>
                        <td class="px-4 py-2.5 text-right"><?= format_id($totBulanBtg, 2) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= format_id($totLaluBtg, 2) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= format_id($totIniBtg, 2) ?></td>
                    </tr>
                    <tr class="bg-gray-50/80 font-semibold text-gray-800">
                        <td class="px-4 py-2.5" colspan="2">Total (Kg)</td>
                        <td class="px-4 py-2.5 text-right"><?= format_id($totBulanKg, 2) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= format_id($totLaluKg, 2) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= format_id($totIniKg, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if ($canMut): ?>
        <div class="px-4 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium hover:bg-forest-700 shadow">Simpan Rincian</button>
        </div>
        </form>
        <?php endif; ?>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/hhbk/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Hapus data HHBK ini?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50">Hapus HHBK</button>
        </form>
    </div>
    <?php endif; ?>
</div>
