# Handoff Report: Milestone M4 Empirical Navigation & Menu Challenge

**Agent**: `challenger_m4_1` (Navigation & Menu Challenger)  
**Parent Agent ID**: `f7ebf938-afab-4e8b-b557-505007c00d2d`  
**Date**: 2026-10-01  
**Milestone**: M4 (Navigation, Menus & Homepage Alignment)  
**Verdict**: **APPROVE**

---

## 1. Observation

Direct empirical observations obtained through WP-CLI execution, code inspection, and test harness execution:

1. **WordPress Database Menu ID 3 (`primary-menu`)**:
   Command:
   ```bash
   /Users/ken/bin/wp menu item list 3 --fields=db_id,title,link,position --format=table --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   Verbatim output:
   ```text
   +-------+----------------------+----------------------+----------+
   | db_id | title                | link                 | position |
   +-------+----------------------+----------------------+----------+
   | 402   | Trang chủ            | /                    | 1        |
   | 410   | Liên thông đại học   | /he-dao-tao/         | 2        |
   | 404   | Ngành học            | /nganh-hoc/          | 3        |
   | 403   | Trường đại học       | /truong-doi-tac/     | 4        |
   | 405   | Kiến thức liên thông | /tin-tuc/            | 5        |
   | 525   | Kiểm tra điều kiện   | /kiem-tra-dieu-kien/ | 6        |
   +-------+----------------------+----------------------+----------+
   ```
   Positions 1 to 6 correspond exactly to the requested titles and URLs.

2. **Simulation of `wp_nav_menu()` and Dynamic Submenu Injection**:
   - Location Registration in `inc/core/class-theme-setup.php:36-39`:
     ```php
     register_nav_menus( [
         'primary-menu' => 'Header Navigation Menu',
         'footer-menu'  => 'Footer Navigation Menu',
     ] );
     ```
   - Invocation in `header.php:29-35` and `header.php:78-84`:
     ```php
     wp_nav_menu( [
         'theme_location' => 'primary-menu',
         'container'      => false,
         'menu_class'     => 'nav-primary-menu',
         'fallback_cb'    => 'ltdh_default_primary_menu',
     ] );
     ```
   - WP-CLI execution of `wp_nav_menu()`:
     ```bash
     /Users/ken/bin/wp eval 'echo wp_nav_menu(["theme_location" => "primary-menu", "echo" => false]);' --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
     ```
     Verbatim rendered HTML under Item 410 ("Liên thông đại học"):
     ```html
     <li id="menu-item-410" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-410"><a href="/he-dao-tao/">Liên thông đại học</a>
     <ul class="sub-menu">
     	<li id="menu-item-526" class="menu-item menu-item-type-taxonomy menu-item-object-training_type menu-item-526"><a href="http://localhost:10028/he-dao-tao/tu-xa/">Từ xa</a></li>
     	<li id="menu-item-527" class="menu-item menu-item-type-taxonomy menu-item-object-training_type menu-item-527"><a href="http://localhost:10028/he-dao-tao/vua-hoc-vua-lam/">Vừa học vừa làm</a></li>
     </ul>
     </li>
     ```
     Sub-items for `Từ xa` (`/he-dao-tao/tu-xa/`) and `Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`) are dynamically injected with `menu-item-has-children` assigned to the parent.
   - *Architectural Note on Location Slug*: When simulating `wp_nav_menu(["theme_location" => "primary"])`, WordPress core does not find an assigned menu because the registered slug is `'primary-menu'`, falling back to `wp_page_menu`. In `header.php`, the theme consistently specifies `'theme_location' => 'primary-menu'`.

3. **Footer Column 3 Audit in `footer.php:75-102`**:
   The HTML for Column 3 contains:
   ```html
   <!-- Column 3: Training Programs -->
   <div>
       <h3 class="font-display font-extrabold text-white mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
           <span class="w-[3px] h-3 inline-block" style="background-color: #00a2f4;"></span>Chương trình đào tạo
       </h3>
       <ul class="space-y-2.5 text-sm">
           <li class="flex items-center">
               <span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
               <a href="<?php echo esc_url( home_url( '/he-dao-tao/tu-xa/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Liên thông Đại học Từ xa</a>
           </li>
           <li class="flex items-center">
               <span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
               <a href="<?php echo esc_url( home_url( '/he-dao-tao/vua-hoc-vua-lam/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Liên thông Vừa học vừa làm</a>
           </li>
           <li class="flex items-center">
               <span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
               <a href="<?php echo esc_url( home_url( '/truong-doi-tac/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Trường đại học tuyển sinh</a>
           </li>
           <li class="flex items-center">
               <span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
               <a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Ngành học liên thông</a>
           </li>
           <li class="flex items-center">
               <span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
               <a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Kiểm tra điều kiện</a>
           </li>
       </ul>
   </div>
   ```
   - Total links: 5
   - Dead `#` links: 0
   - Out-of-scope terms (VB2, Cao đẳng online, Chính quy, Tại chức, VLVH): 0
   - All links point to active, valid in-scope URLs (`/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`, `/truong-doi-tac/`, `/nganh-hoc/`, `/kiem-tra-dieu-kien/`).
   - Bottom legal links in `footer.php:128-129`: point to `/chinh-sach-bao-mat/` and `/dieu-khoan/` (0 dead links).

