# Historical review of the author’s original PRD

**Historical context only — not the current product baseline.** The [consolidated PRD](PRD.md) and its R01–R03 revisions are authoritative. In particular, forgotten-password recovery/Brevo is deferred and advanced architecture is retained until the implementation checkpoint. Earlier “next decision” and recovery-included notes below record the original discussion; they are not current instructions. A byte-preserved pre-update copy is available in [the planning archive](history/2026-11-12-before-consolidation/PRD_DRAFT_REVIEW.md).

Reviewed: 2026-11-12  
Source: `/Users/daraabpathania/Downloads/draf1 prd.pdf` — 29 pages, version 1.0 dated 2026-11-11. All pages were extracted and visually reviewed.  
Status: Interim review; the final assignment compliance review is still pending.

The PDF is source material for planning, not authorization to implement or blanket approval of every feature. Confirmed conversation decisions take precedence over older draft content. The source PDF remains unchanged.

## Strengths

The draft describes the full discovery-to-key journey, backend-driven content, seller selection, authenticated order/key access, payment failure, duplicate purchase prevention, responsive layouts, accessibility, validation, deployment, and submission documentation. Its acceptance criteria provide a useful starting point. Backend ownership checks, key isolation, and failed-payment behavior deserve priority when agreeing the final criteria.

## Conflicts and corrections

| Finding | Source | Treatment in workspace PRD |
| --- | --- | --- |
| Catalog includes DLC, in-game currencies/points, and software alongside games/cards/subscriptions. | §4.1, p.2 | Superseded by confirmed three-type catalog: 30 games, 15 gaming gift cards, 9 subscriptions. Do not silently add extra types. |
| Reviews are sample data, with review creation excluded; later sections permit verified buyers to submit reviews. | §4.7.3, p.6 versus §5.5, pp.16–17 | Resolved under D09: sample, read-only backend product reviews. Verified-purchase submission is excluded. Seller ratings remain a separate proposal. |
| Recommendations and recently viewed requirements appear twice with overlapping numbering and different persistence details. | §§5.2–5.6, pp.13–16 | Resolved recommendation scope under D10: curated backend selection shared by all users; activity-based personalization is excluded. Recently viewed remains a separate pending feature. |
| Detailed feature sections use mandatory wording for enhancements later labeled Should Have. | Homepage, search, accounts, recommendations; §8.2, p.26 | A feature must have one agreed priority and acceptance scope; candidate acceptance criteria do not make it mandatory. |
| “Advanced sorting/filtering combinations” are optional, but simultaneous filters are already described in the core store flow. | §4.3, p.3; §§8.2–8.3, pp.26–27 | Specify essential filter combinations separately from extra filter dimensions. |
| Duplicate/reordered sections obscure which version applies. | Sorting §4.3.1 after §4.7.5; repeated §4.7.2, §4.8.5 and §5 headings | Use a single canonical section for each feature in the final PRD; retain provenance in this review rather than duplicate competing text. |
| AI history may be Markdown or a link. | §§7.4–7.5, p.25 | User's stricter decision applies: full prompts and responses in actual Markdown; links are supplementary. |
| Seven-day deadline is stated without remaining effort. | §§8–9, pp.25–29 | Approximately four days remain per user; exact due time and usable hours still unconfirmed. |
| Product/offer counts, platform set, and region set are unspecified. | Catalog/product sections | Apply confirmed 54-product split, 6–7 seller offers per product, one-time randomization, Steam/PlayStation/Xbox, Global/Europe/United States. |

## Candidate requirements retained for sequential decisions

These are proposals imported from the author's PDF, not newly confirmed requirements. Existing confirmed overlaps remain confirmed.

