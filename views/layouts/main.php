<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-name" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars(APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars(($pageTitle ?? 'Dashboard') . ' — ' . APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(APP_URL . '/assets/img/logo.svg', ENT_QUOTES, 'UTF-8') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(APP_URL . '/assets/css/tokens.css', ENT_QUOTES, 'UTF-8') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
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
    <?php require view_path('partials/app-styles.php'); ?>
</head>
<body class="min-h-screen flex text-gray-900 bg-forest-50 font-sans antialiased">
    <?php require view_path('partials/sidebar.php'); ?>
    <script>
        window.APP_URL = <?= json_encode(APP_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>
    <main class="ml-56 flex-1 flex flex-col min-h-screen">
        <?php require view_path('partials/topbar.php'); ?>
        <div class="flex-1 p-6 space-y-5">
            <?php require view_path('partials/flash.php'); ?>
            <?= $content ?>
        </div>
    </main>
</body>
</html>
