# Báo Cáo Khảo Sát & Đề Xuất Kiến Trúc Thông Tin (IA) — Chuyên Biệt "Liên Thông Đại Học"

**Agent**: `explorer_survey_ia_3` (Frontend & Template Explorer)  
**Parent Agent ID**: `f7ebf938-afab-4e8b-b557-505007c00d2d`  
**Ngày thực hiện**: 2026-10-01  
**Mục tiêu**: Khảo sát hiện trạng toàn bộ Navigation (Header/Footer), Trang chủ (`front-page.php`), Bộ lọc tìm kiếm (`Search & Filter`), Thẻ chương trình (`Program Card`), Chi tiết chương trình (`single-program.php`), và Lưu trữ hình thức học (`taxonomy-training_type.php` / `archive-program.php`). Đảm bảo 100% hướng đến mô hình tuyển sinh **Liên thông đại học** với 2 hình thức học: **Từ xa** và **Vừa học vừa làm**.

---

## 1. Observation (Hiện trạng quan sát trực tiếp)

### 1.1. Hệ thống Menu và Điều hướng (Navigation Header & Footer)

#### 1.1.1. Dữ liệu Menu trong Database (WP-CLI: `wp menu item list`)
- **Primary Menu (Menu ID: 3, slug: `primary-menu`, name: "Primary Menu")**:
  - Item 68: `Trang chủ` — Link: `/` (Type: Custom)
  - Item 69: `Trường đối tác` — Link: `/truong-doi-tac` (Type: Post Type Archive `school`)
  - Item 70: `Chuyên ngành` — Link: `/nganh-hoc/` (Type: Post Type Archive `major`)
  - Item 71: `Hệ đào tạo` — Link: `/he-dao-tao` (Type: Taxonomy `training_type`)
  - Item 72: `Tin tức` — Link: `/tin-tuc` (Type: Post Type Archive `post`)
  - Item 73: `Kiểm tra điều kiện` — Link: `/kiem-tra-dieu-kien/` (Type: Page)
- **Footer Menu (Menu ID: 4, slug: `footer-menu`, name: "Footer Menu")**:
  - Item 74: `Giới thiệu` — Link: `/gioi-thieu/`
  - Item 75: `Câu hỏi thường gặp` — Link: `/faq/`
  - Item 76: `Chính sách bảo mật` — Link: `/chinh-sach-bao-mat/`
  - Item 77: `Liên hệ` — Link: `/lien-he/`

#### 1.1.2. Mã nguồn Header (`header.php` & `inc/core/class-menus.php`)
- **Header Template (`header.php`)**:
  - Dòng 35–48: Gọi `wp_nav_menu` với theme_location `primary`. Nếu chưa gán, fallback vào `ltdh_default_primary_menu()`.
  - Dòng 52: Hardcode nút CTA máy tính để bàn: `<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" ...>TƯ VẤN NGAY</a>`.
  - Dòng 99–128: Mobile Drawer Menu có fallback tĩnh gọi:
    - Trang chủ (`/`)
    - Trường ĐH (`/truong-doi-tac/`)
    - Chuyên ngành (`/nganh-hoc/`)
    - Chương trình (`/he-dao-tao/tu-xa/`) — *Ghi chú: Đang trỏ thẳng vào `/he-dao-tao/tu-xa/` thay vì landing tổng quan!*
    - Tin tức (`/tin-tuc/`)
    - Kiểm tra điều kiện (`/kiem-tra-dieu-kien/`)
- **Bộ tiêm Submenu tự động (`inc/core/class-menus.php`)**:
  - Dòng 104–157: Bắt tiêu đề menu item chứa chuỗi `'hệ đào tạo'` để tiêm danh sách terms của taxonomy `training_type` vào cấp submenu dropdown.
  - Dòng 159–200: Bắt tiêu đề menu item chứa chuỗi `'chuyên ngành'` để tiêm các chuyên ngành nổi bật (`is_hot`).
  - Dòng 19–27: Fallback `ltdh_default_primary_menu()` chứa: `Trang chủ`, `Trường đào tạo`, `Ngành học`, `Hệ đào tạo`, `Tin tuyển sinh`, `Kiểm tra điều kiện`.

