<?php 

GLOBAL $global_username;
GLOBAL $savedlink;
GLOBAL $duplicatedlink;
GLOBAL $updatedlink;
GLOBAL $deletedlink;
GLOBAL $savedcategory;
GLOBAL $duplicatedcategory;
GLOBAL $updatedcategory;
GLOBAL $deletedcategory;
GLOBAL $suscriptiondue;
GLOBAL $suscriptioninactive;
GLOBAL $FreeSubcateryReached;
GLOBAL $sessiontimeoutreached;
GLOBAL $global_name;

GLOBAL $copytoclipboard;
GLOBAL $categorytoclipboard;
GLOBAL $subcategorytoclipboard;
GLOBAL $copynumber;
GLOBAL $linktoclipboard;
GLOBAL $videoUrl;
GLOBAL $embedUrl;
GLOBAL $click;


session_start();

$_SESSION['savedlink']=0;
$_SESSION['duplicatedlink']=0;
$_SESSION['updatedlink']=0;
$_SESSION['deletedlink']=0;

$_SESSION['savedcategory']=0;
$_SESSION['duplicatedcategory']=0;
$_SESSION['updatedcategory']=0;
$_SESSION['deletedcategory']=0;
$_SESSION['suscriptiondue']=0;
$_SESSION['suscriptioninactive']=0;
$_SESSION['FreeSubcateryReached']=0;
$_SESSION['sessiontimeoutreached']=0;

$_SESSION['copytoclipboard']=0;
$_SESSION['categorytoclipboard']=0;
$_SESSION['subcategorytoclipboard']=0;
$_SESSION['copynumber']=0;
$_SESSION['linktoclipboard']=0;
$_SESSION['videoUrl']="";
$_SESSION['embedUrl']="";
$_SESSION['click']=0;
$_SESSION['name']="";

$savedlink = $_SESSION['savedlink'];
$duplicatedlink = $_SESSION['duplicatedlink'];
$updatedlink = $_SESSION['updatedlink'];
$deletedlink = $_SESSION['deletedlink'];
$name = $_SESSION['name'];

$savedcategory = $_SESSION['savedcategory'];
$duplicatedcategory = $_SESSION['duplicatedcategory'];
$updatedcategory = $_SESSION['updatedcategory'];
$deletedcategory = $_SESSION['deletedcategory'];
$FreeSubcateryReached = $_SESSION['FreeSubcateryReached'];

$suscriptiondue = $_SESSION['suscriptiondue'];
$suscriptioninactive = $_SESSION['suscriptioninactive'];
$sessiontimeoutreached = $_SESSION['sessiontimeoutreached'];


$copytoclipboard = $_SESSION['copytoclipboard'];
$categorytoclipboard = $_SESSION['categorytoclipboard'];
$subcategorytoclipboard = $_SESSION['subcategorytoclipboard'];
$copynumber = $_SESSION['copynumber'];
$linktoclipboard = $_SESSION['linktoclipboard'];
$videoUrl = $_SESSION['videoUrl'];
$embedUrl = $_SESSION['embedUrl'];
$click = $_SESSION['click'];

