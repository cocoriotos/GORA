<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
require_once __DIR__ . '/sessions.php';
require_once __DIR__ . '/sessionvalidation.php';
require_once __DIR__ . '/db_connection1.php';
require_once __DIR__ . '/subscription_payment_requests.php';

$name = (string) ($_SESSION['name'] ?? '');
$local_username = (string) ($_SESSION['email'] ?? '');
$suscriptiondue = (($_SESSION['suscriptiondue'] ?? 0) == 1
  || ($_GET['subscription_due'] ?? '') === '1') ? 1 : 0;
$paymentRequestNotice = $_SESSION['subscription_payment_request_notice'] ?? null;
unset($_SESSION['subscription_payment_request_notice']);
$paymentRequestError = '';
if (empty($_SESSION['subscription_payment_request_csrf'])) {
    $_SESSION['subscription_payment_request_csrf'] = bin2hex(random_bytes(32));
}
$paymentRequestCsrf = (string) $_SESSION['subscription_payment_request_csrf'];
if (!isset($_SESSION['subscription_payment_quotes']) || !is_array($_SESSION['subscription_payment_quotes'])) {
    $_SESSION['subscription_payment_quotes'] = [];
}
$activeQuote = null;
$postedQuoteId = (string) ($_POST['quote_id'] ?? $_GET['quote'] ?? '');
if ($postedQuoteId !== '' && isset($_SESSION['subscription_payment_quotes'][$postedQuoteId])) {
    $activeQuote = $_SESSION['subscription_payment_quotes'][$postedQuoteId];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storedProofFile = null;
    $transactionStarted = false;
    try {
        $postedCsrf = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals($paymentRequestCsrf, $postedCsrf)) {
            throw new InvalidArgumentException('La solicitud expiró. Recargue la página e inténtelo de nuevo.');
        }

        $action = (string) ($_POST['payment_action'] ?? '');
        if ($action === 'create_quote') {
            $pendingStmt = $conn->prepare(
                "SELECT request_id
                 FROM gora_subscription_payment_requests
                 WHERE username = ? AND status = 'pending'
                 LIMIT 1"
            );
            $pendingStmt->bind_param('s', $local_username);
            $pendingStmt->execute();
            $hasPendingRequest = $pendingStmt->get_result()->fetch_assoc();
            $pendingStmt->close();
            if ($hasPendingRequest) {
                throw new InvalidArgumentException('Ya tiene un reporte de pago pendiente de revisión.');
            }

            $package = trim((string) ($_POST['package_name'] ?? ''));
            $paymentCountry = trim((string) ($_POST['payment_country'] ?? ''));
            if (!in_array($paymentCountry, ['CO', 'international'], true)) {
                throw new InvalidArgumentException('Seleccione el país desde donde realizará el pago.');
            }
            $paymentMethod = $paymentCountry === 'CO' ? 'Nequi' : 'PayPal';
            $paymentDate = trim((string) ($_POST['payment_date'] ?? ''));
            $activeQuote = goraBuildSubscriptionPaymentQuote($conn, $package, $paymentMethod, $paymentDate);
            $_SESSION['subscription_payment_quotes'][$activeQuote['id']] = $activeQuote;
            header('Location: suscriptionpayment.php?quote=' . rawurlencode($activeQuote['id']), true, 303);
            exit();
        } elseif ($action === 'submit_payment_request') {
            $quoteId = (string) ($_POST['quote_id'] ?? '');
            $quote = $_SESSION['subscription_payment_quotes'][$quoteId] ?? null;
            if (!is_array($quote) || !hash_equals((string) ($quote['id'] ?? ''), $quoteId)) {
                throw new InvalidArgumentException('La cotización ya no está disponible. Genere una cotización nueva.');
            }

            $amountInput = goraNormalizeSubscriptionAmount((string) ($_POST['amount_paid'] ?? ''));
            if ($amountInput === null
                || !preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $amountInput)
                || (float) $amountInput <= 0
            ) {
                throw new InvalidArgumentException('Ingrese el valor enviado en el pago, con máximo dos decimales.');
            }
            $amountPaid = number_format((float) $amountInput, 2, '.', '');
            $paymentReference = trim((string) ($_POST['payment_reference'] ?? ''));
            if (strlen($paymentReference) > 150) {
                throw new InvalidArgumentException('La referencia no puede superar 150 caracteres.');
            }
            $paymentReferenceValue = $paymentReference === '' ? null : $paymentReference;

            $conn->begin_transaction();
            $transactionStarted = true;
            $accountLock = $conn->prepare(
                'SELECT username
                 FROM videotips_app_access_list
                 WHERE username = ?
                 LIMIT 1
                 FOR UPDATE'
            );
            $accountLock->bind_param('s', $local_username);
            $accountLock->execute();
            $accountExists = $accountLock->get_result()->fetch_assoc();
            $accountLock->close();
            if (!$accountExists) {
                throw new RuntimeException('No se encontró la cuenta asociada a esta sesión.');
            }

            $pendingStmt = $conn->prepare(
                "SELECT request_id
                 FROM gora_subscription_payment_requests
                 WHERE username = ? AND status = 'pending'
                 LIMIT 1"
            );
            $pendingStmt->bind_param('s', $local_username);
            $pendingStmt->execute();
            $hasPendingRequest = $pendingStmt->get_result()->fetch_assoc();
            $pendingStmt->close();
            if ($hasPendingRequest) {
                throw new InvalidArgumentException('Ya tiene un reporte de pago pendiente de revisión.');
            }

            if ($paymentReferenceValue !== null) {
                $referenceStmt = $conn->prepare(
                    'SELECT request_id
                     FROM gora_subscription_payment_requests
                     WHERE payment_method = ? AND payment_reference = ?
                     LIMIT 1'
                );
                $referenceStmt->bind_param('ss', $quote['payment_method'], $paymentReferenceValue);
                $referenceStmt->execute();
                $referenceExists = $referenceStmt->get_result()->fetch_assoc();
                $referenceStmt->close();
                if ($referenceExists) {
                    throw new InvalidArgumentException('Esa referencia de pago ya fue reportada.');
                }
            }

            $storedProofFile = goraStorePaymentProof($_FILES['payment_proof'] ?? []);
            $exchangeRate = $quote['exchange_rate'];
            $paypalFeePercent = $quote['paypal_fee_percent'];
            $paypalFixedFee = $quote['paypal_fixed_fee_usd'];
            $priceCop = number_format((float) $quote['price_cop'], 2, '.', '');
            $quotedAmount = (string) $quote['quoted_amount'];
            $insert = $conn->prepare(
                "INSERT INTO gora_subscription_payment_requests
                    (username, package_name, payment_method, payment_date, amount_paid, currency,
                     price_cop, exchange_rate, paypal_fee_percent, paypal_fixed_fee_usd,
                     quoted_amount, payment_reference, proof_file)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $insert->bind_param(
                'sssssssssssss',
                $local_username,
                $quote['package'],
                $quote['payment_method'],
                $quote['payment_date'],
                $amountPaid,
                $quote['currency'],
                $priceCop,
                $exchangeRate,
                $paypalFeePercent,
                $paypalFixedFee,
                $quotedAmount,
                $paymentReferenceValue,
                $storedProofFile
            );
            $insert->execute();
            $requestId = (int) $conn->insert_id;
            $insert->close();
            $conn->commit();
            $transactionStarted = false;

            try {
                $notificationSent = goraSendSubscriptionPaymentNotification($requestId, [
                    'username' => $local_username,
                    'package_name' => $quote['package'],
                    'payment_method' => $quote['payment_method'],
                    'payment_date' => $quote['payment_date'],
                    'amount_paid' => $amountPaid,
                    'currency' => $quote['currency'],
                    'payment_reference' => $paymentReferenceValue,
                ]);
            } catch (Throwable $exception) {
                error_log('Subscription payment notification failed: ' . $exception->getMessage());
                $notificationSent = false;
            }
            if (!$notificationSent) {
                error_log('Subscription payment notification could not be handed to the mail server for request ' . $requestId . '.');
            }

            unset($_SESSION['subscription_payment_quotes'][$quoteId]);
            $_SESSION['subscription_payment_request_notice'] = $notificationSent
                ? 'Reporte recibido. El acceso se actualizará cuando el administrador confirme el pago.'
                : 'El reporte quedó registrado, pero no se pudo enviar el aviso por correo. No lo envíe de nuevo; contacte al administrador.';
            $redirectQuery = $suscriptiondue === 1 ? '?subscription_due=1' : '';
            header('Location: suscriptionpayment.php' . $redirectQuery, true, 303);
            exit();
        } else {
            throw new InvalidArgumentException('La acción solicitada no es válida.');
        }
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        if ($storedProofFile !== null) {
            $proofPath = goraPrivatePaymentProofDirectory() . DIRECTORY_SEPARATOR . $storedProofFile;
            if (is_file($proofPath) && !unlink($proofPath)) {
                error_log('Could not remove an unclaimed subscription payment proof.');
            }
        }
        error_log('Subscription payment request failed: ' . $exception->getMessage());
        $paymentRequestError = !($exception instanceof mysqli_sql_exception)
            && ($exception instanceof InvalidArgumentException || $exception instanceof RuntimeException)
            ? $exception->getMessage()
            : 'No fue posible registrar la solicitud. Inténtelo nuevamente.';
    }
}

