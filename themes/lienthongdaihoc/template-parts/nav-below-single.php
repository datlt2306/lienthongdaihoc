<?php
/**
 * Single post adjacent navigation
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

the_post_navigation( [
	'prev_text' => '<span class="meta-nav">&larr;</span> %title',
	'next_text' => '%title <span class="meta-nav">&rarr;</span>',
] );
