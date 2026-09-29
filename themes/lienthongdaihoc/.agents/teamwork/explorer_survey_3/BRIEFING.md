# BRIEFING — 2026-09-25T05:14:00Z

## Mission
Frontend, SEO & Schema Survey for WordPress theme Liên Thông Đại Học: audit asset management, SEO on-page, Schema markup, and client-side frontend integrity.

## 🔒 My Identity
- Archetype: explorer
- Roles: Frontend, SEO & Schema Surveyor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3
- Original parent: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Milestone: Preview Survey (Survey 3)

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify original theme source code
- Audit and assessment only
- Write only within working directory (.agents/teamwork/explorer_survey_3/)
- Maintain progress.md with timestamps

## Current Parent
- Conversation ID: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Updated: not yet

## Investigation State
- **Explored paths**:
  - `functions.php`, `style.css`, `package.json`, `postcss.config.js`
  - `inc/core/class-theme-setup.php`, `inc/core/class-helpers.php`, `inc/core/class-menus.php`, `inc/core/class-rewrite-rules.php`
  - `inc/config/constants.php`, `inc/config/class-defaults.php`, `inc/post-types.php`, `inc/acf-import-cpts.json`
  - `inc/seo/class-rankmath-integration.php`
  - `inc/comparison.php`, `inc/eligibility.php`
  - `assets/css/main.min.css`, `assets/css/input.css`, `assets/css/eligibility.css`, `assets/css/swiper-bundle.min.css`
  - `assets/js/main.js`, `assets/js/compare.js`, `assets/js/eligibility.js`, `assets/images/*`
  - All template files: `front-page.php`, `index.php`, `single.php`, `single-program.php`, `single-school.php`, `single-major.php`, `single-guide.php`, `archive-program.php`, `archive-school.php`, `archive-major.php`, `taxonomy-training_type.php`, `taxonomy.php`, `page.php`, `page-about.php`, `page-contact.php`, `page-compare-program.php`, `page-eligible.php`, `page-faq.php`, `page-register.php`, `404.php`, `header.php`, `footer.php`, `template-parts/**`
- **Key findings**:
  1. Asset Management: 7 enqueued scripts/styles with version mismatch (`LTDH_VERSION` 2.0.0 vs `style.css` 1.0.0); zero async/defer; 13+ inline `<script>`/`<style>` blocks; 250+ lines duplicate CSS across `style.css` and `input.css`; hardcoded Google Fonts link; CSS loading order puts `style.css` before Tailwind `main.min.css`.
  2. SEO On-Page: Missing `<h1>` on `front-page.php`; Dual `<h1>` tags on `single-major.php`, `page-compare-program.php`, `taxonomy.php`; skipped heading levels (`h2` -> `h4`); missing and generic `alt` attributes; hardcoded localhost URL in `front-page.php:896`; broken breadcrumb links (`/truong-hoc/` vs `/truong-doi-tac/`).
  3. Schema.org: 100% locked to Rank Math hooks; missing `EducationalOrganization` and `WebSite` schemas; `Course` schema lacks `hasCourseInstance`, `offers`, and credentials; `FAQPage` schema missing on dedicated `page-faq.php` and `page-compare-program.php`; `ltdh_breadcrumb()` lacks BreadcrumbList microdata and bypasses Rank Math on `/he-dao-tao/`.
  4. Frontend/JS & Responsive: Broken media query typo in `footer.php:184` (`@media (max-w: 767px)`) breaking mobile bottom padding; `compare.js` fails on AJAX-filtered cards due to lack of event delegation; `eligibility.js` unhandled null dereferences and event listener accumulation; corrupted 29-byte HTML 404 `banner-default.jpg`; 28MB unreferenced design mockup images.
- **Unexplored areas**: None. Full 360-degree survey of asset, SEO, Schema, and JS integrity complete.

## Key Decisions Made
- Structured the handoff report into the required 5-component format (Observation, Logic Chain, Caveats, Conclusion, Verification Method) with comprehensive inventory tables and ready-to-apply remediation code snippets.

## Artifact Index
- DISPATCH.md — Dispatch log
- BRIEFING.md — Situational awareness
- progress.md — Liveness heartbeat and task tracker
- handoff.md — Comprehensive handoff report
