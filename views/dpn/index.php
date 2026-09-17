<?php
/** @var array $result */
/** @var int $filterTahun */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
?>
<div class="page-toolbar fade-up">
    <p class="page-toolbar-meta">
        Dam Penahan · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/dpn/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah DPN
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/dpn', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Sasaran / lokasi / subdas…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/dpn', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Sasaran</th>
                    <th class="text-left">Lokasi</th>
                    <th class="text-left">SubDAS</th>
                    <th class="text-right">Unit</th>
                    <th class="text-left">Tahun</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="6" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-building-bridge"></i></div>
                            <p class="empty-state-title">Belum ada DPN yang cocok</p>
                            <p class="empty-state-desc">Ubah filter tahun atau reset pencarian.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <?php $unit = (int) $r['jumlah_unit']; ?>
                <tr>
                    <td class="font-medium text-gray-800"><?= htmlspecialchars((string) ($r['sasaran'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td title="<?= htmlspecialchars((string) $r['lokasi'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['lokasi'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $r['subdas'] ? htmlspecialchars((string) $r['subdas'], ENT_QUOTES, 'UTF-8') : '<span class="num-empty-hint">—</span>' ?></td>
                    <td class="text-right">
                        <?php if ($unit === 0): ?>
                        <span class="num-empty-hint">—</span>
                        <?php else: ?>
                        <span class="num-active"><?= format_id($unit) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $r['tahun'] ?></td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/dpn/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/dpn/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
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
    $qs = static function (int $p) use ($filterTahun, $filterQ): string {
        $g = array_filter([
            'page' => $p,
            'tahun' => $filterTahun > 0 ? $filterTahun : null,
            'q' => $filterQ !== '' ? $filterQ : null,
        ], static fn ($v) => $v !== null && $v !== '');
        return http_build_query($g);
    };
    ?>
    <div class="pager">
        <span class="pager-meta">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="pager-links">
            <?php if ($cur > 1): ?><a href="<?= htmlspecialchars(APP_URL . '/dpn?' . $qs($cur - 1), ENT_QUOTES, 'UTF-8') ?>">« Prev</a><?php endif; ?>
            <?php if ($cur < $pages): ?><a href="<?= htmlspecialchars(APP_URL . '/dpn?' . $qs($cur + 1), ENT_QUOTES, 'UTF-8') ?>">Next »</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
