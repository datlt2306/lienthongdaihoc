# Handoff Report: Milestone M2 — Core CPTs, Data Flow & Campus Isolation

**Worker**: `worker_m2` (Data Flow & Campus Isolation Worker)  
**Date**: 2026-10-01  
**Working Directory**: `.agents/teamwork/worker_m2/`  
**Target Codebase**: `lienthongdaihoc.com` WordPress Theme  

---

## 1. Observation

### 1.1. Core CPT Preservation
- Verified `inc/post-types.php:15-108` and `inc/acf-import-cpts.json`. Exactly 3 core CPTs exist: `school`, `major`, `program`.
- Zero new CPTs registered or created during this milestone.

### 1.2. `inc/core/class-helpers.php`
- **Helper `ltdh_get_school_training_types( $school_id, $output_format )` (previously lines 948–990)**:
  - Step 1 previously checked:
    ```php
    $terms = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => $output_format ] );
    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        wp_cache_set( $cache_key, $terms, 'ltdh', HOUR_IN_SECONDS );
        return $terms;
    }
    ```
    This allowed stale/static terms assigned directly to the school post in WP Admin to override actual offered programs.
  - Step 2 previously had `'posts_per_page' => 100` and lacked a `tax_query` filtering for in-scope training types (`tu-xa`, `vua-hoc-vua-lam`).
- **Helper `ltdh_get_program_learning_details( $program_id )` (previously lines 729–748)**:
  - Previously implemented as:
    ```php
    $campuses    = wp_get_post_terms($program_id, LTDH_TAX_CAMPUS);
    $campus_name = ! empty($campuses) && ! is_wp_error($campuses) ? implode(', ', wp_list_pluck($campuses, 'name')) : 'Hà Nội';
    ```
    When a program was tagged with the campus term `Online`, `$campus_name` rendered as `"Online"` under physical location ("Cơ sở học").
  - Mode map previously returned `'Học tập trung cuối tuần'` or fallback `'Học tập trung'`.
- **Helper `ltdh_get_school_unique_majors_count( $school_id )` (previously lines 805–818)**:
  - Capped query at 100 posts without filtering for allowed training types.

### 1.3. `single-school.php` and `single-major.php`
- In `single-school.php:365-404`:
  - When `$offered_program_ids` was present, `'posts_per_page'` was omitted (defaulting to 10), and `tax_query` for training type was missing.
  - In the fallback branch (`else`), `'post_status' => 'publish'` was omitted, `'posts_per_page' => 10` arbitrarily limited offerings, and `tax_query` was missing.
- In `single-major.php:348-373`:
  - When `$offered_program_ids` was present, `'posts_per_page'` was omitted (defaulting to 10), and `tax_query` for training type was missing.
  - In the fallback branch (`else`), `'post_status' => 'publish'` was omitted, `'posts_per_page' => 10` arbitrarily limited offerings, and `tax_query` was missing.

### 1.4. `single-program.php` & `inc/comparison.php`
- In `single-program.php:266-268`:
  - Directly rendered `$learning_details['campus']` without explicit fallback guard against "Online".
- In `inc/comparison.php:168-175`:
  - Harvested all `campus` terms without filtering out `'online'`, causing comparison tables and cards (`$item['campus_info']`) to display "Online" as a physical training station.

### 1.5. Syntax Corruption in `taxonomy.php:220`
- Verbatim line 220 in `taxonomy.php`:
  ```html
  <a href="<"'?php the_permalink(); ?>"'>" class="bg-brand-accent text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#e06e00] shadow-sm shadow-brand-accent/10 transition-all">Đăng ký học</a>
  ```
  Contained malformed quotes `<"'?php` and `"'>"`.

---

## 2. Logic Chain

1. **Rollup Exclusivity (Step 1 Elimination)**:
   - Direct school taxonomy terms can become desynchronized from actual curricula (e.g. school has legacy `chinh-quy` term assigned, but only offers Liên thông `tu-xa`).
   - By eliminating Step 1 in `ltdh_get_school_training_types()` and rolling up exclusively from published programs matching `post_status => publish` and `tax_query` for `['tu-xa', 'vua-hoc-vua-lam']`, the badges on school cards and archives represent 100% genuine admission opportunities.

