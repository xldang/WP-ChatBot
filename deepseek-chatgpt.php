<?php
/*
Plugin Name: WP-ChatBot
Description: 在 WordPress 中添加 DeepSeek 问答页面、自定义提示词包装设置和统计功能。
Version: 1.0.1
Author: xldang
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 加载功能文件
require_once plugin_dir_path(__FILE__) . 'includes/chatgpt-qa-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard.php';

// 可在此处添加全局钩子或初始化代码
