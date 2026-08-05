<?php
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
?>
<div class="max-w-2xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-sm font-semibold text-gray-800">Import NTE dari Excel</h2>
        <a href="<?= htmlspecialchars(APP_URL . '/nte', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">← Kembali</a>
    </div>

    <!-- Info format -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4 text-xs text-blue-800">
        <p class="font-medium mb-2"><i class="ti ti-info-circle text-sm"></i> Format File yang Diterima</p>
        <ul class="space-y-1 text-blue-700">
            <li>• Format: <strong>.xlsx</strong>, .xls, atau .csv</li>
            <li>• Baris pertama = header (akan dilewati otomatis)</li>
            <li>• Kolom wajib sesuai urutan: <strong>No | Provinsi | Kab/Kota | Nama Kelompok | Tahun | Bulan | Barang/Jasa | Jenis Produk | Jumlah Penjualan | Satuan | NTE KTH (Rp.) | Penyuluh</strong></li>
            <li>• Nilai Rp boleh tanpa pemisah ribuan</li>
            <li>• Data ratusan ribu baris didukung (chunk 500 baris per transaksi)</li>
        </ul>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars(APP_URL . '/nte/import', ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 space-y-4" id="importForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div>
            <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">File Excel / CSV <span class="text-red-500">*</span></label>
            <input type="file" name="nte_file" accept=".xlsx,.xls,.csv" required
                class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none bg-white">
            <p class="text-[10px] text-gray-400 mt-1">Maksimal 50MB. Proses import mungkin memakan waktu beberapa menit untuk file besar.</p>
        </div>

        <div id="importProgress" class="hidden">
            <div class="flex items-center gap-2 text-xs text-gray-600">
                <i class="ti ti-loader ti-spin text-forest-600"></i>
                Sedang mengimport data, jangan tutup halaman ini...
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" id="importBtn" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium hover:bg-forest-700 flex items-center gap-1.5">
                <i class="ti ti-upload text-sm leading-none"></i> Mulai Import
            </button>
        </div>
    </form>

    <!-- Petunjuk -->
    <div class="mt-4 bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-800">
        <p class="font-medium mb-1"><i class="ti ti-alert-triangle text-sm"></i> Perhatian</p>
        <ul class="space-y-1 text-amber-700">
            <li>• Import tidak akan menghapus data yang sudah ada — hanya menambah</li>
            <li>• Nama KTH akan dicocokkan otomatis dengan master KTH jika tersedia</li>
            <li>• Data yang tidak cocok tetap disimpan dengan nama teks aslinya</li>
            <li>• Pastikan kolom <strong>Tahun</strong> berisi angka (misal: 2026) dan <strong>Bulan</strong> berisi angka 1-12</li>
        </ul>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', function() {
    document.getElementById('importBtn').disabled = true;
    document.getElementById('importBtn').textContent = 'Memproses...';
    document.getElementById('importProgress').classList.remove('hidden');
});
</script>
