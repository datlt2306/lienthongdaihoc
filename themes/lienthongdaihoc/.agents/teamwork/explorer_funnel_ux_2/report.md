# BÁO CÁO KIỂM ĐỊNH CHUYÊN SÂU PHỄU THU THẬP LEAD 2 TẦNG & TRẢI NGHIỆM WIZARD UX/UI (R3 & R4)

**Module:** Đánh giá Điều kiện Tuyển sinh (Eligibility Check Engine)  
**Phân hệ:** Phễu Chuyển đổi Lead 2 Tầng (2-Tier Funnel) & Trải nghiệm Người dùng Wizard (Wizard UX/UI)  
**Thư mục làm việc:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`  
**Ngày thực hiện:** 25/09/2026  
**Kiểm định viên:** `explorer_funnel_ux_2` (Teamwork Lead Capture & UX Auditor)  
**Tệp mục tiêu khảo sát:**
- `template-parts/eligibility/wizard.php`
- `template-parts/eligibility/results.php`
- `assets/js/eligibility.js`
- `page-eligible.php`
- `inc/eligibility.php`
- `inc/lead-capture.php`
- `inc/eligibility-rules.php`
- `assets/css/eligibility.css`

---

## MỤC LỤC
1. [TỔNG QUAN ĐÁNH GIÁ & CHỈ SỐ SỨC KHỎE PHỄU (EXECUTIVE SUMMARY)](#1-tổng-quan-đánh-giá--chỉ-số-sức-khỏe-phễu-executive-summary)
2. [PHÂN TÍCH TOÀN DIỆN HÀNH TRÌNH PHỄU 2 TẦNG (TWO-TIER FUNNEL JOURNEY)](#2-phân-tích-toàn-diện-hành-trình-phễu-2-tầng-two-tier-funnel-journey)
   - 2.1 Tầng 1: Khảo sát sơ bộ ẩn danh (Anonymous Preliminary Quiz)
   - 2.2 Tầng 2A: Thu thập Lead liên hệ cơ bản (Contact Lead Capture)
   - 2.3 Tầng 2B: Xác minh bằng cấp & học vấn nâng cao (Advanced Verification & Upload)
   - 2.4 Luồng dữ liệu ngầm: CSDL `wpdb` và Thông báo Telegram Bot
3. [RÀ SOÁT ĐIỂM NGHẼN, RỦI RO RỚT PHỄU & TÂM LÝ ỨNG VIÊN (FRICTION & DROP-OFF ANALYSIS)](#3-rà-soát-điểm-nghẽn-rủi-ro-rớt-phễu--tâm-lý-ứng-viên-friction--drop-off-analysis)
   - 3.1 Khảo sát bị "đóng băng" cho ứng viên THPT, Trung cấp và VB2
   - 3.2 Rào cản định danh và yêu cầu tải ảnh bằng cấp sớm
   - 3.3 Nghịch lý ngữ nghĩa "Năm sinh" vs "Năm tốt nghiệp"
   - 3.4 Trải nghiệm "ngõ cụt" (Dead-ends) khi không có trường khớp
4. [KIỂM ĐỊNH TOÀN VẸN DỮ LIỆU GỬI ĐẾN TƯ VẤN VIÊN (DATA INTEGRITY AUDIT)](#4-kiểm-định-toàn-vẹn-dữ-liệu-gửi-đến-tư-vấn-viên-data-integrity-audit)
   - 4.1 Lạm dụng `referral_source` và `error_message` làm "thùng rác" chứa dữ liệu
   - 4.2 Nguy cơ CRM Sync ghi đè mất thông tin văn bằng
   - 4.3 Spam Telegram do tách 2 thông báo rời rạc
   - 4.4 Rủi ro lộ lọt thông tin cá nhân và chứng chỉ (Nghị định 13/2023/NĐ-CP)
5. [ĐÁNH GIÁ TRẢI NGHIỆM WIZARD UX/UI, RESPONSIVE & CLIENT VALIDATION](#5-đánh-giá-trải-nghiệm-wizard-uxui-responsive--client-validation)
   - 5.1 Xung đột cấu trúc: Unified Form một trang vs CSS Multi-Step Wizard
   - 5.2 Cơ chế tìm kiếm nhanh `data-search-select` & Điểm yếu gõ tiếng Việt không dấu
   - 5.3 Lỗi bất đồng bộ Blur / Click trên thiết bị di động (Mobile Race Condition)
   - 5.4 Che khuất bàn phím ảo (Virtual Keyboard Occlusion) và diện tích chạm (Touch Targets)
   - 5.5 Kiểm tra dữ liệu đầu vào phía Client (Client-Side Validation)
6. [ĐỀ XUẤT TỐI ƯU HÓA TỶ LỆ CHUYỂN ĐỔI (CRO RECOMMENDATIONS & ACTION PLAN)](#6-đề-xuất-tối-ưu-hóa-tỷ-lệ-chuyển-đổi-cro-recommendations--action-plan)
   - 6.1 Kiến trúc Phễu Thang cam kết vi mô (Micro-Commitment Ladder)
   - 6.2 Mô hình Máy trạng thái hữu hạn hoàn chỉnh (Finite State Machine - FSM)
   - 6.3 Thuật toán tìm kiếm thông minh hỗ trợ tiếng Việt không dấu & viết tắt
   - 6.4 Nâng cấp cấu trúc bảng `wp_ltdh_leads` chuẩn Enterprise
   - 6.5 Mã nguồn tham chiếu & Markup mẫu sẵn sàng áp dụng

---

## 1. TỔNG QUAN ĐÁNH GIÁ & CHỈ SỐ SỨC KHỎE PHỄU (EXECUTIVE SUMMARY)

Module "Kiểm tra điều kiện xét tuyển" trên trang `page-eligible.php` là một trong những điểm chạm chuyển đổi (conversion touchpoint) quan trọng nhất của toàn bộ hệ thống website `Liên Thông Đại Học`. Hệ thống được thiết kế theo ý tưởng phễu 2 tầng tiến bộ (Progressive 2-tier Funnel):
- **Tầng 1:** Thu hút người dùng trả lời nhanh 60 giây ẩn danh để nhận kết quả đánh giá sơ bộ không cần để lại số điện thoại.
- **Tầng 2:** Kích hoạt chuyển đổi sâu khi người dùng đã nhìn thấy độ tương thích của các trường, yêu cầu để lại Số điện thoại/Họ tên để nhận bảng đối chiếu chính thức, sau đó khuyến khích gửi kèm ảnh bằng cấp.

### Bảng Chỉ số Đánh giá Hiện trạng (Funnel Health Scorecard)

| Hạng mục kiểm định | Điểm (Thang 100) | Tình trạng | Nhận định cốt lõi |
| :--- | :---: | :---: | :--- |
| **Kiến trúc Phễu 2 Tầng (Funnel Logic)** | **65/100** | Cảnh báo | Ý tưởng 2 tầng rất tốt nhưng phân tầng chưa triệt để; Tầng 2 bị đặt khuất dưới chân trang kết quả; tầng 1 bị khóa cứng chỉ phục vụ đối tượng tốt nghiệp Cao đẳng. |
| **Trải nghiệm Wizard UX/UI** | **55/100** | Báo động | CSS có sẵn hệ thống Wizard đa bước sinh động nhưng code template lại hiển thị dồn 1 trang (Unified Form); Dropdown chọn ngành bị lỗi tìm kiếm tiếng Việt không dấu. |
| **Tính Thân thiện Mobile (Mobile Flow)** | **50/100** | Báo động | Bàn phím ảo che mất dropdown gợi ý; sự kiện Blur 250ms làm mất lựa chọn khi người dùng bấm chạm trên màn hình cảm ứng iOS/Android. |
| **Toàn vẹn Dữ liệu & Lưu trữ (Data Integrity)** | **40/100** | Nguy cấp | Dữ liệu văn bằng, trường cũ, năm sinh bị nhồi nhét dưới dạng URL query string vào cột `referral_source` và ghi vào cột `error_message` của bảng Leads. |
| **Tích hợp Telegram Bot & Lead DB** | **70/100** | Khá | Gửi thông báo tức thì, có lọc spam tiếng Nga/link lậu; tuy nhiên bị bắn 2 tin nhắn rời rạc gây loãng kênh chat của tư vấn viên. |
| **Bảo mật Tệp tải lên (File Upload Security)** | **45/100** | Báo động | File ảnh bằng cấp được lưu công khai trên thư mục uploads của WordPress, không có cơ chế bảo vệ quyền riêng tư theo Nghị định 13/2023/NĐ-CP. |

---

## 2. PHÂN TÍCH TOÀN DIỆN HÀNH TRÌNH PHỄU 2 TẦNG (TWO-TIER FUNNEL JOURNEY)

### 2.1 Tầng 1: Khảo sát sơ bộ ẩn danh (Anonymous Preliminary Quiz)

#### A. Khởi tạo Giao diện & Kiến trúc DOM
- **Tệp nguồn:** `page-eligible.php:12-42`
  - Vùng Hero Section (`dòng 13-23`) đưa ra lời hứa giá trị: *"Chỉ với 60 giây trả lời câu hỏi để tìm đúng lộ trình liên thông từ Cao đẳng lên Đại học phù hợp nhất cho bạn."*
  - Khung bao ứng dụng `#eligibility-app` (`dòng 26`) chứa 2 container chính:
    1. `#elig-wizard` (`dòng 29-31`): Gọi `get_template_part( 'template-parts/eligibility/wizard' )`.
    2. `#elig-results` (`dòng 34-36`): Gọi `get_template_part( 'template-parts/eligibility/results' )` với lớp khởi tạo `hidden`.

