<?php
/** @var array|null $kps */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var list<array{id:int,nama:string}> $kecamatanOptions */
/** @var list<array{id:int,nama:string}> $desaOptions */
/** @var string $kthDisplay */
$isEdit = $kps !== null;
$action = $isEdit
    ? APP_URL . '/kps/' . (int) $kps['id'] . '/update'
    : APP_URL . '/kps/store';
$fv = static function (string $field) use ($kps): string {
    if ($kps === null) {
        return '';
    }
    $v = $kps[$field] ?? '';

    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$skemaOpts = Kps::skemaList();
$opKab = user_role() === 'operator' ? user_kabupaten_id() : null;
?>
<div class="max-w-4xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit KPS' : 'Tambah KPS' ?></h2>
            <p class="text-xs text-gray-400 mt-0.5">Kelompok Perhutanan Sosial · bidang <span class="text-red-500">*</span> wajib</p>
        </div>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/kps/' . (int) $kps['id'] : APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Batal</a>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="kth_id" id="kth_id" value="<?= $isEdit ? (int) ($kps['kth_id'] ?? 0) : '' ?>">

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Wilayah &amp; skema</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten <span class="text-red-500">*</span></label>
                    <select name="kabupaten_id" id="kabupaten_id" required <?= $opKab !== null ? 'disabled' : '' ?>
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none disabled:bg-gray-50 disabled:text-gray-500">
                        <option value="">— Pilih —</option>
                        <?php foreach ($kabupatenList as $kb): ?>
                        <option value="<?= (int) $kb['id'] ?>" <?= ($kps && (int) $kps['kabupaten_id'] === (int) $kb['id']) || ($opKab !== null && (int) $kb['id'] === $opKab) ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($opKab !== null): ?>
                    <input type="hidden" name="kabupaten_id" value="<?= (int) $opKab ?>">
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                    <select name="kecamatan_id" id="kecamatan_id" required data-initial="<?= $isEdit ? (int) $kps['kecamatan_id'] : '' ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php if ($kecamatanOptions === [] && !$kps): ?>
                        <option value="">— Pilih kabupaten dahulu —</option>
                        <?php else: ?>
                        <?php foreach ($kecamatanOptions as $kc): ?>
                        <option value="<?= (int) $kc['id'] ?>" <?= ($kps && (int) $kps['kecamatan_id'] === (int) $kc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kc['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Desa <span class="text-red-500">*</span></label>
                    <select name="desa_id" id="desa_id" required data-initial="<?= $isEdit ? (int) $kps['desa_id'] : '' ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php if ($desaOptions === [] && !$kps): ?>
                        <option value="">— Pilih kecamatan dahulu —</option>
                        <?php else: ?>
                        <?php foreach ($desaOptions as $d): ?>
                        <option value="<?= (int) $d['id'] ?>" <?= ($kps && (int) $kps['desa_id'] === (int) $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Skema PS <span class="text-red-500">*</span></label>
                    <select name="skema" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php foreach ($skemaOpts as $sk): ?>
                        <option value="<?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?>" <?= ($kps && (string) $kps['skema'] === $sk) ? 'selected' : '' ?>><?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">KTH terkait (opsional)</h3>
            <div class="space-y-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Cari KTH</label>
                <div class="relative">
                    <input type="text" id="kth_search" autocomplete="off"
                        value="<?= htmlspecialchars($kthDisplay ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none"
                        placeholder="Ketik nama KTH / kode register (min 2 huruf)">
                    <div id="kth_results" class="hidden absolute z-20 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-lg max-h-64 overflow-auto"></div>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400">Dipakai untuk mengaitkan KPS ke master KTH (jika ada).</p>
                    <button type="button" id="kth_clear" class="text-xs font-600 text-gray-600 hover:underline">Hapus pilihan</button>
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Identitas &amp; luasan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Nama lembaga <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lembaga" required value="<?= $fv('nama_lembaga') ?>" maxlength="200"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Nomor SK <span class="text-red-500">*</span></label>
                    <input type="text" name="no_sk" required value="<?= $fv('no_sk') ?>" maxlength="200"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 font-mono focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Luas wilayah (Ha)</label>
                    <input type="text" name="luas_wilayah_ha" value="<?= $fv('luas_wilayah_ha') ?>" inputmode="decimal" placeholder="0"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jumlah KK</label>
                    <input type="number" name="jumlah_kk" min="0" max="65535" value="<?= $isEdit ? (int) ($kps['jumlah_kk'] ?? 0) : '0' ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jumlah KUPS</label>
                    <input type="number" name="jumlah_kups" min="0" max="255" value="<?= $isEdit ? (int) ($kps['jumlah_kups'] ?? 0) : '0' ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Nama pendamping</label>
                    <input type="text" name="nama_pendamping" value="<?= $fv('nama_pendamping') ?>" maxlength="150"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Dokumen &amp; penandaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Upload Bukti SK (PDF/JPG/PNG)</label>
                    <input type="file" name="bukti_sk_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none bg-white">
                    <?php if ($isEdit && !empty($kps['bukti_sk_link'])): ?>
                        <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $kps['bukti_sk_link'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $kps['bukti_sk_link'], ENT_QUOTES, 'UTF-8') ?></a></p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Upload RKPS (PDF/JPG/PNG)</label>
                    <input type="file" name="rkps_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none bg-white">
                    <?php if ($isEdit && !empty($kps['rkps_link'])): ?>
                        <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $kps['rkps_link'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $kps['rkps_link'], ENT_QUOTES, 'UTF-8') ?></a></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Penandaan batas areal</label>
                    <input type="text" name="penandaan_batas_areal" value="<?= $fv('penandaan_batas_areal') ?>" maxlength="50"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none" placeholder="Sudah / Proses / Belum">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Penandaan batas andil</label>
                    <input type="text" name="penandaan_batas_andil" value="<?= $fv('penandaan_batas_andil') ?>" maxlength="50"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none" placeholder="Sudah / Proses / Belum">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/kps/' . (int) $kps['id'] : APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700 shadow-sm"><?= $isEdit ? 'Simpan perubahan' : 'Simpan' ?></button>
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

    const selKecId = kec.dataset.initial || '';
    const selDesId = des.dataset.initial || '';

    if (kab.disabled) {
        const h = document.querySelector('input[name="kabupaten_id"][type="hidden"]');
        if (h && h.value) {
            loadKecamatan(h.value, selKecId || null).then(function () {
                if (selKecId) {
                    return loadDesa(selKecId, selDesId || null);
                }
            });
        }
        return;
    }

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

<script>
(function () {
    const APP_URL = window.APP_URL || '';
    const input = document.getElementById('kth_search');
    const hid = document.getElementById('kth_id');
    const box = document.getElementById('kth_results');
    const clearBtn = document.getElementById('kth_clear');
    const kab = document.getElementById('kabupaten_id');
    if (!input || !hid || !box || !clearBtn) return;

    function hide() {
        box.classList.add('hidden');
        box.innerHTML = '';
    }

    function pick(row) {
        hid.value = row.id;
        input.value = (row.kode_register ? row.kode_register + ' — ' : '') + row.nama;
        hide();
    }

    clearBtn.addEventListener('click', function () {
        hid.value = '';
        input.value = '';
        hide();
        input.focus();
    });

    let t = null;
    input.addEventListener('input', function () {
        const q = input.value.trim();
        if (q.length < 2) {
            hid.value = '';
            hide();
            return;
        }
        // Jika user mengetik ulang setelah memilih, hapus pilihan agar tidak salah.
        hid.value = '';

        if (t) window.clearTimeout(t);
        t = window.setTimeout(async function () {
            const kabId = kab && !kab.disabled ? (kab.value || '') : '';
            const url = APP_URL + '/api/kth-search?q=' + encodeURIComponent(q) + (kabId ? '&kabupaten_id=' + encodeURIComponent(kabId) : '');
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const rows = await res.json();
            if (!Array.isArray(rows) || rows.length === 0) {
                box.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Tidak ada hasil.</div>';
                box.classList.remove('hidden');
                return;
            }
            box.innerHTML = '';
            rows.forEach(function (r) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-3 py-2 hover:bg-forest-50 text-sm';
                btn.innerHTML =
                    '<div class="font-600 text-gray-800">' + escapeHtml((r.kode_register ? r.kode_register + ' — ' : '') + r.nama) + '</div>' +
                    '<div class="text-xs text-gray-500 mt-0.5">' + escapeHtml(r.kabupaten_nama || '') + '</div>';
                btn.addEventListener('click', function () { pick(r); });
                box.appendChild(btn);
            });
            box.classList.remove('hidden');
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (e.target === input || box.contains(e.target)) return;
        hide();
    });

    function escapeHtml(s) {
        return String(s)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
})();
</script>
