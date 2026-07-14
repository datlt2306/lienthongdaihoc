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
	<form action="#" method="POST" class="space-y-4">
		<?php foreach ($hidden_fields as $name => $value) : ?>
			<input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
		<?php endforeach; ?>

		<div>
			<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Họ và tên *'); ?></label>
			<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Họ và tên của bạn'); ?>">
		</div>
		<div>
			<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Số điện thoại *'); ?></label>
			<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Số điện thoại liên hệ'); ?>">
		</div>
		<?php if ('consultation' === $type) : ?>
			<div>
				<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Email (Tùy chọn)'); ?></label>
				<input type="email" name="your-email" class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Địa chỉ email'); ?>">
			</div>
		<?php endif; ?>
		<?php if ('consultation' === $type) : ?>
			<div>
				<label class="block text-sm font-semibold text-slate-600 mb-1"><?php esc_html_e('Nội dung cần tư vấn'); ?></label>
				<textarea name="your-message" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none" placeholder="<?php esc_attr_e('Nhập câu hỏi hoặc yêu cầu cụ thể...'); ?>"></textarea>
			</div>
		<?php endif; ?>

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
 * Get site logo URL with ACF → Customizer fallback.
 */
function ltdh_get_logo_url(): string {
	if (function_exists('get_field')) {
		$acf_logo = get_field('global_logo', 'options');
		if ($acf_logo) {
			if (is_numeric($acf_logo)) {
				return wp_get_attachment_image_url((int) $acf_logo, 'full') ?: '';
			} elseif (is_array($acf_logo) && isset($acf_logo['url'])) {
				return $acf_logo['url'];
			} elseif (is_string($acf_logo)) {
				return $acf_logo;
			}
		}
	}

	$logo_id = get_theme_mod('custom_logo');
	if ($logo_id) {
		$url = wp_get_attachment_image_url($logo_id, 'full');
		if ($url) {
			return $url;
		}
	}
	return '';
}

/**
 * Output site logo HTML.
 */
function ltdh_site_logo(int $max_height = 48): void {
	$logo_url = ltdh_get_logo_url();
	if ($logo_url) :
		$site_name = get_bloginfo('name');
	?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center">
			<img src="<?php echo esc_url($logo_url); ?>"
				alt="<?php echo esc_attr($site_name); ?>"
				class="h-auto object-contain"
				style="max-height: <?php echo esc_attr($max_height); ?>px; width: auto;">
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary">
			<div class="flex flex-col leading-none">
				<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
				<span class="text-xl font-extrabold text-brand-primary">ĐẠI HỌC</span>
			</div>
		</a>
	<?php endif;
}

/**
 * Output site logo for mobile.
 */
