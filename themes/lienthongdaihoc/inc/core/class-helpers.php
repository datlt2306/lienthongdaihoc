<?php

/**
 * Helper Functions — Logo, breadcrumb, thumbnails, contact info, form shortcodes.
 *
 * All functions prefixed with ltdh_ are globally available.
 * Every function that previously hardcoded fallbacks now uses ltdh_get_defaults().
 *
 * @package lienthongdaihoc
 */

if (! defined('ABSPATH')) {
	exit;
}

// ----------------------------------------------------
// 1. Contact Information Helpers
// ----------------------------------------------------

/**
 * Get the global hotline number.
 */
function ltdh_get_hotline(): string {
	$hotline = '';
	if (function_exists('get_field')) {
		$hotline = get_field('global_hotline', 'options');
	}
	return $hotline ?: ltdh_default('contact', 'hotline');
}

/**
 * Get the global Zalo URL.
 */
function ltdh_get_zalo_url(): string {
	$url = '';
	if (function_exists('get_field')) {
		$url = get_field('global_zalo_url', 'options');
	}
	return $url ?: ltdh_default('contact', 'zalo_url');
}

/**
 * Get the global Messenger URL.
 */
function ltdh_get_messenger_url(): string {
	$url = '';
	if (function_exists('get_field')) {
		$url = get_field('global_messenger_url', 'options');
	}
	return $url ?: ltdh_default('contact', 'messenger_url');
}

/**
 * Get the global contact email.
 */
function ltdh_get_email(): string {
	return ltdh_default('contact', 'email');
}

/**
 * Get the global address.
 */
function ltdh_get_address(): string {
	return ltdh_default('contact', 'address');
}

/**
 * Get the company name.
 */
function ltdh_get_company_name(): string {
	return ltdh_default('contact', 'company_name');
}

/**
 * Get a program's effective hotline: program override → school → global.
 */
function ltdh_get_program_hotline(int $program_id): string {
	$override = '';
	if (function_exists('get_field')) {
		$override = get_field('hotline_override', $program_id);
	}
	if (! empty($override)) {
		return $override;
	}

	$school_id = 0;
	if (function_exists('get_field')) {
		$school_id = intval(get_field(LTDH_META_SCHOOL_REL, $program_id) ?: 0);
	}
	if ($school_id) {
		$school_hotline = '';
		if (function_exists('get_field')) {
			$school_hotline = get_field('hotline', $school_id);
		}
		if (! empty($school_hotline)) {
			return $school_hotline;
		}
	}

	return ltdh_get_hotline();
}

/**
 * Get a school's effective hotline: school → global.
 */
function ltdh_get_school_hotline(int $school_id): string {
	$school_hotline = '';
	if (function_exists('get_field')) {
		$school_hotline = get_field('hotline', $school_id);
	}
	return $school_hotline ?: ltdh_get_hotline();
}

// ----------------------------------------------------
// 2. Form Shortcode Helper
// ----------------------------------------------------

/**
 * Get the CF7 shortcode for a given context.
 *
 * Contexts: 'consultation', 'contact', 'program'.
 * Falls back to the default form if the specific one is not configured.
 *
 * @param string $context
 * @return string Raw shortcode string, or empty if no form configured.
 */
function ltdh_get_form_shortcode(string $context = 'consultation'): string {
	if (! function_exists('get_field')) {
		return '';
	}

	$option_map = [
		'consultation' => 'cf7_consultation_form_id',
		'contact'      => 'cf7_contact_form_id',
		'program'      => 'cf7_program_form_id',
	];

	$field_name = $option_map[$context] ?? '';
	if ($field_name) {
		$form_id = get_field($field_name, 'options');
		if (! empty($form_id)) {
			return do_shortcode('[contact-form-7 id="' . esc_attr($form_id) . '"]');
		}
	}

	$default_id = get_field('cf7_default_form_id', 'options');
	if (! empty($default_id)) {
		return do_shortcode('[contact-form-7 id="' . esc_attr($default_id) . '"]');
	}

	return '';
}

/**
 * Render a consultation form with automatic fallback to native HTML form.
 *
 * @param array $context_hidden_fields Optional hidden fields to inject.
 */
function ltdh_render_consultation_form(array $context_hidden_fields = []): void {
	$shortcode = ltdh_get_form_shortcode('consultation');
	if (! empty($shortcode)) {
		echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}
	ltdh_render_native_form('consultation', $context_hidden_fields);
}

/**
 * Render a contact form with automatic fallback.
 */
function ltdh_render_contact_form(): void {
	$shortcode = ltdh_get_form_shortcode('contact');
	if (! empty($shortcode)) {
		echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}
	ltdh_render_native_form('contact');
}

/**
 * Render a native HTML form fallback.
 *
 * @param string $type 'consultation' or 'contact'.
 * @param array  $hidden_fields
 */
