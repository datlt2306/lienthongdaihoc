# Báo Cáo Kiểm Định Khảo Sát & Đánh Giá Mã Nguồn: SEO, URLs, Canonicals, Breadcrumbs & 301 Redirects

**Dự án:** Theme WordPress Liên Thông Đại Học (`lienthongdaihoc.com`)  
**Nhiệm vụ:** Tái cấu trúc Kiến trúc Thông tin (Information Architecture), Thuật ngữ & Templates — Phạm vi chuyên trách: SEO & URLs  
**Người thực hiện:** Explorer Survey 3 (SEO & URLs Explorer)  
**Thời gian hoàn thành:** 2026-10-01T09:15:00Z  
**Đường dẫn lưu trữ:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3/handoff.md`

---

## 1. Observation (Các Quan Sát Thực Tế & Bằng Chứng Mã Nguồn)

### 1.1. Khảo Sát Cấu Trúc URL, Rewrite Rules, Slugs & Permalinks

Toàn bộ hệ thống định tuyến (routing) của theme được khai báo tại 3 nguồn chính:
1. `inc/post-types.php` kết hợp tệp JSON `inc/acf-import-cpts.json` (đăng ký CPTs & Taxonomies).
2. `inc/core/class-rewrite-rules.php` (bộ lọc Rewrite Rules, Permalinks, Request Guard & Redirects).
3. `inc/comparison.php` (tuyến đường ảo So sánh chương trình).

#### A. Khảo sát CPTs (Custom Post Types)

1. **CPT `program` (Chương trình / Cơ hội tuyển sinh liên thông):**
   - **Đăng ký:** `inc/acf-import-cpts.json:113-168`
     - `"has_archive": true`, `"has_archive_slug": "chuong-trinh"`
     - `"rewrite": true`, `"rewrite_slug": ""` (chuỗi rỗng)
   - **Single URL:** Flat URL trực tiếp từ root: `/{program-slug}/` (ví dụ: `https://lienthongdaihoc.com/dai-hoc-kinh-te-quoc-dan-ke-toan-tu-xa/`).
     - Bằng chứng hook `post_type_link` tại `inc/core/class-rewrite-rules.php:179-190`:
       ```php
       function ltdh_program_permalink( $url, $post ) {
           if ( $post instanceof WP_Post ) {
               if ( in_array( $post->post_type, [ 'program', 'school' ], true ) ) {
                   return home_url( '/' . $post->post_name . '/' );
               }
               if ( 'major' === $post->post_type ) {
                   return home_url( '/nganh-' . $post->post_name . '/' );
               }
           }
           return $url;
       }
       add_filter( 'post_type_link', 'ltdh_program_permalink', 10, 2 );
       ```
   - **Xử lý Request:** Rule catch-all `([^/]+)/?$` tại `inc/core/class-rewrite-rules.php:43` bắt toàn bộ request 1-segment đưa vào `index.php?program=$matches[1]`. Bộ lọc `ltdh_program_request_guard()` (dòng 55-170) truy vấn `$wpdb->posts` để phân giải thành `program`, `school`, `major`, `post` hoặc `page`.
   - **Archive Base `/chuong-trinh/`:** Bị chặn đứng và cưỡng bức chuyển hướng 301 tại `inc/core/class-rewrite-rules.php:247-254`:
     ```php
     if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
         $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
         if ( ! empty( $_GET ) ) {
             $redirect_url = add_query_arg( $_GET, $redirect_url );
         }
         wp_redirect( $redirect_url, 301 );
         exit;
     }
     ```
   - **Xung đột Template:** Tại `archive-program.php:188`, form lọc vẫn gửi request tới action `home_url('/chuong-trinh/')`:
     ```html
     <form id="catalog-filter-form" action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET" class="space-y-4">
     ```
     Điều này khiến mọi thao tác submit bộ lọc trên `archive-program.php` bị nảy qua một bước **HTTP 301 Redirect** về `/he-dao-tao/tu-xa/`.

2. **CPT `school` (Trường đại học):**
   - **Đăng ký:** `inc/acf-import-cpts.json:2-57`
     - `"has_archive": true`, `"has_archive_slug": "truong-doi-tac"`
     - `"rewrite": true`, `"rewrite_slug": "truong-doi-tac"`
   - **Archive URL:** `/truong-doi-tac/` (phân trang: `/truong-doi-tac/page/X/`). Render qua template `archive-school.php`.
   - **Single URL:** Flat URL root: `/{school-slug}/` (ví dụ: `https://lienthongdaihoc.com/dai-hoc-kinh-te-quoc-dan/`).
   - **Tiền tố cũ:** `/truong-doi-tac/{school-slug}/` đã có lệnh 301 redirect về `/{school-slug}/` tại `inc/core/class-rewrite-rules.php:225-231`.

3. **CPT `major` (Ngành học):**
   - **Đăng ký:** `inc/acf-import-cpts.json:58-112`
     - `"has_archive": true`, `"has_archive_slug": "nganh-hoc"`
     - `"rewrite": true`, `"rewrite_slug": "nganh-hoc"`
   - **Archive URL:** `/nganh-hoc/` (phân trang: `/nganh-hoc/page/X/`, lọc nhóm: `?nhom_nganh={slug}`). Render qua template `archive-major.php`.
   - **Single URL:** Prefix URL: `/nganh-{major-slug}/` (ví dụ: `https://lienthongdaihoc.com/nganh-ke-toan/`).
     - Rewrite rule tại `inc/core/class-rewrite-rules.php:42`:
       ```php
       add_rewrite_rule( '^nganh-([^/]+)/?$', 'index.php?post_type=' . LTDH_CPT_MAJOR . '&name=$matches[1]', 'top' );
       ```
   - **Tiền tố cũ:** `/nganh-hoc/{major-slug}/` đã có lệnh 301 redirect về `/nganh-{major-slug}/` tại `inc/core/class-rewrite-rules.php:233-240`.

4. **CPT `guide` (Cẩm nang tuyển sinh):**
   - **Đăng ký:** `inc/post-types.php:111-128`
     - `'has_archive' => 'cam-nang'`
     - `'rewrite' => [ 'slug' => 'huong-dan', 'with_front' => false ]`
   - **Archive URL:** `/cam-nang/` (render qua template fallback `archive.php` hoặc `index.php`).
   - **Single URL:** `/huong-dan/{slug}/` (render qua `single-guide.php`).

#### B. Khảo sát Taxonomies

