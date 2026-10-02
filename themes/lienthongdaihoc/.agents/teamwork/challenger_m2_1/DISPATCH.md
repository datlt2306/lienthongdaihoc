## 2026-10-01T09:46:38Z
You are challenger_m2_1 (M2 Query & Rollup Challenger).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m2_1/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Empirically test queries via WP-CLI:
1. Call `ltdh_get_school_training_types()` for multiple schools (e.g. UTC ID 1853, HOU ID 1653, TVU ID 1611) and verify the returned terms reflect only published in-scope programs.
2. Test queries on `single-school.php` and `single-major.php` logic to confirm zero draft/out-of-scope programs appear.
3. Verify that School 1853 (UTC) returns exactly `tu-xa` and `vua-hoc-vua-lam` (and does NOT return `chinh-quy`).

Write your report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m2_1/handoff.md

Send message when complete.
