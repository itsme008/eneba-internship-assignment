# Product Requirements Document

Project: Eneba-Style Digital Marketplace  
Owner and final decision maker: Daraab Pathania  
Version: 2.3 — mandatory sign-in before payment  
Updated: 2026-11-13  
Status: Product baseline approved, including the author's latest scope revisions. Implementation authorized on 2026-11-12; local buyer journey implemented. Release validation and deployment remain in progress.

## 1. Purpose, authority and sources

Deliver a compact, Eneba-inspired digital marketplace with a working discovery → seller comparison → cart → sign-in → authenticated simulated payment → digital-key journey. Mock catalog accuracy is acceptable; the platform's functionality and internal consistency are the priority.

This document is the authoritative **current product specification**. [ARCHITECTURE.md](ARCHITECTURE.md), [DATABASE.md](DATABASE.md), [API.md](API.md), and [TASKS.md](TASKS.md) describe its technical realization. They cannot silently override product requirements. Remaining design defaults are explicitly listed in section 10.

Source labels:

- **A — Assignment:** Explicit requirement from `Eneba Internship Assignment.pdf`, two pages, supplied at `/Users/daraabpathania/Downloads/Eneba Internship Assignment.pdf`.
- **U — User decision:** Confirmed project scope beyond or refining the assignment.
- **P — Proposal:** Engineering/default choice awaiting consolidated design review; not a new mandatory assignment requirement.

The assignment is source material, not permission to implement. The author required planning-only work until explicit implementation approval, received on 2026-11-12. Ask consequential scope questions in groups of 3–4; derive routine UI proposals from inspected references.

Historical discovery notes, earlier alternatives, the original decision log and superseded review findings are preserved in history/2026-11-12-before-consolidation (preserved in Git history and the private documentation backup). The original author draft review remains historical context (preserved in Git history and the private documentation backup), not current scope. Full conversation transcripts remain a separate mandatory artifact; history summaries do not replace them.

## 2. Latest approved revisions

| ID | Current decision | Supersedes |
| --- | --- | --- |
| R01 | Consolidate the PRD into one current baseline and preserve older decisions separately. | Discovery notes mixed with current requirements. |
| R02 | Retain the sophisticated architecture for now: signed checkout protocol, versioned currency handling, backend homepage composition, cart/replay mechanisms and current database design. Evaluate after two implementation days. | Review recommendation to simplify those mechanisms immediately — not approved. |
| R03 | Defer **all unauthenticated forgotten-password recovery**, real reset-email delivery beyond the initial submission. Brevo was initially deferred; R04 restores it for purchase-key emails only. Keep registration, login, logout, profile display-name editing and authenticated password change. | Recovery part of D20, D21–D22, D37 and provisional AR08/Brevo choice for the initial submission. |

Deferred recovery's 30-minute, newest-link-only/single-use and session-revocation requirements remain historical future-feature candidates, not initial release acceptance criteria. Do not replace real recovery with a public reset token, mock success, private inbox or reset page for forgotten-password recovery in the initial version.

R02 does not authorize implementation and does not preapprove automatic scope reductions at the checkpoint. Any later scope/contract change requires the author's decision.

## 3. Deadline and delivery constraints

- **A:** Submit within seven days of receiving the assignment and reply to the registration email with the results.
- **U:** Received Monday 9 November 2026 at 14:30. Provisional cutoff is Monday 16 November at 14:30 Europe/Vilnius, assuming the receipt used that timezone and no differing email instruction.
- **U:** Target delivery in 2–3 days from 12 November, approximately 14–15 November. Daily available working hours remain unspecified; this is a target, not a guaranteed estimate.
- **U:** Hold a progress checkpoint after two days of **actual implementation**, starting only after explicit implementation approval. It does not start when these documents are edited. Record implementation start time, elapsed calendar time and focused effort; review against remaining deadline, not an arbitrary percentage of tasks.
- **A:** The author must be able to explain the code and justify scope/architecture choices. The assignment permits shortcuts and favors depth over broad shallow coverage.

## 4. Users and access

**U — D01/D01a:** Two interactive roles: visitors and registered buyers. Seller identity/offers exist as demo data; seller accounts and seller/admin dashboards are excluded.