function ltdh_site_logo_mobile(int $max_height = 36): void {
	$logo_url = ltdh_get_logo_url();
	if ($logo_url) :
		$site_name = get_bloginfo('name');
	?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center">
			<img src="<?php echo esc_url($logo_url); ?>"
				alt="<?php echo esc_attr($site_name); ?>"
				class="h-auto object-contain"
				style="max-height: <?php echo esc_attr($max_height); ?>px; width: auto;">
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary">
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
	if (function_exists('rank_math_the_breadcrumbs')) {
		echo '<div class="ltdh-breadcrumb max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-slate-400">';
		rank_math_the_breadcrumbs();
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

function ltdh_clear_transients_on_save($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	$post_type = get_post_type($post_id);
	if (in_array($post_type, [LTDH_CPT_PROGRAM, LTDH_CPT_SCHOOL, LTDH_CPT_MAJOR], true)) {
		delete_transient(LTDH_TRANSIENT_FEATURED_SCHOOLS);
		delete_transient('ltdh_combinations_data');
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
			return (int) $logo_id;
		}
	}
	if (has_post_thumbnail($school_id)) {
		return (int) get_post_thumbnail_id($school_id);
	}
	return 0;
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
	$campuses    = wp_get_post_terms($program_id, LTDH_TAX_CAMPUS);
	$campus_name = ! empty($campuses) && ! is_wp_error($campuses) ? $campuses[0]->name : 'Hà Nội';

	$types      = wp_get_post_terms($program_id, LTDH_TAX_TRAINING_TYPE);
	$type_slug  = ! empty($types) && ! is_wp_error($types) ? $types[0]->slug : '';

	$mode_map = [
		'tu-xa'          => 'Học online 100%',
		'vua-hoc-vua-lam' => 'Học tập trung cuối tuần',
		'van-bang-2'     => 'Học tập trung / Online linh hoạt',
	];

	$learning_mode = $mode_map[$type_slug] ?? 'Học tập trung';

	return [
		'campus' => $campus_name,
		'mode'   => $learning_mode,
	];
}

// ----------------------------------------------------
// 8. Menu Helpers
// ----------------------------------------------------

/**
 * Get fallback menu items for a given location from ACF options or defaults.
 */
function ltdh_get_fallback_menu_items(string $location = 'primary'): array {
	if (function_exists('get_field')) {
		$acf_items = get_field("menu_{$location}_items", 'options');
		if (! empty($acf_items) && is_array($acf_items)) {
			return $acf_items;
		}
	}
	return ltdh_default('navigation', $location, []);
}

/**
 * Render a fallback menu from config items.
 *
 * @param string $location 'primary', 'mobile', or 'footer'.
 * @param string $ul_class CSS class for the <ul>.
 */
function ltdh_render_fallback_menu(string $location, string $ul_class = ''): void {
	$menu_items = ltdh_get_fallback_menu_items($location);
	if (empty($menu_items)) {
		return;
	}

	$current_path = untrailingslashit(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
	$active_parent = '';
	foreach ($menu_items as $mi) {
		$item_path = untrailingslashit($mi['url']);
		if ($current_path === $item_path || strpos($current_path, $item_path) === 0) {
			$active_parent = $mi['url'];
			break;
		}
	}
	?>
	<ul class="<?php echo esc_attr($ul_class); ?>">
		<?php foreach ($menu_items as $mi) :
			$url_path  = untrailingslashit($mi['url']);
			$is_active = ($current_path === $url_path || $active_parent === $mi['url']);
			$active_class = $is_active ? ' current-menu-item' : '';
		?>
			<li class="menu-item<?php echo esc_attr($active_class); ?>">
				<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php
}

/**
 * Fallback callback for wp_nav_menu — primary menu.
 */
function ltdh_default_primary_menu(): void {
	ltdh_render_fallback_menu('primary', 'nav-primary-menu');
}

/**
 * Fallback callback for wp_nav_menu — mobile menu.
 */
function ltdh_default_mobile_menu(): void {
	ltdh_render_fallback_menu('mobile', 'nav-mobile-menu');
}

/**
 * Fallback callback for wp_nav_menu — footer menu.
 */
function ltdh_default_footer_menu(): void {
	ltdh_render_fallback_menu('footer', 'space-y-2 text-sm flex flex-col nav-footer-menu');
}

// ----------------------------------------------------
// 9. Menu Active Class Logic
// ----------------------------------------------------

function ltdh_menu_add_active_classes($menu_items) {
	if (empty($menu_items) || ! is_array($menu_items)) {
		return $menu_items;
	}

	$current_url = trailingslashit(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
	$current_url = untrailingslashit($current_url);

	foreach ($menu_items as $key => $item) {
		$item_url = trailingslashit(parse_url($item->url, PHP_URL_PATH));
		$item_url = untrailingslashit($item_url);

		if ($current_url === $item_url) {
			$menu_items[$key]->classes[] = 'current-menu-item';
			continue;
		}

		if ($item_url && $current_url !== '/' && strpos($current_url, $item_url) === 0) {
			$menu_items[$key]->classes[] = 'current-menu-ancestor';
		}
	}

	return $menu_items;
}
add_filter('wp_nav_menu_objects', 'ltdh_menu_add_active_classes');

// ----------------------------------------------------
// 10. Dynamic Menu Submenu Injection
// ----------------------------------------------------

function ltdh_dynamic_menu_submenu_injection($sorted_menu_items, $args) {
	if (! in_array($args->theme_location, ['primary-menu'], true)) {
		return $sorted_menu_items;
	}

	$new_items  = [];
	$max_db_id  = 0;
	foreach ($sorted_menu_items as $item) {
		if ($item->ID > $max_db_id) {
			$max_db_id = $item->ID;
		}
	}

	foreach ($sorted_menu_items as $item) {
		$new_items[] = $item;
		$title = mb_strtolower(trim($item->title), 'UTF-8');

		if ($title === 'hệ đào tạo') {
			$filtered_items = [];
			foreach ($new_items as $ni) {
				if ((int) $ni->menu_item_parent === (int) $item->ID) {
					continue;
				}
				$filtered_items[] = $ni;
			}
			$new_items = $filtered_items;

			$item->classes[] = 'menu-item-has-children';
			$types = get_terms(['taxonomy' => LTDH_TAX_TRAINING_TYPE, 'hide_empty' => false]);
			if (! is_wp_error($types)) {
				foreach ($types as $t) {
					$max_db_id++;
					$sub_item                = new stdClass();
					$sub_item->ID            = $max_db_id;
					$sub_item->db_id         = $max_db_id;
					$sub_item->title         = $t->name;
					$sub_item->url           = home_url('/he-dao-tao/' . $t->slug . '/');
					$sub_item->menu_item_parent = $item->ID;
					$sub_item->classes       = ['menu-item', 'menu-item-type-taxonomy', 'menu-item-object-training_type'];
					$sub_item->type          = 'taxonomy';
					$sub_item->object        = LTDH_TAX_TRAINING_TYPE;
					$sub_item->object_id     = $t->term_id;
					$sub_item->post_parent   = 0;
					$sub_item->post_title    = $t->name;
					$sub_item->post_status   = 'publish';
					$sub_item->post_type     = 'nav_menu_item';
					$sub_item->menu_order    = 0;
					$sub_item->target        = '';
					$sub_item->attr_title    = '';
					$sub_item->description   = '';
					$sub_item->xfn           = '';
					$sub_item->current       = false;
					$sub_item->current_item_parent  = false;
					$sub_item->current_item_ancestor = false;

					$new_items[] = $sub_item;
				}
			}
		}
	}

	return $new_items;
}
add_filter('wp_nav_menu_objects', 'ltdh_dynamic_menu_submenu_injection', 10, 2);

// ----------------------------------------------------
// 11. Highlight Menu for Training Type Pages
// ----------------------------------------------------

function ltdh_highlight_menu_parameters($classes, $item) {
	$current_url = home_url($_SERVER['REQUEST_URI'] ?? '/');
	$item_url    = $item->url;
	if (! $item_url) {
		return $classes;
	}

	$current_parts = wp_parse_url($current_url);
	$item_parts    = wp_parse_url($item_url);

	if (! isset($item_parts['path']) || ! isset($current_parts['path'])) {
		return $classes;
	}

	if (preg_match('#/he-dao-tao/([^/]+)/?$#i', $item_parts['path'], $item_match)) {
		if (preg_match('#/he-dao-tao/([^/]+)/?$#i', $current_parts['path'], $current_match)) {
			if ($item_match[1] === $current_match[1]) {
				$classes[] = 'current-menu-item';
			}
		}
	}

	if (isset($item_parts['query'])) {
		parse_str($item_parts['query'], $item_query);
		if (isset($item_query['he'])) {
			$current_he = '';
			if (preg_match('#/he-dao-tao/([^/]+)/?$#i', $current_parts['path'], $m)) {
				$current_he = $m[1];
			} elseif (isset($current_parts['query'])) {
				parse_str($current_parts['query'], $current_query);
				$current_he = $current_query['he'] ?? '';
			}
			if ($current_he && $item_query['he'] === $current_he) {
				$classes[] = 'current-menu-item';
			}
		}
	}

	return $classes;
}
add_filter('nav_menu_css_class', 'ltdh_highlight_menu_parameters', 10, 2);

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
	return ltdh_default('images', "fallback_{$context}", '');
}
