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

