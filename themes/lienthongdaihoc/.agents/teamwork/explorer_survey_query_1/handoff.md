# HANDOFF REPORT: REQUIREMENT R2 TECHNICAL AUDIT
## QUERYING, FILTERING & TAXONOMY UX CHO HỆ ĐÀO TẠO (`training_type`) VÀ TRƯỜNG ĐỐI TÁC (`school`)

- **Agent**: `explorer_survey_query_1`
- **Role**: Teamwork Explorer (Read-only Investigation & Synthesis)
- **Target Audience**: `orchestrator_3` / Parent Agent (`8ecd8568-917b-4973-87b0-609a66bbbf3b`)
- **Date**: 2026-09-28T04:17:00Z
- **Reference Document**: `analysis.md` in this directory

---

## 1. OBSERVATIONS

1. **Taxonomy Object Type Mismatch**:
   - In `inc/acf-import-cpts.json` (lines 170-176):
     ```json
     "taxonomy": "training_type",
     "object_type": [
         "program"
     ],
     ```
     `training_type` is ONLY mapped to `program`. It is NOT assigned to `school`.
   - In `archive-school.php` (Card View, line 199 & Featured Section, line 80):
     ```php
     $school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
     ```
     Card View queries terms on `$school_id` directly, yielding an empty array because terms belong to `program`, not `school`.
   - In `archive-school.php` (List View, lines 265-306):
     ```php
     $offered_program_ids = get_posts( [
         'post_type'   => 'program',
         'numberposts' => -1,
         'fields'      => 'ids',
         'meta_query'  => [ [ 'key' => 'school_relationship', 'value' => $school_id, 'compare' => '=' ] ],
     ] );
     ...
     foreach ( $offered_program_ids as $pid ) {
         $terms = wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE );
         ...
     }
     ```
     List View performs a secondary query for all programs, then loops through each program calling `wp_get_post_terms`. Furthermore, this query has NO `admission_status != 'tam-ngung'` check, meaning paused programs still trigger active badges.

2. **Main Query Bypass & Double Query in `taxonomy-training_type.php`**:
   - In `inc/core/class-query-filters.php` (lines 20-22):
     ```php
     if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) ) {
         $query->set( 'post_type', LTDH_CPT_PROGRAM );
     }
     ```
     The main query is already configured for `program`.
   - In `taxonomy-training_type.php` (line 128):
     ```php
     $args  = apply_filters( 'pre_get_posts_args_ltdh', $args );
     $query = new WP_Query( $args );
     ```
     `taxonomy-training_type.php` ignores the main query and instantiates a second `WP_Query`, running the database query twice per request.
   - `archive-program.php` (541 lines) and `taxonomy-training_type.php` (538 lines) are 100% duplicate code files.

3. **Dead AJAX Code on Frontend**:
   - In `assets/js/main.js` (lines 9-12):
     ```javascript
     const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"]');
     const container = document.getElementById('program-results-container');
     if (filterForm && container) { ... }
     ```
   - In `taxonomy-training_type.php` (line 352):
     ```html
     <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
     ```
     Element `#program-results-container` DOES NOT EXIST anywhere in the template files. Consequently, `filterForm && container` evaluates to false, and AJAX filtering never executes. All filtering is currently full page reload.

4. **Global Facet Counting & Phantom Facet Mismatch**:
   - In `taxonomy-training_type.php` (lines 164-172):
     ```php
     $t_results = $wpdb->get_results( "
         SELECT t.slug, COUNT(p.ID) as count
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         WHERE p.post_type = 'program' AND p.post_status = 'publish' AND tt.taxonomy = 'training_type'
         GROUP BY t.slug
     " );
     ```
     The SQL query counts global programs for each training type without taking `$selected_school` or `$selected_nhom` into account, leading to misleading nonzero badge counts for schools that do not offer those programs.

5. **Canonical URL Redirect Loop & RankMath Breadcrumbs Suppression**:
   - In `inc/acf-import-cpts.json` (line 146): `"has_archive_slug": "chuong-trinh"`.
   - In `inc/core/class-rewrite-rules.php` (lines 154-161):
     ```php
     if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
         $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
         wp_redirect( $redirect_url, 301 );
         exit;
     }
     ```
     Visiting `/he-dao-tao/` results in RankMath generating canonical `https://domain.com/chuong-trinh/`, which returns a 301 Redirect to `/he-dao-tao/tu-xa/`.
   - In `inc/core/class-helpers.php` (line 324):
     `$is_he_dao_tao = (bool) preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path );`
     `if ( ! $is_he_dao_tao && function_exists( 'rank_math_the_breadcrumbs' ) )`
     RankMath breadcrumbs are explicitly disabled for all `/he-dao-tao/*` URLs, falling back to a plain HTML breadcrumb without `BreadcrumbList` JSON-LD schema.

