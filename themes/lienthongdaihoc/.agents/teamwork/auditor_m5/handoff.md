# Forensic Audit Report — Milestone M5: Templates & Program Presentation

**Work Product**: Changes across 8 files (`taxonomy-training_type.php`, `archive-program.php`, `inc/core/class-query-filters.php`, `single-program.php`, `single-school.php`, `single-major.php`, `template-parts/banner.php`, `template-parts/compare/program-cards.php`) and test suite `tests/test-m5-templates-presentation.php`  
**Auditor**: `auditor_m5`  
**Profile**: General Project (Development Mode)  
**Verdict**: **CLEAN**

---

### Phase Results
- **Check 1: Git Diff & Source Code Review across 8 Files**: PASS — All changes implement authentic templates, query filters, dynamic regex string cleansing, and sanitized HTML output.
- **Check 2: Facade & Hardcoded Cheat Detection**: PASS — Zero dummy functions, zero test sniffing (`php_sapi_name`, `HTTP_USER_AGENT`, `getenv`), zero hardcoded post IDs or mock returns.
- **Check 3: Data Loss & Hard Deletion Verification**: PASS — Zero data deleted. Out-of-scope items remain safely preserved in `draft` status (M1); zero calls to `wp_delete_post()`, `wp_trash_post()`, or `$wpdb->delete()` in M5.
- **Check 4: Core CPTs & Study Mode Scope Verification**: PASS — Strictly preserves the 3 educational CPTs (`school`, `major`, `program`); zero new CPTs created. All archive and single template queries enforce `tax_query` restricted exclusively to `['tu-xa', 'vua-hoc-vua-lam']`.
- **Check 5: Independent Build, Syntax & Test Execution**: PASS — 9/9 files passed PHP syntax linting with 0 errors; M5 test suite passed 65/65 tests; full regression suite passed 167/167 assertions.

---

## 1. Observation

### 1.1 Scope of Changes
The git diff across the 8 modified files reveals:
1. `single-program.php`:
   - Line 195: Replaced legacy notice `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."` with `"Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này."`.
   - Line 264–274: Defensive campus guard isolates `"Online"` from physical facility display:
     ```php
     $display_campus = $learning_details['campus'] ?? '';
     if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
         $display_campus = 'Toàn quốc';
     }
     echo esc_html( $display_campus );
     ```
   - Line 570: Replaced `"hệ Liên thông Chính quy"` with clean description `"chương trình Liên thông"`.
2. `template-parts/banner.php`:
   - Lines 25–27: Route `/he-dao-tao` title set to `"Hình thức học"`, subtitle set to `"Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm"`.
   - Lines 75–87, 88–92, 107–111: Replaced all occurrences of `"hệ đào tạo"` and out-of-scope terms ("Văn bằng 2", "Chính quy"). Applied `preg_replace( '/^hệ\s+/iu', '', $he_term->name )` to strip redundant prefix.
3. `template-parts/compare/program-cards.php`:
   - Line 22: Label updated from `'Hệ đào tạo'` to `'Hình thức học'`.
   - Lines 66–78: Cleaned major and training type prefixes dynamically; generated `$card_title = 'Liên thông ngành ' . $clean_item_major . ( $clean_item_type ? ' - ' . $clean_item_type : '' );`.
   - Line 84: Rendered badge with `$clean_item_type` (zero `"Hệ "` prefix).
4. `single-school.php`:
   - Lines 379–384, 393–395, 410–412: Added `$tax_training_type_filter` enforcing `['tu-xa', 'vua-hoc-vua-lam']` on published programs.
   - Lines 460–462: Dynamically computes `$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`.
   - Lines 524, 544–548, 588–592: Renders `"Hình thức học:"` and links opportunity title to `$prog['permalink']`.
5. `single-major.php`:
   - Lines 379–384, 393–395, 410–412: Enforces `['tu-xa', 'vua-hoc-vua-lam']` on published programs.
   - Lines 424–426: Dynamically computes `$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`.
   - Lines 493, 512–517, 550–564: Renders `"Hình thức học:"`, opportunity title linking to permalink, and school name/code subtitle.
6. `inc/core/class-query-filters.php`:
   - Lines 148–155: Defaults `$args['tax_query']` to `[ 'tu-xa', 'vua-hoc-vua-lam' ]`.
   - Lines 191–209: Dynamically derives `$clean_major_name`, `$clean_type_name`, and `$card_headline = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`.
   - Lines 233–345: Upgrades AJAX card markup to exact 1:1 parity with SSR cards (cover image, status badge, training type badge without "Hệ ", headline link, key metrics, and compare button attributes).
   - Lines 355–365: Implements `ltdh_order_by_admission_status_filter()` hook using `$wpdb` to prioritize active programs before paused ones.
7. `taxonomy-training_type.php`:
   - Lines 112–117: `tax_query` default terms enforce in-scope modes `['tu-xa', 'vua-hoc-vua-lam']`.
   - Line 188: H1 title stripped of `"Hệ "` prefix: `Hình thức học: [Tên hình thức]`.
   - Lines 360–378, 420–445: Program card loop computes `$card_headline`, renders school cover, clean badges, key metrics, and compare toggle attributes.
8. `archive-program.php`:
   - Lines 112–117: Exact identical `tax_query` in-scope defaults as `taxonomy-training_type.php`.
   - Program card loop matches 1:1 with `taxonomy-training_type.php`.

### 1.2 Raw Tool Output Evidence

