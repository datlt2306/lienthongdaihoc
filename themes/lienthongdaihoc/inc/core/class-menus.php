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
	$route_info    = function_exists('ltdh_get_current_route_info') ? ltdh_get_current_route_info() : [];

	foreach ($menu_items as $mi) {
		$item_path = untrailingslashit($mi['url']);
		if ($item_path !== '' && ($current_path === $item_path || strpos($current_path, $item_path . '/') === 0)) {
			$active_parent = $mi['url'];
			break;
		}
	}
	?>
	<ul class="<?php echo esc_attr($ul_class); ?>">
		<?php foreach ($menu_items as $mi) :
			$url_path   = untrailingslashit($mi['url']);
			$is_active  = ($current_path === $url_path || $active_parent === $mi['url']);

			if ( ! $is_active && ! empty( $route_info ) ) {
				$mi_label = mb_strtolower( trim( $mi['label'] ), 'UTF-8' );
				if ( ( $route_info['is_program'] || $route_info['is_training_archive'] ) && ( in_array( $url_path, [ '/hinh-thuc-dao-tao', '/he-dao-tao', '/chuong-trinh' ], true ) || in_array( $mi_label, [ 'hình thức đào tạo', 'hình thức liên thông', 'liên thông đại học', 'liên thông', 'hệ đào tạo', 'hình thức học' ], true ) ) ) {
					$is_active = true;
				} elseif ( ( $route_info['is_school'] || $route_info['is_school_archive'] ) && ( in_array( $url_path, [ '/truong-doi-tac', '/truong-dao-tao', '/truong', '/danh-sach-truong' ], true ) || in_array( $mi_label, [ 'trường đối tác', 'trường đào tạo', 'trường', 'đối tác', 'danh sách trường' ], true ) ) ) {
					$is_active = true;
				} elseif ( ( $route_info['is_major'] || $route_info['is_major_archive'] ) && ( in_array( $url_path, [ '/nhom-nganh', '/nganh-hoc', '/chuyen-nganh', '/nganh' ], true ) || in_array( $mi_label, [ 'nhóm ngành', 'ngành học', 'chuyên ngành', 'chuyên ngành học', 'nhóm ngành đào tạo', 'ngành đào tạo', 'ngành' ], true ) ) ) {
					$is_active = true;
				}
			}
			$has_sub    = ! empty($mi['sub']) && is_array($mi['sub']);
			$is_mega    = ( in_array( $url_path, [ '/nganh-hoc', '/nhom-nganh' ], true ) || in_array( mb_strtolower( trim( $mi['label'] ), 'UTF-8' ), [ 'chuyên ngành', 'ngành học', 'nhóm ngành', 'nhóm ngành đào tạo', 'ngành đào tạo' ], true ) );
			$mega_data  = $is_mega ? ltdh_get_mega_menu_data() : [];
			if ( $is_mega && ! empty( $mega_data ) ) {
				$has_sub = true;
			}
			$is_mobile_open = ( $is_active && $has_sub && $location === 'mobile' );
			$li_classes = 'menu-item' . ($is_active ? ' current-menu-item current-menu-ancestor' : '') . ($has_sub ? ' menu-item-has-children' : '') . ($is_mega ? ' menu-item-mega-menu' : '') . ($is_mobile_open ? ' is-open' : '');
		?>
			<li class="<?php echo esc_attr(trim($li_classes)); ?>">
				<?php if ($is_mega && ! empty($mega_data)) : ?>
					<?php if ($location === 'mobile') : ?>
						<div class="mobile-menu-item-row">
							<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
							<button type="button" class="mobile-submenu-toggle" aria-expanded="<?php echo $is_mobile_open ? 'true' : 'false'; ?>" aria-label="Mở rộng menu con" onclick="ltdhToggleSubmenu(this, event)">
								<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
								</svg>
							</button>
						</div>
						<ul class="sub-menu">
							<?php foreach ($mega_data as $cat) : 
								$is_cat_active = ( ! empty( $route_info['major_cat_slug'] ) && $cat['slug'] === $route_info['major_cat_slug'] );
								$cat_li_class  = 'menu-item menu-item-has-children menu-item-category' . ( $is_cat_active ? ' current-menu-ancestor is-open' : '' );
							?>
								<li class="<?php echo esc_attr( $cat_li_class ); ?>">
									<div class="mobile-menu-item-row">
										<a href="<?php echo esc_url($cat['url']); ?>"><?php echo esc_html($cat['name']); ?></a>
										<?php if (! empty($cat['majors'])) : ?>
											<button type="button" class="mobile-submenu-toggle" aria-expanded="<?php echo $is_cat_active ? 'true' : 'false'; ?>" aria-label="Mở rộng nhóm ngành" onclick="ltdhToggleSubmenu(this, event)">
												<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
												</svg>
											</button>
										<?php endif; ?>
									</div>
									<?php if (! empty($cat['majors'])) : 
										$visible_majors = array_slice($cat['majors'], 0, 5);
										$has_more       = count($cat['majors']) > 5;
									?>
										<ul class="sub-menu">
											<?php foreach ($visible_majors as $maj) : 
												$is_maj_active = ( $route_info['is_major'] && ! empty( $route_info['major_id'] ) && (int) ( $maj['id'] ?? 0 ) === (int) $route_info['major_id'] );
												if ( ! $is_maj_active && $route_info['is_major'] && ! empty( $maj['url'] ) ) {
													$maj_p = untrailingslashit( parse_url( $maj['url'], PHP_URL_PATH ) );
													if ( $maj_p === ( $route_info['path'] ?? '' ) ) {
														$is_maj_active = true;
													}
												}
												$maj_class = 'menu-item' . ( $is_maj_active ? ' current-menu-item' : '' );
											?>
												<li class="<?php echo esc_attr( $maj_class ); ?>">
													<div class="mobile-menu-item-row">
														<a href="<?php echo esc_url($maj['url']); ?>"><?php echo esc_html($maj['title']); ?></a>
													</div>
												</li>
											<?php endforeach; ?>
											<?php if ($has_more) : ?>
												<li class="menu-item menu-item-cat-more ltdh-cat-more-link">
													<div class="mobile-menu-item-row">
														<a href="<?php echo esc_url($cat['url']); ?>" class="mega-cat-more-mobile text-xs font-bold text-[#00308b] py-1.5 flex items-center gap-1">
															<span>Xem tất cả</span>
															<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
																<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
															</svg>
														</a>
													</div>
												</li>
											<?php endif; ?>
										</ul>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
							<li class="menu-item ltdh-view-all-link">
								<div class="mobile-menu-item-row">
									<a href="<?php echo esc_url(home_url('/nhom-nganh/')); ?>">Xem tất cả ngành →</a>
								</div>
							</li>
						</ul>
					<?php else : ?>
						<a href="<?php echo esc_url(home_url($mi['url'])); ?>"><?php echo esc_html($mi['label']); ?></a>
						<ul class="sub-menu">
							<?php foreach ($mega_data as $cat) : 
								$is_cat_active = ( ! empty( $route_info['major_cat_slug'] ) && $cat['slug'] === $route_info['major_cat_slug'] );
								$cat_li_class  = 'menu-item menu-item-has-children menu-item-category' . ( $is_cat_active ? ' current-menu-ancestor' : '' );
							?>
								<li class="<?php echo esc_attr( $cat_li_class ); ?>">
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
									<?php if (! empty($cat['majors'])) : 
										$visible_majors = array_slice($cat['majors'], 0, 5);
										$has_more       = count($cat['majors']) > 5;
									?>
										<ul class="sub-menu">
											<?php foreach ($visible_majors as $maj) : 
												$is_maj_active = ( $route_info['is_major'] && ! empty( $route_info['major_id'] ) && (int) ( $maj['id'] ?? 0 ) === (int) $route_info['major_id'] );
												if ( ! $is_maj_active && $route_info['is_major'] && ! empty( $maj['url'] ) ) {
													$maj_p = untrailingslashit( parse_url( $maj['url'], PHP_URL_PATH ) );
													if ( $maj_p === ( $route_info['path'] ?? '' ) ) {
														$is_maj_active = true;
													}
												}
												$maj_class = 'menu-item' . ( $is_maj_active ? ' current-menu-item' : '' );
											?>
												<li class="<?php echo esc_attr( $maj_class ); ?>">
													<a href="<?php echo esc_url($maj['url']); ?>" class="mega-major-link">
														<span class="mega-major-left">
															<span class="mega-major-bullet"></span>
															<span class="mega-major-name"><?php echo esc_html($maj['title']); ?></span>
														</span>
													</a>
												</li>
											<?php endforeach; ?>
											<?php if ($has_more) : ?>
												<li class="menu-item menu-item-cat-more ltdh-cat-more-link">
													<a href="<?php echo esc_url($cat['url']); ?>" class="mega-cat-more-link" title="<?php echo esc_attr('Xem tất cả ngành thuộc nhóm ' . $cat['name']); ?>">
														<span class="mega-cat-more-text">Xem tất cả</span>
														<svg class="w-3.5 h-3.5 mega-cat-more-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
														</svg>
													</a>
												</li>
											<?php endif; ?>
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
									<a href="<?php echo esc_url(home_url('/nhom-nganh/')); ?>" class="mega-footer-btn">
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
								<button type="button" class="mobile-submenu-toggle" aria-expanded="<?php echo $is_mobile_open ? 'true' : 'false'; ?>" aria-label="Mở rộng menu con" onclick="ltdhToggleSubmenu(this, event)">
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
								$sub_path      = untrailingslashit($sub['url']);
								$is_sub_active = ($current_path === $sub_path);

								if ( ! $is_sub_active && ! empty( $route_info ) ) {
									if ( ( $route_info['is_program'] || $route_info['is_training_archive'] ) && ! empty( $route_info['training_type_slug'] ) ) {
										$sub_slug = basename( $sub_path );
										$matching_slugs = [ $route_info['training_type_slug'] ];
										if ( in_array( $route_info['training_type_slug'], [ 'dao-tao-tu-xa', 'tu-xa' ], true ) ) {
											$matching_slugs = [ 'dao-tao-tu-xa', 'tu-xa' ];
										}
										if ( in_array( $sub_slug, $matching_slugs, true ) ) {
											$is_sub_active = true;
										}
									}
								}
								$sub_class    = $is_sub_active ? ' current-menu-item' : '';
							?>
								<li class="menu-item<?php echo esc_attr($sub_class); ?>">
									<?php if ($location === 'mobile') : ?>
										<div class="mobile-menu-item-row">
											<a href="<?php echo esc_url(home_url($sub['url'])); ?>"><?php echo esc_html($sub['label']); ?></a>
										</div>
									<?php else : ?>
										<a href="<?php echo esc_url(home_url($sub['url'])); ?>"><?php echo esc_html($sub['label']); ?></a>
									<?php endif; ?>
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
// 2. Menu Active Class Logic & Route Context Detection
// ----------------------------------------------------

