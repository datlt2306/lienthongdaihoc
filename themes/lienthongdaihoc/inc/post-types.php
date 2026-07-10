<?php
/**
 * Custom Post Types & Taxonomies Handler
 *
 * Automatically registers CPTs and Taxonomies using 'acf-import-cpts.json'
 * as the single source of truth.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ltdh_register_post_types_and_taxonomies_from_json', 0 );

function ltdh_register_post_types_and_taxonomies_from_json() {
	$json_path = get_template_directory() . '/inc/acf-import-cpts.json';
	if ( ! file_exists( $json_path ) ) {
		return;
	}

	$data = json_decode( file_get_contents( $json_path ), true );
	if ( ! is_array( $data ) ) {
		return;
	}

	foreach ( $data as $item ) {
		if ( ! isset( $item['active'] ) || ! $item['active'] ) {
			continue;
		}

		// 1. Post Types registration
		if ( isset( $item['post_type'] ) ) {
			$post_type = $item['post_type'];
			
			// Parse supports
			$supports = isset( $item['supports'] ) ? $item['supports'] : [ 'title', 'editor', 'thumbnail' ];
			if ( ! in_array( 'thumbnail', $supports, true ) ) {
				$supports[] = 'thumbnail';
			}

			// Parse rewrite slug
			$rewrite = false;
			if ( isset( $item['rewrite'] ) && $item['rewrite'] ) {
				$rewrite = [
					'slug'       => isset( $item['rewrite_slug'] ) ? $item['rewrite_slug'] : $post_type,
					'with_front' => isset( $item['rewrite_with_front'] ) ? $item['rewrite_with_front'] : false,
				];
			}

			$args = [
				'label'               => isset( $item['label'] ) ? $item['label'] : '',
				'labels'              => isset( $item['labels'] ) ? $item['labels'] : [],
				'description'         => isset( $item['description'] ) ? $item['description'] : '',
				'public'              => isset( $item['public'] ) ? $item['public'] : true,
				'hierarchical'        => isset( $item['hierarchical'] ) ? $item['hierarchical'] : false,
				'exclude_from_search' => isset( $item['exclude_from_search'] ) ? $item['exclude_from_search'] : false,
				'publicly_queryable'  => isset( $item['publicly_queryable'] ) ? $item['publicly_queryable'] : true,
				'show_ui'             => isset( $item['show_ui'] ) ? $item['show_ui'] : true,
				'show_in_menu'        => isset( $item['show_in_menu'] ) ? $item['show_in_menu'] : true,
				'show_in_nav_menus'   => isset( $item['show_in_nav_menus'] ) ? $item['show_in_nav_menus'] : true,
				'show_in_admin_bar'   => isset( $item['show_in_admin_bar'] ) ? $item['show_in_admin_bar'] : true,
				'show_in_rest'        => isset( $item['show_in_rest'] ) ? $item['show_in_rest'] : true,
				'menu_position'       => isset( $item['menu_position'] ) ? $item['menu_position'] : null,
				'menu_icon'           => isset( $item['menu_icon'] ) ? $item['menu_icon'] : null,
				'supports'            => $supports,
				'taxonomies'          => isset( $item['taxonomies'] ) ? $item['taxonomies'] : [],
				'has_archive'         => ( isset( $item['has_archive'] ) && $item['has_archive'] ) ? ( isset( $item['has_archive_slug'] ) ? $item['has_archive_slug'] : true ) : false,
				'rewrite'             => $rewrite,
				'query_var'           => isset( $item['query_var'] ) ? $item['query_var'] : true,
			];

			register_post_type( $post_type, $args );
		}

		// 2. Taxonomies registration
		if ( isset( $item['taxonomy'] ) ) {
			$taxonomy    = $item['taxonomy'];
			$object_type = isset( $item['object_type'] ) ? $item['object_type'] : [];

			$rewrite = false;
			if ( isset( $item['rewrite'] ) && $item['rewrite'] ) {
				$rewrite = [
					'slug'         => isset( $item['rewrite_slug'] ) ? $item['rewrite_slug'] : $taxonomy,
					'with_front'   => isset( $item['rewrite_with_front'] ) ? $item['rewrite_with_front'] : false,
					'hierarchical' => isset( $item['rewrite_hierarchical'] ) ? $item['rewrite_hierarchical'] : false,
				];
			}

			$args = [
				'labels'            => isset( $item['labels'] ) ? $item['labels'] : [],
				'description'       => isset( $item['description'] ) ? $item['description'] : '',
				'public'            => isset( $item['public'] ) ? $item['public'] : true,
				'publicly_queryable'=> isset( $item['publicly_queryable'] ) ? $item['publicly_queryable'] : true,
				'hierarchical'      => isset( $item['hierarchical'] ) ? $item['hierarchical'] : false,
				'show_ui'           => isset( $item['show_ui'] ) ? $item['show_ui'] : true,
				'show_in_menu'      => isset( $item['show_in_menu'] ) ? $item['show_in_menu'] : true,
				'show_in_nav_menus' => isset( $item['show_in_nav_menus'] ) ? $item['show_in_nav_menus'] : true,
				'show_in_rest'      => isset( $item['show_in_rest'] ) ? $item['show_in_rest'] : true,
				'show_admin_column' => isset( $item['show_admin_column'] ) ? $item['show_admin_column'] : false,
				'rewrite'           => $rewrite,
				'query_var'         => isset( $item['query_var'] ) ? $item['query_var'] : true,
			];

			register_taxonomy( $taxonomy, $object_type, $args );
		}
	}
}
