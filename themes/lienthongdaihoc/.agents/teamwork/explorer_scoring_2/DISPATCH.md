# Dispatch for explorer_scoring_2

## Objective
Analyze compatibility scoring formulas, program recommendation & ranking algorithms, credit exemption & duration estimation, and edge-cases (R2).

## Target Files
- `inc/eligibility.php`
- `inc/eligibility-rules.php`
- `template-parts/eligibility/results.php`
- Context: `ORIGINAL_REQUEST.md`

## Required Deliverable
Write your deep findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_scoring_2/report.md`.
Include:
1. Exact formula and weight breakdown for condition scoring (100-point scale / % compatibility) with line numbers in `inc/eligibility.php` and `inc/eligibility-rules.php`.
2. Program ranking & recommendation algorithm (matching major, campus location, tuition budget, learning mode) with line numbers.
3. Credit exemption estimation and duration logic with line numbers.
4. Edge cases & hazards:
   - Major selected with no available programs / schools.
   - Negative scores or scores > 100%.
   - Division by zero risks.
   - Recommending incompatible programs.
5. Concrete algorithmic upgrade proposals (mathematical formulas, weighting tables, sample PHP code / architecture).
6. Write `handoff.md` and send message to orchestrator.

## 2026-09-25T08:11:23Z
You are explorer_scoring_2.
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_scoring_2
Read /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md and /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_scoring_2/DISPATCH.md.

TASK:
Conduct an exhaustive line-by-line analysis of the scoring algorithm, program ranking & recommendation, credit exemption, and edge cases in `inc/eligibility.php`, `inc/eligibility-rules.php`, and `template-parts/eligibility/results.php` (R2).
Analyze:
1. Exact mathematical formula and weighting breakdown for condition scoring (100-point scale / % compatibility), citing exact line numbers.
2. Program ranking & recommendation algorithm (matching major, campus location, tuition budget, learning mode), citing exact line numbers.
3. Credit exemption estimation and duration logic with line numbers.
4. Edge cases & hazards:
   - User selects major that has no training institutions.
   - Negative scores or scores > 100%.
   - Division by zero risks.
   - Recommending incompatible programs.
5. Formulate concrete proposed upgrades to the scoring algorithm with mathematical formulations, weighting tables, and sample PHP code / architecture.

Write your complete detailed findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_scoring_2/report.md`.
Write `handoff.md` in your directory.
Send a message back to the orchestrator when finished. DO NOT modify any source code!
