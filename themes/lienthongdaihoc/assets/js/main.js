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
    // 2. Hero Swiper Slider Initialization
    // ----------------------------------------------------
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

    // ----------------------------------------------------
    // 3. Program Search & AJAX Filtering
    // ----------------------------------------------------
    const filterForm = document.querySelector('form[action*="/chuong-trinh/"], form[action*="/he-dao-tao/"]');
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
        const resetBtn = filterForm.querySelector('a[href*="/chuong-trinh/"], a[href*="/he-dao-tao/"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterForm.reset();
                filterForm.querySelectorAll('input').forEach(el => el.value = '');
                filterForm.querySelectorAll('select').forEach(el => el.value = '');
                triggerFilter();
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
});
