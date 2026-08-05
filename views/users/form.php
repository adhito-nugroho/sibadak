<?php
/** @var array|null $user */
/** @var list<array{id:int,nama:string}> $roles */
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
$isEdit = $user !== null;
$action = $isEdit ? APP_URL . '/users/' . (int) $user['id'] . '/update' : APP_URL . '/users/store';
$fv = static function (string $field) use ($user): string {
    if ($user === null) return '';
    return htmlspecialchars((string) ($user[$field] ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
<div class="max-w-2xl mx-auto fade-up">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-sm font-semibold text-gray-800"><?= $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h2>
        <a href="<?= htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">← Kembali</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="<?= $fv('nama') ?>" required maxlength="150" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" value="<?= $fv('username') ?>" required maxlength="60" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500 font-mono">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="<?= $fv('email') ?>" required maxlength="150" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
            </div>
            <?php if (!$isEdit): ?>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Password</label>
                <input type="text" name="password" placeholder="Kosongkan untuk generate otomatis" maxlength="100" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
                <p class="text-[10px] text-gray-400 mt-1">Jika dikosongkan, sistem akan membuat password acak 10 karakter.</p>
            </div>
            <?php endif; ?>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Role <span class="text-red-500">*</span></label>
                <select name="role_id" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id'] ?>" <?= $isEdit && (int) $user['role_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Kabupaten</label>
                <select name="kabupaten_id" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
                    <option value="">Semua (Admin/Viewer)</option>
                    <?php foreach ($kabupatenList as $kb): ?>
                    <option value="<?= (int) $kb['id'] ?>" <?= $isEdit && (int) ($user['kabupaten_id'] ?? 0) === (int) $kb['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-gray-400 mt-1">Wajib diisi untuk role Operator (membatasi akses per kabupaten).</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="<?= htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50">Batal</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium hover:bg-forest-700"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' ?></button>
        </div>
    </form>
</div>