4. **Execution of `php tests/test-m4-navigation-homepage.php`**:
   Output:
   ```text
   ===================================================================
   EMPIRICAL TEST SUITE: M4 NAVIGATION, HOMEPAGE & FILTERS
   ===================================================================
   SECTION 1: Header Navigation Menu & Fallbacks -> 6 PASSED
   SECTION 2: Footer Navigation Column 3 & Policy Links -> 4 PASSED
   SECTION 3: Homepage Alignment & Fallbacks -> 7 PASSED
   SECTION 4: Filter Harmonization & Redundancy Check -> 2 PASSED
   SECTION 5: PHP Syntax Integrity -> 5 PASSED
   ===================================================================
   SUMMARY: 24 PASSED, 0 FAILED
   ===================================================================
   ```

5. **Adversarial Stress Harness (`tests/test-m4-adversarial.php`)**:
   Authored and executed 18 adversarial tests covering:
   - Case-insensitivity, accented characters, and leading/trailing whitespace variations for 'Liên thông đại học', 'Liên thông', 'Hình thức học', 'Hệ đào tạo' (all passed).
   - Negative controls ensuring unrelated menu titles are untouched (all passed).
   - Theme location discrimination (footer-menu, empty, primary-menu) (all passed).
   - Error handling when `get_terms()` returns `WP_Error` or empty array (graceful degradation, 0 fatal errors).
   - ID collision prevention with large preexisting IDs (all passed).
   - Fallback menu rendering across 8 distinct `REQUEST_URI` inputs including unset/null `REQUEST_URI` (all passed).
   - Column 3 link count and strict banned word grep (all passed).
   Result: `SUMMARY: 18 PASSED, 0 FAILED`.

6. **Regression Verification on Milestones M2 and M3**:
   - `tests/test-m3-adversarial.php`: 38 PASSED, 0 FAILED.
   - `tests/test-m3-empirical.php`: 24 PASSED, 0 FAILED.
   - `tests/test-m3-edge-cases-empirical.php`: ALL PASSED.
   - `tests/test-m3-label-facets-empirical.php`: ALL PASSED.
   - `tests/test-m2-empirical.php`: 46 PASSED, 0 FAILED.

---

## 2. Logic Chain

1. **Menu ID 3 Ordering & Slugs**:
   - WP-CLI directly queried `wp_posts` and term relationship tables for Menu ID 3 (Observation 1).
   - Every single position (1 through 6) perfectly matches the contract:
     1. Trang chủ (`/`)
     2. Liên thông đại học (`/he-dao-tao/`)
     3. Ngành học (`/nganh-hoc/`)
     4. Trường đại học (`/truong-doi-tac/`)
     5. Kiến thức liên thông (`/tin-tuc/`)
     6. Kiểm tra điều kiện (`/kiem-tra-dieu-kien/`)

