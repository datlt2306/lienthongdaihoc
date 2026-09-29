# BRIEFING — 2026-09-28T04:26:15Z

## Mission
Adversarially stress-test and empirically challenge the findings, assertions, and proposed code snippets in SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md for R1 & R2.

## 🔒 My Identity
- Archetype: challenger
- Roles: critic, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_3_1/
- Original parent: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Milestone: Audit Challenge R1 & R2
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify theme source code
- Audit and challenge only
- Self-contained handoff report with explicit verdict: APPROVE or REQUEST_CHANGES
- Write findings to analysis.md and handoff.md

## Current Parent
- Conversation ID: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Updated: 2026-09-28T04:36:40Z

## Review Scope
- **Files to review**:
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/archive-school.php
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/taxonomy-training_type.php
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/assets/js/main.js
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/acf-import-cpts.json
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/core/class-rewrite-rules.php
  - /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/relationship-hooks.php
- **Interface contracts**: ORIGINAL_REQUEST.md requirements (timestamp 2026-09-28T04:04:15Z)
- **Review criteria**: Empirical correctness, query performance calculation, edge cases, PHP code snippet soundness

## Attack Surface
- **Hypotheses tested**:
  - N+1 query claim on `archive-school.php`: Confirmed and refined to ~466 queries/pageview.
  - Missing `training_type` on `school` in JSON: Confirmed (`object_type` only has `program`).
  - Regex rewrite rule hijacking URL namespace: Confirmed (forces 2 SQL queries on all static/404 pages).
  - Missing `#program-results-container` in DOM: Confirmed (100% dead AJAX filter code).
  - Code snippet validation: Discovered 5 critical defects in proposed fixes.
- **Vulnerabilities found**:
  - BUG-CH-01: `LTDH_Entity_Relationship_Engine` hooks `wp_trash_post` & `before_delete_post`, re-saving orphan IDs.
  - BUG-CH-02: `archive-school.php` fix snippet deletes `$prog_tags` & `$region_terms`, causing PHP 8 warning and breaking UI tag pills.
  - BUG-CH-03: `pre_get_posts` fix snippet omits `nhom_nganh`, `truong`, `s`, `sort` filters.
  - BUG-CH-04: Canonical fix filter targets `/he-dao-tao/`, failing to resolve `/chuong-trinh/` 301 loop.
  - BUG-CH-05: `rawurlencode($bot_token)` in Telegram Bot v2 breaks colon separator, returning HTTP 404.
- **Untested angles**: Full DB load testing under high concurrency.

## Loaded Skills
- Source: php-wordpress (/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md)
  - Core methodology: WordPress development mastery - themes, plugins, Gutenberg blocks, and REST API

## Key Decisions Made
- Verdict: REQUEST_CHANGES. Audit observations are accurate, but proposed code solutions contain critical regressions that must be corrected before implementation.
- Generated comprehensive analysis report in `analysis.md` and complete handoff report in `handoff.md`.

## Artifact Index
- DISPATCH.md — Initial dispatch instructions
- BRIEFING.md — Working memory and identity
- progress.md — Liveness heartbeat and milestone tracker
- analysis.md — Full empirical challenge report and revised code snippets
- handoff.md — Authoritative handoff report with explicit REQUEST_CHANGES verdict
