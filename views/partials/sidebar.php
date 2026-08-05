<?php
$nav = $activeNav ?? '';
$u = $_SESSION['nama'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? 'viewer';
$initial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($u, 0, 1))
    : strtoupper(substr($u, 0, 1) ?: 'U');
?>
<aside class="bg-forest-800 w-56 min-h-screen flex flex-col fixed left-0 top-0 bottom-0 z-30">

    <!-- Brand -->
    <div class="px-4 py-4 border-b border-white/10">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-forest-500 flex items-center justify-center flex-shrink-0 overflow-hidden">
                <svg viewBox="0 0 40 40" fill="none" class="w-9 h-9" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="#22c55e"/>
                    <path d="M20 8c-1.2 2-6 7-6 12a6 6 0 0 0 12 0c0-5-4.8-10-6-12z" fill="white" opacity="0.92"/>
                    <rect x="18.8" y="23" width="2.4" height="6" rx="1.2" fill="white" opacity="0.7"/>
                    <circle cx="16.5" cy="15.5" r="1.8" fill="white" opacity="0.35"/>
                    <circle cx="23.5" cy="17" r="1.3" fill="white" opacity="0.25"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-white font-semibold text-sm leading-tight"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-white/40 text-[10px] leading-snug mt-0.5">CDK Wil. Bojonegoro</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto sidebar-nav py-2">

        <p class="px-4 pt-4 pb-1 text-[9px] font-medium text-white/30 uppercase tracking-widest">Menu Utama</p>

        <a href="<?= htmlspecialchars(APP_URL . '/', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'dashboard' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-layout-dashboard text-base leading-none <?= $nav === 'dashboard' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Dashboard
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'kth' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-building-community text-base leading-none <?= $nav === 'kth' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Data KTH
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'kelembagaan' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-affiliate text-base leading-none <?= $nav === 'kelembagaan' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Kelembagaan
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'kps' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-globe text-base leading-none <?= $nav === 'kps' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Data KPS
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/rkt', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'rkt' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-clipboard-list text-base leading-none <?= $nav === 'rkt' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            RKT per KPS
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'rkps' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-notebook text-base leading-none <?= $nav === 'rkps' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            RKPS
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/rhl', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'rhl' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-leaf text-base leading-none <?= $nav === 'rhl' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            RHL
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kbr', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'kbr' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-plant text-base leading-none <?= $nav === 'kbr' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            KBR
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'aep' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-tool text-base leading-none <?= $nav === 'aep' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            AEP
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/penyuluh', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'penyuluh' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-user-check text-base leading-none <?= $nav === 'penyuluh' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Penyuluh
        </a>

        <p class="px-4 pt-4 pb-1 text-[9px] font-medium text-white/30 uppercase tracking-widest">Produksi</p>

        <a href="<?= htmlspecialchars(APP_URL . '/laporan', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'laporan' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-report-analytics text-base leading-none <?= $nav === 'laporan' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Laporan HHK & HHBK
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/hhk', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'hhk' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-trees text-base leading-none <?= $nav === 'hhk' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            HHK
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/hhbk', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'hhbk' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-leaf-2 text-base leading-none <?= $nav === 'hhbk' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            HHBK
        </a>


        <p class="px-4 pt-4 pb-1 text-[9px] font-medium text-white/30 uppercase tracking-widest">Infrastruktur</p>

        <a href="<?= htmlspecialchars(APP_URL . '/dpn', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'dpn' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-wall text-base leading-none <?= $nav === 'dpn' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Dam Penahan
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/gully-plug', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'gully_plug' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-ripple text-base leading-none <?= $nav === 'gully_plug' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Gully Plug
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/peta', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'peta' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-map-pin text-base leading-none <?= $nav === 'peta' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Peta Wilayah
        </a>

        <?php if ($role === 'admin'): ?>
        <p class="px-4 pt-4 pb-1 text-[9px] font-medium text-white/30 uppercase tracking-widest">Pengaturan</p>

        <a href="<?= htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8') ?>"
           class="flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs <?= $nav === 'users' ? 'bg-white/10 text-forest-200 font-medium' : 'text-white/70 hover:bg-white/5' ?>">
            <i class="ti ti-users-group text-base leading-none <?= $nav === 'users' ? 'text-forest-400' : 'text-white/35' ?>"></i>
            Pengguna
        </a>
        <?php endif; ?>

    </nav>

    <!-- User profile -->
    <div class="mt-auto px-4 py-3 border-t border-white/10 flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-full bg-forest-600 flex items-center justify-center text-[10px] font-semibold text-forest-100"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="flex-1 min-w-0">
            <p class="text-white/80 text-xs font-medium truncate"><?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-white/40 text-[10px] truncate"><?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a href="<?= htmlspecialchars(APP_URL . '/logout', ENT_QUOTES, 'UTF-8') ?>" class="text-white/30 hover:text-white/60 transition-colors" title="Keluar">
            <i class="ti ti-logout text-base leading-none"></i>
        </a>
    </div>

</aside>
