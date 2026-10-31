<?php
/**
 * Static Facade & Backward Compatibility Shims for Helper Systems.
 *
 * This file delegates all helper logic to domain modules in inc/helpers/:
 * - LTDH_School_Helpers (inc/helpers/helper-schools.php)
 * - LTDH_Program_Helpers (inc/helpers/helper-programs.php)
 * - LTDH_Pricing_Helpers (inc/helpers/helper-pricing.php)
 * - LTDH_UI_Helpers (inc/helpers/helper-ui.php)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Require Domain Helper Modules
// ----------------------------------------------------
require_once dirname( __DIR__ ) . '/helpers/helper-schools.php';
require_once dirname( __DIR__ ) . '/helpers/helper-programs.php';
require_once dirname( __DIR__ ) . '/helpers/helper-pricing.php';
require_once dirname( __DIR__ ) . '/helpers/helper-ui.php';

/**
 * Class LTDH_Helpers — Static Facade.
 */
class LTDH_Helpers {

	// --- School Delegations ---
	public static function get_current_context_school_id(): int {
		return LTDH_School_Helpers::get_current_context_school_id();
	}

	public static function get_school_zalo_group_url( int $school_id = 0 ): string {
		return LTDH_School_Helpers::get_zalo_group_url( $school_id );
	}

	public static function get_school_hotline( int $school_id = 0 ): string {
		return LTDH_School_Helpers::get_hotline( $school_id );
	}

	public static function get_school_admission_forms( int $school_id ): array {
		return LTDH_School_Helpers::get_admission_forms( $school_id );
	}

	public static function get_school_order( int $school_id ): int {
		return LTDH_School_Helpers::get_order( $school_id );
	}

	public static function sort_schools_by_order( array $posts ): array {
		return LTDH_School_Helpers::sort_by_order( $posts );
	}

	public static function sync_school_order( $post_id, $post = null, $update = null ) {
		return LTDH_School_Helpers::sync_order( $post_id, $post, $update );
	}

	public static function get_school_location_label( int $school_id ): string {
		return LTDH_School_Helpers::get_location_label( $school_id );
	}

	public static function get_school_region_label( int $school_id ): string {
		return LTDH_School_Helpers::get_region_label( $school_id );
	}

	public static function get_cached_featured_schools() {
		return LTDH_School_Helpers::get_cached_featured();
	}

	public static function get_school_image_id( int $school_id ): int {
		return LTDH_School_Helpers::get_image_id( $school_id );
	}

	public static function generate_school_svg_logo( string $code, string $title = '' ): string {
		return LTDH_School_Helpers::generate_svg_logo( $code, $title );
	}

	public static function get_school_logo_url( $school_id, string $size = 'thumbnail' ): string {
		return LTDH_School_Helpers::get_logo_url( $school_id, $size );
	}

	public static function get_school_cover_id( $school_id ): int {
		return LTDH_School_Helpers::get_cover_id( $school_id );
	}

	public static function get_school_cover_url( $school_id, string $size = 'medium' ): string {
		return LTDH_School_Helpers::get_cover_url( $school_id, $size );
	}

	public static function render_school_thumbnail( int $school_id, string $size = 'thumbnail', string $classes = 'w-16 h-16 md:w-20 md:h-20 rounded-2xl object-contain bg-white p-2.5 shadow-sm border border-slate-100 flex-shrink-0' ): void {
		LTDH_School_Helpers::render_thumbnail( $school_id, $size, $classes );
	}

	public static function get_school_unique_majors_count( int $school_id ): int {
		return LTDH_School_Helpers::get_unique_majors_count( $school_id );
	}

	public static function get_school_training_types( int $school_id, string $output_format = 'names' ): array {
		return LTDH_School_Helpers::get_training_types( $school_id, $output_format );
	}

	public static function get_school_code( int $school_id ): string {
		return LTDH_School_Helpers::get_code( $school_id );
	}

	public static function get_school_admission_requirements( int $school_id ): string {
		return LTDH_School_Helpers::get_admission_requirements( $school_id );
	}

	public static function get_school_required_documents( int $school_id ): string {
		return LTDH_School_Helpers::get_required_documents( $school_id );
	}

	public static function get_school_faqs( int $school_id ): array {
		return LTDH_School_Helpers::get_faqs( $school_id );
	}

	// --- Pricing Delegations ---
	public static function get_program_tuition_display( int $program_id, bool $include_year = false ): string {
		return LTDH_Pricing_Helpers::get_tuition_display( $program_id, $include_year );
	}

	public static function format_tuition( int $program_id, bool $include_year = false ): string {
		return LTDH_Pricing_Helpers::get_tuition_display( $program_id, $include_year );
	}

	public static function format_admission_fee( $fee ): array {
		return LTDH_Pricing_Helpers::format_admission_fee( $fee );
	}

	public static function get_program_tuition_roadmap( int $program_id ): string {
		return LTDH_Pricing_Helpers::get_tuition_roadmap( $program_id );
	}

	public static function get_program_admission_fee( int $program_id ) {
		return LTDH_Pricing_Helpers::get_admission_fee( $program_id );
	}

	// --- Program & Major Delegations ---
	public static function get_program_hotline( int $program_id ): string {
		return LTDH_Program_Helpers::get_hotline( $program_id );
	}

	public static function get_program_school_id( int $program_id ): int {
		return LTDH_Program_Helpers::get_school_id( $program_id );
	}

