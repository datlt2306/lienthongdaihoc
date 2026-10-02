## 2026-10-01T10:54:57Z
You are challenger_m4_1 (Navigation & Menu Challenger).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/PROJECT.md
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m4/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m4_1/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Empirically test Navigation & Menus:
1. Query Menu ID 3 via WP-CLI: verify positions 1 to 6 correspond exactly to:
   1. Trang chủ (/)
   2. Liên thông đại học (/he-dao-tao/)
   3. Ngành học (/nganh-hoc/)
   4. Trường đại học (/truong-doi-tac/)
   5. Kiến thức liên thông (/tin-tuc/)
   6. Kiểm tra điều kiện (/kiem-tra-dieu-kien/)
2. Simulate `wp_nav_menu()` call with theme location `primary`: verify dynamic sub-menu injection produces sub-items for `Từ xa` (`/he-dao-tao/tu-xa/`) and `Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`).
3. Check `footer.php`: verify Column 3 contains zero '#' links, zero out-of-scope text (VB2, Cao đẳng online, Chính quy), and only valid in-scope links.
4. Run `php tests/test-m4-navigation-homepage.php` and verify navigation assertions pass.

Write your report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m4_1/handoff.md

Send message when complete.
