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
			$url_path   = untrailingslashit($mi['url']);
			$is_active  = ($current_path === $url_path || $active_parent === $mi['url']);
			$has_sub    = ! empty($mi['sub']) && is_array($mi['sub']);
			$is_mega    = ( $url_path === '/nganh-hoc' || in_array( mb_strtolower( trim( $mi['label'] ), 'UTF-8' ), [ 'chuyên ngành', 'ngành học' ], true ) );
			$mega_data  = $is_mega ? ltdh_get_mega_menu_data() : [];
			if ( $is_mega && ! empty( $mega_data ) ) {
				$has_sub = true;
			}
			$li_classes = 'menu-item' . ($is_active ? ' current-menu-item' : '') . ($has_sub ? ' menu-item-has-children' : '') . ($is_mega ? ' menu-item-mega-menu' : '');
		?>
			<li class="<?php echo esc_attr(trim($li_classes)); ?>">
				<?php if ($is_mega && ! empty($mega_data)) : ?>
					<?php if ($location === 'mobile') : ?>
						<div class="mobile-menu-item-row">
							<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
							<button type="button" class="mobile-submenu-toggle" aria-expanded="false" aria-label="Mở rộng menu con" onclick="ltdhToggleSubmenu(this, event)">
								<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
								</svg>
							</button>
						</div>
						<ul class="sub-menu">
							<?php foreach ($mega_data as $cat) : ?>
								<li class="menu-item menu-item-has-children menu-item-category">
									<div class="mobile-menu-item-row">
										<a href="<?php echo esc_url($cat['url']); ?>"><?php echo esc_html($cat['name']); ?></a>
										<?php if (! empty($cat['majors'])) : ?>
											<button type="button" class="mobile-submenu-toggle" aria-expanded="false" aria-label="Mở rộng nhóm ngành" onclick="ltdhToggleSubmenu(this, event)">
												<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
												</svg>
											</button>
										<?php endif; ?>
									</div>
									<?php if (! empty($cat['majors'])) : ?>
										<ul class="sub-menu">
											<?php foreach ($cat['majors'] as $maj) : ?>
												<li class="menu-item">
													<div class="mobile-menu-item-row">
														<a href="<?php echo esc_url($maj['url']); ?>"><?php echo esc_html($maj['title']); ?></a>
													</div>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
							<li class="menu-item ltdh-view-all-link">
								<div class="mobile-menu-item-row">
									<a href="<?php echo esc_url(home_url('/nganh-hoc/')); ?>">Xem tất cả ngành →</a>
								</div>
							</li>
						</ul>
					<?php else : ?>
						<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
						<ul class="sub-menu">
							<?php foreach ($mega_data as $cat) : ?>
								<li class="menu-item menu-item-has-children menu-item-category">
									<a href="<?php echo esc_url($cat['url']); ?>" class="mega-cat-header-link">
										<span class="mega-cat-icon"><?php echo ltdh_get_major_cat_icon($cat['slug']); ?></span>
										<span class="mega-cat-info">
											<span class="mega-cat-name"><?php echo esc_html($cat['name']); ?></span>
											<span class="mega-cat-badge"><?php echo count($cat['majors']); ?> ngành</span>
										</span>
										<span class="mega-cat-arrow">
											<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
											</svg>
										</span>
									</a>
									<?php if (! empty($cat['majors'])) : ?>
										<ul class="sub-menu">
											<?php foreach ($cat['majors'] as $maj) : ?>
												<li class="menu-item">
													<a href="<?php echo esc_url($maj['url']); ?>" class="mega-major-link">
														<span class="mega-major-left">
															<span class="mega-major-bullet"></span>
															<span class="mega-major-name"><?php echo esc_html($maj['title']); ?></span>
														</span>
														<?php if (! empty($maj['is_hot'])) : ?>
															<span class="mega-major-hot">HOT</span>
														<?php endif; ?>
													</a>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
							<li class="menu-item ltdh-view-all-link">
								<div class="mega-footer-bar">
									<div class="mega-footer-info">
										<span class="mega-footer-tag">Tuyển sinh liên thông</span>
										<span class="mega-footer-desc">Đầy đủ hệ: Chính quy, Từ xa &amp; Vừa học vừa làm</span>
									</div>
									<a href="<?php echo esc_url(home_url('/nganh-hoc/')); ?>" class="mega-footer-btn">
										<span>Xem tất cả ngành học</span>
										<svg class="w-3.5 h-3.5 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
										</svg>
									</a>
								</div>
							</li>
						</ul>
					<?php endif; ?>
				<?php else : ?>
					<?php if ($location === 'mobile') : ?>
						<div class="mobile-menu-item-row">
							<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
							<?php if ($has_sub) : ?>
								<button type="button" class="mobile-submenu-toggle" aria-expanded="false" aria-label="Mở rộng menu con" onclick="ltdhToggleSubmenu(this, event)">
									<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
									</svg>
								</button>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
					<?php endif; ?>
					<?php if ($has_sub) : ?>
						<ul class="sub-menu">
							<?php foreach ($mi['sub'] as $sub) :
								$sub_path     = untrailingslashit($sub['url']);
								$is_sub_active = ($current_path === $sub_path);
								$sub_class    = $is_sub_active ? ' current-menu-item' : '';
							?>
								<li class="menu-item<?php echo esc_attr($sub_class); ?>">
									<a href="<?php echo esc_url(home_url($sub['url'])); ?>"><?php echo esc_html($sub['label']); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
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

		if ( in_array( $title, [ 'liên thông đại học', 'liên thông' ], true ) || in_array( $title, [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo' ], true ) ) {
			$filtered_items = [];
			foreach ($new_items as $ni) {
				if ((int) $ni->menu_item_parent === (int) $item->ID) {
					continue;
				}
				$filtered_items[] = $ni;
			}
			$new_items = $filtered_items;

			$item->classes[] = 'menu-item-has-children';

			$allowed_training_types = ['chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam'];
			$types = get_terms([
				'taxonomy'   => LTDH_TAX_TRAINING_TYPE,
				'slug'       => $allowed_training_types,
				'hide_empty' => false,
			]);
			if (! is_wp_error($types) && is_array($types)) {
				$types = array_values(array_filter($types, function($t) use ($allowed_training_types) {
					return in_array($t->slug, $allowed_training_types, true);
				}));
				foreach ($types as $t) {
					$term_link = function_exists( 'get_term_link' ) ? get_term_link( $t ) : '';
					$url = ( ! is_wp_error( $term_link ) && ! empty( $term_link ) ) ? $term_link : home_url( '/hinh-thuc-dao-tao/' . $t->slug . '/' );

					$max_db_id++;
					$sub_item                = new stdClass();
					$sub_item->ID            = $max_db_id;
					$sub_item->db_id         = $max_db_id;
					$sub_item->title         = $t->name;
					$sub_item->url           = $url;
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

		$is_major_menu = in_array( $title, [ 'chuyên ngành', 'ngành học', 'chuyên ngành học' ], true )
			|| ( ! empty( $item->url ) && rtrim( (string) parse_url( $item->url, PHP_URL_PATH ), '/' ) === '/nganh-hoc' )
			|| in_array( 'menu-item-mega-menu', (array) $item->classes, true );

		if ( $is_major_menu ) {
			$filtered_items = [];
			foreach ($new_items as $ni) {
				if ((int) $ni->menu_item_parent === (int) $item->ID) {
					continue;
				}
				$filtered_items[] = $ni;
			}
			$new_items = $filtered_items;

			$item->classes[] = 'menu-item-has-children';
			$item->classes[] = 'menu-item-mega-menu';

			$mega_data = ltdh_get_mega_menu_data();

			if ( ! empty( $mega_data ) ) {
				foreach ( $mega_data as $cat_key => $cat ) {
					$max_db_id++;
					$cat_item = new stdClass();
					$cat_item->ID = $max_db_id;
					$cat_item->db_id = $max_db_id;
					$cat_item->title = $cat['name'];
					$cat_item->url = $cat['url'];
					$cat_item->menu_item_parent = $item->ID;
					$cat_item->classes = [
						'menu-item',
						'menu-item-has-children',
						'menu-item-category',
						'menu-cat-' . sanitize_html_class( $cat['slug'] ),
					];
					$cat_item->type = ! empty( $cat['term_id'] ) ? 'taxonomy' : 'custom';
					$cat_item->object = ! empty( $cat['term_id'] ) ? LTDH_TAX_MAJOR_CAT : '';
					$cat_item->object_id = (int) $cat['term_id'];
					$cat_item->post_parent = 0;
					$cat_item->post_title = $cat['name'];
					$cat_item->post_status = 'publish';
					$cat_item->post_type = 'nav_menu_item';
					$cat_item->menu_order = 0;
					$cat_item->target = '';
					$cat_item->attr_title = '';
					$cat_item->description = '';
					$cat_item->xfn = '';
					$cat_item->current = false;
					$cat_item->current_item_parent = false;
					$cat_item->current_item_ancestor = false;
					$new_items[] = $cat_item;

					if ( ! empty( $cat['majors'] ) ) {
						foreach ( $cat['majors'] as $maj ) {
							$max_db_id++;
							$sub_item = new stdClass();
							$sub_item->ID = $max_db_id;
							$sub_item->db_id = $max_db_id;
							$sub_item->title = $maj['title'];
							$sub_item->url = $maj['url'];
							$sub_item->menu_item_parent = $cat_item->ID;
							$sub_item->classes = [
								'menu-item',
								'menu-item-type-post_type',
								'menu-item-object-major',
							];
							if ( ! empty( $maj['is_hot'] ) ) {
								$sub_item->classes[] = 'is-hot-major';
							}
							$sub_item->type = 'post_type';
							$sub_item->object = LTDH_CPT_MAJOR;
							$sub_item->object_id = (int) $maj['ID'];
							$sub_item->post_parent = 0;
							$sub_item->post_title = $maj['title'];
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
					}
				}
			} else {
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

	if (preg_match('#/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $item_parts['path'], $item_match) && 'page' !== $item_match[1]) {
		if (preg_match('#/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $current_parts['path'], $current_match) && 'page' !== $current_match[1]) {
			if ($item_match[1] === $current_match[1]) {
				$classes[] = 'current-menu-item';
			}
		}
	}

	if (isset($item_parts['query'])) {
		parse_str($item_parts['query'], $item_query);
		if (isset($item_query['he'])) {
			$current_he = '';
			if (preg_match('#/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $current_parts['path'], $m) && 'page' !== $m[1]) {
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
		$output_type = defined( 'OBJECT' ) ? OBJECT : 'OBJECT';
		$post = function_exists( 'get_page_by_path' ) ? get_page_by_path( $slug, $output_type, LTDH_CPT_MAJOR ) : null;
		if ($post && isset( $post->post_status ) && $post->post_status === 'publish') {
			$majors[] = $post;
		}
	}

	$expire = defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;
	set_transient( $cache_key, $majors, $expire );
	return $majors;
}

// ----------------------------------------------------
// 6. Mobile Nav Menu Item Output Filter
// ----------------------------------------------------

/**
 * Filter start_el for nav-mobile-menu to embed accordion toggle button directly in server HTML.
 */
function ltdh_mobile_nav_menu_start_el( $item_output, $item, $depth, $args ) {
	if ( ! isset( $args->menu_class ) || strpos( $args->menu_class, 'nav-mobile-menu' ) === false ) {
		return $item_output;
	}

	$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );

	if ( $has_children ) {
		$toggle_btn = '<button type="button" class="mobile-submenu-toggle" aria-expanded="false" aria-label="' . esc_attr__( 'Mở rộng menu con', 'ltdh' ) . '" onclick="ltdhToggleSubmenu(this, event)">'
			. '<svg class="w-4 h-4 text-slate-500 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">'
			. '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />'
			. '</svg>'
			. '</button>';

		return '<div class="mobile-menu-item-row">' . $item_output . $toggle_btn . '</div>';
	}

	return '<div class="mobile-menu-item-row">' . $item_output . '</div>';
}
add_filter( 'walker_nav_menu_start_el', 'ltdh_mobile_nav_menu_start_el', 10, 4 );

// ----------------------------------------------------
// 7. Mega Menu Helpers & Desktop Filters
// ----------------------------------------------------

/**
 * Retrieve grouped categories and their majors for the mega menu.
 * Cached in transient for high performance, auto-invalidated on post/term save.
 *
 * @return array<string, array{term_id: int, name: string, slug: string, url: string, majors: array}>
 */
function ltdh_get_mega_menu_data(): array {
	if ( ! function_exists( 'get_terms' ) || ! function_exists( 'get_posts' ) ) {
		return [];
	}

	$cache_key = 'ltdh_mega_menu_majors_data';
	$cached    = function_exists( 'get_transient' ) ? get_transient( $cache_key ) : false;
	if ( false !== $cached && is_array( $cached ) && ! empty( $cached ) ) {
		return $cached;
	}

	$cats = get_terms( [
		'taxonomy'   => LTDH_TAX_MAJOR_CAT,
		'hide_empty' => false,
		'orderby'    => 'count',
		'order'      => 'DESC',
	] );

	if ( is_wp_error( $cats ) || empty( $cats ) ) {
		return [];
	}

	$hot_majors = ltdh_get_hot_majors();
	$hot_ids    = [];
	foreach ( $hot_majors as $hm ) {
		if ( isset( $hm->ID ) ) {
			$hot_ids[] = (int) $hm->ID;
		}
	}

	$result                 = [];
	$all_assigned_major_ids = [];

	foreach ( $cats as $cat ) {
		$majors = get_posts( [
			'post_type'      => LTDH_CPT_MAJOR,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'tax_query'      => [
				[
					'taxonomy' => LTDH_TAX_MAJOR_CAT,
					'field'    => 'term_id',
					'terms'    => $cat->term_id,
				],
			],
		] );

		if ( empty( $majors ) ) {
			continue;
		}

		$major_list = [];
		foreach ( $majors as $m ) {
			$all_assigned_major_ids[] = (int) $m->ID;
			$major_list[]             = [
				'ID'     => (int) $m->ID,
				'title'  => $m->post_title,
				'slug'   => $m->post_name ?? '',
				'url'    => get_permalink( $m->ID ),
				'is_hot' => in_array( (int) $m->ID, $hot_ids, true ),
			];
		}

		$term_link = get_term_link( $cat );
		$cat_url   = ( ! is_wp_error( $term_link ) && ! empty( $term_link ) ) ? $term_link : home_url( '/nganh-hoc/?nhom_nganh=' . $cat->slug );

		$result[ $cat->slug ] = [
			'term_id' => (int) $cat->term_id,
			'name'    => $cat->name,
			'slug'    => $cat->slug,
			'url'     => $cat_url,
			'majors'  => $major_list,
		];
	}

	// Handle uncategorized majors if any exist
	$uncategorized = get_posts( [
		'post_type'      => LTDH_CPT_MAJOR,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__not_in'   => ! empty( $all_assigned_major_ids ) ? $all_assigned_major_ids : [ 0 ],
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	] );

	if ( ! empty( $uncategorized ) ) {
		$uncat_list = [];
		foreach ( $uncategorized as $m ) {
			$uncat_list[] = [
				'ID'     => (int) $m->ID,
				'title'  => $m->post_title,
				'slug'   => $m->post_name ?? '',
				'url'    => get_permalink( $m->ID ),
				'is_hot' => in_array( (int) $m->ID, $hot_ids, true ),
			];
		}
		$result['khac'] = [
			'term_id' => 0,
			'name'    => 'Ngành đào tạo khác',
			'slug'    => 'khac',
			'url'     => home_url( '/nganh-hoc/' ),
			'majors'  => $uncat_list,
		];
	}

	if ( ! empty( $result ) ) {
		$expire = defined( 'DAY_IN_SECONDS' ) ? DAY_IN_SECONDS : 86400;
		set_transient( $cache_key, $result, $expire );
	}

	return $result;
}

/**
 * Invalidate mega menu transient cache.
 */
function ltdh_clear_mega_menu_cache(): void {
	delete_transient( 'ltdh_mega_menu_majors_data' );
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'save_post_major', 'ltdh_clear_mega_menu_cache' );
	add_action( 'delete_post', function( $post_id ) {
		if ( function_exists( 'get_post_type' ) && get_post_type( $post_id ) === LTDH_CPT_MAJOR ) {
			ltdh_clear_mega_menu_cache();
		}
	} );
	add_action( 'created_major_cat', 'ltdh_clear_mega_menu_cache' );
	add_action( 'edited_major_cat', 'ltdh_clear_mega_menu_cache' );
	add_action( 'delete_major_cat', 'ltdh_clear_mega_menu_cache' );
}

/**
 * Return an SVG icon corresponding to a major category slug.
 */
function ltdh_get_major_cat_icon( string $slug ): string {
	$icons = [
		'kinh-te-quan-ly'    => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
		'ky-thuat-cong-nghe' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
		'xa-hoi-dich-vu'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75A1.125 1.125 0 0013.125 2.625h-7.5A1.125 1.125 0 004.5 3.75v10.5m9-6.75h3.75"/></svg>',
		'ngon-ngu-nhan-van'  => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
		'nong-lam-moi-truong' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"/></svg>',
	];
	return $icons[ $slug ] ?? '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/></svg>';
}

/**
 * Return major count for a given category.
 */
function ltdh_get_major_count_by_cat( string $slug ): int {
	$data = ltdh_get_mega_menu_data();
	if ( isset( $data[ $slug ]['majors'] ) ) {
		return count( $data[ $slug ]['majors'] );
	}
	return 0;
}

/**
 * Filter start_el to enrich mega menu elements on desktop.
 */
function ltdh_mega_menu_nav_menu_start_el( $item_output, $item, $depth, $args ) {
	$is_mobile  = isset( $args->menu_class ) && strpos( $args->menu_class, 'nav-mobile-menu' ) !== false;
	$is_desktop = ( ( isset( $args->menu_class ) && strpos( $args->menu_class, 'nav-primary-menu' ) !== false ) || ( isset( $args->theme_location ) && $args->theme_location === 'primary-menu' ) ) && ! $is_mobile;
	$classes    = (array) ( $item->classes ?? [] );

	if ( $is_desktop && in_array( 'menu-item-category', $classes, true ) ) {
		$cat_slug = '';
		foreach ( $classes as $c ) {
			if ( strpos( $c, 'menu-cat-' ) === 0 ) {
				$cat_slug = substr( $c, 9 );
				break;
			}
		}
		$icon  = ltdh_get_major_cat_icon( $cat_slug );
		$count = ltdh_get_major_count_by_cat( $cat_slug );

		return '<a href="' . esc_url( $item->url ) . '" class="mega-cat-header-link">'
			. '<span class="mega-cat-icon">' . $icon . '</span>'
			. '<span class="mega-cat-info">'
			. '<span class="mega-cat-name">' . esc_html( $item->title ) . '</span>'
			. ( $count > 0 ? '<span class="mega-cat-badge">' . $count . ' ngành</span>' : '' )
			. '</span>'
			. '<span class="mega-cat-arrow"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>'
			. '</a>';
	}

	if ( $is_desktop && in_array( 'menu-item-object-major', $classes, true ) && $depth === 2 ) {
		$is_hot = in_array( 'is-hot-major', $classes, true );
		return '<a href="' . esc_url( $item->url ) . '" class="mega-major-link">'
			. '<span class="mega-major-left">'
			. '<span class="mega-major-bullet"></span>'
			. '<span class="mega-major-name">' . esc_html( $item->title ) . '</span>'
			. '</span>'
			. ( $is_hot ? '<span class="mega-major-hot">HOT</span>' : '' )
			. '</a>';
	}

	if ( $is_desktop && in_array( 'ltdh-view-all-link', $classes, true ) && $depth === 1 ) {
		return '<div class="mega-footer-bar">'
			. '<div class="mega-footer-info">'
			. '<span class="mega-footer-tag">Tuyển sinh liên thông</span>'
			. '<span class="mega-footer-desc">Đầy đủ hệ: Chính quy, Từ xa &amp; Vừa học vừa làm</span>'
			. '</div>'
			. '<a href="' . esc_url( $item->url ) . '" class="mega-footer-btn">'
			. '<span>Xem tất cả ngành học</span>'
			. '<svg class="w-3.5 h-3.5 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>'
			. '</a>'
			. '</div>';
	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'ltdh_mega_menu_nav_menu_start_el', 9, 4 );