1. **Taxonomy `training_type` (Hình thức học / Phương thức đào tạo):**
   - **Đăng ký:** `inc/acf-import-cpts.json:170-208`
     - Rewrite slug: `he-dao-tao`
     - Phân cấp: `hierarchical => true`
   - **Các rewrite rule tùy biến (`inc/core/class-rewrite-rules.php:15-25`):**
     - Base archive: `/he-dao-tao/` -> `index.php?post_type=program`
     - Phân trang Base: `/he-dao-tao/page/([0-9]+)/` -> `index.php?post_type=program&paged=$1`
     - Term archive: `/he-dao-tao/([^/]+)/` -> `index.php?training_type=$1`
     - Phân trang Term: `/he-dao-tao/([^/]+)/page/([0-9]+)/` -> `index.php?training_type=$1&paged=$2`
   - **Template override:** `ltdh_template_include_he_dao_tao` (dòng 261-276) can thiệp hook `template_include`, ưu tiên trả về `taxonomy-training_type.php` cho toàn bộ các URL khớp regex `#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i`.
   - **Các Term đang tồn tại trong seed data (`inc/cli-commands.php:100-105`):**
     - `tu-xa`: `/he-dao-tao/tu-xa/` (Học từ xa / Online)
     - `vua-hoc-vua-lam`: `/he-dao-tao/vua-hoc-vua-lam/` (Vừa học vừa làm / Tại chức)
     - `chinh-quy`: `/he-dao-tao/chinh-quy/` (Liên thông chính quy)
     - `van-bang-2`: `/he-dao-tao/van-bang-2/` (🚨 **VI PHẠM NGHIỆP VỤ CỐT LÕI** - Văn bằng 2 không thuộc phạm vi Liên thông).

2. **Taxonomy `campus` (Cơ sở đào tạo):**
   - Rewrite slug: `co-so`.
   - Base URL `/co-so/` bị 301 redirect về `/he-dao-tao/tu-xa/` (`class-rewrite-rules.php:243-246`).
   - Term URLs: `/co-so/{campus-slug}/` (ví dụ: `/co-so/ha-noi/`, `/co-so/ho-chi-minh/`). Render qua `taxonomy.php`.

3. **Taxonomy `region` (Khu vực địa lý của trường):**
   - Rewrite slug: `khu-vuc`.
   - Term URLs: `/khu-vuc/{region-slug}/` (ví dụ: `/khu-vuc/mien-bac/`). Render qua `taxonomy.php`.

4. **Taxonomy `major_cat` (Nhóm ngành):**
   - Rewrite slug: `nhom-nganh`.
   - Term URLs: `/nhom-nganh/{cat-slug}/`. Render qua `taxonomy.php` hoặc lọc trên `archive-major.php?nhom_nganh={cat-slug}`.

#### C. Tuyến đường So Sánh (Virtual Rewrite)
- Khai báo tại `inc/comparison.php:22-29`:
  - Rule: `so-sanh/chuong-trinh/(.+?)/?$` -> `index.php?ltdh_compare=program&ltdh_compare_slug=$matches[1]`
  - URL mẫu: `/so-sanh/chuong-trinh/slug-a-vs-slug-b/`
  - Template: `page-compare-program.php`

#### D. Các Trang Tĩnh Cốt Lõi (Core Pages)
- `/gioi-thieu/` -> `page-about.php`
- `/lien-he/` -> `page-contact.php`
- `/dang-ky-tu-van/` (hoặc `/dang-ky/`) -> `page-register.php`
- `/cau-hoi-thuong-gap/` (hoặc `/faq/`, `/hoi-dap/`) -> `page-faq.php`
- `/kiem-tra-dieu-kien/` -> `page-eligible.php`
- `/tin-tuc/` (hoặc `/tin-tuyen-sinh/`) -> `index.php` (Trang bài viết blog)

---

### 1.2. Kiểm Toán Triển Khai Breadcrumbs & Schema BreadcrumbList

#### A. Triển khai Breadcrumb Trong Header Toàn Trang
Tại `header.php:98-101`:
```php
<?php if ( ! is_front_page() ) : ?>
    <?php ltdh_breadcrumb(); ?>
<?php endif; ?>
```
Theme gọi hàm `ltdh_breadcrumb()` trên mọi trang con (ngoại trừ trang chủ).

#### B. Khiếm Khuyết Nghiêm Trọng Trong Hàm `ltdh_breadcrumb()` (`inc/core/class-helpers.php:401-496`)
1. **Chủ động ngăn chặn Rank Math Breadcrumbs trên toàn bộ URL `/he-dao-tao/*` (Dòng 414-422):**
   ```php
   $request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
   $is_he_dao_tao = (bool) preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path );

   $html = '';
   if ( ! $is_he_dao_tao && function_exists( 'rank_math_the_breadcrumbs' ) ) {
       ob_start();
       rank_math_the_breadcrumbs();
       $html = ob_get_clean();
   }
   ```
2. **HTML Fallback Thô — Hoàn Toàn Không Có Microdata Schema (Dòng 480-495):**
   ```php
   $html_parts = [];
   foreach ( $crumbs as $crumb ) {
       if ( ! empty( $crumb['url'] ) ) {
           $html_parts[] = '<a href="' . esc_url( $crumb['url'] ) . '" class="hover:text-brand-primary transition-colors">' . esc_html( $crumb['label'] ) . '</a>';
       } else {
           $html_parts[] = '<span class="text-slate-500 font-medium">' . esc_html( $crumb['label'] ) . '</span>';
       }
   }
   $html = implode( ' <span class="mx-2 text-slate-300">/</span> ', $html_parts );
   ```
   *Bằng chứng:* Chuỗi HTML xuất ra chỉ gồm các thẻ `<a>` và `<span>` thông thường, hoàn toàn thiếu vắng các thuộc tính RDFa/Microdata: `itemscope`, `itemtype="https://schema.org/BreadcrumbList"`, `itemprop="itemListElement"`, `itemprop="item"`, `itemprop="name"`, `itemprop="position"`.
3. **Mất Hoàn Toàn BreadcrumbList JSON-LD Khi Rank Math Hoạt Động:**
   Tại `inc/seo/class-rankmath-integration.php:369-373`:
   ```php
   add_action( 'wp_head', 'ltdh_output_native_schema_fallback', 2 );
   function ltdh_output_native_schema_fallback() {
       if ( class_exists( 'RankMath' ) ) {
           return; // Rank Math đã kích hoạt thì fallback tự ngắt!
       }
       ...
   ```
   Do Rank Math đã được kích hoạt, fallback tự ngắt. Nhưng trên `/he-dao-tao/*`, Rank Math Breadcrumb lại bị `ltdh_breadcrumb()` chặn gọi. Kết quả: **Googlebot hoàn toàn không nhận được bất kỳ Breadcrumb Schema nào trên toàn bộ danh mục Hình thức đào tạo**.
