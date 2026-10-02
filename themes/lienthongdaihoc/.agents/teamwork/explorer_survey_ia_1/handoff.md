# Handoff Report: CPT Database Survey & Data Scope Analysis (R1 & R2)

- **Agent**: `explorer_survey_ia_1` (Data Scope Explorer)
- **Milestone**: M1 — Discovery & System Survey
- **Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_1/`
- **Target Audience**: Orchestrator, Worker agents, Reviewer / Auditor agents

---

## 1. Observation

### 1.1. CPT & Post Record Inventory
Direct command executed:
```bash
wp --path=../../../ eval '
foreach (["program", "school", "major", "guide", "post", "page"] as $cpt) {
    $c = wp_count_posts($cpt);
    echo "$cpt: publish={$c->publish}, draft={$c->draft}, trash={$c->trash}\n";
}
'
```
**Results observed directly:**
- `program`: `publish=100`, `draft=0`, `trash=0`
- `school`: `publish=21`, `draft=0`, `trash=0`
- `major`: `publish=34`, `draft=0`, `trash=0`
- `guide`: `publish=3`, `draft=0`, `trash=0`
- `post`: `publish=29`, `draft=0`, `trash=0`
- `page`: `publish=9`, `draft=1`, `trash=0` (Draft: `privacy-policy`)

### 1.2. Taxonomy Term Distribution
Direct command executed:
```bash
wp --path=../../../ eval '
foreach (["training_type", "campus", "region", "major_cat"] as $tax) {
    echo "\n=== Taxonomy: $tax ===\n";
    $terms = get_terms(["taxonomy" => $tax, "hide_empty" => false]);
    foreach ($terms as $t) {
        echo "ID: {$t->term_id} | Slug: {$t->slug} | Name: {$t->name} | Count: {$t->count}\n";
    }
}
'
```
**Results observed:**
- **Taxonomy `training_type`** (Label: "Hệ đào tạo" in `inc/post-types.php:80`):
  - ID 7: `tu-xa` | Name: `Từ xa` | Count: 98
  - ID 8: `vua-hoc-vua-lam` | Name: `Vừa học vừa làm` | Count: 1 (Post ID: 1854)
  - ID 21: `chinh-quy` | Name: `Chính quy` | Count: 1 (Post ID: 2013)
  - ID 27: `van-bang-2` | Name: `Văn bằng 2` | Count: 0
  - *Total object relations*: 100
- **Taxonomy `campus`** (Label: "Cơ sở đào tạo" in `inc/post-types.php:80`):
  - ID 9: `ha-noi` | Name: `Hà Nội` | Count: 36 (35 programs + 1 school)
  - ID 10: `ho-chi-minh` | Name: `Hồ Chí Minh` | Count: 33 (33 programs)
  - ID 13: `online` | Name: `Online` | Count: 32 (32 programs)
  - ID 11: `da-nang` | Name: `Đà Nẵng` | Count: 0
  - ID 12: `thai-nguyen` | Name: `Thái Nguyên` | Count: 0
- **Taxonomy `region`**:
  - ID 14: `mien-bac` | Count: 21 (all 21 schools are assigned `mien-bac`)
  - ID 15: `mien-trung` | Count: 0
  - ID 16: `mien-nam` | Count: 0
- **Taxonomy `major_cat`**:
  - ID 22: `kinh-te-quan-ly` | Count: 15
  - ID 23: `ky-thuat-cong-nghe` | Count: 4
  - ID 24: `ngon-ngu-nhan-van` | Count: 3
  - ID 25: `nong-lam-moi-truong` | Count: 7
  - ID 26: `xa-hoi-dich-vu` | Count: 5

### 1.3. School Records & Typology Inspection
Direct scan of all 21 schools:
- **Universities / Academies (20 schools)**:
  - TVU (Trường Đại học Trà Vinh - ID 1611) — 6 programs
  - UNETI (Trường ĐH Kinh tế - Kỹ thuật Công nghiệp - ID 1614) — 5 programs
  - TMU (Trường Đại học Thương mại - ID 1617) — 5 programs
  - NAU (Trường Đại học Kinh tế Nghệ An - ID 1620) — 2 programs
  - INTRACOM (Trường Đại học Chu Văn An - ID 1623) — 3 programs
  - QBU (Trường Đại học Quảng Bình - ID 1626) — 5 programs
  - HAUI (Trường Đại học Công nghiệp Hà Nội - ID 1629) — 3 programs
  - BAV (Học viện Ngân hàng - ID 1632) — 4 programs
  - DNU (Trường Đại học Đại Nam - ID 1635) — 3 programs
  - NEU (Trường Đại học Kinh tế Quốc dân - ID 1638) — 4 programs
  - PTIT (Học viện Công nghệ Bưu chính Viễn thông - ID 1641) — 3 programs
  - TNU (Đại học Thái Nguyên - ID 1644) — 10 programs
  - AOF (Học viện Tài chính - ID 1647) — 2 programs
  - TUT (Trường Đại học Kỹ thuật Công nghiệp Thái Nguyên - ID 1650) — 5 programs
  - HOU (Viện Đại học Mở Hà Nội - ID 1653) — 11 programs
  - TUAF (Trường Đại học Nông Lâm Thái Nguyên - ID 1656) — 10 programs
  - ULSA (Trường Đại học Lao động - Xã hội - ID 1659) — 3 programs
  - HNMU (Trường Đại học Thủ đô Hà Nội - ID 1665) — 3 programs
  - LDA (Trường Đại học Công đoàn - ID 1668) — 6 programs
  - UTC (Trường Đại học Giao thông Vận tải - ID 1853) — 3 programs
- **Junior Colleges (Cao đẳng) (1 school)**:
  - **HCCT (Trường Cao đẳng Thương mại và Du lịch Hà Nội - ID 1662)** — 4 programs:
    - ID 1786: Cử nhân Quản trị kinh doanh
    - ID 1787: Cử nhân Kế toán
    - ID 1788: Cử nhân Thương mại điện tử
    - ID 1789: Cử nhân Quản trị khách sạn

### 1.4. Relationship Metadata & Orphaned References
- In `inc/relationship-hooks.php:12-74`, bidirectional synchronization hooks maintain `_offered_programs` on `school` and `major`.
- When scanning `_offered_programs` across all 21 schools and 34 majors:
  - School ID 1853 (`Trường Đại học Giao thông Vận tải`) has `_offered_programs = [1854, 1855, 1856, 2013, 2014]`.
  - Major ID 1677 (`Công nghệ thông tin`) has `_offered_programs = [1678, 1705, 1718, 1724, 1732, 1757, 1854, 1855, 1856, 2013, 2014]`.
  - **IDs 1855 and 1856 DO NOT EXIST** in the database (deleted during `wp ltdh seed-utc` at `inc/cli-commands.php:753-756` without clearing the reverse array).
  - Direct evidence: `get_post(1855)` and `get_post(1856)` return `NULL`.
- In `single-school.php:273-284`:
  ```php
  if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
      foreach ( $offered_program_ids as $p_id ) {
          $m_id = get_post_meta( $p_id, 'major_relationship', true );
          ...
          $distinct_major_ids[] = $m_id;
      }
  }
  ```
  The code calls `get_post_meta` without checking `get_post_status( $p_id ) === 'publish'`. Thus, orphaned or drafted IDs could inject stale references.

### 1.5. CPT Program Titles and Slugs Structure
- 97 programs follow the generation template from `inc/cli-commands.php:403-428`:
  - `post_title`: `"Cử nhân " . $major_title` (e.g. `Cử nhân Quản trị kinh doanh`)
  - `post_name`: `cu-nhan-[major-slug]-tu-xa-[school-slug]` (e.g. `cu-nhan-ngon-ngu-anh-tu-xa-truong-dai-hoc-cong-doan`)
- 3 programs belong to UTC seeded via `seed_utc` (`inc/cli-commands.php:760-775`):
  - ID 1854: `Cử nhân Công nghệ thông tin (Vừa học vừa làm)` | Slug: `cu-nhan-cong-nghe-thong-tin-vua-hoc-vua-lam-vua-hoc-vua-lam-utc` | Term: `vua-hoc-vua-lam`
  - ID 2013: `Cử nhân Công nghệ thông tin (Liên thông Chính quy)` | Slug: `cu-nhan-cong-nghe-thong-tin-lien-thong-chinh-quy-chinh-quy-utc` | Term: `chinh-quy`
  - ID 2014: `Cử nhân Công nghệ thông tin (Đào tạo từ xa)` | Slug: `cu-nhan-cong-nghe-thong-tin-dao-tao-tu-xa-tu-xa-utc` | Term: `tu-xa`

### 1.6. Term `Online` in Taxonomy `campus`
- Term ID 13 (`online`) is assigned to 32 programs (e.g. IDs 1800, 1795, 1791, 1788, 1785).
- In `inc/core/class-helpers.php:729-732`:
  ```php
  $campuses = wp_get_post_terms($program_id, LTDH_TAX_CAMPUS);
  $campus_name = ! empty($campuses) && ! is_wp_error($campuses) ? implode(', ', wp_list_pluck($campuses, 'name')) : 'Hà Nội';
  ```
  This causes UI cards and program summaries to output `"📍 Cơ sở: Online"`, treating a remote delivery mode as a physical campus location.

### 1.7. Routing & Redirect Findings
- `inc/core/class-rewrite-rules.php:247-254`:
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
  Every request to `/chuong-trinh/` is hard-redirected with 301 to `/he-dao-tao/tu-xa/`. This causes the forced redirect cited in R3.
- `front-page.php:126`:
  ```php
  <form action="<?php echo esc_url(home_url('/he-dao-tao/tu-xa/')); ?>" method="GET" class="space-y-3 md:space-y-0">
  ```
  The homepage filter form submits directly to `/he-dao-tao/tu-xa/` instead of `/chuong-trinh/` or dynamic action.
- `header.php:27-35`: Uses `wp_nav_menu( ['theme_location' => 'primary-menu'] )`, which loads Header Navigation Menu (ID 3). Header Navigation Menu has:
  - `Trang chủ` (`/`)
  - `Trường đối tác` (`/truong-doi-tac`)
  - `Chuyên ngành` (`/nganh-hoc/`)
  - `Hệ đào tạo` (`/he-dao-tao`)
  - `Tin tức` (`/tin-tuc`)
  - `Kiểm tra điều kiện` (`/kiem-tra-dieu-kien/`)

---

## 2. Logic Chain

```
[Observation 1.1 & 1.2: 100 program records in DB, exactly 98 tu-xa, 1 vua-hoc-vua-lam, 1 chinh-quy, 0 van-bang-2]
                                 │
                                 ▼