function ltdh_render_native_form(string $type = 'consultation', array $hidden_fields = []): void {
?>
	<form action="" method="POST" class="space-y-4">
		<?php wp_nonce_field( 'ltdh_native_lead_submit_action', 'ltdh_native_lead_nonce' ); ?>
		<?php if ( isset( $_GET['submit_success'] ) && '1' === $_GET['submit_success'] ) : ?>
			<div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
				<span class="font-bold">Gửi thông tin thành công!</span> Đội ngũ tư vấn tuyển sinh sẽ liên hệ với bạn trong thời gian sớm nhất.
			</div>
		<?php endif; ?>

		<?php foreach ($hidden_fields as $name => $value) : ?>
			<input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
		<?php endforeach; ?>

		<div class="hidden" style="display:none !important;" aria-hidden="true">
			<label for="hp_website">Website URL</label>
			<input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off" value="">
		</div>

		<div>
			<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Họ và tên *'); ?></label>
			<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Họ và tên của bạn'); ?>">
		</div>
		<div>
			<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Số điện thoại *'); ?></label>
			<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Số điện thoại liên hệ'); ?>">
		</div>
		<div>
			<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Email (Tùy chọn)'); ?></label>
			<input type="email" name="your-email" class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Địa chỉ email'); ?>">
		</div>
		<div>
			<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Nội dung cần tư vấn'); ?></label>
			<textarea name="your-message" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Nhập câu hỏi hoặc yêu cầu cụ thể...'); ?>"></textarea>
		</div>

		<button type="submit" class="w-full bg-brand-primary text-white py-3.5 rounded-lg text-sm font-bold shadow-md shadow-brand-primary/10 hover:bg-brand-darkBlue transition-all mt-2 min-h-[44px] flex items-center justify-center">
			<?php echo 'contact' === $type ? esc_html__('Gửi Liên Hệ') : esc_html__('Gửi Thông Tin Ngay'); ?>
		</button>
	</form>
	<?php
}

// ----------------------------------------------------
// 3. Logo Helpers
// ----------------------------------------------------

/**
 * Automatically resolve optimized image URL (e.g. serving optimized WebP replacements from assets/images/).
 *
 * @param string $url       Original image URL.
 * @param bool   $is_mobile Whether mobile variant is requested.
 * @return string
 */
function ltdh_get_optimized_image_url( string $url, bool $is_mobile = false ): string {
	if ( empty( $url ) ) {
		return '';
	}

	$parsed_path = parse_url( $url, PHP_URL_PATH );
	if ( empty( $parsed_path ) ) {
		return $url;
	}

	$filename         = basename( $parsed_path );
	$name_without_ext = pathinfo( $filename, PATHINFO_FILENAME );

	// Check if theme has an optimized WebP replacement in assets/images/
	$suffix     = $is_mobile ? '-mobile.webp' : '.webp';
	$theme_file = get_template_directory() . '/assets/images/' . $name_without_ext . $suffix;
	if ( file_exists( $theme_file ) ) {
		return get_template_directory_uri() . '/assets/images/' . $name_without_ext . $suffix;
	}

	// Also check standard webp as fallback for mobile if -mobile.webp doesn't exist
	if ( $is_mobile ) {
		$theme_desktop_file = get_template_directory() . '/assets/images/' . $name_without_ext . '.webp';
		if ( file_exists( $theme_desktop_file ) ) {
			return get_template_directory_uri() . '/assets/images/' . $name_without_ext . '.webp';
		}
	}

	return $url;
}

/**
 * Get site logo data (URL, width, height) with Customizer → ACF fallback.
 */
function ltdh_get_logo_data(): array {
	$data = [ 'url' => '', 'width' => 0, 'height' => 0 ];
	$logo_id = get_theme_mod('custom_logo');
	if ($logo_id) {
		$img_data = wp_get_attachment_image_src($logo_id, 'full');
		if ($img_data && ! empty($img_data[0])) {
			$data = [
				'url'    => $img_data[0],
				'width'  => (int) $img_data[1],
				'height' => (int) $img_data[2],
			];
		}
	}

	if ( empty( $data['url'] ) && function_exists('get_field') ) {
		$acf_logo = get_field('global_logo', 'options');
		if ($acf_logo) {
			if (is_numeric($acf_logo)) {
				$img_data = wp_get_attachment_image_src((int) $acf_logo, 'full');
				if ($img_data && ! empty($img_data[0])) {
					$data = [
						'url'    => $img_data[0],
						'width'  => (int) $img_data[1],
						'height' => (int) $img_data[2],
					];
				}
			} elseif (is_array($acf_logo) && isset($acf_logo['url'])) {
				$data = [
					'url'    => $acf_logo['url'],
					'width'  => (int) ($acf_logo['width'] ?? 200),
					'height' => (int) ($acf_logo['height'] ?? 60),
				];
			} elseif (is_string($acf_logo)) {
				$data = [
					'url'    => $acf_logo,
					'width'  => 200,
					'height' => 60,
				];
			}
		}
	}

	if ( ! empty( $data['url'] ) ) {
		$optimized = ltdh_get_optimized_image_url( $data['url'] );
		if ( ! empty( $optimized ) && $optimized !== $data['url'] ) {
			$data['url'] = $optimized;
		}
	}

	return $data;
}


/**
 * Get site logo URL with Customizer → ACF fallback.
 */
function ltdh_get_logo_url(): string {
	$data = ltdh_get_logo_data();
	return $data['url'] ?? '';
}

/**
 * Output site logo HTML with explicit dimensions & aspect-ratio (Zero CLS).
 */
function ltdh_site_logo(int $max_height = 48): void {
	$logo_data = ltdh_get_logo_data();
	if (! empty($logo_data['url'])) :
		$site_name = get_bloginfo('name');
		$orig_w = $logo_data['width'] ?: 200;
		$orig_h = $logo_data['height'] ?: 60;
		$calc_w = $orig_h > 0 ? round(($orig_w / $orig_h) * $max_height) : $orig_w;
	?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center" aria-label="<?php echo esc_attr($site_name); ?>">
			<img src="<?php echo esc_url($logo_data['url']); ?>"
				alt="<?php echo esc_attr($site_name); ?>"
				width="<?php echo esc_attr($calc_w); ?>"
				height="<?php echo esc_attr($max_height); ?>"
				class="h-auto object-contain"
				loading="eager"
				fetchpriority="high"
				decoding="async"
				style="max-height: <?php echo esc_attr($max_height); ?>px; width: auto; aspect-ratio: <?php echo esc_attr($calc_w); ?> / <?php echo esc_attr($max_height); ?>;">
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
			<div class="flex flex-col leading-none">
				<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
				<span class="text-xl font-extrabold text-brand-primary">ĐẠI HỌC</span>
			</div>
		</a>
	<?php endif;
}

