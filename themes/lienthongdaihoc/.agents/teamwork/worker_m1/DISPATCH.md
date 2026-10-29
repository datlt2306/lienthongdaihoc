# DISPATCH for worker_m1

You are teamwork_worker assigned to execute Milestone 1: Git Branching & Bloat Sanitization.
Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m1/
ORIGINAL_REQUEST.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
PROJECT.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/PROJECT.md

## MANDATORY INTEGRITY WARNING
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Explorer Analyses Reference
- explorer_m1_1 report: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m1_1/analysis.md`
- explorer_m1_2 report: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m1_2/analysis.md`
- explorer_m1_3 report: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m1_3/analysis.md`

## Milestone 1 Objectives & Execution Plan
1. **Branch Creation**:
   - Repository root is `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content`.
   - Verify current branch and switch to `refactor`: `git checkout -b refactor`.
   - Verify active branch with `git branch --show-current`.

2. **Bloat Sanitization**:
   - Delete 8 debug screenshots: `rm -f assets/images/screenshot_exemption_design*.png assets/images/screenshot_layout_*.png` (-27.2 MB).
   - Delete backup files: `rm -f inc/acf-import-*.json.bak` (-74 KB).
   - Delete stale data dumps: `rm -f schools_import.json litespeed-cache-settings.data` (-72 KB).
   - Delete scratch directory: `rm -rf scratch/` (-60 KB).
   - DO NOT delete `assets/images/banner-fallback.png` (actively used by theme).
   - Retain `taxonomy.php` for now (explorer_m1_2 verified active routing for `/khu-vuc/` and `/co-so/`; it will be refactored into `archive.php` in M2).

3. **Screenshot Optimization**:
   - Compress root `screenshot.png` from 5.9 MB to a valid 1200x900 PNG < 200 KB using the verified Python Pillow script from explorer_m1_3.

4. **Verification & Testing**:
   - Run `php tests/run-tests.php` -> verify 17/17 pass.
   - Run `php tests/test-dry-school-program-fields.php` -> verify 38/38 pass.
   - Run PHP lint check on all theme PHP files.

5. **Atomic Commit**:
   - Stage ONLY files within `themes/lienthongdaihoc/` (do NOT stage `wp-content/litespeed/`).
   - Commit: `git commit -m "feat(refactor): create refactor branch, optimize screenshot, and prune bloat"`

Document all executed commands, outputs, and verification results in `handoff.md`.
Report back when finished.

## 2026-10-29T06:49:57Z
You are teamwork_worker assigned to execute Milestone 1: Git Branching & Bloat Sanitization.
Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m1/
ORIGINAL_REQUEST.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md
PROJECT.md path: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/PROJECT.md

DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Please read DISPATCH.md in your working directory and the 3 explorer reports:
- explorer_m1_1: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m1_1/analysis.md
- explorer_m1_2: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m1_2/analysis.md
- explorer_m1_3: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_m1_3/analysis.md

Execute:
1. Create and switch to branch `refactor` in the git repository at `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content`. Verify active branch is `refactor`.
2. Delete bloat:
   - 8 debug screenshots in assets/images/screenshot_*.png (-27.2 MB)
   - backup files inc/acf-import-*.json.bak (-74 KB)
   - stale data dumps schools_import.json, litespeed-cache-settings.data (-72 KB)
   - scratch directory scratch/ (-60 KB)
   - Do NOT delete assets/images/banner-fallback.png
   - Retain taxonomy.php for Milestone 2
3. Optimize screenshot.png to standard 1200x900 PNG < 200 KB via Python script.
4. Run verification tests:
   - php tests/run-tests.php (17/17 passed)
   - php tests/test-dry-school-program-fields.php (38/38 passed)
   - PHP lint syntax check
5. Commit staged theme changes cleanly on branch `refactor`.

Document your complete output, command logs, and verification proof in handoff.md.
Send a message when finished.
