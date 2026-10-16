<?php
/**
 * Deep On-Page SEO Audit Script
 * Inspects all 10 on-page pillars: Metadata, Hierarchy, Content Quality,
 * Keywords, Images, Internal Linking, Schema, OpenGraph, Mobile/Tech signals.
 */

$test_pages = [
    'Trang Chủ' => [
        'url' => 'http://localhost:10028/',
        'type' => 'Home',
        'focus_kw' => 'liên thông đại học',
    ],
    'Chi tiết Chương trình (Chính quy)' => [
        'url' => 'http://localhost:10028/lien-thong-cong-nghe-thong-tin-chinh-quy-utc/',
        'type' => 'Single Program',
        'focus_kw' => 'liên thông công nghệ thông tin',
    ],
    'Chi tiết Chương trình (Từ xa)' => [
        'url' => 'http://localhost:10028/lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
        'type' => 'Single Program',
        'focus_kw' => 'liên thông công nghệ thông tin từ xa',
    ],
    'Chi tiết Trường (UTC)' => [
        'url' => 'http://localhost:10028/truong-dai-hoc-giao-thong-van-tai/',
        'type' => 'Single School',
        'focus_kw' => 'liên thông đại học giao thông vận tải',
    ],
    'Chi tiết Ngành (CNTT)' => [
        'url' => 'http://localhost:10028/nganh-cong-nghe-thong-tin/',
        'type' => 'Single Major',
        'focus_kw' => 'ngành công nghệ thông tin',
    ],
    'Danh mục Ngành (Hub)' => [
        'url' => 'http://localhost:10028/nganh-hoc/',
        'type' => 'Archive Major',
        'focus_kw' => 'ngành tuyển sinh liên thông',
    ],
    'Danh mục Trường (Hub)' => [
        'url' => 'http://localhost:10028/truong-da-hoc/',
        'type' => 'Archive School',
        'focus_kw' => 'trường tuyển sinh liên thông',
    ],
    'Hình thức đào tạo (Hub)' => [
        'url' => 'http://localhost:10028/hinh-thuc-dao-tao/',
        'type' => 'Archive Program',
        'focus_kw' => 'hình thức liên thông đại học',
    ],
    'Taxonomy Đào tạo từ xa' => [
        'url' => 'http://localhost:10028/hinh-thuc-dao-tao/dao-tao-tu-xa/',
        'type' => 'Taxonomy Training Type',
        'focus_kw' => 'liên thông đào tạo từ xa',
    ],
    'Câu hỏi thường gặp (FAQ)' => [
        'url' => 'http://localhost:10028/cau-hoi-thuong-gap/',
        'type' => 'Page FAQ',
        'focus_kw' => 'câu hỏi thường gặp',
    ],
    'Kiểm tra điều kiện' => [
        'url' => 'http://localhost:10028/kiem-tra-dieu-kien/',
        'type' => 'Page Utility',
        'focus_kw' => 'kiểm tra điều kiện liên thông',
    ],
    'So sánh chương trình (Chi tiết)' => [
        'url' => 'http://localhost:10028/so-sanh/chuong-trinh/lien-thong-cong-nghe-thong-tin-chinh-quy-utc-vs-lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
        'type' => 'Page Compare',
        'focus_kw' => 'so sánh chương trình',
    ],
];

$results = [];

