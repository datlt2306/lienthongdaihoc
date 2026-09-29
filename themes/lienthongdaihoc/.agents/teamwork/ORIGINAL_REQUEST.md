# Original User Request

## 2026-09-25T05:01:12Z

Thực hiện rà soát, đánh giá toàn diện lại toàn bộ mã nguồn của dự án theme WordPress Liên Thông Đại Học, phát hiện tất cả các vấn đề tiềm ẩn và xuất báo cáo kiểm định chi tiết kèm giải pháp khắc phục cụ thể mà không tự ý sửa đổi code gốc.

Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
Integrity mode: development

## Requirements

### R1. Rà soát chuẩn mã nguồn PHP 8+ & WordPress Core Standards
- Kiểm tra tính tương thích cú pháp với PHP 8.1 - 8.3 trên tất cả các file PHP (chạy kiểm tra cú pháp, phát hiện các hàm bị deprecated, type mismatches, null handling, undefined array key).
- Kiểm tra kiến trúc hook/filter (action hooks, filter hooks, priority, vòng lặp vô hạn tiềm ẩn) và cấu trúc phân tách logic trong theme (`inc/`, `functions.php`, templates).

### R2. Đánh giá chuyên sâu về Bảo mật & Kiểm soát dữ liệu
- Rà soát lỗ hổng bảo mật: SQL Injection (kiểm tra `wpdb->prepare`), XSS (kiểm tra toàn bộ output escaping: `esc_html`, `esc_attr`, `esc_url`, `wp_kses`), CSRF (kiểm tra `wp_verify_nonce` và `check_ajax_referer`).
- Kiểm tra phân quyền truy cập (`current_user_can`), kiểm tra bảo vệ direct file access (`defined('ABSPATH') || exit;`), và kiểm tra làm sạch dữ liệu đầu vào (`sanitize_text_field`, `absint`, v.v.).

### R3. Đánh giá Hiệu năng & Tối ưu Database Query
- Rà soát các vòng lặp WP_Query / query cơ sở dữ liệu nhằm phát hiện lỗi N+1 query, thiếu pagination limit, hoặc query nặng không cần thiết.
- Đánh giá cơ chế bộ nhớ đệm (Transient API / Object Cache) cho các dữ liệu tính toán lớn hoặc gọi API ngoài.
- Kiểm tra quản lý enqueue assets (CSS/JS: trùng lặp, tải tài nguyên không dùng, thiếu async/defer hoặc versioning).

### R4. Đánh giá SEO On-page, Schema Markup & Độ hoàn thiện Frontend
- Kiểm tra cấu trúc thẻ HTML ngữ nghĩa (heading H1-H6, alt ảnh, semantic tags).
- Kiểm tra tính hợp lệ của cấu trúc dữ liệu Schema (JSON-LD: Course, EducationalOrganization, FAQ, BreadcrumbList...).
- Đánh giá tính toàn vẹn giao diện responsive trên các breakpoint và console error từ JavaScript phía client.

### R5. Xuất báo cáo tổng kết và Kế hoạch khắc phục (Audit Report)
- Tổng hợp toàn bộ phát hiện thành tài liệu báo cáo `FULL_PROJECT_AUDIT_REPORT.md` tại thư mục làm việc.
- Phân loại theo mức độ nghiêm trọng: Critical (nguy cấp), High (cao), Medium (trung bình), Low / Info (thấp / khuyến nghị).
- Mỗi vấn đề phải nêu rõ: Đường dẫn file, dòng code liên quan, phân tích rủi ro/nguyên nhân và đoạn code sửa chữa (fix snippet) mẫu sẵn sàng áp dụng.

## Acceptance Criteria

### Tính chính xác và Toàn diện
- [ ] 100% các file `.php` trong theme (`functions.php`, thư mục `inc/`, `template-parts/`, các file `archive-*.php`, `single-*.php`, `page-*.php`, `front-page.php`, `index.php`) đều được quét cú pháp và phân tích tĩnh.
- [ ] Tất cả các điểm lỗi được gắn chính xác file link và số dòng tương ứng.

### Chất lượng giải pháp khắc phục
- [ ] Mọi vấn đề mức Critical và High đều có giải pháp xử lý cụ thể kèm code snippet đạt chuẩn WordPress Coding Standards.
- [ ] Không tự động ghi đè hoặc làm thay đổi logic các file code của dự án trong quá trình đánh giá.

### Báo cáo bàn giao
- [ ] Tài liệu `FULL_PROJECT_AUDIT_REPORT.md` được tạo đầy đủ với bảng tóm tắt chỉ số sức khỏe dự án (Health Score) theo từng khía cạnh và checklist các hạng mục cần ưu tiên xử lý.

## 2026-09-25T07:17:19Z

Thực hiện rà soát và đánh giá chuyên sâu toàn diện logic nghiệp vụ của module "Kiểm tra điều kiện xét tuyển" (Eligibility Check Engine), phân tích ma trận luật chuyển đổi/liên thông, tính điểm tương thích, quy trình thu thập lead & xác minh bằng cấp, và đề xuất cải tiến UX/conversion rate mà không tự ý sửa đổi code gốc.

Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
Integrity mode: development

