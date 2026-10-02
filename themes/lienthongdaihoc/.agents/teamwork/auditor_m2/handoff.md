# Forensic Audit Report: Milestone M2 — Data Flow & Campus Isolation

**Auditor**: `auditor_m2` (M2 Forensic Integrity Auditor)  
**Date**: 2026-10-01  
**Working Directory**: `.agents/teamwork/auditor_m2/`  
**Target Codebase**: `lienthongdaihoc.com` WordPress Theme  
**Integrity Mode**: Development (per `ORIGINAL_REQUEST.md`)  
**Verdict**: `CLEAN`

---

## 1. Observation

### 1.1. Scope of Changes & Git Diff Analysis
A comprehensive forensic inspection of the git diff across all 6 modified files was executed:

1. **`inc/core/class-helpers.php`**:
   - `ltdh_get_program_learning_details(int $program_id): array` (lines 729–780):
     * Retrieves terms via `wp_get_post_terms($program_id, LTDH_TAX_CAMPUS)`.
     * Explicitly iterates over terms and skips any term whose `slug` or `name` is `'online'` (case-insensitive via `strtolower()`).
     * Returns comma-separated physical campuses if available.
     * When physical campuses are absent: returns `'Toàn quốc'` if training type is `'tu-xa'`, or resolves the linked university's region (`LTDH_TAX_REGION`) before falling back to `'Toàn quốc'`.
     * Resolves `$learning_mode` cleanly: `'tu-xa'` -> `'Học online 100%'`, `'vua-hoc-vua-lam'` -> `'Học tập trung / Cuối tuần'`.
     * Stripped out legacy out-of-scope `'van-bang-2'` mapping.
   - `ltdh_get_school_unique_majors_count(int $school_id): int` (lines 835–886):
     * Changed query from arbitrary `posts_per_page => 100` to `posts_per_page => -1`.
     * Strictly enforces `post_status => 'publish'`, `no_found_rows => true`, and `tax_query` filtering for allowed slugs `['tu-xa', 'vua-hoc-vua-lam']`.
     * Added defensive fallback checking `_offered_programs` with identical strict parameters if direct `school_relationship` meta query yields empty.
   - `ltdh_get_school_training_types(int $school_id, string $output_format = 'names'): array` (lines 999–1107):
     * **Step 1 eliminated**: Completely removed the legacy bypass `wp_get_post_terms($school_id, LTDH_TAX_TRAINING_TYPE)` that previously returned static terms assigned directly to the school post.
     * Rolls up exclusively from published `program` posts where `school_relationship = $school_id` and training type term slug is in `['tu-xa', 'vua-hoc-vua-lam']`.
     * Fallback query checks `_offered_programs` with identical filters if meta query returns empty.
     * Gathers, de-duplicates, and formats terms according to `$output_format` (`'names'`, `'slugs'`, `'terms'`, `'objects'`, `'all'`).
     * Caches results via `wp_cache_set` / `wp_cache_get` with 1-hour expiration.

2. **`single-school.php`** (lines 379–423):
   - Added `$tax_training_type_filter` enforcing `taxonomy => LTDH_TAX_TRAINING_TYPE`, `field => slug`, `terms => ['tu-xa', 'vua-hoc-vua-lam']`.
   - Updated primary query on `$offered_program_ids` to specify `post_status => 'publish'`, `posts_per_page => -1`, `no_found_rows => true`, `tax_query => [$tax_training_type_filter]`, and `meta_query => [$meta_status_filter]`.
   - Updated fallback query (when `$offered_program_ids` is missing or yields zero posts) to query by `LTDH_META_SCHOOL_REL => $school_id` with identical `post_status => 'publish'`, `posts_per_page => -1`, `no_found_rows => true`, and `tax_query`.
   - Removed arbitrary `posts_per_page => 10` cap.

3. **`single-major.php`** (lines 348–393):
   - Added `$tax_training_type_filter` enforcing `taxonomy => LTDH_TAX_TRAINING_TYPE`, `field => slug`, `terms => ['tu-xa', 'vua-hoc-vua-lam']`.
   - Updated primary query on `$offered_program_ids` with `post_status => 'publish'`, `posts_per_page => -1`, `no_found_rows => true`, and `$tax_training_type_filter`.
   - Updated fallback query to query by `LTDH_META_MAJOR_REL => $major_id` with identical strict parameters.
   - Removed arbitrary `posts_per_page => 10` cap.

4. **`single-program.php`** (lines 266–274):
   - Replaced raw `$learning_details['campus']` rendering with defense-in-depth sanitization:
     ```php
     $display_campus = $learning_details['campus'] ?? '';
     if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
         $display_campus = 'Toàn quốc';
     }
     echo esc_html( $display_campus );
     ```
   - Guarantees that neither an empty string nor "Online" can ever render as a physical facility.

5. **`inc/comparison.php`** (lines 168–180):
   - Integrated `ltdh_get_program_learning_details($program_id)`.
   - Added explicit exclusion: skips any term where `slug` or `name` is `'online'`.
   - Falls back to `$learning_details['campus']` when `$campus_names` is empty.

6. **`taxonomy.php`** (line 220):
   - Fixed malformed quote and tag syntax:
     - Old: `<a href="<"'?php the_permalink(); ?>"'>" class="...">Đăng ký học</a>`
     - New: `<a href="<?php the_permalink(); ?>" class="...">Đăng ký học</a>`

### 1.2. Core CPT Preservation Check
- Inspected `inc/post-types.php` and `inc/acf-import-cpts.json`.
- Exactly 3 core CPTs exist: `school`, `major`, `program` (along with pre-existing `guide`).
- `inc/post-types.php` was untouched by `worker_m2`.
- Exact count of new CPTs registered: **0**.