/**
 * Retrieve comprehensive current page/route context for accurate menu highlighting.
 *
 * @param string $uri Optional URI to check, defaults to $_SERVER['REQUEST_URI'].
 * @return array
 */
function ltdh_get_current_route_info( string $uri = '' ): array {
	static $cache = [];
	if ( empty( $uri ) ) {
		$uri = $_SERVER['REQUEST_URI'] ?? '/';
	}
	if ( isset( $cache[ $uri ] ) ) {
		return $cache[ $uri ];
	}

	$path = trim( (string) parse_url( $uri, PHP_URL_PATH ), '/' );

	$info = [
		'uri'                 => $uri,
		'path'                => '/' . $path,
		'is_program'          => false,
		'program_id'          => 0,
		'training_type_slug'  => '',
		'is_school'           => false,
		'school_id'           => 0,
		'is_major'            => false,
		'major_id'            => 0,
		'major_cat_slug'      => '',
		'is_training_archive' => false,
		'is_school_archive'   => false,
		'is_major_archive'    => false,
	];

	$cpt_program = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
	$cpt_school  = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
	$cpt_major   = defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major';

	// 1. Check WordPress native conditional tags
	if ( function_exists( 'is_singular' ) ) {
		if ( is_singular( $cpt_program ) ) {
			$info['is_program'] = true;
			$info['program_id'] = (int) get_the_ID();
		} elseif ( is_singular( $cpt_school ) ) {
			$info['is_school']  = true;
			$info['school_id']  = (int) get_the_ID();
		} elseif ( is_singular( $cpt_major ) ) {
			$info['is_major']   = true;
			$info['major_id']   = (int) get_the_ID();
		}
	}

	// 2. Check global $post if conditional tags did not fire
	if ( ! $info['is_program'] && ! $info['is_school'] && ! $info['is_major'] ) {
		global $post;
		if ( ! empty( $post->post_type ) ) {
			if ( $post->post_type === $cpt_program ) {
				$info['is_program'] = true;
				$info['program_id'] = (int) $post->ID;
			} elseif ( $post->post_type === $cpt_school ) {
				$info['is_school']  = true;
				$info['school_id']  = (int) $post->ID;
			} elseif ( $post->post_type === $cpt_major ) {
				$info['is_major']   = true;
				$info['major_id']   = (int) $post->ID;
			}
		}
	}

	// 3. Fallback resolution via URL slug for testing/simulated environments
	if ( ! $info['is_program'] && ! $info['is_school'] && ! $info['is_major'] && ! empty( $path ) ) {
		if ( preg_match( '#^(?:nganh-|nganh/)([^/]+)$#i', $path, $m_match ) ) {
			if ( function_exists( 'get_posts' ) ) {
				$pts = get_posts( [
					'name'           => $m_match[1],
					'post_type'      => $cpt_major,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
				] );
				if ( ! empty( $pts ) ) {
					$info['is_major'] = true;
					$info['major_id'] = (int) $pts[0]->ID;
				}
			}
		} elseif ( preg_match( '#^(?:truong-|truong/)([^/]+)$#i', $path ) ) {
			if ( function_exists( 'get_posts' ) ) {
				$pts = get_posts( [
					'name'           => $path,
					'post_type'      => $cpt_school,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
				] );
				if ( ! empty( $pts ) ) {
					$info['is_school'] = true;
					$info['school_id'] = (int) $pts[0]->ID;
				}
			}
		} else {
			if ( function_exists( 'get_posts' ) ) {
				$pts = get_posts( [
					'name'           => $path,
					'post_type'      => $cpt_program,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
				] );
				if ( ! empty( $pts ) ) {
					$info['is_program'] = true;
					$info['program_id'] = (int) $pts[0]->ID;
				}
			}
		}
	}

	// 4. Retrieve associated taxonomies for matched items
	if ( $info['is_program'] && $info['program_id'] && function_exists( 'get_the_terms' ) ) {
		$tax_tt = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type';
		$terms  = get_the_terms( $info['program_id'], $tax_tt );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$info['training_type_slug'] = $terms[0]->slug;
		}
	}

	if ( $info['is_major'] && $info['major_id'] && function_exists( 'get_the_terms' ) ) {
		$tax_mc = defined( 'LTDH_TAX_MAJOR_CAT' ) ? LTDH_TAX_MAJOR_CAT : 'major_cat';
		$cats   = get_the_terms( $info['major_id'], $tax_mc );
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			$info['major_cat_slug'] = $cats[0]->slug;
		}
	}

	// 5. Taxonomy & Archive route matching
	if ( preg_match( '#^(?:hinh-thuc-dao-tao|he-dao-tao)(?:/([^/]+))?#i', $path, $t_match ) ) {
		$info['is_training_archive'] = true;
		if ( ! empty( $t_match[1] ) && 'page' !== $t_match[1] ) {
			$info['training_type_slug'] = $t_match[1];
		}
	}

	if ( preg_match( '#^(?:nhom-nganh|nganh-hoc)(?:/([^/]+))?#i', $path, $c_match ) ) {
		$info['is_major_archive'] = true;
		if ( ! empty( $c_match[1] ) && 'page' !== $c_match[1] ) {
			$info['major_cat_slug'] = $c_match[1];
		}
	}

	if ( preg_match( '#^(?:truong-doi-tac|truong-dao-tao|danh-sach-truong)#i', $path ) ) {
		$info['is_school_archive'] = true;
	}

	$cache[ $uri ] = $info;
	return $info;
}