#### 1.1.3. Mã nguồn Footer (`footer.php`)
- `footer.php` hoàn toàn **không** render `wp_nav_menu( ['theme_location' => 'footer'] )` cho các cột chuyên môn!
- Cột 3 (Dòng 49–75) hardcode toàn bộ các hệ đào tạo và các liên kết chết/ngoài phạm vi:
  ```php
  // footer.php:49-75
  <h4 class="font-bold text-slate-900 text-sm tracking-wide uppercase mb-4">Hệ đào tạo</h4>
  <ul class="space-y-2.5 text-sm text-slate-600">
      <li><a href="<?php echo esc_url( home_url( '/he-dao-tao/tu-xa/' ) ); ?>" class="hover:text-brand-primary">Học đại học từ xa</a></li>
      <li><a href="#" class="hover:text-brand-primary">Cao đẳng online / VB2</a></li>
      <li><a href="#" class="hover:text-brand-primary">Liên thông Đại Học chính quy</a></li>
      <li><a href="#" class="hover:text-brand-primary">Trung Cấp lên Đại học</a></li>
      <li><a href="#" class="hover:text-brand-primary">Đại học tại chức / VLVH</a></li>
  </ul>
  ```
  *Phát hiện*: Chứa anchor chết `#`, quảng bá hệ ngoài phạm vi ("Cao đẳng online / VB2", "Trung Cấp lên Đại học", "Đại học tại chức").

---

### 1.2. Trang Chủ (`front-page.php` & `class-defaults.php`)

- **Thẻ H1 ngữ nghĩa (Semantic SEO) tại `front-page.php:31`**:
  ```html
  <h1 class="sr-only">Cổng Thông Tin Tuyển Sinh &amp; Đào Tạo Đại Học Trực Tuyến - Đại Học Từ Xa &amp; Văn Bằng 2</h1>
  ```
  *Phát hiện*: Vẫn định vị thương hiệu là "Đại Học Từ Xa & Văn Bằng 2" thay vì cổng chuyên biệt "Liên thông đại học".
- **Hộp tìm kiếm trang chủ tại `front-page.php:124`**:
  ```php
  <form action="<?php echo esc_url( home_url( '/he-dao-tao/tu-xa/' ) ); ?>" method="get" class="w-full">
  ```
  Form tìm kiếm trang chủ đang bị ẩn (`class="hidden sm:block"`) và action bị hardcode trỏ thẳng sang `/he-dao-tao/tu-xa/`.
- **Badges mặc định trên Hero (`inc/core/class-defaults.php:65`)**:
  ```php
  'hero_badge_2' => '50+ chương trình - Liên thông, VB2, Từ xa',
  ```
  Vẫn chứa từ khóa "VB2".
- **Điều kiện xét tuyển (Eligibility Checker Steps) tại `front-page.php:336–350`**:
  ```php
  <option value="thpt">Học sinh tốt nghiệp THPT - Xét học bạ tuyển thẳng</option>
  ```
  *Phát hiện*: Tốt nghiệp THPT không thuộc đối tượng "Liên thông đại học" (vốn yêu cầu đầu vào tối thiểu là Trung cấp hoặc Cao đẳng). Đưa THPT vào làm lệch phân khúc khách hàng.
- **Section Cảm nhận học viên (Testimonials Fallback) tại `front-page.php:851`**:
  ```php
  'program' => 'VB2 Công nghệ thông tin',
  ```
  Nhắc đến văn bằng 2.
- **Section Tin tức tuyển sinh (News Section) tại `front-page.php:945`**:
  Bài viết fallback mẫu: `"Điều kiện học Văn bằng 2 đại học năm 2026"`.

---

### 1.3. Bộ Lọc Tìm Kiếm & Rewrite Rules (Search & Filter)

