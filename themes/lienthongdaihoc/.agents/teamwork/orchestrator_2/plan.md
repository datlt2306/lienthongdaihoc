# Orchestration Plan - Eligibility Business Audit

## Objective
Deliver a comprehensive, deep business audit report `ELIGIBILITY_BUSINESS_AUDIT.md` covering all 5 requirements (R1-R5) and meeting 100% of Acceptance Criteria without modifying any theme code.

## Phase 1: Survey & Deep Multi-Domain Codebase Exploration
Dispatch 3 parallel Explorers:
- **Explorer 1 (explorer_rules_1)**:
  - Focus: R1 (Admission Matrix, MOET Regulations & Degree Conversion, Special cases: Health, Teacher Training, Law, Vocational to University, College to University, 2nd Degree).
  - Files: `inc/eligibility-rules.php`, `inc/eligibility.php`.
- **Explorer 2 (explorer_scoring_1)**:
  - Focus: R2 (Scoring algorithm, compatibility %, credit exemption calculation, duration estimation, program ranking & recommendation algorithm, edge cases like negative score, >100%, div by 0, missing majors).
  - Files: `inc/eligibility.php`, `inc/eligibility-rules.php`, `template-parts/eligibility/results.php`.
- **Explorer 3 (explorer_funnel_ux_1)**:
  - Focus: R3 & R4 (2-tier Lead Capture Funnel, document upload, Telegram bot notification, database persistence, Wizard UI/UX flow, client-side validation, search-select, mobile flow).
  - Files: `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `assets/js/eligibility.js`, `page-eligible.php`, `inc/eligibility.php`.

## Phase 2: Synthesis & Deliverable Authoring
- Worker (worker_audit_writer_1):
  - Synthesizes all Explorer reports.
  - Drafts and writes the complete `ELIGIBILITY_BUSINESS_AUDIT.md` at project root (`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md`).
  - Ensures all 4 core components are exhaustively detailed:
    1. Complete Business Flowchart / State Machine.
    2. Gap Analysis table & evaluation against Vietnam admissions reality (>= 3 critical business risks/gaps).
    3. Minimum 5 concrete real-world test scenarios (THPT to Distance, College same major, College different major, University 2nd degree, Health/Teacher candidate).
    4. Proposed upgraded scoring algorithm & CRO optimization with architecture & code snippets.
    - Exact file paths and line numbers mapped for 100% of relevant code logic.
    - Zero modification to existing theme code.

## Phase 3: Gate & Multi-Agent Quality Assurance
- Reviewer 1 (reviewer_1): Content completeness, technical accuracy against all 6 files, line number precision.
- Reviewer 2 (reviewer_2): Real-world business logic fidelity, MOET regulation compliance, CRO proposals.
- Challenger 1 (challenger_1): Stress-test all 5 test scenarios and edge-cases against the proposed upgraded logic and existing logic.
- Challenger 2 (challenger_2): Review UX flow, friction analysis, client validation edge cases.
- Forensic Auditor (auditor_1): Integrity verification (no theme code modified, no mocked/hallucinated findings, complete genuine report).

## Phase 4: Sign-off & Completion
- Record gate verdicts in `GATE_STATUS.md`.
- Ensure all criteria pass.
- Write `handoff.md`.
- Send completion message to parent.
