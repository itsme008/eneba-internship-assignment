# AI conversation history

The assignment requires complete prompts and responses. A shareable link or summary is not a replacement for the Markdown files in [ai-history](ai-history/).

## Preserved material

All five identified Codex project threads are exported verbatim, with role/timestamp labels. The author confirmed no other AI conversations were used. [The index](ai-history/index.json) lists filenames, cutoff times, message counts and hashes. Each thread has a verification manifest. Overlapping inherited context is retained.

All 23 inline image occurrences are decoded and hash-verified in a private local backup; see [inline attachment inventory](ai-history/inline-attachments.json). The original assignment PDF, draft PRD PDF and recording are privately preserved; see [original attachment inventory](ai-history/attachments.json). Binaries and raw logs are excluded from GitHub.

## Refresh and verify

Run `python3 scripts/export-history.py --source /absolute/path/to/project-session.jsonl` for every project thread. The script verifies exact ordered user/assistant message text and records hashes/counts; it also preserves an immutable raw snapshot under ignored `.runtime/ai-history/raw`.

Exports include recorded user messages and assistant responses/progress updates. Tool items and hidden reasoning are excluded from the Markdown transcript. Non-text attachments are explicitly marked and inventoried separately. Never edit the transcript text silently or replace it with a summary.

Every export states its cutoff. An export made during an active turn cannot include that turn's later response; refresh once more after the final response has been saved. The private `.runtime/submission/eneba-conversation-archive.tar.gz` bundles Markdown/manifests and original attachments. Rebuild it after refreshing exports. A second off-machine backup is still the author's responsibility.

The repository and archive contain personal contact details. The source repository remains private; evaluator access must be arranged before submission. Original superseded planning documents remain recoverable in Git history and `.runtime/submission/documentation-before-cleanup.tar.gz`.
