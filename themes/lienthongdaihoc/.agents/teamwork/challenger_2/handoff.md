# BÁO CÁO THẨM ĐỊNH VÀ PHẢN BIỆN THỰC NGHIỆM (HANDOFF REPORT)
**Người thực hiện**: `teamwork_preview_challenger` (Challenger 2)  
**Mục tiêu**: Thẩm định thực nghiệm mã nguồn và các giải pháp kỹ thuật mẫu tại Mục 7 của `WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md`, kiểm tra cú pháp, chuẩn WPCS, PHP 8.1+, kiểm thử bộ test dự án (`test-m4-adversarial.php`, `test-m6-e2e-master-acceptance.php`), và đưa ra phán quyết độc lập.  
**Thời gian hoàn thành**: 2026-10-06T12:45:00Z  

---

## 1. OBSERVATION (QUAN SÁT THỰC NGHIỆM TRỰC TIẾP)

### 1.1. Thực nghiệm chạy bộ kiểm thử hiện có của dự án
1. **Lệnh thực thi**: `php tests/test-m4-adversarial.php` (môi trường CLI, PHP 8.4.19):
   - **Kết quả**: **CRASH NGAY LẬP TỨC (Exit code: 255)**.
   - **Verbatim Error**:
     ```
     Fatal error: Uncaught Error: Call to undefined function get_term_link() in /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/core/class-menus.php:174
     Stack trace:
     #0 /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/tests/test-m4-adversarial.php(177): ltdh_dynamic_menu_submenu_injection(Array, Object(stdClass))
     #1 {main}
       thrown in /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/core/class-menus.php on line 174
     ```
   - **Thực nghiệm bổ sung**: Khi mock bổ sung hàm `get_term_link()` và `get_template_directory_uri()`, harness chạy tiếp nhưng **FAIL tại Challenge C5.1**:
     ```
     [FAIL] C5.1: Fallback menu renders correctly across all 8 standard routes with expected labels
     ```
     Do file `inc/config/class-defaults.php:44` trả về nhãn `'Trường đối tác'`, trong khi test mong đợi `'Trường đại học'`.

2. **Lệnh thực thi**: `php tests/test-m6-e2e-master-acceptance.php`:
   - **Kết quả**: **FAIL 5 ASSERTION VÀ CRASH (Exit code: 255)**.
   - **Verbatim Failures trước khi crash**:
     - `[FAIL] taxonomy.php:220 has valid '<a href="<?php the_permalink(); ?>"' syntax`
     - `[FAIL] Taxonomy title is 'Hình thức học' (Details: Actual: Hình thức đào tạo)`
     - `[FAIL] Taxonomy label is 'Hình thức học' (Details: Actual: Hình thức đào tạo)`
     - `[FAIL] Taxonomy rewrite_slug is preserved as 'he-dao-tao'`
     - `[FAIL] Redirect target points strictly to /he-dao-tao/ (NOT /he-dao-tao/tu-xa/)`
     - `[FAIL] Canonical filter enforces /he-dao-tao/ on program archive`
   - **Verbatim Fatal Error**:
     ```
     Fatal error: Uncaught Error: Call to undefined function get_template_directory_uri() in /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/config/class-defaults.php:94
     Stack trace:
     #0 /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/tests/test-m6-e2e-master-acceptance.php(287): ltdh_get_defaults('navigation')
     #1 {main}
       thrown in /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/config/class-defaults.php on line 94
     ```

3. **Kiểm tra cú pháp tĩnh toàn theme**:
   - Quét 67 tệp tin `.php` trong theme bằng `php -l`: **100% đạt chuẩn cú pháp (0 Syntax Errors)**.

### 1.2. Thẩm định chi tiết từng đoạn code mẫu trong Mục 7 Báo cáo

