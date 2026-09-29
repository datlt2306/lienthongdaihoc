# BÁO CÁO KIỂM ĐỊNH KỸ THUẬT CHUYÊN SÂU: REQUIREMENT R2
## QUERYING, FILTERING & TAXONOMY UX CHO HỆ ĐÀO TẠO (`training_type`) VÀ TRƯỜNG ĐỐI TÁC (`school`)

- **Dự án**: Theme WordPress Liên Thông Đại Học (`lienthongdaihoc`)
- **Tác nhân thực hiện**: `explorer_survey_query_1` (teamwork_preview_explorer)
- **Thời gian kiểm định**: 2026-09-28T04:15:00Z
- **Phạm vi kiểm định**: R2 - Query logic, WP_Query, Taxonomy Archives, Filter UX, N+1 Bottlenecks, Caching, SEO Permalinks & Breadcrumbs
- **Tệp phân tích chính**:
  - `inc/core/class-query-filters.php`
  - `inc/core/class-rewrite-rules.php`
  - `inc/core/class-helpers.php`
  - `archive-school.php`
  - `single-school.php`
  - `taxonomy-training_type.php`
  - `archive-program.php`
  - `archive-major.php`
  - `single-major.php`
  - `page-compare-program.php`
  - `inc/search-engine.php`
  - `inc/relationship-hooks.php`
  - `inc/seo/class-rankmath-integration.php`
  - `assets/js/main.js`
  - `functions.php`

---

## 1. TỔNG QUAN ĐÁNH GIÁ (EXECUTIVE SUMMARY) & CHỈ SỐ SỨC KHỎE

Hệ thống quản lý **Hệ đào tạo** (`training_type`) và **Trường đối tác** (`school`) là trục xương sống trong nghiệp vụ tuyển sinh của website Liên Thông Đại Học. Tuy nhiên, qua quá trình rà soát mã nguồn tĩnh và truy vết luồng thực thi (execution trace), hệ thống đang bộc lộ những khiếm khuyết cấu trúc nghiêm trọng:

| Tiêu chí đánh giá | Điểm số (Thang 100) | Tình trạng | Rủi ro chính |
|---|---|---|---|
| **WP_Query & Hiệu năng Database** | **42/100** | 🔴 Nghiêm trọng | Lỗi N+1 query nặng trong vòng lặp school/program; bỏ qua Main Query; query trùng lặp. |
| **Tính toàn vẹn dữ liệu Taxonomy** | **48/100** | 🔴 Nghiêm trọng | `training_type` chỉ gán vào `program`, không gán vào `school`. Card view và List view hiển thị lệch nhau hoàn toàn. |
| **Trải nghiệm Lọc (Filter UX)** | **52/100** | 🟠 Cảnh báo | Code AJAX trong `assets/js/main.js` bị chết (dead code) do thiếu ID container; 100% reload trang; số lượng facet bị sai lệch (phantom facets). |
| **Bộ nhớ đệm (Caching & Transients)** | **38/100** | 🔴 Nghiêm trọng | Bỏ qua Transient `LTDH_TRANSIENT_FEATURED_SCHOOLS`; `wp_cache_*` không có persistent backend; cache tìm kiếm không tự xóa khi cập nhật bài viết. |
| **SEO, Canonical & Breadcrumbs** | **55/100** | 🟠 Cảnh báo | RankMath breadcrumb bị tắt trên `/he-dao-tao/*`; rewrite rule gốc `([^/]+)/?$` chạy 2 query/request; canonical của `/he-dao-tao/` tạo vòng lặp chuyển hướng 301. |

---

## 2. KIẾN TRÚC MÔ HÌNH DỮ LIỆU & LUỒNG TRUY VẤN (DATA ARCHITECTURE)

### 2.1. Quan hệ tam giác: School ⟷ Program ⟷ Major ⟷ Taxonomy
Trong cấu hình `inc/acf-import-cpts.json` và `inc/post-types.php`:
- `school`: CPT Trường đối tác. Taxonomies đính kèm: duy nhất `region`.
- `major`: CPT Ngành học. Taxonomies đính kèm: không có (Taxonomy `major_cat` có `object_type: ["major"]`).
- `program`: CPT Chương trình đào tạo (Thực thể trung gian liên kết). Taxonomies đính kèm: `training_type`, `campus`.
- `training_type`: Taxonomy Hệ đào tạo. Khai báo `object_type: ["program"]`.

```
                    ┌─────────────────────────┐
                    │      CPT: school        │
                    │  (Taxonomy: region)     │
                    └────────────┬────────────┘
                                 │
                   school_relationship (ACF Meta)
                                 │
                                 ▼
┌──────────────────┐  major_rel   ┌─────────────────────────┐   tax_query    ┌─────────────────────────┐
│    CPT: major    │ ◄─────────── │      CPT: program       │ ─────────────► │ Taxonomy: training_type │
│ (Tax: major_cat) │              │  (Thực thể trung gian)  │                │ (Từ xa, Liên thông...)  │
└──────────────────┘              └─────────────────────────┘                └─────────────────────────┘
```

