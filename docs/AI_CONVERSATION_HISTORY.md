# Full AI conversation preservation

## Requirement and current status

The assignment requires full agent conversations, including prompts and responses. The project author requires Markdown rather than relying on a shareable link. A PRD, summary, decision log, or prompt-only history does not satisfy this requirement.

Current thread: `01a1cf6d-e835-7bc1-9bc7-f766b3400db3`.

Observed local source:
`/Users/daraabpathania/.codex/sessions/2026/11/12/rollout-2026-11-12T13-04-09-01a1cf6d-e835-7bc1-9bc7-f766b3400db3.jsonl`

This path was verified locally; it is not a portable guarantee for every Codex installation. A refreshed working Markdown snapshot is saved in `ai-history/01a1cf6d-e835-7bc1-9bc7-f766b3400db3.md`. It contains recorded user/assistant messages through its stated cutoff, including assistant progress messages. It is not a final or continuously updated export.

## Preservation procedure

1. Preserve each project thread, including earlier exploration chats and conversations in other AI tools. Maintain a session inventory with source, exported filename, date range, and any known gap. Do not assume this thread includes earlier discussions.
2. At each completed working session, copy that project's original session log to a private backup outside the live session store. Keep the original source; do not replace it with summaries or edit it in place.
3. Export recorded user and assistant message items verbatim, chronologically, with role and timestamp labels. Include both progress messages and final answers. Use one canonical message representation rather than duplicating event mirrors. Preserve attachment references and explicitly inventory any attachment whose content is not embedded.
4. Retain tool calls/results in the private raw source. If a submission export includes a tool appendix, identify it separately from the user/assistant transcript. Hidden reasoning and internal platform instructions are not user-visible conversation messages and should not be invented or presented as such.
5. Reconcile exported messages against the source by role, order, count, and exact text. Check the first project prompt and latest completed response manually. Flag malformed/truncated records and missing attachment content; do not silently omit them.
6. Keep a second backup of the raw logs and Markdown exports. Record SHA-256 hashes and export cutoff timestamps so the snapshot can be checked later. Version Markdown snapshots with project documentation after checking for accidentally included credentials; document any necessary redaction instead of silently altering conversation text.
7. Before submission, regenerate exports after the last substantive work response has been persisted. The export made during an active turn cannot include that turn's later final response. Verify all sessions, link them in an index, and submit actual `.md` file(s) with the repository and deployed URL.

A repeatable exporter now exists at `scripts/export-history.py`. Run it with `--source /absolute/path/to/session.jsonl` from this workspace. It verifies exact ordered canonical message text, writes role counts/source and Markdown hashes, and saves an immutable source snapshot under ignored `.runtime/ai-history/raw`. It is not a background job; no ongoing capture guarantee is claimed. Run it after completed sessions and once more after the final response persists. Keep a second backup outside this machine before submission.

## Fallback if local logs are unavailable

Official OpenAI documentation describes stored thread history via app-server `thread/turns/list` with `itemsView: full`, or `thread/items/list`. Retrieve every page through the final cursor; do not use summaries as full transcripts. Availability depends on the installed version and accessible thread store. This method has not been executed here.

Source: [Official Codex app-server documentation](https://learn.chatgpt.com/docs/app-server).

If neither stored source is accessible, copy all visible prompts and responses manually into Markdown and reconcile them against the chat UI. Mark completeness unverified until checked. A shareable link can supplement, but does not replace, the project Markdown archive.

## Session inventory

| Session | Source | Export | Coverage |
| --- | --- | --- | --- |
| Current project thread, `01a1cf6d-e835-7bc1-9bc7-f766b3400db3` | Verified local JSONL above | `ai-history/01a1cf6d-e835-7bc1-9bc7-f766b3400db3.md` | Working snapshot through manifest cutoff; final refresh required. |
| Earlier exploration/discussion sessions | Not yet identified | Missing | Recovery and completeness check pending. |



Latest refresh and exact message counts/hashes are recorded in `ai-history/manifest.json`. This active turn’s later responses, original attachment binaries and earlier sessions still require final preservation. Planning-document archives are separate from conversation history.


## Recovered local project sessions — 13 November 2026

Five locally recorded project threads are now exported under docs/ai-history. See index.json for every Markdown filename, cutoff, message counts and per-thread hash manifest. Inherited context may overlap between threads; full thread exports are preserved rather than silently deduplicated. manifest.json remains the latest-export compatibility manifest, while each thread also has its own .manifest.json.

Recovered IDs: 01a1cf6d-e835-7bc1-9bc7-f766b3400db3; 01a1cf6d-eb66-73d1-b191-88efd750807b; 01a1d131-b78b-7ca2-b0db-13073dea7b5d; 01a1d259-a8a0-7e30-bef8-a6fd65c64fe8; 01a1d2bf-a7f6-7d02-8600-40c5d4818f80.

These exports verify recorded text within the identified files, not completeness of conversations in other apps or original attachment binaries. Any earlier ChatGPT/external AI chats still need author identification/export. The final saved assistant reply requires a final refresh. Secret-pattern checks found no API keys/private keys/credential-bearing Neon URLs in the export set; the transcript still contains personal contact details and should not be made public without explicit approval.


Original assignment PDF, draft PRD PDF and supplied movie are now copied to ignored .runtime/submission/attachments with hashes in ai-history/attachments.json. Inline screenshot binaries and any other-app conversations still require reconciliation. These private attachment backups are not staged for publication.