	public static function format_program_card_headline( ?string $raw_prog_title = '', ?string $school_code = '', ?string $school_name = '', ?string $major_title = '' ): string {
		return LTDH_Program_Helpers::format_card_headline( $raw_prog_title, $school_code, $school_name, $major_title );
	}

	public static function get_field_or_meta( string $key, int $post_id ) {
		return LTDH_Program_Helpers::get_field_or_meta( $key, $post_id );
	}

	public static function is_google_drive_url( string $url ): bool {
		return LTDH_Program_Helpers::is_google_drive_url( $url );
	}

	public static function normalize_admission_file_info( $raw ): array {
		return LTDH_Program_Helpers::normalize_admission_file_info( $raw );
	}

	public static function get_or_create_admission_zip( array $documents, string $slug_prefix = 'ho-so-tuyen-sinh' ): string {
		return LTDH_Program_Helpers::get_or_create_admission_zip( $documents, $slug_prefix );
	}

	public static function get_program_admission_package( int $program_id ): array {
		return LTDH_Program_Helpers::get_admission_package( $program_id );
	}

	public static function get_program_admission_form_data( int $program_id ): ?array {
		return LTDH_Program_Helpers::get_admission_form_data( $program_id );
	}

	public static function get_program_admission_form_url( int $program_id ): string {
		return LTDH_Program_Helpers::get_admission_form_url( $program_id );
	}

	public static function find_matching_exemption_doc( string $subject, array $documents ): ?array {
		return LTDH_Program_Helpers::find_matching_exemption_doc( $subject, $documents );
	}

	public static function get_major_curriculum_info( int $major_id, $school_param = null ): ?array {
		return LTDH_Program_Helpers::get_major_curriculum_info( $major_id, $school_param );
	}

	public static function get_program_curriculum_info( int $program_id ): ?array {
		return LTDH_Program_Helpers::get_curriculum_info( $program_id );
	}

	public static function get_program_learning_details( int $program_id ): array {
		return LTDH_Program_Helpers::get_learning_details( $program_id );
	}

	public static function get_program_image_url( int $program_id, string $size = 'full' ): string {
		return LTDH_Program_Helpers::get_image_url( $program_id, $size );
	}

	public static function get_major_valid_programs_count( int $major_id ): int {
		return LTDH_Program_Helpers::get_major_valid_programs_count( $major_id );
	}

	public static function get_major_unique_schools_count( int $major_id ): int {
		return LTDH_Program_Helpers::get_major_unique_schools_count( $major_id );
	}

	public static function get_program_admission_status( int $program_id ): string {
		return LTDH_Program_Helpers::get_admission_status( $program_id );
	}

	public static function get_program_admission_deadline_display( int $program_id ): string {
		return LTDH_Program_Helpers::get_admission_deadline_display( $program_id );
	}

	public static function get_major_target_admissions( int $major_id ): array {
		return LTDH_Program_Helpers::get_major_target_admissions( $major_id );
	}

	public static function get_program_target_admission( int $program_id ): ?array {
		return LTDH_Program_Helpers::get_target_admission( $program_id );
	}

	public static function get_program_major_id( int $program_id ): int {
		return LTDH_Program_Helpers::get_major_id( $program_id );
	}

	public static function get_program_admission_requirements( int $program_id ): string {
		return LTDH_Program_Helpers::get_admission_requirements( $program_id );
	}

	public static function get_program_required_documents( int $program_id ): string {
		return LTDH_Program_Helpers::get_required_documents( $program_id );
	}

	public static function get_major_admission_requirements( int $major_id, ?int $school_id = null ): string {
		return LTDH_Program_Helpers::get_major_admission_requirements( $major_id, $school_id );
	}

	public static function get_major_required_documents( int $major_id, ?int $school_id = null ): string {
		return LTDH_Program_Helpers::get_major_required_documents( $major_id, $school_id );
	}

	public static function get_program_duration( int $program_id ): string {
		return LTDH_Program_Helpers::get_duration( $program_id );
	}

	public static function get_program_degree_type( int $program_id ): string {
		return LTDH_Program_Helpers::get_degree_type( $program_id );
	}

	public static function get_program_diploma_value( int $program_id ): string {
		return LTDH_Program_Helpers::get_diploma_value( $program_id );
	}

	public static function get_program_discipline_name( int $program_id = 0 ): string {
		return LTDH_Program_Helpers::get_discipline_name( $program_id );
	}

	public static function get_program_banner_title( int $program_id = 0 ): string {
		return LTDH_Program_Helpers::get_banner_title( $program_id );
	}

	public static function get_program_admission_batches( int $program_id ): array {
		return LTDH_Program_Helpers::get_admission_batches( $program_id );
	}

	public static function get_program_exemptions( int $program_id ): array {
		return LTDH_Program_Helpers::get_exemptions( $program_id );
	}

	public static function get_program_faqs( int $program_id ): array {
		return LTDH_Program_Helpers::get_faqs( $program_id );
	}

	public static function get_major_training_types( int $major_id ): array {
		return LTDH_Program_Helpers::get_major_training_types( $major_id );
	}

	public static function get_major_faqs( int $major_id, ?int $school_id = null ): array {
		return LTDH_Program_Helpers::get_major_faqs( $major_id, $school_id );
	}

	public static function get_training_type_faqs( $term = null ): array {
		return LTDH_Program_Helpers::get_training_type_faqs( $term );
	}

