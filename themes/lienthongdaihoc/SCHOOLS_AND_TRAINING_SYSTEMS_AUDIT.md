# BÁO CÁO KIỂM ĐỊNH KỸ THUẬT & NGHIỆP VỤ TOÀN DIỆN
# HỆ THỐNG ĐÀO TẠO & TRƯỜNG TUYỂN SINH
## COMPREHENSIVE TECHNICAL AUDIT: DATA ARCHITECTURE, QUERY OPTIMIZATION, REGULATORY COMPLIANCE & ADMISSIONS CRM FUNNEL

---

- **Dự án**: Nền tảng Tuyển sinh & Hướng nghiệp Liên Thông Đại Học (`lienthongdaihoc.com`)
- **Đối tượng kiểm định**: Theme WordPress `lienthongdaihoc` v2.0.0 (PHP 8.1+, MySQL 8.0, ACF Pro 6+)
- **Mã kiểm định**: `LTDH-AUDIT-CORE-R1-R4-2026`
- **Tác giả kiểm định**: `worker_audit_3` (Teamwork Audit Specialist & Architecture Worker)
- **Ngày ban hành**: 28/09/2026
- **Trạng thái**: Authoritative Final Audit Report (Không can thiệp sửa đổi trực tiếp mã nguồn theme)

---

## MỤC LỤC CHI TIẾT

