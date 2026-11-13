# Neon deployment connections

Neon supplies two PostgreSQL connection strings, not two API keys. Both credentials remain in ignored .env.deploy.

- `DB_URL`: direct Neon connection, used for controlled migrations and one-time seed.
- `DATABASE_URL_POOLED`: pooled Neon connection with `-pooler` in the hostname. Use this value as Render's `DB_URL` for the running Laravel application; Laravel currently reads `DB_URL`, not `DATABASE_URL_POOLED` automatically.

Both connections must use TLS and identify the same database. Deployment uses PostgreSQL port 5432 explicitly; the local cluster uses a different port. Keep local .env pointed at the local database; never paste deployment strings into source, chat, shell command arguments or public logs. APP_KEY in .env.deploy is the stable deployment-only encryption key and must remain unchanged across releases.

Database setup uses direct TLS access, applies migrations once and seeds the 54 demo products once. The pooled connection is then verified with an application purchase inside an outer transaction that is rolled back, leaving no test buyers/orders or stock changes. No password-reset flow or real payment is introduced. Render service creation/deployment remains on hold until separately approved.

Reference: [Neon connection pooling](https://neon.com/docs/connect/connection-pooling).

## Verified setup status

The direct connection successfully applied migrations and seeded 30 games, 15 gift cards and 9 subscriptions. All 54 products have the expected six or seven seller offers; eight homepage blocks and `pg_trgm` are present.

The pooled connection passed an application purchase inside a rolled-back transaction. Verification left zero buyers and zero orders and preserved stock.

Neon CLI 9.4.0 is installed. Agent setup uses the existing Node.js 22.23.1 installation, satisfying Neon's Node.js 22.20+ requirement for skills installation. CLI browser authentication completed successfully. The user-scoped `neon-postgres` Codex plugin version 3.0.8 is installed with Neon skills and MCP configuration. Restart Codex to load the integration; the MCP connection may request its own authorization when first used.

Setup instructions: [Neon agent setup](https://neon.com/prompts/agent-setup.md).

## Agent configuration audit

Followed the official [Neon overview skill](https://neon.com/.well-known/agent-skills/neon/SKILL.md). The global `neon-postgres@plugins-cli` plugin is enabled in Codex's user configuration. Its bundled skills cover Neon overview, Postgres, Auth, Object Storage, Functions, AI Gateway and Realtime. Its MCP configuration uses OAuth at `https://mcp.neon.tech/mcp`, with no API key embedded in the plugin configuration. No duplicate project-level skills or MCP server were added.

Linked this workspace to the existing `my-first-project` project and `production` branch using `--no-env-pull --no-config`. This created local `.neon` context and added `.neon` to `.gitignore`. Existing application environment files, other Codex plugin/server settings and database infrastructure were preserved. No new database, branch, service or `neon.ts` was provisioned by agent configuration.

For future branch work, use `--no-env-pull` with `neon link` and `neon checkout` to preserve the separate local/deployment environment files. Use an isolated development branch before schema experiments; the linked production branch is the deployment target.

### MCP registration after reload

Reload made the plugin skills available, but no Neon MCP tools were exposed and Codex's MCP registry contained no Neon server. Registered an explicit user-level OAuth server named `Neon`, scoped to this project at `https://mcp.neon.tech/mcp?projectId=fancy-block-46613795`, using the official Neon CLI. Existing plugin and other server settings were retained. This explicit server supplements the plugin's bundled configuration because the latter was not registered by this runtime. Codex reported successful OAuth login to the MCP server `Neon`. CLI authentication remains separate. A further reload is needed to expose the newly registered server tools in this active conversation.

### MCP verification completed

After reload, the Neon MCP tools became available. Read-only MCP requests successfully resolved the existing production branch and confirmed 54 products and the installed `pg_trgm` extension. Agent setup is complete; no database data was changed by these checks.
