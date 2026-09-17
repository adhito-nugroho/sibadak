<?php
/** @var array $result */
/** @var array $kabupatenList */
/** @var list<array{id:int,nama_lembaga:string,kabupaten_id:int,kabupaten_nama:string}> $kpsOptions */
/** @var int $filterKab */
/** @var int $filterKps */
/** @var int $filterTahun */
/** @var string $filterStatus */
/** @var string $filterQ */
/** @var list<int> $yearOptions */
/** @var string|null $selectedKpsName */
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
    <div>
        <p class="page-toolbar-meta">
            Rencana kerja tahunan · <strong><?= format_id($total) ?></strong> entri · halaman <?= $cur ?>/<?= max(1, $pages) ?>
        </p>
        <?php if ($filterKps > 0): ?>
        <p class="text-xs text-forest-700 mt-0.5">Filter: <?= htmlspecialchars($selectedKpsName ?? ('KPS ID ' . $filterKps), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/rkt/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah RKT
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/rkt', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
    <div class="filter-field filter-field-lg">
        <label for="f-kps">KPS</label>
        <select id="f-kps" name="kps_id">
            <option value="">Semua KPS</option>
            <?php foreach ($kpsOptions as $kps): ?>
            <option value="<?= (int) $kps['id'] ?>" <?= $filterKps === (int) $kps['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kps['kabupaten_nama'] . ' — ' . $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-sm">
        <label for="f-tahun">Tahun</label>
        <select id="f-tahun" name="tahun">
            <option value="">Semua</option>
            <?php foreach ($yearOptions as $th): ?>
            <option value="<?= $th ?>" <?= $filterTahun === $th ? 'selected' : '' ?>><?= $th ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-sm">
        <label for="f-status">Status</label>
        <select id="f-status" name="status">
            <option value="">Semua</option>
            <?php foreach (RktKps::statusList() as $st): ?>
            <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="KPS / SK / catatan…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/rkt', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">KPS</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">Tahun</th>
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
                            <div class="empty-state-icon"><i class="ti ti-calendar-event"></i></div>
                            <p class="empty-state-title">Belum ada RKT yang cocok</p>
                            <p class="empty-state-desc">Ubah filter atau reset pencarian untuk menampilkan rencana kerja tahunan.</p>
                        </div>
                    </td>
                </tr>
                <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="cell-stack" title="<?= htmlspecialchars((string) $r['kps_nama'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="font-medium text-gray-800"><?= htmlspecialchars((string) $r['kps_nama'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="sub"><?= htmlspecialchars((string) $r['kps_no_sk'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="num-active"><?= (int) $r['tahun'] ?></span></td>
                    <td><span class="badge <?= $statusBadge((string) $r['status']) ?>"><?= ucfirst((string) $r['status']) ?></span></td>
                    <td>
                        <?php if (!empty($r['dokumen_link'])): ?>
                        <a href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $r['dokumen_link'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="btn-icon" title="Unduh dokumen"><i class="ti ti-download"></i></a>
                        <?php else: ?>
                        <span class="num-empty-hint">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-aksi">
                        <div class="inline-flex items-center gap-1 justify-end">
                            <a href="<?= htmlspecialchars(APP_URL . '/rkt/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                            <?php if ($canMut): ?>
                            <a href="<?= htmlspecialchars(APP_URL . '/rkt/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="btn-icon btn-icon-muted" title="Edit"><i class="ti ti-pencil"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
