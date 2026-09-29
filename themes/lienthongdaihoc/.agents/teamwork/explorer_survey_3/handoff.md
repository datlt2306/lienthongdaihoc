# Báo Cáo Kiểm Định Chi Tiết: Quản Lý Asset, SEO On-Page, Schema Markup & Frontend Integrity

**Dự án:** Theme WordPress Liên Thông Đại Học (`lienthongdaihoc`)  
**Người thực hiện:** Explorer Survey 3 (Frontend, SEO & Schema Surveyor)  
**Thời gian hoàn thành:** 2026-09-25T05:15:00Z  
**Đường dẫn tài liệu:** `.agents/teamwork/explorer_survey_3/handoff.md`

---

## 1. Observation (Các Quan Sát Thực Tế & Bằng Chứng Mã Nguồn)

### 1.1. Quản lý Asset (CSS / JS Enqueue & Inline Assets)

#### Bảng Thống Kê Chi Tiết Toàn Bộ Asset Được Enqueue
| STT | Handle | Loại | Tệp Nguồn | Vị Trí Code Gọi Enqueue | Phụ Thuộc (Deps) | Phiên Bản (Version) | Vị Trí Tải (in_footer / media) | Async / Defer Strategy |
|---|---|---|---|---|---|---|---|---|
| 1 | `ltdh-main-style` | CSS | `style.css` | `inc/core/class-theme-setup.php:49` | `[]` | `filemtime` / `LTDH_VERSION` | `all` (Header) | Không |
| 2 | `swiper-css` | CSS | `assets/css/swiper-bundle.min.css` | `inc/core/class-theme-setup.php:53` (Chỉ `is_front_page()`) | `[]` | `'11.0.0'` (Hardcoded) | `all` (Header) | Không |
| 3 | `swiper-js` | JS | `assets/js/swiper-bundle.min.js` | `inc/core/class-theme-setup.php:56` (Chỉ `is_front_page()`) | `[]` | `'11.0.0'` (Hardcoded) | `true` (Footer) | Không |
| 4 | `ltdh-theme-styles` | CSS | `assets/css/main.min.css` | `inc/core/class-theme-setup.php:63` | `[]` | `filemtime` | `all` (Header) | Không |
| 5 | `ltdh-fallback-js` *(hoặc `ltdh-theme-js`)* | JS | `assets/js/main.js` *(Do `main.bundle.js` không tồn tại)* | `inc/core/class-theme-setup.php:71` | `[]` | `LTDH_VERSION` (`'2.0.0'`) | `true` (Footer) | Không |
| 6 | `ltdh-compare-js` | JS | `assets/js/compare.js` | `inc/core/class-theme-setup.php:81` (Có điều kiện `ltdh_compare_should_load()`) | `[]` | `LTDH_VERSION` (`'2.0.0'`) | `true` (Footer) | Không |
| 7 | `ltdh-eligibility-css` | CSS | `assets/css/eligibility.css` | `inc/eligibility.php:138` (Chỉ `is_page_template('page-eligible.php')`) | `[]` | `filemtime` / `LTDH_VERSION` | `all` (Header) | Không |
| 8 | `ltdh-eligibility-js` | JS | `assets/js/eligibility.js` | `inc/eligibility.php:145` (Chỉ `is_page_template('page-eligible.php')`) | `[]` | `filemtime` / `LTDH_VERSION` | `true` (Footer) | Không |

#### Các Tệp Asset Bị Hardcode / Inline Trực Tiếp Trong Template
1. **Google Fonts hardcoded trực tiếp trong `<head>`:**
   - **Tệp:** `header.php:7-9`
   - **Mã nguồn:**
     ```html
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;850;900&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
     ```
   - **Vấn đề:** Không thông qua `wp_enqueue_style()`. Không thể hook để tối ưu tải font, prefetch hoặc defer.

2. **Các khối JavaScript inline trong Template:**
   - `header.php:99-143`: Script điều khiển Mobile Drawer Menu (`#mobile-menu-toggle`, `#mobile-menu-close`, overlay).
   - `front-page.php:83-104`: Script khởi tạo Swiper Hero Slider.
   - `front-page.php:647-700`: Script trích xuất YouTube Video ID và điều khiển modal video iframe.
   - `single-school.php:162-205`: Script điều khiển đóng/mở mô tả giới thiệu trường (`#school-intro-toggle`).
   - `single-major.php:73-116`: Script điều khiển đóng/mở mô tả ngành học (sao chép nguyên văn logic từ `single-school.php`).
   - `single-program.php:413-440`: Script khai báo hàm toàn cục `ltdhSwitchBatchTab(btn, idx)` bên trong markup đợt tuyển sinh.
   - `archive-program.php:512-537`: Script toggle filter sidebar trên mobile và bộ lọc tìm kiếm ngành trực tiếp (`#major-search-filter`).
   - `taxonomy-training_type.php:509-534`: Script sao chép y hệt từ `archive-program.php`.
   - `archive-major.php:163-170`: Script điều khiển filter sidebar trên mobile.
   - `inc/eligibility.php:1341-1357` & `1649-1665`: Khối script checkbox bulk select trong trang quản trị WP-Admin.

3. **Các khối CSS inline trong Template:**
   - `front-page.php:107-130`: Style ghi đè cho `.hero-swiper .swiper-pagination-bullet-active`.
   - `front-page.php:453-480`: Style cho `.cert-slider-wrapper` và `.cert-swiper`.
   - `single-school.php:32-80`: Style cho `.prose-card-list`.
   - `footer.php:183-221`: Style cho mobile footer và bottom fixed padding.

