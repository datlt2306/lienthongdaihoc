# Project Overview

## Tên dự án

- `lienthongdaihoc.com`

## Mục tiêu kinh doanh

- Xây dựng cổng tuyển sinh tập trung cho các chương trình liên thông đại học, văn bằng 2, vừa học vừa làm và đào tạo từ xa.
- Thu hút học viên qua SEO, landing page và nội dung tư vấn.
- Chuyển đổi traffic thành lead đăng ký tư vấn, cuộc gọi hotline và hồ sơ tuyển sinh.
- Tạo nền tảng dữ liệu có cấu trúc để vận hành tuyển sinh đa trường trên cùng một hệ thống.

## Đối tượng người dùng

- Học viên đã tốt nghiệp trung cấp, cao đẳng hoặc đại học muốn học tiếp lên bậc cao hơn.
- Học viên cần tìm chương trình phù hợp theo ngành, trường, hệ đào tạo, địa điểm và điều kiện đầu vào.
- Đội ngũ vận hành tuyển sinh, nhập liệu nội dung và chăm sóc lead nội bộ.

## KPI chính

- Organic traffic tổng thể và traffic theo landing page SEO.
- Số lượng lead mới theo ngày, tuần, tháng.
- Conversion rate từ trang chương trình, trang trường, trang ngành và form tư vấn.
- Số lượt gọi hotline, click Zalo, submit form Contact Form 7.
- Số lượt dùng công cụ kiểm tra điều kiện tuyển sinh.
- Số lượt xem chi tiết chương trình và số phiên có tương tác với chức năng so sánh.
- Tỷ lệ đồng bộ lead sang CRM thành công.

# Business Domain

## Nghiệp vụ

- Tập hợp thông tin tuyển sinh từ nhiều trường đại học liên kết.
- Chuẩn hóa thông tin trường, ngành và chương trình đào tạo thành cấu trúc dữ liệu đồng nhất.
- Tư vấn định hướng học tập cho học viên dựa trên nhu cầu, nền tảng học vấn và điều kiện đầu vào.
- Thu lead từ các điểm chạm nội dung và phân phối cho quy trình tư vấn tuyển sinh.

## Quy trình vận hành

1. Đội vận hành tạo và cập nhật dữ liệu `school`, `major`, `program`.
2. Nội dung được xuất bản thành archive page, detail page, landing page và bài hướng dẫn.
3. Học viên truy cập từ Google hoặc từ chiến dịch, sau đó tìm kiếm, lọc, so sánh hoặc kiểm tra điều kiện.
4. Học viên để lại thông tin qua form tư vấn hoặc form theo ngữ cảnh của chương trình.
5. Hệ thống lưu lead nội bộ vào bảng custom trong WordPress.
6. Lead được đưa vào hàng đợi đồng bộ CRM theo lịch nền.
7. Đội tư vấn tiếp nhận, chăm sóc và chuyển đổi thành hồ sơ tuyển sinh.

## Luồng dữ liệu

1. Nguồn dữ liệu ban đầu đến từ file import, dữ liệu thủ công và ACF fields.
2. Dữ liệu được lưu ở WordPress Post Types, taxonomies, post meta và option fields.
3. Các trang frontend truy vấn dữ liệu và render qua custom theme.
4. Form tư vấn gửi dữ liệu qua Contact Form 7 hoặc AJAX.
5. Lead được lưu vào bảng `wp_ltdh_leads`.
6. Eligibility checker lưu lịch sử vào bảng `wp_ltdh_eligibility_checks`.
7. Queue sync đẩy lead sang CRM.

# Technical Stack

## Frontend

- WordPress custom theme `lienthongdaihoc`.
- PHP template rendering server-side.
- Tailwind CSS chạy qua CDN và cấu hình trực tiếp trong `header.php`.
- JavaScript thuần cho các tương tác như so sánh chương trình và eligibility checker.
- Google Fonts:
  - `Be Vietnam Pro`
  - `Montserrat`
  - `Playfair Display`

## Backend

- WordPress 6.x.
- PHP 8.3 theo khai báo theme.
- Custom business logic đặt trong thư mục `inc/`.
- WordPress hooks, AJAX handlers, REST-style endpoints nội bộ và WP-CLI commands.

## Database

- MySQL hoặc MariaDB trên VPS.
- WordPress core tables.
- Custom tables:
  - `wp_ltdh_leads`
  - `wp_ltdh_eligibility_checks`

## Hosting

- Môi trường mục tiêu: VPS.
- Kiến trúc dự kiến: Linux VPS + web server + PHP-FPM + MySQL/MariaDB.
- Cần bật cron ổn định hoặc thay WP-Cron bằng server cron cho tác vụ đồng bộ lead.

