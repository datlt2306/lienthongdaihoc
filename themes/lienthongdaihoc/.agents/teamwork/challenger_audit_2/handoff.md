# BÁO CÁO PHẢN BIỆN THỰC NGHIỆM ĐỘC LẬP (EMPIRICAL ADVERSARIAL CHALLENGE REPORT)
## KIỂM TRA PHỄU THU THẬP LEAD, TRẢI NGHIỆM WIZARD UX/UI, XỬ LÝ DỮ LIỆU & BẢO MẬT TRONG `ELIGIBILITY_BUSINESS_AUDIT.md`

- **Người thực hiện**: `challenger_audit_2` (Empirical Challenger: Critic & Specialist)
- **Đối tượng phản biện**: Báo cáo kiểm định `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md`
- **Mã nguồn đối chiếu**:
  1. `assets/js/eligibility.js` (dòng 95 - 180, dòng 240 - 320)
  2. `template-parts/eligibility/results.php` (dòng 8 - 15, dòng 95 - 140)
  3. `template-parts/eligibility/wizard.php` (dòng 25 - 30, dòng 55 - 75, dòng 110 - 130)
  4. `inc/eligibility.php` (dòng 760 - 945)
  5. `inc/lead-capture.php` (dòng 15 - 45, dòng 85 - 285)
- **Thời điểm hoàn thành**: 2026-09-25T15:28:00+07:00
- **Phán quyết chính thức (Verdict)**: 🛑 **REQUEST_CHANGES** (Yêu cầu điều chỉnh báo cáo kiểm toán trước khi đội ngũ kỹ sư triển khai Sprint 0 / Sprint 1)

---

## 1. OBSERVATION (QUAN SÁT THỰC NGHIỆM TRỰC TIẾP)

Dưới đây là các quan sát thực nghiệm từ dòng mã nguồn thực tế, thực thi lệnh và kết quả kiểm thử:

### 1.1. Quan sát 1: Hiện trạng bất đồng bộ Blur & Đề xuất cờ `isInteractingWithDropdown`
- **Mã nguồn thực tế trong `assets/js/eligibility.js` (dòng 110-158)**:
  ```javascript
  input.addEventListener('blur', function () {
      setTimeout(function () {
          var val = input.value.trim().toLowerCase();
          var name = hidden.getAttribute('name');
          var matched = false;
          // ... Duyệt items, nếu không khớp exact thì tìm substring text.indexOf(val) > -1
          if (!matched) {
              hidden.value = '';
              form[name] = '';
          }
      }, 250);
  });
  ```
- **Hành vi thực nghiệm trên thiết bị cảm ứng (Mobile Touchscreen)**:
  - Khi người dùng chạm vào một option trong danh sách dropdown `.elig-search-dropdown`: trình duyệt di động (iOS Safari / Android Chrome) kích hoạt `blur` trên `<input>` ngay khi ngón tay chạm màn hình (`touchstart`).
  - Do cơ chế trễ tương tác (300ms tap delay trên mobile hoặc thao tác chạm giữ kéo dài > 250ms), hàm `blur` chạy trước khi sự kiện `click` của option kịp kích hoạt.
  - Khi người dùng cuộn (scroll) danh sách dropdown (`max-h-48 overflow-y-auto` chứa hơn 50 ngành), thao tác vuốt làm mất nét `<input>` -> timer 250ms chạy -> tự động cưỡng bức chọn option đầu tiên có chứa chuỗi con hoặc xóa trắng (`hidden.value = ''`), làm biến đổi giá trị của biểu mẫu ngay giữa lúc người dùng đang duyệt danh sách.
- **Đề xuất trong `ELIGIBILITY_BUSINESS_AUDIT.md` (dòng 1128-1134)**:
  ```javascript
  dropdown.addEventListener('pointerdown', function() {
      isInteractingWithDropdown = true;
  });
  ```
  Báo cáo chỉ đề xuất gán cờ `isInteractingWithDropdown = true` trên sự kiện `pointerdown` của container dropdown mà **hoàn toàn không định nghĩa logic xóa cờ về `false`** khi thao tác kết thúc hoặc khi người dùng hủy bỏ, dẫn đến nguy cơ rò rỉ trạng thái (flag leak) vô hiệu hóa toàn bộ cơ chế validate blur về sau.

