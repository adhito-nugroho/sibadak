<?php
/** @var array $result */
/** @var array $kabupatenList */
/** @var int $filterKab */
/** @var string $filterSkema */
/** @var string $filterQ */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
$skemaOpts = Kps::skemaList();
$skemaClass = static function (string $s): string {
    return match ($s) {
        'HKm' => 'bg-blue-50 text-blue-800',
        'HD' => 'bg-amber-50 text-amber-800',
        'HTR' => 'bg-violet-50 text-violet-800',
        'Kulin KK' => 'bg-forest-100 text-forest-800',
        'IPHPS' => 'bg-teal-50 text-teal-800',
        default => 'bg-gray-100 text-gray-700',
    };
};
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6 fade-up">
    <div>
        <h2 class="font-display font-700 text-gray-800 text-lg">Data KPS</h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelompok Perhutanan Sosial · <?= format_id($total) ?> entri · halaman <?= $cur ?> / <?= max(1, $pages) ?></p>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/kps/create', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white text-sm font-600 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah KPS
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-4 mb-6 fade-up delay-1 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
    <?php if (user_role() !== 'operator'): ?>
    <div class="md:col-span-3">
        <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten</label>
        <select name="kabupaten_id" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
            <option value="">Semua</option>
            <?php foreach ($kabupatenList as $kb): ?>
            <option value="<?= (int) $kb['id'] ?>" <?= $filterKab === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="md:col-span-3">
        <label class="block text-xs font-600 text-gray-500 mb-1">Skema</label>
        <select name="skema" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
            <option value="">Semua</option>
            <?php foreach ($skemaOpts as $sk): ?>
            <option value="<?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?>" <?= $filterSkema === $sk ? 'selected' : '' ?>><?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-4">
        <label class="block text-xs font-600 text-gray-500 mb-1">Cari nama lembaga / no. SK</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ketik lalu Enter…"
            class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
    </div>
    <div class="md:col-span-2 flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700 transition">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-4 py-3 text-left">Nama lembaga</th>
                    <th class="px-4 py-3 text-left">Skema</th>
                    <th class="px-4 py-3 text-left">Kabupaten</th>
                    <th class="px-4 py-3 text-left">Desa</th>
                    <th class="px-4 py-3 text-right">Luas (Ha)</th>
                    <th class="px-4 py-3 text-right">KK</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if ($rows === []): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm">Tidak ada data yang cocok.</td></tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="px-4 py-3 font-500 text-gray-800 max-w-[14rem]">
                        <span class="line-clamp-2" title="<?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="block text-[10px] text-gray-400 font-mono truncate"><?= htmlspecialchars((string) $r['no_sk'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-600 <?= htmlspecialchars($skemaClass((string) $r['skema']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['skema'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm"><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-gray-500 text-sm"><?= htmlspecialchars((string) $r['desa_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-right text-gray-700 text-sm"><?= $r['luas_wilayah_ha'] !== null ? format_id((float) $r['luas_wilayah_ha'], 2) : '—' ?></td>
                    <td class="px-4 py-3 text-right font-600 text-gray-700"><?= format_id((int) $r['jumlah_kk']) ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap justify-center gap-2 text-sm">
        <?php
        $qs = static function (int $p) use ($filterKab, $filterSkema, $filterQ): string {
            $g = array_filter([
                'page' => $p,
                'kabupaten_id' => $filterKab > 0 ? $filterKab : null,
                'skema' => $filterSkema !== '' ? $filterSkema : null,
                'q' => $filterQ !== '' ? $filterQ : null,
            ], static fn ($v) => $v !== null && $v !== '');
            return http_build_query($g);
        };
        ?>
        <?php if ($cur > 1): ?>
        <a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="<?= htmlspecialchars(APP_URL . '/kps?' . $qs($cur - 1), ENT_QUOTES, 'UTF-8') ?>">« Prev</a>
        <?php endif; ?>
        <span class="px-3 py-1 text-gray-500"><?= $cur ?> / <?= $pages ?></span>
        <?php if ($cur < $pages): ?>
        <a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="<?= htmlspecialchars(APP_URL . '/kps?' . $qs($cur + 1), ENT_QUOTES, 'UTF-8') ?>">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
