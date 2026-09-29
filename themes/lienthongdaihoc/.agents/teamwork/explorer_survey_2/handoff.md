# Báo cáo Khảo sát An ninh & Tối ưu Hóa Truy vấn Cơ sở dữ liệu (Security & DB Query Audit Report)

**Auditor:** teamwork_preview_explorer_survey_2 (Role: Security & DB Query Surveyor)  
**Target Theme:** Liên Thông Đại Học (`lienthongdaihoc`)  
**Workspace:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`  
**Report Date:** 2026-09-25  
**Evaluation Mode:** Read-only Source Code Audit & Static Analysis  

---

## 1. Observation (Các quan sát và Bằng chứng thực nghiệm)

Toàn bộ 49 file `.php` trong theme đã được quét và phân tích tĩnh chuyên sâu. Dưới đây là bằng chứng thực tế được trích xuất trực tiếp từ mã nguồn.

### 1.1. Checklist Bảo vệ Truy cập Trực tiếp (Direct File Access Guards)

- **Tổng số file PHP trong theme:** 49 file (48 file giao diện/logic + 1 file script test).
- **Số file tuân thủ guard (`defined('ABSPATH') || exit;`):** 46 file.
- **Số file vi phạm hoặc thiếu bảo vệ:** 3 file:
  1. `header.php:1` — Hoàn toàn không có guard `defined('ABSPATH') || exit;`. Bắt đầu trực tiếp bằng thẻ HTML:
     ```php
     1: <!DOCTYPE html>
     2: <html <?php language_attributes(); ?>>
     ```
  2. `inc/search-engine.php:1-7` — Hoàn toàn không có guard `defined('ABSPATH') || exit;`. Bắt đầu trực tiếp bằng logic hook:
     ```php
     1: <?php
     2: /**
     3:  * Program Search Engine Filters and Logic
     ...
     7: add_filter( 'pre_get_posts_args_ltdh', 'ltdh_filter_program_search_query' );
     ```
     *Hậu quả:* Khi truy cập trực tiếp qua HTTP URL `/wp-content/themes/lienthongdaihoc/inc/search-engine.php`, PHP ném lỗi nghiêm trọng `Fatal error: Uncaught Error: Call to undefined function add_filter()`, để lộ đường dẫn file vật lý (Full Path Disclosure) và phiên bản máy chủ.
  3. `tests/run-tests.php:1-14` — Tự động boot WordPress thông qua nạp file core `wp-load.php` mà không kiểm tra môi trường thực thi CLI:
     ```php
     8: $wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
     9: if ( ! file_exists( $wp_load_path ) ) {
     10: 	die( "Error: wp-load.php not found at $wp_load_path\n" );
     11: }
     12: define( 'WP_USE_THEMES', false );
     13: require_once $wp_load_path;
     ```
     *Hậu quả:* Không có kiểm tra `php_sapi_name() === 'cli'` hoặc `current_user_can('manage_options')`. Bất kỳ người dùng ẩn danh nào truy cập đường dẫn web `/wp-content/themes/lienthongdaihoc/tests/run-tests.php` đều kích hoạt quá trình tự động thêm bài viết test (Post, Trường, Ngành), can thiệp taxonomy và xóa dữ liệu fixture trong database thật.

---

### 1.2. Danh mục Toàn bộ Truy vấn `$wpdb` & Đánh giá Nguy cơ SQL Injection

Hệ thống có tổng cộng 22 vị trí gọi `$wpdb` tập trung ở 5 file:

| STT | File & Dòng | Phương thức gọi `$wpdb` | Nội dung câu lệnh / Mục đích | Đánh giá Nguy cơ SQLi |
|---|---|---|---|---|
| 1 | `inc/lead-capture.php:40` | `dbDelta( $sql )` | Tạo bảng `wp_ltdh_leads` khi active theme | **An toàn** (Schema tĩnh) |
| 2 | `inc/lead-capture.php:125-143` | `$wpdb->insert( $table_name, [...], [formats] )` | Thêm mới lead vào CSDL | **An toàn** (Có format mapping `%s`, `%d`) |
| 3 | `inc/eligibility.php:631-650` | `$wpdb->insert( $table, [...], [formats] )` | Lưu lượt kiểm tra điều kiện vào bảng `wp_ltdh_eligibility_checks` | **An toàn** (Có format mapping) |
| 4 | `inc/eligibility.php:697-702` | `$wpdb->update( $table, [...], ['id' => $check_id] )` | Đánh dấu lead đã capture trong bảng checks | **An toàn** (Sử dụng API update chuẩn) |
| 5 | `inc/eligibility.php:709-722` | `$wpdb->insert( $table, [...] )` | Fallback thêm lead mới | **An toàn** (Prepared bởi insert API) |
| 6 | `inc/eligibility.php:763` | `$wpdb->get_row( $wpdb->prepare(...) )` | Lấy chi tiết lượt kiểm tra theo ID | **An toàn** (Prepared với `%d`) |
| 7 | `inc/eligibility.php:791-802` | `$wpdb->insert( $table, [...] )` | Fallback tạo lead từ AJAX | **An toàn** |
| 8 | `inc/eligibility.php:808-812` | `$wpdb->update( $table, [...], ['id' => $check_id] )` | Cập nhật trạng thái lead trong checks | **An toàn** |
| 9 | `inc/eligibility.php:853` | `$wpdb->get_row( $wpdb->prepare(...) )` | Lấy dữ liệu lead theo `lead_id` | **An toàn** (Prepared với `%d`) |
| 10 | `inc/eligibility.php:883-890` | `$wpdb->update( $table, [...], ['id' => $lead_id] )` | Cập nhật ghi chú và file bằng cấp | **An toàn** về SQLi, nhưng có lỗi IDOR (xem 1.3) |
| 11 | `inc/eligibility.php:1068` | `$wpdb->delete( $table, ['id' => intval($_GET['lead_id'])] )` | Xóa đơn lẻ lead trong trang quản trị | **An toàn** (Ép kiểu `intval`) |
| 12 | `inc/eligibility.php:1083` | `$wpdb->query( $wpdb->prepare(...) )` | Xóa hàng loạt lead | **An toàn** (Tạo mảng placeholder `%d`) |
| 13 | `inc/eligibility.php:1129-1131` | `$wpdb->prepare( $count_query, $where_params )` | Đếm tổng số lead theo bộ lọc | **An toàn** (Sử dụng `$wpdb->esc_like()`) |
| 14 | `inc/eligibility.php:1145` | `$wpdb->prepare( $query, $query_params )` | Truy vấn phân trang danh sách lead | **An toàn** (Placeholders đầy đủ) |
| 15 | `inc/eligibility.php:1373` | `$wpdb->query( "TRUNCATE TABLE $table" )` | Xóa sạch toàn bộ log lượt kiểm tra | **An toàn** về SQLi (Tên bảng hardcoded, có kiểm tra nonce và quyền admin) |
| 16 | `inc/eligibility.php:1388` | `$wpdb->query( $wpdb->prepare(...) )` | Xóa hàng loạt lượt kiểm tra | **An toàn** (Placeholders `%d`) |
| 17 | `inc/eligibility.php:1440-1442` | `$wpdb->prepare( $count_query, $where_params )` | Đếm tổng lượt kiểm tra | **An toàn** |
| 18 | `inc/eligibility.php:1456` | `$wpdb->prepare( $query, $query_params )` | Truy vấn danh sách lượt kiểm tra | **An toàn** |
| 19 | `inc/crm-adapters.php:47-52` | `$wpdb->get_results(...)` | Lấy danh sách lead chờ đồng bộ CRM (LIMIT 10) | **An toàn** (SQL tĩnh, không có tham số ngoài) |
| 20 | `inc/crm-adapters.php:60, 65, 75` | `$wpdb->update(...)` | Cập nhật trạng thái đồng bộ CRM | **An toàn** |
| 21 | `archive-program.php:166-174` | `$wpdb->get_results(...)` | Đếm số lượng chương trình theo hệ đào tạo | **An toàn về SQLi** (SQL tĩnh), **Vấn đề Hiệu năng:** Query SQL sống trực tiếp trong template không cache! |
| 22 | `taxonomy-training_type.php:164-172` | `$wpdb->get_results(...)` | Đếm số lượng chương trình theo hệ đào tạo | **An toàn về SQLi** (SQL tĩnh), **Vấn đề Hiệu năng:** Trùng lặp query template không cache! |

---

### 1.3. Lỗ hổng Xác thực, CSRF Nonce & Kiểm soát Dữ liệu Nhập

#### (A) Lỗ hổng Tải lên File Trực tiếp Không Ràng buộc Mime-Type (Unrestricted File Upload)
- **Vị trí 1:** `inc/eligibility.php:204-213` (trong AJAX handler `ltdh_elig_ajax_check`):
  ```php
  204: if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
  205:     require_once( ABSPATH . 'wp-admin/includes/file.php' );
  206:     $uploadedfile = $_FILES['degree_file'];
  207:     $upload_overrides = array( 'test_form' => false );
  208:     $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
  ```
- **Vị trí 2:** `inc/eligibility.php:835-845` (trong AJAX handler `ltdh_elig_ajax_advanced_verify`):
  ```php
  835: if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
  836:     require_once( ABSPATH . 'wp-admin/includes/file.php' );
  837:     $uploadedfile = $_FILES['degree_file'];
  838:     $upload_overrides = array( 'test_form' => false );
  839:     $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
  ```
- **Rủi ro:** `$upload_overrides` không định nghĩa danh sách mime-types cho phép (`'mimes' => ['jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf']`), không giới hạn dung lượng file tối đa (max file size). Mặc dù WordPress lọc các file `.php` cơ bản, nhưng người dùng nặc danh (endpoint `wp_ajax_nopriv_`) có thể tải lên các định dạng nguy hại như `.html`, `.svg` (chứa stored XSS payload), `.phar`, hoặc file dung lượng cực lớn làm tràn bộ nhớ server.

#### (B) Lỗ hổng IDOR (Insecure Direct Object Reference) trong AJAX Xác minh Nâng cao
- **Vị trí:** `inc/eligibility.php:824-855`
  ```php
  824: $lead_id = intval( $_POST['lead_id'] ?? 0 );
  ...
  853: $lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_leads WHERE id = %d", $lead_id ) );
  ...
  883: $wpdb->update(
  884:     $wpdb->prefix . 'ltdh_leads',
  885:     [ 'referral_source' => $ref_source, 'error_message' => $current_msg ],
  886:     [ 'id' => $lead_id ]
  887: );
  ```
- **Rủi ro:** Endpoint này mở cho khách vãng lai (`nopriv`). Một kẻ tấn công có thể gửi yêu cầu với bất kỳ ID nào (`lead_id=1, 2, 3...`) kèm file hoặc thông tin giả mạo để ghi đè ghi chú và lịch sử khảo sát của khách hàng khác trong CSDL và kích hoạt bot Telegram bắn tin nhắn rác với dữ liệu của nạn nhân.

#### (C) Thiếu Kiểm tra CSRF Nonce trên Form Gửi Dữ liệu & AJAX
- **Vị trí 1 (AJAX Filter):** `functions.php:69-75` (`ltdh_ajax_filter_programs`):
  Hoàn toàn không có `check_ajax_referer()` xác thực token nguồn gốc yêu cầu.
- **Vị trí 2 (Native Form Submit):** `inc/lead-capture.php:333-345` (`ltdh_handle_native_form_submit`):
  Hooked vào `template_redirect`, xử lý `$_POST['your-name']`, nhưng không có `wp_verify_nonce()` hay `check_admin_referer()`.
- **Vị trí 3 (Form Generator & Template):** 
  - `inc/core/class-helpers.php:186-198` (`ltdh_render_native_form`): Form không chèn `wp_nonce_field()`.
  - `single-guide.php:60-75`: Hardcoded HTML form gửi trực tiếp mà không có trường nonce.

---

### 1.4. Đánh giá Render Đầu ra & Nguy cơ XSS

- Toàn bộ các giá trị từ database và tham số URL đã được lọc qua `sanitize_text_field`, `esc_html`, `esc_attr`, `esc_url` ở đại đa số các vị trí.
- **Vấn đề Chuẩn hóa Output Escaping trong HTML Attributes:**
  Tại 43 vị trí trên các template (`archive-major.php:127, 134`, `archive-program.php:436, 455`, `archive-school.php:236, 357`, `functions.php:170, 185`, `taxonomy.php:59, 72`), thẻ liên kết sử dụng:
  `<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>`
  thay vì chuẩn khuyến nghị của WordPress VIP:
  `<a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>`.

---

### 1.5. Khảo sát Hiệu năng & Vấn đề N+1 Query / Query Loops

#### (A) Lỗi Nghiêm trọng: Tự Động Xóa Transient Cực Nặng trên Mỗi Lượt Tải Trang Chủ
- **Vị trí:** `front-page.php:16`
  ```php
  15: // Cache queries for schools
  16: delete_transient( 'ltdh_featured_schools_data' );
  17: $featured_schools = ltdh_get_cached_featured_schools();
  ```
- **Hậu quả:** Dòng code này chạy **mỗi khi có bất kỳ ai truy cập trang chủ**, lập tức xóa cache transient vừa lưu, biến toàn bộ cơ chế cache trở nên vô nghĩa. Mỗi lượt tải trang chủ buộc hệ thống phải tính toán lại từ đầu toàn bộ dữ liệu trường học và các chương trình liên kết.

#### (B) Thiết lập Mặc định Nguy hiểm `posts_per_page => -1` trên Trang Lưu trữ
- **Vị trí:** `inc/core/class-query-filters.php:24-32`
  ```php
  24: if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
  25:     $limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : -1;
  26:     $valid_limits = [ 10, 20, 30, 50, 100, -1 ];
  27:     if ( in_array( $limit, $valid_limits, true ) ) {
  28:         $query->set( 'posts_per_page', $limit );
  29:     } else {
  30:         $query->set( 'posts_per_page', -1 );
  31:     }
  32: }
  ```
- **Hậu quả:** Nếu người dùng vào `/truong/` hoặc `/nganh/` mà không có tham số `?limit=...`, hệ thống **mặc định gán `posts_per_page = -1`**, tải toàn bộ hàng trăm trường và ngành vào bộ nhớ cùng lúc.
- Ngoài ra, `archive-program.php:32` và `taxonomy-training_type.php:29` cho phép `$valid_limits` chứa `-1`, cho phép người dùng bên ngoài cố tình gửi `?limit=-1` để gây quá tải CPU/RAM máy chủ (Denial of Service).

#### (C) Vấn nạn N+1 Query Nghiêm Trọng tại Trang Danh Sách Trường (`archive-school.php`)
- **Vị trí:** `archive-school.php:78, 197, 264` và `inc/core/class-helpers.php:620-654`:
  ```php
  264: $prog_count = ltdh_get_school_unique_majors_count( $school_id );
  265: $offered_program_ids = get_posts( [
  266:     'post_type'   => 'program',
  267:     'numberposts' => -1,
  268:     'fields'      => 'ids',
  269:     'meta_query'  => [ [ 'key' => 'school_relationship', 'value' => $school_id, 'compare' => '=' ] ],
  270: ] );
  ```
  Và bên trong hàm `ltdh_get_school_unique_majors_count`:
  ```php
  621: $programs = get_posts( [ 'post_type' => 'program', 'posts_per_page' => -1, 'fields' => 'ids', ... ] );
  640: foreach ( $programs as $prog_id ) {
  641:     $major_rel = get_field( 'major_relationship', $prog_id );
  ...
  ```
- **Hậu quả:** Đối với mỗi trường học hiển thị trên danh sách:
  1. Chạy 1 query `get_posts` lấy toàn bộ chương trình của trường.
  2. Lặp qua từng chương trình để gọi `get_field('major_relationship')` (thêm N query meta).
  3. Ngay sau đó lại chạy tiếp 1 query `get_posts` thứ hai với `numberposts => -1` chỉ để lấy 5 tag!
  *Nghịch lý:* Hệ thống đã có hook `inc/relationship-hooks.php:36` tự động đồng bộ sẵn mảng `_offered_programs` vào post meta của từng trường, nhưng các template lại bỏ qua dữ liệu cache này và truy vấn database lặp đi lặp lại.

#### (D) Lưu Đối Tượng `WP_Query` Nguyên Bản Vào Transient
- **Vị trí:** `inc/core/class-helpers.php:408-416` (`ltdh_get_cached_query`):
  Hàm lưu trực tiếp instance object `WP_Query` vào transient cache. Việc serialize một object PHP phức tạp chứa con trỏ DB, mảng bài viết và method vào bảng `wp_options` làm phình to dung lượng CSDL và dễ phát sinh lỗi deserialization khi cấu trúc lớp thay đổi.

---

## 2. Logic Chain (Chuỗi Lập Luận Suy Luận Từ Quan Sát Đến Kết Luận)

```
[QUAN SÁT 1: tests/run-tests.php nạp wp-load.php trực tiếp không có guard CLI]
  │
  ├─> Trình duyệt có thể gửi GET request trực tiếp tới file qua đường dẫn công khai
  │
  └─> KẾT LUẬN 1 (Critical): Bất kỳ ai cũng có thể kích hoạt chạy suite test và xóa post trên CSDL production.

[QUAN SÁT 2: ltdh_elig_ajax_advanced_verify và ltdh_elig_ajax_check dùng wp_handle_upload không có mimes override]
  │
  ├─> Khách vãng lai gọi AJAX kèm file bất kỳ
  │
  └─> KẾT LUẬN 2 (High): Nguy cơ tải lên file chứa mã độc, file kích thước khổng lồ làm đầy ổ cứng hoặc file SVG chứa Stored XSS.

[QUAN SÁT 3: ltdh_elig_ajax_advanced_verify nhận lead_id từ client và update DB trực tiếp]
  │
  ├─> Không kiểm tra quyền sở hữu hay session token của lead_id đó
  │
  └─> KẾT LUẬN 3 (High): Lỗ hổng IDOR cho phép một người sửa đổi thông tin đăng ký của người khác.

[QUAN SÁT 4: front-page.php:16 gọi delete_transient trên mỗi request]
  │
  ├─> Mọi lượt truy cập trang chủ đều làm mất cache ngay lập tức
  │
  └─> KẾT LUẬN 4 (High Performance): Tê liệt cơ chế cache trang chủ, nhân số lượng query lên gấp nhiều lần.

[QUAN SÁT 5: class-query-filters.php đặt mặc định posts_per_page = -1 khi không có tham số limit]
  │
  ├─> Tất cả khách truy cập /truong/ và /nganh/ đều ép CSDL tải 100% dữ liệu không phân trang
  │
  └─> KẾT LUẬN 5 (High Performance): Gây nghẽn cổ chai database và tiêu tốn RAM khi số lượng bài viết tăng lên.

[QUAN SÁT 6: archive-school.php chạy 2 query get_posts(-1) + vòng lặp get_field cho mỗi trường]
  │
  ├─> N+1 query bùng nổ cấp số nhân (20 trường = 40+ query phụ)
  │
  └─> KẾT LUẬN 6 (Medium Performance): Tốc độ tải trang lưu trữ bị chậm đáng kể, lãng phí tài nguyên máy chủ.
```

---

## 3. Caveats (Các Điểm Giới Hạn & Giả Định)

1. **Phạm vi Audit:** Báo cáo dựa trên việc kiểm tra mã nguồn tĩnh (Static Code Analysis) của theme `lienthongdaihoc`. Không thực hiện các hành vi khai thác xâm nhập động (dynamic penetration testing) trên website đang hoạt động để đảm bảo tính toàn vẹn dữ liệu.
2. **Cấu hình Máy chủ & Nginx/Apache:** Nếu máy chủ production đã cấu hình chặn thực thi file trong thư mục `tests/` hoặc thư mục `inc/` từ tầng Nginx/Apache, thì nguy cơ Direct File Access ở các file này sẽ được giảm thiểu ở tầng hạ tầng. Tuy nhiên, ở tầng mã nguồn ứng dụng (defense-in-depth), theme vẫn phải tự bảo vệ.
3. **Contact Form 7 Dependency:** Một số logic phụ thuộc vào các plugin ngoài như Contact Form 7 và ACF PRO. Báo cáo đánh giá dựa trên hành vi code khi các plugin này hoạt động bình thường hoặc khi fallback về code thuần của theme.

---

## 4. Conclusion & Actionable Fix Plan (Kết Luận & Giải Pháp Khắc Phục Cụ Thể)

Dưới đây là bảng tổng hợp các vấn đề phát hiện và đoạn mã sửa chữa (fix snippet) chi tiết sẵn sàng để đội ngũ phát triển áp dụng.

### Bảng Tổng Hợp Vấn Đề Theo Mức Độ Nghiêm Trọng

| Mã Vấn Đề | Vị Trí File & Dòng | Mức Độ | Tóm Tắt Vấn Đề |
|---|---|---|---|
| **SEC-CRIT-01** | `tests/run-tests.php:1-14` | **CRITICAL** | File test nạp WordPress trực tiếp, thiếu kiểm tra môi trường CLI, mở cho web công khai |
| **SEC-HIGH-01** | `inc/eligibility.php:204-213, 835-845` | **HIGH** | Tải lên file công khai không có whitelist định dạng MIME và không kiểm tra dung lượng |
| **SEC-HIGH-02** | `inc/eligibility.php:824-855` | **HIGH** | Lỗ hổng IDOR cho phép cập nhật dữ liệu của bất kỳ `lead_id` nào qua AJAX |
| **PERF-HIGH-01**| `front-page.php:16` | **HIGH** | Gọi `delete_transient()` ngay đầu template trang chủ, phá hỏng toàn bộ bộ nhớ đệm |
| **PERF-HIGH-02**| `inc/core/class-query-filters.php:25-31` | **HIGH** | Mặc định `posts_per_page => -1` trên archive trường học và ngành học khi thiếu param `limit` |
| **SEC-MED-01**  | `functions.php:69-75` | **MEDIUM** | AJAX Filter `ltdh_ajax_filter_programs` thiếu xác thực nonce CSRF |
| **SEC-MED-02**  | `inc/lead-capture.php:333-380` & `inc/core/class-helpers.php:186-220` | **MEDIUM** | Form tư vấn gốc thiếu CSRF Nonce cả phía hiển thị lẫn phía xử lý submit |
| **PERF-MED-01** | `archive-school.php:264-276` & `inc/core/class-helpers.php:620` | **MEDIUM** | Vòng lặp N+1 query nặng nề trên danh sách trường học, bỏ qua meta `_offered_programs` có sẵn |
| **SEC-LOW-01**  | `header.php:1` & `inc/search-engine.php:1` | **LOW** | Thiếu guard bảo vệ truy cập file trực tiếp `defined('ABSPATH') \|\| exit;` |
| **PERF-LOW-01** | `inc/core/class-helpers.php:414` | **LOW** | Lưu trữ toàn bộ instance object `WP_Query` vào transient cache |

---

### Chi Tiết Kế Hoạch Khắc Phục & Code Snippet Chuẩn Hóa

#### 1. Khắc phục [SEC-CRIT-01]: Khóa Chặt File `tests/run-tests.php` Chỉ Cho Phép Chạy Qua CLI
- **File:** `tests/run-tests.php` (dòng 6-14)
- **Giải pháp:** Thêm kiểm tra `php_sapi_name() === 'cli'` ngay trên đầu file trước khi boot WordPress.
```php
// BEFORE:
$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	die( "Error: wp-load.php not found at $wp_load_path\n" );
}
define( 'WP_USE_THEMES', false );
require_once $wp_load_path;

// AFTER:
if ( php_sapi_name() !== 'cli' ) {
	http_response_code( 403 );
	die( 'Forbidden: CLI access only.' );
}

$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	die( "Error: wp-load.php not found at $wp_load_path\n" );
}
define( 'WP_USE_THEMES', false );
require_once $wp_load_path;
```

---

#### 2. Khắc phục [SEC-HIGH-01]: Thêm Whitelist MIME Types & Giới Hạn File Size Cho Upload
- **File:** `inc/eligibility.php` (tại cả 2 vị trí: dòng 204-213 và 835-845)
- **Giải pháp:** Định nghĩa whitelist MIME mảng ảnh và PDF, kiểm tra kích thước tối đa (ví dụ 5MB).
```php
// AFTER:
if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
    $file = $_FILES['degree_file'];

    // 1. Kiểm tra kích thước tối đa 5MB
    $max_size = 5 * 1024 * 1024;
    if ( $file['size'] > $max_size ) {
        wp_send_json_error( [ 'message' => 'Dung lượng file không được vượt quá 5MB.' ] );
    }

    // 2. Chỉ cho phép các định dạng ảnh thông dụng và PDF
    $allowed_mimes = [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'webp'         => 'image/webp',
        'pdf'          => 'application/pdf',
    ];

    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    $upload_overrides = [
        'test_form' => false,
        'mimes'     => $allowed_mimes,
    ];
    $movefile = wp_handle_upload( $file, $upload_overrides );

    if ( $movefile && ! isset( $movefile['error'] ) ) {
        $degree_file_url = esc_url_raw( $movefile['url'] );
    } else {
        wp_send_json_error( [ 'message' => $movefile['error'] ?? 'Lỗi khi tải file lên.' ] );
    }
}
```

---

#### 3. Khắc phục [SEC-HIGH-02]: Vá Lỗ Hổng IDOR trong `ltdh_elig_ajax_advanced_verify`
- **File:** `inc/eligibility.php` (dòng 824-855)
- **Giải pháp:** Khi tạo lead trong session, cấp một `lead_token` (UUID/Hash ngẫu nhiên) trả về cho client. Khi client gọi xác minh nâng cao, bắt buộc gửi kèm token này để đối chiếu với CSDL thay vì chỉ nhận mỗi số `lead_id` thô.
```php
// AFTER:
$lead_id = intval( $_POST['lead_id'] ?? 0 );
$lead_token = sanitize_text_field( $_POST['lead_token'] ?? '' );

if ( ! $lead_id || empty( $lead_token ) ) {
    wp_send_json_error( [ 'message' => 'Yêu cầu không hợp lệ.' ] );
}

global $wpdb;
$lead = $wpdb->get_row( $wpdb->prepare( 
    "SELECT * FROM {$wpdb->prefix}ltdh_leads WHERE id = %d AND referral_source LIKE %s", 
    $lead_id, 
    '%' . $wpdb->esc_like( $lead_token ) . '%' 
) );

if ( ! $lead ) {
    wp_send_json_error( [ 'message' => 'Bạn không có quyền cập nhật hồ sơ này.' ] );
}
```

---

#### 4. Khắc phục [PERF-HIGH-01]: Xóa Dòng Code Tự Hủy Transient trên Trang Chủ
- **File:** `front-page.php` (dòng 16)
- **Giải pháp:** Xóa bỏ lệnh `delete_transient( 'ltdh_featured_schools_data' );` vì hàm `ltdh_clear_transients_on_save()` trong `inc/core/class-helpers.php:499` đã tự động dọn dẹp cache khi có thao tác lưu bài viết.
```php
// BEFORE (front-page.php:15-18):
// Cache queries for schools
delete_transient( 'ltdh_featured_schools_data' );
$featured_schools = ltdh_get_cached_featured_schools();

// AFTER:
// Cache queries for schools
$featured_schools = ltdh_get_cached_featured_schools();
```

---

#### 5. Khắc phục [PERF-HIGH-02]: Đặt Phân Trang Mặc Định Hợp Lý cho Danh Mục Lưu Trữ
- **File:** `inc/core/class-query-filters.php` (dòng 24-32)
- **Giải pháp:** Mặc định phân trang là 12 bài/trang (thay vì `-1`). Giới hạn trần số lượng bản ghi tối đa (tối đa 50 hoặc 100), loại bỏ hoàn toàn tùy chọn `-1` khỏi tham số công khai.
```php
// BEFORE:
if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
    $limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : -1;
    $valid_limits = [ 10, 20, 30, 50, 100, -1 ];
    if ( in_array( $limit, $valid_limits, true ) ) {
        $query->set( 'posts_per_page', $limit );
    } else {
        $query->set( 'posts_per_page', -1 );
    }
}

// AFTER:
if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
    $limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
    $valid_limits = [ 10, 12, 20, 30, 50 ];
    if ( in_array( $limit, $valid_limits, true ) ) {
        $query->set( 'posts_per_page', $limit );
    } else {
        $query->set( 'posts_per_page', 12 );
    }
}
```

---

#### 6. Khắc phục [SEC-MED-01 & SEC-MED-02]: Bổ Sung CSRF Nonce Vào Toàn Bộ Form và AJAX
1. **Trong `functions.php:69`:**
   ```php
   function ltdh_ajax_filter_programs() {
       check_ajax_referer( 'ltdh_filter_nonce', 'nonce' );
       ...
   }
   ```
2. **Trong `inc/core/class-helpers.php:186` (`ltdh_render_native_form`):**
   ```php
   <form action="" method="POST" class="space-y-4">
       <?php wp_nonce_field( 'ltdh_native_form_action', 'ltdh_native_nonce' ); ?>
       ...
   ```
3. **Trong `inc/lead-capture.php:333` (`ltdh_handle_native_form_submit`):**
   ```php
   if ( ! isset( $_POST['ltdh_native_nonce'] ) || ! wp_verify_nonce( $_POST['ltdh_native_nonce'], 'ltdh_native_form_action' ) ) {
       wp_die( 'Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.', 'Bảo mật', [ 'response' => 403 ] );
   }
   ```

---

#### 7. Khắc phục [PERF-MED-01]: Tận Dụng Metadata `_offered_programs` Đã Được Đồng Bộ Sẵn
- **File:** `archive-school.php:264-276` và `inc/core/class-helpers.php:620-654`
- **Giải pháp:** Thay vì chạy `get_posts` lặp đi lặp lại với `posts_per_page => -1`, đọc trực tiếp mảng ID từ post meta `_offered_programs` đã được tạo sẵn qua hook `ltdh_sync_program_relationships`.
```php
// Tối ưu hàm ltdh_get_school_unique_majors_count:
function ltdh_get_school_unique_majors_count( int $school_id ): int {
    $program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
    if ( empty( $program_ids ) || ! is_array( $program_ids ) ) {
        return 0;
    }

    $major_ids = [];
    foreach ( $program_ids as $prog_id ) {
        $m_id = intval( get_post_meta( $prog_id, 'major_relationship', true ) );
        if ( $m_id && ! in_array( $m_id, $major_ids, true ) ) {
            $major_ids[] = $m_id;
        }
    }
    return count( $major_ids );
}
```

---

#### 8. Khắc phục [SEC-LOW-01]: Bổ Sung Direct Access Guard ở `header.php` & `inc/search-engine.php`
- Đặt đoạn code này ở dòng 1 của cả 2 file:
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

---

## 5. Verification Method (Phương Pháp Độc Lập Để Xác Minh Báo Cáo)

Người nhận bàn giao hoặc kiểm toán viên độc lập có thể kiểm chứng lại các quan sát trên bằng các bước sau:

1. **Xác minh thiếu Guard File:**
   ```bash
   head -n 5 "header.php"
   head -n 5 "inc/search-engine.php"
   head -n 15 "tests/run-tests.php"
   ```
   *Kết quả mong đợi:* Thấy ngay `header.php` bắt đầu bằng `<!DOCTYPE html>`, `search-engine.php` bắt đầu bằng `add_filter`, và `run-tests.php` require `wp-load.php` mà không kiểm tra CLI.

2. **Xác minh Bug Tự Hủy Transient Trang Chủ:**
   ```bash
   grep -n "delete_transient" front-page.php
   ```
   *Kết quả mong đợi:* Xác nhận dòng 16 gọi `delete_transient( 'ltdh_featured_schools_data' );`.

3. **Xác minh Mặc Định `posts_per_page => -1`:**
   ```bash
   grep -n -C 5 "valid_limits" inc/core/class-query-filters.php
   ```
   *Kết quả mong đợi:* Xác nhận dòng 25 và dòng 30 gán `-1` khi không có tham số `limit`.

4. **Xác minh Thiếu Nonce trong Form & AJAX:**
   ```bash
   grep -n "ltdh_ajax_filter_programs" functions.php
   grep -n "ltdh_handle_native_form_submit" inc/lead-capture.php
   ```
   *Kết quả mong đợi:* Không có bất kỳ dòng nào gọi `check_ajax_referer` hay `wp_verify_nonce`.

5. **Xác minh Thiếu Mime-type Upload:**
   ```bash
   grep -n -C 5 "wp_handle_upload" inc/eligibility.php
   ```
   *Kết quả mong đợi:* Xác nhận dòng 207 và dòng 838 chỉ truyền `$upload_overrides = array( 'test_form' => false );` mà không có mảng `'mimes'`.

---
*Báo cáo được hoàn thành độc lập và toàn diện, không thay đổi bất kỳ file mã nguồn nào của dự án.*
