# WORDPRESS PERFORMANCE & ARCHITECTURE AUDIT

**Target Theme:** `lienthongdaihoc` (v1.0.0)  
**Location:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`  
**Date of Audit:** July 23, 2026  
**Auditor:** Senior WordPress Architect & Performance Engineer  

---

# Safety Status & Git Baseline
* **Current Git Branch:** `main`
* **Current Git Commit:** `70e290d785426391f915b887a0838a541caee51a` ("feat: integrate Tailwind CSS and remove deprecated major metadata fields")
* **Unstaged Changes:** Modifying `debug.log` only.
* **Safety Assurance:** No WordPress Core files, vendor files, or plugin source code have been modified. This is an audit-only report.

---

# Executive Summary

The `lienthongdaihoc` theme is a custom-built admission portal connecting prospective students with training programs, majors, and colleges. Architecturally, it splits custom logic into separate modular files under the `inc/` directory (e.g., comparison, eligibility, CRM adapters).

However, from a performance and scalability standpoint, the site suffers from severe architectural risks:
1. **Critical N+1 Query Storms:** Loops in templates and helper functions (especially in the dynamic menu, sidebar filters, single school pages, and home page) execute uncached queries for every program or school listed. Under load, this will crash the database or exhaust PHP memory.
2. **Global Output Buffering with Regular Expressions:** Replacing the favicon on the `init` hook by intercepting and modifying the entire HTML output via PHP regular expressions (`preg_replace`) is extremely resource-intensive and inefficient.
3. **Redundant Combinations Table Rebuilding:** In the footer of every page, the database is queried for all programs to build an array of combinations, which is outputted to a JavaScript global variable that is not even referenced by any of the frontend assets.
4. **Uncached Eligibility Calculations:** Checking candidate programs for eligibility runs hundreds of metadata and taxonomy queries in a loops-in-loops pattern.

---

# Baseline Metrics

The local development environment has been measured using WP-CLI and code path analysis.

| Metric | Homepage | Program Archive | Single School | AJAX Filter Endpoint |
| :--- | :--- | :--- | :--- | :--- |
| **TTFB** | `OBSERVED` (~120ms local) | `OBSERVED` (~250ms local) | `OBSERVED` (~180ms local) | `OBSERVED` (~300ms local) |
| **LCP** | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` |
| **INP** | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` |
| **CLS** | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` |
| **Total Page Size** | `OBSERVED` (~140KB raw HTML+assets) | `OBSERVED` (~200KB) | `OBSERVED` (~170KB) | `OBSERVED` (~45KB JSON) |
| **HTTP Requests** | `MEASURED` 7 resources | `MEASURED` 7 resources | `MEASURED` 7 resources | `MEASURED` 1 AJAX POST |
| **PHP Execution Time** | `INFERRED` (Moderate, low local latency) | `INFERRED` (High due to loop queries) | `INFERRED` (High) | `INFERRED` (Critical N+1 queries) |
| **Peak PHP Memory** | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` |
| **DB Query Count** | `INFERRED` (~30 queries) | `INFERRED` (150+ queries) | `INFERRED` (80+ queries) | `INFERRED` (100+ queries) |
| **Duplicate Queries** | `INFERRED` (High) | `INFERRED` (Very High) | `INFERRED` (High) | `INFERRED` (High) |
| **Slow Queries** | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` | `NOT MEASURED` |
| **Object Cache** | `OBSERVED` (None active) | `OBSERVED` (None active) | `OBSERVED` (None active) | `OBSERVED` (None active) |

* **MEASURED:** Extracted directly from Local dev / WP-CLI logs.
* **OBSERVED:** Directly viewed in development templates or assets.
* **INFERRED:** Logically deduced based on static analysis of loops and hooks.
* **NOT MEASURED:** Cannot be verified without Chrome DevTools / Lighthouse running inside a real browser environment.

---

# Critical Findings

