# BRIEFING — 2026-09-28T04:31:30Z

## Mission
Adversarially stress-test and empirically challenge the findings, assertions, and proposed code snippets in SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md.

## 🔒 My Identity
- Archetype: challenger
- Roles: critic, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_3_2
- Original parent: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Milestone: Audit 3.2 Adversarial Challenge
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code
- Zero modification of theme source code. Audit and challenge only.
- Output empirical challenge findings to analysis.md and handoff.md with verdict: APPROVE or REQUEST_CHANGES
- Report completion via send_message to orchestrator_3

## Current Parent
- Conversation ID: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Updated: 2026-09-28T04:31:30Z

## Review Scope
- **Files to review**: SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md, .agents/teamwork/ORIGINAL_REQUEST.md, front-page.php, inc/lead-capture.php, inc/crm-adapters.php, inc/eligibility.php, inc/relationship-hooks.php, inc/core/class-rewrite-rules.php
- **Interface contracts**: ORIGINAL_REQUEST.md timestamp 2026-09-28T04:04:15Z
- **Review criteria**: Legal veracity of Thông tư 27/2019/TT-BGDĐT, code audit of front-page.php (528-530, 432), lead-capture.php (139, 243-255), crm-adapters.php (80), CRM data loss bug execution flow, SQL migration safety, Multi-Tenant Lead Router logic

## Attack Surface
- **Hypotheses tested**:
  1. H1: Does Thông tư 27/2019/TT-BGDĐT truly mandate omitting training mode from diplomas but including it on the Diploma Supplement? -> Verified TRUE (Điều 2 vs Điều 3).
  2. H2: Does front-page.php:528-530 literally say "100% BẰNG CỬ NHÂN CHÍNH QUY" and 432 say "Bằng đỏ"? -> Verified TRUE.
  3. H3: Does ltdh_process_lead_queue() really wipe the user's message upon CRM sync? -> Verified TRUE via PHP test harness (both sync success and failure destroy message).
  4. H4: Is the proposed SQL migration safe, idempotent, and backward-compatible? -> CHALLENGED & DISPROVEN (hardcodes prefix `wp_`, lacks idempotency, and critically omits historic data backfill).
  5. H5: Is the proposed Multi-Tenant Lead Router and Entity Engine safe as written? -> CHALLENGED & FOUND FLAWS (`rawurlencode` breaks Telegram Bot API token, central admin alerts get muted, and ACF hook timing in `LTDH_Entity_Relationship_Engine` causes race condition with postmeta).
- **Vulnerabilities found**:
  - Missing data backfill in proposed SQL migration causing silent loss of pending user notes.
  - Race condition in `save_post` vs `acf/save_post` in proposed `LTDH_Entity_Relationship_Engine`.
  - Bot token encoding bug (`rawurlencode` converting `:` to `%3A`).
  - CRM data loss bug confirmed to also erase Level 2B degree verification links in `inc/eligibility.php:773, 945-968`.
- **Untested angles**:
  - External network API response times from live Telegram Bot API (sandbox offline).

## Loaded Skills
- php-wordpress: WordPress Core standards, hooks, wpdb lifecycle, and database migrations.

## Key Decisions Made
- Issue verdict `REQUEST_CHANGES` to require patching the proposed SQL migration (add backfill and safe prefix execution), fixing the ACF hook timing in the Entity Engine, and correcting the Telegram token encoding and multi-casting logic before developer handoff.

## Artifact Index
- analysis.md — Detailed empirical challenge report with execution traces and test harness outputs.
- handoff.md — 5-component handoff report with explicit verdict REQUEST_CHANGES.
- progress.md — Liveness heartbeat.
- DISPATCH.md — Incoming dispatch log.
