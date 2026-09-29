# Dispatch for challenger_audit_2_v2

## Objective
Re-verify the updated Funnel, UX/UI, Vietnamese search-select, Telegram notification architecture, and IDOR / Decree 13 privacy fixes in `ELIGIBILITY_BUSINESS_AUDIT.md` (v2.1).

## Verification Target
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md`

## Required Verification
1. Verify Vietnamese search algorithm `ltdhSearchMatch` in Section 8.2: check token-aware synonym matching, space replacement for punctuation, and that "Marketing", "ds", and "ngành cntt" all return `true`.
2. Verify "Năm sinh" vs "Năm tốt nghiệp" fix in Sections 5, 8.4, 9.1: check that updating `$years` in `results.php` to range 2001-2026 is explicitly mandated.
3. Verify Telegram notification fix in Section 8.5: check `'blocking' => true` requirement, `reply_to_message_id` with alert sound, and multi-chat ID handling.
4. Verify IDOR vulnerability documentation and HMAC sha256 `lead_verification_token` solution.
5. Verify Decree 13/2023/ND-CP consent checkbox in wizard markup and lead form.
6. Verify touch blur fix using `e.preventDefault()` on `pointerdown` / `mousedown`.
7. Deliver verdict: `APPROVE` or `REQUEST_CHANGES` in `handoff.md`.
