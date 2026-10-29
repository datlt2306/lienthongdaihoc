<?php
/**
 * Comparison Modal / Tray Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'ltdh_compare_should_load' ) && ! ltdh_compare_should_load() ) {
	return;
}

get_template_part( 'template-parts/compare/tray' );
