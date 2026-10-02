# Handoff Report — Challenger M5-2 (Single Program, Banner & Regression Challenger)

**Author**: `challenger_m5_2`  
**Recipient**: `parent` (Orchestrator, ID: `f7ebf938-afab-4e8b-b557-505007c00d2d`)  
**Timestamp**: 2026-10-01T11:27:00Z  
**Type**: Hard Handoff (Task Complete)  
**Verdict**: **APPROVE**

---

## Challenge Summary

- **Overall Risk Assessment**: **LOW**
- **Core Scope Tested**:
  1. `single-program.php` quota announcement notice cleanup, campus fallback guard, and removal of out-of-scope educational notices.
  2. `template-parts/banner.php` title and subtitle purity across standard and simulated routes.
  3. Study mode archive query construction and scope confinement in `taxonomy-training_type.php` and `archive-program.php`.
  4. Cross-template parity of program card headlines and badges across 6 templates.
  5. Full regression across Milestone test suites M2, M3, M4, and M5.
- **Empirical Test Outcomes**: **243 passing assertions, 0 failures**.

---

## 1. Observation

Direct observations from tool executions, file contents, line numbers, and terminal output:

### 1.1 `single-program.php` Notice Cleanup & Purity
- **Quota announcement** (Lines 188–198):
  ```php
  <?php 
  $admission_status = get_post_meta( $program_id, 'admission_status', true ) ?: 'tuyen-sinh';
  if ( $admission_status === 'tam-ngung' ) :
  ?>
      <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 flex items-start gap-3 shadow-2xs">
          ...
          <div>
              <h4 class="font-bold text-red-900 text-sm">Đã hết chỉ tiêu tuyển sinh năm nay</h4>
              <p class="text-xs text-red-700 mt-1">Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này. Quý học viên vui lòng tham khảo các chương trình liên quan hoặc để lại thông tin đăng ký tư vấn để được hướng dẫn lộ trình phù hợp.</p>
          </div>
      </div>
  <?php endif; ?>
  ```
  The legacy notice referring to "hệ Chính quy" has been completely eliminated.
- **Line 573**:
  `<p class="text-xs md:text-sm text-slate-600 mt-0.5">Áp dụng chính thức cho thí sinh đăng ký chương trình Liên thông ngành Công nghệ thông tin.</p>`
  Replaced legacy "hệ Liên thông Chính quy".
- **Campus fallback guard** (Lines 267–273):
  ```php
  $display_campus = $learning_details['campus'] ?? '';
  if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
      $display_campus = 'Toàn quốc';
  }
  echo esc_html( $display_campus );
  ```
- **Labeling** (Line 276): Labeled as `"Hình thức học"` (not `"Hệ đào tạo"`).
- **Grep verification**: Case-insensitive scans for `"hệ Chính quy"`, `"Chính quy"`, `"Văn bằng 2"`, `"van bang 2"`, and `"hệ đào tạo"` returned **0 matches** in `single-program.php`.

### 1.2 `template-parts/banner.php` Text Purity
- **Route `/he-dao-tao`** (Lines 25–27, 106–108):
  ```php
  if ( preg_match( '#^/he-dao-tao(?:/page/\d+)?/?$#i', $request_path ) ) {
      $banner_title    = 'Hình thức học';
      $banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
  }
  ```
- **Taxonomy / Program Archives** (Lines 76–84, 89–93, 109–114):
  Term names are cleanly stripped of `"Hệ "` prefix via `preg_replace( '/^hệ\s+/iu', '', $he_term->name )`.
- **Grep verification**: Scans for `"Văn bằng 2"`, `"Chính quy"`, and `"hệ đào tạo"` returned **0 matches** in `template-parts/banner.php`.

### 1.3 Study Mode Archive Queries
- In `taxonomy-training_type.php` (Lines 106–118) and `archive-program.php` (Lines 106–118):
  ```php
  if ( ! empty( $selected_type ) ) {
      $args['tax_query'][] = [
          'taxonomy' => 'training_type',
          'field'    => 'slug',
          'terms'    => $selected_type,
      ];
  } else {
      $args['tax_query'][] = [
          'taxonomy' => 'training_type',
          'field'    => 'slug',
          'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ],
      ];
  }
  ```
- Both templates enforce `'post_status' => 'publish'` (line 43) and whitelist pills to `['tu-xa', 'vua-hoc-vua-lam']` (line 141), ensuring drafted out-of-scope programs never appear on frontend archives.

### 1.4 Test Suite Execution Results
- `php tests/test-m5-templates-presentation.php`:
  - Suite 4 (Card Headline Parity Across 6 Templates): **6 passed, 0 failed**
  - Suite 5 (Training Type Badges Zero 'Hệ ' Prefix): **6 passed, 0 failed**
  - Suite 7 (Adversarial String Cleaning & Edge Cases): **20 passed, 0 failed**
  - Total M5 Suite: **65 passed, 0 failed**.
