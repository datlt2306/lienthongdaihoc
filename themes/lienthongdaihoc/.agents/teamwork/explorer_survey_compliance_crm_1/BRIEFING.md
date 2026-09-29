# BRIEFING — 2026-09-28T04:20:00Z

## Mission
Deep technical and business audit of R3 (Regulatory Compliance & Degree Trust) and R4 (Lead Routing & Admissions Funnel) for the WordPress theme lienthongdaihoc.

## 🔒 My Identity
- Archetype: explorer (teamwork_preview_explorer)
- Roles: investigation, synthesis
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_compliance_crm_1/
- Original parent: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Milestone: Audit of Requirements R3 & R4

## 🔒 Key Constraints
- Read-only investigation — do NOT implement / ZERO modification of theme source code
- Self-contained handoff.md with 5 components (Observation, Logic Chain, Caveats, Conclusion, Verification Method)
- Output findings in analysis.md and handoff.md
- Report completion via send_message to orchestrator_3 (parent id: 8ecd8568-917b-4973-87b0-609a66bbbf3b)

## Current Parent
- Conversation ID: 8ecd8568-917b-4973-87b0-609a66bbbf3b
- Updated: 2026-09-28T04:20:00Z

## Investigation State
- **Explored paths**:
  - `front-page.php`, `page-faq.php`, `single-school.php`, `single-program.php`, `single-major.php`, `page-register.php`, `taxonomy-training_type.php`, `archive-school.php`
  - `inc/lead-capture.php`, `inc/crm-adapters.php`, `inc/eligibility.php`, `inc/core/class-helpers.php`, `inc/post-types.php`, `inc/acf-fields.php`, `inc/acf-import-fields.json`, `inc/acf-import-cpts.json`, `inc/cli-commands.php`
- **Key findings**:
  1. R3 Compliance:
     - Violation of Circular 27/2019/TT-BGDĐT and Law on Advertising 2012: Hardcoded badge "100% BẰNG CỬ NHÂN CHÍNH QUY" (`front-page.php:528`) and colloquial "Bằng đỏ" (`front-page.php:432`).
     - Concealment of Diploma Supplement (Phụ lục văn bằng) in FAQs (`page-faq.php:31`).
     - Lack of catalogue-level enforcement for Circular 28/2023/TT-BGDĐT (prohibition on distance learning for Health & Teacher training).
     - Static duration claims vs credit exemption requirements under Circular 08/2021/TT-BGDĐT.
  2. R4 Lead Routing & Admissions Funnel:
     - Critical Data Loss Bug: User message stored in `error_message` and wiped out (`error_message = ''`) on successful CRM sync (`inc/crm-adapters.php:80`).
     - Telegram Blindness Bug: Free consultation form strips out school, major, program, training_type, and campus (`inc/lead-capture.php:243`).
     - Single Global CRM Bottleneck: One global CRM setting (`default_crm_type`) for the entire website; no per-school routing.
     - Payload Mapping Error: Vietnamese post titles sent to CRM `school_code` and `major_code` instead of canonical codes.
     - Funnel Leakage: `taxonomy-training_type.php` has no lead capture form.
     - CF7 Integration Bug: Dynamic context fields dropped when CF7 shortcode is rendered.
     - Admission Pause Bug: Program pause message hardcoded to "hệ Chính quy" (`single-program.php:96`).
- **Unexplored areas**: None for R3 and R4 scope (fully audited).

## Key Decisions Made
- Fully documented all technical and legal findings in `analysis.md`.
- Prepared 5-component `handoff.md`.
- Prepared concrete SQL migration, Multi-tenant routing architecture, Telegram fix, and compliant copy snippets.

## Artifact Index
- DISPATCH.md — Initial dispatch message
- BRIEFING.md — Working memory and situational awareness
- progress.md — Liveness heartbeat and step tracking
- analysis.md — Comprehensive technical and regulatory audit report
- handoff.md — 5-component handoff report for orchestrator
