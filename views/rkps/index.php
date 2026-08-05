<?php
/** @var array $result */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-5 fade-up">
    <div>
        <h2 class="text-sm font-semibold text-gray-800">Data RKPS</h2>
        <p class="text-[10px] text-gray-400 mt-0.5">Rencana Kelola Perhutanan Sosial · <?= format_id($total) ?> entri</p>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/rkps/create', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-1.5 bg-forest-800 hover:bg-forest-700 text-white text-xs font-medium px-3.5 py-2 rounded-lg transition-colors"><i class="ti ti-plus text-sm leading-none"></i> Tambah RKPS</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 mb-4 fade-up delay-1 flex flex-wrap items-end gap-3">
    <?php if (user_role() !== 'operator'): ?>
    <div class="w-36">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Kabupaten</label>
        <select name="kabupaten_id" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            <option value="">Semua</option>
            <?php foreach ($kabupatenList as $kb): ?><option value="<?= (int) $kb['id'] ?>" <?= $filterKab === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="w-28">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Status</label>
        <select name="status" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            <option value="">Semua</option>
            <?php foreach (RkpsKps::statusList() as $st): ?><option value="<?= $st ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Cari</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama KPS..." class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium">Cari</button>
</form>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead><tr class="border-b border-gray-100"><th class="px-4 py-2.5 text-left">KPS</th><th class="px-4 py-2.5 text-left">Kabupaten</th><th class="px-4 py-2.5 text-left">Periode</th><th class="px-4 py-2.5 text-left">Status</th><th class="px-4 py-2.5 text-left">Dokumen</th><th class="px-4 py-2.5 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-xs text-gray-500">Tidak ada data.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr class="hover:bg-forest-50/50 transition-colors">
                    <td class="px-4 py-2.5 text-xs font-medium text-gray-800"><?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-700 font-medium"><?= (int) $r['periode_awal'] ?>–<?= (int) $r['periode_akhir'] ?></td>
                    <td class="px-4 py-2.5 text-xs"><?= ucfirst((string) $r['status']) ?></td>
                    <td class="px-4 py-2.5 text-xs"><?= !empty($r['dokumen_link']) ? '<i class="ti ti-file-check text-forest-600"></i>' : '<span class="text-gray-400">—</span>' ?></td>
                    <td class="px-4 py-2.5 text-right"><a href="<?= htmlspecialchars(APP_URL . '/rkps/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-[10px] font-medium text-forest-600 hover:underline">Detail</a></td>
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
