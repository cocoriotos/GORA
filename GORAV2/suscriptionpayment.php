<!DOCTYPE html>
<html lang="es">
<?php 
include "sessions.php";
include "sessionvalidation.php";
include "db_connection1.php";
$name = $_SESSION['name'];
$local_username=$_SESSION['email'];
$suscriptiondue = $_SESSION['suscriptiondue']; 
//include "SessionTimeOut.php";
/*include "SessionTimeOut.php";*/

// Verificar si el usuario está autenticado (si $_SESSION['email'] está definido)
//if (!isset($_SESSION['email'])) {
    // Si no hay sesión, redirigir a la página de autenticación
//    header('Location: closetaskcon.php');
//    exit(); // Detener la ejecución del script
//}

// Si el usuario está autenticado, continuar con el resto del código
//$local_username = $_SESSION['email']; // Obtener el email del usuario desde la sesión
?>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="GORA.ico" type="image/x-icon">
  <link rel="stylesheet" href="style_sheet.css"/>
  <link rel="stylesheet" href="style_sheet_ops.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
  <script src="session-timeout.js?v=20261019-1" defer></script>
  <script src="copynumber.js" defer></script>
  <script src="copypaypal.js" defer></script>
	<style>
    body#bodyadminmodule {
      --welcome-bottom: 48px;
      margin: 0;
      padding-top: 0 !important;
    }

    .subscription-actions {
      display: none !important;
    }

    .subscription-language-switcher {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin: 8px 0;
    }

    .subscription-language-button {
      min-width: 42px;
      padding: 6px 12px;
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 999px;
      background: transparent;
      color: #ffffff;
      font-weight: 700;
      cursor: pointer;
    }

    .subscription-language-button:hover,
    .subscription-language-button.active {
      border-color: #25d366;
      background: #25d366;
      color: #032642;
    }

    .subscription-workspace {
      position: relative;
      min-height: calc(100vh - var(--welcome-bottom));
      padding: 0 20px 64px 90px;
    }

    .subscription-sidebar {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 1100;
      width: 68px;
      min-height: calc(100vh - var(--welcome-bottom));
      height: 100%;
      overflow-y: auto;
      padding: 14px;
      background: #032642;
      box-shadow: 0 16px 32px rgba(3, 38, 66, 0.16);
      transition: width 0.2s ease;
    }

    .subscription-sidebar:not(.collapsed),
    .subscription-sidebar.collapsed:hover {
      width: 240px;
    }

    .subscription-sidebar-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 34px;
      margin-bottom: 12px;
      border: 0;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.12);
      color: #ffffff;
      cursor: pointer;
    }

    .subscription-sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .subscription-sidebar-link {
      display: flex;
      align-items: center;
      gap: 12px;
      width: 100%;
      padding: 12px;
      border: 0;
      border-radius: 10px;
      background: transparent;
      color: #fff;
      font-size: 1rem;
      text-align: left;
      text-decoration: none;
      cursor: pointer;
    }

    .subscription-sidebar-link:hover {
      background: #25d366;
    }

    .subscription-sidebar-link i {
      flex: 0 0 24px;
      width: 24px;
      color: #fff;
      font-size: 1.25rem;
      text-align: center;
    }

    .subscription-sidebar-link .subscription-logout-icon {
      flex: 0 0 24px;
      width: 24px;
      height: 24px;
      fill: none;
      stroke: #ffffff;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .subscription-sidebar.collapsed .subscription-sidebar-link span {
      display: none;
    }

    .subscription-sidebar.collapsed:hover .subscription-sidebar-link span {
      display: inline;
    }

    .subscription-sidebar.collapsed .subscription-sidebar-link {
      justify-content: center;
      padding-left: 8px;
      padding-right: 8px;
    }

    .subscription-sidebar.collapsed:hover .subscription-sidebar-link {
      justify-content: flex-start;
      padding-left: 12px;
      padding-right: 12px;
    }

    .subscription-sidebar-section {
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid rgba(255, 255, 255, 0.18);
    }

    .subscription-sidebar-title {
      padding: 0 12px 6px;
      color: rgba(255, 255, 255, 0.68);
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .subscription-sidebar.collapsed .subscription-sidebar-title {
      display: none;
    }

    .whatsapp-fab {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background-color: #25d366;
      color: #ffffff;
      padding: 12px 18px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 18px 40px rgba(37, 211, 102, 0.24);
      z-index: 999;
      text-decoration: none;
    }

    .whatsapp-fab img {
      width: 30px;
      height: 30px;
    }

    @media (max-width: 768px) {
      .subscription-workspace {
        padding-left: 90px;
      }

      .subscription-workspace .container1,
      .subscription-workspace .col-md-12 {
        width: 100%;
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
      }

      .subscription-workspace .column-custom,
      .subscription-workspace .card {
        padding: 16px !important;
        margin-left: 0;
        margin-right: 0;
      }

      .subscription-workspace p {
        font-size: 16px !important;
        line-height: 1.55;
      }

      .subscription-workspace h3,
      .subscription-workspace .col-form-label {
        font-size: 1.35rem !important;
        line-height: 1.3;
      }

      .subscription-workspace table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        font-size: 16px;
      }

      .subscription-workspace input[type="button"] {
        width: 100%;
        min-height: 48px;
        font-size: 16px !important;
      }

      .whatsapp-fab {
        right: 16px;
        bottom: 16px;
      }
    }

    @media (max-width: 600px) {
      html,
      body#bodyadminmodule {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
      }

      body#bodyadminmodule nav#welcome {
        display: flex;
        width: 100%;
        max-width: 100%;
        padding: 10px 12px;
        box-sizing: border-box;
      }

      body#bodyadminmodule nav#welcome > center {
        display: block;
        width: 100%;
        max-width: 100%;
      }

      body#bodyadminmodule nav#welcome #welcome.navbar-brand {
        display: block;
        width: 100%;
        max-width: 100%;
        margin: 0;
        font-size: 16px !important;
        line-height: 1.3;
        text-align: center;
        white-space: normal;
        overflow-wrap: anywhere;
      }

      .subscription-workspace {
        width: 100%;
        max-width: 100%;
        padding: 0 12px 48px;
        box-sizing: border-box;
      }

      .subscription-sidebar {
        top: 0;
        left: -12px;
        z-index: 1200;
        width: calc(100% + 24px);
        height: 60px;
        min-height: 0;
        padding: 8px 12px;
        overflow: hidden;
        background: #032642;
        box-shadow: 0 8px 18px rgba(3, 38, 66, 0.14);
      }

      .subscription-sidebar.collapsed,
      .subscription-sidebar.collapsed:hover {
        width: calc(100% + 24px);
        height: 60px;
      }

      .subscription-sidebar:not(.collapsed) {
        width: calc(100% + 24px);
        height: auto;
        max-height: calc(100vh - var(--welcome-bottom));
        max-height: calc(100dvh - var(--welcome-bottom));
        overflow-y: auto;
      }

      .subscription-sidebar.collapsed .subscription-sidebar-nav {
        display: none;
      }

      .subscription-sidebar:not(.collapsed) .subscription-sidebar-nav {
        display: flex;
      }

      .subscription-sidebar-toggle,
      .subscription-sidebar.collapsed .subscription-sidebar-toggle {
        width: 44px;
        height: 44px;
        margin: 0;
      }

      .subscription-sidebar:not(.collapsed) .subscription-sidebar-link {
        justify-content: flex-start;
        padding: 12px;
      }

      .subscription-sidebar:not(.collapsed) .subscription-sidebar-link span,
      .subscription-sidebar:not(.collapsed) .subscription-sidebar-title {
        display: inline;
      }

      .subscription-workspace > .container1 {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        padding-top: 60px;
        box-sizing: border-box;
      }

      .subscription-workspace .column-wrap,
      .subscription-workspace .column-custom-wrap,
      .subscription-workspace .column-custom,
      .subscription-workspace .col-xs-12,
      .subscription-workspace .col-sm-12,
      .subscription-workspace .col-md-12,
      .subscription-workspace .card {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
      }
    }

    </style>
	<script src="Popper/popper.min.js"></script>
	<script src="plugins/sweetalert/sweetalert.min.js"></script>
	<script src="plugins/alertifyjs/alertify.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>	
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
</head>

