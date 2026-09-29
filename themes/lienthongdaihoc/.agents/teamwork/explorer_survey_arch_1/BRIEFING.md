# BRIEFING — 2026-09-28T04:16:00Z

## Mission
Deep technical audit of Requirement R1: Data Architecture & Entity Modeling for Training Systems (`training_type`) and Partner Universities (`school`).

## 🔒 My Identity
- Archetype: explorer
- Roles: Teamwork explorer (read-only investigation, analysis, synthesis)
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_arch_1/
- Original parent: 8ecd8568-917b-4973-87b0-609a66bbbf3b (orchestrator_3)
- Milestone: Requirement R1 Technical Audit

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify theme source code
- Inspect code paths: `inc/post-types.php`, `inc/acf-fields.php`, `inc/core/class-entity-*.php`, `inc/database/`, `inc/eligibility*.php`, etc.
- Output findings to `analysis.md` and write self-contained `handoff.md`
- Report completion via send_message to orchestrator_3

## Current Parent
- Conversation ID: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Updated: 2026-09-28T04:16:00Z

## Investigation State
- **Explored paths**:
  * `inc/post-types.php` & `inc/acf-import-cpts.json`: Entity and taxonomy definitions.
  * `inc/acf-fields.php` & `inc/acf-import-fields.json`: ACF field groups, hidden fields, split-brain checkboxes.
  * `inc/relationship-hooks.php`: Bi-directional hook mechanisms, lifecycle gaps.
  * `inc/core/class-helpers.php`: Query helpers, badges, school majors count, learning details.
  * `inc/core/class-rewrite-rules.php`: Program rewrite rules and catch-all URL hijacking.
  * `archive-school.php`, `single-school.php`, `taxonomy-training_type.php`, `single-program.php`, `single-major.php`: Presentation and query ergonomics.
  * `inc/lead-capture.php`, `inc/crm-adapters.php`: Leads schema, CRM integration, lack of school-level routing.
  * `inc/eligibility.php`, `inc/eligibility-rules.php`: Eligibility matrix, scoring rules.
  * `inc/cli-commands.php`: Seed data scripts and taxonomy setup.
- **Key findings**:
  1. Triangular model (`School` ⟷ `Major` via `Program` intermediate entity) is fundamentally sound.
  2. Gaps: Ghost `training_type` on School, orphan IDs on post deletion, split-brain checkboxes, regex URL hijacking, unindexed leads table, and free-text admission batches.
  3. Solution: Hybrid Two-Tier Rollup architecture + Full Lifecycle Relationship Engine + Structured Batches + Input-Level Program Matrix + Multi-tenant CRM Routing.
- **Unexplored areas**: None for Requirement R1. All areas thoroughly investigated and documented.

## Key Decisions Made
- Deliver a comprehensive 8-section audit report `analysis.md` and 5-component `handoff.md`.

## Artifact Index
- DISPATCH.md — Initial dispatch instructions
- BRIEFING.md — Situational awareness and working memory
- progress.md — Liveness heartbeat and progress tracker
- analysis.md — Full in-depth technical analysis report
- handoff.md — 5-component self-contained handoff report
