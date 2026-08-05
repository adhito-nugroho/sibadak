<?php

declare(strict_types=1);

// Load database connection from existing project config
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

function setPublicHeaders(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: https://cdkbojonegoro.my.id');
    header('Access-Control-Allow-Methods: GET');
    header('Cache-Control: public, max-age=300');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Method not allowed', 405);
    }
}

function sendJson(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sendError(string $message, int $status = 500): void
{
    http_response_code($status);
    echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
