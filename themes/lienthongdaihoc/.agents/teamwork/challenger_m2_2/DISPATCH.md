## 2026-10-01T09:46:38Z
You are challenger_m2_2 (M2 Campus Isolation Challenger).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m2_2/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Empirically test campus isolation via WP-CLI / eval scripts:
1. Call `ltdh_get_program_learning_details()` on programs that have `campus = online` (e.g. IDs 1800, 1795, 1791, 1785). Verify that `$details['campus']` is 'Toàn quốc' or physical stations, never 'Online'.
2. Verify that comparison table helper `inc/comparison.php` never outputs 'Online' under "Cơ sở / Trạm đào tạo".
3. Check `taxonomy.php:220` and verify that no PHP syntax or parse error occurs.

Write your report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m2_2/handoff.md

Send message when complete.
