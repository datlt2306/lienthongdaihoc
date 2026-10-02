# Forensic Integrity Audit Report: Milestone M4 (Navigation, Homepage & Filters)

**Auditor**: `auditor_m4` (M4 Forensic Integrity Auditor)  
**Parent Agent ID**: `f7ebf938-afab-4e8b-b557-505007c00d2d`  
**Date**: 2026-10-01  
**Milestone**: M4 (Navigation, Homepage & Filters)  
**Verdict**: `CLEAN`  

---

## Forensic Audit Report

**Work Product**: Milestone M4 Changes (`header.php`, `footer.php`, `front-page.php`, `inc/config/class-defaults.php`, `inc/core/class-menus.php`, and WordPress Database Menu ID 3)  
**Profile**: General Project  
**Integrity Mode**: `development` (per `ORIGINAL_REQUEST.md` line 275)  
**Verdict**: **`CLEAN`**  

### Phase Results
- **Hardcoded test results**: **PASS** — Zero hardcoded test return values, zero artificial PASS triggers, zero pre-computed result mappings.
- **Facade detection**: **PASS** — Complete functional implementations for fallback menu rendering, active class handling, and dynamic submenu injection.
- **Test-sniffing detection**: **PASS** — Zero environment checking (`IS_TEST`, `PHPUNIT`, script names, etc.) in production theme files.
- **Pre-populated artifacts**: **PASS** — Zero stale or fabricated log/result files.
- **Data deletion check**: **PASS** — Zero posts deleted (100 programs, 21 schools, 34 majors intact); zero taxonomy terms deleted (all 4 `training_type` terms intact).
- **WP-CLI Menu DB check**: **PASS** — Menu ID 3 genuinely updated in database tables `wp_posts` & `wp_postmeta` (6 items in exact standardized order).
- **Build & Syntax Verification**: **PASS** — `php -l` passed on 100% of modified files with 0 syntax errors.
- **Empirical Execution**: **PASS** — 24/24 tests in `tests/test-m4-navigation-homepage.php` passed with 0 failures; regression test suites (`test-m3-label-facets-empirical.php`, `test-m3-empirical.php`, `test-m3-edge-cases-empirical.php`, `test-m3-adversarial.php`, `test-m2-empirical.php`) all passed.

---

## 1. Observation

Direct empirical observations gathered during forensic inspection:

1. **Git Diff Inspection across Target Files**:
   - `header.php`: Unmodified in working directory because it delegates menu rendering dynamically to `wp_nav_menu([ 'theme_location' => 'primary-menu', 'fallback_cb' => 'ltdh_default_primary_menu' ])`. Header markup already complies with all structural conventions.
   - `footer.php`: Lines 80–102: Replaced dead `#` links and out-of-scope offerings (`Học đại học từ xa`, `Cao đẳng online / VB2`, `Liên thông Đại Học chính quy`, `Trung Cấp lên Đại học`, `Đại học tại chức / VLVH`) with 5 valid in-scope links:
     * `Liên thông Đại học Từ xa` (`/he-dao-tao/tu-xa/`)
     * `Liên thông Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`)
     * `Trường đại học tuyển sinh` (`/truong-doi-tac/`)
     * `Ngành học liên thông` (`/nganh-hoc/`)
     * `Kiểm tra điều kiện` (`/kiem-tra-dieu-kien/`)
     Lines 128–129: Replaced dead `#` links for privacy policy and terms with `home_url( '/chinh-sach-bao-mat/' )` and `home_url( '/dieu-khoan/' )`.
   - `front-page.php`:
     * Line 31: Updated hidden H1 to `Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm`.
     * Line 126 & 170: Form action and reset link updated to `home_url( '/he-dao-tao/' )` (eliminating obsolete direct reference to `/he-dao-tao/tu-xa/`).
     * Line 159: Select option label updated to `-- Chọn hình thức học --`.
     * Lines 327–347: Eligibility heading updated to `Bạn có đủ điều kiện học<br>Liên thông Đại học?`; removed out-of-scope item "Học sinh tốt nghiệp THPT"; replaced with 3 valid Liên thông entry groups: `Tốt nghiệp Trung cấp`, `Tốt nghiệp Cao đẳng`, `Đã có bằng Đại học`.
     * Lines 851 & 854: Testimonial fallback role updated to `Liên thông Công nghệ thông tin` (zero VB2).
     * Line 945: News fallback updated to `Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026` (zero VB2).
   - `inc/config/class-defaults.php`:
     * Lines 34–61: Updated `'primary'` and `'mobile'` fallback configurations to exactly 6 items with nested sub-items for `tu-xa` and `vua-hoc-vua-lam`. Eliminated obsolete link `/he-dao-tao/tu-xa/` labeled "Chương trình".
     * Lines 77–79: Added `'hero_badge_2' => '50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm'`; updated hero badge subtext to `'Liên thông Đại học: Từ xa & Vừa học vừa làm'`.
   - `inc/core/class-menus.php`:
     * Lines 50–75: Upgraded `ltdh_render_fallback_menu()` to support nested `$mi['sub']` lists, rendering `.sub-menu` markup cleanly with proper escaping.
     * Line 151: Broadened matching in `ltdh_dynamic_menu_submenu_injection()` to include `'liên thông đại học'`, `'liên thông'`, while preserving backward compatibility with `'hình thức học'`, `'hệ đào tạo'`, `'hình thức đào tạo'`.
     * Lines 163–175: Restricted injected terms to whitelist `['tu-xa', 'vua-hoc-vua-lam']`.
     * Line 203: Updated major matching to check `in_array( $title, [ 'chuyên ngành', 'ngành học' ], true )`.

