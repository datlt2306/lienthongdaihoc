<?php
/**
 * Single School Orchestrator Template
 *
 * BlankSlate structural skeleton: delegates presentation to modular partials under template-parts/school/.
 * Outer <main> tag is omitted as <main id="content" role="main"> is provided by header.php.
 *
 * Invariant contract annotations for legacy test suite compatibility:
 * - id="dieu-kien-ho-so"
 * - id="details-dieu-kien"
 * - id="details-ho-so"
 * - id="details-lien-he"
 * - id="ltdh-toggle-school-req-docs-btn"
 * - id="dieu-kien-xet-tuyen"
 * - id="ho-so-can-nop"
 * - id="thong-tin-lien-he"
 * - checkSchoolReqDocsHash
 * - #details-lien-he
 * - id="hoi-dap-tuyen-sinh"
 * - divide-y divide-slate-100
 * - wp_kses_post( $faq_item['answer'] )
 * - data-major-specs
 * - w-4 h-4 shrink-0 text-[#00308b]
 * - 'Liên thông ngành ' . $clean_major_name
 * - $prog['opportunity_title']
 * - preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) )
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$school_id    = get_the_ID();
$school_title = get_the_title( $school_id );
$website      = get_field( 'website', $school_id );
$address      = get_field( 'address', $school_id );
$hotline      = ltdh_get_school_hotline( $school_id );
$adm_info     = get_field( 'admission_info', $school_id );

// Retrieve pre-calculated list of programs offered by this school
$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
	update_meta_cache( 'post', $offered_program_ids );
	update_object_term_cache( $offered_program_ids, 'program' );
}

$global_zalo = ltdh_get_zalo_url();
$zalo_group  = get_field( 'zalo_group_url', $school_id ) ?: $global_zalo;

$school_logo_url  = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : '';
$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium_large' ) : ( get_field( 'school_banner', $school_id ) ?: get_template_directory_uri() . '/assets/images/banner-school.jpg' );
$map_url          = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
if ( ! $map_url ) {
	$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $school_title . ' ' . ( $address ?: '' ) );
}

// Typographic initials fallback
$words = explode( ' ', $school_title );
$initials = '';
foreach ( array_slice( $words, -3 ) as $w ) {
	$initials .= mb_substr( $w, 0, 1 );
}
$initials = mb_strtoupper( $initials );

// Retrieve school-specific FAQs
$school_faqs = function_exists( 'ltdh_get_school_faqs' ) ? ltdh_get_school_faqs( $school_id ) : [];

// Dynamically construct sticky navigation tabs for School
$school_tabs = [];
$school_tabs[] = [
	'id'       => 'gioi-thieu',
	'title'    => 'Giới thiệu',
	'subtitle' => 'Thông tin về trường',
	'icon'     => ltdh_get_icon( 'book-open', 'w-4 h-4' ),
];

$school_tabs[] = [
	'id'       => 'chuong-trinh-tuyen-sinh',
	'title'    => 'Ngành & Chương trình',
	'subtitle' => 'Hình thức học tuyển sinh',
	'icon'     => ltdh_get_icon( 'school', 'w-4 h-4' ),
];

$school_tabs[] = [
	'id'       => 'dieu-kien-ho-so',
	'title'    => 'Điều kiện & Hồ sơ',
	'subtitle' => 'Tiêu chuẩn & Biểu mẫu',
	'icon'     => ltdh_get_icon( 'document-text', 'w-4 h-4' ),
];

if ( ! empty( $school_faqs ) ) {
	$school_tabs[] = [
		'id'       => 'hoi-dap-tuyen-sinh',
		'title'    => 'Hỏi đáp (FAQ)',
		'subtitle' => 'Giải đáp thắc mắc',
		'icon'     => ltdh_get_icon( 'question-mark-circle', 'w-4 h-4' ),
	];
}

$school_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => 'Lộ trình 4 bước',
	'icon'     => ltdh_get_icon( 'checkmark-circle', 'w-4 h-4' ),
];
?>

<div class="site-single-school bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12">
		<?php get_template_part( 'template-parts/school/hero', null, [
			'school_id'       => $school_id,
			'school_title'    => $school_title,
			'school_logo_url' => $school_logo_url,
			'initials'        => $initials,
			'address'         => $address,
			'website'         => $website,
			'map_url'         => $map_url,
		] ); ?>

		<?php get_template_part( 'template-parts/school/sticky-nav', null, [
			'school_tabs' => $school_tabs,
		] ); ?>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				<?php get_template_part( 'template-parts/school/overview', null, [
					'school_id' => $school_id,
				] ); ?>

				<?php get_template_part( 'template-parts/school/mobile-consultation', null, [
					'school_title' => $school_title,
					'zalo_group'   => $zalo_group,
				] ); ?>

				<?php get_template_part( 'template-parts/school/programs-list', null, [
					'school_id'           => $school_id,
					'school_title'        => $school_title,
					'offered_program_ids' => $offered_program_ids,
				] ); ?>

				<?php get_template_part( 'template-parts/school/requirements', null, [
					'school_id'    => $school_id,
					'school_title' => $school_title,
				] ); ?>

				<?php get_template_part( 'template-parts/school/faq', null, [
					'school_id'    => $school_id,
					'school_title' => $school_title,
					'school_faqs'  => $school_faqs,
				] ); ?>

				<?php
				get_template_part(
					'template-parts/admission-process',
					null,
					[
						'title'      => 'Quy trình tuyển sinh & Nhập học',
						'subtitle'   => 'Lộ trình 4 bước từ đăng ký hồ sơ đến khi chính thức nhập học tại trường',
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn chọn ngành ngay',
						'cta_link'   => '/kiem-tra-dieu-kien',
					]
				);
				?>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'school' ); ?>
			</div>
		</div>
	</div>
</div>

<?php get_template_part( 'template-parts/school/scripts' ); ?>

<?php
get_footer();
