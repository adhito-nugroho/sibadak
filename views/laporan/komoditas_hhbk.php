<?php
/** @var list<array{id:int,nama:string,satuan:string,urutan:int,is_active:int}> $komoditas */
/** @var int $tahun */
/** @var array<string,float> $targets */
$yearOptions = range((int)date('Y'), 2020);
?>
<div class="max-w-3xl mx-auto space-y-5 fade-up">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800">Komoditas & Target DPA — HHBK</h2>
        <a href="<?= htmlspecialchars(APP_URL . '/laporan', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">← Kembali</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <h3 class="text-xs font-semibold text-gray-700 mb-3">Tambah / Update Komoditas</h3>
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/laporan/komoditas-hhbk/save', ENT_QUOTES, 'UTF-8') ?>" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Nama Komoditas <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required placeholder="Bambu, Madu, Rotan..." class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <div class="w-24">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Satuan</label>
                <select name="satuan" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                    <option value="Kg">Kg</option>
                    <option value="Btg">Btg</option>
                </select>
            </div>
            <div class="w-20">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Urutan</label>
                <input type="number" name="urutan" min="1" max="99" placeholder="99" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium">Simpan</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-xs font-semibold text-gray-800">Daftar Komoditas HHBK</h3>
        </div>
        <table class="data-table w-full">
            <thead><tr class="border-b border-gray-100"><th class="px-4 py-2.5 text-left">Urutan</th><th class="px-4 py-2.5 text-left">Nama</th><th class="px-4 py-2.5 text-left">Satuan</th><th class="px-4 py-2.5 text-center">Status</th><th class="px-4 py-2.5 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            <?php foreach ($komoditas as $k): $aktif = (int)($k['is_active'] ?? 1); ?>
            <tr style="<?= !$aktif ? 'opacity:0.4' : '' ?>">
                <td class="px-4 py-2 text-xs text-gray-500"><?= (int)$k['urutan'] ?></td>
                <td class="px-4 py-2 text-xs font-medium text-gray-800"><?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="px-4 py-2 text-xs text-gray-600"><?= htmlspecialchars($k['satuan'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="px-4 py-2 text-center"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium <?= $aktif ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>"><?= $aktif ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="px-4 py-2 text-right">
                    <form method="post" action="<?= htmlspecialchars(APP_URL . '/laporan/komoditas-hhbk/' . (int)$k['id'] . '/toggle', ENT_QUOTES, 'UTF-8') ?>" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="text-[10px] font-medium <?= $aktif ? 'text-red-500' : 'text-green-600' ?> hover:underline"><?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <h3 class="text-xs font-semibold text-gray-700 mb-3">Input Target DPA per Tahun</h3>
        <form method="post" action="<?= htmlspecialchars(APP_URL . '/laporan/target-hhbk/save', ENT_QUOTES, 'UTF-8') ?>" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="w-32 mb-3">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tahun</label>
                <select name="tahun" onchange="this.form.submit()" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                    <?php foreach ($yearOptions as $y): ?><option value="<?= $y ?>" <?= $y === $tahun ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <?php foreach ($komoditas as $k): if (!(int)($k['is_active'] ?? 1)) continue; ?>
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 mb-1"><?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?> (<?= $k['satuan'] ?>)</label>
                    <input type="text" name="target_<?= (int)$k['id'] ?>" inputmode="decimal" value="<?= $targets[$k['nama']] ?? 0 ?>" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium">Simpan Target <?= $tahun ?></button>
            </div>
        </form>
    </div>
</div>