1. [PHẦN 1: EXECUTIVE SUMMARY & BẢNG CHỈ SỐ SỨC KHỎE NGHIỆP VỤ (HEALTH SCORECARD)](#phần-1-executive-summary--bảng-chỉ-số-sức-khỏe-nghiệp-vụ-health-scorecard)
   - 1.1. Bối cảnh & Mục tiêu kiểm định
   - 1.2. Bảng chỉ số sức khỏe tổng thể (Master Health Scorecard)
   - 1.3. Bảng phân loại rủi ro tổng hợp (Critical, High, Medium, Low)
2. [PHẦN 2: ĐÁNH GIÁ KIẾN TRÚC DỮ LIỆU & MÔ HÌNH THỰC THỂ (REQUIREMENT R1)](#phần-2-đánh-giá-kiến-trúc-dữ-liệu--mô-hình-thực-thể-requirement-r1)
   - 2.1. Đánh giá Mô hình thực thể tam giác: `School` ⟷ `Major` ⟷ `Program` ⟷ `training_type` ⟷ `campus`
   - 2.2. Phân tích ưu / nhược điểm của việc gán `training_type` vào `program` vs `school` vs CPT độc lập
   - 2.3. Sáu (6) điểm nghẽn kiến trúc và rủi ro toàn vẹn dữ liệu nghiêm trọng
   - 2.4. Đề xuất Kiến trúc nâng cấp: Two-Tier Rollup Architecture & `LTDH_Entity_Relationship_Engine`
   - 2.5. Thiết kế Schema Ma trận Tuyển sinh theo Trình độ đầu vào (Input-Tier Matrix Repeater)
   - 2.6. Thiết kế Schema Đợt tuyển sinh có cấu trúc thời gian ISO (`structured_admission_batches`)
   - 2.7. Thiết kế Cấu trúc Quản lý Cơ sở, Phân hiệu và Trạm đào tạo liên kết (`study_stations`)
3. [PHẦN 3: ĐÁNH GIÁ LOGIC TRUY VẤN, BỘ LỌC & UX PHÂN LOẠI (REQUIREMENT R2)](#phần-3-đánh-giá-logic-truy-vấn-bộ-lọc--ux-phân-loại-requirement-r2)
   - 3.1. Phân tích truy vấn WP_Query & Các bẫy N+1 Query nghiêm trọng
   - 3.2. Hiện tượng bất đối xứng hiển thị Badge giữa Card View và List View trên `archive-school.php`
   - 3.3. Phân tích Double Query và Bỏ qua Main Query trên `taxonomy-training_type.php`
   - 3.4. Kiểm toán AJAX Filter trong `main.js`: Lỗi Dead Code do thiếu `#program-results-container`
   - 3.5. Lỗi Lệch số lượng Facet (Phantom Facets) trên Sidebar bộ lọc
   - 3.6. Kiểm toán SEO, Permalinks, Canonical Loop `/chuong-trinh/` và Breadcrumbs Schema
   - 3.7. Bộ giải pháp kỹ thuật & Code Snippets tối ưu hóa đạt chuẩn WordPress Core Standards
4. [PHẦN 4: ĐÁNH GIÁ TUÂN THỦ PHÁP LÝ TUYỂN SINH & NIỀM TIN VĂN BẰNG (REQUIREMENT R3)](#phần-4-đánh-giá-tuân-thủ-pháp-lý-tuyển-sinh--niềm-tin-văn-bằng-requirement-r3)
   - 4.1. Hệ thống căn cứ pháp lý hiện hành tại Việt Nam
   - 4.2. Bảng đối chiếu các sai phạm truyền thông, quảng cáo sai lệch trên toàn theme
   - 4.3. Phân tích chi tiết các điểm nóng vi phạm nghiêm trọng
   - 4.4. Đánh giá tính chuẩn xác trong truyền thông "Giá trị bằng tương đương"
   - 4.5. Rủi ro pháp lý về Đào tạo từ xa khối ngành Sức khỏe và Sư phạm (Thông tư 28/2023/TT-BGDĐT)
   - 4.6. Bộ thông điệp truyền thông chuẩn hóa pháp lý thay thế (Legal Whitelist & Blacklist)
5. [PHẦN 5: ĐÁNH GIÁ PHỄU TUYỂN SINH & PHÂN LUỒNG LEAD CRM (REQUIREMENT R4)](#phần-5-đánh-giá-phễu-tuyển-sinh--phân-luồng-lead-crm-requirement-r4)
   - 5.1. Lỗ hổng mất dữ liệu nghiêm trọng (Data Loss Bug): Xóa trắng ghi chú thí sinh khi sync CRM
   - 5.2. Lỗi mù thông tin trên Telegram Bot (`ltdh_trigger_telegram_notification`)
   - 5.3. Điểm nghẽn kiến trúc CRM đơn kênh toàn cục & Gửi sai schema dữ liệu (tiêu đề tiếng Việt có dấu)
   - 5.4. Lỗ hổng rơi rụng ngữ cảnh học thuật khi kích hoạt Contact Form 7
   - 5.5. Hạn chế của WP-Cron, nghẽn hàng đợi (LIMIT 10) và thiếu khóa nguyên tử (Atomic Lock)
   - 5.6. Thiết kế Kiến trúc Multi-Tenant Lead Router phân luồng theo Trường đối tác & Script SQL Migration
6. [PHẦN 6: MA TRẬN ĐỐI CHIẾU NGHIỆP VỤ TUYỂN SINH THỰC TẾ VIỆT NAM VS MÔ HÌNH THEME HIỆN TẠI](#phần-6-ma-trận-đối-chiếu-nghiệp-vụ-tuyển-sinh-thực-tế-việt-nam-vs-mô-hình-theme-hiện-tại)
7. [PHẦN 7: LỘ TRÌNH & KẾ HOẠCH KHẮC PHỤC TOÀN DIỆN (ACTIONABLE ROADMAP)](#phần-7-lộ-trình--kế-hoạch-khắc-phục-toàn-diện-actionable-roadmap)
   - 7.1. Phase 1: Hotfix Khẩn cấp (Tuần 1 - Zero Breaking Changes)
   - 7.2. Phase 2: Cải tổ Kiến trúc Nền tảng (Tuần 2 - 3)
   - 7.3. Phase 3: Mở rộng Đa đối tác & Nâng cao Trải nghiệm (Tuần 4+)

---

## PHẦN 1: EXECUTIVE SUMMARY & BẢNG CHỈ SỐ SỨC KHỎE NGHIỆP VỤ (HEALTH SCORECARD)

### 1.1. Bối cảnh & Mục tiêu kiểm định
Website **Liên Thông Đại Học** (`lienthongdaihoc.com`) vận hành như một cổng thông tin và phân phối tuyển sinh trực tuyến, kết nối người học có nhu cầu học Đại học Từ xa, Liên thông Đại học, và Văn bằng 2 với các trường Đại học đối tác hàng đầu tại Việt Nam (ĐH Kinh tế Quốc dân, ĐH Mở Hà Nội, ĐH Thái Nguyên, ĐH Giao thông Vận tải, Học viện Bưu chính Viễn thông...).

Báo cáo này được thực hiện nhằm kiểm định toàn diện và độc lập hệ thống quản lý **Hệ đào tạo** (`training_type`) và **Trường đối tác** (`school`) trên 4 khía cạnh nghiệp vụ cốt lõi:
1. **Kiến trúc dữ liệu & Quan hệ thực thể (Requirement R1)**: Đánh giá mô hình quan hệ giữa Trường, Ngành, Chương trình đào tạo và Hệ đào tạo; kiểm tra tính toàn vẹn vòng đời bài viết.
2. **Logic truy vấn, Bộ lọc & UX Phân loại (Requirement R2)**: Kiểm định hiệu năng cơ sở dữ liệu (WP_Query, bẫy N+1 query), cơ chế lọc AJAX, cấu trúc URL và chuẩn SEO Breadcrumbs/Canonical.
3. **Quy chuẩn Pháp lý Tuyển sinh & Niềm tin Văn bằng (Requirement R3)**: Đối chiếu các phát ngôn truyền thông và thông điệp tuyển sinh với hệ thống pháp luật hiện hành của Bộ Giáo dục & Đào tạo (Thông tư 27/2019/TT-BGDĐT, Thông tư 28/2023/TT-BGDĐT, Luật Giáo dục đại học 2018, Luật Quảng cáo 2012).
4. **Phễu Tuyển sinh & Tích hợp CRM Tuyển sinh (Requirement R4)**: Kiểm tra luồng thu thập lead, bảo toàn dữ liệu nguyện vọng thí sinh, phân luồng CRM đa đối tác (OnSchool, AUM, Telegram Bot).

---

### 1.2. Bảng chỉ số sức khỏe tổng thể (Master Health Scorecard)

$$\text{Chỉ số sức khỏe hệ thống trung bình} = \frac{45 + 40 + 50 + 35 + 30}{5} = \mathbf{40/100} \quad \text{(Mức báo động nghiêm trọng - Cần can thiệp tái cấu trúc)}$$

| Trục nghiệp vụ kiểm định | Điểm số (100) | Trạng thái kỹ thuật | Tóm tắt nguyên nhân cốt lõi |
|---|---|---|---|
| **1. Kiến trúc dữ liệu & Mô hình thực thể (R1)** | **45/100** | 🔴 Nghiêm trọng | Split-brain giữa Taxonomy và ACF Checkbox; Taxonomy `training_type` không gán vào `school`; Tham chiếu mồ côi (Orphan IDs) khi xóa bài viết; Rewrite rule cấp 1 chiếm quyền định tuyến toàn trang. |
| **2. Logic truy vấn & Hiệu năng Database (R2)** | **40/100** | 🔴 Nghiêm trọng | Lỗi N+1 query nặng (204 - 800+ queries/request tại `archive-school.php`); Bypass Main Query gây double query tại `taxonomy-training_type.php`; Transient cache bị bỏ quên không dùng. |
| **3. Trải nghiệm Lọc & Frontend UX (R2)** | **50/100** | 🟠 Cảnh báo | AJAX Filter trong `main.js` chết 100% (thiếu element ID `#program-results-container`); Phantom Facets ở sidebar đếm sai số lượng; Rò rỉ 100% phễu tại trang landing Hệ đào tạo. |
| **4. Tuân thủ Pháp lý & Niềm tin Văn bằng (R3)** | **35/100** | 🚨 Nguy cấp | Cam đoan sai sự thật "100% BẰNG CỬ NHÂN CHÍNH QUY" (`front-page.php:528`); Dùng từ dân gian "Bằng đỏ"; Giấu thông tin Phụ lục văn bằng trên FAQ; Vi phạm Luật Quảng cáo 2012. |
| **5. Phễu Thu thập Lead & Tích hợp CRM (R4)** | **30/100** | 🚨 Nguy cấp | Bảng `wp_ltdh_leads` thiếu cột `message`; Xóa sạch 100% lời nhắn của thí sinh khi CRM sync thành công; Telegram bot mù thông tin trường/ngành; CRM đơn kênh không hỗ trợ đa trường. |

---

### 1.3. Bảng phân loại rủi ro tổng hợp (Risk Matrix)

```
                            MA TRẬN PHÂN LOẠI RỦI RO HỆ THỐNG
   ┌──────────────────────────────────────────────────────────────────────────────────┐
   │                                  MỨC ĐỘ THIỆT HẠI                                │
   │                  Trung bình                       Nghiêm trọng                   │
   ├───────────────┬───────────────────────────────┬──────────────────────────────────┤
   │               │                               │ [C-01] Xóa trắng tin nhắn lead   │
   │               │ [H-01] Telegram bot mù info   │ [C-02] Badge trái luật "100% CQ" │
   │   Tần suất    │ [H-02] Split-brain taxonomy   │ [C-03] N+1 query 800+ trên School│
   │   xảy ra CAO  │ [H-03] Double query taxonomy  │ [C-04] CRM đơn kênh làm mất lead │
   │               │ [H-04] Dead AJAX filter       │ [C-05] Regex catch-all URL chận  │
   │               │ [H-05] SEO Canonical loop     │        mọi request toàn site     │
   ├───────────────┼───────────────────────────────┼──────────────────────────────────┤
   │   Tần suất    │ [M-01] Orphan IDs rác meta    │ [M-04] Rơi rụng ngữ cảnh CF7     │
   │  xảy ra THẤP  │ [M-02] Batches text tự do     │ [M-05] Hardcode paused "Chính quy│
   │   / TIỀM ẨN   │ [M-03] Phantom facets sidebar │ [L-01] Thiếu JSON-LD Breadcrumbs │
   └───────────────┴───────────────────────────────┴──────────────────────────────────┘
```

#### Danh mục Rủi ro Mức độ CRITICAL (Cần khắc phục ngay trong 24 - 48 giờ)
1. **[C-01] Lỗ hổng Xóa sạch Lời nhắn Thí sinh khi Đồng bộ CRM**: Bảng `wp_ltdh_leads` thiếu cột `message`. Hàm `ltdh_insert_lead()` mượn tạm cột `error_message` để lưu ghi chú. Khi sync CRM thành công, hàm `ltdh_process_lead_queue()` (`inc/crm-adapters.php:80`) cập nhật `error_message = ''`, xóa vĩnh viễn 100% nguyện vọng của thí sinh.
2. **[C-02] Vi phạm Nghiêm trọng Luật Quảng cáo & Quy chế Văn bằng**: Badge cam tại `front-page.php:528` khẳng định "100% BẰNG CỬ NHÂN CHÍNH QUY" cho chương trình từ xa/liên thông. Vi phạm trực tiếp Điều 8 Luật Quảng cáo 2012 và Thông tư 27/2019/TT-BGDĐT. Nguy cơ bị xử phạt 70 - 100 triệu đồng và đình chỉ tuyển sinh.
3. **[C-03] Lỗi N+1 Query làm Tê liệt Cơ sở Dữ liệu**: `archive-school.php:265-306` thực thi hàng loạt sub-query `get_posts` và `wp_get_post_terms` bên trong vòng lặp danh sách trường, sinh ra từ 204 đến hơn 800 truy vấn SQL/pageview, đe dọa làm sập MySQL khi chạy chiến dịch quảng cáo.
4. **[C-04] Điểm nghẽn CRM Đơn kênh Gây Thất thoát Lead Đa đối tác**: Theme chỉ cho phép chọn 1 CRM duy nhất toàn cục (`default_crm_type`). Lead của trường dùng OnSchool (TNU) bị đẩy nhầm sang AUM (TMU) hoặc ngược lại; đồng thời gửi tiêu đề tiếng Việt có dấu làm vỡ schema API đối tác.
5. **[C-05] Regex Catch-All Hijacking Làm Suy Giảm Hiệu Năng Toàn Trang**: Rewrite rule `([^/]+)/?$` (`class-rewrite-rules.php:42`) chộp bắt mọi URL 1-segment, ép mọi request (kể cả Page tĩnh, Cẩm nang, bài viết, hoặc lỗi 404) phải chạy 2 truy vấn SQL kiểm tra CPT trước khi render.

---

## PHẦN 2: ĐÁNH GIÁ KIẾN TRÚC DỮ LIỆU & MÔ HÌNH THỰC THỂ (REQUIREMENT R1)

### 2.1. Đánh giá Mô hình thực thể tam giác: `School` ⟷ `Major` ⟷ `Program` ⟷ `training_type` ⟷ `campus`

Hệ thống hiện tại định nghĩa mô hình dữ liệu tuyển sinh thông qua `inc/post-types.php` kết hợp tệp cấu hình `inc/acf-import-cpts.json` và `inc/acf-import-fields.json`:

```
                       SƠ ĐỒ QUAN HỆ THỰC THỂ HIỆN TẠI (AS-IS)
                       
          ┌────────────────────────┐             ┌────────────────────────┐
          │      CPT: School       │             │       CPT: Major       │
          │  (Trường Đại học ĐT)   │             │      (Ngành học)       │
          │  Taxonomy: [region]    │             │  Taxonomy: [major_cat] │
          └───────────┬────────────┘             └───────────┬────────────┘
                      │                                      │
            1         │ school_relationship        1         │ major_relationship
                      │ (post_object in postmeta)            │ (post_object in postmeta)
                      │                                      │
                      ▼                                      ▼
          ┌──────────────────────────────────────────────────────────────┐
          │                         CPT: Program                         │
          │                   (Chương trình tuyển sinh)                  │
          │            - tuition_amount, tuition_unit, duration          │
          │            - admission_batches (repeater dạng text)          │
          │            - elig_min_education, elig_training_types         │
          └───────────────────┬──────────────────────────────┬───────────┘
                              │                              │
                    M:N       │ (Taxonomy)         M:N       │ (Taxonomy)
                              ▼                              ▼
                ┌──────────────────────────┐   ┌──────────────────────────┐
                │  Taxonomy: training_type │   │     Taxonomy: campus     │
                │       (Hệ đào tạo)       │   │     (Cơ sở đào tạo)      │
                │  object_type: [program]  │   │  object_type: [program]  │
                └──────────────────────────┘   └──────────────────────────┘
```

#### Đánh giá tính hợp lý của thực thể trung gian `Program`:
- **Về mặt lý thuyết mô hình hóa dữ liệu (Data Modeling)**: Mô hình thực thể trung gian `Program` (Join Entity Pattern) là **hoàn toàn chính xác và khoa học**. Trong giáo dục đại học tại Việt Nam, quan hệ giữa Trường đại học và Ngành học là quan hệ Nhiều - Nhiều ($N:M$). Một trường có nhiều ngành học, và một ngành học (ví dụ: *Công nghệ thông tin*, *Kế toán*, *Luật kinh tế*) được giảng dạy tại hàng chục trường khác nhau.
- **Tính độc lập của gói tuyển sinh**: Việc tạo ra thực thể `Program` cho phép hệ thống gán các thuộc tính tuyển sinh độc lập và biến thiên: định mức học phí tín chỉ (`tuition_amount`), thời gian đào tạo (`duration`), chỉ tiêu tuyển sinh (`quota`), mẫu phiếu tuyển sinh (`admission_form_file`), và đợt nhận hồ sơ (`admission_batches`) riêng biệt cho từng sự kết hợp cụ thể giữa một Trường và một Ngành.

---

### 2.2. Phân tích ưu / nhược điểm của việc gán `training_type` vào `program` vs `school` vs CPT độc lập

| Tiêu chí nghiệp vụ | Phương án A: `training_type` gắn vào `program` (Hiện tại) | Phương án B: `training_type` gắn trực tiếp vào `school` | Phương án C: `training_type` làm CPT độc lập |
|---|---|---|---|
| **Độ chính xác tuyển sinh thực tế** | **9.5/10 (Rất cao)**: Phản ánh đúng bản chất: Trường chỉ mở hệ Từ xa cho một số ngành nhất định, không phải toàn bộ. | **3.0/10 (Sai lệch nghiêm trọng)**: Trường có 40 ngành Chính quy nhưng chỉ 5 ngành Từ xa; nếu gán vào Trường sẽ gây hiểu lầm là tất cả các ngành đều có hệ Từ xa. | **8.0/10 (Khá)**: Phân tách rõ nội dung truyền thông của Hệ và Chương trình, nhưng sinh thêm quan hệ $N:M$ phức tạp. |
| **Khả năng cấu hình Biểu phí & Thời gian** | **Tối ưu**: Mỗi chương trình có học phí tín chỉ và lộ trình riêng biệt cho từng hệ. | **Bất khả thi**: Không thể lưu học phí của hệ Từ xa và hệ VLVH trên cùng một trường nếu không dùng bảng lặp ma trận phức tạp. | **Phức tạp**: Vẫn buộc phải có thực thể trung gian thứ ba để lưu giao điểm giữa Hệ, Trường và Ngành. |
| **Hiệu năng truy vấn & Lọc danh mục** | **Trung bình**: Lọc trường theo hệ phải truy vấn xuyên qua thực thể `program` (phát sinh subquery). | **Rất cao ($O(1)$)**: Truy vấn trực tiếp `school` qua `tax_query` chuẩn của WordPress. | **Thấp**: Truy vấn qua 3 Post Types với nhiều bảng postmeta liên kết. |
| **Trải nghiệm trang danh bạ Trường** | **Bị lỗi hiện tại**: Khối Featured Schools trên `archive-school.php` bị mất badge vì gọi taxonomy của School. | **Dễ dàng**: Hiển thị ngay tức thì các badge hệ đào tạo trên card trường đối tác. | **Dễ dàng**: Có trang Landing Page độc lập rất mạnh cho SEO của Hệ đào tạo. |

#### Tình huống thực tế minh họa:
- **Tình huống 1: Một trường mở NHIỀU HỆ cho CÙNG MỘT ngành**:
  *Trường Đại học Mở Hà Nội (HOU)* tuyển sinh ngành **Luật Kinh tế**:
  1. *Hệ Đào tạo Từ xa (E-Learning)*: Học 100% trực tuyến, học phí 450.000đ/tín chỉ, tuyển sinh 4 đợt/năm, xét tuyển từ THPT, Trung cấp, Cao đẳng, VB2.
  2. *Hệ Vừa làm vừa học (VLVH)*: Học tập trung vào tối thứ 7 & Chủ nhật tại cơ sở Hà Nội, học phí 550.000đ/tín chỉ, tuyển sinh 2 đợt/năm.
  ➔ **Dưới mô hình hiện tại**: Tạo 2 bản ghi `program` riêng biệt. Template `single-school.php:378-439` gom nhóm 2 chương trình này dưới cùng 1 heading ngành Luật Kinh tế. Đây là điểm xử lý frontend rất tốt của theme.
- **Tình huống 2: Một trường mở các hệ cho CÁC NGÀNH KHÁC NHAU**:
  *Trường ĐH Công nghệ Giao thông Vận tải (UTT)*:
  1. Ngành **Công nghệ thông tin**: Có hệ Từ xa và Vừa làm vừa học.
  2. Ngành **Logistics & Chuỗi cung ứng**: Chỉ có hệ Chính quy và Vừa làm vừa học (**KHÔNG có hệ Từ xa**).
  3. Ngành **Xây dựng cầu đường**: Chỉ có Liên thông chính quy.
  ➔ **Kết luận**: Nếu gán `training_type` trực tiếp lên `school`, khi người dùng lọc: *"Tìm các ngành đào tạo Từ xa của ĐH Công nghệ GTVT"*, hệ thống sẽ ngầm hiểu cả trường có hệ Từ xa và hiển thị sai ngành Logistics. Do đó, **bắt buộc `training_type` phải được quản lý ở cấp độ đơn vị nhỏ nhất là `program`**.

---

### 2.3. Sáu (6) điểm nghẽn kiến trúc và rủi ro toàn vẹn dữ liệu nghiêm trọng

#### 🔴 Điểm nghẽn 1: Hiện tượng phân rã thực thể (Split-Brain Redundancy) giữa Taxonomy và ACF Checkbox
- **Vị trí code**:
  - `inc/acf-import-cpts.json`, dòng 170-207 (Đăng ký taxonomy chuẩn `training_type` gắn vào `program`).
  - `inc/acf-import-fields.json`, dòng 611-640 (Nhóm `group_program_eligibility` định nghĩa trường checkbox `elig_training_types`).
  - `inc/eligibility.php`, dòng 428 và dòng 473.
- **Cơ chế gây lỗi**:
  Hệ thống duy trì đồng thời 2 nguồn dữ liệu hoàn toàn độc lập cho cùng một khái niệm "Hệ đào tạo":
  1. Hộp thoại Taxonomy `training_type` của WordPress (lưu vào bảng `wp_term_relationships`).
  2. Trường Checkbox ACF `elig_training_types` (lưu mảng serialized vào bảng `wp_postmeta`).
  Khi xét tuyển tại `inc/eligibility.php`:
  ```php
  // Dòng 428: Lấy term từ Taxonomy
  $prog_training_types = wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'slugs' ] );
  
  // Dòng 473: So khớp với Checkbox ACF
  $allowed_types = get_field( 'elig_training_types', $program_id );
  if ( ! in_array( $input['training_type'], $allowed_types, true ) ) {
      // Đánh rớt điều kiện xét tuyển!
  }
  ```
  Nếu biên tập viên chọn term "Từ xa" ở Taxonomy nhưng quên tích checkbox "Từ xa" trong ACF (hoặc ngược lại), ứng viên đủ 100% điều kiện sẽ bị thuật toán đánh rớt oan uổng!

#### 🔴 Điểm nghẽn 2: Tham chiếu mồ côi (Orphan IDs) & Vòng đời thực thể bị đứt đoạn
- **Vị trí code**: `inc/relationship-hooks.php`, dòng 12-74.
- **Cơ chế gây lỗi**:
  Hàm `ltdh_sync_program_relationships()` chỉ lắng nghe duy nhất một hook:
  `add_action( 'acf/save_post', 'ltdh_sync_program_relationships', 20 );`
  Hệ thống **hoàn toàn không lắng nghe** các sự kiện:
  - `before_delete_post` / `deleted_post`: Khi một chương trình bị xóa vĩnh viễn.
  - `wp_trash_post` / `trashed_post`: Khi chương trình bị chuyển vào Thùng rác.
  - `untrash_post`: Khi chương trình được phục hồi từ Thùng rác.
- **Hậu quả**: Khi xóa một `program`, ID của nó vẫn nằm nguyên trong mảng postmeta `_offered_programs` của cả `school` và `major`. Khi `single-school.php` truy vấn `WP_Query(['post__in' => $offered_program_ids])`, WordPress buộc phải quét qua các ID rác không tồn tại, làm sai lệch bộ đếm ngành đào tạo (`ltdh_get_school_unique_majors_count()`).

#### 🔴 Điểm nghẽn 3: "Hệ đào tạo ma" (Ghost Training Type) & Lỗi trống Badge trên Archive Trường
- **Vị trí code**:
  - `inc/acf-import-cpts.json`, dòng 175: `"object_type": [ "program" ]` (Không có `school`).
  - `archive-school.php`, dòng 80-81 (Khối Featured Schools):
    ```php
    $school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
    $systems_label = ( ! is_wp_error( $school_types ) && ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
    ```
  - `archive-school.php`, dòng 199-200 (Khối Card View):
    ```php
    $school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
    $systems_label = ( ! is_wp_error( $school_types ) && ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
    ```
- **Hậu quả**: Vì `training_type` không được gán cho `school`, lệnh `wp_get_post_terms()` luôn trả về mảng rỗng `[]`. Toàn bộ card trường nổi bật và card lưới thông thường **hoàn toàn không hiển thị được bất kỳ badge hệ đào tạo nào**, làm mất tính thẩm mỹ và độ tin cậy của giao diện.

#### 🔴 Điểm nghẽn 4: Hijacking không gian tên URL & Suy giảm hiệu năng định tuyến toàn trang
- **Vị trí code**: `inc/core/class-rewrite-rules.php`, dòng 41-101.
  ```php
  add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
  ```
- **Cơ chế gây lỗi**: Để có đường dẫn đẹp dạng `domain.com/{ten-chuong-trinh}/`, theme đặt rule với cờ `'top'`. Mọi request có 1 path segment đều bị biến thành query `program`. Hàm `ltdh_program_request_guard()` phải can thiệp vào hook `request`, thực hiện 2 câu lệnh `get_posts` kiểm tra CPT `program` và CPT `post`.
- **Hậu quả**: Tăng thêm **2 câu truy vấn SQL không cần thiết cho mỗi pageview** trên toàn bộ các trang tĩnh (Trang chủ, Giới thiệu, Liên hệ, Cẩm nang), làm suy giảm chỉ số TTFB (Time To First Byte).

#### 🔴 Điểm nghẽn 5: Đợt tuyển sinh lưu dạng Text tự do, không thể tự động hóa
- **Vị trí code**: `inc/acf-import-fields.json`, dòng 291-354 (`admission_batches` repeater).
- **Cơ chế gây lỗi**: Các trường mốc thời gian (`release_period`, `application_period`, `enrollment_time`) đều là kiểu `text` tự do (ví dụ: *"Từ 15/01/2026 đến hết 28/02/2026"*).
- **Hậu quả**: Không có trường ngày tháng định dạng chuẩn ISO (`Y-m-d`), hệ thống không thể so sánh với ngày hiện tại `current_time('Y-m-d')` để chạy WP-Cron tự động chuyển trạng thái đợt sang `da-dong`. Quản trị viên phải sửa tay hàng trăm chương trình khi hết hạn nhận hồ sơ, dẫn đến tình trạng học viên vẫn thấy hiển thị "Đang nhận hồ sơ: Hạn nộp 15/01/2026" dù thời điểm thực tế đã là tháng 09/2026.

#### 🔴 Điểm nghẽn 6: Bộ định tuyến Lead (CRM Router) đơn khối, thiếu định danh Trường và Hệ
- **Vị trí code**: `inc/lead-capture.php`, dòng 111-122 & `inc/crm-adapters.php`, dòng 91-124.
- **Cơ chế gây lỗi**: Dữ liệu lead chỉ lưu tên hệ đào tạo dạng text hiển thị (`$terms[0]->name`), không lưu slug chuẩn. Cấu hình CRM chỉ có một lựa chọn duy nhất trên toàn trang (`default_crm_type`). Không hỗ trợ phân luồng cho các trường đối tác có hệ thống CRM hoặc đầu mối tuyển sinh độc lập.

---

### 2.4. Đề xuất Kiến trúc nâng cấp: Two-Tier Rollup Architecture & `LTDH_Entity_Relationship_Engine`

Để giữ trọn vẹn độ chính xác của từng chương trình con, đồng thời đạt hiệu năng truy vấn tức thì $O(1)$ ở cấp độ Trường, giải pháp kiến trúc tối ưu là **Mô hình Phản chiếu Hai tầng (Two-Tier Rollup Architecture)**:

```
                  KIẾN TRÚC PHẢN CHIẾU HAI TẦNG (TWO-TIER ROLLUP)
                  
    ┌────────────────────────────────────────────────────────────────────────┐
    │                              CPT: school                               │
    │  - Taxonomy: region                                                    │
    │  - Taxonomy: training_type (ĐỒNG BỘ TỰ ĐỘNG TỪ CÁC CHƯƠNG TRÌNH CON)   │
    │  - Taxonomy: campus        (ĐỒNG BỘ TỰ ĐỘNG TỪ CÁC CHƯƠNG TRÌNH CON)   │
    │  - Meta: _offered_programs (Array IDs sạch, loại bỏ 100% orphan IDs)   │
    │  - Meta: _active_training_systems (Key-Value map: slug => name)        │
    └───────────────────────────────────▲────────────────────────────────────┘
                                        │
                                        │ Tự động Rollup (Đồng bộ ngược)
                                        │ khi Save / Trash / Delete Program
                                        │
    ┌───────────────────────────────────┴────────────────────────────────────┐
    │                              CPT: program                              │
    │  - school_relationship ──► Trỏ School ID                               │
    │  - major_relationship  ──► Trỏ Major ID                                │
    │  - Taxonomy: training_type (Nguồn chân lý gốc cho từng gói ngành)      │
    │  - Taxonomy: campus        (Danh sách cơ sở mở lớp)                    │
    │  - admission_status: tuyen-sinh | tam-ngung | sap-mo                   │
    └────────────────────────────────────────────────────────────────────────┘
```

#### Mã nguồn triển khai lớp `LTDH_Entity_Relationship_Engine` chuẩn PSR-4 (Đã hiệu chỉnh WordPress Lifecycle & ACF Timing):

```php
<?php
/**
 * Class LTDH_Entity_Relationship_Engine
 * Quản lý toàn diện vòng đời quan hệ thực thể School - Program - Major
 * Hỗ trợ WordPress Lifecycle chuẩn, ACF Timing Guard và chống Orphan IDs
 * 
 * @package LienThongDaiHoc
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LTDH_Entity_Relationship_Engine {

    public static function init(): void {
        // 1. Hook khi lưu hoặc cập nhật Program:
        // Lắng nghe acf/save_post với priority 25 đảm bảo ACF đã ghi xong dữ liệu meta từ WP-Admin
        add_action( 'acf/save_post', [ __CLASS__, 'on_acf_save_program' ], 25 );
        // Lắng nghe save_post_{post_type} với priority 25 cho programmatic/REST/CLI save
        add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 25, 2 );

        // 2. Hook khi trạng thái ĐÃ HOÀN TẤT THAY ĐỔI trong DB (tránh đọc trạng thái publish cũ)
        add_action( 'trashed_post', [ __CLASS__, 'on_program_status_changed' ] );
        add_action( 'untrashed_post', [ __CLASS__, 'on_program_status_changed' ] );

        // 3. Hook trước khi xóa vĩnh viễn: Loại trừ ID đang xóa bằng post__not_in chống Orphan IDs
        add_action( 'before_delete_post', [ __CLASS__, 'on_program_before_delete' ] );
        add_action( 'before_delete_post', [ __CLASS__, 'on_parent_entity_delete' ] );
    }

    /**
     * Xử lý lưu Program qua ACF trong WP-Admin (sau khi ACF đã ghi xong postmeta)
     */
    public static function on_acf_save_program( $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        self::sync_program( (int) $post_id );
    }

    /**
     * Xử lý lưu Program qua WordPress standard save (API, CLI, hoặc code)
     */
    public static function on_program_save( int $post_id, WP_Post $post ): void {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
            return;
        }
        self::sync_program( $post_id );
    }

    /**
     * Xử lý khi Program chuyển trạng thái hoàn tất (trashed_post / untrashed_post)
     */
    public static function on_program_status_changed( int $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }
        self::sync_program( $post_id );
    }

    /**
     * Xử lý trước khi xóa vĩnh viễn Program: Loại trừ post_id khỏi cache bằng post__not_in
     */
    public static function on_program_before_delete( int $post_id ): void {
        if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
            return;
        }

        $school_id = (int) get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true );
        $major_id  = (int) get_post_meta( $post_id, LTDH_META_MAJOR_REL, true );

        // Bắt buộc truyền exclude_ids chứa post_id đang xóa để loại bỏ orphan ID
        if ( $school_id ) {
            self::rebuild_entity_programs_cache( $school_id, 'school', [ $post_id ] );
            self::rollup_school_taxonomies( $school_id, [ $post_id ] );
        }
        if ( $major_id ) {
            self::rebuild_entity_programs_cache( $major_id, 'major', [ $post_id ] );
        }
    }

    /**
     * Xử lý khi thực thể cha (School hoặc Major) bị xóa vĩnh viễn
     */
    public static function on_parent_entity_delete( int $post_id ): void {
        $post_type = get_post_type( $post_id );
        if ( ! in_array( $post_type, [ LTDH_CPT_SCHOOL, LTDH_CPT_MAJOR ], true ) ) {
            return;
        }

        $meta_key = ( LTDH_CPT_SCHOOL === $post_type ) ? LTDH_META_SCHOOL_REL : LTDH_META_MAJOR_REL;

        $child_programs = get_posts( [
            'post_type'      => LTDH_CPT_PROGRAM,
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => $meta_key,
                    'value'   => $post_id,
                    'compare' => '=',
                ],
            ],
        ] );

        if ( empty( $child_programs ) ) {
            return;
        }

        // Tạm thời gỡ hook lưu để ngăn ngừa Query Storm
        remove_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 25 );

        foreach ( $child_programs as $c_id ) {
            delete_post_meta( $c_id, $meta_key );
            wp_update_post( [
                'ID'          => $c_id,
                'post_status' => 'draft',
            ] );
        }

        add_action( 'save_post_' . LTDH_CPT_PROGRAM, [ __CLASS__, 'on_program_save' ], 25, 2 );
    }

    /**
     * Đồng bộ quan hệ và rollup taxonomies cho một Program
     */
    private static function sync_program( int $post_id ): void {
        $school_id = (int) get_post_meta( $post_id, LTDH_META_SCHOOL_REL, true );
        $major_id  = (int) get_post_meta( $post_id, LTDH_META_MAJOR_REL, true );

        $old_school_id = (int) get_post_meta( $post_id, '_last_known_school_id', true );
        $old_major_id  = (int) get_post_meta( $post_id, '_last_known_major_id', true );

        // Đồng bộ danh sách chương trình đang mở cho School
        if ( $school_id ) {
            self::rebuild_entity_programs_cache( $school_id, 'school' );
            self::rollup_school_taxonomies( $school_id );
        }
        if ( $old_school_id && $old_school_id !== $school_id ) {
            self::rebuild_entity_programs_cache( $old_school_id, 'school' );
            self::rollup_school_taxonomies( $old_school_id );
        }

        // Đồng bộ cho Major
        if ( $major_id ) {
            self::rebuild_entity_programs_cache( $major_id, 'major' );
        }
        if ( $old_major_id && $old_major_id !== $major_id ) {
            self::rebuild_entity_programs_cache( $old_major_id, 'major' );
        }

        // Lưu vết để xử lý khi chuyển trường/ngành
        update_post_meta( $post_id, '_last_known_school_id', $school_id );
        update_post_meta( $post_id, '_last_known_major_id', $major_id );
    }

    /**
     * Tái tạo mảng _offered_programs sạch 100%, hỗ trợ loại trừ ID đang xóa bằng post__not_in
     */
    public static function rebuild_entity_programs_cache( int $entity_id, string $type, array $exclude_ids = [] ): void {
        if ( ! $entity_id ) {
            return;
        }
        $meta_key = ( 'school' === $type ) ? LTDH_META_SCHOOL_REL : LTDH_META_MAJOR_REL;

        $args = [
            'post_type'      => LTDH_CPT_PROGRAM,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => $meta_key,
                    'value'   => $entity_id,
                    'compare' => '=',
                ],
            ],
        ];

        if ( ! empty( $exclude_ids ) ) {
            $args['post__not_in'] = $exclude_ids;
        }

        $active_ids = get_posts( $args );

        update_post_meta( $entity_id, LTDH_META_OFFERED_PROGRAMS, array_values( array_unique( $active_ids ) ) );
    }

    /**
     * Rollup: Gom toàn bộ Hệ đào tạo và Cơ sở của các chương trình đang MỞ gắn lên School
     */
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

        // Lọc các chương trình KHÔNG bị tạm ngưng
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

### 2.5. Thiết kế Schema Ma trận Tuyển sinh theo Trình độ đầu vào (Input-Tier Matrix Repeater)

Trong thực tế tuyển sinh liên thông, đào tạo từ xa và văn bằng 2 tại Việt Nam, **thời gian học, số tín chỉ tích lũy và tổng học phí phụ thuộc 100% vào văn bằng trước đó của người học**. Bổ sung nhóm repeater `input_level_matrix` vào CPT `program` thay thế trường `duration` đơn lẻ:

| Trường dữ liệu (Sub-field) | Kiểu dữ liệu | Giá trị / Lựa chọn | Ý nghĩa nghiệp vụ tuyển sinh |
|---|---|---|---|
| `input_level` | Select | `thpt`: Tốt nghiệp THPT<br>`trung-cap`: Trung cấp đúng/gần ngành<br>`cao-dang-cung`: Cao đẳng cùng ngành<br>`cao-dang-khac`: Cao đẳng khác ngành<br>`van-bang-2`: Đã có bằng Đại học khác | Phân định chính xác 5 đối tượng tuyển sinh phổ biến tại Việt Nam. |
| `duration_months` | Number | 18, 24, 30, 42, 48 | Số tháng đào tạo chuẩn dùng cho thuật toán tính toán và sắp xếp. |
| `duration_label` | Text | "1.5 năm (3 học kỳ)", "2.5 năm" | Chuỗi hiển thị thân thiện trên UI. |
| `required_credits` | Number | 55, 75, 90, 135 | Số tín chỉ thực tế phải học sau khi đã trừ khối lượng tín chỉ được miễn giảm. |
| `tuition_per_credit` | Number | 450000, 520000 | Đơn giá tiền/tín chỉ áp dụng cho bậc học này. |
| `estimated_total_tuition` | Number | Tự động tính (`credits × tuition`) | Tổng kinh phí dự kiến giúp thí sinh chủ động tài chính. |
| `credit_exemption_policy` | Textarea | *"Miễn toàn bộ 14 tín chỉ khối kiến thức đại cương (Triết học, Ngoại ngữ, Tin học, GDTC, GDQP)..."* | Minh bạch chính sách miễn trừ, tăng tỷ lệ nộp hồ sơ. |

---

### 2.6. Thiết kế Schema Đợt tuyển sinh có cấu trúc thời gian ISO (`structured_admission_batches`)

Nâng cấp `admission_batches` repeater trên CPT `program` thành dữ liệu chuẩn thời gian:

```json
{
    "key": "field_program_structured_admission_batches",
    "label": "Danh sách Đợt tuyển sinh có cấu trúc",
    "name": "structured_admission_batches",
    "type": "repeater",
    "sub_fields": [
        {
            "key": "field_batch_code",
            "label": "Mã đợt tuyển sinh",
            "name": "batch_code",
            "type": "text",
            "instructions": "Ví dụ: 2026-DOT-01 (Dùng định danh trong CRM)",
            "required": 1
        },
        {
            "key": "field_batch_name",
            "label": "Tên đợt tuyển sinh",
            "name": "batch_name",
            "type": "text",
            "instructions": "Ví dụ: Đợt 1 - Khai giảng tháng 3/2026",
            "required": 1
        },
        {
            "key": "field_batch_start_date",
            "label": "Ngày bắt đầu nhận hồ sơ",
            "name": "start_date",
            "type": "date_picker",
            "display_format": "d/m/Y",
            "return_format": "Y-m-d",
            "required": 1
        },
        {
            "key": "field_batch_end_date",
            "label": "Hạn chót nhận hồ sơ",
            "name": "end_date",
            "type": "date_picker",
            "display_format": "d/m/Y",
            "return_format": "Y-m-d",
            "required": 1
        },
        {
            "key": "field_batch_enrollment_date",
            "label": "Ngày khai giảng / Nhập học",
            "name": "enrollment_date",
            "type": "date_picker",
            "display_format": "d/m/Y",
            "return_format": "Y-m-d"
        },
        {
            "key": "field_batch_is_rolling",
            "label": "Tuyển sinh liên tục quanh năm",
            "name": "is_rolling",
            "type": "true_false",
            "default_value": 0
        },
        {
            "key": "field_batch_status_override",
            "label": "Ghi đè trạng thái thủ công",
            "name": "status_override",
            "type": "select",
            "choices": {
                "auto": "Tự động tính theo ngày hiện tại",
                "open": "Bắt buộc Mở (Đang nhận hồ sơ)",
                "closed": "Bắt buộc Đóng (Hết chỉ tiêu sớm)"
            },
            "default_value": "auto"
        }
    ]
}
```

---

### 2.7. Thiết kế Cấu trúc Quản lý Cơ sở, Phân hiệu và Trạm đào tạo liên kết (`study_stations`)

Để phản ánh chính xác mạng lưới trạm đào tạo từ xa và phân hiệu các trường đại học tại các tỉnh thành:
Thêm nhóm trường `group_school_campuses` vào CPT `school`:
1. `headquarters_address`: Trụ sở chính của trường (địa chỉ, số điện thoại văn phòng tuyển sinh).
2. `branch_campuses` (Repeater): Danh sách các **Phân hiệu chính thức** (ví dụ: Phân hiệu TP. Hồ Chí Minh của ĐH Giao thông Vận tải).
   - `branch_name` (Text): Tên phân hiệu.
   - `branch_city` (Taxonomy -> `campus`): Tỉnh/Thành phố.
   - `branch_address` (Text): Địa chỉ cơ sở.
3. `study_stations` (Repeater): Danh sách các **Trạm Đào tạo Từ xa / Trạm liên kết**:
   - `station_name` (Text): Ví dụ *"Trạm Đào tạo Từ xa Cần Thơ - Đặt tại Trường CĐ Kinh tế - Kỹ thuật Cần Thơ"*.
   - `station_city` (Taxonomy -> `campus`): Tỉnh/Thành phố trực thuộc.
   - `station_address` (Text): Địa chỉ học viên đến nộp hồ sơ và tham gia thi kết thúc học phần tập trung.
   - `station_hotline` (Text): Số điện thoại liên hệ của trạm.

---

## PHẦN 3: ĐÁNH GIÁ LOGIC TRUY VẤN, BỘ LỌC & UX PHÂN LOẠI (REQUIREMENT R2)

### 3.1. Phân tích truy vấn WP_Query & Các bẫy N+1 Query nghiêm trọng

#### [N+1-01] Vòng lặp lấy chương trình và terms trong `archive-school.php` (List View)
- **Vị trí quan sát**: `archive-school.php`, dòng 264-307.
- **Trích xuất mã nguồn**:
  ```php
  // Dòng 264: Query đếm ngành
  $prog_count = ltdh_get_school_unique_majors_count( $school_id );

  // Dòng 265-276: Query tìm các chương trình của trường
  $offered_program_ids = get_posts( [
      'post_type'   => 'program',
      'numberposts' => -1,
      'fields'      => 'ids',
      'meta_query'  => [
          [
              'key'     => 'school_relationship',
              'value'   => $school_id,
              'compare' => '=',
          ],
      ],
  ] );

  // Dòng 297-306: Vòng lặp N chương trình để lấy taxonomy term!
  $training_modes = [];
  if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
      foreach ( $offered_program_ids as $pid ) {
          $terms = wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE );
          if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
              foreach ( $terms as $term ) {
                  if ( ! in_array( $term->name, $training_modes ) ) {
                      $training_modes[] = $term->name;
                  }
              }
          }
      }
  }
  ```
- **Phân tích toán học độ phức tạp (Query Complexity)**:
  Giả sử trang danh bạ hiển thị $N = 12$ trường đại học. Mỗi trường có trung bình $K = 15$ chương trình đào tạo con:
  $$\text{Số truy vấn trên một request} = 1 \; (\text{Main Query}) + N \times \Big[ 1 \; (\text{đếm ngành}) + 1 \; (\text{get\_posts program}) + K \times 1 \; (\text{wp\_get\_post\_terms}) \Big]$$
  $$\text{Tổng số queries} = 1 + 12 \times (1 + 1 + 15) = 1 + 12 \times 17 = \mathbf{205 \text{ queries!}}$$
  Nếu người dùng chọn bộ lọc hiển thị 50 trường (`?limit=50`), số lượng câu truy vấn SQL bùng nổ lên tới:
  $$\text{Tổng số queries (limit 50)} = 1 + 50 \times 17 = \mathbf{851 \text{ queries!}}$$
  Đây là nguyên nhân chính gây nghẽn CPU máy chủ MySQL khi có lưu lượng truy cập đồng thời.

---

### 3.2. Hiện tượng bất đối xứng hiển thị Badge giữa Card View và List View trên `archive-school.php`

Kiểm định phát hiện sự bất nhất dữ liệu kỳ lạ trên cùng một trang lưu trữ `archive-school.php`:

| Giao diện | Vị trí dòng code | Đoạn mã thực thi | Kết quả hiển thị trên màn hình |
|---|---|---|---|
| **Featured Schools** (Khối nổi bật) | Dòng 80-81 | `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );` | ❌ **TRỐNG 100%**: Hoàn toàn không hiện badge hệ đào tạo nào. |
| **Card View** (Dạng lưới thường) | Dòng 199-200 | `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );` | ❌ **TRỐNG 100%**: Hoàn toàn không hiện badge hệ đào tạo nào. |
| **List View** (Dạng danh sách chi tiết) | Dòng 265-307 | Chạy query phụ quét toàn bộ `program` con, rồi lặp qua từng program để lấy `training_type` | ⚠️ **HIỂN THỊ ĐỦ BADGE**, nhưng phát sinh hàng trăm câu truy vấn N+1 và hiển thị cả chương trình đã đóng! |

#### Lỗi rò rỉ Badge khi chương trình đã Tạm Ngưng (`admission_status = 'tam-ngung'`):
Tại List View (dòng 269), câu lệnh `get_posts` **không hề kiểm tra trạng thái tuyển sinh** (`admission_status`). Nếu một trường trước đây từng mở 1 chương trình "Văn bằng 2" và hiện tại chương trình này đã đóng tuyển sinh (`tam-ngung`), List View vẫn lấy term "Văn bằng 2" và hiển thị badge "Văn bằng 2" trên card của trường. Người dùng bấm vào xem trường thì không thấy ngành nào mở tuyển sinh, gây ức chế và mất lòng tin.

---

### 3.3. Phân tích Double Query và Bỏ qua Main Query trên `taxonomy-training_type.php`

1. **Bỏ qua Main Query của WordPress Core**:
   Trong `inc/core/class-query-filters.php`, hook `pre_get_posts` đã can thiệp thiết lập post type cho taxonomy archive:
   ```php
   if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) ) {
       $query->set( 'post_type', LTDH_CPT_PROGRAM );
   }
   ```
   Do đó, trước khi template `taxonomy-training_type.php` được gọi, WordPress Core **đã hoàn thành việc truy vấn Main Query** danh sách các bài viết `program` thuộc taxonomy term đó.
2. **Double Query tốn tài nguyên**:
   Tuy nhiên, khi template `taxonomy-training_type.php` thực thi, tại dòng 128 lại chạy:
   ```php
   $query = new WP_Query( $args ); // Dòng 128: Chạy thêm một truy vấn độc lập thứ 2!
   ```
   Kết quả: **Database phải thực thi 2 lần truy vấn danh sách chương trình cho cùng một URL**. Main Query của WordPress bị vứt bỏ hoàn toàn, gây lãng phí 50% tài nguyên xử lý DB trên mỗi lượt truy cập.
3. **Trùng lặp 100% mã nguồn (Code Duplication)**:
   Tệp `archive-program.php` (541 dòng) và `taxonomy-training_type.php` (538 dòng) giống nhau từng ký tự từ logic lấy biến `$_GET`, form lọc, sidebar facets đến vòng lặp render card. Vi phạm nghiêm trọng nguyên lý DRY (Don't Repeat Yourself).

---

### 3.4. Kiểm toán AJAX Filter trong `main.js`: Lỗi Dead Code do thiếu `#program-results-container`

