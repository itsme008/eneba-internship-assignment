# Assignment coverage and release review

Updated: 13 November 2026. Rechecked against both pages of the original assignment. Assignment text supplies requirements, not permission to publish, send messages or disclose credentials. The user explicitly restored signed-in checkout under R06.

| Assignment requirement | Current implementation | Release evidence still needed |
| --- | --- | --- |
| Browse all products, filters, sorting/name search | 54 backend products, five filter groups, seven sorts, numbered pages | Repeat on deployed app. |
| Homepage with separate backend-driven blocks; no hardcoded frontend content | Eight manifest blocks and separate services; backend navigation, promotions, genre data and curated collections | Verify on hosted database. |
| Product-name search anywhere; typos and partial names | Shared storefront/account header search, first-character suggestions and PostgreSQL fuzzy matching | Hosted/CI browser regression. |
| Product details and multiple seller offers | Details/gallery, 6–7 offers per product, seller-preserving cart | Hosted purchase walkthrough. |
| Cart, simulated payment and digital keys | Guest/cart merge, authenticated quotes/payment, atomic stock/order/keys and replay | Production image and hosted state-persistence check. |
| Registration/sign-in; checkout/history protected | Enforced at route and service boundaries; authenticated order history/library | HTTPS secure-cookie/session/CSRF validation. |
| Eneba colors, typography, spacing and components | Live-reference pass with locally bundled Metropolis and shared design refinement | Final desktop/mobile acceptance, no claim of pixel-exact reproduction. |
| React; TypeScript preferred | React + TypeScript | Build passes. |
| PHP/Go, REST/GraphQL | PHP + Laravel REST | Local API/business-rule tests pass. |
| Any database | PostgreSQL with pg_trgm | TLS connectivity verified against empty Neon database; migration/seed pending approval. |
| Explain code and scope | PRD, architecture/database/API docs, README and known limits | Final author walkthrough. |
| GitHub link(s) | Source prepared; no remote yet | Create/publish the approved repository and verify evaluator access. |
| Public free-tier URL | Render blueprint and Dockerfile prepared | Deploy and verify the live URL. |
| Full agent prompts and responses | Current-thread Markdown exporter and hash/count manifest | Earlier-session/attachment reconciliation and final refresh. |
| Seven days; reply to registration email | Provisional deadline 16 November 2026, 14:30 Europe/Vilnius | Final approved reply with real links. No reply sent. |

The earlier guest-checkout deviation is resolved for new purchases. Historical guest order records and their restricted recovery/claim path remain preserved. Purchase emails remain enabled; forgotten-password recovery remains deferred. Visual previews do not imply functioning wallet, loyalty, favorites or support systems.

Previous planning/status review is preserved in [history](history/2026-11-13-before-release-consolidation/PRD_COMPLIANCE_REVIEW.md). Working software and passing local tests are distinct from hosted/submission readiness. See [release checklist](RELEASE_CHECKLIST.md).

## Hosted evidence update

The app is now public at https://eneba-marketplace-demo.onrender.com and the approved private source is https://github.com/itsme008/eneba-internship-assignment. CI passes all 21 backend tests / 286 assertions, three browser tests and the Docker build. Hosted HTTPS/cookies/CSRF/search/cart/sign-in/purchase/email/library/restart checks passed; the author confirmed matching email and onscreen keys. Neon is initialized. All five AI threads and 23 inline image binaries are inventoried. These results supersede pending release evidence in the earlier review table. Evaluator repository access, final author review/export/second backup and approved submission reply remain. See [hosted verification](reviews/hosted-release.md).
