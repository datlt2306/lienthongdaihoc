# BRIEFING — 2026-10-01T10:19:00Z

## Mission
Objective and adversarial review of Milestone 3: Taxonomy Display Standardization (changing label from "Hệ đào tạo" to "Hình thức học", preserving URL slugs `/he-dao-tao/`, preventing redundant "Loại tuyển sinh" / "Liên thông" taxonomies/filters, syntax and integrity verification).

## 🔒 My Identity
- Archetype: reviewer, critic
- Roles: reviewer, critic
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m3_1/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: Milestone 3 (Taxonomy Display Standardization)
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code
- Active integrity check: detect hardcoded outputs, fake facades, skipped logic, fabricated verification
- Strict preservation of URL slugs `/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`
- Ensure NO redundant taxonomy "Loại tuyển sinh" or redundant filter "Liên thông" was introduced
- Run `php -l` on modified files
- Deliver handoff.md with 5 components and clear APPROVE / REQUEST_CHANGES verdict

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T10:19:00Z

## Review Scope
- **Files to review**:
  - `inc/acf-import-cpts.json`
  - `inc/config/class-defaults.php`
  - `inc/core/class-menus.php`
  - `inc/core/class-helpers.php`
  - `taxonomy-training_type.php`
  - `archive-program.php`
  - `single-major.php`
  - `single-school.php`
  - `template-parts/compare/program-table.php`
  - `template-parts/compare/program-cards.php`
  - `template-parts/eligibility/wizard.php`
  - `taxonomy.php`
  - `template-parts/banner.php`
  - Plus routing files: `inc/core/class-rewrite-rules.php`, `inc/seo/class-rankmath-integration.php`, `inc/core/class-query-filters.php`
- **Interface contracts**:
  - `.agents/teamwork/ORIGINAL_REQUEST.md` (## 2026-10-01T09:08:12Z)
  - `.agents/teamwork/orchestrator_5/PROJECT.md`
  - `.agents/teamwork/worker_m3/handoff.md`

## Key Decisions Made
- Confirmed that taxonomy label "Hệ đào tạo" is 100% replaced by "Hình thức học" across all target public templates, breadcrumbs, banners, cards, tables, wizard, and ACF definitions.
- Confirmed that URL slugs `/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/` are 100% preserved.
- Confirmed that `/chuong-trinh/` 301 route cleanly redirects to `/he-dao-tao/` preserving query args, resolving canonical loop.
- Confirmed no redundant taxonomy "Loại tuyển sinh" or filter "Liên thông" was created.
- Verified 17/17 modified PHP files pass `php -l`.
- Executed both empirical (38/38 assertions passed) and forensic (82/82 assertions passed) suites.
- Verdict: APPROVE.

## Artifact Index
- `DISPATCH.md` — Inbound instructions from orchestrator
- `BRIEFING.md` — Situational awareness and working memory
- `progress.md` — Heartbeat and execution progress
- `handoff.md` — Final review report and verdict

## Review Checklist
- **Items reviewed**:
  - `inc/acf-import-cpts.json`: title, label, singular_label, labels object all "Hình thức học"; rewrite_slug "he-dao-tao" intact.
  - `inc/config/class-defaults.php`: label "Hình thức học", URL "/he-dao-tao/".
  - `inc/core/class-menus.php`: case-insensitive check matches "hình thức học", "hệ đào tạo", "hình thức đào tạo".
  - `inc/core/class-helpers.php`: all 5 breadcrumb occurrences updated to "Hình thức học".
  - `taxonomy-training_type.php`: title, pill prefix, filter action updated; no redundant filters.
  - `archive-program.php`: title, pill prefix, filter form action, reset links updated to `/he-dao-tao/`.
  - `single-major.php`: "Hình thức học:" row header.
  - `single-school.php`: "Hình thức học:" row header.
  - `template-parts/compare/program-table.php`: table row label "Hình thức học".
  - `template-parts/compare/program-cards.php`: card section key "Hình thức học".
  - `template-parts/eligibility/wizard.php`: label "Hình thức học mong muốn".
  - `taxonomy.php`: H2 and card metadata "Hình thức học".
  - `template-parts/banner.php`: banner titles and subtitles updated; legacy mentions of "văn bằng 2" purged.
  - `inc/core/class-rewrite-rules.php`: clean 301 redirect `/chuong-trinh/` -> `/he-dao-tao/`.
  - `inc/seo/class-rankmath-integration.php`: canonical filter resolves canonical loop.
  - `inc/core/class-query-filters.php`: badge prefix "Hệ " stripped.
- **Verdict**: APPROVE
- **Unverified claims**: None. All claims independently verified with AST, regex, tokenizer, and runtime simulation.

## Attack Surface
- **Hypotheses tested**:
  - H1: UTF-8 casing of Vietnamese characters in menu matching -> Verified: `mb_strtolower('Hình thức học', 'UTF-8') === 'hình thức học'`.
  - H2: Preserving query parameters across `/chuong-trinh/` -> Verified: single & multi-arg, array params, Vietnamese search terms, UTM parameters preserved.
  - H3: Subpath collision on `/chuong-trinh/` -> Verified: regex `#^/chuong-trinh/?$#i` anchors strictly prevent matching `/chuong-trinh/cntt/`.
  - H4: Canonical redirect loop -> Verified: canonical matches HTTP 200 catalog destination `/he-dao-tao/`.
  - H5: Redundant taxonomy injection -> Verified: 0 new taxonomies registered.
- **Vulnerabilities found**:
  - Minor edge case (non-blocking): If an admin adds an emoji or non-standard prefix in WP Admin menu title (e.g., "🎓 Hình thức học"), string equality in `ltdh_dynamic_menu_submenu_injection` would fail to match.
  - Non-blocking cleanup note: Legacy internal strings in `inc/eligibility.php` (`WP_Error('invalid_training', 'Hệ đào tạo không hợp lệ.')` and `verification_items`) still mention "Hệ đào tạo", but these are internal AJAX validation messages not part of public M3 templates.
- **Untested angles**: Full production browser render with live database (outside CLI environment, though simulated AST & runtime harness achieved 120/120 passes).
