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
define( 'LTDH_OPT_REWRITE_FLUSHED', 'ltdh_rewrite_flushed_v3' );
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
define( 'LTDH_META_ADMISSION_FEE', 'admission_fee' );
define( 'LTDH_META_DURATION', 'duration' );
define( 'LTDH_META_SCHEDULE', 'schedule' );
define( 'LTDH_META_AD_GROUPS', 'admission_groups' );

// Admission status values.
define( 'LTDH_STATUS_OPEN', 'tuyen-sinh' );
define( 'LTDH_STATUS_PAUSED', 'tam-ngung' );
define( 'LTDH_STATUS_COMING_SOON', 'sap-mo' );

// Verification meta keys and status values.
define( 'LTDH_META_VERIFICATION_STATUS', 'verification_status' );
define( 'LTDH_META_LAST_VERIFIED_AT', 'last_verified_at' );
define( 'LTDH_META_VERIFICATION_SOURCE', 'verification_source' );
define( 'LTDH_META_VERIFICATION_SOURCE_TITLE', 'verification_source_title' );
define( 'LTDH_META_VERIFICATION_NOTES', 'verification_notes' );

define( 'LTDH_VERIF_STATUS_UNVERIFIED', 'unverified' );
define( 'LTDH_VERIF_STATUS_PARTIAL', 'partially_verified' );
define( 'LTDH_VERIF_STATUS_VERIFIED', 'verified' );

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

// ====================================================
// CRM Integration Settings (ERPNext / Frappe CRM)
// Can be overridden in wp-config.php for production security
// ====================================================
if ( ! defined( 'LTDH_DEFAULT_CRM_TYPE' ) ) {
	define( 'LTDH_DEFAULT_CRM_TYPE', 'erpnext' );
}
if ( ! defined( 'LTDH_ERPNEXT_ENDPOINT' ) ) {
	define( 'LTDH_ERPNEXT_ENDPOINT', 'http://localhost:8080/api/resource/Lead' );
}
if ( ! defined( 'LTDH_ERPNEXT_TOKEN' ) ) {
	define( 'LTDH_ERPNEXT_TOKEN', '45d010c5e85cfb8:adb49e4e04a729f' );
}
if ( ! defined( 'LTDH_ERPNEXT_COMPANY' ) ) {
	define( 'LTDH_ERPNEXT_COMPANY', 'Liên thông giao thông vận tải' );
}

// ====================================================
// Google reCAPTCHA Security Settings
// Can be overridden in wp-config.php for production security
// ====================================================
if ( ! defined( 'LTDH_RECAPTCHA_ENABLED' ) ) {
	define( 'LTDH_RECAPTCHA_ENABLED', null );
}
if ( ! defined( 'LTDH_RECAPTCHA_VERSION' ) ) {
	define( 'LTDH_RECAPTCHA_VERSION', 'v3' );
}
if ( ! defined( 'LTDH_RECAPTCHA_SITE_KEY' ) ) {
	define( 'LTDH_RECAPTCHA_SITE_KEY', '' );
}
if ( ! defined( 'LTDH_RECAPTCHA_SECRET_KEY' ) ) {
	define( 'LTDH_RECAPTCHA_SECRET_KEY', '' );
}
if ( ! defined( 'LTDH_RECAPTCHA_MIN_SCORE' ) ) {
	define( 'LTDH_RECAPTCHA_MIN_SCORE', 0.5 );
}

// ====================================================
// Email Notification Settings
// Can be overridden in wp-config.php for production security
// ====================================================
if ( ! defined( 'LTDH_EMAIL_NOTIFICATIONS_ENABLED' ) ) {
	define( 'LTDH_EMAIL_NOTIFICATIONS_ENABLED', true );
}
if ( ! defined( 'LTDH_EMAIL_STUDENT_CONFIRMATION' ) ) {
	define( 'LTDH_EMAIL_STUDENT_CONFIRMATION', true );
}
if ( ! defined( 'LTDH_EMAIL_ADMIN_NOTIFICATION' ) ) {
	define( 'LTDH_EMAIL_ADMIN_NOTIFICATION', true );
}
if ( ! defined( 'LTDH_EMAIL_ADMIN_RECIPIENT' ) ) {
	define( 'LTDH_EMAIL_ADMIN_RECIPIENT', '' );
}
if ( ! defined( 'LTDH_EMAIL_FROM_NAME' ) ) {
	define( 'LTDH_EMAIL_FROM_NAME', 'Cổng Tuyển Sinh Liên Thông Đại Học' );
}
if ( ! defined( 'LTDH_EMAIL_FROM_ADDRESS' ) ) {
	define( 'LTDH_EMAIL_FROM_ADDRESS', '' );
}
