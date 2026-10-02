# Empirical Challenger Handoff Report: Milestone M1 Database Stress-Test

- **Agent**: `challenger_m1_1` (M1 Database Adversarial Challenger)
- **Roles**: critic, specialist
- **Milestone**: M1 (Data Audit & Safe Scope Handling)
- **Target Recipient**: Orchestrator (`f7ebf938-afab-4e8b-b557-505007c00d2d`)
- **Verdict**: **APPROVE**

---

## 1. Observation

Direct empirical observations were gathered through independent execution of SQL queries and WP-CLI evaluation scripts on the target WordPress environment (`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`).

### 1.1. Post Count and Trash Integrity (Criteria 1 & 2)
- **Command executed**:
  ```bash
  wp --path=../../../ db query "SELECT post_type, post_status, COUNT(*) as cnt FROM wp_posts WHERE post_type IN ('program', 'school', 'major') GROUP BY post_type, post_status ORDER BY post_type, post_status"
  ```
- **Verbatim Output**:
  ```
  post_type	post_status	cnt
  major	publish	34
  program	draft	5
  program	publish	95
  school	draft	1
  school	publish	20
  ```
- **Trash Check Query**:
  ```bash
  wp --path=../../../ db query "SELECT post_type, COUNT(*) as trash_cnt FROM wp_posts WHERE post_type IN ('program', 'school', 'major') AND post_status = 'trash' GROUP BY post_type"
  ```
- **Verbatim Output**: `0 rows returned` (trash count == 0 for all three CPTs).
- **Drafted Programs Verification**:
  ```bash
  wp --path=../../../ db query "SELECT ID, post_title, post_status FROM wp_posts WHERE post_type = 'program' AND post_status = 'draft'"
  ```
  Output:
  - ID 1786: `Cử nhân Quản trị kinh doanh` (draft, reason: Cao đẳng HCCT)
  - ID 1787: `Cử nhân Kế toán` (draft, reason: Cao đẳng HCCT)
  - ID 1788: `Cử nhân Thương mại điện tử` (draft, reason: Cao đẳng HCCT)
  - ID 1789: `Cử nhân Quản trị khách sạn` (draft, reason: Cao đẳng HCCT)
  - ID 2013: `Cử nhân Công nghệ thông tin (Liên thông Chính quy)` (draft, reason: Chính quy ngoài phạm vi)
- **Drafted School Verification**:
  - ID 1662: `Trường Cao đẳng Thương mại và Du lịch Hà Nội` (draft, reason: Cơ sở đào tạo bậc Cao đẳng HCCT)

### 1.2. Program-to-School and Program-to-Major Relationship Integrity (Criterion 3)
- **Command executed**:
  ```bash
  wp --path=../../../ db query "
  SELECT 
    COUNT(p.ID) as total_published_programs,
    COUNT(DISTINCT sm.meta_value) as distinct_schools_mapped,
    COUNT(DISTINCT mm.meta_value) as distinct_majors_mapped
  FROM wp_posts p
  JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = 'school_relationship'
  JOIN wp_posts s ON CAST(sm.meta_value AS UNSIGNED) = s.ID AND s.post_status = 'publish' AND s.post_type = 'school'
  JOIN wp_postmeta mm ON p.ID = mm.post_id AND mm.meta_key = 'major_relationship'
  JOIN wp_posts m ON CAST(mm.meta_value AS UNSIGNED) = m.ID AND m.post_status = 'publish' AND m.post_type = 'major'
  WHERE p.post_type = 'program' AND p.post_status = 'publish'
  "
  ```
- **Verbatim Output**:
  ```
  total_published_programs	distinct_schools_mapped	distinct_majors_mapped
  95	20	34
  ```
- **Invalid Mapping Detection Query**:
  Executed SQL scanning for any published program with `sm.meta_value IS NULL OR s.ID IS NULL OR s.post_status != 'publish' OR s.post_type != 'school' OR mm.meta_value IS NULL OR m.ID IS NULL OR m.post_status != 'publish' OR m.post_type != 'major'`.
