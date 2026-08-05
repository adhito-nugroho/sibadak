<?php
/** @var array<string,mixed> $row */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
?>
<div class="max-w-3xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Alat &amp; Sarana Ekonomi Produktif</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= htmlspecialchars((string) $row['nama_kth'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars((string) $row['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> · Tahun <?= (int) $row['tahun'] ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl bg-forest-600 text-white hover:bg-forest-700">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Jenis Bantuan</dt><dd class="text-gray-800 mt-0.5 font-500"><?= htmlspecialchars((string) $row['jenis_bantuan'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Jumlah</dt><dd class="text-gray-800 mt-0.5"><?= format_id((int) $row['jumlah']) ?> unit</dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Kabupaten</dt><dd class="text-gray-700 mt-0.5"><?= htmlspecialchars((string) $row['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Desa</dt><dd class="text-gray-700 mt-0.5"><?= $row['desa_nama'] !== null && $row['desa_nama'] !== '' ? htmlspecialchars((string) $row['desa_nama'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-400 text-xs font-600 uppercase">Keterangan</dt><dd class="text-gray-700 mt-0.5"><?= $row['keterangan'] !== null && $row['keterangan'] !== '' ? nl2br(htmlspecialchars((string) $row['keterangan'], ENT_QUOTES, 'UTF-8')) : '—' ?></dd></div>
        </dl>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Hapus data AEP ini?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">Hapus AEP</button>
        </form>
    </div>
    <?php endif; ?>
</div>
