<?php
/**
 * Test Suite: School Admin Tabs Structure ("Thông tin Tuyển sinh")
 *
 * Verifies that ACF fields for 'group_school_details' are properly structured into tabs:
 * 1. Tổng quan
 * 2. Các đợt tuyển sinh
 * 3. Điều kiện & Hồ sơ xét tuyển
 * 4. Hồ sơ & Biểu mẫu tuyển sinh
 * 5. Quy định miễn môn
 * 6. Hỏi đáp (FAQ)
 */

$json_path = dirname(__DIR__) . '/inc/acf-import-fields.json';
$php_fields_path = dirname(__DIR__) . '/inc/acf-fields.php';

$passed = 0;
$failed = 0;

function assert_test($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $message\n";
        $passed++;
    } else {
        echo "  [FAIL] $message\n";
        $failed++;
    }
}

echo "===================================================================\n";
echo "TEST SUITE: SCHOOL ADMIN TABS STRUCTURE (GROUP_SCHOOL_DETAILS)\n";
echo "===================================================================\n\n";

// --- SUITE 1: JSON Verification ---
echo "SUITE 1: JSON Verification (acf-import-fields.json)\n";
echo "-------------------------------------------------------------------\n";

assert_test(file_exists($json_path), "File acf-import-fields.json exists");
$data = json_decode(file_get_contents($json_path), true);
assert_test(json_last_error() === JSON_ERROR_NONE, "Valid JSON syntax");

$school_group = null;
foreach ($data as $group) {
    if ($group['key'] === 'group_school_details') {
        $school_group = $group;
        break;
    }
}

assert_test($school_group !== null, "Found group_school_details");
assert_test($school_group['title'] === 'Thông tin Tuyển sinh', "Group title is 'Thông tin Tuyển sinh'");

$fields = $school_group['fields'];
$tabs = [];
$current_tab = null;
$tab_fields = [];

foreach ($fields as $field) {
    if ($field['type'] === 'tab') {
        $current_tab = $field['name'] ?: $field['key'];
        $tabs[$current_tab] = [
            'label' => $field['label'],
            'key' => $field['key'],
        ];
        $tab_fields[$current_tab] = [];
    } elseif ($current_tab) {
        $tab_fields[$current_tab][] = $field['name'];
    }
}

$expected_tabs = [
    'field_tab_school_overview' => 'Tổng quan',
    'field_tab_school_batches' => 'Các đợt tuyển sinh',
    'field_tab_school_requirements' => 'Điều kiện & Hồ sơ xét tuyển',
    'field_tab_school_admission_forms' => 'Hồ sơ & Biểu mẫu tuyển sinh',
    'field_tab_school_exemptions' => 'Quy định miễn môn',
    'field_tab_school_faq' => 'Hỏi đáp (FAQ)',
];

assert_test(count($tabs) === 6, "Has exactly 6 tabs (found " . count($tabs) . ")");

$idx = 0;
foreach ($expected_tabs as $tab_key => $tab_label) {
    $keys = array_keys($tabs);
    $actual_key = $keys[$idx] ?? '';
    assert_test(
        $actual_key === $tab_key && ($tabs[$actual_key]['label'] ?? '') === $tab_label,
        "Tab #" . ($idx + 1) . " is '{$tab_label}' with key '{$tab_key}'"
    );
    $idx++;
}

// Verify fields under each tab
$expected_fields_by_tab = [
    'field_tab_school_overview' => ['school_code', 'english_name', 'logo', 'school_banner', 'hotline', 'website', 'address', 'zalo_group_url', 'is_featured', 'order', 'duration'],
    'field_tab_school_batches' => ['default_admission_batches', 'admission_info', 'default_admission_fee', 'tuition_increase_roadmap'],
    'field_tab_school_requirements' => ['default_admission_requirements', 'default_required_documents', 'contact_info'],
    'field_tab_school_admission_forms' => ['admission_drive_folder_url', 'admission_forms_by_type'],
    'field_tab_school_exemptions' => ['exemption_title', 'exemption_intro', 'exemption_items'],
    'field_tab_school_faq' => ['school_faq'],
];

foreach ($expected_fields_by_tab as $tkey => $efields) {
    $actual_fields = $tab_fields[$tkey] ?? [];
    $all_present = true;
    foreach ($efields as $f) {
        if (!in_array($f, $actual_fields, true)) {
            $all_present = false;
            break;
        }
    }
    assert_test($all_present, "Tab '{$expected_tabs[$tkey]}' contains all expected fields: " . implode(', ', $efields));
}

// --- SUITE 2: PHP Registration Verification ---
echo "\nSUITE 2: PHP Registration Verification (inc/acf-fields.php)\n";
echo "-------------------------------------------------------------------\n";

$php_content = file_get_contents($php_fields_path);
assert_test(strpos($php_content, 'ltdh_register_school_overview_acf_fields') !== false, "Defines ltdh_register_school_overview_acf_fields hook");
assert_test(strpos($php_content, 'field_tab_school_overview') !== false, "Registers field_tab_school_overview");
assert_test(strpos($php_content, 'field_tab_school_batches') !== false, "Registers field_tab_school_batches");
assert_test(strpos($php_content, 'field_tab_school_requirements') !== false, "Registers field_tab_school_requirements");
assert_test(strpos($php_content, 'field_school_contact_info') !== false, "Registers field_school_contact_info");
assert_test(strpos($php_content, 'field_tab_school_admission_forms') !== false, "Registers field_tab_school_admission_forms");
assert_test(strpos($php_content, 'field_tab_school_exemptions') !== false, "Registers field_tab_school_exemptions");
assert_test(strpos($php_content, 'field_tab_school_faq') !== false, "Registers field_tab_school_faq");
assert_test(strpos($php_content, 'admission_forms_by_type') !== false, "admission_forms_by_type whitelisted in complex fields");

echo "\n===================================================================\n";
echo "SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "===================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
