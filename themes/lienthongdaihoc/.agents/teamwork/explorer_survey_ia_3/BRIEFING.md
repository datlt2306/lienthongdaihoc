# BRIEFING — 2026-10-01T16:21:00Z

## Mission
Comprehensive survey and IA recommendations for Liên thông đại học across Navigation, Homepage, Search/Filter, Program Cards, and Study Mode Archives.

## 🔒 My Identity
- Archetype: explorer
- Roles: Frontend & Template Explorer (IA, Navigation, Homepage, Filters, Program Cards, Archives)
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: IA & Frontend Survey (Milestone 1)

## 🔒 Key Constraints
- Read-only investigation — do NOT implement
- Do NOT modify any source code files or database records
- Preserve 3 core CPTs (school, major, program)
- Protect SEO URLs and canonicals
- Standardize display terminology to "Hình thức học" (Từ xa, Vừa học vừa làm)
- Identify and eliminate out-of-scope admissions (Văn bằng 2, đại học chính quy độc lập, cao đẳng...)

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T16:21:00Z

## Investigation State
- **Explored paths**:
  - WP Menus: Menu ID 3 (Primary), Menu ID 4 (Footer)
  - Templates: `header.php`, `footer.php`, `front-page.php`, `single-program.php`, `taxonomy-training_type.php`, `archive-program.php`, `single-school.php`, `single-major.php`, `template-parts/banner.php`, `template-parts/compare/*`
  - Core logic: `inc/core/class-menus.php`, `inc/core/class-rewrite-rules.php`, `inc/core/class-query-filters.php`, `inc/core/class-helpers.php`, `inc/core/class-defaults.php`
  - Database counts: 100 programs (98 `tu-xa`, 1 `vua-hoc-vua-lam`, 1 `chinh-quy`, 0 `van-bang-2`), 34 schools, 29 majors
- **Key findings**:
  - Footer Column 3 hardcodes out-of-scope and dead `#` links (Cao đẳng online, VB2, Chính quy)
  - Homepage has out-of-scope H1, THPT eligibility step, and VB2 mock testimonials
  - Rewrite rules 301 force-redirects `/chuong-trinh/` -> `/he-dao-tao/tu-xa/` causing filter state resets
  - Program cards in SSR and AJAX have conflicting headline hierarchies and lack the standardized admission formula `"Liên thông ngành [Tên ngành] - [Hình thức học] tại [Trường]"`
  - Terminology across templates uses "Hệ đào tạo" instead of "Hình thức học"
- **Unexplored areas**: None (Full survey complete across all 5 areas).

## Key Decisions Made
- Authored comprehensive 5-component handoff report at `handoff.md` with concrete code line references, analysis, and actionable replacement snippets.

## Artifact Index
- `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/DISPATCH.md` — Received instructions
- `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/progress.md` — Progress and heartbeat tracking
- `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_3/handoff.md` — Final survey report