4. **Trùng lặp 100% hơn 250 dòng CSS giữa `style.css` và `assets/css/input.css`:**
   - Toàn bộ các class sau đây có mặt đồng thời tại cả `style.css` (dòng 14-259) và `assets/css/input.css` (dòng 19-300):
     - Override heading font-weight (`h1...h6 { font-weight: 600 !important; }`).
     - Dropdown panel animation (`.dropdown-panel`).
     - Breadcrumb styles (`.ltdh-breadcrumb`).
     - Dynamic WP Menus (`.nav-primary-menu`, `.sub-menu`).
     - Mobile Nav styling (`.nav-mobile-menu`).
     - Contact Form 7 controls (`.ltdh-cf7-form input`, `select`, `submit`).
     - Utility `.scrollbar-hide`.
   - Cả hai file đều được enqueue ra frontend (`ltdh-main-style` tải `style.css`, `ltdh-theme-styles` tải `main.min.css` được build từ `input.css`), gây trùng lặp và tăng kích thước tải trang vô ích.

5. **Lệch Thứ Tự Tải Phụ Thuộc CSS (CSS Enqueue Cascade Conflict):**
   - Tại `inc/core/class-theme-setup.php`:
     - Dòng 49 enqueue `ltdh-main-style` (`style.css`) với `deps = []`.
     - Dòng 63 enqueue `ltdh-theme-styles` (`main.min.css` - Tailwind base + components) với `deps = []`.
   - Kết quả: `style.css` tải trước `main.min.css`. Các quy tắc trong `style.css` phải lạm dụng `!important` trên hầu hết mọi selector để không bị Tailwind ghi đè.

6. **Bất đồng bộ phiên bản Asset (Versioning Discrepancy):**
   - `style.css:5` ghi `Version: 1.0.0`.
   - `inc/config/constants.php:12` ghi `define( 'LTDH_VERSION', '2.0.0' );`.
   - `inc/core/class-theme-setup.php`:
     - CSS dùng `filemtime()` tự động cập nhật cache buster.
     - JS (`ltdh-fallback-js`, `ltdh-compare-js`) dùng hằng số cố định `LTDH_VERSION` (`2.0.0`). Khi lập trình viên sửa đổi JS, trình duyệt người dùng không tự xóa cache trừ khi `LTDH_VERSION` được sửa bằng tay.
   - Thư viện ngoài Swiper (`swiper-css`, `swiper-js`) bị hardcode phiên bản tĩnh `'11.0.0'`.

---

### 1.2. SEO On-Page & Ngữ Nghĩa HTML5

#### Bảng Phát Hiện Lỗi SEO On-Page & Semantic HTML
| Vị Trí File & Số Dòng | Hạng Mục | Mô Tả Thực Tế | Mức Độ |
|---|---|---|---|
| `front-page.php:1-989` | Heading H1 | **Hoàn toàn KHÔNG có thẻ `<h1>` trên toàn bộ trang chủ.** Phần Hero chỉ là ảnh banner slider. Thẻ tiêu đề đầu tiên là `<h2>` tại dòng 192 (`5 ngành đào tạo hot nhất`). | **Critical** |
| `single-major.php:26` & `36` | Heading H1 | **Trùng lặp 2 thẻ `<h1>` trên cùng một trang.** Dòng 26 gọi `banner.php` (render `<h1>Ngành [Title]</h1>`), ngay bên dưới dòng 36 render tiếp `<h1>Ngành <?php the_title(); ?></h1>`. | **High** |
| `page-compare-program.php:28` & `33` | Heading H1 | **Trùng lặp 2 thẻ `<h1>`.** Dòng 28 gọi `banner.php` (render `<h1>`), dòng 33 lại render `<h1 class="text-2xl md:text-3xl...">So sánh [A] vs [B]</h1>`. | **High** |
| `taxonomy.php:20` & `26` | Heading H1 | **Trùng lặp 2 thẻ `<h1>`.** Dòng 20 gọi `banner.php` (render `<h1>`), dòng 26 lại render `<h1>Hệ đào tạo</h1>`. | **High** |
| `front-page.php:192, 228, 253, 286` | Heading Hierarchy | **Nhảy cóc cấp độ thẻ tiêu đề (Heading Skip):** Từ `<h2>5 ngành đào tạo hot nhất</h2>` nhảy thẳng xuống `<h4><?php echo esc_html($maj->post_title); ?></h4>` (bỏ qua `<h3>`). Từ `<h2>Trường đối tác đào tạo</h2>` nhảy thẳng xuống `<h4>`. | **Medium** |
| `single-program.php:95, 115, 127` | Heading Hierarchy | **Cấu trúc heading lộn xộn:** Nội dung bắt đầu bằng `<h4>Đã hết chỉ tiêu</h4>` và `<h4>[Tên trường]</h4>`, trước khi đến `<h2>Tổng quan chương trình</h2>` ở dòng 127. Không có `<h1>` trong nội dung bài viết (chỉ có ở banner ngoài). | **Medium** |
| `page-contact.php:20-31` | Semantic HTML | **Thẻ `<h1>` bị đặt bên ngoài thẻ `<main>`:** Khối banner chứa `<h1>LIÊN HỆ VỚI CHÚNG TÔI</h1>` nằm ở dòng 20-29. Thẻ `<main id="primary">` chỉ mở ở dòng 31. | **Medium** |
| `front-page.php:896` | Asset / SEO Link | **Hardcode URL Localhost trong production theme:** `style="background-image: url('http://localhost:10028/wp-content/uploads/2026/07/banner-contact.png');"`. Sẽ lỗi 404/Connection Refused khi deploy thực tế. | **High** |
| `index.php:334` | Image Alt | **Thẻ `<img>` thiếu alt:** `<img src="..." alt="" class="w-full h-full object-cover">` trong widget bài viết gần đây. | **Medium** |
| `front-page.php:65, 514, 538` | Image Alt | **Thẻ `<img>` có thuộc tính `alt` vô nghĩa / placeholder tiếng Anh:** `alt="Banner Hero"`, `alt="Students Graduation Slide"`, `alt="VTV24 Video Thumbnail"`. | **Low** |
| `assets/js/compare.js:247` | Image Alt | **Thẻ `<img>` sinh từ JS có `alt=""` rỗng.** | **Low** |
| `assets/js/eligibility.js:453` | Image Alt | **Thẻ `<img>` logo trường sinh từ JS có `alt=""` rỗng:** `<img src="..." alt="" class="elig-card-school-logo">`. | **Low** |
| `front-page.php:278`, `archive-school.php:103`, `archive-program.php:426` | Image Alt | Gọi `wp_get_attachment_image()` mà không truyền tham số `'alt'` dự phòng. Nếu media trong thư viện không điền alt, ảnh sẽ xuất ra `alt=""`. | **Low** |
| `single-program.php`, `single-school.php`, `single-major.php` | Semantic HTML | Toàn bộ 3 template CPT chính **hoàn toàn không sử dụng thẻ `<article>`** cho nội dung chính, chỉ dùng thẻ `<div>`. | **Low** |
| `archive-program.php`, `taxonomy-training_type.php`, `single-program.php` | Semantic HTML | Toàn bộ sidebar bộ lọc và sidebar tư vấn chỉ bọc bằng `<div class="lg:col-span-1">` hoặc `<details>`, **hoàn toàn không dùng thẻ ngữ nghĩa `<aside>`.** | **Low** |
| `page-eligible.php:12` | Semantic HTML | Thẻ `<main>` thiếu thuộc tính định danh chuẩn WordPress `id="primary" class="site-main"`. | **Low** |
| `inc/core/class-helpers.php:341` | Internal Link | Breadcrumb trỏ về `/truong-hoc/` trong khi slug CPT archive đăng ký trong ACF JSON (`acf-import-cpts.json:35`) là `/truong-doi-tac/`. Dẫn tới liên kết hỏng (Broken Link 404). | **High** |
| `inc/config/class-defaults.php:38` vs `class-helpers.php:347` | Internal Link | Fallback primary menu trỏ về `/tin-tuyen-sinh/`, trong khi breadcrumb và rewrite của blog trỏ về `/tin-tuc/`. | **Medium** |
| `inc/seo/class-rankmath-integration.php:314-338` | OpenGraph & Meta | Fallback trong `wp_head` (khi không có Rank Math) **chỉ in `og:image` và `twitter:image`**, hoàn toàn bỏ quên: `og:title`, `og:description`, `og:url`, `og:type`, `og:site_name`, `twitter:card`, `twitter:title`, `twitter:description`. | **Medium** |

