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

function goraRefreshSubscriptionAccess(mysqli $conn, string $username): ?array
{
    $stmt = $conn->prepare(
        "SELECT a.active, a.suscriptionactive, a.suscriptionpayed, a.suscriptionkind, a.adm_role,
                a.registrationdate, a.lastsuscriptionpaymentdate, a.suscription_package,
                p.suscription_package, p.lastpaymentdate,
                DATEDIFF(CURDATE(), NULLIF(a.registrationdate, '0000-00-00')),
                DATEDIFF(
                    CURDATE(),
                    COALESCE(
                        NULLIF(a.lastsuscriptionpaymentdate, '0000-00-00'),
                        NULLIF(p.lastpaymentdate, '0000-00-00')
                    )
                )
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
        $registrationDate,
        $lastPaymentDate,
        $accountPackage,
        $paymentPackage,
        $paymentTableDate,
        $trialElapsedDays,
        $paidElapsedDays
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
    $remainingDays = 0;

    if (in_array($kind, ['De Pago', 'Vencida', 'Suspendida'], true) && $termDays > 0) {
        if ($paidElapsedDays !== null && (int) $paidElapsedDays >= 0) {
            $remainingDays = max($termDays - (int) $paidElapsedDays, 0);
        }

        if ($kind === 'Vencida'
            && (int) $active === 1
            && (int) $subscriptionActive === 1
            && (int) $subscriptionPaid === 1
            && $paidElapsedDays !== null
            && (int) $paidElapsedDays >= 0
            && (int) $paidElapsedDays < $termDays
        ) {
            $kind = 'De Pago';
        } elseif ($kind === 'De Pago'
            && $paidElapsedDays !== null
            && (int) $paidElapsedDays >= $termDays
        ) {
            $kind = 'Vencida';
        }
    } elseif ($kind === 'Trial') {
        if ($trialElapsedDays !== null && (int) $trialElapsedDays >= 0) {
            $remainingDays = max(30 - (int) $trialElapsedDays, 0);
        }

        if ($trialElapsedDays !== null && (int) $trialElapsedDays >= 30) {
            $kind = 'Vencida';
        }
    }

    $effectiveLastPaymentDate = $lastPaymentDate !== null
        && $lastPaymentDate !== ''
        && $lastPaymentDate !== '0000-00-00'
        ? $lastPaymentDate
        : ($paymentTableDate !== null && $paymentTableDate !== '' && $paymentTableDate !== '0000-00-00'
            ? $paymentTableDate
            : null);
    $update = $conn->prepare(
        "UPDATE videotips_app_access_list
         SET daystoenjoy = ?, suscriptionkind = ?, suscription_package = ?,
             lastsuscriptionpaymentdate = COALESCE(?, lastsuscriptionpaymentdate)
         WHERE username = ?"
    );
    $update->bind_param(
        'issss',
        $remainingDays,
        $kind,
        $package,
        $effectiveLastPaymentDate,
        $username
    );
    $update->execute();
    $update->close();

    if ($termDays > 0) {
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
        'suscription_package' => $package,
        'daystoenjoy' => $remainingDays,
        'registrationdate' => $registrationDate,
        'lastsuscriptionpaymentdate' => $effectiveLastPaymentDate,
    ];
}

function goraCanAccessVideoModule(?array $user): bool
{
    if ($user === null || (int) ($user['active'] ?? 0) !== 1) {
        return false;
    }

    $kind = $user['suscriptionkind'] ?? '';
    if ($kind === 'De Pago') {
        return (int) ($user['suscriptionactive'] ?? 0) === 1
            && (int) ($user['suscriptionpayed'] ?? 0) === 1
            && goraSubscriptionTermDays($user['suscription_package'] ?? null) > 0
            && (int) ($user['daystoenjoy'] ?? 0) > 0;
    }

    if ($kind === 'Trial') {
        $daysRemaining = (int) ($user['daystoenjoy'] ?? 0);
        return $daysRemaining > 0 && $daysRemaining < 31;
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
