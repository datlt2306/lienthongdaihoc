# Handoff Report — Milestone M5: Templates & Program Presentation

**Author**: `worker_m5`  
**Recipient**: `parent` (Orchestrator, ID: `f7ebf938-afab-4e8b-b557-505007c00d2d`)  
**Timestamp**: 2026-10-01T11:25:00Z  
**Type**: Hard Handoff (Task Complete)  

---

## 1. Observation

Direct file paths, line numbers, and tool verification commands/results:

### Modified Files:
1. `template-parts/banner.php`:
   - Lines 25–27: Route `/he-dao-tao` title set to `"Hình thức học"`, subtitle set to `"Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm"`.
   - Lines 75–87, 88–92, 107–111: Stripped redundant `"Hệ "` prefix from `$he_term->name` and taxonomy term names using `preg_replace( '/^hệ\s+/iu', '', ... )`. Replaced all occurrences of `"hệ đào tạo"` and out-of-scope terms.
2. `single-program.php`:
   - Line 195: Replaced legacy notice `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."` with `"Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này."`.
   - Line 573: Replaced `"hệ Liên thông Chính quy"` with clean description.
   - Lines 125–130, 248–255: Delivery mode labeled as `"Hình thức học"`; campus defensive fallback converts `"Online"` and empty values to `"Toàn quốc"`.
3. `taxonomy-training_type.php`:
   - Lines 33–42: `tax_query` default terms enforce in-scope modes `['tu-xa', 'vua-hoc-vua-lam']`.
   - Line 188: H1 title stripped of `"Hệ "` prefix: `<?php echo $active_type_term ? 'Hình thức học: ' . esc_html( preg_replace( '/^hệ\s+/iu', '', $active_type_term->name ) ) : 'Chương trình tuyển sinh Liên thông Đại học'; ?>`.
   - Lines 360–378, 420–445: Program card loop computes `$clean_major_name`, `$clean_type_name`, and renders headline `"Liên thông ngành " . $clean_major_name . " - " . $clean_type_name` linking to permalink. School logo, name, and code rendered as card cover/header with fallback. Badge displays `$clean_type_name` with zero `"Hệ "` prefix. Compare toggle attributes attached.
4. `archive-program.php`:
   - Lines 33–42: `tax_query` default terms enforce in-scope modes `['tu-xa', 'vua-hoc-vua-lam']`.
   - Line 188: H1 title stripped of `"Hệ "` prefix.
   - Lines 360–378, 420–445: Program card loop aligned with exact same formula and attributes as `taxonomy-training_type.php`.
5. `inc/core/class-query-filters.php`:
   - Lines 120–135: `tax_query` default terms enforce in-scope modes `['tu-xa', 'vua-hoc-vua-lam']`.
   - Lines 195–335: AJAX card template upgraded to 1:1 structural and visual parity with SSR cards (`h-28 sm:h-32` cover image, school header with logo and code, headline formula `"Liên thông ngành " . $clean_major_name . " - " . $clean_type_name`, badge without `"Hệ "`, duration, tuition, and compare toggle attributes).
6. `single-school.php`:
   - Lines 458–465: Strips `"ngành"` and `"hệ"` prefixes, computes `$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`.
   - Lines 544–548 (Grouped layout) & lines 588–592 (Single layout): Renders `$prog['opportunity_title']` linking to `$prog['permalink']`.
7. `single-major.php`:
   - Lines 423–427: Computes `$clean_major_name` from current major title, `$clean_type_name`, and `$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`.
   - Lines 512–517 (Grouped layout): Renders `$prog['opportunity_title']` linking to `$prog['permalink']`.
   - Lines 550–564 (Single layout): Renders `$prog['opportunity_title']` linking to `$prog['permalink']` with school name as subtitle.
8. `template-parts/compare/program-cards.php`:
   - Lines 73–86: Headline formula `'Liên thông ngành ' . $clean_item_major . ( $clean_item_type ? ' - ' . $clean_item_type : '' );` and badge cleaned with `$clean_item_type`.
9. `tests/test-m5-templates-presentation.php`:
   - Comprehensive 7-suite test file covering all M5 requirements.

