## 2026-10-01T10:12:44Z
You are challenger_m3_1 (M3 Routing & Canonical Challenger).
Read:
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically ## 2026-10-01T09:08:12Z)
- /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/worker_m3/handoff.md

Working directory:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_1/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Task:
Empirically test routing via WP-CLI / PHP scripts:
1. Simulate request to `/chuong-trinh/` and verify 301 target is `/he-dao-tao/` (NOT `/he-dao-tao/tu-xa/`).
2. Simulate request to `/chuong-trinh/?truong=utc&nganh=cntt` and verify query args are preserved in the redirect URL.
3. Verify Rank Math canonical URL function for program archive produces `home_url('/he-dao-tao/')`.
4. Verify that `/he-dao-tao/` returns HTTP 200 without redirect loop.

Write your report and verdict (APPROVE or REQUEST_CHANGES) to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/challenger_m3_1/handoff.md

Send message when complete.
