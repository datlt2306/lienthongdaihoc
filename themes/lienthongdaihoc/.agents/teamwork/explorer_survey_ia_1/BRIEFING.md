# BRIEFING — 2026-10-01T09:25:00Z

## Mission
Survey existing database records of CPTs `program`, `school`, `major`, taxonomy terms (`training_type`, `campus`, `major_cat`), classify in-scope vs out-of-scope records for Liên thông đại học, analyze relations, and draft safe audit specifications for R1.

## 🔒 My Identity
- Archetype: explorer
- Roles: Data Scope Explorer, Synthesis
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_1
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M1 — Discovery & System Survey

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify database/code
- Investigate DB records of program, school, major via WP-CLI / PHP
- Categorize in-scope (Liên thông) vs out-of-scope (VB2, Chính quy, Cao đẳng online...) vs uncertain
- Inspect schools & majors taxonomy terms & program relationships
- Check campus Online term
- Document concrete specs for audit script (R1) with safe draft/private transition and audit_report.json

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: not yet

## Investigation State
- **Explored paths**:
  - WP-CLI queries on database for CPTs `program`, `school`, `major`, `page`, `post`, `guide`
  - Taxonomies: `training_type`, `campus`, `region`, `major_cat`
  - Codebase: `inc/post-types.php`, `inc/relationship-hooks.php`, `inc/cli-commands.php`, `inc/config/constants.php`, `inc/config/class-defaults.php`, `inc/core/class-helpers.php`, `inc/core/class-rewrite-rules.php`, `archive-program.php`, `taxonomy-training_type.php`, `taxonomy.php`, `single-school.php`, `single-major.php`, `single-program.php`, `front-page.php`, `header.php`, `template-parts/banner.php`
- **Key findings**:
  1. Total `program` records: 100 (all `publish`).
  2. `training_type` terms: `tu-xa` (98), `vua-hoc-vua-lam` (1: ID 1854), `chinh-quy` (1: ID 2013), `van-bang-2` (0).
  3. `campus` terms: `ha-noi` (35), `ho-chi-minh` (32), `online` (32), `ha-noi,ho-chi-minh` (1). Term `online` is conflating training delivery mode with physical location.
  4. Total `school` records: 21 (all `publish`). 20 are universities/academies; 1 is a junior college: ID 1662 (Trường Cao đẳng Thương mại và Du lịch Hà Nội - HCCT).
  5. Total `major` records: 34 (all `publish`), all mapped to `major_cat`.
  6. Out-of-scope records: Exactly 5 programs:
     - ID 2013: Cử nhân CNTT Liên thông Chính quy (UTC) -> `chinh-quy`
     - ID 1786, 1787, 1788, 1789: 4 programs belonging to HCCT (Junior college)
     - School ID 1662 (HCCT) also out-of-scope (0 remaining university programs).
  7. In-scope records: Exactly 95 programs (94 Từ xa, 1 Vừa học vừa làm), spanning 20 universities and all 34 majors.
  8. Orphaned metadata: Program IDs 1855, 1856 are orphaned in `_offered_programs` of School 1853 (UTC) and Major 1677 (CNTT).
- **Unexplored areas**: None within survey scope. Ready for handoff to Worker and Reviewer.

## Key Decisions Made
- Confirmed concrete classification rules for R1 audit script.
- Confirmed safe draft transition without hard delete.
- Confirmed need to clean orphaned IDs and synchronize `_offered_programs`.

## Artifact Index
- DISPATCH.md — Dispatch instructions
- BRIEFING.md — Situational awareness
- progress.md — Liveness & progress tracker
- handoff.md — Detailed survey analysis & audit specifications
