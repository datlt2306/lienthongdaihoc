## 2026-10-06T12:20:36Z
Bạn là teamwork_preview_worker phụ trách Milestone 5 (R5): Biên soạn và xuất bản báo cáo kiểm định 360 độ toàn diện `WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md` tại thư mục gốc của theme:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md`

Working directory của bạn:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_report_1/`

Tệp yêu cầu gốc bắt buộc đọc trước tiên:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md` (chú trọng đặc biệt header `## 2026-10-06T11:50:54Z`).

Các báo cáo đầu vào từ 3 Explorer chuyên trách (ĐÃ HOÀN TẤT, BẠN CẦN ĐỌC KỸ TOÀN BỘ):
1. Explorer 1 (Content & Data Integrity):
   - `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_data_1/report.md`
   - `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_data_1/handoff.md`
2. Explorer 2 (Core Features & Funnels):
   - `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_features_1/report.md`
   - `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_features_1/handoff.md`
3. Explorer 3 (UX/UI, Responsive & Technical SEO/Security):
   - `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_ux_seo_1/report.md`
   - `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_ux_seo_1/handoff.md`

Kỹ năng áp dụng:
Đọc và tuân thủ kỹ năng `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/full-output-enforcement/SKILL.md` (tuyệt đối không cắt ngắn, không placeholder, xuất tài liệu đầy đủ chi tiết, đầy đủ bảng ma trận và mã nguồn mẫu).

Quyền sở hữu file (Write ownership):
- Bạn sở hữu duy nhất file `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md` và các file trong thư mục của bạn `.agents/teamwork/worker_report_1/`.
- TUYỆT ĐỐI KHÔNG sửa đổi các file mã nguồn PHP/JS/CSS gốc của theme (chỉ tạo file báo cáo audit report).

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Yêu cầu cấu trúc của file `WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md`:
1. **Tóm tắt điều hành & Chỉ số sức khỏe tổng thể (Executive Summary & Health Score 360°)**:
   - Tổng quan mục tiêu, phạm vi khảo sát (100% templates: front-page, single-school, single-program, single-major, page-eligible, page-compare-program, archive-school, taxonomy-training_type, single, single-guide).
   - Bảng điểm sức khỏe 4 trụ cột (Data Integrity: 54/100, Core Features: 72/100, UX/UI & Responsive: 78/100, Technical SEO & Security: 74/100 -> Điểm tổng thể).
2. **Kiểm định R1: Tính toàn vẹn nội dung & Mô hình dữ liệu**:
   - Vấn đề số tài khoản ngân hàng trong trường hotline (15/20 trường), link PDF đơn UTC gán cứng 100% chương trình, rò rỉ kịch bản nội bộ nhân viên TVTS, lỗi ngày tháng Excel datetime và cắt cụt số thực học phí, thiếu vắng khối văn bằng & Thông tư 27/2019/TT-BGDĐT trên single-program, liên kết Zalo Group rỗng, cân bằng hệ đào tạo (94 Từ xa vs 1 Vừa học vừa làm), tồn dư từ khóa ngoài phạm vi (Văn bằng 2).
   - Đo lường định lượng tỷ lệ hoàn thiện trường dữ liệu.
3. **Kiểm định R2: Chức năng cốt lõi & Phễu chuyển đổi**:
   - Eligibility Quiz: Phân tích chi tiết quy tắc văn bằng, thuật toán tính điểm tương thích, lỗi DOM ID mismatch ẩn danh sách gợi ý thay thế, thiếu logic tính miễn giảm tín chỉ thực tế.
   - Program Comparison: Bảng đối sánh desktop vs stacked cards mobile, xung đột mobile bottom giữa khay so sánh (Compare Tray) và Fixed Mobile Action Bar, lỗi bỏ rơi tham số `program_id` khi chuyển sang form đăng ký tư vấn.
   - Filters & Search: AJAX filtering làm đứt gãy phân trang (ghost pagination), đồng bộ URL query parameters.
   - Lead Capture: Lỗ hổng bảo mật CSRF (thiếu `wp_nonce_field` và `wp_verify_nonce` trên native form), regex số điện thoại chưa chặt chẽ, thiếu kênh thông báo `wp_mail()`.
   - Floating CTA & Navigation: Kiểm tra toàn bộ nút Hotline, Zalo, Sticky bar.
4. **Kiểm định R3: Trải nghiệm người dùng, Responsive & Tối ưu chuyển đổi**:
   - Đánh giá responsive đa kích thước màn hình (Mobile 375px–430px, Tablet 768px–1024px, Desktop).
   - Phân tích xung đột z-index và layout shift (CLS).
   - Thất bại độ tương phản WCAG AA (nút xanh `#00a2f4`, text slate-500 trên nền tối) và touch targets < 48px.
   - Đánh giá thông điệp chuyển đổi, trust signals và friction points.
5. **Kiểm định R4: Technical SEO, Schema Markup, Bảo mật & Hiệu năng**:
   - Lỗi Canonical Rank Math ép toàn bộ URL taxonomy term sạch về trang gốc `/hinh-thuc-dao-tao/`.
   - Kiểm định Schema JSON-LD (Course, EducationalOrganization, FAQPage, BreadcrumbList): thiếu `offers`, `hasCourseInstance` khi bật Rank Math.
   - Lỗi trùng lặp H1, thẻ H1 ẩn `sr-only`, nhảy cóc phân cấp heading, trùng lặp 2 hàng Breadcrumbs.
   - Kiểm tra an ninh (ABSPATH guard, SQLi `wpdb->prepare`) và tối ưu assets (CSS kép, Google Fonts blocking, DB queries không cache).
6. **Ma trận tổng hợp lỗi & Phân loại mức độ ưu tiên (P0 -> P3 Comprehensive Matrix)**:
   - Bảng phân loại chi tiết từng vấn đề: ID, Hạng mục, Mức độ nghiêm trọng (P0 Blocker, P1 High, P2 Medium, P3 Low), Tên lỗi, Tệp & Dòng code liên quan, Tác động nghiệp vụ / rủi ro kỹ thuật.
7. **Kế hoạch hành động kỹ thuật & Mã nguồn khắc phục mẫu (Technical Remediation Plan & Ready-to-use Code Fixes)**:
   - Cung cấp mã nguồn sửa chữa mẫu chuẩn WordPress Coding Standards cho 100% các lỗi P0 và P1 (kèm các lỗi P2 quan trọng).
   - Phân chia thành 3 giai đoạn: Giai đoạn 1 (Khắc phục ngay lập tức trong 24h - P0), Giai đoạn 2 (Tái cấu trúc cốt lõi trong 48h - P1), Giai đoạn 3 (Tối ưu hóa UX, SEO & Performance trong 7 ngày - P2/P3).
8. **Checklist nghiệm thu (Acceptance Checklist)**.

Sau khi hoàn tất việc tạo file `WEBSITE_COMPREHENSIVE_AUDIT_REPORT.md`, bạn hãy viết file `handoff.md` trong thư mục của mình và gửi tin nhắn (send_message) báo cáo cho orchestrator_6 (`61a39739-d3ca-49a4-bab5-08679ea1dc41`).
