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
		$is_authenticated = true;
		$executeUserStatement = static function (string $sql) use ($conn, $local_username): mysqli_stmt {
			$userStmt = $conn->prepare($sql);
			$userStmt->bind_param("s", $local_username);
			$userStmt->execute();
			return $userStmt;
		};
		

				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);

				//actualiza los dias usados de suscripcion del usuario  
				$result3 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptiondaysleft = DATEDIFF(CURDATE(), lastsuscriptionpaymentdate), trialdaysleft = DATEDIFF(CURDATE(), lastsuscriptionpaymentdate), lastlogindate = CURDATE() WHERE username = ?");
				

				//habilitar la actualizacion masiva    
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);
				
				//Asignación del valor de dias de suscripción a disfrutar   
				$result4 = $executeUserStatement("UPDATE videotips_app_access_list SET daystoenjoy = IF(DATEDIFF(CURRENT_DATE(), lastsuscriptionpaymentdate) > 365, 0, DATEDIFF(CURRENT_DATE(), lastsuscriptionpaymentdate)) WHERE suscriptionkind IN ('Suspendida', 'Vencida', 'De Pago') AND username = ?");

				//habilitar la actualizacion masiva    
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);
				
				//Asignación del valor de dias de suscripción a disfrutar   
				$result5 = $executeUserStatement("UPDATE videotips_app_access_list SET daystoenjoy = DATEDIFF(CURRENT_DATE(), registrationdate) WHERE suscriptionkind IN ('Owner', 'Partner') AND username = ?");


				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);
				
				//actualiza el estado de suscripción a trial si el tiempo de Trial no ha vencido
				$result6 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptionkind = 'Trial' WHERE trialdaysleft < 32 AND suscriptiondaysleft < 32 AND username = ?");

				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);

				//actualiza la suscripcion a vencida si el tiempo de suscripcion ha vencido
				$result7 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptionkind = 'Vencida' WHERE (suscriptionpayed = 0 AND daystoenjoy > 365) AND (suscriptionkind = 'De Pago' OR suscriptionkind = 'Vencida' OR suscriptionkind = 'Suspendida' OR suscriptionkind = 'Trial') AND username = ?");
				
				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);

				//actualiza la suscripcion a vencida si el tiempo de suscripcion ha vencido
				$result8 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptionkind = 'Vencida' WHERE suscriptionpayed = 1 AND daystoenjoy = 0 AND active = 1 AND suscriptiondaysleft > 365 AND suscriptionactive = 1 AND suscriptionkind = 'De Pago' AND username = ?");
				
				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);


				//actualiza el estado de pago a vencido si el tiempo de trial ha vencido
				$result9 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptionkind = 'Vencida' WHERE (suscriptionpayed = 0 AND daystoenjoy > 31) AND suscriptionkind = 'Trial' AND username = ?");
				

				//deshabilitar la actualizacion masiva
				$query4="SET SQL_SAFE_UPDATES = 1";
				
				
				
                //extracta en variables de session los datos de suscripción y del usuario
				$stmt = $conn->prepare("SELECT suscriptionkind, daystoenjoy, active, suscriptiondaysleft, suscriptionpayed, name, adm_role, trialdaysleft FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$stmt->bind_result($suscriptionkind, $daystoenjoy, $active, $suscriptiondaysleft, $suscriptionpayed, $name, $admrole, $trialdaysleft);
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

				//actualiza los dias restantes de suscripcion de pago 
				$result14 = $executeUserStatement("UPDATE videotips_suscription_payments SET suscriptiondaysleft = (365 - (DATEDIFF(CURDATE(), lastpaymentdate))) WHERE username = ?");

				//si el usuario es administrador lo redirecciona a la pagina de administracion de usuarios
				if ($admrole > 0){
					$admrole = 0;
					$result17 = $executeUserStatement("UPDATE videotips_app_access_list SET lastlogindate = CURDATE() WHERE username = ?");
					header("refresh:0; url=AppMgmt.php");
					exit();
				}
				
				if ($active == 1 && $daystoenjoy < 365 && $suscriptionpayed == 1 && $suscriptionkind == 'De Pago') {
			      $_SESSION['suscriptiondue']=1;
			      header("Location: videolinkadminmodule.php", true, 303);
			  	  exit();
			  	  }

				if ($daystoenjoy > 31 && $suscriptionpayed == 0 && $suscriptionkind == 'Trial') {
			      $_SESSION['suscriptiondue']=1;
			      header("refresh:0; url=suscriptionpayment.php");
			  	exit();
			  	  }

				  if ($active == 0 && $suscriptionkind == 'De Pago') {
			      $_SESSION['suscriptiondue']=1;
			      header("refresh:0; url=suscriptionpayment.php");
			  	  exit();
			  	  }

				  if ($daystoenjoy == 0 &&  $suscriptionkind == 'De Pago') {
			      $_SESSION['suscriptiondue']=1;
			      header("refresh:0; url=suscriptionpayment.php");
			  	  exit();
			  	  }

				  if ($daystoenjoy < 366 &&  $suscriptionkind == 'De Pago' && $active == 0) {
			      $_SESSION['suscriptiondue']=1;
			      header("refresh:0; url=suscriptionpayment.php");
			  	  exit();
			  	  }
				
			 	  if ($daystoenjoy > 31 && $suscriptionkind == 'Trial') {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }		
				if ($daystoenjoy < 31 && $suscriptionkind == 'Trial' && $active == 0) {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }  
				  
				if ($active == 0 && $suscriptionkind == 'Owner') {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }
				if ($active == 0 && $suscriptionkind == 'Partner') {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }
				if ($suscriptionkind == 'Suspendida') {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }
				if ($active == 0 && $suscriptionkind == 'Test') {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }
				if ($suscriptionkind == 'Vencida') {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }


				  if ($daystoenjoy > 365  && $suscriptionkind == 'De Pago' ) {
					$_SESSION['suscriptiondue']=1;
					header("refresh:0; url=suscriptionpayment.php");
					exit();
				  }    
				  else{	
						if ($is_authenticated)
							{	
								$_SESSION['LAST_ACTIVITY'] = time();
								$result18 = $executeUserStatement("UPDATE videotips_app_access_list SET suscriptiondaysleft = DATEDIFF(CURDATE(), lastsuscriptionpaymentdate), visits = visits+1, lastlogindate = CURDATE() WHERE username = ?");
								header("Location: videolinkadminmodule.php", true, 303);
								exit();
							}
							else 
							{
					echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
					echo "<script>
						document.addEventListener('DOMContentLoaded', function() {
							Swal.fire({
								title: 'Mensaje',
								text: 'Su usuario o contraseña son incorrectos, por favor intentar nuevamente si está registrado de lo contrario solicite la opción de Solicitud de Acceso',
								icon: 'error',
								confirmButtonText: 'Aceptar',
								customClass: {
									popup: 'custom-swal-popup',
									title: 'custom-swal-title',
									content: 'custom-swal-content',
									confirmButton: 'custom-swal-confirm-button'
								}
							}).then(() => {
							window.location.href = 'videotrackerauth.php';
						});
						});
					</script>";	
							}
						}		
}
?>	
