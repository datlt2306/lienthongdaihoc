# HANDOFF REPORT — REQUIREMENT R1 TECHNICAL AUDIT
## Data Architecture & Entity Modeling for Training Systems (`training_type`) and Partner Universities (`school`)

**Agent ID:** `explorer_survey_arch_1`  
**Working Directory:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_arch_1/`  
**Target Recipient:** `orchestrator_3` (ID: `8ecd8568-917b-4973-87b0-609a66bbbf3b`)  
**Artifact File:** `analysis.md` (Tài liệu phân tích chuyên sâu 8 phần đầy đủ)

---

### 1. OBSERVATION

Dưới đây là các quan sát trực tiếp, trích xuất chính xác theo từng đường dẫn tệp tin và số dòng code trong dự án:

1. **Đăng ký thực thể và đối tượng Taxonomy (`inc/acf-import-cpts.json` & `inc/post-types.php:15-108`):**
   - Dòng 170-176: Taxonomy `training_type` được khai báo với `"object_type": [ "program" ]`. Taxonomy `campus` cũng chỉ khai báo `"object_type": [ "program" ]` (dòng 209-215).
   - Tuyệt nhiên **không có `school`** trong danh sách `object_type` của cả hai taxonomy này.
   - CPT `program` có `"rewrite_slug": ""` (dòng 148).
2. **Lỗi logic hiển thị tại `archive-school.php` (dòng 80-81 & 265-307):**
   - Dòng 80: `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );`
     Vì `training_type` không được gán cho `school`, hàm này luôn trả về mảng rỗng `[]`. Khối Featured Schools trên đầu trang bị mất trắng badge hệ đào tạo.
   - Dòng 265-276: Tại vòng lặp danh sách thường, mã nguồn phải chạy `get_posts` phụ cho từng trường (`'key' => 'school_relationship', 'value' => $school_id`), rồi lặp qua từng program để lấy `training_type` (dòng 297-306). Với 20 trường trên 1 trang, sinh ra ít nhất 20 query `get_posts` + 200 query taxonomy terms phụ (N+1 query).
3. **Cơ chế đồng bộ hai chiều đứt đoạn (`inc/relationship-hooks.php:12-74`):**
   - Chỉ lắng nghe duy nhất `add_action( 'acf/save_post', 'ltdh_sync_program_relationships', 20 );` trên `program`.
   - Không có bất kỳ hook nào lắng nghe `before_delete_post`, `wp_trash_post`, hay `untrash_post`. Khi xóa 1 program, ID của nó vẫn tồn tại vĩnh viễn trong mảng meta `_offered_programs` của School và Major.
   - Dòng 46 & 71: Hardcode chuỗi `'_offered_programs'` và dùng `array_diff` không reset index mảng, dẫn đến mảng serialize có index rời rạc.
4. **Phân rã thực thể giữa Taxonomy và ACF Checkbox (`inc/acf-import-fields.json:611-640` & `inc/eligibility.php`):**
   - Nhóm `group_program_eligibility` định nghĩa 2 trường checkbox tĩnh `elig_training_types` và `elig_campuses`.
   - Tại `inc/eligibility.php:428`, mã nguồn vừa đọc taxonomy: `$prog_training_types = wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'slugs' ] );`
   - Tại `inc/eligibility.php:473`, mã nguồn lại so sánh với ACF field: `if ( ! in_array( $input['training_type'], $allowed_types, true ) )`. Nếu admin cập nhật taxonomy mà không tick ACF checkbox, ứng viên bị đánh rớt điều kiện xét tuyển.
5. **Hijacking không gian tên URL toàn site (`inc/core/class-rewrite-rules.php:41-101`):**
   - Dòng 42: `add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );` bắt mọi URL cấp 1.
   - Dòng 62-98 (`ltdh_program_request_guard`): Mọi request HTTP (kể cả Page tĩnh, Post tin tức, hoặc 404) đều kích hoạt tối thiểu 1-2 truy vấn `get_posts` kiểm tra CPT `program` và `post`.
6. **Đợt tuyển sinh và Biểu phí thiếu cấu trúc định lượng (`inc/acf-import-fields.json:190-252, 291-354`):**
   - `duration` là chuỗi text tự do (dòng 250). `tuition_amount` là số đơn lẻ (dòng 198), không chia theo bậc văn bằng đầu vào (THPT vs Cao đẳng vs VB2).
   - `admission_batches` repeater chứa các chuỗi text tự do (`release_period`, `application_period`, `enrollment_time`). Không có trường ngày tháng định dạng chuẩn (`Y-m-d`), không thể tự động hóa việc đóng/mở đợt theo thời gian thực.
7. **Bảng cơ sở dữ liệu `wp_ltdh_leads` thiếu chỉ mục khóa ngoại (`inc/lead-capture.php:22-40`):**
   - Bảng chỉ có khóa chính `id` và index `sync_status`. Hoàn toàn không có index trên `school_id`, `program_id`, hay `created_at`.
   - Bộ đồng bộ CRM (`inc/crm-adapters.php:91-124`) chỉ hỗ trợ 1 endpoint duy nhất toàn cục, không hỗ trợ phân luồng theo Trường đối tác.

---

### 2. LOGIC CHAIN

1. **Từ Quan sát 1 và 2:**
   - Việc chỉ đăng ký `training_type` cho `program` khiến `school` không có quan hệ taxonomy trực tiếp.
   - Do đó, câu lệnh `wp_get_post_terms($school_id, 'training_type')` tại `archive-school.php:80` bắt buộc trả về rỗng, gây lỗi mất badge trên Featured Schools.
   - Để lấy lại thông tin này trên danh sách thường, lập trình viên buộc phải lặp qua từng chương trình con bằng subquery, tạo ra lỗi N+1 query nghiêm trọng làm chậm tốc độ tải trang.
2. **Từ Quan sát 3:**
   - Hook quan hệ hai chiều chỉ kích hoạt khi bài viết được lưu qua giao diện ACF (`acf/save_post`), hoàn toàn bỏ quên vòng đời xóa/thùng rác.
   - Khi quản trị viên xóa một chương trình, ID của nó vẫn lưu lại trong `_offered_programs` của School và Major.
   - Khi các template gọi `WP_Query` với `post__in => $offered_program_ids`, các ID mồ côi này gây lãng phí tài nguyên truy vấn và làm sai lệch bộ đếm ngành/chương trình (`ltdh_get_school_unique_majors_count`).
3. **Từ Quan sát 4:**
   - Cùng một khái niệm "Hệ đào tạo" và "Cơ sở" nhưng vừa lưu dưới dạng Taxonomy Terms, vừa lưu dưới dạng ACF Checkbox options độc lập.
   - Hai nguồn dữ liệu này không có cơ chế đồng bộ, tạo ra hiện tượng "phân rã thực thể" (Split-Brain). Khi người nhập liệu chỉ chọn một bên, module Eligibility Check sẽ hoạt động sai lệch.
4. **Từ Quan sát 5:**
   - Quy tắc rewrite `([^/]+)/?$` can thiệp vào mức ưu tiên cao nhất của router WordPress.
   - Mọi truy cập vào trang web đều phải qua hàm guard thực thi 1-2 truy vấn SQL trước khi WordPress biết được trang đó là gì. Đây là nguyên nhân suy giảm TTFB (Time to First Byte) trên toàn hệ thống.
5. **Từ Quan sát 6 và Nghiệp vụ Tuyển sinh VN:**
   - Thực tế tuyển sinh liên thông/từ xa tại Việt Nam có thời gian học và học phí biến thiên mạnh theo văn bằng đầu vào (THPT 4 năm, CĐ đúng ngành 1.5 năm, VB2 1.5 năm).
   - Việc chỉ lưu một giá trị `duration` và `tuition_amount` duy nhất trên `program` không phản ánh được thực tế, gây hiểu lầm cho người học và làm sai lệch thuật toán gợi ý chương trình.
6. **Từ Quan sát 7:**
   - Bảng leads lưu hàng nghìn liên hệ nhưng thiếu index trên `school_id` và `created_at` sẽ gây chậm khi xuất báo cáo hoặc lọc theo trường trong WP-Admin.
   - Thiếu cấu hình CRM theo từng trường khiến hệ thống không thể phân luồng lead cho các trường đối tác sử dụng nền tảng khác nhau (OnSchool, AUM, hay CRM riêng).

---

### 3. CAVEATS

1. **Giới hạn môi trường Read-Only:** Quá trình kiểm định là 100% phân tích tĩnh và đọc mã nguồn, không thực hiện thay đổi trên mã theme thật.
2. **Dữ liệu thực tế trên môi trường Production:** Kiểm tra trên mã nguồn theme cho thấy các trường dữ liệu và quan hệ được định nghĩa qua JSON và code; tuy nhiên, số lượng bản ghi thực tế trong CSDL production (số lượng School, Major, Program hiện có) chưa được đo đạc trực tiếp bằng SQL client do tôn trọng quyền bảo mật.
3. **Không có mã nguồn của CRM bên thứ ba:** Phân tích giao tiếp CRM dựa trên mã nguồn của `inc/crm-adapters.php`; định dạng payload chính xác mà OnSchool hay AUM yêu cầu có thể cần đối chiếu thêm với tài liệu API chính thức của đối tác.

---

### 4. CONCLUSION

1. **Về mô hình thực thể tổng thể:**
   - Kiến trúc tam giác `School` ⟷ `Program` (Intermediate Entity) ⟷ `Major` là **đúng đắn và bắt buộc phải duy trì** để phản ánh chính xác nghiệp vụ tuyển sinh Việt Nam.
   - Việc gán `training_type` vào `program` là **chính xác**, nhưng cần phải bổ sung cơ chế **Rollup đồng bộ tự động lên `school`** để tối ưu hóa truy vấn và sửa dứt điểm lỗi hiển thị badge.
2. **Các điểm cần nâng cấp khẩn cấp:**
   - **Đăng ký đa thực thể:** Đăng ký `training_type` và `campus` cho cả `program` và `school`.
   - **Xây dựng `LTDH_Entity_Relationship_Engine`:** Thay thế `inc/relationship-hooks.php` để bao phủ 100% vòng đời bài viết (Save, Delete, Trash, Untrash), tự động dọn sạch orphan IDs và rollup taxonomy.
   - **Xóa bỏ Split-Brain Checkbox:** Thống nhất dùng Taxonomy `training_type` và `campus` làm nguồn chân lý duy nhất cho module Eligibility Check.
   - **Cấu trúc lại Ma trận Đầu vào (Input-Level Matrix):** Bổ sung repeater cho `program` chia theo 5 bậc học vấn đầu vào với thời gian, số tín chỉ và học phí ước tính riêng biệt.
   - **Số hóa Đợt tuyển sinh (Structured Batches):** Đưa các trường ngày tháng ISO (`Y-m-d`) vào `admission_batches` để kích hoạt cơ chế tự động đóng/mở đợt theo thời gian thực.
   - **Tối ưu hóa URL Routing:** Loại bỏ regex catch-all `([^/]+)/?$`, chuẩn hóa URL chương trình sang tiền tố `/chuong-trinh/%postname%/` kèm 301 redirect fallback.
   - **Đa đối tác Lead Routing:** Bổ sung cấu hình CRM riêng biệt trên từng CPT `school` và đánh index cho bảng `wp_ltdh_leads`.

---

### 5. VERIFICATION METHOD

Các kỹ sư và agent tiếp quản có thể xác minh độc lập các phát hiện trên thông qua các bước sau:

1. **Xác minh lỗi "Ghost Training Type" trên trang Trường đối tác:**
   - Kiểm tra mã nguồn tại `archive-school.php` dòng 80:
     ```php
     $school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
     ```
   - Chạy lệnh WP-CLI kiểm tra liên kết taxonomy của một trường đối tác bất kỳ:
     ```bash
     wp term list training_type --object_id=<ID_TRUONG>
     ```
     *Kết quả mong đợi:* Không có term nào được trả về, chứng minh badge trên Featured Schools luôn bị trống.
2. **Xác minh lỗi tham chiếu mồ côi (Orphan IDs):**
   - Tạo một program thử nghiệm gắn vào trường X.
   - Kiểm tra postmeta `_offered_programs` của trường X (sẽ chứa ID của program đó).
   - Xóa vĩnh viễn program đó (`wp post delete <ID_PROGRAM> --force`).
   - Kiểm tra lại postmeta `_offered_programs` của trường X:
     ```bash
     wp post meta get <ID_TRUONG> _offered_programs
     ```
     *Kết quả mong đợi:* ID đã bị xóa vẫn còn nằm nguyên trong mảng meta của trường X vì không có hook dọn dẹp khi xóa.
3. **Xác minh lỗi URL Namespace Hijacking:**
   - Mở tệp `inc/core/class-rewrite-rules.php`, dòng 42 và 62-98.
   - Gửi request đến một URL 404 bất kỳ (ví dụ `/duong-dan-khong-ton-tai-12345/`).
   - Bật Query Monitor hoặc log query MySQL: sẽ thấy 2 câu lệnh truy vấn tìm kiếm trong bảng `wp_posts` với `post_type = 'program'` và `post_type = 'post'` trước khi trả về 404.
4. **Kiểm tra cú pháp PHP & Test suite của theme:**
   - Chạy test hiện có của dự án:
     ```bash
     php tests/run-tests.php
     ```
   - Chạy lint cú pháp:
     ```bash
     find inc -name "*.php" -exec php -l {} \;
     ```
     *Kết quả mong đợi:* Không có lỗi cú pháp tĩnh, chứng minh các vấn đề phát hiện đều thuộc về tầng Kiến trúc & Mô hình dữ liệu (Architectural & Data Modeling Deficiencies).
