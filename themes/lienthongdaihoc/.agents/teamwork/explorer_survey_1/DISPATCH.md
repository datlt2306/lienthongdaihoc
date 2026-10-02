# Task Assignment: Survey & Codebase Audit - Information Architecture & Data Model

Your working directory is:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/`

Read the authoritative user request in:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md`
under section `## 2026-10-01T08:50:13Z`.

Also review any existing audit reports in the workspace root:
- `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`
- `ELIGIBILITY_BUSINESS_AUDIT.md`
- `FULL_PROJECT_AUDIT_REPORT.md`

Your focus:
1. Inspect all PHP files defining CPTs, Taxonomies, ACF fields, meta boxes, and data relationships (e.g. `inc/post-types.php`, `inc/taxonomies.php`, `inc/acf-fields.php`, `inc/post-types/`, etc.).
2. Audit existing entities: `program`, `school`, `major`, `training_type`, `campus`, etc.
3. Check current labels, terms, slug registrations, and hierarchical relationships.
4. Search the codebase for prohibited or conflicting terms/concepts: "Văn bằng 2", "VB2", "Đại học mới", "chính quy" (as an independent university admission instead of a study mode of Liên thông), "Hệ đào tạo" vs "Hình thức học" vs "Hình thức đào tạo".
5. Produce the Current -> Target mapping table for Entities, Taxonomies, and Fields according to the required schema:
   `CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`
6. Propose a minimal safe intervention plan for data model & terminology.

Write your findings to `handoff.md` in your working directory and notify the orchestrator via `send_message`.

## 2026-10-01T08:52:36Z
You are the IA & Data Model Explorer for the task: Tái cấu trúc Kiến trúc thông tin (Information Architecture), Thuật ngữ & Templates dự án lienthongdaihoc.com.

Working Directory: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/`
Project Root: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`

Read:
1. `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md` (specifically section `## 2026-10-01T08:50:13Z`).
2. `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/DISPATCH.md`.
3. Check existing audit docs if relevant (`SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`, `FULL_PROJECT_AUDIT_REPORT.md`).

Your focus:
1. Inspect all PHP files defining CPTs, Taxonomies, ACF fields, meta boxes, and data relationships (e.g., `inc/post-types.php`, `inc/taxonomies.php`, `inc/acf-fields.php`, etc.).
2. Audit existing entities: `program`, `school`, `major`, `training_type`, `campus`, etc.
3. Check current labels, terms, slug registrations, and hierarchical relationships.
4. Search the codebase for prohibited or conflicting terms/concepts: "Văn bằng 2", "VB2", "Đại học mới", "chính quy" (as an independent university admission instead of a study mode of Liên thông), "Hệ đào tạo" vs "Hình thức học" vs "Hình thức đào tạo".
5. Produce the Current -> Target mapping table for Entities, Taxonomies, and Fields according to the required schema:
   `CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`
6. Propose minimal safe intervention strategy for data model & terminology.

Write your complete findings and report to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/handoff.md`.
Then notify the orchestrator with `send_message`.