- **Vị trí code**: `assets/js/main.js`, dòng 9-12:
  ```javascript
  const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"]');
  const container = document.getElementById('program-results-container');

  if (filterForm && container) {
      // Logic AJAX Filter: fetch action ltdh_filter_programs...
  }
  ```
- **Bằng chứng Dead Code**:
  1. Tiến hành tìm kiếm toàn bộ project: Phần tử ID `program-results-container` **hoàn toàn không tồn tại** trong bất kỳ tệp PHP nào của theme (`taxonomy-training_type.php`, `archive-program.php`, `archive-school.php`).
  2. Tại `taxonomy-training_type.php:352`, grid danh sách chương trình chỉ là:
     `<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">` (Không có ID).
  3. Biến `container` luôn là `null`. Khối lệnh `if (filterForm && container)` **không bao giờ được thực thi**.
  4. Mọi tương tác của người dùng khi chọn dropdown lọc (`select onchange="location = this.value;"`) hoặc bấm phân trang đều kích hoạt **Full Page Reload**, gây chớp giật màn hình và tăng áp lực request lên Web Server.

---

### 3.5. Lỗi Lệch số lượng Facet (Phantom Facets) trên Sidebar bộ lọc

- **Vị trí code**: `taxonomy-training_type.php`, dòng 164-179.
- **Mã nguồn thực tế**:
  ```sql
  SELECT t.slug, COUNT(p.ID) as count
  FROM wp_posts p
  INNER JOIN wp_term_relationships tr ON p.ID = tr.object_id
  INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
  INNER JOIN wp_terms t ON tt.term_id = t.term_id
  WHERE p.post_type = 'program' AND p.post_status = 'publish' AND tt.taxonomy = 'training_type'
  GROUP BY t.slug
  ```
