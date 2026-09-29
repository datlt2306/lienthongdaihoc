# BRIEFING — 2026-09-25T05:14:00Z

## Mission
Perform comprehensive security and database query audit of the Liên Thông Đại Học WordPress theme, identifying vulnerabilities and performance bottlenecks without modifying source code.

## 🔒 My Identity
- Archetype: explorer
- Roles: Security & DB Query Surveyor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2
- Original parent: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Milestone: Security & DB Query Survey

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify original source files in the theme.
- Write only within working directory (.agents/teamwork/explorer_survey_2/).
- Produce an exhaustive, evidence-backed handoff report (handoff.md) with exact files, line numbers, risk analysis, and sample fix snippets.

## Current Parent
- Conversation ID: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Updated: 2026-09-25T05:14:00Z

## Investigation State
- **Explored paths**: All 49 PHP files across the theme, including functions.php, inc/, template-parts/, archive templates, single templates, page templates, taxonomy templates, and tests/run-tests.php.
- **Key findings**:
  1. Direct file access guards missing in `header.php`, `inc/search-engine.php`, and `tests/run-tests.php` (CLI guard missing).
  2. Public unvalidated file uploads in `inc/eligibility.php` (missing MIME whitelist and size limits).
  3. IDOR vulnerability in `ltdh_elig_ajax_advanced_verify` allowing arbitrary lead updates.
  4. Missing CSRF nonce verification in `ltdh_ajax_filter_programs` and native form submission handler.
  5. Transient self-invalidation bug on `front-page.php:16` (`delete_transient` called on every load).
  6. Dangerous default `posts_per_page => -1` in `class-query-filters.php` and user-controlled `-1` in `archive-program.php`.
  7. Severe N+1 query patterns in `archive-school.php` and `archive-program.php` ignoring pre-synced relationship meta.
- **Unexplored areas**: None. Full static analysis of security and DB queries completed.

## Key Decisions Made
- Documented findings with concrete code snippets, line numbers, risk levels, and ready-to-apply remediation code for implementers.

## Artifact Index
- DISPATCH.md — incoming dispatch records
- BRIEFING.md — persistent working memory
- progress.md — liveness heartbeat and audit step status
- handoff.md — final comprehensive audit report
