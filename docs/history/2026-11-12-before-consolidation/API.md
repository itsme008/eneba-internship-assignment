# API Design

Project: Eneba-Style Digital Marketplace  
Owner: Daraab Pathania  
Updated: 2026-11-12  
Status: Contract draft for review. Documentation only; no endpoints implemented.

## 1. Scope and conventions

This contract expands [ARCHITECTURE.md](ARCHITECTURE.md) and uses the entities/invariants in [DATABASE.md](DATABASE.md). The [PRD](PRD.md) is the approved product baseline. PHP + Laravel, one modular application, PostgreSQL search, Render + Neon, and provisional Brevo email are confirmed. REST, the exact payloads, limits and routine defaults below are proposed engineering details for review, not new product features.

- Same-origin HTTPS; base path `/api/v1`. All paths below include that prefix unless stated otherwise. Static frontend routes are separate from API routes and must not intercept API 404s.
- Requests: `Accept: application/json`; JSON bodies use `Content-Type: application/json`. Use session cookies, not bearer/JWT tokens. All mutation routes use session/CSRF middleware, including public login/registration/recovery and read-only POST preview.
- UUID strings identify domain entities; product detail routes use stable slugs. Taxonomy query values use codes from bootstrap, not UUIDs. Do not confuse slug paths with offer/product UUIDs in bodies.
- Dates/timestamps: ISO 8601 UTC; display formatting is a UI concern. Catalog date sorts use catalog-added timestamp, not release date.
- Money: `{ "currency": "EUR", "minor": "998" }` represents €9.98. All minor-unit amounts are nonnegative integer **strings** to avoid JSON/JavaScript precision loss. Rates are decimal strings. Quantities, page numbers and ratings are JSON numbers; cart revisions are nonnegative integer strings.
- JSON property names are snake_case. Required collections are arrays, including when empty; optional values are explicitly null. Unknown body fields or unsupported filter/sort/currency values return validation errors, rather than being used implicitly. Reject malformed JSON with 400.
- Public prices are current backend amounts. Cart/checkout input never accepts client-authored prices, totals, stock, seller ratings, user ownership or key contents.
- Success with a body uses `data`; lists add `meta`. Errors use `error`. Include a server correlation ID in `X-Request-ID`, sanitize it in logs, and never log passwords, reset links/tokens, quote tokens or delivered keys.
- Money fields in a response all use its stated currency unless clearly marked face value or base EUR. No live currency-rate or payment-provider calls.

Example error (documentation, not application code):

```json
{
  "error": {
    "code": "CART_VERSION_CONFLICT",
    "message": "Your cart changed. Review the latest cart before continuing.",
    "fields": {},
    "details": { "current_revision": "8" }
  }
}
```

Field-validation errors use arrays keyed by body/query paths, e.g. `fields.email` or `fields.items.0.quantity`. Details contain only safe structured context such as affected offer IDs, current stock, or the next action. UI displays appropriate messages rather than raw exception text.

## 2. Session, CSRF, caching and authorization

`GET /api/v1/auth/csrf` starts/refreshes the anonymous browser session and returns `data.csrf_token`. The client sends this token in `X-CSRF-TOKEN` for every POST/PUT/PATCH/DELETE. The session cookie is Secure, HttpOnly and SameSite=Lax in deployment; the CSRF token is readable transiently by the UI and is not an authentication credential. After login/logout/session rotation, reacquire the CSRF token. Use Laravel's session/web middleware with JSON responses; default stateless API routing alone is insufficient. Laravel documents the `X-CSRF-TOKEN` header and JSON validation responses. [CSRF documentation](https://laravel.com/docs/13.x/csrf), [validation documentation](https://laravel.com/docs/13.x/validation).

A proposed `419 CSRF_TOKEN_MISMATCH` follows Laravel's convention. Refresh CSRF and ask the user to retry; never blindly replay an uncertain purchase with a new idempotency key. For a purchase network failure, preserve and reuse the original attempt key/payload to recover its result. On 401 clear private caches and route to sign-in with an allowlisted internal return path.

Authenticated routes derive user ID only from session. Order IDs from another account return the same 404 as unknown IDs. API auth failures return JSON 401, not HTML redirects. Check auth_version on protected requests and recheck under the user lock for checkout mutations so password reset cannot resurrect an old session.

