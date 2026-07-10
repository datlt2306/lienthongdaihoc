<?php
/**
 * Custom Post Types & Taxonomies Handler
 *
 * NOTE: Registering custom post types (school, major, program, guide) and
 * taxonomies (training_type, campus, region) is now managed through ACF Pro.
 * Import the definitions using 'acf-import-cpts.json' in the theme directory.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter taxonomy registration arguments to guarantee correct rewrite slugs
 */
add_filter( 'register_taxonomy_args', 'ltdh_override_taxonomy_rewrite_slugs', 10, 3 );
function ltdh_override_taxonomy_rewrite_slugs( $args, $taxonomy, $object_type ) {
	if ( $taxonomy === 'training_type' ) {
		if ( ! isset( $args['rewrite'] ) || ! is_array( $args['rewrite'] ) ) {
			$args['rewrite'] = [];
		}
		$args['rewrite']['slug']       = 'he-dao-tao';
		$args['rewrite']['with_front'] = false;
	}
	
	if ( $taxonomy === 'campus' ) {
		if ( ! isset( $args['rewrite'] ) || ! is_array( $args['rewrite'] ) ) {
			$args['rewrite'] = [];
		}
		$args['rewrite']['slug']       = 'co-so';
		$args['rewrite']['with_front'] = false;
	}
	
	return $args;
}

/**
 * Filter CPT registration arguments to use Vietnamese rewrite slugs
 */
add_filter( 'register_post_type_args', 'ltdh_override_cpt_rewrite_slugs', 10, 2 );
function ltdh_override_cpt_rewrite_slugs( $args, $post_type ) {
	$slug_map = [
		'program' => 'chuong-trinh',
		'school'  => 'truong',
		'major'   => 'nganh-hoc',
	];

	if ( isset( $slug_map[ $post_type ] ) ) {
		if ( ! isset( $args['rewrite'] ) || ! is_array( $args['rewrite'] ) ) {
			$args['rewrite'] = [];
		}
		$args['rewrite']['slug']       = $slug_map[ $post_type ];
		$args['rewrite']['with_front'] = false;

		// Ensure thumbnail support for all CPTs
		if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
			$args['supports'] = [ 'title', 'editor', 'thumbnail' ];
		} elseif ( ! in_array( 'thumbnail', $args['supports'], true ) ) {
			$args['supports'][] = 'thumbnail';
		}
	}

	return $args;
}
