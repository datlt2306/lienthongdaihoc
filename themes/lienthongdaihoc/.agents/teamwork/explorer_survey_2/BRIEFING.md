# BRIEFING — 2026-10-01T08:55:00Z

## Mission
Survey & audit all theme templates, navigation menus, filter engines, and UI components to align with the core business model of Liên Thông Đại Học, identify discrepancies, produce Current -> Target mapping table, and propose minimal safe intervention plan without breaking styling or SEO.

## 🔒 My Identity
- Archetype: explorer
- Roles: Security & DB Query Surveyor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2
- Original parent: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Milestone: Security & DB Query Survey
- Appended Role (2026-10-01): Templates & UI Explorer
- Parent ID: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e
- Milestone: IA & Templates Survey (lienthongdaihoc.com)

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify original source files in the theme.
- Write only within working directory (.agents/teamwork/explorer_survey_2/).
- Produce an exhaustive, evidence-backed handoff report (handoff.md) with exact files, line numbers, risk analysis, and sample fix snippets.
- Appended Constraints (2026-10-01):
  * Focus strictly on Liên Thông Đại Học. Exclude VB2, ĐH mới, ĐH chính quy độc lập.
  * Target model: University -> Admission Info -> Study Mode (Chính quy, VLVH, Từ xa) -> Major -> Specific Liên thông admission offering.
  * Preserve existing design/styling and avoid changing public slugs/URLs without 301 plan.
  * Produce Current -> Target schema mapping table.

## Current Parent
- Conversation ID: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e
- Updated: 2026-10-01T08:55:00Z

## Investigation State
- **Explored paths**:
  * Navigation menus: `inc/config/class-defaults.php`, `inc/core/class-menus.php`, `header.php`, `footer.php`.
  * Templates: `taxonomy-training_type.php`, `archive-program.php`, `single-program.php`, `archive-school.php`, `single-school.php`, `archive-major.php`, `single-major.php`, `front-page.php`, `page-about.php`, `page-faq.php`, `page-compare-program.php`.
  * Filter engines & helpers: `inc/core/class-query-filters.php`, `inc/core/class-helpers.php`, `inc/search-engine.php`, `inc/core/class-rewrite-rules.php`, `inc/post-types.php`, `inc/acf-import-cpts.json`.
  * Eligibility tools: `template-parts/eligibility/wizard.php`, `results.php`, `inc/eligibility.php`, `inc/eligibility-rules.php`.
  * SEO & Rank Math integration: `inc/seo/class-rankmath-integration.php`.
- **Key findings**:
  1. Primary navigation, footer and defaults mix up terminology ("Hệ đào tạo" vs "Hình thức học", "Chuyên ngành" vs "Ngành học", "Trường đối tác" vs "Trường đại học", "Tin tức" vs "Kiến thức liên thông").
  2. Footer contains 4 dead `#` links and out-of-scope terms like "Cao đẳng online / VB2".
  3. `single-program.php` completely lacks a "Bằng cấp" (Degree value & Thông tư 27/2019/TT-BGDĐT) section and has a hardcoded alert referencing "hệ Chính quy" for all programs.
  4. Redundant filtering: `training_type` taxonomy contains `lien-thong` and `van-bang-2` as sibling terms to `tu-xa` and `chinh-quy`, when 100% of programs are Liên thông.
  5. URL rewrite rule `class-rewrite-rules.php:247-254` forces a 301 redirect from `/chuong-trinh/` to `/he-dao-tao/tu-xa/`, restricting access to other study modes.
  6. Homepage H1 contains "Văn Bằng 2 & Đại Học Từ Xa", which violates the single-purpose Liên thông domain rule.
- **Unexplored areas**: None. Full static inspection of templates, navigation, and filters completed.

## Key Decisions Made
- Formulated full 17-row Current -> Target mapping table according to required schema.
- Designed 4-phase minimal safe intervention plan preserving all public slugs/URLs, CSS classes, and existing DOM structures.
- Documented findings in comprehensive handoff.md.

## Artifact Index
- DISPATCH.md — incoming dispatch records
- BRIEFING.md — persistent working memory
- progress.md — liveness heartbeat
- handoff.md — final comprehensive survey report (A-H aligned)