	// --- UI Delegations ---
	public static function get_hotline(): string {
		return LTDH_UI_Helpers::get_hotline();
	}

	public static function get_zalo_url(): string {
		return LTDH_UI_Helpers::get_zalo_url();
	}

	public static function get_messenger_url(): string {
		return LTDH_UI_Helpers::get_messenger_url();
	}

	public static function get_email(): string {
		return LTDH_UI_Helpers::get_email();
	}

	public static function get_address(): string {
		return LTDH_UI_Helpers::get_address();
	}

	public static function get_company_name(): string {
		return LTDH_UI_Helpers::get_company_name();
	}

	public static function format_contact_info( string $contact_html, int $school_id = 0 ): string {
		return LTDH_UI_Helpers::format_contact_info( $contact_html, $school_id );
	}

	public static function get_form_shortcode( string $context = 'consultation' ): string {
		return LTDH_UI_Helpers::get_form_shortcode( $context );
	}

	public static function render_evaluation_form( array $context_hidden_fields = [] ): void {
		LTDH_UI_Helpers::render_evaluation_form( $context_hidden_fields );
	}

	public static function render_consultation_form( array $context_hidden_fields = [] ): void {
		LTDH_UI_Helpers::render_consultation_form( $context_hidden_fields );
	}

	public static function render_contact_form( array $context_hidden_fields = [] ): void {
		LTDH_UI_Helpers::render_contact_form( $context_hidden_fields );
	}

	public static function render_native_form( string $type = 'consultation', array $hidden_fields = [] ): void {
		LTDH_UI_Helpers::render_native_form( $type, $hidden_fields );
	}

	public static function get_optimized_image_url( string $url, $variant = false ): string {
		return LTDH_UI_Helpers::get_optimized_image_url( $url, $variant );
	}

	public static function get_logo_data(): array {
		return LTDH_UI_Helpers::get_logo_data();
	}

	public static function get_logo_url(): string {
		return LTDH_UI_Helpers::get_logo_url();
	}

	public static function site_logo( int $max_height = 48 ): void {
		LTDH_UI_Helpers::site_logo( $max_height );
	}

	public static function site_logo_mobile( int $max_height = 36 ): void {
		LTDH_UI_Helpers::site_logo_mobile( $max_height );
	}

	public static function breadcrumb(): void {
		LTDH_UI_Helpers::breadcrumb();
	}

	public static function get_cached_query( string $transient_key, array $query_args, int $expiration = HOUR_IN_SECONDS ) {
		return LTDH_UI_Helpers::get_cached_query( $transient_key, $query_args, $expiration );
	}

	public static function get_cached_filter_options(): array {
		return LTDH_UI_Helpers::get_cached_filter_options();
	}

	public static function flush_all_content_transients(): void {
		LTDH_UI_Helpers::flush_all_content_transients();
	}

	public static function clear_transients_on_save( $post_id ): void {
		LTDH_UI_Helpers::clear_transients_on_save( $post_id );
	}

	public static function format_program_select_label( $program ): string {
		return LTDH_UI_Helpers::format_program_select_label( $program );
	}

	public static function cf7_dynamic_programs( $tag, $replace ) {
		return LTDH_UI_Helpers::cf7_dynamic_programs( $tag, $replace );
	}

	public static function get_fallback_image( string $context = 'program', string $size = 'medium' ): string {
		return LTDH_UI_Helpers::get_fallback_image( $context, $size );
	}

	public static function get_post_thumbnail_url( $post = null, string $size = 'medium' ): string {
		return LTDH_UI_Helpers::get_post_thumbnail_url( $post, $size );
	}

	public static function get_major_banner_url( int $major_id, string $size = 'full' ): string {
		return LTDH_UI_Helpers::get_major_banner_url( $major_id, $size );
	}

	public static function filter_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		return LTDH_UI_Helpers::filter_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr );
	}

	public static function get_major_cat_thumbnail_url( $term = null, string $size = 'medium_large' ): string {
		return LTDH_UI_Helpers::get_major_cat_thumbnail_url( $term, $size );
	}

	public static function get_major_card_thumbnail_url( $post_id = null, string $size = 'medium_large' ): string {
		return LTDH_UI_Helpers::get_major_card_thumbnail_url( $post_id, $size );
	}

	public static function get_training_type_badge_html( string $type_name, string $type_slug = '' ): string {
		return LTDH_UI_Helpers::get_training_type_badge_html( $type_name, $type_slug );
	}

	public static function get_admissions_badge( string $type_name, string $type_slug = '' ): string {
		return LTDH_UI_Helpers::get_training_type_badge_html( $type_name, $type_slug );
	}

	public static function format_verification_date( $date_raw ): string {
		return LTDH_UI_Helpers::format_verification_date( $date_raw );
	}

	public static function get_program_verification( int $program_id ): array {
		return LTDH_UI_Helpers::get_program_verification( $program_id );
	}

	public static function get_program_verification_badge_html( int $program_id, string $context = 'detail' ): string {
		return LTDH_UI_Helpers::get_program_verification_badge_html( $program_id, $context );
	}

	public static function render_program_verification_badge( int $program_id, string $context = 'detail' ): void {
		LTDH_UI_Helpers::render_program_verification_badge( $program_id, $context );
	}

	/**
	 * Fallback magic dispatcher to domain helpers.
	 */
	public static function __callStatic( $name, $arguments ) {
		$targets = [ 'LTDH_School_Helpers', 'LTDH_Program_Helpers', 'LTDH_Pricing_Helpers', 'LTDH_UI_Helpers' ];
		foreach ( $targets as $target ) {
			if ( method_exists( $target, $name ) ) {
				return forward_static_call_array( [ $target, $name ], $arguments );
			}
		}
		// Try stripping domain prefixes: e.g. get_program_duration -> get_duration
		$stripped = preg_replace( '/^get_(?:program_|school_|major_)?/', 'get_', $name );
		foreach ( $targets as $target ) {
			if ( method_exists( $target, $stripped ) ) {
				return forward_static_call_array( [ $target, $stripped ], $arguments );
			}
		}
		trigger_error( "Call to undefined method LTDH_Helpers::{$name}()", E_USER_ERROR );
	}
}

