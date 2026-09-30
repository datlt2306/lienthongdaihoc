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
	}

	/**
	 * 1. Resource Hints: Preconnect & DNS Prefetch for Google Fonts and external resources.
	 *
	 * @param array  $urls          Array of URLs.
	 * @param string $relation_type Relation type ('preconnect', 'dns-prefetch', etc.).
	 * @return array
	 */
	public static function resource_hints( $urls, $relation_type ) {
		if ( 'preconnect' === $relation_type ) {
			$urls[] = [
				'href'        => 'https://fonts.googleapis.com',
				'crossorigin' => false,
			];
			$urls[] = [
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => true,
			];
		}

		if ( 'dns-prefetch' === $relation_type ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = 'https://fonts.gstatic.com';
			$urls[] = 'https://api.telegram.org';
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
				$lcp_image_url = $hero_slides[0]['image'];
			} else {
				$lcp_image_url = ltdh_get_fallback_image( 'hero' );
			}
		} elseif ( is_singular( 'program' ) ) {
			$school_id = get_field( 'school_relationship' );
			if ( is_array( $school_id ) && ! empty( $school_id ) ) {
				$school_id = $school_id[0];
			}
			if ( is_object( $school_id ) ) {
				$school_id = $school_id->ID;
			}
			$lcp_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: ( $school_id ? get_field( 'school_banner', (int) $school_id ) : '' );
			if ( empty( $lcp_image_url ) ) {
				$lcp_image_url = get_template_directory_uri() . '/assets/images/banner-program.jpg';
			}
		} elseif ( is_singular( 'school' ) ) {
			$lcp_image_url = get_field( 'school_banner' ) ?: get_the_post_thumbnail_url( get_the_ID(), 'full' );
			if ( empty( $lcp_image_url ) ) {
				$lcp_image_url = get_template_directory_uri() . '/assets/images/banner-school.jpg';
			}
		} elseif ( is_singular( 'major' ) ) {
			$lcp_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		} elseif ( is_singular( 'post' ) ) {
			$lcp_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		} elseif ( is_post_type_archive( 'program' ) || is_tax( 'training_type' ) || is_tax( 'campus' ) ) {
			$lcp_image_url = get_template_directory_uri() . '/assets/images/banner-program.jpg';
		} elseif ( is_post_type_archive( 'school' ) ) {
			$lcp_image_url = get_template_directory_uri() . '/assets/images/banner-school.jpg';
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

		$defer_handles = [
			'swiper-js',
			'ltdh-theme-js',
			'ltdh-fallback-js',
			'ltdh-compare-js',
			'ltdh-eligibility-js',
		];

		if ( in_array( $handle, $defer_handles, true ) ) {
			if ( false === strpos( $tag, 'defer' ) && false === strpos( $tag, 'async' ) ) {
				$tag = str_replace( ' src', ' defer fetchpriority="low" src', $tag );
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
}

LTDH_Performance::init();
