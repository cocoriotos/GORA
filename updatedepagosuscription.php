<?php 
include "db_connection1.php"; 

$id = $_POST['id'];
$username = $_POST['username'];
$active = $_POST['active'];
$suscriptionactive = $_POST['suscriptionactive'];
$suscriptiondaysleft = $_POST['suscriptiondaysleft'];
$suscriptionpayed = $_POST['suscriptionpayed'];
$registrationdate = $_POST['registrationdate'] ?? '';
$lastsuscriptionpaymentdate = $_POST['lastsuscriptionpaymentdate'];
$suscriptionkind = $_POST['suscriptionkind'];   
$packages = $_POST['packages'];
$proofofpurchasenumber = $_POST['proofofpurchasenumber'];

if (!is_string($registrationdate)
    || !preg_match('/\A(\d{4})-(\d{2})-(\d{2})\z/', $registrationdate, $registrationDateParts)
    || !checkdate((int) $registrationDateParts[2], (int) $registrationDateParts[3], (int) $registrationDateParts[1])) {
    http_response_code(400);
    exit('La fecha de inscripción no es válida.');
}


$sql = "UPDATE videotips_suscription_payments SET currentpaid = currentpaid + 1, paymentcounter = paymentcounter + 1, lastpaymentdate = '$lastsuscriptionpaymentdate', suscription_package = '$packages', proofofpurchasenumber = '$proofofpurchasenumber' WHERE username = '$username'";
$result = $conn->query($sql);

$sql1 = "UPDATE videotips_app_access_list SET active = '$active', suscriptionactive = '$suscriptionactive', suscriptionpayed = '$suscriptionpayed', registrationdate = '$registrationdate', lastsuscriptionpaymentdate = '$lastsuscriptionpaymentdate', suscriptionkind = '$suscriptionkind' where username = '$username'";
$result1 = $conn->query($sql1);

if ($result && $result1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Suscripción Actualizada Correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'AppMgmt.php';
        }
      });
    });
    </script>";
    exit(); // Solo para asegurarte de que no se ejecuta más PHP
  }  
  else{
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            title: 'Mensaje',
            text: 'Suscripción Actualizada Correctamente',
            icon: 'success',
            confirmButtonText: 'Aceptar',
            customClass: {
              popup: 'custom-swal-popup',
              title: 'custom-swal-title',
              content: 'custom-swal-content',
              confirmButton: 'custom-swal-confirm-button'
            }
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = 'AppMgmt.php';
            }
          });
        });
        </script>";
        exit(); // Solo para asegurarte de que no se ejecuta más PHP
      }  
?>