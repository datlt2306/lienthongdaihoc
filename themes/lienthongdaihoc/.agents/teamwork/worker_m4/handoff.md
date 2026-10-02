# Handoff Report: Milestone M4 (Navigation, Homepage & Filters)

**Agent**: `worker_m4` (Navigation, Homepage & Filters Worker)  
**Parent Agent ID**: `f7ebf938-afab-4e8b-b557-505007c00d2d`  
**Date**: 2026-10-01  
**Milestone**: M4 (Navigation, Homepage & Filters)  

---

## 1. Observation

Direct observations from codebase inspection and execution before modification:

1. **Menu ID 3 in WordPress Database (`primary-menu`)**:
   Running `wp menu item list 3 --fields=db_id,title,link,position`:
   ```text
   +-------+--------------------+----------------------+----------+
   | db_id | title              | link                 | position |
   +-------+--------------------+----------------------+----------+
   | 402   | Trang chủ          | /                    | 1        |
   | 403   | Trường đối tác     | /truong-doi-tac      | 2        |
   | 404   | Chuyên ngành       | /nganh-hoc/          | 3        |
   | 410   | Hệ đào tạo         | /he-dao-tao          | 4        |
   | 405   | Tin tức            | /tin-tuc             | 5        |
   | 525   | Kiểm tra điều kiện | /kiem-tra-dieu-kien/ | 6        |
   +-------+--------------------+----------------------+----------+
   ```
   *Observed discrepancy*: "Hệ đào tạo" was at position 4 with title "Hệ đào tạo". Position 2 was "Trường đối tác", position 3 was "Chuyên ngành". This diverged from the standardized structure: 1. Trang chủ, 2. Liên thông đại học, 3. Ngành học, 4. Trường đại học, 5. Kiến thức liên thông, 6. Kiểm tra điều kiện.

2. **Submenu Injection Logic in `inc/core/class-menus.php`**:
   Line 137 previously only matched:
   ```php
   if ( in_array( $title, [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo' ], true ) ) {
   ```
   When the menu item title was updated to "Liên thông đại học", dynamic injection did not trigger. Furthermore, line 187 only checked `if ($title === 'chuyên ngành')`, which would fail if the title was "Ngành học". Additionally, `ltdh_render_fallback_menu()` at lines 51–61 rendered flat `<li>` items without supporting nested `sub` structures.

3. **Footer Navigation in `footer.php:76-102`**:
   Column 3 contained dead `#` links and out-of-scope educational offerings:
   ```php
   <a href="<?php echo esc_url( home_url('/he-dao-tao/tu-xa/') ); ?>">Học đại học từ xa</a>
   <a href="#">Cao đẳng online / VB2</a>
   <a href="#">Liên thông Đại Học chính quy</a>
   <a href="#">Trung Cấp lên Đại học</a>
   <a href="#">Đại học tại chức / VLVH</a>
   ```
   Lines 128–129 had `#` anchors for "Chính sách bảo mật" and "Điều khoản dịch vụ".

4. **Homepage Alignments in `front-page.php` & `inc/config/class-defaults.php`**:
   - `front-page.php:31`: `<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học, Văn Bằng 2 & Đại Học Từ Xa</h1>` (promoted Văn Bằng 2).
   - `front-page.php:126`: `<form action="<?php echo esc_url(home_url('/he-dao-tao/tu-xa/')); ?>" method="GET" ...>` (hardcoded to `/tu-xa/` instead of `/he-dao-tao/`).
   - `front-page.php:170`: Reset button pointed to `/he-dao-tao/tu-xa/`.
   - `front-page.php:336`: `['title' => 'Học sinh tốt nghiệp THPT', 'desc' => 'Xét học bạ tuyển thẳng']` (out-of-scope for Liên thông).
   - `front-page.php:851`: `'role' => 'VB2 Công nghệ thông tin'` and content referred to "Văn bằng 2 CNTT".
   - `front-page.php:945`: `['title' => 'Điều kiện học Văn bằng 2 đại học năm 2026', ...]` (out-of-scope news article).
   - `inc/config/class-defaults.php:65`: `'hero_badges'` had `'subtext' => 'Liên thông, VB2, Từ xa'`.

---

## 2. Logic Chain

From these direct observations, the following step-by-step reasoning was applied:

1. **Header Navigation Standardization**:
   - Based on Observation 1, Menu ID 3 was updated using WP-CLI `wp menu item update`:
     - Item 410 -> Title: "Liên thông đại học", Link: "/he-dao-tao/", Position: 2
     - Item 404 -> Title: "Ngành học", Link: "/nganh-hoc/", Position: 3
     - Item 403 -> Title: "Trường đại học", Link: "/truong-doi-tac/", Position: 4
     - Item 405 -> Title: "Kiến thức liên thông", Link: "/tin-tuc/", Position: 5
     - Item 525 -> Title: "Kiểm tra điều kiện", Link: "/kiem-tra-dieu-kien/", Position: 6
   - Based on Observation 2, `inc/core/class-menus.php` was updated:
     - Extended title matching to include `'liên thông đại học'`, `'liên thông'`, while preserving backward compatibility with `'hình thức học'`, `'hệ đào tạo'`, `'hình thức đào tạo'`.
     - Added `'menu-item-has-children'` class to the parent item.
     - Updated major matching to check `in_array( $title, [ 'chuyên ngành', 'ngành học' ], true )`.
     - Upgraded `ltdh_render_fallback_menu()` to support nested `$mi['sub']` items so the fallback rendering correctly emits sub-menus with "Từ xa" and "Vừa học vừa làm".
   - Updated `inc/config/class-defaults.php`:
     - Aligned `'primary'` and `'mobile'` fallback configurations to the 6 standardized items, with sub-items for `tu-xa` and `vua-hoc-vua-lam`, eliminating the obsolete `/he-dao-tao/tu-xa/` link labeled "Chương trình".

