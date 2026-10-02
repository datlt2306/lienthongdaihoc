# BRIEFING — 2026-10-01T10:19:00Z

## Mission
Empirically test and stress-test M3 Routing & Canonical changes for `/chuong-trinh/` redirect to `/he-dao-tao/`, query arg preservation, Rank Math canonical hook, and loop-free HTTP 200 on `/he-dao-tao/`.

## 🔒 My Identity
- Archetype: empirical-challenger
- Roles: critic, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_1
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M3 Routing & Canonical Challenger
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code directly (or if changes needed, request changes in verdict)
- Must empirically test via WP-CLI / PHP scripts; verify every assertion independently
- Report verdict: APPROVE or REQUEST_CHANGES in handoff.md

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T10:19:00Z

## Review Scope
- **Files to review**:
  - `inc/core/class-rewrite-rules.php`
  - `inc/seo/class-rankmath-integration.php`
  - `archive-program.php`
  - `taxonomy-training_type.php`
  - `tests/test-m3-empirical.php`
  - `tests/test-m3-forensic.php`
  - Worker M3 handoff: `.agents/teamwork/worker_m3/handoff.md`
- **Review criteria**:
  1. `/chuong-trinh/` redirects 301 to `/he-dao-tao/` (NOT `/he-dao-tao/tu-xa/`).
  2. `/chuong-trinh/?truong=utc&nganh=cntt` preserves query args in redirect.
  3. Rank Math canonical URL filter for program archive produces `home_url('/he-dao-tao/')`.
  4. `/he-dao-tao/` returns HTTP 200 without redirect loop.

## Key Decisions Made
- Built comprehensive standalone empirical harness `tests/test-m3-empirical.php` with 38 assertions across standard and adversarial test cases.
- Executed forensic test `tests/test-m3-forensic.php` (82 assertions) and empirical test `tests/test-m3-empirical.php` (38 assertions).
- Verified zero syntax errors across all 15 modified files via `php -l`.
- Verdict: APPROVE.

## Artifact Index
- `.agents/teamwork/challenger_m3_1/BRIEFING.md` — Agent working memory
- `.agents/teamwork/challenger_m3_1/progress.md` — Progress tracking & heartbeat
- `.agents/teamwork/challenger_m3_1/DISPATCH.md` — Dispatch log
- `.agents/teamwork/challenger_m3_1/handoff.md` — Final challenge report & verdict
- `tests/test-m3-empirical.php` — Empirical behavioral test harness (38 tests)

## Attack Surface
- **Hypotheses tested**:
  - H1: `/chuong-trinh/` 301 target is `/he-dao-tao/` and never `/he-dao-tao/tu-xa/` -> CONFIRMED (Pass)
  - H2: Non-trailing slash `/chuong-trinh` and uppercase `/CHUONG-TRINH/` redirect correctly -> CONFIRMED (Pass)
  - H3: Subpath `/chuong-trinh/cntt/` is NOT falsely redirected by base rule -> CONFIRMED (Pass)
  - H4: Query parameters (single, multiple, arrays, Vietnamese encoded, UTM) are preserved -> CONFIRMED (Pass)
  - H5: Rank Math canonical URL on program archive and legacy URI produces `/he-dao-tao/` -> CONFIRMED (Pass)
  - H6: Singular program canonicals remain flat (`/slug/`) -> CONFIRMED (Pass)
  - H7: `/he-dao-tao/` terminates at HTTP 200 with catalog template, no redirect loop -> CONFIRMED (Pass)
  - H8: Term and paginated archives (`/he-dao-tao/tu-xa/`, `/he-dao-tao/page/2/`) return HTTP 200 -> CONFIRMED (Pass)
- **Vulnerabilities found**: 0 vulnerabilities or regressions found.
- **Untested angles**: None within M3 routing/canonical scope.

## Loaded Skills
- **Source**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md`
- **Local copy**: `.agents/skills/php-wordpress/SKILL.md`
- **Core methodology**: WordPress development mastery - hooks, rewrites, template redirects, CLI validation