#### 1.3.1. Xung đột định tuyến giữa `/chuong-trinh/` và `/he-dao-tao/`
- Trong WordPress CPT setup (`inc/core/class-cpt.php:89`), post type `program` đăng ký rewrite `has_archive => 'chuong-trinh'`.
- Trong `inc/core/class-rewrite-rules.php:247–251`:
  ```php
  if ( untrailingslashit( $request_uri ) === untrailingslashit( $program_archive_uri ) ) {
      wp_redirect( home_url( '/he-dao-tao/tu-xa/' ), 301 );
      exit;
  }
  ```
  Mã nguồn tự động chuyển hướng 301 toàn bộ truy cập `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`.
- Tuy nhiên:
  - `archive-program.php:188` lại đặt: `<form id="catalog-filter-form" action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET">`.
  - `archive-program.php:180` nút xóa bộ lọc: `<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" ...>Xóa tất cả bộ lọc</a>`.
  *Hệ quả*: Khi người dùng xóa bộ lọc hoặc submit GET form trên `archive-program.php`, hệ thống bị redirect 301 nhảy về `/he-dao-tao/tu-xa/`, làm mất trạng thái lọc toàn bộ chương trình hoặc gây xung đột query.

#### 1.3.2. Cấu trúc bộ lọc hiện tại
- Bố cục Form (trong `taxonomy-training_type.php` và `archive-program.php`):
  1. Input `s`: Từ khóa tìm kiếm chương trình/trường.
  2. Select `truong`: Chọn `school` slug.
  3. Select `nganh`: Chọn `major` slug hoặc `major_cat`.
  4. Select `sort`: Sắp xếp (`title_asc`, `title_desc`, `date_desc`).
  5. Pill Tabs (Row 2): Hiển thị tất cả taxonomy terms của `training_type`.

---

### 1.4. Trình Bày Thẻ Chương Trình (Program Card) & `single-program.php`

#### 1.4.1. Sự không nhất quán giữa Thẻ SSR và Thẻ AJAX
| Yếu tố | Thẻ SSR (`taxonomy-training_type.php:384-406`) | Thẻ AJAX (`inc/core/class-query-filters.php:254-266`) |
| :--- | :--- | :--- |
| **Tiêu đề chính (Headline)** | `<h2 ...><a href="..."><?php the_title(); ?></a></h2>` *(Tên chương trình là tiêu đề lớn)* | `<h3 ...><a href="..."><?php echo esc_html( $school_name ); ?></a></h3>` *(Tên TRƯỜNG là tiêu đề lớn)* |
| **Tên trường** | Text nhỏ phía trên logo: `<?php echo esc_html( $school_name ); ?>` | Biến thành thẻ Link tiêu đề chính |
| **Tên chương trình** | Headline chính của card | Biến thành badge nhỏ màu xám: `<span class="text-xs..."><?php the_title(); ?></span>` |
| **Badge hệ đào tạo** | `<span ...>Hệ <?php echo esc_html( $type_name ); ?></span>` (Dòng 379) | `<span ...>Hệ <?php echo esc_html( $t_name ); ?></span>` (Dòng 235) |

#### 1.4.2. Thiếu công thức định vị cơ hội tuyển sinh
- Cả 2 giao diện card đều chỉ in `the_title()` mộc từ database (ví dụ: *"Cử nhân Ngôn ngữ Anh"*, *"Cử nhân Công nghệ thông tin"*).
- Không có nơi nào thể hiện công thức cơ hội tuyển sinh chuẩn:
  **`"Liên thông ngành [Tên ngành] - [Hình thức học] tại [Trường]"`**.

#### 1.4.3. Trang chi tiết chương trình (`single-program.php`)
- **Dòng 195**: Thông báo tạm dừng tuyển sinh hardcode văn bản hệ chính quy:
  `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."`
