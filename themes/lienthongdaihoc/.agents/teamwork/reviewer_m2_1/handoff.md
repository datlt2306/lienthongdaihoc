# Handoff Report: Milestone M2 Review & Adversarial Audit

**Agent**: `reviewer_m2_1` (M2 Data Flow & Query Reviewer)  
**Role**: Reviewer & Adversarial Critic  
**Date**: 2026-10-01  
**Target Work Product**: Milestone M2 Changes by `worker_m2`  
**Verdict**: **APPROVE**  

---

## 1. Observation

### 1.1. Core CPT Preservation
- Inspected `inc/post-types.php:15-108` and `inc/acf-import-cpts.json:1-170`.
- The system registers exactly three custom post types for the core data model:
  1. `school` (Trường đối tác - `inc/acf-import-cpts.json:6`)
  2. `major` (Ngành học - `inc/acf-import-cpts.json:62`)
  3. `program` (Chương trình đào tạo - `inc/acf-import-cpts.json:116`)
- `git status` and `git diff inc/post-types.php` confirm no modifications to post type registration. Zero new CPTs were introduced.

### 1.2. School Training Types Rollup in `inc/core/class-helpers.php`
- Inspected `inc/core/class-helpers.php:1009-1107` (`ltdh_get_school_training_types()`):
  - **Elimination of Step 1**: The legacy check `wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, ... )` has been completely deleted.
  - **Rollup Exclusivity**: Line 1020 defines `$allowed_slugs = [ 'tu-xa', 'vua-hoc-vua-lam' ];`.
  - **Query Constraints**: Lines 1023–1044 execute `get_posts()` with:
    - `'post_type' => 'program'`
    - `'posts_per_page' => -1`
    - `'post_status' => 'publish'`
    - `'fields' => 'ids'`
    - `'no_found_rows' => true`
    - `'meta_query'` on `'school_relationship' = $school_id`
    - `'tax_query'` on `LTDH_TAX_TRAINING_TYPE` with terms in `['tu-xa', 'vua-hoc-vua-lam']`
  - **Fallback Query**: Lines 1049–1069 fall back to `_offered_programs` if the relationship meta returns empty, maintaining `'post_status' => 'publish'` and `'tax_query'`.
  - **Term Filtering & Formatting**: Lines 1076–1106 iterate terms, discarding any non-object or terms not in `$allowed_slugs`, with support for `'names'`, `'slugs'`, and `'terms'`/`'objects'`.
  - **Caching**: Handled via `wp_cache_get()` and `wp_cache_set( $cache_key, ..., 'ltdh', HOUR_IN_SECONDS )`.

### 1.3. Program Queries in `single-school.php` and `single-major.php`
- Inspected `single-school.php:366-423`:
  - Enforces `'post_status' => 'publish'` (line 390 and 407).
  - Enforces `tax_query` filtering for `['tu-xa', 'vua-hoc-vua-lam']` via `$tax_training_type_filter` (lines 379–384, applied in line 394 and 411).
  - Sets `'posts_per_page' => -1` and `'no_found_rows' => true`.
  - When `$offered_program_ids` yields no active posts, line 403 gracefully triggers the relationship fallback query (`LTDH_META_SCHOOL_REL = $school_id`) with the exact same filters.
- Inspected `single-major.php:348-392`:
  - Enforces `'post_status' => 'publish'` (line 359 and 376).
  - Enforces `tax_query` filtering for `['tu-xa', 'vua-hoc-vua-lam']` via `$tax_training_type_filter` (lines 348–353, applied in line 363 and 380).
  - Sets `'posts_per_page' => -1` and `'no_found_rows' => true`.
  - Triggers fallback query on line 372 (`LTDH_META_MAJOR_REL = $major_id`) if `$offered_program_ids` yields no posts.

### 1.4. Campus "Online" Isolation & Taxonomy Syntax Fix
- Inspected `inc/core/class-helpers.php:729-785` (`ltdh_get_program_learning_details()`):
  - Strips terms with slug or name `'online'` (case-insensitive) from physical campuses.
  - Returns `'Toàn quốc'` when no physical campus is specified for `'tu-xa'`.
  - Maps mode cleanly to `'Học online 100%'` or `'Học tập trung / Cuối tuần'`.
- Inspected `single-program.php:267-273` and `inc/comparison.php:170-180`: Both guard against "Online" displaying under physical campus.
- Inspected `taxonomy.php:220`: Corrected to valid `<a href="<?php the_permalink(); ?>" class="...">Đăng ký học</a>`.

---

## 2. Logic Chain

1. **CPT Preservation**:
   - The original mandate strictly dictates preserving `school`, `major`, and `program` without adding auxiliary CPTs.
   - Observation 1.1 proves that zero new CPT definitions or calls to `register_post_type` were introduced.

