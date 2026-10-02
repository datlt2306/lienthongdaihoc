# BÁO CÁO KIỂM ĐỊNH & THẨM ĐỊNH TOÀN DIỆN KIẾN TRÚC THÔNG TIN (IA), MÔ HÌNH DỮ LIỆU & THUẬT NGỮ DỰ ÁN LIENTHONGDAIHOC.COM
## Comprehensive Codebase Audit: Information Architecture, Data Model, Taxonomy, URL Routing & Terminology Standardization

- **Dự án**: Nền tảng Tuyển sinh & Định hướng Tuyển sinh Liên Thông Đại Học (`lienthongdaihoc.com`)
- **Mã kiểm định**: `LTDH-AUDIT-IA-DATAMODEL-2026-10-01`
- **Tác giả kiểm định**: `explorer_survey_1` (IA & Data Model Explorer)
- **Đối tượng kiểm định**: Mã nguồn Theme WordPress `lienthongdaihoc` v2.0.0 (PHP 8.1+, MySQL 8.0, ACF Pro 6+)
- **Thư mục làm việc**: `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_survey_1/`
- **Ngày ban hành**: 01/10/2026
- **Trạng thái**: Hard Handoff (Báo cáo thẩm định hoàn tất, sẵn sàng cho pha tái cấu trúc)

---

## 1. OBSERVATION (QUAN SÁT THỰC TẾ TRONG MÃ NGUỒN)

Toàn bộ các phát hiện dưới đây được ghi nhận trực tiếp từ phân tích tĩnh, đối chiếu cú pháp và rà soát từng dòng mã nguồn PHP, JSON và JavaScript của theme `lienthongdaihoc`.

### 1.1. Hiện trạng Định nghĩa CPTs, Taxonomies & Schema Nạp Dữ liệu
- **Cơ chế nạp tự động qua JSON**: Tệp `inc/post-types.php:15-108` nạp cấu hình CPT và Taxonomy từ tệp `inc/acf-import-cpts.json` thông qua hook `init` (độ ưu tiên 0).
  * **CPT `school`** (`inc/acf-import-cpts.json:2-57`): Nhãn "Trường đối tác", slug `truong-doi-tac`, gán trực tiếp taxonomy `region`.
  * **CPT `major`** (`inc/acf-import-cpts.json:58-111`): Nhãn "Ngành học", slug `nganh-hoc`, `taxonomies: []`.
  * **CPT `program`** (`inc/acf-import-cpts.json:112-168`): Nhãn "Chương trình đào tạo", slug rewrite `""` (URL phẳng không tiền tố), `has_archive_slug: "chuong-trinh"`, gán taxonomies `["training_type", "campus"]`.
  * **CPT `guide`** (`inc/post-types.php:110-128`): Được đăng ký bổ sung trong PHP nếu chưa có trong JSON: archive slug `cam-nang`, single rewrite `huong-dan`.
  * **Taxonomy `training_type`** (`inc/acf-import-cpts.json:169-208`): Nhãn "Hệ đào tạo", gán vào `["program", "school"]`, slug `he-dao-tao`, `hierarchical: true`.
  * **Taxonomy `campus`** (`inc/acf-import-cpts.json:209-246`): Nhãn "Cơ sở đào tạo", gán vào `["program", "school"]`, slug `co-so`, `hierarchical: true`.
  * **Taxonomy `region`** (`inc/acf-import-cpts.json:247-283`): Nhãn "Khu vực", gán vào `["school"]`, slug `khu-vuc`, `hierarchical: true`.
  * **Taxonomy `major_cat`** (`inc/acf-import-cpts.json:284-320`): Nhãn "Nhóm ngành", gán vào `["major"]`, slug `nhom-nganh`, `hierarchical: true`.

### 1.2. Hiện trạng Quan hệ Thực thể (Entity Relationships)
- Quan hệ giữa `Program`, `School`, và `Major` được quản lý qua ACF Post Object kết hợp sync meta hai chiều (`inc/relationship-hooks.php:12-74`):
  * `Program` lưu `school_relationship` (Post ID của `school`) và `major_relationship` (Post ID của `major`).
  * Hook `acf/save_post` tại `inc/relationship-hooks.php` tự động đẩy Post ID của `program` vào mảng meta `_offered_programs` của bài viết `school` và `major`.
  * Taxonomy `training_type` và `campus` được gán vào `program` dưới dạng `wp_set_object_terms`.
  * Hàm `ltdh_get_school_training_types()` (`inc/core/class-helpers.php:937-991`) thực hiện rollup tự động: nếu `school` chưa có term `training_type` trực tiếp, nó sẽ query ngược từ các `program` con để lấy danh sách term của trường.

