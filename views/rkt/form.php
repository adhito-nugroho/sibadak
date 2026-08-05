<?php
/** @var array|null $rkt */
/** @var list<array{id:int,nama_lembaga:string,kabupaten_id:int,kabupaten_nama:string}> $kpsOptions */
/** @var list<int> $yearOptions */
/** @var int $selectedKpsId */
$isEdit = $rkt !== null;
$action = $isEdit ? APP_URL . '/rkt/' . (int) $rkt['id'] . '/update' : APP_URL . '/rkt/store';
?>
<div class="max-w-3xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit RKT' : 'Tambah RKT' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/rkt/' . (int) $rkt['id'] : APP_URL . '/rkt', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">← Batal</a>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="stat-card p-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div>
            <label class="block text-xs font-600 text-gray-500 mb-1">KPS <span class="text-red-500">*</span></label>
            <div style="position:relative;">
                <input type="hidden" name="kps_id" id="kps_id_val" value="<?= $isEdit ? (int) $rkt['kps_id'] : ($selectedKpsId > 0 ? $selectedKpsId : '') ?>" required>
                <div class="relative flex items-center">
                    <input type="text" id="kps_search" placeholder="Pilih KPS..." autocomplete="off"
                        value="<?php
                            $selKpsId = $isEdit ? (int) $rkt['kps_id'] : $selectedKpsId;
                            if ($selKpsId > 0) {
                                foreach ($kpsOptions as $kps) {
                                    if ((int) $kps['id'] === $selKpsId) {
                                        echo htmlspecialchars($kps['kabupaten_nama'] . ' — ' . $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8');
                                        break;
                                    }
                                }
                            }
                        ?>"
                        class="w-full text-sm pl-3 pr-10 py-2 rounded-xl border border-gray-200 bg-white outline-none focus:border-forest-400 focus:ring-2 focus:ring-forest-100">
                    <button type="button" id="kps_toggle" class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="ti ti-chevron-down text-base"></i>
                    </button>
                </div>
                <div id="kps_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg divide-y divide-gray-50">
                    <?php foreach ($kpsOptions as $kps): ?>
                        <?php $label = $kps['kabupaten_nama'] . ' — ' . $kps['nama_lembaga']; ?>
                        <div class="kps-item px-4 py-2.5 text-sm cursor-pointer hover:bg-forest-50 transition" data-id="<?= (int) $kps['id'] ?>" data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-gray-400 ml-2"><?= htmlspecialchars($kps['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($kps['skema'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div id="kps_no_results" class="hidden px-4 py-3 text-sm text-gray-400">Tidak ditemukan</div>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-1">Klik untuk melihat daftar atau ketik untuk menyaring pilihan</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Tahun <span class="text-red-500">*</span></label>
                <select name="tahun" required class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
                    <?php foreach ($yearOptions as $th): ?>
                    <option value="<?= $th ?>" <?= $isEdit && (int) $rkt['tahun'] === $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
                    <?php foreach (RktKps::statusList() as $st): ?>
                    <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $isEdit && (string) $rkt['status'] === $st ? 'selected' : (!$isEdit && $st === 'belum' ? 'selected' : '') ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-600 text-gray-500 mb-1">Catatan</label>
            <textarea name="catatan" rows="4" class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 outline-none"><?= $isEdit && !empty($rkt['catatan']) ? htmlspecialchars((string) $rkt['catatan'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
        </div>

        <div>
            <label class="block text-xs font-600 text-gray-500 mb-1">Upload Dokumen RKT (PDF/JPG/PNG)</label>
            <input type="file" name="rkt_file" accept=".pdf,.jpg,.jpeg,.png"
                class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white outline-none">
            <?php if ($isEdit && !empty($rkt['dokumen_link'])): ?>
                <p class="text-xs text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline break-all" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $rkt['dokumen_link'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $rkt['dokumen_link'], ENT_QUOTES, 'UTF-8') ?></a></p>
            <?php endif; ?>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700"><?= $isEdit ? 'Simpan perubahan' : 'Simpan RKT' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    const searchInput = document.getElementById('kps_search');
    const hiddenInput = document.getElementById('kps_id_val');
    const dropdown = document.getElementById('kps_dropdown');
    const toggleBtn = document.getElementById('kps_toggle');
    const noResults = document.getElementById('kps_no_results');
    const items = Array.from(dropdown.getElementsByClassName('kps-item'));

    function filterItems(query) {
        const q = query.toLowerCase().trim();
        let visibleCount = 0;
        
        items.forEach(item => {
            const label = item.getAttribute('data-label').toLowerCase();
            if (label.includes(q)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }

    function showDropdown() {
        dropdown.classList.remove('hidden');
        filterItems(searchInput.value);
    }

    searchInput.addEventListener('focus', showDropdown);
    searchInput.addEventListener('input', function() {
        showDropdown();
        if (this.value.trim() === '') {
            hiddenInput.value = '';
        }
    });

    items.forEach(item => {
        item.addEventListener('mousedown', function(e) {
            e.preventDefault();
            hiddenInput.value = this.getAttribute('data-id');
            searchInput.value = this.getAttribute('data-label');
            dropdown.classList.add('hidden');
        });
    });

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (dropdown.classList.contains('hidden')) {
                searchInput.focus();
            } else {
                dropdown.classList.add('hidden');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target))) {
            dropdown.classList.add('hidden');
        }
    });
})();
</script>
