<!--  Developed by julián González Bucheli -->
<html lang="us">
	<?php
    include "sessions.php";
	date_default_timezone_set('America/Bogota');
	?>
	<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - GORA</title>
        <link rel="icon" href="GORA.ico" type="image/x-icon">
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
                <img src="GORA.ico" alt="GORA Logo" class="logo">
                <h1 data-i18n="auth_title">Biblioteca de Contenidos Útiles</h1>
            </div>
            <div id="session-timeout-message" class="session-timeout-message" role="alert" hidden></div>
            <form id="login" action="access_success_Tasks_final.php" method="POST" autocomplete="off">
                <div class="lang-sw">
                    <button class="lb on" type="button" onclick="setLang('es')">ES</button>
                    <button class="lb" type="button" onclick="setLang('en')">EN</button>
                    <button class="lb" type="button" onclick="setLang('pt')">PT</button>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="email" data-i18n-placeholder="auth_email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" data-i18n-placeholder="auth_password" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn-login" data-i18n="auth_login">Ingresar</button>
                <br><br>
            </form>

            <form action="index.php" method="POST" autocomplete="off">
                <button type="submit" class="btn-login" data-i18n="auth_cancel">Cancelar</button>
            </form>
            <a href="recoverpassword.php" class="forgot-password" data-i18n="auth_forgot">¿Olvidaste tu contraseña?</a>

            <form id="request-access" action="requestaccessfinal.php" method="POST" autocomplete="off">
                <p data-i18n="auth_no_access">¿Sin acceso? <button type="submit" class="btn-request" data-i18n="auth_request_here">Solicitarlo aquí</button></p>
                <p class="contact-question" data-i18n="auth_questions">¿Alguna duda? Usa el Boton de WhatsApp</p>
                <p class="contact-email-line">
                    <span data-i18n="auth_contact_email">Contáctenos al Email:</span>
                    <a for="terms" href="mailto:adm@solicionespro.com" style="color: var(--accent); text-decoration: underline;">adm@solicionespro.com</a>
                    <!--<a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a>-->
                </p>
            </form>


<script>
const T={
    es:{auth_title:"Biblioteca de Contenidos Útiles",auth_login:"Ingresar",auth_cancel:"Cancelar",auth_forgot:"¿Olvidaste tu contraseña?",auth_no_access:"¿Sin acceso?",auth_request_here:"Solicitarlo aquí",auth_questions:"¿Alguna duda?",auth_contact_email:"Contáctenos al Email:",auth_email:"Email",auth_password:"Contraseña",auth_timeout:"No se detectó actividad en los últimos 3 minutos. La sesión se cerró por seguridad."},
    en:{auth_title:"Useful Content Library",auth_login:"Log In",auth_cancel:"Cancel",auth_forgot:"Forgot your password?",auth_no_access:"No access?",auth_request_here:"Request it here",auth_questions:"Any questions?",auth_contact_email:"Contact us by email:",auth_email:"Email",auth_password:"Password",auth_timeout:"No activity was detected in the last 3 minutes. Your session was closed for security."},
    pt:{auth_title:"Biblioteca de Conteúdos Úteis",auth_login:"Entrar",auth_cancel:"Cancelar",auth_forgot:"Esqueceu sua senha?",auth_no_access:"Sem acesso?",auth_request_here:"Solicite aqui",auth_questions:"Alguma dúvida?",auth_contact_email:"Contate-nos pelo Email:",auth_email:"Email",auth_password:"Senha",auth_timeout:"Nenhuma atividade foi detectada nos últimos 3 minutos. Sua sessão foi encerrada por segurança."}
};

function applyTranslations(lang){
    const d = T[lang] || T.es;
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.innerHTML=d[k];});
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{const k=el.getAttribute('data-i18n-placeholder');if(d[k]!==undefined)el.placeholder=d[k];});
    document.querySelectorAll('.lb').forEach(b=>{b.classList.toggle('on',b.textContent.trim()===lang.toUpperCase());});
    const timeoutMessage = document.getElementById('session-timeout-message');
    if (timeoutMessage && new URLSearchParams(window.location.search).get('timeout') === '1') {
        timeoutMessage.textContent = d.auth_timeout;
        timeoutMessage.hidden = false;
    }
    document.documentElement.lang = lang;
}

function setLang(lang){
    if(!T[lang]) return;
    localStorage.setItem('smartshelfLang', lang);
    applyTranslations(lang);
    window.dispatchEvent(new Event('languageChanged'));
}

document.addEventListener('DOMContentLoaded', ()=>{
    const initial = localStorage.getItem('smartshelfLang') || 'es';
    applyTranslations(initial);
});

window.addEventListener('languageChanged', ()=>{
    const lang = localStorage.getItem('smartshelfLang') || 'es';
    applyTranslations(lang);
});
</script>
    </body>
    <a href="https://wa.me/573117592209" target="_blank"
    style="position: fixed; bottom: 20px; right: 20px; background-color: #25D366; color: white; padding: 10px 20px; border-radius: 50px; font-size: 16px; text-decoration: none; display: flex; align-items: center;">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="35" height="35" style="margin-right: 10px;"></a>
    <br>
</html>
