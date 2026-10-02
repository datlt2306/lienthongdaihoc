# BRIEFING — 2026-10-01T09:30:00Z

## Mission
Implement WP-CLI `wp ltdh audit-data` command in `inc/cli-commands.php`, execute data audit and safe scope isolation (Milestone M1), prune orphaned metadata, and generate `audit_report.json`.

## 🔒 My Identity
- Archetype: implementer, qa, specialist
- Roles: implementer, qa, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m1
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M1 (Data Audit & Safe Scope Handling)

## 🔒 Key Constraints
- DO NOT CHEAT: Genuine implementation, real database mutations, no hardcoded results or dummy facades.
- Zero calls to `wp_delete_post()`. Absolutely NO hard deletion of any records.
- Safe transition of 5 out-of-scope programs (ID 2013, 1786, 1787, 1788, 1789) and School 1662 (HCCT) to `draft`.
- Set post meta `_ltdh_audit_status`, `_ltdh_audit_reason`, `_ltdh_audited_at`.
- Prune orphaned IDs (1855, 1856) and drafted IDs from `_offered_programs` of all schools & majors (specifically 1853 and 1677).
- Flush transients: `ltdh_filter_options`, `ltdh_featured_schools`, `ltdh_rewrite_flushed_v2`.
- Emit complete structured log to `audit_report.json` in theme root.
- Verify with WP-CLI queries.

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T09:30:00Z

## Task Summary
- **What to build**: WP-CLI command `wp ltdh audit-data [--dry-run] [--apply] [--output-file=<path>]` in `inc/cli-commands.php`.
- **Success criteria**:
  - Exactly 95 programs published, 5 drafted, 0 trashed. [MET]
  - Exactly 20 schools published, 1 drafted (HCCT), 0 trashed. [MET]
  - Exactly 34 majors published. [MET]
  - `_offered_programs` on 1853 and 1677 no longer contain 1855 or 1856 or drafted IDs. [MET]
  - `audit_report.json` exists, is valid JSON, contains comprehensive accounting. [MET]
- **Interface contracts**: PROJECT.md § Data Audit Script Contract (M1).
- **Code layout**: `inc/cli-commands.php`.

## Key Decisions Made
- Registered `wp ltdh audit-data` using `WP_CLI::add_command( 'ltdh audit-data', [ new LTDH_CLI_Commands(), 'audit_data' ] );` to support hyphenated WP-CLI standard command naming.
- Zero calls to `wp_delete_post()`. Used `wp_update_post( ['ID' => $id, 'post_status' => 'draft'] )`.
- Stored audit trail metadata: `_ltdh_audit_status`, `_ltdh_audit_reason`, `_ltdh_audited_at`.
- Filtered `_offered_programs` across all 55 schools and majors, safely pruning ghost IDs 1855, 1856 and drafted programs 1786-1789, 2013.

## Loaded Skills
- **Source**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md`
- **Local copy**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m1/skills/SKILL.md`
- **Core methodology**: WP-CLI command structure, WordPress sanitization/escaping, safe post status transitions, meta handling, transient cache invalidation.

## Change Tracker
- **Files modified**: `inc/cli-commands.php` (Implemented `audit_data` method and command registration)
- **Build status**: `php -l inc/cli-commands.php` PASSED. `wp ltdh audit-data --dry-run` and `wp ltdh audit-data --apply` PASSED.
- **Pending issues**: None.

## Quality Status
- **Build/test result**: All verification queries PASS.
- **Lint status**: 0 PHP syntax errors.
- **Tests added/modified**: WP-CLI command `wp ltdh audit-data` test runs (dry-run & apply).

## Artifact Index
- `.agents/teamwork/worker_m1/DISPATCH.md` — Assignment instructions
- `.agents/teamwork/worker_m1/BRIEFING.md` — Working memory and status
- `.agents/teamwork/worker_m1/progress.md` — Liveness and step tracking
- `inc/cli-commands.php` — Implemented WP-CLI command
- `audit_report.json` — Structured audit output
- `.agents/teamwork/worker_m1/handoff.md` — Final handoff report
