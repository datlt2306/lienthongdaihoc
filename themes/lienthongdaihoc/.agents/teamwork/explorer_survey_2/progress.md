# Progress — Security & DB Query Surveyor

Last visited: 2026-09-25T05:14:30Z

## Status
- [x] Initialized DISPATCH.md and BRIEFING.md
- [x] Scan and catalog all 49 PHP files in the theme
- [x] Direct file access guards (`defined('ABSPATH') || exit;`) audit across all PHP files
  - Found 3 non-compliant files: `header.php`, `inc/search-engine.php`, `tests/run-tests.php`
- [x] Database queries & `$wpdb` calls audit (SQL Injection risk, `$wpdb->prepare`)
  - Cataloged 22 `$wpdb` calls. Identified raw queries in templates (`archive-program.php`, `taxonomy-training_type.php`). Confirmed preparation and parameterization.
- [x] User input entry points & sanitization audit (`$_POST`, `$_GET`, `$_REQUEST`, etc.)
  - Identified file upload without mime-type whitelist in `inc/eligibility.php` (`degree_file`).
  - Identified IDOR vulnerability in `ltdh_elig_ajax_advanced_verify`.
  - Identified user-controlled `?limit=-1` in archive programs and taxonomies.
- [x] Output rendering & XSS escaping audit (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`, unescaped `echo`)
  - Audited all echo / output rendering points. Identified attribute escaping opportunities (`the_permalink` / `the_title` in HTML attributes).
- [x] AJAX handlers, form submissions & REST endpoints audit (CSRF nonce verification, capability checks)
  - Cataloged 6 AJAX actions, 2 REST routes, 2 native form submissions, 2 admin pages.
  - Identified missing nonce in `ltdh_ajax_filter_programs` (`functions.php`).
  - Identified missing nonce in native consultation form submit (`inc/lead-capture.php` & `inc/core/class-helpers.php`).
- [x] WP_Query, get_posts, custom query loops & N+1 / pagination / transient caching audit
  - Discovered critical bug: `delete_transient( 'ltdh_featured_schools_data' )` on line 16 of `front-page.php` running on every homepage load!
  - Discovered default `posts_per_page => -1` in `inc/core/class-query-filters.php` for school and major archives.
  - Discovered severe N+1 query loops in `archive-school.php` and `archive-program.php`.
  - Discovered multiple unbounded `numberposts => -1` queries on frontend and CF7 filter.
- [x] Synthesize findings and write comprehensive `handoff.md`
- [x] Send completion message to parent orchestrator