### 1.3. Hard-Deletion Check
- Grepped across all 6 files for destructive operations (`wp_delete_post`, `wp_trash_post`, `DELETE FROM`, `$wpdb->delete`, `wp_delete_term`).
- Total records hard-deleted: **0**.
- The only deletion found in `class-helpers.php` is transient cache flushing (`delete_transient(...)`) in `ltdh_flush_content_caches()`, which does not affect database post/term entities.

### 1.4. Anti-Cheating & Prohibited Pattern Check
- Hardcoded test results: **None detected**.
- Facade / Mock implementations: **None detected**. All functions execute genuine WordPress database queries and taxonomy lookups.
- Fabricated verification outputs: **None detected**.
- Execution delegation: **None detected**.

---

## 2. Logic Chain

1. **Rollup Exclusivity**:
   - The user requirement in `ORIGINAL_REQUEST.md` (R2) mandates: "Trường (`school`) → Các `program` tuyển sinh Liên thông thực tế → Thuộc tính (`major`, `training_type`). Trang trường và trang ngành phải query các chương trình Liên thông thực tế thay vì dựa vào term gắn trực tiếp trên `school`."
   - By eliminating Step 1 in `ltdh_get_school_training_types()` and querying only `program` posts with `post_status => publish` and `tax_query => ['tu-xa', 'vua-hoc-vua-lam']`, stale or out-of-scope taxonomy terms attached directly to the school post can never surface on frontend cards or badges.
   - Observation 1.1 directly supports this conclusion.

2. **Campus Isolation**:
   - The user requirement in `ORIGINAL_REQUEST.md` (R2) mandates: "Taxonomy `campus`: kiểm tra và cô lập term `Online` không để xuất hiện như một cơ sở vật lý trong bộ lọc hay giao diện."
   - The implementation isolates `Online` across all layers:
     * In `ltdh_get_program_learning_details()`: excludes terms with slug or name `'online'` case-insensitively, falling back to `'Toàn quốc'` or school region.
     * In `inc/comparison.php`: filters out `'online'` from campus terms before building the comparison card.
     * In `single-program.php`: applies an additional defensive fallback ensuring any empty or `'online'` string evaluates to `'Toàn quốc'`.
   - Observation 1.1 directly supports this conclusion.

3. **Query Consistency & Scope Confinement**:
   - On `single-school.php` and `single-major.php`, queries previously lacked training type filters and had arbitrary 10-post limits.
   - The new queries explicitly enforce `tax_training_type_filter` (`['tu-xa', 'vua-hoc-vua-lam']`), `post_status => publish`, and `posts_per_page => -1` with `no_found_rows => true`.
   - In both templates, if the pre-computed `$offered_program_ids` yields zero published in-scope programs, the code cleanly falls back to direct relational queries by school/major ID.
   - Observation 1.1 directly supports this conclusion.

4. **Absence of Integrity Violations**:
   - Zero CPTs were introduced or modified.
   - Zero posts, terms, or database rows were hard-deleted.
   - No mock returns or dummy bypasses were used.
   - Syntax correction in `taxonomy.php` fixes a real template corruption.

---

## 3. Caveats

- **No Caveats**: The audit examined 100% of the modified lines in all 6 target files. The changes are strictly scoped, structurally sound, and adhere fully to WordPress development practices and project requirements.

---

## 4. Conclusion

**Verdict: `CLEAN`**

The changes submitted by `worker_m2` for Milestone M2 are verified to be fully authentic, logically complete, and free of any integrity violations:
1. 3 core CPTs (`school`, `major`, `program`) are strictly preserved; 0 new CPTs registered.
2. Genuine rollup logic in `ltdh_get_school_training_types()` completely eliminates static school term bypasses.
3. Genuine campus isolation in `ltdh_get_program_learning_details()`, `single-program.php`, and `inc/comparison.php` prevents "Online" from ever rendering as a physical facility.
4. Single School and Single Major queries strictly isolate published in-scope programs (`['tu-xa', 'vua-hoc-vua-lam']`) with robust fallback handling and unlimited pagination (`posts_per_page => -1`, `no_found_rows => true`).
5. Zero database entities were hard-deleted.
6. Corrupt syntax in `taxonomy.php:220` is cleanly resolved.

---

## 5. Verification Method

### 5.1. File Inspection Commands
Verify key implementation points directly in the codebase:
- `inc/core/class-helpers.php:729-780`: Verify `ltdh_get_program_learning_details()` excludes `online` terms and sets clean fallbacks.
- `inc/core/class-helpers.php:835-886`: Verify `ltdh_get_school_unique_majors_count()` filters for allowed training types and uses `publish` status.
- `inc/core/class-helpers.php:999-1107`: Verify `ltdh_get_school_training_types()` rolls up from published programs and eliminates Step 1 direct-term check.
- `single-school.php:379-423`: Verify `WP_Query` has `posts_per_page => -1`, `post_status => 'publish'`, and `$tax_training_type_filter`.
- `single-major.php:348-393`: Verify `WP_Query` has `posts_per_page => -1`, `post_status => 'publish'`, and `$tax_training_type_filter`.
- `single-program.php:266-274`: Verify defensive fallback for campus display.
- `inc/comparison.php:168-180`: Verify exclusion of `online` campus terms.
- `taxonomy.php:220`: Verify clean permalink anchor tag.

### 5.2. Static Syntax Verification
```bash
php -l inc/core/class-helpers.php
php -l single-school.php
php -l single-major.php
php -l single-program.php
php -l inc/comparison.php
php -l taxonomy.php
```
All 6 files parse with 0 syntax errors.
