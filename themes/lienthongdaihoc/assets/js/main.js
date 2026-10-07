/**
 * Theme Javascript Logic
 * lienthongdaihoc.com
 *
 * Optimized for Google Core Web Vitals (INP < 200ms, non-blocking execution)
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Mobile Menu Offcanvas Drawer
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

    if (toggleBtn && menu && overlay) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openMenu();
        }, { passive: false });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeMenu, { passive: true });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMenu, { passive: true });
    }

    const mobileLinks = document.querySelectorAll('#mobile-menu a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', closeMenu, { passive: true });
    });

    // ----------------------------------------------------
    // 2. Hero Swiper Slider Initialization (Idle Hydration)
    // ----------------------------------------------------
    const initHeroSwiper = () => {
        const heroSwiperEl = document.querySelector('.hero-swiper');
        if (heroSwiperEl && typeof Swiper !== 'undefined') {
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
    };

    if ('requestIdleCallback' in window) {
        requestIdleCallback(initHeroSwiper, { timeout: 2000 });
    } else {
        setTimeout(initHeroSwiper, 300);
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
        const schoolSelect = document.getElementById('ltdh-major-school-select');
        const countBadge = document.getElementById('ltdh-major-programs-count');

        if (!listContainer || !majorId) return;

        function doFilter(pushUrl = true) {
            listContainer.style.opacity = '0.4';
            listContainer.style.pointerEvents = 'none';

            const activePill = document.querySelector('.ltdh-major-he-pill.is-active');
            const selectedHe = activePill ? (activePill.dataset.he || '') : '';
            const selectedSchool = schoolSelect ? schoolSelect.value : '';

            if (pushUrl) {
                const urlParams = new URLSearchParams(window.location.search);
                if (selectedHe) {
                    urlParams.set('he', selectedHe);
                } else {
                    urlParams.delete('he');
                }

                if (selectedSchool) {
                    urlParams.set('truong', selectedSchool);
                    urlParams.set('from_school', selectedSchool);
                } else {
                    urlParams.delete('truong');
                    urlParams.delete('from_school');
                }

                const searchStr = urlParams.toString();
                const newUrl = window.location.pathname + (searchStr ? '?' + searchStr : '');
                window.history.pushState({ path: newUrl }, '', newUrl);
            }

            const formData = new FormData();
            formData.append('action', 'ltdh_filter_major_programs');
            formData.append('major_id', majorId);
            formData.append('he', selectedHe);
            formData.append('school', selectedSchool);

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
                    if (countBadge && typeof data.data.count !== 'undefined') {
                        countBadge.textContent = selectedSchool ? (data.data.count + ' chương trình') : (data.data.count + ' trường tuyển sinh');
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

        if (schoolSelect) {
            schoolSelect.addEventListener('change', function() {
                doFilter(true);
            });
        }

        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentHe = urlParams.get('he') || '';
            const currentSchool = urlParams.get('truong') || urlParams.get('from_school') || urlParams.get('school') || '';

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

            if (schoolSelect) {
                schoolSelect.value = currentSchool;
            }

            doFilter(false);
        });
    }

    initMajorPageFilter();
});

