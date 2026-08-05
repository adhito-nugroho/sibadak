<?php
ob_start();
?>
<!-- Mobile brand (hidden on desktop, shown on mobile) -->
<div class="lg:hidden text-center mb-8">
    <div class="w-14 h-14 rounded-2xl bg-forest-600 flex items-center justify-center mx-auto shadow-lg shadow-forest-600/30">
        <svg viewBox="0 0 40 40" fill="none" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 8c-1.2 2-6 7-6 12a6 6 0 0 0 12 0c0-5-4.8-10-6-12z" fill="white"/>
            <rect x="18.8" y="23" width="2.4" height="6" rx="1.2" fill="white" opacity="0.7"/>
        </svg>
    </div>
    <h1 class="text-xl font-bold text-gray-900 mt-4"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?></p>
</div>

<!-- Login card -->
<div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
    <div class="p-8">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Masuk ke akun Anda</h2>
            <p class="text-xs text-gray-500 mt-1">Masukkan kredensial untuk mengakses sistem</p>
        </div>

        <form method="post" action="<?= htmlspecialchars(APP_URL . '/login', ENT_QUOTES, 'UTF-8') ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

            <div>
                <label for="username" class="block text-xs font-medium text-gray-600 mb-1.5">Username</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="ti ti-user text-base leading-none"></i>
                    </span>
                    <input type="text" name="username" id="username" autocomplete="username" required
                        placeholder="Masukkan username"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-forest-500/20 focus:border-forest-500 transition-all">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="ti ti-lock text-base leading-none"></i>
                    </span>
                    <input type="password" name="password" id="password" autocomplete="current-password" required
                        placeholder="Masukkan password"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-forest-500/20 focus:border-forest-500 transition-all">
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-forest-700 hover:bg-forest-800 text-white font-medium text-sm transition-all shadow-sm hover:shadow-md hover:shadow-forest-700/20 flex items-center justify-center gap-2">
                <i class="ti ti-login text-base leading-none"></i>
                Masuk
            </button>
        </form>
    </div>

    <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
        <p class="text-[10px] text-gray-400 text-center">
            <i class="ti ti-shield-check text-forest-500 text-xs"></i>
            Sesi dilindungi enkripsi · Hubungi admin jika lupa password
        </p>
    </div>
</div>

<p class="text-center text-[10px] text-gray-400 mt-6">
    <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> v1.0 · Dinas Kehutanan Prov. Jawa Timur
</p>
<?php
$content = ob_get_clean();
$pageTitle = 'Masuk';
require view_path('layouts/auth.php');
