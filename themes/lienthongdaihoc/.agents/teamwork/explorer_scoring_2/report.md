# BÁO CÁO KIỂM ĐỊNH TOÀN DIỆN THUẬT TOÁN TÍNH ĐIỂM, XẾP HẠNG GỢI Ý & MIỄN GIẢM TÍN CHỈ (R2)

**Dự án:** Theme WordPress Liên Thông Đại Học (`lienthongdaihoc`)  
**Tác vụ:** R2 — Phân tích chi tiết thuật toán tính điểm điều kiện, gợi ý chương trình, miễn giảm tín chỉ & rà soát ca biên (Edge Cases)  
**Phạm vi file phân tích:**  
- `inc/eligibility.php` (1703 dòng)  
- `inc/eligibility-rules.php` (161 dòng)  
- `template-parts/eligibility/results.php` (169 dòng)  
- `template-parts/eligibility/wizard.php` (122 dòng)  
- `assets/js/eligibility.js` (673 dòng)  
**Chế độ:** Điều tra chỉ đọc (Read-only Investigation) — Không sửa đổi trực tiếp mã nguồn.  
**Ngày thực hiện:** 2026-09-25  

---

## 1. TỔNG QUAN KẾT QUẢ KIỂM ĐỊNH (EXECUTIVE SUMMARY)

Qua rà soát từng dòng mã nguồn (line-by-line) của module Eligibility Checker Engine, nhóm điều tra phát hiện **nhiều lỗ hổng thuật toán, sai lệch toán học nghiêm trọng, thiếu hụt toàn bộ logic nghiệp vụ cốt lõi (miễn giảm tín chỉ)** và **xung đột kiến trúc giữa SQL query và bộ lọc in-memory**:

1. **Điểm số tối đa trên thực tế không bao giờ đạt 100% (Thực tế chỉ đạt tối đa 80%, hoặc 60% trên giao diện hiện tại):**
   - Trọng số định nghĩa trong `inc/eligibility-rules.php` chỉ đạt tổng 90 điểm (thiếu 10 điểm để thành 100).
   - Tiêu chí `graduation_recent` (10 điểm) **bị bỏ quên hoàn toàn**, không được tính trong `inc/eligibility.php`.
   - Tiêu chí `budget` (20 điểm) **không xuất hiện trong form giao diện người dùng** (`wizard.php`), nên API luôn nhận `budget = ''`, dẫn đến người dùng bình thường chỉ đạt tối đa **60/100 điểm** (hiển thị tương thích 60% dù hồ sơ khớp 100%).
2. **Thuật toán tính chi phí học phí bị sai lệch hàng chục đến hàng trăm lần:**
   - Công thức tại dòng 486 `inc/eligibility.php`: `$total_cost = $tuition_num * 120 * $duration_num;`.
   - Nhân cả đơn giá với 120 tín chỉ và tiếp tục nhân với số năm học ($duration_num = 1.5 - 2$), làm đội chi phí lên $180 - 240$ tín chỉ. Nếu học phí lưu theo học kỳ (ví dụ: 15.000.000đ/học kỳ), thuật toán tính ra **2,7 TỶ ĐỒNG**, khiến 100% chương trình bị trượt ngân sách.
3. **Logic Miễn giảm tín chỉ (Credit Exemption) HOÀN TOÀN KHÔNG TỒN TẠI trong code:**
   - Dù trang chủ và banner tiếp thị cam kết *"Liên thông miễn giảm tín tối đa"*, trong toàn bộ `inc/eligibility.php`, `inc/eligibility-rules.php` và `template-parts/eligibility/results.php`, **không có bất kỳ một dòng code nào** tính toán số tín chỉ được miễn giảm hay rút ngắn thời gian đào tạo dựa trên ngành đã học.
4. **Xung đột kiến trúc giữa SQL Pre-filter và In-Memory Scoring:**
   - Truy vấn SQL (`tax_query`) lọc cứng cơ sở đào tạo (`campus`) trước khi duyệt, khiến nhánh code hỗ trợ chương trình học Online (dòng 470) **không bao giờ có thể chạy được** nếu người dùng chọn cơ sở địa phương.
5. **Cơ chế gợi ý chương trình thay thế (Alternatives) gợi ý sai ngành nghiêm trọng:**
   - Khi ngành mong muốn không có chương trình, hệ thống lấy 5 chương trình ngẫu nhiên bị loại (thuộc các ngành hoàn toàn không liên quan như Y Dược, Ngôn ngữ, Sư phạm) đưa vào mục *"💡 Gợi ý chương trình liên quan"*, kèm nhãn *"✗ Không khớp ngành đào tạo mong muốn"*.
6. **Chương trình không gắn ngành (Orphan Major) bị lọt lưới thành "Phù hợp":**
   - Nếu quản trị viên quên gán trường quan hệ `major_relationship` cho chương trình, chương trình đó sẽ vượt qua kiểm tra ngành và được gợi ý cho tất cả mọi người dùng.

---

## 2. PHÂN TÍCH CHI TIẾT CÔNG THỨC TOÁN HỌC & TRỌNG SỐ TÍNH ĐIỂM

### 2.1. Bảng trọng số theo thiết kế vs Thực tế thực thi

Trong file `inc/eligibility-rules.php` (dòng 99-108):
```php
99:  function ltdh_elig_get_scoring_weights() {
100: 	return [
101: 		'major_match'        => 30,  // Max 30 points
102: 		'major_related'      => 15,  // If related major
103: 		'graduation_recent'  => 10,  // Max 10 points
104: 		'budget_match'       => 20,  // Max 20 points
105: 		'campus_match'       => 10,  // Max 10 points
106: 		'schedule_match'     => 5,   // Max 5 points
107: 	];
108: }
```

| Tiêu chí | Trọng số lý thuyết | Trọng số thực tế trong code | Dòng code trong `inc/eligibility.php` | Tình trạng kiểm định |
| :--- | :---: | :---: | :---: | :--- |
| **1. Khớp ngành mong muốn (`major_match`)** | 30 | 30 | Dòng 417 | Thực thi đúng nếu khớp chính xác ID |
| **2. Khớp / Liên quan ngành cũ (`major_related`)** | 15 | 15 | Dòng 435, 438 | Khớp đúng ngành và ngành gần nhận cùng 15 điểm |
| **3. Năm tốt nghiệp gần đây (`graduation_recent`)** | 10 | **0** | **Không có trong code** | **BỊ BỎ QUÊN HOÀN TOÀN** (0 lần xuất hiện) |
| **4. Phù hợp ngân sách (`budget_match`)** | 20 | 0 hoặc 20 | Dòng 490, 493, 502 | Bị ẩn khỏi form frontend; công thức tính chi phí lỗi |
| **5. Cơ sở / Địa điểm học (`campus_match`)** | 10 | 10 (hoặc 5) | Dòng 468, 471 | Nhánh 5 điểm (Online) bị SQL Pre-filter triệt tiêu |
| **6. Hệ đào tạo / Lịch học (`schedule_match`)** | 5 | 5 | Dòng 459 | Tên là schedule nhưng lại tính cho `training_type` |
| **TỔNG CỘNG ĐIỂM TỐI ĐA ĐẠT ĐƯỢC** | **90 (Lỗi thiết kế)** | **80 (Code) / 60 (UI)** | Dòng 507 | **Không thể chạm mốc 100 điểm** |

### 2.2. Chi tiết từng bước tính điểm trong `inc/eligibility.php`

#### Bước 0: Khởi tạo
- Dòng 385: `$match_score = 0;`
- Dòng 384: `$hard_fail = false;`

#### Bước 1: Điều kiện tiên quyết — Trình độ học vấn tối thiểu (Hard Filter)
- Dòng 387-403: So sánh cấp bậc học vấn của người dùng với trường ACF `elig_min_education` của chương trình dựa trên thang thứ bậc tại `ltdh_elig_get_education_hierarchy()`:
  * `thap-phan` (THPT) = 1
  * `trung-cap` = 2
  * `cao-dang` = 3
  * `dai-hoc` = 4
  * `thac-si` = 5
- Nếu `$user_level < $min_level`: Đánh dấu `$hard_fail = true`, `$preliminary_status = 'not_compatible'`, không cộng điểm.
- **Lưu ý:** Bước này không cộng điểm thưởng, chỉ đóng vai trò bộ lọc loại trừ.

