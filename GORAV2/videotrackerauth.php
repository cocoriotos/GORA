<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
?>
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
        <link rel="manifest" href="gora.webmanifest">
        <link rel="apple-touch-icon" href="GORA.png">
        <meta name="theme-color" content="#050814">
        <script src="head.js?v=<?php echo time(); ?>" defer></script>
        <script src="tab-context.js?v=20261029-1" defer></script>
        <script src="password-visibility.js?v=<?php echo filemtime(__DIR__ . '/password-visibility.js'); ?>" defer></script>
        <script src="active-session-login.js?v=<?php echo filemtime(__DIR__ . '/active-session-login.js'); ?>" defer></script>
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
                <?php include __DIR__ . "/social_links.php"; ?>
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
                    <input type="email" name="email" autocomplete="username" data-i18n-placeholder="auth_email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" autocomplete="current-password" data-i18n-placeholder="auth_password" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn-login" data-i18n="auth_login">Ingresar</button>
                <br><br>
            </form>

            <button type="button" id="install-app" class="btn-login install-app-button" data-i18n="auth_install">Instalar GORA</button>
            <p id="install-instructions" class="install-instructions" role="status" aria-live="polite" hidden></p>

            <form action="index.php" method="POST" autocomplete="off">
                <button type="submit" class="btn-login" data-i18n="auth_cancel">Cancelar</button>
            </form>
            <a href="recoverpassword.php" class="forgot-password" data-i18n="auth_forgot">¿Olvidaste tu contraseña?</a>

            <div id="request-access">
                <p><a href="requestaccessfinal.php" class="btn-request" data-i18n="auth_no_access">¿Sin acceso?</a></p>
                <p class="contact-question" data-i18n="auth_questions">¿Alguna duda? Usa el Boton de WhatsApp</p>
                <p class="contact-email-line">
                    <span data-i18n="auth_contact_email">Contáctenos al Email:</span>
                    <a for="terms" href="mailto:adm@solicionespro.com" style="color: var(--accent); text-decoration: underline;">adm@solicionespro.com</a>
                    <!--<a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a>-->
                </p>
            </div>


<script>
const T={
    es:{auth_title:"Biblioteca de Contenidos Útiles",auth_login:"Ingresar",auth_cancel:"Cancelar",auth_forgot:"¿Olvidaste tu contraseña?",auth_no_access:"¿Sin acceso?",auth_questions:"¿Alguna duda?",auth_contact_email:"Contáctenos al Email:",auth_email:"Email",auth_password:"Contraseña",auth_timeout:"No se detectó actividad en los últimos 10 minutos. La sesión se cerró por seguridad.",auth_invalid:"Usuario o contraseña incorrectos.",auth_active:"Este usuario ya tiene una sesión activa. Cierre la otra sesión o espere 10 minutos de inactividad.",auth_session_invalid:"La sesión ya no está activa. Inicie sesión nuevamente.",auth_session_replaced:"Esta sesión se cerró porque se inició una nueva sesión en otra pestaña o dispositivo."},
    en:{auth_title:"Useful Content Library",auth_login:"Log In",auth_cancel:"Cancel",auth_forgot:"Forgot your password?",auth_no_access:"No access?",auth_questions:"Any questions?",auth_contact_email:"Contact us by email:",auth_email:"Email",auth_password:"Password",auth_timeout:"No activity was detected in the last 10 minutes. Your session was closed for security.",auth_invalid:"Incorrect email or password.",auth_active:"This user already has an active session. Sign out from the other session or wait 10 minutes for it to expire.",auth_session_invalid:"This session is no longer active. Please log in again.",auth_session_replaced:"This session was closed because a new session was started in another tab or device."},
    pt:{auth_title:"Biblioteca de Conteúdos Úteis",auth_login:"Entrar",auth_cancel:"Cancelar",auth_forgot:"Esqueceu sua senha?",auth_no_access:"Sem acesso?",auth_questions:"Alguma dúvida?",auth_contact_email:"Contate-nos pelo Email:",auth_email:"Email",auth_password:"Senha",auth_timeout:"Nenhuma atividade foi detectada nos últimos 10 minutos. Sua sessão foi encerrada por segurança.",auth_invalid:"Email ou senha incorretos.",auth_active:"Este usuário já tem uma sessão ativa. Encerre a outra sessão ou aguarde 10 minutos de inatividade.",auth_session_invalid:"Esta sessão não está mais ativa. Faça login novamente.",auth_session_replaced:"Esta sessão foi encerrada porque uma nova sessão foi iniciada em outra aba ou dispositivo."}
};

