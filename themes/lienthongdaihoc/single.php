<?php
/**
 * Single Post Template
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
			comments_template( '', true );
		}
	endwhile;
endif;
?>
<footer class="footer my-8">
	<?php get_template_part( 'nav', 'below-single' ); ?>
</footer>
<?php
get_footer();