Use `Cache-Control: private, no-store` for auth/bootstrap-CSRF, account/cart/quote/attempt/order and recovery responses. Initially use `Cache-Control: no-store` for all dynamic catalog/content/search responses too: availability/prices must not survive purchase via a stale HTTP cache. Static versioned image/JS/CSS assets may be cached separately. Client query caching is bounded; invalidate/refetch affected offers, cart, catalog cards and content collections after purchase or currency change. Never store order keys in local storage, service-worker caches or URL parameters.

Proposed bounded validation: API body <= 256 KiB; q <= 100 Unicode characters; hydration <= 10 distinct product IDs; cart <= 400 distinct offers, per-line quantity 1–10,000. Merge overflow rejects the entire transfer with explicit feedback and retains the guest snapshot. These are abuse bounds, not promises that stock is sufficient. A field beyond available stock is handled according to the specific cart/merge rules below. Parse monetary strings with exact decimal/integer arithmetic; cap request prices/totals to the safe supported DB/application range.

## 3. Route inventory

Public means no authenticated buyer required; CSRF still applies to mutation methods.

| Method | Path after `/api/v1` | Access | Purpose |
| --- | --- | --- | --- |
| GET | `/bootstrap` | Public | Taxonomies, settings choices/defaults, current FX metadata, UI capabilities. |
| GET | `/products` | Public | Catalog and full search results, filters/sorts/pages. |
| GET | `/products/by-ids` | Public | Bounded card hydration in requested order. |
| GET | `/products/{slug}` | Public | Detail, image gallery, sample reviews and curated related cards. |
| GET | `/products/{slug}/offers` | Public | Recommended/other offers and featured designation. |
| GET | `/homepage/sections` | Public | Backend-controlled section manifest. |
| GET | `/homepage/sections/{key}` | Public | A block's independent backend data. |
| GET | `/search/suggestions` | Public | Relevance-ranked live product suggestions. |
| GET | `/auth/csrf` | Public | Session-bound CSRF token bootstrap. |
| POST | `/auth/register` | Public | Create buyer, authenticate, create/retrieve empty account cart. |
| POST | `/auth/login` | Public | Authenticate and rotate session. |
| POST | `/auth/logout` | Buyer | Invalidate current session. |
| POST | `/auth/forgot-password` | Public | Generic recovery request; real email via Brevo if viable. |
| POST | `/auth/reset-password` | Public | Consume reset token, update password, revoke all sessions. |
| GET | `/me` | Buyer | Current account identity. |
| PATCH | `/me` | Buyer | Edit display name only. |
| POST | `/me/password` | Buyer | Verify current password, change password. |
| POST | `/cart/preview` | Public | Resolve/reprice local guest offer/quantity intent; no saved cart. |
| GET | `/cart` | Buyer | Account cart in requested currency. |
| PUT | `/cart/items/{offer_id}` | Buyer | Set absolute quantity with expected revision. |
| DELETE | `/cart/items/{offer_id}` | Buyer | Remove offer line with expected revision. |
| POST | `/cart/merge` | Buyer | Apply guest transfer once and return account cart. |
| POST | `/checkout/quote` | Buyer | Sign a short-lived authoritative whole-cart quote. |
| POST | `/checkout/attempts` | Buyer | Execute/replay one purchase attempt. |
| GET | `/orders` | Buyer | Owner order summaries, no keys. |
| GET | `/orders/{order_id}` | Buyer | Owner snapshots and delivered simulated keys. |
| GET | `/health/live` | Public | Process alive; no external calls. |
| GET | `/health/ready` | Public | DB connectivity and minimal required-schema readiness. |

Declare literal `/products/by-ids` before the slug wildcard. Every endpoint validates query fields too. No product/seller/admin writes, wishlist, review submissions, cashback ledger, uploads, stock reset, subscription billing or real payment integration endpoints.

## 4. Shared representations

### ProductCard

Fields: `id`, `slug`, `title`, `type` (`game`, `gift_card`, `subscription`), `image {url, alt}`, `platform {code, name, icon_url}`, `region {code, name}`, `starting_price` Money or null, `availability` (`available` or `sold_out`), `demo_cashback {percent, amount, is_mock:true}` or null, `catalog_added_at`. Cashback percent is a decimal string; amount is illustrative Money based on current starting price and never an amount owed. No-stock card has null starting price/cashback amount. API never claims real endorsement or actual cashback value.

