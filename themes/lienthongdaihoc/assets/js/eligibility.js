/**
 * Eligibility Checker — Unified Single-Page Form Frontend Logic (2026–2027 Regulations)
 *
 * Implements 6 core fields, smart branching, real-time month calculation,
 * 3-state decision rendering (Eligible / Urgent Dec 2026 / Not Eligible 2027),
 * and complete flow tracking into CRM/Telegram payloads.
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
	var currentLeadId = null;
	var currentLeadToken = '';

	// ----------------------------------------------------
	// DOM Ready
	// ----------------------------------------------------
	function initAll() {
		initForm();
		initBranchingLogic();
		initNavigation();
		initResultsActions();
		initFileUpload();
		initShareAndCopy();
		initUrlAndInitialResults();
		initCompactWidgets();
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
				var parent = el.closest('.space-y-1\\.5') || el.closest('.space-y-2') || el.parentElement;
				var errorEl = parent.querySelector('.elig-error-message');
				if (errorEl) errorEl.remove();
			};
			unifiedForm.addEventListener('input', clearHandler);
			unifiedForm.addEventListener('change', clearHandler);
		}

		// Searchable autocomplete selects
		initSearchSelects();
	}

	// ----------------------------------------------------
	// Smart Branching Logic (Conditional Fields & Real-time Months)
	// ----------------------------------------------------
	function initBranchingLogic() {
		var eduSelect = document.getElementById('field-education');
		var thptWrap  = document.getElementById('field-thpt-status-wrap');
		var thptSelect = document.getElementById('field-thpt-status');
		var gradWrap  = document.getElementById('field-graduation-date-wrap');
		var gradReqIndicator = document.getElementById('grad-required-indicator');
		var gradMonth = document.getElementById('field-grad-month');
		var gradYear  = document.getElementById('field-grad-year');
		var monthsBadge = document.getElementById('grad-months-badge');

		function updateBranching() {
			var edu = eduSelect ? eduSelect.value : 'cao-dang';
			var thpt = thptSelect ? thptSelect.value : 'co-bang-thpt';

			// Tình trạng bằng THPT: Chỉ hiện khi chọn Trung cấp hoặc Cao đẳng
			if (thptWrap) {
				if (edu === 'trung-cap' || edu === 'cao-dang') {
					thptWrap.classList.remove('hidden');
				} else {
					thptWrap.classList.add('hidden');
				}
			}

			// Cao đẳng + Chưa có bằng THPT -> Bắt buộc nhập thời điểm tốt nghiệp để tính mốc 24 tháng
			if (gradReqIndicator) {
				if (edu === 'cao-dang' && thpt === 'chua-co-giay-van-hoa-thpt') {
					gradReqIndicator.classList.remove('hidden');
				} else {
					gradReqIndicator.classList.add('hidden');
				}
			}

			// Real-time calculation of elapsed months
			if (monthsBadge && gradYear) {
				var y = parseInt(gradYear.value, 10);
				var m = gradMonth ? parseInt(gradMonth.value, 10) : 6;
				if (!isNaN(y) && y > 0) {
					// Reference is current date: November 2026 (local site context: 2026-11)
					var refYear = 2026;
					var refMonth = 11;
					var diff = (refYear - y) * 12 + (refMonth - m);
					if (diff < 0) diff = 0;

					if (edu === 'cao-dang' && thpt === 'chua-co-giay-van-hoa-thpt') {
						if (diff >= 24) {
							monthsBadge.innerHTML = '<svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span>Đã tốt nghiệp ' + diff + ' tháng · Đạt chuẩn ≥ 24 tháng</span>';
							monthsBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-full shadow-2xs whitespace-nowrap';
						} else {
							monthsBadge.innerHTML = '<svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg><span>Mới tốt nghiệp ' + diff + ' tháng · Chưa đủ 24 tháng</span>';
							monthsBadge.className = 'inline-flex items-center gap-1.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200/80 px-2.5 py-1 rounded-full shadow-2xs whitespace-nowrap';
						}
					} else {
						monthsBadge.innerHTML = '<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Thời gian từ tốt nghiệp: ~' + diff + ' tháng</span>';
						monthsBadge.className = 'inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-full shadow-2xs whitespace-nowrap';
					}
				} else {
					monthsBadge.innerHTML = '';
					monthsBadge.className = 'hidden';
				}
			}
		}

		if (eduSelect) {
			eduSelect.addEventListener('change', updateBranching);
		}
		if (thptSelect) {
			thptSelect.addEventListener('change', updateBranching);
		}
		if (gradMonth) {
			gradMonth.addEventListener('change', updateBranching);
		}
		if (gradYear) {
			gradYear.addEventListener('change', updateBranching);
		}

		// Initial run
		updateBranching();
	}

	function removeVietnameseAccents(str) {
		if (!str) return '';
		return str
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.replace(/đ/g, 'd')
			.replace(/Đ/g, 'D');
	}

	function initSearchSelects() {
		var containers = document.querySelectorAll('[data-search-select]');
		containers.forEach(function (container) {
			var input = container.querySelector('.elig-search-input');
			var hidden = container.querySelector('.elig-search-value');
			var dropdown = container.querySelector('.elig-search-dropdown');
			var items = Array.prototype.slice.call(container.querySelectorAll('.elig-search-option-item'));

			if (!input || !dropdown) return;

			var activeIndex = -1;
			var justSelected = false;

			function getVisibleItems() {
				return items.filter(function (item) {
					return item.style.display !== 'none';
				});
			}

			function setActiveItem(index) {
				var visibleItems = getVisibleItems();
				items.forEach(function (it) {
					it.classList.remove('is-active', 'active', 'bg-blue-50', 'text-[#00308b]', 'font-bold');
				});

				if (index >= 0 && index < visibleItems.length) {
					activeIndex = index;
					var activeEl = visibleItems[index];
					activeEl.classList.add('is-active', 'active', 'bg-blue-50', 'text-[#00308b]', 'font-bold');

					var itemTop = activeEl.offsetTop;
					var itemBottom = itemTop + activeEl.offsetHeight;
					var dropTop = dropdown.scrollTop;
					var dropHeight = dropdown.clientHeight;

					if (itemTop < dropTop) {
						dropdown.scrollTop = itemTop;
					} else if (itemBottom > dropTop + dropHeight) {
						dropdown.scrollTop = itemBottom - dropHeight;
					}
				} else {
					activeIndex = -1;
				}
			}

			function openDropdown() {
				document.querySelectorAll('.elig-search-dropdown').forEach(function (d) {
					if (d !== dropdown) {
						d.classList.add('hidden');
					}
				});
				dropdown.classList.remove('hidden');

				var visibleItems = getVisibleItems();
				var currentIndex = -1;
				if (hidden.value) {
					currentIndex = visibleItems.findIndex(function (it) {
						return it.getAttribute('data-value') === hidden.value;
					});
				}
				if (currentIndex >= 0) {
					setActiveItem(currentIndex);
				} else {
					setActiveItem(-1);
				}
			}

			function closeDropdown() {
				dropdown.classList.add('hidden');
				setActiveItem(-1);
			}

			function selectItem(item) {
				if (!item) return;
				var val = item.getAttribute('data-value') || '';
				var text = item.textContent.trim();

				justSelected = true;
				setTimeout(function () {
					justSelected = false;
				}, 350);

				if (val === '') {
					input.value = '';
					hidden.value = '';
				} else {
					input.value = text;
					hidden.value = val;
				}

				var name = hidden.getAttribute('name');
				if (name) {
					form[name] = hidden.value;
				}

				if (hidden.value) {
					input.classList.remove('elig-input-error');
					var errorEl = container.querySelector('.elig-error-message');
					if (errorEl) errorEl.remove();
				}

				closeDropdown();
				hidden.dispatchEvent(new Event('change', { bubbles: true }));
			}

			input.addEventListener('focus', function () {
				openDropdown();
			});

			document.addEventListener('click', function (e) {
				if (!container.contains(e.target)) {
					closeDropdown();
				}
			});

			input.addEventListener('keydown', function (e) {
				var isDropdownOpen = !dropdown.classList.contains('hidden');
				var visibleItems = getVisibleItems();

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					if (!isDropdownOpen) {
						openDropdown();
						visibleItems = getVisibleItems();
						if (visibleItems.length > 0) {
							var defaultIndex = visibleItems.findIndex(function (it) {
								return it.getAttribute('data-value') !== '';
							});
							setActiveItem(defaultIndex >= 0 ? defaultIndex : 0);
						}
					} else if (visibleItems.length > 0) {
						var nextIdx = activeIndex + 1;
						if (nextIdx >= visibleItems.length) {
							nextIdx = 0;
						}
						setActiveItem(nextIdx);
					}
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					if (!isDropdownOpen) {
						openDropdown();
						visibleItems = getVisibleItems();
						if (visibleItems.length > 0) {
							setActiveItem(visibleItems.length - 1);
						}
					} else if (visibleItems.length > 0) {
						var prevIdx = activeIndex - 1;
						if (prevIdx < 0) {
							prevIdx = visibleItems.length - 1;
						}
						setActiveItem(prevIdx);
					}
				} else if (e.key === 'Enter') {
					if (isDropdownOpen) {
						e.preventDefault();
						if (activeIndex >= 0 && activeIndex < visibleItems.length) {
							selectItem(visibleItems[activeIndex]);
						} else if (visibleItems.length > 0) {
							var candidate = visibleItems.find(function (it) {
								return it.getAttribute('data-value') !== '';
							}) || visibleItems[0];
							selectItem(candidate);
						}
					}
				} else if (e.key === 'Escape') {
					if (isDropdownOpen) {
						e.preventDefault();
						closeDropdown();
					}
				} else if (e.key === 'Tab') {
					if (isDropdownOpen) {
						if (activeIndex >= 0 && activeIndex < visibleItems.length) {
							selectItem(visibleItems[activeIndex]);
						} else {
							closeDropdown();
						}
					}
				}
			});

			input.addEventListener('input', function () {
				var query = this.value.toLowerCase().trim();
				var queryFolded = removeVietnameseAccents(query);

				items.forEach(function (item) {
					var val = item.getAttribute('data-value') || '';
					var text = item.textContent.toLowerCase();
					var textFolded = removeVietnameseAccents(text);

					if (val === '' || text.indexOf(query) > -1 || textFolded.indexOf(queryFolded) > -1) {
						item.style.display = 'block';
					} else {
						item.style.display = 'none';
					}
				});

				if (dropdown.classList.contains('hidden')) {
					openDropdown();
				}

				var visibleItems = getVisibleItems();
				if (query !== '' && visibleItems.length > 0) {
					var firstRealIdx = visibleItems.findIndex(function (it) {
						return it.getAttribute('data-value') !== '';
					});
					setActiveItem(firstRealIdx >= 0 ? firstRealIdx : 0);
				} else {
					setActiveItem(-1);
				}
			});

			items.forEach(function (item) {
				item.addEventListener('mouseenter', function () {
					var visibleItems = getVisibleItems();
					var idx = visibleItems.indexOf(item);
					if (idx >= 0) {
						setActiveItem(idx);
					}
				});

				item.addEventListener('click', function () {
					selectItem(this);
				});
			});

			input.addEventListener('blur', function () {
				setTimeout(function () {
					if (justSelected) return;

					var val = input.value.trim().toLowerCase();
					var valFolded = removeVietnameseAccents(val);
					var name = hidden.getAttribute('name');
					var matched = false;

					items.forEach(function (item) {
						var text = item.textContent.trim().toLowerCase();
						var textFolded = removeVietnameseAccents(text);
						var itemVal = item.getAttribute('data-value');

						if (itemVal !== '' && (text === val || textFolded === valFolded)) {
							hidden.value = itemVal;
							input.value = item.textContent.trim();
							form[name] = itemVal;
							matched = true;
						}
					});

					if (!matched && val !== '') {
						for (var i = 0; i < items.length; i++) {
							var item = items[i];
							var text = item.textContent.trim().toLowerCase();
							var textFolded = removeVietnameseAccents(text);
							var itemVal = item.getAttribute('data-value');

							if (itemVal !== '' && (text.indexOf(val) > -1 || textFolded.indexOf(valFolded) > -1)) {
								hidden.value = itemVal;
								input.value = item.textContent.trim();
								form[name] = itemVal;
								matched = true;
								break;
							}
						}
					}

					if (!matched) {
						hidden.value = '';
						form[name] = '';
					}

					if (hidden.value) {
						input.classList.remove('elig-input-error');
						var errorEl = container.querySelector('.elig-error-message');
						if (errorEl) errorEl.remove();
					}
				}, 250);
			});
		});
	}

	function initFileUpload() {
		var degreeInput = document.getElementById('degree-file-input');
		var degreeLabel = document.getElementById('degree-file-label');
		if (degreeInput && degreeLabel && degreeInput.dataset.bound !== 'true') {
			degreeInput.dataset.bound = 'true';
			degreeInput.addEventListener('change', function () {
				if (degreeInput.files && degreeInput.files.length > 0) {
					degreeLabel.textContent = 'Đã chọn: ' + degreeInput.files[0].name;
					degreeLabel.style.color = '#10b981';
				} else {
					degreeLabel.textContent = 'Tải ảnh hoặc PDF bằng cấp lên';
					degreeLabel.style.color = '';
				}
			});
		}

		var transInput = document.getElementById('transcript-file-input');
		var transLabel = document.getElementById('transcript-file-label');
		if (transInput && transLabel && transInput.dataset.bound !== 'true') {
			transInput.dataset.bound = 'true';
			transInput.addEventListener('change', function () {
				if (transInput.files && transInput.files.length > 0) {
					transLabel.textContent = 'Đã chọn: ' + transInput.files[0].name;
					transLabel.style.color = '#10b981';
				} else {
					transLabel.textContent = 'Tải ảnh hoặc PDF bảng điểm lên';
					transLabel.style.color = '';
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

		var parent = el.closest('.space-y-1\\.5') || el.closest('.space-y-2') || el.parentElement;
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

		var educationEl = document.getElementById('field-education');
		var thptSelect  = document.getElementById('field-thpt-status');
		var gradYearEl  = document.getElementById('field-grad-year');
		var desiredMajorHidden = document.querySelector('input[name="desired_major"]');

		// 1. Trình độ học vấn
		if (educationEl && !educationEl.value) {
			showError('#field-education', 'Vui lòng chọn trình độ học vấn hiện tại.');
			isValid = false;
		}

		// 2. Tình trạng bằng THPT (Chỉ bắt buộc khi chọn Trung cấp hoặc Cao đẳng)
		var eduVal = educationEl ? educationEl.value : '';
		if (eduVal === 'trung-cap' || eduVal === 'cao-dang') {
			if (thptSelect && !thptSelect.value) {
				showError('#field-thpt-status', 'Vui lòng chọn tình trạng bằng THPT của bạn.');
				isValid = false;
			}
		}

		// 3. Năm tốt nghiệp (Đặc biệt bắt buộc khi Cao đẳng + Chưa có bằng THPT)
		if (eduVal === 'cao-dang' && thptSelect && thptSelect.value === 'chua-co-giay-van-hoa-thpt') {
			if (!gradYearEl || !gradYearEl.value) {
				showError('#field-grad-year', 'Trường hợp Cao đẳng chưa có bằng THPT bắt buộc phải nhập năm tốt nghiệp để đối chiếu mốc 24 tháng.');
				isValid = false;
			}
		}

		// 4. Ngành mong muốn
		if (desiredMajorHidden && !desiredMajorHidden.value) {
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
					errorEl.textContent = 'Vui lòng chọn ngành đào tạo mong muốn.';
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

		submitBtn.disabled = true;
		if (btnText) btnText.classList.add('hidden');
		if (btnLoading) btnLoading.classList.remove('hidden');

		var data = new FormData();
		data.append('action', 'ltdh_elig_check');
		data.append('nonce', ltdh_elig.nonce);
		
		var eduEl          = document.getElementById('field-education');
		var thptEl         = document.getElementById('field-thpt-status');
		var gradMonthEl    = document.getElementById('field-grad-month');
		var gradYearEl     = document.getElementById('field-grad-year');
		var gradGradeEl    = document.getElementById('field-graduation-grade');
		var relationEl     = document.getElementById('field-major-relation');
		var timePrefEl     = document.getElementById('field-time-preference');
		var desiredMajorEl = document.querySelector('input[name="desired_major"]');
		var campusEl       = document.querySelector('select[name="campus"]');

		var educationVal   = eduEl ? eduEl.value : 'cao-dang';
		var thptVal        = (educationVal === 'trung-cap' || educationVal === 'cao-dang') ? (thptEl ? thptEl.value : 'co-bang-thpt') : '';
		var gradMonthVal   = gradMonthEl ? gradMonthEl.value : '6';
		var gradYearVal    = gradYearEl ? gradYearEl.value : '2024';

		data.append('education', educationVal);
		data.append('thpt_status', thptVal);
		data.append('grad_month', gradMonthVal);
		data.append('grad_year', gradYearVal);
		data.append('graduation', gradYearVal);
		data.append('graduation_grade', gradGradeEl ? gradGradeEl.value : 'kha');
		data.append('major_relation', relationEl ? relationEl.value : 'dung-nganh');
		data.append('study_time_preference', timePrefEl ? timePrefEl.value : 'online-100');
		data.append('desired_major', desiredMajorEl ? (desiredMajorEl.value || 0) : 0);
		data.append('campus', campusEl ? campusEl.value : '');

		fetch(ltdh_elig.ajax_url, {
			method: 'POST',
			body: data,
			credentials: 'same-origin'
		})
		.then(function (response) { return response.json(); })
		.then(function (json) {
			if (json.success) {
				results = json.data;
				var shareKey = results && (results.share_token || results.check_id);
				if (shareKey && window.history && window.history.pushState) {
					var newUrl = window.location.pathname + '?kq=' + encodeURIComponent(shareKey);
					window.history.pushState({ kq: shareKey }, '', newUrl);
				}
				renderResults(results);
				showResults();
			} else {
				alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra. Vui lòng thử lại.');
			}
		})
		.catch(function (err) {
			console.error('Eligibility check error:', err);
			alert(err && err.message ? err.message : 'Có lỗi xảy ra. Vui lòng thử lại.');
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
		if (resultsEl) {
			resultsEl.classList.remove('hidden');
			if (window.ltdhRenderRecaptchaWidgets) {
				window.ltdhRenderRecaptchaWidgets();
			}
		}

		var container = document.getElementById('eligibility-app');
		if (container) {
			container.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}

	// ----------------------------------------------------
	// Render Results with 3 Decision States
	// ----------------------------------------------------
	function renderResults(data) {
		if (!data) return;

		var shareKey = data.share_token || data.check_id;
		if (shareKey && window.history && window.history.pushState) {
			var currentParams = new URLSearchParams(window.location.search);
			if (currentParams.get('kq') !== String(shareKey)) {
				var newUrl = window.location.pathname + '?kq=' + encodeURIComponent(shareKey);
				window.history.pushState({ kq: shareKey }, '', newUrl);
			}
		}

		var decision = data.decision || {};
		var status = decision.decision_status || 'eligible'; // 'eligible' | 'warning_urgent' | 'not_eligible' | 'not_eligible_2027'
		var normalizedStatus = status.replace(/_/g, '-');

		// Header Hero Card: Visual Status 1 (Eligible - Green), 2 (Urgent/Warning - Amber), 3 (Not eligible 2027 - Red)
		var heroCard = document.getElementById('elig-results-header');
		var badge = document.getElementById('elig-decision-badge');
		var badgeText = document.getElementById('elig-decision-badge-text');
		var urgentBadge = document.getElementById('elig-urgent-deadline-badge');
		var title = document.getElementById('elig-results-title');
		var summary = document.getElementById('elig-decision-summary');
		var remedyBox = document.getElementById('elig-remedy-box');
		var remedyContent = document.getElementById('elig-remedy-content');
		var timeNote = document.getElementById('elig-time-filter-note');
		var creditNote = document.getElementById('elig-credit-transfer-note');

		if (heroCard) {
			heroCard.classList.remove('hidden', 'status-eligible', 'status-warning-urgent', 'status-not-eligible', 'status-not_eligible', 'status-not_eligible_2027', 'status-not-eligible-2027');
			heroCard.classList.add('status-' + normalizedStatus);
			heroCard.classList.add('status-' + status);

			if (badge) {
				badge.className = 'elig-badge';
				if (status === 'eligible') {
					badge.classList.add('elig-badge-eligible');
				} else if (status === 'warning_urgent') {
					badge.classList.add('elig-badge-urgent');
				} else {
					badge.classList.add('elig-badge-not-eligible');
				}
			}

			if (badgeText) {
				badgeText.textContent = decision.badge_text || 'Kết quả đối chiếu';
			}

			if (urgentBadge) {
				if (decision.urgent_deadline || status === 'warning_urgent') {
					urgentBadge.classList.remove('hidden');
				} else {
					urgentBadge.classList.add('hidden');
				}
			}

			if (title) {
				title.textContent = decision.decision_title || 'Kết quả đối chiếu điều kiện nhập học';
			}

			if (summary) {
				summary.innerHTML = decision.decision_summary || 'Hồ sơ đã được đối chiếu quy chuẩn theo các quy chế tuyển sinh hiện hành.';
			}

			if (remedyBox && remedyContent) {
				if (decision.remedy_solution) {
					remedyContent.innerHTML = decision.remedy_solution;
					remedyBox.classList.remove('hidden');
				} else {
					remedyBox.classList.add('hidden');
				}
			}

			if (timeNote && decision.time_filter_note) {
				timeNote.textContent = decision.time_filter_note;
			}
			if (creditNote && decision.credit_transfer_note) {
				creditNote.textContent = decision.credit_transfer_note;
			}
		}

		// Input Summary Tags
		renderInputSummary(data.input, decision);

		// Program Recommendations
		var programList = document.getElementById('elig-program-list');
		var noRes = document.getElementById('elig-no-results');
		var progHeading = document.getElementById('elig-programs-heading');
		var progCountBadge = document.getElementById('elig-programs-count-badge');

		if (programList) {
			programList.innerHTML = '';
			if (data.programs && data.programs.length > 0) {
				if (progHeading) progHeading.classList.remove('hidden');
				if (progCountBadge) progCountBadge.textContent = data.programs.length + ' chương trình';
				data.programs.forEach(function (prog, idx) {
					programList.appendChild(renderProgramCard(prog, idx));
				});
				if (noRes) noRes.classList.add('hidden');
			} else {
				if (progHeading) progHeading.classList.add('hidden');
				if (noRes) {
					if (data.alternatives && data.alternatives.length > 0) {
						noRes.classList.add('hidden');
					} else {
						noRes.classList.remove('hidden');
					}
				}
			}
		}

		// Smart Alternatives Section
		var altSection = document.getElementById('elig-alternatives-section');
		var altList = document.getElementById('elig-alternatives-list');
		if (altList) {
			altList.innerHTML = '';
			if (data.alternatives && data.alternatives.length > 0) {
				if (altSection) altSection.classList.remove('hidden');
				data.alternatives.forEach(function (prog, idx) {
					altList.appendChild(renderAlternativeCard(prog, idx));
				});
			} else {
				if (altSection) altSection.classList.add('hidden');
			}
		}

		// Pre-fill hidden fields in Consultation Form for complete payload tracking
		var checkIdFields = document.querySelectorAll('#elig-lead-section input[name="elig_check_id"], #elig-check-id');
		checkIdFields.forEach(function (f) { f.value = data.check_id || data.share_token || ''; });

		var topProg = (data.programs && data.programs[0]) || (data.alternatives && data.alternatives[0]);
		var selectedProgTitleEl = document.getElementById('elig-selected-program-title');
		var progIdFields = document.querySelectorAll('#elig-lead-section input[name="elig_program_id"], #elig-program-id');

		if (topProg) {
			progIdFields.forEach(function (f) { f.value = topProg.program_id; });
			if (selectedProgTitleEl) {
				var schoolTitle = (topProg.school && topProg.school.title) ? topProg.school.title : '';
				selectedProgTitleEl.textContent = (schoolTitle ? (schoolTitle + ' — ') : '') + topProg.title;
			}
		} else {
			if (selectedProgTitleEl) {
				selectedProgTitleEl.textContent = 'Thẩm định hồ sơ liên thông đại học theo tiêu chuẩn 2026–2027';
			}
		}

		// Populate all 6 flow-tracking fields into the lead form
		setFieldValue('elig-eval-education', data.input ? data.input.education : '');
		setFieldValue('elig-eval-thpt-status', data.input ? data.input.thpt_status : '');
		setFieldValue('elig-eval-grad-month', data.input ? data.input.grad_month : '');
		setFieldValue('elig-eval-grad-year', data.input ? data.input.grad_year : '');
		setFieldValue('elig-eval-months-since-grad', decision.months_since_grad || 0);
		setFieldValue('elig-eval-graduation-grade', data.input ? data.input.graduation_grade : '');
		setFieldValue('elig-eval-major-relation', data.input ? data.input.major_relation : '');
		setFieldValue('elig-eval-time-preference', data.input ? data.input.study_time_preference : '');
		setFieldValue('elig-eval-decision-status', decision.decision_status || '');
		setFieldValue('elig-eval-priority-level', decision.priority_level || 'standard');
		setFieldValue('elig-eval-suitable-training-types', (decision.suitable_training_types || []).join(', '));

		// Pre-fill graduation year in lead form
		var leadGradYear = document.getElementById('elig-lead-graduation');
		if (leadGradYear && data.input && data.input.grad_year) {
			leadGradYear.value = data.input.grad_year;
		}

		initLeadForm();
		initFileUpload();
		initCardVerifyListeners();
	}

	function setFieldValue(id, val) {
		var el = document.getElementById(id);
		if (el) el.value = val;
	}

	function renderInputSummary(input, decision) {
		var summary = document.getElementById('elig-input-summary');
		var tags = document.getElementById('elig-summary-tags');
		if (!summary || !tags) return;

		summary.classList.remove('hidden');
		tags.innerHTML = '';

		var eduLabels = {
			'cao-dang': 'Cao đẳng',
			'trung-cap': 'Trung cấp'
		};

		var thptLabels = {
			'co-bang-thpt': 'Đã có bằng THPT',
			'co-giay-van-hoa-thpt': 'Có Giấy chứng nhận văn hóa THPT',
			'chua-co-giay-van-hoa-thpt': 'Chưa có bằng/chứng nhận THPT'
		};

		var gradeLabels = {
			'xuat-sac-gioi': 'Học lực: Xuất sắc / Giỏi',
			'kha': 'Học lực: Khá',
			'trung-binh': 'Học lực: Trung bình'
		};

		var relationLabels = {
			'dung-nganh': 'Đúng ngành',
			'nganh-gan': 'Ngành gần',
			'trai-nganh': 'Trái ngành'
		};

		var timeLabels = {
			'gio-hanh-chinh': 'Thời gian: Giờ hành chính tại trường',
			'ngoai-gio-cuoi-tuan': 'Thời gian: Ngoài giờ / Cuối tuần',
			'online-100': 'Thời gian: Online 100%'
		};

		// 1. Trình độ
		if (input && input.education) {
			var eduTag = document.createElement('span');
			eduTag.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 text-[#00308b] text-xs font-bold border border-blue-100';
			eduTag.textContent = 'Trình độ: ' + (eduLabels[input.education] || input.education);
			tags.appendChild(eduTag);
		}

		// 2. Tình trạng THPT
		if (input && input.thpt_status && thptLabels[input.thpt_status]) {
			var thptTag = document.createElement('span');
			thptTag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200/60';
			thptTag.textContent = 'THPT: ' + thptLabels[input.thpt_status];
			tags.appendChild(thptTag);
		}

		// 3. Số tháng tốt nghiệp
		if (decision && typeof decision.months_since_grad !== 'undefined' && decision.months_since_grad > 0) {
			var gradTag = document.createElement('span');
			gradTag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200/60';
			gradTag.textContent = 'Cấp bằng: ~' + decision.months_since_grad + ' tháng trước';
			tags.appendChild(gradTag);
		}

		// 4. Học lực
		if (input && input.graduation_grade && gradeLabels[input.graduation_grade]) {
			var gradeTag = document.createElement('span');
			gradeTag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200/60';
			gradeTag.textContent = gradeLabels[input.graduation_grade];
			tags.appendChild(gradeTag);
		}

		// 5. Ngành mong muốn & mức độ liên quan
		var majorTitle = (input && input.desired_major_title) ? input.desired_major_title : '';
		if (!majorTitle) {
			var desiredMajorHidden = document.querySelector('input[name="desired_major"]');
			if (desiredMajorHidden) {
				var container = desiredMajorHidden.closest('[data-search-select]');
				if (container) {
					var searchInput = container.querySelector('.elig-search-input');
					if (searchInput && searchInput.value) {
						majorTitle = searchInput.value;
					}
				}
			}
		}

		if (majorTitle) {
			var majorTag = document.createElement('span');
			majorTag.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-100';
			majorTag.innerHTML = '<span>Ngành:</span> <span class="text-slate-900">' + escHtml(majorTitle) + '</span>' + (input && input.major_relation && relationLabels[input.major_relation] ? ' (' + relationLabels[input.major_relation] + ')' : '');
			tags.appendChild(majorTag);
		}

		// 6. Quỹ thời gian
		if (input && input.study_time_preference && timeLabels[input.study_time_preference]) {
			var timeTag = document.createElement('span');
			timeTag.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200/60';
			timeTag.textContent = timeLabels[input.study_time_preference];
			tags.appendChild(timeTag);
		}
	}

	function renderProgramCard(prog, idx) {
		var isTopPick = (idx === 0 && (prog.score >= 70 || !prog.score));
		var schoolCode = (prog.school && prog.school.code) ? prog.school.code.toUpperCase() : '';
		var schoolTitle = (prog.school && prog.school.title) ? prog.school.title : 'Trường Đại học';
		var schoolLogo = (prog.school && prog.school.logo) ? prog.school.logo : '';
		var permalink = prog.permalink || '#';

		var statusClass = prog.preliminary_status === 'compatible'
			? 'bg-blue-50 text-[#00308b] border-blue-100'
			: 'bg-slate-100 text-slate-600 border-slate-200';

		var statusLabel = prog.preliminary_status === 'compatible'
			? 'Tương thích tốt'
			: (prog.preliminary_status === 'needs_verification' ? 'Cần xác minh hồ sơ' : 'Chưa tương thích');

		var scoreHtml = (prog.score && prog.score > 0)
			? '<span class="ml-1 text-[11px] font-black opacity-80">(' + prog.score + '%)</span>'
			: '';

		var topPickBadgeHtml = isTopPick
			? '<div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-[#00308b] text-xs font-bold border border-blue-200 shrink-0">' +
				'<span>★ #1 Lựa chọn phù hợp</span>' +
			  '</div>'
			: '<div class="text-xs font-bold text-slate-400 shrink-0">#' + (idx + 1) + '</div>';

		var topPickAccentLine = isTopPick
			? '<div class="h-1 bg-[#00308b]"></div>'
			: '';

		var schoolHeaderHtml =
			'<div class="p-4 sm:p-5 pb-3 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">' +
				'<div class="flex items-center gap-3 min-w-0">' +
					(schoolLogo
						? '<div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">' +
								'<img src="' + escAttr(schoolLogo) + '" alt="' + escAttr(schoolTitle) + '" class="max-w-full max-h-full object-contain">' +
						  '</div>'
						: '<div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-[#00308b] text-white font-bold text-xs sm:text-sm flex items-center justify-center shrink-0 shadow-2xs">' +
								escHtml(schoolCode || 'ĐH') +
						  '</div>'
					) +
					'<div class="min-w-0">' +
						'<div class="font-bold text-xs sm:text-sm text-slate-800 truncate" title="' + escAttr(schoolTitle) + '">' + escHtml(schoolTitle) + '</div>' +
						(schoolCode ? '<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 mt-0.5">' + escHtml(schoolCode) + '</span>' : '') +
					'</div>' +
				'</div>' +
				'<div class="flex items-center gap-2">' +
					'<div class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border ' + statusClass + '">' +
						statusLabel + scoreHtml +
					'</div>' +
					topPickBadgeHtml +
				'</div>' +
			'</div>';

		var bentoGridHtml =
			'<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-3.5 p-3 sm:p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs">' +
				'<div>' +
					'<span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-[11px]">Học phí</span>' +
					'<span class="font-extrabold text-[#00308b] text-xs sm:text-sm block">' + escHtml(prog.tuition_fee || 'Theo quy định') + '</span>' +
				'</div>' +
				'<div>' +
					'<span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-[11px]">Thời gian</span>' +
					'<span class="font-extrabold text-slate-800 text-xs sm:text-sm block">' + escHtml(prog.duration || '1.5 – 2 năm') + '</span>' +
				'</div>' +
				'<div>' +
					'<span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-[11px]">Hình thức học</span>' +
					'<span class="font-extrabold text-slate-800 text-xs sm:text-sm block">' + escHtml(prog.training_type || 'Linh hoạt') + '</span>' +
				'</div>' +
				'<div>' +
					'<span class="text-slate-400 font-bold uppercase tracking-wider block mb-1 text-[11px]">Cơ sở</span>' +
					'<span class="font-extrabold text-slate-800 text-xs sm:text-sm block truncate" title="' + escAttr(prog.campus_info || 'Toàn quốc') + '">' + escHtml(prog.campus_info || 'Toàn quốc') + '</span>' +
				'</div>' +
			'</div>';

		var reasonsHtml = '';
		if ((prog.match_reasons && prog.match_reasons.length > 0) || (prog.verification_items && prog.verification_items.length > 0) || (prog.mismatch_reasons && prog.mismatch_reasons.length > 0)) {
			reasonsHtml = '<div class="space-y-1.5 my-3 text-xs text-slate-600 leading-relaxed">';
			if (prog.match_reasons && prog.match_reasons.length > 0) {
				prog.match_reasons.forEach(function (reason) {
					reasonsHtml += '<div class="flex items-start gap-2">' +
						'<svg class="w-4 h-4 text-[#00308b] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>' +
						'<span class="text-slate-700">' + escHtml(reason) + '</span>' +
					'</div>';
				});
			}
			if (prog.verification_items && prog.verification_items.length > 0) {
				reasonsHtml += '<div class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">' +
					'<div class="font-bold text-slate-800 flex items-center gap-1.5">' +
						'<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>' +
						'<span>Lưu ý xét duyệt hồ sơ:</span>' +
					'</div>';
				prog.verification_items.forEach(function (item) {
					reasonsHtml += '<div class="pl-5 text-slate-600">• ' + escHtml(item) + '</div>';
				});
				reasonsHtml += '</div>';
			}
			if (prog.mismatch_reasons && prog.mismatch_reasons.length > 0) {
				reasonsHtml += '<div class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">' +
					'<div class="font-bold text-slate-800 flex items-center gap-1.5">' +
						'<svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>' +
						'<span>Điểm chưa phù hợp:</span>' +
					'</div>';
				prog.mismatch_reasons.forEach(function (reason) {
					reasonsHtml += '<div class="pl-5 text-slate-600">• ' + escHtml(reason) + '</div>';
				});
				reasonsHtml += '</div>';
			}
			reasonsHtml += '</div>';
		}

		var card = document.createElement('div');
		card.className = 'elig-card bg-white border border-slate-200/80 rounded-2xl shadow-xs hover:shadow-md transition-all overflow-hidden';

		card.innerHTML =
			topPickAccentLine +
			schoolHeaderHtml +
			'<div class="p-4 sm:p-5 pt-3">' +
				'<div class="mb-2">' +
					'<h3 class="font-extrabold text-base sm:text-lg text-slate-900 leading-snug">' +
						'<a href="' + escAttr(permalink) + '" target="_blank" rel="noopener" class="elig-card-title hover:text-[#00308b] transition-colors no-underline text-slate-900 inline-flex items-center gap-1.5 group">' +
							'<span>' + escHtml(prog.title) + '</span>' +
							'<svg class="w-4 h-4 text-slate-400 group-hover:text-[#00308b] transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>' +
						'</a>' +
					'</h3>' +
				'</div>' +
				bentoGridHtml +
				reasonsHtml +
				'<div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-2.5">' +
					'<a href="' + escAttr(permalink) + '" target="_blank" rel="noopener" class="w-full sm:w-auto px-4 py-2.5 text-center rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-2xs no-underline inline-flex items-center justify-center gap-1.5 cursor-pointer">' +
						'<span>Xem chi tiết trường</span>' +
						'<svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>' +
					'</a>' +
					'<button type="button" class="elig-verify-card-btn w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold transition-all shadow-2xs inline-flex items-center justify-center gap-2 cursor-pointer active:scale-95" data-program-id="' + escAttr(prog.program_id) + '" data-program-title="' + escAttr(prog.title) + '">' +
						'<span>Đăng ký thẩm định & miễn giảm môn</span>' +
						'<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>' +
					'</button>' +
				'</div>' +
			'</div>';

		return card;
	}

	function renderAlternativeCard(prog, idx) {
		var schoolTitle = (prog.school && prog.school.title) ? prog.school.title : 'Trường Đại học';
		var schoolLogo = (prog.school && prog.school.logo) ? prog.school.logo : '';
		var permalink = prog.permalink || '#';

		var card = document.createElement('div');
		card.className = 'elig-card bg-white border border-slate-200/80 rounded-2xl shadow-xs hover:shadow-md transition-all overflow-hidden';

		card.innerHTML =
			'<div class="p-4 sm:p-5 pb-3 border-b border-slate-100 flex items-center justify-between gap-3 bg-white">' +
				'<div class="flex items-center gap-3 min-w-0">' +
					(schoolLogo
						? '<div class="w-10 h-10 rounded-xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">' +
								'<img src="' + escAttr(schoolLogo) + '" alt="' + escAttr(schoolTitle) + '" class="max-w-full max-h-full object-contain">' +
						  '</div>'
						: '<div class="w-10 h-10 rounded-xl bg-slate-700 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">ĐH</div>'
					) +
					'<div class="min-w-0">' +
						'<div class="font-bold text-xs sm:text-sm text-slate-800 truncate">' + escHtml(schoolTitle) + '</div>' +
						'<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 mt-0.5">' + escHtml(prog.alt_badge || 'Phương án dự phòng') + '</span>' +
					'</div>' +
				'</div>' +
				'<div class="text-xs font-bold text-slate-400 shrink-0">Đề xuất #' + (idx + 1) + '</div>' +
			'</div>' +
			'<div class="p-4 sm:p-5 pt-3">' +
				'<h4 class="font-extrabold text-base text-slate-900 mb-2">' +
					'<a href="' + escAttr(permalink) + '" target="_blank" rel="noopener" class="hover:text-[#00308b] no-underline text-slate-900 inline-flex items-center gap-1.5">' +
						'<span>' + escHtml(prog.title) + '</span>' +
					'</a>' +
				'</h4>' +
				'<p class="text-xs text-slate-600 mb-3 leading-relaxed">' + escHtml(prog.reason || prog.suggestion_reason || '') + '</p>' +
				'<div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-2">' +
					'<button type="button" class="elig-verify-card-btn w-full sm:w-auto px-4 py-2 rounded-xl bg-blue-50 text-[#00308b] hover:bg-blue-100 text-xs font-bold transition-all cursor-pointer" data-program-id="' + escAttr(prog.program_id) + '" data-program-title="' + escAttr(prog.title) + '">' +
						'<span>Chọn nộp thẩm định chương trình này</span>' +
					'</button>' +
				'</div>' +
			'</div>';

		return card;
	}

	// ----------------------------------------------------
	// Lead Consultation Form Handler
	// ----------------------------------------------------
	function initLeadForm() {
		var formEl = document.getElementById('elig-consultation-form');
		if (!formEl || formEl.dataset.bound === 'true') return;
		formEl.dataset.bound = 'true';
		formEl.style.display = 'block';

		formEl.addEventListener('submit', function (e) {
			e.preventDefault();
			var submitBtn = document.getElementById('elig-lead-submit-btn');
			var btnText = submitBtn ? submitBtn.querySelector('.elig-btn-text') : null;
			var btnLoading = submitBtn ? submitBtn.querySelector('.elig-btn-loading') : null;

			function resetBtn() {
				if (submitBtn) submitBtn.disabled = false;
				if (btnText) btnText.classList.remove('hidden');
				if (btnLoading) btnLoading.classList.add('hidden');
			}

			if (submitBtn) submitBtn.disabled = true;
			if (btnText) btnText.classList.add('hidden');
			if (btnLoading) btnLoading.classList.remove('hidden');

			var data = new FormData(formEl);
			data.append('action', 'ltdh_elig_lead');
			data.append('nonce', ltdh_elig.nonce);

			var tokenPromise = window.ltdhGetRecaptchaToken
				? window.ltdhGetRecaptchaToken('elig_lead', formEl)
				: Promise.resolve('');

			tokenPromise.then(function (recaptchaToken) {
				if (recaptchaToken) {
					data.append('g-recaptcha-response', recaptchaToken);
				}

				return fetch(ltdh_elig.ajax_url, {
					method: 'POST',
					body: data,
					credentials: 'same-origin'
				});
			})
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (json.success) {
					currentLeadId = json.data.lead_id;
					currentLeadToken = json.data.lead_token || '';
					
					formEl.innerHTML = '<div class="elig-lead-success bg-emerald-50 border border-emerald-200 text-emerald-900 p-6 md:p-8 rounded-2xl text-center space-y-3 animate-fade-in shadow-sm">' +
						'<div class="text-4xl">🎉</div>' +
						'<h4 class="text-xl font-black text-emerald-900">Gửi yêu cầu đối chiếu hồ sơ thành công!</h4>' +
						'<p class="text-sm text-emerald-700 max-w-lg mx-auto leading-relaxed">Hệ thống đã tiếp nhận dữ liệu thẩm định của bạn. Ban tư vấn chuyên môn sẽ thẩm định hồ sơ theo quy chế tuyển sinh 2026–2027 và liên hệ tư vấn lộ trình học phù hợp nhất cho bạn trong vòng 24 giờ làm việc.</p>' +
					'</div>';
				} else {
					alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra.');
					resetBtn();
				}
			})
			.catch(function (err) {
				console.error('Lead capture error:', err);
				alert(err && err.message ? err.message : 'Có lỗi xảy ra khi gửi hồ sơ.');
				resetBtn();
			});
		});
	}

	function initCardVerifyListeners() {
		var container = document.getElementById('elig-results');
		if (!container || container.dataset.verifyBound === 'true') return;
		container.dataset.verifyBound = 'true';

		container.addEventListener('click', function(e) {
			var verifyBtn = e.target.closest('.elig-verify-card-btn');
			if (verifyBtn) {
				var programId = verifyBtn.getAttribute('data-program-id');
				var progTitle = verifyBtn.getAttribute('data-program-title');
				
				var progIdFields = document.querySelectorAll('#elig-lead-section input[name="elig_program_id"], #elig-program-id');
				if (programId) {
					progIdFields.forEach(function (f) { f.value = programId; });
				}
				
				if (!progTitle) {
					var card = verifyBtn.closest('.elig-card');
					if (card) {
						var cardTitleEl = card.querySelector('.elig-card-title');
						if (cardTitleEl) {
							progTitle = cardTitleEl.textContent.trim();
						}
					}
				}

				if (progTitle) {
					var selectedTitleEl = document.getElementById('elig-selected-program-title');
					if (selectedTitleEl) {
						selectedTitleEl.textContent = progTitle;
					}
				}

				var banner = document.getElementById('elig-selected-program-banner');
				if (banner) {
					banner.classList.add('ring-2', 'ring-[#00308b]', 'bg-blue-50');
					setTimeout(function() {
						banner.classList.remove('ring-2', 'ring-[#00308b]', 'bg-blue-50');
					}, 1500);
				}

				var leadSection = document.getElementById('elig-lead-section');
				if (leadSection) {
					leadSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
					var nameInput = document.getElementById('elig-lead-name');
					if (nameInput) {
						setTimeout(function() {
							nameInput.focus();
						}, 400);
					}
				}
			}
		});
	}

	// ----------------------------------------------------
	// Results Actions & Retry
	// ----------------------------------------------------
	function initResultsActions() {
		var retry = document.getElementById('elig-retry');
		var retry2 = document.getElementById('elig-no-results-retry');

		function backToForm() {
			var wizard = document.getElementById('elig-wizard');
			var resultsEl = document.getElementById('elig-results');

			if (wizard) wizard.classList.remove('hidden');
			if (resultsEl) resultsEl.classList.add('hidden');

			if (window.history && window.history.pushState) {
				window.history.pushState({}, '', window.location.pathname);
			}

			var container = document.getElementById('eligibility-app');
			if (container) {
				container.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		}

		if (retry && retry.dataset.bound !== 'true') {
			retry.dataset.bound = 'true';
			retry.addEventListener('click', backToForm);
		}
		if (retry2 && retry2.dataset.bound !== 'true') {
			retry2.dataset.bound = 'true';
			retry2.addEventListener('click', backToForm);
		}
	}

	// ----------------------------------------------------
	// Share & Copy URL Permalink
	// ----------------------------------------------------
	function initShareAndCopy() {
		var copyBtn = document.getElementById('elig-copy-share-btn');
		if (!copyBtn || copyBtn.dataset.bound === 'true') return;
		copyBtn.dataset.bound = 'true';

		copyBtn.addEventListener('click', function () {
			var shareUrl = window.location.href;
			var shareKey = results && (results.share_token || results.check_id);
			if (shareKey && !shareUrl.includes('kq=')) {
				var urlObj = new URL(shareUrl);
				urlObj.searchParams.set('kq', shareKey);
				shareUrl = urlObj.toString();
			}

			copyToClipboard(shareUrl, function () {
				showCopyFeedback();
			});
		});
	}

	function copyToClipboard(text, onSuccess) {
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(text).then(function () {
				if (onSuccess) onSuccess();
			}).catch(function () {
				fallbackCopyText(text, onSuccess);
			});
		} else {
			fallbackCopyText(text, onSuccess);
		}
	}

	function fallbackCopyText(text, onSuccess) {
		var textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.top = '-9999px';
		textArea.style.left = '-9999px';
		textArea.setAttribute('readonly', '');
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();
		try {
			var successful = document.execCommand('copy');
			if (successful && onSuccess) {
				onSuccess();
			}
		} catch (err) {
			console.error('Fallback copy failed', err);
			prompt('Sao chép link kết quả tại đây:', text);
		}
		document.body.removeChild(textArea);
	}

	function showCopyFeedback() {
		var toast = document.getElementById('elig-share-toast');
		var btnText = document.getElementById('elig-copy-btn-text');

		if (btnText) {
			var originalText = btnText.textContent;
			btnText.textContent = '✓ Đã sao chép!';
			setTimeout(function () {
				btnText.textContent = originalText;
			}, 2500);
		}

		if (toast) {
			toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
			toast.classList.add('opacity-100', 'translate-y-0');
			setTimeout(function () {
				toast.classList.remove('opacity-100', 'translate-y-0');
				toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
			}, 3000);
		}
	}

	// ----------------------------------------------------
	// URL Permalinks & Direct Load
	// ----------------------------------------------------
	function initUrlAndInitialResults() {
		var initial = window.ltdh_initial_results || (window.ltdh_elig && window.ltdh_elig.initial_results);
		if (initial) {
			results = initial;
			renderResults(results);
			showResults();
			return;
		}

		var urlParams = new URLSearchParams(window.location.search);
		var checkParam = urlParams.get('kq') || urlParams.get('check_id') || urlParams.get('res') || urlParams.get('token');
		if (checkParam) {
			loadCheckById(checkParam);
		}

		window.addEventListener('popstate', function (e) {
			var stateKey = e.state && (e.state.kq || e.state.check_id);
			if (stateKey) {
				loadCheckById(stateKey);
			} else {
				var params = new URLSearchParams(window.location.search);
				var id = params.get('kq') || params.get('token') || params.get('check_id');
				if (id) {
					loadCheckById(id);
				} else {
					var wizard = document.getElementById('elig-wizard');
					var resultsEl = document.getElementById('elig-results');
					if (wizard) wizard.classList.remove('hidden');
					if (resultsEl) resultsEl.classList.add('hidden');
				}
			}
		});
	}

	function loadCheckById(checkToken) {
		if (!checkToken) return;

		var wizard = document.getElementById('elig-wizard');
		var resultsEl = document.getElementById('elig-results');
		if (wizard) wizard.classList.add('hidden');
		if (resultsEl) resultsEl.classList.remove('hidden');

		var ajaxUrl = (window.ltdh_elig && window.ltdh_elig.ajax_url) ? window.ltdh_elig.ajax_url : '/wp-admin/admin-ajax.php';

		fetch(ajaxUrl + '?action=ltdh_elig_get_check&kq=' + encodeURIComponent(checkToken), {
			credentials: 'same-origin'
		})
		.then(function (r) { return r.json(); })
		.then(function (json) {
			if (json.success && json.data) {
				results = json.data;
				renderResults(results);
				showResults();
			} else {
				if (wizard) wizard.classList.remove('hidden');
				if (resultsEl) resultsEl.classList.add('hidden');
			}
		})
		.catch(function (err) {
			console.error('Failed to load check by id:', err);
			if (wizard) wizard.classList.remove('hidden');
			if (resultsEl) resultsEl.classList.add('hidden');
		});
	}

	// ----------------------------------------------------
	// Reusable Compact Widget Handler (mode="compact")
	// ----------------------------------------------------
	function initCompactWidgets() {
		var widgets = document.querySelectorAll('[data-checker-widget][data-checker-mode="compact"]');
		if (!widgets || widgets.length === 0) return;

		widgets.forEach(function (widget) {
			if (widget.dataset.initialized === 'true') return;
			widget.dataset.initialized = 'true';

			var pageContext = {};
			try {
				pageContext = JSON.parse(widget.getAttribute('data-page-context') || '{}');
			} catch (e) {
				pageContext = {};
			}

			var pane1 = widget.querySelector('[data-pane="1"]');
			var pane2 = widget.querySelector('[data-pane="2"]');
			var pane3 = widget.querySelector('[data-pane="3"]');
			var stepIndicator = widget.querySelector('[data-step-indicator]');
			var pills = widget.querySelectorAll('.compact-step-pill');

			var eduSelect      = widget.querySelector('select[name="compact_education"]');
			var thptBranch     = widget.querySelector('[data-branch="thpt-status"]');
			var gradBranch     = widget.querySelector('[data-branch="grad-date"]');
			var gradMonth      = widget.querySelector('select[name="compact_grad_month"]');
			var gradYear       = widget.querySelector('input[name="compact_grad_year"]');
			var monthBadge     = widget.querySelector('[data-month-calc-badge]');
			var gradeSelect    = widget.querySelector('select[name="compact_grad_grade"]');
			var relationSelect = widget.querySelector('select[name="compact_major_relation"]');

			var resultCard        = widget.querySelector('[data-result-card]');
			var resultIcon        = widget.querySelector('[data-result-icon]');
			var resultBadge       = widget.querySelector('[data-result-badge]');
			var resultUrgentBadge = widget.querySelector('[data-result-urgent-badge]');
			var resultTitle       = widget.querySelector('[data-result-title]');
			var resultSummary     = widget.querySelector('[data-result-summary]');
			var remedyWrap        = widget.querySelector('[data-result-remedy-wrap]');
			var remedyContent     = widget.querySelector('[data-result-remedy]');

			var conflictBox     = widget.querySelector('[data-conflict-box]');
			var conflictTitle   = widget.querySelector('[data-conflict-title]');
			var conflictMessage = widget.querySelector('[data-conflict-message]');
			var conflictBtn     = widget.querySelector('[data-conflict-btn]');
			var conflictBtnText = widget.querySelector('[data-conflict-btn-text]');

			var leadForm = widget.querySelector('[data-compact-lead-form]');

			function updateBranching() {
				var edu = eduSelect ? eduSelect.value : '';
				var thptRadio = widget.querySelector('input[name="compact_thpt_status"]:checked');
				var thpt = thptRadio ? thptRadio.value : '';

				// THPT status branch: only for Trung cấp or Cao đẳng
				if (thptBranch) {
					if (edu === 'trung-cap' || edu === 'cao-dang') {
						thptBranch.classList.remove('hidden');
					} else {
						thptBranch.classList.add('hidden');
						var thptRadios = widget.querySelectorAll('input[name="compact_thpt_status"]');
						thptRadios.forEach(function (r) { r.checked = false; });
						thpt = '';
					}
				}

				// Graduation date branch: mandatory for Cao đẳng + Chưa có bằng THPT
				if (gradBranch) {
					if (edu === 'cao-dang' && thpt === 'chua-co-giay-van-hoa-thpt') {
						gradBranch.classList.remove('hidden');
					} else {
						gradBranch.classList.add('hidden');
					}
				}

				// Real-time month calculation
				if (gradYear && gradYear.value && monthBadge) {
					var y = parseInt(gradYear.value, 10);
					var m = gradMonth && gradMonth.value ? parseInt(gradMonth.value, 10) : 6;
					var months = 0;
					if (window.LtdhEligibilityEngine && window.LtdhEligibilityEngine.calculateMonthsSinceGraduation) {
						months = window.LtdhEligibilityEngine.calculateMonthsSinceGraduation(m, y);
					} else {
						months = Math.max(0, (2026 - y) * 12 + (11 - m));
					}

					if (edu === 'cao-dang' && thpt === 'chua-co-giay-van-hoa-thpt') {
						if (months >= 24) {
							monthBadge.innerHTML = '<span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-600 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span>Đã ' + months + ' tháng (≥ 24 tháng)</span></span>';
							monthBadge.className = 'text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded whitespace-nowrap shrink-0';
						} else {
							monthBadge.innerHTML = '<span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-amber-600 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg><span>Mới ' + months + ' tháng (&lt; 24 tháng)</span></span>';
							monthBadge.className = 'text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-300 px-2 py-0.5 rounded whitespace-nowrap shrink-0';
						}
					} else {
						monthBadge.textContent = '~' + months + ' tháng';
						monthBadge.className = 'text-[10px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded whitespace-nowrap shrink-0';
					}
				} else if (monthBadge) {
					monthBadge.textContent = 'Tính mốc 24 tháng';
					monthBadge.className = 'text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded whitespace-nowrap shrink-0';
				}
			}

			if (eduSelect) eduSelect.addEventListener('change', updateBranching);
			var allThptRadios = widget.querySelectorAll('input[name="compact_thpt_status"]');
			allThptRadios.forEach(function (r) { r.addEventListener('change', updateBranching); });
			if (gradMonth) gradMonth.addEventListener('change', updateBranching);
			if (gradYear) gradYear.addEventListener('input', updateBranching);

			function goToStep(step) {
				[pane1, pane2, pane3].forEach(function (p, idx) {
					if (p) {
						if (idx + 1 === step) {
							p.classList.remove('hidden');
						} else {
							p.classList.add('hidden');
						}
					}
				});

				pills.forEach(function (pill, idx) {
					if (idx + 1 <= step) {
						pill.classList.add('is-active');
					} else {
						pill.classList.remove('is-active');
					}
				});

				if (stepIndicator) {
					if (step === 1) {
						stepIndicator.textContent = 'Bước 1 / 2';
					} else if (step === 2) {
						stepIndicator.textContent = 'Bước 2 / 2';
					} else {
						stepIndicator.textContent = 'Kết quả đối chiếu';
					}
				}

				// Automatically hide any duplicate sidebar consultation form when Step 3 (results & lead form) is active
				try {
					var sidebarParent = widget.closest('aside, .widget-area, #program-sidebar, #school-sidebar, #major-sidebar, #secondary') || document.body;
					var siblingForms = sidebarParent.querySelectorAll('#register, .widget_ltdh_consultation_form, .ltdh-widget-form, [id*="consultation_form"]');
					siblingForms.forEach(function (formEl) {
						if (!widget.contains(formEl)) {
							var wrapper = formEl.closest('.widget, section, .ltdh-widget-form') || formEl;
							if (step === 3) {
								wrapper.setAttribute('data-hidden-by-checker', 'true');
								wrapper.classList.add('hidden');
							} else if (wrapper.getAttribute('data-hidden-by-checker') === 'true') {
								wrapper.removeAttribute('data-hidden-by-checker');
								wrapper.classList.remove('hidden');
							}
						}
					});
				} catch (err) {
					console.warn('Error toggling sidebar consultation form:', err);
				}
			}

			// Step 1 -> Step 2
			var nextBtn = widget.querySelector('[data-action="next-step"]');
			if (nextBtn) {
				nextBtn.addEventListener('click', function () {
					var edu = eduSelect ? eduSelect.value : '';
					if (!edu) {
						alert('Vui lòng chọn trình độ / văn bằng hiện có của bạn (Cao đẳng hoặc Trung cấp).');
						if (eduSelect) eduSelect.focus();
						return;
					}

					if (edu === 'trung-cap' || edu === 'cao-dang') {
						var thptChecked = widget.querySelector('input[name="compact_thpt_status"]:checked');
						if (!thptChecked) {
							alert('Vui lòng chọn tình trạng bằng tốt nghiệp THPT.');
							return;
						}

						if (edu === 'cao-dang' && thptChecked.value === 'chua-co-giay-van-hoa-thpt') {
							var y = gradYear ? parseInt(gradYear.value, 10) : 0;
							if (!y || y < 1990) {
								alert('Trường hợp Cao đẳng chưa có bằng THPT bắt buộc phải nhập năm tốt nghiệp để đối chiếu mốc 24 tháng theo quy chế.');
								if (gradYear) gradYear.focus();
								return;
							}
						}
					}

					goToStep(2);
				});
			}

			// Step 2 -> Step 1
			var prevBtn = widget.querySelector('[data-action="prev-step"]');
			if (prevBtn) {
				prevBtn.addEventListener('click', function () {
					goToStep(1);
				});
			}

			// Step 2 -> Step 3 (Evaluate)
			var evalBtn = widget.querySelector('[data-action="evaluate-now"]');
			if (evalBtn) {
				evalBtn.addEventListener('click', function () {
					var eduVal = eduSelect ? eduSelect.value : '';
					var thptRadio = widget.querySelector('input[name="compact_thpt_status"]:checked');
					var thptVal = (eduVal === 'trung-cap' || eduVal === 'cao-dang') ? (thptRadio ? thptRadio.value : '') : '';
					var mVal = gradMonth ? gradMonth.value : '6';
					var yVal = gradYear ? gradYear.value : '';
					var gradeVal = gradeSelect ? gradeSelect.value : 'kha';
					var relationVal = relationSelect ? relationSelect.value : 'dung-nganh';
					var timeRadio = widget.querySelector('input[name="compact_time_pref"]:checked');
					var timeVal = timeRadio ? timeRadio.value : 'online-100';

					var evalInput = {
						education: eduVal,
						thpt_status: thptVal,
						grad_month: mVal,
						grad_year: yVal,
						graduation: yVal,
						graduation_grade: gradeVal,
						major_relation: relationVal,
						study_time_preference: timeVal
					};

					var engine = window.LtdhEligibilityEngine;
					var decision = (engine && engine.evaluateDecisionTree)
						? engine.evaluateDecisionTree(evalInput, pageContext)
						: {
							decision_status: 'eligible',
							badge_text: 'Đủ điều kiện xét tuyển',
							decision_title: 'Hồ sơ đủ điều kiện tuyển sinh',
							decision_summary: 'Hồ sơ đáp ứng tiêu chuẩn tuyển sinh liên thông đại học.',
							priority_level: 'standard'
						};

					var status = decision.decision_status || 'eligible';
					var normalizedStatus = status.replace(/_/g, '-');

					// Update Result Card
					if (resultCard) {
						resultCard.classList.remove('status-eligible', 'status-warning-urgent', 'status-not-eligible', 'status-not_eligible', 'status-not_eligible_2027', 'status-not-eligible-2027');
						resultCard.classList.add('status-' + normalizedStatus);
						resultCard.classList.add('status-' + status);
					}

					if (resultIcon) {
						var iconSvg = '';
						if (status === 'eligible') {
							iconSvg = '<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
							resultIcon.className = 'decision-icon-badge w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-white shadow-2xs bg-emerald-600';
						} else if (status === 'warning_urgent') {
							iconSvg = '<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>';
							resultIcon.className = 'decision-icon-badge w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-white shadow-2xs bg-amber-500';
						} else {
							iconSvg = '<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
							resultIcon.className = 'decision-icon-badge w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-white shadow-2xs bg-rose-600';
						}
						resultIcon.innerHTML = iconSvg;
					}

					if (resultBadge) {
						resultBadge.className = 'elig-badge text-[10px]';
						if (status === 'eligible') {
							resultBadge.classList.add('elig-badge-eligible');
						} else if (status === 'warning_urgent') {
							resultBadge.classList.add('elig-badge-urgent');
						} else {
							resultBadge.classList.add('elig-badge-not-eligible');
						}
						resultBadge.textContent = decision.status_label || decision.badge_text || 'Kết quả đối chiếu';
					}

					if (resultUrgentBadge) {
						resultUrgentBadge.classList.add('hidden');
					}

					if (resultTitle) {
						resultTitle.textContent = decision.decision_title || 'Kết quả đối chiếu hồ sơ';
					}

					if (resultSummary) {
						var rawSum = decision.decision_summary || '';
						resultSummary.innerHTML = '<div class="p-3 rounded-xl bg-white/95 border border-slate-200/90 text-xs text-slate-800 leading-relaxed font-normal shadow-2xs">' + rawSum + '</div>';
					}

					if (remedyWrap) {
						remedyWrap.classList.add('hidden');
					}

					// Update Context Conflict Alert Box
					if (conflictBox) {
						if (decision.context_conflict) {
							conflictBox.classList.remove('hidden');
							if (conflictTitle) conflictTitle.textContent = decision.context_conflict.title || 'Lưu ý quan trọng:';
							if (conflictMessage) conflictMessage.innerHTML = decision.context_conflict.message || '';
							if (conflictBtn) {
								conflictBtn.setAttribute('href', decision.context_conflict.action_url || '#');
								if (decision.context_conflict.action_url && decision.context_conflict.action_url.indexOf('http') === 0) {
									conflictBtn.setAttribute('target', '_blank');
									conflictBtn.setAttribute('rel', 'noopener');
								} else {
									conflictBtn.removeAttribute('target');
									conflictBtn.removeAttribute('rel');
								}
							}
							if (conflictBtnText) conflictBtnText.textContent = decision.context_conflict.action_text || 'Xem chi tiết';
						} else {
							conflictBox.classList.add('hidden');
						}
					}

					// Populate Lead Form Hidden Fields
					if (leadForm) {
						var setVal = function (name, val) {
							var f = leadForm.querySelector('input[name="' + name + '"]');
							if (f) f.value = val;
						};
						setVal('cf_education', evalInput.education);
						setVal('cf_thpt_status', evalInput.thpt_status);
						setVal('cf_grad_month', evalInput.grad_month);
						setVal('cf_grad_year', evalInput.grad_year);
						setVal('cf_months_since_grad', decision.months_since_grad || 0);
						setVal('cf_grad_grade', evalInput.graduation_grade);
						setVal('cf_major_relation', evalInput.major_relation);
						setVal('cf_time_pref', evalInput.study_time_preference);
						setVal('cf_decision_status', decision.decision_status || '');
						setVal('cf_priority_level', decision.priority_level || 'standard');
						setVal('cf_suitable_training_types', (decision.suitable_training_types || []).join(', '));
					}

					goToStep(3);
				});
			}

			// Reset Button
			var resetBtn = widget.querySelector('[data-action="reset-check"]');
			if (resetBtn) {
				resetBtn.addEventListener('click', function () {
					if (eduSelect) eduSelect.value = '';
					var thptRadios = widget.querySelectorAll('input[name="compact_thpt_status"]');
					thptRadios.forEach(function (r) { r.checked = false; });
					if (gradYear) gradYear.value = '';
					updateBranching();
					goToStep(1);
				});
			}

			// Compact Lead Form AJAX Handler
			if (leadForm) {
				leadForm.addEventListener('submit', function (e) {
					e.preventDefault();
					var sBtn = leadForm.querySelector('[data-lead-submit-btn]');
					if (sBtn) sBtn.disabled = true;

					var formData = new FormData(leadForm);
					formData.append('action', 'ltdh_elig_lead');
					formData.append('nonce', (window.ltdh_elig && window.ltdh_elig.nonce) || '');

					var ajaxUrl = (window.ltdh_elig && window.ltdh_elig.ajax_url) || '/wp-admin/admin-ajax.php';

					var tokenPromise = window.ltdhGetRecaptchaToken
						? window.ltdhGetRecaptchaToken('elig_lead', leadForm)
						: Promise.resolve('');

					tokenPromise.then(function (recaptchaToken) {
						if (recaptchaToken) {
							formData.append('g-recaptcha-response', recaptchaToken);
						}

						return fetch(ajaxUrl, {
							method: 'POST',
							body: formData,
							credentials: 'same-origin'
						});
					})
					.then(function (res) { return res.json(); })
					.then(function (json) {
						if (json.success) {
							leadForm.innerHTML = '<div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center space-y-2 animate-fade-in">' +
								'<div class="w-10 h-10 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-2xs">' +
									'<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>' +
								'</div>' +
								'<h5 class="text-xs sm:text-sm font-bold text-emerald-900">Gửi thông tin thẩm định thành công!</h5>' +
								'<p class="text-[11px] text-emerald-800 leading-relaxed">Hệ thống đã lưu hồ sơ theo quy chuẩn tuyển sinh 2026–2027. Ban tư vấn chuyên môn sẽ liên hệ hướng dẫn lộ trình và phương án xét miễn giảm môn học cho bạn sớm nhất.</p>' +
							'</div>';
						} else {
							alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra. Vui lòng thử lại.');
							if (sBtn) sBtn.disabled = false;
						}
					})
					.catch(function (err) {
						console.error('Compact lead error:', err);
						alert('Có lỗi kết nối. Vui lòng kiểm tra đường truyền và thử lại.');
						if (sBtn) sBtn.disabled = false;
					});
				});
			}
		});
	}

	// ----------------------------------------------------
	// Helpers
	// ----------------------------------------------------
	function decodeHtml(html) {
		if (!html) return '';
		var txt = document.createElement('textarea');
		txt.innerHTML = html;
		return txt.value;
	}

	function escHtml(str) {
		if (!str) return '';
		var decoded = decodeHtml(str);
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(decoded));
		return div.innerHTML;
	}

	function escAttr(str) {
		return escHtml(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	window.ltdhInitCompactWidgets = initCompactWidgets;
})();
