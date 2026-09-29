## 2026-09-28T04:04:15Z

User Request:
Thực hiện rà soát và đánh giá chuyên sâu toàn diện về logic nghiệp vụ, mô hình dữ liệu (Data Modeling & Architecture) và luồng tương tác thực tế của việc quản lý **Hệ đào tạo** (`training_type`) và **Trường đối tác** (`school`) trong hệ thống Liên Thông Đại Học.

Working directory: /Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc
Integrity mode: development

Requirements:
- R1: Data Architecture & Entity Modeling (`School` CPT ⟷ `Major` CPT ⟷ `Program` CPT ⟷ `Training Type` Taxonomy ⟷ `Campus` Taxonomy, pros/cons, alignment with Vietnam admissions reality).
- R2: Querying, Filtering & Taxonomy UX (`WP_Query`, taxonomy archives, N+1 issues, SEO/Breadcrumbs/Canonical).
- R3: Regulatory Compliance & Degree Trust (Thông tư 27/2019/TT-BGDĐT, degree value messaging, admission legal basis).
- R4: Lead Routing & Admissions Funnel (CRM integration OnSchool, AUM, Telegram Bot with school/training_type/campus codes, extensibility).

Strict Constraints:
- ZERO modification of theme source code. This is an audit and analysis task only.
- Deliverable: Export comprehensive audit report `SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md` in the theme root `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/SCHOOLS_AND_TRAINING_SYSTEMS_AUDIT.md`.
- Keep `progress.md` and `BRIEFING.md` continuously updated in your working directory.
- When done, report back with your completion summary.
