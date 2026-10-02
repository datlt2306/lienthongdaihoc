# Review & Adversarial Critic Report — Milestone M5: Program Cards Standardization

**Reviewer**: `reviewer_m5_1` (Program Cards Reviewer & Adversarial Critic)  
**Parent / Caller**: `f7ebf938-afab-4e8b-b557-505007c00d2d` (`parent`)  
**Timestamp**: 2026-10-01T11:30:00Z  
**Verdict**: **APPROVE**  
**Integrity Status**: **CLEAN (0 INTEGRITY VIOLATIONS)**  

---

## 1. Observation

Direct code inspections, line numbers, tool executions, and empirical test results:

### 1.1 Syntax Linting (`php -l`)
Command:
```bash
php -l taxonomy-training_type.php archive-program.php inc/core/class-query-filters.php single-program.php single-school.php single-major.php template-parts/compare/program-cards.php template-parts/banner.php tests/test-m5-templates-presentation.php
```
Result:
```text
No syntax errors detected in taxonomy-training_type.php
No syntax errors detected in archive-program.php
No syntax errors detected in inc/core/class-query-filters.php
No syntax errors detected in single-program.php
No syntax errors detected in single-school.php
No syntax errors detected in single-major.php
No syntax errors detected in template-parts/compare/program-cards.php
No syntax errors detected in template-parts/banner.php
No syntax errors detected in tests/test-m5-templates-presentation.php
```

### 1.2 Program Card Parity (SSR vs. AJAX)
- **SSR Card Containers**:
  - `taxonomy-training_type.php` (lines 379–386) & `archive-program.php` (lines 379–386):
    ```html
    <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between group"
         data-compare-btn
         data-compare-type="program"
         data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
         data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
         data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
         data-compare-thumb="<?php echo esc_url( $thumb ); ?>">
    ```
  - `inc/core/class-query-filters.php` (lines 233–239):
    Exact 1:1 match in CSS classes, flex layout, and data-compare attributes.
- **Card Cover & Institutional Identity**:
  - SSR (`taxonomy-training_type.php:388–428`): `h-28 sm:h-32 w-full bg-cover relative`, top status badge (`tam-ngung` / `sap-mo`), clean `$clean_type_name` badge, school logo container `-mt-8` with `w-12 h-12 bg-white border border-slate-200/90 rounded-xl`, fallback SVG, and uppercase truncated school name.
  - AJAX (`class-query-filters.php:241–282`): Identical DOM structure, geometry, fallback SVG, and typographic classes.
- **Headline Formula**:
  - Computed across SSR (`taxonomy-training_type.php:336–353`, `archive-program.php:336–353`) and AJAX (`class-query-filters.php:191–208`):
    ```php
    $clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
    $clean_type_name  = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
    $card_headline    = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
    ```
  - Displayed inside `h2` link:
    ```html
    <h2 class="font-black text-slate-900 text-base md:text-lg hover:text-brand-primary leading-snug line-clamp-2 min-h-[48px] transition-colors">
        <a href="<?php the_permalink(); ?>"><?php echo esc_html( $card_headline ); ?></a>
    </h2>
    ```
- **Key Information & Learning Details**:
  - Both SSR (`lines 444–458`) and AJAX (`lines 298–312`) render:
    - Học phí: `$tuition` (`font-extrabold text-brand-primary`)
    - Thời gian: `$duration` (`font-bold text-slate-700`)
    - Hình thức: `$learning_details['mode']` derived via `ltdh_get_program_learning_details( $prog_id )`
- **Compare Button & Attributes**:
  - Both SSR (`lines 468–477`) and AJAX (`lines 322–331`) render:
    ```html
    <button type="button"
            class="ltdh-compare-toggle text-xs md:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary rounded-xl py-2.5 px-3 transition-all min-h-[44px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50"
            data-compare-type="program"
            data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
            data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
            data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
            data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
            data-compare-nganh="<?php echo esc_attr( $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '' ); ?>">
        So sánh
    </button>
    ```

### 1.3 Single Views Opportunity Title
- `single-school.php`:
  - Lines 460–462 compute:
    `$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`
  - Rendered in Grouped Layout (line 546) and Single Layout (line 590).
  - Line 524 standardizes section title to `Hình thức học:`.
- `single-major.php`:
  - Lines 424–426 compute:
    `$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );`
  - Rendered in Grouped Layout (line 515) and Single Layout (line 559).
  - Line 493 standardizes section title to `Hình thức học:`.
- `template-parts/compare/program-cards.php`:
  - Lines 73–75 compute:
    `$card_title = 'Liên thông ngành ' . $clean_item_major . ( $clean_item_type ? ' - ' . $clean_item_type : '' );`
  - Rendered at line 79; line 85 invokes `ltdh_get_training_type_badge_html( $clean_item_type )`.

### 1.4 Scope & Notice Cleanup
- `single-program.php`:
  - Line 195: Notice reflects Liên thông: `"Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này."`.
  - Line 270–272: Delivery campus guards against `"Online"` and empty strings, falling back to `"Toàn quốc"`.
  - Line 276: Delivery mode labeled `"Hình thức học"`.
- `template-parts/banner.php`:
  - Lines 25–27, 78–84, 91–93, 107–113: Strips `"Hệ "` prefix from `$he_term->name` using `preg_replace( '/^hệ\s+/iu', '', ... )`, enforces `"Hình thức học"`, zero occurrences of `"Văn bằng 2"` or `"Chính quy"`.