#### A. Syntax Linting (`php -l`):
```text
No syntax errors detected in taxonomy-training_type.php
No syntax errors detected in archive-program.php
No syntax errors detected in inc/core/class-query-filters.php
No syntax errors detected in single-program.php
No syntax errors detected in single-school.php
No syntax errors detected in single-major.php
No syntax errors detected in template-parts/banner.php
No syntax errors detected in template-parts/compare/program-cards.php
No syntax errors detected in tests/test-m5-templates-presentation.php
```

#### B. M5 Test Suite Execution:
```text
===================================================================
M5 EMPIRICAL & ADVERSARIAL TEST SUITE: TEMPLATES & PRESENTATION
===================================================================
SUITE 1: template-parts/banner.php Out-of-Scope Cleanup & Subtitles -> 5 passed
SUITE 2: single-program.php Notice Cleanup & Delivery Mode Guard -> 4 passed
SUITE 3: Archive tax_query In-Scope Defaults & Headers -> 5 passed
SUITE 4: Program Card Headline Formula Parity Across All 6 Templates -> 6 passed
SUITE 5: Training Type Badges Zero 'Hệ ' Prefix Enforcement -> 6 passed
SUITE 6: SSR vs AJAX Program Card Structural Parity -> 19 passed
SUITE 7: Adversarial String Cleaning & Edge Cases Simulation -> 20 passed
===================================================================
M5 TEST SUITE SUMMARY: 65 PASSED, 0 FAILED
===================================================================
```

#### C. Full Regression Test Run:
```text
test-m2-empirical.php:            46 PASSED, 0 FAILED
test-m3-adversarial.php:          38 PASSED, 0 FAILED
test-m4-adversarial.php:          18 PASSED, 0 FAILED
test-m5-templates-presentation.php: 65 PASSED, 0 FAILED
Total:                            167 PASSED, 0 FAILED
```

#### D. Static Analysis for Cheat Patterns:
- Regex search for `(mock|test|fake|dummy|eval|base64_decode)`: 0 cheat matches found (only legitimate ACF date field `evaluation_time` and design comment `mockup`).
- Regex search for `(php_sapi_name|getenv|defined\s*\(\s*['"](?!ABSPATH)[\w_]+['"]\s*\)|_ENV|_SERVER\s*\[\s*['"]HTTP_USER_AGENT)`: 0 results.
- Regex search for `\$(prog_id|prog|id|case|test)\s*===?\s*\d+`: 0 results.

---

## 2. Logic Chain

1. **Direct Inspection of Code Diffs**:
   Every change made across the 8 files was verified directly against the git diff and source files. The edits are genuine modifications to WordPress theme templates and query filter classes.
2. **Absence of Facades and Hardcodes**:
   No functions return dummy constants or uncomputed values. All titles, badges, and card attributes are computed dynamically from post metadata, taxonomy terms, and relationships via standard WordPress APIs (`get_post_meta`, `wp_get_post_terms`, `get_field`).
3. **Data Integrity & Non-Destruction**:
   `audit_report.json` and static inspection verify that zero posts, terms, or metadata entries were deleted. Out-of-scope records (e.g., college programs and full-time records identified in M1) remain safely stored in `draft` status, in full compliance with Requirement R1 of `ORIGINAL_REQUEST.md`.
4. **Adherence to Core CPT Architecture & Study Modes**:
   The 3 core educational CPTs (`school`, `major`, `program`) are strictly maintained. Zero new CPTs were introduced. The taxonomy queries in all templates and AJAX endpoints explicitly restrict public queries to `['tu-xa', 'vua-hoc-vua-lam']`.
5. **Empirical Independent Verification**:
   All 9 files were verified via independent CLI execution of `php -l` and 4 distinct test suites (M2, M3, M4, M5). All 167 automated test assertions passed with zero failures and zero PHP notices or warnings.

---

## 3. Caveats

- **Tailwind CSS Build**: The templates utilize standard Tailwind CSS utility classes configured in the theme (`h-28 sm:h-32`, `ltdh-btn-details`, `shadow-2xs`). Recompilation of assets is not required as all classes already exist in the compiled stylesheets.
- **Dynamic Term Prefix Handling**: Prefix stripping relies on regex `preg_replace( '/^hệ\s+/iu', '', ... )`. If terms are created in the future with non-standard prefix variations, they will be displayed as entered without causing fatal errors.
- No other caveats.

---

## 4. Conclusion

The work product delivered by `worker_m5` for Milestone M5 is **CLEAN**.  
There are **zero integrity violations**, zero facades, zero hardcoded cheats, and zero data deletions. All 8 modified files authentically fulfill the requirements of Milestone M5 and align 100% with the core constraints of `ORIGINAL_REQUEST.md`.

---

## 5. Verification Method

To independently verify the audit findings:

1. **PHP Syntax Verification**:
   ```bash
   php -l taxonomy-training_type.php && \
   php -l archive-program.php && \
   php -l inc/core/class-query-filters.php && \
   php -l single-program.php && \
   php -l single-school.php && \
   php -l single-major.php && \
   php -l template-parts/banner.php && \
   php -l template-parts/compare/program-cards.php && \
   php -l tests/test-m5-templates-presentation.php
   ```
   *Expected outcome*: `No syntax errors detected` across all 9 files.

2. **Milestone M5 Test Execution**:
   ```bash
   php tests/test-m5-templates-presentation.php
   ```
   *Expected outcome*: `M5 TEST SUITE SUMMARY: 65 PASSED, 0 FAILED`.

3. **Full Regression Execution**:
   ```bash
   php tests/test-m2-empirical.php && \
   php tests/test-m3-adversarial.php && \
   php tests/test-m4-adversarial.php && \
   php tests/test-m5-templates-presentation.php
   ```
   *Expected outcome*: All test suites exit with code 0 (167 passed, 0 failed).
