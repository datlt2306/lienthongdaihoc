# Progress — explorer_survey_compliance_crm_1

Last visited: 2026-09-28T04:22:00Z

## Status
- [x] Initialized DISPATCH.md and BRIEFING.md
- [x] Task 1: Regulatory Compliance & Degree Trust Audit (R3)
  - [x] Search theme files for regulatory citations (Thông tư 27/2019, TT 28/2023, TT 08/2021, Luật GDĐH 2018)
  - [x] Inspect single-school.php, taxonomy-training_type.php, front-page.php, inc/, template-parts/
  - [x] Evaluate Hero copy, degree badges, sample diploma displays, FAQs
  - [x] Check legal degree equivalence communication accuracy & misleading claims (Identified critical violations: "100% BẰNG CỬ NHÂN CHÍNH QUY", "Bằng đỏ", "Phôi bằng tương đương chính quy", concealment of Phụ lục văn bằng)
- [x] Task 2: Lead Routing & Admissions Funnel Audit (R4)
  - [x] Inspect inc/lead-capture.php, inc/crm-adapters.php, inc/eligibility.php, AJAX submission handlers
  - [x] Analyze form payloads on school & training_type pages (school code, training type code, campus, program/major) - Found payload degradation & complete absence of lead capture on taxonomy-training_type.php
  - [x] Audit routing logic (OnSchool API, AUM CRM, Telegram Bot, fallback/email) - Found single global CRM bottleneck, passing Vietnamese post titles instead of codes, Telegram stripping context on consultation forms
  - [x] Evaluate extensibility, partner onboarding/offboarding, suspension of school+training_type pairs (Found hardcoded badges, program-level lock-in, misleading pause message)
- [x] Task 3: Vulnerabilities, Compliance Risks, Lead Leakage & Improvements
  - [x] Identified critical data loss bug (user message overwritten in error_message column on CRM sync)
  - [x] Identified CF7 context dropping bug in class-helpers.php
  - [x] Developed comprehensive architecture diagrams and remediation code snippets
- [x] Task 4: Documentation & Handoff
  - [x] Compile comprehensive `analysis.md` (Finished)
  - [x] Write 5-component `handoff.md` (Finished)
  - [x] Update BRIEFING.md and progress.md (Finished)
  - [x] Send completion message to orchestrator_3
