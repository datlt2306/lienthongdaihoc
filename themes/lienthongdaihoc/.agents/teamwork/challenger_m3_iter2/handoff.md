# Handoff Report: Milestone M3 Remediation Verification

**Challenger**: `challenger_m3_iter2` (M3 Remediation Challenger)  
**Date**: 2026-10-01  
**Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_iter2/`  
**Verdict**: **APPROVE**  

---

## 1. Observation

### 1.1. Empirical Test Suite Execution

All test suites were executed directly against the codebase in the theme directory (`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`):

#### Test Suite 1: Empirical Label & Facet Test Harness
**Command**:
```bash
php tests/test-m3-label-facets-empirical.php
```
**Verbatim Output**:
```text
===================================================================
EMPIRICAL TEST SUITE: M3 PUBLIC LABELS & FACETS (challenger_m3_2)
===================================================================

SECTION 1: Public Label Audit for 'Hệ đào tạo' / 'Hệ '
-------------------------------------------------------------------
  [PASS] 1.1: Theme template files contain zero instances of 'Hệ đào tạo'
  [PASS] 1.2: Eligibility engine messages contain zero instances of 'Hệ đào tạo'
  [PASS] 1.3: Program cards contain zero obsolete 'Hệ ' prefix before training type name (e.g. 'Hệ Từ xa')

SECTION 2: Pill Tabs Evaluation on /he-dao-tao/ and Archive Templates
-------------------------------------------------------------------
  [PASS] 2.1: taxonomy-training_type.php pill tabs container has label 'Hình thức học:'
  [PASS] 2.2: taxonomy-training_type.php has 'Tất cả' master tab
  [PASS] 2.3: taxonomy-training_type.php 'Tất cả' tab URL points to '/he-dao-tao/'
  [PASS] 2.4: taxonomy-training_type.php type tab URLs point to '/he-dao-tao/{slug}/'
  [PASS] 2.1: archive-program.php pill tabs container has label 'Hình thức học:'
  [PASS] 2.2: archive-program.php has 'Tất cả' master tab
  [PASS] 2.3: archive-program.php 'Tất cả' tab URL points to '/he-dao-tao/'
  [PASS] 2.4: archive-program.php type tab URLs point to '/he-dao-tao/{slug}/'
  [PASS] 2.5: Default /he-dao-tao/ view renders ONLY allowed modes ('', 'tu-xa', 'vua-hoc-vua-lam')
  [PASS] 2.6: Direct request to out-of-scope slug '/he-dao-tao/van-bang-2/' does NOT leak out-of-scope pill tab into UI
  [PASS] 2.7: Direct request to out-of-scope slug '/he-dao-tao/chinh-quy/' does NOT leak out-of-scope pill tab into UI

SECTION 3: Breadcrumbs Evaluation to 'Hình thức học'
-------------------------------------------------------------------
  [PASS] 3.1: Program single breadcrumb contains 'Hình thức học' pointing to '/he-dao-tao/'
  [PASS] 3.2: Taxonomy training_type archive breadcrumb contains 'Hình thức học' > 'Từ xa'
  [PASS] 3.3: Virtual base route '/he-dao-tao/' evaluates breadcrumb to 'Hình thức học'
  [PASS] 3.4: Virtual sub-route '/he-dao-tao/vua-hoc-vua-lam/' evaluates breadcrumb to 'Hình thức học' > 'Vừa học vừa làm'
  [PASS] 3.5: Compare page breadcrumbs link to '/he-dao-tao/' with label 'Hình thức học' (NOT '/he-dao-tao/tu-xa/' labeled 'Chương trình')

SECTION 4: Taxonomy 'Loại tuyển sinh' Purity Check
-------------------------------------------------------------------
  [PASS] 4.1: inc/acf-import-cpts.json defines NO taxonomy 'Loại tuyển sinh' or 'loai_tuyen_sinh'
  [PASS] 4.2: inc/acf-import-fields.json defines NO field/taxonomy 'Loại tuyển sinh'
  [PASS] 4.3: Zero occurrences of taxonomy 'loai_tuyen_sinh' across all PHP source files
  [PASS] 4.4: inc/config/constants.php defines NO taxonomy constant for 'Loại tuyển sinh'
  [PASS] 4.5: audit_report.json has zero record or trace of 'Loại tuyển sinh'

