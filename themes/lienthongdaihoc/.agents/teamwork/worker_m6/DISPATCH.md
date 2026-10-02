## 2026-10-01T11:28:24Z

You are worker_m6 (Master E2E Verification Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m6/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `tests/test-m6-e2e-master-acceptance.php` and publishing `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/TEST_READY.md`.

Task Requirements (Milestone M6):
1. Comprehensive Syntax Audit:
   - Run `php -l` on EVERY single PHP file in the theme. Verify 100% pass rate with zero syntax errors.
2. Master E2E Acceptance Test Runner:
   - Author and execute `tests/test-m6-e2e-master-acceptance.php` systematically asserting all 5 requirements:
     * R1: Exactly 95 published programs (94 Từ xa, 1 Vừa học vừa làm), 5 drafted out-of-scope programs (IDs 2013, 1786–1789), 1 drafted school (HCCT ID 1662), 20 published universities, 34 published majors, 0 hard deletes (0 calls to wp_delete_post or SQL DELETE), 0 ghost IDs in _offered_programs (1855, 1856 pruned), audit_report.json exists and is valid.
     * R2: Exactly 3 core CPTs, ltdh_get_school_training_types() rolls up from active in-scope programs, campus 'online' is isolated and never outputs as physical location, taxonomy.php:220 syntax clean.
     * R3: "Hình thức học" displayed across templates/breadcrumbs/banners/filters/eligibility, slugs /he-dao-tao/, /he-dao-tao/tu-xa/, /he-dao-tao/vua-hoc-vua-lam/ preserved, /chuong-trinh/ 301 redirect to /he-dao-tao/ preserving query args, Rank Math canonical set to /he-dao-tao/, 0 canonical loops.
     * R4: Menu ID 3 standardized in DB to 6 positions (Trang chủ -> Liên thông đại học [Từ xa, VHVL] -> Ngành học -> Trường đại học -> Kiến thức liên thông -> Kiểm tra điều kiện), footer Column 3 clean (0 dead '#' links, 0 out-of-scope offerings), homepage H1 / search form action / eligibility (0 THPT) / testimonials / news fallbacks clean, 0 redundant "Loại tuyển sinh" filters.
     * R5: Program card 1:1 parity between SSR and AJAX, standard headline formula "Liên thông ngành [Major] - [Type]", clean badges without "Hệ ", single-program.php legacy notice replaced, banner text clean.
3. Full Suite Execution:
   - Execute all test suites in the `tests/` directory:
     * `php tests/test-m2-empirical.php`
     * `php tests/test-m3-empirical.php`
     * `php tests/test-m3-adversarial.php`
     * `php tests/test-m3-forensic.php`
     * `tests/test-m3-label-facets-empirical.php`
     * `php tests/test-m4-navigation-homepage.php`
     * `php tests/test-m4-adversarial.php`
     * `php tests/test-m5-templates-presentation.php`
     * `php tests/test-m5-card-parity-adversarial.php`
     * `php tests/test-m5-challenger-empirical.php`
     * `php tests/test-m6-e2e-master-acceptance.php`
   - Record total passed assertions and confirm 0 failures.
4. Publish `TEST_READY.md`:
   - Write `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/TEST_READY.md` containing test runner commands, assertion totals, coverage summary, and requirement verification matrix.

Write your complete handoff report to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m6/handoff.md
When finished, send a completion message with summary and verification evidence.
