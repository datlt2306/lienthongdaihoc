<?php
/**
 * Query Filters — Customize main queries for archives and search.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customize archive queries for school, major, and program post types.
 */
function ltdh_customize_archive_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) ) {
		$query->set( 'post_type', LTDH_CPT_PROGRAM );
	}

	if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
		$limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : -1;
		$valid_limits = [ 10, 20, 30, 50, 100, -1 ];
		if ( in_array( $limit, $valid_limits, true ) ) {
			$query->set( 'posts_per_page', $limit );
		} else {
			$query->set( 'posts_per_page', -1 );
		}
	}

	if ( $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
		if ( ! empty( $_GET['nhom_nganh'] ) ) {
			$query->set( 'tax_query', [
				[
					'taxonomy' => LTDH_TAX_MAJOR_CAT,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $_GET['nhom_nganh'] ),
				],
			] );
		}

		$sort = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
		if ( $sort === 'title_asc' ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		} elseif ( $sort === 'title_desc' ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'DESC' );
		} elseif ( $sort === 'date_desc' ) {
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}
	}
}
add_action( 'pre_get_posts', 'ltdh_customize_archive_queries' );