#### Bước 2: Khớp ngành mong muốn (`major_match`)
- Dòng 415-424:
```php
415: if ( ! empty( $input['desired_major'] ) && $prog_major_id ) {
416: 	if ( (int) $input['desired_major'] === $prog_major_id ) {
417: 		$match_score += $weights['major_match']; // +30 điểm
418: 		$match_reasons[] = 'Đúng ngành bạn mong muốn học.';
419: 	} else {
420: 		$preliminary_status = 'not_compatible';
421: 		$mismatch_reasons[] = 'Không khớp ngành đào tạo mong muốn.';
422: 		$hard_fail = true;
423: 	}
424: }
```
- Nếu khớp: Cộng **+30 điểm**.
- Nếu khác: Đánh dấu `$hard_fail = true;`.

#### Bước 3: Quan hệ giữa ngành cũ đã tốt nghiệp và ngành của chương trình (`major_related`)
- Dòng 427-447:
```php
434: if ( $prog_major_id && (int) $input['major_id'] === $prog_major_id ) {
435: 	$match_score += $weights['major_related']; // +15 điểm
436: 	$match_reasons[] = 'Chuyên ngành muốn học trùng khớp với ngành bạn đã tốt nghiệp.';
437: } elseif ( $prog_major_id && ltdh_elig_are_majors_related( $input['major_id'], $prog_major_id ) ) {
438: 	$match_score += $weights['major_related']; // +15 điểm
439: 	$match_reasons[] = 'Ngành bạn đã tốt nghiệp có liên quan mật thiết với ngành muốn học.';
440: } else {
441: 	if ( $preliminary_status !== 'not_compatible' ) {
442: 		$preliminary_status = 'needs_verification';
443: 	}
444: 	$verification_items[] = 'Ngành bạn muốn học khác với ngành đã tốt nghiệp...';
445: }
```
- Nếu học đúng ngành cũ: Cộng **+15 điểm**.
- Nếu học ngành gần (kiểm tra qua ACF `major_related` hoặc slug mapping fallback tại `ltdh_elig_get_major_relationships()`): Cộng **+15 điểm**.
- **Khiếm khuyết:** Điểm cộng cho người học "đúng ngành 100%" bằng hệt điểm người học "ngành gần" (đều là 15 điểm), không phản ánh lợi thế chuyển tiếp của người học đúng ngành.

#### Bước 4: Kiểm tra hệ đào tạo tương thích (`schedule_match`)
- Dòng 449-462:
```php
452: $allowed_types = $compatibility[ $user_edu ] ?? [];
453: if ( ! in_array( $input['training_type'], $allowed_types, true ) ) {
454: 	// Cần xác minh...
455: } else {
456: 	$match_score += $weights['schedule_match']; // +5 điểm
457: }
```
- **Khiếm khuyết ngữ nghĩa:** Khóa trọng số đặt tên là `schedule_match` (vốn ám chỉ lịch học buổi tối / cuối tuần / ban ngày theo định nghĩa tại dòng 87-94), nhưng code thực thi lại dùng để chấm điểm cho tính tương thích của **hệ đào tạo** (`training_type`). Trường `schedule` của chương trình hoàn toàn không được đối chiếu!

#### Bước 5: Kiểm tra cơ sở đào tạo (`campus_match`)
- Dòng 464-478:
```php
467: if ( in_array( $input['campus'], $all_campuses, true ) ) {
468: 	$match_score += $weights['campus_match']; // +10 điểm
469: } elseif ( in_array( 'online', $all_campuses, true ) ) {
470: 	$match_score += (int) ( $weights['campus_match'] * 0.5 ); // +5 điểm
471: } else {
472: 	$preliminary_status = 'not_compatible';
473: 	$hard_fail = true;
474: }
```
- Khớp cơ sở chọn: Cộng **+10 điểm**.
- Hỗ trợ Online: Cộng **+5 điểm**.
- Không khớp: Bị loại (`$hard_fail = true`).

#### Bước 6: Đánh giá ngân sách học phí (`budget_match`)
- Dòng 480-505:
```php
484: $tuition_num = ltdh_elig_parse_tuition( $tuition_str );
485: $duration_num = ltdh_elig_parse_duration( get_post_meta( $program_id, 'duration', true ) ?: '' );
486: $total_cost = $tuition_num * 120 * $duration_num;
...
490: 	$match_score += $weights['budget_match']; // +20 điểm nếu <= budget['max']
493: 	$match_score += (int) ( $weights['budget_match'] * 0.5 ); // +10 điểm nếu <= budget['max'] * 1.2
...
502: 	$match_score += $weights['budget_match']; // +20 điểm (Fallback nếu chi phí = 0 hoặc gói trên 50 triệu)
```

#### Bước 7: Giới hạn trần điểm số (Cap)
- Dòng 507:
```php
507: $match_score = min( $match_score, 100 );
```
- Điểm được chặn trên tại 100. Tuy nhiên, do tổng các điểm thành phần chỉ tối đa:
  $$\text{Max Score} = 30 (\text{major}) + 15 (\text{related}) + 5 (\text{training}) + 10 (\text{campus}) + 20 (\text{budget}) = 80 \text{ điểm}$$
  Và trong luồng người dùng thực tế tại `template-parts/eligibility/wizard.php`, trường `budget` không hề có trong biểu mẫu (xem phân tích mục 2.3), nên:
  $$\text{Max Score Thực tế (UI)} = 30 + 15 + 5 + 10 + 0 = 60 \text{ điểm}$$
  Kết quả là trong bảng điều khiển Admin (`inc/eligibility.php:1638`) và trên giao diện, **không có bất kỳ người dùng nào đạt trên 60% độ tương thích**!

---

## 3. PHÂN TÍCH THUẬT TOÁN XẾP HẠNG & GỢI Ý CHƯƠNG TRÌNH

### 3.1. Truy vấn tiền lọc (Pre-filter Query) tại Step 1 (`inc/eligibility.php:329-374`)
```php
330: $query_args = [
331: 	'post_type'      => LTDH_CPT_PROGRAM,
332: 	'post_status'    => 'publish',
333: 	'posts_per_page' => 100, // Limit to prevent memory exhaustion
334: 	'fields'         => 'ids',
335: 	'meta_query'     => [
336: 		[
337: 			'key'     => LTDH_META_ADMISSION_STATUS,
338: 			'value'   => LTDH_STATUS_OPEN,
339: 			'compare' => '=',
340: 		],
341: 	],
342: ];
347: $query_args['tax_query'] = [ ... ]; // Lọc theo training_type nếu có
360: $query_args['tax_query'][] = [ ... ]; // Lọc theo campus nếu có
```

#### Các khuyết tật chí mạng của bộ lọc tiền cấp SQL:
1. **Không lọc `desired_major` ở tầng SQL:**
   - Bộ lọc lấy tối đa 100 chương trình đang mở tuyển sinh bất kỳ. Nếu hệ thống có 250 chương trình thuộc nhiều ngành khác nhau, 100 chương trình đầu tiên được load có thể không chứa chương trình thuộc ngành người dùng muốn tìm.
2. **Triệt tiêu logic Fallback Online của Campus:**
   - Dòng 360 ép điều kiện SQL `taxonomy: campus, terms: $input['campus']`.
   - Kết quả: Tất cả các chương trình trực tuyến (`campus = 'online'`) mà không gắn slug của tỉnh thành đó sẽ **bị SQL loại bỏ ngay từ đầu**.
   - Dẫn đến dòng 470 (`elseif in_array('online', $all_campuses)`) trong vòng lặp in-memory **vô hiệu hóa 100%**.
3. **Chỉ lấy trạng thái tuyển sinh `open`:**
   - Dòng 338 lọc `LTDH_STATUS_OPEN`. Các chương trình "Sắp mở" (`sap-mo` hoặc `incoming`) có thể nhận hồ sơ đợt tiếp theo bị loại bỏ hoàn toàn, làm giảm số lượng kết quả hữu ích cho người học.

