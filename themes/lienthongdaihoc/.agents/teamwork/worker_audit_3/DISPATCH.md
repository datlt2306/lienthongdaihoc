## 2026-09-28T04:21:02Z

You are worker_audit_3, a teamwork_preview_worker agent.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_audit_3/
You MUST create your directory and state files (BRIEFING.md, progress.md) within your working directory.

MANDATORY FIRST STEP:
Read the authoritative user request at:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
Specifically review the requirements under timestamp `2026-09-28T04:04:15Z`.

Read the project scope document:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_3/PROJECT.md

Read the authoritative research and analysis reports from the 3 Explorers:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_arch_1/analysis.md
   and handoff.md
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_query_1/analysis.md
   and handoff.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_compliance_crm_1/analysis.md
   and handoff.md

YOUR DELIVERABLE:
Write the complete, authoritative, and exhaustive audit report to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md

FILE OWNERSHIP:
You own exclusively:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md
- All files inside your working directory /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_audit_3/

STRICT CONSTRAINT:
- ZERO modification of theme source code (PHP, JS, CSS, JSON). Do NOT modify existing theme files. This is strictly an audit report deliverable.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

DELIVERABLE SPECIFICATION & STRUCTURE:
Write a comprehensive report in Vietnamese, using professional technical engineering standards. The report must contain all 7 sections:
- Phần 1: Executive Summary & Bảng chỉ số sức khỏe nghiệp vụ (Health Scorecard)
  * Tổng quan đánh giá, bảng phân loại rủi ro (Critical, High, Medium, Low).
- Phần 2: Đánh giá Kiến trúc Dữ liệu & Mô hình Thực thể (Requirement R1)
  * Đánh giá mô hình tam giác School ⟷ Major ⟷ Program ⟷ training_type ⟷ campus.
  * Phân tích 6 điểm nghẽn và rủi ro toàn vẹn dữ liệu (Ghost training type, Split-brain ACF vs taxonomy, Orphan records khi xóa/trash, Thiếu trạm/phân hiệu, Đợt tuyển sinh free text, Regex catch-all URL hijacking).
  * Giải pháp Two-Tier Rollup Architecture, `LTDH_Entity_Relationship_Engine`, Ma trận đầu vào 5 bậc học vấn, Schema đợt tuyển sinh chuẩn ISO.
- Phần 3: Đánh giá Logic Truy vấn, Bộ lọc & UX Phân loại (Requirement R2)
  * Phân tích N+1 query trên `archive-school.php` (Card vs List view), Double query trên `taxonomy-training_type.php`.
  * Phân tích Dead AJAX filter trong `main.js` (thiếu `#program-results-container`), Phantom facets trong sidebar.
  * Phân tích SEO Canonical redirect loop `/chuong-trinh/` và thiếu JSON-LD BreadcrumbList schema.
  * Giải pháp và code snippet khắc phục chuẩn WordPress Coding Standards.
- Phần 4: Đánh giá Tuân thủ Pháp lý Tuyển sinh & Niềm tin Văn bằng (Requirement R3)
  * Đối chiếu Thông tư 27/2019/TT-BGDĐT và Luật Quảng cáo 2012.
  * Các sai phạm nghiêm trọng (badge "100% BẰNG CỬ NHÂN CHÍNH QUY", "Bằng đỏ", giấu thông tin Phụ lục văn bằng trên FAQ).
  * Quy định đào tạo từ xa khối ngành Sức khỏe/Sư phạm theo Thông tư 28/2023/TT-BGDĐT.
  * Bộ thông điệp truyền thông chuẩn mực pháp lý thay thế.
- Phần 5: Đánh giá Phễu Tuyển sinh & Phân luồng Lead CRM (Requirement R4)
  * Lỗ hổng mất dữ liệu nghiêm trọng: `wp_ltdh_leads` thiếu cột `message`, xóa sạch lời nhắn khi sync CRM thành công trong `ltdh_process_lead_queue()`.
  * Lỗi Telegram Bot mù thông tin trường/ngành với form tư vấn thông thường.
  * Kiến trúc Multi-Tenant Lead Router phân luồng theo Trường đối tác (OnSchool, AUM, Telegram riêng).
  * Script SQL migration cho bảng `wp_ltdh_leads` và code mẫu.
- Phần 6: Ma trận Đối chiếu Nghiệp vụ Tuyển sinh Thực tế tại Việt Nam vs Mô hình Theme Hiện tại
  * Bảng so sánh 8 khía cạnh thực tế (Cơ sở/Trạm, Bậc học đầu vào & miễn giảm tín chỉ, Biểu phí, Đợt tuyển sinh, Phân loại ngành đúng/gần/khác, Giá trị văn bằng & Phụ lục, Phân luồng CRM theo đối tác, URL & SEO).
- Phần 7: Lộ trình & Kế hoạch Khắc phục Toàn diện (Actionable Roadmap)
  * Phase 1: Hotfix Khẩn cấp (Sửa lỗi mất data CRM, gỡ bỏ vi phạm pháp lý quảng cáo, sửa N+1 query).
  * Phase 2: Cải tổ Kiến trúc Nền tảng (Two-tier Rollup, Entity Relationship Engine, SQL migration).
  * Phase 3: Nâng cấp Mở rộng & Trải nghiệm (Multi-tenant CRM, AJAX filter, Structured ISO batches).
