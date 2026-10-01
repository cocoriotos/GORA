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

$_SESSION['LAST_ACTIVITY'] = time();
echo json_encode(['active' => true]);