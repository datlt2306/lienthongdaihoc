## 2026-10-01T10:24:03Z
You are worker_m3_iter2 (Taxonomy & Routing Remediation Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m3_iter2/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_2/handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `taxonomy-training_type.php`, `archive-program.php`, `inc/eligibility.php`, `inc/core/class-menus.php`, and `inc/core/class-helpers.php`.

Task Requirements (Address the 4 concrete issues from challenger_m3_2):
1. In `taxonomy-training_type.php:379` and `archive-program.php:379`:
   - Change `Hệ <?php echo esc_html( $type_name ); ?>` to `<?php echo esc_html( $type_name ); ?>`.
2. In `inc/eligibility.php:306, 479, 482`:
   - Line 306: Change 'Hệ đào tạo không hợp lệ.' to 'Hình thức học không hợp lệ.'.
   - Line 479: Change 'Hệ đào tạo ' . ltdh_elig_get_training_label(...) to 'Hình thức học ' . ltdh_elig_get_training_label(...).
   - Line 482: Change 'Hỗ trợ hệ đào tạo ' . ltdh_elig_get_training_label(...) to 'Hỗ trợ hình thức học ' . ltdh_elig_get_training_label(...).
3. Whitelist allowed modes to `['tu-xa', 'vua-hoc-vua-lam']`:
   - In `taxonomy-training_type.php:135` and `archive-program.php:135`: filter/whitelist `$all_types` to only allowed slugs (`['tu-xa', 'vua-hoc-vua-lam']`).
   - In `inc/core/class-menus.php:148`: filter `$types` to only allowed slugs (`['tu-xa', 'vua-hoc-vua-lam']`).
4. In `inc/core/class-helpers.php:407`:
   - Update comparison breadcrumb from `home_url( '/he-dao-tao/tu-xa/' )` labeled 'Chương trình' to `home_url( '/he-dao-tao/' )` labeled 'Hình thức học'.
5. Verification:
   - Run `php -l` on all modified files.
   - Run `php tests/test-m3-label-facets-empirical.php` and verify that all 24 assertions pass with 0 failures!

Write your complete handoff report to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m3_iter2/handoff.md
When finished, send a completion message with summary and verification evidence.
