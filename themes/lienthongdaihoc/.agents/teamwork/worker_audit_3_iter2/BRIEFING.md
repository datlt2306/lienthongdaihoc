# BRIEFING — 2026-09-28T04:48:00Z

## Mission
Patch all 6 identified architectural and code snippet defects in SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md based on challenger_audit_3_1 and challenger_audit_3_2 findings, with zero modifications to theme source code.

## 🔒 My Identity
- Archetype: teamwork_preview_worker
- Roles: implementer, qa, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_audit_3_iter2/
- Original parent: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Milestone: Iteration 2 Audit Document Patching

## 🔒 Key Constraints
- ZERO modification of theme source code (PHP, JS, CSS, JSON). Only update SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md.
- Genuine implementations only, no hardcoding or dummy facades.
- All 6 action items must be thoroughly and accurately addressed.

## Current Parent
- Conversation ID: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Updated: 2026-09-28T04:48:00Z

## Task Summary
- **What to build**: Complete, production-grade drop-in code snippets and explanations in SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md resolving all issues identified by challengers:
  1. Section 2.4: `LTDH_Entity_Relationship_Engine` trashing/deleting hooks (`trashed_post`, `untrashed_post`, `before_delete_post`) with `post__not_in => [$post_id]` and save hooks (`acf/save_post` priority 25, `save_post_program` priority 25).
  2. Section 3.7.1: `archive-school.php` list view snippet replacing only lines 295-307, preserving `$prog_tags` and `$region_terms`.
  3. Section 3.7.2: `pre_get_posts` on `taxonomy-training_type.php` preserving user filter params (`$_GET['nhom_nganh']`, `$_GET['truong']`, `$_GET['s']`, `$_GET['sort']`).
  4. Section 3.7.4: Rank Math canonical URL filter targeting `is_post_type_archive('program')` / `/chuong-trinh/`.
  5. Section 5.6.3: `ltdh_trigger_telegram_notification_v2` without `rawurlencode($bot_token)` and with multi-casting (central admin + partner school Telegram group).
  6. Section 5.6 & 7.2: Dynamic `$wpdb->prefix`, safe PHP DDL migration checking if column exists, data backfill query.
- **Success criteria**: All 6 action items accurately patched into SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md with no theme source code modifications, verified against original requests and challenger analysis.
- **Interface contracts**: SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md

## Change Tracker
- **Files modified**:
  - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`: Patched Sections 2.4, 3.7.1, 3.7.2, 3.7.3, 3.7.4, 5.6.1, 5.6.3, 7.1 (row 1.7), and 7.2 (row 2.1).
- **Build status**: All patches applied cleanly and verified.
- **Pending issues**: None. Ready for auditor review.

## Quality Status
- **Build/test result**: All 6 action items successfully implemented and verified.
- **Lint status**: Clean.
- **Tests added/modified**: N/A (documentation audit file update with zero theme code modifications).

## Loaded Skills
- Source: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md
- Core methodology: WordPress theme & plugin development, hooks, post types, taxonomies, ACF integration, WPDB queries.

## Key Decisions Made
- [Initial] Audit doc patched directly with exact verified drop-in code chunks.
- [Patch 1] Section 2.4 uses `trashed_post`, `untrashed_post`, `before_delete_post` with `post__not_in`, and dual priority 25 save hooks (`acf/save_post` and `save_post_program`).
- [Patch 2] Section 3.7.1 replaces only lines 295-307 in `archive-school.php`, protecting lines 278-290 (`$prog_tags`) and lines 292-293 (`$region_terms`), preventing PHP 8 undefined variable notices.
- [Patch 3] Section 3.7.2 supports all user filters (`$_GET['nhom_nganh']`, `$_GET['truong']`, `$_GET['s']`, `$_GET['sort']`, `$_GET['limit']`) and uses `$wp_query->max_num_pages`.
- [Patch 4] Section 3.7.4 targets `is_post_type_archive('program')` and path `/chuong-trinh/` to eliminate canonical 301 loop.
- [Patch 5] Section 5.6.3 implements multi-casting to central admin + school group and preserves token `:` delimiter.
- [Patch 6] Section 5.6 & 7.2 implements safe PHP DDL migration with dynamic `$wpdb->prefix`, idempotency checks, and historic data backfill query.

## Artifact Index
- DISPATCH.md — Initial assignment
- BRIEFING.md — Situational awareness
- progress.md — Liveness heartbeat and step tracking
- handoff.md — Final 5-component handoff report
