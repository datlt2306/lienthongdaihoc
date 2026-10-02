# Project: lienthongdaihoc.com IA & Scope Refactoring

## Architecture
Controlled refactoring of information architecture, taxonomy, routing, and data display for `lienthongdaihoc.com` to standardize 100% of the scope exclusively on **Liên thông đại học**.

```
Trường Đại học (School CPT)
    ↓
Thông tin tuyển sinh Liên thông
    ↓
Hình thức học (Study Mode / training_type taxonomy)
    ├── Từ xa (tu-xa)
    └── Vừa học vừa làm (vua-hoc-vua-lam)
    ↓
Ngành học (Major CPT / major_relationship)
    ↓
Cơ hội / Chương trình tuyển sinh Liên thông cụ thể (Program CPT)
Formula: "Liên thông ngành [Tên ngành] - [Hình thức học] tại [Tên trường]"
```

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Data Audit & Classification | Scan and classify all 100 `program`, 21 `school`, 34 `major` records | M1 | Survey 1 / R1 |
| 2 | Safe Out-of-Scope Isolation | Transition 5 out-of-scope programs (ID 2013, 1786-1789) and 1 school (HCCT ID 1662) to `draft` (0 hard deletes) | M1 | Survey 1 / R1 |
| 3 | Orphan Meta Pruning | Clean ghost IDs (1855, 1856) and drafted IDs from `_offered_programs` on UTC (1853) & CNTT (1677) | M1 | Survey 1 / R1 |
| 4 | Audit Report Generation | Emit `audit_report.json` with complete transparency and classification log | M1 | Survey 1 / R1 |
| 5 | Preserve Core 3 CPTs | Strictly maintain `school`, `major`, `program`; zero new CPTs created | M2 | Survey 2 / R2 |
| 6 | School & Major Rollup Queries | `ltdh_get_school_training_types()` and single view queries roll up exclusively from active published Liên thông programs | M2 | Survey 2 / R2 |
| 7 | Campus `Online` Isolation | Isolate `online` in `campus` taxonomy from physical facility UI and filters; show "Toàn quốc" or delivery mode | M2 | Survey 1 & 2 / R2 |
| 8 | Fix Syntax Corruption in `taxonomy.php` | Fix corrupted permalink syntax on line 220 of `taxonomy.php` | M2 | Survey 2 |
| 9 | Taxonomy Label Standardization | Rename frontend display of `training_type` from "Hệ đào tạo" to "Hình thức học" across all templates, breadcrumbs, banners, filters | M3 | Survey 2 / R3 |
| 10 | Preserve Indexed Slugs | Retain `/he-dao-tao/`, `/he-dao-tao/tu-xa/`, `/he-dao-tao/vua-hoc-vua-lam/` | M3 | Survey 2 / R3 |
| 11 | Clean `/chuong-trinh/` 301 Route | Replace forced 301 to `/he-dao-tao/tu-xa/` with clean redirect to `/he-dao-tao/` preserving query args; update canonicals | M3 | Survey 2 & 3 / R3 |
| 12 | Header & Footer Navigation | Standardize Menu ID 3 (Trang chủ -> Liên thông [Từ xa, VHVL] -> Ngành -> Trường -> Tin tức -> Kiểm tra điều kiện -> Tư vấn); clean footer | M4 | Survey 3 / R4 |
| 13 | Homepage Alignment | Update semantic H1, hero badges, remove THPT step, update sample testimonials and news to 100% Liên thông | M4 | Survey 3 / R4 |
| 14 | Filter & Search Refinement | Filter forms submit to `/he-dao-tao/`; pills limited to "Tất cả", "Từ xa", "Vừa học vừa làm"; 0 redundant filters | M4 | Survey 3 / R4 |
| 15 | Program Card Standardization | Standardize SSR and AJAX cards to headline formula "Liên thông ngành [Tên ngành] - [Hình thức học] tại [Trường]" | M5 | Survey 3 / R5 |
| 16 | Single Program Template | Clean `single-program.php` quota announcement; display requirements, tuition, duration, learning mode | M5 | Survey 3 / R5 |
| 17 | Study Mode Archives | Ensure `/he-dao-tao/tu-xa/` and `/he-dao-tao/vua-hoc-vua-lam/` query only valid published Liên thông programs | M5 | Survey 3 / R5 |
| 18 | Comprehensive E2E Verification | 100% acceptance criteria check, 0 non-Liên thông public, 0 PHP errors/warnings, 0 JS errors | M6 | Acceptance Criteria |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | Data Audit & Safe Scope Handling | WP-CLI audit script, transition 5 programs + 1 school to draft, prune ghost IDs, emit `audit_report.json` | None | DONE |
| M2 | Core CPTs, Data Flow & Campus Isolation | `ltdh_get_school_training_types()`, single school/major queries, isolate campus `online`, fix `taxonomy.php:220` | M1 | DONE |
| M3 | Taxonomy Label & Clean Routing | Rename "Hệ đào tạo" -> "Hình thức học", preserve slugs, clean `/chuong-trinh/` 301 route & Rank Math canonicals | M2 | DONE |
| M4 | Navigation, Homepage & Filters | Header menu (Menu 3), footer columns, homepage hero/H1/steps/testimonials, filter forms & pills | M3 | DONE |
| M5 | Templates & Program Presentation | Program cards (SSR & AJAX), `single-program.php` presentation, study mode archives | M4 | DONE |
| M6 | Comprehensive Verification & Victory Audit | Run full test suite, verify 0 PHP warnings/fatals, verify all 5 requirements, hand off to Sentinel | M5 | DONE |