### 3.2. Thuật toán phân loại Hợp lệ / Loại trừ (Eligible vs Rejected)
- Dòng 543-547:
```php
543: if ( $preliminary_status === 'not_compatible' || $hard_fail ) {
544: 	$rejected[] = $program_data;
545: } else {
546: 	$eligible[] = $program_data;
547: }
```
- Các chương trình có trạng thái `needs_verification` (ví dụ học trái ngành cần học chuyển đổi, hoặc học phí vượt ngân sách) vẫn được xếp vào mảng `$eligible[]`. Đây là thiết kế hợp lý nhằm giữ chân người dùng (giữ phễu tư vấn).

### 3.3. Thuật toán Xếp hạng & Cắt lát (Sorting & Slicing)
- Dòng 551-555:
```php
551: usort( $eligible, function( $a, $b ) {
552: 	return $b['score'] <=> $a['score'];
553: });
555: $eligible_slice = array_slice( $eligible, 0, 10 );
```

#### Đánh giá thuật toán sắp xếp:
- **Thiếu Tie-breaker (Tiêu chí phụ giải quyết đồng điểm):**
  * Hầu hết các chương trình cùng ngành sẽ có điểm số bằng nhau (ví dụ: cùng 60 điểm hoặc cùng 75 điểm).
  * Hàm `usort` hiện tại chỉ so sánh duy nhất trường `score`. Khi hai chương trình bằng điểm, thứ tự phụ thuộc hoàn toàn vào thứ tự ID trả về từ MySQL, mang tính ngẫu nhiên.
  * Thiếu hoàn toàn các tiêu chí phụ quan trọng: Uy tín/Xếp hạng trường, Học phí thấp hơn, Thời hạn nhận hồ sơ gần nhất, hoặc Tỷ lệ việc làm sau tốt nghiệp.

### 3.4. Thuật toán Gợi ý thay thế (`$alternatives`) — Lỗi logic hiển thị
- Dòng 558-568:
```php
558: $alternatives = [];
559: foreach ( $rejected as $r ) {
560: 	if ( count( $alternatives ) >= 5 ) {
561: 		break;
562: 	}
563: 	$alternatives[] = [
564: 		'program_id' => $r['program_id'],
565: 		'title'      => $r['title'],
566: 		'reason'     => ! empty( $r['mismatch_reasons'] ) ? $r['mismatch_reasons'][0] : 'Chưa phù hợp điều kiện',
567: 	];
568: }
```

#### Xung đột cấu trúc dữ liệu giữa Backend PHP và Frontend JS:
- Trong PHP (`inc/eligibility.php:563`), mỗi phần tử của `$alternatives` chỉ có 3 keys: `program_id`, `title`, `reason`.
- Nhưng ở Frontend (`assets/js/eligibility.js:405`), code gọi hàm `renderProgramCard(prog, idx)`:
  ```javascript
  405: data.alternatives.forEach(function (prog, idx) {
  406:     altList.appendChild(renderProgramCard(prog, idx));
  407: });
  ```
  Hàm `renderProgramCard` đòi hỏi các trường: `prog.preliminary_status`, `prog.score`, `prog.school`, `prog.tuition_fee`, `prog.duration`, `prog.campus_info`, `prog.mismatch_reasons`.
- Vì PHP không trả về các trường này, nên khi render card thay thế:
  * Điểm `score` bị `undefined` -> Hiển thị nhãn mặc định.
  * Trường học `school` bị `undefined` -> Không có logo, không có tên trường.
  * Thông tin học phí và thời gian đào tạo hiển thị rỗng (`—`).
  * Đặc biệt: Lý do không phù hợp (`prog.mismatch_reasons`) bị `undefined` vì PHP trả về key `'reason'` chứ không phải `'mismatch_reasons'`.

---

## 4. PHÂN TÍCH LOGIC ƯỚC TÍNH MIỄN GIẢM TÍN CHỈ & THỜI GIAN ĐÀO TẠO

### 4.1. Thực trạng kiểm tra mã nguồn
Kết quả kiểm tra toàn diện trên toàn bộ repository:
- **`inc/eligibility.php`**: Hoàn toàn **không có code tính miễn giảm tín chỉ**. Từ khóa `"tín chỉ"` chỉ xuất hiện đúng 1 lần tại dòng 585 trong hàm lọc chuỗi `ltdh_elig_parse_tuition()`.
- **`inc/eligibility-rules.php`**: Không có bảng tỷ lệ miễn giảm hoặc quy tắc chuyển đổi tín chỉ giữa các khối ngành.
- **`template-parts/eligibility/results.php`**: Không có component hoặc thẻ HTML nào hiển thị số tín chỉ dự kiến được miễn giảm hay thời gian học đã được rút ngắn.
- **`assets/js/eligibility.js`**: Không có logic tính toán hay hiển thị tín chỉ miễn giảm.

### 4.2. Thời gian hoàn thành khóa học thực tế bị "đóng băng"
- Dòng 485 và dòng 538 `inc/eligibility.php`:
```php
485: $duration_num = ltdh_elig_parse_duration( get_post_meta( $program_id, 'duration', true ) ?: '' );
...
538: 'duration' => get_post_meta( $program_id, 'duration', true ) ?: '',
```
- Giá trị thời gian học (`duration`) chỉ là việc đọc chuỗi tĩnh từ Custom Field (ví dụ: `"1.5 - 2 năm"`).
- Nó **hoàn toàn không thay đổi** bất kể ứng viên:
  * Đã tốt nghiệp Cao đẳng đúng ngành (đáng lẽ được miễn 45-60 tín chỉ, học chỉ mất 1.5 năm).
  * Đã tốt nghiệp Cao đẳng khác ngành (phải học bổ sung 15-20 tín chỉ chuyển đổi, thời gian học kéo dài lên 2 - 2.5 năm).
  * Tốt nghiệp Đại học học Văn bằng 2 (được miễn toàn bộ khối kiến thức đại cương, học 1.5 - 2 năm).

### 4.3. Đối chiếu với Quy chế đào tạo của Bộ GD&ĐT (Thông tư 08/2021/TT-BGDĐT & Quyết định 18/2017/QĐ-TTg)
Theo quy định hiện hành về đào tạo liên thông và công nhận giá trị chuyển đổi kết quả học tập:
1. **Khối lượng học tập toàn khóa đại học:** Thường từ 120 đến 140 tín chỉ (đối với cử nhân 4 năm).
2. **Miễn giảm theo khối kiến thức:**
   - **Khối kiến thức giáo dục đại cương:** Triết học Mác - Lênin, Kinh tế chính trị, Chủ nghĩa xã hội khoa học, Lịch sử Đảng, Tư tưởng Hồ Chí Minh, Ngoại ngữ cơ bản, Tin học đại cương, Giáo dục thể chất, Giáo dục quốc phòng - an ninh (khoảng 20 - 30 tín chỉ). Người đã có bằng Cao đẳng hoặc Đại học được xét công nhận tương đương phần lớn các học phần này.
   - **Khối kiến thức cơ sở ngành:** Nếu liên thông đúng ngành hoặc ngành gần, người học được miễn các môn cơ sở ngành đã học ở trình độ cao đẳng có khối lượng và nội dung tương đương (từ 15 - 30 tín chỉ).
3. **Ước tính thực tế theo từng diện đối tượng:**
   - **Liên thông đúng ngành (Cao đẳng -> ĐH):** Miễn giảm **40 - 55 tín chỉ** (~35% - 40% chương trình). Số tín chỉ còn lại: **65 - 80 tín chỉ**. Thời gian đào tạo: **1.5 năm (3 - 4 học kỳ)**.
   - **Liên thông ngành gần (Cao đẳng -> ĐH):** Miễn giảm **25 - 40 tín chỉ**. Phải học bổ sung kiến thức (10 - 15 tín chỉ). Số tín chỉ còn lại: **80 - 95 tín chỉ**. Thời gian đào tạo: **2.0 năm (4 - 5 học kỳ)**.
   - **Liên thông trái ngành (Cao đẳng -> ĐH):** Miễn giảm **15 - 25 tín chỉ** (chủ yếu là đại cương). Số tín chỉ còn lại: **95 - 115 tín chỉ**. Thời gian đào tạo: **2.5 năm (5 - 6 học kỳ)**.
   - **Văn bằng 2 (Đã có bằng ĐH):** Miễn giảm **35 - 50 tín chỉ**. Thời gian đào tạo: **1.5 - 2.0 năm**.