| Bản vá | Vị trí đề xuất | Tình trạng cú pháp & chuẩn WPCS | Rủi ro / Lỗi thực nghiệm phát hiện |
| :--- | :--- | :--- | :--- |
| **Bản vá 1.1** (CSRF Lead Form) | `inc/core/class-helpers.php`<br>`single-guide.php`<br>`inc/lead-capture.php:403-452` | Cú pháp hợp lệ, đúng chuẩn Nonce WordPress | **CRITICAL REGRESSION**: <br>1. Đoạn code mẫu đã **bỏ quên hàm kiểm tra spam** `ltdh_is_spam_submission()` vốn có tại `inc/lead-capture.php:522`. Áp dụng code mẫu này sẽ làm tê liệt honeypot & bộ lọc Cyrillic/URL spam!<br>2. **Mất liên kết tham số**: Code mẫu đọc `$_POST['program_id']`, nhưng toàn bộ form đơn hiện tại (`single-program.php:1070`, `single-school.php:671`) đang gửi `current_program_id`. Nếu thay thế, toàn bộ lead từ các trang single sẽ bị lưu thành `program_id = 0`! |
| **Bản vá 1.2** (Clean Data JSON) | Script chạy một lần `schools_import.json` | Cú pháp hợp lệ | **INCOMPLETE SANITIZATION**: <br>1. Script chỉ áp dụng regex xóa lên `$school['admission_info']`. Tuy nhiên, đoạn kịch bản nhạy cảm `(Văn phòng tuyển sinh: tên TVTS - SĐT` nằm trong trường `$school['contact_info']` (trường UNET, dòng 28). Do đó, script chạy xong vẫn để lọt nguyên văn chuỗi nhạy cảm trên web!<br>2. Mảng `$official_school_hotlines` khai báo mã `'UNETI'`, nhưng trong JSON mã thực tế là `'UNET'`. |
| **Bản vá 1.3** (Form PDF Link) | `inc/cli-commands.php:443` | Cú pháp hợp lệ | Biến `$school_code` có tồn tại trong scope vòng lặp. Cần ép kiểu `(string) $school_code` trước khi gọi `preg_replace` để tránh deprecation notice trên PHP 8.1+ khi giá trị null. |
| **Bản vá 2.1** (Z-index Mobile Bar) | `footer.php:151`<br>`compare/tray.php:11`<br>`assets/js/compare.js` | Cú pháp hợp lệ | **LOGIC DEFECT & UX REGRESSION**:<br>1. Điều kiện `$should_render_global_mobile_bar = ! is_singular( [ 'program', 'major' ] ) && ! is_page( 'so-sanh-chuong-trinh' );` bị sai. Trang so sánh của theme được route qua custom rewrite `^so-sanh/chuong-trinh/(.+?)/?$` (biến `ltdh_compare`), hàm `is_page('so-sanh-chuong-trinh')` **luôn trả về false**, thanh mobile bar vẫn sẽ xuất hiện trên trang so sánh.<br>2. Trang `single-major.php` hoàn toàn không có sticky bottom bar riêng. Việc loại trừ `'major'` sẽ khiến toàn bộ trang chi tiết ngành đào tạo **mất hoàn toàn các nút gọi điện/chat/đăng ký trên di động**! |
| **Bản vá 2.2** (DOM ID Mismatch Quiz) | `template-parts/eligibility/results.php:41` | Cú pháp hợp lệ, đồng bộ chuẩn DOM | **CHÍNH XÁC 100%**: Sửa `id="elig-alternatives"` thành `id="elig-alternatives-section"` khớp với `assets/js/eligibility.js:400`. Đạt chuẩn. |
| **Bản vá 2.3** (Preserve `program_id`) | `page-register.php:24-35` | Cú pháp hợp lệ | Cần gửi đồng thời cả hai key `program_id` và `current_program_id` vào `$hidden_context` để tương thích ngược 100% với logic tiếp nhận lead. |
| **Bản vá 2.4** (Rank Math Canonical) | `inc/seo/class-rankmath-integration.php:138-163` | Cú pháp hợp lệ | **DEFECT**: `return get_term_link( $current_term );` thiếu guard kiểm tra `is_wp_error()`. Khi `$current_term` trả về rỗng/lỗi, Rank Math nhận đối tượng `WP_Error` làm canonical sẽ gây lỗi render HTML thẻ `<link rel="canonical">`. |
| **Bản vá 2.5** (Course Schema) | `inc/seo/class-rankmath-integration.php:205-235` | Cú pháp hợp lệ | **DATA DEFECT**: Gán cứng `'courseMode' => 'Online'` bỏ qua hệ `vua-hoc-vua-lam`. Khi học phí rỗng, phát sinh `'price' => '0'` dẫn tới Google hiểu nhầm là khóa học miễn phí (vi phạm Google Search Central Guidelines về giá). |
| **Bản vá 2.6** (ACF Admin & Diploma) | `inc/acf-fields.php:149`<br>`single-program.php` | Cú pháp hợp lệ, bảo mật escape đầy đủ | **CHÍNH XÁC 100%**: Mở khóa trường văn bằng và bổ sung khối Thông tư 27/2019/TT-BGDĐT đáp ứng đúng yêu cầu pháp lý đào tạo liên thông. |
| **Bản vá 3.1** (Phone Regex VN) | `inc/lead-capture.php:121-130` | Cú pháp regex hợp lệ | **FALSE POSITIVE SPAM RISK**: Regex `$vn_phone_pattern` chỉ chấp nhận đầu số di động 10 số, chặn toàn bộ số điện thoại cố định bàn cơ quan/nhà trường (`024...`, `028...`) và đầu số mới FPT (`055`). Cần nới lỏng để không loại bỏ lead hợp lệ. |
| **Bản vá 3.2** (Email Notification) | `inc/lead-capture.php:217` | Cú pháp hợp lệ, an toàn header injection | **CHÍNH XÁC 100%**: Gửi email qua `wp_mail()` có kiểm tra `is_email()`, nội dung plain text an toàn. |
| **Bản vá 3.3** (ABSPATH Guard) | `header.php`, `inc/search-engine.php` | Cú pháp hợp lệ | **CHÍNH XÁC 100%**: Chuẩn bảo mật file WordPress. |
| **Bản vá 3.4** (Dequeue redundant CSS) | `inc/core/class-theme-setup.php` | Cú pháp hợp lệ | **CHÍNH XÁC 100%**: Bỏ enqueue file `style.css` 15KB trùng lặp, tối ưu tốc độ. |
| **Bản vá 3.5** (H1 Deduplication) | `page-compare-program.php:53` | Cú pháp hợp lệ | **CHÍNH XÁC 100%**: Chuyển H1 thứ 2 thành H2, triệt tiêu lỗi duplicate H1 theo chuẩn SEO. |

