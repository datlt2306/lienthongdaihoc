# Challenge Report: Milestone M3 — Taxonomy Label & Routing Verification

**Challenger**: `challenger_m3_1` (M3 Routing & Canonical Challenger)  
**Date**: 2026-10-01  
**Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_1/`  
**Verdict**: **APPROVE**  

---

## 1. Observation

### 1.1. Implementation Code Inspection
1. **Redirect Implementation (`inc/core/class-rewrite-rules.php:247-254`)**:
   ```php
   if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
       $redirect_url = home_url( '/he-dao-tao/' );
       if ( ! empty( $_GET ) ) {
           $redirect_url = add_query_arg( $_GET, $redirect_url );
       }
       wp_redirect( $redirect_url, 301 );
       exit;
   }
   ```
   Observed that:
   - Pattern `#^/chuong-trinh/?$#i` matches `/chuong-trinh` and `/chuong-trinh/` case-insensitively.
   - The redirect target is explicitly `home_url( '/he-dao-tao/' )`, completely replacing the legacy forced redirect `home_url( '/he-dao-tao/tu-xa/' )`.
   - `$_GET` parameters are preserved and appended via `add_query_arg( $_GET, $redirect_url )`.
   - Redirect code is `301` (Permanent Redirect).
   - Strict regex anchors `^...$` ensure subpaths such as `/chuong-trinh/cntt/` do not accidentally trigger this redirect.

2. **Rank Math Canonical Hook (`inc/seo/class-rankmath-integration.php:68-88`)**:
   ```php
   function ltdh_seo_enforce_canonical_url( $canonical ) {
       if ( is_singular( 'program' ) || is_singular( 'school' ) ) {
           $post_id = get_the_ID();
           if ( $post_id ) {
               return home_url( '/' . get_post_field( 'post_name', $post_id ) . '/' );
           }
       }

       if ( is_post_type_archive( 'program' ) ) {
           return home_url( '/he-dao-tao/' );
       }

       $request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
       if ( $request_path && preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
           return home_url( '/he-dao-tao/' );
       }

       return $canonical;
   }
   add_filter( 'rank_math/frontend/canonical', 'ltdh_seo_enforce_canonical_url' );
   add_filter( 'rank_math/paper/canonical_url', 'ltdh_seo_enforce_canonical_url' );
   ```
   Observed that:
   - When `is_post_type_archive( 'program' )` is true, canonical returns `home_url( '/he-dao-tao/' )`.
   - When `REQUEST_URI` path is `/chuong-trinh/`, canonical returns `home_url( '/he-dao-tao/' )`.
   - Both filters (`rank_math/frontend/canonical` and `rank_math/paper/canonical_url`) are registered with `ltdh_seo_enforce_canonical_url`.
   - Singular program canonicals (`is_singular( 'program' )`) are protected, returning flat permalinks `/%postname%/`.
   - Non-program routes pass through untouched.

3. **Catalog Base Route `/he-dao-tao/` Handling (`inc/core/class-rewrite-rules.php:261-276`)**:
   ```php
   function ltdh_template_include_he_dao_tao( $template ) {
       $request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
       if ( preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path ) ) {
           global $wp_query;
           if ( $wp_query ) {
               $wp_query->is_404 = false;
               status_header( 200 );
           }
           $archive_template = locate_template( 'taxonomy-training_type.php' ) ?: locate_template( 'archive-program.php' );
           if ( $archive_template ) {
               return $archive_template;
           }
       }
       return $template;
   }
   add_filter( 'template_include', 'ltdh_template_include_he_dao_tao' );
   ```
   Observed that:
   - When visiting `/he-dao-tao/` (and its subpaths/pagination), `ltdh_redirect_taxonomy_base()` does not intercept or redirect.
   - `ltdh_template_include_he_dao_tao()` intercepts the template loader, forces `$wp_query->is_404 = false`, explicitly calls `status_header( 200 )`, and loads `taxonomy-training_type.php` or `archive-program.php`.

