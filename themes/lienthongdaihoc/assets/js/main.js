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
        menu.classList.remove('translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
        document.body.classList.add('overflow-hidden');
    }

    function closeMenu() {
        if (!menu || !overlay) return;
        menu.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0', 'pointer-events-none');
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
    // 2. Swiper Sliders Initialization (Hero & School Partners)
    // ----------------------------------------------------
    let swiperAttempts = 0;
    const initSwipers = () => {
        if (typeof Swiper === 'undefined') {
            if (++swiperAttempts < 100) {
                setTimeout(initSwipers, swiperAttempts > 20 ? 250 : 100);
            }
            return;
        }

        const heroSwiperEl = document.querySelector('.hero-swiper');
        if (heroSwiperEl) {
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

        const schoolSwiperEl = document.querySelector('.school-partner-swiper');
        if (schoolSwiperEl) {
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
        if (hotMajorsSwiperEl) {
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

    if ('requestIdleCallback' in window) {
        requestIdleCallback(initSwipers, { timeout: 2000 });
    } else {
        setTimeout(initSwipers, 300);
    }

    // ----------------------------------------------------
    // 3. Program Search & AJAX Filtering
    // ----------------------------------------------------
    // Clean empty GET form controls on submit to prevent dirty URLs like ?s=&truong=abc&nganh=&sort=
    document.querySelectorAll('form[method="GET"], form[method="get"]').forEach(form => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('input, select').forEach(input => {
                if (!input.value || input.value.trim() === '') {
                    input.disabled = true;
                }
            });
        });
    });

    const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"], form[action*="/hinh-thuc-dao-tao/"]');
    const container = document.getElementById('program-results-container');

    if (filterForm && container) {
        // Prevent form reload on submit
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerFilter();
        });

        // Trigger filter instantly on dropdown changes
        filterForm.querySelectorAll('select').forEach(select => {
            select.removeAttribute('onchange');
            select.addEventListener('change', triggerFilter, { passive: true });
        });

        // Debounce input typing in search field
        let searchTimeout;
        const searchInput = filterForm.querySelector('input[name="s"]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(triggerFilter, 300);
            }, { passive: true });
        }

        // Intercept Reset button clicks
        const resetBtn = document.querySelector('.js-ltdh-reset-filter');
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterForm.reset();
                filterForm.querySelectorAll('input').forEach(el => el.value = '');
                filterForm.querySelectorAll('select').forEach(el => el.value = '');
                window.location.href = resetBtn.href;
            });
        }

        function triggerFilter() {
            // Apply loading state
            container.style.opacity = '0.4';
            container.style.pointerEvents = 'none';

            // Gather values
            const formData = new FormData(filterForm);
            formData.append('action', 'ltdh_filter_programs');

            // Construct new URL parameters
            const queryParams = [];
            filterForm.querySelectorAll('input, select').forEach(el => {
                if (el.value) {
                    queryParams.push(`${encodeURIComponent(el.name)}=${encodeURIComponent(el.value)}`);
                }
            });
            const newUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname}` + (queryParams.length ? `?${queryParams.join('&')}` : '');
            
            // Push State to update browser URL bar dynamically
            window.history.pushState({ path: newUrl }, '', newUrl);

            // Fetch AJAX
            if (typeof ltdh_ajax !== 'undefined' && ltdh_ajax.ajax_url) {
                fetch(ltdh_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        container.innerHTML = res.data.html;
                        if (window.ltdhCompare && typeof window.ltdhCompare.syncButtonStates === 'function') {
                            window.ltdhCompare.syncButtonStates();
                        }
                    }
                })
                .catch(err => console.error('Filter error:', err))
                .finally(() => {
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                });
            }
        }
    }

    // Single Major Page Filter (Training Type Pills & School Select Dropdown)
    function initMajorPageFilter() {
        const wrapper = document.getElementById('ltdh-major-programs-wrapper');
        if (!wrapper) return;

        const majorId = wrapper.dataset.majorId;
        const listContainer = document.getElementById('ltdh-major-programs-list');
        const pills = document.querySelectorAll('.ltdh-major-he-pill');

        if (!listContainer || !majorId) return;

        function doFilter(pushUrl = true) {
            listContainer.style.opacity = '0.4';
            listContainer.style.pointerEvents = 'none';

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
                listContainer.style.opacity = '1';
                listContainer.style.pointerEvents = 'auto';
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
        const mobileFilterBtn = document.getElementById('ltdh-mobile-filter-btn');
        const mobileFilterBody = document.getElementById('major-filter-body');
        const mobileFilterChevron = document.getElementById('ltdh-mobile-filter-chevron');
        const mobileFilterBtnText = document.getElementById('ltdh-mobile-filter-btn-text');
        const mobileCloseBtn = document.getElementById('ltdh-mobile-filter-close');

        const searchInput = filterForm.querySelector('input[name="s"]');
        const catSelect = filterForm.querySelector('select[name="nhom_nganh"]');
        const sortSelect = filterForm.querySelector('select[name="sort"]');
        const limitSelect = filterForm.querySelector('select[name="limit"]');

        let searchTimeout = null;
        let activeAbortController = null;
        const basePath = window.location.pathname.replace(/\/page\/\d+\/?$/, '/');

        // Mobile Filter Accordion Toggle
        if (mobileFilterBtn && mobileFilterBody && !mobileFilterBtn._bound) {
            mobileFilterBtn._bound = true;
            function toggleMobileFilter() {
                const isHidden = mobileFilterBody.classList.contains('hidden');
                if (isHidden) {
                    mobileFilterBody.classList.remove('hidden');
                    mobileFilterBtn.setAttribute('aria-expanded', 'true');
                    if (mobileFilterChevron) mobileFilterChevron.classList.add('rotate-180');
                    if (mobileFilterBtnText) mobileFilterBtnText.textContent = 'Thu gọn bộ lọc';
                } else {
                    mobileFilterBody.classList.add('hidden');
                    mobileFilterBtn.setAttribute('aria-expanded', 'false');
                    if (mobileFilterChevron) mobileFilterChevron.classList.remove('rotate-180');
                    if (mobileFilterBtnText) mobileFilterBtnText.textContent = 'Bộ lọc & Tìm kiếm';
                }
            }
            mobileFilterBtn.addEventListener('click', toggleMobileFilter);
            if (mobileCloseBtn) {
                mobileCloseBtn.addEventListener('click', toggleMobileFilter);
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

            if (catVal) params.set('nhom_nganh', catVal);
            if (sVal) params.set('s', sVal);
            if (sortVal) params.set('sort', sortVal);
            if (limitVal && limitVal !== '12') params.set('limit', limitVal);
            if (paged > 1) params.set('paged', paged);

            const queryString = params.toString();
            return queryString ? `${basePath}?${queryString}` : basePath;
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

            container.style.opacity = '0.35';
            container.style.pointerEvents = 'none';

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
                    if (mobileApplyBtnCount && typeof data.found_posts !== 'undefined') {
                        mobileApplyBtnCount.textContent = data.found_posts;
                    }

                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination_html || '';
                    }

                    if (mobileChipsWrapper) {
                        mobileChipsWrapper.innerHTML = data.chips_html || '';
                    }

                    const resetBtnHtml = `<a href="${basePath}" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]"><span>✕</span> <span>Xóa tất cả bộ lọc</span></a>`;
                    const mobileResetHtml = `<a href="${basePath}" class="js-ltdh-reset-filter flex items-center justify-center gap-1 px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 min-h-[44px] transition-colors shrink-0" title="Xóa tất cả bộ lọc"><span>✕ Xóa lọc</span></a>`;

                    if (desktopResetWrapper) {
                        desktopResetWrapper.innerHTML = data.has_active_filters ? resetBtnHtml : '';
                    }
                    if (mobileResetWrapper) {
                        mobileResetWrapper.innerHTML = data.has_active_filters ? mobileResetHtml : '';
                    }

                    if (mobileFilterBadge) {
                        mobileFilterBadge.innerHTML = (data.active_filter_count > 0)
                            ? `<span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[11px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[20px] shadow-2xs">${data.active_filter_count}</span>`
                            : '';
                    }
                    if (mobileFilterBtn) {
                        if (data.has_active_filters) {
                            mobileFilterBtn.className = 'flex-1 flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border font-bold text-xs sm:text-sm transition-all duration-200 min-h-[44px] cursor-pointer shadow-2xs bg-blue-50/90 border-blue-200/90 text-[#00308b]';
                        } else {
                            mobileFilterBtn.className = 'flex-1 flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border font-bold text-xs sm:text-sm transition-all duration-200 min-h-[44px] cursor-pointer shadow-2xs bg-slate-50 hover:bg-slate-100 border-slate-200/80 text-slate-700';
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
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
        }

        // 1. Form submit event
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerFilter({ paged: 1, pushUrl: true });
            if (mobileFilterBody && !mobileFilterBody.classList.contains('hidden') && window.innerWidth < 768) {
                mobileFilterBody.classList.add('hidden');
                if (mobileFilterBtn) mobileFilterBtn.setAttribute('aria-expanded', 'false');
                if (mobileFilterChevron) mobileFilterChevron.classList.remove('rotate-180');
                if (mobileFilterBtnText) mobileFilterBtnText.textContent = 'Bộ lọc & Tìm kiếm';
            }
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
            const resetBtn = e.target.closest('.js-ltdh-reset-filter');
            if (!resetBtn) return;
            e.preventDefault();

            if (searchInput) searchInput.value = '';
            if (catSelect) catSelect.value = '';
            if (sortSelect) sortSelect.value = '';
            if (limitSelect) limitSelect.value = '12';

            triggerFilter({ paged: 1, pushUrl: true });
        });

        // 7. Chip Remove Click (Delegated)
        document.addEventListener('click', (e) => {
            const chip = e.target.closest('.js-ltdh-chip-remove');
            if (!chip) return;
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
            const catVal = params.get('nhom_nganh') || '';
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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ltdhInitTheme);
} else {
    ltdhInitTheme();
}

