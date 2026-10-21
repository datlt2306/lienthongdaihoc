<?php
/**
 * Performance Optimization Engine — Google Core Web Vitals (CWV) Standards
 *
 * Implements speed and rendering enhancements:
 * - LCP Image Preload & fetchpriority="high"
 * - JavaScript Deferral & Modern Script Strategy (INP / FCP)
 * - WordPress Core Bloat & Asset Pruning (Emoji, Block Library, Generator)
 * - Image Decoding & Responsive Aspect-Ratio (Zero CLS)
 * - Resource Hints (DNS Prefetch, Preconnect)
 * - Query Caching & Transient Rollups (TTFB)
 * - HTTP Security & Compression Headers
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LTDH_Performance
 */
class LTDH_Performance {

	/**
	 * Initialize all performance hooks.
	 */
	public static function init() {
		// 1. Resource Hints: Preconnect & DNS Prefetch
		add_filter( 'wp_resource_hints', [ __CLASS__, 'resource_hints' ], 10, 2 );

		// 2. Preload LCP Images in <head>
		add_action( 'wp_head', [ __CLASS__, 'preload_lcp_image' ], 1 );

		// 3. Defer Non-Critical JavaScript
		add_filter( 'script_loader_tag', [ __CLASS__, 'defer_scripts' ], 10, 2 );

		// 4. Clean up WordPress Header & Bloat
		add_action( 'init', [ __CLASS__, 'cleanup_wp_bloat' ] );

		// 5. Dequeue Unused Block & Core Styles on Frontend
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'cleanup_frontend_assets' ], 99 );

		// 6. Optimize Image Attributes (decoding="async", lazy loading filters)
		add_filter( 'wp_get_attachment_image_attributes', [ __CLASS__, 'optimize_attachment_image_attributes' ], 10, 3 );

		// 7. Security & HTTP Caching Headers
		add_action( 'send_headers', [ __CLASS__, 'send_performance_headers' ] );

		// 8. Auto-rewrite Attachment Images & URLs to Optimized WebP
		add_filter( 'wp_get_attachment_image_src', [ __CLASS__, 'filter_attachment_image_src' ], 10, 4 );
		add_filter( 'wp_get_attachment_url', [ __CLASS__, 'filter_attachment_url' ], 10, 2 );
		add_filter( 'post_thumbnail_html', [ __CLASS__, 'filter_image_html' ], 10, 5 );

		// 9. VPS Disk Saver Engine — Auto WebP Convert & Core Bloat Pruning
		add_filter( 'wp_handle_upload', [ __CLASS__, 'auto_convert_upload_to_webp' ], 10, 1 );
		add_filter( 'intermediate_image_sizes_advanced', [ __CLASS__, 'prune_unnecessary_image_sizes' ], 10, 1 );
		add_filter( 'big_image_size_threshold', [ __CLASS__, 'cap_max_image_threshold' ], 10, 0 );

		// 10. Intelligent Delay for Analytics & Tag Manager (Saves ~143 KiB unused JS)
		add_filter( 'googlesitekit_tagmanager_tag_blocked', [ __CLASS__, 'block_sitekit_tags' ] );
		add_filter( 'googlesitekit_analytics-4_tag_blocked', [ __CLASS__, 'block_sitekit_tags' ] );
		add_action( 'wp_head', [ __CLASS__, 'render_delayed_analytics_loader' ], 2 );

		// 11. Cloudflare R2 Theme Asset Offloader (Rewrites all static theme assets to R2 CDN)
		add_action( 'template_redirect', [ __CLASS__, 'start_r2_assets_buffer' ], 1 );

		// 11. Async Non-Critical Styles (Eliminates Render-Blocking CSS)
		add_filter( 'style_loader_tag', [ __CLASS__, 'async_non_critical_styles' ], 10, 4 );
	}

	/**
	 * 1. Resource Hints: Preconnect & DNS Prefetch for Google Fonts and external resources.
	 *
	 * @param array  $urls          Array of URLs.
	 * @param string $relation_type Relation type ('preconnect', 'dns-prefetch', etc.).
	 * @return array
	 */
	public static function resource_hints( $urls, $relation_type ) {
		// Preconnect tags are directly declared in header.php for early connection.
		// Avoid duplicate preconnect injection here to ensure total preconnect <= 3.
		if ( 'preconnect' === $relation_type ) {
			$urls = array_filter( $urls, function( $entry ) {
				$href = is_array( $entry ) ? ( $entry['href'] ?? '' ) : $entry;
				return ! in_array( $href, [ 'https://fonts.googleapis.com', 'https://fonts.gstatic.com', 'https://www.google.com' ], true );
			} );
		}

		if ( 'dns-prefetch' === $relation_type ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = 'https://fonts.gstatic.com';
			$urls[] = 'https://api.telegram.org';
			$urls[] = 'https://www.google.com';
			$urls[] = 'https://www.gstatic.com';
		}

		return $urls;
	}

	/**
	 * 2. Preload LCP (Largest Contentful Paint) Image in <head>.
	 *
	 * By preloading the top hero or featured image, the browser discovers and downloads
	 * the largest visual asset immediately alongside the HTML, slashing LCP time.
	 */
	public static function preload_lcp_image() {
		$lcp_image_url = '';

		// Homepage Hero Slide 1
		if ( is_front_page() ) {
			$hero_slides = function_exists( 'get_field' ) ? get_field( 'hero_slides', 'options' ) : [];
			if ( ! empty( $hero_slides[0]['image'] ) ) {
				$lcp_raw = $hero_slides[0]['image'];
			} else {
				$lcp_raw = ltdh_get_fallback_image( 'hero' );
			}

			$lcp_desktop = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $lcp_raw ) : $lcp_raw;
			$lcp_1400    = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $lcp_raw, '1400w' ) : '';
			$lcp_mobile  = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $lcp_raw, 'mobile' ) : '';

			if ( ! empty( $lcp_desktop ) ) {
				$srcset_entries = [];
				if ( ! empty( $lcp_mobile ) && $lcp_mobile !== $lcp_desktop ) {
					$srcset_entries[] = esc_url( $lcp_mobile ) . ' 768w';
				}
				if ( ! empty( $lcp_1400 ) && $lcp_1400 !== $lcp_desktop ) {
					$srcset_entries[] = esc_url( $lcp_1400 ) . ' 1400w';
				}
				$srcset_entries[] = esc_url( $lcp_desktop ) . ' 1942w';
				$srcset_str = implode( ', ', $srcset_entries );

				$fallback_href = ! empty( $lcp_1400 ) ? $lcp_1400 : $lcp_desktop;
				echo '<link rel="preload" as="image" href="' . esc_url( $fallback_href ) . '" imagesrcset="' . $srcset_str . '" imagesizes="(max-width: 768px) 100vw, (max-width: 1440px) 1335px, 1942px" type="image/webp" fetchpriority="high">' . "\n";
			}
			return;
		} elseif ( is_singular( 'school' ) ) {
			$lcp_image_url = get_field( 'school_banner' ) ?: get_the_post_thumbnail_url( get_the_ID(), 'full' );
		}

		if ( ! empty( $lcp_image_url ) ) {
			if ( function_exists( 'ltdh_get_optimized_image_url' ) ) {
				$lcp_image_url = ltdh_get_optimized_image_url( $lcp_image_url );
			}
			$type_attr = ( strpos( $lcp_image_url, '.webp' ) !== false ) ? ' type="image/webp"' : '';
			echo '<link rel="preload" as="image" href="' . esc_url( $lcp_image_url ) . '"' . $type_attr . ' fetchpriority="high">' . "\n";
		}
	}


	/**
	 * 3. Defer Non-Critical JavaScript to eliminate render-blocking resources.
	 *
	 * @param string $tag    The `<script>` tag.
	 * @param string $handle The script handle.
	 * @return string
	 */
	public static function defer_scripts( $tag, $handle ) {
		// Do not modify admin or logged-in customizer
		if ( is_admin() ) {
			return $tag;
		}

		// Only modify external scripts with a src attribute
		if ( false === strpos( $tag, 'src=' ) && false === strpos( $tag, 'src =' ) ) {
			return $tag;
		}

		$defer_handles = [
			'swiper-js',
			'ltdh-theme-js',
			'ltdh-fallback-js',
			'ltdh-compare-js',
			'ltdh-eligibility-js',
			'google-recaptcha-api',
			'ltdh-recaptcha-js',
			'contact-form-7',
			'swv',
		];

		$is_defer = in_array( $handle, $defer_handles, true ) || ( false !== strpos( $handle, 'googlesitekit' ) );

		if ( $is_defer ) {
			if ( 'google-recaptcha-api' === $handle ) {
				// Google reCAPTCHA v3 requires both defer & async for non-blocking execution
				$tag = preg_replace_callback( '/<script\b(?=[^>]*\ssrc=)([^>]*)>/i', function( $matches ) {
					if ( preg_match( '/\s+defer(?=[\s>"\']|$)/i', $matches[1] ) && preg_match( '/\s+async(?=[\s>"\']|$)/i', $matches[1] ) ) {
						return $matches[0];
					}
					$attrs = preg_replace( '/\s+(defer|async|fetchpriority="[^"]*")(?=[\s>"\']|$)/i', '', $matches[1] );
					return '<script async defer fetchpriority="low"' . $attrs . '>';
				}, $tag );
			} else {
				$tag = preg_replace_callback( '/<script\b(?=[^>]*\ssrc=)([^>]*)>/i', function( $matches ) {
					if ( ! preg_match( '/\s+defer(?=[\s>"\']|$)/i', $matches[1] ) ) {
						return '<script defer fetchpriority="low"' . $matches[1] . '>';
					}
					return $matches[0];
				}, $tag );
			}
		}

		return $tag;
	}

	/**
	 * 4. Clean up WordPress core bloat from <head>.
	 *
	 * Strips WordPress Emojis, Windows Live Writer, RSD, generator tags, and shortlinks.
	 */
	public static function cleanup_wp_bloat() {
		// Remove Emoji scripts & styles
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', [ __CLASS__, 'disable_emojis_tinymce' ] );
		add_filter( 'wp_resource_hints', [ __CLASS__, 'disable_emojis_dns_prefetch' ], 10, 2 );

		// Remove RSD, WLW Manifest, Shortlink, and WP Version
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

		// Remove pingback header
		add_filter( 'pings_open', '__return_false', 20 );
	}

	/**
	 * Disable emojis in TinyMCE editor.
	 */
	public static function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, [ 'wpemoji' ] );
		}
		return [];
	}

	/**
	 * Remove s.w.org DNS prefetch for emojis.
	 */
	public static function disable_emojis_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$urls = array_filter( $urls, function( $url ) {
				return strpos( $url, 'https://s.w.org/images/core/emoji' ) === false;
			} );
		}
		return $urls;
	}

	/**
	 * 5. Dequeue Unused Block & Core Styles on Frontend.
	 *
	 * Since the theme is built using custom Tailwind CSS, default block-library styles
	 * and Dashicons add unnecessary render-blocking payload for non-admin visitors.
	 */
	public static function cleanup_frontend_assets() {
		if ( is_admin() ) {
			return;
		}

		// Disable Dashicons on frontend for non-logged-in users
		if ( ! is_user_logged_in() ) {
			wp_deregister_style( 'dashicons' );
		}

		// Remove classic theme styles and global styles inline SVG filters if not using FSE
		wp_dequeue_style( 'classic-theme-styles' );

		// Dequeue Contact Form 7 CSS and scripts on front page only if CF7 consultation form is not active
		$has_front_cf7 = function_exists( 'ltdh_get_form_shortcode' ) ? ! empty( ltdh_get_form_shortcode( 'consultation' ) ) : false;
		if ( is_front_page() && ! $has_front_cf7 ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'contact-form-7' );
			wp_dequeue_style( 'contact-form-7-rtl' );
			wp_dequeue_script( 'contact-form-7' );
			wp_dequeue_script( 'swv' );
			wp_dequeue_script( 'googlesitekit-events-provider-contact-form-7' );
		}
	}

	/**
	 * 6. Optimize Image Attributes (decoding="async", responsive dimensions).
	 *
	 * @param array        $attr       Array of image attributes.
	 * @param WP_Post      $attachment Attachment post object.
	 * @param string|array $size       Requested size.
	 * @return array
	 */
	public static function optimize_attachment_image_attributes( $attr, $attachment, $size ) {
		if ( ! isset( $attr['decoding'] ) ) {
			$attr['decoding'] = 'async';
		}
		return $attr;
	}

	/**
	 * 7. Security and Performance HTTP Response Headers.
	 */
	public static function send_performance_headers() {
		if ( headers_sent() ) {
			return;
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'X-XSS-Protection: 1; mode=block' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );

		if ( is_ssl() || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
		}
	}

	/**
	 * 8. Automatically rewrite image attachment src to WebP if an optimized version exists.
	 */
	public static function filter_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		if ( ! empty( $image[0] ) && function_exists( 'ltdh_get_optimized_image_url' ) ) {
			$image[0] = ltdh_get_optimized_image_url( $image[0] );
		}
		return $image;
	}

	/**
	 * Automatically rewrite attachment URL to WebP if an optimized version exists.
	 */
	public static function filter_attachment_url( $url, $attachment_id ) {
		if ( ! empty( $url ) && function_exists( 'ltdh_get_optimized_image_url' ) ) {
			return ltdh_get_optimized_image_url( $url );
		}
		return $url;
	}

	/**
	 * Rewrite any huge image URLs in rendered HTML to WebP.
	 */
	public static function filter_image_html( $html ) {
		if ( empty( $html ) || ! function_exists( 'ltdh_get_optimized_image_url' ) ) {
			return $html;
		}
		return preg_replace_callback( '/(src|srcset)=["\']([^"\']+)["\']/i', function( $matches ) {
			$attr = $matches[1];
			$val = $matches[2];
			if ( strpos( $val, '.png' ) !== false || strpos( $val, '.jpg' ) !== false || strpos( $val, '.jpeg' ) !== false ) {
				$val = ltdh_get_optimized_image_url( $val );
			}
			return $attr . '="' . $val . '"';
		}, $html );
	}

	/**
	 * 9. Auto-convert uploaded PNG/JPG files to WebP on upload and delete heavy originals.
	 *
	 * Saves ~85% disk space on VPS for every new upload without third-party services.
	 *
	 * @param array $upload Array of upload data (file, url, type).
	 * @return array
	 */
	public static function auto_convert_upload_to_webp( $upload ) {
		if ( ! isset( $upload['file'] ) || ! file_exists( $upload['file'] ) ) {
			return $upload;
		}

		$file_path = $upload['file'];
		$type      = $upload['type'] ?? '';

		// Only process JPG and PNG images
		if ( ! in_array( $type, [ 'image/jpeg', 'image/jpg', 'image/png' ], true ) ) {
			return $upload;
		}

		// Ensure WP Image Editor API is loaded
		if ( ! function_exists( 'wp_get_image_editor' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$editor = wp_get_image_editor( $file_path );
		if ( is_wp_error( $editor ) ) {
			return $upload;
		}

		$path_info = pathinfo( $file_path );
		$directory = $path_info['dirname'];
		$filename  = $path_info['filename'];
		$webp_file = $directory . '/' . $filename . '.webp';

		// Set high-efficiency quality (82% quality yields ~85% size savings with zero visual loss)
		$editor->set_quality( 82 );
		$saved = $editor->save( $webp_file, 'image/webp' );

		if ( ! is_wp_error( $saved ) && isset( $saved['path'] ) && file_exists( $saved['path'] ) ) {
			// Remove the heavy original PNG/JPG file from VPS disk
			@unlink( $file_path );

			$upload['file'] = $saved['path'];
			$upload['url']  = str_replace( '.' . $path_info['extension'], '.webp', $upload['url'] );
			$upload['type'] = 'image/webp';
		}

		return $upload;
	}

	/**
	 * Remove redundant intermediate thumbnail sizes generated by WP Core.
	 * Saves significant VPS disk space per upload.
	 *
	 * @param array $sizes Array of intermediate image sizes.
	 * @return array
	 */
	public static function prune_unnecessary_image_sizes( $sizes ) {
		unset( $sizes['medium_large'] );
		unset( $sizes['1536x1536'] );
		unset( $sizes['2048x2048'] );
		return $sizes;
	}

	/**
	 * Cap maximum dimension threshold for uploaded raw images to 1920px width/height.
	 * Prevents 4K/8K photos from wasting VPS storage.
	 *
	 * @return int
	 */
	public static function cap_max_image_threshold() {
		return 1920;
	}

	/**
	 * 10. Intelligent Non-blocking Google Analytics & Tag Manager Loader
	 *
	 * Eliminates render-blocking & unused JS flags (~143 KiB saved) by deferring
	 * gtm.js and gtag.js downloads until first user interaction or idle timeout,
	 * while preserving immediate dataLayer queueing so no analytics events are lost.
	 *
	 * @param bool $blocked Whether or not tag output is suppressed.
	 * @return bool
	 */
	public static function block_sitekit_tags( $blocked ) {
		if ( is_admin() ) {
			return $blocked;
		}
		return true;
	}

	/**
	 * Render non-blocking delayed GTM and GA4 scripts in wp_head.
	 */
	public static function render_delayed_analytics_loader() {
		if ( is_admin() ) {
			return;
		}

		$gtm_settings = get_option( 'googlesitekit_tagmanager_settings', [] );
		$ga4_settings = get_option( 'googlesitekit_analytics-4_settings', [] );

		$gtm_id = ( ! empty( $gtm_settings['useSnippet'] ) && ! empty( $gtm_settings['containerID'] ) ) ? $gtm_settings['containerID'] : '';

		// Respect GA4 trackingDisabled for logged-in users if configured
		$disabled_users = $ga4_settings['trackingDisabled'] ?? [];
		if ( is_user_logged_in() && in_array( 'loggedinUsers', (array) $disabled_users, true ) ) {
			$ga4_id = '';
		} else {
			$ga4_id = ( ! empty( $ga4_settings['useSnippet'] ) && ! empty( $ga4_settings['googleTagID'] ) ) ? $ga4_settings['googleTagID'] : ( $ga4_settings['measurementID'] ?? '' );
		}

		// If neither GTM nor GA4 is active, nothing to load
		if ( empty( $gtm_id ) && empty( $ga4_id ) ) {
			return;
		}
		?>
		<!-- LTDH Intelligent Non-blocking Analytics Loader -->
		<script id="ltdh-delayed-analytics">
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		<?php if ( ! empty( $ga4_id ) ) : ?>
		gtag('config', '<?php echo esc_js( $ga4_id ); ?>');
		<?php endif; ?>
		(function() {
			var loaded = false;
			function loadAnalytics() {
				if (loaded) return;
				loaded = true;
				['scroll', 'touchstart', 'mousemove', 'keydown', 'click'].forEach(function(ev) {
					window.removeEventListener(ev, loadAnalytics, { passive: true });
				});
				<?php if ( ! empty( $gtm_id ) ) : ?>
				var sGtm = document.createElement('script');
				sGtm.async = true;
				sGtm.src = 'https://www.googletagmanager.com/gtm.js?id=<?php echo esc_js( $gtm_id ); ?>';
				document.head.appendChild(sGtm);
				<?php endif; ?>
				<?php if ( ! empty( $ga4_id ) ) : ?>
				var sGa = document.createElement('script');
				sGa.async = true;
				sGa.src = 'https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $ga4_id ); ?>';
				document.head.appendChild(sGa);
				<?php endif; ?>
			}
			['scroll', 'touchstart', 'mousemove', 'keydown', 'click'].forEach(function(ev) {
				window.addEventListener(ev, loadAnalytics, { passive: true, once: true });
			});
			if ('requestIdleCallback' in window) {
				requestIdleCallback(function() {
					setTimeout(loadAnalytics, 3500);
				});
			} else {
				setTimeout(loadAnalytics, 4000);
			}
		})();
		</script>
		<?php
	}

	/**
	 * 11. Load Non-Critical CSS Asynchronously (Zero Render-Blocking)
	 *
	 * Uses rel="preload" and onload swap for secondary stylesheets (e.g. Swiper CSS),
	 * eliminating render-blocking penalty while keeping zero CLS via inline critical CSS.
	 *
	 * @param string $html   The link tag HTML.
	 * @param string $handle The stylesheet handle.
	 * @param string $href   The stylesheet URL.
	 * @param string $media  The stylesheet media attribute.
	 * @return string
	 */
	public static function async_non_critical_styles( $html, $handle, $href, $media ) {
		if ( is_admin() ) {
			return $html;
		}

		if ( 'swiper-css' === $handle || 'ltdh-main-style' === $handle ) {
			return sprintf(
				'<link rel="preload" href="%s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="%s"></noscript>' . "\n",
				esc_url( $href ),
				esc_url( $href )
			);
		}

		return $html;
	}
	/**
	 * 12. Offload theme static images to Cloudflare R2 CDN globally.
	 */
	public static function start_r2_assets_buffer() {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		ob_start( function( $html ) {
			if ( empty( $html ) || ! is_string( $html ) ) {
				return $html;
			}
			$r2_cdn_base = 'https://pub-f075baadfe3743948b6fe0a4828c9880.r2.dev/assets/images/';
			return preg_replace(
				'#https?://[^/]+/wp-content/themes/lienthongdaihoc/assets/images/([^"\'\)\s]+)#i',
				$r2_cdn_base . '$1',
				$html
			);
		} );
	}
}

LTDH_Performance::init();
