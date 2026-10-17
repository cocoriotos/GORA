<?php

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');

$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$tokenIsWellFormed = preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;
$language = (string) ($_POST['language'] ?? $_GET['lang'] ?? 'es');
if (!in_array($language, ['es', 'en', 'pt'], true)) {
    $language = 'es';
}
$showForm = false;
$resetComplete = false;
$errorMessage = '';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$newPassword = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if ($isPost && !$tokenIsWellFormed) {
    $errorMessage = 'invalid';
} elseif ($isPost && strlen($newPassword) < 8) {
    $errorMessage = 'shortPassword';
    $showForm = true;
} elseif ($isPost && !hash_equals($newPassword, $confirmPassword)) {
    $errorMessage = 'passwordMismatch';
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
                $errorMessage = 'invalid';
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
                    $errorMessage = 'accountUpdateFailed';
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
                $errorMessage = 'invalid';
            }
        }
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        error_log('Password reset failed: ' . $exception->getMessage());
        http_response_code(503);
        $errorMessage = 'serviceUnavailable';
    }
}

if (!$isPost && !$tokenIsWellFormed) {
    $errorMessage = 'invalid';
}

?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title data-i18n="pageTitle">Restablecer contraseña - GORA</title>
    <link rel="icon" href="GORA.ico" type="image/x-icon">
    <link rel="stylesheet" href="style_sheet_auth.css">
    <style>
        .reset-message {
            margin: 0 0 18px;
            overflow-wrap: anywhere;
        }

        .reset-form label,
        .reset-form input,
        .reset-form button,
        .reset-actions a {
            display: block;
            width: 100%;
        }

        .reset-form label {
            margin: 12px 0 6px;
            text-align: left;
        }

        .reset-form input {
            min-height: 48px;
            margin: 0 0 14px;
            padding: 12px 14px;
            border: 1px solid var(--b2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.06);
            color: var(--t1);
            font-size: 1rem;
        }

        .reset-form .btn-login,
        .reset-actions .btn-login {
            margin-top: 14px;
            text-align: center;
            text-decoration: none;
        }

        .reset-actions {
            display: grid;
            gap: 12px;
        }
    </style>
</head>
<body id="bodyadminmodule">
<main class="login-container">
    <header class="login-header">
        <img src="GORA.ico" alt="GORA Logo" class="logo">
        <h1 data-i18n="heading">Restablecer contraseña</h1>
        <?php include __DIR__ . '/social_links.php'; ?>
    </header>

    <nav class="reset-language lang-sw" aria-label="Language selection">
        <button class="lb" type="button" data-set-language="es" aria-pressed="false">ES</button>
        <button class="lb" type="button" data-set-language="en" aria-pressed="false">EN</button>
        <button class="lb" type="button" data-set-language="pt" aria-pressed="false">PT</button>
    </nav>

    <?php if ($resetComplete): ?>
        <p class="reset-message" role="status" data-i18n="success">Tu contraseña fue actualizada. Ya puedes iniciar sesión con la nueva contraseña.</p>
        <div class="reset-actions">
            <a class="btn-login" href="videotrackerauth.php" data-i18n="login">Ir al inicio de sesión</a>
        </div>
    <?php else: ?>
        <?php if ($errorMessage !== ''): ?>
            <p class="reset-message" role="alert" data-i18n="<?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>"></p>
        <?php endif; ?>
        <?php if ($showForm): ?>
            <form class="reset-form" method="POST" action="resetpassword.php" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="language" id="reset-language" value="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="password" data-i18n="newPassword">Nueva contraseña</label>
                <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                <label for="confirm_password" data-i18n="confirmPassword">Confirmar nueva contraseña</label>
                <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                <button class="btn-login" type="submit" data-i18n="savePassword">Guardar nueva contraseña</button>
            </form>
        <?php else: ?>
            <div class="reset-actions">
                <a class="btn-login" href="recoverpassword.php" data-i18n="requestAnother">Solicitar otro enlace</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        const resetTranslations = {
            es: {
                pageTitle: 'Restablecer contraseña - GORA',
                heading: 'Restablecer contraseña',
                success: 'Tu contraseña fue actualizada. Ya puedes iniciar sesión con la nueva contraseña.',
                login: 'Ir al inicio de sesión',
                invalid: 'El enlace no es válido o ya venció. Solicita uno nuevo.',
                shortPassword: 'La contraseña debe tener al menos 8 caracteres.',
                passwordMismatch: 'Las contraseñas no coinciden.',
                accountUpdateFailed: 'No fue posible actualizar la cuenta. Solicita un enlace nuevo.',
                serviceUnavailable: 'El servicio no está disponible temporalmente. Intenta nuevamente más tarde.',
                newPassword: 'Nueva contraseña',
                confirmPassword: 'Confirmar nueva contraseña',
                savePassword: 'Guardar nueva contraseña',
                requestAnother: 'Solicitar otro enlace'
            },
            en: {
                pageTitle: 'Reset password - GORA',
                heading: 'Reset password',
                success: 'Your password has been updated. You can now sign in with your new password.',
                login: 'Go to sign in',
                invalid: 'This link is invalid or has expired. Request a new one.',
                shortPassword: 'The password must be at least 8 characters long.',
                passwordMismatch: 'The passwords do not match.',
                accountUpdateFailed: 'The account could not be updated. Request a new link.',
                serviceUnavailable: 'The service is temporarily unavailable. Please try again later.',
                newPassword: 'New password',
                confirmPassword: 'Confirm new password',
                savePassword: 'Save new password',
                requestAnother: 'Request another link'
            },
            pt: {
                pageTitle: 'Redefinir senha - GORA',
                heading: 'Redefinir senha',
                success: 'Sua senha foi atualizada. Agora você pode entrar com a nova senha.',
                login: 'Ir para o login',
                invalid: 'Este link é inválido ou expirou. Solicite um novo.',
                shortPassword: 'A senha deve ter pelo menos 8 caracteres.',
                passwordMismatch: 'As senhas não coincidem.',
                accountUpdateFailed: 'Não foi possível atualizar a conta. Solicite um novo link.',
                serviceUnavailable: 'O serviço está temporariamente indisponível. Tente novamente mais tarde.',
                newPassword: 'Nova senha',
                confirmPassword: 'Confirmar nova senha',
                savePassword: 'Salvar nova senha',
                requestAnother: 'Solicitar outro link'
            }
        };

        function applyResetLanguage(language) {
            const selectedLanguage = resetTranslations[language] ? language : 'es';
            const translations = resetTranslations[selectedLanguage];
            document.documentElement.lang = selectedLanguage;
            document.querySelectorAll('[data-i18n]').forEach(element => {
                const key = element.dataset.i18n;
                if (translations[key]) element.textContent = translations[key];
            });
            document.querySelectorAll('[data-set-language]').forEach(button => {
                const isSelected = button.dataset.setLanguage === selectedLanguage;
                button.classList.toggle('on', isSelected);
                button.setAttribute('aria-pressed', String(isSelected));
            });
            const languageInput = document.getElementById('reset-language');
            if (languageInput) languageInput.value = selectedLanguage;
            localStorage.setItem('smartshelfLang', selectedLanguage);
        }

        document.querySelectorAll('[data-set-language]').forEach(button => {
            button.addEventListener('click', () => applyResetLanguage(button.dataset.setLanguage));
        });

        applyResetLanguage(<?php echo json_encode($language, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
    </script>
</main>
</body>
</html>
