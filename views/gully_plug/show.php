<?php
/** @var array<string,mixed> $row */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
?>
<div class="max-w-2xl mx-auto space-y-5 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Gully Plug</p>
            <h2 class="text-sm font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) ($row['sasaran'] ?? 'Gully Plug #' . $row['id']), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/gully-plug', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/gully-plug/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg bg-forest-800 text-white hover:bg-forest-700">Edit</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-xs">
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Lokasi</dt><dd class="text-gray-800 mt-0.5"><?= nl2br(htmlspecialchars((string) $row['lokasi'], ENT_QUOTES, 'UTF-8')) ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Desa</dt><dd class="text-gray-700 mt-0.5"><?= $row['desa_nama'] ?? '—' ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">SubDAS</dt><dd class="text-gray-700 mt-0.5"><?= $row['subdas'] ?? '—' ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Tahun</dt><dd class="text-gray-700 mt-0.5"><?= (int) $row['tahun'] ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Jumlah Unit</dt><dd class="text-gray-700 mt-0.5"><?= format_id((int) $row['jumlah_unit']) ?></dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Dimensi (P×L×T)</dt><dd class="text-gray-700 mt-0.5 font-mono"><?= $row['panjang_m'] ?? '—' ?> × <?= $row['lebar_m'] ?? '—' ?> × <?= $row['tinggi_m'] ?? '—' ?> m</dd></div>
            <div><dt class="text-[10px] text-gray-500 font-medium uppercase">Koordinat</dt><dd class="text-gray-700 mt-0.5 font-mono"><?= $row['koordinat_ls'] ?? '—' ?>, <?= $row['koordinat_bt'] ?? '—' ?></dd></div>
        </dl>
    </div>
    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/gully-plug/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Hapus data Gully Plug ini?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50">Hapus Gully Plug</button>
        </form>
    </div>
    <?php endif; ?>
</div>
