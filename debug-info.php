<?php
// 诊断信息页面
// 访问方式: https://your-site.com/wp-content/plugins/deepseek-chatgpt/debug-info.php

// 检查是否在WordPress环境中
if (!defined('ABSPATH')) {
    // 如果不在WordPress环境中，尝试加载WordPress
    $wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    }
}

// 安全检查
if (!current_user_can('manage_options')) {
    die('权限不足');
}

$api_url = get_option('deepseek_api_url', 'https://api.deepseek.com');
$api_key = get_option('deepseek_api_key', '');
$prefix = get_option('deepseek_prefix', '');
$suffix = get_option('deepseek_suffix', '');
$context = get_option('deepseek_context', '');
$background = get_option('deepseek_background', '');
$person = get_option('deepseek_person', 'third');

$log_file = dirname(__FILE__) . '/debug.log';
$log_exists = file_exists($log_file);
$log_size = $log_exists ? filesize($log_file) : 0;
$log_modified = $log_exists ? date('Y-m-d H:i:s', filemtime($log_file)) : 'N/A';

?>
<!DOCTYPE html>
<html>
<head>
    <title>DeepSeek ChatGPT 插件诊断信息</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #007cba;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }
        .section h2 {
            color: #495057;
            margin-top: 0;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #f8f9fa;
            padding-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
            color: #495057;
        }
        .info-value {
            flex: 1;
            color: #6c757d;
        }
        .status-ok {
            color: #28a745;
        }
        .status-error {
            color: #dc3545;
        }
        .status-warning {
            color: #ffc107;
        }
        .btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn:hover {
            background: #005a87;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 DeepSeek ChatGPT 插件诊断信息</h1>
        
        <div class="section">
            <h2>📋 系统信息</h2>
            <div class="info-row">
                <div class="info-label">WordPress版本:</div>
                <div class="info-value"><?php echo get_bloginfo('version'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">PHP版本:</div>
                <div class="info-value"><?php echo PHP_VERSION; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">插件版本:</div>
                <div class="info-value">1.0.1</div>
            </div>
            <div class="info-row">
                <div class="info-label">当前时间:</div>
                <div class="info-value"><?php echo current_time('Y-m-d H:i:s'); ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>⚙️ API配置</h2>
            <div class="info-row">
                <div class="info-label">API URL:</div>
                <div class="info-value <?php echo !empty($api_url) ? 'status-ok' : 'status-error'; ?>">
                    <?php echo htmlspecialchars($api_url); ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">API密钥:</div>
                <div class="info-value <?php echo !empty($api_key) ? 'status-ok' : 'status-error'; ?>">
                    <?php echo !empty($api_key) ? substr($api_key, 0, 10) . '...' : '未设置'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">人称设置:</div>
                <div class="info-value"><?php echo htmlspecialchars($person); ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>📝 提示词配置</h2>
            <div class="info-row">
                <div class="info-label">前缀:</div>
                <div class="info-value"><?php echo htmlspecialchars($prefix ?: '未设置'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">后缀:</div>
                <div class="info-value"><?php echo htmlspecialchars($suffix ?: '未设置'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">上下文:</div>
                <div class="info-value"><?php echo htmlspecialchars($context ?: '未设置'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">背景知识:</div>
                <div class="info-value"><?php echo htmlspecialchars($background ?: '未设置'); ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>📊 日志信息</h2>
            <div class="info-row">
                <div class="info-label">日志文件:</div>
                <div class="info-value"><?php echo htmlspecialchars($log_file); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">文件存在:</div>
                <div class="info-value <?php echo $log_exists ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $log_exists ? '是' : '否'; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">文件大小:</div>
                <div class="info-value"><?php echo number_format($log_size); ?> 字节</div>
            </div>
            <div class="info-row">
                <div class="info-label">最后修改:</div>
                <div class="info-value"><?php echo $log_modified; ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>🔗 快速操作</h2>
            <a href="log-viewer.php" class="btn">📋 查看日志</a>
            <a href="<?php echo admin_url('options-general.php?page=deepseek-settings'); ?>" class="btn">⚙️ 插件设置</a>
            <a href="javascript:location.reload()" class="btn">🔄 刷新页面</a>
        </div>
        
        <div class="section">
            <h2>🐛 故障排除建议</h2>
            <ul>
                <li><strong>如果API密钥未设置:</strong> 请前往插件设置页面配置API密钥</li>
                <li><strong>如果日志文件不存在:</strong> 尝试使用插件功能，日志文件会自动创建</li>
                <li><strong>如果遇到缓存问题:</strong> 清除浏览器缓存或使用Ctrl+F5强制刷新</li>
                <li><strong>如果JavaScript不工作:</strong> 检查浏览器控制台是否有错误信息</li>
            </ul>
        </div>
    </div>
</body>
</html> 