<body id="bodyadminmodule">
  <?php include "headersuscription.php"; ?>
  <div class="subscription-workspace">
    <aside class="subscription-sidebar collapsed" id="subscription-sidebar">
      <button type="button" class="subscription-sidebar-toggle" aria-label="Menu" aria-expanded="false" onclick="toggleSubscriptionSidebar()">
        <i class="fas fa-bars"></i>
      </button>
      <nav class="subscription-sidebar-nav">
        <!--<a class="subscription-sidebar-link" href="videolinkadminmodule.php">
          <i class="fas fa-plus-circle"></i><span>Adicionar Enlace</span>
        </a>
        <a class="subscription-sidebar-link" href="addcategory.php">
          <i class="fas fa-folder-tree"></i><span>Categorías</span>
        </a>-->
        <div class="subscription-sidebar-section">
          <div class="subscription-sidebar-title">Medios de Pago de Suscripción</div>
          <button type="button" class="subscription-sidebar-link" onclick="copiarPaypal()">
            <i class="fab fa-paypal"></i><span>Copiar cuenta Paypal: YSXRZMT2AAG4G</span>
          </button>
          <button type="button" class="subscription-sidebar-link" onclick="window.open('https://www.paypal.com/', '_blank')">
            <i class="fas fa-external-link-alt"></i><span>Ir a Paypal</span>
          </button>
          <button type="button" class="subscription-sidebar-link" onclick="copiarNumero()">
            <i class="fas fa-mobile-alt"></i><span>Copiar número de Nequi: 3054293185</span>
          </button>
          <button type="button" class="subscription-sidebar-link" onclick="window.open('https://clientes.nequi.com.co/recargas', '_blank')">
            <i class="fas fa-external-link-alt"></i><span>Ir a Nequi</span>
          </button>
        </div>
        <div class="subscription-sidebar-section">
          <div class="subscription-sidebar-title">Documentación</div>
          <!--<a class="subscription-sidebar-link" href="https://www.youtube.com/playlist?list=PLRQ5KF9igtB2GRlHLSP6Uwx1lzy387Wz5" target="_blank">
            <i class="fas fa-play-circle"></i><span>Tutoriales</span>
          </a>-->
          <a class="subscription-sidebar-link" id="subscription-manual-link" href="GORAManualUsuarioES.pdf" target="_blank" rel="noopener">
            <i class="fas fa-book-open"></i><span>Manual</span>
          </a>
        </div>
        <div class="subscription-sidebar-section">
          <a class="subscription-sidebar-link" href="closetaskscon.php">
            <svg class="subscription-logout-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5"></path>
              <path d="M14 16l4-4-4-4"></path>
              <path d="M18 12H9"></path>
            </svg>
            <span data-subscription-i18n="sidebar_exit">Salir</span>
          </a>
        </div>
      </nav>
    </aside>
  <div class="container1">
      <div class="column-wrap clearfix">
            <div class="col-xs-12">
                <hr>
            </div>
      <center><div class="col-xs-12 col-sm-12 column-custom-wrap">
                <div class="column-custom">
                    <h3 id="conditions" align="center" style="padding-bottom: 5px">Condiciones y Beneficios de Uso de la Aplicación</h3>
                    <!--<p> Video paso a paso del uso de la aplicación. Click Aquí</p><br>-->
                    <br>
                    <center><p style="color: blue; font-weight: bold; font-size: 24px;"> Ventajas </p></center>
                    <p > Gracias por haber usado la herramienta por suscripción o la versión gratuita de 30 días calendario de la biblioteca de Contenidos Útiles la cuál te ayuda a: </p><br>
                    <p> 1. Etiquetar con una breve descripción del contenido para que puedas hacer las búsquedas más facilmente</p>
                    <p> 2. Tener todos tus enlaces importantes, indistinto de cual plataforma sea, donde la búsqueda la haces en un solo sitio</p>
                    <p> 3. La búsqueda de un enlace en cualquier histórico o favoritos en las aplicaciones, que normalmente toma mucho tiempo encontrarla, la harás en segundos en tu bliblioteca porque las tendrás en un solo lugar y con un motor de búsqueda efectivo</p>
                    <p> 4. Abrir el enlace directamente para ver los contenidos</p>
                    <p> 5. Copiar los Enlaces guardados para compartirlos</p>
					          <br>
                    <center><p style="color: blue; font-weight: bold; font-size: 24px;"> Condiciones </p></center>
                    <br>
                    <p> 1. No incluir información sensitiva ni personal. No nos hacemos responsables del salvaguardar o uso de su información por terceros</p>
                    <!-- <p>  Habeas Data</p> -->
                    <p>2. Tratamiento de los datos bajo la modalidad de <a href="HabeasData.pdf" target="_blank" style="color: var(--accent) !important; text-decoration: underline;">Habeas Data</a></p>
                    <p> 3. No nos responsabilizamos por información que incite a la violencia física, psicológica, delitos informáticos o cualquier manifestación delictiva</p>
                    <p> 4. Opciones de valores por Suscripción: </p>
                    <p><span class="destacado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$20.000</span> Pesos Colombianos COP por Tres Meses</p>
                    <p><span class="destacado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$40.000</span> Pesos Colombianos COP por Seis Meses</p>
                    <p><span class="destacado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$60.000</span> Pesos Colombianos COP por un Año</p>
                    <p> 5. Al momento de no renovación de suscripción y no querer continuar con el servicios, dispondrá de máximos 3 meses calendario para solicitar sean enviados sus enlaces e información asociados a ellos en archivo CSV. Hacer la solicitud al correo al correo <a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a> y recibirá la información al correo registrado del usuario en la página</p>
                    <br>
                    <center><p style="color: blue; font-weight: bold; font-size: 24px;"> Forma de Pago de Suscripción y Subcategorías</p></center>
                    <p> 1. Pago por Nequi al número +57 305 4293185 o PayPal al YSXRZMT2AAG4G.</p> 
                    <p> 2. Tomar una imagen por cada uno de los pagos realizados y convertirlas a formato PDF</p>
                    <p> 3. Al realizar el pago dar click en el botón de abajo para enviar correo el archivo PDF o una imagen del pago realizado.</p>
                    <p> 4. Si tiene alguna duda, enviar correo al administrador: adm@solicionespro.com o WhatsApp: +57 311 7592209</p>
				        </div>
                <a href="https://wa.me/573117592209" target="_blank" class="whatsapp-fab">
          <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
        <span id="support-label">Soporte</span>
    </a>
          </div>
          </center>
          <hr>
      </div>    
  </div>

  <div class="col-md-12">
					<br>
				<!--<?php /*include("search.php") */?>-->
				<div class="card card-body">
				<center><label for="description" class="col-form-label" style="color: black; font-size: 28px;"><strong> Resúmen de tu estado de cuenta </strong></label></center>

              <table id="autosearch" class="display" font color="back">
                  <thead id="tableswhite">
                  <tr>
                    <th>Usuario</th>
                    <th>Activa</th>
                    <th>Fecha Pago</th>
                    <th>Fecha Registro</th>
                    <th>Suscripcion</th>
                    <th>Paquete</th>
                    <th>Días disfrutados de la suscripción</th>
                    <!--<th>Descargar Documento</th>-->
                  </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $query1 = "select * from videotips_app_access_list where username ='$local_username'";
                    //$query1 = "select * from videotips_app_access_list where active = '1' and username ='$local_username'";
                    $result_links = mysqli_query($conn,$query1);
                    while($links = mysqli_fetch_array($result_links)) { ?>
                    <tr>
                      <td align="center"><?php echo $links['username']?></td>
                      <td align="center"><?php echo $links['active']?></td>
                      <td align="center"><?php echo $links['lastsuscriptionpaymentdate']?></td>
                      <td align="center"><?php echo $links['registrationdate']?></td>
                      <td align="center"><?php echo $links['suscriptionkind']?></td>
                      <td align="center"><?php echo $links['suscription_package']?></td>
                      <td align="center"><?php echo $links['daystoenjoy']?></td>
                      </td>
                    </tr>
                    <?php }?>
                  <tbody>
                </table>
                <br>
                <center><a id="payment-proof-link" data-username="<?php echo htmlspecialchars($local_username, ENT_QUOTES, 'UTF-8'); ?>" href="mailto:adm@solicionespro.com?subject=Evidencia%20Pago%20Suscripción.&body=Hola,%20adjunto%20evidencia%20del%20pago%20de%20la%20suscripción%20a%20nombre%20de%20<?php echo urlencode($local_username); ?>.">
                <input id="payment-proof-button" type="button" class="btn btn-success btn-block" style="color: white; font-weight: bold; font-size: 24px;" value="Enviar Constancia de Pago por Correo"></a></center>

                <!--<center><input type="submit" class="btn btn-success btn-block" name="save_link" value="Agregar Constancia de Pago"></input></center>-->
        </div>
  </div>
