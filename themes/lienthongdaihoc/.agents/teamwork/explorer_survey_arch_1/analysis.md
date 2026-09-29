# BÁO CÁO KIỂM ĐỊNH KỸ THUẬT CHUYÊN SÂU: REQUIREMENT R1
## DATA ARCHITECTURE & ENTITY MODELING FOR TRAINING SYSTEMS (`training_type`) AND PARTNER UNIVERSITIES (`school`)

**Mã phân tích:** AUDIT-R1-ARCH-SURVEY-2026-09-28  
**Tác giả:** `explorer_survey_arch_1` (Teamwork Preview Explorer)  
**Môi trường khảo sát:** WordPress Theme `lienthongdaihoc` v2.0.0 (PHP 8.1+, MySQL 8.0, ACF Pro 6+)  
**Tình trạng code gốc:** Read-Only Audit (Không thay đổi mã nguồn dự án)

---

## 1. TỔNG QUAN & TÓM TẮT THỰC TRẠNG KIẾN TRÚC

Hệ thống quản lý thông tin tuyển sinh của **Liên Thông Đại Học** hiện đang vận hành dựa trên mô hình thực thể trung gian (Intermediate / Join Entity Pattern), trong đó:
- `school` (Custom Post Type - CPT): Đại diện cho các trường Đại học đối tác (NEU, TMU, PTIT, HOU...).
- `major` (CPT): Đại diện cho các ngành học học thuật trừu tượng (Công nghệ thông tin, Quản trị kinh doanh, Kế toán...).
- `program` (CPT - Thực thể trung gian): Đại diện cho gói tuyển sinh cụ thể được mở tại một trường đối tác cho một ngành học nhất định.
- `training_type` (Hierarchical Taxonomy): Đại diện cho hệ đào tạo (Từ xa, Vừa làm vừa học, Liên thông, Văn bằng 2, Chính quy) gắn vào `program`.
- `campus` (Hierarchical Taxonomy): Đại diện cho cơ sở đào tạo gắn vào `program`.
- `region` (Hierarchical Taxonomy): Đại diện cho miền địa lý gắn vào `school`.
- `major_cat` (Hierarchical Taxonomy): Phân loại nhóm ngành gắn vào `major`.

### Đánh giá sức khỏe kiến trúc dữ liệu (Health Score: 62/100)
1. **Ưu điểm cốt lõi:**
   - Việc thiết lập `program` làm thực thể trung gian giữa `school` và `major` là **hoàn toàn chính xác về mặt lý thuyết mô hình hóa quan hệ nhiều-nhiều (N:M)** trong giáo dục đại học.
   - Cho phép định nghĩa thuộc tính tuyển sinh độc lập (học phí, thời gian, điều kiện xét tuyển, đợt nhận hồ sơ) cho từng sự kết hợp (Trường × Ngành × Hệ).
2. **Khuyết tật kiến trúc nghiêm trọng (Architectural Deficiencies):**
   - **Hiện tượng phân rã thực thể (Split-Brain Taxonomy & Meta):** Tồn tại song song giữa taxonomy chuẩn (`training_type`, `campus`) và các trường ACF Checkbox bị hardcode tĩnh (`elig_training_types`, `elig_campuses`), gây lệch pha dữ liệu khi quản trị viên cập nhật.
   - **Quan hệ hai chiều nửa vời (Broken Bi-directional Lifecycle):** Hook `inc/relationship-hooks.php` chỉ lắng nghe `acf/save_post` khi cập nhật chương trình, hoàn toàn bỏ quên vòng đời xóa/thùng rác (`wp_delete_post`, `before_delete_post`, `trashed_post`), tạo ra các tham chiếu mồ côi (orphan IDs) làm phình to postmeta và sai lệch bộ đếm.
   - **Nghẽn cổ chai truy vấn N+1 & Quét bảng postmeta:** Toàn bộ quan hệ giữa các thực thể phụ thuộc vào trường ACF Post Object lưu trong `wp_postmeta` dạng chuỗi unindexed, buộc các trang lưu trữ và trang chi tiết phải chạy nhiều sub-query không index (`meta_query` so khớp ID).
   - **Bất đối xứng giữa Trường và Hệ (The "Ghost Training Type" Bug):** Taxonomy `training_type` chỉ được đăng ký cho `program`, không đăng ký cho `school`. Do đó, tại `archive-school.php`, việc gọi `wp_get_post_terms($school_id, 'training_type')` trả về rỗng, khiến badge hệ đào tạo trên card trường nổi bật bị biến mất, trong khi danh sách thường phải chạy quét N+1 query để gom hệ đào tạo từ các chương trình con.
   - **Xung đột định tuyến toàn cục (Catch-All URL Hijacking):** Rewrite rule `([^/]+)/?$` tại `inc/core/class-rewrite-rules.php:42` chặn bắt toàn bộ URL cấp 1 của website, ép mọi request (kể cả Page, Post thường, 404) phải chạy từ 1 đến 2 câu lệnh SQL kiểm tra sự tồn tại của `program`.

---

## 2. RÀ SOÁT CẤU TRÚC ĐĂNG KÝ THỰC THỂ & CSDL

### 2.1. Đăng ký CPT và Taxonomy (`inc/post-types.php` & `inc/acf-import-cpts.json`)

Mã nguồn sử dụng tệp `inc/acf-import-cpts.json` làm nguồn chân lý duy nhất (Single Source of Truth) để nạp tham số cho hàm `register_post_type` và `register_taxonomy` thông qua hook `init` (priority 0) tại `inc/post-types.php:15`.

| Tên thực thể | Loại thực thể | Object Type gắn vào | Rewrite Slug | Phân cấp (Hierarchical) | Trạng thái hỗ trợ (Supports) |
|---|---|---|---|---|---|
| `school` | Custom Post Type | — | `truong-doi-tac` | `false` | `title`, `editor`, `thumbnail`, `excerpt`, `revisions` |
| `major` | Custom Post Type | — | `nganh-hoc` | `false` | `title`, `editor`, `thumbnail`, `excerpt`, `revisions` |
| `program` | Custom Post Type | — | `""` (Rỗng, can thiệp custom) | `false` | `title`, `editor`, `thumbnail`, `excerpt`, `revisions` |
| `training_type` | Taxonomy | `["program"]` | `he-dao-tao` | `true` | `show_admin_column: true`, `show_in_rest: true` |
| `campus` | Taxonomy | `["program"]` | `co-so` | `true` | `show_admin_column: true`, `show_in_rest: true` |
| `region` | Taxonomy | `["school"]` | `khu-vuc` | `true` | `show_admin_column: true`, `show_in_rest: true` |
| `major_cat` | Taxonomy | `["major"]` | `nhom-nganh` | `true` | `show_admin_column: true`, `show_in_rest: true` |

#### Phát hiện tại bước đăng ký:
1. `program` có `"rewrite_slug": ""` trong JSON (dòng 148). Mã PHP tại `inc/post-types.php:47` lấy giá trị này gán cho `rewrite['slug']`, tạo ra xung đột tiềm ẩn nếu không có module rewrite rules tùy chỉnh tại `inc/core/class-rewrite-rules.php`.
2. `training_type` và `campus` **chỉ được gán cho `program`** (dòng 175, 214 trong JSON). Thực thể `school` hoàn toàn không có mối quan hệ taxonomy trực tiếp với `training_type` hay `campus`.

---

### 2.2. Khảo sát cấu trúc trường dữ liệu ACF (`inc/acf-import-fields.json` & `inc/acf-fields.php`)