---

## 2. LOGIC CHAIN (CHUỖI SUY LUẬN TỪ THỰC NGHIỆM ĐẾN KẾT LUẬN)

1. **Từ Quan sát 1.1**:
   - Khi chạy `php tests/test-m4-adversarial.php`, hàm `ltdh_dynamic_menu_submenu_injection()` gọi `get_term_link()` tại `class-menus.php:174`. Tuy nhiên bộ test không mock hàm này, dẫn đến Fatal Error dừng ngay ở đầu test.
   - Khi chạy `php tests/test-m6-e2e-master-acceptance.php`, hàm `ltdh_get_defaults()` khởi tạo mảng defaults có gọi `get_template_directory_uri()` tại `class-defaults.php:94`. Bộ test không mock hàm này, gây Fatal Error.
   - Đồng thời, Suite 4 của Test M6 phát hiện xung đột định danh cốt lõi: Theme đang dùng `/hinh-thuc-dao-tao/` và `'Hình thức đào tạo'`, trong khi test suite mong đợi `/he-dao-tao/` và `'Hình thức học'`.
   - $\rightarrow$ **Suy luận**: Tuyên bố rằng mã nguồn theme gốc hiện tại đang pass 100% hai bộ test này là **không chính xác về mặt thực nghiệm**. Bộ test bị thiếu mock hàm WordPress và có sự lệch pha về taxonomy slug.

