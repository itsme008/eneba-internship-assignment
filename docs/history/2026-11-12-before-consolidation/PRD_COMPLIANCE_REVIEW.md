# Approved PRD: assignment coverage review

Reviewed: 2026-11-12. Source: both pages of `Eneba Internship Assignment.pdf`, including the purchase-flow diagram previously visually reviewed. PRD approved by Daraab; this review assesses documented requirements, not working software. Application implementation remains unapproved.

| Assignment reference | Approved coverage | Finding / required evidence later |
| --- | --- | --- |
| Store | D02a–g, D03c–h, D38: all 54 products, five filters, seven catalog sorts, search and numbered pages. | Covered. Genre applies to games; explicit sorting takes precedence over search relevance. |
| Homepage | D03a–b, D10, D36: banner, shortcuts, curated featured products, top games, gaming cards, recent history, shared chrome. | Covered with interpretation: block-specific backend content services may live in one application and use one database. Explain this choice; no hardcoded frontend block product lists. |
| Search anywhere, names, partial/typo matching | D03f–g, D38: global live suggestions and results; accepted Minecraft examples. | Covered. PostgreSQL trigram search selected during architecture review, superseding the earlier Elasticsearch preference. |
| Product details and several sellers | D02d, D26, D31: 6–7 offers per product, recommended/other offers and purchase panel, static gallery. | Covered. Mock seller ratings and review content are identified as demo data. |
| Cart, payment, key delivery | D13–19, D24–25, D32: stock-limited quantities, whole-cart purchase, simulated methods/outcomes, unique keys, ownership, no duplicate purchase. | Covered. All-or-nothing backend transaction and replay protection needed; visual payment brands are not actual payment integrations. |
| Registration/sign-in and protected checkout/history | D01a, D20–23, D37. | Covered. Email verification is excluded; password-reset email is additional user-selected scope and remains required. |
| Eneba styling on every screen | Reference reviews, D26, D30–33. | Covered as design intent. Validate actual responsive screenshots and keyboard flow later. Visual mocks do not substitute for required behavior. |
| React frontend, TypeScript preferred | C03. | Recorded. Architecture recommends React + TypeScript; version pinning deferred to implementation approval. |
| PHP or Go backend, REST or GraphQL | C04. | Recorded. PHP + Laravel confirmed during architecture review; REST recommended. |
| Database | C05, stock/orders/account persistence. | Recorded. PostgreSQL recommended in architecture; schema follows in DATABASE.md. |
| Explainable code and justified scope | A12, curated data, simulated payments, exclusions. | Covered in intent. Include rationale, setup, testing and known limits in final README. |
| Seven-day submission; reply to registration email | C01–02. | Provisional cutoff 2026-11-16 14:30 Europe/Vilnius, based on assumed receipt timezone; target 2026-11-14–15. No email submission has been authorized/performed. |
| GitHub project links | A09. | Mandatory deliverable; no repository publication or implementation yet. |
| Public free-tier deployment | A10. | Mandatory. Actual free-tier resource availability, persistence, sleep and provider limits must be checked at deployment. |
| Full prompts and responses | A11, U04, AI_CONVERSATION_HISTORY.md. | Markdown required by user. Snapshot exists but final export and prior-session inventory remain incomplete. A link alone is insufficient. |

## Reconciled contradictions

Later confirmed decisions override older draft alternatives: three product types only; sample read-only reviews; shared curated recommendations; same-browser recently viewed separately included; real-email password recovery; no key/receipt email; fixed-rate multi-currency checkout; visual-only cashback; no wishlist or stock reset. The older author PDF's “Should Have” label does not demote explicitly selected account/recovery functionality.

PRD discovery text retains historical open notes even where a later D decision resolved them. Use the confirmed D sections and decision log as the baseline. Do not treat the approval as permission to add every candidate feature or as permission to implement.

## Design assumptions requiring visibility

The exact country list/defaults, demo FX rates/rounding, details of search thresholds, page/URL controls, stock initialization, logout/cart conflict handling, and homepage/offer display counts were not individually decided. Design documents may propose concrete defaults; label them as design recommendations and preserve product scope. Real-email sender availability is an external prerequisite, not a mockable replacement under the approved PRD.
