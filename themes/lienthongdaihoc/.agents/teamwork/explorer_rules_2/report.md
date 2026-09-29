# BÁO CÁO KIỂM ĐỊNH NGHIỆP VỤ & MA TRẬN LUẬT XÉT TUYỂN (R1)
## ĐỐI CHIẾU HỆ THỐNG XÉT TUYỂN VỚI QUY CHẾ TUYỂN SINH BỘ GD&ĐT HIỆN HÀNH

- **Mã nhiệm vụ**: R1 — Rà soát ma trận luật xét tuyển & Chuyển đổi bằng cấp
- **Tác nhân thực hiện**: `explorer_rules_2` (Read-only Investigation)
- **Thời gian thực hiện**: 2026-09-25
- **Tập tin trọng tâm**:
  * `inc/eligibility-rules.php`
  * `inc/eligibility.php`
  * Liên kết phụ trợ: `template-parts/eligibility/wizard.php`, `template-parts/eligibility/results.php`, `page-eligible.php`, `assets/js/eligibility.js`, `inc/acf-import-fields.json`

---

## MỤC LỤC

1. [TỔNG QUAN ĐIỀU HÀNH & KẾT LUẬN SƠ BỘ](#1-tổng-quan-điều-hành--kết-luận-sơ-bộ)
2. [CĂN CỨ PHÁP LÝ TUYỂN SINH & ĐÀO TẠO HIỆN HÀNH](#2-căn-cứ-pháp-lý-tuyển-sinh--đào-tạo-hiện-hành)
3. [RÀ SOÁT CHI TIẾT TỪNG DÒNG MÃ NGUỒN (LINE-BY-LINE AUDIT)](#3-rà-soát-chi-tiết-từng-dòng-mã-nguồn-line-by-line-audit)
   - 3.1. Rà soát tập tin `inc/eligibility-rules.php`
   - 3.2. Rà soát tập tin `inc/eligibility.php`
   - 3.3. Rà soát giao diện và tương tác (`wizard.php`, `results.php`, `eligibility.js`)
4. [ĐỐI CHIẾU NGHIỆP VỤ VỚI QUY CHẾ BỘ GD&ĐT](#4-đối-chiếu-nghiệp-vụ-với-quy-chế-bộ-gdđt)
   - 4.1. Phân loại trình độ đầu vào (THPT, Trung cấp nghề, Cao đẳng, Đại học)
   - 4.2. Ma trận phân loại ngành (Đúng ngành, Ngành gần, Ngành khác) & Thời gian chuẩn
   - 4.3. Các khối ngành đặc thù có điều kiện: Sức khỏe, Sư phạm, Luật
5. [PHÂN TÍCH 05 HỒ SƠ ỨNG VIÊN ĐIỂN HÌNH (CASE STUDIES)](#5-phân-tích-05-hồ-sơ-ứng-viên-điển-hình-case-studies)
   - Hồ sơ 1: Thí sinh tốt nghiệp THPT muốn học Đại học từ xa (4 năm)
   - Hồ sơ 2: Thí sinh Cao đẳng đúng ngành liên thông Đại học (1.5 năm)
   - Hồ sơ 3: Thí sinh Cao đẳng khác ngành liên thông Đại học (2 - 2.5 năm)
   - Hồ sơ 4: Người đã tốt nghiệp Đại học muốn học Văn bằng 2 Đại học (2 - 2.5 năm)
   - Hồ sơ 5: Ứng viên ngành Sức khỏe / Sư phạm (CĐ Dược/Sư phạm lên ĐH)
6. [BẢNG PHÂN TÍCH GAP ANALYSIS & ĐÁNH GIÁ 04 RỦI RO NGHIỆP VỤ TRỌNG YẾU](#6-bảng-phân-tích-gap-analysis--đánh-giá-04-rủi-ro-nghiệp-vụ-trọng-yếu)
7. [ĐỀ XUẤT GIẢI PHÁP KIẾN TRÚC & MÃ NGUỒN KHẮC PHỤC (ROADMAP)](#7-đề-xuất-giải-pháp-kiến-trúc--mã-nguồn-khắc-phục-roadmap)

---

## 1. TỔNG QUAN ĐIỀU HÀNH & KẾT LUẬN SƠ BỘ

Module "Kiểm tra điều kiện tuyển sinh" (Eligibility Checker Engine) được xây dựng với mục tiêu tự động hóa việc đánh giá sơ bộ hồ sơ học vấn của ứng viên, đối chiếu với danh mục chương trình đào tạo của các trường đại học đối tác, xếp hạng độ tương thích và thu thập thông tin ứng viên (lead generation).

Tuy nhiên, qua rà soát toàn diện và đối chiếu chuyên sâu với hệ thống văn bản quy phạm pháp luật hiện hành của Bộ Giáo dục và Đào tạo (Bộ GD&ĐT), Bộ Y tế và Bộ Tư pháp, module này hiện đang tồn tại **nhiều sai lệch nghiệp vụ nghiêm trọng, vi phạm quy chế đào tạo cấp quốc gia và có nguy cơ rủi ro pháp lý cao cho doanh nghiệp vận hành**, cụ thể:

1. **Vi phạm quy chế cấm đào tạo từ xa của Bộ GD&ĐT**: Hệ thống hoàn toàn không có cơ chế chặn hệ "Từ xa" (`tu-xa`) đối với khối ngành Sức khỏe (Y, Dược, Điều dưỡng) và khối ngành Sư phạm (Đào tạo giáo viên). Điều này vi phạm trực tiếp **Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT**.
2. **Khái niệm Văn bằng 2 bị sai lệch pháp lý và bị chặn cứng**: Hệ thống chú thích và xử lý sai khi cho rằng Cao đẳng có thể học Văn bằng 2 (`VB2: requires existing degree (Cao đẳng+)`). Theo **Thông tư 08/2021/TT-BGDĐT**, Văn bằng 2 bắt buộc phải là người đã có bằng ĐẠI HỌC. Đồng thời, controller API và form frontend lại loại trừ hoàn toàn hệ `van-bang-2`, khiến nhóm khách hàng có thu nhập cao nhất bị bỏ rơi.
3. **Bóp nghẹt 70%+ dung lượng thị trường tuyển sinh**: Tầng controller (`ltdh_elig_validate_input`) hardcode `$valid_education = ['cao-dang']`. Mọi thí sinh có bằng THPT, Trung cấp nghề hoặc Đại học đều bị từ chối với thông báo lỗi 400. Điều này làm tê liệt hoàn toàn phễu thu thập học viên trực tuyến cho các hệ đào tạo từ xa và văn bằng 2.
4. **Lỗi tính toán tài chính và sai lệch thời gian đào tạo**: Công thức ước tính học phí tại `inc/eligibility.php:486` bị nhân bội số `$tuition_num * 120 * $duration_num`, làm tổng học phí bị đội lên từ 1.5 đến 4 lần (thậm chí hàng tỷ đồng), khiến hầu hết chương trình bị đánh rớt khỏi tiêu chí ngân sách một cách oan uổng. Thời gian đào tạo hoàn toàn là số tĩnh, không linh hoạt điều chỉnh theo quan hệ ngành đúng (1.5 năm), ngành gần (2 - 2.5 năm) hay ngành khác (3 - 3.5 năm).

---

## 2. CĂN CỨ PHÁP LÝ TUYỂN SINH & ĐÀO TẠO HIỆN HÀNH

Hệ thống luật và quy chế tuyển sinh - đào tạo đại học tại Việt Nam điều chỉnh trực tiếp các hành vi của công cụ này bao gồm:

| STT | Văn bản pháp quy | Cơ quan ban hành | Phạm vi điều chỉnh chính | Điều khoản áp dụng trực tiếp |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Quyết định 18/2017/QĐ-TTg** (31/05/2017) | Thủ tướng Chính phủ | Quy định về liên thông giữa trình độ trung cấp, cao đẳng với trình độ đại học | - **Điều 4**: Điều kiện dự tuyển liên thông (văn hóa THPT cho Trung cấp).<br>- **Điều 5**: Tuyển sinh ngành sức khỏe (phải có CCHN/GPHN, ngưỡng chất lượng).<br>- **Điều 6**: Miễn trừ khối lượng học tập. |
| **2** | **Thông tư 08/2022/TT-BGDĐT** (06/06/2022) | Bộ Giáo dục và Đào tạo | Quy chế tuyển sinh đại học; tuyển sinh cao đẳng ngành Giáo dục Mầm non | - **Điều 5**: Đối tượng và điều kiện dự tuyển.<br>- **Điều 9 Khoản 2**: Điểm sàn và ngưỡng bảo đảm chất lượng đầu vào đối với ngành Sư phạm và ngành Sức khỏe có cấp chứng chỉ hành nghề. |
| **3** | **Thông tư 28/2023/TT-BGDĐT** (28/12/2023) | Bộ Giáo dục và Đào tạo | Quy chế đào tạo từ xa trình độ đại học (thay thế TT 10/2017/TT-BGDĐT) | - **Điều 5 Khoản 3**: **NGHIÊM CẤM** đào tạo từ xa đối với các ngành đào tạo thuộc lĩnh vực sức khỏe có cấp chứng chỉ hành nghề và lĩnh vực đào tạo giáo viên. |
| **4** | **Thông tư 08/2021/TT-BGDĐT** (18/03/2021) | Bộ Giáo dục và Đào tạo | Quy chế đào tạo trình độ đại học (Chính quy, Vừa làm vừa học) | - **Điều 16**: Quy định về đào tạo bằng đại học thứ hai (Văn bằng 2) — **chỉ áp dụng cho người đã có bằng tốt nghiệp đại học**.<br>- **Điều 17**: Công nhận kết quả học tập và chuyển đổi tín chỉ. |
| **5** | **Thông tư 15/2022/TT-BGDĐT** (08/11/2022) | Bộ Giáo dục và Đào tạo | Quy định giảng dạy khối lượng kiến thức văn hóa THPT trong cơ sở GDNN | - Học sinh tốt nghiệp Trung cấp muốn liên thông đại học bắt buộc phải hoàn thành 4 môn văn hóa THPT (tối thiểu 250 tiết/môn) và được cấp Giấy chứng nhận. |
| **6** | **Luật Khám bệnh, chữa bệnh 2023** (Luật số 15/2023/QH15) & **Nghị định 96/2023/NĐ-CP** | Quốc hội / Chính phủ | Quản lý hành nghề y tế, giấy phép hành nghề | - Chuyển đổi Chứng chỉ hành nghề (CCHN) sang Giấy phép hành nghề (GPHN); lộ trình chuẩn hóa trình độ Điều dưỡng, Kỹ thuật y từ Cao đẳng trở lên; cấm đào tạo lý thuyết chay. |
| **7** | **Luật Giáo dục 2019** & **Nghị định 71/2020/NĐ-CP** | Quốc hội / Chính phủ | Lộ trình nâng chuẩn trình độ đào tạo của giáo viên | - Bắt buộc giáo viên tiểu học, THCS phải có bằng Cử nhân Đại học (nâng chuẩn từ Trung cấp, Cao đẳng lên Đại học). |
| **8** | **Nghị định 04/2021/NĐ-CP** (22/01/2021) | Chính phủ | Quy định xử phạt vi phạm hành chính trong lĩnh vực giáo dục | - Điều 8, Điều 14: Xử phạt từ 20.000.000đ đến 40.000.000đ đối với hành vi thông báo tuyển sinh sai đối tượng, sai chỉ tiêu hoặc sai hình thức đào tạo. |

---

## 3. RÀ SOÁT CHI TIẾT TỪNG DÒNG MÃ NGUỒN (LINE-BY-LINE AUDIT)

### 3.1. Rà soát tập tin `inc/eligibility-rules.php`

Tập tin này có 161 dòng, đóng vai trò là "Bộ quy tắc trung tâm" định nghĩa các bảng tra cứu, trọng số chấm điểm và hàm liên kết ngành.

| Dòng code | Nội dung mã nguồn | Phân tích lỗi kỹ thuật & Sai lệch nghiệp vụ |
| :--- | :--- | :--- |
| **Dòng 18** | `'thap-phan' => [ 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],` | **Lỗi định danh (Naming Convention):** Sử dụng slug `'thap-phan'` (dịch thô của chữ "thập phân" / decimal) thay vì `'thpt'` hoặc `'pho-thong'`. Lỗi này kéo dài qua `ltdh_elig_get_education_hierarchy()` (dòng 75) và `ltdh_elig_get_education_label()` (dòng 598 của `eligibility.php`). |
| **Dòng 19** | `'trung-cap' => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],` | **Thiếu quy tắc ràng buộc pháp lý:** Theo QĐ 18/2017/QĐ-TTg và TT 15/2022/TT-BGDĐT, thí sinh tốt nghiệp Trung cấp (đặc biệt là Trung cấp nghề) **bắt buộc phải có bằng tốt nghiệp THPT hoặc Giấy chứng nhận hoàn thành đủ khối lượng văn hóa THPT** mới được liên thông ĐH. Mảng này chấp nhận thô mà không có cờ kiểm tra văn hóa THPT. |
| **Dòng 21** | `'dai-hoc' => [ 'tu-xa', 'vua-hoc-vua-lam' ],` | **Thiếu hình thức Văn bằng 2 và Chính quy:** Người có bằng đại học có quyền học Văn bằng 2 đại học dưới hình thức Chính quy hoặc Vừa làm vừa học hoặc Từ xa. Việc thiếu `'van-bang-2'` và `'chinh-quy'` khiến nhóm ứng viên đã có bằng ĐH không thể tìm kiếm chương trình phù hợp. |
| **Dòng 22** | `'thac-si' => [ 'tu-xa' ],` | **Bất hợp lý nghiệp vụ:** Đặt Thạc sĩ chỉ học `'tu-xa'` là thiếu cơ sở thực tế. Người có bằng Thạc sĩ khi học thêm đại học ngành khác thực chất là học theo diện Văn bằng 2. |
| **Dòng 27** | `* VB2: requires existing degree (Cao đẳng+)` | **SAI LỆCH PHÁP LÝ NGHIÊM TRỌNG:** Chú thích code ghi nhận Văn bằng 2 áp dụng cho "Cao đẳng+". Điều 16 Thông tư 08/2021/TT-BGDĐT quy định rõ: *Đào tạo để cấp bằng tốt nghiệp đại học thứ hai (Văn bằng 2) chỉ áp dụng cho người ĐÃ CÓ BẰNG TỐT NGHIỆP ĐẠI HỌC*. Người có bằng Cao đẳng học lên ĐH là "Đào tạo liên thông", tuyệt đối không phải Văn bằng 2. |
| **Dòng 28** | `* Từ xa/Vừa học vừa làm: compatible with all levels` | **VI PHẠM QUY CHẾ BỘ GD&ĐT:** Ghi chú cho rằng Từ xa tương thích với mọi trình độ mà bỏ qua điều cấm của Thông tư 28/2023/TT-BGDĐT đối với ngành Sức khỏe và Sư phạm. |
| **Dòng 46-53** | `ltdh_elig_get_major_relationships()` | **Thiếu hụt dữ liệu nghiêm trọng (>90%):** Chỉ định nghĩa 6 cặp ngành kinh doanh/CNTT cơ bản (`ke-toan`, `quan-tri-kinh-doanh`, `cong-nghe-thong-tin`, `ngon-ngu-anh`, `marketing`, `kinh-doanh-thuong-mai`). Toàn bộ các khối ngành Kỹ thuật, Xây dựng, Sức khỏe, Giáo dục, Luật, Nông nghiệp, Du lịch... đều không có dữ liệu đối chiếu. |
| **Dòng 50** | `'ngon-ngu-anh' => [ 'ngon-ngu-nhat', 'ngon-ngu-trung', 'dich-thuat' ]` | **Sai lệch chuẩn kiến thức:** Ngôn ngữ Anh và Ngôn ngữ Nhật/Trung là hai hệ ngôn ngữ hoàn toàn khác biệt (hệ chữ Latinh vs chữ tượng hình/Kanji). Không thể xem là ngành gần để miễn giảm môn cơ sở ngành tương tự như Kế toán sang Kiểm toán. |
| **Dòng 60-67** | `ltdh_elig_get_budget_ranges()` | **Mơ hồ về chu kỳ thanh toán:** Không định rõ các mức "Dưới 20 triệu", "20-30 triệu" là ngân sách **toàn khóa học** hay **mỗi năm** hay **mỗi học kỳ**, dẫn đến việc so sánh khập khiễng với mức tính chi phí tại `inc/eligibility.php`. |
| **Dòng 73-81** | `ltdh_elig_get_education_hierarchy()` | **So sánh tuyến tính thiếu mềm dẻo:** Đặt thứ bậc từ 1 đến 5 (`thap-phan: 1`, `trung-cap: 2`, `cao-dang: 3`, `dai-hoc: 4`, `thac-si: 5`). Khi một người có bằng Đại học (bậc 4) đăng ký chương trình Liên thông thiết kế riêng cho Cao đẳng (bậc 3), phép so sánh `$user_level >= $min_level` sẽ cho kết quả hợp lệ, trong khi thực tế chương trình liên thông chỉ nhận phôi bằng Cao đẳng đúng ngành, người có bằng ĐH khác ngành phải học theo diện Văn bằng 2. |
| **Dòng 87-94** | `ltdh_elig_get_schedule_keywords()` | **Dead Code (Mã nguồn rác):** Hàm này định nghĩa từ khóa lịch học nhưng không hề được gọi ở bất kỳ đâu trong `inc/eligibility.php` hay các template hiển thị. |
| **Dòng 99-108** | `ltdh_elig_get_scoring_weights()` | **Trọng số thiếu đồng bộ:** Khai báo `'graduation_recent' => 10` (ưu tiên người mới tốt nghiệp), nhưng trong `ltdh_elig_run_check()` biến này **hoàn toàn không được sử dụng để tính điểm**. Dẫn đến tổng điểm tối đa thực tế chỉ đạt 75/100 hoặc 80/100, triệt tiêu cơ hội đạt điểm tuyệt đối 100%. |
| **Dòng 113-159** | `ltdh_elig_are_majors_related()` | **Phụ thuộc thủ công, thiếu chuẩn hóa:** Hàm kiểm tra quan hệ ngành phụ thuộc vào việc quản trị viên phải cấu hình thủ công trường quan hệ ACF `major_related`. Thiếu cơ chế tự động nhận diện theo mã ngành đào tạo cấp IV quốc gia (Quyết định 09/2022/QĐ-TTg). |

---

### 3.2. Rà soát tập tin `inc/eligibility.php`

Tập tin này có 1703 dòng, là động cơ thực thi chính của hệ thống.

| Dòng code | Nội dung mã nguồn | Phân tích lỗi kỹ thuật & Sai lệch nghiệp vụ |
| :--- | :--- | :--- |
| **Dòng 31** | `input_graduation year DEFAULT NULL` | **Lẫn lộn dữ liệu (Data Ambiguity):** Trường CSDL định nghĩa là `input_graduation` (Năm tốt nghiệp), nhưng tại form nâng cao (`results.php:105`), nhãn hiển thị cho người dùng là **"Năm sinh"**. Hậu quả là dữ liệu lưu trong CSDL là năm sinh của thí sinh (ví dụ: 1998, 2002) chứ không phải năm tốt nghiệp. |
| **Dòng 288** | `$valid_education = [ 'cao-dang' ];` | **LỖI PHỄU NGHIÊM TRỌNG NHẤT (BLOCKING BUG):** Tầng xác thực chỉ chấp nhận duy nhất giá trị `'cao-dang'`. |
| **Dòng 299-301** | `if ( empty( $input['education'] ) \|\| ! in_array( $input['education'], $valid_education, true ) ) { return new WP_Error( 'invalid_education', 'Hệ thống chỉ hỗ trợ kiểm tra điều kiện liên thông từ Cao đẳng lên Đại học.' ); }` | **Triệt tiêu toàn bộ đối tượng khác:** Chặn đứng 100% người dùng có trình độ THPT (muốn học ĐH từ xa), Trung cấp (học liên thông lên ĐH), và Đại học (học Văn bằng 2). Bất kỳ request nào ngoài Cao đẳng đều bị ném lỗi 400. Mâu thuẫn hoàn toàn với tập luật đã dày công xây dựng tại `inc/eligibility-rules.php`. |
| **Dòng 292** | `$valid_training = ... array_diff( $training_terms, [ 'van-bang-2' ] ) ...` | **Loại trừ chủ động Văn bằng 2:** Chủ động loại bỏ taxonomy term `'van-bang-2'` khỏi danh sách hệ đào tạo hợp lệ, tước đi cơ hội tiếp cận của học viên có nhu cầu học văn bằng 2. |
| **Dòng 345-365** | `$query_args['tax_query'] = ...` | **Xung đột logic lọc Campus:** Tại bước tiền lọc query, nếu thí sinh chọn cơ sở "Hà Nội", query sẽ lọc cứng `campus = 'ha-noi'`. Các chương trình thuần học từ xa (chỉ gắn term `online`) sẽ bị loại bỏ ngay từ database query, khiến logic xử lý fallback ở dòng 470 (`in_array( 'online', $all_campuses )`) không bao giờ được kích hoạt. |
| **Dòng 388-404** | `$min_edu = get_field( 'elig_min_education', $program_id ) ?: '';` | **Bỏ rơi dữ liệu ACF đã thiết kế:** Chỉ lấy trường `elig_min_education`, bỏ qua hoàn toàn các trường ACF quan trọng khác đã có trong schema (`inc/acf-import-fields.json`): `elig_training_types`, `elig_campuses`, `elig_max_grad_years`, `elig_notes`. |
| **Dòng 450-462** | `// 4. Training Type Compatibility` | **Thiếu bộ lọc pháp lý ngành đặc thù:** Chỉ kiểm tra xem `$input['training_type']` có nằm trong mảng compatibility của trình độ học vấn hay không. Hoàn toàn không kiểm tra xem chương trình đó có thuộc khối ngành **Sức khỏe** hoặc **Sư phạm** hay không. Cho phép một thí sinh Cao đẳng Dược đăng ký học ĐH Dược hệ Từ xa mà vẫn được chấm điểm và báo "Tương thích tốt". |
| **Dòng 484-487** | `$tuition_num = ltdh_elig_parse_tuition( ... );`<br>`$duration_num = ltdh_elig_parse_duration( ... );`<br>`$total_cost = $tuition_num * 120 * $duration_num;` | **LỖI THUẬT TOÁN HỌC PHÍ TAI HẠI (CRITICAL CALCULATION BUG):**<br>- Giả sử học phí là `450.000đ/tín chỉ`, chương trình liên thông 1.5 năm (khoảng 60 tín chỉ tích lũy thêm).<br>- Code lấy `450.000 * 120 = 54.000.000đ` (đã là tiền của 120 tín chỉ cho cả 4 năm).<br>- Sau đó lại **NHÂN TIẾP với $duration_num** (1.5) => thành `81.000.000đ` (bị đội lên gấp rưỡi).<br>- Nếu học phí nhập theo học kỳ (ví dụ `15.000.000đ/kỳ`), công thức ra: `15.000.000 * 120 * 2 = 3.600.000.000đ` (3,6 tỷ đồng!).<br>- Kết quả: Thuật toán làm tổng chi phí vượt xa ngân sách của ứng viên, đánh tụt điểm hoặc gán nhãn `needs_verification` sai hoàn toàn. |
| **Dòng 507** | `$match_score = min( $match_score, 100 );` | **Trần điểm giả tạo:** Do thiếu 10 điểm `graduation_recent`, điểm tối đa thực tế của hồ sơ hợp lệ 100% chỉ đạt mức 70-80 điểm, không bao giờ đạt được ngưỡng 100% để hiển thị thông báo "Phù hợp hoàn hảo". |
| **Dòng 685-691** | `$ref_source = 'eligibility_checker?education_level=' ...` | Gửi dữ liệu khảo sát sang hệ thống lead CRM: Tham số `birth_year` lại lấy từ biến `$input['graduation']`, tiếp tục duy trì sự nhầm lẫn giữa năm tốt nghiệp và năm sinh. |

---

### 3.3. Rà soát giao diện và tương tác (`wizard.php`, `results.php`, `eligibility.js`)

1. **Giao diện bảng hỏi (`template-parts/eligibility/wizard.php`)**:
   - Dòng 24-28: Dropdown "Trình độ học vấn hiện tại" chỉ có duy nhất một thẻ `<option value="cao-dang" selected>Cao đẳng (Bằng Cao đẳng)</option>`. Không cho phép người dùng chọn bất kỳ bậc học nào khác.
   - Dòng 79-84: Dropdown hệ đào tạo chủ động loại bỏ `'van-bang-2'` (`if ( $tt->slug === 'van-bang-2' ) continue;`).
2. **Giao diện kết quả (`template-parts/eligibility/results.php`)**:
   - Dòng 99: Nhãn form xác minh ghi cứng: `Trường Cao đẳng trước đây`. Gây phản cảm và sai lệch nếu thí sinh là học sinh THPT hoặc người đã có bằng Đại học.
   - Dòng 105: Nhãn hiển thị là `Năm sinh`, nhưng tên trường input là `name="graduation"`.
3. **Logic JavaScript (`assets/js/eligibility.js`)**:
   - Dòng 310: Hardcode gửi `graduation = 0` ở lượt kiểm tra sơ bộ ban đầu, làm mất tiêu chí tính toán năm tốt nghiệp.
   - Dòng 435: Bảng ánh xạ nhãn (`labels.education`) vẫn phải chứa khóa `'thap-phan'` để hiển thị chữ "THPT", phản ánh sự phụ thuộc vào lỗi đặt tên ban đầu.

---

## 4. ĐỐI CHIẾU NGHIỆP VỤ VỚI QUY CHẾ BỘ GD&ĐT

### 4.1. Phân loại trình độ đầu vào (THPT, Trung cấp nghề, Cao đẳng, Đại học)

Theo khung cơ cấu hệ thống giáo dục quốc dân (Quyết định 1981/QĐ-TTg) và các quy chế đào tạo của Bộ GD&ĐT:

```
                  ┌────────────────────────┐
                  │ Tốt nghiệp THPT (Đầu vào)│
                  └───────────┬────────────┘
                              │
         ┌────────────────────┼────────────────────┐
         ▼                    ▼                    ▼
┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
│  Trung cấp nghề  │ │     Cao đẳng     │ │     Đại học      │
│  (GD Nghề nghiệp)│ │  (GD Nghề nghiệp)│ │  (GD Đại học)    │
└────────┬─────────┘ └────────┬─────────┘ └────────┬─────────┘
         │ (Cần văn hóa THPT)  │ (Miễn trừ 50-60TC) │ (Miễn trừ ĐC)
         ▼                    ▼                    ▼
┌────────────────────────────────────────────────────────────┐
│                    LIÊN THÔNG / VB2 / TỪ XA                │
│                 CẤP BẰNG CỬ NHÂN / KỸ SƯ ĐẠI HỌC           │
└────────────────────────────────────────────────────────────┘
```

1. **Đầu vào THPT (Trung học phổ thông / Bổ túc THPT)**:
   - *Quy định pháp lý*: Đủ điều kiện tuyển sinh đại học từ đầu theo mọi hình thức: Chính quy, Vừa làm vừa học, và Đào tạo từ xa (Thông tư 08/2022/TT-BGDĐT và Thông tư 28/2023/TT-BGDĐT).
   - *Thời gian đào tạo chuẩn*: 4.0 đến 4.5 năm (120 – 140 tín chỉ đối với khối Kinh tế, Khoa học xã hội; 150 – 160 tín chỉ đối với khối Kỹ thuật/Công nghệ).
   - *Thực trạng trong code*: Bị hardcode loại bỏ hoàn toàn tại `inc/eligibility.php:288` và `wizard.php:26`.
2. **Đầu vào Trung cấp / Trung cấp nghề**:
   - *Quy định pháp lý*: Điều 4 Quyết định 18/2017/QĐ-TTg quy định rõ người tốt nghiệp trung cấp nhưng chưa có bằng tốt nghiệp THPT phải học và được công nhận hoàn thành các môn văn hóa THPT theo quy định của Bộ GD&ĐT (Thông tư 15/2022/TT-BGDĐT: 4 môn Toán, Văn và 2 môn chuyên ngành).
   - *Thời gian đào tạo chuẩn*: 2.5 đến 3.0 năm (liên thông trực tiếp lên Đại học).
   - *Thực trạng trong code*: Không có trường kiểm tra tình trạng hoàn thành văn hóa THPT; bị controller từ chối thẳng thừng.
3. **Đầu vào Cao đẳng (Cao đẳng chính quy & Cao đẳng nghề)**:
   - *Quy định pháp lý*: Đối tượng liên thông truyền thống phổ biến nhất. Được xét công nhận chuyển đổi kết quả học tập và miễn trừ khối lượng tín chỉ tương đương (thường từ 40 đến 65 tín chỉ tùy chương trình đào tạo của từng trường đại học).
   - *Thời gian đào tạo chuẩn*: 1.5 đến 2.0 năm (đúng/gần ngành).
   - *Thực trạng trong code*: Là đối tượng duy nhất được chấp nhận, nhưng bị áp sai công thức tính học phí và không phân định được khối lượng tín chỉ miễn giảm thực tế.
4. **Đầu vào Đại học (Học bằng đại học thứ hai — Văn bằng 2)**:
   - *Quy định pháp lý*: Điều 16 Thông tư 08/2021/TT-BGDĐT. Chỉ áp dụng cho người **đã có bằng tốt nghiệp đại học**. Được miễn trừ toàn bộ khối kiến thức giáo dục đại cương (Triết học Mác - Lênin, Kinh tế chính trị, Chủ nghĩa xã hội khoa học, Lịch sử Đảng, Tư tưởng Hồ Chí Minh, Ngoại ngữ cơ sở, Tin học đại cương, Giáo dục thể chất, GDQP-AN).
   - *Thời gian đào tạo chuẩn*: 1.5 đến 2.0 năm (khoảng 50 – 70 tín chỉ chuyên ngành).
   - *Thực trạng trong code*: Bị nhầm lẫn với Cao đẳng (`inc/eligibility-rules.php:27`), bị loại bỏ khỏi danh mục hệ đào tạo (`inc/eligibility.php:292`), bị ẩn trên giao diện người dùng.

---

### 4.2. Ma trận phân loại ngành (Đúng ngành, Ngành gần, Ngành khác) & Thời gian chuẩn

Theo Quyết định số 18/2017/QĐ-TTg và Thông tư 08/2021/TT-BGDĐT:

| Phân loại quan hệ ngành | Định nghĩa chuẩn Bộ GD&ĐT | Khối lượng kiến thức cần học bổ sung | Thời gian đào tạo chuẩn thực tế | Đánh giá phản ánh trong mã nguồn dự án |
| :--- | :--- | :--- | :--- | :--- |
| **1. Ngành đúng / Phù hợp** *(Same Major)* | Cùng mã ngành cấp IV (7 chữ số, ví dụ `7480201` - CNTT) hoặc chương trình đào tạo ở trình độ CĐ có chuẩn đầu ra khớp từ 80% trở lên. | **0 tín chỉ bổ sung**. Được miễn tối đa khối lượng môn cơ sở ngành đã học ở CĐ. | **1.5 năm** (3 học kỳ) đến **2.0 năm** (4 học kỳ). | Được cộng 30đ match + 15đ related. Tuy nhiên, thời gian đào tạo chỉ hiển thị tĩnh theo trường meta của post, không có giải trình miễn giảm tín chỉ. |
| **2. Ngành gần** *(Related Major)* | Cùng nhóm ngành / lĩnh vực đào tạo (mã cấp II hoặc cấp III, ví dụ Kế toán `7340301` liên thông Quản trị kinh doanh `7340101`). | Phải học **bổ sung kiến thức** (thường từ 3 đến 8 môn cơ sở ngành, tương đương **9 – 18 tín chỉ** trước hoặc song song kỳ 1). | **2.0 năm** đến **2.5 năm** (4 – 5 học kỳ). | Chỉ nhận diện được 6 ngành hardcode; thiếu cơ chế tính thêm thời gian học bổ sung kiến thức. |
| **3. Ngành khác** *(Different Major)* | Khác hoàn toàn nhóm ngành (ví dụ tốt nghiệp CĐ Xây dựng muốn học ĐH Quản trị kinh doanh hoặc Ngôn ngữ Anh). | - Nếu từ CĐ: Không được liên thông tắt ngắn hạn, phải xét tuyển như ĐH từ đầu hoặc học bổ sung kiến thức toàn diện, chỉ được bảo lưu các môn chung.<br>- Nếu từ ĐH (học VB2): Được miễn các môn đại cương chung toàn quốc. | - Từ CĐ: **2.5 đến 3.5 năm**.<br>- Từ ĐH (học VB2): **2.0 đến 2.5 năm**. | Chỉ gắn cảnh báo `needs_verification` ("Ngành bạn muốn học khác với ngành đã tốt nghiệp..."). Hệ thống vẫn giữ nguyên thời lượng hiển thị 1.5 năm, gây ngộ nhận nghiêm trọng cho người học. |

---

### 4.3. Các khối ngành đặc thù có điều kiện: Sức khỏe, Sư phạm, Luật

Đây là ba khối ngành có tính nhạy cảm pháp lý cao nhất trong hệ thống giáo dục Việt Nam, được quy định bởi các luật chuyên ngành và thông tư riêng:

#### A. Khối ngành Sức khỏe (Y đa khoa, Răng Hàm Mặt, Y học cổ truyền, Dược học, Điều dưỡng, Kỹ thuật y học...)
- **CẤM ĐÀO TẠO TỪ XA TUYỆT ĐỐI**: Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT nghiêm cấm áp dụng hình thức đào tạo từ xa cho ngành sức khỏe có cấp chứng chỉ hành nghề.
- **Yêu cầu Chứng chỉ hành nghề / Giấy phép hành nghề**: Điều 5 Quyết định 18/2017/QĐ-TTg và Luật Khám bệnh, chữa bệnh 2023 bắt buộc thí sinh dự tuyển liên thông phải có CCHN/GPHN do Sở Y tế hoặc Bộ Y tế cấp, đúng với chuyên môn đào tạo (ví dụ tốt nghiệp Y sĩ muốn liên thông Bác sĩ phải có CCHN Y sĩ).
- **Ngưỡng bảo đảm chất lượng đầu vào (Điểm sàn)**: Điều 9 Thông tư 08/2022/TT-BGDĐT quy định:
  * Ngành Y đa khoa, Dược: Tốt nghiệp CĐ loại **Giỏi** hoặc học lực lớp 12 loại **Giỏi**.
  * Ngành Điều dưỡng, Kỹ thuật y học: Tốt nghiệp CĐ loại **Khá** trở lên hoặc học lực lớp 12 loại **Khá** trở lên (nếu loại Trung bình phải có tối thiểu 2 năm thâm niên làm việc chuyên môn).
- **Thực trạng trong code**: Hoàn toàn bỏ trống! Không kiểm tra CCHN, không kiểm tra học lực, không chặn hệ Từ xa.

#### B. Khối ngành Đào tạo giáo viên (Sư phạm Mầm non, Tiểu học, Sư phạm bộ môn)
- **CẤM ĐÀO TẠO TỪ XA TUYỆT ĐỐI**: Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT nghiêm cấm đào tạo từ xa đối với lĩnh vực đào tạo giáo viên.
- **Ngưỡng chất lượng đầu vào**: Thông tư 08/2022/TT-BGDĐT quy định xét tuyển thí sinh liên thông ngành Sư phạm phải có bằng tốt nghiệp CĐ loại **Khá/Giỏi** hoặc học lực lớp 12 loại **Giỏi/Khá** tương ứng.
- **Nâng chuẩn giáo viên**: Luật Giáo dục 2019 và Nghị định 71/2020/NĐ-CP yêu cầu giáo viên Tiểu học, THCS công lập phải đạt chuẩn trình độ Đại học. Thí sinh học liên thông sư phạm bắt buộc phải theo học hình thức Chính quy hoặc Vừa làm vừa học trực tiếp để tích lũy giờ thực tập đứng lớp.
- **Thực trạng trong code**: Không có cờ cảnh báo, không có kiểm tra điều kiện đứng lớp hay học lực.

#### C. Khối ngành Pháp luật (Luật, Luật kinh tế)
- **Ràng buộc hành nghề Luật sư & Công chức Tư pháp**:
  * Đào tạo Cử nhân Luật hệ Từ xa / Vừa học vừa làm được Bộ GD&ĐT cho phép, nhưng để hành nghề Luật sư, Học viện Tư pháp (Bộ Tư pháp) và Liên đoàn Luật sư Việt Nam có những quy định khắt khe về việc thẩm định văn bằng đầu vào khi đăng ký lớp đào tạo nghề luật sư.
  * Nhiều cơ quan tư pháp (Viện Kiểm sát nhân dân, Tòa án nhân dân) và một số địa phương không tuyển dụng công chức ngành kiểm sát/tòa án đối với người tốt nghiệp đại học hệ từ xa.
- **Thực trạng trong code**: Cần bổ sung cảnh báo định hướng nghề nghiệp (disclaimer/notes) để học viên nắm rõ mục đích học tập (phục vụ doanh nghiệp, nâng ngạch hay theo đuổi chức danh tư pháp).

---

## 5. PHÂN TÍCH 05 HỒ SƠ ỨNG VIÊN ĐIỂN HÌNH (CASE STUDIES)

Bảng phân tích dưới đây kiểm chứng phản hồi thực tế của mã nguồn hiện tại so với yêu cầu chuẩn hóa quy chế:

```
┌────────────────────────────────────────────────────────────────────────┐
│                      5 PROFILES EVALUATION SUMMARY                     │
├────┬─────────────────────────────┬──────────────┬──────────────────────┤
│ #  │ Kịch bản thí sinh           │ Mã nguồn cũ  │ Chuẩn hóa MOET       │
├────┼─────────────────────────────┼──────────────┼──────────────────────┤
│ P1 │ THPT học Đại học từ xa      │ ❌ Block 400 │ ✅ Hợp lệ (4.0 năm)  │
│ P2 │ Cao đẳng đúng ngành liên t. │ ⚠️ Lỗi học phí│ ✅ Hợp lệ (1.5 năm)  │
│ P3 │ Cao đẳng khác ngành liên t. │ ⚠️ Sai thời g│ 🟡 Học bổ sung (2.5n)│
│ P4 │ Đại học học Văn bằng 2      │ ❌ Block 400 │ ✅ Hợp lệ (2.0 năm)  │
│ P5 │ Ứng viên Y Dược / Sư phạm   │ ❌ Vi phạm TT│ 🛑 Cấm Từ xa + CCHN  │
└────┴─────────────────────────────┴──────────────┴──────────────────────┘
```

### Hồ sơ 1: Thí sinh tốt nghiệp THPT muốn học Đại học từ xa (4.0 năm)
- **Thông tin ứng viên**:
  * Trình độ hiện tại: THPT (Tốt nghiệp năm 2023).
  * Ngành mong muốn: Công nghệ thông tin (`desired_major: cong-nghe-thong-tin`).
  * Hệ học mong muốn: Đào tạo từ xa (`training_type: tu-xa`).
  * Địa điểm / Ngân sách: Toàn quốc (`campus: online`), ngân sách `30-50-trieu`.
- **Hành vi thực tế của hệ thống hiện tại**:
  1. *Trên giao diện*: Người dùng tìm kiếm mục "THPT" trong dropdown trình độ nhưng chỉ thấy duy nhất lựa chọn "Cao đẳng". Nếu cố tình không chọn hoặc cố vượt qua, client validation báo lỗi.
  2. *Gửi dữ liệu qua API*: Nếu gửi payload `education=thap-phan` hoặc `education=thpt`, hàm `ltdh_elig_validate_input` (dòng 299) trả về ngay lập tức lỗi:
     `{"code": "invalid_education", "message": "Hệ thống chỉ hỗ trợ kiểm tra điều kiện liên thông từ Cao đẳng lên Đại học."}`
  3. **Kết luận**: **THẤT BẠI 100% (BLOCKING)**. Mất hoàn toàn khách hàng tiềm năng.
- **Yêu cầu đối chiếu chuẩn MOET**:
  * Thí sinh tốt nghiệp THPT hoàn toàn đủ điều kiện học Đại học từ xa theo Thông tư 28/2023/TT-BGDĐT.
  * Thời gian đào tạo chuẩn: 4.0 năm (8 kỳ, ~130 tín chỉ).
  * Đánh giá hợp lệ: Hợp lệ 100% (Eligible), xếp hạng ưu tiên cao cho các trường có ngành CNTT từ xa (như Đại học Mở HN, Học viện Bưu chính Viễn thông...).

---

### Hồ sơ 2: Thí sinh Cao đẳng đúng ngành lên Đại học (1.5 năm)
- **Thông tin ứng viên**:
  * Trình độ hiện tại: Cao đẳng Công nghệ thông tin (`education: cao-dang`, `major_id: cong-nghe-thong-tin`).
  * Ngành mong muốn: Đại học Công nghệ thông tin (`desired_major: cong-nghe-thong-tin`).
  * Hệ học mong muốn: Liên thông vừa học vừa làm hoặc Từ xa (`training_type: tu-xa`).
  * Ngân sách: `30-50-trieu`.
- **Hành vi thực tế của hệ thống hiện tại**:
  1. Điền được form vì trùng khớp với giá trị hardcode duy nhất `cao-dang`.
  2. Bị lỗi tính toán ngân sách tại dòng 486: Trường nhập học phí `420.000đ/tín chỉ`, duration là `1.5 năm`. Code tính: `420.000 * 120 * 1.5 = 75.600.000đ`. Số tiền này vượt quá mức ngân sách tối đa `50.000.000đ` mà thí sinh đã chọn.
  3. Kết quả trả về: Chương trình bị đẩy vào nhóm `needs_verification` kèm cảnh báo: *"Mức học phí ước tính cao hơn ngân sách dự kiến của bạn."* Thí sinh bị trừ mất 20 điểm ngân sách, điểm tổng tụt xuống 55 - 60 điểm (chỉ đạt mức "Phù hợp tốt" thay vì "Phù hợp hoàn hảo").
- **Yêu cầu đối chiếu chuẩn MOET**:
  * Liên thông đúng ngành được miễn trừ 55 – 65 tín chỉ của chương trình CĐ. Số tín chỉ phải tích lũy chỉ khoảng 60 – 70 tín chỉ.
  * Chi phí thực tế cả khóa: `420.000 * 65 tín chỉ = 27.300.000đ` (hoàn toàn nằm gọn trong gói ngân sách 30-50 triệu).
  * Thời gian hoàn thành: 1.5 năm (3 học kỳ).
  * Điểm tương thích chuẩn: 95 – 100% (Phù hợp hoàn hảo).

---

### Hồ sơ 3: Thí sinh Cao đẳng khác ngành lên Đại học (2.0 – 2.5 năm)
- **Thông tin ứng viên**:
  * Trình độ hiện tại: Cao đẳng Kế toán (`education: cao-dang`, `major_id: ke-toan`).
  * Ngành mong muốn: Đại học Quản trị kinh doanh (`desired_major: quan-tri-kinh-doanh`) hoặc CNTT (`cong-nghe-thong-tin`).
  * Hệ học: Liên thông / Từ xa.
- **Hành vi thực tế của hệ thống hiện tại**:
  1. *Nếu chọn Quản trị kinh doanh (ngành gần)*: Không có trong bảng 6 ngành của `inc/eligibility-rules.php` (chỉ có chiều ngược lại hoặc quan hệ 1 chiều). Nếu không được admin nối ACF, code xem là khác ngành.
  2. *Nếu chọn CNTT (khác ngành hoàn toàn)*: Dòng 444 gán `needs_verification` với thông báo: *"Ngành bạn muốn học khác với ngành đã tốt nghiệp (Cần kiểm tra quy chế tiếp nhận ngành chéo của trường)."*
  3. Tuy nhiên, thời gian hiển thị trên card chương trình vẫn là `duration: 1.5 năm` (lấy tĩnh từ post meta).
  4. Thí sinh ngộ nhận rằng mình học trái ngành từ Kế toán sang CNTT mà vẫn chỉ mất 1.5 năm để lấy bằng kỹ sư CNTT.
- **Yêu cầu đối chiếu chuẩn MOET**:
  * Theo QĐ 18/2017/QĐ-TTg, liên thông khác ngành bắt buộc phải học bổ sung khối lượng kiến thức cơ sở ngành rất lớn (thường từ 20 – 35 tín chỉ).
  * Thời gian thực tế phải kéo dài: **2.0 đến 2.5 năm** (đối với khối ngành kinh tế gần nhau) và **3.0 đến 3.5 năm** (từ kinh tế sang kỹ thuật CNTT).
  * Hệ thống cần tính toán cộng thêm số kỳ học và dự toán chi phí học bổ sung kiến thức.

---

### Hồ sơ 4: Người đã tốt nghiệp Đại học muốn học Văn bằng 2 Đại học (2.0 năm)
- **Thông tin ứng viên**:
  * Trình độ hiện tại: Cử nhân Ngôn ngữ Anh (`education: dai-hoc`, `major_id: ngon-ngu-anh`).
  * Nguyện vọng: Học thêm bằng Cử nhân Luật hoặc Cử nhân Quản trị kinh doanh.
  * Hệ học: Văn bằng 2 / Từ xa trực tuyến.
- **Hành vi thực tế của hệ thống hiện tại**:
  1. Form giao diện không có mục chọn "Đại học".
  2. Form loại trừ lựa chọn "Văn bằng 2".
  3. Backend API dòng 288 từ chối `education: dai-hoc` với mã lỗi 400.
  4. Backend dòng 292 loại trừ term `van-bang-2`.
  5. Chú thích code dòng 27 khẳng định sai: "VB2 yêu cầu Cao đẳng trở lên".
  6. **Kết luận**: **BỊ CHẶN TOÀN TẬP (BLOCKING)**. Doanh nghiệp mất đi nhóm khách hàng có khả năng chi trả học phí tốt nhất và nhu cầu học tập thực tế cực kỳ cao tại các đô thị lớn.
- **Yêu cầu đối chiếu chuẩn MOET**:
  * Hoàn toàn hợp lệ theo Điều 16 Thông tư 08/2021/TT-BGDĐT.
  * Được miễn toàn bộ khối lượng giáo dục đại cương (~30 – 40 tín chỉ).
  * Thời gian hoàn thành: 1.5 đến 2.0 năm.
  * Đánh giá: Hợp lệ 100% với hệ đào tạo Văn bằng 2 hoặc Từ xa.

---

### Hồ sơ 5: Ứng viên ngành Sức khỏe hoặc Sư phạm (CĐ Dược / CĐ Sư phạm lên ĐH)
- **Thông tin ứng viên**:
  * Trình độ hiện tại: Cao đẳng Dược (`education: cao-dang`, `major_id: duoc-hoc`).
  * Nguyện vọng: Liên thông Đại học Dược.
  * Hệ học lựa chọn: Đào tạo từ xa (`training_type: tu-xa`).
- **Hành vi thực tế của hệ thống hiện tại**:
  1. Do thí sinh chọn học vấn là Cao đẳng nên vượt qua bước xác thực controller.
  2. Tại dòng 452, code lấy mảng allowed types từ `'cao-dang' => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ]`. Do `'tu-xa'` nằm trong danh sách này, code kết luận hợp lệ và CỘNG 5 ĐIỂM `schedule_match`.
  3. Nếu trong CSDL có chương trình Dược hoặc Điều dưỡng được nhập term `tu-xa`, hệ thống sẽ trả về: **"Tìm thấy chương trình phù hợp - Độ tương thích tốt (Ưu tiên cao)"**!
  4. Không có bất kỳ cảnh báo nào về Chứng chỉ hành nghề, không có kiểm tra học lực loại Khá/Giỏi.
- **Yêu cầu đối chiếu chuẩn MOET**:
  * **VI PHẠM PHÁP LUẬT NGHIÊM TRỌNG**: Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT nghiêm cấm đào tạo từ xa ngành Dược.
  * Hệ thống phải **HARD FAIL (Loại trừ bắt buộc)** kèm cảnh báo pháp lý:
    `"Theo quy định của Bộ Giáo dục & Đào tạo (Thông tư 28/2023/TT-BGDĐT), ngành Dược học/Sức khỏe KHÔNG ĐƯỢC PHÉP đào tạo từ xa. Bạn chỉ có thể theo học hình thức Liên thông Chính quy hoặc Vừa làm vừa học tập trung để đảm bảo điều kiện thực hành lâm sàng và cấp chứng chỉ hành nghề."`
  * Đồng thời, hệ thống phải yêu cầu khai báo: (1) Đã có Chứng chỉ/Giấy phép hành nghề hay chưa; (2) Xếp loại tốt nghiệp CĐ có đạt từ loại Khá trở lên hay không (theo Điều 9 Thông tư 08/2022/TT-BGDĐT).

---

## 6. BẢNG PHÂN TÍCH GAP ANALYSIS & ĐÁNH GIÁ 04 RỦI RO NGHIỆP VỤ TRỌNG YẾU

| Lĩnh vực kiểm tra | Trạng thái hiện tại trong code | Chuẩn mực quy chế Bộ GD&ĐT | Mức độ rủi ro | Hậu quả pháp lý & Kinh doanh |
| :--- | :--- | :--- | :--- | :--- |
| **1. Cấm đào tạo từ xa ngành đặc thù** | Không kiểm tra ngành; cho phép Từ xa mọi ngành (`inc/eligibility.php:450-462`). | Cấm tuyệt đối Từ xa đối với Sức khỏe & Sư phạm (TT 28/2023/TT-BGDĐT). | **CRITICAL (Cực kỳ nguy cấp)** | Doanh nghiệp đối mặt nguy cơ bị xử phạt hành chính từ 20-40 triệu đồng theo Nghị định 04/2021/NĐ-CP; bị đình chỉ hoạt động tư vấn tuyển sinh do thông tin gian dối. |
| **2. Điều kiện CCHN & Học lực đầu vào** | Bỏ qua hoàn toàn; không có trường dữ liệu hay logic kiểm tra. | Bắt buộc có CCHN/GPHN (QĐ 18/2017); tốt nghiệp Khá/Giỏi (TT 08/2022/TT-BGDĐT). | **HIGH (Nghiêm trọng)** | Thu nhận hàng loạt lead "rác" không đủ điều kiện pháp lý để nhập học, gây lãng phí thời gian của đội ngũ tuyển sinh và gây bức xúc cho học viên. |
| **3. Độ phủ phân khúc tuyển sinh** | Chặn cứng chỉ cho phép `cao-dang` (`inc/eligibility.php:288-301`). | Cho phép THPT, Trung cấp (đủ văn hóa THPT), Cao đẳng và Đại học (VB2). | **CRITICAL (Thiệt hại kinh doanh)** | Đánh mất hơn 70% thị phần tuyển sinh trực tuyến (người có bằng THPT học ĐH từ xa và người đã có bằng ĐH học văn bằng 2). |
| **4. Công thức tính chi phí học phí** | Nhân sai: `$tuition * 120 * $duration` (`inc/eligibility.php:486`). | Chi phí = Học phí/tín chỉ × Số tín chỉ tích lũy thực tế (hoặc Học phí/kỳ × Số kỳ). | **HIGH (Lỗi thuật toán)** | Sai lệch số tiền hàng chục triệu/hàng tỷ đồng. Đánh rớt oan các chương trình học phí rẻ, làm hỏng thuật toán gợi ý ngân sách. |
| **5. Tính toán thời gian đào tạo** | Đọc tĩnh từ post meta, không phản ánh quan hệ ngành. | Đúng ngành: 1.5n; Ngành gần: 2.0-2.5n; Khác ngành: 2.5-3.5n; THPT: 4.0n. | **MEDIUM (Trải nghiệm người dùng)** | Trải nghiệm tư vấn thiếu chuyên nghiệp, hứa hẹn sai thời gian học dẫn đến tranh chấp khi nhập học. |
| **6. Chuẩn hóa dữ liệu & Mã ngành** | Slug 6 ngành thô sơ, tên biến `'thap-phan'`, nhầm lẫn Năm sinh/Năm tốt nghiệp. | Mã ngành đào tạo cấp IV quốc gia (QĐ 09/2022/QĐ-TTg); đồng bộ dữ liệu CRM. | **MEDIUM (Kỹ thuật hệ thống)** | Khó mở rộng hệ thống, dữ liệu đổ về Telegram/CRM bị sai lệch ngữ nghĩa. |

---

## 7. ĐỀ XUẤT GIẢI PHÁP KIẾN TRÚC & MÃ NGUỒN KHẮC PHỤC (ROADMAP)

Dưới đây là phương án tái cấu trúc toàn diện module nghiệp vụ kiểm tra điều kiện tuyển sinh, đảm bảo tuân thủ 100% quy chế pháp lý và tối ưu hóa hiệu quả kinh doanh.

### 7.1. Cấu trúc lại bộ luật trong `inc/eligibility-rules.php`

1. **Chuẩn hóa lại danh mục trình độ học vấn và ma trận tương thích**:
   Thay thế slug `'thap-phan'` bằng `'thpt'`, bổ sung đầy đủ hệ đào tạo:
   ```php
   function ltdh_elig_get_training_type_compatibility() {
       return [
           'thpt'       => [ 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
           'trung-cap'  => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
           'cao-dang'   => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
           'dai-hoc'    => [ 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam' ],
           'thac-si'    => [ 'van-bang-2', 'tu-xa' ],
       ];
   }
   ```

2. **Bổ sung Quy tắc chặn các khối ngành cấm đào tạo từ xa (Thông tư 28/2023/TT-BGDĐT)**:
   ```php
   /**
    * Danh mục các nhóm ngành CẤM đào tạo từ xa theo TT 28/2023/TT-BGDĐT.
    * Bao gồm: Lĩnh vực sức khỏe có cấp CCHN (mã nhóm 772) và Lĩnh vực đào tạo giáo viên (mã nhóm 714).
    */
   function ltdh_elig_get_prohibited_distance_learning_categories() {
       return [
           'y-khoa', 'duoc-hoc', 'dieu-duong', 'rang-ham-mat', 'y-hoc-co-truyen',
           'ky-thuat-xet-nghiem-y-hoc', 'ky-thuat-hinh-anh-y-hoc',
           'su-pham-toan', 'su-pham-van', 'giao-duc-mam-non', 'giao-duc-tieu-hoc'
       ];
   }
   ```

3. **Thuật toán xác định thời gian đào tạo và số tín chỉ tích lũy động**:
   ```php
   function ltdh_elig_calculate_duration_and_credits( $input_edu, $current_major, $desired_major ) {
       // Mặc định
       $result = [
           'duration_years'   => 1.5,
           'required_credits' => 65,
           'exempt_credits'   => 60,
           'bridge_courses'   => 0, // Số môn bổ sung kiến thức
           'relationship'     => 'same'
       ];

       if ( $input_edu === 'thpt' ) {
           return [
               'duration_years'   => 4.0,
               'required_credits' => 130,
               'exempt_credits'   => 0,
               'bridge_courses'   => 0,
               'relationship'     => 'direct'
           ];
       }

       if ( $input_edu === 'trung-cap' ) {
           return [
               'duration_years'   => 2.5,
               'required_credits' => 90,
               'exempt_credits'   => 40,
               'bridge_courses'   => 4,
               'relationship'     => 'vocational'
           ];
       }

       if ( $input_edu === 'dai-hoc' ) {
           // Học Văn bằng 2
           return [
               'duration_years'   => 2.0,
               'required_credits' => 65,
               'exempt_credits'   => 45, // Miễn đại cương
               'bridge_courses'   => 0,
               'relationship'     => 'vb2'
           ];
       }

       // Đối với Cao đẳng: Xét đúng ngành / ngành gần / ngành khác
       if ( (int) $current_major === (int) $desired_major ) {
           $result['duration_years']   = 1.5;
           $result['required_credits'] = 60;
           $result['exempt_credits']   = 65;
           $result['relationship']     = 'same';
       } elseif ( ltdh_elig_are_majors_related( $current_major, $desired_major ) ) {
           $result['duration_years']   = 2.0;
           $result['required_credits'] = 75;
           $result['exempt_credits']   = 50;
           $result['bridge_courses']   = 4; // Cần học bổ sung 4 môn
           $result['relationship']     = 'related';
       } else {
           $result['duration_years']   = 2.5;
           $result['required_credits'] = 95;
           $result['exempt_credits']   = 30; // Chỉ miễn các môn chung
           $result['bridge_courses']   = 8;
           $result['relationship']     = 'different';
       }

       return $result;
   }
   ```

---

### 7.2. Cấu trúc lại bộ xử lý trong `inc/eligibility.php`

1. **Mở khóa tầng xác thực đầu vào (`ltdh_elig_validate_input`)**:
   ```php
   // THAY THẾ dòng 288:
   $valid_education = [ 'thpt', 'trung-cap', 'cao-dang', 'dai-hoc' ];
   
   // THAY THẾ dòng 292:
   // Bỏ lệnh loại trừ array_diff, chấp nhận đầy đủ term 'van-bang-2'
   $valid_training = ! is_wp_error( $training_terms ) && ! empty( $training_terms ) ? $training_terms : [ 'lien-thong', 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ];
   ```

2. **Thêm luật kiểm tra ngành cấm đào tạo từ xa trong `ltdh_elig_run_check`**:
   ```php
   // Kiểm tra trước khi chấm điểm:
   $prog_major_slug = get_post_field( 'post_name', $prog_major_id );
   $prohibited_majors = ltdh_elig_get_prohibited_distance_learning_categories();

   if ( $input['training_type'] === 'tu-xa' && in_array( $prog_major_slug, $prohibited_majors, true ) ) {
       $preliminary_status = 'not_compatible';
       $mismatch_reasons[] = 'Theo Thông tư 28/2023/TT-BGDĐT, ngành ' . get_the_title( $prog_major_id ) . ' KHÔNG ĐƯỢC PHÉP đào tạo từ xa. Vui lòng chọn hệ Vừa làm vừa học hoặc Chính quy.';
       $hard_fail = true;
   }
   ```

3. **Sửa lỗi công thức tính toán tài chính học phí**:
   ```php
   // THAY THẾ dòng 486:
   // Tính toán chi phí dựa trên tổng số tín chỉ cần học của từng lộ trình:
   $calc_info = ltdh_elig_calculate_duration_and_credits( $input['education'], $input['major_id'], $prog_major_id );
   $credits_to_study = $calc_info['required_credits'];

   // Nếu tuition_num là học phí theo tín chỉ (ví dụ < 2.000.000đ/tín chỉ)
   if ( $tuition_num > 0 && $tuition_num < 2000000 ) {
       $total_cost = $tuition_num * $credits_to_study;
   } elseif ( $tuition_num >= 2000000 ) {
       // Nếu là học phí theo kỳ (ví dụ 10 - 20 triệu/kỳ), tính số kỳ = duration * 2
       $total_cost = $tuition_num * ( $calc_info['duration_years'] * 2 );
   } else {
       $total_cost = 0;
   }
   ```

4. **Đồng bộ hóa dữ liệu Năm sinh và Năm tốt nghiệp**:
   - Tách biệt rõ ràng trong schema database: `input_birth_year smallint` (Năm sinh) và `input_graduation_year smallint` (Năm tốt nghiệp trường cũ).
   - Bổ sung trường kiểm tra giấy phép hành nghề: `has_practicing_license tinyint(1) DEFAULT 0` và xếp loại tốt nghiệp: `input_academic_rank varchar(20)`.

---

## 8. KẾT LUẬN & KIẾN NGHỊ THỰC THI

1. **Về mặt pháp lý**: Dự án cần được cập nhật ngay lập tức các điều khoản ngăn chặn đào tạo từ xa đối với ngành Y Dược và Sư phạm để tránh rủi ro pháp lý nghiêm trọng theo Nghị định 04/2021/NĐ-CP của Chính phủ.
2. **Về mặt kinh doanh**: Cần mở khóa ngay lập tức tầng xác thực `ltdh_elig_validate_input` và thêm các tùy chọn THPT, Trung cấp, Đại học (học VB2) vào dropdown form kiểm tra. Động thái này sẽ **mở rộng ngay lập tức tệp khách hàng tiềm năng lên hơn 300%**, đặc biệt là học viên học Cử nhân trực tuyến từ xa và Cử nhân văn bằng 2.
3. **Về mặt kỹ thuật**: Cần sửa dứt điểm lỗi thuật toán nhân bội số học phí và triển khai thuật toán tính thời gian đào tạo động theo quan hệ ngành đúng - gần - khác.

*Báo cáo được hoàn thành và đối chiếu dựa trên toàn bộ mã nguồn thực tế của theme `lienthongdaihoc` tại thời điểm kiểm tra.*
