<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();
require_once __DIR__ . '/sessions.php';
require_once __DIR__ . '/db_connection1.php';
require_once __DIR__ . '/subscription_payment_requests.php';

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
    exit('No tiene autorización para revisar pagos.');
}

if (empty($_SESSION['subscription_payment_review_csrf'])) {
    $_SESSION['subscription_payment_review_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['subscription_payment_review_csrf'];
$flash = $_SESSION['subscription_payment_review_flash'] ?? null;
unset($_SESSION['subscription_payment_review_flash']);
$errorMessage = '';
$transactionStarted = false;

function subscriptionReviewEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function subscriptionReviewRedirect(): void
{
    header('Location: SubscriptionPaymentReview.php', true, 303);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
            throw new InvalidArgumentException('La solicitud expiró. Recargue la página e inténtelo nuevamente.');
        }

        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'save_paypal_fee') {
            $feeInput = trim((string) ($_POST['fee_percent'] ?? ''));
            $fixedInput = trim((string) ($_POST['fixed_fee_usd'] ?? ''));
            if (!preg_match('/^\d{1,2}(?:\.\d{1,4})?$/D', $feeInput)
                || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $fixedInput)
            ) {
                throw new InvalidArgumentException('Ingrese una comisión porcentual y un cargo fijo válidos.');
            }
            $feePercent = (float) $feeInput;
            $fixedFee = (float) $fixedInput;
            if ($feePercent < 0 || $feePercent >= 100 || $fixedFee < 0) {
                throw new InvalidArgumentException('La comisión debe ser menor al 100% y el cargo fijo no puede ser negativo.');
            }
            $saveFee = $conn->prepare(
                'INSERT INTO gora_paypal_fee_settings
                    (settings_id, fee_percent, fixed_fee_usd, updated_by)
                 VALUES (1, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    fee_percent = VALUES(fee_percent),
                    fixed_fee_usd = VALUES(fixed_fee_usd),
                    updated_by = VALUES(updated_by)'
            );
            $saveFee->bind_param('dds', $feeInput, $fixedInput, $adminEmail);
            $saveFee->execute();
            $saveFee->close();

            $_SESSION['subscription_payment_review_flash'] = [
                'type' => 'success',
                'message' => 'La tarifa de PayPal quedó guardada.',
            ];
            subscriptionReviewRedirect();
        }

        $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
        if ($requestId === false || $requestId <= 0) {
            throw new InvalidArgumentException('El reporte de pago indicado no es válido.');
        }

        if ($action === 'approve_payment') {
            $conn->begin_transaction();
            $transactionStarted = true;
            $requestStmt = $conn->prepare(
                "SELECT username, package_name, payment_date, payment_reference
                 FROM gora_subscription_payment_requests
                 WHERE request_id = ? AND status = 'pending'
                 LIMIT 1
                 FOR UPDATE"
            );
            $requestStmt->bind_param('i', $requestId);
            $requestStmt->execute();
            $request = $requestStmt->get_result()->fetch_assoc();
            $requestStmt->close();
            if (!$request) {
                throw new InvalidArgumentException('El reporte ya fue revisado o no existe.');
            }

            goraApplyPaidSubscription(
                $conn,
                (string) $request['username'],
                (string) $request['package_name'],
                (string) $request['payment_date'],
                (string) ($request['payment_reference'] ?? '')
            );
            if (goraRefreshSubscriptionAccess($conn, (string) $request['username']) === null) {
                throw new RuntimeException('No fue posible verificar la actualización del acceso.');
            }

            $approveStmt = $conn->prepare(
                "UPDATE gora_subscription_payment_requests
                 SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), rejection_reason = NULL
                 WHERE request_id = ? AND status = 'pending'"
            );
            $approveStmt->bind_param('si', $adminEmail, $requestId);
            $approveStmt->execute();
            if ($approveStmt->affected_rows !== 1) {
                $approveStmt->close();
                throw new RuntimeException('No fue posible marcar el reporte como aprobado.');
            }
            $approveStmt->close();
            $conn->commit();
            $transactionStarted = false;

            $_SESSION['subscription_payment_review_flash'] = [
                'type' => 'success',
                'message' => 'Pago confirmado. Las fechas y el acceso quedaron actualizados.',
            ];
            subscriptionReviewRedirect();
        }

        if ($action === 'reject_payment') {
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
            if ($reason === '' || strlen($reason) > 500) {
                throw new InvalidArgumentException('Indique el motivo del rechazo (máximo 500 caracteres).');
            }
            $rejectStmt = $conn->prepare(
                "UPDATE gora_subscription_payment_requests
                 SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW()
                 WHERE request_id = ? AND status = 'pending'"
            );
            $rejectStmt->bind_param('ssi', $reason, $adminEmail, $requestId);
            $rejectStmt->execute();
            $rejected = $rejectStmt->affected_rows === 1;
            $rejectStmt->close();
            if (!$rejected) {
                throw new InvalidArgumentException('El reporte ya fue revisado o no existe.');
            }
            $_SESSION['subscription_payment_review_flash'] = [
                'type' => 'success',
                'message' => 'El reporte fue rechazado y el motivo quedó registrado.',
            ];
            subscriptionReviewRedirect();
        }

        throw new InvalidArgumentException('La acción solicitada no es válida.');
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        error_log('Subscription payment review failed: ' . $exception->getMessage());
        $errorMessage = !($exception instanceof mysqli_sql_exception)
            && ($exception instanceof InvalidArgumentException || $exception instanceof RuntimeException)
            ? $exception->getMessage()
            : 'No fue posible completar la operación. Verifique la base de datos e inténtelo nuevamente.';
    }
}

