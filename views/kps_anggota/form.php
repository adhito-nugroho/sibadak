<?php
/** @var array<string, mixed> $kps */
/** @var array<string, mixed>|null $anggota */
/** @var list<array{id:int,nama:string}> $kecamatanOptions */
/** @var list<array{id:int,nama:string}> $desaOptions */
$isEdit = $anggota !== null;
$action = $isEdit
    ? APP_URL . '/kps/' . (int) $kps['id'] . '/anggota/' . (int) $anggota['id'] . '/update'
    : APP_URL . '/kps/' . (int) $kps['id'] . '/anggota/store';
$fv = static function (string $field) use ($anggota): string {
    if ($anggota === null) {
        return '';
    }
    $v = $anggota[$field] ?? '';
    return is_scalar($v) || $v === null ? (string) ($v ?? '') : '';
};
$kategori = $isEdit ? $fv('kategori') : (isset($_GET['kategori']) ? (string) $_GET['kategori'] : 'andil_garapan');
if (!in_array($kategori, KpsAnggota::kategoriList(), true)) {
    $kategori = 'andil_garapan';
}
?>

<div class="max-w-4xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Anggota KPS</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1"><?= $isEdit ? 'Edit Anggota' : 'Tambah Anggota' ?></h2>
            <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars((string) $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'] . '/anggota', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Kembali</a>
        </div>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-6 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select name="kategori" required class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                    <option value="ruang_perlindungan_komunal" <?= $kategori === 'ruang_perlindungan_komunal' ? 'selected' : '' ?>>Ruang Perlindungan &amp; Komunal</option>
                    <option value="andil_garapan" <?= $kategori === 'andil_garapan' ? 'selected' : '' ?>>Andil Garapan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">No Andil</label>
                <input type="text" name="no_andil" value="<?= htmlspecialchars($isEdit ? $fv('no_andil') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="Contoh: 33.PG.1 / 33.PG.1.K">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Nama Penggarap <span class="text-red-500">*</span></label>
                <input type="text" name="nama_penggarap" required maxlength="150" value="<?= htmlspecialchars($isEdit ? $fv('nama_penggarap') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="Nama sesuai dokumen">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Komoditas</label>
                <input type="text" name="komoditas" value="<?= htmlspecialchars($isEdit ? $fv('komoditas') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="Sengon, jagung, jati, ...">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">No KK</label>
                <input type="text" name="no_kk" value="<?= htmlspecialchars($isEdit ? $fv('no_kk') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">NIK</label>
                <input type="text" name="nik" value="<?= htmlspecialchars($isEdit ? $fv('nik') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Luas (Ha)</label>
                <input type="number" step="0.0001" name="luas_ha" value="<?= htmlspecialchars($isEdit ? $fv('luas_ha') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="0,25">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat BT (Bujur)</label>
                <input type="text" name="koordinat_bt" value="<?= htmlspecialchars($isEdit ? $fv('koordinat_bt') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="contoh: 110° 57' 24,27&quot;">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat LS (Lintang)</label>
                <input type="text" name="koordinat_ls" value="<?= htmlspecialchars($isEdit ? $fv('koordinat_ls') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" placeholder="contoh: 7° 18' 37,45&quot;">
            </div>
        </div>

        <div>
            <label class="block text-xs font-600 text-gray-500 mb-2">Batas Andil Garapan (opsional)</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-600 text-gray-400 mb-1">Barat</label>
                    <input type="text" name="batas_barat" value="<?= htmlspecialchars($isEdit ? $fv('batas_barat') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-600 text-gray-400 mb-1">Utara</label>
                    <input type="text" name="batas_utara" value="<?= htmlspecialchars($isEdit ? $fv('batas_utara') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-600 text-gray-400 mb-1">Selatan</label>
                    <input type="text" name="batas_selatan" value="<?= htmlspecialchars($isEdit ? $fv('batas_selatan') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-600 text-gray-400 mb-1">Timur</label>
                    <input type="text" name="batas_timur" value="<?= htmlspecialchars($isEdit ? $fv('batas_timur') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Kecamatan</label>
                <select name="kecamatan_id" id="kecamatan_id" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" data-initial="<?= htmlspecialchars($isEdit ? $fv('kecamatan_id') : (string) $kps['kecamatan_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <option value="">—</option>
                    <?php foreach ($kecamatanOptions as $kc): ?>
                    <option value="<?= (int) $kc['id'] ?>" <?= ($isEdit ? (int) $fv('kecamatan_id') : (int) $kps['kecamatan_id']) === (int) $kc['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $kc['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Desa</label>
                <select name="desa_id" id="desa_id" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none" data-initial="<?= htmlspecialchars($isEdit ? $fv('desa_id') : (string) $kps['desa_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <option value="">—</option>
                    <?php foreach ($desaOptions as $ds): ?>
                    <option value="<?= (int) $ds['id'] ?>" <?= ($isEdit ? (int) $fv('desa_id') : (int) $kps['desa_id']) === (int) $ds['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $ds['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Pengukur</label>
                <input type="text" name="pengukur" value="<?= htmlspecialchars($isEdit ? $fv('pengukur') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($isEdit ? $fv('tanggal') : '', ENT_QUOTES, 'UTF-8') ?>" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'] . '/anggota', ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700"><?= $isEdit ? 'Simpan perubahan' : 'Simpan' ?></button>
        </div>
    </form>

    <script>
    (function () {
        const kec = document.getElementById('kecamatan_id');
        const desa = document.getElementById('desa_id');
        if (!kec || !desa) return;

        async function loadDesa(kecamatanId, initial) {
            desa.innerHTML = '<option value="">—</option>';
            if (!kecamatanId) return;
            const url = <?= json_encode(APP_URL . '/api/desa?kecamatan_id=') ?> + encodeURIComponent(kecamatanId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            (data || []).forEach(function (d) {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.nama;
                if (initial && String(initial) === String(d.id)) opt.selected = true;
                desa.appendChild(opt);
            });
        }

        const initialKec = kec.getAttribute('data-initial');
        const initialDesa = desa.getAttribute('data-initial');
        if (initialKec) {
            loadDesa(initialKec, initialDesa);
        }

        kec.addEventListener('change', function () {
            loadDesa(this.value, null);
        });
    })();
    </script>
</div>