| Area | Draft candidates | Source | Review guidance |
| --- | --- | --- | --- |
| Homepage | Promotional banners; category/platform shortcuts; recently viewed; rule-based recommendations; top games; best-selling cards; upcoming games; categories; footer. | §§4.2, 5, pp.3, 13–16 | Choose a bounded block set. Upcoming products need an explicit availability/purchase policy. Personalization is more work than curated backend collections. |
| Catalog/navigation | Responsive cards; category views; hierarchical menus; combined filters; clear filters; pagination. | §§4.3–4.5, pp.3–4 | Reuse the catalog journey where appropriate; choose navigation depth and page size. |
| Filters | Type, platform, region/country, price, publisher/brand, seller, availability, edition, OS, genre. | §4.4, p.4 | Recommend type/platform/region/price first; each additional filter needs coherent sample data and combination behavior. |
| Sorting | Most popular default, price ascending/descending, newest, best selling; backend popularity data. | §4.3.1, p.7 | Define metrics; distinguish curated popularity from actual sales. Define product price as an eligible offer's price. |
| Search | Header autocomplete with image/price, full results page, backend case-insensitive/partial/typo matching, result counts, filters/sorts/pagination, preserved query. | §4.6, pp.4–5 | Agree matching examples and autocomplete priority; avoid vague “attempts” as the only typo acceptance criterion. |
| Product | Image/title/category/edition, description, platform/region, delivery information, availability, type-specific attributes, related products. | §4.7, pp.5–8 | Agree minimum details; gift-card denomination and subscription duration matter even with shared key delivery. |
| Offers | Seller name/rating, currency/price, stock; display all offers; lowest-price-first; preserve chosen seller and price in cart; block unavailable purchases. | §4.7.2 and revised version, pp.6–8 | 6–7 offers and stable one-time randomization already confirmed. Seller pool, rating display, stock rules, currency, and price-change handling remain open. |
| Cart | Guest/buyer access; product/image/seller/unit price/quantity; quantity changes within stock; removal; totals; preserve cart through login/registration. | §4.8.1–2, p.8 | Guest cart and preservation through sign-in already confirmed. Quantity, persistence, registration transfer, and merge rules need decisions. |
| Payment | Simulated card/PayPal/wallet choices, no real credentials; email/order summary; optional fee; success/failure outcomes. | §4.8.3–4, pp.8–9 | Simulation already confirmed. Choose deliberate failure/retry behavior rather than surprising random failures; choose fees and currency explicitly. |
| Orders/keys | Confirmation, order number/items/sellers/total/status, key per purchased unit, copy control, durable order history, ownership, unique assignment, no keys on failure. | §4.8.5 (both versions), pp.9–10; §4.9.5, p.12 | Viewing keys and order history already confirmed; agree detailed acceptance criteria and stock/retry behavior. |
| Accounts | Email/password registration, duplicate prevention, safe login errors, logout, protected access; profile editing; password change/recovery with expiring single-use tokens. | §4.9, pp.10–12 | Basic account management already included but undefined. Password recovery adds delivery and token lifecycle work; determine priority explicitly. |
| Reviews | Sample display OR verified-purchase submission, one per buyer/product, 1–5 stars/text, aggregate counts, separate predefined seller ratings. | §4.7.3, p.6; §5.5, pp.16–17 | Resolve competing scopes before using associated acceptance/test requirements. |
| Responsive UX | Desktop/tablet/mobile purchase journey, adaptive menus/grids/filters, readable touch controls, no unintended horizontal overflow. | §6.1, pp.17–18 | Proposed quality scope; choose representative viewport targets. |
| Loading/errors | Skeletons, request feedback, empty states, retries, preserved input, stale-search response protection, duplicate-submit prevention, safe errors. | §6.2, pp.18–19 | Essential feedback versus extra polish should have separate priority. |
| Security | Hashed passwords, session/logout behavior, backend ownership checks/input validation, safe queries/content, rate limits, private keys, backend-validated payment, duplicate-purchase prevention. | §6.3, pp.19–21 | Preserve these as candidate release acceptance requirements; review absent-feature dependencies such as reset tokens/review content. |
| Accessibility | Contrast, non-color cues, alt text, keyboard purchase flow, focus, labels/errors, semantics, touch usability. | §6.4, pp.21–22 | Agree a practical acceptance checklist; draft contains no measured accessibility target. |
| Validation | Critical backend checks, one automated purchase E2E, responsive manual review, payment/stock/auth/request failure cases. | §6.5, pp.22–24 | Proposed validation scope; tests for optional reviews apply only if selected. No tests are being written during PRD work. |
| Deployment/docs | HTTPS frontend/backend/database, free tier, secret-free config, source/schema/sample data/tests, setup/testing docs, trade-offs/limitations, explainable implementation. | §§7–9, pp.24–29 | Hosting/stack architecture remains deferred; assignment deliverables stay mandatory. README and deployment verification need time reserved. |

## Preliminary check against assignment

The draft substantively covers all seven named functional parts: Store, Homepage, Search, Product page, Checkout, Authentication, and Eneba styling. It also covers the GitHub, deployment, conversation-history, and seven-day deliverables. These findings concern documented coverage, not implemented compliance.

The following assignment constraints are missing or need more explicit treatment in the author's PDF:

- React frontend, with TypeScript preferred; PHP or Go backend; REST or GraphQL; a database. The workspace PRD already records these constraints; stack choices remain deferred.
- Reply to the registration email with results. The workspace PRD records this submission instruction; sending is not currently authorized.
- Full Markdown history under the user's stricter requirement, with verified preservation/export instead of relying on an accessible link.
- A defensible interpretation of each homepage block's own backend service/database source. Both drafts repeat the requirement, but neither settles architecture, which is correctly deferred.

The final review must reconcile selected scope and concrete acceptance criteria against the original assignment after decisions are complete.

## Review decisions

**D09 confirmed:** Sample, read-only product reviews from backend data. No review submission, editing, deleting, moderation, or verified-purchase eligibility workflow in the initial version. Submission-related acceptance and testing criteria in the source draft no longer apply. Seller ratings remain separate.

**D10 confirmed:** Homepage recommendations are a curated backend selection shared by all users. No activity-based personalization or recommendation scoring. Original personalized-recommendation acceptance criteria no longer apply; recently viewed is separately undecided.

## Next decision

**D03a confirmed:** Promotional banner, category/platform shortcuts, curated Featured products, Top games, Featured gaming gift cards, shared header/footer. Live homepage revisited to compare hero, shortcut strip, and product collections. Additional recently viewed, lifestyle eGift-card, upcoming-game, and lower category sections remain reference-only.

