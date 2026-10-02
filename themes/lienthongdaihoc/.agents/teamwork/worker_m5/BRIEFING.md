# BRIEFING — 2026-10-01T11:20:00Z

## Mission
Milestone M5: Standardize Templates & Program Presentation across SSR & AJAX cards, single views, study mode archives, and banners.

## 🔒 My Identity
- Archetype: worker
- Roles: implementer, qa, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m5/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M5 (Templates & Program Presentation)

## 🔒 Key Constraints
- DO NOT CHEAT. All implementations must be genuine.
- Minimal change principle. No unrelated refactoring.
- Scope ownership: `taxonomy-training_type.php`, `archive-program.php`, `inc/core/class-query-filters.php`, `single-program.php`, `single-school.php`, `single-major.php`, `template-parts/banner.php`, `template-parts/compare/program-cards.php`.
- Zero "Hệ " prefix on type badges in program cards.
- Program headline formula: `"Liên thông ngành " . esc_html( $major_name ) . " - " . esc_html( $type_name )` with school name as subtitle/header.
- Single program notice cleanup: replace obsolete "hệ Chính quy" notice.
- Banner template cleanup: 0 "Văn bằng 2", "Chính quy", "hệ đào tạo". Subtitle reflects in-scope description.
- Preservation of prior milestone tests (M2, M3, M4) with 0 regressions.

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: not yet

## Task Summary
- **What to build**: Standardize Program Cards (SSR, AJAX, single-school, single-major, compare), update single-program.php notice and presentation, align study mode archives, clean template-parts/banner.php.
- **Success criteria**:
  1. Structural and visual parity between SSR and AJAX cards with standard admission opportunity formula.
  2. Badge displays clean type name with no "Hệ ".
  3. School and major single program cards aligned to formula.
  4. Compare card title aligned to formula.
  5. `single-program.php` quota announcement updated.
  6. Study mode archives query published in-scope programs only, headings reflect "Hình thức học".
  7. Banner template pure of "Văn bằng 2", "Chính quy", "hệ đào tạo".
  8. All syntax checks (`php -l`) pass, empirical test script passes, M2-M4 test suites pass.
- **Interface contracts**: PROJECT.md § Headline Presentation Contract (M5)

## Key Decisions Made
- Standardized program cards across SSR and AJAX to display school identity in card cover/header with fallback logo, headline formula `Liên thông ngành [Major] - [Type]`, and compare toggle attributes.
- Stripped redundant prefixes `Ngành `, `Cử nhân `, `Kỹ sư ` from major names and `Hệ ` from training type names before headline and badge assembly to avoid double-prefixing.
- Replaced legacy admissions quota notice in `single-program.php` with in-scope message: "Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này."
- Enforced clean study mode archives with tax_query default `['tu-xa', 'vua-hoc-vua-lam']` and H1 heading "Hình thức học: [Tên hình thức]".
- Cleaned banner subtitles for `/he-dao-tao/` and taxonomy terms, stripping out-of-scope terms.

## Change Tracker
- **Files modified**:
  - `template-parts/banner.php`: Cleaned out-of-scope titles/subtitles, stripped "Hệ " prefix.
  - `single-program.php`: Replaced legacy notice, cleaned delivery mode label and campus fallback.
  - `taxonomy-training_type.php`: Standardized card headline, badge without "Hệ", compare attributes, and in-scope tax_query.
  - `archive-program.php`: Standardized card headline, badge without "Hệ", compare attributes, and in-scope tax_query.
  - `inc/core/class-query-filters.php`: Brought AJAX cards to 1:1 structural parity with SSR cards.
  - `single-school.php`: Calculated `$opportunity_title` and rendered in grouped and single layouts.
  - `single-major.php`: Calculated `$opportunity_title` and rendered in grouped and single layouts with school subtitle.
  - `template-parts/compare/program-cards.php`: Aligned card title and badge with formula.
  - `tests/test-m5-templates-presentation.php`: New test suite with 65 assertions.
- **Build status**: PASS (`php -l` clean on all 9 files)
- **Pending issues**: None

## Quality Status
- **Build/test result**: All 4 test suites passing: M2 (46/46), M3 (38/38), M4 (18/18), M5 (65/65). Total: 167 tests passed, 0 failed.
- **Lint status**: Zero syntax errors across all 9 PHP files.
- **Tests added/modified**: `tests/test-m5-templates-presentation.php` (65 assertions covering all M5 requirements).

## Loaded Skills
- **Source**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md`
- **Local copy**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m5/SKILL_php-wordpress.md`
- **Core methodology**: WordPress theme development best practices, template hierarchy, query efficiency, escaping/sanitization, and AJAX handling.

## Artifact Index
- `.agents/teamwork/worker_m5/DISPATCH.md` — Assignment from orchestrator
- `.agents/teamwork/worker_m5/BRIEFING.md` — Persistent state index
- `.agents/teamwork/worker_m5/progress.md` — Liveness heartbeat
- `.agents/teamwork/worker_m5/handoff.md` — 5-component handoff report
- `tests/test-m5-templates-presentation.php` — M5 test suite