### Offer

Fields: `id`, `product_id`, `seller {id, name, avatar_url, rating {score, scale:10, label, is_mock:true}}`, `unit_price` Money, `compare_at_price` Money or null, `stock_quantity`, `is_purchasable`, `unavailable_reason` or null, `recommended_position` integer or null, `is_featured`, `demo_cashback` or null. Current amount comes from base EUR and the response's FX version. Only active public offers are listed; stock-zero offers remain visible but unavailable. Inactive products/sellers/offers cannot be purchased. No key hashes or inventory key material appear.

### Cart

Fields: `id` (null for preview), `revision` (null for preview), `currency`, `fx_version`, `items`, `issues`, `can_checkout`, `subtotal` Money or null, `total` Money or null. A preview's can_checkout means stock/data eligibility only, not authentication permission.

Each line contains `offer_id`, `product` card/identity, `seller` identity, `quantity`, `available_quantity`, `unit_price` Money or null, `line_total` Money or null, `is_purchasable`, and safe issue codes. No price is stored in local guest intent. Same product/different seller is separate. Unknown references are returned in issues, not silently dropped. If a line cannot be priced, authoritative whole-cart totals are null; do not show a partial total as payable. Known insufficient-stock lines can show requested totals but can_checkout is false.

### User

Fields: `id`, `email`, `display_name`. No password hash, auth_version, token, session payload or recovery status. No verification requirement. A successful register/login returns this representation and sets cookies, not an API bearer token.

## 5. Bootstrap and backend-driven homepage

### `GET /bootstrap`

200: `data` contains types/platforms/regions/genres; settings {countries, languages, currencies, defaults}; FX {version, base_currency, rates, is_mock:true}; payment_methods with `is_simulated:true`; and capability flags indicating sample reviews, mock cashback, no wishlist/recurring billing. Exact countries/FX values follow design defaults until reviewed. Language list includes English only. Country is a saved browser preference, not an implicit catalog or compatibility constraint. No preference-write API is needed.

### `GET /homepage/sections`

200: manifest entries `{key, renderer_type, title, position, content_url, requires_history}` from backend content rows. Include banner, shortcuts, featured_products, top_games, gaming_cards and recently_viewed; shared navigation/footer may use the same content services. No frontend hardcoded content IDs. Section titles/order/destinations are backend data; renderers are frontend components.

### `GET /homepage/sections/{key}`

Accept `currency`; recently_viewed also accepts `ids[]` (<=10 UUIDs). 200 `data` is a discriminated block object:

- Banner: key/type/title plus `image {url,alt}`, `text`, `destination` allowlisted internal path; one static banner.
- Shortcuts/navigation/footer: backend labels/icons/internal destinations and grouped links.
- Curated collection: key/type/title plus ProductCard `items` and optional `view_all_destination`.
- Recently viewed: current cards for supplied valid IDs, first occurrence order preserved, removed/unknown IDs omitted. Return empty items with no history; frontend hides empty block. No account history or personalization recorded.

404 `SECTION_NOT_FOUND` for unknown/disabled block. Block failure is isolated; the homepage can render other blocks and offer a local retry. No requested personalized values change the shared featured collection. `GET /products/by-ids?ids[]=…&currency=…` provides the same bounded ordered hydration helper outside the section renderer.

## 6. Catalog, search and product details

### `GET /products`

| Query | Rules |
| --- | --- |
| `q` | Optional trimmed name query <=100 chars. Empty means catalog; case-insensitive exact/partial/basic typo matching. |
| `type[]` | game, gift_card, subscription; deduplicate selections. |
| `platform[]` | steam, playstation, xbox. |
| `region[]` | global, europe, united_states. |
| `genre[]` | Valid bootstrap codes; matches games only. |
| `price_min_minor`, `price_max_minor` | Optional inclusive integer strings in selected currency; min <= max. |
| `currency` | EUR, USD, GBP; default EUR. |
| `sort` | popularity, price_asc, price_desc, title_asc, title_desc, date_desc, date_asc; relevance only when q is nonempty. |
| `page` | Integer >=1, default 1; page size always 12. |

Absent sort defaults to relevance for nonempty q and popularity otherwise. Explicit sort takes precedence over matching score. OR within categorical groups, AND across groups/query/price. No selected values means no constraint. Selecting a genre excludes non-games, even in mixed-type selection. Starting price = cheapest active in-stock offer from an active seller, converted/rounded consistently. Products with no eligible price remain discoverable, sort last in both price directions and are excluded by price bounds. Stable tie-breaker: product UUID.

