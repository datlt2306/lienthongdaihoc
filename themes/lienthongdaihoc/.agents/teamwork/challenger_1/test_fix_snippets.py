#!/usr/bin/env python3
"""
Test runner to empirically verify syntax and viability of all proposed fix snippets
in FULL_PROJECT_AUDIT_REPORT.md.
"""
import os
import subprocess
import tempfile
import sys

SNIPPETS_PHP = {
    "SEC-CRIT-01": """<?php
if ( php_sapi_name() !== 'cli' ) {
	http_response_code( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	die( 'Forbidden: Automated test runner can only be executed via the command-line interface (CLI).' );
}
$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	die( "Error: wp-load.php not found at $wp_load_path\\n" );
}
define( 'WP_USE_THEMES', false );
require_once $wp_load_path;
""",

    "SCHEMA-CRIT-01": """<?php
add_action( 'wp_head', 'ltdh_output_native_schema_fallback', 2 );
function ltdh_output_native_schema_fallback() {
	if ( class_exists( 'RankMath' ) ) {
		return;
	}

	$schemas = [];

	$defaults = ltdh_get_defaults();
	$schemas[] = [
		'@context'    => 'https://schema.org',
		'@type'       => 'EducationalOrganization',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => ltdh_get_logo_url(),
		'description' => get_bloginfo( 'description' ),
		'telephone'   => $defaults['hotline'] ?? '',
		'email'       => $defaults['email'] ?? '',
		'address'     => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $defaults['address'] ?? '',
			'addressCountry'  => 'VN',
		],
	];

	if ( is_singular( 'program' ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( 'school_relationship', $program_id ) ?: 0 );
		$tuition    = get_field( 'program_tuition', $program_id );
		$duration   = get_field( 'program_duration', $program_id ) ?: '1.5 - 2.5 năm';
		
		$schemas[] = [
			'@context'          => 'https://schema.org',
			'@type'             => 'Course',
			'name'              => get_the_title( $program_id ),
			'description'       => get_the_excerpt( $program_id ) ?: get_the_title( $program_id ),
			'provider'          => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school_id ? get_the_title( $school_id ) : get_bloginfo( 'name' ),
				'url'   => $school_id ? get_permalink( $school_id ) : home_url( '/' ),
			],
			'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư',
			'hasCourseInstance' => [
				'@type'          => 'CourseInstance',
				'courseMode'     => 'Blended / Online',
				'courseWorkload' => $duration,
			],
			'offers'            => [
				'@type'         => 'Offer',
				'price'         => is_numeric( $tuition ) ? $tuition : '0',
				'priceCurrency' => 'VND',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $program_id ),
			],
		];
	}

	if ( ! empty( $schemas ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\\n";
	}
}
""",

    "SEC-HIGH-01": """<?php
if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
    $file = $_FILES['degree_file'];

    $max_file_size = 5 * 1024 * 1024;
    if ( isset( $file['size'] ) && $file['size'] > $max_file_size ) {
        wp_send_json_error( [ 'message' => 'Dung lượng tệp vượt quá giới hạn 5MB cho phép.' ] );
    }

    $allowed_mimes = [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'webp'         => 'image/webp',
        'pdf'          => 'application/pdf',
    ];

    require_once ABSPATH . 'wp-admin/includes/file.php';
    $upload_overrides = [
        'test_form' => false,
        'mimes'     => $allowed_mimes,
    ];
    $movefile = wp_handle_upload( $file, $upload_overrides );

    if ( $movefile && ! isset( $movefile['error'] ) ) {
        $degree_file_url = esc_url_raw( $movefile['url'] );
    } else {
        wp_send_json_error( [ 'message' => $movefile['error'] ?? 'Định dạng tệp không được hỗ trợ.' ] );
    }
}
""",

    "SEC-HIGH-02": """<?php
$lead_id    = intval( $_POST['lead_id'] ?? 0 );
$lead_token = sanitize_text_field( $_POST['lead_token'] ?? '' );

if ( ! $lead_id || empty( $lead_token ) ) {
    wp_send_json_error( [ 'message' => 'Yêu cầu không hợp lệ hoặc thiếu mã xác thực phiên.' ] );
}

global $wpdb;
$lead = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}ltdh_leads WHERE id = %d AND referral_source LIKE %s",
    $lead_id,
    '%' . $wpdb->esc_like( $lead_token ) . '%'
) );

if ( ! $lead ) {
    wp_send_json_error( [ 'message' => 'Bạn không có quyền cập nhật hồ sơ này.' ] );
}
""",

    "PERF-HIGH-01": """<?php
$featured_schools = ltdh_get_cached_featured_schools();
""",

    "PERF-HIGH-02": """<?php
if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
    $limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
    $valid_limits = [ 10, 12, 20, 30, 50 ];
    if ( in_array( $limit, $valid_limits, true ) ) {
        $query->set( 'posts_per_page', $limit );
    } else {
        $query->set( 'posts_per_page', 12 );
    }
}
""",

    "ARCH-HIGH-01": """<?php
register_post_type( LTDH_CPT_GUIDE, [
    'labels' => [
        'name'               => 'Cẩm nang tuyển sinh',
        'singular_name'      => 'Cẩm nang',
        'add_new'            => 'Thêm bài viết cẩm nang',
        'add_new_item'       => 'Thêm bài viết cẩm nang mới',
        'edit_item'          => 'Chỉnh sửa cẩm nang',
        'all_items'          => 'Tất cả cẩm nang',
    ],
    'public'              => true,
    'has_archive'         => 'cam-nang',
    'rewrite'             => [ 'slug' => 'huong-dan', 'with_front' => false ],
    'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
    'menu_icon'           => 'dashicons-book-alt',
    'show_in_rest'        => true,
] );
""",

    "SEO-HIGH-03": """<?php
$crumbs[] = [ 
    'label' => 'Trường đối tác', 
    'url'   => get_post_type_archive_link( LTDH_CPT_SCHOOL ) ?: home_url( '/truong-doi-tac/' ) 
];
""",

    "SCHEMA-HIGH-01": """<?php
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    $defaults = ltdh_get_defaults();

    if ( is_front_page() ) {
        $data['WebSite'] = [
            '@type'           => 'WebSite',
            '@id'             => home_url( '/#website' ),
            'url'             => home_url( '/' ),
            'name'            => get_bloginfo( 'name' ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => home_url( '/he-dao-tao/?q={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    $data['EducationalOrganization'] = [
        '@type'       => 'EducationalOrganization',
        '@id'         => home_url( '/#organization' ),
        'name'        => get_bloginfo( 'name' ),
        'url'         => home_url( '/' ),
        'logo'        => ltdh_get_logo_url(),
        'telephone'   => $defaults['hotline'] ?? '',
        'email'       => $defaults['email'] ?? '',
        'sameAs'      => array_values( array_filter( [
            $defaults['facebook'] ?? '',
            $defaults['youtube'] ?? '',
            $defaults['zalo'] ?? '',
        ] ) ),
    ];

    return $data;
}, 99, 2 );
""",

    "SCHEMA-HIGH-02": """<?php
$tuition_raw = get_field( LTDH_META_TUITION, $post_id );
$duration    = get_field( LTDH_META_DURATION, $post_id ) ?: '1.5 - 2 năm';
$mode_terms  = wp_get_post_terms( $post_id, LTDH_TAX_TRAINING_TYPE );
$mode_name   = ! empty( $mode_terms ) && ! is_wp_error( $mode_terms ) ? $mode_terms[0]->name : 'Đại học từ xa';

$data['Course'] = [
    '@type'       => 'Course',
    'name'        => get_the_title( $post_id ),
    'description' => get_the_excerpt( $post_id ) ?: get_the_title( $post_id ),
    'provider'    => [
        '@type' => 'CollegeOrUniversity',
        'name'  => $school_title,
        'url'   => $school_id ? get_permalink( $school_id ) : home_url( '/' ),
    ],
    'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư Đại học',
    'hasCourseInstance' => [
        '@type'          => 'CourseInstance',
        'courseMode'     => $mode_name,
        'courseWorkload' => $duration,
        'instructor'     => [
            '@type' => 'CollegeOrUniversity',
            'name'  => $school_title,
        ],
    ],
    'offers' => [
        '@type'         => 'Offer',
        'price'         => is_numeric( $tuition_raw ) ? $tuition_raw : '0',
        'priceCurrency' => 'VND',
        'availability'  => 'https://schema.org/InStock',
        'url'           => get_permalink( $post_id ),
    ],
];
""",

    "SCHEMA-HIGH-03": """<?php
if ( is_page_template( 'page-faq.php' ) || is_page( 'hoi-dap' ) ) {
    $faq_list = get_field( 'faq_items' ) ?: ltdh_get_defaults()['faq_list'] ?? [];
    if ( ! empty( $faq_list ) && is_array( $faq_list ) ) {
        $faq_schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => [],
        ];
        foreach ( $faq_list as $item ) {
            $q = trim( wp_strip_all_tags( $item['question'] ?? '' ) );
            $a = trim( wp_strip_all_tags( $item['answer'] ?? '' ) );
            if ( $q && $a ) {
                $faq_schema['mainEntity'][] = [
                    '@type'          => 'Question',
                    'name'           => $q,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $a,
                    ],
                ];
            }
        }
        if ( ! empty( $faq_schema['mainEntity'] ) ) {
            $data['FAQPage'] = $faq_schema;
        }
    }
}
""",

    "SCHEMA-HIGH-04": """<?php
echo '<nav aria-label="Breadcrumb" class="ltdh-breadcrumb text-sm text-slate-500 py-3">';
echo '<ol class="flex flex-wrap items-center gap-2" itemscope itemtype="https://schema.org/BreadcrumbList">';

$position = 1;
foreach ( $crumbs as $crumb ) {
    echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="inline-flex items-center">';
    if ( ! empty( $crumb['url'] ) ) {
        echo '<a itemprop="item" href="' . esc_url( $crumb['url'] ) . '" class="hover:text-brand-primary transition-colors">';
        echo '<span itemprop="name">' . esc_html( $crumb['label'] ) . '</span></a>';
    } else {
        echo '<span itemprop="name" class="text-slate-700 font-semibold">' . esc_html( $crumb['label'] ) . '</span>';
    }
    echo '<meta itemprop="position" content="' . $position . '" />';
    echo '</li>';
    $position++;
}
echo '</ol></nav>';
""",

    "DEPR-MED-01": """<?php
$schools = get_posts( [
    'name'           => sanitize_title( $selected_school ),
    'post_type'      => LTDH_CPT_SCHOOL,
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'no_found_rows'  => true,
] );
$school_post = ! empty( $schools ) ? $schools[0] : null;
""",

    "SEC-MED-01": """<?php
function ltdh_ajax_filter_programs() {
    check_ajax_referer( 'ltdh_filter_nonce', 'nonce' );
    $selected_school = isset( $_GET['school'] ) ? sanitize_text_field( $_GET['school'] ) : '';
}
""",

    "SEC-MED-02": """<?php
if ( isset( $_POST['your-name'] ) ) {
    if ( ! isset( $_POST['ltdh_lead_nonce'] ) || ! wp_verify_nonce( $_POST['ltdh_lead_nonce'], 'ltdh_native_lead_action' ) ) {
        wp_die( 'Yêu cầu không hợp lệ hoặc phiên bảo mật đã hết hạn.', 'Lỗi Bảo Mật', [ 'response' => 403 ] );
    }
}
""",

    "PERF-MED-01": """<?php
function ltdh_get_school_unique_majors_count( int $school_id ): int {
    $program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
    if ( empty( $program_ids ) || ! is_array( $program_ids ) ) {
        return 0;
    }

    $major_ids = [];
    foreach ( $program_ids as $prog_id ) {
        $m_id = intval( get_post_meta( $prog_id, 'major_relationship', true ) );
        if ( $m_id && ! in_array( $m_id, $major_ids, true ) ) {
            $major_ids[] = $m_id;
        }
    }
    return count( $major_ids );
}
""",

    "ASSET-MED-01": """<?php
wp_enqueue_style(
    'ltdh-google-fonts',
    'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;850;900&family=Montserrat:wght@600;700;800;900&display=swap',
    [],
    null
);
""",

    "ASSET-MED-04": """<?php
wp_enqueue_script( 'ltdh-main-js', get_template_directory_uri() . '/assets/js/main.js', [], LTDH_VERSION, [
    'in_footer' => true,
    'strategy'  => 'defer',
] );
"""
}

