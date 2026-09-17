<?php
/** @var array $result */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var int $filterKab */
/** @var int $filterTahun */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
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
        Alat &amp; Sarana Ekonomi Produktif · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/aep/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah AEP
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama KTH / jenis bantuan…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Nama KTH</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">Jenis Bantuan</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-left">Tahun</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="6" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-shovel"></i></div>
                            <p class="empty-state-title">Belum ada AEP yang cocok</p>
                            <p class="empty-state-desc">Ubah filter atau reset pencarian untuk menampilkan data bantuan sarana.</p>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($rows as $r): ?>
                <?php $jumlah = (int) $r['jumlah']; ?>
                <tr>
                    <td class="font-medium text-gray-800" title="<?= htmlspecialchars((string) $r['nama_kth'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['nama_kth'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td title="<?= htmlspecialchars((string) $r['jenis_bantuan'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['jenis_bantuan'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-right">
                        <?php if ($jumlah === 0): ?>
                        <span class="num-empty-hint">—</span>
                        <?php else: ?>
                        <span class="num-active"><?= format_id($jumlah) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="num-active"><?= (int) $r['tahun'] ?></span></td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/aep/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
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
