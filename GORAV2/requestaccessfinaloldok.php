<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
?>
<html lang="us">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Acceso - SmartShelf</title>
    <link rel="icon" href="SSCircleBackgroundWhite.ico" type="image/x-icon">
    <script src="head.js" defer></script>
    <script src="tab-context.js?v=20261029-1" defer></script>
    <script src="password-visibility.js?v=<?php echo filemtime(__DIR__ . '/password-visibility.js'); ?>" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_sheet_auth.css">
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
            <img src="SmartShelfUsefulContentLibraryDarrkLightGreen.ico" alt="SmartShelf Logo" class="logo">
            <h1>Solicitud de Acceso</h1>
            <?php include __DIR__ . "/social_links.php"; ?>
        </div>

        <!-- Formulario de solicitud de acceso -->
        <form id="login" action="accessemailFinal.php" method="POST" autocomplete="off" onsubmit="return validateForm()">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="Name" placeholder="Nombre" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="LastName" placeholder="Apellido" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="text" name="Email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <i class="fas fa-globe"></i>
                <input type="text" name="Country" placeholder="País de residencia" required>
            </div>
            <div class="input-group">
                <i class="fas fa-city"></i>
                <input type="text" name="City" placeholder="Ciudad de residencia" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password1" placeholder="Contraseña" required>
            </div>

            <div class="terms">
                <input type="checkbox" id="terms" onclick="toggleSubmitButton()" required>
                <label for="terms">Acepto los <a href="TermsConditions.php" target="_blank">términos y condiciones</a></label>
            </div>
            <button type="submit" class="btn-login" id="loginbutton" disabled>Enviar</button>
        </form>

        <form action="index.php" method="POST" autocomplete="off">
            <button type="submit" class="btn-login">Cancelar</button>
        </form>

        <form id="request-access">
            <p>¿Alguna duda? Contáctenos al Email: <a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a></p>
        </form>
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
</body>
</html>