/**
 * Output site logo for mobile with explicit dimensions & aspect-ratio (Zero CLS).
 */
function ltdh_site_logo_mobile(int $max_height = 36): void {
	$logo_data = ltdh_get_logo_data();
	if (! empty($logo_data['url'])) :
		$site_name = get_bloginfo('name');
		$orig_w = $logo_data['width'] ?: 200;
		$orig_h = $logo_data['height'] ?: 60;
		$calc_w = $orig_h > 0 ? round(($orig_w / $orig_h) * $max_height) : $orig_w;
	?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center" aria-label="<?php echo esc_attr($site_name); ?>">
			<img src="<?php echo esc_url($logo_data['url']); ?>"
				alt="<?php echo esc_attr($site_name); ?>"
				width="<?php echo esc_attr($calc_w); ?>"
				height="<?php echo esc_attr($max_height); ?>"
				class="h-auto object-contain"
				loading="eager"
				decoding="async"
				style="max-height: <?php echo esc_attr($max_height); ?>px; width: auto; aspect-ratio: <?php echo esc_attr($calc_w); ?> / <?php echo esc_attr($max_height); ?>;">
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
			<div class="flex flex-col leading-none">
				<span class="text-xs font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
				<span class="text-lg font-extrabold text-brand-primary">ĐẠI HỌC</span>
			</div>
		</a>
	<?php endif;
}

// ----------------------------------------------------
// 4. Breadcrumb
// ----------------------------------------------------

function ltdh_breadcrumb(): void {
	$type = get_query_var( 'ltdh_compare' );
	if ( $type ) {
		echo '<div class="ltdh-breadcrumb bg-slate-50/60 border-b border-slate-100 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none flex items-center gap-1.5">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="hover:text-brand-primary transition-colors font-medium">Trang chủ</a>';
		echo ' <span class="mx-1 text-slate-300 font-light">/</span> ';
		echo '<a href="' . esc_url( home_url( '/hinh-thuc-dao-tao/' ) ) . '" class="hover:text-brand-primary transition-colors font-medium">Hình thức đào tạo</a>';
		echo ' <span class="mx-1 text-slate-300 font-light">/</span> ';
		echo '<span class="text-slate-700 font-semibold">So sánh chương trình</span>';
		echo '</div>';
		return;
	}

	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	$is_he_dao_tao = (bool) preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path );

	$html = '';
	if ( ! $is_he_dao_tao && function_exists( 'rank_math_the_breadcrumbs' ) ) {
		ob_start();
		rank_math_the_breadcrumbs();
		$html = ob_get_clean();
	}

	if ( empty( trim( $html ) ) ) {
		// Custom fallback breadcrumbs
		$crumbs   = [];
		$crumbs[] = [ 'label' => 'Trang chủ', 'url' => home_url( '/' ) ];

		if ( is_singular() ) {
			$post_type = get_post_type();
			if ( $post_type === 'program' ) {
				$crumbs[] = [ 'label' => 'Hình thức đào tạo', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
				$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
			} elseif ( $post_type === 'school' ) {
				$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => get_post_type_archive_link( 'school' ) ?: home_url( '/truong-doi-tac/' ) ];
				$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
			} elseif ( $post_type === 'major' ) {
				$crumbs[] = [ 'label' => 'Chuyên ngành', 'url' => home_url( '/nganh-hoc/' ) ];
				$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
			} elseif ( $post_type === 'post' ) {
				$crumbs[] = [ 'label' => 'Tin tức', 'url' => home_url( '/tin-tuc/' ) ];
				$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
			} else {
				$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
			}
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			if ( is_tax( 'training_type' ) ) {
				$crumbs[] = [ 'label' => 'Hình thức đào tạo', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
			}
			$crumbs[] = [ 'label' => $term->name, 'url' => '' ];
		} elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( $post_type === 'school' ) {
				$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => '' ];
			} elseif ( $post_type === 'major' ) {
				$crumbs[] = [ 'label' => 'Chuyên ngành', 'url' => '' ];
			} else {
				$crumbs[] = [ 'label' => 'Hình thức đào tạo', 'url' => '' ];
			}
		} elseif ( is_home() ) {
			$crumbs[] = [ 'label' => 'Tin tức', 'url' => '' ];
		} else {
			// Check if we are on training_type virtual archive /hinh-thuc-dao-tao/ or /he-dao-tao/
			$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
			if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path, $m ) ) {
				if ( ! empty( $m[1] ) && 'page' !== $m[1] ) {
					$slug_lookup = ( 'tu-xa' === $m[1] ) ? 'dao-tao-tu-xa' : $m[1];
					$term        = get_term_by( 'slug', $slug_lookup, 'training_type' ) ?: get_term_by( 'slug', $m[1], 'training_type' );
					$crumbs[]    = [ 'label' => 'Hình thức đào tạo', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
					$crumbs[]    = [ 'label' => $term ? $term->name : esc_html( $m[1] ), 'url' => '' ];
				} else {
					$crumbs[] = [ 'label' => 'Hình thức đào tạo', 'url' => '' ];
				}
			} else {
				$crumbs[] = [ 'label' => wp_title( '', false ) ?: 'Lưu trữ', 'url' => '' ];
			}
		}

		// Render crumbs
		$html_parts = [];
		foreach ( $crumbs as $crumb ) {
			if ( ! empty( $crumb['url'] ) ) {
				$html_parts[] = '<a href="' . esc_url( $crumb['url'] ) . '" class="hover:text-brand-primary transition-colors font-medium shrink-0">' . esc_html( $crumb['label'] ) . '</a>';
			} else {
				$html_parts[] = '<span class="text-slate-700 font-semibold shrink-0">' . esc_html( $crumb['label'] ) . '</span>';
			}
		}
		$html = implode( ' <span class="mx-1 text-slate-300 font-light shrink-0">/</span> ', $html_parts );
	}

	if ( ! empty( $html ) ) {
		echo '<div class="ltdh-breadcrumb bg-slate-50/60 border-b border-slate-100 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none flex items-center gap-1.5">';
		echo $html;
		echo '</div>';
	}
}

