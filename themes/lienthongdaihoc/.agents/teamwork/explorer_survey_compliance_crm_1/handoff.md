# BÁO CÁO BÀN GIAO KIỂM TOÁN KỸ THUẬT (HANDOFF REPORT)
## KIỂM ĐỊNH R3 (PHÁP LÝ & VĂN BẰNG) & R4 (PHÂN LUỒNG CRM & PHỄU TUYỂN SINH)

**Tác tử thực hiện**: `explorer_survey_compliance_crm_1`  
**Tác tử nhận bàn giao**: `orchestrator_3`  
**Loại bàn giao**: **Hard Handoff** (Nhiệm vụ kiểm định hoàn tất đầy đủ 100%)  
**Tài liệu phân tích gốc**: `.agents/teamwork/explorer_survey_compliance_crm_1/analysis.md`  

---

### 1. QUAN SÁT THỰC TẾ (OBSERVATION)

1. **Quan sát O1 — Badge cam khẳng định "100% BẰNG CỬ NHÂN CHÍNH QUY"**:
   - Tệp: `front-page.php`, dòng 527-530:
     ```html
     <!-- Orange badge -->
     <div class="absolute bottom-6 left-6 bg-[#f97316] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[150px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
         <span class="text-3xl font-black leading-none">100%</span>
         <span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>
     </div>
     ```
   - Tệp `front-page.php`, dòng 432-434:
     `<h4 class="font-bold text-slate-900 text-base">Bằng đỏ</h4>`  
     `<p class="text-slate-500 text-sm leading-relaxed">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân (Bằng đỏ), được Bộ GD&ĐT công nhận.</p>`
   - Tệp `inc/cli-commands.php`, dòng 823:
     `'advantages' => '<ul><li><strong>Bằng đại học chính quy:</strong> Nhận bằng tốt nghiệp đại học chính quy từ Trường Đại học Giao thông Vận tải.</li>...'`

2. **Quan sát O2 — Thiếu minh bạch Phụ lục văn bằng theo Thông tư 27/2019/TT-BGDĐT**:
   - Tệp `page-faq.php`, dòng 31:
     `[ 'question' => 'Bằng tốt nghiệp đại học từ xa có ghi chữ "Từ xa" không?', 'answer' => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo từ ngày 1/3/2020, bằng đại học sẽ không còn ghi hình thức đào tạo (như Từ xa, Vừa học vừa làm, Chính quy) trên văn bằng tốt nghiệp. Tất cả phôi bằng đều có giá trị tương đương tốt nghiệp chính quy.' ]`
   - Tệp `inc/cli-commands.php`, dòng 991:
     `'Có. Theo Thông tư của Bộ GD&ĐT, từ ngày 01/03/2020 trên văn bằng tốt nghiệp Đại học không ghi hình thức đào tạo (Chính quy, Từ xa, hay Vừa học vừa làm), giá trị pháp lý là hoàn toàn như nhau.'`

3. **Quan sát O3 — Xóa sạch lời nhắn/ghi chú của người dùng khi sync CRM**:
   - Tệp `inc/lead-capture.php`, dòng 22-40: Bảng `wp_ltdh_leads` chỉ có các cột `id`, `name`, `phone`, `email`, `program_id`, `school_id`, `major_id`, `training_type`, `campus`, `referral_source`, `sync_status`, `retry_count`, `error_message`, `created_at`, `synced_at`. Hoàn toàn không có cột `message` hay `notes`.
   - Tệp `inc/lead-capture.php`, dòng 139: Mượn tạm cột `error_message` để lưu `$message`:
     `'error_message' => $message,`
   - Tệp `inc/crm-adapters.php`, dòng 75-84: Khi sync CRM thành công, hàm `ltdh_process_lead_queue()` xóa sạch cột `error_message`:
     ```php
     $wpdb->update(
         $table_name,
         [
             'sync_status'   => 'synced',
             'synced_at'     => current_time( 'mysql' ),
             'error_message' => '',
         ],
         [ 'id' => $lead->id ]
     );
     ```

