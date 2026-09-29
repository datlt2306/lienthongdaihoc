## 2026-09-25T05:03:18Z

Received dispatch from parent orchestrator:
Role: Frontend, SEO & Schema Surveyor (explorer_survey_3)
Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3
Target project root: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
Authoritative original request: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md

Task Objective:
Map the asset management, SEO on-page, Schema markup, and frontend integrity:
1. Asset Management (CSS / JS):
   - Enumerate all `wp_enqueue_script` and `wp_enqueue_style` calls.
   - Check for hardcoded script/style tags in template files.
   - Check asset versioning (`wp_get_theme()->get('Version')` vs hardcoded vs filemtime).
   - Check asset dependencies, duplicates, render-blocking scripts, and async/defer usage.
2. SEO On-Page & Semantic HTML:
   - Check heading hierarchy (H1 through H6) across all page templates, archive templates, single templates.
   - Check for missing alt attributes on images (`<img>` tags, `wp_get_attachment_image`).
   - Check semantic HTML5 usage (`<header>`, `<nav>`, `<main>`, `<article>`, `<section>`, `<footer>`).
   - Check meta tags, OpenGraph, title tag support, and canonical links.
3. Schema.org Structured Data:
   - Identify all JSON-LD or microdata generation in the theme.
   - Verify compliance and completeness for educational themes: EducationalOrganization, Course, Program, BreadcrumbList, FAQPage, Article, etc.
   - Identify syntax errors or missing required Schema properties.
4. Frontend & Client-side Script Integrity:
   - Analyze all JavaScript files in the theme for syntax errors, deprecations, undefined global references, or console error risks.
   - Evaluate responsive design structure (CSS breakpoints, viewport meta, container overflows).
