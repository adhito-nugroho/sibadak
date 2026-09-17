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
<div class="page-toolbar fade-up">
    <p class="page-toolbar-meta">
        <strong><?= format_id($total) ?></strong> kelompok aktif · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/kth/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i>
        Tambah KTH
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
    <?php if (user_role() !== 'operator'): ?>
    <div class="filter-field filter-field-kab">
        <label for="f-kab">Kabupaten</label>
        <select id="f-kab" name="kabupaten_id">
            <option value="">Semua</option>
            <?php foreach ($kabupatenList as $kb): ?>
            <option value="<?= (int) $kb['id'] ?>" <?= $filterKab === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="filter-field filter-field-kelas">
        <label for="f-kelas">Kelas</label>
        <select id="f-kelas" name="kelas">
            <option value="">Semua</option>
            <option value="Utama" <?= $filterKelas === 'Utama' ? 'selected' : '' ?>>Utama</option>
            <option value="Madya" <?= $filterKelas === 'Madya' ? 'selected' : '' ?>>Madya</option>
            <option value="Pemula" <?= $filterKelas === 'Pemula' ? 'selected' : '' ?>>Pemula</option>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama KTH atau kode register…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<?php require view_path('partials/bulk-bar.php'); ?>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-check">
                        <input type="checkbox" id="check-all" title="Pilih semua di halaman ini" aria-label="Pilih semua">
                    </th>
                    <th class="col-name text-left th-sortable">Nama KTH <i class="ti ti-arrows-sort sort-icon text-xs" aria-hidden="true"></i></th>
                    <th class="col-register text-left th-sortable">Register <i class="ti ti-arrows-sort sort-icon text-xs" aria-hidden="true"></i></th>
                    <th class="col-kab text-left">Kabupaten</th>
                    <th class="col-kec text-left">Kecamatan</th>
                    <th class="col-kelas text-left th-sortable">Kelas <i class="ti ti-arrows-sort sort-icon text-xs" aria-hidden="true"></i></th>
                    <th class="col-anggota th-sortable">Anggota <i class="ti ti-arrows-sort sort-icon text-xs" aria-hidden="true"></i></th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="8" style="white-space:normal; overflow:visible;">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-trees"></i></div>
                            <p class="empty-state-title">Belum ada KTH yang cocok</p>
                            <p class="empty-state-desc">Ubah filter kabupaten/kelas, atau reset pencarian untuk menampilkan kelompok tani hutan.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <?php
                    $anggota = (int) $r['jumlah_anggota'];
                    $reg = (string) $r['kode_register'];
                    $bc = match ($r['kelas']) {
                        'Utama' => 'badge-utama',
                        'Madya' => 'badge-madya',
                        default => 'badge-pemula',
                    };
                ?>
                <tr>
                    <td class="col-check">
                        <input type="checkbox" class="row-check" name="ids[]" value="<?= (int) $r['id'] ?>" aria-label="Pilih <?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8') ?>">
                    </td>
                    <td class="col-name" title="<?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="col-register mono-clip" title="<?= htmlspecialchars($reg, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($reg, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="col-kab" title="<?= htmlspecialchars($r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="col-kec" title="<?= htmlspecialchars($r['kecamatan_nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r['kecamatan_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="col-kelas"><span class="badge <?= $bc ?>"><?= htmlspecialchars($r['kelas'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="col-anggota">
                        <?php if ($anggota === 0): ?>
                        <span class="num-empty-hint" title="Belum ada anggota">—</span>
                        <?php else: ?>
                        <span class="num-active"><?= format_id($anggota) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail">
                                <i class="ti ti-eye"></i>
                            </a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/kth/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit">
                                <i class="ti ti-pencil"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 border-t border-gray-100 bg-gray-50/60">
        <span class="text-xs text-gray-500">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="flex gap-2">
            <?php if ($cur > 1): ?>
            <a class="text-xs px-2.5 py-1 rounded-md border border-gray-200 bg-white hover:bg-gray-50" href="?<?= htmlspecialchars($qsBase(['page' => $cur - 1]), ENT_QUOTES, 'UTF-8') ?>">« Prev</a>
            <?php endif; ?>
            <?php if ($cur < $pages): ?>
            <a class="text-xs px-2.5 py-1 rounded-md border border-gray-200 bg-white hover:bg-gray-50" href="?<?= htmlspecialchars($qsBase(['page' => $cur + 1]), ENT_QUOTES, 'UTF-8') ?>">Next »</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
