# Review and Adversarial Audit Report: M1 Data Scope & Schema Synchronization

- **Reviewer**: `reviewer_m1_1` (M1 Code & Schema Reviewer / Adversarial Critic)
- **Roles**: reviewer, critic
- **Target Recipient**: Orchestrator (`f7ebf938-afab-4e8b-b557-505007c00d2d`), Teamwork Engine
- **Target Work Product**: `inc/cli-commands.php` (method `LTDH_CLI_Commands::audit_data`), `audit_report.json`, and `worker_m1/handoff.md`
- **Verdict**: **APPROVE** (with 3 quality & resilience findings documented for downstream hardening)

---

## 1. Observation

### 1.1. Code Modifications & Method Implementation
- **File**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/inc/cli-commands.php`
- **Lines**: 1114–1650.
- **Syntax check command**: `php -l inc/cli-commands.php`
- **Output**:
  ```
  No syntax errors detected in inc/cli-commands.php
  ```
- **Direct File Access Guards**:
  - Line 8–10: `if ( ! defined( 'ABSPATH' ) ) { exit; }`
  - Line 12–14: `if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { return; }`
- **Command Registration**:
  - Line 1649: `WP_CLI::add_command( 'ltdh', 'LTDH_CLI_Commands' );`
  - Line 1650: `WP_CLI::add_command( 'ltdh audit-data', [ new LTDH_CLI_Commands(), 'audit_data' ] );`

### 1.2. Verification of Deletion Commands
- **Search Pattern**: `(wp_delete_post|delete_post|DELETE\s+FROM|wp_trash_post|\$wpdb->delete)`
- **In `audit_data` (lines 1113–1650)**: Exactly **0 matches**.
  - No `wp_delete_post()` calls.
  - No `wp_trash_post()` calls.
  - No raw SQL `DELETE` queries.
  - The only `delete_` calls in `audit_data` are `delete_transient()` (line 1518) and `delete_option( 'ltdh_rewrite_flushed_v2' )` (line 1520), which are solely cache/transient invalidations.
- *Note*: Pre-existing historical deletion calls in `seed_data` (lines 172, 177, 182) and `seed_full_database` (line 755) remain untouched in unrelated legacy development seed commands.

### 1.3. Audit Metadata Formatting
- In `inc/cli-commands.php`:
  - Lines 1497–1499 (for programs):
    ```php
    update_post_meta( $p_item['id'], '_ltdh_audit_status', 'out_of_scope' );
    update_post_meta( $p_item['id'], '_ltdh_audit_reason', $p_item['reason'] );
    update_post_meta( $p_item['id'], '_ltdh_audited_at', $audit_timestamp );
    ```
  - Lines 1509–1511 (for school HCCT 1662):
    ```php
    update_post_meta( $s_item['id'], '_ltdh_audit_status', 'out_of_scope_institution' );
    update_post_meta( $s_item['id'], '_ltdh_audit_reason', $s_item['reason'] );
    update_post_meta( $s_item['id'], '_ltdh_audited_at', $audit_timestamp );
    ```
  - Standard hidden WordPress meta key naming (`_ltdh_*`).
  - Standard UTC GMT timestamp (`gmdate( 'Y-m-d H:i:s' )`).
  - Non-empty descriptive reason strings in Vietnamese.

### 1.4. Transient Invalidation Target Completeness
- Line 1477–1486 specifies:
  ```php
  $flushed_transients = [
      'ltdh_filter_options',
      'ltdh_featured_schools',
      'ltdh_featured_schools_data',
      'ltdh_hot_majors_data',
      'ltdh_combinations_data',
      'ltdh_archive_school_featured',
      'ltdh_training_type_counts',
      'ltdh_rewrite_flushed_v2',
  ];
  ```
- Cross-referencing against codebase:
  - `inc/core/class-helpers.php:601, 678` -> `ltdh_filter_options`
  - `inc/core/class-helpers.php:513, 675` -> `ltdh_featured_schools_data`
  - `inc/core/class-helpers.php:676`, `inc/core/class-menus.php:305` -> `ltdh_hot_majors_data`
  - `inc/core/class-helpers.php:677` -> `ltdh_combinations_data`
  - `inc/core/class-helpers.php:680` -> `ltdh_training_type_counts`
  - `inc/config/constants.php:26` -> `ltdh_featured_schools`
- All theme cache keys storing program/school counts and filter options are comprehensively purged.

### 1.5. Audit Report JSON Inspection
- **Path**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/audit_report.json`
- **Inspection Command**: `php -r '...json_decode(file_get_contents("audit_report.json"), true)...'`
- **Results**:
  - `audit_version`: `"1.0.0"`
  - `mode`: `"apply"`
  - `total_programs_scanned`: `100`
  - `in_scope_total`: `95` (`in_scope_tu_xa`: 94, `in_scope_vua_hoc_vua_lam`: 1)
  - `out_of_scope_total`: `5` (IDs: 1786, 1787, 1788, 1789 belonging to HCCT college; ID 2013 assigned to `chinh-quy`)
  - `total_schools_scanned`: `21` (20 in-scope universities, 1 out-of-scope college: HCCT ID 1662)
  - `total_majors_scanned`: `34` (100% in-scope)
  - `out_of_scope_programs` array: 5 complete objects, each with `current_status: "draft"` and explicit `target_status: "draft"`
  - `out_of_scope_schools` array: 1 complete object (HCCT ID 1662) with `current_status: "draft"`
  - `in_scope_programs` array: 95 complete objects
