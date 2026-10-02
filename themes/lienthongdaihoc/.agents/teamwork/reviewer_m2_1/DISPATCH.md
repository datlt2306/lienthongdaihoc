## 2026-10-01T09:46:38Z
You are reviewer_m2_1 (M2 Data Flow & Query Reviewer).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m2_1/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Review code changes in `inc/core/class-helpers.php` (specifically `ltdh_get_school_training_types()`), `single-school.php`, and `single-major.php`.
Verify:
1. Exactly 3 core CPTs preserved, 0 new CPTs registered or created.
2. School training types rollup exclusively from published in-scope programs; Step 1 checking direct school terms is eliminated.
3. `single-school.php` and `single-major.php` queries enforce `'post_status' => 'publish'` and `tax_query` for allowed training types (`tu-xa`, `vua-hoc-vua-lam`).
4. Run `php -l` on modified files.

Write your review report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m2_1/handoff.md

Send message when complete.
