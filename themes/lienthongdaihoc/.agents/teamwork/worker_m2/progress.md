# Progress — worker_m2

**Last visited**: 2026-10-01T09:50:00Z  
**Status**: Completed  
**Current Milestone**: M2 (Core CPTs, Data Flow & Campus Isolation)

## Checklist
- [x] Read DISPATCH.md, PROJECT.md, ORIGINAL_REQUEST.md, explorer_survey_ia_2/handoff.md
- [x] Review php-wordpress skill
- [x] Inspect current implementations in:
  - [x] `inc/core/class-helpers.php` (`ltdh_get_school_training_types`, `ltdh_get_program_learning_details`, `ltdh_get_school_unique_majors_count`)
  - [x] `single-school.php` (program queries and display)
  - [x] `single-major.php` (program queries and display)
  - [x] `single-program.php` (campus rendering)
  - [x] `inc/comparison.php` (campus rendering)
  - [x] `taxonomy.php` (syntax corruption and campus rendering)
- [x] Refactor `inc/core/class-helpers.php`:
  - [x] `ltdh_get_school_training_types`: Roll up exclusively from published in-scope programs with allowed terms (`tu-xa`, `vua-hoc-vua-lam`), eliminated Step 1 (direct terms on school)
  - [x] `ltdh_get_program_learning_details`: Filter out `online` from physical campuses, set delivery mode & default to 'Toàn quốc'
  - [x] `ltdh_get_school_unique_majors_count`: Removed 100 limit, filtered by allowed training types
- [x] Refactor `single-school.php`:
  - [x] Enforce `post_status => publish`
  - [x] Filter by allowed Liên thông training types (`tu-xa`, `vua-hoc-vua-lam`)
  - [x] Remove artificial `posts_per_page => 10` limits
- [x] Refactor `single-major.php`:
  - [x] Enforce `post_status => publish`
  - [x] Filter by allowed Liên thông training types (`tu-xa`, `vua-hoc-vua-lam`)
  - [x] Remove artificial `posts_per_page => 10` limits
- [x] Refactor `single-program.php`:
  - [x] Sanitize campus display (never show "Online" under physical location, fallback to 'Toàn quốc')
- [x] Refactor `inc/comparison.php`:
  - [x] Sanitize campus display (exclude "online" / use helper learning details)
- [x] Fix syntax corruption in `taxonomy.php`:
  - [x] Fixed `<a href="<"'?php the_permalink(); ?>"'>" ` to `<a href="<?php the_permalink(); ?>" `
- [x] Verification:
  - [x] Run `php -l` on all 6 modified files (100% clean, 0 syntax errors)
  - [x] Verified logic with automated test assertions
- [x] Write `handoff.md` and send completion message to parent