// ----------------------------------------------------
// 5. Performance / Query Caching
// ----------------------------------------------------

function ltdh_get_cached_query(string $transient_key, array $query_args, int $expiration = HOUR_IN_SECONDS) {
	$cached_results = get_transient($transient_key);
	if (false !== $cached_results) {
		return $cached_results;
	}
	$query = new WP_Query($query_args);
	set_transient($transient_key, $query, $expiration);
	return $query;
}

function ltdh_get_cached_featured_schools() {
	$cache_key = 'ltdh_featured_schools_data_v10';
	$data      = get_transient( $cache_key );
	if ( false !== $data && is_array( $data ) ) {
		return $data;
	}

	$schools_query = new WP_Query([
		'post_type'      => 'school',
		'posts_per_page' => 5,
		'post_status'    => 'publish',
	]);

	$data = [];
	if ( $schools_query->have_posts() ) {
		$index = 0;
		$fallback_images = ltdh_default('images', 'fallback_school_covers', []);
		while ( $schools_query->have_posts() ) {
			$schools_query->the_post();
			$school_id = get_the_ID();
			$address   = get_field( 'address', $school_id );
			$hotline   = ltdh_get_school_hotline( $school_id );
			$thumb_url = ltdh_get_school_cover_url( $school_id, 'medium' );
			$logo_id   = ltdh_get_school_image_id( $school_id );
			$en_name   = get_post_meta( $school_id, 'english_name', true ) ?: 'University';

			$school_progs = get_posts([
				'post_type' => 'program',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query' => [
					[
						'key' => 'school_relationship',
						'value' => $school_id,
						'compare' => '='
					]
				]
			]);

			$systems = [];
			if ( ! empty( $school_progs ) ) {
				foreach ($school_progs as $sp_id) {
					$terms = wp_get_post_terms($sp_id, 'training_type');
					if (! is_wp_error($terms)) {
						foreach ($terms as $t) {
							$systems[$t->slug] = $t->name;
						}
					}
				}
			}

			$systems_label = '';
			if (! empty($systems)) {
				if (count($systems) === 1 && isset($systems['tu-xa'])) {
					$systems_label = '';
				} else {
					$systems_label = implode(' · ', $systems);
				}
			}

			$data[] = [
				'id'            => $school_id,
				'title'         => get_the_title($school_id),
				'permalink'     => get_permalink($school_id),
				'address'       => $address,
				'hotline'       => $hotline,
				'thumb_url'     => $thumb_url,
				'logo_id'       => $logo_id,
				'en_name'       => $en_name,
				'systems_label' => $systems_label,
				'prog_count'    => ltdh_get_school_unique_majors_count($school_id),
			];
			$index++;
		}
		wp_reset_postdata();
	}

	set_transient( $cache_key, $data, HOUR_IN_SECONDS );
	return $data;
}

/**
 * Cached filter dropdown options for Homepage & Search Bar.
 *
 * Avoids executing get_posts( -1 ) on every page request.
 *
 * @return array
 */
