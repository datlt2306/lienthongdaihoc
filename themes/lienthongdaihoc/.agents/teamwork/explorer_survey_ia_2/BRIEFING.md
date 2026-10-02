# BRIEFING — 2026-10-01T09:21:00Z

## Mission
Comprehensive architecture, taxonomy, routing, and query data flow investigation for lienthongdaihoc.com.

## 🔒 My Identity
- Archetype: explorer
- Roles: Architecture & Routing Explorer
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_2/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: Survey & Architectural Analysis

## 🔒 Key Constraints
- Read-only investigation — do NOT implement
- Do NOT modify any source code files or database records
- Write only to .agents/teamwork/explorer_survey_ia_2/

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: not yet

## Investigation State
- **Explored paths**:
  - `inc/post-types.php`, `inc/acf-import-cpts.json`, `inc/acf-fields.php`, `inc/acf-import-fields.json`
  - `inc/core/class-rewrite-rules.php`, `inc/core/class-query-filters.php`, `inc/core/class-helpers.php`, `inc/core/class-menus.php`
  - `single-school.php`, `archive-school.php`, `single-major.php`, `archive-major.php`, `single-program.php`, `archive-program.php`, `taxonomy-training_type.php`, `taxonomy.php`, `front-page.php`, `header.php`, `footer.php`
  - `inc/seo/class-rankmath-integration.php`, `inc/config/class-defaults.php`, `inc/config/constants.php`, `inc/cli-commands.php`, `inc/relationship-hooks.php`
- **Key findings**:
  - CPTs and Taxonomies loaded from `inc/acf-import-cpts.json`. CPTs: `school`, `major`, `program` (and `guide`). Taxonomies: `training_type`, `campus`, `region`, `major_cat`.
  - `training_type` taxonomy currently labeled "Hệ đào tạo" throughout JSON, defaults, breadcrumbs, menus, templates, and filter badges. Must change to "Hình thức học" while keeping slug `/he-dao-tao/` and terms (`tu-xa`, `vua-hoc-vua-lam`).
  - `/chuong-trinh/` is registered as `has_archive_slug` for CPT `program`, but `inc/core/class-rewrite-rules.php:247-254` forces an arbitrary 301 redirect to `/he-dao-tao/tu-xa/`. Meanwhile, `archive-program.php` still uses `/chuong-trinh/` for form actions and reset links, and Rank Math outputs canonical to `/chuong-trinh/`, creating a 301 canonical loop!
  - Data flow: `single-school.php` and `single-major.php` query programs via `_offered_programs` and `school_relationship`/`major_relationship`, but lack scope checks (any non-lienthong program would render). `archive-school.php` directly reads school-attached terms in `ltdh_get_school_training_types()` before checking programs.
  - `campus`: 'Online' was seeded as a term in `campus` taxonomy (`inc/cli-commands.php:119`), causing "Online" to be rendered as a physical campus location across templates (`single-program.php`, `taxonomy.php`, `inc/comparison.php`).
- **Unexplored areas**: None, all 5 scopes deeply surveyed.

## Key Decisions Made
- Map out exact code locations for all 5 scopes.
- Deliver full, unabridged 5-component handoff report.

## Artifact Index
- DISPATCH.md — Incoming instruction log
- BRIEFING.md — Persistent context & state
- progress.md — Liveness & progress tracker
- handoff.md — [To be created] Final comprehensive analysis report