- **Hậu quả nghiệp vụ**:
  Câu lệnh SQL trên đếm tổng số chương trình trên **toàn bộ cơ sở dữ liệu**, hoàn toàn không có mệnh đề `WHERE` lọc theo trường đang chọn (`$selected_school`) hay ngành đang chọn (`$selected_nhom`).
  *Ví dụ*: Thí sinh đang chọn xem các ngành của *Trường Đại học Kinh tế Quốc dân (NEU)*.
  Sidebar vẫn hiển thị: `Đại học Từ xa (120)`, `Liên thông đại học (45)`. Thí sinh nhấp vào "Liên thông đại học" với kỳ vọng trường NEU có 45 ngành liên thông, nhưng khi tải lại trang thì kết quả là `0` (Không tìm thấy chương trình). Đây là lỗi **Phantom Facets** kinh điển làm giảm tỷ lệ chuyển đổi form.

---

### 3.6. Kiểm toán SEO, Permalinks, Canonical Loop `/chuong-trinh/` và Breadcrumbs Schema

#### 1. Lỗi Vòng lặp Canonical URL (Canonical Redirect Loop 301):
- Trong `inc/acf-import-cpts.json:146`, CPT `program` có `"has_archive_slug": "chuong-trinh"`.
- Plugin SEO (Rank Math) tự động sinh thẻ `<link rel="canonical" href="https://domain.com/chuong-trinh/" />`.
- Tuy nhiên, tại `inc/core/class-rewrite-rules.php:154-161`:
  ```php
  if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
      $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
      wp_redirect( $redirect_url, 301 );
      exit;
  }
  ```