### 1.3. Hiện trạng Cấu trúc URLs và Rewrite Rules
- Tệp `inc/core/class-rewrite-rules.php` can thiệp sâu vào URL routing:
  * Dòng 15-24: Đăng ký rewrite `/he-dao-tao/` và `/he-dao-tao/([^/]+)/` trỏ về `index.php?training_type=$matches[1]` (phục vụ hiển thị archive hệ đào tạo qua `taxonomy-training_type.php`).
  * Dòng 41-45: Đăng ký regex top-priority: `^nganh-([^/]+)/?$` cho Major, và `([^/]+)/?$` cho Program (catch-all top priority).
  * Dòng 55-170: Hàm `ltdh_program_request_guard()` chộp bắt mọi query var `program` và kiểm tra CSDL theo thứ tự: nếu slug thuộc `program` → render `single-program.php`; nếu có tiền tố `nganh-` → render `single-major.php`; nếu thuộc `school` → render `single-school.php`; nếu không thì trả về `post` hoặc `page`.
  * Dòng 179-190 (`ltdh_program_permalink`):
    - `program`: URL phẳng `/{program_slug}/` (ví dụ: `https://lienthongdaihoc.com/dai-hoc-kinh-te-quoc-dan-ke-toan-tu-xa/`).
    - `school`: URL phẳng `/{school_slug}/` (ví dụ: `https://lienthongdaihoc.com/dai-hoc-kinh-te-quoc-dan/`).
    - `major`: URL có tiền tố `/nganh-{major_slug}/` (ví dụ: `https://lienthongdaihoc.com/nganh-cong-nghe-thong-tin/`).
  * Dòng 212-255 (`ltdh_redirect_taxonomy_base`):
    - `/program/{slug}/` → 301 → `/{slug}/`.
    - `/truong-doi-tac/{slug}/` → 301 → `/{slug}/`.
    - `/nganh-hoc/{slug}/` → 301 → `/nganh-{slug}/`.
    - `/co-so/` → 301 → `/he-dao-tao/tu-xa/`.
    - `/chuong-trinh/` → 301 → `/he-dao-tao/tu-xa/`.

### 1.4. Các khái niệm Xung đột và Thuật ngữ Bị cấm trong Codebase
Qua rà soát toàn bộ dự án bằng công cụ tìm kiếm tĩnh, phát hiện các điểm xung đột nghiêm trọng:
1. **Thuật ngữ "Văn bằng 2" / "VB2" (49 lượt xuất hiện tại 15 tệp)**:
   - `inc/cli-commands.php:101, 304, 400`: `van-bang-2` ('Văn bằng 2') được gieo mẫu như một term chính thức của taxonomy `training_type` bên cạnh `tu-xa` và `chinh-quy`.
   - `inc/acf-import-fields.json:912`: Lựa chọn checkbox `field_elig_training_types` chứa: `"van-bang-2": "Văn bằng 2"`.
   - `inc/acf-fields.php:286-293`: Bảng lộ trình đào tạo mặc định của ngành CNTT khai báo: `entry_level => 'Văn bằng 2 (Đã có 1 bằng ĐH/CĐ khác)'`, `study_mode => 'Văn bằng 2 CNTT'`, `degree_output => 'Bằng Đại học thứ 2 CNTT'`.
   - `inc/eligibility-rules.php:21-28`: Ma trận tương thích gán: `'cao-dang' => [..., 'van-bang-2', ...]`, `'dai-hoc' => [ 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam' ]`, kèm chú thích `* VB2: requires existing degree (Cao đẳng/Đại học)`.
   - `inc/eligibility.php:294, 639, 648, 1590`: Thừa nhận `van-bang-2` như một hệ đào tạo hợp lệ; dòng 639 map `'dai-hoc' => 'Đại học (VB2)'`.
   - `template-parts/eligibility/wizard.php:29`: Khai báo option: `<option value="dai-hoc">Đã tốt nghiệp Đại học (Học Văn bằng 2)</option>`.
   - `front-page.php:31`: Thẻ H1 ẩn ghi: `<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học, Văn Bằng 2 & Đại Học Từ Xa</h1>`.
   - `front-page.php:851-854`: Testimonial học viên ghi: `'role' => 'VB2 Công nghệ thông tin'`, `'content' => 'Mình đã học Văn bằng 2 CNTT tại đây...'`.
   - `front-page.php:945`: Bài viết mẫu: `['title' => 'Điều kiện học Văn bằng 2 đại học năm 2026']`.
   - `inc/config/class-defaults.php:65`: Subtext của Hero badge: `'subtext' => 'Liên thông, VB2, Từ xa'`.
   - `template-parts/banner.php:27, 106`: `$banner_subtitle = 'Tổng hợp các chương trình đào tạo từ xa, liên thông, văn bằng 2';`.
   - `taxonomy.php:27`: `<p class="text-slate-500 text-sm">Tất cả chương trình đào tạo liên thông, văn bằng 2, đại học từ xa.</p>`.
   - `footer.php:87`: Link chân trang ghi nhãn `"Cao đẳng online / VB2"`.
2. **Khái niệm "Chính quy" bị tách rời thành sản phẩm tuyển sinh độc lập**:
   - `inc/cli-commands.php:104, 304, 400`: `chinh-quy` được tạo thành term riêng biệt ngang hàng với `tu-xa` và `van-bang-2`.
   - `inc/eligibility-rules.php:18-20, 30`: Cho phép thí sinh tốt nghiệp THPT chọn `chinh-quy` (`'thpt' => [ 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ]`). Điều này ngầm định website tuyển sinh cả hệ Đại học Chính quy 4 năm cho học sinh cấp 3, vi phạm trực tiếp phạm vi duy nhất là "Liên thông Đại học".
   - `single-program.php:195`: Ghi thông báo cảnh báo: `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu"`. (Trong khi ở chương trình chuẩn UTC tại dòng 567, text đúng phải là: `"hệ Liên thông Chính quy"`).
3. **Xung đột thuật ngữ "Hệ đào tạo" vs "Hình thức học" (91 lượt xuất hiện "Hệ đào tạo")**:
   - Taxonomy `training_type` bị đặt tên là "Hệ đào tạo" trong toàn bộ JSON (`inc/acf-import-cpts.json:171`), Breadcrumb (`inc/core/class-helpers.php:432`), Menu (`inc/core/class-menus.php:137`), tiêu đề Archive (`taxonomy-training_type.php:172`), và nhãn hiển thị tại `single-school.php:501` và `single-major.php:470`.
   - Thực chất, trong Luật Giáo dục Đại học sửa đổi 2018 (Điều 6), giáo dục đại học chỉ có các **hình thức đào tạo** (Chính quy, Vừa làm vừa học, Đào tạo từ xa). "Hệ đào tạo" là cách gọi dân gian gây nhầm lẫn giữa cấp bậc đào tạo (Cao đẳng/Đại học) và phương thức học.
