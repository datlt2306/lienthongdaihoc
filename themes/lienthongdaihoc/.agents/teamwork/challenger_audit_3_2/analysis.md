# BÁO CÁO PHẢN BIỆN & KIỂM CHỨNG THỰC NGHIỆM ĐỘC LẬP
## ADVERSARIAL CHALLENGE & EMPIRICAL VERIFICATION REPORT
### ĐỐI TƯỢNG: `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` (REQUIREMENTS R3 & R4)

- **Mã kiểm định**: `LTDH-CHALLENGE-AUDIT-3-2-2026`
- **Chuyên gia phản biện**: `challenger_audit_3_2` (Teamwork Empirical Challenger: Critic & Specialist)
- **Tài liệu phản biện**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`
- **Mã nguồn thực nghiệm**:
  - `front-page.php` (dòng 432-448, 526-532)
  - `inc/lead-capture.php` (dòng 15-45, 90-165, 188-275)
  - `inc/crm-adapters.php` (dòng 60-125, 126-210)
  - `inc/eligibility.php` (dòng 760-778, 935-975)
  - `inc/relationship-hooks.php` (dòng 12-74)
  - `inc/core/class-rewrite-rules.php` (dòng 41-101, 145-163)
  - `assets/js/main.js` (dòng 9-50)
  - `ELIGIBILITY_BUSINESS_AUDIT.md` (dòng 1152-1175)
- **Ngày thực hiện**: 28/09/2026
- **Phán quyết tổng quát (Verdict)**: 🛑 **REQUEST_CHANGES** (Xác nhận tính chính xác 100% của các phát hiện lỗi, nhưng **YÊU CẦU ĐIỀU CHỈNH 3 ĐIỂM NGHẼN KỸ THUẬT NGUY HIỂM** trong giải pháp đề xuất trước khi chuyển giao lập trình viên triển khai Phase 1 & Phase 2).

---

## 1. TỔNG QUAN PHẢN BIỆN (EXECUTIVE CHALLENGE SUMMARY)

Báo cáo kiểm định `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` do `worker_audit_3` thực hiện là một công trình kiểm toán có chiều sâu kỹ thuật và tính phát hiện rất xuất sắc. Các phát hiện cốt lõi về **Lỗ hổng mất dữ liệu khách hàng (CRM Data Loss Bug)**, **Vi phạm nghiêm trọng Luật Quảng cáo tại Badge cam `front-page.php`**, **Bẫy N+1 query tại trang Danh bạ trường**, và **Dead Code AJAX Filter** đều hoàn toàn có thật trong mã nguồn và đã được kiểm chứng thực nghiệm 100%.

Tuy nhiên, dưới góc độ của **Empirical Challenger** với tư duy phản biện đối nghịch (Adversarial Stress-Testing), cuộc kiểm tra phát hiện **3 lỗ hổng và rủi ro triển khai nghiêm trọng** trong các đoạn mã giải pháp mà báo cáo đề xuất:

1. **Rủi ro Mất mát dữ liệu lịch sử trong Script SQL Migration (Section 5.6 & 7.2)**: Báo cáo đề xuất lệnh `ALTER TABLE wp_ltdh_leads ADD COLUMN message TEXT...` nhưng **hoàn toàn bỏ quên câu lệnh Backfill dữ liệu**. Hàng trăm lead đang chờ xử lý (`pending`) có lời nhắn đang tạm lưu trong cột `error_message` sẽ bị bỏ rơi thành `NULL`, dẫn đến việc lập trình viên đổi code đọc cột `message` sẽ làm "bốc hơi" toàn bộ ghi chú lịch sử của thí sinh. Đồng thời, script hardcode prefix `wp_` và thiếu tính lũy đẳng (idempotency).
2. **Bẫy Vòng đời Hook ACF trong `LTDH_Entity_Relationship_Engine` (Section 2.4)**: Đoạn mã đề xuất dùng hook `add_action( 'save_post_program', ... )` kết hợp với `get_post_meta()`. Trong WordPress, khi biên tập viên chỉnh sửa bài viết trong WP Admin, hook `save_post` chạy **TRƯỚC** khi ACF lưu dữ liệu custom fields vào database. Lớp xử lý mới sẽ đọc ra dữ liệu quan hệ trường/ngành cũ (stale meta), khiến cơ chế rollup bị sai lệch hoàn toàn.
3. **Lỗi Mã hóa URL Telegram Token & Mất kiểm soát đầu mối quản trị trung tâm (Section 5.6)**: Hàm đề xuất `ltdh_trigger_telegram_notification_v2` sử dụng `rawurlencode( $bot_token )`. Cú pháp token của Telegram Bot bắt buộc có dấu hai chấm `:` (ví dụ `123456789:ABC...`); việc `rawurlencode` biến `:` thành `%3A`, khiến request tới Telegram Bot API báo lỗi 404/401. Đồng thời, logic định tuyến sang nhóm chat riêng của trường đã ghi đè và cắt đứt luồng thông báo về nhóm Telegram quản trị trung tâm.

---

## 2. KIỂM ĐỊNH TÍNH PHÁP LÝ: THÔNG TƯ 27/2019/TT-BGDĐT & QUY CHẾ VĂN BẰNG (REQUIREMENT R3)

### 2.1. Đối chiếu quy định pháp lý thực tế
Báo cáo kiểm định viện dẫn:
> *Thông tư 27/2019/TT-BGDĐT quy định bãi bỏ việc ghi hình thức đào tạo trên trang chính của văn bằng, nhưng Điều 3 bắt buộc phải ghi rõ hình thức đào tạo trên Phụ lục văn bằng (Diploma Supplement).*

**Kết quả xác minh độc lập**:
- Căn cứ theo **Thông tư số 27/2019/TT-BGDĐT** ngày 30/12/2019 của Bộ Giáo dục và Đào tạo (có hiệu lực từ ngày 01/03/2020 thay thế Thông tư 19/2011/TT-BGDĐT):
  - **Điều 2 (Nội dung chính ghi trên văn bằng giáo dục đại học)**: Liệt kê chính xác 10 nội dung bắt buộc in trên văn bằng (Tiêu ngữ, Tên cơ sở GDĐH, Tên văn bằng, Ngành đào tạo, Họ tên người học, Ngày sinh, Hạng tốt nghiệp nếu có, Địa danh ngày cấp, Chức danh chữ ký người cấp, Số hiệu vào sổ gốc). **Quy định này ĐÃ BÃI BỎ HOÀN TOÀN nội dung "Hình thức đào tạo"** (chính quy, vừa làm vừa học, từ xa) vốn tồn tại trong Thông tư 19/2011 trước đó.
  - **Điều 3 (Nội dung chính ghi trên phụ lục văn bằng giáo dục đại học)**: Khoản 1 Điểm c (và Khoản 2) quy định bắt buộc phải ghi: Tên cơ sở giáo dục đại học cấp bằng, chuyên ngành đào tạo, ngày nhập học, ngôn ngữ đào tạo, thời gian đào tạo, trình độ đào tạo theo Khung trình độ quốc gia Việt Nam, và **HÌNH THỨC ĐÀO TẠO** (*Đào tạo chính quy*, *Vừa làm vừa học*, hoặc *Đào tạo từ xa*).
- Căn cứ theo **Khoản 2 Điều 6 và Điều 65 Luật Giáo dục đại học sửa đổi năm 2018 (Luật số 34/2018/QH14)**:
  - Luật khẳng định 3 hình thức đào tạo đại học gồm: Chính quy, vừa làm vừa học, đào tạo từ xa.
  - Văn bằng đại học của các hình thức đào tạo thuộc hệ thống giáo dục quốc dân có **giá trị pháp lý như nhau** trong tuyển dụng cán bộ công chức, viên chức, xét xếp lương và học tiếp lên trình độ cao hơn (Thạc sĩ, Tiến sĩ).

### 2.2. Kết luận phản biện về khía cạnh pháp lý
- Nhận định của `worker_audit_3` là **hoàn toàn chính xác về mặt căn cứ pháp lý**: "Giá trị pháp lý tương đương" không có nghĩa là "Đào tạo từ xa là đào tạo chính quy".
- Hình thức đào tạo vẫn được ghi nhận minh bạch và không thể xóa bỏ trên **Phụ lục văn bằng** và trong **Sổ gốc cấp văn bằng** của cơ sở giáo dục đại học.
- Khi người học mang văn bằng đi xin việc vào các cơ quan, doanh nghiệp hoặc nộp hồ sơ nâng ngạch, nhà tuyển dụng luôn yêu cầu nộp kèm Phụ lục văn bằng (bảng điểm kèm theo).

---

## 3. KIỂM ĐỊNH MÃ NGUỒN FRONTEND: VI PHẠM QUẢNG CÁO TẠI `front-page.php`

### 3.1. Điểm nóng 1: Badge cam "100% BẰNG CỬ NHÂN CHÍNH QUY" (`front-page.php:526-532`)
Thực hiện quan sát trực tiếp tệp `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/front-page.php`:

```html
526: 						<!-- Orange badge -->
527: 						<div class="absolute bottom-6 left-6 bg-[#f97316] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[150px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
528: 							<span class="text-3xl font-black leading-none">100%</span>
529: 							<span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>
530: 						</div>
```

- **Thực nghiệm**: Dòng 528-530 ghi chính xác từng ký tự:
  `<span class="text-3xl font-black leading-none">100%</span>`
  `<span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>`
- **Đánh giá rủi ro**: Website quảng bá các chương trình tuyển sinh Đại học Từ xa và Liên thông Đại học. Việc in chữ "100% BẰNG CỬ NHÂN CHÍNH QUY" là hành vi **quảng cáo gian dối về tính chất, chất lượng dịch vụ giáo dục**, vi phạm nghiêm trọng **Khoản 9 Điều 8 Luật Quảng cáo 2012** và **Nghị định 38/2021/NĐ-CP**. Mức xử phạt vi phạm hành chính có thể lên tới 70 - 100 triệu đồng và buộc gỡ bỏ nội dung quảng cáo.

### 3.2. Điểm nóng 2: Thuật ngữ dân gian "Bằng đỏ" (`front-page.php:431-435`)
Thực hiện quan sát trực tiếp tệp `front-page.php`:

```html
431: 								<div>
432: 									<h4 class="font-bold text-slate-900 text-base">Bằng đỏ</h4>
433: 									<p class="text-slate-500 text-sm leading-relaxed">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân (Bằng đỏ), được Bộ GD&ĐT công nhận.</p>
434: 								</div>
```

- **Thực nghiệm**: Dòng 432 nguyên bản có thẻ `<h4>Bằng đỏ</h4>` và dòng 433 ghi "cấp bằng Cử nhân (Bằng đỏ)".
- **Đánh giá**: Trong danh mục văn bằng giáo dục đại học Việt Nam, chỉ có bằng Cử nhân, Kỹ sư, Kiến trúc sư, Thạc sĩ, Tiến sĩ. Khái niệm "bằng đỏ" là thuật ngữ dân gian (thường chỉ bằng tốt nghiệp loại Xuất sắc). Việc đưa từ ngữ này vào ấn phẩm tuyển sinh chính thức tạo cam kết sai lệch và làm suy giảm tính học thuật của cổng thông tin.

---

## 4. TÁI HIỆN THỰC NGHIỆM LỖ HỔNG MẤT DỮ LIỆU LEAD (CRM DATA LOSS BUG)

### 4.1. Chuỗi thực thi dẫn tới lỗi mất dữ liệu (Trace Execution Flow)
Thực hiện rà soát đồng thời 3 tệp mã nguồn:
1. `inc/lead-capture.php` (dòng 23-40): Bảng CSDL `$table_name = $wpdb->prefix . LTDH_TABLE_LEADS` được khởi tạo với các cột:
   `id, name, phone, email, program_id, school_id, major_id, training_type, campus, referral_source, sync_status, retry_count, error_message, created_at, synced_at`.
   👉 **BẢNG HOÀN TOÀN KHÔNG CÓ CỘT `message`**.
2. `inc/lead-capture.php` (dòng 125-143): Khi hàm `ltdh_insert_lead()` tiếp nhận dữ liệu từ form tư vấn, do thiếu cột `message`, lập trình viên đã mượn cột `error_message` để lưu lời nhắn của học viên:
   ```php
   139: 'error_message' => $message,
   ```
3. `inc/crm-adapters.php` (dòng 98-113): Hàm `ltdh_sync_lead_to_crm()` đóng gói `$payload` để gửi sang API của OnSchool / AUM. Mảng `$payload` **hoàn toàn không chứa trường `error_message` hay `message`**! Lời nhắn của thí sinh không bao giờ được gửi tới đối tác.
4. `inc/crm-adapters.php` (dòng 64-84): Tiến trình WP-Cron chạy hàm `ltdh_process_lead_queue()` định kỳ 5 phút:
   - **Trường hợp A (CRM sync thành công)**:
     ```php
     75: } else {
     76:     $wpdb->update(
     77:         $table_name,
     78:         [
     79:             'sync_status'   => 'synced',
     80:             'synced_at'     => current_time( 'mysql' ),
     81:             'error_message' => '', // 🚨 XÓA TRẮNG HOÀN TOÀN CỘT ERROR_MESSAGE!
     82:         ],
     83:         [ 'id' => $lead->id ]
     84:     );
     ```
   - **Trường hợp B (CRM sync thất bại)**:
     ```php
     65:     $wpdb->update(
     66:         $table_name,
     67:         [
     68:             'sync_status'   => 'failed',
     69:             'retry_count'   => $lead->retry_count + 1,
     70:             'error_message' => $result->get_error_message(), // 🚨 GHI ĐÈ LỖI KỸ THUẬT VÀO LỜI NHẮN!
     71:         ],
     72:         [ 'id' => $lead->id ]
     73:     );
     ```

### 4.2. Khám phá bổ sung của Challenger: Lỗ hổng xóa trắng hồ sơ bằng cấp Tầng 2B (`inc/eligibility.php`)
Kiểm tra mở rộng của Challenger phát hiện lỗi này **nguy hiểm hơn gấp nhiều lần** so với những gì được mô tả trong báo cáo kiểm định:
- Tại `inc/eligibility.php:773`: Khi học viên hoàn thành Tầng 2A của bài kiểm tra điều kiện, hệ thống lưu link bằng cấp:
  `'error_message' => ! empty( $input['degree_link'] ) ? 'Ảnh bằng cấp đính kèm: ' . $input['degree_link'] : ''`.
- Tại `inc/eligibility.php:945-968`: Khi học viên bổ sung thông tin trường cũ, năm sinh và ảnh bằng cấp ở Tầng 2B:
  ```php
  945: $current_msg = $lead->error_message;
  948: $notes[] = "Trường cũ: " . $previous_school;
  951: $notes[] = "Năm sinh: " . $graduation;
  954: $notes[] = "Ảnh bằng cấp: " . $degree_link;
  958: $current_msg = trim( ($current_msg ? $current_msg . ' | ' : '') . implode(' | ', $notes) );
  965: 'error_message' => $current_msg,
  ```
- **Hệ quả thực tế**: Toàn bộ dữ liệu xác minh học thuật quý giá gồm: **Link ảnh bằng cấp/bảng điểm, Tên trường Cao đẳng đã tốt nghiệp, Năm tốt nghiệp** được gom vào `error_message`. Chỉ 5 phút sau, khi WP-Cron chạy sync CRM, hàm `ltdh_process_lead_queue()` **XÓA SẠCH VĨNH VIỄN 100% LINK ẢNH BẰNG CẤP VÀ HỒ SƠ CỦA HỌC VIÊN!**

### 4.3. Kết quả chạy Harness Mô phỏng PHP
Thực hiện chạy kịch bản kiểm thử cô lập trên PHP CLI:

```
=== STEP 1: LEAD SUBMISSION ===
User input message: Em đã tốt nghiệp CĐ FPT ngành CNTT, muốn học liên thông lấy bằng cử nhân tối T7-CN.
Lead inserted in DB. error_message column contains: Em đã tốt nghiệp CĐ FPT ngành CNTT, muốn học liên thông lấy bằng cử nhân tối T7-CN.

=== STEP 2: CRM QUEUE PROCESSING (SYNC SUCCESS CASE) ===
After successful CRM sync:
sync_status: synced
error_message: ''
Original message preserved? NO! DESTROYED (Data Loss Bug CONFIRMED!)

=== STEP 3: CRM QUEUE PROCESSING (SYNC FAILURE CASE) ===
After failed CRM sync:
sync_status: failed
error_message: 'HTTP 400 Bad Request: Invalid school code'
Original message preserved? NO! OVERWRITTEN BY CRM ERROR!
```

👉 **KẾT LUẬN**: Lỗ hổng C-01 hoàn toàn có thật, mức độ nghiêm trọng **CRITICAL**, gây thiệt hại trực tiếp đến hoạt động kinh doanh và tư vấn tuyển sinh.

---

## 5. KIỂM ĐỊNH THỰC NGHIỆM: TELEGRAM BOT MÙ THÔNG TIN DỮ LIỆU LEAD

### 5.1. Rà soát logic nhánh thông báo trong `inc/lead-capture.php:188-255`
Trong hàm `ltdh_trigger_telegram_notification()`:
- Dòng 188: `$is_eligibility = ( isset( $data['referral_source'] ) && strpos( $data['referral_source'], 'eligibility_checker' ) !== false );`
- Nếu là form Eligibility Check (`$is_eligibility == true`): Dòng 229-236 xuất đầy đủ Trường, Ngành, Hệ học, Cơ sở.
- Nếu là Form Tư Vấn Miễn Phí Thông Thường (`else` - Dòng 243-255) (bao gồm form ở `single-school.php`, form ở `single-program.php`, form popup và form trang chủ):
  ```php
  245: 		$msg_text  = "🔔 <b>YÊU CẦU TƯ VẤN MIỄN PHÍ MỚI</b> 🔔\n\n";
  246: 		$msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
  247: 		$msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
  248: 		$msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
  249: 		if ( ! empty( $message ) ) {
  250: 			$msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
  251: 		}
  ```
  👉 **HOÀN TOÀN BỎ RƠI** `$school_title`, `$major_title`, `$training_type`, `$campus`, và `$data['referral_source']`!

### 5.2. Kết quả kiểm thử thực nghiệm
Chạy harness giả lập nộp form tư vấn tại trang chi tiết trường ĐH Giao thông Vận tải:
```
=== TELEGRAM NOTIFICATION OUTPUT ===
🔔 <b>YÊU CẦU TƯ VẤN MIỄN PHÍ MỚI</b> 🔔

👤 <b>Họ và tên:</b> Le Thi C
📞 <b>Số điện thoại:</b> 0901234567
✉ <b>Email:</b> c@example.com
💬 <b>Nội dung yêu cầu:</b> Tư vấn giúp em thủ tục xét tuyển đợt 1 năm 2026.
====================================
Contains School? NO (BLIND TELEGRAM BOT BUG CONFIRMED!)
Contains Major? NO
Contains Training Type? NO
```
👉 **KẾT LUẬN**: Khẳng định của `worker_audit_3` tại Mục 5.2 là **hoàn toàn chính xác 100%**.

---

## 6. PHẢN BIỆN ADVERSARIAL: CÁC ĐIỂM NGHẼN TRONG GIẢI PHÁP ĐỀ XUẤT CỦA BÁO CÁO

### 6.1. Rủi ro trong Script SQL Migration (`SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:1061-1077`)
Báo cáo đề xuất script sau:
```sql
ALTER TABLE wp_ltdh_leads 
ADD COLUMN message TEXT NULL AFTER referral_source,
ADD COLUMN school_code VARCHAR(50) DEFAULT '' AFTER school_id,
ADD COLUMN major_code VARCHAR(50) DEFAULT '' AFTER major_id,
ADD COLUMN training_type_slug VARCHAR(100) DEFAULT '' AFTER training_type,
ADD COLUMN campus_slug VARCHAR(100) DEFAULT '' AFTER campus,
ADD COLUMN crm_provider VARCHAR(50) DEFAULT 'default' AFTER sync_status,
ADD COLUMN external_lead_id VARCHAR(100) DEFAULT '' AFTER crm_provider,
ADD COLUMN utm_source VARCHAR(100) DEFAULT '' AFTER external_lead_id,
ADD COLUMN utm_medium VARCHAR(100) DEFAULT '' AFTER utm_source,
ADD COLUMN utm_campaign VARCHAR(100) DEFAULT '' AFTER utm_medium;

ALTER TABLE wp_ltdh_leads ADD INDEX idx_school_created (school_id, created_at);
ALTER TABLE wp_ltdh_leads ADD INDEX idx_program (program_id);
ALTER TABLE wp_ltdh_leads ADD INDEX idx_sync_queue (sync_status, retry_count);
```

#### Phân tích phản biện các khiếm khuyết nguy hiểm:
1. **Thiếu lệnh Backfill dữ liệu (Fatal Historic Data Loss)**:
   - Khi chạy lệnh `ALTER TABLE` trên, cột mới `message` sẽ có giá trị `NULL` cho toàn bộ các bản ghi hiện có trong bảng.
   - Các lead chưa sync (`pending`) hoặc sync lỗi (`failed`) đang lưu lời nhắn hoặc thông tin bằng cấp trong cột `error_message`.
   - Nếu không có câu lệnh:
     ```sql
     UPDATE {$wpdb->prefix}ltdh_leads 
     SET message = error_message 
     WHERE (message IS NULL OR message = '') 
       AND error_message != '' 
       AND sync_status IN ('pending', 'failed', 'processing');
     ```
     thì ngay khi lập trình viên cập nhật code đọc từ cột `message`, toàn bộ dữ liệu ghi chú của các khách hàng đang chờ tư vấn sẽ biến thành chuỗi rỗng!
2. **Hardcode tên bảng `wp_ltdh_leads`**:
   - Nếu môi trường production sử dụng table prefix tùy chỉnh (như `ltdh_`, `wpx_`), việc hardcode `wp_ltdh_leads` sẽ khiến script ném lỗi `Table doesn't exist`.
3. **Thiếu tính lũy đẳng (Non-idempotent)**:
   - Nếu chạy script lần 2 hoặc đã có một cột tồn tại từ trước (ví dụ do chạy dở script của `ELIGIBILITY_BUSINESS_AUDIT.md`), MySQL sẽ báo lỗi `ERROR 1060 (42S21): Duplicate column name` và dừng toàn bộ quá trình migration.
4. **Xung đột vị trí `AFTER` với `ELIGIBILITY_BUSINESS_AUDIT.md`**:
   - Báo cáo kiểm định điều kiện xét tuyển (`ELIGIBILITY_BUSINESS_AUDIT.md:1156`) đề xuất `ADD COLUMN education_level varchar(50) DEFAULT '' AFTER campus`.
   - Báo cáo này lại đề xuất `ADD COLUMN campus_slug VARCHAR(100) DEFAULT '' AFTER campus`.
   - Nếu không có một runner thống nhất kiểm tra sự tồn tại của cột, việc thực thi cả 2 script sẽ gây xung đột cấu trúc.

---

### 6.2. Lỗi Timing & Hook Race Condition trong `LTDH_Entity_Relationship_Engine` (Section 2.4)
Trong Section 2.4 (dòng 300), báo cáo đề xuất:
```php
add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 20, 2 );
```
Và trong hàm `on_program_save`:
```php
$school_id = (int) get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true );
$major_id  = (int) get_post_meta( $post_id, LTDH_META_MAJOR_REL, true );
```

#### Phân tích phản biện cơ chế hook của WordPress:
- Khi người dùng bấm "Cập nhật" một `program` trong WordPress Admin:
  1. WordPress Core kích hoạt hook `save_post` (và `save_post_program`).
  2. Plugin ACF (Advanced Custom Fields) lắng nghe hook `acf/save_post` (thường chạy sau hoặc hook vào `save_post` với priority 10).
  3. Chỉ **SAU KHI** ACF xử lý xong thì các giá trị từ form (`school_relationship`, `major_relationship`) mới thực sự được ghi vào bảng `wp_postmeta`.
- **Hậu quả**: Nếu `LTDH_Entity_Relationship_Engine` hook vào `save_post_program` với priority 20, nhưng ACF chạy lưu ở `acf/save_post` priority 10/20, hàm `get_post_meta()` sẽ **lấy ra dữ liệu cũ (stale data) trước khi sửa**, hoặc trả về giá trị rỗng khi tạo mới bài viết!
- **Khắc phục**: Bắt buộc phải duy trì hook vào `acf/save_post` với priority cao (ví dụ priority 25) đối với các thao tác lưu từ Admin, và dùng `save_post` / `wp_trash_post` cho các thao tác ngoài giao diện admin hoặc REST API.

---

### 6.3. Lỗi Mã hóa URL Bot Token Telegram & Mất giám sát Admin Trung tâm (Section 5.6)
Trong Section 5.6, mã nguồn `ltdh_trigger_telegram_notification_v2` có 2 lỗi:
1. **Lỗi `rawurlencode( $bot_token )` (dòng 1157)**:
   ```php
   $api_url = 'https://api.telegram.org/bot' . rawurlencode( $bot_token ) . '/sendMessage';
   ```
   - Bot Token của Telegram có định dạng chuẩn: `123456789:ABCdefgh-ijklmn_opqrst`. Dấu `:` là ký tự phân tách bắt buộc.
   - Chạy kiểm nghiệm trong PHP:
     `rawurlencode("123456789:ABC...")` cho ra `123456789%3AABC...`.
     Hệ thống định tuyến của Telegram Bot API trên máy chủ `api.telegram.org` coi `%3A` là một ký tự khác, không nhận diện được bot ID và trả về lỗi **HTTP 404 Not Found**!
   - Đoạn mã gốc trong `inc/lead-capture.php:267` dùng `urlencode()` cũng mắc lỗi tương tự, nhưng trong `inc/eligibility.php:798` thì dùng chuỗi trực tiếp:
     `$api_url = 'https://api.telegram.org/bot' . $bot_token . '/sendMessage';`
2. **Cắt đứt luồng thông báo về Admin Tổng (Mute Central Admin)**:
   ```php
   1115: if ( $school_id && function_exists( 'get_field' ) ) {
   1116:     $chat_id = get_field( 'school_telegram_chat_id', $school_id );
   1117: }
   1118: if ( empty( $chat_id ) ) {
   1119:     $chat_id = defined( 'LTDH_TELEGRAM_CHAT_ID' ) ... : get_field( 'telegram_chat_id', 'options' );
   1120: }
   ```
   - Nếu một trường đối tác (ví dụ ĐH Thái Nguyên) được cấu hình `school_telegram_chat_id`: biến `$chat_id` bị gán bằng ID của trường. Khối lệnh `if (empty($chat_id))` bị bỏ qua hoàn toàn.
   - Nhóm Telegram quản trị trung tâm của website `lienthongdaihoc.com` **hoàn toàn không nhận được lead**, làm mất khả năng đối soát, giám sát chất lượng và chống thất thoát hoa hồng tuyển sinh!
   - **Khắc phục**: Cơ chế phân luồng phải là **Multi-cast (Phát đồng thời)**: Luôn gửi một bản sao về Admin Tổng, và nếu trường có nhóm riêng thì gửi thêm vào nhóm của trường đó.

---

## 7. BỘ MÃ NGUỒN HIỆU CHỈNH CHUẨN XÁC DÀNH CHO TRIỂN KHAI

### 7.1. Script Migration Nâng cấp Database Hoàn chỉnh (Idempotent & Safe Backfill)
Lập trình viên khi triển khai Phase 2 cần sử dụng script PHP Migration có kiểm tra cấu trúc sau thay vì chạy raw SQL:

```php
function ltdh_migrate_leads_table_v2(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . LTDH_TABLE_LEADS;
    
    // 1. Kiểm tra danh sách cột hiện có
    $existing_cols = $wpdb->get_col( "DESC {$table_name}", 0 );
    
    $cols_to_add = [
        'message'            => "ALTER TABLE {$table_name} ADD COLUMN message TEXT NULL AFTER referral_source",
        'school_code'        => "ALTER TABLE {$table_name} ADD COLUMN school_code VARCHAR(50) DEFAULT '' AFTER school_id",
        'major_code'         => "ALTER TABLE {$table_name} ADD COLUMN major_code VARCHAR(50) DEFAULT '' AFTER major_id",
        'training_type_slug' => "ALTER TABLE {$table_name} ADD COLUMN training_type_slug VARCHAR(100) DEFAULT '' AFTER training_type",
        'campus_slug'        => "ALTER TABLE {$table_name} ADD COLUMN campus_slug VARCHAR(100) DEFAULT '' AFTER campus",
        'crm_provider'       => "ALTER TABLE {$table_name} ADD COLUMN crm_provider VARCHAR(50) DEFAULT 'default' AFTER sync_status",
        'external_lead_id'   => "ALTER TABLE {$table_name} ADD COLUMN external_lead_id VARCHAR(100) DEFAULT '' AFTER crm_provider",
        'utm_source'         => "ALTER TABLE {$table_name} ADD COLUMN utm_source VARCHAR(100) DEFAULT '' AFTER external_lead_id",
        'utm_medium'         => "ALTER TABLE {$table_name} ADD COLUMN utm_medium VARCHAR(100) DEFAULT '' AFTER utm_source",
        'utm_campaign'       => "ALTER TABLE {$table_name} ADD COLUMN utm_campaign VARCHAR(100) DEFAULT '' AFTER utm_medium",
    ];

    foreach ( $cols_to_add as $col => $sql ) {
        if ( ! in_array( $col, $existing_cols, true ) ) {
            $wpdb->query( $sql );
        }
    }

    // 2. BACKFILL DỮ LIỆU LỊCH SỬ ĐỂ KHÔNG BỊ MẤT TIN NHẮN CỦA KHÁCH HÀNG CŨ
    $wpdb->query( "
        UPDATE {$table_name} 
        SET message = error_message 
        WHERE (message IS NULL OR message = '') 
          AND error_message != '' 
          AND sync_status IN ('pending', 'failed', 'processing')
    " );

    // 3. Đánh chỉ mục Index nếu chưa có
    $indices = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
    $index_names = array_unique( wp_list_pluck( $indices, 'Key_name' ) );

    if ( ! in_array( 'idx_school_created', $index_names, true ) ) {
        $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_school_created (school_id, created_at)" );
    }
    if ( ! in_array( 'idx_program', $index_names, true ) ) {
        $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_program (program_id)" );
    }
    if ( ! in_array( 'idx_sync_queue', $index_names, true ) ) {
        $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_sync_queue (sync_status, retry_count)" );
    }
}
```

---

### 7.2. Lớp `LTDH_Entity_Relationship_Engine` Hiệu chỉnh Timing Hook
Sửa đổi các hook lắng nghe để loại bỏ hoàn toàn bẫy timing của ACF:

```php
class LTDH_Entity_Relationship_Engine {

    public static function init(): void {
        // Lắng nghe acf/save_post với priority 25 để đảm bảo ACF đã ghi xong postmeta
        add_action( 'acf/save_post', [ __CLASS__, 'on_acf_program_save' ], 25 );

        // Lắng nghe khi Program được phục hồi từ Thùng rác
        add_action( 'untrash_post', [ __CLASS__, 'on_program_lifecycle_change' ] );

        // Lắng nghe trước khi Program bị chuyển vào thùng rác hoặc xóa vĩnh viễn
        add_action( 'wp_trash_post', [ __CLASS__, 'on_program_lifecycle_change' ] );
        add_action( 'before_delete_post', [ __CLASS__, 'on_program_delete' ] );
        add_action( 'before_delete_post', [ __CLASS__, 'on_parent_entity_delete' ] );
    }

    public static function on_acf_program_save( $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $school_id = (int) get_field( LTDH_META_SCHOOL_REL, $post_id );
        $major_id  = (int) get_field( LTDH_META_MAJOR_REL, $post_id );

        $old_school_id = (int) get_post_meta( $post_id, '_last_known_school_id', true );
        $old_major_id  = (int) get_post_meta( $post_id, '_last_known_major_id', true );

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

        update_post_meta( $post_id, '_last_known_school_id', $school_id );
        update_post_meta( $post_id, '_last_known_major_id', $major_id );
    }
    // ... (Giữ nguyên các hàm rollup và rebuild cache)
}
```

---

### 7.3. Hàm Telegram Notification Hiệu chỉnh URL Token & Multi-Cast Admin
Sửa đổi hàm `ltdh_trigger_telegram_notification_v2` để:
1. Giữ nguyên `$bot_token` không qua `rawurlencode` (tránh biến `:` thành `%3A`).
2. Gửi đồng thời tới cả Admin Tổng VÀ Nhóm Telegram riêng của Trường đối tác (Multi-cast).

```php
function ltdh_trigger_telegram_notification_v2( array $data ): void {
    $school_id   = (int) ( $data['school_id'] ?? 0 );
    $program_id  = (int) ( $data['program_id'] ?? 0 );
    $major_id    = (int) ( $data['major_id'] ?? 0 );

    $bot_token = defined( 'LTDH_TELEGRAM_BOT_TOKEN' ) && LTDH_TELEGRAM_BOT_TOKEN ? LTDH_TELEGRAM_BOT_TOKEN : get_field( 'telegram_bot_token', 'options' );
    if ( empty( $bot_token ) ) {
        return;
    }

    // 1. Thu thập danh sách Chat ID cần gửi (Đồng thời cả Admin Tổng và Nhóm Trường)
    $chat_targets = [];
    $global_chat  = defined( 'LTDH_TELEGRAM_CHAT_ID' ) && LTDH_TELEGRAM_CHAT_ID ? LTDH_TELEGRAM_CHAT_ID : get_field( 'telegram_chat_id', 'options' );
    if ( ! empty( $global_chat ) ) {
        $chat_targets[] = $global_chat;
    }

    if ( $school_id && function_exists( 'get_field' ) ) {
        $school_chat = get_field( 'school_telegram_chat_id', $school_id );
        if ( ! empty( $school_chat ) ) {
            $chat_targets[] = $school_chat;
        }
    }

    // Tách các chat ID theo dấu phẩy hoặc khoảng trắng và loại bỏ trùng lặp
    $final_chat_ids = [];
    foreach ( $chat_targets as $raw_target ) {
        $split = preg_split( '/[\s,;]+/', $raw_target );
        foreach ( $split as $cid ) {
            $cid = trim( $cid );
            if ( ! empty( $cid ) ) {
                $final_chat_ids[] = $cid;
            }
        }
    }
    $final_chat_ids = array_unique( $final_chat_ids );

    if ( empty( $final_chat_ids ) ) {
        return;
    }

    // 2. Thu thập đầy đủ ngữ cảnh bài viết
    $school_title  = $school_id ? get_the_title( $school_id ) : 'Chưa chọn trường (Đăng ký chung)';
    $major_title   = $major_id ? get_the_title( $major_id ) : 'Chưa chọn ngành';
    $program_title = $program_id ? get_the_title( $program_id ) : '';
    $training_type = ! empty( $data['training_type'] ) ? $data['training_type'] : 'Tư vấn theo hồ sơ';
    $campus        = ! empty( $data['campus'] ) ? $data['campus'] : 'Toàn quốc / Trực tuyến';

    $is_eligibility = ( isset( $data['referral_source'] ) && strpos( $data['referral_source'], 'eligibility_checker' ) !== false );
    $header_title   = $is_eligibility ? '🎯 ĐÁNH GIÁ ĐIỀU KIỆN XÉT TUYỂN MỚI' : '🔔 ĐĂNG KÝ TƯ VẤN TUYỂN SINH MỚI';

    $msg  = "<b>{$header_title}</b>\n\n";
    $msg .= "👤 <b>Họ và tên:</b> " . esc_html( $data['name'] ?? 'N/A' ) . "\n";
    $msg .= "📞 <b>Số điện thoại:</b> <code>" . esc_html( $data['phone'] ?? 'N/A' ) . "</code>\n";
    if ( ! empty( $data['email'] ) ) {
        $msg .= "✉️ <b>Email:</b> " . esc_html( $data['email'] ) . "\n";
    }
    $msg .= "🏫 <b>Trường đăng ký:</b> " . esc_html( $school_title ) . "\n";
    $msg .= "🎓 <b>Ngành quan tâm:</b> " . esc_html( $major_title ) . "\n";
    $msg .= "🏷️ <b>Hệ đào tạo:</b> " . esc_html( $training_type ) . "\n";
    $msg .= "📍 <b>Cơ sở / Trạm:</b> " . esc_html( $campus ) . "\n";
    if ( ! empty( $program_title ) ) {
        $msg .= "📌 <b>Chương trình cụ thể:</b> " . esc_html( $program_title ) . "\n";
    }
    if ( ! empty( $data['message'] ) ) {
        $msg .= "💬 <b>Ghi chú nguyện vọng:</b> <i>" . esc_html( $data['message'] ) . "</i>\n";
    }
    if ( ! empty( $data['degree_link'] ) ) {
        $msg .= "📎 <b>Ảnh bằng cấp:</b> " . esc_url( $data['degree_link'] ) . "\n";
    }
    $msg .= "🔗 <b>Nguồn đăng ký:</b> " . esc_url( $data['referral_source'] ?? '' ) . "\n";
    $msg .= "⏱️ <b>Thời gian:</b> " . current_time( 'd/m/Y H:i:s' ) . "\n";

    // Chuẩn hóa Endpoint Telegram API: Không dùng rawurlencode lên bot token!
    $clean_token = trim( $bot_token );
    $api_url     = "https://api.telegram.org/bot{$clean_token}/sendMessage";

    foreach ( $final_chat_ids as $cid ) {
        wp_remote_post( $api_url, [
            'body'     => [
                'chat_id'    => $cid,
                'text'       => $msg,
                'parse_mode' => 'HTML',
            ],
            'timeout'  => 5,
            'blocking' => false,
        ] );
    }
}
```

---

## 8. BẢNG TỔNG KẾT SO SÁNH GIỮA BÁO CÁO GỐC & KẾT QUẢ PHẢN BIỆN

| Trọng tâm kiểm định | Nhận định của `worker_audit_3` | Kết quả xác minh của `challenger_audit_3_2` | Trạng thái xác nhận & Hướng dẫn kỹ thuật |
|---|---|---|---|
| **Căn cứ Thông tư 27/2019** | Bãi bỏ ghi hệ trên bằng; Bắt buộc ghi hệ trên Phụ lục văn bằng. | **XÁC MINH 100%**: Điều 2 bãi bỏ hệ trên bằng chính; Điều 3 bắt buộc ghi hệ trên Phụ lục. | ✅ **ĐÃ XÁC MINH ĐÚNG** |
| **Badge cam tại `front-page.php:528`** | Khẳng định sai sự thật "100% BẰNG CỬ NHÂN CHÍNH QUY". | **XÁC MINH 100%**: Dòng 528-530 nguyên văn ghi chữ này. Vi phạm Điều 8 Luật Quảng cáo. | ✅ **ĐÃ XÁC MINH ĐÚNG** |
| **"Bằng đỏ" tại `front-page.php:432`** | Dùng từ dân gian gây hiểu lầm. | **XÁC MINH 100%**: Dòng 432 có thẻ `<h4>Bằng đỏ</h4>`. | ✅ **ĐÃ XÁC MINH ĐÚNG** |
| **CRM Data Loss Bug** | Cập nhật `error_message = ''` xóa trắng lời nhắn thí sinh. | **XÁC MINH 100%** & **PHÁT HIỆN THÊM**: Xóa cả ảnh bằng cấp Tầng 2B của `eligibility.php:965`. | ✅ **ĐÃ XÁC MINH ĐÚNG (Mức độ nặng hơn)** |
| **Blind Telegram Bot** | Bỏ rơi trường, ngành, hệ ở form tư vấn chung. | **XÁC MINH 100%**: Nhánh `else` dòng 243-255 không hề định dạng các trường này. | ✅ **ĐÃ XÁC MINH ĐÚNG** |
| **Script SQL Migration** | Đề xuất `ALTER TABLE wp_ltdh_leads ADD COLUMN message TEXT...` | ❌ **CHALLENGE THÀNH CÔNG**: Thiếu lệnh backfill dữ liệu lịch sử; hardcode prefix `wp_`; thiếu idempotency. | 🛑 **YÊU CẦU ĐIỀU CHỈNH** (Dùng script tại Mục 7.1) |
| **`LTDH_Entity_Relationship_Engine`** | Đề xuất thay `acf/save_post` bằng `save_post_program`. | ❌ **CHALLENGE THÀNH CÔNG**: Bẫy timing! `save_post` chạy trước khi ACF lưu meta, đọc ra stale data. | 🛑 **YÊU CẦU ĐIỀU CHỈNH** (Dùng hook tại Mục 7.2) |
| **Multi-Tenant Lead Router** | Đề xuất `rawurlencode($bot_token)` & ghi đè Chat ID. | ❌ **CHALLENGE THÀNH CÔNG**: `%3A` làm chết API Telegram; ghi đè làm mất cảnh báo của Admin Tổng. | 🛑 **YÊU CẦU ĐIỀU CHỈNH** (Dùng hàm tại Mục 7.3) |