- **Banner (`template-parts/banner.php:47-59`)**: Chỉ in `get_the_title()` và phụ đề `$school_id ? get_the_title( $school_id ) : ...`.
- **Dòng 270**: Khối "Hình thức học" gọi helper `ltdh_get_program_learning_details($program_id)`. Helper này trong `inc/core/class-helpers.php:736` vẫn map `van-bang-2 => 'Học tập trung / Online linh hoạt'` và fallback `'-- Chọn ngành hoặc hệ học --'` (dòng 763).

---

### 1.5. Trang Lưu Trữ Hình Thức Học (`taxonomy-training_type.php` / `archive-program.php`)

#### 1.5.1. Dữ liệu thực tế Taxonomy `training_type` và CPT `program` trong Database
Truy vấn WP-CLI kiểm tra phân bổ dữ liệu:
```bash
wp term list training_type --fields=term_id,name,slug,count
```
- ID 2: `Từ xa` (`tu-xa`) — **98 programs**
- ID 5: `Vừa học vừa làm` (`vua-hoc-vua-lam`) — **1 program** (Post ID 785: "Cử nhân Công nghệ thông tin (Vừa học vừa làm)")
- ID 7: `Chính quy` (`chinh-quy`) — **1 program** (Post ID 786: "Cử nhân Kỹ thuật Cơ điện tử")
- ID 6: `Văn bằng 2` (`van-bang-2`) — **0 programs**

Tổng số chương trình đang publish: **100 programs**. Trong đó:
- 99 programs thuộc 2 hình thức hợp lệ (`tu-xa`: 98, `vua-hoc-vua-lam`: 1).
- 1 program thuộc `chinh-quy` (ngoài phạm vi liên thông, cần chuyển draft hoặc gán lại hình thức phù hợp).

#### 1.5.2. Tiêu đề và Banner tại `template-parts/banner.php`
- Dòng 26–27 & Dòng 105–106:
  ```php
  $banner_title    = 'Hệ Đào Tạo';
  $banner_subtitle = 'Tổng hợp các chương trình đào tạo từ xa, liên thông, văn bằng 2';
  ```
- Dòng 78–79 & Dòng 90–91:
  ```php
  $banner_title    = 'Hệ đào tạo: ' . $term->name;
  $banner_subtitle = $term->description ?: 'Danh sách chương trình thuộc hệ đào tạo ' . $term->name;
  ```
  Nhãn hiển thị đang dùng từ "Hệ đào tạo" thay vì "Hình thức học".

---

## 2. Logic Chain (Chuỗi lập luận từ hiện trạng đến giải pháp)

