<?php
/**
 * Theme functions and definitions — Bootstrap file.
 *
 * This file only loads modules. All logic lives in inc/.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Constants & Configuration
// ----------------------------------------------------
require_once __DIR__ . '/inc/config/constants.php';
require_once __DIR__ . '/inc/config/class-defaults.php';

// ----------------------------------------------------
// 2. Core: Setup, Helpers, Rewrites, Queries
// ----------------------------------------------------
require_once __DIR__ . '/inc/core/class-theme-setup.php';
require_once __DIR__ . '/inc/core/class-helpers.php';
require_once __DIR__ . '/inc/core/class-menus.php';
require_once __DIR__ . '/inc/core/class-rewrite-rules.php';
require_once __DIR__ . '/inc/core/class-query-filters.php';

// ----------------------------------------------------
// 3. ACF: Field Groups & Options Page Extensions
// ----------------------------------------------------
require_once __DIR__ . '/inc/acf-fields.php';
require_once __DIR__ . '/inc/admin/class-options-page.php';

// ----------------------------------------------------
// 4. Content: CPTs & Taxonomies
// ----------------------------------------------------
require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/relationship-hooks.php';

// ----------------------------------------------------
// 5. Modules: Business Logic
// ----------------------------------------------------
require_once __DIR__ . '/inc/lead-capture.php';
require_once __DIR__ . '/inc/crm-adapters.php';
require_once __DIR__ . '/inc/search-engine.php';
require_once __DIR__ . '/inc/comparison.php';
require_once __DIR__ . '/inc/eligibility.php';
require_once __DIR__ . '/inc/eligibility-rules.php';

// ----------------------------------------------------
// 6. SEO: Rank Math Integration
// ----------------------------------------------------
require_once __DIR__ . '/inc/seo/class-rankmath-integration.php';

// ----------------------------------------------------
// 7. CLI: WP-CLI Commands
// ----------------------------------------------------
require_once __DIR__ . '/inc/cli-commands.php';

// ----------------------------------------------------
// 8. AJAX Handlers (kept inline for backward compat)
// ----------------------------------------------------
add_action( 'wp_ajax_' . LTDH_AJAX_FILTER_PROGRAMS, 'ltdh_ajax_filter_programs' );
add_action( 'wp_ajax_nopriv_' . LTDH_AJAX_FILTER_PROGRAMS, 'ltdh_ajax_filter_programs' );

/**
 * AJAX handler for filtering programs dynamically.
 */
function ltdh_ajax_filter_programs() {
	$selected_school = isset( $_POST['truong'] ) ? sanitize_text_field( wp_unslash( $_POST['truong'] ) ) : '';
	$selected_major  = isset( $_POST['nganh'] ) ? sanitize_text_field( wp_unslash( $_POST['nganh'] ) ) : '';
	$selected_type   = isset( $_POST['he'] ) ? sanitize_text_field( wp_unslash( $_POST['he'] ) ) : '';
	$selected_search = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';

	$args = [
		'post_type'      => LTDH_CPT_PROGRAM,
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'meta_query'     => [
			'relation' => 'AND',
			[
				'relation' => 'OR',
				[
					'key'     => LTDH_META_ADMISSION_STATUS,
					'value'   => LTDH_STATUS_PAUSED,
					'compare' => '!=',
				],
				[
					'key'     => LTDH_META_ADMISSION_STATUS,
					'compare' => 'NOT EXISTS',
				],
			],
		],
		'tax_query'      => [ 'relation' => 'AND' ],
	];

	if ( ! empty( $selected_search ) ) {
		$args['s'] = $selected_search;
	}

	if ( ! empty( $selected_school ) ) {
		if ( ! is_numeric( $selected_school ) ) {
			$school_post = get_page_by_path( $selected_school, OBJECT, LTDH_CPT_SCHOOL );
			$school_id   = $school_post ? $school_post->ID : 0;
		} else {
			$school_id = intval( $selected_school );
		}
		$args['meta_query'][] = [
			'key'     => LTDH_META_SCHOOL_REL,
			'value'   => $school_id,
			'compare' => '=',
		];
	}

	if ( ! empty( $selected_major ) ) {
		if ( ! is_numeric( $selected_major ) ) {
			$major_post = get_page_by_path( $selected_major, OBJECT, LTDH_CPT_MAJOR );
			$major_id   = $major_post ? $major_post->ID : 0;
		} else {
			$major_id = intval( $selected_major );
		}
		if ( $major_id ) {
			$args['meta_query'][] = [
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => $major_id,
				'compare' => '=',
			];
		}
	}

	if ( ! empty( $selected_type ) ) {
		$args['tax_query'][] = [
			'taxonomy' => LTDH_TAX_TRAINING_TYPE,
			'field'    => 'slug',
			'terms'    => $selected_type,
		];
	}

	$args  = apply_filters( 'pre_get_posts_args_ltdh', $args );
	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$prog_id       = get_the_ID();
			$school_rel_id = get_field( LTDH_META_SCHOOL_REL, $prog_id );
			$school_name   = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';
			$major_rel_id  = get_field( LTDH_META_MAJOR_REL, $prog_id );
			$major_thumb   = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
			if ( ! $major_thumb ) {
				$major_thumb = ltdh_get_fallback_image( 'program' );
			}
			$types            = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
			$type_name        = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : 'Chưa xác định';
			$groups           = get_post_meta( $prog_id, LTDH_META_AD_GROUPS, true );
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
							<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( LTDH_META_TUITION, $prog_id ) ?: 'Liên hệ' ); ?></span></p>
							<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( LTDH_META_DURATION, $prog_id ) ?: '1.5 - 2 năm' ); ?></span></p>
							<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( $learning_details['campus'] ); ?></span></p>
							<p>Hình thức: <span class=" text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
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
						<a href="<?php the_permalink(); ?>" class="bg-brand-primary text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-brand-darkBlue shadow-sm transition-all">Đăng ký học</a>
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

// Exclude .git directory from All-in-One WP Migration export to prevent size ballooning
add_filter( 'ai1wm_exclude_content_from_export', function ( $exclude_filters ) {
	$exclude_filters[] = '.git';
	return $exclude_filters;
} );
