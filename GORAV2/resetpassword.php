<?php

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');

$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$tokenIsWellFormed = preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;
$showForm = false;
$resetComplete = false;
$errorMessage = '';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$newPassword = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if ($isPost && !$tokenIsWellFormed) {
    $errorMessage = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
} elseif ($isPost && strlen($newPassword) < 8) {
    $errorMessage = 'La contraseña debe tener al menos 8 caracteres.';
    $showForm = true;
} elseif ($isPost && !hash_equals($newPassword, $confirmPassword)) {
    $errorMessage = 'Las contraseñas no coinciden.';
    $showForm = true;
} elseif ($tokenIsWellFormed) {
    $tokenHash = hash('sha256', $token);
    $transactionStarted = false;

    try {
        include __DIR__ . '/db_connection1.php';

        if ($isPost) {
            $conn->begin_transaction();
            $transactionStarted = true;
            $stmt = $conn->prepare('SELECT email FROM password_reset_tokens WHERE token_hash = ? AND expires_at > UTC_TIMESTAMP() FOR UPDATE');
            $stmt->bind_param('s', $tokenHash);
            $stmt->execute();
            $stmt->bind_result($accountEmail);
            $tokenIsValid = $stmt->fetch();
            $stmt->close();

            if (!$tokenIsValid) {
                $conn->rollback();
                $transactionStarted = false;
                $errorMessage = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare('UPDATE videotips_app_access_list SET password = ? WHERE email = ? AND active = 1');
                $updateStmt->bind_param('ss', $passwordHash, $accountEmail);
                $updateStmt->execute();
                $accountUpdated = $updateStmt->affected_rows === 1;
                $updateStmt->close();

                if (!$accountUpdated) {
                    $conn->rollback();
                    $transactionStarted = false;
                    $errorMessage = 'No fue posible actualizar la cuenta. Solicita un enlace nuevo.';
                } else {
                    $cleanupStmt = $conn->prepare("UPDATE videotips_accessrequests SET password = '' WHERE email = ?");
                    $cleanupStmt->bind_param('s', $accountEmail);
                    $cleanupStmt->execute();
                    $cleanupStmt->close();

                    $deleteStmt = $conn->prepare('DELETE FROM password_reset_tokens WHERE email = ?');
                    $deleteStmt->bind_param('s', $accountEmail);
                    $deleteStmt->execute();
                    $deleteStmt->close();

                    $conn->commit();
                    $transactionStarted = false;
                    $resetComplete = true;
                }
            }
        } else {
            $stmt = $conn->prepare('SELECT 1 FROM password_reset_tokens WHERE token_hash = ? AND expires_at > UTC_TIMESTAMP()');
            $stmt->bind_param('s', $tokenHash);
            $stmt->execute();
            $stmt->bind_result($validToken);
            $showForm = $stmt->fetch();
            $stmt->close();

            if (!$showForm) {
                $errorMessage = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
            }
        }
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        error_log('Password reset failed: ' . $exception->getMessage());
        http_response_code(503);
        $errorMessage = 'El servicio no está disponible temporalmente. Intenta nuevamente más tarde.';
    }
}

if (!$isPost && !$tokenIsWellFormed) {
    $errorMessage = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Restablecer contraseña - GORA</title>
    <link rel="stylesheet" href="style_sheet_auth.css">
</head>
<body id="bodyadminmodule">
<main class="login-container">
    <h1>Restablecer contraseña</h1>
    <?php if ($resetComplete): ?>
        <p>Tu contraseña fue actualizada. Ya puedes iniciar sesión con la nueva contraseña.</p>
        <a class="btn-login" href="videotrackerauth.php">Ir al inicio de sesión</a>
    <?php else: ?>
        <?php if ($errorMessage !== ''): ?>
            <p role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($showForm): ?>
            <form method="POST" action="resetpassword.php" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="password">Nueva contraseña</label>
                <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                <label for="confirm_password">Confirmar nueva contraseña</label>
                <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                <button class="btn-login" type="submit">Guardar nueva contraseña</button>
            </form>
        <?php else: ?>
            <a class="btn-login" href="recoverpassword.php">Solicitar otro enlace</a>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
