/**
 * Theme Javascript Logic
 * lienthongdaihoc.com
 *
 * Optimized for Google Core Web Vitals (INP < 200ms, non-blocking execution)
 */

// Global toggle function accessible from HTML onclick attributes
window.ltdhToggleSubmenu = window.ltdhToggleSubmenu || function(btn, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const li = btn.closest('li.menu-item-has-children');
    if (!li) return;
    const isOpen = li.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    btn.setAttribute('aria-label', isOpen ? 'Thu gọn menu con' : 'Mở rộng menu con');
};

// Global Share & Clipboard Utilities
window.ltdhCopyToClipboard = window.ltdhCopyToClipboard || function(text, onSuccess, onError) {
    if (!text) {
        if (onError) onError(new Error('No text provided to copy'));
        return;
    }

    function fallbackExecCopy(val, onSucc, onErr) {
        try {
            const textArea = document.createElement('textarea');
            textArea.value = val;
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';
            textArea.style.opacity = '0';
            textArea.setAttribute('readonly', '');
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            if (successful) {
                if (onSucc) onSucc();
            } else {
                if (onErr) onErr(new Error('execCommand copy failed'));
            }
        } catch (err) {
            if (onErr) onErr(err);
        }
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            if (onSuccess) onSuccess();
        }).catch(function() {
            fallbackExecCopy(text, onSuccess, onError);
        });
    } else {
        fallbackExecCopy(text, onSuccess, onError);
    }
};

window.ltdhOpenSharePopup = window.ltdhOpenSharePopup || function(e, url, title) {
    if (e && e.preventDefault) e.preventDefault();
    if (!url || url === '#' || url.startsWith('javascript:')) return false;

    const width = 600;
    const height = 550;
    const left = Math.max(0, (window.screen.width - width) / 2);
    const top = Math.max(0, (window.screen.height - height) / 2);
    const windowFeatures = `scrollbars=yes,resizable=yes,toolbar=no,location=yes,width=${width},height=${height},top=${top},left=${left}`;

    try {
        const win = window.open(url, title || 'ltdh_share_window', windowFeatures);
        if (!win || win.closed || typeof win.closed === 'undefined') {
            window.open(url, '_blank', 'noopener,noreferrer');
        } else {
            win.focus();
        }
    } catch (err) {
        window.open(url, '_blank', 'noopener,noreferrer');
    }
    return false;
};

window.ltdhOpenFacebookShare = window.ltdhOpenFacebookShare || function(e, btn) {
    if (e && e.preventDefault) e.preventDefault();
    if (!btn) return false;

    // Determine the exact URL of the post/program to share
    const rawDataUrl = btn.getAttribute('data-url');
    let targetUrl = rawDataUrl;

    if (!targetUrl && window.location.protocol.startsWith('http')) {
        targetUrl = window.location.href;
    } else if (targetUrl && !targetUrl.startsWith('http')) {
        targetUrl = window.location.origin + targetUrl;
    }

    const shareEndpoint = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(targetUrl || window.location.href);
    return window.ltdhOpenSharePopup(e, shareEndpoint, 'Facebook');
};

window.ltdhCopyPostLink = window.ltdhCopyPostLink || function(btn) {
    if (!btn) return;
    const rawUrl = btn.getAttribute('data-url') || window.location.href;
    const shareUrl = (window.location.protocol.startsWith('http') && window.location.href) ? window.location.href : rawUrl;
    const toast = document.getElementById('ltdh-share-toast');
    const toastMsg = document.getElementById('ltdh-share-toast-msg');

    function triggerToast(msg) {
        if (!toast) return;
        if (toastMsg && msg) toastMsg.textContent = msg;
        toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
        toast.classList.add('opacity-100', 'translate-y-0');
        clearTimeout(btn._toastTimer);
        btn._toastTimer = setTimeout(function() {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
        }, 2500);
    }

    if (!btn._origHtml) {
        btn._origHtml = btn.innerHTML;
    }

    const type = btn.getAttribute('data-type') || 'post';
    const successMsg = type === 'program'
        ? '✓ Đã sao chép liên kết chương trình vào bộ nhớ tạm!'
        : '✓ Đã sao chép liên kết bài viết vào bộ nhớ tạm!';

    window.ltdhCopyToClipboard(shareUrl, function() {
        btn.innerHTML = `
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-emerald-700 font-bold">Đã sao chép link!</span>
        `;
        btn.classList.add('bg-emerald-50', 'border-emerald-300');
        triggerToast(successMsg);

        clearTimeout(btn._resetTimer);
        btn._resetTimer = setTimeout(function() {
            btn.innerHTML = btn._origHtml;
            btn.classList.remove('bg-emerald-50', 'border-emerald-300');
        }, 2500);
    }, function() {
        triggerToast('Không thể tự động sao chép. Vui lòng copy URL từ thanh địa chỉ.');
    });
};

window.ltdhCopyProgramLink = window.ltdhCopyProgramLink || function(btn) {
    if (btn && !btn.getAttribute('data-type')) {
        btn.setAttribute('data-type', 'program');
    }
    return window.ltdhCopyPostLink(btn);
};

