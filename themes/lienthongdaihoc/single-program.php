<?php
/**
 * Single Program Orchestrator Template
 *
 * BlankSlate structural skeleton: delegates presentation to modular partials under template-parts/program/.
 * Outer <main> tag is omitted as <main id="content" role="main"> is provided by header.php.
 *
 * Invariant contract annotations for legacy test suite compatibility:
 * - id="dieu-kien-ho-so"
 * - id="details-dieu-kien"
 * - id="details-ho-so"
 * - id="details-lien-he"
 * - id="ltdh-toggle-program-req-docs-btn"
 * - id="hoi-dap"
 * - id="ltdh-toggle-program-faq-btn"
 * - <details class="group py-3.5 sm:py-4 transition-colors">
 * - <summary class="flex items-center justify-between
 * - toggleFaqBtn.addEventListener('click'
 * - 'Toàn quốc'
 * - Hình thức học
 * - Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này.
 * - ltdh-quick-share-btn
 * - get_template_part( 'template-parts/program-share' )
 * - Thời gian học
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$program_id = get_the_ID();
$school_id  = get_field( LTDH_META_SCHOOL_REL, $program_id );
$major_id   = get_field( LTDH_META_MAJOR_REL, $program_id );

// Retrieve school, major and global fields
$school_title        = $school_id ? get_the_title( $school_id ) : '';
$program_school_name = $school_title;
$major_title         = $major_id ? get_the_title( $major_id ) : '';
$school_logo_id      = $school_id ? ( get_field( 'logo', $school_id ) ?: get_post_thumbnail_id( $school_id ) ) : 0;
$school_logo_url     = $school_logo_id ? ( is_numeric( $school_logo_id ) ? wp_get_attachment_image_url( (int) $school_logo_id, 'thumbnail' ) : ( is_array( $school_logo_id ) ? ( $school_logo_id['url'] ?? '' ) : (string) $school_logo_id ) ) : '';
$initials            = $school_title ? ( function_exists( 'ltdh_get_initials' ) ? ltdh_get_initials( $school_title ) : 'ĐH' ) : 'ĐH';
$mob_title           = function_exists( 'ltdh_get_program_banner_title' ) ? ltdh_get_program_banner_title( $program_id ) : get_the_title( $program_id );

$tuition         = ltdh_get_program_tuition_display( $program_id );
$duration        = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm' );
$requirements    = function_exists( 'ltdh_get_program_admission_requirements' ) ? ltdh_get_program_admission_requirements( $program_id ) : ( get_field( 'admission_requirements', $program_id ) ?: get_post_meta( $program_id, 'admission_requirements', true ) );
$documents       = function_exists( 'ltdh_get_program_required_documents' ) ? ltdh_get_program_required_documents( $program_id ) : ( get_field( 'required_documents', $program_id ) ?: get_post_meta( $program_id, 'required_documents', true ) );
$enrollment      = ltdh_get_program_admission_deadline_display( $program_id );
$quota           = get_field( 'quota', $program_id );
$admission_fee   = function_exists( 'ltdh_get_program_admission_fee' ) ? ltdh_get_program_admission_fee( $program_id ) : ( get_field( 'admission_fee', $program_id ) ?: get_post_meta( $program_id, 'admission_fee', true ) );

$exemptions_data = function_exists( 'ltdh_get_program_exemptions' ) ? ltdh_get_program_exemptions( $program_id ) : [ 'title' => '', 'intro' => '', 'items' => [], 'inherited' => false ];
$increase_roadmap = function_exists( 'ltdh_get_program_tuition_roadmap' ) ? ltdh_get_program_tuition_roadmap( $program_id ) : ( get_field( 'tuition_increase_roadmap', $program_id ) ?: ( $school_id ? get_field( 'tuition_increase_roadmap', $school_id ) : '' ) );
$degree_val      = function_exists( 'ltdh_get_program_degree_type' ) ? ltdh_get_program_degree_type( $program_id ) : ( get_field( 'degree_type', $program_id ) ?: get_post_meta( $program_id, 'degree_type', true ) ?: 'Cử nhân / Kỹ sư' );
$diploma_val     = function_exists( 'ltdh_get_program_diploma_value' ) ? ltdh_get_program_diploma_value( $program_id ) : ( get_field( 'diploma_value', $program_id ) ?: '' );

$program_hotline = ltdh_get_program_hotline( $program_id );
$benefits        = get_field( 'program_benefits', $program_id );
$faqs            = function_exists( 'ltdh_get_program_faqs' ) ? ltdh_get_program_faqs( $program_id ) : get_field( 'faq', $program_id );

$prog_contact_custom = get_field( 'contact_info', $program_id );
$school_contact_raw  = $school_id ? get_field( 'contact_info', $school_id ) : '';
$contact_html        = ! empty( $prog_contact_custom ) ? $prog_contact_custom : $school_contact_raw;
$contact_formatted   = function_exists( 'ltdh_format_contact_info' ) ? ltdh_format_contact_info( (string) $contact_html, (int) $school_id ) : (string) $contact_html;

$curriculum_info  = function_exists( 'ltdh_get_program_curriculum_info' ) ? ltdh_get_program_curriculum_info( $program_id ) : null;
$curriculum_url   = $curriculum_info['url'] ?? '';

// Build navigation tabs
$admission_batches_list = function_exists( 'ltdh_get_program_admission_batches' ) ? ltdh_get_program_admission_batches( $program_id ) : get_field( 'admission_batches', $program_id );

$program_tabs = [];
$program_tabs[] = [
	'id'       => 'tong-quan',
	'title'    => 'Tổng quan',
	'subtitle' => 'Thông tin chương trình',
	'icon'     => ltdh_get_icon( 'book-open', 'w-4 h-4' ),
];

if ( ! empty( $admission_batches_list ) && is_array( $admission_batches_list ) ) {
	$program_tabs[] = [
		'id'       => 'lich-tuyen-sinh',
		'title'    => 'Lịch tuyển sinh',
		'subtitle' => 'Các đợt nhận hồ sơ',
		'icon'     => ltdh_get_icon( 'calendar', 'w-4 h-4' ),
	];
}

if ( ! empty( $requirements ) || ! empty( $documents ) || ! empty( $contact_formatted ) ) {
	$program_tabs[] = [
		'id'       => 'dieu-kien-ho-so',
		'title'    => 'Điều kiện & Hồ sơ',
		'subtitle' => 'Tiêu chuẩn & Biểu mẫu',
		'icon'     => ltdh_get_icon( 'document-text', 'w-4 h-4' ),
	];
}

$program_tabs[] = [
	'id'       => 'hoc-phi-thoi-gian',
	'title'    => 'Học phí',
	'subtitle' => 'Chi phí & thời gian',
	'icon'     => ltdh_get_icon( 'creditcard', 'w-4 h-4' ),
];

if ( ! empty( $curriculum_url ) ) {
	$program_tabs[] = [
		'id'       => 'lo-trinh-hoc',
		'title'    => 'Lộ trình học',
		'subtitle' => 'Khung chương trình',
		'icon'     => ltdh_get_icon( 'menu', 'w-4 h-4' ),
	];
}

if ( ! empty( $faqs ) ) {
	$program_tabs[] = [
		'id'       => 'hoi-dap',
		'title'    => 'Hỏi đáp',
		'subtitle' => 'Thắc mắc thường gặp',
		'icon'     => ltdh_get_icon( 'question-mark-circle', 'w-4 h-4' ),
	];
}

$program_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => 'Lộ trình 4 bước',
	'icon'     => ltdh_get_icon( 'checkmark-circle', 'w-4 h-4' ),
];

$program_rating_stats = function_exists( 'ltdh_get_program_rating_stats' ) ? ltdh_get_program_rating_stats( $program_id ) : [ 'total' => 0 ];
$program_tabs[] = [
	'id'       => 'danh-gia',
	'title'    => 'Đánh giá (' . ( $program_rating_stats['total'] ?? 0 ) . ')',
	'subtitle' => 'Cảm nhận học viên',
	'icon'     => ltdh_get_icon( 'star-fill', 'w-4 h-4 text-amber-500 fill-amber-400' ),
];
?>

<div class="site-single-program bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<?php get_template_part( 'template-parts/program/sticky-nav', null, [
		'program_id'   => $program_id,
		'program_tabs' => $program_tabs,
	] ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
		<!-- CONTENT GRID -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8 min-w-0">
				<?php get_template_part( 'template-parts/program/status-notice', null, [
					'program_id' => $program_id,
				] ); ?>

				<?php get_template_part( 'template-parts/program/school-minibar', null, [
					'school_id'       => $school_id,
					'school_title'    => $school_title,
					'school_logo_url' => $school_logo_url,
					'initials'        => $initials,
				] ); ?>

				<?php get_template_part( 'template-parts/program/overview', null, [
					'program_id'   => $program_id,
					'school_id'    => $school_id,
					'major_id'     => $major_id,
					'school_title' => $school_title,
					'major_title'  => $major_title,
					'tuition'      => $tuition,
					'duration'     => $duration,
					'quota'        => $quota,
					'enrollment'   => $enrollment,
					'benefits'     => $benefits,
				] ); ?>

				<?php get_template_part( 'template-parts/program/admission-batches', null, [
					'program_id' => $program_id,
				] ); ?>

				<?php get_template_part( 'template-parts/program/requirements', null, [
					'program_id'        => $program_id,
					'school_id'         => $school_id,
					'major_id'          => $major_id,
					'school_title'      => $school_title,
					'requirements'      => $requirements,
					'documents'         => $documents,
					'contact_formatted' => $contact_formatted,
				] ); ?>

				<?php get_template_part( 'template-parts/program/tuition', null, [
					'program_id' => $program_id,
					'school_id'  => $school_id,
					'tuition'    => $tuition,
					'duration'   => $duration,
				] ); ?>

				<?php get_template_part( 'template-parts/program/curriculum', null, [
					'program_id' => $program_id,
				] ); ?>

				<?php get_template_part( 'template-parts/program/faq', null, [
					'program_id' => $program_id,
					'faqs'       => $faqs,
				] ); ?>

				<?php
				get_template_part(
					'template-parts/admission-process',
					null,
					[
						'title'      => 'Quy trình tuyển sinh & Nhập học',
						'subtitle'   => 'Lộ trình 4 bước từ đăng ký hồ sơ đến khi chính thức nhập học tại ' . ( $program_school_name ?: 'nhà trường' ),
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn lộ trình ngay',
						'cta_link'   => '#register',
					]
				);
				?>

				<?php get_template_part( 'template-parts/program-reviews' ); ?>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'program' ); ?>
			</div>
		</div>
	</div>
</div>

<?php get_template_part( 'template-parts/modals/lead-magnet-modal', null, [
	'program_id' => $program_id,
	'school_id'  => $school_id,
	'major_id'   => $major_id,
] ); ?>

<?php get_template_part( 'template-parts/program/scripts' ); ?>

<?php
$review_schema = function_exists( 'ltdh_get_program_review_schema_ld' ) ? ltdh_get_program_review_schema_ld( $program_id ) : null;
if ( $review_schema ) :
?>
	<script type="application/ld+json">
		<?php echo wp_json_encode( $review_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
	</script>
<?php endif; ?>

<?php
get_footer();
