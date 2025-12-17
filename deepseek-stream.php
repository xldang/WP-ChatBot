<?php
// deepseek-stream.php
// 用于 DeepSeek API 流式输出代理

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 日志记录函数
function deepseek_stream_log($message) {
    $log_file = dirname(__FILE__) . '/debug.log';
    // 设置时区为北京时间
    date_default_timezone_set('Asia/Shanghai');
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

try {
    // 加载WordPress
    $wp_load_path = '../../../wp-load.php';
    if (!file_exists($wp_load_path)) {
        deepseek_stream_log('Error: wp-load.php not found at ' . $wp_load_path);
        http_response_code(500);
        echo "event: error\ndata: WordPress 加载失败\n\n";
        exit;
    }
    
    require_once($wp_load_path);
    deepseek_stream_log('WordPress loaded successfully');
    
    // 设置响应头
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no'); // 关闭 Nginx 缓冲

// 获取配置的API设置
$api_url = get_option('deepseek_api_url', 'https://api.deepseek.com');
$api_key = get_option('deepseek_api_key', '');
$model = get_option('deepseek_model', 'deepseek-chat');

// 检查API密钥是否已设置
if (empty($api_key)) {
    deepseek_stream_log('DeepSeek Stream: Error - API key not set');
    echo "event: error\ndata: 请先在设置中配置 DeepSeek API 密钥\n\n";
    @ob_flush(); flush();
    exit;
}

deepseek_stream_log('DeepSeek Stream: Starting request');

// 获取设置项
$prefix = get_option('deepseek_prefix', '');
$suffix = get_option('deepseek_suffix', '');
$context = get_option('deepseek_context', '');
$background = get_option('deepseek_background', '');
$person = get_option('deepseek_person', 'third');

// 生成人称指令
$person_instruction = '';
switch($person) {
    case 'first':
        $person_instruction = '请使用第一人称（我/我们）来回答。';
        break;
    case 'second':
        $person_instruction = '请使用第二人称（你/您）来回答。';
        break;
    case 'third':
    default:
        $person_instruction = '请使用第三人称（它/他们）来回答。';
        break;
}

// 兼容 multipart/form-data 和 application/json
$messages = null;
if (isset($_POST['messages'])) {
    deepseek_stream_log('POST messages: ' . $_POST['messages']);
    $raw_messages = trim($_POST['messages']);
    $raw_messages = preg_replace('/^\xEF\xBB\xBF/', '', $raw_messages); // 去除BOM
    deepseek_stream_log('Cleaned messages: ' . $raw_messages);
    $raw_messages = str_replace('\\"', '"', $raw_messages);
    $messages = json_decode($raw_messages, true);
    if ($messages === null) {
        deepseek_stream_log('json_last_error: ' . json_last_error() . ' - ' . json_last_error_msg());
    }
} else {
    deepseek_stream_log('=== FILE EXECUTED ===');
    deepseek_stream_log('CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? ''));
    $raw = file_get_contents('php://input');
    deepseek_stream_log('RAW: ' . $raw);
    if ($raw) {
        if (preg_match('/name="messages"\s*\r\n\r\n(.*?)\r\n--/s', $raw, $m)) {
            $messages = json_decode($m[1], true);
        } else {
            $json = json_decode($raw, true);
            if (isset($json['messages'])) {
                $messages = $json['messages'];
            }
        }
    }
}

if (!$messages) {
    deepseek_stream_log('DeepSeek Stream: Error - messages empty');
    echo "event: error\ndata: messages 不能为空\n\n";
    @ob_flush(); flush();
    exit;
}

deepseek_stream_log('Decoded messages: ' . print_r($messages, true));

// 包装 system prompt
$system_content = trim($prefix . "\n" . $context . "\n" . $background . "\n" . $person_instruction . "\n" . $suffix);
if ($system_content !== '') {
    // 插入到 messages 最前面
    array_unshift($messages, array('role' => 'system', 'content' => $system_content));
    deepseek_stream_log('Added system prompt: ' . $system_content);
}

$body = json_encode([
    'model' => $model,
    'messages' => $messages,
    'stream' => true
]);

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) {
    $lines = explode("\n", $chunk);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line === 'data: [DONE]') continue;
        if (strpos($line, 'data: ') === 0) {
            echo $line . "\n\n";
            @ob_flush(); flush();
        }
    }
    return strlen($chunk);
});
curl_exec($ch);
curl_close($ch);

} catch (Exception $e) {
    deepseek_stream_log('Exception: ' . $e->getMessage());
    http_response_code(500);
    echo "event: error\ndata: 服务器错误: " . $e->getMessage() . "\n\n";
    @ob_flush(); flush();
} 