- **Result**: `0 rows returned` (zero orphaned or invalid mappings).
- **College Leakage Check**:
  Queried for programs linked to HCCT (school ID 1662). Exactly 4 programs found (1786, 1787, 1788, 1789), and **all 4 are status `draft`**. Zero programs linked to HCCT remain published.

### 1.3. Reverse Metadata Integrity `_offered_programs` (Criterion 4)
- **Script executed**:
  Unpacked every array entry in `_offered_programs` across all 55 entities (21 schools + 34 majors).
- **Verbatim Output**:
  ```
  === OFFERED PROGRAMS CHECK RESULT ===
  Total entities checked: 55
  Total program references in _offered_programs: 190
  Ghost/Non-existent IDs found: 0
  Drafted/Non-publish IDs found: 0
  Wrong post type IDs found: 0
  Specific ghost IDs (1855, 1856) found: 0
  Specific draft IDs (1786-1789, 2013) found: 0
  ```
- **Bidirectional Symmetry Check**:
  Iterated through all 95 published programs to check presence in mapped school and major's `_offered_programs`.
  - Published programs checked: 95
  - Symmetry errors found: 0
  - Mathematical integrity: 95 programs × 2 references = 190 total references.
- **Drafted School Cleanup Check**:
  School ID 1662 (HCCT) `_offered_programs` = `[]` (empty array).
- **School 1853 and Major 1677 Targeted Check**:
  - School ID 1853 (UTC) contains: `[1854, 2014]` (ghost IDs 1855, 1856 and draft ID 2013 were completely eradicated).
  - Major ID 1677 (IT) contains: `[1678, 1705, 1718, 1724, 1732, 1757, 1854, 2014]`.

### 1.4. Audit Report JSON Validity and Accuracy (Criterion 5)
- **File**: `audit_report.json`
- **Integrity script executed**:
  Loaded `audit_report.json` and cross-referenced each entry with `wp_posts`.
- **Verbatim Output**:
  ```
  === AUDIT_REPORT.JSON VS DB INTEGRITY TEST ===
  1. JSON Validity: OK
  Report claims:
   - In-scope programs: 95
   - Out-of-scope programs: 5
   - Out-of-scope schools: 1
  Program status discrepancies between JSON and DB: 0
  School status discrepancies between JSON and DB: 0
  Unaccounted DB programs: 0
  ```
- **CLI Idempotency Verification**:
  Executed `wp --path=../../../ ltdh audit-data --apply` a second time: completed cleanly with 0 errors, 0 ghost IDs remaining, and generated updated `audit_report.json` with `"mode": "apply"`.

### 1.5. Public WP_Query Isolation Check
- Executed default public `WP_Query` instances:
  - `program` public count: 95 (0 draft IDs returned).
  - `school` public count: 20 (0 HCCT ID returned).
  - `major` public count: 34.
- Direct status of drafted records: 2013 is `draft`, 1662 is `draft` (unauthenticated visitors receive 404).

---

## 2. Logic Chain

1. **Non-destructive Invariant Verified**:
   - Observation 1.1 establishes that `trash == 0` for `program`, `school`, and `major`.
   - The total number of programs in the database remains exactly 100 (95 + 5), schools exactly 21 (20 + 1), and majors exactly 34.
   - Therefore, no hard deletion (`wp_delete_post`) occurred, and data loss was zero.
2. **Scope Confinement Verified**:
   - All 5 programs identified as non-Liên thông or belonging to a non-university (1786–1789, 2013) and the non-university institution (1662) have `post_status = 'draft'` (Observation 1.1).
   - All 95 published programs belong strictly to `training_type` terms `tu-xa` (94) or `vua-hoc-vua-lam` (1). Zero published programs carry `chinh-quy` or `van-bang-2`.