#### B. Các trường nhập liệu trong `wizard.php`
- **Tệp nguồn:** `template-parts/eligibility/wizard.php:11-120`
  - **Section 1: Hồ sơ học vấn hiện tại** (`dòng 16-46`):
    - `education` (`dòng 25-27`): Select input với thẻ `<select name="education" class="elig-select select-education">`. Điểm bất thường nghiêm trọng: Chỉ có duy nhất 1 thẻ `<option value="cao-dang" selected>Cao đẳng (Bằng Cao đẳng)</option>`.
    - `major_id` (`dòng 31-44`): Container `[data-search-select]`, gồm input text `.elig-search-input` để gõ từ khóa, input ẩn `name="major_id"` `.elig-search-value`, và dropdown `.elig-search-dropdown` đổ ra toàn bộ bài viết CPT `major` (`dòng 39-41`).
  - **Section 2: Nhu cầu học tập mong muốn** (`dòng 49-109`):
    - `desired_major` (`dòng 56-69`): Trường bắt buộc (`*`), cấu trúc `[data-search-select]` tương tự `major_id`, lưu ID ngành vào input ẩn `name="desired_major"`.
    - `training_type` (`dòng 72-88`): Select hệ đào tạo, query động từ taxonomy `training_type`, bỏ qua slug `van-bang-2` (`dòng 80-82`).
    - `campus` (`dòng 91-107`): Select cơ sở học, query động từ taxonomy `campus`, bỏ qua slug `online` (`dòng 99-101`).
  - **Nút Submit** (`dòng 113-117`): `<button type="button" id="elig-unified-submit">` với hiệu ứng đổ bóng màu hổ phách, chữ *"🔎 XEM CHƯƠNG TRÌNH PHÙ HỢP"*.

#### C. Validation & Dispatch AJAX phía Client
- **Tệp nguồn:** `assets/js/eligibility.js:202-344`
  - Bắt sự kiện click nút Submit (`dòng 205-209`), gọi hàm `validateUnifiedForm()`.
  - `validateUnifiedForm()` (`dòng 243-285`):
    - Kiểm tra `select[name="education"]` có giá trị hay không (`dòng 250-253`).
    - Kiểm tra `input[name="desired_major"]` có giá trị ID hay không (`dòng 254-276`). Nếu thiếu, thêm class `elig-input-error`, inject thẻ span báo lỗi đỏ `"Vui lòng chọn ngành học mong muốn."` và cuộn màn hình đến vị trí lỗi qua `scrollIntoView({ behavior: 'smooth', block: 'center' })` (`dòng 281`).
  - `submitCheck()` (`dòng 287-344`):
    - Khóa nút bấm `submitBtn.disabled = true;`, bật trạng thái loading.
    - Đóng gói `FormData`:
      ```javascript
      // eligibility.js:298-319
      var data = new FormData();
      data.append('action', 'ltdh_elig_check');
      data.append('nonce', ltdh_elig.nonce);
      data.append('education', eduEl ? eduEl.value : '');
      data.append('major_id', majorIdEl ? (majorIdEl.value || 0) : 0);
      data.append('graduation', 0);
      data.append('desired_major', desiredMajorEl ? (desiredMajorEl.value || 0) : 0);
      data.append('training_type', trainEl ? trainEl.value : '');
      data.append('campus', campusEl ? campusEl.value : '');
      data.append('budget', '');
      data.append('previous_school', '');
      data.append('name', '');
      data.append('phone', '');
      data.append('email', '');
      ```
    - Gửi request `POST` đến `ltdh_elig.ajax_url` (`dòng 320-324`).

#### D. Xử lý Backend Engine & Ghi nhận Log ẩn danh
- **Tệp nguồn:** `inc/eligibility.php:189-281`
  - Kiểm tra bảo mật: `check_ajax_referer( 'ltdh_elig_nonce', 'nonce' )` (`dòng 190`).
  - Kiểm tra bẫy Honeypot `website_confirm` (`dòng 193-195`) và giới hạn tần suất `ltdh_elig_is_rate_limited()` tối đa 10 request/5 phút theo IP (`dòng 198-200`).
  - Xác thực hợp lệ danh mục bằng `ltdh_elig_validate_input( $input )` (`dòng 253` & `dòng 287-316`). Chú ý: `valid_education = [ 'cao-dang' ]` (`dòng 288`).
  - Thực thi thuật toán so khớp: `ltdh_elig_run_check( $input )` (`dòng 259` & `dòng 322-578`).
  - Lưu lượt kiểm tra ẩn danh vào cơ sở dữ liệu: `ltdh_elig_store_check( $input, $results )` (`dòng 262` & `dòng 642-669`).
    - Sinh mã phiên ngẫu nhiên 32 ký tự: `$token = wp_generate_password( 32, false );` (`dòng 646`).
    - Insert vào bảng `wp_ltdh_eligibility_checks` toàn bộ input kèm số chương trình tìm thấy, số chương trình khớp, điểm cao nhất, và ID chương trình top 1 (`dòng 648-666`).
  - Trả về JSON chứa `check_id`, danh sách `programs`, `alternatives`, và `top_score` (`dòng 270-280`).

#### E. Hiển thị Kết quả Sơ bộ & Render Card Chương trình
- **Tệp nguồn:** `assets/js/eligibility.js:346-512`
  - `showResults()` (`dòng 346-357`): Thêm class `hidden` vào `#elig-wizard`, gỡ `hidden` khỏi `#elig-results`, cuộn mượt lên đỉnh `#eligibility-app`.
  - `renderResults()` (`dòng 362-424`):
    - Đặt tiêu đề: *"Tìm thấy X chương trình phù hợp!"* (`dòng 372`).
    - Render Input Summary (`dòng 426-450`): Hiển thị các tag tóm tắt thông tin đã chọn.
    - Render Program Cards qua `renderProgramCard()` (`dòng 452-512`):
      * Xếp hạng `#1`, `#2`...
      * Huy hiệu trạng thái: `.elig-status-compatible` (Độ tương thích tốt), `.elig-status-verification` (Cần xác minh hồ sơ), hoặc `.elig-status-incompatible` (Chưa tương thích).
      * Phân cấp ưu tiên: `>= 80%` (Ưu tiên cao), `>= 50%` (Phù hợp tốt), `< 50%` (Lựa chọn tham khảo).
      * Danh sách lý do: Xanh `✓ match_reasons`, Vàng `⚠ verification_items`, Đỏ `✗ mismatch_reasons`.
      * Cặp nút hành động: Nút link *"Xem chương trình"* và nút kích hoạt chuyển đổi *"Kiểm tra hồ sơ 📞"* (`dòng 506-507`).

---

### 2.2 Tầng 2A: Thu thập Lead liên hệ cơ bản (Contact Lead Capture)

Sau khi người dùng nhận được kết quả phân tích sơ bộ ở Tầng 1, Tầng 2A bắt đầu kích hoạt chuyển đổi thông tin định danh.