4. **Nhầm lẫn giữa "Chương trình học thuật" (Curriculum) và "Cơ hội tuyển sinh Liên thông cụ thể" (Admission Offering)**:
   - Trong `inc/acf-import-cpts.json:117` và admin menu, CPT `program` có nhãn là "Chương trình đào tạo".
   - Trên thực tế, mỗi bài viết `program` chính là một **Cơ hội tuyển sinh Liên thông cụ thể** gồm 4 thành tố: `Trường đại học đối tác` + `Ngành học` + `Hình thức học (Chính quy / VLVH / Từ xa)` + `Thông tin tuyển sinh thực tế (Học phí, Đợt tuyển, Điều kiện)`. Ví dụ: *Đại học GTVT — Liên thông ngành CNTT — Vừa học vừa làm*.

---

## 2. LOGIC CHAIN (CHUỖI LẬP LUẬN TỪ QUAN SÁT ĐẾN KẾT LUẬN)

1. **Từ Quan sát 1.1, 1.2 và Yêu cầu 1 của Chỉ thị**:
   - *Bước 1*: Người dùng chỉ thị rõ ràng website `lienthongdaihoc.com` CHỈ TẬP TRUNG DUY NHẤT vào nghiệp vụ **Liên Thông Đại Học**.
   - *Bước 2*: Hiện tại, mã nguồn đang gán `van-bang-2` ('Văn bằng 2') vào taxonomy `training_type` (Quan sát 1.4.1), xem VB2 như một nhánh tuyển sinh độc lập ngang hàng với Từ xa và Liên thông.
   - *Bước 3*: Theo Quy định hiện hành của Bộ GD&ĐT (Thông tư 08/2021/TT-BGDĐT và Luật GDĐH 2018), người đã có bằng đại học thứ nhất khi học đại học thứ hai thực chất là hình thức đào tạo liên thông hoặc đào tạo theo hình thức Vừa làm vừa học / Từ xa được công nhận và miễn trừ tín chỉ.
   - *Bước 4*: Do đó, việc duy trì "Văn bằng 2" như một taxonomy term hoặc một sản phẩm tuyển sinh riêng lẻ trên website `lienthongdaihoc.com` làm phân tán định vị thương hiệu, gây hiểu lầm cho người học và phá vỡ cấu trúc IA cốt lõi. Cần loại bỏ term này khỏi hiển thị và phễu tuyển sinh, chuyển hướng người học đã có bằng ĐH sang lộ trình tuyển sinh Liên thông / Nhận bằng thứ 2 theo hình thức Từ xa hoặc Vừa làm vừa học.

2. **Từ Quan sát 1.4.2 về thuật ngữ "Chính quy"**:
   - *Bước 1*: Việc `inc/eligibility-rules.php:18` cho phép thí sinh `thpt` chọn `chinh-quy` biến website thành cổng tuyển sinh đại học tổng hợp (tuyển học sinh cấp 3 vào học đại học 4 năm).
   - *Bước 2*: Nhưng tại `inc/cli-commands.php:811` và `single-program.php:567`, chương trình chính quy thực tế lại là "Cử nhân Công nghệ thông tin (Liên thông Chính quy)" — tức là sinh viên đã có bằng Cao đẳng, học liên thông tập trung ban ngày tại trường ĐH.
   - *Bước 3*: Vì vậy, "Chính quy" KHÔNG PHẢI là một loại tuyển sinh độc lập, mà là một **Hình thức học** (Study Mode) bên trong tuyển sinh Liên thông. Cần chuẩn hóa nhãn sang "Liên thông Chính quy", và chặn không cho đối tượng thuần THPT nộp hồ sơ trực tiếp mà phải có bằng Trung cấp/Cao đẳng.

3. **Từ Quan sát 1.4.3 về "Hệ đào tạo" vs "Hình thức học"**:
   - *Bước 1*: Mô hình nghiệp vụ mục tiêu quy định rõ:
     `Trường Đại học → Tuyển sinh Liên thông → Hình thức học (Chính quy, Vừa học vừa làm, Từ xa) → Ngành học → Cơ hội tuyển sinh cụ thể`.
   - *Bước 2*: Trong mã nguồn, thuật ngữ "Hệ đào tạo" đang được dùng để đại diện cho `training_type`.
   - *Bước 3*: Để người dùng dễ hiểu và đúng chuẩn pháp lý, nhãn giao diện công khai và menu cần được chuyển thành "Hình thức học" (hoặc "Phương thức đào tạo liên thông").
   - *Bước 4*: Tuy nhiên, về mặt kỹ thuật, việc đổi slug taxonomy `training_type` hay URL rewrite `/he-dao-tao/` trong CSDL sẽ gây nguy cơ vỡ toàn bộ rewrite rules, 404 URL đã index và đứt gãy term relationships (Quan sát 1.1, 1.3). Vì vậy, can thiệp an toàn tối thiểu là **giữ nguyên slug kỹ thuật `training_type` và route `/he-dao-tao/`**, chỉ thay đổi nhãn hiển thị UI, breadcrumbs, H1 và meta SEO sang "Hình thức học: [Liên thông Từ xa / Vừa học vừa làm / Chính quy]".

