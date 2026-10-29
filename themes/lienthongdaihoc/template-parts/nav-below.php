<?php
/**
 * Multi-post archive pagination
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

the_posts_navigation( [
	'prev_text' => sprintf( esc_html__( '%s Bài viết cũ hơn', 'ltdh' ), '<span class="meta-nav">&larr;</span>' ),
	'next_text' => sprintf( esc_html__( 'Bài viết mới hơn %s', 'ltdh' ), '<span class="meta-nav">&rarr;</span>' ),
] );
