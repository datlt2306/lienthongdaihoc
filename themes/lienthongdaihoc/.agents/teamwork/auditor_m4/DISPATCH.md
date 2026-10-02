# Dispatch Instructions: auditor_m4 (M4 Forensic Integrity Auditor)

## Objective
Perform forensic integrity audit on worker_m4's changes to ensure genuine implementation, zero facades, zero hardcoding, zero data deletions, and zero regressions.

## Verification Requirements
1. Review git diff across all modified files (`header.php`, `footer.php`, `front-page.php`, `inc/config/class-defaults.php`, `inc/core/class-menus.php`).
2. Verify WP-CLI menu modifications in database are genuine and intact.
3. Verify zero test-sniffing or dummy implementations.
4. Verify that zero posts/taxonomies were deleted.
5. Check for any integrity violations.

Provide binary verdict: `CLEAN` or `INTEGRITY VIOLATION`.

## 2026-10-01T10:54:57Z
You are auditor_m4 (M4 Forensic Integrity Auditor).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m4/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/auditor_m4/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Perform forensic integrity audit on worker_m4's changes:
1. Review git diff across all modified files (`header.php`, `footer.php`, `front-page.php`, `inc/config/class-defaults.php`, `inc/core/class-menus.php`).
2. Verify WP-CLI menu modifications in database are genuine and intact.
3. Verify zero test-sniffing or dummy implementations.
4. Verify that zero posts/taxonomies were deleted.
5. Check for any integrity violations.

Output binary verdict: `CLEAN` or `INTEGRITY VIOLATION` with full forensic evidence to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/auditor_m4/handoff.md

Send message when complete.