function ltdh_get_cached_filter_options(): array {
	$cache_key = 'ltdh_filter_options';
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$schools = get_posts([
		'post_type'      => 'school',
		'posts_per_page' => 100,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	]);

	$school_list = [];
	foreach ( $schools as $sid ) {
		$school_list[] = [
			'slug'  => get_post_field( 'post_name', $sid ),
			'title' => get_the_title( $sid ),
		];
	}

	$majors = get_posts([
		'post_type'      => 'major',
		'posts_per_page' => 100,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	]);

	$major_list = [];
	foreach ( $majors as $mid ) {
		$major_list[] = [
			'slug'  => get_post_field( 'post_name', $mid ),
			'title' => get_the_title( $mid ),
		];
	}

	$types_terms = get_terms([
		'taxonomy'   => LTDH_TAX_TRAINING_TYPE,
		'hide_empty' => false,
	]);

	$type_list = [];
	if ( ! is_wp_error( $types_terms ) && ! empty( $types_terms ) ) {
		foreach ( $types_terms as $tt ) {
			$type_list[] = [
				'slug' => $tt->slug,
				'name' => $tt->name,
			];
		}
	}

	$data = [
		'schools' => $school_list,
		'majors'  => $major_list,
		'types'   => $type_list,
	];

	set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

function ltdh_clear_transients_on_save($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	$post_type = get_post_type($post_id);
	if (in_array($post_type, [LTDH_CPT_PROGRAM, LTDH_CPT_SCHOOL, LTDH_CPT_MAJOR], true)) {
		delete_transient(LTDH_TRANSIENT_FEATURED_SCHOOLS);
		delete_transient('ltdh_featured_schools_data');
		delete_transient('ltdh_hot_majors_data');
		delete_transient('ltdh_combinations_data');
		delete_transient('ltdh_filter_options');
		delete_transient('ltdh_archive_school_featured');
		delete_transient('ltdh_training_type_counts');
	}
	if ('post' === $post_type) {
		delete_transient('ltdh_homepage_news');
	}
}
add_action('save_post', 'ltdh_clear_transients_on_save');

// ----------------------------------------------------

// 6. School Thumbnail Helpers
// ----------------------------------------------------

function ltdh_get_school_image_id(int $school_id): int {
	if (function_exists('get_field')) {
		$logo_id = get_field('logo', $school_id);
		if ($logo_id) {
			if (is_numeric($logo_id)) {
				return (int) $logo_id;
			}
			if (is_array($logo_id) && !empty($logo_id['ID'])) {
				return (int) $logo_id['ID'];
			}
		}
	}
	return 0;
}

function ltdh_generate_school_svg_logo( string $code, string $title = '' ): string {
	$code = strtoupper( trim( $code ) );
	$color_map = [
		'UTC'  => [ 'bg' => '#00308b', 'text' => '#ffffff', 'accent' => '#f97316' ],
		'NEU'  => [ 'bg' => '#b91c1c', 'text' => '#ffffff', 'accent' => '#fbbf24' ],
		'TNU'  => [ 'bg' => '#047857', 'text' => '#ffffff', 'accent' => '#34d399' ],
		'PTIT' => [ 'bg' => '#c2410c', 'text' => '#ffffff', 'accent' => '#fca5a5' ],
		'TMU'  => [ 'bg' => '#1d4ed8', 'text' => '#ffffff', 'accent' => '#93c5fd' ],
		'TVU'  => [ 'bg' => '#0f766e', 'text' => '#ffffff', 'accent' => '#5eead4' ],
		'DNU'  => [ 'bg' => '#ea580c', 'text' => '#ffffff', 'accent' => '#fed7aa' ],
		'BAV'  => [ 'bg' => '#1e3a8a', 'text' => '#ffffff', 'accent' => '#93c5fd' ],
		'AOF'  => [ 'bg' => '#4338ca', 'text' => '#ffffff', 'accent' => '#c7d2fe' ],
		'HAUI' => [ 'bg' => '#0369a1', 'text' => '#ffffff', 'accent' => '#7dd3fc' ],
	];

	$c = $color_map[ $code ] ?? [ 'bg' => '#0f172a', 'text' => '#ffffff', 'accent' => '#3b82f6' ];
	$font_size = strlen( $code ) <= 3 ? '40' : ( strlen( $code ) <= 5 ? '30' : '22' );

	$svg = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">' .
		'<rect width="120" height="120" rx="24" fill="%s"/>' .
		'<circle cx="60" cy="60" r="50" fill="none" stroke="%s" stroke-width="3" stroke-opacity="0.3"/>' .
		'<text x="60" y="67" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif" font-weight="900" font-size="%s" fill="%s" text-anchor="middle" dominant-baseline="middle">%s</text>' .
		'</svg>',
		$c['bg'],
		$c['accent'],
		$font_size,
		$c['text'],
		esc_html( $code )
	);

	return 'data:image/svg+xml;utf8,' . rawurlencode( $svg );
}

function ltdh_get_school_logo_url( $school_id, string $size = 'thumbnail' ): string {
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
	} elseif ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	if ( $school_id > 0 && function_exists( 'get_field' ) ) {
		$logo = get_field( 'logo', $school_id );
		if ( ! empty( $logo ) ) {
			$url = '';
			if ( is_numeric( $logo ) ) {
				$url = wp_get_attachment_image_url( (int) $logo, $size );
			} elseif ( is_array( $logo ) ) {
				$url = $logo['sizes'][ $size ] ?? ( $logo['url'] ?? '' );
			} elseif ( is_string( $logo ) && 0 === strpos( $logo, 'http' ) ) {
				$url = $logo;
			}
			if ( ! empty( $url ) && false === strpos( $url, 'cropped-logo' ) ) {
				return $url;
			}
		}

		$image_id = ltdh_get_school_image_id( $school_id );
		if ( $image_id ) {
			$url = wp_get_attachment_image_url( $image_id, $size );
			if ( ! empty( $url ) && false === strpos( $url, 'cropped-logo' ) ) {
				return $url;
			}
		}
	}

	$school_code = '';
	$title       = '';
	if ( $school_id > 0 ) {
		$school_code = get_post_meta( $school_id, 'school_code', true );
		$title       = get_the_title( $school_id );
	}

	if ( empty( $school_code ) && ! empty( $title ) ) {
		if ( false !== mb_stripos( $title, 'Giao thông' ) ) {
			$school_code = 'UTC';
		} elseif ( false !== mb_stripos( $title, 'Thái Nguyên' ) ) {
			$school_code = 'TNU';
		} elseif ( false !== mb_stripos( $title, 'Kinh tế Quốc dân' ) ) {
			$school_code = 'NEU';
		} elseif ( false !== mb_stripos( $title, 'Bưu chính' ) ) {
			$school_code = 'PTIT';
		} elseif ( false !== mb_stripos( $title, 'Thương mại' ) ) {
			$school_code = 'TMU';
		} elseif ( false !== mb_stripos( $title, 'Trà Vinh' ) ) {
			$school_code = 'TVU';
		} elseif ( false !== mb_stripos( $title, 'Đại Nam' ) ) {
			$school_code = 'DNU';
		} elseif ( false !== mb_stripos( $title, 'Ngân hàng' ) ) {
			$school_code = 'BAV';
		} elseif ( false !== mb_stripos( $title, 'Tài chính' ) ) {
			$school_code = 'AOF';
		} elseif ( false !== mb_stripos( $title, 'Công nghiệp' ) ) {
			$school_code = 'HAUI';
		} else {
			$words       = explode( ' ', preg_replace( '/^(Trường|Đại học|Học viện)\s+/iu', '', $title ) );
			$school_code = '';
			foreach ( array_slice( $words, 0, 4 ) as $w ) {
				if ( ! empty( $w ) ) {
					$school_code .= mb_substr( $w, 0, 1 );
				}
			}
			$school_code = mb_strtoupper( $school_code );
		}
	}

	if ( ! empty( $school_code ) ) {
		return ltdh_generate_school_svg_logo( $school_code, $title );
	}

	return ltdh_generate_school_svg_logo( 'UNI', 'Đại học' );
}

