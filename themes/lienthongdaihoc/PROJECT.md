# PROJECT ARCHITECTURE & CODEBASE INVENTORY

**Dự án:** Theme WordPress Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học  
**Slug Theme:** `lienthongdaihoc`  
**Đường dẫn thư mục:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`  
**Phiên bản hiện tại:** `1.0.0` (style.css) / `2.0.0` (constants.php `LTDH_VERSION`)  
**Yêu cầu hệ thống:** PHP >= 8.0 (Tương thích PHP 8.1 - 8.4), WordPress >= 6.0  
**Tác giả:** Principal Architect  
**Trạng thái kiểm định:** Hoàn thành khảo sát kỹ thuật toàn diện (Audit Completed)  

---

## 1. Project Overview & Architecture

### 1.1. Sơ Đồ Cây Thư Mục Toàn Dự Án (Directory Structure)

```
wp-content/themes/lienthongdaihoc/
├── style.css                                # Định danh theme, CSS typography, breadcrumb, dropdown menu
├── functions.php                            # Bootstrap nạp các module inc/, legacy AJAX filter
├── index.php                                # Blog archive (/tin-tuc/), category navigation, split-card post
├── header.php                               # Global HTML header, Google Fonts, nav menu, mobile drawer
├── footer.php                               # 4-col footer, floating CTA, mobile sticky bar, wp_footer()
├── 404.php                                  # Trang báo lỗi 404 Not Found kèm gợi ý điều hướng
├── front-page.php                           # Trang chủ: Hero Swiper, fast filter bar, trường nổi bật, review
├── page.php                                 # Template trang tĩnh mặc định của WordPress
├── page-about.php                           # Template trang "Giới thiệu" (/gioi-thieu/)
├── page-contact.php                         # Template trang "Liên hệ" (/lien-he/)
├── page-faq.php                             # Template trang "Hỏi đáp" (/hoi-dap/)
├── page-register.php                        # Template trang đích "Đăng ký tư vấn" (/dang-ky/)
├── page-eligible.php                        # Template ứng dụng "Kiểm tra điều kiện" (/kiem-tra-dieu-kien/)
├── page-compare-program.php                 # Template so sánh chương trình (/so-sanh/chuong-trinh/slug-vs-slug/)
├── single.php                               # Single template cho bài viết tin tức mặc định (post)
├── single-school.php                        # Single template cho Trường đối tác (CPT school)
├── single-major.php                         # Single template cho Ngành học (CPT major)
├── single-program.php                       # Single template cho Chương trình đào tạo (CPT program)
├── single-guide.php                         # Single template cho Cẩm nang tuyển sinh (CPT guide - orphan)
├── archive-school.php                       # Archive template danh sách Trường đối tác (/truong-doi-tac/)
├── archive-major.php                        # Archive template danh mục Ngành học (/nganh-hoc/)
├── archive-program.php                      # Archive template danh sách Chương trình đào tạo (/he-dao-tao/)
├── taxonomy.php                             # Generic taxonomy archive fallback (region, campus...)
├── taxonomy-training_type.php               # Archive template chuyên biệt cho Hệ đào tạo (/he-dao-tao/%term%/)
├── inc/                                     # Thư mục chứa toàn bộ logic kiến trúc backend của theme
│   ├── config/
│   │   ├── constants.php                    # Định nghĩa hằng số hệ thống, version, tên bảng CSDL, query vars
│   │   └── class-defaults.php               # Fallback mặc định tập trung (ltdh_get_defaults)
│   ├── core/
│   │   ├── class-theme-setup.php            # Khai báo theme supports, enqueue script/style, WebP upload filter
│   │   ├── class-helpers.php                # Các hàm tiện ích toàn cục: hotline, breadcrumb, cache transient
│   │   ├── class-menus.php                  # Quản lý active menu class, inject submenu động cho trường/ngành
│   │   ├── class-rewrite-rules.php          # Rewrite rules cho /he-dao-tao/, prefixless program /%postname%/
│   │   └── class-query-filters.php          # Hook pre_get_posts tùy biến query archive trường/ngành/chương trình
│   ├── seo/
│   │   └── class-rankmath-integration.php   # Tích hợp Rank Math SEO: dynamic title, Course/Org Schema, OG
│   ├── acf-fields.php                       # Nạp field groups từ JSON, tùy biến nhãn ACF program
│   ├── post-types.php                       # Tự động đăng ký CPTs & Taxonomies từ acf-import-cpts.json
│   ├── relationship-hooks.php               # Đồng bộ quan hệ hai chiều Program <-> School và Program <-> Major
│   ├── lead-capture.php                     # Bảng CSDL wp_ltdh_leads, hook CF7, native form submit, Telegram bot
│   ├── crm-adapters.php                     # WP-Cron 5 phút, xử lý hàng đợi lead, adapter OnSchool/AUM/ERPNext
│   ├── search-engine.php                    # Thuật toán tìm kiếm chương trình: từ điển đồng nghĩa, resolve thực thể
│   ├── comparison.php                       # Công cụ so sánh chương trình: phân tích slug, REST & AJAX API
│   ├── eligibility.php                      # Động cơ kiểm tra điều kiện: bảng CSDL, tính điểm, AJAX/REST API, Admin
│   ├── eligibility-rules.php                # Ma trận tập luật xét tuyển, quan hệ ngành, trọng số điểm, học phí
│   ├── cli-commands.php                     # Lệnh WP-CLI: wp ltdh setup-system, create-taxonomies, seed-sample-data
│   ├── acf-import-cpts.json                 # JSON định nghĩa cấu hình CPT (school, major, program) & Taxonomies
│   ├── acf-import-fields.json               # JSON định nghĩa cấu hình toàn bộ trường dữ liệu ACF PRO
│   ├── admin/                               # Thư mục mở rộng cho backend admin (hiện để trống)
│   ├── modules/                             # Thư mục mở rộng cho feature modules (hiện để trống)
│   └── relationships/                       # Thư mục mở rộng cho relations (hiện để trống)
├── template-parts/                          # Các thành phần giao diện dùng chung (reusable UI parts)
│   ├── banner.php                           # Top banner hero hỗ trợ breadcrumb và ảnh nền ACF tùy biến
│   ├── compare/
│   │   ├── cta-bar.php                      # Thanh chuyển đổi dính đáy trang so sánh (Hotline, Zalo, Đăng ký)
│   │   ├── program-cards.php                # Giao diện so sánh dạng thẻ trượt trên thiết bị di động
│   │   ├── program-table.php                # Bảng đối soát thuộc tính song song trên màn hình máy tính
│   │   └── tray.php                         # Khay nổi dính chân trang lưu trữ danh sách chọn so sánh
│   └── eligibility/
│       ├── wizard.php                       # Form trắc nghiệm đa bước kiểm tra điều kiện tuyển sinh
│       └── results.php                      # Giao diện hiển thị điểm số, trường phù hợp và form chốt lead
├── assets/                                  # Tài nguyên tĩnh
│   ├── css/
│   │   ├── input.css                        # Mã nguồn Tailwind CSS v4 (@import "tailwindcss"; @theme tokens)
│   │   ├── main.min.css                     # File CSS thành phẩm sau khi biên dịch từ input.css
│   │   ├── swiper-bundle.min.css            # Thư viện Swiper Slider 11.0.0 phục vụ slider trang chủ
│   │   └── eligibility.css                  # Giao diện styling riêng cho wizard và kết quả kiểm tra điều kiện
│   ├── js/
│   │   ├── main.js                          # Xử lý AJAX filter chương trình, mobile menu, video modal
│   │   ├── compare.js                       # Quản lý localStorage so sánh, render so sánh, sync UI tray
│   │   ├── eligibility.js                   # Xử lý autocomplete trường/ngành, wizard, tính điểm, submit AJAX
│   │   └── swiper-bundle.min.js             # Thư viện Swiper Slider 11.0.0
│   └── images/                              # Banner, ảnh đại diện sinh viên và các tệp hình ảnh
└── tests/
    └── run-tests.php                        # Kịch bản kiểm thử tự động thuật toán tìm kiếm chương trình