4. **Nhãn Breadcrumb Lệch Chuẩn Nghiệp Vụ:**
   - Dòng 432: `'label' => 'Hệ đào tạo', 'url' => home_url( '/he-dao-tao/' )` (Cần đổi thành `Liên thông [Hình thức học]`).
   - Dòng 435: `'label' => 'Trường đối tác'` (Cần đổi thành `Trường đại học`).
   - Dòng 438: `'label' => 'Chuyên ngành'` (Cần đổi thành `Ngành học`).
5. **Trùng Lặp Breadcrumb (Double Breadcrumbs) & Dead Link Trên `single-guide.php:23-27`:**
   ```html
   <!-- Breadcrumbs -->
   <nav class="text-sm text-slate-500 mb-6">
       <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary">Trang chủ</a> / 
       <a href="<?php echo esc_url( home_url( '/huong-dan/' ) ); ?>" class="hover:text-brand-primary">Hướng dẫn tuyển sinh</a> / 
       <span><?php the_title(); ?></span>
   </nav>
   ```
   - *Lỗi 1:* `header.php` đã in một thanh breadcrumb ở trên, template lại in thêm thanh thứ hai ngay trong content.
   - *Lỗi 2:* Đường dẫn `home_url( '/huong-dan/' )` là **Dead Link (Lỗi 404)** vì CPT `guide` có archive slug là `/cam-nang/`, còn `/huong-dan/` chỉ là tiền tố single post.

---

### 1.3. Kiểm Toán SEO Titles, H1 Tags & Meta Descriptions Across Templates

#### A. Tiêu Chuẩn Tiêu Đề Mục Tiêu (Target Standard Formats)
Theo yêu cầu bắt buộc của dự án:
- `Liên thông [Hình thức học]` (ví dụ: *Liên thông từ xa*, *Liên thông vừa học vừa làm*, *Liên thông chính quy*)
- `Liên thông [Ngành]` (ví dụ: *Liên thông Ngành Kế toán*, *Liên thông Ngành Công nghệ thông tin*)
- `Liên thông [Trường]` (ví dụ: *Liên thông Đại học Kinh tế Quốc dân*, *Liên thông Đại học Thương mại*)

#### B. Hiện Trạng Từng Template Đối Chiếu Với Tiêu Chuẩn

1. **Trang Chủ (`front-page.php`):**
   - **Thẻ H1 hiện tại (Dòng 31):**
     ```html
     <h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học, Văn Bằng 2 & Đại Học Từ Xa</h1>
     ```
     *Lỗi vi phạm:* Nhồi nhét từ khóa *"Văn Bằng 2 & Đại Học Từ Xa"*, trực tiếp vi phạm nguyên tắc cốt lõi: *"TUYỆT ĐỐI KHÔNG đưa vào hoặc duy trì các loại hình tuyển sinh không liên quan như: Văn bằng 2, Đại học mới, Đại học chính quy như một sản phẩm độc lập"*.
   - **SEO Title:** Theo cấu hình Rank Math mặc định, thiếu định vị thương hiệu chuyên biệt về Liên thông.

2. **Trang Chi Tiết Chương Trình Tuyển Sinh (`single-program.php`):**
   - **SEO Title hiện tại (`inc/seo/class-rankmath-integration.php:28`):**
     ```php
     return sprintf( 'Học %s (%s) - %s | Tuyển sinh %d', get_the_title(), $training_type ?: 'Từ xa', $school, $year );
     ```
     *Lỗi vi phạm:* Dùng tiền tố chung chung *"Học [Tên]"*, hoàn toàn thiếu vắng từ khóa vàng *"Liên thông"*. Ví dụ kết quả sinh ra: `Học Kế toán (Từ xa) - Đại học Kinh tế Quốc dân | Tuyển sinh 2026`.
   - **Meta Description hiện tại (`class-rankmath-integration.php:51-58`):**
     ```php
     return sprintf(
         'Đăng ký tuyển sinh lớp %s (%s) tại trường %s. Thời gian đào tạo %s, học phí chỉ %s. Nhận tư vấn lộ trình và hướng dẫn làm hồ sơ miễn phí.',
         get_the_title(), $training_type ?: 'Từ xa', $school, $duration, $tuition
     );
     ```
     *Lỗi vi phạm:* Dùng từ *"lớp"*, không làm nổi bật tính chất đào tạo liên thông đại học và công nhận miễn giảm tín chỉ.
   - **H1 Tag:** Kế thừa từ `template-parts/banner.php:145` (`$banner_title = get_the_title()`).

3. **Trang Danh Mục & Lưu Trữ Hình Thức Học (`taxonomy-training_type.php`):**
   - **H1 Lỗi Kép (Dual `<h1>` Tags):**
     - H1 thứ nhất tại `template-parts/banner.php:145`: Xuất ra `$banner_title` = `'Hệ ' . $he_term->name` (ví dụ: `Hệ Từ xa`).
     - H1 thứ hai tại `taxonomy-training_type.php:171`:
       ```html
       <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
           <?php echo $active_type_term ? 'Hệ đào tạo: ' . esc_html( $active_type_term->name ) : 'Tất cả chương trình đào tạo'; ?>
       </h1>
       ```
     *Lỗi vi phạm:* Trang có đồng thời 2 thẻ `<h1>`, làm phân mảnh tín hiệu SEO On-page của Google. Cả 2 thẻ đều dùng thuật ngữ cũ *"Hệ đào tạo: [Tên]"* thay vì *"Liên thông [Hình thức học]"*.
   - **Subtitle vi phạm nghiệp vụ (`template-parts/banner.php:26-27`):**
     ```php
     if ( preg_match( '#^/he-dao-tao(?:/page/\d+)?/?$#i', $request_path ) ) {
         $banner_title    = 'Hệ Đào Tạo';
         $banner_subtitle = 'Tổng hợp các chương trình đào tạo từ xa, liên thông, văn bằng 2';
     ```
     Khẳng định sai lệch *"liên thông, văn bằng 2"* trên banner danh mục.

4. **Trang Danh Sách Trường Đại Học (`archive-school.php`):**
   - **H1 hiện tại (`template-parts/banner.php:61`):**
     `$banner_title = 'Trường Đại học Đối tác'`
     *Nhược điểm:* Quá chung chung, không gắn liền với mục đích tuyển sinh liên thông.
   - **Cần chuyển thành:** `Trường Đại Học Tuyển Sinh Liên Thông` hoặc `Danh Sách Trường Tuyển Sinh Liên Thông Đại Học`.

5. **Trang Chi Tiết Trường Đại Học (`single-school.php`):**
   - **H1 hiện tại (`single-school.php:100`):**
     ```html
     <h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight"><?php the_title(); ?></h1>
     ```
     Chỉ hiển thị tên trường (ví dụ: `Trường Đại học Kinh tế Quốc dân`).
   - **SEO Title:** Mặc định theo tiêu đề bài viết.
   - **Cần chuẩn hóa:** SEO Title & H1 phải có dạng: `Liên thông [Tên Trường]` (ví dụ: `Liên thông Đại học Kinh tế Quốc dân - Thông tin tuyển sinh & Ngành đào tạo`).

