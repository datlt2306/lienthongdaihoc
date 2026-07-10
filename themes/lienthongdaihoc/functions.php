<?php
/**
 * Theme functions and definitions
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Theme Configuration & Core Initializations
// ----------------------------------------------------

// Setup theme support
add_action( 'after_setup_theme', 'ltdh_theme_setup' );
function ltdh_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	
	// Rank Math breadcrumbs
	add_theme_support( 'rank-math-breadcrumbs' );

	// Register navigation menus
	register_nav_menus( [
		'primary-menu' => 'Header Navigation Menu',
		'footer-menu'  => 'Footer Navigation Menu',
	] );
}

/**
 * Output Rank Math breadcrumb with consistent styling
 */
function ltdh_breadcrumb() {
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<div class="ltdh-breadcrumb max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-slate-400">';
		rank_math_the_breadcrumbs();
		echo '</div>';
	}
}

// Enqueue styles and scripts
add_action( 'wp_enqueue_scripts', 'ltdh_enqueue_assets' );
function ltdh_enqueue_assets() {
	// Standard Stylesheets
	wp_enqueue_style( 'ltdh-main-style', get_template_directory_uri() . '/style.css', [], '1.0.0' );
	
	if ( file_exists( get_template_directory() . '/assets/css/main.min.css' ) ) {
		wp_enqueue_style( 'ltdh-theme-styles', get_template_directory_uri() . '/assets/css/main.min.css', [], '1.0.0' );
	}
	
	// Vanilla JS Bundle
	$script_handle = 'ltdh-fallback-js';
	if ( file_exists( get_template_directory() . '/assets/js/main.bundle.js' ) ) {
		wp_enqueue_script( 'ltdh-theme-js', get_template_directory_uri() . '/assets/js/main.bundle.js', [], '1.0.0', true );
		$script_handle = 'ltdh-theme-js';
	} else {
		// Fallback vanilla script for dev
		wp_enqueue_script( 'ltdh-fallback-js', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true );
	}

	wp_localize_script( $script_handle, 'ltdh_ajax', [
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
		'compare_nonce' => wp_create_nonce( 'ltdh_compare_nonce' ),
	] );

	// Compare JS
	wp_enqueue_script( 'ltdh-compare-js', get_template_directory_uri() . '/assets/js/compare.js', [], '1.0.0', true );
}

/**
 * Output the floating compare tray on all frontend pages.
 */
add_action( 'wp_footer', 'ltdh_compare_output_tray' );
function ltdh_compare_output_tray() {
	if ( is_admin() ) {
		return;
	}
	get_template_part( 'template-parts/compare/tray' );
}

// ----------------------------------------------------
// 2. Performance Query Caching Helpers
// ----------------------------------------------------
/**
 * Cached WP_Query wrapper using WordPress Transients
 */
function ltdh_get_cached_query( $transient_key, $query_args, $expiration = HOUR_IN_SECONDS ) {
	$cached_results = get_transient( $transient_key );
	if ( false !== $cached_results ) {
		return $cached_results;
	}

	$query = new WP_Query( $query_args );
	set_transient( $transient_key, $query, $expiration );

	return $query;
}

// Clear transients on publish/edit to ensure freshness
add_action( 'save_post', 'ltdh_clear_transients_on_save' );
function ltdh_clear_transients_on_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	delete_transient( 'ltdh_featured_schools' );
}

/**
 * Resolve school image attachment ID (ACF logo → featured image).
 */
function ltdh_get_school_image_id( $school_id ) {
	$logo_id = get_field( 'logo', $school_id );
	if ( $logo_id ) {
		return (int) $logo_id;
	}

	if ( has_post_thumbnail( $school_id ) ) {
		return (int) get_post_thumbnail_id( $school_id );
	}

	return 0;
}

/**
 * Render school thumbnail with UNI fallback.
 */
