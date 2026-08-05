<?php
/** @var array<string, mixed> $row */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$kthUrl = APP_URL . '/kth/' . (int) $row['kth_id'];
?>
<div class="max-w-4xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Anggota KTH</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= htmlspecialchars((string) $row['nama'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars((string) $row['posisi'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $row['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl bg-forest-600 text-white hover:bg-forest-700 font-600">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card p-6">
        <p class="text-xs text-gray-400 mb-3">Terdaftar pada kelompok</p>
        <a href="<?= htmlspecialchars($kthUrl, ENT_QUOTES, 'UTF-8') ?>" class="font-display font-700 text-forest-700 hover:underline text-lg"><?= htmlspecialchars((string) $row['kth_nama'], ENT_QUOTES, 'UTF-8') ?></a>
        <p class="text-xs font-mono text-gray-400 mt-1"><?= htmlspecialchars((string) $row['kth_kode'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="stat-card p-6">
        <h3 class="font-display font-700 text-gray-800 text-sm mb-4">Identitas</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div><dt class="text-gray-400 text-xs font-600 uppercase">NIK</dt><dd class="text-gray-800 mt-0.5 font-mono text-xs"><?= $row['nik'] !== null && $row['nik'] !== '' ? htmlspecialchars((string) $row['nik'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">No. KK</dt><dd class="text-gray-800 mt-0.5"><?= $row['no_kk'] !== null && $row['no_kk'] !== '' ? htmlspecialchars((string) $row['no_kk'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Jenis kelamin</dt><dd class="text-gray-800 mt-0.5"><?= ($row['gender'] ?? '') === 'L' ? 'Laki-laki' : (($row['gender'] ?? '') === 'P' ? 'Perempuan' : '—') ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Telepon</dt><dd class="text-gray-800 mt-0.5"><?= $row['no_telpon'] !== null && $row['no_telpon'] !== '' ? htmlspecialchars((string) $row['no_telpon'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-400 text-xs font-600 uppercase">Alamat</dt><dd class="text-gray-700 mt-0.5"><?= $row['alamat'] !== null && $row['alamat'] !== '' ? nl2br(htmlspecialchars((string) $row['alamat'], ENT_QUOTES, 'UTF-8')) : '—' ?></dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-400 text-xs font-600 uppercase">Pekerjaan</dt><dd class="text-gray-700 mt-0.5"><?= $row['pekerjaan'] !== null && $row['pekerjaan'] !== '' ? htmlspecialchars((string) $row['pekerjaan'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
        </dl>
    </div>

    <div class="stat-card p-6">
        <h3 class="font-display font-700 text-gray-800 text-sm mb-4">Usaha &amp; lokasi</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Luas garapan (ha)</dt><dd class="text-gray-800 mt-0.5"><?= $row['luas_garapan_ha'] !== null ? htmlspecialchars((string) $row['luas_garapan_ha'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">HTM</dt><dd class="text-gray-800 mt-0.5"><?= $row['htm'] !== null && $row['htm'] !== '' ? htmlspecialchars((string) $row['htm'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Komoditi HHBK</dt><dd class="text-gray-700 mt-0.5"><?= $row['komoditi_hhbk'] !== null && $row['komoditi_hhbk'] !== '' ? htmlspecialchars((string) $row['komoditi_hhbk'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Komoditi HHK</dt><dd class="text-gray-700 mt-0.5"><?= $row['komoditi_hhk'] !== null && $row['komoditi_hhk'] !== '' ? htmlspecialchars((string) $row['komoditi_hhk'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Koordinat</dt><dd class="text-gray-700 mt-0.5 font-mono text-xs"><?= $row['koordinat_ls'] !== null ? htmlspecialchars((string) $row['koordinat_ls'], ENT_QUOTES, 'UTF-8') : '—' ?>, <?= $row['koordinat_bt'] !== null ? htmlspecialchars((string) $row['koordinat_bt'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
        </dl>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/kelembagaan/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Hapus anggota ini dari database?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">Hapus data</button>
        </form>
    </div>
    <?php endif; ?>
</div>
