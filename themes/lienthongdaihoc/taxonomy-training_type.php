<?php
/**
 * Taxonomy Training Type Archive Template (handles /hinh-thuc-dao-tao/)
 *
 * Delegates directly to archive-program.php to eliminate code duplication and maintain 100% DRY compliance.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require locate_template( 'archive-program.php' );
