# Dispatch for Explorer Survey 1

## 2026-09-25T05:03:18Z

Role: Codebase & PHP Inventory Surveyor
Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1
Target project root: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc

TASK OBJECTIVE:
Map the complete PHP codebase and theme architecture:
1. Enumerate 100% of PHP files in the theme (root, inc/, template-parts/, templates/, etc.). Get line count, file size, purpose.
2. Run syntax verification (`php -l`) on every single PHP file in the theme. Document any files with syntax errors, deprecation warnings, or lint failures.
3. Map the theme architecture:
   - Entry points (`style.css`, `functions.php`, `header.php`, `footer.php`, `index.php`)
   - Include structure (`inc/` files loaded and loading order)
   - Template hierarchy (archive, single, page, search, 404, front-page, taxonomy)
   - Hook and filter architecture (actions registered, filters registered, custom hooks created)
   - Custom post types, taxonomies, and theme supports
4. Identify any immediate PHP 8.1 - 8.3 compatibility issues, type mismatch risks, null safety concerns, or undefined array key patterns in the file structure.
