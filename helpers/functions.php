<?php

declare(strict_types=1);

function app_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $root . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
}

function view_path(string $path): string
{
    return app_path('views/' . ltrim($path, '/\\'));
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(403);
        echo 'CSRF token tidak valid';
        exit;
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

/** @return array{type: string, msg: string}|null */
function consume_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $f;
}

function format_id(int|float $n, int $decimals = 0): string
{
    return number_format((float) $n, $decimals, ',', '.');
}

/**
 * Empty cell marker for data tables (muted em dash).
 */
function table_empty_hint(string $title = 'Kosong'): string
{
    $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    return '<span class="num-empty-hint" title="' . $t . '">—</span>';
}

/**
 * Year pill: current calendar year = green, otherwise muted gray.
 */
function table_year_badge(?int $year): string
{
    if ($year === null || $year <= 0) {
        return table_empty_hint('Tahun kosong');
    }
    $current = (int) date('Y');
    $cls = $year === $current ? 'badge-tahun badge-tahun-current' : 'badge-tahun badge-tahun-past';
    return '<span class="' . $cls . '">' . $year . '</span>';
}

/**
 * Donut chart arcs (Skema KPS). Warna harus hex aman (sumber server).
 *
 * @param list<array{jumlah:int,warna:string}> $segments
 */
function svg_donut_kps_segments(array $segments, int $total): string
{
    if ($total <= 0) {
        return '<svg width="130" height="130" viewBox="0 0 130 130"><circle fill="none" stroke="#e8f0e8" stroke-width="8" cx="65" cy="65" r="52"/></svg>';
    }

    $r = 52;
    $circ = 2 * M_PI * $r;
    $cx = 65;
    $cy = 65;
    $offset = 0.0;
    $svg = '<svg width="130" height="130" viewBox="0 0 130 130">';
    $svg .= '<circle fill="none" stroke="#e8f0e8" stroke-width="8" cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '"/>';

    foreach ($segments as $seg) {
        $j = (int) ($seg['jumlah'] ?? 0);
        if ($j <= 0) {
            continue;
        }
        $pct = $j / $total;
        $dash = $pct * $circ;
        $drawLen = max(0.01, $dash - 2);
        $gapLen = max(0.01, $circ - $drawLen);
        $rawColor = (string) ($seg['warna'] ?? '#6b7280');
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $rawColor)) {
            $rawColor = '#6b7280';
        }
        $svg .= sprintf(
            '<circle cx="%d" cy="%d" r="%d" fill="none" stroke="%s" stroke-width="8" stroke-linecap="round" stroke-dasharray="%F %F" stroke-dashoffset="%F"/>',
            $cx,
            $cy,
            $r,
            $rawColor,
            $drawLen,
            $gapLen,
            -$offset
        );
        $offset += $dash;
    }

    $svg .= '</svg>';

    return $svg;
}

function user_role(): string
{
    return (string) ($_SESSION['role'] ?? '');
}

function user_kabupaten_id(): ?int
{
    if (!isset($_SESSION['kabupaten_id']) || $_SESSION['kabupaten_id'] === null || $_SESSION['kabupaten_id'] === '') {
        return null;
    }

    return (int) $_SESSION['kabupaten_id'];
}

function require_can_mutate_data(): void
{
    if (!in_array(user_role(), ['admin', 'operator'], true)) {
        http_response_code(403);
        echo 'Akses ditolak — peran Anda hanya dapat melihat data.';
        exit;
    }
}

function log_activity(\PDO $pdo, string $modul, string $aksi, string $detail = ''): void
{
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        return;
    }
    try {
        // Hindari kegagalan FK jika user session sudah tidak ada di tabel users.
        $existsStmt = $pdo->prepare('SELECT 1 FROM users WHERE id = ? LIMIT 1');
        $existsStmt->execute([$uid]);
        if (!(bool) $existsStmt->fetchColumn()) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO activity_log (user_id, modul, aksi, detail, ip_address) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $uid,
            $modul,
            $aksi,
            $detail !== '' ? $detail : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (\PDOException $e) {
        // Logging tidak boleh menghentikan proses utama CRUD.
        return;
    }
}

/** @param scalar|null $v */
function req_str(string $key, $v = null): string
{
    if ($v !== null) {
        return trim((string) $v);
    }

    return trim((string) ($_POST[$key] ?? ''));
}

/** @param scalar|null $v */
function req_int_null(string $key, $v = null): ?int
{
    $s = $v !== null ? trim((string) $v) : trim((string) ($_POST[$key] ?? ''));
    if ($s === '') {
        return null;
    }

    return (int) $s;
}

function format_tanggal_hari_ini(): string
{
    if (class_exists(\IntlDateFormatter::class)) {
        $fmt = new \IntlDateFormatter(
            'id_ID',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'Asia/Jakarta',
            \IntlDateFormatter::GREGORIAN,
            "EEEE, d MMMM yyyy"
        );
        if ($fmt !== false) {
            $out = $fmt->format(new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')));
            if ($out !== false) {
                return $out;
            }
        }
    }

    return date('d-m-Y');
}

function request_path(): string
{
    $raw = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $raw = str_replace('\\', '/', $raw);
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = dirname($script);
    if ($dir !== '/' && $dir !== '.' && $dir !== '' && str_starts_with($raw, $dir)) {
        $raw = substr($raw, strlen($dir)) ?: '/';
    }
    $path = trim($raw, '/');

    return $path === '' ? '/' : '/' . $path;
}

require_once __DIR__ . '/bulk_export.php';
