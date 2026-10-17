<!--  Developed by julián González Bucheli -->
<html lang="us">
	<?php
    include "sessions.php";
	date_default_timezone_set('America/Bogota');
	?>
	<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - SmartShelf</title>
        <link rel="icon" href="SSCircleBackgroundWhite.ico" type="image/x-icon">
        <script src="head.js?v=<?php echo time(); ?>" defer></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="stylesheet" href="style_sheet_auth.css?v=<?php echo time(); ?>">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <script>
            window.onload = function() {
                // Verificar si la página ya ha sido cargada
                if (!sessionStorage.getItem('pageReloaded')) {
                    if ('caches' in window) {
                        caches.keys().then(function(names) {
                            for (let name of names) caches.delete(name);
                        });
                    }
                    // Marcar la página como recargada
                    sessionStorage.setItem('pageReloaded', 'true');
                    // Recargar la página sin modificar la URL
                    location.replace(location.href);
                }
            };
        </script>
    </head>
	
	<body id="bodyadminmodule">   
        <div class="login-container">
            <!-- New section for "Video Tutoriales" and "Manual del Usuario" links -->
            <div class="help-links">
                <!--<a id="ayuda" href="https://www.youtube.com/playlist?list=PLRQ5KF9igtB2GRlHLSP6Uwx1lzy387Wz5" target="_blank">Video Tutoriales</a>while Adjustments
                <a id="ayuda" href="UCLToolManualDelUsuario2025.pdf" target="_blank">Manual del Usuario</a>-->
            </div> 
            <div class="login-header">
                <img src="SmartShelfUsefulContentLibraryDarrkLightGreen.ico" alt="SmartShelf Logo" class="logo">
                <h1>Biblioteca de Contenidos Útiles</h1>
                <?php include __DIR__ . "/social_links.php"; ?>
            </div>
            <form id="login" action="access_success_Tasks_final.php" method="POST" autocomplete="off">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <img src="captcha.php" alt="CAPTCHA" class="captcha-image">
                <input type="text" name="captcha" placeholder="Ingrese el CAPTCHA" required>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>

            <form action="index.php" method="POST" autocomplete="off">
                <button type="submit" class="btn-login">Cancelar</button>
            </form>
            <a href="recoverpassword.php" class="forgot-password">¿Olvidaste tu contraseña?</a>

            <form id="request-access" action="requestaccessfinal.php" method="POST" autocomplete="off">
                <p>¿Sin acceso? <button type="submit" class="btn-request">Solicitarlo aquí</button></p>
                <p>¿Alguna duda? Contáctenos al Email: <a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a></p>
            </form>
        </div>
    </body>
</html>
