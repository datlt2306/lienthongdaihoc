## 2026-10-01T11:35:41Z
You are auditor_m6 (Master Forensic Integrity Auditor).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m6/handoff.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/TEST_READY.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/auditor_m6/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Perform the final master forensic integrity audit on the entire refactored theme across all 5 requirements (R1–R5) prior to Victory Audit Handover to Sentinel:
1. Verify that 100% of the site scope is authentically dedicated to Liên thông đại học (Từ xa & Vừa học vừa làm).
2. Verify zero hard deletions of data (`wp_delete_post()` and SQL `DELETE` count = 0).
3. Verify that the 3 core CPTs (`school`, `major`, `program`) are strictly preserved and 0 unauthorized CPTs exist.
4. Verify that zero facades, dummy stubs, test-sniffing functions, or hardcoded cheats exist in theme source code.
5. Verify that `TEST_READY.md` and `audit_report.json` are authentic and valid.

Output binary verdict: `CLEAN` or `INTEGRITY VIOLATION` with full forensic evidence to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/auditor_m6/handoff.md

Send message when complete.