---

### 1.3. Schema.org Structured Data Audit

#### Bảng Kiểm Tra Tuân Thủ Chuẩn Schema.org
| Thực Thể Schema | Tệp Nguồn & Dòng Code | Hiện Trạng Thực Tế | Thiếu Sót & Lỗi Chuẩn Google Search Central | Mức Độ |
|---|---|---|---|---|
| **Cơ Chế Khởi Tạo Schema Tổng Thể** | `inc/seo/class-rankmath-integration.php:67-124, 256-286` | Toàn bộ Schema được gắn thông qua filter hook `rank_math/json_ld`. | **Nếu website không cài đặt hoặc tắt plugin Rank Math SEO, TOÀN BỘ website KHÔNG CÓ BẤT KỲ DÒNG JSON-LD HAY MICRODATA NÀO.** Không có native fallback JSON-LD. | **Critical** |
| **`EducationalOrganization`** | Toàn bộ mã nguồn theme | Không tìm thấy bất kỳ khai báo nào. | **Thiếu hoàn toàn Schema tổ chức giáo dục** cho thương hiệu cổng thông tin `lienthongdaihoc.com` (Name, Logo, URL, SameAs, ContactPoint, Address). | **High** |
| **`WebSite` & `SearchAction`** | Toàn bộ mã nguồn theme | Không tìm thấy bất kỳ khai báo nào. | **Thiếu Schema WebSite và Sitelinks Searchbox** cho trang chủ, làm mất cơ hội hiển thị hộp tìm kiếm trực tiếp trên kết quả tìm kiếm Google. | **Medium** |
| **`Course` Schema** *(Chương trình đào tạo)* | `inc/seo/class-rankmath-integration.php:73-83` | Khai báo Course cơ bản: `name`, `description`, `provider`. | **Thiếu nghiêm trọng các trường bắt buộc/khuyến nghị của Google Course Rich Results:**<br>1. Thiếu `hasCourseInstance` (chứa `courseMode`: online/blended, `courseWorkload`, `duration`).<br>2. Thiếu `offers` (`price`, `priceCurrency`: "VND", `availability`).<br>3. Thiếu `educationalCredentialAwarded` (Cử nhân, Kỹ sư).<br>4. Thiếu `courseCode` / `identifier` (mã ngành).<br>*(Lưu ý: Dữ liệu này có sẵn trong ACF và postmeta nhưng không được map vào Schema).* | **High** |
| **`Course` Schema** *(Trang so sánh)* | `inc/seo/class-rankmath-integration.php:271-282` | Render mảng các Course được so sánh. | Cung cấp Course sơ sài, thiếu `offers`, `courseMode` và `hasCourseInstance`. | **Medium** |
| **`CollegeOrUniversity` Schema** *(Trường đối tác)* | `inc/seo/class-rankmath-integration.php:108-120` | Khai báo `CollegeOrUniversity` gồm `name`, `url`, `address`, `telephone`. | 1. **Thiếu `logo` và `image`** (không lấy `thumbnail`).<br>2. **Thiếu `description`**.<br>3. **Lỗi sinh chuỗi rỗng:** Nếu ACF `address` hoặc `hotline` trống, Schema sẽ xuất `"streetAddress": ""` và `"telephone": ""` vi phạm cú pháp dữ liệu cấu trúc. | **Medium** |
| **`FAQPage` Schema** | `inc/seo/class-rankmath-integration.php:85-103` | Chỉ inject trên `is_singular('program')` nếu trường ACF `faq` có dữ liệu. | 1. **Hoàn toàn KHÔNG CÓ trên trang câu hỏi thường gặp chuyên biệt `page-faq.php`** (nơi có sẵn 5 câu hỏi mặc định và ACF `faq_items`).<br>2. Không có trên `page-compare-program.php` (nơi có sẵn mục FAQ so sánh).<br>3. Chưa làm sạch HTML (`wp_strip_all_tags`) trước khi đưa text vào `acceptedAnswer.text`. | **High** |
| **`BreadcrumbList` Schema** | `inc/core/class-helpers.php:307-400` | Hàm `ltdh_breadcrumb()` dựng HTML thuần `<div><a>..</a> / <span>..</span></div>`. | 1. **Không có bất kỳ thuộc tính Microdata (`itemscope`, `itemtype="https://schema.org/BreadcrumbList"`) nào.**<br>2. Dòng 324: Biến `$is_he_dao_tao` **chủ động bỏ qua `rank_math_the_breadcrumbs()`** trên toàn bộ đường dẫn `/he-dao-tao/`. Do đó, toàn bộ danh mục chương trình học (phần quan trọng nhất của site) **hoàn toàn mất BreadcrumbList Schema** trên Google SERP. | **High** |

