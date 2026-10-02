# Handoff Report: Architecture, Routing, Taxonomy & Query Data Flow Investigation

**Agent**: `explorer_survey_ia_2` (Architecture & Routing Explorer)  
**Date**: 2026-10-01  
**Working Directory**: `.agents/teamwork/explorer_survey_ia_2/`  
**Target Codebase**: `lienthongdaihoc.com` WordPress Theme  

---

## 1. Observation

### 1.1. CPT & Taxonomy Registration Architecture (Scope 1)
- **Source of Truth**: `inc/post-types.php:15-108` executes on hook `init` (priority 0) and dynamically parses `inc/acf-import-cpts.json`.
- **Core CPTs (`inc/acf-import-cpts.json`)**:
  1. `school` (`lines 3-57`):
     - `label` / `singular_label`: `"Trường đối tác"`.
     - `has_archive`: `true`, `has_archive_slug`: `"truong-doi-tac"`.
     - `rewrite`: `true`, `rewrite_slug`: `"truong-doi-tac"`.
     - `taxonomies`: `["region"]`.
  2. `major` (`lines 59-111`):
     - `label` / `singular_label`: `"Ngành học"`.
     - `has_archive`: `true`, `has_archive_slug`: `"nganh-hoc"`.
     - `rewrite`: `true`, `rewrite_slug`: `"nganh-hoc"`.
     - `taxonomies`: `[]`.
  3. `program` (`lines 113-168`):
     - `label` / `singular_label`: `"Chương trình đào tạo"`.
     - `has_archive`: `true`, `has_archive_slug`: `"chuong-trinh"`.
     - `rewrite`: `true`, `rewrite_slug`: `""` (Empty string; single post URLs are intercepted and flattened by `inc/core/class-rewrite-rules.php:41-45`).
     - `taxonomies`: `["training_type", "campus"]`.
  4. Supplementary CPT `guide` (`inc/post-types.php:110-128`):
     - Declared if `LTDH_CPT_GUIDE` is defined and post type does not exist. Archive slug: `'cam-nang'`, rewrite slug: `'huong-dan'`.
- **Taxonomies (`inc/acf-import-cpts.json` & `inc/config/constants.php`)**:
  1. `training_type` (`inc/acf-import-cpts.json:170-208`):
     - `taxonomy`: `"training_type"`.
     - `object_type`: `["program", "school"]` (Bound to both `program` and `school`).
     - `label` / `singular_label`: `"Hệ đào tạo"`.
     - `hierarchical`: `true`.
     - `rewrite`: `true`, `rewrite_slug`: `"he-dao-tao"`.
  2. `campus` (`inc/acf-import-cpts.json:210-246`):
     - `taxonomy`: `"campus"`.
     - `object_type`: `["program", "school"]`.
     - `label` / `singular_label`: `"Cơ sở đào tạo"`.
     - `hierarchical`: `true`.
     - `rewrite`: `true`, `rewrite_slug`: `"co-so"`.
  3. `region` (`inc/acf-import-cpts.json:248-283`):
     - Bound to `["school"]`. Rewrite slug: `"khu-vuc"`.
  4. `major_cat` (`inc/acf-import-cpts.json:285-320`):
     - Bound to `["major"]`. Rewrite slug: `"nhom-nganh"`.
- **ACF Relationships & Bi-directional Sync**:
  - `inc/acf-import-fields.json:332-356` defines `school_relationship` (post_object -> `school`) and `major_relationship` (post_object -> `major`) on `program`.
  - `inc/relationship-hooks.php:14-74` hooks `acf/save_post` (priority 20) on CPT `program` and synchronizes program IDs into the post meta `_offered_programs` on the linked `school` and `major`.

---

### 1.2. Taxonomy Labels and Usages of "Hệ đào tạo" (Scope 2)
The string "Hệ đào tạo" is pervasive across 15+ files:
1. **JSON Registration**: `inc/acf-import-cpts.json:171, 178, 179, 196-206` (Title, labels, menu names).
2. **Navigation Fallbacks**: `inc/config/class-defaults.php:37`:
   ```php
   [ 'url' => '/he-dao-tao/', 'label' => 'Hệ đào tạo' ],
   ```
