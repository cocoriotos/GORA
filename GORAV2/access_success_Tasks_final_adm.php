<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'], $_POST['password'])) {
    header("Location: videotrackerauth.php");
    exit();
}

$local_username = trim((string) $_POST['email']);
$password = (string) $_POST['password'];
if (!filter_var($local_username, FILTER_VALIDATE_EMAIL) || $password === '') {
    header("Location: videotrackerauth.php");
    exit();
}

include("db_connection1.php");

$stmt = $conn->prepare("SELECT password FROM videotips_app_access_list WHERE email = ? AND active = 1");
$stmt->bind_param("s", $local_username);
$stmt->execute();
$authRecord = $stmt->get_result()->fetch_assoc();
$storedPassword = $authRecord['password'] ?? '';
$passwordVerified = $storedPassword !== '' && password_verify($password, $storedPassword);

if (!$passwordVerified && $storedPassword !== '' && hash_equals($storedPassword, $password)) {
    $upgradedHash = password_hash($password, PASSWORD_DEFAULT);
    $upgradeStmt = $conn->prepare("UPDATE videotips_app_access_list SET password = ? WHERE email = ? AND password = ?");
    $upgradeStmt->bind_param("sss", $upgradedHash, $local_username, $storedPassword);
    $upgradeStmt->execute();
    $passwordVerified = $upgradeStmt->affected_rows === 1;
}

if ($passwordVerified) {
    $cleanupStmt = $conn->prepare("UPDATE videotips_accessrequests SET password = '' WHERE email = ?");
    $cleanupStmt->bind_param("s", $local_username);
    $cleanupStmt->execute();
}

if (!$passwordVerified) {
    header("Location: videotrackerauth.php?login=failed");
    exit();
}

require_once __DIR__ . '/session_guard.php';
$reuseCurrentSession = false;
$takeoverRequested = ($_POST['takeover'] ?? '') === '1';
if (!empty($_SESSION['email'])) {
    $currentSessionId = session_id();
    $currentEmail = (string) $_SESSION['email'];
    if (strcasecmp($currentEmail, $local_username) === 0) {
        $reuseCurrentSession = goraValidateActiveSession($conn, $currentEmail, $currentSessionId, time());
    } else {
        goraReleaseActiveSession($conn, $currentEmail, $currentSessionId);
        $_SESSION = [];
    }
}

if (!$reuseCurrentSession) {
    session_regenerate_id(true);
    if ($takeoverRequested) {
        goraTakeoverActiveSession($conn, $local_username, session_id(), time());
    } elseif (!goraClaimActiveSession($conn, $local_username, session_id(), time())) {
        goraRenderSessionTakeoverConfirmation(
            $local_username,
            $password,
            'access_success_Tasks_final_adm.php',
            'videotrackerauthadm..php'
        );
        exit();
    }
}
$_SESSION['email'] = $local_username;
$_SESSION['LAST_ACTIVITY'] = time();

$stmt = $conn->prepare("UPDATE videotips_app_access_list SET suscriptiondaysleft = DATEDIFF(CURDATE(), registrationdate) WHERE username = ?");
$stmt->bind_param("s", $local_username);
$stmt->execute();

$stmt = $conn->prepare("SELECT suscriptiondaysleft, suscriptionpayed FROM videotips_app_access_list WHERE username = ?");
$stmt->bind_param("s", $local_username);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$suscriptiondaysleft = (int) ($userData['suscriptiondaysleft'] ?? 0);
$suscriptionpayed = (int) ($userData['suscriptionpayed'] ?? 0);

if ($suscriptiondaysleft > 8 && $suscriptionpayed === 0) {
    $_SESSION['suscriptiondue'] = 1;
    header("Location: suscriptionpayment.php");
    exit();
}

header("Location: videolinkadminmodule.php");
exit();
?>