## Tích hợp bên thứ ba

- Advanced Custom Fields.
- Rank Math SEO.
- Contact Form 7.
- ERPNext là CRM mục tiêu cần tích hợp.
- OnSchool và AUM tồn tại trong codebase cũ nhưng không còn là hướng tích hợp ưu tiên.

# Design System

## Màu sắc

- Primary: `#1E3A8A`
- Secondary text: `#0F172A`
- Accent: `#D97706`
- Hover/Dark blue: `#1D4ED8`
- Light background: `#F8FAFC`
- Hệ màu bổ trợ đang dùng thêm các tone `slate`, `blue`, `amber`, `emerald`.

## Typography

- Font nội dung chính: `Be Vietnam Pro`
- Font heading/display: `Montserrat`
- Font nhấn cảm xúc hoặc editorial: `Playfair Display`

## Layout

- Desktop-first nhưng đã có điều chỉnh mobile trong template.
- Sử dụng container rộng kiểu landing page, section-based, CTA rõ ràng.
- Archive page theo mô hình filter sidebar hoặc filter bar.
- Single page có cấu trúc nội dung + form lead + CTA cố định theo ngữ cảnh.

## UI Style

- Giữ nguyên hướng nhận diện hiện tại.
- Phong cách tin cậy, giáo dục, tuyển sinh, nhiều CTA.
- Sử dụng card, badge, gradient nhẹ, khối nội dung rõ ràng và trực tiếp.
- Trọng tâm UX là tra cứu nhanh, hiểu nhanh, đăng ký nhanh.

# Content Model

## Các loại dữ liệu

- `school`
  - Thông tin trường, logo, banner, mã trường, tên tiếng Anh, website, địa chỉ, hotline, thông tin tuyển sinh.
- `major`
  - Thông tin ngành, mã ngành, cơ hội nghề nghiệp, thị trường việc làm, ngành liên quan.
- `program`
  - Đơn vị tuyển sinh cốt lõi, liên kết với trường và ngành, có học phí, thời lượng, hồ sơ, điều kiện, FAQ, lịch học.
- `post`
  - Tin tức, cẩm nang, nội dung hỗ trợ SEO.
- `page`
  - Landing pages, giới thiệu, liên hệ, đăng ký, FAQ, eligibility page.
- Taxonomy
  - `training_type`
  - `campus`
  - `region`
  - `major_cat`

## Quan hệ dữ liệu

- Một `program` thuộc một `school`.
- Một `program` thuộc một `major`.
- Một `school` có nhiều `program`.
- Một `major` có nhiều `program`.
- Một `program` có thể gắn nhiều `campus`.
- Một `program` có thể gắn một hoặc nhiều ngữ nghĩa hệ đào tạo qua `training_type`.
- `major` có quan hệ ngành liên quan qua field `major_related`.

## Metadata

- `school`
  - `logo`, `school_banner`, `school_code`, `english_name`, `website`, `address`, `hotline`, `admission_info`, `contact_info`, `support_services`
- `major`
  - `major_code`, `career_opportunities`, `salary_info`, `job_market`, `major_related`
- `program`
  - `school_relationship`, `major_relationship`, `tuition_fee`, `duration`, `admission_requirements`, `required_documents`, `enrollment_period`, `hotline_override`, `program_benefits`, `why_choose_us`, `faq`, `schedule`, `target_students`, `degree_type`, `diploma_value`, `disadvantages`
- Global options
  - `global_hotline`, `global_zalo_url`, `global_messenger_url`, cấu hình CRM
- Vận hành
  - `admission_status` đang được dùng ở nhiều truy vấn dù chưa thấy mô tả đầy đủ trong tài liệu dữ liệu hiện tại.

# Functional Modules

## Danh sách module

- Core theme setup
- Content management
- Program search and filter
- School/major/program detail pages
- Compare programs
- Eligibility checker
- Lead capture
- CRM sync queue
- SEO engine
- Internal relationship sync
- WP-CLI setup and seeding
- Test runner

## Mô tả từng module

- `Core theme setup`
  - Khởi tạo theme supports, menu, assets, breadcrumbs, logo fallback và helper query cache.
- `Content management`
  - Đăng ký CPT và taxonomy từ JSON, nạp ACF fields từ JSON, hỗ trợ import và seed dữ liệu.
- `Program search and filter`
  - Tìm kiếm theo từ khóa, mở rộng synonym, lọc theo trường, ngành, hệ đào tạo và trạng thái tuyển sinh.