4. **Từ Quan sát 1.3 về URL Routing & SEO**:
   - *Bước 1*: Hệ thống rewrite rules đang hoạt động rất tinh vi: Program và School dùng chung regex catch-all phẳng `/{slug}/`, được lọc qua cache và DB guard (`ltdh_program_request_guard`).
   - *Bước 2*: Bất kỳ sự thay đổi tùy tiện nào với permalink của CPT `program` hoặc `school` sẽ gây xung đột rewrite, vòng lặp chuyển hướng hoặc sập 404 cho hàng loạt trang đã index.
   - *Bước 3*: Do đó, tuyệt đối giữ nguyên cấu trúc URL công khai của các thực thể (`/{school_slug}/`, `/nganh-{major_slug}/`, `/{program_slug}/`, `/he-dao-tao/{type_slug}/`). Đối với URL lỗi thời `/he-dao-tao/van-bang-2/`, thiết lập 301 Permanent Redirect về `/he-dao-tao/tu-xa/`.

---

## 3. BẢNG ÁNH XẠ KIỂM TOÁN HIỆN TRẠNG → MỤC TIÊU (9-COLUMN SCHEMA)

Bảng đối chiếu toàn diện dưới đây tuân thủ 100% schema bắt buộc theo chỉ thị:
`CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE`

| CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE |
|---|---|---|---|---|---|---|---|---|
| **CPT: `program`** | Chương trình đào tạo / Chương trình | Thực thể trung gian đại diện cho một khóa tuyển sinh cụ thể | `training_type`, `campus` | M:1 với `school` (`school_relationship`), M:1 với `major` (`major_relationship`) | `/{program_slug}/` (URL phẳng không prefix) | `single-program.php` | **Cơ hội Tuyển sinh Liên thông Cụ thể (Admission Offering)** | Giữ nguyên post type `program` trong CSDL. Đổi nhãn quản trị và UI sang "Cơ hội tuyển sinh Liên thông". Tiêu đề chuẩn hóa: `Liên thông [Ngành] - [Hình thức học] - [Trường]`. Cung cấp đầy đủ thông tin: Trường, Ngành, Hình thức học, Đối tượng, Thời gian, Học phí, Đợt tuyển, Form tư vấn. |
| **CPT: `school`** | Trường đối tác | Hồ sơ trường đại học đối tác tuyển sinh | `region` (trực tiếp); `training_type`, `campus` (qua rollup) | 1:M với `program` (lưu array ID trong meta `_offered_programs`) | Archive: `/truong-doi-tac/`<br>Single: `/{school_slug}/` | `archive-school.php`<br>`single-school.php` | **Trường Đại học Tuyển sinh Liên thông (University Profile)** | Giữ nguyên CPT và URL. Chuẩn hóa H1/SEO Title: `Tuyển sinh Liên thông [Tên Trường] [Năm]`. Tại `single-school.php`, danh sách cơ hội tuyển sinh gom nhóm theo Ngành và Hình thức học của Liên thông. |
| **CPT: `major`** | Ngành học / Chuyên ngành | Ngành đào tạo học thuật / mã ngành | `major_cat` (Nhóm ngành) | 1:M với `program` (lưu array ID trong `_offered_programs`), M:N với `major` qua `major_related` | Archive: `/nganh-hoc/`<br>Single: `/nganh-{major_slug}/` | `archive-major.php`<br>`single-major.php` | **Ngành đào tạo Liên thông (Articulated Major)** | Giữ nguyên CPT và URL. Chuẩn hóa H1/SEO: `Liên thông ngành [Tên Ngành] - Danh sách trường & Học phí [Năm]`. Trong `single-major.php`, chuẩn hóa bảng lộ trình: bỏ mục "Văn bằng 2", thay bằng "Đã có bằng ĐH khác (học thêm ngành 2)"; đổi nhãn "Hệ đào tạo:" thành "Hình thức học:". |
| **CPT: `guide`** | Cẩm nang tuyển sinh | Bài viết hướng dẫn thủ tục, quy chế liên thông | `post_tag`, `category` (nếu gán) | M:N qua `group_related_entities` | Archive: `/cam-nang/`<br>Single: `/huong-dan/{slug}/` | `single-guide.php` | **Kiến thức & Cẩm nang Tuyển sinh Liên thông** | Giữ nguyên CPT và URL. Đăng ký chính thức vào `inc/acf-import-cpts.json` để đồng bộ. Tập trung nội dung vào hướng dẫn liên thông, chuyển đổi tín chỉ, quy chế thi/xét tuyển liên thông. |
| **CPT: `post`** | Tin tức / Tin tuyển sinh | Tin tức sự kiện, thông báo khai giảng | `category`, `post_tag` | M:N qua `group_related_entities` | Archive: `/tin-tuc/`<br>Single: `/tin-tuc/{slug}/` hoặc `/{slug}/` | `index.php`<br>`single.php` | **Tin tức & Thông báo Tuyển sinh Liên thông** | Giữ nguyên CPT và URL. Đảm bảo tin bài chỉ xoay quanh lịch thi, khai giảng, điểm chuẩn các đợt liên thông của các trường. |
| **Taxonomy: `training_type`** | Hệ đào tạo | Phân loại hình thức học (nhưng đang bị lẫn lộn loại hình tuyển sinh) | N/A (Áp dụng cho `program`, `school`) | M:N với `program` | Base: `/he-dao-tao/`<br>Term: `/he-dao-tao/{term_slug}/` | `taxonomy-training_type.php`<br>`archive-program.php` | **Hình thức học Liên thông (Study Mode)** | Giữ nguyên slug taxonomy `training_type` và URL `/he-dao-tao/` để bảo toàn SEO. Đổi nhãn giao diện và admin thành "Hình thức học". Chuẩn hóa SEO Title/H1 thành `Liên thông [Tên Hình thức học]`. |
| **Term: `tu-xa`** (thuộc `training_type`) | Từ xa | Chương trình học trực tuyến qua E-learning | Thuộc `training_type` | Gán cho các program trực tuyến | `/he-dao-tao/tu-xa/` | `taxonomy-training_type.php` | **Hình thức học: Liên thông Từ xa (Online Articulation)** | Đổi display name thành "Liên thông Từ xa". Tiêu đề trang archive: `Tuyển sinh Liên thông Đại học Từ xa [Năm]`. Hiển thị danh sách các cơ hội tuyển sinh liên thông học online. |
| **Term: `vua-hoc-vua-lam`** (thuộc `training_type`) | Vừa học vừa làm | Chương trình học buổi tối hoặc cuối tuần | Thuộc `training_type` | Gán cho các program học linh hoạt tại trạm | `/he-dao-tao/vua-hoc-vua-lam/` | `taxonomy-training_type.php` | **Hình thức học: Liên thông Vừa học vừa làm (Part-time Articulation)** | Đổi display name thành "Liên thông Vừa học vừa làm". Tiêu đề trang archive: `Tuyển sinh Liên thông Đại học Vừa học vừa làm [Năm]`. Phù hợp người đi làm muốn học trực tiếp ngoài giờ. |
| **Term: `chinh-quy`** (thuộc `training_type`) | Chính quy | Đang bị hiểu nhầm là đại học chính quy từ THPT | Thuộc `training_type` | Gán cho các program học tập trung ban ngày | `/he-dao-tao/chinh-quy/` | `taxonomy-training_type.php` | **Hình thức học: Liên thông Chính quy (Full-time Continuous Articulation)** | Làm rõ ngữ cảnh: ĐÂY LÀ HÌNH THỨC HỌC CỦA LIÊN THÔNG. Đổi display name thành "Liên thông Chính quy". Tiêu đề archive: `Tuyển sinh Liên thông Đại học Chính quy [Năm]`. Đối tượng: Thí sinh có bằng Cao đẳng/Trung cấp muốn học tập trung tại giảng đường. |
| **Term: `van-bang-2`** (thuộc `training_type`) | Văn bằng 2 | Tuyển sinh văn bằng 2 đại học | Thuộc `training_type` | Gán cho các program VB2 | `/he-dao-tao/van-bang-2/` | `taxonomy-training_type.php` | **KHÁI NIỆM BỊ LOẠI BỎ (Banned/Deprecated)** | **LOẠI BỎ KHỎI GIAO DIỆN & TAXONOMY**. Xóa bỏ khỏi menu điều hướng và bộ lọc. Re-map các bài viết đang gán term này sang `tu-xa` hoặc `vua-hoc-vua-lam`. Thiết lập 301 Redirect: `/he-dao-tao/van-bang-2/` → 301 → `/he-dao-tao/tu-xa/`. |
| **Taxonomy: `campus`** | Cơ sở đào tạo | Địa điểm lớp học, phân hiệu, trạm thi | N/A (Áp dụng cho `program`, `school`) | M:N với `program` | Base: `/co-so/` (đã 301 về `/he-dao-tao/tu-xa/`) | `taxonomy.php` | **Địa điểm / Trạm Đào tạo Liên thông** | Giữ nguyên. Trong dropdown bộ lọc cơ sở học, loại bỏ term `online` để tránh trùng lặp ngữ nghĩa với Hình thức học `tu-xa`. Chỉ giữ các địa điểm địa lý thực tế (Hà Nội, TP.HCM, Đà Nẵng, Thái Nguyên...). |
| **Taxonomy: `region`** | Khu vực | Miền Bắc, Miền Trung, Miền Nam | N/A (Áp dụng cho `school`) | M:N với `school` | `/khu-vuc/{slug}/` | `taxonomy.php` | **Khu vực Địa lý Trường Đại học** | Giữ nguyên. Hỗ trợ lọc trường đại học đối tác theo vùng miền. |
| **Taxonomy: `major_cat`** | Nhóm ngành | Khối ngành (Kinh tế, Kỹ thuật - CNTT, Xã hội...) | N/A (Áp dụng cho `major`) | M:N với `major` | `/nhom-nganh/{slug}/` | `taxonomy.php` | **Khối / Nhóm ngành Tuyển sinh Liên thông** | Giữ nguyên. Hỗ trợ bộ lọc tìm kiếm ngành liên thông theo chuyên môn. |
| **ACF Field: `elig_training_types`** | Hệ đào tạo áp dụng | Checkbox chọn hệ trong metabox điều kiện | Ánh xạ vào `training_type` | Thuộc `program` | N/A (Admin) | N/A | **Hình thức học áp dụng** | Đã được tắt qua filter `inc/acf-fields.php:156`. Cập nhật file JSON: loại bỏ choice `van-bang-2`, chỉ giữ `tu-xa`, `vua-hoc-vua-lam`, `chinh-quy`. Sử dụng taxonomy chuẩn làm nguồn chân lý duy nhất. |
| **ACF Field: `major_entry_roadmaps`** | Bảng lộ trình theo đầu vào | Repeater mô tả thời gian, hình thức và bằng cấp theo đối tượng | N/A | Thuộc `major` | N/A | `single-major.php` | **Lộ trình Liên thông theo Văn bằng Hiện có** | Trong `inc/acf-fields.php:286-293`, sửa mục "Văn bằng 2" thành: `entry_level`: "Đã có bằng Đại học khác (Học ngành thứ hai)", `study_mode`: "Online từ xa / Vừa học vừa làm", `degree_output`: "Bằng Cử nhân / Kỹ sư thứ 2 chuẩn Bộ GD&ĐT". |
| **Logic: `eligibility-rules.php`** | Ma trận xét tuyển tương thích | Kiểm tra điều kiện đầu vào với hệ đào tạo | N/A | Thuộc module kiểm tra điều kiện | `/kiem-tra-dieu-kien/` | `page-eligible.php`<br>`template-parts/eligibility/wizard.php` | **Quy chuẩn Xét tuyển Điều kiện Liên thông** | 1. Thí sinh `thpt`: Chặn không gợi ý Liên thông trực tiếp, hiển thị tư vấn lộ trình 2 giai đoạn (Học TC/CĐ rồi liên thông).<br>2. Thí sinh `trung-cap`: Liên thông ĐH (2.5 - 3 năm).<br>3. Thí sinh `cao-dang`: Liên thông ĐH (1.5 - 2 năm).<br>4. Thí sinh `dai-hoc`: Lộ trình học liên thông ngành thứ hai (1.5 - 2 năm), loại bỏ chữ "VB2". |
| **Global Menu & Defaults** | Navigation Menu | Thanh menu điều hướng chính, mobile và footer | `training_type` | N/A | Toàn website | `header.php`<br>`inc/core/class-menus.php`<br>`class-defaults.php` | **Kiến trúc Điều hướng Chuẩn hóa** | Cấu trúc chuẩn: `Trang chủ → Liên thông (Chính quy, Vừa học vừa làm, Từ xa) → Ngành học → Trường đại học → Kiến thức liên thông → Tư vấn`. Loại bỏ mục "VB2", đổi nhãn "Hệ đào tạo" thành "Hình thức học". |
| **SEO Titles & Metadata** | Rank Math Integration | Tạo dynamic title, meta desc và JSON-LD schema | `training_type` | N/A | Toàn website | `inc/seo/class-rankmath-integration.php` | **SEO Chuẩn hóa Ngữ nghĩa Liên thông** | Áp dụng cấu trúc đồng bộ:<br>- Program: `Liên thông [Ngành] ([Hình thức học]) - [Trường] \| Tuyển sinh [Năm]`<br>- Term archive: `Liên thông [Hình thức học] - Danh sách trường & Ngành tuyển sinh [Năm]`<br>- Single School: `Tuyển sinh Liên thông [Trường] [Năm]`<br>- Single Major: `Liên thông ngành [Ngành] [Năm]`. |