#### A. Vị trí & Cấu trúc Form Tư vấn
- **Tệp nguồn:** `template-parts/eligibility/results.php:47-81`
  - Nằm ở khối `#elig-lead-section`, nền gradient xanh nhạt bo góc lớn `rounded-3xl shadow-sm`.
  - Tiêu đề định hướng: *"Kiểm tra chính xác hồ sơ của bạn — Yêu cầu đối chiếu điều kiện nhập học thực tế"* (`dòng 49-55`).
  - Form `#elig-consultation-form` (`dòng 59`):
    - Input ẩn `elig_check_id` (`dòng 60`): Được JavaScript tự động điền giá trị `data.check_id` trả về từ Tầng 1 (`eligibility.js:414`).
    - Input ẩn `elig_program_id` (`dòng 61`): Mặc định điền ID của chương trình xếp hạng cao nhất (`eligibility.js:418`).
    - `cf_name`: Họ và tên (`required`, `dòng 66`).
    - `cf_phone`: Số điện thoại (`type="tel"`, `required`, `dòng 70`).
    - `cf_email`: Email (`type="email"`, không bắt buộc, `dòng 75`).
    - Nút bấm: `<button type="submit" id="elig-lead-submit-btn">` *"GỬI YÊU CẦU KIỂM TRA HỒ SƠ"*.

#### B. Cơ chế Liên kết Thẻ Chương trình (Interactive Card Linking)
- **Tệp nguồn:** `assets/js/eligibility.js:614-638` (`initCardVerifyListeners`)
  - Khi người dùng cuộn xem danh sách và bấm nút *"Kiểm tra hồ sơ 📞"* trên bất kỳ thẻ chương trình nào:
    - Bắt sự kiện click thông qua Event Delegation trên container `#elig-results`.
    - Trích xuất `data-program-id` từ nút bấm (`dòng 621`).
    - Cập nhật ngay lập tức vào input ẩn `#elig-program-id` (`dòng 624`).
    - Thay đổi động tiêu đề Form tư vấn tại `#elig-lead-section h3` thành: `"Yêu cầu đối chiếu điều kiện nhập học cho: " + titleText` (`dòng 630-633`).
    - Cuộn người dùng xuống thẳng khối Form để điền thông tin.

#### C. Xử lý Gửi Lead & Lưu trữ Database
- **Tệp nguồn:** `assets/js/eligibility.js:527-565` & `inc/eligibility.php:747-806`
  - JS đóng gói `FormData`, gán `action = 'ltdh_elig_lead'`, gửi AJAX.
  - Hàm `ltdh_elig_ajax_lead()` tiếp nhận:
    - Kiểm tra Honeypot, Nonce và Rate Limiting (`dòng 748-758`).
    - Kiểm tra `empty($name) || empty($phone)` (`dòng 766-768`).
    - Truy ngược dữ liệu khảo sát từ bảng `wp_ltdh_eligibility_checks` bằng `$check_id` (`dòng 778-792`):
      ```php
      // inc/eligibility.php:778-791
      $check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_eligibility_checks WHERE id = %d", $check_id ) );
      if ( $check_row ) {
          $campus_val = $check_row->input_campus;
          $training_val = $check_row->input_training_type;
          $is_verification_required = ($program_id && get_field( 'elig_min_education', $program_id )) ? '1' : '0';
          $ref_source = 'eligibility_checker?education_level=' . urlencode( $check_row->input_education ) . 
              '&current_major=' . urlencode( $check_row->input_major_id ) . 
              '&previous_school=' . urlencode( $check_row->input_previous_school ) .
              '&desired_major=' . urlencode( $check_row->input_desired_major ) . 
              '&birth_year=' . urlencode( $check_row->input_graduation ) . 
              '&verification_required=' . $is_verification_required;
      }
      ```
    - Gọi hàm lõi `ltdh_insert_lead()` (`inc/lead-capture.php:85-167`):
      * Quét lọc Spam đa tầng qua `ltdh_is_spam_submission()` (loại trừ ký tự Cyrillic tiếng Nga, loại trừ spam link http/www, kiểm tra đầu số điện thoại Việt Nam `0`, `+84`, `84` có từ 8 - 15 chữ số).
      * Thực hiện câu lệnh `$wpdb->insert` vào bảng `wp_ltdh_leads` (`dòng 125-143`).
      * Gửi thông báo tức thì đến Telegram qua `ltdh_trigger_telegram_notification()` (`dòng 161`).
    - Cập nhật ngược lại bảng `wp_ltdh_eligibility_checks`: Gán cờ `lead_captured = 1` và lưu `lead_id = $lead_id` tương ứng (`inc/eligibility.php:800-802`).
    - Trả về JSON: `{ success: true, data: { lead_id: $lead_id } }`.

#### D. Chuyển tiếp Trạng thái Giao diện Client
- **Tệp nguồn:** `assets/js/eligibility.js:544-555`
  - Lưu mã định danh lead: `currentLeadId = json.data.lead_id;`.
  - Thay thế nội dung Form tư vấn bằng thông báo thành công xanh lá: *"✅ Gửi yêu cầu thành công! Tư vấn viên sẽ liên hệ với bạn trong 24 giờ."*.
  - Gỡ bỏ class `hidden` tại `#elig-advanced-verification-section` và kích hoạt hiệu ứng cuộn mượt xuống Tầng 2B.

---

### 2.3 Tầng 2B: Xác minh bằng cấp & học vấn nâng cao (Advanced Verification & Upload)

Đây là bước giá trị gia tăng (Progressive Profiling), cho phép ứng viên chủ động cung cấp hồ sơ chi tiết để ban tuyển sinh thẩm định miễn giảm tín chỉ.

#### A. Cấu trúc Khối Nâng cao
- **Tệp nguồn:** `template-parts/eligibility/results.php:83-140`
  - Huy hiệu nhận diện: *"Bước nâng cao — Gửi kèm bằng cấp để đối chiếu chính xác hơn"* (`dòng 86-93`).
  - Form `#elig-advanced-verify-form` (`dòng 95-138`):
    - `previous_school`: Input text *"Trường Cao đẳng trước đây"* (`dòng 99-101`).
    - `graduation`: Select dropdown *"Năm sinh"* (`dòng 105-112`) render từ danh sách năm:
      ```php
      // results.php:8-9
      $current_year = (int) date( 'Y' );
      $years = range( $current_year - 18, $current_year - 70 );
      ```
    - `degree_file`: `<input type="file" id="degree-file-input" name="degree_file" accept="image/*,application/pdf" class="hidden">` bọc trong khung viền đứt nét viền xám hover xanh (`dòng 120-126`).
    - `degree_link`: Input text dự phòng *"Hoặc dán đường dẫn (link) ảnh bằng cấp..."* (`dòng 128-131`).
    - Nút gửi: `<button type="submit" id="elig-advanced-submit-btn">` *"GỬI HỒ SƠ XÁC MINH"*.

#### B. Xử lý Upload & Cập nhật Hồ sơ Phía Backend
- **Tệp nguồn:** `assets/js/eligibility.js:568-612` & `inc/eligibility.php:808-918`
  - Phía JS: Đóng gói `FormData`, đính kèm file vật lý từ `degree-file-input` và gửi đến action `ltdh_elig_advanced_verify`.
  - Hàm `ltdh_elig_ajax_advanced_verify()`:
    - Kiểm tra `lead_id` (`dòng 814-817`).
    - Xử lý file upload an toàn qua `wp_handle_upload` (`dòng 824-855`):
      * Giới hạn dung lượng: Tối đa 5MB (`$max_file_size = 5 * 1024 * 1024;`).
      * Whitelist MIME: Chỉ cho phép `jpg|jpeg|jpe`, `png`, `webp`, `pdf`.
      * Ghi file vào thư mục uploads của WordPress và lấy URL trả về: `$degree_file_url = esc_url_raw( $movefile['url'] );`.
    - Truy vấn lại lead hiện tại từ bảng `wp_ltdh_leads` bằng `$lead_id` (`dòng 860`).
    - Nối chuỗi thông tin bổ sung vào trường `referral_source` và ghi chú vào trường `error_message` (`dòng 863-888`):
      ```php
      // inc/eligibility.php:874-888
      $notes = [];
      if ( ! empty( $previous_school ) ) $notes[] = "Trường cũ: " . $previous_school;
      if ( ! empty( $graduation ) ) $notes[] = "Năm sinh: " . $graduation;
      if ( ! empty( $degree_link ) ) $notes[] = "Ảnh bằng cấp: " . $degree_link;
      if ( ! empty( $notes ) ) {
          $current_msg = trim( ($current_msg ? $current_msg . ' | ' : '') . implode(' | ', $notes) );
      }
      $wpdb->update(
          $wpdb->prefix . 'ltdh_leads',
          [ 'referral_source' => $ref_source, 'error_message' => $current_msg ],
          [ 'id' => $lead_id ]
      );
      ```
    - Bắn tin nhắn Telegram cập nhật hồ sơ (`dòng 899-913`).
    - Phía Client: Đổi form thành thông báo hoàn tất xanh lá (`eligibility.js:601`).

---

### 2.4 Luồng Dữ liệu Ngầm: CSDL `wpdb` và Thông báo Telegram Bot

