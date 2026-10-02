# BRIEFING — 2026-10-01T10:24:00Z

## Mission
Empirically test and challenge frontend labels, pill tabs, breadcrumbs, and taxonomy cleanliness for M3 (Public Label & Facet Challenger).

## 🔒 My Identity
- Archetype: challenger
- Roles: critic, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_2/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M3 Public Label & Facet Verification
- Instance: 2 of 2

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code.
- Write tests/empirical verification scripts and execute them directly.
- Empirical evidence required: no assumptions or relying on worker logs.
- Write report to /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_2/handoff.md.

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T10:13:00Z

## Review Scope
- **Files to review**: Template files, taxonomies, pill tabs on `/he-dao-tao/` and archive templates, breadcrumbs, worker_m3 handoff.
- **Interface contracts**: ORIGINAL_REQUEST.md (## 2026-10-01T09:08:12Z)
- **Review criteria**:
  1. Search template files for any remaining public instances of "Hệ đào tạo" that should be "Hình thức học".
  2. Verify that pill tabs on `/he-dao-tao/` and archive templates show "Hình thức học" and display allowed modes ("Từ xa", "Vừa học vừa làm").
  3. Verify that breadcrumbs evaluate to "Hình thức học".
  4. Verify no taxonomy "Loại tuyển sinh" exists in database or code.

## Key Decisions Made
- Created automated test harness `tests/test-m3-label-facets-empirical.php` with 24 assertions.
- Verdict reached: REQUEST_CHANGES based on 4 confirmed empirical bugs.

## Attack Surface
- **Hypotheses tested**:
  - H1: Are there any template files still rendering "Hệ đào tạo" or "Hệ "? -> CONFIRMED BUG: `taxonomy-training_type.php:379` and `archive-program.php:379` render `Hệ <?php echo esc_html( $type_name ); ?>`; `inc/eligibility.php:306, 479, 482` render "Hệ đào tạo" in AJAX responses.
  - H2: Do pill tabs strictly constrain to allowed modes ("Từ xa", "Vừa học vừa làm")? -> CONFIRMED VULNERABILITY: visiting out-of-scope slug renders that slug as an active pill tab; navigation dropdown injects all terms from `wp_terms`.
  - H3: Do breadcrumbs evaluate to "Hình thức học" in all contexts? -> CONFIRMED BUG: Comparison view hardcodes `/he-dao-tao/tu-xa/` with label "Chương trình" in `inc/core/class-helpers.php:407`.
  - H4: Does taxonomy "Loại tuyển sinh" exist anywhere? -> CONFIRMED CLEAN: 0 definitions in JSON, constants, PHP code, or database audit.
- **Vulnerabilities found**: 4 bugs documented with lines, verbatim code, and repro harness.
- **Untested angles**: None within M3 scope.

## Loaded Skills
- Source: None specified in dispatch
- Core methodology: WordPress theme inspection, AST analysis, regex scanning, empirical test harnesses.

## Artifact Index
- `tests/test-m3-label-facets-empirical.php` — Automated test harness (19 passed, 5 failed)
- `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_2/handoff.md` — Final challenge report & verdict
