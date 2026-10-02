## 2026-10-01T11:21:44Z
You are reviewer_m5_2 (Single Program & Archives Reviewer).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m5/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m5_2/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Review code changes for `single-program.php`, study mode archive query restrictions, and banner template text purity:
- `single-program.php`
- `taxonomy-training_type.php` (tax query logic)
- `archive-program.php` (tax query logic)
- `template-parts/banner.php`

Verification Requirements:
1. In `single-program.php`: Verify replacement of obsolete legacy notice referring to "hệ Chính quy" (line 195) with an in-scope Liên thông notice. Verify delivery mode displays "Hình thức học" and campus location guards against "Online".
2. In `taxonomy-training_type.php` and `archive-program.php`: Verify default `tax_query` enforces allowed training types `['tu-xa', 'vua-hoc-vua-lam']`.
3. In `template-parts/banner.php`: Verify zero mentions of "Văn bằng 2", "Chính quy", or "hệ đào tạo". Verify "Hệ " is stripped from taxonomy terms.
4. Run `php -l` on modified files.

Write your review report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m5_2/handoff.md

Send message when complete.