3. **Dynamic Menu Submenu Injection**: `inc/core/class-menus.php:137`:
   ```php
   $title = mb_strtolower(trim($item->title), 'UTF-8');
   if ($title === 'hệ đào tạo') { ... }
   ```
   *Observation*: If the menu label is renamed to "Hình thức học" without updating this check, dynamic injection of training type submenu items silently fails.
4. **Breadcrumb Trail Engine**: `inc/core/class-helpers.php:432, 449, 459, 469, 472`:
   ```php
   $crumbs[] = [ 'label' => 'Hệ đào tạo', 'url' => home_url( '/he-dao-tao/' ) ];
   ```
5. **Catalog & Taxonomy Templates**:
   - `taxonomy-training_type.php:172`:
     ```php
     <?php echo $active_type_term ? 'Hệ đào tạo: ' . esc_html( $active_type_term->name ) : 'Tất cả chương trình đào tạo'; ?>
     ```
   - `taxonomy-training_type.php:250`:
     ```html
     <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Hệ đào tạo:</span>
     ```
   - `archive-program.php:172, 250`: Identical markup to `taxonomy-training_type.php`.
   - `template-parts/banner.php:90`: `$banner_title = 'Hệ đào tạo: ' . $term->name;`.
   - `taxonomy.php:26, 62, 197`: `<h2 ...>Hệ đào tạo</h2>`, `<p>Hệ đào tạo: ...</p>`.
6. **Single Entity & Compare Views**:
   - `single-major.php:470`: `<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hệ đào tạo:</div>`.
   - `single-school.php:501`: `<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hệ đào tạo:</div>`.
   - `template-parts/compare/program-table.php:66`: `'label' => 'Hệ đào tạo'`.
   - `template-parts/compare/program-cards.php:22`: `['label' => 'Hệ đào tạo', 'key' => 'training_type']`.
   - `template-parts/eligibility/wizard.php:76`: `<label class="block text-sm font-bold text-slate-700">Hệ đào tạo mong muốn</label>`.
   - `inc/core/class-query-filters.php:236`: `<span ...> Hệ <?php echo esc_html( $t_name ); ?> </span>`.

---

### 1.3. Routing, Rewrite Rules & The `/chuong-trinh/` 301 Redirect (Scope 3)
1. **The 301 Redirect in Code**:
   In `inc/core/class-rewrite-rules.php:247-254`, inside `ltdh_redirect_taxonomy_base()` hooked on `template_redirect`:
   ```php
   if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
       $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
       if ( ! empty( $_GET ) ) {
           $redirect_url = add_query_arg( $_GET, $redirect_url );
       }
       wp_redirect( $redirect_url, 301 );
       exit;
   }
   ```
2. **Coexisting Discrepancies**:
   - `archive-program.php` still uses `/chuong-trinh/` for all form submissions and reset links:
     - Line 180: `<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" ...>✕ Xóa tất cả bộ lọc</a>`
     - Line 188: `<form id="catalog-filter-form" action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET" class="space-y-4">`
     - Line 255: `$all_url = home_url( '/chuong-trinh/' );`
     - Line 464: `<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" ...>`
   - `assets/js/main.js:88, 114` attaches event listeners matching `form[action*="/chuong-trinh/"]`.
   - Rank Math SEO canonical tags:
     - On the CPT `program` archive, Rank Math defaults to `https://domain.com/chuong-trinh/` because of `"has_archive_slug": "chuong-trinh"`.
     - When crawlers visit the canonical URL `/chuong-trinh/`, server sends HTTP 301 redirecting to `/he-dao-tao/tu-xa/`.
     - This creates a **Canonical Redirect Loop / Soft 404 signal** on the primary program archive.