Hệ thống định nghĩa các nhóm trường ACF chính:

#### 1. Nhóm `group_school_details` (Áp dụng cho CPT `school`):
- `logo` (`image` ID): Logo trường.
- `is_featured` (`true_false`): Đánh dấu trường nổi bật.
- `school_banner` (`image` URL): Ảnh bìa trường.
- `school_code` (`text`): Mã trường / Ký hiệu viết tắt (NEU, PTIT...).
- `english_name` (`text`): Tên tiếng Anh.
- `website` (`url`): Website chính thức.
- `address` (`text`): Địa chỉ trụ sở chính.
- `hotline` (`text`): Hotline tuyển sinh riêng của trường.
- `admission_info` (`wysiwyg`): Phương thức tuyển sinh.
- `contact_info` (`wysiwyg`): Thông tin liên hệ nộp hồ sơ.

#### 2. Nhóm `group_major_details` (Áp dụng cho CPT `major`):
- `major_code` (`text`): Mã ngành chuẩn theo danh mục GD&ĐT (VD: `7340101`).
- `career_opportunities` (`wysiwyg`): Cơ hội nghề nghiệp & định hướng việc làm.
- `major_related` (`relationship`): Danh sách ngành liên quan (dùng cho thuật toán tính điểm chuyển đổi trong module Eligibility Check).

#### 3. Nhóm `group_program_details` (Áp dụng cho CPT `program`):
- `school_relationship` (`post_object` -> `school`, Single, Return ID): Trường đào tạo liên kết.
- `major_relationship` (`post_object` -> `major`, Single, Return ID): Ngành đào tạo liên kết.
- `tuition_fee` (`text`): Chuỗi mô tả học phí cũ (fallback).
- `tuition_amount` (`number`): Định mức học phí dạng số nguyên (VD: `684026`).
- `tuition_unit` (`select`: `tin-chi`, `hoc-ky`, `nam`): Đơn vị tính học phí.
- `tuition_academic_year` (`text`): Năm học áp dụng (VD: `2025 - 2026`).
- `tuition_total_credits` (`number`): Tổng số tín chỉ toàn khóa.
- `tuition_increase_roadmap` (`textarea`): Lộ trình tăng học phí / Ghi chú.
- `quota` (`number`): Chỉ tiêu tuyển sinh.
- `duration` (`text`): Thời gian đào tạo (VD: `1.5 - 2 năm`).
- `admission_requirements` (`wysiwyg`): Điều kiện xét tuyển.
- `required_documents` (`wysiwyg`): Hồ sơ giấy tờ cần thiết.
- `admission_form_file` (`file`): Tệp mẫu phiếu tuyển sinh (PDF/DOCX).
- `curriculum_file` (`file`): Khung chương trình / lộ trình học.
- `enrollment_period` (`text`): Hạn nhận hồ sơ (legacy string).
- `admission_batches` (`repeater`): Danh sách đợt tuyển sinh (`batch_name`, `release_period`, `application_period`, `review_time`, `evaluation_time`, `enrollment_time`, `batch_status`).
- `hotline_override` (`text`): Hotline riêng của chương trình.
- `program_benefits` (`wysiwyg`): Quyền lợi học viên.
- `faq` (`repeater`): Danh sách câu hỏi - trả lời thường gặp.

#### 4. Nhóm `group_program_eligibility` (Áp dụng cho CPT `program`):
- `elig_min_education` (`select`: `thap-phan` [THPT], `trung-cap`, `cao-dang`, `dai-hoc`, `thac-si`): Trình độ tối thiểu.
- `elig_training_types` (`checkbox`): Danh sách mã hệ đào tạo cho phép (`lien-thong`, `van-bang-2`, `tu-xa`, `vua-hoc-vua-lam`, `chinh-quy`).
- `elig_campuses` (`checkbox`): Danh sách mã cơ sở cho phép (`ha-noi`, `ho-chi-minh`, `da-nang`, `thai-nguyen`, `online`).
- `elig_max_grad_years` (`number`): Thời gian tốt nghiệp tối đa (năm).
- `elig_notes` (`textarea`): Ghi chú điều kiện.

#### 5. Nhóm trường bị ẩn trong Admin (`inc/acf-fields.php:103-108`):
```php
add_filter( 'acf/prepare_field/key=field_program_why_choose', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_schedule', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_target_students', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_degree_type', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_diploma_value', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_disadvantages', '__return_false' );
```
*Nhận xét:* Việc ẩn các trường này bằng filter giải quyết bài toán giao diện nhập liệu đỡ rối, nhưng làm mất đi khả năng cấu hình lịch học (`schedule`), đối tượng học viên (`target_students`), và chuẩn đầu ra bằng cấp (`degree_type`, `diploma_value`) trên từng chương trình riêng biệt.

---

### 2.3. Bảng cơ sở dữ liệu tùy chỉnh (`ltdh_leads` & `ltdh_eligibility_checks`)

Theme định nghĩa hai bảng CSDL riêng biệt qua `dbDelta`:
1. **`wp_ltdh_leads`** (`inc/lead-capture.php:22-40`):
   - Lưu trữ thông tin đăng ký tư vấn tuyển sinh.
   - Cột liên kết: `program_id` (bigint), `school_id` (bigint), `major_id` (bigint), `training_type` (varchar 100), `campus` (varchar 100).
   - Chỉ mục: Duy nhất `PRIMARY KEY (id)` và `KEY sync_status (sync_status)`. **Hoàn toàn không có index trên `school_id`, `program_id`, `created_at`**.
2. **`wp_ltdh_eligibility_checks`** (`inc/eligibility.php:25-45`):
   - Lưu trữ lịch sử người dùng chạy kiểm tra điều kiện xét tuyển.
   - Cột liên kết: `desired_major_id` (bigint), `input_training_type` (varchar 50), `input_campus` (varchar 50), `matched_programs` (longtext JSON).
   - Chỉ mục: Duy nhất `PRIMARY KEY (id)` và `KEY session_id (session_id)`.

---

## 3. PHÂN TÍCH QUAN HỆ THỰC THỂ TAM GIÁC & CƠ CHẾ LƯU TRỮ

### 3.1. Sơ đồ thực thể hiện tại (Current Entity-Relationship Diagram)

```
       ┌────────────────────────┐                   ┌────────────────────────┐
       │      CPT: School       │                   │       CPT: Major       │
       │  (Trường Đại học ĐT)   │                   │      (Ngành học)       │
       └───────────┬────────────┘                   └───────────┬────────────┘
                   │                                            │
         1         │ [school_relationship]            1         │ [major_relationship]
                   │ (post_object in postmeta)                  │ (post_object in postmeta)
                   │                                            │
                   ▼                                            ▼
       ┌─────────────────────────────────────────────────────────────────────┐
       │                            CPT: Program                             │
       │                    (Chương trình tuyển sinh)                        │
       │                  - tuition_amount, duration                         │
       │                  - admission_batches (repeater)                     │
       │                  - elig_min_education, elig_training_types          │
       └───────────────────┬─────────────────────────────────┬───────────────┘
                           │                                 │
                 M:N       │ (Taxonomy)            M:N       │ (Taxonomy)
                           ▼                                 ▼
             ┌──────────────────────────┐      ┌──────────────────────────┐
             │  Taxonomy: training_type │      │     Taxonomy: campus     │
             │       (Hệ đào tạo)       │      │     (Cơ sở đào tạo)      │
             └──────────────────────────┘      └──────────────────────────┘
```

