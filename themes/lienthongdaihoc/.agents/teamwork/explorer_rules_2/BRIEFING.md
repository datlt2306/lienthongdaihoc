# BRIEFING — 2026-09-25T08:14:00Z

## Mission
Exhaustive line-by-line review of admission matrix in inc/eligibility-rules.php and mapping logic in inc/eligibility.php against Vietnam MOET regulations.

## 🔒 My Identity
- Archetype: explorer
- Roles: explorer, investigator, synthesis
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_rules_2
- Original parent: 75c2dc24-0c32-4c91-a36a-94dfdce7b011
- Milestone: R1 Admission Matrix & Legal MOET Compliance Review

## 🔒 Key Constraints
- Read-only investigation — do NOT implement
- Do not modify source code outside .agents/teamwork/explorer_rules_2
- Exhaustive line-by-line review of inc/eligibility-rules.php and inc/eligibility.php
- Cite exact file paths and line numbers
- Adhere strictly to Vietnam MOET regulations (QĐ 18/2017/QĐ-TTg, TT 08/2022/TT-BGDĐT, TT 28/2023/TT-BGDĐT, Luật GDĐH, Luật Khám chữa bệnh)

## Current Parent
- Conversation ID: 75c2dc24-0c32-4c91-a36a-94dfdce7b011
- Updated: 2026-09-25T08:11:23Z

## Investigation State
- **Explored paths**:
  * `inc/eligibility-rules.php` (lines 1-161)
  * `inc/eligibility.php` (lines 1-1703)
  * `template-parts/eligibility/wizard.php` (lines 1-122)
  * `template-parts/eligibility/results.php` (lines 1-169)
  * `page-eligible.php` (lines 1-45)
  * `assets/js/eligibility.js` (lines 1-673)
  * `inc/config/constants.php`, `inc/post-types.php`, `inc/acf-fields.php`, `inc/acf-import-fields.json`
- **Key findings**:
  1. Input education validation in `inc/eligibility.php:288-301` hardcodes `$valid_education = ['cao-dang']`, blocking THPT, Trung cấp, and Đại học (VB2).
  2. Training type array in `inc/eligibility.php:292` strips `'van-bang-2'`.
  3. Slug `'thap-phan'` in `inc/eligibility-rules.php:18,75,598` is a severe misnomer/mistranslation for THPT.
  4. Legal violation against Circular 28/2023/TT-BGDĐT: Code allows Distance Learning (`tu-xa`) for Health Sciences and Teacher Training without restriction.
  5. Missing accreditation prerequisites: No practicing certificate (CCHN/GPHN) checks (QĐ 18/2017 & Law on Medical Examination and Treatment 2023), no entrance score/academic threshold (Khá/Giỏi) checks (TT 08/2022/TT-BGDĐT).
  6. Financial calculation bug at `inc/eligibility.php:486` (`$total_cost = $tuition_num * 120 * $duration_num`) inflates tuition multi-fold.
  7. Discrepancy between field `graduation` and frontend label "Năm sinh".
  8. ACF fields `elig_training_types`, `elig_campuses`, `elig_max_grad_years`, `elig_notes` are completely ignored in `ltdh_elig_run_check()`.
- **Unexplored areas**: None, all critical paths explored.

## Key Decisions Made
- Structuring `report.md` into comprehensive sections covering Executive Summary, Line-by-Line Audit, Regulatory Alignment, 5 Candidate Profiles, Gap Analysis & Business Risks, and Architectural Recommendations.

## Artifact Index
- DISPATCH.md — Task assignment and instructions
- BRIEFING.md — Context and identity memory
- progress.md — Heartbeat and activity log
- report.md — Comprehensive Audit and Findings Report (R1)
- handoff.md — Standard 5-component handoff report
