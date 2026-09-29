# Dispatch Instructions

## 2026-09-25T07:18:44Z

You are the Project Orchestrator (orchestrator_2) for the project audit task.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_2
The project root is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
The authoritative request is in /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (under section ## 2026-09-25T07:17:19Z).

User Request Details:
Thực hiện rà soát và đánh giá chuyên sâu toàn diện logic nghiệp vụ của module "Kiểm tra điều kiện xét tuyển" (Eligibility Check Engine), phân tích ma trận luật chuyển đổi/liên thông, tính điểm tương thích, quy trình thu thập lead & xác minh bằng cấp, và đề xuất cải tiến UX/conversion rate mà không tự ý sửa đổi code gốc.

Integrity mode: development
Target output: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md

Key Requirements:
R1. Kiểm định Ma trận luật xét tuyển & Chuyển đổi bằng cấp:
- Rà soát ma trận luật trong `inc/eligibility-rules.php` và logic ánh xạ trong `inc/eligibility.php`.
- Đối chiếu với thực tế và Quy chế tuyển sinh Đại học / Liên thông / Đào tạo từ xa hiện hành của Bộ GD&ĐT (THPT, Trung cấp nghề, CĐ, ĐH VB2; ngành đúng, ngành gần, ngành khác; thời gian 1.5, 2, 2.5, 4 năm; ngành đặc thù sức khỏe, sư phạm, luật...).

R2. Đánh giá Thuật toán tính điểm tương thích, Gợi ý chương trình & Miễn giảm tín chỉ:
- Phân tích chi tiết công thức tính điểm điều kiện (thang 100 / % tương thích).
- Đánh giá thuật toán xếp hạng và gợi ý chương trình phù hợp nhất (khớp ngành, campus, ngân sách học phí, hệ đào tạo).
- Kiểm tra logic ước tính số tín chỉ miễn giảm & thời gian hoàn thành.
- Phát hiện các edge-cases nguy hiểm: ngành không có trường đào tạo, điểm âm hoặc vượt 100%, chia cho 0, gợi ý chương trình không tương thích.

R3. Đánh giá Phễu chuyển đổi ứng viên (Lead Capture Funnel & Xác minh nâng cao):
- Hành trình người dùng 2 tầng phễu (Tầng 1 khảo sát sơ bộ -> Tầng 2 thu thập thông tin, upload ảnh bằng cấp/bảng điểm, Telegram bot, lưu CSDL lead).
- Đánh giá friction, tỷ lệ drop-off, tính toàn vẹn dữ liệu gửi tới tư vấn tuyển sinh.

R4. Đánh giá Trải nghiệm Người dùng (Wizard UX/UI, Validation & Mobile Flow):
- Kiến trúc luồng câu hỏi đa bước (`template-parts/eligibility/wizard.php` và `results.php`).
- Mobile friendliness, client-side validation (`assets/js/eligibility.js`), tìm kiếm nhanh ngành/trường cũ (`data-search-select`).

R5. Xuất Báo cáo Kiểm định Nghiệp vụ Toàn diện:
- Tạo `ELIGIBILITY_BUSINESS_AUDIT.md` tại thư mục làm việc gốc.
- Bao gồm: (1) Bản đồ luồng nghiệp vụ (Business Flowchart / State Machine), (2) Bảng Gap Analysis & đánh giá mức độ phù hợp với thực tế tuyển sinh (chỉ ra ít nhất 3 rủi ro nghiệp vụ/điểm thiếu sót), (3) Bộ 5+ kịch bản kiểm thử nghiệp vụ thực tế (THPT lên ĐH từ xa, CĐ đúng ngành lên ĐH, CĐ khác ngành lên ĐH, ĐH học VB2, ứng viên sức khỏe/sư phạm), (4) Đề xuất cải tiến thuật toán tính điểm và tối ưu tỷ lệ chuyển đổi (CRO) kèm mã nguồn / kiến trúc mẫu.

Acceptance Criteria:
- 100% các file liên quan (`inc/eligibility.php`, `inc/eligibility-rules.php`, `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`, `page-eligible.php`) được phân tích nghiệp vụ sâu, chỉ rõ từng dòng code chịu trách nhiệm cho từng khâu logic nghiệp vụ.
- Đối chiếu tối thiểu 5 hồ sơ ứng viên điển hình.
- Gap Analysis chỉ ra ít nhất 3 rủi ro nghiệp vụ / điểm thiếu sót.
- Báo cáo ELIGIBILITY_BUSINESS_AUDIT.md đầy đủ, chuẩn markdown, có giải pháp nâng cấp thuật toán cụ thể.
- Tuyệt đối KHÔNG tự ý sửa đổi code gốc của theme.

## 2026-09-25T08:03:25Z

The server has restarted. Please resume orchestrating and driving the business logic audit for the Eligibility Check Engine module to completion. Please check the status of your subagents (explorer_rules_1, explorer_scoring_1, explorer_funnel_ux_1) and revive/resume them or continue Phase 1 synthesis and Phase 2/3 authoring & gate verification, ultimately producing ELIGIBILITY_BUSINESS_AUDIT.md.