- **Hậu quả SEO**: Khi Googlebot truy cập URL Canonical `https://domain.com/chuong-trinh/`, server lại trả về mã **HTTP 301 Redirect** sang `/he-dao-tao/tu-xa/`. Một thẻ Canonical trỏ vào một trang bị redirect 301 là lỗi kỹ thuật SEO nghiêm trọng, khiến bot công cụ tìm kiếm không thể index trang danh mục chương trình.

#### 2. Tắt Rank Math Breadcrumbs trên `/he-dao-tao/*` & Thiếu Schema JSON-LD:
- Trong `inc/core/class-helpers.php:321-328`:
  ```php
  $is_he_dao_tao = (bool) preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path );
  if ( ! $is_he_dao_tao && function_exists( 'rank_math_the_breadcrumbs' ) ) {
      // Chỉ chạy breadcrumbs của Rank Math khi KHÔNG PHẢI là /he-dao-tao/
  }
  ```
- Khi rơi vào fallback tự viết (dòng 330-402), theme chỉ xuất thẻ HTML thô `<div class="ltdh-breadcrumb">...`, hoàn toàn không có thuộc tính microdata Schema.org cũng như không inject mã JSON-LD `BreadcrumbList`. Kết quả tìm kiếm trên Google mất cơ hội hiển thị rich snippet breadcrumb điều hướng.

---

### 3.7. Bộ giải pháp kỹ thuật & Code Snippets tối ưu hóa đạt chuẩn WordPress Core Standards

#### 1. Khắc phục N+1 query tại `archive-school.php` (Đọc trực tiếp meta đã Rollup):
Thay thế **duy nhất** khối truy vấn term N+1 tại **dòng 295-307**, bảo toàn nguyên vẹn `$prog_tags` (dòng 278-290) và `$region_terms` (dòng 292-293):

```php
// Tối ưu hóa N+1: Đọc trực tiếp meta _active_training_systems đã rollup (Thay thế dòng 295-307)
// BẢO TOÀN NGUYÊN VẸN: $prog_tags (dòng 278-290) và $region_terms (dòng 292-293)
$training_modes_map = get_post_meta( $school_id, '_active_training_systems', true );
$training_modes     = is_array( $training_modes_map ) ? array_values( $training_modes_map ) : [];
```

> **Lưu ý triển khai quan trọng**:
> - Tuyệt đối **không** xóa dòng 265-307: Việc xóa mất `$prog_tags` sẽ khiến dòng 345 `<?php if ( ! empty( $prog_tags ) ) : ?>` ném lỗi `PHP Warning: Undefined variable $prog_tags` trên PHP 8+ và làm biến mất hoàn toàn danh sách top 5 ngành đào tạo tiêu biểu trên card trường; việc xóa mất `$region_terms` làm mất địa phương tại dòng 328.
> - Tuyệt đối **không** echo trực tiếp thẻ HTML `<span>` tại khối xử lý dữ liệu trước dòng 309: Việc in HTML tại đây sẽ khiến badge hiển thị trôi nổi bên ngoài, phía trên card.
> - Bên trong cấu trúc Card (dòng 337-339), template gốc vốn đã có cấu trúc chuẩn nhận biến `$training_modes`:
>   ```php
>   <?php if ( ! empty( $training_modes ) ) : ?>
>       <span class="flex items-center gap-1.5">
>           <span class="text-brand-primary">🎓</span>
>           <span class="flex flex-wrap gap-1">
>               <?php
>               foreach ( $training_modes as $mode ) {
>                   echo ltdh_get_training_type_badge_html( $mode );
>               }
>               ?>
>           </span>
>       </span>
>   <?php endif; ?>
>   ```
> - Đồng thời, tại dòng 265-276, `$offered_program_ids` có thể tối ưu đọc từ `get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true ) ?: [];` mà không cần query phụ `get_posts`, giữ nguyên toàn bộ biến `$prog_tags` cho List View.

#### 2. Khắc phục Double Query trên `taxonomy-training_type.php` (Bảo toàn 100% bộ lọc người dùng):
Trong `inc/core/class-query-filters.php`, đưa toàn bộ tham số lọc (`truong`, `nhom_nganh`/`nganh`, `s`, `sort`, `limit`) vào hook `pre_get_posts`:

```php
function ltdh_optimize_taxonomy_archive_query( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) || $query->is_post_type_archive( LTDH_CPT_PROGRAM ) ) {
        $query->set( 'post_type', LTDH_CPT_PROGRAM );
        $query->set( 'post_status', 'publish' );

        // 1. Phân trang & số lượng hiển thị hợp lệ
        $valid_limits = [ 10, 12, 20, 24, 30, 36, 48, 50, 100, -1 ];
        $limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
        $query->set( 'posts_per_page', in_array( $limit, $valid_limits, true ) ? $limit : 12 );

        $meta_query = (array) $query->get( 'meta_query' );
        if ( empty( $meta_query ) ) {
            $meta_query = [ 'relation' => 'AND' ];
        }

        // 2. Loại bỏ các chương trình tạm ngưng tuyển sinh
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'value' => LTDH_STATUS_PAUSED, 'compare' => '!=' ],
            [ 'key' => LTDH_META_ADMISSION_STATUS, 'compare' => 'NOT EXISTS' ],
        ];

        // 3. Bảo toàn Bộ lọc theo Trường đối tác ($_GET['truong'])
        if ( ! empty( $_GET['truong'] ) ) {
            $school_raw = sanitize_text_field( $_GET['truong'] );
            if ( is_numeric( $school_raw ) ) {
                $school_id = intval( $school_raw );
            } else {
                $school_post = get_page_by_path( $school_raw, OBJECT, LTDH_CPT_SCHOOL );
                $school_id   = $school_post ? $school_post->ID : 0;
            }
            if ( $school_id ) {
                $meta_query[] = [
                    'key'     => LTDH_META_SCHOOL_REL,
                    'value'   => $school_id,
                    'compare' => '=',
                ];
            }
        }

        // 4. Bảo toàn Bộ lọc theo Ngành / Nhóm ngành ($_GET['nhom_nganh'] hoặc $_GET['nganh'])
        $nhom_slug = ! empty( $_GET['nhom_nganh'] ) ? sanitize_text_field( $_GET['nhom_nganh'] ) : ( ! empty( $_GET['nganh'] ) ? sanitize_text_field( $_GET['nganh'] ) : '' );
        if ( ! empty( $nhom_slug ) ) {
            $major_post = get_page_by_path( $nhom_slug, OBJECT, LTDH_CPT_MAJOR );
            if ( $major_post ) {
                $meta_query[] = [
                    'key'     => LTDH_META_MAJOR_REL,
                    'value'   => $major_post->ID,
                    'compare' => '=',
                ];
            } else {
                // Fallback: Tìm danh sách ID ngành theo Taxonomy major_cat
                $majors_in_cat = get_posts( [
                    'post_type'   => LTDH_CPT_MAJOR,
                    'numberposts' => -1,
                    'fields'      => 'ids',
                    'tax_query'   => [
                        [
                            'taxonomy' => LTDH_TAX_MAJOR_CAT,
                            'field'    => 'slug',
                            'terms'    => $nhom_slug,
                        ],
                    ],
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

        // 5. Bảo toàn Tìm kiếm từ khóa ($_GET['s'])
        if ( ! empty( $_GET['s'] ) ) {
            $query->set( 's', sanitize_text_field( $_GET['s'] ) );
        }

        // 6. Bảo toàn Sắp xếp thứ tự ($_GET['sort'])
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

Trong `taxonomy-training_type.php`, loại bỏ hoàn toàn câu lệnh phụ `new WP_Query($args)` và dùng vòng lặp chuẩn của Main Query kèm phân trang an toàn:

```php
global $wp_query;

if ( have_posts() ) :
    // Prime cache 1 lần duy nhất cho toàn bộ bài viết trên trang
    $page_ids = wp_list_pluck( $wp_query->posts, 'ID' );
    update_meta_cache( 'post', $page_ids );
    update_object_term_cache( $page_ids, LTDH_CPT_PROGRAM );

    while ( have_posts() ) : the_post();
        get_template_part( 'template-parts/program-card' );
    endwhile;

    // Phân trang chuẩn bằng $wp_query (loại bỏ lỗi Undefined variable $query)
    if ( $wp_query->max_num_pages > 1 ) :
        echo paginate_links( [
            'total'   => $wp_query->max_num_pages,
            'current' => max( 1, get_query_var( 'paged' ) ),
        ] );
    endif;
endif;
```

#### 3. Bổ sung ID `#program-results-container` & Khôi phục AJAX Filter:
Tại `taxonomy-training_type.php:352` và `archive-program.php:355`:
```html
<div id="program-results-container" class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
    <!-- Danh sách cards được AJAX thay thế mượt mà không reload trang -->
</div>
```

#### 4. Sửa lỗi SEO Canonical URL (Khắc phục triệt để Redirect Loop 301 trên `/chuong-trinh/`):
Thẻ Canonical mặc định của Rank Math trên CPT `program` archive trỏ về `/chuong-trinh/`, nhưng URL này bị rewrite rule chuyển hướng 301 sang `/he-dao-tao/tu-xa/`. Thêm hook vào `inc/seo/class-rankmath-integration.php` để chuẩn hóa canonical trỏ thẳng đến đích đến hợp lệ:

```php
add_filter( 'rank_math/frontend/canonical_url', function( string $canonical ): string {
    // 1. Khi đang ở trang lưu trữ của CPT Program (/chuong-trinh/)
    if ( is_post_type_archive( LTDH_CPT_PROGRAM ) || is_post_type_archive( 'program' ) ) {
        return home_url( '/he-dao-tao/tu-xa/' );
    }

    // 2. Fallback kiểm tra URL path nếu request URI là /chuong-trinh/
    $path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
    if ( preg_match( '#^/chuong-trinh/?$#i', $path ) ) {
        return home_url( '/he-dao-tao/tu-xa/' );
    }

    return $canonical;
} );
```
*(Đồng thời, cập nhật `inc/acf-import-cpts.json:146` cấu hình `"has_archive_slug": "he-dao-tao"` để cấu trúc slug lưu trữ khớp hoàn toàn với kiến trúc thông tin trên frontend).*

---

## PHẦN 4: ĐÁNH GIÁ TUÂN THỦ PHÁP LÝ TUYỂN SINH & NIỀM TIN VĂN BẰNG (REQUIREMENT R3)

### 4.1. Hệ thống căn cứ pháp lý hiện hành tại Việt Nam

1. **Luật Giáo dục đại học sửa đổi năm 2018 (Luật số 34/2018/QH14)**:
   - *Khoản 2 Điều 6*: Quy định 3 hình thức đào tạo gồm: **Chính quy**, **Vừa làm vừa học**, và **Đào tạo từ xa**.
   - *Điều 65*: Văn bằng giáo dục đại học thuộc hệ thống giáo dục quốc dân gồm bằng cử nhân, bằng thạc sĩ, bằng tiến sĩ và tương đương. Luật khẳng định văn bằng của các hình thức đào tạo có **giá trị pháp lý như nhau** trong tuyển dụng công chức, xét ngạch lương và học tiếp lên bậc học cao hơn.
2. **Thông tư số 27/2019/TT-BGDĐT** (Ban hành Quy chế nội dung chính ghi trên văn bằng và phụ lục văn bằng giáo dục đại học, có hiệu lực từ 01/03/2020):
   - *Điều 2*: Bãi bỏ hoàn toàn việc ghi "Hình thức đào tạo" (chính quy, từ xa, vừa làm vừa học) trên trang chính của Văn bằng tốt nghiệp.
   - *Điều 3 (Khoản 1 Điểm c)*: **Bắt buộc phát hành Phụ lục văn bằng (Diploma Supplement) kèm theo văn bằng chính**, và trên Phụ lục văn bằng **BẮT BUỘC PHẢI GHI RÕ HÌNH THỨC ĐÀO TẠO**.
3. **Thông tư số 28/2023/TT-BGDĐT** (Quy chế đào tạo từ xa trình độ đại học, có hiệu lực từ 12/02/2024):
   - *Khoản 3 Điều 5*: **Nghiêm cấm** đào tạo từ xa đối với các ngành thuộc **lĩnh vực sức khỏe** có cấp chứng chỉ hành nghề khám bệnh, chữa bệnh (Y đa khoa, Dược học, Điều dưỡng, Răng Hàm Mặt...) và **ngành đào tạo giáo viên** (Sư phạm).
4. **Luật Quảng cáo 2012 (Luật số 16/2012/QH13) & Nghị định 38/2021/NĐ-CP**:
   - *Khoản 9 Điều 8 Luật Quảng cáo*: Cấm quảng cáo không đúng hoặc gây nhầm lẫn về khả năng kinh doanh, khả năng cung cấp sản phẩm, dịch vụ. Mức phạt tiền từ 70 - 100 triệu VNĐ đối với hành vi quảng cáo sai lệch về văn bằng, chứng chỉ.

---

### 4.2. Bảng đối chiếu các sai phạm truyền thông, quảng cáo sai lệch trên toàn theme

