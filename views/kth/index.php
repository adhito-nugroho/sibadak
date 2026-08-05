<?php
/** @var array $result */
/** @var array $kabupatenList */
/** @var int $filterKab */
/** @var string $filterKelas */
/** @var string $filterQ */
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
        <h2 class="font-display font-700 text-gray-800 text-lg">Daftar KTH</h2>
        <p class="text-xs text-gray-400 mt-0.5"><?= format_id($total) ?> kelompok aktif · halaman <?= $cur ?> / <?= max(1, $pages) ?></p>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/kth/create', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white text-sm font-600 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah KTH
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-4 mb-6 fade-up delay-1 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
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
    <div class="md:col-span-2">
        <label class="block text-xs font-600 text-gray-500 mb-1">Kelas</label>
        <select name="kelas" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
            <option value="">Semua</option>
            <option value="Utama" <?= $filterKelas === 'Utama' ? 'selected' : '' ?>>Utama</option>
            <option value="Madya" <?= $filterKelas === 'Madya' ? 'selected' : '' ?>>Madya</option>
            <option value="Pemula" <?= $filterKelas === 'Pemula' ? 'selected' : '' ?>>Pemula</option>
        </select>
    </div>
    <div class="md:col-span-4">
        <label class="block text-xs font-600 text-gray-500 mb-1">Cari nama / kode register</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ketik lalu Enter…"
            class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
    </div>
    <div class="md:col-span-3 flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700 transition">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-4 py-3 text-left">Nama KTH</th>
                    <th class="px-4 py-3 text-left">Register</th>
                    <th class="px-4 py-3 text-left">Kabupaten</th>
                    <th class="px-4 py-3 text-left">Kecamatan</th>
                    <th class="px-4 py-3 text-left">Kelas</th>
                    <th class="px-4 py-3 text-right">Anggota</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if ($rows === []): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm">Tidak ada data yang cocok.</td></tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="px-4 py-3 font-500 text-gray-800"><?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-gray-500 text-xs font-mono"><?= htmlspecialchars($r['kode_register'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-gray-600 text-sm"><?= htmlspecialchars($r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3 text-gray-500 text-sm"><?= htmlspecialchars($r['kecamatan_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $bc = match ($r['kelas']) {
                            'Utama' => 'badge-utama',
                            'Madya' => 'badge-madya',
                            default => 'badge-pemula',
                        };
                        ?>
                        <span class="badge <?= $bc ?>"><?= htmlspecialchars($r['kelas'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td class="px-4 py-3 text-right font-600 text-gray-700"><?= format_id((int) $r['jumlah_anggota']) ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Detail</a>
                        <?php if ($canMut): ?>
                        <span class="text-gray-300 mx-1">|</span>
                        <a href="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-gray-600 hover:underline">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-t border-gray-100 bg-gray-50/50">
        <span class="text-xs text-gray-500">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="flex gap-2">
            <?php if ($cur > 1): ?>
            <a class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50" href="?<?= htmlspecialchars($qsBase(['page' => $cur - 1]), ENT_QUOTES, 'UTF-8') ?>">« Sebelumnya</a>
            <?php endif; ?>
            <?php if ($cur < $pages): ?>
            <a class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50" href="?<?= htmlspecialchars($qsBase(['page' => $cur + 1]), ENT_QUOTES, 'UTF-8') ?>">Berikutnya »</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
