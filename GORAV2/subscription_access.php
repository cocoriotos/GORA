<?php

function goraSubscriptionTermDays(?string $package): int
{
    switch (strtolower(trim((string) $package))) {
        case 'anual':
            return 365;
        case 'semestral':
            return 185;
        case 'trimestral':
            return 90;
        default:
            return 0;
    }
}

function goraSubscriptionPriceCop(?string $package): int
{
    switch (strtolower(trim((string) $package))) {
        case 'trimestral':
            return 20000;
        case 'semestral':
            return 40000;
        case 'anual':
            return 60000;
        default:
            return 0;
    }
}

function goraSubscriptionToday(): DateTimeImmutable
{
    return new DateTimeImmutable('today', new DateTimeZone('America/Bogota'));
}

function goraSubscriptionDate(?string $value): ?DateTimeImmutable
{
    if ($value === null || $value === '' || $value === '0000-00-00') {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('America/Bogota'));
    return $date && $date->format('Y-m-d') === $value ? $date : null;
}

function goraRefreshSubscriptionAccess(mysqli $conn, string $username): ?array
{
    $stmt = $conn->prepare(
        "SELECT a.active, a.suscriptionactive, a.suscriptionpayed, a.suscriptionkind, a.adm_role, a.name,
                a.daystoenjoy, a.suscriptiondaysleft, a.trialdaysleft,
                a.registrationdate, a.lastsuscriptionpaymentdate, a.suscription_package,
                a.subscription_expires_on, a.subscription_counting_starts_on,
                p.suscription_package, p.lastpaymentdate
         FROM videotips_app_access_list a
         LEFT JOIN videotips_suscription_payments p ON p.username = a.username
         WHERE a.username = ?
         LIMIT 1"
    );
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result(
        $active,
        $subscriptionActive,
        $subscriptionPaid,
        $subscriptionKind,
        $adminRole,
        $name,
        $accountDaysRemaining,
        $accountSubscriptionDaysLeft,
        $accountTrialDaysLeft,
        $registrationDate,
        $lastPaymentDate,
        $accountPackage,
        $expiryDate,
        $countingStartDate,
        $paymentPackage,
        $paymentTableDate
    );

    if (!$stmt->fetch()) {
        $stmt->close();
        return null;
    }
    $stmt->close();

    $package = $paymentPackage !== null && $paymentPackage !== ''
        ? $paymentPackage
        : $accountPackage;
    $termDays = goraSubscriptionTermDays($package);
    $kind = (string) $subscriptionKind;
    $today = goraSubscriptionToday();
    $remainingDays = 0;
    $subscriptionDaysLeft = (int) $accountSubscriptionDaysLeft;
    $trialElapsedDays = null;
    $effectiveLastPaymentDate = goraSubscriptionDate($lastPaymentDate)
        ? $lastPaymentDate
        : (goraSubscriptionDate($paymentTableDate) ? $paymentTableDate : null);

    $effectiveExpiry = goraSubscriptionDate($expiryDate);
    $counterStarts = goraSubscriptionDate($countingStartDate);
    $hasPaidSubscriptionRecord = in_array($kind, ['De Pago', 'Suspendida'], true)
        || ($kind === 'Vencida'
            && ((int) $subscriptionPaid === 1
                || (int) $subscriptionActive === 1
                || $effectiveExpiry !== null));
    if ($hasPaidSubscriptionRecord && $effectiveExpiry === null
        && $effectiveLastPaymentDate !== null
        && $termDays > 0
    ) {
        $effectiveExpiry = goraSubscriptionDate($effectiveLastPaymentDate)->modify('+' . $termDays . ' days');
        $counterStarts = goraSubscriptionDate($effectiveLastPaymentDate)->modify('+1 day');
    }
    $isPaidSubscription = $hasPaidSubscriptionRecord;

    if ($isPaidSubscription && $termDays > 0) {
        if ($effectiveExpiry !== null && $effectiveExpiry >= $today) {
            $remainingDays = (int) $today->diff($effectiveExpiry)->days + 1;
            if ($counterStarts !== null && $counterStarts > $today) {
                $remainingDays = max(
                    0,
                    $remainingDays - (int) $today->diff($counterStarts)->days
                );
            }
        }

        if ($effectiveExpiry !== null && $effectiveExpiry < $today) {
            $kind = 'Vencida';
        } elseif ($kind === 'Vencida'
            && (int) $active === 1
            && (int) $subscriptionActive === 1
            && (int) $subscriptionPaid === 1
        ) {
            $kind = 'De Pago';
        }
        $subscriptionDaysLeft = $remainingDays;
    } elseif ($kind === 'Trial'
        || ($kind === 'Vencida'
            && (int) $subscriptionPaid === 0
            && (int) $subscriptionActive === 0
            && $effectiveExpiry === null
            && goraSubscriptionDate($registrationDate) !== null)
    ) {
        $registration = goraSubscriptionDate($registrationDate);
        if ($registration !== null && $registration <= $today) {
            $trialElapsedDays = (int) $registration->diff($today)->days;
            $remainingDays = max(30 - $trialElapsedDays, 0);
            if ($trialElapsedDays >= 30) {
                $kind = 'Vencida';
            } else {
                $kind = 'Trial';
            }
        }
        $subscriptionDaysLeft = $remainingDays;
    } else {
        $remainingDays = (int) $accountDaysRemaining;
        $trialElapsedDays = $accountTrialDaysLeft === null ? null : (int) $accountTrialDaysLeft;
    }

    if ($isPaidSubscription || in_array($kind, ['Vencida', 'Trial'], true)) {
        $update = $conn->prepare(
            "UPDATE videotips_app_access_list
             SET daystoenjoy = ?, suscriptiondaysleft = ?, trialdaysleft = ?,
                 suscriptionkind = ?, suscription_package = COALESCE(?, suscription_package),
                 lastsuscriptionpaymentdate = COALESCE(?, lastsuscriptionpaymentdate),
                 subscription_expires_on = COALESCE(?, subscription_expires_on),
                 subscription_counting_starts_on = COALESCE(?, subscription_counting_starts_on)
             WHERE username = ?"
        );
        $remainingForUpdate = $remainingDays;
        $trialDaysForUpdate = $trialElapsedDays ?? (int) $accountTrialDaysLeft;
        $expiryDateForUpdate = $effectiveExpiry ? $effectiveExpiry->format('Y-m-d') : null;
        $counterStartForUpdate = $counterStarts ? $counterStarts->format('Y-m-d') : null;
        $update->bind_param(
            'iiissssss',
            $remainingDays,
            $remainingForUpdate,
            $trialDaysForUpdate,
            $kind,
            $package,
            $effectiveLastPaymentDate,
            $expiryDateForUpdate,
            $counterStartForUpdate,
            $username
        );
        $update->execute();
        $update->close();
    }

    if ($termDays > 0 && $isPaidSubscription) {
        $paymentUpdate = $conn->prepare(
            "UPDATE videotips_suscription_payments
             SET suscriptiondaysleft = ?
             WHERE username = ?"
        );
        $paymentUpdate->bind_param('is', $remainingDays, $username);
        $paymentUpdate->execute();
        $paymentUpdate->close();
    }

    return [
        'active' => (int) $active,
        'suscriptionactive' => (int) $subscriptionActive,
        'suscriptionpayed' => (int) $subscriptionPaid,
        'suscriptionkind' => $kind,
        'adm_role' => (int) $adminRole,
        'name' => $name,
        'suscription_package' => $package,
        'daystoenjoy' => $remainingDays,
        'trialdaysleft' => $trialElapsedDays,
        'registrationdate' => $registrationDate,
        'lastsuscriptionpaymentdate' => $effectiveLastPaymentDate,
        'subscription_expires_on' => $effectiveExpiry ? $effectiveExpiry->format('Y-m-d') : null,
        'suscriptiondaysleft' => $subscriptionDaysLeft,
    ];
}

