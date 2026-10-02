# BÁO CÁO KIỂM TOÁN & KHẢO SÁT TOÀN DIỆN
# TEMPLATES, NAVIGATION, UI & FILTER ENGINE DỰ ÁN LIENTHONGDAIHOC.COM

**Người thực hiện**: Templates & UI Explorer (`explorer_survey_2`)  
**Ngày thực hiện**: 2026-10-01  
**Mục tiêu**: Tái cấu trúc Kiến trúc thông tin (Information Architecture), Thuật ngữ & Templates dự án `lienthongdaihoc.com` xoay quanh phạm vi duy nhất: **LIÊN THÔNG ĐẠI HỌC**.

---

## 1. OBSERVATION (Quan sát trực tiếp & Dẫn chứng mã nguồn)

Toàn bộ 50 file mã nguồn PHP và các tài nguyên frontend (CSS, JS) của theme đã được rà soát trực tiếp. Dưới đây là các quan sát thực tế kèm đường dẫn file và số dòng code cụ thể:

### 1.1. Menu Điều hướng & Cấu hình Defaults
- **File**: `inc/config/class-defaults.php`
  - **Dòng 33–49**: Menu mặc định primary và mobile sử dụng các nhãn không chuẩn:
    ```php
    'primary' => [
        [ 'url' => '/',                   'label' => 'Trang chủ' ],
        [ 'url' => '/truong-doi-tac/',   'label' => 'Trường đối tác' ],
        [ 'url' => '/nganh-hoc/',         'label' => 'Chuyên ngành' ],
        [ 'url' => '/he-dao-tao/',        'label' => 'Hệ đào tạo' ],
        [ 'url' => '/tin-tuyen-sinh/',    'label' => 'Tin tức' ],
        [ 'url' => '/lien-he/',           'label' => 'Liên hệ' ],
    ],
    'mobile' => [
        ...
        [ 'url' => '/he-dao-tao/tu-xa/',  'label' => 'Chương trình' ],
    ```
    *Dẫn chứng*: Menu primary dùng `"Hệ đào tạo"`, `"Chuyên ngành"`, `"Trường đối tác"`, `"Tin tức"`. Mobile menu trỏ link `/he-dao-tao/tu-xa/` nhưng lại gán nhãn `"Chương trình"`.
  - **Dòng 64–68**: Hero badge mặc định chứa thuật ngữ ngoài phạm vi:
    ```php
    'hero_badges' => [
        [ 'text' => '50+ chương trình', 'subtext' => 'Liên thông, VB2, Từ xa' ],
        [ 'text' => '30+ trường ĐH',     'subtext' => 'Đối tác uy tín toàn quốc' ],
        [ 'text' => 'Miễn giảm tín chỉ', 'subtext' => 'Rút ngắn thời gian học' ],
    ],
    ```
    *Dẫn chứng*: Xuất hiện cụm từ `"VB2"` và đặt `"Từ xa"` ngang hàng với `"Liên thông"`.
- **File**: `inc/core/class-menus.php`
  - **Dòng 137 & Dòng 180**: Logic inject menu con phụ thuộc vào chuỗi tên cứng:
    ```php
    if ($title === 'hệ đào tạo') { ... }
    if ($title === 'chuyên ngành') { ... }
    ```
    *Dẫn chứng*: Nếu đổi tiêu đề menu trong WordPress Admin mà không cập nhật hook, submenu hệ đào tạo và ngành hot sẽ không được inject.
- **File**: `footer.php`
  - **Dòng 81–101**: Cột 3 footer chứa link chết và thuật ngữ sai phạm vi:
    ```html
    <a href="<?php echo esc_url( home_url('/he-dao-tao/tu-xa/') ); ?>" ...>Học đại học từ xa</a>
    <a href="#" ...>Cao đẳng online / VB2</a>
    <a href="#" ...>Liên thông Đại Học chính quy</a>
    <a href="#" ...>Trung Cấp lên Đại học</a>
    <a href="#" ...>Đại học tại chức / VLVH</a>
    ```
    *Dẫn chứng*: Xuất hiện 4 liên kết `href="#"` (dead links), xuất hiện `"VB2"` và `"Cao đẳng online"`.

### 1.2. Taxonomy Archive `taxonomy-training_type.php` & Routing
- **File**: `taxonomy-training_type.php`
  - **Dòng 171–173**: H1 trang lưu trữ hệ đào tạo:
    ```php
    <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
        <?php echo $active_type_term ? 'Hệ đào tạo: ' . esc_html( $active_type_term->name ) : 'Tất cả chương trình đào tạo'; ?>
    </h1>
    ```
    *Dẫn chứng*: Render `"Hệ đào tạo: Từ xa"`, `"Hệ đào tạo: Chính quy"`, không phản ánh đúng chuẩn SEO `"Liên thông từ xa"`, `"Liên thông chính quy"`.
  - **Dòng 250**: Nhãn hàng filter pills:
    ```html
    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Hệ đào tạo:</span>
    ```
  - **Dòng 338**: Logic gán class badge:
    ```php
    } elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
    ```
    *Dẫn chứng*: Coi `"liên thông"` và `"văn bằng 2"` là các term con của `training_type`.