function ltdh_render_school_thumbnail( $school_id, $size = 'thumbnail', $classes = 'h-14 w-14 object-cover border border-slate-100 bg-white rounded-lg' ) {
	$image_id = ltdh_get_school_image_id( $school_id );

	if ( $image_id ) {
		echo wp_get_attachment_image( $image_id, $size, false, [
			'class'   => $classes,
			'loading' => 'lazy',
			'alt'     => sprintf( 'Logo %s', get_the_title( $school_id ) ),
		] );
		return;
	}

	$fallback_classes = preg_replace( '/\bobject-(cover|contain)\b/', '', $classes );
	$fallback_classes = trim( preg_replace( '/\s+/', ' ', $fallback_classes ) );

	printf(
		'<div class="%s bg-blue-50 text-[#2563EB] font-display font-black text-sm flex items-center justify-center" aria-hidden="true">UNI</div>',
		esc_attr( $fallback_classes )
	);
}

// ----------------------------------------------------
// 3. Module Loader
// ----------------------------------------------------
$ltdh_modules = [
	'inc/post-types.php',
	'inc/acf-fields.php',
	'inc/relationship-hooks.php',
	'inc/lead-capture.php',
	'inc/crm-adapters.php',
	'inc/seo.php',
	'inc/cli-commands.php',
	'inc/search-engine.php',
	'inc/comparison.php',
	'inc/eligibility.php',
	'inc/eligibility-rules.php',
];

