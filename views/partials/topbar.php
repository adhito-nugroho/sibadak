<header class="flex items-center justify-between px-6 pt-5 pb-0">
    <div>
        <h1 class="text-gray-900 font-semibold text-lg leading-tight"><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-gray-500 text-xs mt-0.5"><?= htmlspecialchars(format_tanggal_hari_ini(), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 bg-white border border-green-100 rounded-lg px-3 py-1.5 text-xs text-gray-400 w-56">
            <i class="ti ti-search text-sm leading-none"></i>
            <span>Cari KTH, desa, kecamatan…</span>
        </div>
        <button type="button" class="relative w-8 h-8 bg-white border border-green-100 rounded-lg flex items-center justify-center" disabled>
            <i class="ti ti-bell text-base text-gray-500 leading-none"></i>
            <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>
        <button type="button" class="flex items-center gap-1.5 bg-forest-800 hover:bg-forest-700 text-white text-xs font-medium px-3.5 py-2 rounded-lg transition-colors" disabled>
            <i class="ti ti-download text-sm leading-none"></i>
            Export
        </button>
    </div>
</header>