function ltdh_menu_add_active_classes( $menu_items, $args = null ) {
	if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
		return $menu_items;
	}

	$current_url = trailingslashit( parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) );
	$current_url = untrailingslashit( $current_url );
	$route_info  = ltdh_get_current_route_info();

	// 1. Standard URL matching
	foreach ( $menu_items as $key => $item ) {
		$item_url  = trailingslashit( parse_url( $item->url, PHP_URL_PATH ) );
		$item_url  = untrailingslashit( $item_url );

		// Standard URL matching
		if ( $current_url === $item_url ) {
			$menu_items[ $key ]->classes[] = 'current-menu-item';
			$menu_items[ $key ]->current   = true;
			continue;
		}

		if ( $item_url !== '' && $item_url !== '/' && $current_url !== '/' && $current_url !== '' && strpos( $current_url, $item_url . '/' ) === 0 ) {
			$menu_items[ $key ]->classes[] = 'current-menu-ancestor';
		}
	}

	// 2. Program / Training Type Contextual Highlighting
	if ( $route_info['is_program'] || $route_info['is_training_archive'] ) {
		$training_top_level_ids = [];
		$target_training_slugs  = [];
		if ( ! empty( $route_info['training_type_slug'] ) ) {
			$target_training_slugs[] = $route_info['training_type_slug'];
			if ( in_array( $route_info['training_type_slug'], [ 'dao-tao-tu-xa', 'tu-xa' ], true ) ) {
				$target_training_slugs = array_merge( $target_training_slugs, [ 'dao-tao-tu-xa', 'tu-xa' ] );
			}
		}

		foreach ( $menu_items as $key => $item ) {
			$item_path = untrailingslashit( parse_url( $item->url, PHP_URL_PATH ) );
			$title     = mb_strtolower( trim( (string) $item->title ), 'UTF-8' );
			$is_top    = ( 0 === (int) $item->menu_item_parent );

			if ( $is_top ) {
				$training_titles = [ 'hình thức đào tạo', 'hình thức liên thông', 'liên thông đại học', 'liên thông', 'hệ đào tạo', 'hình thức học' ];
				$training_paths  = [ '/hinh-thuc-dao-tao', '/he-dao-tao', '/chuong-trinh' ];
				if ( in_array( $title, $training_titles, true ) || in_array( $item_path, $training_paths, true ) ) {
					$menu_items[ $key ]->classes[] = 'current-menu-item';
					$menu_items[ $key ]->classes[] = 'current-menu-ancestor';
					$menu_items[ $key ]->classes[] = 'current-menu-parent';
					$menu_items[ $key ]->current   = true;
					$training_top_level_ids[]      = (int) $item->ID;
				}
			}
		}

		// Highlight matching training type sub-item
		if ( ! empty( $target_training_slugs ) ) {
			foreach ( $menu_items as $key => $item ) {
				$parent_id = (int) $item->menu_item_parent;
				if ( in_array( $parent_id, $training_top_level_ids, true ) || ( ! empty( $item->object ) && 'training_type' === $item->object ) ) {
					$item_path = untrailingslashit( parse_url( $item->url, PHP_URL_PATH ) );
					$is_match  = false;

					foreach ( $target_training_slugs as $slug ) {
						if ( preg_match( '#/(?:hinh-thuc-dao-tao|he-dao-tao)/' . preg_quote( $slug, '#' ) . '(?:/|$)#i', $item_path ) ) {
							$is_match = true;
							break;
						}
					}

					if ( ! $is_match && ! empty( $item->object_id ) && function_exists( 'get_term' ) ) {
						$t = get_term( (int) $item->object_id, LTDH_TAX_TRAINING_TYPE );
						if ( $t && ! is_wp_error( $t ) && in_array( $t->slug, $target_training_slugs, true ) ) {
							$is_match = true;
						}
					}

					if ( ! $is_match ) {
						$t_title = mb_strtolower( trim( (string) $item->title ), 'UTF-8' );
						if ( in_array( 'dao-tao-tu-xa', $target_training_slugs, true ) || in_array( 'tu-xa', $target_training_slugs, true ) ) {
							if ( in_array( $t_title, [ 'từ xa', 'đào tạo từ xa' ], true ) ) {
								$is_match = true;
							}
						} elseif ( in_array( 'chinh-quy', $target_training_slugs, true ) && 'chính quy' === $t_title ) {
							$is_match = true;
						} elseif ( in_array( 'vua-hoc-vua-lam', $target_training_slugs, true ) && 'vừa học vừa làm' === $t_title ) {
							$is_match = true;
						}
					}

					if ( $is_match ) {
						$menu_items[ $key ]->classes[] = 'current-menu-item';
						$menu_items[ $key ]->current   = true;
					}
				}
			}
		}
	}

	// 3. School Contextual Highlighting
	if ( $route_info['is_school'] || $route_info['is_school_archive'] ) {
		foreach ( $menu_items as $key => $item ) {
			$item_path = untrailingslashit( parse_url( $item->url, PHP_URL_PATH ) );
			$title     = mb_strtolower( trim( (string) $item->title ), 'UTF-8' );
			$is_top    = ( 0 === (int) $item->menu_item_parent );

			if ( $is_top ) {
				$school_titles = [ 'trường đối tác', 'trường đào tạo', 'trường đại học', 'danh sách trường', 'trường', 'đối tác' ];
				$school_paths  = [ '/truong-doi-tac', '/truong-dao-tao', '/truong', '/danh-sach-truong' ];
				if ( in_array( $title, $school_titles, true ) || in_array( $item_path, $school_paths, true ) ) {
					$menu_items[ $key ]->classes[] = 'current-menu-item';
					$menu_items[ $key ]->classes[] = 'current-menu-ancestor';
					$menu_items[ $key ]->current   = true;
				}
			} elseif ( $route_info['is_school'] && ! empty( $route_info['school_id'] ) ) {
				if ( (int) $item->object_id === $route_info['school_id'] ) {
					$menu_items[ $key ]->classes[] = 'current-menu-item';
					$menu_items[ $key ]->current   = true;
				}
			}
		}
	}

	// 4. Major Contextual Highlighting
	if ( $route_info['is_major'] || $route_info['is_major_archive'] ) {
		$major_top_level_ids = [];
		foreach ( $menu_items as $key => $item ) {
			$item_path = untrailingslashit( parse_url( $item->url, PHP_URL_PATH ) );
			$title     = mb_strtolower( trim( (string) $item->title ), 'UTF-8' );
			$is_top    = ( 0 === (int) $item->menu_item_parent );

			if ( $is_top ) {
				$major_titles = [ 'nhóm ngành', 'ngành học', 'chuyên ngành', 'chuyên ngành học', 'nhóm ngành đào tạo', 'ngành đào tạo', 'ngành' ];
				$major_paths  = [ '/nhom-nganh', '/nganh-hoc', '/chuyen-nganh', '/nganh' ];
				if ( in_array( $title, $major_titles, true ) || in_array( $item_path, $major_paths, true ) || in_array( 'menu-item-mega-menu', (array) $item->classes, true ) ) {
					$menu_items[ $key ]->classes[] = 'current-menu-item';
					$menu_items[ $key ]->classes[] = 'current-menu-ancestor';
					$menu_items[ $key ]->classes[] = 'current-menu-parent';
					$menu_items[ $key ]->current   = true;
					$major_top_level_ids[]         = (int) $item->ID;
				}
			}
		}

		if ( ! empty( $major_top_level_ids ) ) {
			$cat_col_ids = [];

			// Highlight Category Header
			if ( ! empty( $route_info['major_cat_slug'] ) ) {
				$target_cat_slug = $route_info['major_cat_slug'];
				foreach ( $menu_items as $key => $item ) {
					if ( in_array( (int) $item->menu_item_parent, $major_top_level_ids, true ) ) {
						$is_cat_match = false;
						$classes_arr  = (array) $item->classes;

						if ( in_array( 'menu-cat-' . $target_cat_slug, $classes_arr, true ) ) {
							$is_cat_match = true;
						} elseif ( ! empty( $item->url ) && preg_match( '#/nhom-nganh/' . preg_quote( $target_cat_slug, '#' ) . '(?:/|$)#i', $item->url ) ) {
							$is_cat_match = true;
						}

						if ( $is_cat_match ) {
							$cat_col_ids[] = (int) $item->ID;
							$menu_items[ $key ]->classes[] = 'current-menu-ancestor';
							$menu_items[ $key ]->classes[] = 'current-menu-parent';
							if ( $route_info['is_major_archive'] && ! $route_info['is_major'] ) {
								$menu_items[ $key ]->classes[] = 'current-menu-item';
								$menu_items[ $key ]->current   = true;
							}
						}
					}
				}
			}

			// Highlight Major Item (and prevent view-all button from being marked active)
			if ( $route_info['is_major'] && ! empty( $route_info['major_id'] ) ) {
				foreach ( $menu_items as $key => $item ) {
					$parent_id = (int) $item->menu_item_parent;
					if ( in_array( $parent_id, $cat_col_ids, true ) || in_array( $parent_id, $major_top_level_ids, true ) || ( ! empty( $item->object ) && 'major' === $item->object ) ) {
						if ( in_array( 'ltdh-view-all-link', (array) $item->classes, true ) || stripos( (string) $item->title, 'tất cả' ) !== false ) {
							continue;
						}

						$is_major_match = false;
						if ( (int) $item->object_id === (int) $route_info['major_id'] ) {
							$is_major_match = true;
						} else {
							$item_path = untrailingslashit( parse_url( (string) $item->url, PHP_URL_PATH ) );
							if ( ! empty( $item_path ) && $item_path === $route_info['path'] ) {
								$is_major_match = true;
							}
						}

						if ( $is_major_match ) {
							$menu_items[ $key ]->classes[] = 'current-menu-item';
							$menu_items[ $key ]->current   = true;
						}
					}
				}
			}
		}
	}

	// 5. Final class deduplication and sanitize
	foreach ( $menu_items as $key => $item ) {
		// Clean false positives from view all links
		if ( in_array( 'ltdh-view-all-link', (array) $item->classes, true ) || stripos( (string) $item->title, 'tất cả' ) !== false ) {
			$menu_items[ $key ]->classes = array_diff( (array) $menu_items[ $key ]->classes, [ 'current-menu-item', 'current-menu-ancestor', 'current_page_item', 'current-menu-parent' ] );
			$menu_items[ $key ]->current = false;
		}
		$menu_items[ $key ]->classes = array_values( array_unique( (array) $menu_items[ $key ]->classes ) );
	}

	return $menu_items;
}
add_filter( 'wp_nav_menu_objects', 'ltdh_menu_add_active_classes', 20, 2 );

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

		if ( in_array( $title, [ 'liên thông đại học', 'liên thông' ], true ) || in_array( $title, [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo', 'hình thức liên thông' ], true ) ) {
			// Check if this item ALREADY has saved children directly in WordPress Menu
			$has_saved_training_children = false;
			foreach ( $sorted_menu_items as $chk_training ) {
				if ( (int) $chk_training->menu_item_parent === (int) $item->ID ) {
					$has_saved_training_children = true;
					break;
				}
			}

			if ( ! $has_saved_training_children ) {
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
		}

		$is_top_level  = ( (int) ( $item->menu_item_parent ?? 0 ) === 0 );
		$item_path     = ! empty( $item->url ) ? rtrim( (string) parse_url( $item->url, PHP_URL_PATH ), '/' ) : '';
		$explicit_mega = function_exists( 'get_post_meta' ) ? get_post_meta( $item->ID, '_menu_item_is_mega_menu', true ) : '';

		if ( '0' === $explicit_mega ) {
			// Explicitly turned off by admin: Never treat as mega menu
			$is_major_menu = false;
		} elseif ( '1' === $explicit_mega ) {
			// Explicitly turned on by admin
			$is_major_menu = $is_top_level;
		} else {
			// Default / Unset: Auto-detect ONLY for academic major categories or majors
			$is_major_menu = $is_top_level && (
				in_array( $title, [ 'chuyên ngành', 'ngành học', 'chuyên ngành học', 'nhóm ngành', 'nhóm ngành đào tạo', 'ngành đào tạo' ], true )
				|| in_array( $item_path, [ '/nganh-hoc', '/nhom-nganh' ], true )
				|| in_array( 'menu-item-mega-menu', (array) $item->classes, true )
			);

			// Auto-detect by child items: STRICTLY if any direct child has object major_cat or major
			if ( ! $is_major_menu && $is_top_level ) {
				foreach ( $sorted_menu_items as $sub_chk ) {
					if ( (int) $sub_chk->menu_item_parent === (int) $item->ID ) {
						if ( isset( $sub_chk->object ) && in_array( $sub_chk->object, [ LTDH_TAX_MAJOR_CAT, LTDH_CPT_MAJOR, 'major_cat', 'major' ], true ) ) {
							$is_major_menu = true;
							break;
						}
					}
				}
			}
		}

		if ( $is_major_menu ) {
			if ( ! in_array( 'menu-item-has-children', $item->classes, true ) ) {
				$item->classes[] = 'menu-item-has-children';
			}
			if ( ! in_array( 'menu-item-mega-menu', $item->classes, true ) ) {
				$item->classes[] = 'menu-item-mega-menu';
			}

			// Check if this menu item already has saved children directly in WordPress Menu
			$has_saved_children = false;
			foreach ( $sorted_menu_items as $chk ) {
				if ( (int) $chk->menu_item_parent === (int) $item->ID ) {
					$has_saved_children = true;
					break;
				}
			}

			if ( $has_saved_children ) {
				// Admin-managed Mode: The user has arranged child items in Appearance -> Menus.
				// Respect all saved items, enrich their classes, and DO NOT wipe them.
				$saved_cat_ids = [];
				foreach ( $sorted_menu_items as $chk_cat ) {
					if ( (int) $chk_cat->menu_item_parent === (int) $item->ID ) {
						$saved_cat_ids[] = (int) $chk_cat->ID;

						$is_footer = ( function_exists( 'get_post_meta' ) && get_post_meta( $chk_cat->ID, '_menu_item_is_footer_bar', true ) === '1' )
							|| stripos( $chk_cat->title, 'tất cả' ) !== false;

						if ( $is_footer ) {
							if ( ! in_array( 'ltdh-view-all-link', $chk_cat->classes, true ) ) {
								$chk_cat->classes[] = 'ltdh-view-all-link';
							}
						} else {
							if ( ! in_array( 'menu-item-category', $chk_cat->classes, true ) ) {
								$chk_cat->classes[] = 'menu-item-category';
							}
							if ( ! in_array( 'menu-item-has-children', $chk_cat->classes, true ) ) {
								$chk_cat->classes[] = 'menu-item-has-children';
							}

							$cat_slug = '';
							if ( function_exists( 'get_post_meta' ) ) {
								$cat_slug = get_post_meta( $chk_cat->ID, '_menu_item_mega_icon', true );
							}
							if ( empty( $cat_slug ) && $chk_cat->object === LTDH_TAX_MAJOR_CAT && ! empty( $chk_cat->object_id ) ) {
								$term = function_exists( 'get_term' ) ? get_term( (int) $chk_cat->object_id, LTDH_TAX_MAJOR_CAT ) : null;
								if ( $term && ! is_wp_error( $term ) ) {
									$cat_slug = $term->slug;
								}
							}
							if ( empty( $cat_slug ) ) {
								$cat_slug = function_exists( 'sanitize_title' ) ? sanitize_title( $chk_cat->title ) : preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $chk_cat->title ) );
							}
							if ( ! empty( $cat_slug ) ) {
								$cat_class = 'menu-cat-' . sanitize_html_class( $cat_slug );
								if ( ! in_array( $cat_class, $chk_cat->classes, true ) ) {
									$chk_cat->classes[] = $cat_class;
								}
							}
						}
					}
				}

				// Grandchildren: Sub-majors inside each category column
				foreach ( $sorted_menu_items as $chk_maj ) {
					if ( in_array( (int) $chk_maj->menu_item_parent, $saved_cat_ids, true ) ) {
						if ( ! in_array( 'menu-item-object-major', $chk_maj->classes, true ) ) {
							$chk_maj->classes[] = 'menu-item-object-major';
						}

						$is_hot = ( function_exists( 'get_post_meta' ) && get_post_meta( $chk_maj->ID, '_menu_item_is_hot', true ) === '1' );
						if ( ! $is_hot && $chk_maj->object === LTDH_CPT_MAJOR && ! empty( $chk_maj->object_id ) && function_exists( 'get_post_meta' ) ) {
							$is_hot = ( get_post_meta( (int) $chk_maj->object_id, '_is_hot', true ) === '1' );
						}
						if ( $is_hot && ! in_array( 'is-hot-major', $chk_maj->classes, true ) ) {
							$chk_maj->classes[] = 'is-hot-major';
						}
					}
				}

				continue;
			}

			// Dynamic Fallback Mode: When no children exist in WP Menu, generate dynamically from DB
			$filtered_items = [];
			foreach ($new_items as $ni) {
				if ((int) $ni->menu_item_parent === (int) $item->ID) {
					continue;
				}
				$filtered_items[] = $ni;
			}
			$new_items = $filtered_items;

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
			$view_all->url = home_url('/nhom-nganh/');
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

	// Enforce UI/UX rule: Each category column displays at most 5 majors; if > 5, append "Xem tất cả"
	$cat_items_map = [];
	foreach ( $new_items as $it ) {
		if ( in_array( 'menu-item-category', (array) $it->classes, true ) ) {
			$cat_items_map[ (int) $it->ID ] = $it;
		}
	}

	if ( ! empty( $cat_items_map ) ) {
		$cat_majors_total = [];
		foreach ( $new_items as $it ) {
			$p_id = (int) $it->menu_item_parent;
			if ( isset( $cat_items_map[ $p_id ] ) && in_array( 'menu-item-object-major', (array) $it->classes, true ) ) {
				$cat_majors_total[ $p_id ] = ( $cat_majors_total[ $p_id ] ?? 0 ) + 1;
			}
		}

		$final_items    = [];
		$cat_seen_count = [];

		foreach ( $new_items as $it ) {
			$p_id = (int) $it->menu_item_parent;

			if ( isset( $cat_items_map[ $p_id ] ) && in_array( 'menu-item-object-major', (array) $it->classes, true ) ) {
				if ( ! isset( $cat_seen_count[ $p_id ] ) ) {
					$cat_seen_count[ $p_id ] = 0;
				}
				$cat_seen_count[ $p_id ]++;

				if ( $cat_seen_count[ $p_id ] <= 5 ) {
					$final_items[] = $it;
				}

				if ( $cat_seen_count[ $p_id ] === 5 && ( $cat_majors_total[ $p_id ] ?? 0 ) > 5 ) {
					$cat_obj = $cat_items_map[ $p_id ];
					$max_db_id++;
					$more_item                        = new stdClass();
					$more_item->ID                    = $max_db_id;
					$more_item->db_id                 = $max_db_id;
					$more_item->title                 = 'Xem tất cả';
					$more_item->url                   = $cat_obj->url;
					$more_item->menu_item_parent      = $p_id;
					$more_item->classes               = [
						'menu-item',
						'menu-item-cat-more',
						'ltdh-cat-more-link',
					];
					$more_item->type                  = 'custom';
					$more_item->object                = '';
					$more_item->object_id             = 0;
					$more_item->post_parent           = 0;
					$more_item->post_title            = 'Xem tất cả';
					$more_item->post_status           = 'publish';
					$more_item->post_type             = 'nav_menu_item';
					$more_item->menu_order            = 0;
					$more_item->target                = '';
					$more_item->attr_title            = 'Xem tất cả ngành thuộc nhóm ' . esc_attr( $cat_obj->title );
					$more_item->description           = '';
					$more_item->xfn                   = '';
					$more_item->current               = false;
					$more_item->current_item_parent   = false;
					$more_item->current_item_ancestor = false;
					$final_items[]                    = $more_item;
				}

				continue;
			}

			if ( in_array( 'menu-item-cat-more', (array) $it->classes, true ) ) {
				continue;
			}

			$final_items[] = $it;
		}

		$new_items = $final_items;
	}

	return $new_items;
}
add_filter('wp_nav_menu_objects', 'ltdh_dynamic_menu_submenu_injection', 10, 2);

// ----------------------------------------------------
// 4. Highlight Menu for Training Type Pages
// ----------------------------------------------------

function ltdh_highlight_menu_parameters( $classes, $item, $args = null, $depth = 0 ) {
	$current_url = home_url( $_SERVER['REQUEST_URI'] ?? '/' );
	$item_url    = $item->url ?? '';
	if ( ! $item_url ) {
		return $classes;
	}

	$route_info = function_exists( 'ltdh_get_current_route_info' ) ? ltdh_get_current_route_info() : [];

	// 1. Mobile Menu accordion auto-open on active ancestry
	if ( $args && isset( $args->menu_class ) && strpos( $args->menu_class, 'nav-mobile-menu' ) !== false ) {
		if ( in_array( 'current-menu-ancestor', $classes, true ) || in_array( 'current-menu-item', $classes, true ) ) {
			if ( in_array( 'menu-item-has-children', $classes, true ) ) {
				$classes[] = 'is-open';
			}
		}
	}

	// 2. Training Type Query Parameter & Path Highlighting
	$current_parts = wp_parse_url( $current_url );
	$item_parts    = wp_parse_url( $item_url );

	if ( isset( $item_parts['path'], $current_parts['path'] ) ) {
		if ( preg_match( '#/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $item_parts['path'], $item_match ) && 'page' !== $item_match[1] ) {
			if ( preg_match( '#/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $current_parts['path'], $current_match ) && 'page' !== $current_match[1] ) {
				if ( $item_match[1] === $current_match[1] ) {
					$classes[] = 'current-menu-item';
				}
			}
		}
	}

	if ( isset( $item_parts['query'] ) ) {
		parse_str( $item_parts['query'], $item_query );
		if ( isset( $item_query['he'] ) ) {
			$current_he = '';
			if ( ! empty( $route_info['training_type_slug'] ) ) {
				$current_he = $route_info['training_type_slug'];
			} elseif ( isset( $current_parts['path'] ) && preg_match( '#/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $current_parts['path'], $m ) && 'page' !== $m[1] ) {
				$current_he = $m[1];
			} elseif ( isset( $current_parts['query'] ) ) {
				parse_str( $current_parts['query'], $current_query );
				$current_he = $current_query['he'] ?? '';
			}

			if ( $current_he ) {
				$matching_he = [ $current_he ];
				if ( in_array( $current_he, [ 'dao-tao-tu-xa', 'tu-xa' ], true ) ) {
					$matching_he = [ 'dao-tao-tu-xa', 'tu-xa' ];
				}
				if ( in_array( $item_query['he'], $matching_he, true ) ) {
					$classes[] = 'current-menu-item';
				}
			}
		}
	}

	// 3. Prevent 'Xem tất cả' button from ever having current-menu-item on single pages
	if ( in_array( 'ltdh-view-all-link', $classes, true ) || ( isset( $item->title ) && stripos( $item->title, 'tất cả' ) !== false ) ) {
		if ( ! empty( $route_info['is_major'] ) || ! empty( $route_info['is_program'] ) || ! empty( $route_info['is_school'] ) ) {
			$classes = array_diff( $classes, [ 'current-menu-item', 'current-menu-ancestor', 'current_page_item', 'current-menu-parent' ] );
		}
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'nav_menu_css_class', 'ltdh_highlight_menu_parameters', 10, 4 );

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
		$is_open    = in_array( 'is-open', (array) $item->classes, true ) || in_array( 'current-menu-ancestor', (array) $item->classes, true );
		$toggle_btn = '<button type="button" class="mobile-submenu-toggle" aria-expanded="' . ( $is_open ? 'true' : 'false' ) . '" aria-label="' . esc_attr__( 'Mở rộng menu con', 'ltdh' ) . '" onclick="ltdhToggleSubmenu(this, event)">'
			. '<svg class="w-4 h-4 text-slate-500 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">'
			. '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />'
			. '</svg>'
			. '</button>';

		return '<div class="mobile-menu-item-row">' . $item_output . $toggle_btn . '</div>';
	}

	return '<div class="mobile-menu-item-row">' . $item_output . '</div>';
}
add_filter( 'walker_nav_menu_start_el', 'ltdh_mobile_nav_menu_start_el', 10, 4 );

/**
 * Append "Liên hệ" menu item specifically to the mobile navigation drawer menu.
 *
 * @param string   $items The HTML list content for the menu items.
 * @param stdClass $args  An object containing wp_nav_menu() arguments.
 * @return string
 */
function ltdh_append_mobile_contact_menu_item( $items, $args ) {
	if ( isset( $args->menu_class ) && strpos( $args->menu_class, 'nav-mobile-menu' ) !== false ) {
		$contact_url  = home_url( '/lien-he/' );
		$contact_path = '/lien-he';
		$current_path = untrailingslashit( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) );
		$is_active    = ( $current_path === $contact_path );
		$active_class = $is_active ? ' current-menu-item' : '';

		if ( strpos( $items, 'href="' . esc_url( $contact_url ) . '"' ) === false && strpos( $items, '/lien-he/' ) === false ) {
			$items .= '<li class="menu-item menu-item-type-custom menu-item-object-custom' . esc_attr( $active_class ) . '">';
			$items .= '<div class="mobile-menu-item-row">';
			$items .= '<a href="' . esc_url( $contact_url ) . '">Liên hệ</a>';
			$items .= '</div>';
			$items .= '</li>';
		}
	}
	return $items;
}
add_filter( 'wp_nav_menu_items', 'ltdh_append_mobile_contact_menu_item', 20, 2 );

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
		$cat_url   = ( ! is_wp_error( $term_link ) && ! empty( $term_link ) ) ? $term_link : home_url( '/nhom-nganh/' . $cat->slug . '/' );

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
			'url'     => home_url( '/nhom-nganh/' ),
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
		if ( function_exists( 'get_post_meta' ) ) {
			$meta_icon = get_post_meta( $item->ID, '_menu_item_mega_icon', true );
			if ( ! empty( $meta_icon ) ) {
				$cat_slug = $meta_icon;
			}
		}
		if ( empty( $cat_slug ) ) {
			foreach ( $classes as $c ) {
				if ( strpos( $c, 'menu-cat-' ) === 0 ) {
					$cat_slug = substr( $c, 9 );
					break;
				}
			}
		}
		$icon  = ( $cat_slug === 'none' ) ? '' : ltdh_get_major_cat_icon( $cat_slug );
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
			. '</a>';
	}

	if ( $is_desktop && ( in_array( 'menu-item-cat-more', $classes, true ) || in_array( 'ltdh-cat-more-link', $classes, true ) ) && $depth === 2 ) {
		return '<a href="' . esc_url( $item->url ) . '" class="mega-cat-more-link" title="' . esc_attr( $item->attr_title ?: ( 'Xem tất cả ' . $item->title ) ) . '">'
			. '<span class="mega-cat-more-text">' . esc_html( $item->title ?: 'Xem tất cả' ) . '</span>'
			. '<svg class="w-3.5 h-3.5 mega-cat-more-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">'
			. '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>'
			. '</svg>'
			. '</a>';
	}

	if ( $is_desktop && in_array( 'ltdh-view-all-link', $classes, true ) && $depth === 1 ) {
		$cta_title = ! empty( $item->title ) ? $item->title : 'Xem tất cả ngành học';
		return '<div class="mega-footer-bar">'
			. '<div class="mega-footer-info">'
			. '<span class="mega-footer-tag">Tuyển sinh liên thông</span>'
			. '<span class="mega-footer-desc">Đầy đủ hệ: Chính quy, Từ xa &amp; Vừa học vừa làm</span>'
			. '</div>'
			. '<a href="' . esc_url( $item->url ) . '" class="mega-footer-btn">'
			. '<span>' . esc_html( $cta_title ) . '</span>'
			. '<svg class="w-3.5 h-3.5 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>'
			. '</a>'
			. '</div>';
	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'ltdh_mega_menu_nav_menu_start_el', 9, 4 );

// ----------------------------------------------------
// 9. WordPress Admin Mega Menu Custom Fields & Tools
// ----------------------------------------------------

/**
 * Ensure Major CPT and Major Cat taxonomy metaboxes are visible by default in Appearance -> Menus.
 */
if ( function_exists( 'add_filter' ) ) {
	add_filter( 'default_hidden_meta_boxes', function( $hidden, $screen ) {
		if ( $screen && $screen->id === 'nav-menus' ) {
			$hidden = array_diff( (array) $hidden, [ 'add-post-type-major', 'add-major_cat' ] );
		}
		return $hidden;
	}, 10, 2 );

	add_filter( 'get_user_option_metaboxhidden_nav-menus', function( $result ) {
		if ( is_array( $result ) ) {
			$result = array_diff( $result, [ 'add-post-type-major', 'add-major_cat' ] );
		}
		return $result;
	}, 10 );
}

/**
 * Render custom fields inside each nav menu item card in Appearance -> Menus.
 */
function ltdh_menu_item_custom_fields( $item_id, $item, $depth, $args ) {
	$is_mega_menu  = function_exists( 'get_post_meta' ) ? get_post_meta( $item_id, '_menu_item_is_mega_menu', true ) : '';
	$is_hot        = function_exists( 'get_post_meta' ) ? get_post_meta( $item_id, '_menu_item_is_hot', true ) : '';
	$is_cat_header = function_exists( 'get_post_meta' ) ? get_post_meta( $item_id, '_menu_item_is_cat_header', true ) : '';
	$mega_icon     = function_exists( 'get_post_meta' ) ? get_post_meta( $item_id, '_menu_item_mega_icon', true ) : '';
	$is_footer_bar = function_exists( 'get_post_meta' ) ? get_post_meta( $item_id, '_menu_item_is_footer_bar', true ) : '';

	// Auto detect mega menu for /nganh-hoc/, /nhom-nganh/ or 'chuyên ngành', 'nhóm ngành' if unset
	if ( '' === $is_mega_menu && 0 === (int) $depth ) {
		$title = mb_strtolower( trim( $item->title ?? '' ), 'UTF-8' );
		$url   = (string) ( $item->url ?? '' );
		if ( in_array( $title, [ 'chuyên ngành', 'ngành học', 'nhóm ngành', 'nhóm ngành đào tạo', 'ngành đào tạo' ], true ) || false !== strpos( $url, '/nganh-hoc' ) || false !== strpos( $url, '/nhom-nganh' ) ) {
			$is_mega_menu = '1';
		}
	}
	?>
	<div class="ltdh-mega-menu-fields" style="margin: 14px 0 6px; padding: 12px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
		<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; font-weight: 700; color: #0f172a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
			<span style="display:inline-block; width: 6px; height: 14px; background: #0284c7; border-radius: 2px;"></span>
			Cấu hình Mega Menu Liên Thông
		</div>

		<p class="description description-wide" style="margin: 4px 0 8px;">
			<label for="edit-menu-item-is-mega-menu-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-is-mega-menu-<?php echo esc_attr( $item_id ); ?>" 
				       name="menu_item_is_mega_menu[<?php echo esc_attr( $item_id ); ?>]" 
				       value="1" <?php checked( $is_mega_menu, '1' ); ?> />
				<strong style="color: #0369a1;">Kích hoạt Mega Menu đa cột</strong> 
				<span class="description" style="display: block; color: #64748b; font-size: 11px; margin-left: 20px;">
					(Bật cho menu cha như "Chuyên ngành", "Ngành học". Các menu con cấp 1 sẽ thành cột Nhóm ngành, cấp 2 thành Ngành con).
				</span>
			</label>
		</p>

		<p class="description description-wide" style="margin: 4px 0 8px;">
			<label for="edit-menu-item-is-cat-header-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-is-cat-header-<?php echo esc_attr( $item_id ); ?>" 
				       name="menu_item_is_cat_header[<?php echo esc_attr( $item_id ); ?>]" 
				       value="1" <?php checked( $is_cat_header, '1' ); ?> />
				<strong>Tiêu đề cột nhóm ngành</strong>
				<span class="description" style="display: block; color: #64748b; font-size: 11px; margin-left: 20px;">
					(Đánh dấu menu này làm tiêu đề của một cột nhóm ngành bên trong Mega Menu).
				</span>
			</label>
		</p>

		<p class="description description-wide" style="margin: 4px 0 8px;">
			<label for="edit-menu-item-mega-icon-<?php echo esc_attr( $item_id ); ?>" style="font-weight: 600; font-size: 12px; color: #334155;">
				Biểu tượng Icon nhóm ngành:
				<select id="edit-menu-item-mega-icon-<?php echo esc_attr( $item_id ); ?>" 
				        name="menu_item_mega_icon[<?php echo esc_attr( $item_id ); ?>]" 
				        style="display: block; width: 100%; margin-top: 4px; font-weight: normal;">
					<option value="" <?php selected( $mega_icon, '' ); ?>>— Tự động theo danh mục (Mặc định) —</option>
					<option value="kinh-te-quan-ly" <?php selected( $mega_icon, 'kinh-te-quan-ly' ); ?>>📊 Kinh tế & Quản lý</option>
					<option value="ky-thuat-cong-nghe" <?php selected( $mega_icon, 'ky-thuat-cong-nghe' ); ?>>💻 Kỹ thuật & Công nghệ</option>
					<option value="xa-hoi-dich-vu" <?php selected( $mega_icon, 'xa-hoi-dich-vu' ); ?>>📦 Xã hội & Dịch vụ</option>
					<option value="ngon-ngu-nhan-van" <?php selected( $mega_icon, 'ngon-ngu-nhan-van' ); ?>>🌍 Ngôn ngữ & Nhân văn</option>
					<option value="nong-lam-moi-truong" <?php selected( $mega_icon, 'nong-lam-moi-truong' ); ?>>🌿 Nông lâm & Môi trường</option>
					<option value="none" <?php selected( $mega_icon, 'none' ); ?>>🚫 Không hiển thị icon</option>
				</select>
			</label>
		</p>

		<p class="description description-wide" style="margin: 4px 0 8px;">
			<label for="edit-menu-item-is-hot-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-is-hot-<?php echo esc_attr( $item_id ); ?>" 
				       name="menu_item_is_hot[<?php echo esc_attr( $item_id ); ?>]" 
				       value="1" <?php checked( $is_hot, '1' ); ?> />
				<span style="display: inline-block; padding: 1px 6px; background: #ea580c; color: #fff; font-size: 10px; font-weight: 800; border-radius: 4px; vertical-align: middle;">HOT</span>
				<strong>Đánh dấu ngành nổi bật</strong>
			</label>
		</p>

		<p class="description description-wide" style="margin: 4px 0 0;">
			<label for="edit-menu-item-is-footer-bar-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-is-footer-bar-<?php echo esc_attr( $item_id ); ?>" 
				       name="menu_item_is_footer_bar[<?php echo esc_attr( $item_id ); ?>]" 
				       value="1" <?php checked( $is_footer_bar, '1' ); ?> />
				<strong>Thanh Footer Bar chân Mega Menu (Full-width CTA)</strong>
				<span class="description" style="display: block; color: #64748b; font-size: 11px; margin-left: 20px;">
					(Ví dụ link "Xem tất cả ngành học →" sẽ trải dài toàn bộ đáy bảng Mega Menu).
				</span>
			</label>
		</p>
	</div>
	<?php
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'wp_nav_menu_item_custom_fields', 'ltdh_menu_item_custom_fields', 10, 4 );
}