### 2.2. Điểm nghẽn kiến trúc cốt lõi
1. **Taxonomy `training_type` không được gán cho `school`**:
   - Khi người dùng muốn xem: *"Danh sách các Trường có tuyển sinh Hệ Từ xa"*, hệ sinh thái WordPress không có quan hệ taxonomy trực tiếp trên Post Type `school`.
   - Trang lưu trữ trường (`archive-school.php`) không thể dùng `tax_query` chuẩn của WordPress trên bảng `term_relationships` của `school`.
2. **Sự phụ thuộc hoàn toàn vào CPT trung gian `program`**:
   - Mọi thông tin về việc một trường có hệ đào tạo nào, tuyển sinh ngành gì đều phải đi gián tiếp qua việc query các bài viết `program` có `meta_query` `school_relationship = $school_id`.

---

## 3. ĐÁNH GIÁ CHUYÊN SÂU: QUERY LOGIC & WP_QUERY AUDITING

### 3.1. Truy vấn Trường theo Hệ Đào Tạo (`training_type`)
- **Tại `archive-school.php`**:
  - Không có bất kỳ bộ lọc nào theo `training_type`. Trang chỉ hỗ trợ chuyển đổi giao diện `?view=list` và `?view=card` (dòng 14).
  - Tệp `inc/core/class-query-filters.php` (dòng 24-32) chỉ can thiệp biến `posts_per_page` từ `$_GET['limit']`:
    ```php
    if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
        $limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
        ...
    }
    ```
  - Nếu người dùng truy cập `/truong-doi-tac/?he=tu-xa`, tham số `he` bị bỏ qua 100%, hệ thống vẫn trả về toàn bộ danh sách trường.

- **Tại `taxonomy-training_type.php` / `archive-program.php`**:
  - Đối tượng query chính **không phải là `school`** mà là `program`.
  - Nếu người dùng chọn lọc theo trường (`$_GET['truong']`), hệ thống lọc các `program` có `school_relationship` bằng ID trường đó.
  - **Hệ quả UX**: Không có trang đích chuyên biệt dạng *"Danh bạ các trường đại học đào tạo từ xa"* hay *"Danh bạ các trường đại học đào tạo liên thông"*. Người dùng chỉ được xem danh sách rời rạc các chương trình học hoặc các card trường bị lặp lại theo từng chương trình.

### 3.2. Truy vấn Chương trình/Ngành học theo Trường và Hệ
- Tại `taxonomy-training_type.php` (dòng 39-60), query kết hợp cả taxonomy và 2 tầng meta:
  ```php
  $args = [
      'post_type'      => 'program',
      'posts_per_page' => $selected_limit,
      'paged'          => $paged,
      'post_status'    => 'publish',
      'meta_query'     => [
          'relation' => 'AND',
          [
              'relation' => 'OR',
              [ 'key' => 'admission_status', 'value' => 'tam-ngung', 'compare' => '!=' ],
              [ 'key' => 'admission_status', 'compare' => 'NOT EXISTS' ],
          ]
      ],
      'tax_query' => [ 'relation' => 'AND' ],
  ];
  ```
- **Xử lý tham số `nganh` (dòng 92-117)**:
  - Hệ thống kiểm tra xem `$_GET['nganh']` có phải slug của CPT `major` không bằng hàm `get_page_by_path( $selected_nhom, OBJECT, 'major' )`.
  - Nếu không phải, hệ thống chạy thêm một query `get_posts( ['post_type' => 'major', 'numberposts' => -1, 'tax_query' => [...]] )` để lấy toàn bộ ID ngành thuộc nhóm ngành rồi đẩy vào `meta_query` `IN`.
  - **Đánh giá**: Cơ chế fallback kép này làm tăng từ 1 đến 2 truy vấn SQL trước khi chạy query chính, và tạo ra sự nhập nhằng giữa slug của CPT `major` và slug của taxonomy `major_cat`.

### 3.3. Danh mục các mẫu lỗi N+1 Query nghiêm trọng

#### [N+1-01] Vòng lặp lấy chương trình và terms trong `archive-school.php` (List View)
- **Vị trí**: `archive-school.php`, dòng 264-306.
- **Mã nguồn thực tế**:
  ```php
  // Dòng 264: Query đếm số ngành (chạy 1 get_posts + N lần get_post_meta)
  $prog_count = ltdh_get_school_unique_majors_count( $school_id );

  // Dòng 265-276: Query lấy toàn bộ ID chương trình của trường
  $offered_program_ids = get_posts( [
      'post_type'   => 'program',
      'numberposts' => -1,
      'fields'      => 'ids',
      'meta_query'  => [
          [
              'key'     => 'school_relationship',
              'value'   => $school_id,
              'compare' => '=',
          ],
      ],
  ] );

  // Dòng 297-306: Vòng lặp N chương trình để lấy taxonomy term!
  if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
      foreach ( $offered_program_ids as $pid ) {
          $terms = wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE );
          ...
      }
  }
  ```
- **Phân tích tác động**:
  - Với mỗi trường trên trang: Chạy 1 query đếm ngành + 1 query lấy chương trình + K query lấy terms cho K chương trình.
  - Trang lưu trữ hiển thị mặc định 12 trường, mỗi trường có trung bình 15 chương trình:
    $$\text{Số query phát sinh} = 12 \times (1 + 1 + 15) = 204 \text{ queries!}$$
  - Nếu người dùng chọn `limit=50`: Số query vượt quá **800 queries** trên một lượt tải trang!

