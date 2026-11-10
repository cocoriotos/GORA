<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
require_once __DIR__ . '/sessionvalidation.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

$manuals = [
    'es' => ['file' => 'GORAManualUsuarioES.pdf', 'name' => 'GORAManualUsuarioES.pdf'],
    'en' => ['file' => 'GORAUserManualEN.pdf', 'name' => 'GORAUserManualEN.pdf'],
    'pt' => ['file' => 'GORAManualUsuarioPT.pdf', 'name' => 'GORAManualUsuarioPT.pdf'],
];
$language = (string) ($_GET['lang'] ?? '');

if (!isset($manuals[$language])) {
    http_response_code(404);
    exit('Manual not found.');
}

$manualPath = __DIR__ . DIRECTORY_SEPARATOR . $manuals[$language]['file'];
if (!is_file($manualPath) || !is_readable($manualPath)) {
    error_log('Requested GORA manual is missing or unreadable: ' . $manuals[$language]['file']);
    http_response_code(404);
    exit('Manual not found.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $manuals[$language]['name'] . '"');
header('Content-Length: ' . (string) filesize($manualPath));
readfile($manualPath);
exit;