| Capability | Visitor | Registered buyer |
| --- | --- | --- |
| Homepage, catalog, search, filters/sorts, product/seller comparison | Yes | Yes |
| Prepare cart, choose seller, adjust quantities, remove items | Yes | Yes |
| Complete checkout and view purchased keys | Sign-in/registration required before payment | Yes, own purchases only |
| Persistent order history across devices | No | Yes |
| Account details, display-name editing, authenticated password change | No | Own account only |

Registration uses email/password. Sign-in is required before checkout/payment; guest cart contents are preserved and merged after authentication. Sender verification in Brevo is an operational prerequisite, not customer email verification. Customer email verification is excluded. Login/logout are included. Password change requires the current password. Forgotten-password recovery is deferred under R03. A user who cannot authenticate cannot change a password in the initial release.

## 5. Catalog and discovery

### 5.1 Product range and seller data

**U — D02a–g/D35:** Exactly 54 catalog products: 30 games, 15 gaming gift cards and 9 subscriptions. Cover Steam, PlayStation and Xbox, with Global, Europe and United States activation-region labels. Each product has 6–7 offers from distinct sellers: 324–378 offers total.

Randomize demo offers once during initial data preparation and persist them. Do not rerandomize prices/sellers on page visits or redeployment. Actual purchases reduce stock; stable demo data does not mean stock is frozen.

All types use simulated non-activatable keys. Gift cards have fixed face denominations/currencies; subscriptions have fixed durations. No wallet top-ups/balances, renewals, recurring billing, actual key verification or external activation integrations. Gift-card face currency is distinct from selling currency.

### 5.2 Homepage

**A:** Several different backend-driven content blocks; frontend displays received content rather than hardcoded block content.

**U — D03a–b/D10/D36:** Include shared header/footer, a promotional banner with manually selected slides (R05), category/platform shortcuts, Featured products, Top games, Featured gaming gift cards, and Recently viewed when local history exists.

Featured products are a backend-curated selection shared by all users. Recently viewed uses same-browser history only, hydrated with current backend product data. It does not personalize recommendations and does not synchronize browsing history across devices.

**U — AR02 interpretation:** Each block has its own backend content module/service, within one modular application sharing PostgreSQL. Separate deployments/databases are not required by the chosen interpretation of the assignment. R02 retains the backend section manifest/composition design.

### 5.3 Store, filters, sorting and pagination

**A:** Browse all marketplace products, filter, change ordering and search by name.

**U — D03c–e/D03h:** Responsive product cards show image, title, platform, region and starting price. Five filters: type, platform, region, price range and game genre. Categorical filters support multiple selection: OR within a group, AND across groups/price/query. Clear filters resets selected filters/range while keeping query/sort. Genre applies to games; exact interaction defaults are in section 10.

Seven catalog sorts: Most popular (default), Price low–high/high–low, Title A–Z/Z–A, Date newest/oldest. Popularity/dates/prices may be stable mock data. Date means catalog-added time; starting price uses the cheapest eligible seller offer. Numbered catalog and search pages display 12 products per page.

### 5.4 Global search

**A:** Product-name search is available throughout the application, including the checkout layout; support partial names and typos where possible.

**U — D03f–g/D38:** Provide live name/thumbnail/starting-price suggestions and a full filterable/sortable results page. Selecting a suggestion opens the product. Submitting or selecting View all opens results.

Search is case-insensitive and supports partial names/basic typos. Acceptance examples: `MINECRAFT`, `mine`, and `mincraft` match a seeded Minecraft product; an unrelated query gives a clear no-results state. Exact matches rank before typo-only matches under relevance. Search defaults to relevance; unqueried catalog defaults to popularity; explicit chosen sort overrides relevance. No synonyms or semantic search.

**U — AR05:** Use PostgreSQL search/trigram matching; Elasticsearch is excluded.

## 6. Product page and seller selection

**A:** Show product details and offers from several sellers so buyers can compare and choose.

**U — D09/D26/D29/D31:** Follow inspected Eneba layout: static image gallery, title/details, platform/activation-region and delivery information, featured purchase panel, Recommended offers and expandable Other offers. Seller identity/avatar/mock rating and price are visible; unavailable offers cannot be purchased. Selecting an offer preserves that seller and offer through the cart.

