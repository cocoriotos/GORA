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
        $subject = "Requerimiento de acceso a GORA";
        $message = "Buen día $lastname, $name :  \n\n";
        $message .= "Su requerimiento ha sido exitoso. Acá la información de su solicitud \n\n";
        $message .= str_pad("Nombre", 40) . ": $name \n";
        $message .= str_pad("Apellido", 40) . ": $lastname \n";
        $message .= str_pad("Email", 40) . ": $email \n";
        $message .= str_pad("País", 40) . ": $country \n";
        $message .= str_pad("Ciudad", 40) . ": $city \n";
        $message .= str_pad("Nombre de usuario asignado", 40) . ": $email \n\n";
        $message .= "Por favor no responder éste correo \n\n";
        $message .= "Gracias por su registro, ya puede ingresar a la app desde este enlace https://solicionespro.com/GORAV2/videotrackerauth.php con su usuario $email y la contraseña que escogió. Por seguridad, nunca enviamos contraseñas por correo. \n\n";
        $message .= "Puedes también ingresar por este enlace donde podrás obtener más información importante de la aplicación así como videos tutoriales y manual del usuario https://www.youtube.com/playlist?list=PLRQ5KF9igtB2GRlHLSP6Uwx1lzy387Wz5. \n\n";
        $header = "From: adm@solicionespro.com" . "\r\n";
        $header .= "Bcc: adm@solicionespro.com\r\n";
        $header .= "X-Mailer: PHP/" . phpversion();

        $mail1 = mail($to, $subject, $message, $header);

        if ($result && $mail && $mail1) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Mensaje',
                        text: 'Su requerimiento fue enviado. Recibirá un correo con los detalles de acceso; por seguridad, la contraseña que escogió no se envía por correo.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
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