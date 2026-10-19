<!DOCTYPE html>
<?php
include "sessions.php";
include "sessionvalidation.php";
$name = $_SESSION['name'];
?>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_sheet_ops.css"/>
  <script src="session-timeout.js?v=20261001-3" defer></script>
</head>
<html lang="es">
  <header>
  <nav class="navbar navbar-dark bg-dark" id="admin-welcome">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a id="welcome" class="navbar-brand">
            <span data-admin-i18n="welcome">Bienvenido</span>
            <span class="username-style"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>.
            <span data-admin-i18n="module_title">Administración de Operaciones</span>
        </a>
        <div class="admin-language-switcher" role="group" aria-label="Language">
            <button type="button" class="admin-language-button" data-admin-lang="es" aria-label="Español">ES</button>
            <button type="button" class="admin-language-button" data-admin-lang="en" aria-label="English">EN</button>
            <button type="button" class="admin-language-button" data-admin-lang="pt" aria-label="Português">PT</button>
        </div>
    </div>
  </nav>
  <?php include __DIR__ . "/social_links.php"; ?>
  <!-- BOOTSTRAP -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"></link>
    <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
	</header>
    </html>