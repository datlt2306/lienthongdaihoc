# Task Assignment: Survey & Codebase Audit - SEO, URLs, Canonicals, Breadcrumbs & Redirects

## 2026-10-01T08:52:36Z

Working Directory: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3/`
Project Root: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`

Read:
1. `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md` (specifically section `## 2026-10-01T08:50:13Z`).
2. `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3/DISPATCH.md`.

Your focus:
1. Inspect URL structures, rewrites, slugs, permalinks for all CPTs, taxonomies, and pages:
   - CPT slugs: `chuong-trinh`, `truong`, `nganh`, etc.
   - Taxonomy slugs: `he-dao-tao` (or `hinh-thuc-dao-tao`), `co-so`, `nhom-nganh`, etc.
2. Audit Breadcrumbs implementation (Yoast / RankMath / Custom breadcrumb walker / Schema BreadcrumbList).
3. Audit SEO titles, H1 tags, meta descriptions across archive and single templates:
   - Standard format required: `Liên thông [Hình thức học]`, `Liên thông [Ngành]`, `Liên thông [Trường]`.
4. Identify risks of URL breakage, changes needed, and define exact 301 Redirect strategy (`OLD URL -> 301 REDIRECT -> NEW URL`).
5. Audit Canonical URL generation and Schema JSON-LD markup.
6. Produce the Current -> Target mapping table for URLs, SEO metadata, Breadcrumbs, and Redirects according to the required schema:
   `CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`
7. Propose a minimal safe intervention plan for SEO & URLs.

Write your complete findings and report to `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_3/handoff.md`.
Then notify the orchestrator with `send_message`.