===================================================================
SUMMARY: 24 PASSED, 0 FAILED
===================================================================
```
**Exit Code**: `0`

---

#### Test Suite 2: Routing & Canonical Empirical Test
**Command**:
```bash
php tests/test-m3-empirical.php
```
**Verbatim Summary**:
```text
===================================================================
EMPIRICAL TEST SUITE: M3 ROUTING & CANONICAL VERIFICATION
===================================================================
SUITE 1: Request to /chuong-trinh/ Redirect Target Verification (8 assertions: all PASS)
SUITE 2: Query Args Preservation Verification (6 assertions: all PASS)
SUITE 3: Rank Math Canonical URL Filter Verification (7 assertions: all PASS)
SUITE 4: Verify /he-dao-tao/ Returns HTTP 200 without Redirect Loop (17 assertions: all PASS)
===================================================================
TEST RESULTS: 38 PASSED, 0 FAILED
===================================================================
```
**Exit Code**: `0`

---

#### Test Suite 3: Adversarial Stress Test
**Command**:
```bash
php tests/test-m3-adversarial.php
```
**Verbatim Summary**:
```text
===================================================================
ADVERSARIAL STRESS-TEST SUITE: MILESTONE M3
===================================================================
STRESS 1: Query parameter preservation edge cases (8 assertions: all PASS)
STRESS 2: Dynamic Submenu Title Matching Variations (11 assertions: all PASS)
STRESS 3: Template Inclusion Regex Patterns (15 assertions: all PASS)
STRESS 4: Canonical Resolution Under Various Query Strings (4 assertions: all PASS)
===================================================================
ADVERSARIAL STRESS RESULTS: 38 PASSED, 0 FAILED
===================================================================
```
**Exit Code**: `0`

---

#### Test Suite 4: Forensic Integrity Audit
**Command**:
```bash
php tests/test-m3-forensic.php
```
**Verbatim Summary**:
```text
===================================================================
FORENSIC INTEGRITY AUDIT: MILESTONE M3
===================================================================
CHECK 1: AST Token Analysis on All 15 Modified Files (all PASS)
CHECK 2: ACF JSON Taxonomy Definition Integrity (all PASS)
CHECK 3: Navigation Defaults Configuration (all PASS)
CHECK 4: Dynamic Submenu Injection Matching Logic (all PASS)
CHECK 5: Breadcrumbs Trail Configuration in class-helpers.php (all PASS)
CHECK 6: Rewrite Rules & 301 Redirect Logic (all PASS)
CHECK 7: Rank Math Canonical Resolution Logic (all PASS)
CHECK 8: Public UI Label Audit Across All Templates (all PASS)
CHECK 9: Program Archive Form Action & Tab Links (all PASS)
===================================================================
AUDIT SUMMARY: 82 PASSED, 0 FAILED
===================================================================
```
**Exit Code**: `0`

---

#### Test Suite 5: Direct Out-of-Scope & Adversarial Edge Cases Harness (`tests/test-m3-edge-cases-empirical.php`)
A dedicated empirical stress-test harness was authored to challenge out-of-scope taxonomy slugs visited directly via URL paths or query parameters (`/he-dao-tao/van-bang-2/`, `/he-dao-tao/chinh-quy/`, `/he-dao-tao/lien-thong/`, `/he-dao-tao/dai-hoc-chinh-quy/`, `/he-dao-tao/thpt/`, `?he=van-bang-2`, `?he=chinh-quy`, non-existent slug).

**Command**:
```bash
php tests/test-m3-edge-cases-empirical.php
```
**Verbatim Output**:
```text
===================================================================
EMPIRICAL EDGE CASE & LEAKAGE TEST SUITE (challenger_m3_iter2)
===================================================================

SUITE 1: Pill Tabs Isolation Under Adversarial Slugs
-------------------------------------------------------------------
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/van-bang-2/' (van-bang-2)
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/chinh-quy/' (chinh-quy)
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/lien-thong/' (lien-thong)
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/dai-hoc-chinh-quy/' (dai-hoc-chinh-quy)
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/thpt/' (thpt)
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/?he=van-bang-2' (van-bang-2 (query param))
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/?he=chinh-quy' (chinh-quy (query param))
  [PASS] 1. Pill tabs do NOT leak unapproved modes on request to '/he-dao-tao/non-existent-taxonomy-foo/' (non-existent slug)

SUITE 2: Header Navigation Menu Dropdown Submenu Isolation
-------------------------------------------------------------------
  [PASS] 2.1: inc/core/class-menus.php explicitly defines whitelist ['tu-xa', 'vua-hoc-vua-lam']
  [PASS] 2.2: inc/core/class-menus.php passes $allowed_training_types to get_terms()
  [PASS] 2.3: inc/core/class-menus.php has secondary array_filter defense against unapproved modes

