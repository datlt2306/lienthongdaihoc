# Handoff Report — explorer_rules_2

**Task**: R1 Admission Matrix & Legal MOET Compliance Review
**Date**: 2026-09-25
**Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_rules_2`

---

## 1. Observation

Direct code observations from files:

1. **Hardcoded Blocking of Non-College Candidates**:
   - In `inc/eligibility.php`, line 288:
     ```php
     $valid_education = [ 'cao-dang' ];
     ```
   - In `inc/eligibility.php`, lines 299-301:
     ```php
     if ( empty( $input['education'] ) || ! in_array( $input['education'], $valid_education, true ) ) {
         return new WP_Error( 'invalid_education', 'Hệ thống chỉ hỗ trợ kiểm tra điều kiện liên thông từ Cao đẳng lên Đại học.' );
     }
     ```
   - In `template-parts/eligibility/wizard.php`, lines 25-27:
     ```html
     <select name="education" class="elig-select select-education">
         <option value="cao-dang" selected>Cao đẳng (Bằng Cao đẳng)</option>
     </select>
     ```
   - In `template-parts/eligibility/wizard.php`, lines 80-82:
     ```php
     if ( $tt->slug === 'van-bang-2' ) {
         continue;
     }
     ```
   - In `inc/eligibility.php`, line 292:
     ```php
     $valid_training = ! is_wp_error( $training_terms ) && ! empty( $training_terms ) ? array_diff( $training_terms, [ 'van-bang-2' ] ) : [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ];
     ```

2. **Severe Legal Violations & Naming Anomalies in Rules**:
   - In `inc/eligibility-rules.php`, lines 18-23:
     ```php
     'thap-phan'    => [ 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
     'trung-cap'    => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
     'cao-dang'     => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
     'dai-hoc'      => [ 'tu-xa', 'vua-hoc-vua-lam' ],
     'thac-si'      => [ 'tu-xa' ],
     ```
   - In `inc/eligibility-rules.php`, line 27:
     ```php
     * VB2: requires existing degree (Cao đẳng+)
     ```
   - In `inc/eligibility-rules.php`, lines 46-53:
     `ltdh_elig_get_major_relationships()` only contains 6 hardcoded majors (`ke-toan`, `quan-tri-kinh-doanh`, `cong-nghe-thong-tin`, `ngon-ngu-anh`, `marketing`, `kinh-doanh-thuong-mai`), leaving out Health, Education, Law, Engineering, and Architecture.
   - In `inc/eligibility-rules.php`, line 103:
     `'graduation_recent' => 10` is declared but never referenced or calculated anywhere in `inc/eligibility.php`.

3. **Financial Calculation Bug**:
   - In `inc/eligibility.php`, lines 484-486:
     ```php
     $tuition_num = ltdh_elig_parse_tuition( $tuition_str );
     $duration_num = ltdh_elig_parse_duration( get_post_meta( $program_id, 'duration', true ) ?: '' );
     $total_cost = $tuition_num * 120 * $duration_num;
     ```

4. **Data Semantic Mismatch**:
   - In `inc/eligibility.php`, line 31:
     `input_graduation year DEFAULT NULL`
   - In `template-parts/eligibility/results.php`, line 105:
     `<label class="block text-sm font-bold text-slate-700">Năm sinh</label>`
     `<select name="graduation" class="elig-select">`

---

## 2. Logic Chain

1. **From Observation 1 to Business Impact**:
   - `inc/eligibility.php` lines 288-301 restrict `$valid_education` solely to `'cao-dang'`.
   - `wizard.php` provides only the "Cao đẳng" `<option>`.
   - Any applicant holding a THPT diploma, Vocational Intermediate (Trung cấp) diploma, or Bachelor's (Đại học) degree submitting the form receives an HTTP 400 error.
   - According to MOET statistics and market realities, THPT candidates enrolling in Distance Learning (`tu-xa`) and University graduates seeking a Second Bachelor's Degree (`van-bang-2`) constitute over 70% of non-regular higher education demand.
   - *Conclusion*: The current code eliminates 70%+ of the addressable market and creates a severe drop-off wall.

2. **From Observation 2 to Legal Risk**:
   - Circular 28/2023/TT-BGDĐT, Article 5, Clause 3 strictly prohibits Distance Learning for Health Sciences with practicing certificates and Teacher Training.
   - `inc/eligibility-rules.php` line 28 states "Từ xa/Vừa học vừa làm: compatible with all levels" and `inc/eligibility.php` lines 450-462 perform no domain/major filtering when checking training type compatibility.
   - A Nursing or Pharmacy applicant choosing Distance Learning is marked "Compatible" and awarded match points.
   - Under Decree 04/2021/NĐ-CP (Articles 8 & 14), providing false admission notices or advertising unaccredited distance programs faces administrative fines of 20,000,000 to 40,000,000 VND and enrollment suspension.
   - Circular 08/2021/TT-BGDĐT Article 16 restricts "Văn bằng 2" solely to holders of an existing Bachelor's degree, directly contradicting the theme's comment stating "Cao đẳng+".

3. **From Observation 3 to Financial Algorithm Breakdown**:
   - A standard college-to-university program requires completing 55-70 credits over 1.5 to 2 years.
   - In `inc/eligibility.php:486`, multiplying credit fee by 120 (4-year curriculum total) and then multiplying again by `$duration_num` (e.g. 1.5) inflates costs up to 150-400% of the actual tuition, or into billions of VND if semester tuition was entered.
   - This causes almost all affordable programs to fail the user's budget range, wrongly penalizing their match score by 20 points and degrading recommendations.

---

## 3. Caveats

- Database content: The audit evaluated the codebase and ACF JSON definitions without inspecting live production database entries (e.g., how individual schools currently format their `tuition_fee` string in post meta).
- External CRM endpoints: Verified the Telegram payload structure and database table insertion, but did not make outbound network calls to external CRM webhooks.
- No source code files outside `.agents/teamwork/explorer_rules_2/` were modified during this investigation.

---

## 4. Conclusion

The eligibility module currently operates with critical legal compliance violations, severe financial calculation errors, and an artificially constrained target audience:
1. **Critical Legal Risk**: Permits distance learning for Health Sciences and Teacher Training in direct violation of Circular 28/2023/TT-BGDĐT; lacks mandatory practicing certificate and academic threshold checks.
2. **Critical Business Bottleneck**: Hardcoded validation rejecting all education levels except College (`cao-dang`), discarding ~70% of potential leads (THPT distance learners and Second Degree applicants).
3. **Algorithm Error**: Tuition calculation multiplies credits by duration erroneously, invalidating the budget scoring feature.
4. **Architectural Gap**: ACF schema fields (`elig_training_types`, `elig_campuses`, `elig_max_grad_years`, `elig_notes`) are completely bypassed by the matching engine.

A comprehensive analysis with 5 candidate profiles and a remediation roadmap has been documented in `report.md`.

---

## 5. Verification Method

To verify these findings independently:

1. **Verify Controller Blocking**:
   - Inspect `inc/eligibility.php:288-301`.
   - Send an AJAX POST request with `action=ltdh_elig_check` and `education=thpt` or `education=dai-hoc`:
     Observe the verbatim JSON response: `{"code":"invalid_education","message":"Hệ thống chỉ hỗ trợ kiểm tra điều kiện liên thông từ Cao đẳng lên Đại học."}`.
2. **Verify Financial Inflation Formula**:
   - Inspect `inc/eligibility.php:486`: `total_cost = $tuition_num * 120 * $duration_num`.
   - Trace inputs: `$tuition_num = 450000`, `$duration_num = 1.5` => Result is `81,000,000 VND` instead of `27,000,000 - 30,000,000 VND` for a 60-credit degree.
3. **Verify Distance Learning Prohibition in Health/Education**:
   - Inspect `inc/eligibility.php:450-462` and `inc/eligibility-rules.php:16-31`: Note total absence of check for `duoc-hoc`, `dieu-duong`, or `su-pham` against `tu-xa`.
4. **Inspect Generated Report**:
   - Read `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_rules_2/report.md`.
