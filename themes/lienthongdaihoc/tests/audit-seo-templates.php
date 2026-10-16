<?php
/**
 * SEO Audit Script: Inspect Heading Outline, Meta Tags, and Schemas
 */

$urls = [
    'Trang chủ'             => 'http://localhost:10028/',
    'Chi tiết Chương trình' => 'http://localhost:10028/lien-thong-cong-nghe-thong-tin-chinh-quy-utc/',
    'Chi tiết Trường'       => 'http://localhost:10028/truong-dai-hoc-giao-thong-van-tai/',
    'Chi tiết Ngành'        => 'http://localhost:10028/nganh-cong-nghe-thong-tin/',
    'Danh mục Ngành'        => 'http://localhost:10028/nganh-hoc/',
    'Danh mục Trường'       => 'http://localhost:10028/truong-da-hoc/',
    'Hình thức đào tạo'     => 'http://localhost:10028/hinh-thuc-dao-tao/',
    'Hệ Từ xa (Tax)'        => 'http://localhost:10028/hinh-thuc-dao-tao/dao-tao-tu-xa/',
    'Hỏi đáp FAQ'           => 'http://localhost:10028/cau-hoi-thuong-gap/',
    'Kiểm tra điều kiện'    => 'http://localhost:10028/kiem-tra-dieu-kien/',
];

$audit_results = [];

foreach ( $urls as $label => $url ) {
    echo str_repeat( '=', 70 ) . "\n";
    echo "AUDIT: {$label} ({$url})\n";
    echo str_repeat( '=', 70 ) . "\n";

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (SEO-Audit-Agent)' );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
    $html = curl_exec( $ch );
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    curl_close( $ch );

    echo "HTTP STATUS: {$http_code}\n";
    if ( $http_code !== 200 || ! $html ) {
        echo "FAILED TO LOAD HTML\n\n";
        continue;
    }

    // Title
    preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $m_title );
    $title = isset( $m_title[1] ) ? trim( html_entity_decode( $m_title[1] ) ) : 'NONE';
    $title_len = mb_strlen( $title );
    $title_status = ( $title_len >= 30 && $title_len <= 65 ) ? 'PASS' : ( $title_len < 30 ? 'WARN (Too short)' : 'WARN (Too long)' );
    echo "TITLE [{$title_len} chars | {$title_status}]: {$title}\n";

    // Meta Description
    preg_match( '/<meta[^>]+name=[\'"]description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_desc );
    $desc = isset( $m_desc[1] ) ? trim( html_entity_decode( $m_desc[1] ) ) : 'NONE';
    $desc_len = mb_strlen( $desc );
    $desc_status = ( $desc_len >= 120 && $desc_len <= 165 ) ? 'PASS' : ( $desc_len < 120 ? 'WARN (Too short)' : 'WARN (Too long)' );
    echo "META DESC [{$desc_len} chars | {$desc_status}]: {$desc}\n";

    // Canonical
    preg_match( '/<link[^>]+rel=[\'"]canonical[\'"][^>]+href=[\'"](.*?)[\'"]/is', $html, $m_canon );
    $canon = isset( $m_canon[1] ) ? trim( $m_canon[1] ) : 'NONE';
    echo "CANONICAL: {$canon}\n";

    // Robots
    preg_match( '/<meta[^>]+name=[\'"]robots[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_robots );
    $robots = isset( $m_robots[1] ) ? trim( $m_robots[1] ) : 'NONE';
    echo "ROBOTS: {$robots}\n";

    // OG Tags
    preg_match( '/<meta[^>]+property=[\'"]og:title[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_og_t );
    preg_match( '/<meta[^>]+property=[\'"]og:description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_og_d );
    preg_match( '/<meta[^>]+property=[\'"]og:image[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $m_og_i );
    $og_t = isset( $m_og_t[1] ) ? html_entity_decode( $m_og_t[1] ) : 'NONE';
    $og_d = isset( $m_og_d[1] ) ? html_entity_decode( $m_og_d[1] ) : 'NONE';
    $og_i = isset( $m_og_i[1] ) ? $m_og_i[1] : 'NONE';
    echo "OG:TITLE: {$og_t}\n";
    echo "OG:DESC: {$og_d}\n";
    echo "OG:IMAGE: {$og_i}\n";

    // Headings Outline
    preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1_m );
    $h1s = array_map( function( $v ) { return trim( strip_tags( html_entity_decode( $v ) ) ); }, $h1_m[1] ?? [] );
    $h1_status = ( count( $h1s ) === 1 ) ? 'PASS (1 H1)' : ( count( $h1s ) === 0 ? 'CRITICAL (0 H1)' : 'WARN (Multiple H1s: ' . count( $h1s ) . ')' );
    echo "H1 COUNT [{$h1_status}]:\n";
    foreach ( $h1s as $idx => $h ) {
        echo "  H1[" . ( $idx + 1 ) . "]: {$h}\n";
    }

    preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/is', $html, $h2_m );
    $h2s = array_map( function( $v ) { return trim( strip_tags( html_entity_decode( $v ) ) ); }, $h2_m[1] ?? [] );
    echo "H2 COUNT (" . count( $h2s ) . "):\n";
    foreach ( array_slice( $h2s, 0, 8 ) as $idx => $h ) {
        echo "  H2[" . ( $idx + 1 ) . "]: {$h}\n";
    }
    if ( count( $h2s ) > 8 ) {
        echo "  ... and " . ( count( $h2s ) - 8 ) . " more H2s\n";
    }

    preg_match_all( '/<h3[^>]*>(.*?)<\/h3>/is', $html, $h3_m );
    $h3s = array_map( function( $v ) { return trim( strip_tags( html_entity_decode( $v ) ) ); }, $h3_m[1] ?? [] );
    echo "H3 COUNT (" . count( $h3s ) . "):\n";
    foreach ( array_slice( $h3s, 0, 5 ) as $idx => $h ) {
        echo "  H3[" . ( $idx + 1 ) . "]: {$h}\n";
    }
    if ( count( $h3s ) > 5 ) {
        echo "  ... and " . ( count( $h3s ) - 5 ) . " more H3s\n";
    }

    // Schemas
    preg_match_all( '/<script[^>]+type=[\'"]application\/ld\+json[\'"][^>]*>(.*?)<\/script>/is', $html, $s_m );
    echo "SCHEMA BLOCKS: " . count( $s_m[1] ) . "\n";
    foreach ( $s_m[1] as $idx => $s_text ) {
        preg_match_all( '/"@type"\s*:\s*"([^"]+)"/', $s_text, $t_m );
        echo "  Block " . ( $idx + 1 ) . " types: " . implode( ', ', $t_m[1] ?? [] ) . "\n";
    }

    // Images alt check
    preg_match_all( '/<img[^>]+>/is', $html, $img_m );
    $total_imgs = count( $img_m[0] ?? [] );
    $missing_alt = 0;
    foreach ( ( $img_m[0] ?? [] ) as $img_tag ) {
        if ( ! preg_match( '/alt=[\'"][^\'"]*[\'"]/i', $img_tag ) || preg_match( '/alt=[\'"]\s*[\'"]/i', $img_tag ) ) {
            $missing_alt++;
        }
    }
    echo "IMAGES: {$total_imgs} total, {$missing_alt} missing/empty alt\n";

    echo "\n";
}
