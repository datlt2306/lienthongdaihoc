# Progress — challenger_m3_2

Last visited: 2026-10-01T10:23:00Z

## Status
- [x] Initialized DISPATCH.md and BRIEFING.md
- [x] Read ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z) and worker_m3/handoff.md
- [x] Inspected codebase for public instances of "Hệ đào tạo" vs "Hình thức học"
  - Found: `inc/eligibility.php:306, 479, 482`
  - Found: Obsolete prefix `Hệ ` in `taxonomy-training_type.php:379` and `archive-program.php:379`
- [x] Evaluated pill tabs on `/he-dao-tao/` and archive templates
  - Found: Container label is correctly "Hình thức học:" and default view renders allowed modes (`tu-xa`, `vua-hoc-vua-lam`)
  - Found: Out-of-scope leak vulnerability when visiting `/he-dao-tao/van-bang-2/` or `/he-dao-tao/chinh-quy/`, and in `inc/core/class-menus.php` dropdown injection
- [x] Evaluated breadcrumbs engine
  - Found: `ltdh_breadcrumb()` evaluates to "Hình thức học" across program singles, taxonomy archives, and `/he-dao-tao/` routes
  - Found: Hardcoded legacy `/he-dao-tao/tu-xa/` with label "Chương trình" in comparison view (`inc/core/class-helpers.php:407`)
- [x] Verified taxonomy "Loại tuyển sinh" does not exist in DB or code
  - Verified 100% clean: zero definition in CPT JSON, fields JSON, constants, PHP code, or audit report
- [x] Created empirical test suite `tests/test-m3-label-facets-empirical.php` (19 passed, 5 failed)
- [/] Writing handoff.md with REQUEST_CHANGES verdict
- [ ] Send message to orchestrator