/**
 * Get school cover / banner photo URL (e.g. for card top backgrounds or school page banners).
 * Never uses the school logo attachment as a banner background.
 *
 * @param int|array|object $school_id School post ID, array, or post object.
 * @param string           $size      Image size name (default 'medium_large').
 * @return string
 */
function ltdh_get_school_cover_url( $school_id, string $size = 'medium_large' ): string {
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
	} elseif ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	$theme_uri = get_template_directory_uri();

	if ( $school_id > 0 ) {
		$logo_id  = function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_id ) : 0;
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

		if ( function_exists( 'get_field' ) ) {
			// 1. Try ACF school_banner, cover_image, page_banner, or banner
			$banner = get_field( 'school_banner', $school_id ) ?: get_field( 'cover_image', $school_id ) ?: get_field( 'page_banner', $school_id ) ?: get_field( 'banner', $school_id );
			if ( ! empty( $banner ) ) {
				$url = '';
				if ( is_numeric( $banner ) ) {
					$url = wp_get_attachment_image_url( (int) $banner, $size );
				} elseif ( is_array( $banner ) ) {
					$url = $banner['sizes'][ $size ] ?? ( $banner['url'] ?? '' );
				} elseif ( is_string( $banner ) && 0 === strpos( $banner, 'http' ) ) {
					$url = $banner;
				}
				if ( ! empty( $url ) && $url !== $logo_url && false === strpos( $url, 'cropped-logo' ) && false === strpos( strtolower( $url ), 'logo' ) ) {
					return $url;
				}
			}

			// 2. Check featured image ONLY IF it's NOT the logo attachment
			$thumb_id = get_post_thumbnail_id( $school_id );
			if ( $thumb_id && (int) $thumb_id !== (int) $logo_id ) {
				$url = wp_get_attachment_image_url( $thumb_id, $size );
				if ( ! empty( $url ) && $url !== $logo_url && false === strpos( $url, 'cropped-logo' ) && false === strpos( strtolower( $url ), 'logo' ) ) {
					return $url;
				}
			}
		}
	}

	// 3. Fallback campus cover photos (never returning logo image)
	return $theme_uri . '/assets/images/banner-school.jpg';
}

function ltdh_render_school_thumbnail(int $school_id, string $size = 'thumbnail', string $classes = 'h-14 w-14 object-cover border border-slate-100 bg-white rounded-lg'): void {
	$image_id = ltdh_get_school_image_id($school_id);
	if ($image_id) {
		echo wp_get_attachment_image($image_id, $size, false, [
			'class'   => $classes,
			'loading' => 'lazy',
			'alt'     => sprintf('Logo %s', get_the_title($school_id)),
		]);
		return;
	}

	$fallback_classes = preg_replace('/\bobject-(cover|contain)\b/', '', $classes);
	$fallback_classes = trim(preg_replace('/\s+/', ' ', $fallback_classes));
	printf(
		'<div class="%s bg-blue-50 text-brand-primary font-display font-black text-sm flex items-center justify-center" aria-hidden="true">UNI</div>',
		esc_attr($fallback_classes)
	);
}

// ----------------------------------------------------
// 7. Program Learning Details
// ----------------------------------------------------

function ltdh_get_program_learning_details(int $program_id): array {
	$campuses          = wp_get_post_terms($program_id, LTDH_TAX_CAMPUS);
	$physical_campuses = [];

	if ( ! empty($campuses) && ! is_wp_error($campuses) ) {
		foreach ($campuses as $campus) {
			$slug = is_object($campus) ? $campus->slug : '';
			$name = is_object($campus) ? trim($campus->name) : '';
			if ('online' === strtolower($slug) || 'online' === strtolower($name)) {
				continue;
			}
			if ($name && ! in_array($name, $physical_campuses, true)) {
				$physical_campuses[] = $name;
			}
		}
	}

	$types     = wp_get_post_terms($program_id, LTDH_TAX_TRAINING_TYPE);
	$type_slug = ! empty($types) && ! is_wp_error($types) ? $types[0]->slug : '';

	// Resolve physical campus location:
	if ( in_array( $type_slug, [ 'tu-xa', 'dao-tao-tu-xa' ], true ) ) {
		// Online learning is accessible nationwide
		$campus_name = ! empty( $physical_campuses ) ? 'Toàn quốc (' . implode( ', ', $physical_campuses ) . ')' : 'Toàn quốc (Học Online)';
	} elseif ( ! empty( $physical_campuses ) ) {
		$campus_name = implode( ', ', $physical_campuses );
	} else {
		// Non-tu-xa without explicit physical campus: check school region
		$school_id = get_post_meta( $program_id, 'school_relationship', true );
		if ( is_array( $school_id ) ) {
			$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
		} elseif ( is_object( $school_id ) ) {
			$school_id = $school_id->ID;
		}
		$school_region = '';
		if ( $school_id ) {
			$region_terms = wp_get_post_terms( (int) $school_id, LTDH_TAX_REGION );
			if ( ! empty( $region_terms ) && ! is_wp_error( $region_terms ) ) {
				$school_region = $region_terms[0]->name;
			}
		}
		$campus_name = $school_region ?: 'Hà Nội, TP. Hồ Chí Minh';
	}

	// Resolve learning mode
	if ( 'tu-xa' === $type_slug ) {
		$learning_mode = 'Học online 100%';
	} elseif ( 'vua-hoc-vua-lam' === $type_slug ) {
		$learning_mode = 'Học tập trung / Cuối tuần';
	} else {
		$learning_mode = 'Học tập trung / Cuối tuần';
	}

	return [
		'campus' => $campus_name,
		'mode'   => $learning_mode,
	];
}

