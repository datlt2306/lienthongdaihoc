/**
 * Comparison System — Client-side Tray & Interaction
 *
 * @package lienthongdaihoc
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'ltdh_compare_items';
	var MAX_ITEMS   = 4;
	var ajaxUrl     = (window.ltdh_ajax && window.ltdh_ajax.ajax_url) || '/wp-admin/admin-ajax.php';
	var homeUrl     = (window.ltdh_ajax && window.ltdh_ajax.home_url) || '/';
	if (homeUrl.slice(-1) !== '/') {
		homeUrl += '/';
	}

	// ----------------------------------------------------
	// 1. sessionStorage Helpers
	// ----------------------------------------------------
	// Purge stale details cache containing old banner/photo/cropped-logo URLs
	try {
		var rawDetails = sessionStorage.getItem('ltdh_compare_details');
		if (rawDetails && (rawDetails.indexOf('banner-') !== -1 || rawDetails.indexOf('photo-') !== -1 || rawDetails.indexOf('unsplash.com') !== -1 || rawDetails.indexOf('cropped-logo') !== -1)) {
			sessionStorage.removeItem('ltdh_compare_details');
		}
	} catch (e) {}
	function getItems() {
		try {
			var raw = sessionStorage.getItem(STORAGE_KEY);
			return raw ? JSON.parse(raw) : { program: [] };
		} catch (e) {
			return { program: [] };
		}
	}

	function saveItems(items) {
		try {
			sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items));
		} catch (e) { /* silent */ }
	}

	function getMetadata() {
		try {
			var raw = sessionStorage.getItem('ltdh_compare_metadata');
			return raw ? JSON.parse(raw) : {};
		} catch (e) {
			return {};
		}
	}

	function saveMetadata(meta) {
		try {
			sessionStorage.setItem('ltdh_compare_metadata', JSON.stringify(meta));
		} catch (e) { /* silent */ }
	}

	function getDetailsCache() {
		try {
			var raw = sessionStorage.getItem('ltdh_compare_details');
			return raw ? JSON.parse(raw) : {};
		} catch (e) {
			return {};
		}
	}

	function saveDetailsCache(cache) {
		try {
			sessionStorage.setItem('ltdh_compare_details', JSON.stringify(cache));
		} catch (e) { /* silent */ }
	}

	function addItem(type, id, he, nganh, title, thumb, majorName, schoolName) {
		var items = getItems();
		if (!items[type]) items[type] = [];
		if (items[type].indexOf(id) === -1) {
			if (items[type].length >= MAX_ITEMS) return false;
			items[type].push(id);
			saveItems(items);

			// Save metadata
			var meta = getMetadata();
			meta[id] = { he: he || '', nganh: nganh || '' };
			saveMetadata(meta);

			// Save details
			var cache = getDetailsCache();
			cache[id] = {
				title: title || '',
				thumb: thumb || '',
				majorName: majorName || '',
				schoolName: schoolName || ''
			};
			saveDetailsCache(cache);
		}
		return true;
	}

	function removeItem(type, id) {
		var items = getItems();
		if (!items[type]) return;
		items[type] = items[type].filter(function (i) { return i !== id; });
		saveItems(items);

		// Remove metadata
		var meta = getMetadata();
		delete meta[id];
		saveMetadata(meta);

		// Remove details
		var cache = getDetailsCache();
		delete cache[id];
		saveDetailsCache(cache);

		syncCompareButtonStates();
	}

	function getCount(type) {
		var items = getItems();
		return type ? (items[type] || []).length : Object.values(items).reduce(function (s, a) { return s + a.length; }, 0);
	}

	function hasItem(type, id) {
		var items = getItems();
		return items[type] && items[type].indexOf(id) !== -1;
	}

	function clearAll() {
		sessionStorage.removeItem(STORAGE_KEY);
		sessionStorage.removeItem('ltdh_compare_metadata');
		sessionStorage.removeItem('ltdh_compare_details');

		// Reset all buttons on the page
		var buttons = document.querySelectorAll('.ltdh-compare-toggle, .ltdh-compare-single-btn');
		buttons.forEach(function (btn) {
			btn.classList.remove('is-compared');
			btn.textContent = btn.classList.contains('ltdh-compare-single-btn') ? '📊 Thêm vào so sánh' : 'So sánh';
		});

		updateTray();
		showToast('Đã xóa tất cả mục so sánh.', 'info');
	}

	// ----------------------------------------------------
	// 2. Compare Button Toggle (Event Delegation)
	// ----------------------------------------------------
	function syncCompareButtonStates() {
		var buttons = document.querySelectorAll('.ltdh-compare-toggle, .ltdh-compare-single-btn');
		buttons.forEach(function (btn) {
			var type = btn.getAttribute('data-compare-type');
			var id   = parseInt(btn.getAttribute('data-compare-id'), 10);
			if (!type || !id) return;

			if (hasItem(type, id)) {
				btn.classList.add('is-compared');
				btn.textContent = '✓ Đã thêm';
			} else {
				btn.classList.remove('is-compared');
				btn.textContent = btn.classList.contains('ltdh-compare-single-btn') ? '📊 Thêm vào so sánh' : 'So sánh';
			}
		});
	}

	function initCompareDelegation() {
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.ltdh-compare-toggle, .ltdh-compare-single-btn');
			if (!btn) return;

			e.preventDefault();
			e.stopPropagation();

			var type = btn.getAttribute('data-compare-type');
			var id   = parseInt(btn.getAttribute('data-compare-id'), 10);
			if (!type || !id) return;

			if (hasItem(type, id)) {
				removeItem(type, id);
				btn.classList.remove('is-compared');
				btn.textContent = btn.classList.contains('ltdh-compare-single-btn') ? '📊 Thêm vào so sánh' : 'So sánh';
			} else {
				var items = getItems();
				var total = Object.values(items).reduce(function (s, a) { return s + a.length; }, 0);
				if (total >= MAX_ITEMS) {
					showToast('Chỉ so sánh tối đa ' + MAX_ITEMS + ' mục.', 'warning');
					return;
				}

				// Validation: Same major (nganh)
				var btnHe = btn.getAttribute('data-compare-he');
				var btnNganh = btn.getAttribute('data-compare-nganh');
				var activeIds = items[type] || [];

				if (activeIds.length > 0 && btnNganh) {
					var meta = getMetadata();
					for (var idx = 0; idx < activeIds.length; idx++) {
						var existingId = activeIds[idx];
						var existingMeta = meta[existingId];
						if (existingMeta) {
							if (existingMeta.nganh && existingMeta.nganh !== btnNganh) {
								showToast('Chỉ được so sánh các chương trình CÙNG NGÀNH ĐÀO TẠO.', 'error');
								return;
							}
						}
					}
				}

				var btnTitle = btn.getAttribute('data-compare-title') || '';
				var btnThumb = btn.getAttribute('data-compare-thumb') || '';
				var btnMajorName = btn.getAttribute('data-compare-major-name') || '';
				var btnSchoolName = btn.getAttribute('data-compare-school-name') || '';

				var cardEl = document.querySelector('[data-compare-id="' + id + '"]');
				if (cardEl) {
					btnThumb = cardEl.getAttribute('data-compare-thumb') || btnThumb;
					btnTitle = cardEl.getAttribute('data-compare-title') || btnTitle;
					btnMajorName = cardEl.getAttribute('data-compare-major-name') || btnMajorName;
					btnSchoolName = cardEl.getAttribute('data-compare-school-name') || btnSchoolName;
				}

				addItem(type, id, btnHe, btnNganh, btnTitle, btnThumb, btnMajorName, btnSchoolName);
				btn.classList.add('is-compared');
				btn.textContent = '✓ Đã thêm';
				showToast('Đã thêm vào danh sách so sánh (' + (total + 1) + '/' + MAX_ITEMS + ')', 'success');
			}
			updateTray();
		});
	}

	// ----------------------------------------------------
	// 3. Floating Tray
	// ----------------------------------------------------
	function updateTray() {
		var tray = document.getElementById('ltdh-compare-tray');
		if (!tray) return;

		var items = getItems();
		var totalCount = Object.values(items).reduce(function (s, a) { return s + a.length; }, 0);
		var activeType = detectActiveType();

		if (totalCount < 1) {
			tray.classList.add('hidden');
			return;
		}

		tray.classList.remove('hidden');

		var listEl = tray.querySelector('.ltdh-tray-items');
		if (!listEl) return;

		listEl.innerHTML = '';
		var activeItems = items[activeType] || [];

		var cache = getDetailsCache();
		var activeMajorName = '';

		activeItems.forEach(function (id) {
			var cached = cache[id] || {};
			var card = document.querySelector('[data-compare-id="' + id + '"]');

			var cardTitle  = card ? card.getAttribute('data-compare-title') : '';
			var cardThumb  = card ? card.getAttribute('data-compare-thumb') : '';
			var cardMajor  = card ? card.getAttribute('data-compare-major-name') : '';
			var cardSchool = card ? card.getAttribute('data-compare-school-name') : '';

			var title      = cardTitle || cached.title || ('Mục #' + id);
			var thumb      = (cardThumb && cardThumb.indexOf('cropped-logo') === -1) ? cardThumb : ((cached.thumb && cached.thumb.indexOf('cropped-logo') === -1) ? cached.thumb : (cardThumb || ''));
			var majorName  = cardMajor || cached.majorName || '';
			var schoolName = cardSchool || cached.schoolName || '';

			if (thumb && (thumb.indexOf('banner-') !== -1 || thumb.indexOf('photo-') !== -1 || thumb.indexOf('unsplash.com') !== -1 || thumb.indexOf('cropped-logo') !== -1)) {
				thumb = (cardThumb && cardThumb.indexOf('cropped-logo') === -1) ? cardThumb : '';
			}

			if (!majorName && title) {
				var cleanT = title.replace(/^Liên thông ngành\s+/iu, '');
				majorName = cleanT.split(/\s+-\s+/)[0] || cleanT;
			}

			if (majorName && !activeMajorName) {
				activeMajorName = majorName;
			}

			cache[id] = {
				title: title,
				thumb: thumb,
				majorName: majorName,
				schoolName: schoolName
			};

			var safeTitle = String(title).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
			var displayLabel = schoolName ? schoolName : (title ? title.replace(/^Liên thông ngành\s+/iu, '') : ('Mục #' + id));
			var safeLabel = String(displayLabel).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

			var imgTag = '';
			if (thumb && thumb.indexOf('cropped-logo') === -1) {
				var safeImgUrl = thumb.replace(/"/g, '&quot;');
				imgTag = '<img src="' + safeImgUrl + '" class="h-8 w-8 rounded object-contain p-0.5 bg-white border border-slate-200 shrink-0" alt="" onerror="this.style.display=\'none\';">';
			} else {
				var sName = schoolName || (title ? title.replace(/^Liên thông ngành\s+/iu, '') : 'UNI');
				var code = sName.replace(/^(Trường\s+)?(Đại\s+học|Học\s+viện|Cao\s+đẳng)\s+/iu, '').split(/\s+/).slice(0, 3).map(function(w) { return w ? w[0] : ''; }).join('').toUpperCase();
				if (!code) code = 'UNI';
				var safeCode = String(code).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				imgTag = '<div class="h-8 w-8 rounded bg-[#00308b] text-white font-black text-[10px] flex items-center justify-center shrink-0 uppercase tracking-tighter shadow-xs border border-blue-800">' + safeCode + '</div>';
			}

			var el = document.createElement('div');
			el.className = 'flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-1.5 text-sm shrink-0';
			el.title = safeTitle;
			el.innerHTML =
				imgTag +
				'<span class="font-semibold text-slate-700 truncate max-w-[160px]">' + safeLabel + '</span>' +
				'<button class="ltdh-tray-remove text-slate-400 hover:text-red-500 ml-1 text-xl leading-none w-7 h-7 flex items-center justify-center" data-type="' + activeType + '" data-id="' + id + '">&times;</button>';
			listEl.appendChild(el);
		});

		saveDetailsCache(cache);

		// Update tray major badge
		var trayMajor = tray.querySelector('.ltdh-tray-major');
		if (trayMajor) {
			if (activeMajorName) {
				trayMajor.textContent = 'Ngành: ' + activeMajorName;
				trayMajor.title = 'Ngành ' + activeMajorName;
				trayMajor.classList.remove('hidden');
				trayMajor.classList.add('inline-flex');
			} else {
				trayMajor.textContent = '';
				trayMajor.classList.add('hidden');
				trayMajor.classList.remove('inline-flex');
			}
		}

		// Bind remove buttons
		listEl.querySelectorAll('.ltdh-tray-remove').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var t = btn.getAttribute('data-type');
				var i = parseInt(btn.getAttribute('data-id'), 10);
				removeItem(t, i);
				updateTray();
			});
		});

		// Update counter
		var counter = tray.querySelector('.ltdh-tray-count');
		if (counter) {
			counter.textContent = totalCount + '/' + MAX_ITEMS;
		}

		// Update compare link
		var link = tray.querySelector('.ltdh-tray-link');
		if (link && activeItems.length >= 1) {
			var slug = generateCompareSlug(activeType, activeItems);
			link.href = homeUrl + 'so-sanh/' + getTypeSlug(activeType) + '/' + slug + '/';
			link.classList.remove('opacity-50', 'pointer-events-none');
		} else if (link) {
			link.href = '#';
			link.classList.add('opacity-50', 'pointer-events-none');
		}
	}

	function handleComparePageRemoval(id) {
		removeItem('program', id);
		var items = getItems();
		var activeItems = items['program'] || [];
		if (activeItems.length >= 1) {
			var slug = generateCompareSlug('program', activeItems);
			window.location.href = homeUrl + 'so-sanh/chuong-trinh/' + slug + '/';
		} else {
			window.location.href = homeUrl + 'hinh-thuc-dao-tao/';
		}
	}

	document.addEventListener('click', function (e) {
		var removeBtn = e.target.closest('.ltdh-compare-page-remove');
		if (!removeBtn) return;
		e.preventDefault();
		e.stopPropagation();
		var id = parseInt(removeBtn.getAttribute('data-id'), 10);
		if (id) {
			handleComparePageRemoval(id);
		}
	});

	function detectActiveType() {
		if (document.querySelector('[data-compare-type="program"]')) return 'program';
		return 'program';
	}

	function getTypeSlug(type) {
		return { program: 'chuong-trinh' }[type] || 'chuong-trinh';
	}

	function generateCompareSlug(type, ids) {
		var parts = [];
		ids.forEach(function (id) {
			var el = document.querySelector('[data-compare-id="' + id + '"][data-compare-slug]');
			if (el) {
				var slug = el.getAttribute('data-compare-slug');
				if (slug) { parts.push(slug); return; }
			}
			// Fallback
			parts.push('item-' + id);
		});
		return parts.join('-vs-');
	}

	// ----------------------------------------------------
	// 4. Toast Notifications
	// ----------------------------------------------------
	function showToast(message, type) {
		type = type || 'info';
		var existing = document.getElementById('ltdh-compare-toast');
		if (existing) existing.remove();

		var colors = {
			info: 'bg-blue-600',
			success: 'bg-emerald-600',
			warning: 'bg-amber-500',
			error: 'bg-red-600'
		};

		var toast = document.createElement('div');
		toast.id = 'ltdh-compare-toast';
		toast.className = 'fixed bottom-20 left-1/2 -translate-x-1/2 z-[100] ' + (colors[type] || colors.info) + ' text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold transition-all';
		toast.style.maxWidth = '90vw';
		toast.textContent = message;
		document.body.appendChild(toast);

		setTimeout(function () {
			toast.style.opacity = '0';
			toast.style.transform = 'translateX(-50%) translateY(10px)';
			setTimeout(function () { toast.remove(); }, 300);
		}, 3000);
	}

	// ----------------------------------------------------
	// 5. Init
	// ----------------------------------------------------
	function init() {
		initCompareDelegation();
		syncCompareButtonStates();

		var isComparePage = window.location.pathname.indexOf('/so-sanh/') !== -1;
		if (isComparePage) {
			var tray = document.getElementById('ltdh-compare-tray');
			if (tray) tray.classList.add('hidden');
		} else {
			updateTray();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Expose for external use
	window.ltdhCompare = {
		add: function (type, id, he, nganh, title, thumb) { addItem(type, id, he, nganh, title, thumb); updateTray(); syncCompareButtonStates(); },
		remove: function (type, id) { removeItem(type, id); updateTray(); syncCompareButtonStates(); },
		clearAll: clearAll,
		getItems: getItems,
		getCount: getCount,
		hasItem: hasItem,
		updateTray: updateTray,
		showToast: showToast,
		syncButtonStates: syncCompareButtonStates
	};
})();
