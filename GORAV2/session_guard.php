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

function goraReplaceActiveSession(mysqli $conn, string $email, string $sessionId, int $now): bool
{
    $replaceStmt = $conn->prepare(
        'UPDATE gora_active_sessions
         SET session_id = ?, last_activity = ?
         WHERE email = ?'
    );
    $replaceStmt->bind_param('sis', $sessionId, $now, $email);
    $replaceStmt->execute();

    if ($replaceStmt->affected_rows === 1) {
        return true;
    }

    return goraClaimActiveSession($conn, $email, $sessionId, $now);
}

function goraCreateSessionReplacementChallenge(string $email): string
{
    $challenge = bin2hex(random_bytes(32));
    $_SESSION['gora_session_replacement_challenge'] = [
        'email' => $email,
        'token' => $challenge,
        'expires_at' => time() + 300,
    ];

    return $challenge;
}

function goraConsumeSessionReplacementChallenge(string $email, string $challenge): bool
{
    $pendingChallenge = $_SESSION['gora_session_replacement_challenge'] ?? null;
    unset($_SESSION['gora_session_replacement_challenge']);

    if (!is_array($pendingChallenge)
        || !isset($pendingChallenge['email'], $pendingChallenge['token'], $pendingChallenge['expires_at'])
        || !is_string($pendingChallenge['email'])
        || !is_string($pendingChallenge['token'])
        || !is_int($pendingChallenge['expires_at'])
        || $pendingChallenge['expires_at'] < time()
        || strcasecmp($pendingChallenge['email'], $email) !== 0
    ) {
        return false;
    }

    return hash_equals($pendingChallenge['token'], $challenge);
}

function goraRespondToActiveSessionConflict(string $email): void
{
    if (($_SERVER['HTTP_X_GORA_LOGIN_REQUEST'] ?? '') === '1') {
        $challenge = goraCreateSessionReplacementChallenge($email);
        http_response_code(409);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode(['error' => 'active_session', 'challenge' => $challenge]);
        exit;
    }

    header('Location: videotrackerauth.php?login=active');
    exit;
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

function goraIsActiveSession(mysqli $conn, string $email, string $sessionId): bool
{
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
