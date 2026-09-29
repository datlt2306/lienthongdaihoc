# Dispatch for explorer_rules_1

## Objective
Analyze the admission rule matrix in `inc/eligibility-rules.php` and mapping logic in `inc/eligibility.php` against actual Vietnam MOET admission regulations (R1).

## Target Files
- `inc/eligibility-rules.php`
- `inc/eligibility.php`
- Context: `ORIGINAL_REQUEST.md`

## Required Deliverable
Write your deep findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_rules_1/report.md`.
Include:
1. Exact line numbers for all rule structures and mapping logic.
2. Comparison with MOET regulations (THPT, Trung cap, Cao dang, Dai hoc VB2).
3. Right/close/different major classification and duration logic (1.5, 2, 2.5, 4 years).
4. Special cases: Health (Y/Dược/Điều dưỡng), Teacher training (Sư phạm), Law (Luật) - accreditation, certificates, restrictions.
5. Gap Analysis: At least 3 critical business/legal risks and gaps.
6. When done, write `handoff.md` and send message to orchestrator.

## 2026-09-25T07:20:17Z
TASK:
Conduct an exhaustive line-by-line review of the admission matrix in `inc/eligibility-rules.php` and mapping logic in `inc/eligibility.php` against actual Vietnam MOET admission regulations (R1).
Analyze:
1. Input degree qualifications: THPT, Trung cấp nghề, Cao đẳng, Đại học (học VB2).
2. Right major (ngành đúng/phù hợp), close major (ngành gần), different major (ngành khác), and standard training durations (1.5, 2, 2.5, 4 years).
3. Regulated special fields: Health sciences (Y, Dược, Điều dưỡng - require practicing certificate/chứng chỉ hành nghề, academic score thresholds), Teacher training (Sư phạm), Law (Luật).
4. Identify all discrepancies, legal compliance gaps, missing rules, and business risks. Cite exact line numbers.
5. Provide detailed analysis of at least 5 typical candidate profiles (THPT to Distance, College same major, College different major, University 2nd degree, Health/Teacher candidate).
6. Provide Gap Analysis with at least 3 critical business risks/gaps.

Write your complete detailed findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_rules_1/report.md`.
Write `handoff.md` in your directory.
Send a message back to the orchestrator when finished. DO NOT modify any source code!
