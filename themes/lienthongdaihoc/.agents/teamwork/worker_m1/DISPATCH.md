## 2026-10-01T09:22:25Z
You are worker_m1 (WP-CLI Audit & Data Scope Worker).
Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m1/

Load and follow the domain skill:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md

Read these authoritative input files:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z).
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
3. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_1/handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope & Write Ownership:
You own `inc/cli-commands.php`, running the WP-CLI audit command, and generating `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/audit_report.json`.

Task Requirements (Milestone M1):
1. In `inc/cli-commands.php`, register and implement the `audit_data` command under WP-CLI command `wp ltdh audit-data`:
   - Support arguments: `--dry-run`, `--apply`, `--output-file=<file>` (default to theme root `audit_report.json`).
   - Scan all 100 `program` records, all 21 `school` records, and all 34 `major` records.
   - Accurately categorize programs:
     * In-scope Liên thông (95 records): 94 Từ xa across 19 universities + 1 Vừa học vừa làm (UTC ID 1854).
     * Out-of-scope (5 records): ID 2013 (Chính quy at UTC), and IDs 1786, 1787, 1788, 1789 (Cao đẳng HCCT).
   - In `--apply` mode:
     * Safely transition the 5 out-of-scope programs to `post_status = 'draft'`. Set meta `_ltdh_audit_status`, `_ltdh_audit_reason`, `_ltdh_audited_at`.
     * Safely transition School 1662 (HCCT) to `post_status = 'draft'`. Set meta `_ltdh_audit_status = 'out_of_scope_institution'`.
     * NEVER hard-delete any record from database! Zero calls to `wp_delete_post()`.
     * Prune orphaned IDs (1855, 1856) and drafted IDs from `_offered_programs` of School 1853 and Major 1677.
     * Flush query transients: `ltdh_filter_options`, `ltdh_featured_schools`, `ltdh_rewrite_flushed_v2`.
     * Save complete structured audit log to `audit_report.json`.
2. Run syntax check `php -l inc/cli-commands.php`.
3. Execute `wp ltdh audit-data --dry-run` and then `wp ltdh audit-data --apply`.
4. Verify results with WP-CLI queries:
   - Exactly 95 programs published, 5 drafted, 0 trashed.
   - Exactly 20 schools published, 1 drafted (HCCT), 0 trashed.
   - Exactly 34 majors published.
   - Check that `_offered_programs` on 1853 and 1677 no longer contain 1855 or 1856.
   - Verify `audit_report.json` exists, is valid JSON, and contains full transparent accounting.

Write your complete handoff report to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m1/handoff.md
Include build/command output and verification details. When done, use send_message to report back.
