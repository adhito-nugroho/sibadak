<?php
/** @var array $result */
/** @var array $kabupatenList */
/** @var int $filterKab */
/** @var string $filterSkema */
/** @var string $filterQ */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
$skemaOpts = Kps::skemaList();
$skemaBadge = static function (string $s): string {
    return match ($s) {
        'HKm' => 'badge-skema-hkm',
        'HD' => 'badge-skema-hd',
        'HTR' => 'badge-skema-htr',
        'Kulin KK' => 'badge-skema-kulin',
        'IPHPS' => 'badge-skema-iphps',
        default => 'badge-pemula',
    };
};
?>
<div class="page-toolbar fade-up">
    <p class="page-toolbar-meta">
        Kelompok Perhutanan Sosial · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/kps/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah KPS
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
    <div class="filter-field filter-field-md">
        <label for="f-skema">Skema</label>
        <select id="f-skema" name="skema">
            <option value="">Semua</option>
            <?php foreach ($skemaOpts as $sk): ?>
            <option value="<?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?>" <?= $filterSkema === $sk ? 'selected' : '' ?>><?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama lembaga / no. SK…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Nama lembaga</th>
                    <th class="text-left">Skema</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">Desa</th>
                    <th class="text-right">Luas (Ha)</th>
                    <th class="text-right">KK</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="7" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-map"></i></div>
                            <p class="empty-state-title">Belum ada KPS yang cocok</p>
                            <p class="empty-state-desc">Ubah filter kabupaten/skema atau reset pencarian.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <?php $kk = (int) $r['jumlah_kk']; ?>
                <tr>
                    <td class="cell-stack" title="<?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="font-medium text-gray-800"><?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="sub"><?= htmlspecialchars((string) $r['no_sk'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td><span class="badge <?= $skemaBadge((string) $r['skema']) ?>"><?= htmlspecialchars((string) $r['skema'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $r['desa_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-right"><?= $r['luas_wilayah_ha'] !== null ? format_id((float) $r['luas_wilayah_ha'], 2) : '<span class="num-empty-hint">—</span>' ?></td>
                    <td class="text-right">
                        <?php if ($kk === 0): ?>
                        <span class="num-empty-hint" title="Belum ada KK">—</span>
                        <?php else: ?>
                        <span class="num-active"><?= format_id($kk) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
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
    $qs = static function (int $p) use ($filterKab, $filterSkema, $filterQ): string {
        $g = array_filter([
            'page' => $p,
            'kabupaten_id' => $filterKab > 0 ? $filterKab : null,
            'skema' => $filterSkema !== '' ? $filterSkema : null,
            'q' => $filterQ !== '' ? $filterQ : null,
        ], static fn ($v) => $v !== null && $v !== '');
        return http_build_query($g);
    };
    ?>
    <div class="pager">
        <span class="pager-meta">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="pager-links">
            <?php if ($cur > 1): ?><a href="<?= htmlspecialchars(APP_URL . '/kps?' . $qs($cur - 1), ENT_QUOTES, 'UTF-8') ?>">« Prev</a><?php endif; ?>
            <?php if ($cur < $pages): ?><a href="<?= htmlspecialchars(APP_URL . '/kps?' . $qs($cur + 1), ENT_QUOTES, 'UTF-8') ?>">Next »</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
