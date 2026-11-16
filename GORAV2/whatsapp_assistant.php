<?php

function goraWhatsappRequiredSetting(string $name): string
{
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException('WhatsApp configuration is missing: ' . $name);
    }

    return trim($value);
}

function goraWhatsappApiRequest(string $recipient, array $message): string
{
    $version = goraWhatsappRequiredSetting('GORA_WA_GRAPH_API_VERSION');
    $phoneNumberId = goraWhatsappRequiredSetting('GORA_WA_PHONE_NUMBER_ID');
    $accessToken = goraWhatsappRequiredSetting('GORA_WA_ACCESS_TOKEN');
    if (!preg_match('/\Av[0-9]+\.[0-9]+\z/', $version)) {
        throw new RuntimeException('WhatsApp Graph API version is invalid.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required for WhatsApp messaging.');
    }

    $payload = array_merge([
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $recipient,
    ], $message);
    $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($encodedPayload === false) {
        throw new RuntimeException('Unable to encode WhatsApp message.');
    }

    $curl = curl_init('https://graph.facebook.com/' . $version . '/' . rawurlencode($phoneNumberId) . '/messages');
    if ($curl === false) {
        throw new RuntimeException('Unable to initialize the WhatsApp API request.');
    }
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $encodedPayload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($curl);
    $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    if ($response === false) {
        throw new RuntimeException('WhatsApp API request failed: ' . $curlError);
    }

    $decodedResponse = json_decode($response, true);
    if ($httpStatus < 200 || $httpStatus >= 300 || !is_array($decodedResponse)) {
        throw new RuntimeException('WhatsApp API returned HTTP ' . $httpStatus . '.');
    }
    $messageId = $decodedResponse['messages'][0]['id'] ?? null;
    if (!is_string($messageId) || $messageId === '') {
        throw new RuntimeException('WhatsApp API response did not contain a message ID.');
    }

    return $messageId;
}

function goraWhatsappRecordMessage(mysqli $conn, string $messageId, string $recipient, string $direction, string $status): void
{
    $stmt = $conn->prepare(
        'INSERT INTO gora_whatsapp_events (provider_message_id, contact_identifier, direction, status, created_at)
         VALUES (?, ?, ?, ?, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE status = VALUES(status)'
    );
    $stmt->bind_param('ssss', $messageId, $recipient, $direction, $status);
    if (!$stmt->execute()) {
        throw new RuntimeException('Unable to record WhatsApp message event: ' . $stmt->error);
    }
    $stmt->close();
}

function goraWhatsappSendText(string $recipient, string $text): string
{
    $messageId = goraWhatsappApiRequest($recipient, [
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $text,
        ],
    ]);

    return $messageId;
}

function goraWhatsappSendRegistrationWelcome(mysqli $conn, string $phone, string $name, string $language): void
{
    if (!preg_match('/\A\+[1-9][0-9]{7,14}\z/', $phone)) {
        throw new RuntimeException('Registration phone is not in E.164 format.');
    }

    $templateName = getenv('GORA_WA_WELCOME_TEMPLATE_NAME');
    if ($templateName === false || trim($templateName) === '') {
        throw new RuntimeException('WhatsApp welcome template name is not configured.');
    }
    $templateLanguages = [
        'es' => getenv('GORA_WA_WELCOME_TEMPLATE_LANG_ES') ?: 'es',
        'en' => getenv('GORA_WA_WELCOME_TEMPLATE_LANG_EN') ?: 'en_US',
        'pt' => getenv('GORA_WA_WELCOME_TEMPLATE_LANG_PT') ?: 'pt_BR',
    ];
    $templateLanguage = $templateLanguages[$language] ?? $templateLanguages['es'];
    $messageId = goraWhatsappApiRequest(ltrim($phone, '+'), [
        'type' => 'template',
        'template' => [
            'name' => trim($templateName),
            'language' => ['code' => $templateLanguage],
            'components' => [[
                'type' => 'body',
                'parameters' => [[
                    'type' => 'text',
                    'text' => $name,
                ]],
            ]],
        ],
    ]);

    goraWhatsappRecordMessage($conn, $messageId, $phone, 'outbound', 'sent');
}

