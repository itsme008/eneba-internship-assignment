# Render deployment proposal

Status: prepared for author approval; no service created yet.

- Source: private `itsme008/eneba-internship-assignment`, branch `main`.
- Service: `eneba-marketplace-demo`, Docker, Frankfurt, free compute ($0/month, 0.1 CPU, 512 MB RAM).
- Build: repository Dockerfile; Apache serves compiled React and Laravel on port 10000.
- Health endpoint: `/api/v1/health/ready`.
- Automatic deployment: after CI checks pass.
- Database: existing initialized Neon production branch; use saved pooled URL as `DB_URL`, TLS, explicit port 5432. No repeat seeding.
- Secrets to transfer to Render: deployment `APP_KEY`, pooled Neon `DB_URL`, Brevo API key and verified sender address. Credentials remain outside GitHub and conversation text.
- Runtime: production mode, debug off, secure encrypted database sessions, database cache, synchronous email delivery with durable retry state; configuration follows `render.yaml`.
- Public URL: Render-assigned HTTPS hostname; set `APP_URL` to the actual hostname.

Approval authorizes creation of this new free public demo service and storage of the named credentials as Render environment secrets. Existing services remain untouched. Cold starts are expected after inactivity; accounts, orders, stock and encrypted keys persist in Neon.

After deployment, verify health, HTTPS/session/CSRF, search, sign-in-required checkout, purchase/key retrieval, and persistence across restart. Real test emails need an explicitly authorized recipient. The full conversation archive remains in the private source repository and is excluded from the Docker build context.