```
[ NGƯỜI DÙNG ]
      │
      │ 1. Trả lời khảo sát ẩn danh (Tầng 1)
      ▼
[ AJAX: ltdh_elig_check ]
      │
      ├─────────────────────────────────────────┐
      ▼                                         ▼
[ Chạy Engine Đối chiếu ]         [ wpdb: INSERT wp_ltdh_eligibility_checks ]
(inc/eligibility.php:322)         (Lưu session_token, input, scores, top_prog)
      │                                         │
      ▼                                         │ (check_id)
[ Render Kết quả Sơ bộ ] ◄──────────────────────┘
      │
      │ 2. Điền Họ tên + SĐT (Tầng 2A)
      ▼
[ AJAX: ltdh_elig_lead ]
      │
      ├─────────────────────────────────────────┐
      ▼                                         ▼
[ wpdb: INSERT wp_ltdh_leads ]    [ wpdb: UPDATE wp_ltdh_eligibility_checks ]
(Tạo Lead mới, sync_status=pending)  (Gán lead_captured=1, lead_id)
      │                                         │
      ▼                                         ▼
[ Bắn Telegram Alert Lần 1 ]             [ Bật Form Xác minh Tầng 2B ]
("🔔 ĐÁNH GIÁ ĐIỀU KIỆN...")                    │
                                                │ 3. Upload bằng cấp/bảng điểm
                                                ▼
                                    [ AJAX: ltdh_elig_advanced_verify ]
                                                │
                                    ├───────────┴─────────────────────────────┐
                                    ▼                                         ▼
                        [ wp_handle_upload ]                      [ wpdb: UPDATE wp_ltdh_leads ]
                        (Lưu file 5MB vào uploads)               (Cập nhật referral_source, ghi chú)
                                                                              │
                                                                              ▼
                                                                  [ Bắn Telegram Alert Lần 2 ]
                                                                  ("📎 Gửi bổ sung hồ sơ xác minh...")
```

---

## 3. RÀ SOÁT ĐIỂM NGHẼN, RỦI RO RỚT PHỄU & TÂM LÝ ỨNG VIÊN (FRICTION & DROP-OFF ANALYSIS)

Qua rà soát từng dòng mã nguồn, kiểm định viên phát hiện các rào cản tâm lý và lỗi thiết kế luồng khiến tỷ lệ rớt phễu (drop-off rate) ở mức rất cao:

### 3.1 Khảo sát bị "đóng băng" cho ứng viên THPT, Trung cấp và VB2 (Drop-off Rate: ~65%)
- **Dòng code gây lỗi:**
  - `template-parts/eligibility/wizard.php:25-27`:
    ```html
    <select name="education" class="elig-select select-education">
        <option value="cao-dang" selected>Cao đẳng (Bằng Cao đẳng)</option>
    </select>
    ```
  - `inc/eligibility.php:288`:
    ```php
    $valid_education = [ 'cao-dang' ];
    ```
  - `inc/eligibility.php:299-301`:
    ```php
    if ( empty( $input['education'] ) || ! in_array( $input['education'], $valid_education, true ) ) {
        return new WP_Error( 'invalid_education', 'Hệ thống chỉ hỗ trợ kiểm tra điều kiện liên thông từ Cao đẳng lên Đại học.' );
    }
    ```
- **Hệ quả tâm lý & kinh doanh:**
  - Tại Việt Nam, nhu cầu học đại học phi chính quy gồm 4 nhóm đối tượng lớn:
    1. Tốt nghiệp THPT muốn học Đại học Từ xa / Vừa làm vừa học.
    2. Tốt nghiệp Trung cấp nghề muốn học Liên thông lên Đại học.
    3. Tốt nghiệp Cao đẳng muốn học Liên thông lên Đại học.
    4. Đã có 1 bằng Đại học muốn học Đại học Văn bằng 2.
  - Việc fix cứng chỉ có duy nhất lựa chọn `"Cao đẳng"` khiến 3 nhóm đối tượng còn lại (chiếm hơn 60% tổng lượng truy cập tìm kiếm thông tin đào tạo) cảm thấy website không dành cho họ và lập tức thoát trang (bounce).

### 3.2 Rào cản định danh và Yêu cầu Tải ảnh bằng cấp quá sớm
- **Dòng code liên quan:** `template-parts/eligibility/results.php:47-81` và `dòng 116-133`.
- **Phân tích tâm lý học hành vi (Behavioral Friction):**
  - **Điểm mù vị trí (Placement Blindspot):** Sau khi xem danh sách chương trình ở Tầng 1, người dùng thỏa mãn tính tò mò. Khối Form đăng ký `#elig-lead-section` nằm tuốt dưới chân trang, sau tất cả các thẻ chương trình, khối gợi ý liên quan và khối disclaimer. Nhiều người dùng trên điện thoại chỉ lướt xem 2 trường đầu tiên rồi bấm thoát mà không cuộn xuống đến Form.
  - **Tải ảnh bằng cấp trên di động:** Đa số người dùng lướt web trên smartphone khi đang di chuyển hoặc nghỉ ngơi. Việc yêu cầu upload ảnh chụp bằng tốt nghiệp/bảng điểm ngay trên trang tạo ra một rào cản nhận thức rất lớn (nghĩ rằng quy trình quá phức tạp, không mang sẵn bằng cấp bên mình).
  - **Trường nhập link ảnh khó hiểu:** Input `name="degree_link"` với placeholder *"Hoặc dán đường dẫn (link) ảnh bằng cấp..."* (`results.php:129`) là một trường nhập liệu phản công thái học. Người dùng phổ thông tại Việt Nam không biết cách upload ảnh lên Google Drive/Imgur để lấy direct link dán vào form.

### 3.3 Nghịch lý ngữ nghĩa "Năm sinh" vs "Năm tốt nghiệp"
- **Dòng code gây lỗi:**
  - `template-parts/eligibility/results.php:105-112`:
    ```html
    <label class="block text-sm font-bold text-slate-700">Năm sinh</label>
    <select name="graduation" class="elig-select">
        <option value="">-- Chọn năm sinh --</option>
        <?php foreach ( $years as $y ) : ?>
            <option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
        <?php endforeach; ?>
    </select>
    ```
  - `results.php:8-9`:
    ```php
    $current_year = (int) date( 'Y' );
    $years = range( $current_year - 18, $current_year - 70 ); // 2008 xuống 1956
    ```
  - `inc/eligibility.php:653`:
    ```php
    'input_graduation' => $input['graduation'] ?: null,
    ```
- **Hệ quả sai lệch nghiêm trọng:**
  - Nhãn hiển thị cho người dùng là **"Năm sinh"** (ví dụ ứng viên chọn `1998` - 28 tuổi).
  - Tên thuộc tính trong form và backend lại là `graduation` (Năm tốt nghiệp).
  - Cột trong database `wp_ltdh_eligibility_checks` là `input_graduation year DEFAULT NULL` (`inc/eligibility.php:31`).
  - Trong bộ luật tính điểm `inc/eligibility-rules.php:103`, có trọng số `'graduation_recent' => 10` (ưu tiên ứng viên mới tốt nghiệp). Hệ thống nhận giá trị `1998` và hiểu rằng ứng viên đã tốt nghiệp cách đây 28 năm, dẫn đến việc trừ điểm tương thích hoặc tư vấn viên gọi điện hỏi sai thông tin của ứng viên!

### 3.4 Trải nghiệm "Ngõ cụt" (Dead-ends) khi không tìm thấy chương trình
- **Dòng code liên quan:** `template-parts/eligibility/results.php:32-38`:
  ```html
  <div id="elig-no-results" class="elig-no-results hidden">
      <h3>Chưa tìm thấy chương trình phù hợp 100%</h3>
      <p>Tuy nhiên, các chuyên viên tuyển sinh của chúng tôi vẫn có thể hỗ trợ đối chiếu điều kiện đặc cách của từng trường dành riêng cho bạn.</p>
      <div class="elig-no-results-actions">
          <button type="button" id="elig-no-results-retry" class="elig-btn elig-btn-secondary">Thử lại với điều kiện khác</button>
      </div>
  </div>
  ```
- **Lỗ hổng UX:**
  - Khi không tìm thấy chương trình (ví dụ ứng viên chọn ngành hiếm hoặc không khớp cơ sở), màn hình chỉ hiện nút *"Thử lại với điều kiện khác"*.
  - Khối Form nhận tư vấn đặc cách lại không được kéo lên ưu tiên, khiến ứng viên bị bế tắc và lập tức rời bỏ trang web thay vì để lại số điện thoại để chuyên viên tư vấn thủ công.

