# Báo Cáo Phân Tích & Đề Xuất Khắc Phục FRONT-HIGH-02 (Explorer Fix 2)

- **Người thực hiện:** teamwork_preview_explorer_fix_2 (Frontend Form Remediation Explorer)
- **Thư mục làm việc:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_fix_2`
- **Tệp phân tích trọng tâm:** 
  - `FULL_PROJECT_AUDIT_REPORT.md` (Dòng 557–607)
  - `template-parts/eligibility/results.php` (Dòng 58–140)
  - `assets/js/eligibility.js` (Dòng 404–416, 506–560)
  - `.agents/teamwork/reviewer_2/handoff.md` (Dòng 172–198, 370–413)

---

## 1. OBSERVATION (QUAN SÁT TRỰC NGHIỆM ĐỐI SOÁT)

### 1.1. Vị trí tồn tại của `document.getElementById('elig-lead-form')` và Placeholder Comment
- Quan sát tại `FULL_PROJECT_AUDIT_REPORT.md` (Dòng 579–606):
```javascript
// BEFORE (assets/js/eligibility.js:405-416):
function renderResults(data) {
    var checkIdField = document.getElementById('elig-check-id');
    if (checkIdField) checkIdField.value = data.check_id;

    if (data.programs && data.programs.length > 0) {
        var progIdField = document.getElementById('elig-program-id');
        if (progIdField && data.programs[0]) progIdField.value = data.programs[0].program_id;
    }

    // Lead form submission
    initLeadForm();
    initCardVerifyListeners();
}

// AFTER:
// Sử dụng cờ đánh dấu (dataset) để tránh bind trùng lặp event listener
function initLeadForm() {
    var form = document.getElementById('elig-lead-form');
    if (!form || form.dataset.bound === 'true') return;
    form.dataset.bound = 'true';

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        // Xử lý gửi form an toàn
    });
}
```
- **Xác nhận:**
  1. `document.getElementById('elig-lead-form')` xuất hiện tại **dòng 597**.
  2. Placeholder comment `// Xử lý gửi form an toàn` xuất hiện tại **dòng 603**.

### 1.2. Đối soát DOM ID thực tế trong Template PHP
- Quan sát tại `template-parts/eligibility/results.php` (Dòng 58–62):
```php
	<!-- Step 1: Lead Capture Form -->
	<form id="elig-consultation-form" class="space-y-4" autocomplete="off">
		<input type="hidden" name="elig_check_id" id="elig-check-id" value="">
		<input type="hidden" name="elig_program_id" id="elig-program-id" value="">
```
- Quan sát tại `template-parts/eligibility/results.php` (Dòng 83–84, 95):
```php
	<!-- Step 2: Advanced Verification Section (Hidden initially, shown after lead success) -->
	<div id="elig-advanced-verification-section" class="hidden space-y-6 pt-6 border-t border-slate-200 animate-fade-in">
...
		<form id="elig-advanced-verify-form" class="space-y-4" autocomplete="off">
```
- **Xác nhận:**
  - ID của form tư vấn / thu thập lead thực tế là `elig-consultation-form`, **hoàn toàn không tồn tại ID nào tên là `elig-lead-form`**.
  - ID của vùng xác minh nâng cao là `elig-advanced-verification-section`.

### 1.3. Đối soát mã nguồn thực tế trong `assets/js/eligibility.js`
- Quan sát tại `assets/js/eligibility.js` (Dòng 511–557):
```javascript
	function initLeadForm() {
		var formEl = document.getElementById('elig-consultation-form');
		if (!formEl) return;

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
					
					// Hide standard form elements and show success
					formEl.innerHTML = '<div class="elig-lead-success bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 font-bold mb-4">✅ Gửi yêu cầu thành công! Tư vấn viên sẽ liên hệ với bạn trong 24 giờ.</div>';
					
					// Show Advanced Verification section
					var advSection = document.getElementById('elig-advanced-verification-section');
					if (advSection) {
						advSection.classList.remove('hidden');
						advSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
					initAdvancedVerificationForm();
				} else {
					alert(json.data && json.data.message ? json.data.message : 'Có lỗi xảy ra.');
					if (submitBtn) submitBtn.disabled = false;
				}
			})
			.catch(function (err) {
				console.error('Lead capture error:', err);
				if (submitBtn) submitBtn.disabled = false;
			});
		});
	}
```
- **Xác nhận:** Mã nguồn gốc vốn đã dùng `document.getElementById('elig-consultation-form')`, nhưng thiếu cơ chế chặn bind sự kiện trùng lặp (`dataset.bound`). Khi viết snippet khắc phục cho báo cáo trước đây, tác giả đã vô tình đổi tên selector thành `elig-lead-form` và cắt ngắn thân hàm thành placeholder `// Xử lý gửi form an toàn`.

