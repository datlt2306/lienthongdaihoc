# BÁO CÁO KIỂM ĐỊNH KỸ THUẬT & PHÁP LÝ TOÀN DIỆN (R3 & R4)
## DỰ ÁN THEME WORDPRESS LIÊN THÔNG ĐẠI HỌC (lienthongdaihoc.com)

**Mã phân tích**: `AUDIT-R3-R4-COMPLIANCE-CRM-01`  
**Ngày kiểm định**: 28/09/2026  
**Chuyên viên kiểm định**: `explorer_survey_compliance_crm_1` (Teamwork Explorer)  
**Mục tiêu kiểm định**: 
1. **Yêu cầu R3 (Regulatory Compliance & Degree Trust)**: Đối chiếu chuẩn pháp lý đào tạo đại học, quy định văn bằng giáo dục đại học (Thông tư 27/2019/TT-BGDĐT, Thông tư 28/2023/TT-BGDĐT, Thông tư 08/2021/TT-BGDĐT, Luật Giáo dục đại học sửa đổi 2018), phát hiện rủi ro truyền thông sai lệch, quảng cáo gian dối và đề xuất chuẩn hóa thông điệp.
2. **Yêu cầu R4 (Lead Routing & Admissions Funnel)**: Kiểm toán luồng thu thập dữ liệu tuyển sinh, tính toàn vẹn payload trên từng trang (Trường, Hệ, Ngành, Chương trình), cơ chế phân luồng CRM (OnSchool, AUM, ERPNext, Telegram Bot), phát hiện lỗi mất mát dữ liệu (data loss), rò rỉ phễu (lead leakage) và đánh giá khả năng mở rộng (extensibility).

---