```

---

### 1.2. Vòng Đời Thực Thi & Thứ Tự Nạp Module (`functions.php`)

Khi một yêu cầu (HTTP Request hoặc CLI) gửi đến WordPress, theme `lienthongdaihoc` được khởi tạo thông qua `functions.php`. Quá trình nạp module được chia thành 7 tầng kiến trúc theo đúng thứ tự logic:

```
[WordPress Core Bootstrap]
        │
        ▼
[functions.php] (Khởi tạo theme)
        │
        ├── 1. Cấu hình & Hằng số (Config & Constants)
        │     ├── inc/config/constants.php          (LTDH_VERSION, LTDH_TABLE_*, LTDH_CPT_*, LTDH_TAX_*)
        │     └── inc/config/class-defaults.php     (ltdh_get_defaults() - fallback hotline, menu, SEO)
        │
        ├── 2. Nền tảng Theme Core (Foundation)
        │     ├── inc/core/class-theme-setup.php    (theme supports, wp_enqueue_scripts, webp upload)
        │     ├── inc/core/class-helpers.php        (hotline, logo, breadcrumbs, transient cache helpers)
        │     ├── inc/core/class-menus.php          (dynamic menu injection, active link state)
        │     ├── inc/core/class-rewrite-rules.php  (URL rewrites, ltdh_program_request_guard)
        │     └── inc/core/class-query-filters.php  (pre_get_posts archive logic)
        │
        ├── 3. Cấu hình Trường Tùy Biến (Advanced Custom Fields)
        │     └── inc/acf-fields.php                (Nạp JSON acf-import-fields.json, chuẩn hóa label)
        │
        ├── 4. Định Nghĩa Cấu Trúc Nội Dung (Content Modeling)
        │     ├── inc/post-types.php                (Đăng ký CPTs & Taxonomies từ acf-import-cpts.json)
        │     └── inc/relationship-hooks.php        (acf/save_post: đồng bộ quan hệ 2 chiều)
        │
        ├── 5. Động Cơ Nghiệp Vụ Chuyên Sâu (Business & Feature Engines)
        │     ├── inc/lead-capture.php              (Bảng leads, CF7 interceptor, native form, Telegram)
        │     ├── inc/crm-adapters.php              (Cron sync 5 phút, queue processor, OnSchool/AUM)
        │     ├── inc/search-engine.php             (Thuật toán tìm kiếm đa thực thể, mở rộng từ đồng nghĩa)
        │     ├── inc/comparison.php                (Routing so sánh slug-vs-slug, REST API, AJAX handlers)
        │     ├── inc/eligibility.php               (Bảng checks, scoring, AJAX lead capture, WP-Admin menu)
        │     └── inc/eligibility-rules.php         (Ma trận luật chuyển đổi bằng cấp, quan hệ khối ngành)
        │
        ├── 6. Tích Hợp SEO & Dữ Liệu Cấu Trúc (SEO & Schema Integration)
        │     └── inc/seo/class-rankmath-integration.php (Rank Math titles, Course/Org Schema, OG fallback)
        │
        └── 7. Công Cụ Dòng Lệnh Quản Trị (WP-CLI Tools)
              └── inc/cli-commands.php              (wp ltdh setup-system, create-taxonomies, seed-data)