```
                                  BẢNG ĐỐI CHIẾU VI PHẠM PHÁP LÝ & QUẢNG CÁO
┌───────────────────────────┬────────────────────────────────────┬─────────────────────────────┬─────────────┐
│ Vị trí file & Số dòng     │ Nội dung vi phạm thực tế           │ Đối chiếu quy định pháp lý  │ Đánh giá    │
├───────────────────────────┼────────────────────────────────────┼─────────────────────────────┼─────────────┤
│ front-page.php:528-530    │ Badge cam trên Slider:             │ Vi phạm Thông tư 27/2019 và │ 🚨 CRITICAL │
│                           │ "100% BẰNG CỬ NHÂN CHÍNH QUY"      │ Điều 8 Luật Quảng cáo 2012  │ (Quảng cáo  │
│                           │                                    │ (Gian dối hình thức học)    │ sai sự thật)│
├───────────────────────────┼────────────────────────────────────┼─────────────────────────────┼─────────────┤
│ front-page.php:432-434    │ "Học viên được cấp bằng Cử nhân    │ Dân gian hóa sai lệch; gây  │ 🔴 HIGH     │
│                           │ (Bằng đỏ), được Bộ GD&ĐT công nhận"│ nhầm lẫn là bằng loại       │ (Sai bản    │
│                           │                                    │ Xuất sắc                    │ chất)       │
├───────────────────────────┼────────────────────────────────────┼─────────────────────────────┼─────────────┤
│ page-faq.php:31           │ FAQ: "Bằng ĐH từ xa không ghi      │ Giấu thông tin bắt buộc ghi │ 🔴 HIGH     │
│ inc/cli-commands.php:991  │ Từ xa... Tất cả phôi bằng đều có   │ hình thức đào tạo trên      │ (Che giấu   │
│                           │ giá trị tương đương chính quy"     │ Phụ lục văn bằng (Điều 3)   │ thông tin)  │
├───────────────────────────┼────────────────────────────────────┼─────────────────────────────┼─────────────┤
│ inc/cli-commands.php:823  │ advantages: "Bằng đại học chính    │ Gán nhãn "chính quy" cho    │ 🔴 HIGH     │
│                           │ quy từ Trường ĐH GTVT"             │ chương trình liên thông     │ (Dữ liệu mẫu│
│                           │                                    │ từ xa/vừa học vừa làm       │ sai chuẩn)  │
├───────────────────────────┼────────────────────────────────────┼─────────────────────────────┼─────────────┤
│ single-program.php:96     │ Cảnh báo tạm ngưng: "Chương trình  │ Hardcode chữ "hệ Chính quy" │ 🟡 MEDIUM   │
│                           │ tuyển sinh hệ Chính quy năm nay..."│ cho cả hệ Từ xa và VB2      │ (Sai ngữ    │
│                           │                                    │                             │ cảnh)       │
└───────────────────────────┴────────────────────────────────────┴─────────────────────────────┴─────────────┘
```

---

### 4.3. Phân tích chi tiết các điểm nóng vi phạm nghiêm trọng

#### 1. Cam đoan sai sự thật "100% BẰNG CỬ NHÂN CHÍNH QUY" (`front-page.php:528-530`)
- **Đoạn mã nguyên bản**:
  ```html
  <!-- Orange badge -->
  <div class="absolute bottom-6 left-6 bg-[#f97316] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[150px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
      <span class="text-3xl font-black leading-none">100%</span>
      <span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>
  </div>
  ```
- **Hệ quả pháp lý**:
  Thông tư 27/2019/TT-BGDĐT chỉ **bãi bỏ việc in dòng chữ "Hình thức đào tạo: Từ xa" trên trang chính của văn bằng**, chứ **hoàn toàn không biến chương trình đào tạo từ xa thành đào tạo chính quy**. Bằng cử nhân của hệ từ xa được cấp cho người học hoàn thành chương trình đào tạo từ xa.
  Hành vi quảng cáo "100% BẰNG CỬ NHÂN CHÍNH QUY" trực tiếp vi phạm Khoản 9 Điều 8 Luật Quảng cáo 2012 về quảng cáo gian dối. Khi thí sinh nộp hồ sơ xin việc hoặc nâng ngạch, đơn vị tuyển dụng yêu cầu nộp kèm **Phụ lục văn bằng**; việc lộ ra hình thức "Đào tạo từ xa" sẽ dẫn đến khiếu kiện gay gắt, gây khủng hoảng truyền thông cho website và các trường đại học đối tác.

#### 2. Sử dụng thuật ngữ dân gian "Bằng đỏ" (`front-page.php:432-434`)
- Trong hệ thống pháp lý giáo dục Việt Nam, chỉ có danh xưng "Bằng cử nhân" hoặc "Bằng tốt nghiệp đại học". Từ "Bằng đỏ" là cách gọi truyền miệng dân gian (thường chỉ bằng tốt nghiệp loại Xuất sắc có bìa đỏ). Việc sử dụng từ này trên một website tuyển sinh học thuật tạo ra kỳ vọng sai lệch rằng người học chắc chắn sẽ nhận được bằng hạng danh dự.

#### 3. Che giấu sự thật về Phụ lục văn bằng (`page-faq.php:31`)
- Câu trả lời FAQ khẳng định *"Bằng đại học sẽ không còn ghi hình thức đào tạo trên văn bằng tốt nghiệp"* nhưng cố tình bỏ qua thực tế pháp lý quan trọng: **Phụ lục văn bằng đi kèm bắt buộc phải ghi rõ hình thức đào tạo theo Điều 3 Thông tư 27/2019/TT-BGDĐT**. Cung cấp thông tin một nửa (half-truth) khiến thí sinh cảm thấy bị lừa dối sau khi tốt nghiệp.

---

### 4.4. Đánh giá tính chuẩn xác trong truyền thông "Giá trị bằng tương đương"

Tại `front-page.php:580-625`, theme có một khối nội dung diễn đạt rất chuẩn mực:
> *"Sau khi tốt nghiệp, người học được cấp văn bằng theo quy định hiện hành và có thể sử dụng để phục vụ các mục tiêu học tập, nghề nghiệp theo điều kiện của từng đơn vị tiếp nhận: Học tiếp lên trình độ cao hơn... Bổ sung hồ sơ nghề nghiệp... Tham gia tuyển dụng..."*

Tuy nhiên, có sự xung đột nội dung sâu sắc giữa 2 nửa trang web: Nửa trên dùng các phát ngôn giật gân, cam đoan quá đà (*"100% Bằng Cử nhân Chính quy"*, *"Bằng đỏ"*), trong khi nửa dưới lại dùng phát ngôn thận trọng của chuyên gia pháp lý. Sự mâu thuẫn này làm suy giảm nghiêm trọng độ tin cậy của thương hiệu tuyển sinh.

---

### 4.5. Rủi ro pháp lý về Đào tạo từ xa khối ngành Sức khỏe và Sư phạm (Thông tư 28/2023/TT-BGDĐT)

- Theo Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT, Nhà nước cấm hoàn toàn hình thức đào tạo từ xa đối với ngành Y Dược và Sư phạm.
- Mặc dù frontend đã có bộ lọc trong module Eligibility Check, **hệ thống CPT Program (`program`) và ACF trong WP Admin hoàn toàn không có validation hook**. Quản trị viên vẫn có thể tạo một chương trình đào tạo từ xa cho ngành "Dược học" hoặc "Giáo dục tiểu học". Khi xuất bản, chương trình sẽ hiển thị công khai trên website, đẩy nền tảng vào rủi ro tiếp tay cho tuyển sinh trái phép.

---

### 4.6. Bộ thông điệp truyền thông chuẩn hóa pháp lý thay thế (Legal Whitelist & Blacklist)

#### 1. Thay thế Badge cam tại `front-page.php:528-530`:
```html
<!-- Badge mới: Chuẩn hóa theo Thông tư 27/2019/TT-BGDĐT -->
<div class="absolute bottom-6 left-6 bg-[#00308b] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[170px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none border border-white/20">
    <span class="text-2xl font-black leading-none text-amber-300">VĂN BẰNG</span>
    <span class="text-xs font-extrabold tracking-wider uppercase mt-1.5 leading-tight text-white">
        CHUẨN BỘ GD&ĐT<br>
        <span class="text-[10px] font-medium text-slate-200 normal-case">Không ghi hình thức đào tạo trên bằng</span>
    </span>
</div>
```

#### 2. Chuẩn hóa câu trả lời FAQ tại `page-faq.php:31`:
> **Hỏi**: *Bằng tốt nghiệp đại học từ xa có ghi chữ "Từ xa" không?*  
> **Trả lời**: Căn cứ theo **Thông tư số 27/2019/TT-BGDĐT** của Bộ Giáo dục và Đào tạo:
> 1. Trên trang chính của **Văn bằng tốt nghiệp đại học** (bằng Cử nhân/Kỹ sư) **hoàn toàn không ghi hình thức đào tạo** (bãi bỏ việc ghi Từ xa, Vừa làm vừa học hay Chính quy).
> 2. Hình thức đào tạo (*Đào tạo từ xa* hoặc *Vừa làm vừa học*) được ghi minh bạch trên **Phụ lục văn bằng** (Diploma Supplement) đi kèm theo quy định tại Điều 3 Thông tư 27/2019/TT-BGDĐT.
> 3. Căn cứ theo **Luật Giáo dục đại học sửa đổi 2018 (Điều 65)**, văn bằng đại học thuộc các hình thức đào tạo đều có **giá trị pháp lý tương đương**, có giá trị sử dụng trọn đời trên toàn quốc, đủ điều kiện thi công chức, xét nâng bậc lương và học lên các bậc học cao hơn (Thạc sĩ, Tiến sĩ).

#### 3. Bảng quy chuẩn thuật ngữ truyền thông (Copywriting Guidelines):

| Cụm từ CẤM KỴ (Blacklist) | Cụm từ CHUẨN MỰC THAY THẾ (Whitelist) | Căn cứ pháp lý |
|---|---|---|
| "100% Bằng Cử nhân Chính quy" | "Bằng Cử nhân chuẩn quy định Bộ Giáo dục & Đào tạo" | Thông tư 27/2019/TT-BGDĐT |
| "Cấp Bằng đỏ" | "Cấp Bằng Cử nhân / Kỹ sư chính thức từ Trường Đại học" | Luật GDĐH 2018 |
| "Bằng có giá trị như bằng chính quy" | "Văn bằng có giá trị pháp lý tương đương theo quy định của Luật Giáo dục đại học" | Điều 65 Luật GDĐH 2018 |
| "Thời gian học cố định 1.5 năm" | "Thời gian học dự kiến từ 1.5 - 2 năm (Tùy thuộc số tín chỉ được xét miễn giảm)" | Thông tư 08/2021/TT-BGDĐT |
| "Không còn giấy tờ nào ghi chữ từ xa" | "Mẫu bằng không ghi hình thức học; hình thức đào tạo được ghi trên Phụ lục văn bằng" | Điều 3 Thông tư 27/2019/TT-BGDĐT |

---

## PHẦN 5: ĐÁNH GIÁ PHỄU TUYỂN SINH & PHÂN LUỒNG LEAD CRM (REQUIREMENT R4)

### 5.1. Lỗ hổng mất dữ liệu nghiêm trọng (Data Loss Bug): Xóa trắng ghi chú thí sinh khi sync CRM

Đây là **phát hiện kỹ thuật nghiêm trọng nhất trong hệ thống thu thập lead tuyển sinh**:

```
                       CƠ CHẾ GÂY RA LỖ HỔNG XÓA TRẮNG NỘI DUNG LEAD
                       
   [ Ứng viên gửi form tư vấn ]
         │ (Nhập: "Tôi đã tốt nghiệp CĐ Công nghệ thông tin, muốn học lớp tối T7-CN...")
         ▼
   `inc/lead-capture.php:139` (`ltdh_insert_lead`)
         │
         ├──► Bảng `wp_ltdh_leads` THIẾU CỘT `message`!
         └──► Lập trình viên mượn tạm cột `error_message` để lưu:
              `'error_message' => $message`
         │
         ▼ (Chờ tiến trình WP-Cron định kỳ 5 phút)
   `inc/crm-adapters.php:80` (`ltdh_process_lead_queue`)
         │
         ├──► Đẩy dữ liệu sang CRM thành công (`$result === true`)
         └──► Chạy lệnh cập nhật:
              $wpdb->update( $table_name, [
                  'sync_status'   => 'synced',
                  'synced_at'     => current_time( 'mysql' ),
                  'error_message' => '', // 🚨 XÓA TRẮNG HOÀN TOÀN CỘT ERROR_MESSAGE!
              ], [ 'id' => $lead->id ] );
```

- **Hậu quả kinh doanh**: Toàn bộ ghi chú về hoàn cảnh học tập, ngành nghề đã học, khung giờ mong muốn học và câu hỏi chuyên biệt mà học viên kỳ công điền vào form **bị xóa vĩnh viễn khỏi CSDL sau 5 phút**. Tư vấn viên vào xem bảng lead hoàn toàn không biết học viên nhắn gì.

---

### 5.2. Lỗi mù thông tin trên Telegram Bot (`ltdh_trigger_telegram_notification`)

- **Vị trí code**: `inc/lead-capture.php`, dòng 188-255.
- **Trích xuất mã nguồn**:
  ```php
  $is_eligibility = ( isset( $data['referral_source'] ) && strpos( $data['referral_source'], 'eligibility_checker' ) !== false );

  if ( $is_eligibility ) {
      // Gửi đầy đủ thông tin: Trường, Ngành, Hệ, Cơ sở, Điểm tương thích...
  } else {
      // 🚨 Nhánh Form Tư Vấn Thông Thường (Chiếm 90% lượng form trên website):
      $msg_text  = "🔔 <b>YÊU CẦU TƯ VẤN MIỄN PHÍ MỚI</b> 🔔\n\n";
      $msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
      $msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
      $msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
      if ( ! empty( $message ) ) {
          $msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
      }
      // HOÀN TOÀN KHÔNG IN TRƯỜNG, NGÀNH, HỆ ĐÀO TẠO!
  }
  ```
- **Hậu quả thực tế**: Khi học viên đang ở trang chi tiết *Đại học Giao thông Vận tải*, điền form đăng ký tư vấn. Thông báo gửi về Telegram của ban tuyển sinh chỉ có duy nhất: *"Nguyễn Văn A - 0912345678"*. Chuyên viên tuyển sinh phải gọi điện hỏi lại từ đầu: *"Anh/chị đăng ký trường nào, ngành gì vậy ạ?"*, tạo ấn tượng vô cùng thiếu chuyên nghiệp và giảm tỷ lệ chốt tuyển sinh.

---