### 1.2. Quan sát 2: Thuật toán tìm kiếm tiếng Việt và từ điển viết tắt
- **Đề xuất trong `ELIGIBILITY_BUSINESS_AUDIT.md` (dòng 1076-1122)**:
  ```javascript
  function ltdhNormalizeVietnamese(str) {
      if (!str) return '';
      return str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g, 'd').replace(/[^a-z0-9\s]/g, '').trim();
  }
  var LTDH_SYNONYMS = {
      'cntt': 'cong nghe thong tin',
      'marketing': 'marketing tiep thi',
      'ds': 'duoc si duoc hoc',
      'kt': 'ke toan',
      // ...
  };
  function ltdhSearchMatch(text, query) {
      var normText = ltdhNormalizeVietnamese(text);
      var normQuery = ltdhNormalizeVietnamese(query);
      if (LTDH_SYNONYMS[normQuery]) {
          normQuery = LTDH_SYNONYMS[normQuery];
      }
      var tokens = normQuery.split(/\s+/);
      return tokens.every(function(token) {
          return normText.indexOf(token) > -1;
      });
  }
  ```
- **Kết quả chạy thực nghiệm (Empirical Execution via Node.js v22.17.1)**:
  - **Lỗi chí mạng 1 (False Negative Breaking Bug)**:
    - Tìm kiếm `"marketing"` cho ngành `"Marketing"`: Trả về **`false`**!
      *Giải thích*: `LTDH_SYNONYMS['marketing']` bị mở rộng thành `'marketing tiep thi'`. Hàm `tokens.every(...)` đòi hỏi tiêu đề ngành phải chứa cả 3 từ `"marketing"`, `"tiep"`, `"thi"`. Tên ngành gốc là "Marketing" không hề có chữ "tiep" hay "thi", nên bị đánh trượt 100%!
    - Tìm kiếm `"ds"` cho ngành `"Dược học"`: Trả về **`false`**!
      *Giải thích*: Mở rộng thành `"duoc si duoc hoc"` đòi hỏi phải có từ `"si"`. Ngành "Dược học" không chứa từ "si", nên người dùng gõ "ds" không bao giờ tìm thấy ngành Dược!
  - **Lỗi chí mạng 2 (Whole-Query-Only Lockout)**:
    - Tìm kiếm `"ngành cntt"` cho `"Công nghệ thông tin"`: Trả về **`false`**!
    - Tìm kiếm `"học cntt từ xa"` cho `"Công nghệ thông tin"`: Trả về **`false`**!
    - Tìm kiếm `"đại học qtkd"` cho `"Quản trị kinh doanh"`: Trả về **`false`**!
    - *Giải thích*: Câu lệnh `if (LTDH_SYNONYMS[normQuery])` chỉ kiểm tra nguyên chuỗi. Khi người dùng gõ từ phụ tự nhiên (ngành, học, lớp...), từ viết tắt `cntt` không được nhận diện, và do `cntt` không nằm trong chuỗi "cong nghe thong tin", kết quả trả về rỗng!
  - **Lỗi chí mạng 3 (Compound Diacritics & Punctuation Conflation)**:
    - Biểu thức `.replace(/[^a-z0-9\s]/g, '')` thay thế ký tự đặc biệt bằng chuỗi rỗng `''` thay vì khoảng trắng `' '`.
    - Tên ngành `"Kỹ thuật Điện-Điện tử"` (không có dấu cách quanh dấu gạch nối) bị chuẩn hóa thành `"ky thuat diendien tu"`.
    - Tên ngành `"Toán/Tin ứng dụng"` bị chuẩn hóa thành `"toantin ung dung"`.
    - Ngành `"Khoa học & Kỹ thuật"` biến thành `"khoa hoc  ky thuat"` (2 dấu cách) mà không có hàm chuẩn hóa khoảng trắng thừa `replace(/\s+/g, ' ')`.