function goraWhatsappNormalizePhrase(string $value): string
{
    $value = trim($value);
    if (function_exists('iconv')) {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized !== false) {
            $value = $normalized;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';
    return trim(preg_replace('/\s+/', ' ', $value) ?? '');
}

function goraWhatsappFindFaq(mysqli $conn, string $language, string $message): ?string
{
    $normalizedMessage = goraWhatsappNormalizePhrase($message);
    if ($normalizedMessage === '') {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT match_phrase, answer
         FROM gora_whatsapp_faqs
         WHERE language = ? AND is_active = 1
         ORDER BY priority DESC, CHAR_LENGTH(match_phrase) DESC
         LIMIT 500'
    );
    $stmt->bind_param('s', $language);
    if (!$stmt->execute()) {
        throw new RuntimeException('Unable to load WhatsApp FAQs: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    while ($faq = $result->fetch_assoc()) {
        $phrase = goraWhatsappNormalizePhrase((string) $faq['match_phrase']);
        if ($phrase !== '' && strpos($normalizedMessage, $phrase) !== false) {
            $answer = trim((string) $faq['answer']);
            $stmt->close();
            return $answer !== '' ? $answer : null;
        }
    }
    $stmt->close();

    return null;
}

function goraWhatsappFallback(string $language, bool $requestQueued = false): string
{
    $now = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));
    $weekday = (int) $now->format('N');
    $hour = (int) $now->format('G');
    $withinSupportHours = $weekday >= 1 && $weekday <= 5 && $hour >= 8 && $hour < 17;

    $messages = [
        'es' => $requestQueued
            ? ($withinSupportHours
                ? 'Gracias por escribir a GORA. Aún no tengo una respuesta configurada para esa pregunta y ya la envié al equipo de soporte. Nuestro horario es de lunes a viernes, de 8:00 a. m. a 5:00 p. m. (hora Colombia).'
                : 'Gracias por escribir a GORA. Aún no tengo una respuesta configurada para esa pregunta y la envié al equipo de soporte. Te responderán en nuestro horario, de lunes a viernes, de 8:00 a. m. a 5:00 p. m. (hora Colombia).')
            : 'Gracias por escribir a GORA. Aún no tengo una respuesta configurada para esa pregunta. Nuestro horario de soporte es de lunes a viernes, de 8:00 a. m. a 5:00 p. m. (hora Colombia). Envíanos tu duda o problema técnico y te ayudaremos.',
        'en' => $requestQueued
            ? ($withinSupportHours
                ? 'Thanks for contacting GORA. I do not have an answer configured for that question yet, so I sent it to our support team. Support hours are Monday to Friday, 8:00 a.m. to 5:00 p.m. Colombia time.'
                : 'Thanks for contacting GORA. I do not have an answer configured for that question yet, so I sent it to our support team. They will reply during support hours: Monday to Friday, 8:00 a.m. to 5:00 p.m. Colombia time.')
            : 'Thanks for contacting GORA. I do not have an answer configured for that question yet. Support hours are Monday to Friday, 8:00 a.m. to 5:00 p.m. Colombia time. Send us your question or technical issue and we will help.',
        'pt' => $requestQueued
            ? ($withinSupportHours
                ? 'Obrigado por entrar em contato com a GORA. Ainda não tenho uma resposta configurada para essa pergunta e já a encaminhei à equipe de suporte. O horário de atendimento é de segunda a sexta-feira, das 8h às 17h (horário da Colômbia).'
                : 'Obrigado por entrar em contato com a GORA. Ainda não tenho uma resposta configurada para essa pergunta e já a encaminhei à equipe de suporte. A equipe responderá no horário de atendimento: de segunda a sexta-feira, das 8h às 17h (horário da Colômbia).')
            : 'Obrigado por entrar em contato com a GORA. Ainda não tenho uma resposta configurada para essa pergunta. Nosso horário de suporte é de segunda a sexta-feira, das 8h às 17h (horário da Colômbia). Envie sua dúvida ou problema técnico e ajudaremos.',
    ];

    return $messages[$language] ?? $messages['es'];
}

