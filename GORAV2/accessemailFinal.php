<?php 
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que los campos existan
    if (!isset($_POST["Name"]) || !isset($_POST["LastName"]) || !isset($_POST["Email"]) || !isset($_POST["Country"]) || !isset($_POST["City"]) || !isset($_POST["phone"]) || !isset($_POST["password1"]) || !isset($_POST["captcha"])) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Mensaje',
                    text: '¡Todos los campos son obligatorios!',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    timer: 2000,
                    timerProgressBar: true,
                    willClose: () => {
                        window.location.href = 'requestaccessfinal.php';
                    }
                });
            });
        </script>";
        exit();
    }

    $captcha_input = (string) $_POST["captcha"];

    // Validar si el CAPTCHA existe y ha expirado
    if (!isset($_SESSION["captcha"])) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Mensaje',
                    text: 'CAPTCHA ha expirado, recarga la página.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    timer: 2000,
                    timerProgressBar: true,
                    willClose: () => {
                        window.location.href = 'requestaccessfinal.php';
                    }
                });
            });
        </script>";
        exit();
    }

    // Comparar el CAPTCHA ingresado con el almacenado en sesión
    if (strtolower($captcha_input) !== strtolower($_SESSION["captcha"])) {
        unset($_SESSION["captcha"]);
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Mensaje',
                    text: 'CAPTCHA incorrecto.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    timer: 2000,
                    timerProgressBar: true,
                    willClose: () => {
                        window.location.href = 'requestaccessfinal.php';
                    }
                });
            });
        </script>";
        exit();
    }

    // Destruir el CAPTCHA después de la verificación para evitar reutilización
    unset($_SESSION["captcha"]);

    include ("db_connection1.php");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $name = trim($_POST['Name']);
    $lastname = trim($_POST['LastName']);
    $email = trim($_POST['Email']);
    $language = (string) ($_POST['language'] ?? 'es');
    if (!in_array($language, ['es', 'en', 'pt'], true)) {
        $language = 'es';
    }
    $country = trim($_POST['Country']);
    $city = trim($_POST['City']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        exit('La dirección de correo electrónico no es válida.');
    }
    $phone = trim($_POST['phone']);
    if ($phone === '' || !preg_match('/^[0-9]+$/', $phone)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Mensaje',
                    text: 'El número telefónico debe contener solo números.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = 'requestaccessfinal.php';
                });
            });
        </script>";
        exit();
    }
    $submittedPassword = $_POST['password1'];
    if ($submittedPassword === '') {
        http_response_code(400);
        exit('La contraseña es obligatoria.');
    }
    $passwordHash = password_hash($submittedPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT 1 FROM videotips_app_access_list WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result3 = $stmt->get_result();

    if ($result3->num_rows > 0) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Mensaje',
                    text: 'Correo ya había sido registrado en la aplicación, Si olvidó la contraseña usar la opción de recuperación. Si no es su correo entonces por favor usar otro',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-confirm-button'
                    }
                }).then(() => {
                    window.location.href = 'requestaccessfinal.php';
                });
            });
        </script>";
		exit();
    } else {
        $conn->begin_transaction();
        $stmt = $conn->prepare("INSERT INTO videotips_accessrequests (name, lastname, email, country, city, password, processed, granted, phone) VALUES (?, ?, ?, ?, ?, ?, 'Yes', 'Yes', ?)");
        $stmt->bind_param("sssssss", $name, $lastname, $email, $country, $city, $passwordHash, $phone);
        $result = $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO videotips_app_access_list (name, lastname, username, email, phone, password, role, active, adm_role, suscriptionactive, terms_conditions_awareness, suscriptionkind, lastsuscriptionpaymentdate, suscriptiondaysleft, trialdaysleft) VALUES (?, ?, ?, ?, ?, ?, 'user', 1, 0, 1, 'Yes', 'Trial', CURDATE(), 0, 0)");
        $stmt->bind_param("ssssss", $name, $lastname, $email, $email, $phone, $passwordHash);
        $result1 = $stmt->execute();

        if (!$result || !$result1) {
            $conn->rollback();
            error_log('Registration insert failed: ' . $conn->error);
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Mensaje',
                        text: 'No fue posible guardar la información de registro. Intente nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = 'requestaccessfinal.php';
                    });
                });
            </script>";
            exit();
        }
        $conn->commit();

        $stmt = $conn->prepare("INSERT INTO videotips_suscription_payments (username, active, freeregistrationdate) SELECT email, active, registrationdate FROM videotips_app_access_list WHERE username = ?");
        $stmt->bind_param("s", $email);
        $result2 = $stmt->execute();

        $query3 = "UPDATE videotips_app_access_list t1 JOIN videotips_suscription_payments t2 ON t1.username = t2.username SET t1.suscription_package = t2.suscription_package;";
        $result3 = $conn->query($query3);

        $stmt = $conn->prepare("INSERT INTO videotips_maincategory (maincategory, username) values ('Enlaces Utiles', ?)");
        $stmt->bind_param("s", $email);
        $result4 = $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO videotips_viodetipscategory (category, maincategory, username) values ('Favoritos','Enlaces Utiles', ?)");
        $stmt->bind_param("s", $email);
        $result5 = $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO videotips_videotips (videolink, maincategory, category, content, active, username) values ('https://www.youtube.com/@GORAColOficial','Enlaces Utiles', 'Favoritos', 'Canal de yourtube GORA para ver los Manuales y contenidos de la APlicación', 1, ?)");
        $stmt->bind_param("s", $email);
        $result6 = $stmt->execute();

        /*Destination email information*/
        $to = "adm@solicionespro.com";
        $subject = "Urgent GORA access request";
        $message = "Good day Admin Team:  \n\n";
        $message .= "This user is requesting tool access, please response ASAP \n\n";
        $message .= str_pad("Member name", 40) . ": $name \n";
        $message .= str_pad("Member Last name", 40) . ": $lastname \n";
        $message .= str_pad("Member email", 40) . ": $email \n\n";
        $message .= "Thank you for your support \n\n";
        $message .= "Email Application sender";
        $message .= "Dear Customer, you will be receiving Tool admin message advising if was granted or denied your request \n\n";
        $message .= "Email Application sender";
        $header = "From: adm@solicionespro.com" . "\r\n";
        $header .= "Bcc: adm@solicionespro.com\r\n";
        $header .= "Reply-To: noreply@solicionespro.com" . "\r\n";
        $header .= "X-Mailer: PHP/" . phpversion();

        //Sending Email 
        $mail = mail($to, $subject, $message, $header);

        $to = "$email, adm@solicionespro.com";
        $registrationEmail = [
            'es' => [
                'subject' => 'Requerimiento de acceso a GORA',
                'greeting' => "Buen día $lastname, $name:\n\n",
                'details' => "Su solicitud fue recibida correctamente. Estos son los datos registrados:\n\nNombre: $name\nApellido: $lastname\nEmail: $email\nPaís: $country\nCiudad: $city\nUsuario: $email\n\n",
                'instructions' => "Por favor, no responda a este correo.\n\nYa puede ingresar a la aplicación desde https://solicionespro.com/videotrackerauth.php con el usuario indicado y la contraseña que escogió. Por seguridad, nunca enviamos contraseñas por correo.\n\n"
            ],
            'en' => [
                'subject' => 'GORA access request',
                'greeting' => "Hello $name $lastname,\n\n",
                'details' => "Your request was received successfully. These are the details you provided:\n\nName: $name\nLast name: $lastname\nEmail: $email\nCountry: $country\nCity: $city\nUsername: $email\n\n",
                'instructions' => "Please do not reply to this email.\n\nYou can now access the application at https://solicionespro.com/videotrackerauth.php using the username above and the password you chose. For your security, we never send passwords by email.\n\n"
            ],
            'pt' => [
                'subject' => 'Solicitação de acesso ao GORA',
                'greeting' => "Olá $name $lastname,\n\n",
                'details' => "Sua solicitação foi recebida com sucesso. Estes são os dados informados:\n\nNome: $name\nSobrenome: $lastname\nEmail: $email\nPaís: $country\nCidade: $city\nUsuário: $email\n\n",
                'instructions' => "Por favor, não responda a este email.\n\nVocê já pode acessar o aplicativo em https://solicionespro.com/videotrackerauth.php usando o usuário informado e a senha que escolheu. Por segurança, nunca enviamos senhas por email.\n\n"
            ]
        ][$language];
        $subject = $registrationEmail['subject'];
        $message = $registrationEmail['greeting'] . $registrationEmail['details'] . $registrationEmail['instructions'];
        $header = "From: adm@solicionespro.com" . "\r\n";
        $header .= "Bcc: adm@solicionespro.com\r\n";
        $header .= "X-Mailer: PHP/" . phpversion();

        $mail1 = mail($to, $subject, $message, $header);

        if ($result && $mail && $mail1) {
            $confirmationUi = [
                'es' => ['Mensaje', 'Su solicitud fue recibida. Recibirá un correo con los detalles de acceso; por seguridad, la contraseña no se envía por correo.', 'Aceptar'],
                'en' => ['Message', 'Your request was received. You will receive an email with your access details; for your security, your password is not sent by email.', 'OK'],
                'pt' => ['Mensagem', 'Sua solicitação foi recebida. Você receberá um email com os dados de acesso; por segurança, sua senha não será enviada por email.', 'OK']
            ][$language];
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: " . json_encode($confirmationUi[0], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ",
                        text: " . json_encode($confirmationUi[1], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ",
                        icon: 'success',
                        confirmButtonText: " . json_encode($confirmationUi[2], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ",
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'custom-swal-confirm-button'
                        }
                    }).then(() => {
                        window.location.href = 'videotrackerauth.php';
                    });
                });
            </script>";
			exit();
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Mensaje',
                        text: 'Requerimiento no enviado, por favor trate nuevamente o más tarde.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'custom-swal-confirm-button'
                        }
                    }).then(() => {
                        window.location.href = 'closetaskscon.php';
                    });
                });
            </script>";
			exit();
        }
    }
}
?>