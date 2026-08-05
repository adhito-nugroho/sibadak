<?php
/** @var array<string, mixed> $kps */
/** @var list<array<string, mixed>> $ruang */
/** @var list<array<string, mixed>> $andil */
/** @var bool $canMut */
?>

<div class="max-w-6xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Anggota KPS</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= htmlspecialchars((string) $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-500 mt-2">
                <?= htmlspecialchars((string) $kps['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> ·
                <?= htmlspecialchars((string) $kps['kecamatan_nama'], ENT_QUOTES, 'UTF-8') ?> ·
                <?= htmlspecialchars((string) $kps['desa_nama'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'], ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Kembali ke detail KPS</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'] . '/anggota/import', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-forest-200 text-forest-700 hover:bg-forest-50 font-600">Import Excel</a>
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'] . '/anggota/create', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl bg-forest-600 text-white hover:bg-forest-700 font-600">+ Tambah anggota</a>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $renderTable = static function (string $title, array $rows, int $kpsId, bool $canMut) : void {
        ?>
        <div class="stat-card p-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="font-display font-700 text-gray-800 text-sm"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Total: <?= format_id(count($rows)) ?></p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-3 py-2 text-left">No Andil</th>
                            <th class="px-3 py-2 text-left">Nama Penggarap</th>
                            <th class="px-3 py-2 text-left">KK</th>
                            <th class="px-3 py-2 text-left">NIK</th>
                            <th class="px-3 py-2 text-left">Koordinat</th>
                            <th class="px-3 py-2 text-left">Batas (B/U/S/T)</th>
                            <th class="px-3 py-2 text-left">Desa</th>
                            <th class="px-3 py-2 text-left">Kecamatan</th>
                            <th class="px-3 py-2 text-left">Pengukur</th>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-left">Komoditas</th>
                            <th class="px-3 py-2 text-right">Luas (Ha)</th>
                            <?php if ($canMut): ?><th class="px-3 py-2 text-right">Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <?php if ($rows === []): ?>
                        <tr><td colspan="<?= $canMut ? 13 : 12 ?>" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data.</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-700 font-mono"><?= !empty($r['no_andil']) ? htmlspecialchars((string) $r['no_andil'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm font-600 text-gray-800"><?= htmlspecialchars((string) $r['nama_penggarap'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['no_kk']) ? htmlspecialchars((string) $r['no_kk'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['nik']) ? htmlspecialchars((string) $r['nik'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-xs text-gray-600">
                                <?php
                                $bt = !empty($r['koordinat_bt']) ? (string) $r['koordinat_bt'] : '';
                                $ls = !empty($r['koordinat_ls']) ? (string) $r['koordinat_ls'] : '';
                                echo ($bt !== '' || $ls !== '') ? htmlspecialchars(trim($bt . ' / ' . $ls, ' /'), ENT_QUOTES, 'UTF-8') : '—';
                                ?>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-600">
                                <?php
                                $b = !empty($r['batas_barat']) ? (string) $r['batas_barat'] : '';
                                $u = !empty($r['batas_utara']) ? (string) $r['batas_utara'] : '';
                                $s = !empty($r['batas_selatan']) ? (string) $r['batas_selatan'] : '';
                                $t = !empty($r['batas_timur']) ? (string) $r['batas_timur'] : '';
                                $parts = [];
                                if ($b !== '') { $parts[] = 'B:' . $b; }
                                if ($u !== '') { $parts[] = 'U:' . $u; }
                                if ($s !== '') { $parts[] = 'S:' . $s; }
                                if ($t !== '') { $parts[] = 'T:' . $t; }
                                echo $parts !== [] ? htmlspecialchars(implode(' · ', $parts), ENT_QUOTES, 'UTF-8') : '—';
                                ?>
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['desa_nama']) ? htmlspecialchars((string) $r['desa_nama'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['kecamatan_nama']) ? htmlspecialchars((string) $r['kecamatan_nama'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['pengukur']) ? htmlspecialchars((string) $r['pengukur'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['tanggal']) ? htmlspecialchars((string) $r['tanggal'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?= !empty($r['komoditas']) ? htmlspecialchars((string) $r['komoditas'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="px-3 py-2 text-sm text-gray-700 text-right"><?= $r['luas_ha'] !== null ? format_id((float) $r['luas_ha'], 2) : '—' ?></td>
                            <?php if ($canMut): ?>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <a href="<?= htmlspecialchars(APP_URL . '/kps/' . $kpsId . '/anggota/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Edit</a>
                                <form method="post" action="<?= htmlspecialchars(APP_URL . '/kps/' . $kpsId . '/anggota/' . (int) $r['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Hapus anggota ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="text-xs font-600 text-red-600 hover:underline ml-3">Hapus</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    };
    ?>

    <?php $renderTable('I. Ruang Perlindungan dan Komunal', $ruang, (int) $kps['id'], $canMut); ?>
    <?php $renderTable('II. Andil Garapan', $andil, (int) $kps['id'], $canMut); ?>
</div>