200: `data` array of ProductCard, `meta {page, per_page:12, total, last_page, sort, currency, fx_version, query}`. Use last_page >=1 even when total=0. A page past the last valid page returns 200 empty data with real metadata; UI offers first/last valid navigation rather than treating it as an unknown resource. Query/filter/sort changes reset page 1. Preserve filters/search/sort/currency across paging. Clear filters does not clear search or sort. Invalid enum/array/bounds returns 422 `VALIDATION_FAILED`.

### `GET /search/suggestions`

`q` and currency only. At least two trimmed characters for a nonempty suggestion response; shorter input returns 200 empty items. Proposed UI debounce 250 ms; server limit six results. 200 `data {query, items:[ProductCard], view_all_destination}`. Do not return a misleading total limited to six; full result total belongs to /products. Rank exact, prefix/substring and typo matches deterministically; validate `MINECRAFT`, `mine`, `mincraft` against seeded Minecraft and an unrelated no-match query. No synonyms or semantic search. Backend returns the query so stale UI responses can be ignored.

### `GET /products/{slug}`

200: `data` extends product identity with description, edition/publisher (nullable), genre codes/names, static gallery `{url,alt,position}`, digital-delivery explanation, `gift_card {face_value:Money}` or null, `subscription {duration_months}` or null, `reviews {is_sample:true, average_rating, count, items:[{id,author,rating,body,reviewed_at}]}`, and `related_products:[ProductCard]`. Game type fields are null. No actual activation or recurring subscription functionality. Up to five backend-curated available related cards, excluding the current product. Unknown/inactive slug returns 404 `PRODUCT_NOT_FOUND`; sold-out active product remains viewable.

### `GET /products/{slug}/offers?currency=…`

200 `data {product_id, currency, fx_version, total_offers, featured_offer_id, recommended:[Offer], other:[Offer]}`. Return all 6–7 offers in one response; showing three other offers then Load more is a UI detail, avoiding unnecessary server pagination. Recommended entries use backend rank; other entries exclude them and ascend in price, then ID. Featured ID points to an offer already in recommended, not additional inventory. Unavailable entries retain purchase controls disabled. No silent seller replacement if the user's chosen offer is unavailable. Do not claim featured is cheapest.

## 7. Accounts and recovery

| Endpoint | Body | Success and failure contract |
| --- | --- | --- |
| `GET /auth/csrf` | None | 200 `{data:{csrf_token}}`; no-store. |
| `POST /auth/register` | email, display_name, password, password_confirmation | 201 User, session rotated; cart exists empty if new. Invalid/duplicate registration 422 with generic registration feedback where appropriate. Client then explicitly merges guest cart. No verification email. |
| `POST /auth/login` | email, password | 200 User, rotated session. Wrong/unknown credentials share 422 `INVALID_CREDENTIALS`, same message. Guest merge is a separate explicit call, not implicit repeated login addition. |
| `POST /auth/logout` | Empty JSON | 204; invalidate current session. UI clears private state, starts empty guest cart; saved buyer cart remains. |
| `GET /me` | None | 200 User, or 401. |
| `PATCH /me` | display_name only | 200 updated User. Email changes rejected; no extra profile fields. |
| `POST /me/password` | current_password, password, password_confirmation | 204, current password checked; proposed all-session revocation and fresh login. Incorrect current password 422; password policy failure 422. |
| `POST /auth/forgot-password` | email | 202 `{data:{message:"If an account exists, password reset instructions will be sent."}}` for syntactically valid known/unknown email. No token/link in response. |
| `POST /auth/reset-password` | email, token, password, password_confirmation | 204; password updated, token consumed, all sessions revoked; fresh login required. Invalid/expired/used token 422 `RESET_LINK_INVALID` with identical generic message. |

Design defaults: display_name 1–80 chars; email <=254 chars, trimmed/lowercased; passwords proposed 12–128 characters with confirmation and a framework hash strategy that safely supports the documented maximum (no silent algorithm truncation). These bounds refine the approved account feature, pending consolidated review. Never transform a password by trimming/case folding. Do not perform external breach-list requests as an unplanned dependency.

