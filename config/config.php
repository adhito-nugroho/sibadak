<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

if (is_readable($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

define('APP_NAME', $_ENV['APP_NAME'] ?? 'SIBADAK');
define(
    'APP_TAGLINE',
    $_ENV['APP_TAGLINE'] ?? 'Sistem Informasi Basis Data Kehutanan'
);
if (isset($_SERVER['HTTP_HOST'])) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        || (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) === 'on');
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = rtrim(dirname($scriptName), '/\\');
    $dynamicUrl = $protocol . '://' . $host . $basePath;
} else {
    $dynamicUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000/sibadak';
}
define('APP_URL', rtrim($dynamicUrl, '/'));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN));

date_default_timezone_set($_ENV['TIMEZONE'] ?? 'Asia/Jakarta');

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
}
