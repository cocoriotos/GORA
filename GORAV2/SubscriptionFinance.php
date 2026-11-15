<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
require_once __DIR__ . '/sessions.php';
require_once __DIR__ . '/db_connection1.php';
require_once __DIR__ . '/sessionvalidation.php';
require_once __DIR__ . '/subscription_access.php';

if (empty($_SESSION['email'])) {
    header('Location: videotrackerauth.php');
    exit();
}

$adminEmail = (string) $_SESSION['email'];
$adminStmt = $conn->prepare(
    "SELECT active, adm_role, suscriptionkind
     FROM videotips_app_access_list
     WHERE username = ?
     LIMIT 1"
);
$adminStmt->bind_param('s', $adminEmail);
$adminStmt->execute();
$admin = $adminStmt->get_result()->fetch_assoc();
$adminStmt->close();

if (!goraCanAccessAppManagement($admin)) {
    http_response_code(403);
    exit('No tiene autorización para acceder a este módulo.');
}

if (empty($_SESSION['subscription_finance_csrf'])) {
    $_SESSION['subscription_finance_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['subscription_finance_csrf'];
$flash = $_SESSION['subscription_finance_flash'] ?? null;
unset($_SESSION['subscription_finance_flash']);
$errorMessage = '';
$transactionStarted = false;

function financeEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function financeAmountFromInput(string $input): array
{
    if (!preg_match('/^\d{1,11}(?:\.\d{1,2})?$/D', $input)) {
        throw new InvalidArgumentException('El importe debe ser positivo y tener hasta dos decimales.');
    }

    $parts = explode('.', $input, 2);
    $whole = (int) $parts[0];
    $fraction = isset($parts[1]) ? (int) str_pad($parts[1], 2, '0') : 0;
    $cents = $whole * 100 + $fraction;
    if ($cents <= 0) {
        throw new InvalidArgumentException('El importe debe ser mayor que cero.');
    }

    return [$cents, $whole . '.' . str_pad((string) $fraction, 2, '0', STR_PAD_LEFT)];
}

function financeAmountFromCents(int $cents): string
{
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
}

function financeSplitAmount(int $amountCents): array
{
    $percentages = [
        'investment' => ['percentage' => '10.00', 'whole_percent' => 10],
        'seller' => ['percentage' => '10.00', 'whole_percent' => 10],
        'fedsal' => ['percentage' => '40.00', 'whole_percent' => 40],
        'julgon' => ['percentage' => '40.00', 'whole_percent' => 40],
    ];
    $shares = [];
    $allocatedCents = 0;
    foreach ($percentages as $type => $details) {
        $numerator = $amountCents * $details['whole_percent'];
        $shares[$type] = [
            'percentage' => $details['percentage'],
            'amount_cents' => intdiv($numerator, 100),
            'remainder' => $numerator % 100,
        ];
        $allocatedCents += $shares[$type]['amount_cents'];
    }

    $types = array_keys($shares);
    usort($types, static function (string $left, string $right) use ($shares, $percentages): int {
        $remainderComparison = $shares[$right]['remainder'] <=> $shares[$left]['remainder'];
        return $remainderComparison !== 0
            ? $remainderComparison
            : array_search($left, array_keys($percentages), true) <=> array_search($right, array_keys($percentages), true);
    });

    $remainingCents = $amountCents - $allocatedCents;
    for ($index = 0; $index < $remainingCents; $index++) {
        $shares[$types[$index]]['amount_cents']++;
    }

    $allocations = [];
    foreach ($percentages as $type => $details) {
        $allocations[] = [$type, $details['percentage'], $shares[$type]['amount_cents']];
    }

    return $allocations;
}

function financeSalesCodePart(string $value): string
{
    $ascii = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) : false;
    if ($ascii === false) {
        $ascii = strtr($value, [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c', 'Ñ' => 'N', 'ñ' => 'n',
        ]);
    }
    $letters = strtoupper((string) preg_replace('/[^A-Za-z]/', '', $ascii));
    if (strlen($letters) < 3) {
        throw new InvalidArgumentException('El nombre y el apellido deben tener al menos tres letras para generar el código.');
    }

    return substr($letters, 0, 3);
}

function financeRedirect(string $month, string $userEmail, string $anchor): void
{
    $query = http_build_query(['month' => $month, 'user' => $userEmail]);
    header('Location: SubscriptionFinance.php?' . $query . '#' . $anchor, true, 303);
    exit();
}

$today = date('Y-m-d');
$selectedMonth = (string) ($_GET['month'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}
$selectedUser = trim((string) ($_GET['user'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedMonth = (string) ($_POST['month'] ?? $selectedMonth);
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $postedMonth)) {
        $postedMonth = $selectedMonth;
    }
    $postedUser = trim((string) ($_POST['user_filter'] ?? $selectedUser));

    try {
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals($csrfToken, $postedToken)) {
            throw new InvalidArgumentException('La solicitud expiró. Recargue la página e inténtelo de nuevo.');
        }

        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'add_seller') {
            $firstName = trim((string) ($_POST['first_name'] ?? ''));
            $lastName = trim((string) ($_POST['last_name'] ?? ''));
            $documentNumber = trim((string) ($_POST['document_number'] ?? ''));
            if ($firstName === '' || $lastName === '' || strlen($firstName) > 100 || strlen($lastName) > 100) {
                throw new InvalidArgumentException('Ingrese un nombre y apellido válidos (máximo 100 caracteres cada uno).');
            }
            if (!preg_match('/^\d{4,50}$/D', $documentNumber)) {
                throw new InvalidArgumentException('Ingrese el número de identificación completo, con al menos cuatro dígitos.');
            }

            $documentStmt = $conn->prepare('SELECT seller_id FROM gora_finance_sellers WHERE document_number = ? LIMIT 1');
            $documentStmt->bind_param('s', $documentNumber);
            $documentStmt->execute();
            $documentStmt->store_result();
            $documentExists = $documentStmt->num_rows > 0;
            $documentStmt->close();
            if ($documentExists) {
                throw new InvalidArgumentException('El número de identificación ya está registrado para otro vendedor.');
            }

            $documentLast4 = substr($documentNumber, -4);
            $salesCode = financeSalesCodePart($firstName)
                . financeSalesCodePart($lastName)
                . $documentLast4;
            $codeStmt = $conn->prepare('SELECT seller_id FROM gora_finance_sellers WHERE sales_code = ? LIMIT 1');
            $codeStmt->bind_param('s', $salesCode);
            $codeStmt->execute();
            $codeStmt->store_result();
            $codeExists = $codeStmt->num_rows > 0;
            $codeStmt->close();
            if ($codeExists) {
                throw new InvalidArgumentException('El código generado ya existe. Verifique que el vendedor no esté registrado.');
            }

            $stmt = $conn->prepare(
                'INSERT INTO gora_finance_sellers
                    (first_name, last_name, document_number, document_last4, sales_code, created_by)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssssss', $firstName, $lastName, $documentNumber, $documentLast4, $salesCode, $adminEmail);
            $stmt->execute();
            $stmt->close();

            $_SESSION['subscription_finance_flash'] = ['type' => 'success', 'message' => 'Vendedor registrado. Código: ' . $salesCode];
            financeRedirect($postedMonth, $postedUser, 'sellers');
        }

        if ($action === 'add_subscription_payment') {
            $userEmail = trim((string) ($_POST['user_email'] ?? ''));
            $packageName = trim((string) ($_POST['package_name'] ?? ''));
            $paymentDate = trim((string) ($_POST['payment_date'] ?? ''));
            $currency = trim((string) ($_POST['currency'] ?? ''));
            $paymentMethod = trim((string) ($_POST['payment_method'] ?? ''));
            $paymentReference = trim((string) ($_POST['payment_reference'] ?? ''));
            $sellerId = filter_var($_POST['seller_id'] ?? null, FILTER_VALIDATE_INT);
            $packageDays = goraSubscriptionTermDays($packageName);

            if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Seleccione un usuario válido.');
            }
            if ($packageDays === 0) {
                throw new InvalidArgumentException('Seleccione un paquete de suscripción válido.');
            }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $paymentDate);
            if (!$date || $date->format('Y-m-d') !== $paymentDate || $paymentDate > $today) {
                throw new InvalidArgumentException('La fecha de pago no puede ser futura y debe ser válida.');
            }
            if (!in_array($currency, ['COP', 'USD'], true)) {
                throw new InvalidArgumentException('Seleccione COP o USD como moneda.');
            }
            if (!in_array($paymentMethod, ['PayPal', 'Nequi', 'Transferencia', 'Efectivo', 'Otro'], true)) {
                throw new InvalidArgumentException('Seleccione un medio de pago válido.');
            }
            if ($sellerId === false || $sellerId <= 0) {
                throw new InvalidArgumentException('Seleccione un vendedor activo para la venta.');
            }
            if (strlen($paymentReference) > 150) {
                throw new InvalidArgumentException('La referencia no puede superar 150 caracteres.');
            }
            [$amountCents, $amount] = financeAmountFromInput(trim((string) ($_POST['amount'] ?? '')));
            $paymentMonth = substr($paymentDate, 0, 7);
            $paymentReferenceValue = $paymentReference === '' ? null : $paymentReference;
            $legacyPaymentReference = $paymentReference;

            $conn->begin_transaction();
            $transactionStarted = true;
            $userStmt = $conn->prepare(
                'SELECT username FROM videotips_app_access_list WHERE username = ? LIMIT 1 FOR UPDATE'
            );
            $userStmt->bind_param('s', $userEmail);
            $userStmt->execute();
            $userExists = $userStmt->get_result()->fetch_assoc();
            $userStmt->close();
            if (!$userExists) {
                throw new InvalidArgumentException('El usuario seleccionado ya no existe.');
            }

            $sellerStmt = $conn->prepare(
                'SELECT seller_id FROM gora_finance_sellers WHERE seller_id = ? AND active = 1 LIMIT 1 FOR UPDATE'
            );
            $sellerStmt->bind_param('i', $sellerId);
            $sellerStmt->execute();
            $sellerExists = $sellerStmt->get_result()->fetch_assoc();
            $sellerStmt->close();
            if (!$sellerExists) {
                throw new InvalidArgumentException('El vendedor seleccionado no existe o está inactivo.');
            }

            $accessPaymentStmt = $conn->prepare(
                'SELECT username FROM videotips_suscription_payments WHERE username = ? LIMIT 1 FOR UPDATE'
            );
            $accessPaymentStmt->bind_param('s', $userEmail);
            $accessPaymentStmt->execute();
            $accessPaymentExists = $accessPaymentStmt->get_result()->fetch_assoc();
            $accessPaymentStmt->close();
            if (!$accessPaymentExists) {
                throw new RuntimeException('El usuario no tiene un registro de suscripción asociado. No se guardó el pago.');
            }

            $saleStmt = $conn->prepare(
                'INSERT INTO gora_subscription_sales
                    (user_email, package_name, payment_date, payment_month, amount, currency, payment_method, payment_reference, seller_id, recorded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $saleStmt->bind_param(
                'ssssssssis',
                $userEmail,
                $packageName,
                $paymentDate,
                $paymentMonth,
                $amount,
                $currency,
                $paymentMethod,
                $paymentReferenceValue,
                $sellerId,
                $adminEmail
            );
            $saleStmt->execute();
            $paymentId = (int) $conn->insert_id;
            $saleStmt->close();

            $allocations = financeSplitAmount($amountCents);
            $allocationStmt = $conn->prepare(
                'INSERT INTO gora_subscription_allocations
                    (payment_id, allocation_type, percentage, amount, currency, payment_month, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($allocations as [$allocationType, $percentage, $allocationCents]) {
                $allocationAmount = financeAmountFromCents($allocationCents);
                $allocationStatus = $allocationCents === 0 ? 'not_applicable' : 'pending';
                $allocationStmt->bind_param(
                    'issssss',
                    $paymentId,
                    $allocationType,
                    $percentage,
                    $allocationAmount,
                    $currency,
                    $paymentMonth,
                    $allocationStatus
                );
                $allocationStmt->execute();
            }
            $allocationStmt->close();

            $subscriptionUpdate = $conn->prepare(
                "UPDATE videotips_app_access_list
                 SET active = 1, suscriptionactive = 1, suscriptionpayed = 1,
                     lastsuscriptionpaymentdate = ?, suscriptionkind = 'De Pago',
                     suscription_package = ?
                 WHERE username = ?"
            );
            $subscriptionUpdate->bind_param('sss', $paymentDate, $packageName, $userEmail);
            $subscriptionUpdate->execute();
            $subscriptionUpdate->close();

            $existingPaymentUpdate = $conn->prepare(
                'UPDATE videotips_suscription_payments
                 SET currentpaid = currentpaid + 1,
                     paymentcounter = paymentcounter + 1,
                     lastpaymentdate = ?,
                     suscription_package = ?,
                     proofofpurchasenumber = ?
                 WHERE username = ?'
            );
            $existingPaymentUpdate->bind_param(
                'ssss',
                $paymentDate,
                $packageName,
                $legacyPaymentReference,
                $userEmail
            );
            $existingPaymentUpdate->execute();
            if ($existingPaymentUpdate->affected_rows !== 1) {
                $existingPaymentUpdate->close();
                throw new RuntimeException('No fue posible actualizar la suscripción existente. El pago no se guardó.');
            }
            $existingPaymentUpdate->close();

            if (goraRefreshSubscriptionAccess($conn, $userEmail) === null) {
                throw new RuntimeException('No fue posible verificar el acceso actualizado. El pago no se guardó.');
            }
            $conn->commit();
            $transactionStarted = false;

            $_SESSION['subscription_finance_flash'] = ['type' => 'success', 'message' => 'Pago registrado y suscripción actualizada.'];
            financeRedirect($paymentMonth, $userEmail, 'subscription-payments');
        }

        if ($action === 'mark_allocation_paid') {
            $allocationId = filter_var($_POST['allocation_id'] ?? null, FILTER_VALIDATE_INT);
            $paidReference = trim((string) ($_POST['paid_reference'] ?? ''));
            if ($allocationId === false || $allocationId <= 0 || strlen($paidReference) > 150) {
                throw new InvalidArgumentException('La cuota o referencia de pago no es válida.');
            }
            $paidReferenceValue = $paidReference === '' ? null : $paidReference;
            $stmt = $conn->prepare(
                "UPDATE gora_subscription_allocations
                 SET status = 'paid', paid_date = CURDATE(), paid_reference = ?, paid_by = ?
                 WHERE allocation_id = ? AND status = 'pending'"
            );
            $stmt->bind_param('ssi', $paidReferenceValue, $adminEmail, $allocationId);
            $stmt->execute();
            $updated = $stmt->affected_rows === 1;
            $stmt->close();
            if (!$updated) {
                throw new InvalidArgumentException('La cuota ya fue pagada o no existe.');
            }

            $_SESSION['subscription_finance_flash'] = ['type' => 'success', 'message' => 'Cuota marcada como pagada.'];
            financeRedirect($postedMonth, $postedUser, 'monthly-distributions');
        }

        throw new InvalidArgumentException('La acción solicitada no es válida.');
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            try {
                $conn->rollback();
                $transactionStarted = false;
            } catch (Throwable $rollbackException) {
                error_log('Subscription finance rollback failed: ' . $rollbackException->getMessage());
            }
        }
        error_log('Subscription finance operation failed: ' . $exception->getMessage());
        $errorMessage = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'No fue posible completar la operación. Verifique el esquema y los datos e inténtelo nuevamente.';
        $selectedMonth = $postedMonth;
        $selectedUser = $postedUser;
    }
}

$users = [];
$userResult = $conn->query(
    'SELECT username, name, lastname, active
     FROM videotips_app_access_list
     ORDER BY lastname, name, username'
);
while ($row = $userResult->fetch_assoc()) {
    $users[] = $row;
}
$userResult->free();

$sellers = [];
$sellerResult = $conn->query(
    'SELECT seller_id, first_name, last_name, document_last4, sales_code, active
     FROM gora_finance_sellers
     ORDER BY active DESC, last_name, first_name'
);
while ($row = $sellerResult->fetch_assoc()) {
    $sellers[] = $row;
}
$sellerResult->free();

$paymentStmt = $conn->prepare(
    'SELECT p.payment_id, p.user_email, p.package_name, p.payment_date, p.amount, p.currency,
            p.payment_method, p.payment_reference, s.first_name, s.last_name, s.sales_code,
            p.recorded_by
     FROM gora_subscription_sales p
     JOIN gora_finance_sellers s ON s.seller_id = p.seller_id
     WHERE p.payment_month = ? AND (? = \'\' OR p.user_email = ?)
     ORDER BY p.payment_date DESC, p.payment_id DESC'
);
$paymentStmt->bind_param('sss', $selectedMonth, $selectedUser, $selectedUser);
$paymentStmt->execute();
$payments = $paymentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$paymentStmt->close();

$allocationStmt = $conn->prepare(
    'SELECT a.allocation_id, a.payment_id, a.allocation_type, a.percentage, a.amount, a.currency,
            a.payment_month, a.status, a.paid_date, a.paid_reference, a.paid_by,
            p.user_email, p.payment_date, p.package_name,
            s.first_name, s.last_name, s.sales_code
     FROM gora_subscription_allocations a
     JOIN gora_subscription_sales p ON p.payment_id = a.payment_id
     JOIN gora_finance_sellers s ON s.seller_id = p.seller_id
     WHERE a.payment_month = ? AND (? = \'\' OR p.user_email = ?)
     ORDER BY a.status = \'pending\' DESC, p.payment_date, a.payment_id, a.allocation_id'
);
$allocationStmt->bind_param('sss', $selectedMonth, $selectedUser, $selectedUser);
$allocationStmt->execute();
$allocations = $allocationStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$allocationStmt->close();

$totalsStmt = $conn->prepare(
    'SELECT currency, SUM(amount) AS total_amount, COUNT(*) AS payment_count
     FROM gora_subscription_sales
     WHERE payment_month = ? AND (? = \'\' OR user_email = ?)
     GROUP BY currency
     ORDER BY currency'
);
$totalsStmt->bind_param('sss', $selectedMonth, $selectedUser, $selectedUser);
$totalsStmt->execute();
$totals = $totalsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$totalsStmt->close();

$allocationTotalsStmt = $conn->prepare(
    'SELECT a.allocation_type, a.currency, a.status, SUM(a.amount) AS total_amount
     FROM gora_subscription_allocations a
     JOIN gora_subscription_sales p ON p.payment_id = a.payment_id
     WHERE a.payment_month = ? AND a.status <> \'not_applicable\'
       AND (? = \'\' OR p.user_email = ?)
     GROUP BY a.allocation_type, a.currency, a.status
     ORDER BY a.allocation_type, a.currency, a.status'
);
$allocationTotalsStmt->bind_param('sss', $selectedMonth, $selectedUser, $selectedUser);
$allocationTotalsStmt->execute();
$allocationTotals = $allocationTotalsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$allocationTotalsStmt->close();

$currencyLabels = ['COP' => 'COP', 'USD' => 'USD'];
$allocationLabels = [
    'investment' => 'Fondo de inversión',
    'seller' => 'Vendedor',
    'fedsal' => 'FedSal',
    'julgon' => 'JulGon',
];
$statusLabels = ['pending' => 'Pendiente', 'paid' => 'Pagado', 'not_applicable' => 'No aplica'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control financiero de suscripciones | GORA</title>
    <link rel="icon" href="GORA.ico" type="image/x-icon">
    <link rel="stylesheet" href="style_sheet_ops.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="tab-context.js?v=20261029-1" defer></script>
    <script src="session-timeout.js?v=20261029-3" data-session-generation="<?php echo financeEscape($_SESSION['gora_session_generation'] ?? ''); ?>" data-account-email="<?php echo financeEscape($adminEmail); ?>" defer></script>
    <style>
        body.finance-page {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            background: #f3f6f9;
            color: #172b3a;
        }
        .finance-shell { max-width: 1440px; margin: 0 auto; }
        .finance-header, .finance-card {
            margin-bottom: 20px;
            padding: 22px;
            border: 1px solid #dfe7ed;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 26px rgba(3, 38, 66, .07);
        }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; background: #032642; color: #fff; }
        .finance-header h1, .finance-card h2 { margin: 0 0 12px; }
        .finance-header h1 { margin: 0; font-size: clamp(1.35rem, 3vw, 2rem); }
        .finance-header a { color: #fff; }
        .finance-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 20px; }
        .finance-card h2 { color: #032642; font-size: 1.25rem; }
        .finance-card form { display: grid; gap: 12px; }
        .finance-card label { display: grid; gap: 5px; font-weight: 600; }
        .finance-card input, .finance-card select {
            width: 100%; min-width: 0; padding: 10px 12px; border: 1px solid #c9d4de;
            border-radius: 8px; background: #fff; color: #172b3a; font: inherit;
        }
        .finance-button {
            display: inline-flex; width: fit-content; align-items: center; justify-content: center;
            gap: 8px; padding: 10px 16px; border: 0; border-radius: 8px;
            background: #0f766e; color: #fff; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer;
        }
        .finance-button:hover { background: #115e59; color: #fff; }
        .finance-button.secondary { background: #032642; }
        .finance-button.small { padding: 7px 10px; font-size: .9rem; }
        .finance-notice { margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; }
        .finance-notice.success { background: #e8f7ef; color: #155d36; }
        .finance-notice.error { background: #fff0ef; color: #8a1f17; }
        .finance-note { color: #526676; font-size: .9rem; }
        .finance-filter { display: flex !important; flex-wrap: wrap; align-items: end; gap: 12px !important; }
        .finance-filter label { flex: 1 1 190px; }
        .finance-summary { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
        .finance-summary span { padding: 10px 14px; border-radius: 10px; background: #eaf2f7; font-weight: 700; }
        .finance-table-wrap { overflow-x: auto; margin-top: 14px; }
        .finance-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        .finance-table th, .finance-table td { padding: 10px; border-bottom: 1px solid #e2e8ee; text-align: left; vertical-align: middle; }
        .finance-table th { background: #032642; color: #fff; }
        .finance-table td form { display: flex; align-items: center; gap: 8px; }
        .finance-table td input { min-width: 130px; }
        .finance-status { font-weight: 700; }
        .finance-status.pending { color: #9a5d00; }
        .finance-status.paid { color: #16703b; }
        .finance-status.not_applicable { color: #526676; }
        .finance-toolbar { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 16px; }
        .finance-toolbar form { display: flex; flex-wrap: wrap; align-items: end; gap: 12px; }
        .finance-empty { padding: 18px; color: #526676; text-align: center; }
        @media (max-width: 640px) {
            body.finance-page { padding: 12px; }
            .finance-header, .finance-card { padding: 16px; }
            .finance-header { align-items: flex-start; flex-direction: column; }
            .finance-table td form { align-items: stretch; flex-direction: column; }
        }
    </style>
</head>
<body class="finance-page">
<main class="finance-shell">
    <header class="finance-header">
        <h1 data-finance-i18n="page_title">Control financiero de suscripciones</h1>
        <a href="AppMgmt.php" class="finance-button secondary"><i class="fa-solid fa-arrow-left"></i> <span data-finance-i18n="back_admin">Volver a administración</span></a>
    </header>

    <?php if ($flash): ?>
        <div class="finance-notice <?php echo financeEscape($flash['type'] ?? 'success'); ?>" role="status"><?php echo financeEscape($flash['message'] ?? ''); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="finance-notice error" role="alert"><?php echo financeEscape($errorMessage); ?></div>
    <?php endif; ?>

    <div class="finance-grid">
        <section class="finance-card" id="sellers">
            <h2 data-finance-i18n="seller_registration">Registro de vendedores</h2>
            <p class="finance-note" data-finance-i18n="seller_id_note">Se registra el número de identificación completo; el código usa las tres primeras letras del nombre, las tres del apellido y los últimos cuatro dígitos.</p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo financeEscape($csrfToken); ?>">
                <input type="hidden" name="action" value="add_seller">
                <input type="hidden" name="month" value="<?php echo financeEscape($selectedMonth); ?>">
                <input type="hidden" name="user_filter" value="<?php echo financeEscape($selectedUser); ?>">
                <label><span data-finance-i18n="first_name">Nombre</span><input name="first_name" maxlength="100" required></label>
                <label><span data-finance-i18n="last_name">Apellido</span><input name="last_name" maxlength="100" required></label>
                <label><span data-finance-i18n="document_number">Número de identificación completo</span><input name="document_number" inputmode="numeric" pattern="[0-9]{4,50}" maxlength="50" autocomplete="off" required></label>
                <button class="finance-button" type="submit"><i class="fa-solid fa-user-plus"></i> <span data-finance-i18n="register_seller">Registrar vendedor</span></button>
            </form>

            <div class="finance-table-wrap">
                <table class="finance-table">
                    <thead><tr>
                        <th data-finance-i18n="seller">Vendedor</th>
                        <th data-finance-i18n="sales_code">Código de ventas</th>
                        <th data-finance-i18n="document_last4_short">Doc. (4 últimos)</th>
                        <th data-finance-i18n="seller_status">Estado</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!$sellers): ?>
                        <tr><td class="finance-empty" colspan="4" data-finance-i18n="no_sellers">Aún no hay vendedores registrados.</td></tr>
                    <?php else: foreach ($sellers as $seller): ?>
                        <tr>
                            <td><?php echo financeEscape($seller['first_name'] . ' ' . $seller['last_name']); ?></td>
                            <td><?php echo financeEscape($seller['sales_code']); ?></td>
                            <td><?php echo financeEscape($seller['document_last4']); ?></td>
                            <td data-finance-i18n="<?php echo (int) $seller['active'] === 1 ? 'active' : 'inactive'; ?>"><?php echo (int) $seller['active'] === 1 ? 'Activo' : 'Inactivo'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="finance-card" id="register-payment">
            <h2 data-finance-i18n="register_subscription_payment">Registro de pago de suscripción</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo financeEscape($csrfToken); ?>">
                <input type="hidden" name="action" value="add_subscription_payment">
                <input type="hidden" name="month" value="<?php echo financeEscape($selectedMonth); ?>">
                <input type="hidden" name="user_filter" value="<?php echo financeEscape($selectedUser); ?>">
                <label><span data-finance-i18n="user">Usuario</span>
                    <select name="user_email" required>
                        <option value="" data-finance-i18n="select_user">Seleccione usuario</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo financeEscape($user['username']); ?>">
                                <?php echo financeEscape(trim($user['name'] . ' ' . $user['lastname']) . ' (' . $user['username'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><span data-finance-i18n="package">Paquete</span>
                    <select name="package_name" required>
                        <option value="" data-finance-i18n="select_package">Seleccione paquete</option>
                        <option value="Trimestral" data-finance-i18n="quarterly">Trimestral (90 días)</option>
                        <option value="Semestral" data-finance-i18n="semiannual">Semestral (185 días)</option>
                        <option value="Anual" data-finance-i18n="annual">Anual (365 días)</option>
                    </select>
                </label>
                <label><span data-finance-i18n="payment_date">Fecha de pago</span><input type="date" name="payment_date" max="<?php echo financeEscape($today); ?>" value="<?php echo financeEscape($today); ?>" required></label>
                <label><span data-finance-i18n="amount">Importe</span><input name="amount" type="number" min="0.01" step="0.01" inputmode="decimal" required></label>
                <label><span data-finance-i18n="currency">Moneda</span>
                    <select name="currency" required><option value="COP">COP</option><option value="USD">USD</option></select>
                </label>
                <label><span data-finance-i18n="payment_method">Medio de pago</span>
                    <select name="payment_method" required>
                        <option value="" data-finance-i18n="select_method">Seleccione medio</option>
                        <option value="PayPal">PayPal</option><option value="Nequi">Nequi</option>
                        <option value="Transferencia" data-finance-i18n="bank_transfer">Transferencia</option>
                        <option value="Efectivo" data-finance-i18n="cash">Efectivo</option>
                        <option value="Otro" data-finance-i18n="other">Otro</option>
                    </select>
                </label>
                <label><span data-finance-i18n="payment_reference">Comprobante o referencia (opcional)</span><input name="payment_reference" maxlength="150"></label>
                <label><span data-finance-i18n="seller">Vendedor</span>
                    <select name="seller_id" required>
                        <option value="" data-finance-i18n="select_seller">Seleccione vendedor</option>
                        <?php foreach ($sellers as $seller): if ((int) $seller['active'] !== 1) continue; ?>
                            <option value="<?php echo (int) $seller['seller_id']; ?>">
                                <?php echo financeEscape($seller['sales_code'] . ' — ' . $seller['first_name'] . ' ' . $seller['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="finance-button" type="submit"><i class="fa-solid fa-circle-check"></i> <span data-finance-i18n="save_payment">Guardar pago y actualizar acceso</span></button>
            </form>
            <p class="finance-note" data-finance-i18n="split_note">El importe se reparte automáticamente: 10% inversión, 10% vendedor, 40% FedSal y 40% JulGon. Se redondea al centavo y el reparto siempre suma el pago total. Las cuotas se registran pendientes para el mes del pago; los importes se mantienen separados por moneda.</p>
        </section>
    </div>

    <section class="finance-card" id="reports">
        <div class="finance-toolbar">
            <h2 data-finance-i18n="monthly_reports">Reportes por mes o usuario</h2>
            <form method="get" class="finance-filter">
                <label><span data-finance-i18n="month">Mes</span><input type="month" name="month" value="<?php echo financeEscape($selectedMonth); ?>"></label>
                <label><span data-finance-i18n="filter_user">Usuario (opcional)</span>
                    <select name="user">
                        <option value="" data-finance-i18n="all_users">Todos los usuarios</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo financeEscape($user['username']); ?>" <?php echo $selectedUser === $user['username'] ? 'selected' : ''; ?>>
                                <?php echo financeEscape(trim($user['name'] . ' ' . $user['lastname']) . ' (' . $user['username'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="finance-button secondary" type="submit"><i class="fa-solid fa-filter"></i> <span data-finance-i18n="apply_filter">Aplicar filtro</span></button>
            </form>
        </div>
        <div class="finance-summary">
            <?php if (!$totals): ?>
                <span data-finance-i18n="no_month_payments">Sin pagos registrados para este filtro.</span>
            <?php else: foreach ($totals as $total): ?>
                <span>
                    <?php echo financeEscape($total['currency']); ?>:
                    <?php echo financeEscape(number_format((float) $total['total_amount'], 2)); ?>
                    (<?php echo (int) $total['payment_count']; ?>)
                </span>
            <?php endforeach; endif; ?>
        </div>

        <h2 id="subscription-payments" data-finance-i18n="subscription_payments">Pagos de suscripción</h2>
        <div class="finance-table-wrap">
            <table class="finance-table">
                <thead><tr>
                    <th data-finance-i18n="payment_date">Fecha</th><th data-finance-i18n="user">Usuario</th>
                    <th data-finance-i18n="package">Paquete</th><th data-finance-i18n="amount">Importe</th>
                    <th data-finance-i18n="payment_method">Medio</th><th data-finance-i18n="payment_reference_short">Referencia</th>
                    <th data-finance-i18n="seller">Vendedor</th><th data-finance-i18n="recorded_by">Registrado por</th>
                </tr></thead>
                <tbody>
                <?php if (!$payments): ?>
                    <tr><td class="finance-empty" colspan="8" data-finance-i18n="no_month_payments">Sin pagos registrados para este filtro.</td></tr>
                <?php else: foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo financeEscape($payment['payment_date']); ?></td>
                        <td><?php echo financeEscape($payment['user_email']); ?></td>
                        <td><?php echo financeEscape($payment['package_name']); ?></td>
                        <td><?php echo financeEscape($payment['currency'] . ' ' . number_format((float) $payment['amount'], 2)); ?></td>
                        <td><?php echo financeEscape($payment['payment_method']); ?></td>
                        <td><?php echo financeEscape($payment['payment_reference'] ?? '—'); ?></td>
                        <td><?php echo financeEscape($payment['sales_code'] . ' — ' . $payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                        <td><?php echo financeEscape($payment['recorded_by']); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <h2 id="monthly-distributions" data-finance-i18n="monthly_distributions">Comisiones y distribuciones del mes</h2>
        <div class="finance-summary">
            <?php foreach ($allocationTotals as $total): ?>
                <span>
                    <span data-finance-i18n="<?php echo financeEscape($total['allocation_type'] === 'investment' ? 'investment' : ($total['allocation_type'] === 'seller' ? 'seller' : $total['allocation_type'])); ?>"><?php echo financeEscape($allocationLabels[$total['allocation_type']] ?? $total['allocation_type']); ?></span>
                    — <?php echo financeEscape($total['currency']); ?>
                    — <span data-finance-i18n="<?php echo financeEscape($total['status']); ?>"><?php echo financeEscape($statusLabels[$total['status']] ?? $total['status']); ?></span>:
                    <?php echo financeEscape(number_format((float) $total['total_amount'], 2)); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <div class="finance-table-wrap">
            <table class="finance-table">
                <thead><tr>
                    <th data-finance-i18n="user">Usuario</th><th data-finance-i18n="payment_date">Fecha pago</th>
                    <th data-finance-i18n="beneficiary">Beneficiario / reparto</th><th data-finance-i18n="percentage">Porcentaje</th>
                    <th data-finance-i18n="amount">Importe</th><th data-finance-i18n="payout_status">Estado de pago</th>
                    <th data-finance-i18n="paid_date">Fecha pagado</th><th data-finance-i18n="payout_action">Registro de pago</th>
                </tr></thead>
                <tbody>
                <?php if (!$allocations): ?>
                    <tr><td class="finance-empty" colspan="8" data-finance-i18n="no_month_allocations">Sin comisiones o distribuciones para este filtro.</td></tr>
                <?php else: foreach ($allocations as $allocation): ?>
                    <?php
                    $type = $allocation['allocation_type'];
                    $beneficiary = $type === 'seller'
                        ? $allocation['sales_code'] . ' — ' . $allocation['first_name'] . ' ' . $allocation['last_name']
                        : ($allocationLabels[$type] ?? $type);
                    $beneficiaryTranslationKey = $type === 'investment' ? 'investment' : ($type === 'seller' ? 'seller' : $type);
                    $status = $allocation['status'];
                    ?>
                    <tr>
                        <td><?php echo financeEscape($allocation['user_email']); ?></td>
                        <td><?php echo financeEscape($allocation['payment_date']); ?></td>
                        <td><?php if ($type === 'seller') { echo financeEscape($beneficiary); } else { ?><span data-finance-i18n="<?php echo financeEscape($beneficiaryTranslationKey); ?>"><?php echo financeEscape($beneficiary); ?></span><?php } ?></td>
                        <td><?php echo financeEscape(number_format((float) $allocation['percentage'], 2)); ?>%</td>
                        <td><?php echo financeEscape($allocation['currency'] . ' ' . number_format((float) $allocation['amount'], 2)); ?></td>
                        <td class="finance-status <?php echo financeEscape($status); ?>" data-finance-i18n="<?php echo financeEscape($status); ?>"><?php echo financeEscape($statusLabels[$status] ?? $status); ?></td>
                        <td><?php echo financeEscape($allocation['paid_date'] ?? '—'); ?></td>
                        <td>
                            <?php if ($status === 'pending'): ?>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo financeEscape($csrfToken); ?>">
                                    <input type="hidden" name="action" value="mark_allocation_paid">
                                    <input type="hidden" name="allocation_id" value="<?php echo (int) $allocation['allocation_id']; ?>">
                                    <input type="hidden" name="month" value="<?php echo financeEscape($selectedMonth); ?>">
                                    <input type="hidden" name="user_filter" value="<?php echo financeEscape($selectedUser); ?>">
                                    <input type="text" name="paid_reference" maxlength="150" data-finance-i18n-placeholder="paid_reference_placeholder" placeholder="Referencia del pago (opcional)">
                                    <button class="finance-button small" type="submit"><span data-finance-i18n="mark_paid">Marcar pagado</span></button>
                                </form>
                            <?php elseif ($status === 'paid' && !empty($allocation['paid_reference'])): ?>
                                <?php echo financeEscape($allocation['paid_reference']); ?>
                            <?php else: ?>
                                <span data-finance-i18n="<?php echo financeEscape($status); ?>"><?php echo financeEscape($statusLabels[$status] ?? $status); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <p class="finance-note" data-finance-i18n="allocations_note">Cada cuota se marca pagada por separado y registra la fecha, el administrador y una referencia opcional. Los pagos son de cuota completa; no se permiten abonos parciales.</p>
    </section>
</main>
<script>
const financeTranslations = {
    es: {
        page_title:'Control financiero de suscripciones',back_admin:'Volver a administración',
        seller_registration:'Registro de vendedores',seller_id_note:'Se registra el número de identificación completo; el código usa las tres primeras letras del nombre, las tres del apellido y los últimos cuatro dígitos.',
        first_name:'Nombre',last_name:'Apellido',document_number:'Número de identificación completo',register_seller:'Registrar vendedor',
        seller:'Vendedor',sales_code:'Código de ventas',document_last4_short:'Doc. (4 últimos)',seller_status:'Estado',no_sellers:'Aún no hay vendedores registrados.',
        active:'Activo',inactive:'Inactivo',register_subscription_payment:'Registro de pago de suscripción',user:'Usuario',select_user:'Seleccione usuario',
        package:'Paquete',select_package:'Seleccione paquete',quarterly:'Trimestral (90 días)',semiannual:'Semestral (185 días)',annual:'Anual (365 días)',
        payment_date:'Fecha de pago',amount:'Importe',currency:'Moneda',payment_method:'Medio de pago',select_method:'Seleccione medio',
        bank_transfer:'Transferencia',cash:'Efectivo',other:'Otro',payment_reference:'Comprobante o referencia (opcional)',select_seller:'Seleccione vendedor',
        save_payment:'Guardar pago y actualizar acceso',split_note:'El importe se reparte automáticamente: 10% inversión, 10% vendedor, 40% FedSal y 40% JulGon. Se redondea al centavo y el reparto siempre suma el pago total. Las cuotas se registran pendientes para el mes del pago; los importes se mantienen separados por moneda.',
        monthly_reports:'Reportes por mes o usuario',month:'Mes',filter_user:'Usuario (opcional)',all_users:'Todos los usuarios',apply_filter:'Aplicar filtro',
        no_month_payments:'Sin pagos registrados para este filtro.',subscription_payments:'Pagos de suscripción',payment_reference_short:'Referencia',recorded_by:'Registrado por',
        monthly_distributions:'Comisiones y distribuciones del mes',beneficiary:'Beneficiario / reparto',percentage:'Porcentaje',payout_status:'Estado de pago',
        paid_date:'Fecha pagado',payout_action:'Registro de pago',no_month_allocations:'Sin comisiones o distribuciones para este filtro.',
        pending:'Pendiente',paid:'Pagado',not_applicable:'No aplica',mark_paid:'Marcar pagado',
        paid_reference_placeholder:'Referencia del pago (opcional)',
        allocations_note:'Cada cuota se marca pagada por separado y registra la fecha, el administrador y una referencia opcional. Los pagos son de cuota completa; no se permiten abonos parciales.',
        investment:'Fondo de inversión',seller_share:'Vendedor',fedsal:'FedSal',julgon:'JulGon'
    },
    en: {
        page_title:'Subscription financial control',back_admin:'Back to administration',
        seller_registration:'Seller registration',seller_id_note:'The full ID number is recorded; the code uses the first three letters of the first name, the first three of the last name, and the final four ID digits.',
        first_name:'First name',last_name:'Last name',document_number:'Full identification number',register_seller:'Register seller',
        seller:'Seller',sales_code:'Sales code',document_last4_short:'ID (last 4)',seller_status:'Status',no_sellers:'No sellers registered yet.',
        active:'Active',inactive:'Inactive',register_subscription_payment:'Subscription payment registration',user:'User',select_user:'Select user',
        package:'Plan',select_package:'Select a plan',quarterly:'Quarterly (90 days)',semiannual:'Semiannual (185 days)',annual:'Annual (365 days)',
        payment_date:'Payment date',amount:'Amount',currency:'Currency',payment_method:'Payment method',select_method:'Select a method',
        bank_transfer:'Bank transfer',cash:'Cash',other:'Other',payment_reference:'Receipt or reference (optional)',select_seller:'Select seller',
        save_payment:'Save payment and update access',split_note:'The amount is automatically allocated: 10% investment, 10% seller, 40% FedSal and 40% JulGon. Allocations are rounded to the cent and always add up to the full payment. They are pending in the payment month; amounts remain separated by currency.',
        monthly_reports:'Reports by month or user',month:'Month',filter_user:'User (optional)',all_users:'All users',apply_filter:'Apply filter',
        no_month_payments:'No payments for this filter.',subscription_payments:'Subscription payments',payment_reference_short:'Reference',recorded_by:'Recorded by',
        monthly_distributions:'Monthly commissions and allocations',beneficiary:'Beneficiary / allocation',percentage:'Percentage',payout_status:'Payment status',
        paid_date:'Date paid',payout_action:'Payment record',no_month_allocations:'No commissions or allocations for this filter.',
        pending:'Pending',paid:'Paid',not_applicable:'N/A',mark_paid:'Mark paid',
        paid_reference_placeholder:'Payment reference (optional)',
        allocations_note:'Each allocation is marked paid separately with the date, administrator and an optional reference. Allocations must be paid in full; partial payments are not supported.',
        investment:'Investment fund',seller_share:'Seller',fedsal:'FedSal',julgon:'JulGon'
    },
    pt: {
        page_title:'Controle financeiro de assinaturas',back_admin:'Voltar à administração',
        seller_registration:'Cadastro de vendedores',seller_id_note:'O número completo de identificação é registrado; o código usa as três primeiras letras do nome, as três do sobrenome e os últimos quatro dígitos.',
        first_name:'Nome',last_name:'Sobrenome',document_number:'Número de identificação completo',register_seller:'Cadastrar vendedor',
        seller:'Vendedor',sales_code:'Código de vendas',document_last4_short:'Doc. (últimos 4)',seller_status:'Status',no_sellers:'Ainda não há vendedores cadastrados.',
        active:'Ativo',inactive:'Inativo',register_subscription_payment:'Cadastro de pagamento da assinatura',user:'Usuário',select_user:'Selecione o usuário',
        package:'Plano',select_package:'Selecione um plano',quarterly:'Trimestral (90 dias)',semiannual:'Semestral (185 dias)',annual:'Anual (365 dias)',
        payment_date:'Data do pagamento',amount:'Valor',currency:'Moeda',payment_method:'Forma de pagamento',select_method:'Selecione a forma',
        bank_transfer:'Transferência',cash:'Dinheiro',other:'Outro',payment_reference:'Comprovante ou referência (opcional)',select_seller:'Selecione o vendedor',
        save_payment:'Salvar pagamento e atualizar acesso',split_note:'O valor é distribuído automaticamente: 10% investimento, 10% vendedor, 40% FedSal e 40% JulGon. Os valores são arredondados ao centavo e sempre somam o total pago. As parcelas ficam pendentes no mês do pagamento; os valores permanecem separados por moeda.',
        monthly_reports:'Relatórios por mês ou usuário',month:'Mês',filter_user:'Usuário (opcional)',all_users:'Todos os usuários',apply_filter:'Aplicar filtro',
        no_month_payments:'Não há pagamentos para este filtro.',subscription_payments:'Pagamentos de assinatura',payment_reference_short:'Referência',recorded_by:'Registrado por',
        monthly_distributions:'Comissões e distribuições do mês',beneficiary:'Beneficiário / distribuição',percentage:'Percentual',payout_status:'Status do pagamento',
        paid_date:'Data do pagamento',payout_action:'Registro do pagamento',no_month_allocations:'Não há comissões ou distribuições para este filtro.',
        pending:'Pendente',paid:'Pago',not_applicable:'N/A',mark_paid:'Marcar como pago',
        paid_reference_placeholder:'Referência do pagamento (opcional)',
        allocations_note:'Cada parcela é marcada como paga separadamente e registra a data, o administrador e uma referência opcional. As parcelas são pagas integralmente; pagamentos parciais não são permitidos.',
        investment:'Fundo de investimento',seller_share:'Vendedor',fedsal:'FedSal',julgon:'JulGon'
    }
};

function applyFinanceTranslations(language) {
    const translations = financeTranslations[language] || financeTranslations.es;
    document.documentElement.lang = language;
    document.querySelectorAll('[data-finance-i18n]').forEach(element => {
        const key = element.dataset.financeI18n;
        if (translations[key]) element.textContent = translations[key];
    });
    document.querySelectorAll('[data-finance-i18n-placeholder]').forEach(element => {
        const key = element.dataset.financeI18nPlaceholder;
        if (translations[key]) element.placeholder = translations[key];
    });
}

applyFinanceTranslations(localStorage.getItem('smartshelfLang') || 'es');
window.addEventListener('languageChanged', () => {
    applyFinanceTranslations(localStorage.getItem('smartshelfLang') || 'es');
});
</script>
</body>
</html>
