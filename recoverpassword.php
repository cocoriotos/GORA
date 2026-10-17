<!-- Developed by Julián González Bucheli -->
<html lang="es">
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

        <div class="lang-sw reset-language">
            <button class="lb" type="button" onclick="setLang('es')">ES</button>
            <button class="lb" type="button" onclick="setLang('en')">EN</button>
            <button class="lb" type="button" onclick="setLang('pt')">PT</button>
        </div>

        <!-- Formulario de recuperación de contraseña -->
        <form id="login" action="recoverpasswordemailFinal.php" method="POST" autocomplete="off">
            <input type="hidden" name="language" id="recovery-language" value="es">
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
            <p><span data-i18n="rec_questions">¿Alguna duda? Contáctenos al Email:</span> <a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a></p>
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
                const language = localStorage.getItem('smartshelfLang') || 'es';
                const messages = T[language] || T.es;
                Swal.fire({
                    title: messages.rec_message,
                    text: messages.rec_invalid,
                    icon: 'error',
                    confirmButtonText: messages.rec_ok,
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
          es: { rec_title: 'Recuperación de Contraseña', rec_email: 'Email', rec_submit: 'Recuperar Contraseña', rec_cancel: 'Cancelar', rec_questions: '¿Alguna duda? Contáctenos al Email:', rec_message: 'Mensaje', rec_invalid: 'Por favor, ingresa una dirección de correo electrónico válida.', rec_ok: 'Aceptar' },
          en: { rec_title: 'Password Recovery', rec_email: 'Email', rec_submit: 'Recover Password', rec_cancel: 'Cancel', rec_questions: 'Questions? Contact us by email:', rec_message: 'Message', rec_invalid: 'Please enter a valid email address.', rec_ok: 'OK' },
          pt: { rec_title: 'Recuperação de Senha', rec_email: 'Email', rec_submit: 'Recuperar Senha', rec_cancel: 'Cancelar', rec_questions: 'Dúvidas? Entre em contato pelo email:', rec_message: 'Mensagem', rec_invalid: 'Digite um endereço de email válido.', rec_ok: 'OK' }
        };

        function applyTranslationsRec(lang){
          const selectedLanguage = T[lang] ? lang : 'es';
          const d = T[selectedLanguage];
          document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.innerHTML=d[k];});
          document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{const k=el.getAttribute('data-i18n-placeholder');if(d[k]!==undefined)el.placeholder=d[k];});
          document.querySelectorAll('.lang-sw .lb').forEach(button=>button.classList.toggle('on', button.textContent.trim() === selectedLanguage.toUpperCase()));
          document.getElementById('recovery-language').value = selectedLanguage;
          document.documentElement.lang = selectedLanguage;
        }

        function setLang(lang) {
          if (!T[lang]) return;
          localStorage.setItem('smartshelfLang', lang);
          applyTranslationsRec(lang);
          window.dispatchEvent(new Event('languageChanged'));
        }

        document.addEventListener('DOMContentLoaded', ()=>{
          const lang = localStorage.getItem('smartshelfLang') || 'es';
          applyTranslationsRec(lang);
          document.querySelector('#login + form .btn-login').textContent = T[lang].rec_cancel;
        });

        window.addEventListener('languageChanged', ()=>{
          const lang = localStorage.getItem('smartshelfLang') || 'es';
          applyTranslationsRec(lang);
          document.querySelector('#login + form .btn-login').textContent = T[lang].rec_cancel;
        });
    </script>
</body>
</html>