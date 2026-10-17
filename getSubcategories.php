<?php
include "sessionvalidation.php";
include "db_connection1.php";
$local_username = $_SESSION['email'];
$maincategory = $_GET['maincategory'];
$current_category = isset($_GET['current_category']) ? $_GET['current_category'] : '';

if (!empty($maincategory)) {
    $query = "SELECT DISTINCT category FROM videotips_viodetipscategory 
              WHERE maincategory = '$maincategory' AND username = '$local_username' 
              ORDER BY category ASC";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $selected = ($row['category'] == $current_category) ? "selected" : "";
        echo "<option value='{$row['category']}' $selected>{$row['category']}</option>";
    }
}
?>