6. **Trang Danh Sách Ngành Học (`archive-major.php`):**
   - **H1 hiện tại (`template-parts/banner.php:64`):**
     `$banner_title = 'Chuyên Ngành'`
     *Nhược điểm:* Nhầm lẫn thuật ngữ giữa "Chuyên ngành" (Specialization) và "Ngành học" (Major).
   - **Cần chuyển thành:** `Ngành Học Tuyển Sinh Liên Thông` hoặc `Ngành Đào Tạo Liên Thông`.

7. **Trang Chi Tiết Ngành Học (`single-major.php`):**
   - **H1 hiện tại (`template-parts/banner.php:42`):**
     ```php
     $banner_title = ( 0 === stripos( trim( $raw_title ), 'ngành' ) ) ? $raw_title : 'Ngành ' . $raw_title;
     ```
     Ví dụ: `Ngành Kế toán` hoặc `Công nghệ thông tin`.
   - **SEO Title:** Mặc định của Rank Math.
   - **Cần chuẩn hóa:** `Liên thông [Ngành]` (ví dụ: `Liên thông Ngành Kế toán - Các trường đào tạo & Lịch tuyển sinh`).

---

### 1.4. Kiểm Toán Rủi Ro Đứt Gãy URLs & Chiến Lược Chuyển Hướng 301

#### A. Các Rủi Ro Đứt Gãy Được Xác Định
1. **Rủi ro vòng lặp Canonical 301 trên `/chuong-trinh/`:**
   CPT `program` đăng ký archive slug là `chuong-trinh`, nhưng lại bị `class-rewrite-rules.php:247` 301 redirect sang `/he-dao-tao/tu-xa/`. Rank Math sinh thẻ Canonical `https://domain.com/chuong-trinh/` nhưng trang này không bao giờ phản hồi HTTP 200.
2. **Rủi ro rò rỉ và lỗi 404 đối với Term Out-of-scope `/he-dao-tao/van-bang-2/`:**
   Term `van-bang-2` đã được sinh trong CSDL và có thể đã được các công cụ tìm kiếm cào dữ liệu (crawl). Khi loại bỏ khỏi menu và ẩn hiển thị, nếu không có chuyển hướng 301 thì người dùng và bot truy cập sẽ nhận mã lỗi 404.
3. **Rủi ro Dead Link từ chân trang (`footer.php:87-100`):**
   4 liên kết trong cột "Chương trình đào tạo" bị hardcode `href="#"` (gồm: *Cao đẳng online / VB2*, *Liên thông Đại Học chính quy*, *Trung Cấp lên Đại học*, *Đại học tại chức / VLVH*).
4. **Rủi ro Dead Link từ breadcrumb hướng dẫn (`single-guide.php:25`):**
   Liên kết `home_url('/huong-dan/')` trả về mã lỗi 404 vì archive của guide là `/cam-nang/`.
5. **Rủi ro phân mảnh URL trang tĩnh:**
   - Trang Hỏi đáp: `class-defaults.php` trỏ `/cau-hoi-thuong-gap/`, nhưng `cli-commands.php` tạo trang `/faq/`, `PROJECT.md` ghi `/hoi-dap/`.
   - Trang Tin tức: `class-defaults.php` trỏ `/tin-tuyen-sinh/`, trong khi các template dùng `/tin-tuc/`.
   - Trang Đăng ký: Có tài liệu tham chiếu `/dang-ky/` thay vì `/dang-ky-tu-van/`.

---

### 1.5. Kiểm Toán Canonical URL & Schema JSON-LD Markup

#### A. Canonical URL
1. **Singular Program & School:** Đã được lọc bởi `ltdh_seo_enforce_canonical_url()` (`class-rankmath-integration.php:67-78`), bảo đảm Canonical luôn trả về định dạng phẳng `home_url( '/' . $post->post_name . '/' )`.
2. **Singular Major:** Trả về chuẩn `/nganh-{slug}/`.
3. **Lỗ hổng Canonical trên `/he-dao-tao/` và `/he-dao-tao/{term}/`:**
   Do là các rewrite rule ảo gán vào `post_type=program`, Rank Math có thể nhận nhầm Canonical URL là `/chuong-trinh/` (vốn đang bị 301 redirect). Cần có hook tường minh ép Canonical của taxonomy `training_type` về đúng URL `/he-dao-tao/` hoặc `/he-dao-tao/{term}/`.

#### B. Schema JSON-LD Markup
1. **Schema `Course` (`class-rankmath-integration.php:99-109`):**
   - **Hiện tại:** Chỉ có `@type: Course`, `name`, `description`, và `provider`.
   - **Thiếu sót nghiêm trọng:**
     - Thiếu `hasCourseInstance` (chứa `courseMode`: Từ xa/Vừa học vừa làm/Chính quy, và `courseWorkload`).
     - Thiếu `offers` (học phí: `price`, `priceCurrency: VND`, `availability`).
     - Thiếu `educationalCredentialAwarded` ('Bằng Cử nhân / Kỹ sư').
2. **Schema `FAQPage` trên các trang thương mại:**
   - Tại `class-rankmath-integration.php:112-128`, code cố gắng inject `FAQPage` cho từng trang single program.
   - **Lưu ý nguyên tắc SEO Google (August 2023 - SEO Skill Rule 2):** Google đã giới hạn hiển thị FAQ Rich Snippets chỉ dành riêng cho các website cơ quan chính phủ và y tế uy tín. Việc nhồi nhét FAQPage bừa bãi vào từng sản phẩm thương mại không còn mang lại rich result và có nguy cơ bị thuật toán đánh giá là lạm dụng schema. Thay vào đó, chỉ nên duy trì FAQPage trên trang hỏi đáp tập trung (`page-faq.php`).
3. **Thiếu Schema `EducationalOrganization` và `WebSite` toàn trang:**
   Chỉ được định nghĩa trong hàm fallback khi KHÔNG có Rank Math. Khi Rank Math hoạt động, schema phụ thuộc vào cấu hình admin của Rank Math trong database. Cần bảo đảm theme bổ sung Schema JSON-LD thực thể trường đại học và cơ sở giáo dục một cách đồng nhất.

---

## 2. Logic Chain (Chuỗi Lập Luận Từ Quan Sát Đến Kết Luận)

