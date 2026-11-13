# Account and storefront update — 13 November 2026

Approved after direct inspection of Eneba's live storefront/profile menu, account navigation and keys library, alongside the user's supplied overview/library screenshots.

## Delivered

- Account-specific purple header/sidebar and pale dashboard, profile summary, real latest purchases, separate profile and password settings.
- Avatar dropdown with account identity, account/order/library navigation, logout and labeled preview entries.
- Authenticated searchable/paginated keys library with purchased-product snapshots, quantity, seller, date, order number, charged line total and links to existing authorized order keys. Secret keys are not returned in the list endpoint.
- Storefront favorites preview, country flag, category/platform strip, backend-provided manual promotional slides, genre tiles and expanded footer. Newsletter is disabled and explicitly labeled as a preview. External links are marked.
- Existing purchase/key/claim behavior preserved. No database migration or catalog expansion.

## Verification

- Backend suite: 22 tests passed, 283 assertions. New coverage checks authentication, cross-account isolation, literal title/order-number search, empty results, invalid pagination and absence of keys in summaries.
- PHP formatting, TypeScript checking and production build passed.
- Connected browser: desktop account overview shows the actual purchase cover; library lists its correct product, seller, price and order link; title search works. Profile form and menu navigation verified. Menu Escape dismissal verified. Promotional slide changes image/text/action.
- At 390×844, account library and homepage document widths equal the viewport (390px); no page overflow. The library table scrolls within its focusable region. Storefront image check found no broken loaded images.
- Standalone Playwright suite has not been run; existing purchase scenario locators were updated for the new avatar/logout controls.

## Deliberate scope limits

Wallets, balances, cashback, loyalty, favorites and support workflows are previews only. Login history, connected applications and MFA remain deferred. The supported 54 products/types/platforms remain unchanged; no unsupported eGift catalog, upcoming-game lifecycle, financial transactions or real activation was added. The interface is Eneba-inspired rather than a pixel-exact reproduction.


Fuzzy-search follow-up: added typo-tolerant library product-name matching with exact/literal matches ranked first and literal order-number matching. Regression suite: 22 tests / 289 assertions passed, including typo matching, literal percent handling, and isolation for fuzzy queries. Type check, PHP formatting and production build passed. Connected-browser query `cybrpunk` retrieved the existing Cyberpunk 2077 purchase.


Global-search follow-up: removed the previous two-character suggestion gate from UI/API. Connected-browser checks confirmed `x` displays Xbox product suggestions and `cybrpunk` finds Cyberpunk 2077 in the main header dropdown. Regression suite: 22 tests / 301 assertions passed; type checking, build and PHP formatting passed.