- **File**: `inc/core/class-rewrite-rules.php`
  - **Dòng 247–254**: Redirect cứng `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`:
    ```php
    if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
        $redirect_url = home_url( '/he-dao-tao/tu-xa/' );
        ...
        wp_redirect( $redirect_url, 301 );
        exit;
    }
    ```
    *Dẫn chứng*: Mọi lượt truy cập `/chuong-trinh/` đều bị ép sang một hình thức duy nhất là `/he-dao-tao/tu-xa/`, làm méo mó cấu trúc điều hướng.

### 1.3. Single Program Template `single-program.php`
- **File**: `single-program.php`
  - **Rà soát 12 mục bắt buộc**:
    1. *Trường*: Có (Banner, logo, mini bar dòng 201-222, sidebar card dòng 973-1036).
    2. *Ngành*: Yếu. Không có block/thẻ riêng thể hiện rõ mã ngành, nhóm ngành, link về trang ngành học cha.
    3. *Hình thức học*: Có (`$learning_details['mode']`, badge), nhưng nhãn còn dùng "Hệ đào tạo".
    4. *Đối tượng tuyển sinh*: **THIẾU BLOCK RIÊNG**. Tab subtitle dòng 102 ghi "Đối tượng & tiêu chuẩn", nhưng section `#dieu-kien-xet-tuyen` (dòng 548) chỉ render `$requirements` (nội dung môn thi/xét tuyển). Không có khung rõ ràng về đối tượng: Tốt nghiệp Trung cấp, Cao đẳng (đúng ngành / gần ngành / khác ngành).
    5. *Điều kiện*: Có (Môn thi tuyển Toán, Toán rời rạc, Cấu trúc dữ liệu hoặc xét tuyển, dòng 557-615).
    6. *Thời gian học*: Có (Khung thời gian chuẩn, quy định miễn giảm môn, dòng 680-814).
    7. *Học phí*: Có (Đơn giá, số tín chỉ, lộ trình học phí, dòng 626-678).
    8. *Địa điểm/Phương thức*: Có (Campus, chế độ học online/cuối tuần, dòng 265-272).
    9. *Bằng cấp*: **HOÀN TOÀN THIẾU**. Tìm kiếm chuỗi `"bằng cấp"` trong file trả về 0 kết quả! Người học không thấy thông tin cấp bằng Cử nhân/Kỹ sư, Thông tư 27/2019/TT-BGDĐT không ghi hình thức đào tạo, giá trị thi cao học/công chức.
    10. *Hồ sơ*: Có (`#ho-so-can-nop`, tải mẫu phiếu tuyển sinh, dòng 863-899).
    11. *Thời gian tuyển sinh*: Có (`#lich-tuyen-sinh`, timeline các đợt tuyển sinh, dòng 280-543).
    12. *Form đăng ký*: Có (Sidebar form dòng 1039-1053, modal tải tài liệu dòng 1185-1248, fixed CTA bar dòng 1167-1182).
  - **Dòng 195**: Cảnh báo tạm ngưng bị hardcode chữ "hệ Chính quy":
    ```html
    <p class="text-xs text-red-700 mt-1">Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu...</p>
    ```
    *Dẫn chứng*: Chương trình Từ xa hoặc Vừa học vừa làm nếu bị đóng chỉ tiêu vẫn hiển thị câu thông báo "hệ Chính quy"!
  - **Dòng 747**: Ghi chú miễn môn đề cập văn bằng 1:
    ```html
    <p class="text-slate-600">Học viên được xem xét miễn giảm các môn đại cương và môn chuyên ngành dựa trên bảng điểm tốt nghiệp trung cấp, cao đẳng hoặc văn bằng 1 đã có.</p>
    ```

### 1.4. Template Trường & Ngành
- **File**: `archive-school.php`
  - **Dòng 61 & 70**: Tiêu đề banner và section:
    Banner: `"Trường Đại học Đối tác"` (từ `template-parts/banner.php:61`).  
    Section: `"Trường đại học nổi bật"` -> Thiếu ngữ cảnh trọng tâm: "Trường đại học tuyển sinh Liên thông".
- **File**: `single-school.php`
  - **Dòng 58–59**: Tab navigation:
    ```php
    'id'       => 'chuong-trinh-tuyen-sinh',
    'title'    => 'Lớp tuyển sinh',
    'subtitle' => 'Chương trình đang mở',
    ```
    *Dẫn chứng*: Dùng từ `"Lớp tuyển sinh"` thay vì `"Tuyển sinh liên thông"` / `"Chương trình liên thông"`.
  - **Dòng 501**: Nhãn nhóm:
    ```html
    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hệ đào tạo:</div>
    ```
