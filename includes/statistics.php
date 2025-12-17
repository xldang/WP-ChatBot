<?php
// WP-ChatBot Statistics Module

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 创建统计数据表
function wp_chatbot_create_stats_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        event_type varchar(50) NOT NULL,
        event_data longtext NOT NULL,
        user_id bigint(20) unsigned DEFAULT 0,
        session_id varchar(100) DEFAULT '',
        ip_address varchar(45) DEFAULT '',
        user_agent text DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY event_type (event_type),
        KEY created_at (created_at),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// 获取客户端IP地址
function wp_chatbot_get_client_ip() {
    $ip_headers = array(
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // 处理多个IP的情况（取第一个）
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            // 验证IP格式
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return '';
}

// 记录统计数据
function wp_chatbot_log_stats($event_type, $event_data = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    // 确保表存在
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        wp_chatbot_create_stats_table();
    }

    $current_user_id = get_current_user_id();
    // Generate session ID if not exists (compatible with older WP versions)
    if (!session_id()) {
        session_start();
    }
    $session_id = session_id() ?: uniqid('wp_chatbot_', true);

    $data = array(
        'event_type' => $event_type,
        'event_data' => wp_json_encode($event_data),
        'user_id' => $current_user_id,
        'session_id' => $session_id,
        'ip_address' => wp_chatbot_get_client_ip(),
        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
        'created_at' => current_time('mysql')
    );

    $wpdb->insert($table_name, $data);
}

// 获取统计数据
function wp_chatbot_get_stats($event_type = '', $days = 30, $limit = 1000) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    $where = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)";
    if (!empty($event_type)) {
        $where .= $wpdb->prepare(" AND event_type = %s", $event_type);
    }

    $sql = "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d";
    $results = $wpdb->get_results($wpdb->prepare($sql, $limit));

    // 解析JSON数据
    foreach ($results as &$result) {
        $result->event_data = json_decode($result->event_data, true);
    }

    return $results;
}

// 获取汇总统计
function wp_chatbot_get_summary_stats($days = 30) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    $stats = array(
        'total_conversations' => 0,
        'total_messages' => 0,
        'total_users' => 0,
        'api_calls' => 0,
        'api_success' => 0,
        'api_errors' => 0,
        'avg_response_time' => 0,
        'today_conversations' => 0,
        'week_conversations' => 0,
        'month_conversations' => 0
    );

    // 总对话数
    $stats['total_conversations'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT session_id) FROM $table_name
         WHERE event_type = 'conversation_start'
         AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days
    ));

    // 今日对话数
    $stats['today_conversations'] = $wpdb->get_var(
        "SELECT COUNT(DISTINCT session_id) FROM $table_name
         WHERE event_type = 'conversation_start'
         AND DATE(created_at) = CURDATE()"
    );

    // 本周对话数
    $stats['week_conversations'] = $wpdb->get_var(
        "SELECT COUNT(DISTINCT session_id) FROM $table_name
         WHERE event_type = 'conversation_start'
         AND YEARWEEK(created_at) = YEARWEEK(NOW())"
    );

    // 本月对话数
    $stats['month_conversations'] = $wpdb->get_var(
        "SELECT COUNT(DISTINCT session_id) FROM $table_name
         WHERE event_type = 'conversation_start'
         AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())"
    );

    // 总消息数
    $stats['total_messages'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name
         WHERE event_type IN ('user_message', 'ai_response')
         AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days
    ));

    // 独立用户数
    $stats['total_users'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT user_id) FROM $table_name
         WHERE user_id > 0
         AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days
    ));

    // API调用统计
    $api_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT
            COUNT(*) as total_calls,
            SUM(CASE WHEN JSON_EXTRACT(event_data, '$.success') = 'true' THEN 1 ELSE 0 END) as success_calls,
            AVG(JSON_EXTRACT(event_data, '$.response_time')) as avg_response_time
         FROM $table_name
         WHERE event_type = 'api_call'
         AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", $days
    ), ARRAY_A);

    if ($api_stats) {
        $stats['api_calls'] = intval($api_stats['total_calls']);
        $stats['api_success'] = intval($api_stats['success_calls']);
        $stats['avg_response_time'] = round(floatval($api_stats['avg_response_time']), 2);
    }

    // 错误统计
    $stats['api_errors'] = $stats['api_calls'] - $stats['api_success'];

    return $stats;
}

// 清理旧的统计数据
function wp_chatbot_cleanup_stats($days_to_keep = 90) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
        $days_to_keep
    ));
}

// 获取热门问题关键词
function wp_chatbot_get_popular_keywords($days = 30, $limit = 20) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT
            JSON_EXTRACT(event_data, '$.content') as content,
            COUNT(*) as frequency
         FROM $table_name
         WHERE event_type = 'user_message'
         AND JSON_EXTRACT(event_data, '$.content') IS NOT NULL
         AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
         GROUP BY JSON_EXTRACT(event_data, '$.content')
         ORDER BY frequency DESC
         LIMIT %d", $days, $limit
    ));

    return $results;
}

// 获取时段分布
function wp_chatbot_get_hourly_distribution($days = 30) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_chatbot_stats';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT
            HOUR(created_at) as hour,
            COUNT(*) as count
         FROM $table_name
         WHERE event_type = 'conversation_start'
         AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
         GROUP BY HOUR(created_at)
         ORDER BY hour", $days
    ), ARRAY_A);

    // 确保24小时都有数据
    $hourly_data = array_fill(0, 24, 0);
    foreach ($results as $result) {
        $hourly_data[intval($result['hour'])] = intval($result['count']);
    }

    return $hourly_data;
}

// 初始化统计功能
function wp_chatbot_stats_init() {
    // 创建数据库表
    wp_chatbot_create_stats_table();

    // 注册清理任务（每月执行一次）
    if (!wp_next_scheduled('wp_chatbot_cleanup_stats')) {
        wp_schedule_event(time(), 'monthly', 'wp_chatbot_cleanup_stats');
    }
}
add_action('init', 'wp_chatbot_stats_init');

// 清理任务钩子
add_action('wp_chatbot_cleanup_stats', 'wp_chatbot_cleanup_stats');

// 激活插件时初始化
register_activation_hook(__FILE__, 'wp_chatbot_stats_init');
