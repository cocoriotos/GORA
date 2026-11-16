# GORA WhatsApp assistant

The WhatsApp integration uses Meta's WhatsApp Business Cloud API. The same
implementation is available at the project root and under `GORAV2`.

## Database

Run `GORAV2/migrations/20261117_whatsapp_assistant.sql` against the GORA
database. The country migration `GORAV2/migrations/20261116_create_country_codes.sql`
must also have been applied because registration stores an E.164 phone number.

FAQ entries can be added to `gora_whatsapp_faqs`. `match_phrase` is the phrase
the user is likely to write; `answer` is the reply. Add separate rows for each
supported language (`es`, `en`, `pt`). Matching ignores case and accents and
uses the phrase anywhere in the incoming text. Set `is_active` to `0` to
disable an entry. When no FAQ matches, the incoming text is added to
`gora_whatsapp_support_queue` for the support team and the assistant tells the
user the support hours (Monday to Friday, 8:00 a.m. to 5:00 p.m. Colombia
time). The support team can work the queue by updating its `status`.

## Meta setup

1. Connect the GORA WhatsApp Business number to a Meta app with the WhatsApp
   product and configure the public webhook URL as either
   `https://<domain>/whatsapp-webhook.php` or
   `https://<domain>/GORAV2/whatsapp-webhook.php`.
2. Subscribe the app to the `messages` webhook field.
3. Configure these environment variables in the hosting environment or the
   server's private configuration (never commit their values):
   - `GORA_WA_VERIFY_TOKEN`: private string used for webhook verification.
   - `GORA_WA_APP_SECRET`: Meta app secret, used to validate webhook signatures.
   - `GORA_WA_ACCESS_TOKEN`: system-user access token with WhatsApp messaging
     permission.
   - `GORA_WA_PHONE_NUMBER_ID`: ID of the connected WhatsApp Business phone.
   - `GORA_WA_GRAPH_API_VERSION`: supported Graph API version, e.g. `vXX.0`.
   - `GORA_WA_WELCOME_TEMPLATE_NAME`: approved message template name.
   - Optional locale overrides: `GORA_WA_WELCOME_TEMPLATE_LANG_ES`,
     `GORA_WA_WELCOME_TEMPLATE_LANG_EN`, and
     `GORA_WA_WELCOME_TEMPLATE_LANG_PT` (defaults: `es`, `en_US`, `pt_BR`).
4. Create and obtain approval for a utility template using one text parameter
   in the body for the user's name. The message should welcome the user to
   GORA, explain this is the support chat for questions, FAQs, and technical
   issues, and state the Monday-to-Friday, 8 a.m.-to-5 p.m. Colombia support
   hours. The template must have approved localizations matching the configured
   language codes. Set its name in `GORA_WA_WELCOME_TEMPLATE_NAME`.
5. Test webhook verification, inbound replies, and the approved welcome
   template with Meta's test number before switching to the production number.

The registration form has an optional, unchecked WhatsApp consent checkbox.
The welcome template is sent only when the user checks it. Users can opt out of
automated replies by sending `SALIR`, `STOP`, or `SAIR`; they can re-enable them
by sending `INICIAR` or `START`. Registration itself is not blocked if the user
does not consent.

Incoming free-form support replies are sent only after the user messages GORA;
the proactive registration welcome uses the approved template.
