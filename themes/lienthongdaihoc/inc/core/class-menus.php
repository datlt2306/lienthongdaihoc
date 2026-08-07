<?php
/**
 * Navigation Menu Helpers and Filters.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Fallback Menu Configuration & Rendering
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
// 2. Menu Active Class Logic
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
// 3. Dynamic Menu Submenu Injection
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

		if ($title === 'chuyên ngành') {
			$filtered_items = [];
			foreach ($new_items as $ni) {
				if ((int) $ni->menu_item_parent === (int) $item->ID) {
					continue;
				}
				$filtered_items[] = $ni;
			}
			$new_items = $filtered_items;

			$item->classes[] = 'menu-item-has-children';
			$hot_majors = ltdh_get_hot_majors();
			foreach ($hot_majors as $maj) {
				$max_db_id++;
				$sub_item = new stdClass();
				$sub_item->ID = $max_db_id;
				$sub_item->db_id = $max_db_id;
				$sub_item->title = $maj->post_title;
				$sub_item->url = get_permalink($maj->ID);
				$sub_item->menu_item_parent = $item->ID;
				$sub_item->classes = ['menu-item', 'menu-item-type-post_type', 'menu-item-object-major'];
				$sub_item->type = 'post_type';
				$sub_item->object = LTDH_CPT_MAJOR;
				$sub_item->object_id = $maj->ID;
				$sub_item->post_parent = 0;
				$sub_item->post_title = $maj->post_title;
				$sub_item->post_status = 'publish';
				$sub_item->post_type = 'nav_menu_item';
				$sub_item->menu_order = 0;
				$sub_item->target = '';
				$sub_item->attr_title = '';
				$sub_item->description = '';
				$sub_item->xfn = '';
				$sub_item->current = false;
				$sub_item->current_item_parent = false;
				$sub_item->current_item_ancestor = false;
				$new_items[] = $sub_item;
			}

			$max_db_id++;
			$view_all = new stdClass();
			$view_all->ID = $max_db_id;
			$view_all->db_id = $max_db_id;
			$view_all->title = 'Xem tất cả ngành →';
			$view_all->url = home_url('/nganh-hoc/');
			$view_all->menu_item_parent = $item->ID;
			$view_all->classes = ['menu-item', 'ltdh-view-all-link'];
			$view_all->type = 'custom';
			$view_all->object = '';
			$view_all->object_id = 0;
			$view_all->post_parent = 0;
			$view_all->post_title = 'Xem tất cả ngành →';
			$view_all->post_status = 'publish';
			$view_all->post_type = 'nav_menu_item';
			$view_all->menu_order = 0;
			$view_all->target = '';
			$view_all->attr_title = '';
			$view_all->description = '';
			$view_all->xfn = '';
			$view_all->current = false;
			$view_all->current_item_parent = false;
			$view_all->current_item_ancestor = false;
			$new_items[] = $view_all;
		}
	}

	return $new_items;
}
add_filter('wp_nav_menu_objects', 'ltdh_dynamic_menu_submenu_injection', 10, 2);

// ----------------------------------------------------
// 4. Highlight Menu for Training Type Pages
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

	if (preg_match('#/he-dao-tao/([^/]+)(?:/page/\d+)?/?$#i', $item_parts['path'], $item_match) && 'page' !== $item_match[1]) {
		if (preg_match('#/he-dao-tao/([^/]+)(?:/page/\d+)?/?$#i', $current_parts['path'], $current_match) && 'page' !== $current_match[1]) {
			if ($item_match[1] === $current_match[1]) {
				$classes[] = 'current-menu-item';
			}
		}
	}

	if (isset($item_parts['query'])) {
		parse_str($item_parts['query'], $item_query);
		if (isset($item_query['he'])) {
			$current_he = '';
			if (preg_match('#/he-dao-tao/([^/]+)(?:/page/\d+)?/?$#i', $current_parts['path'], $m) && 'page' !== $m[1]) {
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
// 5. Hot Majors Helper
// ----------------------------------------------------

/**
 * Get the top 5 hot majors for menu and homepage.
 * Looks for ACF option first, falls back to hardcoded slugs.
 */
function ltdh_get_hot_majors(): array {
	$cache_key = 'ltdh_hot_majors_data';
	$majors = get_transient( $cache_key );
	if ( false !== $majors ) {
		return $majors;
	}

	$slugs = [];
	if ( function_exists('get_field') ) {
		$slugs = get_field('hot_major_slugs', 'options') ?: [];
	}
	if ( empty($slugs) ) {
		$slugs = ['cong-nghe-thong-tin', 'quan-tri-kinh-doanh', 'ke-toan', 'thuong-mai-dien-tu', 'logistics'];
	}

	$majors = [];
	foreach ($slugs as $slug) {
		$post = get_page_by_path($slug, OBJECT, LTDH_CPT_MAJOR);
		if ($post && $post->post_status === 'publish') {
			$majors[] = $post;
		}
	}

	set_transient( $cache_key, $majors, DAY_IN_SECONDS );
	return $majors;
}