```
[Hiện trạng quan sát]
1. Domain: lienthongdaihoc.edu.vn (hoặc theme lienthongdaihoc).
2. Định vị doanh nghiệp: 100% chuyên sâu "Liên thông đại học".
3. Hai hình thức đào tạo cốt lõi: "Từ xa" (online) và "Vừa học vừa làm" (cuối tuần).
     │
     ▼
[Phát hiện xung đột & bất cập]
1. Thuật ngữ đang bị phân mảnh:
   - Toàn theme dùng "Hệ đào tạo" để gọi chung Từ xa, Vừa học vừa làm, Văn bằng 2, Chính quy.
   - Theo Luật Giáo dục đại học & quy chuẩn học tập: "Liên thông đại học" là BẬC/PHƯƠNG THỨC ĐÀO TẠO.
   - "Từ xa" và "Vừa học vừa làm" là HÌNH THỨC HỌC (Learning Mode / Study Mode), không phải là các hệ đối trọng tách rời.
2. Dữ liệu ngoài phạm vi:
   - Footer hardcode "Cao đẳng online / VB2", "Đại Học chính quy".
   - Homepage H1 & tin tức chứa "Văn bằng 2", bài tập THPT.
   - Database có 1 post gắn `chinh-quy`.
3. Xung đột Navigation & Route:
   - Primary Menu trỏ `/he-dao-tao` (archive của training_type).
   - `class-rewrite-rules.php` redirect 301 cứng `/chuong-trinh/` -> `/he-dao-tao/tu-xa/`.
   - `archive-program.php` submit form về `/chuong-trinh/`, gây loop redirect hoặc nhảy mất bộ lọc.
4. Trải nghiệm người dùng (UX) & Định vị giá trị (Value Proposition):
   - Thẻ bài (Cards) ở SSR và AJAX không đồng bộ cấu trúc headline.
   - Thẻ bài chỉ hiện "Cử nhân Ngôn ngữ Anh" mà không nói rõ đây là "Liên thông ngành Ngôn ngữ Anh - Từ xa tại ĐH Mở Hà Nội". Người học không biết chương trình này dành cho ai và học theo hình thức nào.
     │
     ▼
[Đề xuất chuyển đổi kiến trúc IA & Giao diện]
1. Chuẩn hóa thuật ngữ: Đổi toàn bộ nhãn hiển thị "Hệ đào tạo" -> "Hình thức học".
2. Tái cấu trúc Menu & Điều hướng:
   - Menu chính: Trang chủ (/) -> Liên thông (/he-dao-tao/) -> Chuyên ngành (/nganh-hoc/) -> Trường ĐH (/truong-doi-tac/) -> Tin tức (/tin-tuc/) -> Kiểm tra điều kiện (/kiem-tra-dieu-kien/) -> CTA Tư vấn.
   - Submenu của "Liên thông": Liên thông Từ xa (/he-dao-tao/tu-xa/) | Liên thông Vừa học vừa làm (/he-dao-tao/vua-hoc-vua-lam/).
   - Footer: Loại bỏ hoàn toàn các liên kết out-of-scope (VB2, Cao đẳng, Chính quy), đồng bộ menu chính sách và 2 hình thức học.
3. Đồng bộ Thẻ Chương trình:
   - Thống nhất cấu trúc thẻ SSR và AJAX: Headline chính là Công thức tuyển sinh `"Liên thông ngành [Major] - [Hình thức học]"`. Phụ đề trên logo là `[Tên Trường]`.
4. Làm sạch Route & Redirect:
   - Cho phép `/he-dao-tao/` là Landing Catalog tổng hợp tất cả chương trình liên thông.
   - Bộ lọc chỉ hiển thị 2 pills: "Từ xa" và "Vừa học vừa làm".
   - Đảm bảo form action trên archive trỏ đúng canonical URL không bị 301.
```

---

## 3. Caveats (Ràng buộc, Phạm vi & Giả định)

1. **Ràng buộc Read-Only**: Đây là báo cáo khảo sát và kiến trúc độc lập. Không can thiệp sửa đổi trực tiếp vào code `.php` hoặc cập nhật database trong lượt này. Mọi đề xuất đã kèm code snippet trước/sau cụ thể để Implementer thực thi chính xác.
2. **Bảo toàn dữ liệu (Non-destructive)**:
   - Giữ nguyên cấu trúc 3 CPT cốt lõi: `school`, `major`, `program` và taxonomy `training_type` (về mặt kỹ thuật database không đổi tên taxonomy slug để tránh gãy DB schema, chỉ đổi nhãn hiển thị UI thành "Hình thức học").
   - Đối với 1 bài viết mang term `chinh-quy` (Post ID 786): Khuyến nghị chuyển sang trạng thái `draft` hoặc phân loại lại thành `vua-hoc-vua-lam` thay vì xóa vĩnh viễn (hard delete).
3. **Bảo toàn SEO URLs**:
   - Giữ nguyên cấu trúc đường dẫn canonical đã index:
     - `/he-dao-tao/` (Trang tổng hợp chương trình)
     - `/he-dao-tao/tu-xa/` (Hình thức học Từ xa)
     - `/he-dao-tao/vua-hoc-vua-lam/` (Hình thức học Vừa học vừa làm)
     - `/truong-doi-tac/` & `/truong-doi-tac/[slug]/`
     - `/nganh-hoc/` & `/nganh-hoc/[slug]/`
     - `/[slug]/` (Chi tiết chương trình `program`)