[Request R1: lienthongdaihoc.com serves 100% "Liên thông đại học" via 2 training forms: "Từ xa" & "Vừa học vừa làm"]
                                 │
        ┌────────────────────────┴────────────────────────┐
        ▼                                                 ▼
[Program 2013 has term chinh-quy]              [Programs 1786-1789 belong to School 1662]
        │                                                 │
        ▼                                                 ▼
[Chính quy is NOT in (Từ xa, VHVL);             [School 1662 is HCCT (Cao đẳng), NOT an]
Explicitly listed as out-of-scope in R1]         [accredited Đại học; awards no Cử nhân]
        │                                                 │
        └────────────────────────┬────────────────────────┘
                                 │
                                 ▼
[Exactly 5 programs are OUT-OF-SCOPE: IDs 2013, 1786, 1787, 1788, 1789]
                                 │
                                 ▼
[R1 rule: Out-of-scope records must be transitioned to post_status = 'draft' (NEVER deleted)]
                                 │
                                 ▼
[Impact on Majors: 0 majors reach 0 programs (all 34 majors retain >= 1 active program)]
[Impact on Schools: School 1662 (HCCT) reaches 0 active programs; School 1662 must also be drafted]
                                 │
                                 ▼
[Observation 1.4: IDs 1855 & 1856 are orphaned ghost IDs in _offered_programs of School 1853 & Major 1677]
                                 │
                                 ▼
