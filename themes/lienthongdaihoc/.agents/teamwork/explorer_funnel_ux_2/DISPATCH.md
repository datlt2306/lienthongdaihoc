# Dispatch for explorer_funnel_ux_2

## Objective
Analyze the 2-tier Lead Capture Funnel (Telegram bot, DB storage, document uploads) and Wizard UX/UI (step flow, client validation, search-select, mobile flow) (R3 & R4).

## Target Files
- `template-parts/eligibility/wizard.php`
- `template-parts/eligibility/results.php`
- `assets/js/eligibility.js`
- `page-eligible.php`
- `inc/eligibility.php`
- Context: `ORIGINAL_REQUEST.md`

## Required Deliverable
Write your deep findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_funnel_ux_2/report.md`.
Include:
1. Two-tier funnel journey breakdown:
   - Tier 1 (Anonymous preliminary quiz): Fields, interaction flow, AJAX submission, preliminary result rendering with line numbers.
   - Tier 2 (Lead capture & advanced verification): Full name, phone, email, graduation year, file upload (diploma/transcript), Telegram bot alert, lead DB persistence via `ltdh_insert_lead` and `wpdb` with line numbers.
2. Friction analysis, drop-off risks, data integrity issues sent to admissions advisors.
3. Multi-step wizard UX/UI: question sequence, mobile responsiveness, client-side validation in `assets/js/eligibility.js`, `data-search-select` search-filter UX.
4. CRO (Conversion Rate Optimization) recommendations with UX architecture, state machine, and code/markup snippets.

## 2026-09-25T08:11:23Z
TASK:
Conduct an exhaustive analysis of the 2-tier Lead Capture Funnel and the Wizard UX/UI in `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`, `page-eligible.php`, and `inc/eligibility.php` (R3 & R4).
Analyze:
1. Two-tier user journey: Tier 1 (Anonymous preliminary quiz) -> Tier 2 (Lead capture & advanced verification: name, phone, email, diploma/transcript upload, Telegram bot alert, lead DB persistence via wpdb). Cite exact line numbers.
2. Friction analysis, drop-off risks, data integrity issues sent to admissions advisors.
3. Multi-step wizard UX/UI: question sequence, mobile responsiveness, client-side validation in `assets/js/eligibility.js`, fast search-select mechanism (`data-search-select`).
4. Concrete CRO (Conversion Rate Optimization) recommendations with UX architecture, state machine, and code/markup snippets.

Write your complete detailed findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_funnel_ux_2/report.md`.
Write `handoff.md` in your directory.
Send a message back to the orchestrator when finished. DO NOT modify any source code!
