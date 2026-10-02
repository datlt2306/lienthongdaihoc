## 2026-10-01T10:42:17Z
You are worker_m4 (Navigation, Homepage & Filters Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m4/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `header.php`, `footer.php`, `front-page.php`, `inc/config/class-defaults.php`, `inc/core/class-menus.php`, and running WP-CLI commands to update Menu ID 3 navigation items if needed.

Task Requirements (Milestone M4):
1. Header Navigation Menu:
   - Inspect Menu ID 3 (`primary-menu`). Using WP-CLI, ensure the menu items follow the standard structure:
     1. Trang chủ (/)
     2. Liên thông đại học (/he-dao-tao/) — with dynamic submenu or sub-items: Từ xa (/he-dao-tao/tu-xa/), Vừa học vừa làm (/he-dao-tao/vua-hoc-vua-lam/)
     3. Ngành học (/nganh-hoc/)
     4. Trường đại học (/truong-doi-tac/)
     5. Kiến thức liên thông (/tin-tuc/)
     6. Kiểm tra điều kiện (/kiem-tra-dieu-kien/)
   - In `header.php`: Update mobile drawer menu fallback (lines 99–128) to eliminate duplicate archive links and point to `/he-dao-tao/`.
   - In `inc/core/class-menus.php`: Ensure fallback menu `ltdh_default_primary_menu()` matches this standardized structure, and ensure submenu injection supports "Liên thông đại học" as well as "Hình thức học".
2. Footer Navigation:
   - In `footer.php`: Clean column 3 (lines 49–75).
     Remove dead '#' links and out-of-scope items ("Cao đẳng online / VB2", "Liên thông Đại Học chính quy", "Trung Cấp lên Đại học", "Đại học tại chức / VLVH").
     Replace with clean, valid in-scope links:
     - Liên thông Đại học Từ xa (/he-dao-tao/tu-xa/)
     - Liên thông Vừa học vừa làm (/he-dao-tao/vua-hoc-vua-lam/)
     - Trường đại học tuyển sinh (/truong-doi-tac/)
     - Ngành học liên thông (/nganh-hoc/)
     - Kiểm tra điều kiện (/kiem-tra-dieu-kien/)
3. Homepage Alignment (`front-page.php` & `inc/config/class-defaults.php`):
   - In `front-page.php:31`: Update hidden semantic H1 from '...Đại Học Từ Xa & Văn Bằng 2' to 'Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm'.
   - In `front-page.php:124`: Update search form action from '/he-dao-tao/tu-xa/' to `home_url( '/he-dao-tao/' )`.
   - In `front-page.php:336-350`: In the eligibility checker dropdown on homepage, remove the out-of-scope THPT option (`<option value="thpt">Học sinh tốt nghiệp THPT...</option>`). For Liên thông đại học, entry levels must be in-scope: Trung cấp, Cao đẳng, Đại học. Adjust options so all choices are valid for Liên thông.
   - In `front-page.php:851`: Update testimonial fallback `'program' => 'VB2 Công nghệ thông tin'` to `'Liên thông Công nghệ thông tin'`.
   - In `front-page.php:945`: Update news fallback `'Điều kiện học Văn bằng 2 đại học năm 2026'` to `'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026'`.
   - In `inc/config/class-defaults.php:65`: Update `'hero_badge_2'` from `'50+ chương trình - Liên thông, VB2, Từ xa'` to `'50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm'`.
4. Filter Harmonization:
   - Verify that all search/filter forms submit to `/he-dao-tao/`.
   - Verify that zero redundant "Loại tuyển sinh" or "Liên thông" filters exist.
5. Testing & Verification:
   - Run `php -l` on all modified files.
   - Write and execute an empirical test script `tests/test-m4-navigation-homepage.php` verifying all requirements.