Việc thiếu vắng mô hình tính toán này là khoảng trống nghiệp vụ lớn nhất của hệ thống, làm giảm giá trị tư vấn cốt lõi đối với ứng viên liên thông đại học.

---

## 5. PHÂN TÍCH RỦI RO & CÁC CA BIÊN NGUY HIỂM (EDGE CASES & HAZARDS)

### 5.1. Ca biên 1: Người dùng chọn ngành không có trường / chương trình đào tạo
- **Hiện tượng:** Người dùng chọn một chuyên ngành (ví dụ: *Công nghệ sinh học*, *Kỹ thuật hạt nhân*) hoặc kết hợp ngành + cơ sở đào tạo (ví dụ: *Thiết kế đồ họa* tại cơ sở *Thái Nguyên*) mà cơ sở dữ liệu không có chương trình nào đang mở.
- **Diễn tiến trong code:**
  * Dòng 368: `$candidate_ids` chỉ chứa các chương trình của ngành khác hoặc mảng rỗng.
  * Nếu có chương trình ngành khác: Dòng 422 đánh dấu `$hard_fail = true; mismatch_reasons[] = 'Không khớp ngành đào tạo mong muốn.'`.
  * Dòng 544: 100% chương trình bị đẩy vào `$rejected`.
  * Dòng 571: `$eligible_slice` rỗng, `$results['eligible'] = false`.
  * Dòng 559: Lấy 5 chương trình ngẫu nhiên trong `$rejected` đẩy vào `$alternatives`.
- **Hậu quả nghiệp vụ:**
  * Ứng viên chọn học *Công nghệ thông tin* nhưng nhận được danh sách gợi ý gồm: *Điều dưỡng*, *Sư phạm mầm non*, *Luật kinh tế*.
  * Trên thẻ chương trình hiển thị dòng cảnh báo: `✗ Không khớp ngành đào tạo mong muốn.` gây phản cảm và mất uy tín hệ thống.
  * Người dùng rơi vào ngõ cụt chuyển đổi (Dead-end UX), không có gợi ý chuyển sang hệ Từ xa (Online) hoặc các ngành công nghệ lân cận (Kỹ thuật phần mềm, Hệ thống thông tin).

### 5.2. Ca biên 2: Điểm số bị âm hoặc vượt ngưỡng 100%
- **Nguy cơ điểm vượt 100%:**
  * Code có cơ chế bảo vệ tại dòng 507: `$match_score = min( $match_score, 100 );`.
  * Tuy nhiên, như đã chứng minh ở mục 2, điểm số thực tế tối đa chỉ đạt 80 (hoặc 60 trên web), nên điểm không bao giờ vượt 100%, nhưng lại bị "lùn điểm" (Under-scoring), gây hiểu lầm cho người dùng là hồ sơ của họ luôn "kém tương thích".
- **Nguy cơ điểm bị âm (Negative Score):**
  * Trong code hiện tại, điểm chỉ cộng dồn nên không bị âm.
  * **Tuy nhiên, không có hàm sàn `max(0, $match_score)`**.
  * Nếu sau này hệ thống áp dụng điểm trừ (Penalties) — ví dụ trừ điểm văn bằng quá hạn tốt nghiệp, trừ điểm ngành thuộc diện hạn chế chuyển tiếp — mà không có hàm sàn, điểm số có thể rơi vào giá trị âm. Cột database `top_score int(11)` sẽ lưu số âm và giao diện hiển thị -15% tương thích!

### 5.3. Ca biên 3: Nguy cơ chia cho 0 (Division by Zero Risks)
Các vị trí tiềm ẩn nguy cơ chia cho 0 trong PHP 8.1+ (gây Fatal Crash):
1. **Phân trang trong Admin:**
   - Dòng 1171 & 1482 `inc/eligibility.php`: `$total_pages = ceil( $total_items / $per_page );`.
   - Hiện tại `$per_page = 20`. Nhưng nếu sau này biến `$per_page` được lấy từ `get_option()` hoặc query parameter `$_GET['per_page']` mà trả về 0 hoặc rỗng, PHP 8 sẽ ném ra ngoại lệ `DivisionByZeroError`.
2. **Ước tính học phí & tỷ lệ hoàn thành:**
   - Khi triển khai tính toán chi phí trung bình: `$avg_tuition = $total_tuition / $total_credits;`. Nếu chương trình chưa nhập `tuition_total_credits` (mặc định = 0), code sẽ sập ngay lập tức.
   - Khi tính thời gian đào tạo: `$semesters = ceil($remaining_credits / $credits_per_semester);`. Nếu `$credits_per_semester` không được kiểm tra `> 0`, lỗi nghiêm trọng sẽ xảy ra.
3. **Chuẩn hóa tỷ lệ phần trăm:**
   - Nếu chuẩn hóa điểm theo công thức: `($score / $max_possible_score) * 100`, nếu `$max_possible_score == 0`, hệ thống sẽ sập.

### 5.4. Ca biên 4: Gợi ý chương trình không tương thích (Incompatible Program Recommendations)
1. **Lỗ hổng chương trình mồ côi (Orphan Major Vulnerability):**
   - Dòng 415: `if ( ! empty( $input['desired_major'] ) && $prog_major_id )`.
   - Nếu một bài đăng chương trình đào tạo quên không chọn `major_relationship` trong ACF, `$prog_major_id` sẽ bằng 0.
   - Khi đó, điều kiện `if` trên bị bỏ qua, biến `$hard_fail` **vẫn giữ nguyên là `false`**.
   - Chương trình này sẽ lọt qua tất cả các bộ lọc ngành và được đưa vào danh sách `$eligible[]` đề xuất cho ứng viên, dù ứng viên tìm ngành nào!
2. **Khuyến nghị chương trình bị loại vào mục Alternatives:**
   - Dòng 559 duyệt thẳng mảng `$rejected`. Nếu một chương trình bị loại do ứng viên chỉ có bằng THPT mà chương trình yêu cầu trình độ Đại học (học VB2), chương trình đó vẫn bị nhét vào `$alternatives` và gợi ý cho học sinh THPT!
3. **Bỏ qua điều kiện đặc thù ngành Sức khỏe & Sư phạm:**
   - Theo quy định của Bộ GD&ĐT, các ngành Sức khỏe (Y đa khoa, Dược, Điều dưỡng) và Sư phạm **nghiêm cấm tuyển sinh liên thông trái ngành**. Ngoài ra, điểm tốt nghiệp Cao đẳng phải đạt loại Khá trở lên hoặc phải có thâm niên công tác/chứng chỉ hành nghề.
   - Thuật toán hiện tại xử lý ngành Điều dưỡng, Dược y hệt như Quản trị kinh doanh, cho phép liên thông trái ngành (chỉ gán nhãn `needs_verification` thay vì chặn cứng `hard_fail`).

### 5.5. Ca biên 5: Lỗi tính toán ngân sách học phí đội lên hàng trăm lần
- Hãy xem xét dòng 483-487 `inc/eligibility.php`:
```php
483: $tuition_str = get_post_meta( $program_id, 'tuition_fee', true ) ?: '';
484: $tuition_num = ltdh_elig_parse_tuition( $tuition_str );
485: $duration_num = ltdh_elig_parse_duration( get_post_meta( $program_id, 'duration', true ) ?: '' );
486: $total_cost = $tuition_num * 120 * $duration_num;
```
- **Kịch bản thực tế 1:** Chương trình có học phí là `"450.000đ / tín chỉ"`, thời gian học `"1.5 năm"`.
  * `$tuition_num = 450000`.
  * `$duration_num = 1.5`.
  * `$total_cost = 450,000 * 120 * 1.5 = 81.000.000đ`.
  * Nhưng thực tế, 120 tín chỉ là **tổng toàn bộ khóa học**! Chi phí thực tế chỉ là $450.000 \times 120 = 54.000.000đ$. Việc nhân thêm 1.5 làm chi phí bị đội thêm 50%!
- **Kịch bản thực tế 2:** Chương trình có học phí tính theo học kỳ: `"12.000.000đ / học kỳ"`, thời gian học `"2 năm"`.
  * Hàm `ltdh_elig_parse_tuition()` loại bỏ chuỗi `'/học kỳ'`, trả về `$tuition_num = 12000000`.
  * `$duration_num = 2`.
  * `$total_cost = 12,000,000 * 120 * 2 = 2.880.000.000đ` (**2,88 TỶ ĐỒNG**)!
  * So sánh với ngân sách người dùng chọn: cao nhất là `tren-50-trieu` (dòng 65).
  * Chương trình này lập tức bị xếp vào diện vượt ngân sách, mất 20 điểm và bị đẩy trạng thái xuống `needs_verification`.

