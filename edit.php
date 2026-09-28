<?php 
include "sessions.php";
include "sessionvalidation.php";
$id = $_GET['id'];
$videolink = $_GET['videolink'];
$local_username = $_SESSION['email'];
$deletedlink = $_SESSION['deletedlink'];
$updatedlink = $_SESSION['updatedlink'];
$sessiontimeoutreached = $_SESSION['sessiontimeoutreached'];
$name = $_SESSION['name'];
include "headeredit.php";
include "db_connection1.php";
?>

<head>	
    <script src="head.js" defer></script>
    <link rel="stylesheet" href="style_sheet_ops.css"/>
    <script src="Popper/popper.min.js"></script>
    <script src="plugins/sweetalert/sweetalert.min.js"></script>
    <script src="plugins/alertifyjs/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>	
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
</head>

<body id="bodyadminmodule">	
    <div class="container-fluid p-0">
        <div class="row justify-content-start">
            <div class="col-md-12">
                <div class="card card-body">
                    <?php 
                    $query = "SELECT * FROM videotips_videotips WHERE id  = '$id' AND username = '$local_username'";
                    $result_link = mysqli_query($conn, $query);
                    $link = mysqli_fetch_array($result_link);
                    ?>
                    <form text-align="center" action="updatelinks.php" method="POST"> 
                        <center><label for="title" class="col-form-label" style="color: black; font-size: 28px;"><strong> Editar Enlace </strong></label></center>
                        <center>
                            <div class="row justify-content-center">
                                <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                                <!--<div class="form-group col-md-2">
                                    <label for="id" class="col-form-label" style="color: black;"><strong>Id</strong></label><br>	
                                    <input id="videolink" style="text-align: center;" type="text" name="id" class="form-control" placeholder="ID" autofocus value="<?php /*echo $link['id'];*/ ?>" readonly></input><br>
                                </div>-->
                                <div class="form-group col-md-2">
                                    <label for="videolink" class="col-form-label" style="color: black;"><strong>Enlace</strong></label><br>	
                                    <input id="videolink" type="text" name="videolink" class="form-control" placeholder="Enlace" autofocus value="<?php echo $link['videolink']; ?>"></input><br>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="maincategory" class="col-form-label" style="color: black;"><strong>Categoría</strong></label><br>
                                    <select id="maincategory" class="form-control" name="maincategory" onchange="getSubcategories(this.value)" required>
                                        <?php 
                                        $query_options = "SELECT DISTINCT(maincategory) FROM videotips_viodetipscategory WHERE username = '$local_username' ORDER BY maincategory ASC";
                                        $result_options = mysqli_query($conn, $query_options);
                                        while ($option = mysqli_fetch_assoc($result_options)) {
                                            $selected = ($option['maincategory'] == $link['maincategory']) ? "selected" : "";
                                            echo "<option value=\"{$option['maincategory']}\" $selected>{$option['maincategory']}</option>";
                                        }
                                        ?>
                                    </select><br><br>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="category" class="col-form-label" style="color: black;"><strong>Subcategoría</strong></label><br>
                                    <select id="category" class="form-control" name="category" data-current-category="<?php echo $link['category']; ?>" required>
                                        <option value="">Seleccione una subcategoría</option>
                                        <?php 
                                        $query_options = "SELECT DISTINCT(category) FROM videotips_viodetipscategory WHERE username = '$local_username' ORDER BY category ASC";
                                        $result_options = mysqli_query($conn, $query_options);
                                        while ($option = mysqli_fetch_assoc($result_options)) {
                                            $selected = ($option['category'] == $link['category']) ? "selected" : "";
                                            echo "<option value=\"{$option['category']}\" $selected>{$option['category']}</option>";
                                        }
                                        ?>
                                    </select><br><br>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="proforpers" class="col-form-label" style="color: black;"><strong>Contenido</strong></label><br>
                                    <select id="proforpers" class="form-control" name="proforpers" required>
                                        <?php 
                                        $query_options = "SELECT DISTINCT(proforpers) FROM videotips_proforpers";
                                        $result_options = mysqli_query($conn, $query_options);
                                        while ($option = mysqli_fetch_assoc($result_options)) {
                                            $selected = ($option['proforpers'] == $link['proforpers']) ? "selected" : "";
                                            echo "<option value=\"{$option['proforpers']}\" $selected>{$option['proforpers']}</option>";
                                        }
                                        ?>
                                    </select><br><br>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="description" class="col-form-label" style="color: black;"><strong>Descripción</strong></label><br>	
                                    <textarea id="description" name="description" rows="1" class="form-control" placeholder="Descripción del Contenido" required><?php echo $link['content']; ?></textarea><br>
                                </div>						
                            </div>
                        </center>
                        <center>
                            <input id="save_link" type="submit" class="btn btn-success btn-block" name="update_link" value="Actualizar"></input>
                            <a href="videolinkadminmodule.php" class="btn btn-success btn-block" style="text-decoration: none;">Cancelar</a>
                            <!--<input id="save_link" type="submit" class="btn btn-success btn-block" name="logout" value="Borrar" formaction="delete.php"></input>-->
                        </center><br>
                    </form>
                </div>
            </div>
            <br>
            <div class="col-md-12">
                <br>
                <div class="card card-body">
                        <table id="autosearch" class="display" font color="back">
                            <center><label for="maincategory"  style="color: black; font-size: 28px;"><strong> Información de Enlace a Modificar </strong></label></center>
                            <thead id="tableswhite">
                                <tr>
                                    <!--<th>ID</th>-->
                                    <th style="width: 16.66%;">Enlace</th>
                                    <th style="width: 16.66%;">Categoría</th>
                                    <th style="width: 16.66%;">Subcategoría</th>
                                    <th style="width: 16.66%;">Contenido</th>
                                    <th style="width: 16.66%;">Fecha Creación</th>
                                    <th style="width: 16.66%;">Descripción</th>             
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $query1 = "SELECT * FROM videotips_videotips WHERE active = 'Yes' AND id = '$id' AND username = '$local_username'";
                                $result_link1 = mysqli_query($conn, $query1);
                                while ($link = mysqli_fetch_array($result_link1)) { ?>
                                    <tr>
                                        <!--<td align="center"><?php /*echo $link['id']; */?></td>-->
                                        <td align="left" style="width: 16.66%;"><?php echo $link['videolink']; ?></td>
                                        <td align="center" style="width: 16.66%;"><?php echo $link['maincategory']; ?></td>
                                        <td align="center" style="width: 16.66%;"><?php echo $link['category']; ?></td>
                                        <td align="center" style="width: 16.66%;"><?php echo $link['proforpers']; ?></td>
                                        <td align="center" style="width: 16.66%;"><?php echo $link['creationdate']; ?></td>
                                        <td align="left" style="width: 16.66%;"><?php echo $link['content']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                </div>	
            </div>
        </div>
    </div>
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</body>

<script>
    function getSubcategories(maincategory) {
    var currentCategory = document.getElementById("category").getAttribute("data-current-category");

    if (maincategory == "") {
        document.getElementById("category").innerHTML = "<option value=''>Seleccione una subcategoría</option>";
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("category").innerHTML = this.responseText;
        }
    };
    xhr.open("GET", "getSubcategories.php?maincategory=" + encodeURIComponent(maincategory) + "&current_category=" + encodeURIComponent(currentCategory), true);
    xhr.send();
}

// Ejecutar al cargar la página
window.onload = function() {
    var maincategory = document.getElementById("maincategory").value;
    if (maincategory) {
        getSubcategories(maincategory);
    }
};
</script>

<?php 
if ($sessiontimeoutreached == 1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Mensaje',
            text: 'Detectada que la sesión no tiene actividad por más de 15 minutos, debe iniciar sesión nuevamente',
            icon: 'error',
            confirmButtonText: 'Aceptar',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'custom-swal-confirm-button'
            },
            timer: 1500, // 1500 milisegundos = 1.5 segundos
            timerProgressBar: true, // Muestra una barra de progreso
            didOpen: () => {
                Swal.showLoading(); // Muestra un indicador de carga
            },
            willClose: () => {
            }
        });
    });
    </script>";  
}
?>