[Single-school & Single-major query routines must NOT trust raw _offered_programs without status verification]
[Audit script must purge orphaned IDs 1855, 1856 and clean drafted programs from _offered_programs]
                                 │
                                 ▼
[Observation 1.6: 32 programs have campus: online -> oxymoron "Cơ sở: Online"]
                                 │
                                 ▼
[R2 rule: campus online term must be isolated from physical campus filters and cards]
                                 │
                                 ▼
[Observation 1.7: class-rewrite-rules.php lines 247-254 enforce 301 from /chuong-trinh/ to /he-dao-tao/tu-xa/]
                                 │
                                 ▼
[R3 rule: remove forced 301; allow /chuong-trinh/ as canonical program archive or preserve indexed aliases]
```

---

## 3. Detailed Survey & Data Classification

### 3.1. Inventory Matrix

| CPT | Total Records | Published | Draft | In-Scope (Liên thông) | Out-of-Scope | Action Required |
|---|---|---|---|---|---|---|
| `program` | 100 | 100 | 0 | **95** (94 Từ xa, 1 VHVL) | **5** (1 Chính quy, 4 Cao đẳng) | Set 5 out-of-scope to `draft` |
| `school` | 21 | 21 | 0 | **20** (Universities) | **1** (Cao đẳng HCCT) | Set School 1662 to `draft` |
| `major` | 34 | 34 | 0 | **34** (All retain programs) | **0** | Keep all 34 `publish` |
| `guide` | 3 | 3 | 0 | **3** | 0 | Keep `publish` |
| `page` | 10 | 9 | 1 | 9 | 0 | Keep existing pages |

---

### 3.2. Detailed Program Classification

#### Group A: In-Scope Programs (Valid Liên thông Đào tạo) — Total: 95 Records
- **Hình thức "Vừa học vừa làm" (`vua-hoc-vua-lam`)**: 1 program
  - ID 1854: *Cử nhân Công nghệ thông tin (Vừa học vừa làm)* — Trường Đại học Giao thông Vận tải (UTC)
- **Hình thức "Từ xa" (`tu-xa`)**: 94 programs across 19 universities:
  - **Viện Đại học Mở Hà Nội (HOU)** (11 programs): Kế toán (1749), QTKD (1750), Quản trị DV Du lịch & Lữ hành (1752), Quản trị khách sạn (1754), Thương mại điện tử (1755), Tài chính Ngân hàng (1756), CNTT (1757), Luật kinh tế (1758), Luật (1759), Ngôn ngữ Anh (1760), Ngôn ngữ Trung (1761).
  - **Đại học Thái Nguyên (TNU)** (10 programs): Luật kinh tế (1729), QTKD (1730), Kế toán (1731), CNTT (1732), Tài chính Ngân hàng (1733), Kỹ thuật ĐTVT (1734), TMĐT & Marketing số (1736), Luật (1737), Ngôn ngữ Anh (1727), Ngôn ngữ Trung (1728).
  - **Trường Đại học Nông Lâm Thái Nguyên (TUAF)** (10 programs): CN Thực phẩm tiếng Anh (1763), CN Sinh học (1765), Nông nghiệp công nghệ cao (1767), CN Thực phẩm (1769), KH Môi trường (1771), Kinh tế nông nghiệp (1773), Quản lý đất đai (1775), Bất động sản (1777), Thú Y (1779), Quản lý TNTN & Môi trường (1781).
  - **Trường Đại học Trà Vinh (TVU)** (6 programs): QTKD (1672), Kế toán (1674), Luật (1676), CNTT (1678), Ngôn ngữ Anh (1680), Thương mại điện tử (1682).
  - **Trường Đại học Công đoàn (LDA)** (6 programs): Luật (1793), Bảo hộ lao động (1795), Quản trị nhân lực (1797), Việt Nam học (1799), Công tác xã hội (1800), Ngôn ngữ Anh (1801).
  - **Trường ĐH Kinh tế - Kỹ thuật Công nghiệp (UNETI)** (5 programs): Ngôn ngữ Anh (1684), Kế toán (1685), QTKD (1687), Kinh doanh thương mại (1689), Marketing (1690).
  - **Trường Đại học Thương mại (TMU)** (5 programs): QTKD (1691), Marketing (1693), Logistics (1695), Luật kinh tế (1696), TMĐT (1697).
  - **Trường Đại học Quảng Bình (QBU)** (5 programs): QTKD (1702), Kế toán (1703), CNTT (1705), Luật kinh tế (1707), Ngôn ngữ Anh (1708).
  - **Trường ĐH Kỹ thuật Công nghiệp Thái Nguyên (TUT)** (5 programs): Kỹ thuật xây dựng (1741), Ngôn ngữ Anh (1742), Kỹ thuật máy tính (1744), Kinh tế công nghiệp (1746), Quản lý công nghiệp (1748).
  - **Trường Đại học Kinh tế Quốc dân (NEU)** (4 programs): QTKD (1716), Kế toán (1717), CNTT (1718), Luật (1720).
  - **Học viện Ngân hàng (BAV)** (4 programs): QTKD (1712), Kế toán (1713), Tài chính Ngân hàng (1714), Ngôn ngữ Anh (1715).
  - **INTRACOM (Trường Đại học Chu Văn An)** (3 programs): QTKD (1699), Kế toán (1700), Luật kinh tế (1701).
  - **Trường Đại học Công nghiệp Hà Nội (HAUI)** (3 programs): QTKD (1709), Kế toán (1710), Ngôn ngữ Trung (1711).
  - **Trường Đại học Đại Nam (DNU)** (3 programs): QTKD (1721), Kế toán (1722), CNTT (1724).
  - **Học viện Công nghệ Bưu chính Viễn thông (PTIT)** (3 programs): CNTT (1723), Kỹ thuật ĐTVT (1725), QTKD (1726).
  - **Trường Đại học Lao động - Xã hội (ULSA)** (3 programs): Ngôn ngữ Anh (1782), Luật kinh tế (1783), Công tác xã hội (1785).
  - **Trường Đại học Thủ đô Hà Nội (HNMU)** (3 programs): Luật (1790), Logistics (1791), Ngôn ngữ Trung (1792).
  - **Trường Đại học Kinh tế Nghệ An (NAU)** (2 programs): QTKD (1698), Kế toán (1704).
  - **Học viện Tài chính (AOF)** (2 programs): Kế toán (1738), QTKD (1739).
  - **Trường Đại học Giao thông Vận tải (UTC)** (1 program): Cử nhân CNTT (Đào tạo từ xa) (2014).

#### Group B: Out-of-Scope Programs — Total: 5 Records

| Post ID | Title | Slug | Associated School | Training Type Term | Reason for Out-of-Scope | Safe Target Status |
|---|---|---|---|---|---|---|
| **2013** | Cử nhân Công nghệ thông tin (Liên thông Chính quy) | `cu-nhan-cong-nghe-thong-tin-lien-thong-chinh-quy-chinh-quy-utc` | Trường ĐH Giao thông Vận tải (ID 1853) | `chinh-quy` | R1 & R3 specify that only "Từ xa" and "Vừa học vừa làm" are in-scope. Chính quy is explicitly excluded. | `draft` |
| **1786** | Cử nhân Quản trị kinh doanh | `cu-nhan-quan-tri-kinh-doanh-tu-xa-truong-cao-dang-thuong-mai-va-du-lich-ha-noi` | Trường Cao đẳng Thương mại và Du lịch Hà Nội (ID 1662) | `tu-xa` | HCCT is a junior college (Cao đẳng), not an accredited university. Cao đẳng online is out-of-scope. | `draft` |
| **1787** | Cử nhân Kế toán | `cu-nhan-ke-toan-tu-xa-truong-cao-dang-thuong-mai-va-du-lich-ha-noi` | Trường Cao đẳng Thương mại và Du lịch Hà Nội (ID 1662) | `tu-xa` | Same as above. | `draft` |
| **1788** | Cử nhân Thương mại điện tử | `cu-nhan-thuong-mai-dien-tu-tu-xa-truong-cao-dang-thuong-mai-va-du-lich-ha-noi` | Trường Cao đẳng Thương mại và Du lịch Hà Nội (ID 1662) | `tu-xa` | Same as above. | `draft` |
| **1789** | Cử nhân Quản trị khách sạn | `cu-nhan-quan-tri-khach-san-tu-xa-truong-cao-dang-thuong-mai-va-du-lich-ha-noi` | Trường Cao đẳng Thương mại và Du lịch Hà Nội (ID 1662) | `tu-xa` | Same as above. | `draft` |

#### Group C: Out-of-Scope Institutions — Total: 1 Record

| School ID | School Title | Slug | School Code | Reason for Out-of-Scope | Safe Target Status |
|---|---|---|---|---|---|
| **1662** | Trường Cao đẳng Thương mại và Du lịch Hà Nội | `truong-cao-dang-thuong-mai-va-du-lich-ha-noi` | HCCT | Junior college (Cao đẳng). All 4 of its programs are out-of-scope. Retaining it as published would display an institution with 0 programs on `/truong-doi-tac/`. | `draft` |

#### Group D: Uncertain / Ambiguous Programs — Total: 0 Records
- All 100 programs have valid school relations, valid major relations, and identifiable training types.
- No orphan records or unmapped posts exist.

---

## 4. Technical Specifications for the Audit Script (R1 Implementation)

### 4.1. Command Specification
The script should be executable via WP-CLI:
```bash
wp ltdh audit-data [--dry-run] [--apply] [--output-file=audit_report.json]
```

### 4.2. Algorithmic Steps for Audit Script
1. **Step 1: Scan and Categorize CPT `program`**:
   - Query all 100 `program` records (`post_status => any`).
   - For each program:
     - Check linked `school_relationship`. If school title contains `"Cao đẳng"` or slug contains `"cao-dang"` -> Mark `OUT_OF_SCOPE_COLLEGE`.
     - Check `training_type` taxonomy term. If slug is `chinh-quy` -> Mark `OUT_OF_SCOPE_FULLTIME`.
     - If slug is `van-bang-2` -> Mark `OUT_OF_SCOPE_VB2`.
     - If slug is `tu-xa` or `vua-hoc-vua-lam` and school is a university -> Mark `IN_SCOPE`.
     - Otherwise -> Mark `MANUAL_REVIEW`.
2. **Step 2: Safe Transition to `draft` (When `--apply` is set)**:
   - For all records categorized as `OUT_OF_SCOPE_*`:
     ```php
     wp_update_post([
         'ID'          => $program_id,
         'post_status' => 'draft',
     ]);
     update_post_meta( $program_id, '_ltdh_audit_status', 'out_of_scope' );
     update_post_meta( $program_id, '_ltdh_audit_reason', $reason );
     update_post_meta( $program_id, '_ltdh_audited_at', gmdate( 'Y-m-d H:i:s' ) );
     ```
   - **Crucial Rule**: NEVER call `wp_delete_post()`. All SEO links, comments, attachments, and historical post data remain preserved.
3. **Step 3: Handle Out-of-Scope Schools**:
   - Scan CPT `school`.
   - If a school has 0 in-scope programs remaining (specifically HCCT ID 1662):
     ```php
     wp_update_post([
         'ID'          => 1662,
         'post_status' => 'draft',
     ]);
     update_post_meta( 1662, '_ltdh_audit_status', 'out_of_scope_institution' );
     ```
4. **Step 4: Purge Orphaned & Drafted IDs from `_offered_programs` Metadata**:
   - For all schools and majors:
     - Retrieve current `_offered_programs`.
     - Filter the array to ONLY include program IDs that:
       1) Actually exist in DB (`get_post($pid)` is not null).
       2) Have `post_status === 'publish'`.
       3) Have `post_type === 'program'`.
     - Re-save `update_post_meta( $object_id, '_offered_programs', $clean_ids )`.
   - This cleanly fixes the orphaned IDs 1855, 1856 on UTC (1853) and CNTT (1677), and prevents drafted programs (2013, 1786-1789) from bleeding into frontend queries.
5. **Step 5: Flush Transients & Query Caches**:
   - Delete transient `ltdh_filter_options`.
   - Delete transient `ltdh_featured_schools`.
   - Delete rewrite transient `ltdh_rewrite_flushed_v2`.
6. **Step 6: Generate `audit_report.json`**:
   - Write out complete JSON reporting summary numbers, affected IDs, old status, new status, reasons, and timestamps.

---

## 5. Specifications for Taxonomy, Campus `Online`, and Routing (R2, R3, R4)

### 5.1. Taxonomy `training_type` Display Normalization (R3)
- Front-facing label must be changed from **"Hệ đào tạo"** to **"Hình thức học"**.
- Code locations to update:
  - `inc/post-types.php:92`: Label in taxonomy registration.
  - `archive-program.php:172, 250`: Change `"Hệ đào tạo"` to `"Hình thức học"`.
  - `taxonomy-training_type.php:172, 250`: Change `"Hệ đào tạo"` to `"Hình thức học"`.
  - `taxonomy.php:26, 90`: Change `"Hệ đào tạo"` to `"Hình thức học"`.
  - `front-page.php:159`: Change `"-- Chọn hệ học --"` to `"-- Chọn hình thức học --"`.
  - `template-parts/banner.php:26, 78, 90`: Change banner titles from `"Hệ Đào Tạo"` to `"Hình thức học"`.
- Slugs to preserve for SEO: `/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`.

### 5.2. Campus `Online` Isolation (R2)
- Term ID 13 (`online`) must not appear as a physical facility in campus dropdowns or location filters.
- In `ltdh_get_program_learning_details()` (`inc/core/class-helpers.php:730`):
  - If a program has `campus: online`, do not display `"📍 Cơ sở: Online"`. Instead display `"📍 Toàn quốc (Trực tuyến)"` or omit the physical location pin and emphasize `"Hình thức: Học online 100%"`.
- In any physical location filter queries (e.g. `archive-school.php` or future station directory):
  - Explicitly exclude `campus` term `online` (`'tax_query' => [['taxonomy' => 'campus', 'field' => 'slug', 'terms' => ['online'], 'operator' => 'NOT IN']]`).

### 5.3. Removal of Forced 301 on `/chuong-trinh/` (R3)
- In `inc/core/class-rewrite-rules.php:247-254`:
  - **Remove lines 247-254 completely**:
    ```php
    // REMOVE THIS BLOCK:
    if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
        $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
        ...
        wp_redirect( $redirect_url, 301 );
        exit;
    }
    ```
  - Ensure `/chuong-trinh/` serves the `archive-program.php` template cleanly without redirection.
  - Update `front-page.php:126` search form action from `home_url('/he-dao-tao/tu-xa/')` to `home_url('/chuong-trinh/')`.

### 5.4. Navigation Menu Standardization (R4)
- Header Navigation Menu (ID 3) items must be updated to:
  1. **Trang chủ** (`/`)
  2. **Liên thông** (Dropdown or parent link: Từ xa `/he-dao-tao/tu-xa/`, Vừa học vừa làm `/he-dao-tao/vua-hoc-vua-lam/`)
  3. **Ngành học** (`/nganh-hoc/`)
  4. **Trường đại học** (`/truong-doi-tac/` or `/truong-dai-hoc/`)
  5. **Kiến thức liên thông** (`/cam-nang/` or `/huong-dan/`)
  6. **Tư vấn** (`/dang-ky-tu-van/` or `/lien-he/`)
- Update fallback definitions in `inc/config/class-defaults.php:33-56`.

---

## 6. Caveats

1. **Intracom School Name (ID 1623)**:
   School ID 1623 is currently titled `INTRACOM`. In reality, this record corresponds to **Trường Đại học Chu Văn An** (DCA, acquired by Intracom Group). The bank transfer info, address in Phố Hiến - Hưng Yên, and admission targets all belong to Đại học Chu Văn An. The title `INTRACOM` should eventually be renamed to `Trường Đại học Chu Văn An` in content curation to improve candidate trust and SEO, but it is a valid in-scope university.
2. **Guide Post 63**:
   Guide ID 63 is titled *"Văn bằng 2 là gì? Ai nên học?"*. While CPT `program` has 0 Văn bằng 2 programs, informational guide articles explaining differences between Liên thông and Văn bằng 2 are educational/SEO content and can remain published unless the orchestrator decides to unpublish non-liên thông guide posts.
3. **Campus Term Cleanup**:
   Term `online` (ID 13) in taxonomy `campus` should NOT be deleted via `wp_delete_term()` immediately because existing post-term relationships would trigger database writes and may break cached queries. Isolating it at the presentation and query layer is significantly safer.

---

## 7. Conclusion

1. **System Health & Scope**: The database contains 100 `program` records, 21 `school` records, and 34 `major` records. All are currently `publish`.
2. **Data Scope Split**:
   - **95 programs** are genuine in-scope **Liên thông đại học** (94 Từ xa, 1 Vừa học vừa làm) across 20 accredited universities.
   - **5 programs** are out-of-scope: 1 is Chính quy (ID 2013), and 4 belong to a junior college (HCCT IDs 1786–1789).
   - **1 school** is out-of-scope: HCCT (ID 1662), which has 0 valid university bachelor programs.
   - **0 majors** become empty: all 34 majors have between 1 and 14 published university programs remaining.
3. **Data Integrity Issue**: Stale orphaned IDs `1855` and `1856` in `_offered_programs` must be pruned during the audit execution.
4. **Actionability**: All findings are backed by line numbers and database verification. The audit script specification provided in Section 4 is ready for immediate implementation by the Worker agent.

---

## 8. Verification Method

To independently verify the observations and findings in this report, run the following WP-CLI commands from the theme directory:

```bash
# 1. Verify program training types breakdown
wp --path=../../../ eval '
$counts = [];
foreach (get_posts(["post_type" => "program", "numberposts" => -1]) as $p) {
    $t = wp_get_post_terms($p->ID, "training_type", ["fields" => "slugs"]);
    $k = !empty($t) ? $t[0] : "none";
    $counts[$k] = ($counts[$k] ?? 0) + 1;
}
print_r($counts);
'