/**
 * Save custom fields when nav menu item is updated.
 */
function ltdh_save_menu_item_custom_fields( $menu_id, $menu_item_db_id, $args ) {
	if ( ! function_exists( 'update_post_meta' ) ) {
		return;
	}

	if ( isset( $_POST['menu_item_is_mega_menu'][ $menu_item_db_id ] ) ) {
		update_post_meta( $menu_item_db_id, '_menu_item_is_mega_menu', '1' );
	} else {
		update_post_meta( $menu_item_db_id, '_menu_item_is_mega_menu', '0' );
	}

	if ( isset( $_POST['menu_item_is_cat_header'][ $menu_item_db_id ] ) ) {
		update_post_meta( $menu_item_db_id, '_menu_item_is_cat_header', '1' );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_is_cat_header' );
	}

	if ( isset( $_POST['menu_item_mega_icon'][ $menu_item_db_id ] ) ) {
		$icon_val = sanitize_text_field( $_POST['menu_item_mega_icon'][ $menu_item_db_id ] );
		update_post_meta( $menu_item_db_id, '_menu_item_mega_icon', $icon_val );
	}

	if ( isset( $_POST['menu_item_is_hot'][ $menu_item_db_id ] ) ) {
		update_post_meta( $menu_item_db_id, '_menu_item_is_hot', '1' );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_is_hot' );
	}

	if ( isset( $_POST['menu_item_is_footer_bar'][ $menu_item_db_id ] ) ) {
		update_post_meta( $menu_item_db_id, '_menu_item_is_footer_bar', '1' );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_is_footer_bar' );
	}

	if ( function_exists( 'ltdh_clear_mega_menu_cache' ) ) {
		ltdh_clear_mega_menu_cache();
	}
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'wp_update_nav_menu_item', 'ltdh_save_menu_item_custom_fields', 10, 3 );
}