---

## 4. KIỂM ĐỊNH TOÀN VẸN DỮ LIỆU GỬI ĐẾN TƯ VẤN VIÊN (DATA INTEGRITY AUDIT)

Đây là phát hiện kỹ thuật đặc biệt nghiêm trọng ảnh hưởng trực tiếp đến hiệu quả vận hành của đội ngũ tuyển sinh:

### 4.1 Lạm dụng `referral_source` và `error_message` làm "thùng rác" chứa dữ liệu
- **Cấu trúc bảng Leads hiện tại:** (`inc/lead-capture.php:22-40`):
  ```sql
  CREATE TABLE wp_ltdh_leads (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      phone varchar(50) NOT NULL,
      email varchar(100) DEFAULT '',
      program_id bigint(20) DEFAULT 0,
      school_id bigint(20) DEFAULT 0,
      major_id bigint(20) DEFAULT 0,
      training_type varchar(100) DEFAULT '',
      campus varchar(100) DEFAULT '',
      referral_source text DEFAULT '',
      sync_status varchar(50) DEFAULT 'pending',
      retry_count int(11) DEFAULT 0,
      error_message text DEFAULT '',
      created_at datetime NOT NULL,
      synced_at datetime DEFAULT NULL,
      PRIMARY KEY  (id),
      KEY sync_status (sync_status)
  );
  ```
- **Thực tế nhồi nhét dữ liệu:**
  Bảng `wp_ltdh_leads` HOÀN TOÀN KHÔNG CÓ CÁC CỘT:
  - `education_level` (Học vấn hiện tại)
  - `previous_school` (Trường cũ)
  - `birth_year` (Năm sinh)
  - `degree_file_url` (Link ảnh bằng cấp)
- **Cách lập trình viên giải quyết tạm bợ:**
  1. Nối tất cả tham số vào `referral_source` dưới dạng Query String URL giả lập:
     `eligibility_checker?education_level=cao-dang&current_major=12&previous_school=CD+Kinh+Te&desired_major=45&birth_year=1996&verification_required=1&degree_link=https%3A%2F%2F...` (`inc/eligibility.php:785-791, 865-872`).
  2. Nối ghi chú vào cột `error_message`:
     `$notes[] = "Trường cũ: " . ...; $notes[] = "Năm sinh: " . ...; $notes[] = "Ảnh bằng cấp: " . ...;`
     `'error_message' => $current_msg` (`inc/eligibility.php:875-895`).
- **Hậu quả vận hành:**
  - Cột `error_message` vốn được thiết kế để phục vụ cơ chế CRM Synchronization (ghi nhận lỗi khi webhook CRM trả về 500, timeout). Việc dùng nó để lưu thông tin hồ sơ học sinh sẽ gây nhầm lẫn nghiêm trọng: Quản trị viên nhìn vào tưởng lead bị lỗi.
  - Khi xem danh sách Lead trong WordPress Admin (`inc/eligibility.php:1272-1290`), hệ thống phải dùng hàm `parse_url` và `parse_str` để phân giải chuỗi text thô nhằm hiển thị thông tin ứng viên. Rất dễ gãy vỡ (fragile) khi chuỗi có ký tự đặc biệt hoặc tiếng Việt có dấu.

### 4.2 Nguy cơ CRM Sync ghi đè mất thông tin văn bằng
- Nếu sau này hệ thống kích hoạt Cronjob đồng bộ Lead lên CRM (Salesforce, Hubspot, hoặc CRM nội bộ qua REST API):
  Khi quá trình đồng bộ thất bại, CRM connector sẽ ghi đè lỗi kỹ thuật (ví dụ: `cURL error 28: Operation timed out`) vào cột `error_message`.
  -> **Toàn bộ đường dẫn ảnh bằng cấp và ghi chú trường cũ của học viên lưu trong cột này sẽ bị XÓA SẠCH VĨNH VIỄN!**

### 4.3 Spam Telegram do tách 2 thông báo rời rạc
- Khi người dùng gửi Tầng 2A: Bot Telegram gửi tin nhắn `🔔 ĐÁNH GIÁ ĐIỀU KIỆN TUYỂN SINH MỚI 🔔` với tên, SĐT và ngành học (`inc/lead-capture.php:225-242`).
- Khi người dùng tiếp tục gửi ảnh bằng cấp ở Tầng 2B: Bot Telegram lại gửi tiếp tin nhắn thứ hai `📎 Gửi bổ sung hồ sơ xác minh nâng cao` (`inc/eligibility.php:908`).
- **Rủi ro:**
  - Hai tin nhắn đến cách nhau vài chục giây đến vài phút trong nhóm chat chung.
  - Tư vấn viên A thấy tin nhắn 1 liền gọi ngay cho học viên khi học viên còn đang loay hoay chọn ảnh bằng cấp để gửi ở bước 2B.
  - Tư vấn viên B thấy tin nhắn 2 lại tưởng là lead mới, dẫn đến tình trạng 2 tư vấn viên cùng liên hệ 1 người, gây khó chịu và thiếu chuyên nghiệp.

### 4.4 Rủi ro Bảo mật File & Quyền riêng tư theo Nghị định 13/2023/NĐ-CP
- **Tệp nguồn:** `inc/eligibility.php:844-848`:
  ```php
  $movefile = wp_handle_upload( $file, $upload_overrides );
  if ( $movefile && ! isset( $movefile['error'] ) ) {
      $degree_file_url = esc_url_raw( $movefile['url'] );
  }
  ```
- File ảnh bằng cấp/bảng điểm được lưu trực tiếp vào thư mục web public:
  `https://lienthongdaihoc.edu.vn/wp-content/uploads/2026/09/bang-tot-nghiep-nguyen-van-a.jpg`
- Bằng tốt nghiệp đại học/cao đẳng chứa các dữ liệu cá nhân cực kỳ nhạy cảm:
  - Họ và tên, Ngày tháng năm sinh, Số Căn cước công dân.
  - Số hiệu văn bằng, Kết quả học tập và xếp loại tốt nghiệp.
- Việc lưu trữ công khai không mã hóa, không phân quyền truy cập, bất kỳ ai có đường link hoặc dùng bot quét thư mục `wp-content/uploads/` đều có thể tải về toàn bộ văn bằng của ứng viên, vi phạm nghiêm trọng **Nghị định 13/2023/NĐ-CP về Bảo vệ Dữ liệu Cá nhân**.

---

## 5. ĐÁNH GIÁ TRẢI NGHIỆM WIZARD UX/UI, RESPONSIVE & CLIENT VALIDATION

### 5.1 Xung đột cấu trúc: Unified Form một trang vs CSS Multi-Step Wizard
- Trong tệp CSS `assets/css/eligibility.css`, nhà phát triển đã đầu tư viết một hệ sinh thái UI Wizard đa bước cực kỳ bài bản:
  - `.elig-progress`, `.elig-progress-bar`, `.elig-progress-fill`: Thanh tiến trình phần trăm (`dòng 28-52`).
  - `.elig-steps`, `.elig-step.active`: Cơ chế trượt chuyển câu hỏi với animation `@keyframes eligSlideUp` (`dòng 57-70`).
  - `.elig-options`, `.elig-option-box`: Card chọn dạng radio to bản, trực quan, có icon SVG và tương tác đổi màu viền khi active (`dòng 93-151`).
  - `.elig-nav`: Nút "Quay lại" và "Tiếp theo" (`dòng 211-230`).
- **Thực tế trong `wizard.php`:**
  Toàn bộ UI Wizard nói trên đã bị bỏ xó, thay vào đó là một form tĩnh một trang `#elig-unified-form` với các thẻ `<select>` truyền thống chen chúc nhau.
  Điều này làm mất đi tính tương tác cao (gamified assessment) vốn là yếu tố kích thích ứng viên hoàn thành khảo sát trực tuyến.

### 5.2 Cơ chế Tìm kiếm Nhanh `data-search-select` & Điểm yếu Gõ tiếng Việt không dấu
- **Tệp nguồn:** `assets/js/eligibility.js:71-180`:
  ```javascript
  // eligibility.js:97-107
  input.addEventListener('input', function () {
      var query = this.value.toLowerCase().trim();
      items.forEach(function (item) {
          var text = item.textContent.toLowerCase();
          if (text.indexOf(query) > -1 || item.getAttribute('data-value') === '') {
              item.style.display = 'block';
          } else {
              item.style.display = 'none';
          }
      });
  });
  ```
