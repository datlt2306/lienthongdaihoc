# Task Assignment: Survey & Codebase Audit - Templates, Navigation, UI & Filter Engine

Your working directory is:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2/`

Read the authoritative user request in:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md`
under section `## 2026-10-01T08:50:13Z`.

Your focus:
1. Inspect all theme templates and UI components:
   - Navigation menus (header, mobile menu, footer, navigation walkers/hooks)
   - `taxonomy-training_type.php` (or training mode archives)
   - `single-program.php`
   - `archive-school.php`, `single-school.php`
   - `archive-major.php`, `single-major.php`
   - Filter engines: `inc/core/class-query-filters.php`, search/filter forms, AJAX endpoints, facet/filter template-parts.
   - Eligibility wizard & calculator if they touch programs/schools.
2. Identify discrepancies with the target business model:
   - University -> Admission Information -> Study Mode (Chính quy, Vừa học vừa làm, Từ xa) -> Major -> Specific Liên thông admission offering.
   - Redundant filters (e.g. filter by "Loại chương trình: Liên thông" when 100% of programs are Liên thông).
   - Templates that miss required sections (e.g., in `single-program.php`: Trường, Ngành, Hình thức học, Đối tượng, Điều kiện, Thời gian học, Học phí, Địa điểm/Phương thức, Bằng cấp, Hồ sơ, Thời gian tuyển sinh, Form đăng ký).
   - Misaligned H1, hero copy, badges, degree descriptions.
3. Produce the Current -> Target mapping table for Templates and Navigation according to the required schema:
   `CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`
4. Propose a minimal safe intervention plan for templates and UI while preserving existing design/styling.

Write your findings to `handoff.md` in your working directory and notify the orchestrator via `send_message`.

## 2026-10-01T08:52:36Z
You are the Templates & UI Explorer for the task: Tái cấu trúc Kiến trúc thông tin (Information Architecture), Thuật ngữ & Templates dự án lienthongdaihoc.com.

Working Directory: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2/`
Project Root: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`

Read:
1. `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md` (specifically section `## 2026-10-01T08:50:13Z`).
2. `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2/DISPATCH.md`.

Your focus:
1. Inspect all theme templates and UI components:
   - Navigation menus (header, mobile menu, footer, navigation walkers/hooks)
   - `taxonomy-training_type.php` (or training mode archives)
   - `single-program.php`
   - `archive-school.php`, `single-school.php`
   - `archive-major.php`, `single-major.php`
   - Filter engines: `inc/core/class-query-filters.php`, search/filter forms, AJAX endpoints, facet/filter template-parts.
   - Eligibility wizard & calculator if they touch programs/schools.
2. Identify discrepancies with the target business model:
   - University -> Admission Information -> Study Mode (Chính quy, Vừa học vừa làm, Từ xa) -> Major -> Specific Liên thông admission offering.
   - Redundant filters (e.g. filter by "Loại chương trình: Liên thông" when 100% of programs are Liên thông).
   - Templates that miss required sections (e.g., in `single-program.php`: Trường, Ngành, Hình thức học, Đối tượng, Điều kiện, Thời gian học, Học phí, Địa điểm/Phương thức, Bằng cấp, Hồ sơ, Thời gian tuyển sinh, Form đăng ký).
   - Misaligned H1, hero copy, badges, degree descriptions.
3. Produce the Current -> Target mapping table for Templates and Navigation according to the required schema:
   `CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`
4. Propose a minimal safe intervention plan for templates and UI while preserving existing design/styling.

Write your complete findings and report to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2/handoff.md`.
Then notify the orchestrator with `send_message`.

