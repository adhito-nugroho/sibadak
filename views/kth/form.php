<?php
/** @var array|null $kth */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var list<array{id:int,nama:string}> $kecamatanOptions */
/** @var list<array{id:int,nama:string}> $desaOptions */
$isEdit = $kth !== null;
$action = $isEdit
    ? APP_URL . '/kth/' . (int) $kth['id'] . '/update'
    : APP_URL . '/kth/store';
$fv = static function (string $field) use ($kth): string {
    if ($kth === null) {
        return '';
    }

    $v = $kth[$field] ?? '';

    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
?>
<div class="max-w-4xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit KTH' : 'Tambah KTH' ?></h2>
            <p class="text-xs text-gray-400 mt-0.5">Lengkapi bidang bertanda <span class="text-red-500">*</span></p>
        </div>
        <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Wilayah</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten <span class="text-red-500">*</span></label>
                    <select name="kabupaten_id" id="kabupaten_id" required
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <option value="">— Pilih —</option>
                        <?php foreach ($kabupatenList as $kb): ?>
                        <option value="<?= (int) $kb['id'] ?>" <?= ($kth && (int) $kth['kabupaten_id'] === (int) $kb['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                    <select name="kecamatan_id" id="kecamatan_id" required
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php if ($kecamatanOptions === [] && !$kth): ?>
                        <option value="">— Pilih kabupaten dahulu —</option>
                        <?php else: ?>
                        <?php foreach ($kecamatanOptions as $kc): ?>
                        <option value="<?= (int) $kc['id'] ?>" <?= ($kth && (int) $kth['kecamatan_id'] === (int) $kc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kc['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Desa <span class="text-red-500">*</span></label>
                    <select name="desa_id" id="desa_id" required
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php if ($desaOptions === [] && !$kth): ?>
                        <option value="">— Pilih kecamatan dahulu —</option>
                        <?php else: ?>
                        <?php foreach ($desaOptions as $d): ?>
                        <option value="<?= (int) $d['id'] ?>" <?= ($kth && (int) $kth['desa_id'] === (int) $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Dusun / Blok</label>
                    <input type="text" name="dusun_blok" value="<?= $fv('dusun_blok') ?>" maxlength="100"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Data kelompok</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Nama KTH <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="<?= $fv('nama') ?>" required maxlength="150"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Kode register <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_register" value="<?= $fv('kode_register') ?>" required maxlength="60"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 font-mono focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <select name="kelas" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php
                        $selKelas = $kth ? (string) ($kth['kelas'] ?? 'Pemula') : 'Pemula';
                        foreach (['Pemula', 'Madya', 'Utama'] as $kl):
                            ?>
                        <option value="<?= $kl ?>" <?= $kl === $selKelas ? 'selected' : '' ?>><?= $kl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jumlah anggota</label>
                    <input type="number" name="jumlah_anggota" value="<?= $kth ? (int) ($kth['jumlah_anggota'] ?? 0) : '0' ?>" min="0" step="1"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jenis usaha</label>
                    <input type="text" name="jenis_usaha" value="<?= $fv('jenis_usaha') ?>" maxlength="150"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat LS</label>
                    <input type="text" name="koordinat_ls" inputmode="decimal" placeholder="-7.xxxxxxx" value="<?= $fv('koordinat_ls') ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat BT</label>
                    <input type="text" name="koordinat_bt" inputmode="decimal" placeholder="112.xxxxxxx" value="<?= $fv('koordinat_bt') ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Legalitas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-600 text-gray-500 mb-1">SK Kepala Desa</label>
                    <input type="text" name="sk_kepala_desa" value="<?= $fv('sk_kepala_desa') ?>" maxlength="200" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400"></div>
                <div><label class="block text-xs font-600 text-gray-500 mb-1">SK Kepala Dinas</label>
                    <input type="text" name="sk_kepala_dinas" value="<?= $fv('sk_kepala_dinas') ?>" maxlength="200" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400"></div>
                <div><label class="block text-xs font-600 text-gray-500 mb-1">Akta notaris</label>
                    <input type="text" name="akta_notaris" value="<?= $fv('akta_notaris') ?>" maxlength="200" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400"></div>
                <div><label class="block text-xs font-600 text-gray-500 mb-1">SK Kemenkumham</label>
                    <input type="text" name="sk_kemenkumham" value="<?= $fv('sk_kemenkumham') ?>" maxlength="200" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400"></div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Tautan dokumen</h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Upload SK KTH (PDF/JPG/PNG)</label>
                    <input type="file" name="link_sk_kth_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400 bg-white">
                    <?php if ($isEdit && !empty($kth['link_sk_kth'])): ?>
                        <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $kth['link_sk_kth'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $kth['link_sk_kth'], ENT_QUOTES, 'UTF-8') ?></a></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Upload SK Kades (PDF/JPG/PNG)</label>
                    <input type="file" name="link_sk_kades_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400 bg-white">
                    <?php if ($isEdit && !empty($kth['link_sk_kades'])): ?>
                        <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $kth['link_sk_kades'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $kth['link_sk_kades'], ENT_QUOTES, 'UTF-8') ?></a></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Upload Sertifikat (PDF/JPG/PNG)</label>
                    <input type="file" name="link_sertifikat_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-forest-400 bg-white">
                    <?php if ($isEdit && !empty($kth['link_sertifikat'])): ?>
                        <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $kth['link_sertifikat'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $kth['link_sertifikat'], ENT_QUOTES, 'UTF-8') ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-forest-600 hover:bg-forest-700 text-white text-sm font-600 shadow-sm"><?= $isEdit ? 'Simpan perubahan' : 'Simpan KTH' ?></button>
        </div>
    </form>
</div>

<script>
(function () {
    const APP_URL = window.APP_URL || '';
    const kab = document.getElementById('kabupaten_id');
    const kec = document.getElementById('kecamatan_id');
    const des = document.getElementById('desa_id');
    if (!kab || !kec || !des) return;

    async function loadKecamatan(kabId, selectedId) {
        kec.innerHTML = '<option value="">Memuat…</option>';
        des.innerHTML = '<option value="">— Pilih kecamatan —</option>';
        if (!kabId) {
            kec.innerHTML = '<option value="">— Pilih kabupaten —</option>';
            return;
        }
        const r = await fetch(APP_URL + '/api/kecamatan?kabupaten_id=' + encodeURIComponent(kabId));
        const rows = await r.json();
        kec.innerHTML = '<option value="">— Pilih —</option>';
        rows.forEach(function (row) {
            const o = document.createElement('option');
            o.value = row.id;
            o.textContent = row.nama;
            if (selectedId && String(selectedId) === String(row.id)) o.selected = true;
            kec.appendChild(o);
        });
    }

    async function loadDesa(kecId, selectedId) {
        des.innerHTML = '<option value="">Memuat…</option>';
        if (!kecId) {
            des.innerHTML = '<option value="">— Pilih kecamatan —</option>';
            return;
        }
        const r = await fetch(APP_URL + '/api/desa?kecamatan_id=' + encodeURIComponent(kecId));
        const rows = await r.json();
        des.innerHTML = '<option value="">— Pilih —</option>';
        rows.forEach(function (row) {
            const o = document.createElement('option');
            o.value = row.id;
            o.textContent = row.nama;
            if (selectedId && String(selectedId) === String(row.id)) o.selected = true;
            des.appendChild(o);
        });
    }

    kab.addEventListener('change', function () {
        loadKecamatan(kab.value, null);
    });
    kec.addEventListener('change', function () {
        loadDesa(kec.value, null);
    });
})();
</script>
