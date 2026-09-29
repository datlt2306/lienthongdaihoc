# Project: Schools and Training Systems Comprehensive Audit
Working Directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_3/
Deliverable Target: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md

## Architecture
- **Context & Scope**: WordPress Theme `lienthongdaihoc` (Custom Theme).
- **Core Entities**:
  - `school` (Custom Post Type) — Partner Universities (Trường đối tác).
  - `major` (Custom Post Type) — Academic Majors (Ngành đào tạo).
  - `program` (Custom Post Type) — Intermediate Entity (Chương trình đào tạo cụ thể: School × Major × Training Type).
  - `training_type` (Taxonomy) — Training Systems (Từ xa, Vừa làm vừa học, Văn bằng 2, Liên thông chính quy).
  - `campus` (Taxonomy) — Locations / Branches (Cơ sở / Trạm đào tạo).
- **Subsystem Boundaries**:
  - Entity Registration & ACF Definitions: `inc/post-types.php`, `inc/acf-import-cpts.json`, `inc/acf-import-fields.json`.
  - Relationship Management & Synchronization: `inc/relationship-hooks.php`.
  - Query Filters, Rewrites & SEO: `inc/core/class-query-filters.php`, `inc/core/class-rewrite-rules.php`, `inc/seo/class-rankmath-integration.php`.
  - Template Hierarchy & Presentation: `archive-school.php`, `single-school.php`, `taxonomy-training_type.php`, `single-program.php`, `front-page.php`.
  - Admissions Funnel & Lead Routing: `inc/lead-capture.php`, `inc/crm-adapters.php`, `wp_ltdh_leads` table, Telegram Bot integration.
  - Legal & Regulatory Compliance: Circular 27/2019/TT-BGDĐT, Law on Advertising 2012, Circular 28/2023/TT-BGDĐT, Circular 08/2021/TT-BGDĐT.

## Feature Inventory
| # | Feature / Area | Description | Milestone | Source |
|---|----------------|-------------|-----------|--------|
| 1 | Entity Triangular Modeling Audit | Detailed analysis of `School` ⟷ `Major` ⟷ `Program` intermediate architecture, taxonomy object types, and two-tier rollup solution | M1 (Report Compilation) | Explorer 1 / R1 |
| 2 | Data Integrity Gaps & Lifecycle Hooks | Audit 6 critical data integrity gaps (Ghost badges, Split-brain ACF vs taxonomy, orphan IDs on trash/delete, missing stations, free-text batches, URL regex hijacking) | M1 (Report Compilation) | Explorer 1 / R1 |
| 3 | Upgraded Data Schema & Relationship Engine | Concrete technical design of `LTDH_Entity_Relationship_Engine`, input-level matrix repeater, ISO batches, and clean rewrite rules | M1 (Report Compilation) | Explorer 1 / R1 |
| 4 | WP_Query & N+1 Bottlenecks Audit | Audit of `archive-school.php` list view (144-800+ queries), Card View missing badges, and double query in `taxonomy-training_type.php` | M1 (Report Compilation) | Explorer 2 / R2 |
| 5 | Filter UX, Dead AJAX & Phantom Facets | Audit of `main.js` missing `#program-results-container`, global SQL facet counting mismatch, and mobile filter friction | M1 (Report Compilation) | Explorer 2 / R2 |
| 6 | SEO Permalinks, Canonical & Breadcrumbs | Audit of `/chuong-trinh/` 301 canonical loop, suppression of RankMath JSON-LD BreadcrumbList schema on `/he-dao-tao/*` | M1 (Report Compilation) | Explorer 2 / R2 |
| 7 | Circular 27/2019/TT-BGDĐT & Advertising Compliance | Critical legal audit of "100% BẰNG CỬ NHÂN CHÍNH QUY", "Bằng đỏ" marketing copy, omission of Diploma Supplement in FAQ, and health/education distance restrictions | M1 (Report Compilation) | Explorer 3 / R3 |
| 8 | Lead Routing & Critical Data Loss Bug | Critical bug in `wp_ltdh_leads` where `error_message = ''` deletes 100% user notes on CRM sync; Telegram bot information blindness | M1 (Report Compilation) | Explorer 3 / R4 |
| 9 | Multi-Tenant CRM Architecture | Scalable lead routing schema for partner universities (OnSchool, AUM, Telegram, Webhook), code standard mapping, and CF7 context fix | M1 (Report Compilation) | Explorer 3 / R4 |
| 10 | Independent Quality Review | Multi-perspective objective review of audit report completeness, evidence chains, and code snippet quality | M2 (Review Gate) | Reviewers |
| 11 | Empirical & Stress-Test Verification | Verification of query patterns, schema migration, regex safety, and regulatory compliance claims | M3 (Challenger Gate) | Challengers |
| 12 | Forensic Integrity Audit | Systematic integrity check verifying authentic analysis, zero plagiarism/fabrication, and strict read-only compliance | M4 (Auditor Gate) | Auditor |

