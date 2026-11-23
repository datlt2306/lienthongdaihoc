<?php
/**
 * Condition Checker Modal Template Part (Bottom Sheet on Mobile)
 *
 * Dedicated decoupled slide-up bottom sheet modal for admission condition assessment on mobile.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id = 0;
if ( is_singular( 'program' ) ) {
	$program_id = (int) get_the_ID();
}

$page_context = function_exists( 'ltdh_elig_get_page_context' ) ? ltdh_elig_get_page_context( $program_id ) : [];
$school_title = $page_context['school_title'] ?? '';
$major_title  = $page_context['major_title'] ?? '';
$modal_title  = $major_title ? 'Kiểm tra điều kiện vào ' . $major_title : ( $school_title ? 'Kiểm tra điều kiện vào ' . $school_title : 'Kiểm tra điều kiện tuyển sinh' );
?>

<div id="ltdh-condition-checker-modal" 
     class="fixed inset-0 z-[999] hidden bg-slate-900/60 backdrop-blur-xs items-end sm:items-center justify-center p-0 sm:p-4 transition-all duration-300" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="ltdh-condition-modal-title">

	<!-- Bottom Sheet / Modal Dialog Container -->
	<div class="relative bg-white w-full sm:max-w-xl rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[92vh] sm:max-h-[88vh] overflow-hidden transform transition-transform duration-300 ease-out translate-y-full sm:translate-y-0"
	     id="ltdh-condition-modal-sheet">
		
		<!-- Mobile Pull Drag Indicator -->
		<div class="w-12 h-1.5 bg-slate-300 rounded-full mx-auto my-2.5 shrink-0 sm:hidden"></div>

		<!-- Modal Header (Navy Blue Gradient - Brand Style) -->
		<div class="bg-gradient-to-r from-[#00308b] via-[#004bb5] to-[#002266] px-4 sm:px-5 py-3.5 text-white flex items-center justify-between shrink-0" style="background: linear-gradient(135deg, #00308B 0%, #001F5C 100%) !important;">
			<div class="flex items-center gap-2.5 min-w-0">
				<div class="w-9 h-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white shrink-0 shadow-xs">
					<svg class="w-5 h-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
				</div>
				<div class="min-w-0">
					<h3 id="ltdh-condition-modal-title" class="font-extrabold text-sm sm:text-base leading-tight truncate text-white">
						<?php echo esc_html( $modal_title ); ?>
					</h3>
					<p class="text-[11px] sm:text-xs text-blue-200 mt-0.5 truncate">
						<?php echo esc_html( $school_title ? $school_title . ' • ' : '' ); ?>Chuẩn quy chế Bộ GD&ĐT 2026–2027
					</p>
				</div>
			</div>
			<button type="button" 
			        onclick="window.ltdhCloseConditionCheckerModal && window.ltdhCloseConditionCheckerModal()" 
			        class="p-2 -mr-1 rounded-xl text-white/80 hover:text-white hover:bg-white/10 active:scale-95 transition-all cursor-pointer shrink-0" 
			        aria-label="Đóng bảng kiểm tra">
				<?php ltdh_icon( 'xmark', 'w-5 h-5' ); ?>
			</button>
		</div>

		<!-- Modal Body: Compact Condition Checker Widget -->
		<div class="p-3 sm:p-5 overflow-y-auto flex-1 overscroll-contain">
			<?php
			get_template_part( 'template-parts/widgets/condition-checker', null, [
				'mode'          => 'compact',
				'program_id'    => $program_id,
				'widget_id'     => 'ltdh-checker-modal-instance',
				'wrapper_class' => 'my-0! shadow-none! border-0! bg-transparent!',
				'hide_header'   => true,
			] );
			?>

			<!-- Direct Consultation Alternative Switch -->
			<div class="mt-4 pt-3 border-t border-slate-100 text-center">
				<p class="text-xs text-slate-500 mb-1.5">
					Bạn chỉ cần ban tuyển sinh gọi lại hỗ trợ giải đáp trực tiếp?
				</p>
				<button type="button" 
				        onclick="window.ltdhCloseConditionCheckerModal && window.ltdhCloseConditionCheckerModal(); setTimeout(function(){ window.ltdhOpenConsultationModal && window.ltdhOpenConsultationModal(); }, 150);" 
				        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0071E3] hover:underline cursor-pointer">
					<span>Đăng ký nhận tư vấn nhanh qua điện thoại</span>
					<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
				</button>
			</div>
		</div>

	</div>
</div>

<script>
(function() {
	function openConditionCheckerModal(e) {
		if (e && e.preventDefault) e.preventDefault();
		// If consultation modal is open, close it first
		if (window.ltdhCloseConsultationModal) {
			window.ltdhCloseConsultationModal();
		}
		var modal = document.getElementById('ltdh-condition-checker-modal');
		var sheet = document.getElementById('ltdh-condition-modal-sheet');
		if (modal && sheet) {
			modal.classList.remove('hidden');
			modal.classList.add('flex');
			document.body.style.overflow = 'hidden';
			// Trigger smooth slide-up
			requestAnimationFrame(function() {
				sheet.classList.remove('translate-y-full');
				sheet.classList.add('translate-y-0');
			});
			// Ensure compact widget inside is initialized
			if (window.ltdhInitCompactWidgets) {
				window.ltdhInitCompactWidgets();
			}
		}
	}

	function closeConditionCheckerModal() {
		var modal = document.getElementById('ltdh-condition-checker-modal');
		var sheet = document.getElementById('ltdh-condition-modal-sheet');
		if (modal && sheet) {
			sheet.classList.remove('translate-y-0');
			sheet.classList.add('translate-y-full');
			setTimeout(function() {
				modal.classList.add('hidden');
				modal.classList.remove('flex');
				document.body.style.overflow = '';
			}, 250);
		}
	}

	window.ltdhOpenConditionCheckerModal = openConditionCheckerModal;
	window.ltdhCloseConditionCheckerModal = closeConditionCheckerModal;

	// Global event delegation for all triggers
	document.addEventListener('click', function(e) {
		var trigger = e.target && e.target.closest ? e.target.closest('.js-open-condition-checker-modal') : null;
		if (trigger) {
			openConditionCheckerModal(e);
		}
	});

	// Backdrop click to close
	var modal = document.getElementById('ltdh-condition-checker-modal');
	if (modal) {
		modal.addEventListener('click', function(e) {
			if (e.target === modal) {
				closeConditionCheckerModal();
			}
		});
	}

	// Escape key to close
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			closeConditionCheckerModal();
		}
	});
})();
</script>
