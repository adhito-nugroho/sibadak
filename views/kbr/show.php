<?php
/** @var array<string,mixed> $row */
/** @var list<array<string,mixed>> $tanamans */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
?>
<div class="max-w-3xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Kebun Bibit Rakyat</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= htmlspecialchars((string) $row['nama_kth'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars((string) ($row['kabupaten_nama'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><?= $row['tahun_tanam'] !== null ? ' · Tahun ' . (int) $row['tahun_tanam'] : '' ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kbr', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/kbr/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl bg-forest-600 text-white hover:bg-forest-700">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Lokasi</dt><dd class="text-gray-800 mt-0.5"><?= $row['lokasi'] !== null && $row['lokasi'] !== '' ? nl2br(htmlspecialchars((string) $row['lokasi'], ENT_QUOTES, 'UTF-8')) : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Desa</dt><dd class="text-gray-700 mt-0.5"><?= $row['desa_nama'] !== null && $row['desa_nama'] !== '' ? htmlspecialchars((string) $row['desa_nama'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">SubDAS</dt><dd class="text-gray-700 mt-0.5"><?= $row['subdas'] !== null && $row['subdas'] !== '' ? htmlspecialchars((string) $row['subdas'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Koordinat</dt><dd class="text-gray-700 mt-0.5 font-mono text-xs"><?= $row['koordinat_ls'] !== null ? htmlspecialchars((string) $row['koordinat_ls'], ENT_QUOTES, 'UTF-8') : '—' ?>, <?= $row['koordinat_bt'] !== null ? htmlspecialchars((string) $row['koordinat_bt'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
        </dl>
    </div>

    <div class="stat-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-600 text-gray-800">Rincian Tanaman</h3>
        </div>

        <?php if ($canMut): ?>
        <div class="px-6 py-4 border-b border-gray-100 bg-white">
            <form method="post" action="<?= htmlspecialchars(APP_URL . '/kbr/' . (int) $row['id'] . '/tanaman/store', ENT_QUOTES, 'UTF-8') ?>" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jenis Tanaman</label>
                    <input type="text" name="jenis" required placeholder="Contoh: Jati, Mahoni..." class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jumlah (Btg)</label>
                    <input type="number" name="jumlah_btg" required min="1" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Luas (Ha)</label>
                    <input type="text" name="luas_ha" inputmode="decimal" placeholder="0.0000" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700 transition">Tambah</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-600 text-left">
                        <th class="px-6 py-3 font-600">Jenis Tanaman</th>
                        <th class="px-6 py-3 font-600 text-right">Jumlah (Btg)</th>
                        <th class="px-6 py-3 font-600 text-right">Luas (Ha)</th>
                        <?php if ($canMut): ?><th class="px-6 py-3 font-600 text-right">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if ($tanamans === []): ?>
                    <tr><td colspan="<?= $canMut ? 4 : 3 ?>" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada data tanaman.</td></tr>
                    <?php else: ?>
                    <?php $totBtg = 0; $totLuas = 0.0; foreach ($tanamans as $t): $totBtg += (int) $t['jumlah_btg']; $totLuas += (float) ($t['luas_ha'] ?? 0); ?>
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-3 text-gray-800 font-500"><?= htmlspecialchars((string) $t['jenis'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-6 py-3 text-right text-gray-700 font-mono"><?= format_id((int) $t['jumlah_btg']) ?></td>
                        <td class="px-6 py-3 text-right text-gray-700 font-mono"><?= $t['luas_ha'] !== null ? format_id((float) $t['luas_ha'], 2) : '—' ?></td>
                        <?php if ($canMut): ?>
                        <td class="px-6 py-3 text-right">
                            <form method="post" action="<?= htmlspecialchars(APP_URL . '/kbr/tanaman/' . (int) $t['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline-block" onsubmit="return confirm('Hapus tanaman ini?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-600">Hapus</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray-50/50 border-t-2 border-gray-100 font-600 text-gray-800">
                        <td class="px-6 py-3 text-right">Total:</td>
                        <td class="px-6 py-3 text-right font-mono"><?= format_id($totBtg) ?></td>
                        <td class="px-6 py-3 text-right font-mono"><?= format_id($totLuas, 2) ?></td>
                        <?php if ($canMut): ?><td></td><?php endif; ?>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/kbr/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Hapus data KBR ini beserta semua tanaman?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">Hapus KBR</button>
        </form>
    </div>
    <?php endif; ?>
</div>