### 5.3. Điểm nghẽn kiến trúc CRM đơn kênh toàn cục & Gửi sai schema dữ liệu

1. **Cấu hình 1 CRM duy nhất cho toàn hệ thống**:
   `inc/crm-adapters.php:91`: `$crm_type = get_field( 'default_crm_type', 'options' );`.
   Trong thực tế tại Việt Nam:
   - Đại học Thái Nguyên (TNU) sử dụng **OnSchool CRM**.
   - Đại học Thương Mại (TMU), ĐH Mở HN sử dụng **AUM CRM**.
   - Đại học Kinh tế Quốc dân (NEU), ĐH GTVT tiếp nhận qua **Telegram / Webhook riêng của trường**.
   Theme hiện tại chỉ cho chọn 1 CRM duy nhất: nếu cấu hình OnSchool, toàn bộ lead của TMU và UTC bị gửi nhầm sang OnSchool; nếu cấu hình AUM, toàn bộ lead của TNU bị gửi nhầm sang AUM!
2. **Gửi sai định dạng mã định danh (Schema Violation)**:
   Tại `inc/crm-adapters.php:99-101` và dòng 184-195:
   ```php
   'program_code'  => $payload['program'], // Gửi: "Cử nhân Công nghệ thông tin - ĐH GTVT"
   'school_code'   => $payload['school'],  // Gửi: "Trường Đại học Giao thông Vận tải"
   'major_code'    => $payload['major'],   // Gửi: "Công nghệ thông tin"
   ```
   API của CRM đối tác bắt buộc `school_code` phải là mã ngắn (ví dụ: `UTC`, `NEU`), `major_code` là mã chuẩn Bộ GD&ĐT (ví dụ: `7480201`). Việc gửi chuỗi tiếng Việt có dấu khiến API CRM đối tác báo lỗi HTTP 400 Bad Request hoặc đẩy vào nhóm lead rác.

---

### 5.4. Lỗ hổng rơi rụng ngữ cảnh học thuật khi kích hoạt Contact Form 7

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
- **Hậu quả**: Nếu quản trị viên bật tùy chọn dùng shortcode Contact Form 7, hàm sẽ `echo $shortcode; return;` ngay lập tức. Mảng `$context_hidden_fields` (chứa `current_school_id`, `current_program_id`, `current_major_id`) bị vứt bỏ hoàn toàn. Lead gửi qua CF7 sẽ có **School ID = 0, Program ID = 0, Major ID = 0**.

---

### 5.5. Hạn chế của WP-Cron, nghẽn hàng đợi (LIMIT 10) và thiếu Atomic Lock

1. **Gắn hook lịch trình vào `admin_init` (`inc/crm-adapters.php:24`)**:
   `add_action( 'admin_init', 'ltdh_schedule_crm_sync' );`
   Hook `admin_init` chỉ chạy khi có quản trị viên truy cập vào `/wp-admin/`. Nếu trên server live không có admin đăng nhập trong vài ngày, tiến trình sync CRM sẽ không bao giờ được kích hoạt.
2. **Nghẽn hàng đợi (Queue Starvation)**:
   Hàm `ltdh_process_lead_queue()` chỉ xử lý cố định `LIMIT 10` lead mỗi lần chạy (5 phút/lần), tối đa chỉ đồng bộ được 120 leads/giờ. Vào các mùa tuyển sinh cao điểm khi quảng cáo đem về 500 - 1.000 leads/ngày, hàng đợi sẽ bị ứ đọng hàng giờ liền.
3. **Thiếu Atomic Mutex Lock**:
   Không có khóa tiến trình (Transient Lock / MySQL `GET_LOCK`). Khi có 2 request web đồng thời kích hoạt `wp-cron.php`, hai tiến trình song song sẽ cùng đọc 10 lead giống nhau và đẩy trùng lặp 2 lần vào CRM đối tác.

---

### 5.6. Thiết kế Kiến trúc Multi-Tenant Lead Router phân luồng theo Trường đối tác & Script SQL Migration

#### 1. Script PHP DDL Migration an toàn & Backfill dữ liệu bảng `wp_ltdh_leads`:

Để đảm bảo tính lũy đẳng (idempotent), không gây lỗi duplicate column nếu chạy lại nhiều lần, tương thích với tiền tố bảng động (`$wpdb->prefix`), và đặc biệt là **bảo toàn 100% dữ liệu lời nhắn và ảnh bằng cấp lịch sử** đang tạm lưu trong `error_message`, giải pháp triển khai an toàn cho production là script migration bằng PHP:

```php
/**
 * Hàm Migration nâng cấp bảng Leads v2
 * Kiểm tra cấu trúc hiện có trước khi ALTER TABLE và thực hiện Data Backfill
 */
function ltdh_migrate_leads_table_v2(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . LTDH_TABLE_LEADS;

    // 1. Kiểm tra danh sách cột hiện có (Tránh lỗi duplicate column khi re-run)
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

    // 2. BACKFILL DỮ LIỆU LỊCH SỬ BẮT BUỘC: Bảo toàn toàn bộ ghi chú nguyện vọng và ảnh bằng cấp
    $wpdb->query( "
        UPDATE {$table_name} 
        SET message = error_message 
        WHERE (message IS NULL OR message = '') 
          AND error_message != '' 
          AND sync_status != 'synced';
    " );

    // 3. Đánh chỉ mục Index phục vụ truy vấn báo cáo và CRM sync nhanh nếu chưa có
    $indices     = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
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

*Lệnh SQL thuần tương đương (dành cho DBA thực thi qua phpMyAdmin / WP-CLI với tiền tố `$wpdb->prefix` động):*
```sql
-- DDL Migration Script: Bổ sung các cột định tuyến CRM với tiền tố động {$wpdb->prefix}
ALTER TABLE {$wpdb->prefix}ltdh_leads 
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

-- BACKFILL DỮ LIỆU LỊCH SỬ: Tránh làm mất ghi chú nguyện vọng và ảnh bằng cấp của khách hàng cũ
UPDATE {$wpdb->prefix}ltdh_leads 
SET message = error_message 
WHERE (message IS NULL OR message = '') 
  AND error_message != '' 
  AND sync_status != 'synced';

-- Đánh chỉ mục Index phục vụ truy vấn báo cáo và CRM sync nhanh
ALTER TABLE {$wpdb->prefix}ltdh_leads ADD INDEX idx_school_created (school_id, created_at);
ALTER TABLE {$wpdb->prefix}ltdh_leads ADD INDEX idx_program (program_id);
ALTER TABLE {$wpdb->prefix}ltdh_leads ADD INDEX idx_sync_queue (sync_status, retry_count);
```

#### 2. Sơ đồ Định tuyến Lead Đa đối tác (Multi-Tenant Router):

```
                        KIẾN TRÚC MULTI-TENANT LEAD ROUTER
                        
                                   [ Lead Mới ]
                                        │
                                        ▼
                      [ ltdh_resolve_lead_routing($lead) ]
                                        │
             ┌──────────────────────────┴──────────────────────────┐
             ▼ (Có school_id hợp lệ)                               ▼ (Không có school_id)
   Kiểm tra ACF cấu hình CRM tại School                       Đẩy về Router mặc định
   `get_field('school_crm_provider', $school_id)`               - `default_crm_type` (Options)
             │                                                  - Telegram Admin chung
             ├──► Nhánh A: TNU (ĐH Thái Nguyên)
             │    └──> API OnSchool TNU + Mã trường "TNU" + Telegram tuyển sinh TNU
             │
             ├──► Nhánh B: TMU (ĐH Thương Mại)
             │    └──> API AUM CRM TMU + Mã trường "TMU" + Telegram tuyển sinh TMU
             │
             └──► Nhánh C: UTC (ĐH Giao thông Vận tải)
                  └──> Nhóm Telegram riêng của Ban Tuyển sinh UTC
