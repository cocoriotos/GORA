<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_cache_limiter('nocache');
	session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($GLOBALS['gora_session_guard_checked'])) {
	$GLOBALS['gora_session_guard_checked'] = true;

	if (!empty($_SESSION['email'])) {
		require_once __DIR__ . '/session_guard.php';
		$now = time();
		$lastActivity = isset($_SESSION['LAST_ACTIVITY']) ? (int) $_SESSION['LAST_ACTIVITY'] : $now;

		if (isset($_GET['timeout']) || $now - $lastActivity >= GORA_SESSION_IDLE_TIMEOUT) {
			if (!isset($conn)) {
				include __DIR__ . '/db_connection1.php';
			}
			goraReleaseActiveSession($conn, (string) $_SESSION['email'], session_id());
			goraDestroyCurrentSession();

			header('Location: videotrackerauth.php?timeout=1');
			exit;
		}

		$_SESSION['LAST_ACTIVITY'] = $now;

		if (!isset($conn)) {
			include __DIR__ . '/db_connection1.php';
		}
		if (!goraValidateActiveSession($conn, (string) $_SESSION['email'], session_id(), $now)) {
			goraDestroyCurrentSession();
			if (basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'sessionactivity.php') {
				http_response_code(401);
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode(['active' => false]);
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