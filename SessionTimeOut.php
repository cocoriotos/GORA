<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
$timeout_duration = 900;
$sessiontimeoutreached = $_SESSION['sessiontimeoutreached'];


if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];
    if ($elapsed_time > $timeout_duration) {
        $_SESSION['sessiontimeoutreached'] = 1;
        header("refresh:0; url=videolinkadminmodule.php"); // Redirige a la página de autenticación
        include("closetaskscon.php");
	include("videotrackerauth.php");
    }
}
$_SESSION['LAST_ACTIVITY'] = time();
?>