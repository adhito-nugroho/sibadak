<?php
/** @var array $result */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
$statusBadge = static function (string $s): string {
    return match (strtolower($s)) {
        'sudah' => 'badge-sudah',
        'proses' => 'badge-proses',
        default => 'badge-belum',
    };
};
?>
<div class="page-toolbar fade-up">
    <p class="page-toolbar-meta">
        Rencana Kelola Perhutanan Sosial · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/rkps/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah RKPS
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
        <label for="f-status">Status</label>
        <select id="f-status" name="status">
            <option value="">Semua</option>
            <?php foreach (RkpsKps::statusList() as $st): ?>
            <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama KPS…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">KPS</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">Periode</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Dokumen</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="6" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-notebook"></i></div>
                            <p class="empty-state-title">Belum ada RKPS yang cocok</p>
                            <p class="empty-state-desc">Ubah filter atau reset pencarian.</p>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="font-medium text-gray-800" title="<?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="num-active"><?= (int) $r['periode_awal'] ?>–<?= (int) $r['periode_akhir'] ?></span></td>
                    <td><span class="badge <?= $statusBadge((string) $r['status']) ?>"><?= ucfirst((string) $r['status']) ?></span></td>
                    <td>
                        <?php if (!empty($r['dokumen_link'])): ?>
                        <i class="ti ti-file-check text-forest-600" title="Ada dokumen"></i>
                        <?php else: ?>
                        <span class="num-empty-hint">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/rkps/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/rkps/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
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
            <?php if ($cur > 1): ?><a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $cur - 1])), ENT_QUOTES, 'UTF-8') ?>">« Prev</a><?php endif; ?>
            <?php if ($cur < $pages): ?><a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $cur + 1])), ENT_QUOTES, 'UTF-8') ?>">Next »</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