4. **Program Archive Internal References (`archive-program.php`)**:
   - Line 180: `<a href="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" ...>Xóa tất cả bộ lọc</a>`
   - Line 188: `<form id="catalog-filter-form" action="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" method="GET" ...>`
   - Line 255: `$all_url = home_url( '/he-dao-tao/' );`
   - Line 464: `<a href="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" ...>✕ Đặt lại tất cả bộ lọc</a>`
   All form actions and reset links cleanly reference `/he-dao-tao/`.

### 1.2. Automated Empirical Test Execution
Executed `php tests/test-m3-empirical.php` with 38 behavioral assertions.
Verbatim command output:
```text
===================================================================
EMPIRICAL TEST SUITE: M3 ROUTING & CANONICAL VERIFICATION
===================================================================

SUITE 1: Request to /chuong-trinh/ Redirect Target Verification
-------------------------------------------------------------------
  [PASS] 1.1: /chuong-trinh/ triggers redirect
  [PASS] 1.1: /chuong-trinh/ redirect status is HTTP 301 (actual: 301)
  [PASS] 1.1: /chuong-trinh/ target is https://lienthongdaihoc.com/he-dao-tao/ (actual: https://lienthongdaihoc.com/he-dao-tao/)
  [PASS] 1.1: /chuong-trinh/ target is NOT /he-dao-tao/tu-xa/
  [PASS] 1.2: /chuong-trinh (no trailing slash) 301 redirects to /he-dao-tao/
  [PASS] 1.3a: /CHUONG-TRINH/ (uppercase) 301 redirects to /he-dao-tao/
  [PASS] 1.3b: /Chuong-Trinh/ (mixed case) 301 redirects to /he-dao-tao/
  [PASS] 1.4: Subpath /chuong-trinh/cntt/ is NOT redirected by ltdh_redirect_taxonomy_base

SUITE 2: Query Args Preservation Verification
-------------------------------------------------------------------
  [PASS] 2.1: /chuong-trinh/?truong=utc&nganh=cntt returns 301
  [PASS] 2.1: Query args preserved exactly in target URL (actual: https://lienthongdaihoc.com/he-dao-tao/?truong=utc&nganh=cntt)
  [PASS] 2.1: Both truong=utc and nganh=cntt present in destination
  [PASS] 2.2: /chuong-trinh?truong=utc&nganh=cntt (no slash) preserves query args and adds trailing slash to path
  [PASS] 2.3: Multi-param query (truong, nganh, sort, paged) preserved cleanly
  [PASS] 2.4: Array parameters truong[] preserved accurately in redirect
  [PASS] 2.5: Vietnamese accented search query preserved in redirect
  [PASS] 2.6: Marketing UTM parameters preserved on redirect

SUITE 3: Rank Math Canonical URL Filter Verification
-------------------------------------------------------------------
  [PASS] 3.1a: ltdh_seo_enforce_canonical_url is hooked to rank_math/frontend/canonical
  [PASS] 3.1b: ltdh_seo_enforce_canonical_url is hooked to rank_math/paper/canonical_url
  [PASS] 3.2: On is_post_type_archive('program'), canonical is home_url('/he-dao-tao/') (actual: https://lienthongdaihoc.com/he-dao-tao/)
  [PASS] 3.3: When REQUEST_URI is /chuong-trinh/, canonical points directly to /he-dao-tao/ (actual: https://lienthongdaihoc.com/he-dao-tao/)
  [PASS] 3.4: With query args, canonical points to clean canonical base /he-dao-tao/ without query args
  [PASS] 3.5: Singular program canonical enforces flat URL /cu-nhan-cong-tac-xa-hoi/
  [PASS] 3.6: Unrelated page canonical is untouched (https://lienthongdaihoc.com/tin-tuc/)

SUITE 4: Verify /he-dao-tao/ Returns HTTP 200 without Redirect Loop
-------------------------------------------------------------------
  [PASS] 4.1: /he-dao-tao/ does NOT redirect in ltdh_redirect_taxonomy_base()
  [PASS] 4.2: status_header(200) was called for /he-dao-tao/ (actual: 200)
  [PASS] 4.2: wp_query->is_404 is cleared to false
  [PASS] 4.2: Valid catalog template returned (taxonomy-training_type.php)
  [PASS] 4.3a: Term archive /he-dao-tao/tu-xa/ does NOT redirect
  [PASS] 4.3a: /he-dao-tao/tu-xa/ sets HTTP 200 and is_404=false
  [PASS] 4.3b: Term archive /he-dao-tao/vua-hoc-vua-lam/ does NOT redirect
  [PASS] 4.3b: /he-dao-tao/vua-hoc-vua-lam/ sets HTTP 200 and is_404=false
  [PASS] 4.4: /he-dao-tao/page/2/ does NOT redirect
  [PASS] 4.4: /he-dao-tao/page/2/ sets HTTP 200 and is_404=false
  [PASS] 4.5: /he-dao-tao/tu-xa/page/2/ does NOT redirect
  [PASS] 4.6: Hop 1: /chuong-trinh/ returns 301
  [PASS] 4.6: Hop 2: Destination /he-dao-tao/ terminates cleanly (NO redirect, NOT looping back to /chuong-trinh/)
  [PASS] 4.6: Hop 2: Destination /he-dao-tao/ serves HTTP 200 with catalog template
  [PASS] 4.6: Hop 2: Canonical tag on /he-dao-tao/ is self-referential /he-dao-tao/ (Zero Canonical Loop)

===================================================================
TEST RESULTS: 38 PASSED, 0 FAILED
===================================================================
```

