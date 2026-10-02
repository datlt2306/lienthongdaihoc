# Challenge Report: Milestone M3 — Public Labels, Facets & Taxonomy Verification

**Challenger**: `challenger_m3_2` (M3 Public Label & Facet Challenger)  
**Date**: 2026-10-01  
**Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_2/`  
**Verdict**: **REQUEST_CHANGES**  

---

## 1. Observation

### 1.1. Empirical Test Execution
An automated empirical test harness was authored and executed at `tests/test-m3-label-facets-empirical.php` covering the 4 designated task areas (24 assertions total).
Command executed:
```bash
php tests/test-m3-label-facets-empirical.php
```

Verbatim output:
```text
===================================================================
EMPIRICAL TEST SUITE: M3 PUBLIC LABELS & FACETS (challenger_m3_2)
===================================================================

SECTION 1: Public Label Audit for 'Hệ đào tạo' / 'Hệ '
-------------------------------------------------------------------
  [PASS] 1.1: Theme template files contain zero instances of 'Hệ đào tạo'
  [FAIL] 1.2: Eligibility engine messages contain zero instances of 'Hệ đào tạo'
         - found: [{"file":"inc\/eligibility.php","line":306,"content":"return new WP_Error( 'invalid_training', 'Hệ đào tạo không hợp lệ.' );"},{"file":"inc\/eligibility.php","line":479,"content":"$verification_items[] = 'Hệ đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' cần được nhà trường xác nhận với trình độ hiện tại.';"},{"file":"inc\/eligibility.php","line":482,"content":"$match_reasons[] = 'Hỗ trợ hệ đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' phù hợp.';"}]
  [FAIL] 1.3: Program cards contain zero obsolete 'Hệ ' prefix before training type name (e.g. 'Hệ Từ xa')
         - found: [{"file":"taxonomy-training_type.php","line":379,"content":"Hệ <?php echo esc_html( $type_name ); ?>"},{"file":"archive-program.php","line":379,"content":"Hệ <?php echo esc_html( $type_name ); ?>"}]

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
  [FAIL] 2.6: Direct request to out-of-scope slug '/he-dao-tao/van-bang-2/' does NOT leak out-of-scope pill tab into UI
         - rendered_slugs: ["","tu-xa","vua-hoc-vua-lam","van-bang-2"]
         - explanation: If terms array is not whitelisted to allowed modes [tu-xa, vua-hoc-vua-lam], visiting an out-of-scope slug renders that slug as an active tab even with count=0.
  [FAIL] 2.7: Direct request to out-of-scope slug '/he-dao-tao/chinh-quy/' does NOT leak out-of-scope pill tab into UI
         - rendered_slugs: ["","tu-xa","vua-hoc-vua-lam","chinh-quy"]
         - explanation: If terms array is not whitelisted to allowed modes [tu-xa, vua-hoc-vua-lam], visiting an out-of-scope slug renders that slug as an active tab even with count=0.

