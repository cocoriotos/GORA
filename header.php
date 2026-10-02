<!DOCTYPE html>
<?php
include "sessions.php";
include "sessionvalidation.php";
/*include "SessionTimeOut.php";*/
$name = $_SESSION['name'];
?>
<head>    
    <link rel="stylesheet" href="style_sheet_ops.css"/>
  <script src="session-timeout.js?v=20261001-3" defer></script>
</head>
<html lang="us"> 
  <header>
  <nav class="navbar navbar-dark bg-dark d-flex justify-content-center" id="welcome" >
  <center><a id="welcome"  class="navbar-brand"><span class="username-style"><?php echo $name; ?></span>, <span data-i18n="header_welcome_msg">estás en tu Biblioteca de Contenidos Útiles</span></a></center>
  </nav>
  <script src="copynumber.js"></script>
  <script src="copypaypal.js"></script>

  <!--<nav class="navbar navbar-dark bg-dark d-flex justify-content-between align-items-center">
      <div class="header-action-group">
        <a id="headerfonts" href="suscriptionpayment.php" class="header-action-btn subscribe"><i class="fas fa-gem"></i> <span data-i18n="header_subscribe">Suscribirse</span></a>
        <a id="headerfonts" href="https://www.youtube.com/playlist?list=PLRQ5KF9igtB2GRlHLSP6Uwx1lzy387Wz5" class="header-action-btn tutorials" target="_blank"><i class="fas fa-play-circle"></i> <span data-i18n="header_tutorials">Tutoriales</span></a>
        <a id="headerfonts" href="UCLToolManualDelUsuario2025.pdf" class="header-action-btn manual" target="_blank"><i class="fas fa-book-open"></i> <span data-i18n="header_manual">Manual</span></a>
        <a id="headerfonts" href="videolinkadminmodule.php" class="header-action-btn clear"><i class="fas fa-broom"></i> <span data-i18n="header_clear">Limpiar Formulario</span></a>
        <a id="headerfonts" href="addcategory.php" class="header-action-btn categories"><i class="fas fa-folder-tree"></i> <span data-i18n="header_categories">Categorías</span></a>
        <a id="headerfonts" href="closetaskscon.php" class="header-action-btn logout"><i class="fas fa-right-from-bracket"></i> <span data-i18n="header_logout">Salir</span></a>
      </div>
  </nav>-->


  <!-- BOOTSTRAP -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"></link>
    <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
    <script>
      const headerTranslations = {
        es: {
          header_welcome_msg: 'estás en tu Biblioteca de Contenidos Útiles',
          header_subscribe: 'Suscribirse',
          header_tutorials: 'Tutoriales',
          header_manual: 'Manual',
          header_clear: 'Limpiar Formulario',
          header_categories: 'Categorías',
          header_logout: 'Salir'
        },
        en: {
          header_subscribe: 'Subscribe',
          header_tutorials: 'Tutorials',
          header_manual: 'Manual',
          header_clear: 'Clear Form',
          header_categories: 'Categories',
          header_logout: 'Logout',
          header_welcome_msg: 'you are in your Useful Contents Library'
        },
        pt: {
          header_subscribe: 'Inscrever-se',
          header_tutorials: 'Tutoriais',
          header_manual: 'Manual',
          header_clear: 'Limpar Formulário',
          header_categories: 'Categorias',
          header_logout: 'Sair',
          header_welcome_msg: 'você está na sua Biblioteca de Conteúdos Úteis'
        }
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
      window.addEventListener('languageChanged', updateHeaderLang);
    </script>
	</header>
    </html>