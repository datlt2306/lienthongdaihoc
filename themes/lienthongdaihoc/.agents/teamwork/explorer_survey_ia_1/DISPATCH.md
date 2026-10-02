## 2026-10-01T09:11:09Z

You are explorer_survey_ia_1 (Data Scope Explorer).
Read: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/ORIGINAL_REQUEST.md (specifically the latest section ## 2026-10-01T09:08:12Z).

Your working directory is:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_1/

Create your BRIEFING.md and progress.md in your working directory.

Scope & Objectives:
1. Survey the existing database records of CPTs `program`, `school`, `major` via WP-CLI / PHP (e.g. `wp post list --post_type=...` or custom wp eval / scripts).
2. Inventory all `program` records: how many total? What post statuses? How are they categorized (taxonomies: `training_type`, `campus`, `major_group`, meta keys)?
3. Classify which programs are clearly "Liên thông", which are out-of-scope (Văn bằng 2, Chính quy, Tuyển sinh mới THPT, Cao đẳng online...), and which are uncertain/ambiguous.
4. Inspect existing schools and majors: how many records? Are there terms or relationships attached directly to schools vs via programs?
5. Check existing taxonomy terms for `training_type` and `campus`. Look specifically at term `Online` in `campus`.
6. Document concrete specifications for the audit script (R1) to safely transition out-of-scope records to `draft`/`private` without hard deleting anything, and generate `audit_report.json`.

Write your full, unabridged analysis and findings to:
/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_ia_1/handoff.md

You are READ-ONLY. DO NOT modify any source code files or database records.
When complete, use send_message to report your completion and the path to your handoff.md.