## Requirements

### R1. Kiểm định Ma trận luật xét tuyển & Chuyển đổi bằng cấp
- Rà soát ma trận luật trong `inc/eligibility-rules.php` và logic ánh xạ trong `inc/eligibility.php`.
- Đối chiếu với thực tế và Quy chế tuyển sinh Đại học / Liên thông / Đào tạo từ xa hiện hành của Bộ GD&ĐT:
  * Đầu vào THPT, Trung cấp nghề, Cao đẳng, Đại học (học VB2).
  * Quy tắc phân loại ngành đúng (ngành phù hợp), ngành gần và ngành khác; thời gian đào tạo tương ứng (1.5 năm, 2 năm, 2.5 năm, 4 năm).
  * Các trường hợp đặc thù (ngành sức khỏe, sư phạm, luật hoặc các ngành đòi hỏi chứng chỉ hành nghề).

### R2. Đánh giá Thuật toán tính điểm tương thích, Gợi ý chương trình & Miễn giảm tín chỉ
- Phân tích chi tiết công thức tính điểm điều kiện (thang điểm 100 hoặc trọng số % tương thích).
- Đánh giá thuật toán xếp hạng và gợi ý danh sách chương trình đào tạo phù hợp nhất với hồ sơ ứng viên (khớp ngành, vị trí cơ sở/campus, ngân sách học phí, hệ đào tạo).
- Kiểm tra logic ước tính số tín chỉ được miễn giảm và thời gian hoàn thành khóa học thực tế.
- Phát hiện các ca biên (edge-cases) nguy hiểm: người dùng chọn ngành không có trường đào tạo, điểm bị âm hoặc vượt 100%, lỗi chia cho 0, hoặc gợi ý chương trình không tương thích.

### R3. Đánh giá Phễu chuyển đổi ứng viên (Lead Capture Funnel & Xác minh nâng cao)
- Phân tích hành trình người dùng qua 2 tầng phễu:
  * Tầng 1 (Khảo sát sơ bộ): Điền form ẩn danh -> Nhận kết quả đánh giá sơ bộ.
  * Tầng 2 (Thu thập thông tin & Xác minh nâng cao): Nhập họ tên, SĐT, email -> Tải ảnh bằng cấp/bảng điểm -> Gửi thông báo Telegram Bot & Lưu CSDL lead.
- Đánh giá tính liền mạch, rào cản tâm lý người dùng (friction), tỷ lệ rớt phễu (drop-off rate) và tính toàn vẹn dữ liệu được gửi đến đội ngũ tư vấn tuyển sinh.

### R4. Đánh giá Trải nghiệm Người dùng (Wizard UX/UI, Validation & Mobile Flow)
- Đánh giá kiến trúc luồng câu hỏi đa bước (`template-parts/eligibility/wizard.php` và `results.php`).
- Kiểm tra tính thân thiện trên thiết bị di động, cơ chế kiểm tra tính hợp lệ dữ liệu nhập (client-side validation), khả năng tìm kiếm chọn nhanh ngành học/trường cũ (`data-search-select`).

### R5. Xuất Báo cáo Kiểm định Nghiệp vụ Toàn diện
- Tổng hợp toàn bộ phân tích thành tài liệu `ELIGIBILITY_BUSINESS_AUDIT.md` tại thư mục làm việc.
- Báo cáo phải bao gồm:
  1. Bản đồ luồng nghiệp vụ hoàn chỉnh (Business Flowchart / State Machine).
  2. Bảng phân tích Gap Analysis & Đánh giá mức độ phù hợp với thực tế tuyển sinh.
  3. Bộ 5+ kịch bản kiểm thử nghiệp vụ thực tế (Test Scenarios & Edge Cases Walkthrough).
  4. Đề xuất cải tiến thuật toán tính điểm và tối ưu tỷ lệ chuyển đổi (CRO) kèm mã nguồn / kiến trúc mẫu.

## Acceptance Criteria

### Tính toàn vẹn và Phủ kín
- [ ] 100% các tệp liên quan đến module kiểm tra điều kiện (`inc/eligibility.php`, `inc/eligibility-rules.php`, `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`, `page-eligible.php`) đều được phân tích nghiệp vụ sâu.
- [ ] Báo cáo chỉ rõ từng dòng code chịu trách nhiệm cho từng khâu logic nghiệp vụ.

### Độ sâu nghiệp vụ
- [ ] Báo cáo phân tích đối chiếu tối thiểu 5 hồ sơ ứng viên điển hình (Từ THPT lên ĐH từ xa, Từ CĐ đúng ngành lên ĐH, Từ CĐ khác ngành lên ĐH, Tốt nghiệp ĐH học VB2, Ứng viên ngành sức khỏe/sư phạm).
- [ ] Bảng Gap Analysis chỉ ra ít nhất 3 rủi ro nghiệp vụ hoặc điểm thiếu sót so với thực tế tuyển sinh tại Việt Nam.

