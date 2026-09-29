def estimate_credit_exemption(edu_level, align_type, total_credits=130):
    if edu_level in ['thpt', 'thap-phan'] or align_type == 'freshman':
        return {
            'total_credits': total_credits,
            'exempted_credits': 0,
            'gen_exempt': 0,
            'spec_exempt': 0,
            'bridge_credits': 0,
            'remain_credits': total_credits,
            'estimated_years': 4.0,
            'saved_months': 0
        }
    
    gen_pct = {
        'dai-hoc': 0.35,
        'cao-dang': 0.20,
        'trung-cap': 0.10
    }.get(edu_level, 0.0)
    gen_exempt = int(round(total_credits * gen_pct))
    
    spec_exempt = 0
    bridge = 0
    if align_type == 'same':
        spec_pct = {
            'dai-hoc': 0.20,
            'cao-dang': 0.25,
            'trung-cap': 0.15
        }.get(edu_level, 0.0)
        spec_exempt = int(round(total_credits * spec_pct))
        bridge = 0
    elif align_type == 'related':
        spec_pct = {
            'dai-hoc': 0.10,
            'cao-dang': 0.12,
            'trung-cap': 0.08
        }.get(edu_level, 0.0)
        spec_exempt = int(round(total_credits * spec_pct))
        bridge = 9
    else: # different
        spec_exempt = 0
        bridge = 0 if edu_level == 'dai-hoc' else 15
        
    total_exempt = gen_exempt + spec_exempt
    remaining = max(30, total_credits - total_exempt + bridge)
    credits_per_year = 36
    years = max(1.5, round(remaining / credits_per_year, 1))
    saved_months = max(0, int(round((total_exempt / credits_per_year) * 12)))
    
    return {
        'total_credits': total_credits,
        'exempted_credits': total_exempt,
        'gen_exempt': gen_exempt,
        'spec_exempt': spec_exempt,
        'bridge_credits': bridge,
        'remain_credits': remaining,
        'estimated_years': years,
        'saved_months': saved_months
    }

profiles = [
    ('Profile 1: THPT -> ĐH Từ xa', 'thpt', 'freshman'),
    ('Profile 2: CĐ đúng ngành -> ĐH', 'cao-dang', 'same'),
    ('Profile 3: CĐ khác ngành -> ĐH', 'cao-dang', 'different'),
    ('Profile 4: ĐH học VB2 khác ngành', 'dai-hoc', 'different'),
    ('Profile 4b: ĐH học VB2 cùng ngành', 'dai-hoc', 'same'),
]

print('=== TESTING INDEPENDENT FORMULA AGAINST CLAIMED PROFILES ===')
for name, edu, align in profiles:
    res = estimate_credit_exemption(edu, align, 130)
    g_ex = res['gen_exempt']
    s_ex = res['spec_exempt']
    t_ex = res['exempted_credits']
    br = res['bridge_credits']
    rem = res['remain_credits']
    yr = res['estimated_years']
    mo = res['saved_months']
    print(f"\n{name}:")
    print(f"  Gen exempt: {g_ex}, Spec exempt: {s_ex}, Total exempt: {t_ex}")
    print(f"  Bridge credits: {br}, Remaining credits: {rem}")
    print(f"  Estimated years: {yr} years ({mo} months saved)")