$feeSettings = goraGetPaypalFeeSettings($conn);
$requestsStmt = $conn->prepare(
    'SELECT request_id, username, package_name, payment_method, payment_date,
            amount_paid, currency, price_cop, exchange_rate, paypal_fee_percent,
            paypal_fixed_fee_usd, quoted_amount, payment_reference, status,
            rejection_reason, reviewed_by, reviewed_at, created_at
     FROM gora_subscription_payment_requests
     ORDER BY status = \'pending\' DESC, request_id DESC
     LIMIT 100'
);
$requestsStmt->execute();
$requests = $requestsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$requestsStmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Revisión de pagos de suscripción | GORA</title>
    <link rel="icon" href="GORA.ico" type="image/x-icon">
    <link rel="stylesheet" href="style_sheet_ops.css">
    <script src="tab-context.js?v=20261029-1" defer></script>
    <script src="session-timeout.js?v=20261029-3" data-session-generation="<?php echo subscriptionReviewEscape($_SESSION['gora_session_generation'] ?? ''); ?>" data-account-email="<?php echo subscriptionReviewEscape($adminEmail); ?>" defer></script>
    <style>
        body { margin: 0; padding: 24px; background: #f3f7f8; color: #10283d; font-family: Arial, sans-serif; }
        main { max-width: 1280px; margin: auto; }
        .review-card { margin: 18px 0; padding: 22px; border-radius: 12px; background: #fff; box-shadow: 0 8px 26px rgba(3, 38, 66, .08); }
        .review-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; align-items: end; }
        label { display: grid; gap: 6px; font-weight: 600; }
        input, textarea, button { padding: 10px; font: inherit; }
        input, textarea { border: 1px solid #b8c5cc; border-radius: 6px; }
        button, .review-link { border: 0; border-radius: 6px; padding: 10px 14px; background: #087c52; color: #fff; cursor: pointer; text-decoration: none; }
        button.reject { background: #a52f36; }
        .notice { padding: 12px; border-radius: 6px; background: #e4f5ea; }
        .notice.error { background: #fde9e8; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #d5dfe3; text-align: left; vertical-align: top; }
        th { background: #032642; color: #fff; }
        .actions { min-width: 220px; display: grid; gap: 8px; }
        @media (max-width: 700px) { body { padding: 12px; } }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Revisión de reportes de pago</h1>
        <a class="review-link" href="SubscriptionFinance.php">Volver a control financiero</a>
        <a class="review-link" href="AppMgmt.php?section=Administration">Administración</a>
    </header>

    <?php if ($flash): ?>
        <p class="notice" role="status"><?php echo subscriptionReviewEscape($flash['message'] ?? ''); ?></p>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <p class="notice error" role="alert"><?php echo subscriptionReviewEscape($errorMessage); ?></p>
    <?php endif; ?>

    <section class="review-card">
        <h2>Tarifa de PayPal</h2>
        <p>Ingrese la comisión real de su cuenta. Se usará para cotizar el total en USD; no se inventa una tarifa por defecto.</p>
        <form method="post" class="review-grid">
            <input type="hidden" name="csrf_token" value="<?php echo subscriptionReviewEscape($csrfToken); ?>">
            <input type="hidden" name="action" value="save_paypal_fee">
            <label>Comisión porcentual (%)
                <input type="number" name="fee_percent" min="0" max="99.9999" step="0.0001"
                    value="<?php echo subscriptionReviewEscape($feeSettings['fee_percent'] ?? ''); ?>" required>
            </label>
            <label>Cargo fijo por transacción (USD)
                <input type="number" name="fixed_fee_usd" min="0" step="0.01"
                    value="<?php echo subscriptionReviewEscape($feeSettings['fixed_fee_usd'] ?? ''); ?>" required>
            </label>
            <button type="submit">Guardar tarifa</button>
        </form>
        <?php if ($feeSettings): ?>
            <p>Configurada: <?php echo subscriptionReviewEscape($feeSettings['fee_percent']); ?>% + USD <?php echo subscriptionReviewEscape($feeSettings['fixed_fee_usd']); ?></p>
        <?php else: ?>
            <p class="notice error">Hasta guardar la comisión, no se podrán generar cotizaciones PayPal.</p>
        <?php endif; ?>
    </section>

    <section class="review-card">
        <h2>Reportes recientes</h2>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Fecha de reporte</th><th>Usuario</th><th>Plan</th><th>Pago</th>
                    <th>Referencia</th><th>Comprobante</th><th>Estado / revisión</th><th>Acción</th>
                </tr></thead>
                <tbody>
                <?php if (!$requests): ?>
                    <tr><td colspan="8">No hay reportes de pago.</td></tr>
                <?php else: foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo subscriptionReviewEscape($request['created_at']); ?><br>Pago: <?php echo subscriptionReviewEscape($request['payment_date']); ?></td>
                        <td><?php echo subscriptionReviewEscape($request['username']); ?></td>
                        <td><?php echo subscriptionReviewEscape($request['package_name']); ?></td>
                        <td>
                            <?php echo subscriptionReviewEscape($request['payment_method']); ?><br>
                            Reportado: <?php echo subscriptionReviewEscape($request['currency'] . ' ' . $request['amount_paid']); ?><br>
                            Precio COP: <?php echo subscriptionReviewEscape($request['price_cop']); ?><br>
                            <?php if ($request['payment_method'] === 'PayPal'): ?>
                                TRM: <?php echo subscriptionReviewEscape($request['exchange_rate']); ?><br>
                                Cotizado: USD <?php echo subscriptionReviewEscape($request['quoted_amount']); ?><br>
                                Comisión: <?php echo subscriptionReviewEscape($request['paypal_fee_percent']); ?>% + USD <?php echo subscriptionReviewEscape($request['paypal_fixed_fee_usd']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo subscriptionReviewEscape($request['payment_reference'] ?? '—'); ?></td>
                        <td><a href="SubscriptionPaymentProof.php?id=<?php echo (int) $request['request_id']; ?>" target="_blank" rel="noopener">Ver comprobante</a></td>
                        <td>
                            <?php echo subscriptionReviewEscape($request['status']); ?>
                            <?php if ($request['reviewed_by']): ?>
                                <br><?php echo subscriptionReviewEscape($request['reviewed_by']); ?> — <?php echo subscriptionReviewEscape($request['reviewed_at']); ?>
                            <?php endif; ?>
                            <?php if ($request['rejection_reason']): ?>
                                <br>Motivo: <?php echo subscriptionReviewEscape($request['rejection_reason']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($request['status'] === 'pending'): ?>
                                <form method="post" class="actions">
                                    <input type="hidden" name="csrf_token" value="<?php echo subscriptionReviewEscape($csrfToken); ?>">
                                    <input type="hidden" name="request_id" value="<?php echo (int) $request['request_id']; ?>">
                                    <button type="submit" name="action" value="approve_payment">Confirmar pago y activar</button>
                                    <textarea name="rejection_reason" maxlength="500" placeholder="Motivo obligatorio para rechazar"></textarea>
                                    <button class="reject" type="submit" name="action" value="reject_payment">Rechazar reporte</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
