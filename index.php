<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

/**
 * @return array{class-string, string, list<int|string>}|null
 */
function match_route(string $method, string $path): ?array
{
    $routesGet = [
        '/' => [DashboardController::class, 'index', []],
        '/login' => [AuthController::class, 'loginForm', []],
        '/logout' => [AuthController::class, 'logout', []],
        '/kth' => [KthController::class, 'index', []],
        '/kth/create' => [KthController::class, 'create', []],
        '/kelembagaan' => [KelembagaanController::class, 'index', []],
        '/kelembagaan/create' => [KelembagaanController::class, 'create', []],
        '/kps' => [KpsController::class, 'index', []],
        '/kps/create' => [KpsController::class, 'create', []],
        '/rkt' => [RktController::class, 'index', []],
        '/rkt/create' => [RktController::class, 'create', []],
        '/rhl' => [RhlController::class, 'index', []],
        '/rhl/create' => [RhlController::class, 'create', []],
        '/kbr' => [KbrController::class, 'index', []],
        '/kbr/create' => [KbrController::class, 'create', []],
        '/aep' => [AepController::class, 'index', []],
        '/aep/create' => [AepController::class, 'create', []],
        '/penyuluh' => [PenyuluhKehutananController::class, 'index', []],
        '/penyuluh/create' => [PenyuluhKehutananController::class, 'create', []],
        '/hhk' => [HhkController::class, 'index', []],
        '/hhk/create' => [HhkController::class, 'create', []],
        '/hhbk' => [HhbkController::class, 'index', []],
        '/hhbk/create' => [HhbkController::class, 'create', []],
        '/users' => [UserController::class, 'index', []],
        '/users/create' => [UserController::class, 'create', []],
        '/peta' => [PetaController::class, 'index', []],
        '/api/peta' => [PetaController::class, 'apiData', []],
        '/dpn' => [DpnController::class, 'index', []],
        '/dpn/create' => [DpnController::class, 'create', []],
        '/gully-plug' => [GullyPlugController::class, 'index', []],
        '/gully-plug/create' => [GullyPlugController::class, 'create', []],
        '/rkps' => [RkpsController::class, 'index', []],
        '/rkps/create' => [RkpsController::class, 'create', []],
        '/nte' => [NteController::class, 'index', []],
        '/nte/import' => [NteController::class, 'importForm', []],
        '/hhk' => [HhkController::class, 'index', []],
        '/hhk/create' => [HhkController::class, 'create', []],
        '/hhbk' => [HhbkController::class, 'index', []],
        '/hhbk/create' => [HhbkController::class, 'create', []],
        '/laporan' => [LaporanController::class, 'index', []],
        '/laporan/komoditas-hhk' => [LaporanController::class, 'komoditasHhk', []],
        '/laporan/komoditas-hhbk' => [LaporanController::class, 'komoditasHhbk', []],
        '/laporan/preview' => [LaporanController::class, 'preview', []],
        '/laporan/export-excel' => [LaporanController::class, 'exportExcel', []],
        '/laporan/export-pdf' => [LaporanController::class, 'exportPdf', []],
        '/api/kecamatan' => [ApiController::class, 'kecamatan', []],
        '/api/desa' => [ApiController::class, 'desa', []],
        '/api/kth-search' => [ApiController::class, 'kthSearch', []],
        '/api/kps-search' => [ApiController::class, 'kpsSearch', []],
        '/api/pelaksana' => [ApiController::class, 'pelaksana', []],
    ];
    $routesPost = [
        '/login' => [AuthController::class, 'login', []],
        '/kth/store' => [KthController::class, 'store', []],
        '/kth/bulk' => [KthController::class, 'bulk', []],
        '/kelembagaan/store' => [KelembagaanController::class, 'store', []],
        '/kelembagaan/bulk' => [KelembagaanController::class, 'bulk', []],
        '/kps/store' => [KpsController::class, 'store', []],
        '/kps/bulk' => [KpsController::class, 'bulk', []],
        '/rkt/store' => [RktController::class, 'store', []],
        '/rkt/bulk' => [RktController::class, 'bulk', []],
        '/rhl/store' => [RhlController::class, 'store', []],
        '/rhl/bulk' => [RhlController::class, 'bulk', []],
        '/kbr/store' => [KbrController::class, 'store', []],
        '/kbr/bulk' => [KbrController::class, 'bulk', []],
        '/aep/store' => [AepController::class, 'store', []],
        '/aep/bulk' => [AepController::class, 'bulk', []],
        '/penyuluh/store' => [PenyuluhKehutananController::class, 'store', []],
        '/penyuluh/bulk' => [PenyuluhKehutananController::class, 'bulk', []],
        '/hhk/store' => [HhkController::class, 'store', []],
        '/hhk/bulk' => [HhkController::class, 'bulk', []],
        '/hhbk/store' => [HhbkController::class, 'store', []],
        '/hhbk/bulk' => [HhbkController::class, 'bulk', []],
        '/users/store' => [UserController::class, 'store', []],
        '/dpn/store' => [DpnController::class, 'store', []],
        '/dpn/bulk' => [DpnController::class, 'bulk', []],
        '/gully-plug/store' => [GullyPlugController::class, 'store', []],
        '/gully-plug/bulk' => [GullyPlugController::class, 'bulk', []],
        '/rkps/store' => [RkpsController::class, 'store', []],
        '/rkps/bulk' => [RkpsController::class, 'bulk', []],
        '/nte/import' => [NteController::class, 'importProcess', []],
        '/laporan/komoditas-hhk/save' => [LaporanController::class, 'saveKomoditasHhk', []],
        '/laporan/komoditas-hhbk/save' => [LaporanController::class, 'saveKomoditasHhbk', []],
        '/laporan/target-hhk/save' => [LaporanController::class, 'saveTargetHhk', []],
        '/laporan/target-hhbk/save' => [LaporanController::class, 'saveTargetHhbk', []],
    ];

    if ($method === 'GET' && isset($routesGet[$path])) {
        return $routesGet[$path];
    }
    if ($method === 'POST' && isset($routesPost[$path])) {
        return $routesPost[$path];
    }

    if ($method === 'GET' && preg_match('#^/kth/(\d+)$#', $path, $m)) {
        return [KthController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kth/(\d+)/edit$#', $path, $m)) {
        return [KthController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kth/(\d+)/update$#', $path, $m)) {
        return [KthController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kth/(\d+)/delete$#', $path, $m)) {
        return [KthController::class, 'delete', [(int) $m[1]]];
    }

    if ($method === 'GET' && preg_match('#^/kelembagaan/(\d+)$#', $path, $m)) {
        return [KelembagaanController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kelembagaan/(\d+)/edit$#', $path, $m)) {
        return [KelembagaanController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kelembagaan/(\d+)/update$#', $path, $m)) {
        return [KelembagaanController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kelembagaan/(\d+)/delete$#', $path, $m)) {
        return [KelembagaanController::class, 'delete', [(int) $m[1]]];
    }

    if ($method === 'GET' && preg_match('#^/kps/(\d+)$#', $path, $m)) {
        return [KpsController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kps/(\d+)/anggota$#', $path, $m)) {
        return [KpsAnggotaController::class, 'index', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kps/(\d+)/anggota/create$#', $path, $m)) {
        return [KpsAnggotaController::class, 'create', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kps/(\d+)/anggota/import$#', $path, $m)) {
        return [KpsAnggotaController::class, 'importForm', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/anggota/import/preview$#', $path, $m)) {
        return [KpsAnggotaController::class, 'importPreview', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/anggota/import/process$#', $path, $m)) {
        return [KpsAnggotaController::class, 'importProcess', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/anggota/store$#', $path, $m)) {
        return [KpsAnggotaController::class, 'store', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kps/(\d+)/anggota/(\d+)/edit$#', $path, $m)) {
        return [KpsAnggotaController::class, 'edit', [(int) $m[1], (int) $m[2]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/anggota/(\d+)/update$#', $path, $m)) {
        return [KpsAnggotaController::class, 'update', [(int) $m[1], (int) $m[2]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/anggota/(\d+)/delete$#', $path, $m)) {
        return [KpsAnggotaController::class, 'delete', [(int) $m[1], (int) $m[2]]];
    }
    if ($method === 'GET' && preg_match('#^/kps/(\d+)/edit$#', $path, $m)) {
        return [KpsController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/update$#', $path, $m)) {
        return [KpsController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kps/(\d+)/delete$#', $path, $m)) {
        return [KpsController::class, 'delete', [(int) $m[1]]];
    }

    if ($method === 'GET' && preg_match('#^/rkt/(\d+)$#', $path, $m)) {
        return [RktController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/rkt/(\d+)/edit$#', $path, $m)) {
        return [RktController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rkt/(\d+)/update$#', $path, $m)) {
        return [RktController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rkt/(\d+)/delete$#', $path, $m)) {
        return [RktController::class, 'delete', [(int) $m[1]]];
    }

    if ($method === 'GET' && preg_match('#^/rhl/(\d+)$#', $path, $m)) {
        return [RhlController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/rhl/(\d+)/edit$#', $path, $m)) {
        return [RhlController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rhl/(\d+)/update$#', $path, $m)) {
        return [RhlController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rhl/(\d+)/delete$#', $path, $m)) {
        return [RhlController::class, 'delete', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rhl/(\d+)/bibit/store$#', $path, $m)) {
        return [RhlController::class, 'storeBibit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rhl/bibit/(\d+)/delete$#', $path, $m)) {
        return [RhlController::class, 'deleteBibit', [(int) $m[1]]];
    }

    // KBR routes
    if ($method === 'GET' && preg_match('#^/kbr/(\d+)$#', $path, $m)) {
        return [KbrController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/kbr/(\d+)/edit$#', $path, $m)) {
        return [KbrController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kbr/(\d+)/update$#', $path, $m)) {
        return [KbrController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kbr/(\d+)/delete$#', $path, $m)) {
        return [KbrController::class, 'delete', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kbr/(\d+)/tanaman/store$#', $path, $m)) {
        return [KbrController::class, 'storeTanaman', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/kbr/tanaman/(\d+)/delete$#', $path, $m)) {
        return [KbrController::class, 'deleteTanaman', [(int) $m[1]]];
    }

    // AEP routes
    if ($method === 'GET' && preg_match('#^/aep/(\d+)$#', $path, $m)) {
        return [AepController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/aep/(\d+)/edit$#', $path, $m)) {
        return [AepController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/aep/(\d+)/update$#', $path, $m)) {
        return [AepController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/aep/(\d+)/delete$#', $path, $m)) {
        return [AepController::class, 'delete', [(int) $m[1]]];
    }

    // Penyuluh Kehutanan routes
    if ($method === 'GET' && preg_match('#^/penyuluh/(\d+)/edit$#', $path, $m)) {
        return [PenyuluhKehutananController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/penyuluh/(\d+)/update$#', $path, $m)) {
        return [PenyuluhKehutananController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/penyuluh/(\d+)/delete$#', $path, $m)) {
        return [PenyuluhKehutananController::class, 'delete', [(int) $m[1]]];
    }

    // HHK routes
    if ($method === 'GET' && preg_match('#^/hhk/(\d+)$#', $path, $m)) {
        return [HhkController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/hhk/(\d+)/edit$#', $path, $m)) {
        return [HhkController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/hhk/(\d+)/update$#', $path, $m)) {
        return [HhkController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/hhk/(\d+)/delete$#', $path, $m)) {
        return [HhkController::class, 'delete', [(int) $m[1]]];
    }

    // HHBK routes
    if ($method === 'GET' && preg_match('#^/hhbk/(\d+)$#', $path, $m)) {
        return [HhbkController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/hhbk/(\d+)/edit$#', $path, $m)) {
        return [HhbkController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/hhbk/(\d+)/update$#', $path, $m)) {
        return [HhbkController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/hhbk/(\d+)/delete$#', $path, $m)) {
        return [HhbkController::class, 'delete', [(int) $m[1]]];
    }

    // User management routes
    if ($method === 'GET' && preg_match('#^/users/(\d+)/edit$#', $path, $m)) {
        return [UserController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/users/(\d+)/update$#', $path, $m)) {
        return [UserController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/users/(\d+)/reset-password$#', $path, $m)) {
        return [UserController::class, 'resetPassword', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/users/(\d+)/toggle-active$#', $path, $m)) {
        return [UserController::class, 'toggleActive', [(int) $m[1]]];
    }

    // DPN routes
    if ($method === 'GET' && preg_match('#^/dpn/(\d+)$#', $path, $m)) {
        return [DpnController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/dpn/(\d+)/edit$#', $path, $m)) {
        return [DpnController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/dpn/(\d+)/update$#', $path, $m)) {
        return [DpnController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/dpn/(\d+)/delete$#', $path, $m)) {
        return [DpnController::class, 'delete', [(int) $m[1]]];
    }

    // Gully Plug routes
    if ($method === 'GET' && preg_match('#^/gully-plug/(\d+)$#', $path, $m)) {
        return [GullyPlugController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/gully-plug/(\d+)/edit$#', $path, $m)) {
        return [GullyPlugController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/gully-plug/(\d+)/update$#', $path, $m)) {
        return [GullyPlugController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/gully-plug/(\d+)/delete$#', $path, $m)) {
        return [GullyPlugController::class, 'delete', [(int) $m[1]]];
    }

    // RKPS routes
    if ($method === 'GET' && preg_match('#^/rkps/(\d+)$#', $path, $m)) {
        return [RkpsController::class, 'show', [(int) $m[1]]];
    }
    if ($method === 'GET' && preg_match('#^/rkps/(\d+)/edit$#', $path, $m)) {
        return [RkpsController::class, 'edit', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rkps/(\d+)/update$#', $path, $m)) {
        return [RkpsController::class, 'update', [(int) $m[1]]];
    }
    if ($method === 'POST' && preg_match('#^/rkps/(\d+)/delete$#', $path, $m)) {
        return [RkpsController::class, 'delete', [(int) $m[1]]];
    }

    // NTE routes
    if ($method === 'POST' && preg_match('#^/nte/(\d+)/delete$#', $path, $m)) {
        return [NteController::class, 'delete', [(int) $m[1]]];
    }

    // HHK routes
    if ($method === 'GET' && preg_match('#^/hhk/(\d+)$#', $path, $m)) { return [HhkController::class, 'show', [(int) $m[1]]]; }
    if ($method === 'GET' && preg_match('#^/hhk/(\d+)/edit$#', $path, $m)) { return [HhkController::class, 'edit', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhk/(\d+)/update$#', $path, $m)) { return [HhkController::class, 'update', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhk/(\d+)/delete$#', $path, $m)) { return [HhkController::class, 'delete', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhk/(\d+)/detail/store$#', $path, $m)) { return [HhkController::class, 'storeDetail', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhk/detail/(\d+)/delete$#', $path, $m)) { return [HhkController::class, 'deleteDetail', [(int) $m[1]]]; }

    // HHBK routes
    if ($method === 'GET' && preg_match('#^/hhbk/(\d+)$#', $path, $m)) { return [HhbkController::class, 'show', [(int) $m[1]]]; }
    if ($method === 'GET' && preg_match('#^/hhbk/(\d+)/edit$#', $path, $m)) { return [HhbkController::class, 'edit', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhbk/(\d+)/update$#', $path, $m)) { return [HhbkController::class, 'update', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhbk/(\d+)/delete$#', $path, $m)) { return [HhbkController::class, 'delete', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhbk/(\d+)/detail/store$#', $path, $m)) { return [HhbkController::class, 'storeDetail', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/hhbk/detail/(\d+)/delete$#', $path, $m)) { return [HhbkController::class, 'deleteDetail', [(int) $m[1]]]; }

    // Laporan routes
    if ($method === 'POST' && preg_match('#^/laporan/komoditas-hhk/(\d+)/toggle$#', $path, $m)) { return [LaporanController::class, 'toggleKomoditasHhk', [(int) $m[1]]]; }
    if ($method === 'POST' && preg_match('#^/laporan/komoditas-hhbk/(\d+)/toggle$#', $path, $m)) { return [LaporanController::class, 'toggleKomoditasHhbk', [(int) $m[1]]]; }

    return null;
}

function is_public_route(string $method, string $path): bool
{
    if (($method === 'GET' || $method === 'POST') && $path === '/login') {
        return true;
    }

    return false;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = request_path();

if (!str_starts_with($path, '/api/')) {
    header('Content-Type: text/html; charset=utf-8');
}

if (!is_public_route($method, $path)) {
    requireLogin();
}

$handler = match_route($method, $path);

if ($handler === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>404</title></head><body>';
    echo '<p>Halaman tidak ditemukan.</p><p><a href="' . htmlspecialchars(APP_URL . '/', ENT_QUOTES, 'UTF-8') . '">Beranda</a></p>';
    echo '</body></html>';
    exit;
}

[$class, $action, $args] = $handler;

$controller = new $class();
$controller->$action(...$args);