2. **Campus Isolation**:
   - Physical facility (`campus`) and delivery mode (`training_type`) are orthogonal. "Online" represents a delivery mechanism, never a physical brick-and-mortar location.
   - In `ltdh_get_program_learning_details()`:
     * Campuses with slug or name `'online'` (case-insensitive) are strictly excluded from `$physical_campuses`.
     * If physical campuses exist (e.g., Hà Nội, TP.HCM), their names are returned.
     * If no physical campus exists and training type is `tu-xa`, `'Toàn quốc'` is returned — never `'Online'`.
     * For non-`tu-xa` programs without explicit physical campuses, the school's regional location (`LTDH_TAX_REGION`) is checked before falling back to `'Toàn quốc'`.
     * `mode` is explicitly mapped: `'tu-xa'` -> `'Học online 100%'`, `'vua-hoc-vua-lam'` -> `'Học tập trung / Cuối tuần'`.
   - In `inc/comparison.php:168-175`:
     * Filtered `'online'` from `$campus_terms` and integrated with `ltdh_get_program_learning_details()` to ensure comparison columns ("Cơ sở / Trạm đào tạo") never output "Online".
   - In `single-program.php:266-268`:
     * Added defense-in-depth sanitization check defaulting any empty or 'online' string to `'Toàn quốc'`.

3. **Query Sanitization on Single School & Single Major**:
   - In `single-school.php` and `single-major.php`, queries now explicitly enforce:
     * `'post_status' => 'publish'` (preventing draft/private offerings from appearing).
     * `tax_query` with `[ 'taxonomy' => LTDH_TAX_TRAINING_TYPE, 'field' => 'slug', 'terms' => [ 'tu-xa', 'vua-hoc-vua-lam' ], 'operator' => 'IN' ]` (strictly isolating in-scope Liên thông programs).
     * `'posts_per_page' => -1` (removing the artificial 10-item cutoff so all valid offerings appear).
     * Robust fallback from `_offered_programs` to direct relationship meta queries (`LTDH_META_SCHOOL_REL` / `LTDH_META_MAJOR_REL`).

4. **Taxonomy Syntax Correction**:
   - Corrected `taxonomy.php:220` to `<a href="<?php the_permalink(); ?>" class="...">Đăng ký học</a>`.

---

## 3. Caveats

- **No Caveats**: All changes are strictly within the assigned milestone scope and owned files. No database schema changes or additional CPTs were introduced.

---

## 4. Conclusion

Milestone M2 is fully implemented:
1. Core 3 CPTs (`school`, `major`, `program`) are strictly preserved.
2. `ltdh_get_school_training_types()` rolls up active formats exclusively from published in-scope programs with allowed terms (`tu-xa`, `vua-hoc-vua-lam`); static school terms check is eliminated.
3. `ltdh_get_program_learning_details()` sanitizes physical campuses, isolates `online`, and sets standardized learning mode labels.
4. Program listings on `single-school.php` and `single-major.php` enforce `post_status => publish`, filter for in-scope training types, and display all offerings (`posts_per_page => -1`).
5. Campus rendering in `single-program.php` and `inc/comparison.php` never outputs "Online" under physical location.
6. Syntax corruption in `taxonomy.php:220` is fixed.
7. All 6 modified files compile with 0 syntax errors under `php -l`.

---

## 5. Verification Method

### 5.1. PHP Syntax Verification
Run:
```bash
php -l inc/core/class-helpers.php
php -l single-school.php
php -l single-major.php
php -l single-program.php
php -l inc/comparison.php
php -l taxonomy.php
```
**Expected Result**: `No syntax errors detected in <file>` for all 6 files.

### 5.2. Code Inspection Points
1. **`inc/core/class-helpers.php:937-991`**: Verify `ltdh_get_school_training_types()` does not check `$school_id` taxonomy terms directly; verify `meta_query` + `tax_query` with `['tu-xa', 'vua-hoc-vua-lam']`.
2. **`inc/core/class-helpers.php:729-775`**: Verify `ltdh_get_program_learning_details()` filters `online`, returns `'Toàn quốc'` when empty for `tu-xa`, and sets `mode` to `'Học online 100%'` or `'Học tập trung / Cuối tuần'`.
3. **`single-school.php:365-420`**: Verify `WP_Query` has `'post_status' => 'publish'`, `'posts_per_page' => -1`, and `tax_query` for `['tu-xa', 'vua-hoc-vua-lam']`.
4. **`single-major.php:348-390`**: Verify `WP_Query` has `'post_status' => 'publish'`, `'posts_per_page' => -1`, and `tax_query` for `['tu-xa', 'vua-hoc-vua-lam']`.
5. **`single-program.php:266-268`**: Verify sanitized campus rendering with fallback guard.
6. **`inc/comparison.php:168-175`**: Verify `online` filter and consumption of `ltdh_get_program_learning_details()`.
7. **`taxonomy.php:220`**: Verify clean `<a href="<?php the_permalink(); ?>"` markup.