SUITE 3: School Training Types Rollup Isolation
-------------------------------------------------------------------
  [PASS] 3.1: ltdh_get_school_training_types restricts rollup to ['tu-xa', 'vua-hoc-vua-lam']
  [PASS] 3.2: ltdh_get_school_training_types enforces tax_query terms => $allowed_slugs

SUITE 4: Obsolete 'Hệ ' Badge Prefix Scan Across All Source Files
-------------------------------------------------------------------
  [PASS] 4.1: Zero obsolete 'Hệ ' badge prefixes found across all template and filter files

SUITE 5: Public Eligibility Engine Output Strings Consistency
-------------------------------------------------------------------
  [PASS] 5.1: inc/eligibility.php:306 uses 'Hình thức học không hợp lệ.' (0 occurrences of 'Hệ đào tạo không hợp lệ.')
  [PASS] 5.2: inc/eligibility.php:479 uses 'Hình thức học [Từ xa] cần được nhà trường xác nhận...'
  [PASS] 5.3: inc/eligibility.php:482 uses 'Hỗ trợ hình thức học [Từ xa] phù hợp.'

SUITE 6: Comparison Breadcrumb Cleanliness
-------------------------------------------------------------------
  [PASS] 6.1: inc/core/class-helpers.php:407 breadcrumb for comparison links to /he-dao-tao/ with label 'Hình thức học'
  [PASS] 6.2: ltdh_breadcrumb() contains zero hardcoded '/he-dao-tao/tu-xa/' links

===================================================================
EDGE CASE AUDIT SUMMARY: 19 PASSED, 0 FAILED
===================================================================
```
**Exit Code**: `0`

---

### 1.2. Verification of Specific Remediation Points

1. **Badge Prefix Elimination**:
   - `taxonomy-training_type.php:376-382`:
     ```php
     <?php if ( $show_type_badge ) : ?>
     	<span class="absolute top-3 right-3 <?php echo esc_attr( $badge_class ); ?> text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide border shadow-sm z-10">
     		<?php echo esc_html( $type_name ); ?>
     	</span>
     <?php endif; ?>
     ```
   - `archive-program.php:376-382`: Exactly identical clean badge output without `'Hệ '`.
   - `inc/core/class-query-filters.php:233-237`: Clean badge output `<?php echo esc_html( $t_name ); ?>`.
   - Result: SSR and AJAX cards have 100% presentation parity (`Từ xa`, `Vừa học vừa làm`).

2. **Eligibility Engine String Standardization**:
   - `inc/eligibility.php:306`: `'Hình thức học không hợp lệ.'`
   - `inc/eligibility.php:479`: `'Hình thức học ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' cần được nhà trường xác nhận với trình độ hiện tại.'`
   - `inc/eligibility.php:482`: `'Hỗ trợ hình thức học ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' phù hợp.'`
   - Result: Public AJAX responses to `assets/js/eligibility.js` render pure "Hình thức học" labels in candidate cards.

3. **Facet & Submenu Whitelisting**:
   - `taxonomy-training_type.php:135-145` and `archive-program.php:135-145`:
     ```php
     $allowed_training_types = [ 'tu-xa', 'vua-hoc-vua-lam' ];
     $all_types             = get_terms( [
     	'taxonomy'   => 'training_type',
     	'slug'       => $allowed_training_types,
     	'hide_empty' => false,
     ] );
     if ( ! is_wp_error( $all_types ) && is_array( $all_types ) ) {
     	$all_types = array_values( array_filter( $all_types, function( $t ) use ( $allowed_training_types ) {
     		return in_array( $t->slug, $allowed_training_types, true );
     	} ) );
     }
     ```
   - `inc/core/class-menus.php:147-156`:
     ```php
     $allowed_training_types = ['tu-xa', 'vua-hoc-vua-lam'];
     $types = get_terms([
     	'taxonomy'   => LTDH_TAX_TRAINING_TYPE,
     	'slug'       => $allowed_training_types,
     	'hide_empty' => false,
     ]);
     if (! is_wp_error($types) && is_array($types)) {
     	$types = array_values(array_filter($types, function($t) use ($allowed_training_types) {
     		return in_array($t->slug, $allowed_training_types, true);
     	}));
     ...
     ```
   - Result: Regardless of database terms or user URL requests, only allowed modes can be output as pill tabs or navigation dropdown items.

