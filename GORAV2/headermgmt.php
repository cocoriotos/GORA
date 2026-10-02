<!DOCTYPE html>
<?php
include "sessions.php";
include "sessionvalidation.php";
$name = $_SESSION['name'];
?>
<head>    
    <link rel="stylesheet" href="style_sheet_ops.css"/>
  <script src="session-timeout.js?v=20261001-3" defer></script>
</head>
<html lang="us"> 
  <header>
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <!-- Texto de bienvenida -->
        <a id="welcome" class="navbar-brand">
            Bienvenido <span class="username-style"><?php echo $name; ?></span>. Administración de Operaciones</a>

        <!-- Botón Salir -->
        <a id="headerfonts" href="videotrackerauth.php" class="btn btn-danger" style="background-color: #9A97F5; color: black; font-weight: bold;"><i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
  </nav>
  <!-- BOOTSTRAP -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"></link>
    <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
	</header>
  <br>
    </html>