### Báo cáo bàn giao
- [ ] Tài liệu `ELIGIBILITY_BUSINESS_AUDIT.md` được tạo đầy đủ, chuẩn cấu trúc Markdown, có giải pháp nâng cấp thuật toán cụ thể.
- [ ] Không tự ý sửa đổi code gốc của theme trong quá trình đánh giá.

## 2026-09-28T04:04:15Z

Thực hiện rà soát và đánh giá chuyên sâu toàn diện về logic nghiệp vụ, mô hình dữ liệu (Data Modeling & Architecture) và luồng tương tác thực tế của việc quản lý **Hệ đào tạo** (`training_type`) và **Trường đối tác** (`school`) trong hệ thống Liên Thông Đại Học.

Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
Integrity mode: development

## Requirements

### R1. Đánh giá Mô hình dữ liệu & Quan hệ thực thể (Data Architecture & Entity Modeling)
- Kiểm tra tính hợp lý của quan hệ tam giác: `School` (CPT) ⟷ `Major` (CPT) ⟷ `Program` (CPT - Thực thể trung gian) ⟷ `Training Type` (Taxonomy) ⟷ `Campus` (Taxonomy).
- Đánh giá ưu / nhược điểm của việc gán `training_type` vào `program` thay vì trực tiếp vào `school` hoặc tạo thành Post Type độc lập.
- Đối chiếu với thực tế tuyển sinh liên thông/từ xa/VB2 tại Việt Nam (một trường có nhiều cơ sở, nhiều đợt tuyển sinh, biểu phí và thời gian đào tạo khác biệt giữa các hệ).

### R2. Đánh giá Logic Phân loại, Tìm kiếm, Lọc & Điều hướng (Querying, Filtering & Taxonomy UX)
- Kiểm tra logic truy vấn `WP_Query` khi người dùng lọc trường theo hệ đào tạo, lọc ngành theo trường và hệ.
- Đánh giá các trang taxonomy archive (`taxonomy-training_type.php`, `archive-school.php`, `single-school.php`): cách hiển thị các hệ đào tạo mà trường đó thực sự tuyển sinh, các lỗi N+1 hoặc sai lệch dữ liệu tiềm ẩn.
- Đánh giá Breadcrumbs, Canonical URLs và cấu trúc URL/Slug thân thiện SEO cho các hệ và trường.

### R3. Đánh giá Quy chuẩn Pháp lý Tuyển sinh & Hiển thị Văn bằng (Regulatory Compliance & Degree Trust)
- Đối chiếu với Thông tư 27/2019/TT-BGDĐT (Quy định nội dung chính ghi trên văn bằng giáo dục đại học - không ghi hình thức đào tạo chính quy/từ xa/VLVH trên văn bằng tốt nghiệp).
- Kiểm tra tính chính xác và nhất quán của việc truyền thông giá trị bằng cấp giữa các hệ trên giao diện người dùng (Hero, Badge, Bằng cấp mẫu, FAQ).
- Quy định điều kiện mở ngành, chỉ tiêu tuyển sinh và cơ sở pháp lý đào tạo từ xa / liên thông của các trường đối tác.

### R4. Đánh giá Luồng Lead Routing & Tích hợp Tuyển sinh theo Trường/Hệ (CRM & Admissions Funnel)
- Kiểm tra logic phân luồng Lead (Lead Routing) khi người dùng đăng ký theo Trường và Hệ đào tạo cụ thể: dữ liệu gửi vào CRM (OnSchool, AUM, Telegram Bot) có đầy đủ mã trường, mã hệ, cơ sở hay không.
- Đánh giá khả năng mở rộng khi thêm trường mới, mở hệ đào tạo mới hoặc trường tạm dừng tuyển sinh một hệ cụ thể.

## Acceptance Criteria

### Tính toàn vẹn và Phủ kín
- [ ] Rà soát 100% các file liên quan đến quản lý Trường & Hệ (`inc/post-types.php`, `inc/acf-fields.php`, `inc/core/class-query-filters.php`, `single-school.php`, `archive-school.php`, `taxonomy-training_type.php`, `inc/leads/`).
- [ ] Báo cáo chỉ rõ từng dòng code chịu trách nhiệm cho các quan hệ thực thể, hook và filter liên quan.

### Độ sâu nghiệp vụ tuyển sinh
- [ ] Báo cáo chỉ ra ít nhất 3 điểm nghẽn (bottlenecks) hoặc rủi ro dữ liệu (Data Integrity Gaps) giữa Trường và Hệ đào tạo trong kiến trúc hiện tại.
- [ ] Cung cấp bảng ma trận đối chiếu nghiệp vụ tuyển sinh thực tế tại Việt Nam với mô hình CPT/Taxonomy hiện tại.

### Báo cáo bàn giao
- [ ] Xuất tài liệu đánh giá hoàn chỉnh `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` tại thư mục làm việc với các giải pháp / schema kiến trúc nâng cấp tối ưu.
- [ ] Không tự ý sửa đổi code gốc của theme trong quá trình đánh giá.