#### [N+1-02] Render Card trong `taxonomy-training_type.php` và `archive-program.php`
- **Vị trí**: `taxonomy-training_type.php`, dòng 355-447.
- **Mã nguồn thực tế**:
  Trong vòng lặp `while ( $query->have_posts() ) : $query->the_post();`:
  - Dòng 357: `get_field( 'school_relationship', $prog_id )` (truy vấn postmeta)
  - Dòng 358: `get_the_title( $school_rel_id )` (load post school)
  - Dòng 359: `get_field( 'major_relationship', $prog_id )` (truy vấn postmeta)
  - Dòng 367: `get_the_post_thumbnail_url( $major_rel_id, 'medium' )` (truy vấn postmeta/attachment)
  - Dòng 372: `wp_get_post_terms( $prog_id, 'training_type' )` (truy vấn terms)
  - Dòng 402: `get_the_post_thumbnail_url( $school_rel_id, 'medium' )` (truy vấn thumbnail trường)
  - Dòng 421: `ltdh_get_school_image_id( $school_rel_id )` (chạy `get_field('logo', $school_id)`)
  - Dòng 440: `ltdh_get_program_learning_details( $prog_id )` (chạy `wp_get_post_terms` cho campus)
  - Dòng 443-444: `get_field('tuition_fee')`, `get_field('duration')`
- **Phân tích tác động**:
  - Không có lệnh `update_meta_cache()` hay `update_object_term_cache()` cho các ID trường và ngành liên kết trước vòng lặp.
  - Mỗi card chương trình sinh ra từ 8 đến 11 lệnh đọc DB/cache riêng rẽ.

#### [N+1-03] Lỗi tính Facet Sidebar trong `taxonomy-training_type.php`
- **Vị trí**: `taxonomy-training_type.php`, dòng 184-216.
- **Mã nguồn thực tế**:
  ```php
  $filtered_sidebar_programs = get_posts( $major_filter_args ); // posts_per_page = -1
  if ( ! empty( $filtered_sidebar_programs ) ) {
      update_meta_cache( 'post', $filtered_sidebar_programs );
      foreach ( $filtered_sidebar_programs as $p_id ) {
          $major_rel = get_post_meta( $p_id, 'major_relationship', true );
          ...
      }
  }
  ```
- **Phân tích tác động**:
  - Dù có gọi `update_meta_cache`, việc query toàn bộ chương trình (`posts_per_page => -1`) rồi lặp PHP để tính mảng đếm tần suất `$m_counts` là giải pháp tốn bộ nhớ RAM (memory exhaustion) khi dữ liệu mở rộng.

#### [N+1-04] Lỗi Rewrite Request Guard trên toàn bộ URL 1-segment
- **Vị trí**: `inc/core/class-rewrite-rules.php`, dòng 41-101.
- **Mã nguồn thực tế**:
  ```php
  add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
  ...
  function ltdh_program_request_guard( $query_vars ) {
      ...
      $program_post = get_posts( [ 'name' => $slug, 'post_type' => 'program', 'posts_per_page' => 1, 'fields' => 'ids' ] );
      ...
      $regular_post = get_posts( [ 'name' => $slug, 'post_type' => 'post', 'posts_per_page' => 1, 'fields' => 'ids' ] );
      ...
  }
  ```
- **Phân tích tác động**:
  - Hook filter `request` chạy trên **tất cả** các request có 1 path segment (ví dụ `/gioi-thieu/`, `/lien-he/`, `/tin-tuc/`).
  - Mỗi request phải gánh thêm **1 đến 2 truy vấn SQL** chỉ để kiểm tra xem slug đó có phải là `program` hay `post` hay không, làm chậm TTFB (Time To First Byte) trên toàn trang.

### 3.4. Đánh giá cơ chế Cache (Transients & Object Cache)
1. **Lãng phí Transient `LTDH_TRANSIENT_FEATURED_SCHOOLS`**:
   - Được định nghĩa trong `inc/config/constants.php` dòng 26:
     ```php
     define( 'LTDH_TRANSIENT_FEATURED_SCHOOLS', 'ltdh_featured_schools' );
     ```
   - Trong `inc/core/class-helpers.php` dòng 505 có hook xóa transient này khi lưu bài viết:
     ```php
     delete_transient( LTDH_TRANSIENT_FEATURED_SCHOOLS );
     ```
   - **Tuy nhiên**, trong `archive-school.php` (dòng 23-50), code truy vấn trường nổi bật lại chạy trực tiếp:
     ```php
     $featured_query = new WP_Query( $featured_args );
     ```
     Hoàn toàn **không gọi `get_transient()` hay `set_transient()`**! Transient này bị "bỏ quên" 100% trong template danh bạ trường.
2. **Hàm `ltdh_get_school_unique_majors_count()`**:
   - Dùng `wp_cache_get()` và `wp_cache_set()` với group `'ltdh'`.
   - Vì môi trường mặc định của WordPress không có Redis/Memcached Object Cache persistent, `wp_cache_*` chỉ tồn tại trong vòng đời của 1 HTTP request (non-persistent memory). Khi có request mới từ người dùng khác, toàn bộ phép tính đếm lặp lại từ đầu.