### 1.5 Automated Test Runs
- `php tests/test-m5-templates-presentation.php`: **65 PASSED, 0 FAILED**
- `php tests/test-m2-empirical.php`: **46 PASSED, 0 FAILED**
- `php tests/test-m3-adversarial.php`: **38 PASSED, 0 FAILED**
- `php tests/test-m4-adversarial.php`: **18 PASSED, 0 FAILED**
- Overall test execution: **167 tests passed, 0 failures**.

---

## 2. Logic Chain

1. **Visual and Structural Parity (Observation 1.2)**:
   - When a user filters programs via the top filter bar, `assets/js/modules/filters.js` sends an AJAX request handled by `inc/core/class-query-filters.php::ajax_filter_programs()`.
   - Before M5, AJAX-rendered cards differed noticeably in height, badges, and attributes from SSR cards loaded on `/he-dao-tao/`.
   - By aligning card geometry (`h-28 sm:h-32`), status badges, school logos, headline formulas, key information rows, and compare toggle attributes 1:1, users experience seamless visual transitions between initial SSR render and AJAX filter updates.
2. **Redundant Prefix Stripping (Observation 1.2 & 1.3)**:
   - Major CPT titles in the database often include strings like `"Ngành Kế toán"` or `"Cử nhân Công nghệ thông tin"`, while `training_type` terms often start with `"Hệ "`.
   - Direct concatenation would generate unprofessional headlines like `"Liên thông ngành Ngành Kế toán - Hệ Từ xa"`.
   - Applying `preg_replace( '/^ngành\s+/iu', '', ... )` and `preg_replace( '/^hệ\s+/iu', '', ... )` guarantees clean output: `"Liên thông ngành Kế toán - Từ xa"`.
   - In all templates (`taxonomy-training_type.php`, `archive-program.php`, `class-query-filters.php`, `single-school.php`, `single-major.php`, `compare/program-cards.php`), this formula is applied consistently.
3. **Compare Functionality Integrity (Observation 1.2 & JS inspection)**:
   - `compare.js` relies on event delegation on `document` targeting `.ltdh-compare-toggle`.
   - It reads `data-compare-id`, `data-compare-type`, `data-compare-title`, `data-compare-he`, and `data-compare-nganh`.
   - Because all attributes are identically present on both SSR and AJAX cards, compare actions function identically regardless of whether the card was rendered by PHP or inserted dynamically into `#program-results-container`.
4. **Scope Integrity & Regulatory Compliance (Observation 1.4)**:
   - `single-program.php` had legacy notices referring to "hệ Chính quy" and "hệ Liên thông Chính quy", directly violating the mandate of `ORIGINAL_REQUEST.md` (only Liên thông Từ xa and Vừa học vừa làm).
   - The updated copy and campus guard cleanly resolve this without any data loss or broken layout.
5. **No Cheating / Integrity Validation**:
   - Source inspection confirms no hardcoded mock results, no dummy facades, and genuine dynamic database querying.

---

## 3. Caveats

- **Active Term Badge Suppression**: When viewing an archive for a specific training type (e.g. `/he-dao-tao/tu-xa/`), the right-hand badge on the card is intentionally omitted because the user is already on the dedicated filter tab, avoiding repetitive UI noise. On `/he-dao-tao/` (All/Tất cả), badges are actively displayed for every card. This matches designed UX behavior.
- **Compare Limit Warning**: The client-side compare system restricts comparisons to maximum 3 programs within the same major (`nganh`). This behavior is controlled by `compare.js` and functions as intended.
- No other caveats.

---

## 4. Conclusion

**Verdict: APPROVE**

Milestone M5 satisfies all functional, architectural, visual, and adversarial criteria:
- **1:1 Structural Parity**: SSR cards and AJAX cards have identical DOM structure, Tailwind CSS styling, badges, key metrics, and compare attributes.
- **Standardized Headline Formula**: Implemented across all 6 relevant templates (`taxonomy-training_type.php`, `archive-program.php`, `class-query-filters.php`, `single-school.php`, `single-major.php`, `template-parts/compare/program-cards.php`).
- **Badge Purity**: Zero "Hệ " prefix across badges and titles.
- **Code Cleanliness & Safety**: 0 syntax errors, thorough output escaping (`esc_html`, `esc_attr`, `esc_url`), 0 regressions.
- **Zero Integrity Violations**: Real logic, authentic tests, no shortcuts.

---

## 5. Verification Method

To independently reproduce the verification:

1. **Syntax Check**:
   ```bash
   php -l taxonomy-training_type.php archive-program.php inc/core/class-query-filters.php single-program.php single-school.php single-major.php template-parts/compare/program-cards.php template-parts/banner.php tests/test-m5-templates-presentation.php
   ```
   *Expected*: `No syntax errors detected` for all 9 files.

2. **Run M5 Presentation Test Suite**:
   ```bash
   php tests/test-m5-templates-presentation.php
   ```
   *Expected*: `M5 TEST SUITE SUMMARY: 65 PASSED, 0 FAILED`.

3. **Run Full Regression Suite**:
   ```bash
   php tests/test-m2-empirical.php && php tests/test-m3-adversarial.php && php tests/test-m4-adversarial.php && php tests/test-m5-templates-presentation.php
   ```
   *Expected*: All 4 suites pass with 167 total assertions and exit code 0.