---

### 1.4. Tính Toàn Vẹn Frontend & JavaScript Phía Client

#### 1. Lỗi Cú Pháp Media Query Gây Vỡ Layout Mobile Trong `footer.php:184` (Critical Bug)
- **Vị trí:** `footer.php:183-186`
- **Mã nguồn quan sát:**
  ```html
  <style>
  @media (max-w: 767px) {
    body {
      padding-bottom: 72px !important;
    }
  ```
- **Phân tích:** 
  - Cú pháp CSS hợp lệ bắt buộc là `@media (max-width: 767px)`. Thuộc tính `max-w:` là class của Tailwind, **không phải là cú pháp CSS Media Feature hợp lệ**.
  - Trình duyệt sẽ loại bỏ toàn bộ khối `@media (max-w: 767px)` này vì lỗi cú pháp phân tích (CSS Parse Error).
  - **Hệ quả trực tiếp:** Quy tắc `body { padding-bottom: 72px !important; }` không được áp dụng trên thiết bị di động. Thanh điều hướng cố định dưới đáy màn hình (Mobile Bottom CTA Bar `fixed bottom-0 z-50 md:hidden`) sẽ **đè bẹp và che khuất hoàn toàn nội dung chân trang, các nút bấm và thông tin bản quyền** trên màn hình điện thoại.

#### 2. Lỗi Liệt Nút "So Sánh" Sau Khi Lọc AJAX Trong `assets/js/main.js` & `compare.js` (High Bug)
- **Vị trí:** `assets/js/compare.js:132-144` & `assets/js/main.js:73-77`
- **Mã nguồn quan sát:**
  - Trong `compare.js:132-134`:
    ```javascript
    function initCompareButtons() {
        var buttons = document.querySelectorAll('.ltdh-compare-toggle, .ltdh-compare-single-btn');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function (e) { ... });
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        initCompareButtons();
    });
    ```
  - Trong `main.js:73-76`:
    ```javascript
    .then(res => {
        if (res.success) {
            container.innerHTML = res.data.html; // Ghi đè toàn bộ DOM card
        }
    })
    ```
- **Phân tích:** 
  - `compare.js` gắn trực tiếp sự kiện `click` lên các nút có sẵn tại thời điểm `DOMContentLoaded`.
  - Khi người dùng chọn trường/ngành để lọc chương trình, `main.js` nhận HTML mới qua AJAX và gán `container.innerHTML = res.data.html`.
  - Toàn bộ các thẻ `<button class="ltdh-compare-toggle">` mới sinh ra **hoàn toàn không được gán event listener nào**.
  - `compare.js` không sử dụng kỹ thuật ủy quyền sự kiện (Event Delegation) trên `document`, và `main.js` cũng không gọi hàm khởi tạo lại.
  - **Hệ quả:** Người dùng bấm nút "So sánh" trên bất kỳ card chương trình nào sau khi lọc đều **hoàn toàn không có phản hồi gì (chết nút)**.

#### 3. Rủi Ro Uncaught TypeError Gây Dừng Thực Thi Script Trong `assets/js/eligibility.js` (High Bug)
- **Vị trí:** `assets/js/eligibility.js:74-85` & `300-305`
- **Mã nguồn quan sát:**
  ```javascript
  // Dòng 74-80:
  containers.forEach(function (container) {
      var input = container.querySelector('.elig-search-input');
      // Không kiểm tra input !== null
      input.addEventListener('focus', function () { ... });
  });

  // Dòng 300-305:
  data.append('education', document.querySelector('select[name="education"]').value);
  data.append('training_type', document.querySelector('select[name="training_type"]').value);
  data.append('campus', document.querySelector('select[name="campus"]').value);
  ```
- **Phân tích:** 
  - Truy cập trực tiếp thuộc tính `.value` hoặc gọi `.addEventListener()` trên kết quả `querySelector` mà không kiểm tra phần tử có tồn tại (`null check`).
  - Nếu bất kỳ trường chọn nào bị ẩn, tải chậm hoặc bị chỉnh sửa trong template con, script sẽ quăng lỗi: `Uncaught TypeError: Cannot read properties of null (reading 'value')`. Toàn bộ quá trình kiểm tra điều kiện bị gián đoạn và nút submit bị treo ở trạng thái loading.

