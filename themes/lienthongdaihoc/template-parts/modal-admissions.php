<?php
/**
 * Admissions Consultation Modal Template Part
 *
 * Decoupled modal dialogue for lead capture across desktop and mobile.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$modal_hidden = [
	'referral_source' => home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) ),
];
$modal_title    = 'Đăng ký nhận tư vấn tuyển sinh';
$modal_subtitle = 'Để lại thông tin, ban tư vấn sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong 15 phút.';

if ( is_singular( 'program' ) ) {
	$cur_pid = get_the_ID();
	$modal_hidden['program_id']         = $cur_pid;
	$modal_hidden['current_program_id'] = $cur_pid;
	$modal_school_id = get_field( 'school_relationship', $cur_pid );
	if ( $modal_school_id ) {
		$sid = is_object( $modal_school_id ) ? $modal_school_id->ID : (int) $modal_school_id;
		$modal_hidden['school_id']         = $sid;
		$modal_hidden['current_school_id'] = $sid;
	}
	$modal_major_id = get_field( 'major_relationship', $cur_pid );
	if ( ! empty( $modal_major_id ) ) {
		$mid = is_object( $modal_major_id ) ? $modal_major_id->ID : ( is_array( $modal_major_id ) && ! empty( $modal_major_id[0] ) ? ( is_object( $modal_major_id[0] ) ? $modal_major_id[0]->ID : (int) $modal_major_id[0] ) : (int) $modal_major_id );
		$modal_hidden['major_id']         = $mid;
		$modal_hidden['current_major_id'] = $mid;
	}
	$modal_title    = 'Đăng ký tư vấn chương trình';
	$modal_subtitle = get_the_title( $cur_pid );
} elseif ( is_singular( 'school' ) ) {
	$cur_sid = get_the_ID();
	$modal_hidden['school_id']         = $cur_sid;
	$modal_hidden['current_school_id'] = $cur_sid;
	$modal_title    = 'Đăng ký vào ' . get_the_title( $cur_sid );
	$modal_subtitle = 'Nhận tư vấn hồ sơ và hỗ trợ xử lý thủ tục nhập học nhanh chóng.';
} elseif ( is_singular( 'major' ) ) {
	$cur_mid = get_the_ID();
	$modal_hidden['major_id']         = $cur_mid;
	$modal_hidden['current_major_id'] = $cur_mid;
	$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
	if ( ! empty( $school_param ) ) {
		if ( is_numeric( $school_param ) ) {
			$s_post = get_post( intval( $school_param ) );
		} else {
			$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
			$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
		}
		if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
			$modal_hidden['school_id']         = $s_post->ID;
			$modal_hidden['current_school_id'] = $s_post->ID;
		}
	}
	$modal_title    = 'Đăng ký tư vấn ngành ' . get_the_title( $cur_mid );
	$modal_subtitle = 'Ban tư vấn sẽ gửi danh sách trường và lộ trình học tối ưu cho bạn.';
}
?>
<div id="ltdh-consultation-modal" 
     class="fixed inset-0 z-[999] hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs items-center justify-center p-3 sm:p-5"
     role="dialog"
     aria-modal="true"
     aria-labelledby="ltdh-consultation-modal-title">
	<div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all my-auto">
		<!-- Modal Header -->
		<div class="bg-gradient-to-r from-[#00308b] via-[#004bb5] to-[#002266] px-5 py-4 text-white flex items-center justify-between">
			<div class="flex items-center gap-3 min-w-0">
				<div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg shrink-0 border border-white/20">
					🎓
				</div>
				<div class="min-w-0">
					<h3 id="ltdh-consultation-modal-title" class="font-extrabold text-sm sm:text-base leading-tight truncate">
						<?php echo esc_html( $modal_title ); ?>
					</h3>
					<p class="text-[11px] sm:text-xs text-blue-200 mt-0.5 truncate">
						<?php echo esc_html( $modal_subtitle ); ?>
					</p>
				</div>
			</div>
			<button type="button" 
			        onclick="window.ltdhCloseConsultationModal && window.ltdhCloseConsultationModal()" 
			        class="p-2 -mr-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 active:scale-95 transition-all cursor-pointer shrink-0" 
			        aria-label="Đóng popup">
				<?php ltdh_icon( 'xmark', 'w-5 h-5' ); ?>
			</button>
		</div>

		<!-- Modal Body -->
		<div class="p-5 sm:p-6 max-h-[calc(90vh-80px)] overflow-y-auto">
			<?php
			if ( function_exists( 'ltdh_render_consultation_form' ) ) {
				ltdh_render_consultation_form( $modal_hidden );
			}
			?>
		</div>
	</div>
</div>

<script>
(function() {
	function openConsultationModal(e) {
		if (e && e.preventDefault) e.preventDefault();
		var modal = document.getElementById('ltdh-consultation-modal');
		if (modal) {
			modal.classList.remove('hidden');
			modal.classList.add('flex');
			document.body.style.overflow = 'hidden';
			var firstInput = modal.querySelector('input[name="your-name"], input[type="text"]:not([tabindex="-1"]), input[type="tel"]');
			if (firstInput) {
				setTimeout(function() { firstInput.focus(); }, 100);
			}
		}
	}

	function closeConsultationModal() {
		var modal = document.getElementById('ltdh-consultation-modal');
		if (modal) {
			modal.classList.add('hidden');
			modal.classList.remove('flex');
			document.body.style.overflow = '';
		}
	}

	window.ltdhOpenConsultationModal = openConsultationModal;
	window.ltdhCloseConsultationModal = closeConsultationModal;

	document.addEventListener('DOMContentLoaded', function() {
		// Attach to all elements marked with js-open-consultation-modal
		document.querySelectorAll('.js-open-consultation-modal, .ltdh-open-consultation-modal, [data-target="#ltdh-consultation-modal"]').forEach(function(el) {
			el.addEventListener('click', openConsultationModal);
		});

		// On mobile (< 1024px), intercept any clicks to #register anchors
		document.querySelectorAll('a[href="#register"], a[href="#register-section"]').forEach(function(el) {
			el.addEventListener('click', function(e) {
				if (window.innerWidth < 1024) {
					openConsultationModal(e);
				}
			});
		});

		// Close modal on backdrop click
		var modal = document.getElementById('ltdh-consultation-modal');
		if (modal) {
			modal.addEventListener('click', function(e) {
				if (e.target === modal) {
					closeConsultationModal();
				}
			});
		}

		// Close modal on Escape
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				closeConsultationModal();
			}
		});

		// If returned from successful form submission and on mobile, open modal to show success alert
		if (window.location.search.indexOf('submit_success=1') !== -1 && window.innerWidth < 1024) {
			var successBox = modal ? modal.querySelector('.text-green-800') : null;
			if (successBox) {
				openConsultationModal();
			}
		}
	});
})();
</script>