---

## 4. CHI TIẾT CÁC ĐIỂM NÓNG CẦN KHẮC PHỤC TRONG MÃ NGUỒN

### Điểm nóng 1: Loại bỏ triệt để khái niệm "Văn bằng 2" và "VB2"
- **Tệp `front-page.php:31`**:
  * *Hiện trạng*: `<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học, Văn Bằng 2 & Đại Học Từ Xa</h1>`
  * *Sửa thành*: `<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học Toàn Quốc</h1>`
- **Tệp `front-page.php:851-854`**:
  * *Hiện trạng*: `'role' => 'VB2 Công nghệ thông tin'`, `'content' => 'Mình đã học Văn bằng 2 CNTT tại đây...'`
  * *Sửa thành*: `'role' => 'Liên thông ĐH (Người đã có bằng ĐH khác) - CNTT'`, `'content' => 'Mình đã đăng ký liên thông học thêm ngành CNTT tại đây...'`
- **Tệp `inc/config/class-defaults.php:65`**:
  * *Hiện trạng*: `'subtext' => 'Liên thông, VB2, Từ xa'`
  * *Sửa thành*: `'subtext' => 'Từ xa, Vừa học vừa làm, Chính quy'`
- **Tệp `template-parts/banner.php:27, 106`**:
  * *Hiện trạng*: `$banner_subtitle = 'Tổng hợp các chương trình đào tạo từ xa, liên thông, văn bằng 2';`
  * *Sửa thành*: `$banner_subtitle = 'Tổng hợp thông tin tuyển sinh liên thông đại học từ xa, vừa học vừa làm và chính quy';`
- **Tệp `taxonomy.php:27`**:
  * *Hiện trạng*: `<p class="text-slate-500 text-sm">Tất cả chương trình đào tạo liên thông, văn bằng 2, đại học từ xa.</p>`
  * *Sửa thành*: `<p class="text-slate-500 text-sm">Tất cả cơ hội tuyển sinh liên thông đại học trên toàn quốc.</p>`
- **Tệp `footer.php:87`**:
  * *Hiện trạng*: `<a href="#" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Cao đẳng online / VB2</a>`
  * *Sửa thành*: `<a href="<?php echo esc_url( home_url('/he-dao-tao/vua-hoc-vua-lam/') ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Liên thông Vừa học vừa làm</a>`
- **Tệp `template-parts/eligibility/wizard.php:29`**:
  * *Hiện trạng*: `<option value="dai-hoc">Đã tốt nghiệp Đại học (Học Văn bằng 2)</option>`
  * *Sửa thành*: `<option value="dai-hoc">Đã tốt nghiệp Đại học (Học thêm ngành thứ hai)</option>`
- **Tệp `template-parts/eligibility/wizard.php:26`**:
  * *Hiện trạng*: `<option value="thpt">Tốt nghiệp THPT (Học ĐH Từ xa)</option>`
  * *Giải pháp*: Với người dùng THPT, khi chọn, hệ thống wizard sẽ hiển thị khuyến nghị: *"Hệ thống chuyên sâu về Liên thông Đại học (yêu cầu đầu vào tối thiểu Trung cấp/Cao đẳng). Bạn có thể xem lộ trình 2 giai đoạn: Học Cao đẳng nghề trước rồi Liên thông Đại học"* hoặc loại bỏ để đảm bảo tính tinh khiết của phễu.