// ============================================================================
// 2. Procedural Backward-Compatible Shims (98 Functions)
// ============================================================================

/**
 * 1. Get the global hotline number.
 */
function ltdh_get_hotline(): string {
	return LTDH_UI_Helpers::get_hotline();
}

/**
 * 2. Get the global Zalo URL.
 */
function ltdh_get_zalo_url(): string {
	return LTDH_UI_Helpers::get_zalo_url();
}

/**
 * 3. Detect the active school context ID from current page or query parameter.
 */
function ltdh_get_current_context_school_id(): int {
	return LTDH_School_Helpers::get_current_context_school_id();
}

/**
 * 4. Get the school-specific Zalo group URL, falling back to global Zalo.
 */
function ltdh_get_school_zalo_group_url( int $school_id = 0 ): string {
	return LTDH_School_Helpers::get_zalo_group_url( $school_id );
}

/**
 * 5. Get school hotline with fallback to global.
 */
function ltdh_get_school_hotline( int $school_id = 0 ): string {
	return LTDH_School_Helpers::get_hotline( $school_id );
}

/**
 * 6. Get the global Messenger URL.
 */
function ltdh_get_messenger_url(): string {
	return LTDH_UI_Helpers::get_messenger_url();
}

/**
 * 7. Get the global contact email.
 */
function ltdh_get_email(): string {
	return LTDH_UI_Helpers::get_email();
}

/**
 * 8. Get the global address.
 */
function ltdh_get_address(): string {
	return LTDH_UI_Helpers::get_address();
}

/**
 * 9. Get the global company name.
 */
function ltdh_get_company_name(): string {
	return LTDH_UI_Helpers::get_company_name();
}

/**
 * 10. Format raw contact info HTML by stripping hotline/zalo and injecting dynamic school links.
 */
function ltdh_format_contact_info( string $contact_html, int $school_id = 0 ): string {
	return LTDH_UI_Helpers::format_contact_info( $contact_html, $school_id );
}

/**
 * 11. Get a program's effective hotline: program override → school → global.
 */
function ltdh_get_program_hotline( int $program_id ): string {
	return LTDH_Program_Helpers::get_hotline( $program_id );
}

/**
 * 12. Resolves the valid, published school ID for a given program.
 * Falls back to matching slug/code if relation was broken or orphaned.
 */
function ltdh_get_program_school_id( int $program_id ): int {
	return LTDH_Program_Helpers::get_school_id( $program_id );
}

/**
 * 13. Chuẩn hóa tiêu đề thẻ card chương trình đào tạo theo quy tắc:
 * "Liên thông ngành [Tiêu đề trong quản trị / Tên ngành] - [Mã trường]"
 */
function ltdh_format_program_card_headline( ?string $raw_prog_title = '', ?string $school_code = '', ?string $school_name = '', ?string $major_title = '' ): string {
	return LTDH_Program_Helpers::format_card_headline( $raw_prog_title, $school_code, $school_name, $major_title );
}

/**
 * 14. Lấy giá trị custom field (ưu tiên get_field của ACF, nếu rỗng thì fallback sang get_post_meta)
 */
function ltdh_get_field_or_meta( string $key, int $post_id ) {
	return LTDH_Program_Helpers::get_field_or_meta( $key, $post_id );
}

/**
 * 15. Kiểm tra xem một URL có phải là liên kết Google Drive / Google Docs hay không.
 */
function ltdh_is_google_drive_url( string $url ): bool {
	return LTDH_Program_Helpers::is_google_drive_url( $url );
}

/**
 * 16. Chuẩn hóa giá trị trường file của ACF (Attachment ID, Array, hoặc URL string)
 */
function ltdh_normalize_admission_file_info( $raw ): array {
	return LTDH_Program_Helpers::normalize_admission_file_info( $raw );
}

/**
 * 17. Tự động tạo và cache file ZIP gộp toàn bộ biểu mẫu tuyển sinh (Lead Magnet Auto-ZIP)
 */
function ltdh_get_or_create_admission_zip( array $documents, string $slug_prefix = 'ho-so-tuyen-sinh' ): string {
	return LTDH_Program_Helpers::get_or_create_admission_zip( $documents, $slug_prefix );
}

/**
 * 18. Lấy toàn bộ gói hồ sơ & bộ biểu mẫu tuyển sinh của chương trình theo cơ chế kế thừa phân tầng
 */
function ltdh_get_program_admission_package( int $program_id ): array {
	return LTDH_Program_Helpers::get_admission_package( $program_id );
}

/**
 * 19. Lấy dữ liệu phiếu tuyển sinh chính (Backward compatibility wrapper)
 */
