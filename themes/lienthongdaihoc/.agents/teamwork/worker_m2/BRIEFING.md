# BRIEFING — 2026-10-01T09:51:00Z

## Mission
Implement Milestone M2: Core CPTs, Data Flow & Campus Isolation for lienthongdaihoc.com WordPress theme.

## 🔒 My Identity
- Archetype: worker
- Roles: implementer, qa, specialist
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m2/
- Original parent: f7ebf938-afab-4e8b-b557-505007c00d2d
- Milestone: M2 (Core CPTs, Data Flow & Campus Isolation)

## 🔒 Key Constraints
- Strictly preserve exactly 3 core CPTs: `school`, `major`, `program`. NEVER register or create any new CPTs.
- DO NOT CHEAT. All implementations must be genuine.
- Minimal change principle: only modify what is necessary in owned files.
- Owned files: `inc/core/class-helpers.php`, `single-school.php`, `single-major.php`, `single-program.php`, `inc/comparison.php`, `taxonomy.php`.

## Current Parent
- Conversation ID: f7ebf938-afab-4e8b-b557-505007c00d2d
- Updated: 2026-10-01T09:51:00Z

## Task Summary
- **What to build**:
  1. In `inc/core/class-helpers.php`:
     - Refactored `ltdh_get_school_training_types( $school_id )`: Step 1 eliminated; rolls up exclusively from published, in-scope programs with allowed terms (`tu-xa`, `vua-hoc-vua-lam`).
     - Refactored `ltdh_get_program_learning_details( $program_id )`: Filters out `online` from physical campuses; returns 'Toàn quốc' if tu-xa and no physical campus; sets mode to 'Học online 100%' (tu-xa) or 'Học tập trung / Cuối tuần' (vua-hoc-vua-lam).
     - Refactored `ltdh_get_school_unique_majors_count`: Removed 100 limit, filtered by allowed training types.
  2. In `single-school.php` (lines 365-404) and `single-major.php` (lines 348-373):
     - Enforced `'post_status' => 'publish'` and filtered by allowed training types (`tu-xa`, `vua-hoc-vua-lam`).
     - Removed artificial `posts_per_page => 10` limits (`'posts_per_page' => -1`).
  3. In `single-program.php:266-268` and `inc/comparison.php:168`:
     - Sanitized campus rendering, preventing "Online" from displaying under physical location.
  4. In `taxonomy.php:220`:
     - Fixed corrupted link syntax `<a href="<"'?php the_permalink(); ?>"'>"`.
  5. Verified syntax with `php -l` and verified logic assertions.
- **Success criteria**:
  - Zero syntax errors (`php -l` passes across all 6 files).
  - Genuine data rollup and sanitization logic.
  - Strict preservation of 3 CPTs.
- **Interface contracts**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md`
- **Code layout**: PROJECT.md § Code Layout

## Key Decisions Made
- `ltdh_get_school_training_types()` queries published programs using `tax_query` for `tu-xa` and `vua-hoc-vua-lam`, with fallback to `_offered_programs`, completely bypassing school-level static terms.
- `ltdh_get_program_learning_details()` isolates `online` from physical campus list, maps delivery mode precisely, and defaults `campus` to `'Toàn quốc'` for remote training.

## Artifact Index
- `.agents/teamwork/worker_m2/DISPATCH.md` — Assignment instructions
- `.agents/teamwork/worker_m2/BRIEFING.md` — Situational awareness
- `.agents/teamwork/worker_m2/progress.md` — Liveness & progress tracking
- `.agents/teamwork/worker_m2/handoff.md` — Final completion report

## Change Tracker
- **Files modified**:
  - `inc/core/class-helpers.php`: Refactored `ltdh_get_program_learning_details()`, `ltdh_get_school_training_types()`, and `ltdh_get_school_unique_majors_count()`.
  - `single-school.php`: Updated program query to enforce publish status, filter by allowed training types, and remove 10-post limit.
  - `single-major.php`: Updated program query to enforce publish status, filter by allowed training types, and remove 10-post limit.
  - `single-program.php`: Sanitized physical campus display with fallback to 'Toàn quốc'.
  - `inc/comparison.php`: Sanitized campus array filtering out 'online'.
  - `taxonomy.php`: Fixed corrupted permalink tag on line 220.
- **Build status**: PASS (all 6 files pass `php -l`)
- **Pending issues**: None

## Quality Status
- **Build/test result**: PASS (`php -l` 0 errors, verification logic passed)
- **Lint status**: Clean
- **Tests added/modified**: Verified all assertions via test execution

## Loaded Skills
- **Source**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/skills/php-wordpress/SKILL.md`
- **Local copy**: Read directly from skill directory
- **Core methodology**: WordPress theme development best practices, secure coding (escaping, sanitization), WP_Query optimization, taxonomy & post relations.