SECTION 3: Breadcrumbs Evaluation to 'Hình thức học'
-------------------------------------------------------------------
  [PASS] 3.1: Program single breadcrumb contains 'Hình thức học' pointing to '/he-dao-tao/'
  [PASS] 3.2: Taxonomy training_type archive breadcrumb contains 'Hình thức học' > 'Từ xa'
  [PASS] 3.3: Virtual base route '/he-dao-tao/' evaluates breadcrumb to 'Hình thức học'
  [PASS] 3.4: Virtual sub-route '/he-dao-tao/vua-hoc-vua-lam/' evaluates breadcrumb to 'Hình thức học' > 'Vừa học vừa làm'
  [FAIL] 3.5: Compare page breadcrumbs link to '/he-dao-tao/' with label 'Hình thức học' (NOT '/he-dao-tao/tu-xa/' labeled 'Chương trình')
         - compare_block: ltdh_compare' );
	if ( $type ) {
		echo '<div class="ltdh-breadcrumb max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-slate-400">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="hover:text-brand-primary">Trang chủ</a>';
		echo ' <span class="mx-2 text-slate-300">/</span> ';
		echo '<a href="' . esc_url( home_url( '/he-dao-tao/tu-xa/' ) ) . '" class="hover:text-brand-primary">Chương trình</a>';
		echo ' <span class="mx-2 text-slate-300">/</span> ';
		echo '<span class="text-slate-600 font-medium">So sánh chương trình</span>';
		echo '</div>';
		return;
         - issue: inc/core/class-helpers.php:407 hardcodes /he-dao-tao/tu-xa/ and label "Chương trình"

SECTION 4: Taxonomy 'Loại tuyển sinh' Purity Check
-------------------------------------------------------------------
  [PASS] 4.1: inc/acf-import-cpts.json defines NO taxonomy 'Loại tuyển sinh' or 'loai_tuyen_sinh'
  [PASS] 4.2: inc/acf-import-fields.json defines NO field/taxonomy 'Loại tuyển sinh'
  [PASS] 4.3: Zero occurrences of taxonomy 'loai_tuyen_sinh' across all PHP source files
  [PASS] 4.4: inc/config/constants.php defines NO taxonomy constant for 'Loại tuyển sinh'
  [PASS] 4.5: audit_report.json has zero record or trace of 'Loại tuyển sinh'

===================================================================
SUMMARY: 19 PASSED, 5 FAILED
===================================================================
```

### 1.2. Specific Code Observations

#### Issue 1: Obsolete "Hệ " Prefix on Program Cards in Primary SSR Templates
- **Files**: `taxonomy-training_type.php:378-380` and `archive-program.php:378-380`:
  ```php
  <!-- Training Type Badge (Right) -->
  <?php if ( $show_type_badge ) : ?>
  	<span class="absolute top-3 right-3 <?php echo esc_attr( $badge_class ); ?> text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide border shadow-sm z-10">
  		Hệ <?php echo esc_html( $type_name ); ?>
  	</span>
  <?php endif; ?>
  ```
- **Contrast with AJAX filter template (`inc/core/class-query-filters.php:233-237`)**:
  ```php
  <?php if ( $t_name ) : ?>
  	<span class="absolute top-2.5 right-2.5 <?php echo esc_attr( $badge_class ); ?> text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide border shadow-sm z-10">
  		<?php echo esc_html( $t_name ); ?>
  	</span>
  <?php endif; ?>
  ```
- In `worker_m3`'s handoff report (Section 2, item 2), the worker stated:
  > "In `inc/core/class-query-filters.php:235`, the badge prefix `'Hệ '` was removed, rendering clean badges (`Từ xa`, `Vừa học vừa làm`)."
  However, the worker omitted removing `'Hệ '` in the primary server-side templates `taxonomy-training_type.php` and `archive-program.php`. On initial page load, cards display `Hệ Từ xa`. Upon AJAX filtering, cards switch to `Từ xa`.

#### Issue 2: Public Eligibility Engine Output Strings Retain "Hệ đào tạo"
- **File**: `inc/eligibility.php:306, 479, 482`:
  - Line 306:
    ```php
    return new WP_Error( 'invalid_training', 'Hệ đào tạo không hợp lệ.' );
    ```
  - Line 479:
    ```php
    $verification_items[] = 'Hệ đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' cần được nhà trường xác nhận với trình độ hiện tại.';
    ```
  - Line 482:
    ```php
    $match_reasons[] = 'Hỗ trợ hệ đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' phù hợp.';
    ```
- **Client-Side Rendering (`assets/js/eligibility.js:470-478`)**:
  ```js
  if (prog.match_reasons && prog.match_reasons.length > 0) {
      prog.match_reasons.forEach(function (reason) {
          reasonsHtml += '<div class="elig-reason-item text-emerald-600"...>✓ ' + escHtml(reason) + '</div>';
      });
  }
  if (prog.verification_items && prog.verification_items.length > 0) {
      prog.verification_items.forEach(function (item) {
          reasonsHtml += '<div class="elig-reason-item text-amber-600"...>⚠ ' + escHtml(item) + '</div>';
      });
  }
  ```
  Users filling out the wizard see step label "Hình thức học mong muốn" (`template-parts/eligibility/wizard.php:74, 76`), but the resulting card displays:  
  `✓ Hỗ trợ hệ đào tạo Từ xa phù hợp.` or  
  `⚠ Hệ đào tạo Từ xa cần được nhà trường xác nhận với trình độ hiện tại.`

#### Issue 3: Pill Tabs & Header Navigation Menu Leak Out-of-Scope Facets
- **Files**: `taxonomy-training_type.php:135, 274-298`, `archive-program.php:135, 274-298`, and `inc/core/class-menus.php:148-177`:
  ```php
  $all_types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
  ```
  ```php
  foreach ( $all_types as $t_term ) :
      $t_count = $t_counts[ $t_term->slug ] ?? 0;
      $is_active = ( $selected_type === $t_term->slug );

      // Hide training types with 0 programs unless currently active
      if ( $t_count === 0 && ! $is_active ) {
          continue;
      }
  ```
- Because `$all_types` is queried without restricting terms to allowed modes (`['tu-xa', 'vua-hoc-vua-lam']`):
  1. If a user or bot accesses an out-of-scope URL like `/he-dao-tao/van-bang-2/` or `/he-dao-tao/chinh-quy/`, `$is_active` evaluates to `true`, and the UI generates an active pill tab for "Văn bằng 2 (0)" or "Chính quy (0)".
  2. In `inc/core/class-menus.php:148-177`, `get_terms` with `hide_empty => false` injects all terms into the header navigation menu dropdown, publishing "Chính quy" and "Văn bằng 2" in the main site menu if those terms exist in `wp_terms`.

#### Issue 4: Hardcoded Legacy Breadcrumb in Comparison View
- **File**: `inc/core/class-helpers.php:402-411`:
  ```php
  $type = get_query_var( 'ltdh_compare' );
  if ( $type ) {
      echo '<div class="ltdh-breadcrumb max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-slate-400">';
      echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="hover:text-brand-primary">Trang chủ</a>';
      echo ' <span class="mx-2 text-slate-300">/</span> ';
      echo '<a href="' . esc_url( home_url( '/he-dao-tao/tu-xa/' ) ) . '" class="hover:text-brand-primary">Chương trình</a>';
      echo ' <span class="mx-2 text-slate-300">/</span> ';
      echo '<span class="text-slate-600 font-medium">So sánh chương trình</span>';
      echo '</div>';
      return;
  }
  ```
  Line 407 hardcodes the URL `home_url( '/he-dao-tao/tu-xa/' )` and the obsolete crumb label `'Chương trình'`. It should point to `home_url( '/he-dao-tao/' )` with label `'Hình thức học'`.

---

## 2. Logic Chain

1. **Premise**: ORIGINAL_REQUEST.md (## 2026-10-01T09:08:12Z) requires:
   - "đổi thuật ngữ hiển thị thành **Hình thức học** (`Từ xa`, `Vừa học vừa làm`)"
   - "Nhãn frontend hiển thị của `training_type` là 'Hình thức học' trên toàn hệ thống UI."
   - "TUYỆT ĐỐI KHÔNG đưa vào hoặc duy trì các loại hình tuyển sinh không liên quan như: Văn bằng 2, Đại học chính quy như một sản phẩm tuyển sinh riêng biệt độc lập..."
2. **Badge Inconsistency**:
   - `taxonomy-training_type.php:379` and `archive-program.php:379` concatenate `'Hệ ' . $type_name`.
   - On the frontend catalog, the user sees "HỆ TỪ XA" on initial page load, and "TỪ XA" after any AJAX interaction (`inc/core/class-query-filters.php:235`).
   - This directly violates the requirement to eliminate obsolete "Hệ" labels across the UI.
3. **Eligibility Leak**:
   - While `template-parts/eligibility/wizard.php` was updated to "Hình thức học mong muốn", `inc/eligibility.php:479, 482` produces `"Hệ đào tạo [Từ xa]..."` and `"Hỗ trợ hệ đào tạo [Từ xa]..."` in AJAX responses rendered to end-users via `assets/js/eligibility.js`.
4. **Facet Scoping Leak**:
   - The pill tabs in `taxonomy-training_type.php` and `archive-program.php` do not restrict `$all_types` to the two allowed Liên thông modes (`tu-xa`, `vua-hoc-vua-lam`).
   - Visiting `/he-dao-tao/van-bang-2/` bypasses the `count === 0` filter because `$is_active` is true, displaying "Văn bằng 2 (0)" as an active tab.
   - Dynamic menu injection in `class-menus.php:148` likewise pulls all database terms with `hide_empty => false`, publishing out-of-scope terms to the public header navigation menu.
5. **Breadcrumb Inconsistency**:
   - `inc/core/class-helpers.php:407` was overlooked during M3 breadcrumb updates, continuing to link to `/he-dao-tao/tu-xa/` with the label `'Chương trình'`.
6. **Taxonomy Purity Verified**:
   - Zero instances of taxonomy "Loại tuyển sinh" or "loai_tuyen_sinh" exist in `inc/acf-import-cpts.json`, `inc/acf-import-fields.json`, `inc/config/constants.php`, or any PHP template file (100% compliant).

---

## 3. Caveats

1. **Scope of Challenger**:
   - As `challenger_m3_2`, my role is strictly empirical testing and audit; per teamwork rules, I do not modify production implementation code directly.
2. **Root Routing Pass**:
   - Routing for `/chuong-trinh/` (301 to `/he-dao-tao/`) and Rank Math canonical hooks verified by `challenger_m3_1` are working as intended. The defects noted here are confined to frontend label rendering, facet scoping, and secondary breadcrumbs.

---

## 4. Conclusion

**Verdict: REQUEST_CHANGES**

The following concrete remediation items must be addressed by the implementation worker:

1. **Remove obsolete "Hệ " badge prefix in SSR templates**:
   - In `taxonomy-training_type.php:379`: Change `Hệ <?php echo esc_html( $type_name ); ?>` to `<?php echo esc_html( $type_name ); ?>`.
   - In `archive-program.php:379`: Change `Hệ <?php echo esc_html( $type_name ); ?>` to `<?php echo esc_html( $type_name ); ?>`.

2. **Standardize eligibility engine output strings**:
   - In `inc/eligibility.php:306`: Change `'Hệ đào tạo không hợp lệ.'` to `'Hình thức học không hợp lệ.'`.
   - In `inc/eligibility.php:479`: Change `'Hệ đào tạo ' . ltdh_elig_get_training_label(...) . ' cần được...'` to `'Hình thức học ' . ltdh_elig_get_training_label(...) . ' cần được...'`.
   - In `inc/eligibility.php:482`: Change `'Hỗ trợ hệ đào tạo ' . ltdh_elig_get_training_label(...) . ' phù hợp.'` to `'Hỗ trợ hình thức học ' . ltdh_elig_get_training_label(...) . ' phù hợp.'`.

3. **Whitelist allowed modes for pill tabs and dynamic header menus**:
   - In `taxonomy-training_type.php:135` and `archive-program.php:135`:
     Filter `$all_types` to only allowed slugs (`[ 'tu-xa', 'vua-hoc-vua-lam' ]`) via `get_terms( [ 'taxonomy' => 'training_type', 'slug' => [ 'tu-xa', 'vua-hoc-vua-lam' ], 'hide_empty' => false ] )` or an explicit `array_filter`.
   - In `inc/core/class-menus.php:148`:
     Filter `$types` to only allowed slugs (`[ 'tu-xa', 'vua-hoc-vua-lam' ]`).

4. **Update comparison view breadcrumbs**:
   - In `inc/core/class-helpers.php:407`: Change `<a href="' . esc_url( home_url( '/he-dao-tao/tu-xa/' ) ) . '" class="hover:text-brand-primary">Chương trình</a>` to `<a href="' . esc_url( home_url( '/he-dao-tao/' ) ) . '" class="hover:text-brand-primary">Hình thức học</a>`.

---

## 5. Verification Method

Run the empirical test harness from the theme directory:
```bash
php tests/test-m3-label-facets-empirical.php
```

- **Pass Condition**: `SUMMARY: 24 PASSED, 0 FAILED` (exit code `0`).
- **Current Status**: `SUMMARY: 19 PASSED, 5 FAILED` (exit code `1`).