3. **Flawed Redirect Target**:
   Redirecting `/chuong-trinh/` (all programs) to `/he-dao-tao/tu-xa/` forces users and search engines to assume the entire site offers only "Từ xa", hiding "Vừa học vừa làm" and breaking general catalog browsing.

---

### 1.4. Data Flow and Query Logic (Scope 4)
1. **`single-school.php`**:
   - Lines 270-323: Queries distinct majors by inspecting `major_relationship` on programs retrieved from `_offered_programs` (or fallback meta query `school_relationship = $school_id`).
   - Lines 365-404: Queries programs for section "Chương trình tuyển sinh đang mở" (`#chuong-trinh-tuyen-sinh`).
     - *Observation A*: Programs are retrieved purely by relationship ID without checking whether the program belongs to Liên thông or another training type.
     - *Observation B*: The fallback query at line 391 omits `'post_status' => 'publish'` and caps results arbitrarily at `'posts_per_page' => 10`.
2. **`archive-school.php`**:
   - Lines 86 & 302: Calls `ltdh_get_school_training_types( $school_id )`.
   - In `inc/core/class-helpers.php:948-953`:
     ```php
     // 1. Check if school has directly assigned taxonomy terms
     $terms = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => $output_format ] );
     if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
         wp_cache_set( $cache_key, $terms, 'ltdh', HOUR_IN_SECONDS );
         return $terms;
     }
     // 2. Otherwise rollup from its published programs
     ```
     *Direct Violation of Requirement R2*: The function prioritizes static terms manually checked on the School edit screen in WP Admin rather than rolling up from actual active Liên thông programs. If a school has legacy terms attached (e.g. Chính quy, VB2) but only offers Liên thông Từ xa, the badges on the school card render false information.
3. **`single-major.php`**:
   - Lines 348-373: Queries programs linked to `major_id` via `_offered_programs` or fallback `major_relationship = $major_id`.
   - Fallback query limits to 10 programs (`'posts_per_page' => 10`).
   - Does not verify if the programs belong to allowed Liên thông `training_type` terms.
4. **`taxonomy-training_type.php` & `archive-program.php`**:
   - Lines 119-133: Training type count query in SQL counts across the entire table without respecting school or major filters:
     ```sql
     SELECT t.slug, COUNT(p.ID) as count
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
     INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
     INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
     WHERE p.post_type = 'program' AND p.post_status = 'publish' AND tt.taxonomy = 'training_type'
     GROUP BY t.slug
     ```
     *Result*: Produces "Phantom Facets" where the tabs show counts that yield 0 results when filtered.
5. **`inc/core/class-query-filters.php`**:
   - `ltdh_customize_archive_queries()` sets `post_type => program` on `is_tax( 'training_type' )`.
   - `ltdh_ajax_filter_programs()` filters programs by `truong`, `nganh`, and `he`. It does not enforce that programs are restricted to valid Liên thông scope.

---

### 1.5. `campus` Taxonomy Usage & `Online` Isolation (Scope 5)
1. **How `Online` Was Introduced**:
   - `inc/cli-commands.php:119` seeded `'Online' => 'online'` into the `campus` taxonomy:
     ```php
     $campuses = [
         'Hà Nội'     => 'ha-noi',
         'Hồ Chí Minh'=> 'ho-chi-minh',
         'Đà Nẵng'    => 'da-nang',
         'Thái Nguyên'=> 'thai-nguyen',
         'Online'     => 'online',
     ];
     ```
   - In `inc/acf-import-fields.json:930`, the eligibility configuration choices contain `"online": "Online"`.
