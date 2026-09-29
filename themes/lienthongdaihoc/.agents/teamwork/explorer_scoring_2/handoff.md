# Handoff Report — explorer_scoring_2 (R2 Scoring, Ranking & Credit Exemption)

## 1. Observation
- **O1 (Scoring Weights Deficit)**: `inc/eligibility-rules.php` lines 99-108 defines scoring weights: `major_match => 30`, `major_related => 15`, `graduation_recent => 10`, `budget_match => 20`, `campus_match => 10`, `schedule_match => 5`. The sum is $30 + 15 + 10 + 20 + 10 + 5 = 90$ points, failing to sum to 100 points.
- **O2 (Omission of Graduation Recent Criterion)**: `inc/eligibility.php` lines 385-507 evaluates `$weights['major_match']` (line 417), `$weights['major_related']` (lines 435, 438), `$weights['schedule_match']` (line 459), `$weights['campus_match']` (lines 468, 471), and `$weights['budget_match']` (lines 490, 493, 502). `graduation_recent` is never evaluated anywhere in `inc/eligibility.php` (0 occurrences).
- **O3 (Frontend Budget Field Missing & Score Collapse)**: `template-parts/eligibility/wizard.php` lines 1-122 contains inputs for `education` (line 25), `major_id` (line 34), `desired_major` (line 59), `training_type` (line 74), and `campus` (line 93). There is NO input for `budget`. Consequently, `assets/js/eligibility.js` lines 314 hardcodes `data.append('budget', '');`. Because `$input['budget']` is empty, the budget scoring block in `inc/eligibility.php` lines 481-505 is skipped, capping real-world UI scores at $30 + 15 + 5 + 10 = 60$ points.
- **O4 (Cost Calculation Explosion)**: `inc/eligibility.php` line 486 computes total cost as:
  `$total_cost = $tuition_num * 120 * $duration_num;`
  For per-credit tuition (e.g., 450,000 VND) and duration 1.5 years, cost is $450,000 \times 120 \times 1.5 = 81,000,000$ VND instead of 54,000,000 VND (120 credits total). For semester tuition (e.g., 12,000,000 VND/semester), `ltdh_elig_parse_tuition()` strips `/học kỳ` and yields $12,000,000 \times 120 \times 2 = 2,880,000,000$ VND (2.88 billion VND), failing all budget ranges in `ltdh_elig_get_budget_ranges()` (lines 60-67).
- **O5 (Credit Exemption Logic Absent)**: Grep search across `inc/` for `credit`, `exemption`, `miễn giảm`, and `tín chỉ` confirmed that `inc/eligibility.php` and `inc/eligibility-rules.php` contain zero lines of code calculating credit exemptions, remaining credits, or course duration reduction.
- **O6 (SQL Pre-filter vs Online Fallback Conflict)**: `inc/eligibility.php` lines 355-365 appends a taxonomy query for `campus` slug. If a user selects `campus = 'ha-noi'`, only programs tagged with `ha-noi` are retrieved. In Step 2, line 470 (`elseif ( in_array( 'online', $all_campuses, true ) )`), online programs lacking the `ha-noi` slug were already filtered out by MySQL, making line 470 dead/unreachable code.
- **O7 (Alternatives Payload Mismatch & Irrelevant Recommendation)**: `inc/eligibility.php` lines 558-568 builds `$alternatives` by taking the first 5 programs in `$rejected` and only returning `program_id`, `title`, and `reason`. Meanwhile, `assets/js/eligibility.js` line 405 passes each alternative to `renderProgramCard()`, which expects `score`, `school`, `tuition_fee`, `duration`, `campus_info`, and `mismatch_reasons`, causing broken/empty card rendering and recommending unrelated majors (e.g. Nursing for an IT applicant).

## 2. Logic Chain
1. From O1 and O2, the maximum achievable score in the scoring engine is $30 + 15 + 5 + 10 + 20 = 80$ points, despite `min($match_score, 100)` at line 507.
2. From O3, because the frontend form omits the budget field, `$input['budget']` is always empty in production, which means lines 481-505 are never entered. Thus, the effective maximum score displayed to users is 60%, and no candidate can ever achieve "Phù hợp hoàn hảo" or priority >= 80%.
3. From O4, when budget is submitted via REST or testing, the arithmetic error multiplies unit fee by 120 AND by duration in years. Any program whose tuition is configured per semester/year evaluates to billions of VND, falsely categorizing viable programs as over-budget and downgrading status to `needs_verification`.
4. From O5, despite front-end marketing claims ("Liên thông miễn giảm tín tối đa"), the core eligibility engine provides no dynamic estimation of exempted credits or shortened study duration based on academic compatibility.
5. From O6 and O7, candidates selecting a specific campus or a major not offered locally are barred from seeing available online alternatives due to premature SQL filtering, and receive 5 arbitrary disqualified programs from unrelated fields.

## 3. Caveats
- The investigation focused on `inc/eligibility.php`, `inc/eligibility-rules.php`, `template-parts/eligibility/results.php`, `template-parts/eligibility/wizard.php`, and `assets/js/eligibility.js`.
- Database data in actual production (real program posts, ACF metadata values) was analyzed via schema definitions (`inc/acf-import-fields.json`) and CLI seeders (`inc/cli-commands.php`). Actual live database rows may have varying degrees of missing ACF relationships.
- No source files were modified during this investigation.

## 4. Conclusion
The Eligibility scoring and recommendation engine (R2) suffers from mathematical incompleteness (80% theoretical cap, 60% UI cap), severe pricing arithmetic errors (billions VND calculation for semester tuition), complete absence of the credit exemption feature, architectural conflict between SQL tax queries and in-memory fallback, and broken alternative program recommendations. A transition to a two-tier decision engine (Hard Gates + Normalized 100-pt Soft Scoring) with dynamic credit exemption modeling (`LTDH_Eligibility_Scoring_Engine`) is required.

## 5. Verification Method
1. **Verify Scoring Cap**: Inspect lines 99-108 in `inc/eligibility-rules.php` and lines 417, 435, 459, 468, 490 in `inc/eligibility.php`. Calculate sum of weights: $30 + 15 + 5 + 10 + 20 = 80$. Verify absence of `graduation_recent` via `grep_search`.
2. **Verify Budget Absence in Form**: Inspect `template-parts/eligibility/wizard.php` for `name="budget"`. Observe 0 matches. Inspect `assets/js/eligibility.js:314` for `data.append('budget', '')`.
3. **Verify Tuition Calculation Error**: In `inc/eligibility.php:486`, test `$tuition_num = 15000000; $duration_num = 2; $total_cost = 15000000 * 120 * 2 = 3600000000;`. Compare with `$budget_ranges` max value 50,000,000.
4. **Verify Missing Credit Exemption**: Run `grep_search` for `credit`, `exemption`, `miễn giảm` across `inc/eligibility.php`.
5. **Detailed Documentation**: Full line citations, mathematical proofs, edge-case walkthroughs, and sample PHP class architecture are available in `.agents/teamwork/explorer_scoring_2/report.md`.
