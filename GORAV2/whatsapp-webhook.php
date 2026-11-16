<?php
require_once __DIR__ . '/whatsapp_assistant.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
if ($requestMethod === 'GET') {
    $verifyToken = getenv('GORA_WA_VERIFY_TOKEN');
    $mode = $_GET['hub.mode'] ?? $_GET['hub_mode'] ?? '';
    $providedToken = $_GET['hub.verify_token'] ?? $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub.challenge'] ?? $_GET['hub_challenge'] ?? '';

    if ($verifyToken !== false
        && $mode === 'subscribe'
        && is_string($providedToken)
        && hash_equals($verifyToken, $providedToken)
        && is_string($challenge)) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $challenge;
        exit();
    }

    http_response_code(403);
    exit('Forbidden');
}

if ($requestMethod !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Method not allowed');
}

$appSecret = getenv('GORA_WA_APP_SECRET');
if ($appSecret === false || $appSecret === '') {
    error_log('WhatsApp webhook app secret is not configured.');
    http_response_code(503);
    exit('Webhook is not configured');
}

$rawBody = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!is_string($rawBody) || !is_string($signature) || !preg_match('/\Asha256=[a-f0-9]{64}\z/i', $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}
$expectedSignature = 'sha256=' . hash_hmac('sha256', $rawBody, $appSecret);
if (!hash_equals($expectedSignature, strtolower($signature))) {
    http_response_code(401);
    exit('Invalid signature');
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    exit('Invalid payload');
}
if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
    http_response_code(200);
    exit('EVENT_RECEIVED');
}

$entries = $payload['entry'] ?? [];
if (!is_array($entries)) {
    http_response_code(400);
    exit('Invalid payload');
}

require_once __DIR__ . '/db_connection1.php';
try {
    foreach ($entries as $entry) {
        if (!is_array($entry) || !is_array($entry['changes'] ?? [])) {
            http_response_code(400);
            exit('Invalid payload');
        }
        foreach ($entry['changes'] ?? [] as $change) {
            if (!is_array($change) || !is_array($change['value'] ?? [])) {
                http_response_code(400);
                exit('Invalid payload');
            }
            if (($change['field'] ?? '') !== 'messages') {
                continue;
            }
            $messages = $change['value']['messages'] ?? [];
            if (!is_array($messages)) {
                http_response_code(400);
                exit('Invalid payload');
            }
            foreach ($messages as $message) {
                if (is_array($message)) {
                    goraWhatsappHandleIncomingMessage($conn, $message);
                }
            }
        }
    }
} catch (Throwable $exception) {
    error_log('WhatsApp webhook processing failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Webhook processing failed');
}

http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo 'EVENT_RECEIVED';
