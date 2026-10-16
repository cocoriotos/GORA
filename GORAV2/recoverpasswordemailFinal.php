<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido.');
}

$email = trim((string) ($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('La dirección de correo electrónico no es válida.');
}

include __DIR__ . '/db_connection1.php';

$transactionStarted = false;

try {
    $stmt = $conn->prepare('SELECT email FROM videotips_app_access_list WHERE email = ? AND active = 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($accountEmail);
    $accountExists = $stmt->fetch();
    $stmt->close();

    if ($accountExists) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $conn->begin_transaction();
        $transactionStarted = true;
        $deleteStmt = $conn->prepare('DELETE FROM password_reset_tokens WHERE email = ?');
        $deleteStmt->bind_param('s', $accountEmail);
        $deleteStmt->execute();
        $deleteStmt->close();

        $insertStmt = $conn->prepare('INSERT INTO password_reset_tokens (email, token_hash, expires_at) VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 HOUR))');
        $insertStmt->bind_param('ss', $accountEmail, $tokenHash);
        $insertStmt->execute();
        $insertStmt->close();
        $conn->commit();
        $transactionStarted = false;

        $resetUrl = 'https://solicionespro.com/GORAV2/resetpassword.php?token=' . rawurlencode($token);
        $subject = 'Restablecimiento de contraseña de GORA';
        $message = "Recibimos una solicitud para cambiar la contraseña de tu cuenta de GORA.\n\n";
        $message .= "Para elegir una contraseña nueva, abre este enlace dentro de la próxima hora:\n";
        $message .= $resetUrl . "\n\n";
        $message .= "Si no solicitaste este cambio, ignora este mensaje. Tu contraseña actual no ha sido enviada ni modificada.\n";
        $headers = "From: adm@solicionespro.com\r\n";
        $headers .= "Reply-To: noreply@solicionespro.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (!mail($accountEmail, $subject, $message, $headers)) {
            error_log('Password reset email delivery failed.');
        }
    }
} catch (mysqli_sql_exception $exception) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    error_log('Password reset request failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('No fue posible procesar la solicitud en este momento.');
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperación de contraseña - GORA</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: 'Solicitud recibida',
        text: 'Si el correo está asociado a una cuenta activa, recibirás un enlace para elegir una contraseña nueva. Por seguridad, nunca enviamos la contraseña actual.',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(function () {
        window.location.href = 'videotrackerauth.php';
    });
});
</script>
</body>
</html>
