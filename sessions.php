<?php
session_cache_limiter('nocache');
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!empty($_SESSION['email'])) {
	$now = time();
	$lastActivity = isset($_SESSION['LAST_ACTIVITY']) ? (int) $_SESSION['LAST_ACTIVITY'] : $now;

	if (isset($_GET['timeout']) || $now - $lastActivity >= 180) {
		session_unset();
		session_destroy();

		if (ini_get('session.use_cookies')) {
			$cookieParams = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
		}

		header('Location: videotrackerauth.php?timeout=1');
		exit;
	}

	$_SESSION['LAST_ACTIVITY'] = $now;
}
?>