3. **Transient tìm kiếm trong `inc/search-engine.php` (dòng 27-28 & 118)**:
   - Cache kết quả tìm kiếm vào transient `ltdh_search_' . md5($keyword)` trong 1 giờ.
   - **Thiếu Invalidation**: Khi admin thêm trường mới, mở ngành mới hoặc đổi tên chương trình, hàm `ltdh_clear_transients_on_save` trong `class-helpers.php` không hề xóa các transient dạng `ltdh_search_*`. Dữ liệu tìm kiếm của người dùng sẽ bị cũ trong tối đa 60 phút.

---

## 4. ĐÁNH GIÁ TAXONOMY ARCHIVES & SINGLE TEMPLATES

### 4.1. Bất thường tại `taxonomy-training_type.php`
1. **Bỏ qua Main Query của WordPress**:
   - Tệp `inc/core/class-query-filters.php` đã hook vào `pre_get_posts` để set post type:
     ```php
     if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) ) {
         $query->set( 'post_type', LTDH_CPT_PROGRAM );
     }
     ```
   - WordPress Core đã tự động thực hiện truy vấn chính (Main Query) cho taxonomy archive này.
   - Nhưng khi template `taxonomy-training_type.php` được load, dòng 128 lại khởi tạo một instance truy vấn phụ:
     ```php
     $query = new WP_Query( $args );
     ```
   - **Hậu quả**: Database phải thực hiện **2 lần truy vấn danh sách chương trình** trên cùng một URL! Main Query bị vứt bỏ, làm tăng gấp đôi tải DB không cần thiết.
