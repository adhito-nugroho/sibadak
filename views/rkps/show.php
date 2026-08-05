<?php
/** @var array<string,mixed> $row */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
?>
<div class="max-w-2xl mx-auto space-y-5 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Rencana Kelola Perhutanan Sosial</p>
            <h2 class="text-sm font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) $row['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-[10px] text-gray-500 mt-0.5"><?= htmlspecialchars((string) $row['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> · Skema <?= htmlspecialchars((string) $row['skema'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/rkps/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg bg-forest-800 text-white hover:bg-forest-700">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-xs">
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Periode</dt><dd class="text-gray-800 mt-0.5 font-medium"><?= (int) $row['periode_awal'] ?> – <?= (int) $row['periode_akhir'] ?> <span class="text-gray-400 font-normal">(<?= (int) $row['periode_akhir'] - (int) $row['periode_awal'] ?> tahun)</span></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Status</dt><dd class="mt-0.5">
                <?php $stBadge = match ((string) $row['status']) { 'sudah' => 'bg-green-50 text-green-700', 'proses' => 'bg-amber-50 text-amber-700', default => 'bg-gray-100 text-gray-600' }; ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium <?= $stBadge ?>"><?= ucfirst((string) $row['status']) ?></span>
            </dd></div>
            <div class="sm:col-span-2"><dt class="text-[10px] text-gray-500 font-medium uppercase">Catatan</dt><dd class="text-gray-700 mt-0.5"><?= !empty($row['catatan']) ? nl2br(htmlspecialchars((string) $row['catatan'], ENT_QUOTES, 'UTF-8')) : '—' ?></dd></div>
            <div class="sm:col-span-2"><dt class="text-[10px] text-gray-500 font-medium uppercase">Dokumen</dt><dd class="mt-0.5">
                <?php if (!empty($row['dokumen_link'])): ?>
                <a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $row['dokumen_link'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-forest-600 hover:underline text-xs"><i class="ti ti-file-download text-sm"></i> Unduh dokumen</a>
                <?php else: ?>—<?php endif; ?>
            </dd></div>
        </dl>
    </div>

    <div class="flex items-center gap-3">
        <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $row['kps_id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline"><i class="ti ti-arrow-left text-sm"></i> Lihat detail KPS</a>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/rkps/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Hapus data RKPS ini?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50">Hapus RKPS</button>
        </form>
    </div>
    <?php endif; ?>
</div>