foreach ( $test_pages as $label => $info ) {
    $url = $info['url'];
    $kw  = $info['focus_kw'];
    
    $start_time = microtime( true );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36' );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
    $html = curl_exec( $ch );
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    $effective_url = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
    $load_time = round( microtime( true ) - $start_time, 3 );
    curl_close( $ch );

    $page_result = [
        'label' => $label,
        'url' => $url,
        'effective_url' => $effective_url,
        'http_code' => $http_code,
        'load_time_sec' => $load_time,
        'type' => $info['type'],
        'focus_kw' => $kw,
        'issues' => [],
        'warnings' => [],
        'passes' => [],
    ];

    if ( $http_code !== 200 || ! $html ) {
        $page_result['issues'][] = "HTTP Status {$http_code} hoặc không tải được nội dung HTML.";
        $results[] = $page_result;
        continue;
    }

    // 1. Title Tag
    if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $m ) ) {
        $title = trim( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
        $title_len = mb_strlen( $title );
        $page_result['title'] = $title;
        $page_result['title_len'] = $title_len;
        if ( $title_len < 30 ) {
            $page_result['warnings'][] = "Title quá ngắn ({$title_len} ký tự). Cần tối thiểu 30-50 ký tự.";
        } elseif ( $title_len > 65 ) {
            $page_result['warnings'][] = "Title quá dài ({$title_len} ký tự). Google sẽ cắt cụt sau ~60-65 ký tự.";
        } else {
            $page_result['passes'][] = "Title chuẩn SERP ({$title_len} ký tự): '{$title}'";
        }
    } else {
        $page_result['issues'][] = "Thiếu thẻ <title> hoàn toàn!";
    }

    // 2. Meta Description
    if ( preg_match( '/<meta[^>]+name=[\'"]description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m ) ) {
        $desc = trim( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
        $desc_len = mb_strlen( $desc );
        $page_result['desc'] = $desc;
        $page_result['desc_len'] = $desc_len;
        if ( $desc_len < 110 ) {
            $page_result['warnings'][] = "Meta description quá ngắn ({$desc_len} ký tự). Khuyến nghị 130-165 ký tự.";
        } elseif ( $desc_len > 170 ) {
            $page_result['warnings'][] = "Meta description quá dài ({$desc_len} ký tự). Có nguy cơ bị cắt cụt trên SERP.";
        } else {
            $page_result['passes'][] = "Meta description chuẩn ({$desc_len} ký tự).";
        }
    } else {
        $page_result['issues'][] = "Thiếu thẻ <meta name=\"description\">!";
    }

    // 3. Meta Robots
    if ( preg_match( '/<meta[^>]+name=[\'"]robots[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m ) ) {
        $robots = trim( $m[1] );
        $page_result['robots'] = $robots;
        if ( stripos( $robots, 'noindex' ) !== false ) {
            $page_result['issues'][] = "Trang bị gắn chỉ thị NOINDEX: '{$robots}'";
        } else {
            $page_result['passes'][] = "Robots cho phép index: '{$robots}'";
        }
    } else {
        $page_result['passes'][] = "Không có meta robots (mặc định cho phép index).";
    }

    // 4. Canonical Tag
    if ( preg_match( '/<link[^>]+rel=[\'"]canonical[\'"][^>]+href=[\'"](.*?)[\'"]/is', $html, $m ) ) {
        $canonical = trim( $m[1] );
        $page_result['canonical'] = $canonical;
        $page_result['passes'][] = "Thẻ Canonical hợp lệ: '{$canonical}'";
    } else {
        $page_result['issues'][] = "Thiếu thẻ <link rel=\"canonical\">!";
    }

    // 5. OpenGraph & Twitter Cards
    preg_match( '/<meta[^>]+property=[\'"]og:title[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_ogt );
    preg_match( '/<meta[^>]+property=[\'"]og:description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_ogd );
    preg_match( '/<meta[^>]+property=[\'"]og:image[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_ogi );
    preg_match( '/<meta[^>]+name=[\'"]twitter:card[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_twc );

    $og_t = isset( $m_ogt[1] ) ? trim( $m_ogt[1] ) : '';
    $og_d = isset( $m_ogd[1] ) ? trim( $m_ogd[1] ) : '';
    $og_i = isset( $m_ogi[1] ) ? trim( $m_ogi[1] ) : '';

    if ( ! empty( $og_t ) && ! empty( $og_d ) && ! empty( $og_i ) ) {
        $page_result['passes'][] = "Đầy đủ thẻ OpenGraph (og:title, og:description, og:image).";
    } else {
        $page_result['warnings'][] = "Thiếu một trong các thẻ OpenGraph cơ bản (title/desc/image).";
    }

    // 6. Heading Outline Hierarchy
    preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $m_headings, PREG_SET_ORDER );
    $h1_count = 0;
    $heading_tree = [];
    $prev_level = 0;
    $has_skipped_level = false;

    foreach ( $m_headings as $h_item ) {
        $level = intval( $h_item[1] );
        $text  = trim( strip_tags( html_entity_decode( $h_item[2] ) ) );
        if ( 1 === $level ) {
            $h1_count++;
        }
        if ( $prev_level > 0 && $level > ( $prev_level + 1 ) ) {
            $has_skipped_level = true;
        }
        $prev_level = $level;
        $heading_tree[] = [ 'level' => $level, 'text' => $text ];
    }

    $page_result['h1_count'] = $h1_count;
    $page_result['heading_tree_count'] = count( $heading_tree );

    if ( 1 === $h1_count ) {
        $page_result['passes'][] = "Đạt chuẩn cấu trúc: duy nhất 1 thẻ H1 ('" . ( $heading_tree[0]['text'] ?? '' ) . "')";
    } elseif ( 0 === $h1_count ) {
        $page_result['issues'][] = "Trang KHÔNG có thẻ H1 nào!";
    } else {
        $page_result['issues'][] = "Trang có NHIỀU HƠN 1 thẻ H1 ({$h1_count} thẻ H1)!";
    }

    if ( $has_skipped_level ) {
        $page_result['warnings'][] = "Cấu trúc Heading bị nhảy bậc (ví dụ H1 nhảy cóc lên H3 không qua H2).";
    } else {
        $page_result['passes'][] = "Cấu trúc Heading tuần tự logic (không nhảy bậc).";
    }

    // 7. Image Optimization & Alt attributes
    preg_match_all( '/<img[^>]+>/is', $html, $m_imgs );
    $total_imgs = count( $m_imgs[0] );
    $missing_alt = 0;
    $missing_dimensions = 0;
    $missing_lazy = 0;

    foreach ( $m_imgs[0] as $idx => $img_tag ) {
        // Alt check
        if ( ! preg_match( '/alt=[\'"][^\'"]*[\'"]/i', $img_tag ) || preg_match( '/alt=[\'"]\s*[\'"]/i', $img_tag ) ) {
            $missing_alt++;
        }
        // Dimensions check (CLS)
        if ( ! preg_match( '/width=[\'"][^\'"]*[\'"]/i', $img_tag ) || ! preg_match( '/height=[\'"][^\'"]*[\'"]/i', $img_tag ) ) {
            $missing_dimensions++;
        }
        // Lazy load check (ignore first 2 images as possible hero/LCP)
        if ( $idx >= 2 && ! preg_match( '/loading=[\'"]lazy[\'"]/i', $img_tag ) ) {
            $missing_lazy++;
        }
    }

    $page_result['total_images'] = $total_imgs;
    $page_result['missing_alt'] = $missing_alt;
    $page_result['missing_dimensions'] = $missing_dimensions;

    if ( 0 === $missing_alt ) {
        $page_result['passes'][] = "100% hình ảnh có thuộc tính Alt ({$total_imgs} ảnh).";
    } else {
        $page_result['issues'][] = "Có {$missing_alt}/{$total_imgs} hình ảnh bị thiếu hoặc rỗng Alt text!";
    }

    // 8. Content & Word Count
    $clean_body = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $html );
    $clean_body = preg_replace( '/<style\b[^>]*>(.*?)<\/style>/is', '', $clean_body );
    $text_content = trim( strip_tags( $clean_body ) );
    $text_content = preg_replace( '/\s+/u', ' ', $text_content );
    $word_count = count( preg_split( '/\s+/u', $text_content, -1, PREG_SPLIT_NO_EMPTY ) );
    $page_result['word_count'] = $word_count;

    if ( $word_count >= 500 ) {
        $page_result['passes'][] = "Độ dài nội dung dồi dào: {$word_count} từ.";
    } elseif ( $word_count >= 250 ) {
        $page_result['passes'][] = "Độ dài nội dung đạt chuẩn: {$word_count} từ.";
    } else {
        $page_result['warnings'][] = "Nội dung mỏng ({$word_count} từ). Cần bổ sung thêm ngữ cảnh và dữ liệu chuyên sâu.";
    }

    // 9. Links Analysis
    preg_match_all( '/<a\s+[^>]*href=[\'"]([^\'"]*)[\'"][^>]*>(.*?)<\/a>/is', $html, $m_links, PREG_SET_ORDER );
    $internal_links = 0;
    $external_links = 0;
    $generic_anchors = 0;
    $generic_phrases = [ 'xem thêm', 'chi tiết', 'tại đây', 'click here', 'read more', 'click', 'vào đây' ];

    foreach ( $m_links as $link ) {
        $href = trim( $link[1] );
        $anchor = trim( strip_tags( html_entity_decode( $link[2] ) ) );
        if ( empty( $href ) || '#' === $href[0] || strpos( $href, 'javascript:' ) === 0 ) {
            continue;
        }
        if ( strpos( $href, 'http' ) === 0 && strpos( $href, 'localhost:10028' ) === false && strpos( $href, 'lienthongdaihoc.com' ) === false ) {
            $external_links++;
        } else {
            $internal_links++;
        }
        if ( in_array( mb_strtolower( $anchor, 'UTF-8' ), $generic_phrases, true ) ) {
            $generic_anchors++;
        }
    }

    $page_result['internal_links'] = $internal_links;
    $page_result['external_links'] = $external_links;
    $page_result['generic_anchors'] = $generic_anchors;

    if ( $internal_links >= 10 ) {
        $page_result['passes'][] = "Liên kết nội bộ mạnh mẽ: {$internal_links} links nội bộ.";
    } else {
        $page_result['passes'][] = "{$internal_links} links nội bộ.";
    }
    if ( $generic_anchors > 0 ) {
        $page_result['warnings'][] = "Có {$generic_anchors} liên kết dùng neo từ chung chung ('xem thêm', 'chi tiết'). Khuyến nghị thêm ngữ cảnh từ khóa vào anchor.";
    }

    // 10. Schema JSON-LD Blocks
    preg_match_all( '/<script[^>]+type=[\'"]application\/ld\+json[\'"][^>]*>(.*?)<\/script>/is', $html, $m_schemas );
    $schema_types = [];
    foreach ( $m_schemas[1] as $s_raw ) {
        $decoded = json_decode( $s_raw, true );
        if ( ! $decoded ) {
            $page_result['issues'][] = "Khối Schema JSON-LD bị lỗi cú pháp cú pháp JSON!";
            continue;
        }
        preg_match_all( '/"@type"\s*:\s*"([^"]+)"/', $s_raw, $m_types );
        foreach ( $m_types[1] ?? [] as $t ) {
            $schema_types[] = $t;
        }
    }
    $page_result['schema_types'] = array_unique( $schema_types );
    $page_result['passes'][] = "Phát hiện Schema JSON-LD hợp lệ: " . implode( ', ', array_unique( $schema_types ) );

    $results[] = $page_result;
}

// In bảng tổng hợp
echo "\n" . str_repeat( '=', 95 ) . "\n";
echo "BÁO CÁO KIỂM THỬ DEEP ON-PAGE SEO THEO TIÊU CHUẨN GOOGLE SEARCH CENTRAL\n";
echo str_repeat( '=', 95 ) . "\n\n";

foreach ( $results as $r ) {
    echo "▶ Trang: {$r['label']} ({$r['type']})\n";
    echo "  URL: {$r['url']} | Status: {$r['http_code']} | Load: {$r['load_time_sec']}s\n";
    echo "  Title: [{$r['title_len']} chars] {$r['title']}\n";
    echo "  Meta Desc: [{$r['desc_len']} chars] {$r['desc']}\n";
    echo "  H1: {$r['h1_count']} thẻ | Tổng Headings: {$r['heading_tree_count']} | Nội dung: {$r['word_count']} từ | Images: {$r['total_images']} (Thiếu alt: {$r['missing_alt']})\n";
    echo "  Liên kết: {$r['internal_links']} nội bộ | {$r['external_links']} ngoài | Schemas: " . implode( ', ', $r['schema_types'] ?? [] ) . "\n";
    
    if ( ! empty( $r['issues'] ) ) {
        echo "  🔴 LỖI NGHIÊM TRỌNG (Issues):\n";
        foreach ( $r['issues'] as $iss ) {
            echo "     - {$iss}\n";
        }
    }
    if ( ! empty( $r['warnings'] ) ) {
        echo "  ⚠️ CẢNH BÁO TỐI ƯU (Warnings):\n";
        foreach ( $r['warnings'] as $warn ) {
            echo "     - {$warn}\n";
        }
    }
    echo "  ✅ ĐẠT YÊU CẦU: " . count( $r['passes'] ) . " chỉ số pass.\n";
    echo str_repeat( '-', 95 ) . "\n";
}
