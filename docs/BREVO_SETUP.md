# Brevo setup for purchase-key emails

This integration sends **transactional purchase receipts and simulated keys** over HTTPS. It does not enable forgotten-password recovery, marketing campaigns or real payments. Account setup and real inbox delivery have not yet been verified.

1. Sign in or create an account at [Brevo](https://login.brevo.com/). Complete account verification yourself. If using Google sign-in, complete that flow in your own browser; browser automation was blocked from accessing Google's account origin.
2. Go to **Settings → Senders, Domains, IPs → Senders → Add a sender**. Use a recognizable demo name, such as `Daraab's Marketplace Demo`, and an email address you control. Complete the verification code sent to that inbox. [Official sender instructions](https://help.brevo.com/hc/en-us/articles/208836149-Create-a-new-sender-From-name-and-From-email).
3. Go to **Settings → SMTP & API → API Keys & MCP → Generate API key**. Create a standard API key named `eneba-demo-key-delivery`. Choose Restricted access, set Transactional email to All, and leave every other resource at None. Brevo currently offers only None/Read/All for transactional email; Read cannot send. Do not create an SMTP key or convert it to an MCP key. Copy the secret directly into the ignored local `.env` file, not chat, screenshots or source control. [Official API-key instructions](https://help.brevo.com/hc/en-us/articles/209467485-Create-and-manage-your-API-keys).
4. Set these server-only values in `.env`:

```dotenv
ORDER_EMAIL_DRIVER=brevo
BREVO_API_KEY=your_private_api_key
MAIL_FROM_ADDRESS=the_sender_you_verified
MAIL_FROM_NAME="Daraab's Marketplace Demo"
```

Do not use a `VITE_` prefix: that would put credentials into the browser build. Do not replace the existing `APP_KEY`. `MAIL_MAILER=log` may remain for unrelated framework mail; purchase receipts use the explicit Brevo HTTPS adapter and never log keys.

5. Run `php artisan config:clear`. Check that Brevo's transactional sending is active and any account approval is complete. If the API reports a blocked/unapproved account or sender, complete that provider step before attempting delivery again.
6. Make a simulated guest purchase with **your own receiving email**. Confirm that the on-screen keys remain available and that the email arrives, including the spam folder. The page reports provider acceptance, not guaranteed inbox placement. Until this real mailbox check succeeds, delivery is unverified.

A custom domain is preferred, but do not buy one solely to continue this demo without checking the verified sender option first. Brevo documents temporary sender rewriting for free/unauthenticated domains; this is not a guarantee that a particular account is activated or that an email will reach an inbox. [Official sender/domain troubleshooting](https://help.brevo.com/hc/en-us/articles/16045394674066-Troubleshooting-Domain-authentication).

## Failures and retries

A committed purchase is never rolled back by a mail error. The order page shows delivery status and an authorized retry button. Recipients come from the immutable order email snapshot; callers cannot use the retry endpoint to send to arbitrary addresses. Retries are rate-limited and attempt-bounded. A short DB lease prevents overlapping sends; a crashed send becomes retryable after lease expiry. A provider timeout may have accepted an email already, so retries can produce another copy of the **same** keys, never a new purchase or new key set.

For an operator-controlled retry of pending emails: `php artisan orders:send-emails --limit=10`. This sends real emails to recorded order recipients when configured. Do not run it against development purchases with fake addresses. No background worker or scheduled retry is installed. Use the order page's retry for a controlled single-order test.

## Deployment

Set the same values as Render secrets after local validation, using the HTTPS public `APP_URL`. Brevo uses `https://api.brevo.com/v3/smtp/email`; there is no SMTP-port dependency. Keep the stable encryption key and Neon database settings. Configure the sender and API access in Brevo before claiming email delivery is ready. [Official transactional API](https://developers.brevo.com/docs/send-a-transactional-email).