### 1.3. Forensic Code Integrity & Linting
- Executed `php -l` on all 15 modified files:
  15/15 passed with `No syntax errors detected`.
- Executed `php tests/test-m3-forensic.php`:
  82/82 assertions passed across AST token streams, JSON schema, menu injection, breadcrumb trails, public UI label audits, and form action checks.

---

## 2. Logic Chain

1. **Verification of Task 1 (`/chuong-trinh/` 301 Redirect Target)**:
   - From Observation 1.1 #1 and Observation 1.2 Suite 1, requesting `/chuong-trinh/` triggers `ltdh_redirect_taxonomy_base()`.
   - The regex `#^/chuong-trinh/?$#i` matches `/chuong-trinh/`, `/chuong-trinh`, and uppercase variations.
   - The destination is `home_url( '/he-dao-tao/' )` and status code is `301`.
   - String assertion confirmed that `/he-dao-tao/tu-xa/` is never produced.
   - Hence, Task 1 is fully satisfied.

2. **Verification of Task 2 (Preservation of Query Parameters)**:
   - From Observation 1.1 #1 and Observation 1.2 Suite 2, when visiting `/chuong-trinh/?truong=utc&nganh=cntt`, `$_GET` is populated with `[ 'truong' => 'utc', 'nganh' => 'cntt' ]`.
   - The logic checks `if ( ! empty( $_GET ) ) { $redirect_url = add_query_arg( $_GET, $redirect_url ); }`.
   - Empirical test 2.1 proved that the redirect destination is `https://lienthongdaihoc.com/he-dao-tao/?truong=utc&nganh=cntt`.
   - Adversarial stress tests (2.2 through 2.6) proved preservation across arrays (`truong[]=utc&truong[]=neu`), Vietnamese percent-encoded keywords (`s=công nghệ thông tin`), and marketing UTM parameters.
   - Hence, Task 2 is fully satisfied.

3. **Verification of Task 3 (Rank Math Canonical Hook)**:
   - From Observation 1.1 #2 and Observation 1.2 Suite 3, `ltdh_seo_enforce_canonical_url` is registered on `rank_math/frontend/canonical` and `rank_math/paper/canonical_url`.
   - When `is_post_type_archive( 'program' )` is active, the function returns `https://lienthongdaihoc.com/he-dao-tao/`.
   - When `REQUEST_URI` matches `/chuong-trinh/` (or with query parameters), the function returns `https://lienthongdaihoc.com/he-dao-tao/`.
   - Crucially, query parameters on `/chuong-trinh/?truong=utc&nganh=cntt` are stripped from the canonical tag, pointing search engine crawlers to the clean base URL `https://lienthongdaihoc.com/he-dao-tao/` per SEO best practices.
   - Singular program posts retain their flat permalink canonicals (`https://lienthongdaihoc.com/cu-nhan-cong-tac-xa-hoi/`).
   - Hence, Task 3 is fully satisfied.

