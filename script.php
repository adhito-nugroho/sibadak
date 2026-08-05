<?php
require __DIR__ . '/config/database.php';
$pdo = Database::connect();
$stmt = $pdo->query('SHOW CREATE TABLE rhl');
echo $stmt->fetchColumn(1);
