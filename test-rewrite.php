<?php
echo '<h2>Server Diagnostic</h2>';
echo '<pre>';
echo 'PHP Version: ' . PHP_VERSION . "\n";
echo 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo 'SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo 'DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo 'SERVER_SOFTWARE: ' . ($_SERVER['SERVER_SOFTWARE'] ?? '') . "\n";

if (function_exists('apache_get_modules')) {
    $mods = apache_get_modules();
    echo "\nmod_rewrite: " . (in_array('mod_rewrite', $mods, true) ? 'AKTIF ✓' : 'TIDAK AKTIF ✗') . "\n";
} else {
    echo "\napache_get_modules() tidak tersedia (mungkin running di FastCGI/PHP-FPM)\n";
}
echo '</pre>';

echo '<h3>Test Links:</h3>';
echo '<a href="/sibadak/test-rewrite.php?test=1">Direct PHP Access</a><br>';
echo '<a href="/sibadak/login">Test Rewrite to /login</a><br>';
echo '<a href="/sibadak/index.php">Direct index.php</a><br>';
