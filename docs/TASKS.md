# Implementation and release plan

Updated: 13 November 2026. Current scope is PRD R06: guest browsing/cart preparation, mandatory registration/sign-in before payment, authenticated order history/library, and purchase-key emails. Earlier task estimates and decision history are preserved in [history](history/2026-11-13-before-release-consolidation/TASKS.md).

## Current status

| Workstream | Status | Evidence / remaining work |
| --- | --- | --- |
| Laravel/React/TypeScript/PostgreSQL environment | Complete locally | Locked dependencies; Composer platform checks pass. |
| Schema, 54 demo products and 6–7 sellers per product | Complete locally | Stable seed/inventory and constraint tests. |
| Catalogue, filtering, sorting and pagination | Complete locally | PostgreSQL tests and connected-browser checks. |
| Global partial/fuzzy search and live suggestions | Complete locally | First-character matching, longer-query typo handling, keyboard controls; shared search in storefront and account headers. |
| Independent backend homepage blocks | Complete locally | Eight manifest sections, including backend-owned navigation and genre tiles; independent disable/content tests. |
| Product, seller comparison, static gallery, sample reviews, related products | Complete locally | Real backend data and reference-inspired layout. |
| Registration/login/logout/profile/password change | Complete locally | Session/CSRF/ownership tests; password recovery deferred. |
| Guest/account carts, merge, Buy now | Complete locally | Guest persistence, revision/merge receipts, existing whole-cart behavior. |
| Authenticated payment, stock, distinct keys and replay | Complete locally | Server/service auth gates, atomic purchase, decline/rollback/contention checks. |
| Purchase email | Verified locally | Brevo accepted; user confirmed inbox and matching order/email key. Hosted configuration still required. |
| Account dashboard, keys library and visual previews | Complete locally | Actual purchase summaries, owner-scoped fuzzy search; unsupported features clearly labeled previews. |
| Eneba visual refinement | Local pass complete | Bundled Metropolis, reference palette, spacing, cards, header/banner/rails, product/cart/checkout/account layouts. Final user visual review welcome. |
| Local backend/build/format checks | Passing | Latest dated validation reports carry exact counts. |
| Connected-browser regression | Exercised | Authenticated cart → decline → success → logout/login/key retrieval, desktop/mobile and search checks. |
| Repeatable Playwright runner | Prepared, not executed | Updated scenarios and GitHub workflow; run before submission. |
| Production Docker image | Prepared, not built | No Docker engine locally; CI builds the image after tests once published. |
| Render + Neon | Awaiting configuration and release approval | User has Render account and Neon string; secrets go into ignored .env.deploy. No hosted app claimed. |
| GitHub publication | Pending approval | Connected account works; CLI authentication works with authorized network access. Repository not yet created. |
| Documentation/history/submission | In progress | Current-thread Markdown export works; earlier-session/attachment inventory and final links still need reconciliation. |

## Next actions, in order

1. Finish remaining local UI/error/keyboard checks; preserve screenshot/validation evidence and keep backend checks green.
2. Run CI/Playwright and production image validation after approved repository publication.
3. Read-only Neon connectivity/schema check after the user saves DB_URL in .env.deploy. Prepare a concrete Render configuration; ask approval before provisioning/publishing or mutating the hosted database.
4. Apply one controlled migration/seed to the intended empty demo database; configure stable production secrets and deploy free-tier Render service.
5. Verify HTTPS/secure sessions/CSRF, deep routes, auth gate, purchase/email/library and durable state across restart.
6. Complete [release checklist](RELEASE_CHECKLIST.md), conversation inventory and [submission draft](SUBMISSION_DRAFT.md). Obtain final approval before sending the registration-email reply.

## Deadline and checkpoint

Assignment received 9 November 2026 at 14:30; provisional deadline 16 November at 14:30 Europe/Vilnius. Preferred target remains 14–15 November. Implementation began 12 November at 21:09:32 Europe/Vilnius. The agreed two-day architecture checkpoint is due 14 November at 21:09:32 Europe/Vilnius. It has not been declared complete; no advanced mechanism was silently simplified. Review deployability earlier if necessary.

## Limits

No real payments, activatable keys, actual wallets/cashback/loyalty, functioning favorites/support, password recovery, seller/admin dashboards or localization. Retain signed quotes, precise fixed FX, immutable receipts and encrypted keys. Preserve historical guest orders while preventing new anonymous payments. Never reset stock or seed destructively on startup.

See [authenticated-checkout evidence](reviews/authenticated-checkout-restored.md), [account/storefront evidence](reviews/account-storefront-update.md), and [release checklist](RELEASE_CHECKLIST.md).
