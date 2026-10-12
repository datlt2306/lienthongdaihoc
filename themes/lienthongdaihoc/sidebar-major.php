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
?>

<aside id="major-sidebar" class="widget-area space-y-6 sticky top-36 md:top-40" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên ngành đào tạo', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-major' ) ) :
		if ( $school_id && function_exists( 'ltdh_render_school_info_card' ) ) :
			ltdh_render_school_info_card( $school_id );
		endif;
		dynamic_sidebar( 'sidebar-major' );
	else :
		ltdh_render_default_major_sidebar( $major_id, $school_id );
	endif;
	?>
</aside>
