# Progress: Milestone M2 Empirical Challenge

- **Last visited**: 2026-10-01T09:55:00Z
- **Current Step**: Test suite execution complete; synthesizing handoff report
- **Status**: IN_PROGRESS

## Test Plan & Empirical Results
- [x] Step 1: Review worker_m2 changes & inspect modified files. (PASSED)
- [x] Step 2: Syntax verification (`php -l`) across all 6 modified files:
  - `inc/core/class-helpers.php` -> No syntax errors detected.
  - `single-school.php` -> No syntax errors detected.
  - `single-major.php` -> No syntax errors detected.
  - `single-program.php` -> No syntax errors detected.
  - `inc/comparison.php` -> No syntax errors detected.
  - `taxonomy.php` -> No syntax errors detected.
- [x] Step 3: Empirical WP-CLI / PHP check for School 1853 (UTC), School 1653 (HOU), School 1611 (TVU):
  - UTC ID 1853: returns exactly `['vua-hoc-vua-lam', 'tu-xa']` (slugs) / `['Vừa học vừa làm', 'Từ xa']` (names). Strictly excludes `chinh-quy` (Program 2013 draft). (PASSED)
  - HOU ID 1653: returns strictly `['tu-xa']` across all 11 published programs. (PASSED)
  - TVU ID 1611: returns strictly `['tu-xa']` across all 6 published programs. (PASSED)
  - Direct School Term Bypass (Step 1 elimination): School 1853 with adversarial terms `chinh-quy` and `van-bang-2` assigned directly to school post does NOT leak these terms. (PASSED)
- [x] Step 4: Empirical check for `single-school.php` and `single-major.php` query logic:
  - Primary query with poisoned `$offered_program_ids` containing draft (2013) and out-of-scope (3001) IDs filters them out completely via `post_status => 'publish'` and `tax_query`. (PASSED)
  - Fallback query (`LTDH_META_SCHOOL_REL` and `LTDH_META_MAJOR_REL`) executes identically and safely when `$offered_program_ids` is empty or yields no posts. (PASSED)
  - College HCCT 1662 (all 4 programs drafted) returns 0 posts on frontend single-school. (PASSED)
  - Major 1677 (CNTT) filters out draft 2013 and chinh-quy 3001, returning strictly published in-scope programs (1854, 2014, 1757). (PASSED)
- [x] Step 5: Empirical check for Campus Isolation:
  - Programs with only `Online` campus -> campus resolves to `'Toàn quốc'`, mode to `'Học online 100%'`. (PASSED)
  - Programs with mixed-case `oNLiNe` -> safely isolated. (PASSED)
  - Programs with physical campus + `Online` -> physical campus retained, `Online` stripped. (PASSED)
  - `single-program.php` defense-in-depth guard defaults empty/'online' to `'Toàn quốc'`. (PASSED)
  - `inc/comparison.php` table generator filters `'online'` and falls back to sanitized campus details. (PASSED)
- [x] Step 6: Adversarial edge cases & stress-testing:
  - School with 0 programs -> returns `[]`. (PASSED)
  - School with only draft programs -> returns `[]`. (PASSED)
  - Invalid school ID (0) -> returns `[]`. (PASSED)
  - Cache set & get verified with 1-hour expiration. (PASSED)
- [ ] Step 7: Update BRIEFING.md and write handoff report with verdict APPROVE.