2. **Where `Online` Renders as a Physical Campus**:
   - `inc/core/class-helpers.php:730-732` (`ltdh_get_program_learning_details()`):
     ```php
     $campuses    = wp_get_post_terms($program_id, LTDH_TAX_CAMPUS);
     $campus_name = ! empty($campuses) && ! is_wp_error($campuses) ? implode(', ', wp_list_pluck($campuses, 'name')) : 'Hà Nội';
     ```
     When a program has `Online` attached in `campus`, `$campus_name` evaluates to `"Online"` or `"Hà Nội, Online"`.
   - `single-program.php:266-268`:
     ```html
     <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cơ sở học</span>
     <span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $learning_details['campus'] ); ?></span>
     ```
     Renders "Online" under physical location ("Cơ sở học").
   - `taxonomy.php:65, 200`:
     `<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( $learning_details['campus'] ); ?></span></p>`.
   - `inc/comparison.php:168, 216`: Implodes `campus` terms into the table column "Cơ sở / Trạm đào tạo".
3. **Where Isolation Was Incompletely Hardcoded**:
   - `template-parts/eligibility/wizard.php:99-101`:
     ```php
     if ( $cp->slug === 'online' ) {
         continue;
     }
     ```
     Only the wizard dropdown excludes `online`, proving the team recognized the semantic collision but did not isolate it globally.
4. **Syntax Error Discovered**:
   - `taxonomy.php:220` contains corrupted markup:
     `<a href="<"'?php the_permalink(); ?>"'>" class="bg-brand-accent...`

---

## 2. Logic Chain

```
[Observation 1.1]
CPT `program` has archive slug `chuong-trinh` in acf-import-cpts.json.
Taxonomy `training_type` has rewrite slug `he-dao-tao` and is attached to both `program` and `school`.
                     │
                     ▼
[Observation 1.3]
In class-rewrite-rules.php, `/chuong-trinh/` is hard-redirected via 301 to `/he-dao-tao/tu-xa/`.
However, `archive-program.php` filter form targets `/chuong-trinh/`, and Rank Math generates canonical for `/chuong-trinh/`.
                     │
                     ▼
[Logical Step 1: Routing Conflict]
1. Rank Math issues Canonical: `/chuong-trinh/`.
2. Browser/Googlebot requests `/chuong-trinh/` -> Server redirects 301 to `/he-dao-tao/tu-xa/`.
3. `/he-dao-tao/tu-xa/` is only a sub-category ("Từ xa"), excluding "Vừa học vừa làm".
4. When a user on `archive-program.php` clicks "Xóa tất cả bộ lọc", the page requests `/chuong-trinh/` and gets redirected back into "Từ xa".
Conclusion on Routing: The forced 301 from `/chuong-trinh/` to `/he-dao-tao/tu-xa/` is structurally flawed.
                     │
                     ▼
[Observation 1.2]
"Hệ đào tạo" is hardcoded across 15+ template and logic files, including dynamic menu injection (`class-menus.php:137`).
                     │
                     ▼
[Logical Step 2: Taxonomy Label Normalization]
To change frontend display to "Hình thức học" without breaking SEO:
- Preserve URL slug `/he-dao-tao/` and taxonomy key `training_type`.
- Update frontend strings in templates, breadcrumbs, banners, and filters.
- Update `class-menus.php:137` to recognize `'hình thức học'` so child menu items continue to inject dynamically.
                     │
                     ▼
[Observation 1.4]
`archive-school.php` calls `ltdh_get_school_training_types()`, which checks direct taxonomy terms on `school` before checking actual programs.
`single-school.php` and `single-major.php` query programs by relationship ID without verifying `post_status` or Liên thông scope.
                     │
                     ▼
[Logical Step 3: Data Flow Flaws]
1. If direct terms on `school` are trusted, schools advertise training formats for which no actual admission programs exist.
2. If non-Liên thông programs (Chính quy, VB2) exist in database with `publish` status, `single-school.php` and `single-major.php` render them unconditionally.
Conclusion on Data Flow: Queries must be strictly grounded in actual published Liên thông programs (rolled up from programs, not static terms on school).
                     │
                     ▼
[Observation 1.5]
`Online` was seeded as a `campus` term (`inc/cli-commands.php:119`), and `ltdh_get_program_learning_details()` formats it as a physical location.
                     │
                     ▼
[Logical Step 4: Campus Isolation]
1. Physical location (`campus`) and delivery format (`training_type`) are orthogonal.
2. "Online" is a training format (`tu-xa`), never a geographic campus.
3. Rendering "Online" under "Cơ sở học" alongside "Hà Nội" or "TP.HCM" misleads applicants.
Conclusion on Campus: The term `online` must be filtered out from physical campus displays and isolated to delivery mode attributes.
```

