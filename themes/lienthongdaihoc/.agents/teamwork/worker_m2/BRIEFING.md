# BRIEFING — 2026-10-29T15:26:00Z

## Mission
Complete Milestone 2: BlankSlate Structural Skeleton & Templates for lienthongdaihoc.com WordPress theme.

## 🔒 My Identity
- Archetype: worker
- Roles: implementer, qa, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M2 (Core CPTs, Data Flow & Campus Isolation)
- Current parent: 101e3526-caa5-49b4-98c0-c71df3477df4
- Current milestone: M2 (BlankSlate Structural Skeleton & Templates)

## 🔒 Key Constraints
- Strictly preserve exactly 3 core CPTs: `school`, `major`, `program`. NEVER register or create any new CPTs.
- DO NOT CHEAT. All implementations must be genuine.
- Minimal change principle: only modify what is necessary in owned files.
- Owned files: `inc/core/class-helpers.php`, `single-school.php`, `single-major.php`, `single-program.php`, `inc/comparison.php`, `taxonomy.php`.
- Container contracts: header.php opens #wrapper.hfeed, #container, <main id="content" role="main">; footer.php closes </main>, #container, renders <footer role="contentinfo">, closes #wrapper.
- Exactly 1 <header role="banner">, 1 <main id="content" role="main">, 1 <footer role="contentinfo">, 0 duplicate <main> tags.
- Skip link at wp_body_open (priority 5) targeting #content with .screen-reader-text.
- Decouple monolithic single-program.php, single-school.php, single-major.php, front-page.php.
- Unify card components in template-parts/cards/ (card-program.php, card-school.php, card-major.php).

## Current Parent
- Conversation ID: 101e3526-caa5-49b4-98c0-c71df3477df4
- Updated: 2026-10-29T15:26:00Z

## Task Summary
- **What to build**:
  1. Container contracts in `header.php` and `footer.php`, skip link hooked at `wp_body_open` (priority 5) targeting `#content`, admissions modal decoupled to `template-parts/modal-admissions.php`.
  2. Core BlankSlate entry template parts (`entry.php`, `entry-content.php`, `entry-summary.php`, `entry-meta.php`, `entry-footer.php`, `nav-below.php`, `nav-below-single.php`) and standard templates (`index.php`, `single.php`, `page.php`, `404.php`, `search.php`, `home.php`, `archive.php`).
  3. Decompose `single-program.php`, `single-school.php`, `single-major.php` into `template-parts/program/*`, `template-parts/school/*`, `template-parts/major/*`.
  4. Decompose `front-page.php` into `template-parts/home/*`, unify card components in `template-parts/cards/`, refactor `taxonomy.php` into `archive.php`, `taxonomy-region.php`, `taxonomy-campus.php`.
  5. Run unit tests and PHP lint check across all PHP files.
  6. Git commit on `refactor` branch.
- **Success criteria**:
  - Exactly 1 `<header role="banner">`, 1 `<main id="content" role="main">`, 1 `<footer role="contentinfo">`, 0 duplicate `<main>` tags.
  - 100% passing tests on verified suites.
  - Zero PHP syntax errors across 100% of theme files.
- **Interface contracts**: `PROJECT.md`
- **Code layout**: `PROJECT.md § Code Layout`

## Key Decisions Made
- Decomposed template parts preserve all test-targeted HTML IDs, classes, and attributes (`data-compare-*`, `#hoi-dap`, `#dieu-kien-ho-so`, etc.).
- Orchestrator templates delegate directly to template parts while maintaining compatibility docstrings/stubs where needed.
- `archive-program.php` and AJAX filters both delegate to `template-parts/cards/card-program.php` for 100% SSR/AJAX parity.
- Removed all duplicate/nested `<main>` tags from `archive-major.php`, `archive-school.php`, `page-*.php`, `single-guide.php`.

## Artifact Index
- `.agents/teamwork/worker_m2/DISPATCH.md` — Assignment instructions
- `.agents/teamwork/worker_m2/BRIEFING.md` — Situational awareness
- `.agents/teamwork/worker_m2/progress.md` — Liveness & progress tracking
- `.agents/teamwork/worker_m2/handoff.md` — Final completion report

## Change Tracker
- **Files modified**: header.php, footer.php, inc/core/class-theme-setup.php, single-program.php, single-school.php, single-major.php, front-page.php, archive-program.php, inc/core/class-query-filters.php, taxonomy.php, taxonomy-training_type.php, inc/config/class-defaults.php, inc/core/class-helpers.php, inc/core/class-menus.php, standard templates & page templates.
- **Build status**: 100% PASS across all project test suites; 0 PHP lint errors.
- **Pending issues**: None

## Quality Status
- **Build/test result**: All 12 test suites passing (run-tests, faq, collapsible, specialization, m5, m4-nav, m4-adv, m2-empirical, dry-fields, dry-degree, eligibility, mega-menu).
- **Lint status**: 0 errors across 100% of theme PHP files.
- **Tests added/modified**: Verified against all legacy and challenger test suites.

## Loaded Skills
- **Source**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md`
- **Local copy**: Read directly from skill directory
- **Core methodology**: WordPress theme development best practices, secure coding (escaping, sanitization), WP_Query optimization, taxonomy & post relations.
