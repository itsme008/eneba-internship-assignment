# Current PRD: assignment coverage review

Updated: 2026-11-12. Source: both pages of `Eneba Internship Assignment.pdf`, including the purchase-flow diagram previously reviewed. This assesses documented coverage, not working software. The author approved PRD consolidation, retained the advanced architecture until a two-implementation-day checkpoint, and deferred forgotten-password recovery/Brevo. No application implementation is authorized.

| Assignment reference | Current PRD coverage | Finding / required delivery evidence |
| --- | --- | --- |
| Store | §5: 54 products, five multi-select filters, seven sorts, name search and numbered pages. | Covered. PostgreSQL-backed query/count/page validation still required. |
| Homepage | §5.2: banner, shortcuts, shared curated collections, local recent history, shared chrome. | Covered using accepted interpretation: separate backend content services in one application/database. Manifest architecture retained; no hardcoded frontend block content. |
| Search anywhere, names, partial/typo matching | §5.4: global suggestions/results and accepted Minecraft cases. | Covered; PostgreSQL trigram search selected, Elasticsearch excluded. Global search includes checkout layout. |
| Product details and several sellers | §6: 6–7 offers, featured/recommended/other pattern, static gallery. | Covered; mock ratings/reviews remain clearly sample data. |
| Cart, payment, digital keys | §7: offer quantities, guest/account persistence/merge, all-or-nothing simulated checkout, stock, unique keys and replay. | Covered. Signed quotes, versioned currency handling and transaction/replay design retained; no premature simplification. |
| Registration/sign-in and protected checkout/history | §§4, 7: accounts, own orders/keys only. | Covered. Current-password-verified authenticated password change remains included. Forgotten-password recovery is not an explicit assignment requirement and is now deferred by R03. No real-email release dependency. |
| Eneba styling on every screen | §§6, 8: inspected references, responsive/keyboard core flow. | Covered as design intent; actual screen/interaction validation still required. |
| React frontend; TypeScript preferred | §9. | React selected; TypeScript proposed alongside build tooling. |
| PHP or Go backend; REST or GraphQL | §9. | PHP + Laravel confirmed; proposed REST satisfies the interface constraint. |
| Database | §9; DATABASE.md. | Neon PostgreSQL confirmed; no DB created yet. |
| Explain code and justify scope | §§3, 8–9. | Keep architecture/trade-off/setup/testing/known-limitation documentation. Retained sophistication does not replace explainability. |
| Seven days; reply to registration email | §3. | Provisional 2026-11-16 14:30 Europe/Vilnius cutoff; target 14–15 November. Checkpoint must leave release time. Submission email action not yet authorized/performed. |
| GitHub links and public free-tier URL | §8. | Mandatory; actual account eligibility, deployment and link accessibility still to verify. |
| Full prompts and responses | §8, AI_CONVERSATION_HISTORY.md. | User requires actual Markdown. Working snapshot exists; final export, earlier sessions and attachment inventory remain to complete. |

## Current reconciliations

The consolidated PRD replaces discovery alternatives with the latest confirmed requirements. Historical notes and full prior planning documents are preserved in [history/2026-11-12-before-consolidation](history/2026-11-12-before-consolidation/PRD.md). The historical author's draft and its review do not override current scope.

R01: one authoritative PRD. R02: signed quotes, versioned FX data, backend homepage manifest and other advanced mechanisms remain for the attempt; any checkpoint simplification requires a new author decision. R03: forgotten-password routes/pages/token storage/email/Brevo are deferred beyond initial submission, superseding historical D21–D22/D37 and the recovery part of D20. Registration/login/logout, profile and authenticated password change remain. No key/receipt emails, email verification, wishlist or stock reset are added.

## Remaining review and delivery limits

Exact numeric/UI defaults remain proposed in the design documents: SKU identity convention, seller pool/initial stock, sample FX/default country list, quote/session lifetimes/password bounds, display limits, ordinary-password-change session policy and tooling/version pins. Checkpoint scheduling/behind-schedule thresholds need acceptance. See ARCHITECTURE.md for expensive reversals and TASKS.md for checkpoint evidence/decision rules. No runtime implementation or tests exist yet; documentation coverage is not submission completion.
