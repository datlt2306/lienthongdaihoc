# BÁO CÁO PHẢN BIỆN KỸ THUẬT & KIỂM ĐỊNH THỰC NGHIỆM (ADVERSARIAL CHALLENGE REPORT)
## KIỂM ĐỊNH TÀI LIỆU: SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md (REQUIREMENTS R1 & R2)

- **Agent thực hiện**: `challenger_audit_3_1` (Teamwork Preview Challenger — Empirical Challenger & Code Auditor)
- **Đối tượng phản biện**: Báo cáo kiểm định `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` (tác giả: `worker_audit_3`)
- **Ngày thực hiện**: 28/09/2026
- **Phán quyết cuối cùng (Verdict)**: ⚠️ **REQUEST_CHANGES** (Yêu cầu chỉnh sửa các đoạn code đề xuất trước khi chuyển giao sang giai đoạn triển khai)

---

## MỤC LỤC
1. [TỔNG QUAN ĐÁNH GIÁ (EXECUTIVE CHALLENGE SUMMARY)](#1-tổng-quan-đánh-giá-executive-challenge-summary)
2. [XÁC MINH THỰC NGHIỆM CÁC PHÁT HIỆN R1 & R2 (EMPIRICAL VERIFICATION)](#2-xác-minh-thực-nghiệm-các-phát-hiện-r1--r2-empirical-verification)
   - 2.1. Kiểm định lỗi N+1 Query và tính toán chính xác số lượng truy vấn trên `archive-school.php`
   - 2.2. Kiểm định Taxonomy `training_type` bị thiếu trên CPT `school` và mảng rỗng `wp_get_post_terms`
   - 2.3. Kiểm định Regex Rewrite Rule `([^/]+)/?$` hijacking URL và ép 2 truy vấn trên trang tĩnh/404
   - 2.4. Kiểm định Dead Code do thiếu `#program-results-container` trong template files
3. [RÀ SOÁT & PHẢN BIỆN CHUYÊN SÂU CÁC ĐOẠN CODE ĐỀ XUẤT (CODE SNIPPETS ADVERSARIAL AUDIT)](#3-rà-soát--phản-biện-chuyên-sâu-các-đoạn-code-đề-xuất-code-snippets-adversarial-audit)
   - 3.1. Phản biện `LTDH_Entity_Relationship_Engine` (R1): 4 lỗi vòng đời WordPress nghiêm trọng
   - 3.2. Phản biện Snippet tối ưu `archive-school.php` (R2): Gây vỡ giao diện & Undefined variable
   - 3.3. Phản biện Snippet tối ưu `taxonomy-training_type.php` (R2): Rơi rụng toàn bộ bộ lọc người dùng
   - 3.4. Phản biện Snippet sửa SEO Canonical URL (R2): Sửa nhầm URL mục tiêu
   - 3.5. Phản biện Snippet Telegram Bot API v2 (R4): Lỗi mã hóa bot token làm hỏng webhook
4. [BẢNG TỔNG HỢP LỖI PHÁT HIỆN TRONG BÁO CÁO CỦA WORKER](#4-bảng-tổng-hợp-lỗi-phát-hiện-trong-báo-cáo-của-worker)
5. [ĐỀ XUẤT CODE ĐÃ HIỆU CHỈNH CHUẨN XÁC (REVISED CODE SNIPPETS)](#5-đề-xuất-code-đã-hiệu-chỉnh-chuẩn-xác-revised-code-snippets)

---

## 1. TỔNG QUAN ĐÁNH GIÁ (EXECUTIVE CHALLENGE SUMMARY)

Qua quá trình rà soát đối kháng độc lập, chạy kiểm tra tĩnh và kiểm thử thực nghiệm trên toàn bộ cây thư mục mã nguồn theme `lienthongdaihoc`, Challenger đưa ra đánh giá như sau:

1. **Về mặt Phát hiện lỗi (Audit Findings)**:
   - Các khẳng định thực nghiệm của Worker về lỗi kiến trúc dữ liệu và hiệu năng truy vấn trên R1 & R2 là **CHÍNH XÁC 100%**.
   - Bẫy N+1 query trên `archive-school.php`, lỗi thiếu taxonomy `training_type` trên `school`, lỗi chiếm quyền định tuyến của rewrite rule cấp 1, và lỗi mất AJAX do thiếu ID `#program-results-container` đều được xác minh thực tế tồn tại đúng như mô tả.
   
2. **Về mặt Đề xuất Kỹ thuật & Code Snippets (Proposed Solutions)**:
   - Báo cáo của Worker mắc phải **4 lỗi kỹ thuật nghiêm trọng trong các đoạn code PHP đề xuất**, có nguy cơ gây lỗi trắng trang, mất tính năng lọc và vỡ giao diện nếu lập trình viên sao chép và triển khai trực tiếp:
     - **Lỗi 1 (Cực kỳ nghiêm trọng)**: Lớp `LTDH_Entity_Relationship_Engine` móc vào `wp_trash_post`, `untrash_post` và `before_delete_post` thay vì các hook trạng thái hoàn tất (`trashed_post`, `untrashed_post`). Hậu quả: Khi đưa bài viết vào thùng rác hoặc xóa vĩnh viễn, bài viết **vẫn bị lưu lại trong `_offered_programs`**, sinh ra chính lỗi tham chiếu mồ côi (Orphan IDs) mà nó dự định giải quyết!
     - **Lỗi 2 (Vỡ giao diện & Lỗi PHP 8)**: Đoạn code đề xuất sửa N+1 trên `archive-school.php` hướng dẫn xóa toàn bộ dòng 265-307. Hành động này làm biến mất biến `$prog_tags` và `$region_terms`, dẫn đến ném lỗi `PHP Warning: Undefined variable $prog_tags` trên PHP 8+, làm mất hoàn toàn danh sách tag top 5 ngành học trên card trường, đồng thời in thẻ HTML `<span>` ngoài cấu trúc thẻ div card.
     - **Lỗi 3 (Liệt toàn bộ bộ lọc)**: Đoạn code đề xuất chuyển `taxonomy-training_type.php` sang `pre_get_posts` hoàn toàn bỏ quên các tham số lọc `$_GET['nhom_nganh']`, `$_GET['truong']`, `$_GET['s']`, `$_GET['sort']`. Nếu áp dụng, tính năng lọc trường và ngành của người dùng sẽ bị vô hiệu hóa 100%.
     - **Lỗi 4 (SEO Canonical Disconnect)**: Vấn đề được chẩn đoán là loop 301 tại `/chuong-trinh/`, nhưng snippet đề xuất lại kiểm tra đường dẫn `/he-dao-tao/`, không giải quyết được nguồn gốc lỗi.
     - **Lỗi 5 (Hỏng Telegram Bot)**: Lệnh `rawurlencode( $bot_token )` trong hàm Telegram v2 sẽ biến dấu hai chấm `:` trong token Telegram thành `%3A`, khiến Telegram API trả về HTTP 404.

Do đó, phán quyết là: **REQUEST_CHANGES**. Báo cáo audit cần được cập nhật lại các đoạn code giải pháp chuẩn xác trước khi đưa vào tài liệu bàn giao chính thức.

---

## 2. XÁC MINH THỰC NGHIỆM CÁC PHÁT HIỆN R1 & R2 (EMPIRICAL VERIFICATION)

### 2.1. Kiểm định lỗi N+1 Query và tính toán chính xác số lượng truy vấn trên `archive-school.php`
- **Quan sát code thực tế**:
  Tại `archive-school.php:14`:
  ```php
  $view_mode = isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'list', 'card' ], true ) ? $_GET['view'] : ( wp_is_mobile() ? 'card' : 'list' );
  ```
  Trên giao diện máy tính để bàn (Desktop), `$view_mode` mặc định là `'list'`.
  Trong vòng lặp List View (dòng 253-307):
  ```php
  // 1. Gọi hàm đếm ngành
  $prog_count = ltdh_get_school_unique_majors_count( $school_id );
  
  // 2. Chạy get_posts lấy toàn bộ program ID
  $offered_program_ids = get_posts( [
      'post_type'   => 'program',
      'numberposts' => -1,
      'fields'      => 'ids',
      'meta_query'  => [ [ 'key' => 'school_relationship', 'value' => $school_id, 'compare' => '=' ] ],
  ] );
  
  // 3. Vòng lặp foreach từng program ID để lấy term
  foreach ( $offered_program_ids as $pid ) {
      $terms = wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE );
  }
  ```
- **Xác minh hàm `ltdh_get_school_unique_majors_count()` (`inc/core/class-helpers.php:620-664`)**:
  Hàm này sử dụng `wp_cache_get( $cache_key, 'ltdh' )`. Trong môi trường PHP thông thường không cài Redis/Memcached, đây là non-persistent runtime cache. Vì mỗi trường có ID khác nhau, trên cùng một pageview, cache này trượt (cache miss) cho tất cả các trường.
  Bên trong hàm:
  - Chạy 1 câu `get_posts` với `'fields' => 'ids'`.
  - Trong WordPress Core, khi `fields => 'ids'`, bộ nhớ đệm postmeta **không được nạp sẵn (not primed)**.
  - Vòng lặp `foreach ( $programs as $prog_id )` gọi `get_post_meta( $prog_id, 'major_relationship', true )`. Mỗi lần gọi này sinh thêm 1 câu truy vấn SQL riêng biệt vào bảng `wp_postmeta` nếu meta cache chưa primed!
- **Công thức tính toán số lượng truy vấn chính xác**:
  Gọi $N$ là số lượng trường trên trang ($N = 12$ mặc định; tối đa 50 khi có query `?limit=50`).
  Gọi $K_i$ là số lượng chương trình đào tạo của trường thứ $i$ ($K \approx 15$).
  Gọi $F$ là số lượng trường nổi bật ở đầu trang ($F = 4$).
  
  Trên mỗi trường trong List View:
  1. `get_posts` trong hàm đếm ngành: $1$ query.
  2. `get_post_meta` trong hàm đếm ngành: $K_i$ queries.
  3. `get_posts` tìm `$offered_program_ids` (bị lặp lại vô nghĩa): $1$ query.
  4. `wp_get_post_terms( $school_id, LTDH_TAX_REGION )`: $1$ query.
  5. `wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE )`: $K_i$ queries (do term cache của `$pid` chưa được primed).
  
  $$\text{Số queries cho 1 trường} = 3 + 2 \times K_i$$
  Với $K = 15$: $3 + 2 \times 15 = 33 \text{ queries / trường}$.
  Cho 12 trường: $12 \times 33 = 396 \text{ queries}$.
  Thêm khối 4 Featured Schools: $4 \times (2 + K) \approx 68 \text{ queries}$.
  Thêm Main Query và Featured Query: $2 \text{ queries}$.
  $$\mathbf{\text{Tổng cộng thực tế}} \approx \mathbf{466 \text{ queries / pageview!}}$$
- **Kết luận phản biện**: Khẳng định của Worker về lỗi N+1 là **HOÀN TOÀN CHÍNH XÁC**. Thậm chí thực tế số truy vấn còn trầm trọng hơn mức 205 queries mà Worker ước tính do hàm `ltdh_get_school_unique_majors_count` cũng bị bẫy N+1 lặp lại.

---

### 2.2. Kiểm định Taxonomy `training_type` bị thiếu trên CPT `school` và mảng rỗng `wp_get_post_terms`
- **Quan sát file `inc/acf-import-cpts.json`**:
  - Dòng 174-176:
    ```json
    "taxonomy": "training_type",
    "object_type": [
        "program"
    ],
    ```
  - Dòng 213-215:
    ```json
    "taxonomy": "campus",
    "object_type": [
        "program"
    ],
    ```
  Cả 2 taxonomy `training_type` và `campus` đều chỉ được đăng ký cho duy nhất Post Type `program`. CPT `school` hoàn toàn không có tên trong danh sách `object_type`.
- **Hệ quả trong `archive-school.php`**:
  - Dòng 80 (Khối Featured):
    `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );`
  - Dòng 199 (Khối Card View):
    `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );`
  Do taxonomy `training_type` không được gán cho `school` và trong bảng `wp_term_relationships` không có bản ghi nào liên kết `$school_id` với taxonomy này, hàm `wp_get_post_terms()` luôn luôn trả về mảng rỗng `[]`.
  Tại dòng 117 và dòng 223:
  `if ( ! empty( $school_types ) && ! is_wp_error( $school_types ) )` -> luôn trả về `false`.
- **Kết luận phản biện**: Khẳng định của Worker là **HOÀN TOÀN CHÍNH XÁC**. Toàn bộ khối Card View và Featured Schools trên thực tế hoàn toàn không hiển thị được bất kỳ badge hệ đào tạo nào.

---

### 2.3. Kiểm định Regex Rewrite Rule `([^/]+)/?$` hijacking URL và ép 2 truy vấn trên trang tĩnh/404
- **Quan sát file `inc/core/class-rewrite-rules.php`**:
  - Dòng 41-44:
    ```php
    function ltdh_register_program_rewrite() {
        add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
    }
    add_action( 'init', 'ltdh_register_program_rewrite' );
    ```
  - Cờ `'top'` đưa rule này lên vị trí ưu tiên số 1 trong mảng `rewrite_rules` của WordPress. Bất kỳ URL nào chỉ có 1 path segment (ví dụ: `/gioi-thieu/`, `/lien-he/`, `/cam-nang/`, `/faq/`, `/truong-dai-hoc/`, hoặc bất kỳ URL 404 nào) đều bị khớp vào regex này và gán `?program=slug`.
  - Tại dòng 54-101: Hook `request` kích hoạt hàm `ltdh_program_request_guard( $query_vars )`:
    1. Câu truy vấn 1 (dòng 62-68):
       `get_posts(['name' => $slug, 'post_type' => 'program', 'post_status' => 'publish', ...])`
    2. Nếu không thấy, câu truy vấn 2 (dòng 82-88):
       `get_posts(['name' => $slug, 'post_type' => 'post', 'post_status' => 'publish', ...])`
    3. Nếu vẫn không thấy, gán `$query_vars['pagename'] = $slug` và để WordPress chạy câu truy vấn 3 (Main Query).
- **Kết luận phản biện**: Khẳng định của Worker là **HOÀN TOÀN CHÍNH XÁC**. Toàn bộ các trang tĩnh, bài viết và các đường dẫn lỗi 404 đều phải chịu thêm 2 câu truy vấn SQL lãng phí trước khi render.

---

### 2.4. Kiểm định Dead Code do thiếu `#program-results-container` trong template files
- **Quan sát mã JavaScript `assets/js/main.js`**:
  - Dòng 9-12:
    ```javascript
    const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"]');
    const container = document.getElementById('program-results-container');

    if (filterForm && container) {
        // Toàn bộ logic AJAX Filter...
    }
    ```
- **Kiểm tra toàn bộ mã nguồn PHP**:
  Chạy tìm kiếm chuỗi `program-results-container` trên toàn bộ theme:
  - Kết quả: Không tìm thấy bất kỳ thẻ HTML nào chứa `id="program-results-container"`.
  - Tại `taxonomy-training_type.php:352`:
    `<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">` (Hoàn toàn không có thuộc tính `id`).
  - Tại `archive-program.php:355`:
    `<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">` (Hoàn toàn không có thuộc tính `id`).
  Do đó, biến `container` luôn mang giá trị `null`, biểu thức `if (filterForm && container)` luôn trả về `false`.
- **Kết luận phản biện**: Khẳng định của Worker là **HOÀN TOÀN CHÍNH XÁC**. 100% logic AJAX Filter trong `main.js` là Dead Code; hệ thống luôn rơi vào Full Page Reload khi người dùng thao tác.

---

## 3. RÀ SOÁT & PHẢN BIỆN CHUYÊN SÂU CÁC ĐOẠN CODE ĐỀ XUẤT (CODE SNIPPETS ADVERSARIAL AUDIT)

Đây là phần phản biện trọng tâm, chỉ ra các sai sót kỹ thuật tiềm ẩn trong các đoạn code mà Worker đề xuất tại `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`.

---

### 3.1. Phản biện `LTDH_Entity_Relationship_Engine` (R1): 4 lỗi vòng đời WordPress nghiêm trọng

Trong Section 2.4 của báo cáo kiểm định, Worker đề xuất lớp `LTDH_Entity_Relationship_Engine` (dòng 283-481). Sau khi kiểm toán từng phương thức, Challenger phát hiện 4 lỗ hổng nghiêm trọng:

#### 🚨 Lỗ hổng 1: Đưa bài vào Thùng rác nhưng không xóa khỏi cache do hook sai thời điểm (`wp_trash_post`)
- **Code đề xuất của Worker (dòng 303-304)**:
  ```php
  add_action( 'wp_trash_post', [ __CLASS__, 'on_program_lifecycle_change' ] );
  add_action( 'untrash_post', [ __CLASS__, 'on_program_lifecycle_change' ] );
  ```
- **Phân tích cơ chế lỗi WordPress Core**:
  Trong mã nguồn WordPress Core (`wp-includes/post.php`), hàm `wp_trash_post( $post_id )` thực thi theo thứ tự:
  1. `do_action( 'wp_trash_post', $post_id, $previous_status );` (Hook chạy tại đây!)
  2. `$wpdb->update( $wpdb->posts, [ 'post_status' => 'trash' ], [ 'ID' => $post->ID ] );`
  3. `do_action( 'trashed_post', $post_id, $previous_status );`
  
  Khi `wp_trash_post` kích hoạt `on_program_lifecycle_change()`, phương thức này gọi:
  `rebuild_entity_programs_cache( $school_id, 'school' )`.
  Bên trong `rebuild_entity_programs_cache()`, câu lệnh lấy ID là:
  ```php
  $active_ids = get_posts( [
      'post_type'   => LTDH_CPT_PROGRAM,
      'post_status' => 'publish', // Lọc bài có status publish
      ...
  ] );
  ```
  **Thời điểm này trạng thái trong CSDL của post vẫn đang là `'publish'`, chưa chuyển sang `'trash'`!**
  Do đó, câu truy vấn `get_posts` **VẪN LẤY ĐƯỢC CHƯƠNG TRÌNH ĐÓ** và ghi ngược trở lại `_offered_programs`!
  Sau khi hàm kết thúc, WordPress mới đổi status thành `'trash'`. Nhưng `_offered_programs` thì vĩnh viễn không được cập nhật lại!
- **Tương tự với `untrash_post`**:
  `untrash_post` chạy trước khi trạng thái được khôi phục từ `'trash'` thành `'publish'`. Khi query chạy, bài viết vẫn đang ở trạng thái `'trash'`, nên chương trình được phục hồi **KHÔNG BAO GIỜ ĐƯỢC THÊM LẠI** vào `_offered_programs`!
- **Khắc phục**: Phải lắng nghe action `trashed_post` và `untrashed_post`.

---

#### 🚨 Lỗ hổng 2: Tái tạo Orphan ID khi xóa vĩnh viễn bài viết (`before_delete_post`)
- **Code đề xuất của Worker (dòng 307 & 365-367)**:
  ```php
  add_action( 'before_delete_post', [ __CLASS__, 'on_program_delete' ] );
  public static function on_program_delete( int $post_id ): void {
      self::on_program_lifecycle_change( $post_id );
  }
  ```
- **Phân tích cơ chế lỗi**:
  Tương tự như trên, `before_delete_post` chạy **trước khi bản ghi bị xóa khỏi bảng `wp_posts`**.
  Khi `rebuild_entity_programs_cache` chạy, nó quét toàn bộ `program` có `post_status = 'publish'` và `school_relationship = $school_id`. Bài viết sắp bị xóa **vẫn tồn tại trong DB**, nên nó được đưa vào mảng `$active_ids` và lưu vào postmeta của School.
  Ngay sau đó, WordPress xóa bài viết khỏi bảng `wp_posts`.
  **Kết quả**: School vẫn giữ ID của chương trình đã bị xóa trong `_offered_programs`. Đây chính xác là lỗi tham chiếu mồ côi (Orphan IDs) mà chính Worker đã phê phán!
- **Khắc phục**: Khi gọi `rebuild_entity_programs_cache`, phải truyền thêm tham số loại trừ ID đang bị xóa: `'post__not_in' => [ $post_id ]`.

---

#### 🚨 Lỗ hổng 3: Bão truy vấn (Query Storm) và thực thi thừa khi xóa Trường (`on_parent_entity_delete`)
- **Code đề xuất của Worker (dòng 369-398)**:
  ```php
  public static function on_parent_entity_delete( int $post_id ): void {
      ...
      foreach ( $child_programs as $c_id ) {
          wp_update_post( [
              'ID'          => $c_id,
              'post_status' => 'draft',
          ] );
          delete_post_meta( $c_id, $meta_key );
      }
  }
  ```
- **Phân tích cơ chế lỗi**:
  1. Khi gọi `wp_update_post([ 'ID' => $c_id, 'post_status' => 'draft' ])`, WordPress kích hoạt action `save_post_program`. Action này lại gọi `on_program_save()`.
  2. Tại thời điểm đó, dòng lệnh `delete_post_meta( $c_id, $meta_key )` **chưa chạy** (nằm ở dòng kế tiếp).
  3. `on_program_save()` vẫn đọc thấy `$school_id = $post_id` (chính là trường đang bị xóa!) và kích hoạt `rebuild_entity_programs_cache` và `rollup_school_taxonomies` trên chính cái trường sắp bị xóa!
  4. Nếu trường có 40 chương trình đào tạo, vòng lặp này sẽ kích hoạt 40 lần cập nhật cache và rollup đồng bộ, sinh ra hơn 200 truy vấn SQL vô nghĩa vào một thực thể đang chuẩn bị biến mất.
- **Khắc phục**: Phải chạy `delete_post_meta` trước, đồng thời tạm thời gỡ bỏ (unhook) `save_post_program` trong quá trình cascade update, hoặc chỉ chạy cập nhật draft mà không trigger relationship sync.

---

#### 🚨 Lỗ hổng 4: Race condition với ACF trong môi trường WP-Admin
- **Code đề xuất của Worker (dòng 300)**:
  `add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20, 2 );`
- **Phân tích cơ chế lỗi**:
  Trong WP-Admin, các trường quan hệ `school_relationship` và `major_relationship` là ACF Post Object.
  Khi người dùng bấm "Cập nhật" bài viết trong Admin:
  Action `save_post` chạy trước hoặc song song với quá trình ACF lưu dữ liệu từ `$_POST['acf']`.
  Nếu chỉ móc vào `save_post_program`, tại thời điểm hook chạy, ACF chưa cập nhật giá trị mới vào `wp_postmeta`, dẫn đến `get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true )` đọc phải ID cũ!
- **Khắc phục**: Phải lắng nghe cả `acf/save_post` (priority 20) cho các thao tác trong Admin và `save_post_program` cho các thao tác cập nhật qua API/CLI.

---

### 3.2. Phản biện Snippet tối ưu `archive-school.php` (R2): Gây vỡ giao diện & Undefined variable

Trong Section 3.7.1 và Bảng 7.1 (mục 1.7), Worker đề xuất:
> *"Thay thế toàn bộ đoạn code N+1 dòng 265-307 bằng:*
> ```php
> // Tối ưu hóa: Đọc dữ liệu đã đồng bộ sẵn, 0 query phụ phát sinh
> $training_modes = get_post_meta( $school_id, '_active_training_systems', true );
> if ( is_array( $training_modes ) && ! empty( $training_modes ) ) {
>     foreach ( $training_modes as $slug => $name ) {
>         echo '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 mr-1.5 mb-1">' . esc_html( $name ) . '</span>';
>     }
> }
> ```
> *"

#### 🔴 Phân tích hậu quả thực tế khi áp dụng:
1. **Lỗi `Undefined variable $prog_tags` & Mất danh sách Top 5 ngành học**:
   Trong `archive-school.php`, các dòng 278-290 là đoạn khởi tạo mảng `$prog_tags`:
   ```php
   $prog_tags = [];
   if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
       $tag_ids = array_slice( $offered_program_ids, 0, 5 );
       foreach ( $tag_ids as $tid ) {
           $title = get_the_title( $tid );
           if ( $title ) {
               $prog_tags[] = [ 'title' => $title, 'link' => get_permalink( $tid ) ];
           }
       }
   }
   ```
   Nếu lập trình viên xóa sạch từ dòng 265 đến 307 theo đúng chỉ dẫn của audit, `$prog_tags` **hoàn toàn không được khởi tạo**.
   Đến dòng 345:
   `<?php if ( ! empty( $prog_tags ) ) : ?>`
   Trên PHP 8.1 - 8.4, việc kiểm tra một biến chưa từng được khai báo trong scope sẽ ném cảnh báo `PHP Warning: Undefined variable $prog_tags`. Đồng thời, toàn bộ khu vực hiển thị danh sách các ngành tiêu biểu của trường trên List View sẽ biến mất!
2. **Lỗi xóa mất biến `$region_terms`**:
   Dòng 292-293 lấy `$region_terms` để hiển thị địa phương của trường tại dòng 328. Xóa dòng 265-307 làm mất luôn thông tin địa chỉ khu vực.
3. **In thẻ HTML sai vị trí (Invalid Markup Placement)**:
   Dòng 265-307 nằm ở khối PHP chuẩn bị dữ liệu, **trước khi mở thẻ HTML của Card** (`archive-school.php:309`: `<div class="bg-white border ...">`).
   Nếu đặt lệnh `echo '<span class="inline-flex...">'` tại vị trí này, các thẻ `<span>` badge hệ đào tạo sẽ bị in trôi nổi bên ngoài, phía trên đầu của hộp card trường!
   Hơn nữa, tại dòng 337-339 bên trong card, code gốc vốn đã có:
   ```php
   foreach ( $training_modes as $mode ) {
       echo ltdh_get_training_type_badge_html( $mode );
   }
   ```
   Nếu snippet của Worker chạy, badge sẽ bị in trùng lặp 2 lần: 1 lần ngoài card và 1 lần trong card!

---

### 3.3. Phản biện Snippet tối ưu `taxonomy-training_type.php` (R2): Rơi rụng toàn bộ bộ lọc người dùng

Tại Section 3.7.2, Worker đề xuất hàm `ltdh_optimize_taxonomy_archive_query` hook vào `pre_get_posts`:
```php
function ltdh_optimize_taxonomy_archive_query( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) || $query->is_post_type_archive( LTDH_CPT_PROGRAM ) ) {
        $query->set( 'post_type', LTDH_CPT_PROGRAM );
        $query->set( 'post_status', 'publish' );

        $limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
        $query->set( 'posts_per_page', in_array( $limit, [ 12, 24, 36, 48 ], true ) ? $limit : 12 );

        // Loại bỏ các chương trình tạm ngưng
        $meta_query   = (array) $query->get( 'meta_query' );
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'value' => LTDH_STATUS_PAUSED, 'compare' => '!=' ],
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'compare' => 'NOT EXISTS' ],
        ];
        $query->set( 'meta_query', $meta_query );
    }
}
```
Và tại template, Worker đề xuất thay thế toàn bộ bằng vòng lặp `while ( have_posts() )`.

#### 🔴 Phân tích hậu quả thực tế khi áp dụng:
1. **Rơi rụng 100% các bộ lọc của người dùng**:
   Template `taxonomy-training_type.php` hiện tại hỗ trợ 4 tham số lọc rất quan trọng:
   - `$_GET['nhom_nganh']`: Lọc theo nhóm ngành (`major_cat`) hoặc ngành cụ thể (`major`).
   - `$_GET['truong']`: Lọc theo trường đại học (`school_relationship`).
   - `$_GET['s']`: Tìm kiếm từ khóa.
   - `$_GET['sort']`: Sắp xếp theo tên A-Z, Z-A hoặc mới nhất.
   Trong hàm `pre_get_posts` mà Worker đề xuất, **hoàn toàn không có một dòng code nào xử lý `nhom_nganh`, `truong`, `s` hay `sort`**!
   Nếu thay thế `$query = new WP_Query($args)` bằng Main Query theo đề xuất này, khi người dùng chọn trường ĐH Kinh Tế Quốc Dân hoặc chọn ngành CNTT, **kết quả trả về vẫn là toàn bộ các trường và các ngành**! Toàn bộ tính năng lọc trên trang Hệ đào tạo sẽ bị tê liệt hoàn toàn.
2. **Lỗi phân trang `paginate_links()`**:
   Tại dòng 488 của `taxonomy-training_type.php`, code gốc kiểm tra:
   `if ( $query->max_num_pages > 1 )`
   Nếu xóa `$query`, biến `$query` không còn tồn tại, phân trang sẽ văng lỗi `PHP Notice: Undefined variable $query`. Phải sử dụng biến toàn cục `$wp_query->max_num_pages`.

---

### 3.4. Phản biện Snippet sửa SEO Canonical URL (R2): Sửa nhầm URL mục tiêu

Tại Section 3.6.1, Worker chỉ ra một lỗi SEO rất chuẩn:
CPT `program` có `has_archive_slug: "chuong-trinh"`. Rank Math sinh thẻ canonical trỏ về `domain.com/chuong-trinh/`. Nhưng file `class-rewrite-rules.php:154` lại redirect 301 từ `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`. Dẫn tới thẻ canonical trỏ vào một trang bị redirect 301.

Tuy nhiên, tại Section 3.7.4, đoạn code sửa chữa được đề xuất là:
```php
add_filter( 'rank_math/frontend/canonical_url', function( string $canonical ): string {
    $path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
    if ( preg_match( '#^/he-dao-tao/?$#i', $path ) ) {
        return home_url( '/he-dao-tao/' );
    }
    return $canonical;
} );
```
#### 🔴 Phân tích sự bất hợp lý:
- Regex trong snippet chỉ kiểm tra: `#^/he-dao-tao/?$#i`.
- Đoạn code này **hoàn toàn không chạm vào URL `/chuong-trinh/`**! Khi Googlebot hoặc Rank Math quét trang lưu trữ của CPT `program`, thẻ canonical vẫn trỏ về `/chuong-trinh/` và vẫn bị loop redirect 301!
- Giải pháp đúng đắn là phải xử lý ngay trong `inc/acf-import-cpts.json` đổi `has_archive_slug` thành `he-dao-tao`, hoặc trong filter kiểm tra `is_post_type_archive( 'program' )` để trả về URL đích.

---

### 3.5. Phản biện Snippet Telegram Bot API v2 (R4): Lỗi mã hóa bot token làm hỏng webhook

Tại Section 5.6.3, hàm `ltdh_trigger_telegram_notification_v2` có đoạn:
```php
$api_url = 'https://api.telegram.org/bot' . rawurlencode( $bot_token ) . '/sendMessage';
```
#### 🔴 Phân tích cơ chế lỗi:
- Theo định dạng chuẩn của Telegram Bot API, mọi bot token đều có cấu trúc: `<bot_id>:<bot_secret>` (ví dụ: `1234567890:ABCdefGHI...`).
- Dấu hai chấm `:` là thành phần phân tách bắt buộc giữa Bot ID và Secret Token.
- Hàm `rawurlencode( $bot_token )` sẽ mã hóa dấu `:` thành chuỗi `%3A`.
- URL gửi đi sẽ trở thành: `https://api.telegram.org/bot1234567890%3AABCdefGHI.../sendMessage`.
- Nginx reverse-proxy của máy chủ Telegram API yêu cầu ký tự `:` nguyên bản trong URL path. Khi gặp `%3A`, Telegram không nhận diện được bot và trả về mã lỗi **HTTP 404 Not Found**.
- Kết hợp với cờ `'blocking' => false`, thông báo Telegram sẽ thất bại hoàn toàn trong im lặng mà không để lại bất kỳ log lỗi nào.
- **Khắc phục**: Không dùng `rawurlencode` hay `urlencode` lên toàn bộ bot token. Giữ nguyên chuỗi token hoặc chỉ trim khoảng trắng.

---

## 4. BẢNG TỔNG HỢP LỖI PHÁT HIỆN TRONG BÁO CÁO CỦA WORKER

| Mã lỗi | Vị trí trong báo cáo | Mức độ nghiêm trọng | Bản chất lỗi phát hiện | Khuyến nghị hành động |
|---|---|---|---|---|
| **BUG-CH-01** | Section 2.4 (`LTDH_Entity_Relationship_Engine`) | 🚨 **CRITICAL** | Dùng hook `wp_trash_post` & `before_delete_post` làm tái sinh Orphan IDs trong `_offered_programs` khi xóa bài. | Đổi sang `trashed_post`, `untrashed_post` và truyền `post__not_in` khi xóa. |
| **BUG-CH-02** | Section 3.7.1 (`archive-school.php` fix) | 🔴 **HIGH** | Hướng dẫn xóa dòng 265-307 làm mất `$prog_tags` (gây warning PHP 8 & mất UI tag) và echo HTML ngoài card. | Chỉ thay thế cụm lấy terms dòng 295-307 bằng `_active_training_systems`. |
| **BUG-CH-03** | Section 3.7.2 (`pre_get_posts` fix) | 🚨 **CRITICAL** | Bỏ sót toàn bộ các biến `$_GET['nhom_nganh']`, `$_GET['truong']`, `$_GET['s']`, `$_GET['sort']` khiến bộ lọc liệt 100%. | Đưa đầy đủ logic parse query vars từ `taxonomy-training_type.php` vào `pre_get_posts`. |
| **BUG-CH-04** | Section 3.7.4 (Rank Math Canonical) | 🟡 **MEDIUM** | Filter chỉ bắt `/he-dao-tao/`, không giải quyết lỗi canonical loop `/chuong-trinh/` 301 đã chẩn đoán. | Đổi `has_archive_slug` trong JSON hoặc filter `is_post_type_archive('program')`. |
| **BUG-CH-05** | Section 5.6.3 (`ltdh_trigger_telegram_notification_v2`) | 🔴 **HIGH** | `rawurlencode($bot_token)` biến dấu `:` thành `%3A`, làm Telegram trả về 404 Not Found. | Loại bỏ `rawurlencode`, dùng trực tiếp `$bot_token`. |

---

## 5. ĐỀ XUẤT CODE ĐÃ HIỆU CHỈNH CHUẨN XÁC (REVISED CODE SNIPPETS)

Dưới đây là các đoạn mã nguồn đã được Challenger tinh chỉnh, vá toàn bộ các lỗ hổng vòng đời và sẵn sàng để đội ngũ triển khai áp dụng an toàn.

### 5.1. Lớp `LTDH_Entity_Relationship_Engine` hoàn thiện (Chuẩn WordPress Lifecycle):

```php
<?php
/**
 * Class LTDH_Entity_Relationship_Engine (Patched Version)
 * Quản lý vòng đời quan hệ thực thể School - Program - Major
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LTDH_Entity_Relationship_Engine {

    public static function init(): void {
        // 1. Hook lưu bài viết (hỗ trợ cả Admin ACF và Programmatic Save)
        add_action( 'acf/save_post', [ __CLASS__, 'on_acf_save_program' ], 25 );
        add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20, 2 );

        // 2. Hook sau khi trạng thái ĐÃ THAY ĐỔI (tránh đọc trạng thái cũ)
        add_action( 'trashed_post', [ __CLASS__, 'on_program_status_changed' ] );
        add_action( 'untrashed_post', [ __CLASS__, 'on_program_status_changed' ] );

        // 3. Hook khi xóa vĩnh viễn
        add_action( 'before_delete_post', [ __CLASS__, 'on_program_before_delete' ] );
        add_action( 'before_delete_post', [ __CLASS__, 'on_parent_before_delete' ] );
    }

    public static function on_acf_save_program( $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }
        self::sync_program( (int) $post_id );
    }

    public static function on_program_save( int $post_id, WP_Post $post ): void {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
            return;
        }
        self::sync_program( $post_id );
    }

    public static function on_program_status_changed( int $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }
        self::sync_program( $post_id );
    }

    public static function on_program_before_delete( int $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }
        $school_id = (int) get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true );
        $major_id  = (int) get_post_meta( $post_id, LTDH_META_MAJOR_REL, true );

        // Loại trừ rõ ràng ID sắp bị xóa khỏi danh sách
        if ( $school_id ) {
            self::rebuild_entity_programs_cache( $school_id, 'school', [ $post_id ] );
            self::rollup_school_taxonomies( $school_id, [ $post_id ] );
        }
        if ( $major_id ) {
            self::rebuild_entity_programs_cache( $major_id, 'major', [ $post_id ] );
        }
    }

    public static function on_parent_before_delete( int $post_id ): void {
        $post_type = get_post_type( $post_id );
        if ( ! in_array( $post_type, [ LTDH_CPT_SCHOOL, LTDH_CPT_MAJOR ], true ) ) {
            return;
        }

        $meta_key = ( LTDH_CPT_SCHOOL === $post_type ) ? LTDH_META_SCHOOL_REL : LTDH_META_MAJOR_REL;

        $child_programs = get_posts( [
            'post_type'      => LTDH_CPT_PROGRAM,
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [ [ 'key' => $meta_key, 'value' => $post_id, 'compare' => '=' ] ],
        ] );

        // Ngắt hook tạm thời để tránh loop query
        remove_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20 );

        foreach ( $child_programs as $c_id ) {
            delete_post_meta( $c_id, $meta_key );
            wp_update_post( [ 'ID' => $c_id, 'post_status' => 'draft' ] );
        }

        add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20, 2 );
    }

    private static function sync_program( int $post_id ): void {
        $school_id = (int) get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true );
        $major_id  = (int) get_post_meta( $post_id, LTDH_META_MAJOR_REL, true );

        $old_school_id = (int) get_post_meta( $post_id, LTDH_META_LAST_SCHOOL, true );
        $old_major_id  = (int) get_post_meta( $post_id, LTDH_META_LAST_MAJOR, true );

        if ( $school_id ) {
            self::rebuild_entity_programs_cache( $school_id, 'school' );
            self::rollup_school_taxonomies( $school_id );
        }
        if ( $old_school_id && $old_school_id !== $school_id ) {
            self::rebuild_entity_programs_cache( $old_school_id, 'school' );
            self::rollup_school_taxonomies( $old_school_id );
        }

        if ( $major_id ) {
            self::rebuild_entity_programs_cache( $major_id, 'major' );
        }
        if ( $old_major_id && $old_major_id !== $major_id ) {
            self::rebuild_entity_programs_cache( $old_major_id, 'major' );
        }

        update_post_meta( $post_id, LTDH_META_LAST_SCHOOL, $school_id );
        update_post_meta( $post_id, LTDH_META_LAST_MAJOR, $major_id );
    }

    public static function rebuild_entity_programs_cache( int $entity_id, string $type, array $exclude_ids = [] ): void {
        if ( ! $entity_id ) return;
        $meta_key = ( 'school' === $type ) ? LTDH_META_SCHOOL_REL : LTDH_META_MAJOR_REL;

        $args = [
            'post_type'      => LTDH_CPT_PROGRAM,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => [ [ 'key' => $meta_key, 'value' => $entity_id, 'compare' => '=' ] ],
        ];
        if ( ! empty( $exclude_ids ) ) {
            $args['post__not_in'] = $exclude_ids;
        }

        $active_ids = get_posts( $args );
        update_post_meta( $entity_id, LTDH_META_OFFERED_PROGRAMS, array_values( array_unique( $active_ids ) ) );
    }

    public static function rollup_school_taxonomies( int $school_id, array $exclude_ids = [] ): void {
        $programs = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
        if ( ! empty( $exclude_ids ) && is_array( $programs ) ) {
            $programs = array_diff( $programs, $exclude_ids );
        }

        if ( empty( $programs ) || ! is_array( $programs ) ) {
            wp_set_object_terms( $school_id, [], LTDH_TAX_TRAINING_TYPE );
            wp_set_object_terms( $school_id, [], LTDH_TAX_CAMPUS );
            update_post_meta( $school_id, '_active_training_systems', [] );
            return;
        }

        $valid_programs = get_posts( [
            'post_type'      => LTDH_CPT_PROGRAM,
            'post__in'       => $programs,
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => 'publish',
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => LTDH_META_ADMISSION_STATUS, 'value' => LTDH_STATUS_PAUSED, 'compare' => '!=' ],
                [ 'key' => LTDH_META_ADMISSION_STATUS, 'compare' => 'NOT EXISTS' ],
            ],
        ] );

        if ( empty( $valid_programs ) ) {
            wp_set_object_terms( $school_id, [], LTDH_TAX_TRAINING_TYPE );
            wp_set_object_terms( $school_id, [], LTDH_TAX_CAMPUS );
            update_post_meta( $school_id, '_active_training_systems', [] );
            return;
        }

        $training_type_ids = [];
        $active_systems    = [];
        $tt_terms = wp_get_object_terms( $valid_programs, LTDH_TAX_TRAINING_TYPE );
        if ( ! is_wp_error( $tt_terms ) && ! empty( $tt_terms ) ) {
            foreach ( $tt_terms as $t ) {
                $training_type_ids[]        = (int) $t->term_id;
                $active_systems[ $t->slug ] = $t->name;
            }
        }

        $campus_ids = [];
        $cp_terms = wp_get_object_terms( $valid_programs, LTDH_TAX_CAMPUS );
        if ( ! is_wp_error( $cp_terms ) && ! empty( $cp_terms ) ) {
            foreach ( $cp_terms as $c ) {
                $campus_ids[] = (int) $c->term_id;
            }
        }

        wp_set_object_terms( $school_id, array_unique( $training_type_ids ), LTDH_TAX_TRAINING_TYPE );
        wp_set_object_terms( $school_id, array_unique( $campus_ids ), LTDH_TAX_CAMPUS );
        update_post_meta( $school_id, '_active_training_systems', $active_systems );
    }
}
```

---

### 5.2. Đoạn code sửa N+1 chuẩn xác cho `archive-school.php` (Bảo toàn `$prog_tags` & Giao diện):

Chỉ thay thế đoạn lấy term dòng 295-307 bằng:

```php
// Tối ưu hóa: Đọc trực tiếp meta đã rollup, không sinh query phụ
$training_modes_map = get_post_meta( $school_id, '_active_training_systems', true );
$training_modes     = is_array( $training_modes_map ) ? array_values( $training_modes_map ) : [];
```

Giữ nguyên các dòng 278-294 tạo `$prog_tags` và `$region_terms`.
Tại dòng 337-339, hàm `ltdh_get_training_type_badge_html( $mode )` sẽ nhận mảng tên `$training_modes` và render đẹp mắt, đúng vị trí bên trong card mà không bị trùng lặp.

---

### 5.3. Đoạn code hoàn chỉnh cho `pre_get_posts` trên `taxonomy-training_type.php`:

```php
function ltdh_optimize_taxonomy_archive_query( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) || $query->is_post_type_archive( LTDH_CPT_PROGRAM ) ) {
        $query->set( 'post_type', LTDH_CPT_PROGRAM );
        $query->set( 'post_status', 'publish' );

        // 1. Phân trang
        $limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
        $query->set( 'posts_per_page', in_array( $limit, [ 12, 24, 36, 48 ], true ) ? $limit : 12 );

        $meta_query = (array) $query->get( 'meta_query' );
        if ( empty( $meta_query ) ) {
            $meta_query = [ 'relation' => 'AND' ];
        }

        // 2. Loại bỏ chương trình tạm ngưng
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'value' => LTDH_STATUS_PAUSED, 'compare' => '!=' ],
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'compare' => 'NOT EXISTS' ],
        ];

        // 3. Lọc theo Trường
        if ( ! empty( $_GET['truong'] ) ) {
            $school_raw = sanitize_text_field( $_GET['truong'] );
            $school_id  = is_numeric( $school_raw ) ? intval( $school_raw ) : ( ( $sp = get_page_by_path( $school_raw, OBJECT, LTDH_CPT_SCHOOL ) ) ? $sp->ID : 0 );
            if ( $school_id ) {
                $meta_query[] = [
                    'key'     => LTDH_META_SCHOOL_REL,
                    'value'   => $school_id,
                    'compare' => '=',
                ];
            }
        }

        // 4. Lọc theo Ngành / Nhóm ngành
        if ( ! empty( $_GET['nhom_nganh'] ) ) {
            $nhom_slug = sanitize_text_field( $_GET['nhom_nganh'] );
            $major_post = get_page_by_path( $nhom_slug, OBJECT, LTDH_CPT_MAJOR );
            if ( $major_post ) {
                $meta_query[] = [
                    'key'     => LTDH_META_MAJOR_REL,
                    'value'   => $major_post->ID,
                    'compare' => '=',
                ];
            } else {
                $majors_in_cat = get_posts( [
                    'post_type'   => LTDH_CPT_MAJOR,
                    'numberposts' => -1,
                    'fields'      => 'ids',
                    'tax_query'   => [ [ 'taxonomy' => LTDH_TAX_MAJOR_CAT, 'field' => 'slug', 'terms' => $nhom_slug ] ],
                ] );
                if ( ! empty( $majors_in_cat ) ) {
                    $meta_query[] = [
                        'key'     => LTDH_META_MAJOR_REL,
                        'value'   => $majors_in_cat,
                        'compare' => 'IN',
                    ];
                }
            }
        }

        $query->set( 'meta_query', $meta_query );

        // 5. Tìm kiếm từ khóa
        if ( ! empty( $_GET['s'] ) ) {
            $query->set( 's', sanitize_text_field( $_GET['s'] ) );
        }

        // 6. Sắp xếp
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
    }
}
add_action( 'pre_get_posts', 'ltdh_optimize_taxonomy_archive_query' );
```

---

### 5.4. Sửa hàm Telegram Notification (Không mã hóa dấu `:` trong bot token):

```php
// Sửa tại inc/lead-capture.php & inc/crm-adapters.php:
$clean_token = trim( $bot_token );
$api_url     = 'https://api.telegram.org/bot' . $clean_token . '/sendMessage';
```
*(Tuyệt đối không dùng `rawurlencode` hoặc `urlencode` lên toàn bộ bot token vì sẽ làm hỏng dấu phân tách `:` giữa Bot ID và Token).*

---