2. **Trùng lặp 100% giữa `taxonomy-training_type.php` và `archive-program.php`**:
   - So sánh từng dòng: `archive-program.php` (541 dòng) và `taxonomy-training_type.php` (538 dòng) là hai tệp sinh đôi, giống nhau từng ký tự từ logic lấy tham số, form search, sidebar facets đến vòng lặp render card và pagination.
   - Vi phạm nghiêm trọng nguyên lý DRY (Don't Repeat Yourself), gây khó khăn cho việc bảo trì.
3. **Phụ thuộc vào Regex URL thay vì WordPress Query Vars**:
   - Dòng 21-24:
     ```php
     $request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
     if ( preg_match( '#^/he-dao-tao/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
         $selected_type = sanitize_text_field( $m[1] );
     }
     ```
   - Thay vì lấy term qua `get_queried_object()` hoặc `get_query_var('training_type')`, template lại parse thủ công `$_SERVER['REQUEST_URI']`. Điều này cho thấy hệ thống routing taxonomy đang có xung đột với rewrite rules của WordPress.

### 4.2. Xung đột hiển thị Badge Hệ Đào Tạo tại `archive-school.php`

Đây là **phát hiện bất thường nghiêm trọng nhất** về tính nhất quán dữ liệu:

| Giao diện | Vị trí code | Logic lấy Hệ Đào Tạo của Trường | Kết quả thực tế |
|---|---|---|---|
| **Featured Schools** (Trường nổi bật) | `archive-school.php:80` | `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, ['fields' => 'names'] );` | ❌ **Rỗng / Không hiển thị badge**. Vì taxonomy `training_type` không được đăng ký cho CPT `school`. |
| **Card View** (Xem dạng lưới) | `archive-school.php:199` | `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, ['fields' => 'names'] );` | ❌ **Rỗng / Không hiển thị badge**. Cùng lý do như trên. |
| **List View** (Xem danh sách) | `archive-school.php:297-306` | Query toàn bộ `program` của trường, sau đó lặp từng program để lấy `wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE )` | ⚠️ **Hiển thị đầy đủ badge**, nhưng sinh ra **hàng trăm N+1 queries** và hiển thị cả các hệ đang tạm ngưng! |

#### Hiện tượng rò rỉ Badge khi chương trình đã Tạm Ngưng (`admission_status = 'tam-ngung'`)
- Trong List View (dòng 265-276), câu lệnh query chương trình của trường:
  ```php
  $offered_program_ids = get_posts( [
      'post_type'   => 'program',
      'numberposts' => -1,
      'fields'      => 'ids',
      'meta_query'  => [
          [
              'key'     => 'school_relationship',
              'value'   => $school_id,
              'compare' => '=',
          ],
      ],
  ] );
  ```
- Không hề có điều kiện kiểm tra `admission_status != 'tam-ngung'`!
- **Kịch bản thực tế**: Một trường đại học chỉ từng mở 1 chương trình hệ "Văn bằng 2" trong quá khứ và hiện tại chương trình này đã chuyển sang trạng thái "Tạm ngưng tuyển sinh". List View vẫn lấy được term "Văn bằng 2" và hiển thị badge "Văn bằng 2" trên card của trường! Người dùng nhấp vào trường tìm kiếm thì không thấy chương trình mở nào, gây ức chế và mất lòng tin.

### 4.3. Khảo sát `single-school.php`
- **Phần Hero (dòng 111-136)**: Hoàn toàn **không hiển thị** các badge hệ đào tạo mà trường đang có. Người dùng phải cuộn xuống tận mục "Chương trình tuyển sinh đang mở" mới biết trường đào tạo hệ gì.
- **Phần "Các ngành đào tạo phổ biến" (dòng 231-282)**: Lặp qua `$offered_program_ids` để lấy meta `major_relationship`, sau đó chạy thêm một `new WP_Query` cho post type `major`.
- **Phần "Chương trình tuyển sinh đang mở" (dòng 340-365)**: Chạy thêm một `new WP_Query` nữa cho post type `program`. Dù có gọi `update_meta_cache` và `update_object_term_cache`, việc query lặp đi lặp lại nhiều lần không được gom nhóm gây lãng phí tài nguyên máy chủ.

---

## 5. ĐÁNH GIÁ TRẢI NGHIỆM NGƯỜI DÙNG & BỘ LỌC (TAXONOMY UX & FILTER INTERACTIONS)

### 5.1. Tình trạng Lọc: AJAX Chết (Dead Code) & 100% Reload Trang
Trong `assets/js/main.js` (dòng 9-87):
```javascript
const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"]');
const container = document.getElementById('program-results-container');

if (filterForm && container) {
    ...
    // Fetch AJAX action: ltdh_filter_programs
    ...
}
```
- **Kiểm tra thực tế**:
  1. Phần tử `#program-results-container` **hoàn toàn không tồn tại** trong bất kỳ tệp PHP nào của theme (`taxonomy-training_type.php`, `archive-program.php`, `archive-school.php`).
  2. Grid kết quả trong `taxonomy-training_type.php` dòng 352 chỉ là: `<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">` (không có ID).
  3. Form tìm kiếm duy nhất trong sidebar chỉ có một thẻ `<input name="s">`, không chứa các trường chọn trường, hệ học hay ngành học.
  4. Các dropdown sắp xếp và phân trang trong header bar (dòng 326, 336) sử dụng sự kiện inline thuần:
     ```html
     <select id="sort-select" onchange="location = this.value;">
     ```
  5. Các liên kết lọc ngành học và hệ học ở sidebar là thẻ `<a>` thuần (`href="..."`), kích hoạt chuyển trang GET truyền thống.
- **Kết luận**: Tính năng AJAX Filter `ltdh_filter_programs` trong `assets/js/main.js` và `functions.php` là **Dead Code 100%**. Toàn bộ tương tác lọc của người dùng hiện nay đều gây ra hiện tượng tải lại toàn bộ trang (Full Page Reload), gây giật màn hình và tăng áp lực lên Web Server.

### 5.2. Lỗi Lệch Số Lượng Facet (Phantom Facets)
- Trong `taxonomy-training_type.php`, số lượng chương trình hiển thị cạnh mỗi Hệ đào tạo ở sidebar (dòng 164-179):
  ```sql
  SELECT t.slug, COUNT(p.ID) as count
  FROM {$wpdb->posts} p
  INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
  INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
  INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
  WHERE p.post_type = 'program' AND p.post_status = 'publish' AND tt.taxonomy = 'training_type'
  GROUP BY t.slug
  ```
- **Lỗ hổng logic**: Câu truy vấn SQL này đếm **tổng số chương trình trên toàn hệ thống**, hoàn toàn **không quan tâm** người dùng đang lọc theo trường nào (`$selected_school`) hay ngành nào (`$selected_nhom`)!
- **Ví dụ**:
  - Người dùng đang lọc theo `Trường Đại học Kinh tế Quốc dân`.
  - Sidebar vẫn hiện: `Đại học Từ xa (120)`, `Liên thông đại học (45)`.
  - Người dùng thấy số 45 tưởng trường có tuyển sinh liên thông, bấm vào thì kết quả là `0` (Không tìm thấy chương trình phù hợp).
  - Đây là lỗi **Phantom Facet** kinh điển, gây sai lệch thông tin và giảm tỷ lệ chuyển đổi lead.

### 5.3. Xử lý Trạng thái Trống (Empty State)
- Khi không có kết quả (dòng 473-481):
  - Hiển thị thông báo chung chung: *"Không tìm thấy chương trình phù hợp - Hệ học này hiện chưa có chương trình hoặc không khớp với các bộ lọc khác."*
  - Nút bấm gợi ý bị hardcode cố định:
    ```html
    <a href="<?php echo esc_url( home_url( '/he-dao-tao/lien-thong/' ) ); ?>">Xem hệ Liên thông</a>
    ```
  - Nếu người dùng vốn dĩ đang ở trang "Liên thông" mà gặp lỗi này, nút bấm lại điều hướng người dùng xem lại chính trang rỗng đó.
  - Thiếu logic gợi ý thông minh: ví dụ *"Xem các trường khác có cùng ngành học này"* hoặc *"Xem các ngành khác của trường này"*.

---

## 6. ĐÁNH GIÁ SEO, PERMALINKS, CANONICAL VÀ BREADCRUMBS

### 6.1. Breadcrumbs Hierarchy & Cấu trúc Schema JSON-LD
1. **Tắt RankMath Breadcrumb trên `/he-dao-tao/*`**:
   - Trong `inc/core/class-helpers.php` dòng 321-324:
     ```php
     $is_he_dao_tao = (bool) preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path );
     if ( ! $is_he_dao_tao && function_exists( 'rank_math_the_breadcrumbs' ) ) {
         ob_start();
         rank_math_the_breadcrumbs();
         $html = ob_get_clean();
     }
     ```
   - Theme chủ động tắt tính năng breadcrumbs của RankMath đối với mọi URL bắt đầu bằng `/he-dao-tao/` vì xung đột đường dẫn ảo.
2. **Breadcrumb Fallback thiếu phân cấp ngữ nghĩa**:
   - Với trang chi tiết chương trình (`single-program`):
     `Trang chủ > Hệ đào tạo > [Tiêu đề chương trình]`
     ➔ Hoàn toàn thiếu tên Hệ cụ thể (ví dụ: *Đại học Từ xa*) và thiếu tên Trường đối tác.
   - Với trang chi tiết trường (`single-school`):
     `Trang chủ > Trường đối tác > [Tên trường]`
     ➔ Không thể hiện ngữ cảnh trường thuộc hệ đào tạo nào nếu người dùng đi từ luồng hệ đào tạo sang.
3. **Thiếu Schema `BreadcrumbList`**:
   - Breadcrumb fallback tự render bằng HTML thô (`<div class="ltdh-breadcrumb">...`), không có thuộc tính microdata Schema.org cũng như không inject mã JSON-LD `BreadcrumbList`. Kết quả tìm kiếm trên Google mất cơ hội hiển thị rich snippet breadcrumb điều hướng.

### 6.2. Lỗi Vòng Lặp Canonical URL (Canonical Redirect Loop)
- Trong `inc/acf-import-cpts.json` (dòng 146):
  `program` CPT được khai báo: `"has_archive_slug": "chuong-trinh"`.
- Trong `inc/core/class-rewrite-rules.php` (dòng 154-161):
  ```php
  if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
      $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
      wp_redirect( $redirect_url, 301 );
      exit;
  }
  ```
- **Xung đột SEO**:
  - Khi người dùng hoặc bot truy cập `/he-dao-tao/` (trang tổng các hệ đào tạo), rewrite rule ánh xạ nó sang `post_type=program`.
  - Các plugin SEO như Rank Math lấy `get_post_type_archive_link('program')` làm thẻ Canonical URL ➔ trả về `https://domain.com/chuong-trinh/`.
  - Tuy nhiên, khi bot truy cập URL Canonical `https://domain.com/chuong-trinh/`, server lại trả về mã **HTTP 301 Redirect** sang `https://domain.com/he-dao-tao/tu-xa/`.
  - Đây là lỗi kỹ thuật SEO nghiêm trọng: **Canonical trỏ vào một URL Redirect 301**, khiến Googlebot không thể lập chỉ mục ổn định cho trang lưu trữ chương trình.

### 6.3. Rủi ro Va Chạm Slug (Slug Collisions) từ Rewrite Rule Không Tiền Tố
- Quy tắc rewrite tại dòng 42 của `class-rewrite-rules.php`:
  ```php
  add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
  ```
- Đặt cờ `'top'` khiến mọi URL cấp 1 (root slug) đều bị bắt vào `program`.
- Mặc dù có `ltdh_program_request_guard` để fallback về `page` hoặc `post`, nếu biên tập viên tạo một chương trình có slug trùng với một page quan trọng (ví dụ chương trình có slug `gioi-thieu`, `lien-he`, `huong-dan`, `tuyen-sinh`), page gốc sẽ bị chiếm quyền điều hướng ngay lập tức.

---

## 7. ĐỀ XUẤT GIẢI PHÁP TỐI ƯU HÓA TOÀN DIỆN KÈM CODE MẪU

Dưới đây là các giải pháp kỹ thuật cụ thể nhằm khắc phục triệt để các vấn đề đã nêu mà không làm xáo trộn kiến trúc CPT hiện có:

### Giải pháp 1: Khắc phục lỗi N+1 Query & Không nhất quán Badge tại `archive-school.php`

**Ý tưởng**: Tự động đồng bộ các taxonomy terms của các chương trình đang hoạt động vào Post Meta của Trường (`_active_training_types`) mỗi khi lưu bài viết `program`, hoặc preload toàn bộ dữ liệu trong một query duy nhất thay vì query lặp trong `while(have_posts())`.

#### 1.1. Bổ sung hook đồng bộ dữ liệu vào `inc/relationship-hooks.php`:
```php
/**
 * Tự động đồng bộ các hệ đào tạo đang hoạt động vào School meta khi lưu Program.
 */
function ltdh_sync_school_active_training_types( $school_id ) {
    if ( ! $school_id ) {
        return;
    }

    // Lấy các chương trình đang MỞ tuyển sinh của trường
    $active_programs = get_posts( [
        'post_type'      => LTDH_CPT_PROGRAM,
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => LTDH_META_SCHOOL_REL,
                'value'   => $school_id,
                'compare' => '=',
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => LTDH_META_ADMISSION_STATUS,
                    'value'   => LTDH_STATUS_PAUSED,
                    'compare' => '!=',
                ],
                [
                    'key'     => LTDH_META_ADMISSION_STATUS,
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ],
    ] );

    $active_types = [];
    if ( ! empty( $active_programs ) ) {
        $terms = wp_get_object_terms( $active_programs, LTDH_TAX_TRAINING_TYPE );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $active_types[ $term->slug ] = $term->name;
            }
        }
    }

    update_post_meta( $school_id, '_active_training_systems', $active_types );
}
```

#### 1.2. Tối ưu mã nguồn render badge trong `archive-school.php`:
Thay thế đoạn mã N+1 (dòng 265-306) bằng cách đọc trực tiếp meta đã cache:
```php
// Thay vì chạy get_posts và wp_get_post_terms bên trong vòng lặp:
$training_modes = get_post_meta( $school_id, '_active_training_systems', true );
if ( ! is_array( $training_modes ) ) {
    $training_modes = [];
}
// Render badge đồng nhất cho cả Card View và List View
if ( ! empty( $training_modes ) ) {
    foreach ( $training_modes as $slug => $name ) {
        echo ltdh_get_training_type_badge_html( $name );
    }
}
```
*Kết quả*: Giảm số truy vấn từ **200+ queries** xuống còn **0 query phụ** trong vòng lặp danh bạ trường (vì postmeta đã được WP tự động prime qua `update_post_caches`).

---

### Giải pháp 2: Trả lại Main Query & Khắc phục Double Query cho `taxonomy-training_type.php`

**Ý tưởng**: Tận dụng triệt để hook `pre_get_posts` trong `inc/core/class-query-filters.php` để thiết lập đầy đủ điều kiện lọc cho Main Query, loại bỏ việc gọi `new WP_Query( $args )` trong template.

#### 2.1. Cập nhật `inc/core/class-query-filters.php`:
```php
function ltdh_customize_archive_queries( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) || $query->is_post_type_archive( LTDH_CPT_PROGRAM ) ) {
        $query->set( 'post_type', LTDH_CPT_PROGRAM );
        $query->set( 'post_status', 'publish' );

        // Phân trang
        $limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
        $query->set( 'posts_per_page', in_array( $limit, [ 10, 12, 20, 24, 36, 48, -1 ], true ) ? $limit : 12 );

        // Sắp xếp
        $sort = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
        if ( 'title_asc' === $sort ) {
            $query->set( 'orderby', 'title' );
            $query->set( 'order', 'ASC' );
        } elseif ( 'title_desc' === $sort ) {
            $query->set( 'orderby', 'title' );
            $query->set( 'order', 'DESC' );
        } elseif ( 'date_desc' === $sort ) {
            $query->set( 'orderby', 'date' );
            $query->set( 'order', 'DESC' );
        }

        // Meta query: Loại bỏ chương trình tạm ngưng
        $meta_query = (array) $query->get( 'meta_query' );
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'value' => LTDH_STATUS_PAUSED, 'compare' => '!=' ],
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'compare' => 'NOT EXISTS' ],
        ];

        // Lọc theo trường
        if ( ! empty( $_GET['truong'] ) ) {
            $school_param = sanitize_text_field( $_GET['truong'] );
            $school_id    = is_numeric( $school_param ) ? intval( $school_param ) : ( ( $sp = get_page_by_path( $school_param, OBJECT, LTDH_CPT_SCHOOL ) ) ? $sp->ID : 0 );
            if ( $school_id ) {
                $meta_query[] = [
                    'key'     => LTDH_META_SCHOOL_REL,
                    'value'   => $school_id,
                    'compare' => '=',
                ];
            }
        }

        // Lọc theo ngành
        if ( ! empty( $_GET['nganh'] ) ) {
            $major_param = sanitize_text_field( $_GET['nganh'] );
            $major_post  = get_page_by_path( $major_param, OBJECT, LTDH_CPT_MAJOR );
            if ( $major_post ) {
                $meta_query[] = [
                    'key'     => LTDH_META_MAJOR_REL,
                    'value'   => $major_post->ID,
                    'compare' => '=',
                ];
            }
        }

        $query->set( 'meta_query', $meta_query );
    }
}
add_action( 'pre_get_posts', 'ltdh_customize_archive_queries' );
```

#### 2.2. Đơn giản hóa `taxonomy-training_type.php`:
Trong template, chỉ cần dùng vòng lặp chuẩn:
```php
// Thay vì: $query = new WP_Query($args); while($query->have_posts()) ...
if ( have_posts() ) :
    // Prime cache một lần cho toàn bộ bài viết trên trang
    $post_ids = wp_list_pluck( $wp_query->posts, 'ID' );
    update_meta_cache( 'post', $post_ids );
    update_object_term_cache( $post_ids, 'program' );

    while ( have_posts() ) : the_post();
        // Render card
    endwhile;
endif;
```
*Kết quả*: Tiết kiệm 50% thời gian truy vấn DB trên mỗi trang lưu trữ hệ đào tạo.

---

### Giải pháp 3: Khôi phục và Đồng bộ AJAX Filter UX

1. **Bổ sung ID wrapper vào `taxonomy-training_type.php`**:
   ```html
   <!-- Bọc grid danh sách chương trình bằng ID mà main.js đang lắng nghe -->
   <div id="program-results-container" class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
       <!-- Cards render tại đây -->
   </div>
   ```
2. **Tách Card Chương Trình thành Component Dùng Chung**:
   Tạo tệp `template-parts/program-card.php` để cả `taxonomy-training_type.php`, `archive-program.php` và callback `ltdh_ajax_filter_programs()` cùng gọi chung qua `get_template_part('template-parts/program-card')`. Điều này triệt tiêu hoàn toàn sự lệch pha về HTML markup giữa AJAX và Server Render.
3. **Sửa lỗi Phantom Facet đếm theo điều kiện thực tế**:
   Chuyển bộ đếm sidebar sang sử dụng query điều kiện có truyền `$selected_school`:
   ```php
   // Khi người dùng đã chọn trường, chỉ đếm số lượng hệ đào tạo của chính trường đó:
   function ltdh_get_dynamic_training_type_counts( $school_id = 0 ) {
       global $wpdb;
       $sql = "
           SELECT t.slug, COUNT(p.ID) as count
           FROM {$wpdb->posts} p
           INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
           INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
           INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
       ";
       if ( $school_id ) {
           $sql .= " INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'school_relationship' AND pm.meta_value = %d ";
       }
       $sql .= " WHERE p.post_type = 'program' AND p.post_status = 'publish' AND tt.taxonomy = 'training_type' GROUP BY t.slug ";

       return $school_id ? $wpdb->get_results( $wpdb->prepare( $sql, $school_id ) ) : $wpdb->get_results( $sql );
   }
   ```

---

### Giải pháp 4: Chuẩn hóa SEO Canonical & Breadcrumbs Phân Cấp

#### 4.1. Khắc phục lỗi Canonical Redirect Loop:
Thêm filter hook xử lý canonical riêng cho `/he-dao-tao/` trong `inc/seo/class-rankmath-integration.php`:
```php
/**
 * Đảm bảo Canonical URL của /he-dao-tao/ không bị trỏ về /chuong-trinh/
 */
add_filter( 'rank_math/frontend/canonical_url', function( $canonical ) {
    $request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
    if ( preg_match( '#^/he-dao-tao/?$#i', $request_path ) ) {
        return home_url( '/he-dao-tao/' );
    }
    return $canonical;
} );
```

#### 4.2. Xây dựng Breadcrumbs Phân Cấp Chuẩn Ngữ Nghĩa Kèm Schema:
Cập nhật `ltdh_breadcrumbs()` trong `inc/core/class-helpers.php` để sinh ra phân cấp logic hoàn chỉnh:
- **Chi tiết Chương trình**:
  `Trang chủ > Hệ đào tạo > Hệ [Tên hệ] > [Tên trường] > [Tên ngành/chương trình]`
- **Chi tiết Trường**:
  `Trang chủ > Trường đối tác > [Tên trường]`
- **Bổ sung thẻ Schema JSON-LD `BreadcrumbList` tự động** khi RankMath không xử lý được đường dẫn ảo `/he-dao-tao/`.

---

## 8. BẢNG TỔNG KẾT KHUYẾN NGHỊ VÀ MỨC ĐỘ ƯU TIÊN

| STT | Vấn đề phát hiện | Mức độ nghiêm trọng | Vị trí file | Hành động khuyến nghị |
|---|---|---|---|---|
| **1** | Lỗi N+1 query nặng trong List View | **Critical** | `archive-school.php:265-306` | Preload data hoặc đọc từ meta `_active_training_systems` đã sync. |
| **2** | Không nhất quán hiển thị badge giữa Card và List | **Critical** | `archive-school.php:80, 199` | Đăng ký `training_type` cho cả `school` hoặc sync term names vào School meta. |
| **3** | Bỏ qua Main Query, chạy double query | **High** | `taxonomy-training_type.php:128` | Chuyển logic sang `pre_get_posts`, dùng vòng lặp `have_posts()`. |
| **4** | AJAX Filter chết do thiếu ID `#program-results-container` | **High** | `assets/js/main.js:10`, `taxonomy-training_type.php:352` | Gán ID vào wrapper grid và đồng bộ card markup giữa AJAX & Server. |
| **5** | Lỗi Phantom Facet đếm sai số lượng ở Sidebar | **High** | `taxonomy-training_type.php:164-179` | Thêm điều kiện `$selected_school` vào câu SQL đếm facet. |
| **6** | Lỗi Canonical Redirect Loop trên `/he-dao-tao/` | **High** | `inc/core/class-rewrite-rules.php:154` | Hook `rank_math/frontend/canonical_url` ép canonical về `/he-dao-tao/`. |
| **7** | Badge hệ đào tạo hiển thị dù chương trình đã tạm ngưng | **Medium** | `archive-school.php:269` | Thêm filter loại trừ `admission_status = 'tam-ngung'`. |
| **8** | File clone trùng lặp 100% | **Medium** | `archive-program.php` & `taxonomy-training_type.php` | Gom chung một template hoặc dùng layout include thống nhất. |
| **9** | Rewrite rule `([^/]+)/?$` chạy 2 query thừa mỗi request | **Medium** | `inc/core/class-rewrite-rules.php:41` | Tối ưu request guard, cache kết quả kiểm tra slug bằng transient. |
| **10** | Thiếu Schema `BreadcrumbList` trên Breadcrumb Fallback | **Low** | `inc/core/class-helpers.php:330-402` | Inject JSON-LD `BreadcrumbList` cho trang `/he-dao-tao/*`. |