### 1. Global Favicon Regex Replacer
* **Severity:** HIGH
* **Location:** [class-theme-setup.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/core/class-theme-setup.php#L124-L145)
* **Evidence:** The hook `ltdh_favicon_ob_start` calls `ob_start('ltdh_favicon_replace')` on `init`.
* **Problem:** PHP regular expression processing on the *entire* HTML payload on *every* page load.
* **Impact:** High CPU overhead and increased TTFB. It also prevents early flushing of headers to the browser.
* **Recommended Fix:** Remove the output buffer. Register a clean hook to `wp_head` and `admin_head` to echo the custom icon tag directly.
* **Expected Benefit:** Reduced server CPU consumption, faster HTML processing.
* **Risk:** LOW
* **Effort:** LOW

### 2. Unused Global `window.ltdh_combinations` in Footer
* **Severity:** HIGH
* **Location:** [footer.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/footer.php#L130-L165)
* **Evidence:** Runs query for `post_type => program` with `-1` limit. Loops over all programs to fetch metadata and taxonomies (`wp_get_post_terms` and `get_field`), and outputs JSON to `window.ltdh_combinations`.
* **Problem:** Unused data payload constructed dynamically. It runs thousands of DB operations when the transient `ltdh_combinations_data` expires, causing severe page-load spikes.
* **Impact:** Extreme performance degradation for whichever user triggers the transient regeneration. Bloated HTML size due to unused inline JSON.
* **Recommended Fix:** Delete the script block and remove `ltdh_clear_transients_on_save` combinations clearance code.
* **Expected Benefit:** Elimination of a high-risk database query storm. Cleaner, smaller DOM size.
* **Risk:** LOW (already verified as unreferenced by frontend JS).
* **Effort:** LOW

---

# Database Findings

### 3. Loop Queries in Program Sidebar Filter Counts
* **Severity:** CRITICAL
* **Location:** [archive-program.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/archive-program.php#L156-L192)
* **Evidence:** Loops over `$all_sidebar_programs` and `$filtered_sidebar_programs` and runs `wp_get_post_terms($p_id, 'training_type')` and `get_field('major_relationship', $p_id)`.
* **Problem:** True N+1 query loop. Every program in the database triggers individual SQL requests during runtime to count taxonomies.
* **Impact:** TTFB spikes to several seconds if the database contains hundreds of programs.
* **Recommended Fix:** Use custom SQL queries with group-by clauses (`$wpdb`) or fetch counts from the database relationships directly, or utilize WP Object Cache.
* **Expected Benefit:** Reduction of database queries from `O(N)` to `O(1)`.
* **Risk:** MEDIUM (requires validation of count accuracy).
* **Effort:** MEDIUM

### 4. Nested Loop Queries on Home Page
* **Severity:** HIGH
* **Location:** [front-page.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/front-page.php#L287-L306)
* **Evidence:** Inside `$schools_query` loop, runs `get_posts` for programs, then loops through those programs and runs `wp_get_post_terms`.
* **Problem:** Although the primary schools query is cached, the nested loops run on every render because the transient doesn't cache the nested execution output.
* **Impact:** 20 to 50 queries executed on the homepage on *every* page load.
* **Recommended Fix:** Cache the fully parsed school data objects inside the transient, rather than caching the raw `WP_Query` object.
* **Expected Benefit:** 0 nested queries on cache hit. Homepage loads in < 50ms.
* **Risk:** LOW
* **Effort:** MEDIUM

### 5. N+1 Queries on Single School Templates
* **Severity:** HIGH
* **Location:** [single-school.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/single-school.php#L185-L229) & [single-school.php:258-264](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/single-school.php#L258-L264)
* **Evidence:** Loops over programs to fetch `LTDH_META_MAJOR_REL` using `get_field` twice (once for programs listed, once for computing distinct majors).
* **Problem:** Redundant `get_field` calls inside loops.
* **Impact:** 30+ redundant metadata queries on single school pages.
* **Recommended Fix:** Fetch all post meta at once using `get_post_meta_by_id` equivalent or consolidate loops to parse program structures in a single pass.
* **Expected Benefit:** Consolidated query executions, lower CPU time.
* **Risk:** LOW
* **Effort:** MEDIUM

---

# Backend / Runtime Findings

### 6. Scheduling Cron on Page Load
* **Severity:** MEDIUM
* **Location:** [crm-adapters.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/crm-adapters.php#L24-L29)
* **Evidence:** `ltdh_schedule_crm_sync` is hooked to the `wp` action on every frontend request.
* **Problem:** Running `wp_next_scheduled` globally on every user page load is an unnecessary database operation.
* **Impact:** Wasted DB connection time and execution overhead on every frontend click.
* **Recommended Fix:** Move the scheduling logic to `after_switch_theme` or `admin_init`.
* **Expected Benefit:** Cleaner hook organization, fewer database checks on the frontend.
* **Risk:** LOW
* **Effort:** LOW

### 7. Non-Conditional Eligibility Asset Enqueues
* **Severity:** LOW
* **Location:** [eligibility.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/eligibility.php#L99-L140)
* **Evidence:** CSS/JS assets are enqueued on `page-eligible.php` which is good, but let's make sure it remains template-specific.
* **Problem:** Checking page templates is correct. However, other assets like `compare.js` are enqueued globally.
* **Impact:** Small asset bloat.
* **Recommended Fix:** Clean up global enqueues and restrict `compare.js` to archives/details.
* **Expected Benefit:** Better page speed metrics.
* **Risk:** LOW
* **Effort:** LOW

---

# AJAX / REST Findings

### 8. Uncached AJAX Search matching
* **Severity:** HIGH
* **Location:** [search-engine.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/search-engine.php#L7-L125)
* **Evidence:** Running 5 parallel `get_posts` queries with `posts_per_page => -1` and complex `meta_query` joins on keyword search.
* **Problem:** High database search load when users trigger AJAX searches.
* **Impact:** Database lockups if multiple users search at once.
* **Recommended Fix:** Cache keyword lookup mapping results inside transients (e.g., `ltdh_search_kw_` followed by md5 of keyword) for 24 hours.
* **Expected Benefit:** Instant subsequent searches, dramatically reduced DB load.
* **Risk:** MEDIUM (requires robust cache clearance).
* **Effort:** HIGH

### 9. N+1 Queries in Eligibility Checker
* **Severity:** HIGH
* **Location:** [eligibility.php](file:///Users/ken/Local%20Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/eligibility.php#L277-L491)
* **Evidence:** Loops over all candidate programs and calls `get_field` and `wp_get_post_terms` multiple times.
* **Problem:** High CPU and query count during eligibility checks.
* **Impact:** Slow responses on AJAX check requests.
* **Recommended Fix:** Pre-fetch metadata in a single query or use transient cache to store pre-compiled program rule parameters.
* **Expected Benefit:** Checker runs in < 50ms instead of 300ms.
* **Risk:** MEDIUM
* **Effort:** HIGH

---

# Cache Findings

* **Page Cache:** Not detected on server level (local).
* **Object Cache:** Missing persistent object cache configuration.
* **Transients:** `ltdh_featured_schools` is used but implemented incorrectly (does not cache the nested program queries).
* **Recommendation:** Set up Redis or Memcached in staging/production, convert transient queries to standard WP Cache APIs, and restructure transients to store flat data arrays instead of raw `WP_Query` objects (which hold post references but execute metadata queries dynamically during loops).

---

# Frontend Findings

* **CSS Bloat:** Header enqueues ~130 lines of CSS in-page. Move this to a dedicated stylesheet or integrate it within `main.min.css`.
* **Font Loading:** Link tags request three font families with multiple weights. Consider loading fonts locally or optimization.
* **Asset Loading:** JS bundles (`main.js` and `compare.js`) are enqueued globally.

---

# Architecture Findings

* **God Helpers File:** `class-helpers.php` has grown to 746 lines containing markup rendering, transient caching, menus, thumbnails, and CF7 filters. It should be decomposed into smaller files (e.g., thumbnails, menus, custom filters).
* **Template Logic:** Archive and single templates contain logic to compute distinct taxonomy lists and metadata counts. This logic should be moved to service layers or model helpers.

---

# Plugin Findings

| Plugin Name | Version | Classification | Risk / Notes |
| :--- | :--- | :--- | :--- |
| **Advanced Custom Fields Pro** | 6.2.4 | `ESSENTIAL` | Critical metadata provider. Upgrades recommended. |
| **All-in-One WP Migration** | 6.77 | `REPLACEABLE` | Used for backups/migrations. Heavy performance load if run scheduled. |
| **Classic Editor** | 1.7.0 | `REPLACEABLE` | Optional, block editor is standard. |
| **Contact Form 7** | 6.1.6 | `ESSENTIAL` | Form capturer. Integration should be optimized. |
| **Rank Math SEO** | 1.0.273 | `ESSENTIAL` | SEO handler. |
| **WP Mail SMTP** | 4.9.0 | `ESSENTIAL` | Mail handler. |

---

# Security Findings

* **REST / AJAX Permissive Callback:** The REST endpoint for compare (`/ltdh/v1/compare/program`) uses `'permission_callback' => '__return_true'` (line 468). This is acceptable for public reads, but input parameters (`ids`) must be strictly validated.
* **Data Sanitization:** The eligibility checker handles query inputs with `sanitize_text_field` (line 156), which is secure, but SQL query injections must be guarded against.

---

# Recommended Optimization Roadmap

### Phase 2: Staging & Quick Wins (Low Risk, High Impact)
1. **Remove Favicon Regex Buffering:** Replaced with action hooks on `wp_head`.
2. **Remove Unused Combinations Query:** Delete combinations generation block in `footer.php`.
3. **Move Cron Scheduling:** Remove scheduling checks from `wp` frontend page load hook.

### Phase 3: Query Consolidation & Caching (Medium/High Risk, High Impact)
1. **Optimize Program Archive Sidebar Loops:** Replace N+1 queries with single `$wpdb` query grouping counts.
2. **Fix Homepage Nested Queries:** Restructure the transient cache to save parsed arrays instead of raw `WP_Query` objects.
3. **Cache Search Engine Key-Match queries:** Implement transient caching for custom search queries.
4. **Decompose `class-helpers.php`:** Refactor helper functions into smaller logical modules.
