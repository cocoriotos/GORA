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
    <script>
        const editTranslations = {
            es: {
                editTitle: "Editar Enlace", link: "Enlace", category: "Categoría", subcategory: "Subcategoría",
                subcategoryPlaceholder: "Seleccione una subcategoría", content: "Contenido", description: "Descripción",
                descriptionPlaceholder: "Descripción del Contenido", update: "Actualizar", cancel: "Cancelar",
                tableTitle: "Información de Enlace a Modificar", creationDate: "Fecha Creación",
                sessionTimeoutTitle: "Mensaje",
                sessionTimeoutText: "La sesión ha estado inactiva por más de 15 minutos. Debes iniciar sesión nuevamente.",
                accept: "Aceptar"
            },
            en: {
                editTitle: "Edit Link", link: "Link", category: "Category", subcategory: "Subcategory",
                subcategoryPlaceholder: "Select a subcategory", content: "Content", description: "Description",
                descriptionPlaceholder: "Content Description", update: "Update", cancel: "Cancel",
                tableTitle: "Link Information to Edit", creationDate: "Creation Date",
                sessionTimeoutTitle: "Message",
                sessionTimeoutText: "Your session has been inactive for more than 15 minutes. Please sign in again.",
                accept: "OK"
            },
            pt: {
                editTitle: "Editar Link", link: "Link", category: "Categoria", subcategory: "Subcategoria",
                subcategoryPlaceholder: "Selecione uma subcategoria", content: "Conteúdo", description: "Descrição",
                descriptionPlaceholder: "Descrição do Conteúdo", update: "Atualizar", cancel: "Cancelar",
                tableTitle: "Informações do Link para Editar", creationDate: "Data de Criação",
                sessionTimeoutTitle: "Mensagem",
                sessionTimeoutText: "Sua sessão está inativa há mais de 15 minutos. Faça login novamente.",
                accept: "OK"
            }
        };

        function getEditLanguage() {
            const language = localStorage.getItem("smartshelfLang") || localStorage.getItem("moduleLang") || "es";
            return editTranslations[language] ? language : "es";
        }

        function editText(key) {
            return editTranslations[getEditLanguage()][key];
        }

        function applyEditTranslations() {
            const language = getEditLanguage();
            const translations = editTranslations[language];
            document.querySelectorAll("[data-edit-i18n]").forEach(element => {
                element.textContent = translations[element.dataset.editI18n];
            });
            document.querySelectorAll("[data-edit-i18n-placeholder]").forEach(element => {
                element.placeholder = translations[element.dataset.editI18nPlaceholder];
            });
            document.querySelectorAll("[data-edit-i18n-value]").forEach(element => {
                element.value = translations[element.dataset.editI18nValue];
            });
            document.documentElement.lang = language;
        }

        document.addEventListener("DOMContentLoaded", applyEditTranslations);
        window.addEventListener("storage", event => {
            if (event.key === "smartshelfLang" || event.key === "moduleLang") {
                applyEditTranslations();
            }
        });
    </script>
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
                        <center><label for="title" class="col-form-label" style="color: black; font-size: 28px;"><strong data-edit-i18n="editTitle">Editar Enlace</strong></label></center>
                        <center>
                            <div class="row justify-content-center">
                                <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                                <!--<div class="form-group col-md-2">
                                    <label for="id" class="col-form-label" style="color: black;"><strong>Id</strong></label><br>	
                                    <input id="videolink" style="text-align: center;" type="text" name="id" class="form-control" placeholder="ID" autofocus value="<?php /*echo $link['id'];*/ ?>" readonly></input><br>
                                </div>-->
                                <div class="form-group col-md-2">
                                    <label for="videolink" class="col-form-label" style="color: black;"><strong data-edit-i18n="link">Enlace</strong></label><br>
                                    <input id="videolink" type="text" name="videolink" class="form-control" placeholder="Enlace" data-edit-i18n-placeholder="link" autofocus value="<?php echo $link['videolink']; ?>"></input><br>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="maincategory" class="col-form-label" style="color: black;"><strong data-edit-i18n="category">Categoría</strong></label><br>
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
                                    <label for="category" class="col-form-label" style="color: black;"><strong data-edit-i18n="subcategory">Subcategoría</strong></label><br>
                                    <select id="category" class="form-control" name="category" data-current-category="<?php echo $link['category']; ?>" required>
                                        <option value="" data-edit-i18n="subcategoryPlaceholder">Seleccione una subcategoría</option>
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
                                    <label for="proforpers" class="col-form-label" style="color: black;"><strong data-edit-i18n="content">Contenido</strong></label><br>
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
                                    <label for="description" class="col-form-label" style="color: black;"><strong data-edit-i18n="description">Descripción</strong></label><br>
                                    <textarea id="description" name="description" rows="1" class="form-control" placeholder="Descripción del Contenido" data-edit-i18n-placeholder="descriptionPlaceholder" required><?php echo $link['content']; ?></textarea><br>
                                </div>						
                            </div>
                        </center>
                        <center>
                            <div style="display: flex; justify-content: center; gap: 15px; max-width: 400px; margin: 0 auto;">
                                <input id="save_link" type="submit" class="btn btn-success" name="update_link" value="Actualizar" data-edit-i18n-value="update" style="flex: 1; font-size: 16px; line-height: 1.5; padding: 10px 0; margin: 0; box-sizing: border-box;">
                                <a href="videolinkadminmodule.php" class="btn btn-success" data-edit-i18n="cancel" style="flex: 1; font-size: 16px; line-height: 1.5; padding: 10px 0; margin: 0; box-sizing: border-box; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center;">Cancelar</a>
                            </div>
                        </center><br>
                    </form>
                </div>
            </div>
            <br>
            <div class="col-md-12">
                <br>
                <div class="card card-body">
                        <table id="autosearch" class="display" font color="back">
                            <center><label for="maincategory"  style="color: black; font-size: 28px;"><strong data-edit-i18n="tableTitle">Información de Enlace a Modificar</strong></label></center>
                            <thead id="tableswhite">
                                <tr>
                                    <!--<th>ID</th>-->
                                    <th style="width: 16.66%;" data-edit-i18n="link">Enlace</th>
                                    <th style="width: 16.66%;" data-edit-i18n="category">Categoría</th>
                                    <th style="width: 16.66%;" data-edit-i18n="subcategory">Subcategoría</th>
                                    <th style="width: 16.66%;" data-edit-i18n="content">Contenido</th>
                                    <th style="width: 16.66%;" data-edit-i18n="creationDate">Fecha Creación</th>
                                    <th style="width: 16.66%;" data-edit-i18n="description">Descripción</th>
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
        document.getElementById("category").innerHTML = "<option value=''>" + editText("subcategoryPlaceholder") + "</option>";
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("category").innerHTML = this.responseText;
            const placeholder = document.querySelector("#category option[value='']");
            if (placeholder) placeholder.textContent = editText("subcategoryPlaceholder");
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
            title: editText('sessionTimeoutTitle'),
            text: editText('sessionTimeoutText'),
            icon: 'error',
            confirmButtonText: editText('accept'),
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