---

## 6. ĐỀ XUẤT CẢI TIẾN THUẬT TOÁN TÍNH ĐIỂM, XẾP HẠNG & MIỄN GIẢM TÍN CHỈ

Để khắc phục triệt để các vấn đề trên, nhóm kiểm định đề xuất kiến trúc thuật toán thế hệ mới 2 tầng (Two-tier Decision Engine): **Hard Filters Matrix** + **Multi-factor Soft Scoring Engine** kèm **Credit Exemption & Degree Acceleration Model**.

### 6.1. Mô hình toán học 2 tầng (Mathematical Architecture)

#### Tầng 1: Ma trận điều kiện tiên quyết (Hard Gates: $G \in \{0, 1\}$)
Chương trình chỉ được xem xét tính điểm nếu thỏa mãn tích logic của tất cả các cổng:
$$E_{\text{hard}} = G_{\text{edu}} \times G_{\text{major}} \times G_{\text{regulated}} \times G_{\text{campus\_mode}}$$

Trong đó:
1. $G_{\text{edu}} = 1$ nếu $L_{\text{user\_edu}} \ge L_{\text{prog\_min\_edu}}$ (Thứ bậc học vấn đạt yêu cầu tối thiểu).
2. $G_{\text{major}} = 1$ nếu:
   - Ngành mong muốn khớp chính xác chương trình: $\text{ID}_{\text{desired}} = \text{ID}_{\text{prog\_major}}$.
   - Hoặc chương trình thuộc nhóm ngành tương thích được nhà trường cho phép chuyển đổi.
3. $G_{\text{regulated}} = 0$ nếu thuộc khối ngành Y Dược / Sư phạm mà ứng viên tốt nghiệp trái ngành (Loại trừ bắt buộc theo quy chế Bộ GD&ĐT).
4. $G_{\text{campus\_mode}} = 1$ nếu chương trình có cơ sở tại địa phương ứng viên chọn **HOẶC** là hệ đào tạo Từ xa / Trực tuyến (`online`).

Nếu $E_{\text{hard}} = 0$, chương trình bị loại tuyệt đối khỏi danh sách gợi ý chính.

#### Tầng 2: Thuật toán tính điểm tương thích đa thành phần (Soft Scoring Formula)
Hệ thống tính điểm trên thang chuẩn 100 điểm với 5 nhóm trọng số cân bằng:

$$S_{\text{total}} = \sum_{i=1}^{5} W_i \cdot s_i \quad (\text{với } \sum W_i = 100, \, s_i \in [0, 1.0])$$

$$\boxed{S_{\text{total}} = 35 \cdot s_{\text{major}} + 20 \cdot s_{\text{mode}} + 15 \cdot s_{\text{campus}} + 15 \cdot s_{\text{budget}} + 15 \cdot s_{\text{acceleration}}}$$

### 6.2. Bảng phân rã trọng số chi tiết (Scoring Weight Breakdown Table)

| Thành phần | Trọng số ($W_i$) | Tiêu chí chi tiết | Hệ số thành phần ($s_i$) | Điểm đạt được |
| :--- | :---: | :--- | :---: | :---: |
| **1. Khớp nối học thuật & Ngành đào tạo** | **35 điểm** | • Đúng ngành đã tốt nghiệp (Liên thông cùng ngành 100%)<br>• Ngành gần (Có trong danh mục ngành đào tạo liên quan)<br>• Ngành khác nhưng được phép chuyển đổi (Phải học bổ sung) | $1.0$<br>$0.70$<br>$0.35$ | **35.0 đ**<br>24.5 đ<br>12.2 đ |
| **2. Hình thức & Hệ đào tạo** | **20 điểm** | • Khớp chính xác hệ đào tạo mong muốn (Từ xa / VLVH / CQ)<br>• Hệ Từ xa (E-Learning) khi người dùng chọn linh hoạt<br>• Khớp lịch học ưu tiên (Tối / Cuối tuần) | $1.0$<br>$0.85$<br>$0.70$ | **20.0 đ**<br>17.0 đ<br>14.0 đ |
| **3. Cơ sở & Địa điểm học** | **15 điểm** | • Có cơ sở trực tiếp tại tỉnh/thành người học chọn<br>• Có trạm đào tạo từ xa / điểm thi đặt tại địa phương<br>• Học 100% Online, thi trực tuyến linh hoạt | $1.0$<br>$0.85$<br>$0.75$ | **15.0 đ**<br>12.7 đ<br>11.2 đ |
| **4. Ngân sách & Học phí** | **15 điểm** | • Tổng học phí thực tế $\le$ Ngân sách tối đa của ứng viên<br>• Học phí vượt nhẹ trong khoảng $\le 120\%$ ngân sách<br>• Học phí vượt $> 120\%$ ngân sách hoặc chưa công bố | $1.0$<br>$0.50$<br>$0.20$ | **15.0 đ**<br>7.5 đ<br>3.0 đ |
| **5. Rút ngắn thời gian & Miễn giảm tín chỉ** | **15 điểm** | • Miễn giảm $\ge 40$ tín chỉ (Thời gian học chỉ $1.5$ năm)<br>• Miễn giảm $25 - 39$ tín chỉ (Thời gian học $2.0$ năm)<br>• Miễn giảm $15 - 24$ tín chỉ (Thời gian học $\ge 2.5$ năm) | $1.0$<br>$0.70$<br>$0.40$ | **15.0 đ**<br>10.5 đ<br>6.0 đ |
| **TỔNG CỘNG** | **100 điểm** | **Chuẩn hóa tối đa: 100 điểm — Tương thích tuyệt đối: 100%** | | **100 điểm** |

### 6.3. Thuật toán ước tính Miễn giảm tín chỉ & Rút ngắn lộ trình (Credit Exemption Model)

Công thức xác định số tín chỉ miễn giảm và thời gian đào tạo:

$$C_{\text{exempt}} = \text{round}\left( C_{\text{total}} \times K_{\text{edu}} \times K_{\text{align}} \right)$$

$$C_{\text{remain}} = C_{\text{total}} - C_{\text{exempt}} + C_{\text{bridge}}$$

$$T_{\text{years}} = \max\left( 1.5, \, \text{round}\left( \frac{C_{\text{remain}}}{k_{\text{credits\_per\_year}}}, 1 \right) \right)$$

Trong đó:
- $C_{\text{total}}$: Tổng số tín chỉ toàn khóa chuẩn của trường (Mặc định: 130 tín chỉ).
- $K_{\text{edu}}$ (Hệ số theo trình độ đầu vào):
  * Bằng Đại học thứ 1 (Học VB2): $K_{\text{edu}} = 0.38$
  * Bằng Cao đẳng chính quy/nghề: $K_{\text{edu}} = 0.35$
  * Bằng Trung cấp: $K_{\text{edu}} = 0.20$
  * Bằng THPT: $K_{\text{edu}} = 0.0$
- $K_{\text{align}}$ (Hệ số mức độ tương thích ngành):
  * Cùng chuyên ngành: $K_{\text{align}} = 1.0$ (Số môn bổ sung $C_{\text{bridge}} = 0$)
  * Ngành gần / liên quan: $K_{\text{align}} = 0.75$ ($C_{\text{bridge}} = 9 - 12$ tín chỉ)
  * Trái ngành được phép: $K_{\text{align}} = 0.40$ ($C_{\text{bridge}} = 15 - 21$ tín chỉ)
- $k_{\text{credits\_per\_year}}$: Năng lực tích lũy tín chỉ trung bình của người vừa học vừa làm (Chuẩn: 36 tín chỉ/năm).

