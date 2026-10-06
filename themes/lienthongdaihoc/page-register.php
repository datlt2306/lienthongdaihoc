<?php
/**
 * Template Name: Đăng ký tư vấn
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main py-16 bg-slate-50">
	<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<div class="bg-white rounded-xl p-8 md:p-12 shadow-md border border-slate-200">
			<div class="text-center max-w-lg mx-auto mb-8">
				<h1 class="text-3xl font-black text-slate-900 mb-3">ĐĂNG KÝ NHẬN TƯ VẤN TUYỂN SINH</h1>
				<p class="text-slate-500 text-sm">Hãy điền đầy đủ thông tin của bạn dưới đây. Các chuyên gia sẽ liên hệ tư vấn lộ trình học phù hợp nhất với bạn hoàn toàn miễn phí.</p>
			</div>

			<?php 
				$hidden_fields = [
					'referral_source' => esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ?? get_permalink() ) ),
				];

				$req_prog_id = isset( $_GET['program_id'] ) ? absint( $_GET['program_id'] ) : 0;
				if ( ! $req_prog_id && isset( $_GET['program'] ) ) {
					$prog_by_slug = get_page_by_path( sanitize_title( wp_unslash( $_GET['program'] ) ), OBJECT, 'program' );
					if ( $prog_by_slug ) {
						$req_prog_id = $prog_by_slug->ID;
					}
				}

				if ( $req_prog_id ) {
					$hidden_fields['program_id']         = $req_prog_id;
					$hidden_fields['current_program_id'] = $req_prog_id;
					$school_rel = get_field( 'school_relationship', $req_prog_id );
					if ( $school_rel ) {
						$hidden_fields['school_id'] = is_object( $school_rel ) ? $school_rel->ID : absint( $school_rel );
					}
					$major_rel = get_field( 'major_relationship', $req_prog_id );
					if ( $major_rel ) {
						$hidden_fields['major_id'] = is_object( $major_rel ) ? $major_rel->ID : absint( $major_rel );
					}
					?>
					<div class="mb-6 p-4 bg-blue-50/80 border border-blue-200 rounded-xl flex items-center gap-3 text-slate-800">
						<span class="text-2xl shrink-0">🎓</span>
						<div class="min-w-0">
							<span class="text-xs uppercase font-bold text-blue-700 block">Chương trình bạn đang quan tâm:</span>
							<strong class="text-sm md:text-base font-extrabold text-slate-900"><?php echo esc_html( get_the_title( $req_prog_id ) ); ?></strong>
						</div>
					</div>
					<?php
				}

				if ( isset( $_GET['school_id'] ) ) {
					$hidden_fields['school_id'] = absint( $_GET['school_id'] );
				} elseif ( isset( $_GET['school'] ) ) {
					$school_by_slug = get_page_by_path( sanitize_title( wp_unslash( $_GET['school'] ) ), OBJECT, 'school' );
					if ( $school_by_slug ) {
						$hidden_fields['school_id'] = $school_by_slug->ID;
					}
				}

				ltdh_render_consultation_form( $hidden_fields );
			?>
		</div>

	</div>
</main>

<?php
get_footer();
