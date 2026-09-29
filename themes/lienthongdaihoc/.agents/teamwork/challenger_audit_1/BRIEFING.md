# BRIEFING — 2026-09-25T08:27:00Z

## Mission
Empirically and adversarially challenge mathematical formulas, scoring engine, credit exemption calculations, and 5 candidate profiles in ELIGIBILITY_BUSINESS_AUDIT.md.

## 🔒 My Identity
- Archetype: empirical_challenger
- Roles: critic, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_1
- Original parent: 75c2dc24-0c32-4c91-a36a-94dfdce7b011
- Milestone: eligibility_business_audit_challenge
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code of the theme
- Must run empirical tests and test harnesses directly
- Write-only to own folder: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_1/
- Deliver verdict: APPROVE or REQUEST_CHANGES in handoff.md

## Current Parent
- Conversation ID: 75c2dc24-0c32-4c91-a36a-94dfdce7b011
- Updated: 2026-09-25T08:27:00Z

## Review Scope
- **Files to review**: `ELIGIBILITY_BUSINESS_AUDIT.md`, `inc/eligibility.php`, `inc/eligibility-rules.php`, `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`
- **Review criteria**: Mathematical correctness, numerical stability, edge cases, candidate simulation accuracy, specification vs implementation fidelity.

## Attack Surface
- **Hypotheses tested**:
  1. Existing scoring weights sum to 90; web ceiling is 60 pts; API ceiling is 80 pts. -> CONFIRMED.
  2. Existing tuition multiplication ($tuition * 120 * duration) causes 2.77x to 60x inflation. -> CONFIRMED.
  3. Proposed credit exemption formula in Section 7.3 ($C_{\text{exempt}} = \text{round}(C_{\text{total}} \times K_{\text{edu}} \times K_{\text{align}})$) and duration estimation. -> FAILED (Critiqued).
  4. Proposed scoring engine in Section 7.5 handles 5 Candidate Profiles accurately. -> FAILED (Critiqued).
  5. Edge cases: division by zero, empty/0 desired major, 0 matching programs, unannounced tuition. -> 4 CRITICAL FLAWS IDENTIFIED.
- **Vulnerabilities found**:
  - **V1 (THPT Penalization)**: THPT has no prior major, so engine treats them as "trái ngành", penalizing major score from 35 to 12.25, adding "trái ngành" warning, adding 18 bridge credits (148 credits total, 4.1 yrs), and reducing score to 61/100 (audit claimed 90-95).
  - **V2 (VB2 Exemption Collapse)**: Degree factor (0.38) multiplied by alignment factor (0.40) slashes general education exemption from 49 to 20 credits; adding 18 bridge credits yields 128 credits remaining (only 2 credits saved!) and 3.6 years duration; score drops to 61/100 (audit claimed 95-100).
  - **V3 (Smart Alternatives Denial for THPT)**: Line 792 checks `$user_major > 0 && ltdh_elig_are_majors_related(...)`. For THPT ($user\_major = 0$), alternatives are always empty.
  - **V4 (Spec Table 7.2 vs Code 7.5 Inconsistencies)**: Unannounced tuition gets 15 pts in code vs 3 pts in table; empty training mode gets 18 pts in code vs 17 pts in table; credit < 15 credits gets 6 pts in code vs 0 pts in table.
  - **V5 (Desired Major = 0 Fallthrough)**: If user submits desired_major = 0, program passes hard gates and scores 56-65 points as eligible.
  - **V6 (Missing Health License/Rank Check in Engine)**: Heavy emphasis on CCHN and Khá/Giỏi in narrative, but zero enforcement in proposed PHP scoring engine.
- **Untested angles**: None. Full simulation conducted.

## Loaded Skills
- Empirical simulation harness executed via PHP 8.4 CLI and Python 3.14.

## Key Decisions Made
- Deliver verdict: **REQUEST_CHANGES** due to 6 critical discrepancies and mathematical flaws in proposed Section 7 formulas and code.
- Provide concrete mathematical corrections decoupling general education exemption from major alignment.

## Artifact Index
- `.agents/teamwork/challenger_audit_1/BRIEFING.md` — Agent working memory
- `.agents/teamwork/challenger_audit_1/progress.md` — Liveness and execution heartbeat
- `.agents/teamwork/challenger_audit_1/handoff.md` — Authoritative Challenger Deliverable with verdict REQUEST_CHANGES