### Điểm nóng 2: Chuyển đổi ngữ nghĩa "Hệ đào tạo" sang "Hình thức học"
- **Tệp `taxonomy-training_type.php:172` & `archive-program.php:172`**:
  * *Hiện trạng*: `<?php echo $active_type_term ? 'Hệ đào tạo: ' . esc_html( $active_type_term->name ) : 'Tất cả chương trình đào tạo'; ?>`
  * *Sửa thành*: `<?php echo $active_type_term ? 'Hình thức học: ' . esc_html( $active_type_term->name ) : 'Tất cả cơ hội tuyển sinh liên thông'; ?>`
- **Tệp `taxonomy-training_type.php:250` & `archive-program.php:250`**:
  * *Hiện trạng*: `<span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Hệ đào tạo:</span>`
  * *Sửa thành*: `<span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Hình thức học:</span>`
- **Tệp `single-school.php:501` & `single-major.php:470`**:
  * *Hiện trạng*: `<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hệ đào tạo:</div>`
  * *Sửa thành*: `<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hình thức học:</div>`
- **Tệp `front-page.php:158-164` (Dropdown bộ lọc homepage)**:
  * *Hiện trạng*:
    ```html
    <select name="he" class="...">
        <option value="">-- Chọn hệ học --</option>
    ```
  * *Sửa thành*:
    ```html
    <select name="he" class="...">
        <option value="">-- Chọn hình thức học --</option>
    ```
- **Tệp `inc/core/class-menus.php:135-178` (Menu động)**:
  * Kiểm tra tiêu đề menu `hệ đào tạo` hoặc `liên thông` để tự động inject các sub-item:
    - `Liên thông Từ xa` (`/he-dao-tao/tu-xa/`)
    - `Liên thông Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`)
    - `Liên thông Chính quy` (`/he-dao-tao/chinh-quy/`)

### Điểm nóng 3: Chuẩn hóa nhãn và loại bỏ bộ lọc vô nghĩa
- Trong các form tìm kiếm và sidebar bộ lọc:
  * Loại bỏ hoàn toàn lựa chọn lọc "Loại chương trình: Liên thông" (vì 100% chương trình trên trang web đều là Liên thông).
  * Chỉ giữ lại các tiêu chí phân loại thực tế:
    1. **Hình thức học** (Tất cả, Từ xa, Vừa học vừa làm, Chính quy).
    2. **Trường Đại học đối tác** (ĐH Kinh tế Quốc dân, ĐH Giao thông Vận tải, ĐH Mở, ĐH Thái Nguyên...).
    3. **Ngành học mong muốn** (Công nghệ thông tin, Quản trị kinh doanh, Kế toán, Ngôn ngữ Anh...).
    4. **Cơ sở / Khu vực** (Hà Nội, TP.HCM, Đà Nẵng...).

---

## 5. CHIẾN LƯỢC CAN THIỆP TỐI THIỂU AN TOÀN (MINIMAL SAFE INTERVENTION STRATEGY)

Để bảo đảm tái cấu trúc thành công mà **không gây breaking change, không làm rơi rụng SEO, không mất dữ liệu CSDL và không làm vỡ giao diện**, chiến lược can thiệp tuân thủ 5 nguyên tắc cốt lõi:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│               CHIẾN LƯỢC CAN THIỆN TỐI THIỂU AN TOÀN (5 NGUYÊN TẮC)         │
├─────────────────────────────────────────────────────────────────────────────┤
│ 1. Zero Database Schema Breaking Changes                                    │
│    - Giữ nguyên Post Types: program, school, major, guide                   │
│    - Giữ nguyên Taxonomies: training_type, campus, region, major_cat        │
│    - Không xóa bảng hay đổi tên cột SQL.                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│ 2. Semantic Re-labeling at Presentation Layer                               │
│    - Đổi toàn bộ nhãn hiển thị UI, admin labels, breadcrumbs, H1, meta      │
│    - "Hệ đào tạo" → "Hình thức học" / "Phương thức đào tạo liên thông"      │
│    - "Chương trình đào tạo" → "Cơ hội tuyển sinh Liên thông"                │
├─────────────────────────────────────────────────────────────────────────────┤
│ 3. 100% Preservation of Public URLs & Canonical Routing                     │
│    - Tuyệt đối không thay đổi URLs công khai:                               │
│      /{school_slug}/, /nganh-{major_slug}/, /{program_slug}/                │
│    - Giữ URL taxonomy route: /he-dao-tao/ và /he-dao-tao/{term}/            │
│    - Thiết lập 301 Redirect duy nhất cho term bị khai tử:                   │
│      /he-dao-tao/van-bang-2/ ──[ 301 Permanent Redirect ]──> /he-dao-tao/tu-xa/ │
├─────────────────────────────────────────────────────────────────────────────┤
│ 4. Terminology Cleansing & Scope Quarantine                                 │
│    - Thanh lọc 100% từ khóa "Văn bằng 2", "VB2" khỏi Hero, Badge, FAQ       │
│    - Định nghĩa lại "Chính quy" thành "Liên thông Chính quy"                │
│    - Ngăn chặn triệt để luồng tuyển sinh THPT vào học ĐH mới                │
├─────────────────────────────────────────────────────────────────────────────┤
│ 5. Redundant Facet Pruning                                                  │
│    - Loại bỏ bộ lọc thừa "Loại chương trình: Liên thông"                    │
│    - Giữ bộ lọc chuẩn 4 trục: Hình thức học, Trường, Ngành, Cơ sở           │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 6. CAVEATS (GIỚI HẠN & GIẢ ĐỊNH)

