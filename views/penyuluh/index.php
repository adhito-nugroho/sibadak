<?php
/** @var array $result */
/** @var string $filterQ */
/** @var string $filterStatus */
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
        <h2 class="font-display font-700 text-gray-800 text-lg">Data Penyuluh Kehutanan</h2>
        <p class="text-xs text-gray-400 mt-0.5">Master basis penyuluh untuk HHK dan HHBK &middot; <?= format_id($total) ?> entri</p>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/penyuluh/create', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white text-sm font-600 rounded-xl transition shadow-sm">+ Tambah Penyuluh</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/penyuluh', ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-4 mb-6 fade-up delay-1 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
    <div class="md:col-span-7">
        <label class="block text-xs font-600 text-gray-500 mb-1">Cari</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="NIP / nama / pangkat / jabatan">
    </div>
    <div class="md:col-span-3">
        <label class="block text-xs font-600 text-gray-500 mb-1">Status</label>
        <select name="status" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <option value="">Semua</option>
            <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= $filterStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </div>
    <div class="md:col-span-2"><button class="w-full px-3 py-2 rounded-xl bg-forest-600 text-white text-sm">Filter</button></div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead><tr class="border-b border-gray-100"><th class="px-4 py-3 text-left">NIP</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">Pangkat</th><th class="px-4 py-3 text-left">Jabatan</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada data.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars((string) $r['nip'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-gray-800 font-500"><?= htmlspecialchars((string) $r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars((string) $r['pangkat'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars((string) $r['jabatan'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full <?= (int) $r['is_active'] === 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>"><?= (int) $r['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="px-4 py-3 text-right">
                        <?php if ($canMut): ?><a href="<?= htmlspecialchars(APP_URL . '/penyuluh/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Edit</a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex justify-center gap-2 text-sm">
        <?php if ($cur > 1): ?><a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600" href="?<?= htmlspecialchars($qsBase(['page' => $cur - 1]), ENT_QUOTES, 'UTF-8') ?>">Prev</a><?php endif; ?>
        <span class="px-3 py-1 text-gray-500"><?= $cur ?> / <?= $pages ?></span>
        <?php if ($cur < $pages): ?><a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600" href="?<?= htmlspecialchars($qsBase(['page' => $cur + 1]), ENT_QUOTES, 'UTF-8') ?>">Next</a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