Latest issued reset token invalidates previous; expiry 30 minutes. Issuance/consumption serialize under user/reset locks as specified in DATABASE.md. Link uses configured app origin and frontend `/reset-password` route carrying token/email, not arbitrary user redirect. No external analytics/referrer leakage; client removes token-bearing URL after completion, avoids logging it, and never saves it in persistent storage.

Brevo is provisional and real delivery is required. Provider failure for a particular known account does not produce account-enumerating public feedback; record a private sanitized operational failure. If the provider is globally unavailable, a uniform 503 for all syntactically valid requests is permissible; do not invent successful delivery. Bound external call timeout; no auto background-worker assumption. Automated tests mock provider calls; an authorized real-recipient recovery delivery test is a separate release check.

Proposed throttles (shared DB counters, subject to tuning): login 5/minute per email+IP plus 30/minute/IP, registration 5/hour/IP, recovery 1/minute per email hash plus 5/hour/IP, reset consumption 5/minute/IP. Apply email-based throttling consistently for unknown emails too. 429 `RATE_LIMITED` includes `Retry-After` seconds. Rate limits are not a substitute for authorization/CSRF. No email sends occur merely by writing this design.

## 8. Guest pricing, account cart and transfer

### `POST /cart/preview` — public, read-only server state

Body `{currency, items:[{offer_id,quantity}]}`; no account cart mutation. 200 Cart preview includes current offer prices, totals and issues, including unknown/withdrawn/sold-out references. Accept duplicate-free offer IDs only; malformed duplicates return 422. This gives guests backend pricing without persisting a server guest cart. Local storage retains IDs/quantities, not this response's amounts.

### `GET /cart?currency=…` — buyer

200 account Cart; create the reusable empty cart on first access if absent, with a uniqueness-safe internal operation. All mutations below return a 200 Cart in requested currency unless noted otherwise.

### `PUT /cart/items/{offer_id}`

Body `{quantity, expected_revision, currency}`. Quantity is absolute, not an increment; set existing line or create it. Expected revision must match the locked cart, otherwise 409 `CART_VERSION_CONFLICT` and current revision. Invalid offer returns 404; inactive/sold-out or requested quantity above current stock returns 409 `OFFER_UNAVAILABLE`/`INSUFFICIENT_STOCK` with no changes. Quantity zero is invalid; use DELETE. Setting an unchanged line is a no-op, leaving revision unchanged. Effective edits increment revision once.

A lost PUT response may be retried with the same body. If revision is now stale, fetch current cart; compare the intended absolute quantity and never blindly add again. Clicking Buy now computes the intended absolute new quantity, makes this same mutation once, then opens checkout for the whole resulting cart. It never starts an isolated quick-purchase order.

### `DELETE /cart/items/{offer_id}`

Body `{expected_revision,currency}`. With matching revision, remove line and increment revision if present. An already absent line is a no-op. Stale revision returns 409; fetch current state instead of silently deleting another device's newer line. JSON DELETE bodies are an explicit contract; if the chosen client tooling does not support them, revise the contract consistently before implementation rather than quietly dropping revision checks.

### `POST /cart/merge`

Body `{transfer_id, currency, items:[{offer_id,quantity}]}`. Snapshot contains the guest cart only. The authenticated account owner comes from session. Merge all guest intent with existing account lines; add quantities for identical offer IDs; different seller offers stay separate. No expected revision is needed because addition applies transactionally to the latest locked cart.

- Same user + transfer ID + canonical payload returns the original transfer acknowledgement without adding again. Return the **current** cart, which may have changed since initial acknowledgement, plus `meta {transfer_id, replayed, applied_revision}`. Currency is presentation-only and excluded from the transfer-content hash; item IDs/quantities define intent.
- Same transfer ID with changed items returns 409 `TRANSFER_PAYLOAD_CONFLICT`; no lines change. Freeze guest edits while an uncertain transfer is being reconciled. Clear the matching local snapshot only after acknowledgement; a newly changed snapshot gets a new ID.
- Merged quantity above stock remains in cart with an issue and cannot checkout. Do not clamp quantities. A known inactive offer can remain flagged until removed; unknown IDs/overflow/malformed data reject the whole transfer with safe affected IDs and no changes. Browser snapshot remains available for user correction; no silent dropping.
- A newly applied nonempty merge increments revision once. No internal guest cart account or cross-device visitor history is created.

