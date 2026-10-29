# Empirical Challenger Report — Milestone 1: Git Branching & Bloat Sanitization

**Agent**: `challenger_m1_2` (teamwork_challenger / critic)  
**Parent Orchestrator ID**: `101e3526-caa5-49b4-98c0-c71df3477df4`  
**Working Directory**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m1_2/`  
**Verdict**: **APPROVE**  
**Date**: 2026-10-29  

---

## 1. Observation

### 1.1 Git Working Tree, Branch & Commit Integrity
- **Repository Root**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content`
- **Active Branch**: `git branch --show-current` outputs `refactor`.
- **Merge Base**: `git merge-base main refactor` outputs `bb1ced1ad0aa9ddbe948f05f686f85d9828badf6` (identical to `main` and `origin/main`).
- **Commit History**: Exactly 1 commit ahead of `main`:
  ```text
  e783aaa (HEAD -> refactor) feat(refactor): create refactor branch, optimize screenshot, and prune bloat
  ```
- **Commit File Scope**: `git diff-tree --no-commit-id --name-only -r e783aaa` confirms all 17 modified/deleted files are strictly within `themes/lienthongdaihoc/`. Zero files outside the theme repository (e.g., `plugins/`, `uploads/`, `litespeed/`) were staged or committed.
- **D/F Namespace Conflict**: Resolved cleanly by renaming `refactor/school-program-architecture` to `archive/refactor-school-program-architecture` (`e74d6cc`), preserving historical commits.

### 1.2 Preservation of Critical Assets
- **`banner-fallback.png` & `banner-fallback.webp`**:
  - `assets/images/banner-fallback.png`: 1,402,051 bytes, PNG image data, 1734 x 907, RGB. Verified present.
  - `assets/images/banner-fallback.webp`: 98,788 bytes, WebP image, VP8 encoding, 1734 x 907. Verified present.
  - References verified in:
    - `inc/core/class-helpers.php` (lines 2503, 2811, 3063, 3089)
    - `inc/seo/class-rankmath-integration.php` (lines 1624, 1627)
    - `template-parts/cards/card-major.php` (line 25)
  - WP-CLI execution verified:
    - `ltdh_get_school_cover_url(0, "full")` returns `http://localhost:10028/wp-content/themes/lienthongdaihoc/assets/images/banner-fallback.webp`.
    - `ltdh_seo_resolve_global_share_image()` returns `https://ik.imagekit.io/cdnplus/2026/10/banner-fallback-1.webp`.
- **`taxonomy.php`**:
  - `taxonomy.php`: 16,033 bytes, 273 lines, syntax valid (`php -l` clean).
  - WP-CLI template resolution test: `get_taxonomy_template()` resolves directly to `taxonomy.php`. Preserving `taxonomy.php` prevents WordPress from falling back to `index.php` for `region` and `campus` taxonomies before Milestone 2 decomposition.

### 1.3 Bloat Deletion Verification
Direct filesystem inspection confirms deletion of all 14 target bloat files:
- 8 debug screenshots in `assets/images/` (`screenshot_exemption_design*.png`, `screenshot_layout_*.png`): all `exists=False` (~27.29 MB reclaimed).
- `schools_import.json`: `exists=False`.
- `litespeed-cache-settings.data`: `exists=False`.
- `inc/*.bak` (`acf-import-cpts.json.bak`, `acf-import-fields.json.bak`): `exists=False`.
- `scratch/` directory: `exists=False`.

### 1.4 Theme Screenshot Optimization
Empirically inspected `screenshot.png` with Python PIL:
- Format: `PNG`
- Color mode: `P` (256-color palette)
- Dimensions: `1200 x 900`
- Size on disk: `120,971 bytes` (~118.14 KB, well below the 200 KB threshold).
- Visual aspect ratio: Proportional downscale centered vertically on `#f8fafc` canvas.

### 1.5 Theme Operability via WordPress / WP-CLI
- `wp theme status lienthongdaihoc`: Active, Version 2.0.0.
- `wp eval 'echo wp_get_theme()->get("Name");'`: Liên Thông Đại Học.
- Direct WP_Query tests:
  - Front page (ID 23): Found 1 post.
  - School CPT (ID 1853 UTC): Resolved title, slug, and cover URL cleanly.
  - Program CPT (ID 3636): Resolved title cleanly.
  - Major CPT (ID 3612): Resolved title cleanly.
- HTTP rendering: `curl -s -i -H "User-Agent: Mozilla/5.0 (...)" http://localhost:10028/` returned `HTTP/1.1 200 OK` with full semantic HTML layout. (Note: Naked curls are filtered by AIOS firewall `aios_blocked_request_redirect_url` configured in `class-theme-setup.php:45`, returning expected 403 bot block).
- PHP Lint: Scanned all 105 PHP files across theme; 0 syntax errors detected.
- Automated Test Suite Execution:
  - `php tests/run-tests.php`: 17/17 passed (TC-01 through TC-17).
  - `php tests/test-dry-school-program-fields.php`: 38/38 passed.
  - `php tests/test-dry-degree-and-diploma.php`: 21/21 passed.
  - `php tests/test-program-title-and-breadcrumb.php`: 15/15 passed.
  - `php tests/test-program-title-normalization.php`: 9/9 passed.
  - `php tests/test-post-sharing.php`: 16/16 passed.
  - `php tests/test-eligibility-unit.php`: 32/32 passed.
  - `php tests/test-program-sharing.php`: 21/21 passed.
  - Total core passed assertions: 169 passed, 0 failed.