---

## 4. Conclusion & Actionable Recommendations (Kết luận & Đề xuất hành động chi tiết)

### 4.1. Đề xuất Cây Điều Hướng (Navigation Tree Proposal)

#### Cây Menu Chính (Header Primary Menu):
```
1. Trang chủ (/)
2. Chương trình liên thông (/he-dao-tao/) [Nhãn hiển thị có thể là "Chương trình Liên thông" hoặc "Hình thức học"]
   ├── Liên thông Đại học Từ xa (/he-dao-tao/tu-xa/)
   └── Liên thông Vừa học vừa làm (/he-dao-tao/vua-hoc-vua-lam/)
3. Ngành học liên thông (/nganh-hoc/)
   └── [Tự động tiêm các chuyên ngành tuyển sinh hot]
4. Trường đại học đối tác (/truong-doi-tac/)
5. Tin tuyển sinh (/tin-tuc/)
6. Kiểm tra điều kiện (/kiem-tra-dieu-kien/)
[Nút CTA Cố Định]: ĐĂNG KÝ TƯ VẤN (/lien-he/)
```

#### Bố cục Chân Trang (Footer Architecture):
- **Cột 1: Nhận diện thương hiệu & Pháp lý**: Logo Cổng Thông Tin Liên Thông Đại Học, mô tả sứ mệnh hỗ trợ học viên tốt nghiệp trung cấp, cao đẳng nâng chuẩn bằng cấp đại học uy tín.
- **Cột 2: Hình thức đào tạo**:
  - `Liên thông Đại học Từ xa (Online 100%)` -> `/he-dao-tao/tu-xa/`
  - `Liên thông Vừa học vừa làm (Tập trung / Cuối tuần)` -> `/he-dao-tao/vua-hoc-vua-lam/`
  - `Danh sách Trường đại học tuyển sinh` -> `/truong-doi-tac/`
  - `Tất cả chuyên ngành liên thông` -> `/nganh-hoc/`
- **Cột 3: Hướng dẫn & Tra cứu**:
  - `Kiểm tra điều kiện liên thông` -> `/kiem-tra-dieu-kien/`
  - `So sánh chương trình đào tạo` -> `/so-sanh/`
  - `Lịch tuyển sinh các đợt` -> `/tin-tuc/`
  - `Câu hỏi thường gặp (FAQ)` -> `/faq/`
- **Cột 4: Liên hệ & Tư vấn tuyển sinh**: Hotline, Zalo OA, Form tư vấn 24/7.

---

### 4.2. Khắc phục & Điều chỉnh Trang Chủ (`front-page.php`)

| Vị trí | Hiện trạng | Đề xuất thay thế |
| :--- | :--- | :--- |
| `front-page.php:31` (H1 ẩn) | `...Đại Học Từ Xa & Văn Bằng 2` | `Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm` |
| `front-page.php:124` (Search form) | `action="/he-dao-tao/tu-xa/"` | `action="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>"` |
| `front-page.php:336` (Điều kiện xét tuyển) | `<option value="thpt">Học sinh tốt nghiệp THPT...</option>` | Thay bằng: `<option value="trung-cap">Người tốt nghiệp Trung cấp (khác ngành / đúng ngành)</option>` |
| `front-page.php:851` (Cảm nhận học viên) | `'VB2 Công nghệ thông tin'` | `'Liên thông ngành Công nghệ thông tin'` |
| `front-page.php:945` (Tin tức mẫu) | `Điều kiện học Văn bằng 2 đại học năm 2026` | `Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026` |
| `inc/core/class-defaults.php:65` | `'50+ chương trình - Liên thông, VB2, Từ xa'` | `'50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm'` |

---

### 4.3. Khắc phục Bộ Lọc Tìm Kiếm & Rewrite Rules