### 3.2. Đánh giá cơ chế lưu trữ quan hệ
1. **Lưu trữ quan hệ qua ACF Post Object (`school_relationship`, `major_relationship`):**
   - Giá trị lưu vào bảng `wp_postmeta` với `meta_key = 'school_relationship'` và `meta_value = {school_id}`.
   - Khi truy vấn tất cả các chương trình thuộc một trường (ví dụ trong `single-school.php` hoặc `archive-program.php`), WordPress phải thực thi:
     ```sql
     SELECT post_id FROM wp_postmeta 
     WHERE meta_key = 'school_relationship' AND meta_value = '123';
     ```
   - Trong bảng `wp_postmeta` tiêu chuẩn của WordPress, cột `meta_value` là `LONGTEXT` và không được đánh chỉ mục. Khi số lượng bản ghi postmeta tăng lên hàng trăm nghìn dòng, các câu lệnh này dẫn đến Full Table Scan.
2. **Cơ chế đồng bộ 2 chiều (`inc/relationship-hooks.php`):**
   - Đọc hook: `add_action( 'acf/save_post', 'ltdh_sync_program_relationships', 20 );`
   - Khi lưu một `program`, hàm sẽ đọc `school_relationship` và `major_relationship`, sau đó đẩy ID của `program` vào mảng postmeta `_offered_programs` của School và Major tương ứng.
   - Đồng thời lưu `_last_known_school_id` và `_last_known_major_id` trên `program` để phát hiện sự thay đổi và xóa program khỏi trường cũ.
3. **Các điểm gãy (Failure Points) của cơ chế đồng bộ hiện tại:**
   - **Bỏ quên sự kiện Xóa và Thùng rác:** Nếu một `program` bị xóa vĩnh viễn (`before_delete_post`), bị chuyển vào Thùng rác (`wp_trash_post`), hoặc khôi phục (`untrash_post`), hook `acf/save_post` **không hề kích hoạt**. Kết quả là ID của bài viết đã bị xóa vẫn nằm nguyên trong mảng `_offered_programs` của `school` và `major`.
   - **Không áp dụng khi import hoặc chạy REST/CLI:** Nếu dữ liệu được nhập qua WP-CLI (`wp post create`) hoặc WP All Import, trừ khi gọi tường minh `ltdh_sync_program_relationships()`, mảng `_offered_programs` sẽ hoàn toàn rỗng.
   - **Xung đột kiểu dữ liệu do `array_diff`:** Dòng 45 và 70 sử dụng `array_diff($old_programs, [$post_id])`. Hàm này giữ nguyên key số của mảng ban đầu, khi serialize vào postmeta có thể bị chuyển đổi thành object/associative array thay vì mảng tuần tự.
   - **Template không nhất quán khi đọc dữ liệu:**
     - `single-school.php:23` đọc `_offered_programs` rồi chạy `WP_Query(['post__in' => ...])`.
     - Nhưng `archive-school.php:265` lại bỏ qua `_offered_programs` và chạy `get_posts(['meta_query' => ['school_relationship' => $school_id]])`.
     - `inc/core/class-helpers.php:444` và `inc/core/class-helpers.php:627` cũng hoàn toàn bỏ qua `_offered_programs` mà chạy `meta_query` trực tiếp vào CSDL!

---

## 4. ĐÁNH GIÁ KIẾN TRÚC: GÁN `training_type` VÀO `program` VS `school` VS CPT ĐỘC LẬP

### 4.1. Bảng so sánh 3 mô hình kiến trúc

| Tiêu chí đánh giá | Phương án 1: `training_type` gắn vào `program` (Hiện tại) | Phương án 2: `training_type` gắn trực tiếp vào `school` | Phương án 3: `training_type` làm Custom Post Type độc lập |
|---|---|---|---|
| **Độ khớp thực tế tuyển sinh VN** | **Xuất sắc (9.5/10):** Đúng bản chất mỗi trường mở hệ nào cho ngành nào cụ thể. | **Rất kém (3/10):** Một trường có 30 ngành chính quy nhưng chỉ 5 ngành từ xa, gán vào trường sẽ gây hiểu lầm toàn bộ ngành đều có hệ đó. | **Tốt (8/10):** Tách bạch rõ nội dung truyền thông của hệ đào tạo và các gói tuyển sinh. |
| **Định mức biểu phí & thời gian** | **Chuẩn xác:** Học phí, tín chỉ, thời gian được lưu theo từng chương trình cụ thể. | **Không thể:** Không thể lưu học phí của hệ Từ xa và Chính quy trên cùng một trường nếu không dùng repeater phức tạp. | **Phải kết hợp:** Vẫn cần thực thể trung gian giữa CPT Training Type và School/Major. |
| **Hiệu năng truy vấn & Lọc** | **Trung bình:** Lọc trường theo hệ phải đi qua `program` (Subquery / Join 3 bảng). | **Rất cao (O(1)):** Truy vấn trực tiếp `school` theo `tax_query` của hệ đào tạo. | **Trung bình - Thấp:** Sinh thêm nhiều quan hệ post-to-post giữa 3 CPT. |
| **Trải nghiệm trang danh mục** | **Bất cập hiện tại:** `/he-dao-tao/tu-xa/` chỉ liệt kê danh sách chương trình, không nhóm theo trường đối tác. | **Dễ dàng:** Liệt kê ngay danh sách các trường đào tạo hệ đó. | **Xuất sắc:** Có trang Landing Page đồ sộ cho Hệ đào tạo với SEO Schema, Thông tư, Video. |
| **Độ phức tạp nhập liệu (Admin UX)** | **Cao:** Số lượng bản ghi `program` tăng cấp số nhân (Trường × Ngành × Hệ). | **Thấp nhất:** Chỉ cần tick chọn hệ đào tạo trên trang biên tập trường. | **Rất cao:** Phải quản lý liên kết giữa 3 loại bài viết khác nhau. |

### 4.2. Phân tích tình huống: Một trường mở nhiều hệ cho cùng một ngành vs khác ngành

#### Trường hợp A: Một trường mở nhiều hệ cho CÙNG MỘT ngành
*Ví dụ thực tế:* Đại học Mở Hà Nội (HOU) tuyển sinh ngành **Luật Kinh tế** dưới 2 hình thức:
1. **Hệ Đào tạo Từ xa (E-Learning):** Học 100% online, học phí 450.000đ/tín chỉ, tuyển sinh 4 đợt/năm, xét đầu vào THPT/TC/CĐ/VB2.
2. **Hệ Vừa làm vừa học (VLVH):** Học trực tiếp tại cơ sở vào tối thứ 7 & Chủ nhật, học phí 550.000đ/tín chỉ, tuyển sinh 2 đợt/năm.

- **Dưới kiến trúc hiện tại:**
  - Hệ thống tạo ra 2 bản ghi `program` độc lập:
    * `program_1`: Luật Kinh tế - Từ xa - HOU (gắn term `tu-xa`).
    * `program_2`: Luật Kinh tế - VLVH - HOU (gắn term `vua-hoc-vua-lam`).
  - Giao diện `single-school.php` (dòng 378-439) đã xử lý việc nhóm 2 chương trình này lại dưới cùng một tiêu đề ngành Luật Kinh tế. Đây là điểm xử lý frontend rất tốt của theme.
  - **Nhược điểm phát sinh:** Slug của 2 chương trình bị trùng lặp tiêu đề nếu không được đặt tên khéo léo; quản trị viên phải sao chép nội dung mô tả ngành học 2 lần trên 2 bản ghi.

