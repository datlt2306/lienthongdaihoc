# HANDOFF REPORT — CHALLENGER AUDIT R1 & R2
## EMPIRICAL STRESS-TEST & CODE VALIDATION

- **Agent**: `challenger_audit_3_1` (teamwork_preview_challenger)
- **Role**: critic, specialist
- **Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_audit_3_1/`
- **Target File Audited**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`
- **Verdict**: ⚠️ **REQUEST_CHANGES**

---

### 1. OBSERVATION

1. **`archive-school.php` List View Query Storm**:
   - `archive-school.php:14`: `$view_mode` defaults to `'list'` on desktop.
   - `archive-school.php:264`: calls `ltdh_get_school_unique_majors_count( $school_id )`.
   - `inc/core/class-helpers.php:627-640`: runs `get_posts(['post_type' => 'program', 'fields' => 'ids'])`.
   - `inc/core/class-helpers.php:648-659`: loops through `$programs`, calling `get_post_meta( $prog_id, 'major_relationship', true )`. Because `'fields' => 'ids'` does not prime postmeta cache, each iteration executes a separate query.
   - `archive-school.php:265-276`: executes a second, redundant `get_posts` with `'school_relationship' => $school_id`.
   - `archive-school.php:292`: executes `wp_get_post_terms( $school_id, LTDH_TAX_REGION )`.
   - `archive-school.php:297-306`: loops through `$offered_program_ids`, calling `wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE )` for every single program ID. Object term cache is unprimed, generating a SQL query per program.
   - For 12 schools ($K = 15$ programs each) + 4 featured schools: $(12 \times 33) + (4 \times 17) + 2 = \mathbf{466 \text{ SQL queries}}$ on a single request.

2. **Taxonomy `training_type` Mapping in `inc/acf-import-cpts.json`**:
   - `inc/acf-import-cpts.json:174-176`: `"taxonomy": "training_type", "object_type": [ "program" ]`. Post type `school` is absent.
   - `archive-school.php:80` (Featured) & `archive-school.php:199` (Card View):
     `$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );`
   - Returns empty array `[]` 100% of the time. Badge markup inside `if ( ! empty( $school_types ) )` (lines 117, 223) never executes.

3. **URL Hijacking in `inc/core/class-rewrite-rules.php`**:
   - `inc/core/class-rewrite-rules.php:42`: `add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );`.
   - `inc/core/class-rewrite-rules.php:62-88`: `ltdh_program_request_guard` intercepts every single 1-segment request. For every static page (e.g. `/gioi-thieu/`, `/lien-he/`, `/cam-nang/`) and 404 URL, it executes 2 database queries (`get_posts` for `program`, then `get_posts` for `post`) before WordPress core runs the page query.

4. **Missing `#program-results-container` in DOM**:
   - `assets/js/main.js:10`: `const container = document.getElementById('program-results-container');`.
   - Grep search confirms `program-results-container` exists nowhere in the theme templates (`taxonomy-training_type.php:352` and `archive-program.php:355` have only `class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6"` without ID).
   - Biến `container` is always `null`. AJAX filtering is 100% dead code.

5. **Fatal Defects in Proposed Snippets in `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`**:
   - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:303-304`: Hooks `wp_trash_post` and `untrash_post`. In WP Core, `wp_trash_post` fires before status is updated in DB. `rebuild_entity_programs_cache` queries `'post_status' => 'publish'`, finding the post still published, and re-saves it in `_offered_programs`. Trashed posts are never removed.
   - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:307`: Hooks `before_delete_post`. The post is still in the database when `rebuild_entity_programs_cache` runs, so its ID is re-saved to `_offered_programs` right before deletion, permanently preserving an orphan ID.
   - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:753`: Instructs replacing lines 265-307 of `archive-school.php`. This deletes `$prog_tags` (lines 278-290) and `$region_terms` (lines 292-293), triggering `PHP Warning: Undefined variable $prog_tags` on line 345, permanently destroying the top 5 program tag pills on list view cards, and echoing HTML `<span>` elements outside and above the card container.
   - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:767-790`: Proposed `pre_get_posts` query filter completely omits `$_GET['nhom_nganh']`, `$_GET['truong']`, `$_GET['s']`, and `$_GET['sort']`, completely breaking user filters on `taxonomy-training_type.php`.
   - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:816-823`: Canonical filter checks `#^/he-dao-tao/?$#i`, which does not fix the diagnosed canonical redirect loop on `/chuong-trinh/`.
   - `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md:1157`: `rawurlencode( $bot_token )` encodes `:` to `%3A`, causing Telegram API to return HTTP 404 Not Found.