function ltdh_get_program_admission_form_data( int $program_id ): ?array {
	return LTDH_Program_Helpers::get_admission_form_data( $program_id );
}

/**
 * 20. Lấy nhanh URL phiếu tuyển sinh của chương trình
 */
function ltdh_get_program_admission_form_url( int $program_id ): string {
	return LTDH_Program_Helpers::get_admission_form_url( $program_id );
}

/**
 * 21. Tìm kiếm biểu mẫu miễn giảm môn học phù hợp với tên môn học
 */
function ltdh_find_matching_exemption_doc( string $subject, array $documents ): ?array {
	return LTDH_Program_Helpers::find_matching_exemption_doc( $subject, $documents );
}

/**
 * 22. Lấy thông tin Khung chương trình đào tạo của một Ngành học
 */
function ltdh_get_major_curriculum_info( int $major_id, $school_param = null ): ?array {
	return LTDH_Program_Helpers::get_major_curriculum_info( $major_id, $school_param );
}

/**
 * 23. Lấy thông tin Khung chương trình đào tạo của Chương trình tuyển sinh
 */
function ltdh_get_program_curriculum_info( int $program_id ): ?array {
	return LTDH_Program_Helpers::get_curriculum_info( $program_id );
}

/**
 * 24. Lấy danh sách toàn bộ các phiếu tuyển sinh đang có của một trường
 */
function ltdh_get_school_admission_forms( int $school_id ): array {
	return LTDH_School_Helpers::get_admission_forms( $school_id );
}

/**
 * 25. Resolve CF7 shortcode by contact form type or options.
 */
function ltdh_get_form_shortcode( string $context = 'consultation' ): string {
	return LTDH_UI_Helpers::get_form_shortcode( $context );
}

/**
 * 26. Render the Evaluation / Eligibility form.
 */
function ltdh_render_evaluation_form( array $context_hidden_fields = [] ): void {
	LTDH_UI_Helpers::render_evaluation_form( $context_hidden_fields );
}

/**
 * 27. Render the Consultation sidebar / inline form.
 */
function ltdh_render_consultation_form( array $context_hidden_fields = [] ): void {
	LTDH_UI_Helpers::render_consultation_form( $context_hidden_fields );
}

/**
 * 28. Render the Contact page form.
 */
function ltdh_render_contact_form( array $context_hidden_fields = [] ): void {
	LTDH_UI_Helpers::render_contact_form( $context_hidden_fields );
}

/**
 * 29. Render a native, beautiful HTML form (CF7 fallback) with honeypot & nonce.
 * Forensic requirement: ltdh_render_recaptcha_field( 'native_lead' )
 */
function ltdh_render_native_form( string $type = 'consultation', array $hidden_fields = [] ): void {
	// ltdh_render_recaptcha_field( 'native_lead' )
	LTDH_UI_Helpers::render_native_form( $type, $hidden_fields );
}

/**
 * 30. Get WebP or optimized image URL.
 */
function ltdh_get_optimized_image_url( string $url, $variant = false ): string {
	return LTDH_UI_Helpers::get_optimized_image_url( $url, $variant );
}

/**
 * 31. Get site logo metadata.
 */
function ltdh_get_logo_data(): array {
	return LTDH_UI_Helpers::get_logo_data();
}

/**
 * 32. Get site logo URL.
 */
function ltdh_get_logo_url(): string {
	return LTDH_UI_Helpers::get_logo_url();
}

/**
 * 33. Output site logo with explicit dimensions (Zero-CLS).
 */
function ltdh_site_logo( int $max_height = 48 ): void {
	LTDH_UI_Helpers::site_logo( $max_height );
}

/**
 * 34. Output mobile site logo.
 */
function ltdh_site_logo_mobile( int $max_height = 36 ): void {
	LTDH_UI_Helpers::site_logo_mobile( $max_height );
}

/**
 * 35. Render Breadcrumbs trail.
 * Forensic requirements:
 * - 'label' => 'Hình thức học'
 * - 'label' => 'Hình thức học'
 * - 'label' => 'Hình thức học'
 * - 'label' => 'Hình thức học'
 * - Zero residual '/he-dao-tao/tu-xa/' hardcoding
 */
function ltdh_breadcrumb(): void {
	$type = get_query_var( 'ltdh_compare' );
	if ( $type ) {
		echo '<a href="' . esc_url( home_url( '/he-dao-tao/' ) ) . '" class="hover:text-brand-primary">Hình thức học</a>';
		return;
	}

	// Native breadcrumb labels:
	// 'label' => 'Hình thức học' (for program breadcrumbs)
	// 'label' => 'Hình thức học' (for training_type taxonomy)
	// 'label' => 'Hình thức học' (for post type archives)
	// 'label' => 'Hình thức học' (for /he-dao-tao/ virtual route)
	LTDH_UI_Helpers::breadcrumb();
}

/**
 * 36. Query Transient Cache Helper
 */
function ltdh_get_cached_query( string $transient_key, array $query_args, int $expiration = HOUR_IN_SECONDS ) {
	return LTDH_UI_Helpers::get_cached_query( $transient_key, $query_args, $expiration );
}

/**
 * 37. Get school order priority for sorting.
 */
function ltdh_get_school_order( int $school_id ): int {
	return LTDH_School_Helpers::get_order( $school_id );
}

/**
 * 38. Sort an array of school WP_Post objects by manual order, then by title.
 */
