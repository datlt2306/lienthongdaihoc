## 2026-10-01T08:51:21Z

You are the Project Orchestrator for the task: "Tái cấu trúc Kiến trúc thông tin (Information Architecture), Thuật ngữ & Templates dự án lienthongdaihoc.com".

Your working directory is:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_4/`

The project root is:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`

Read the authoritative user request in:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md`
under section `## 2026-10-01T08:50:13Z`.

Key requirements and constraints:
1. Core Business Scope (CRITICAL): The website is STRICTLY AND EXCLUSIVELY focused on "LIÊN THÔNG ĐẠI HỌC". Do NOT introduce or maintain unrelated admission types like Văn bằng 2, đại học mới, or independent đại học chính quy.
2. Target Business Model:
   University -> Admission Information -> Study Mode (Chính quy, Vừa học vừa làm, Từ xa) -> Major / Field -> Specific Liên thông admission offering.
3. Terminology & Entities: The smallest useful public unit is a specific admission offering (e.g., Đại học ABC — Liên thông ngành Kế toán — Từ xa). Do not confuse academic curriculum with admission offering. Keep existing entities (program, school, major) if appropriate, adjust labels, semantic hierarchy, and relationships.
4. Mandatory 8-step process (Phase 1: Audit only):
   Step 1: Survey & Read code, *.md, taxonomy, CPTs, templates, URLs, breadcrumbs.
   Step 2: Audit & build Current -> Target mapping table.
   Step 3: Risk assessment (SEO, URLs, template breakage).
   Step 4: Propose minimal safe intervention plan.
   Step 5: Implement controlled modifications.
   Step 6: Syntax check (php -l), query loops, UI.
   Step 7: Verify SEO, URLs, Canonicals, 301 Redirects.
   Step 8: Complete Final Acceptance Report (Sections A to H).
5. SEO & URLs preservation: Do not change public slugs/URLs unnecessarily. If changes are unavoidable, configure 301 redirects, update canonicals, sitemaps, breadcrumbs, internal links. Standardize SEO Title / H1 / Meta Description.
6. Templates, UI & Navigation: Standardize navigation menu, taxonomy-training_type.php, single-program.php, archive/single for major and school, filters. Preserve UI design.
7. Deliverables: Produce the final comprehensive completion report with sections A through H. Maintain your progress in `progress.md` and `BRIEFING.md` in your working directory.
8. Report back to the Sentinel when the task is complete.
