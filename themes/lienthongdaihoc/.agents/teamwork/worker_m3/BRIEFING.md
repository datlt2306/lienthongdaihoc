# BRIEFING — 2026-10-01T10:00:00Z

## Mission
Standardize frontend display of training_type taxonomy to "Hình thức học", preserve URL slugs, and clean up /chuong-trinh/ routing & canonicals.

## 🔒 My Identity
- Archetype: implementer
- Roles: implementer, qa, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m3
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M3 (Taxonomy Label & Clean Routing)

## 🔒 Key Constraints
- Standardize frontend display of training_type taxonomy from "Hệ đào tạo" to "Hình thức học" across all theme files.
- STRICTLY PRESERVE all URL slugs: /he-dao-tao/, /he-dao-tao/tu-xa/, /he-dao-tao/vua-hoc-vua-lam/.
- Strictly DO NOT create any redundant taxonomy "Loại tuyển sinh" or filter "Liên thông".
- Clean up /chuong-trinh/ routing: 301 redirect to home_url('/he-dao-tao/') preserving $_GET.
- Update archive-program.php form actions, all_url, reset links from home_url('/chuong-trinh/') to home_url('/he-dao-tao/').
- Ensure Rank Math canonical for program archive points cleanly to home_url('/he-dao-tao/') to eliminate redirect loops.
- Assets/js/main.js: ensure form selectors match form[action*="/he-dao-tao/"] as well as form[action*="/chuong-trinh/"].
- Run php -l across all modified files and verify.

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: not yet

## Task Summary
- **What to build**: Standardize "Hệ đào tạo" -> "Hình thức học", preserve slugs, clean /chuong-trinh/ 301 redirect, update Rank Math canonical, update form actions & JS selectors.
- **Success criteria**: All occurrences in scope updated, zero PHP syntax errors, preserve slugs, eliminate canonical redirect loops.
- **Interface contracts**: Routing Contract (M3) in orchestrator_5/PROJECT.md.
- **Code layout**: Specified in orchestrator_5/PROJECT.md and DISPATCH.md.

## Key Decisions Made
- Standardized taxonomy_training_type title and all labels to "Hình thức học" in `inc/acf-import-cpts.json` while strictly retaining `rewrite_slug: "he-dao-tao"`.
- Updated navigation fallback and dynamic menu matching to accept ['hình thức học', 'hệ đào tạo', 'hình thức đào tạo'] for backward-compatible submenu injection.
- Updated breadcrumbs across program singles, taxonomy archives, and virtual training type routes to "Hình thức học".
- Standardized heading, pill filter, and empty-state reset links in `archive-program.php` and `taxonomy-training_type.php`.
- Replaced 301 redirect destination in `inc/core/class-rewrite-rules.php` from `/he-dao-tao/tu-xa/` to `/he-dao-tao/` preserving `$_GET`.
- Updated `inc/seo/class-rankmath-integration.php` to set canonical URL to `home_url('/he-dao-tao/')` on program archive to eliminate canonical redirect loops.
- Cleaned badge prefix in `inc/core/class-query-filters.php` from "Hệ " to "".

## Artifact Index
- .agents/teamwork/worker_m3/DISPATCH.md
- .agents/teamwork/worker_m3/BRIEFING.md
- .agents/teamwork/worker_m3/progress.md
- .agents/teamwork/worker_m3/handoff.md

## Change Tracker
- **Files modified**:
  * `inc/acf-import-cpts.json`: Updated training_type labels to "Hình thức học", preserved slug `he-dao-tao`
  * `inc/config/class-defaults.php`: Updated navigation item label to 'Hình thức học'
  * `inc/core/class-menus.php`: Expanded title check in `ltdh_dynamic_menu_submenu_injection`
  * `inc/core/class-helpers.php`: Changed breadcrumb labels at lines 432, 449, 459, 469, 472 to 'Hình thức học'
  * `taxonomy-training_type.php`: Changed heading and filter label to 'Hình thức học'
  * `archive-program.php`: Updated labels to 'Hình thức học', changed form action, reset, and all_url from `/chuong-trinh/` to `/he-dao-tao/`
  * `single-major.php`: Changed card row label to 'Hình thức học:'
  * `single-school.php`: Changed card row label to 'Hình thức học:'
  * `template-parts/compare/program-table.php`: Changed row label to 'Hình thức học'
  * `template-parts/compare/program-cards.php`: Changed section key label to 'Hình thức học'
  * `template-parts/eligibility/wizard.php`: Changed label to 'Hình thức học mong muốn'
  * `inc/core/class-query-filters.php`: Removed 'Hệ ' prefix from badge
  * `taxonomy.php`: Updated heading and program info labels to 'Hình thức học'
  * `template-parts/banner.php`: Updated banner titles and subtitles for base, taxonomy, and fallback routes
  * `inc/core/class-rewrite-rules.php`: Changed 301 redirect target for `/chuong-trinh/` to `/he-dao-tao/` preserving query args
  * `inc/seo/class-rankmath-integration.php`: Pointed canonical for program archive and `/chuong-trinh/` to `/he-dao-tao/`
  * `assets/js/main.js`: Verified selectors match `form[action*="/he-dao-tao/"]` and `form[action*="/chuong-trinh/"]`
- **Build status**: All PHP files pass `php -l` with 0 errors. All unit tests pass.
- **Pending issues**: None

## Quality Status
- **Build/test result**: Pass (15 PHP files syntax-checked, 6 automated test assertions pass)
- **Lint status**: 0 syntax/lint violations
- **Tests added/modified**: Automated assertions for JSON structure, menu match logic, defaults fallback, redirect regex, and canonical URL resolution

## Loaded Skills
- **Source**: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md
- **Local copy**: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m3/skills/php-wordpress.md
- **Core methodology**: WordPress theme and plugin development standards, hooks, template hierarchy, sanitization, routing.