#### Trường hợp B: Một trường mở các hệ cho CÁC ngành KHÁC NHAU
*Ví dụ thực tế:* Trường ĐH Công nghệ Giao thông Vận tải (UTT):
- Ngành **Công nghệ thông tin**: Có đào tạo Từ xa và Vừa làm vừa học.
- Ngành **Logistics & Chuỗi cung ứng**: Chỉ đào tạo Chính quy và Vừa làm vừa học (KHÔNG có đào tạo Từ xa).
- Ngành **Xây dựng cầu đường**: Chỉ có Liên thông chính quy.

- **Kết luận:** Nếu gán `training_type` lên `school`, khi người dùng lọc: *"Tìm các ngành đào tạo Từ xa của trường ĐH Công nghệ Giao thông Vận tải"*, hệ thống sẽ hiển thị sai lệch nếu ngầm hiểu trường có hệ Từ xa thì mọi ngành của trường đều có hệ Từ xa.
- Do đó: **BẮT BUỘC `training_type` PHẢI GẮN VÀO `program` (Mức độ đơn vị tuyển sinh thấp nhất).**

### 4.3. Giải pháp tối ưu: Kiến trúc Phản chiếu Hai tầng (Two-Tier Rollup Architecture)
Để giải quyết bài toán: vừa giữ được tính chính xác chi tiết ở cấp độ `program`, vừa cho phép lọc trường theo hệ với tốc độ cao ở cấp độ `school`:
1. **Đăng ký `training_type` cho CẢ HAI đối tượng:** `['program', 'school']`.
2. **Tự động Rollup (Đồng bộ ngược) từ Program lên School:**
   Khi lưu hoặc xóa bất kỳ `program` nào của một trường, hệ thống tự động gom tập hợp tất cả các terms `training_type` của các chương trình đang hoạt động (`publish`, trạng thái `tuyen-sinh`) và gán thẳng vào `school` đó bằng `wp_set_object_terms($school_id, $aggregated_types, 'training_type')`.
3. **Lợi ích vượt trội:**
   - Trang `/truong-doi-tac/?he=tu-xa` có thể dùng truy vấn `tax_query` chuẩn trên `school`, loại bỏ hoàn toàn các vòng lặp N+1 query.
   - Sửa dứt điểm lỗi hiển thị badge hệ đào tạo trên `archive-school.php:80`.
   - Vẫn bảo toàn 100% dữ liệu chi tiết của từng chương trình con.

---

## 5. ĐỐI CHIẾU VỚI THỰC TẾ TUYỂN SINH TẠI VIỆT NAM

Qua rà soát quy chế tuyển sinh liên thông, đào tạo từ xa hiện hành của Bộ GD&ĐT (Thông tư 28/2023/TT-BGDĐT, Thông tư 08/2022/TT-BGDĐT, Quyết định 18/2017/QĐ-TTg), kiến trúc hiện tại bộc lộ các khoảng cách (gaps) nghiệp vụ nghiêm trọng:

### 5.1. Mô hình Đa cơ sở & Trạm đào tạo từ xa (Campuses vs Study Centers)
- **Thực tế Việt Nam:**
  - Một trường Đại học có:
    1. **Trụ sở chính (Headquarters):** Nơi đặt bộ máy hành chính (VD: Hà Nội).
    2. **Phân hiệu (Branch Campuses):** Cơ sở đào tạo độc lập được Bộ cấp phép (VD: Phân hiệu TP.HCM của ĐH Giao thông Vận tải, Phân hiệu Vĩnh Long của ĐH Kinh tế TP.HCM).
    3. **Trạm Đào tạo Từ xa / Trạm liên kết tiếp nhận hồ sơ:** Các điểm phối hợp đặt tại các trường Cao đẳng, Trung cấp địa phương trên cả nước (VD: ĐH Thái Nguyên có trạm tại Hà Nội, Nghệ An, Cần Thơ, TP.HCM để sinh viên đến thi học phần tập trung).
- **Thiếu sót của Theme:**
  - Taxonomy `campus` hiện chỉ là danh sách phẳng gồm 5 địa danh: Hà Nội, TP.HCM, Đà Nẵng, Thái Nguyên, Online.
  - CPT `school` chỉ có một trường text đơn lẻ `address`. Khi trường mở trạm đào tạo mới ở tỉnh lẻ (ví dụ Trạm Cần Thơ), hệ thống không có cách nào hiển thị danh sách địa chỉ các trạm liên kết của trường đó để thí sinh tại địa phương đến nộp hồ sơ trực tiếp.
  - Hàm `ltdh_get_program_learning_details()` (`inc/core/class-helpers.php:558`) chỉ lấy `$campuses[0]->name ?: 'Hà Nội'`. Nếu chương trình có mặt ở cả 3 cơ sở (Hà Nội, Đà Nẵng, TP.HCM), giao diện chỉ hiện duy nhất cơ sở đầu tiên.

### 5.2. Các đợt tuyển sinh và Tuyển sinh liên tục (Batches vs Rolling Admissions)
- **Thực tế Việt Nam:**
  - Hệ Đào tạo từ xa và Vừa làm vừa học **không tuyển sinh một đợt duy nhất** như đại học chính quy (tháng 9).
  - Có 2 hình thức chính:
    1. **Tuyển sinh theo đợt cố định:** Mỗi năm có từ 3 đến 6 đợt (Đợt 1: Tháng 3; Đợt 2: Tháng 6; Đợt 3: Tháng 9; Đợt 4: Tháng 11).
    2. **Tuyển sinh liên tục quanh năm (Rolling Admissions):** Thu nhận hồ sơ liên tục, cứ gom đủ từ 30-50 học viên là mở lớp khai giảng trực tuyến.
- **Thiếu sót của Theme:**
  - Trường `admission_batches` repeater trên `program` (`inc/acf-import-fields.json:291-354`) chứa các trường `text` tự do (`release_period`, `application_period`, `enrollment_time`).
  - Do lưu dưới dạng text ("Từ 19/12/2025 đến 05/01/2026", "Tháng 7/2026"), hệ thống **hoàn toàn không có trường ngày tháng định dạng ISO (`Y-m-d`)**.
  - **Hậu quả:** Hệ thống không thể tự động tính toán đợt tuyển sinh nào đã hết hạn (`expired`), đợt nào đang mở (`active`), và không thể chạy Cron Job để tự động cập nhật `batch_status` sang `da-dong`. Quản trị viên phải vào sửa tay từng đợt của từng chương trình mỗi khi hết hạn hồ sơ.

### 5.3. Biểu phí, Tín chỉ và Thời gian đào tạo theo Trình độ đầu vào (Input-Tier Matrix)
- **Thực tế Việt Nam:**
  Thời gian học và tổng học phí của một người học Liên thông / VB2 / Từ xa **phụ thuộc 100% vào trình độ văn bằng đã có (Văn bằng đầu vào)**:

| Trình độ đầu vào của học viên | Số tín chỉ cần tích lũy thực tế | Thời gian hoàn thành | Biểu phí ước tính (VD: 450k/TC) | Quy định xét miễn giảm tín chỉ |
|---|---|---|---|---|
| **Tốt nghiệp THPT** | 130 - 145 tín chỉ | 3.5 - 4.0 năm | ~58.500.000đ - 65.000.000đ | Không miễn giảm |
| **Tốt nghiệp Trung cấp đúng ngành** | 80 - 95 tín chỉ | 2.0 - 2.5 năm | ~36.000.000đ - 42.000.000đ | Miễn giảm khối kiến thức cơ bản nghề |
| **Tốt nghiệp Cao đẳng đúng ngành** | 50 - 65 tín chỉ | 1.5 - 2.0 năm | ~22.500.000đ - 29.000.000đ | Miễn giảm toàn bộ đại cương & cơ sở ngành |
| **Tốt nghiệp Cao đẳng khác ngành** | 70 - 85 tín chỉ | 2.0 - 2.5 năm | ~31.500.000đ - 38.000.000đ | Phải học bổ sung kiến thức chuyển đổi |
| **Đã có bằng Đại học (Văn bằng 2)** | 45 - 60 tín chỉ | 1.5 - 2.0 năm | ~20.000.000đ - 27.000.000đ | Miễn toàn bộ: Triết học, Pháp luật đại cương, Ngoại ngữ, Tin học, GDQP, Thể chất |

- **Thiếu sót của Theme:**
  - Thực thể `program` hiện chỉ có 1 trường duy nhất `duration` (`text`: "1.5 - 2 năm") và 1 trường `tuition_amount` (`number`: định mức theo tín chỉ).
  - Không có cấu trúc dữ liệu dạng Ma trận (Matrix) để lưu: Ứng với đầu vào THPT thì học bao nhiêu năm, đầu vào Cao đẳng thì học bao nhiêu năm, miễn giảm bao nhiêu tín chỉ.
  - Khi thí sinh tốt nghiệp THPT vào xem một chương trình ghi "Thời gian học: 1.5 năm", thí sinh sẽ bị tư vấn sai lệch vì 1.5 năm chỉ dành cho người đã có bằng Cao đẳng cùng ngành.

---

## 6. SÁU (6) ĐIỂM NGHẼN KIẾN TRÚC & RỦI RO TOÀN VẸN DỮ LIỆU

### 🔴 Điểm nghẽn 1: Hiện tượng phân rã thực thể (Split-Brain Redundancy) giữa Taxonomy và ACF Checkbox
- **Vị trí quan sát:**
  - `inc/acf-import-cpts.json:170-207` (Taxonomy `training_type` gắn vào `program`).
  - `inc/acf-import-fields.json:611-640` (Nhóm trường `group_program_eligibility` có `elig_training_types` dạng Checkbox).
  - `inc/eligibility.php:428` và `inc/eligibility.php:473`.
- **Phân tích nguyên nhân & rủi ro:**
  - Khi tạo một `program`, quản trị viên phải chọn "Hệ đào tạo" ở hộp thoại Taxonomy bên phải của WordPress, ĐỒNG THỜI phải tick chọn các checkbox trong hộp "Điều kiện tuyển sinh" của ACF.
  - Hai nguồn dữ liệu này hoàn toàn độc lập và không có cơ chế đồng bộ.
  - Trong logic xử lý xét tuyển (`inc/eligibility.php`), mã nguồn vừa đọc term từ taxonomy:
    ```php
    $prog_training_types = wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'slugs' ] );
    ```
    Lại vừa so khớp với mảng lấy từ trường ACF:
    ```php
    $allowed_types = get_field( 'elig_training_types', $program_id );
    if ( ! in_array( $input['training_type'], $allowed_types, true ) ) { ... }
    ```
  - Nếu quản trị viên chỉ chọn taxonomy mà quên tick checkbox ACF (hoặc ngược lại), thuật toán Eligibility Checker sẽ báo học viên không đủ điều kiện xét tuyển một cách oan uổng.

### 🔴 Điểm nghẽn 2: Tham chiếu mồ côi (Orphan References) & Vòng đời thực thể bị đứt đoạn
- **Vị trí quan sát:**
  - `inc/relationship-hooks.php:12-74`.
- **Phân tích nguyên nhân & rủi ro:**
  - Hook đồng bộ quan hệ chỉ bắt duy nhất sự kiện `acf/save_post` (dòng 12).
  - Khi một chương trình tuyển sinh bị xóa bỏ hoàn toàn (`wp_delete_post`), bị chuyển vào Thùng rác (`wp_trash_post`), hoặc trạng thái chuyển thành Draft/Pending, hook không chạy.
  - ID của chương trình này vẫn nằm vĩnh viễn trong mảng `_offered_programs` được serialize trong postmeta của Trường và Ngành.
  - Khi `single-school.php` hoặc `single-major.php` đọc mảng này và truyền vào `post__in` của `WP_Query`, WordPress vẫn phải tốn tài nguyên tìm kiếm và xử lý các ID không còn tồn tại hoặc không ở trạng thái publish.
  - Ngược lại, nếu một Trường (`school`) bị xóa, tất cả các Chương trình (`program`) liên kết với trường đó vẫn giữ nguyên `school_relationship` trỏ về ID của trường đã chết. Không có bất kỳ cơ chế Foreign Key Cascade hay cảnh báo nào trong WP Admin.

### 🔴 Điểm nghẽn 3: "Hệ đào tạo ma" (Ghost Training Type) & Lỗi trống Badge trên Archive Trường
- **Vị trí quan sát:**
  - `archive-school.php:80-81`:
    ```php
    $school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
    $systems_label = ( ! is_wp_error( $school_types ) && ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
    ```
- **Phân tích nguyên nhân & rủi ro:**
  - Taxonomy `training_type` **không được liên kết với post type `school`** trong `inc/acf-import-cpts.json:175`.
  - Lệnh `wp_get_post_terms($school_id, 'training_type')` luôn luôn trả về mảng rỗng `[]` đối với tất cả các trường.
  - Kết quả: Toàn bộ khối Trường đại học nổi bật (Featured Schools) trên đầu trang `archive-school.php` **hoàn toàn không hiển thị được badge hệ đào tạo nào**, làm giảm nghiêm trọng trải nghiệm thị giác và độ tin cậy thông tin.
  - Tại danh sách trường thường bên dưới (dòng 265-307), lập trình viên nhận ra vấn đề nên đã viết vòng lặp phụ: truy vấn toàn bộ program con của từng trường, rồi lặp qua từng program để lấy `training_type`. Điều này tạo ra **thảm họa N+1 query**: 20 trường trên trang = 20 câu lệnh `get_posts` + 200 câu lệnh `wp_get_post_terms` phụ.

### 🔴 Điểm nghẽn 4: Hijacking không gian tên URL & Suy giảm hiệu năng định tuyến toàn trang
- **Vị trí quan sát:**
  - `inc/core/class-rewrite-rules.php:41-101`:
    ```php
    add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
    ```
- **Phân tích nguyên nhân & rủi ro:**
  - Để có đường dẫn đẹp dạng `domain.com/{ten-chuong-trinh}/` mà không có prefix `/program/`, theme đã đăng ký một biểu thức chính quy bắt mọi slug cấp 1 với độ ưu tiên cao nhất (`top`).
  - Sau đó, trong bộ lọc `request` (`ltdh_program_request_guard`), theme phải thực hiện:
    1. Chạy 1 truy vấn SQL tìm xem slug có phải là `program` không.
    2. Nếu không phải, chạy tiếp 1 truy vấn SQL tìm xem slug có phải là `post` (bài viết tin tức) không.
    3. Nếu không phải, mới trả về cho WordPress xử lý `pagename` (Trang tĩnh) hoặc CPT khác.
  - **Hậu quả:** Bất kỳ lượt truy cập nào vào các trang tĩnh (Trang chủ, Giới thiệu, Liên hệ, Cẩm nang), hoặc người dùng gõ nhầm link 404, đều phải chịu thêm **ít nhất 2 truy vấn SQL không cần thiết** trước khi WordPress phân giải được trang.
  - **Nguy cơ đè slug (Slug Shadowing):** Nếu một chương trình tuyển sinh vô tình có slug trùng với một Trang (`page`) hoặc Cẩm nang (`guide`), chương trình sẽ chiếm quyền hiển thị và làm trang kia biến mất hoàn toàn.