```
[QUAN SÁT 1]
CPT program có archive_slug="chuong-trinh", nhưng class-rewrite-rules.php:247 lại 301 redirect /chuong-trinh/ sang /he-dao-tao/tu-xa/.
                    │
                    ▼
[LẬP LUẬN 1]
Rank Math sinh Canonical URL /chuong-trinh/ dựa trên CPT archive_slug, nhưng URL này lại nảy 301 sang /he-dao-tao/tu-xa/. Đồng thời archive-program.php:188 gửi form action tới /chuong-trinh/.
                    │
                    ▼
[KẾT LUẬN 1]
Gây ra Canonical Redirect Loop và làm chậm trễ trải nghiệm lọc của người dùng do bị 301 không cần thiết. Cần giữ URL /he-dao-tao/ làm trung tâm lưu trữ danh mục tuyển sinh liên thông, triệt tiêu 301 trên form action, và chuẩn hóa Canonical của /he-dao-tao/*.
```

```
[QUAN SÁT 2]
inc/cli-commands.php:101 gieo term "van-bang-2" vào taxonomy training_type. front-page.php:31 chứa H1 "Văn Bằng 2 & Đại Học Từ Xa". footer.php:87 có link "Cao đẳng online / VB2".
                    │
                    ▼
[LẬP LUẬN 2]
Yêu cầu nghiệp vụ cốt lõi tại section 1 & 2 của ORIGINAL_REQUEST.md nghiêm cấm mở rộng sang Văn bằng 2 hoặc coi ĐH từ xa là sản phẩm độc lập ngoài Liên thông.
                    │
                    ▼
[KẾT LUẬN 2]
Cần loại bỏ thuật ngữ "Văn bằng 2" khỏi H1 trang chủ, footer và menu; đồng thời thiết lập 301 Redirect an toàn: /he-dao-tao/van-bang-2/ -> 301 -> /he-dao-tao/tu-xa/ để hứng trọn vẹn traffic cũ mà không gây lỗi 404.
```

```
[QUAN SÁT 3]
class-rankmath-integration.php:28 xuất tiêu đề "Học [Tên] ([Hệ]) - [Trường] | Tuyển sinh [Năm]". template-parts/banner.php xuất H1 "Hệ [Tên]", "Trường Đại học Đối tác", "Chuyên Ngành".
                    │
                    ▼
[LẬP LUẬN 3]
Các tiêu đề và thẻ H1 này hoàn toàn vắng bóng từ khóa trọng tâm "Liên thông", làm suy yếu năng lực xếp hạng từ khóa tìm kiếm có chủ đích cao (High-Intent SEO keywords) của thí sinh.
                    │
                    ▼
[KẾT LUẬN 3]
Chuẩn hóa 100% SEO Title, H1 và Meta Description theo công thức mục tiêu: "Liên thông [Hình thức học]", "Liên thông [Ngành]", "Liên thông [Trường]". Loại bỏ hoàn toàn thẻ H1 thứ hai trên taxonomy-training_type.php và archive-program.php.
```

```
[QUAN SÁT 4]
ltdh_breadcrumb() cố tình ngắt rank_math_the_breadcrumbs() trên /he-dao-tao/* và in HTML thô không có thuộc tính Microdata. single-guide.php có 2 lần breadcrumbs và chứa link 404 /huong-dan/.
                    │
                    ▼
[LẬP LUẬN 4]
Khu vực danh mục quan trọng nhất của portal (/he-dao-tao/*) bị mất sạch BreadcrumbList Schema trên Google SERP. Người dùng xem bài viết hướng dẫn bị rối mắt và nhấp vào link gãy.
                    │
                    ▼
[KẾT LUẬN 4]
Bổ sung đầy đủ thuộc tính Microdata Schema.org vào HTML fallback của ltdh_breadcrumb(); xóa bỏ khối breadcrumb thủ công trùng lặp trong single-guide.php và cấu hình 301 redirect /huong-dan/ -> /cam-nang/.
```

---

## 3. Caveats (Giới Hạn Khảo Sát & Giả Định)

1. **Chế độ kiểm tra mã nguồn (Read-Only):**
   Khảo sát được thực hiện hoàn toàn ở chế độ đọc tĩnh (static analysis) và kiểm tra cơ chế hoạt động của code PHP. Không thực hiện bất kỳ lệnh ghi đè nào lên mã nguồn của theme trong giai đoạn này.
2. **Trạng thái Database và Plugin thực tế:**
   Quá trình kiểm tra ghi nhận plugin Rank Math SEO được tích hợp sâu qua các filter trong mã nguồn (`rank_math/frontend/title`, `rank_math/frontend/canonical`, `rank_math/json_ld`). Kết quả hiển thị thực tế trên môi trường live còn phụ thuộc vào việc plugin Rank Math đã được cài đặt và kích hoạt trong WP-Admin hay chưa. Mã nguồn theme đã có sẵn cơ chế fallback (`ltdh_output_native_schema_fallback`) khi không có Rank Math.
3. **Chỉ mục Google (Google Indexation Status):**
   Giả định rằng website đã từng được Google cào dữ liệu các URL cũ như `/truong-doi-tac/`, `/nganh-hoc/`, `/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/van-bang-2/`, `/{school-slug}/`. Mọi giải pháp đề xuất đều tuân thủ nguyên tắc **BẢO TOÀN URLS CÔNG KHAI TUYỆT ĐỐI** và sử dụng chuyển hướng 301 vĩnh viễn đối với các biến thể.

---

## 4. Bảng Ánh Xạ Kiểm Toán Bắt Buộc (Current -> Target Mapping Table)

Bảng ánh xạ 9 cột theo đúng quy chuẩn kiểm toán của dự án:
`CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`

| CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE |
|---|---|---|---|---|---|---|---|---|
| **CPT: program** (Single) | Chương trình đào tạo | Đại diện cho một cơ hội tuyển sinh liên thông cụ thể của một trường | `training_type`, `campus` | N:1 với `school` (`school_relationship`), N:1 với `major` (`major_relationship`) | `/{program-slug}/` (Flat root) | `single-program.php` | **Cơ hội tuyển sinh Liên thông cụ thể** | Chuẩn hóa SEO Title thành `Liên thông [Ngành] ([Hình thức]) - [Trường] | Tuyển sinh [Năm]`. Bổ sung `hasCourseInstance` và `offers` vào Schema Course JSON-LD. |
| **CPT: program** (Archive Base) | Chương trình đào tạo (Archive) | Trang gốc danh mục chương trình | — | Chứa các bài viết `program` | `/chuong-trinh/` (Đang bị 301 sang `/he-dao-tao/tu-xa/`) | `archive-program.php` (Đang bị bypass) | **Lưu trữ Cơ hội tuyển sinh Liên thông** | Duy trì 301 Redirect an toàn: `/chuong-trinh/` -> `/he-dao-tao/tu-xa/`. Sửa form action trong `archive-program.php` thành `/he-dao-tao/` để tránh redirect loop. |
| **CPT: school** (Single) | Trường đối tác | Trang hồ sơ chi tiết và thông tin tuyển sinh của trường ĐH | `region` | 1:N với `program` (qua meta rollup `_offered_programs`) | `/{school-slug}/` (Flat root) | `single-school.php` | **Trường Đại học Tuyển sinh Liên thông** | Đổi H1 và SEO Title thành `Liên thông [Tên Trường]` (ví dụ: `Liên thông Đại học Kinh tế Quốc dân`). Giữ nguyên URL root phẳng. |
| **CPT: school** (Archive) | Trường đối tác (Archive) | Danh bạ các trường đại học tuyển sinh | — | Danh sách các trường `school` | `/truong-doi-tac/` | `archive-school.php` | **Danh mục Trường Đại học Tuyển sinh Liên thông** | Đổi tiêu đề banner & H1 từ "Trường Đại học Đối tác" thành "Trường Đại học Tuyển sinh Liên thông". Giữ nguyên URL `/truong-doi-tac/`. |
| **CPT: major** (Single) | Ngành học / Chuyên ngành | Giới thiệu chương trình học, cơ hội nghề nghiệp của ngành | `major_cat` | 1:N với `program` (qua meta rollup `_offered_programs`) | `/nganh-{major-slug}/` | `single-major.php` | **Ngành Đào tạo Liên thông** | Đổi tiêu đề banner & H1 thành `Liên thông Ngành [Tên Ngành]`. Đồng nhất thuật ngữ "Ngành học" (không gọi lẫn lộn là "Chuyên ngành"). Giữ nguyên URL `/nganh-{slug}/`. |
| **CPT: major** (Archive) | Chuyên Ngành (Archive) | Danh bạ các ngành đào tạo đại học | — | Danh sách các ngành `major` | `/nganh-hoc/` | `archive-major.php` | **Danh mục Ngành Tuyển sinh Liên thông** | Đổi tiêu đề banner & H1 từ "Chuyên Ngành" thành "Ngành Học Tuyển Sinh Liên Thông". Giữ nguyên URL `/nganh-hoc/`. |
| **Taxonomy: training_type** (Term `tu-xa`) | Từ xa | Phân loại phương thức học trực tuyến | `training_type` | Gắn vào `program`, `school` | `/he-dao-tao/tu-xa/` | `taxonomy-training_type.php` | **Hình thức: Liên thông Từ xa** | Đổi H1 & Title thành `Liên thông từ xa`. Loại bỏ thẻ H1 trùng lặp thứ 2 trong template. Bổ sung Breadcrumb Microdata. Giữ nguyên URL `/he-dao-tao/tu-xa/`. |
| **Taxonomy: training_type** (Term `vua-hoc-vua-lam`) | Vừa học vừa làm | Phân loại phương thức học buổi tối / cuối tuần | `training_type` | Gắn vào `program`, `school` | `/he-dao-tao/vua-hoc-vua-lam/` | `taxonomy-training_type.php` | **Hình thức: Liên thông Vừa học vừa làm** | Đổi H1 & Title thành `Liên thông vừa học vừa làm`. Loại bỏ thẻ H1 trùng lặp thứ 2. Giữ nguyên URL `/he-dao-tao/vua-hoc-vua-lam/`. |
| **Taxonomy: training_type** (Term `chinh-quy`) | Chính quy | Phân loại học tập trung ban ngày tại cơ sở | `training_type` | Gắn vào `program`, `school` | `/he-dao-tao/chinh-quy/` | `taxonomy-training_type.php` | **Hình thức: Liên thông Chính quy** | Đổi H1 & Title thành `Liên thông chính quy`. Loại bỏ thẻ H1 trùng lặp thứ 2. Giữ nguyên URL `/he-dao-tao/chinh-quy/`. |
| **Taxonomy: training_type** (Term `van-bang-2`) | Văn bằng 2 | Phân loại tuyển sinh người đã có bằng ĐH khác | `training_type` | Gắn vào `program`, `school` | `/he-dao-tao/van-bang-2/` | `taxonomy-training_type.php` | 🚨 **NGOÀI PHẠM VI (DEPRECATED)** | Gỡ bỏ hoàn toàn khỏi menu, bộ lọc và nội dung tuyển sinh. Thiết lập **HTTP 301 Redirect**: `/he-dao-tao/van-bang-2/` -> `/he-dao-tao/tu-xa/`. |
| **Taxonomy: training_type** (Base Archive) | Hệ đào tạo | Trang gốc hiển thị các hình thức học | `training_type` | Danh sách tất cả các chương trình liên thông | `/he-dao-tao/` | `taxonomy-training_type.php` | **Các Hình thức Tuyển sinh Liên thông** | Đổi H1 từ "Hệ Đào Tạo" thành "Hình Thức Học Liên Thông Đại Học". Xóa bỏ mô tả "Văn bằng 2" trên banner. |
| **Taxonomy: campus** | Cơ sở đào tạo | Địa điểm học tập, thi tuyển | `campus` | Gắn vào `program`, `school` | `/co-so/{slug}/` (Base `/co-so/` 301 sang `/he-dao-tao/tu-xa/`) | `taxonomy.php` | **Cơ sở / Trạm đào tạo Liên thông** | Giữ nguyên routing hiện tại. Đổi tiêu đề banner thành `Cơ sở đào tạo: [Tên]`. |
| **Taxonomy: region** | Khu vực | Miền Bắc, Miền Trung, Miền Nam | `region` | Gắn vào `school` | `/khu-vuc/{slug}/` | `taxonomy.php` | **Khu vực trường tuyển sinh** | Giữ nguyên routing và template. |
| **Taxonomy: major_cat** | Nhóm ngành | Nhóm kinh tế, kỹ thuật, ngôn ngữ... | `major_cat` | Gắn vào `major` | `/nhom-nganh/{slug}/` | `taxonomy.php` | **Nhóm ngành tuyển sinh** | Giữ nguyên routing và template. |
| **CPT: guide** (Single) | Cẩm nang tuyển sinh | Bài viết hướng dẫn quy chế, hồ sơ liên thông | — | Bài viết độc lập | `/huong-dan/{slug}/` | `single-guide.php` | **Bài viết Hướng dẫn Liên thông** | Xóa khối breadcrumb trùng lặp và sửa liên kết gãy `/huong-dan/` thành `/cam-nang/`. |
| **CPT: guide** (Archive) | Cẩm nang tuyển sinh (Archive) | Danh sách bài viết hướng dẫn | — | Danh sách `guide` | `/cam-nang/` | `archive.php` / `index.php` | **Chuyên mục Cẩm nang Liên thông** | Giữ nguyên URL `/cam-nang/`. Thiết lập 301 redirect `/huong-dan/` -> `/cam-nang/`. |
| **Virtual Route: Compare** | So sánh chương trình | So sánh học phí, lộ trình giữa 2 chương trình | — | So sánh giữa 2 CPT `program` | `/so-sanh/chuong-trinh/{slug1}-vs-{slug2}/` | `page-compare-program.php` | **So sánh Cơ hội Tuyển sinh Liên thông** | Giữ nguyên routing. Cập nhật breadcrumb đồng nhất. |
| **Trang chủ (Front Page)** | Trang chủ | Cổng thông tin chính | — | Toàn bộ hệ thống | `/` | `front-page.php` | **Cổng Tuyển Sinh Liên Thông Đại Học** | Thay đổi thẻ H1 thành `Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học Toàn Quốc` (xóa "Văn Bằng 2 & Đại Học Từ Xa"). |
| **Trang tĩnh: Hỏi đáp** | Câu hỏi thường gặp | Giải đáp thắc mắc liên thông | — | — | `/cau-hoi-thuong-gap/` | `page-faq.php` | **Hỏi đáp Tuyển sinh Liên thông** | Đồng nhất URL chính là `/cau-hoi-thuong-gap/`. Thêm 301 redirect từ `/faq/` và `/hoi-dap/`. Bổ sung FAQPage Schema. |
| **Trang tĩnh: Đăng ký** | Đăng ký tư vấn | Form gửi hồ sơ ứng viên | — | — | `/dang-ky-tu-van/` | `page-register.php` | **Đăng ký Xét tuyển Liên thông** | Đồng nhất URL `/dang-ky-tu-van/`. Thêm 301 redirect từ `/dang-ky/`. |
| **Trang tĩnh: Tin tức** | Tin tức tuyển sinh | Blog tin tức giáo dục | — | — | `/tin-tuc/` | `index.php` | **Tin Tức Tuyển Sinh Liên Thông** | Đồng nhất URL `/tin-tuc/`. Thêm 301 redirect từ `/tin-tuyen-sinh/`. |

