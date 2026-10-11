<?php 
// defining and loading  data fields
include "sessions.php";
include ("db_connection1.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Validar que el campo no esté vacío
    if (empty($email)) {
        die("El campo de correo electrónico es requerido.");
    }

    // Validar el formato del correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Por favor, ingresa una dirección de correo electrónico válida.");
    } else {
		$email = trim($_POST['email']);
		$stmt = $conn->prepare("SELECT 1 FROM videotips_app_access_list WHERE email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result3 = $stmt->get_result();

		if ($result3->num_rows > 0) {
			$to = "adm@solicionespro.com";
			$subject = "Password recovery requested";
			$message = "A password recovery was requested for account: $email\n";
			$header = "From: adm@solicionespro.com\r\n";
			$header .= "Reply-To: noreply@solicionespro.com\r\n";
			$header .= "Content-Type: text/plain; charset=UTF-8\r\n";
			if (!mail($to, $subject, $message, $header)) {
				error_log('Password recovery notification email failed.');
			}
		}

			// Mostrar mensaje con SweetAlert2
			echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
			echo "<script>
				document.addEventListener('DOMContentLoaded', function() {
					Swal.fire({
						title: 'Mensaje',
						text: 'Si el correo está registrado, el equipo de soporte recibió la solicitud. Por seguridad, nunca enviamos contraseñas por correo.',
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
		}
	}
?>