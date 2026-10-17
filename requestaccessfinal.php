<html lang="us">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Acceso - CORA</title>
    <link rel="icon" href="GORA.ico" type="image/x-icon">
    <script src="head.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_sheet_auth.css?v=20260917">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <h1 data-i18n="req_title">Solicitud de Acceso</h1>
            <?php include __DIR__ . "/social_links.php"; ?>
        </div>

        <!-- Formulario de solicitud de acceso -->
        <div class="request-access-stack">
        <form id="login" action="accessemailFinal.php" method="POST" autocomplete="off" onsubmit="return validateForm()">
            <div class="lang-sw">
                <button class="lb on" type="button" onclick="setLang('es')">ES</button>
                <button class="lb" type="button" onclick="setLang('en')">EN</button>
                <button class="lb" type="button" onclick="setLang('pt')">PT</button>
            </div>
            <input type="hidden" name="language" id="registration-language" value="es">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="Name" data-i18n-placeholder="req_name" placeholder="Nombre" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="LastName" data-i18n-placeholder="req_lastname" placeholder="Apellido" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="Email" autocomplete="username" data-i18n-placeholder="req_email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <i class="fas fa-globe"></i>
                <input type="text" name="Country" data-i18n-placeholder="req_country" placeholder="País de residencia" required>
            </div>
            <div class="input-group">
                <i class="fas fa-city"></i>
                <input type="text" name="City" data-i18n-placeholder="req_city" placeholder="Ciudad de residencia" required>
            </div>
            <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" data-i18n-placeholder="req_phone" placeholder="Número telefónico" inputmode="numeric" pattern="[0-9]+" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password1" autocomplete="new-password" data-i18n-placeholder="req_password" placeholder="Contraseña" required>
            </div>
            <img src="captcha.php" alt="CAPTCHA" class="captcha-image">
            <input type="text" name="captcha" data-i18n-placeholder="req_captcha" placeholder="Ingrese el CAPTCHA" required>
            <div class="terms">
                <input type="checkbox" id="terms" onclick="toggleSubmitButton()" required>
                <label for="terms" >Acepto los <a href="TermsConditions.php" target="_blank" style="color: var(--accent) !important; text-decoration: underline;">términos y condiciones</a></label>
            </div>
            <button type="submit" class="btn-login" id="loginbutton" data-i18n="req_send" disabled>Enviar</button>
            <br><br>
        </form>

        <div class="cancel-action">
            <button type="button" class="btn-login" data-i18n="req_cancel" onclick="window.location.href = 'index.php';">Cancelar</button>
        </div>

        <form id="request-access">
            <p class="contact-question" data-i18n="req_questions">¿Alguna duda? Usa el Boton de WhatsApp</p>
            <p class="contact-email-line">
                <span data-i18n="req_contact_email">Contáctenos al Email:</span>
                <a for="terms" href="mailto:adm@solicionespro.com" style="color: var(--accent); text-decoration: underline;">adm@solicionespro.com</a>
            </p>
        </form>
        </div>
    </div>

    <script>
        function toggleSubmitButton() {
            const submitButton = document.getElementById("loginbutton");
            const termsCheckbox = document.getElementById("terms");
            submitButton.disabled = !termsCheckbox.checked;
        }

        function validateForm() {
            const inputs = document.querySelectorAll("input[required]");
            let isValid = true;

            inputs.forEach(input => {
                // Elimina espacios en blanco al inicio y al final
                input.value = input.value.trim();

                // Verifica si el campo está vacío o solo contiene espacios en blanco
                if (input.value === "") {
                    isValid = false;
                    input.classList.add("error"); // Agrega una clase de error para resaltar el campo
                } else {
                    input.classList.remove("error"); // Remueve la clase de error si el campo es válido
                }
            });

            if (!isValid) {
                Swal.fire({
                    title: 'Error',
                    text: 'Por favor, completa todos los campos requeridos con información.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-confirm-button'
                    }
                });
                return false; // Evita que el formulario se envíe
            }

            return true; // Permite que el formulario se envíe si todos los campos son válidos
        }
    </script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById("login").addEventListener("submit", function(event) {
            var emailInput = document.querySelector("input[name='Email']");
            var email = emailInput.value;

            // Expresión regular para validar el formato de un correo electrónico
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(email)) {
                // Evita que el formulario se envíe
                event.preventDefault();

                // Muestra el SweetAlert
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
                });
            }
        });
    </script>
    <script>
const T={
    es:{req_title:"Solicitud de Acceso",req_name:"Nombre",req_lastname:"Apellido",req_email:"Correo electrónico",req_country:"País de residencia",req_city:"Ciudad de residencia",req_phone:"Número telefónico",req_password:"Contraseña",req_captcha:"Ingrese el CAPTCHA",req_terms:"Acepto los <a href='TermsConditions.php' target='_blank'>términos y condiciones</a>",req_send:"Enviar",req_cancel:"Cancelar",req_questions:"¿Alguna duda?",req_contact_email:"Contáctenos al Email:"},
    en:{req_title:"Access Request",req_name:"Name",req_lastname:"Last Name",req_email:"Email",req_country:"Country of residence",req_city:"City of residence",req_phone:"Phone number",req_password:"Password",req_captcha:"Enter the CAPTCHA",req_terms:"I accept the <a href='TermsConditions.php' target='_blank'>terms and conditions</a>",req_send:"Send",req_cancel:"Cancel",req_questions:"Any questions?",req_contact_email:"Contact us by email:"},
    pt:{req_title:"Solicitação de Acesso",req_name:"Nome",req_lastname:"Sobrenome",req_email:"Email",req_country:"País de residência",req_city:"Cidade de residência",req_phone:"Número de telefone",req_password:"Senha",req_captcha:"Digite o CAPTCHA",req_terms:"Aceito os <a href='TermsConditions.php' target='_blank'>termos e condições</a>",req_send:"Enviar",req_cancel:"Cancelar",req_questions:"Alguma dúvida?",req_contact_email:"Contate-nos pelo Email:"}
};

function applyTranslations(lang){
    const d = T[lang] || T.es;
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.innerHTML=d[k];});
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{const k=el.getAttribute('data-i18n-placeholder');if(d[k]!==undefined)el.placeholder=d[k];});
    document.querySelectorAll('.lb').forEach(b=>{b.classList.toggle('on',b.textContent.trim()===lang.toUpperCase());});
    document.getElementById('registration-language').value = lang;
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
    <a href="https://wa.me/573117592209" target="_blank"
    style="position: fixed; bottom: 20px; right: 20px; background-color: #25D366; color: white; padding: 10px 20px; border-radius: 50px; font-size: 16px; text-decoration: none; display: flex; align-items: center;">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="35" height="35" style="margin-right: 10px;"></a>
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

</body>
</html>