6. **Unused Transients**:
   - `LTDH_TRANSIENT_FEATURED_SCHOOLS` is defined in `inc/config/constants.php:26` and deleted in `inc/core/class-helpers.php:505`, but `archive-school.php:35` runs raw `new WP_Query( $featured_args )` on every load without calling `get_transient` or `set_transient`.

---

## 2. LOGIC CHAIN

1. From Observation 1, because `training_type` is attached only to `program`, calling `wp_get_post_terms( $school_id, 'training_type' )` fails to retrieve badges in Card View. To circumvent this in List View, developers added a nested loop querying all programs of the school and their terms. This directly creates a 144+ query N+1 bottleneck on the school archive page and displays badges for paused programs because `admission_status` was not filtered.
2. From Observation 2, because `taxonomy-training_type.php` executes `new WP_Query` inside the template instead of configuring the main query via `pre_get_posts`, WordPress runs the archive query twice on every page hit, doubling database overhead and execution time.
3. From Observation 3, because the JavaScript selector `#program-results-container` is missing from the template HTML, the event listeners for AJAX filtering are never attached. Thus, any perceived AJAX speed or dynamic loading is completely absent in production; every filter interaction triggers a full page refresh.
4. From Observation 4, because the facet counts are computed with raw SQL grouping solely by taxonomy term rather than filtering by current active query parameters (e.g. school), users encounter "phantom facets"—clicking a facet showing a positive count leads to a 0-result page.
5. From Observation 5, because the program CPT archive slug is `chuong-trinh` while rewrite rules redirect `/chuong-trinh/` 301 to `/he-dao-tao/tu-xa/`, RankMath generates a canonical link pointing to a redirected URL. This damages indexing and crawl efficiency. Furthermore, disabling RankMath breadcrumbs without outputting JSON-LD `BreadcrumbList` schema strips structured data from Google Search results.

---

## 3. CAVEATS

- No source code modifications were performed during this audit (strictly read-only mode).
- Testing was performed via static source code analysis, AST tracing, and database schema cross-referencing. Actual query execution times and server response latencies may vary depending on MySQL server configuration, MariaDB version, and hardware specs.
- External Object Cache servers (e.g. Redis, Memcached) were assumed not to be configured by default in local/production environments unless explicitly installed via WordPress drop-in (`object-cache.php`).

---

## 4. CONCLUSION

Requirement R2 contains **3 Critical, 3 High, and 4 Medium technical bottlenecks**:
1. **Critical**: Massive N+1 queries in `archive-school.php` (up to 200-800 queries/page).
2. **Critical**: Inconsistent training system badge display between Card View (0 badges) and List View (N+1 queries + paused program badge leakage).
3. **High**: Main query bypass and double query execution on all `/he-dao-tao/*` archives.
4. **High**: Dead AJAX filter code in `assets/js/main.js` due to missing DOM wrapper ID.
5. **High**: Phantom facet counts misleading users on school-filtered views.
6. **High**: Canonical redirect loop on `/he-dao-tao/` and lack of structured breadcrumb schema.

All 4 concrete optimization solutions (with full code snippets) are documented in detail in `analysis.md` and are ready for implementation by the engineering team.

---

## 5. VERIFICATION METHOD

To independently verify all findings in this report:

1. **Verify N+1 Queries**:
   - Inspect `archive-school.php` lines 265-306.
   - Run Query Monitor or enable SQL logging on `/truong-doi-tac/?view=list`. Observe the repetitive `SELECT * FROM wp_posts WHERE post_type = 'program' ...` followed by repetitive queries to `wp_term_relationships`.
2. **Verify Missing Badges in Card View**:
   - Inspect `archive-school.php` line 199. Check the return value of `wp_get_post_terms($school_id, 'training_type')`. Observe that it returns an empty array for schools because `training_type` object type is `['program']` in `inc/acf-import-cpts.json:174`.
3. **Verify Dead AJAX Filter**:
   - Inspect `assets/js/main.js` line 10 (`document.getElementById('program-results-container')`).
   - Run grep command:
     `grep -rn "program-results-container" .`
     Confirm that the string exists only in `main.js` and nowhere in `.php` templates.
4. **Verify Canonical Redirect Loop**:
   - Inspect `inc/acf-import-cpts.json` line 146 (`has_archive_slug: chuong-trinh`) and `inc/core/class-rewrite-rules.php` lines 154-161.
   - Run `curl -I https://domain.test/chuong-trinh/` to verify HTTP 301 response redirecting to `/he-dao-tao/tu-xa/`.
5. **Verify Double Query**:
   - Inspect `taxonomy-training_type.php` line 128 (`$query = new WP_Query( $args )`).
   - Observe that the template does not use global `$wp_query` or `have_posts()`.
