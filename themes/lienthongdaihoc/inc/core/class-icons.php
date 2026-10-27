<?php
/**
 * Apple SF Symbols / Cupertino Aesthetic Icon Library & Helpers
 *
 * Provides standardized, geometrically aligned SVG icons styled according to
 * Apple Human Interface Guidelines (SF Symbols).
 *
 * @package LienThongDaiHoc
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Icons {

	/**
	 * Registry of SF Symbols SVGs.
	 * Format: 'icon-name' => [ 'type' => 'stroke|fill', 'paths' => '...', 'viewBox' => '0 0 24 24' ]
	 *
	 * @var array<string, array{type: string, paths: string, viewBox?: string, stroke_width?: string}>
	 */
	private static array $symbols = [
		// Apple Share: square.and.arrow.up
		'share' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M12 3v11.25m0-11.25L8.25 6.75M12 3l3.75 3.75" />',
		],
		'square.and.arrow.up' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M12 3v11.25m0-11.25L8.25 6.75M12 3l3.75 3.75" />',
		],

		// Apple Search: magnifyingglass
		'search' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />',
		],
		'magnifyingglass' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />',
		],

		// Apple Filter / Controls: slider.horizontal.3
		'filter' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />',
		],
		'slider.horizontal.3' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />',
		],

		// Apple Phone: phone & phone.fill
		'phone' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />',
		],
		'phone.fill' => [
			'type'  => 'fill',
			'paths' => '<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />',
		],

		// Apple Location: mappin.and.ellipse & location.fill
		'location' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />',
		],
		'mappin' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />',
		],

		// Apple Calendar & Clock
		'calendar' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />',
		],
		'clock' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
		],

		// Apple Graduation Cap: graduationcap.fill & graduationcap
		'graduationcap' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />',
		],
		'academic-cap' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 0 1 .665 6.479A11.952 11.952 0 0 1 12 20.055a11.952 11.952 0 0 1-6.824-2.998 12.078 12.078 0 0 1 .665-6.479L12 14zm-4 6v-7.5" />',
		],

		// Apple Chevrons: 90-degree smooth curve
		'chevron-right' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />',
		],
		'chevron-down' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />',
		],
		'chevron-left' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />',
		],
		'chevron-up' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />',
		],

		// Apple Arrow: arrow.right
		'arrow-right' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />',
		],

		// Apple Close: xmark & xmark.circle.fill
		'xmark' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
		],
		'close' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
		],
		'xmark-circle-fill' => [
			'type'  => 'fill',
			'paths' => '<path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />',
		],

		// Apple Menu: line.3.horizontal
		'menu' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />',
		],

		// Apple Checkmark, Checkmark Circle & Checkmark Seal (Verified)
		'checkmark' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />',
		],
		'checkmark-circle' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
		],
		'checkmark.seal.fill' => [
			'type'  => 'fill',
			'paths' => '<path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />',
		],
		'verified' => [
			'type'  => 'fill',
			'paths' => '<path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />',
		],

		// Apple Star: star.fill & star
		'star-fill' => [
			'type'  => 'fill',
			'paths' => '<path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />',
		],
		'star' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />',
		],

		// Apple Link: link
		'link' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />',
		],

		// Apple Compare: arrow.left.arrow.right
		'compare' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />',
		],

		// Apple Mail: envelope
		'envelope' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />',
		],

		// Apple Tuition / Currency: creditcard
		'creditcard' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-12 3.75h16.5a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-16.5A2.25 2.25 0 0 0 .75 6.75v10.5A2.25 2.25 0 0 0 3 20.25Z" />',
		],
		'banknotes' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v8.25m0 0h.75A.75.75 0 0 1 3.75 15v.75m0-9.75h16.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H3.75m16.5-10.5V4.5a1.5 1.5 0 0 0-1.5-1.5h-13.5a1.5 1.5 0 0 0-1.5 1.5v.75m16.5 0H3.75m6 6a2.25 2.25 0 1 1 4.5 0 2.25 2.25 0 0 1-4.5 0Z" />',
		],

		// Apple Education / Book: book.closed & book-open
		'book-open' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />',
		],

		// Apple Edit / Pencil: pencil
		'pencil' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />',
		],

		// Apple Trash: trash
		'trash' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />',
		],

		// Apple External Link: arrow.up.right
		'arrow-up-right' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />',
		],

		// Apple Download: arrow.down.tray
		'download' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />',
		],

		// Apple Information: info.circle
		'info-circle' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />',
		],

		// Apple Question: questionmark.circle
		'question-mark-circle' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0118 0Zm-9 5.25h.008v.008H12v-.008Z" />',
		],

		// Apple School / University: building.columns
		'school' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />',
		],

		// Apple Like / Thumbs Up: hand.thumbsup
		'thumb-up' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.038a4.501 4.501 0 0 0-1.423-.23H6.633M6.633 10.25v10.5m0-10.5H4.133a2.25 2.25 0 0 0-2.25 2.25v6a2.25 2.25 0 0 0 2.25 2.25h2.5" />',
		],

		// Apple User: person & person.2
		'user' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
		],
		'users' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
		],

		// Apple Shield / Security: shield.check
		'shield-check' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
		],

		// Document: document.text
		'document-text' => [
			'type'  => 'stroke',
			'paths' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
		],
	];

	/**
	 * Get SVG string for a specific SF Symbol name.
	 *
	 * @param string               $name       Symbol name.
	 * @param string               $class      CSS class names.
	 * @param array<string, mixed> $attributes Additional HTML attributes.
	 * @return string Rendered SVG string.
	 */
	public static function get( string $name, string $class = '', array $attributes = [] ): string {
		$name = strtolower( trim( $name ) );

		if ( ! isset( self::$symbols[ $name ] ) ) {
			return '';
		}

		$config       = self::$symbols[ $name ];
		$type         = $config['type'] ?? 'stroke';
		$view_box     = $config['viewBox'] ?? '0 0 24 24';
		$stroke_width = $attributes['stroke-width'] ?? ( $config['stroke_width'] ?? '2' );
		unset( $attributes['stroke-width'] );

		$attr_strings = [];

		// Class attribute
		if ( ! empty( $class ) ) {
			$attr_strings[] = sprintf( 'class="%s"', esc_attr( trim( $class ) ) );
		}

		// Accessibility defaults
		if ( ! isset( $attributes['aria-hidden'] ) && ! isset( $attributes['aria-label'] ) ) {
			$attr_strings[] = 'aria-hidden="true"';
		}

		// Extra attributes
		foreach ( $attributes as $key => $val ) {
			$attr_strings[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( (string) $val ) );
		}

		if ( 'fill' === $type ) {
			return sprintf(
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="%s" fill="currentColor" %s>%s</svg>',
				esc_attr( $view_box ),
				implode( ' ', $attr_strings ),
				$config['paths']
			);
		}

		return sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="%s" fill="none" stroke="currentColor" stroke-width="%s" %s>%s</svg>',
			esc_attr( $view_box ),
			esc_attr( (string) $stroke_width ),
			implode( ' ', $attr_strings ),
			$config['paths']
		);
	}

	/**
	 * Output the SVG directly.
	 *
	 * @param string               $name       Symbol name.
	 * @param string               $class      CSS class names.
	 * @param array<string, mixed> $attributes Additional HTML attributes.
	 */
	public static function render( string $name, string $class = '', array $attributes = [] ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get( $name, $class, $attributes );
	}
}

/**
 * Global helper: get icon SVG.
 */
function ltdh_get_icon( string $name, string $class = '', array $attributes = [] ): string {
	return LTDH_Icons::get( $name, $class, $attributes );
}

/**
 * Global helper: render icon SVG.
 */
function ltdh_icon( string $name, string $class = '', array $attributes = [] ): void {
	LTDH_Icons::render( $name, $class, $attributes );
}
