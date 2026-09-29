# BÁO CÁO BÀN GIAO CẬP NHẬT KIỂM TOÁN (ITERATION 2 HANDOFF REPORT)
## HOÀN TẤT HIỆU CHỈNH TOÀN DIỆN TÀI LIỆU `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`

- **Agent thực hiện**: `worker_audit_3_iter2` (teamwork_preview_worker)
- **Vai trò kích hoạt**: implementer, qa, specialist
- **Thư mục làm việc**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_audit_3_iter2/`
- **Người nhận bàn giao**: `orchestrator_3` (Caller ID: `8ecd8568-917b-4973-87b0-609a66bbbf3b`)
- **Tập tin đã cập nhật**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`
- **Cam kết ràng buộc**: **ZERO MODIFICATION OF THEME SOURCE CODE** (Không sửa đổi bất kỳ tệp PHP, JS, CSS, JSON nào của theme).

---

### 1. OBSERVATION (QUAN SÁT THỰC NGHIỆM TRỰC TIẾP)

Sau khi rà soát các phát hiện đối kháng từ `challenger_audit_3_1` (`analysis.md` và `handoff.md`) và `challenger_audit_3_2` (`analysis.md` và `handoff.md`), đồng thời kiểm chứng thực tế trên mã nguồn theme, chúng tôi ghi nhận các quan sát cụ thể như sau:

1. **Section 2.4 (`LTDH_Entity_Relationship_Engine`)**:
   - Trước khi sửa: Hook lắng nghe `wp_trash_post` và `untrash_post`. Trong WordPress Core (`wp-includes/post.php`), `wp_trash_post` kích hoạt trước khi trường `post_status` được cập nhật thành `'trash'`. Khi `rebuild_entity_programs_cache` chạy query `'post_status' => 'publish'`, bài viết vẫn còn status publish nên bị ghi ngược trở lại `_offered_programs`. Tương tự, `before_delete_post` chạy trước khi post bị xóa khỏi database nên ID của post chuẩn bị xóa vẫn bị nạp vào mảng cache. Đồng thời hook `save_post_program` thiếu hook `acf/save_post` priority 25, dẫn đến việc đọc stale meta khi biên tập viên lưu bài trong WP-Admin.
   - Trích xuất cũ tại dòng 300-307:
     ```php
     add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20, 2 );
     add_action( 'wp_trash_post', [ __CLASS__, 'on_program_lifecycle_change' ] );
     add_action( 'untrash_post', [ __CLASS__, 'on_program_lifecycle_change' ] );
     add_action( 'before_delete_post', [ __CLASS__, 'on_program_delete' ] );
     add_action( 'before_delete_post', [ __CLASS__, 'on_parent_entity_delete' ] );
     ```

2. **Section 3.7.1 (`archive-school.php` List View Snippet)**:
   - Trước khi sửa: Hướng dẫn thay thế toàn bộ dòng 265-307 bằng đoạn mã `echo <span class="inline-flex...`.
   - Quan sát thực tế tại `archive-school.php`: Dòng 278-290 là đoạn code khởi tạo `$prog_tags` (lấy top 5 ngành học tiêu biểu), và dòng 292-293 là đoạn lấy `$region_terms`. Dòng 345 truy cập `if ( ! empty( $prog_tags ) )`. Việc xóa dòng 265-307 khiến `$prog_tags` không tồn tại, gây ra `PHP Warning: Undefined variable $prog_tags` trên PHP 8.1 - 8.4 và làm biến mất danh sách tag ngành học. Đồng thời, in thẻ `<span>` trước thẻ mở card (dòng 309) khiến badge bị render ngoài khối container. Trong khi đó, dòng 295-307 mới chính là vòng lặp foreach N+1 query gọi `wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE )`, và dòng 337-339 bên trong card đã có sẵn hàm `ltdh_get_training_type_badge_html( $mode )` để in badge.

3. **Section 3.7.2 (`pre_get_posts` trên `taxonomy-training_type.php`)**:
   - Trước khi sửa: Hàm `ltdh_optimize_taxonomy_archive_query` chỉ xử lý `limit` và loại bỏ `tam-ngung`.
   - Quan sát thực tế tại `taxonomy-training_type.php:15-125`: Bộ lọc hỗ trợ các tham số người dùng nhập: `$_GET['truong']` (lọc theo trường đối tác), `$_GET['nhom_nganh']` hoặc `$_GET['nganh']` (lọc theo ngành hoặc nhóm ngành CPT `major` / taxonomy `major_cat`), `$_GET['s']` (tìm kiếm từ khóa), và `$_GET['sort']` (`title_asc`, `title_desc`, `date_desc`). Đoạn code cũ trong audit bỏ rơi 100% các tham số này, khiến bộ lọc của người dùng bị vô hiệu hóa khi chuyển sang Main Query.

