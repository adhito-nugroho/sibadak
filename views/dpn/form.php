<?php
/** @var array|null $dpn */
$isEdit = $dpn !== null;
$action = $isEdit ? APP_URL . '/dpn/' . (int) $dpn['id'] . '/update' : APP_URL . '/dpn/store';
$fv = static fn(string $f) => htmlspecialchars((string) ($dpn[$f] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="max-w-2xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-sm font-semibold text-gray-800"><?= $isEdit ? 'Edit DPN' : 'Tambah DPN' ?></h2>
        <a href="<?= htmlspecialchars($isEdit ? APP_URL . '/dpn/' . (int) $dpn['id'] : APP_URL . '/dpn', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">← Kembali</a>
    </div>
    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Sasaran / Penerima</label>
                <input type="text" name="sasaran" value="<?= $fv('sasaran') ?>" maxlength="200" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Lokasi <span class="text-red-500">*</span></label>
                <textarea name="lokasi" required rows="2" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none resize-y"><?= $fv('lokasi') ?></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">SubDAS</label>
                <input type="text" name="subdas" value="<?= $fv('subdas') ?>" maxlength="100" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tahun <span class="text-red-500">*</span></label>
                <select name="tahun" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                    <?php foreach ($yearOptions as $th): ?><option value="<?= $th ?>" <?= $isEdit && (int) $dpn['tahun'] === $th ? 'selected' : '' ?>><?= $th ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Jumlah Unit</label>
                <input type="number" name="jumlah_unit" value="<?= $isEdit ? (int) $dpn['jumlah_unit'] : 1 ?>" min="1" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Panjang (m)</label>
                <input type="text" name="panjang_m" value="<?= $fv('panjang_m') ?>" inputmode="decimal" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Lebar (m)</label>
                <input type="text" name="lebar_m" value="<?= $fv('lebar_m') ?>" inputmode="decimal" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tinggi (m)</label>
                <input type="text" name="tinggi_m" value="<?= $fv('tinggi_m') ?>" inputmode="decimal" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Koordinat LS</label>
                <input type="text" name="koordinat_ls" value="<?= $fv('koordinat_ls') ?>" inputmode="decimal" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Koordinat BT</label>
                <input type="text" name="koordinat_bt" value="<?= $fv('koordinat_bt') ?>" inputmode="decimal" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
        </div>
        <div class="flex justify-end pt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium hover:bg-forest-700"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan DPN' ?></button>
        </div>
    </form>
</div>
