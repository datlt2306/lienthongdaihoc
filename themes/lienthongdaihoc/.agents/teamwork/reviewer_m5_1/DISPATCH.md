## 2026-10-01T11:21:44Z
You are reviewer_m5_1 (Program Cards Reviewer).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m5/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m5_1/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Review code changes for Program Card standardization across SSR, AJAX, and single view templates:
- `taxonomy-training_type.php`
- `archive-program.php`
- `inc/core/class-query-filters.php`
- `single-school.php`
- `single-major.php`
- `template-parts/compare/program-cards.php`

Verification Requirements:
1. Verify 1:1 structural and visual parity between SSR cards and AJAX cards:
   - Headline formula: `"Liên thông ngành " . esc_html( $major_name ) . " - " . esc_html( $type_name )` (with redundant prefixes stripped).
   - Institution name and logo header.
   - Badge displays clean `$type_name` without "Hệ " prefix.
   - Tuition, duration, learning details (`ltdh_get_program_learning_details`), and admission status.
   - Compare toggle attributes present on both SSR and AJAX cards.
2. Verify program cards in `single-school.php` and `single-major.php` implement the standardized opportunity title.
3. Run `php -l` on modified files.

Write your review report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m5_1/handoff.md

Send message when complete.