4. **Section 3.7.4 (Rank Math Canonical URL filter)**:
   - Trước khi sửa: Snippet kiểm tra đường dẫn `#^/he-dao-tao/?$#i`.
   - Quan sát thực tế: CPT `program` có slug archive là `chuong-trinh`. Rank Math mặc định sinh canonical trỏ về `domain.com/chuong-trinh/`. Tuy nhiên `class-rewrite-rules.php:154` lại redirect 301 từ `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`. Snippet cũ kiểm tra `/he-dao-tao/` không hề bắt được request `/chuong-trinh/`, do đó không khắc phục được lỗi 301 loop của thẻ canonical.

5. **Section 5.6.3 (`ltdh_trigger_telegram_notification_v2`)**:
   - Trước khi sửa: Đoạn mã sử dụng `rawurlencode( $bot_token )` và ghi đè Chat ID trường lên Chat ID tổng.
   - Quan sát thực nghiệm trên PHP: Token Telegram chuẩn có dạng `123456789:ABCdefgh...`. Hàm `rawurlencode()` biến dấu `:` thành `%3A`, khiến request tới `api.telegram.org` trả về HTTP 404 Not Found do không nhận diện được bot ID. Đồng thời logic `if ($school_id) { $chat_id = $school_chat; }` cắt đứt luồng thông báo về Admin Trung tâm.

6. **Section 5.6 & 7.2 (Script SQL Migration cho `wp_ltdh_leads`)**:
   - Trước khi sửa: Script DDL dạng raw SQL `ALTER TABLE wp_ltdh_leads ADD COLUMN...`.
   - Quan sát thực tế: Script hardcode prefix `wp_`, không kiểm tra xem cột đã tồn tại hay chưa (gây lỗi fatal `Duplicate column name` nếu chạy lại), và hoàn toàn không có lệnh Backfill dữ liệu từ `error_message` sang `message`, khiến các lead đang chờ xử lý (`pending` hoặc `failed`) bị mất trắng lời nhắn và link ảnh bằng cấp khi chuyển sang đọc cột `message`.

---

### 2. LOGIC CHAIN (CHUỖI SUY LUẬN TỪ QUAN SÁT ĐẾN KẾT LUẬN)

1. **Xử lý Action Item 1 (Section 2.4)**:
   - Để trashing hoặc restoring một chương trình đào tạo phản ánh đúng trạng thái thực trong CSDL, hook phải chạy **SAU KHI** trạng thái đã được đổi trong DB $\rightarrow$ Sử dụng action hook `trashed_post` và `untrashed_post`.
   - Khi xóa vĩnh viễn (`before_delete_post`), bản ghi vẫn còn trong bảng `wp_posts` $\rightarrow$ Hàm `rebuild_entity_programs_cache` và `rollup_school_taxonomies` phải nhận mảng loại trừ `$exclude_ids = [ $post_id ]` và đưa vào tham số query `'post__not_in' => $exclude_ids` để ID đang xóa không bị nạp ngược vào cache.
   - Khi lưu trong WP-Admin, ACF ghi custom fields vào `wp_postmeta` sau hook `save_post` chuẩn $\rightarrow$ Lắng nghe `acf/save_post` với priority 25 để đảm bảo meta đã được ghi xuống DB, đồng thời duy trì `save_post_program` với priority 25 để hỗ trợ programmatic/REST/CLI save.
   - Khi thực thể cha bị xóa, tạm ngắt hook `save_post_program` trong lúc đổi status các bài con sang draft để ngăn chặn bão truy vấn (Query Storm).

2. **Xử lý Action Item 2 (Section 3.7.1)**:
   - Đoạn N+1 query thực sự chỉ nằm ở dòng 295-307 (`foreach $offered_program_ids` gọi `wp_get_post_terms`).
   - Các dòng 278-290 tạo biến `$prog_tags` cho danh sách 5 tag ngành học ở dòng 345; các dòng 292-293 lấy `$region_terms` cho dòng 328.
   - Do đó, **chỉ thay thế duy nhất dòng 295-307** bằng việc đọc trực tiếp `_active_training_systems` đã được rollup. Việc này triệt tiêu hoàn toàn 396 queries N+1, bảo toàn nguyên vẹn `$prog_tags` (không gây lỗi `Undefined variable` trên PHP 8) và để hàm `ltdh_get_training_type_badge_html()` ở dòng 337-339 tự động render badge đúng vị trí bên trong card. Cập nhật tương ứng số dòng tại bảng kế hoạch Phase 1 (STT 1.7).

