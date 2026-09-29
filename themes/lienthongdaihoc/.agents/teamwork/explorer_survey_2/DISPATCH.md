# Dispatch for Explorer Survey 2
Ready for dispatch.

## 2026-09-25T05:03:18Z
You are teamwork_preview_explorer_survey_2 (Role: Security & DB Query Surveyor).
Your working directory is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_2
The target project root is: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
The authoritative original request is recorded at: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md

TASK OBJECTIVE:
Map the security landscape and database query patterns across the theme:
1. Security & Data Control:
   - Find all SQL queries and `$wpdb` calls across the theme. Check for SQL Injection risks and missing `$wpdb->prepare()`.
   - Find all user input entry points (`$_POST`, `$_GET`, `$_REQUEST`, `$_SERVER`, `$_COOKIE`). Check for missing sanitization (`sanitize_text_field`, `absint`, `esc_url_raw`, etc.).
   - Find all output rendering points and check for missing escaping (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`, `esc_textarea`). Check for XSS vulnerabilities.
   - Find all AJAX handlers (`wp_ajax_` and `wp_ajax_nopriv_`) and form submissions. Check for CSRF protection (`check_ajax_referer`, `wp_verify_nonce`).
   - Check authorization checks (`current_user_can`) on privileged endpoints.
   - Check direct file access guards (`defined('ABSPATH') || exit;`) across all PHP files.
2. Performance & DB Query Optimization:
   - Identify all `WP_Query`, `get_posts()`, and custom query loops.
   - Detect potential N+1 query patterns (e.g. queries or `get_post_meta` inside loops).
   - Detect queries without pagination limits or dangerous `posts_per_page => -1`.
   - Check for caching / Transients API usage for heavy computations or external requests.
