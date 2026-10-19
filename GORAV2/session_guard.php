<?php

const GORA_SESSION_IDLE_TIMEOUT = 600;

function goraClaimActiveSession(mysqli $conn, string $email, string $sessionId, int $now): bool
{
    $insertStmt = $conn->prepare(
        'INSERT INTO gora_active_sessions (email, session_id, last_activity)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE last_activity = last_activity'
    );
    $insertStmt->bind_param('ssi', $email, $sessionId, $now);
    $insertStmt->execute();

    if ($insertStmt->affected_rows === 1) {
        return true;
    }

    $cutoff = $now - GORA_SESSION_IDLE_TIMEOUT;
    $replaceStmt = $conn->prepare(
        'UPDATE gora_active_sessions
         SET session_id = ?, last_activity = ?
         WHERE email = ? AND last_activity <= ?'
    );
    $replaceStmt->bind_param('sisi', $sessionId, $now, $email, $cutoff);
    $replaceStmt->execute();

    return $replaceStmt->affected_rows === 1;
}

function goraTakeoverActiveSession(mysqli $conn, string $email, string $sessionId, int $now): void
{
    $takeoverStmt = $conn->prepare(
        'INSERT INTO gora_active_sessions (email, session_id, last_activity)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE session_id = ?, last_activity = ?'
    );
    $takeoverStmt->bind_param('ssisi', $email, $sessionId, $now, $sessionId, $now);
    $takeoverStmt->execute();
}

function goraRenderSessionTakeoverConfirmation(
    string $email,
    string $password,
    string $processor,
    string $loginPage,
    ?string $captcha = null
): void {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    $escapedEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $escapedPassword = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
    $escapedProcessor = htmlspecialchars($processor, ENT_QUOTES, 'UTF-8');
    $escapedLoginPage = htmlspecialchars($loginPage, ENT_QUOTES, 'UTF-8');
    $escapedCaptcha = $captcha === null ? null : htmlspecialchars($captcha, ENT_QUOTES, 'UTF-8');

    echo '<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Sesión activa</title><body style="font-family:Arial,sans-serif;max-width:32rem;margin:15vh auto;padding:1.5rem;text-align:center">';
    echo '<h1>Sesión activa</h1><p>Este usuario ya tiene una sesión abierta. ¿Desea cerrarla e ingresar aquí?</p>';
    echo '<form method="post" action="' . $escapedProcessor . '">';
    echo '<input type="hidden" name="email" value="' . $escapedEmail . '">';
    echo '<input type="hidden" name="password" value="' . $escapedPassword . '">';
    if ($escapedCaptcha !== null) {
        echo '<input type="hidden" name="captcha" value="' . $escapedCaptcha . '">';
    }
    echo '<input type="hidden" name="takeover" value="1">';
    echo '<button type="submit">Sí, cerrar la otra sesión e ingresar</button> ';
    echo '<a href="' . $escapedLoginPage . '">Cancelar</a></form></body></html>';
}

function goraValidateActiveSession(mysqli $conn, string $email, string $sessionId, int $now): bool
{
    $updateStmt = $conn->prepare(
        'UPDATE gora_active_sessions
         SET last_activity = ?
         WHERE email = ? AND session_id = ?'
    );
    $updateStmt->bind_param('iss', $now, $email, $sessionId);
    $updateStmt->execute();

    if ($updateStmt->affected_rows === 1) {
        return true;
    }

    $checkStmt = $conn->prepare(
        'SELECT 1 FROM gora_active_sessions WHERE email = ? AND session_id = ?'
    );
    $checkStmt->bind_param('ss', $email, $sessionId);
    $checkStmt->execute();

    return $checkStmt->get_result()->num_rows === 1;
}

function goraReleaseActiveSession(mysqli $conn, string $email, string $sessionId): void
{
    $deleteStmt = $conn->prepare(
        'DELETE FROM gora_active_sessions WHERE email = ? AND session_id = ?'
    );
    $deleteStmt->bind_param('ss', $email, $sessionId);
    $deleteStmt->execute();
}

function goraDestroyCurrentSession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookieParams = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
