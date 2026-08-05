<?php
/** @var array|null $rhl */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var list<int> $yearOptions */
/** @var list<string> $kegiatanOptions */
$isEdit = $rhl !== null;
$action = $isEdit ? APP_URL . '/rhl/' . (int) $rhl['id'] . '/update' : APP_URL . '/rhl/store';
$fv = static function (string $field) use ($rhl): string {
    if ($rhl === null) {
        return '';
    }
    return htmlspecialchars((string) ($rhl[$field] ?? ''), ENT_QUOTES, 'UTF-8');
};
$opKab = user_role() === 'operator' ? user_kabupaten_id() : null;
?>
<div class="max-w-3xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit RHL' : 'Tambah RHL' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/rhl/' . (int) $rhl['id'] : APP_URL . '/rhl', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Batal</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="stat-card p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten <span class="text-red-500">*</span></label>
                <select name="kabupaten_id" required <?= $opKab !== null ? 'disabled' : '' ?> class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none disabled:bg-gray-50 disabled:text-gray-500">
                    <option value="">— Pilih —</option>
                    <?php foreach ($kabupatenList as $kb): ?>
                    <option value="<?= (int) $kb['id'] ?>" <?= ($isEdit && (int) $rhl['kabupaten_id'] === (int) $kb['id']) || ($opKab !== null && (int) $kb['id'] === $opKab) ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($opKab !== null): ?><input type="hidden" name="kabupaten_id" value="<?= (int) $opKab ?>"><?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Nama KTH/Pelaksana <span class="text-red-500">*</span></label>
                <div style="position:relative;">
                    <input type="text" name="nama_kth" id="rhl_nama_kth" value="<?= $fv('nama_kth') ?>" required maxlength="200" autocomplete="off"
                        placeholder="Ketik untuk mencari atau isi nama bebas..."
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-forest-500/20 focus:border-forest-500">
                    <div id="rhl_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pilih kabupaten untuk lihat saran KTH/KPS, atau ketik nama bebas (misal: Pemdes, BPDAS)</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Kegiatan <span class="text-red-500">*</span></label>
                <select name="kegiatan" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <?php foreach ($kegiatanOptions as $k): ?>
                    <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $isEdit && (string) $rhl['kegiatan'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Tahun <span class="text-red-500">*</span></label>
                <select name="tahun" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <?php foreach ($yearOptions as $th): ?>
                    <option value="<?= $th ?>" <?= $isEdit && (int) $rhl['tahun'] === $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Luas (Ha)</label>
                <input type="text" name="luas_ha" value="<?= $fv('luas_ha') ?>" inputmode="decimal" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat LS</label>
                <input type="text" name="koordinat_ls" value="<?= $fv('koordinat_ls') ?>" inputmode="decimal" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat BT</label>
                <input type="text" name="koordinat_bt" value="<?= $fv('koordinat_bt') ?>" inputmode="decimal" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Sumber Dana <span class="text-red-500">*</span></label>
                <select name="sumber_dana" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <option value="">— Pilih —</option>
                    <?php foreach ($sumberDanaOptions as $sd): ?>
                    <option value="<?= htmlspecialchars($sd, ENT_QUOTES, 'UTF-8') ?>" <?= ($isEdit && (string) ($rhl['sumber_dana'] ?? '') === $sd) ? 'selected' : '' ?>><?= htmlspecialchars($sd, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Upload File .SHP <span class="text-gray-400">(Opsional)</span></label>
                <input type="file" name="shp_file" accept=".shp" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-forest-50 file:text-forest-700 hover:file:bg-forest-100">
                <?php if ($isEdit && !empty($rhl['shp_file'])): ?>
                    <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $rhl['shp_file'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(basename((string) $rhl['shp_file']), ENT_QUOTES, 'UTF-8') ?></a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700"><?= $isEdit ? 'Simpan perubahan' : 'Simpan RHL' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    const baseUrl = <?= json_encode(APP_URL) ?>;
    const input = document.getElementById('rhl_nama_kth');
    const dropdown = document.getElementById('rhl_dropdown');
    let pelaksanaList = [];
    let debounceTimer = null;

    function getKabId() {
        const all = document.getElementsByName('kabupaten_id');
        for (const el of all) {
            if (el.value && el.value !== '0') return el.value;
        }
        return null;
    }

    async function loadPelaksana(kabId) {
        pelaksanaList = [];
        if (!kabId) return;
        try {
            const res = await fetch(baseUrl + '/api/pelaksana?kabupaten_id=' + kabId);
            pelaksanaList = await res.json();
        } catch(e) { console.error('Load pelaksana failed', e); }
    }

    function showSuggestions(q) {
        const query = q.trim().toLowerCase();
        if (pelaksanaList.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        const filtered = query.length === 0
            ? pelaksanaList.slice(0, 15)
            : pelaksanaList.filter(n => n.toLowerCase().includes(query)).slice(0, 15);
        if (filtered.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        dropdown.innerHTML = filtered.map(n =>
            '<div class="px-4 py-2 text-sm cursor-pointer hover:bg-forest-50 transition" data-value="' + n.replace(/"/g, '&quot;') + '">' + n + '</div>'
        ).join('');
        dropdown.classList.remove('hidden');
    }

    document.getElementsByName('kabupaten_id').forEach(el => {
        if (el.tagName === 'SELECT') {
            el.addEventListener('change', function(){ loadPelaksana(this.value); });
        }
    });

    loadPelaksana(getKabId());

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
})();
</script>
