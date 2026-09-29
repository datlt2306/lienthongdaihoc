# Dispatch for worker_audit_2

## Objective
Update and perfect `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md` by directly incorporating all empirical corrections and architectural enhancements from `challenger_audit_1` and `challenger_audit_2`.

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT modify any original theme source code. Only modify `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md` and your agent metadata files.

## Specific Required Fixes

### 1. Mathematical Scoring & Credit Exemption Engine (Mục 7.2, 7.3, 7.4, 7.5):
- **Decoupled Credit Exemption Formula (Mục 7.3)**:
  Replace compound multiplication $C_{\text{exempt}} = \text{round}(C_{\text{total}} \times K_{\text{edu}} \times K_{\text{align}})$ with decoupled formula:
  $$C_{\text{exempt}} = C_{\text{gen\_exempt}} + C_{\text{spec\_exempt}}$$
  where:
  - $C_{\text{gen\_exempt}}$ (General education exemption):
    * ĐH (VB2): $\text{round}(C_{\text{total}} \times 0.35)$ (~45 credits).
    * CĐ: $\text{round}(C_{\text{total}} \times 0.20)$ (~26 credits).
    * TC: $\text{round}(C_{\text{total}} \times 0.10)$ (~13 credits).
    * THPT: 0 credits.
  - $C_{\text{spec\_exempt}}$ (Major specialization exemption):
    * Same major (`same`): ĐH +20% (26 credits, total exempt 71, remain 59 credits, 1.5 yrs); CĐ +25% (32 credits, total exempt 58, remain 72 credits, 2.0 yrs); TC +15% (20 credits, total exempt 33, remain 97 credits, 2.7 yrs).
    * Related major (`related`): +10%-12% core major credits, $C_{\text{bridge}} = 9$.
    * Different major (`different`): $C_{\text{spec\_exempt}} = 0$. CĐ/TC $C_{\text{bridge}} = 15$. VB2 $C_{\text{bridge}} = 0$ (total exempt 46, remain 84 credits, 2.3 yrs). THPT $C_{\text{bridge}} = 0$ (remain 130 credits, 4.0 yrs).
- **Engine Logic for THPT Freshmen & Second Degree (Mục 7.5)**:
  - In `LTDH_Eligibility_Scoring_Engine`, when `$user_edu === 'thpt'`, set `$major_coef = 1.0`, `$align_type = 'freshman'`, no penalty!
  - When `$user_edu === 'dai-hoc'`, handle VB2 same (1.0), related (0.90), different (0.80).
  - In Smart Alternatives: remove `$user_major > 0` condition from `ltdh_elig_are_majors_related($desired_major, $prog_major_id)` so THPT applicants can receive alternative suggestions.
  - Add Hard Gate check: If `empty($desired_major) || $desired_major <= 0`, return `is_hard_pass = false`.
  - Align Table 7.2 and Code 7.5 for unannounced tuition: unannounced or $\le 0$ tuition returns `ratio = 0.20` (3.0 pts) with warning label.
  - Add explicit Health Sciences checks for practicing certificate (`has_practicing_license`) and academic rank (`academic_rank`) in the engine code.

### 2. Funnel, Search-Select, Privacy & Telegram Fixes (Mục 3.2, 4.5, 5, 8, 9):
- **Vietnamese Search Algorithm & Synonyms (Mục 8.2)**:
  - Fix tokenization to replace punctuation with space `replace(/[^a-z0-9]/g, ' ')`.
  - Fix `ltdhSearchMatch` to avoid requiring all tokens of synonyms with `every()`. Ensure queries like "Marketing", "ds", and "ngành cntt" match correctly.
- **Năm sinh vs Năm tốt nghiệp (Mục 5, 8.4, 9.1)**:
  - Clarify that simply changing the label to "Năm tốt nghiệp" breaks the form because `results.php:8-9` sets `$years = range($current_year - 18, $current_year - 70)` ($1956 - 2008$).
  - Mandate updating `$years` in `results.php` to `$years = range($current_year, $current_year - 25)` ($2001 - 2026$) when collecting graduation year, or decouple into two fields: `birth_year` (1956-2008) and `graduation_year` (2001-2026).
- **Telegram Notification & Non-blocking Fix (Mục 8.5)**:
  - Document that `inc/lead-capture.php` uses `'blocking' => false`, so `message_id` cannot be captured unless `'blocking' => true` is used for lead creation.
  - Recommend `reply_to_message_id` with active ping notifications rather than silent `editMessageText` so admission counselors receive an alert when diplomas are uploaded.
  - Support multi-chat IDs in schema (`telegram_message_ids text`).
- **IDOR Vulnerability & Decree 13/2023/ND-CP (Mục 3.2, 4.5, 8.4, 9)**:
  - Document IDOR vulnerability in `ltdh_elig_ajax_advanced_verify()` where unauthenticated users can modify any `lead_id`. Fix by issuing a signed `lead_verification_token` (HMAC sha256) at Tier 2A and validating it at Tier 2B.
  - Add explicit Decree 13 affirmative consent checkbox to Tier 2A & 2B form designs.
- **Dropdown Touch Blur Fix (Mục 8.3)**:
  - Document calling `e.preventDefault()` on `pointerdown`/`mousedown` of dropdown options to prevent `<input>` blur during touchscreen scrolling and selection.

Write the updated `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md`, create `handoff.md`, and report back.
