<?php
/** @var array|null $anggota */
/** @var list<array{id:int,kode_register:string,nama:string,kabupaten_nama:string}> $kthOptions */
/** @var int $selectedKthId */
$isEdit = $anggota !== null;
$action = $isEdit
    ? APP_URL . '/kelembagaan/' . (int) $anggota['id'] . '/update'
    : APP_URL . '/kelembagaan/store';
$fv = static function (string $field) use ($anggota): string {
    if ($anggota === null) {
        return '';
    }
    $v = $anggota[$field] ?? '';

    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$posisiOpts = KthAnggota::posisiList();
$backUrl = $isEdit
    ? APP_URL . '/kelembagaan/' . (int) $anggota['id']
    : APP_URL . '/kelembagaan';
?>
<div class="max-w-4xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit anggota' : 'Tambah anggota' ?></h2>
            <p class="text-xs text-gray-400 mt-0.5">Bidang <span class="text-red-500">*</span> wajib diisi</p>
        </div>
        <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Batal</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Kelompok &amp; peran</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">KTH <span class="text-red-500">*</span></label>
                    <input type="hidden" name="kth_id" id="kth_id_val" value="<?= $isEdit ? (int) $anggota['kth_id'] : ($selectedKthId > 0 ? $selectedKthId : '') ?>" required>
                    <input type="text" id="kth_search" placeholder="Ketik nama KTH untuk mencari..." autocomplete="off"
                        value="<?php
                            if ($isEdit || $selectedKthId > 0) {
                                $selId = $isEdit ? (int) $anggota['kth_id'] : $selectedKthId;
                                foreach ($kthOptions as $kt) {
                                    if ((int) $kt['id'] === $selId) {
                                        echo htmlspecialchars($kt['kabupaten_nama'] . ' — ' . $kt['nama'] . ' (' . $kt['kode_register'] . ')', ENT_QUOTES, 'UTF-8');
                                        break;
                                    }
                                }
                            }
                        ?>"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                    <div id="kth_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
                    <p class="text-xs text-gray-400 mt-1">Minimal 2 karakter untuk mencari</p>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Posisi <span class="text-red-500">*</span></label>
                    <select name="posisi" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <?php foreach ($posisiOpts as $po): ?>
                        <option value="<?= htmlspecialchars($po, ENT_QUOTES, 'UTF-8') ?>" <?= ($isEdit && (string) $anggota['posisi'] === $po) || (!$isEdit && $po === 'Anggota') ? 'selected' : '' ?>><?= htmlspecialchars($po, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Nama lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required value="<?= $fv('nama') ?>" maxlength="150"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Identitas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">NIK</label>
                    <input type="text" name="nik" value="<?= $fv('nik') ?>" maxlength="20" inputmode="numeric" placeholder="Unik jika diisi"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 font-mono focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">No. KK</label>
                    <input type="text" name="no_kk" value="<?= $fv('no_kk') ?>" maxlength="20"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Jenis kelamin</label>
                    <select name="gender" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                        <option value="">—</option>
                        <option value="L" <?= $isEdit && ($anggota['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $isEdit && ($anggota['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">No. telepon</label>
                    <input type="text" name="no_telpon" value="<?= $fv('no_telpon') ?>" maxlength="20"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Alamat</label>
                    <textarea name="alamat" rows="2" maxlength="65535" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none"><?= $isEdit ? $fv('alamat') : '' ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-600 text-gray-500 mb-1">Pekerjaan</label>
                    <input type="text" name="pekerjaan" value="<?= $fv('pekerjaan') ?>" maxlength="100"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
            </div>
        </div>

        <div class="stat-card p-6">
            <h3 class="font-display font-700 text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100">Usaha &amp; lokasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Luas garapan (ha)</label>
                    <input type="text" name="luas_garapan_ha" value="<?= $fv('luas_garapan_ha') ?>" inputmode="decimal" placeholder="0"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">HTM</label>
                    <input type="text" name="htm" value="<?= $fv('htm') ?>" maxlength="100"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Komoditi HHBK</label>
                    <input type="text" name="komoditi_hhbk" value="<?= $fv('komoditi_hhbk') ?>" maxlength="200"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Komoditi HHK</label>
                    <input type="text" name="komoditi_hhk" value="<?= $fv('komoditi_hhk') ?>" maxlength="200"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat LS</label>
                    <input type="text" name="koordinat_ls" value="<?= $fv('koordinat_ls') ?>" inputmode="decimal"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 font-mono focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-600 text-gray-500 mb-1">Koordinat BT</label>
                    <input type="text" name="koordinat_bt" value="<?= $fv('koordinat_bt') ?>" inputmode="decimal"
                        class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 font-mono focus:border-forest-400 focus:ring-2 focus:ring-forest-100 outline-none">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700 shadow-sm"><?= $isEdit ? 'Simpan perubahan' : 'Simpan' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    const baseUrl = <?= json_encode(APP_URL) ?>;
    const searchInput = document.getElementById('kth_search');
    const hiddenInput = document.getElementById('kth_id_val');
    const dropdown = document.getElementById('kth_dropdown');
    let debounceTimer = null;

    searchInput.parentElement.style.position = 'relative';

    searchInput.addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length < 2) { dropdown.classList.add('hidden'); return; }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async function() {
            try {
                const res = await fetch(baseUrl + '/api/kth-search?q=' + encodeURIComponent(q));
                const data = await res.json();
                if (data.length === 0) {
                    dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">Tidak ditemukan</div>';
                } else {
                    dropdown.innerHTML = data.map(function(r) {
                        return '<div class="px-4 py-2.5 text-sm cursor-pointer hover:bg-forest-50 transition" data-id="' + r.id + '" data-label="' + r.kabupaten_nama + ' — ' + r.nama + ' (' + r.kode_register + ')">' +
                            '<span class="text-gray-800 font-500">' + r.nama + '</span>' +
                            '<span class="text-gray-400 text-xs ml-2">' + r.kabupaten_nama + ' · ' + r.kode_register + '</span></div>';
                    }).join('');
                }
                dropdown.classList.remove('hidden');
            } catch(e) { dropdown.classList.add('hidden'); }
        }, 250);
    });

    dropdown.addEventListener('click', function(e) {
        const item = e.target.closest('[data-id]');
        if (!item) return;
        hiddenInput.value = item.dataset.id;
        searchInput.value = item.dataset.label;
        dropdown.classList.add('hidden');
    });

    searchInput.addEventListener('focus', function() {
        if (dropdown.innerHTML && this.value.trim().length >= 2) dropdown.classList.remove('hidden');
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Clear hidden ID if user manually clears the text
    searchInput.addEventListener('change', function() {
        if (this.value.trim() === '') hiddenInput.value = '';
    });
})();
</script>