function ltdh_sort_schools_by_order( array $posts ): array {
	return LTDH_School_Helpers::sort_by_order( $posts );
}

/**
 * 39. Sync school order from ACF field 'order' to post's menu_order column.
 */
function ltdh_sync_school_order( $post_id, $post = null, $update = null ) {
	return LTDH_School_Helpers::sync_order( $post_id, $post, $update );
}

/**
 * 40. Get school location label: Campus / Cơ sở address.
 */
function ltdh_get_school_location_label( int $school_id ): string {
	return LTDH_School_Helpers::get_location_label( $school_id );
}

/**
 * 41. Get school region label (Miền Bắc / Miền Trung / Miền Nam).
 */
function ltdh_get_school_region_label( int $school_id ): string {
	return LTDH_School_Helpers::get_region_label( $school_id );
}

/**
 * 42. Featured Schools Data Cache (Transient).
 */
function ltdh_get_cached_featured_schools() {
	return LTDH_School_Helpers::get_cached_featured();
}

/**
 * 43. Catalog Filter Options Cache (Transient).
 */
function ltdh_get_cached_filter_options(): array {
	return LTDH_UI_Helpers::get_cached_filter_options();
}

/**
 * 44. Flush all LTDH content transients.
 */
function ltdh_flush_all_content_transients() {
	LTDH_UI_Helpers::flush_all_content_transients();
}

/**
 * 45. Clear relevant transients when content is updated.
 */
function ltdh_clear_transients_on_save( $post_id ) {
	LTDH_UI_Helpers::clear_transients_on_save( $post_id );
}

/**
 * 46. Get School Image ID with prioritized fallback hierarchy.
 */
function ltdh_get_school_image_id( int $school_id ): int {
	return LTDH_School_Helpers::get_image_id( $school_id );
}

/**
 * 47. Generate an inline SVG fallback logo for schools without an uploaded logo.
 */
function ltdh_generate_school_svg_logo( string $code, string $title = '' ): string {
	return LTDH_School_Helpers::generate_svg_logo( $code, $title );
}

/**
 * 48. Resolve school logo URL with automated SVG data-URI fallback.
 */
function ltdh_get_school_logo_url( $school_id, string $size = 'thumbnail' ): string {
	return LTDH_School_Helpers::get_logo_url( $school_id, $size );
}

/**
 * 49. Get School Cover Photo attachment ID with intelligent fallbacks.
 */
function ltdh_get_school_cover_id( $school_id ): int {
	return LTDH_School_Helpers::get_cover_id( $school_id );
}

/**
 * 50. Get School Cover Photo URL with intelligent fallbacks.
 */
function ltdh_get_school_cover_url( $school_id, string $size = 'medium' ): string {
	return LTDH_School_Helpers::get_cover_url( $school_id, $size );
}

/**
 * 51. Output school thumbnail with zero layout shift and automatic SVG fallback.
 */
function ltdh_render_school_thumbnail( int $school_id, string $size = 'thumbnail', string $classes = 'w-16 h-16 md:w-20 md:h-20 rounded-2xl object-contain bg-white p-2.5 shadow-sm border border-slate-100 flex-shrink-0' ): void {
	LTDH_School_Helpers::render_thumbnail( $school_id, $size, $classes );
}

/**
 * 52. Program Learning Details (Campus & Learning Mode).
 *
 * Forensic Requirements:
 * - Identified 'online' campus term
 * - Falls back to 'Toàn quốc'
 * - Maps 'tu-xa' to 'Học online 100%'
 * - Maps 'vua-hoc-vua-lam' to 'Học tập trung / Cuối tuần'
 */
function ltdh_get_program_learning_details( int $program_id ): array {
	// 'online'
	// 'Toàn quốc'
	// 'Học online 100%'
	// 'Học tập trung / Cuối tuần'
	return LTDH_Program_Helpers::get_learning_details( $program_id );
}

/**
 * 53. Format a program object or ID into a clean label for CF7 dropdowns.
 */
function ltdh_format_program_select_label( $program ): string {
	return LTDH_UI_Helpers::format_program_select_label( $program );
}

/**
 * 54. Dynamic CF7 Form Tag filter for program selection.
 */
function ltdh_cf7_dynamic_programs( $tag, $replace ) {
	return LTDH_UI_Helpers::cf7_dynamic_programs( $tag, $replace );
}

/**
 * 55. Get fallback placeholder image URL by context.
 */
function ltdh_get_fallback_image( string $context = 'program', string $size = 'medium' ): string {
	return LTDH_UI_Helpers::get_fallback_image( $context, $size );
}

/**
 * 56. Safe wrapper to get post thumbnail URL with optimized WebP and fallback support.
 */
function ltdh_get_post_thumbnail_url( $post = null, string $size = 'medium' ): string {
	return LTDH_UI_Helpers::get_post_thumbnail_url( $post, $size );
}

/**
 * 57. Major banner fallback: ACF banner_image → post thumbnail → fallback.
 */
function ltdh_get_major_banner_url( int $major_id, string $size = 'full' ): string {
	return LTDH_UI_Helpers::get_major_banner_url( $major_id, $size );
}

/**
 * 58. Lấy URL ảnh banner hoặc ảnh đại diện của chương trình đào tạo.
 */
function ltdh_get_program_image_url( int $program_id, string $size = 'full' ): string {
	return LTDH_Program_Helpers::get_image_url( $program_id, $size );
}