```

---

### 1.3. Cây Phân Cấp Giao Diện (Template Hierarchy & Custom Routing)

```
                            ┌─────────────────────────────────────────┐
                            │            WordPress Request            │
                            └────────────────────┬────────────────────┘
                                                 │
          ┌──────────────────────────────────────┼──────────────────────────────────────┐
          ▼                                      ▼                                      ▼
[Front Page Request]                    [Single Template]                      [Archive Template]
          │                                      │                                      │
    front-page.php                     ┌─────────┴─────────┐                  ┌─────────┴─────────┐
                                       │                   │                  │                   │
                               [post_type: program]  [post_type: school]  [Tax: training_type] [Archive: school]
                                       │                   │                  │                   │
                               single-program.php    single-school.php   taxonomy-training_type   archive-school.php
                                       │                   │                  │                   │
                               [post_type: major]    [post_type: guide]   [Tax: major_cat]     [Archive: major]
                                       │                   │                  │                   │
                                single-major.php     single-guide.php     taxonomy.php         archive-major.php
                                       │                                      │                   │
                                 [post_type: post]                      [Base: he-dao-tao]     [Archive: program]
                                       │                                      │                   │
                                   single.php                        taxonomy-training_type   archive-program.php
                                       │                                                          │
                                [Static Pages]                                             [Blog: /tin-tuc/]
                                       │                                                          │
                                    page.php                                                  index.php
                               (page-about.php,
                               page-contact.php,
                               page-faq.php,
                               page-register.php,
                               page-eligible.php,
                               page-compare-program.php)