- `php tests/test-m2-empirical.php`: **46 passed, 0 failed**.
- `php tests/test-m3-adversarial.php`: **38 passed, 0 failed**.
- `php tests/test-m4-adversarial.php`: **18 passed, 0 failed**.
- `php tests/test-m5-challenger-empirical.php` (New Challenger harness): **76 passed, 0 failed**.
- **Grand total**: **243 passing assertions across all suites, 0 failures**.
- `php -l` lint check on all 10 theme and test files: **No syntax errors detected**.

---

## 2. Logic Chain

1. **Information Architecture Confinement**:
   The website `lienthongdaihoc.com` is committed strictly to Liên thông Đại học (specifically Từ xa and Vừa học vừa làm).
   Observations in `single-program.php` and `template-parts/banner.php` show that legacy references to "Chính quy", "Văn bằng 2", and "hệ đào tạo" have been fully excised and replaced with accurate in-scope descriptions.
2. **Defensive Data Rollup & Campus Guarding**:
   Programs marked as `Online` in physical campus attributes are defensively remapped to `"Toàn quốc"` by `single-program.php:270` and `ltdh_get_program_learning_details()`. This prevents virtual delivery modes from masquerading as physical campuses.
3. **Query Confinement in Study Mode Archives**:
   Both `taxonomy-training_type.php` and `archive-program.php` default their `tax_query` to `['tu-xa', 'vua-hoc-vua-lam']` when no specific mode is selected, while strictly querying `'post_status' => 'publish'`. Since all out-of-scope programs (IDs 2013, 1786–1789) were transitioned to `draft` in Milestone M1, they are structurally prevented from leaking into public archives.
4. **Headline & Badge Semantic Consistency**:
   All 6 program presentation templates (`taxonomy-training_type.php`, `archive-program.php`, `class-query-filters.php`, `single-school.php`, `single-major.php`, `template-parts/compare/program-cards.php`) uniformly execute:
   - Major prefix cleaning (`ngành`, `cử nhân`, `kỹ sư`)
   - Study mode prefix cleaning (`hệ`)
   - Opportunity headline formula: `"Liên thông ngành " . $clean_major_name . " - " . $clean_type_name`
   Empirical simulation under aggressive accented/uppercase test inputs confirms zero occurrences of double prefixes like `"Liên thông ngành Ngành..."` or `"Hệ "`.
5. **Zero Regression Across All Milestones**:
   Re-running test suites across M2 (CPT relationships & campus isolation), M3 (taxonomy routing & canonical redirects), M4 (navigation, footer & homepage), and M5 (templates & presentation) yielded 100% passing results, confirming absolute backward compatibility and stability.

---

## 3. Caveats

- **External Term Prefixes**: Regex patterns target standard Vietnamese prefixes (`Hệ `, `hệ `, `ngành `, `cử nhân `, `kỹ sư `). If an editor creates an arbitrary new prefix (e.g. `Hình thức ` in term title), it will be preserved as-is. Standard terms configured in the system (`Từ xa`, `Vừa học vừa làm`) work flawlessly.
- **Dynamic JavaScript Compare Tray**: The server-side HTML contains all necessary data attributes (`data-compare-id`, `data-compare-title`, `data-compare-he`, etc.) matching 1:1 between SSR and AJAX cards. Browser-side localStorage interactions were verified via DOM attribute contract.
- No other caveats.

---

## 4. Conclusion

The implementation authored by `worker_m5` fully satisfies all acceptance criteria for Milestone M5:
- `single-program.php` has zero out-of-scope notices, contains a clear Liên thông quota announcement, and implements defensive campus fallback.
- `template-parts/banner.php` has zero forbidden strings and cleanly outputs "Hình thức học" headers and descriptions.
- Study mode archive queries are confined strictly to published in-scope programs.
- All regression suites from M2 through M5 pass with 0 failures.

**Verdict**: **APPROVE** (Proceed to Milestone M6).

---

## 5. Verification Method

To independently reproduce and verify this assessment:

1. **Execute All Regression & M5 Test Suites**:
   ```bash
   php tests/test-m2-empirical.php && \
   php tests/test-m3-adversarial.php && \
   php tests/test-m4-adversarial.php && \
   php tests/test-m5-templates-presentation.php && \
   php tests/test-m5-challenger-empirical.php
   ```
   *Expected Result*: All 5 suites exit with code 0 (243 assertions passing, 0 failures).

2. **Verify Zero Out-of-Scope Mentions in Core Templates**:
   ```bash
   php -r '
   $files = ["single-program.php", "template-parts/banner.php"];
   foreach ($files as $f) {
       $c = file_get_contents($f);
       foreach (["Văn bằng 2", "Chính quy", "hệ đào tạo"] as $term) {
           if (stripos($c, $term) !== false) echo "VIOLATION: $term in $f\n";
       }
   }
   echo "Scan complete.\n";
   '
   ```
   *Expected Result*: `Scan complete.` with zero violation notices.

3. **PHP Lint Check**:
   ```bash
   php -l single-program.php template-parts/banner.php taxonomy-training_type.php archive-program.php inc/core/class-query-filters.php
   ```
   *Expected Result*: `No syntax errors detected` across all files.
