# Release checklist

Current release policy: visitors browse and prepare carts; sign-in/registration is mandatory before quote/payment. Account emails receive simulated keys. Existing historical guest orders are preserved.

## Locally verified

- PostgreSQL suite: 21 tests / 286 assertions, including authentication, ownership, stock contention, replay, money, merge, search and library.
- Type checking, production asset build, PHP formatting and Composer platform validation.
- Brevo test accepted and recipient confirmed receipt; the user also confirmed that actual purchase email and onscreen keys match.
- Connected-browser authenticated purchase, decline, session boundaries, cart transfer, account library/history, fuzzy suggestions, and desktop/mobile checks (see dated review evidence).

## Must pass before submission

- [ ] Review final visual match at desktop/mobile widths; confirm no critical interaction regression.
- [ ] Run the repeatable Playwright suite. CI workflow is prepared but has not run on GitHub yet.
- [ ] Build/run the production Docker image; the local workstation has no Docker engine.
- [ ] Create/publish the intended repository, with no .env, credentials, runtime database or generated traces.
- [ ] Create Neon PostgreSQL; configure a TLS database URL and verify pg_trgm migrations.
- [ ] Configure Render from render.yaml using the same production database and a stable APP_KEY.
- [ ] Apply migrations once and explicitly seed once. Do not reset stock or reseed on every app start.
- [ ] Verify HTTPS/secure sessions, CSRF, deep links, protected payment, catalogue/search, simulated purchase, matching onscreen/email keys, and account library on the live site.
- [ ] Restart/redeploy and confirm accounts, orders, encrypted keys and stock remain consistent.
- [ ] Reconcile earlier conversations and original attachments. Refresh Markdown export after the final saved response; maintain a second backup.
- [ ] Fill in real GitHub/public app links in the submission draft and author approves the final reply to Eneba.

## Hosted configuration

Use Render secrets/settings for APP_KEY, APP_URL, DB_URL, BREVO_API_KEY, MAIL_FROM_ADDRESS and MAIL_FROM_NAME. Keep APP_DEBUG=false, SESSION_SECURE_COOKIE=true and ORDER_EMAIL_DRIVER=brevo for the deployed HTTPS app. The verified sender must exactly match MAIL_FROM_ADDRESS. Never put credentials into GitHub, screenshots or the public transcript.

Neon TLS/read-only connectivity is verified: empty public schema and CREATE permission. Render sign-in is confirmed. Repository/service creation and hosted migration/deployment approval are still pending. A connected GitHub account exists, and local CLI authentication is verified with network access. No external repository/service was created during preparation.
