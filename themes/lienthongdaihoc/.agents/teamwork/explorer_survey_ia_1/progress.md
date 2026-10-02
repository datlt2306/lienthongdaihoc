# Progress Tracker — explorer_survey_ia_1

**Mission**: Survey existing database records (CPTs: `program`, `school`, `major`), taxonomy terms, relations, and define safe audit specifications.
**Last visited**: 2026-10-01T09:30:00Z

## Status
- [x] Initialized DISPATCH.md and BRIEFING.md
- [x] Checked environment and WP-CLI / PHP connectivity
- [x] Inventoried CPT `program` (100 total posts, 100 publish, taxonomies `training_type` and `campus`, meta relationships)
- [x] Inventoried CPT `school` (21 posts) & `major` (34 posts) (counts, taxonomies, relationships, identified orphaned IDs 1855, 1856)
- [x] Examined `training_type` and `campus` taxonomies (isolated `campus: online` issue with 32 programs)
- [x] Classified in-scope vs out-of-scope programs (95 in-scope: 94 Từ xa + 1 Vừa học vừa làm; 5 out-of-scope: 1 Chính quy + 4 Cao đẳng HCCT; 0 uncertain)
- [x] Formulated concrete audit specifications for R1 (`audit_report.json`, safe draft transition, cleanup of `_offered_programs`)
- [x] Updated BRIEFING.md with findings and decisions
- [x] Wrote comprehensive `handoff.md` (444 lines, 5-component report)
- [x] Send handoff message to parent agent
