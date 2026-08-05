<?php
/** @var array $row */
/** @var list<array<string,mixed>> $anggota */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$badge = match ($row['kelas']) {
    'Utama' => 'badge-utama',
    'Madya' => 'badge-madya',
    default => 'badge-pemula',
};
?>
<div class="max-w-4xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Detail KTH</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-500 font-mono mt-1"><?= htmlspecialchars($row['kode_register'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan/create?kth_id=' . (int) $row['id'], ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-forest-200 text-forest-700 hover:bg-forest-50 font-600">+ Anggota</a>
            <a href="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl bg-forest-600 text-white hover:bg-forest-700 font-600">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card p-6">
        <div class="flex flex-wrap gap-3 items-center mb-4">
            <span class="badge <?= $badge ?>"><?= htmlspecialchars($row['kelas'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="text-sm text-gray-500"><?= htmlspecialchars($row['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($row['kecamatan_nama'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($row['desa_nama'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Anggota</dt><dd class="font-600 text-gray-800 mt-0.5"><?= format_id((int) $row['jumlah_anggota']) ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Jenis usaha</dt><dd class="text-gray-700 mt-0.5"><?= $row['jenis_usaha'] !== null && $row['jenis_usaha'] !== '' ? htmlspecialchars((string) $row['jenis_usaha'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Dusun / blok</dt><dd class="text-gray-700 mt-0.5"><?= $row['dusun_blok'] !== null && $row['dusun_blok'] !== '' ? htmlspecialchars((string) $row['dusun_blok'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Koordinat</dt><dd class="text-gray-700 mt-0.5 font-mono text-xs"><?= $row['koordinat_ls'] !== null ? htmlspecialchars((string) $row['koordinat_ls'], ENT_QUOTES, 'UTF-8') : '—' ?>, <?= $row['koordinat_bt'] !== null ? htmlspecialchars((string) $row['koordinat_bt'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
        </dl>
    </div>

    <div class="stat-card p-6">
        <h3 class="font-display font-700 text-gray-800 text-sm mb-4">Dokumen</h3>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-gray-400 text-xs font-600 uppercase">SK KTH</dt>
                <dd class="mt-0.5">
                    <?php if (!empty($row['link_sk_kth'])): ?>
                        <a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $row['link_sk_kth'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-forest-600 hover:underline break-all"><?= htmlspecialchars((string) $row['link_sk_kth'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>—<?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-gray-400 text-xs font-600 uppercase">SK Kades</dt>
                <dd class="mt-0.5">
                    <?php if (!empty($row['link_sk_kades'])): ?>
                        <a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $row['link_sk_kades'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-forest-600 hover:underline break-all"><?= htmlspecialchars((string) $row['link_sk_kades'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>—<?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-gray-400 text-xs font-600 uppercase">Sertifikat</dt>
                <dd class="mt-0.5">
                    <?php if (!empty($row['link_sertifikat'])): ?>
                        <a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $row['link_sertifikat'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-forest-600 hover:underline break-all"><?= htmlspecialchars((string) $row['link_sertifikat'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>—<?php endif; ?>
                </dd>
            </div>
        </dl>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Nonaktifkan KTH ini dari sistem?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">Nonaktifkan KTH</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Kegiatan Terkait -->
    <?php
    $hasKegiatan = !empty($kegiatan['rhl']) || !empty($kegiatan['kbr']) || !empty($kegiatan['aep']) || !empty($kegiatan['kps']);
    ?>
    <div class="stat-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-display font-700 text-gray-800 text-base">Kegiatan Terkait</h3>
            <p class="text-xs text-gray-400 mt-0.5">Semua kegiatan yang melibatkan KTH ini</p>
        </div>

        <?php if (!$hasKegiatan): ?>
        <div class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada kegiatan terkait KTH ini.</div>
        <?php else: ?>
        <div class="divide-y divide-gray-100">

            <?php if (!empty($kegiatan['kps'])): ?>
            <div class="px-6 py-4">
                <h4 class="text-xs font-600 text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> KPS (Perhutanan Sosial)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($kegiatan['kps'] as $k): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $k['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition group">
                        <div>
                            <p class="text-sm font-500 text-gray-800 group-hover:text-forest-700"><?= htmlspecialchars((string) $k['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-xs text-gray-400 mt-0.5">Skema: <?= htmlspecialchars((string) $k['skema'], ENT_QUOTES, 'UTF-8') ?><?= $k['luas_wilayah_ha'] !== null ? ' · ' . format_id((float) $k['luas_wilayah_ha'], 2) . ' Ha' : '' ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-forest-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($kegiatan['rhl'])): ?>
            <div class="px-6 py-4">
                <h4 class="text-xs font-600 text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> RHL (Rehabilitasi Hutan & Lahan)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($kegiatan['rhl'] as $r): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/rhl/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition group">
                        <div>
                            <p class="text-sm font-500 text-gray-800 group-hover:text-forest-700"><?= htmlspecialchars((string) $r['kegiatan'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-xs text-gray-400 mt-0.5">Tahun <?= (int) $r['tahun'] ?><?= $r['luas_ha'] !== null ? ' · ' . format_id((float) $r['luas_ha'], 2) . ' Ha' : '' ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-forest-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($kegiatan['kbr'])): ?>
            <div class="px-6 py-4">
                <h4 class="text-xs font-600 text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span> KBR (Kebun Bibit Rakyat)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($kegiatan['kbr'] as $k): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/kbr/' . (int) $k['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition group">
                        <div>
                            <p class="text-sm font-500 text-gray-800 group-hover:text-forest-700">KBR <?= $k['tahun_tanam'] !== null ? 'Tahun ' . (int) $k['tahun_tanam'] : '' ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?= $k['subdas'] !== null ? 'SubDAS: ' . htmlspecialchars((string) $k['subdas'], ENT_QUOTES, 'UTF-8') : '' ?><?= $k['lokasi'] !== null ? ' · ' . htmlspecialchars(mb_strimwidth((string) $k['lokasi'], 0, 60, '...'), ENT_QUOTES, 'UTF-8') : '' ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-forest-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($kegiatan['aep'])): ?>
            <div class="px-6 py-4">
                <h4 class="text-xs font-600 text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> AEP (Alat & Sarana Ekonomi Produktif)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($kegiatan['aep'] as $a): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $a['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition group">
                        <div>
                            <p class="text-sm font-500 text-gray-800 group-hover:text-forest-700"><?= htmlspecialchars((string) $a['jenis_bantuan'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-xs text-gray-400 mt-0.5">Tahun <?= (int) $a['tahun'] ?> · Jumlah: <?= format_id((int) $a['jumlah']) ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-forest-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>
    </div>

    <div class="stat-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-display font-700 text-gray-800 text-base">Anggota &amp; Pengurus</h3>
            <p class="text-xs text-gray-400 mt-0.5"><?= format_id((int) $row['jumlah_anggota']) ?> anggota terdaftar</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Posisi</th>
                        <th class="px-4 py-3 text-left">NIK</th>
                        <th class="px-4 py-3 text-left">Telepon</th>
                        <?php if ($canMut): ?>
                        <th class="px-4 py-3 text-right">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if ($anggota === []): ?>
                    <tr><td colspan="<?= $canMut ? 5 : 4 ?>" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada data anggota.</td></tr>
                    <?php else: ?>
                    <?php foreach ($anggota as $a): ?>
                    <tr>
                        <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars((string) $a['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-3 text-gray-600 text-sm"><?= htmlspecialchars((string) $a['posisi'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-3 text-gray-500 text-xs font-mono"><?= $a['nik'] !== null && $a['nik'] !== '' ? htmlspecialchars((string) $a['nik'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                        <td class="px-4 py-3 text-gray-500 text-sm"><?= $a['no_telpon'] !== null && $a['no_telpon'] !== '' ? htmlspecialchars((string) $a['no_telpon'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                        <?php if ($canMut): ?>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan/' . (int) $a['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Detail</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Nonaktifkan KTH ini dari sistem?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">Nonaktifkan KTH</button>
        </form>
    </div>
    <?php endif; ?>
</div>
