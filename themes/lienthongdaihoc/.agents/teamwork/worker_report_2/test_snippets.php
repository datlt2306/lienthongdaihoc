<?php
// Scratch test for PHP snippets syntax verification

// 1. SCHEMA-CRIT-01 Snippet Syntax Test
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

// 2. SCHEMA-HIGH-01 Snippet Syntax Test
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    $contact_defaults = ltdh_get_defaults( 'contact' );

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
        'telephone'   => $contact_defaults['hotline'] ?? '',
        'email'       => $contact_defaults['email'] ?? '',
        'address'     => [
            '@type'          => 'PostalAddress',
            'streetAddress'  => $contact_defaults['address'] ?? '',
            'addressCountry' => 'VN',
        ],
        'sameAs'      => array_values( array_filter( [
            $contact_defaults['facebook'] ?? '',
            $contact_defaults['youtube'] ?? '',
            $contact_defaults['zalo_url'] ?? ( $contact_defaults['zalo'] ?? '' ),
        ] ) ),
    ];

    return $data;
}, 99, 2 );

// 3. SCHEMA-HIGH-03 Snippet Syntax Test
function test_faq_schema() {
    $data = [];
    if ( is_page_template( 'page-faq.php' ) || is_page( 'hoi-dap' ) ) {
        // SỬA ĐÚNG: Lấy từ 'options' và cung cấp hardcoded fallback giống page-faq.php:29-37
        $faq_list = get_field( 'faq_items', 'options' );
        if ( empty( $faq_list ) || ! is_array( $faq_list ) ) {
            $faq_list = [
                [ 'question' => 'Bằng tốt nghiệp đại học từ xa có ghi chữ "Từ xa" không?', 'answer' => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo từ ngày 1/3/2020, bằng đại học sẽ không còn ghi hình thức đào tạo (như Từ xa, Vừa học vừa làm, Chính quy) trên văn bằng tốt nghiệp. Tất cả phôi bằng đều có giá trị tương đương tốt nghiệp chính quy.' ],
                [ 'question' => 'Thời gian hoàn thành chương trình liên thông/văn bằng 2 là bao lâu?', 'answer' => 'Thời gian đào tạo dao động từ 1.5 đến 2 năm. Thời gian cụ thể tùy thuộc vào số lượng tín chỉ bạn được miễn giảm dựa trên bảng điểm tốt nghiệp trung cấp, cao đẳng hoặc văn bằng 1 đã có.' ],
                [ 'question' => 'Hình thức học trực tuyến (Online) diễn ra như thế nào?', 'answer' => 'Học viên sẽ học qua hệ thống quản lý học tập E-Learning của nhà trường. Bạn có thể tự học qua video bài giảng, tài liệu slide mọi lúc mọi nơi và tham gia ôn tập trực tuyến với giảng viên vào cuối tuần.' ],
                [ 'question' => 'Bằng đại học liên thông/văn bằng 2 có đủ điều kiện thi cao học không?', 'answer' => 'Hoàn toàn đủ điều kiện. Tấm bằng tốt nghiệp do các đại học đối tác cấp có đầy đủ giá trị pháp lý để bạn đăng ký thi thạc sĩ, cao học, thi công chức nhà nước hoặc nâng bậc lương.' ],
                [ 'question' => 'Hồ sơ tuyển sinh gồm những giấy tờ gì?', 'answer' => 'Hồ sơ cơ bản bao gồm: Phiếu đăng ký theo mẫu của trường, Bản sao công chứng Bằng tốt nghiệp + Bảng điểm cấp cao nhất, Bản sao CCCD, Ảnh 3x4. Bạn sẽ được chuyên viên tư vấn gửi mẫu và hướng dẫn chi tiết.' ],
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
    return $data;
}

// 4. PERF-MED-01 Snippet Syntax Test
function ltdh_get_school_unique_majors_count( int $school_id ): int {
    $program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
    if ( empty( $program_ids ) || ! is_array( $program_ids ) ) {
        return 0;
    }

    $major_ids = [];
    foreach ( $program_ids as $prog_id ) {
        $m_meta = get_post_meta( $prog_id, LTDH_META_MAJOR_REL, true );
        $m_id   = is_array( $m_meta ) ? intval( $m_meta[0] ?? 0 ) : intval( $m_meta );
        if ( $m_id && ! in_array( $m_id, $major_ids, true ) ) {
            $major_ids[] = $m_id;
        }
    }
    return count( $major_ids );
}

function test_archive_school_perf( int $school_id ) {
    $offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
    if ( ! is_array( $offered_program_ids ) ) {
        $offered_program_ids = [];
    }
    return $offered_program_ids;
}
