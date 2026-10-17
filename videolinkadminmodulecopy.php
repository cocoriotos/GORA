<!-- Developed by Julián González Bucheli -->
<html>
<?php
include "sessions.php";
include "sessionvalidation.php";
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
$name = $_SESSION['name'];
$delconfirm = $_SESSION['delconfirm'];

include "db_connection1.php";
include "header.php";
?>

<head>
    <script src="head.js" defer></script>
    <script src="Linktoclipboard.js" defer></script>
    <link rel="icon" href="SSCircleBackgroundWhite.ico" type="image/x-icon">
    <link rel="stylesheet" href="style_sheet_ops.css" />
    <script src="Popper/popper.min.js"></script>
    <script src="plugins/sweetalert/sweetalert.min.js"></script>
    <script src="plugins/alertifyjs/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css" />
</head>

<body id="bodyadminmodule" style="padding: 0%;">
    <div class="container-fluid p-0">
        <div class="row justify-content-start" style="padding: 0%; width: 100%;">
            <!-- Formulario para Adicionar Enlaces -->
            <div class="col-md-12">
    <div class="card card-body" id="card-body">
        <form action="savelinks.php" method="POST">
            <center>
                <label for="title" class="col-form-label" style="color: black; font-size: 28px;">
                    <strong>Adicionar Enlace</strong>
                </label>
            </center>

            <!-- Primera fila del formulario -->
            <div class="row justify-content-center">
                <!-- Campo: Enlace Útil -->
                <div class="form-group col-md-4">
                    <label for="videolink" class="col-form-label" style="color: black;">
                        <strong>Enlace Útil</strong>
                    </label>
                    <textarea id="videolink" name="videolink" rows="1" class="form-control" placeholder="Pega aquí el enlace" required></textarea>
                </div>

                <!-- Campo: Categoría -->
                <div class="form-group col-md-2">
                    <label for="maincategory" class="col-form-label" style="color: black;">
                        <strong>Categoría</strong>
                    </label>
                    <select class="form-control" name="maincategory" id="maincategory" onchange="getSubcategories(this.value)" required>
                        <option value="" disabled selected>Seleccione una categoría</option>
                        <?php
                        $SQLSELECT = "SELECT distinct(maincategory) FROM videotips_viodetipscategory WHERE username = '$local_username' ORDER BY maincategory ASC";
                        $result_set = mysqli_query($conn, $SQLSELECT);
                        while ($rows = $result_set->fetch_assoc()) {
                            $maincategory = $rows['maincategory'];
                            echo "<option value='$maincategory'>$maincategory</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Campo: Subcategoría -->
                <div class="form-group col-md-2">
                    <label for="category" class="col-form-label" style="color: black;">
                        <strong>Subcategoría</strong>
                    </label>
                    <select class="form-control" name="category" id="category" required>
                        <option value="" disabled selected>Seleccione una subcategoría</option>
                    </select>
                </div>
            </div>

            <!-- Segunda fila del formulario -->
            <div class="row justify-content-center">
                <!-- Campo: Tipo de Contenido -->
                <div class="form-group col-md-4">
                    <label for="proforpers" class="col-form-label" style="color: black;">
                        <strong>Contenido</strong>
                    </label>
                    <select class="form-control" name="proforpers" id="proforpers" required>
                        <?php
                        $SQLSELECT = "SELECT proforpers FROM videotips_proforpers";
                        $result_set = mysqli_query($conn, $SQLSELECT);
                        while ($rows = $result_set->fetch_assoc()) {
                            $proforpers = $rows['proforpers'];
                            echo "<option value='$proforpers'>$proforpers</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Campo: Descripción -->
                <div class="form-group col-md-4">
                    <label for="description" class="col-form-label" style="color: black;">
                        <strong>Descripción</strong>
                    </label>
                    <textarea id="description" name="description" rows="1" class="form-control" placeholder="Describe el contenido del enlace" required></textarea>
                </div>
            </div>

            <!-- Botón de Guardar -->
            <br>
            <center>
                <input id="save_link" type="submit" class="btn btn-success btn-block" name="save_link" value="Guardar" disabled>
            </center>
        </form>
    </div>
</div>

            <!-- Sección de "Tus Contenidos Útiles" -->
            <div class="col-md-12">
                <br>
                <center><?php include("search.php") ?></center> <!-- Incluir el buscador -->
                <div class="card card-body" div="card-body">
                    <center>
                        <label for="description" class="col-form-label" style="color: black; font-size: 28px;">
                            <strong>Tus Contenidos Útiles</strong>
                        </label>
                    </center>
                    <div class="grid-container">
                    <?php
                    $query1 = "SELECT * FROM videotips_videotips 
                              WHERE active = 'Yes' AND username ='$local_username' 
                              ORDER BY content ASC";
                    $result_links = mysqli_query($conn, $query1);
                    while ($links = mysqli_fetch_array($result_links)) {
                        $randomColor = getRandomLightColor();
                    ?>
                        <div class="grid-item" style="background-color: <?php echo $randomColor; ?>; display: none;">
                            <div class="grid-item-content">
                                <button class="grid-item-action-btn" style="color: black; font-size: 40px; font-weight: bold;" onclick="toggleActions(event, <?php echo $links['id']; ?>)">...</button>
                                <div class="grid-item-actions">
                                    <div class="grid-item-action-menu" id="action-menu-<?php echo $links['id']; ?>">
                                        <button style="background: white; color: green; font-size: 12px;" onclick="copyToClipboard('<?php echo $links['videolink']; ?>'); toggleActions(event, <?php echo $links['id']; ?>);" class="btn btn-secondary">Copiar Enlace</button>
                                        <button style="background: white; color: gray; font-size: 12px;" onclick="window.location.href = 'edit.php?id=<?php echo $links['id']; ?>'" class="btn btn-secondary">Modificar</button>
                                        <button style="background: white; color: red; font-size: 12px;" onclick="confirmDelete(<?php echo $links['id']; ?>)" class="btn btn-secondary">Borrar</button>
                                    </div>
                                </div>
                                <div class="grid-item-header"></div>
                                <span class="grid-item-title" style="color: blue"><?php echo $links['content']; ?></span>
                                <div class="grid-item-body">
                                    <p><span class="p-title">Categoría:</span><span class="p-content"><?php echo $links['maincategory']; ?></span></p>
                                    <p><span class="p-title">Subcategoría:</span><span class="p-content"><?php echo $links['category']; ?></span></p>
                                    <p><span class="p-title">Contenido:</span><span class="p-content"><?php echo $links['proforpers']; ?></span></p>
                                    <p><span class="p-title">Creación:</span><span class="p-content"><?php echo $links['creationdate']; ?></span></p>
                                </div>
                                <a href="<?php echo $links['videolink']; ?>" target="_blank" class="btn btn-primary">Ir al Contenido</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
              </div>
             </div>
        </div>
    </div>
    <!-- Botón de WhatsApp -->
    <a href="https://wa.me/573117592209" target="_blank"
        style="position: fixed; bottom: 20px; right: 20px; background-color: #25D366; color: white; padding: 10px 20px; border-radius: 50px; font-size: 16px; text-decoration: none; display: flex; align-items: center;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="35" height="35" style="margin-right: 10px;"></a>
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</body>

<script>
    function getSubcategories(maincategory) {
        if (maincategory == "") {
            document.getElementById("category").innerHTML = "<option value=''>Seleccione una subcategoría</option>";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("category").innerHTML = this.responseText;
                checkForm(); // Verificar el estado del formulario después de cargar las subcategorías
            }
        };
        xhr.open("GET", "getSubcategories.php?maincategory=" + maincategory, true);
        xhr.send();
    }

    function checkForm() {
        const videolink = document.getElementById("videolink").value.trim();
        const maincategory = document.getElementById("maincategory").value;
        const category = document.getElementById("category").value;
        const proforpers = document.getElementById("proforpers").value;
        const description = document.getElementById("description").value.trim();

        const saveButton = document.getElementById("save_link");

        if (videolink !== "" && !videolink.includes('""') &&
            maincategory !== "" && category !== "" &&
            proforpers !== "" && description !== "" && !description.includes('""')) {
            saveButton.disabled = false;
        } else {
            saveButton.disabled = true;
        }
    }

    document.getElementById("videolink").addEventListener("input", checkForm);
    document.getElementById("maincategory").addEventListener("change", checkForm);
    document.getElementById("category").addEventListener("change", checkForm);
    document.getElementById("proforpers").addEventListener("change", checkForm);
    document.getElementById("description").addEventListener("input", checkForm);

    window.onload = function() {
        var maincategory = document.getElementById("maincategory").value;
        getSubcategories(maincategory);
    };

    function toggleActions(event, id) {
        event.stopPropagation(); // Evita que el evento de clic se propague al documento
        var actionMenu = document.getElementById("action-menu-" + id);
        if (actionMenu.style.display === "block") {
            actionMenu.style.display = "none";
        } else {
            // Cerrar todos los menús abiertos antes de abrir uno nuevo
            var allMenus = document.querySelectorAll('.grid-item-action-menu');
            allMenus.forEach(function(menu) {
                menu.style.display = "none";
            });
            actionMenu.style.display = "block";
        }
    }

    // Cerrar el menú al hacer clic fuera de él
    document.addEventListener('click', function(event) {
        var allMenus = document.querySelectorAll('.grid-item-action-menu');
        var isClickInside = false;

        allMenus.forEach(function(menu) {
            // Verificar si el clic fue dentro del menú
            if (menu.contains(event.target)) {
                isClickInside = true;
            }
        });

        if (!isClickInside) {
            allMenus.forEach(function(menu) {
                menu.style.display = "none";
            });
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#032642',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, borrar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'custom-swal-confirm-button',
                cancelButton: 'custom-swal-cancel-button'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, redirigir a delete.php con el ID
                window.location.href = "delete.php?id=" + id;
            } else {
                // Si el usuario cancela, no hacer nada
                Swal.fire({
                    title: 'Cancelado',
                    text: 'El elemento no fue borrado.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-confirm-button'
                    }
                });
            }
        });
    }
