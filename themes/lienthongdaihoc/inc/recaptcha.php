<?php
/**
 * Google reCAPTCHA Integration Module (v3 & v2)
 *
 * Provides anti-spam verification for lead capture forms, eligibility wizards,
 * consultation sidebars, and brochure download lead magnets.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve the Google reCAPTCHA Site Key.
 *
 * @return string
 */
function ltdh_get_recaptcha_site_key(): string {
	if ( defined( 'LTDH_RECAPTCHA_SITE_KEY' ) && ! empty( LTDH_RECAPTCHA_SITE_KEY ) ) {
		return trim( LTDH_RECAPTCHA_SITE_KEY );
	}

	if ( function_exists( 'get_field' ) ) {
		$key = get_field( 'recaptcha_site_key', 'option' );
		if ( ! empty( $key ) ) {
			return trim( (string) $key );
		}
	}

	$opt = get_option( 'options_recaptcha_site_key' );
	return ! empty( $opt ) ? trim( (string) $opt ) : '';
}

/**
 * Retrieve the Google reCAPTCHA Secret Key.
 *
 * @return string
 */
function ltdh_get_recaptcha_secret_key(): string {
	if ( defined( 'LTDH_RECAPTCHA_SECRET_KEY' ) && ! empty( LTDH_RECAPTCHA_SECRET_KEY ) ) {
		return trim( LTDH_RECAPTCHA_SECRET_KEY );
	}

	if ( function_exists( 'get_field' ) ) {
		$key = get_field( 'recaptcha_secret_key', 'option' );
		if ( ! empty( $key ) ) {
			return trim( (string) $key );
		}
	}

	$opt = get_option( 'options_recaptcha_secret_key' );
	return ! empty( $opt ) ? trim( (string) $opt ) : '';
}

/**
 * Retrieve the active Google reCAPTCHA version ('v3' or 'v2').
 *
 * @return string
 */
function ltdh_get_recaptcha_version(): string {
	if ( defined( 'LTDH_RECAPTCHA_VERSION' ) && ! empty( LTDH_RECAPTCHA_VERSION ) ) {
		return strtolower( trim( LTDH_RECAPTCHA_VERSION ) );
	}

	if ( function_exists( 'get_field' ) ) {
		$ver = get_field( 'recaptcha_version', 'option' );
		if ( ! empty( $ver ) ) {
			return strtolower( trim( (string) $ver ) );
		}
	}

	$opt = get_option( 'options_recaptcha_version' );
	return ! empty( $opt ) ? strtolower( trim( (string) $opt ) ) : 'v3';
}

/**
 * Retrieve the minimum threshold score for reCAPTCHA v3.
 *
 * @return float Score between 0.0 and 1.0 (default 0.5)
 */
function ltdh_get_recaptcha_min_score(): float {
	if ( defined( 'LTDH_RECAPTCHA_MIN_SCORE' ) && is_numeric( LTDH_RECAPTCHA_MIN_SCORE ) ) {
		return (float) LTDH_RECAPTCHA_MIN_SCORE;
	}

	if ( function_exists( 'get_field' ) ) {
		$score = get_field( 'recaptcha_min_score', 'option' );
		if ( is_numeric( $score ) ) {
			return (float) $score;
		}
	}

	$opt = get_option( 'options_recaptcha_min_score' );
	return is_numeric( $opt ) ? (float) $opt : 0.5;
}

/**
 * Check if Google reCAPTCHA is active and properly configured.
 *
 * @return bool True if both site_key and secret_key are present and enabled.
 */
function ltdh_is_recaptcha_enabled(): bool {
	$site_key   = ltdh_get_recaptcha_site_key();
	$secret_key = ltdh_get_recaptcha_secret_key();

	// Without both keys, reCAPTCHA cannot operate.
	if ( empty( $site_key ) || empty( $secret_key ) ) {
		return false;
	}

	// Constant explicitly defines state
	if ( defined( 'LTDH_RECAPTCHA_ENABLED' ) && null !== LTDH_RECAPTCHA_ENABLED ) {
		return (bool) LTDH_RECAPTCHA_ENABLED;
	}

	if ( function_exists( 'get_field' ) ) {
		$enabled = get_field( 'recaptcha_enabled', 'option' );
		if ( null !== $enabled && '' !== $enabled ) {
			return (bool) $enabled;
		}
	}

	$opt = get_option( 'options_recaptcha_enabled' );
	if ( false !== $opt && '' !== $opt ) {
		return (bool) $opt;
	}

	// If keys are explicitly configured but toggle not set in DB, default to enabled
	return true;
}

/**
 * Server-side verification of Google reCAPTCHA token.
 *
 * @param string $token           Token sent by the frontend client (g-recaptcha-response).
 * @param string $expected_action Optional expected action name for reCAPTCHA v3.
 * @return bool True if verification succeeds (or if reCAPTCHA is disabled). False otherwise.
 */