---

## 5. Chiến Lược Chuyển Hướng 301 Chi Tiết (301 Redirect Strategy)

Toàn bộ các quy tắc chuyển hướng 301 bắt buộc phải được xử lý ngay tại sự kiện `template_redirect` (hoặc cấu hình Nginx/Apache) với mã trạng thái HTTP 301 (Moved Permanently):

```text
========================================================================================
                          DANH SÁCH 301 REDIRECT BẮT BUỘC
========================================================================================
1. LOẠI BỎ HỆ ĐÀO TẠO NGOÀI PHẠM VI (VĂN BẰNG 2):
   /he-dao-tao/van-bang-2/              -> 301 -> /he-dao-tao/tu-xa/
   /he-dao-tao/van-bang-2/page/(.*)     -> 301 -> /he-dao-tao/tu-xa/

2. BẢO VỆ DANH MỤC CHƯƠNG TRÌNH & TRÁNH REDIRECT LOOP:
   /chuong-trinh/                       -> 301 -> /he-dao-tao/tu-xa/
   /program/(.*)                        -> 301 -> /$1/

3. TIỀN TỐ CŨ CỦA TRƯỜNG & NGÀNH (ĐÃ HOẠT ĐỘNG, TIẾP TỤC BẢO TOÀN):
   /truong-doi-tac/([^/]+)/             -> 301 -> /$1/  (ngoại trừ /page/X/)
   /nganh-hoc/([^/]+)/                  -> 301 -> /nganh-$1/  (ngoại trừ /page/X/)

4. BASE TAXONOMY RỖNG:
   /co-so/                              -> 301 -> /he-dao-tao/tu-xa/

5. SỬA LỖI ĐƯỜNG DẪN GÃY CỦA CẨM NANG:
   /huong-dan/                          -> 301 -> /cam-nang/

6. ĐỒNG NHẤT CÁC BIẾN THỂ TRANG TĨNH:
   /tin-tuyen-sinh/                     -> 301 -> /tin-tuc/
   /tin-tuyen-sinh/(.*)                 -> 301 -> /tin-tuc/$1
   /faq/                                -> 301 -> /cau-hoi-thuong-gap/
   /hoi-dap/                            -> 301 -> /cau-hoi-thuong-gap/
   /dang-ky/                            -> 301 -> /dang-ky-tu-van/
========================================================================================
```

---

## 6. Đề Xuất Phương Án Can Thiệp Tối Thiểu An Toàn Nhất (Minimal Safe Intervention Plan)

Kế hoạch can thiệp được thiết kế theo 4 gói chỉnh sửa cô đọng, **không làm thay đổi kiến trúc DB cốt lõi, không đổi slug công khai đã index, không phá vỡ UI/CSS hiện có**:

### Gói 1: Tối Ưu Định Tuyến & 301 Redirects (`inc/core/class-rewrite-rules.php`)
- **Mục tiêu:** Củng cố các rule chuyển hướng 301 tập trung, bảo đảm xử lý 100% các biến thể URL cũ và out-of-scope (như `van-bang-2`, `tin-tuyen-sinh`, `huong-dan`).
- **Thực hiện:**
  - Bổ sung lệnh redirect `/he-dao-tao/van-bang-2/` và các biến thể trang tĩnh vào hàm `ltdh_redirect_taxonomy_base()`.
  - Giữ nguyên toàn bộ cấu trúc slug phẳng root `/{school-slug}/`, `/{program-slug}/` và `/nganh-{major-slug}/`.

### Gói 2: Chuẩn Hóa SEO Titles, H1 & Meta Descriptions (`inc/seo/class-rankmath-integration.php`)
- **Mục tiêu:** Đưa toàn bộ thẻ tiêu đề và mô tả về công thức chuẩn `Liên thông [Đối tượng]`.
- **Thực hiện:**
  - Cập nhật filter `rank_math/frontend/title`:
    - Với `program`: `sprintf( 'Liên thông %s (%s) - %s | Tuyển sinh %d', get_the_title(), $training_type, $school, $year )`.
    - Với `school`: `sprintf( 'Liên thông %s - Thông tin tuyển sinh & Ngành đào tạo', get_the_title() )`.
    - Với `major`: `sprintf( 'Liên thông Ngành %s - Danh sách trường & Lộ trình đào tạo', get_the_title() )`.
    - Với term `training_type`: `sprintf( 'Liên thông %s - Danh sách trường & Chương trình tuyển sinh', $term->name )`.
  - Cập nhật filter `rank_math/frontend/description`: Thêm từ khóa ngữ cảnh về chuyển đổi bằng cấp, công nhận tín chỉ liên thông.
  - Thêm filter `rank_math/frontend/canonical`: Ép chặt Canonical trên `/he-dao-tao/` và `/he-dao-tao/{term}/` về chính nó, triệt tiêu nguy cơ trỏ vào `/chuong-trinh/`.