foreach ( $ltdh_modules as $module ) {
	$file_path = get_template_directory() . '/' . $module;
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// ----------------------------------------------------
// 4. Redirect Empty Taxonomy Base URLs
// ----------------------------------------------------
add_action( 'template_redirect', 'ltdh_redirect_taxonomy_base' );
function ltdh_redirect_taxonomy_base() {
	if ( is_tax( 'training_type' ) || is_tax( 'campus' ) ) {
		return;
	}

	$request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( preg_match( '#^/he-dao-tao/?$#i', $request_path ) || preg_match( '#^/co-so/?$#i', $request_path ) ) {
		wp_redirect( home_url( '/chuong-trinh/' ), 301 );
		exit;
	}
}

// ----------------------------------------------------
// 5. Flush Rewrite Rules on Theme Activation
// ----------------------------------------------------
add_action( 'after_switch_theme', 'ltdh_flush_rewrite_rules' );
function ltdh_flush_rewrite_rules() {
	flush_rewrite_rules();
}

/**
 * Fallback menu for desktop primary header navigation
 */
function ltdh_default_primary_menu() {
	$schools = get_posts( [ 'post_type' => 'school', 'numberposts' => 5 ] );
	$majors  = get_posts( [ 'post_type' => 'major', 'numberposts' => 5 ] );
	$types   = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
	?>
	<ul class="nav-primary-menu">
		<li class="menu-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a></li>
		
		<li class="menu-item menu-item-has-children">
			<a href="<?php echo esc_url( home_url( '/truong-lien-ket/' ) ); ?>">Trường liên kết</a>
			<?php if ( ! empty( $schools ) ) : ?>
				<ul class="sub-menu">
					<?php foreach ( $schools as $s ) : ?>
						<li class="menu-item"><a href="<?php echo esc_url( get_permalink( $s->ID ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="menu-item menu-item-has-children">
			<a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>">Ngành học</a>
			<?php if ( ! empty( $majors ) ) : ?>
				<ul class="sub-menu">
					<?php foreach ( $majors as $m ) : ?>
						<li class="menu-item"><a href="<?php echo esc_url( get_permalink( $m->ID ) ); ?>"><?php echo esc_html( $m->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="menu-item menu-item-has-children">
			<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>">Hệ đào tạo</a>
			<?php if ( ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
				<ul class="sub-menu">
					<?php foreach ( $types as $t ) : ?>
						<li class="menu-item"><a href="<?php echo esc_url( home_url( '/chuong-trinh/?he=' . $t->slug ) ); ?>"><?php echo esc_html( $t->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="menu-item"><a href="<?php echo esc_url( home_url( '/tin-tuyen-sinh/' ) ); ?>">Tin tức</a></li>
		<li class="menu-item"><a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>">Kiểm tra điều kiện</a></li>
	</ul>
	<?php
}

/**
 * Fallback menu for mobile navigation
 */
function ltdh_default_mobile_menu() {
	$schools = get_posts( [ 'post_type' => 'school', 'numberposts' => 5 ] );
	$majors  = get_posts( [ 'post_type' => 'major', 'numberposts' => 5 ] );
	$types   = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
	?>
	<ul class="nav-mobile-menu">
		<li class="menu-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a></li>
		
		<li class="menu-item menu-item-has-children">
			<a href="<?php echo esc_url( home_url( '/truong-lien-ket/' ) ); ?>">Trường liên kết</a>
			<?php if ( ! empty( $schools ) ) : ?>
				<ul class="sub-menu">
					<?php foreach ( $schools as $s ) : ?>
						<li class="menu-item"><a href="<?php echo esc_url( get_permalink( $s->ID ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="menu-item menu-item-has-children">
			<a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>">Ngành học</a>
			<?php if ( ! empty( $majors ) ) : ?>
				<ul class="sub-menu">
					<?php foreach ( $majors as $m ) : ?>
						<li class="menu-item"><a href="<?php echo esc_url( get_permalink( $m->ID ) ); ?>"><?php echo esc_html( $m->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="menu-item menu-item-has-children">
			<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>">Hệ đào tạo</a>
			<?php if ( ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
				<ul class="sub-menu">
					<?php foreach ( $types as $t ) : ?>
						<li class="menu-item"><a href="<?php echo esc_url( home_url( '/chuong-trinh/?he=' . $t->slug ) ); ?>"><?php echo esc_html( $t->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>

		<li class="menu-item"><a href="<?php echo esc_url( home_url( '/tin-tuyen-sinh/' ) ); ?>">Tin tức</a></li>
		<li class="menu-item"><a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>">Kiểm tra điều kiện</a></li>
	</ul>
	<?php
}

/**
 * Fallback menu for footer quick links
 */
function ltdh_default_footer_menu() {
	?>
	<ul class="space-y-2 text-sm flex flex-col nav-footer-menu">
		<li><a href="<?php echo esc_url( home_url( '/gioi-thieu/' ) ); ?>">Giới thiệu</a></li>
		<li><a href="<?php echo esc_url( home_url( '/cau-hoi-thuong-gap/' ) ); ?>">Câu hỏi thường gặp</a></li>
		<li><a href="<?php echo esc_url( home_url( '/chinh-sach-bao-mat/' ) ); ?>">Chính sách bảo mật</a></li>
		<li><a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Liên hệ</a></li>
	</ul>
	<?php
}

/**
 * Get formatted learning mode and campus text for a program dynamically
 */
function ltdh_get_program_learning_details( $program_id ) {
	// Get campus taxonomy term
	$campuses = wp_get_post_terms( $program_id, 'campus' );
	$campus_name = ! empty( $campuses ) && ! is_wp_error( $campuses ) ? $campuses[0]->name : 'Hà Nội';

	// Get training type taxonomy term
	$types = wp_get_post_terms( $program_id, 'training_type' );
	$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';

	$learning_mode = 'Học tập trung';
	if ( $type_slug === 'tu-xa' ) {
		$learning_mode = 'Học online 100%';
	} elseif ( $type_slug === 'vua-hoc-vua-lam' ) {
		$learning_mode = 'Học tập trung cuối tuần';
	} elseif ( $type_slug === 'van-bang-2' ) {
		$learning_mode = 'Học tập trung / Online linh hoạt';
	}

	return [
		'campus' => $campus_name,
		'mode'   => $learning_mode,
	];
}

/**
 * AJAX handler for filtering programs dynamically
 */
add_action( 'wp_ajax_ltdh_filter_programs', 'ltdh_ajax_filter_programs' );
add_action( 'wp_ajax_nopriv_ltdh_filter_programs', 'ltdh_ajax_filter_programs' );

function ltdh_ajax_filter_programs() {
	// Fetch filter values from POST
	$selected_school = isset( $_POST['truong'] ) ? sanitize_text_field( $_POST['truong'] ) : '';
	$selected_major  = isset( $_POST['nganh'] ) ? sanitize_text_field( $_POST['nganh'] ) : '';
	$selected_type   = isset( $_POST['he'] ) ? sanitize_text_field( $_POST['he'] ) : '';
	$selected_search = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '';

	$args = [
		'post_type'      => 'program',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'meta_query'     => [
			'relation' => 'AND',
			[
				'relation' => 'OR',
				[
					'key'     => 'admission_status',
					'value'   => 'tam-ngung',
					'compare' => '!=',
				],
				[
					'key'     => 'admission_status',
					'compare' => 'NOT EXISTS',
				],
			]
		],
		'tax_query'      => [ 'relation' => 'AND' ],
	];

	if ( ! empty( $selected_search ) ) {
		$args['s'] = $selected_search;
	}

	if ( ! empty( $selected_school ) ) {
		if ( ! is_numeric( $selected_school ) ) {
			$school_post = get_page_by_path( $selected_school, OBJECT, 'school' );
			$school_id = $school_post ? $school_post->ID : 0;
		} else {
			$school_id = intval( $selected_school );
		}
		$args['meta_query'][] = [
			'key'     => 'school_relationship',
			'value'   => $school_id,
			'compare' => '=',
		];
	}

	if ( ! empty( $selected_major ) ) {
		if ( ! is_numeric( $selected_major ) ) {
			$major_post = get_page_by_path( $selected_major, OBJECT, 'major' );
			$major_id = $major_post ? $major_post->ID : 0;
		} else {
			$major_id = intval( $selected_major );
		}
		$args['meta_query'][] = [
			'key'     => 'major_relationship',
			'value'   => $major_id,
			'compare' => '=',
		];
	}

	if ( ! empty( $selected_type ) ) {
		$args['tax_query'][] = [
			'taxonomy' => 'training_type',
			'field'    => 'slug',
			'terms'    => $selected_type,
		];
	}

	// Apply synonym/relational search interceptor
	$args  = apply_filters( 'pre_get_posts_args_ltdh', $args );
	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$prog_id = get_the_ID();
			$school_rel_id = get_field( 'school_relationship', $prog_id );
			$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';
			$major_rel_id = get_field( 'major_relationship', $prog_id );
			$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
			if ( ! $major_thumb ) {
				$major_thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
			}
			$types = wp_get_post_terms( $prog_id, 'training_type' );
			$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : 'Chưa xác định';
			$groups = get_post_meta( $prog_id, 'admission_groups', true );
			$learning_details = ltdh_get_program_learning_details( $prog_id );
			?>
			<div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
				 data-compare-btn data-compare-type="program" data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
				 data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
				 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
				 data-compare-thumb="<?php echo esc_url( $major_thumb ); ?>">
				<div class="h-44 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $major_thumb ); ?>');"></div>
				<div class="p-6 flex-1 flex flex-col justify-between">
					<div>
						<div class="flex items-center flex-wrap gap-2 mb-1.5">
							<span class="text-sm text-slate-400 font-semibold uppercase"><?php echo esc_html( $school_name ); ?></span>
						</div>
						<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-3">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>
						
						<div class="space-y-1.5 text-sm text-slate-500 py-3 border-t border-slate-100">
							<p>Hệ đào tạo: <span class="font-bold text-slate-700"><?php echo esc_html( $type_name ); ?></span></p>
							<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ' ); ?></span></p>
							<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' ); ?></span></p>
							<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( $learning_details['campus'] ); ?></span></p>
							<p>Hình thức: <span class="font-bold text-slate-700 text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
							<?php if ( ! empty( $groups ) ) : ?>
								<p>Tổ hợp: <span class="font-bold text-slate-700"><?php echo esc_html( $groups ); ?></span></p>
							<?php endif; ?>
						</div>
					</div>

					<div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
						<div class="flex items-center gap-2">
							<a href="<?php the_permalink(); ?>" class="text-sm text-brand-primary font-bold hover:underline">Chi tiết</a>
							<button type="button" class="ltdh-compare-toggle text-xs text-slate-400 hover:text-brand-primary font-semibold border border-slate-200 hover:border-brand-primary rounded-lg px-2.5 py-1 transition-all"
									data-compare-type="program" data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
									data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
									data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>">
								So sánh
							</button>
						</div>
						<a href="<?php the_permalink(); ?>#register" class="bg-[#2563EB] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#1E40AF] transition-all">Đăng ký học</a>
					</div>
				</div>
			</div>
			<?php
		endwhile;
		wp_reset_postdata();
	else :
		echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Không tìm thấy chương trình học nào khớp với bộ lọc.</p></div>';
	endif;
	$html = ob_get_clean();

	wp_send_json_success( [ 'html' => $html ] );
}

/**
 * Dynamically inject submenus for Trường liên kết, Ngành học, and Hệ đào tạo
 * into the WordPress primary menu, even if it is configured via the WP Admin Menu UI.
 */
add_filter( 'wp_nav_menu_objects', 'ltdh_dynamic_menu_submenu_injection', 10, 2 );
function ltdh_dynamic_menu_submenu_injection( $sorted_menu_items, $args ) {
	if ( ! in_array( $args->theme_location, [ 'primary-menu' ], true ) ) {
		return $sorted_menu_items;
	}

	$new_items = [];
	$max_db_id = 0;
	foreach ( $sorted_menu_items as $item ) {
		if ( $item->ID > $max_db_id ) {
			$max_db_id = $item->ID;
		}
	}

	foreach ( $sorted_menu_items as $item ) {
		$new_items[] = $item;
		$title = mb_strtolower( trim( $item->title ), 'UTF-8' );

		// Only inject if the item does not already have children manually configured
		if ( $title === 'trường liên kết' ) {
			// No dropdown
		} elseif ( $title === 'ngành học' ) {
			$item->classes[] = 'menu-item-has-children';
			
			$majors = get_posts( [ 'post_type' => 'major', 'numberposts' => -1, 'post_status' => 'publish' ] );
			foreach ( $majors as $m ) {
				$max_db_id++;
				$sub_item = new stdClass();
				$sub_item->ID = $max_db_id;
				$sub_item->db_id = $max_db_id;
				$sub_item->title = $m->post_title;
				$sub_item->url = get_permalink( $m->ID );
				$sub_item->menu_item_parent = $item->ID;
				$sub_item->classes = [ 'menu-item', 'menu-item-type-post_type', 'menu-item-object-major' ];
				$sub_item->type = 'post_type';
				$sub_item->object = 'major';
				$sub_item->object_id = $m->ID;
				$sub_item->post_parent = 0;
				$sub_item->post_title = $m->post_title;
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
		} elseif ( $title === 'hệ đào tạo' ) {
			// Remove any existing manual children of 'Hệ đào tạo'
			$filtered_items = [];
			foreach ( $new_items as $k => $ni ) {
				if ( (int) $ni->menu_item_parent === (int) $item->ID ) {
					continue;
				}
				$filtered_items[] = $ni;
			}
			$new_items = $filtered_items;

			$item->classes[] = 'menu-item-has-children';
			$types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
			if ( ! is_wp_error( $types ) ) {
				foreach ( $types as $t ) {
					$max_db_id++;
					$sub_item = new stdClass();
					$sub_item->ID = $max_db_id;
					$sub_item->db_id = $max_db_id;
					$sub_item->title = $t->name;
					$sub_item->url = home_url( '/chuong-trinh/?he=' . $t->slug );
					$sub_item->menu_item_parent = $item->ID;
					$sub_item->classes = [ 'menu-item', 'menu-item-type-taxonomy', 'menu-item-object-training_type' ];
					$sub_item->type = 'taxonomy';
					$sub_item->object = 'training_type';
					$sub_item->object_id = $t->term_id;
					$sub_item->post_parent = 0;
					$sub_item->post_title = $t->name;
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
	}
	
	return $new_items;
}



/**
 * Customize queries for specific post types
 */
add_action( 'pre_get_posts', 'ltdh_customize_archive_queries' );
function ltdh_customize_archive_queries( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		if ( $query->is_post_type_archive( 'school' ) ) {
			$query->set( 'posts_per_page', -1 );
		}
	}
}

/**
 * Enable WebP upload support
 */
add_filter( 'upload_mimes', 'ltdh_enable_webp_upload' );
function ltdh_enable_webp_upload( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'ltdh_allow_webp_upload_check', 10, 4 );
function ltdh_allow_webp_upload_check( $data, $file, $filename, $mimes ) {
	$filetype = wp_check_filetype( $filename, $mimes );
	$ext      = $filetype['ext'];
	$type     = $filetype['type'];
	if ( 'webp' === $ext ) {
		$data['ext']  = 'webp';
		$data['type'] = 'image/webp';
	}
	return $data;
}