### 1.3. Quan sát 3: Xung đột ngữ nghĩa "Năm sinh" vs `graduation` & Khoảng năm khởi tạo trong PHP
- **Mã nguồn thực tế trong `template-parts/eligibility/results.php` (dòng 8-9 & 105-112)**:
  ```php
  $current_year = intval( date( 'Y' ) );
  $years = range( $current_year - 18, $current_year - 70 );
  // ...
  <label class="block text-sm font-bold text-slate-700">Năm sinh</label>
  <select name="graduation" class="elig-select">
      <option value="">-- Chọn năm sinh --</option>
      <?php foreach ( $years as $y ) : ?>
          <option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
      <?php endforeach; ?>
  </select>
  ```
- **Kết quả chạy thực nghiệm khoảng năm (PHP 8.4.19)**:
  - Năm hiện tại: 2026.
  - Giá trị lớn nhất trong mảng `$years`: $2026 - 18 = \mathbf{2008}$.
  - Giá trị nhỏ nhất trong mảng `$years`: $2026 - 70 = \mathbf{1956}$.
  - **Khuyến nghị của báo cáo kiểm toán tại Mục 9.1 (Dòng 1390)**:
    *"5. Đổi nhãn 'Năm sinh' thành 'Năm tốt nghiệp' tại template-parts/eligibility/results.php:105 để chấm dứt xung đột ngữ nghĩa."*
  - **Lỗi thực nghiệm phát sinh**:
    Nếu lập trình viên chỉ đổi nhãn hiển thị thành "Năm tốt nghiệp" mà không sửa code khởi tạo mảng `$years`:
    Thí sinh tốt nghiệp các năm **2009, 2015, 2020, 2024, 2025, 2026** (toàn bộ phân khúc học viên đang có nhu cầu liên thông thực tế) **hoàn toàn không có năm tốt nghiệp của mình trong danh sách dropdown**! Năm gần nhất họ có thể chọn là năm 2008 (cách đây 18 năm).

### 1.4. Quan sát 4: Bất khả thi kỹ thuật của `editMessageText` trên HTTP Non-blocking & Vi phạm Nghị định 13
- **Mã nguồn gửi Telegram trong `inc/lead-capture.php` (dòng 260-280)**:
  ```php
  $chat_ids = preg_split( '/[\s,;]+/', $chat_id );
  $api_url = "https://api.telegram.org/bot" . urlencode( $bot_token ) . "/sendMessage";
  foreach ( $chat_ids as $single_chat_id ) {
      wp_remote_post( $api_url, [
          'body' => [
              'chat_id'    => $single_chat_id,
              'text'       => $msg_text,
              'parse_mode' => 'HTML',
          ],
          'timeout'  => 10,
          'blocking' => false, // Non-blocking request!
      ] );
  }
  ```
- **Các điểm xung đột kiến trúc thực tế**:
  1. Khi `'blocking' => false`, WordPress ngắt kết nối cURL ngay sau khi gửi dữ liệu và **vứt bỏ hoàn toàn phản hồi từ máy chủ Telegram** (`wp_remote_retrieve_body()` luôn rỗng). Do đó, hệ thống **không thể lấy được `message_id`** để lưu vào cơ sở dữ liệu như đề xuất của báo cáo kiểm toán.
  2. Hệ thống hỗ trợ đa kênh nhận thông báo (`$chat_ids` phân tách bởi dấu phẩy, khoảng trắng). Khi gửi vào nhiều nhóm chat, mỗi nhóm sẽ có một `message_id` độc lập khác nhau. Cột CSDL `telegram_message_id int(11)` đề xuất trong báo cáo kiểm toán là kiểu số nguyên đơn vị, không thể lưu trữ ID của nhiều nhóm chat.
  3. Cơ chế `editMessageText` của Telegram là thao tác chỉnh sửa tĩnh, **hoàn toàn không phát ra âm thanh thông báo hay rung màn hình (Silent Notification)**. Nếu tư vấn viên không chủ động kéo lại tin nhắn cũ, việc thí sinh gửi thêm bằng cấp quan trọng sẽ bị bỏ sót.
