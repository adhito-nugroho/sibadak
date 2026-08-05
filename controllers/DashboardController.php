<?php

declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        requireLogin();

        $pageTitle = 'Dashboard';
        $activeNav = 'dashboard';

        // Get filter parameters
        $filters = [];
        if (!empty($_GET['kabupaten']) && $_GET['kabupaten'] !== '0') {
            $filters['kabupaten_id'] = (int) $_GET['kabupaten'];
        }
        if (!empty($_GET['tahun'])) {
            $filters['tahun'] = (int) $_GET['tahun'];
        }

        try {
            $pdo = Database::connect();
            $dashboard = (new Dashboard($pdo))->fetchAll($filters);
        } catch (\Throwable $e) {
            $dashboard = [
                'stats' => ['kth' => 0, 'kps' => 0, 'anggota' => 0, 'rhl_ha' => 0.0],
                'kth_per_kab' => [],
                'kps_per_skema' => [],
                'kth_per_kelas' => [],
                'rhl_per_kegiatan' => [],
                'kth_terbaru' => [],
                'kps_terbaru' => [],
                'error' => APP_DEBUG ? $e->getMessage() : 'Tidak dapat terhubung ke database.',
            ];
        }

        ob_start();
        require view_path('dashboard/index.php');
        $content = ob_get_clean();

        require view_path('layouts/main.php');
    }
}
