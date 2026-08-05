<?php
/** @var array<string,mixed>|null $row */
$isEdit = $row !== null;
$action = $isEdit ? APP_URL . '/penyuluh/' . (int) $row['id'] . '/update' : APP_URL . '/penyuluh/store';
$fv = static fn (string $field): string => $row === null ? '' : htmlspecialchars((string) ($row[$field] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="max-w-3xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-display font-700 text-gray-800 text-lg"><?= $isEdit ? 'Edit Penyuluh' : 'Tambah Penyuluh' ?></h2>
        <a href="<?= htmlspecialchars(APP_URL . '/penyuluh', ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-forest-600 hover:underline">Batal</a>
    </div>
    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="stat-card p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">NIP <span class="text-red-500">*</span></label>
                <input type="text" name="nip" value="<?= $fv('nip') ?>" required maxlength="32" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="<?= $fv('nama') ?>" required maxlength="150" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Pangkat <span class="text-red-500">*</span></label>
                <input type="text" name="pangkat" value="<?= $fv('pangkat') ?>" required maxlength="100" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-500 mb-1">Status</label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 px-3 py-2.5 rounded-xl border border-gray-200"><input type="checkbox" name="is_active" value="1" <?= !$isEdit || (int) $row['is_active'] === 1 ? 'checked' : '' ?>> Aktif</label>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-600 text-gray-500 mb-1">Jabatan <span class="text-red-500">*</span></label>
                <textarea name="jabatan" rows="3" required maxlength="255" class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 outline-none resize-y"><?= $fv('jabatan') ?></textarea>
            </div>
        </div>
        <div class="flex justify-end">
            <button class="px-5 py-2.5 rounded-xl bg-forest-600 text-white text-sm font-600 hover:bg-forest-700"><?= $isEdit ? 'Simpan perubahan' : 'Simpan penyuluh' ?></button>
        </div>
    </form>
    <?php if ($isEdit): ?>
    <form method="post" action="<?= htmlspecialchars(APP_URL . '/penyuluh/' . (int) $row['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>" class="mt-4 text-right" onsubmit="return confirm('Nonaktifkan penyuluh ini?');">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <button class="text-sm text-red-600 hover:text-red-700 font-600 px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50">Nonaktifkan</button>
    </form>
    <?php endif; ?>
</div>
