# Implementation and release plan

Updated: 13 November 2026. Current scope is PRD R06: guest browsing/cart preparation, mandatory registration/sign-in before payment, authenticated order history/library, and purchase-key emails. Earlier task estimates and decision history are preserved in [history](history/2026-11-13-before-release-consolidation/TASKS.md).

## Current status

| Workstream | Status | Evidence / remaining work |
| --- | --- | --- |
| Laravel/React/TypeScript/PostgreSQL environment | Implemented and released | Locked dependencies; Composer platform checks pass. |
| Schema, 54 demo products and 6–7 sellers per product | Implemented and released | Stable seed/inventory and constraint tests. |
| Catalogue, filtering, sorting and pagination | Implemented and released | PostgreSQL tests and connected-browser checks. |
| Global partial/fuzzy search and live suggestions | Implemented and released | First-character matching, longer-query typo handling, keyboard controls; shared search in storefront and account headers. |
| Independent backend homepage blocks | Implemented and released | Eight manifest sections, including backend-owned navigation and genre tiles; independent disable/content tests. |
| Product, seller comparison, static gallery, sample reviews, related products | Implemented and released | Real backend data and reference-inspired layout. |
| Registration/login/logout/profile/password change | Implemented and released | Session/CSRF/ownership tests; password recovery deferred. |
| Guest/account carts, merge, Buy now | Implemented and released | Guest persistence, revision/merge receipts, existing whole-cart behavior. |
| Authenticated payment, stock, distinct keys and replay | Implemented and released | Server/service auth gates, atomic purchase, decline/rollback/contention checks. |
| Purchase email | Verified live | Author confirmed live order email and onscreen key match. |
| Account dashboard, keys library and visual previews | Implemented and released | Actual purchase summaries, owner-scoped fuzzy search; unsupported features clearly labeled previews. |
| Eneba visual refinement | Local pass complete | Bundled Metropolis, reference palette, spacing, cards, header/banner/rails, product/cart/checkout/account layouts. Final user visual review welcome. |
| Local backend/build/format checks | Passing | Latest dated validation reports carry exact counts. |
| Connected-browser regression | Exercised | Authenticated cart → decline → success → logout/login/key retrieval, desktop/mobile and search checks. |
| Repeatable Playwright runner | Passing in CI | All three tests pass, including authenticated purchase and mobile filtering. |
| Production Docker image | Built in CI; running live | Free Render Docker service passes readiness and purchase checks. |
| Render + Neon | Deployed and verified | See hosted-release report for HTTPS, CSRF, purchase/email and restart evidence. |
| GitHub publication | Published privately | itsme008/eneba-internship-assignment; evaluator access still needs arrangement. |
| Documentation/history/submission | Final author review pending | Five threads exported; 23 inline image binaries privately preserved and hashed. Final export/second backup/submission approval remain. |

## Next actions, in order

1. Arrange evaluator access to the private GitHub repository and complete author visual/code review.
2. Refresh all conversation exports after the final saved response and preserve a second backup outside this workstation.
3. Finalize the submission draft and obtain approval before sending it to Eneba.

Release evidence: [hosted verification](reviews/hosted-release.md), [release checklist](RELEASE_CHECKLIST.md).

## Deadline and checkpoint

Assignment received 9 November 2026 at 14:30; provisional deadline 16 November at 14:30 Europe/Vilnius. Preferred target remains 14–15 November. Implementation began 12 November at 21:09:32 Europe/Vilnius. The agreed two-day architecture checkpoint is due 14 November at 21:09:32 Europe/Vilnius. It has not been declared complete; no advanced mechanism was silently simplified. Review deployability earlier if necessary.

## Limits

No real payments, activatable keys, actual wallets/cashback/loyalty, functioning favorites/support, password recovery, seller/admin dashboards or localization. Retain signed quotes, precise fixed FX, immutable receipts and encrypted keys. Preserve historical guest orders while preventing new anonymous payments. Never reset stock or seed destructively on startup.

See [authenticated-checkout evidence](reviews/authenticated-checkout-restored.md), [account/storefront evidence](reviews/account-storefront-update.md), and [release checklist](RELEASE_CHECKLIST.md).