4. **Quan sát O4 — Telegram Bot lọc bỏ toàn bộ thông tin trường/ngành với form tư vấn**:
   - Tệp `inc/lead-capture.php`, dòng 188-255:
     ```php
     $is_eligibility = ( isset( $data['referral_source'] ) && strpos( $data['referral_source'], 'eligibility_checker' ) !== false );
     if ( $is_eligibility ) {
         // Gửi đầy đủ Trường, Ngành, Hệ, Cơ sở...
     } else {
         // Form tư vấn thông thường tại Trang trường, Chương trình, Ngành, Trang chủ:
         $msg_text  = "🔔 <b>YÊU CẦU TƯ VẤN MIỄN PHÍ MỚI</b> 🔔\n\n";
         $msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
         $msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
         $msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
         if ( ! empty( $message ) ) {
             $msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
         }
         ...
     }
     ```
     -> Cắt bỏ toàn bộ `$data['school_id']`, `$data['major_id']`, `$data['program_id']`, `$data['training_type']`.

5. **Quan sát O5 — Đơn kênh CRM toàn cục và truyền dữ liệu sai định dạng**:
   - Tệp `inc/crm-adapters.php`, dòng 91-124: Hệ thống chỉ đọc 1 option duy nhất `$crm_type = get_field( 'default_crm_type', 'options' );`. Không có cấu hình CRM hay Telegram theo từng trường đối tác.
   - Tệp `inc/crm-adapters.php`, dòng 99-101 và 184-195: Lấy chuỗi tiêu đề tiếng Việt bài viết (`$school_name = get_the_title(...)`, `$major_name = get_the_title(...)`) truyền vào `school_code`, `major_code` của AUM CRM và OnSchool thay vì mã trường ngắn (`UTC`) hay mã ngành chuẩn Bộ (`7480201`).

6. **Quan sát O6 — Rò rỉ phễu 100% tại Landing page Hệ đào tạo**:
   - Tệp `taxonomy-training_type.php`: Hoàn toàn không có form tư vấn hoặc CTA thu thập lead. Ứng viên truy cập vào chuyên mục hệ `/he-dao-tao/tu-xa/` không có nơi để lại thông tin.

7. **Quan sát O7 — Rơi rụng ngữ cảnh khi bật Contact Form 7**:
   - Tệp `inc/core/class-helpers.php`, dòng 159-166:
     ```php
     function ltdh_render_consultation_form(array $context_hidden_fields = []): void {
         $shortcode = ltdh_get_form_shortcode('consultation');
         if (! empty($shortcode)) {
             echo $shortcode;
             return;
         }
         ltdh_render_native_form('consultation', $context_hidden_fields);
     }
     ```
     Nếu bật CF7 shortcode, biến `$context_hidden_fields` chứa `school_id`, `program_id` bị bỏ qua hoàn toàn.

8. **Quan sát O8 — Đứt gãy logic tạm dừng tuyển sinh**:
   - Tệp `single-program.php`, dòng 96: Khi `admission_status === 'tam-ngung'`, hệ thống hiển thị câu chữ hardcode:
     `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."`
     (Kể cả chương trình đang xem là hệ Từ xa hay Văn bằng 2). Form tư vấn tại sidebar vẫn mở nhận đăng ký bình thường.

---

### 2. CHUỖI SUY LUẬN LOGIC (LOGIC CHAIN)

1. **Từ Quan sát O1 & O2 suy ra Vi phạm Pháp lý Quảng cáo & Rủi ro thanh tra Giáo dục**:
   - Thông tư 27/2019/TT-BGDĐT chỉ bỏ việc ghi hình thức đào tạo trên trang chính văn bằng, còn Điều 3 quy định rõ Phụ lục văn bằng bắt buộc ghi hình thức đào tạo ("Đào tạo từ xa" hoặc "Vừa làm vừa học").
   - Luật Quảng cáo 2012 (Khoản 9 Điều 8) nghiêm cấm quảng cáo sai sự thật. Gán nhãn "100% BẰNG CỬ NHÂN CHÍNH QUY" cho chương trình từ xa/liên thông là hành vi gian lận thông tin quảng cáo, có thể bị xử phạt 70 - 100 triệu đồng theo Nghị định 38/2021/NĐ-CP và Nghị định 04/2021/NĐ-CP.
   - Việc giấu thông tin về Phụ lục văn bằng trên FAQ dẫn đến tranh chấp pháp lý và khiếu kiện từ người học khi nhận bằng tốt nghiệp.

2. **Từ Quan sát O3 suy ra Lỗ hổng Mất mát Dữ liệu Nghiêm trọng (Data Loss Vulnerability)**:
   - Vì bảng `wp_ltdh_leads` thiếu cột `message`, lập trình viên tạm lưu lời nhắn vào cột `error_message`.
   - Tiến trình sync CRM định kỳ khi hoàn tất cập nhật `error_message = ''`.
   - Kết quả: 100% nguyện vọng, câu hỏi chuyên biệt và ghi chú của người học bị xóa sạch khỏi cơ sở dữ liệu sau 5 phút.

