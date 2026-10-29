<?php
/**
 * BlankSlate Universal Fallback Loop
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		get_template_part( 'entry' );
		if ( comments_open() ) {
			comments_template();
		}
	endwhile;
endif;

get_template_part( 'nav', 'below' );

get_footer();