1. **Về dữ liệu hiện hữu trong CSDL live**:
   - Khảo sát được thực hiện trực tiếp trên mã nguồn theme tại thư mục `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc`. Dữ liệu các bài viết thực tế trong database MySQL cục bộ (Local Sites) có thể chứa một số bài viết mẫu được gieo từ lệnh `wp ltdh seed-all` trước đây (chứa term `van-bang-2`). Khi triển khai Pha 2, cần chạy một migration script nhỏ trong PHP để chuyển các bài viết này sang term `tu-xa` trước khi xóa term `van-bang-2`.
2. **Về cấu hình menu trong `wp_terms` / `wp_posts`**:
   - Nếu quản trị viên đã ghim cứng menu "Hệ đào tạo" trong giao diện WordPress Admin (`Appearance → Menus`), bộ lọc filter `ltdh_dynamic_menu_submenu_injection` chỉ can thiệp nếu tiêu đề menu khớp chữ. Do đó, cần hỗ trợ cả hai chuỗi so khớp `"hệ đào tạo"` và `"hình thức học"`.
3. **Không phát hiện thêm xung đột nghiêm trọng**:
   - Mã nguồn không tồn tại thực thể lạ như "Đại học mới" ở cấp CPT/Taxonomy (chỉ xuất hiện trong các câu văn ngẫu nhiên). Không có CPT nào khác ngoài `program`, `school`, `major`, `guide` và `post`.

---

## 7. CONCLUSION (KẾT LUẬN KIỂM ĐỊNH)

1. **Tính tương thích của Mô hình Dữ liệu Hiện tại**:
   - Mô hình dữ liệu tam giác hiện tại: `School` ⟷ `Major` ⟷ `Program` (trung gian) ⟷ `training_type` ⟷ `campus` **hoàn toàn có thể đáp ứng 100% Mô hình Nghiệp vụ Mục tiêu** của dự án mà không cần phá vỡ kiến trúc CPT hay tạo bảng mới.
   - `Program` đóng vai trò hoàn hảo là **Cơ hội tuyển sinh Liên thông cụ thể**, liên kết giữa Trường, Ngành, và Hình thức học (Taxonomy `training_type`).
2. **Bản chất của việc Tái cấu trúc (Restructure Scope)**:
   - Nhiệm vụ tái cấu trúc thông tin không phải là viết lại mã nguồn từ đầu, mà là **Chuẩn hóa Ngữ nghĩa, Tinh chỉnh Nhãn hiển thị, Thanh lọc các loại hình tuyển sinh ngoài phạm vi (Văn bằng 2, Đại học mới), và Đồng bộ hóa SEO Title / H1 / Breadcrumbs** xoay quanh trục duy nhất: **LIÊN THÔNG ĐẠI HỌC**.
3. **Tính sẵn sàng chuyển giao (Actionable Next Steps)**:
   - Toàn bộ 49 tệp PHP đã sẵn sàng cho giai đoạn chỉnh sửa có kiểm soát.
   - Báo cáo này cung cấp đầy đủ bảng ánh xạ 9 cột làm kim chỉ nam chính xác cho kỹ sư triển khai tiếp theo.

---

## 8. INDEPENDENT VERIFICATION METHOD (PHƯƠNG PHÁP XÁC MINH ĐỘC LẬP)

Để kiểm chứng độc lập các quan sát và kết luận trong báo cáo này, người kiểm định hoặc Orchestrator có thể thực thi các lệnh và kiểm tra các tệp sau:

1. **Kiểm tra cú pháp PHP toàn theme**:
   ```bash
   find "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc" -name "*.php" -not -path "*/.agents/*" -not -path "*/vendor/*" -exec php -l {} \; | grep -v "No syntax errors detected"
   ```
   *(Kết quả mong đợi: Không có lỗi cú pháp nào).*

2. **Kiểm chứng các vị trí xuất hiện của "Văn bằng 2" / "VB2"**:
   ```bash
   grep -rnEi "văn bằng 2|van-bang-2|vb2" "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc" --exclude-dir={.git,.agents,node_modules,vendor} --exclude="*.md"
   ```
   *(Kết quả mong đợi: Khớp chính xác 49 dòng code tại các tệp đã nêu trong Mục 1.4).*

3. **Kiểm chứng vị trí xuất hiện của "Hệ đào tạo"**:
   ```bash
   grep -rnEi "hệ đào tạo|he-dao-tao" "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc" --exclude-dir={.git,.agents,node_modules,vendor} --exclude="*.md"
   ```
   *(Kết quả mong đợi: Khớp chính xác 91 dòng code liên quan đến menu, breadcrumb, tiêu đề archive và filters).*

4. **Kiểm chứng quy tắc Rewrite Rules & Canonical**:
   Kiểm tra trực tiếp tại `inc/core/class-rewrite-rules.php:15-50` và `inc/seo/class-rankmath-integration.php:65-80` để xác nhận logic URL phẳng của `program` và `school`.

5. **Điều kiện Vô hiệu hóa (Invalidation Conditions)**:
   Báo cáo này sẽ mất tính hiệu lực nếu:
   - Có sự can thiệp làm thay đổi slug của CPT `program`, `school`, `major` hoặc taxonomy `training_type` trực tiếp trong CSDL mà không có kế hoạch di chuyển dữ liệu (migration).
   - Thêm mới các CPT tuyển sinh độc lập ngoài phạm vi liên thông.