/**
 * Register a helper metabox in Appearance -> Menus for one-click Mega Menu sync.
 */
function ltdh_register_mega_menu_nav_metabox() {
	if ( function_exists( 'add_meta_box' ) ) {
		add_meta_box(
			'ltdh_mega_menu_sync_box',
			'⚡ Đồng bộ Mega Menu Chuyên Ngành',
			'ltdh_render_mega_menu_nav_metabox',
			'nav-menus',
			'side',
			'high'
		);
	}
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'admin_init', 'ltdh_register_mega_menu_nav_metabox' );
}

function ltdh_render_mega_menu_nav_metabox() {
	?>
	<div style="padding: 6px 0;">
		<p style="font-size: 13px; color: #475569; margin: 0 0 10px; line-height: 1.5;">
			Bạn có thể trực tiếp kéo thả, sắp xếp hoặc đổi tên các <strong>Nhóm ngành</strong> và <strong>Ngành học con</strong> ngay trong menu.
		</p>
		<p style="font-size: 12px; color: #64748b; margin: 0 0 12px; line-height: 1.4;">
			Nhấn nút bên dưới để <strong>tự động nạp toàn bộ cấu trúc Nhóm ngành &amp; Ngành nhỏ</strong> hiện có trong cơ sở dữ liệu vào menu:
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ltdh_sync_mega_menu', 'ltdh_sync_nonce' ); ?>
			<input type="hidden" name="action" value="ltdh_sync_mega_menu_action">
			<input type="hidden" name="nav_menu_id" value="<?php echo esc_attr( isset( $_GET['menu'] ) ? (int) $_GET['menu'] : 0 ); ?>">
			<button type="submit" class="button button-primary" style="width: 100%; height: auto; padding: 9px 12px; font-weight: 700; text-align: center; font-size: 13px;">
				🔄 Nạp tự động vào Menu
			</button>
		</form>
	</div>
	<?php
}