- **Rà soát Nghị định 13/2023/NĐ-CP và Lỗ hổng bảo mật nghiêm trọng (IDOR)**:
  - Form Tầng 2A và Tầng 2B hoàn toàn không có hộp kiểm chấp thuận (Consent Checkbox) xử lý dữ liệu cá nhân theo Điều 11 NĐ 13/2023/NĐ-CP.
  - Việc gửi thông tin số điện thoại, họ tên và liên kết ảnh bằng cấp qua bot Telegram (máy chủ đặt ngoài lãnh thổ Việt Nam) thuộc phạm vi điều chỉnh của **Điều 25 NĐ 13/2023/NĐ-CP về chuyển dữ liệu cá nhân ra nước ngoài**, đòi hỏi phải có sự đồng ý tường minh của chủ thể dữ liệu và lập hồ sơ đánh giá tác động.
  - **Lỗ hổng IDOR chưa từng được ghi nhận trong báo cáo**: Tại `inc/eligibility.php:838-848`, hàm `ltdh_elig_ajax_advanced_verify()` nhận tham số `$lead_id = intval($_POST['lead_id'] ?? 0)` và chỉ kiểm tra nonce công khai `ltdh_elig_nonce`. Bất kỳ ai cũng có thể gửi `POST` với `lead_id` ngẫu nhiên từ 1 đến 10000 để ghi đè ghi chú, chèn liên kết giả mạo vào hồ sơ ứng viên khác và kích hoạt spam hàng loạt thông báo Telegram!

---

## 2. LOGIC CHAIN (CHUỖI SUY LUẬN TỪ QUAN SÁT ĐẾN KẾT LUẬN)

1. **Từ Quan sát 1 (Blur Race Condition & Pointerdown)**:
   - Cơ chế dùng `setTimeout(..., 250)` để đợi sự kiện `click` là một antipattern kinh điển trong lập trình giao diện Web. Trên thiết bị di động, thao tác chạm hoặc vuốt cuộn dropdown kích hoạt `blur` ngay lập tức, và độ trễ sự kiện trên màn hình cảm ứng thường vượt quá 250ms.
   - Giải pháp đề xuất `isInteractingWithDropdown` qua `pointerdown` của báo cáo kiểm toán thiếu vòng đời dọn dẹp cờ (state cleanup). Nếu người dùng vuốt trượt để cuộn mà không click chọn, cờ vẫn mang giá trị `true`, gây lỗi logic cho các tương tác kế tiếp.
   - *Suy luận kỹ thuật*: Giải pháp chuẩn mực và triệt để nhất của WAI-ARIA Combobox là gọi `event.preventDefault()` trực tiếp trên sự kiện `pointerdown`/`mousedown` của các phần tử dropdown. Lệnh `preventDefault()` trên `pointerdown` ngăn trình duyệt chuyển tiêu điểm (prevent focus shift), giúp `<input>` **không bao giờ bị kích hoạt sự kiện `blur`** trong suốt quá trình người dùng thao tác trong danh sách tùy chọn.

2. **Từ Quan sát 2 (Thuật toán Tìm kiếm Tiếng Việt & Từ điển Viết tắt)**:
   - Thuật toán `ltdhSearchMatch` trong báo cáo kiểm toán mắc sai lầm nghiêm trọng khi đồng nhất "Từ đồng nghĩa/Từ viết tắt" (Synonyms/Acronyms) với "Từ khóa bắt buộc đồng thời" (Conjunctive AND Tokens).
   - Khi gán `'marketing': 'marketing tiep thi'`, hàm biến một truy vấn 1 từ thành điều kiện bắt buộc tiêu đề phải có đủ cả 3 từ `marketing`, `tiep`, và `thi`. Do đó, bất kỳ chuyên ngành nào chỉ đặt tên chuẩn mực quốc tế là "Marketing" đều bị loại trừ.
   - Khi người dùng gõ theo văn phong tự nhiên ("ngành cntt", "học cntt"), việc kiểm tra nguyên chuỗi `if (LTDH_SYNONYMS[normQuery])` hoàn toàn vô dụng.
   - *Suy luận kỹ thuật*: Từ điển viết tắt phải được thiết kế theo mô hình ánh xạ thay thế cụm từ (Phrase/Token Substitution) hoặc cơ chế tìm kiếm OR phân nhánh (Alternative Branching): `(token === 'cntt' || token === 'cong nghe thong tin')`. Đồng thời, các ký tự phân cách như `-`, `/`, `&` phải được thay thế bằng dấu cách (`' '`) trước khi loại bỏ ký tự lạ để tránh dính từ ("Điện-Điện tử" -> "diendien tu").