### 6.4. Thuật toán Xếp hạng đa tiêu chí (Multi-criteria Program Ranking)
Khi sắp xếp các chương trình trong `$eligible`:
```php
usort( $eligible, function( $a, $b ) {
    // Tiêu chí 1: Điểm tương thích tổng hợp giảm dần
    if ( $b['score'] !== $a['score'] ) {
        return $b['score'] <=> $a['score'];
    }
    // Tiêu chí 2 (Tie-breaker): Ưu tiên chương trình đang nhận hồ sơ ngay (status dang-nhan)
    if ( ( $b['is_admitting_now'] ?? 0 ) !== ( $a['is_admitting_now'] ?? 0 ) ) {
        return ( $b['is_admitting_now'] ?? 0 ) <=> ( $a['is_admitting_now'] ?? 0 );
    }
    // Tiêu chí 3: Ưu tiên chương trình học phí tối ưu hơn
    if ( ( $a['normalized_cost'] ?? 0 ) !== ( $b['normalized_cost'] ?? 0 ) ) {
        return ( $a['normalized_cost'] ?? 0 ) <=> ( $b['normalized_cost'] ?? 0 );
    }
    // Tiêu chí 4: Ưu tiên trường đối tác VIP / Đánh giá cao
    return ( $b['school_priority'] ?? 0 ) <=> ( $a['school_priority'] ?? 0 );
});
```

### 6.5. Kiến trúc mã nguồn đề xuất (Sample PHP Architecture)

Dưới đây là thiết kế class xử lý tính điểm và miễn giảm tín chỉ hoàn chỉnh, đảm bảo an toàn tuyệt đối trước các lỗi chia cho 0, xử lý đa luồng học phí và trả về báo cáo chi tiết:

```php
<?php
/**
 * Class LTDH_Eligibility_Scoring_Engine
 * Engine tính điểm tương thích, gợi ý xếp hạng và ước tính miễn giảm tín chỉ.
 */
class LTDH_Eligibility_Scoring_Engine {

    const WEIGHT_MAJOR_ALIGNMENT = 35;
    const WEIGHT_TRAINING_MODE   = 20;
    const WEIGHT_CAMPUS_LOCATION = 15;
    const WEIGHT_BUDGET_MATCH    = 15;
    const WEIGHT_TIME_REDUCTION  = 15;

    /**
     * Chạy toàn bộ quy trình đánh giá hồ sơ ứng viên
     */
    public static function evaluate_candidate( array $input ): array {
        $user_edu       = sanitize_text_field( $input['education'] ?? 'cao-dang' );
        $user_major_id  = intval( $input['major_id'] ?? 0 );
        $desired_major  = intval( $input['desired_major'] ?? 0 );
        $training_type  = sanitize_text_field( $input['training_type'] ?? '' );
        $campus         = sanitize_text_field( $input['campus'] ?? '' );
        $budget_key     = sanitize_text_field( $input['budget'] ?? '' );

        // 1. Lấy danh sách ứng viên từ Database (Nới lỏng tax_query để không triệt tiêu Online)
        $candidates = self::query_candidate_programs( $desired_major, $training_type, $campus );

        $eligible_programs = [];
        $smart_alternatives = [];

        foreach ( $candidates as $program_id ) {
            $eval = self::evaluate_single_program( $program_id, $user_edu, $user_major_id, $desired_major, $training_type, $campus, $budget_key );

            if ( $eval['is_hard_pass'] ) {
                $eligible_programs[] = $eval['data'];
            } else {
                if ( $eval['can_be_alternative'] ) {
                    $smart_alternatives[] = $eval['data'];
                }
            }
        }

        // 2. Sắp xếp đa tiêu chí
        usort( $eligible_programs, function( $a, $b ) {
            if ( $b['score'] !== $a['score'] ) {
                return $b['score'] <=> $a['score'];
            }
            if ( ( $b['admitting_priority'] ?? 0 ) !== ( $a['admitting_priority'] ?? 0 ) ) {
                return ( $b['admitting_priority'] ?? 0 ) <=> ( $a['admitting_priority'] ?? 0 );
            }
            return ( $a['estimated_cost'] ?? 0 ) <=> ( $b['estimated_cost'] ?? 0 );
        });

        // 3. Fallback thông minh nếu không có kết quả khớp chính xác
        if ( empty( $eligible_programs ) && ! empty( $smart_alternatives ) ) {
            usort( $smart_alternatives, function( $a, $b ) {
                return $b['score'] <=> $a['score'];
            });
            $smart_alternatives = array_slice( $smart_alternatives, 0, 5 );
        }

        return [
            'success'          => true,
            'eligible'         => ! empty( $eligible_programs ),
            'programs'         => array_slice( $eligible_programs, 0, 10 ),
            'total_candidates' => count( $candidates ),
            'eligible_count'   => count( $eligible_programs ),
            'top_score'        => $eligible_programs[0]['score'] ?? 0,
            'alternatives'     => array_slice( $smart_alternatives, 0, 5 ),
        ];
    }

    /**
     * Đánh giá chi tiết 1 chương trình
     */
    private static function evaluate_single_program( int $prog_id, string $user_edu, int $user_major, int $desired_major, string $train_type, string $campus, string $budget_key ): array {
        $match_reasons      = [];
        $verification_items = [];
        $mismatch_reasons   = [];
        $hard_fail          = false;
        $score              = 0.0;

        $prog_major_id = intval( get_post_meta( $prog_id, 'major_relationship', true ) );
        if ( ! $prog_major_id ) {
            // Không rõ ngành của chương trình -> Loại trừ an toàn
            return [ 'is_hard_pass' => false, 'can_be_alternative' => false, 'data' => [] ];
        }

        // CỔNG 1: Trình độ tối thiểu
        $hierarchy = [ 'thap-phan' => 1, 'trung-cap' => 2, 'cao-dang' => 3, 'dai-hoc' => 4, 'thac-si' => 5 ];
        $min_edu   = get_post_meta( $prog_id, 'elig_min_education', true ) ?: 'thap-phan';
        $user_lv   = $hierarchy[ $user_edu ] ?? 3;
        $min_lv    = $hierarchy[ $min_edu ] ?? 1;

        if ( $user_lv < $min_lv ) {
            $hard_fail = true;
            $mismatch_reasons[] = 'Yêu cầu tối thiểu: ' . ltdh_elig_get_education_label( $min_edu );
            // Ứng viên không đủ trình độ học vấn KHÔNG ĐƯỢC làm alternative
            return [ 'is_hard_pass' => false, 'can_be_alternative' => false, 'data' => [] ];
        }

        // CỔNG 2 & ĐIỂM 1: Mức độ tương thích chuyên ngành
        $major_coef = 0.0;
        $align_type = 'different'; // 'same', 'related', 'different'

        if ( $desired_major > 0 && $prog_major_id === $desired_major ) {
            if ( $user_major > 0 && $user_major === $desired_major ) {
                $major_coef = 1.0;
                $align_type = 'same';
                $match_reasons[] = 'Học đúng chuyên ngành đã tốt nghiệp (Cùng ngành 100%).';
            } elseif ( $user_major > 0 && ltdh_elig_are_majors_related( $user_major, $prog_major_id ) ) {
                $major_coef = 0.70;
                $align_type = 'related';
                $match_reasons[] = 'Ngành mong muốn gần với ngành bạn đã có văn bằng.';
            } else {
                $major_coef = 0.35;
                $align_type = 'different';
                $verification_items[] = 'Liên thông trái ngành (Cần học bổ sung kiến thức chuyển đổi).';
            }
        } elseif ( $desired_major > 0 && $prog_major_id !== $desired_major ) {
            // Khác ngành mong muốn -> Đưa vào làm alternative nếu là ngành gần
            $is_related = ( $user_major > 0 && ltdh_elig_are_majors_related( $desired_major, $prog_major_id ) );
            return [
                'is_hard_pass'       => false,
                'can_be_alternative' => $is_related,
                'data'               => self::build_program_payload( $prog_id, 0, 'not_compatible', [], [], [ 'Ngành liên quan thay thế' ], $align_type, $user_edu )
            ];
        }

        $score += ( self::WEIGHT_MAJOR_ALIGNMENT * $major_coef );

        // ĐIỂM 2: Hình thức & Hệ đào tạo
        $prog_train_types = wp_get_post_terms( $prog_id, 'training_type', [ 'fields' => 'slugs' ] );
        if ( ! empty( $train_type ) ) {
            if ( in_array( $train_type, $prog_train_types, true ) ) {
                $score += self::WEIGHT_TRAINING_MODE * 1.0;
                $match_reasons[] = 'Đúng hệ đào tạo ' . ltdh_elig_get_training_label( $train_type );
            } elseif ( in_array( 'tu-xa', $prog_train_types, true ) ) {
                $score += self::WEIGHT_TRAINING_MODE * 0.85;
                $match_reasons[] = 'Có phương thức đào tạo Đại học Trực tuyến / Từ xa linh hoạt.';
            } else {
                $score += self::WEIGHT_TRAINING_MODE * 0.50;
            }
        } else {
            $score += self::WEIGHT_TRAINING_MODE * 0.90;
        }

        // ĐIỂM 3: Cơ sở & Địa điểm học
        $prog_campuses = wp_get_post_terms( $prog_id, 'campus', [ 'fields' => 'slugs' ] );
        if ( ! empty( $campus ) ) {
            if ( in_array( $campus, $prog_campuses, true ) ) {
                $score += self::WEIGHT_CAMPUS_LOCATION * 1.0;
                $match_reasons[] = 'Có cơ sở học tập tại ' . ltdh_elig_get_campus_label( $campus );
            } elseif ( in_array( 'online', $prog_campuses, true ) || in_array( 'tu-xa', $prog_train_types, true ) ) {
                $score += self::WEIGHT_CAMPUS_LOCATION * 0.80;
                $match_reasons[] = 'Hỗ trợ học và thi trực tuyến (Phù hợp mọi khu vực).';
            } else {
                $verification_items[] = 'Chương trình chưa có cơ sở trực tiếp tại địa phương bạn chọn.';
                $score += self::WEIGHT_CAMPUS_LOCATION * 0.30;
            }
        } else {
            $score += self::WEIGHT_CAMPUS_LOCATION * 1.0;
        }

        // ĐIỂM 4 & DỰ TOÁN: Học phí
        $cost_info = self::calculate_program_cost( $prog_id, $align_type, $user_edu );
        $budget_score = self::evaluate_budget( $cost_info['total_cost'], $budget_key );
        $score += ( self::WEIGHT_BUDGET_MATCH * $budget_score['ratio'] );
        if ( ! empty( $budget_score['reason'] ) ) {
            $match_reasons[] = $budget_score['reason'];
        }

        // ĐIỂM 5: Miễn giảm tín chỉ & Rút ngắn lộ trình
        $credit_info = self::estimate_credit_exemption( $user_edu, $align_type, $prog_id );
        $time_ratio  = ( $credit_info['exempted_credits'] >= 40 ) ? 1.0 : ( ( $credit_info['exempted_credits'] >= 25 ) ? 0.70 : 0.40 );
        $score += ( self::WEIGHT_TIME_REDUCTION * $time_ratio );
        $match_reasons[] = 'Ước tính miễn giảm ' . $credit_info['exempted_credits'] . ' tín chỉ (~' . $credit_info['saved_months'] . ' tháng học).';

        $final_score = intval( round( min( max( $score, 0 ), 100 ) ) );
        $status = ( $final_score >= 70 && empty( $verification_items ) ) ? 'compatible' : 'needs_verification';

        $payload = self::build_program_payload( $prog_id, $final_score, $status, $match_reasons, $verification_items, $mismatch_reasons, $align_type, $user_edu, $credit_info, $cost_info );

        return [
            'is_hard_pass'       => ! $hard_fail,
            'can_be_alternative' => false,
            'data'               => $payload,
        ];
    }

    /**
     * Ước tính số tín chỉ được miễn giảm và thời gian học thực tế
     */
    public static function estimate_credit_exemption( string $edu_level, string $align_type, int $prog_id ): array {
        $total_credits = intval( get_post_meta( $prog_id, 'tuition_total_credits', true ) );
        if ( $total_credits <= 0 ) {
            $total_credits = 130; // Mặc định cử nhân tiêu chuẩn
        }

        $k_edu = 0.0;
        if ( $edu_level === 'dai-hoc' ) {
            $k_edu = 0.38;
        } elseif ( $edu_level === 'cao-dang' ) {
            $k_edu = 0.35;
        } elseif ( $edu_level === 'trung-cap' ) {
            $k_edu = 0.20;
        }

        $k_align  = 0.40;
        $c_bridge = 18;
        if ( $align_type === 'same' ) {
            $k_align  = 1.0;
            $c_bridge = 0;
        } elseif ( $align_type === 'related' ) {
            $k_align  = 0.75;
            $c_bridge = 9;
        }

        $exempted = intval( round( $total_credits * $k_edu * $k_align ) );
        $remaining = max( 30, $total_credits - $exempted + $c_bridge );

        // Tính thời gian hoàn thành (36 tín chỉ / năm)
        $credits_per_year = 36;
        $years = max( 1.5, round( $remaining / $credits_per_year, 1 ) );
        $saved_months = max( 0, intval( round( ( $exempted / $credits_per_year ) * 12 ) ) );

        return [
            'total_credits'    => $total_credits,
            'exempted_credits' => $exempted,
            'bridge_credits'   => $c_bridge,
            'remain_credits'   => $remaining,
            'estimated_years'  => $years,
            'saved_months'     => $saved_months,
            'duration_label'   => $years . ' năm (' . ceil( $years * 2 ) . ' học kỳ)',
        ];
    }

    /**
     * Tính toán tổng học phí toàn khóa chuẩn xác theo ACF schema
     */
    private static function calculate_program_cost( int $prog_id, string $align_type, string $user_edu ): array {
        $amount = floatval( get_post_meta( $prog_id, 'tuition_amount', true ) );
        $unit   = get_post_meta( $prog_id, 'tuition_unit', true ) ?: 'tin-chi';
        
        $credit_info = self::estimate_credit_exemption( $user_edu, $align_type, $prog_id );
        $remaining_credits = $credit_info['remain_credits'];
        $years = $credit_info['estimated_years'];

        $total_cost = 0;
        if ( $amount > 0 ) {
            if ( $unit === 'tin-chi' ) {
                $total_cost = $amount * $remaining_credits;
            } elseif ( $unit === 'hoc-ky' ) {
                $total_cost = $amount * ceil( $years * 2 );
            } elseif ( $unit === 'nam' ) {
                $total_cost = $amount * $years;
            }
        } else {
            // Fallback sang parse chuỗi cũ
            $legacy_str = get_post_meta( $prog_id, 'tuition_fee', true ) ?: '';
            $parsed_num = ltdh_elig_parse_tuition( $legacy_str );
            if ( $parsed_num > 0 ) {
                $total_cost = ( $parsed_num < 2000000 ) ? ( $parsed_num * $remaining_credits ) : ( $parsed_num * ceil( $years * 2 ) );
            }
        }

        return [
            'total_cost'     => $total_cost,
            'formatted_cost' => $total_cost > 0 ? ( number_format( $total_cost, 0, ',', '.' ) . ' đ' ) : 'Liên hệ tư vấn',
        ];
    }

    /**
     * Đánh giá mức độ phù hợp ngân sách an toàn, tránh lỗi chia 0
     */
    private static function evaluate_budget( float $total_cost, string $budget_key ): array {
        if ( empty( $budget_key ) || $total_cost <= 0 ) {
            return [ 'ratio' => 1.0, 'reason' => 'Chi phí hợp lý.' ];
        }

        $ranges = [
            'duoi-20-trieu' => 20000000,
            '20-30-trieu'   => 30000000,
            '30-50-trieu'   => 50000000,
            'tren-50-trieu' => PHP_INT_MAX,
        ];

        $max_budget = $ranges[ $budget_key ] ?? PHP_INT_MAX;

        if ( $total_cost <= $max_budget ) {
            return [ 'ratio' => 1.0, 'reason' => 'Học phí hoàn toàn nằm trong ngân sách dự kiến của bạn.' ];
        } elseif ( $total_cost <= $max_budget * 1.25 ) {
            return [ 'ratio' => 0.50, 'reason' => 'Học phí vượt nhẹ ngân sách khoảng 10-25%.' ];
        } else {
            return [ 'ratio' => 0.20, 'reason' => '' ];
        }
    }

    /**
     * Đóng gói payload trả về đồng nhất cho frontend
     */
    private static function build_program_payload( int $prog_id, int $score, string $status, array $reasons, array $verif, array $mismatch, string $align, string $edu, array $credit = [], array $cost = [] ): array {
        $school_id = intval( get_post_meta( $prog_id, 'school_relationship', true ) );
        return [
            'program_id'         => $prog_id,
            'title'              => get_the_title( $prog_id ),
            'permalink'          => get_permalink( $prog_id ),
            'score'              => $score,
            'preliminary_status' => $status,
            'match_reasons'      => $reasons,
            'verification_items' => $verif,
            'mismatch_reasons'   => $mismatch,
            'school'             => $school_id ? [
                'id'    => $school_id,
                'title' => get_the_title( $school_id ),
                'logo'  => wp_get_attachment_image_url( ltdh_get_school_image_id( $school_id ), 'thumbnail' ) ?: '',
            ] : null,
            'estimated_credits'  => $credit,
            'estimated_cost'     => $cost['formatted_cost'] ?? '',
            'duration'           => $credit['duration_label'] ?? ( get_post_meta( $prog_id, 'duration', true ) ?: '1.5 - 2 năm' ),
            'tuition_fee'        => $cost['formatted_cost'] ?? 'Liên hệ',
            'schedule'           => get_post_meta( $prog_id, 'schedule', true ) ?: 'Linh hoạt',
            'campus_info'        => implode( ', ', wp_get_post_terms( $prog_id, 'campus', [ 'fields' => 'names' ] ) ) ?: 'Toàn quốc',
        ];
    }

    /**
     * Truy vấn thông minh SQL
     */
    private static function query_candidate_programs( int $desired_major, string $training_type, string $campus ): array {
        $args = [
            'post_type'      => LTDH_CPT_PROGRAM,
            'post_status'    => 'publish',
            'posts_per_page' => 150,
            'fields'         => 'ids',
        ];

        // Ưu tiên khớp ngành nếu có
        if ( $desired_major > 0 ) {
            $args['meta_query'][] = [
                'key'   => 'major_relationship',
                'value' => $desired_major,
            ];
        }

        $ids = get_posts( $args );

        // Nếu ngành đó không có chương trình nào, mở rộng tìm kiếm các ngành liên quan
        if ( empty( $ids ) && $desired_major > 0 ) {
            unset( $args['meta_query'] );
            $ids = get_posts( $args );
        }

        return $ids;
    }
}
```

