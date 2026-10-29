<?php
/**
 * Major Sticky Nav Styles & Scripts
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- STICKY SECTION NAV STYLES & SCROLLSPY -->
<style>
/* Header & Sticky Nav Positioning */
#masthead {
	transition: top 0.2s ease;
}
#ltdh-major-sticky-nav {
	transition: top 0.2s ease, box-shadow 0.2s ease;
}
body.admin-bar #masthead {
	top: 32px !important;
}
body.admin-bar #ltdh-major-sticky-nav {
	top: calc(80px + 32px) !important;
}
@media screen and (max-width: 782px) {
	body.admin-bar #masthead {
		top: 46px !important;
	}
	body.admin-bar #ltdh-major-sticky-nav {
		top: calc(80px + 46px) !important;
	}
}

/* Active tab style matching UI/UX Pro Max standards */
.ltdh-major-tab-link {
	border: 1px solid transparent;
}
.ltdh-major-tab-link.is-active {
	background-color: rgba(239, 246, 255, 0.95) !important;
	border-color: rgba(191, 219, 254, 0.85) !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-icon {
	background-color: #00308b !important;
	color: #ffffff !important;
	box-shadow: 0 2px 6px rgba(0, 48, 139, 0.25) !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-title {
	color: #00308b !important;
	font-weight: 800 !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-sub {
	color: #1d4ed8 !important;
	font-weight: 600 !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-indicator {
	background-color: #EA580C !important;
	opacity: 1 !important;
}
</style>

<script>
(function() {
	function initMajorStickyNav() {
		var stickyNav = document.getElementById('ltdh-major-sticky-nav');
		if (!stickyNav) return;

		var navContainer = stickyNav.querySelector('nav');
		var tabLinks = Array.prototype.slice.call(stickyNav.querySelectorAll('.ltdh-major-tab-link'));
		if (!tabLinks.length) return;

		var sectionMap = [];
		tabLinks.forEach(function(link) {
			var id = link.getAttribute('data-tab-target');
			var sec = document.getElementById(id);
			if (sec) {
				sectionMap.push({
					id: id,
					link: link,
					section: sec
				});
			}
		});

		if (!sectionMap.length) return;

		var isClickScrolling = false;
		var scrollTimeout = null;

		function getStickyOffset() {
			var header = document.getElementById('masthead');
			var headerHeight = header ? header.offsetHeight : 80;
			var navHeight = stickyNav.offsetHeight || 54;
			var adminBar = document.getElementById('wpadminbar');
			var adminBarHeight = (adminBar && window.getComputedStyle(adminBar).position === 'fixed') ? adminBar.offsetHeight : 0;
			return headerHeight + navHeight + adminBarHeight;
		}

		var currentActiveId = null;

		function setActiveTab(targetId, isFromUserClick) {
			if (targetId === currentActiveId && !isFromUserClick) return;
			currentActiveId = targetId;

			sectionMap.forEach(function(item) {
				var link = item.link;
				var iconEl = link.querySelector('.ltdh-tab-icon');
				var titleEl = link.querySelector('.ltdh-tab-title');
				var subEl = link.querySelector('.ltdh-tab-sub');
				var indicatorEl = link.querySelector('.ltdh-tab-indicator');

				if (item.id === targetId) {
					link.classList.add('bg-blue-50/90', 'border-blue-200/80', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.remove('text-slate-600', 'hover:bg-slate-100/70', 'border-transparent');
					if (iconEl) {
						iconEl.classList.add('bg-[#00308b]', 'text-white', 'shadow-xs', 'shadow-blue-900/20');
						iconEl.classList.remove('bg-slate-100', 'text-slate-500');
					}
					if (titleEl) {
						titleEl.classList.add('text-[#00308b]', 'font-extrabold');
						titleEl.classList.remove('text-slate-700');
					}
					if (subEl) {
						subEl.classList.add('text-blue-700/80', 'font-medium');
						subEl.classList.remove('text-slate-400');
					}
					if (indicatorEl) {
						indicatorEl.classList.add('bg-[#EA580C]', 'opacity-100');
						indicatorEl.classList.remove('bg-transparent', 'opacity-0');
					}

					if (navContainer && navContainer.scrollWidth > navContainer.clientWidth) {
						var tabLeft = link.offsetLeft;
						var tabWidth = link.offsetWidth;
						var containerWidth = navContainer.clientWidth;
						navContainer.scrollTo({
							left: tabLeft - (containerWidth / 2) + (tabWidth / 2),
							behavior: isFromUserClick ? 'smooth' : 'auto'
						});
					}
				} else {
					link.classList.remove('bg-blue-50/90', 'border-blue-200/80', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.add('text-slate-600', 'hover:bg-slate-100/70', 'border-transparent');
					if (iconEl) {
						iconEl.classList.remove('bg-[#00308b]', 'text-white', 'shadow-xs', 'shadow-blue-900/20');
						iconEl.classList.add('bg-slate-100', 'text-slate-500');
					}
					if (titleEl) {
						titleEl.classList.remove('text-[#00308b]', 'font-extrabold');
						titleEl.classList.add('text-slate-700');
					}
					if (subEl) {
						subEl.classList.remove('text-blue-700/80', 'font-medium');
						subEl.classList.add('text-slate-400');
					}
					if (indicatorEl) {
						indicatorEl.classList.remove('bg-[#EA580C]', 'opacity-100');
						indicatorEl.classList.add('bg-transparent', 'opacity-0');
					}
				}
			});
		}

		// Handle Click on Tabs
		tabLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				var targetId = this.getAttribute('data-tab-target');
				var targetItem = sectionMap.find(function(item) { return item.id === targetId; });
				if (!targetItem) return;

				isClickScrolling = true;
				clearTimeout(scrollTimeout);
				setActiveTab(targetId, true);

				var totalOffset = getStickyOffset();
				var elementPosition = targetItem.section.getBoundingClientRect().top + window.pageYOffset;
				var offsetPosition = elementPosition - totalOffset + 8; // gentle padding

				window.scrollTo({
					top: offsetPosition,
					behavior: 'smooth'
				});

				if (history.replaceState) {
					history.replaceState(null, '', '#' + targetId);
				}

				scrollTimeout = setTimeout(function() {
					isClickScrolling = false;
				}, 750);
			});
		});

		// Scrollspy with requestAnimationFrame
		var ticking = false;
		function onScroll() {
			if (isClickScrolling) return;

			var totalOffset = getStickyOffset();
			var scrollPos = window.pageYOffset + totalOffset + 50;
			var currentId = sectionMap[0].id;

			var atPageBottom = (window.innerHeight + window.pageYOffset) >= (document.documentElement.scrollHeight - 60);

			if (atPageBottom) {
				currentId = sectionMap[sectionMap.length - 1].id;
			} else {
				for (var i = 0; i < sectionMap.length; i++) {
					var item = sectionMap[i];
					var secTop = item.section.offsetTop;
					var secHeight = item.section.offsetHeight;
					if (scrollPos >= secTop && scrollPos < secTop + secHeight) {
						currentId = item.id;
						break;
					} else if (scrollPos >= secTop) {
						currentId = item.id;
					}
				}
			}

			if (currentId) {
				setActiveTab(currentId, false);
			}
		}

		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					onScroll();
					ticking = false;
				});
				ticking = true;
			}
		}, { passive: true });

		// Toggle All / Collapse All for Major Requirements & Target Applicants
		var toggleMajorReqDocsBtn = document.getElementById('ltdh-toggle-major-req-docs-btn');
		var majorReqDocsDetails = document.querySelectorAll('#ltdh-major-req-docs-accordion details');
		if (toggleMajorReqDocsBtn && majorReqDocsDetails.length) {
			var toggleMajorText = document.getElementById('ltdh-toggle-major-req-docs-text');
			var toggleMajorIcon = document.getElementById('ltdh-toggle-major-req-docs-icon');

			function updateMajorReqDocsState() {
				var allOpen = Array.prototype.slice.call(majorReqDocsDetails).every(function(d) { return d.open; });
				if (toggleMajorText) {
					toggleMajorText.textContent = allOpen ? 'Thu gọn' : 'Mở tất cả';
				}
				if (toggleMajorIcon) {
					if (allOpen) {
						toggleMajorIcon.classList.add('rotate-180');
					} else {
						toggleMajorIcon.classList.remove('rotate-180');
					}
				}
			}

			toggleMajorReqDocsBtn.addEventListener('click', function() {
				var hasClosed = Array.prototype.slice.call(majorReqDocsDetails).some(function(d) { return !d.open; });
				majorReqDocsDetails.forEach(function(d) {
					d.open = hasClosed;
				});
				updateMajorReqDocsState();
			});

			majorReqDocsDetails.forEach(function(d) {
				d.addEventListener('toggle', updateMajorReqDocsState);
			});

			function checkMajorReqDocsHash() {
				var h = window.location.hash;
				if (h === '#dieu-kien-xet-tuyen' || h === '#details-dieu-kien' || h === '#doi-tuong-tuyen-sinh' || h === '#details-doi-tuong' || h === '#nganh-dung-nganh-gan') {
					var item1 = document.getElementById('details-doi-tuong') || document.getElementById('details-dieu-kien');
					if (item1) { item1.open = true; }
				} else if (h === '#ho-so-can-nop' || h === '#details-ho-so') {
					var item2 = document.getElementById('details-ho-so');
					if (item2) { item2.open = true; }
				}
			}
			window.addEventListener('hashchange', checkMajorReqDocsHash);
			checkMajorReqDocsHash();
			updateMajorReqDocsState();
		}

		// Handle initial hash in URL
		if (window.location.hash) {
			var initialHash = window.location.hash.replace('#', '');
			var match = sectionMap.find(function(item) { return item.id === initialHash; });
			if (match) {
				setTimeout(function() {
					match.link.click();
				}, 250);
			} else {
				setTimeout(onScroll, 100);
			}
		} else {
			setTimeout(onScroll, 100);
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMajorStickyNav);
	} else {
		initMajorStickyNav();
	}
})();
</script>
