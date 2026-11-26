<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
require_once __DIR__ . '/sessions.php';
require_once __DIR__ . '/db_connection1.php';
require_once __DIR__ . '/subscription_payment_requests.php';

if (empty($_SESSION['email'])) {
    http_response_code(401);
    exit('Debe iniciar sesión.');
}

$adminEmail = (string) $_SESSION['email'];
$adminStmt = $conn->prepare(
    "SELECT active, adm_role, suscriptionkind
     FROM videotips_app_access_list
     WHERE username = ?
     LIMIT 1"
);
$adminStmt->bind_param('s', $adminEmail);
$adminStmt->execute();
$admin = $adminStmt->get_result()->fetch_assoc();
$adminStmt->close();
if (!goraCanAccessAppManagement($admin)) {
    http_response_code(403);
    exit('No tiene autorización para ver comprobantes.');
}

$proofType = (string) ($_GET['type'] ?? 'request');
$recordId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!in_array($proofType, ['request', 'sale'], true) || $recordId === false || $recordId <= 0) {
    http_response_code(400);
    exit('El comprobante indicado no es válido.');
}

if ($proofType === 'sale') {
    $stmt = $conn->prepare(
        'SELECT proof_file
         FROM gora_subscription_sales
         WHERE payment_id = ?
         LIMIT 1'
    );
} else {
    $stmt = $conn->prepare(
        'SELECT proof_file
         FROM gora_subscription_payment_requests
         WHERE request_id = ?
         LIMIT 1'
    );
}
$stmt->bind_param('i', $recordId);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$payment || empty($payment['proof_file'])) {
    http_response_code(404);
    exit('No se encontró el comprobante.');
}

$fileName = (string) $payment['proof_file'];
if (!preg_match('/\A[a-f0-9]{48}\.(pdf|jpg|png|webp)\z/D', $fileName)) {
    error_log('Invalid stored subscription payment proof filename.');
    http_response_code(500);
    exit('No fue posible abrir el comprobante.');
}
$filePath = goraPrivatePaymentProofDirectory() . DIRECTORY_SEPARATOR . $fileName;
if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit('El archivo del comprobante no está disponible.');
}

$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $fileInfo->file($filePath);
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
if (!is_string($mimeType) || !in_array($mimeType, $allowedTypes, true)) {
    error_log('Stored subscription payment proof has an unsupported content type.');
    http_response_code(500);
    exit('El archivo del comprobante no es válido.');
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . (string) filesize($filePath));
header('Content-Disposition: attachment; filename="payment-proof.' . pathinfo($fileName, PATHINFO_EXTENSION) . '"');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
readfile($filePath);