# 2. Verify the 5 out-of-scope programs
wp --path=../../../ eval '
$ids = [2013, 1786, 1787, 1788, 1789];
foreach ($ids as $id) {
    $p = get_post($id);
    $sid = get_post_meta($id, "school_relationship", true);
    $s = get_post($sid);
    $tt = wp_get_post_terms($id, "training_type", ["fields" => "names"]);
    echo "ID: $id | Title: {$p->post_title} | School: {$s->post_title} | TT: " . implode(",", $tt) . "\n";
}
'

# 3. Verify orphaned IDs in _offered_programs
wp --path=../../../ eval '
$s1853 = get_post_meta(1853, "_offered_programs", true);
$m1677 = get_post_meta(1677, "_offered_programs", true);
echo "1855 exists: " . (get_post(1855) ? "YES" : "NO") . "\n";
echo "1856 exists: " . (get_post(1856) ? "YES" : "NO") . "\n";
echo "In School 1853: " . (in_array(1855, $s1853) ? "FOUND" : "NOT FOUND") . "\n";
echo "In Major 1677: " . (in_array(1855, $m1677) ? "FOUND" : "NOT FOUND") . "\n";
'

# 4. Verify forced 301 redirect code location
grep -n -C 5 "chuong-trinh" inc/core/class-rewrite-rules.php
```
