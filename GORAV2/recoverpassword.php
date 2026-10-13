<!-- Developed by Julián González Bucheli -->
<html lang="us">
<?php 
include "sessions.php";
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - GORA</title>
    <link rel="icon" href="GORA.ico" type="image/x-icon">
    <script src="head.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_sheet_auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body id="bodyadminmodule">
    <div class="login-container">
        <!-- Enlaces de ayuda en la parte superior derecha -->
        <div class="help-links">
            <!--<a href="https://www.youtube.com/playlist?list=PLRQ5KF9igtB2GRlHLSP6Uwx1lzy387Wz5" target="_blank">Video Tutoriales</a>while Adjustments
            <a href="UCLToolManualDelUsuario2025.pdf" target="_blank">Manual del Usuario</a>-->
        </div>

        <!-- Encabezado del formulario -->
        <div class="login-header">
            <img src="GORA.ico" alt="GORA Logo" class="logo">
            <h1 data-i18n="rec_title">Recuperación de Contraseña</h1>
            <?php include __DIR__ . "/social_links.php"; ?>
        </div>

        <!-- Formulario de recuperación de contraseña -->
        <form id="login" action="recoverpasswordemailFinal.php" method="POST" autocomplete="off">
            <div class="lang-sw">
                <button class="lb on" type="button" onclick="setLang('es')">ES</button>
                <button class="lb" type="button" onclick="setLang('en')">EN</button>
                <button class="lb" type="button" onclick="setLang('pt')">PT</button>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" data-i18n-placeholder="rec_email" placeholder="Email" required>
            </div>
            <button type="submit" class="btn-login" data-i18n="rec_submit">Recuperar Contraseña</button>
            <br><br>
        </form>
        
        <form action="index.php" method="POST" autocomplete="off">
            <button type="submit" class="btn-login">Cancelar</button>
        </form>

        <!-- Información de contacto -->
        <form id="request-access">
            <p>¿Alguna duda? Contáctenos al Email: <a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a></p>
            <br>
            <!--<p>Fecha: <?php /*echo date('m/d/Y');*/ ?></p>-->
        </form>
    </div>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Validation (keeps same behavior)
        document.getElementById("login").addEventListener("submit", function(event) {
            var emailInput = document.querySelector("input[name='email']");
            var email = emailInput.value;
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                event.preventDefault();
                Swal.fire({
                    title: 'Mensaje',
                    text: 'Por favor, ingresa una dirección de correo electrónico válida.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-confirm-button'
                    }
                }).then(() => { window.location.href = 'recoverpassword.php'; });
            }
        });

        // Simple i18n for this page
        const T = {
          es: { rec_title: 'Recuperación de Contraseña', rec_email: 'Email', rec_submit: 'Recuperar Contraseña' },
          en: { rec_title: 'Password Recovery', rec_email: 'Email', rec_submit: 'Recover Password' },
          pt: { rec_title: 'Recuperação de Senha', rec_email: 'Email', rec_submit: 'Recuperar Senha' }
        };

        function applyTranslationsRec(lang){
          const d = T[lang] || T.es;
          document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.innerHTML=d[k];});
          document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{const k=el.getAttribute('data-i18n-placeholder');if(d[k]!==undefined)el.placeholder=d[k];});
          document.documentElement.lang = lang;
        }

        document.addEventListener('DOMContentLoaded', ()=>{
          const lang = localStorage.getItem('smartshelfLang') || 'es';
          applyTranslationsRec(lang);
        });

        window.addEventListener('languageChanged', ()=>{
          const lang = localStorage.getItem('smartshelfLang') || 'es';
          applyTranslationsRec(lang);
        });
    </script>
</body>
</html>