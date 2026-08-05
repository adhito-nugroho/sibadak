<?php
/** @var array|null $kbr */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var list<int> $yearOptions */
/** @var list<string> $pelaksanaOptions */
$isEdit = $kbr !== null;
$action = $isEdit ? APP_URL . '/kbr/' . (int) $kbr['id'] . '/update' : APP_URL . '/kbr/store';
$fv = static function (string $field) use ($kbr): string {
    if ($kbr === null) {
        return '';
    }
    return htmlspecialchars((string) ($kbr[$field] ?? ''), ENT_QUOTES, 'UTF-8');
};
$opKab = user_role() === 'operator' ? user_kabupaten_id() : null;
?>
<div class="max-w-3xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit KBR' : 'Tambah KBR' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/kbr/' . (int) $kbr['id'] : APP_URL . '/kbr', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Batal</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="stat-card p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Nama KTH <span class="text-red-500">*</span></label>
                <div style="position:relative;">
                    <input type="text" name="nama_kth" id="kbr_nama_kth" value="<?= $fv('nama_kth') ?>" required maxlength="200" autocomplete="off"
                        placeholder="Ketik untuk mencari atau isi nama bebas..."
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-forest-500/20 focus:border-forest-500">
                    <div id="kbr_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pilih kabupaten untuk lihat saran KTH</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Lokasi</label>
                <textarea name="lokasi" rows="2" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none resize-y"><?= $fv('lokasi') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten</label>
                <select name="kabupaten_id_display" id="kbr_kabupaten" <?= $opKab !== null ? 'disabled' : '' ?> class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none disabled:bg-gray-50 disabled:text-gray-500">
                    <option value="">— Pilih —</option>
                    <?php foreach ($kabupatenList as $kb): ?>
                    <option value="<?= (int) $kb['id'] ?>" <?= ($isEdit && ($kbr['kabupaten_nama'] ?? '') === $kb['nama']) || ($opKab !== null && (int) $kb['id'] === $opKab) ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Kecamatan</label>
                <select id="kbr_kecamatan" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <option value="">— Pilih kabupaten dulu —</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Desa</label>
                <select name="desa_id" id="kbr_desa" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <option value="">— Pilih kecamatan dulu —</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">SubDAS</label>
                <input type="text" name="subdas" value="<?= $fv('subdas') ?>" maxlength="100" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Tahun Tanam</label>
                <select name="tahun_tanam" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <option value="">— Pilih —</option>
                    <?php foreach ($yearOptions as $th): ?>
                    <option value="<?= $th ?>" <?= $isEdit && (int) ($kbr['tahun_tanam'] ?? 0) === $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div></div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat LS</label>
                <input type="text" name="koordinat_ls" value="<?= $fv('koordinat_ls') ?>" inputmode="decimal" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat BT</label>
                <input type="text" name="koordinat_bt" value="<?= $fv('koordinat_bt') ?>" inputmode="decimal" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700"><?= $isEdit ? 'Simpan perubahan' : 'Simpan KBR' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    const baseUrl = <?= json_encode(APP_URL) ?>;
    const kabSel = document.getElementById('kbr_kabupaten');
    const kecSel = document.getElementById('kbr_kecamatan');
    const desSel = document.getElementById('kbr_desa');
    const input = document.getElementById('kbr_nama_kth');
    const dropdown = document.getElementById('kbr_dropdown');
    const existingDesaId = <?= $isEdit ? (int) ($kbr['desa_id'] ?? 0) : 0 ?>;
    let pelaksanaList = [];
    let debounceTimer = null;

    async function loadPelaksana(kabId) {
        pelaksanaList = [];
        if (!kabId) return;
        try {
            const res = await fetch(baseUrl + '/api/pelaksana?kabupaten_id=' + kabId);
            pelaksanaList = await res.json();
        } catch(e) {}
    }

    function showSuggestions(q) {
        const query = q.trim().toLowerCase();
        if (query.length < 1 || pelaksanaList.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        const filtered = pelaksanaList.filter(n => n.toLowerCase().includes(query)).slice(0, 15);
        if (filtered.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        dropdown.innerHTML = filtered.map(n =>
            '<div class="px-4 py-2 text-sm cursor-pointer hover:bg-forest-50 transition" data-value="' + n.replace(/"/g, '&quot;') + '">' + n + '</div>'
        ).join('');
        dropdown.classList.remove('hidden');
    }

    input.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        const v = this.value;
        debounceTimer = setTimeout(() => showSuggestions(v), 100);
    });
    input.addEventListener('focus', function(){ showSuggestions(this.value); });
    dropdown.addEventListener('click', function(e){
        const item = e.target.closest('[data-value]');
        if (!item) return;
        input.value = item.dataset.value;
        dropdown.classList.add('hidden');
    });
    document.addEventListener('click', function(e){
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    async function loadKecamatan(kabId, preselect) {
        kecSel.innerHTML = '<option value="">Memuat...</option>';
        desSel.innerHTML = '<option value="">— Pilih kecamatan dulu —</option>';
        if (!kabId) { kecSel.innerHTML = '<option value="">— Pilih kabupaten dulu —</option>'; return; }
        const res = await fetch(baseUrl + '/api/kecamatan?kabupaten_id=' + kabId);
        const data = await res.json();
        kecSel.innerHTML = '<option value="">— Pilih —</option>';
        data.forEach(function(r){
            const o = document.createElement('option');
            o.value = r.id; o.textContent = r.nama;
            if (preselect && parseInt(r.id) === preselect) o.selected = true;
            kecSel.appendChild(o);
        });
        if (preselect) kecSel.dispatchEvent(new Event('change'));
    }

    async function loadDesa(kecId, preselect) {
        desSel.innerHTML = '<option value="">Memuat...</option>';
        if (!kecId) { desSel.innerHTML = '<option value="">— Pilih kecamatan dulu —</option>'; return; }
        const res = await fetch(baseUrl + '/api/desa?kecamatan_id=' + kecId);
        const data = await res.json();
        desSel.innerHTML = '<option value="">— Pilih —</option>';
        data.forEach(function(r){
            const o = document.createElement('option');
            o.value = r.id; o.textContent = r.nama;
            if (preselect && parseInt(r.id) === preselect) o.selected = true;
            desSel.appendChild(o);
        });
    }

    kabSel.addEventListener('change', function(){ loadKecamatan(this.value, null); loadPelaksana(this.value); });
    kecSel.addEventListener('change', function(){ loadDesa(this.value, null); });

    // Load pelaksana on init if kabupaten is selected
    if (kabSel.value) loadPelaksana(kabSel.value);

    <?php if ($isEdit && !empty($kbr['desa_id'])): ?>
    // Pre-load chain for edit mode
    (async function(){
        const desaId = <?= (int) $kbr['desa_id'] ?>;
        // Get kecamatan_id from desa
        const resD = await fetch(baseUrl + '/api/desa?kecamatan_id=0');
        // We need to find kecamatan from kabupaten first
        if (kabSel.value) {
            const resK = await fetch(baseUrl + '/api/kecamatan?kabupaten_id=' + kabSel.value);
            const kecs = await resK.json();
            kecSel.innerHTML = '<option value="">— Pilih —</option>';
            for (const k of kecs) {
                const o = document.createElement('option');
                o.value = k.id; o.textContent = k.nama;
                kecSel.appendChild(o);
                // Try loading desa for each kecamatan to find the right one
            }
            // Load all desa for each kecamatan to find match
            for (const k of kecs) {
                const resDs = await fetch(baseUrl + '/api/desa?kecamatan_id=' + k.id);
                const desas = await resDs.json();
                const match = desas.find(d => parseInt(d.id) === desaId);
                if (match) {
                    kecSel.value = k.id;
                    desSel.innerHTML = '<option value="">— Pilih —</option>';
                    desas.forEach(function(r){
                        const o = document.createElement('option');
                        o.value = r.id; o.textContent = r.nama;
                        if (parseInt(r.id) === desaId) o.selected = true;
                        desSel.appendChild(o);
                    });
                    break;
                }
            }
        }
    })();
    <?php endif; ?>
})();
</script>
