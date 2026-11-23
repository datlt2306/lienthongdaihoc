<?php
/**
 * The sidebar template for single major posts.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id  = get_the_ID();
$school_id = isset( $args['school_id'] ) ? absint( $args['school_id'] ) : 0;

if ( ! $school_id ) {
	$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
	if ( ! empty( $school_param ) ) {
		if ( is_numeric( $school_param ) ) {
			$school_obj = get_post( intval( $school_param ) );
		} else {
			$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
			$school_obj = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
		}
		if ( $school_obj && 'publish' === $school_obj->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $school_obj->post_type ) {
			$school_id = $school_obj->ID;
		}
	}
}

$has_dynamic_checker = (
	( is_active_sidebar( 'sidebar-major' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-major' ) ) ||
	( ! is_active_sidebar( 'sidebar-major' ) && $school_id && is_active_sidebar( 'sidebar-school' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-school' ) )
);
?>

<aside id="major-sidebar" class="widget-area space-y-4 lg:space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên ngành đào tạo', 'ltdh' ); ?>">
	<?php
	if ( $school_id && function_exists( 'ltdh_render_school_info_card' ) ) :
		ltdh_render_school_info_card( $school_id );
	endif;

	// 1. Luôn gắn mặc định Widget Kiểm tra điều kiện ở thanh bên ngành đào tạo
	if ( ! $has_dynamic_checker ) {
		get_template_part( 'template-parts/widgets/condition-checker', null, [
			'mode'      => 'compact',
			'major_id'  => $major_id,
			'school_id' => $school_id,
		] );
	}

	if ( is_active_sidebar( 'sidebar-major' ) ) :
		dynamic_sidebar( 'sidebar-major' );
	elseif ( $school_id && is_active_sidebar( 'sidebar-school' ) ) :
		// Kế thừa widget từ Trường nếu Ngành xem trong ngữ cảnh trường và chưa setup
		dynamic_sidebar( 'sidebar-school' );
	else :
		ltdh_render_default_major_sidebar( $major_id, $school_id, true );
	endif;
	?>
</aside>
