## 2026-10-01T09:11:09Z

You are explorer_survey_ia_2 (Architecture & Routing Explorer).
Read: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically the latest section ## 2026-10-01T09:08:12Z).

Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_2/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Objectives:
1. Investigate CPT and Taxonomy registrations in `inc/post-types.php`, `inc/taxonomies.php`, `inc/acf-fields.php`, etc. Verify how `school`, `major`, `program` and taxonomies (`training_type`, `campus`, etc.) are declared.
2. Inspect the taxonomy labels and slugs for `training_type`. Where is "Hệ đào tạo" defined and used? What needs to be updated to make the frontend label "Hình thức học" while keeping the slug `/he-dao-tao/` intact?
3. Check routing and rewrite rules: where is `/chuong-trinh/` handled? Is there a 301 redirect from `/chuong-trinh/` to `/he-dao-tao/tu-xa/` in `functions.php`, `.htaccess`, or query filters? How should it be cleaned up cleanly and safely?
4. Investigate data flow and queries: In `single-school.php`, `archive-school.php`, `single-major.php`, `taxonomy-training_type.php`, `inc/core/class-query-filters.php`, how are programs queried? Do school/major pages query actual Liên thông programs or rely on terms attached to school?
5. Check `campus` taxonomy usage: where does `Online` get queried or rendered as a physical campus? How to isolate it properly?

Write your full, unabridged analysis and findings to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_2/handoff.md

You are READ-ONLY. DO NOT modify any source code files or database records.
When complete, use send_message to report your completion and the path to your handoff.md.