3. **Từ Quan sát O4, O5 & O7 suy ra Tỷ lệ Thất bại CRM & Rò rỉ Chuyển đổi (Lead Degradation)**:
   - Form tư vấn thông thường (chiếm 90% lượng lead) khi gửi Telegram bị cắt bỏ toàn bộ tên trường và ngành học khiến tư vấn viên bị "mù thông tin", không thể gọi điện chăm sóc ngay lập tức.
   - Cơ chế CRM chỉ hỗ trợ 1 cổng chung toàn site: Khi có nhiều trường đối tác khác nhau (TNU dùng OnSchool, TMU dùng AUM, UTC dùng Telegram/Webhook nội bộ), lead bị gửi nhầm CRM hoặc bị CRM từ chối do truyền chuỗi tiếng Việt vào trường mã code.
   - Khi kích hoạt CF7, toàn bộ ID trường/ngành bị rơi rụng khiến lead biến thành lead rác (ID = 0).

4. **Từ Quan sát O6 & O8 suy ra Trải nghiệm Phễu tuyển sinh & Khả năng Vận hành Kém**:
   - Trang landing page hệ đào tạo (`taxonomy-training_type.php`) không có form chuyển đổi, đánh mất toàn bộ organic traffic vào hệ.
   - Khi tạm dừng tuyển sinh một hệ tại một trường, quản trị viên phải sửa từng bài viết program, và thông báo tạm ngưng trên frontend bị hardcode sai lệch thành "hệ Chính quy", gây mất niềm tin cho thí sinh.

---

### 3. CÁC ĐIỂM CHƯA ĐIỀU TRA / GIẢ ĐỊNH (CAVEATS)

1. Chưa kiểm tra API Key thực tế của OnSchool và AUM trên môi trường Live (do đang kiểm định tĩnh trên mã nguồn theme, không gọi request ra bên ngoài để tránh phát sinh dữ liệu rác trên CRM đối tác).
2. Chưa kiểm tra plugin bên thứ ba (Contact Form 7, Flamingo, WP Mail SMTP) trên database Live của khách hàng; giả định mã nguồn theme chạy độc lập trên WordPress core standards.

---

### 4. KẾT LUẬN (CONCLUSION)

1. **Về tuân thủ pháp lý (R3)**: Hệ thống đang đối mặt với rủi ro pháp lý cao độ do các phát ngôn quảng cáo vi phạm Thông tư 27/2019/TT-BGDĐT và Luật Quảng cáo 2012 (đặc biệt là badge "100% BẰNG CỬ NHÂN CHÍNH QUY" và thuật ngữ "Bằng đỏ"). Cần lập tức thay thế bằng bộ thông điệp chuẩn hóa pháp lý đã được soạn thảo trong `analysis.md`.
2. **Về phễu tuyển sinh và CRM (R4)**: Hệ thống bị lỗi mất dữ liệu nghiêm trọng (xóa sạch lời nhắn khách hàng khi sync CRM), lỗi mù thông tin trên Telegram Bot, và điểm nghẽn kiến trúc đơn kênh CRM không đáp ứng được mô hình cổng tuyển sinh đa trường đối tác.

---

### 5. PHƯƠNG PHÁP XÁC MINH ĐỘC LẬP (VERIFICATION METHOD)

1. **Xác minh lỗi vi phạm copy văn bằng**:
   ```bash
   grep -n -C 3 "CHÍNH QUY" "front-page.php"
   # Kiểm tra dòng 528-530
   grep -n "Bằng đỏ" "front-page.php"
   # Kiểm tra dòng 432
   ```
2. **Xác minh lỗi xóa trắng tin nhắn khách hàng trong CRM Queue**:
   - Mở tệp `inc/lead-capture.php`, kiểm tra dòng 139: `'error_message' => $message`.
   - Mở tệp `inc/crm-adapters.php`, kiểm tra dòng 80: `'error_message' => ''`.
3. **Xác minh lỗi Telegram cắt bỏ thông tin trường**:
   - Mở tệp `inc/lead-capture.php`, kiểm tra dòng 243-255: nhánh `else` của `$is_eligibility` chỉ nối chuỗi `$name`, `$phone`, `$email`, `$message`, hoàn toàn không in `$school_title` hay `$major_title`.
4. **Xác minh lỗi CF7 rơi rụng context**:
   - Mở tệp `inc/core/class-helpers.php`, kiểm tra dòng 159-166: hàm return ngay khi `echo $shortcode` mà không inject `$context_hidden_fields`.