/**
 * Handle admin-post action for one-click Mega Menu sync.
 */
function ltdh_handle_sync_mega_menu_action() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! check_admin_referer( 'ltdh_sync_mega_menu', 'ltdh_sync_nonce' ) ) {
		wp_die( 'Unauthorized' );
	}

	$menu_id = isset( $_POST['nav_menu_id'] ) ? (int) $_POST['nav_menu_id'] : 0;
	if ( ! $menu_id ) {
		$locations = get_nav_menu_locations();
		$menu_id   = $locations['primary-menu'] ?? 0;
	}

	if ( $menu_id ) {
		ltdh_sync_mega_menu_items_to_nav_menu( $menu_id );
	}

	wp_safe_redirect( add_query_arg( [ 'menu' => $menu_id, 'ltdh_synced' => '1' ], admin_url( 'nav-menus.php' ) ) );
	exit;
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'admin_post_ltdh_sync_mega_menu_action', 'ltdh_handle_sync_mega_menu_action' );
}

/**
 * Admin notice after sync.
 */
function ltdh_mega_menu_admin_notice() {
	if ( isset( $_GET['ltdh_synced'] ) && '1' === $_GET['ltdh_synced'] ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Thành công!</strong> Toàn bộ cấu trúc Nhóm ngành và Chuyên ngành đã được nạp vào menu. Bạn có thể kéo thả để sắp xếp lại theo ý muốn và nhấn <strong>Lưu menu</strong>.</p>
		</div>
		<?php
	}
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'admin_notices', 'ltdh_mega_menu_admin_notice' );
}

