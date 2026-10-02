## 2026-10-01T11:03:20Z

You are worker_m5 (Templates & Program Presentation Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m5/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `taxonomy-training_type.php`, `archive-program.php`, `inc/core/class-query-filters.php`, `single-program.php`, `single-school.php`, `single-major.php`, `template-parts/banner.php`, and `template-parts/compare/program-cards.php`.

Task Requirements (Milestone M5):
1. Standardize Program Cards across SSR and AJAX:
   - In `taxonomy-training_type.php:384-406`, `archive-program.php:384-406` (SSR cards) and `inc/core/class-query-filters.php:240-275` (AJAX cards):
     * Enforce structural and visual parity between SSR and AJAX card rendering.
     * Headline link MUST use the standard admission opportunity formula:
       `"Liên thông ngành " . esc_html( $major_name ) . " - " . esc_html( $type_name )`
       with school name clearly shown as institution subtitle/header.
     * Badge in cover header displays clean `$type_name` (e.g. "Từ xa", "Vừa học vừa làm") with 0 "Hệ " prefix.
     * Display tuition (`$tuition`), duration (`$duration`), learning details (`ltdh_get_program_learning_details`), and admission status cleanly.
   - In `single-school.php:480-530` and `single-major.php:450-500`:
     * Align program card items in school and major single views to the same formula and presentation.
   - In `template-parts/compare/program-cards.php`:
     * Align card title to the standardized formula.
2. Single Program Template (`single-program.php`):
   - Line 195: Replace obsolete legacy notice `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."` with an appropriate in-scope notice: `"Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này."`.
   - Verify that admission requirements, tuition, duration, and delivery mode are displayed with "Hình thức học" terminology.
   - Verify that campus location never displays "Online" (shows physical stations or "Toàn quốc" per M2 helper).
3. Study Mode Archives (`taxonomy-training_type.php` / `/he-dao-tao/tu-xa/` & `/he-dao-tao/vua-hoc-vua-lam/`):
   - Ensure archive templates query and render exclusively valid published programs (95 in-scope programs).
   - Verify headings and subtitles reflect "Hình thức học: [Tên hình thức]".
4. Banner Template Cleanup (`template-parts/banner.php`):
   - Review lines 26–27, 78–79, 90–91, 105–106. Ensure banner titles and subtitles do NOT contain "Văn bằng 2", "Chính quy", or "hệ đào tạo". Update subtitles to: `"Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm"` or similar 100% in-scope description.
5. Testing & Verification:
   - Run `php -l` on all modified files.
   - Write and execute an empirical test script `tests/test-m5-templates-presentation.php` verifying card headline formula parity across SSR and AJAX, single program notice cleanup, banner text purity, and study mode archive queries.
   - Run prior regression suites (M2, M3, M4) to ensure zero regressions.