#### 4. Tràn Bộ Nhớ & Trùng Lặp Event Listener (Memory Leak & Duplicate Handlers) Trong `eligibility.js`
- **Vị trí:** `assets/js/eligibility.js:88-92` & `414-415`
- **Mã nguồn quan sát:**
  - Dòng 88-92: Mỗi container autocomplete đều đăng ký một listener `document.addEventListener('click', ...)` riêng biệt. Với 5 trường chọn, có 5 listener click toàn cục chạy đồng thời mỗi khi người dùng click chuột.
  - Dòng 414-415: Hàm `initLeadForm()` và `initCardVerifyListeners()` được gọi bên trong `renderResults()`. Mỗi khi người dùng bấm "Kiểm tra lại" và submit lần 2, một sự kiện `submit` mới lại được add vào form cũ. Kết quả: một lần bấm gửi form tư vấn sẽ kích hoạt 2 đến 3 request AJAX liên tiếp, tạo Lead trùng lặp trong database.

#### 5. Sử Dụng Hộp Thoại Trình Duyệt `alert()` Chặn Luồng (Blocking UI)
- **Vị trí:** `assets/js/eligibility.js:324, 329, 548, 566, 594`
- Sử dụng hàm `alert(...)` gốc của trình duyệt để thông báo lỗi thay vì toast thông báo đồng bộ với theme (`compare.js` có sẵn `showToast()`). `alert()` làm dừng luồng giao diện, gây khó chịu trên thiết bị di động.

#### 6. Tệp Fallback Image Bị Lỗi 404 Lưu Thành File JPG (`banner-default.jpg`)
- **Vị trí:** `assets/images/banner-default.jpg`
- **Nội dung thực tế (Dung lượng 29 bytes):**
  ```html
  <html><body>404</body></html>
  ```
- **Phân tích:** Đây là nội dung trang lỗi 404 HTML được lưu dưới đuôi mở rộng `.jpg`. Khi code gọi fallback tại `inc/comparison.php:578`, trình duyệt sẽ cố gắng hiển thị file HTML này dưới dạng ảnh, gây vỡ hình ảnh (broken image icon).

#### 7. Dư Thừa 28MB Ảnh Chụp Thiết Kế (Mockup Screenshots) Trong Theme Assets
- **Thư mục:** `assets/images/`
- Chứa 8 tệp ảnh chụp màn hình kiểm thử giao diện kích thước cực lớn không hề được code gọi tới:
  - `screenshot_layout_rows_full.png` (8.0 MB)
  - `screenshot_layout_error.png` (3.2 MB)
  - `screenshot_exemption_design_v2.png` (3.2 MB)
  - `screenshot_exemption_design_v3.png` (3.2 MB)
  - `screenshot_exemption_design_v4.png` (3.2 MB)
  - `screenshot_exemption_design.png` (2.5 MB)
  - `screenshot_layout_fixed.png` (2.5 MB)
  - `screenshot_layout_rows.png` (2.5 MB)
- **Tổng dung lượng lãng phí:** ~28.5 MB, làm phình to gói cài đặt theme và thời gian backup/deploy.

---

## 2. Logic Chain (Chuỗi Lập Luận Từ Quan Sát Đến Kết Luận)

```
[QUAN SÁT 1: footer.php:184 dùng @media (max-w: 767px)]
  └──> Cú pháp CSS không hợp lệ theo chuẩn W3C
  └──> Trình duyệt bỏ qua toàn bộ khối CSS
  └──> body không có padding-bottom: 72px trên mobile
  └──> KẾT LUẬN: Thanh Mobile Bottom Bar đè che nội dung chân trang, nút bấm và bản quyền trên mobile.

[QUAN SÁT 2: compare.js bind click 1 lần trên DOMContentLoaded; main.js thay DOM bằng AJAX]
  └──> Sau khi AJAX load lại danh sách chương trình, DOM mới không có event listener
  └──> compare.js không có Event Delegation trên document
  └──> KẾT LUẬN: Người dùng không thể thêm vào so sánh sau khi dùng bộ lọc trường/ngành (tính năng cốt lõi bị hỏng).

[QUAN SÁT 3: front-page.php không có thẻ <h1> nào]
  └──> Bot tìm kiếm của Google quét trang chủ không xác định được chủ đề cấp cao nhất (Primary Keyword Focus)
  └──> Điểm đánh giá On-page SEO tụt giảm nghiêm trọng
  └──> KẾT LUẬN: Trang chủ mất lợi thế xếp hạng từ khóa tìm kiếm chính "Liên thông đại học".

[QUAN SÁT 4: single-major, page-compare-program, taxonomy.php đều render 2 thẻ <h1>]
  └──> banner.php tự động sinh 1 thẻ <h1>, template bên dưới lại khai báo thêm 1 thẻ <h1>
  └──> Cấu trúc trang vi phạm chuẩn One-H1-Per-Page của Google On-page SEO
  └──> KẾT LUẬN: Gây loãng trọng số từ khóa và xung đột ngữ nghĩa thẻ tiêu đề.

[QUAN SÁT 5: Toàn bộ Schema JSON-LD chỉ nằm trong filter hook của Rank Math]
  └──> Nếu web chạy mà chưa kích hoạt plugin Rank Math, site có 0 byte dữ liệu cấu trúc
  └──> Kể cả khi có Rank Math, Course Schema thiếu hasCourseInstance/offers, thiếu EducationalOrganization, page-faq thiếu FAQPage
  └──> KẾT LUẬN: Không đủ điều kiện kích hoạt các Rich Snippets của Google (Course Carousel, Star Ratings, FAQ Dropdowns, Sitelinks).

[QUAN SÁT 6: style.css và input.css chứa trùng lặp 250+ dòng code; style.css load trước main.min.css]
  └──> Tải trùng lặp CSS làm tăng thời gian FCP (First Contentful Paint)
  └──> Phải dùng !important tràn lan để đè Tailwind
  └──> KẾT LUẬN: Vi phạm nguyên tắc DRY, gây khó khăn cho bảo trì giao diện và tối ưu Core Web Vitals.
```