// ----------------------------------------------------
// 12. Dynamic CF7 Program Selector
// ----------------------------------------------------

function ltdh_cf7_dynamic_programs($tag, $replace) {
	if ('current_program_id' === $tag['name']) {
		$programs = get_posts(['post_type' => LTDH_CPT_PROGRAM, 'numberposts' => -1]);
		$tag['raw_values'] = [];
		$tag['values']     = [];
		$tag['labels']     = [];

		$tag['raw_values'][] = '';
		$tag['values'][]     = '';
		$tag['labels'][]     = '-- Chọn ngành hoặc hệ học --';

		foreach ($programs as $p) {
			$tag['raw_values'][] = $p->post_title;
			$tag['values'][]     = $p->post_title;
			$tag['labels'][]     = $p->post_title;
		}
	}
	return $tag;
}
add_filter('wpcf7_form_tag', 'ltdh_cf7_dynamic_programs', 10, 2);

// ----------------------------------------------------
// 13. Fallback Image Helper
// ----------------------------------------------------

function ltdh_get_fallback_image(string $context = 'program'): string {
	$theme_uri = get_template_directory_uri();
	if ( $context === 'school' ) {
		return $theme_uri . '/assets/images/banner-school.jpg';
	}
	if ( $context === 'news' ) {
		if ( file_exists( get_template_directory() . '/assets/images/banner-fallback.webp' ) ) {
			return $theme_uri . '/assets/images/banner-fallback.webp';
		}
	}
	if ( function_exists( 'get_field' ) ) {
		$custom_share_image = get_field( 'global_share_image', 'options' );
		if ( ! empty( $custom_share_image ) ) {
			return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $custom_share_image ) : $custom_share_image;
		}
	}
	return $theme_uri . '/assets/images/banner-program.jpg';
}

function ltdh_get_school_unique_majors_count( int $school_id ): int {
	$cache_key = 'ltdh_school_majors_count_' . $school_id;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	$allowed_slugs = [ 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];
	$programs      = get_posts( [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[
				'key'     => 'school_relationship',
				'value'   => $school_id,
				'compare' => '=',
			],
		],
		'tax_query'      => [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => $allowed_slugs,
				'operator' => 'IN',
			],
		],
	] );

	if ( empty( $programs ) ) {
		$offered_ids = get_post_meta( $school_id, '_offered_programs', true );
		if ( ! empty( $offered_ids ) && is_array( $offered_ids ) ) {
			$programs = get_posts( [
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'post__in'       => $offered_ids,
				'tax_query'      => [
					[
						'taxonomy' => LTDH_TAX_TRAINING_TYPE,
						'field'    => 'slug',
						'terms'    => $allowed_slugs,
						'operator' => 'IN',
					],
				],
			] );
		}
	}

	if ( empty( $programs ) ) {
		wp_cache_set( $cache_key, 0, 'ltdh', HOUR_IN_SECONDS );
		return 0;
	}

	$major_ids = [];
	foreach ( $programs as $prog_id ) {
		$major_rel = get_post_meta( $prog_id, 'major_relationship', true );
		if ( is_array( $major_rel ) ) {
			$major_rel = ! empty( $major_rel ) ? ( is_object( $major_rel[0] ) ? $major_rel[0]->ID : $major_rel[0] ) : 0;
		} elseif ( is_object( $major_rel ) ) {
			$major_rel = $major_rel->ID;
		}
		$major_id = intval( $major_rel );
		if ( $major_id && ! in_array( $major_id, $major_ids, true ) ) {
			$major_ids[] = $major_id;
		}
	}

	$count = count( $major_ids );
	wp_cache_set( $cache_key, $count, 'ltdh', HOUR_IN_SECONDS );
	return $count;
}

function ltdh_get_training_type_badge_html( string $type_name ): string {
	if ( ! $type_name ) {
		return '';
	}
	$badge_class = 'bg-orange-50 text-orange-600 border border-orange-100';
	$type_name_lower = mb_strtolower( trim( $type_name ), 'UTF-8' );
	if ( false !== strpos( $type_name_lower, 'chính quy' ) ) {
		$badge_class = 'bg-blue-50 text-blue-600 border border-blue-100';
	} elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
		$badge_class = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
	} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
		$badge_class = 'bg-amber-50 text-amber-600 border border-amber-100';
	}
	return sprintf(
		'<span class="%s text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide border shadow-xs inline-block leading-normal">%s</span>',
		esc_attr( $badge_class ),
		esc_html( $type_name )
	);
}