D03b: proceed with the recommended single static backend-driven banner, based on interpreted acceptance explicitly communicated to the author. D03c confirmed: type, platform, region, price range, and game genre filters. Other source-draft filters are outside the selected initial set. D03d confirmed: Most popular, price low–high/high–low, title A–Z/Z–A, date newest/oldest; mock data accepted. Catalog-added date is the communicated working interpretation. Best selling is outside the selected set. D03e confirmed: multi-select categorical filters with OR within groups and AND across groups/price range, Clear filters, and search/sort alongside filters. D03f confirmed: live suggestions plus full results. Elasticsearch is a user-proposed technology for later architecture review. D03g confirmed: case-insensitive, partial-name, basic-typo search; exact-match relevance priority; clear empty state; no synonyms/semantic search. D03h confirmed: numbered catalog/search pages, 12 products per page. Next choose checkout currency.

## Additional confirmed settings scope

D11: Balanced settings modal with saved browser country preference, English-only interface, fixed demo rates for EUR/USD/GBP display prices, and immutable historical order currency/amounts. Country does not automatically filter products. D11a confirms checkout in the selected currency. Defaults, countries, and rate/rounding details remain open.

D12 confirmed: no simulated service fee; source-draft fee proposal excluded. Total is the sum of offer unit prices × quantities, subject to agreed currency rounding.

D13 confirmed: quantities up to available stock per seller offer; each purchased copy receives a separate simulated key. D14 confirms stock revalidation at checkout and blocking the whole purchase on any insufficient-stock item. Stock-decrement and concurrent-request rules remain open.

D14 confirmed: insufficient stock blocks the entire purchase, with affected-item feedback and cart retained for correction; no partial payment, completed order, or keys.

D15 confirmed: Card, PayPal, and Digital wallet choices, all explicitly simulated with no real payment credentials or financial transactions. D16 confirms default success with an explicit “Simulate failure” option. Detailed retry behavior remains pending.

D16 confirmed: success by default, explicit failure simulation, no random failures; failed payments retain the cart and deliver no keys. Next: key-delivery channels.

D17 confirmed: keys delivered in confirmation and order history/details only, with copying and retrieval across sign-ins; accessible only to the purchasing buyer. Email delivery excluded. Next: purchase/stock consistency.

D18 confirmed: successful purchases persistently consume selected seller-offer stock; failed payments consume no stock. Next: duplicate-checkout behavior.

D19 confirmed: repeated submissions of the same purchase attempt yield at most one order and no additional stock/key consumption. Technical retry mechanism deferred to architecture. Next: account-management scope.

D20 confirmed: view email, edit display name, access orders/keys, current-password-verified password changes, and forgotten-password recovery. Recovery is included scope; link delivery and token lifetime remain pending. Purchase/key email exclusion does not determine recovery delivery.

D21 confirmed: real-email reset-link delivery with expiring, single-use tokens; no public reset links. Provider and sender setup deferred to architecture. Purchase/key emails remain excluded. Next: reset-link lifetime.

D22 confirmed: password-reset links expire 30 minutes after issuance and are single-use. Next: whether registration requires email verification.

D23 confirmed: registration does not require email verification; verification emails and pending-verification flows excluded. Password recovery still uses real email. Next: cart persistence.

D24 confirmed: guest cart persists in the same browser across refresh/reopening; account carts are retrievable across devices. Next: guest/account merge rules at sign-in.

D25 confirmed: merge guest/account carts at sign-in; add identical-offer quantities, retain different sellers separately, and flag shortages. Approximately 10–12 substantive discovery decisions remain; routine details will be proposed for consolidated review.

D26 confirmed: Eneba-style seller comparison with featured purchase panel, recommended offers, and expandable Other offers; flat-list proposal superseded. Specific counts and ranking rules remain explicit reference-derived proposals. Workflow updated to 3–4 consequential questions per group. Product page and cart drawer/page revisited read-only; no real cart mutations or purchases performed.

D27–D30 confirmed: visual-only mock cashback with no balances/redemption; wishlist functionality excluded; backend-curated related products included. Secondary features may retain clearly distinguished visual mock/future presentation without full implementation; assignment-required functionality remains real and operational in the demo. Receipt: 2026-11-09 14:30; timezone provisionally Europe/Vilnius. Seven-day cutoff provisionally 2026-11-16 14:30; working target 2026-11-14–15 (2–3 days from planning). No implementation approval implied.

D31–D34 confirmed: static image gallery without trailers; Buy now adds the selected offer to the whole cart and proceeds to checkout; responsive/keyboard/loading-error quality baseline, critical backend tests and one automated purchase journey; stock-reset feature/procedure excluded. Persistent stock consumption remains in force.

D35–D38 confirmed: fixed-denomination/duration keys without recurring billing/top-ups/activation integrations; same-browser Recently viewed independent from curated recommendations; latest-reset-link-only plus session invalidation/fresh login and generic request confirmation; relevance-default search with explicit-sort precedence. Main feature decisions ready for consolidated routine-proposal and assignment-compliance review.
