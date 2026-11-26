<?php

require_once __DIR__ . '/subscription_access.php';

function goraFormatSubscriptionMoney($amount, string $currency): string
{
    $amountString = (string) $amount;
    $decimals = $currency === 'COP' && preg_match('/\.0{1,2}$/D', $amountString) ? 0 : 2;
    return number_format((float) $amountString, $decimals, ',', '.');
}

function goraNormalizeSubscriptionAmount(string $value): ?string
{
    $value = trim($value);
    if (strpos($value, ',') !== false) {
        if (!preg_match('/^(?:\d{1,3}(?:\.\d{3})+|\d+)(?:,\d{1,2})?$/D', $value)) {
            return null;
        }
        return str_replace(',', '.', str_replace('.', '', $value));
    }

    if (preg_match('/^\d{1,3}(?:\.\d{3})+$/D', $value)) {
        return str_replace('.', '', $value);
    }

    return preg_match('/^\d+(?:\.\d{1,2})?$/D', $value) ? $value : null;
}

function goraSendSubscriptionPaymentNotification(int $requestId, array $request): bool
{
    if (!function_exists('mail')) {
        return false;
    }

    $subject = 'Nuevo reporte de pago de suscripcion GORA #' . $requestId;
    $body = implode("\n", [
        'Se recibió un nuevo reporte de pago de suscripción en GORA.',
        '',
        'Número de reporte: ' . $requestId,
        'Usuario: ' . (string) ($request['username'] ?? ''),
        'Plan: ' . (string) ($request['package_name'] ?? ''),
        'Medio de pago: ' . (string) ($request['payment_method'] ?? ''),
        'Fecha del pago: ' . (string) ($request['payment_date'] ?? ''),
        'Valor reportado: ' . (string) ($request['currency'] ?? '')
            . ' ' . goraFormatSubscriptionMoney($request['amount_paid'] ?? '0', (string) ($request['currency'] ?? '')),
        'Referencia: ' . (string) ($request['payment_reference'] ?? 'No indicada'),
        '',
        'El comprobante está disponible en el panel de revisión de pagos. Confirme el pago antes de activar el acceso.',
    ]);
    $headers = "From: GORA <adm@solicionespro.com>\r\n"
        . "Cc: cocoriotos1@gmail.com, goracoloficial@gmail.com\r\n"
        . "Reply-To: adm@solicionespro.com\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail('adm@solicionespro.com', $subject, $body, $headers);
}

function goraDecimalToScaledInteger(string $value, int $decimalPlaces): int
{
    $pattern = '/^\d+(?:\.\d{1,' . $decimalPlaces . '})?$/D';
    if (!preg_match($pattern, $value)) {
        throw new RuntimeException('Se encontró un importe decimal no válido.');
    }

    $parts = explode('.', $value, 2);
    $whole = (int) $parts[0];
    $fraction = isset($parts[1])
        ? (int) str_pad($parts[1], $decimalPlaces, '0')
        : 0;
    return $whole * (10 ** $decimalPlaces) + $fraction;
}

function goraFetchTrmForDate(string $targetDate): array
{
    $target = goraSubscriptionDate($targetDate);
    if ($target === null || $target > goraSubscriptionToday()) {
        throw new InvalidArgumentException('La fecha de pago no es válida.');
    }

    $where = "vigenciadesde <= '{$targetDate}T00:00:00' AND vigenciahasta >= '{$targetDate}T00:00:00'";
    $url = 'https://www.datos.gov.co/resource/32sa-8pi3.json?'
        . http_build_query([
            '$where' => $where,
            '$order' => 'vigenciadesde DESC',
            '$limit' => 1,
        ], '', '&', PHP_QUERY_RFC3986);

    if (!function_exists('curl_init')) {
        throw new RuntimeException('No está disponible la conexión segura para consultar el TRM oficial.');
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'GORA Subscription Pricing',
    ]);
    $response = curl_exec($curl);
    $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);

    if (!is_string($response) || $httpStatus !== 200) {
        error_log('Official TRM request failed: HTTP ' . $httpStatus . ' ' . $curlError);
        throw new RuntimeException('No fue posible consultar el TRM oficial. Intente nuevamente más tarde.');
    }

    $rows = json_decode($response, true);
    if (!is_array($rows) || !isset($rows[0]['valor'], $rows[0]['vigenciadesde'], $rows[0]['vigenciahasta'])) {
        throw new RuntimeException('La respuesta del TRM oficial no tiene el formato esperado.');
    }

    $rateText = (string) $rows[0]['valor'];
    $rateScaled = goraDecimalToScaledInteger($rateText, 4);
    $validFrom = substr((string) $rows[0]['vigenciadesde'], 0, 10);
    $validThrough = substr((string) $rows[0]['vigenciahasta'], 0, 10);
    if ($rateScaled <= 0
        || $validFrom > $targetDate
        || $validThrough < $targetDate
    ) {
        throw new RuntimeException('No hay una tasa TRM oficial vigente para la fecha indicada.');
    }

    return [
        'rate_scaled' => $rateScaled,
        'rate_text' => number_format($rateScaled / 10000, 4, '.', ''),
        'valid_from' => $validFrom,
        'valid_through' => $validThrough,
    ];
}