3. **Từ Quan sát 3 (Xung đột "Năm sinh" vs `graduation` & Mảng `$years`)**:
   - Tác giả báo cáo kiểm toán nhận thấy nhãn hiển thị là "Năm sinh" nhưng biến backend là `graduation`, nên đưa ra khuyến nghị tại Sprint 0 là: Đổi nhãn thành "Năm tốt nghiệp".
   - Tuy nhiên, tác giả đã không kiểm tra dòng 8-9 của `template-parts/eligibility/results.php`. Đoạn code PHP tại đây sinh mảng năm từ `$current_year - 18` xuống `$current_year - 70`. Đây là công thức tính tuổi công dân từ 18 đến 70 tuổi (chính xác dành cho Năm sinh).
   - Nếu áp dụng mù quáng khuyến nghị của Sprint 0, toàn bộ ứng viên vừa tốt nghiệp Cao đẳng từ năm 2009 đến 2026 sẽ bị chặn đứng vì không có năm tốt nghiệp để chọn.
   - *Suy luận kỹ thuật*: Cần phân định rõ ràng 2 trường dữ liệu:
     - Nếu thu thập **Năm sinh** (như nhãn giao diện hiện hành): Đổi tên biến HTML thành `name="birth_year"`, giữ nguyên dải năm $1956 - 2008$.
     - Nếu thu thập **Năm tốt nghiệp** (như mục tiêu tính điểm `graduation_recent`): Sửa vòng lặp PHP thành `$years = range( $current_year, $current_year - 25 );` ($2001 - 2026$).
     - Nếu thu thập cả hai (như bảng SQL đề xuất): Phải cung cấp 2 trường select độc lập với logic sinh dải năm tương ứng.

4. **Từ Quan sát 4 (Telegram Non-blocking, IDOR & Tuân thủ Nghị định 13)**:
   - Báo cáo kiểm toán đề xuất giải pháp Sprint 2 dùng `editMessageText` để cập nhật tin nhắn Telegram khi học viên tải bằng cấp ở Tầng 2B. Nhưng báo cáo không nhận ra rằng `wp_remote_post` trong theme đang chạy chế độ `blocking => false`. Muốn lấy được `message_id`, bắt buộc phải chuyển sang blocking hoặc dùng queue xử lý nền.
   - Thêm vào đó, việc âm thầm chỉnh sửa tin nhắn cũ (`editMessageText`) làm mất đi tính chủ động nhắc việc của tư vấn viên vì Telegram không báo chuông đối với tin nhắn sửa.
   - Báo cáo kiểm toán hoàn toàn bỏ sót lỗ hổng bảo mật IDOR tại `ltdh_elig_ajax_advanced_verify()`: người dùng có thể gửi bất kỳ `lead_id` nào để thao túng dữ liệu khách hàng khác và kích hoạt spam bot.
   - *Suy luận kỹ thuật*: Giải pháp thông báo tối ưu là gửi tin nhắn trả lời phân luồng (`reply_to_message_id`) thay vì chỉ edit đơn thuần. Đồng thời phải vá ngay lỗ hổng IDOR bằng cách cấp `verification_token` (HMAC sha256) khi tạo lead ở Tầng 2A và kiểm tra token này ở Tầng 2B; bổ sung hộp kiểm đồng ý chính sách bảo mật theo Điều 11 NĐ 13.

---

## 3. CAVEATS (GIỚI HẠN & GIẢ ĐỊNH)