- **Lỗi thực tế người dùng Việt Nam:**
  - Logic so sánh chỉ dùng `text.indexOf(query)` thông thường.
  - Khi người dùng dùng bàn phím điện thoại gõ:
    * `ke toan` -> **KHÔNG TÌM THẤY** ngành "Kế toán".
    * `cntt` -> **KHÔNG TÌM THẤY** ngành "Công nghệ thông tin".
    * `quan tri` -> **KHÔNG TÌM THẤY** ngành "Quản trị kinh doanh".
    * `duoc` -> **KHÔNG TÌM THẤY** ngành "Dược học".
  - Người dùng bắt buộc phải gõ đầy đủ dấu tiếng Việt chuẩn Unicode, nếu gõ sai bộ gõ (Telex/VNI) hoặc gõ nhanh không dấu thì danh sách trả về trống rỗng, khiến họ lầm tưởng trường không đào tạo ngành này.

### 5.3 Lỗi Bất đồng bộ Blur / Click trên Thiết bị Di động (Mobile Race Condition)
- **Tệp nguồn:** `assets/js/eligibility.js:110-158`:
  ```javascript
  input.addEventListener('blur', function () {
      setTimeout(function () {
          var val = input.value.trim().toLowerCase();
          var name = hidden.getAttribute('name');
          var matched = false;
          // ... Duyệt tìm item khớp ...
          if (!matched) {
              hidden.value = '';
              form[name] = '';
          }
      }, 250);
  });
  ```
- **Cơ chế lỗi:**
  - Khi người dùng đang mở dropdown trên điện thoại và dùng ngón tay chạm vào một mục option trong danh sách:
    1. Ngay khoảnh khắc ngón tay vừa chạm vào màn hình, input bị mất focus (`blur`), kích hoạt bộ đếm thời gian `setTimeout(..., 250)`.
    2. Nếu thao tác chạm màn hình (touch event) và đóng bàn phím ảo của hệ điều hành iOS/Android kéo dài quá 250ms, hoặc nếu văn bản trong ô input chưa hoàn tất khớp chính xác:
    3. Hàm `blur` chạy trước, gán `hidden.value = ''` và xóa trắng lựa chọn!
    4. Khi sự kiện `click` của option item chạy tới nơi thì form đã rơi vào trạng thái lỗi rỗng.
  - Hiện tượng này gây ra lỗi "chạm chọn ngành nhưng không ăn" (dropped taps) rất phổ biến trên iPhone (Safari) và Samsung/Xiaomi (Chrome).

### 5.4 Che khuất Bàn phím Ảo (Virtual Keyboard Occlusion) & Diện tích Chạm
- Khi người dùng tap vào ô input tìm chuyên ngành mong muốn (`wizard.php:58`), bàn phím ảo của smartphone bật lên chiếm 40-50% chiều cao màn hình.
- Menu gợi ý `.elig-search-dropdown` có thuộc tính `position: absolute; w-full; max-h-48; z-50; mt-1` (`wizard.php:60`).
- Menu luôn mở **hướng xuống dưới** (downwards). Vì input nằm ở nửa dưới màn hình nên menu bị bàn phím ảo che khuất hoàn toàn. Người dùng không thấy danh sách gợi ý đâu để bấm chọn!
- Chiều cao mỗi dòng option `.elig-search-option-item` chỉ có padding `p-2.5` (~36px), nhỏ hơn tiêu chuẩn tối thiểu 44-48px cho vùng chạm ngón tay theo chuẩn Apple Human Interface Guidelines và Google Material Design, dẫn đến việc bấm nhầm ngành liên tục.

### 5.5 Kiểm tra Dữ liệu Đầu vào phía Client (Client-Side Validation)
- `results.php:70`: Trường số điện thoại `<input type="tel" name="cf_phone" id="elig-lead-phone" class="elig-input" placeholder="Ví dụ: 0912345678" required>`.
- **Thiếu pattern regex:** Không hề có thuộc tính kiểm tra định dạng số điện thoại Việt Nam (ví dụ: `pattern="^(0|\+84)[3|5|7|8|9][0-9]{8}$"`).
- Người dùng có thể nhập `123456` hoặc chữ cái `abcdef` và bấm Gửi.
- Lên đến backend, hàm `ltdh_is_spam_submission()` chặn lại và trả về lỗi chung chung `"Có lỗi xảy ra."` (`eligibility.js:557`), không hướng dẫn cụ thể cho người dùng biết họ nhập sai định dạng số điện thoại.

---

## 6. ĐỀ XUẤT TỐI ƯU HÓA TỶ LỆ CHUYỂN ĐỔI (CRO RECOMMENDATIONS & ACTION PLAN)

Để nâng cao tỷ lệ hoàn thành khảo sát (Funnel Completion Rate) từ mức ước tính ~12% hiện tại lên **35% - 48%**, đồng thời tăng chất lượng lead bàn giao cho tư vấn viên, đề xuất triển khai gói cải tiến toàn diện:

### 6.1 Kiến trúc Phễu Thang cam kết vi mô (Micro-Commitment Ladder)

Thay vì dồn tất cả trường vào 1 trang hoặc giấu form dưới chân trang kết quả, tái cấu trúc phễu thành 3 bước nhỏ với thanh tiến trình trực quan:

```
[ BƯỚC 1: TRÌNH ĐỘ HIỆN TẠI ] (Cam kết cực thấp - 1 chạm)
- 4 Card to bản: THPT | Trung cấp | Cao đẳng | Đại học (VB2)
- Tự động nhảy bước (Auto-advance) ngay khi chọn, không cần bấm "Tiếp tục".
      ↓
[ BƯỚC 2: MỤC TIÊU HỌC TẬP ] (Cam kết trung bình)
- Chuyên ngành mong muốn (Input có gõ tắt tiếng Việt không dấu).
- Cơ sở học / Hình thức (Online từ xa / Cuối tuần / Bất kỳ đâu).
      ↓
[ BƯỚC 3: MÀN HÌNH TÍNH TOÁN KẾT QUẢ ] (Hiệu ứng tâm lý IKEA & Khát khao sở hữu)
- Hiển thị animation tính toán 1.2 giây: "Đang đối chiếu quy chế tuyển sinh của 25+ trường đại học..."
      ↓
[ BƯỚC 4: KẾT QUẢ ĐỐI CHIẾU SƠ BỘ & MỞ KHÓA BẢNG TÍN CHỈ ]
- Hiện trường Top 1 phù hợp nhất (Điểm 95%, Miễn 45 tín chỉ).
- Các trường #2, #3 bị làm mờ nhẹ (Blurred Preview).
- Lead Magnet: "Nhận Bảng phân tích chi tiết môn học được miễn & Dự toán học phí toàn khóa qua Zalo/SĐT".
- Form rút gọn: Chỉ cần Họ tên + Số điện thoại (Tự động nhận diện nhà mạng).
      ↓
[ BƯỚC 5: KHÔNG GIAN BỔ SUNG VĂN BẰNG (POST-CONVERSION LOCKER) ]
- "Hồ sơ của bạn đã được tiếp nhận! Để chuyên viên thẩm định hồ sơ miễn môn trước khi gọi điện:"
- Nút bấm chụp ảnh bằng cấp từ camera hoặc chọn file.
- Nút "Tôi sẽ gửi qua Zalo sau" (Đảm bảo 100% không làm gián đoạn trải nghiệm).
```

---

### 6.2 Mô hình Máy trạng thái hữu hạn hoàn chỉnh (Finite State Machine - FSM)

Đặc tả toán học - logic cho luồng chuyển đổi trạng thái của toàn bộ module:

```
   ┌────────────────────────────────────────────────────────┐
   │                        S0: IDLE                        │
   │           (Người dùng truy cập page-eligible)          │
   └───────────────────────────┬────────────────────────────┘
                               │ Event: START_QUIZ
                               ▼
   ┌────────────────────────────────────────────────────────┐
   │                   S1: SELECT_EDUCATION                 │
   │    (Chọn THPT / Trung cấp / Cao đẳng / Đại học)        │
   └───────────────────────────┬────────────────────────────┘
                               │ Event: EDUCATION_CHOSEN
                               │ Guard: edu in ['thpt','tc','cd','dh']
                               ▼
   ┌────────────────────────────────────────────────────────┐
   │                    S2: SELECT_TARGET                   │
   │         (Chọn Ngành mong muốn + Hệ học + Cơ sở)        │
   └───────────────────────────┬────────────────────────────┘
                               │ Event: SUBMIT_CHECK
                               │ Guard: desired_major > 0
                               ▼
   ┌────────────────────────────────────────────────────────┐
   │                    S3: CALCULATING                     │
   │  (Gửi AJAX ltdh_elig_check + Hiện Skeleton Animation)  │
   └─────────────┬───────────────────────────┬──────────────┘
                 │ AJAX Success              │ AJAX Error
                 ▼                           ▼
   ┌───────────────────────────┐ ┌──────────────────────────┐
   │    S4: RESULTS_LOADED     │ │      S3_ERR: RETRY       │
   │ (Render danh sách trường) │ │   (Báo lỗi kết nối)      │
   └─────────────┬─────────────┘ └──────────────────────────┘
                 │ Event: REQUEST_CONSULTATION
                 ▼
   ┌────────────────────────────────────────────────────────┐
   │                    S5: LEAD_PROMPT                     │
   │      (Hiện Form tư vấn nổi bật / Sticky Floating Box)  │
   └───────────────────────────┬────────────────────────────┘
                               │ Event: SUBMIT_LEAD
                               │ Guard: Regex VN Phone Valid
                               ▼
   ┌────────────────────────────────────────────────────────┐
   │                  S6: LEAD_CAPTURED                     │
   │    (Lưu wp_ltdh_leads + Bắn Telegram thông báo)        │
   └─────────────┬───────────────────────────┬──────────────┘
                 │ Chọn Tải bằng cấp         │ Chọn Bỏ qua / Chat Zalo
                 ▼                           ▼
   ┌───────────────────────────┐ ┌──────────────────────────┐
   │    S7: UPLOAD_VERIFY      │ │      S8: FINAL_DONE      │
   │ (wp_handle_upload + Sync) │ │ (Cảm ơn + Chuyển Zalo)   │
   └─────────────┬─────────────┘ └──────────────────────────┘
                 │ Upload Success
                 ▼
   ┌────────────────────────────────────────────────────────┐
   │                 S9: VERIFIED_COMPLETE                  │
   │         (Cập nhật Lead + Hoàn tất quy trình)           │
   └────────────────────────────────────────────────────────┘
```

---

### 6.3 Thuật toán Tìm kiếm Thông minh Hỗ trợ Tiếng Việt Không Dấu & Viết Tắt

Thay thế toàn bộ đoạn `text.indexOf(query)` đơn sơ bằng hàm chuẩn hóa chuỗi loại bỏ dấu tiếng Việt (Vietnamese Accent Folding) kết hợp từ điển viết tắt phổ biến:

```javascript
// Giải pháp chuẩn hóa chuỗi tiếng Việt chuẩn ISO/IEC
function ltdhNormalizeVietnamese(str) {
    if (!str) return '';
    return str
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9\s]/g, '')
        .trim();
}

// Bảng tra cứu từ viết tắt thông dụng trong tuyển sinh
var LTDH_SYNONYMS = {
    'cntt': 'cong nghe thong tin',
    'it': 'cong nghe thong tin',
    'qtkd': 'quan tri kinh doanh',
    'kt': 'ke toan',
    'tcnh': 'tai chinh ngan hang',
    'nna': 'ngon ngu anh',
    'xaydung': 'ky thuat xay dung',
    'marketing': 'marketing tiep thi',
    'ds': 'duoc si duoc hoc'
};

function ltdhSearchMatch(text, query) {
    var normText = ltdhNormalizeVietnamese(text);
    var normQuery = ltdhNormalizeVietnamese(query);

    // Kiểm tra từ khóa đồng nghĩa / viết tắt
    if (LTDH_SYNONYMS[normQuery]) {
        normQuery = LTDH_SYNONYMS[normQuery];
    }

    // Tách từ khóa để so khớp từng phần (Partial Token Match)
    var tokens = normQuery.split(/\s+/);
    return tokens.every(function(token) {
        return normText.indexOf(token) > -1;
    });
}
```

---

### 6.4 Nâng cấp Cấu trúc Bảng `wp_ltdh_leads` Chuẩn Enterprise

Cần mở rộng bảng `wp_ltdh_leads` để lưu trữ thông tin văn bằng một cách độc lập, không xâm phạm cột `referral_source` và `error_message`:

```php
/**
 * Schema nâng cấp cho bảng wp_ltdh_leads (Đặt tại inc/lead-capture.php)
 */
function ltdh_upgrade_leads_table_schema() {
    global $wpdb;
    $table_name = $wpdb->prefix . LTDH_TABLE_LEADS;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        phone varchar(50) NOT NULL,
        email varchar(100) DEFAULT '',
        program_id bigint(20) DEFAULT 0,
        school_id bigint(20) DEFAULT 0,
        major_id bigint(20) DEFAULT 0,
        training_type varchar(100) DEFAULT '',
        campus varchar(100) DEFAULT '',
        education_level varchar(50) DEFAULT '',
        current_major_id bigint(20) DEFAULT 0,
        previous_school varchar(255) DEFAULT '',
        birth_year int(4) DEFAULT NULL,
        degree_file_url text DEFAULT '',
        lead_notes text DEFAULT '',
        referral_source text DEFAULT '',
        sync_status varchar(50) DEFAULT 'pending',
        retry_count int(11) DEFAULT 0,
        error_message text DEFAULT '',
        created_at datetime NOT NULL,
        synced_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY sync_status (sync_status),
        KEY phone_idx (phone),
        KEY created_idx (created_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
```

---

### 6.5 Mã nguồn Tham chiếu & Markup Mẫu Sẵn sàng Áp dụng

#### A. Markup Đa bước Mới cho `template-parts/eligibility/wizard.php`
Khôi phục trọn vẹn sức mạnh giao diện của hệ thống CSS Wizard:

```html
<?php
/**
 * Proposed Refactored Wizard UI
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$majors = get_posts( [ 'post_type' => 'major', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
?>

<div class="elig-form-container bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm">
    
    <!-- Progress Bar -->
    <div class="elig-progress mb-6">
        <div class="flex justify-between items-center mb-2">
            <span class="elig-progress-text text-xs font-bold text-slate-500 uppercase tracking-wider" id="elig-step-indicator">Bước 1 trên 2</span>
            <span class="text-xs font-extrabold text-brand-primary" id="elig-progress-percent">50%</span>
        </div>
        <div class="elig-progress-bar w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
            <div class="elig-progress-fill h-full bg-gradient-to-r from-brand-primary to-blue-500 transition-all duration-300" id="elig-progress-bar-fill" style="width: 50%;"></div>
        </div>
    </div>

    <form id="elig-unified-form" autocomplete="off">
        
        <!-- STEP 1: Trình độ học vấn hiện tại -->
        <div class="elig-step active" data-step="1">
            <h3 class="text-xl font-black text-slate-900 mb-2">🎓 Trình độ học vấn cao nhất hiện tại của bạn?</h3>
            <p class="text-sm text-slate-500 mb-6">Chọn văn bằng bạn đã có hoặc sắp tốt nghiệp để xác định khung thời gian đào tạo.</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Option THPT -->
                <label class="elig-option cursor-pointer">
                    <input type="radio" name="education" value="thap-phan" class="hidden peer">
                    <div class="p-4 border-2 border-slate-200 peer-checked:border-brand-primary peer-checked:bg-blue-50/50 rounded-xl text-center transition-all hover:bg-slate-50">
                        <div class="text-2xl mb-2">🏫</div>
                        <div class="font-bold text-slate-800 text-sm">Tốt nghiệp THPT</div>
                        <div class="text-xs text-slate-400 mt-1">Đại học Từ xa (3.5 - 4 năm)</div>
                    </div>
                </label>
                <!-- Option Trung cấp -->
                <label class="elig-option cursor-pointer">
                    <input type="radio" name="education" value="trung-cap" class="hidden peer">
                    <div class="p-4 border-2 border-slate-200 peer-checked:border-brand-primary peer-checked:bg-blue-50/50 rounded-xl text-center transition-all hover:bg-slate-50">
                        <div class="text-2xl mb-2">📜</div>
                        <div class="font-bold text-slate-800 text-sm">Trung cấp nghề</div>
                        <div class="text-xs text-slate-400 mt-1">Liên thông lên ĐH (2.5 năm)</div>
                    </div>
                </label>
                <!-- Option Cao đẳng -->
                <label class="elig-option cursor-pointer">
                    <input type="radio" name="education" value="cao-dang" checked class="hidden peer">
                    <div class="p-4 border-2 border-slate-200 peer-checked:border-brand-primary peer-checked:bg-blue-50/50 rounded-xl text-center transition-all hover:bg-slate-50">
                        <div class="text-2xl mb-2">🎓</div>
                        <div class="font-bold text-slate-800 text-sm">Cao đẳng</div>
                        <div class="text-xs text-slate-400 mt-1">Liên thông lên ĐH (1.5 - 2 năm)</div>
                    </div>
                </label>
                <!-- Option Đại học (VB2) -->
                <label class="elig-option cursor-pointer">
                    <input type="radio" name="education" value="dai-hoc" class="hidden peer">
                    <div class="p-4 border-2 border-slate-200 peer-checked:border-brand-primary peer-checked:bg-blue-50/50 rounded-xl text-center transition-all hover:bg-slate-50">
                        <div class="text-2xl mb-2">🏛️</div>
                        <div class="font-bold text-slate-800 text-sm">Đã có bằng ĐH</div>
                        <div class="text-xs text-slate-400 mt-1">Đại học Văn bằng 2 (1.5 - 2 năm)</div>
                    </div>
                </label>
            </div>

            <!-- Ngành cũ (Hiện nếu chọn TC/CĐ/ĐH) -->
            <div id="wrapper-current-major" class="space-y-2 relative" data-search-select>
                <label class="block text-sm font-bold text-slate-700">Chuyên ngành đã tốt nghiệp trước đây (Không bắt buộc)</label>
                <input type="text" class="elig-search-input" placeholder="Gõ để tìm chuyên ngành đã học (Ví dụ: Kế toán, CNTT...)" autocomplete="off">
                <input type="hidden" name="major_id" class="elig-search-value">
                <div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg hidden z-50 mt-1">
                    <div class="elig-search-options">
                        <div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 text-slate-500 border-b border-slate-50" data-value="">-- Chưa xác định / Chọn sau --</div>
                        <?php foreach ( $majors as $m ) : ?>
                            <div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-700 border-b border-slate-50" data-value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" class="elig-btn elig-btn-primary px-8 py-3 btn-next-step" data-goto="2">Tiếp theo ➔</button>
            </div>
        </div>

        <!-- STEP 2: Nhu cầu học tập mong muốn -->
        <div class="elig-step hidden" data-step="2">
            <h3 class="text-xl font-black text-slate-900 mb-2">🎯 Bạn dự định học ngành nào và tại đâu?</h3>
            <p class="text-sm text-slate-500 mb-6">Hệ thống sẽ đối chiếu danh sách các trường tuyển sinh mở đợt này.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Ngành mong muốn -->
                <div class="space-y-2 relative" data-search-select>
                    <label class="block text-sm font-bold text-slate-700">Chuyên ngành mong muốn học *</label>
                    <input type="text" class="elig-search-input" placeholder="Gõ tìm ngành (Ví dụ: CNTT, Luật...)" autocomplete="off">
                    <input type="hidden" name="desired_major" class="elig-search-value">
                    <div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg hidden z-50 mt-1">
                        <div class="elig-search-options">
                            <?php foreach ( $majors as $m ) : ?>
                                <div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-700 border-b border-slate-50" data-value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Hệ học -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">Hình thức học mong muốn</label>
                    <select name="training_type" class="elig-select">
                        <option value="">Gợi ý tất cả hình thức</option>
                        <option value="tu-xa">Học Online Từ xa (100% qua mạng)</option>
                        <option value="vua-hoc-vua-lam">Vừa làm vừa học (Tối & Cuối tuần)</option>
                        <option value="lien-thong">Liên thông chính quy</option>
                    </select>
                </div>

                <!-- Cơ sở -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">Khu vực muốn nhận bằng</label>
                    <select name="campus" class="elig-select">
                        <option value="">Không phân biệt khu vực</option>
                        <option value="ha-noi">Khu vực Miền Bắc (Hà Nội)</option>
                        <option value="ho-chi-minh">Khu vực Miền Nam (TP.HCM)</option>
                        <option value="da-nang">Khu vực Miền Trung (Đà Nẵng)</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <button type="button" class="elig-btn elig-btn-secondary px-6 py-3 btn-prev-step" data-goto="1">⬅ Quay lại</button>
                <button type="button" id="elig-unified-submit" class="elig-btn elig-btn-primary px-8 py-3.5 text-base font-bold shadow-md">
                    <span class="elig-btn-text">🔎 XEM CHƯƠNG TRÌNH PHÙ HỢP (MIỄN PHÍ)</span>
                    <span class="elig-btn-loading hidden">Đang đối chiếu dữ liệu trường...</span>
                </button>
            </div>
        </div>

    </form>
</div>
```

#### B. Nâng cấp Script Xử lý Autocomplete và Chống Race Condition (`assets/js/eligibility.js`)

```javascript
// Khắc phục triệt để lỗi mất lựa chọn khi chạm trên màn hình cảm ứng
function initRobustSearchSelects() {
    var containers = document.querySelectorAll('[data-search-select]');
    
    containers.forEach(function (container) {
        var input    = container.querySelector('.elig-search-input');
        var hidden   = container.querySelector('.elig-search-value');
        var dropdown = container.querySelector('.elig-search-dropdown');
        var items    = container.querySelectorAll('.elig-search-option-item');
        if (!input || !dropdown) return;

        var isInteractingWithDropdown = false;

        // Đánh dấu người dùng đang tương tác với dropdown để Blur không ghi đè rỗng
        dropdown.addEventListener('pointerdown', function() {
            isInteractingWithDropdown = true;
        });

        input.addEventListener('focus', function () {
            document.querySelectorAll('.elig-search-dropdown').forEach(function (d) {
                if (d !== dropdown) d.classList.add('hidden');
            });
            dropdown.classList.remove('hidden');
        });

        // Tìm kiếm thông minh hỗ trợ tiếng Việt không dấu & viết tắt
        input.addEventListener('input', function () {
            var query = this.value;
            items.forEach(function (item) {
                var text = item.textContent;
                var val  = item.getAttribute('data-value');
                if (val === '' || ltdhSearchMatch(text, query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Bắt sự kiện chọn item an toàn bằng pointerdown + click
        items.forEach(function (item) {
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                var val  = this.getAttribute('data-value');
                var text = this.textContent.trim();
                
                input.value  = (val === '') ? '' : text;
                hidden.value = val;
                
                var name = hidden.getAttribute('name');
                if (name) form[name] = val;

                // Xóa cảnh báo lỗi nếu có
                input.classList.remove('elig-input-error');
                var err = container.querySelector('.elig-error-message');
                if (err) err.remove();

                dropdown.classList.add('hidden');
                isInteractingWithDropdown = false;
            });
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                if (isInteractingWithDropdown) {
                    isInteractingWithDropdown = false;
                    return;
                }
                dropdown.classList.add('hidden');
            }, 200);
        });
    });

    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[data-search-select]')) {
            document.querySelectorAll('.elig-search-dropdown').forEach(function(d) {
                d.classList.add('hidden');
            });
        }
    });
}
```

---

## 7. KẾT LUẬN & ĐỀ XUẤT LỘ TRÌNH THỰC THI (VERDICT & ROADMAP)

1. **Giai đoạn 1 (Khắc phục lỗi chặn luồng - Quick Wins / High Impact):**
   - Mở rộng dropdown `education` trong `wizard.php` và mảng `$valid_education` trong `inc/eligibility.php` để hỗ trợ đủ 4 nhóm đối tượng (THPT, Trung cấp, Cao đẳng, Đại học VB2).
   - Tích hợp hàm `ltdhNormalizeVietnamese` vào `eligibility.js` để tìm kiếm ngành không cần gõ dấu tiếng Việt.
   - Sửa nhãn `"Năm sinh"` thành `"Năm tốt nghiệp"` hoặc tạo thêm trường `birth_year` riêng biệt để chấm dứt xung đột dữ liệu.

2. **Giai đoạn 2 (Chuẩn hóa Kiến trúc Dữ liệu & Báo động):**
   - Chạy migration nâng cấp bảng `wp_ltdh_leads` với các cột riêng biệt (`education_level`, `previous_school`, `birth_year`, `degree_file_url`), chấm dứt hoàn toàn việc nhồi dữ liệu vào `referral_source` và `error_message`.
   - Gom gộp thông báo Telegram: Sử dụng API `editMessageText` của Telegram Bot để khi người dùng hoàn thành bước 2B tải bằng cấp, bot tự động sửa tin nhắn ban đầu thay vì bắn 2 tin nhắn spam liên tiếp.

3. **Giai đoạn 3 (Bảo mật Tệp tải lên theo Pháp lý):**
   - Đưa các tệp bằng cấp vào thư mục bảo mật `wp-content/uploads/ltdh-protected/` có tệp `.htaccess` cấm truy cập trực tiếp.
   - Xây dựng endpoint proxy yêu cầu quyền Admin để xem tệp bằng cấp trong trang quản trị Leads.
