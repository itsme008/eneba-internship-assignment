# Guest checkout validation — 13 November 2026

## Scope and authority

The user approved guest email capture, simulated payment, onscreen and emailed keys, and optional account creation after purchase. This deliberately supersedes the assignment's signed-in-only checkout wording. Order history remains authenticated. Password recovery remains deferred.

## Implemented boundaries

Guest orders require the originating server session for access. Account creation or login can claim only the selected order, with both that session proof and an exact normalized email match; email knowledge alone cannot claim orders. Claimed orders require account authentication. Quote validation, atomic stock/key allocation and idempotent payment attempts are shared with account purchases.

Email is attempted after purchase commits. Provider failure does not roll back payment or allocate another key. Delivery retries reuse the existing keys and recipient. Leases, retry spacing and attempt limits bound retries. An uncertain provider response can cause the same email to be delivered twice; purchase/key allocation remains idempotent. Provider acceptance does not prove inbox delivery. No public password-reset flow was added.

## Evidence

- Latest backend regression run: **21 tests passed, 265 assertions**. Includes guest privacy, quote changes, decline, purchase replay, registration/login claims, invalid claims and provider failure/retry.
- Type checking and production asset build passed after the guest UI changes. PHP formatting completed.
- Connected-browser verification on the isolated email-disabled local service (port 8001): guest purchase confirmation displayed a simulated key, cart was empty, optional registration succeeded, and the same order appeared in the new account's history.
- Playwright purchase scenarios were updated/authored; the standalone Playwright suite has not yet been executed.
- Local additive migration applied successfully. Rollback refuses to discard existing guest checkout records.

## Pending release checks

Verify Brevo sender/account approval, configure secrets locally and on hosting, and test a real recipient inbox with explicit authorization. One explicitly authorized test email was submitted to Brevo using the configured sender as recipient. Brevo rejected it with HTTP 401 (`unauthorized`); no message ID was returned and inbox delivery is unverified. Local configuration contains a standard-format API key with no outer whitespace and is not cached. Credentials must be corrected before another authorized test. The browser QA service explicitly disables outgoing email to avoid sending to synthetic addresses. Do not run the bulk email retry command against these synthetic QA orders.

Brevo browser inspection encountered a Google account sign-in redirect and automatic approval review rejected that access. Setup continues manually using official documentation; no Google account access is needed.

Deployment, production email acceptance/delivery, and the complete deployed purchase journey remain unverified. See [Brevo setup](../BREVO_SETUP.md).


## Brevo diagnostic update — 13 November 2026

Direct browser inspection confirmed the configured key is active, has Transactional email All access, and IP blocking is disabled. A read-only transactional statistics API request succeeded (HTTP 200), establishing that the key authenticates. Earlier HTTP 401 responses alone did not establish an invalid key. After explicit approval for a diagnostic send, Brevo accepted the single demo-key email to the configured sender address with HTTP 201 and returned a message ID. Inbox receipt is still awaiting user confirmation. The specific cause of the earlier failures is not established. No provider security settings were changed.


## Delivery rejection diagnosis

The accepted diagnostic message subsequently received an `error` event: sender invalid; validate sender or authenticate domain. Direct inspection found that the verified sender in Brevo differs from `MAIL_FROM_ADDRESS`. HTTP 201 represented acceptance for processing, not successful delivery. No additional email was sent during this read-only diagnosis. Align the configured sender with a verified Brevo sender or verify the intended custom-domain sender before retesting.


## Successful inbox verification — 13 November 2026

The user approved using the verified Gmail sender for the demo. Local `MAIL_FROM_ADDRESS` was aligned with that verified sender and the configuration cache was cleared. A separately authorized demo-key test to the Gmail inbox was accepted by Brevo (HTTP 201). The user then confirmed receipt. This verifies the local template/API/sender-to-inbox path for that test; it does not establish production deployment or every recipient delivery. No real redeemable key was sent.
