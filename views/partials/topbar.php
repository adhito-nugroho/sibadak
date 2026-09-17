<header class="flex items-center justify-between px-6 pt-4 pb-0 gap-3">
    <div class="min-w-0">
        <h1 class="text-gray-900 font-semibold text-lg leading-tight truncate"><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-gray-500 text-xs mt-0.5"><?= htmlspecialchars(format_tanggal_hari_ini(), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="flex items-center gap-1.5 flex-shrink-0">
        <?php
        $gsScope = $activeNav ?? 'kth';
        $gsScopes = [
            'kth' => 'KTH',
            'kps' => 'KPS',
            'rhl' => 'RHL',
            'kbr' => 'KBR',
            'aep' => 'AEP',
        ];
        if (!isset($gsScopes[$gsScope])) {
            $gsScope = 'kth';
        }
        $gsAction = APP_URL . '/' . $gsScope;
        ?>
        <form method="get" action="<?= htmlspecialchars($gsAction, ENT_QUOTES, 'UTF-8') ?>" class="global-search hidden md:flex" id="global-search-form" title="Pencarian lintas modul">
            <i class="ti ti-world-search gs-icon" aria-hidden="true"></i>
            <select id="global-search-scope" aria-label="Lingkup pencarian">
                <?php foreach ($gsScopes as $key => $label): ?>
                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $gsScope === $key ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <input type="search" name="q" value="" placeholder="Cari lintas modul…" aria-label="Pencarian lintas modul" autocomplete="off">
        </form>
        <button type="button" id="sidebar-toggle" class="w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-500 hover:bg-forest-50 hover:text-forest-700 hover:border-forest-200 transition-colors" title="Ciutkan sidebar" aria-label="Ciutkan / perluas sidebar" aria-expanded="true">
            <i class="ti ti-layout-sidebar-left-collapse text-base leading-none"></i>
        </button>
        <button type="button" class="relative w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-400" disabled title="Notifikasi (segera)">
            <i class="ti ti-bell text-base leading-none"></i>
        </button>
    </div>
</header>
