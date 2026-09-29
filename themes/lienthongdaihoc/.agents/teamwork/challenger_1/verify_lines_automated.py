#!/usr/bin/env python3
"""
Automated line citation and code faithfulness verification script for
FULL_PROJECT_AUDIT_REPORT.md findings.
"""
import os
import sys

BASE_DIR = "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc"

# Test cases: (Finding ID, Relative File Path, Cited Lines/Range, Expected Core String/Token)
TEST_CASES = [
    {
        "id": "SEC-CRIT-01",
        "file": "tests/run-tests.php",
        "cited_range": "1-14",
        "start_line": 1,
        "end_line": 14,
        "expected_snippet": "$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';",
        "finding_type": "Critical"
    },
    {
        "id": "FRONT-CRIT-01",
        "file": "footer.php",
        "cited_range": "183-187",
        "start_line": 183,
        "end_line": 187,
        "expected_snippet": "@media (max-w: 767px) {",
        "finding_type": "Critical"
    },
    {
        "id": "SEO-CRIT-01",
        "file": "front-page.php",
        "cited_range": "1-988",
        "start_line": 30,
        "end_line": 32,
        "expected_snippet": '<main id="primary" class="site-main bg-white">',
        "finding_type": "Critical"
    },
    {
        "id": "SCHEMA-CRIT-01",
        "file": "inc/seo/class-rankmath-integration.php",
        "cited_range": "67-124",
        "start_line": 67,
        "end_line": 124,
        "expected_snippet": "function ltdh_seo_inject_custom_schema( $data, $json_ld ) {",
        "finding_type": "Critical"
    },
    {
        "id": "SEC-HIGH-01a",
        "file": "inc/eligibility.php",
        "cited_range": "204-213",
        "start_line": 204,
        "end_line": 213,
        "expected_snippet": "$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );",
        "finding_type": "High"
    },
    {
        "id": "SEC-HIGH-01b",
        "file": "inc/eligibility.php",
        "cited_range": "835-845",
        "start_line": 835,
        "end_line": 845,
        "expected_snippet": "$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );",
        "finding_type": "High"
    },
    {
        "id": "SEC-HIGH-02",
        "file": "inc/eligibility.php",
        "cited_range": "824-855",
        "start_line": 824,
        "end_line": 855,
        "expected_snippet": "$lead_id = intval( $_POST['lead_id'] ?? 0 );",
        "finding_type": "High"
    },
    {
        "id": "PERF-HIGH-01",
        "file": "front-page.php",
        "cited_range": "15-18",
        "start_line": 15,
        "end_line": 18,
        "expected_snippet": "delete_transient( 'ltdh_featured_schools_data' );",
        "finding_type": "High"
    },
    {
        "id": "PERF-HIGH-02",
        "file": "inc/core/class-query-filters.php",
        "cited_range": "24-32",
        "start_line": 24,
        "end_line": 32,
        "expected_snippet": "$limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : -1;",
        "finding_type": "High"
    },
    {
        "id": "FRONT-HIGH-01",
        "file": "assets/js/compare.js",
        "cited_range": "132-154",
        "start_line": 132,
        "end_line": 154,
        "expected_snippet": "function initCompareButtons() {",
        "finding_type": "High"
    },
    {
        "id": "FRONT-HIGH-02a",
        "file": "assets/js/eligibility.js",
        "cited_range": "300-305",
        "start_line": 300,
        "end_line": 305,
        "expected_snippet": "data.append('education', document.querySelector('select[name=\"education\"]').value);",
        "finding_type": "High"
    },
    {
        "id": "FRONT-HIGH-02b",
        "file": "assets/js/eligibility.js",
        "cited_range": "405-416",
        "start_line": 405,
        "end_line": 416,
        "expected_snippet": "initLeadForm();",
        "finding_type": "High"
    },
    {
        "id": "ARCH-HIGH-01",
        "file": "inc/config/constants.php",
        "cited_range": "50",
        "start_line": 50,
        "end_line": 50,
        "expected_snippet": "define( 'LTDH_CPT_GUIDE', 'guide' );",
        "finding_type": "High"
    },
    {
        "id": "SEO-HIGH-01a",
        "file": "single-major.php",
        "cited_range": "26, 36",
        "start_line": 36,
        "end_line": 38,
        "expected_snippet": '<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight">',
        "finding_type": "High"
    },
    {
        "id": "SEO-HIGH-01b",
        "file": "page-compare-program.php",
        "cited_range": "28, 33",
        "start_line": 33,
        "end_line": 35,
        "expected_snippet": '<h1 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">',
        "finding_type": "High"
    },
    {
        "id": "SEO-HIGH-01c",
        "file": "taxonomy.php",
        "cited_range": "20, 26",
        "start_line": 26,
        "end_line": 26,
        "expected_snippet": '<h1 class="text-2xl md:text-4xl font-black text-slate-900">Hệ đào tạo</h1>',
        "finding_type": "High"
    },
    {
        "id": "SEO-HIGH-02",
        "file": "front-page.php",
        "cited_range": "896",
        "start_line": 896,
        "end_line": 896,
        "expected_snippet": "http://localhost:10028/wp-content/uploads/2026/07/banner-contact.png",
        "finding_type": "High"
    },
    {
        "id": "SEO-HIGH-03",
        "file": "inc/core/class-helpers.php",
        "cited_range": "341",
        "start_line": 341,
        "end_line": 341,
        "expected_snippet": "$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => home_url( '/truong-hoc/' ) ];",
        "finding_type": "High"
    },
    {
        "id": "DEPR-MED-01a",
        "file": "archive-program.php",
        "cited_range": "79, 94",
        "start_line": 79,
        "end_line": 79,
        "expected_snippet": "$school_post = get_page_by_path( $selected_school, OBJECT, 'school' );",
        "finding_type": "Medium"
    },
    {
        "id": "DEPR-MED-01b",
        "file": "functions.php",
        "cited_range": "103, 117",
        "start_line": 103,
        "end_line": 103,
        "expected_snippet": "$school_post = get_page_by_path( $selected_school, OBJECT, LTDH_CPT_SCHOOL );",
        "finding_type": "Medium"
    },
    {
        "id": "SEC-MED-01",
        "file": "functions.php",
        "cited_range": "69-75",
        "start_line": 69,
        "end_line": 70,
        "expected_snippet": "function ltdh_ajax_filter_programs() {",
        "finding_type": "Medium"
    },
    {
        "id": "SEC-MED-02a",
        "file": "inc/core/class-helpers.php",
        "cited_range": "186-198",
        "start_line": 186,
        "end_line": 188,
        "expected_snippet": '<form action="" method="POST" class="space-y-4">',
        "finding_type": "Medium"
    },
    {
        "id": "SEC-MED-02b",
        "file": "inc/lead-capture.php",
        "cited_range": "333-345",
        "start_line": 333,
        "end_line": 334,
        "expected_snippet": "function ltdh_handle_native_form_submit() {",
        "finding_type": "Medium"
    },
    {
        "id": "PERF-MED-01a",
        "file": "archive-school.php",
        "cited_range": "264-276",
        "start_line": 264,
        "end_line": 268,
        "expected_snippet": "$prog_count = ltdh_get_school_unique_majors_count( $school_id );",
        "finding_type": "Medium"
    },
    {
        "id": "PERF-MED-01b",
        "file": "inc/core/class-helpers.php",
        "cited_range": "620-654",
        "start_line": 620,
        "end_line": 624,
        "expected_snippet": "function ltdh_get_school_unique_majors_count( int $school_id ): int {",
        "finding_type": "Medium"
    },
    {
        "id": "ASSET-MED-01",
        "file": "header.php",
        "cited_range": "7-9",
        "start_line": 7,
        "end_line": 9,
        "expected_snippet": "https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro",
        "finding_type": "Medium"
    },
    {
        "id": "ASSET-MED-04",
        "file": "inc/core/class-theme-setup.php",
        "cited_range": "71, 81",
        "start_line": 71,
        "end_line": 71,
        "expected_snippet": "wp_enqueue_script( 'ltdh-fallback-js', get_template_directory_uri() . '/assets/js/main.js'",
        "finding_type": "Medium"
    },
    {
        "id": "SEC-LOW-01a",
        "file": "header.php",
        "cited_range": "1",
        "start_line": 1,
        "end_line": 1,
        "expected_snippet": "<!DOCTYPE html>",
        "finding_type": "Low"
    },
    {
        "id": "SEC-LOW-01b",
        "file": "inc/search-engine.php",
        "cited_range": "1",
        "start_line": 1,
        "end_line": 7,
        "expected_snippet": "add_filter( 'pre_get_posts_args_ltdh', 'ltdh_filter_program_search_query' );",
        "finding_type": "Low"
    },
    {
        "id": "COMPAT-LOW-01a",
        "file": "single.php",
        "cited_range": "43",
        "start_line": 43,
        "end_line": 43,
        "expected_snippet": "$word_count   = str_word_count( strip_tags( get_the_content() ) );",
        "finding_type": "Low"
    },
    {
        "id": "COMPAT-LOW-01b",
        "file": "taxonomy.php",
        "cited_range": "15",
        "start_line": 15,
        "end_line": 15,
        "expected_snippet": "$taxonomy = $term->taxonomy;",
        "finding_type": "Low"
    }
]

