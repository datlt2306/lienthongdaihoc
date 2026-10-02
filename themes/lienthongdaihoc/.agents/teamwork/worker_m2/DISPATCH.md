## 2026-10-01T09:38:25Z
You are worker_m2 (Data Flow & Campus Isolation Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_2/handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `inc/core/class-helpers.php`, `single-school.php`, `single-major.php`, `single-program.php`, `inc/comparison.php`, and `taxonomy.php`.

Task Requirements (Milestone M2):
1. Strictly preserve exactly 3 core CPTs: `school`, `major`, `program`. NEVER register or create any new CPTs.
2. In `inc/core/class-helpers.php`:
   - Refactor `ltdh_get_school_training_types( $school_id )`:
     * Eliminate Step 1 (checking terms directly on school post).
     * Roll up training types exclusively from published, in-scope programs where `school_relationship = $school_id` and training type term slug is in `['tu-xa', 'vua-hoc-vua-lam']`.
   - Refactor `ltdh_get_program_learning_details( $program_id )`:
     * Filter out `online` from physical campuses (`$campuses`).
     * If physical campuses exist, return them. If no physical campus exists and training type is `tu-xa`, return 'Toàn quốc' (or 'Trực tuyến toàn quốc'), NEVER 'Online'.
     * Set `mode` to 'Học online 100%' (for `tu-xa`) or 'Học tập trung / Cuối tuần' (for `vua-hoc-vua-lam`).
3. In `single-school.php` (lines 365-404) and `single-major.php` (lines 348-373):
   - Ensure program queries enforce `'post_status' => 'publish'` and filter by allowed Liên thông training types (`tu-xa`, `vua-hoc-vua-lam`).
   - Remove artificial `posts_per_page => 10` limits on program listings so all valid offerings appear.
4. In `single-program.php:266-268` and `inc/comparison.php:168`:
   - Ensure campus rendering uses sanitized output (never displays "Online" under physical location).
5. In `taxonomy.php:220`:
   - Fix corrupted link syntax `<a href="<"'?php the_permalink(); ?>"'>"`.
6. Run `php -l` on all modified files. Verify syntax and logic.

Write your complete handoff report to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/handoff.md
When finished, send a completion message with summary and verification evidence.
