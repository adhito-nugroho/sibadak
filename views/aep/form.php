<?php
/** @var array|null $aep */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
/** @var list<int> $yearOptions */
/** @var list<string> $pelaksanaOptions */
$isEdit = $aep !== null;
$action = $isEdit ? APP_URL . '/aep/' . (int) $aep['id'] . '/update' : APP_URL . '/aep/store';
$fv = static function (string $field) use ($aep): string {
    if ($aep === null) {
        return '';
    }
    return htmlspecialchars((string) ($aep[$field] ?? ''), ENT_QUOTES, 'UTF-8');
};
$opKab = user_role() === 'operator' ? user_kabupaten_id() : null;
?>
<div class="max-w-3xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit AEP' : 'Tambah AEP' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/aep/' . (int) $aep['id'] : APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Batal</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="stat-card p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Kabupaten <span class="text-red-500">*</span></label>
                <select name="kabupaten_id" required <?= $opKab !== null ? 'disabled' : '' ?> class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none disabled:bg-gray-50 disabled:text-gray-500">
                    <option value="">— Pilih —</option>
                    <?php foreach ($kabupatenList as $kb): ?>
                    <option value="<?= (int) $kb['id'] ?>" <?= ($isEdit && (int) $aep['kabupaten_id'] === (int) $kb['id']) || ($opKab !== null && (int) $kb['id'] === $opKab) ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($opKab !== null): ?><input type="hidden" name="kabupaten_id" value="<?= (int) $opKab ?>"><?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Nama KTH <span class="text-red-500">*</span></label>
                <div style="position:relative;">
                    <input type="text" name="nama_kth" id="aep_nama_kth" value="<?= $fv('nama_kth') ?>" required maxlength="200" autocomplete="off"
                        placeholder="Ketik untuk mencari atau isi nama bebas..."
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-forest-500/20 focus:border-forest-500">
                    <div id="aep_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pilih kabupaten untuk lihat saran KTH</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Jenis Bantuan <span class="text-red-500">*</span></label>
                <input type="text" name="jenis_bantuan" value="<?= $fv('jenis_bantuan') ?>" required maxlength="150" placeholder="Contoh: Cultivator, Pencacah Rumput, Mesin Perajang..." class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Jumlah <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah" value="<?= $isEdit ? (int) $aep['jumlah'] : 1 ?>" required min="1" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Tahun <span class="text-red-500">*</span></label>
                <select name="tahun" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
                    <?php foreach ($yearOptions as $th): ?>
                    <option value="<?= $th ?>" <?= $isEdit && (int) $aep['tahun'] === $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none resize-y"><?= $fv('keterangan') ?></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700"><?= $isEdit ? 'Simpan perubahan' : 'Simpan AEP' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    const baseUrl = <?= json_encode(APP_URL) ?>;
    const input = document.getElementById('aep_nama_kth');
    const dropdown = document.getElementById('aep_dropdown');
    let pelaksanaList = [];
    let debounceTimer = null;

    function getKabId() {
        const all = document.getElementsByName('kabupaten_id');
        for (let i = 0; i < all.length; i++) {
            if (all[i].value && all[i].value !== '0') return all[i].value;
        }
        return null;
    }

    async function loadPelaksana(kabId) {
        pelaksanaList = [];
        if (!kabId) { console.log('[AEP] no kabupaten selected'); return; }
        const url = baseUrl + '/api/pelaksana?kabupaten_id=' + kabId;
        console.log('[AEP] loading:', url);
        try {
            const res = await fetch(url);
            console.log('[AEP] response:', res.status);
            if (!res.ok) return;
            pelaksanaList = await res.json();
            console.log('[AEP] loaded', pelaksanaList.length, 'pelaksana');
        } catch(e) { console.error('[AEP] fetch failed', e); }
    }

    function showSuggestions(q) {
        const query = (q || '').trim().toLowerCase();
        if (pelaksanaList.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        const filtered = query.length === 0
            ? pelaksanaList.slice(0, 15)
            : pelaksanaList.filter(function(n){ return n.toLowerCase().indexOf(query) !== -1; }).slice(0, 15);
        if (filtered.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        let html = '';
        for (let i = 0; i < filtered.length; i++) {
            const n = filtered[i];
            const safe = n.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
            html += '<div class="px-4 py-2 text-sm cursor-pointer hover:bg-forest-50 transition" data-value="' + safe + '">' + safe + '</div>';
        }
        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');
    }

    // Listen perubahan kabupaten
    const kabSelects = document.getElementsByName('kabupaten_id');
    for (let i = 0; i < kabSelects.length; i++) {
        if (kabSelects[i].tagName === 'SELECT') {
            kabSelects[i].addEventListener('change', function(){ loadPelaksana(this.value); });
        }
    }

    // Initial load
    loadPelaksana(getKabId());

    input.addEventListener('input', function(){
        clearTimeout(debounceTimer);
        const v = this.value;
        debounceTimer = setTimeout(function(){ showSuggestions(v); }, 100);
    });
    input.addEventListener('focus', function(){ showSuggestions(this.value); });
    dropdown.addEventListener('mousedown', function(e){
        const item = e.target.closest('[data-value]');
        if (!item) return;
        e.preventDefault();
        input.value = item.getAttribute('data-value');
        dropdown.classList.add('hidden');
    });
    document.addEventListener('click', function(e){
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
})();
</script>
