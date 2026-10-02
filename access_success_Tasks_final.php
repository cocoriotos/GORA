<html>
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

$global_username=$_POST['email'];
$_SESSION['email']=$global_username;
$local_username=$_SESSION['email'];
$password=$_POST['password'];
$admrole =0;
$suscriptionkind = "None";
$trialdaysleft = 0;
$active = 0;

	if($_POST)
 {
  $db_host="127.0.0.1";
  $db_user="u927778197_adm";
  $db_pass="C0mp13t3501ut10n5*";
  $db_name="u927778197_appsdb";
  $conn=mysqli_connect($db_host,$db_user,$db_pass,$db_name);

		if(mysqli_connect_errno()) 
					{	
					include("No_DB_Connectionfinal.php");
					include ("videotrackerauth.php");
					exit();
					}
		mysqli_select_db($conn,$db_name) or die ("<center>No hay conexión disponible a la aplicación</center>");		
		

		if ($conn==true)
				{

				} 
				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);

				//actualiza los dias usados de suscripcion del usuario  
				$query3="UPDATE videotips_app_access_list SET suscriptiondaysleft = DATEDIFF(CURDATE(), registrationdate), trialdaysleft = DATEDIFF(CURDATE(), lastsuscriptionpaymentdate), lastlogindate = CURDATE()  where username ='$local_username'"; 
				$result3=mysqli_query($conn, $query3);
				

				//habilitar la actualizacion masiva    
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);
				
				//Asignación del valor de dias de suscripción a disfrutar   
				$query4="UPDATE videotips_app_access_list SET daystoenjoy = IF(DATEDIFF(CURRENT_DATE(), lastsuscriptionpaymentdate) > 365, 0, DATEDIFF(CURRENT_DATE(), lastsuscriptionpaymentdate)) where suscriptionkind in ('Suspendida', 'Vencida', 'De Pago') and username ='$local_username'"; 
				$result4=mysqli_query($conn, $query4);

				//habilitar la actualizacion masiva    
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);
				
				//Asignación del valor de dias de suscripción a disfrutar   
				$query5="UPDATE videotips_app_access_list SET daystoenjoy = DATEDIFF(CURRENT_DATE(), registrationdate) where suscriptionkind in ('Owner', 'Partner') and username ='$local_username'"; 
				$result5=mysqli_query($conn, $query5);


				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);
				
				//actualiza el estado de suscripción a trial si el tiempo de Trial no ha vencido
				$query6="UPDATE videotips_app_access_list SET suscriptionkind = 'Trial' where trialdaysleft < 32 and suscriptiondaysleft < 32 and username ='$local_username'"; 
				$result6=mysqli_query($conn, $query6);

				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);

				//actualiza la suscripcion a vencida si el tiempo de suscripcion ha vencido
				$query7="UPDATE videotips_app_access_list SET suscriptionkind = 'Vencida' where (suscriptionpayed = 0 and daystoenjoy > 365)  and (suscriptionkind = 'De Pago' or suscriptionkind = 'Vencida' or suscriptionkind = 'Suspendida' or suscriptionkind = 'Trial') and username ='$local_username'"; 
				$result7=mysqli_query($conn, $query7);
				
				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);

				//actualiza la suscripcion a vencida si el tiempo de suscripcion ha vencido
				$query8="UPDATE videotips_app_access_list SET suscriptionkind = 'Vencida' where suscriptionpayed = 1 and daystoenjoy = 0 and active = 1 and suscriptiondaysleft > 365 and suscriptionactive = 1 and suscriptionkind = 'De Pago' and username ='$local_username'"; 
				$result8=mysqli_query($conn, $query8);
				
				//habilitar la actualizacion masiva 
				$query1="SET SQL_SAFE_UPDATES = 0";
				$result1=mysqli_query($conn, $query1);
				$query2="SET SQL_SAFE_UPDATES = 0";
				$result2=mysqli_query($conn, $query2);


				//actualiza el estado de pago a vencido si el tiempo de trial ha vencido
				$query9="UPDATE videotips_app_access_list SET suscriptionkind = 'Vencida' where (suscriptionpayed = 0 and daystoenjoy > 31)  and suscriptionkind = 'Trial' and username ='$local_username'"; 
				$result9=mysqli_query($conn, $query9);
				

				//deshabilitar la actualizacion masiva
				$query4="SET SQL_SAFE_UPDATES = 1";
				
				
				
                //extracta en variables de session el tipo de suscripcion, dias restantes y si ha pagado o no
				$stmt = $conn->prepare("SELECT suscriptionkind FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$result10 = $stmt->get_result();
				$suscriptionkind = $result10->fetch_assoc()['suscriptionkind'];

				//extracta en variables si el usuario está activo o no
				$stmt = $conn->prepare("SELECT active FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$result11 = $stmt->get_result();
				$active = $result11->fetch_assoc()['active'];


				$stmt = $conn->prepare("SELECT suscriptiondaysleft FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$result13 = $stmt->get_result();
				$suscriptiondaysleft = $result13->fetch_assoc()['suscriptiondaysleft'];

				$stmt = $conn->prepare("SELECT suscriptionpayed FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$result14 = $stmt->get_result();
				$suscriptionpayed = $result14->fetch_assoc()['suscriptionpayed'];

				//extracta en variables de session el nombre del usuario
				$stmt = $conn->prepare("SELECT name FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$result15 = $stmt->get_result();
				$_SESSION['name'] = $result15->fetch_assoc()['name'];

				//consulta si el usuario y contraseña son correctos y si esta activa la cuenta
				$query12="select * from videotips_app_access_list where email='$local_username' and active='1' and password='$password'"; 
				$result12=mysqli_query($conn, $query12); 	

				//actuzaliza la fecha de ultimo acceso del usuario 
				$query13="UPDATE videotips_suscription_payments SET currentdate = CURDATE() where username ='$local_username'"; 
				$result13=mysqli_query($conn, $query13);

				//actualiza los dias restantes de suscripcion de pago 
				$query14="UPDATE videotips_suscription_payments SET suscriptiondaysleft = (365 - (DATEDIFF(CURDATE(), lastpaymentdate))) where username ='$local_username'"; 
				$result14=mysqli_query($conn, $query14);

				//extracta en variables de session el rol de administrador
				$query15="select adm_role from videotips_app_access_list where username ='$local_username'"; 
				$result15=mysqli_query($conn, $query15);
				$admrole = $result15->fetch_assoc()['adm_role'];

				//extracta en variable trialdaysleft 
				$stmt = $conn->prepare("SELECT trialdaysleft FROM videotips_app_access_list WHERE username = ?");
				$stmt->bind_param("s", $local_username);
				$stmt->execute();
				$result16 = $stmt->get_result();
				$trialdaysleft = $result16->fetch_assoc()['trialdaysleft'];
				
				
				//si el usuario es administrador lo redirecciona a la pagina de administracion de usuarios
				if ($admrole > 0){
					$admrole = 0;
					$query17="UPDATE videotips_app_access_list SET lastlogindate = CURDATE()  where username ='$local_username'"; 
					$result17=mysqli_query($conn, $query17);
					header("refresh:0; url=AppMgmt.php");
					exit();
				}
				
				if ($active == 1 && $daystoenjoy < 365 && $suscriptionpayed == 1 && $suscriptionkind == 'De Pago') {
			      $_SESSION['suscriptiondue']=1;
			      header("refresh:0; url=videolinkadminmodule.php");
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
						if(mysqli_num_rows($result12)==true)
							{	
								$_SESSION['LAST_ACTIVITY'] = time();
								$query18="update videotips_app_access_list SET suscriptiondaysleft = DATEDIFF(CURDATE(), lastsuscriptionpaymentdate), visits = visits+1, lastlogindate = CURDATE() where username ='$local_username'"; 
								$result18=mysqli_query($conn, $query18);
								header("refresh:0; url=videolinkadminmodule.php");
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
</html>