</script>

<script>
let allCards = [];
let cardsPerLoad = 20; // cantidad a mostrar por bloque
let currentIndex = 0;

function loadMoreCards() {
    const endIndex = currentIndex + cardsPerLoad;
    for (let i = currentIndex; i < endIndex && i < allCards.length; i++) {
        allCards[i].style.display = "block";
    }
    currentIndex = endIndex;
}

document.addEventListener("DOMContentLoaded", () => {
    allCards = Array.from(document.querySelectorAll(".grid-item"));
    loadMoreCards(); // Mostrar el primer bloque
});

window.addEventListener("scroll", () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
        loadMoreCards();
    }
});
</script>


<?php
if ($copytoclipboard == 1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: '¡Enlace copiado al portapapeles!',
        icon: 'success',
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
    $copytoclipboard = 0;
}

if ($savedlink == 1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: '¡Enlace Adicionado Exitosamente!',
        icon: 'success',
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
    $_SESSION['savedlink'] = 0;
}

if ($savedlink == 2) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Hubo un problema al adicionar el enlace, intente nuevamente',
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
    $_SESSION['savedlink'] = 0;
}

if ($duplicatedlink == 1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Este enlace ya lo Tenías guardado en tu Biblioteca',
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
    $_SESSION['duplicatedlink'] = 0;
}

if ($updatedlink == 1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Enlace Actualizado Exitosamente',
        icon: 'success',
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
    $_SESSION['updatedlink'] = 0;
}

if ($updatedlink == 2) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Hubo un problema al actualizar el enlace, intente nuevamente',
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
    $_SESSION['updatedlink'] = 0;
}

if ($deletedlink == 1) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Enlace Borrado Exitosamente',
        icon: 'success',
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
    $_SESSION['deletedlink'] = 0;
}

if ($deletedlink == 2) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Hubo un problema al borrar el enlace, intente nuevamente',
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
    $_SESSION['deletedlink'] = 0;
}
?>

<?php
function getRandomLightColor() {
    // Genera componentes de color claros (valores entre 200 y 255 para asegurar colores claros)
    $red = rand(200, 255);
    $green = rand(200, 255);
    $blue = rand(200, 255);
    return sprintf("#%02x%02x%02x", $red, $green, $blue); // Convierte a formato hexadecimal
}
?>
</html>