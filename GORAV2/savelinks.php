<?php 
include "sessions.php";
include "sessionvalidation.php";
include "db_connection1.php";



$local_username = $_SESSION['email'];
$videolink = $_POST['videolink'];
$maincategory = $_POST["maincategory"];
$category = $_POST["category"];
$proforpers = $_POST["proforpers"];
$description = $_POST["description"];
$savedlink = $_SESSION['savedlink'];
$duplicatedlink = $_SESSION['duplicatedlink'];
$suscriptioninactive = $_SESSION['suscriptioninactive'];


// Verificar suscripción activa
$query = "SELECT suscriptionactive FROM videotips_app_access_list WHERE username = '$local_username'";
$activesuscription = $conn->query($query);
$suscription_row = $activesuscription->fetch_assoc();
$is_active = $suscription_row['suscriptionactive'] ?? 0; // Asegúrate de que sea un valor numérico

// Verificar si el enlace ya existe
$query1 = "SELECT * FROM videotips_videotips WHERE videolink = '$videolink' AND username = '$local_username'";
$urlduplicated = $conn->query($query1);

if ($urlduplicated->num_rows > 0) { 
    $_SESSION['duplicatedlink'] = 1;
    header("refresh:0; url=videolinkadminmodule.php");
    exit();
} 

if ($is_active == 1) {
    
    $query3 = "INSERT INTO videotips_videotips (videolink, maincategory, category,  proforpers, content, active, username) VALUES ('$videolink', '$maincategory', '$category', '$proforpers', '$description', 'Yes', '$local_username')";
    
    if ($conn->query($query3) === TRUE) {
        $_SESSION['savedlink']=1;
        header("refresh:0; url=videolinkadminmodule.php");
        exit();
    } else {
      $_SESSION['savedlink']=2;
      header("refresh:0; url=videolinkadminmodule.php");  
      exit();
    }
} else {
    
    $_SESSION['suscriptioninactive']=1;
    $_SESSION['savedlink']=0;
    header("refresh:0; url=videolinkadminmodule.php");
    exit();
}
?>