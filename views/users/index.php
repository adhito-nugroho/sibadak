<?php
/** @var array $result */
/** @var string $filterQ */
$rows = $result['data'];
$pages = $result['pages'];
$cur = $result['current'];
$total = $result['total'];
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-5 fade-up">
    <div>
        <h2 class="text-sm font-semibold text-gray-800">Manajemen Pengguna</h2>
        <p class="text-[10px] text-gray-400 mt-0.5"><?= format_id($total) ?> pengguna terdaftar</p>
    </div>
    <a href="<?= htmlspecialchars(APP_URL . '/users/create', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-1.5 bg-forest-800 hover:bg-forest-700 text-white text-xs font-medium px-3.5 py-2 rounded-lg transition-colors">
        <i class="ti ti-plus text-sm leading-none"></i> Tambah Pengguna
    </a>
</div>

<form method="get" action="<?= htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8') ?>" class="bg-white rounded-xl border border-gray-100 p-4 mb-4 fade-up delay-1 flex items-end gap-3">
    <div class="flex-1">
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Cari</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama, username, atau email..." class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none focus:ring-1 focus:ring-forest-500 focus:border-forest-500">
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg bg-forest-800 text-white text-xs font-medium">Cari</button>
</form>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden fade-up delay-2">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-4 py-2.5 text-left">Nama</th>
                    <th class="px-4 py-2.5 text-left">Username</th>
                    <th class="px-4 py-2.5 text-left">Email</th>
                    <th class="px-4 py-2.5 text-left">Role</th>
                    <th class="px-4 py-2.5 text-left">Kabupaten</th>
                    <th class="px-4 py-2.5 text-center">Status</th>
                    <th class="px-4 py-2.5 text-left">Login Terakhir</th>
                    <th class="px-4 py-2.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if ($rows === []): ?>
                <tr><td colspan="8" class="px-4 py-8 text-center text-xs text-gray-500">Tidak ada data pengguna.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                <tr class="hover:bg-forest-50/50 transition-colors">
                    <td class="px-4 py-2.5 text-xs font-medium text-gray-800"><?= htmlspecialchars((string) $r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 font-mono"><?= htmlspecialchars((string) $r['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= htmlspecialchars((string) $r['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-2.5">
                        <?php $roleBadge = match ((string) $r['role_nama']) {
                            'admin' => 'bg-red-50 text-red-700',
                            'operator' => 'bg-amber-50 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        }; ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium <?= $roleBadge ?>"><?= ucfirst((string) $r['role_nama']) ?></span>
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= $r['kabupaten_nama'] !== null ? htmlspecialchars((string) $r['kabupaten_nama'], ENT_QUOTES, 'UTF-8') : '<span class="text-gray-400">Semua</span>' ?></td>
                    <td class="px-4 py-2.5 text-center">
                        <?php if ((int) $r['is_active'] === 1): ?>
                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-green-600"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif</span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-gray-400"><span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-500"><?= $r['last_login'] !== null ? htmlspecialchars((string) $r['last_login'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td class="px-4 py-2.5 text-right whitespace-nowrap">
                        <a href="<?= htmlspecialchars(APP_URL . '/users/' . (int) $r['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>" class="text-[10px] font-medium text-forest-600 hover:underline">Edit</a>
                        <span class="text-gray-300 mx-1">|</span>
                        <form method="post" action="<?= htmlspecialchars(APP_URL . '/users/' . (int) $r['id'] . '/reset-password', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('Reset password pengguna ini?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="text-[10px] font-medium text-amber-600 hover:underline">Reset PW</button>
                        </form>
                        <span class="text-gray-300 mx-1">|</span>
                        <form method="post" action="<?= htmlspecialchars(APP_URL . '/users/' . (int) $r['id'] . '/toggle-active', ENT_QUOTES, 'UTF-8') ?>" class="inline" onsubmit="return confirm('<?= (int) $r['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?> pengguna ini?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="text-[10px] font-medium <?= (int) $r['is_active'] === 1 ? 'text-red-500 hover:underline' : 'text-green-600 hover:underline' ?>"><?= (int) $r['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex justify-center gap-2 text-xs">
        <?php if ($cur > 1): ?>
        <a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="?<?= http_build_query(array_merge($_GET, ['page' => $cur - 1])) ?>">« Prev</a>
        <?php endif; ?>
        <span class="px-3 py-1 text-gray-500"><?= $cur ?> / <?= $pages ?></span>
        <?php if ($cur < $pages): ?>
        <a class="px-3 py-1 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" href="?<?= http_build_query(array_merge($_GET, ['page' => $cur + 1])) ?>">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