### Gói 3: Tối Ưu Thẻ H1 & Thuật Ngữ Trên Templates
- **`front-page.php:31`:** Thay H1 thành `<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học Toàn Quốc</h1>`.
- **`template-parts/banner.php`:**
  - Dòng 26-27: Đổi tiêu đề banner `/he-dao-tao/` thành `Hình Thức Học Liên Thông Đại Học`, subtitle thành `Khám phá các hình thức học liên thông: Từ xa, Vừa học vừa làm, Chính quy`.
  - Dòng 78: Đổi `$banner_title = 'Liên thông ' . $he_term->name`.
  - Dòng 61: Đổi archive school thành `Trường Đại Học Tuyển Sinh Liên Thông`.
  - Dòng 64: Đổi archive major thành `Ngành Đào Tạo Liên Thông`.
- **`taxonomy-training_type.php:171` & `archive-program.php:171`:** Đổi thẻ `<h1>` thứ hai thành thẻ `<h2>` hoặc `<div>` tiêu đề bộ lọc để triệt tiêu lỗi Dual H1 tags.
- **`single-school.php:100`:** Thêm tiền tố ngữ cảnh hoặc subtitle thể hiện tuyển sinh liên thông.
- **`single-guide.php:23-27`:** Xóa bỏ khối `<nav>` breadcrumb thủ công và sửa link gãy.
- **`footer.php:87-100`:** Xóa link `Cao đẳng online / VB2`, thay bằng các liên kết chuẩn tới 3 hình thức học liên thông (`/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`, `/he-dao-tao/chinh-quy/`).

### Gói 4: Chuẩn Hóa Breadcrumbs & Microdata Schema (`inc/core/class-helpers.php` & `inc/seo/class-rankmath-integration.php`)
- **`inc/core/class-helpers.php`:**
  - Bổ sung các thuộc tính Schema Microdata (`itemscope`, `itemtype="https://schema.org/BreadcrumbList"`, `itemprop="itemListElement"`) vào mã HTML xuất ra của `ltdh_breadcrumb()`.
  - Cập nhật nhãn phân cấp: `Hệ đào tạo` -> `Liên thông [Hình thức]`, `Trường đối tác` -> `Trường đại học`, `Chuyên ngành` -> `Ngành học`.
- **`inc/seo/class-rankmath-integration.php`:**
  - Bổ sung `hasCourseInstance` (chứa `courseMode`, `courseWorkload`) và `offers` vào Course Schema của Rank Math hook `rank_math/json_ld`.
  - Thêm xử lý `FAQPage` JSON-LD dành riêng cho `page-faq.php`.

---

## 7. Verification Method (Phương Pháp Kiểm Tra & Xác Minh Độc Lập)

Người thực hiện sau hoặc kỹ sư triển khai có thể kiểm chứng lại toàn bộ các phát hiện trên bằng các câu lệnh và phương pháp sau:

### 7.1. Kiểm Tra Cú Pháp Toàn Bộ Tệp PHP Liên Quan
Chạy kiểm tra cú pháp PHP 8+ không có lỗi:
```bash
php -l inc/post-types.php
php -l inc/core/class-rewrite-rules.php
php -l inc/core/class-helpers.php
php -l inc/core/class-menus.php
php -l inc/seo/class-rankmath-integration.php
php -l template-parts/banner.php
php -l taxonomy-training_type.php
php -l archive-program.php
php -l single-program.php
php -l single-school.php
php -l single-major.php
php -l single-guide.php
php -l front-page.php
php -l footer.php
```

### 7.2. Kiểm Tra Lỗi Dual H1 Bằng CLI
Xác minh sự xuất hiện của thẻ `<h1>` trên các tệp template:
```bash
grep -n "<h1" taxonomy-training_type.php
grep -n "<h1" archive-program.php
grep -n "<h1" front-page.php
grep -n "<h1" template-parts/banner.php
```
*Điều kiện xác minh:* `taxonomy-training_type.php` và `archive-program.php` mỗi tệp có 1 thẻ H1 bên trong tệp và 1 thẻ H1 khác được inject thông qua `get_template_part('template-parts/banner')`.

### 7.3. Kiểm Tra Mã Chuyển Hướng 301 Bằng cURL
Khi server đang chạy (local hoặc staging), kiểm tra HTTP Response Code:
```bash
# Kiểm tra redirect /chuong-trinh/ -> 301
curl -I "http://localhost:10028/chuong-trinh/"

# Kiểm tra redirect /he-dao-tao/van-bang-2/ -> 301
curl -I "http://localhost:10028/he-dao-tao/van-bang-2/"

# Kiểm tra redirect /huong-dan/ -> 301
curl -I "http://localhost:10028/huong-dan/"

# Kiểm tra redirect /faq/ -> 301
curl -I "http://localhost:10028/faq/"
```
*Điều kiện xác minh:* Header trả về phải có `HTTP/1.1 301 Moved Permanently` kèm header `Location` chính xác.

### 7.4. Kiểm Tra Schema JSON-LD & BreadcrumbList
Truy cập mã nguồn HTML của trang chi tiết chương trình và trang danh mục:
```bash
# Kiểm tra Course Schema có hasCourseInstance và offers
curl -s "http://localhost:10028/dai-hoc-kinh-te-quoc-dan-ke-toan-tu-xa/" | grep -A 25 "application/ld+json"

# Kiểm tra BreadcrumbList microdata trên /he-dao-tao/tu-xa/
curl -s "http://localhost:10028/he-dao-tao/tu-xa/" | grep -E "BreadcrumbList|itemListElement"
```

---

## 8. Kết Luận (Conclusion)

1. **Về Kiến trúc URL:** Kiến trúc URL hiện tại của theme (`/{program-slug}/`, `/{school-slug}/`, `/nganh-{major-slug}/`, `/truong-doi-tac/`, `/nganh-hoc/`, `/he-dao-tao/`) **hoàn toàn có thể bảo toàn 100%** mà không cần đập bỏ hay đổi slug công khai gây tổn hại SEO.
2. **Về Xung đột Nghiệp vụ:** Toàn bộ các dấu vết của "Văn bằng 2" và sự nhầm lẫn coi "Đào tạo từ xa" là một sản phẩm tuyển sinh riêng biệt ngoài Liên thông đã được khoanh vùng chính xác (H1 trang chủ, term taxonomy `van-bang-2`, menu chân trang, banner subtitle).
3. **Về On-Page SEO:** Đã nhận diện triệt để các lỗi kỹ thuật: Dual H1 tags trên danh mục, thiếu Schema Course nâng cao, mất BreadcrumbList Schema trên `/he-dao-tao/*`, và liên kết gãy chân trang/cẩm nang.
4. **Kế hoạch hành động:** Báo cáo cung cấp đầy đủ bảng ánh xạ Current -> Target và kế hoạch can thiệp tối thiểu an toàn, sẵn sàng làm cơ sở vững chắc cho các bước triển khai tiếp theo.
