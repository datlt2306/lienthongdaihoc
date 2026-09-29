# Dispatch for challenger_audit_1_v2

## Objective
Re-verify the updated mathematical formulas, scoring engine, credit decoupling, and candidate profiles in `ELIGIBILITY_BUSINESS_AUDIT.md` (v2.1).

## Verification Target
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md`

## Required Verification
1. Check that Section 7.3 decoupled formula ($C_{\text{exempt}} = C_{\text{gen\_exempt}} + C_{\text{spec\_exempt}}$) is correctly integrated.
2. Check that Profile 1 (THPT freshmen) and Profile 4 (VB2) calculations are fully resolved (no 18-credit penalty for THPT, VB2 retains full general credit waiver and 90-95 score).
3. Check that Smart Alternatives in Section 7.5 no longer blocks THPT candidates ($user_major > 0$ removed).
4. Check that desired_major <= 0 hard gate is present.
5. Check Table 7.2 and Code 7.5 alignment for unannounced tuition (0.20 / 3.0 pts).
6. Check Health Sciences checks in engine code.
7. Deliver verdict: `APPROVE` or `REQUEST_CHANGES` in `handoff.md`.

## 2026-09-25T08:40:22Z
Received dispatch from orchestrator (parent: 75c2dc24-0c32-4c91-a36a-94dfdce7b011).
Re-verify the updated mathematical formulas, scoring engine, credit decoupling, and candidate profiles in `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md` (v2.1).
Check:
1. Section 7.3 decoupled formula ($C_{\text{exempt}} = C_{\text{gen\_exempt}} + C_{\text{spec\_exempt}}$).
2. Profile 1 (THPT freshmen) and Profile 4 (VB2) calculations.
3. Smart Alternatives ($user_major > 0$ removed).
4. desired_major <= 0 hard gate.
5. Table 7.2 and Code 7.5 unannounced tuition alignment.
6. Health Sciences practicing license and academic rank checks.
Write `handoff.md` with explicit verdict (`APPROVE` or `REQUEST_CHANGES`).
Send message back to orchestrator.
