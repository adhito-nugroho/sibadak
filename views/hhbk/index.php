<?php
/** @var array $result */
/** @var array $kabupatenList */
/** @var int $filterKab */
/** @var int $filterTahun */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
$bulanList = Hhbk::bulanList();
?>
<div class="page-toolbar fade-up">
    <p class="page-toolbar-meta">
        Hasil Hutan Bukan Kayu · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/hhbk/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah HHBK
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/hhbk', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
            <?php foreach (array_reverse($yearOptions) as $y): ?>
            <option value="<?= $y ?>" <?= $filterTahun === $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama KTH / penyuluh…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/hhbk', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">KTH / Pelaksana</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">Penyuluh</th>
                    <th class="text-left">Periode</th>
                    <th class="text-right">Total Btg Bln Ini</th>
                    <th class="text-right">Total Kg Bln Ini</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="7" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-leaf"></i></div>
                            <p class="empty-state-title">Belum ada HHBK yang cocok</p>
                            <p class="empty-state-desc">Ubah filter kabupaten/tahun atau reset pencarian.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="font-medium text-gray-800"><?= htmlspecialchars((string) ($r['nama_kth'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($r['kabupaten_nama'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($r['nama_penyuluh'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $bulanList[(int) $r['bulan']] ?> <?= (int) $r['tahun'] ?></td>
                    <td class="text-right"><?= format_id((float) ($r['total_btg_bulan_ini'] ?? 0), 2) ?> btg</td>
                    <td class="text-right"><?= format_id((float) ($r['total_kg_bulan_ini'] ?? 0), 2) ?> kg</td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/hhbk/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/hhbk/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
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
    <?php
    $qs = static function (int $p) use ($filterKab, $filterTahun, $filterQ): string {
        $g = array_filter([
            'page' => $p,
            'kabupaten_id' => $filterKab > 0 ? $filterKab : null,
            'tahun' => $filterTahun > 0 ? $filterTahun : null,
            'q' => $filterQ !== '' ? $filterQ : null,
        ], static fn ($v) => $v !== null && $v !== '');
        return http_build_query($g);
    };
    ?>
    <div class="pager">
        <span class="pager-meta">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="pager-links">
            <?php if ($cur > 1): ?><a href="<?= htmlspecialchars(APP_URL . '/hhbk?' . $qs($cur - 1), ENT_QUOTES, 'UTF-8') ?>">« Prev</a><?php endif; ?>
            <?php if ($cur < $pages): ?><a href="<?= htmlspecialchars(APP_URL . '/hhbk?' . $qs($cur + 1), ENT_QUOTES, 'UTF-8') ?>">Next »</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
