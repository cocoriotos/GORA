<?php
	include "sessions.php";
	require_once __DIR__ . '/session_guard.php';
	if (!empty($_SESSION['email'])) {
		include __DIR__ . '/db_connection1.php';
		goraReleaseActiveSession($conn, (string) $_SESSION['email'], session_id());
	}
	goraDestroyCurrentSession();
	include "sessionvalidation.php";
?>