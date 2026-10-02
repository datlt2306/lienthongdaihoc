# BRIEFING — 2026-10-01T09:50:00Z

## Mission
Review M2 Data Flow & Query implementation: verify CPT preservation, training types rollup, program queries in single-school.php and single-major.php, and php syntax.

## 🔒 My Identity
- Archetype: reviewer_and_adversarial_critic
- Roles: reviewer, critic
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m2_1/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M2 Data Flow & Query Review
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code
- Exactly 3 core CPTs preserved, 0 new CPTs registered or created
- Check for integrity violations (hardcoded test results, facade logic, bypassed checks)

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: not yet

## Review Scope
- **Files to review**: `inc/core/class-helpers.php`, `single-school.php`, `single-major.php`, `single-program.php`, `inc/comparison.php`, `taxonomy.php`
- **Interface contracts**: PROJECT.md, worker_m2/handoff.md, ORIGINAL_REQUEST.md
- **Review criteria**: correctness, integrity, scope conformance, syntax validity

## Review Checklist
- **Items reviewed**:
  1. CPT registration in `inc/post-types.php` and `acf-import-cpts.json` (3 CPTs confirmed, 0 new CPTs).
  2. `ltdh_get_school_training_types()` in `inc/core/class-helpers.php` (Step 1 eliminated, strict in-scope program rollup with cache).
  3. `single-school.php` and `single-major.php` program queries (enforce `post_status => publish`, `tax_query => ['tu-xa', 'vua-hoc-vua-lam']`, `posts_per_page => -1`, fallback query).
  4. Campus `online` isolation in `ltdh_get_program_learning_details()`, `single-program.php`, and `inc/comparison.php`.
  5. Syntax fix at `taxonomy.php:220`.
- **Verdict**: APPROVE
- **Unverified claims**: None

## Attack Surface
- **Hypotheses tested**:
  - Direct school terms leakage: Tested and verified eliminated.
  - Draft / private post leakage in single views: Tested and verified blocked by `post_status => publish`.
  - Non-in-scope training types leakage: Tested and verified blocked by `tax_query` and inner term checks.
  - "Online" rendering as physical campus: Tested and verified sanitized with fallback to "Toàn quốc" or regional name.
  - Empty programs fallback: Verified fallback query activates properly.
- **Vulnerabilities found**: None. 0 integrity violations.
- **Untested angles**: Runtime database execution in WordPress environment (covered by worker_m2 / auditor).

## Key Decisions Made
- Confirmed full compliance with M2 specifications; issuing APPROVE verdict.

## Artifact Index
- handoff.md — Complete review and adversarial report
- progress.md — Liveness heartbeat
- DISPATCH.md — Task assignment log
