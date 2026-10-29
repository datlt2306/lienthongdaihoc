<?php
/**
 * Referral Tracking & Partner Attribution System
 *
 * Handles inbound referral codes (?ref=... / ?partner=...),
 * persists attribution via 30-day cookie & localStorage,
 * and auto-injects attribution into all lead submission forms.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'LTDH_REF_COOKIE' ) ) {
	define( 'LTDH_REF_COOKIE', 'ltdh_ref' );
}

if ( ! defined( 'LTDH_REF_COOKIE_EXPIRY' ) ) {
	define( 'LTDH_REF_COOKIE_EXPIRY', 30 * DAY_IN_SECONDS ); // 30 days
}

/**
 * Sanitize referral code.
 * Allows alphanumeric, hyphens, and underscores, up to 50 chars.
 *
 * @param string $raw_ref
 * @return string
 */
function ltdh_sanitize_referral_code( string $raw_ref ): string {
	$clean = preg_replace( '/[^a-zA-Z0-9_\-]/', '', trim( $raw_ref ) );
	return substr( $clean, 0, 50 );
}

/**
 * Get active referral code for current request or visitor.
 * Hierarchy:
 * 1. Explicit POST parameter ('ref', 'partner', 'sales_partner')
 * 2. Current GET parameter ('ref', 'partner')
 * 3. Cookie ('ltdh_ref')
 *
 * @return string
 */
function ltdh_get_active_referral(): string {
	// 1. Check POST
	if ( ! empty( $_POST['ref'] ) ) {
		return ltdh_sanitize_referral_code( wp_unslash( $_POST['ref'] ) );
	}
	if ( ! empty( $_POST['partner'] ) ) {
		return ltdh_sanitize_referral_code( wp_unslash( $_POST['partner'] ) );
	}
	if ( ! empty( $_POST['sales_partner'] ) ) {
		return ltdh_sanitize_referral_code( wp_unslash( $_POST['sales_partner'] ) );
	}

	// 2. Check GET
	if ( ! empty( $_GET['ref'] ) ) {
		return ltdh_sanitize_referral_code( wp_unslash( $_GET['ref'] ) );
	}
	if ( ! empty( $_GET['partner'] ) ) {
		return ltdh_sanitize_referral_code( wp_unslash( $_GET['partner'] ) );
	}

	// 3. Check Cookie
	if ( ! empty( $_COOKIE[ LTDH_REF_COOKIE ] ) ) {
		return ltdh_sanitize_referral_code( wp_unslash( $_COOKIE[ LTDH_REF_COOKIE ] ) );
	}

	return '';
}

/**
 * Capture referral parameter on inbound request and set cookie server-side.
 * Hooked to init priority 1 so it runs before any template or output.
 */
add_action( 'init', 'ltdh_capture_inbound_referral', 1 );
function ltdh_capture_inbound_referral() {
	$inbound_ref = '';
	if ( ! empty( $_GET['ref'] ) ) {
		$inbound_ref = ltdh_sanitize_referral_code( wp_unslash( $_GET['ref'] ) );
	} elseif ( ! empty( $_GET['partner'] ) ) {
		$inbound_ref = ltdh_sanitize_referral_code( wp_unslash( $_GET['partner'] ) );
	}

	if ( ! empty( $inbound_ref ) ) {
		// Set cookie
		$cookie_path   = COOKIEPATH ? COOKIEPATH : '/';
		$cookie_domain = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
		$secure        = is_ssl();
		$httponly      = false; // Allow JS to read if needed

		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie( LTDH_REF_COOKIE, $inbound_ref, [
				'expires'  => time() + LTDH_REF_COOKIE_EXPIRY,
				'path'     => $cookie_path,
				'domain'   => $cookie_domain,
				'secure'   => $secure,
				'httponly' => $httponly,
				'samesite' => 'Lax',
			] );
		} else {
			setcookie( LTDH_REF_COOKIE, $inbound_ref, time() + LTDH_REF_COOKIE_EXPIRY, $cookie_path, $cookie_domain, $secure, $httponly );
		}

		// Make available in current request immediately
		$_COOKIE[ LTDH_REF_COOKIE ] = $inbound_ref;
	}
}