- **File**: `archive-major.php`
  - **Dòng 25**: Banner H1 là `"Chuyên Ngành"` (từ `template-parts/banner.php:64`).
  - **Dòng 86**: Mô tả:
    ```html
    <p class="text-sm font-medium text-slate-500">Danh sách các ngành đào tạo tuyển sinh đại học trực tuyến và liên thông.</p>
    ```
    *Dẫn chứng*: Tách rời "đại học trực tuyến" và "liên thông" như 2 loại hình tuyển sinh độc lập.
- **File**: `single-major.php`
  - **Dòng 470**: Nhãn nhóm dùng `"Hệ đào tạo:"`.
  - Cấu trúc gom nhóm theo trường và hình thức học rất tốt: Ngành -> Trường tuyển sinh -> Hình thức học (Từ xa, VLVH, Chính quy) -> Offerings cụ thể.

### 1.5. Bộ lọc & AJAX Filter Engine
- **File**: `front-page.php`
  - **Dòng 31**: H1 ẩn cho SEO:
    ```html
    <h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học, Văn Bằng 2 & Đại Học Từ Xa</h1>
    ```
    *Dẫn chứng*: Chứa trực tiếp cụm `"Văn Bằng 2 & Đại Học Từ Xa"`.
  - **Dòng 126**: Search form action trỏ cứng về `/he-dao-tao/tu-xa/`:
    ```html
    <form action="<?php echo esc_url(home_url('/he-dao-tao/tu-xa/')); ?>" method="GET" class="space-y-3 md:space-y-0">
    ```
  - **Dòng 159**: Select option: `<option value="">-- Chọn hệ học --</option>`.
- **File**: `inc/core/class-helpers.php`
  - **Dòng 736–742**: Ánh xạ hình thức học:
    ```php
    $mode_map = [
        'tu-xa'           => 'Học online 100%',
        'vua-hoc-vua-lam' => 'Học tập trung cuối tuần',
        'van-bang-2'      => 'Học tập trung / Online linh hoạt',
    ];
    ```
    *Dẫn chứng*: Đưa `'van-bang-2'` vào danh mục hình thức học (`learning_mode`).
- **File**: `inc/eligibility-rules.php`
  - **Dòng 18–24**: Ma trận đào tạo chứa đồng thời `'lien-thong'`, `'van-bang-2'`, và `'thpt'`:
    ```php
    'thpt'      => [ 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
    'trung-cap' => [ 'lien-thong', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
    'cao-dang'  => [ 'lien-thong', 'tu-xa', 'van-bang-2', 'vua-hoc-vua-lam', 'chinh-quy' ],
    'dai-hoc'   => [ 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam' ],
    ```
    *Dẫn chứng*: Coi Liên thông là một nhánh con cùng cấp với Từ xa và Văn bằng 2, đồng thời cho phép đầu vào THPT học Chính quy/VLVH/Từ xa (đây là Đại học mới, không phải Liên thông).

---

## 2. LOGIC CHAIN (Chuỗi suy luận từ quan sát đến kết luận)

1. **Từ Quan sát 1.1, 1.2, 1.5**: Mã nguồn hiện tại chứa nhiều dấu vết của mô hình "Cổng thông tin tuyển sinh tổng hợp" (từng bao gồm cả Văn bằng 2, Đại học từ xa độc lập, và thậm chí tuyển sinh từ THPT).
2. **Đối chiếu với Yêu cầu Nghiệp vụ Cốt lõi**:
   - Tên miền và định vị thương hiệu là `lienthongdaihoc.com`.
   - Quy chuẩn Bộ GD&ĐT (Luật Giáo dục Đại học, Quyết định 18/2017/QĐ-TTg, Thông tư 08/2021/TT-BGDĐT): **Liên thông đại học** là hình thức đào tạo dành cho người đã có bằng tốt nghiệp Trung cấp hoặc Cao đẳng (hoặc người đã có bằng ĐH muốn liên thông sang ngành khác) để học tiếp lên trình độ đại học.
   - Các phương thức: **Chính quy**, **Vừa học vừa làm**, **Từ xa** là các *hình thức tổ chức đào tạo (Study Mode)* BÊN TRONG liên thông đại học, KHÔNG PHẢI các sản phẩm tuyển sinh cạnh tranh ngang hàng với liên thông.
3. **Từ Quan sát 1.2 & 1.3**:
   - Khi coi "Liên thông" là 1 term ngang hàng với "Từ xa" trong taxonomy `training_type`, hệ thống xuất hiện các lỗi nghịch lý:
     * Bộ lọc có nút lọc "Hệ Liên thông" bên cạnh "Hệ Từ xa" -> Khiến người dùng hiểu nhầm rằng các chương trình "Từ xa" không phải là "Liên thông", và ngược lại.
     * Trang `/chuong-trinh/` bị redirect cứng 301 sang `/he-dao-tao/tu-xa/` -> Tước đoạt cơ hội tiếp cận các chương trình liên thông Vừa học vừa làm và Chính quy.
