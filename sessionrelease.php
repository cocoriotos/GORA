<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_cache_limiter('nocache');
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (($_POST['release'] ?? '') !== '1') {
    http_response_code(400);
    exit;
}

if (empty($_SESSION['email'])) {
    http_response_code(401);
    exit;
}

require_once __DIR__ . '/session_guard.php';
require_once __DIR__ . '/db_connection1.php';

goraReleaseActiveSession($conn, (string) $_SESSION['email'], session_id());
http_response_code(204);
