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

No automatic background exporter or backup job is installed. No ongoing capture guarantee is claimed. Until a repeatable export mechanism is approved and verified, explicitly request an updated snapshot at session boundaries and perform the final post-response export before submission.

## Fallback if local logs are unavailable

Official OpenAI documentation describes stored thread history via app-server `thread/turns/list` with `itemsView: full`, or `thread/items/list`. Retrieve every page through the final cursor; do not use summaries as full transcripts. Availability depends on the installed version and accessible thread store. This method has not been executed here.

Source: [Official Codex app-server documentation](https://learn.chatgpt.com/docs/app-server).

If neither stored source is accessible, copy all visible prompts and responses manually into Markdown and reconcile them against the chat UI. Mark completeness unverified until checked. A shareable link can supplement, but does not replace, the project Markdown archive.

## Session inventory

| Session | Source | Export | Coverage |
| --- | --- | --- | --- |
| Current planning thread, `01a1cf6d-e835-7bc1-9bc7-f766b3400db3` | Verified local JSONL above | `ai-history/01a1cf6d-e835-7bc1-9bc7-f766b3400db3.md` | Working snapshot through manifest cutoff; final refresh required. |
| Earlier exploration/discussion sessions | Not yet identified | Missing | Recovery and completeness check pending. |


Latest refresh: 2026-11-12T18:42:37.964Z. Verified inclusion of 50 recorded user messages and 100 recorded assistant messages through cutoff. Attachment binaries and later responses require final preservation. No continuous exporter or private raw-log backup installed.