4. **Từ Quan sát 1.3**:
   - Trong tuyển sinh liên thông tại Việt Nam, **Văn bằng tốt nghiệp (Bằng cấp)** là mối quan tâm hàng đầu của người học (lo ngại bằng có ghi chữ "Từ xa", "Tại chức", bằng có được thi công chức hay học thạc sĩ không). Trang chủ đã giải thích rất tốt về Thông tư 27/2019/TT-BGDĐT (Quan sát 1.5, dòng 387-444), nhưng trang `single-program.php` lại **thiếu hoàn toàn** mục Bằng cấp. Đây là lỗ hổng chuyển đổi (CRO) và thông tin rất nghiêm trọng.
5. **Kết luận suy luận**: Cần chuẩn hóa toàn diện từ vựng, nhãn điều hướng, taxonomy terms và các section của templates về đúng mô hình phân cấp:
   `Trường Đại học -> Tuyển sinh Liên thông -> Hình thức học (Chính quy, VLVH, Từ xa) -> Ngành đào tạo -> Cơ hội tuyển sinh cụ thể`.

---

## 3. CAVEATS (Phạm vi giới hạn & Giả định)

1. **Mã nguồn nguyên bản không bị sửa đổi**: Đây là giai đoạn khảo sát (Phase 1: Read-only Audit). Chưa có file mã nguồn nào bị chỉnh sửa trong đợt khảo sát này.
2. **Bảo tồn URLs công khai**: Do website đã hoàn thiện và có thể đã được lập chỉ mục SEO (Google Index), kế hoạch can thiệp tuyệt đối KHÔNG đổi cấu trúc URL công khai:
   - Trường: `/{slug}/` (giữ nguyên)
   - Ngành: `/nganh-{slug}/` (giữ nguyên)
   - Chương trình: `/{slug}/` (giữ nguyên)
   - Hình thức học: `/he-dao-tao/{slug}/` (giữ nguyên URL path, chỉ đổi nhãn hiển thị và nội dung)
3. **Giả định về dữ liệu CSDL**: Giả định taxonomy `training_type` trong CSDL hiện có các terms: `tu-xa`, `vua-hoc-vua-lam`, `chinh-quy`. Bất kỳ term nào thừa như `van-bang-2` hoặc `lien-thong` cần được dọn dẹp hoặc gỡ liên kết trong giai đoạn thực thi CSDL.

---

## 4. CONCLUSION (Bảng ánh xạ Current -> Target & Đánh giá)

### 4.1. Bảng Ánh Xạ Kiểm Toán Theo Schema Bắt Buộc

