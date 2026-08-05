<?php
/** @var array<string, mixed> $kps */
/** @var list<array{id:int,nama:string}> $kabupatenList */
?>
<div class="max-w-6xl mx-auto space-y-6 fade-up">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide font-display font-600">Anggota KPS</p>
            <h2 class="font-display font-700 text-gray-800 text-xl mt-1">Impor Anggota dari Excel / CSV</h2>
            <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars((string) $kps['nama_lembaga'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'] . '/anggota', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">← Kembali</a>
        </div>
    </div>

    <!-- Stepper -->
    <div class="flex items-center justify-center max-w-lg mx-auto bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div id="step-badge-1" class="w-8 h-8 rounded-full bg-forest-600 text-white flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300">1</div>
            <span id="step-text-1" class="text-sm font-semibold text-gray-800 transition-all duration-300">Unggah File</span>
            <div class="w-12 h-[2px] bg-gray-200" id="step-divider"></div>
            <div id="step-badge-2" class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-sm transition-all duration-300">2</div>
            <span id="step-text-2" class="text-sm font-medium text-gray-400 transition-all duration-300">Atur Pemetaan</span>
        </div>
    </div>

    <!-- Step 1: Upload Panel -->
    <div id="upload-panel" class="stat-card p-8 max-w-2xl mx-auto space-y-6 text-center">
        <div class="flex flex-col items-center justify-center py-6 border-2 border-dashed border-gray-200 rounded-2xl hover:border-forest-400 transition-colors cursor-pointer relative group" id="drop-zone">
            <input type="file" id="import_file" accept=".xlsx,.xls,.csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
            <div class="w-16 h-16 rounded-full bg-forest-50 text-forest-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h4 class="font-display font-600 text-gray-700 text-sm">Seret & letakkan file Excel / CSV di sini</h4>
            <p class="text-xs text-gray-400 mt-1">Mendukung format .xlsx, .xls, .csv</p>
            <div id="selected-file-info" class="mt-4 hidden p-2 px-4 bg-forest-50 border border-forest-100 text-forest-800 text-xs rounded-xl font-600"></div>
        </div>

        <div id="upload-error" class="hidden text-xs text-red-600 bg-red-50 border border-red-100 p-3 rounded-xl"></div>

        <div class="flex justify-center pt-2">
            <button type="button" id="btn-next-step" disabled class="px-6 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-md flex items-center gap-2">
                Unggah & Atur Pemetaan
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Step 2: Mapping Panel (hidden initially) -->
    <form id="mapping-form" method="post" action="<?= htmlspecialchars(APP_URL . '/kps/' . (int) $kps['id'] . '/anggota/import/process', ENT_QUOTES, 'UTF-8') ?>" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="temp_file" id="temp_file_path" value="">

        <!-- Left: Mapping Settings (lg:col-span-5) -->
        <div class="lg:col-span-5 stat-card p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3">
                <h3 class="font-display font-700 text-gray-800 text-sm">Petakan Kolom Data</h3>
                <p class="text-xs text-gray-400 mt-0.5">Tentukan kolom Excel yang sesuai untuk setiap field.</p>
            </div>

            <!-- Global Kategori Default -->
            <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl space-y-2">
                <label class="block text-xs font-700 text-gray-600">Kategori Default Data <span class="text-red-500">*</span></label>
                <select name="kategori_default" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 bg-white outline-none focus:border-forest-500">
                    <option value="andil_garapan">Andil Garapan</option>
                    <option value="ruang_perlindungan_komunal">Ruang Perlindungan &amp; Komunal</option>
                </select>
                <p class="text-[10px] text-gray-400">Digunakan jika kategori tidak dipetakan secara individu dari kolom Excel.</p>
            </div>

            <div class="space-y-4 max-h-[450px] overflow-y-auto pr-1">
                <!-- List target fields -->
                <?php
                $targetFields = [
                    'nama_penggarap' => ['Nama Penggarap', true],
                    'no_andil'       => ['No Andil', false],
                    'nik'            => ['NIK / No KTP', false],
                    'no_kk'          => ['No KK', false],
                    'luas_ha'        => ['Luas (Ha)', false],
                    'komoditas'      => ['Komoditas', false],
                    'kecamatan_id'   => ['Kecamatan (Nama)', false],
                    'desa_id'        => ['Desa (Nama)', false],
                    'koordinat_bt'   => ['Koordinat BT (Bujur)', false],
                    'koordinat_ls'   => ['Koordinat LS (Lintang)', false],
                    'batas_barat'    => ['Batas Barat', false],
                    'batas_utara'    => ['Batas Utara', false],
                    'batas_selatan'  => ['Batas Selatan', false],
                    'batas_timur'    => ['Batas Timur', false],
                    'pengukur'       => ['Pengukur', false],
                    'tanggal'        => ['Tanggal', false],
                ];
                foreach ($targetFields as $fieldName => [$label, $required]):
                ?>
                <div class="space-y-1.5 mapping-group">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-600 text-gray-600 flex items-center gap-1">
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
                        </label>
                    </div>
                    <select name="mappings[<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>]" 
                            id="map_<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>" 
                            data-field="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>"
                            class="mapping-select w-full text-xs px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-forest-100 focus:border-forest-500 bg-white">
                        <option value="">— Abaikan —</option>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex gap-2 pt-3 border-t border-gray-100">
                <button type="button" id="btn-back-to-upload" class="flex-1 py-2 rounded-xl border border-gray-200 text-xs text-gray-600 font-600 hover:bg-gray-50">Kembali</button>
                <button type="submit" class="flex-1 py-2 rounded-xl bg-forest-600 text-white text-xs font-600 hover:bg-forest-700 shadow-md">Mulai Impor</button>
            </div>
        </div>

        <!-- Right: Interactive Preview (lg:col-span-7) -->
        <div class="lg:col-span-7 stat-card p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-display font-700 text-gray-800 text-sm">Pratinjau File Excel</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Tampilan data 5 baris pertama dari file Anda.</p>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-100 rounded-xl max-h-[500px]">
                <table class="w-full text-left text-xs border-collapse" id="preview-table">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100" id="preview-table-headers-mapping">
                            <!-- Field mapping tags go here -->
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-100" id="preview-table-headers">
                            <!-- Excel headers go here -->
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="preview-table-body">
                        <!-- Preview rows go here -->
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<!-- Loading overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm flex flex-col items-center justify-center text-white">
    <div class="w-16 h-16 border-4 border-white border-t-forest-600 rounded-full animate-spin mb-4"></div>
    <p class="font-display font-600 text-sm" id="loading-message">Memproses...</p>
</div>

<script>
(function () {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('import_file');
    const fileInfo = document.getElementById('selected-file-info');
    const btnNext = document.getElementById('btn-next-step');
    const uploadPanel = document.getElementById('upload-panel');
    const mappingForm = document.getElementById('mapping-form');
    const tempFileHidden = document.getElementById('temp_file_path');
    const uploadError = document.getElementById('upload-error');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingMsg = document.getElementById('loading-message');

    let excelHeaders = [];
    let excelRows = [];

    // Dropzone logic
    ['dragenter', 'dragover'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-forest-500', 'bg-forest-50/50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-forest-500', 'bg-forest-50/50');
        }, false);
    });

    // Click zone handler
    dropZone.addEventListener('click', function (e) {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            fileInfo.textContent = `Berkas terpilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            fileInfo.classList.remove('hidden');
            btnNext.removeAttribute('disabled');
            uploadError.classList.add('hidden');
        } else {
            fileInfo.classList.add('hidden');
            btnNext.setAttribute('disabled', 'true');
        }
    });

    // Step 1: Upload -> Preview
    btnNext.addEventListener('click', async function () {
        if (!fileInput.files || !fileInput.files[0]) return;
        
        loadingMsg.textContent = 'Membaca file Excel...';
        loadingOverlay.classList.remove('hidden');
        uploadError.classList.add('hidden');

        const formData = new FormData();
        formData.append('import_file', fileInput.files[0]);
        formData.append('csrf_token', '<?= csrf_token() ?>');

        try {
            const url = window.APP_URL + '/kps/' + <?= (int)$kps['id'] ?> + '/anggota/import/preview';
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.error || 'Gagal membaca berkas Excel. Silakan periksa format berkas.');
            }

            // Populate data
            tempFileHidden.value = data.temp_file;
            excelHeaders = data.headers || [];
            excelRows = data.preview_rows || [];

            // Initialize mapping controls
            initMappingSelects();
            renderPreviewTable();

            // Toggle panels
            uploadPanel.classList.add('hidden');
            mappingForm.classList.remove('hidden');

            // Stepper style
            document.getElementById('step-badge-1').classList.remove('bg-forest-600', 'text-white');
            document.getElementById('step-badge-1').classList.add('bg-forest-50', 'text-forest-600');
            document.getElementById('step-text-1').classList.remove('font-semibold', 'text-gray-800');
            document.getElementById('step-text-1').classList.add('text-gray-400');
            
            document.getElementById('step-badge-2').classList.remove('bg-gray-100', 'text-gray-400');
            document.getElementById('step-badge-2').classList.add('bg-forest-600', 'text-white', 'shadow-sm');
            document.getElementById('step-text-2').classList.remove('text-gray-400');
            document.getElementById('step-text-2').classList.add('font-semibold', 'text-gray-800');

        } catch (err) {
            uploadError.textContent = err.message;
            uploadError.classList.remove('hidden');
        } finally {
            loadingOverlay.classList.add('hidden');
        }
    });

    // Step 2: Back to Upload
    document.getElementById('btn-back-to-upload').addEventListener('click', function () {
        mappingForm.classList.add('hidden');
        uploadPanel.classList.remove('hidden');

        // Reset Stepper
        document.getElementById('step-badge-1').classList.add('bg-forest-600', 'text-white');
        document.getElementById('step-badge-1').classList.remove('bg-forest-50', 'text-forest-600');
        document.getElementById('step-text-1').classList.add('font-semibold', 'text-gray-800');
        document.getElementById('step-text-1').classList.remove('text-gray-400');

        document.getElementById('step-badge-2').classList.add('bg-gray-100', 'text-gray-400');
        document.getElementById('step-badge-2').classList.remove('bg-forest-600', 'text-white', 'shadow-sm');
        document.getElementById('step-text-2').classList.add('text-gray-400');
        document.getElementById('step-text-2').classList.remove('font-semibold', 'text-gray-800');
    });

    // Auto-match logic
    const fieldRules = [
        { name: 'nama_penggarap', labels: ['nama', 'penggarap', 'nama penggarap', 'nama_penggarap', 'name'] },
        { name: 'no_andil', labels: ['no andil', 'no_andil', 'andil', 'no. andil', 'no_urut', 'no'] },
        { name: 'nik', labels: ['nik', 'nomor induk', 'no. ktp', 'ktp', 'identity'] },
        { name: 'no_kk', labels: ['kk', 'no kk', 'nokk', 'no_kk', 'no. kk', 'kartu keluarga'] },
        { name: 'luas_ha', labels: ['luas', 'luas ha', 'luas (ha)', 'luas_ha', 'luasan'] },
        { name: 'komoditas', labels: ['komoditas', 'komoditi', 'tanaman'] },
        { name: 'kecamatan_id', labels: ['kecamatan', 'kec'] },
        { name: 'desa_id', labels: ['desa', 'kelurahan'] },
        { name: 'koordinat_bt', labels: ['koordinat bt', 'koordinat_bt', 'bujur', 'bt', 'x'] },
        { name: 'koordinat_ls', labels: ['koordinat ls', 'koordinat_ls', 'lintang', 'ls', 'y'] },
        { name: 'batas_barat', labels: ['batas barat', 'barat'] },
        { name: 'batas_utara', labels: ['batas utara', 'utara'] },
        { name: 'batas_selatan', labels: ['batas selatan', 'selatan'] },
        { name: 'batas_timur', labels: ['batas timur', 'timur'] },
        { name: 'pengukur', labels: ['pengukur', 'surveyor'] },
        { name: 'tanggal', labels: ['tanggal', 'tgl', 'date'] }
    ];

    function initMappingSelects() {
        const selects = document.querySelectorAll('.mapping-select');
        selects.forEach(select => {
            // Clear current options except "Abaikan"
            select.innerHTML = '<option value="">— Abaikan —</option>';
            
            // Add excel columns as options (value is 0-indexed column index)
            excelHeaders.forEach((h, index) => {
                const opt = document.createElement('option');
                opt.value = index;
                opt.textContent = `Kolom ${index + 1}: ${h || '(kosong)'}`;
                select.appendChild(opt);
            });

            // Auto-matching
            const fName = select.dataset.field;
            const rule = fieldRules.find(r => r.name === fName);
            if (rule) {
                // Find matching column header
                const matchIndex = excelHeaders.findIndex(header => {
                    const hLower = (header || '').toLowerCase().trim();
                    return rule.labels.some(l => hLower.includes(l) || l.includes(hLower));
                });
                if (matchIndex !== -1) {
                    select.value = matchIndex;
                }
            }

            // Listen to select changes to update highlighted columns
            select.addEventListener('change', updateTableHighlighting);
        });
        
        updateTableHighlighting();
    }

    function renderPreviewTable() {
        const trMapping = document.getElementById('preview-table-headers-mapping');
        const trHeaders = document.getElementById('preview-table-headers');
        const tbody = document.getElementById('preview-table-body');

        trMapping.innerHTML = '';
        trHeaders.innerHTML = '';
        tbody.innerHTML = '';

        // Generate headers columns
        excelHeaders.forEach((h, i) => {
            const thMap = document.createElement('th');
            thMap.className = 'px-3 py-1 font-semibold text-[10px] text-gray-400 border-b border-gray-100 bg-gray-50/50';
            thMap.id = `col-map-badge-${i}`;
            thMap.innerHTML = '<span class="text-gray-300">abaikan</span>';
            trMapping.appendChild(thMap);

            const thHeader = document.createElement('th');
            thHeader.className = 'px-3 py-2 text-gray-700 font-semibold border-b border-gray-200 bg-gray-50';
            thHeader.textContent = h || `Kolom ${i + 1}`;
            trHeaders.appendChild(thHeader);
        });

        // Generate preview rows (max 5 rows)
        excelRows.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50/50';
            
            excelHeaders.forEach((h, i) => {
                const td = document.createElement('td');
                td.className = 'px-3 py-2 text-gray-600 border-b border-gray-100 font-mono text-[11px] whitespace-nowrap overflow-hidden max-w-[150px]';
                td.textContent = row[i] !== null && row[i] !== undefined ? row[i] : '';
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
    }

    function updateTableHighlighting() {
        // Reset all columns highlights & badges
        excelHeaders.forEach((h, i) => {
            const badge = document.getElementById(`col-map-badge-${i}`);
            if (badge) {
                badge.innerHTML = '<span class="text-gray-300">abaikan</span>';
            }

            // Remove bg highlight classes in table
            const table = document.getElementById('preview-table');
            const cells = table.querySelectorAll(`tr td:nth-child(${i + 1})`);
            cells.forEach(td => {
                td.classList.remove('bg-forest-50/40', 'font-600', 'text-forest-900');
            });
            const thHeaders = table.querySelectorAll(`tr:nth-child(2) th:nth-child(${i + 1})`);
            thHeaders.forEach(th => {
                th.classList.remove('bg-forest-50', 'text-forest-900');
            });
        });

        // Apply highlighting for mapped fields
        const selects = document.querySelectorAll('.mapping-select');
        selects.forEach(select => {
            const val = select.value;
            if (val !== '') {
                const colIdx = parseInt(val);
                const fieldName = select.dataset.field;
                const label = select.options[select.selectedIndex].text.split(': ')[1] || '';
                const fieldLabel = select.closest('.mapping-group').querySelector('label').textContent.replace('*', '').trim();

                const badge = document.getElementById(`col-map-badge-${colIdx}`);
                if (badge) {
                    badge.innerHTML = `<span class="inline-block px-1.5 py-0.5 rounded-md bg-forest-100 text-forest-700 font-700 text-[9px] uppercase tracking-wider">${fieldLabel}</span>`;
                }

                // Add bg highlight classes
                const table = document.getElementById('preview-table');
                const cells = table.querySelectorAll(`tr td:nth-child(${colIdx + 1})`);
                cells.forEach(td => {
                    td.classList.add('bg-forest-50/40', 'font-600', 'text-forest-900');
                });
                const thHeaders = table.querySelectorAll(`tr:nth-child(2) th:nth-child(${colIdx + 1})`);
                thHeaders.forEach(th => {
                    th.classList.add('bg-forest-50', 'text-forest-900');
                });
            }
        });
    }

    // Submit form handler
    mappingForm.addEventListener('submit', function (e) {
        // Validate required field (Nama Penggarap)
        const nameSelect = document.getElementById('map_nama_penggarap');
        if (nameSelect.value === '') {
            e.preventDefault();
            alert('Kolom "Nama Penggarap" wajib dipetakan untuk memproses impor data.');
            return;
        }

        loadingMsg.textContent = 'Mengimpor data anggota KPS...';
        loadingOverlay.classList.remove('hidden');
    });

})();
</script>
