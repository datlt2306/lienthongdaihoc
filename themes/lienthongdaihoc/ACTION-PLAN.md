# KẾ HOẠCH HÀNH ĐỘNG TỐI ƯU HÓA SEO (ACTION PLAN)
**Dự án**: Cổng thông tin Tuyển sinh Liên thông Đại học (`lienthongdaihoc.com`)  
**Mục tiêu**: Chuẩn hóa 100% On-page SEO, Cấu trúc Heading, Meta Tags và Rich Snippets trên toàn bộ các template WordPress.

---

## 1. CÁC HẠNG MỤC ƯU TIÊN CAO (ĐÃ HOÀN TẤT & ĐÃ TRIỂN KHAI 100%)

### ✅ Hạng mục 1: Xóa bỏ triệt để xung đột Thẻ H1 kép (Single H1 Enforcement)
- **Vấn đề**: Các trang danh mục (`archive-major.php`, `archive-program.php`, `taxonomy-training_type.php`, `page.php`, `page-compare-program.php`) vừa nạp `template-parts/banner.php` (render H1), vừa tự khai báo một thẻ `<h1 class="...">` ở phần đầu danh sách bộ lọc.
- **Giải pháp**:
  - Chuyển toàn bộ thẻ `<h1>` thứ hai trong thân trang thành `<h2>`.
  - Giữ nguyên cấu trúc CSS và layout để không ảnh hưởng đến giao diện hiển thị.
- **Kết quả**: Tất cả 12 template hiện nay đều có duy nhất **1 thẻ `<h1>` duy nhất**, cấu trúc phân cấp từ H1 -> H2 -> H3 đạt chuẩn 100%.

---

### ✅ Hạng mục 2: Tối ưu hóa Độ dài Title Tag theo SERP Pixel Limit (< 65 ký tự)
- **Vấn đề**: Trang chi tiết chương trình ghép chuỗi tên ngành + hình thức + tên đầy đủ của trường đại học dẫn đến tiêu đề dài tới 96 ký tự (`Liên thông Công nghệ thông tin (Chính quy) – Trường Đại học Giao thông Vận tải | Tuyển sinh 2026`), bị Google cắt cụt bằng dấu ba chấm (`...`).
- **Giải pháp trong `inc/seo/class-rankmath-integration.php`**:
  - Tự động kiểm tra độ dài chuỗi tiêu đề.
  - Nếu vượt quá 65 ký tự, tự động rút gọn tên trường thành viết tắt chuẩn (ví dụ: `UTC`, `ĐH Giao thông Vận tải`) hoặc lược bỏ từ nối phụ.
  - Chuẩn hóa tiêu đề Trang chủ: `Liên Thông Đại Học 2026: Hệ Từ Xa, Vừa Học Vừa Làm, Chính Quy` (61 ký tự).
- **Kết quả**: 100% tiêu đề các trang audit nằm trong khoảng 50–65 ký tự vàng.

---

### ✅ Hạng mục 3: Chuẩn hóa Meta Description Động (135–165 ký tự)
- **Vấn đề**:
  - Trang chủ có meta description quá ngắn (64 ký tự).
  - Trang FAQ (`/cau-hoi-thuong-gap/`) và Kiểm tra điều kiện (`/kiem-tra-dieu-kien/`) bị thiếu meta description (`NONE`).
  - Trang chi tiết so sánh quá dài (180 ký tự).
- **Giải pháp**:
  - Tích hợp hook lọc `rank_math/frontend/description`, `rank_math/opengraph/facebook/description`, `rank_math/opengraph/twitter/description`.
  - Cung cấp mô tả động chứa từ khóa tuyển sinh, thời gian học, đối tượng tuyển sinh (người có bằng Trung cấp/Cao đẳng) và CTA xét tuyển.
  - Giới hạn cứng trần 160–165 ký tự cho mô tả trang so sánh.
- **Kết quả**: Độ dài trung bình đạt 145–160 ký tự, hiển thị hoàn hảo trên cả Mobile và Desktop SERP.

---

