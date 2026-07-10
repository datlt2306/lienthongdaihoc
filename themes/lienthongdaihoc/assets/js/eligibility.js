/**
 * Eligibility Checker — Frontend Logic
 *
 * Multi-step wizard, AJAX eligibility check, results display, lead capture.
 *
 * @package lienthongdaihoc
 */
(function () {
	'use strict';

	// ----------------------------------------------------
	// State
	// ----------------------------------------------------
	var currentStep = 1;
	var totalSteps = 8;
	var form = {};
	var results = null;

	// ----------------------------------------------------
	// DOM Ready
	// ----------------------------------------------------
	document.addEventListener('DOMContentLoaded', function () {
		initWizard();
		initNavigation();
		initResultsActions();
	});

	// ----------------------------------------------------
	// Wizard Init
	// ----------------------------------------------------
	function initWizard() {
		// Radio/option click handlers
		var options = document.querySelectorAll('.elig-option input[type="radio"]');
		for (var i = 0; i < options.length; i++) {
			options[i].addEventListener('change', function () {
				var name = this.getAttribute('name');
				form[name] = this.value;
				// Auto-advance after short delay
				setTimeout(function () {
					if (currentStep < totalSteps) {
						goToStep(currentStep + 1);
					}
				}, 300);
			});
		}

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
	}
	// Navigation
	// ----------------------------------------------------
	function initNavigation() {
		var prevBtn = document.getElementById('elig-prev');
		var nextBtn = document.getElementById('elig-next');
		var submitBtn = document.getElementById('elig-submit');

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				goToStep(currentStep - 1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				if (validateCurrentStep()) {
					goToStep(currentStep + 1);
				}
			});
		}

		if (submitBtn) {
			submitBtn.addEventListener('click', function () {
				if (validateCurrentStep()) {
					submitCheck();
				}
			});
		}
	}

	function validateCurrentStep() {
		if (currentStep === 1) {
			if (!form.education) {
				alert('Vui lòng chọn trình độ học vấn hiện tại.');
				return false;
			}
		}
		if (currentStep === 3) {
			if (!form.graduation) {
				alert('Vui lòng chọn năm sinh.');
				return false;
			}
		}
		if (currentStep === 4) {
			if (!form.desired_major) {
				alert('Vui lòng chọn ngành học mong muốn.');
				return false;
			}
		}
		if (currentStep === 5) {
			if (!form.training_type) {
				alert('Vui lòng chọn hệ đào tạo.');
				return false;
			}
		}
		if (currentStep === 6) {
			if (!form.campus) {
				alert('Vui lòng chọn cơ sở học.');
				return false;
			}
		}
		return true;
	}

	function goToStep(step) {
		if (step < 1 || step > totalSteps) return;

		// Hide current
		var current = document.querySelector('.elig-step.active');
		if (current) current.classList.remove('active');

		// Show new
		var target = document.querySelector('.elig-step[data-step="' + step + '"]');
		if (target) target.classList.add('active');

		currentStep = step;

		// Update progress
		updateProgress();

		// Update nav buttons
		updateNavButtons();

		// Scroll to top
		var container = document.getElementById('elig-wizard');
		if (container) {
			container.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}

	function updateProgress() {
		var fill = document.getElementById('elig-progress-fill');
		var text = document.getElementById('elig-progress-text');
		var pct = Math.round((currentStep / totalSteps) * 100);
		if (fill) fill.style.width = pct + '%';
		if (text) text.textContent = 'Bước ' + currentStep + ' / ' + totalSteps;
	}

	function updateNavButtons() {
		var prevBtn = document.getElementById('elig-prev');
		var nextBtn = document.getElementById('elig-next');
		var submitBtn = document.getElementById('elig-submit');

		if (prevBtn) {
			prevBtn.classList.toggle('hidden', currentStep === 1);
		}

		if (currentStep === totalSteps) {
			if (nextBtn) nextBtn.classList.add('hidden');
			if (submitBtn) submitBtn.classList.remove('hidden');
		} else {
			if (nextBtn) nextBtn.classList.remove('hidden');
			if (submitBtn) submitBtn.classList.add('hidden');
		}
	}

	// ----------------------------------------------------
	// Submit Eligibility Check
	// ----------------------------------------------------
	function submitCheck() {
		var submitBtn = document.getElementById('elig-submit');
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
		data.append('education', form.education || '');
		data.append('major_id', form.major_id || 0);
		data.append('graduation', form.graduation || 0);
		data.append('desired_major', form.desired_major || 0);
		data.append('training_type', form.training_type || '');
		data.append('campus', form.campus || '');
		data.append('budget', form.budget || '');
		data.append('phone', form.phone || '');
		data.append('email', form.email || '');

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

	// ----------------------------------------------------
	// Show Results
	// ----------------------------------------------------
	function showResults() {
		var wizard = document.getElementById('elig-wizard');
		var resultsEl = document.getElementById('elig-results');
		var nav = document.querySelector('.elig-nav');
		var progress = document.querySelector('.elig-progress');

		if (wizard) wizard.classList.add('hidden');
		if (resultsEl) resultsEl.classList.remove('hidden');
		if (nav) nav.classList.add('hidden');
		if (progress) progress.classList.add('hidden');

		resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

		if (header) header.classList.remove('hidden');

		if (data.eligible_count > 0) {
			if (icon) icon.textContent = '✅';
			if (title) title.textContent = 'Bạn ĐỦ ĐIỀU KIỆN cho ' + data.eligible_count + ' chương trình!';
			if (subtitle) subtitle.textContent = 'Dưới đây là các chương trình phù hợp nhất với bạn.';
		} else {
			if (icon) icon.textContent = '😔';
			if (title) title.textContent = 'Chưa tìm thấy chương trình phù hợp';
			if (subtitle) subtitle.textContent = 'Hãy thử thay đổi điều kiện hoặc liên hệ tư vấn.';
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
			}
		}

		// No results
		var noResults = document.getElementById('elig-no-results');
		if (data.eligible_count === 0 && noResults) {
			noResults.classList.remove('hidden');
		}

		// Alternatives
		var altEl = document.getElementById('elig-alternatives');
		var altList = document.getElementById('elig-alternative-list');
		if (data.alternatives && data.alternatives.length > 0 && altEl && altList) {
			altEl.classList.remove('hidden');
			altList.innerHTML = '';
			data.alternatives.forEach(function (alt) {
				var div = document.createElement('div');
				div.className = 'elig-alt-item';
				div.innerHTML = '<span class="elig-alt-title">' + escHtml(alt.title) + '</span>' +
					'<span class="elig-alt-reason">' + escHtml(alt.reason) + '</span>';
				altList.appendChild(div);
			});
		}

		// Documents
		var docs = document.getElementById('elig-documents');
		if (docs && data.eligible_count > 0) {
			docs.classList.remove('hidden');
		}

		// Lead form
		var leadForm = document.getElementById('elig-lead-form');
		if (leadForm && data.eligible_count > 0) {
			leadForm.classList.remove('hidden');
			var checkIdField = document.getElementById('elig-check-id');
			if (checkIdField) checkIdField.value = data.check_id || '';
			var progIdField = document.getElementById('elig-program-id');
			if (progIdField && data.programs[0]) progIdField.value = data.programs[0].program_id;
		}

		// Lead form submission
		initLeadForm();
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
			campus: { 'ha-noi': 'Hà Nội', 'ho-chi-minh': 'TP.HCM', 'da-nang': 'Đà Nẵng', 'thai-nguyen': 'Thái Nguyên', 'online': 'Online' },
			budget: { 'duoi-20-trieu': 'Dưới 20M', '20-30-trieu': '20-30M', '30-50-trieu': '30-50M', 'tren-50-trieu': 'Trên 50M' }
		};

		var keys = ['education', 'training_type', 'campus', 'budget'];
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
		var scoreClass = prog.score >= 90 ? 'perfect' : (prog.score >= 70 ? 'very-good' : (prog.score >= 50 ? 'possible' : 'not-match'));
		var scoreLabel = prog.score >= 90 ? 'Phù hợp hoàn hảo' : (prog.score >= 70 ? 'Phù hợp tốt' : (prog.score >= 50 ? 'Có thể phù hợp' : 'Chưa phù hợp'));

		var schoolHtml = '';
		if (prog.school) {
			schoolHtml = '<div class="elig-card-school">';
			if (prog.school.logo) {
				schoolHtml += '<img src="' + escAttr(prog.school.logo) + '" alt="" class="elig-card-school-logo">';
			}
			schoolHtml += '<span>' + escHtml(prog.school.title) + '</span></div>';
		}

		var breakdownHtml = '';
		if (prog.breakdown) {
			Object.keys(prog.breakdown).forEach(function (key) {
				var b = prog.breakdown[key];
				if (b.score > 0) {
					breakdownHtml += '<span class="elig-badge elig-badge-pass">✅ ' + escHtml(b.label) + '</span>';
				}
			});
		}

		var card = document.createElement('div');
		card.className = 'elig-card';
		card.innerHTML =
			'<div class="elig-card-header">' +
				'<div class="elig-card-rank">#' + (idx + 1) + '</div>' +
				'<div class="elig-card-score ' + scoreClass + '">' +
					'<span class="elig-score-num">' + prog.score + '%</span>' +
					'<span class="elig-score-label">' + scoreLabel + '</span>' +
				'</div>' +
			'</div>' +
			'<div class="elig-card-body">' +
				'<div class="elig-card-info">' +
					schoolHtml +
					'<h3 class="elig-card-title"><a href="' + escAttr(prog.permalink) + '">' + escHtml(prog.title) + '</a></h3>' +
					'<div class="elig-card-meta">' +
						'<span class="elig-meta-item">💰 ' + escHtml(prog.tuition_fee || 'Liên hệ') + '</span>' +
						'<span class="elig-meta-item">⏱ ' + escHtml(prog.duration || '—') + '</span>' +
						'<span class="elig-meta-item">📅 ' + escHtml(prog.schedule || 'Linh hoạt') + '</span>' +
						'<span class="elig-meta-item">📍 ' + escHtml(prog.campus_info || '—') + '</span>' +
					'</div>' +
				'</div>' +
				'<div class="elig-card-badges">' + breakdownHtml + '</div>' +
				'<div class="elig-card-actions">' +
					'<a href="' + escAttr(prog.permalink) + '" class="elig-btn elig-btn-primary elig-btn-sm">Chi tiết →</a>' +
					'<button type="button" class="elig-btn elig-btn-secondary elig-btn-sm elig-compare-btn" ' +
						'data-compare-type="program" data-compare-id="' + prog.program_id + '" ' +
						'data-compare-title="' + escAttr(prog.title) + '" ' +
						'data-compare-slug="' + escAttr((prog.permalink || '').split('/').filter(Boolean).pop()) + '">' +
						'📊 So sánh</button>' +
				'</div>' +
			'</div>';

		return card;
	}

	// ----------------------------------------------------
	// Lead Form
	// ----------------------------------------------------
	function initLeadForm() {
		var form = document.getElementById('elig-consultation-form');
		if (!form) return;

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var data = new FormData(form);
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
					form.innerHTML = '<div class="elig-lead-success">✅ Đăng ký thành công! Tư vấn viên sẽ liên hệ trong 24 giờ.</div>';
				} else {
					alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra.');
				}
			});
		});
	}

	// ----------------------------------------------------
	// Results Actions
	// ----------------------------------------------------
	function initResultsActions() {
		// Retry buttons
		var retry = document.getElementById('elig-retry');
		var retry2 = document.getElementById('elig-no-results-retry');

		function backToWizard() {
			var wizard = document.getElementById('elig-wizard');
			var resultsEl = document.getElementById('elig-results');
			var nav = document.querySelector('.elig-nav');
			var progress = document.querySelector('.elig-progress');

			if (wizard) wizard.classList.remove('hidden');
			if (resultsEl) resultsEl.classList.add('hidden');
			if (nav) nav.classList.remove('hidden');
			if (progress) progress.classList.remove('hidden');

			goToStep(1);
		}

		if (retry) retry.addEventListener('click', backToWizard);
		if (retry2) retry2.addEventListener('click', backToWizard);
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
