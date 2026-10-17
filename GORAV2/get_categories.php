<?php

include "sessionvalidation.php";
include('db_connection1.php'); // Asegúrate de incluir tu conexión a la base de datos.
    $local_username = $_SESSION['email'];
	$savedlink = $_SESSION['savedlink'];
	$duplicatedlink = $_SESSION['duplicatedlink'];
	$updatedlink = $_SESSION['updatedlink'];
	$deletedlink = $_SESSION['deletedlink'];
	$sessiontimeoutreached = $_SESSION['sessiontimeoutreached'];
	$copytoclipboard = $_SESSION['copytoclipboard'];
	$videoUrl = $_SESSION['videoUrl'];
	$embedUrl = $_SESSION['embedUrl'];
	$click = $_SESSION['click'];
	
if (isset($_GET['maincategory'])) {
    $maincategory = $_GET['maincategory'];
    /*$username = $_SESSION['username']; // Asegúrate de obtener el username de la sesión.*/

    $SQLSELECT = "SELECT DISTINCT(category) FROM videotips_viodetipscategory WHERE maincategory = ? AND username = ? ORDER BY category ASC";
    $stmt = $conn->prepare($SQLSELECT);
    $stmt->bind_param("ss", $maincategory, $local_username);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }

    // Devuelve las categorías como JSON
    echo json_encode($categories);
}
?>
