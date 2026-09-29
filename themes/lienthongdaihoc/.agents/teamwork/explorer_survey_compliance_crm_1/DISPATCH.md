## 2026-09-28T04:09:10Z

You are explorer_survey_compliance_crm_1, a teamwork_preview_explorer agent.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_compliance_crm_1/
You MUST create your directory and state files (BRIEFING.md, progress.md) within your working directory.

MISSION:
Perform a deep technical audit of Requirements R3 & R4: Regulatory Compliance & Degree Trust, and Lead Routing & Admissions Funnel.

MANDATORY FIRST STEP:
Read the authoritative user request at:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
Specifically review the requirements under timestamp `2026-09-28T04:04:15Z`.

KEY INVESTIGATION AREAS:
1. Requirement R3: Regulatory Compliance & Degree Trust:
   - Contrast frontend messaging and copy across the theme against Circular 27/2019/TT-BGDĐT (Thông tư 27/2019/TT-BGDĐT - quy định nội dung chính ghi trên văn bằng giáo dục đại học, bãi bỏ việc ghi hình thức đào tạo chính quy/từ xa/vừa làm vừa học trên văn bằng tốt nghiệp).
   - Inspect `single-school.php`, `taxonomy-training_type.php`, `front-page.php`, `inc/`, and template-parts for Hero copy, degree badges, sample diploma displays, FAQs, and admission legal citations.
   - Is degree equivalence (giá trị bằng tương đương chính quy) accurately and legally communicated without misleading candidates?
   - Check legal compliance requirements for distance education / e-learning / joint training programs with partner universities (Luật Giáo dục Đại học sửa đổi 2018, Thông tư 28/2023/TT-BGDĐT về đào tạo từ xa, Thông tư 08/2021/TT-BGDĐT về quy chế tuyển sinh).
2. Requirement R4: Lead Routing & Admissions Funnel:
   - Inspect `inc/leads/`, `inc/crm/`, `inc/notifications/`, Telegram bot integrations, OnSchool API, AUM CRM integrations, AJAX submission handlers.
   - When a candidate submits a lead form on a school page or training type page, does the payload capture:
     * School code / School ID
     * Training type code / slug
     * Campus / Branch code
     * Program / Major ID
   - How is lead routing handled? Are leads routed to specific partner CRM endpoints or notification channels based on school / training_type?
   - Extensibility analysis: How easy or error-prone is it to add a new university partner, open a new training system, or suspend admissions for a specific school+training_type combination?
3. Identify vulnerabilities, compliance risks, lead leakage risks, and recommend concrete improvements with architecture diagrams/code snippets.

CONSTRAINTS:
- ZERO modification of theme source code. Audit and analysis only.
- Output your findings to `analysis.md` and write a comprehensive, self-contained `handoff.md` in your working directory.
- Report completion via send_message to orchestrator_3.