## Milestones
| # | Milestone Name | Scope | Dependencies | Status |
|---|----------------|-------|--------------|--------|
| M1 | Draft Comprehensive Audit Report | Worker compiles exhaustive, authoritative `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` adhering to all requirements R1-R4 and explorer evidence | Phase 0 Survey complete | DONE |
| M2 | Independent Technical Review | 2 Reviewers independently evaluate completeness, accuracy, actionable code snippets, and structural integrity | M1 | DONE (APPROVE) |
| M3 | Empirical Adversarial Challenge | 2 Challengers test claims, stress test query complexity, verify regulatory citations, and check SQL/PHP snippets | M1 | DONE (APPROVE) |
| M4 | Forensic Integrity Audit | Forensic Auditor performs integrity forensics verifying zero source code modifications, authentic analysis, and strict compliance | M1, M2, M3 | DONE (CLEAN) |
| M5 | Final Synthesis & Delivery | Orchestrator synthesizes gate verdicts, verifies deliverable presence, and presents completion report to user | M1-M4 passed | IN_PROGRESS |

## Interface Contracts & Deliverable Specification
The deliverable `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` must be written in Vietnamese with professional technical terminology, structured into 7 main sections:
- **Phần 1: Executive Summary & Bảng chỉ số sức khỏe nghiệp vụ (Health Scorecard)**: Tổng quan, ma trận rủi ro (Critical, High, Medium, Low).
- **Phần 2: Đánh giá Kiến trúc dữ liệu & Mô hình thực thể (Requirement R1)**: Phân tích mô hình tam giác, 6 rủi ro toàn vẹn dữ liệu, giải pháp Two-Tier Rollup, sơ đồ quan hệ nâng cấp.
- **Phần 3: Đánh giá Logic Truy vấn, Bộ lọc & UX Phân loại (Requirement R2)**: Phân tích N+1 query, Card vs List view badge bug, Dead AJAX filter, Phantom facets, SEO canonical & Schema.
- **Phần 4: Đánh giá Tuân thủ Pháp lý Tuyển sinh & Niềm tin Văn bằng (Requirement R3)**: Đối chiếu Thông tư 27/2019/TT-BGDĐT, Luật Quảng cáo 2012, các phát ngôn sai lệch và bộ quy chuẩn truyền thông thay thế.
- **Phần 5: Đánh giá Phễu Tuyển sinh & Phân luồng Lead CRM (Requirement R4)**: Lỗi mất dữ liệu nghiêm trọng, lỗi Telegram bot mù thông tin, kiến trúc Multi-Tenant Lead Router, SQL migration.
- **Phần 6: Ma trận Đối chiếu Nghiệp vụ Tuyển sinh Việt Nam vs Thực trạng Code**: Bảng so sánh 8 khía cạnh thực tế (Cơ sở, biểu phí, thời gian, xét miễn giảm, đợt tuyển sinh, v.v.).
- **Phần 7: Lộ trình & Kế hoạch Khắc phục Toàn diện (Actionable Roadmap)**: Phân kỳ Phase 1 (Hotfix khẩn cấp), Phase 2 (Kiến trúc nền tảng), Phase 3 (Mở rộng & Nâng cao).
