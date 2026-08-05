<?php
/** @var array $result */
/** @var list<array{tahun:int,total_nilai:float,total_transaksi:int}> $rekap */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
$bulanNames = Nte::bulanList();
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-5 fade-up">
    <div>
        <h2 class="text-sm font-semibold text-gray-800">Nilai Transaksi Ekonomi (NTE)</h2>
        <p class="text-[10px] text-gray-400 mt-0.5"><?= format_id($total) ?> transaksi</p>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/nte/import', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-1.5 bg-forest-800 hover:bg-forest-700 text-white text-xs font-medium px-3.5 py-2 rounded-lg transition-colors">
        <i class="ti ti-file-import text-sm leading-none"></i> Import Excel
    </a>
    <?php endif; ?>
</div>

<!-- Rekap ringkas -->
<?php if (!empty($rekap)): ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5 fade-up delay-1">
    <?php foreach (array_slice($rekap, 0, 4) as $r): ?>
    <div class="bg-white rounded-xl border border-gray-100 p-3">
        <p class="text-[10px] text-gray-400">Tahun <?= (int) $r['tahun'] ?></p>
        <p class="text-sm font-semibold text-gray-800 mt-0.5">Rp <?= format_id((float) $r['total_nilai']) ?></p>
        <p class="text-[10px] text-gray-400"><?= format_id((int) $r['total_transaksi']) ?> transaksi</p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filter -->
<form method="get" action="<?= htmlspecialchars(APP_URL . '/nte', ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 mb-4 fade-up delay-1 flex flex-wrap items-end gap-3">
    <?php if (user_role() !== 'operator'): ?>
    <div class="w-36">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Kabupaten</label>
        <select name="kabupaten_id" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            <option value="">Semua</option>
            <?php foreach ($kabupatenList as $kb): ?><option value="<?= (int) $kb['id'] ?>" <?= $filterKab === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="w-24">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tahun</label>
        <select name="tahun" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            <option value="">Semua</option>
            <?php foreach (array_reverse($yearOptions) as $y): ?><option value="<?= $y ?>" <?= $filterTahun === $y ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="w-28">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Bulan</label>
        <select name="bulan" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            <option value="">Semua</option>
            <?php foreach ($bulanOptions as $b => $n): ?><option value="<?= $b ?>" <?= $filterBulan === $b ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Cari</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama KTH / jenis barang..." class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium">Cari</button>
</form>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead><tr class="border-b border-gray-100">
                <th class="px-3 py-2.5 text-left">Nama KTH</th>
                <th class="px-3 py-2.5 text-left">Kabupaten</th>
                <th class="px-3 py-2.5 text-left">Tahun / Bulan</th>
                <th class="px-3 py-2.5 text-left">Barang/Jasa</th>
                <th class="px-3 py-2.5 text-left">Produk</th>
                <th class="px-3 py-2.5 text-right">Jumlah</th>
                <th class="px-3 py-2.5 text-left">Satuan</th>
                <th class="px-3 py-2.5 text-right">NTE (Rp)</th>
                <?php if ($canMut): ?><th class="px-3 py-2.5 text-right">Aksi</th><?php endif; ?>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            <?php if ($rows === []): ?>
                <tr><td colspan="<?= $canMut ? 9 : 8 ?>" class="px-4 py-8 text-center text-xs text-gray-500">Tidak ada data. <a href="<?= htmlspecialchars(APP_URL . '/nte/import', ENT_QUOTES, 'UTF-8') ?>" class="text-forest-600 hover:underline">Import Excel →</a></td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr class="hover:bg-forest-50/50 transition-colors">
                    <td class="px-3 py-2 text-xs font-medium text-gray-800"><?= htmlspecialchars((string) ($r['nama_kth'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-3 py-2 text-xs text-gray-600"><?= htmlspecialchars((string) ($r['kabupaten_nama'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-3 py-2 text-xs text-gray-700"><?= (int) $r['tahun'] ?> / <?= $bulanNames[(int) $r['bulan']] ?? $r['bulan'] ?></td>
                    <td class="px-3 py-2 text-xs text-gray-600 max-w-[160px] truncate" title="<?= htmlspecialchars((string) $r['jenis_barang'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['jenis_barang'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-3 py-2 text-xs text-gray-600"><?= htmlspecialchars((string) ($r['produk'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-3 py-2 text-xs text-right text-gray-700"><?= $r['jumlah'] !== null ? format_id((float) $r['jumlah'], 2) : '—' ?></td>
                    <td class="px-3 py-2 text-xs text-gray-500"><?= htmlspecialchars((string) ($r['satuan'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-3 py-2 text-xs text-right font-medium text-gray-800">Rp <?= format_id((int) $r['nilai_rp']) ?></td>
                    <?php if ($canMut): ?>
                    <td class="px-3 py-2 text-right">
                        <form method="post" action="<?= htmlspecialchars(APP_URL . '/nte/' . (int) $r['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Hapus transaksi ini?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="text-[10px] text-red-500 hover:underline">Hapus</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex justify-center gap-2 text-xs">
        <?php if ($cur > 1): ?><a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="?<?= http_build_query(array_merge($_GET, ['page' => $cur - 1])) ?>">« Prev</a><?php endif; ?>
        <span class="px-3 py-1 text-gray-500"><?= $cur ?> / <?= $pages ?></span>
        <?php if ($cur < $pages): ?><a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="?<?= http_build_query(array_merge($_GET, ['page' => $cur + 1])) ?>">Next »</a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