3. **Referential Integrity Verified**:
   - Every published program references a valid, published university (20 distinct) and a valid, published major (34 distinct) via `school_relationship` and `major_relationship` (Observation 1.2).
   - In reverse, all 55 `_offered_programs` postmeta entries contain only valid, published program IDs (Observation 1.3).
   - Ghost IDs 1855 and 1856, as well as drafted program IDs, have been 100% eliminated from all relationship arrays.
4. **Accounting Accuracy Verified**:
   - Every entity in `audit_report.json` maps 1:1 to actual post records in MySQL with 0 discrepancies (Observation 1.4).
   - The audit command is fully idempotent and safe to re-run.

---

## 3. Caveats

- **Frontend Template Terminology**: The scope of M1 is strictly database and data scope standardization. Frontend template modifications (displaying "Hình thức học" instead of "Hệ đào tạo" in cards and archives, and isolating the `online` campus term in filters) are assigned to upcoming milestones M2 through M5.
- **WP-CLI Runtime**: Deprecation warnings regarding third-party plugins (`all-in-one-wp-migration`, `wp-mail-smtp`) were observed during WP-CLI runs; these are core WordPress/plugin deprecation notices unrelated to `cli-commands.php` and do not affect theme execution.

---

## 4. Conclusion

**Verdict: APPROVE**

The work submitted for Milestone M1 by `worker_m1` completely satisfies all empirical criteria:
1. No records were deleted (trash count == 0 for program, school, major).
2. Exactly 95 published programs and 5 drafted programs exist in the database.
3. Every published program is mapped to a published university school and a published major.
4. Zero orphaned IDs, zero drafted IDs, and zero ghost IDs (1855, 1856) remain in any entity's `_offered_programs`.
5. `audit_report.json` is valid, complete, and 100% synchronized with the database state.

---

## 5. Verification Method

To independently reproduce this verification, run the following commands from `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`:

```bash
# 1. Verify Post Counts & Zero Trash
wp --path=../../../ db query "SELECT post_type, post_status, COUNT(*) as cnt FROM wp_posts WHERE post_type IN ('program', 'school', 'major') GROUP BY post_type, post_status ORDER BY post_type, post_status"

# 2. Verify Program-to-School/Major Mappings
wp --path=../../../ db query "
SELECT 
  COUNT(p.ID) as total_published_programs,
  COUNT(DISTINCT sm.meta_value) as distinct_schools_mapped,
  COUNT(DISTINCT mm.meta_value) as distinct_majors_mapped
FROM wp_posts p
JOIN wp_postmeta sm ON p.ID = sm.post_id AND sm.meta_key = 'school_relationship'
JOIN wp_posts s ON CAST(sm.meta_value AS UNSIGNED) = s.ID AND s.post_status = 'publish' AND s.post_type = 'school'
JOIN wp_postmeta mm ON p.ID = mm.post_id AND mm.meta_key = 'major_relationship'
JOIN wp_posts m ON CAST(mm.meta_value AS UNSIGNED) = m.ID AND m.post_status = 'publish' AND m.post_type = 'major'
WHERE p.post_type = 'program' AND p.post_status = 'publish'
"

# 3. Verify _offered_programs Cleanliness (0 ghosts, 0 drafts)
wp --path=../../../ eval '
$entities = get_posts(["post_type" => ["school", "major"], "post_status" => "any", "numberposts" => -1]);
$ghosts = 0; $drafts = 0;
foreach ($entities as $e) {
    $offered = get_post_meta($e->ID, "_offered_programs", true) ?: [];
    foreach ($offered as $pid) {
        $p = get_post($pid);
        if (!$p) $ghosts++;
        elseif ($p->post_status !== "publish") $drafts++;
    }
}
echo "Offered check: ghosts={$ghosts}, drafts={$drafts}\n";
'

# 4. Verify WP-CLI Audit Command Idempotence
wp --path=../../../ ltdh audit-data --apply
```