function ltdh_verify_recaptcha( string $token = '', string $expected_action = '' ): bool {
	// If reCAPTCHA is not configured or disabled, pass-through gracefully.
	if ( ! ltdh_is_recaptcha_enabled() ) {
		return true;
	}

	$token = trim( $token );
	if ( empty( $token ) ) {
		return false;
	}

	$secret_key = ltdh_get_recaptcha_secret_key();
	$remote_ip  = $_SERVER['REMOTE_ADDR'] ?? '';

	$verify_args = [
		'secret'   => $secret_key,
		'response' => $token,
	];
	if ( ! empty( $remote_ip ) && $remote_ip !== '127.0.0.1' && $remote_ip !== '::1' ) {
		$verify_args['remoteip'] = $remote_ip;
	}

	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
		'body'    => $verify_args,
		'timeout' => 10,
	] );

	if ( is_wp_error( $response ) ) {
		error_log( 'ltdh_verify_recaptcha error: ' . $response->get_error_message() );
		return false;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		error_log( 'ltdh_verify_recaptcha HTTP status error: ' . $code );
		return false;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || empty( $body['success'] ) ) {
		return false;
	}

	$version = ltdh_get_recaptcha_version();
	if ( 'v3' === $version ) {
		$score     = isset( $body['score'] ) ? (float) $body['score'] : 0.0;
		$min_score = ltdh_get_recaptcha_min_score();

		if ( $score < $min_score ) {
			error_log( sprintf( 'reCAPTCHA v3 blocked spam: score %.2f < threshold %.2f', $score, $min_score ) );
			return false;
		}

		if ( ! empty( $expected_action ) && ! empty( $body['action'] ) ) {
			if ( $body['action'] !== $expected_action ) {
				error_log( sprintf( 'reCAPTCHA v3 action mismatch: expected %s, got %s', $expected_action, $body['action'] ) );
				// We log action mismatch but allow if score is solid unless strict enforcement
			}
		}
	}

	return true;
}

/**
 * Output HTML markup for reCAPTCHA inside forms.
 *
 * For v3: renders a hidden input and privacy compliance notice.
 * For v2: renders the explicit container for the checkbox widget.
 *
 * @param string $action Optional action name context.
 */
function ltdh_render_recaptcha_field( string $action = 'submit' ): void {
	if ( ! ltdh_is_recaptcha_enabled() ) {
		return;
	}

	$version  = ltdh_get_recaptcha_version();
	$site_key = ltdh_get_recaptcha_site_key();

	if ( 'v2' === $version ) {
		echo '<div class="ltdh-recaptcha-v2-wrapper my-3 flex justify-center">';
		echo '<div class="ltdh-recaptcha-v2" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
		echo '</div>';
	} else {
		echo '<input type="hidden" name="g-recaptcha-response" class="ltdh-recaptcha-token" value="">';
		echo '<p class="text-[11px] text-slate-400 text-center mt-1.5 leading-normal">';
		echo esc_html__( 'Trang được bảo vệ chống spam bởi Google reCAPTCHA. ', 'lienthongdaihoc' );
		echo '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener nofollow" class="underline hover:text-slate-600">' . esc_html__( 'Bảo mật', 'lienthongdaihoc' ) . '</a> &bull; ';
		echo '<a href="https://policies.google.com/terms" target="_blank" rel="noopener nofollow" class="underline hover:text-slate-600">' . esc_html__( 'Điều khoản', 'lienthongdaihoc' ) . '</a>';
		echo '</p>';
	}
}

/**
 * Enqueue reCAPTCHA script assets on the frontend.
 */
add_action( 'wp_enqueue_scripts', 'ltdh_recaptcha_enqueue_scripts', 15 );
function ltdh_recaptcha_enqueue_scripts(): void {
	if ( is_admin() ) {
		return;
	}

	$enabled  = ltdh_is_recaptcha_enabled();
	$version  = ltdh_get_recaptcha_version();
	$site_key = ltdh_get_recaptcha_site_key();

	$script_path = get_template_directory() . '/assets/js/recaptcha.js';
	$script_ver  = file_exists( $script_path ) ? filemtime( $script_path ) : LTDH_VERSION;

	// Always enqueue the lightweight theme bridge script.
	// Google's external API script is lazily injected upon first user interaction
	// (touch, scroll, click, form focus) or on demand by ltdhGetRecaptchaToken().
	// This prevents ~1,000ms CPU blocking during initial page load, boosting CWV.
	wp_enqueue_script(
		'ltdh-recaptcha-js',
		get_template_directory_uri() . '/assets/js/recaptcha.js',
		[],
		$script_ver,
		true
	);

	wp_localize_script( 'ltdh-recaptcha-js', 'ltdhRecaptchaConfig', [
		'enabled' => $enabled,
		'siteKey' => $site_key,
		'version' => $version,
	] );
}

/**
 * Filter script_loader_tag to guarantee defer & async attributes for reCAPTCHA scripts.
 *
 * @param string $tag    The script tag HTML.
 * @param string $handle The registered script handle.
 * @return string
 */
add_filter( 'script_loader_tag', 'ltdh_recaptcha_script_loader_tag', 15, 2 );
function ltdh_recaptcha_script_loader_tag( string $tag, string $handle ): string {
	if ( is_admin() ) {
		return $tag;
	}

	// Only modify external scripts with a src attribute
	if ( false === strpos( $tag, 'src=' ) && false === strpos( $tag, 'src =' ) ) {
		return $tag;
	}

	if ( 'google-recaptcha-api' === $handle ) {
		$tag = preg_replace_callback( '/<script\b(?=[^>]*\ssrc=)([^>]*)>/i', function( $matches ) {
			if ( preg_match( '/\s+defer(?=[\s>"\']|$)/i', $matches[1] ) && preg_match( '/\s+async(?=[\s>"\']|$)/i', $matches[1] ) ) {
				return $matches[0];
			}
			$attrs = preg_replace( '/\s+(defer|async|fetchpriority="[^"]*")(?=[\s>"\']|$)/i', '', $matches[1] );
			return '<script async defer fetchpriority="low"' . $attrs . '>';
		}, $tag );
	} elseif ( 'ltdh-recaptcha-js' === $handle ) {
		$tag = preg_replace_callback( '/<script\b(?=[^>]*\ssrc=)([^>]*)>/i', function( $matches ) {
			if ( ! preg_match( '/\s+defer(?=[\s>"\']|$)/i', $matches[1] ) ) {
				return '<script defer fetchpriority="low"' . $matches[1] . '>';
			}
			return $matches[0];
		}, $tag );
	}

	return $tag;
}