4. **Comparison View Breadcrumb**:
   - `inc/core/class-helpers.php:407`:
     ```php
     echo '<a href="' . esc_url( home_url( '/he-dao-tao/' ) ) . '" class="hover:text-brand-primary">Hình thức học</a>';
     ```
   - Result: Repointed from `/he-dao-tao/tu-xa/` ("Chương trình") to `/he-dao-tao/` ("Hình thức học"), achieving complete breadcrumb uniformity.

---

## 2. Logic Chain

1. **Premise**: Milestone M3 requires changing all frontend training type labels to "Hình thức học", whitelisting study modes strictly to `['tu-xa', 'vua-hoc-vua-lam']`, preserving indexed URLs (`/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`), redirecting `/chuong-trinh/` via 301 to `/he-dao-tao/`, and eliminating any leak of unapproved study modes into the UI.
2. **Prior Defect Identification (`challenger_m3_2`)**: 5 assertions in `test-m3-label-facets-empirical.php` failed due to:
   - Residual "Hệ " badge prefix in SSR templates
   - Legacy "Hệ đào tạo" strings in `inc/eligibility.php`
   - Lack of terms whitelisting in `taxonomy-training_type.php`, `archive-program.php`, and `class-menus.php`
   - Hardcoded crumb in `class-helpers.php:407`
3. **Remediation Assessment (`worker_m3_iter2`)**:
   - Each of the 4 root causes was modified directly in production source code.
   - PHP lint checks (`php -l`) confirm 0 syntax errors across all 5 modified files.
4. **Empirical Verification**:
   - Re-running `tests/test-m3-label-facets-empirical.php` yields 24/24 PASS (0 failures, exit code 0).
   - Re-running `tests/test-m3-empirical.php` yields 38/38 PASS (routing, redirect rules, query parameter preservation, and Rank Math canonicals 100% intact).
   - Re-running `tests/test-m3-adversarial.php` yields 38/38 PASS (no regex regressions, no template inclusion failures).
   - Re-running `tests/test-m3-forensic.php` yields 82/82 PASS (ACF JSON definitions, navigation defaults, breadcrumbs, and rewrite rules 100% compliant).
   - Running newly authored `tests/test-m3-edge-cases-empirical.php` yields 19/19 PASS (confirming that requesting out-of-scope taxonomy slugs directly produces zero pill tab or UI leakage).
5. **Conclusion**: All acceptance criteria for Milestone M3 and all remediation points raised by `challenger_m3_2` are satisfied.

---

## 3. Caveats

1. **Database-Level Term Records**: Terms like `chinh-quy` or `van-bang-2` might still exist in WordPress `wp_terms` if created by legacy imports, but because they have 0 published programs and all theme query and display logic enforces strict slug whitelisting (`['tu-xa', 'vua-hoc-vua-lam']`), they are completely inert and isolated from the public frontend.
2. **Subsequent Milestones**: Presentation formulas on individual cards (M5) and full homepage copy alignment (M4) remain planned for their respective milestones per `PROJECT.md`.

---

## 4. Conclusion

**Verdict: APPROVE**

Milestone M3 remediation is complete, correct, and empirically verified. All 201 assertions across 5 automated test harnesses pass with 0 failures:
- 0 instances of obsolete "Hệ " badge prefixes in SSR or AJAX cards.
- 0 instances of "Hệ đào tạo" in public eligibility engine responses.
- 0 leaks of unapproved study modes when accessing out-of-scope taxonomy slugs.
- 100% integrity of 301 redirects, query args preservation, and Rank Math canonical filters.

Milestone M3 is ready to be marked DONE in `PROJECT.md`.

---

## 5. Verification Method

To independently reproduce this verification, run the following commands from the theme directory:

```bash
# 1. Verify PHP syntax across modified files
php -l taxonomy-training_type.php && \
php -l archive-program.php && \
php -l inc/eligibility.php && \
php -l inc/core/class-menus.php && \
php -l inc/core/class-helpers.php

# 2. Run Label & Facets Empirical Test Suite (24 assertions)
php tests/test-m3-label-facets-empirical.php

# 3. Run Routing & Canonicals Empirical Test Suite (38 assertions)
php tests/test-m3-empirical.php

# 4. Run Adversarial Stress Test Suite (38 assertions)
php tests/test-m3-adversarial.php

# 5. Run Forensic Integrity Audit (82 assertions)
php tests/test-m3-forensic.php

# 6. Run Out-of-Scope & Edge Cases Empirical Test Suite (19 assertions)
php tests/test-m3-edge-cases-empirical.php
```

All 6 test executions terminate with exit code `0` and `0 FAILED`.