Cross-device mutations consistently lock user then cart. Ordinary stale-version edits return conflicts; merge receipt uniqueness prevents duplicate addition. After logout, no buyer cart or keys are copied into local guest storage.

## 9. Quotes, purchases and replay

### `POST /checkout/quote`

Body `{currency, expected_revision}`; account cart is authoritative. Empty cart returns 409 `CART_EMPTY`; stale revision or any unpurchasable line returns 409 with affected offer details and no reservation. 200:

```json
{
  "data": {
    "quote_token": "opaque-authenticated-quote",
    "expires_at": "2026-11-14T10:10:00Z",
    "cart_id": "40000000-0000-4000-8000-000000000001",
    "cart_revision": "7",
    "currency": "EUR",
    "fx_version": "demo-v1",
    "items": [
      {
        "offer_id": "30000000-0000-4000-8000-000000000001",
        "quantity": 2,
        "unit_price": { "currency": "EUR", "minor": "998" },
        "line_total": { "currency": "EUR", "minor": "1996" }
      }
    ],
    "subtotal": { "currency": "EUR", "minor": "1996" },
    "total": { "currency": "EUR", "minor": "1996" },
    "service_fee": { "currency": "EUR", "minor": "0" }
  }
}
```

Illustrative UUIDs/times/amounts, not actual persisted records. Recommended token lifetime 10 minutes. It authenticates user/cart/revision, full line IDs/quantities/base and charged unit prices, currency/rate version, totals, issue/expiry; no keys or credentials. All amounts calculated by backend: round converted unit half-up, multiply quantity, sum lines. No fee, cashback deductions or client-authored totals. Quote is not a stock reservation. Switching currency obtains a fresh quote before confirmation.

### `POST /checkout/attempts`

Headers: `Idempotency-Key: <client UUID>` plus CSRF/session headers. Body `{quote_token, payment_method, simulate_failure}`. Payment method is card/paypal/digital_wallet; simulate_failure is a required explicit boolean so retry fingerprints are unambiguous. No actual payment credentials/redirects. Retain exact request + key transiently during network uncertainty; never generate a new key automatically just because the response timed out.

| Outcome | HTTP | Response / effects |
| --- | --- | --- |
| Success | 201 | `data {attempt_id,status:"succeeded",order_id,order_number}`; commit one completed order, stock reduction, distinct keys/unit and cart clear. |
| Deliberate simulated decline | 200 | `data {attempt_id,status:"declined",code:"SIMULATED_PAYMENT_FAILED",message}`; no order/keys/stock change; cart retained. |
| Stock/offer/cart/quote changed | 409 | error code and safe affected IDs/current values; terminal rejected attempt stored if validly admitted, no purchase effects. |
| Reused key with different request | 409 | `IDEMPOTENCY_PAYLOAD_CONFLICT`; no changes to prior outcome. |
| Unknown/missing session | 401 | No purchase; sign in then recover using the same account/attempt when appropriate. |
| Invalid body/key format or forged token | 422 | Validation/`QUOTE_INVALID`; no order/stock/keys; may be rejected before an attempt is admitted. |
| Unavailable service/unexpected exception | 503/500 | Do not assert payment outcome; transaction rollback unless already committed. Retry same attempt to recover uncertainty. |

A replay of a terminal admitted attempt returns the same semantic HTTP status/outcome and order reference, with `Idempotency-Replayed: true`. Original uses false. Message/correlation metadata may differ; stock/order/key effects do not. Compare payload fingerprint and look up terminal outcome **before** requiring its old quote to be unexpired or cart still populated. Always authenticate and authorize the current caller. An order key set is not stored in the replay payload; UI separately fetches owner-protected order details.

Rejected/declined attempts are terminal: transport replay returns the old result. After correction or an intentional new payment, create a new key and (as needed) quote. No partial purchasing, automatic seller substitution, stock clamping, random decline, extra service fees, or future credit/wallet balances. A successful response is sent only after database commit.

Concurrency: attempt uniqueness, user/session-version check, cart revision lock, stable sorted offer locks and DB transaction make order/items/keys/inventory/cart changes all-or-nothing. A second buyer competing for the last units either sees enough current stock or rejects the entire checkout. Replaying after a crash/network loss must recover the committed result. A cart changed after quoting cannot be cleared by the old quote.

## 10. Orders and keys

