# Dispatch Log

## 2026-10-01T09:09:30Z

You are the Project Orchestrator (orchestrator_5) for the WordPress project lienthongdaihoc.com.

Your working directory is:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_5/`

Your authoritative instructions and requirements are recorded in:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md`
under the latest section: `## 2026-10-01T09:08:12Z`.

Summary of Mission & Core Objectives:
Execute a controlled refactoring of information architecture, taxonomy, routing, and data display for the WordPress website `lienthongdaihoc.com` to standardize 100% of the scope exclusively on **Liên thông đại học**.

Strict Constraints & Requirements:
1. R1: Data Audit & Safe Scope Handling. Run a PHP/WP-CLI audit script to scan and classify all records of CPT `program`, `school`, `major` and export an audit report (`audit_report.json` or detailed log).
   - Valid Liên thông records remain public; normalize taxonomy `training_type` to either `Từ xa` or `Vừa học vừa làm`.
   - Out-of-scope records (Văn bằng 2, Chính quy, Tuyển sinh mới THPT, Cao đẳng online...): Safely transition `post_status` to `draft` (or `private`). NEVER hard-delete any record from database!
   - Uncertain records: Flag for manual review, exclude from main public admissions stream.
2. R2: Preserve Core 3 CPTs & Standard Data Flow.
   - Strictly keep exactly 3 CPTs: `school`, `major`, `program`. NEVER create new CPTs (`course`, `admission`, `intake`, etc.).
   - School -> Programs -> Attributes (major, training_type). School and major pages must query actual Liên thông programs rather than relying on terms attached directly to school.
   - Taxonomy `campus`: isolate and ensure `Online` does not appear as a physical campus in filters or UI.
3. R3: Taxonomy & Routing Standardization.
   - Taxonomy `training_type`: Frontend display label is "Hình thức học" (`Từ xa`, `Vừa học vừa làm`). Preserve slug `/he-dao-tao/` to protect existing indexed SEO URLs.
   - Strictly DO NOT create redundant taxonomy "Loại tuyển sinh" or redundant filter "Liên thông".
   - Audit `/chuong-trinh/` route: remove forced 301 to `/he-dao-tao/tu-xa/` unless needed for legacy SEO; ensure clean and safe routing.
4. R4: Homepage, Navigation & Filter Refinement.
   - Homepage: Align hero, sections, cards, CTAs 100% to Liên thông admissions by Study Mode (Hình thức học), Major, and School. Remove out-of-scope promotions.
   - Navigation Header & Footer: Standardize menu structure (Trang chủ -> Liên thông [Từ xa, Vừa học vừa làm] -> Ngành học -> Trường đại học -> Kiến thức liên thông -> Tư vấn). Eliminate duplicate links pointing to same archive.
   - Filter/Search: Only filter by Hình thức học (`Từ xa`, `Vừa học vừa làm`), Major, School, and relevant admission criteria.
5. R5: Template & Card Presentation.
   - Program card and `single-program.php`: Display clear admissions opportunity "Liên thông ngành [Tên ngành] - [Hình thức học] tại [Trường]", with requirements, tuition, target audience, duration, and admission status.
   - Archive pages for Study Mode (`/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/`): Query only valid Liên thông programs.

Strict Acceptance Criteria:
- No non-Liên thông records appear on frontend public.
- 100% out-of-scope records safely set to draft/private, 0 hard deleted. Transparent log file.
- Exactly 3 CPTs preserved.
- Frontend label for training_type is "Hình thức học".
- Navigation tree unified, 0 duplicate links.
- 0 PHP errors/warnings/fatals and 0 JS console errors.
