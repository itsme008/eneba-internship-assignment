# Release checklist

Current release policy: visitors browse and prepare carts; sign-in/registration is mandatory before quote/payment. Account emails receive simulated keys. Existing historical guest orders are preserved.

## Locally verified

- PostgreSQL suite: 21 tests / 286 assertions, including authentication, ownership, stock contention, replay, money, merge, search and library.
- Type checking, production asset build, PHP formatting and Composer platform validation.
- Brevo test accepted and recipient confirmed receipt; the user also confirmed that actual purchase email and onscreen keys match.
- Connected-browser authenticated purchase, decline, session boundaries, cart transfer, account library/history, fuzzy suggestions, and desktop/mobile checks (see dated review evidence).

## Must pass before submission

- [ ] Review final visual match at desktop/mobile widths; confirm no critical interaction regression.
- [x] Run the repeatable Playwright suite: three browser tests passed in CI.
- [x] Build the production Docker image in CI. Runtime verification remains part of hosted deployment checks below.
- [x] Create/publish the intended private repository, with no .env, credentials, runtime database or generated traces.
- [x] Initialize existing Neon PostgreSQL; verify direct/pooled TLS connections and pg_trgm migrations.
- [x] Configure the approved free Render Docker service using the same production database and stable APP_KEY; first build in progress.
- [x] Apply migrations once and explicitly seed once. Do not reset stock or reseed on every app start.
- [x] Verify HTTPS/secure sessions, CSRF, deep links, protected payment, catalogue/search, simulated purchase, matching onscreen/email keys, and account library on the live site.
- [x] Restart the service and confirm accounts, session, orders, encrypted keys, stock and single email attempt remain consistent.
- [ ] Reconcile earlier conversations and original attachments. Refresh Markdown export after the final saved response; maintain a second backup.
- [ ] Fill in real GitHub/public app links in the submission draft and author approves the final reply to Eneba.

## Hosted configuration

Use Render secrets/settings for APP_KEY, APP_URL, DB_URL, BREVO_API_KEY, MAIL_FROM_ADDRESS and MAIL_FROM_NAME. Keep APP_DEBUG=false, SESSION_SECURE_COOKIE=true and ORDER_EMAIL_DRIVER=brevo for the deployed HTTPS app. The verified sender must exactly match MAIL_FROM_ADDRESS. Never put credentials into GitHub, screenshots or the public transcript.

Neon migrations and the 54-product seed completed. Pooled application purchase verification passed inside a rolled-back transaction; Neon MCP read-only verification passed. Render sign-in is confirmed. Private repository publication is approved; Render service creation/deployment remains pending approval.

Private repository published: `itsme008/eneba-internship-assignment`. The author approved GitHub workflow authorization; push succeeded. CI run `43358789563` passed on application commit `4a6c1ca`: 21 backend tests / 286 assertions, three browser tests, type/build/format checks and production Docker build.

Hosted release passed: see [hosted verification](reviews/hosted-release.md). The author approved free Render deployment and secret transfer, plus one live demo purchase/email, and confirmed inbox receipt with the matching key. All 23 inline chat image binaries are privately preserved and hash-inventoried in `ai-history/inline-attachments.json`. Final export and off-machine backup remain.