function goraApplyPaidSubscription(
    mysqli $conn,
    string $username,
    string $package,
    string $paymentDate,
    string $paymentReference
): array {
    $termDays = goraSubscriptionTermDays($package);
    $paidOn = goraSubscriptionDate($paymentDate);
    if ($termDays === 0 || $paidOn === null || $paidOn > goraSubscriptionToday()) {
        throw new InvalidArgumentException('El paquete o la fecha del pago no son válidos.');
    }

    $stmt = $conn->prepare(
        "SELECT active, suscriptionactive, suscriptionpayed, suscriptionkind,
                subscription_expires_on, subscription_counting_starts_on,
                lastsuscriptionpaymentdate, registrationdate
         FROM videotips_app_access_list
         WHERE username = ?
         LIMIT 1
         FOR UPDATE"
    );
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result(
        $active,
        $subscriptionActive,
        $subscriptionPaid,
        $kind,
        $expiryValue,
        $countStartValue,
        $previousPaymentDateValue,
        $registrationDateValue
    );
    if (!$stmt->fetch()) {
        $stmt->close();
        throw new RuntimeException('No se encontró la cuenta a la que aplicar el pago.');
    }
    $stmt->close();

    $previousExpiry = goraSubscriptionDate($expiryValue);
    $previousCounterStart = goraSubscriptionDate($countStartValue);
    $previousPaymentDate = goraSubscriptionDate($previousPaymentDateValue);
    $trialRegistrationDate = goraSubscriptionDate($registrationDateValue);
    $isRenewal = $previousExpiry !== null
        && $previousExpiry >= $paidOn
        && (int) $active === 1
        && (int) $subscriptionActive === 1
        && (int) $subscriptionPaid === 1
        && in_array((string) $kind, ['De Pago', 'Vencida', 'Suspendida'], true);

    $trialElapsedDaysAtPayment = $trialRegistrationDate !== null && $trialRegistrationDate <= $paidOn
        ? (int) $trialRegistrationDate->diff($paidOn)->days
        : null;
    $trialDaysRemainingAtPayment = $trialElapsedDaysAtPayment === null
        ? 0
        : max(30 - $trialElapsedDaysAtPayment, 0);
    $preserveTrial = !$isRenewal
        && $previousExpiry === null
        && (int) $active === 1
        && (int) $subscriptionActive === 0
        && (int) $subscriptionPaid === 0
        && in_array((string) $kind, ['Trial', 'Vencida'], true)
        && $trialDaysRemainingAtPayment > 0;

    $expiryBase = $isRenewal
        ? $previousExpiry
        : ($preserveTrial
            ? $trialRegistrationDate->modify('+29 days')
            : $paidOn);
    $newExpiry = $expiryBase->modify('+' . $termDays . ' days');
    if ($isRenewal) {
        $counterStarts = $previousCounterStart
            ?? ($previousPaymentDate !== null ? $previousPaymentDate->modify('+1 day') : $paidOn->modify('+1 day'));
    } elseif ($preserveTrial) {
        $counterStarts = $trialRegistrationDate->modify('+30 days');
    } else {
        $counterStarts = $paidOn->modify('+1 day');
    }
    $newExpiryValue = $newExpiry->format('Y-m-d');
    $counterStartsValue = $counterStarts->format('Y-m-d');

    $accessUpdate = $conn->prepare(
        "UPDATE videotips_app_access_list
         SET active = 1, suscriptionactive = 1, suscriptionpayed = 1,
             lastsuscriptionpaymentdate = ?, suscriptionkind = 'De Pago',
             suscription_package = ?, subscription_expires_on = ?,
             subscription_counting_starts_on = ?
         WHERE username = ?"
    );
    $accessUpdate->bind_param(
        'sssss',
        $paymentDate,
        $package,
        $newExpiryValue,
        $counterStartsValue,
        $username
    );
    $accessUpdate->execute();
    $accessUpdate->close();

    $paymentRecordLookup = $conn->prepare(
        'SELECT username
         FROM videotips_suscription_payments
         WHERE username = ?
         LIMIT 1
         FOR UPDATE'
    );
    $paymentRecordLookup->bind_param('s', $username);
    $paymentRecordLookup->execute();
    $paymentRecord = $paymentRecordLookup->get_result()->fetch_assoc();
    $hasPaymentRecord = is_array($paymentRecord);
    $paymentRecordLookup->close();

    if (!$hasPaymentRecord) {
        $paymentRecordInsert = $conn->prepare(
            'INSERT INTO videotips_suscription_payments (username, active, freeregistrationdate)
             SELECT username, active, registrationdate
             FROM videotips_app_access_list
             WHERE username = ?'
        );
        $paymentRecordInsert->bind_param('s', $username);
        $paymentRecordInsert->execute();
        if ($paymentRecordInsert->affected_rows !== 1) {
            $paymentRecordInsert->close();
            throw new RuntimeException('No fue posible crear el registro de pagos del usuario.');
        }
        $paymentRecordInsert->close();
    }

    $paymentCountersStmt = $conn->prepare(
        'SELECT COUNT(*) AS record_count,
                COALESCE(MAX(currentpaid), 0) AS currentpaid,
                COALESCE(MAX(paymentcounter), 0) AS paymentcounter
         FROM videotips_suscription_payments
         WHERE username = ?'
    );
    $paymentCountersStmt->bind_param('s', $username);
    $paymentCountersStmt->execute();
    $paymentCounters = $paymentCountersStmt->get_result()->fetch_assoc();
    $paymentCountersStmt->close();
    if (!is_array($paymentCounters) || (int) $paymentCounters['record_count'] < 1) {
        throw new RuntimeException('No se encontró el registro de pagos del usuario después de prepararlo.');
    }

    if ((int) $paymentCounters['record_count'] > 1) {
        error_log(
            'Duplicate subscription payment rows found for account '
            . hash('sha256', $username)
            . '; normalizing their counters.'
        );
    }

    $currentPaidCount = (int) $paymentCounters['currentpaid'] + 1;
    $paymentCounter = (int) $paymentCounters['paymentcounter'] + 1;
    $paymentUpdate = $conn->prepare(
        "UPDATE videotips_suscription_payments
         SET currentpaid = ?,
             paymentcounter = ?,
             lastpaymentdate = ?,
             suscription_package = ?,
             proofofpurchasenumber = ?
         WHERE username = ?"
    );
    $paymentUpdate->bind_param(
        'iissss',
        $currentPaidCount,
        $paymentCounter,
        $paymentDate,
        $package,
        $paymentReference,
        $username
    );
    $paymentUpdate->execute();
    if ($paymentUpdate->affected_rows < 1) {
        error_log(
            'Subscription payment update affected no rows for account '
            . hash('sha256', $username)
            . ': ' . $paymentUpdate->error
        );
        $paymentUpdate->close();
        throw new RuntimeException('No fue posible actualizar el registro de pagos del usuario.');
    }
    $paymentUpdate->close();

    return [
        'subscription_expires_on' => $newExpiryValue,
        'subscription_counting_starts_on' => $counterStartsValue,
        'is_renewal' => $isRenewal,
    ];
}