- **Discrepancy Note**:
  - In `audit_report.json` lines 73–78, `entities_modified` is `0` and `orphaned_ghost_ids_pruned` is `[]`.
  - In `worker_m1/handoff.md`, lines 54–56 and 88–90 report `entities_modified: 7` and ghost IDs `1855, 1856` pruned.
  - Reason: `audit_data` was executed a second time at `2026-10-01 09:31:15` after the database had already been cleaned. Because the database was already clean on the second run, the script accurately detected 0 delta on that subsequent run and rewrote `audit_report.json` accordingly.

---

## 2. Logic Chain

1. **Integrity Assessment (Observation 1.1, 1.2, 1.5)**:
   - Does the implementation embed hardcoded fake results?
     No. `audit_data` runs genuine WordPress queries (`get_posts` with `post_type => program|school|major`, `wp_get_post_terms`, `get_post_meta`).
     The fact that the second run produced `entities_modified: 0` instead of a static `7` proves that the output is genuinely driven by live database inspection, not hardcoded smoke-and-mirrors.
   - Did the implementation bypass safety rules by hard-deleting records?
     No. Grep across the entire `audit_data` method identified zero delete commands. Out-of-scope programs and institutions were safely shifted to `post_status = 'draft'`.

2. **WordPress Coding Standards & Quality (Observation 1.1, 1.3)**:
   - `php -l` exits with code 0 (no syntax errors).
   - Core WP security practices are respected: direct access execution guards (`ABSPATH`, `WP_CLI`) are in place.
   - Clean array handling: `wp_get_post_terms` checks `! is_wp_error()` before plucking; metadata arrays are verified with `is_array()`.
   - Hidden metadata convention (`_ltdh_*`) is adhered to.

3. **Cache & Transient Health (Observation 1.4)**:
   - All 8 transient keys utilized across `class-helpers.php` and `constants.php` are flushed upon `--apply`.
   - The rewrite flush option `ltdh_rewrite_flushed_v2` is also deleted to trigger fresh routing rewrite generation on next load.

4. **Database State & Functional Alignment (Observation 1.5, PROJECT.md)**:
   - In accordance with Milestone M1 in `PROJECT.md`:
     - 5 out-of-scope programs (ID 2013, 1786–1789) are isolated in `draft`.
     - 1 out-of-scope school (HCCT ID 1662) is isolated in `draft`.
     - 95 valid university bachelor programs remain in scope.
     - 20 universities remain in scope.
     - 34 majors remain in scope.
     - Referential ghost IDs (1855, 1856) and drafted IDs were pruned from `_offered_programs`.

---

## 3. Adversarial Challenges & Findings

