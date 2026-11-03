<?php
include "sessions.php";

if (isset($_GET['timeout'])) {
    header('Location: videotrackerauth.php?timeout=1');
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['active' => false]);
    exit;
}

$sessionGeneration = (string) ($_SESSION['gora_session_generation'] ?? '');
if ($sessionGeneration === '') {
    $sessionGeneration = bin2hex(random_bytes(32));
    $_SESSION['gora_session_generation'] = $sessionGeneration;
}

$pageGeneration = (string) ($_SERVER['HTTP_X_GORA_SESSION_GENERATION'] ?? '');
if ($pageGeneration !== '' && !hash_equals($sessionGeneration, $pageGeneration)) {
    http_response_code(409);
    echo json_encode(['active' => false, 'error' => 'session_replaced']);
    exit;
}

echo json_encode(['active' => true, 'generation' => $sessionGeneration]);