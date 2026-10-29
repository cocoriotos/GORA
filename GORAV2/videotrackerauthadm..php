<!--  Developed by julián González Bucheli -->
<html lang="us">
	<?php /*include "nobackpage.php"; 
	include "SessionTimeOut.php";*/	
	date_default_timezone_set('America/Bogota');
	?>
	<head id="contact">	
		<script src="head.js" defer></script>	
		<script src="password-visibility.js" defer></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"></link>
		<link rel="stylesheet" href="style_sheet.css"/>
		<script src="Popper/popper.min.js"></script>
		<script src="plugins/sweetalert/sweetalert.min.js"></script>
		<script src="plugins/alertifyjs/alertify.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>	
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>	  
	</head>
	<header id="contact">
			
				<center><h1><font color="#E1EAF7">Biblioteca de Contenidos Útiles</font></h1></center>
                <?php include __DIR__ . "/social_links.php"; ?>
				
	</header>	
	  <body id="bodyadminmodule">
	  <br>
	  <br>
	  <br>
		<form id="login" action="access_success_Tasks_final_adm.php" method="POST" autocomplete="off"> 
			    <center>
					<font color=lightblue id="form_title"><strong>Formulario de Autenticación</strong></font><br><br>
                	<i class="fas fa-sign-in-alt fa-5x" style="color: lightblue;"></i><br><br>
					<hr>
					<div class="inputdata1">
						<font id= "form_title" color="white"><strong>Email</strong></font><br><br>
						<input id="username1" type="email" name="email" placeholder="Digite el email" autocomplete="username" required ><br> <!-- Login  --><br>
					</div >
					<div class="inputdata1">
						<font id= "form_title" color="white"><strong>Contraseña</strong></font><br><br>
						<input id="username1" type="password" name="password" placeholder="Digite su Constraseña" autocomplete="current-password" required ><br> <!-- Login PAM username  --><br>
					</div >
					<input id="loginbutton" type="submit" value="Ingresar">
					<br>
					<br>
					<br>
					<br>
				</center>
		</form>	
		<br>
		<footer id="contact">	
					<hgroup>
					
					<h1 align="center" datetime="<?php echo date('Y-m-d H:i'); ?>" pubdate> Fecha: <?php echo date('m/d/Y');?><address><strong> Alguna duda? contáctenos al Email: <u>adm@solicionespro.com</u></strong></address></h1>
					
					</hgroup>			
		</footer>
	</body>
</html>