2. **Submenu Dynamic Injection & Location Identifier**:
   - `ltdh_dynamic_menu_submenu_injection()` hooks into `wp_nav_menu_objects`.
   - Inspection of `class-theme-setup.php` and `header.php` shows that the theme registers and uses the location slug `'primary-menu'`.
   - When `wp_nav_menu(['theme_location' => 'primary-menu'])` is called, the filter detects item 410 ("Liên thông đại học"), appends `menu-item-has-children`, and injects taxonomy terms `tu-xa` and `vua-hoc-vua-lam` as valid sub-items with unique IDs.
   - In addition, it injects the top hot majors and "Xem tất cả ngành →" under "Ngành học".
   - Fallback rendering in `ltdh_render_fallback_menu('primary')` also correctly emits nested `sub-menu` markup.

3. **Footer Sanitization**:
   - Column 3 was inspected directly from `footer.php` and analyzed via AST/regex (Observation 3).
   - Dead `#` anchors that previously existed for out-of-scope items (VB2, Cao đẳng online, Chính quy) have been completely removed.
   - All 5 remaining links are strictly within scope for Liên thông đại học.

4. **Homepage & Filter Harmonization**:
   - Semantic H1 on the homepage contains zero references to VB2 and focuses on Liên thông: Từ xa & Vừa học vừa làm.
   - Homepage search forms submit to canonical `/he-dao-tao/` instead of `/tu-xa/`.
   - Eligibility entry levels reflect Trung cấp, Cao đẳng, and Đại học (zero THPT).
   - News and testimonial fallbacks are purged of VB2.
   - Zero occurrences of obsolete filter "Loại tuyển sinh" or "loai_tuyen_sinh" exist.

---

## 3. Caveats

- In WordPress, theme locations are arbitrary strings defined via `register_nav_menus()`. Here the location is `'primary-menu'` (with `'primary'` used as a config key in `class-defaults.php`). If an external script calls `wp_nav_menu(['theme_location' => 'primary'])` instead of `primary-menu`, WordPress will not find the menu assignment. This is not a bug in theme execution since `header.php` explicitly calls `'primary-menu'`.
- Front-end mobile menu drawer open/close animations and touch events depend on vanilla JS (`main.min.js`), which was not altered in this milestone and will be verified during milestone E2E.

---

## 4. Conclusion

**Verdict**: **APPROVE**

Milestone M4 implementation meets 100% of the requirements and acceptance criteria:
- Database Menu ID 3 positions 1 to 6 correspond exactly to the required specification.
- Dynamic submenu injection correctly renders sub-items for `Từ xa` (`/he-dao-tao/tu-xa/`) and `Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`) under "Liên thông đại học".
- `footer.php` Column 3 contains zero `#` links, zero out-of-scope text (VB2, Cao đẳng online, Chính quy), and only valid in-scope links.
- `tests/test-m4-navigation-homepage.php` (24 assertions) and `tests/test-m4-adversarial.php` (18 assertions) pass with 0 errors.
- Zero regressions across prior milestone test suites.

---

## 5. Verification Method

To independently reproduce and verify this verdict:

1. **Verify Menu ID 3 in WordPress Database**:
   ```bash
   /Users/ken/bin/wp menu item list 3 --fields=db_id,title,link,position --format=table --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected output*: Positions 1 through 6 correspond to Trang chủ, Liên thông đại học, Ngành học, Trường đại học, Kiến thức liên thông, Kiểm tra điều kiện.

2. **Verify Dynamic Submenu Injection Output**:
   ```bash
   /Users/ken/bin/wp eval 'echo wp_nav_menu(["theme_location" => "primary-menu", "echo" => false]);' --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected output*: Contains `sub-menu` with `/he-dao-tao/tu-xa/` ("Từ xa") and `/he-dao-tao/vua-hoc-vua-lam/` ("Vừa học vừa làm").

3. **Run M4 Test Suite**:
   ```bash
   php tests/test-m4-navigation-homepage.php
   ```
   *Expected output*: `SUMMARY: 24 PASSED, 0 FAILED`.

4. **Run Adversarial Stress Harness**:
   ```bash
   php tests/test-m4-adversarial.php
   ```
   *Expected output*: `ADVERSARIAL SUITE SUMMARY: 18 PASSED, 0 FAILED`.

5. **Run Regression Suites**:
   ```bash
   php tests/test-m3-adversarial.php
   php tests/test-m3-empirical.php
   php tests/test-m2-empirical.php
   ```
   *Expected output*: All suites pass with 0 failures.
