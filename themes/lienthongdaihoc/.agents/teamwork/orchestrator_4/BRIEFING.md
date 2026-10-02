# BRIEFING — 2026-10-01T08:52:45Z

## Mission
Tái cấu trúc Kiến trúc thông tin (Information Architecture), Thuật ngữ & Templates dự án lienthongdaihoc.com tập trung 100% vào Liên Thông Đại Học.

## 🔒 My Identity
- Archetype: orchestrator
- Roles: orchestrator, user_liaison, human_reporter, successor
- Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/orchestrator_4/
- Original parent: Sentinel
- Original parent conversation ID: c0662936-edaf-481e-801f-dd164e3ea7e4

## 🔒 My Workflow
- **Pattern**: Project
- **Scope document**: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/PROJECT.md
1. **Decompose**: Survey (3 explorers) -> Audit & Mapping -> Decompose into milestones -> Interface contracts
2. **Dispatch & Execute**:
   - **Direct (iteration loop)**: Explorer (3) -> Worker (1) -> Reviewer (2) -> Challenger (2) -> Auditor (1) -> Gate
3. **On failure** (in this order):
   - Retry: nudge stuck agent or re-send task
   - Replace: spawn fresh agent with partial progress
   - Skip: proceed without (only if non-critical)
   - Redistribute: split stuck agent's remaining work
   - Redesign: re-partition decomposition
   - Escalate: report to parent (sub-orchestrators only, last resort)
4. **Succession**: at 16 spawns, write handoff.md, spawn successor
- **Work items**:
  1. Survey & Codebase Audit [in-progress]
  2. Current -> Target Mapping & Risk Assessment [pending]
  3. Minimal Safe Intervention Plan & Milestone Decomposition [pending]
  4. Implementation & Controlled Refactoring [pending]
  5. Verification (PHP Syntax, Query Loops, UI, SEO, URLs) [pending]
  6. Final Acceptance Report (Sections A-H) [pending]
- **Current phase**: 0 (Survey)
- **Current focus**: Survey & Explorers dispatch (Explorer 1, 2, 3 running)

## 🔒 Key Constraints
- NEVER write, modify, or create source code files directly.
- NEVER run build/test commands yourself — require workers to do so.
- NEVER investigate or explore the problem at the code level — dispatch Explorers for technical investigation.
- Mandatory 8-step process (Phase 1: Audit only before proposing plan).
- Core Business Scope: STRICTLY AND EXCLUSIVELY "LIÊN THÔNG ĐẠI HỌC". No Văn bằng 2, đại học mới, or independent đại học chính quy.
- Preserve SEO slugs / URLs unless unavoidable (with 301 redirects).
- Auditor is NON-SKIPPABLE. Binary veto.
- Never reuse a subagent after it has delivered its handoff — always spawn fresh.

## Current Parent
- Conversation ID: c0662936-edaf-481e-801f-dd164e3ea7e4
- Updated: 2026-10-01T08:51:21Z

## Key Decisions Made
- Initiated Survey Phase: Dispatched 3 parallel explorers to investigate (1) Data Model & Terminology, (2) Templates & UI, (3) SEO & URLs.

## Team Roster
| Agent | Type | Work Item | Status | Conv ID |
|-------|------|-----------|--------|---------|
| explorer_survey_1 | teamwork_preview_explorer | IA & Data Model Explorer | in-progress | c0b5401f-0de7-4068-a9d0-75a101f6476c |
| explorer_survey_2 | teamwork_preview_explorer | Templates & UI Explorer | in-progress | 60099fad-8188-486c-a63e-b5dc5d23869c |
| explorer_survey_3 | teamwork_preview_explorer | SEO & URLs Explorer | in-progress | 05f90af1-f98b-434f-95ec-20f21cdc8a43 |

## Succession Status
- Succession required: no
- Spawn count: 3 / 16
- Pending subagents: c0b5401f-0de7-4068-a9d0-75a101f6476c, 60099fad-8188-486c-a63e-b5dc5d23869c, 05f90af1-f98b-434f-95ec-20f21cdc8a43
- Predecessor: none
- Successor: not yet spawned

## Active Timers
- Heartbeat cron: 1ef7ebb7-b196-4d09-8346-e85d510f7b1e/task-10
- Safety timer: scheduled
- On succession: kill all timers before spawning successor
- On context truncation: run `manage_task(Action="list")` — re-create if missing

## Artifact Index
- ORIGINAL_REQUEST.md — user request
- DISPATCH.md — incoming dispatch instructions
- progress.md — liveness and execution progress
