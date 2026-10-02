# Progress Log - worker_m3

Last visited: 2026-10-01T10:10:00Z

## Status
Completed Milestone M3 (Taxonomy Label & Clean Routing). All files updated, syntax verified, and tests passing.

## Steps
- [x] Initialized DISPATCH.md and BRIEFING.md
- [x] Investigate and verify each file in scope
- [x] Implement Taxonomy Label standardizations ("Hệ đào tạo" -> "Hình thức học")
  - [x] `inc/acf-import-cpts.json`: Changed title and labels to "Hình thức học", preserved `rewrite_slug: "he-dao-tao"`
  - [x] `inc/config/class-defaults.php`: Changed primary navigation fallback to 'Hình thức học'
  - [x] `inc/core/class-menus.php`: Updated submenu injection check to `in_array( $title, [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo' ], true )`
  - [x] `inc/core/class-helpers.php`: Updated breadcrumbs (lines 432, 449, 459, 469, 472) to 'Hình thức học'
  - [x] `taxonomy-training_type.php`: Updated heading (line 172) and pill tab label (line 250) to 'Hình thức học'
  - [x] `archive-program.php`: Updated heading (line 172) and pill tab label (line 250) to 'Hình thức học'
  - [x] `single-major.php`: Updated card row label (line 489) to 'Hình thức học:'
  - [x] `single-school.php`: Updated card row label (line 520) to 'Hình thức học:'
  - [x] `template-parts/compare/program-table.php`: Updated table row label (line 66) to 'Hình thức học'
  - [x] `template-parts/compare/program-cards.php`: Updated card section label (line 22) to 'Hình thức học'
  - [x] `template-parts/eligibility/wizard.php`: Updated label and comment (lines 74, 76) to 'Hình thức học mong muốn'
  - [x] `inc/core/class-query-filters.php`: Removed 'Hệ ' prefix from badge (line 235)
  - [x] `taxonomy.php`: Updated heading (line 26) and card info (lines 62, 197) to 'Hình thức học'
  - [x] `template-parts/banner.php`: Updated banner titles and subtitles for base /he-dao-tao/, taxonomy, and fallback routes
- [x] Implement Routing & Rewrite Clean-up for `/chuong-trinh/`
  - [x] `inc/core/class-rewrite-rules.php`: Clean 301 redirect from `/chuong-trinh/` to `home_url('/he-dao-tao/')` preserving query parameters
  - [x] `archive-program.php`: Updated form action (line 188), reset buttons (lines 180, 464), and all_url (line 255) to `home_url('/he-dao-tao/')`
- [x] Update Rank Math canonical integration
  - [x] `inc/seo/class-rankmath-integration.php`: Added handling for `is_post_type_archive('program')` and request path `/chuong-trinh/` to return `home_url('/he-dao-tao/')`
- [x] Verify JavaScript form selectors in `assets/js/main.js`
  - [x] Confirmed `main.js` matches `form[action*="/he-dao-tao/"]` and `form[action*="/chuong-trinh/"]`
- [x] Run `php -l` and unit tests
  - [x] All 15 modified PHP files passed `php -l` with 0 errors
  - [x] Executed automated unit tests covering JSON config, defaults, menu logic, redirect regex, and canonical URL resolution
- [x] Update BRIEFING.md
- [ ] Produce handoff.md and report to parent
