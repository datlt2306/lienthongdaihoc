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

	function addItem(type, id, title, thumb) {
		var items = getItems();
		if (!items[type]) items[type] = [];
		if (items[type].indexOf(id) === -1) {
			if (items[type].length >= MAX_ITEMS) return false;
			items[type].push(id);
			saveItems(items);

			// Save details
			var cache = getDetailsCache();
			cache[id] = { title: title || '', thumb: thumb || '' };
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
	// 2. Compare Button Toggle
	// ----------------------------------------------------
	function initCompareButtons() {
		var buttons = document.querySelectorAll('.ltdh-compare-toggle, .ltdh-compare-single-btn');
		buttons.forEach(function (btn) {
			var type = btn.getAttribute('data-compare-type');
			var id   = parseInt(btn.getAttribute('data-compare-id'), 10);
			if (!type || !id) return;

			if (hasItem(type, id)) {
				btn.classList.add('is-compared');
				btn.textContent = '✓ Đã thêm';
			}

			btn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();

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

						var btnTitle = btn.getAttribute('data-compare-title') || '';
					var btnThumb = btn.getAttribute('data-compare-thumb') || '';
					if (!btnThumb) {
						var cardEl = document.querySelector('[data-compare-id="' + id + '"][data-compare-thumb]');
						if (cardEl) {
							btnThumb = cardEl.getAttribute('data-compare-thumb') || '';
						}
					}
					if (!btnTitle) {
						var cardEl = document.querySelector('[data-compare-id="' + id + '"][data-compare-title]');
						if (cardEl) {
							btnTitle = cardEl.getAttribute('data-compare-title') || '';
						}
					}

					addItem(type, id, btnTitle, btnThumb);
					btn.classList.add('is-compared');
					btn.textContent = '✓ Đã thêm';
					showToast('Đã thêm vào danh sách so sánh (' + (total + 1) + '/' + MAX_ITEMS + ')', 'success');
				}
				updateTray();
			});
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

		if (totalCount < 2) {
			tray.classList.add('hidden');
			return;
		}

		tray.classList.remove('hidden');

		var listEl = tray.querySelector('.ltdh-tray-items');
		if (!listEl) return;

		listEl.innerHTML = '';
		var activeItems = items[activeType] || [];

		var cache = getDetailsCache();
		activeItems.forEach(function (id) {
			var cached = cache[id] || {};
			var title = cached.title || 'Mục #' + id;
			var thumb = cached.thumb || '';

			if (!cached.title || !cached.thumb) {
				var card = document.querySelector('[data-compare-id="' + id + '"][data-compare-thumb]');
				if (card) {
					title = card.getAttribute('data-compare-title') || title;
					thumb = card.getAttribute('data-compare-thumb') || thumb;
					cache[id] = { title: title, thumb: thumb };
					saveDetailsCache(cache);
				}
			}

			var el = document.createElement('div');
			el.className = 'flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-1.5 text-sm';
			el.innerHTML =
				(thumb ? '<img src="' + thumb + '" class="h-8 w-8 rounded object-cover" alt="">' : '') +
				'<span class="font-semibold text-slate-700 truncate max-w-[120px]">' + title + '</span>' +
				'<button class="ltdh-tray-remove text-slate-400 hover:text-red-500 ml-1 text-xl leading-none w-11 h-11 flex items-center justify-center" data-type="' + activeType + '" data-id="' + id + '">&times;</button>';
			listEl.appendChild(el);
		});

		// Bind remove buttons
		listEl.querySelectorAll('.ltdh-tray-remove').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var t = btn.getAttribute('data-type');
				var i = parseInt(btn.getAttribute('data-id'), 10);
				removeItem(t, i);
				// Update compare buttons in page
				var pageBtn = document.querySelector('[data-compare-type="' + t + '"][data-compare-id="' + i + '"]');
				if (pageBtn) {
					pageBtn.classList.remove('is-compared');
					pageBtn.textContent = 'So sánh';
				}
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
		if (link && activeItems.length >= 2) {
			var slug = generateCompareSlug(activeType, activeItems);
			link.href = homeUrl + 'so-sanh/' + getTypeSlug(activeType) + '/' + slug + '/';
			link.classList.remove('opacity-50', 'pointer-events-none');
		} else if (link) {
			link.href = '#';
			link.classList.add('opacity-50', 'pointer-events-none');
		}
	}

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
	document.addEventListener('DOMContentLoaded', function () {
		// Hide tray on comparison pages (already viewing comparison)
		var isComparePage = window.location.pathname.indexOf('/so-sanh/') !== -1;
		if (isComparePage) {
			var tray = document.getElementById('ltdh-compare-tray');
			if (tray) tray.classList.add('hidden');
			return;
		}

		initCompareButtons();
		updateTray();
	});

	// Expose for external use
	window.ltdhCompare = {
		add: function (type, id, title, thumb) { addItem(type, id, title, thumb); updateTray(); },
		remove: function (type, id) { removeItem(type, id); updateTray(); },
		clearAll: clearAll,
		getItems: getItems,
		getCount: getCount,
		hasItem: hasItem,
		updateTray: updateTray,
		showToast: showToast
	};
})();