function goraCanAccessVideoModule(?array $user): bool
{
    if ($user === null || (int) ($user['active'] ?? 0) !== 1) {
        return false;
    }

    $kind = $user['suscriptionkind'] ?? '';
    if ($kind === 'De Pago') {
        $expiryDate = goraSubscriptionDate($user['subscription_expires_on'] ?? null);
        return (int) ($user['suscriptionactive'] ?? 0) === 1
            && (int) ($user['suscriptionpayed'] ?? 0) === 1
            && goraSubscriptionTermDays($user['suscription_package'] ?? null) > 0
            && $expiryDate !== null
            && $expiryDate >= goraSubscriptionToday();
    }

    if ($kind === 'Trial') {
        $daysRemaining = (int) ($user['daystoenjoy'] ?? 0);
        return $daysRemaining > 0 && $daysRemaining <= 30;
    }

    if ($kind === 'Owner') {
        return (int) ($user['adm_role'] ?? 0) === 0;
    }

    return in_array($kind, ['Test', 'Partner'], true);
}

function goraCanAccessAppManagement(?array $user): bool
{
    return $user !== null
        && (int) ($user['active'] ?? 0) === 1
        && (int) ($user['adm_role'] ?? 0) === 1
        && ($user['suscriptionkind'] ?? '') === 'Owner';
}
