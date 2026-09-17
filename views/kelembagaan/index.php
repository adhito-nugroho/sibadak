<?php
/** @var array $result */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var list<array{id:int,kode_register:string,nama:string,kabupaten_nama:string}> $kthOptions */
/** @var int $filterKab */
/** @var int $filterKth */
/** @var string $filterPosisi */
/** @var string $filterQ */
$canMut = in_array(user_role(), ['admin', 'operator'], true);
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
$posisiOpts = KthAnggota::posisiList();
?>
<div class="page-toolbar fade-up">
    <p class="page-toolbar-meta">
        Pengurus &amp; anggota · <strong><?= format_id($total) ?></strong> orang · halaman <?= $cur ?>/<?= max(1, $pages) ?>
    </p>
    <?php if ($canMut): ?>
    <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan/create', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-add">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah anggota
    </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/kelembagaan', ENT_QUOTES, 'UTF-8') ?>" class="filter-bar fade-up delay-1">
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
        <label for="f-kth">Kelompok (KTH)</label>
        <select id="f-kth" name="kth_id">
            <option value="">Semua KTH</option>
            <?php foreach ($kthOptions as $kt): ?>
            <option value="<?= (int) $kt['id'] ?>" <?= $filterKth === (int) $kt['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kt['kabupaten_nama'] . ' — ' . $kt['nama'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-md">
        <label for="f-posisi">Posisi</label>
        <select id="f-posisi" name="posisi">
            <option value="">Semua</option>
            <?php foreach ($posisiOpts as $po): ?>
            <option value="<?= htmlspecialchars($po, ENT_QUOTES, 'UTF-8') ?>" <?= $filterPosisi === $po ? 'selected' : '' ?>><?= htmlspecialchars($po, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field filter-field-q">
        <label for="f-q">Filter dalam tabel</label>
        <input id="f-q" type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama / NIK / KTH…">
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn-apply">Terapkan</button>
        <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan', ENT_QUOTES, 'UTF-8') ?>" class="btn-reset">Reset</a>
    </div>
</form>

<div class="stat-card overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Nama</th>
                    <th class="text-left">Posisi</th>
                    <th class="text-left">KTH</th>
                    <th class="text-left">Kabupaten</th>
                    <th class="text-left">NIK</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                <tr>
                    <td colspan="6" class="cell-empty">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="ti ti-users-group"></i></div>
                            <p class="empty-state-title">Belum ada anggota yang cocok</p>
                            <p class="empty-state-desc">Ubah filter atau reset pencarian untuk menampilkan pengurus &amp; anggota KTH.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="font-medium text-gray-800" title="<?= htmlspecialchars((string) $r['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $r['posisi'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="cell-stack" title="<?= htmlspecialchars((string) $r['kth_nama'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars((string) $r['kth_nama'], ENT_QUOTES, 'UTF-8') ?>
                        <span class="sub"><?= htmlspecialchars((string) $r['kth_kode'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td><?= htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="mono-clip"><?= $r['nik'] !== null && $r['nik'] !== '' ? htmlspecialchars((string) $r['nik'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td class="col-aksi">
                        <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-icon" title="Detail"><i class="ti ti-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <?php
    $qs = static function (int $p) use ($filterKab, $filterKth, $filterPosisi, $filterQ): string {
        $g = array_filter([
            'page' => $p,
            'kabupaten_id' => $filterKab > 0 ? $filterKab : null,
            'kth_id' => $filterKth > 0 ? $filterKth : null,
            'posisi' => $filterPosisi !== '' ? $filterPosisi : null,
            'q' => $filterQ !== '' ? $filterQ : null,
        ], static fn ($v) => $v !== null && $v !== '');
        return http_build_query($g);
    };
    ?>
    <div class="pager">
        <span class="pager-meta">Halaman <?= $cur ?> dari <?= $pages ?></span>
        <div class="pager-links">
            <?php if ($cur > 1): ?><a href="<?= htmlspecialchars(APP_URL . '/kelembagaan?' . $qs($cur - 1), ENT_QUOTES, 'UTF-8') ?>">« Prev</a><?php endif; ?>
            <?php if ($cur < $pages): ?><a href="<?= htmlspecialchars(APP_URL . '/kelembagaan?' . $qs($cur + 1), ENT_QUOTES, 'UTF-8') ?>">Next »</a><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
