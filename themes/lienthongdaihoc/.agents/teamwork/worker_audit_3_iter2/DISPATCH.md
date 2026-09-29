## 2026-09-28T04:39:25Z

You are worker_audit_3_iter2, a teamwork_preview_worker agent.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_audit_3_iter2/
You MUST create your directory and state files (BRIEFING.md, progress.md) within your working directory.

MANDATORY FIRST STEP:
Read the authoritative user request at:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
Specifically review the requirements under timestamp `2026-09-28T04:04:15Z`.

Read the exact challenger findings and drop-in code fixes:
1. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_3_1/analysis.md
   and handoff.md
2. /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_3_2/analysis.md
   and handoff.md

FILE TO UPDATE:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md

STRICT CONSTRAINT:
- ZERO modification of theme source code (PHP, JS, CSS, JSON). Only update SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

ACTION ITEMS TO PATCH IN SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:
1. Section 2.4 (LTDH_Entity_Relationship_Engine):
   - Update hooks to use `trashed_post`, `untrashed_post`, and `before_delete_post` with `'post__not_in' => [$post_id]` so trashing/deleting actually purges orphan IDs.
   - Update save hooks to include `acf/save_post` priority 25 and `save_post_program` priority 25 to eliminate ACF meta timing race condition.
2. Section 3.7.1 (`archive-school.php` List View Snippet):
   - Replace only lines 295-307, preserving `$prog_tags` (lines 278-290) and `$region_terms` (lines 292-293) so line 345 does not produce undefined variable warning and HTML tags stay inside the card container.
3. Section 3.7.2 (`pre_get_posts` on `taxonomy-training_type.php`):
   - Fully retain and support user filter parameters `$_GET['nhom_nganh']`, `$_GET['truong']`, `$_GET['s']`, `$_GET['sort']`.
4. Section 3.7.4 (Rank Math Canonical URL filter):
   - Target `is_post_type_archive('program')` / `/chuong-trinh/` to resolve the canonical redirect loop.
5. Section 5.6.3 (`ltdh_trigger_telegram_notification_v2`):
   - Remove `rawurlencode($bot_token)` so token delimiter `:` is not transformed to `%3A`.
   - Implement multi-casting: send simultaneously to central admin and partner school Telegram group.
6. Section 5.6 & 7.2 (SQL Migration for `wp_ltdh_leads`):
   - Use dynamic `$wpdb->prefix`.
   - Use safe PHP DDL migration checking if column exists before altering.
   - Include data backfill: `UPDATE {$table_name} SET message = error_message WHERE (message IS NULL OR message = '') AND error_message != '' AND sync_status != 'synced';`.

When done, write `handoff.md` in your working directory and notify orchestrator_3 via send_message.
