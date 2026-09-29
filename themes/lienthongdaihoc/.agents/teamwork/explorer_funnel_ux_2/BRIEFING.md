# BRIEFING — 2026-09-25T08:15:25Z

## Mission
Conduct an exhaustive analysis of the 2-tier Lead Capture Funnel and the Wizard UX/UI in `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`, `page-eligible.php`, and `inc/eligibility.php` (R3 & R4).

## 🔒 My Identity
- Archetype: explorer
- Roles: Lead Capture Funnel & Wizard UX/UI Auditor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_funnel_ux_2
- Original parent: 75c2dc24-0c32-4c91-a36a-94dfdce7b011
- Milestone: Eligibility Business & Funnel Audit (R3 & R4)

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify source code
- Files for content delivery (`report.md`, `handoff.md`), Messages for coordination
- Exact line numbers and citations for all observations
- 5-component handoff report

## Current Parent
- Conversation ID: 75c2dc24-0c32-4c91-a36a-94dfdce7b011
- Updated: 2026-09-25T08:15:25Z

## Investigation State
- **Explored paths**: `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`, `page-eligible.php`, `inc/eligibility.php`, `inc/lead-capture.php`, `inc/eligibility-rules.php`, `assets/css/eligibility.css`
- **Key findings**:
  1. Single education lockout: `education` option is hardcoded to `cao-dang` only (blocking THPT, Trung cấp, VB2).
  2. Attribute collision: Label "Năm sinh" maps to input `graduation`, contaminating graduation recency scoring and DB storage.
  3. Data integrity flaw: `wp_ltdh_leads` lacks columns for academic background; developers dumped survey query strings into `referral_source` and notes into `error_message`.
  4. Search-select UX bugs: Lack of Vietnamese accent-folding causes 0 results on non-accented typing; 250ms blur timer causes selection drops on mobile; keyboard occludes dropdown.
  5. Telegram alert split: Tier 2A and 2B fire 2 separate unthreaded messages, causing advisor race conditions.
  6. Data privacy: Diploma uploads stored publicly in `/wp-content/uploads/` without protection under Decree 13/2023/ND-CP.
- **Unexplored areas**: None (analysis completed across all target files).

## Key Decisions Made
- Formulated an exhaustive audit report in `report.md` covering all 4 core dispatch areas with line numbers.
- Designed a Micro-Commitment Ladder CRO architecture with complete Finite State Machine (FSM).
- Provided production-ready markup and script refactoring solutions.

## Artifact Index
- `report.md` — Complete detailed audit report on Funnel and Wizard UX
- `handoff.md` — 5-component handoff report
