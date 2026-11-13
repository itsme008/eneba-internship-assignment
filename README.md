# Eneba-style digital marketplace

A React + TypeScript storefront and Laravel REST backend with PostgreSQL. The local buyer journey is implemented: discover products, compare seller offers, carry a guest cart through mandatory sign-in/registration, make a simulated payment, receive unique demo keys onscreen and by email, and retrieve purchases from account history or the keys library.

**This is an internship demonstration.** Prices, product/platform combinations, seller ratings, reviews and availability are sample data. Payments and delivered keys are simulated. No real payment credentials are requested. Forgotten-password recovery remains deferred. Purchase-key email delivery is implemented through Brevo HTTPS; sender/account configuration and real inbox verification are required.

## Run locally

Requirements: PHP **8.5** with PDO PostgreSQL, BCMath, mbstring and intl; Composer 2; Node **22.12+**; PostgreSQL 17 with permission to create `pg_trgm`. Dependency versions are locked in `composer.lock` and `package-lock.json`.

1. Install dependencies: `composer install` and `npm ci`.
2. Copy `.env.example` to `.env`; set PostgreSQL credentials for an empty `eneba` database.
3. Run `php artisan key:generate`. Keep that key stable: encrypted keys and session/quote data depend on it.
4. Run `php artisan migrate --seed`. The seed creates exactly **30 games, 15 gift cards and 9 subscriptions**, with 6–7 distinct seller offers each. A repeated seed leaves existing products/stock/orders untouched.
5. Run `npm run build`, then `php artisan serve --host=127.0.0.1 --port=8000`.
6. Open [the local storefront](http://127.0.0.1:8000). Create your own buyer account; no shared demo password is seeded.

For frontend editing, run `npm run dev` alongside Laravel. Laravel serves browser routes and API from one origin. The production build does not need the Vite server.

This workspace currently uses an isolated local PostgreSQL cluster in ignored `.runtime/postgres`, port **55432**. That cluster and `.env` are local-only. Other machines should use their own PostgreSQL service and credentials.

## Checks

- `npm run typecheck`
- `npm run build`
- `vendor/bin/pint --test`
- Create an **isolated** `eneba_test` PostgreSQL database and run `php artisan test`. `phpunit.xml` sets that database name; host/port/user/password come from the environment. These tests rebuild that database, so never point them at valuable data. `pcntl` enables the real two-process last-key contention test; otherwise that test is skipped explicitly.
- `npx playwright install chromium`, then `npm run test:e2e` against a locally running, seeded app on the email-disabled test port: `ORDER_EMAIL_DRIVER=disabled php artisan serve --host=127.0.0.1 --port=8001 --no-reload`. Set `E2E_BASE_URL` if needed. Browser tests create sample accounts and consume a small amount of demo stock; use a disposable local database. They are authored as a repeatable regression suite; see the validation report for what has actually run.

The browser journey was also exercised through the connected browser: the original registered journey and the new guest-email/payment/keys/optional account journey. See [validation evidence](docs/reviews/implementation-validation.md).

## Design and code map

- `app/Services`: money conversion, catalogue, cart, checkout and separately resolved homepage block services.
- `app/Http`: session-version/input middleware and REST controllers.
- `database/migrations` and `database/seeders`: relational constraints, demo content and one-time inventory.
- `resources/js/app.tsx`, `types.ts`, `resources/css/app.css`: routed storefront and shared API/domain types.
- `tests/Feature`: PostgreSQL business-rule, ownership, rollback and contention checks.
- [PRD](docs/PRD.md), [architecture](docs/ARCHITECTURE.md), [database](docs/DATABASE.md), [API](docs/API.md), [tasks](docs/TASKS.md).

Checkout uses an authenticated encrypted, versioned ten-minute quote. User/cart/attempt and sorted offer locks protect an all-or-nothing transaction. A terminal attempt receipt is replayed before checking old quote expiry/cart emptiness. Prices round converted units half-up before multiplying quantities. A key is generated for each purchased unit and encrypted at rest. Guest transfers have immutable IDs and receipts; shortages are retained for explicit correction. Brevo HTTPS handles purchase emails after commit with durable status, bounded retries and leases. No payment gateway, Elasticsearch, Redis or background worker is needed.

## Render + Neon deployment

The selected host remains Render with Neon PostgreSQL. `Dockerfile`, `docker/apache.conf`, `.dockerignore` and `render.yaml` prepare a single Apache/PHP application that includes compiled React assets. **The Docker image and public deployment have not yet been validated.** Docker is not installed in this workspace.

1. Create the Neon database and enable `pg_trgm` via the migration role. Supply a TLS `DB_URL` with `sslmode=require` as a secret, never a committed file.
2. Set one stable `APP_KEY`, `APP_URL` to the HTTPS hostname, `APP_ENV=production`, `APP_DEBUG=false`, secure session cookies, DB-backed sessions/cache and stderr logging. `render.yaml` lists these settings. Trust only the deployed proxy addresses if URL/security handling requires it; verify secure cookies after deployment.
3. Run one controlled `php artisan migrate --force` and one explicit `php artisan db:seed --force` using the same release/environment before traffic. Free-tier shell/predeploy availability must be checked; a trusted local release command with Neon credentials is an alternative. Boot/redeploy does **not** run destructive seeding.
4. Build/deploy the pinned source revision. Apache listens on port 10000 and serves `public/`; frontend deep routes and API 404s remain separate.
5. Verify `/api/v1/health/ready`, HTTPS sign-in/CSRF, catalogue, purchase, key retrieval, cold-start behaviour and persistence across restart. Set secure cookies only for HTTPS deployment, not HTTP local development.

GitHub repository publication, host account provisioning and submission are still pending. No public URL is claimed yet.

## Assets and conversation history

Game cover/screenshot artwork is downloaded from public Steam promotional endpoints into `public/images/games`; it is bundled locally, not fetched by the runtime backend. `scripts/fetch-art.py` reproduces those downloads. Gift-card/subscription illustrations and the Minecraft placeholder are local SVG demo assets. Game art belongs to its respective rights holders; this is an unaffiliated educational demo.

The assignment requires the complete user/assistant conversation. [History preservation instructions](docs/AI_CONVERSATION_HISTORY.md) explain the source and current coverage. Run `python3 scripts/export-history.py --source /absolute/path/to/project-session.jsonl` to refresh the Markdown snapshot and hash/count manifest after each session. Earlier project chats and attachment binaries still need reconciliation. A snapshot generated during a turn cannot include its later final response; do a final refresh after that response has been saved.


## Authenticated checkout and purchase-key email (R06)

Current scope: sign-in/registration is mandatory before payment, matching the assignment. Guest carts survive authentication and merge once into the buyer cart. Keys are shown in the owner-only order and emailed to the account purchase address. Historical guest orders remain accessible through their existing session proof and can still be linked to a matching account; no new guest purchases are accepted.

See [Brevo setup](docs/BREVO_SETUP.md). Set `ORDER_EMAIL_DRIVER=brevo`, `BREVO_API_KEY`, `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` as server secrets. Configuration absence is shown honestly; provider failure never undoes a purchase. Outbox retries send the same keys and never repurchase. Guest links require the purchasing browser session unless the order is attached to an account; emails contain the keys directly. No password-reset flow is restored.

Run browser QA with email disabled on port 8001, so fake test recipients cannot reach a real provider. Only use a receiving mailbox you control for a specifically authorized live mail check. Test and real-delivery evidence is recorded in [guest-checkout review](docs/reviews/guest-checkout.md).


## Release progress

The current status is in [TASKS.md](docs/TASKS.md) and the [release checklist](docs/RELEASE_CHECKLIST.md). GitHub Actions is prepared to run PostgreSQL tests, type/build checks, repeatable browser purchase scenarios and a production Docker build; the workflow has not run remotely yet. Metropolis font files are bundled under resources/fonts with their original license in public/fonts; no runtime Google Fonts request is required.