1. **Sửa lỗi Redirect 301 tại `inc/core/class-rewrite-rules.php:247`**:
   - Hiện tại: Redirect toàn bộ `/chuong-trinh/` sang `/he-dao-tao/tu-xa/`.
   - Đề xuất: Chuyển hướng `/chuong-trinh/` sang `/he-dao-tao/` (Archive tổng), giữ nguyên query string (`$_SERVER['QUERY_STRING']`) để tránh làm mất tham số filter khi người dùng truy cập route cũ.
2. **Đồng bộ Form Action trong `archive-program.php` & `taxonomy-training_type.php`**:
   - Đổi form action trong `archive-program.php:188` từ `/chuong-trinh/` thành `/he-dao-tao/`.
   - Đổi link nút xóa bộ lọc (`archive-program.php:180`) từ `/chuong-trinh/` thành `/he-dao-tao/`.
3. **Pill Tabs Hình thức học**:
   - Trong `taxonomy-training_type.php:273–298`:
     Chỉ hiển thị 2 tabs cố định:
     - `Tất cả` (`/he-dao-tao/`)
     - `Từ xa` (`/he-dao-tao/tu-xa/`)
     - `Vừa học vừa làm` (`/he-dao-tao/vua-hoc-vua-lam/`)
     Ẩn hoàn toàn các term khác (`chinh-quy`, `van-bang-2`).
   - Đổi nhãn `taxonomy-training_type.php:250`:
     `<span class="...">Hệ đào tạo:</span>` -> `<span class="...">Hình thức học:</span>`.

---

### 4.4. Chuẩn Hóa Thẻ Chương Trình (Program Card Formula)

#### Công thức tiêu chuẩn:
`"Liên thông ngành [Tên ngành] - [Hình thức học] tại [Tên trường]"`

#### Cấu trúc HTML & Layout khuyến nghị (Đồng bộ SSR & AJAX):
```html
<div class="program-card bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all ...">
    <!-- Cover & Badges -->
    <div class="h-28 sm:h-32 w-full bg-cover relative" style="background-image: url('...');">
        <!-- Badge Tuyển sinh bên trái -->
        <span class="admission-status-badge ...">Đang tuyển sinh</span>
        <!-- Badge Hình thức học bên phải (KHÔNG in "Hệ Từ xa" mà in rõ "Từ xa" hoặc "Học online 100%") -->
        <span class="study-mode-badge ..."><?php echo esc_html( $learning_mode_label ); ?></span>
    </div>

    <!-- Nội dung thẻ -->
    <div class="p-5 pb-0">
        <!-- Logo & Tên Trường -->
        <div class="flex items-center gap-2.5 mb-2 -mt-7 relative z-10">
            <div class="w-10 h-10 bg-white border rounded-lg p-1 shrink-0 shadow-xs">
                <img src="<?php echo esc_url($school_logo); ?>" alt="<?php echo esc_attr($school_name); ?>" class="w-full h-full object-contain">
            </div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider truncate"><?php echo esc_html( $school_name ); ?></span>
        </div>

        <!-- Headline: Cơ hội tuyển sinh chuẩn -->
        <h2 class="font-extrabold text-slate-900 text-base hover:text-brand-primary leading-snug line-clamp-2 min-h-[48px]">
            <a href="<?php the_permalink(); ?>">
                Liên thông ngành <?php echo esc_html( $major_name ); ?> - <?php echo esc_html( $type_name ); ?>
            </a>
        </h2>

        <!-- Thông tin tóm tắt -->
        <div class="space-y-1.5 text-xs text-slate-600 py-3 border-t border-slate-100">
            <div class="flex justify-between"><span>Học phí:</span><strong class="text-brand-primary"><?php echo esc_html( $tuition ); ?></strong></div>
            <div class="flex justify-between"><span>Thời gian đào tạo:</span><strong class="text-slate-800"><?php echo esc_html( $duration ); ?></strong></div>
            <div class="flex justify-between"><span>Hình thức học:</span><span class="bg-slate-100 px-2 py-0.5 rounded font-medium"><?php echo esc_html( $learning_mode_detail ); ?></span></div>
        </div>
    </div>
    
    <!-- CTA Button -->
    <div class="p-5 pt-3 border-t border-slate-100">
        <a href="<?php the_permalink(); ?>" class="ltdh-btn-details ...">Xem chi tiết tuyển sinh</a>
    </div>
</div>
```

