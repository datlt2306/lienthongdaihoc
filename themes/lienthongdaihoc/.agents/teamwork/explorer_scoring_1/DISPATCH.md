# Dispatch for explorer_scoring_1

## Objective
Analyze compatibility scoring formulas, program recommendation & ranking algorithms, credit exemption & duration estimation, and edge-cases (R2).

## Target Files
- `inc/eligibility.php`
- `inc/eligibility-rules.php`
- `template-parts/eligibility/results.php`
- Context: `ORIGINAL_REQUEST.md`

## Required Deliverable
Write your deep findings to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_scoring_1/report.md`.
Include:
1. Exact formula and weight breakdown for condition scoring (100-point scale / % compatibility) with line numbers.
2. Program ranking & recommendation algorithm (matching major, campus, tuition budget, learning mode) with line numbers.
3. Credit exemption estimation and duration logic with line numbers.
4. Edge cases & hazards:
   - Major selected with no available programs / schools.
   - Negative scores or > 100%.
   - Division by zero.
   - Recommending incompatible programs.
5. Algorithmic upgrade proposals (mathematical formulas, pseudo-code / code snippets).
6. When done, write `handoff.md` and send message to orchestrator.