if (goraRefreshSubscriptionAccess($conn, $local_username) === null && $paymentRequestError === '') {
    $paymentRequestError = 'No fue posible consultar el estado de acceso de la cuenta.';
}

$requestStmt = $conn->prepare(
    'SELECT package_name, payment_method, payment_date, amount_paid, currency,
            status, rejection_reason, created_at
     FROM gora_subscription_payment_requests
     WHERE username = ?
     ORDER BY request_id DESC
     LIMIT 5'
);
$requestStmt->bind_param('s', $local_username);
$requestStmt->execute();
$paymentRequests = $requestStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$requestStmt->close();
$todayForPayment = goraSubscriptionToday()->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="GORA.ico" type="image/x-icon">
  <link rel="stylesheet" href="style_sheet.css"/>
  <link rel="stylesheet" href="style_sheet_ops.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
  <script src="tab-context.js?v=20261029-1" defer></script>
  <script src="session-timeout.js?v=20261029-3" data-session-generation="<?php echo htmlspecialchars((string) ($_SESSION['gora_session_generation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" data-account-email="<?php echo htmlspecialchars((string) ($_SESSION['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
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
        <?php if ($suscriptiondue === 0): ?>
          <div class="subscription-sidebar-section">
            <a class="subscription-sidebar-link" href="videolinkadminmodule.php">
              <i class="fas fa-arrow-left"></i><span data-subscription-i18n="sidebar_return_to_app">Volver a la aplicación</span>
            </a>
          </div>
        <?php endif; ?>
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
          <a class="subscription-sidebar-link" id="subscription-manual-link" href="manual.php?lang=es" target="_blank" rel="noopener">
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
                    <p> 1. Colombia: Nequi al número +57 305 4293185. Otros países: PayPal a YSXRZMT2AAG4G.</p>
                    <p> 2. Selecciona tu plan, país y fecha del pago. En PayPal se aplica el TRM oficial vigente en esa fecha y la comisión configurada.</p>
                    <p> 3. Después de pagar, registra el pago en el formulario y adjunta obligatoriamente el comprobante en PDF o imagen. El acceso se activa cuando el administrador confirme el pago.</p>
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
        <div class="card card-body">
          <h2 data-subscription-i18n="Reportar pago de suscripción">Reportar pago de suscripción</h2>
          <?php if ($paymentRequestNotice !== null): ?>
            <div class="alert alert-success" role="status"><?php echo htmlspecialchars($paymentRequestNotice, ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
          <?php if ($paymentRequestError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($paymentRequestError, ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
          <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($paymentRequestCsrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="payment_action" value="create_quote">
            <div class="col-md-4">
              <label class="form-label" for="subscription-package" data-subscription-i18n="Plan">Plan</label>
              <select class="form-select" id="subscription-package" name="package_name" required>
                <option value="Trimestral">Trimestral (90 días) — COP 20.000</option>
                <option value="Semestral">Semestral (185 días) — COP 40.000</option>
                <option value="Anual">Anual (365 días) — COP 60.000</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="payment-country" data-subscription-i18n="País desde donde pagas">País desde donde pagas</label>
              <select class="form-select" id="payment-country" name="payment_country" required>
                <option value="CO" data-subscription-i18n="Colombia — Nequi">Colombia — Nequi</option>
                <option value="international" data-subscription-i18n="Otro país — PayPal">Otro país — PayPal</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="payment-date" data-subscription-i18n="Fecha del pago">Fecha del pago</label>
              <input class="form-control" id="payment-date" name="payment_date" type="date"
                max="<?php echo htmlspecialchars($todayForPayment, ENT_QUOTES, 'UTF-8'); ?>"
                value="<?php echo htmlspecialchars($todayForPayment, ENT_QUOTES, 'UTF-8'); ?>" required>
              <small data-subscription-i18n="Indica el día en que realizaste el pago.">Indica el día en que realizaste el pago.</small>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary" data-subscription-i18n="Calcular valor">Calcular valor</button>
            </div>
          </form>

          <?php if ($activeQuote !== null): ?>
            <div class="alert alert-info mt-4">
              <strong data-subscription-i18n="Cotización para pago">Cotización para pago</strong>
              <div><?php echo htmlspecialchars($activeQuote['package'], ENT_QUOTES, 'UTF-8'); ?> —
                <?php echo htmlspecialchars($activeQuote['payment_date'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div>Precio base: COP <?php echo goraFormatSubscriptionMoney($activeQuote['price_cop'], 'COP'); ?></div>
              <?php if ($activeQuote['payment_method'] === 'PayPal'): ?>
                <div>TRM oficial: COP <?php echo number_format((float) $activeQuote['exchange_rate'], 4, ',', '.'); ?> por USD</div>
                <div>Valor base: USD <?php echo goraFormatSubscriptionMoney($activeQuote['base_amount_usd'], 'USD'); ?></div>
                <div>Comisión estimada de PayPal: USD <?php echo goraFormatSubscriptionMoney($activeQuote['estimated_fee_usd'], 'USD'); ?></div>
              <?php endif; ?>
              <div class="fs-5 fw-bold">Total a pagar: <?php echo htmlspecialchars($activeQuote['currency'], ENT_QUOTES, 'UTF-8'); ?>
                <?php echo goraFormatSubscriptionMoney($activeQuote['quoted_amount'], $activeQuote['currency']); ?></div>
              <?php if ($activeQuote['payment_method'] === 'Nequi'): ?>
                <p class="mb-0">Envía el valor exacto al Nequi +57 305 429 3185.</p>
              <?php else: ?>
                <p class="mb-0">Envía el total exacto a PayPal: YSXRZMT2AAG4G. La cotización corresponde al TRM vigente de esta fecha.</p>
              <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="row g-3">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($paymentRequestCsrf, ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="payment_action" value="submit_payment_request">
              <input type="hidden" name="quote_id" value="<?php echo htmlspecialchars($activeQuote['id'], ENT_QUOTES, 'UTF-8'); ?>">
              <div class="col-md-4">
                <label class="form-label" for="amount-paid" data-subscription-i18n="Valor enviado">Valor enviado</label>
                <input class="form-control" id="amount-paid" name="amount_paid" type="text" inputmode="decimal"
                  value="<?php echo goraFormatSubscriptionMoney($activeQuote['quoted_amount'], $activeQuote['currency']); ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="payment-reference" data-subscription-i18n="Referencia de pago (opcional)">Referencia de pago (opcional)</label>
                <input class="form-control" id="payment-reference" name="payment_reference" maxlength="150">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="payment-proof" data-subscription-i18n="Comprobante (obligatorio, máximo 5 MB)">Comprobante (obligatorio, máximo 5 MB)</label>
                <input class="form-control" id="payment-proof" name="payment_proof" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-success" data-subscription-i18n="Enviar reporte para revisión">Enviar reporte para revisión</button>
                <span class="ms-2" data-subscription-i18n="El comprobante será revisado antes de activar el acceso.">El comprobante será revisado antes de activar el acceso.</span>
              </div>
            </form>
          <?php endif; ?>

          <?php if ($paymentRequests): ?>
            <h3 class="mt-4" data-subscription-i18n="Mis reportes de pago">Mis reportes de pago</h3>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead><tr><th>Fecha</th><th>Plan</th><th>Medio</th><th>Valor reportado</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                  <?php foreach ($paymentRequests as $request): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($request['payment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($request['package_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($request['payment_method'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($request['currency'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo goraFormatSubscriptionMoney($request['amount_paid'], $request['currency']); ?></td>
                      <td><?php echo $request['status'] === 'pending' ? 'Pendiente' : ($request['status'] === 'approved' ? 'Aprobado' : 'Rechazado'); ?></td>
                      <td><?php echo htmlspecialchars($request['rejection_reason'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
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
                    <th>Días de acceso restantes</th>
                    <!--<th>Descargar Documento</th>-->
                  </tr>
                  </thead>
                  <tbody>
                    <?php
                    $accountStmt = $conn->prepare(
                        'SELECT username, active, lastsuscriptionpaymentdate, registrationdate,
                                suscriptionkind, suscription_package, daystoenjoy
                         FROM videotips_app_access_list
                         WHERE username = ?'
                    );
                    $accountStmt->bind_param('s', $local_username);
                    $accountStmt->execute();
                    $accountResult = $accountStmt->get_result();
                    while ($links = $accountResult->fetch_assoc()) { ?>
                    <tr>
                      <td align="center"><?php echo htmlspecialchars($links['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td align="center"><?php echo (int) $links['active']; ?></td>
                      <td align="center"><?php echo htmlspecialchars((string) $links['lastsuscriptionpaymentdate'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td align="center"><?php echo htmlspecialchars((string) $links['registrationdate'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td align="center"><?php echo htmlspecialchars($links['suscriptionkind'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td align="center"><?php echo htmlspecialchars((string) $links['suscription_package'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td align="center"><?php echo (int) $links['daystoenjoy']; ?></td>
                    </tr>
                    <?php }
                    $accountStmt->close(); ?>
                  </tbody>
                </table>
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
      sidebar_return_to_app: 'Back to the app',
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
      '1. Colombia: Nequi al número +57 305 4293185. Otros países: PayPal a YSXRZMT2AAG4G.': '1. Colombia: Nequi at +57 305 4293185. Other countries: PayPal at YSXRZMT2AAG4G.',
      '2. Selecciona tu plan, país y fecha del pago. En PayPal se aplica el TRM oficial vigente en esa fecha y la comisión configurada.': '2. Select your plan, country and payment date. PayPal uses the official TRM for that date and the configured fee.',
      '3. Después de pagar, registra el pago en el formulario y adjunta obligatoriamente el comprobante en PDF o imagen. El acceso se activa cuando el administrador confirme el pago.': '3. After paying, submit the payment form and attach the required PDF or image receipt. Access is activated after the administrator confirms the payment.',
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
      'Días de acceso restantes': 'Subscription Access Days Remaining',
      'Reportar pago de suscripción': 'Report a subscription payment',
      Plan: 'Plan',
      'País desde donde pagas': 'Country you are paying from',
      'Colombia — Nequi': 'Colombia — Nequi',
      'Otro país — PayPal': 'Other country — PayPal',
      'Fecha del pago': 'Payment date',
      'Indica el día en que realizaste el pago.': 'Enter the date you made the payment.',
      'Calcular valor': 'Calculate amount',
      'Cotización para pago': 'Payment quote',
      'Precio base:': 'Base price:',
      'TRM oficial:': 'Official TRM:',
      'Valor base:': 'Base amount:',
      'Comisión estimada de PayPal:': 'Estimated PayPal fee:',
      'Total a pagar:': 'Total due:',
      'Valor enviado': 'Amount sent',
      'Referencia de pago (opcional)': 'Payment reference (optional)',
      'Comprobante (obligatorio, máximo 5 MB)': 'Receipt (required, maximum 5 MB)',
      'Enviar reporte para revisión': 'Submit report for review',
      'El comprobante será revisado antes de activar el acceso.': 'The receipt will be reviewed before access is activated.',
      'Mis reportes de pago': 'My payment reports',
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
      sidebar_return_to_app: 'Voltar ao aplicativo',
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
      '1. Colombia: Nequi al número +57 305 429 3185. Otros países: PayPal a YSXRZMT2AAG4G.': '1. Colômbia: Nequi no número +57 305 429 3185. Outros países: PayPal em YSXRZMT2AAG4G.',
      '2. Selecciona tu plan, país y fecha del pago. En PayPal se aplica el TRM oficial vigente en esa fecha y la comisión configurada.': '2. Selecione seu plano, país e data do pagamento. O PayPal usa a TRM oficial dessa data e a comissão configurada.',
      '3. Después de pagar, registra el pago en el formulario y adjunta obligatoriamente el comprobante en PDF o imagen. El acceso se activa cuando el administrador confirme el pago.': '3. Após pagar, informe o pagamento no formulário e anexe o comprovante obrigatório em PDF ou imagem. O acesso é ativado após a confirmação do administrador.',
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
      'Días de acceso restantes': 'Dias restantes de acesso',
      'Reportar pago de suscripción': 'Informar pagamento da assinatura',
      Plan: 'Plano',
      'País desde donde pagas': 'País de onde você paga',
      'Colombia — Nequi': 'Colômbia — Nequi',
      'Otro país — PayPal': 'Outro país — PayPal',
      'Fecha del pago': 'Data do pagamento',
      'Indica el día en que realizaste el pago.': 'Informe o dia em que fez o pagamento.',
      'Calcular valor': 'Calcular valor',
      'Cotización para pago': 'Cotação de pagamento',
      'Precio base:': 'Preço base:',
      'TRM oficial:': 'TRM oficial:',
      'Valor base:': 'Valor base:',
      'Comisión estimada de PayPal:': 'Comissão estimada do PayPal:',
      'Total a pagar:': 'Total a pagar:',
      'Valor enviado': 'Valor enviado',
      'Referencia de pago (opcional)': 'Referência de pagamento (opcional)',
      'Comprobante (obligatorio, máximo 5 MB)': 'Comprovante (obrigatório, máximo 5 MB)',
      'Enviar reporte para revisión': 'Enviar solicitação para análise',
      'El comprobante será revisado antes de activar el acceso.': 'O comprovante será verificado antes da ativação do acesso.',
      'Mis reportes de pago': 'Meus comprovantes de pagamento',
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
      es: 'manual.php?lang=es',
      en: 'manual.php?lang=en',
      pt: 'manual.php?lang=pt'
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
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.delete('subscription_due');
      window.history.replaceState({}, '', currentUrl);
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