Recommended/featured selections are backend demo designations, not a production ranking engine. A repeated featured panel references the same offer/inventory as its row. Include sample read-only product reviews, distinct from seller ratings, and a small backend-curated related-products collection. No review submission/moderation or personalized related-product engine.

**U — D27/D28/D30:** Cashback is visual-only mock content with no balance/earning/redemption or effect on totals. Wishlist functionality is excluded. Secondary deferred features may retain clearly disabled/demo visual presentation, but no fake success, unusable data collection, unsupported refund promises or real external endorsements. This permission never substitutes for working assignment-required behavior.

## 7. Cart, currency and purchase

### 7.1 Persistence and merging

**U — D24/D25:** Guest cart persists through refresh/reopening in the same browser. Buyer cart persists with the account across devices. At login/registration, merge guest and account carts; add quantities for identical seller offers and keep different sellers separate. Preserve shortages for correction. Repeating the same transfer must not add quantities twice.

**U — D13/D32:** Quantities can increase up to current seller-offer stock; remove controls are included. Buy now adds the selected offer to the existing cart and proceeds with **whole-cart** checkout. No isolated quick-purchase flow.

### 7.2 Settings and money

**U — D11/D11a/D12:** Eneba-style settings modal: browser-saved preferred country, English-only interface, EUR/USD/GBP choices with fixed demo rates. Country is a preference only; it does not automatically filter products or determine activation compatibility/tax. No localization.

The chosen currency applies throughout discovery, cart, simulated checkout and the resulting order. Past orders retain their original currency and amounts after preference changes. No service fee: total is selected unit prices × quantities, summed. The architecture's versioned fixed-rate handling and signed quotes remain selected under R02; exact values/rounding defaults are technical proposals, not live market rates.

### 7.3 Checkout, stock and payment

**A / U — R06:** Sign-in is required before payment, as specified by the assignment. The user explicitly reversed R04 guest checkout. Visitors may browse and prepare a persistent cart, then sign in or register; merge that cart before reviewing a signed-in payment quote. Anonymous quote/payment requests are rejected server-side. Order history remains authenticated. Payment remains simulated.

**U — D14–D16/D18/D19:** Checkout validates current backend availability. Any insufficient-stock line blocks the entire purchase, identifies affected items and preserves the cart. No silent seller substitution, quantity reduction or partial purchase.

Payment choices are Card, PayPal and Digital wallet, all clearly simulated, with no real credentials collected. Success is the default; a labeled Simulate failure option deliberately exercises decline. Failure creates no completed order/keys, consumes no stock and retains cart. A successful purchase persistently consumes each selected offer's purchased quantity.

Repeated submissions/network retries of the same purchase attempt return the existing outcome, creating at most one order without extra stock/key consumption. A deliberate new purchase is separate. Order/items/stock/keys/cart changes must be atomic. No stock-reset or replenishment feature/procedure is included; initial data seeding is distinct from reset.

### 7.4 Orders and digital keys

**U — D17 / R06:** Show confirmation and owner-only order history/details. Each purchased unit receives a distinct simulated key; buyers can view/copy keys immediately and retrieve the same keys after signing out/in. Send the same keys to the immutable account purchase email through Brevo HTTPS after the order commits. Mail failure preserves the completed order and keys and exposes an authorized retry. Preserve existing historical guest orders/keys and their session-proof linking path; no new guest purchases are permitted.

Historical order details include order reference/date, purchased product/seller/quantity snapshots, original currency/prices/total and completed status. Failed attempts are not completed purchases. No refunds, key reassignment or external activation.

## 8. Quality, acceptance and deliverables

**U — D33:** Responsive desktop/mobile buyer journey, keyboard-accessible core controls, understandable loading/empty/error/retry states, critical backend tests and one automated successful buyer journey. Animation polish is lower priority.

Initial-submission acceptance:

