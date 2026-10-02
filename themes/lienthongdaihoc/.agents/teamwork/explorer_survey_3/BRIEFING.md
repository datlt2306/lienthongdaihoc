# BRIEFING — 2026-10-01T09:15:00Z

## Mission
Survey & audit of Information Architecture (IA), URL structures, rewrite rules, permalinks, SEO titles/H1/descriptions, Breadcrumbs, Canonicals, Schema JSON-LD, and 301 Redirect strategy for `lienthongdaihoc.com`, strictly aligned with core business: "Liên thông Đại học".

## 🔒 My Identity
- Archetype: explorer
- Roles: SEO, URLs, Canonicals, Breadcrumbs & Redirects Auditor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3
- Original parent: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e
- Milestone: IA, Terminology & Templates Restructure Audit (Survey 3: SEO & URLs)

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify original theme source code
- Audit and assessment only
- Write only within working directory (.agents/teamwork/explorer_survey_3/)
- Maintain progress.md with timestamps

## Current Parent
- Conversation ID: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e
- Updated: 2026-10-01T08:52:36Z

## Investigation State
- **Explored paths**:
  - `inc/post-types.php`, `inc/acf-import-cpts.json`, `inc/config/constants.php`, `inc/config/class-defaults.php`
  - `inc/core/class-rewrite-rules.php`, `inc/core/class-helpers.php`, `inc/core/class-menus.php`, `inc/core/class-query-filters.php`
  - `inc/seo/class-rankmath-integration.php`, `inc/comparison.php`, `inc/cli-commands.php`
  - All template files: `front-page.php`, `taxonomy-training_type.php`, `archive-program.php`, `archive-school.php`, `archive-major.php`, `single-program.php`, `single-school.php`, `single-major.php`, `single-guide.php`, `header.php`, `footer.php`, `template-parts/banner.php`, `page-faq.php`, `page-register.php`
- **Key findings**:
  1. URL Preservation: Public URLs (`/truong-doi-tac/`, `/nganh-hoc/`, `/he-dao-tao/`, `/nganh-{slug}/`, `/{school-slug}/`, `/{program-slug}/`) can be 100% preserved while updating labels and metadata.
  2. Out-of-scope Term & 301 Redirect: `van-bang-2` seeded in taxonomy `training_type` and referenced on homepage H1 and footer. Needs complete phasing out and 301 redirect: `/he-dao-tao/van-bang-2/` -> `/he-dao-tao/tu-xa/`.
  3. Canonical Redirect Loop: CPT `program` archive `/chuong-trinh/` 301-redirects to `/he-dao-tao/tu-xa/`, but filter form on `archive-program.php` still submits to `/chuong-trinh/`, and Rank Math generates canonical for `/chuong-trinh/`.
  4. SEO Titles & H1 Standardization: Program titles currently lack "Liên thông" (`Học %s (%s) - %s`), School and Major single templates lack "Liên thông [Trường]" and "Liên thông [Ngành]". Dual H1 tags found on `taxonomy-training_type.php` and `archive-program.php`.
  5. Breadcrumbs & Microdata: `ltdh_breadcrumb()` deliberately skips Rank Math breadcrumbs on `/he-dao-tao/*` and outputs crude HTML without Schema Microdata. Duplicate breadcrumbs with 404 dead link `/huong-dan/` on `single-guide.php`.
  6. Schema JSON-LD: Rank Math `Course` schema lacks `hasCourseInstance` and `offers`.
- **Unexplored areas**: None. Comprehensive 360-degree audit completed.

## Key Decisions Made
- Constructed complete 9-column Current -> Target Mapping Table and full 301 Redirect Strategy in `handoff.md`.
- Recommended minimal safe intervention plan without breaking any publicly indexed URLs or CSS layouts.

## Artifact Index
- DISPATCH.md — Task assignment and instructions
- BRIEFING.md — Situational awareness
- progress.md — Liveness heartbeat and task tracker
- handoff.md — Comprehensive SEO, URLs, Canonicals, Breadcrumbs & Redirects Audit Report