| CURRENT ENTITY | CURRENT NAME | CURRENT PURPOSE | CURRENT TAXONOMY | CURRENT RELATIONSHIPS | CURRENT URL | CURRENT TEMPLATE | TARGET CONCEPT | REQUIRED CHANGE |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Primary Menu: Hệ đào tạo** | Hệ đào tạo | Lối vào trang archive hệ đào tạo & inject menu con | `training_type` | Cha của các terms: `tu-xa`, `chinh-quy`, `vua-hoc-vua-lam` | `/he-dao-tao/` | `header.php`, `class-menus.php:137`, `class-defaults.php:37` | **Hình thức học** (hoặc **Liên thông**) | Đổi label thành "Hình thức học" (hoặc "Liên thông"). Submenu gồm: "Liên thông từ xa", "Liên thông vừa học vừa làm", "Liên thông chính quy". Sửa hook `ltdh_dynamic_menu_submenu_injection` nhận diện title mới. |
| **Primary Menu: Chuyên ngành** | Chuyên ngành | Lối vào danh bạ ngành & inject 5 ngành hot | CPT `major` | Trỏ tới archive major & 5 bài viết major hot | `/nganh-hoc/` | `header.php`, `class-menus.php:180`, `class-defaults.php:36` | **Ngành học** (Ngành đào tạo) | Đổi label thành "Ngành học" chuẩn quy chế tuyển sinh. Cập nhật hook `class-menus.php` nhận diện nhãn mới. |
| **Primary Menu: Trường đối tác** | Trường đối tác | Lối vào danh bạ trường đại học | CPT `school` | Trỏ tới archive school | `/truong-doi-tac/` | `header.php`, `class-defaults.php:35` | **Trường đại học** | Đổi label thành "Trường đại học" (nhất quán với thực thể cấp 1). Giữ nguyên URL `/truong-doi-tac/`. |
| **Primary Menu: Tin tức** | Tin tức | Lối vào bài viết cẩm nang & tin tức | Post / Guide | Trỏ tới archive post/guide | `/tin-tuyen-sinh/` | `header.php`, `class-defaults.php:38` | **Kiến thức liên thông** | Đổi label thành "Kiến thức liên thông" hoặc "Cẩm nang tuyển sinh" để tập trung đúng vào tệp người học liên thông. |
| **Mobile Menu: Chương trình** | Chương trình | Lối tắt trên menu di động | `training_type:tu-xa` | Trỏ cứng về term từ xa | `/he-dao-tao/tu-xa/` | `class-defaults.php:45` | **Hình thức học** | Xóa link trỏ cứng lệch khái niệm (`/he-dao-tao/tu-xa/` dán nhãn "Chương trình"). Thay bằng lối vào chuẩn tới "Hình thức học" (`/he-dao-tao/`). |
| **Footer Column 3** | Chương trình đào tạo | Danh sách link chân trang | `training_type` | Chứa 4 link chết `href="#"` và nhãn VB2 | `/he-dao-tao/tu-xa/`, `href="#"` | `footer.php:75-102` | **Hình thức học Liên thông** | Xóa bỏ "Cao đẳng online / VB2", "Trung Cấp lên Đại học". Thay bằng link sống: Liên thông từ xa (`/he-dao-tao/tu-xa/`), Liên thông VLVH (`/he-dao-tao/vua-hoc-vua-lam/`), Liên thông chính quy (`/he-dao-tao/chinh-quy/`), Ngành học (`/nganh-hoc/`), Trường ĐH (`/truong-doi-tac/`). |
| **Taxonomy Archive Template** | `taxonomy-training_type.php` | Archive hiển thị chương trình theo hệ | `training_type` | `program` ⟷ `training_type`, `school`, `major` | `/he-dao-tao/[slug]/` & `/he-dao-tao/` | `taxonomy-training_type.php` | **Trang lưu trữ Hình thức học Liên thông** | 1. Sửa H1: `Liên thông [Tên hình thức]` (VD: Liên thông từ xa, Liên thông chính quy). Base archive: "Các chương trình tuyển sinh Liên thông Đại học".<br>2. Sửa nhãn "Hệ đào tạo:" thành "Hình thức học:".<br>3. Bỏ badge/filter "Liên thông" và "Văn bằng 2". |
| **Program Archive Template** | `archive-program.php` | Archive post type `program` | `training_type` | `program` CPT | `/chuong-trinh/` (bị 301 sang `/he-dao-tao/tu-xa/`) | `archive-program.php`, `class-rewrite-rules.php:247` | **Danh mục Chương trình Tuyển sinh** | 1. Hủy bỏ redirect cứng sang `/he-dao-tao/tu-xa/` tại `class-rewrite-rules.php:247`. Cho phép `/chuong-trinh/` 301 về `/he-dao-tao/` (tổng thể mọi hình thức).<br>2. Sửa form action sang `/he-dao-tao/`. |
| **Single Program Template** | `single-program.php` | Trang chi tiết cơ hội tuyển sinh cụ thể | `training_type`, `campus` | `program` ⟷ `school`, `major` | `/{slug}/` | `single-program.php` | **Chi tiết cơ hội tuyển sinh Liên thông** | 1. **Bổ sung mục Bằng cấp**: Hiển thị giá trị văn bằng theo Thông tư 27/2019/TT-BGDĐT.<br>2. **Bổ sung/Làm rõ mục Đối tượng tuyển sinh**: Phân định rõ đối tượng có bằng Trung cấp/Cao đẳng/ĐH.<br>3. Sửa câu cảnh báo dòng 195 (bỏ chữ "hệ Chính quy" bị gắn cứng).<br>4. Thêm hiển thị thông tin Ngành đào tạo cha.<br>5. Chuẩn hóa Breadcrumbs. |
| **School Archive Template** | `archive-school.php` | Danh bạ các trường đại học đối tác | `region` | `school` ⟷ `program` | `/truong-doi-tac/` | `archive-school.php`, `banner.php:61` | **Danh bạ Trường Đại học tuyển sinh Liên thông** | 1. Banner H1: "Trường Đại học tuyển sinh Liên thông".<br>2. Subtitle: Nhấn mạnh mạng lưới các trường đại học đào tạo liên thông uy tín.<br>3. Card: Thể hiện các hình thức học liên thông trường đang mở. |
| **Single School Template** | `single-school.php` | Hồ sơ tuyển sinh của 1 trường đại học | `region`, `training_type` | `school` ⟷ `program`, `major` | `/{slug}/` | `single-school.php` | **Trang tuyển sinh Liên thông của Trường** | 1. Sửa Tab "Lớp tuyển sinh" -> "Tuyển sinh liên thông".<br>2. Sửa nhãn "Hệ đào tạo:" -> "Hình thức học:".<br>3. Khẳng định trường tuyển sinh các ngành liên thông đại học. |
| **Major Archive Template** | `archive-major.php` | Danh bạ ngành học | `major_cat` | `major` ⟷ `program` | `/nganh-hoc/` | `archive-major.php`, `banner.php:64` | **Danh bạ Ngành đào tạo Liên thông Đại học** | 1. Banner H1: Sửa "Chuyên Ngành" thành "Ngành đào tạo Liên thông Đại học".<br>2. Sửa mô tả dòng 86 (bỏ cụm từ tách rời "đại học trực tuyến và liên thông"). |
| **Single Major Template** | `single-major.php` | Chi tiết ngành học & các trường tuyển sinh | `major_cat` | `major` ⟷ `school`, `program` | `/nganh-{slug}/` | `single-major.php` | **Chi tiết Ngành đào tạo Liên thông Đại học** | 1. Sửa nhãn "Hệ đào tạo:" -> "Hình thức học:" tại dòng 470.<br>2. Khẳng định đối tượng người học liên thông trong tổng quan ngành. |
| **Homepage Hero & H1** | `front-page.php` | Hero banner & Tiêu đề SEO trang chủ | Không | Toàn trang chủ | `/` | `front-page.php:31, 126`, `class-defaults.php:65` | **Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học** | 1. Sửa H1 ẩn: Xóa bỏ `"Văn Bằng 2 & Đại Học Từ Xa"`, đặt thành `"Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học Toàn Quốc"`.<br>2. Sửa hero badge: Thay `'Liên thông, VB2, Từ xa'` thành `'Chính quy, VLVH, Từ xa'`.<br>3. Sửa form action: Từ `/he-dao-tao/tu-xa/` thành `/he-dao-tao/`. |
| **Eligibility Wizard** | `wizard.php` | Form trắc nghiệm điều kiện tuyển sinh | `training_type`, `campus` | Form kiểm tra tương thích chương trình | `/kiem-tra-dieu-kien/` | `template-parts/eligibility/wizard.php`, `inc/eligibility-rules.php` | **Công cụ kiểm tra điều kiện Liên thông** | 1. Đổi option `dai-hoc` từ "Học Văn bằng 2" sang "Liên thông ngành thứ hai".<br>2. Sửa `inc/eligibility-rules.php`: Bỏ `van-bang-2` và `lien-thong` khỏi compatibility matrix; chỉ giữ 3 hình thức học: `tu-xa`, `vua-hoc-vua-lam`, `chinh-quy`. |
| **Program Comparison** | `page-compare-program.php` | Bảng so sánh 2-3 chương trình | `training_type` | So sánh thông số các `program` | `/so-sanh-chuong-trinh/` | `page-compare-program.php`, `program-table.php` | **So sánh Chương trình Liên thông** | 1. Sửa link nút trống từ `/he-dao-tao/tu-xa/` thành `/he-dao-tao/`.<br>2. Xóa bỏ chữ "văn bằng 2" trong FAQ so sánh.<br>3. Hợp nhất hàng "Hệ đào tạo" và "Hình thức học" trong bảng so sánh thành 1 hàng "Hình thức học". |
| **Dynamic SEO & Schema Engine** | `class-rankmath-integration.php` | Tự động tạo title, description, schema | `training_type`, `school`, `major` | Hooks vào Rank Math & native `wp_head` | Toàn bộ URL | `inc/seo/class-rankmath-integration.php` | **Chuẩn hóa SEO Title / Schema Liên thông** | 1. Thêm hook tạo dynamic title cho taxonomy `training_type`: `Liên thông [Hình thức học] | Tuyển sinh [Năm]`.<br>2. Dynamic title cho major: `Liên thông ngành [Tên ngành] | Tuyển sinh [Năm]`.<br>3. Dynamic title cho school: `Tuyển sinh Liên thông [Tên trường] | [Năm]`.<br>4. Schema Course: bổ sung `educationalCredentialAwarded` là Bằng Cử nhân/Kỹ sư. |

