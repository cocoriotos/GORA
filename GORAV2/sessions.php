<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($GLOBALS['gora_session_guard_checked'])) {
	$GLOBALS['gora_session_guard_checked'] = true;

	if (!empty($_SESSION['email'])) {
		require_once __DIR__ . '/session_guard.php';
		$now = time();
		$lastActivity = isset($_SESSION['LAST_ACTIVITY']) ? (int) $_SESSION['LAST_ACTIVITY'] : $now;
		$checkOnly = ($_SERVER['HTTP_X_GORA_SESSION_CHECK_ONLY'] ?? '') === '1';

		if (isset($_GET['timeout']) || $now - $lastActivity >= GORA_SESSION_IDLE_TIMEOUT) {
			if (!isset($conn)) {
				include __DIR__ . '/db_connection1.php';
			}
			goraReleaseActiveSession($conn, (string) $_SESSION['email'], session_id());
			goraDestroyCurrentSession();

			header('Location: videotrackerauth.php?timeout=1');
			exit;
		}

		if (!$checkOnly) {
			$_SESSION['LAST_ACTIVITY'] = $now;
		}

		if (!isset($conn)) {
			include __DIR__ . '/db_connection1.php';
		}
		$sessionIsActive = $checkOnly
			? goraIsActiveSession($conn, (string) $_SESSION['email'], session_id())
			: goraValidateActiveSession($conn, (string) $_SESSION['email'], session_id(), $now);
		if (!$sessionIsActive) {
			goraDestroyCurrentSession();
			if (basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'sessionactivity.php') {
				http_response_code($checkOnly ? 409 : 401);
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode([
					'active' => false,
					'error' => $checkOnly ? 'session_replaced' : null,
				]);
				exit;
			}

			header('Location: videotrackerauth.php?login=session_invalid');
			exit;
		}

		if (empty($_SESSION['gora_session_generation'])) {
			$_SESSION['gora_session_generation'] = bin2hex(random_bytes(32));
		}
	}
}
?>