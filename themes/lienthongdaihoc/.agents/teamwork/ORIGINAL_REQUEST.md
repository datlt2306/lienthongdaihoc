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

## 2026-10-01T08:50:13Z

# Nhiệm vụ: Tái cấu trúc Kiến trúc thông tin (Information Architecture), Thuật ngữ & Templates dự án lienthongdaihoc.com

Working directory: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`
Integrity mode: development

Bạn đang làm việc trên một dự án phần mềm ĐÃ XÂY DỰNG HOÀN THIỆN: `lienthongdaihoc.com` (WordPress theme).
Nhiệm vụ KHÔNG PHẢI là đập đi xây lại trang web, mà là:
1. Hiểu sâu kiến trúc hiện tại, mô hình nội dung, taxonomy, templates, URLs, cấu trúc SEO, UI và mã nguồn thực tế.
2. Thực hiện tái cấu trúc có kiểm soát Kiến trúc Thông tin (IA) xoay quanh PHẠM VI NGHIỆP VỤ DUY NHẤT của website: **LIÊN THÔNG ĐẠI HỌC**.

---

## 1. PHẠM VI NGHIỆP VỤ CỐT LÕI (CRITICAL)
Website CHỈ TẬP TRUNG DUY NHẤT vào:
> **LIÊN THÔNG ĐẠI HỌC**

TUYỆT ĐỐI KHÔNG đưa vào hoặc duy trì các loại hình tuyển sinh không liên quan như:
- Văn bằng 2
- Đại học mới
- Đại học chính quy như một sản phẩm tuyển sinh riêng biệt độc lập
- Các loại tuyển sinh đại học chung khác

Trang web KHÔNG PHẢI là cổng thông tin tuyển sinh đại học tổng hợp. Mục đích cốt lõi:
> Giúp người học tìm kiếm, khám phá và so sánh các cơ hội/chương trình liên thông đại học.

---

## 2. MÔ HÌNH NGHIỆP VỤ MỤC TIÊU
```text
Trường Đại học (University)
    ↓
Thông tin tuyển sinh Liên thông
    ↓
Hình thức học (Study Mode)
    ├── Chính quy
    ├── Vừa học vừa làm
    └── Từ xa
    ↓
Ngành học (Major / Field)
    ↓
