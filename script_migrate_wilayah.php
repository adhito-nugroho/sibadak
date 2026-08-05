<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']),
    $_ENV['DB_USER'], $_ENV['DB_PASS'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

echo "=== MIGRASI WILAYAH KEMENDAGRI → SIBADAK ===\n\n";

// Mapping
$kabMapping = ['BJN' => '35.22', 'TBN' => '35.23', 'LMG' => '35.24', 'GRK' => '35.25'];

// Step 1: Parse file
echo "1. Parsing wilayah.sql...\n";
$content = file_get_contents(__DIR__ . '/wilayah/wilayah.sql');
preg_match_all("/\('(35\.2[2-5]\.[^']+)','([^']+)'\)/", $content, $matches, PREG_SET_ORDER);

$kecSrc = []; // kode => nama
$desSrc = []; // kode => nama
foreach ($matches as $m) {
    $kode = $m[1];
    $nama = $m[2];
    $dots = substr_count($kode, '.');
    if ($dots === 2) $kecSrc[$kode] = $nama;
    elseif ($dots === 3) $desSrc[$kode] = $nama;
}
echo "   Kecamatan: " . count($kecSrc) . ", Desa: " . count($desSrc) . "\n\n";

// Step 2: Add columns
echo "2. Kolom kode_kemendagri...\n";
foreach (['kabupaten' => 5, 'kecamatan' => 8, 'desa' => 13] as $tbl => $len) {
    $cols = $pdo->query("SHOW COLUMNS FROM $tbl LIKE 'kode_kemendagri'")->fetchAll();
    if (empty($cols)) $pdo->exec("ALTER TABLE $tbl ADD COLUMN kode_kemendagri VARCHAR($len) DEFAULT NULL");
}
echo "   OK\n\n";

// Step 3: Update kabupaten kode
echo "3. Update kabupaten...\n";
foreach ($kabMapping as $kode => $kmd) {
    $pdo->prepare("UPDATE kabupaten SET kode_kemendagri = ? WHERE kode = ?")->execute([$kmd, $kode]);
}

// Get kab IDs
$kabIds = []; // '35.22' => 1
foreach ($pdo->query("SELECT id, kode_kemendagri FROM kabupaten WHERE kode_kemendagri IS NOT NULL")->fetchAll() as $r) {
    $kabIds[$r['kode_kemendagri']] = (int) $r['id'];
}
echo "   OK\n\n";

// Step 4: Sync kecamatan
echo "4. Sync kecamatan...\n";
$existing = [];
foreach ($pdo->query("SELECT id, kabupaten_id, LOWER(TRIM(nama)) AS lnama FROM kecamatan")->fetchAll() as $r) {
    $existing[$r['kabupaten_id'] . '|' . $r['lnama']] = (int) $r['id'];
}

$updated = 0; $inserted = 0;
foreach ($kecSrc as $kode => $nama) {
    $kabKmd = substr($kode, 0, 5); // 35.22
    $kabId = $kabIds[$kabKmd] ?? null;
    if (!$kabId) continue;

    $key = $kabId . '|' . mb_strtolower(trim($nama));
    if (isset($existing[$key])) {
        $pdo->prepare("UPDATE kecamatan SET kode_kemendagri = ? WHERE id = ?")->execute([$kode, $existing[$key]]);
        $updated++;
    } else {
        $pdo->prepare("INSERT INTO kecamatan (kabupaten_id, nama, kode_kemendagri) VALUES (?, ?, ?)")->execute([$kabId, $nama, $kode]);
        $inserted++;
    }
}
echo "   Matched: $updated, New: $inserted\n\n";

// Rebuild kec lookup
$kecIds = [];
foreach ($pdo->query("SELECT id, kode_kemendagri FROM kecamatan WHERE kode_kemendagri IS NOT NULL")->fetchAll() as $r) {
    $kecIds[$r['kode_kemendagri']] = (int) $r['id'];
}

// Step 5: Sync desa
echo "5. Sync desa...\n";
$existingDesa = [];
foreach ($pdo->query("SELECT id, kecamatan_id, LOWER(TRIM(nama)) AS lnama FROM desa")->fetchAll() as $r) {
    $existingDesa[$r['kecamatan_id'] . '|' . $r['lnama']] = (int) $r['id'];
}

$updated = 0; $inserted = 0;
foreach ($desSrc as $kode => $nama) {
    $kecKmd = substr($kode, 0, 8); // 35.22.01
    $kecId = $kecIds[$kecKmd] ?? null;
    if (!$kecId) continue;

    $key = $kecId . '|' . mb_strtolower(trim($nama));
    if (isset($existingDesa[$key])) {
        $pdo->prepare("UPDATE desa SET kode_kemendagri = ? WHERE id = ?")->execute([$kode, $existingDesa[$key]]);
        $updated++;
    } else {
        $pdo->prepare("INSERT INTO desa (kecamatan_id, nama, kode_kemendagri) VALUES (?, ?, ?)")->execute([$kecId, $nama, $kode]);
        $inserted++;
    }
}
echo "   Matched: $updated, New: $inserted\n\n";

echo "=== SELESAI ===\n";
$total = $pdo->query("SELECT COUNT(*) FROM kecamatan")->fetchColumn();
$totalD = $pdo->query("SELECT COUNT(*) FROM desa")->fetchColumn();
echo "Total kecamatan sekarang: $total\n";
echo "Total desa sekarang: $totalD\n";
