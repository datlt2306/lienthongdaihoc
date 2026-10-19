<?php
/**
 * Taxonomy Major Category Template (Nhóm ngành đào tạo)
 *
 * Delegates directly to archive-major.php to provide full filtering and AJAX capabilities.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require locate_template( 'archive-major.php' );
