# Empirical Challenge & Verification Report: Milestone M2 (Campus Isolation & Data Flow)

- **Challenger**: `challenger_m2_2` (M2 Campus Isolation Challenger)
- **Roles**: critic, specialist
- **Date**: 2026-10-01
- **Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m2_2/`
- **Target Work Product**: `worker_m2` implementation (`inc/core/class-helpers.php`, `inc/comparison.php`, `taxonomy.php`, `single-program.php`, `single-school.php`, `single-major.php`)
- **Verdict**: **`APPROVE`**

---

## 1. Observation

### 1.1. Syntax Verification (`php -l` and Token AST)
- Ran full PHP lint check across the entire theme directory:
  ```bash
  find . -name "*.php" -exec php -l {} + | grep -v "No syntax errors"
  ```
  **Verbatim output**: Clean (0 syntax errors across 100% of theme PHP files).
- Specifically checked `taxonomy.php:220`:
  ```bash
  php -l taxonomy.php
  ```
  **Verbatim output**: `No syntax errors detected in taxonomy.php`.
- Inspected verbatim line 220 in `taxonomy.php`:
  ```html
  <a href="<?php the_permalink(); ?>" class="bg-brand-accent text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#e06e00] shadow-sm shadow-brand-accent/10 transition-all">Đăng ký học</a>
  ```
  The previous syntax corruption (`<"'?php the_permalink(); ?>"'>" `) was verified as completely eliminated.
- Evaluated token stream via `token_get_all()`: 0 `T_BAD_CHARACTER` tokens found; line evaluates and renders valid HTML anchor:
  `<a href="https://lienthongdaihoc.com/test-url-1800" class="bg-brand-accent text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#e06e00] shadow-sm shadow-brand-accent/10 transition-all">Đăng ký học</a>`.

### 1.2. Program Learning Details Helper (`inc/core/class-helpers.php:729-785`)
- Function inspected: `ltdh_get_program_learning_details( int $program_id ): array`.
- Empirically evaluated on programs with `campus = online` (seeded program IDs 1800, 1795, 1791, 1785 from `audit_report.json`):
  - **Program 1800** (Cử nhân Công tác xã hội, ĐH Công đoàn, Từ xa):
    - `$details['campus']`: `'Toàn quốc'` (verbatim).
    - Contains 'Online': `FALSE` (`stripos` check passed).
    - `$details['mode']`: `'Học online 100%'` (verbatim).
  - **Program 1795** (Cử nhân Bảo hộ lao động, ĐH Công đoàn, Từ xa):
    - `$details['campus']`: `'Toàn quốc'` (verbatim).
    - Contains 'Online': `FALSE`.
  - **Program 1791** (Cử nhân Logistics, ĐH Thủ đô Hà Nội, Từ xa):
    - `$details['campus']`: `'Toàn quốc'` (verbatim).
    - Contains 'Online': `FALSE`.
  - **Program 1785** (Cử nhân Công tác xã hội, ĐH Lao động - Xã hội, Từ xa):
    - `$details['campus']`: `'Toàn quốc'` (verbatim).
    - Contains 'Online': `FALSE`.

### 1.3. Adversarial Edge Cases on `ltdh_get_program_learning_details()`
- **Mixed Campuses** (tagged with `['online', 'ha-noi', 'ho-chi-minh']`):
  - Strips `'Online'` completely.
  - Returns verbatim physical locations: `'Hà Nội, TP. Hồ Chí Minh'`.
- **Casing & Whitespace Variations** (tagged with slug `'oNLiNe'`, name `' ONLINE '`):
  - Case-insensitive check `('online' === strtolower($slug) || 'online' === strtolower($name))` successfully stripped term and defaulted to `'Toàn quốc'`.
- **Non-`tu-xa` Program with `online` Tag** (`vua-hoc-vua-lam`):
  - Strips `'Online'`, resolves school region (`LTDH_TAX_REGION`): returned `'Miền Bắc'`.
  - `$details['mode']`: `'Học tập trung / Cuối tuần'`.
- **Zero Campus Terms Tagged**:
  - `tu-xa` program defaults cleanly to `'Toàn quốc'`.
- **Physical Campus Only** (tagged `'da-nang'`):
  - Preserves verbatim `'Đà Nẵng'`.
- **Invalid / Missing Post IDs** (e.g. `999999`, `0`, `-1`):
  - Safely returns array `['campus' => 'Toàn quốc', 'mode' => 'Học tập trung / Cuối tuần']` without PHP Warning, Notice, or Fatal error.

### 1.4. Comparison Module (`inc/comparison.php`)
- Function inspected: `ltdh_compare_resolve_program( $program_id )`.
- Verified return structure across program IDs 1800, 1795, 1791, 1785:
  - `$item['campus']`: `'Toàn quốc'`.
  - `$item['campus_info']`: `'Toàn quốc'`.
  - `$item['learning_mode']`: `'Học online 100%'`.
  - 'Online' search in `$item['campus_info']`: `FALSE`.