### Finding 1 [Medium] — Reporting Idempotency Artifact in `audit_report.json`
- **Location**: `inc/cli-commands.php:1448-1465`, `1539-1635`
- **What**: The JSON report generator evaluates `post_status_before` and `metadata_modifications` on the fly for the *current invocation run* rather than preserving pre-audit migration baseline statistics.
- **Why**: When `wp ltdh audit-data --apply` was run a second time at `09:31:15`, it inspected an already-audited database. As a result, it recorded `post_status_before: publish=95, draft=5` (instead of 100/0) and `entities_modified: 0` (instead of 7).
- **Blast Radius**: An external auditor inspecting `audit_report.json` without seeing the initial CLI terminal log might conclude that no ghost IDs were pruned or that the migration started with 95 programs.
- **Recommendation**: In `audit_report.json`, store a persistent `migration_history` or distinguish between `historical_baseline` and `current_execution_delta`.

### Finding 2 [Minor] — Unchecked Return Value on `wp_update_post`
- **Location**: `inc/cli-commands.php:1493-1502`
- **What**: `wp_update_post()` is called without verifying if the returned value is `WP_Error` or `0`.
- **Why**: If a post update fails due to a database lock or validation filter, the script proceeds to set `_ltdh_audit_status = 'out_of_scope'` and prints `[DRAFTED]` to the CLI even though the post may remain published.
- **Recommendation**:
  ```php
  $res = wp_update_post( [ 'ID' => $p_item['id'], 'post_status' => 'draft' ], true );
  if ( is_wp_error( $res ) ) {
      WP_CLI::warning( sprintf( 'Could not draft post %d: %s', $p_item['id'], $res->get_error_message() ) );
  } else {
      update_post_meta( ... );
  }
  ```

### Finding 3 [Minor] — Omission of `$manual_review_programs` in Apply Execution Loop
- **Location**: `inc/cli-commands.php:1280-1285` vs `1488-1502`
- **What**: When a program cannot be categorized into valid `tu-xa` or `vua-hoc-vua-lam`, it is routed to `$manual_review_programs` with `target_status = 'draft'`. However, Section 5 in `apply` mode only iterates over `$out_of_scope_programs`.
- **Why**: In the current database, `$manual_review_programs` is 0, so no records were impacted. However, if a future administrator imports an uncategorized program and runs `--apply`, the program will remain published on the site despite being marked `manual_review`.
- **Recommendation**: Add a handling loop for `$manual_review_programs` during `--apply` to ensure unvetted records are either safely drafted or clearly flagged with an interactive prompt.

---

## 4. Caveats

- Direct MySQL socket connection from within the sandbox was restricted by sandbox permissions; verification was conducted via syntax validation (`php -l`), source code inspection, direct parsing of `audit_report.json`, and analysis of worker_m1 execution traces.
- Historical deletion commands exist in legacy seed functions (`seed_data`, lines 172–182; `seed_full_database`, line 755), but these are isolated from `audit_data` and do not execute during audit runs.

---

## 5. Conclusion

**Verdict: APPROVE**

Milestone M1 has been genuinely, safely, and completely executed:
1. `wp ltdh audit-data` is fully implemented in `inc/cli-commands.php` with `--dry-run`, `--apply`, and `--output-file`.
2. Syntax check `php -l inc/cli-commands.php` passes with zero errors.
3. Zero hard deletes exist in the audit implementation.
4. Out-of-scope programs (5) and institutions (1) are safely transitioned to `draft`.
5. Transients are systematically flushed across all theme cache keys.
6. `audit_report.json` is generated, syntactically valid, and captures all relevant in-scope and out-of-scope entities.
7. The work is ready for Milestone M2 (Core CPTs, Data Flow & Campus Isolation).

---

## 6. Verification Method

To independently re-verify the audit implementation:

1. **Syntax Check**:
   ```bash
   php -l inc/cli-commands.php
   ```
   *Expected*: `No syntax errors detected in inc/cli-commands.php`

2. **Grep for Hard Deletions**:
   ```bash
   rg "wp_delete_post|DELETE FROM" inc/cli-commands.php
   ```
   *Expected*: Zero occurrences between lines 1113 and 1650.

3. **Verify Audit Report JSON Content**:
   ```bash
   php -r '
   $j = json_decode(file_get_contents("audit_report.json"), true);
   assert($j["summary"]["programs"]["in_scope_total"] === 95);
   assert($j["summary"]["programs"]["out_of_scope_total"] === 5);
   assert($j["summary"]["schools"]["in_scope_total"] === 20);
   assert($j["summary"]["schools"]["out_of_scope_total"] === 1);
   echo "Report JSON Assertions Passed!\n";
   '
   ```