/**
 * Populate or synchronize categories & majors into a specified WP nav menu under the Mega Menu parent.
 */
function ltdh_sync_mega_menu_items_to_nav_menu( int $menu_id ): bool {
	if ( ! function_exists( 'wp_get_nav_menu_items' ) || ! function_exists( 'wp_update_nav_menu_item' ) ) {
		return false;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! $items ) {
		return false;
	}

	$parent_item = null;
	foreach ( $items as $it ) {
		if ( (int) $it->menu_item_parent === 0 ) {
			$title = mb_strtolower( trim( $it->title ), 'UTF-8' );
			if ( in_array( $title, [ 'chuyên ngành', 'ngành học', 'chuyên ngành học', 'nhóm ngành', 'nhóm ngành đào tạo', 'ngành đào tạo' ], true )
				|| ( ! empty( $it->url ) && ( false !== strpos( $it->url, '/nganh-hoc' ) || false !== strpos( $it->url, '/nhom-nganh' ) ) )
				|| ( function_exists( 'get_post_meta' ) && get_post_meta( $it->ID, '_menu_item_is_mega_menu', true ) === '1' )
			) {
				$parent_item = $it;
				break;
			}
		}
	}

	if ( ! $parent_item ) {
		return false;
	}

	// Remove existing children of this parent item to prevent duplication on re-sync
	foreach ( $items as $it ) {
		if ( (int) $it->menu_item_parent === (int) $parent_item->ID ) {
			foreach ( $items as $sub_it ) {
				if ( (int) $sub_it->menu_item_parent === (int) $it->ID ) {
					wp_delete_post( $sub_it->ID, true );
				}
			}
			wp_delete_post( $it->ID, true );
		}
	}

	// Set parent item meta
	update_post_meta( $parent_item->ID, '_menu_item_is_mega_menu', '1' );

	$mega_data = ltdh_get_mega_menu_data();
	if ( empty( $mega_data ) ) {
		return false;
	}

	$order = (int) $parent_item->menu_order;

	foreach ( $mega_data as $cat_slug => $cat ) {
		$order++;
		$cat_args = [
			'menu-item-title'     => $cat['name'],
			'menu-item-parent-id' => $parent_item->ID,
			'menu-item-position'  => $order,
			'menu-item-status'    => 'publish',
		];

		if ( ! empty( $cat['term_id'] ) ) {
			$cat_args['menu-item-type']      = 'taxonomy';
			$cat_args['menu-item-object']    = LTDH_TAX_MAJOR_CAT;
			$cat_args['menu-item-object-id'] = (int) $cat['term_id'];
		} else {
			$cat_args['menu-item-type'] = 'custom';
			$cat_args['menu-item-url']  = $cat['url'];
		}

		$cat_item_id = wp_update_nav_menu_item( $menu_id, 0, $cat_args );
		if ( ! is_wp_error( $cat_item_id ) && $cat_item_id ) {
			update_post_meta( $cat_item_id, '_menu_item_is_cat_header', '1' );
			update_post_meta( $cat_item_id, '_menu_item_mega_icon', $cat_slug );

			if ( ! empty( $cat['majors'] ) ) {
				foreach ( $cat['majors'] as $maj ) {
					$order++;
					$maj_args = [
						'menu-item-title'     => $maj['title'],
						'menu-item-parent-id' => $cat_item_id,
						'menu-item-position'  => $order,
						'menu-item-type'      => 'post_type',
						'menu-item-object'    => LTDH_CPT_MAJOR,
						'menu-item-object-id' => (int) $maj['ID'],
						'menu-item-status'    => 'publish',
					];
					$maj_item_id = wp_update_nav_menu_item( $menu_id, 0, $maj_args );
					if ( ! is_wp_error( $maj_item_id ) && $maj_item_id ) {
						if ( ! empty( $maj['is_hot'] ) ) {
							update_post_meta( $maj_item_id, '_menu_item_is_hot', '1' );
						}
					}
				}
			}
		}
	}

	// Add Footer CTA
	$order++;
	$footer_args = [
		'menu-item-title'     => 'Xem tất cả ngành học →',
		'menu-item-url'       => home_url( '/nhom-nganh/' ),
		'menu-item-type'      => 'custom',
		'menu-item-parent-id' => $parent_item->ID,
		'menu-item-position'  => $order,
		'menu-item-status'    => 'publish',
	];
	$footer_item_id = wp_update_nav_menu_item( $menu_id, 0, $footer_args );
	if ( ! is_wp_error( $footer_item_id ) && $footer_item_id ) {
		update_post_meta( $footer_item_id, '_menu_item_is_footer_bar', '1' );
	}

	// Renumber remaining items that were after parent_item so the menu order is cleanly preserved
	foreach ( $items as $it ) {
		if ( (int) $it->menu_item_parent === 0 && (int) $it->ID !== (int) $parent_item->ID && (int) $it->menu_order >= (int) $parent_item->menu_order ) {
			$order++;
			wp_update_post( [ 'ID' => $it->ID, 'menu_order' => $order ] );
		}
	}

	ltdh_clear_mega_menu_cache();
	return true;
}