---

### 4.5. Khắc phục `single-program.php` & Banner chung

1. **`single-program.php:195`**:
   - Thay: `"Chương trình tuyển sinh hệ Chính quy của trường năm nay hiện đã nhận đủ chỉ tiêu."`
   - Bằng: `"Chương trình liên thông đại học ngành này của trường hiện đã nhận đủ chỉ tiêu đợt này. Quý học viên vui lòng tham khảo các đợt kế tiếp hoặc đăng ký để nhận tư vấn lộ trình phù hợp."`
2. **`template-parts/banner.php`**:
   - Thay tiêu đề mặc định khi xem `/he-dao-tao/`:
     - Tiêu đề: `"Chương Trình Tuyển Sinh Liên Thông Đại Học"`
     - Phụ đề: `"Tổng hợp các chương trình đào tạo liên thông theo hình thức Từ xa & Vừa học vừa làm từ các trường đại học uy tín"`
   - Thay tiêu đề taxonomy `training_type`:
     - Thay `"Hệ đào tạo: " . $term->name` thành `"Liên thông đại học - Hình thức " . $term->name`
3. **`inc/core/class-helpers.php:763`**:
   - Thay `-- Chọn ngành hoặc hệ học --` bằng `-- Chọn ngành học liên thông --`.

---

## 5. Verification Method (Phương pháp kiểm chứng độc lập)

Để kiểm chứng tính chính xác của các nhận định và kiểm tra sau khi Implementer thực thi:

1. **Kiểm tra WP Menu & Tuyến đường dẫn (WP-CLI)**:
   ```bash
   /Users/ken/bin/wp menu item list 3 --fields=db_id,title,url
   /Users/ken/bin/wp menu item list 4 --fields=db_id,title,url
   ```
   *Điều kiện hợp lệ*: Không còn item nào chứa nhãn "VB2" hoặc liên kết chết `#`.
2. **Kiểm tra trạng thái Redirect HTTP (Curl / Header check)**:
   ```bash
   curl -I "http://lienthongdaihoc.local/chuong-trinh/"
   curl -I "http://lienthongdaihoc.local/he-dao-tao/"
   curl -I "http://lienthongdaihoc.local/he-dao-tao/tu-xa/"
   curl -I "http://lienthongdaihoc.local/he-dao-tao/vua-hoc-vua-lam/"
   ```
   *Điều kiện hợp lệ*:
   - `/he-dao-tao/` trả về HTTP 200 (không bị 301 loop).
   - `/he-dao-tao/tu-xa/` và `/he-dao-tao/vua-hoc-vua-lam/` trả về HTTP 200.
3. **Kiểm tra bài viết không thuộc phạm vi tuyển sinh liên thông**:
   ```bash
   /Users/ken/bin/wp post list --post_type=program --tax_query='[{"taxonomy":"training_type","field":"slug","terms":["chinh-quy","van-bang-2"]}]' --fields=ID,post_title,post_status
   ```
   *Hành động*: Kiểm tra xem Post 786 ("Cử nhân Kỹ thuật Cơ điện tử") đã được chuyển sang `draft` hoặc phân loại lại hay chưa.
4. **Kiểm tra giao diện Template (Grep Search)**:
   ```bash
   grep -rn "Hệ đào tạo" /Users/ken/Local\ Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/template-parts/
   grep -rn "hệ Chính quy" /Users/ken/Local\ Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/single-program.php
   ```
   *Điều kiện hợp lệ*: Không còn văn bản nhầm lẫn giữa "Hệ đào tạo" và "Hình thức học", không còn chuỗi "hệ Chính quy" trong thông báo tuyển sinh liên thông.