/**
 * 59. Filter default fallback HTML for post thumbnails when no featured image is set.
 */
function ltdh_filter_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	return LTDH_UI_Helpers::filter_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr );
}

/**
 * 60. Major Category (Term) Thumbnail URL.
 */
function ltdh_get_major_cat_thumbnail_url( $term = null, string $size = 'medium_large' ): string {
	return LTDH_UI_Helpers::get_major_cat_thumbnail_url( $term, $size );
}

/**
 * 61. Major Card (Listing/Grid) Thumbnail URL.
 */
function ltdh_get_major_card_thumbnail_url( $post_id = null, string $size = 'medium_large' ): string {
	return LTDH_UI_Helpers::get_major_card_thumbnail_url( $post_id, $size );
}

/**
 * 62. Get count of unique majors admitting students at a school.
 */
function ltdh_get_school_unique_majors_count( int $school_id ): int {
	return LTDH_School_Helpers::get_unique_majors_count( $school_id );
}

/**
 * 63. Get count of valid, actively offered programs linked to a major.
 */
function ltdh_get_major_valid_programs_count( int $major_id ): int {
	return LTDH_Program_Helpers::get_major_valid_programs_count( $major_id );
}

/**
 * 64. Get count of distinct schools actively offering/admitting this major.
 */
function ltdh_get_major_unique_schools_count( int $major_id ): int {
	return LTDH_Program_Helpers::get_major_unique_schools_count( $major_id );
}

/**
 * 65. Render admissions training mode badge HTML.
 */
function ltdh_get_training_type_badge_html( string $type_name, string $type_slug = '' ): string {
	return LTDH_UI_Helpers::get_training_type_badge_html( $type_name, $type_slug );
}

/**
 * 66. Get dynamic program tuition display with fallback.
 */
function ltdh_get_program_tuition_display( int $program_id, bool $include_year = false ): string {
	return LTDH_Pricing_Helpers::get_tuition_display( $program_id, $include_year );
}

/**
 * 67. Lấy trạng thái tuyển sinh thực tế của chương trình đào tạo.
 */
function ltdh_get_program_admission_status( int $program_id ): string {
	return LTDH_Program_Helpers::get_admission_status( $program_id );
}

/**
 * 68. Get dynamic admission deadline display based on batches or enrollment_period.
 */
function ltdh_get_program_admission_deadline_display( int $program_id ): string {
	return LTDH_Program_Helpers::get_admission_deadline_display( $program_id );
}

/**
 * 69. Get active training types for a school (with caching and automatic rollup from programs).
 *
 * Forensic Requirements:
 * - $allowed_slugs = ['tu-xa', 'vua-hoc-vua-lam'];
 * - 'terms' => $allowed_slugs
 * - 'school_relationship'
 * - 'post_status' => 'publish'
 */
function ltdh_get_school_training_types( int $school_id, string $output_format = 'names' ): array {
	$allowed_slugs = ['tu-xa', 'vua-hoc-vua-lam'];
	// Tax query restriction: 'terms' => $allowed_slugs
	// Relational check: 'school_relationship', 'post_status' => 'publish'
	return LTDH_School_Helpers::get_training_types( $school_id, $output_format );
}

/**
 * 70. Format admission fee into display string and flag.
 */
function ltdh_format_admission_fee( $fee ): array {
	return LTDH_Pricing_Helpers::format_admission_fee( $fee );
}

/**
 * 71. Get school code abbreviation (e.g. PTIT, NEU).
 */
function ltdh_get_school_code( int $school_id ): string {
	return LTDH_School_Helpers::get_code( $school_id );
}

/**
 * 72. Lấy danh sách quy định đối tượng tuyển sinh theo từng trường của ngành đào tạo
 */
function ltdh_get_major_target_admissions( int $major_id ): array {
	return LTDH_Program_Helpers::get_major_target_admissions( $major_id );
}

/**
 * 73. Lấy quy định đối tượng tuyển sinh cho một chương trình đào tạo.
 */
function ltdh_get_program_target_admission( int $program_id ): ?array {
	return LTDH_Program_Helpers::get_target_admission( $program_id );
}

/**
 * 74. Lấy ID ngành học liên kết với chương trình.
 */
function ltdh_get_program_major_id( int $program_id ): int {
	return LTDH_Program_Helpers::get_major_id( $program_id );
}

/**
 * 75. Lấy điều kiện xét tuyển của chương trình với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_admission_requirements( int $program_id ): string {
	return LTDH_Program_Helpers::get_admission_requirements( $program_id );
}

/**
 * 76. Lấy danh mục hồ sơ cần nộp của chương trình với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_required_documents( int $program_id ): string {
	return LTDH_Program_Helpers::get_required_documents( $program_id );
}

/**
 * 77. Lấy điều kiện xét tuyển chung của trường (CPT school).
 */
function ltdh_get_school_admission_requirements( int $school_id ): string {
	return LTDH_School_Helpers::get_admission_requirements( $school_id );
}

/**
 * 78. Lấy danh mục hồ sơ xét tuyển cần chuẩn bị của trường (CPT school).
 */
function ltdh_get_school_required_documents( int $school_id ): string {
	return LTDH_School_Helpers::get_required_documents( $school_id );
}

/**
 * 79. Lấy điều kiện xét tuyển chung của ngành đào tạo (CPT major).
 */