1. **Môi trường thử nghiệm**: Các thực nghiệm về chuỗi, Unicode, JSON payload và thuật toán được kiểm chứng trực tiếp thông qua công cụ Node.js v22.17.1 và PHP 8.4.19 CLI trên môi trường máy chủ cục bộ của dự án.
2. **Khía cạnh chưa kiểm tra tải cao (High Concurrency)**: Chưa đo lường độ trễ mạng thực tế của Telegram Bot API khi gửi hàng trăm webhook đồng thời trong các đợt cao điểm tuyển sinh mùa thi.
3. **Phạm vi bảo toàn mã nguồn gốc**: Tuân thủ nguyên tắc Review-Only của archetype, toàn bộ mã nguồn của theme (`assets/js/eligibility.js`, `inc/eligibility.php`, `results.php`, v.v.) được giữ nguyên vẹn 100%, không bị sửa đổi trong suốt quá trình kiểm định phản biện.

---

## 4. CONCLUSION (KẾT LUẬN & PHÁN QUYẾT)

### 🛑 Phán quyết chính thức: REQUEST_CHANGES

Báo cáo kiểm định `ELIGIBILITY_BUSINESS_AUDIT.md` là một tài liệu có giá trị phân tích nghiệp vụ giáo dục rất sâu sắc, đã chỉ ra chính xác các sai lệch trọng yếu của hệ thống (vi phạm Thông tư 28/2023 về cấm từ xa ngành Y Dược/Sư phạm, công thức học phí nhân sai 3,6 tỷ đồng, khóa cứng Cao đẳng).

Tuy nhiên, **ở phân hệ Kỹ thuật Funnel, Thuật toán Tìm kiếm, UX Tương tác và Bảo mật dữ liệu**, báo cáo kiểm toán hiện đang tồn tại **4 khiếm khuyết kỹ thuật nghiêm trọng**:

1. **Thuật toán tìm kiếm đề xuất tại Mục 8.2 gây lỗi đánh trượt (False Negative)**: Tìm kiếm "Marketing" không ra ngành "Marketing", tìm kiếm "ds" không ra ngành "Dược học", và không hỗ trợ người dùng gõ từ khóa tự nhiên ("ngành cntt").
2. **Khuyến nghị Sprint 0 tại Mục 9.1 về đổi tên "Năm sinh" thành "Năm tốt nghiệp" sẽ phá hỏng biểu mẫu**: Khiến 100% thí sinh tốt nghiệp từ năm 2009 đến 2026 không thể chọn được năm tốt nghiệp của mình do vướng dải năm $1956 - 2008$ trong PHP.
3. **Giải pháp Telegram `editMessageText` tại Mục 8.5 không khả thi với mã nguồn hiện tại**: Bị triệt tiêu bởi cờ `'blocking' => false`, không hỗ trợ đa kênh chat, và làm mất tính năng chuông thông báo đẩy khi có bằng cấp mới.
4. **Bỏ sót lỗ hổng bảo mật IDOR và thiếu sót pháp lý Nghị định 13**: Không phát hiện lỗ hổng cho phép kẻ tấn công sửa đổi lead bất kỳ qua endpoint AJAX, chưa đưa yêu cầu hộp kiểm chấp thuận (Consent) theo Điều 11 NĐ 13/2023/NĐ-CP vào thiết kế giao diện.

### Các Yêu Cầu Cụ Thể Cần Điều Chỉnh Trong Báo Cáo `ELIGIBILITY_BUSINESS_AUDIT.md`:

1. **Sửa lại Thuật toán tìm kiếm tại Mục 8.2**:
   - Loại bỏ việc gán từ viết tắt thành danh sách bắt buộc AND (`every`).
   - Chuẩn hóa tách token, thay thế ký tự đặc biệt bằng khoảng trắng `replace(/[^a-z0-9]/g, ' ')`.
   - Áp dụng cơ chế thay thế token viết tắt trong truy vấn thay vì chỉ so khớp nguyên chuỗi (`normQuery`).
2. **Sửa lại Khuyến nghị tại Mục 9.1 (Giai đoạn 1 - Hành động 5) & Mục 8.4**:
   - Quy định rõ ràng: Sửa cả nhãn và cấu trúc mảng `$years` trong PHP. Tách biệt `birth_year` (dải $1956 - 2008$) và `graduation_year` (dải $2001 - 2026$).
