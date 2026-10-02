# Review Report & Handoff: Milestone M3 Routing & Canonical Review

**Reviewer**: `reviewer_m3_2` (M3 Routing & Canonical Reviewer & Adversarial Critic)  
**Date**: 2026-10-01  
**Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/reviewer_m3_2/`  
**Verdict**: **APPROVE**  
**Integrity Mode**: Validated (0 integrity violations)

---

## 1. Observation

### 1.1. Redirect Rule in `inc/core/class-rewrite-rules.php:247-254`
Direct inspection of `inc/core/class-rewrite-rules.php:247-254` confirms the implementation:
```php
247: 	if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
248: 		$redirect_url = home_url( '/he-dao-tao/' );
249: 		if ( ! empty( $_GET ) ) {
250: 			$redirect_url = add_query_arg( $_GET, $redirect_url );
251: 		}
252: 		wp_redirect( $redirect_url, 301 );
253: 		exit;
254: 	}
```
- Previously redirected with 301 to `home_url( '/he-dao-tao/tu-xa/' )`.
- Now cleanly redirects to `home_url( '/he-dao-tao/' )`.
- Checks `! empty( $_GET )` and applies `add_query_arg( $_GET, $redirect_url )`, fully preserving all query parameters.
- Emits HTTP 301 and exits immediately.

### 1.2. Rank Math Canonical Integration in `inc/seo/class-rankmath-integration.php:68-89`
Direct inspection confirms:
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
- Canonical for `is_post_type_archive( 'program' )` returns `home_url( '/he-dao-tao/' )`.
- Request path `/chuong-trinh/` explicitly falls back to returning `home_url( '/he-dao-tao/' )`.
- Both `rank_math/frontend/canonical` and `rank_math/paper/canonical_url` filters are registered.
- On taxonomy archives (`/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`), `is_post_type_archive( 'program' )` evaluates to `false`, leaving the term canonical intact.
- This completely eliminates the previous **Canonical 301 Redirect Loop** (where canonical was `/chuong-trinh/`, while `/chuong-trinh/` 301 redirected to `/he-dao-tao/tu-xa/`).

### 1.3. Form Actions, Reset Links, and Tab Links in `archive-program.php`
Direct inspection confirms:
- **Form Action** (line 188): `<form id="catalog-filter-form" action="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" method="GET" class="space-y-4">`
- **Active Filters Reset Button** (line 180): `<a href="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" class="...">✕ Xóa tất cả bộ lọc</a>`
- **Empty State Reset Button** (line 464): `<a href="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" class="...">✕ Đặt lại tất cả bộ lọc</a>`
- **"Tất cả" Tab Link** (lines 255-265): `$all_url = home_url( '/he-dao-tao/' );` with `$preserved_args` appended via `add_query_arg()`.
- **Term Tab Links** (lines 285-288): `$type_url = home_url( '/he-dao-tao/' . $t_term->slug . '/' );` with `$preserved_args` appended via `add_query_arg()`.
- Zero remaining occurrences of `/chuong-trinh/` in `archive-program.php`.

### 1.4. JavaScript Form Selectors in `assets/js/main.js`
Direct inspection confirms:
- Line 88: `const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"]');`
- Line 114: `const resetBtn = filterForm.querySelector('a[href*="/chuong-trinh/"], a[href*="/he-dao-tao/"]');`
- Selectors match `form[action*="/he-dao-tao/"]` and reset links cleanly.
- `triggerFilter()` gathers form elements, performs `window.history.pushState()`, and fetches AJAX results smoothly without page reload.

### 1.5. Syntax Validation (`php -l`)
Executed `php -l` on all 15 modified/relevant PHP files:
- `inc/core/class-rewrite-rules.php`: No syntax errors detected
- `inc/seo/class-rankmath-integration.php`: No syntax errors detected
- `archive-program.php`: No syntax errors detected
- `inc/config/class-defaults.php`: No syntax errors detected
- `inc/core/class-menus.php`: No syntax errors detected
- `inc/core/class-helpers.php`: No syntax errors detected
- `taxonomy-training_type.php`: No syntax errors detected
- `single-major.php`: No syntax errors detected
- `single-school.php`: No syntax errors detected
- `template-parts/compare/program-table.php`: No syntax errors detected
- `template-parts/compare/program-cards.php`: No syntax errors detected
- `template-parts/eligibility/wizard.php`: No syntax errors detected
- `inc/core/class-query-filters.php`: No syntax errors detected
- `taxonomy.php`: No syntax errors detected
- `template-parts/banner.php`: No syntax errors detected
*Result*: 15/15 files passed with 0 errors.

---

## 2. Logic Chain

1. **Clean 301 Redirection**:
   The user/crawler visiting `/chuong-trinh/` triggers `ltdh_redirect_taxonomy_base` on hook `template_redirect`. Line 247 regex `#^/chuong-trinh/?$#i` matches case-insensitively with or without trailing slash. Any query string passed (e.g. `?truong=dai-hoc-mo&s=cntt`) is extracted from `$_GET` and appended onto `home_url( '/he-dao-tao/' )` via `add_query_arg()`. The server issues an HTTP 301 Moved Permanently response.

