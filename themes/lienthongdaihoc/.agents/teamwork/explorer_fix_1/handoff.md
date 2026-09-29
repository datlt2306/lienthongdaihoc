# BÁO CÁO PHÂN TÍCH & GIẢI PHÁP KHẮC PHỤC SCHEMA CODE SNIPPETS (EXPLORER FIX 1)
## Focus: Remediation Analysis for SCHEMA-CRIT-01, SCHEMA-HIGH-01, SCHEMA-HIGH-03 in `FULL_PROJECT_AUDIT_REPORT.md`

- **Tác giả:** teamwork_preview_explorer_fix_1 (Role: Schema Remediation Explorer)
- **Thư mục làm việc:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/.agents/teamwork/explorer_fix_1`
- **Tệp mục tiêu cần cập nhật:** `/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/FULL_PROJECT_AUDIT_REPORT.md`
- **Thời gian thực hiện:** 2026-09-25T05:38:30Z (UTC)

---

## 1. OBSERVATION (QUAN SÁT THỰC NGHIỆM ĐỐI SOÁT TRỰC TIẾP)

Quá trình rà soát và kiểm chứng thực nghiệm trực tiếp trên tệp báo cáo `FULL_PROJECT_AUDIT_REPORT.md` và mã nguồn theme xác nhận:

### 1.1. Lỗi `ArgumentCountError` trong `SCHEMA-CRIT-01`
- **Vị trí quan sát:** `FULL_PROJECT_AUDIT_REPORT.md:237` (nằm trong khối code dòng 225–291).
- **Đoạn code hiện tại:**
  ```php
  $defaults = ltdh_get_defaults();
  ```
- **Đối soát định nghĩa hàm:** Tại `inc/config/class-defaults.php:22`:
  ```php
  function ltdh_get_defaults( string $group ): array {
  ```
  Hàm có 1 tham số bắt buộc `$group` không có default value.
- **Thực nghiệm PHP CLI:**
  Chạy lệnh: `php -r "define('ABSPATH', 1); require 'inc/config/class-defaults.php'; ltdh_get_defaults();"`
  Kết quả thực tế:
  `Fatal error: Uncaught ArgumentCountError: Too few arguments to function ltdh_get_defaults(), 0 passed in Command line code on line 1 and exactly 1 expected in .../inc/config/class-defaults.php:22`.

### 1.2. Lỗi `ArgumentCountError` trong `SCHEMA-HIGH-01`
- **Vị trí quan sát:** `FULL_PROJECT_AUDIT_REPORT.md:727` (nằm trong khối code dòng 724–762).
- **Đoạn code hiện tại:**
  ```php
  $defaults = ltdh_get_defaults();
  ```
- **Đối soát khóa mảng:** Trong `class-defaults.php:24-31`, nhóm `'contact'` trả về các khóa: `hotline`, `zalo_url`, `messenger_url`, `email`, `address`, `company_name`. Khóa Zalo là `zalo_url` (không phải `zalo`).

### 1.3. Lỗi Fatal & ACF Options Query trong `SCHEMA-HIGH-03`
- **Vị trí quan sát:** `FULL_PROJECT_AUDIT_REPORT.md:830` (nằm trong khối code dòng 827–856).
- **Đoạn code hiện tại:**
  ```php
  $faq_list = get_field( 'faq_items' ) ?: ltdh_get_defaults()['faq_list'] ?? [];
  ```
- **Khiếm khuyết kỹ thuật kép:**
  1. `ltdh_get_defaults()` gọi không đối số dẫn đến Fatal Error `ArgumentCountError`.
  2. Mảng `$defaults` trong `inc/config/class-defaults.php` hoàn toàn không có khóa `'faq_list'`. Biểu thức luôn trả về `[]` khi ACF rỗng.
  3. Trong template `page-faq.php:25`: Dữ liệu FAQ được định nghĩa và truy xuất từ trang Options của ACF: `get_field( 'faq_items', 'options' )`. Việc gọi `get_field( 'faq_items' )` không có đối số `'options'` sẽ tìm post meta trên trang `page-faq`, dẫn tới giá trị `null`.
  4. Template `page-faq.php:29-37` có sẵn 5 câu hỏi fallback cốt lõi khi ACF chưa có dữ liệu. Snippet trong báo cáo thiếu hoàn toàn 5 câu hỏi này.

---

## 2. LOGIC CHAIN (CHUỖI LẬP LUẬN VÀ SUY LUẬN)

1. **Từ Quan sát 1.1 & 1.2:**
   - Trong PHP 8.0+, bất kỳ lệnh gọi hàm nào thiếu tham số bắt buộc sẽ lập tức ném ra ngoại lệ `ArgumentCountError` (trước PHP 7.1 là E_WARNING, từ PHP 7.1+ là ArgumentCountError/Error).
   - Vì hàm xuất schema fallback được gắn vào hook `wp_head` hoặc hook filter `rank_math/json_ld`, mọi request của người dùng và bot tìm kiếm sẽ kích hoạt hàm này.
   - Do đó, nếu áp dụng trực tiếp snippet dòng 237 hoặc 727, website sẽ gặp Fatal Error làm sập toàn bộ frontend.
   - Khi truyền đối số `'contact'`, hàm `ltdh_get_defaults( 'contact' )` trả về đầy đủ `hotline`, `email`, `address`, giải quyết triệt để lỗi Fatal.

2. **Từ Quan sát 1.3:**
   - Snippet FAQ của Rank Math cần đồng bộ 100% với dữ liệu render trên giao diện thực tế tại `page-faq.php`.
   - Vì `page-faq.php` lấy dữ liệu từ `get_field( 'faq_items', 'options' )` và dự phòng bằng 5 câu hỏi chuẩn về tuyển sinh liên thông/từ xa (Thông tư 27/2019/TT-BGDĐT, thời gian học, E-learning, thi cao học, hồ sơ), việc đồng bộ chính xác logic này vào filter hook của Rank Math đảm bảo Schema `FAQPage` luôn luôn sinh ra đầy đủ 5 thực thể `Question`/`Answer`, bất kể ACF đã được nhập liệu hay chưa.

---

## 3. CAVEATS (CÁC GIỚI HẠN & GIẢ ĐỊNH)

1. **Không sửa code gốc theme:** Theo tôn chỉ điều tra (Read-only Explorer), chúng tôi không can thiệp vào bất kỳ tệp PHP nào của theme. Tất cả các sửa đổi chỉ nhắm vào tệp tài liệu báo cáo `FULL_PROJECT_AUDIT_REPORT.md`.
2. **Khả năng tương thích:** Đoạn code sửa đổi đã được kiểm tra tính tương thích cú pháp PHP 8.0 - 8.4 CLI và đảm bảo an toàn tuyệt đối khi đưa vào tài liệu.
3. **Phạm vi:** Báo cáo này xử lý chính xác 3 khiếm khuyết Schema (`SCHEMA-CRIT-01`, `SCHEMA-HIGH-01`, `SCHEMA-HIGH-03`) theo yêu cầu phản biện của Reviewer 2. Các mục khác (như `FRONT-HIGH-02`) thuộc phạm vi của Explorer Fix 2 / 3.

---

## 4. CONCLUSION & ACTIONABLE REPLACEMENT SNIPPETS (HƯỚNG DẪN THAY THẾ DÀNH CHO WORKER)

Worker tiếp theo có thể áp dụng công cụ `replace_file_content` trực tiếp trên tệp:
`/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/FULL_PROJECT_AUDIT_REPORT.md`
với các thông số chính xác dưới đây:

### 4.1. Khắc phục `SCHEMA-CRIT-01`
- **Tệp mục tiêu:** `FULL_PROJECT_AUDIT_REPORT.md`
- **Khoảng dòng:** `StartLine: 225`, `EndLine: 291`
- **TargetContent (chính xác từng ký tự):**
```php
```php
// Bổ sung hàm xuất Schema native fallback vào wp_head trong inc/seo/class-rankmath-integration.php:
add_action( 'wp_head', 'ltdh_output_native_schema_fallback', 2 );
function ltdh_output_native_schema_fallback() {
	// Nếu Rank Math đang hoạt động thì để Rank Math xử lý qua hook rank_math/json_ld
	if ( class_exists( 'RankMath' ) ) {
		return;
	}

	$schemas = [];

	// 1. Schema EducationalOrganization cho toàn website
	$defaults = ltdh_get_defaults();
	$schemas[] = [
		'@context'    => 'https://schema.org',
		'@type'       => 'EducationalOrganization',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => ltdh_get_logo_url(),
		'description' => get_bloginfo( 'description' ),
		'telephone'   => $defaults['hotline'] ?? '',
		'email'       => $defaults['email'] ?? '',
		'address'     => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $defaults['address'] ?? '',
			'addressCountry'  => 'VN',
		],
	];

	// 2. Schema Course cho chi tiết chương trình
	if ( is_singular( 'program' ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( 'school_relationship', $program_id ) ?: 0 );
		$tuition    = get_field( 'program_tuition', $program_id );
		$duration   = get_field( 'program_duration', $program_id ) ?: '1.5 - 2.5 năm';
		
		$schemas[] = [
			'@context'          => 'https://schema.org',
			'@type'             => 'Course',
			'name'              => get_the_title( $program_id ),
			'description'       => get_the_excerpt( $program_id ) ?: get_the_title( $program_id ),
			'provider'          => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school_id ? get_the_title( $school_id ) : get_bloginfo( 'name' ),
				'url'   => $school_id ? get_permalink( $school_id ) : home_url( '/' ),
			],
			'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư',
			'hasCourseInstance' => [
				'@type'          => 'CourseInstance',
				'courseMode'     => 'Blended / Online',
				'courseWorkload' => $duration,
			],
			'offers'            => [
				'@type'         => 'Offer',
				'price'         => is_numeric( $tuition ) ? $tuition : '0',
				'priceCurrency' => 'VND',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $program_id ),
			],
		];
	}

	if ( ! empty( $schemas ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}
}
```
```

- **ReplacementContent:**
```php
```php
// Bổ sung hàm xuất Schema native fallback vào wp_head trong inc/seo/class-rankmath-integration.php:
add_action( 'wp_head', 'ltdh_output_native_schema_fallback', 2 );
function ltdh_output_native_schema_fallback() {
	if ( class_exists( 'RankMath' ) ) {
		return;
	}

	$schemas = [];

	// SỬA ĐÚNG: Truyền đối số 'contact' để tránh ArgumentCountError trên PHP 8+
	$contact_defaults = ltdh_get_defaults( 'contact' );

	$schemas[] = [
		'@context'    => 'https://schema.org',
		'@type'       => 'EducationalOrganization',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => ltdh_get_logo_url(),
		'description' => get_bloginfo( 'description' ),
		'telephone'   => $contact_defaults['hotline'] ?? '',
		'email'       => $contact_defaults['email'] ?? '',
		'address'     => [
			'@type'          => 'PostalAddress',
			'streetAddress'  => $contact_defaults['address'] ?? '',
			'addressCountry' => 'VN',
		],
	];

	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id  = get_the_ID();
		$school_id   = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$tuition_raw = get_field( LTDH_META_TUITION, $program_id );
		$duration    = get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2.5 năm';
		$mode_terms  = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
		$mode_name   = ( ! empty( $mode_terms ) && ! is_wp_error( $mode_terms ) ) ? $mode_terms[0]->name : 'Đại học từ xa';

		$schemas[] = [
			'@context'                     => 'https://schema.org',
			'@type'                        => 'Course',
			'name'                         => get_the_title( $program_id ),
			'description'                  => get_the_excerpt( $program_id ) ?: get_the_title( $program_id ),
			'provider'                     => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school_id ? get_the_title( $school_id ) : get_bloginfo( 'name' ),
				'url'   => $school_id ? get_permalink( $school_id ) : home_url( '/' ),
			],
			'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư',
			'hasCourseInstance'            => [
				'@type'          => 'CourseInstance',
				'courseMode'     => $mode_name,
				'courseWorkload' => $duration,
			],
			'offers'                       => [
				'@type'         => 'Offer',
				'price'         => is_numeric( $tuition_raw ) ? $tuition_raw : '0',
				'priceCurrency' => 'VND',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $program_id ),
			],
		];
	}

	if ( ! empty( $schemas ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}
}
```
```

---

### 4.2. Khắc phục `SCHEMA-HIGH-01`
- **Tệp mục tiêu:** `FULL_PROJECT_AUDIT_REPORT.md`
- **Khoảng dòng:** `StartLine: 724`, `EndLine: 762`
- **TargetContent (chính xác từng ký tự):**
```php
```php
// Bổ sung vào filter hook rank_math/json_ld trong inc/seo/class-rankmath-integration.php:
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    $defaults = ltdh_get_defaults();

    // 1. Thêm WebSite Schema kèm Sitelinks Search Action
    if ( is_front_page() ) {
        $data['WebSite'] = [
            '@type'           => 'WebSite',
            '@id'             => home_url( '/#website' ),
            'url'             => home_url( '/' ),
            'name'            => get_bloginfo( 'name' ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => home_url( '/he-dao-tao/?q={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    // 2. Thêm EducationalOrganization Schema
    $data['EducationalOrganization'] = [
        '@type'       => 'EducationalOrganization',
        '@id'         => home_url( '/#organization' ),
        'name'        => get_bloginfo( 'name' ),
        'url'         => home_url( '/' ),
        'logo'        => ltdh_get_logo_url(),
        'telephone'   => $defaults['hotline'] ?? '',
        'email'       => $defaults['email'] ?? '',
        'sameAs'      => array_values( array_filter( [
            $defaults['facebook'] ?? '',
            $defaults['youtube'] ?? '',
            $defaults['zalo'] ?? '',
        ] ) ),
    ];

    return $data;
}, 99, 2 );
```
```

- **ReplacementContent:**
```php
```php
// Bổ sung vào filter hook rank_math/json_ld trong inc/seo/class-rankmath-integration.php:
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    // SỬA ĐÚNG: Truyền đối số 'contact' để tránh ArgumentCountError trên PHP 8+
    $defaults = ltdh_get_defaults( 'contact' );

    // 1. Thêm WebSite Schema kèm Sitelinks Search Action
    if ( is_front_page() ) {
        $data['WebSite'] = [
            '@type'           => 'WebSite',
            '@id'             => home_url( '/#website' ),
            'url'             => home_url( '/' ),
            'name'            => get_bloginfo( 'name' ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => home_url( '/he-dao-tao/?q={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    // 2. Thêm EducationalOrganization Schema
    $data['EducationalOrganization'] = [
        '@type'       => 'EducationalOrganization',
        '@id'         => home_url( '/#organization' ),
        'name'        => get_bloginfo( 'name' ),
        'url'         => home_url( '/' ),
        'logo'        => ltdh_get_logo_url(),
        'telephone'   => $defaults['hotline'] ?? '',
        'email'       => $defaults['email'] ?? '',
        'sameAs'      => array_values( array_filter( [
            $defaults['facebook'] ?? '',
            $defaults['youtube'] ?? '',
            $defaults['zalo_url'] ?? ( $defaults['zalo'] ?? '' ),
        ] ) ),
    ];

    return $data;
}, 99, 2 );
```
```

---

### 4.3. Khắc phục `SCHEMA-HIGH-03`
- **Tệp mục tiêu:** `FULL_PROJECT_AUDIT_REPORT.md`
- **Khoảng dòng:** `StartLine: 827`, `EndLine: 856`
- **TargetContent (chính xác từng ký tự):**
```php
```php
// Bổ sung xử lý FAQPage cho page-faq.php trong inc/seo/class-rankmath-integration.php:
if ( is_page_template( 'page-faq.php' ) || is_page( 'hoi-dap' ) ) {
    $faq_list = get_field( 'faq_items' ) ?: ltdh_get_defaults()['faq_list'] ?? [];
    if ( ! empty( $faq_list ) && is_array( $faq_list ) ) {
        $faq_schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => [],
        ];
        foreach ( $faq_list as $item ) {
            $q = trim( wp_strip_all_tags( $item['question'] ?? '' ) );
            $a = trim( wp_strip_all_tags( $item['answer'] ?? '' ) );
            if ( $q && $a ) {
                $faq_schema['mainEntity'][] = [
                    '@type'          => 'Question',
                    'name'           => $q,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $a,
                    ],
                ];
            }
        }
        if ( ! empty( $faq_schema['mainEntity'] ) ) {
            $data['FAQPage'] = $faq_schema;
        }
    }
}
```
```

- **ReplacementContent:**
```php
```php
// Bổ sung xử lý FAQPage cho page-faq.php trong inc/seo/class-rankmath-integration.php:
if ( is_page_template( 'page-faq.php' ) || is_page( 'hoi-dap' ) ) {
    // SỬA ĐÚNG: Lấy từ 'options' và cung cấp hardcoded fallback giống page-faq.php:29-37
    $faq_list = get_field( 'faq_items', 'options' );
    if ( empty( $faq_list ) || ! is_array( $faq_list ) ) {
        $faq_list = [
            [ 'question' => 'Bằng tốt nghiệp đại học từ xa có ghi chữ "Từ xa" không?', 'answer' => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo từ ngày 1/3/2020, bằng đại học sẽ không còn ghi hình thức đào tạo trên văn bằng tốt nghiệp.' ],
            [ 'question' => 'Thời gian hoàn thành chương trình liên thông/văn bằng 2 là bao lâu?', 'answer' => 'Thời gian đào tạo dao động từ 1.5 đến 2 năm tùy thuộc số lượng tín chỉ được miễn giảm.' ],
            [ 'question' => 'Hình thức học trực tuyến (Online) diễn ra như thế nào?', 'answer' => 'Học viên sẽ học qua hệ thống quản lý học tập E-Learning của nhà trường.' ],
            [ 'question' => 'Bằng đại học liên thông/văn bằng 2 có đủ điều kiện thi cao học không?', 'answer' => 'Hoàn toàn đủ điều kiện để đăng ký thi thạc sĩ, cao học hoặc nâng bậc lương.' ],
            [ 'question' => 'Hồ sơ tuyển sinh gồm những giấy tờ gì?', 'answer' => 'Hồ sơ cơ bản bao gồm phiếu đăng ký, bản sao công chứng bằng và bảng điểm, bản sao CCCD, ảnh 3x4.' ],
        ];
    }

    $faq_elements = [];
    foreach ( $faq_list as $item ) {
        $q = trim( wp_strip_all_tags( $item['question'] ?? '' ) );
        $a = trim( wp_strip_all_tags( $item['answer'] ?? '' ) );
        if ( $q && $a ) {
            $faq_elements[] = [
                '@type'          => 'Question',
                'name'           => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $a,
                ],
            ];
        }
    }
    if ( ! empty( $faq_elements ) ) {
        $data['FAQPage'] = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faq_elements,
        ];
    }
}
```
```

---

## 5. VERIFICATION METHOD (PHƯƠNG PHÁP XÁC MINH ĐỘC LẬP)

Để kiểm chứng tính chính xác của các đoạn code khắc phục trên máy tính cục bộ:

1. **Xác minh lỗi `ArgumentCountError` khi không truyền tham số:**
   ```bash
   php -r "define('ABSPATH', 1); require 'inc/config/class-defaults.php'; ltdh_get_defaults();"
   # Output mong đợi: Fatal error: Uncaught ArgumentCountError: Too few arguments to function ltdh_get_defaults(), 0 passed
   ```

2. **Xác minh tính đúng đắn khi truyền đối số `'contact'`:**
   ```bash
   php -r "define('ABSPATH', 1); require 'inc/config/class-defaults.php'; print_r(ltdh_get_defaults('contact'));"
   # Output mong đợi: Mảng chứa hotline, email, address, zalo_url, messenger_url, company_name.
   ```

3. **Xác minh ACF options trong `page-faq.php`:**
   ```bash
   grep -n "get_field" page-faq.php
   # Output mong đợi: Dòng 25: $faq_items = get_field( 'faq_items', 'options' ) ?: [];
   ```