### 1.4. Đánh giá snippet đề xuất của Reviewer 2
- Reviewer 2 đề xuất tại `.agents/teamwork/reviewer_2/handoff.md:371-413`:
  - Đã khôi phục selector đúng: `document.getElementById('elig-consultation-form')`.
  - Đã thêm guard: `if (!formEl || formEl.dataset.bound === 'true') return; formEl.dataset.bound = 'true';`.
  - Đã khôi phục toàn bộ AJAX logic không placeholder.
  - **Phát hiện sâu thêm (Fine-tuning):** Reviewer 2 gọi `document.getElementById('elig-advanced-verify-section')`, trong khi ID thực tế tại `template-parts/eligibility/results.php:84` và `assets/js/eligibility.js:541` là `elig-advanced-verification-section`. Đồng thời snippet của reviewer 2 bỏ sót hàm `initAdvancedVerificationForm()`. Do đó, snippet hoàn thiện nhất cần dùng fallback an toàn và giữ trọn vẹn lệnh khởi tạo form bước 2.

---

## 2. LOGIC CHAIN (CHUỖI LẬP LUẬN)

1. **Từ Quan sát 1.1 & 1.2:** Đoạn code khắc phục trong báo cáo `FULL_PROJECT_AUDIT_REPORT.md` (dòng 597) sử dụng `document.getElementById('elig-lead-form')`. Vì template `template-parts/eligibility/results.php:59` chỉ render form với ID `elig-consultation-form`, hàm `getElementById('elig-lead-form')` luôn trả về `null`. Hệ quả là không có bất kỳ event listener nào được gắn vào form nếu lập trình viên sao chép đoạn code này.
2. **Từ Quan sát 1.1:** Dòng comment `// Xử lý gửi form an toàn` tại dòng 603 là placeholder thô, vi phạm trực tiếp Acceptance Criteria R5: *"Mọi vấn đề mức Critical và High đều có giải pháp xử lý cụ thể kèm code snippet... không có placeholder"*.
3. **Từ Quan sát 1.3 & 1.4:** Đoạn mã nguồn gốc của `initLeadForm()` trong `assets/js/eligibility.js:511–557` chứa logic hoàn chỉnh xử lý submit AJAX `ltdh_elig_lead`, render thông báo thành công và kích hoạt bước upload xác minh nâng cao `initAdvancedVerificationForm()`. Khiếm khuyết duy nhất của code gốc là mỗi lần `renderResults()` chạy lại (khi người dùng tính toán lại hồ sơ), hàm `initLeadForm()` lại được gọi thêm một lần và tiếp tục `addEventListener('submit')`, gây ra trùng lặp gửi request.
4. **Kết luận logic:** Đoạn code giải pháp chuẩn hóa cho `FULL_PROJECT_AUDIT_REPORT.md` phải là đoạn code hoàn chỉnh, giữ nguyên vẹn toàn bộ logic xử lý AJAX của `assets/js/eligibility.js:511–557`, sử dụng đúng ID `elig-consultation-form`, bổ sung kiểm tra `dataset.bound`, và xử lý hiển thị `elig-advanced-verification-section` một cách chính xác tuyệt đối.

---

## 3. CAVEATS