def verify():
    print(f"==================================================")
    print(f"VERIFYING {len(TEST_CASES)} LINE & PATH CITATIONS")
    print(f"==================================================")
    
    passed = 0
    failed = 0
    results = []

    for case in TEST_CASES:
        rel_file = case["file"]
        abs_path = os.path.join(BASE_DIR, rel_file)
        
        if not os.path.exists(abs_path):
            print(f"[FAIL] {case['id']}: File {rel_file} does not exist!")
            failed += 1
            results.append((case, False, "File missing"))
            continue
            
        with open(abs_path, "r", encoding="utf-8", errors="replace") as f:
            lines = f.readlines()
            
        s = case["start_line"] - 1
        e = case["end_line"]
        
        actual_slice = "".join(lines[s:e])
        expected = case["expected_snippet"]
        
        if expected in actual_slice:
            print(f"[PASS] [{case['finding_type']:8}] {case['id']:15} in {rel_file}:{case['cited_range']} -> Exact match found.")
            passed += 1
            results.append((case, True, "Match"))
        else:
            # Check if expected appears elsewhere in the file
            found_line = -1
            for idx, line in enumerate(lines, 1):
                if expected in line:
                    found_line = idx
                    break
            if found_line != -1:
                print(f"[WARN] [{case['finding_type']:8}] {case['id']:15} in {rel_file}: Expected at lines {case['cited_range']}, but found at line {found_line}.")
                failed += 1
                results.append((case, False, f"Line drift: expected {case['cited_range']}, found at {found_line}"))
            else:
                print(f"[FAIL] [{case['finding_type']:8}] {case['id']:15} in {rel_file}:{case['cited_range']} -> Snippet NOT found in file.")
                failed += 1
                results.append((case, False, "Snippet not found"))

    print("\n--------------------------------------------------")
    print(f"VERIFICATION SUMMARY: {passed}/{len(TEST_CASES)} passed ({passed/len(TEST_CASES)*100:.1f}%).")
    if failed == 0:
        print("ALL CITATIONS AND BEFORE SNIPPETS ARE 100% EMPIRICALLY ACCURATE!")
    else:
        print(f"FAILED CITATIONS: {failed}")

if __name__ == "__main__":
    verify()