function goraWhatsappUpdateInboundStatus(mysqli $conn, string $messageId, string $status): void
{
    $stmt = $conn->prepare(
        "UPDATE gora_whatsapp_events
         SET status = ?, processed_at = UTC_TIMESTAMP()
         WHERE provider_message_id = ? AND direction = 'inbound'"
    );
    $stmt->bind_param('ss', $status, $messageId);
    if (!$stmt->execute()) {
        throw new RuntimeException('Unable to update WhatsApp event status: ' . $stmt->error);
    }
    $stmt->close();
}

function goraWhatsappHandleIncomingMessage(mysqli $conn, array $message): void
{
    $messageId = $message['id'] ?? null;
    $recipient = $message['from'] ?? $message['from_user_id'] ?? null;
    if (!is_string($messageId) || $messageId === '' || !is_string($recipient) || $recipient === '') {
        error_log('Ignoring WhatsApp webhook message without a message ID or recipient.');
        return;
    }
    if (strlen($recipient) > 128) {
        error_log('Ignoring WhatsApp webhook message with an invalid recipient identifier.');
        return;
    }

    $phone = preg_match('/\A[1-9][0-9]{7,14}\z/', $recipient) ? '+' . $recipient : null;
    $stmt = $conn->prepare(
        "INSERT IGNORE INTO gora_whatsapp_events
            (provider_message_id, contact_identifier, direction, status, created_at)
         VALUES (?, ?, 'inbound', 'pending', UTC_TIMESTAMP())"
    );
    $stmt->bind_param('ss', $messageId, $recipient);
    if (!$stmt->execute()) {
        throw new RuntimeException('Unable to record incoming WhatsApp event: ' . $stmt->error);
    }
    $isNewEvent = $stmt->affected_rows === 1;
    $stmt->close();

    if (!$isNewEvent) {
        $stmt = $conn->prepare(
            "UPDATE gora_whatsapp_events
             SET status = 'processing'
             WHERE provider_message_id = ? AND direction = 'inbound' AND status IN ('pending', 'failed')"
        );
        $stmt->bind_param('s', $messageId);
        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to claim WhatsApp event: ' . $stmt->error);
        }
        $claimed = $stmt->affected_rows === 1;
        $stmt->close();
        if (!$claimed) {
            return;
        }
    } else {
        goraWhatsappUpdateInboundStatus($conn, $messageId, 'processing');
    }

    $messageText = '';
    if (($message['type'] ?? '') === 'text' && is_string($message['text']['body'] ?? null)) {
        $messageText = trim($message['text']['body']);
    }
    $command = strtoupper(goraWhatsappNormalizePhrase($messageText));

    try {
        $language = 'es';
        $optedOut = false;
        if ($phone !== null) {
            $stmt = $conn->prepare(
                'SELECT language, opted_out_at
                 FROM gora_whatsapp_contacts
                 WHERE phone_e164 = ?
                 ORDER BY opted_out_at IS NOT NULL DESC, contact_id DESC
                 LIMIT 1'
            );
            $stmt->bind_param('s', $phone);
            if (!$stmt->execute()) {
                throw new RuntimeException('Unable to load WhatsApp contact: ' . $stmt->error);
            }
            $contact = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($contact) {
                $language = in_array($contact['language'], ['es', 'en', 'pt'], true) ? $contact['language'] : 'es';
                $optedOut = $contact['opted_out_at'] !== null;
            }
        }

        if (in_array($command, ['SALIR', 'STOP', 'SAIR'], true)) {
            if ($phone !== null) {
                $stmt = $conn->prepare(
                    'UPDATE gora_whatsapp_contacts SET opted_out_at = UTC_TIMESTAMP()
                     WHERE phone_e164 = ?'
                );
                $stmt->bind_param('s', $phone);
                if (!$stmt->execute()) {
                    throw new RuntimeException('Unable to save WhatsApp opt-out: ' . $stmt->error);
                }
                $updatedContacts = $stmt->affected_rows;
                $stmt->close();
                if ($updatedContacts === 0) {
                    $stmt = $conn->prepare(
                        "INSERT INTO gora_whatsapp_contacts (phone_e164, language, opted_out_at)
                         VALUES (?, ?, UTC_TIMESTAMP())"
                    );
                    $stmt->bind_param('ss', $phone, $language);
                    if (!$stmt->execute()) {
                        throw new RuntimeException('Unable to save WhatsApp opt-out: ' . $stmt->error);
                    }
                    $stmt->close();
                }
            }
            $reply = [
                'es' => 'De acuerdo. No recibirás más respuestas automáticas de GORA por WhatsApp. Para volver a activarlas, escribe INICIAR.',
                'en' => 'Understood. You will no longer receive automated replies from GORA on WhatsApp. Text START to turn them back on.',
                'pt' => 'Entendido. Você não receberá mais respostas automáticas da GORA pelo WhatsApp. Envie INICIAR para reativá-las.',
            ][$language];
        } elseif (in_array($command, ['INICIAR', 'START'], true)) {
            if ($phone !== null) {
                $stmt = $conn->prepare(
                    'UPDATE gora_whatsapp_contacts
                     SET opted_in_at = UTC_TIMESTAMP(), opted_out_at = NULL
                     WHERE phone_e164 = ?'
                );
                $stmt->bind_param('s', $phone);
                if (!$stmt->execute()) {
                    throw new RuntimeException('Unable to save WhatsApp opt-in: ' . $stmt->error);
                }
                $updatedContacts = $stmt->affected_rows;
                $stmt->close();
                if ($updatedContacts === 0) {
                    $stmt = $conn->prepare(
                        "INSERT INTO gora_whatsapp_contacts (phone_e164, language, opted_in_at)
                         VALUES (?, ?, UTC_TIMESTAMP())"
                    );
                    $stmt->bind_param('ss', $phone, $language);
                    if (!$stmt->execute()) {
                        throw new RuntimeException('Unable to save WhatsApp opt-in: ' . $stmt->error);
                    }
                    $stmt->close();
                }
            }
            $reply = [
                'es' => 'Listo. Las respuestas automáticas de soporte de GORA están activadas nuevamente.',
                'en' => 'Done. GORA automated support replies are enabled again.',
                'pt' => 'Pronto. As respostas automáticas de suporte da GORA foram reativadas.',
            ][$language];
        } elseif ($optedOut) {
            goraWhatsappUpdateInboundStatus($conn, $messageId, 'ignored');
            return;
        } else {
            $reply = $messageText !== '' ? goraWhatsappFindFaq($conn, $language, $messageText) : null;
            if ($reply === null) {
                $requestQueued = $messageText !== '';
                if ($requestQueued) {
                    $stmt = $conn->prepare(
                        "INSERT IGNORE INTO gora_whatsapp_support_queue
                            (inbound_message_id, contact_identifier, language, message_text, status, created_at)
                         VALUES (?, ?, ?, ?, 'pending', UTC_TIMESTAMP())"
                    );
                    $stmt->bind_param('ssss', $messageId, $recipient, $language, $messageText);
                    if (!$stmt->execute()) {
                        throw new RuntimeException('Unable to queue WhatsApp support request: ' . $stmt->error);
                    }
                    $stmt->close();
                }
                $reply = goraWhatsappFallback($language, $requestQueued);
            }
        }

        $outgoingMessageId = goraWhatsappSendText($recipient, $reply);
        goraWhatsappUpdateInboundStatus($conn, $messageId, 'replied');
        try {
            goraWhatsappRecordMessage($conn, $outgoingMessageId, $recipient, 'outbound', 'sent');
        } catch (RuntimeException $exception) {
            error_log('WhatsApp reply was sent but could not be recorded: ' . $exception->getMessage());
        }
    } catch (Throwable $exception) {
        try {
            goraWhatsappUpdateInboundStatus($conn, $messageId, 'failed');
        } catch (Throwable $statusException) {
            error_log('Unable to record failed WhatsApp event: ' . $statusException->getMessage());
        }
        throw $exception;
    }
}