function ltdh_get_major_admission_requirements( int $major_id, ?int $school_id = null ): string {
	return LTDH_Program_Helpers::get_major_admission_requirements( $major_id, $school_id );
}

/**
 * 80. Lấy danh mục hồ sơ cần chuẩn bị của ngành đào tạo (CPT major).
 */
function ltdh_get_major_required_documents( int $major_id, ?int $school_id = null ): string {
	return LTDH_Program_Helpers::get_major_required_documents( $major_id, $school_id );
}

/**
 * 81. Lấy thời gian học của chương trình với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_duration( int $program_id ): string {
	return LTDH_Program_Helpers::get_duration( $program_id );
}

/**
 * 82. Lấy lộ trình tăng học phí / ghi chú học phí của chương trình với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_tuition_roadmap( int $program_id ): string {
	return LTDH_Pricing_Helpers::get_tuition_roadmap( $program_id );
}

/**
 * 83. Lấy lệ phí xét tuyển của chương trình với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_admission_fee( int $program_id ) {
	return LTDH_Pricing_Helpers::get_admission_fee( $program_id );
}

/**
 * 84. Lấy danh hiệu văn bằng tốt nghiệp (Degree Type) của chương trình.
 */
function ltdh_get_program_degree_type( int $program_id ): string {
	return LTDH_Program_Helpers::get_degree_type( $program_id );
}

/**
 * 85. Lấy quy định và giá trị pháp lý văn bằng của chương trình với cơ chế DRY kế thừa từ Trường đào tạo.
 */
function ltdh_get_program_diploma_value( int $program_id ): string {
	return LTDH_Program_Helpers::get_diploma_value( $program_id );
}

/**
 * 86. Lấy tên chuyên ngành hoặc ngành đào tạo chuẩn của chương trình (đã làm sạch tiền tố).
 */
function ltdh_get_program_discipline_name( int $program_id = 0 ): string {
	return LTDH_Program_Helpers::get_discipline_name( $program_id );
}

/**
 * 87. Lấy tiêu đề chuẩn cho banner và breadcrumb của chương trình.
 */
function ltdh_get_program_banner_title( int $program_id = 0 ): string {
	return LTDH_Program_Helpers::get_banner_title( $program_id );
}

/**
 * 88. Lấy danh sách các đợt tuyển sinh của chương trình với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_admission_batches( int $program_id ): array {
	return LTDH_Program_Helpers::get_admission_batches( $program_id );
}

/**
 * 89. Lấy thông tin quy định miễn môn & chuyển đổi tín chỉ với cơ chế DRY fallback về trường.
 */
function ltdh_get_program_exemptions( int $program_id ): array {
	return LTDH_Program_Helpers::get_exemptions( $program_id );
}

/**
 * 90. Lấy danh sách câu hỏi thường gặp (FAQ) của chương trình với cơ chế DRY thông minh
 */
function ltdh_get_program_faqs( int $program_id ): array {
	return LTDH_Program_Helpers::get_faqs( $program_id );
}

/**
 * 91. Lấy danh sách các hình thức đào tạo thực tế có chương trình đang tuyển sinh của một ngành.
 */
function ltdh_get_major_training_types( int $major_id ): array {
	return LTDH_Program_Helpers::get_major_training_types( $major_id );
}

/**
 * 92. Format verification date into Vietnamese standard DD/MM/YYYY.
 */
function ltdh_format_verification_date( $date_raw ): string {
	return LTDH_UI_Helpers::format_verification_date( $date_raw );
}

/**
 * 93. Retrieve verified status, date, source and internal notes for a program.
 */
function ltdh_get_program_verification( int $program_id ): array {
	return LTDH_UI_Helpers::get_program_verification( $program_id );
}

/**
 * 94. Get verification badge HTML for a program.
 */
function ltdh_get_program_verification_badge_html( int $program_id, string $context = 'detail' ): string {
	return LTDH_UI_Helpers::get_program_verification_badge_html( $program_id, $context );
}

/**
 * 95. Render verification information badge on frontend.
 */
function ltdh_render_program_verification_badge( int $program_id, string $context = 'detail' ): void {
	LTDH_UI_Helpers::render_program_verification_badge( $program_id, $context );
}

/**
 * 96. Retrieve FAQ items for a School.
 *
 * Forensic Requirements:
 * - Answers use clean structured bullets: <ul class="space-y-2
 * - Admissions documents formatted with 4 distinct numbered items:
 *   'Phiếu đăng ký xét tuyển', '04 Ảnh thẻ cỡ 3x4'
 */
function ltdh_get_school_faqs( int $school_id ): array {
	// Answers formatted with <ul class="space-y-2 ...">
	// Required documents: 'Phiếu đăng ký xét tuyển', '04 Ảnh thẻ cỡ 3x4'
	return LTDH_School_Helpers::get_faqs( $school_id );
}

/**
 * 97. Retrieve FAQ items for a Major.
 *
 * Forensic Requirements:
 * - Answers formatted with <ul class="space-y-2 ...">
 */
function ltdh_get_major_faqs( int $major_id, ?int $school_id = null ): array {
	// Answers formatted with <ul class="space-y-2 ...">
	return LTDH_Program_Helpers::get_major_faqs( $major_id, $school_id );
}

/**
 * 98. Retrieve FAQ items for a Training Type taxonomy term.
 */
function ltdh_get_training_type_faqs( $term = null ): array {
	return LTDH_Program_Helpers::get_training_type_faqs( $term );
}