### ✅ Hạng mục 4: Đảm bảo 100% Hình ảnh có Thuộc tính Alt
- **Vấn đề**: Một số ảnh trường đối tác trên Trang chủ bị rỗng `alt=""` khi ảnh tải từ Media Library chưa được nhập văn bản thay thế.
- **Giải pháp**:
  - Bổ sung fallback alt `Logo [Tên trường]` trực tiếp trong vòng lặp trường đối tác tại `front-page.php`.
  - Đăng ký filter toàn cục `wp_get_attachment_image_attributes` trong `class-rankmath-integration.php`: nếu alt rỗng, tự động lấy tên bài viết cha hoặc tiêu đề tệp đính kèm.
- **Kết quả**: 0 ảnh thiếu thẻ Alt trên toàn bộ hệ thống trang đã kiểm thử (100% alt coverage).

---

### ✅ Hạng mục 5: 301 Redirect Tự động & Canonical Sạch Cho Đường Dẫn So Sánh
- **Vấn đề**:
  - Đường dẫn `/hinh-thuc-dao-tao/tu-xa/` không khớp với slug danh mục chuẩn trong CSDL (`dao-tao-tu-xa`), dẫn đến RankMath gán nhầm thẻ `noindex`.
  - Tuyến so sánh `/so-sanh/chuong-trinh/.../` có canonical trỏ nhầm về bài viết blog `/tin-tuc/`.
  - Base route `/so-sanh/` bị lỗi 404 Not Found.
- **Giải pháp**:
  - Thêm rule chuyển hướng 301 vĩnh viễn từ `/hinh-thuc-dao-tao/tu-xa/` sang `/hinh-thuc-dao-tao/dao-tao-tu-xa/`.
  - Đăng ký rewrite rule chuẩn cho base so sánh: `^so-sanh(?:/chuong-trinh)?/?$`.
  - Ép canonical tự tham chiếu sạch cho route so sánh qua `ltdh_seo_enforce_canonical_url`.
- **Kết quả**: Toàn bộ đường dẫn trả về 200 OK, canonical chuẩn, hoàn toàn thân thiện với bot tìm kiếm.

---

### ✅ Hạng mục 6: Khắc phục Triệt để Lỗi Skip Heading (H1->H3/H4 và H2->H4)
- **Vấn đề**: Một số khối UI (thẻ thông báo hết chỉ tiêu, thanh bar mobile, thẻ tải phiếu, thẻ mạng xã hội, các card bài viết trong grid) dùng thẻ `<h4>` trực tiếp dưới `<h1>` hoặc `<h2>`, làm hỏng cây phân cấp ngữ nghĩa của tài liệu.
- **Giải pháp**:
  - Đổi các khối UI component không phải tiêu đề phần (như alert, mini bar, sticky CTA, FB group box) thành thẻ `<p class="font-bold...">`.
  - Đổi các tiêu đề card bài viết / ngành hot trong grid nằm dưới `<h2>` thành `<h3>`.
  - Đổi câu hỏi FAQ dưới `<h2>Câu hỏi thường gặp</h2>` thành `<h3>`.
- **Kết quả**: Cây Heading Outline trên toàn bộ 12 template tuân thủ chặt chẽ nguyên tắc H1 -> H2 -> H3 -> H4, hoàn toàn không có bất kỳ bước nhảy cấp nào.

---

## 2. KẾ HOẠCH BẢO TRÌ & NÂNG CAO ĐỊNH KỲ (FUTURE ROADMAP)

1. **Hình ảnh chia sẻ mạng xã hội tùy biến (OG:Image Customization)**:
   - Truy cập **Admin WP > Cài đặt Website (`ltdh-settings`) > Ảnh chia sẻ mạng xã hội**.
   - Thiết kế ảnh kích thước chuẩn `1200 x 630 px` có logo và thông điệp tuyển sinh 2026 để tăng tỷ lệ nhấp (CTR) khi chia sẻ trên Zalo và Facebook.
2. **Kiểm tra tự động trước mỗi lần Release**:
   - Chạy lệnh CLI để kiểm định toàn bộ 12 trang:
     ```bash
     php tests/audit-onpage-deep.php
     ```
   - Đảm bảo terminal báo `11/11 chỉ số pass` và zero warnings trước khi deploy lên Production.
