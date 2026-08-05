<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-name" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars(APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars(($pageTitle ?? 'Masuk') . ' — ' . APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(APP_URL . '/assets/img/logo.svg', ENT_QUOTES, 'UTF-8') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            forest: {
              950: '#052e16',
              900: '#14532d',
              800: '#1A3A28',
              700: '#155730',
              600: '#1A6B3A',
              500: '#3AA870',
              200: '#bbf7d0',
              100: '#E8F5EC',
              50:  '#EEF2EE',
            },
          }
        }
      }
    }
    </script>
    <style>
      @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-15px) rotate(2deg); }
      }
      @keyframes pulse-slow {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.6; }
      }
      .float-1 { animation: float 6s ease-in-out infinite; }
      .float-2 { animation: float 8s ease-in-out infinite; animation-delay: 1s; }
      .float-3 { animation: float 7s ease-in-out infinite; animation-delay: 2s; }
      .pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
      .fade-in-up { animation: fadeInUp 0.6s ease forwards; }

      .grid-pattern {
        background-image:
          linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
          linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
        background-size: 32px 32px;
      }
    </style>
</head>
<body class="min-h-screen bg-forest-50 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex">

        <!-- Left side: Branding panel -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-forest-900 via-forest-800 to-forest-700">
            <!-- Grid pattern overlay -->
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Floating decorative elements -->
            <div class="absolute top-20 left-16 w-32 h-32 rounded-full bg-forest-500/20 blur-3xl pulse-slow"></div>
            <div class="absolute bottom-32 right-20 w-40 h-40 rounded-full bg-forest-400/20 blur-3xl pulse-slow" style="animation-delay:1.5s;"></div>
            <div class="absolute top-1/2 left-1/3 w-24 h-24 rounded-full bg-emerald-400/10 blur-2xl pulse-slow" style="animation-delay:0.5s;"></div>

            <!-- Floating leaf icons -->
            <i class="ti ti-leaf absolute text-white/10 text-7xl float-1" style="top:15%;right:18%;"></i>
            <i class="ti ti-trees absolute text-white/10 text-6xl float-2" style="top:55%;right:10%;"></i>
            <i class="ti ti-plant absolute text-white/10 text-5xl float-3" style="bottom:18%;left:22%;"></i>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-between w-full p-12 text-white">
                <!-- Top: brand mark -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center border border-white/20">
                        <svg viewBox="0 0 40 40" fill="none" class="w-7 h-7" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 8c-1.2 2-6 7-6 12a6 6 0 0 0 12 0c0-5-4.8-10-6-12z" fill="white"/>
                            <rect x="18.8" y="23" width="2.4" height="6" rx="1.2" fill="white" opacity="0.7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-base"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-white/50 text-xs">CDK Wilayah Bojonegoro</p>
                    </div>
                </div>

                <!-- Middle: hero text -->
                <div class="fade-in-up">
                    <h1 class="text-4xl font-bold leading-tight mb-4">
                        Sistem Informasi<br>
                        <span class="text-forest-200">Basis Data Kehutanan</span>
                    </h1>
                    <p class="text-white/70 text-base leading-relaxed max-w-md">
                        Platform terpadu untuk pengelolaan data Kelompok Tani Hutan, Perhutanan Sosial, dan kegiatan rehabilitasi di wilayah CDK Bojonegoro.
                    </p>

                    <!-- Stats badges -->
                    <div class="grid grid-cols-3 gap-3 mt-8 max-w-md">
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <i class="ti ti-map-pin text-forest-300 text-lg"></i>
                            <p class="text-xl font-bold mt-1">4</p>
                            <p class="text-[10px] text-white/50">Kabupaten</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <i class="ti ti-building-community text-forest-300 text-lg"></i>
                            <p class="text-xl font-bold mt-1">400+</p>
                            <p class="text-[10px] text-white/50">KTH</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                            <i class="ti ti-leaf text-forest-300 text-lg"></i>
                            <p class="text-xl font-bold mt-1">100+</p>
                            <p class="text-[10px] text-white/50">KPS</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom: footer -->
                <div class="text-white/40 text-xs">
                    <p>Dinas Kehutanan Provinsi Jawa Timur</p>
                    <p class="mt-0.5">© <?= date('Y') ?> · Versi 1.0</p>
                </div>
            </div>
        </div>

        <!-- Right side: Login form -->
        <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-sm fade-in-up">
                <?php require view_path('partials/flash.php'); ?>
                <?= $content ?>
            </div>
        </div>

    </div>
</body>
</html>
