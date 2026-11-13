# Product Requirements Document — Eneba Internship Marketplace

Status: Product requirements approved — application implementation not authorized  
Updated: 2026-11-12  
Project: Eneba-Style Digital Marketplace  
Product owner / final decision maker: Daraab Pathania

Approval: Daraab approved the PRD in this conversation on 2026-11-12 and authorized system architecture followed by database design. Confirmed decisions D01–D38 and the U decision log form the approved product baseline. Older discovery notes are retained as history; a superseded “open” note does not override a later confirmed decision. Explicitly marked routine proposals remain distinguishable from confirmed decisions. See [assignment coverage review](PRD_COMPLIANCE_REVIEW.md) and [architecture draft](ARCHITECTURE.md).

## 1. Purpose and working agreement

Define a feasible marketplace submission before architecture, database design, development planning, or implementation. No application code or implementation is authorized. Ask meaningful scope/complexity questions in groups of 3–4, following the author’s updated workflow. Infer routine UI patterns from the reference and document assumptions/proposals explicitly. Record product decisions only after the author agrees. The author supplied PRD sections 1–3 and subsequently the complete 29-page draft on 2026-11-12. The full draft has been reviewed; new feature details are retained as proposals for sequential decisions, not blanket approvals. Other prior discussions must not be inferred.

## 2. Sources and requirement labels

Primary source: `Eneba Internship Assignment.pdf`, two pages, titled **Eneba Intern Assignment 2027**, supplied at `/Users/daraabpathania/Downloads/Eneba Internship Assignment.pdf`. Both pages, including the purchase-flow diagram, were reviewed.

Additional source: author’s `draf1 prd.pdf`, version 1.0 dated 2026-11-11, 29 pages. See [draft review and proposal inventory](PRD_DRAFT_REVIEW.md) for source locations, conflicts, and candidate acceptance requirements. Confirmed conversation decisions supersede conflicting older draft content.

- **A — Assignment:** Explicit requirement or constraint from the PDF.
- **U — User:** Explicit instruction or agreed product decision from the author.
- **P — Proposal:** Recommendation awaiting the author's decision.
- **Q — Open:** Ambiguity or question requiring resolution.

The PDF describes the eventual submission; it does not override the user's planning-only instruction. Examples in the PDF are distinguished from mandatory behavior below.

### Design references reviewed (2026-11-12)

The author supplied five desktop homepage screenshots in this conversation. These show lower homepage sections, rather than the complete header/hero. Screenshots remain conversation attachments; standalone image files have not been saved in the workspace.