3. **Cập nhật Thiết kế Thông báo Telegram tại Mục 8.5**:
   - Chuyển `wp_remote_post` sang chế độ lấy `message_id` hoặc sử dụng kiến trúc gửi tin nhắn trả lời liên kết (`reply_to_message_id`) kèm ping âm thanh để thông báo cho tư vấn viên khi có bằng cấp mới.
   - Thiết kế lưu trữ ID tin nhắn tương thích với danh sách nhiều `chat_id`.
4. **Bổ sung Phân tích Lỗ hổng IDOR & Tuân thủ Nghị định 13 tại Mục 3.2, Mục 4.5 và Mục 8**:
   - Thêm hạng mục vá lỗi IDOR bằng `lead_verification_token` vào danh mục P0/P1.
   - Thêm quy định về Hộp kiểm Đồng ý xử lý dữ liệu cá nhân (Consent Checkbox) vào thiết kế Wizard và Lead Form.

---

## 5. VERIFICATION METHOD (HƯỚNG DẪN KIỂM CHỨNG ĐỘC LẬP)

Bất kỳ chuyên viên hay kỹ sư nào cũng có thể độc lập tái hiện và kiểm chứng 100% các phát hiện phản biện nêu trên bằng cách chạy các lệnh kiểm thử sau trong terminal:

### Bước 1: Kiểm chứng lỗi thuật toán tìm kiếm "Marketing" và "ds"
Chạy lệnh sau tại thư mục gốc của theme:
```bash
node -e '
function ltdhNormalizeVietnamese(str) {
    if (!str) return "";
    return str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[đĐ]/g, "d").replace(/[^a-z0-9\s]/g, "").trim();
}
var LTDH_SYNONYMS = { "cntt": "cong nghe thong tin", "marketing": "marketing tiep thi", "ds": "duoc si duoc hoc" };
function ltdhSearchMatch(text, query) {
    var normText = ltdhNormalizeVietnamese(text);
    var normQuery = ltdhNormalizeVietnamese(query);
    if (LTDH_SYNONYMS[normQuery]) normQuery = LTDH_SYNONYMS[normQuery];
    var tokens = normQuery.split(/\s+/);
    return tokens.every(token => normText.indexOf(token) > -1);
}
console.log("Tìm Marketing trong ngành Marketing:", ltdhSearchMatch("Marketing", "marketing"));
console.log("Tìm ds trong ngành Dược học:", ltdhSearchMatch("Dược học", "ds"));
console.log("Tìm ngành cntt trong Công nghệ thông tin:", ltdhSearchMatch("Công nghệ thông tin", "ngành cntt"));
'
```
*Điều kiện hợp lệ*: Cả 3 kết quả trên đều trả về `false` -> Chứng minh thuật toán trong báo cáo kiểm toán bị lỗi.

### Bước 2: Kiểm chứng lỗi dải năm tốt nghiệp trong PHP
Chạy lệnh sau:
```bash
php -r '
$current_year = 2026;
$years = range( $current_year - 18, $current_year - 70 );
echo "Max year: " . max($years) . "\n";
echo "Can a 2024 graduate select 2024? " . (in_array(2024, $years) ? "YES" : "NO") . "\n";
'
```
*Điều kiện hợp lệ*: `Max year: 2008`, `Can a 2024 graduate select 2024? NO` -> Chứng minh đổi nhãn thành "Năm tốt nghiệp" mà không đổi mảng `$years` sẽ làm tê liệt biểu mẫu.

### Bước 3: Kiểm chứng cờ non-blocking trong gửi Telegram
Mở tệp `inc/lead-capture.php` tại dòng 278 và kiểm tra thuộc tính:
`'blocking' => false`
Xác nhận rằng hàm không nhận body phản hồi từ Telegram Bot API, khiến việc lưu `message_id` là bất khả thi.

### Bước 4: Kiểm chứng lỗ hổng IDOR tại AJAX Advanced Verify
Mở tệp `inc/eligibility.php` tại dòng 838-848 và 917-924:
Xác nhận tham số `$_POST['lead_id']` được sử dụng trực tiếp để truy vấn và cập nhật bảng `wp_ltdh_leads` mà không có bất kỳ cơ chế kiểm tra token quyền sở hữu nào.
