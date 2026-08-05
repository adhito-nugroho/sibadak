<?php
/** @var array $result */
/** @var array $kabupatenList */
/** @var list<array{id:int,nama_lembaga:string,kabupaten_id:int,kabupaten_nama:string}> $kpsOptions */
/** @var int $filterKab */
/** @var int $filterKps */
/** @var int $filterTahun */
/** @var string $filterStatus */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
/** @var string|null $selectedKpsName */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6 fade-up">
    <div>
        <h2 class="font-display font-700 text-gray-800 text-lg">RKT per KPS</h2>
        <p class="text-xs text-gray-400 mt-0.5">Status rencana kerja tahunan · <?= format_id($total) ?> entri · halaman <?= $cur ?> / <?= max(1, $pages) ?></p>
        <?php if ($filterKps > 0): ?>
        <p class="text-xs text-forest-700 mt-1">Filter aktif: <?= htmlspecialchars($selectedKpsName ?? ('KPS ID ' . $filterKps), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/rkt/create', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white text-sm font-600 rounded-xl transition shadow-sm">+ Tambah RKT</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/rkt', ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-4 mb-6 fade-up delay-1 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
    <?php if (user_role() !== 'operator'): ?>
    <div class="md:col-span-2">
        <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten</label>
        <select name="kabupaten_id" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua</option>
            <?php foreach ($kabupatenList as $kb): ?>
            <option value="<?= (int) $kb['id'] ?>" <?= $filterKab === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="md:col-span-3">
        <label class="block text-xs font-600 text-gray-500 mb-1">KPS</label>
        <select name="kps_id" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua KPS</option>
            <?php foreach ($kpsOptions as $kps): ?>
            <option value="<?= (int) $kps['id'] ?>" <?= $filterKps === (int) $kps['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kps['kabupaten_nama'] . ' — ' . $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-600 text-gray-500 mb-1">Tahun</label>
        <select name="tahun" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua</option>
            <?php foreach ($yearOptions as $th): ?>
            <option value="<?= $th ?>" <?= $filterTahun === $th ? 'selected' : '' ?>><?= $th ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-600 text-gray-500 mb-1">Status</label>
        <select name="status" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua</option>
            <?php foreach (RktKps::statusList() as $st): ?>
            <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-600 text-gray-500 mb-1">Cari</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="KPS / SK / catatan">
    </div>
    <div class="md:col-span-1 flex gap-2">
        <button type="submit" class="px-3 py-2 rounded-xl bg-forest-600 text-white text-sm">OK</button>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead><tr class="border-b border-gray-100">
                <th class="px-4 py-3 text-left">KPS</th>
                <th class="px-4 py-3 text-left">Kabupaten</th>
                <th class="px-4 py-3 text-left">Tahun</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Dokumen</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                <?php if ($rows === []): ?>
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada data.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="px-4 py-3">
                        <div class="text-gray-800 font-500"><?= htmlspecialchars((string) $r['kps_nama'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars((string) $r['kps_no_sk'], ENT_QUOTES, 'UTF-8') ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-sm font-600 text-gray-700"><?= (int) $r['tahun'] ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= ucfirst((string) $r['status']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php if (!empty($r['dokumen_link'])): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $r['dokumen_link'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-xs font-600 text-forest-600 hover:underline">Unduh</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right"><a href="<?= htmlspecialchars(APP_URL . '/rkt/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Detail</a></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