1. Browse the 54-product catalog; combine filters/sorts/pages; exercise exact/partial/typo/no-match global search.
2. Every homepage block receives backend content; local recently viewed remains separate from curated Featured products.
3. Compare seller offers, choose one, prepare a guest cart, sign in or register, merge the cart exactly once, confirm price and pay as an authenticated buyer.
4. Purchase multiple units in the selected currency; receive the correct distinct simulated keys; retrieve the same owner-only order later.
5. Deliberate decline/shortage creates no partial order, keys or stock consumption. Replayed requests and competing last-stock purchases do not oversell or duplicate fulfillment.
6. Register/login/logout, edit display name and change password with the correct current password. Forgotten-password recovery remains deferred. Purchase-key email delivery is now required under R04 and needs real sender/provider verification.
7. Verify account history cannot be accessed as a guest and orders/keys cannot be accessed by another guest session or buyer, passwords are hashed, inputs are backend-validated, and private keys do not leak through public API/logs/cache.
8. Complete one automated purchase E2E and meaningful PostgreSQL-backed business-rule tests; manually check mobile/desktop and keyboard behavior.
9. Validate the public deployed app, not only local builds. Accounts/orders/stock survive restart and no destructive seeding occurs on deploy.
10. Provide GitHub project link(s), free-tier public URL, clear setup/testing/trade-off/known-limitation documentation, and full AI prompts/responses in actual Markdown. Reconcile earlier sessions/attachments and export after the final response; a share link alone is insufficient under the user's requirement.

### Assignment traceability

| ID | Assignment requirement | Current coverage |
| --- | --- | --- |
| A01 | Store: full catalog, filters, sorting/name search | §§5.1, 5.3–5.4 |
| A02 | Different backend-driven homepage blocks | §5.2; AR02 interpretation |
| A03 | Global product-name search, partial/typo handling | §5.4 |
| A04 | Details and multiple seller offers | §6 |
| A05 | Cart, simulated/test payment, digital key | §7 |
| A06 | Registration/sign-in, protected checkout/history | §§4, 7.3–7.4 |
| A07 | Eneba look/feel across screens | §§6, 8; inspected reference screenshots/site |
| A08 | Homepage/search → product → cart → authenticated checkout → payment → order/key diagram | §§5–8 |
| A09 | GitHub link(s) | §8 acceptance 10 |
| A10 | Public free-tier deployment | §8 acceptance 9–10 |
| A11 | Full agent prompts and responses | §8 acceptance 10; Markdown stricter user requirement |
| A12 | Explain code and justify scope | §§3, 8; delivery documentation |
| C01–C02 | Seven-day submission/reply and working target | §3 |
| C03–C05 | React (TypeScript preferred), PHP/Go, REST/GraphQL, database | §9 |
| C06 | Planning until explicit implementation approval | §§1–2 |

## 9. Technical decisions and retained architecture

**A:** React frontend; TypeScript preferred. PHP or Go backend using REST or GraphQL; any database.

**U:** PHP + Laravel, PostgreSQL with trigram search, one modular application with separate homepage services sharing the database, Render application serving React assets/API, Neon persistence. Retain the current advanced architecture for the attempt and reassess after two implementation days. Forgotten-password recovery remains deferred. R04 restores Brevo HTTPS for transactional purchase-key delivery, adding sender/account verification to release checks.

**P:** React + TypeScript/Vite build, REST JSON, cookie sessions/CSRF, signed quote protocol, exact decimal/integer money handling, versioned FX sets, backend manifest/renderers, cart revisions/transfer receipts, transactional attempt replay, encrypted simulated keys and auth_version strategy are specified in design documents. R02 rejects premature simplification; it does not automatically approve every proposed default. Record remaining choices before code.

## 10. Remaining defaults and two-day review

These are engineering proposals, not unresolved competing product requirements: one platform/region-specific SKU per product row; 8–10 reusable mock sellers; 10–30 initial offer stock; two recommended/one featured offer; up to 10 recent IDs/five visible cards; country list/default Lithuania/EUR/English; sample FX values and unit rounding; 10-minute quote lifetime; session/password policies; URL/revision conflict behavior; final tooling/version pins.

Implementation default: an ordinary authenticated password change verifies the current password, ends all sessions and requires fresh login. This is a recorded engineering default under the implementation authorization, not an assignment requirement or an earlier explicit product selection. Recovery remains deferred.