- **Không tự ý sửa mã nguồn:** Tuân thủ nguyên tắc read-only investigation, không sửa trực tiếp vào file `.js` hay `FULL_PROJECT_AUDIT_REPORT.md`. File này đóng vai trò là tài liệu hướng dẫn kỹ thuật chi tiết cho Worker tiếp theo thực hiện thay thế.
- **Phạm vi tập trung:** Báo cáo này xử lý độc lập và triệt để lỗi `FRONT-HIGH-02`. Các lỗi Schema (`SCHEMA-CRIT-01`, `SCHEMA-HIGH-01`, `SCHEMA-HIGH-03`) do explorer khác hoặc worker phụ trách tương ứng.

---

## 4. CONCLUSION & KẾ HOẠCH BÀN GIAO CHO WORKER

### 4.1. Vị trí dòng cần thay thế trong `FULL_PROJECT_AUDIT_REPORT.md`
- **Tệp mục tiêu:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/FULL_PROJECT_AUDIT_REPORT.md`
- **Dòng bắt đầu (StartLine):** `579`
- **Dòng kết thúc (EndLine):** `606`

### 4.2. Đoạn văn bản hiện tại (TargetContent / Before)
```javascript
// BEFORE (assets/js/eligibility.js:405-416):
function renderResults(data) {
    var checkIdField = document.getElementById('elig-check-id');
    if (checkIdField) checkIdField.value = data.check_id;

    if (data.programs && data.programs.length > 0) {
        var progIdField = document.getElementById('elig-program-id');
        if (progIdField && data.programs[0]) progIdField.value = data.programs[0].program_id;
    }

    // Lead form submission
    initLeadForm();
    initCardVerifyListeners();
}

// AFTER:
// Sử dụng cờ đánh dấu (dataset) để tránh bind trùng lặp event listener
function initLeadForm() {
    var form = document.getElementById('elig-lead-form');
    if (!form || form.dataset.bound === 'true') return;
    form.dataset.bound = 'true';

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        // Xử lý gửi form an toàn
    });
}
```

### 4.3. Đoạn văn bản thay thế hoàn chỉnh (ReplacementContent / After)
```javascript
// BEFORE (assets/js/eligibility.js:511-525):
function initLeadForm() {
    var formEl = document.getElementById('elig-consultation-form');
    if (!formEl) return;

    // Reset form HTML state if it was replaced previously
    formEl.style.display = 'block';

    formEl.addEventListener('submit', function (e) {
        // Thiếu cờ guard dataset.bound dẫn đến bind trùng lặp listener mỗi khi renderResults() chạy
        e.preventDefault();
        ...
    });
}

// AFTER:
// SỬA ĐÚNG: Đúng ID 'elig-consultation-form' (template results.php:59), sử dụng dataset.bound để ngăn trùng lặp listener và giữ trọn vẹn toàn bộ logic xử lý AJAX:
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
                var advSection = document.getElementById('elig-advanced-verification-section') || document.getElementById('elig-advanced-verify-section');
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
```

---

## 5. VERIFICATION METHOD (PHƯƠNG PHÁP XÁC MINH ĐỘC LẬP)

Để kiểm chứng tính chính xác của đề xuất trên, chạy các lệnh kiểm tra sau tại thư mục theme:

1. **Xác nhận form ID trong template results.php:**
   ```bash
   grep -n "<form" template-parts/eligibility/results.php
   ```
   *Kết quả mong đợi:*
   - Dòng 59: `<form id="elig-consultation-form" class="space-y-4" autocomplete="off">`
   - Dòng 95: `<form id="elig-advanced-verify-form" class="space-y-4" autocomplete="off">`

2. **Xác nhận section ID trong template results.php:**
   ```bash
   grep -n "elig-advanced" template-parts/eligibility/results.php
   ```
   *Kết quả mong đợi:* Dòng 84: `<div id="elig-advanced-verification-section" ...>`

3. **Xác nhận sự tồn tại của placeholder trong `FULL_PROJECT_AUDIT_REPORT.md` trước khi sửa:**
   ```bash
   grep -n "Xử lý gửi form an toàn" FULL_PROJECT_AUDIT_REPORT.md
   ```
   *Kết quả mong đợi:* Dòng 603 chứa comment placeholder này.