$global_username = '';
$local_username = '';
$password = '';
$password=$_POST['password'];
$admrole =0;
$suscriptionkind = "None";
$trialdaysleft = 0;
$active = 0;



	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password']))
 {
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
		$stmt->bind_result($storedPassword);
		$stmt->fetch();
		$stmt->close();
		$storedPassword = $storedPassword ?? '';
		$passwordVerified = $storedPassword !== '' && password_verify($password, $storedPassword);
		
		if (!$passwordVerified && $storedPassword !== '' && hash_equals($storedPassword, $password)) {
			
			$upgradedHash = password_hash($password, PASSWORD_DEFAULT);
			$upgradeStmt = $conn->prepare("UPDATE videotips_app_access_list SET password = ? WHERE email = ? AND password = ?");
			$upgradeStmt->bind_param("sss", $upgradedHash, $local_username, $storedPassword);
			$upgradeStmt->execute();
			$passwordVerified = $upgradeStmt->affected_rows === 1;
		}
		if (!$passwordVerified) {
			header("Location: videotrackerauth.php?login=failed");
			exit();
		}

		if (!empty($_SESSION['email'])) {
			header("Location: videotrackerauth.php?login=active");
			exit();
		}

		$cleanupStmt = $conn->prepare("UPDATE videotips_accessrequests SET password = '' WHERE email = ?");
		$cleanupStmt->bind_param("s", $local_username);
		$cleanupStmt->execute();

		session_regenerate_id(true);
		require_once __DIR__ . '/session_guard.php';
		if (!goraClaimActiveSession($conn, $local_username, session_id(), time())) {
			header("Location: videotrackerauth.php?login=active");
			exit();
		}
		$_SESSION['email'] = $local_username;
		$_SESSION['LAST_ACTIVITY'] = time();
		$global_username = $local_username;
		require_once __DIR__ . '/subscription_access.php';
		$executeUserStatement = static function (string $sql) use ($conn, $local_username): mysqli_stmt {
			$userStmt = $conn->prepare($sql);
			$userStmt->bind_param("s", $local_username);
			$userStmt->execute();
			return $userStmt;
		};
		

				$result3 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptiondaysleft = DATEDIFF(CURDATE(), registrationdate), trialdaysleft = DATEDIFF(CURDATE(), lastsuscriptionpaymentdate), lastlogindate = CURDATE() WHERE username = ?");
				$result3->close();
				$subscriptionAccess = goraRefreshSubscriptionAccess($conn, $local_username);
				if ($subscriptionAccess === null) {
					error_log('Authenticated user subscription record was not found.');
					http_response_code(500);
					exit('No fue posible cargar los datos de la cuenta.');
				}

                //extracta en variables de session los datos de suscripción y del usuario
				$stmt = $conn->prepare("SELECT suscriptionkind, daystoenjoy, active, suscriptiondaysleft, suscriptionpayed, name, adm_role, trialdaysleft, suscriptionactive, suscription_package FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$stmt->bind_result($suscriptionkind, $daystoenjoy, $active, $suscriptiondaysleft, $suscriptionpayed, $name, $admrole, $trialdaysleft, $suscriptionactive, $suscription_package);
				if (!$stmt->fetch()) {
					error_log('Authenticated user subscription record was not found.');
					http_response_code(500);
					exit('No fue posible cargar los datos de la cuenta.');
				}
				$stmt->close();
				$_SESSION['name'] = $name;

				//consulta si el usuario y contraseña son correctos y si esta activa la cuenta
				//actuzaliza la fecha de ultimo acceso del usuario 
				$result13 = $executeUserStatement("UPDATE videotips_suscription_payments SET currentdate = CURDATE() WHERE username = ?");
				$result13->close();

				$accessUser = [
					'active' => (int) $active,
					'suscriptionactive' => (int) $suscriptionactive,
					'suscriptionpayed' => (int) $suscriptionpayed,
					'suscriptionkind' => $suscriptionkind,
					'suscription_package' => $suscription_package,
					'daystoenjoy' => (int) $daystoenjoy,
					'adm_role' => (int) $admrole,
				];

				if (goraCanAccessAppManagement($accessUser)){
					$result17 = $executeUserStatement("UPDATE videotips_app_access_list SET lastlogindate = CURDATE() WHERE username = ?");
					$result17->close();
					header("Location: AppMgmt.php", true, 303);
					exit();
				}
				
				if (goraCanAccessVideoModule($accessUser)) {
					$visitUpdate = $executeUserStatement("UPDATE videotips_app_access_list SET visits = visits + 1, lastlogindate = CURDATE() WHERE username = ?");
					$visitUpdate->close();
			      $_SESSION['suscriptiondue']=1;
			      header("Location: videolinkadminmodule.php", true, 303);
			  	  exit();
			  	  }
				$_SESSION['suscriptiondue'] = 1;
				header("Location: suscriptionpayment.php", true, 303);
				exit();
}
?>	
