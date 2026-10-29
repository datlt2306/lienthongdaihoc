# DISPATCH for worker_m2

You are teamwork_worker assigned to execute Milestone 2: BlankSlate Structural Skeleton & Templates.
Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/
ORIGINAL_REQUEST.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
PROJECT.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/PROJECT.md

## MANDATORY INTEGRITY WARNING
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Explorer Blueprints to Implement
Read the 3 Explorer reports carefully:
- `explorer_m2_1`: `.agents/teamwork/explorer_m2_1/analysis.md`
- `explorer_m2_2`: `.agents/teamwork/explorer_m2_2/analysis.md`
- `explorer_m2_3`: `.agents/teamwork/explorer_m2_3/analysis.md`

## Concrete Scope of Work

### 1. Container Contracts & Core BlankSlate Templates
- **`header.php`**:
  - Add Schema microdata to `<html>`: `itemscope itemtype="https://schema.org/WebPage"`.
  - Remove inline `<style id="ltdh-critical-typography">` (lines 52-64).
  - Remove hardcoded skip link (lines 80-82). Hook skip link via `wp_body_open` priority 5 targeting `#content` in `inc/core/class-theme-setup.php`.
  - Update root wrapper to `<div id="wrapper" class="hfeed">`.
  - Add `role="banner"` to `<header id="masthead">`.
  - Add `role="navigation"` and `aria-label` to `<nav id="site-navigation">`.
  - Open container at bottom: `<div id="container"><main id="content" class="site-main" role="main">`.
- **`footer.php`**:
  - Before footer, close container: `</main></div><!-- #container -->`.
  - Add `role="contentinfo"` to `<footer id="colophon">`.
  - Close wrapper: `</div><!-- #wrapper -->`.
  - Decouple admissions modal into `template-parts/modal-admissions.php` and include via `get_template_part( 'template-parts/modal-admissions' )`.
- **Create 7 BlankSlate Entry Partials**:
  - `entry.php`, `entry-content.php`, `entry-summary.php`, `entry-meta.php`, `entry-footer.php`, `nav-below.php`, `nav-below-single.php`.
- **Standard WordPress Fallbacks**:
  - `index.php`: Universal BlankSlate fallback loop delegating to `entry.php`.
  - `single.php`: BlankSlate single template delegating to `entry.php`, `nav-below-single.php`, `comments_template()`.
  - `page.php`: BlankSlate page template delegating to `entry.php`.
  - `404.php`: BlankSlate semantic 404 error page.
  - `search.php`: Search results page delegating to `entry.php`.
  - `home.php`: Move the blog archive logic from legacy `index.php` to `home.php` so `/tin-tuc/` continues functioning.

### 2. Single Template Decomposition
- **`single-program.php`**: Decompose into modular partials under `template-parts/program/`:
  - `hero.php`, `highlights.php`, `batches.php`, `overview.php`, `requirements.php`, `tuition.php`, `exemptions.php`, `curriculum.php`, `faq.php`, `verification.php`, `related.php`, `sticky-bar.php`.
  - In `single-program.php`, remove outer `<main>` tags (now provided by `header.php`/`footer.php`).
- **`single-school.php`**: Decompose into modular partials under `template-parts/school/`:
  - `hero.php`, `overview.php`, `programs.php`, `admission-info.php`, `contact.php`, `related.php`.
  - Remove outer `<main>` tags.
- **`single-major.php`**: Decompose into modular partials under `template-parts/major/`:
  - `hero.php`, `roadmaps.php`, `programs.php`, `admission-targets.php`, `faq.php`, `related.php`.
  - Remove outer `<main>` tags.

### 3. Front Page, Archives, Taxonomy & Card Decomposition
- **`front-page.php`**: Decompose into `template-parts/home/`:
  - `hero.php`, `stats.php`, `search-box.php`, `featured-schools.php`, `hot-programs.php`, `major-categories.php`, `admission-guide.php`, `eligibility-cta.php`, `latest-news.php`, `consultation-section.php`.
  - Remove outer `<main>` tags.
- **Card Components** under `template-parts/cards/`:
  - `card-program.php`: Reusable program card.
  - `card-school.php`: Reusable school card.
  - `card-major.php`: Reusable major card.
- **`archive-program.php` & `taxonomy-training_type.php`**:
  - Delegate card rendering to `template-parts/cards/card-program.php`.
  - Remove outer `<main>` tags.
- **`taxonomy.php` Refactoring**:
  - Create standard BlankSlate `archive.php`.
  - Create `taxonomy-region.php` and `taxonomy-campus.php`.
  - Remove legacy monolithic `taxonomy.php`.

### 4. Verification & Testing
- Run all test suites:
  - `php tests/run-tests.php`
  - `php tests/test-dry-school-program-fields.php`
  - `php tests/test-dry-degree-and-diploma.php`
  - `php tests/test-program-title-and-breadcrumb.php`
  - `php tests/test-program-title-normalization.php`
  - `php tests/test-post-sharing.php`
  - `php tests/test-eligibility-unit.php`
- PHP syntax lint across all PHP files: `find . -name "*.php" ... -exec php -l {} +` -> must have 0 syntax errors.
- Ensure HTML5 landmark validation: Exactly 1 `<header role="banner">`, 1 `<main id="content" role="main">`, 1 `<footer role="contentinfo">`, 0 duplicate `<main>` tags.

### 5. Git Commit
- Stage changes in `themes/lienthongdaihoc/`.
- Commit on branch `refactor`: `feat(refactor): implement BlankSlate structural skeleton and modular template hierarchy`.

Document all modified files, created partials, test outputs, and verification commands in `handoff.md`.
Report back when complete.


## 2026-10-29T07:24:43Z
[Message] timestamp=2026-10-29T07:24:43Z sender=101e3526-caa5-49b4-98c0-c71df3477df4 priority=MESSAGE_PRIORITY_HIGH content=You are teamwork_worker assigned to execute Milestone 2: BlankSlate Structural Skeleton & Templates.
Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/
ORIGINAL_REQUEST.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
PROJECT.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/PROJECT.md

DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Please read DISPATCH.md in your working directory and the 3 explorer reports:
- explorer_m2_1: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m2_1/analysis.md
- explorer_m2_2: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m2_2/analysis.md
- explorer_m2_3: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m2_3/analysis.md

Implement Milestone 2 per the detailed specifications in DISPATCH.md:
1. Container contracts in header.php and footer.php (#wrapper.hfeed, role="banner", #container, <main id="content" role="main">, role="contentinfo"), skip link at wp_body_open (priority 5), modal decoupling to template-parts/modal-admissions.php.
2. Core BlankSlate entry template parts (entry.php, entry-content.php, entry-summary.php, entry-meta.php, entry-footer.php, nav-below.php, nav-below-single.php) and standard templates (index.php, single.php, page.php, 404.php, search.php, home.php).
3. Decompose monolithic single-program.php, single-school.php, single-major.php into template-parts/program/*, template-parts/school/*, template-parts/major/*.
4. Decompose front-page.php into template-parts/home/*, unify card components in template-parts/cards/, refactor taxonomy.php into archive.php, taxonomy-region.php, taxonomy-campus.php.
5. Run all unit tests and PHP lint check.
6. Commit all changes on the `refactor` branch.

Write your handoff report to handoff.md with complete logs, test proofs, and file inventories.
Send a message when complete.
