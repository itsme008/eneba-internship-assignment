# Mandatory sign-in restored — 13 November 2026

## Decision

The user explicitly restored the assignment's requirement to sign in before payment. R06 supersedes the new-guest-payment part of R04. Purchase-key emails remain enabled for authenticated buyers. Guest browsing and persistent cart preparation remain supported.

## Changes

- Checkout UI requires authentication and redirects guests to login with a checkout return path; registration carries the same return path.
- Quote and payment routes require authentication. The checkout service also rejects missing-user/guest actors, preventing route bypass or reuse of old guest quotes.
- Signed-in quotes use the account cart revision and account email; guest email/items are prohibited.
- The payment page displays the account delivery email and removes guest email capture/post-purchase registration messaging for new orders.
- Existing guest orders, encrypted keys, sessions and the session-proof claim/read mechanism remain intact. No destructive migration, rollback or stock reset was performed.
- Active planning documents and README record R06 and preserve R04 as historical behavior.

## Verification

Backend regression suite: **21 tests passed, 280 assertions**. Current coverage includes anonymous quote/payment rejection (including an existing historical guest session and valid account quote), direct service guards, signed-in success/decline/email retry/replay, legacy order privacy/claim behavior, cart merge/ownership/stock/concurrency, and discovery/search/library behavior. The old guest-purchase success tests were replaced by current-policy tests and historical fixtures.

TypeScript checking, production build and PHP formatting passed. Playwright scenarios were updated to expect required sign-in; the standalone runner has not been executed.

Connected-browser automation on an explicitly email-disabled local QA server verified guest add-to-cart, login redirect, registration, return to checkout with exactly one original item/seller, signed-in quote/payment, onscreen demo key, automatic appearance in account order history, logout blocking checkout again, and sign-in for subsequent retrieval. No real email was sent during this QA.
