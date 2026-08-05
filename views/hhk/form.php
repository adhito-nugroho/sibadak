<?php
/** @var array|null $hhk */
$isEdit = $hhk !== null;
$action = $isEdit ? APP_URL . '/hhk/' . (int) $hhk['id'] . '/update' : APP_URL . '/hhk/store';
$fv = static fn(string $f) => htmlspecialchars((string) ($hhk[$f] ?? ''), ENT_QUOTES, 'UTF-8');
$opKab = user_role() === 'operator' ? user_kabupaten_id() : null;
?>
<div class="max-w-2xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-sm font-semibold text-gray-800"><?= $isEdit ? 'Edit HHK' : 'Tambah HHK' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/hhk/' . (int) $hhk['id'] : APP_URL . '/hhk', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">← Kembali</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <!-- 1. Wilayah -->
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 pb-1 border-b border-gray-100">1. Wilayah</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Kabupaten <span class="text-red-500">*</span></label>
                    <select name="kabupaten_id" id="hhk_kab" required <?= $opKab !== null ? 'disabled' : '' ?> class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none disabled:bg-gray-50">
                        <option value="">— Pilih —</option>
                        <?php foreach ($kabupatenList as $kb): ?><option value="<?= (int) $kb['id'] ?>" <?= ($isEdit && (int) ($hhk['kabupaten_id'] ?? 0) === (int) $kb['id']) || ($opKab !== null && (int) $kb['id'] === $opKab) ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                    </select>
                    <?php if ($opKab !== null): ?><input type="hidden" name="kabupaten_id" value="<?= (int) $opKab ?>"><?php endif; ?>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Kecamatan</label>
                    <select name="kecamatan_id" id="hhk_kec" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                        <option value="">— Pilih kabupaten dulu —</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Desa</label>
                    <select name="desa_id" id="hhk_desa" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                        <option value="">— Pilih kecamatan dulu —</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. KTH & Penyuluh -->
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 pb-1 border-b border-gray-100">2. KTH & Penyuluh</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Nama KTH <span class="text-gray-400">(opsional / perorangan)</span></label>
                    <div style="position:relative;">
                        <input type="text" name="nama_kth" id="hhk_nama_kth" value="<?= $fv('nama_kth') ?>" autocomplete="off"
                            placeholder="Ketik nama KTH atau perorangan..."
                            class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:border-forest-500">
                        <div id="hhk_kth_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-52 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Nama Penyuluh <span class="text-red-500">*</span></label>
                    <select name="penyuluh_id" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none bg-white">
                        <option value="">— Pilih Penyuluh —</option>
                        <?php foreach ($penyuluhList as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= ($isEdit && (int) ($hhk['penyuluh_id'] ?? 0) === (int) $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($p['nip'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. Periode & Keterangan -->
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3 pb-1 border-b border-gray-100">3. Periode</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Bulan <span class="text-red-500">*</span></label>
                    <select name="bulan" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                        <?php foreach ($bulanOptions as $b => $n): ?><option value="<?= $b ?>" <?= $isEdit && (int) $hhk['bulan'] === $b ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tahun <span class="text-red-500">*</span></label>
                    <select name="tahun" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                        <?php foreach (array_reverse($yearOptions) as $y): ?><option value="<?= $y ?>" <?= $isEdit && (int) $hhk['tahun'] === $y ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none resize-y"><?= $fv('keterangan') ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium hover:bg-forest-700"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan & Lanjut Isi Rincian' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    const baseUrl = <?= json_encode(APP_URL) ?>;
    const kabSel = document.getElementById('hhk_kab');
    const kecSel = document.getElementById('hhk_kec');
    const desSel = document.getElementById('hhk_desa');
    const kthInput = document.getElementById('hhk_nama_kth');
    const kthDropdown = document.getElementById('hhk_kth_dropdown');
    let pelaksanaList = [];
    let debounceTimer = null;

    async function loadKec(kabId) {
        kecSel.innerHTML = '<option value="">Memuat...</option>'; desSel.innerHTML = '<option value="">—</option>';
        if (!kabId) { kecSel.innerHTML = '<option value="">— Pilih kabupaten dulu —</option>'; return; }
        const res = await fetch(baseUrl + '/api/kecamatan?kabupaten_id=' + kabId);
        const data = await res.json();
        kecSel.innerHTML = '<option value="">— Pilih —</option>';
        data.forEach(r => { const o = new Option(r.nama, r.id); <?= $isEdit ? 'if(parseInt(r.id)===' . (int)($hhk['kecamatan_id'] ?? 0) . ')o.selected=true;' : '' ?> kecSel.appendChild(o); });
        <?php if ($isEdit && !empty($hhk['kecamatan_id'])): ?>kecSel.dispatchEvent(new Event('change'));<?php endif; ?>
    }
    async function loadDesa(kecId) {
        desSel.innerHTML = '<option value="">Memuat...</option>';
        if (!kecId) { desSel.innerHTML = '<option value="">— Pilih kecamatan dulu —</option>'; return; }
        const res = await fetch(baseUrl + '/api/desa?kecamatan_id=' + kecId);
        const data = await res.json();
        desSel.innerHTML = '<option value="">— Pilih —</option>';
        data.forEach(r => { const o = new Option(r.nama, r.id); <?= $isEdit ? 'if(parseInt(r.id)===' . (int)($hhk['desa_id'] ?? 0) . ')o.selected=true;' : '' ?> desSel.appendChild(o); });
    }
    async function loadPelaksana(kabId) {
        pelaksanaList = [];
        if (!kabId) return;
        try {
            const res = await fetch(baseUrl + '/api/pelaksana?kabupaten_id=' + kabId);
            pelaksanaList = await res.json();
        } catch (e) {
            console.error('Load pelaksana failed', e);
        }
    }

    function showSuggestions(q) {
        const query = (q || '').trim().toLowerCase();
        if (pelaksanaList.length === 0) {
            kthDropdown.classList.add('hidden');
            return;
        }
        const filtered = query.length === 0
            ? pelaksanaList.slice(0, 15)
            : pelaksanaList.filter(n => n.toLowerCase().includes(query)).slice(0, 15);
        if (filtered.length === 0) {
            kthDropdown.classList.add('hidden');
            return;
        }
        kthDropdown.innerHTML = filtered.map(n =>
            '<div class="px-3 py-2 text-xs cursor-pointer hover:bg-forest-50 transition" data-value="' + n.replace(/"/g, '&quot;') + '">' + n + '</div>'
        ).join('');
        kthDropdown.classList.remove('hidden');
    }

    if (kabSel) {
        kabSel.addEventListener('change', function(){ loadKec(this.value); loadPelaksana(this.value); });
        const initKab = kabSel.value || <?= json_encode($opKab ?? '') ?>;
        if (initKab) { loadKec(initKab); loadPelaksana(initKab); }
    }
    kecSel.addEventListener('change', function(){ loadDesa(this.value); });

    kthInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const v = this.value;
        debounceTimer = setTimeout(() => showSuggestions(v), 100);
    });
    kthInput.addEventListener('focus', function() {
        showSuggestions(this.value);
    });
    kthDropdown.addEventListener('click', function(e) {
        const item = e.target.closest('[data-value]');
        if (!item) return;
        kthInput.value = item.dataset.value;
        kthDropdown.classList.add('hidden');
    });
    document.addEventListener('click', function(e) {
        if (!kthInput.contains(e.target) && !kthDropdown.contains(e.target)) {
            kthDropdown.classList.add('hidden');
        }
    });
})();
</script>