---

### 4.2. Danh Sách Các Bộ Lọc Dư Thừa Cần Loại Bỏ & Chuẩn Hóa
1. **Loại bỏ bộ lọc "Hệ đào tạo: Liên thông"**:
   - *Lý do*: 100% chương trình trên website đều là chương trình Liên thông. Việc tồn tại term `lien-thong` bên trong taxonomy `training_type` (ngang hàng với `tu-xa`, `chinh-quy`) là sai lệch bản chất nghiệp vụ.
   - *Giải pháp*: Không render pill tab hoặc dropdown option có tên "Liên thông". Danh mục hình thức học chỉ bao gồm 3 phương thức: **Từ xa**, **Vừa học vừa làm**, **Chính quy**.
2. **Loại bỏ bộ lọc / nhãn "Văn bằng 2"**:
   - *Lý do*: Yêu cầu nghiệp vụ cốt lõi cấm đưa Văn bằng 2 vào như một sản phẩm tuyển sinh độc lập.
   - *Giải pháp*: Xóa bỏ term `van-bang-2` khỏi badge matching trong `taxonomy-training_type.php:338`, `archive-program.php:338`, `class-query-filters.php:200` và `class-helpers.php:739`.

---

### 4.3. Đánh Giá Khuyết Thiếu 12 Mục Của Template `single-program.php`

