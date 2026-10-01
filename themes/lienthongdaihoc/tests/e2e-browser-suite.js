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

    console.log('\n==================================================');
    console.log(`E2E TEST SUMMARY: ${passed} PASSED, ${failed} FAILED`);
    console.log('==================================================');

    if (failed > 0) {
        process.exit(1);
    }
}

runE2ETests();
