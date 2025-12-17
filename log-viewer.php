<?php
// 简单的日志查看器
// 访问方式: https://your-site.com/wp-content/plugins/deepseek-chatgpt/log-viewer.php

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

$log_file = dirname(__FILE__) . '/debug.log';
$log_content = '';

if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    if ($log_content === false) {
        $log_content = '无法读取日志文件';
    }
} else {
    $log_content = '日志文件不存在';
}

// 处理清空日志的请求
if (isset($_POST['clear_log']) && $_POST['clear_log'] === '1') {
    if (file_put_contents($log_file, '') !== false) {
        $log_content = '日志已清空';
    } else {
        $log_content = '清空日志失败';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>DeepSeek ChatGPT 插件日志查看器</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
        .log-content {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-height: 600px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .controls {
            margin-bottom: 20px;
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
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DeepSeek ChatGPT 插件日志查看器</h1>
        
        <div class="info">
            <strong>日志文件位置:</strong> <?php echo htmlspecialchars($log_file); ?><br>
            <strong>文件大小:</strong> <?php echo file_exists($log_file) ? number_format(filesize($log_file)) . ' 字节' : '文件不存在'; ?><br>
            <strong>最后修改:</strong> <?php echo file_exists($log_file) ? date('Y-m-d H:i:s', filemtime($log_file)) : 'N/A'; ?>
        </div>
        
        <div class="controls">
            <a href="?refresh=1" class="btn">🔄 刷新日志</a>
            <form method="post" style="display: inline;">
                <input type="hidden" name="clear_log" value="1">
                <button type="submit" class="btn btn-danger" onclick="return confirm('确定要清空日志吗？')">🗑️ 清空日志</button>
            </form>
            <a href="javascript:location.reload()" class="btn">🔄 自动刷新</a>
        </div>
        
        <div class="log-content"><?php echo htmlspecialchars($log_content); ?></div>
    </div>
    
    <script>
        // 自动滚动到底部
        window.onload = function() {
            var logContent = document.querySelector('.log-content');
            logContent.scrollTop = logContent.scrollHeight;
        };
        
        // 每5秒自动刷新
        if (window.location.search.includes('refresh=1')) {
            setTimeout(function() {
                location.reload();
            }, 5000);
        }
    </script>
</body>
</html> 