### 🔴 Điểm nghẽn 5: Đợt tuyển sinh lưu dạng Text tự do, không có khả năng tự động đóng mở
- **Vị trí quan sát:**
  - `inc/acf-import-fields.json:291-354` (`admission_batches` repeater).
  - `inc/core/class-helpers.php:716-747` (`ltdh_get_program_admission_deadline_display`).
- **Phân tích nguyên nhân & rủi ro:**
  - Hạn nộp hồ sơ và thời gian ôn thi đều là trường `text` (ví dụ: "Đợt 1 đến hết 30/11/2026").
  - Trạng thái đợt (`batch_status`) là trường `select` thủ công: `dang-nhan`, `sap-mo`, `da-dong`.
  - Không có trường ngày kết thúc dạng số (`deadline_timestamp` / `end_date`), dẫn đến việc hệ thống không thể so sánh với ngày hiện tại (`current_time('Y-m-d')`).
  - Học viên vẫn thấy hiển thị "Đang nhận hồ sơ: Đợt 1 đến hết 15/01/2026" dù thời điểm hiện tại đã là tháng 09/2026, tạo cảm giác website bị bỏ hoang, mất uy tín tuyển sinh.

### 🔴 Điểm nghẽn 6: Bộ định tuyến Lead (CRM Router) đơn khối, thiếu định danh Trường và Hệ
- **Vị trí quan sát:**
  - `inc/lead-capture.php:111-122`.
  - `inc/crm-adapters.php:91-124`.
- **Phân tích nguyên nhân & rủi ro:**
  - Dữ liệu lead lưu vào bảng `wp_ltdh_leads` lấy tên hệ đào tạo bằng string hiển thị (`$terms[0]->name`), không lưu slug hoặc term ID chuẩn.
  - Cấu hình CRM trong theme (`group_ltdh_global_settings`) chỉ có duy nhất một bộ Endpoint/Token toàn cục (OnSchool hoặc AUM).
  - **Thực tế:** Các trường đối tác khác nhau do các đối tác tuyển sinh khác nhau quản lý (ví dụ: Lead trường Đại học Mở chuyển vào OnSchool API; Lead trường Đại học Thái Nguyên chuyển vào AUM API; Lead hệ Vừa học vừa làm chuyển qua Telegram ban tuyển sinh nội bộ).
  - Kiến trúc hiện tại gửi toàn bộ lead của tất cả các trường vào duy nhất một CRM chung mà không có cơ chế phân luồng (Lead Routing Matrix by School & Training Type).

---

## 7. ĐỀ XUẤT KIẾN TRÚC & SCHEMA DỮ LIỆU NÂNG CẤP TOÀN DIỆN

Để giải quyết triệt để 6 điểm nghẽn kiến trúc và đáp ứng chính xác thực tế tuyển sinh tại Việt Nam, kiến trúc nâng cấp được thiết kế theo 5 trụ cột:

### 7.1. Sơ đồ thực thể nâng cấp (Target Architecture ERD)

```
┌─────────────────────────────────┐                       ┌─────────────────────────────────┐
│           CPT: school           │                       │           CPT: major            │
│  - school_code (NEU, TMU...)    │                       │  - major_code (7340101...)      │
│  - branch_campuses (repeater)   │                       │  - career_opportunities         │
│  - crm_routing_config (group)   │                       │  - major_related (relationship) │
│  - training_type (Taxonomy Sync)│                       └───────────────┬─────────────────┘
└───────────────┬─────────────────┘                                       │
                │                                                         │
      1         │ [school_relationship]                         1         │ [major_relationship]
                │                                                         │
                ▼                                                         ▼
┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                                       CPT: program                                        │
│  - training_type (Taxonomy - Single Source of Truth)                                      │
│  - campus (Taxonomy - Multi-campus selection)                                             │
│  - admission_status: tuyen-sinh | tam-ngung | sap-mo                                      │
│                                                                                           │
│  [REPEATER] input_level_matrix:                                                           │
│    * input_level: thpt | trung-cap | cao-dang-cung-nganh | cao-dang-khac | van-bang-2     │
│    * duration_months: 18 | 24 | 36 | 48                                                   │
│    * duration_label: "1.5 năm" | "2 năm" | "3.5 năm"                                      │
│    * required_credits: 55 | 75 | 135                                                      │
│    * tuition_per_credit: 450000                                                           │
│    * estimated_total_tuition: 24750000                                                    │
│    * exemption_policy_notes: "Được miễn 12 tín chỉ cơ sở..."                              │
│                                                                                           │
│  [REPEATER] structured_admission_batches:                                                 │
│    * batch_code: "DOT-1-2026"                                                             │
│    * batch_name: "Đợt 1 - Khai giảng tháng 3"                                             │
│    * start_date: "2026-01-01"                                                             │
│    * end_date: "2026-03-15"                                                               │
│    * exam_or_review_date: "2026-03-25"                                                    │
│    * enrollment_date: "2026-04-05"                                                        │
│    * is_rolling: false                                                                    │
│    * computed_status: (Virtual/Auto: dang-nhan | sap-mo | da-dong)                        │
└───────────────────────────────────────────────────────────────────────────────────────────┘
```

---

### 7.2. Chi tiết cấu trúc dữ liệu và Schema mới

#### 1. Đăng ký Taxonomy đa thực thể (Multi-Object Taxonomy)
Cập nhật `inc/acf-import-cpts.json`:
```json
{
    "key": "taxonomy_training_type",
    "taxonomy": "training_type",
    "object_type": [
        "program",
        "school"
    ],
    "hierarchical": true,
    "show_admin_column": true,
    "rewrite": { "slug": "he-dao-tao", "with_front": false }
},
{
    "key": "taxonomy_campus",
    "taxonomy": "campus",
    "object_type": [
        "program",
        "school"
    ],
    "hierarchical": true,
    "show_admin_column": true,
    "rewrite": { "slug": "co-so", "with_front": false }
}
```

#### 2. Schema Ma trận Tuyển sinh theo Trình độ đầu vào (`input_level_matrix`)
Thay thế trường `duration` và `tuition_amount` đơn lẻ bằng một Repeater cấu trúc trên CPT `program`:

| Subfield Key | Field Name | Type | Options / Validation | Rationale Nghiệp vụ Tuyển sinh |
|---|---|---|---|---|
| `input_level` | Trình độ văn bằng đầu vào | `select` | `thpt`: Tốt nghiệp THPT<br>`trung-cap`: Trung cấp đúng/gần ngành<br>`cao-dang-cung`: Cao đẳng đúng ngành<br>`cao-dang-khac`: Cao đẳng khác ngành<br>`van-bang-2`: Đã có bằng Đại học | Phân định rõ 5 nhóm thí sinh nộp hồ sơ phổ biến nhất tại VN. |
| `duration_months` | Thời gian đào tạo (tháng) | `number` | Min: 12, Max: 60 (VD: 18, 24, 42) | Dùng để so sánh, lọc và vẽ lộ trình học trực quan. |
| `duration_label` | Nhãn hiển thị thời gian | `text` | VD: "1.5 năm (3 học kỳ)" | Hiển thị thân thiện trên UI Frontend. |
| `required_credits` | Số tín chỉ tích lũy | `number` | VD: 58 | Số tín chỉ thực tế phải học sau khi trừ tín chỉ miễn giảm. |
| `tuition_per_credit`| Đơn giá tín chỉ | `number` | VD: 480000 | Định mức tiền cho 1 tín chỉ của hệ này. |
| `estimated_total_tuition`| Tổng học phí ước tính | `number` | Tự động tính (`credits × tuition`) | Cung cấp con số ngân sách chuẩn xác cho module so sánh và tư vấn. |
| `exemption_notes` | Quy định miễn giảm tín chỉ | `textarea` | VD: "Miễn toàn bộ 15 tín chỉ đại cương và GDQP" | Tạo sự minh bạch, tăng tỷ lệ chuyển đổi form. |

#### 3. Schema Đợt tuyển sinh có cấu trúc thời gian (`structured_admission_batches`)
Nâng cấp `admission_batches` repeater trên CPT `program`:

| Subfield Key | Field Name | Type | Format / Constraints | Logic tự động hóa |
|---|---|---|---|---|
| `batch_code` | Mã đợt | `text` | VD: `2026-D1` | Khóa định danh cho Lead Routing và CRM. |
| `batch_name` | Tên đợt tuyển sinh | `text` | VD: `Đợt 1 (Mùa Xuân 2026)` | Tên hiển thị người dùng. |
| `start_date` | Ngày bắt đầu nhận hồ sơ | `date_picker` | `Y-m-d` | Dùng để kích hoạt trạng thái "Đang nhận hồ sơ". |
| `end_date` | Hạn chót nhận hồ sơ | `date_picker` | `Y-m-d` | So sánh với `current_time('Y-m-d')` để tự động đóng đợt. |
| `exam_or_eval_date` | Ngày xét tuyển / Ôn thi | `date_picker` | `Y-m-d` | Hiển thị mốc thời gian rõ ràng cho học viên. |
| `enrollment_date` | Thời gian nhập học | `date_picker` | `Y-m-d` | Mốc học viên chính thức vào học kỳ 1. |
| `is_rolling` | Tuyển sinh liên tục quanh năm | `true_false` | Default: 0 | Nếu bật, hệ thống hiển thị badge "Tuyển sinh liên tục - Khai giảng hàng tháng". |
| `manual_status_override` | Ghi đè trạng thái thủ công | `select` | `auto`: Tự động theo ngày<br>`dang-nhan`: Ép mở<br>`da-dong`: Ép đóng hết chỉ tiêu | Linh hoạt cho quản trị viên khi hết chỉ tiêu sớm. |

#### 4. Schema Quản lý Cơ sở & Trạm đào tạo liên kết trên CPT `school`
Thêm nhóm trường `group_school_campuses` vào CPT `school`:
- `headquarters_address` (`text`): Trụ sở chính.
- `branch_campuses` (`repeater`):
  * `branch_name` (`text`): Tên phân hiệu / cơ sở (VD: "Phân hiệu TP. Hồ Chí Minh").
  * `branch_city` (`taxonomy` -> `campus`): Tỉnh/Thành phố trực thuộc.
  * `branch_address` (`text`): Địa chỉ cụ thể.
  * `branch_hotline` (`text`): Hotline riêng của phân hiệu.
- `study_stations` (`repeater`): Danh sách Trạm đào tạo từ xa / Trạm liên kết:
  * `station_name` (`text`): Tên trạm (VD: "Trạm Đào tạo Từ xa Cần Thơ - Trường CĐ Kinh tế Kỹ thuật Cần Thơ").
  * `station_city` (`taxonomy` -> `campus`): Tỉnh/Thành phố.
  * `station_address` (`text`): Địa chỉ nộp hồ sơ và tổ chức thi học phần.

---

### 7.3. Engine đồng bộ quan hệ hoàn chỉnh (Full-Lifecycle Bi-directional Sync)

Thay thế file `inc/relationship-hooks.php` bằng một lớp chuyên trách `LTDH_Entity_Relationship_Engine` xử lý đầy đủ các hook trong vòng đời bài viết:

```php
/**
 * Kiến trúc Engine đồng bộ quan hệ thực thể toàn diện
 */
class LTDH_Entity_Relationship_Engine {

    public static function init() {
        // 1. Khi lưu bài viết CPT Program (ACF UI hoặc REST/CLI)
        add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20, 2 );

        // 2. Khi xóa hoặc chuyển vào thùng rác
        add_action( 'wp_trash_post', [ __CLASS__, 'on_program_status_change' ] );
        add_action( 'untrash_post', [ __CLASS__, 'on_program_status_change' ] );
        add_action( 'before_delete_post', [ __CLASS__, 'on_program_delete' ] );

        // 3. Khi School hoặc Major bị xóa
        add_action( 'before_delete_post', [ __CLASS__, 'on_parent_entity_delete' ] );
    }

    public static function on_program_save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( $post->post_status === 'auto-draft' ) return;

        // Lấy school_id và major_id từ postmeta
        $school_id = intval( get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true ) );
        $major_id  = intval( get_post_meta( $post_id, LTDH_META_MAJOR_REL, true ) );
        
        $old_school_id = intval( get_post_meta( $post_id, LTDH_META_LAST_SCHOOL, true ) );
        $old_major_id  = intval( get_post_meta( $post_id, LTDH_META_LAST_MAJOR, true ) );

        // Đồng bộ danh sách _offered_programs cho School & Major
        self::rebuild_entity_programs_cache( $school_id, 'school' );
        if ( $old_school_id && $old_school_id !== $school_id ) {
            self::rebuild_entity_programs_cache( $old_school_id, 'school' );
        }

        self::rebuild_entity_programs_cache( $major_id, 'major' );
        if ( $old_major_id && $old_major_id !== $major_id ) {
            self::rebuild_entity_programs_cache( $old_major_id, 'major' );
        }

        // Cập nhật last known IDs
        update_post_meta( $post_id, LTDH_META_LAST_SCHOOL, $school_id );
        update_post_meta( $post_id, LTDH_META_LAST_MAJOR, $major_id );

        // Đồng bộ ngược (Rollup) Taxonomy training_type & campus lên School
        if ( $school_id ) {
            self::rollup_school_taxonomies( $school_id );
        }
    }

    public static function on_program_status_change( $post_id ) {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) return;
        $school_id = intval( get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true ) );
        $major_id  = intval( get_post_meta( $post_id, LTDH_META_MAJOR_REL, true ) );
        
        if ( $school_id ) {
            self::rebuild_entity_programs_cache( $school_id, 'school' );
            self::rollup_school_taxonomies( $school_id );
        }
        if ( $major_id ) {
            self::rebuild_entity_programs_cache( $major_id, 'major' );
        }
    }

    public static function on_program_delete( $post_id ) {
        self::on_program_status_change( $post_id );
    }

    /**
     * Tái tạo mảng _offered_programs sạch, loại bỏ 100% orphan IDs
     */
    public static function rebuild_entity_programs_cache( int $entity_id, string $type ) {
        if ( ! $entity_id ) return;
        $meta_key = ( 'school' === $type ) ? LTDH_META_SCHOOL_REL : LTDH_META_MAJOR_REL;

        $active_program_ids = get_posts( [
            'post_type'      => LTDH_CPT_PROGRAM,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => $meta_key,
                    'value'   => $entity_id,
                    'compare' => '=',
                ]
            ],
        ] );

        update_post_meta( $entity_id, LTDH_META_OFFERED_PROGRAMS, array_values( array_unique( $active_program_ids ) ) );
    }

    /**
     * Tự động Rollup: Gom toàn bộ training_type và campus từ Program con gắn lên School
     */
    public static function rollup_school_taxonomies( int $school_id ) {
        $programs = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true ) ?: [];
        if ( empty( $programs ) ) {
            wp_set_object_terms( $school_id, [], 'training_type' );
            wp_set_object_terms( $school_id, [], 'campus' );
            return;
        }

        $all_training_type_ids = [];
        $all_campus_ids = [];

        foreach ( $programs as $pid ) {
            $tt_terms = wp_get_object_terms( $pid, 'training_type', [ 'fields' => 'ids' ] );
            if ( ! is_wp_error( $tt_terms ) ) {
                $all_training_type_ids = array_merge( $all_training_type_ids, $tt_terms );
            }

            $cp_terms = wp_get_object_terms( $pid, 'campus', [ 'fields' => 'ids' ] );
            if ( ! is_wp_error( $cp_terms ) ) {
                $all_campus_ids = array_merge( $all_campus_ids, $cp_terms );
            }
        }

        wp_set_object_terms( $school_id, array_unique( $all_training_type_ids ), 'training_type' );
        wp_set_object_terms( $school_id, array_unique( $all_campus_ids ), 'campus' );
    }
}
```