## Interface Contracts
### Data Audit Script Contract (M1)
- WP-CLI command: `wp ltdh audit-data [--dry-run] [--apply] [--output-file=audit_report.json]`
- Post Status: Out-of-scope records set to `draft` (IDs: 2013, 1786, 1787, 1788, 1789, and School 1662).
- Zero calls to `wp_delete_post()`.
- Metadata `_offered_programs` filtered: only existing, `publish`, `program` IDs retained.
- Output: `audit_report.json` in theme root or working directory.

### School/Major Rollup Contract (M2)
- Function `ltdh_get_school_training_types( $school_id )`:
  - Returns `WP_Term[]` or slugs derived exclusively from published `program` posts where `school_relationship = $school_id` and taxonomy term in `['tu-xa', 'vua-hoc-vua-lam']`.
- Helper `ltdh_get_program_learning_details( $program_id )`:
  - Strips `online` from physical campuses. If physical campus exists -> return physical campus string. If no physical campus and `tu-xa` -> return `"Toàn quốc"`.
  - Under `mode` -> return `"Học online 100%"` (for `tu-xa`) or `"Học tập trung / Cuối tuần"` (for `vua-hoc-vua-lam`).

### Routing Contract (M3)
- Request `/chuong-trinh/` -> HTTP 301 to `/he-dao-tao/` (preserving `$_GET`).
- Rank Math canonical on `is_post_type_archive( 'program' )` -> `home_url( '/he-dao-tao/' )`.
- No redirect loop between `/chuong-trinh/` and `/he-dao-tao/`.

### Headline Presentation Contract (M5)
- Standard headline string across SSR & AJAX cards:
  `"Liên thông ngành " . esc_html($major_name) . " - " . esc_html($training_type_name)` with school name as institution subtitle.

## Code Layout
- `inc/cli-commands.php`: WP-CLI commands including `audit-data`.
- `inc/core/class-helpers.php`: `ltdh_get_school_training_types()`, `ltdh_get_program_learning_details()`, breadcrumbs.
- `inc/core/class-menus.php`: Menu registration, fallback menu, dynamic submenu injection.
- `inc/core/class-rewrite-rules.php`: Custom rewrites and redirect rules.
- `inc/core/class-query-filters.php`: AJAX program filter, archive query customizations.
- `inc/config/class-defaults.php`: Fallback strings, hero defaults, site defaults.
- `front-page.php`: Homepage template.
- `header.php`: Primary header navigation.
- `footer.php`: Footer navigation columns.
- `archive-program.php`: Program catalog archive template.
- `taxonomy-training_type.php`: Training type / Study mode archive template.
- `single-program.php`: Single program detail view.
- `single-school.php`: Single school detail view.
- `single-major.php`: Single major detail view.
- `taxonomy.php`: General taxonomy view (needs syntax fix on line 220).
