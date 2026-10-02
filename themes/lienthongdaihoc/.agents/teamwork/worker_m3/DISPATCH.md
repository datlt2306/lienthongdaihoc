## 2026-10-01T09:58:46Z
You are worker_m3 (Taxonomy Label & Routing Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m3/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_2/handoff.md
4. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `inc/acf-import-cpts.json`, `inc/config/class-defaults.php`, `inc/core/class-menus.php`, `inc/core/class-helpers.php`, `taxonomy-training_type.php`, `archive-program.php`, `single-major.php`, `single-school.php`, `template-parts/compare/program-table.php`, `template-parts/compare/program-cards.php`, `template-parts/eligibility/wizard.php`, `inc/core/class-query-filters.php`, `taxonomy.php`, `template-parts/banner.php`, `inc/core/class-rewrite-rules.php`, `inc/seo/class-rankmath-integration.php`, and `assets/js/main.js`.

Task Requirements (Milestone M3):
1. Standardize frontend display of `training_type` taxonomy to "Hình thức học" across all theme files:
   - `inc/acf-import-cpts.json:171, 178, 179, 196-206`: change labels to "Hình thức học".
   - `inc/config/class-defaults.php:37`: change label to 'Hình thức học', preserve URL '/he-dao-tao/'.
   - `inc/core/class-menus.php:137`: update string check to match in_array($title, ['hình thức học', 'hệ đào tạo', 'hình thức đào tạo'], true).
   - `inc/core/class-helpers.php:432, 449, 459, 469, 472`: change breadcrumb labels from 'Hệ đào tạo' to 'Hình thức học'.
   - `taxonomy-training_type.php:172, 250` & `archive-program.php:172, 250`: update headings and filter labels from "Hệ đào tạo" to "Hình thức học".
   - `single-major.php:470`, `single-school.php:501`: change card row labels from "Hệ đào tạo:" to "Hình thức học:".
   - `template-parts/compare/program-table.php:66`, `template-parts/compare/program-cards.php:22`, `template-parts/eligibility/wizard.php:76`: update to "Hình thức học".
   - `inc/core/class-query-filters.php:236`: clean badge from " Hệ ..." to "".
   - `taxonomy.php:26, 62, 197`: update labels from "Hệ đào tạo" to "Hình thức học".
   - `template-parts/banner.php:26, 78, 90`: update banner titles from "Hệ Đào Tạo" / "Hệ đào tạo: " to "Hình thức học" / "Hình thức học: " or "Liên thông đại học - Hình thức ...".
   - STRICTLY PRESERVE all URL slugs: `/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`.
   - Strictly DO NOT create any redundant taxonomy "Loại tuyển sinh" or filter "Liên thông".
2. Routing & Rewrite Clean-up for `/chuong-trinh/`:
   - In `inc/core/class-rewrite-rules.php:247-254`:
     * Replace forced 301 redirect to `/he-dao-tao/tu-xa/` with a clean 301 redirect to `home_url( '/he-dao-tao/' )` preserving all query args ($_GET).
   - In `archive-program.php:180, 188, 255, 464`:
     * Update all form action, all_url, and reset links from `home_url( '/chuong-trinh/' )` to `home_url( '/he-dao-tao/' )`.
   - In `inc/seo/class-rankmath-integration.php`:
     * Ensure canonical URL for program archive points cleanly to `home_url( '/he-dao-tao/' )` to eliminate canonical redirect loops.
   - In `assets/js/main.js`:
     * Ensure form selectors match `form[action*="/he-dao-tao/"]` as well as `form[action*="/chuong-trinh/"]`.
3. Run `php -l` across all modified files. Verify syntax and test HTTP redirects/rewrite rules.
