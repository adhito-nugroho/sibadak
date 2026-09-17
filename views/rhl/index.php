<?php
/** @var array $result */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var int $filterKab */
/** @var int $filterTahun */
/** @var string $filterKegiatan */
/** @var string $filterSumberDana */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
/** @var list<string> $kegiatanOptions */
/** @var list<string> $sumberDanaOptions */
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
        Rehabilitasi Hutan &amp; Lahan · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/rhl/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah RHL
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/rhl', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
    <div class="filter-field filter-field-sm">
        <label for="f-tahun">Tahun</label>
        <select id="f-tahun" name="tahun">
            <option value="">Semua</option>
            <?php foreach ($yearOptions as $th): ?>
            <option value="<?= $th ?>" <?= $filterTahun === $th ? 'selected' : '' ?>><?= $th ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-md">
        <label for="f-dana">Sumber Dana</label>
        <select id="f-dana" name="sumber_dana">
            <option value="">Semua</option>
            <?php foreach ($sumberDanaOptions as $sd): ?>
            <option value="<?= htmlspecialchars($sd, ENT_QUOTES, 'UTF-8') ?>" <?= $filterSumberDana === $sd ? 'selected' : '' ?>><?= htmlspecialchars($sd, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-lg">
        <label for="f-keg">Kegiatan</label>
        <select id="f-keg" name="kegiatan">
            <option value="">Semua</option>
            <?php foreach ($kegiatanOptions as $k): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $filterKegiatan === $k ? 'selected' : '' ?>><?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama pelaksana…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/rhl', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Pelaksana</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">Kegiatan</th>
                    <th class="text-left">Tahun</th>
                    <th class="text-left">Sumber Dana</th>
                    <th class="text-right">Luas (Ha)</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="7" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-seedling"></i></div>
                            <p class="empty-state-title">Belum ada RHL yang cocok</p>
                            <p class="empty-state-desc">Ubah filter atau reset pencarian untuk menampilkan data rehabilitasi hutan &amp; lahan.</p>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="font-medium text-gray-800" title="<?= htmlspecialchars((string) $r['nama_kth'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['nama_kth'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td title="<?= htmlspecialchars((string) $r['kegiatan'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['kegiatan'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="num-active"><?= (int) $r['tahun'] ?></span></td>
                    <td><?= htmlspecialchars((string) ($r['sumber_dana'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-right"><?= $r['luas_ha'] !== null ? '<span class="num-active">' . format_id((float) $r['luas_ha'], 2) . '</span>' : '<span class="num-empty-hint">—</span>' ?></td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/rhl/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/rhl/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="pager">
        <span class="pager-meta">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="pager-links">
            <?php if ($cur > 1): ?><a href="?<?= htmlspecialchars($qsBase(['page' => $cur - 1]), ENT_QUOTES, 'UTF-8') ?>">« Prev</a><?php endif; ?>
            <?php if ($cur < $pages): ?><a href="?<?= htmlspecialchars($qsBase(['page' => $cur + 1]), ENT_QUOTES, 'UTF-8') ?>">Next »</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