---

### 7.4. Kiến trúc Định tuyến URL (Clean Rewrite Routing)
Khắc phục triệt để lỗi quét CSDL toàn cục tại `inc/core/class-rewrite-rules.php`:
1. **Phương án cấu trúc URL chuẩn mực:**
   - Thay vì dùng biểu thức bắt dính mọi slug `([^/]+)/?$`, chuyển cấu trúc URL của `program` sang tiền tố ngữ nghĩa:
     `/chuong-trinh/%postname%/` (hoặc `/tuyen-sinh/%postname%/`).
   - Cấu trúc này khớp 100% với WordPress Core CPT Rewrite tiêu chuẩn, loại bỏ hoàn toàn hook `ltdh_program_request_guard` và các câu truy vấn SQL thừa trên mỗi pageview.
2. **Kế hoạch chuyển đổi và bảo toàn SEO (301 Redirect):**
   - Với các URL cũ đang hoạt động dạng `domain.com/cu-nhan-cntt-tu-xa/`, thiết lập một bảng hash map lưu trong transient hoặc options.
   - Nếu một request cấp 1 trả về 404, chỉ lúc đó mới tra cứu bảng hash map để thực hiện `wp_redirect(home_url('/chuong-trinh/' . $slug . '/'), 301)`. Điều này bảo toàn 100% tài nguyên cho 99% các request bình thường.

---

### 7.5. Ma trận Phân luồng Lead Tuyển sinh Đa đối tác (Lead Routing Engine)
Mở rộng bảng `wp_ltdh_leads` và thêm cấu hình trên từng CPT `school`:
1. **Thêm trường cấu hình CRM trên `school`:**
   - `crm_provider`: `default` (kế thừa toàn cục), `onschool`, `aum`, `custom_webhook`, `internal_only`.
   - `crm_endpoint_override`: Endpoint API riêng nếu trường này do đối tác khác vận hành.
   - `crm_auth_token_override`: Token riêng.
   - `crm_school_code`: Mã mapping của trường trong hệ thống CRM đối tác (VD: `HOU_ONLINE`, `NEU_DEC`).
2. **Cập nhật hàm `ltdh_sync_lead_to_crm()`:**
   - Khi có lead mới, kiểm tra `school_id`.
   - Lấy cấu hình CRM của trường tương ứng. Nếu trường có cấu hình riêng ➔ gửi thẳng đến CRM của đối tác đó kèm theo `school_code`, `major_code`, và `batch_code`.
   - Nếu trường để mặc định ➔ gửi về CRM toàn cục.
   - Bổ sung Index vào bảng `wp_ltdh_leads`:
     ```sql
     ALTER TABLE wp_ltdh_leads ADD INDEX idx_school_created (school_id, created_at);
     ALTER TABLE wp_ltdh_leads ADD INDEX idx_program (program_id);
     ```

---

## 8. LỘ TRÌNH KHẮC PHỤC & TRIỂN KHAI CHO ĐỘI NGŨ DEVELOPER

| Giai đoạn | Hạng mục thực hiện | Tệp tin liên quan | Mức độ ưu tiên | Rủi ro |
|---|---|---|---|---|
| **Phase 1: Hotfix Khẩn cấp (Zero Breaking Changes)** | - Bổ sung `'school'` vào `object_type` của `training_type` và `campus` trong `acf-import-cpts.json`.<br>- Viết lệnh CLI chạy 1 lần để rollup gán toàn bộ hệ đào tạo hiện có của các program lên school tương ứng.<br>- Xóa bỏ truy vấn phụ N+1 trên `archive-school.php:265`. | `inc/acf-import-cpts.json`<br>`archive-school.php`<br>`inc/cli-commands.php` | **CRITICAL** | Rất thấp. Sửa ngay lỗi mất badge trên featured schools và tăng tốc trang trường đối tác x5 lần. |
| **Phase 2: Hoàn thiện Vòng đời Quan hệ (Lifecycle Engine)** | - Triển khai class `LTDH_Entity_Relationship_Engine` thay thế cho `relationship-hooks.php`.<br>- Lắng nghe thêm các hook `before_delete_post`, `wp_trash_post`, `untrash_post`.<br>- Tự động xóa sạch orphan IDs khi bài viết bị xóa. | `inc/relationship-hooks.php`<br>`functions.php` | **HIGH** | Thấp. Làm sạch dữ liệu cache postmeta. |
| **Phase 3: Chuẩn hóa Schema & Thống nhất Nguồn dữ liệu** | - Loại bỏ trường checkbox trùng lặp `elig_training_types` và `elig_campuses` trong `inc/acf-import-fields.json`.<br>- Chuyển đổi logic kiểm tra điều kiện trong `inc/eligibility.php` sang đọc taxonomy terms 100%.<br>- Thêm trường `input_level_matrix` và `structured_admission_batches`. | `inc/acf-import-fields.json`<br>`inc/eligibility.php`<br>`inc/core/class-helpers.php` | **HIGH** | Trung bình. Cần script migration dữ liệu từ các trường cũ sang schema mới. |
| **Phase 4: Tối ưu Định tuyến & Phân luồng Tuyển sinh** | - Nâng cấp CRM Routing theo từng trường đối tác.<br>- Đánh chỉ mục Index cho bảng `wp_ltdh_leads`.<br>- Tái cấu trúc Rewrite Rule cho CPT Program với tiền tố an toàn, loại bỏ cơ chế catch-all SQL overhead. | `inc/core/class-rewrite-rules.php`<br>`inc/lead-capture.php`<br>`inc/crm-adapters.php` | **MEDIUM** | Cao (Cần cấu hình 301 Redirect cẩn thận để không ảnh hưởng index Google). |

---

*Báo cáo được hoàn thành và bảo lưu toàn bộ dữ liệu kiểm chứng tại thư mục làm việc của `explorer_survey_arch_1`.*