---

## 7. BẢNG TỔNG HỢP KIỂM ĐỊNH THEO DÒNG MÃ NGUỒN (CODE CITATION AUDIT TABLE)

| File | Dòng code | Mô tả đoạn mã | Vấn đề / Khiếm khuyết kiểm định | Hướng khắc phục chuẩn |
| :--- | :--- | :--- | :--- | :--- |
| `inc/eligibility-rules.php` | 99-108 | `ltdh_elig_get_scoring_weights()` | Tổng trọng số chỉ bằng 90 điểm, thiếu 10 điểm để thành thang chuẩn 100 điểm. | Chuẩn hóa bảng trọng số 5 thành phần có tổng đúng 100 điểm. |
| `inc/eligibility.php` | 288 | `$valid_education = [ 'cao-dang' ];` | Khóa cứng chỉ cho phép Cao đẳng, từ chối THPT, Trung cấp và VB2 dù quy chế có hỗ trợ. | Mở rộng danh sách: `['thap-phan', 'trung-cap', 'cao-dang', 'dai-hoc']`. |
| `inc/eligibility.php` | 338 | `'value' => LTDH_STATUS_OPEN` | Chỉ lấy chương trình đang mở, bỏ qua các chương trình "Sắp mở" tuyển sinh đợt tiếp. | Chấp nhận cả trạng thái `open` và `sap-mo`. |
| `inc/eligibility.php` | 360-365 | `$query_args['tax_query'][] = campus` | SQL lọc cứng cơ sở địa phương làm triệt tiêu các chương trình Online toàn quốc. | Dùng điều kiện `OR`: hoặc cơ sở chọn, hoặc có gắn cơ sở `online`. |
| `inc/eligibility.php` | 415-424 | `$prog_major_id = get_field('major_relationship')` | Nếu chương trình không gắn `major_relationship`, `$hard_fail` không được bật, lọt lưới gợi ý. | Bổ sung kiểm tra: Nếu `$prog_major_id == 0`, coi như không hợp lệ và loại bỏ. |
| `inc/eligibility.php` | 435, 438 | `$match_score += $weights['major_related']` | Cùng ngành và ngành gần nhận cùng số điểm (+15), không tạo ưu thế cho người đúng ngành. | Phân tách: Cùng ngành +35 điểm, Ngành gần +25 điểm. |
| `inc/eligibility.php` | 459 | `$match_score += $weights['schedule_match']` | Trọng số tên `schedule_match` nhưng lại cộng điểm cho `training_type`. | Đổi tên trọng số thành `training_mode_match` và bổ sung chấm điểm lịch học thực tế. |
| `inc/eligibility.php` | 470 | `elseif in_array('online', $all_campuses)` | Nhánh code chết (Unreachable code) do bị SQL Pre-filter loại từ trước. | Sửa SQL Pre-filter để chương trình Online không bị chặn ở tầng database. |
| `inc/eligibility.php` | 486 | `$total_cost = $tuition_num * 120 * $duration_num;` | Nhân cả 120 tín chỉ lẫn số năm học, đội chi phí lên $1.5 - 2$ lần (hoặc hàng tỷ đồng nếu học phí theo kỳ). | Sử dụng trường `tuition_amount` và `tuition_unit` trong ACF để tính chính xác. |
| `inc/eligibility.php` | 507 | `$match_score = min( $match_score, 100 );` | Chỉ chặn trên 100, không chặn dưới 0. Thực tế điểm chỉ đạt tối đa 60/100 trên UI. | Dùng `round( min( max( $score, 0 ), 100 ) )` và mở rộng đủ trọng số. |
| `inc/eligibility.php` | 551-555 | `usort(...)` theo `$b['score'] <=> $a['score']` | Thiếu tie-breaker, thứ tự chương trình bằng điểm bị ngẫu nhiên. | Bổ sung tie-breaker: Đang nhận hồ sơ -> Học phí tối ưu -> Thứ hạng trường. |
| `inc/eligibility.php` | 563-567 | `$alternatives[]` chỉ có `program_id, title, reason` | Sai lệch cấu trúc với `renderProgramCard()` trong JS, làm vỡ hiển thị card gợi ý. | Trả về đầy đủ payload đồng nhất gồm `school`, `tuition`, `duration`, `mismatch_reasons`. |
| `template-parts/eligibility/wizard.php` | 26 | `<option value="cao-dang">` | Hardcode chỉ có duy nhất lựa chọn Cao đẳng. Ẩn hoàn toàn trường nhập `budget`. | Thêm các option THPT, Trung cấp, Đại học và trường ngân sách dự kiến. |
| `assets/js/eligibility.js` | 310-318 | `data.append('budget', '')` | Gửi chuỗi rỗng lên server cho trường budget và graduation year. | Thu thập giá trị ngân sách và năm tốt nghiệp từ form. |

---

## 8. KẾT LUẬN & KIẾN NGHỊ BÀN GIAO (CONCLUSIONS & NEXT STEPS)

1. **Kết luận chuyên môn:**
   - Module tính điểm và xét tuyển (`Eligibility Engine`) hiện tại đang ở trạng thái **chưa hoàn thiện về mặt toán học và nghiệp vụ**: thiếu 40% điểm số khả dụng trên UI, công thức học phí bị lỗi nghiêm trọng, và thiếu hụt 100% chức năng tính toán miễn giảm tín chỉ vốn là "linh hồn" của trang web Liên Thông Đại Học.
   - Các ca biên như chọn ngành không có trường, chương trình thiếu dữ liệu quan hệ ACF đang gây ra trải nghiệm người dùng tiêu cực (gợi ý sai ngành, hiển thị card rỗng dữ liệu).
2. **Kiến nghị thực hiện:**
   - Đưa bản phân tích chi tiết này vào tài liệu tổng thể `ELIGIBILITY_BUSINESS_AUDIT.md`.
   - Chuyển giao các đặc tả toán học và mã nguồn mẫu `LTDH_Eligibility_Scoring_Engine` cho nhóm triển khai (Implementation Team) để tích hợp vào `inc/eligibility.php` và `template-parts/eligibility/results.php` trong sprint tiếp theo.
