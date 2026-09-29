# BRIEFING — 2026-09-28T04:16:30Z

## Mission
Perform a deep technical audit of Requirement R2: Querying, Filtering & Taxonomy UX for Training Systems (`training_type`) and Partner Universities (`school`).

## 🔒 My Identity
- Archetype: explorer
- Roles: investigation, synthesis
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_query_1
- Original parent: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Milestone: Requirement R2 Deep Technical Audit

## 🔒 Key Constraints
- Read-only investigation — do NOT implement
- ZERO modification of theme source code. Audit and analysis only.
- Output findings to analysis.md and handoff.md in working directory.
- Report completion via send_message to orchestrator_3.

## Current Parent
- Conversation ID: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Updated: 2026-09-28T04:16:30Z

## Investigation State
- **Explored paths**:
  - `inc/acf-import-cpts.json` & `inc/post-types.php`: taxonomy registration and post type mappings
  - `inc/core/class-query-filters.php`: pre_get_posts modifications
  - `inc/core/class-rewrite-rules.php`: URL rewrite rules and request guards
  - `inc/core/class-helpers.php`: caching functions, breadcrumbs, badge generators
  - `archive-school.php`: featured queries, card view vs list view badge discrepancies, N+1 loops
  - `single-school.php`: overview, majors offered, program queries
  - `taxonomy-training_type.php` & `archive-program.php`: query bypass, facets, duplicate template
  - `inc/search-engine.php`: multi-query search, synonyms, transient cache
  - `inc/relationship-hooks.php`: post relationship syncing
  - `inc/seo/class-rankmath-integration.php`: titles, meta descriptions, schemas, breadcrumb overrides
  - `assets/js/main.js` & `functions.php`: AJAX filter handler and DOM target discrepancy
- **Key findings**:
  - `training_type` taxonomy registered only on `program`, not on `school`. Card view cannot display badges while List view runs hundreds of N+1 queries.
  - Secondary `new WP_Query` in `taxonomy-training_type.php` bypasses main query, doubling database work.
  - AJAX filter is dead code due to missing `#program-results-container`.
  - Canonical URL for `/he-dao-tao/` causes a 301 redirect loop to `/chuong-trinh/` -> `/he-dao-tao/tu-xa/`.
  - Sidebar facet count displays global totals, leading to phantom facets and zero-result views.
- **Unexplored areas**: None within Requirement R2 scope.

## Key Decisions Made
- Completed deep code inspection across 15+ files.
- Formulated 4 architectural remediation solutions with code snippets.
- Documented all findings in `analysis.md`.

## Artifact Index
- DISPATCH.md — Initial dispatch message
- BRIEFING.md — Working memory
- progress.md — Liveness heartbeat and progress tracker
- analysis.md — Comprehensive technical audit report
- handoff.md — 5-component handoff report (to be written)
