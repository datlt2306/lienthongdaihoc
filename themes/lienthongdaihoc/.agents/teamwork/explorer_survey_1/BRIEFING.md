# BRIEFING — 2026-10-01T09:00:00Z

## Mission
Comprehensive audit & mapping of Information Architecture (IA), Data Models, CPTs, Taxonomies, ACF Fields, Relationships, and Terminology across lienthongdaihoc.com to enforce pure "Liên thông Đại học" scope and eliminate prohibited/conflicting concepts.

## 🔒 My Identity
- Archetype: explorer
- Roles: Codebase & PHP Inventory Surveyor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1
- Original parent: 51dbca0c-0bdf-4e7a-a663-8f16b15aa54f
- Milestone: Phase 1 Deep Exploration & Inventory
- Sub-role: IA & Data Model Explorer (2026-10-01)
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/
- Active parent: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e
- Milestone: IA & Data Model Restructure Audit

## 🔒 Key Constraints
- Read-only investigation — do NOT modify any original theme source code
- Write only within /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/
- Maintain progress.md with timestamps
- Enumerate 100% of PHP files in the theme with line counts, byte sizes, purpose, syntax verification
- Read-only investigation: absolutely no code modifications to theme files
- Pure focus on "Liên thông Đại học": identify and eliminate "Văn bằng 2", "VB2", "Đại học mới", standalone "chính quy", and standardize "Hình thức học" vs "Hệ đào tạo"
- Protect public URLs and SEO: propose 301 redirects if any slug changes are mandatory, prioritize minimal safe interventions

## Current Parent
- Conversation ID: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e
- Updated: 2026-10-01T08:52:36Z

## Investigation State
- **Explored paths**:
  - `ORIGINAL_REQUEST.md` (section 2026-10-01T08:50:13Z)
  - `inc/post-types.php`, `inc/acf-import-cpts.json`, `inc/acf-fields.php`, `inc/acf-import-fields.json`
  - `inc/relationship-hooks.php`, `inc/config/constants.php`, `inc/config/class-defaults.php`
  - `inc/core/class-rewrite-rules.php`, `inc/core/class-menus.php`, `inc/core/class-helpers.php`, `inc/core/class-query-filters.php`
  - `inc/seo/class-rankmath-integration.php`, `inc/eligibility-rules.php`, `inc/eligibility.php`, `inc/cli-commands.php`
  - Templates: `front-page.php`, `single-program.php`, `single-school.php`, `single-major.php`, `taxonomy-training_type.php`, `archive-program.php`, `template-parts/eligibility/wizard.php`, `template-parts/banner.php`, `footer.php`, `taxonomy.php`
- **Key findings**:
  1. Entity model (`school` ⟷ `major` ⟷ `program` ⟷ `training_type` ⟷ `campus`) perfectly supports target IA without DB table changes.
  2. "Văn bằng 2" / "VB2": Found 49 occurrences across 15 files. It is erroneously treated as a distinct training type alongside Từ xa and Chính quy. Must be deprecated and re-routed.
  3. "Chính quy": Erroneously allowed from THPT in eligibility rules. Must be clarified strictly as "Liên thông Chính quy" (for College graduates).
  4. "Hệ đào tạo": Found 91 occurrences. Can be safely transformed to "Hình thức học" at presentation/UI/label layer while keeping slug `training_type` and URL `/he-dao-tao/` intact to guarantee 0 broken URLs.
  5. 301 Redirect strategy: `/he-dao-tao/van-bang-2/` → 301 → `/he-dao-tao/tu-xa/`.
  6. Produced comprehensive 9-column Current -> Target mapping table in `handoff.md`.
- **Unexplored areas**: None. Codebase audit and mapping complete.

## Key Decisions Made
- Zero DB schema breaking changes: Retain CPT and taxonomy slugs (`program`, `school`, `major`, `training_type`, `campus`).
- Re-label at presentation layer: UI, labels, headings, breadcrumbs, H1, meta SEO.
- Formulated Minimal Safe Intervention Strategy documented in `handoff.md`.

## Artifact Index
- [BRIEFING.md](BRIEFING.md) — Persistent working memory
- [DISPATCH.md](DISPATCH.md) — Dispatch log
- [progress.md](progress.md) — Liveness heartbeat and milestone tracker
- [handoff.md](handoff.md) — Final comprehensive handoff report with 9-column mapping table
