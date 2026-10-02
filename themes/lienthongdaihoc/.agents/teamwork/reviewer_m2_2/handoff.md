# Milestone M2 Review Report & Handoff

**Reviewer**: `reviewer_m2_2` (M2 Campus & Presentation Reviewer)  
**Roles**: Reviewer, Adversarial Critic  
**Date**: 2026-10-01  
**Working Directory**: `.agents/teamwork/reviewer_m2_2/`  
**Verdict**: **APPROVE**  

---

## 1. Observation

### 1.1. Integrity Assessment
- Checked for hardcoded test results, facade logic, bypass shortcuts, fabricated logs, or self-certification:
  - **Result**: Zero integrity violations found.
  - All implementations dynamically query WordPress core functions (`wp_get_post_terms`, `get_posts`, `get_post_meta`, `get_field`) and handle actual taxonomy relationships.

### 1.2. Campus "Online" Isolation
- **`inc/core/class-helpers.php:729-785` (`ltdh_get_program_learning_details`)**:
  - Filter logic:
    ```php
    foreach ($campuses as $campus) {
        $slug = is_object($campus) ? $campus->slug : '';
        $name = is_object($campus) ? trim($campus->name) : '';
        if ('online' === strtolower($slug) || 'online' === strtolower($name)) {
            continue;
        }
        if ($name && ! in_array($name, $physical_campuses, true)) {
            $physical_campuses[] = $name;
        }
    }
    ```
  - Physical campus resolution:
    ```php
    if ( ! empty($physical_campuses) ) {
        $campus_name = implode(', ', $physical_campuses);
    } elseif ( 'tu-xa' === $type_slug ) {
        $campus_name = 'Toàn quốc';
    } else {
        $school_id = get_post_meta($program_id, 'school_relationship', true);
        if ( is_array($school_id) ) {
            $school_id = ! empty($school_id) ? ( is_object($school_id[0]) ? $school_id[0]->ID : $school_id[0] ) : 0;
        } elseif ( is_object($school_id) ) {
            $school_id = $school_id->ID;
        }
        $school_region = '';
        if ($school_id) {
            $region_terms = wp_get_post_terms((int) $school_id, LTDH_TAX_REGION);
            if ( ! empty($region_terms) && ! is_wp_error($region_terms) ) {
                $school_region = $region_terms[0]->name;
            }
        }
        $campus_name = $school_region ?: 'Toàn quốc';
    }
    ```
  - Verbatim check: Under no execution branch does `$campus_name` output or retain `"Online"`.
- **`single-program.php:266-274`**:
  - Defense-in-depth sanitization:
    ```php
    <span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php 
        $display_campus = $learning_details['campus'] ?? '';
        if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
            $display_campus = 'Toàn quốc';
        }
        echo esc_html( $display_campus ); 
    ?></span>
    ```
- **`inc/comparison.php:168-181` (`ltdh_compare_resolve_program`)**:
  - Filter logic:
    ```php
    $learning_details = ltdh_get_program_learning_details( $program_id );

    $campus_terms = wp_get_post_terms( $program_id, 'campus' );
    $campus_names = [];
    if ( ! is_wp_error( $campus_terms ) && ! empty( $campus_terms ) ) {
        foreach ( $campus_terms as $term ) {
            if ( 'online' === strtolower( $term->slug ) || 'online' === strtolower( trim( $term->name ) ) ) {
                continue;
            }
            $campus_names[] = $term->name;
        }
    }
    $campus_name = ! empty( $campus_names ) ? implode( ', ', $campus_names ) : $learning_details['campus'];
    ```
  - Both physical campus terms and the fallback `$learning_details['campus']` are sanitized against `'online'`.
- **Empirical Database Scan (100 programs)**:
  - Ran automated scan across all 100 `program` records in the database (`wp eval`).
  - Result: `SUCCESS: Checked 100 programs. ZERO instances of Online in physical campus locations!`

### 1.3. Delivery Mode Details Standardization
- **`inc/core/class-helpers.php:772-785`**:
  - Mapping:
    ```php
    if ( 'tu-xa' === $type_slug ) {
        $learning_mode = 'Học online 100%';
    } elseif ( 'vua-hoc-vua-lam' === $type_slug ) {
        $learning_mode = 'Học tập trung / Cuối tuần';
    } else {
        $learning_mode = 'Học tập trung / Cuối tuần';
    }
    ```
- **Empirical Distribution Scan**:
  - Tested all 100 `program` records in database:
    - `"Học online 100%"`: 98 programs
    - `"Học tập trung / Cuối tuần"`: 2 programs
    - 0 unstandardized strings, 0 legacy labels.

### 1.4. Syntax Fix on `taxonomy.php:220`
- **Before**:
  ```html
  <a href="<"'?php the_permalink(); ?>"'>" class="bg-brand-accent text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#e06e00] shadow-sm shadow-brand-accent/10 transition-all">Đăng ký học</a>
  ```
- **After**:
  ```html
  <a href="<?php the_permalink(); ?>" class="bg-brand-accent text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#e06e00] shadow-sm shadow-brand-accent/10 transition-all">Đăng ký học</a>
  ```
- Result: Clean, well-formed HTML anchor tag.