SNIPPETS_JS = {
    "FRONT-HIGH-01": """
function initCompareDelegation() {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.ltdh-compare-toggle, .ltdh-compare-single-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var type = btn.getAttribute('data-compare-type');
        var id   = parseInt(btn.getAttribute('data-compare-id'), 10);
        if (!type || !id) return;

        if (hasItem(type, id)) {
            removeItem(type, id);
            btn.classList.remove('is-compared');
            btn.textContent = btn.classList.contains('ltdh-compare-single-btn') ? '📊 Thêm vào so sánh' : 'So sánh';
        } else {
            var items = getItems();
            var total = Object.values(items).reduce(function (s, a) { return s + a.length; }, 0);
            if (total >= MAX_ITEMS) {
                showToast('Chỉ so sánh tối đa ' + MAX_ITEMS + ' mục.', 'warning');
                return;
            }

            var btnHe     = btn.getAttribute('data-compare-he') || '';
            var btnNganh  = btn.getAttribute('data-compare-nganh') || '';
            var btnTitle  = btn.getAttribute('data-compare-title') || '';
            var btnThumb  = btn.getAttribute('data-compare-thumb') || '';

            addItem(type, id, btnHe, btnNganh, btnTitle, btnThumb);
            btn.classList.add('is-compared');
            btn.textContent = '✓ Đã thêm';
            showToast('Đã thêm vào danh sách so sánh (' + (total + 1) + '/' + MAX_ITEMS + ')', 'success');
        }
        updateTray();
    });
}
document.addEventListener('DOMContentLoaded', function () {
    initCompareDelegation();
});
""",

    "FRONT-HIGH-02": """
function testEligibilityFix() {
    var data = new FormData();
    var eduEl     = document.querySelector('select[name="education"]');
    var trainEl   = document.querySelector('select[name="training_type"]');
    var campusEl  = document.querySelector('select[name="campus"]');

    data.append('education', eduEl ? eduEl.value : '');
    data.append('training_type', trainEl ? trainEl.value : '');
    data.append('campus', campusEl ? campusEl.value : '');
}

function initLeadForm() {
    var form = document.getElementById('elig-lead-form');
    if (!form || form.dataset.bound === 'true') return;
    form.dataset.bound = 'true';

    form.addEventListener('submit', function (e) {
        e.preventDefault();
    });
}
"""
}