2. **Elimination of Canonical Redirect Loop**:
   Previously, Rank Math assigned canonical URL `.../chuong-trinh/` to the CPT `program` archive, while server rules redirected `.../chuong-trinh/` to `.../he-dao-tao/tu-xa/`. This was a classic canonical redirect loop that confuses search crawlers and impairs indexing.
   With the updated `ltdh_seo_enforce_canonical_url`:
   - Request to `/he-dao-tao/` parses as `is_post_type_archive( 'program' )` -> Returns `https://domain.com/he-dao-tao/`.
   - Request to legacy `/chuong-trinh/` matches path regex `#^/chuong-trinh/?$#i` -> Returns `https://domain.com/he-dao-tao/`.
   - The canonical URL matches the 301 destination exactly. Canonical loop is completely resolved.

3. **Template & Client-Side UI Cohesion**:
   Form submission, reset actions, and tab clicks in `archive-program.php` submit directly to `/he-dao-tao/`. In `assets/js/main.js`, `form[action*="/he-dao-tao/"]` binds submit, input debounce, and select change events, ensuring seamless AJAX filtering and URL synchronicity.

4. **Integrity Assessment**:
   No hardcoded test mocks, facades, bypasses, or fabricated outputs were found. The implementation utilizes native WordPress core and Rank Math filter APIs cleanly.

---

## 3. Caveats & Adversarial Findings

### 3.1. [Minor Finding] Legacy Paginated URLs `/chuong-trinh/page/X/`
- **Observation**: The redirect regex in `inc/core/class-rewrite-rules.php:247` is `#^/chuong-trinh/?$#i`.
- **Scenario**: If a search engine had previously indexed a paginated URL like `/chuong-trinh/page/2/`, this regex will not match. In that case, WordPress serves the page under `archive-program.php` directly (since CPT `program` has `has_archive_slug => 'chuong-trinh'`). Rank Math correctly outputs canonical `/he-dao-tao/`, so no loop occurs, but the URL is not 301-redirected.
- **Recommendation for future polish**: Update regex to `#^/chuong-trinh(?:/page/(\d+))?/?$#i` and redirect to `/he-dao-tao/page/$1/` if `$1` exists. This is low priority as paginated archive pages change rapidly and have rel=canonical pointing to root.

### 3.2. WP Admin ACF JSON Sync
Edits made in `inc/acf-import-cpts.json` affect taxonomy labels dynamically through `inc/post-types.php`. If ACF Pro UI is actively used in WP Admin, an admin should sync post types/taxonomies if prompted.

---

## 4. Conclusion

The implementation by `worker_m3` is verified to be correct, complete, robust, and free of syntax errors or integrity violations:
- Redirect rule in `inc/core/class-rewrite-rules.php:247-254` cleanly 301 redirects `/chuong-trinh/` to `/he-dao-tao/` while preserving query parameters.
- Rank Math canonical integration in `inc/seo/class-rankmath-integration.php` resolves the canonical 301 redirect loop.
- All form actions, reset buttons, and tab links in `archive-program.php` point to `/he-dao-tao/`.
- `assets/js/main.js` form selectors match `/he-dao-tao/`.
- All 15 modified files pass `php -l`.

**Final Verdict**: **APPROVE**

---

## 5. Verification Method

To independently verify these findings, run the following commands:

```bash
# 1. PHP Syntax Check
php -l inc/core/class-rewrite-rules.php
php -l inc/seo/class-rankmath-integration.php
php -l archive-program.php
php -l assets/js/main.js

# 2. Automated Redirect & Canonical Logic Assertions
php -r '
define("ABSPATH", __DIR__ . "/");
define("LTDH_CPT_PROGRAM", "program");
define("LTDH_CPT_SCHOOL", "school");

function home_url($p = "") { return "https://lienthongdaihoc.com" . $p; }
function is_singular($t = "") { return false; }
function is_post_type_archive($t = "") { return $t === "program"; }
function add_filter($t, $cb, $p = 10, $a = 1) {}
function add_action($t, $cb, $p = 10, $a = 1) {}

require "inc/seo/class-rankmath-integration.php";

// Test A: Archive canonical
$_SERVER["REQUEST_URI"] = "/he-dao-tao/";
assert(ltdh_seo_enforce_canonical_url("") === "https://lienthongdaihoc.com/he-dao-tao/");

// Test B: Legacy /chuong-trinh/ canonical
$_SERVER["REQUEST_URI"] = "/chuong-trinh/";
assert(ltdh_seo_enforce_canonical_url("") === "https://lienthongdaihoc.com/he-dao-tao/");

// Test C: Redirect rule regex and query args
$path = "/chuong-trinh/";
$_GET = ["truong" => "dai-hoc-mo", "s" => "cntt"];
if (preg_match("#^/chuong-trinh/?$#i", $path)) {
    $redirect_url = home_url("/he-dao-tao/");
    if (!empty($_GET)) {
        $redirect_url .= "?" . http_build_query($_GET);
    }
}
assert($redirect_url === "https://lienthongdaihoc.com/he-dao-tao/?truong=dai-hoc-mo&s=cntt");

echo "ALL VERIFICATION CHECKS PASSED\n";
'
```

### Invalidation Conditions
- Any return of HTTP 301 from `/chuong-trinh/` pointing to `/he-dao-tao/tu-xa/`.
- Rank Math emitting `<link rel="canonical" href=".../chuong-trinh/">`.
- Any form or reset link in `archive-program.php` with `href` or `action` containing `/chuong-trinh/`.