/**
 * Output client-side script in wp_head (priority 1).
 * Ensures attribution works even on aggressively cached static HTML pages.
 * Also automatically injects a hidden <input type="hidden" name="ref" value="...">
 * into all submission forms on the site.
 */
add_action( 'wp_head', 'ltdh_render_referral_head_script', 1 );
function ltdh_render_referral_head_script() {
	?>
	<script id="ltdh-referral-tracking">
	(function() {
		try {
			var COOKIE_NAME = '<?php echo esc_js( LTDH_REF_COOKIE ); ?>';
			var EXPIRY_DAYS = 30;

			function getQueryParam(name) {
				var search = window.location.search;
				if (!search) return '';
				var match = new RegExp('[?&]' + name + '=([^&]*)').exec(search);
				return match ? decodeURIComponent(match[1].replace(/\+/g, ' ')) : '';
			}

			function sanitizeRef(val) {
				if (!val) return '';
				return val.replace(/[^a-zA-Z0-9_\-]/g, '').substring(0, 50);
			}

			function getCookie(name) {
				var nameEQ = name + "=";
				var ca = document.cookie.split(';');
				for (var i = 0; i < ca.length; i++) {
					var c = ca[i];
					while (c.charAt(0) === ' ') c = c.substring(1, c.length);
					if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
				}
				return '';
			}

			function setCookie(name, val, days) {
				var d = new Date();
				d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
				document.cookie = name + "=" + encodeURIComponent(val) + ";expires=" + d.toUTCString() + ";path=/;SameSite=Lax";
			}

			// Inbound check from URL
			var rawRef = getQueryParam('ref') || getQueryParam('partner');
			var cleanRef = sanitizeRef(rawRef);

			if (cleanRef) {
				setCookie(COOKIE_NAME, cleanRef, EXPIRY_DAYS);
				try { localStorage.setItem(COOKIE_NAME, cleanRef); } catch (e) {}
			}

			// Helper to get active referral code
			window.ltdhGetActiveRef = function() {
				var current = sanitizeRef(getQueryParam('ref') || getQueryParam('partner'));
				if (current) return current;
				var fromCookie = sanitizeRef(getCookie(COOKIE_NAME));
				if (fromCookie) return fromCookie;
				try {
					var fromStorage = sanitizeRef(localStorage.getItem(COOKIE_NAME));
					if (fromStorage) return fromStorage;
				} catch (e) {}
				return '';
			};

			function injectRefInputs() {
				var activeRef = window.ltdhGetActiveRef();
				if (!activeRef) return;

				var forms = document.querySelectorAll('form');
				for (var i = 0; i < forms.length; i++) {
					var form = forms[i];
					if (form.getAttribute('role') === 'search' || form.classList.contains('search-form')) {
						continue;
					}
					var input = form.querySelector('input[name="ref"]');
					if (!input) {
						input = document.createElement('input');
						input.type = 'hidden';
						input.name = 'ref';
						form.appendChild(input);
					}
					input.value = activeRef;
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', injectRefInputs);
			} else {
				injectRefInputs();
			}

			// Auto inject on dynamic DOM insertion or form submits
			document.addEventListener('submit', function(e) {
				var form = e.target;
				if (form && form.tagName === 'FORM') {
					if (form.getAttribute('role') === 'search' || form.classList.contains('search-form')) {
						return;
					}
					var activeRef = window.ltdhGetActiveRef();
					if (activeRef) {
						var input = form.querySelector('input[name="ref"]');
						if (!input) {
							input = document.createElement('input');
							input.type = 'hidden';
							input.name = 'ref';
							form.appendChild(input);
						}
						input.value = activeRef;
					}
				}
			}, true);

			// Contact Form 7 support
			document.addEventListener('wpcf7beforesubmit', function(e) {
				var activeRef = window.ltdhGetActiveRef();
				if (activeRef && e.detail && e.detail.formData) {
					e.detail.formData.set('ref', activeRef);
				}
			});
		} catch (err) {
			console.warn('[LTDH Ref Tracking] Init error', err);
		}
	})();
	</script>
	<?php
}

/**
 * Helper to render hidden referral input into forms manually if needed.
 */
function ltdh_render_referral_hidden_input(): void {
	$ref = ltdh_get_active_referral();
	if ( ! empty( $ref ) ) {
		echo '<input type="hidden" name="ref" value="' . esc_attr( $ref ) . '">';
	}
}