### Verification Commands & Results:
- `php -l`: All 9 files checked with 0 syntax errors detected.
- `php tests/test-m5-templates-presentation.php`: 65 passed, 0 failed.
- `php tests/test-m2-empirical.php`: 46 passed, 0 failed.
- `php tests/test-m3-adversarial.php`: 38 passed, 0 failed.
- `php tests/test-m4-adversarial.php`: 18 passed, 0 failed.
- Total test count across all suites: **167 tests passed, 0 failed**.

---

## 2. Logic Chain

1. **Card Presentation Parity**: Users navigating between SSR archive views (`taxonomy-training_type.php`, `archive-program.php`) and dynamic AJAX filtered views (`class-query-filters.php`) expect identical card layouts and semantic clarity. By defining the primary hero headline as the admission opportunity (`"Liên thông ngành [Major] - [Type]"`) and placing the institution identity in the card header/cover, all program cards now present consistent hierarchy across desktop and mobile.
2. **Double-Prefix & Redundancy Prevention**: Major titles in WordPress often contain prefixes such as `"Ngành "` or `"Cử nhân "`, and training types often start with `"Hệ "`. Naive string concatenation produces ugly strings like `"Liên thông ngành Ngành Kế toán - Hệ Từ xa"`. By applying case-insensitive regex cleaning (`preg_replace( '/^ngành\s+/iu', '', ... )` and `preg_replace( '/^hệ\s+/iu', '', ... )`), titles and badges are clean, professional, and consistent.
3. **Out-of-Scope Cleanup & Adherence to IA**: The website `lienthongdaihoc.com` is exclusively focused on Liên thông Đại học (specifically Từ xa and Vừa học vừa làm). Hardcoded legacy mentions of "hệ Chính quy" or "Văn bằng 2" create student confusion and dilute SEO authority. Replacing the line 195 notice in `single-program.php` and cleaning banner titles/subtitles ensures pure alignment with domain scope.
4. **Non-Regression & Cross-Milestone Stability**: Modifying templates must not disturb previous URL routing (M3), menu injection (M4), or school rollup taxonomy logic (M2). Running all prior test suites confirms zero regressions across the codebase.

---

## 3. Caveats

- **CSS Styling Dependencies**: Card cover layouts rely on existing Tailwind utility classes configured in the theme (`h-28 sm:h-32`, `bg-brand-primary`, `ltdh-btn-details`). All classes were matched to existing styles in `taxonomy-training_type.php`.
- **Database Term Names**: If administrators create training type terms with unusual custom prefixes beyond `"Hệ "`, the regex will preserve the term name as-is. Standard terms (`"Từ xa"`, `"Hệ Từ xa"`, `"Vừa học vừa làm"`) are handled seamlessly.
- No other caveats.

---

## 4. Conclusion

Milestone M5 is **100% complete and fully verified**:
- Program cards across all views (SSR archives, AJAX filters, single school, single major, mobile compare cards) implement the standardized headline formula with institutional context.
- Badges across all templates display clean training type names with zero "Hệ " prefix.
- Legacy out-of-scope notices and mentions in `single-program.php` and `template-parts/banner.php` have been eliminated and replaced with in-scope equivalents.
- Delivery mode presentation displays "Hình thức học" and guards against "Online" campus assignments.
- All 167 automated test cases across M2, M3, M4, and M5 pass with zero errors.

---

## 5. Verification Method

To independently verify the implementation:

1. **Syntax Linting**:
   ```bash
   php -l taxonomy-training_type.php archive-program.php inc/core/class-query-filters.php single-program.php single-school.php single-major.php template-parts/banner.php template-parts/compare/program-cards.php tests/test-m5-templates-presentation.php
   ```
   *Expected outcome*: `No syntax errors detected in [filename]` for all 9 files.

2. **Milestone M5 Test Suite**:
   ```bash
   php tests/test-m5-templates-presentation.php
   ```
   *Expected outcome*: `M5 TEST SUITE SUMMARY: 65 PASSED, 0 FAILED`.

3. **Full Regression Test Run**:
   ```bash
   php tests/test-m2-empirical.php && php tests/test-m3-adversarial.php && php tests/test-m4-adversarial.php && php tests/test-m5-templates-presentation.php
   ```
   *Expected outcome*: All suites exit with code 0 (167 total assertions passing).