---

## 2. Logic Chain

1. **Git Isolation Verification**:
   - The requirement (§R1) dictates all refactoring work must reside on branch `refactor`.
   - Inspection of `git branch -a`, `git rev-parse`, and `git merge-base` establishes that `refactor` originates from `main` (`bb1ced1`), is currently checked out, and holds commit `e783aaa`.
   - Inspection of `git diff-tree` confirms zero unwanted external files were captured.

2. **Asset Preservation Safety**:
   - Deleting needed theme assets would produce 404 images and broken social previews.
   - Code tracing in `class-helpers.php` and `class-rankmath-integration.php` confirmed that `banner-fallback.png` and `banner-fallback.webp` are required fallbacks.
   - WP-CLI execution verified that helper methods actively return these fallback paths when thumbnails are missing.
   - `get_taxonomy_template()` verified that `taxonomy.php` is active for `region` and `campus`. Retaining it until M2 eliminates any route breakage.

3. **Bloat Purge Safety**:
   - Codebase search verified zero references to the deleted screenshot images, scratch scripts, or `.bak` files.
   - Filesystem verification confirmed the removal of ~27.5 MB of dead weight.

4. **Operability & Non-Regression**:
   - Execution of WP-CLI commands and HTTP requests proved WordPress loads and renders the theme cleanly.
   - 169 unit test assertions across 8 test suites passed with 100% integrity, confirming zero regressions.

---

## 3. Caveats

- **AIOS Firewall on Naked Curls**: Naked `curl` requests without a browser `User-Agent` trigger the AIOS security firewall (`class-theme-setup.php:45`), returning 403. Browser requests and curl with standard browser `User-Agent` return `HTTP/1.1 200 OK`.
- **Legacy Test Suite Outdated String**: `tests/test-specialization-and-collision.php` contains a stale class assertion (`w-4 h-4 shrink-0 text-[#00308b]`) written prior to the Apple icon overhaul (`79011e0`). This is a pre-existing test assertion mismatch, unrelated to M1.
- **Taxonomy Polymorphism Deferred to M2**: As planned in `PROJECT.md`, `taxonomy.php` will be decomposed into BlankSlate archive templates (`archive.php`, `taxonomy-region.php`) during Milestone 2.

---

## 4. Conclusion

**Verdict: APPROVE**

Milestone 1 satisfies all requirements:
1. Branch `refactor` is active and isolated, 1 commit ahead of `main`.
2. All 17 modified/deleted files in commit `e783aaa` are strictly scoped to the theme.
3. ~27.5 MB of bloat has been safely purged with zero broken references.
4. Critical fallbacks (`banner-fallback.png`, `banner-fallback.webp`) and `taxonomy.php` are intact and operational.
5. `screenshot.png` is optimized to standard 1200x900 PNG at 120,971 bytes (<200 KB).
6. WordPress theme status is Active (v2.0.0), 0 PHP syntax errors, and 169/169 core unit tests pass.

The codebase is ready for Milestone 2 (BlankSlate Structural Skeleton & Templates).

---

## 5. Verification Method

To independently reproduce this verification:

```bash
# 1. Verify git branch and commit scope
cd "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content"
git branch --show-current
# Expected: refactor
git log -n 1 --oneline
# Expected: e783aaa feat(refactor): create refactor branch, optimize screenshot, and prune bloat

# 2. Verify asset preservation & bloat purge
cd "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc"
test -f assets/images/banner-fallback.png && test -f assets/images/banner-fallback.webp && test -f taxonomy.php
test ! -f assets/images/screenshot_layout_rows_full.png
test ! -f schools_import.json
test ! -d scratch
echo "Filesystem assertions passed."

# 3. Verify screenshot.png dimensions and size
python3 -c "
import os; from PIL import Image
im = Image.open('screenshot.png')
sz = os.path.getsize('screenshot.png')
assert im.format == 'PNG' and im.size == (1200, 900) and sz < 200 * 1024
print(f'Screenshot valid: {im.size}, {sz} bytes')
"

# 4. Verify theme operability via WP-CLI
cd "/Users/ken/Local Sites/lienthongdaihoc/app/public"
wp theme status lienthongdaihoc
wp eval 'echo "Active Theme: " . wp_get_theme()->get("Name") . "\n";'

# 5. Verify core test suites and PHP syntax
cd "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc"
php tests/run-tests.php
php tests/test-dry-school-program-fields.php
php tests/test-dry-degree-and-diploma.php
php tests/test-eligibility-unit.php
find . -name "*.php" -not -path "./node_modules/*" -not -path "./vendor/*" -not -path "./.agents/*" -not -path "./.agent/*" -exec php -l {} + | grep -v "No syntax errors detected" || echo "All PHP files clean (0 syntax errors)."
```
