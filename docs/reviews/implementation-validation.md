# Local implementation evidence — 12 November 2026

Implementation was explicitly authorized by “lets start implementing”. The retained advanced architecture is implemented locally; no two-day simplification checkpoint has taken place. This report distinguishes working evidence from release prerequisites.

## Implemented and exercised

- PHP 8.5.12, Laravel 13.42.0, React 19, TypeScript, Vite 7 and PostgreSQL 17.12. Composer/npm lockfiles exist; Composer validation/platform checks pass.
- Real PostgreSQL migrations with `pg_trgm`, foreign keys, offer/featured/replay/key uniqueness, nonnegative inventory and line-amount constraints.
- Exactly 54 catalogue records: 30 games, 15 gift cards, 9 subscriptions. Each has 6–7 distinct sellers from an eight-seller pool. First-run random sample prices/selection/stock remain stable after repeat seeding; fresh independent databases may differ. Seeding never resets purchased stock or overwrites orders.
- Store with all five multi-filter groups, seven explicit sorts, numbered pages, global partial/typo search, debounced suggestions and stale-response protection. Arrow-key focus and Enter navigation were exercised on a typo suggestion.
- Backend section manifest and independently resolved homepage services; database banner/category/curated content; per-block loading/error/retry. Recently viewed IDs remain browser-local.
- Product gallery, featured seller, recommended/other offers, offer expansion, read-only sample reviews and curated related products.
- Same-browser guest cart; DB buyer cart; revision conflicts and exactly-once transfer receipts; whole-cart Buy now; stock-blocking checkout.
- Registration, sign-in/out, profile display-name updates and current-password-authenticated password change. The recorded engineering default revokes all sessions. No recovery-token table, reset route or Brevo integration.
- Fixed versioned EUR/USD/GBP rates, integer-string money responses and exact BCMath unit rounding before quantity multiplication.
- Ten-minute authenticated encrypted quotes; stored request fingerprints/terminal replay receipts; user → cart → attempt → sorted offer locking. Base/charged prices, FX version, quantity and cart revision are revalidated. Per-unit keys are unique and encrypted at rest; order access is owner-only.
- Three simulated payment methods, explicit decline control, order confirmation/history and key copy. No real payment credentials, real charges or activatable keys.

## Verification actually run

`php artisan test --compact`: **14 tests, 200 assertions, all passing**. Uses an isolated real PostgreSQL test database, not SQLite. Coverage includes catalogue counts/filter/sort/typo examples, first-seed stability, exact conversion, purchase success, receipt replay after quote expiry, unique encrypted keys, owner-only retrieval, simulated decline, shortage preservation, stale cart revision, merge replay, forged/expired quotes, changed price, stale session version, password change, rollback after injected key-write failure, malformed JSON, unknown fields, API/deep-route separation, independent backend-driven homepage content and a missing-CSRF negative check outside Laravel's testing bypass.

The contention test forks two PHP processes with separate PostgreSQL connections to buy the final key. Results were one 201 success and one 409 rejection; stock became zero, exactly one order/key existed and the losing cart remained. Systems without `pcntl` explicitly skip this test; this workstation ran it.

`npm run typecheck` and `npm run build` passed. PHP formatting was applied with Pint; final formatting validation is recorded with the working tree checks. TypeScript currently uses a non-strict project setting and typed shared money/buyer/cart/product structures; fuller UI response typing is follow-up maintainability work.

Connected-browser automation exercised the local application:

1. Homepage loads backend collections; product page displays seller offers and gallery.
2. Guest adds an Elden Ring offer, opens/reloads cart, proceeds to sign-in and registers a local test buyer. Guest quantity appears once in buyer cart.
3. Currency switched from EUR to USD; quote and payment summary agree on the converted price.
4. Simulated decline leaves the cart; intentional PayPal success creates confirmation and a demo key. Copy control reports success.
5. Sign-out, sign-in, order-history reload and original order retrieval preserve the purchased key and charged currency.
6. At 390 × 844, catalogue/product/cart/checkout rendered without horizontal document overflow; category filtering returned 15 gift cards and account-cart increment/decrement worked. The temporary viewport override was reset afterward.

The Playwright CLI regression suite is authored in `tests/e2e/purchase.spec.ts`, but **has not been executed by its standalone runner**. The evidence above comes from connected-browser automation. Final CI/browser regression execution and a wider responsive/keyboard/error matrix remain open. No claim of exhaustive accessibility or all API combinations is made.

## Release work still open

- No Render/Neon account provisioning or managed DB connectivity has been performed. TLS/extension availability, cold starts, secure cookies/proxy handling and persisted data across host restart remain to be checked.
- Dockerfile/Apache/Render blueprint are prepared. Docker is absent locally; the image has not been built or run. Validate it before publication.
- Local Git repository initialized; no remote GitHub repository published, no public deployment URL, no submission email sent.
- Complete prompt/response Markdown snapshot and repeatable hash/count exporter exist. Raw snapshot backups are ignored under `.runtime/ai-history/raw`. Earlier exploration chats and original attachments still need reconciliation, and the final assistant response must be exported after it has persisted. A second-device backup is still the author's responsibility.
- Checkout uncertainty preserves the original key/payload in memory while that checkout screen is active. Reloading/navigation loses that transient recovery state; order history remains authoritative after a completed purchase. No delivered keys are written to local storage. Wider interrupted-navigation recovery can be reviewed before release.
- Final contract review should cover independent buyer-device sessions, all currency/filter edge combinations, and release infrastructure. No advanced mechanism has been silently simplified or removed.

Checkpoint due: **14 November 2026, 19:09:32 UTC / 21:09:32 Europe/Vilnius**. Assess deployability and remaining work before that time if blockers threaten the deadline. Local progress is encouraging; submission readiness depends on the remaining hosted and artifact checks.


Guest-checkout changes and later validation are recorded in [guest checkout validation](guest-checkout.md). The earlier counts above describe their dated baseline.