- `School/major/program detail pages`
  - Render chi tiết thực thể, nội dung SEO và CTA chuyển đổi theo từng bối cảnh.
- `Compare programs`
  - Cho phép thêm chương trình vào khay so sánh, tạo URL thân thiện SEO và hiển thị bảng so sánh.
- `Eligibility checker`
  - Thu thập thông tin học viên, chấm điểm phù hợp, trả về danh sách chương trình gợi ý và lưu lịch sử kiểm tra.
- `Lead capture`
  - Bắt dữ liệu từ Contact Form 7 và từ eligibility flow, lưu vào bảng lead nội bộ.
- `CRM sync queue`
  - Xử lý hàng đợi đồng bộ lead định kỳ. Hiện code còn adapter OnSchool/AUM; target state là thay bằng ERPNext adapter.
- `SEO engine`
  - Tạo dynamic title, description, schema JSON-LD, breadcrumb tinh chỉnh và SEO cho trang so sánh.
- `Internal relationship sync`
  - Đồng bộ quan hệ hai chiều giữa `program`, `school` và `major` thông qua metadata.
- `WP-CLI setup and seeding`
  - Tạo trang chuẩn, taxonomy mẫu, dữ liệu seed và hỗ trợ bootstrap hệ thống.
- `Test runner`
  - Có test script thủ công cho logic search/filter.

# SEO Strategy

## Cấu trúc nội dung

- Hub chính theo intent:
  - Trang trường
  - Trang ngành
  - Trang chương trình
  - Bài hướng dẫn tuyển sinh
  - Trang FAQ và policy
- Mô hình nội dung nên theo cụm chủ đề:
  - `Trường` -> `Ngành` -> `Chương trình` -> `Hướng dẫn hồ sơ` -> `Câu hỏi thường gặp`
- Ưu tiên từ khóa có ý định chuyển đổi cao:
  - liên thông đại học
  - văn bằng 2
  - học từ xa
  - tuyển sinh theo trường
  - tuyển sinh theo ngành
  - điều kiện đầu vào

## Landing pages

- Landing page theo trường.
- Landing page theo ngành.
- Landing page theo hệ đào tạo.
- Landing page theo khu vực hoặc campus.
- Landing page theo đối tượng học viên.
- Landing page so sánh giữa các chương trình phổ biến.

## Internal links

- Từ `school` link sang các `program` liên quan.
- Từ `major` link sang các `program` và `school` phù hợp.
- Từ `program` link ngược sang `school`, `major`, bài hướng dẫn và bài FAQ.
- Từ blog/guide link về trang chương trình có khả năng chuyển đổi cao.
- Dùng breadcrumb, related content và CTA context-based để tăng depth phiên.

# Coding Standards

## Naming Convention

- Slug dùng lowercase, không dấu, phân tách bằng dấu gạch ngang.
- PHP function theo prefix `ltdh_`.
- Post type, taxonomy và meta key theo snake_case hoặc lowercase nhất quán.
- Tránh đặt tên business field mơ hồ; ưu tiên tên phản ánh trực tiếp nghiệp vụ tuyển sinh.

## Folder Structure

- `themes/lienthongdaihoc/`
  - `inc/`: business logic và hooks
  - `assets/css/`: stylesheet
  - `assets/js/`: JavaScript modules
  - `template-parts/`: partials tái sử dụng
  - `tests/`: test script thủ công
  - `sources/`: file dữ liệu nguồn

## Security Rules

- Luôn sanitize và escape dữ liệu đầu vào/đầu ra theo chuẩn WordPress.
- Nonce bắt buộc cho AJAX và form action có thao tác dữ liệu.
- Không hardcode token CRM trong template hoặc repo public.
- Không lộ thông tin lead, token, endpoint trong frontend.
- Kiểm soát quyền admin/editor khi thêm tính năng import, sync và cấu hình CRM.

## Performance Rules

- Tận dụng cache truy vấn cho danh sách nặng.
- Hạn chế `posts_per_page = -1` trên môi trường production nếu dữ liệu tăng lớn.
- Giảm phụ thuộc vào Tailwind CDN về lâu dài; ưu tiên build asset tĩnh khi hệ thống ổn định.
- Ảnh trường và banner cần tối ưu dung lượng.
- Dùng server cron thay WP-Cron cho queue sync ở production VPS.

# AI Agent Rules

## Những gì AI được phép làm

- Đọc codebase và cập nhật tài liệu, template, nội dung kỹ thuật.
- Tạo hoặc chỉnh sửa module theo đúng kiến trúc WordPress hiện tại.
- Chuẩn hóa dữ liệu, gợi ý cấu trúc content, SEO, schema và internal linking.
- Viết script import, kiểm thử, migration và adapter tích hợp CRM mới.
- Đề xuất refactor khi giúp tăng ổn định, bảo mật hoặc hiệu năng.