function goraGetPaypalFeeSettings(mysqli $conn): ?array
{
    $result = $conn->query(
        'SELECT fee_percent, fixed_fee_usd
         FROM gora_paypal_fee_settings
         WHERE settings_id = 1
         LIMIT 1'
    );
    $settings = $result->fetch_assoc();
    $result->free();
    return $settings ?: null;
}

function goraBuildSubscriptionPaymentQuote(
    mysqli $conn,
    string $package,
    string $paymentMethod,
    string $paymentDate
): array {
    $priceCop = goraSubscriptionPriceCop($package);
    $termDays = goraSubscriptionTermDays($package);
    if ($priceCop <= 0 || $termDays <= 0) {
        throw new InvalidArgumentException('Seleccione un plan de suscripción válido.');
    }
    if (!in_array($paymentMethod, ['Nequi', 'PayPal'], true)) {
        throw new InvalidArgumentException('Seleccione Nequi o PayPal como medio de pago.');
    }
    $paymentDay = goraSubscriptionDate($paymentDate);
    if ($paymentDay === null || $paymentDay > goraSubscriptionToday()) {
        throw new InvalidArgumentException('Seleccione una fecha de pago válida, no futura.');
    }

    $quote = [
        'id' => bin2hex(random_bytes(24)),
        'package' => $package,
        'payment_method' => $paymentMethod,
        'payment_date' => $paymentDate,
        'created_at' => time(),
        'price_cop' => $priceCop,
        'currency' => 'COP',
        'quoted_amount' => number_format($priceCop, 2, '.', ''),
        'exchange_rate' => null,
        'paypal_fee_percent' => null,
        'paypal_fixed_fee_usd' => null,
    ];

    if ($paymentMethod === 'PayPal') {
        $settings = goraGetPaypalFeeSettings($conn);
        if ($settings === null) {
            throw new RuntimeException('PayPal no está configurado todavía. Contacte al administrador.');
        }

        $feePercent = number_format((float) $settings['fee_percent'], 2, '.', '');
        $fixedFeeUsd = (string) $settings['fixed_fee_usd'];
        $feePercentScaled = goraDecimalToScaledInteger($feePercent, 4);
        $fixedFeeCents = goraDecimalToScaledInteger($fixedFeeUsd, 2);
        if ($feePercentScaled >= 1000000) {
            throw new RuntimeException('La configuración de comisión de PayPal no es válida.');
        }

        $trm = goraFetchTrmForDate($paymentDate);
        $baseNumerator = $priceCop * 100 * 10000;
        $baseUsdCents = intdiv($baseNumerator + $trm['rate_scaled'] - 1, $trm['rate_scaled']);
        $grossNumerator = ($baseUsdCents + $fixedFeeCents) * 1000000;
        $feeDivisor = 1000000 - $feePercentScaled;
        $grossUsdCents = intdiv($grossNumerator + $feeDivisor - 1, $feeDivisor);

        $quote['currency'] = 'USD';
        $quote['quoted_amount'] = number_format($grossUsdCents / 100, 2, '.', '');
        $quote['base_amount_usd'] = number_format($baseUsdCents / 100, 2, '.', '');
        $quote['estimated_fee_usd'] = number_format(($grossUsdCents - $baseUsdCents) / 100, 2, '.', '');
        $quote['exchange_rate'] = $trm['rate_text'];
        $quote['paypal_fee_percent'] = number_format($feePercentScaled / 10000, 2, '.', '');
        $quote['paypal_fixed_fee_usd'] = number_format($fixedFeeCents / 100, 2, '.', '');
    }

    return $quote;
}

function goraPrivatePaymentProofDirectory(): string
{
    $configuredPath = getenv('GORA_PRIVATE_PAYMENT_PROOF_DIR');
    if (is_string($configuredPath) && trim($configuredPath) !== '') {
        return rtrim($configuredPath, '\\/');
    }

    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'gora_private_payment_proofs';
}

function goraStorePaymentProof(array $uploadedFile): string
{
    if (!isset($uploadedFile['error'], $uploadedFile['tmp_name'], $uploadedFile['size'])
        || (int) $uploadedFile['error'] !== UPLOAD_ERR_OK
        || !is_uploaded_file((string) $uploadedFile['tmp_name'])
    ) {
        throw new InvalidArgumentException('Adjunte el comprobante de pago para continuar.');
    }

    if ((int) $uploadedFile['size'] <= 0 || (int) $uploadedFile['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('El comprobante debe tener un tamaño máximo de 5 MB.');
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file((string) $uploadedFile['tmp_name']);
    $allowedTypes = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!is_string($mimeType) || !isset($allowedTypes[$mimeType])) {
        throw new InvalidArgumentException('Adjunte un PDF, JPG, PNG o WebP válido.');
    }

    $directory = goraPrivatePaymentProofDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('No fue posible preparar el almacenamiento privado de comprobantes.');
    }

    $fileName = bin2hex(random_bytes(24)) . '.' . $allowedTypes[$mimeType];
    $destination = $directory . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file((string) $uploadedFile['tmp_name'], $destination)) {
        throw new RuntimeException('No fue posible guardar el comprobante de forma segura.');
    }
    if (!chmod($destination, 0600)) {
        if (!unlink($destination)) {
            error_log('Could not remove a payment proof with insecure permissions.');
        }
        throw new RuntimeException('No fue posible restringir el acceso al comprobante.');
    }

    return $fileName;
}