---

### 2. LOGIC CHAIN

1. **Premise 1 (Empirical Accuracy of Audit)**: Observations 1, 2, 3, and 4 verify that the Worker's diagnostic claims regarding N+1 queries, missing taxonomy mapping, rewrite rule hijacking, and broken AJAX filter are factual and verifiable in the codebase.
2. **Premise 2 (Defective Proposed Solutions)**: Observation 5 proves that the code snippets provided in Sections 2.4, 3.7.1, 3.7.2, 3.7.4, and 5.6.3 contain critical lifecycle bugs, breaking UI regressions, undefined variable notices, and API communication failures.
3. **Inference 1**: If the engineering team applies the proposed `LTDH_Entity_Relationship_Engine` as written, trashing and deleting programs will continue to leave orphan IDs in `_offered_programs`, failing the primary objective of Requirement R1.
4. **Inference 2**: If the engineering team applies the `archive-school.php` snippet as written, the site will suffer UI corruption and PHP warnings on PHP 8+.
5. **Inference 3**: If the engineering team applies the `pre_get_posts` snippet on `taxonomy-training_type.php`, faceted program filtering by school and major will break completely.
6. **Deductive Conclusion**: While the audit's findings are valid, the proposed code fixes are unsafe for direct implementation. The audit document must be updated with the revised, verified code snippets before sign-off.

---

### 3. CAVEATS

- No theme source code was modified during this review (strict compliance with the ZERO modification constraint).
- The WordPress database was not directly modified; all observations were verified via static analysis, code trace, and simulated PHP 8.4 runtime execution.
- Requirements R3 (Legal) and R4 (CRM Funnel) were audited for code interactions (Telegram bot token encoding, lead table schema), but legal interpretations were cross-referenced against statutory texts (TT 27/2019, TT 28/2023, Luat Quang Cao 2012) without legal counsel representation.

---

### 4. CONCLUSION

- **VERDICT**: ⚠️ **REQUEST_CHANGES**
- **Action Items for Worker / Orchestrator**:
  1. Revise `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` Section 2.4: Replace `LTDH_Entity_Relationship_Engine` with the patched version from `analysis.md` (listening to `trashed_post`, `untrashed_post`, passing `post__not_in` on deletion, and supporting both `acf/save_post` and `save_post`).
  2. Revise Section 3.7.1: Update the `archive-school.php` fix snippet so that only lines 295-307 are replaced, preserving `$prog_tags` and `$region_terms`.
  3. Revise Section 3.7.2: Include all filter parameters (`nhom_nganh`, `truong`, `s`, `sort`) in the `pre_get_posts` optimization.
  4. Revise Section 3.7.4: Target the canonical URL fix to `is_post_type_archive('program')` / `/chuong-trinh/`.
  5. Revise Section 5.6.3: Remove `rawurlencode` from `$bot_token` in `ltdh_trigger_telegram_notification_v2`.

---

### 5. VERIFICATION METHOD

To independently verify these findings:
1. **Verify Missing Container**:
   `grep -rn "program-results-container" .` → Only matches `assets/js/main.js:10`. Zero occurrences in `.php` files.
2. **Verify Taxonomy Object Type**:
   Inspect `inc/acf-import-cpts.json` lines 174-176. Confirm `"object_type": [ "program" ]`.
3. **Verify WordPress Trashing Lifecycle**:
   Inspect WordPress Core `wp_trash_post()` and `wp_untrash_post()` in `wp-includes/post.php`. Confirm `wp_trash_post` fires prior to updating `post_status = 'trash'`.
4. **Verify PHP 8 Undefined Variable**:
   Run PHP linter or dry-run replacing lines 265-307 in `archive-school.php`. Notice line 345 checks `$prog_tags`, which is not declared anywhere else in the file.
5. **Verify Telegram Token Colon Encoding**:
   Execute `php -r 'echo rawurlencode("123:ABC");'` → Output is `123%3AABC`. Telegram API requires the raw colon separator.