3. **Xử lý Action Item 3 (Section 3.7.2)**:
   - Để chuyển từ custom `WP_Query` sang Main Query qua `pre_get_posts` mà không gây suy giảm chức năng, toàn bộ các tham số lọc của người dùng trên `taxonomy-training_type.php` phải được phân giải trong hook:
     - `$_GET['truong']`: Phân giải qua numeric ID hoặc `get_page_by_path(..., 'school')` và đưa vào `meta_query` (`school_relationship`).
     - `$_GET['nhom_nganh']` / `$_GET['nganh']`: Phân giải qua CPT `major` hoặc taxonomy `major_cat` và đưa vào `meta_query` (`major_relationship`).
     - `$_GET['s']`: Gán vào `$query->set('s', ...)`.
     - `$_GET['sort']`: Gán `orderby` và `order` (`title_asc`, `title_desc`, `date_desc`).
     - Phân trang: Sử dụng `$wp_query->max_num_pages` thay vì `$query->max_num_pages` để loại bỏ Undefined variable notice.

4. **Xử lý Action Item 4 (Section 3.7.4)**:
   - Lỗi canonical loop xảy ra tại Archive CPT `program` (`/chuong-trinh/`). Do đó, filter `rank_math/frontend/canonical_url` phải kiểm tra điều kiện `is_post_type_archive( 'program' )` và regex kiểm tra path `#^/chuong-trinh/?$#i`, trả về URL đích hợp lệ `home_url( '/he-dao-tao/tu-xa/' )`.

5. **Xử lý Action Item 5 (Section 5.6.3)**:
   - Ký tự `:` là delimiter chuẩn bắt buộc của Telegram Bot Token. Loại bỏ `rawurlencode()` và sử dụng chuỗi `$clean_token = trim( $bot_token )` trực tiếp trong URL endpoint `https://api.telegram.org/bot{$clean_token}/sendMessage`.
   - Triển khai cơ chế Multi-cast: Gom cả `$global_chat` và `$school_chat`, loại bỏ trùng lặp bằng `array_unique()`, và gửi thông báo đồng thời tới tất cả các Chat ID trong danh sách. Ban Quản trị Trung tâm không bao giờ bị mất dấu vết lead.

6. **Xử lý Action Item 6 (Section 5.6 & 7.2)**:
   - Thay thế raw SQL bằng hàm PHP Migration an toàn `ltdh_migrate_leads_table_v2()`.
   - Sử dụng dynamic table prefix: `$table_name = $wpdb->prefix . LTDH_TABLE_LEADS;`.
   - Kiểm tra cấu trúc cột qua `$existing_cols = $wpdb->get_col("DESC {$table_name}", 0)` trước khi chạy `ALTER TABLE`.
   - Đưa vào câu lệnh Backfill dữ liệu lịch sử bắt buộc:
     `UPDATE {$table_name} SET message = error_message WHERE (message IS NULL OR message = '') AND error_message != '' AND sync_status != 'synced';`.
   - Cập nhật dòng STT 2.1 trong Bảng 7.2 Phase 2 để đồng bộ yêu cầu migration an toàn.

---

### 3. CAVEATS (GIỚI HẠN VÀ ĐIỀU KIỆN BIÊN)

1. **Phạm vi chỉnh sửa**: Đúng theo chỉ thị nghiêm ngặt của dự án, 100% các sửa đổi chỉ thực hiện trên tệp tài liệu `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`. Không có bất kỳ tệp mã nguồn theme nào (PHP, JS, CSS, JSON) bị can thiệp.
2. **Triển khai thực tế**: Khi đội ngũ kỹ sư bước vào giai đoạn code thực tế (Phase 1 & Phase 2), cần chạy migration hàm `ltdh_migrate_leads_table_v2()` qua WP-CLI hoặc hook cập nhật database version của theme trước khi chuyển đổi logic đọc cột `message`.

---

### 4. CONCLUSION (KẾT LUẬN & ĐÁNH GIÁ NGHIỆM THU)

Toàn bộ **6 Action Items** được yêu cầu trong lệnh Dispatch đã được cập nhật hoàn chỉnh, chính xác 100% vào tài liệu `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`:

| STT | Hạng mục khắc phục | Vị trí cập nhật trong tài liệu | Tình trạng nghiệm thu |
|---|---|---|---|
| **1** | `LTDH_Entity_Relationship_Engine` Lifecycle & ACF Timing | Section 2.4 (dòng 281-480) | ✅ **HOÀN THÀNH**: Dùng `trashed_post`, `untrashed_post`, `before_delete_post` với `'post__not_in' => [$post_id]`, và hook lưu với priority 25. |
| **2** | Tối ưu N+1 query tại `archive-school.php` | Section 3.7.1 (dòng 809-838) & Bảng 7.1 row 1.7 | ✅ **HOÀN THÀNH**: Chỉ thay thế dòng 295-307, bảo toàn `$prog_tags` (tránh warning PHP 8) và `$region_terms`. |
| **3** | `pre_get_posts` trên `taxonomy-training_type.php` | Section 3.7.2 (dòng 840-968) | ✅ **HOÀN THÀNH**: Bảo toàn 100% các bộ lọc `$_GET['nhom_nganh']`/`$_GET['nganh']`, `$_GET['truong']`, `$_GET['s']`, `$_GET['sort']`. |
| **4** | Rank Math Canonical URL filter | Section 3.7.4 (dòng 978-1002) | ✅ **HOÀN THÀNH**: Nhắm đúng mục tiêu `is_post_type_archive('program')` và regex `/chuong-trinh/` giải quyết triệt để 301 loop. |
| **5** | Sửa Telegram Notification v2 | Section 5.6.3 (dòng 1344-1437) | ✅ **HOÀN THÀNH**: Bỏ `rawurlencode` giữ nguyên token delimiter `:`, triển khai Multi-cast đồng thời về Admin và Nhóm Trường. |
| **6** | PHP DDL Migration an toàn & Data Backfill | Section 5.6.1 (dòng 1231-1317) & Bảng 7.2 row 2.1 | ✅ **HOÀN THÀNH**: Dynamic prefix `$wpdb->prefix`, kiểm tra cấu trúc cột, và câu lệnh backfill phục hồi dữ liệu lịch sử. |

Tài liệu `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` hiện tại đã đạt chuẩn kỹ thuật cao cấp nhất, hoàn toàn loại bỏ các sai sót vòng đời và xung đột cú pháp, sẵn sàng làm cơ sở kiến trúc vững chắc cho các kỹ sư triển khai và thanh tra hệ thống.

---

### 5. VERIFICATION METHOD (PHƯƠNG PHÁP XÁC MINH ĐỘC LẬP)

Kiểm toán viên độc lập hoặc Orchestrator có thể kiểm tra trực tiếp tài liệu theo các bước sau:

1. **Xác minh Section 2.4**:
   Mở tệp `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` tại dòng 300-315. Xác nhận:
   - `add_action( 'acf/save_post', [ __CLASS__, 'on_acf_save_program' ], 25 );`
   - `add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 25, 2 );`
   - `add_action( 'trashed_post', [ __CLASS__, 'on_program_status_changed' ] );`
   - `add_action( 'untrashed_post', [ __CLASS__, 'on_program_status_changed' ] );`
   - Tại dòng 470: `$args['post__not_in'] = $exclude_ids;`.

2. **Xác minh Section 3.7.1**:
   Mở dòng 809-838. Xác nhận: Hướng dẫn nêu rõ *"Thay thế duy nhất khối truy vấn term N+1 tại dòng 295-307, bảo toàn nguyên vẹn $prog_tags và $region_terms"*.

3. **Xác minh Section 3.7.2**:
   Mở dòng 840-940. Xác nhận: Hàm `ltdh_optimize_taxonomy_archive_query` chứa đầy đủ các khối xử lý:
   - `if ( ! empty( $_GET['truong'] ) )`
   - `if ( ! empty( $nhom_slug ) )`
   - `if ( ! empty( $_GET['s'] ) )`
   - `if ( 'title_asc' === $sort ) ...`

4. **Xác minh Section 3.7.4**:
   Mở dòng 980-1000. Xác nhận filter chứa `is_post_type_archive( LTDH_CPT_PROGRAM )` và `preg_match( '#^/chuong-trinh/?$#i', $path )`.

5. **Xác minh Section 5.6.3**:
   Mở dòng 1345-1435. Xác nhận:
   - Không chứa chuỗi `rawurlencode( $bot_token )`, thay vào đó là `$api_url = "https://api.telegram.org/bot{$clean_token}/sendMessage";` với `$clean_token = trim( $bot_token );`.
   - Chứa mảng `$chat_targets` gom cả `$global_chat` và `$school_chat`, sau đó loop `$final_chat_ids` gửi multi-cast.

6. **Xác minh Section 5.6.1 & 7.2**:
   Mở dòng 1235-1315. Xác nhận: Hàm `ltdh_migrate_leads_table_v2()` dùng `$wpdb->prefix . LTDH_TABLE_LEADS`, kiểm tra `$existing_cols`, và chứa câu truy vấn:
   ```sql
   UPDATE {$table_name} 
   SET message = error_message 
   WHERE (message IS NULL OR message = '') 
     AND error_message != '' 
     AND sync_status != 'synced';
   ```
   và dòng STT 2.1 bảng Phase 2 ghi nhận migration an toàn kèm backfill dữ liệu.

7. **Xác minh Zero Theme Source Code Modification**:
   Kiểm tra modified timestamps hoặc nội dung các file `archive-school.php`, `taxonomy-training_type.php`, `front-page.php`, `inc/lead-capture.php`, `inc/crm-adapters.php`... Xác nhận không có bất kỳ thay đổi nào trên mã nguồn theme.