| Hạng mục nghiệp vụ | Trạng thái hiện tại | Vị trí code hiện tại | Đánh giá & Yêu cầu can thiệp |
| :--- | :--- | :--- | :--- |
| **1. Trường** | Đạt | Dòng 201–222 (Mobile), Dòng 973–1036 (Desktop sidebar) | Đầy đủ logo, tên trường, địa chỉ, link chi tiết trường. |
| **2. Ngành** | Yếu | Lấy từ `major_relationship` nhưng thiếu block trực quan | Cần hiển thị rõ Mã ngành, Nhóm ngành và đường link trỏ về trang ngành học cha ngay tại phần thông tin cốt lõi. |
| **3. Hình thức học** | Đạt (Cần chỉnh nhãn) | Dòng 270–272 (`$learning_details['mode']`) | Cần bỏ chữ "Hệ" trong badge ("Hệ Từ xa" -> "Từ xa"). |
| **4. Đối tượng tuyển sinh** | **THIẾU** | Không có block riêng (bị gộp vào điều kiện) | **Bổ sung block Đối tượng tuyển sinh**: Phân định rõ tiêu chuẩn đầu vào: Đã tốt nghiệp Trung cấp (1.5 - 2 năm), Tốt nghiệp Cao đẳng (1 - 1.5 năm), Đã có bằng ĐH khác (1 - 1.5 năm). Khẳng định không tuyển sinh trực tiếp từ THPT. |
| **5. Điều kiện xét tuyển** | Đạt | Dòng 548–622 (`#dieu-kien-xet-tuyen`) | Rõ ràng về phương thức (Thi tuyển 3 môn hoặc Xét tuyển học bạ/bảng điểm). |
| **6. Thời gian học** | Đạt | Dòng 680–814 (`#hoc-phi-thoi-gian`) | Lộ trình chuẩn, quy định miễn giảm môn học phần. |
| **7. Học phí** | Đạt | Dòng 626–678 (`#hoc-phi-thoi-gian`) | Đơn giá tín chỉ, tổng số tín chỉ, niên khóa, lộ trình tăng học phí. |
| **8. Địa điểm / Phương thức** | Đạt | Dòng 265–272 (`campus` & `learning_mode`) | Rõ ràng về cơ sở học và phương thức học online/cuối tuần. |
| **9. Bằng cấp** | **HOÀN TOÀN THIẾU** | Không có trong file | **Bổ sung section Bằng cấp**: Trích dẫn Thông tư 27/2019/TT-BGDĐT: Bằng Cử nhân / Kỹ sư chính quy do Trường Đại học cấp, không phân biệt hình thức đào tạo, có giá trị học lên Thạc sĩ/Tiến sĩ, thi tuyển công chức nhà nước và nâng bậc lương. |
| **10. Hồ sơ tuyển sinh** | Đạt | Dòng 863–899 (`#ho-so-can-nop`) | Danh mục giấy tờ cần nộp + Nút tải mẫu phiếu đăng ký tuyển sinh chính thức (PDF). |
| **11. Thời gian tuyển sinh** | Đạt | Dòng 280–543 (`#lich-tuyen-sinh`) | Timeline các đợt phát hành, hạn nộp, ôn tập, xét tuyển/thi tuyển. |
| **12. Form đăng ký tư vấn** | Đạt | Dòng 1039–1053 (`#register`), dòng 1167–1182 (Mobile bar), dòng 1185–1248 (Modal) | Đầy đủ form tư vấn và tải tài liệu. |

---

## 5. MINIMAL SAFE INTERVENTION PLAN (Kế hoạch Can thiệp Tối thiểu An toàn)

Kế hoạch can thiệp được chia làm 4 giai đoạn độc lập, đảm bảo **không làm thay đổi layout CSS/Tailwind**, **không phá vỡ URLs/SEO**, và **dễ dàng kiểm tra ngược**:

### Giai đoạn 1: Chuẩn hóa Thuật ngữ, Defaults & Navigation
1. **File `inc/config/class-defaults.php`**:
   - Đổi nhãn menu primary: `"Trường đối tác"` -> `"Trường đại học"`, `"Chuyên ngành"` -> `"Ngành học"`, `"Hệ đào tạo"` -> `"Hình thức học"`, `"Tin tức"` -> `"Kiến thức liên thông"`.
   - Sửa mobile menu default: thay `/he-dao-tao/tu-xa/` nhãn "Chương trình" bằng link hợp lệ.
   - Sửa `hero_badges` default: Thay `'Liên thông, VB2, Từ xa'` thành `'Chính quy, VLVH, Từ xa'`.
2. **File `inc/core/class-menus.php`**:
   - Cập nhật dòng 137: `if ( in_array( $title, [ 'hệ đào tạo', 'hình thức học', 'liên thông' ], true ) )`.
   - Cập nhật dòng 180: `if ( in_array( $title, [ 'chuyên ngành', 'ngành học', 'ngành đào tạo' ], true ) )`.
3. **File `footer.php`**:
   - Thay thế các link `href="#"` tại Cột 3 bằng các liên kết chuẩn của 3 hình thức học liên thông và danh bạ ngành/trường. Xóa thuật ngữ `"VB2"` và `"Cao đẳng online"`.

### Giai đoạn 2: Bổ sung & Chuẩn hóa Templates
1. **File `single-program.php`**:
   - **Thêm Tab & Section Bằng cấp (`#van-bang-tot-nghiep`)**: Bố trí thẻ UI card đồng bộ phong cách Tailwind hiện tại, giải thích giá trị văn bằng theo Thông tư 27/2019/TT-BGDĐT.
   - **Tách bạch Đối tượng tuyển sinh**: Làm rõ đối tượng có bằng Trung cấp/Cao đẳng/ĐH ngay trong phần tổng quan hoặc điều kiện.
   - **Sửa câu cảnh báo tạm ngưng (dòng 195)**: Bỏ chữ "hệ Chính quy" bị hardcode, thay bằng tên hình thức học thực tế của chương trình (`$learning_details['mode']`).
   - **Hiển thị thông tin Ngành cha**: Thêm link rõ ràng về CPT `major` tương ứng.
