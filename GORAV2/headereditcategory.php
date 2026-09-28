<!DOCTYPE html>
<?php
include "sessions.php";
include "sessionvalidation.php";
$name = $_SESSION['name'];
?>
<html lang="us">
<head>    
    <link rel="stylesheet" href="style_sheet_ops.css"/>
</head>
<header>
<nav class="navbar navbar-dark bg-dark d-flex justify-content-center" id="welcome">
<center><a id="welcome"  class="navbar-brand"><span class="username-style"><?php echo $name; ?></span>, éstas en tu Biblioteca de Contenidos Útiles</a></center>
</nav>

<!--<nav class="navbar navbar-dark bg-dark d-flex justify-content-between align-items-center">
     
      <div class="header-action-group">
          <a id="headerfonts" href="videolinkadminmodule.php" class="header-action-btn clear"><i class="fas fa-reply"></i> <span data-i18n="header_add_link">Adicionar Enlace</span></a>
          <a id="headerfonts" href="addcategory.php" class="header-action-btn categories"><i class="fas fa-folder-tree"></i> <span data-i18n="header_categories">Categorías</span></a>  
          <a id="headerfonts" href="closetaskscon.php" class="header-action-btn logout"><i class="fas fa-right-from-bracket"></i> <span data-i18n="header_logout">Salir</span></a>
      </div>
</nav>-->

  <!-- BOOTSTRAP -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"></link>
    <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
    <script>
      const headerTranslations = {
        es: { header_add_link: 'Adicionar Enlace', header_categories: 'Categorías', header_logout: 'Salir' },
        en: { header_add_link: 'Add Link', header_categories: 'Categories', header_logout: 'Logout' },
        pt: { header_add_link: 'Adicionar Link', header_categories: 'Categorias', header_logout: 'Sair' }
      };
      function updateHeaderLang() {
        const lang = localStorage.getItem('smartshelfLang') || 'es';
        const translations = headerTranslations[lang] || headerTranslations.es;
        document.querySelectorAll('[data-i18n]').forEach(el => {
          const key = el.getAttribute('data-i18n');
          if (translations[key] !== undefined) el.textContent = translations[key];
        });
        document.documentElement.lang = lang;
      }
      document.addEventListener('DOMContentLoaded', updateHeaderLang);
    </script>
	</header>
    </html>