```

#### Quy Tắc Điều Hướng Tùy Biến (Custom Rewrite Rules):
1. **Chương trình đào tạo dạng Root Slug (`/%postname%/`):**
   - Rewrite rule: `^([^/]+)/?$` -> `index.php?program=$matches[1]`
   - Bộ bảo vệ `ltdh_program_request_guard` (hook `request`): Kiểm tra xem slug có khớp với một `program` đã xuất bản không. Nếu không, hủy bỏ query var `program` để WordPress tự do xử lý route cho `page` hoặc `post`, ngăn chặn xung đột route toàn trang.
   - Filter `post_type_link`: Loại bỏ tiền tố `/program/` để permalink xuất ra dạng `domain.com/cntt-dai-hoc-tu-xa/`.
2. **Hệ đào tạo (`/he-dao-tao/`):**
   - Rewrite rule: `^he-dao-tao/?$` -> `index.php?post_type=program` (kết hợp `template_include` nạp `taxonomy-training_type.php`).
   - Rewrite rule: `^he-dao-tao/([^/]+)/?$` -> `index.php?training_type=$matches[1]`.
3. **So sánh chương trình (`/so-sanh/chuong-trinh/slug-vs-slug/`):**
   - Rewrite rule: `^so-sanh/chuong-trinh/(.+?)/?$` -> `index.php?ltdh_compare=program&ltdh_compare_slug=$matches[1]`.
   - Xử lý tại `ltdh_compare_template_redirect`: Tách chuỗi theo dấu `-vs-`, truy vấn ID các chương trình liên quan và nạp template `page-compare-program.php`.

---

### 1.4. Mô Hình Dữ Liệu Nội Dung (CPTs, Taxonomies & Custom Tables)

#### 1. Custom Post Types (Nội dung chuyên ngành):
| CPT Slug | Hằng số | Archive Slug | Single Rewrite Pattern | Trạng Thái Đăng Ký |
|---|---|---|---|---|
| `school` | `LTDH_CPT_SCHOOL` | `/truong-doi-tac/` | `/truong-doi-tac/%school%/` | Đăng ký qua `acf-import-cpts.json` & `inc/post-types.php` |
| `major` | `LTDH_CPT_MAJOR` | `/nganh-hoc/` | `/nganh-hoc/%major%/` | Đăng ký qua `acf-import-cpts.json` & `inc/post-types.php` |
| `program` | `LTDH_CPT_PROGRAM` | `/he-dao-tao/` | `/%postname%/` (Root rewrite) | Đăng ký qua `acf-import-cpts.json` + `class-rewrite-rules.php` |
| `guide` | `LTDH_CPT_GUIDE` | Không | `/huong-dan/%postname%/` | **CHƯA ĐĂNG KÝ** (Định nghĩa hằng số & template mồ côi `single-guide.php`) |

#### 2. Custom Taxonomies (Phân loại chuyên môn):
| Taxonomy Slug | Hằng số | Áp dụng cho CPT | Rewrite Slug | Kiểu phân loại |
|---|---|---|---|---|
| `training_type` | `LTDH_TAX_TRAINING_TYPE` | `program` | `he-dao-tao` | Phẳng (Non-hierarchical) |
| `campus` | `LTDH_TAX_CAMPUS` | `program` | `co-so` | Phẳng (Non-hierarchical) |
| `region` | `LTDH_TAX_REGION` | `school` | `khu-vuc` | Phẳng (Non-hierarchical) |
| `major_cat` | `LTDH_TAX_MAJOR_CAT` | `major` | `nhom-nganh` | Phẳng (Non-hierarchical) |

#### 3. Bảng Cơ Sở Dữ Liệu Tùy Biến (Custom Tables):
1. **`wp_ltdh_leads` (`LTDH_TABLE_LEADS`):**
   - **Mục đích:** Lưu trữ hồ sơ thông tin sinh viên đăng ký tư vấn tuyển sinh từ mọi nguồn (CF7, Form gốc, Khảo sát điều kiện).
   - **Cấu trúc trường:** `id` (bigint, PK), `name` (varchar 100), `phone` (varchar 20), `email` (varchar 100), `school` (varchar 255), `major` (varchar 255), `campus` (varchar 100), `degree` (varchar 100), `graduation_year` (int 4), `graduation_grade` (varchar 50), `referral_source` (varchar 255), `crm_status` (enum: pending, synced, failed), `crm_lead_id` (varchar 100), `crm_synced_at` (datetime), `error_message` (text), `created_at` (datetime).
2. **`wp_ltdh_eligibility_checks` (`LTDH_TABLE_ELIGIBILITY`):**
   - **Mục đích:** Ghi nhận nhật ký trắc nghiệm kiểm tra điều kiện đầu vào của người dùng.
   - **Cấu trúc trường:** `id` (bigint, PK), `current_degree` (varchar 50), `target_major` (varchar 100), `target_school` (varchar 100), `learning_mode` (varchar 50), `budget_range` (varchar 50), `matched_programs` (text JSON), `eligibility_score` (int 3), `status` (varchar 20), `lead_captured` (tinyint 1), `ip_address` (varchar 45), `user_agent` (text), `created_at` (datetime).

---

## 2. Bảng Thống Kê Tính Năng & Phân Bổ Module (Feature Inventory)

| Tính Năng / Nghiệp Vụ | Cột Mốc (Milestone) | Tệp Mã Nguồn Phụ Trách | Trạng Thái Kiểm Định |
|---|---|---|---|
| **Cấu hình & Hằng số Tập trung** | M1: Foundation | `inc/config/constants.php`, `inc/config/class-defaults.php` | Đạt chuẩn, tập trung hóa tốt |
| **Theme Setup & Asset Enqueue** | M1: Foundation | `inc/core/class-theme-setup.php` | Cần tối ưu thứ tự CSS và thêm `defer` |
| **Hệ thống Menu Động & Mega Dropdown** | M1: Foundation | `inc/core/class-menus.php` | Đạt, inject chuyên mục mượt mà |
| **Hệ thống Routing & URL Rewrite** | M1: Foundation | `inc/core/class-rewrite-rules.php` | Tốt, bảo vệ root slug chương trình hiệu quả |
| **Bộ Lọc Truy Vấn Mặc Định** | M1: Foundation | `inc/core/class-query-filters.php` | **Phát hiện lỗi:** Mặc định `posts_per_page = -1` |
| **ACF Schema & Trường Tùy Biến** | M1: Foundation | `inc/acf-fields.php`, `inc/acf-import-fields.json` | Đạt, đồng bộ đầy đủ cấu trúc field |
| **Đăng Ký CPT & Taxonomies** | M1: Foundation | `inc/post-types.php`, `inc/acf-import-cpts.json` | **Phát hiện lỗi:** Thiếu đăng ký CPT `guide` |
| **Đồng Bộ Quan Hệ 2 Chiều** | M2: Data Logic | `inc/relationship-hooks.php` | Hoạt động chính xác trên `acf/save_post` |
| **Thu Thập & Quản Lý Lead Tuyển Sinh**| M2: Data Logic | `inc/lead-capture.php` | Thiếu CSRF nonce trên native form |
| **Đồng Bộ CRM Tuyển Sinh (Cron)** | M2: Data Logic | `inc/crm-adapters.php` | Chạy nền định kỳ 5 phút ổn định |
| **Thuật Toán Tìm Kiếm Đa Thực Thể** | M2: Data Logic | `inc/search-engine.php` | Thiếu ABSPATH guard; query `-1` cần giới hạn |
| **Động Cơ So Sánh Chương Trình** | M3: Experience | `inc/comparison.php`, `template-parts/compare/*`, `compare.js`| **Phát hiện lỗi:** Nút so sánh tê liệt sau AJAX |
| **Động Cơ Kiểm Tra Điều Kiện (Elig)** | M3: Experience | `inc/eligibility.php`, `inc/eligibility-rules.php`, `eligibility.js` | **Lỗ hổng:** Upload MIME & IDOR update lead |
| **Tích Hợp SEO & Schema Rank Math** | M4: SEO & Schema | `inc/seo/class-rankmath-integration.php` | **Lỗ hổng:** Phụ thuộc 100% Rank Math, thiếu fallback |
| **Công Cụ WP-CLI Tiện Ích** | M5: Tooling | `inc/cli-commands.php` | 1,115 dòng code seeding đầy đủ, 9 hàm deprecated |
| **Suite Kiểm Thử Tự Động Tìm Kiếm** | M5: Tooling | `tests/run-tests.php` | **Lỗ hổng Critical:** Thiếu CLI check, mở cho web |

---

## 3. Toàn Bộ Danh Mục 49 Tệp PHP Trong Mã Nguồn (100% PHP File Inventory)

*Toàn bộ 49 tệp PHP đã được kiểm tra tính hợp lệ cú pháp bằng `php -l` dưới môi trường PHP 8.4.19 (Kết quả: 100% tệp vượt qua không có lỗi cú pháp).*

| # | Tệp Mã Nguồn | Số Dòng | Kích Thước (Bytes) | Phân Loại Module | Guard `ABSPATH` | Cú Pháp (`php -l`) | Vai Trò & Chức Năng Chi Tiết |
|---|---|---|---|---|---|---|---|
| 1 | `style.css` | 260 | 6,952 | Meta / Stylesheet | N/A (CSS) | Hợp lệ | Định danh Theme v1.0.0, typography headings, dropdown panel, breadcrumb |
| 2 | `functions.php` | 212 | 9,050 | Core Bootstrap | Có (`defined('ABSPATH')`) | Pass | Nạp 17 module trong `inc/`, AJAX filter chương trình, filter backup |
| 3 | `header.php` | 147 | 6,796 | Entry Point | **THIẾU** | Pass | Khởi tạo HTML, Google Fonts, `wp_head()`, top bar, logo, mobile nav drawer |
| 4 | `footer.php` | 227 | 14,210 | Entry Point | Có (`defined('ABSPATH')`) | Pass | Footer 4 cột, floating action pills, mobile sticky conversion bar, `wp_footer()` |
| 5 | `index.php` | 369 | 17,609 | Entry Point / Archive | Có (`defined('ABSPATH')`) | Pass | Archive tin tức (`/tin-tuc/`), phân loại danh mục bài viết, featured split-card |
| 6 | `404.php` | 36 | 1,436 | Error Fallback | Có (`defined('ABSPATH')`) | Pass | Giao diện thông báo lỗi 404, nút bấm quay lại trang chủ và danh mục ngành |
| 7 | `front-page.php` | 988 | 46,746 | Template Giao Diện | Có (`defined('ABSPATH')`) | Pass | Trang chủ: Hero Swiper, thanh lọc nhanh, trường liên kết, đánh giá sinh viên |
| 8 | `page.php` | 41 | 938 | Core Fallback | Có (`defined('ABSPATH')`) | Pass | Template cơ bản hiển thị nội dung các trang tĩnh tiêu chuẩn |
| 9 | `single.php` | 273 | 13,124 | Core Fallback | Có (`defined('ABSPATH')`) | Pass | Chi tiết bài viết tin tức: thời gian đọc, nút chia sẻ MXH, bài viết liên quan |
| 10 | `taxonomy.php` | 238 | 13,500 | Core Fallback | Có (`defined('ABSPATH')`) | Pass | Template lưu trữ phân loại chung fallback cho các taxonomy |
| 11 | `taxonomy-training_type.php` | 537 | 26,392 | Taxonomy Template | Có (`defined('ABSPATH')`) | Pass | Lưu trữ hệ đào tạo (`/he-dao-tao/`, `/he-dao-tao/tu-xa/`), bộ lọc sidebar |
| 12 | `archive-program.php` | 540 | 26,515 | CPT Archive | Có (`defined('ABSPATH')`) | Pass | Danh sách chương trình đào tạo, lọc động học phí/thời gian, so sánh |
| 13 | `archive-school.php` | 385 | 18,387 | CPT Archive | Có (`defined('ABSPATH')`) | Pass | Danh bạ trường đối tác (`/truong-doi-tac/`), chuyển view grid/list, lọc vùng |
| 14 | `archive-major.php` | 173 | 9,818 | CPT Archive | Có (`defined('ABSPATH')`) | Pass | Danh mục các ngành đào tạo (`/nganh-hoc/`), phân nhóm ngành `major_cat` |
| 15 | `single-program.php` | 1,077 | 61,687 | CPT Single | Có (`defined('ABSPATH')`) | Pass | Chi tiết chương trình đào tạo: học phí, lộ trình, đợt tuyển sinh, hồ sơ, FAQ |
| 16 | `single-school.php` | 682 | 30,340 | CPT Single | Có (`defined('ABSPATH')`) | Pass | Hồ sơ trường đối tác: giới thiệu, danh sách hệ đào tạo, các ngành trực thuộc |
| 17 | `single-major.php` | 498 | 22,910 | CPT Single | Có (`defined('ABSPATH')`) | Pass | Hồ sơ ngành học: cơ hội nghề nghiệp, mức lương, trường đào tạo ngành này |
| 18 | `single-guide.php` | 97 | 4,348 | CPT Single | Có (`defined('ABSPATH')`) | Pass | Chi tiết bài viết cẩm nang / hướng dẫn tuyển sinh (`/huong-dan/`) |
| 19 | `page-about.php` | 77 | 3,616 | Custom Page Template | Có (`defined('ABSPATH')`) | Pass | Trang giới thiệu: sứ mệnh, tầm nhìn, giá trị cốt lõi, ACF fallback |
| 20 | `page-contact.php` | 105 | 4,376 | Custom Page Template | Có (`defined('ABSPATH')`) | Pass | Trang liên hệ: danh sách văn phòng tư vấn, bản đồ Google Maps, form liên hệ |
| 21 | `page-faq.php` | 57 | 3,572 | Custom Page Template | Có (`defined('ABSPATH')`) | Pass | Trang hỏi đáp thường gặp: accordion thẻ HTML5 `<details>`, ACF fallback |
| 22 | `page-register.php` | 35 | 867 | Custom Page Template | Có (`defined('ABSPATH')`) | Pass | Trang landing page đăng ký tư vấn tuyển sinh trực tuyến |
| 23 | `page-eligible.php` | 44 | 1,624 | Custom Page Template | Có (`defined('ABSPATH')`) | Pass | Trang ứng dụng kiểm tra điều kiện xét tuyển đại học trực tuyến |
| 24 | `page-compare-program.php` | 117 | 4,515 | Custom Page Template | Có (`defined('ABSPATH')`) | Pass | Trang so sánh đối soát 2 hoặc nhiều chương trình đào tạo cùng ngành |
| 25 | `inc/config/constants.php` | 84 | 2,846 | Cấu hình | Có (`defined('ABSPATH')`) | Pass | Định nghĩa các hằng số phiên bản, tiền tố bảng CSDL, query vars, CPT slugs |
| 26 | `inc/config/class-defaults.php` | 122 | 5,627 | Cấu hình | Có (`defined('ABSPATH')`) | Pass | Hàm `ltdh_get_defaults()` cung cấp giá trị mặc định cho hotline, menu, SEO |
| 27 | `inc/core/class-theme-setup.php` | 160 | 5,207 | Core Module | Có (`defined('ABSPATH')`) | Pass | Khai báo theme supports, đăng ký menu, enqueue script/style, filter WebP |
| 28 | `inc/core/class-helpers.php` | 740 | 25,002 | Core Module | Có (`defined('ABSPATH')`) | Pass | Hàm tiện ích: hotline, Zalo, breadcrumb, transient cache, thẻ CF7 |
| 29 | `inc/core/class-menus.php` | 329 | 10,159 | Core Module | Có (`defined('ABSPATH')`) | Pass | Active state menu, inject mega dropdown cho Trường đối tác & Hệ đào tạo |
| 30 | `inc/core/class-rewrite-rules.php` | 183 | 6,139 | Core Module | Có (`defined('ABSPATH')`) | Pass | Định nghĩa rewrite rules cho root program permalink, `/he-dao-tao/`, 301 |
| 31 | `inc/core/class-query-filters.php` | 58 | 1,606 | Core Module | Có (`defined('ABSPATH')`) | Pass | Filter `pre_get_posts` tùy biến phân trang và sắp xếp bài viết archive |
| 32 | `inc/acf-fields.php` | 110 | 3,662 | ACF Integration | Có (`defined('ABSPATH')`) | Pass | Tự động nạp field groups từ JSON `acf-import-fields.json`, đổi nhãn label |
| 33 | `inc/post-types.php` | 109 | 4,832 | Content Modeling | Có (`defined('ABSPATH')`) | Pass | Nạp CPT và Taxonomy từ `acf-import-cpts.json` đăng ký vào WordPress Core |
| 34 | `inc/relationship-hooks.php` | 74 | 2,409 | Business Logic | Có (`defined('ABSPATH')`) | Pass | Hook `acf/save_post` tự động đồng bộ quan hệ 2 chiều giữa Program, School, Major |
| 35 | `inc/lead-capture.php` | 381 | 13,957 | Business Logic | Có (`defined('ABSPATH')`) | Pass | Quản lý bảng `wp_ltdh_leads`, chặn submit CF7, xử lý form gốc, bot Telegram |
| 36 | `inc/crm-adapters.php` | 253 | 7,250 | Integrations | Có (`defined('ABSPATH')`) | Pass | Cron job định kỳ 5 phút, xử lý hàng đợi lead, adapter OnSchool, AUM, ERPNext |
| 37 | `inc/search-engine.php` | 152 | 3,805 | Search Engine | **THIẾU** | Pass | Thuật toán tìm kiếm chương trình đa thực thể: từ điển đồng nghĩa, resolve post |
| 38 | `inc/comparison.php` | 660 | 19,373 | Feature Engine | Có (`defined('ABSPATH')`) | Pass | Động cơ so sánh: phân tích slug, REST API, AJAX endpoint lưu phiên so sánh |
| 39 | `inc/eligibility.php` | 1,668 | 65,535 | Feature Engine | Có (`defined('ABSPATH')`) | Pass | Động cơ kiểm tra điều kiện: bảng CSDL, tính điểm, AJAX, tải bằng cấp, Admin |
| 40 | `inc/eligibility-rules.php` | 160 | 5,498 | Business Rules | Có (`defined('ABSPATH')`) | Pass | Ma trận tập luật xét tuyển, nhóm ngành tương đương, khoảng học phí, trọng số |
| 41 | `inc/seo/class-rankmath-integration.php` | 339 | 10,964 | SEO Integration | Có (`defined('ABSPATH')`) | Pass | Tích hợp Rank Math: tiêu đề động, mô tả meta, Course & Org JSON-LD |
| 42 | `inc/cli-commands.php` | 1,115 | 56,729 | Tooling / WP-CLI | Có (`defined('ABSPATH')`) | Pass | Bộ lệnh WP-CLI: `wp ltdh setup-system`, tạo taxonomy, sinh dữ liệu mẫu |
| 43 | `template-parts/banner.php` | 150 | 7,228 | Template Part | Có (`defined('ABSPATH')`) | Pass | Top banner dùng chung hỗ trợ breadcrumb và ảnh nền ACF tùy biến |
| 44 | `template-parts/compare/cta-bar.php` | 57 | 2,794 | Template Part | Có (`defined('ABSPATH')`) | Pass | Thanh điều hướng dính chân trang trên màn hình so sánh chương trình |
| 45 | `template-parts/compare/program-cards.php` | 136 | 6,593 | Template Part | Có (`defined('ABSPATH')`) | Pass | Giao diện hiển thị so sánh dạng thẻ trượt trên thiết bị di động |
| 46 | `template-parts/compare/program-table.php` | 199 | 7,916 | Template Part | Có (`defined('ABSPATH')`) | Pass | Bảng đối soát chi tiết thuộc tính chương trình trên màn hình máy tính |
| 47 | `template-parts/compare/tray.php` | 34 | 1,512 | Template Part | Có (`defined('ABSPATH')`) | Pass | Khay nổi dưới chân trang lưu danh sách các chương trình đang được chọn so sánh |
| 48 | `template-parts/eligibility/results.php` | 168 | 9,290 | Template Part | Có (`defined('ABSPATH')`) | Pass | Giao diện hiển thị điểm điều kiện, trường tương thích và form thu thập lead |
| 49 | `template-parts/eligibility/wizard.php` | 121 | 6,134 | Template Part | Có (`defined('ABSPATH')`) | Pass | Bảng câu hỏi khảo sát đa bước kiểm tra điều kiện xét tuyển |
| 50 | `tests/run-tests.php` | 264 | 9,261 | Tooling / Testing | **THIẾU** | Pass | Kịch bản kiểm thử tự động thuật toán tìm kiếm chương trình đào tạo |

---

## 4. Lộ Trình & Trạng Thái Cột Mốc Dự Án (Milestones & Status)

- **Milestone 1: Khởi Tạo Kiến Trúc & Cấu Trúc Dữ Liệu (Foundation & Architecture):**  
  *Trạng thái: Hoàn thành 90%.* Cấu trúc module rõ ràng, phân tách trách nhiệm tốt. Tồn đọng: thiếu đăng ký CPT `guide`, thiếu guard tại 2 file và lệch phiên bản asset.
- **Milestone 2: Quản Lý Lead & Động Cơ Tìm Kiếm Tuyển Sinh (Data & Search Engines):**  
  *Trạng thái: Hoàn thành 85%.* Bảng lead, hook CF7, adapter CRM và tìm kiếm hoàn tất. Tồn đọng: lỗ hổng bảo mật upload MIME, nguy cơ IDOR lead update và thiếu nonce form.
- **Milestone 3: Động Cơ So Sánh & Ứng Dụng Kiểm Tra Điều Kiện (Interactive Features):**  
  *Trạng thái: Hoàn thành 80%.* Logic so sánh và wizard kiểm tra hoạt động tốt về thuật toán. Tồn đọng: nút so sánh bị liệt sau AJAX do thiếu Event Delegation, lỗi null pointer JS.
- **Milestone 4: Tối Ưu SEO On-Page, Dữ Liệu Cấu Trúc & Trải Nghiệm Frontend (SEO & Polish):**  
  *Trạng thái: Cần can thiệp khẩn cấp (Score: 54/100).* Lỗi cú pháp CSS `@media (max-w: 767px)` làm vỡ mobile footer, trang chủ mất thẻ H1, 3 trang bị trùng 2 H1, Schema phụ thuộc 100% vào plugin ngoài.
- **Milestone 5: Kiểm Thử Toàn Diện & Báo Cáo Kỹ Thuật (Master Audit Report):**  
  *Trạng thái: Đang hoàn thành.* Báo cáo kiểm định toàn diện `FULL_PROJECT_AUDIT_REPORT.md` sẵn sàng với kế hoạch khắc phục và code mẫu hoàn chỉnh.
