<?php 
include "sessions.php";
include "sessionvalidation.php";
$id = $_GET['id'];
$local_username=$_SESSION['email'];
$updatedcategory = $_SESSION['updatedcategory'];
$deletedcategory = $_SESSION['deletedcategory'];
$sessiontimeoutreached = $_SESSION['sessiontimeoutreached'];
$name = $_SESSION['name'];
include "headereditcategory.php";
include "db_connection1.php";
?>

<head>	
	<script src="head.js" defer></script>	  
	<link rel="stylesheet" href="style_sheet_ops.css"/>
	<script>
		const categoryEditTranslations = {
			es: {
				editTitle: "Editar Categoría",
				category: "Categoría",
				subcategory: "Subcategoría",
				categoryPlaceholder: "Categoría",
				subcategoryPlaceholder: "Subcategoría",
				update: "Actualizar",
				tableTitle: "Información de Categoría a Modificar",
				sessionTimeoutTitle: "Mensaje",
				sessionTimeoutText: "La sesión ha estado inactiva por más de 15 minutos. Debes iniciar sesión nuevamente.",
				accept: "Aceptar"
			},
			en: {
				editTitle: "Edit Category",
				category: "Category",
				subcategory: "Subcategory",
				categoryPlaceholder: "Category",
				subcategoryPlaceholder: "Subcategory",
				update: "Update",
				tableTitle: "Category Information to Edit",
				sessionTimeoutTitle: "Message",
				sessionTimeoutText: "Your session has been inactive for more than 15 minutes. Please sign in again.",
				accept: "OK"
			},
			pt: {
				editTitle: "Editar Categoria",
				category: "Categoria",
				subcategory: "Subcategoria",
				categoryPlaceholder: "Categoria",
				subcategoryPlaceholder: "Subcategoria",
				update: "Atualizar",
				tableTitle: "Informações da Categoria para Editar",
				sessionTimeoutTitle: "Mensagem",
				sessionTimeoutText: "Sua sessão está inativa há mais de 15 minutos. Faça login novamente.",
				accept: "OK"
			}
		};

		function getCategoryEditLanguage() {
			const language = localStorage.getItem("smartshelfLang") || localStorage.getItem("moduleLang") || "es";
			return categoryEditTranslations[language] ? language : "es";
		}

		function categoryEditText(key) {
			return categoryEditTranslations[getCategoryEditLanguage()][key];
		}

		function applyCategoryEditTranslations() {
			const language = getCategoryEditLanguage();
			const translations = categoryEditTranslations[language];
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

		document.addEventListener("DOMContentLoaded", applyCategoryEditTranslations);
		window.addEventListener("storage", event => {
			if (event.key === "smartshelfLang" || event.key === "moduleLang") {
				applyCategoryEditTranslations();
			}
		});
	</script>
</head>
<body id="bodyadminmodule"style="padding: 0%;>
<div class="container-fluid p-0" >
	<div class="row justify-content-start" style="padding: 0%; width: 100%;>
		<div class="col-md-12">
			 <?php 
					/*$query = "select * from videotips_viodetipscategory1 where category = '$category' and username='$local_username'";*/
					$query = "select * from videotips_viodetipscategory where id ='$id'";
					$result_link = mysqli_query($conn,$query);
					$link = mysqli_fetch_array($result_link);
			 ?>
			<div class="card card-body">
				<form text-align="center" action="updatecategory.php" method="POST"> 
					<center><label for="title" class="col-form-label" style="color: black; font-size: 28px;"><strong data-edit-i18n="editTitle">Editar Categoría</strong></label></center>
						<center><div class="row justify-content-center">	
							<input type="hidden" name="id" value="<?php echo $link['id']; ?>">
							<!--<div class="form-group col-md-4">
								<label for="id" class="col-form-label" style="color: black;"><strong>Id</strong></label><br>	
								<input id="videolink" class="form-control" style="text-align: center;" type="text" name="id" class="form-control" placeholder="ID" autofocus value ="<?php echo $link['id'];?>"readonly></input><br>
							</div>-->
							<div class="form-group col-md-4">
								<label for="maincategory" class="col-form-label" style="color: black;"><strong data-edit-i18n="category">Categoría</strong></label><br>
								<input id="maincategory" class="form-control" style="text-align: center;" type="text" name="maincategory" placeholder="Categoría" data-edit-i18n-placeholder="categoryPlaceholder" required autofocus value ="<?php echo $link['maincategory'];?>"></input><br>
							</div>
							<div class="form-group col-md-4">
								<label for="category" class="col-form-label" style="color: black;"><strong data-edit-i18n="subcategory">Subcategoría</strong></label><br>
								<input id="category" class="form-control" style="text-align: center;" type="text" name="category" placeholder="Subcategoría" data-edit-i18n-placeholder="subcategoryPlaceholder" required autofocus value ="<?php echo $link['category'];?>"></input><br>
							</div>
							</div></center>					
								<center><input id="save_link" type="submit" class="btn btn-success btn-block" name="update_category" value="Actualizar" data-edit-i18n-value="update"></input>
								<!--<input id="save_link" type="submit" class="btn btn-success btn-block" name="logout" value="Borrar" formaction="deletecategory.php"></input></center><br>-->
                 </form>
		    </div>
        </div>	
		
		<div class="col-md-12">
			<br>
			<div class="card card-body">
					<table id="autosearch" class="display" font color="back">
					<center><label for="maincategory" style="color: black; font-size: 28px;"><strong data-edit-i18n="tableTitle">Información de Categoría a Modificar</strong></label></center>
						<thead id="tableswhite">
							<tr>
								<!--<th style="width: 33.33%;">ID</th>-->
								<th style="width: 50%;" data-edit-i18n="category">Categoría</th>
								<th style="width: 50%;" data-edit-i18n="subcategory">Subcategoría</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							/*$query1 = "select * from videotips_viodetipscategory1 where category = '$category' and username = '$local_username'";*/
							$query1 = "select * from videotips_viodetipscategory where id = '$id'";
							$result_link1 = mysqli_query($conn,$query1);
							while($link = mysqli_fetch_array($result_link1)) { ?>
							<tr>
								<!--<td style="width: 33.33%;"><?php /*echo $link['id'] */?></td>-->
								<td style="width: 50%;"><?php echo $link['maincategory'] ?></td>
								<td style="width: 50%;"><?php echo $link['category'] ?></td>
								</td>
							</tr>
							<?php }?>
							</tbody>
					</table>
			</div>	
		</div>
	</div>
</div>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</body>

<?php


if ($sessiontimeoutreached  == 1){
	
	
	echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: categoryEditText('sessionTimeoutTitle'),
        text: categoryEditText('sessionTimeoutText'),
        icon: 'error',
        confirmButtonText: categoryEditText('accept'),
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
<?php include ("footer.php")?>