/**
 * Get dynamic program tuition display with fallback.
 */
function ltdh_get_program_tuition_display(int $program_id, bool $include_year = false): string {
	$amount        = get_field('tuition_amount', $program_id);
	$unit          = get_field('tuition_unit', $program_id);
	$academic_year = get_field('tuition_academic_year', $program_id);

	if (! empty($amount)) {
		$unit_label = 'đ';
		if ($unit === 'tin-chi') {
			$unit_label = 'đ/tín chỉ';
		} elseif ($unit === 'hoc-ky') {
			$unit_label = 'đ/học kỳ';
		} elseif ($unit === 'nam') {
			$unit_label = 'đ/năm';
		}
		
		$year_suffix = ($include_year && ! empty($academic_year)) ? ' · ' . $academic_year : '';
		return number_format((float) $amount, 0, ',', '.') . ' ' . $unit_label . $year_suffix;
	}

	// Fallback to original tuition_fee
	$legacy_tuition = get_field('tuition_fee', $program_id);
	return ! empty($legacy_tuition) ? $legacy_tuition : 'Liên hệ';
}

/**
 * Get dynamic admission deadline display based on batches or enrollment_period.
 */
function ltdh_get_program_admission_deadline_display(int $program_id): string {
	$batches = get_field('admission_batches', $program_id);
	if (is_array($batches) && ! empty($batches)) {
		$sap_mo_batches = [];
		foreach ($batches as $b) {
			$status             = $b['batch_status'] ?? '';
			$batch_name         = $b['batch_name'] ?? '';
			$clean_name         = preg_replace('/^Tuyển sinh\s*/ui', '', $batch_name);
			$application_period = $b['application_period'] ?? '';
			
			if ($status === 'dang-nhan') {
				return esc_html($clean_name) . ': ' . esc_html($application_period);
			}
			$sap_mo_batches[] = [
				'status' => $status,
				'name'   => $clean_name,
				'period' => $application_period,
			];
		}
		
		// If no batch is active, try to find a "sap-mo" batch
		foreach ($sap_mo_batches as $b) {
			if ($b['status'] === 'sap-mo') {
				return esc_html($b['name']) . ' (Sắp mở): ' . esc_html($b['period']);
			}
		}
	}

	// Fallback to legacy enrollment_period
	$legacy_period = get_field('enrollment_period', $program_id);
	return ! empty($legacy_period) ? $legacy_period : 'Đang nhận hồ sơ';
}


// Hot majors helper has been moved to inc/core/class-menus.php

/**
 * Get active training types for a school (with caching and automatic rollup from programs).
 *
 * Rolls up exclusively from published, in-scope programs where
 * school_relationship = $school_id and training type term slug is in ['tu-xa', 'vua-hoc-vua-lam'].
 * Step 1 (checking terms directly on school post) is eliminated.
 *
 * @param int    $school_id      School post ID.
 * @param string $output_format  'names', 'slugs', or 'terms'/'objects'.
 * @return array
 */
function ltdh_get_school_training_types( int $school_id, string $output_format = 'names' ): array {
	if ( ! $school_id ) {
		return [];
	}

	$cache_key = 'ltdh_school_tt_' . $school_id . '_' . $output_format;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached ) {
		return (array) $cached;
	}

	$allowed_slugs = [ 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];

	// Rollup exclusively from published in-scope programs linked to this school
	$query_args = [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[
				'key'     => 'school_relationship',
				'value'   => $school_id,
				'compare' => '=',
			],
		],
		'tax_query'      => [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => $allowed_slugs,
				'operator' => 'IN',
			],
		],
	];

	$programs = get_posts( $query_args );

	// Fallback to _offered_programs if meta_query returned no programs
	if ( empty( $programs ) ) {
		$offered_ids = get_post_meta( $school_id, '_offered_programs', true );
		if ( ! empty( $offered_ids ) && is_array( $offered_ids ) ) {
			$programs = get_posts( [
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'post__in'       => $offered_ids,
				'tax_query'      => [
					[
						'taxonomy' => LTDH_TAX_TRAINING_TYPE,
						'field'    => 'slug',
						'terms'    => $allowed_slugs,
						'operator' => 'IN',
					],
				],
			] );
		}
	}

	if ( empty( $programs ) ) {
		wp_cache_set( $cache_key, [], 'ltdh', HOUR_IN_SECONDS );
		return [];
	}

	$school_types = [];
	foreach ( $programs as $prog_id ) {
		$prog_terms = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $prog_terms ) && ! empty( $prog_terms ) ) {
			foreach ( $prog_terms as $t ) {
				if ( ! is_object( $t ) || ! in_array( $t->slug, $allowed_slugs, true ) ) {
					continue;
				}
				if ( 'slugs' === $output_format ) {
					$val = $t->slug;
				} elseif ( 'terms' === $output_format || 'objects' === $output_format || 'all' === $output_format ) {
					$val = $t;
				} else {
					$val = $t->name;
				}

				if ( is_object( $val ) ) {
					$school_types[ $t->slug ] = $val;
				} elseif ( ! in_array( $val, $school_types, true ) ) {
					$school_types[] = $val;
				}
			}
		}
	}

	$result = ( 'terms' === $output_format || 'objects' === $output_format || 'all' === $output_format )
		? array_values( $school_types )
		: $school_types;

	wp_cache_set( $cache_key, $result, 'ltdh', HOUR_IN_SECONDS );
	return $result;
}