Cơ hội / Chương trình tuyển sinh Liên thông cụ thể (Specific Liên thông admission offering)
```

Phân biệt then chốt:
- **"Liên thông"**: Là loại hình tuyển sinh tổng thể / phạm vi bao trùm toàn bộ website.
- **"Chính quy / Vừa học vừa làm / Từ xa"**: Là các hình thức học / phương thức đào tạo BÊN TRONG tuyển sinh liên thông. KHÔNG được coi là các sản phẩm tuyển sinh độc lập tương đương với "Liên thông".

---

## 3. THUẬT NGỮ & THỰC THỂ
Khảo sát toàn bộ thuật ngữ hiện có trong dự án:
- Hệ đào tạo, Chương trình, Chương trình đào tạo, Loại chương trình, Hình thức đào tạo, Hình thức học, Loại tuyển sinh, v.v.
- Đơn vị hữu ích nhỏ nhất hiển thị ra công chúng là một CƠ HỘI TUYỂN SINH CỤ THỂ, ví dụ:
  > Đại học ABC — Liên thông ngành Kế toán — Từ xa
- Không nhầm lẫn giữa "Chương trình đào tạo học thuật" (curriculum) với "Cơ hội tuyển sinh liên thông cụ thể" (admission offering).
- Giữ nguyên thực thể nếu cấu trúc hiện tại (`program`, `school`, `major`) đã phản ánh đúng, chỉ hiệu chỉnh nhãn, phân cấp ngữ nghĩa và quan hệ.

---

## 4. QUY TRÌNH THỰC HIỆN BẮT BUỘC (PHASE 1: AUDIT ONLY)
Tuyệt đối KHÔNG sửa code ngay lập tức.
Quy trình:
```text
1. Khảo sát & Đọc hiểu (toàn bộ code, *.md, taxonomy, CPTs, templates, URLs, breadcrumbs)
2. Kiểm toán (Audit) & Lập bảng ánh xạ Current → Target
3. Nhận diện rủi ro (đặc biệt là SEO, URL, template break)
4. Đề xuất phương án can thiệp tối thiểu an toàn nhất
5. Triển khai sửa đổi có kiểm soát
6. Kiểm thử cú pháp (php -l), query loops, giao diện
7. Xác minh SEO, URLs, Canonicals, 301 Redirects
8. Hoàn thiện Báo cáo tổng kết đầy đủ (Mục A đến H)
```

Bảng ánh xạ kiểm toán bắt buộc:
```text
CURRENT ENTITY
CURRENT NAME
CURRENT PURPOSE
CURRENT TAXONOMY
CURRENT RELATIONSHIPS
CURRENT URL
CURRENT TEMPLATE
TARGET CONCEPT
REQUIRED CHANGE
```

---

## 5. BẢO TOÀN SEO & URLS (CỰC KỲ QUAN TRỌNG)
- Website đã hoàn thiện và có thể đã lập chỉ mục URLs.
- Không tự ý thay đổi slug / URLs công khai.
- Nếu bắt buộc phải đổi URL: Phải có 301 Redirect tương ứng (`OLD URL → 301 REDIRECT → NEW URL`), cập nhật canonical, sitemap, breadcrumbs và liên kết nội bộ.
- Chuẩn hóa SEO Title / H1 / Meta Description:
  - `Liên thông [Hình thức học]` (ví dụ: Liên thông từ xa, Liên thông chính quy)
  - `Liên thông [Ngành]`
  - `Liên thông [Trường]`

---

## 6. TEMPLATES, UI & ĐIỀU HƯỚNG
- **Menu điều hướng**:
  Trang chủ → Liên thông (Chính quy, Vừa học vừa làm, Từ xa) → Ngành học → Trường đại học → Kiến thức liên thông → Tư vấn.
  Không tạo các lối vào trùng lặp trỏ về cùng nội dung.
- **Trang Archive Hình thức học** (`taxonomy-training_type.php` hoặc tương đương):
  Tiêu đề & nội dung phải thể hiện rõ: "Liên thông từ xa", "Liên thông vừa học vừa làm", "Liên thông chính quy". Danh sách hiển thị các cơ hội tuyển sinh cụ thể.
- **Trang Single Program** (`single-program.php`):
  Hiển thị chuẩn: Trường, Ngành, Hình thức học, Đối tượng, Điều kiện, Thời gian học, Học phí, Địa điểm/Phương thức, Bằng cấp, Hồ sơ, Thời gian tuyển sinh, Form đăng ký tư vấn.
- **Trang Ngành & Trang Trường**: Gom nhóm các cơ hội tuyển sinh liên thông tương ứng.
- **Bộ lọc**: Loại bỏ các bộ lọc vô nghĩa (ví dụ lọc "Loại chương trình: Liên thông"). Chỉ giữ các bộ lọc thực tế: Hình thức học, Ngành, Trường, v.v.
- **Giữ nguyên thiết kế UI**: Không thay đổi CSS/layout một cách vô cớ.

---

## 7. BÁO CÁO NGHIỆM THU CUỐI CÙNG (CÁC MỤC A ĐẾN H)
Sau khi hoàn thành, xuất báo cáo đầy đủ:
- **A. Current architecture**: Entity, Taxonomy, Relationship, URL, Template.
- **B. Problems found**: Các xung đột thực tế phát hiện trong mã nguồn.
- **C. Target architecture**: Sơ đồ kiến trúc và phân cấp chuẩn hóa.
- **D. Files changed**: Từng tệp đã sửa và lý do.
- **E. Database/content changes**: Migration, taxonomies, fields.
- **F. URL changes**: Danh sách chuyển hướng 301 (hoặc ghi rõ không đổi URL công khai nào).
- **G. SEO impact**: Canonicals, redirects, sitemaps, breadcrumbs, internal links.
- **H. Verification**: Kết quả kiểm thử toàn diện từng trang, bộ lọc, navigation và mã PHP.

## 2026-10-01T09:08:12Z

Thực hiện đợt refactor có kiểm soát kiến trúc thông tin, taxonomy, routing và dữ liệu hiển thị cho website WordPress `lienthongdaihoc.com` nhằm chuẩn hoá 100% phạm vi phục vụ duy nhất là **Liên thông đại học**, tuyệt đối bảo toàn 3 CPT cốt lõi (`school`, `major`, `program`), đổi thuật ngữ hiển thị thành **Hình thức học** (`Từ xa`, `Vừa học vừa làm`), và xử lý an toàn dữ liệu ngoài phạm vi không làm hỏng SEO hay mất dữ liệu.

Working directory: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`
Integrity mode: development

## Requirements