</div>

</body>

<script>
  const subscriptionTranslations = {
    en: {
      header_title: 'Useful Content Library | Subscription',
      'Medios de Pago de Suscripción': 'Subscription Payment Methods',
      'Copiar cuenta Paypal: YSXRZMT2AAG4G': 'Copy PayPal account: YSXRZMT2AAG4G',
      'Ir a Paypal': 'Go to PayPal',
      'Copiar númeroNequi: 3054293185': 'Copy Nequi number: 3054293185',
      'Copiar número de Nequi: 3054293185': 'Copy Nequi number: 3054293185',
      'Ir a Nequi': 'Go to Nequi',
      'Documentación': 'Documentation',
      'Manual': 'Manual',
      sidebar_exit: 'Log out',
      'Condiciones y Beneficios de Uso de la Aplicación': 'Terms and Benefits of Using the Application',
      'Ventajas': 'Benefits',
      'Gracias por haber usado la herramienta por suscripción o la versión gratuita de 30 días calendario de la biblioteca de Contenidos Útiles la cuál te ayuda a:': 'Thank you for using the subscription tool or the 30-calendar-day free version of the Useful Content Library, which helps you to:',
      '1. Etiquetar con una breve descripción del contenido para que puedas hacer las búsquedas más facilmente': '1. Tag content with a brief description so you can search more easily.',
      '2. Tener todos tus enlaces importantes, indistinto de cual plataforma sea, donde la búsqueda la haces en un solo sitio': '2. Keep all your important links, regardless of platform, and search for them in one place.',
      '3. La búsqueda de un enlace en cualquier histórico o favoritos en las aplicaciones, que normalmente toma mucho tiempo encontrarla, la harás en segundos en tu bliblioteca porque las tendrás en un solo lugar y con un motor de búsqueda efectivo': '3. Find a link from any app history or favorites in seconds in your library, instead of spending time searching across apps, because everything is in one place with an effective search engine.',
      '4. Abrir el enlace directamente para ver los contenidos': '4. Open the link directly to view its content.',
      '5. Copiar los Enlaces guardados para compartirlos': '5. Copy saved links to share them.',
      'Condiciones': 'Terms',
      '1. No incluir información sensitiva ni personal. No nos hacemos responsables del salvaguardar o uso de su información por terceros': '1. Do not include sensitive or personal information. We are not responsible for the storage or use of your information by third parties.',
      'Habeas Data': 'Data Protection',
      '2. Tratamiento de los datos bajo la modalidad de': '2. Data is processed under the terms of',
      '3. No nos responsabilizamos por información que incite a la violencia física, psicológica, delitos informáticos o cualquier manifestación delictiva': '3. We are not responsible for information that incites physical or psychological violence, cybercrime, or any other criminal act.',
      '4. Opciones de valores por Suscripción:': '4. Subscription pricing options:',
      'Pesos Colombianos COP por Tres Meses': 'Colombian pesos (COP) for three months',
      'Pesos Colombianos COP por Seis Meses': 'Colombian pesos (COP) for six months',
      'Pesos Colombianos COP por un Año': 'Colombian pesos (COP) for one year',
      '5. Al momento de no renovación de suscripción y no querer continuar con el servicios, dispondrá de máximos 3 meses calendario para solicitar sean enviados sus enlaces e información asociados a ellos en archivo CSV. Hacer la solicitud al correo al correo': '5. If you do not renew and no longer wish to continue the service, you have up to 3 calendar months to request your links and related information as a CSV file. Send your request by email to',
      'y recibirá la información al correo registrado del usuario en la página': 'and the information will be sent to the email address registered on the site.',
      'Forma de Pago de Suscripción y Subcategorías': 'Subscription and Subcategory Payment Methods',
      '1. Pago por Nequi al número +57 305 4293185 o PayPal al YSXRZMT2AAG4G.': '1. Pay with Nequi at +57 305 4293185 or with PayPal at YSXRZMT2AAG4G.',
      '2. Tomar una imagen por cada uno de los pagos realizados y convertirlas a formato PDF': '2. Take a picture of each payment and convert the images to PDF.',
      '3. Al realizar el pago dar click en el botón de abajo para enviar correo el archivo PDF o una imagen del pago realizado.': '3. After making the payment, click the button below to email the PDF or an image of the payment.',
      '4. Si tiene alguna duda, enviar correo al administrador:': '4. If you have any questions, email the administrator at:',
      'o WhatsApp: +57 311 7592209': 'or WhatsApp: +57 311 7592209',
      Soporte: 'Support',
      'Resúmen de tu estado de cuenta': 'Your Account Summary',
      Usuario: 'Username',
      Activa: 'Active',
      'Fecha Pago': 'Payment Date',
      'Fecha Registro': 'Registration Date',
      Suscripcion: 'Subscription',
      Paquete: 'Package',
      'Días disfrutados de la suscripción': 'Subscription Days Used',
      'Enviar Constancia de Pago por Correo': 'Email Payment Receipt',
      'Mensaje': 'Message',
      'Has alcanzado el límite de 30 días Calendario de uso gratuito y/o tu Suscripción está vencida. Te invitamos a renovarla por medio nuestros canales de Nequi  para Colombia o Paypayl para Internacional': 'You have reached the 30-calendar-day free trial limit and/or your subscription has expired. Please renew using Nequi in Colombia or PayPal internationally.',
      'Aceptar': 'OK',
      '1 Mes:': '1 Month:',
      '2 Meses:': '2 Months:',
      '3 Meses:': '3 Months:',
      '4 Meses:': '4 Months:',
      '5 Meses:': '5 Months:',
      '6 Meses:': '6 Months:',
      '7 Meses:': '7 Months:',
      '8 Meses:': '8 Months:',
      '9 Meses:': '9 Months:',
      '10 Meses:': '10 Months:',
      '11 Meses:': '11 Months:',
      '12 Meses:': '12 Months:',
      'De Pago': 'Paid',
      Vencida: 'Expired',
      Suspendida: 'Suspended',
      Trial: 'Trial',
      Owner: 'Owner',
      Partner: 'Partner'
    },
    pt: {
      header_title: 'Biblioteca de Conteúdos Úteis | Assinatura',
      'Medios de Pago de Suscripción': 'Meios de Pagamento da Assinatura',
      'Copiar cuenta Paypal: YSXRZMT2AAG4G': 'Copiar conta PayPal: YSXRZMT2AAG4G',
      'Ir a Paypal': 'Ir para o PayPal',
      'Copiar númeroNequi: 3054293185': 'Copiar número do Nequi: 3054293185',
      'Copiar número de Nequi: 3054293185': 'Copiar número do Nequi: 3054293185',
      'Ir a Nequi': 'Ir para o Nequi',
      'Documentación': 'Documentação',
      'Manual': 'Manual',
      sidebar_exit: 'Sair',
      'Condiciones y Beneficios de Uso de la Aplicación': 'Condições e Benefícios de Uso do Aplicativo',
      'Ventajas': 'Vantagens',
      'Gracias por haber usado la herramienta por suscripción o la versión gratuita de 30 días calendario de la biblioteca de Contenidos Útiles la cuál te ayuda a:': 'Agradecemos por usar a ferramenta por assinatura ou a versão gratuita de 30 dias corridos da Biblioteca de Conteúdos Úteis, que ajuda você a:',
      '1. Etiquetar con una breve descripción del contenido para que puedas hacer las búsquedas más facilmente': '1. Identificar o conteúdo com uma breve descrição para facilitar as buscas.',
      '2. Tener todos tus enlaces importantes, indistinto de cual plataforma sea, donde la búsqueda la haces en un solo sitio': '2. Manter todos os seus links importantes, independentemente da plataforma, e pesquisá-los em um só lugar.',
      '3. La búsqueda de un enlace en cualquier histórico o favoritos en las aplicaciones, que normalmente toma mucho tiempo encontrarla, la harás en segundos en tu bliblioteca porque las tendrás en un solo lugar y con un motor de búsqueda efectivo': '3. Encontrar em segundos na sua biblioteca um link do histórico ou dos favoritos de qualquer aplicativo, sem perder tempo procurando em vários lugares: tudo fica reunido em um só lugar com uma busca eficiente.',
      '4. Abrir el enlace directamente para ver los contenidos': '4. Abrir o link diretamente para visualizar o conteúdo.',
      '5. Copiar los Enlaces guardados para compartirlos': '5. Copiar os links salvos para compartilhá-los.',
      'Condiciones': 'Condições',
      '1. No incluir información sensitiva ni personal. No nos hacemos responsables del salvaguardar o uso de su información por terceros': '1. Não inclua informações confidenciais ou pessoais. Não nos responsabilizamos pelo armazenamento ou uso das suas informações por terceiros.',
      'Habeas Data': 'Proteção de Dados',
      '2. Tratamiento de los datos bajo la modalidad de': '2. Tratamento de dados conforme os termos de',
      '3. No nos responsabilizamos por información que incite a la violencia física, psicológica, delitos informáticos o cualquier manifestación delictiva': '3. Não nos responsabilizamos por informações que incentivem violência física ou psicológica, crimes cibernéticos ou qualquer outra atividade criminosa.',
      '4. Opciones de valores por Suscripción:': '4. Opções de preços da assinatura:',
      'Pesos Colombianos COP por Tres Meses': 'pesos colombianos (COP) por três meses',
      'Pesos Colombianos COP por Seis Meses': 'pesos colombianos (COP) por seis meses',
      'Pesos Colombianos COP por un Año': 'pesos colombianos (COP) por um ano',
      '5. Al momento de no renovación de suscripción y no querer continuar con el servicios, dispondrá de máximos 3 meses calendario para solicitar sean enviados sus enlaces e información asociados a ellos en archivo CSV. Hacer la solicitud al correo al correo': '5. Se não renovar a assinatura e não quiser continuar com o serviço, você terá até 3 meses corridos para solicitar seus links e as informações relacionadas em um arquivo CSV. Envie a solicitação por email para',
      'y recibirá la información al correo registrado del usuario en la página': 'e as informações serão enviadas ao endereço de email cadastrado no site.',
      'Forma de Pago de Suscripción y Subcategorías': 'Formas de Pagamento da Assinatura e das Subcategorias',
      '1. Pago por Nequi al número +57 305 4293185 o PayPal al YSXRZMT2AAG4G.': '1. Pague pelo Nequi no número +57 305 4293185 ou pelo PayPal em YSXRZMT2AAG4G.',
      '2. Tomar una imagen por cada uno de los pagos realizados y convertirlas a formato PDF': '2. Tire uma foto de cada pagamento e converta as imagens para PDF.',
      '3. Al realizar el pago dar click en el botón de abajo para enviar correo el archivo PDF o una imagen del pago realizado.': '3. Após efetuar o pagamento, clique no botão abaixo para enviar por email o arquivo PDF ou uma imagem do pagamento.',
      '4. Si tiene alguna duda, enviar correo al administrador:': '4. Se tiver dúvidas, envie um email ao administrador:',
      'o WhatsApp: +57 311 7592209': 'ou WhatsApp: +57 311 7592209',
      Soporte: 'Suporte',
      'Resúmen de tu estado de cuenta': 'Resumo da sua conta',
      Usuario: 'Usuário',
      Activa: 'Ativa',
      'Fecha Pago': 'Data do pagamento',
      'Fecha Registro': 'Data de cadastro',
      Suscripcion: 'Assinatura',
      Paquete: 'Pacote',
      'Días disfrutados de la suscripción': 'Dias de assinatura utilizados',
      'Enviar Constancia de Pago por Correo': 'Enviar comprovante de pagamento por email',
      'Mensaje': 'Mensagem',
      'Has alcanzado el límite de 30 días Calendario de uso gratuito y/o tu Suscripción está vencida. Te invitamos a renovarla por medio nuestros canales de Nequi  para Colombia o Paypayl para Internacional': 'Você atingiu o limite de 30 dias corridos de uso gratuito e/ou sua assinatura expirou. Renove pelo Nequi na Colômbia ou pelo PayPal para pagamentos internacionais.',
      'Aceptar': 'OK',
      '1 Mes:': '1 mês:',
      '2 Meses:': '2 meses:',
      '3 Meses:': '3 meses:',
      '4 Meses:': '4 meses:',
      '5 Meses:': '5 meses:',
      '6 Meses:': '6 meses:',
      '7 Meses:': '7 meses:',
      '8 Meses:': '8 meses:',
      '9 Meses:': '9 meses:',
      '10 Meses:': '10 meses:',
      '11 Meses:': '11 meses:',
      '12 Meses:': '12 meses:',
      'De Pago': 'Pago',
      Vencida: 'Expirada',
      Suspendida: 'Suspensa',
      Trial: 'Teste',
      Owner: 'Proprietário',
      Partner: 'Parceiro'
    }
  };

  const originalSubscriptionText = new WeakMap();
  const subscriptionLanguageLabels = {
    es: { es: 'Español', en: 'Inglés', pt: 'Portugués', group: 'Idioma' },
    en: { es: 'Spanish', en: 'English', pt: 'Portuguese', group: 'Language' },
    pt: { es: 'Espanhol', en: 'Inglês', pt: 'Português', group: 'Idioma' }
  };

  function subscriptionTranslation(key, language) {
    return subscriptionTranslations[language]?.[key] || key;
  }

  function setSubscriptionLanguage(language) {
    if (!['es', 'en', 'pt'].includes(language)) {
      language = 'es';
    }

    const manualPaths = {
      es: 'GORAManualUsuarioES.pdf',
      en: 'GORAUserManualEN.pdf',
      pt: 'GORAManualUsuarioPT.pdf'
    };
    const manualLink = document.getElementById('subscription-manual-link');
    if (manualLink) {
      manualLink.href = manualPaths[language];
    }

    localStorage.setItem('smartshelfLang', language);
    localStorage.setItem('moduleLang', language);
    document.documentElement.lang = language;

    document.querySelectorAll('[data-subscription-i18n]').forEach(element => {
      const key = element.dataset.subscriptionI18n;
      const source = element.dataset.subscriptionSource || element.textContent.trim();
      element.dataset.subscriptionSource = source;
      element.textContent = subscriptionTranslations[language]?.[key] || source;
    });

    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    let textNode;
    while ((textNode = walker.nextNode())) {
      const parent = textNode.parentElement;
      if (!parent || parent.closest('[data-subscription-i18n], .username-style, script, style')) {
        continue;
      }

      if (!originalSubscriptionText.has(textNode)) {
        originalSubscriptionText.set(textNode, textNode.nodeValue);
      }

      const original = originalSubscriptionText.get(textNode);
      const source = original.trim().replace(/\s+/g, ' ');
      if (!source) {
        continue;
      }

      const translated = subscriptionTranslation(source, language);
      if (parent.closest('.destacado') && source.startsWith('$')) {
        textNode.nodeValue = original;
      } else {
        textNode.nodeValue = `${original.match(/^\s*/)[0]}${translated}${original.match(/\s*$/)[0]}`;
      }
    }

    document.querySelectorAll('.subscription-language-button').forEach(button => {
      const selected = button.dataset.subscriptionLang === language;
      button.classList.toggle('active', selected);
      button.setAttribute('aria-pressed', String(selected));
      button.setAttribute('aria-label', subscriptionLanguageLabels[language][button.dataset.subscriptionLang]);
    });
    const languageSwitcher = document.querySelector('.subscription-language-switcher');
    if (languageSwitcher) {
      languageSwitcher.setAttribute('aria-label', subscriptionLanguageLabels[language].group);
    }

    const paymentButton = document.getElementById('payment-proof-button');
    if (paymentButton) {
      paymentButton.value = subscriptionTranslation('Enviar Constancia de Pago por Correo', language);
    }

    const paymentLink = document.getElementById('payment-proof-link');
    if (paymentLink) {
      const subjects = {
        es: 'Evidencia Pago Suscripción.',
        en: 'Subscription Payment Proof.',
        pt: 'Comprovante de Pagamento da Assinatura.'
      };
      const bodies = {
        es: `Hola, adjunto evidencia del pago de la suscripción a nombre de ${paymentLink.dataset.username}.`,
        en: `Hello, attached is proof of the subscription payment for ${paymentLink.dataset.username}.`,
        pt: `Olá, segue o comprovante do pagamento da assinatura em nome de ${paymentLink.dataset.username}.`
      };
      const params = new URLSearchParams({
        subject: subjects[language],
        body: bodies[language]
      });
      paymentLink.href = `mailto:adm@solicionespro.com?${params.toString()}`;
    }

    window.dispatchEvent(new Event('languageChanged'));
  }

  document.querySelectorAll('.subscription-language-button').forEach(button => {
    button.addEventListener('click', () => setSubscriptionLanguage(button.dataset.subscriptionLang));
  });

  const savedSubscriptionLanguage = localStorage.getItem('smartshelfLang') || localStorage.getItem('moduleLang') || 'es';
  setSubscriptionLanguage(savedSubscriptionLanguage);

  function alignSubscriptionSidebar() {
    const welcomeNav = document.querySelector('nav#welcome');
    if (!welcomeNav) return;

    const welcomeBottom = Math.ceil(welcomeNav.getBoundingClientRect().bottom);
    document.documentElement.style.setProperty('--welcome-bottom', `${welcomeBottom}px`);
  }

  function toggleSubscriptionSidebar() {
    const sidebar = document.getElementById('subscription-sidebar');
    const isCollapsed = sidebar.classList.toggle('collapsed');
    document.querySelector('.subscription-sidebar-toggle').setAttribute('aria-expanded', String(!isCollapsed));
  }

  alignSubscriptionSidebar();
  window.addEventListener('load', alignSubscriptionSidebar);
  window.addEventListener('resize', alignSubscriptionSidebar);
  window.addEventListener('languageChanged', alignSubscriptionSidebar);
