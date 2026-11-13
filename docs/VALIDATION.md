# Hosted release verification — 13 November 2026

Live application: https://eneba-marketplace-demo.onrender.com  
Private source: https://github.com/itsme008/eneba-internship-assignment  
Render service: `srv-dbr8epdckfvc73dhlc80`, Docker, Frankfurt, free compute.  
Deployed revision: `b9df8df`; CI run `43359294707` passed. Application changes were verified by CI run `43358789563`: 21 backend tests / 286 assertions, three Playwright tests, PHP formatting, TypeScript, frontend production build and Docker build.

## Hosted checks passed

- Database readiness endpoint returns HTTP 200 with `ready`; React assets and deep routes load over HTTPS.
- Session cookie has Secure, HttpOnly and SameSite=Lax attributes. XSRF cookie has Secure and SameSite=Lax; it is intentionally readable for CSRF request headers. Login POST without CSRF proof returns HTTP 419 `CSRF_TOKEN_MISMATCH`.
- Backend homepage and product/seller data load. Query `x` returns Xbox suggestions; `mincraft` returns Minecraft.
- A guest cart survives refresh; checkout redirects to sign-in before payment. Registration with the explicitly approved demo email preserves the seller and cart, then returns to checkout.
- One authorized simulated purchase completed for order `EN-BTBG3O3UPJ`. One key was delivered onscreen. Brevo accepted the email, and the author confirmed receipt with the matching key. No money or payment credentials were collected.
- Logout hides the order; signing in again retrieves the same key. Account library lists the real purchased product, seller, price and order.
- Render Events records a service restart at 05:49 Europe/Vilnius. After restart, the session and same decrypted key remain available. Read-only Neon checks before/after agree: completed order, quantity one, seller stock 22, one key, email status sent with one attempt. No reseed, duplicate purchase or duplicate email occurred.
- Live catalogue at an actual 390px viewport filters to 15 gaming gift cards; document width remains 390px. Temporary viewport override was reset. A later optional screenshot interaction timed out; no screenshot artifact is claimed.

## Remaining submission steps

Arrange evaluator access to the private repository, complete the author's visual/code walkthrough, refresh conversation exports after the final saved response and copy the archive to a second location, then obtain explicit approval for the submission reply. No submission email has been sent. Free Render cold starts can delay the first request after inactivity.

The demo buyer credentials generated for this verification are stored only in ignored `.runtime/live-qa-account.json` with restricted permissions; no password is in source or this report. The demo account/order intentionally remain for persistence verification.
