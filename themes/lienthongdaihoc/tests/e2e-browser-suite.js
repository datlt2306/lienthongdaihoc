/**
 * End-to-End (E2E) Browser & Integration Test Suite
 * lienthongdaihoc.com Theme
 *
 * Automated verification for:
 * 1. Major URL Rewrites (/nganh-cong-nghe-thong-tin/) & 301 Redirects
 * 2. School URL Rewrites (/truong-dai-hoc-giao-thong-van-tai/) & 301 Redirects
 * 3. Rank Math Canonical URLs & XML Sitemaps
 * 4. Header Button Shape Unification (rounded-lg)
 * 5. Prose Table Styling (white header text & single-line first column)
 * 6. LocalStorage Toggle State & Global Smooth Scroll CSS
 */

const BASE_URL = 'http://localhost:10028';

async function runE2ETests() {
    console.log('==================================================');
    console.log('STARTING E2E BROWSER & INTEGRATION TEST SUITE');
    console.log('==================================================\n');

    let passed = 0;
    let failed = 0;

    function assert(condition, message) {
        if (condition) {
            console.log(`✅ PASSED: ${message}`);
            passed++;
        } else {
            console.error(`❌ FAILED: ${message}`);
            failed++;
        }
    }

    // ----------------------------------------------------
    // TEST 1: Major Flat URL Resolution (/nganh-cong-nghe-thong-tin/)
    // ----------------------------------------------------
    console.log('--- TEST 1: Major Flat URL Resolution ---');
    try {
        const url = `${BASE_URL}/nganh-cong-nghe-thong-tin/`;
        const res = await fetch(url);
        assert(res.status === 200, `GET ${url} returns HTTP 200 OK (Got ${res.status})`);
        
        const html = await res.text();
        assert(html.includes('Công nghệ thông tin') || html.includes('CNTT'), 'Page contains Major Title/Content');
        assert(html.includes('rel="canonical"') && html.includes('/nganh-cong-nghe-thong-tin/'), 'Rank Math Canonical URL matches /nganh-cong-nghe-thong-tin/');
    } catch (err) {
        assert(false, `Major Flat URL test error: ${err.message}`);
    }

    // ----------------------------------------------------
    // TEST 2: Legacy Major URL 301 Redirect (/nganh-hoc/cong-nghe-thong-tin/)
    // ----------------------------------------------------
    console.log('\n--- TEST 2: Legacy Major URL 301 Redirect ---');
    try {
        const url = `${BASE_URL}/nganh-hoc/cong-nghe-thong-tin/`;
        const res = await fetch(url, { redirect: 'manual' });
        assert(res.status === 301, `GET ${url} returns HTTP 301 Redirect (Got ${res.status})`);
        
        const location = res.headers.get('location');
        assert(location && location.includes('/nganh-cong-nghe-thong-tin/'), `Redirect location points to /nganh-cong-nghe-thong-tin/ (Got: ${location})`);
    } catch (err) {
        assert(false, `Major 301 Redirect test error: ${err.message}`);
    }

    // ----------------------------------------------------
    // TEST 3: School Legacy URL 301 Redirect (/truong-doi-tac/...)
    // ----------------------------------------------------
    console.log('\n--- TEST 3: School Legacy URL 301 Redirect ---');
    try {
        const url = `${BASE_URL}/truong-doi-tac/truong-dai-hoc-giao-thong-van-tai/`;
        const res = await fetch(url, { redirect: 'manual' });
        assert(res.status === 301, `GET ${url} returns HTTP 301 Redirect (Got ${res.status})`);
        
        const location = res.headers.get('location');
        assert(location && location.includes('/truong-dai-hoc-giao-thong-van-tai/'), `Redirect location points to /truong-dai-hoc-giao-thong-van-tai/ (Got: ${location})`);
    } catch (err) {
        assert(false, `School 301 Redirect test error: ${err.message}`);
    }

    // ----------------------------------------------------
    // TEST 4: Header Button Shape Unification (rounded-lg)
    // ----------------------------------------------------
    console.log('\n--- TEST 4: Header Button Shape Unification ---');
    try {
        const res = await fetch(`${BASE_URL}/`);
        const html = await res.text();
        assert(html.includes('TƯ VẤN NGAY'), 'Homepage contains TƯ VẤN NGAY button');
        assert(html.includes('rounded-lg') && html.includes('TƯ VẤN NGAY'), 'Button TƯ VẤN NGAY uses rounded-lg class');
    } catch (err) {
        assert(false, `Header button test error: ${err.message}`);
    }

    // ----------------------------------------------------
    // TEST 5: Table Formatting & White Text CSS Rules
    // ----------------------------------------------------
    console.log('\n--- TEST 5: Table Formatting & White Text CSS ---');
    try {
        const res = await fetch(`${BASE_URL}/wp-content/themes/lienthongdaihoc/assets/css/main.min.css`);
        const css = await res.text();
        assert(css.includes('color:#fff') || css.includes('color: #fff') || css.includes('color:inherit'), 'CSS contains table header white text override');
        assert(css.includes('white-space:nowrap') || css.includes('white-space: nowrap'), 'CSS contains table single-line first column override');
        assert(css.includes('scroll-behavior:smooth') || css.includes('scroll-behavior: smooth'), 'CSS contains html scroll-behavior: smooth');
    } catch (err) {
        assert(false, `Table CSS test error: ${err.message}`);
    }

    // ----------------------------------------------------
    // TEST 6: LocalStorage Code Verification
    // ----------------------------------------------------
    console.log('\n--- TEST 6: LocalStorage Code Verification ---');
    try {
        const res = await fetch(`${BASE_URL}/nganh-cong-nghe-thong-tin/`);
        const html = await res.text();
        assert(html.includes('localStorage.getItem(storageKey)') || html.includes('localStorage.setItem(storageKey'), 'Single Major page contains localStorage toggle state logic');
    } catch (err) {
        assert(false, `LocalStorage code test error: ${err.message}`);
    }

    // ----------------------------------------------------
    // TEST 7: Eligibility Checker Page Delivery & Asset Validation
    // ----------------------------------------------------
    console.log('\n--- TEST 7: Eligibility Checker Page Delivery & Assets ---');
    try {
        const res = await fetch(`${BASE_URL}/kiem-tra-dieu-kien/`);
        assert(res.status === 200, `GET ${BASE_URL}/kiem-tra-dieu-kien/ returns HTTP 200 OK`);

        const html = await res.text();
        assert(html.includes('id="elig-unified-form"'), 'Page contains #elig-unified-form');
        assert(html.includes('id="elig-unified-submit"'), 'Page contains unified submit button');
        assert(html.includes('id="elig-input-summary"') && html.includes('id="elig-summary-tags"'), 'Page contains input summary & tags container');
        assert(html.includes('assets/css/eligibility.css?ver='), 'Styles eligibility.css enqueued with version parameter');
        assert(html.includes('assets/js/eligibility.js?ver='), 'Script eligibility.js enqueued with version parameter');

        // Extract localized config
        const match = html.match(/var ltdh_elig = (\{.*?\});/s);
        assert(match && match[1], 'Page contains localized ltdh_elig object');

        if (match && match[1]) {
            const config = JSON.parse(match[1]);
            assert(config.ajax_url && config.nonce, 'ltdh_elig contains ajax_url and nonce');

            // ----------------------------------------------------
            // TEST 8: Live AJAX Eligibility Check & UTC #1 Ranking
            // ----------------------------------------------------
            console.log('\n--- TEST 8: Live AJAX Eligibility Matching (UTC #1) ---');
            const params = new URLSearchParams();
            params.append('action', 'ltdh_elig_check');
            params.append('nonce', config.nonce);
            params.append('education', 'cao-dang');
            params.append('desired_major', '1677'); // CNTT
            params.append('training_type', 'tu-xa');
            params.append('campus', 'ha-noi');

            const ajaxRes = await fetch(config.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });

            assert(ajaxRes.status === 200, `POST ${config.ajax_url} returns HTTP 200 OK`);
            const ajaxJson = await ajaxRes.json();

            assert(ajaxJson.success === true, 'AJAX response success is true');
            assert(ajaxJson.data && ajaxJson.data.eligible === true, 'Data eligible is true');
            assert(ajaxJson.data.input && ajaxJson.data.input.desired_major_title === 'Công nghệ thông tin', 'Serialized input contains desired_major_title ("Công nghệ thông tin")');
            assert(ajaxJson.data.programs && ajaxJson.data.programs.length > 0, 'Returned programs array is not empty');

            const topProg = ajaxJson.data.programs[0];
            assert(topProg.program_id === 2014, `Top match program_id is 2014 (Got: ${topProg.program_id})`);
            assert(topProg.school && topProg.school.code === 'UTC', `Top match university code is 'UTC' (Got: ${topProg.school ? topProg.school.code : 'NONE'})`);
            assert(topProg.school && topProg.school.title === 'Trường Đại học Giao thông Vận tải', `Top match school title is 'Trường Đại học Giao thông Vận tải'`);
        }
    } catch (err) {
        assert(false, `Eligibility E2E test error: ${err.message}`);
    }

    console.log('\n==================================================');
    console.log(`E2E TEST SUMMARY: ${passed} PASSED, ${failed} FAILED`);
    console.log('==================================================');

    if (failed > 0) {
        process.exit(1);
    }
}

runE2ETests();