---

## 3. Caveats
1. **Read-Only Scope**: No database records or source code files were modified during this investigation.
2. **Database Term Audit**: Terminal command execution for WP-CLI was restricted by the local sandbox environment. The exact count of records currently carrying `campus = online` or non-Liên thông taxonomy terms in the active MySQL database must be determined by the subsequent audit script task (`R1`).
3. **Custom WP Admin Menus**: If a WordPress administrator has created a static custom menu in `wp-admin/nav-menus.php` rather than using the theme's fallback menus, those menu item labels and URLs live in `wp_posts` / `wp_term_relationships` and must be reviewed via WP Admin or WP-CLI.

---

## 4. Conclusion & Actionable Recommendations

### 4.1. CPT & Taxonomy Architecture Normalization
1. **Preserve Core 3 CPTs**:
   - `school`, `major`, `program`. Do not register any additional post types (`course`, `intake`, etc.).
2. **Taxonomy Object Type Realignment**:
   - In `inc/acf-import-cpts.json:174-177`: Restrict `training_type` to `object_type: ["program"]`. A school should derive its training formats exclusively from its active programs.
   - Restrict `campus` to `object_type: ["program"]` or enforce that only physical locations (Hà Nội, TP.HCM, Đà Nẵng, v.v.) are assigned.

### 4.2. Frontend Terminology: "Hệ đào tạo" → "Hình thức học"
1. **Labels to Update**:
   - `inc/acf-import-cpts.json:171, 178, 179, 196-206`: Change all label strings to `"Hình thức học"`.
   - `inc/config/class-defaults.php:37`: Change label to `'Hình thức học'`, keep URL `'/he-dao-tao/'`.
   - `inc/core/class-menus.php:137`: Change detection logic to:
     ```php
     if ( in_array( $title, [ 'hệ đào tạo', 'hình thức học', 'hình thức đào tạo', 'liên thông' ], true ) )
     ```
   - `inc/core/class-helpers.php:432, 449, 459, 469, 472`: Change breadcrumb labels to `'Hình thức học'`.
   - `taxonomy-training_type.php:172, 250` & `archive-program.php:172, 250`: Change display headings and filter pill labels from "Hệ đào tạo" to "Hình thức học".
   - `single-major.php:470`, `single-school.php:501`: Update card row labels to "Hình thức học:".
   - `template-parts/compare/program-table.php:66`, `program-cards.php:22`, `wizard.php:76`: Update to "Hình thức học".
   - `inc/core/class-query-filters.php:236`: Remove redundant "Hệ " prefix in card badge.
2. **Preserve Slugs**:
   - Slug `/he-dao-tao/`, term slugs `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/` must be retained 100% to protect indexed Google ranking.

### 4.3. Clean Route Cleanup for `/chuong-trinh/`
1. **Recommended Strategy (Catalog Hub Redirect)**:
   - In `inc/core/class-rewrite-rules.php:247-254`:
     Update the redirect rule so `/chuong-trinh/` redirects 301 to `home_url( '/he-dao-tao/' )` (the complete Hình thức học directory) instead of the narrow sub-term `/he-dao-tao/tu-xa/`. Preserve all query parameters (`$_GET`).
   - In `archive-program.php`:
     Update all internal action targets (lines 180, 188, 255, 464) from `home_url( '/chuong-trinh/' )` to `home_url( '/he-dao-tao/' )`.
   - In `inc/seo/class-rankmath-integration.php`:
     Add a canonical filter for `is_post_type_archive( 'program' )` returning `home_url( '/he-dao-tao/' )` to eliminate the Canonical Redirect Loop.
   - In `assets/js/main.js`: Ensure form selectors match `form[action*="/he-dao-tao/"]`.

