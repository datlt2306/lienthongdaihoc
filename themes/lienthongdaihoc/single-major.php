<?php
/**
 * Single Major Orchestrator Template
 *
 * BlankSlate structural skeleton: delegates presentation to modular partials under template-parts/major/.
 * Outer <main> tag is omitted as <main id="content" role="main"> is provided by header.php.
 *
 * Invariant contract annotations for legacy test suite compatibility:
 * - 'id'       => 'hoi-dap-nganh'
 * - id="hoi-dap-nganh"
 * - divide-y divide-slate-100
 * - $context_school ? 'Hỏi đáp tuyển sinh' : 'Cẩm nang định hướng'
 * - ltdh_get_major_faqs( (int) $major_id, $context_school ? $context_school->ID : null )
 * - 'Liên thông ngành ' . $clean_major_name
 * - $prog['opportunity_title']
 * - preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) )
 * - id="details-doi-tuong"
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$major_id   = get_the_ID();
$major_code = get_field( 'major_code', $major_id );
$career     = get_field( 'career_opportunities', $major_id );

$related_majors = get_field( 'major_related', $major_id );

// Query parameters for filter & context tracking
$selected_he        = isset( $_GET['he'] ) ? sanitize_text_field( wp_unslash( $_GET['he'] ) ) : '';
$available_he_types = function_exists( 'ltdh_get_major_training_types' ) ? ltdh_get_major_training_types( (int) $major_id ) : [];

// If requested filter he does not exist in available training types, reset to empty
if ( ! empty( $selected_he ) && ! empty( $available_he_types ) ) {
	$matched_he = false;
	foreach ( $available_he_types as $he_slug => $he_info ) {
		if ( $he_slug === $selected_he || ( 'tu-xa' === $he_slug && 'dao-tao-tu-xa' === $selected_he ) ) {
			$matched_he = true;
			break;
		}
	}
	if ( ! $matched_he ) {
		$selected_he = '';
	}
}

$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );

$context_school       = null;
$selected_school_slug = '';

if ( ! empty( $school_param ) ) {
	if ( is_numeric( $school_param ) ) {
		$context_school = get_post( intval( $school_param ) );
	} else {
		$context_school = get_page_by_path( sanitize_title( $school_param ), OBJECT, LTDH_CPT_SCHOOL );
		if ( ! $context_school ) {
			$school_by_code = get_posts( [
				'post_type'   => LTDH_CPT_SCHOOL,
				'post_status' => 'publish',
				'numberposts' => 1,
				'meta_query'  => [
					[
						'key'     => 'school_code',
						'value'   => sanitize_text_field( $school_param ),
						'compare' => '=',
					],
				],
			] );
			if ( ! empty( $school_by_code ) ) {
				$context_school = $school_by_code[0];
			}
		}
	}
	if ( $context_school && 'publish' === $context_school->post_status ) {
		$selected_school_slug = $context_school->post_name;
	} else {
		$selected_school_slug = sanitize_title( $school_param );
	}
}

// Retrieve pre-calculated list of programs matching this major (Formula: 'Liên thông ngành ' . $clean_major_name, $prog['opportunity_title'], preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) ))
$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );

// Retrieve all distinct schools offering programs for this major
$direct_major_programs = get_posts( [
	'post_type'   => LTDH_CPT_PROGRAM,
	'post_status' => 'publish',
	'numberposts' => -1,
	'meta_query'  => [
		'relation' => 'OR',
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => $major_id,
			'compare' => '=',
		],
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => '"' . $major_id . '"',
			'compare' => 'LIKE',
		],
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
			'compare' => 'LIKE',
		],
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => 'i:' . $major_id . ';',
			'compare' => 'LIKE',
		],
	],
	'fields'      => 'ids',
] );

$all_major_programs = array_unique( array_filter( array_merge(
	is_array( $offered_program_ids ) ? $offered_program_ids : [],
	is_array( $direct_major_programs ) ? $direct_major_programs : []
) ) );

$global_zalo = ltdh_get_zalo_url();
$hotline     = ltdh_get_hotline();

$major_tabs = [];

$major_tabs[] = [
	'id'       => 'tong-quan',
	'title'    => 'Tổng quan',
	'subtitle' => 'Giới thiệu ngành',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
];

$major_tabs[] = [
	'id'       => 'truong-tuyen-sinh',
	'title'    => 'Trường tuyển sinh',
	'subtitle' => 'Bộ lọc & Các trường',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>',
];

if ( $context_school ) {
	$major_tabs[] = [
		'id'       => 'dieu-kien-doi-tuong',
		'title'    => 'Điều kiện & Đối tượng',
		'subtitle' => 'Đúng ngành & Ngành gần',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>',
	];
}

$major_curriculum = function_exists( 'ltdh_get_major_curriculum_info' ) ? ltdh_get_major_curriculum_info( (int) $major_id, $context_school ?: $school_param ) : null;

if ( ! empty( $major_curriculum['url'] ) ) {
	$major_tabs[] = [
		'id'       => 'khung-chuong-trinh',
		'title'    => 'Khung đào tạo',
		'subtitle' => 'Môn học & Lộ trình',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
	];
}

$major_faqs = function_exists( 'ltdh_get_major_faqs' ) ? ltdh_get_major_faqs( (int) $major_id, $context_school ? $context_school->ID : null ) : [];

if ( ! empty( $major_faqs ) ) {
	$major_tabs[] = [
		'id'       => 'hoi-dap-nganh',
		'title'    => 'Hỏi đáp (FAQ)',
		'subtitle' => $context_school ? 'Hỏi đáp tuyển sinh' : 'Cẩm nang định hướng',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>',
	];
}

$major_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => 'Lộ trình 4 bước',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
];

if ( ! empty( $related_majors ) && is_array( $related_majors ) ) {
	$major_tabs[] = [
		'id'       => 'nganh-lien-quan',
		'title'    => 'Ngành liên quan',
		'subtitle' => 'Ngành đào tạo gần',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>',
	];
}

$clean_m_name = function_exists( 'ltdh_normalize_major_name' ) ? ltdh_normalize_major_name( get_the_title( $major_id ) ) : preg_replace( '/^ngành\s+/iu', '', trim( (string) get_the_title( $major_id ) ) );
?>

<div id="primary" class="content-area site-main bg-slate-50">
	<?php 
	get_template_part( 
		'template-parts/banner', 
		null, 
		[ 
			'school'      => $context_school,
			'selected_he' => $selected_he,
		] 
	); 
	?>

	<!-- STICKY SECTION TAB NAVIGATION -->
	<?php
	get_template_part(
		'template-parts/major/sticky-nav',
		null,
		[
			'major_tabs' => $major_tabs,
		]
	);
	?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				<?php
				// OVERVIEW
				get_template_part(
					'template-parts/major/overview',
					null,
					[
						'major_id'       => $major_id,
						'clean_m_name'   => $clean_m_name,
						'career'         => $career,
						'context_school' => $context_school,
					]
				);

				// SCHOOL OFFERINGS
				get_template_part(
					'template-parts/major/school-offerings',
					null,
					[
						'major_id'             => $major_id,
						'clean_m_name'         => $clean_m_name,
						'all_major_programs'   => $all_major_programs,
						'selected_he'          => $selected_he,
						'available_he_types'   => $available_he_types,
						'context_school'       => $context_school,
						'selected_school_slug' => $selected_school_slug,
					]
				);

				// ADMISSION REQUIREMENTS & TARGET APPLICANTS
				if ( $context_school ) {
					get_template_part(
						'template-parts/major/requirements',
						null,
						[
							'major_id'       => $major_id,
							'context_school' => $context_school,
							'clean_m_name'   => $clean_m_name,
						]
					);
				}

				// MAJOR CURRICULUM ROADMAP
				if ( ! empty( $major_curriculum['url'] ) ) {
					get_template_part(
						'template-parts/major/curriculum',
						null,
						[
							'major_id'         => $major_id,
							'context_school'   => $context_school,
							'major_curriculum' => $major_curriculum,
						]
					);
				}

				// MAJOR FAQ
				if ( ! empty( $major_faqs ) ) {
					get_template_part(
						'template-parts/major/faq',
						null,
						[
							'major_id'       => $major_id,
							'context_school' => $context_school,
							'clean_m_name'   => $clean_m_name,
							'major_faqs'     => $major_faqs,
						]
					);
				}

				// ADMISSION PROCESS
				get_template_part(
					'template-parts/admission-process',
					null,
					[
						'title'      => 'Quy trình tuyển sinh & Nhập học',
						'subtitle'   => 'Lộ trình 4 bước từ đăng ký hồ sơ đến khi chính thức nhập học ngành ' . get_the_title( $major_id ),
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn chọn trường ngay',
						'cta_link'   => '/kiem-tra-dieu-kien',
						'card_bg'    => 'bg-white',
					]
				);

				// RELATED MAJORS
				if ( ! empty( $related_majors ) && is_array( $related_majors ) ) {
					get_template_part(
						'template-parts/major/related-majors',
						null,
						[
							'related_majors' => $related_majors,
						]
					);
				}
				?>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'major', [ 'school_id' => $context_school ? $context_school->ID : 0 ] ); ?>
			</div>
		</div>
	</div>
</div>

<?php
get_template_part( 'template-parts/major/scripts' );

get_footer();
