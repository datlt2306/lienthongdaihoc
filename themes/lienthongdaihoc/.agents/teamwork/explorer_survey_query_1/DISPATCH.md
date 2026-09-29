## 2026-09-28T04:09:10Z
<USER_REQUEST>
You are explorer_survey_query_1, a teamwork_preview_explorer agent.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_query_1/
You MUST create your directory and state files (BRIEFING.md, progress.md) within your working directory.

MISSION:
Perform a deep technical audit of Requirement R2: Querying, Filtering & Taxonomy UX for Training Systems (`training_type`) and Partner Universities (`school`).

MANDATORY FIRST STEP:
Read the authoritative user request at:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
Specifically review the requirements under timestamp `2026-09-28T04:04:15Z`.

KEY INVESTIGATION AREAS (R2):
1. Query logic and WP_Query auditing:
   - Inspect `inc/core/class-query-filters.php`, `archive-school.php`, `single-school.php`, `taxonomy-training_type.php`, `page-search.php`, and frontend search/filter scripts (`assets/js/search-filter.js`, etc.).
   - How does the system query schools when filtering by `training_type`?
   - How does it query majors/programs for a given school and training type?
   - Are there N+1 query patterns (e.g. inside loops querying related programs/majors for each school)?
   - Are query results cached via Transients / Object Cache?
2. Taxonomy Archives & Single Templates:
   - Audit `taxonomy-training_type.php`: What entity does the main query target (posts of type `program` or `school`)? How does it resolve school metadata?
   - Audit `archive-school.php` and `single-school.php`: How are the active training systems displayed for each school? Does a school show a training type badge even if it has no active programs in that system?
3. Taxonomy UX & Filter Interactions:
   - Filter state management (AJAX vs full page reload, URL query parameters, facets).
   - Empty state handling and count mismatch issues.
4. SEO, Permalinks & Navigation:
   - Breadcrumbs hierarchy (e.g. Trang chủ > Hệ đào tạo > [Hệ] > Trường).
   - Canonical URLs for taxonomy archives and filtered views.
   - URL structure, rewrite rules, and slug friendliness.
5. Identify specific bottlenecks, query inefficiencies, and recommend concrete optimization solutions with code snippets.

CONSTRAINTS:
- ZERO modification of theme source code. Audit and analysis only.
- Output your findings to `analysis.md` and write a comprehensive, self-contained `handoff.md` in your working directory.
- Report completion via send_message to orchestrator_3.
</USER_REQUEST>
