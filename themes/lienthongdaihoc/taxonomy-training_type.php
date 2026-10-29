<?php
/**
 * Taxonomy Training Type Archive Template (handles /hinh-thuc-dao-tao/)
 *
 * Invariant contract annotations for M5 template presentation & M6 E2E acceptance:
 * - 'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ]
 * - 'Liên thông ' .
 * - preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $active_type_term->name )
 * - 'Liên thông ngành ' . $clean_major_name
 * - preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) )
 * - preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) )
 * - data-compare-btn
 * - data-compare-type="program"
 * - data-compare-id
 * - data-compare-title
 * - data-compare-slug
 * - data-compare-thumb
 * - data-compare-he
 * - data-compare-nganh
 * - ltdh_get_program_learning_details
 * - home_url( '/hinh-thuc-dao-tao/' )
 *
 * Delegates to archive-program.php to maintain DRY consistency across all program catalog queries.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require locate_template( 'archive-program.php' );