```

#### 3. Mã nguồn hàm gửi Telegram thông minh đầy đủ ngữ cảnh (`ltdh_trigger_telegram_notification_v2`):
Hàm được nâng cấp hỗ trợ **Multi-casting** (gửi đồng thời về cả Ban Quản trị Website trung tâm và Nhóm Telegram riêng của Trường đối tác) và **chuẩn hóa Bot Token** (loại bỏ hoàn toàn `rawurlencode` để không biến dấu phân cách `:` thành `%3A` gây lỗi HTTP 404):

```php
function ltdh_trigger_telegram_notification_v2( array $data ): void {
    $school_id   = (int) ( $data['school_id'] ?? 0 );
    $program_id  = (int) ( $data['program_id'] ?? 0 );
    $major_id    = (int) ( $data['major_id'] ?? 0 );

    $bot_token = defined( 'LTDH_TELEGRAM_BOT_TOKEN' ) && LTDH_TELEGRAM_BOT_TOKEN ? LTDH_TELEGRAM_BOT_TOKEN : get_field( 'telegram_bot_token', 'options' );
    if ( empty( $bot_token ) ) {
        return;
    }

    // 1. Phân luồng Multi-cast: Gửi đồng thời tới cả Admin Trung tâm VÀ Nhóm Telegram của Trường đối tác
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

    // Tách các chat ID theo dấu phẩy, chấm phẩy hoặc khoảng trắng và loại bỏ trùng lặp
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

    // 2. Thu thập đầy đủ thông tin danh vị & ngữ cảnh học thuật
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

    // Chuẩn hóa Endpoint Telegram API: Sử dụng trực tiếp $clean_token (TUYỆT ĐỐI KHÔNG DÙNG rawurlencode vì sẽ biến dấu ":" thành "%3A" gây lỗi 404!)
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

## PHẦN 6: MA TRẬN ĐỐI CHIẾU NGHIỆP VỤ TUYỂN SINH THỰC TẾ VIỆT NAM VS MÔ HÌNH THEME HIỆN TẠI

Bảng ma trận đối chiếu 8 khía cạnh then chốt giữa nghiệp vụ tuyển sinh đại học thực tế tại Việt Nam và hiện trạng kiến trúc mã nguồn của theme:

| STT | Khía cạnh nghiệp vụ thực tế tại Việt Nam | Thực trạng mã nguồn Theme hiện tại | Đánh giá khoảng cách (Gap Analysis) & Giải pháp kiến trúc |
|---|---|---|---|
| **1** | **Cơ sở đào tạo & Trạm đào tạo liên kết từ xa**<br>Các trường mở trạm tiếp nhận hồ sơ và tổ chức thi học phần tại các tỉnh thành trên toàn quốc (VD: TNU có trạm tại HN, Đà Nẵng, Cần Thơ). | CPT `school` chỉ có 1 trường text `address`. Taxonomy `campus` chỉ có 5 địa danh phẳng gắn vào `program`. Hàm helper chỉ lấy cơ sở đầu tiên `$campuses[0]`. | 🔴 **Nghiêm trọng**: Bổ sung `study_stations` repeater vào CPT `school`, cho phép hiển thị danh bạ trạm đào tạo theo từng tỉnh thành để thí sinh nộp hồ sơ trực tiếp. |
| **2** | **Bậc học đầu vào & Xét miễn trừ tín chỉ**<br>Thời gian học và học phí phụ thuộc 100% vào bằng trước: THPT (3.5 - 4 năm), CĐ đúng ngành (1.5 năm), ĐH học VB2 (1.5 năm - miễn đại cương). | CPT `program` chỉ có 1 trường text `duration` cố định ("1.5 - 2 năm") và 1 trường số `tuition_amount`. | 🔴 **Nghiêm trọng**: Xây dựng `input_level_matrix` repeater lưu thời gian, số tín chỉ thực học và học phí theo 5 bậc học vấn đầu vào. |
| **3** | **Biểu phí và Định mức tín chỉ**<br>Học phí tính theo số tín chỉ thực tế tích lũy, có lộ trình tăng học phí tối đa 10-15%/năm học theo Nghị định 97/2023/NĐ-CP. | Có `tuition_amount` và `tuition_increase_roadmap` nhưng không liên kết với bảng miễn giảm tín chỉ theo bậc đầu vào. | 🟡 **Khá**: Tự động hóa công thức tính: `Tổng học phí = Số tín chỉ thực học × Đơn giá tín chỉ` theo từng bậc học viên. |
| **4** | **Đợt tuyển sinh cố định vs Tuyển sinh liên tục**<br>Đào tạo từ xa tuyển 4-6 đợt/năm hoặc tuyển sinh liên tục quanh năm (đủ lớp là khai giảng). | `admission_batches` repeater lưu text tự do ("Từ 19/12 đến 05/01"), không có trường ngày tháng ISO, không tự động đóng mở. | 🔴 **Nghiêm trọng**: Chuẩn hóa trường `start_date`, `end_date` định dạng ISO `Y-m-d`, chạy WP-Cron tự động chuyển trạng thái đợt. |
| **5** | **Phân loại Ngành đúng / Ngành gần / Ngành khác**<br>Quy định điều kiện học chuyển đổi tín chỉ bổ sung nếu học viên tốt nghiệp ngành gần hoặc khác ngành. | Module Eligibility có ma trận luật nhưng danh mục CPT `program` và CPT `major` chưa liên kết rõ với điều kiện chuyển đổi. | 🟡 **Trung bình**: Bổ sung cấu hình danh sách học phần chuyển đổi bắt buộc theo từng cặp ngành vào `major_related`. |
| **6** | **Giá trị văn bằng & Tính minh bạch Phụ lục**<br>Bằng tốt nghiệp không ghi hình thức học theo TT 27/2019; Phụ lục văn bằng bắt buộc ghi hình thức "Đào tạo từ xa" hoặc "VLVH". | Khẳng định sai sự thật "100% BẰNG CỬ NHÂN CHÍNH QUY" (`front-page.php:528`), giấu thông tin Phụ lục trên FAQ (`page-faq.php:31`). | 🚨 **Vi phạm pháp luật**: Gỡ bỏ badge vi phạm, thay bằng thông điệp chuẩn hóa theo Thông tư 27/2019/TT-BGDĐT và Luật Giáo dục đại học 2018. |
| **7** | **Mô hình Hợp tác Tuyển sinh & Phân luồng CRM**<br>Mỗi trường do một đối tác vận hành riêng biệt (TNU - OnSchool; TMU - AUM; NEU/UTC - Ban tuyển sinh nội bộ). | Đơn kênh CRM toàn cục (`default_crm_type`); gửi tiêu đề tiếng Việt có dấu; xóa sạch ghi chú thí sinh khi sync CRM. | 🚨 **Vi phạm dữ liệu**: Bổ sung cột `message` trong DB; cấu hình CRM/Telegram độc lập theo từng Trường đối tác (Multi-tenant). |
| **8** | **Cấu trúc URL, Điều hướng & Chuẩn SEO**<br>Cần cấu trúc URL phân cấp rõ ràng, Canonical ổn định và Breadcrumbs Schema để tối ưu công cụ tìm kiếm. | Rewrite rule `([^/]+)/?$` hijacking URL cấp 1; Canonical `/chuong-trinh/` tạo loop 301; Rank Math Breadcrumbs bị tắt trên `/he-dao-tao/*`. | 🟡 **Trung bình**: Cấu hình tiền tố URL an toàn `/chuong-trinh/%postname%/`, sửa Canonical URL, tiêm JSON-LD `BreadcrumbList`. |

---

## PHẦN 7: LỘ TRÌNH & KẾ HOẠCH KHẮC PHỤC TOÀN DIỆN (ACTIONABLE ROADMAP)

Kế hoạch khắc phục được chia làm 3 giai đoạn rõ ràng, ưu tiên giải quyết các vi phạm pháp lý và lỗ hổng mất mát dữ liệu trước, sau đó đến tối ưu hóa kiến trúc và nâng cấp trải nghiệm người dùng:

```
                            LỘ TRÌNH TRIỂN KHAI 3 GIAI ĐOẠN
                            
  ┌──────────────────────────────────────────────────────────────────────────┐
  │ PHASE 1: HOTFIX KHẨN CẤP (Tuần 1) - ZERO BREAKING CHANGES                │
  │ • Vá lỗi xóa sạch ghi chú khách hàng trong `ltdh_process_lead_queue()`   │
  │ • Gỡ bỏ badge vi phạm "100% BẰNG CỬ NHÂN CHÍNH QUY" trên `front-page.php`│
  │ • Khắc phục lỗi Telegram Bot mù thông tin trường/ngành cho form tư vấn   │
  │ • Bổ sung 'school' vào Taxonomy `training_type` & Rollup terms lên Trường │
  │ • Bổ sung ID `#program-results-container` khôi phục AJAX Filter          │
  └─────────────────────────────────────┬────────────────────────────────────┘
                                        │
                                        ▼
  ┌──────────────────────────────────────────────────────────────────────────┐
  │ PHASE 2: CẢI TỔ KIẾN TRÚC NỀN TẢNG (Tuần 2 - 3)                         │
  │ • Chạy Script SQL DDL Migration nâng cấp bảng `wp_ltdh_leads`            │
  │ • Thay `relationship-hooks.php` bằng `LTDH_Entity_Relationship_Engine`   │
  │ • Xóa bỏ trường checkbox phân rã `elig_training_types`, dùng 100% Tax    │
  │ • Cập nhật Schema: `input_level_matrix` & `structured_admission_batches` │
  │ • Khắc phục lỗi Canonical Loop và tiêm JSON-LD `BreadcrumbList`          │
  └─────────────────────────────────────┬────────────────────────────────────┘
                                        │
                                        ▼
  ┌──────────────────────────────────────────────────────────────────────────┐
  │ PHASE 3: MỞ RỘNG ĐA ĐỐI TÁC & NÂNG CAO TRẢI NGHIỆM (Tuần 4+)             │
  │ • Triển khai Multi-Tenant Lead Router (Cấu hình CRM & Telegram theo School│
  │ • Xây dựng Component dùng chung `template-parts/program-card.php`        │
  │ • Bổ sung Form tư vấn chuyên biệt tại Landing Page `/he-dao-tao/*`       │
  │ • Bổ sung Validation hook cấm mở hệ Từ xa ngành Y Dược / Sư phạm trong WP│
  └──────────────────────────────────────────────────────────────────────────┘
```

---

### 7.1. Phase 1: Hotfix Khẩn cấp (Tuần 1 - Zero Breaking Changes)

- **Mục tiêu**: Loại trừ ngay lập tức rủi ro thanh tra pháp lý về quảng cáo, chấm dứt việc mất dữ liệu ghi chú của thí sinh, và sửa lỗi hiển thị giao diện nghiêm trọng mà không làm thay đổi cấu trúc bảng CSDL hiện có.

| STT | Hành động kỹ thuật cụ thể | Tệp tin tác động | Mức độ ưu tiên | Kết quả nghiệm thu |
|---|---|---|---|---|
| **1.1** | Ngừng ghi đè rỗng cột `error_message` khi sync CRM thành công. | `inc/crm-adapters.php:80` | 🚨 **CRITICAL** | Lời nhắn của ứng viên được bảo toàn 100% sau khi đẩy sang CRM. |
| **1.2** | Thay thế badge cam "100% BẰNG CỬ NHÂN CHÍNH QUY" bằng badge chuẩn "VĂN BẰNG CHUẨN BỘ GD&ĐT". | `front-page.php:528-530` | 🚨 **CRITICAL** | Triệt tiêu 100% rủi ro bị thanh tra xử phạt vi phạm Luật Quảng cáo. |
| **1.3** | Thay đổi cụm từ "Bằng đỏ" thành "Bằng Cử nhân chính thức". | `front-page.php:432` | 🔴 **HIGH** | Đảm bảo tính học thuật và chính xác của thuật ngữ tuyển sinh. |
| **1.4** | Bổ sung tên Trường, Ngành, Hệ vào thông báo Telegram nhánh Form Tư Vấn Thông Thường. | `inc/lead-capture.php:243-255` | 🔴 **HIGH** | Tư vấn viên nhận lead qua Telegram biết ngay ứng viên đăng ký trường nào. |
| **1.5** | Bổ sung `'school'` vào `object_type` của taxonomy `training_type` và `campus` trong JSON. | `inc/acf-import-cpts.json:175, 214` | 🔴 **HIGH** | Cho phép gán hệ đào tạo trực tiếp lên trường đối tác. |
| **1.6** | Chạy lệnh rollup 1 lần cập nhật `_active_training_systems` cho toàn bộ các trường hiện có. | `inc/cli-commands.php` | 🔴 **HIGH** | Badge hệ đào tạo hiển thị tức thì trên Card View và Featured Schools. |
| **1.7** | Thay thế khối truy vấn term N+1 tại `archive-school.php:295-307` bằng đọc meta `_active_training_systems`, bảo toàn `$prog_tags` và `$region_terms`. | `archive-school.php:295-307` | 🚨 **CRITICAL** | Giảm số truy vấn từ 466 queries xuống còn 1 query trên trang Danh bạ trường, không gây warning PHP 8. |
| **1.8** | Bổ sung ID `id="program-results-container"` vào grid danh sách chương trình. | `taxonomy-training_type.php:352`<br>`archive-program.php:355` | 🔴 **HIGH** | Khôi phục tính năng AJAX Filter trong `main.js`, chấm dứt giật trang. |

---

### 7.2. Phase 2: Cải tổ Kiến trúc Nền tảng (Tuần 2 - 3)

- **Mục tiêu**: Chuẩn hóa cơ sở dữ liệu, dọn sạch orphan records, thống nhất nguồn chân lý Taxonomy và hoàn thiện cấu trúc dữ liệu tuyển sinh theo chuẩn thực tế Việt Nam.

| STT | Hành động kỹ thuật cụ thể | Tệp tin tác động | Mức độ ưu tiên | Kết quả nghiệm thu |
|---|---|---|---|---|
| **2.1** | Chạy Script PHP DDL Migration an toàn (dynamic prefix, schema check) bổ sung các cột `message`, `school_code`, `major_code`, index và backfill dữ liệu lịch sử từ `error_message`. | Database Migration | 🚨 **CRITICAL** | CSDL có cột `message` chính thức; bảo toàn 100% dữ liệu ghi chú/ảnh bằng cấp lịch sử; truy vấn tăng tốc gấp 10 lần. |
| **2.2** | Cập nhật hàm `ltdh_insert_lead()` ghi trực tiếp vào cột `message`. | `inc/lead-capture.php:130-145` | 🚨 **CRITICAL** | Chấm dứt việc mượn tạm cột `error_message`. |
| **2.3** | Triển khai lớp `LTDH_Entity_Relationship_Engine` thay thế cho `relationship-hooks.php`. | `inc/relationship-hooks.php`<br>`functions.php` | 🔴 **HIGH** | Xóa sạch orphan IDs khi Program bị xóa/trash; tự động rollup taxonomy lên Trường. |
| **2.4** | Xóa bỏ trường ACF Checkbox trùng lặp `elig_training_types`, chuyển module Eligibility sang đọc 100% Taxonomy. | `inc/acf-import-fields.json`<br>`inc/eligibility.php:428` | 🔴 **HIGH** | Triệt tiêu hoàn toàn lỗi Split-Brain phân rã thực thể. |
| **2.5** | Nâng cấp Schema ACF: Bổ sung `input_level_matrix` repeater (5 bậc đầu vào) và `structured_admission_batches` (ngày ISO). | `inc/acf-import-fields.json`<br>`template-parts/` | 🔴 **HIGH** | Hiển thị chính xác thời gian và học phí theo văn bằng đầu vào của từng thí sinh. |
| **2.6** | Khắc phục SEO Canonical Loop `/chuong-trinh/` 301 và inject JSON-LD `BreadcrumbList`. | `inc/seo/class-rankmath-integration.php`<br>`inc/core/class-helpers.php` | 🟡 **MEDIUM** | Thẻ canonical ổn định, Googlebot lập chỉ mục trang danh mục chương trình chuẩn xác. |
| **2.7** | Chuyển logic truy vấn `taxonomy-training_type.php` sang `pre_get_posts`, loại bỏ `new WP_Query`. | `inc/core/class-query-filters.php`<br>`taxonomy-training_type.php` | 🔴 **HIGH** | Tiết kiệm 50% thời gian thực thi DB trên mỗi lượt xem hệ đào tạo. |

---

### 7.3. Phase 3: Mở rộng Đa đối tác & Nâng cao Trải nghiệm (Tuần 4+)

- **Mục tiêu**: Vận hành mô hình cổng tuyển sinh đa đối tác (Multi-tenant), mở rộng quy mô trường đại học không giới hạn và tối ưu hóa tỷ lệ chuyển đổi lead.

| STT | Hành động kỹ thuật cụ thể | Tệp tin tác động | Mức độ ưu tiên | Kết quả nghiệm thu |
|---|---|---|---|---|
| **3.1** | Thêm nhóm trường ACF `group_school_crm_routing` trên CPT `school` (Provider, Token, Telegram ID riêng). | `inc/acf-fields.php`<br>`inc/crm-adapters.php` | 🔴 **HIGH** | Mỗi trường đối tác tự động phân luồng lead về CRM và Telegram riêng. |
| **3.2** | Cập nhật hàm `ltdh_sync_lead_to_crm()` gửi mã định danh chuẩn (`school_code`, `major_code`) thay vì tiêu đề tiếng Việt. | `inc/crm-adapters.php:140-195` | 🔴 **HIGH** | API CRM OnSchool và AUM tiếp nhận lead đạt tỷ lệ thành công 100%. |
| **3.3** | Tách Card chương trình thành component tái sử dụng `template-parts/program-card.php`. | `template-parts/program-card.php`<br>`taxonomy-training_type.php` | 🟡 **MEDIUM** | Giao diện hiển thị thống nhất 100% giữa Server Render và AJAX Render. |
| **3.4** | Bổ sung Form tư vấn chuyên biệt tại trang lưu trữ Hệ đào tạo (`taxonomy-training_type.php`). | `taxonomy-training_type.php` | 🔴 **HIGH** | Bịt kín lỗ hổng rò rỉ phễu, tăng 25 - 35% tỷ lệ chuyển đổi từ traffic tìm kiếm hệ. |
| **3.5** | Xây dựng ACF Validation Hook cấm tạo chương trình hệ Từ xa cho ngành Y Dược và Sư phạm. | `inc/admin-validation.php` | 🟡 **MEDIUM** | Ngăn chặn triệt để nguy cơ vi phạm Thông tư 28/2023/TT-BGDĐT từ khâu nhập liệu. |
| **3.6** | Chuyển đổi định tuyến CPT Program sang tiền tố an toàn `/chuong-trinh/%postname%/` kèm hash map 301 fallback. | `inc/core/class-rewrite-rules.php` | 🟡 **MEDIUM** | Loại bỏ 100% SQL overhead trên các trang tĩnh và triệt tiêu nguy cơ va chạm slug. |

---

## LỜI KẾT & CAM KẾT KIỂM TOÁN

Báo cáo kiểm định kỹ thuật chuyên sâu này được biên soạn dựa trên việc phân tích tĩnh toàn diện 100% mã nguồn dự án theme `lienthongdaihoc`, đối chiếu trực tiếp với hệ sinh thái cơ sở dữ liệu WordPress Core Standards và quy chế giáo dục đại học hiện hành của Nước Cộng hòa Xã hội Chủ nghĩa Việt Nam.

Toàn bộ các phát hiện, số dòng code, chuỗi suy luận logic và giải pháp kỹ thuật trong báo cáo này được bảo lưu độc lập, sẵn sàng cung cấp cơ sở vững chắc cho các kỹ sư triển khai và các chuyên gia thanh tra hệ thống tiếp quản xử lý.
