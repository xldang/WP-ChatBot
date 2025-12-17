<?php
/*
Plugin Name: DeepSeek ChatGPT Q&A
Description: 在 WordPress 中添加 DeepSeek 问答页面和自定义提示词包装设置。
Version: 1.0.0
Author: 你的名字
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 加载功能文件
require_once plugin_dir_path(__FILE__) . 'includes/chatgpt-qa-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

// 可在此处添加全局钩子或初始化代码 