2. **File `taxonomy-training_type.php`**:
   - Chuẩn hóa H1 (dòng 171): Hiển thị `"Liên thông từ xa"`, `"Liên thông chính quy"`, `"Liên thông vừa học vừa làm"` khi chọn term; hiển thị `"Các chương trình tuyển sinh Liên thông Đại học"` khi ở base archive.
   - Sửa nhãn filter pills: `"Hệ đào tạo:"` -> `"Hình thức học:"`.
   - Loại trừ term `lien-thong` và `van-bang-2` nếu xuất hiện trong vòng lặp pills.
3. **File `single-school.php` & `single-major.php`**:
   - Sửa nhãn `"Lớp tuyển sinh"` -> `"Tuyển sinh liên thông"`.
   - Sửa nhãn `"Hệ đào tạo:"` -> `"Hình thức học:"`.

### Giai đoạn 3: Tối ưu Bộ Lọc & Rewrite Routing
1. **File `inc/core/class-rewrite-rules.php`**:
   - Tại dòng 247-254: Sửa redirect `/chuong-trinh/` trỏ về `/he-dao-tao/` (tổng thể mọi hình thức) thay vì ép sang `/he-dao-tao/tu-xa/`.
2. **File `front-page.php`**:
   - Sửa H1 ẩn (dòng 31): Đổi thành `"Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học Toàn Quốc"`.
   - Sửa form search hero (dòng 126): Sửa action trỏ về `/he-dao-tao/`; sửa option `-- Chọn hệ học --` thành `-- Chọn hình thức học --`.
3. **File `inc/core/class-helpers.php`**:
   - Sửa `$mode_map` tại dòng 736: Loại bỏ `'van-bang-2'`, thêm tường minh `'chinh-quy' => 'Học chính quy tập trung'`.
   - Sửa `ltdh_breadcrumb()`: Cập nhật nhãn ngữ nghĩa cho breadcrumbs.

### Giai đoạn 4: SEO On-page & Schema
1. **File `inc/seo/class-rankmath-integration.php`**:
   - Bổ sung filter title cho taxonomy `training_type`: `Liên thông [Tên hình thức] | Tuyển sinh [Năm]`.
   - Bổ sung filter title cho `school`: `Tuyển sinh Liên thông [Tên trường] | [Năm]`.
   - Bổ sung filter title cho `major`: `Liên thông ngành [Tên ngành] | Tuyển sinh [Năm]`.
   - Bổ sung filter description cho từng trang archive tương ứng.
   - Bổ sung thuộc tính `educationalCredentialAwarded` trong schema JSON-LD.

---

## 6. VERIFICATION METHOD (Phương pháp Kiểm chứng Độc lập)

Để kiểm chứng tính chính xác của các phát hiện và phương án can thiệp:

1. **Kiểm tra trực tiếp các dòng mã nguồn**:
   - Kiểm tra `class-defaults.php:33-49, 64-68` để xác nhận menu mặc định và subtext có chứa `"VB2"`.
   - Kiểm tra `footer.php:75-102` để xác nhận 4 link `href="#"` và nhãn `"Cao đẳng online / VB2"`.
   - Kiểm tra `single-program.php` để xác nhận không có bất kỳ dòng nào về "bằng cấp" và dòng 195 bị hardcode chữ "hệ Chính quy".
   - Kiểm tra `class-rewrite-rules.php:247-254` để xác nhận redirect 301 cứng từ `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`.
   - Kiểm tra `front-page.php:31` để xác nhận H1 chứa `"Văn Bằng 2 & Đại Học Từ Xa"`.

2. **Kiểm tra cú pháp PHP (Syntax Check)**:
   Sau bất kỳ chỉnh sửa nào trong Phase 2, thực thi lệnh lint trên toàn bộ các file liên quan:
   ```bash
   php -l inc/config/class-defaults.php
   php -l inc/core/class-menus.php
   php -l inc/core/class-helpers.php
   php -l inc/core/class-rewrite-rules.php
   php -l inc/seo/class-rankmath-integration.php
   php -l single-program.php
   php -l single-school.php
   php -l single-major.php
   php -l archive-school.php
   php -l archive-major.php
   php -l taxonomy-training_type.php
   php -l footer.php
   php -l front-page.php
   ```

3. **Điều kiện vô hiệu hóa (Invalidation Conditions)**:
   - Nếu phát hiện bất kỳ URL công khai nào bị đổi dẫn tới lỗi 404 hoặc mất index trên Google Search Console mà không có 301 redirect tương ứng.
   - Nếu giao diện responsive hoặc layout grid/card bị vỡ cấu trúc CSS do thay đổi class Tailwind.
   - Nếu bất kỳ bộ lọc AJAX nào trong `ltdh_ajax_filter_programs` bị mất tham số truy vấn.