2. **Từ Quan sát 1.2 (Bản vá 1.1 - CSRF)**:
   - Trong `inc/lead-capture.php`, hàm `ltdh_handle_native_form_submit()` gốc có gọi `ltdh_is_spam_submission()`.
   - Bản vá 1.1 trong Mục 7 viết lại toàn bộ hàm nhưng lược bỏ dòng kiểm tra spam này.
   - Các template hiện tại (`single-program.php`, `single-school.php`, `single-major.php`) truyền ID với tiền tố `current_program_id`, trong khi code mẫu của bản vá 1.1 chỉ trích xuất `program_id`.
   - $\rightarrow$ **Suy luận**: Nếu developer copy nguyên văn Bản vá 1.1 vào theme, hệ thống sẽ: (a) Mở toang cửa cho bot spam gửi rác (do mất spam check); (b) Mất toàn bộ thông tin trường/ngành/chương trình của lead gửi từ các trang chi tiết.

3. **Từ Quan sát 1.2 (Bản vá 1.2 - Clean Data)**:
   - Regex trong Bản vá 1.2 chỉ chạy trên `$school['admission_info']`.
   - Trong `schools_import.json`, chuỗi nhạy cảm `(Văn phòng tuyển sinh: tên TVTS - SĐT` nằm tại dòng 28 trong trường `contact_info`.
   - $\rightarrow$ **Suy luận**: Bản vá 1.2 không thể làm sạch triệt để dữ liệu như báo cáo đã tuyên bố nếu không bổ sung trường `contact_info` vào phạm vi xử lý.

4. **Từ Quan sát 1.2 (Bản vá 2.1 - Mobile Bar)**:
   - Bản vá 2.1 dùng `is_page( 'so-sanh-chuong-trinh' )` để ẩn thanh bar.
   - Cấu trúc theme xử lý trang so sánh qua rewrite rule `so-sanh/chuong-trinh/(.+?)/?$` gán biến `ltdh_compare = 'program'`, không phải là một WordPress standard page.
   - Do đó, `is_page( 'so-sanh-chuong-trinh' )` luôn trả về false $\rightarrow$ Thanh bar không được ẩn trên trang so sánh.
   - Đồng thời loại trừ `is_singular( 'major' )` làm mất CTA trên trang ngành đào tạo (nơi không có thanh sticky riêng).
   - $\rightarrow$ **Suy luận**: Bản vá 2.1 sai điều kiện logic WordPress và gây tác dụng phụ làm hỏng trải nghiệm người dùng trên trang ngành.

---

## 3. CAVEATS (GIỚI HẠN & GIẢ ĐỊNH)

1. Môi trường kiểm thử dòng lệnh CLI không nạp được `wp-load.php` toàn phần do máy chủ MySQL của Local WP không khởi chạy socket qua CLI, các bài test phải thực thi qua mock harness.
2. Việc thay đổi slug từ `/hinh-thuc-dao-tao/` sang `/he-dao-tao/` liên quan đến URL rewrite rules và dữ liệu đã index trên Rank Math; cần có kế hoạch 301 redirect tương ứng nếu thực hiện chuẩn hóa.

---

## 4. CONCLUSION & FINAL VERDICT (KẾT LUẬN & PHÁN QUYẾT)

### PHÁN QUYẾT CHÍNH THỨC: **REJECT CODE GỐC SECTION 7 (YÊU CẦU ĐIỀU CHỈNH 5 LỖI TRƯỚC KHI ÁP DỤNG)**
*(Hoặc CONDITIONAL APPROVAL - Chỉ được áp dụng khi đã tích hợp các điều chỉnh kỹ thuật dưới đây)*

Báo cáo `WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md` có chất lượng phân tích kiến trúc rất sâu sắc và phát hiện đúng các vấn đề gốc rễ. Tuy nhiên, **các đoạn code giải pháp mẫu tại Mục 7 KHÔNG THỂ sao chép trực tiếp vào production** vì chứa 5 lỗi logic/hồi quy nghiêm trọng:

### 5 ĐIỀU CHỈNH BẮT BUỘC PHẢI THỰC HIỆN:

1. **Khắc phục Bản vá 1.1**:
   - Khôi phục kiểm tra `ltdh_is_spam_submission( [ 'name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $notes ] )`.
   - Hỗ trợ fallback tương thích ngược cho các tham số ID:
     ```php
     $program_id = isset( $_POST['program_id'] ) ? absint( $_POST['program_id'] ) : ( isset( $_POST['current_program_id'] ) ? absint( $_POST['current_program_id'] ) : 0 );
     $school_id  = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : ( isset( $_POST['current_school_id'] ) ? absint( $_POST['current_school_id'] ) : 0 );
     $major_id   = isset( $_POST['major_id'] ) ? absint( $_POST['major_id'] ) : ( isset( $_POST['current_major_id'] ) ? absint( $_POST['current_major_id'] ) : 0 );
     ```

2. **Khắc phục Bản vá 1.2**:
   - Áp dụng mảng regex cho **cả 2 trường** `admission_info` VÀ `contact_info`.
   - Sửa key hotline từ `'UNETI'` thành `'UNET'`.

3. **Khắc phục Bản vá 2.1**:
   - Thay đổi điều kiện ẩn Mobile Bar thành:
     ```php
     $is_compare = ( get_query_var( 'ltdh_compare' ) === 'program' ) || ( strpos( $_SERVER['REQUEST_URI'] ?? '', '/so-sanh/' ) !== false );
     $should_render_global_mobile_bar = ! is_singular( 'program' ) && ! $is_compare;
     ```
   - Không loại trừ `is_singular( 'major' )` để giữ CTA liên hệ cho trang ngành.

4. **Khắc phục Bản vá 2.4 & 2.5**:
   - Thêm guard `if ( ! is_wp_error( $term_link ) && ! empty( $term_link ) )` trước khi trả về URL canonical.
   - Gán `courseMode` động theo taxonomy thay vì hardcode `'Online'`; chỉ render `offers` khi `price > 0`.

5. **Khắc phục Bản vá 3.1**:
   - Mở rộng regex số điện thoại hỗ trợ đầu số máy bàn và đầu số mới:
     ```php
     $vn_phone_pattern = '/^(?:0|\+84|84)(?:(?:3[2-9]|5[25689]|7[06-9]|8[1-9]|9[0-9])[0-9]{7}|2[0-9]{8,9})$/';
     ```

6. **Khắc phục 2 file test dự án**:
   - Trong `tests/test-m4-adversarial.php`: Mock thêm `get_term_link()` và `get_template_directory_uri()`.
   - Trong `tests/test-m6-e2e-master-acceptance.php`: Mock thêm `get_template_directory_uri()`.

---

## 5. VERIFICATION METHOD (PHƯƠNG PHÁP KIỂM TRA ĐỘC LẬP)

Bất kỳ reviewer nào cũng có thể kiểm chứng độc lập các kết luận trên bằng các bước sau:

1. **Kiểm tra crash test suite gốc**:
   ```bash
   php "tests/test-m4-adversarial.php"
   # Quan sát: Fatal error get_term_link() tại inc/core/class-menus.php:174
   
   php "tests/test-m6-e2e-master-acceptance.php"
   # Quan sát: Fatal error get_template_directory_uri() tại inc/config/class-defaults.php:94
   ```

2. **Kiểm chứng rò rỉ dữ liệu trong `contact_info`**:
   ```bash
   php -r '$data = json_decode(file_get_contents("schools_import.json"), true); foreach($data as $s){ if(strpos($s["contact_info"]??"", "tên TVTS") !== false) echo "Found leak in contact_info of " . $s["code"] . "\n"; }'
   # Quan sát: Output xác nhận "Found leak in contact_info of UNET"
   ```

3. **Kiểm chứng biến so sánh**:
   - Mở `inc/comparison.php` xem dòng 23-45 để thấy rewrite rule gán vào `ltdh_compare`, chứng minh `is_page('so-sanh-chuong-trinh')` luôn là false.