Object.assign(T.es, {
    auth_install:"Instalar GORA",
    auth_install_ios_safari:"En Safari: toca Compartir y selecciona “Añadir a pantalla de inicio”.",
    auth_install_ios_browser:"Abre esta página en Safari, toca Compartir y selecciona “Añadir a pantalla de inicio”.",
    auth_install_android:"Abre el menú ⋮ del navegador y selecciona “Instalar aplicación” o “Añadir a pantalla principal”.",
    auth_install_safari_mac:"En Safari, ve a Archivo > Añadir al Dock para crear el acceso directo.",
    auth_install_firefox:"Firefox no admite la instalación directa desde este botón. Abre GORA en Chrome o Edge y selecciona “Instalar GORA” en el menú del navegador.",
    auth_install_desktop:"Abre el menú del navegador y selecciona “Instalar GORA” o “Crear acceso directo”.",
    auth_install_done:"GORA se instaló correctamente."
});
Object.assign(T.en, {
    auth_install:"Install GORA",
    auth_install_ios_safari:"In Safari, tap Share and select “Add to Home Screen”.",
    auth_install_ios_browser:"Open this page in Safari, tap Share and select “Add to Home Screen”.",
    auth_install_android:"Open your browser’s ⋮ menu and select “Install app” or “Add to Home screen”.",
    auth_install_safari_mac:"In Safari, go to File > Add to Dock to create the shortcut.",
    auth_install_firefox:"Firefox does not support direct installation from this button. Open GORA in Chrome or Edge and select “Install GORA” from the browser menu.",
    auth_install_desktop:"Open your browser menu and select “Install GORA” or “Create shortcut”.",
    auth_install_done:"GORA was installed successfully."
});
Object.assign(T.pt, {
    auth_install:"Instalar GORA",
    auth_install_ios_safari:"No Safari, toque em Compartilhar e selecione “Adicionar à Tela de Início”.",
    auth_install_ios_browser:"Abra esta página no Safari, toque em Compartilhar e selecione “Adicionar à Tela de Início”.",
    auth_install_android:"Abra o menu ⋮ do navegador e selecione “Instalar aplicativo” ou “Adicionar à tela inicial”.",
    auth_install_safari_mac:"No Safari, acesse Arquivo > Adicionar ao Dock para criar o atalho.",
    auth_install_firefox:"O Firefox não oferece instalação direta por este botão. Abra o GORA no Chrome ou Edge e selecione “Instalar GORA” no menu do navegador.",
    auth_install_desktop:"Abra o menu do navegador e selecione “Instalar GORA” ou “Criar atalho”.",
    auth_install_done:"O GORA foi instalado com sucesso."
});

let deferredInstallPrompt = null;
let installMessageKey = '';
const installButton = document.getElementById('install-app');
const installInstructions = document.getElementById('install-instructions');

function showInstallInstructions(key){
    installMessageKey = key;
    const lang = localStorage.getItem('smartshelfLang') || 'es';
    installInstructions.textContent = (T[lang] || T.es)[key];
    installInstructions.hidden = false;
}

function getInstallInstructionsKey(){
    const userAgent = navigator.userAgent.toLowerCase();
    const isAppleMobile = /iphone|ipad|ipod/.test(userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isSafari = /safari/.test(userAgent) && !/crios|fxios|edgios|opios/.test(userAgent);

    if(isAppleMobile) return isSafari ? 'auth_install_ios_safari' : 'auth_install_ios_browser';
    if(/android/.test(userAgent)) return 'auth_install_android';
    if(/macintosh/.test(userAgent) && isSafari) return 'auth_install_safari_mac';
    if(/firefox|fxios/.test(userAgent)) return 'auth_install_firefox';
    return 'auth_install_desktop';
}

window.addEventListener('beforeinstallprompt', event=>{
    event.preventDefault();
    deferredInstallPrompt = event;
});

installButton.addEventListener('click', async ()=>{
    if(deferredInstallPrompt){
        const promptEvent = deferredInstallPrompt;
        deferredInstallPrompt = null;
        try{
            await promptEvent.prompt();
            const choice = await promptEvent.userChoice;
            if(choice.outcome === 'accepted'){
                installInstructions.hidden = true;
                installMessageKey = '';
            }
        }catch(error){
            console.error('GORA installation prompt failed:', error);
            showInstallInstructions(getInstallInstructionsKey());
        }
        return;
    }

    showInstallInstructions(getInstallInstructionsKey());
});

window.addEventListener('appinstalled', ()=>{
    installButton.hidden = true;
    showInstallInstructions('auth_install_done');
});

if(window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true){
    installButton.hidden = true;
}

if('serviceWorker' in navigator){
    navigator.serviceWorker.register('./gora-service-worker.js')
        .catch(error=>console.error('GORA service worker registration failed:', error));
}

function applyTranslations(lang){
    const d = T[lang] || T.es;
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.innerHTML=d[k];});
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{const k=el.getAttribute('data-i18n-placeholder');if(d[k]!==undefined)el.placeholder=d[k];});
    document.querySelectorAll('.lb').forEach(b=>{b.classList.toggle('on',b.textContent.trim()===lang.toUpperCase());});
    if(installMessageKey) installInstructions.textContent = d[installMessageKey];
    const timeoutMessage = document.getElementById('session-timeout-message');
    if (timeoutMessage && new URLSearchParams(window.location.search).get('timeout') === '1') {
        timeoutMessage.textContent = d.auth_timeout;
        timeoutMessage.hidden = false;
    }
    if (timeoutMessage && new URLSearchParams(window.location.search).get('login') === 'failed') {
        timeoutMessage.textContent = d.auth_invalid;
        timeoutMessage.hidden = false;
    }
    if (timeoutMessage && new URLSearchParams(window.location.search).get('login') === 'active') {
        timeoutMessage.textContent = d.auth_active;
        timeoutMessage.hidden = false;
    }
    if (timeoutMessage && new URLSearchParams(window.location.search).get('login') === 'session_invalid') {
        timeoutMessage.textContent = d.auth_session_invalid;
        timeoutMessage.hidden = false;
    }
    if (timeoutMessage && new URLSearchParams(window.location.search).get('login') === 'session_replaced') {
        timeoutMessage.textContent = d.auth_session_replaced;
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