2. **WordPress Database Verification (Menu ID 3)**:
   - Command: `wp menu item list 3 --fields=db_id,title,link,position,type,object,object_id,menu_item_parent --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"`
   - Output:
     ```text
     +-------+--------------------+----------------------+----------+--------+--------+-----------+------------------+
     | db_id | title              | link                 | position | type   | object | object_id | menu_item_parent |
     +-------+--------------------+----------------------+----------+--------+--------+-----------+------------------+
     | 402   | Trang chủ          | /                    | 1        | custom | custom | 402       | 0                |
     | 410   | Liên thông đại học | /he-dao-tao/         | 2        | custom | custom | 410       | 0                |
     | 404   | Ngành học          | /nganh-hoc/          | 3        | custom | custom | 404       | 0                |
     | 403   | Trường đại học     | /truong-doi-tac/     | 4        | custom | custom | 403       | 0                |
     | 405   | Kiến thức liên thô | /tin-tuc/            | 5        | custom | custom | 405       | 0                |
     | 525   | Kiểm tra điều kiện | /kiem-tra-dieu-kien/ | 6        | custom | custom | 525       | 0                |
     +-------+--------------------+----------------------+----------+--------+--------+-----------+------------------+
     ```
   - Menu items are genuine records stored in WordPress database tables (`wp_posts` with `post_type = 'nav_menu_item'`).

3. **Live Rendering Verification via WP-CLI**:
   - Command: `wp eval 'wp_nav_menu(["theme_location" => "primary-menu", "echo" => true]);' --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"`
   - Output confirmed:
     * Item 402: "Trang chủ" (`/`)
     * Item 410: "Liên thông đại học" (`/he-dao-tao/`) with `.sub-menu`:
       - Item 526: "Từ xa" (`http://localhost:10028/he-dao-tao/tu-xa/`)
       - Item 527: "Vừa học vừa làm" (`http://localhost:10028/he-dao-tao/vua-hoc-vua-lam/`)
     * Item 404: "Ngành học" (`/nganh-hoc/`) with top major sub-menu and "Xem tất cả ngành →"
     * Item 403: "Trường đại học" (`/truong-doi-tac/`)
     * Item 405: "Kiến thức liên thông" (`/tin-tuc/`)
     * Item 525: "Kiểm tra điều kiện" (`/kiem-tra-dieu-kien/`)

4. **Data Deletion Verification**:
   - Command: `wp db query "SELECT post_type, post_status, COUNT(*) as cnt FROM wp_posts GROUP BY post_type, post_status;"`
   - Results:
     * `major`: 34 published
     * `school`: 20 published, 1 draft (Total 21 — HCCT ID 1662 safely drafted in M1)
     * `program`: 95 published, 5 draft (Total 100 — 5 out-of-scope programs safely drafted in M1)
     * `post`: 29 published
     * `page`: 9 published, 1 draft
     * Zero hard-deleted posts.
   - Command: `wp term list training_type --fields=term_id,name,slug,count`
   - Results:
     * `tu-xa` (ID 7, count 94)
     * `vua-hoc-vua-lam` (ID 8, count 1)
     * `chinh-quy` (ID 21, count 0)
     * `van-bang-2` (ID 27, count 0)
     * Zero taxonomy terms deleted.

5. **Test-Sniffing & Facade Audit**:
   - Ripgrep searches for `test-m4`, `RUNNING_TEST`, `TEST_MODE`, `IS_TEST` returned 0 matches in production code.
   - All logic paths in `inc/core/class-menus.php` perform genuine data fetching and output escaping.

