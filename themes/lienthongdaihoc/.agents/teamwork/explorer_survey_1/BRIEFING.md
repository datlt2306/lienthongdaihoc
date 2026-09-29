# BRIEFING — 2026-09-25T05:12:00Z

## Mission
Perform comprehensive codebase & PHP inventory survey, syntax checks (`php -l`), architecture mapping, hook/filter cataloging, and PHP 8.1-8.3 compatibility auditing across 100% of theme PHP files.

## 🔒 My Identity
- Archetype: explorer
- Roles: Codebase & PHP Inventory Surveyor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1
- Original parent: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Milestone: Phase 1 Deep Exploration & Inventory

## 🔒 Key Constraints
- Read-only investigation — do NOT modify any original theme source code
- Write only within /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/
- Maintain progress.md with timestamps
- Enumerate 100% of PHP files in the theme with line counts, byte sizes, purpose, syntax verification

## Current Parent
- Conversation ID: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Updated: not yet

## Investigation State
- **Explored paths**: All 49 PHP files + style.css in /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
- **Key findings**:
  1. Exactly 49 PHP files, 14,780 total lines of PHP code, 643,704 bytes. 100% passed `php -l` under PHP 8.4.19 with 0 syntax errors.
  2. 3 files missing `defined('ABSPATH') || exit;`: `header.php`, `inc/search-engine.php`, `tests/run-tests.php`.
  3. CPT `guide` is defined as constant `LTDH_CPT_GUIDE` and has template `single-guide.php` and queries in other singles, but is NOT registered in `inc/post-types.php` or `inc/acf-import-cpts.json`.
  4. 18 usages of `get_page_by_path()` across templates and CLI (deprecated in WP 6.2+).
  5. Transient cache busting bug: `front-page.php:16` explicitly calls `delete_transient('ltdh_featured_schools_data')` on every page hit.
  6. Unbounded database queries with `numberposts => -1` or `posts_per_page => -1` in `front-page.php:143-144`, `inc/core/class-query-filters.php:30`, `inc/search-engine.php:35,44,67,81,101`, `archive-program.php:104`, `taxonomy.php:33`, `single-school.php:249`.
  7. High duplication: `archive-program.php` and `taxonomy-training_type.php` are 98% identical clones (540 vs 537 lines, only 79 diff lines).
  8. Missing nonce verification in `ltdh_ajax_filter_programs()` (`functions.php:69`) and `ltdh_handle_native_form_submit()` (`inc/lead-capture.php:333`).
  9. Empty directories in `inc/`: `inc/admin`, `inc/modules`, `inc/relationships`.
- **Unexplored areas**: None for Codebase & PHP Inventory. All 49 files completely surveyed.

## Key Decisions Made
- Used automated static analysis and verification scripts to avoid human error or omissions.
- Mapped all 40 registered actions, 37 registered filters, 1 custom applied filter (`pre_get_posts_args_ltdh`), and all custom post types/taxonomies.

## Artifact Index
- [BRIEFING.md](BRIEFING.md) — Persistent working memory
- [DISPATCH.md](DISPATCH.md) — Dispatch log
- [progress.md](progress.md) — Liveness heartbeat and milestone tracker
- [survey_analyzer.py](survey_analyzer.py) — Automated file inventory and syntax parser
- [survey_data.json](survey_data.json) — Full JSON dataset for 49 PHP files
- [php8_audit.py](php8_audit.py) — PHP 8.1+ compatibility and deprecation scanner
- [php8_audit_results.json](php8_audit_results.json) — Detailed issue occurrences
- [hooks_data.json](hooks_data.json) — Parsed actions and filters index
- [handoff.md](handoff.md) — Final comprehensive handoff report