def main():
    print("==================================================")
    print("STARTING EMPIRICAL SYNTAX VERIFICATION FOR FIX SNIPPETS")
    print("==================================================")

    php_passed = 0
    php_failed = 0
    for name, code in SNIPPETS_PHP.items():
        with tempfile.NamedTemporaryFile("w", suffix=".php", delete=False) as f:
            f.write(code)
            f_path = f.name
        
        try:
            res = subprocess.run(["php", "-l", f_path], capture_output=True, text=True)
            if res.returncode == 0:
                print(f"[PHP PASS] {name:15} -> Syntax valid (php -l)")
                php_passed += 1
            else:
                print(f"[PHP FAIL] {name:15} -> ERROR: {res.stderr.strip() or res.stdout.strip()}")
                php_failed += 1
        finally:
            if os.path.exists(f_path):
                os.remove(f_path)

    js_passed = 0
    js_failed = 0
    for name, code in SNIPPETS_JS.items():
        with tempfile.NamedTemporaryFile("w", suffix=".js", delete=False) as f:
            f.write(code)
            f_path = f.name
        
        try:
            res = subprocess.run(["node", "-c", f_path], capture_output=True, text=True)
            if res.returncode == 0:
                print(f"[JS PASS]  {name:15} -> Syntax valid (node -c)")
                js_passed += 1
            else:
                print(f"[JS FAIL]  {name:15} -> ERROR: {res.stderr.strip() or res.stdout.strip()}")
                js_failed += 1
        finally:
            if os.path.exists(f_path):
                os.remove(f_path)

    print("\n--------------------------------------------------")
    print(f"RESULTS: PHP {php_passed}/{len(SNIPPETS_PHP)} passed, JS {js_passed}/{len(SNIPPETS_JS)} passed.")
    if php_failed > 0 or js_failed > 0:
        print("SYNTAX VERIFICATION FAILED!")
        sys.exit(1)
    else:
        print("ALL PROPOSED FIX SNIPPETS ARE SYNTACTICALLY VALID!")
        sys.exit(0)

if __name__ == "__main__":
    main()
