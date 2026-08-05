<?php
/** @var array|null $rkps */
/** @var list<array{id:int,nama_lembaga:string,kabupaten_id:int,kabupaten_nama:string}> $kpsOptions */
/** @var int $selectedKpsId */
$isEdit = $rkps !== null;
$action = $isEdit ? APP_URL . '/rkps/' . (int) $rkps['id'] . '/update' : APP_URL . '/rkps/store';
?>
<div class="max-w-2xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-sm font-semibold text-gray-800"><?= $isEdit ? 'Edit RKPS' : 'Tambah RKPS' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/rkps/' . (int) $rkps['id'] : APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">← Kembali</a>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div>
            <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">KPS <span class="text-red-500">*</span></label>
            <div style="position:relative;">
                <input type="hidden" name="kps_id" id="kps_id_val" value="<?= $isEdit ? (int) $rkps['kps_id'] : ($selectedKpsId > 0 ? $selectedKpsId : '') ?>" required>
                <div class="relative flex items-center">
                    <input type="text" id="kps_search" placeholder="Pilih KPS..." autocomplete="off"
                        value="<?php
                            $selKpsId = $isEdit ? (int) $rkps['kps_id'] : $selectedKpsId;
                            if ($selKpsId > 0) {
                                foreach ($kpsOptions as $kps) {
                                    if ((int) $kps['id'] === $selKpsId) {
                                        echo htmlspecialchars($kps['kabupaten_nama'] . ' — ' . $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8');
                                        break;
                                    }
                                }
                            }
                        ?>"
                        class="w-full text-xs pl-3 pr-10 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
                    <button type="button" id="kps_toggle" class="absolute right-0 top-0 bottom-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="ti ti-chevron-down text-sm"></i>
                    </button>
                </div>
                <div id="kps_dropdown" class="hidden absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg divide-y divide-gray-50">
                    <?php foreach ($kpsOptions as $kps): ?>
                        <?php $label = $kps['kabupaten_nama'] . ' — ' . $kps['nama_lembaga']; ?>
                        <div class="kps-item px-4 py-2.5 text-xs cursor-pointer hover:bg-forest-50 transition" data-id="<?= (int) $kps['id'] ?>" data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-gray-400 ml-2"><?= htmlspecialchars($kps['kabupaten_nama'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($kps['skema'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div id="kps_no_results" class="hidden px-4 py-3 text-xs text-gray-400">Tidak ditemukan</div>
                </div>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">Klik untuk melihat daftar atau ketik untuk menyaring pilihan</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Periode Awal <span class="text-red-500">*</span></label>
                <select name="periode_awal" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                    <?php for ($y = 2020; $y <= 2035; $y++): ?>
                    <option value="<?= $y ?>" <?= $isEdit && (int) $rkps['periode_awal'] === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Periode Akhir <span class="text-red-500">*</span></label>
                <select name="periode_akhir" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                    <?php for ($y = 2025; $y <= 2045; $y++): ?>
                    <option value="<?= $y ?>" <?= $isEdit && (int) $rkps['periode_akhir'] === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                    <?php foreach (RkpsKps::statusList() as $st): ?>
                    <option value="<?= $st ?>" <?= $isEdit && (string) $rkps['status'] === $st ? 'selected' : (!$isEdit && $st === 'belum' ? 'selected' : '') ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Catatan</label>
            <textarea name="catatan" rows="4" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none resize-y"><?= $isEdit && !empty($rkps['catatan']) ? htmlspecialchars((string) $rkps['catatan'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
        </div>

        <div>
            <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Upload Dokumen RKPS (PDF/JPG/PNG)</label>
            <input type="file" name="rkps_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            <?php if ($isEdit && !empty($rkps['dokumen_link'])): ?>
                <p class="text-[10px] text-gray-400 mt-2">File tersimpan: <a class="text-forest-600 hover:underline" target="_blank" rel="noopener" href="<?= htmlspecialchars(APP_URL . '/' . ltrim((string) $rkps['dokumen_link'], '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $rkps['dokumen_link'], ENT_QUOTES, 'UTF-8') ?></a></p>
            <?php endif; ?>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium hover:bg-forest-700"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan RKPS' ?></button>
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