</script>

<?php
if ($suscriptiondue == 1) {
    
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      const selectedLanguage = localStorage.getItem('smartshelfLang') || 'es';
      const alertTranslations = subscriptionTranslations[selectedLanguage] || {};
      Swal.fire({
        title: alertTranslations['Mensaje'] || 'Mensaje',
        text: alertTranslations['Has alcanzado el límite de 30 días Calendario de uso gratuito y/o tu Suscripción está vencida. Te invitamos a renovarla por medio nuestros canales de Nequi  para Colombia o Paypayl para Internacional'] || 'Has alcanzado el límite de 30 días Calendario de uso gratuito y/o tu Suscripción está vencida. Te invitamos a renovarla por medio nuestros canales de Nequi  para Colombia o Paypayl para Internacional',
        icon: 'error',
        confirmButtonText: alertTranslations['Aceptar'] || 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        },
        timer: 5000, // 5000 milisegundos = 5 segundos
        timerProgressBar: true, // Muestra una barra de progreso
        didOpen: () => {
          Swal.showLoading(); // Muestra un indicador de carga
        },
        willClose: () => {
          // Aquí puedes agregar cualquier acción que desees realizar cuando la alerta se cierre
        }
      });
    });
  </script>";
    $_SESSION['suscriptiondue'] = 0;
}
?>
</html>