## Những gì AI không được phép làm

- Không tự ý thay đổi dữ liệu production hoặc xóa dữ liệu hiện có.
- Không tự ý thay đổi cấu trúc CRM đích nếu chưa xác nhận mapping với đội vận hành.
- Không xóa adapter cũ hoặc thay quy trình sync nếu chưa có kế hoạch chuyển đổi.
- Không commit secret, token, endpoint thật vào mã nguồn.
- Không thay đổi brand direction hoặc UX chính khi chưa có yêu cầu rõ ràng.

# Development Roadmap

## Phase 1

- Hoàn thiện dữ liệu cốt lõi cho `school`, `major`, `program`.
- Chuẩn hóa slug, metadata và admission status.
- Hoàn thiện landing pages SEO theo trường, ngành, hệ đào tạo.
- Tăng chất lượng nội dung chương trình, FAQ, schema và internal link.
- Rà soát conversion points: form, hotline, Zalo, CTA.

## Phase 2

- Thay thế hướng tích hợp CRM cũ bằng ERPNext adapter.
- Chuẩn hóa queue sync, retry policy, log lỗi và monitoring đồng bộ lead.
- Nâng cấp eligibility checker về rule engine, scoring và analytics.
- Bổ sung tracking cho toàn bộ hành vi chuyển đổi quan trọng.

## Phase 3

- Xây dashboard vận hành cho lead, nguồn traffic và hiệu suất nội dung.
- Thêm cá nhân hóa gợi ý chương trình theo hồ sơ học viên.
- Mở rộng tự động hóa chăm sóc lead và phân phối theo đội tư vấn.
- Nghiên cứu AI assistant nội bộ cho tra cứu chương trình và hỗ trợ biên tập nội dung.

# Đánh giá sau khi chuẩn hóa

## Những điểm còn thiếu

- Chưa có tài liệu chính thức cho mapping dữ liệu từ lead sang ERPNext.
- Chưa có định nghĩa đo lường chi tiết cho từng KPI.
- Chưa có quy chuẩn rõ về trạng thái tuyển sinh như `tuyen-sinh`, `tam-ngung`, `sap-mo`.
- Chưa có tài liệu môi trường production, backup, deploy và rollback.
- Chưa thấy test automation đầy đủ ngoài search test thủ công.
- Chưa có schema tài liệu hóa đầy đủ cho taxonomy, option fields và dữ liệu import.

## Những rủi ro có thể phát sinh

- Codebase hiện vẫn mang logic tích hợp CRM cũ, dễ gây lệch giữa tài liệu và triển khai thực tế.
- WP-Cron trên VPS có thể không ổn định nếu không thay bằng cron hệ thống.
- Dùng Tailwind CDN trong production làm giảm khả năng kiểm soát hiệu năng và versioning.
- Nhiều truy vấn WordPress đang tải toàn bộ dữ liệu, có thể chậm khi số chương trình tăng mạnh.
- Custom tables đang xử lý lead nhạy cảm nhưng chưa thấy chiến lược lưu vết, masking và retention policy.
- Eligibility logic phụ thuộc nhiều vào metadata, nếu nhập liệu không chuẩn sẽ làm sai kết quả gợi ý.

## Những quyết định kiến trúc cần xác nhận

- Xác nhận mô hình tích hợp ERPNext:
  - Ghi lead một chiều từ WordPress sang ERPNext hay đồng bộ hai chiều.
- Xác nhận kiến trúc asset frontend:
  - Giữ Tailwind CDN hay chuyển sang build pipeline cục bộ.
- Xác nhận chiến lược cron:
  - Dùng WP-Cron hay system cron trên VPS.
- Xác nhận phạm vi analytics:
  - Chỉ đo lead cơ bản hay cần event tracking đầy đủ cho search, compare, eligibility, CTA.
- Xác nhận data governance:
  - Ai được sửa dữ liệu trường, ngành, chương trình và quy trình kiểm duyệt trước publish.
- Xác nhận roadmap CRM migration:
  - Thay trực tiếp adapter cũ hay chạy song song giai đoạn chuyển tiếp.

# Assumptions

- Dự án hiện vận hành như một WordPress monolith với custom theme là trung tâm.
- Brand direction hiện tại được giữ nguyên.
- ERPNext là đích tích hợp trong tương lai gần nhưng chưa được triển khai trong codebase hiện tại.
- KPI “tất cả” được hiểu là cần theo dõi cả traffic, engagement, lead và operational conversion.
