# BRIEFING — 2026-10-01T09:58:00Z

## Mission
Empirically stress-test and verify Milestone M2 campus isolation and data flow changes via WP-CLI / PHP evaluation scripts.

## 🔒 My Identity
- Archetype: challenger (Empirical Challenger)
- Roles: critic, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m2_2
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M2
- Instance: challenger_m2_2

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code
- Write only to own folder (.agents/teamwork/challenger_m2_2/)
- Must empirically test and verify all assertions using WP-CLI or PHP execution

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T09:47:00Z

## Review Scope
- **Files to review**:
  - `inc/core/class-helpers.php` (`ltdh_get_program_learning_details`, `ltdh_get_school_training_types`, `ltdh_get_school_unique_majors_count`)
  - `inc/comparison.php` (`ltdh_compare_resolve_program`, `ltdh_compare_get_items`)
  - `taxonomy.php` (line 220 syntax fix and rendering)
  - `single-program.php` (line 266-274 defense-in-depth campus guard)
  - `template-parts/compare/program-table.php` and `program-cards.php`
- **Interface contracts**: ORIGINAL_REQUEST.md, worker_m2/handoff.md
- **Review criteria**:
  1. `ltdh_get_program_learning_details()` on programs with `campus = online` (e.g. 1800, 1795, 1791, 1785) never returns 'Online' as campus.
  2. `inc/comparison.php` comparison output never displays 'Online' under Cơ sở / Trạm đào tạo (`campus_info`).
  3. `taxonomy.php:220` has no PHP syntax or parse error.

## Attack Surface
- **Hypotheses tested**:
  - H1: Programs with `campus = online` (IDs 1800, 1795, 1791, 1785) return 'Toàn quốc' and learning mode 'Học online 100%' — CONFIRMED (Passed).
  - H2: Programs with mixed campuses ('Online', 'Hà Nội', 'TP.HCM') strip 'Online' and return physical stations — CONFIRMED (Passed).
  - H3: Programs with unconventional casing/whitespace ('oNLiNe', ' ONLINE ') are stripped case-insensitively — CONFIRMED (Passed).
  - H4: Non-tu-xa programs with campus = 'Online' resolve to school region or 'Toàn quốc', mode 'Học tập trung / Cuối tuần' — CONFIRMED (Passed).
  - H5: Programs with zero campus terms default safely to 'Toàn quốc' without notice — CONFIRMED (Passed).
  - H6: Comparison table helper (`inc/comparison.php`) and templates never output 'Online' under "Cơ sở / Trạm đào tạo" (`campus_info`) — CONFIRMED (Passed).
  - H7: `taxonomy.php:220` has no syntax or AST parse errors and renders clean anchor tag — CONFIRMED (Passed).
  - H8: Step 1 elimination in `ltdh_get_school_training_types()` strictly excludes static school terms (e.g. 'chinh-quy', 'van-bang-2') and rolls up exclusively from published in-scope programs — CONFIRMED (Passed).
  - H9: `ltdh_get_school_unique_majors_count()` accurately counts unique majors without limits or draft leaks — CONFIRMED (Passed).
- **Vulnerabilities found**: None. All edge cases handled cleanly.
- **Untested angles**: None. 46 test assertions executed across 6 test suites with 100% pass rate.

## Loaded Skills
- **Source**: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md
- **Core methodology**: WordPress development mastery - themes, plugins, taxonomy, WP_Query, WP-CLI testing

## Key Decisions Made
- Constructed standalone empirical test harness `tests/test-m2-empirical.php` executing 46 automated assertions covering baseline and adversarial inputs.
- Validated all 6 suites with zero failures and zero PHP notices.

## Artifact Index
- `BRIEFING.md` — Persistent situational awareness
- `progress.md` — Liveness heartbeat and progress log
- `DISPATCH.md` — Message dispatch log
- `tests/test-m2-empirical.php` — Comprehensive empirical test harness
- `handoff.md` — Final 5-component verification report and verdict