---

## 3. Caveats (Các Điểm Giới Hạn & Giả Định)

1. **Môi trường hoạt động của Plugin:** Đánh giá này dựa trên phân tích tĩnh toàn bộ mã nguồn theme hiện tại. Nếu website production có kích hoạt Rank Math SEO bản Pro và cấu trừ schema thông qua giao diện admin Rank Math (Schema Builder), một số thuộc tính Schema có thể được bổ sung thông qua database của Rank Math thay vì code theme. Tuy nhiên, việc code theme tự chèn Schema thiếu sót và phụ thuộc hoàn toàn vào plugin là một lỗ hổng kiến trúc cần khắc phục.
2. **ACF Data Content:** Một số lỗi Schema (như Course thiếu offers hay Credential) là do code mapper tại `class-rankmath-integration.php` chưa nối các trường ACF tương ứng vào mảng JSON-LD.
3. **Phạm vi kiểm thử:** Script syntax đã được kiểm tra tính hợp lệ bằng Node engine. Không can thiệp sửa đổi trực tiếp vào source code dự án theo đúng chỉ thị bắt buộc.

---

## 4. Conclusion (Kết Luận Đánh Giá & Kế Hoạch Khắc Phục Cụ Thể)

### Bảng Điểm Sức Khỏe Frontend & SEO (Health Scorecard)
| Hạng Mục Đánh Giá | Điểm Hiện Tại (Thang 100) | Đánh Giá Chung |
|---|---|---|
| **Asset Management (CSS/JS)** | **62 / 100** | Nhiều script/style inline, trùng lặp 250 dòng CSS, không có async/defer, lệch versioning. |
| **SEO On-Page & Semantic HTML** | **55 / 100** | Trang chủ mất H1, 3 template bị 2 H1, heading nhảy cóc, thiếu alt ảnh, link hỏng 404 trong breadcrumb. |
| **Schema.org Structured Data** | **48 / 100** | Phụ thuộc 100% vào plugin ngoài, thiếu Schema Tổ chức giáo dục, Course Schema không đủ chuẩn Rich Snippet. |
| **Frontend & Client JS Integrity** | **58 / 100** | Lỗi media query làm vỡ mobile footer, liệt nút so sánh sau AJAX, nguy cơ crash script do null pointer. |

---

### Danh Sách Giải Pháp & Đoạn Code Khắc Phục Cụ Thể (Ready-to-Apply Fix Snippets)