6. **Prior Milestone Test Suite Analysis**:
   - `test-m4-navigation-homepage.php`: 24 PASSED, 0 FAILED.
   - `test-m3-label-facets-empirical.php`: 24 PASSED, 0 FAILED.
   - `test-m3-empirical.php`: 38 PASSED, 0 FAILED.
   - `test-m3-edge-cases-empirical.php`: 19 PASSED, 0 FAILED.
   - `test-m3-adversarial.php`: 38 PASSED, 0 FAILED.
   - `test-m2-empirical.php`: 46 PASSED, 0 FAILED.
   - `test-m3-forensic.php`: 80 passed, 2 failed in Check 3. Forensic investigation of lines 129–131 confirmed that Check 3 asserted the old M3 navigation index (`$nav_defaults['primary'][3]['label'] === 'Hình thức học'`). In M4, per user requirements (ORIGINAL_REQUEST.md lines 300–307), "Liên thông đại học" was moved to Position 2 (index 1), and "Trường đại học" to Position 4 (index 3). Hence, the failure was due to the legacy test checking superseded M3 ordering.

---

## 2. Logic Chain

1. **Verification of Scope and Non-Interference**:
   - Observation 1 demonstrates that all changes made across `footer.php`, `front-page.php`, `inc/config/class-defaults.php`, and `inc/core/class-menus.php` are strictly bounded to the Milestone M4 requirements.
   - No core logic outside navigation, homepage copy/forms, and footer was touched.

2. **Verification of Database Integrity**:
   - Observation 2 & 4 prove empirically via WP-CLI that Menu ID 3 items exist as genuine records in `wp_posts` and `wp_postmeta`.
   - Post counts for all CPTs (`program`, `school`, `major`) and taxonomy terms remain identical to Milestone M1 and M2 baselines. No records were deleted.

3. **Verification of Authenticity (No Facades / No Test-Sniffing)**:
   - Observation 3 & 5 demonstrate that the menu rendering pipeline executes genuine WordPress Core calls (`wp_nav_menu()`) and theme filter hooks (`wp_nav_menu_objects`).
   - The output contains full nested HTML markup with correctly dynamically resolved terms and permalinks.
   - There are zero test environment sniffers or mock-only branches in production theme files.

4. **Verification of Regression Freedom**:
   - Observation 6 verifies that all 6 independent test suites pass cleanly.
   - The only failure in `test-m3-forensic.php` was proven to be an obsolete assumption about menu ordering superseded by M4's explicit user brief.

---

## 3. Caveats

- `template-parts/eligibility/wizard.php`: Contains the interactive wizard steps. Homepage entry cards were scrubbed of THPT in M4, but internal wizard step questions were preserved and not altered as they belong to dedicated wizard milestones.
- `tests/test-m3-forensic.php`: Check 3 expects the old M3 array order for `$nav_defaults['primary'][3]`. It does not affect runtime application behavior since M4 tests verify the new standardized order. Per auditor constraints, test files from previous milestones were not modified.

---

## 4. Conclusion

The work product delivered by `worker_m4` for Milestone M4 (Navigation, Homepage & Filters) meets all forensic integrity standards:
- **Zero facades**: All menu and fallback implementations are genuine and fully functional.
- **Zero test-sniffing**: No environment bypasses exist.
- **Zero hardcoding**: Nav menus use standard WordPress data structures and dynamic hooks.
- **Zero data loss**: All posts and taxonomy terms in the database are intact.
- **100% compliant**: Header navigation, footer links, homepage copy, and filter routes strictly follow the user specification.

**Final Verdict**: **`CLEAN`**

---

## 5. Verification Method

To independently reproduce the forensic verification:

1. **Run M4 Empirical Test Suite**:
   ```bash
   php tests/test-m4-navigation-homepage.php
   ```
   *Expected result*: `SUMMARY: 24 PASSED, 0 FAILED`.

2. **Verify PHP Syntax across all M4 files**:
   ```bash
   php -l header.php
   php -l footer.php
   php -l front-page.php
   php -l inc/config/class-defaults.php
   php -l inc/core/class-menus.php
   ```
   *Expected result*: `No syntax errors detected` on all files.

3. **Verify Database Menu ID 3 via WP-CLI**:
   ```bash
   wp menu item list 3 --fields=db_id,title,link,position --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected result*: 6 items in exact order (Trang chủ, Liên thông đại học, Ngành học, Trường đại học, Kiến thức liên thông, Kiểm tra điều kiện).

4. **Verify Live Render via WP-CLI**:
   ```bash
   wp eval 'wp_nav_menu(["theme_location" => "primary-menu", "echo" => true]);' --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected result*: Outputs valid HTML with dynamic sub-menu containing "Từ xa" and "Vừa học vừa làm" under "Liên thông đại học".

5. **Verify Database Post & Taxonomy Term Counts**:
   ```bash
   wp db query "SELECT post_type, post_status, COUNT(*) as cnt FROM wp_posts WHERE post_type IN ('program', 'school', 'major') GROUP BY post_type, post_status;" --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected result*: `program` (95 pub + 5 draft = 100), `school` (20 pub + 1 draft = 21), `major` (34 pub).