- Verified Desktop Comparison Table (`template-parts/compare/program-table.php` row `'campus_info'` / `'Cơ sở học'`):
  - Cell renderer output for IDs 1800, 1795, 1791, 1785 renders `'Toàn quốc'`.
  - Never outputs `'Online'` under "Cơ sở học" / "Cơ sở / Trạm đào tạo".
- Verified Mobile Comparison Cards (`template-parts/compare/program-cards.php` section `'campus_info'` / `'Cơ sở'`):
  - Card output for IDs 1800, 1795, 1791, 1785 renders `'Toàn quốc'`.
  - Never outputs `'Online'`.

### 1.5. Defense-in-Depth Campus Guard (`single-program.php:266-274`)
- Verbatim code inspected:
  ```php
  $display_campus = $learning_details['campus'] ?? '';
  if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
      $display_campus = 'Toàn quốc';
  }
  echo esc_html( $display_campus );
  ```
  Even if upstream data were corrupted, any `'Online'` string or empty string is defensively forced to `'Toàn quốc'`.

### 1.6. Rollup Exclusivity & Major Counts
- **`ltdh_get_school_training_types()`**:
  - Direct school taxonomy terms check (Step 1) was eliminated.
  - Empirically tested with School 1650: school post was assigned static legacy terms `'chinh-quy'` and `'van-bang-2'`.
  - Function queried published in-scope programs and returned strictly `['tu-xa', 'vua-hoc-vua-lam']` / `['Từ xa', 'Vừa học vừa làm']`.
  - Static legacy terms `'chinh-quy'` and `'van-bang-2'` were strictly excluded.
- **`ltdh_get_school_unique_majors_count()`**:
  - Tested on school with multiple programs sharing major relationships and a draft program.
  - Returns exactly distinct count of published in-scope majors (2), ignoring drafts and duplicate associations.

---

## 2. Logic Chain

1. **Premise**: Per `ORIGINAL_REQUEST.md` (Milestone M2) and dispatch instructions, campus isolation requires that the delivery mechanism term `Online` must never appear as a physical campus/facility. All programs tagged with `campus = online` must render as `'Toàn quốc'` or physical stations. Comparison table helper `inc/comparison.php` must never display 'Online' under "Cơ sở / Trạm đào tạo". `taxonomy.php:220` must have zero syntax errors.
2. **Observation**: Execution of `tests/test-m2-empirical.php` ran 46 automated assertions across 6 test suites with 0 failures and 0 notices.
3. **Observation**: `ltdh_get_program_learning_details()` filters out any term where `strtolower($slug) === 'online'` or `strtolower($name) === 'online'`. For `tu-xa` programs without physical stations, it defaults to `'Toàn quốc'`. For mixed stations, it returns the physical stations and strips `'Online'`.
4. **Observation**: In `inc/comparison.php:170-181`, `$campus_terms` loops over terms and excludes any where slug or trimmed name is `'online'`, falling back to `$learning_details['campus']`. Table and card templates consume `$item['campus_info']`.
5. **Observation**: `php -l taxonomy.php` and AST tokenizer verify that line 220 contains standard, well-formed `<a href="<?php the_permalink(); ?>" class="...">` without malformed quotes.
6. **Deduction**: All requirements for Milestone M2 campus isolation and data flow have been empirically satisfied and stress-tested against adversarial edge cases.

---

## 3. Caveats

- **No Caveats**: All 46 automated test assertions pass with 100% success rate. The implementation includes double-layer defense-in-depth sanitization at both helper and template level.

---

## 4. Conclusion

**Verdict: `APPROVE`**

Milestone M2 is fully verified and approved:
1. `ltdh_get_program_learning_details()` on programs with `campus = online` (IDs 1800, 1795, 1791, 1785) returns `'Toàn quốc'` and mode `'Học online 100%'`, never `'Online'`.
2. Mixed campuses strip `'Online'` and retain only brick-and-mortar stations.
3. Comparison table helper `inc/comparison.php` and comparison templates never output `'Online'` under "Cơ sở / Trạm đào tạo".
4. `taxonomy.php:220` is free of syntax errors and renders properly.
5. School training types rollup exclusivity is verified; Step 1 direct term override is removed.

---

## 5. Verification Method

To independently reproduce the empirical verification, run:

```bash
# 1. PHP Syntax Check on all modified files
php -l inc/core/class-helpers.php
php -l inc/comparison.php
php -l taxonomy.php
php -l single-program.php
php -l single-school.php
php -l single-major.php

# 2. Run Comprehensive Empirical Test Suite (46 assertions across 6 suites)
php tests/test-m2-empirical.php
```

**Expected Result**:
```text
TEST RESULTS: 46 PASSED, 0 FAILED
Exit Code: 0
```