Live reference review also covered the [homepage](https://www.eneba.com/), [catalog](https://www.eneba.com/store/all), and a [representative product page](https://www.eneba.com/steam-rivals-of-aether-ii-pc-steam-key-global). Catalog and product layouts were visually inspected in the browser. Observations describe the reference, not approved project features:

| Surface | Observed reference patterns | Scope implication / pending decision |
| --- | --- | --- |
| Shared header | Logo, prominent search, category navigation, account and cart controls. | Supports discovery and purchase navigation; exact navigation is pending. |
| Homepage screenshots | Alternating purple section backgrounds, white headings, horizontal product rows, dark product cards, portrait cover art, platform strips, region labels, prominent “from” prices, outlined section links. Categories and a dark multi-column footer are visible. | Establishes visual direction. Exact sections, content types, and number of blocks remain D03 decisions. |
| Catalog | Left filter sidebar; result count and sorting above a product grid. Reference filters include price, country, type, operating system, platform, genre, and region. | Select a smaller useful filter set under D03; the live filter inventory is not a requirement to reproduce. |
| Product page | Cover art and title, platform/activation-region and digital-delivery details, gallery, prominent purchase panel with yellow action, and seller-offer rows showing seller identity, rating, price, and purchase controls. | Required seller comparison should use this visual reference; ratings, galleries, and featured-offer logic remain optional proposals. |
| Reference extras | Cashback, wishlists, personalized recommendations, preorders, newsletters, payment-brand strips, external trust badges, app-download links, and promotional widgets. | Reference observations only. Do not infer approval to implement these or to display unsupported service/trust claims. |

**P:** Match Eneba's shared visual language across the required journey while limiting functional depth to agreed features. Exact typography, dimensions, responsive behavior, and visual acceptance criteria remain open under D07.

**Q:** Search interactions, authentication screens, populated cart, checkout, and order/key screens have not yet been reviewed. Review accessible references when those decisions arise; request targeted screenshots only for inaccessible states. No cart changes, checkout actions, or account changes were made during reference review.

## 3. Product overview, problem, and goals

**A — Product overview:** A web-based digital marketplace inspired by Eneba, supporting product discovery, comparison of multiple seller offers, and digital-product/key purchases. No real money is involved.

**P — Product statement (author draft, 2026-11-11):** Create a compact, realistic marketplace demo where buyers can complete the journey from product discovery to receiving a simulated digital key, with transparent seller offers and responsive, Eneba-inspired styling.

**P — User problem (author draft):** Buyers need a straightforward way to discover video games and gaming-related products, compare prices from different sellers, and complete purchases through a convenient and reliable shopping experience.

**P — Product goal (author draft):** Deliver a functional marketplace demonstrating product discovery, seller comparison, user authentication, and an end-to-end purchasing experience, including simulated payment and digital key delivery.

### Success criteria

**A:** Build a marketplace web application matching Eneba's look and feel. The author must understand and explain the code and justify scope choices. Shortcuts are permitted; depth is preferred over many shallow features.

**P:** Prioritize a coherent, demonstrable buyer journey with reliable purchase and key delivery, supported by a deliberately small catalog. Product quantities and depth remain undecided.

Success means satisfying the assignment baseline below, completing agreed acceptance criteria, providing an accessible deployed demo and repository, and supplying full AI prompts and responses in Markdown. Detailed acceptance criteria require product decisions and are not yet approved.

## 4. Time and constraints

| ID | Source | Constraint |
| --- | --- | --- |
| C01 | A, p.1 | Complete within seven days of receiving the assignment; reply to the registration email with the results. |
| C02 | U | Three days have already elapsed; approximately four days remain. Received Monday 2026-11-09 at 14:30 per author. Seven-day cutoff is provisionally 2026-11-16 at 14:30, assuming Europe/Vilnius time and no differing email instruction; timezone not explicitly confirmed. Plan against a realistic 2–3 day delivery window from 2026-11-12 (target 2026-11-14–15), retaining remaining time as buffer. Daily usable hours remain unspecified. |
| C03 | A, p.2 | React frontend; TypeScript preferred, not mandatory. |
| C04 | A, p.2 | PHP or Go backend; REST or GraphQL. Choices deferred to architecture. |
| C05 | A, p.2 | Use a database; database technology and data model are open. |
| C06 | U | Planning only until explicit implementation approval. PRD precedes architecture, database design, and development planning. |

## 5. Assignment requirements and traceability

The evidence column describes proposed review evidence, not an approved test implementation.

| ID | Assignment baseline | Source | Proposed acceptance evidence / unresolved detail |
| --- | --- | --- | --- |
| A01 | Store shows all marketplace products; supports filtering, changing order, and searching by name. | p.1, Store | Demonstrate full catalog, narrowed results, changed order, and a name query. Platform, region, and price are examples; exact filters and sort options are open. |
| A02 | Homepage contains several different blocks; each gets content from its own backend service or database; frontend displays received content without hardcoded block content. | p.1, Homepage | Verify backend-driven content for every agreed block. Banner, best sellers, and deals are examples. Block count/types and interpretation of “own backend service or database” require an explicit rationale. Do not assume independent deployable services are mandatory. |
| A03 | Product-name search is available throughout the app, with matching results and support for typos and partial names where possible. | p.1, Search | Demonstrate agreed exact, partial, typo, and no-match queries. Tolerance and behavior remain open. |
| A04 | Product page displays details and offers from several sellers for comparison and selection. | p.1, Product page | Demonstrate comparison and selection of a seller's offer; exact fields and offer counts remain open. Seller-facing account or listing tools are not explicitly required. |
| A05 | Buyer adds products to a cart, pays, and receives the purchased digital key. Payment may be simulated or provider test mode; no real money. | p.1, Checkout | Demonstrate selected offer through cart, payment, order, and key delivery. Cart behavior, delivery channel, and failure cases remain open. |
| A06 | Visitors can register and sign in; protected areas include checkout and order history. | p.1, Authentication | Demonstrate registration/login and access protection; define order-history contents and ownership rules. |
| A07 | Colors, typography, spacing, and components follow Eneba's design reference. | p.1, Eneba styling | Review agreed screens against the reference; visual fidelity and responsive targets remain open. |
| A08 | Purchase journey supports homepage or search entry, product page, cart, sign-in-required checkout, payment, then order and key; styling applies to every screen. | p.1, diagram | Walk through the complete journey, preserving selected purchase context through sign-in (confirmed under D01a). |
| A09 | Submit GitHub project link(s). | p.2, Deliverables | Verify accessible project source and setup information. |
| A10 | Submit a public app URL deployed on a free-tier service. | p.2, Deliverables | Verify the hosted purchase journey. Render and Vercel are examples, not mandated platforms. |
| A11 | Preserve full agent conversations, including both prompts and responses; submit Markdown or an AI-chat link. | pp.1–2 | User requires a reliable Markdown export; link alone is insufficient for this project. See `AI_CONVERSATION_HISTORY.md`. |
| A12 | Author can explain code and justify selected scope. | pp.1–2 | Proposed evidence: concise scope rationale and a walkthrough the author can explain. |

## 6. Target users and roles

**U — D01 confirmed:** The initial version supports two interactive user types: visitors and registered buyers. Sellers are represented through backend/database-maintained profiles and offers. Seller accounts, seller dashboards, and administrator dashboards are excluded. These decisions come from the author's supplied PRD sections 3.1–3.3.

### 6.1 Visitors (unauthenticated)

Visitors can:

- Browse the homepage and product catalog.
- Search products by name and use available filters and sorting.
- View product details and compare offers from different sellers.
- Register or sign in to access purchasing features.

Visitors cannot complete purchases or access order history without authentication.

**U — D01a confirmed:** Visitors can add products to and manage a cart before signing in. Authentication is required at checkout, and the selected cart contents must survive sign-in so the buyer can continue the purchase.

Proposed acceptance evidence: add an offer as a visitor, start checkout, sign in, and verify the same selected offer and quantity remain available for checkout. Cart behavior when an account already has a saved cart, persistence duration, and handling changed prices or availability remain open under D04.

### 6.2 Registered buyers (authenticated)

Buyers have all visitor capabilities and can:

- Sign in and sign out.
- Manage basic account information (**U:** included in the author's draft; editable fields and priority remain to be defined).
- Add products to their shopping cart.
- Complete purchases using a simulated payment process (**U:** selected in the author's role capabilities; provider test-mode integration is not required by this selection).
- Receive and view purchased digital keys.
- Access order history and purchase details.

**Q:** Key simulation details, remaining cart rules, account fields, and order-detail contents still need decisions. Key delivery channels are confirmed under D17. The author's product statement/problem/goal remain proposals because they are explicitly marked Proposed in the supplied draft.

### 6.3 Excluded roles and operations

**U:** No seller accounts, seller dashboards, or administrator dashboards in the initial version. Seller profiles and offers are maintained through the backend/database; the maintenance mechanism will be decided later. This exclusion does not remove required multi-seller comparison.

## 7. Scope and prioritization

### Confirmed product range and catalog (D02a–D02d)

**U:** Include all three product types: game keys, gaming gift cards, and subscriptions. All are fulfilled as simulated digital keys. Use a deliberately small sample catalog; hundreds of products are not required. **U — D02b confirmed:** The initial catalog contains 54 products in total. **U — D02c confirmed:** 30 games, 15 gaming gift cards, and 9 subscriptions (54 total). Seller offers are separate from this product count.

**U — D02d confirmed:** Each of the 54 products has 6–7 offers from distinct sellers, with randomized variation. This means 324–378 seller offers in total. Randomization timing is confirmed under D02e; exact variation rules remain open.

**U — D02e confirmed:** Randomize demo offers once during initial data preparation. The resulting offers remain stable across page loads and throughout browsing and checkout; they are not rerandomized on each visit. Seller identities, prices, and initial availability are stable demo data; stock changes caused by purchases remain a separate D04 decision.

**Q:** Seller-pool size, offer fields, and variation rules remain open. **P:** Generate varied demo data rather than manually authoring hundreds of offers to contain preparation effort.

**U — D02f confirmed:** The catalog covers Steam, PlayStation, and Xbox. Each product identifies its applicable platform or redemption service. The distribution of the 54 products across platforms is not yet specified.

**U — D02g confirmed:** Activation-region coverage is Global, Europe, and United States. These are key-redemption restrictions, not a selection of checkout currencies. Product distribution across regions and the display of compatibility guidance remain open.

**U — D35 confirmed:** Use the same purchase and key-delivery journey for all three types. Gift cards are fixed-denomination keys and subscriptions are fixed-duration keys. Wallet top-ups/balances, recurring billing, automatic renewals, subscription lifecycle management, and external activation integrations are excluded.

**Q:** Define the minimum useful product details for each type. Gift cards need clear denomination/currency information and subscriptions need duration information to make offers understandable; exact fields, platform/region coverage, and offer counts remain open.


Assignment baselines A01–A12 remain tracked. The author has confirmed the role scope and buyer capabilities in section 6; no assignment feature has been removed. Required behavior should be narrowed in depth through explicit decisions, rather than silently omitted.

Confirmed exclusions under D01: seller accounts, seller dashboards, and admin UI. Seller onboarding and seller-facing listing management are consequently outside the initial role scope. Other possible additions (wishlists, reviews, coupons, recommendations, refunds, multiple currencies) are not assumed to be in scope.

### Proposals imported from the complete author draft

**P:** The full candidate inventory is retained in [PRD_DRAFT_REVIEW.md](PRD_DRAFT_REVIEW.md). It covers homepage blocks/personalization, catalog navigation and filters, sorting, search/autocomplete, product/offer details, cart quantities and persistence, simulated payment methods/failures/fees, key/order details, profile and password recovery, reviews, responsive UX, security, accessibility, validation, and submission documentation. No newly imported feature or acceptance criterion is confirmed solely because the PDF uses “must” or “will.”

**U — D09 confirmed: Sample, read-only product reviews.** Product pages display sample review data retrieved from the backend. Visitors and buyers may read reviews; creating, editing, deleting, and moderating reviews are outside the initial scope. Verified-purchase review submission and its associated eligibility checks/tests from the older draft are superseded. Sample reviews must not be presented as genuine purchases made through this demo. Exact display fields and sample-data volume remain open. Seller ratings are separate and still pending.

Proposed acceptance evidence: review data appears on the appropriate product page; no review-submission controls or operations are provided; sample content is identifiable as demo data.

**U — D10 confirmed: Curated backend recommendations.** The homepage recommendation collection is selected through backend-maintained data and is the same for all visitors and buyers. No browsing- or purchase-based personalization, preference inference, recommendation scoring, or activity tracking for recommendations is included. The frontend renders the returned selection without hardcoding product choices. This supersedes the personalized recommendation behavior and associated criteria in the original draft. D36 separately includes a same-browser recently viewed section; its history does not affect curated recommendations.

Proposed acceptance evidence: visitors and buyers receive the same curated selection; changing the backend selection changes the rendered collection without editing frontend product choices. The visible section name and other homepage blocks remain open under D03.

**P — Prioritization:** Adopt a core-first priority structure after agreeing feature scope. Keep purchase integrity, ownership of orders/keys, and usable failure feedback in the proposed release baseline; consider review submission, personalization, password recovery, and advanced filters as candidates for deferral given the remaining time. This is a recommendation, not an approved scope cut.

### Confirmed homepage blocks (D03a)

**U:** Include a promotional banner, category/platform shortcuts, Featured products (the shared curated recommendation collection), Top games, and Featured gaming gift cards, alongside the shared header and footer. All block content and product selections come from backend services/data sources in accordance with A02. This selection was explicitly approved before the author's request to recheck the live homepage.

**Reference recheck, 2026-11-12:** Revisited [Eneba's homepage](https://www.eneba.com/) in the browser and visually inspected the loaded hero and product collections. The observed page has a large promotional hero with adjacent campaign panels, a horizontal icon shortcut strip, Recently viewed, Recommended for you, Top games, separate Best selling eGift Cards and Best selling gift cards sections, Upcoming games, and Categories, followed by the footer. Some collections appear after initial loading; this is an observed session, not a guarantee of identical content for every visitor.

The approved demo maps the curated collection to Featured products, Top games to the corresponding game collection, and Featured gaming gift cards to the gaming-card collection. Lifestyle eGift cards are outside the confirmed catalog. D36 subsequently approves Recently viewed as a same-browser history block. Upcoming/preorder behavior and an additional lower genre/category block are not approved by viewing the reference. Banner count, carousel behavior, section ordering, visible product counts, collection links, header/footer contents, and responsive behavior still need decisions. Reference patterns include alternating purple section backgrounds, portrait product cards, large white headings, and outlined collection links.

**U — D03b (interpreted acceptance):** Use one static promotional banner for the initial version, with backend-driven image, text, and destination link. A rotating carousel and multiple campaign panels are outside the selected banner scope. The author replied “yeah, lets proceed” after the banner options; this is interpreted as accepting the recommended static banner and stated explicitly in the response. Exact artwork and destination remain open.

### Confirmed catalog filters (D03c)

**U:** Include product type, platform, activation region, price range, and game genre filters.

- Type: games, gaming gift cards, subscriptions.
- Platform: Steam, PlayStation, Xbox.
- Region: Global, Europe, United States.
- Price range: minimum/maximum purchase price; checkout currency and the exact product-price basis remain open.
- Genre: applies to games; genre vocabulary and handling of mixed results or non-game selections remain open.

**U — D03e confirmed:** Type, platform, region, and genre support multiple selections. Selected values within one filter use OR; different filter groups and the price range combine using AND. Search and sorting work alongside the active filters. Include a “Clear filters” action to reset filter selections and the price range. Clearing filters does not imply clearing the search query or changing sorting.

Proposed acceptance example: selecting Steam and Xbox plus Europe shows products matching (Steam OR Xbox) AND Europe; adding a price range further narrows that result. Selecting no values in a group imposes no restriction from that group. The price filter remains one minimum/maximum range rather than a list of selected values.

**Q:** Persistence in navigation, genre behavior across mixed product types, exact reset presentation, and no-result handling still need decisions. Additional filters from the source draft (publisher/brand, seller, availability, edition, operating system, and a separate country filter) are not included in the selected initial filter set.

### Confirmed catalog sorting and demo-data policy (D03d)

**U:** Include seven sort options: Most popular, Price low–high, Price high–low, Title A–Z, Title Z–A, Date newest first, and Date oldest first. Most popular is the default from the selected option and the author's source draft. Best selling is not in the selected set.

**U:** Mock data is acceptable; demonstrating the platform's behavior takes priority over real-world catalog accuracy. Popularity, dates, prices, and other demo attributes may be predefined fictional data. This does not replace functional behavior with static UI: filtering/sorting, seller selection, cart totals, orders, and key access must operate consistently on the demo data.

**Working interpretation (explicitly communicated):** Date sorting uses the date the product was added to the demo catalog, rather than its real-world release date. Price sorting uses the cheapest available seller offer as proposed in the preceding sorting question. Exact tie-breaking and treatment of products with no available offers remain open.

**P:** Supply these attributes through the backend alongside product data. Keep mock popularity/date values stable; the confirmed one-time randomization rule for seller offers also remains in force.

### Confirmed global search experience (D03f)

**U:** Provide live suggestions in the global header search alongside a full search-results page. Suggestions display matching product names, thumbnails, and starting prices. Selecting a suggestion opens its product page; submitting the query or selecting “View all” opens full results, with the agreed filters and sorting available. Partial-name matching and basic typo tolerance remain assignment requirements; accepted examples are recorded under D03g below. Suggestion count, minimum query length, loading/error states, and keyboard interaction still need acceptance decisions.

**User-proposed technology:** The author mentioned “elastic searc,” interpreted as Elasticsearch. Record this as a proposed search technology for architecture review, not an implementation decision. No search service is being provisioned or implemented during PRD planning. At architecture time, assess fit with PHP/Go, deployment constraints, setup/operating effort, and the 54-product demo before final selection. Search behavior is required regardless of the implementation chosen.

### Confirmed search acceptance (D03g)

**U:** Search is case-insensitive, supports partial product names and basic typos, and ranks exact matches ahead of typo matches under relevance ordering. No matches produce a clear empty state. Synonyms and semantic search are outside the initial scope.

Accepted examples, using corresponding demo catalog data:

- `MINECRAFT` matches `Minecraft`.
- `mine` matches `Minecraft`.
- `mincraft` matches `Minecraft`.
- An exact name match ranks above a typo-only match in suggestions and relevance-ranked results.
- A query with no matches displays a clear no-results state.

**Q:** The search-results default ordering and its interaction with explicitly selected catalog sorts remain to be resolved. Exact-match priority should not silently override an explicit user-selected price/title/date sort.

### Confirmed result pagination (D03h)

**U:** The catalog and full search results use numbered pages with 12 products per page, based on the selected numbered-page option. The complete 54-product catalog spans five pages before filtering (12 products on each of the first four pages and 6 on the last).

**P:** Show the result count and current page, retain active query/filter/sort selections while paging, and return to page 1 when the query, filters, or sorting changes. Disable unavailable previous/next actions. These detailed navigation behaviors remain proposals pending review.

### Confirmed locale settings scope (D11)

**U:** Full interface localization is not required for this task. The author supplied a screenshot of Eneba's settings modal and requested options for region, language, and currency behavior before deciding scope.

**Reference:** Purple “Update your settings” modal with preferred region, language, and currency dropdowns, close control, Cancel, and Save. The screenshot is a conversation attachment; no standalone image copy is currently in the workspace.

**U — Balanced option selected:** Provide an Eneba-style settings modal with a saved preferred-country selection, English as the only supported interface language, and EUR/USD/GBP currency choices using fixed demo exchange rates. Save preferences in the current browser. Country is a preference only: it does not automatically filter the catalog or determine activation compatibility, tax, or product availability.

Currency changes consistently update product, offer, and cart display prices. Completed orders retain their purchase currency and recorded amounts when preferences change later. Rates are illustrative demo data, not live exchange rates. Gift-card face value/currency remains distinct from the product selling price.

**U — D11a confirmed:** Checkout uses the buyer's selected currency (EUR, USD, or GBP), consistently with browsing and cart prices. The completed order records that currency and its final amounts; subsequent preference changes do not convert historical purchases. All payments remain simulated.

**Q:** Exact country choices, default country/currency, fixed exchange rates, and rounding still require agreement. Lithuania, United States, and United Kingdom are proposed country choices, not yet confirmed.

Preferred country is a visitor setting; activation region remains a product attribute. Do not present untranslated interface languages as supported. The English-only choice can remain visible to match the reference. No implementation is authorized.

### Confirmed checkout service-fee policy (D12)

**U:** No simulated service fee is charged. Checkout total equals the sum of each selected offer's unit price multiplied by its quantity, in the selected currency. The optional simulated service fee in the source draft is excluded. Currency conversion/rounding rules still need definition so cart and order totals agree.

### Confirmed cart quantities (D13)

**U:** Buyers and visitors can adjust cart quantities up to the selected seller offer's available stock. Each purchased copy receives a separate simulated digital key. Availability and quantity limits apply per seller offer rather than to a shared product-wide count.

Proposed acceptance evidence: a quantity within available stock can be selected, increasing quantity updates line totals and the order total, a quantity above available stock is rejected, and a completed purchase of quantity N delivers N distinct simulated keys. Exact stock-decrement timing, handling stock changes before checkout, and simultaneous purchase behavior remain open.

### Confirmed insufficient-stock behavior (D14)

**U:** If any cart item has insufficient available seller-offer stock at checkout, block the entire purchase. Identify affected items and let the buyer adjust quantities, remove items, or choose another offer before retrying. Do not silently reduce quantities, substitute sellers, or purchase only the remaining items. The blocked attempt produces no payment, completed order, or key delivery. Retain the cart for correction.

Proposed acceptance evidence: prepare a multi-item cart with one insufficient-stock offer; checkout identifies that item and completes no part of the purchase. After correcting the cart, the buyer can retry. Stock must be checked against current backend availability at checkout; exact stock-decrement and concurrent-request rules remain to be specified.

### Confirmed simulated payment methods (D15)

**U:** Checkout offers Card, PayPal, and Digital wallet as selectable simulated payment methods. Clearly label the payment flow as simulated. No real payment credentials are collected and no real financial transaction occurs. Selecting a brand does not imply a live integration with that provider.

**Q:** Retry details and any simulated method-specific presentation remain open. Payment outcomes are confirmed under D16 below. A generic Digital wallet choice does not imply an actual wallet balance, account, deposit, or external-wallet integration.

### Confirmed payment outcome simulation (D16)

**U:** Simulated payment succeeds by default. Provide a clearly labeled “Simulate failure” option so evaluators can deliberately exercise the failure path. This outcome control applies to the simulated payment flow, separate from the selected Card/PayPal/Digital wallet method. Do not randomly fail payments.

On simulated failure, display an understandable failure message, retain the cart for retry, and deliver no keys. A failed attempt must not become a completed purchase. Successful checkout remains subject to stock validation and the other agreed purchase rules. Detailed retry/reset behavior, failed-attempt history, stock-decrement timing, and duplicate-request handling remain open.

Proposed acceptance evidence: a normal payment completes the purchase and delivers keys; selecting simulated failure produces no completed purchase or keys and preserves cart contents. All three payment methods use the same predictable outcome policy.

### Confirmed key-delivery channels (D17)

**U:** After a successful purchase, show purchased simulated keys on the order confirmation page and in the buyer's order history/order details. Buyers can view and copy keys immediately and retrieve the same keys after signing out and signing back in. Only the purchasing buyer may access those keys. Email delivery is excluded from the initial version.

This applies to all three product types and each purchased copy under D13. The frontend and backend must enforce authenticated ownership of order/key access; hiding controls alone is insufficient.

Proposed acceptance evidence: complete a purchase, view/copy keys on confirmation, sign out and back in, and retrieve the same keys from the order. A different account and an unauthenticated visitor cannot retrieve them. No email delivery is required. Order-list fields, key presentation, unique assignment, and retry/concurrency rules remain to be finalized.

### Confirmed stock consumption (D18)

**U:** A successful purchase persistently reduces each selected seller offer's stock by the purchased quantity. Failed simulated payments leave stock unchanged. Stock does not reset when the page reloads; this is a purchase-driven change to stable demo data, not rerandomization.

Proposed acceptance evidence: buy 2 copies of an offer and observe its remaining stock decrease by 2; refresh and confirm the updated availability persists. Repeat with simulated failure and verify no stock change. Stock must not become negative; insufficient availability follows D14. Duplicate-request/concurrent-purchase handling and demo stock reset procedures still need definition.

### Confirmed duplicate-checkout behavior (D19)

**U:** Repeated submissions of the same purchase attempt, including double-clicks and network retries, produce at most one order and return the existing result. They must not consume extra stock or allocate additional keys. A deliberate new purchase is a separate attempt and may create a separate order, subject to stock and payment validation.

Proposed acceptance evidence: submit the same successful purchase attempt more than once and verify one order, one stock reduction per purchased quantity, and one corresponding set of keys. The protection must be enforced by the backend; disabling the button alone does not cover network retries. Technical mechanism and retry lifecycle are deferred to architecture.

### Confirmed account-management scope (D20)

**U:** Include viewing the account email, editing the display name, accessing orders/purchased keys, changing the password with verification of the current password, and forgotten-password recovery. Email/password registration, sign-in, and sign-out remain included. Email-address editing and additional profile fields are not part of the selected account scope.

Recovery requires a secure, expiring, single-use reset token and a way to deliver the recovery link to the requester. D21 confirms delivery through real email; D22 confirms a 30-minute token lifetime. Recovery requests should not reveal whether the submitted email has an account; this is a proposed security acceptance criterion from the author draft, pending consolidated review. Changing a password and consuming a recovery token are distinct flows.

**Scope risk:** Password recovery adds token lifecycle, delivery, and validation work beyond basic authentication. It is explicitly selected, not a stretch feature by default. Keep the delivery approach bounded; do not silently remove it to meet the deadline. D17 excludes purchase/key emails, not password-recovery email as selected under D21.

### Confirmed password-recovery delivery (D21)

**U:** Deliver password-reset links to the account's email address through real email, using expiring, single-use reset tokens. Do not expose reset links on a public recovery page. This does not add order-confirmation or digital-key emails; D17 still applies.

**Q / delivery dependency:** Email provider, free-tier suitability, sender verification, credentials, and delivery setup are deferred to architecture. The deployed recovery flow must be demonstrable; a public UI that merely claims an email was sent is insufficient. Do not send email or configure external services during PRD planning. D22 confirms a 30-minute reset-link lifetime. Resend behavior, delivery-failure feedback, and session behavior after a password reset remain open.

Proposed acceptance evidence: request recovery for an account, receive its reset email, open the link and set a new password, then confirm the new password works and the consumed link cannot be reused. Expired links must not permit password changes.

### Confirmed password-reset expiry (D22)

**U:** Password-reset links expire 30 minutes after issuance and remain single-use. Expired or already-used links cannot change a password. Proposed acceptance evidence: a valid unused link works within 30 minutes, and expired/consumed links are rejected with an understandable route to request another link.

### Confirmed registration verification policy (D23)

**U:** Registration does not require email verification before purchasing. After successful registration, the buyer can access authenticated checkout without following a verification link. Verification emails, pending-verification account states, and verification-resend controls are excluded from the initial version. This does not remove email-format validation, duplicate-account handling, or the real-email password-recovery flow.

### Confirmed cart persistence (D24)

**U:** Guest carts persist in the same browser across refreshes and reopening. Registered buyers have an account-associated saved cart that is retrievable across devices after sign-in. Guest cart contents must survive sign-in under D01a; merge with an existing account cart according to D25. Current stock is revalidated at checkout under D14.

Proposed acceptance evidence: a guest adds an offer, refreshes/reopens the app in the same browser, and sees the saved cart; a buyer saves a cart and signs in from another browser/device to retrieve it. Guest carts need not transfer across devices. Expiry, logout behavior, and cross-device conflict handling remain open; exact storage technology is deferred to architecture.

### Confirmed cart merging (D25)

**U:** At sign-in, merge the guest cart with the existing saved account cart. Retain offers from both carts. For the same seller offer, add the quantities; offers for the same product from different sellers remain separate cart lines. Flag any resulting stock shortage for correction rather than silently dropping items or reducing quantities. Insufficient stock blocks checkout under D14.

Proposed acceptance evidence: sign in with overlapping guest/account carts and verify combined quantities for identical offers and separate lines for different seller offers. Repeated processing of the same sign-in transfer must not add guest quantities again. The transfer mechanism is deferred to architecture.

### Remaining discovery review

Approximately 10–12 substantive product decisions remain; this is an estimate, not a fixed questionnaire. Prioritize user-visible choices and scope boundaries. Routine details should be documented as proposals for consolidated review rather than expanded into separate low-impact questions. The author subsequently changed this workflow: ask questions in groups of 3–4, focus on consequential scope/complexity trade-offs, and inspect live reference pages for routine UI details. This supersedes the original one-at-a-time instruction.

Remaining areas: seller/product detail presentation; gift-card/subscription boundaries; search defaults and remaining interaction details; filter/genre/pagination behavior; homepage collection/navigation details; order-history presentation; account/cart lifecycle behavior; settings defaults/rates/rounding; responsive/accessibility acceptance; release validation and security criteria; deadline/priority reconciliation; final assignment compliance review.

### Confirmed seller-comparison direction (D26)

**U:** Follow the actual Eneba product-page seller comparison and the author's supplied screenshot rather than the previously proposed flat list. Retain the agreed 6–7 distinct-seller offers per product; the reference screenshot's larger offer count does not change D02d.

**Observed reference, rechecked 2026-11-12:** The [product page](https://www.eneba.com/steam-rivals-of-aether-ii-pc-steam-key-global) separates Recommended offers from Other offers. It has a prominent featured purchase panel, seller identity/avatar/rating, prices, Buy now and cart controls, three initially visible other offers, and a “Load more offers” control. Featured/recommended offers are not necessarily the cheapest; the observed other-offer rows ascend in price. The screenshot confirms the same structure.

**P — Reference-derived demo details:** Use two backend-designated recommended offers from the existing offer set, designate one as the featured panel offer, show three remaining offers initially in price order, and reveal the rest with Load more. A featured offer may appear in the purchase panel and its recommended row but remains one offer, not extra inventory. Mock seller names/avatars/ratings come from backend data. Buy now carries the selected offer into the authenticated checkout journey; Add to cart retains ordinary cart behavior. Any choice about combining an existing cart with Buy now must be explicit before final approval. On small screens, stack the panel and rows with all seller/purchase controls reachable.

This is approval of the seller-comparison pattern, not blanket approval of cashback, wishlists, refund systems, newsletters, external trust claims, payment integrations, or personalized recommendations visible around it. Seller ratings are mock demo data, distinct from sample product reviews. Exact recommended-offer selection is a backend demo designation, not an inferred production ranking algorithm.

### Additional live reference review and routine proposals

Revisited the loaded product page, opened the existing shopping-cart drawer, and inspected the [cart page](https://www.eneba.com/checkout) without adding/removing items, changing quantities, or submitting a purchase. The drawer includes product/seller details, quantities, totals, and cart/checkout links. The cart page uses Cart → Payment → Get your product progression, cart rows, and a summary panel. Catalog/sidebar/filter/grid patterns were inspected earlier. Payment completion, private order pages, and recovery email flow have not been exercised on the real site.

**P:** Follow those drawer/cart/summary patterns while keeping confirmed no-fee, simulated-payment, key-delivery, and authentication behavior. Omit unrelated donation/marketing controls unless separately selected. Document further routine behavior (responsive layouts, keyboard controls, no-results/loading/errors, URL-preserved filters/pages, standard order detail fields) as explicit acceptance proposals for final review instead of asking individually about every control.

### Confirmed secondary-feature and visual-mock policy (D27–D30)

**U — D27:** Cashback may be visually represented with mock promotional data, but earning, balances, redemption, refunds of cashback, and accounting are excluded. Mock cashback does not alter cart totals, payment amounts, stock, or keys. Proposed presentation: label it as demo/illustrative cashback and explain that it has no redeemable value.

**U — D28:** Wishlist functionality is excluded: no saved-list data, add/remove operations, or wishlist page. Under D30, the reference heart icon may be shown as a disabled or clearly identified future/demo affordance; do not imply that clicking it saves a product.

**U — D29:** Include a small related-products collection on product pages, supplied by backend-curated demo data. Reuse the existing product cards and links. No behavioral tracking, machine learning, or personalized scoring is implied. Proposed display: up to five available related products, excluding the current product.

**U — D30:** Across secondary features, prioritize Eneba-like visual presentation where feasible; features may be represented as mock/future UI without implementing their full functionality. Keep working assignment requirements distinct from decorative/mock extras. Clearly identify unsupported interactive features instead of showing fake success, collecting unusable input, or promising real services. This policy does not authorize real third-party integrations or expand the approved feature set to every feature on Eneba. Future updates may implement deferred features.

Visual-only representations must respect explicit scope decisions: e.g. D03b still has one static banner, D09 still has read-only reviews, and D17 still excludes key-delivery emails. Refund services, external endorsements, or security guarantees must not be presented as actual properties of the demo. Preserve their visual hierarchy with appropriate demo-specific content if needed.

**U — C02 updated:** Assignment received Monday 9 November 2026 at 14:30. The author estimates four days remaining and requests a realistic 2–3 day delivery target. See C02 for provisional deadline/timezone calculation. This is an effort constraint for the upcoming plan; no implementation is authorized yet.

### Confirmed product media and Buy now behavior (D31–D32)

**U — D31:** Use a static product image gallery. Playable trailers/video playback are excluded. Proposed routine details: cover image plus a small thumbnail set, selected thumbnail updates the main image, sensible single-image fallback, descriptive alternative text, and keyboard-operable gallery controls. Asset counts remain content-preparation details.

**U — D32:** Buy now adds the selected seller offer to the existing cart and proceeds to checkout for the whole cart. It does not create an isolated single-offer purchase. Preserve the chosen seller, merge identical-offer quantities using the existing cart rules, and require sign-in for checkout. Apply stock validation, currency, payment, and duplicate-attempt rules to the resulting complete cart.

### Confirmed quality baseline (D33)

**U:** Include responsive desktop/mobile layouts, keyboard-accessible core controls, meaningful loading/empty/error states, critical backend tests, and one automated end-to-end purchase journey. Animation polish is lower priority than these requirements and the complete working purchase flow.

Proposed acceptance detail for consolidated review:

- Complete discovery → offer selection → guest cart → sign-in → simulated payment → confirmation/key → later order/key retrieval on desktop and mobile.
- Keyboard access and visible focus for navigation, search suggestions, gallery, cart, forms, payment, and key copying; labeled inputs and readable contrast.
- Catalog/search/product loading feedback; no-results, empty cart/history, unknown-product and request-failure states; useful retry actions without exposing backend details.
- Critical backend tests cover ownership boundaries, authentication, filter/search/sort behavior, stock validation, payment success/failure, unique key assignment, and repeated checkout attempts. Password-recovery token expiry/single use and real-email integration need suitable validation.
- At least one automated successful purchase journey verifies the resulting keys and order history. Security/failure cases receive focused backend checks rather than duplicating every UI path in E2E.
- Verify deployed behavior, not just local operation. Exact tools and implementation remain deferred.

### Confirmed demo-reset exclusion (D34)

**U:** A demo stock-reset procedure is not needed and is outside scope. Do not add an admin dashboard, public reset button, or dedicated private stock-reset feature. Initial demo-data preparation remains necessary for the agreed catalog; this is distinct from a reset feature. Successful purchases persistently consume stock under D18, so replenishment is not guaranteed and offers may sell out. Initial stock levels remain a proposed data-preparation choice.

### Confirmed recently viewed history (D36)

**U:** Include a Recently viewed homepage section using history in the same browser. This applies independently of the backend-curated Featured products selection, which stays identical for all users. Cross-device/account-synchronized browsing history and recommendation personalization are excluded.

**P — Routine behavior:** Keep up to 10 distinct product IDs locally, newest first; revisiting a product moves it to the front. Display up to five at once using existing cards and hide the section when empty. Obtain current product details, offer prices, and availability from the backend rather than saving hardcoded/stale product-card content. Ignore removed/unknown product IDs. These bounds and presentation details await consolidated review.

### Confirmed recovery lifecycle (D37)

**U:** Issuing a new reset link invalidates older reset links for that account. A successful reset invalidates existing authenticated sessions and requires fresh login. Recovery request confirmation does not reveal whether an email is registered. Links remain single-use with a 30-minute lifetime under D22. Real-email delivery remains required under D21.

Proposed acceptance evidence: an older link cannot reset the password after a new link is issued; the latest unused link works within its lifetime; prior sessions stop accessing protected resources after reset; known/unknown email requests receive the same generic confirmation. Exact throttling and delivery-failure handling remain part of consolidated security/error review.

### Confirmed search-default ordering (D38)

**U:** Search results default to relevance, including exact-match priority over typo-only matches. The unqueried catalog defaults to Most popular. Explicitly choosing one of the agreed catalog sorts overrides relevance for search results. Suggestions continue to use relevance ordering.

**P:** Treat relevance as the search-specific default rather than another unqueried catalog sort. Reset to page 1 when a query/filter/sort changes, preserve those selections through paging, and keep a user-selected sort until deliberately changed or the search/catalog context is reset. Exact URL/navigation conventions await consolidated review.

### Approval and transition

The author approved the product requirements and authorized architecture followed by database design on 2026-11-12. Architectural details and routine unresolved choices are documented as recommendations in the design documents rather than silently presented as earlier product decisions. Application implementation still requires explicit approval.

## 8. Product decisions to resolve

Resolve these sequentially, adapting the list to the author's answers. Review scope is resolved under D09. Recommendation scope is resolved under D10. Homepage blocks are confirmed under D03a. D03b selects a static backend-driven banner (interpreted acceptance recorded above). The catalog filter set is confirmed under D03c. Sorting options are confirmed under D03d. Multi-select filter behavior is confirmed under D03e. Live search interaction is confirmed under D03f. Search accuracy is confirmed under D03g. Numbered pagination is confirmed under D03h. D11 balanced settings scope is confirmed. D11a confirms checkout in the selected currency. D12 confirms no service fee. D13 confirms quantities up to offer stock. D14 confirms blocking the entire checkout if any item lacks stock. D15 confirms Card, PayPal, and Digital wallet simulation. D16 confirms default-success payment with an explicit failure option. D17 confirms confirmation/history key delivery with copying and buyer-only access. D18 confirms persistent stock consumption on success only. D19 confirms at most one order per purchase attempt. D20 confirms display-name editing, current-password-verified password change, and forgotten-password recovery. D21 confirms real-email password recovery. D22 confirms a 30-minute reset-link lifetime. D23 confirms no registration email verification. D24 confirms same-browser guest carts and cross-device buyer carts. D25 confirms merged guest/account carts. D26 confirms Eneba’s seller-comparison pattern. D27–D30 and the delivery target are confirmed. D31–D34 confirm static media, whole-cart Buy now, quality baseline, and no stock-reset feature. D35–D38 confirm product boundaries, same-browser Recently viewed, recovery lifecycle, and search-default ordering. Next consolidate routine proposals and perform the original-assignment compliance review:

1. **D01 — Roles and operating scope: Confirmed.** Visitors and registered buyers; no seller/admin accounts or dashboards. **D01a confirmed:** Guest cart allowed; sign-in required at checkout; cart survives sign-in.
2. **D02 — Product range:** **D02a confirmed:** Game keys, gaming gift cards, and subscriptions, all delivered as simulated keys, in a small sample catalog. **D02b confirmed:** 54 products total, excluding seller offers. **D02c confirmed:** 30 games, 15 gaming gift cards, and 9 subscriptions. **D02d confirmed:** 6–7 distinct-seller offers per product, with randomized variation. **D02e confirmed:** Randomize once during demo-data preparation and keep resulting offers stable. **D02f confirmed:** Steam, PlayStation, and Xbox. **D02g confirmed:** Global, Europe, and United States activation regions. Product-type boundaries remain open.
3. **D03 — Discovery:** **D03a confirmed:** Banner, category/platform shortcuts, Featured products, Top games, Featured gaming gift cards; shared header/footer. **D03b:** static backend-driven banner (interpreted acceptance). **D03c confirmed:** Type, platform, region, price range, genre. **D03d confirmed:** Most popular (default), price ascending/descending, title ascending/descending, date newest/oldest. Mock data accepted. **D03e confirmed:** Multi-select filters, OR within groups, AND between groups, Clear filters; search/sort combine with active filters. **D03f confirmed:** Live name/thumbnail/starting-price suggestions plus full filterable/sortable results. Elasticsearch proposed for later architecture review. **D03g confirmed:** Case-insensitive/partial/basic-typo matching; exact matches rank first under relevance; clear no-results state; no synonyms/semantic search. **D03h confirmed:** Numbered catalog/search pages, 12 products per page. D11 confirms English-only settings with country preference and fixed-rate EUR/USD/GBP display prices. D11a confirms checkout in the selected currency. D12 confirms no service fee. D13 confirms cart quantities up to available offer stock, with a separate key per purchased copy. D14 confirms blocking the entire checkout on insufficient stock. D15 confirms simulated Card, PayPal, and Digital wallet. D16 confirms default success with a labeled failure option, retaining cart on failure. D17 confirms confirmation/history key delivery only, with copying and buyer-only access. D18 confirms persistent stock consumption on success only. D19 confirms at most one order per purchase attempt. D20 confirms expanded account management including password recovery. D21 confirms real-email password recovery. D22 confirms a 30-minute reset-link lifetime. D23 confirms no registration email verification. D24 confirms same-browser guest carts and cross-device buyer carts. D25 confirms merged guest/account carts. D26 confirms Eneba-style seller comparison. Next: grouped consequential scope choices and deadline confirmation; routine details remain proposed for final review.
4. **D04 — Product and cart:** Detail fields, offer comparison, selection, quantities, persistence, availability behavior.
5. **D05 — Purchase:** Simulated payment selected; define payment outcomes, key delivery, order contents/history, failure and retry behavior.
6. **D06 — Accounts:** Registration/login details, protected behavior, purchase ownership, optional account functions.
7. **D07 — Experience:** Eneba reference screens, responsive targets, loading/empty/error states, accessibility expectations.
8. **D08 — Acceptance and priority:** End-to-end acceptance scenarios, essential depth versus stretch goals.

Deadline precision and available working hours also need confirmation before estimating delivery. Architecture and database-design documentation are now authorized; see ARCHITECTURE.md and DATABASE.md. Application implementation remains deferred.

## 9. Confirmed user decisions

| ID | Decision | Rationale / provenance |
| --- | --- | --- |
| U01 | Planning only; implementation requires explicit approval. | User's opening request. |
| U02 | Maintain `docs/PRD.md`; author makes final decisions. Initial one-at-a-time approach is superseded by U44: group 3–4 meaningful questions. | User’s opening request and subsequent workflow correction. |
| U03 | Separate assignment requirements, proposed features, and assumptions; challenge scope against remaining time. | User's opening request. |
| U04 | Preserve full AI history in Markdown; a shareable link alone is insufficient. | User's opening request. |
| U05 | Complete a final assignment compliance review after PRD agreement. | User's opening request. |
| U06 / D01 | Visitors and registered buyers only; seller profiles/offers maintained through backend/database; no seller accounts or seller/admin dashboards. | Author's supplied PRD, section 3. |
| U07 | Buyers can sign out, manage basic account information, use a cart, make simulated payments, view purchased keys, and access order history/details. | Author's supplied PRD, section 3.2; detailed behavior and account fields pending. |
| U08 / D01a | Visitors can use the cart; checkout requires sign-in; cart contents survive sign-in. | Author explicitly accepted the guest-cart recommendation. |
| U09 / D02a | Include game keys, gaming gift cards, and subscriptions; all fulfilled as simulated keys; keep the catalog small rather than adding hundreds of products. | Author's explicit product-range selection. |
| U10 / D02b | Initial catalog contains 54 products total; seller offers do not count as additional products. | Author explicitly selected 54 in response to the product-count question. |
| U11 / D02c | Catalog distribution: 30 games, 15 gaming gift cards, and 9 subscriptions. | Author selected 30 games, 15 cards, and the remainder as subscriptions; 54 - 30 - 15 = 9. |
| U12 / D02d | Each product has 6–7 offers from distinct sellers, with randomized variation (324–378 total offers). | Author selected “6–7 randomized” in response to seller offers per product; variation rules pending. |
| U13 / D02e | Randomize demo offers once during data preparation; keep offers stable across visits and the purchase journey. | Author explicitly selected one-time randomization. |
| U14 / D02f | Catalog platforms: Steam, PlayStation, and Xbox. | Author explicitly selected “steam, ps, xbox.” |
| U15 / D02g | Activation regions: Global, Europe, and United States. | Author explicitly accepted the recommended region set. |
| U16 / D09 | Sample, read-only product reviews sourced from backend data; no buyer review submission in the initial version. | Author explicitly selected the read-only review option. |
| U17 / D10 | Homepage recommendations use a curated backend selection shared by all users; no activity-based personalization. | Author explicitly selected the curated backend option. |
| U18 / D03a | Homepage includes promotional banner, category/platform shortcuts, Featured products, Top games, Featured gaming gift cards, and shared header/footer. | Author accepted the proposed set and requested another live homepage review. |
| U19 / D03b | One static promotional banner with backend-driven image, text, and link. | “Yeah, lets proceed” interpreted as acceptance of the recommended option; interpretation explicitly communicated. |
| U20 / D03c | Catalog filters: product type, platform, activation region, price range, and game genre. | Author explicitly selected the second filter option and listed all five filters. |
| U21 / D03d | Seven sorts: Most popular, price low–high/high–low, title A–Z/Z–A, date newest/oldest. | Author explicitly listed the requested sort options. |
| U22 | Mock data is acceptable; prioritize platform functionality over real-world data accuracy. | Author's explicit clarification accompanying sorting selection. |
| U23 / D03e | Multi-select categorical filters; OR within a group, AND across groups and price range; Clear filters action; search/sort work with active filters. | Author selected the multi-select option. |
| U24 / D03f | Global search provides live suggestions alongside full results as described above. | Author accepted live suggestions and mentioned Elasticsearch as a technology preference for architecture review. |
| U25 / D03g | Case-insensitive, partial-name and basic-typo search; exact matches ahead of typo matches; clear empty state; no synonyms or semantic search. | Author accepted the proposed search accuracy examples and bounded scope. |
| U26 / D03h | Numbered catalog and full-search-results pages, 12 products per page. | Author selected the numbered-page option, which proposed 12 products per page. |
| U27 | Full interface localization is not required; discuss bounded region/language/currency settings options. | Author's settings-modal reference and explicit clarification. |
| U28 / D11 | Balanced settings: saved browser country preference, English-only UI, fixed-demo-rate EUR/USD/GBP prices; completed orders retain purchase currency. Country does not automatically filter products. | Author selected “balanced.” |
| U29 / D11a | Checkout uses the buyer's selected currency, which is recorded with final order amounts. | Author accepted checkout in the selected currency. |
| U30 / D12 | No simulated service fee; total is the sum of unit prices × quantities in the selected currency. | Author answered “no” to including a service fee. |
| U31 / D13 | Cart quantities may increase up to the selected offer's available stock; each purchased copy receives a separate simulated key. | Author explicitly selected quantities up to available stock. |
| U32 / D14 | Insufficient stock in any cart item blocks the entire checkout; identify affected items, preserve cart for correction, and produce no partial purchase/payment/key delivery. | Author selected “block.” |
| U33 / D15 | Simulated payment choices: Card, PayPal, and Digital wallet; clearly labeled simulation, with no real credentials or transactions. | Author explicitly selected all three methods from the proposed options. |
| U34 / D16 | Simulated payments succeed by default; a labeled “Simulate failure” option demonstrates failure. Failed payments preserve the cart and deliver no keys. | Author selected the first payment-outcome option. |
| U35 / D17 | Keys are viewable/copyable on confirmation and in order history/details, persist across sign-ins, and are accessible only to the purchasing buyer; no email delivery. | Author explicitly selected confirmation and history only. |
| U36 / D18 | Successful purchases persistently reduce seller-offer stock by quantity purchased; failed payments leave stock unchanged. | Author accepted stock reduction after successful purchases. |
| U37 / D19 | Repeated submissions of the same purchase attempt return the existing result and create at most one order, without extra stock/key consumption; deliberate new purchases are separate. | Author accepted the proposed duplicate-checkout behavior. |
| U38 / D20 | View email, edit display name, access orders/keys, change password using current-password verification, and recover forgotten passwords. | Author selected the full account-management option. |
| U39 / D21 | Password-reset links are delivered through real email with expiring, single-use tokens; no public reset-link exposure. | Author selected real email delivery. |
| U40 / D22 | Password-reset links expire 30 minutes after issuance and are single-use. | Author selected 30 minutes. |
| U41 / D23 | No registration email verification; successful registration immediately enables authenticated checkout. | Author answered “no” to required verification. |
| U42 / D24 | Guest carts persist across refresh/reopening in the same browser; buyer carts persist with the account across devices. | Author explicitly selected the recommended persistence option. |
| U43 / D25 | Merge guest and saved account carts at sign-in; sum identical-offer quantities, retain different offers separately, and flag shortages. | Author selected “merge.” |
| U44 | Ask consequential scope/complexity questions in groups of 3–4. Inspect Eneba reference pages and draft routine UI details rather than asking trivial questions. | Author explicitly updated the collaboration workflow. |
| U45 / D26 | Seller comparison follows live Eneba and the supplied screenshot: featured purchase panel, recommended offers, and expandable other-offer rows. | Author explicitly requested reference-based seller comparison. |
| U46 / D27 | Cashback is visual mock content only, without earning/balance/redemption functionality. | Author selected “exclude but mock it.” |
| U47 / D28 | Wishlist functionality excluded. | Author explicitly excluded it. |
| U48 / D29 | Include backend-curated related products. | Author explicitly included related products. |
| U49 / D30 | Secondary features may use visual mock/future presentation; defer full functionality where feasible. | Author explicitly applied this principle generally. |
| U50 / C02 | Received 2026-11-09 at 14:30; target delivery within 2–3 days from current planning rather than using all remaining four days. | Author supplied receipt time and realistic planning window; receipt timezone remains assumed. |
| U51 / D31 | Static product image gallery; no playable trailers. | Author selected static media. |
| U52 / D32 | Buy now adds the selected offer to the existing cart and proceeds with whole-cart checkout. | Author accepted Buy now with an existing cart after the whole-cart recommendation. |
| U53 / D33 | Responsive desktop/mobile, keyboard core controls, loading/empty/error states, critical backend tests, and one automated purchase journey. | Author explicitly accepted the proposed quality baseline. |
| U54 / D34 | Demo stock reset is outside scope. | Author explicitly excluded the reset procedure. |
| U55 / D35 | Fixed-denomination gift-card keys and fixed-duration subscription keys; no wallet top-ups, recurring billing, renewals, or activation integrations. | Author accepted the proposed product boundaries. |
| U56 / D36 | Same-browser Recently viewed history section; independent from curated recommendations. | Author selected same-browser history. |
| U57 / D37 | New reset links invalidate old ones; successful reset signs out existing sessions and requires login; generic recovery-request confirmation. | Author accepted the proposed recovery lifecycle. |
| U58 / D38 | Search defaults to relevance; unqueried catalog to Most popular; an explicit selected sort overrides relevance. | Author accepted the proposed ordering policy. |
| U59 | PRD approved; proceed with architecture, then database-design documentation. Application implementation still requires separate approval. | Author's explicit stage transition. |
| U60 / AR02 | PHP + Laravel backend. | Author selected Laravel during architecture review. |
| U61 / AR05 | PostgreSQL search with trigram matching; supersedes the earlier Elasticsearch preference. | Author selected PostgreSQL search during architecture review. |
| U62 | No verified sender domain is available yet. | Author's architecture prerequisite response; real delivery validation remains open. |
| U63 / AR02 | One modular Laravel application; homepage blocks use separate backend modules/services sharing PostgreSQL. | Author explicitly accepted the modular structure and assignment interpretation. |
| U64 / AR07 | One Render service serves React assets and API; Neon PostgreSQL provides persistence. | Author explicitly selected Render + Neon. |
| U65 / AR08 | Brevo provisionally selected for password-recovery email, subject to sender/account and delivery validation. | Author selected Brevo provisionally. |

## 10. Risks and unresolved assumptions

- Approximately four days remain by the author’s estimate; target delivery is 2–3 days. These are not confirmed full working days. Broad seller/admin features would compete with required purchase, deployment, and submission work.
- Homepage data ownership wording is ambiguous; document a defensible interpretation before architecture.
- A 54-product catalog increases image, metadata, and content-verification work compared with the proposed 18-product catalog. Reuse shared layouts and tightly bound optional features; the category split is confirmed and seller-offer depth is confirmed at 6–7 per product (324–378 total). Randomization should be bounded so catalog preparation and purchase consistency remain manageable.
- Search tolerance needs explicit examples to avoid an unbounded search-engine project.
- Key delivery, stock, and repeated payment behavior need bounded acceptance criteria.
- The complete author draft has been reviewed and its proposals inventoried. The review conflict is resolved under D09; recommendation scope is resolved under D10. Other enhancement priorities still require decisions. Other prior conversations may contain decisions that still need importing.
- Current conversation export is a snapshot; prior sessions and the final response of an active turn must also be captured before submission.

## 11. Final PRD review gate

Product approval received on 2026-11-12. Both assignment pages were rechecked against confirmed requirements; see [PRD_COMPLIANCE_REVIEW.md](PRD_COMPLIANCE_REVIEW.md). Documented coverage is not implemented compliance. Homepage-source interpretation, real-email prerequisites, final history export, and deployment evidence require follow-through in design and delivery. PRD approval does not authorize implementation.

## 12. Change log

- 2026-11-12: Created discovery draft from both assignment pages and the user's planning constraints. D01 awaits the author's decision.

- 2026-11-12: Added user-supplied homepage references and live homepage/catalog/product observations. All observed extras remain unapproved; D01 remains pending because “proceed with the PRD” does not unambiguously select a role option.

- 2026-11-12: Incorporated Daraab’s supplied PRD sections 1–3, preserving proposed statement/problem/goals. Confirmed D01 role scope and simulated-payment buyer capabilities; guest cart behavior remains open.

- 2026-11-12: Confirmed D01a: guest cart access, authentication at checkout, and cart preservation through sign-in. Next decision: product types under D02.

- 2026-11-12: Confirmed D02a: all three product types with simulated key delivery and a small catalog. Proposed boundaries for gift-card/subscription behavior remain pending; exact size is the next decision.

- 2026-11-12: Confirmed D02b: 54 products total, excluding seller offers. Category distribution remains pending; recorded content-preparation scope risk.

- 2026-11-12: Confirmed D02c: 30 games + 15 gaming gift cards + 9 subscriptions = 54 products. Next decision: seller-offer depth.

- 2026-11-12: Confirmed D02d: 6–7 randomized distinct-seller offers per product (324–378 total). Proposed one-time data randomization with stable persisted offers; awaiting decision on timing.

- 2026-11-12: Confirmed D02e: one-time offer randomization during demo-data preparation, with stable offers afterward. Platform coverage is the next decision.

- 2026-11-12: Confirmed D02f: Steam, PlayStation, and Xbox platform coverage. Activation-region coverage remains open.

- 2026-11-12: Confirmed D02g: Global, Europe, and United States activation regions. Author offered a fuller existing PRD for review; only previously supplied sections 1–3 have been incorporated so far.

- 2026-11-12: Reviewed all 29 pages of the author’s full draft. Added source provenance and a linked proposal/conflict inventory. Confirmed conversation decisions remain authoritative; review scope is the next decision. No implementation authorized.

- 2026-11-12: Confirmed D09: sample, read-only product reviews; superseded verified-purchase review submission and dependent requirements. Next decision: homepage recommendation scope.

- 2026-11-12: Confirmed D10: shared curated backend recommendation selection, superseding personalized recommendation requirements. Recently viewed remains a separate open scope choice. Next: homepage block selection.

- 2026-11-12: Confirmed D03a homepage block set and revisited the live homepage, including hero, loaded section headings, and product-collection appearance. Reference-only extras remain unapproved. Next: banner behavior.

- 2026-11-12: Interpreted acceptance of recommended static backend-driven banner under D03b; communicated interpretation. Next decision: essential catalog filters.

- 2026-11-12: Confirmed D03c: type, platform, region, price range, and genre filters. Genre behavior and filter combination rules remain open; sorting is the next decision.

- 2026-11-12: Confirmed D03d seven sorting options and mock-data policy. Communicated catalog-added date interpretation; stable, internally consistent behavior remains required. Next: filter combination rules.

- 2026-11-12: Confirmed D03e multi-select filter behavior and Clear filters action. Next decision: search interaction.

- 2026-11-12: Confirmed D03f live suggestions and full search results. Recorded Elasticsearch as user-proposed technology deferred to architecture. Next: bounded typo-tolerance acceptance.

- 2026-11-12: Confirmed D03g search accuracy examples and exclusions. Noted relevance versus explicit sorting interaction for later resolution. Next: pagination.

- 2026-11-12: Confirmed D03h numbered pagination with 12 products per page. Detailed page-reset/preservation behavior remains proposed. Next: checkout currency.

- 2026-11-12: Recorded no-localization constraint and supplied locale-modal reference. Added three proposed settings/currency scopes under D11; awaiting selection.

- 2026-11-12: Confirmed D11 balanced locale settings. Exact country choices, defaults/rates/rounding, and checkout currency policy remain open. Next: checkout currency behavior.

- 2026-11-12: Confirmed D11a: selected currency applies through simulated checkout and is retained on completed orders. Next decision: simulated service fees.

- 2026-11-12: Confirmed D12: no simulated service fee. Next decision: cart quantities.

- 2026-11-12: Confirmed D13: quantities up to offer stock and a separate simulated key per purchased copy. Next: checkout behavior when stock is insufficient.

- 2026-11-12: Confirmed D14: insufficient stock blocks the whole purchase; retain cart and identify affected items for correction. Next: simulated payment methods.

- 2026-11-12: Confirmed D15 simulated Card, PayPal, and Digital wallet choices. Next decision: payment outcome simulation.

- 2026-11-12: Confirmed D16 predictable payment outcomes: default success plus explicit failure control; preserve cart and withhold keys on failure. Next: key-delivery channels.

- 2026-11-12: Confirmed D17 in-app key delivery on confirmation and order history/details, with copying, durable retrieval, and buyer-only access; email excluded. Next: purchase/stock consistency.

- 2026-11-12: Confirmed D18 persistent stock reduction on successful purchases only. Next: duplicate-checkout behavior.

- 2026-11-12: Confirmed D19 at-most-one order per purchase attempt, including repeated submissions/network retries. Next: account-management scope.

- 2026-11-12: Confirmed D20 expanded account management and forgotten-password recovery. Recorded added delivery/token lifecycle effort. Next: recovery-link delivery channel.

- 2026-11-12: Confirmed D21 real-email password recovery. Provider/sender setup deferred to architecture; no external email actions authorized or performed. Next: reset-link lifetime.

- 2026-11-12: Confirmed D22: password-reset links expire after 30 minutes. Next: registration email verification.

- 2026-11-12: Confirmed D23: no registration email verification; immediate access to authenticated checkout after successful registration. Next: cart persistence.

- 2026-11-12: Confirmed D24 guest same-browser and buyer cross-device cart persistence. Next: merging guest and existing account carts at sign-in.

- 2026-11-12: Confirmed D25 cart merging. Recorded estimated 10–12 remaining substantive decisions and a more focused review approach, retaining one-decision-at-a-time workflow. Next: seller-offer presentation.

- 2026-11-12: Updated workflow to groups of 3–4 consequential questions. Confirmed D26 reference-based seller comparison; revisited loaded offers and cart drawer/page without modifying the real cart. Added reference-derived proposals and grouped cashback/wishlist/related-products/time-budget decisions.

- 2026-11-12: Confirmed visual-only mock cashback, excluded wishlist functionality, included curated related products, and generalized clearly distinguished secondary-feature visual mocks. Recorded receipt date/time and 2–3 day target. Next: grouped media, Buy now, quality, and demo-reset scope.

- 2026-11-12: Confirmed D31–D34: static gallery, whole-cart Buy now, quality baseline, and no demo stock reset. Next grouped decisions: type boundaries, recently viewed, recovery lifecycle, and search default ordering.

- 2026-11-12: Confirmed D35–D38 product boundaries, same-browser Recently viewed, recovery lifecycle, and search relevance defaults. Ready for consolidated routine-proposal review and final assignment comparison.

- 2026-11-12: Author approved the PRD and authorized architecture then database-design documentation. Updated approval status and linked assignment coverage review; implementation remains unapproved.

- 2026-11-12: Confirmed PHP + Laravel and PostgreSQL search, recorded no verified email domain, and drafted ARCHITECTURE.md followed by DATABASE.md. Stack/hosting defaults remain explicitly distinguished from approvals.

- 2026-11-12: Confirmed modular shared-database application and Render + Neon deployment; provisionally selected Brevo with real delivery still to validate. Updated architecture and database design; implementation remains unapproved.

- 2026-11-12: Author authorized API design followed by implementation-plan documentation. Created API.md and TASKS.md against the confirmed stack; no application code, migrations or service setup authorized or performed.