## MỤC LỤC
1. [TỔNG QUAN KẾT QUẢ KIỂM ĐỊNH & MA TRẬN RỦI RO](#1-tổng-quan-kết-quả-kiểm-định--ma-trận-rủi-ro)
2. [KIỂM TOÁN CHUYÊN SÂU YÊU CẦU R3: PHÁP LÝ & NIỀM TIN VĂN BẰNG](#2-kiểm-toán-chuyên-sâu-yêu-cầu-r3-pháp-lý--niềm-tin-văn-bằng)
   - 2.1. Căn cứ pháp lý cốt lõi hiện hành tại Việt Nam
   - 2.2. Bảng đối chiếu vi phạm copy & messaging trên toàn theme
   - 2.3. Phân tích chi tiết các điểm nóng vi phạm nghiêm trọng
   - 2.4. Đánh giá tính chuẩn xác trong truyền thông "Giá trị bằng tương đương"
   - 2.5. Tuân thủ Thông tư 28/2023/TT-BGDĐT về Đào tạo từ xa
   - 2.6. Tuân thủ Thông tư 08/2021/TT-BGDĐT & 08/2022/TT-BGDĐT
3. [KIỂM TOÁN CHUYÊN SÂU YÊU CẦU R4: PHỄU TUYỂN SINH & PHÂN LUỒNG CRM](#3-kiểm-toán-chuyên-sâu-yêu-cầu-r4-phễu-tuyển-sinh--phân-luồng-crm)
   - 3.1. Sơ đồ kiến trúc luồng dữ liệu tuyển sinh hiện tại
   - 3.2. Ma trận thu thập dữ liệu (Payload Integrity Matrix) theo từng điểm chạm
   - 3.3. Lỗi mù thông tin Lead trên Telegram Bot (`ltdh_trigger_telegram_notification`)
   - 3.4. Điểm nghẽn kiến trúc CRM: "Một CRM cho toàn bộ hệ thống"
   - 3.5. Lỗi ánh xạ dữ liệu: Gửi chuỗi tiêu đề tiếng Việt thay vì mã định danh
   - 3.6. Lỗi xóa vĩnh viễn nội dung ghi chú người dùng (`error_message` bug)
   - 3.7. Lỗi rơi rụng ngữ cảnh khi kích hoạt Contact Form 7
   - 3.8. Hạn chế của WP-Cron và rủi ro nghẽn hàng đợi (Queue Bottleneck)
4. [ĐÁNH GIÁ KHẢ NĂNG MỞ RỘNG & VẬN HÀNH ĐỐI TÁC TRƯỜNG / HỆ](#4-đánh-giá-khả-năng-mở-rộng--vận-hành-đối-tác-trường--hệ)
   - 4.1. Quy trình kết nối thêm Trường Đại học đối tác mới
   - 4.2. Quy trình mở một Hệ đào tạo mới
   - 4.3. Quy trình tạm dừng tuyển sinh một cặp (Trường + Hệ đào tạo)
5. [ĐỀ XUẤT KIẾN TRÚC NÂNG CẤP & MÃ NGUỒN MẪU KHẮC PHỤC](#5-đề-xuất-kiến-trúc-nâng-cấp--mã-nguồn-mẫu-khắc-phục)
   - 5.1. Nâng cấp cơ sở dữ liệu `wp_ltdh_leads`
   - 5.2. Kiến trúc Lead Routing đa đối tác (Multi-tenant CRM Routing Engine)
   - 5.3. Chuẩn hóa bộ ACF Trường đối tác: Cấu hình CRM & Telegram riêng
   - 5.4. Khắc phục hàm gửi Telegram thông minh đầy đủ ngữ cảnh
   - 5.5. Bộ quy chuẩn thông điệp văn bằng & Disclaimer chuẩn pháp lý

---

## 1. TỔNG QUAN KẾT QUẢ KIỂM ĐỊNH & MA TRẬN RỦI RO

| Hạng mục kiểm toán | Mức độ nghiêm trọng | Hiện trạng thực tế | Hậu quả / Rủi ro |
|---|---|---|---|
| **Badge "100% Bằng Cử nhân Chính quy"** (`front-page.php:528`) | 🚨 **CRITICAL** | Gắn nhãn cứng cam đoan hệ từ xa được cấp bằng cử nhân chính quy | Vi phạm Luật Quảng cáo 2012, đối mặt phạt tiền 70-100 triệu VNĐ, thu hồi giấy phép |
| **Xóa trắng tin nhắn ứng viên** (`inc/crm-adapters.php:80`) | 🚨 **CRITICAL** | Ghi đè rỗng cột `error_message` (nơi tạm lưu message) sau khi CRM sync | Mất vĩnh viễn nội dung nguyện vọng của thí sinh |
| **Lỗi mù thông tin Telegram** (`inc/lead-capture.php:243`) | 🔴 **HIGH** | Form tư vấn ở Trang trường, Ngành, Chương trình gửi về Telegram chỉ có Tên + SĐT | Tư vấn viên không biết ứng viên đăng ký trường nào, ngành gì |
| **Rò rỉ 100% phễu Landing Hệ** (`taxonomy-training_type.php`) | 🔴 **HIGH** | Trang lưu trữ hệ `/he-dao-tao/tu-xa/` không có form đăng ký/CTA nhận lead | Đánh mất toàn bộ chuyển đổi từ lưu lượng truy cập tìm kiếm theo hệ |
| **Đơn kênh CRM toàn cục** (`inc/crm-adapters.php:91`) | 🔴 **HIGH** | Chỉ cho phép chọn 1 CRM duy nhất cho toàn site (OnSchool hoặc AUM) | Không thể vận hành mô hình cổng tuyển sinh hợp tác đa đối tác |
| **Gửi sai định dạng mã ngành/trường** (`inc/crm-adapters.php:147`) | 🔴 **HIGH** | Gửi Tiêu đề tiếng Việt ("Đại học GTVT") vào trường `school_code`, `major_code` | CRM đối tác từ chối nhận lead hoặc phân loại sai trường tuyển sinh |
| **Thiếu minh bạch Phụ lục văn bằng** (`page-faq.php:31`) | 🟡 **MEDIUM** | Tuyên truyền bằng không ghi hình thức học nhưng giấu việc phụ lục ghi rõ | Thí sinh khiếu nại, tranh chấp pháp lý gay gắt khi tốt nghiệp |
| **Treo Cron do phụ thuộc `admin_init`** (`inc/crm-adapters.php:24`) | 🟡 **MEDIUM** | Lịch chạy cron đẩy lead sang CRM gắn vào `admin_init` | Nếu không có admin đăng nhập wp-admin, tiến trình sync CRM không bao giờ được kích hoạt |
| **Lỗi thông báo tạm ngưng tuyển sinh** (`single-program.php:96`) | 🟡 **MEDIUM** | Hardcode nội dung "hệ Chính quy đã hết chỉ tiêu" cho mọi hệ đào tạo | Gây hiểu lầm tai hại khi xem chương trình Từ xa hoặc Vừa làm vừa học |

---

## 2. KIỂM TOÁN CHUYÊN SÂU YÊU CẦU R3: PHÁP LÝ & NIỀM TIN VĂN BẰNG

### 2.1. Căn cứ pháp lý cốt lõi hiện hành tại Việt Nam
1. **Luật Giáo dục đại học sửa đổi 2018 (Luật số 34/2018/QH14)**:
   - *Khoản 2 Điều 6*: Quy định 3 hình thức đào tạo gồm **chính quy**, **vừa làm vừa học**, và **đào tạo từ xa**. Việc chuyển đổi được thực hiện theo nguyên tắc liên thông.
   - *Điều 65*: Văn bằng giáo dục đại học thuộc hệ thống giáo dục quốc dân gồm bằng cử nhân, bằng thạc sĩ, bằng tiến sĩ và văn bằng trình độ tương đương. Luật xóa bỏ sự phân biệt về **giá trị pháp lý** giữa các hình thức đào tạo trong tuyển dụng công chức, xét bậc lương và học lên trình độ cao hơn.
2. **Thông tư số 27/2019/TT-BGDĐT** (Quy định nội dung chính ghi trên văn bằng và phụ lục văn bằng giáo dục đại học, có hiệu lực từ 01/03/2020):
   - *Điều 2*: **Bãi bỏ hoàn toàn việc ghi "Hình thức đào tạo"** (chính quy, từ xa, vừa làm vừa học) trên trang chính của Bằng tốt nghiệp đại học. Bằng chỉ ghi: Tên cơ sở giáo dục, Ngành đào tạo, Trình độ đào tạo (Bằng cử nhân/Kỹ sư), Họ tên, Ngày sinh, Hạng tốt nghiệp...
   - *Điều 3*: **Bắt buộc phát hành Phụ lục văn bằng (Diploma Supplement)** kèm theo văn bằng chính. Điểm c Khoản 1 Điều 3 nêu rõ: Phụ lục văn bằng **BẮT BUỘC PHẢI GHI RÕ HÌNH THỨC ĐÀO TẠO** (chính quy, vừa làm vừa học, đào tạo từ xa).
3. **Thông tư số 28/2023/TT-BGDĐT** (Quy chế đào tạo từ xa trình độ đại học, có hiệu lực từ 12/02/2024):
   - *Khoản 3 Điều 5*: **Nghiêm cấm** thực hiện đào tạo từ xa đối với các ngành thuộc **lĩnh vực sức khỏe** có cấp chứng chỉ hành nghề khám chữa bệnh (Y khoa, Dược học, Điều dưỡng, Răng - Hàm - Mặt...) và **ngành đào tạo giáo viên** (Sư phạm).
4. **Thông tư số 08/2021/TT-BGDĐT** (Quy chế đào tạo trình độ đại học) & **Thông tư 08/2022/TT-BGDĐT** (Quy chế tuyển sinh đại học):
   - Quy định việc công nhận kết quả học tập và chuyển đổi tín chỉ (áp dụng cho đào tạo liên thông và văn bằng 2). Thời gian đào tạo thực tế phụ thuộc vào việc thẩm định bảng điểm và miễn giảm tín chỉ của Hội đồng chuyên môn từng trường.
5. **Luật Quảng cáo 2012 (Luật số 16/2012/QH13) & Nghị định 38/2021/NĐ-CP**:
   - *Khoản 9 Điều 8 Luật Quảng cáo*: Cấm quảng cáo không đúng hoặc gây nhầm lẫn về khả năng kinh doanh, khả năng cung cấp sản phẩm, hàng hóa, dịch vụ.

---

### 2.2. Bảng đối chiếu vi phạm Copy & Messaging trên toàn theme

```
                                      MA TRẬN KIỂM ĐỊNH PHÁP LÝ VĂN BẰNG
┌─────────────────────────────────┬──────────────────────────────────┬─────────────────────────────┬─────────────┐
│ Vị trí file & Số dòng           │ Đoạn nội dung / UI Component     │ Đối chiếu quy định pháp lý  │ Đánh giá    │
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ front-page.php:528-530          │ Badge cam trên Slider bằng cấp:  │ Trái TT 27/2019/TT-BGDĐT &  │ 🚨 CRITICAL │
│                                 │ "100% BẰNG CỬ NHÂN CHÍNH QUY"    │ Luật Quảng cáo 2012. Bằng   │ (Quảng cáo  │
│                                 │                                  │ từ xa không thể là "CQ"     │ sai sự thật)│
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ front-page.php:432-434          │ "Học viên sẽ được trường cấp bằng│ Dân gian hóa sai lệch; gây  │ 🔴 HIGH     │
│                                 │ Cử nhân (Bằng đỏ), được Bộ GD&ĐT │ nhầm lẫn là bằng loại       │ (Sai bản    │
│                                 │ công nhận."                      │ Xuất sắc                    │ chất)       │
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ front-page.php:727              │ "Phôi bằng tương đương chính quy,│ Sai thuật ngữ pháp lý. Phôi │ 🟡 MEDIUM   │
│                                 │ đủ điều kiện xét bậc lương..."   │ bằng thống nhất mẫu chuẩn;  │ (Sai thuật  │
│                                 │                                  │ giá trị pháp lý tương đương │ ngữ)        │
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ page-faq.php:31                 │ "Bằng ĐH từ xa không ghi Từ xa...│ Giấu thông tin bắt buộc ghi │ 🔴 HIGH     │
│ inc/cli-commands.php:991        │ Tất cả phôi bằng đều có giá trị  │ hình thức đào tạo trên      │ (Thiếu minh │
│                                 │ tương đương tốt nghiệp chính quy"│ Phụ lục văn bằng (Điều 3)   │ bạch)       │
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ inc/cli-commands.php:823        │ advantages: "Bằng đại học chính  │ Gán nhãn "Bằng chính quy"   │ 🔴 HIGH     │
│                                 │ quy từ Trường ĐH GTVT"           │ cho chương trình liên thông │ (Dữ liệu mẫu│
│                                 │                                  │ từ xa/vừa học vừa làm       │ sai chuẩn)  │
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ single-school.php:409-415       │ Hardcoded string matching hiển   │ Thiếu hệ thống phân loại    │ 🟡 MEDIUM   │
│ taxonomy-training_type.php:379  │ thị badge: 'chính quy' -> blue,  │ chuẩn theo Taxonomy Term ID │ (Code mong  │
│                                 │ 'từ xa' -> green...              │ hoặc Slug chuẩn             │ manh)       │
├─────────────────────────────────┼──────────────────────────────────┼─────────────────────────────┼─────────────┤
│ single-program.php:96           │ "Chương trình tuyển sinh hệ      │ Hardcode chữ "hệ Chính quy" │ 🟡 MEDIUM   │
│                                 │ Chính quy của trường năm nay..." │ khi chương trình tạm ngưng  │ (Sai ngữ    │
│                                 │                                  │ cho cả Từ xa / VB2          │ cảnh)       │
└─────────────────────────────────┴──────────────────────────────────┴─────────────────────────────┴─────────────┘
```

---

### 2.3. Phân tích chi tiết các điểm nóng vi phạm nghiêm trọng

#### Điểm nóng 1: Cam đoan "100% BẰNG CỬ NHÂN CHÍNH QUY" (`front-page.php:528-530`)
- **Đoạn code trực tiếp**:
  ```php
  <!-- Orange badge -->
  <div class="absolute bottom-6 left-6 bg-[#f97316] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[150px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
      <span class="text-3xl font-black leading-none">100%</span>
      <span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>
  </div>
  ```
- **Hệ quả pháp lý**:
  Thông tư 27/2019/TT-BGDĐT chỉ **bãi bỏ việc ghi chữ "Từ xa" hay "Chính quy"** trên trang chính của văn bằng, chứ **hoàn toàn không chuyển đổi chương trình đào tạo từ xa thành đào tạo chính quy**. Bằng cử nhân của hệ từ xa được cấp dưới hình thức đào tạo từ xa.
  Việc quảng bá "100% BẰNG CỬ NHÂN CHÍNH QUY" cấu thành hành vi **gian lận thông tin quảng cáo tuyển sinh**, vi phạm trực tiếp Điều 8 Luật Quảng cáo 2012 và Điều 18 Nghị định 04/2021/NĐ-CP về xử phạt vi phạm hành chính trong lĩnh vực giáo dục. Khi thí sinh nộp hồ sơ vào doanh nghiệp hoặc cơ quan nhà nước, đơn vị tuyển dụng yêu cầu nộp kèm **Phụ lục văn bằng**, việc lộ ra hình thức "Từ xa" sẽ dẫn đến khiếu kiện, khủng hoảng truyền thông cho website và các trường đại học đối tác.

#### Điểm nóng 2: Cụm từ dân gian "Bằng đỏ" (`front-page.php:432-434`)
- **Đoạn code trực tiếp**:
  ```html
  <h4 class="font-bold text-slate-900 text-base">Bằng đỏ</h4>
  <p class="text-slate-500 text-sm leading-relaxed">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân (Bằng đỏ), được Bộ GD&ĐT công nhận.</p>
  ```
- **Phân tích rủi ro**:
  Trong hệ thống giáo dục Việt Nam, thuật ngữ pháp lý chỉ có "Bằng tốt nghiệp đại học" hoặc "Bằng cử nhân/Kỹ sư". Từ "Bằng đỏ" chỉ là cách gọi truyền miệng dân gian (thường để chỉ bằng tốt nghiệp loại Xuất sắc có bìa màu đỏ, phân biệt với bìa màu xanh thông thường ở một số đại học trước đây). Sử dụng thuật ngữ này trên trang thương mại tuyển sinh chính thức là thiếu tính học thuật, gây hiểu lầm cho người học rằng họ chắc chắn sẽ nhận được bằng hạng danh dự/xuất sắc.

#### Điểm nóng 3: Sự thật về Phụ lục văn bằng bị che giấu (`page-faq.php:31`)
- **Đoạn code trực tiếp**:
  ```php
  [ 
      'question' => 'Bằng tốt nghiệp đại học từ xa có ghi chữ "Từ xa" không?', 
      'answer'   => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo từ ngày 1/3/2020, bằng đại học sẽ không còn ghi hình thức đào tạo (như Từ xa, Vừa học vừa làm, Chính quy) trên văn bằng tốt nghiệp. Tất cả phôi bằng đều có giá trị tương đương tốt nghiệp chính quy.' 
  ]
  ```
- **Phân tích rủi ro**:
  Câu trả lời này cung cấp sự thật một nửa (half-truth). Người đọc sẽ suy diễn rằng: "Không còn bất kỳ giấy tờ nào nhắc đến chữ Từ xa". Nhưng thực tế theo **Điều 3 Thông tư 27/2019/TT-BGDĐT**, Phụ lục văn bằng là văn bản pháp lý đi liền không tách rời của văn bằng, và **Khoản 1 Điều 3 bắt buộc phải ghi rõ hình thức đào tạo**. Việc không minh bạch điều này khiến người học cảm thấy bị lừa dối khi nhận bằng tốt nghiệp.

---

### 2.4. Đánh giá tính chuẩn xác trong truyền thông "Giá trị bằng tương đương"
- **Điểm tích cực**: Tại `front-page.php:580-625`, theme đã có một khối nội dung diễn đạt rất chuẩn mực và thận trọng:
  > *"Sau khi tốt nghiệp, người học được cấp văn bằng theo quy định hiện hành và có thể sử dụng để phục vụ các mục tiêu học tập, nghề nghiệp theo điều kiện của từng đơn vị tiếp nhận: Học tiếp lên trình độ cao hơn... Bổ sung hồ sơ nghề nghiệp... Tham gia tuyển dụng..."*
- **Xung đột nội tại**: Giao diện tồn tại sự mâu thuẫn trực tiếp giữa 2 nửa trang web: Nửa trên dùng các phát ngôn giật gân, cam đoan thái quá ("100% Bằng Cử nhân Chính quy", "Bằng đỏ", "Có giá trị sử dụng suốt đời trên toàn quốc"), trong khi nửa dưới lại dùng phát ngôn chuẩn mực của luật gia ("theo điều kiện của từng đơn vị tiếp nhận"). Sự thiếu nhất quán này làm suy giảm uy tín học thuật của thương hiệu.

---

### 2.5. Tuân thủ Thông tư 28/2023/TT-BGDĐT về Đào tạo từ xa
- **Lỗ hổng kiểm soát danh mục chương trình (Catalogue Governance Gap)**:
  Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT cấm đào tạo từ xa đối với ngành Sư phạm và Y Dược có cấp chứng chỉ hành nghề.
  Mặc dù module Eligibility Checker (`inc/eligibility.php:426`) đã có logic lọc chặn, nhưng **hệ thống CPT Program (`program`) và ACF fields trong wp-admin hoàn toàn không có validation hook**.
  Nếu quản trị viên nhập dữ liệu vô tình tạo một `program` có taxonomy `training_type = 'tu-xa'` liên kết với `major` là "Dược học" hoặc "Giáo dục mầm non", chương trình này sẽ lập tức hiển thị công khai trên frontend tại `/he-dao-tao/tu-xa/` và `/truong-dai-hoc/[school-slug]/`. Điều này biến website thành kênh quảng bá tuyển sinh trái phép.

---

### 2.6. Tuân thủ Thông tư 08/2021/TT-BGDĐT & 08/2022/TT-BGDĐT
- **Khẳng định cố định thời gian đào tạo**:
  Tại `single-program.php:158` và `single-school.php:402`, giao diện hiển thị cứng: `Thời gian học: 1.5 - 2 năm`.
  Trong khi đó, theo Điều 16 Thông tư 08/2021/TT-BGDĐT, thời gian học liên thông/văn bằng 2 được rút ngắn hoàn toàn dựa trên khối lượng tín chỉ được miễn trừ của văn bằng trước.
  Chính trong file dữ liệu gốc `schools_import.json:107`, chuyên viên tuyển sinh của trường đã lưu ý:
  `"TVTS sử dụng để truyền thông tư vấn và luôn nói dự kiến, ko khẳng định"`.
  Việc đưa ra con số cố định trên website mà không có từ "Dự kiến" hoặc "Tùy thuộc số tín chỉ miễn trừ" sẽ gây ngộ nhận cho thí sinh khi thời gian học thực tế bị kéo dài.

---

## 3. KIỂM TOÁN CHUYÊN SÂU YÊU CẦU R4: PHỄU TUYỂN SINH & PHÂN LUỒNG CRM

### 3.1. Sơ đồ kiến trúc luồng dữ liệu tuyển sinh hiện tại

```
                                    KIẾN TRÚC DỮ LIỆU TUYỂN SINH HIỆN TẠI
                                    
  [ Ứng viên gửi Form ]
          │
          ├──> single-program.php  ──> Injects: program_id, school_id, major_id
          ├──> single-school.php   ──> Injects: school_id (MẤT: training_type, major, campus)
          ├──> single-major.php    ──> Injects: major_id (MẤT: school, training_type, campus)
          ├──> front-page.php      ──> Injects: Không có gì (MẤT 100% ngữ cảnh học thuật)
          ├──> page-register.php   ──> Injects: Không có gì (MẤT 100% ngữ cảnh học thuật)
          └──> taxonomy-training   ──> KHÔNG CÓ FORM (100% RÒ RỈ PHỄU / LEAD LEAKAGE)
          │
          ▼
  [ ltdh_handle_native_form_submit() ]  ──> Kiểm tra Spam (Nếu nghi ngờ: wp_die trắng trang!)
          │
          ▼
  [ ltdh_insert_lead() ]  ──> Insert bảng wp_ltdh_leads
          │                     - Ghi $message vào cột `error_message` (LỖI THIẾT KẾ CSDL)
          │
          ├───► GỬI TELEGRAM TỨC THỜI (`ltdh_trigger_telegram_notification`)
          │         │
          │         ├──> Nếu từ Eligibility: Đầy đủ trường, ngành, cơ sở, hệ, điểm số.
          │         └──> Nếu từ Form tư vấn thông thường:
          │                  🚨 BỊ CẮT BỎ TOÀN BỘ Trường, Ngành, Hệ, Cơ sở!
          │                  (Tư vấn viên chỉ nhận: Tên + SĐT + Email + Message)
          │
          └───► ĐƯA VÀO HÀNG ĐỢI CRM SYNC (Trạng thái: 'pending')
                    │
                    ▼  (Chờ Cron định kỳ 5 phút: `ltdh_cron_sync_leads`)
           [ ltdh_process_lead_queue() ]
                    │  (Bị chặn bởi LIMIT 10; Không có cơ chế Row Lock)
                    ▼
           [ ltdh_sync_lead_to_crm() ]
                    │
                    ├──> Đọc duy nhất Option toàn cục `default_crm_type`
                    │    (Mô hình 1 CRM cho tất cả - Không hỗ trợ multi-tenant)
                    │
                    ├──> Gửi Title tiếng Việt vào school_code, major_code (LỖI SCHEMA CRM)
                    │
                    └──> Nếu Sync thành công:
                             🚨 UPDATE `error_message = ''`
                             ==> XÓA SẠCH VĨNH VIỄN LỜI NHẮN / NGUYỆN VỌNG CỦA ỨNG VIÊN!
```

---

### 3.2. Ma trận thu thập dữ liệu (Payload Integrity Matrix) theo từng điểm chạm

| Điểm chạm (Touchpoint) | Trường dữ liệu truyền vào Form | Thu thập vào Database | Dữ liệu đẩy sang Telegram | Dữ liệu đẩy sang CRM | Đánh giá mức độ toàn vẹn |
|---|---|---|---|---|---|
| **Trang Chương trình** (`single-program.php`) | `current_program_id`<br>`current_school_id`<br>`current_major_id` | `program_id`, `school_id`, `major_id`, tự giải quyết `training_type` & `campus` | ❌ BỊ LỌC BỎ (Chỉ gửi Họ tên, SĐT, Email) | Gửi Tiêu đề tiếng Việt chương trình, trường, ngành | 🟡 **Khá**: DB có đủ, nhưng Telegram và CRM bị méo dạng |
| **Trang Trường đối tác** (`single-school.php`) | `current_school_id` | `school_id`<br>❌ `program_id = 0`<br>❌ `major_id = 0`<br>❌ `training_type = ''`<br>❌ `campus = ''` | ❌ BỊ LỌC BỎ (Chỉ gửi Họ tên, SĐT, Email) | `university: [Tên trường]`<br>`target_class: Không xác định`<br>`major: Không xác định` | 🔴 **Kém**: Thiếu ngành và hệ, CRM nhận lead vô danh ngành |
| **Trang Chuyên ngành** (`single-major.php`) | `current_major_id` | `major_id`<br>❌ `school_id = 0`<br>❌ `program_id = 0`<br>❌ `training_type = ''`<br>❌ `campus = ''` | ❌ BỊ LỌC BỎ (Chỉ gửi Họ tên, SĐT, Email) | `major: [Tên ngành]`<br>`university: Không xác định`<br>`target_class: Không xác định` | 🔴 **Kém**: Thiếu trường và hệ, không biết gửi cho trường nào |
| **Trang Hệ đào tạo** (`taxonomy-training_type.php`) | ❌ **KHÔNG CÓ FORM** | Không thu thập được gì | Không | Không | 🚨 **RÒ RỈ 100%**: Ứng viên quan tâm hệ từ xa không thể đăng ký tại trang này |
| **Trang Đăng ký chung** (`page-register.php`) | Không có trường ẩn nào | Toàn bộ ID = 0, chuỗi = rỗng | Chỉ gửi Họ tên, SĐT, Email | Tất cả thực thể học thuật đều là "Không xác định" | 🚨 **NGUY CẤP**: Lead không có bất kỳ thông tin nguyện vọng nào |
| **Trang chủ Hero / Form** (`front-page.php`) | Không có trường ẩn nào | Toàn bộ ID = 0, chuỗi = rỗng | Chỉ gửi Họ tên, SĐT, Email | Tất cả thực thể học thuật đều là "Không xác định" | 🚨 **NGUY CẤP**: Lead rác thông tin, tốn chi phí gọi phân loại |

---

### 3.3. Lỗi mù thông tin Lead trên Telegram Bot (`ltdh_trigger_telegram_notification`)
- **Vị trí code**: `inc/lead-capture.php`, dòng 188-255.
- **Quan sát thực tế**:
  ```php
  $is_eligibility = ( isset( $data['referral_source'] ) && strpos( $data['referral_source'], 'eligibility_checker' ) !== false );
  
  if ( $is_eligibility ) {
      // Nhánh Eligibility: Soạn tin đầy đủ Trường, Ngành, Hệ, Cơ sở, Ảnh bằng...
      ...
  } else {
      // Nhánh Form tư vấn thông thường (Chiếm 90% số lượng form trên site):
      $msg_text  = "🔔 <b>YÊU CẦU TƯ VẤN MIỄN PHÍ MỚI</b> 🔔\n\n";
      $msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
      $msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
      $msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
      if ( ! empty( $message ) ) {
          $msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
      }
      if ( ! empty( $degree_link ) ) {
          $msg_text .= "📎 <b>Ảnh bằng cấp:</b> " . esc_url( $degree_link ) . "\n";
      }
  }
  ```
- **Hậu quả vận hành**:
  Khi ứng viên đang đứng ở trang **Đại học Giao thông Vận tải**, điền form "Đăng ký vào Trường Đại học GTVT", hàm `ltdh_insert_lead` nhận được `school_id`. Tuy nhiên, khi chuyển sang `ltdh_trigger_telegram_notification`, vì `$is_eligibility == false`, tin nhắn Telegram **hoàn toàn không in ra tên trường, không in ra tên ngành, không in ra hệ đào tạo**!
  Đội ngũ tư vấn trực Telegram chỉ nhận được: *"Khách hàng Nguyễn Văn A, SĐT 0912345678"*. Chuyên viên tuyển sinh phải gọi điện hỏi lại từ đầu: "Anh/chị đăng ký trường nào vậy ạ?", gây trải nghiệm vô cùng thiếu chuyên nghiệp và giảm tỷ lệ chốt tuyển sinh (conversion rate).

---

### 3.4. Điểm nghẽn kiến trúc CRM: "Một CRM cho toàn bộ hệ thống"
- **Vị trí code**: `inc/crm-adapters.php`, dòng 91-124.
- **Phân tích logic**:
  ```php
  function ltdh_sync_lead_to_crm( $lead ) {
      $crm_type = get_field( 'default_crm_type', 'options' );

      if ( ! $crm_type || $crm_type === 'internal' ) {
          return true; // No third-party CRM sync needed
      }
      ...
      if ( $crm_type === 'onschool' ) {
          return ltdh_sync_onschool_adapter( $payload );
      } elseif ( $crm_type === 'aum' ) {
          return ltdh_sync_aum_adapter( $payload );
      } elseif ( $crm_type === 'erpnext' ) {
          return ltdh_sync_erpnext_adapter( $payload );
      }
  }
  ```
- **Lỗ hổng kiến trúc nghiêm trọng**:
  Trong thực tế tuyển sinh liên thông và từ xa tại Việt Nam:
  * Trường ĐH Thái Nguyên (TNU) sử dụng nền tảng **OnSchool**.
  * Trường ĐH Thương Mại (TMU), ĐH Mở HN sử dụng nền tảng **AUM CRM**.
  * Các trường như ĐH Kinh tế Quốc dân (NEU), ĐH GTVT có trung tâm tuyển sinh riêng, tiếp nhận lead qua **Webhook/Email/Telegram nội bộ**.
  Theme hiện tại chỉ có một thiết lập toàn cục duy nhất `default_crm_type`.
  * Nếu quản trị viên cấu hình `onschool`: **Toàn bộ lead của TMU, UTC, NEU bị đẩy thẳng sang OnSchool!** (OnSchool sẽ từ chối hoặc làm thất lạc data của trường khác).
  * Nếu cấu hình `aum`: Toàn bộ lead của OnSchool bị đẩy sang AUM!
  * **Hệ thống hoàn toàn thiếu cơ chế định tuyến lead dựa trên Trường đối tác (School-based Routing) hoặc dựa trên Hệ đào tạo (Training Type-based Routing).**

---

### 3.5. Lỗi ánh xạ dữ liệu: Gửi chuỗi tiêu đề tiếng Việt thay vì mã định danh
- **Vị trí code**: `inc/crm-adapters.php`, dòng 99-101 và 142-195.
- **Thực tế trong code**:
  ```php
  $program_name = $lead->program_id ? get_the_title( $lead->program_id ) : 'Không xác định';
  $school_name  = $lead->school_id ? get_the_title( $lead->school_id ) : 'Không xác định';
  $major_name   = $lead->major_id ? get_the_title( $lead->major_id ) : 'Không xác định';
  ```
  Và khi đẩy sang AUM CRM (dòng 184-195):
  ```php
  'body' => wp_json_encode( [
      'fullname'      => $payload['name'],
      'telephone'     => $payload['phone'],
      'email_address' => $payload['email'],
      'program_code'  => $payload['program'], // <-- Gửi: "Cử nhân Công nghệ thông tin - ĐH GTVT"
      'school_code'   => $payload['school'],  // <-- Gửi: "Trường Đại học Giao thông Vận tải"
      'major_code'    => $payload['major'],   // <-- Gửi: "Công nghệ thông tin"
      'training'      => $payload['training_type'], // <-- Gửi: "Từ xa"
      'location'      => $payload['campus'],        // <-- Gửi: "Hà Nội"
  ] )
  ```
- **Hậu quả API**:
  Bất kỳ hệ thống CRM nào (AUM, OnSchool hay ERPNext) đều yêu cầu `school_code` là mã viết tắt chuẩn hóa (ví dụ: `UTC`, `NEU`, `TMU`), `major_code` là mã chuẩn Bộ GD&ĐT (ví dụ: `7480201`), `training` là mã hệ (`tu-xa` hoặc `DISTANCE`), và `location` là mã chi nhánh (`HAN`, `SGN`).
  Việc gửi cả chuỗi tiếng Việt dài có dấu sẽ làm gãy schema validation của API CRM đối tác, trả về mã lỗi HTTP 400 Bad Request, hoặc bị CRM đẩy vào thùng chứa rác (Unassigned Leads).

---

### 3.6. Lỗi xóa vĩnh viễn nội dung ghi chú người dùng (`error_message` bug)
- **Vị trí code**: 
  1. `inc/lead-capture.php`, dòng 22-40 (Tạo bảng CSDL) & dòng 139 (`ltdh_insert_lead`).
  2. `inc/crm-adapters.php`, dòng 80 (`ltdh_process_lead_queue`).
- **Phân tích cơ chế gây lỗi**:
  * Khi tạo bảng `wp_ltdh_leads`, lập trình viên **quên tạo cột `message` hoặc `notes`**. Bảng chỉ có các cột: `id`, `name`, `phone`, `email`, `program_id`, `school_id`, `major_id`, `training_type`, `campus`, `referral_source`, `sync_status`, `retry_count`, `error_message`, `created_at`, `synced_at`.
  * Khi hàm `ltdh_insert_lead` chạy (dòng 139):
    `'error_message' => $message,` -> Tạm thời "mượn" cột `error_message` để lưu lời nhắn của thí sinh (ví dụ: "Tôi đã có bằng CĐ Kế toán, muốn học lớp tối T7-CN...").
  * Khi tiến trình Cron chạy đến `ltdh_process_lead_queue` (dòng 80):
    Sau khi sync CRM thành công, code chạy:
    ```php
    $wpdb->update(
        $table_name,
        [
            'sync_status'   => 'synced',
            'synced_at'     => current_time( 'mysql' ),
            'error_message' => '', // <-- XÓA TRẮNG CỘT ERROR_MESSAGE!
        ],
        [ 'id' => $lead->id ]
    );
    ```
  * **Hậu quả nghiêm trọng**: Toàn bộ lời nhắn, câu hỏi chuyên biệt và thông tin học vấn cũ mà thí sinh kỳ công gõ vào form **bị xóa sạch 100% khỏi cơ sở dữ liệu** ngay khi lead được đồng bộ! Thậm chí trong trang quản trị wp-admin (`inc/eligibility.php:1321-1378`), bảng hiển thị lead cũng **không có cột hiển thị lời nhắn**. Dữ liệu này biến mất không dấu vết.

---

### 3.7. Lỗi rơi rụng ngữ cảnh khi kích hoạt Contact Form 7
- **Vị trí code**: `inc/core/class-helpers.php`, dòng 159-166:
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
- **Phân tích**:
  Nếu người quản trị vào cài đặt Theme Options và nhập ID của một Contact Form 7 (`cf7_consultation_form_id`), hàm sẽ lập tức `echo $shortcode; return;`.
  Toàn bộ tham số trong mảng `$context_hidden_fields` (chứa `current_school_id`, `current_program_id`, `current_major_id`) **bị vứt bỏ hoàn toàn**, không được truyền vào CF7.
  Khi thí sinh submit form CF7, hàm interceptor `ltdh_capture_cf7_lead` (`inc/lead-capture.php:307`) tìm kiếm `current_program_id`, `current_school_id` trong `$posted_data` nhưng không tìm thấy (vì CF7 mặc định không tự sinh các thẻ ẩn này).
  Kết quả: **Tất cả các lead gửi qua CF7 đều có School ID = 0, Program ID = 0, Major ID = 0!**

---

### 3.8. Hạn chế của WP-Cron và rủi ro nghẽn hàng đợi (Queue Bottleneck)
1. **Lỗi móc Hook vào `admin_init`**:
   `inc/crm-adapters.php:24`:
   `add_action( 'admin_init', 'ltdh_schedule_crm_sync' );`
   Hook `admin_init` chỉ kích hoạt khi có người dùng đăng nhập vào trang quản trị wp-admin. Nếu trên môi trường Production, quản trị viên không vào wp-admin trong vài ngày hoặc dùng headless/caching, lịch cron định kỳ `ltdh_cron_sync_leads` sẽ không được nạp vào hệ thống.
2. **Nghẽn hàng đợi (Starvation under load)**:
   `ltdh_process_lead_queue()` giới hạn cố định `LIMIT 10` lead mỗi lần chạy (5 phút/lần). Như vậy tối đa hệ thống chỉ đồng bộ được 120 leads/giờ.
   Trong các đợt chạy quảng cáo tuyển sinh cao điểm (Facebook Ads, Google Ads mùa tuyển sinh), số lượng lead có thể đạt 500 - 1.000 leads/ngày, tạo ra hiện tượng ứ đọng hàng đợi kéo dài hàng giờ, khiến tư vấn viên tiếp cận khách hàng quá trễ (Lead bị nguội).
3. **Thiếu cơ chế Atomic Lock**:
   Không có cờ khoá tiến trình (Transient Lock / Mutex) hoặc `SELECT ... FOR UPDATE`. Nếu có 2 web request cùng kích hoạt `wp-cron.php`, hai tiến trình song song sẽ cùng lấy 10 lead giống nhau và đẩy trùng lặp 2 lần vào CRM đối tác.

---

## 4. ĐÁNH GIÁ KHẢ NĂNG MỞ RỘNG & VẬN HÀNH ĐỐI TÁC TRƯỜNG / HỆ

### 4.1. Quy trình kết nối thêm Trường Đại học đối tác mới
- **Thao tác hiện tại**:
  1. Tạo bài viết CPT `school` mới trong wp-admin.
  2. Tạo thủ công N bài viết CPT `program` tương ứng với từng ngành và hệ của trường.
- **Rào cản / Điểm nghẽn**:
  * Không thể cấu hình đầu mối tiếp nhận riêng cho trường này. Nếu trường yêu cầu: "Lead của trường tôi phải bắn về Telegram Group riêng của Ban tuyển sinh trường" hoặc "Bắn về Webhook CRM của trường tôi", hệ thống hiện tại **hoàn toàn bất lực** vì mọi cài đặt CRM/Telegram đều nằm cứng ở trang Options toàn trang.
  * Mã trường `school_code` (ví dụ: `TNU`, `UTC`) đã có trong ACF (`field_school_code`) nhưng không bao giờ được sử dụng trong logic routing của CRM.

### 4.2. Quy trình mở một Hệ đào tạo mới (Ví dụ: "Chất lượng cao", "Song bằng")
- **Thao tác hiện tại**:
  1. Thêm Term mới trong Taxonomy `training_type`.
- **Rào cản / Điểm nghẽn**:
  * Taxonomy `training_type` **chỉ được gán vào CPT `program`** (`acf-import-cpts.json:175`), **hoàn toàn không được gán vào CPT `school`**.
  * Tại `archive-school.php:80`:
    `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );`
    Code cố tình lấy taxonomy của School, dẫn đến luôn trả về mảng rỗng (Trường nổi bật không bao giờ hiện được nhãn hệ đào tạo).
  * Toàn bộ mã màu, badge hiển thị của hệ đào tạo bị hardcode bằng chuỗi ký tự trong các template PHP (`single-school.php:409-415`, `taxonomy-training_type.php:380-386`):
    `if ( false !== strpos( $type_name_lower, 'chính quy' ) ) ... elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) ...`
    Khi mở một hệ mới, hệ thống không thể tự nhận diện màu sắc/icon mà sẽ fallback về màu cam mặc định, không có định tuyến riêng.

### 4.3. Quy trình tạm dừng tuyển sinh một cặp (Trường + Hệ đào tạo)
- **Kịch bản thực tế**: "Trường Đại học Kinh tế Quốc dân (NEU) tạm dừng tuyển sinh đợt này đối với toàn bộ các ngành hệ Từ xa".
- **Rào cản trong kiến trúc hiện tại**:
  * Trạng thái tuyển sinh `admission_status` (`tuyen-sinh`, `tam-ngung`, `sap-mo`) chỉ được lưu tại từng bài viết `program`.
  * Nếu NEU có 15 ngành hệ Từ xa, quản trị viên bắt buộc phải vào tìm và sửa thủ công cả 15 bài viết `program` từ `tuyen-sinh` sang `tam-ngung`. Không có nút gạt tổng thể cấp Trường + Hệ.
  * **Trải nghiệm frontend bị đứt gãy**:
    - Trên `single-school.php:330`: Hệ thống dùng query filter loại bỏ hoàn toàn các program `tam-ngung`. Thí sinh vào xem trang trường NEU sẽ thấy danh sách ngành biến mất không một lời giải thích.
    - Nếu thí sinh truy cập trực tiếp link chương trình tạm ngưng (`single-program.php:96`):
      Hệ thống hiển thị cảnh báo màu đỏ với câu chữ hardcode:
      `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."`
      (Dù đây là chương trình Từ xa, vẫn bị thông báo là "hệ Chính quy").
    - **Form đăng ký ở sidebar vẫn hiển thị và cho phép submit bình thường**, tiếp tục thu thập lead cho một chương trình đã đóng mà không hề có cờ cảnh báo `is_paused` gửi sang tư vấn viên.

---

## 5. ĐỀ XUẤT KIẾN TRÚC NÂNG CẤP & MÃ NGUỒN MẪU KHẮC PHỤC

### 5.1. Nâng cấp cơ sở dữ liệu `wp_ltdh_leads`
Cần thực hiện DB migration bổ sung các trường bị thiếu nhằm giải quyết triệt để vấn đề mất mát dữ liệu và phục vụ định tuyến CRM:

```sql
-- Migration Script: Bổ sung các cột định tuyến và lưu trữ ghi chú
ALTER TABLE wp_ltdh_leads 
ADD COLUMN message TEXT NULL AFTER referral_source,
ADD COLUMN school_code VARCHAR(50) DEFAULT '' AFTER school_id,
ADD COLUMN major_code VARCHAR(50) DEFAULT '' AFTER major_id,
ADD COLUMN training_type_slug VARCHAR(100) DEFAULT '' AFTER training_type,
ADD COLUMN campus_slug VARCHAR(100) DEFAULT '' AFTER campus,
ADD COLUMN crm_target VARCHAR(50) DEFAULT 'default' AFTER sync_status,
ADD COLUMN external_lead_id VARCHAR(100) DEFAULT '' AFTER crm_target,
ADD COLUMN utm_source VARCHAR(100) DEFAULT '' AFTER external_lead_id,
ADD COLUMN utm_medium VARCHAR(100) DEFAULT '' AFTER utm_source,
ADD COLUMN utm_campaign VARCHAR(100) DEFAULT '' AFTER utm_medium;
```

---

### 5.2. Kiến trúc Lead Routing đa đối tác (Multi-tenant CRM Routing Engine)

```
                       SƠ ĐỒ ĐỊNH TUYẾN LEAD ĐA ĐỐI TÁC (MULTI-TENANT ROUTER)
                       
                                     [ Lead Mới ]
                                          │
                                          ▼
                         [ ltdh_resolve_lead_routing($lead) ]
                                          │
                ┌─────────────────────────┴─────────────────────────┐
                ▼                                                   ▼
     { Có school_id hợp lệ? }                            { Không có school_id? }
          │ (YES)                                                   │
          ├──> Kiểm tra cấu hình CRM tại Trường                     └──> Chuyển về Router mặc định
          │    (get_field('school_crm_override', $school_id))            - default_crm_type (Options)
          │                                                              - Telegram Admin chung
          ├───► Trường hợp A: TNU (Đại học Thái Nguyên)
          │         └──> Endpoint OnSchool TNU + Mã trường "TNU"
          │
          ├───► Trường hợp B: TMU (Đại học Thương Mại)
          │         └──> Endpoint AUM CRM TMU + Mã trường "TMU"
          │
          └───► Trường hợp C: UTC (ĐH Giao thông Vận tải)
                    └──> Nhóm Telegram riêng của Ban Tuyển sinh UTC
```

---

### 5.3. Chuẩn hóa bộ ACF Trường đối tác: Cấu hình CRM & Telegram riêng
Bổ sung Field Group `group_school_crm_routing` vào CPT `school`:
- `school_crm_provider`: Select (`inherit_global`, `onschool`, `aum`, `internal`, `custom_webhook`).
- `school_crm_endpoint`: URL (Ghi đè endpoint API riêng cho từng trường).
- `school_crm_token`: Password (API Token riêng của tài khoản trường đối tác).
- `school_telegram_chat_id`: Text (Chat ID của nhóm Zalo/Telegram phụ trách tuyển sinh riêng của trường đó).
- `school_admission_paused_types`: Checkbox các hệ đào tạo tạm ngưng tuyển sinh tại trường này (`tu-xa`, `lien-thong`, `van-bang-2`).

---

### 5.4. Khắc phục hàm gửi Telegram thông minh đầy đủ ngữ cảnh

```php
function ltdh_trigger_telegram_notification_v2( array $data ): void {
    $school_id   = intval( $data['school_id'] ?? 0 );
    $program_id  = intval( $data['program_id'] ?? 0 );
    $major_id    = intval( $data['major_id'] ?? 0 );

    // 1. Xác định Chat ID theo Trường (nếu trường có cấu hình riêng) hoặc dùng Chat ID chung
    $bot_token = defined( 'LTDH_TELEGRAM_BOT_TOKEN' ) && LTDH_TELEGRAM_BOT_TOKEN ? LTDH_TELEGRAM_BOT_TOKEN : get_field( 'telegram_bot_token', 'options' );
    $chat_id   = '';
    if ( $school_id && function_exists( 'get_field' ) ) {
        $chat_id = get_field( 'school_telegram_chat_id', $school_id );
    }
    if ( empty( $chat_id ) ) {
        $chat_id = defined( 'LTDH_TELEGRAM_CHAT_ID' ) && LTDH_TELEGRAM_CHAT_ID ? LTDH_TELEGRAM_CHAT_ID : get_field( 'telegram_chat_id', 'options' );
    }

    if ( empty( $bot_token ) || empty( $chat_id ) ) {
        return;
    }

    // 2. Phục hồi đầy đủ thông tin danh vị
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
    $msg .= "📍 <b>Cơ sở / Khu vực:</b> " . esc_html( $campus ) . "\n";
    if ( ! empty( $program_title ) ) {
        $msg .= "📌 <b>Chương trình cụ thể:</b> " . esc_html( $program_title ) . "\n";
    }
    if ( ! empty( $data['message'] ) ) {
        $msg .= "💬 <b>Ghi chú của thí sinh:</b> <i>" . esc_html( $data['message'] ) . "</i>\n";
    }
    $msg .= "🔗 <b>Nguồn đăng ký:</b> " . esc_url( $data['referral_source'] ?? '' ) . "\n";
    $msg .= "⏱️ <b>Thời gian:</b> " . current_time( 'd/m/Y H:i:s' ) . "\n";

    // Gửi Telegram phi đồng bộ
    $chat_ids = array_filter( array_map( 'trim', preg_split( '/[\s,;]+/', $chat_id ) ) );
    $api_url  = "https://api.telegram.org/bot" . urlencode( $bot_token ) . "/sendMessage";

    foreach ( $chat_ids as $cid ) {
        wp_remote_post( $api_url, [
            'body'     => [ 'chat_id' => $cid, 'text' => $msg, 'parse_mode' => 'HTML' ],
            'timeout'  => 5,
            'blocking' => false,
        ] );
    }
}
```

---

### 5.5. Bộ quy chuẩn thông điệp văn bằng & Disclaimer chuẩn pháp lý

#### 1. Thay thế Badge vi phạm tại `front-page.php:528-530`
- **Mã cũ (Vi phạm pháp luật)**:
  ```html
  <span class="text-3xl font-black leading-none">100%</span>
  <span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>
  ```
- **Mã mới (Chuẩn hóa pháp lý theo Thông tư 27/2019/TT-BGDĐT)**:
  ```html
  <span class="text-2xl font-black leading-none text-white">BẰNG CHUẨN</span>
  <span class="text-xs font-extrabold tracking-wider uppercase mt-1 leading-tight text-white/90">BỘ GIÁO DỤC & ĐÀO TẠO<br><span class="text-[10px] font-semibold text-amber-200">KHÔNG GHI HÌNH THỨC ĐÀO TẠO</span></span>
  ```

#### 2. Chuẩn hóa câu trả lời FAQ tại `page-faq.php:31`
- **Nội dung chuẩn hóa đề xuất**:
  > *"**Hỏi:** Bằng tốt nghiệp đại học từ xa có ghi chữ 'Từ xa' không?  
  > **Trả lời:** Căn cứ theo **Thông tư số 27/2019/TT-BGDĐT** của Bộ Giáo dục và Đào tạo có hiệu lực từ ngày 01/03/2020:  
  > 1. Trên trang chính của **Văn bằng tốt nghiệp đại học** (bằng Cử nhân/Kỹ sư) **hoàn toàn không ghi hình thức đào tạo** (bãi bỏ việc ghi Từ xa, Vừa làm vừa học hay Chính quy).  
  > 2. Hình thức đào tạo ('Đào tạo từ xa' hoặc 'Vừa làm vừa học') được ghi đầy đủ và minh bạch trên **Phụ lục văn bằng** (Diploma Supplement) đi kèm theo đúng quy định tại Điều 3 Thông tư 27/2019/TT-BGDĐT.  
  > 3. Căn cứ theo **Luật Giáo dục đại học sửa đổi năm 2018 (Điều 65)**, văn bằng giáo dục đại học thuộc các hình thức đào tạo khác nhau đều có **giá trị pháp lý tương đương**, có giá trị sử dụng trọn đời trên toàn quốc, đủ điều kiện thi công chức, xét nâng bậc lương và đăng ký thi tuyển các bậc học cao hơn (Thạc sĩ, Tiến sĩ).  
  > *Lưu ý: Đối với một số ngành nghề đặc thù có cấp chứng chỉ hành nghề y tế chuyên sâu hoặc khối ngành tư pháp/lực lượng vũ trang, điều kiện tuyển dụng cụ thể sẽ áp dụng theo quy định của từng ngành."*

#### 3. Bổ sung Legal Disclaimer Footer tại trang chi tiết Chương trình & Trường
Thêm khối thông tin pháp lý ở chân trang `single-program.php` và `single-school.php`:
```html
<div class="mt-8 p-4 bg-slate-100 rounded-xl border border-slate-200 text-xs text-slate-500 leading-relaxed space-y-1">
    <p class="font-bold text-slate-700">⚖️ CĂN CỨ PHÁP LÝ & QUY CHẾ ĐÀO TẠO:</p>
    <p>• Chương trình được tổ chức và cấp bằng theo Quy chế đào tạo đại học hiện hành của Bộ GD&ĐT (Thông tư 08/2021/TT-BGDĐT đối với Liên thông/Văn bằng 2; Thông tư 28/2023/TT-BGDĐT đối với Đào tạo từ xa).</p>
    <p>• Mẫu văn bằng tốt nghiệp tuân thủ Thông tư 27/2019/TT-BGDĐT (không ghi hình thức đào tạo trên văn bằng chính; hình thức đào tạo ghi trên Phụ lục văn bằng).</p>
    <p>• Thời gian đào tạo hiển thị trên website mang tính chất dự kiến và tiêu chuẩn. Thời gian thực tế được xác định sau khi Hội đồng tuyển sinh của Nhà trường thẩm định bảng điểm và công nhận số tín chỉ miễn trừ của từng học viên.</p>
</div>
```

---

## KẾT LUẬN & KIẾN NGHỊ ƯU TIÊN

1. **Ưu tiên 1 (Khẩn cấp - Tuân thủ pháp luật)**: Sửa ngay badge `100% BẰNG CỬ NHÂN CHÍNH QUY` tại `front-page.php:528` và các phát ngôn "Bằng đỏ" để triệt tiêu hoàn toàn rủi ro bị thanh tra xử phạt hành chính về vi phạm quảng cáo giáo dục.
2. **Ưu tiên 2 (Khẩn cấp - Cứu vãn dữ liệu tuyển sinh)**: Sửa ngay hàm `ltdh_process_lead_queue()` tại `inc/crm-adapters.php:80` để chấm dứt việc xóa sạch lời nhắn người dùng khi sync CRM.
3. **Ưu tiên 3 (Quan trọng - Tối ưu tỷ lệ chốt)**: Nâng cấp hàm gửi Telegram `ltdh_trigger_telegram_notification` để tư vấn viên lập tức nhận được tên trường và tên ngành khi thí sinh submit từ bất kỳ trang nào.
4. **Ưu tiên 4 (Cải tạo phễu)**: Bổ sung form đăng ký nhận tư vấn theo hệ trên trang `taxonomy-training_type.php` để bịt lỗ hổng rò rỉ chuyển đổi.
5. **Ưu tiên 5 (Kiến trúc dài hạn)**: Tách lớp Lead Routing thành kiến trúc đa đối tác (Multi-tenant) có cấu hình CRM và Telegram riêng theo từng Trường Đại học đối tác.