2. **Footer Navigation Sanitization**:
   - Based on Observation 3, replaced Column 3 lines in `footer.php` with 5 valid in-scope links:
     - `Liên thông Đại học Từ xa` (`/he-dao-tao/tu-xa/`)
     - `Liên thông Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`)
     - `Trường đại học tuyển sinh` (`/truong-doi-tac/`)
     - `Ngành học liên thông` (`/nganh-hoc/`)
     - `Kiểm tra điều kiện` (`/kiem-tra-dieu-kien/`)
   - Replaced dead `#` links in footer bottom with `home_url('/chinh-sach-bao-mat/')` and `home_url('/dieu-khoan/')`.

3. **Homepage Alignment**:
   - Based on Observation 4, modified `front-page.php`:
     - Line 31: Updated hidden H1 to `Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm`.
     - Line 126 & 170: Updated search form `action` and reset link to `home_url( '/he-dao-tao/' )`.
     - Line 159: Updated select default label to `-- Chọn hình thức học --`.
     - Line 327 & 333–347: Updated eligibility heading to `Bạn có đủ điều kiện học<br>Liên thông Đại học?`, and replaced THPT item with valid Liên thông entry levels:
       * Tốt nghiệp Trung cấp (Liên thông lên Đại học)
       * Tốt nghiệp Cao đẳng (Liên thông miễn giảm tín chỉ)
       * Đã có bằng Đại học (Liên thông văn bằng thứ hai)
     - Line 851 & 854: Updated testimonial fallback to `Liên thông Công nghệ thông tin`.
     - Line 945: Updated news fallback to `Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026`.
   - In `inc/config/class-defaults.php:65`:
     - Added `'hero_badge_2' => '50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm'`.
     - Updated first entry of `'hero_badges'` subtext to `'Liên thông Đại học: Từ xa & Vừa học vừa làm'`.

4. **Filter Harmonization**:
   - Confirmed all filter and search forms across the theme submit to canonical route `/he-dao-tao/` or `/he-dao-tao/{term}/`.
   - Verified that zero redundant "Loại tuyển sinh" or "loai_tuyen_sinh" filters exist across all templates and code files.

---

## 3. Caveats

- `template-parts/eligibility/wizard.php`: Contains full interactive wizard questions. While homepage entry-point cards were scrubbed of THPT, changes to wizard-internal questions fall under future evaluation/milestones or dedicated wizard tasks.
- Static HTML caches / object transients: WordPress transients for hot majors (`ltdh_hot_majors_data`) will automatically refresh upon expiration (DAY_IN_SECONDS) or via `wp cache flush`.

---

## 4. Conclusion

Milestone M4 is completely implemented and verified:
- Menu ID 3 navigation items in database follow the exact requested structure (Trang chủ -> Liên thông đại học [sub: Từ xa, Vừa học vừa làm] -> Ngành học -> Trường đại học -> Kiến thức liên thông -> Kiểm tra điều kiện).
- Fallback menu and dynamic submenu injection handle both desktop and mobile navigation cleanly with nested sub-menus.
- Footer Column 3 is 100% clean of dead links and out-of-scope text, displaying only valid in-scope links.
- Homepage (`front-page.php`) has 0 references to VB2 in semantic H1, search action, testimonials, or news fallbacks, and THPT was replaced with in-scope Liên thông entry levels.
- All modified files pass `php -l` with 0 syntax errors.
- Comprehensive empirical test harness `tests/test-m4-navigation-homepage.php` passes all 24 assertions with 0 failures.

---

## 5. Verification Method

To independently verify the implementation:

1. **Run the empirical test suite**:
   ```bash
   php tests/test-m4-navigation-homepage.php
   ```
   *Expected output*: `SUMMARY: 24 PASSED, 0 FAILED`.

2. **Verify PHP Syntax on all modified files**:
   ```bash
   php -l header.php
   php -l footer.php
   php -l front-page.php
   php -l inc/config/class-defaults.php
   php -l inc/core/class-menus.php
   ```
   *Expected output*: `No syntax errors detected` on all files.

3. **Verify WordPress Menu ID 3 Items**:
   ```bash
   wp menu item list 3 --fields=db_id,title,link,position --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected output*:
   - Position 1: Trang chủ (`/`)
   - Position 2: Liên thông đại học (`/he-dao-tao/`)
   - Position 3: Ngành học (`/nganh-hoc/`)
   - Position 4: Trường đại học (`/truong-doi-tac/`)
   - Position 5: Kiến thức liên thông (`/tin-tuc/`)
   - Position 6: Kiểm tra điều kiện (`/kiem-tra-dieu-kien/`)

4. **Verify Dynamic Submenu Injection Output**:
   ```bash
   wp eval 'wp_nav_menu(["theme_location" => "primary-menu", "echo" => true]);' --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
   ```
   *Expected output*: Contains `sub-menu` under "Liên thông đại học" with "Từ xa" (`/he-dao-tao/tu-xa/`) and "Vừa học vừa làm" (`/he-dao-tao/vua-hoc-vua-lam/`).

5. **Verify Zero Regressions on Previous Milestone Suites**:
   ```bash
   php tests/test-m3-label-facets-empirical.php
   php tests/test-m3-empirical.php
   php tests/test-m3-edge-cases-empirical.php
   php tests/test-m3-adversarial.php
   php tests/test-m2-empirical.php
   ```
   *Expected output*: All suites pass with 0 failures.