4. **Verification of Task 4 (HTTP 200 without Redirect Loop)**:
   - From Observation 1.1 #3 and Observation 1.2 Suite 4, requesting `/he-dao-tao/`:
     - Does NOT match any redirect conditions in `ltdh_redirect_taxonomy_base()`.
     - Matches `#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i` in `ltdh_template_include_he_dao_tao()`.
     - Executes `status_header( 200 )` and sets `$wp_query->is_404 = false`.
     - Loads `taxonomy-training_type.php` or `archive-program.php`.
   - In test 4.6 (multi-hop simulation):
     - Hop 1: `GET /chuong-trinh/` -> 301 -> Location: `/he-dao-tao/`.
     - Hop 2: `GET /he-dao-tao/` -> HTTP 200 (terminal state, zero further redirects).
     - Canonical on `/he-dao-tao/`: points to `/he-dao-tao/` (self-referential, resolving the canonical loop where previously canonical pointed to a 301 redirecting URL).
   - Hence, Task 4 is fully satisfied.

---

## 3. Caveats

1. **WordPress CLI Sandbox Environment**:
   - Direct execution of `wp` CLI binary via Local by Flywheel was constrained by sandbox filesystem boundaries preventing access to files outside the theme directory (`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-includes`).
   - All tests were executed empirically using dedicated PHP test harnesses (`tests/test-m3-empirical.php` and `tests/test-m3-forensic.php`) running the theme's actual implementation classes and functions under PHP 8.4.19.
2. **ACF JSON Field Sync in WP Admin**:
   - The taxonomy label updates in `inc/acf-import-cpts.json` dynamically register "Hình thức học" via `inc/post-types.php`. If an administrator manages ACF via the WordPress Admin dashboard, syncing via the ACF UI can be done if prompted.

---

## 4. Conclusion

**Verdict: APPROVE**

The implementation of Milestone M3 satisfies 100% of the functional, technical, and adversarial requirements:
1. `/chuong-trinh/` cleanly redirects with HTTP 301 to `/he-dao-tao/` (NOT `/he-dao-tao/tu-xa/`).
2. Query parameters on `/chuong-trinh/?...` are preserved completely upon redirection.
3. Rank Math canonical hook returns `home_url('/he-dao-tao/')` for the program archive and legacy catalog paths, eliminating the canonical redirect loop.
4. `/he-dao-tao/` serves HTTP 200 with catalog template and terminates cleanly with zero redirect loop.
5. All 15 modified files have valid PHP syntax and zero visible occurrences of obsolete "Hệ đào tạo" in public UI templates.

---

## 5. Verification Method

To independently verify these findings, run the following commands from the theme directory:

1. **Run the Empirical Test Suite (38 assertions)**:
   ```bash
   php tests/test-m3-empirical.php
   ```
   *Expected output*: `TEST RESULTS: 38 PASSED, 0 FAILED`

2. **Run the Forensic Code Audit (82 assertions)**:
   ```bash
   php tests/test-m3-forensic.php
   ```
   *Expected output*: `AUDIT SUMMARY: 82 PASSED, 0 FAILED`

3. **Verify PHP Syntax on All Modified Files**:
   ```bash
   php -l inc/config/class-defaults.php
   php -l inc/core/class-menus.php
   php -l inc/core/class-helpers.php
   php -l taxonomy-training_type.php
   php -l archive-program.php
   php -l single-major.php
   php -l single-school.php
   php -l template-parts/compare/program-table.php
   php -l template-parts/compare/program-cards.php
   php -l template-parts/eligibility/wizard.php
   php -l inc/core/class-query-filters.php
   php -l taxonomy.php
   php -l template-parts/banner.php
   php -l inc/core/class-rewrite-rules.php
   php -l inc/seo/class-rankmath-integration.php
   ```
   *Expected output*: `No syntax errors detected` across all 15 files.

4. **Invalidation Conditions**:
   - Any HTTP redirect on `/chuong-trinh/` targeting `/he-dao-tao/tu-xa/`.
   - Any dropped query parameter when accessing `/chuong-trinh/?truong=utc&nganh=cntt`.
   - Any canonical tag on program archives pointing to `/chuong-trinh/`.
   - Any redirect loop triggered when visiting `/he-dao-tao/`.
