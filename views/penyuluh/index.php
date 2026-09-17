<?php
/** @var array $result */
/** @var string $filterQ */
/** @var string $filterStatus */
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
        Master penyuluh HHK/HHBK · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/penyuluh/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah Penyuluh
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/penyuluh', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="NIP / nama / pangkat / jabatan…">
    </div>
    <div class="filter-field filter-field-sm">
        <label for="f-status">Status</label>
        <select id="f-status" name="status">
            <option value="">Semua</option>
            <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= $filterStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/penyuluh', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">NIP</th>
                    <th class="text-left">Nama</th>
                    <th class="text-left">Pangkat</th>
                    <th class="text-left">Jabatan</th>
                    <th class="text-left">Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="6" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-user-heart"></i></div>
                            <p class="empty-state-title">Belum ada penyuluh yang cocok</p>
                            <p class="empty-state-desc">Ubah filter status atau reset pencarian.</p>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($rows as $r): ?>
                <?php $aktif = (int) $r['is_active'] === 1; ?>
                <tr>
                    <td class="mono-clip"><?= htmlspecialchars((string) $r['nip'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="font-medium text-gray-800"><?= htmlspecialchars((string) $r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $r['pangkat'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td title="<?= htmlspecialchars((string) $r['jabatan'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['jabatan'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $aktif ? 'badge-aktif' : 'badge-nonaktif' ?>"><?= $aktif ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="col-aksi">
                        <?php if ($canMut): ?>
                        <a href="<?= htmlspecialchars(APP_URL . '/penyuluh/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
                        <?php else: ?>
                        <span class="num-empty-hint">—</span>
                        <?php endif; ?>
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
