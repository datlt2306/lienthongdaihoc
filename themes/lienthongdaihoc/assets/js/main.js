/**
 * Theme Javascript Logic
 * lienthongdaihoc.com
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('lienthongdaihoc.com Theme Initialized.');

    const filterForm = document.querySelector('form[action*="/chuong-trinh/"]');
    const container = document.getElementById('program-results-container');

    if (filterForm && container) {
        // Prevent form reload on submit
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerFilter();
        });

        // Trigger filter instantly on dropdown changes
        filterForm.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', triggerFilter);
        });

        // Debounce input typing in search field
        let searchTimeout;
        const searchInput = filterForm.querySelector('input[name="s"]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(triggerFilter, 300);
            });
        }

        // Intercept Reset button clicks
        const resetBtn = filterForm.querySelector('a[href*="/chuong-trinh/"]');
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
            fetch(ltdh_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    container.innerHTML = res.data.html;
                }
            })
            .catch(err => console.error('Filter error:', err))
            .finally(() => {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
        }
    }
});