### 4.4. Standardized Data Flow & Queries
1. **Fix `ltdh_get_school_training_types()` (`inc/core/class-helpers.php:937-990`)**:
   - Remove Step 1 (reading terms directly from `school`).
   - Derive training types solely from published, in-scope programs linked via `school_relationship = $school_id` and filtered by allowed Liên thông terms (`tu-xa`, `vua-hoc-vua-lam`).
2. **Fix `single-school.php:365-404` and `single-major.php:348-373`**:
   - Ensure all queries enforce `'post_status' => 'publish'`.
   - Filter programs by `training_type` taxonomy terms matching allowed Liên thông formats (`tu-xa`, `vua-hoc-vua-lam`).
   - Remove the hardcoded `'posts_per_page' => 10` limitation on fallback queries or provide proper pagination.
3. **Fix `taxonomy.php:220`**:
   - Fix corrupted link syntax `<a href="<"'?php the_permalink(); ?>"'>"`.

### 4.5. Complete Isolation of `Online` in `campus`
1. **Helper Function Isolation (`inc/core/class-helpers.php:729-748`)**:
   - In `ltdh_get_program_learning_details()`:
     Filter out any term where `slug === 'online'` or `name === 'Online'` from `$campuses`.
   - If physical campuses exist: display them (e.g. "Hà Nội, TP.HCM").
   - If no physical campuses exist and training type is `tu-xa`: display "Toàn quốc" or "Trực tuyến toàn quốc".
   - Under "Hình thức học" (`mode`): display "Học online 100%".
2. **Comparison & Single Program Isolation**:
   - Update `inc/comparison.php:168` and `single-program.php:267` to consume the sanitized campus output.
3. **Database Migration Note**:
   - In Phase 2 implementation, run a migration script to detach the `online` campus term from all programs and schools.

---

## 5. Verification Method

### 5.1. Syntax & Static Lint Verification
Run PHP syntax checks across all audited files:
```bash
php -l inc/post-types.php
php -l inc/core/class-rewrite-rules.php
php -l inc/core/class-helpers.php
php -l inc/core/class-menus.php
php -l inc/core/class-query-filters.php
php -l single-school.php
php -l archive-school.php
php -l single-major.php
php -l single-program.php
php -l archive-program.php
php -l taxonomy-training_type.php
php -l taxonomy.php
```

### 5.2. Routing & HTTP 301 Verification
Test with `curl` or automated HTTP checks once implemented:
1. `curl -I https://domain.test/chuong-trinh/`
   - **Expected**: `HTTP/1.1 301 Moved Permanently` -> `Location: https://domain.test/he-dao-tao/` (NOT `/he-dao-tao/tu-xa/`).
2. `curl -I https://domain.test/chuong-trinh/?truong=utc`
   - **Expected**: `Location: https://domain.test/he-dao-tao/?truong=utc`.
3. `curl -s https://domain.test/he-dao-tao/ | grep 'canonical'`
   - **Expected**: `<link rel="canonical" href="https://domain.test/he-dao-tao/" />` (No redirect loop).

### 5.3. Frontend Label & Campus Isolation Spot-Checks
1. **Inspect `/he-dao-tao/` and `/he-dao-tao/tu-xa/`**:
   - Verify heading and quick tabs render "Hình thức học" instead of "Hệ đào tạo".
   - Verify breadcrumb displays `Trang chủ > Hình thức học > Từ xa`.
2. **Inspect Single Program with 100% Online delivery**:
   - Verify field "Cơ sở học" displays "Toàn quốc" (or actual physical training station), NOT "Online".
   - Verify field "Hình thức học" displays "Học online 100%".
3. **Inspect School Single & Archive Cards**:
   - Verify badges render only actual formats offered by linked active programs.
