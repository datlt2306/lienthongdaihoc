<?php
/**
 * Lead Magnet Modal Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$school_id  = isset( $args['school_id'] ) ? (int) $args['school_id'] : (int) get_field( LTDH_META_SCHOOL_REL, $program_id );
$major_id   = isset( $args['major_id'] ) ? (int) $args['major_id'] : (int) get_field( LTDH_META_MAJOR_REL, $program_id );
?>

<!-- LEAD MAGNET MODAL COMPONENT -->
<div id="ltdh-lead-magnet-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
	<div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
		<!-- Header -->
		<div class="bg-gradient-to-r from-[#00308b] to-[#002266] px-5 py-4 text-white flex items-center justify-between">
			<div class="flex items-center gap-2.5">
				<span class="text-xl">📥</span>
				<div>
					<h3 class="font-extrabold text-sm sm:text-base leading-tight">Tải Tài Liệu Tuyển Sinh</h3>
					<p class="text-[11px] text-blue-200 mt-0.5">Mẫu đơn đăng ký & Khung chương trình đào tạo</p>
				</div>
			</div>
			<button type="button" onclick="ltdhCloseLeadMagnetModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer" aria-label="Đóng">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
			</button>
		</div>

		<!-- Form Body -->
		<form id="ltdh-lead-magnet-form" class="p-5 sm:p-6 space-y-4" onsubmit="ltdhSubmitLeadMagnet(event)">
			<input type="hidden" name="action" value="ltdh_lead_magnet_download">
			<input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'ltdh_lead_magnet_nonce' ) ); ?>">
			<input type="hidden" name="program_id" value="<?php echo (int) $program_id; ?>">
			<input type="hidden" name="school_id" value="<?php echo (int) $school_id; ?>">
			<input type="hidden" name="major_id" value="<?php echo (int) $major_id; ?>">
			<input type="hidden" id="ltdh-lm-file-url" name="doc_url" value="">
			<input type="hidden" id="ltdh-lm-file-title" name="doc_title" value="">

			<!-- Honeypot -->
			<div class="hidden" style="display:none !important;" aria-hidden="true">
				<label for="lm_hp_website">Website URL</label>
				<input type="text" name="hp_website" id="lm_hp_website" tabindex="-1" autocomplete="off" value="">
			</div>

			<div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex items-start gap-2.5">
				<span class="text-base text-blue-600 shrink-0">📄</span>
				<div class="text-xs text-slate-700 min-w-0">
					<span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Tài liệu bạn chọn:</span>
					<strong id="ltdh-lm-doc-name-label" class="text-slate-900 truncate block">Khung chương trình đào tạo</strong>
				</div>
			</div>

			<div>
				<label for="ltdh-lm-name" class="block text-xs font-bold text-slate-700 mb-1">Họ và tên của bạn <span class="text-red-500">*</span></label>
				<input type="text" id="ltdh-lm-name" name="name" required autocomplete="name" aria-required="true" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b] focus:outline-none" placeholder="Nguyễn Văn A">
			</div>

			<div>
				<label for="ltdh-lm-phone" class="block text-xs font-bold text-slate-700 mb-1">Số điện thoại nhận tài liệu (Zalo) <span class="text-red-500">*</span></label>
				<input type="tel" id="ltdh-lm-phone" name="phone" required autocomplete="tel" aria-required="true" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b] focus:outline-none" placeholder="0912 345 678">
				<p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ mở file trực tiếp và gửi bản dự phòng qua Zalo cho bạn.</p>
			</div>

			<div id="ltdh-lm-error" class="hidden text-xs text-red-600 bg-red-50 p-2.5 rounded-lg border border-red-200"></div>

			<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
				<?php ltdh_render_recaptcha_field( 'lead_magnet' ); ?>
			<?php endif; ?>

			<button type="submit" id="ltdh-lm-submit-btn" class="w-full bg-[#0071E3] hover:bg-[#0077ED] text-white py-3 rounded-full text-sm font-semibold tracking-tight shadow-none hover:shadow-xs active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer">
				<span>Tải Tài Liệu Miễn Phí</span>
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
			</button>

			<p class="text-[10px] text-slate-400 text-center">
				🔒 Cam kết bảo mật thông tin 100% theo Nghị định 13/2023/NĐ-CP.
			</p>
		</form>
	</div>
</div>

<script data-no-defer="1">
window.ltdhCloseLeadMagnetModal = function() {
	const modal = document.getElementById('ltdh-lead-magnet-modal');
	if (modal) {
		modal.classList.add('hidden');
		modal.classList.remove('flex');
		document.body.style.overflow = '';
	}
};

window.ltdhSubmitLeadMagnet = async function(e) {
	if (e && e.preventDefault) e.preventDefault();
	const form = document.getElementById('ltdh-lead-magnet-form');
	if (!form) return;

	const fileUrlInput = document.getElementById('ltdh-lm-file-url');
	const submitBtn    = document.getElementById('ltdh-lm-submit-btn');
	const errorEl      = document.getElementById('ltdh-lm-error');
	const targetUrl    = fileUrlInput ? fileUrlInput.value : '';

	if (submitBtn) {
		submitBtn.disabled = true;
		submitBtn.innerHTML = '<span>Đang gửi thông tin...</span>';
	}
	if (errorEl) errorEl.classList.add('hidden');

	const formData  = new FormData(form);

	if (window.ltdhGetRecaptchaToken) {
		try {
			const recaptchaToken = await window.ltdhGetRecaptchaToken('lead_magnet', form);
			if (recaptchaToken) {
				formData.set('g-recaptcha-response', recaptchaToken);
			}
		} catch (recaptchaErr) {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<span>Tải Tài Liệu Miễn Phí</span>';
			}
			if (errorEl) {
				errorEl.textContent = recaptchaErr.message || 'Vui lòng xác thực ô "Tôi không phải là người máy".';
				errorEl.classList.remove('hidden');
			}
			return;
		}
	}

	fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
		method: 'POST',
		body: formData
	})
	.then(res => res.json())
	.then(data => {
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.innerHTML = '<span>Tải Tài Liệu Miễn Phí</span>';
		}

		if (data.success) {
			sessionStorage.setItem('ltdh_lead_magnet_unlocked', '1');
			window.ltdhCloseLeadMagnetModal();

			// Automatically trigger download or open file in new window
			if (targetUrl) {
				const win = window.open(targetUrl, '_blank');
				if (!win || win.closed || typeof win.closed === 'undefined') {
					window.location.href = targetUrl;
				}
			}
		} else {
			if (errorEl) {
				errorEl.textContent = data.data && data.data.message ? data.data.message : 'Có lỗi xảy ra, vui lòng thử lại.';
				errorEl.classList.remove('hidden');
			}
		}
	})
	.catch(err => {
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.innerHTML = '<span>Tải Tài Liệu Miễn Phí</span>';
		}
		if (errorEl) {
			errorEl.textContent = 'Lỗi kết nối mạng. Vui lòng thử lại sau.';
			errorEl.classList.remove('hidden');
		}
	});
};

function ltdhInitLeadMagnet() {
	const modal          = document.getElementById('ltdh-lead-magnet-modal');
	const fileUrlInput   = document.getElementById('ltdh-lm-file-url');
	const fileTitleInput = document.getElementById('ltdh-lm-file-title');
	const docLabel       = document.getElementById('ltdh-lm-doc-name-label');
	const errorEl        = document.getElementById('ltdh-lm-error');

	// Document buttons open in new tab with rel="nofollow noopener noreferrer" natively.
	// Keep modal trigger available if gated action is explicitly requested.
	window.ltdhOpenLeadMagnetModal = function(targetUrl, targetTitle) {
		if (fileUrlInput) fileUrlInput.value = targetUrl || '';
		if (fileTitleInput) fileTitleInput.value = targetTitle || '';
		if (docLabel) docLabel.textContent = targetTitle || 'Tài liệu tuyển sinh';
		if (errorEl) {
			errorEl.textContent = '';
			errorEl.classList.add('hidden');
		}
		if (modal) {
			modal.classList.remove('hidden');
			modal.classList.add('flex');
			document.body.style.overflow = 'hidden';
		}
	};

	// Close on modal backdrop click
	if (modal) {
		modal.addEventListener('click', function(e) {
			if (e.target === modal) {
				window.ltdhCloseLeadMagnetModal();
			}
		});
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', ltdhInitLeadMagnet);
} else {
	ltdhInitLeadMagnet();
}
</script>