function ltdhInitTheme() {
    // ----------------------------------------------------
    // 1. Mobile Menu Offcanvas Drawer & Collapsible Accordion
    // ----------------------------------------------------
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const closeBtn  = document.getElementById('mobile-menu-close');
    const menu      = document.getElementById('mobile-menu');
    const overlay   = document.getElementById('mobile-menu-overlay');

    function openMenu() {
        if (!menu || !overlay) return;
        const header = document.getElementById('masthead');
        if (header) {
            header.classList.remove('is-hidden');
            document.documentElement.classList.remove('ltdh-header-hidden');
        }
        menu.classList.remove('translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMenu() {
        if (!menu || !overlay) return;
        menu.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        document.documentElement.classList.remove('overflow-hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (toggleBtn && menu && overlay && !toggleBtn._bound) {
        toggleBtn._bound = true;
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openMenu();
        }, { passive: false });
    }

    if (closeBtn && !closeBtn._bound) {
        closeBtn._bound = true;
        closeBtn.addEventListener('click', closeMenu, { passive: true });
    }

    if (overlay && !overlay._bound) {
        overlay._bound = true;
        overlay.addEventListener('click', closeMenu, { passive: true });
        overlay.addEventListener('touchmove', (e) => {
            e.preventDefault();
        }, { passive: false });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu && !menu.classList.contains('translate-x-full')) {
            closeMenu();
        }
    });

    // Close menu when clicking actual navigation links (delegated, ignores dummy toggles)
    if (menu && !menu._clickBound) {
        menu._clickBound = true;
        menu.addEventListener('click', (e) => {
            const toggleButton = e.target.closest('.mobile-submenu-toggle');
            if (toggleButton) {
                // Toggle clicked, do not navigate or close drawer
                return;
            }

            const link = e.target.closest('a');
            if (!link) return;

            const href = (link.getAttribute('href') || '').trim();
            if (!href || href === '#' || href.startsWith('javascript:')) {
                // Dummy/toggle link clicked - toggle accordion instead
                const parentLi = link.closest('li.menu-item-has-children');
                if (parentLi) {
                    const btn = parentLi.querySelector('.mobile-submenu-toggle');
                    if (btn) {
                        window.ltdhToggleSubmenu(btn, e);
                    }
                }
                return;
            }

            // Real navigation link clicked: close menu
            closeMenu();
        });
    }

    // ----------------------------------------------------
    // 1b. Mobile Floating Dynamic Bar - Scroll-Aware Auto-Hide (Apple iOS Style)
    // ----------------------------------------------------
    function initMobileFloatingBarScroll() {
        const bar = document.getElementById('ltdh-global-mobile-bar');
        if (!bar || bar._scrollInit) return;
        bar._scrollInit = true;

        let lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
        let ticking = false;
        const threshold = 8;
        let isHidden = false;

        function updateBarState() {
            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight;
            const diff = currentScrollY - lastScrollY;

            // 1. Near top of page: always show
            if (currentScrollY < 60) {
                if (isHidden) showBar();
            }
            // 2. Near bottom of page: always show
            else if (currentScrollY + windowHeight >= docHeight - 80) {
                if (isHidden) showBar();
            }
            // 3. Scroll Down: hide
            else if (diff > threshold && !isHidden) {
                hideBar();
            }
            // 4. Scroll Up: show
            else if (diff < -threshold && isHidden) {
                showBar();
            }

            lastScrollY = currentScrollY <= 0 ? 0 : currentScrollY;
            ticking = false;
        }

        function hideBar() {
            bar.classList.add('translate-y-[150%]', 'opacity-0', 'pointer-events-none');
            bar.classList.remove('translate-y-0', 'opacity-100');
            isHidden = true;
        }

        function showBar() {
            bar.classList.remove('translate-y-[150%]', 'opacity-0', 'pointer-events-none');
            bar.classList.add('translate-y-0', 'opacity-100');
            isHidden = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateBarState);
                ticking = true;
            }
        }, { passive: true });

        document.addEventListener('touchstart', (e) => {
            if (isHidden && e.touches && e.touches[0].clientY > window.innerHeight - 70) {
                showBar();
            }
        }, { passive: true });
    }

    initMobileFloatingBarScroll();

    // ----------------------------------------------------
    // 1c. Header Floating Island - Scroll-Aware Auto-Hide (Apple iOS Style)
    // ----------------------------------------------------
    function initHeaderScrollAutoHide() {
        const header = document.getElementById('masthead');
        if (!header || header._scrollAutoHideInit) return;
        header._scrollAutoHideInit = true;

        const mobileMenu = document.getElementById('mobile-menu');
        let lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
        let ticking = false;
        const threshold = 8;
        let isHidden = header.classList.contains('is-hidden');

        function hideHeader() {
            // Never hide if mobile menu drawer is open
            if (mobileMenu && !mobileMenu.classList.contains('translate-x-full')) return;
            header.classList.add('is-hidden');
            document.documentElement.classList.add('ltdh-header-hidden');
            isHidden = true;
        }

        function showHeader() {
            header.classList.remove('is-hidden');
            document.documentElement.classList.remove('ltdh-header-hidden');
            isHidden = false;
        }

        function updateHeaderState() {
            // ONLY execute auto-hide on mobile devices (< 1024px)
            if (window.innerWidth >= 1024) {
                if (isHidden) showHeader();
                ticking = false;
                return;
            }

            const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
            const diff = currentScrollY - lastScrollY;

            // 1. Near top of page: always show
            if (currentScrollY < 60) {
                if (isHidden) showHeader();
            }
            // 2. Scroll Down: hide
            else if (diff > threshold && !isHidden) {
                hideHeader();
            }
            // 3. Scroll Up: show
            else if (diff < -threshold && isHidden) {
                showHeader();
            }

            lastScrollY = currentScrollY <= 0 ? 0 : currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateHeaderState);
                ticking = true;
            }
        }, { passive: true });

        // Touch top edge to reveal header instantly on mobile
        document.addEventListener('touchstart', (e) => {
            if (isHidden && e.touches && e.touches[0].clientY < 60) {
                showHeader();
            }
        }, { passive: true });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && isHidden) {
                showHeader();
            }
        }, { passive: true });

        window.ltdhShowHeader = showHeader;
        window.ltdhHideHeader = hideHeader;
    }

    initHeaderScrollAutoHide();

    // ----------------------------------------------------
    // 2. Swiper Sliders Initialization (Hero & School Partners)
    // ----------------------------------------------------
    let swiperLoaded = false;
    let swiperLoading = false;
    const swiperCallbacks = [];

    const loadSwiperScript = (cb) => {
        if (typeof Swiper !== 'undefined') {
            swiperLoaded = true;
            if (cb) cb();
            return;
        }
        if (cb) swiperCallbacks.push(cb);
        if (swiperLoading) return;
        swiperLoading = true;

        const swiperUrl = (window.ltdh_ajax && window.ltdh_ajax.swiper_url)
            ? window.ltdh_ajax.swiper_url
            : (window.location.origin + '/wp-content/themes/lienthongdaihoc/assets/js/swiper-bundle.min.js');

        const s = document.createElement('script');
        s.src = swiperUrl;
        s.async = true;
        s.onload = () => {
            swiperLoaded = true;
            swiperLoading = false;
            window.dispatchEvent(new CustomEvent('ltdh:swiper:loaded'));
            while (swiperCallbacks.length) {
                try {
                    swiperCallbacks.shift()();
                } catch (e) {
                    console.error('Swiper callback error:', e);
                }
            }
        };
        s.onerror = () => {
            swiperLoading = false;
            console.error('Failed to load Swiper bundle');
        };
        document.head.appendChild(s);
    };

    const initSwipers = () => {
        if (typeof Swiper === 'undefined') {
            loadSwiperScript(initSwipers);
            return;
        }

        const heroSwiperEl = document.querySelector('.hero-swiper');
        if (heroSwiperEl && !heroSwiperEl.classList.contains('swiper-initialized')) {
            new Swiper('.hero-swiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                speed: 800,
                watchSlidesProgress: true,
            });
        }

        const initBelowFoldSwipers = () => {
            const schoolSwiperEl = document.querySelector('.school-partner-swiper');
            if (schoolSwiperEl && !schoolSwiperEl.classList.contains('swiper-initialized')) {
                new Swiper('.school-partner-swiper', {
                    slidesPerView: 1.25,
                    spaceBetween: 16,
                    rewind: true,
                    speed: 600,
                    watchSlidesProgress: true,
                    navigation: {
                        nextEl: '.school-slider-next',
                        prevEl: '.school-slider-prev',
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 2.2,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 3.2,
                            spaceBetween: 24,
                        },
                        1280: {
                            slidesPerView: 4,
                            spaceBetween: 24,
                        },
                    },
                });
            }

            const hotMajorsSwiperEl = document.querySelector('.hot-majors-swiper');
            if (hotMajorsSwiperEl && !hotMajorsSwiperEl.classList.contains('swiper-initialized')) {
                new Swiper('.hot-majors-swiper', {
                    slidesPerView: 2.2,
                    spaceBetween: 12,
                    watchSlidesProgress: true,
                    grabCursor: true,
                    pagination: {
                        el: '.hot-majors-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        480: {
                            slidesPerView: 2.5,
                            spaceBetween: 14,
                        },
                        640: {
                            slidesPerView: 3.5,
                            spaceBetween: 16,
                        },
                        1024: {
                            slidesPerView: 5,
                            spaceBetween: 16,
                            allowTouchMove: false,
                        },
                    },
                });
            }
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        obs.disconnect();
                        initBelowFoldSwipers();
                    }
                });
            }, { rootMargin: '300px 0px' });

            const target = document.querySelector('.school-partner-swiper') || document.querySelector('.hot-majors-swiper');
            if (target) {
                observer.observe(target);
            } else {
                initBelowFoldSwipers();
            }
        } else {
            setTimeout(initBelowFoldSwipers, 800);
        }
    };

    // Smart on-demand loading of Swiper bundle
    const swiperElements = document.querySelectorAll('.swiper, .hero-swiper, .school-partner-swiper, .hot-majors-swiper, .cert-swiper');
    if (swiperElements.length > 0) {
        let swiperTriggered = false;
        const triggerSwiper = () => {
            if (swiperTriggered) return;
            swiperTriggered = true;
            ['scroll', 'touchstart', 'mousemove', 'keydown', 'click'].forEach(ev => {
                window.removeEventListener(ev, triggerSwiper, { passive: true });
            });
            initSwipers();
        };

        ['scroll', 'touchstart', 'mousemove', 'keydown', 'click'].forEach(ev => {
            window.addEventListener(ev, triggerSwiper, { passive: true, once: true });
        });

        // Fast path: if user hovers or touches swiper directly or clicks arrow
        swiperElements.forEach(el => {
            el.addEventListener('mouseenter', triggerSwiper, { passive: true, once: true });
            el.addEventListener('touchstart', triggerSwiper, { passive: true, once: true });
        });

        // Fallback timer: load after 3500ms for idle visitors
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                setTimeout(triggerSwiper, 3500);
            });
        } else {
            setTimeout(triggerSwiper, 3500);
        }
    }

    // ----------------------------------------------------
    // Reusable AJAX Loading Indicator Overlay
    // ----------------------------------------------------
    function showLoadingOverlay(container, message) {
        if (!container) return;
        message = message || 'Đang tải dữ liệu...';
        const wrapper = container.closest('#program-results-wrapper, #major-results-wrapper, #ltdh-major-programs-wrapper') || container;
        let overlay = wrapper.querySelector(':scope > .ltdh-loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'ltdh-loading-overlay absolute inset-0 bg-white/80 backdrop-blur-[2px] z-30 flex flex-col items-center justify-center min-h-[220px] rounded-2xl transition-opacity duration-200';
            overlay.innerHTML = `
                <div class="flex flex-col items-center gap-3 p-5 sm:p-6 bg-white/95 rounded-2xl shadow-xl border border-slate-100 max-w-[260px] text-center pointer-events-none">
                    <svg class="w-9 h-9 text-[#00308b] animate-spin shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <div class="flex flex-col items-center">
                        <span class="text-xs sm:text-sm font-bold text-slate-800 tracking-wide">${message}</span>
                        <span class="text-[11px] text-slate-400 mt-0.5">Vui lòng chờ trong giây lát</span>
                    </div>
                </div>
            `;
            if (getComputedStyle(wrapper).position === 'static') {
                wrapper.style.position = 'relative';
            }
            wrapper.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        overlay.style.opacity = '1';
        container.style.opacity = '0.35';
        container.style.pointerEvents = 'none';
    }

    function hideLoadingOverlay(container) {
        if (!container) return;
        const wrapper = container.closest('#program-results-wrapper, #major-results-wrapper, #ltdh-major-programs-wrapper') || container;
        const overlay = wrapper.querySelector(':scope > .ltdh-loading-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                if (overlay && overlay.parentNode === wrapper) {
                    overlay.remove();
                }
            }, 180);
        }
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    }

    // ----------------------------------------------------
    // Shared Mobile Drawer Scroll Lock Manager (Prevents Background Touch/Wheel Scroll)
    // ----------------------------------------------------
    let ltdhDrawerTouchStartY = 0;

    function ltdhDrawerTouchStart(e) {
        if (e.touches && e.touches.length === 1) {
            ltdhDrawerTouchStartY = e.touches[0].clientY;
        }
    }

    function ltdhDrawerTouchMoveLock(e) {
        if (e.target.closest('button, a, input, select, label')) return;
        const scrollable = e.target.closest('#catalog-drawer-major-filter-wrapper, #catalog-drawer-filter-wrapper, #home-drawer-filter-wrapper');
        if (!scrollable) {
            if (e.cancelable) e.preventDefault();
            return;
        }

        if (e.touches && e.touches.length === 1) {
            const currentY = e.touches[0].clientY;
            const deltaY = currentY - ltdhDrawerTouchStartY;

            if (scrollable.scrollTop <= 0 && deltaY > 0) {
                if (e.cancelable) e.preventDefault();
                return;
            }

            const maxScroll = scrollable.scrollHeight - scrollable.clientHeight;
            if (scrollable.scrollTop >= maxScroll && deltaY < 0) {
                if (e.cancelable) e.preventDefault();
                return;
            }
        }
    }

    function ltdhLockDrawerScroll() {
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');
        document.addEventListener('touchstart', ltdhDrawerTouchStart, { passive: true });
        document.addEventListener('touchmove', ltdhDrawerTouchMoveLock, { passive: false });
    }

    function ltdhUnlockDrawerScroll() {
        document.documentElement.classList.remove('overflow-hidden');
        document.body.classList.remove('overflow-hidden');
        document.removeEventListener('touchstart', ltdhDrawerTouchStart);
        document.removeEventListener('touchmove', ltdhDrawerTouchMoveLock);
    }

    // ----------------------------------------------------
    // 3. Program Search & AJAX Filtering with SEO URL Sync
    // ----------------------------------------------------
    function initProgramArchiveFilter() {
        const filterForm = document.getElementById('catalog-filter-form') || document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"], form[action*="/hinh-thuc-dao-tao/"]');
        const container = document.getElementById('program-results-container');
        if (!filterForm || !container) return;

        const headingTitle = document.getElementById('catalog-heading-title');
        const countDisplay = document.getElementById('catalog-count-display');
        const desktopResetWrapper = document.getElementById('catalog-desktop-reset');
        const mobileResetWrapper = document.getElementById('catalog-mobile-reset');
        const mobileFilterBadge = document.getElementById('catalog-mobile-filter-badge');
        const mobileChipsWrapper = document.getElementById('catalog-mobile-chips');
        const pillsContainer = document.getElementById('catalog-pills-container');
        const paginationContainer = document.getElementById('catalog-pagination-container');
        const mobileFilterBtn = document.getElementById('ltdh-mobile-filter-btn');
        const mobileFilterBody = document.getElementById('catalog-filter-body');
        const mobileFilterChevron = document.getElementById('ltdh-mobile-filter-chevron');
        const mobileFilterBtnText = document.getElementById('ltdh-mobile-filter-btn-text');
        const mobileCloseBtn = document.getElementById('ltdh-mobile-filter-close');

        // Mobile Off-Canvas Drawer Elements
        const filterDrawer = document.getElementById('ltdh-filter-drawer');
        const filterOverlay = document.getElementById('ltdh-filter-drawer-overlay');
        const drawerCloseBtn = document.getElementById('ltdh-filter-drawer-close');
        const drawerApplyBtn = document.getElementById('ltdh-drawer-apply-btn');
        const desktopWrapper = document.getElementById('catalog-desktop-filter-wrapper');
        const drawerWrapper = document.getElementById('catalog-drawer-filter-wrapper');

        function relocateDrawerForm() {
            if (!mobileFilterBody) return;
            if (window.innerWidth < 768) {
                if (drawerWrapper && !drawerWrapper.contains(mobileFilterBody)) {
                    drawerWrapper.appendChild(mobileFilterBody);
                }
            } else {
                if (desktopWrapper && !desktopWrapper.contains(mobileFilterBody)) {
                    desktopWrapper.appendChild(mobileFilterBody);
                }
                closeFilterDrawer();
            }
        }

        function openFilterDrawer() {
            if (!filterDrawer || !filterOverlay) return;
            relocateDrawerForm();
            filterDrawer.classList.remove('-translate-x-full');
            filterDrawer.classList.add('translate-x-0');
            filterOverlay.classList.remove('opacity-0', 'pointer-events-none');
            filterOverlay.classList.add('opacity-100', 'pointer-events-auto');
            ltdhLockDrawerScroll();
            if (mobileFilterBtn) mobileFilterBtn.setAttribute('aria-expanded', 'true');
        }

        function closeFilterDrawer() {
            if (!filterDrawer || !filterOverlay) return;
            filterDrawer.classList.remove('translate-x-0');
            filterDrawer.classList.add('-translate-x-full');
            filterOverlay.classList.remove('opacity-100', 'pointer-events-auto');
            filterOverlay.classList.add('opacity-0', 'pointer-events-none');
            ltdhUnlockDrawerScroll();
            if (mobileFilterBtn) mobileFilterBtn.setAttribute('aria-expanded', 'false');
        }

        if (filterOverlay && !filterOverlay._scrollBlocked) {
            filterOverlay._scrollBlocked = true;
            filterOverlay.addEventListener('touchmove', (e) => {
                if (e.cancelable) e.preventDefault();
            }, { passive: false });
            filterOverlay.addEventListener('wheel', (e) => {
                if (e.cancelable) e.preventDefault();
            }, { passive: false });
        }

        relocateDrawerForm();
        window.addEventListener('resize', relocateDrawerForm);

        if (mobileFilterBtn && !mobileFilterBtn._boundDrawer) {
            mobileFilterBtn._boundDrawer = true;
            mobileFilterBtn.addEventListener('click', openFilterDrawer);
        }
        if (drawerCloseBtn && !drawerCloseBtn._boundDrawer) {
            drawerCloseBtn._boundDrawer = true;
            drawerCloseBtn.addEventListener('click', closeFilterDrawer);
        }
        if (filterOverlay && !filterOverlay._boundDrawer) {
            filterOverlay._boundDrawer = true;
            filterOverlay.addEventListener('click', closeFilterDrawer);
        }
        if (drawerApplyBtn && !drawerApplyBtn._boundDrawer) {
            drawerApplyBtn._boundDrawer = true;
            drawerApplyBtn.addEventListener('click', closeFilterDrawer);
        }
        const drawerResetBtn = document.getElementById('ltdh-drawer-reset-btn');
        if (drawerResetBtn && !drawerResetBtn._boundProgramReset) {
            drawerResetBtn._boundProgramReset = true;
            drawerResetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (regionSelect) regionSelect.value = '';
                if (schoolSelect) schoolSelect.value = '';
                if (majorSelect) majorSelect.value = '';
                if (sortSelect) sortSelect.value = '';
                if (heInput) heInput.value = '';
                triggerFilter({ paged: 1, pushUrl: true });
                closeFilterDrawer();
            });
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeFilterDrawer();
        });

        window.ltdhCloseFilterDrawer = closeFilterDrawer;
        window.ltdhOpenFilterDrawer = openFilterDrawer;

        const searchInput = filterForm.querySelector('input[name="s"]');
        const regionSelect = filterForm.querySelector('select[name="khu_vuc"]');
        const schoolSelect = filterForm.querySelector('select[name="truong"]');
        const majorSelect = filterForm.querySelector('select[name="nganh"]');
        const sortSelect = filterForm.querySelector('select[name="sort"]');
        let heInput = filterForm.querySelector('input[name="he"]');
        if (!heInput) {
            heInput = document.createElement('input');
            heInput.type = 'hidden';
            heInput.name = 'he';
            heInput.value = '';
            filterForm.appendChild(heInput);
        }

        let searchTimeout = null;
        let activeAbortController = null;
        
        // Base archive root
        let rootArchiveUrl = '/hinh-thuc-dao-tao/';
        if (window.location.pathname.startsWith('/chuong-trinh')) {
            rootArchiveUrl = '/chuong-trinh/';
        }

        // Initialize active training type
        const pathMatch = window.location.pathname.match(/^\/(?:hinh-thuc-dao-tao|he-dao-tao)\/([^\/]+)(?:\/page\/\d+)?\/?$/i);
        if (pathMatch && pathMatch[1] && pathMatch[1] !== 'page') {
            heInput.value = decodeURIComponent(pathMatch[1]);
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('he')) {
                heInput.value = urlParams.get('he');
            }
        }

        function updatePillStyles(activeTypeSlug) {
            if (!pillsContainer) return;
            const pills = pillsContainer.querySelectorAll('.js-ltdh-type-pill');
            pills.forEach(pill => {
                const pillType = pill.getAttribute('data-type') || '';
                const badge = pill.querySelector('.pill-badge');
                const isMatch = (pillType === (activeTypeSlug || '')) ||
                    ((activeTypeSlug === 'tu-xa' || activeTypeSlug === 'dao-tao-tu-xa') && (pillType === 'tu-xa' || pillType === 'dao-tao-tu-xa'));
                if (isMatch) {
                    pill.classList.add('bg-brand-primary', 'text-white', 'shadow-xs');
                    pill.classList.remove('bg-slate-100', 'hover:bg-slate-200', 'text-slate-700');
                    if (badge) {
                        badge.classList.add('bg-white/20', 'text-white');
                        badge.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200/60');
                    }
                } else {
                    pill.classList.remove('bg-brand-primary', 'text-white', 'shadow-xs');
                    pill.classList.add('bg-slate-100', 'hover:bg-slate-200', 'text-slate-700');
                    if (badge) {
                        badge.classList.remove('bg-white/20', 'text-white');
                        badge.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200/60');
                    }
                }
            });
        }

        function buildCurrentUrl(paged = 1) {
            const params = new URLSearchParams();
            const sVal = (searchInput ? searchInput.value : '').trim();
            const regionVal = regionSelect ? regionSelect.value : '';
            const schoolVal = schoolSelect ? schoolSelect.value : '';
            const majorVal = majorSelect ? majorSelect.value : '';
            const sortVal = sortSelect ? sortSelect.value : '';
            const heVal = (heInput ? heInput.value : '').trim();

            let targetPath = rootArchiveUrl;
            if (heVal && rootArchiveUrl === '/hinh-thuc-dao-tao/') {
                targetPath = `${rootArchiveUrl}${encodeURIComponent(heVal)}/`;
            } else if (heVal) {
                params.set('he', heVal);
            }

            if (regionVal) params.set('khu_vuc', regionVal);
            if (schoolVal) params.set('truong', schoolVal);
            if (majorVal) params.set('nganh', majorVal);
            if (sVal) params.set('s', sVal);
            if (sortVal) params.set('sort', sortVal);
            if (paged > 1) params.set('paged', paged);

            const queryString = params.toString();
            return queryString ? `${targetPath}?${queryString}` : targetPath;
        }

        function triggerFilter(options = {}) {
            const paged = options.paged || 1;
            const pushUrl = options.pushUrl !== false;
            const isTyping = options.isTyping || false;
            const scrollToTop = options.scrollToTop || false;

            const targetUrl = buildCurrentUrl(paged);

            if (pushUrl) {
                if (isTyping) {
                    window.history.replaceState({ ltdhProgramFilter: true, paged: paged }, '', targetUrl);
                } else {
                    window.history.pushState({ ltdhProgramFilter: true, paged: paged }, '', targetUrl);
                }
            }

            const currentHe = (heInput ? heInput.value : '').trim();
            updatePillStyles(currentHe);

            if (activeAbortController) {
                activeAbortController.abort();
            }
            activeAbortController = new AbortController();

            // Display loading overlay with spinner
            showLoadingOverlay(container, 'Đang tìm chương trình...');

            const formData = new FormData();
            formData.append('action', 'ltdh_filter_programs');
            formData.append('paged', paged);
            if (currentHe) formData.append('he', currentHe);
            if (searchInput && searchInput.value.trim()) formData.append('s', searchInput.value.trim());
            if (regionSelect && regionSelect.value) formData.append('khu_vuc', regionSelect.value);
            if (schoolSelect && schoolSelect.value) formData.append('truong', schoolSelect.value);
            if (majorSelect && majorSelect.value) formData.append('nganh', majorSelect.value);
            if (sortSelect && sortSelect.value) formData.append('sort', sortSelect.value);

            const ajaxUrl = (typeof ltdh_ajax !== 'undefined' && ltdh_ajax.ajax_url)
                ? ltdh_ajax.ajax_url
                : '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                signal: activeAbortController.signal
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const data = res.data;

                    container.innerHTML = data.html;

                    if (headingTitle && data.heading_title) {
                        headingTitle.textContent = data.heading_title;
                    }
                    if (countDisplay && typeof data.found_posts !== 'undefined') {
                        countDisplay.textContent = data.found_posts;
                    }
                    const mobileCountDisplay = document.getElementById('catalog-count-display-mobile');
                    if (mobileCountDisplay && typeof data.found_posts !== 'undefined') {
                        mobileCountDisplay.textContent = data.found_posts;
                    }
                    document.querySelectorAll('.js-filter-count-badge').forEach(b => {
                        b.textContent = `(${data.found_posts})`;
                    });

                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination_html || '';
                    }

                    if (mobileChipsWrapper) {
                        mobileChipsWrapper.innerHTML = data.chips_html || '';
                    }

                    const resetBtnHtml = `<a href="${rootArchiveUrl}" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50/80 hover:bg-rose-100/80 border border-rose-100 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]"><span>✕</span> <span>Xóa tất cả bộ lọc</span></a>`;
                    const mobileResetHtml = `<a href="${rootArchiveUrl}" class="js-ltdh-reset-filter inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 min-h-[38px] transition-colors" title="Xóa tất cả bộ lọc"><span>✕ Xóa lọc</span></a>`;

                    if (desktopResetWrapper) {
                        desktopResetWrapper.innerHTML = data.has_active_filters ? resetBtnHtml : '';
                    }
                    if (mobileResetWrapper) {
                        mobileResetWrapper.innerHTML = data.has_active_filters ? mobileResetHtml : '';
                    }

                    if (mobileFilterBadge) {
                        mobileFilterBadge.innerHTML = (data.active_filter_count > 0)
                            ? `<span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[18px]">${data.active_filter_count}</span>`
                            : '';
                    }
                    if (mobileFilterBtn) {
                        if (data.has_active_filters) {
                            mobileFilterBtn.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-bold text-xs transition-all duration-200 min-h-[38px] cursor-pointer shadow-2xs bg-blue-50 border-blue-200 text-[#00308b]';
                        } else {
                            mobileFilterBtn.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-bold text-xs transition-all duration-200 min-h-[38px] cursor-pointer shadow-2xs bg-white hover:bg-slate-50 border-slate-200 text-slate-700';
                        }
                    }

                    if (window.ltdhCompare && typeof window.ltdhCompare.syncButtonStates === 'function') {
                        window.ltdhCompare.syncButtonStates();
                    }

                    if (scrollToTop) {
                        const targetEl = document.getElementById('catalog-filter-body') || container;
                        const topOffset = targetEl.getBoundingClientRect().top + window.pageYOffset - 100;
                        window.scrollTo({ top: topOffset, behavior: 'smooth' });
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Error filtering programs:', err);
                }
            })
            .finally(() => {
                hideLoadingOverlay(container);
            });
        }

        // 1. Form submit event
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerFilter({ paged: 1, pushUrl: true });
            closeFilterDrawer();
        });

        // 2. Dropdown changes
        [regionSelect, schoolSelect, majorSelect, sortSelect].forEach(select => {
            if (!select) return;
            select.removeAttribute('onchange');
            select.addEventListener('change', () => {
                triggerFilter({ paged: 1, pushUrl: true });
            });
        });

        // 3. Search input (debounced)
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    triggerFilter({ paged: 1, pushUrl: true, isTyping: true });
                }, 350);
            });
        }

        // 4. Training Type Pills Click
        if (pillsContainer) {
            pillsContainer.addEventListener('click', (e) => {
                const pill = e.target.closest('.js-ltdh-type-pill');
                if (!pill) return;
                e.preventDefault();

                const typeSlug = pill.getAttribute('data-type') || '';
                if (heInput) {
                    heInput.value = typeSlug;
                }
                triggerFilter({ paged: 1, pushUrl: true });
            });
        }

        // 5. Pagination Click (Delegated for catalog)
        document.addEventListener('click', (e) => {
            if (!container || !document.body.contains(container)) return;
            const pageLink = e.target.closest('#catalog-pagination-container a.page-numbers');
            if (!pageLink) return;
            e.preventDefault();

            const href = pageLink.getAttribute('href') || '';
            let targetPage = 1;

            const pagedMatch = href.match(/[?&]paged=(\d+)/);
            if (pagedMatch) {
                targetPage = parseInt(pagedMatch[1], 10);
            } else {
                const slashMatch = href.match(/\/page\/(\d+)\/?/);
                if (slashMatch) {
                    targetPage = parseInt(slashMatch[1], 10);
                } else {
                    const textNum = parseInt(pageLink.textContent.trim(), 10);
                    if (!isNaN(textNum)) {
                        targetPage = textNum;
                    }
                }
            }

            triggerFilter({ paged: targetPage, pushUrl: true, scrollToTop: true });
        });

        // 6. Reset Filters (Delegated for catalog)
        document.addEventListener('click', (e) => {
            if (!container || !document.body.contains(container)) return;
            const resetBtn = e.target.closest('#catalog-desktop-reset .js-ltdh-reset-filter, #catalog-mobile-reset .js-ltdh-reset-filter, #ltdh-drawer-reset-btn, .js-ltdh-reset-filter');
            if (!resetBtn) return;
            if (resetBtn.closest('#ltdh-major-filter-drawer, #catalog-desktop-major-filter-wrapper, #major-results-wrapper, #major-mobile-chips, #major-mobile-reset, #major-desktop-reset')) return;
            e.preventDefault();

            if (searchInput) searchInput.value = '';
            if (regionSelect) regionSelect.value = '';
            if (schoolSelect) schoolSelect.value = '';
            if (majorSelect) majorSelect.value = '';
            if (sortSelect) sortSelect.value = '';
            if (heInput) heInput.value = '';

            triggerFilter({ paged: 1, pushUrl: true });
            closeFilterDrawer();
        });

        // 7. Chip Remove Click (Delegated for catalog)
        document.addEventListener('click', (e) => {
            if (!container || !document.body.contains(container)) return;
            const chip = e.target.closest('#catalog-mobile-chips .js-ltdh-chip-remove');
            if (!chip) return;
            e.preventDefault();

            const key = chip.getAttribute('data-filter-key');
            if (key === 's' && searchInput) searchInput.value = '';
            if (key === 'khu_vuc' && regionSelect) regionSelect.value = '';
            if (key === 'truong' && schoolSelect) schoolSelect.value = '';
            if (key === 'nganh' && majorSelect) majorSelect.value = '';
            if (key === 'sort' && sortSelect) sortSelect.value = '';
            if (key === 'he' && heInput) heInput.value = '';

            triggerFilter({ paged: 1, pushUrl: true });
        });

        // 8. Popstate (Browser Back / Forward)
        window.addEventListener('popstate', () => {
            if (!container || !document.body.contains(container)) return;
            const params = new URLSearchParams(window.location.search);
            const sVal = params.get('s') || '';
            const regionVal = params.get('khu_vuc') || params.get('khu-vuc') || '';
            const schoolVal = params.get('truong') || '';
            const majorVal = params.get('nganh') || params.get('nhom_nganh') || '';
            const sortVal = params.get('sort') || '';
            const pagedVal = parseInt(params.get('paged') || '1', 10);

            let heVal = params.get('he') || params.get('training_type') || '';
            const currentPathMatch = window.location.pathname.match(/^\/(?:hinh-thuc-dao-tao|he-dao-tao)\/([^\/]+)(?:\/page\/\d+)?\/?$/i);
            if (currentPathMatch && currentPathMatch[1] && currentPathMatch[1] !== 'page') {
                heVal = decodeURIComponent(currentPathMatch[1]);
            }

            if (searchInput) searchInput.value = sVal;
            if (regionSelect) regionSelect.value = regionVal;
            if (schoolSelect) schoolSelect.value = schoolVal;
            if (majorSelect) majorSelect.value = majorVal;
            if (sortSelect) sortSelect.value = sortVal;
            if (heInput) heInput.value = heVal;

            updatePillStyles(heVal);
            triggerFilter({ paged: pagedVal, pushUrl: false });
        });
    }

    initProgramArchiveFilter();

    // Single Major Page Filter (Training Type Pills & School Select Dropdown)
    function initMajorPageFilter() {
        const wrapper = document.getElementById('ltdh-major-programs-wrapper');
        if (!wrapper) return;

        const majorId = wrapper.dataset.majorId;
        const listContainer = document.getElementById('ltdh-major-programs-list');
        const pills = document.querySelectorAll('.ltdh-major-he-pill');

        if (!listContainer || !majorId) return;

        function doFilter(pushUrl = true) {
            showLoadingOverlay(listContainer, 'Đang tải danh sách...');

            const activePill = document.querySelector('.ltdh-major-he-pill.is-active');
            const selectedHe = activePill ? (activePill.dataset.he || '') : '';

            const currentSchoolSlug = new URLSearchParams(window.location.search).get('truong')
                || new URLSearchParams(window.location.search).get('from_school')
                || new URLSearchParams(window.location.search).get('school')
                || (wrapper ? (wrapper.dataset.schoolSlug || '') : '');

            if (pushUrl) {
                const urlParams = new URLSearchParams(window.location.search);
                if (selectedHe) {
                    urlParams.set('he', selectedHe);
                } else {
                    urlParams.delete('he');
                }
                if (currentSchoolSlug) {
                    urlParams.set('truong', currentSchoolSlug);
                }

                const searchStr = urlParams.toString();
                const newUrl = window.location.pathname + (searchStr ? '?' + searchStr : '');
                window.history.pushState({ path: newUrl }, '', newUrl);
            }

            const formData = new FormData();
            formData.append('action', 'ltdh_filter_major_programs');
            formData.append('major_id', majorId);
            formData.append('he', selectedHe);
            if (currentSchoolSlug) {
                formData.append('school', currentSchoolSlug);
            }

            const ajaxUrl = (typeof ltdh_ajax !== 'undefined' && ltdh_ajax.ajax_url)
                ? ltdh_ajax.ajax_url
                : '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    listContainer.innerHTML = data.data.html;
                    const titleEl = document.getElementById('ltdh-major-programs-title');
                    if (titleEl) {
                        titleEl.textContent = 'Trường đang tuyển sinh liên thông';
                    }
                    if (window.ltdhCompare && typeof window.ltdhCompare.syncButtonStates === 'function') {
                        window.ltdhCompare.syncButtonStates();
                    }
                }
            })
            .catch(err => console.error('Error filtering major programs:', err))
            .finally(() => {
                hideLoadingOverlay(listContainer);
            });
        }

        pills.forEach(pill => {
            pill.addEventListener('click', function() {
                pills.forEach(p => {
                    p.classList.remove('bg-[#00308b]', 'text-white', 'shadow-xs', 'is-active');
                    p.classList.add('bg-transparent', 'text-slate-600', 'hover:bg-white/60');
                });
                this.classList.remove('bg-transparent', 'text-slate-600', 'hover:bg-white/60');
                this.classList.add('bg-[#00308b]', 'text-white', 'shadow-xs', 'is-active');

                doFilter(true);
            });
        });

        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentHe = urlParams.get('he') || '';

            pills.forEach(p => {
                const pHe = p.dataset.he || '';
                if (pHe === currentHe) {
                    p.classList.remove('bg-transparent', 'text-slate-600', 'hover:bg-white/60');
                    p.classList.add('bg-[#00308b]', 'text-white', 'shadow-xs', 'is-active');
                } else {
                    p.classList.remove('bg-[#00308b]', 'text-white', 'shadow-xs', 'is-active');
                    p.classList.add('bg-transparent', 'text-slate-600', 'hover:bg-white/60');
                }
            });

        doFilter(false);
        });
    }

    initMajorPageFilter();

    // ----------------------------------------------------
    // 4b. Major Archive AJAX Filter & SEO URL Synchronization
    // ----------------------------------------------------
    function initMajorArchiveFilter() {
        const filterForm = document.getElementById('major-filter-form');
        const container = document.getElementById('major-results-container');
        if (!filterForm || !container) return;

        const headingTitle = document.getElementById('major-heading-title');
        const countDisplay = document.getElementById('major-count-display');
        const desktopResetWrapper = document.getElementById('major-desktop-reset');
        const mobileResetWrapper = document.getElementById('major-mobile-reset');
        const mobileFilterBadge = document.getElementById('major-mobile-filter-badge');
        const mobileChipsWrapper = document.getElementById('major-mobile-chips');
        const mobileApplyBtnCount = document.getElementById('major-apply-btn-count');
        const pillsContainer = document.getElementById('major-pills-container');
        const paginationContainer = document.getElementById('major-pagination-container');
        const mobileFilterBtn = document.getElementById('ltdh-mobile-major-filter-btn') || document.getElementById('ltdh-mobile-filter-btn');
        const mobileFilterBody = document.getElementById('major-filter-body');
        const mobileFilterChevron = document.getElementById('ltdh-mobile-filter-chevron');
        const mobileFilterBtnText = document.getElementById('ltdh-mobile-major-filter-btn-text') || document.getElementById('ltdh-mobile-filter-btn-text');
        const mobileCloseBtn = document.getElementById('ltdh-mobile-filter-close');

        // Mobile Off-Canvas Drawer Elements
        const filterDrawer = document.getElementById('ltdh-major-filter-drawer');
        const filterOverlay = document.getElementById('ltdh-major-filter-drawer-overlay');
        const drawerCloseBtn = document.getElementById('ltdh-major-filter-drawer-close');
        const drawerApplyBtn = document.getElementById('ltdh-major-drawer-apply-btn');
        const desktopWrapper = document.getElementById('catalog-desktop-major-filter-wrapper');
        const drawerWrapper = document.getElementById('catalog-drawer-major-filter-wrapper');

        function relocateMajorDrawerForm() {
            if (!mobileFilterBody) return;
            if (window.innerWidth < 768) {
                if (drawerWrapper && !drawerWrapper.contains(mobileFilterBody)) {
                    drawerWrapper.appendChild(mobileFilterBody);
                }
            } else {
                if (desktopWrapper && !desktopWrapper.contains(mobileFilterBody)) {
                    desktopWrapper.appendChild(mobileFilterBody);
                }
                closeMajorFilterDrawer();
            }
        }

        function openMajorFilterDrawer() {
            if (!filterDrawer || !filterOverlay) return;
            relocateMajorDrawerForm();
            filterDrawer.classList.remove('-translate-x-full');
            filterDrawer.classList.add('translate-x-0');
            filterOverlay.classList.remove('opacity-0', 'pointer-events-none');
            filterOverlay.classList.add('opacity-100', 'pointer-events-auto');
            ltdhLockDrawerScroll();
            if (mobileFilterBtn) mobileFilterBtn.setAttribute('aria-expanded', 'true');
        }

        function closeMajorFilterDrawer() {
            if (!filterDrawer || !filterOverlay) return;
            filterDrawer.classList.remove('translate-x-0');
            filterDrawer.classList.add('-translate-x-full');
            filterOverlay.classList.remove('opacity-100', 'pointer-events-auto');
            filterOverlay.classList.add('opacity-0', 'pointer-events-none');
            ltdhUnlockDrawerScroll();
            if (mobileFilterBtn) mobileFilterBtn.setAttribute('aria-expanded', 'false');
        }

        if (filterOverlay && !filterOverlay._scrollBlocked) {
            filterOverlay._scrollBlocked = true;
            filterOverlay.addEventListener('touchmove', (e) => {
                if (e.cancelable) e.preventDefault();
            }, { passive: false });
            filterOverlay.addEventListener('wheel', (e) => {
                if (e.cancelable) e.preventDefault();
            }, { passive: false });
        }

        relocateMajorDrawerForm();
        window.addEventListener('resize', relocateMajorDrawerForm);

        if (mobileFilterBtn && !mobileFilterBtn._boundMajorDrawer) {
            mobileFilterBtn._boundMajorDrawer = true;
            mobileFilterBtn.addEventListener('click', openMajorFilterDrawer);
        }
        if (drawerCloseBtn && !drawerCloseBtn._boundMajorDrawer) {
            drawerCloseBtn._boundMajorDrawer = true;
            drawerCloseBtn.addEventListener('click', closeMajorFilterDrawer);
        }
        if (filterOverlay && !filterOverlay._boundMajorDrawer) {
            filterOverlay._boundMajorDrawer = true;
            filterOverlay.addEventListener('click', closeMajorFilterDrawer);
        }
        if (drawerApplyBtn && !drawerApplyBtn._boundMajorDrawer) {
            drawerApplyBtn._boundMajorDrawer = true;
            drawerApplyBtn.addEventListener('click', () => {
                triggerFilter({ paged: 1, pushUrl: true });
                closeMajorFilterDrawer();
            });
        }
        const drawerResetBtn = document.getElementById('ltdh-major-drawer-reset-btn');
        if (drawerResetBtn && !drawerResetBtn._boundMajorReset) {
            drawerResetBtn._boundMajorReset = true;
            drawerResetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (catSelect) catSelect.value = '';
                if (sortSelect) sortSelect.value = '';
                if (limitSelect) limitSelect.value = '12';
                triggerFilter({ paged: 1, pushUrl: true });
                closeMajorFilterDrawer();
            });
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMajorFilterDrawer();
        });

        window.ltdhCloseMajorFilterDrawer = closeMajorFilterDrawer;
        window.ltdhOpenMajorFilterDrawer = openMajorFilterDrawer;

        const searchInput = filterForm.querySelector('input[name="s"]');
        const catSelect = filterForm.querySelector('select[name="nhom_nganh"]');
        const sortSelect = filterForm.querySelector('select[name="sort"]');
        const limitSelect = filterForm.querySelector('select[name="limit"]');

        let searchTimeout = null;
        let activeAbortController = null;
        const rootArchiveUrl = '/nhom-nganh/';

        // Sync catSelect with path if loaded directly from /nhom-nganh/{slug}/
        if (catSelect && !catSelect.value) {
            const initialPathMatch = window.location.pathname.match(/^\/nhom-nganh\/([^\/]+)(?:\/page\/\d+)?\/?$/i);
            if (initialPathMatch && initialPathMatch[1] && initialPathMatch[1] !== 'page') {
                catSelect.value = decodeURIComponent(initialPathMatch[1]);
            }
        }

        function updatePillStyles(activeCatSlug) {
            if (!pillsContainer) return;
            const pills = pillsContainer.querySelectorAll('.js-ltdh-major-pill');
            pills.forEach(pill => {
                const cat = pill.getAttribute('data-cat') || '';
                const badge = pill.querySelector('.pill-badge');
                if (cat === (activeCatSlug || '')) {
                    pill.classList.add('is-active', 'bg-brand-primary', 'text-white', 'shadow-xs');
                    pill.classList.remove('bg-slate-100', 'hover:bg-slate-200', 'text-slate-700');
                    if (badge) {
                        badge.classList.add('bg-white/20', 'text-white');
                        badge.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200/60');
                    }
                } else {
                    pill.classList.remove('is-active', 'bg-brand-primary', 'text-white', 'shadow-xs');
                    pill.classList.add('bg-slate-100', 'hover:bg-slate-200', 'text-slate-700');
                    if (badge) {
                        badge.classList.remove('bg-white/20', 'text-white');
                        badge.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200/60');
                    }
                }
            });
        }

        function buildCurrentUrl(paged = 1) {
            const params = new URLSearchParams();
            const sVal = (searchInput ? searchInput.value : '').trim();
            const catVal = catSelect ? catSelect.value : '';
            const sortVal = sortSelect ? sortSelect.value : '';
            const limitVal = limitSelect ? limitSelect.value : '';

            let path = rootArchiveUrl;
            if (catVal) {
                path = `${rootArchiveUrl}${encodeURIComponent(catVal)}/`;
            }

            if (sVal) params.set('s', sVal);
            if (sortVal) params.set('sort', sortVal);
            if (limitVal && limitVal !== '12') params.set('limit', limitVal);
            if (paged > 1) params.set('paged', paged);

            const queryString = params.toString();
            return queryString ? `${path}?${queryString}` : path;
        }

        function triggerFilter(options = {}) {
            const paged = options.paged || 1;
            const pushUrl = options.pushUrl !== false;
            const isTyping = options.isTyping || false;
            const scrollToTop = options.scrollToTop || false;

            const targetUrl = buildCurrentUrl(paged);

            if (pushUrl) {
                if (isTyping) {
                    window.history.replaceState({ ltdhMajorFilter: true, paged: paged }, '', targetUrl);
                } else {
                    window.history.pushState({ ltdhMajorFilter: true, paged: paged }, '', targetUrl);
                }
            }

            const currentCat = catSelect ? catSelect.value : '';
            updatePillStyles(currentCat);

            if (activeAbortController) {
                activeAbortController.abort();
            }
            activeAbortController = new AbortController();

            // Display loading overlay with spinner
            showLoadingOverlay(container, 'Đang tìm ngành học...');

            const formData = new FormData();
            formData.append('action', 'ltdh_filter_majors');
            formData.append('paged', paged);
            if (catSelect && catSelect.value) formData.append('nhom_nganh', catSelect.value);
            if (searchInput && searchInput.value.trim()) formData.append('s', searchInput.value.trim());
            if (sortSelect && sortSelect.value) formData.append('sort', sortSelect.value);
            if (limitSelect && limitSelect.value) formData.append('limit', limitSelect.value);

            const ajaxUrl = (typeof ltdh_ajax !== 'undefined' && ltdh_ajax.ajax_url)
                ? ltdh_ajax.ajax_url
                : '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                signal: activeAbortController.signal
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const data = res.data;

                    container.innerHTML = data.cards_html;

                    if (headingTitle && data.heading_title) {
                        headingTitle.textContent = data.heading_title;
                    }
                    if (countDisplay && typeof data.found_posts !== 'undefined') {
                        countDisplay.textContent = data.found_posts;
                    }
                    const mobileCountDisplay = document.getElementById('catalog-major-count-display-mobile');
                    if (mobileCountDisplay && typeof data.found_posts !== 'undefined') {
                        mobileCountDisplay.textContent = data.found_posts;
                    }
                    document.querySelectorAll('.js-major-filter-count-badge').forEach(b => {
                        b.textContent = `(${data.found_posts})`;
                    });
                    if (mobileApplyBtnCount && typeof data.found_posts !== 'undefined') {
                        mobileApplyBtnCount.textContent = data.found_posts;
                    }

                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination_html || '';
                    }

                    if (mobileChipsWrapper) {
                        mobileChipsWrapper.innerHTML = data.chips_html || '';
                    }

                    const resetBtnHtml = `<a href="${rootArchiveUrl}" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50/80 hover:bg-rose-100/80 border border-rose-100 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]"><span>✕</span> <span>Xóa tất cả bộ lọc</span></a>`;
                    const mobileResetHtml = `<a href="${rootArchiveUrl}" class="js-ltdh-reset-filter inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 min-h-[38px] transition-colors" title="Xóa tất cả bộ lọc"><span>✕ Xóa lọc</span></a>`;

                    if (desktopResetWrapper) {
                        desktopResetWrapper.innerHTML = data.has_active_filters ? resetBtnHtml : '';
                    }
                    if (mobileResetWrapper) {
                        mobileResetWrapper.innerHTML = data.has_active_filters ? mobileResetHtml : '';
                    }

                    if (mobileFilterBadge) {
                        mobileFilterBadge.innerHTML = (data.active_filter_count > 0)
                            ? `<span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[18px]">${data.active_filter_count}</span>`
                            : '';
                    }
                    if (mobileFilterBtn) {
                        if (data.has_active_filters) {
                            mobileFilterBtn.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-bold text-xs transition-all duration-200 min-h-[38px] cursor-pointer shadow-2xs bg-blue-50 border-blue-200 text-[#00308b]';
                        } else {
                            mobileFilterBtn.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-bold text-xs transition-all duration-200 min-h-[38px] cursor-pointer shadow-2xs bg-white hover:bg-slate-50 border-slate-200 text-slate-700';
                        }
                    }

                    if (scrollToTop) {
                        const targetEl = document.getElementById('major-filter-body') || container;
                        const topOffset = targetEl.getBoundingClientRect().top + window.pageYOffset - 100;
                        window.scrollTo({ top: topOffset, behavior: 'smooth' });
                    }
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Error filtering majors:', err);
                }
            })
            .finally(() => {
                hideLoadingOverlay(container);
            });
        }

        // 1. Form submit event
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerFilter({ paged: 1, pushUrl: true });
            closeMajorFilterDrawer();
        });

        // 2. Dropdown changes
        [catSelect, sortSelect, limitSelect].forEach(select => {
            if (!select) return;
            select.removeAttribute('onchange');
            select.addEventListener('change', () => {
                triggerFilter({ paged: 1, pushUrl: true });
            });
        });

        // 3. Search input (debounced)
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    triggerFilter({ paged: 1, pushUrl: true, isTyping: true });
                }, 350);
            });
        }

        // 4. Category Pills Click
        if (pillsContainer) {
            pillsContainer.addEventListener('click', (e) => {
                const pill = e.target.closest('.js-ltdh-major-pill');
                if (!pill) return;
                e.preventDefault();

                const catSlug = pill.getAttribute('data-cat') || '';
                if (catSelect) {
                    catSelect.value = catSlug;
                }
                triggerFilter({ paged: 1, pushUrl: true });
            });
        }

        // 5. Pagination Click (Delegated)
        document.addEventListener('click', (e) => {
            const pageLink = e.target.closest('.theme-pagination a.page-numbers');
            if (!pageLink || (!container.contains(pageLink) && !paginationContainer.contains(pageLink))) return;
            e.preventDefault();

            const href = pageLink.getAttribute('href') || '';
            let targetPage = 1;

            const pagedMatch = href.match(/[?&]paged=(\d+)/);
            if (pagedMatch) {
                targetPage = parseInt(pagedMatch[1], 10);
            } else {
                const slashMatch = href.match(/\/page\/(\d+)\/?/);
                if (slashMatch) {
                    targetPage = parseInt(slashMatch[1], 10);
                } else {
                    const textNum = parseInt(pageLink.textContent.trim(), 10);
                    if (!isNaN(textNum)) {
                        targetPage = textNum;
                    }
                }
            }

            triggerFilter({ paged: targetPage, pushUrl: true, scrollToTop: true });
        });

        // 6. Reset Filters (Delegated)
        document.addEventListener('click', (e) => {
            if (!container || !document.body.contains(container)) return;
            const resetBtn = e.target.closest('#major-desktop-reset .js-ltdh-reset-filter, #major-mobile-reset .js-ltdh-reset-filter, #ltdh-major-drawer-reset-btn, .js-ltdh-reset-filter');
            if (!resetBtn) return;
            if (resetBtn.closest('#ltdh-filter-drawer, #catalog-desktop-filter-wrapper, #program-results-wrapper, #catalog-mobile-chips, #catalog-mobile-reset, #catalog-desktop-reset')) return;
            e.preventDefault();

            if (searchInput) searchInput.value = '';
            if (catSelect) catSelect.value = '';
            if (sortSelect) sortSelect.value = '';
            if (limitSelect) limitSelect.value = '12';

            triggerFilter({ paged: 1, pushUrl: true });
            closeMajorFilterDrawer();
        });

        // 7. Chip Remove Click (Delegated)
        document.addEventListener('click', (e) => {
            if (!container || !document.body.contains(container)) return;
            const chip = e.target.closest('#major-mobile-chips .js-ltdh-chip-remove, .js-ltdh-chip-remove');
            if (!chip) return;
            if (chip.closest('#catalog-mobile-chips')) return;
            e.preventDefault();

            const key = chip.getAttribute('data-filter-key');
            if (key === 's' && searchInput) searchInput.value = '';
            if (key === 'nhom_nganh' && catSelect) catSelect.value = '';
            if (key === 'sort' && sortSelect) sortSelect.value = '';
            if (key === 'limit' && limitSelect) limitSelect.value = '12';

            triggerFilter({ paged: 1, pushUrl: true });
        });

        // 8. Popstate (Browser Back / Forward)
        window.addEventListener('popstate', () => {
            const params = new URLSearchParams(window.location.search);
            let catVal = params.get('nhom_nganh') || '';
            const pathMatch = window.location.pathname.match(/^\/nhom-nganh\/([^\/]+)(?:\/page\/\d+)?\/?$/i);
            if (pathMatch && pathMatch[1] && pathMatch[1] !== 'page') {
                catVal = decodeURIComponent(pathMatch[1]);
            }

            const sVal = params.get('s') || '';
            const sortVal = params.get('sort') || '';
            const limitVal = params.get('limit') || '12';
            const pagedVal = parseInt(params.get('paged') || '1', 10);

            if (catSelect) catSelect.value = catVal;
            if (searchInput) searchInput.value = sVal;
            if (sortSelect) sortSelect.value = sortVal;
            if (limitSelect) limitSelect.value = limitVal;

            updatePillStyles(catVal);
            triggerFilter({ paged: pagedVal, pushUrl: false });
        });
    }

    initMajorArchiveFilter();

    // ----------------------------------------------------
    // 5. Scroll-triggered KPI Number Counter Animation
    // ----------------------------------------------------
    function initStatsCounter() {
        const grid = document.getElementById('ltdh-kpi-metrics-grid');
        const counterElements = grid ? grid.querySelectorAll('.ltdh-counter') : document.querySelectorAll('.ltdh-counter');
        if (!counterElements || !counterElements.length) return;

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        function animateCounter(el) {
            if (el.dataset.animated === 'true') return;
            el.dataset.animated = 'true';

            const rawTarget = el.getAttribute('data-target') || el.textContent.trim();
            const match = rawTarget.match(/^([^\d]*)([\d,\.]+)(.*)$/);
            if (!match) return;

            const prefix = match[1] || '';
            const numStr = match[2];
            const suffix = match[3] || '';

            const hasDotSeparator = numStr.includes('.') && !numStr.includes(',');
            const hasCommaSeparator = numStr.includes(',') && !numStr.includes('.');
            const cleanNum = numStr.replace(/[\.,]/g, '');
            const targetNum = parseInt(cleanNum, 10);
            if (isNaN(targetNum)) return;

            const duration = targetNum <= 10 ? 900 : 1400;
            const startTime = performance.now();

            function formatNumber(val) {
                if (hasDotSeparator) {
                    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
                if (hasCommaSeparator) {
                    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }
                return val.toString();
            }

            function easeOutExpo(t) {
                return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
            }
            function easeOutQuad(t) {
                return 1 - (1 - t) * (1 - t);
            }

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const ease = targetNum <= 10 ? easeOutQuad(progress) : easeOutExpo(progress);

                const currentVal = Math.round(ease * targetNum);
                el.textContent = prefix + formatNumber(currentVal) + suffix;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    el.textContent = rawTarget;
                }
            }

            el.textContent = prefix + '0' + suffix;
            requestAnimationFrame(update);
        }

        function triggerAll() {
            counterElements.forEach(el => animateCounter(el));
        }

        const observeTarget = grid || counterElements[0];
        if ('IntersectionObserver' in window && observeTarget) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        obs.unobserve(entry.target);
                        triggerAll();
                    }
                });
            }, {
                threshold: 0.15,
                rootMargin: '0px 0px -40px 0px'
            });
            observer.observe(observeTarget);
        } else {
            triggerAll();
        }
    }

    initStatsCounter();

    // ----------------------------------------------------
    // Post Sharing & Clipboard Module
    // ----------------------------------------------------
    function initPostSharing() {
        // Unhide native share button if supported by browser/device
        const nativeShareBtn = document.getElementById('ltdh-native-share-btn');
        if (nativeShareBtn && typeof navigator.share === 'function') {
            nativeShareBtn.classList.remove('hidden');
            if (!nativeShareBtn._bound) {
                nativeShareBtn._bound = true;
                nativeShareBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const title = nativeShareBtn.getAttribute('data-title') || document.title;
                    const url = nativeShareBtn.getAttribute('data-url') || window.location.href;
                    navigator.share({
                        title: title,
                        text: title,
                        url: url
                    }).catch(() => {
                        // User cancelled, safe to ignore
                    });
                });
            }
        }

        // Support native share for program sharing and class-based buttons
        document.querySelectorAll('#ltdh-native-program-share-btn, .ltdh-native-share-btn').forEach(btn => {
            if (typeof navigator.share === 'function') {
                btn.classList.remove('hidden');
                if (!btn._bound) {
                    btn._bound = true;
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const title = btn.getAttribute('data-title') || document.title;
                        const url = btn.getAttribute('data-url') || window.location.href;
                        navigator.share({
                            title: title,
                            text: title,
                            url: url
                        }).catch(() => {});
                    });
                }
            }
        });

        // Delegate popup handler to social share buttons (posts and programs)
        document.querySelectorAll('.ltdh-post-share-section .ltdh-share-btn[data-share-type], .ltdh-program-share-section .ltdh-share-btn[data-share-type]').forEach(btn => {
            if (btn._boundPopup) return;
            btn._boundPopup = true;
            btn.addEventListener('click', (e) => {
                const type = btn.getAttribute('data-share-type') || 'Social';
                if (type.toLowerCase() === 'facebook') {
                    if (window.ltdhOpenFacebookShare) {
                        return window.ltdhOpenFacebookShare(e, btn);
                    }
                }
                const url = btn.getAttribute('href');
                if (url && url !== '#' && !url.startsWith('javascript:')) {
                    window.ltdhOpenSharePopup(e, url, type);
                }
            });
        });

        // Delegate copy post link button
        const copyBtn = document.getElementById('ltdh-copy-post-btn');
        if (copyBtn && !copyBtn._bound) {
            copyBtn._bound = true;
            copyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.ltdhCopyPostLink(copyBtn);
            });
        }

        // Delegate copy program link button
        document.querySelectorAll('#ltdh-copy-program-btn, .ltdh-copy-program-btn').forEach(progCopyBtn => {
            if (!progCopyBtn._bound) {
                progCopyBtn._bound = true;
                progCopyBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (window.ltdhCopyProgramLink) {
                        window.ltdhCopyProgramLink(progCopyBtn);
                    } else {
                        window.ltdhCopyPostLink(progCopyBtn);
                    }
                });
            }
        });

        // Delegate quick share buttons (Sticky Nav, Sidebar, Cards)
        document.querySelectorAll('.ltdh-quick-share-btn, .ltdh-program-sidebar-share-btn, .ltdh-card-share-btn').forEach(btn => {
            if (btn._bound) return;
            btn._bound = true;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const url = btn.getAttribute('data-url') || window.location.href;
                const title = btn.getAttribute('data-title') || document.title;
                if (typeof navigator.share === 'function') {
                    navigator.share({
                        title: title,
                        text: title,
                        url: url
                    }).catch(() => {});
                } else {
                    if (window.ltdhCopyProgramLink) {
                        window.ltdhCopyProgramLink(btn);
                    } else if (window.ltdhCopyPostLink) {
                        window.ltdhCopyPostLink(btn);
                    }
                }
            });
        });
    }

    initPostSharing();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ltdhInitTheme);
} else {
    ltdhInitTheme();
}

