<?php
/**
 * Theme Constants — Single source of truth for keys, slugs, and identifiers.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LTDH_VERSION', '2.0.0' );

// Database table names (with prefix handled at runtime).
define( 'LTDH_TABLE_LEADS', 'ltdh_leads' );
define( 'LTDH_TABLE_ELIGIBILITY', 'ltdh_eligibility_checks' );

// Option keys stored via update_option / get_option.
define( 'LTDH_OPT_REWRITE_FLUSHED', 'ltdh_rewrite_flushed_v2' );
define( 'LTDH_OPT_ELIG_TABLE_VERSION', 'ltdh_elig_table_version' );

// ACF Options Page slug.
define( 'LTDH_OPTIONS_PAGE', 'ltdh-settings' );

// Transient keys.
define( 'LTDH_TRANSIENT_FEATURED_SCHOOLS', 'ltdh_featured_schools' );

// Rewrite tag names.
define( 'LTDH_QV_COMPARE', 'ltdh_compare' );
define( 'LTDH_QV_COMPARE_SLUG', 'ltdh_compare_slug' );

// Nonce action names.
define( 'LTDH_NONCE_COMPARE', 'ltdh_compare_nonce' );
define( 'LTDH_NONCE_ELIG', 'ltdh_elig_nonce' );

// AJAX action names.
define( 'LTDH_AJAX_FILTER_PROGRAMS', 'ltdh_filter_programs' );
define( 'LTDH_AJAX_COMPARE_ADD', 'ltdh_compare_add' );
define( 'LTDH_AJAX_COMPARE_REMOVE', 'ltdh_compare_remove' );
define( 'LTDH_AJAX_ELIG_CHECK', 'ltdh_elig_check' );
define( 'LTDH_AJAX_ELIG_LEAD', 'ltdh_elig_lead' );

// REST namespace.
define( 'LTDH_REST_NS', 'ltdh/v1' );

// Post type slugs.
define( 'LTDH_CPT_SCHOOL', 'school' );
define( 'LTDH_CPT_MAJOR', 'major' );
define( 'LTDH_CPT_PROGRAM', 'program' );
define( 'LTDH_CPT_GUIDE', 'guide' );

// Taxonomy slugs.
define( 'LTDH_TAX_TRAINING_TYPE', 'training_type' );
define( 'LTDH_TAX_CAMPUS', 'campus' );
define( 'LTDH_TAX_REGION', 'region' );
define( 'LTDH_TAX_MAJOR_CAT', 'major_cat' );

// Meta keys (business-facing).
define( 'LTDH_META_SCHOOL_REL', 'school_relationship' );
define( 'LTDH_META_MAJOR_REL', 'major_relationship' );
define( 'LTDH_META_ADMISSION_STATUS', 'admission_status' );
define( 'LTDH_META_OFFERED_PROGRAMS', '_offered_programs' );
define( 'LTDH_META_LAST_SCHOOL', '_last_known_school_id' );
define( 'LTDH_META_LAST_MAJOR', '_last_known_major_id' );
define( 'LTDH_META_TUITION', 'tuition_fee' );
define( 'LTDH_META_DURATION', 'duration' );
define( 'LTDH_META_SCHEDULE', 'schedule' );
define( 'LTDH_META_AD_GROUPS', 'admission_groups' );

// Admission status values.
define( 'LTDH_STATUS_OPEN', 'tuyen-sinh' );
define( 'LTDH_STATUS_PAUSED', 'tam-ngung' );
define( 'LTDH_STATUS_COMING_SOON', 'sap-mo' );

// ====================================================
// Telegram Bot Settings (For Lead Notifications)
// Can be overridden in wp-config.php for production security
// ====================================================
if ( ! defined( 'LTDH_TELEGRAM_BOT_TOKEN' ) ) {
	define( 'LTDH_TELEGRAM_BOT_TOKEN', '' );
}
if ( ! defined( 'LTDH_TELEGRAM_CHAT_ID' ) ) {
	define( 'LTDH_TELEGRAM_CHAT_ID', '' );
}