### `GET /orders?page=1`

200 `data` summary list, newest purchased_at then ID, `meta {page,per_page:12,total,last_page}`. Each summary: id, order_number, purchased_at, status completed, payment_method, original currency, total Money, item_count (distinct lines), unit_count (sum quantities), and brief product image/title snapshots. No keys in list payload. Failed attempts are not completed orders and not added to order history.

### `GET /orders/{order_id}`

200 `data` contains order header snapshots, buyer_email, original currency, subtotal/total, payment_method, purchased_at and item snapshots. Each item has product/seller/type/platform/region, quantity, charged unit/line amounts and `keys:[{unit_number,value,is_simulated:true}]`. Gift-card face value/subscription duration comes from purchase snapshots, not later catalog state. Each quantity unit has one distinct non-activatable simulated key.

Only the purchasing account can retrieve it; another buyer receives 404, anonymous caller 401. Confirmation page uses this same endpoint after successful purchase. Copy key is a local clipboard action, not a write endpoint. Historical values never convert when the current currency preference changes. Return no-store; redact keys and reset data from logs. No email fulfillment, public order sharing, refunds or key reassignment.

## 11. Error/status catalog and availability

| HTTP / code | Client handling |
| --- | --- |
| 400 `INVALID_JSON` | Correct request syntax; not an automatic retry. |
| 401 `UNAUTHENTICATED` | Clear private cache, sign in; retain guest intent and uncertain attempt identity safely. |
| 404 `PRODUCT_NOT_FOUND`, `ORDER_NOT_FOUND`, `SECTION_NOT_FOUND` | Appropriate missing-page/section state; no ownership disclosure. |
| 409 `CART_VERSION_CONFLICT`, `CART_EMPTY` | Refetch/review cart; empty cart cannot purchase. |
| 409 `INSUFFICIENT_STOCK`, `OFFER_UNAVAILABLE` | Identify lines; preserve cart; adjust/remove/choose seller explicitly. |
| 409 `QUOTE_EXPIRED`, `QUOTE_CHANGED` | Obtain fresh quote, show amounts for confirmation, then intentional new attempt. |
| 409 `TRANSFER_PAYLOAD_CONFLICT`, `IDEMPOTENCY_PAYLOAD_CONFLICT` | Do not replay changed intent under old identity. |
| 419 `CSRF_TOKEN_MISMATCH` | Refresh CSRF; preserve purchase identity during uncertain result recovery. |
| 422 `VALIDATION_FAILED`, `INVALID_CREDENTIALS`, `RESET_LINK_INVALID`, `QUOTE_INVALID` | Safe field/form feedback; no raw backend errors. |
| 429 `RATE_LIMITED` | Respect Retry-After, no tight loops. |
| 500 `INTERNAL_ERROR`, 503 `SERVICE_UNAVAILABLE` | Safe retry UI and request ID; uncertain purchases retry same key. |

`GET /health/live`: 200 `{data:{status:"ok"}}` if process is alive. `GET /health/ready`: 200 ready or 503 unavailable depending on a bounded DB/schema check. Never send an email or modify inventory in a health check. Email-provider configuration and authorized real delivery are separate release checks, not called on every readiness probe. Free-host cold-start HTML/network failures are possible before API handling; the UI must tolerate non-JSON unavailable responses without displaying raw HTML.

## 12. Compatibility decisions and verification

This contract resolves prior outline gaps: product-offer routes consistently use slugs; guest carts use a public read-only POST preview; full search reuses /products; repeated offer expansion is local; account merge is explicit and replay-safe; CSRF is a JSON token handshake rather than an assumed Sanctum dependency. No schema additions are required beyond DATABASE.md.

Before implementation, review REST/session/contract defaults alongside the architecture. During implementation, verify request/response cases with PostgreSQL-backed feature tests and one browser purchase journey. Required contract cases: all filter/sort/currency/page combinations, typo examples, expired session/token, duplicate guest merge, stale cart, shortage, quote changes, same-attempt success/decline replay, concurrent last-stock purchase, rollback and order/key ownership. Do not write tests solely to mirror serializers; test the business guarantees and a few stable response shapes.

Open external dependency: Brevo sender approval and real delivery without a verified domain. Provider changes must preserve this public recovery contract. The corresponding readiness task is scheduled early in [TASKS.md](TASKS.md); a mock email is not an approved substitute.
