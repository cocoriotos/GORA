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
$language = (string) ($_POST['language'] ?? 'es');
if (!in_array($language, ['es', 'en', 'pt'], true)) {
    $language = 'es';
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

        $resetUrl = 'https://solicionespro.com/resetpassword.php?token=' . rawurlencode($token) . '&lang=' . rawurlencode($language);
        $emailCopy = [
            'es' => [
                'subject' => 'Restablecimiento de contraseña de GORA',
                'body' => "Recibimos una solicitud para cambiar la contraseña de tu cuenta de GORA.\n\nPara elegir una contraseña nueva, abre este enlace dentro de la próxima hora:\n%s\n\nSi no solicitaste este cambio, ignora este mensaje. Tu contraseña actual no ha sido enviada ni modificada.\n"
            ],
            'en' => [
                'subject' => 'GORA password reset',
                'body' => "We received a request to change your GORA account password.\n\nTo choose a new password, open this link within the next hour:\n%s\n\nIf you did not request this change, ignore this email. Your current password has not been sent or changed.\n"
            ],
            'pt' => [
                'subject' => 'Redefinição de senha do GORA',
                'body' => "Recebemos uma solicitação para alterar a senha da sua conta GORA.\n\nPara escolher uma nova senha, acesse este link dentro de uma hora:\n%s\n\nSe você não solicitou essa alteração, ignore este email. Sua senha atual não foi enviada nem alterada.\n"
            ]
        ];
        $subject = $emailCopy[$language]['subject'];
        $message = sprintf($emailCopy[$language]['body'], $resetUrl);
        $headers = "From: adm@solicionespro.com\r\n";
        $headers .= "Reply-To: noreply@solicionespro.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (!mail($accountEmail, $subject, $message, $headers)) {
            error_log('Password reset email delivery failed.');
            $deleteStmt = $conn->prepare('DELETE FROM password_reset_tokens WHERE token_hash = ?');
            $deleteStmt->bind_param('s', $tokenHash);
            $deleteStmt->execute();
            $deleteStmt->close();
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
    const language = <?php echo json_encode($language, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const messages = {
        es: ['Solicitud recibida', 'Si el correo está asociado a una cuenta activa, recibirás un enlace para elegir una contraseña nueva. Por seguridad, nunca enviamos la contraseña actual.', 'Aceptar'],
        en: ['Request received', 'If this email belongs to an active account, you will receive a link to choose a new password. For your security, we never send your current password.', 'OK'],
        pt: ['Solicitação recebida', 'Se este email estiver associado a uma conta ativa, você receberá um link para escolher uma nova senha. Por segurança, nunca enviamos sua senha atual.', 'OK']
    }[language];
    Swal.fire({
        title: messages[0],
        text: messages[1],
        icon: 'success',
        confirmButtonText: messages[2]
    }).then(function () {
        window.location.href = 'videotrackerauth.php';
    });
});
</script>
</body>
</html>
