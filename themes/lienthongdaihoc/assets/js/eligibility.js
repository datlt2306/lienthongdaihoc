/**
 * Eligibility Checker — Unified Single-Page Form Frontend Logic
 *
 * @package lienthongdaihoc
 */
(function () {
	'use strict';

	// ----------------------------------------------------
	// State
	// ----------------------------------------------------
	var form = {};
	var results = null;

	// ----------------------------------------------------
	// DOM Ready
	// ----------------------------------------------------
	function initAll() {
		initForm();
		initNavigation();
		initResultsActions();
		initFileUpload();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	// ----------------------------------------------------
	// Form Init
	// ----------------------------------------------------
	function initForm() {
		// Input change handlers
		var inputs = document.querySelectorAll('.elig-input');
		for (var j = 0; j < inputs.length; j++) {
			inputs[j].addEventListener('change', function () {
				var name = this.getAttribute('name');
				if (name) form[name] = this.value;
			});
		}

		// Select change handlers
		var selects = document.querySelectorAll('.elig-select');
		for (var k = 0; k < selects.length; k++) {
			selects[k].addEventListener('change', function () {
				var name = this.getAttribute('name');
				if (name) form[name] = this.value;
			});
		}

		// Clear validation errors on user interaction
		var unifiedForm = document.getElementById('elig-unified-form');
		if (unifiedForm) {
			var clearHandler = function (e) {
				var el = e.target;
				el.classList.remove('elig-input-error');
				var parent = el.closest('.space-y-2') || el.parentElement;
				var errorEl = parent.querySelector('.elig-error-message');
				if (errorEl) errorEl.remove();
			};
			unifiedForm.addEventListener('input', clearHandler);
			unifiedForm.addEventListener('change', clearHandler);
		}

		// Searchable autocomplete selects
		initSearchSelects();
	}

	function initSearchSelects() {
		var containers = document.querySelectorAll('[data-search-select]');
		containers.forEach(function (container) {
			var input = container.querySelector('.elig-search-input');
			var hidden = container.querySelector('.elig-search-value');
			var dropdown = container.querySelector('.elig-search-dropdown');
			var items = container.querySelectorAll('.elig-search-option-item');

			// Focus input -> show dropdown
			input.addEventListener('focus', function () {
				document.querySelectorAll('.elig-search-dropdown').forEach(function (d) {
					d.classList.add('hidden');
				});
				dropdown.classList.remove('hidden');
			});

			// Click outside -> hide dropdown
			document.addEventListener('click', function (e) {
				if (!container.contains(e.target)) {
					dropdown.classList.add('hidden');
				}
			});

			// Filter items as user types
			input.addEventListener('input', function () {
				var query = this.value.toLowerCase().trim();
				items.forEach(function (item) {
					var text = item.textContent.toLowerCase();
					if (text.indexOf(query) > -1 || item.getAttribute('data-value') === '') {
						item.style.display = 'block';
					} else {
						item.style.display = 'none';
					}
				});
			});

			// Auto-match values when blurring or losing focus
			input.addEventListener('blur', function () {
				setTimeout(function () {
					var val = input.value.trim().toLowerCase();
					var name = hidden.getAttribute('name');
					var matched = false;

					items.forEach(function (item) {
						var text = item.textContent.trim().toLowerCase();
						var itemVal = item.getAttribute('data-value');

						if (itemVal !== '' && text === val) {
							hidden.value = itemVal;
							input.value = item.textContent.trim(); // Normalize capitalization
							form[name] = itemVal;
							matched = true;
						}
					});

					// If no exact match, try matching containing substring
					if (!matched && val !== '') {
						for (var i = 0; i < items.length; i++) {
							var item = items[i];
							var text = item.textContent.trim().toLowerCase();
							var itemVal = item.getAttribute('data-value');

							if (itemVal !== '' && text.indexOf(val) > -1) {
								hidden.value = itemVal;
								input.value = item.textContent.trim();
								form[name] = itemVal;
								matched = true;
								break;
							}
						}
					}

					// If still no match and they cleared the text
					if (!matched) {
						hidden.value = '';
						form[name] = '';
					}

					// Clear validation error if matched successfully
					if (hidden.value) {
						input.classList.remove('elig-input-error');
						var errorEl = container.querySelector('.elig-error-message');
						if (errorEl) errorEl.remove();
					}
				}, 250);
			});

			// Click item -> select value
			items.forEach(function (item) {
				item.addEventListener('click', function () {
					var val = this.getAttribute('data-value');
					var text = this.textContent;
					if (val === '') {
						input.value = '';
						hidden.value = '';
					} else {
						input.value = text;
						hidden.value = val;
					}
					// Update global form state
					var name = hidden.getAttribute('name');
					if (name) {
						form[name] = hidden.value;
					}
					dropdown.classList.add('hidden');
				});
			});
		});
	}

	function initFileUpload() {
		var fileInput = document.getElementById('degree-file-input');
		var fileLabel = document.getElementById('degree-file-label');
		if (fileInput && fileLabel) {
			fileInput.addEventListener('change', function () {
				if (fileInput.files.length > 0) {
					fileLabel.textContent = "Đã chọn: " + fileInput.files[0].name;
					fileLabel.style.color = '#10b981'; // emerald-500
				} else {
					fileLabel.textContent = "Tải ảnh hoặc PDF bằng cấp lên";
					fileLabel.style.color = '';
				}
			});
		}
	}

	// ----------------------------------------------------
	// Navigation & Submit
	// ----------------------------------------------------
	function initNavigation() {
		var submitBtn = document.getElementById('elig-unified-submit');
		if (submitBtn) {
			submitBtn.addEventListener('click', function () {
				if (validateUnifiedForm()) {
					submitCheck();
				}
			});
		}
	}

	function showError(selector, message) {
		var el = document.querySelector(selector);
		if (!el) return;

		el.classList.add('elig-input-error');

		var parent = el.closest('.space-y-2') || el.parentElement;
		var errorEl = parent.querySelector('.elig-error-message');
		if (!errorEl) {
			errorEl = document.createElement('span');
			errorEl.className = 'elig-error-message';
			errorEl.style.color = '#ef4444';
			errorEl.style.fontSize = '0.75rem';
			errorEl.style.fontWeight = '700';
			errorEl.style.marginTop = '0.25rem';
			errorEl.style.display = 'block';
			parent.appendChild(errorEl);
		}
		errorEl.textContent = message;
	}

	function clearErrors() {
		document.querySelectorAll('.elig-input-error').forEach(function (el) {
			el.classList.remove('elig-input-error');
		});
		document.querySelectorAll('.elig-error-message').forEach(function (el) {
			el.remove();
		});
	}

	function validateUnifiedForm() {
		clearErrors();
		var isValid = true;

		var educationEl = document.querySelector('select[name="education"]');
		var desiredMajorHidden = document.querySelector('input[name="desired_major"]');

		if (educationEl && !educationEl.value) {
			showError('select[name="education"]', 'Vui lòng chọn trình độ học vấn hiện tại.');
			isValid = false;
		}
		if (desiredMajorHidden && !desiredMajorHidden.value) {
			// Find visible search input inside Ngành mong muốn block
			var container = desiredMajorHidden.closest('[data-search-select]');
			if (container) {
				var searchInput = container.querySelector('.elig-search-input');
				if (searchInput) {
					searchInput.classList.add('elig-input-error');
					var errorEl = container.querySelector('.elig-error-message');
					if (!errorEl) {
						errorEl = document.createElement('span');
						errorEl.className = 'elig-error-message';
						errorEl.style.color = '#ef4444';
						errorEl.style.fontSize = '0.75rem';
						errorEl.style.fontWeight = '700';
						errorEl.style.marginTop = '0.25rem';
						errorEl.style.display = 'block';
						container.appendChild(errorEl);
					}
					errorEl.textContent = 'Vui lòng chọn ngành học mong muốn.';
				}
			}
			isValid = false;
		}

		if (!isValid) {
			var firstError = document.querySelector('.elig-input-error');
			if (firstError) {
				firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		}
		return isValid;
	}

	function submitCheck() {
		var submitBtn = document.getElementById('elig-unified-submit');
		var btnText = submitBtn.querySelector('.elig-btn-text');
		var btnLoading = submitBtn.querySelector('.elig-btn-loading');

		// Show loading
		submitBtn.disabled = true;
		if (btnText) btnText.classList.add('hidden');
		if (btnLoading) btnLoading.classList.remove('hidden');

		// Prepare data
		var data = new FormData();
		data.append('action', 'ltdh_elig_check');
		data.append('nonce', ltdh_elig.nonce);
		
		data.append('education', document.querySelector('select[name="education"]').value);
		data.append('major_id', document.querySelector('input[name="major_id"]').value || 0);
		data.append('graduation', 0);
		data.append('desired_major', document.querySelector('input[name="desired_major"]').value || 0);
		data.append('training_type', document.querySelector('select[name="training_type"]').value);
		data.append('campus', document.querySelector('select[name="campus"]').value);
		data.append('budget', '');
		data.append('previous_school', '');
		data.append('name', '');
		data.append('phone', '');
		data.append('email', '');

		fetch(ltdh_elig.ajax_url, {
			method: 'POST',
			body: data,
			credentials: 'same-origin'
		})
		.then(function (response) { return response.json(); })
		.then(function (json) {
			if (json.success) {
				results = json.data;
				renderResults(results);
				showResults();
			} else {
				alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra. Vui lòng thử lại.');
			}
		})
		.catch(function (err) {
			console.error('Eligibility check error:', err);
			alert('Có lỗi xảy ra. Vui lòng thử lại.');
		})
		.finally(function () {
			submitBtn.disabled = false;
			if (btnText) btnText.classList.remove('hidden');
			if (btnLoading) btnLoading.classList.add('hidden');
		});
	}

	function showResults() {
		var wizard = document.getElementById('elig-wizard');
		var resultsEl = document.getElementById('elig-results');
		if (wizard) wizard.classList.add('hidden');
		if (resultsEl) resultsEl.classList.remove('hidden');

		// Scroll to top
		var container = document.getElementById('eligibility-app');
		if (container) {
			container.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}

	// ----------------------------------------------------
	// Render Results
	// ----------------------------------------------------
	function renderResults(data) {
		// Header
		var header = document.getElementById('elig-results-header');
		var icon = document.getElementById('elig-results-icon');
		var title = document.getElementById('elig-results-title');
		var subtitle = document.getElementById('elig-results-subtitle');

		if (data.eligible_count > 0) {
			if (header) header.classList.remove('hidden');
			if (icon) icon.textContent = '✅';
			if (title) title.textContent = 'Tìm thấy ' + data.eligible_count + ' chương trình phù hợp!';
			if (subtitle) subtitle.textContent = 'Dưới đây là các chương trình được xếp hạng phù hợp nhất với nhu cầu của bạn.';
		} else {
			if (header) header.classList.add('hidden');
		}

		// Input Summary
		renderInputSummary(data.input);

		// Programs
		var programList = document.getElementById('elig-program-list');
		if (programList) {
			programList.innerHTML = '';
			if (data.programs && data.programs.length > 0) {
				data.programs.forEach(function (prog, idx) {
					programList.appendChild(renderProgramCard(prog, idx));
				});
				var noRes = document.getElementById('elig-no-results');
				if (noRes) noRes.classList.add('hidden');
			} else {
				var noRes2 = document.getElementById('elig-no-results');
				if (noRes2) noRes2.classList.remove('hidden');
			}
		}

		// Alternatives
		var altSection = document.getElementById('elig-alternatives-section');
		var altList = document.getElementById('elig-alternatives-list');
		if (altList) {
			altList.innerHTML = '';
			if (data.alternatives && data.alternatives.length > 0) {
				if (altSection) altSection.classList.remove('hidden');
				data.alternatives.forEach(function (prog, idx) {
					altList.appendChild(renderProgramCard(prog, idx));
				});
			} else {
				if (altSection) altSection.classList.add('hidden');
			}
		}

		// Pre-fill fields in Consultation lead capture form at the bottom
		var checkIdField = document.getElementById('elig-check-id');
		if (checkIdField) checkIdField.value = data.check_id;

		if (data.programs && data.programs.length > 0) {
			var progIdField = document.getElementById('elig-program-id');
			if (progIdField && data.programs[0]) progIdField.value = data.programs[0].program_id;
		}

		// Lead form submission
		initLeadForm();
		initCardVerifyListeners();
	}

	function renderInputSummary(input) {
		var summary = document.getElementById('elig-input-summary');
		var tags = document.getElementById('elig-summary-tags');
		if (!summary || !tags) return;

		summary.classList.remove('hidden');
		tags.innerHTML = '';

		var labels = {
			education: { 'thap-phan': 'THPT', 'trung-cap': 'Trung cấp', 'cao-dang': 'Cao đẳng', 'dai-hoc': 'Đại học', 'thac-si': 'Thạc sĩ' },
			training_type: { 'lien-thong': 'Liên thông', 'van-bang-2': 'Văn bằng 2', 'tu-xa': 'Từ xa', 'vua-hoc-vua-lam': 'Vừa học vừa làm', 'chinh-quy': 'Chính quy' },
			campus: { 'ha-noi': 'Hà Nội', 'ho-chi-minh': 'TP.HCM', 'da-nang': 'Đà Nẵng', 'thai-nguyen': 'Thái Nguyên', 'online': 'Online' }
		};

		var keys = ['education', 'training_type', 'campus'];
		keys.forEach(function (key) {
			if (input[key]) {
				var label = labels[key] && labels[key][input[key]] ? labels[key][input[key]] : input[key];
				var tag = document.createElement('span');
				tag.className = 'elig-tag';
				tag.textContent = label;
				tags.appendChild(tag);
			}
		});
	}

	function renderProgramCard(prog, idx) {
		var statusClass = prog.preliminary_status === 'compatible' ? 'elig-status-compatible' : (prog.preliminary_status === 'needs_verification' ? 'elig-status-verification' : 'elig-status-incompatible');
		var statusLabel = prog.preliminary_status === 'compatible' ? 'Độ tương thích tốt' : (prog.preliminary_status === 'needs_verification' ? 'Cần xác minh hồ sơ' : 'Chưa tương thích');
		var matchPriorityLabel = prog.score >= 80 ? 'Ưu tiên cao' : (prog.score >= 50 ? 'Phù hợp tốt' : 'Lựa chọn tham khảo');

		var schoolHtml = '';
		if (prog.school) {
			schoolHtml = '<div class="elig-card-school">';
			if (prog.school.logo) {
				schoolHtml += '<img src="' + escAttr(prog.school.logo) + '" alt="" class="elig-card-school-logo">';
			}
			schoolHtml += '<span>' + escHtml(prog.school.title) + '</span></div>';
		}

		var reasonsHtml = '<div class="elig-reasons-container" style="margin-top: 10px; font-size: 0.85rem; line-height: 1.4;">';
		if (prog.match_reasons && prog.match_reasons.length > 0) {
			prog.match_reasons.forEach(function (reason) {
				reasonsHtml += '<div class="elig-reason-item text-emerald-600" style="color: #059669; margin-bottom: 3px;">✓ ' + escHtml(reason) + '</div>';
			});
		}
		if (prog.verification_items && prog.verification_items.length > 0) {
			prog.verification_items.forEach(function (item) {
				reasonsHtml += '<div class="elig-reason-item text-amber-600" style="color: #d97706; margin-bottom: 3px;">⚠ ' + escHtml(item) + '</div>';
			});
		}
		if (prog.mismatch_reasons && prog.mismatch_reasons.length > 0) {
			prog.mismatch_reasons.forEach(function (reason) {
				reasonsHtml += '<div class="elig-reason-item text-rose-600" style="color: #e11d48; margin-bottom: 3px;">✗ ' + escHtml(reason) + '</div>';
			});
		}
		reasonsHtml += '</div>';

		var card = document.createElement('div');
		card.className = 'elig-card';
		card.innerHTML =
			'<div class="elig-card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">' +
				'<div class="elig-card-rank">#' + (idx + 1) + '</div>' +
				'<div class="elig-card-status ' + statusClass + '" style="font-weight: bold; font-size: 0.85rem;">' +
					'<span class="elig-score-label" style="display: inline-block; padding: 4px 8px; border-radius: 4px;">' + statusLabel + ' (' + matchPriorityLabel + ')</span>' +
				'</div>' +
			'</div>' +
			'<div class="elig-card-body">' +
				'<div class="elig-card-info">' +
					schoolHtml +
					'<h3 class="elig-card-title" style="margin-top: 8px; font-weight: 800; font-size: 1.1rem;"><a href="' + escAttr(prog.permalink) + '" style="text-decoration: none; color: #1e293b;">' + escHtml(prog.title) + '</a></h3>' +
					'<div class="elig-card-meta" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 10px; font-size: 0.85rem; color: #64748b;">' +
						'<span class="elig-meta-item">💰 ' + escHtml(prog.tuition_fee || 'Liên hệ') + '</span>' +
						'<span class="elig-meta-item">⏱ ' + escHtml(prog.duration || '—') + '</span>' +
						'<span class="elig-meta-item">📅 ' + escHtml(prog.schedule || 'Linh hoạt') + '</span>' +
						'<span class="elig-meta-item">📍 ' + escHtml(prog.campus_info || '—') + '</span>' +
					'</div>' +
				'</div>' +
				reasonsHtml +
				'<div class="elig-card-actions" style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 8px;">' +
					'<a href="' + escAttr(prog.permalink) + '" class="elig-btn elig-btn-primary elig-btn-sm" style="flex: 1; text-align: center; text-decoration: none; min-width: 120px;">Xem chương trình</a>' +
					'<a href="#elig-lead-section" class="elig-btn elig-btn-secondary elig-btn-sm elig-verify-card-btn" data-program-id="' + prog.program_id + '" style="flex: 1; text-align: center; text-decoration: none; min-width: 120px; font-weight: bold;">Kiểm tra hồ sơ 📞</a>' +
				'</div>' +
			'</div>';

		return card;
	}

	// ----------------------------------------------------
	// Progressive Lead Capture & Advanced Verification
	// ----------------------------------------------------
	var currentLeadId = null;

	function initLeadForm() {
		var formEl = document.getElementById('elig-consultation-form');
		if (!formEl) return;

		// Reset form HTML state if it was replaced previously
		formEl.style.display = 'block';

		formEl.addEventListener('submit', function (e) {
			e.preventDefault();
			var submitBtn = document.getElementById('elig-lead-submit-btn');
			if (submitBtn) submitBtn.disabled = true;

			var data = new FormData(formEl);
			data.append('action', 'ltdh_elig_lead');
			data.append('nonce', ltdh_elig.nonce);

			fetch(ltdh_elig.ajax_url, {
				method: 'POST',
				body: data,
				credentials: 'same-origin'
			})
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (json.success) {
					currentLeadId = json.data.lead_id;
					
					// Hide standard form elements and show success
					formEl.innerHTML = '<div class="elig-lead-success bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 font-bold mb-4">✅ Gửi yêu cầu thành công! Tư vấn viên sẽ liên hệ với bạn trong 24 giờ.</div>';
					
					// Show Advanced Verification section
					var advSection = document.getElementById('elig-advanced-verification-section');
					if (advSection) {
						advSection.classList.remove('hidden');
						advSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
					initAdvancedVerificationForm();
				} else {
					alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra.');
					if (submitBtn) submitBtn.disabled = false;
				}
			})
			.catch(function (err) {
				console.error('Lead capture error:', err);
				if (submitBtn) submitBtn.disabled = false;
			});
		});
	}

	function initAdvancedVerificationForm() {
		var advForm = document.getElementById('elig-advanced-verify-form');
		if (!advForm) return;

		advForm.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!currentLeadId) {
				alert('Không tìm thấy thông tin Lead để cập nhật.');
				return;
			}
			
			var submitBtn = document.getElementById('elig-advanced-submit-btn');
			if (submitBtn) submitBtn.disabled = true;

			var data = new FormData(advForm);
			data.append('action', 'ltdh_elig_advanced_verify');
			data.append('nonce', ltdh_elig.nonce);
			data.append('lead_id', currentLeadId);

			// Append file manually if selected
			var fileInput = document.getElementById('degree-file-input');
			if (fileInput && fileInput.files.length > 0) {
				data.append('degree_file', fileInput.files[0]);
			}

			fetch(ltdh_elig.ajax_url, {
				method: 'POST',
				body: data,
				credentials: 'same-origin'
			})
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (json.success) {
					advForm.innerHTML = '<div class="elig-lead-success bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 font-bold">✅ Bổ sung thông tin xác minh thành công! Chúng tôi đã cập nhật hồ sơ của bạn.</div>';
				} else {
					alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra.');
					if (submitBtn) submitBtn.disabled = false;
				}
			})
			.catch(function (err) {
				console.error('Advanced verification error:', err);
				if (submitBtn) submitBtn.disabled = false;
			});
		});
	}

	function initCardVerifyListeners() {
		// Event delegation to capture card clicks
		var container = document.getElementById('elig-results');
		if (container) {
			container.addEventListener('click', function(e) {
				var verifyBtn = e.target.closest('.elig-verify-card-btn');
				if (verifyBtn) {
					var programId = verifyBtn.getAttribute('data-program-id');
					var progIdField = document.getElementById('elig-program-id');
					if (progIdField) {
						progIdField.value = programId;
					}
					
					var cardTitleEl = verifyBtn.closest('.elig-card').querySelector('.elig-card-title');
					if (cardTitleEl) {
						var titleText = cardTitleEl.textContent;
						var contextH3 = document.querySelector('#elig-lead-section h3');
						if (contextH3) {
							contextH3.textContent = "Yêu cầu đối chiếu điều kiện nhập học cho: " + titleText;
						}
					}
				}
			});
		}
	}

	// ----------------------------------------------------
	// Results Actions
	// ----------------------------------------------------
	function initResultsActions() {
		var retry = document.getElementById('elig-retry');
		var retry2 = document.getElementById('elig-no-results-retry');

		function backToForm() {
			var wizard = document.getElementById('elig-wizard');
			var resultsEl = document.getElementById('elig-results');

			if (wizard) wizard.classList.remove('hidden');
			if (resultsEl) resultsEl.classList.add('hidden');
		}

		if (retry) retry.addEventListener('click', backToForm);
		if (retry2) retry2.addEventListener('click', backToForm);
	}

	// ----------------------------------------------------
	// Helpers
	// ----------------------------------------------------
	function escHtml(str) {
		if (!str) return '';
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

	function escAttr(str) {
		return escHtml(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}
})();