### 1.5. Syntax Compilation (`php -l`)
- Executed `php -l` on all modified files:
  - `php -l inc/core/class-helpers.php` -> `No syntax errors detected`
  - `php -l single-program.php` -> `No syntax errors detected`
  - `php -l inc/comparison.php` -> `No syntax errors detected`
  - `php -l taxonomy.php` -> `No syntax errors detected`
  - `php -l single-school.php` -> `No syntax errors detected`
  - `php -l single-major.php` -> `No syntax errors detected`

---

## 2. Logic Chain

1. **Physical Facility vs. Delivery Mode Separation**:
   - In WordPress taxonomy modeling, `campus` was historically used for both physical cities (`Hà Nội`, `Hồ Chí Minh`) and `Online`.
   - By explicitly stripping any term where `slug === 'online'` or `name === 'online'` (case-insensitive and trimmed) in `ltdh_get_program_learning_details()` and `inc/comparison.php`, the theme ensures that "Cơ sở học" strictly represents physical locations.
   - For distance learning programs (`tu-xa`) that lack physical venues, defaulting to `'Toàn quốc'` accurately reflects nationwide online availability.
   - For on-site programs (`vua-hoc-vua-lam`) lacking specific campus tags, falling back to the parent institution's geographic region (`LTDH_TAX_REGION`) ensures contextual accuracy without exposing an empty field or false online badge.

2. **Presentation Standardization**:
   - The delivery mode label (`mode`) was previously fragmented into `'Học online 100%'`, `'Học tập trung cuối tuần'`, and `'Học tập trung'`.
   - The refactored resolver maps all `tu-xa` programs to `'Học online 100%'` and all `vua-hoc-vua-lam` programs to `'Học tập trung / Cuối tuần'`.
   - Verified that all consumer templates (`single-program.php`, `archive-program.php`, `taxonomy-training_type.php`, `taxonomy.php`, `inc/core/class-query-filters.php`, `inc/comparison.php`) display this standardized format consistently.

3. **Adversarial Edge Cases**:
   - *Case 1: Multiple campuses including Online*: The loop skips `online` and accumulates physical campuses, returning e.g. `'Hà Nội, TP.HCM'`.
   - *Case 2: Empty campus terms on a program*: Safely resolves based on `training_type` and school region, preventing undefined indexes or empty strings.
   - *Case 3: Incomplete/Null school relationship*: Handled gracefully via type checks on ACF return values (`is_array`, `is_object`, or `int`).
   - *Case 4: XSS Safety*: All template outputs use `esc_html()` or `esc_attr()` appropriately.

4. **Rollup Query Refinement**:
   - `ltdh_get_school_training_types()` now strictly queries published programs (`post_status => 'publish'`) and filters by `['tu-xa', 'vua-hoc-vua-lam']`, removing static school taxonomy overrides.
   - `single-school.php` and `single-major.php` program queries now enforce `'post_status' => 'publish'`, `'posts_per_page' => -1`, and in-scope taxonomy filtering, preventing out-of-scope or drafted programs from leaking onto the frontend.

---

## 3. Caveats

- No caveats. The changes are strictly scoped, fully backwards-compatible, and do not introduce database schema alterations or new CPTs.

---

## 4. Conclusion

- **Verdict**: **APPROVE**
- All 4 verification criteria have been rigorously met:
  1. The term `online` in the `campus` taxonomy is completely isolated and never outputs as a physical location across all templates, helpers, and comparison tools.
  2. Delivery mode details are standardized to `"Học online 100%"` and `"Học tập trung / Cuối tuần"`.
  3. The syntax error on `taxonomy.php:220` is cleanly resolved.
  4. All affected PHP files pass `php -l` without errors or warnings.
- Zero integrity violations were detected.

---

## 5. Verification Method

To independently reproduce the verification:

1. **PHP Syntax Check**:
   ```bash
   php -l inc/core/class-helpers.php
   php -l single-program.php
   php -l inc/comparison.php
   php -l taxonomy.php
   php -l single-school.php
   php -l single-major.php
   ```
   *Expected output*: `No syntax errors detected in <file>` for all files.

2. **Automated Database Scan for Campus Online Leaks**:
   ```bash
   wp eval '
   $programs = get_posts(["post_type" => "program", "post_status" => "any", "posts_per_page" => -1, "fields" => "ids"]);
   $leaks = [];
   foreach ($programs as $id) {
       $details = ltdh_get_program_learning_details($id);
       if (stripos($details["campus"], "online") !== false) $leaks[] = "Details ID $id: " . $details["campus"];
       $cmp = ltdh_compare_resolve_program($id);
       if (stripos($cmp["campus_info"], "online") !== false) $leaks[] = "Compare ID $id: " . $cmp["campus_info"];
   }
   echo empty($leaks) ? "ZERO LEAKS\n" : "LEAKS FOUND: " . count($leaks) . "\n";
   '
   ```
   *Expected output*: `ZERO LEAKS`.

3. **Inspect Line 220 of `taxonomy.php`**:
   ```bash
   sed -n '218,222p' taxonomy.php
   ```
   *Expected output*: Clean anchor tag `<a href="<?php the_permalink(); ?>" class="...">Đăng ký học</a>`.
