// Scratch test for JS snippet syntax verification (FRONT-HIGH-02)

function initLeadForm() {
    var formEl = document.getElementById('elig-consultation-form');
    if (!formEl || formEl.dataset.bound === 'true') return;
    formEl.dataset.bound = 'true';

    // Reset form HTML state if it was replaced previously
    formEl.style.display = 'block';

    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        var submitBtn = document.getElementById('elig-lead-submit-btn');
        if (submitBtn) submitBtn.disabled = true;

        var data = new FormData(formEl);
        data.append('action', 'ltdh_elig_lead');
        data.append('nonce', ltdh_elig.nonce);

        fetch(ltdh_elig.ajax_url, {
            method: 'POST',
            body: data,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            if (json.success) {
                currentLeadId = json.data.lead_id;
                
                // Hiển thị thông báo thành công
                formEl.innerHTML = '<div class="elig-lead-success bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 font-bold mb-4">✅ Gửi yêu cầu thành công! Tư vấn viên sẽ liên hệ với bạn trong 24 giờ.</div>';
                
                // Mở section xác minh nâng cao
                var advSection = document.getElementById('elig-advanced-verification-section');
                if (advSection) {
                    advSection.classList.remove('hidden');
                    advSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (typeof initAdvancedVerificationForm === 'function') {
                    initAdvancedVerificationForm();
                }
            } else {
                alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra, vui lòng thử lại.');
                if (submitBtn) submitBtn.disabled = false;
            }
        })
        .catch(function (err) {
            console.error('Lead submit error:', err);
            if (submitBtn) submitBtn.disabled = false;
        });
    });
}
