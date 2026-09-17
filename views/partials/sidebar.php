<?php
$nav = $activeNav ?? '';
$u = $_SESSION['nama'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? 'viewer';
$initial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($u, 0, 1))
    : strtoupper(substr($u, 0, 1) ?: 'U');

$linkClass = static function (string $key, string $nav): string {
    $base = 'nav-link flex items-center gap-2.5 mx-1.5 px-3 py-1.5 rounded-lg text-xs transition-colors';
    return $nav === $key
        ? $base . ' is-active bg-white/12 text-white font-medium'
        : $base . ' text-white/65 hover:bg-white/5 hover:text-white/90';
};
$iconClass = static function (string $key, string $nav): string {
    return 'ti text-base leading-none flex-shrink-0 ' . ($nav === $key ? 'text-forest-400' : 'text-white/35');
};
?>
<aside id="app-sidebar" class="app-sidebar bg-forest-800 min-h-screen flex flex-col fixed left-0 top-0 bottom-0 z-30">

    <!-- Brand -->
    <div class="px-3 py-4 border-b border-white/10">
        <div class="brand-row flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-forest-500 flex items-center justify-center flex-shrink-0 overflow-hidden">
                <svg viewBox="0 0 40 40" fill="none" class="w-9 h-9" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="40" height="40" rx="10" fill="#22c55e"/>
                    <path d="M20 8c-1.2 2-6 7-6 12a6 6 0 0 0 12 0c0-5-4.8-10-6-12z" fill="white" opacity="0.92"/>
                    <rect x="18.8" y="23" width="2.4" height="6" rx="1.2" fill="white" opacity="0.7"/>
                    <circle cx="16.5" cy="15.5" r="1.8" fill="white" opacity="0.35"/>
                    <circle cx="23.5" cy="17" r="1.3" fill="white" opacity="0.25"/>
                </svg>
            </div>
            <div class="brand-text min-w-0">
                <p class="text-white font-semibold text-sm leading-tight"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-white/40 text-[10px] leading-snug mt-0.5">CDK Wil. Bojonegoro</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto sidebar-nav py-2">

        <p class="nav-group-label px-4 pb-1.5 text-[9px] font-medium text-white/30 uppercase tracking-widest">Menu Utama</p>

        <a href="<?= htmlspecialchars(APP_URL . '/', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('dashboard', $nav) ?>" title="Dashboard">
            <i class="<?= $iconClass('dashboard', $nav) ?> ti-layout-dashboard"></i>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('kth', $nav) ?>" title="Data KTH">
            <i class="<?= $iconClass('kth', $nav) ?> ti-trees"></i>
            <span class="nav-label">Data KTH</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kelembagaan', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('kelembagaan', $nav) ?>" title="Kelembagaan">
            <i class="<?= $iconClass('kelembagaan', $nav) ?> ti-users-group"></i>
            <span class="nav-label">Kelembagaan</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('kps', $nav) ?>" title="Data KPS">
            <i class="<?= $iconClass('kps', $nav) ?> ti-map"></i>
            <span class="nav-label">Data KPS</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/rkt', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('rkt', $nav) ?>" title="RKT per KPS">
            <i class="<?= $iconClass('rkt', $nav) ?> ti-calendar-event"></i>
            <span class="nav-label">RKT per KPS</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/rkps', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('rkps', $nav) ?>" title="RKPS">
            <i class="<?= $iconClass('rkps', $nav) ?> ti-notebook"></i>
            <span class="nav-label">RKPS</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/rhl', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('rhl', $nav) ?>" title="RHL">
            <i class="<?= $iconClass('rhl', $nav) ?> ti-seedling"></i>
            <span class="nav-label">RHL</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/kbr', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('kbr', $nav) ?>" title="KBR">
            <i class="<?= $iconClass('kbr', $nav) ?> ti-plant-2"></i>
            <span class="nav-label">KBR</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/aep', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('aep', $nav) ?>" title="AEP">
            <i class="<?= $iconClass('aep', $nav) ?> ti-shovel"></i>
            <span class="nav-label">AEP</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/penyuluh', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('penyuluh', $nav) ?>" title="Penyuluh">
            <i class="<?= $iconClass('penyuluh', $nav) ?> ti-user-heart"></i>
            <span class="nav-label">Penyuluh</span>
        </a>

        <p class="nav-group-label px-4 pb-1.5 text-[9px] font-medium text-white/30 uppercase tracking-widest">Produksi</p>

        <a href="<?= htmlspecialchars(APP_URL . '/laporan', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('laporan', $nav) ?>" title="Laporan HHK & HHBK">
            <i class="<?= $iconClass('laporan', $nav) ?> ti-report-analytics"></i>
            <span class="nav-label">Laporan HHK & HHBK</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/hhk', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('hhk', $nav) ?>" title="HHK">
            <i class="<?= $iconClass('hhk', $nav) ?> ti-tree"></i>
            <span class="nav-label">HHK</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/hhbk', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('hhbk', $nav) ?>" title="HHBK">
            <i class="<?= $iconClass('hhbk', $nav) ?> ti-leaf"></i>
            <span class="nav-label">HHBK</span>
        </a>

        <p class="nav-group-label px-4 pb-1.5 text-[9px] font-medium text-white/30 uppercase tracking-widest">Infrastruktur</p>

        <a href="<?= htmlspecialchars(APP_URL . '/dpn', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('dpn', $nav) ?>" title="Dam Penahan">
            <i class="<?= $iconClass('dpn', $nav) ?> ti-building-bridge"></i>
            <span class="nav-label">Dam Penahan</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/gully-plug', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('gully_plug', $nav) ?>" title="Gully Plug">
            <i class="<?= $iconClass('gully_plug', $nav) ?> ti-ripple"></i>
            <span class="nav-label">Gully Plug</span>
        </a>

        <a href="<?= htmlspecialchars(APP_URL . '/peta', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('peta', $nav) ?>" title="Peta Wilayah">
            <i class="<?= $iconClass('peta', $nav) ?> ti-map-2"></i>
            <span class="nav-label">Peta Wilayah</span>
        </a>

        <?php if ($role === 'admin'): ?>
        <p class="nav-group-label px-4 pb-1.5 text-[9px] font-medium text-white/30 uppercase tracking-widest">Pengaturan</p>

        <a href="<?= htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8') ?>"
           class="<?= $linkClass('users', $nav) ?>" title="Pengguna">
            <i class="<?= $iconClass('users', $nav) ?> ti-shield-lock"></i>
            <span class="nav-label">Pengguna</span>
        </a>
        <?php endif; ?>

    </nav>

    <!-- User profile -->
    <div class="mt-auto px-3 py-3 border-t border-white/10 user-row flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-full bg-forest-600 flex items-center justify-center text-[10px] font-semibold text-forest-100 flex-shrink-0"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="user-meta flex-1 min-w-0">
            <p class="text-white/80 text-xs font-medium truncate"><?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-white/40 text-[10px] truncate"><?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a href="<?= htmlspecialchars(APP_URL . '/logout', ENT_QUOTES, 'UTF-8') ?>" class="logout-btn text-white/30 hover:text-white/60 transition-colors" title="Keluar">
            <i class="ti ti-logout text-base leading-none"></i>
        </a>
    </div>

</aside>