#### Khắc phục 1: Sửa lỗi Media Query trong `footer.php` (Mức độ: Critical)
**File cần sửa:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/footer.php`  
**Dòng:** 184  
**Thay thế:**
```php
// TRƯỚC (Lỗi):
<style>
@media (max-w: 767px) {
  body {
    padding-bottom: 72px !important;
  }

// SAU (Đã khắc phục):
<style>
@media (max-width: 767px) {
  body {
    padding-bottom: 72px !important;
  }
```

---

#### Khắc phục 2: Bổ sung Event Delegation cho nút So sánh trong `assets/js/compare.js` (Mức độ: High)
**File cần sửa:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/assets/js/compare.js`  
**Dòng:** 132-200  
**Giải pháp:** Thay vì gắn `addEventListener` cố định từng nút, sử dụng Event Delegation trên `document` để tự động nhận diện cả các nút được render qua AJAX:
```javascript
// Thay thế đoạn initCompareButtons() bằng Event Delegation:
function initCompareDelegation() {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.ltdh-compare-toggle, .ltdh-compare-single-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var type = btn.getAttribute('data-compare-type');
        var id   = parseInt(btn.getAttribute('data-compare-id'), 10);
        if (!type || !id) return;

        if (hasItem(type, id)) {
            removeItem(type, id);
            btn.classList.remove('is-compared');
            btn.textContent = btn.classList.contains('ltdh-compare-single-btn') ? '📊 Thêm vào so sánh' : 'So sánh';
        } else {
            var items = getItems();
            var total = Object.values(items).reduce(function (s, a) { return s + a.length; }, 0);
            if (total >= MAX_ITEMS) {
                showToast('Chỉ so sánh tối đa ' + MAX_ITEMS + ' mục.', 'warning');
                return;
            }

            var btnHe = btn.getAttribute('data-compare-he') || '';
            var btnNganh = btn.getAttribute('data-compare-nganh') || '';
            var activeIds = items[type] || [];

            if (activeIds.length > 0 && btnNganh) {
                var meta = getMetadata();
                for (var idx = 0; idx < activeIds.length; idx++) {
                    var existingId = activeIds[idx];
                    var existingMeta = meta[existingId];
                    if (existingMeta && existingMeta.nganh && existingMeta.nganh !== btnNganh) {
                        showToast('Chỉ được so sánh các chương trình CÙNG NGÀNH ĐÀO TẠO.', 'error');
                        return;
                    }
                }
            }

            var btnTitle = btn.getAttribute('data-compare-title') || '';
            var btnThumb = btn.getAttribute('data-compare-thumb') || '';
            if (!btnThumb) {
                var cardEl = document.querySelector('[data-compare-id="' + id + '"][data-compare-thumb]');
                if (cardEl) btnThumb = cardEl.getAttribute('data-compare-thumb') || '';
            }

            addItem(type, id, btnHe, btnNganh, btnTitle, btnThumb);
            btn.classList.add('is-compared');
            btn.textContent = '✓ Đã thêm';
            showToast('Đã thêm vào danh sách so sánh (' + (total + 1) + '/' + MAX_ITEMS + ')', 'success');
        }
        updateTray();
    });
}
```

---

#### Khắc phục 3: Bổ sung H1 chuẩn ngữ nghĩa trên `front-page.php` (Mức độ: Critical)
**File cần sửa:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/front-page.php`  
**Vị trí:** Ngay đầu `<main id="primary">` (Dòng 31)  
**Giải pháp:** Thêm thẻ `<h1>` chứa từ khóa thương hiệu chính nhưng dùng class `sr-only` (accessible cho Search Engine và Screen Reader) hoặc hiển thị trực quan trong Hero:
```html
<main id="primary" class="site-main bg-white">
    <!-- Primary H1 for SEO -->
    <h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học, Văn Bằng 2 & Đại Học Từ Xa</h1>
```

---

#### Khắc phục 4: Xóa bỏ H1 trùng lặp trong `single-major.php`, `page-compare-program.php`, `taxonomy.php` (Mức độ: High)
1. **Trong `single-major.php`:**
   - Dòng 26 đã có `banner.php` chứa `<h1>`.
   - Tại dòng 36-38: Đổi thẻ `<h1>` thành thẻ `<h2>`:
   ```html
   <!-- Đổi thành h2 -->
   <h2 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight">
       Ngành <?php the_title(); ?>
   </h2>
   ```
2. **Trong `page-compare-program.php`:**
   - Dòng 28 đã gọi `banner.php`.
   - Tại dòng 33: Đổi thẻ `<h1>` thành `<h2>`:
   ```html
   <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">
       <?php echo esc_html( $seo_title ); ?>
   </h2>
   ```
3. **Trong `taxonomy.php`:**
   - Dòng 20 đã gọi `banner.php`.
   - Tại dòng 26: Đổi thẻ `<h1>` thành `<h2>`:
   ```html
   <h2 class="text-2xl md:text-4xl font-black text-slate-900">Hệ đào tạo</h2>
   ```

---

#### Khắc phục 5: Khắc phục URL Localhost trong `front-page.php:896` (Mức độ: High)
**File cần sửa:** `front-page.php` dòng 896  
```html
<!-- TRƯỚC: -->
<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('http://localhost:10028/wp-content/uploads/2026/07/banner-contact.png');"></div>

<!-- SAU: -->
<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url( home_url( '/wp-content/uploads/2026/07/banner-contact.png' ) ); ?>');"></div>
```

---

#### Khắc phục 6: Khắc phục liên kết hỏng `/truong-hoc/` trong `class-helpers.php:341` (Mức độ: High)
**File cần sửa:** `inc/core/class-helpers.php` dòng 341  
```php
// TRƯỚC:
$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => home_url( '/truong-hoc/' ) ];

// SAU:
$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => get_post_type_archive_link( LTDH_CPT_SCHOOL ) ?: home_url( '/truong-doi-tac/' ) ];
```

---

#### Khắc phục 7: Chuẩn hóa Schema.org Cho Chương Trình Giáo Dục & Native Fallback (Mức độ: High)
**File cần sửa:** `inc/seo/class-rankmath-integration.php`  
**Giải pháp:** Bổ sung `EducationalOrganization`, `hasCourseInstance`, `offers` cho Course, và bổ sung action `wp_head` để output JSON-LD khi không có Rank Math:
```php
// Bổ sung đầy đủ thuộc tính chuẩn cho Course Schema:
function ltdh_build_course_schema( $program_id ) {
    $school_id = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
    $school_title = $school_id ? get_the_title( $school_id ) : 'Đối tác liên kết';
    $school_url   = $school_id ? ( get_field( 'website', $school_id ) ?: get_permalink( $school_id ) ) : home_url( '/' );
    
    $tuition_raw = get_field( LTDH_META_TUITION, $program_id );
    $duration    = get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm';
    $mode_term   = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
    $mode_name   = ! empty( $mode_term ) && ! is_wp_error( $mode_term ) ? $mode_term[0]->name : 'Đại học từ xa';

    return [
        '@context'    => 'https://schema.org',
        '@type'       => 'Course',
        'name'        => get_the_title( $program_id ),
        'description' => get_the_excerpt( $program_id ) ?: 'Chương trình đào tạo liên thông và đại học từ xa.',
        'provider'    => [
            '@type' => 'CollegeOrUniversity',
            'name'  => $school_title,
            'url'   => esc_url( $school_url ),
        ],
        'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư Đại học',
        'hasCourseInstance' => [
            '@type'          => 'CourseInstance',
            'courseMode'     => $mode_name,
            'courseWorkload' => $duration,
            'instructor'     => [
                '@type' => 'CollegeOrUniversity',
                'name'  => $school_title,
            ],
        ],
        'offers' => [
            '@type'         => 'Offer',
            'category'      => 'Tuition',
            'priceCurrency' => 'VND',
            'price'         => is_numeric( $tuition_raw ) ? $tuition_raw : '0',
            'availability'  => 'https://schema.org/InStock',
            'url'           => get_permalink( $program_id ),
        ],
    ];
}
```

---

#### Khắc phục 8: Tối ưu Enqueue và Thêm Hỗ trợ `defer` trong `class-theme-setup.php` (Mức độ: Medium)
**File cần sửa:** `inc/core/class-theme-setup.php:44-84`  
1. Chuyển Google Fonts từ `header.php` vào `wp_enqueue_style()`.
2. Đặt `ltdh-theme-styles` làm dependency cho `ltdh-main-style` để đảm bảo thứ tự tầng CSS.
3. Kích hoạt thuộc tính `strategy => defer` cho toàn bộ script phía client:
```php
function ltdh_enqueue_assets() {
    // 1. Google Fonts enqueued properly
    wp_enqueue_style(
        'ltdh-google-fonts',
        'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;850;900&family=Montserrat:wght@600;700;800;900&display=swap',
        [],
        null
    );

    // 2. Base Tailwind CSS First
    $theme_css_path = get_template_directory() . '/assets/css/main.min.css';
    $theme_version  = file_exists( $theme_css_path ) ? filemtime( $theme_css_path ) : LTDH_VERSION;
    wp_enqueue_style( 'ltdh-theme-styles', get_template_directory_uri() . '/assets/css/main.min.css', [], $theme_version );

    // 3. Custom theme style overrides loaded AFTER Tailwind
    $main_css_path = get_template_directory() . '/style.css';
    $main_version  = file_exists( $main_css_path ) ? filemtime( $main_css_path ) : LTDH_VERSION;
    wp_enqueue_style( 'ltdh-main-style', get_template_directory_uri() . '/style.css', [ 'ltdh-theme-styles' ], $main_version );

    // 4. Scripts with defer strategy
    $main_js_path = get_template_directory() . '/assets/js/main.js';
    $js_ver       = file_exists( $main_js_path ) ? filemtime( $main_js_path ) : LTDH_VERSION;
    
    wp_enqueue_script( 'ltdh-main-js', get_template_directory_uri() . '/assets/js/main.js', [], $js_ver, [
        'in_footer' => true,
        'strategy'  => 'defer',
    ] );

    wp_localize_script( 'ltdh-main-js', 'ltdh_ajax', [
        'ajax_url'      => admin_url( 'admin-ajax.php' ),
        'compare_nonce' => wp_create_nonce( LTDH_NONCE_COMPARE ),
        'home_url'      => esc_url( home_url( '/' ) ),
    ] );

    if ( ltdh_compare_should_load() ) {
        $compare_js_path = get_template_directory() . '/assets/js/compare.js';
        $compare_ver     = file_exists( $compare_js_path ) ? filemtime( $compare_js_path ) : LTDH_VERSION;
        wp_enqueue_script( 'ltdh-compare-js', get_template_directory_uri() . '/assets/js/compare.js', [ 'ltdh-main-js' ], $compare_ver, [
            'in_footer' => true,
            'strategy'  => 'defer',
        ] );
    }
}
```

---

## 5. Verification Method (Phương Pháp Xác Minh Độc Lập)

Người kiểm định tiếp theo hoặc lập trình viên phụ trách sửa lỗi có thể độc lập xác minh từng điểm phát hiện theo các bước sau:

1. **Xác minh lỗi cú pháp media query `@media (max-w: 767px)`:**
   - Mở file `footer.php` tại dòng 184.
   - Chạy lệnh kiểm tra chuỗi:
     ```bash
     grep -n "max-w: 767px" "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/footer.php"
     ```
   - Quan sát kết quả: dòng 184 chứa chuỗi `@media (max-w: 767px)`. Mở trình duyệt Chrome DevTools trên responsive view (iPhone 14 / màn hình 390px), kiểm tra computed style của thẻ `<body>`, kiểm tra xem thuộc tính `padding-bottom: 72px` có bị gạch bỏ / không áp dụng hay không.

2. **Xác minh lỗi thiếu thẻ `<h1>` trên trang chủ:**
   - Chạy lệnh tìm kiếm thẻ `h1` trên `front-page.php`:
     ```bash
     grep -n "<h1" "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/front-page.php"
     ```
   - Quan sát kết quả: Trả về 0 kết quả (exit code 1).

3. **Xác minh lỗi 2 thẻ `<h1>` trên `single-major.php`:**
   - Mở `single-major.php`:
     - Dòng 26 gọi `get_template_part( 'template-parts/banner' );`. Mở `template-parts/banner.php:141` thấy thẻ `<h1>` chứa `$banner_title`.
     - Dòng 36 của `single-major.php` chứa tiếp thẻ `<h1 class="text-2xl md:text-4xl...">`.

4. **Xác minh lỗi nút So sánh sau khi lọc AJAX:**
   - Mở trang `/chuong-trinh/` trên trình duyệt.
   - Chọn một trường trong dropdown bộ lọc để kích hoạt hàm `triggerFilter()` trong `main.js`.
   - Sau khi kết quả AJAX được cập nhật vào DOM, bấm vào nút "So sánh" trên bất kỳ card kết quả nào.
   - Quan sát: Không có toast thông báo nào hiện ra, số lượng trong tray so sánh không tăng, chứng minh event listener bị mất.

5. **Xác minh tính hợp lệ cú pháp JS:**
   - Chạy lệnh Node check:
     ```bash
     node -c assets/js/main.js assets/js/compare.js assets/js/eligibility.js
     ```
   - Lệnh trả về exit code 0 (cú pháp hợp lệ, các lỗi tìm được là lỗi logic DOM và runtime).

6. **Xác minh tệp ảnh giả dạng 404 (`banner-default.jpg`):**
   - Chạy lệnh:
     ```bash
     cat "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/assets/images/banner-default.jpg"
     ```
   - Quan sát: Output in ra nguyên văn chuỗi HTML `<html><body>404</body></html>`.

7. **Xác minh URL Localhost trong mã nguồn:**
   - Chạy lệnh:
     ```bash
     grep -n "localhost" "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/front-page.php"
     ```
   - Quan sát: Dòng 896 hiển thị URL `http://localhost:10028/...`.