2. **Rollup Data Flow**:
   - Previously, schools could show stale badges if terms were assigned to the school post directly.
   - Observation 1.2 proves that direct school terms are no longer checked. Badges are now generated dynamically by aggregating terms from active published programs under the school that match in-scope slugs (`tu-xa`, `vua-hoc-vua-lam`).
   - Drafted, out-of-scope programs (such as those isolated in M1) are excluded by both `post_status => publish` and the taxonomy filter.

3. **Query Consistency & Safety on Single Templates**:
   - Program sections in `single-school.php` and `single-major.php` previously lacked `post_status` and `tax_query` checks in fallback branches, risking leakage of drafted or out-of-scope programs.
   - Observation 1.3 confirms that both primary (`post__in`) and fallback queries enforce `'post_status' => 'publish'`, `'tax_query' => ['tu-xa', 'vua-hoc-vua-lam']`, and `'posts_per_page' => -1`.
   - Any drafted program is safely filtered out at the SQL query level.

4. **Integrity & Syntax**:
   - No dummy/facade implementations or hardcoded mock data were found.
   - Static analysis of the AST and syntax across all modified files (`inc/core/class-helpers.php`, `single-school.php`, `single-major.php`, `single-program.php`, `inc/comparison.php`, `taxonomy.php`) confirms zero syntax errors.

---

## 3. Caveats

- **No Caveats**: All code modifications remain strictly within the boundaries of Milestone M2. No core files outside M2 scope were altered.

---

## 4. Quality Review Report

### Review Summary
**Verdict**: **APPROVE**

### Findings
- **Positive Finding (Resilience)**: Both `single-school.php` and `single-major.php` implement a dual-pass query logic (`if ( empty( $programs_query ) || ! $programs_query->have_posts() )`). If `_offered_programs` contains IDs of programs that were drafted in M1, the query automatically falls back to relationship matching with active `publish` status and in-scope taxonomy terms.
- **Positive Finding (Performance)**: In `single-school.php:428-429`, `update_meta_cache()` and `update_object_term_cache()` are called immediately after querying, mitigating N+1 database queries.

### Verified Claims
1. Exactly 3 core CPTs preserved, 0 new CPTs created → **PASS** (verified via `inc/post-types.php`, `acf-import-cpts.json`, and git diff).
2. Step 1 (direct school taxonomy term check) eliminated in `ltdh_get_school_training_types()` → **PASS** (verified in `inc/core/class-helpers.php:1009-1107`).
3. Single templates query enforcement (`post_status => publish`, `tax_query => ['tu-xa', 'vua-hoc-vua-lam']`) → **PASS** (verified in `single-school.php:366-423` and `single-major.php:348-392`).
4. PHP syntax validity → **PASS** (verified across all 6 modified files).

---

## 5. Adversarial Audit & Stress-Test Report

### Risk Assessment
**Overall Risk Assessment**: **LOW**

### Adversarial Challenges & Edge-Case Evaluation

1. **Integrity Violation Analysis**:
   - *Check*: Did the worker hardcode query results or create facades?
   - *Result*: **NONE**. All logic utilizes genuine WordPress query structures (`WP_Query`, `get_posts`, `wp_get_post_terms`) and dynamic term validation.

2. **Draft / Private Post Leakage Stress-Test**:
   - *Scenario*: An out-of-scope program (e.g. ID 2013) is still listed in `_offered_programs` post meta of a school.
   - *Behavior*: `single-school.php` sets `'post_status' => 'publish'`. WP_Query excludes non-published posts from the output. If all programs in `_offered_programs` are draft, the fallback query runs with the same filter.
   - *Result*: **PASS**.

3. **Delivery Mode vs Physical Facility Ambiguity ("Online")**:
   - *Scenario*: A program is tagged with `campus = 'Online'`.
   - *Behavior*: `ltdh_get_program_learning_details()` ignores `'online'` terms for physical campus, returning `'Toàn quốc'` if training type is `tu-xa`, and reports `'Học online 100%'` under `mode`. `single-program.php` and `inc/comparison.php` apply secondary sanitation.
   - *Result*: **PASS**.

4. **Empty Offerings Handling**:
   - *Scenario*: A school or major has zero active published programs.
   - *Behavior*: Returns `[]` or 0 without PHP notices/warnings; template renders clean fallback notice.
   - *Result*: **PASS**.

---

## 6. Verification Method

To independently verify this review:
1. Inspect git changes:
   ```bash
   git diff inc/core/class-helpers.php single-school.php single-major.php
   ```
2. Verify PHP syntax on modified files:
   ```bash
   php -l inc/core/class-helpers.php
   php -l single-school.php
   php -l single-major.php
   php -l single-program.php
   php -l inc/comparison.php
   php -l taxonomy.php
   ```
3. Verify CPT definitions:
   Inspect `inc/post-types.php` and `inc/acf-import-cpts.json` to confirm only `school`, `major`, and `program` are defined.
