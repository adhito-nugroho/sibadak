<?php
/** @var array $result */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var int $filterKab */
/** @var int $filterTahun */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];

$qsBase = static function (array $extra): string {
    $p = array_merge($_GET, $extra);
    $p = array_filter($p, static fn ($v) => $v !== null && $v !== '');
    return http_build_query($p);
};
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6 fade-up">
    <div>
        <h2 class="font-display font-700 text-gray-800 text-lg">Data AEP</h2>
        <p class="text-xs text-gray-400 mt-0.5">Alat &amp; Sarana Ekonomi Produktif · <?= format_id($total) ?> entri</p>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/aep/create', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white text-sm font-600 rounded-xl transition shadow-sm">+ Tambah AEP</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-4 mb-6 fade-up delay-1 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
    <?php if (user_role() !== 'operator'): ?>
    <div class="md:col-span-3">
        <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten</label>
        <select name="kabupaten_id" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua</option>
            <?php foreach ($kabupatenList as $kb): ?>
            <option value="<?= (int) $kb['id'] ?>" <?= $filterKab === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="md:col-span-2">
        <label class="block text-xs font-600 text-gray-500 mb-1">Tahun</label>
        <select name="tahun" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua</option>
            <?php foreach ($yearOptions as $th): ?>
            <option value="<?= $th ?>" <?= $filterTahun === $th ? 'selected' : '' ?>><?= $th ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-<?= user_role() !== 'operator' ? '6' : '9' ?>">
        <label class="block text-xs font-600 text-gray-500 mb-1">Cari</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="Nama KTH / jenis bantuan">
    </div>
    <div class="md:col-span-1"><button type="submit" class="w-full px-3 py-2 rounded-xl bg-forest-600 text-white text-sm">OK</button></div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead><tr class="border-b border-gray-100"><th class="px-4 py-3 text-left">Nama KTH</th><th class="px-4 py-3 text-left">Kabupaten</th><th class="px-4 py-3 text-left">Jenis Bantuan</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-left">Tahun</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada data.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars((string) $r['nama_kth'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars((string) $r['jenis_bantuan'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700"><?= format_id((int) $r['jumlah']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-700"><?= (int) $r['tahun'] ?></td>
                    <td class="px-4 py-3 text-right"><a href="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Detail</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap justify-center gap-2 text-sm">
        <?php if ($cur > 1): ?>
        <a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="?<?= htmlspecialchars($qsBase(['page' => $cur - 1]), ENT_QUOTES, 'UTF-8') ?>">« Prev</a>
        <?php endif; ?>
        <span class="px-3 py-1 text-gray-500"><?= $cur ?> / <?= $pages ?></span>
        <?php if ($cur < $pages): ?>
        <a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="?<?= htmlspecialchars($qsBase(['page' => $cur + 1]), ENT_QUOTES, 'UTF-8') ?>">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
