## 2026-09-28T04:09:10Z

<USER_REQUEST>
You are explorer_survey_arch_1, a teamwork_preview_explorer agent.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_arch_1/
You MUST create your directory and state files (BRIEFING.md, progress.md) within your working directory.

MISSION:
Perform a deep technical audit of Requirement R1: Data Architecture & Entity Modeling for Training Systems (`training_type`) and Partner Universities (`school`).

MANDATORY FIRST STEP:
Read the authoritative user request at:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
Specifically review the requirements under timestamp `2026-09-28T04:04:15Z`.

KEY INVESTIGATION AREAS (R1):
1. Investigate how `school` (CPT), `major` (CPT), `program` (CPT), `training_type` (taxonomy), `campus` (taxonomy), and any other related entities are registered and structured.
   - Inspect: `inc/post-types.php`, `inc/acf-fields.php`, `inc/core/class-entity-*.php`, `inc/database/`, `inc/eligibility*.php`, and related files.
2. Examine the triangular / intermediate entity relationship:
   - `School` (CPT) ⟷ `Major` (CPT) ⟷ `Program` (CPT - Intermediate entity) ⟷ `Training Type` (Taxonomy) ⟷ `Campus` (Taxonomy).
   - How are associations stored? ACF post objects, relationship fields, bidirectional hooks, custom post meta, or taxonomy term relationships?
3. Evaluate the architectural pros and cons of:
   - Assigning `training_type` to `program` vs directly to `school` or as an independent Post Type.
   - What happens when a school offers multiple training types for the same major vs different majors?
4. Alignment with Vietnam Admissions Reality:
   - Multiple campuses per university (Hanoi, HCMC, provincial campuses, affiliated training centers).
   - Multiple admission batches (Đợt 1, Đợt 2, tuyển sinh liên tục quanh năm).
   - Divergent tuition fee structures, credit requirements, and duration across training systems (Từ xa vs Vừa làm vừa học vs Liên thông chính quy) and target inputs (THPT, Trung cấp, CĐ cùng ngành, CĐ khác ngành, VB2).
5. Identify at least 3 critical data integrity gaps, bottlenecks, or admin UX issues (e.g. data desynchronization, orphan records, redundant data entry).
6. Recommend a comprehensive upgraded architecture / schema.

CONSTRAINTS:
- ZERO modification of theme source code. Audit and analysis only.
- Output your findings to `analysis.md` and write a comprehensive, self-contained `handoff.md` in your working directory.
- Report completion via send_message to orchestrator_3.
</USER_REQUEST>
