<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
$name = $_POST["name"];
$email = $_POST["email"];
$message = $_POST["message"];
$captcha_input = $_POST["captcha"];
$captchaok = 1;

      


if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (!isset($_POST["name"]) || !isset($_POST["email"]) || !isset($_POST["message"]) || !isset($_POST["captcha"])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
		echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
		Swal.fire({
					title: 'Mensaje',
                text: '¡Todos los campos son obligatorios!',
                icon: 'error',
                confirmButtonText: 'Aceptar',
                timer: 1500, // 1.5 segundos antes de redirigir
                timerProgressBar: true, // Barra de progreso
                willClose: () => {
                  window.location.href = 'index.php'; // Redirección automática
                }
              });
          });
         </script>";
         $captchaok = 0;
         include ("index.php");
         exit();
  }
  if (!isset($_SESSION["captcha"])) {
		echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
      echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
                  title: 'Mensaje',
                  text: 'CAPTCHA ha expirado, recarga la página.',
                  icon: 'error',
                  confirmButtonText: 'Aceptar',
                  timer: 1500, // 1.5 segundos antes de redirigir
                  timerProgressBar: true, // Barra de progreso
                  willClose: () => {
                    window.location.href = 'index.php'; // Redirección automática
                  }
                });
            });
           </script>";
           $captchaok = 0;
           include ("index.php");
           exit();
    }
    // Comparar el CAPTCHA ingresado con el almacenado en sesión
    if (strtolower($captcha_input) !== strtolower($_SESSION["captcha"])) {
      echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
      echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Mensaje',
            text: 'CAPTCHA incorrecto.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
            timer: 1500, // 1.5 segundos antes de redirigir
            timerProgressBar: true, // Barra de progreso
            willClose: () => {
              window.location.href = 'index.php'; // Redirección automática
            }
            });
          });
         </script>";
         $captchaok = 0;
         include ("index.php");
         exit();
      }
      // Destruir el CAPTCHA después de la verificación para evitar reutilización
      unset($_SESSION["captcha"]);
      if ($captchaok = 1) {
        $to1 = "cocoriotos@hotmail.com, adm@solicionespro.com";
        $subject1 = "Nuevo Mensaje de SmartShelf";
        $body1 = "Nombre: $name\nCorreo: $email\nMensaje: $message";
        $headers1 = "From: adm@solicionespro.com";
      
        $mail3 = mail($to1, $subject1, $body1, $headers1);
  
          // Alerta de éxito y redirección
          /*echo "El Mensaje fué enviado correctamente";*/
          echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
          echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
              Swal.fire({
                  title: 'Mensaje',
                  text: 'Gracias por contactarnos, estamos revisando tu mensaje y te responderemos pronto.',
                  icon: 'success',
                  confirmButtonText: 'Aceptar',
                  timer: 1500, // 1.5 segundos antes de redirigir
                  timerProgressBar: true, // Barra de progreso
                  willClose: () => {
                    window.location.href = 'index.php'; // Redirección automática
                  }
                  });
                });
              </script>";
              exit();
      }
}
?>