### R1. Kiểm toán Dữ liệu CPT & Xử lý An toàn Ngoài Phạm Vi
- Chạy script PHP/WP-CLI kiểm toán (Audit Script) để scan và phân loại toàn bộ record của CPT `program`, `school`, `major` và xuất file báo cáo (`audit_report.json` hoặc log chi tiết).
- Với từng record `program`:
  - Record thuộc **Liên thông**: Giữ nguyên public, chuẩn hoá taxonomy `training_type` thành một trong hai hình thức: `Từ xa` hoặc `Vừa học vừa làm`.
  - Record **NGOÀI phạm vi** (Văn bằng 2, Chính quy, Tuyển sinh mới THPT, Cao đẳng online...): Chuyển `post_status` sang `draft` (hoặc `private`), tuyệt đối không xóa cứng (hard-delete) khỏi database.
  - Record không chắc chắn: Đưa vào danh sách "cần review thủ công", tạm thời không hiển thị ở luồng tuyển sinh liên thông chính.

### R2. Bảo toàn Cốt lõi 3 CPT & Data Flow Chuẩn
- Giữ nguyên 3 CPT: `school`, `major`, `program`. Tuyệt đối không tạo thêm CPT mới (`course`, `admission`, `intake`, v.v.).
- Chuẩn hoá quan hệ dữ liệu:
  - Trường (`school`) → Các `program` tuyển sinh Liên thông thực tế → Thuộc tính (`major`, `training_type`).
  - Trang trường và trang ngành phải query các chương trình Liên thông thực tế thay vì dựa vào term gắn trực tiếp trên `school`.
- Taxonomy `campus`: kiểm tra và cô lập term `Online` không để xuất hiện như một cơ sở vật lý trong bộ lọc hay giao diện.

### R3. Chuẩn hoá Thuật ngữ Taxonomy & Routing
- Taxonomy `training_type`: Đổi nhãn hiển thị frontend từ "Hệ đào tạo" thành **"Hình thức học"** (gồm `Từ xa` và `Vừa học vừa làm`). Giữ nguyên slug URL `/he-dao-tao/` nếu cần để bảo vệ URL đã index SEO.
- Tuyệt đối không tạo thêm taxonomy "Loại tuyển sinh" hay bộ lọc thừa "Liên thông".
- Rà soát route `/chuong-trinh/`: Không giữ 301 gượng ép sang `/he-dao-tao/tu-xa/` nếu không có lý do SEO legacy; xử lý routing sạch và an toàn cho các URL đang index.

### R4. Tinh chỉnh Trang Chủ, Navigation & Bộ lọc
- Trang chủ: Điều chỉnh hero, sections, cards và CTAs để toàn bộ thông điệp hướng về tìm kiếm chương trình Liên thông theo Hình thức học, Ngành và Trường. Loại bỏ mọi yếu tố quảng bá ngoài phạm vi.
- Navigation Header & Footer: Chuẩn hoá cấu trúc menu:
  - Trang chủ
  - Liên thông (Từ xa, Vừa học vừa làm)
  - Ngành học
  - Trường đại học
  - Kiến thức liên thông
  - Tư vấn
  Xoá bỏ các link trùng lặp cùng trỏ về một archive.
- Bộ lọc Search/Filter: Chỉ lọc theo Hình thức học (`Từ xa`, `Vừa học vừa làm`), Ngành, Trường và thuộc tính tuyển sinh liên quan.

### R5. Template & Card Presentation
- Card `program` và template `single-program.php`: Giữ nguyên kiến trúc file hiện có, hiển thị rõ ràng cơ hội tuyển sinh "Liên thông ngành [Tên ngành] - [Hình thức học] tại [Trường]", kèm điều kiện, học phí, đối tượng, thời gian học và trạng thái tuyển sinh.
- Các trang archive Hình thức học (`/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`): Chỉ query các `program` Liên thông hợp lệ.

## Acceptance Criteria

### Data & Scope Integrity
- [ ] Không có record nào không phải Liên thông xuất hiện trên frontend public (homepage, archives, search, related).
- [ ] 100% record ngoài phạm vi được chuyển `post_status` an toàn sang draft/private; không có record nào bị xoá vĩnh viễn khỏi database. Có file log phân loại minh bạch.

### CPT & Taxonomy Architecture
- [ ] Số lượng CPT giữ nguyên đúng 3 (`school`, `major`, `program`), không phát sinh CPT thừa.
- [ ] Nhãn frontend hiển thị của `training_type` là "Hình thức học" trên toàn hệ thống UI.
- [ ] Không có taxonomy "Loại tuyển sinh" hay filter thừa.

### Navigation, Routing & Frontend
- [ ] Cây menu điều hướng thống nhất, không còn 2 link khác nhau cùng trỏ về một archive.
- [ ] Trang chủ 100% đồng nhất thông điệp tuyển sinh Liên thông.
- [ ] Không có lỗi 301 bất hợp lý từ `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`.
- [ ] Toàn bộ trang web (Single, Archive, Homepage, Search) không phát sinh lỗi PHP (Notice/Warning/Fatal) hay JS Console error.
