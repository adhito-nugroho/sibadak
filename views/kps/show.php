<?php
/** @var array<string, mixed> $row */
/** @var array{total:int,sudah:int,proses:int,belum:int,latest_tahun:?int,latest_status:?string} $rktSummary */
/** @var list<array{tahun:int,status:string,catatan:?string,id:int}> $rktRows */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$skColors = match ((string) $row['skema']) {
    'HKm' => 'badge bg-blue-50 text-blue-800 border border-blue-100',
    'HD' => 'badge bg-amber-50 text-amber-800 border border-amber-100',
    'HTR' => 'badge bg-violet-50 text-violet-800 border border-violet-100',
    'Kulin KK' => 'badge bg-forest-100 text-forest-800 border border-forest-200',
    'IPHPS' => 'badge bg-teal-50 text-teal-800 border border-teal-100',
    default => 'badge bg-gray-100 text-gray-700',
};
?>
<div class="max-w-4xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Kelompok Perhutanan Sosial</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= htmlspecialchars((string) $row['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-500 mt-2 flex flex-wrap items-center gap-2">
                <span class="<?= htmlspecialchars($skColors, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $row['skema'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-gray-400">·</span>
                <span><?= htmlspecialchars((string) $row['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $row['kecamatan_nama'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $row['desa_nama'], ENT_QUOTES, 'UTF-8') ?></span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Daftar</a>
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $row['id'] . '/anggota', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Anggota KPS</a>
            <?php if ($canMut): ?>
            <a href="<?= htmlspecialchars(APP_URL . '/rkt/create?kps_id=' . (int) $row['id'], ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-forest-200 text-forest-700 hover:bg-forest-50 font-600">+ RKT</a>
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $row['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl bg-forest-600 text-white hover:bg-forest-700 font-600">Edit</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card p-6">
        <h3 class="font-display font-700 text-gray-800 text-sm mb-4">Surat keputusan &amp; luasan</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div class="sm:col-span-2"><dt class="text-gray-400 text-xs font-600 uppercase">Nomor SK</dt><dd class="text-gray-800 mt-0.5 font-mono text-sm"><?= htmlspecialchars((string) $row['no_sk'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Luas wilayah (Ha)</dt><dd class="font-600 text-gray-800 mt-0.5"><?= $row['luas_wilayah_ha'] !== null ? format_id((float) $row['luas_wilayah_ha'], 2) : '—' ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Jumlah KK</dt><dd class="font-600 text-gray-800 mt-0.5"><?= format_id((int) $row['jumlah_kk']) ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Jumlah KUPS</dt><dd class="text-gray-800 mt-0.5"><?= format_id((int) $row['jumlah_kups']) ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Pendamping</dt><dd class="text-gray-700 mt-0.5"><?= $row['nama_pendamping'] !== null && $row['nama_pendamping'] !== '' ? htmlspecialchars((string) $row['nama_pendamping'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
        </dl>
    </div>

    <div class="stat-card p-6">
        <h3 class="font-display font-700 text-gray-800 text-sm mb-4">Tautan &amp; penandaan</h3>
        <dl class="space-y-3 text-sm">
            <div><dt class="text-gray-400 text-xs font-600 uppercase">Bukti SK</dt><dd class="mt-0.5"><?php if (!empty($row['bukti_sk_link'])): ?><a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $row['bukti_sk_link'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-forest-600 hover:underline break-all"><?= htmlspecialchars((string) $row['bukti_sk_link'], ENT_QUOTES, 'UTF-8') ?></a><?php else: ?>—<?php endif; ?></dd></div>
            <div><dt class="text-gray-400 text-xs font-600 uppercase">RKPS</dt><dd class="mt-0.5"><?php if (!empty($row['rkps_link'])): ?><a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $row['rkps_link'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-forest-600 hover:underline break-all"><?= htmlspecialchars((string) $row['rkps_link'], ENT_QUOTES, 'UTF-8') ?></a><?php else: ?>—<?php endif; ?></dd></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div><dt class="text-gray-400 text-xs font-600 uppercase">Penandaan batas areal</dt><dd class="text-gray-700 mt-0.5"><?= $row['penandaan_batas_areal'] !== null && $row['penandaan_batas_areal'] !== '' ? htmlspecialchars((string) $row['penandaan_batas_areal'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
                <div><dt class="text-gray-400 text-xs font-600 uppercase">Penandaan batas andil</dt><dd class="text-gray-700 mt-0.5"><?= $row['penandaan_batas_andil'] !== null && $row['penandaan_batas_andil'] !== '' ? htmlspecialchars((string) $row['penandaan_batas_andil'], ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
            </div>
        </dl>
    </div>

    <div class="stat-card p-6">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="font-display font-700 text-gray-800 text-sm">RKT per tahun</h3>
                <p class="text-xs text-gray-400 mt-0.5">Monitoring rencana kerja tahunan untuk KPS ini.</p>
            </div>
            <div class="flex gap-2">
                <a href="<?= htmlspecialchars(APP_URL . '/rkt?kps_id=' . (int) $row['id'], ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Lihat semua</a>
                <?php if ($canMut): ?>
                <a href="<?= htmlspecialchars(APP_URL . '/rkt/create?kps_id=' . (int) $row['id'], ENT_QUOTES, 'UTF-8') ?>" class="px-3 py-1.5 text-xs rounded-lg border border-forest-200 text-forest-700 hover:bg-forest-50 font-600">+ Tambah RKT</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
            <div class="rounded-xl border border-gray-100 bg-white px-3 py-2"><p class="text-[10px] text-gray-400 uppercase">Total</p><p class="font-700 text-gray-800"><?= format_id($rktSummary['total']) ?></p></div>
            <div class="rounded-xl border border-green-100 bg-green-50/50 px-3 py-2"><p class="text-[10px] text-green-600 uppercase">Sudah</p><p class="font-700 text-green-700"><?= format_id($rktSummary['sudah']) ?></p></div>
            <div class="rounded-xl border border-amber-100 bg-amber-50/50 px-3 py-2"><p class="text-[10px] text-amber-600 uppercase">Proses</p><p class="font-700 text-amber-700"><?= format_id($rktSummary['proses']) ?></p></div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2"><p class="text-[10px] text-slate-500 uppercase">Belum</p><p class="font-700 text-slate-700"><?= format_id($rktSummary['belum']) ?></p></div>
            <div class="rounded-xl border border-gray-100 bg-white px-3 py-2"><p class="text-[10px] text-gray-400 uppercase">Terakhir</p><p class="font-700 text-gray-800"><?= $rktSummary['latest_tahun'] !== null ? (int) $rktSummary['latest_tahun'] . ' · ' . ucfirst((string) $rktSummary['latest_status']) : '—' ?></p></div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead><tr class="border-b border-gray-100"><th class="px-3 py-2 text-left">Tahun</th><th class="px-3 py-2 text-left">Status</th><th class="px-3 py-2 text-left">Catatan</th><th class="px-3 py-2 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                <?php if ($rktRows === []): ?>
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data RKT untuk KPS ini.</td></tr>
                <?php else: foreach ($rktRows as $rk): ?>
                    <tr>
                        <td class="px-3 py-2 text-sm font-600 text-gray-700"><?= (int) $rk['tahun'] ?></td>
                        <td class="px-3 py-2 text-sm text-gray-600"><?= ucfirst((string) $rk['status']) ?></td>
                        <td class="px-3 py-2 text-sm text-gray-500">
                            <?php
                            $note = $rk['catatan'] !== null && $rk['catatan'] !== '' ? (string) $rk['catatan'] : '';
                            $doc = $rk['dokumen_link'] ?? null;
                            ?>
                            <?= $note !== '' ? htmlspecialchars($note, ENT_QUOTES, 'UTF-8') : '—' ?>
                            <?php if (!empty($doc)): ?>
                                <div class="mt-1">
                                    <a class="text-xs font-600 text-forest-600 hover:underline" target="_blank" rel="noopener"
                                       href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $doc, '/'), ENT_QUOTES, 'UTF-8') ?>">Dokumen</a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 text-right"><a href="<?= htmlspecialchars(APP_URL . '/rkt/' . (int) $rk['id'], ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-600 text-forest-600 hover:underline">Detail</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kegiatan Terkait -->
    <?php $hasRelated = !empty($relatedRhl) || !empty($relatedKbr) || !empty($relatedAep); ?>
    <?php if ($hasRelated): ?>
    <div class="stat-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-sm font-semibold text-gray-800">Kegiatan Terkait</h3>
            <p class="text-[10px] text-gray-400 mt-0.5">Kegiatan yang melibatkan KPS ini sebagai pelaksana</p>
        </div>
        <div class="divide-y divide-gray-100">

            <?php if (!empty($relatedRhl)): ?>
            <div class="px-6 py-4">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <i class="ti ti-leaf text-green-500 text-sm leading-none"></i> RHL (Rehabilitasi Hutan & Lahan)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($relatedRhl as $r): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/rhl/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div>
                            <p class="text-xs font-medium text-gray-800 group-hover:text-forest-700"><?= htmlspecialchars((string) $r['kegiatan'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Tahun <?= (int) $r['tahun'] ?><?= $r['luas_ha'] !== null ? ' · ' . format_id((float) $r['luas_ha'], 2) . ' Ha' : '' ?></p>
                        </div>
                        <i class="ti ti-chevron-right text-gray-300 group-hover:text-forest-600 text-base leading-none"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatedKbr)): ?>
            <div class="px-6 py-4">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <i class="ti ti-plant text-teal-500 text-sm leading-none"></i> KBR (Kebun Bibit Rakyat)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($relatedKbr as $k): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/kbr/' . (int) $k['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div>
                            <p class="text-xs font-medium text-gray-800 group-hover:text-forest-700">KBR <?= $k['tahun_tanam'] !== null ? 'Tahun ' . (int) $k['tahun_tanam'] : '' ?></p>
                            <p class="text-[10px] text-gray-400 mt-0.5"><?= $k['subdas'] !== null ? 'SubDAS: ' . htmlspecialchars((string) $k['subdas'], ENT_QUOTES, 'UTF-8') : '' ?></p>
                        </div>
                        <i class="ti ti-chevron-right text-gray-300 group-hover:text-forest-600 text-base leading-none"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatedAep)): ?>
            <div class="px-6 py-4">
                <h4 class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <i class="ti ti-tool text-purple-500 text-sm leading-none"></i> AEP (Alat & Sarana Ekonomi Produktif)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($relatedAep as $a): ?>
                    <a href="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $a['id'], ENT_QUOTES, 'UTF-8') ?>" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div>
                            <p class="text-xs font-medium text-gray-800 group-hover:text-forest-700"><?= htmlspecialchars((string) $a['jenis_bantuan'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Tahun <?= (int) $a['tahun'] ?> · Jumlah: <?= format_id((int) $a['jumlah']) ?></p>
                        </div>
                        <i class="ti ti-chevron-right text-gray-300 group-hover:text-forest-600 text-base leading-none"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    <?php if ($canMut): ?>
    <div class="flex justify-end">
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Menghapus KPS akan ikut menghapus data RKT terkait di basis data. Lanjutkan?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">Hapus data KPS</button>
        </form>
    </div>
    <?php endif; ?>
</div>