The [two-day checkpoint in TASKS.md](TASKS.md#two-day-implementation-checkpoint) evaluates working marketplace evidence, tests, deployed persistence, remaining effort and deadline. Signed quotes, FX, homepage composition and other advanced mechanisms are reconsideration candidates only if significantly behind, and only by author approval. Data integrity, authorization, unique key delivery and submission artifacts remain protected. Implementation started at 2026-11-12 19:09:32 UTC; the 48-hour checkpoint is due 2026-11-14 19:09:32 UTC. No checkpoint review has occurred.

## 11. Out of initial scope and future candidates

**Deferred:** Forgotten-password recovery, reset-token tables/routes/pages, recovery emails; future recovery restoration needs a separately approved task and migration. Purchase-key emails and Brevo setup are in scope under R04. Preserve earlier token/lifecycle requirements in historical records for that review.

**Excluded:** Seller/admin accounts/dashboards, wishlist functionality, real payments/activation, subscription lifecycle/renewals, wallets/top-ups, review submission, personalized recommendation tracking, localization, playable trailers, refunds/disputes, extra product types, actual cashback accounting and stock-reset features. Frontend-only mock catalog behavior is never a substitute for the assignment-required backend.

## 12. Change and approval record

- 2026-11-12: Original product decisions and PRD approved; technology/deployment decisions subsequently confirmed. See the preserved decision history (preserved in Git history and the private documentation backup).
- 2026-11-12: R01–R03 explicitly approved: consolidated PRD; retained architecture with a two-implementation-day review; deferred forgotten-password/Brevo scope. Updated all active planning documents consistently. No application implementation authorized.

- 2026-11-12, subsequent authorization: author said “lets start implementing”; local buyer journey implemented and validation evidence recorded.


### R04 — Historical guest purchase and transactional email (guest payment superseded by R06) (13 November 2026)

Explicitly authorized by “lets implement” after discussion of the guest-email → simulated payment → onscreen/email keys → optional account flow. Supersedes sign-in-at-checkout and no-key-email decisions only; R03 still excludes password recovery. Mail sender/API credentials and real inbox delivery must be verified before this feature is release-ready. [Brevo setup](BREVO_SETUP.md); [change and validation evidence](VALIDATION.md).


### R05 — Account dashboard and storefront refinement (13 November 2026)

The user approved proceeding after the live Eneba comparison. Add an account-specific header, avatar dropdown, sidebar, overview with profile summary and latest real purchases, separate profile/password settings, and a searchable, paginated keys library. The library lists actual purchase items and links to the existing authorized order/key view; keys are not returned in list responses. This supersedes the earlier confirmation/history-only presentation restriction. Guest orders enter the account library only through the existing approved claim flow.

Storefront updates include a favorites preview entry, country flag, category/platform icon navigation, manually selected backend-provided promotions, genre tiles, and expanded footer. Existing 54-product scope and supported platforms/types remain unchanged. Balance, cashback, loyalty, favorites, support tickets and newsletter remain explicitly marked previews, with no transactions or data collection. External footer links are labeled as external. Login history, connected apps and MFA remain deferred. No database schema change is required for these views.


Fuzzy-search update: keys-library product-name search now uses PostgreSQL word similarity (threshold > 0.3 for queries of at least three characters), alongside literal matching. Exact/prefix/substring matches rank ahead of approximate results; order-number matching remains literal. All results stay scoped to the signed-in buyer. Catalogue and live suggestions already support trigram typo matching.


Global-search refinement: header suggestions appear after the first non-whitespace character (220ms debounce, up to six results). One/two-character queries use literal partial-name matching; queries of at least three characters also use trigram typo matching. Relevance ranks exact, prefix and substring matches before approximate results. Loading feedback is shown while fetching. Keys-library fuzzy search is retained.


### R06 — Restore assignment-required sign-in before payment (13 November 2026)

The user explicitly chose the assignment's sign-in-before-payment requirement over real-site guest parity. This supersedes R04 for new checkout/payment behavior. The frontend requires authentication before checkout and both quote/payment endpoints enforce it, with service-level guards as defense in depth. Guest browsing/carts, exactly-once merge, authenticated purchase emails, dashboard and library remain in scope. Preserve all historical guest records/keys and the original session-proof claim/read mechanism; no destructive rollback/reseed. The earlier assignment deviation is resolved for new purchases.
