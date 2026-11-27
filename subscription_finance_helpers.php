<?php

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

function financeFindDuplicateSubscriptionSale(
    mysqli $conn,
    string $userEmail,
    string $packageName,
    string $paymentDate,
    string $amount,
    string $currency,
    string $paymentMethod
): ?array {
    $stmt = $conn->prepare(
        'SELECT p.payment_id, p.payment_request_id
         FROM gora_subscription_sales p
         WHERE p.user_email = ? AND p.package_name = ? AND p.payment_date = ?
           AND p.amount = ? AND p.currency = ? AND p.payment_method = ?
           AND (p.payment_request_id IS NULL OR EXISTS (
               SELECT 1
               FROM gora_subscription_payment_requests r
               WHERE r.request_id = p.payment_request_id AND r.status = \'approved\'
           ))
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->bind_param('ssssss', $userEmail, $packageName, $paymentDate, $amount, $currency, $paymentMethod);
    $stmt->execute();
    $duplicate = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return is_array($duplicate) ? $duplicate : null;
}

function financeFindMatchingPendingPaymentRequest(
    mysqli $conn,
    string $userEmail,
    string $packageName,
    string $paymentDate,
    string $amount,
    string $currency,
    string $paymentMethod
): ?array {
    $stmt = $conn->prepare(
        "SELECT request_id, payment_reference
         FROM gora_subscription_payment_requests
         WHERE username = ? AND package_name = ? AND payment_date = ?
           AND amount_paid = ? AND currency = ? AND payment_method = ?
           AND status = 'pending'
         ORDER BY request_id
         LIMIT 2
         FOR UPDATE"
    );
    $stmt->bind_param('ssssss', $userEmail, $packageName, $paymentDate, $amount, $currency, $paymentMethod);
    $stmt->execute();
    $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($matches) > 1) {
        throw new InvalidArgumentException('Hay varios reportes pendientes con estos datos. Revise los reportes antes del registro manual.');
    }

    return $matches[0] ?? null;
}

function financeFindMatchingRejectedPaymentRequest(
    mysqli $conn,
    string $userEmail,
    string $packageName,
    string $paymentDate,
    string $amount,
    string $currency,
    string $paymentMethod,
    ?string $paymentReference
): ?array {
    $stmt = $conn->prepare(
        "SELECT request_id, payment_reference
         FROM gora_subscription_payment_requests
         WHERE username = ? AND package_name = ? AND payment_date = ?
           AND amount_paid = ? AND currency = ? AND payment_method = ?
           AND status = 'rejected'
         ORDER BY request_id DESC
         FOR UPDATE"
    );
    $stmt->bind_param('ssssss', $userEmail, $packageName, $paymentDate, $amount, $currency, $paymentMethod);
    $stmt->execute();
    $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($matches as $match) {
        $rejectedReference = $match['payment_reference'];
        if ($paymentReference === null
            || $rejectedReference === null
            || hash_equals((string) $rejectedReference, $paymentReference)
        ) {
            return $match;
        }
    }

    return null;
}

function financeRecordSaleWithAllocations(
    mysqli $conn,
    string $userEmail,
    string $packageName,
    string $paymentDate,
    string $amountInput,
    string $currency,
    string $paymentMethod,
    ?string $paymentReference,
    int $sellerId,
    string $recordedBy,
    ?int $paymentRequestId,
    string $proofFile
): int {
    [$amountCents, $amount] = financeAmountFromInput($amountInput);
    $paymentMonth = substr($paymentDate, 0, 7);
    $saleStmt = $conn->prepare(
        'INSERT INTO gora_subscription_sales
            (user_email, package_name, payment_date, payment_month, amount, currency,
             payment_method, payment_reference, seller_id, recorded_by, payment_request_id, proof_file)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $saleStmt->bind_param(
        'ssssssssisis',
        $userEmail,
        $packageName,
        $paymentDate,
        $paymentMonth,
        $amount,
        $currency,
        $paymentMethod,
        $paymentReference,
        $sellerId,
        $recordedBy,
        $paymentRequestId,
        $proofFile
    );
    $saleStmt->execute();
    $paymentId = (int) $conn->insert_id;
    $saleStmt->close();

    $allocationStmt = $conn->prepare(
        'INSERT INTO gora_subscription_allocations
            (payment_id, allocation_type, percentage, amount, currency, payment_month, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach (financeSplitAmount($amountCents) as [$allocationType, $percentage, $allocationCents]) {
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

    return $paymentId;
}
