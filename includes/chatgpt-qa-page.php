<?php
// 注册 DeepSeek Q&A 页面短代码
function deepseek_qa_shortcode() {
    ob_start();
    $responder_name = esc_html(get_option('deepseek_responder_name', 'DEEPSEEK'));
    ?>
    <style>
        #deepseek-chatgpt-container {
            width: 60%;
            max-width: 1000px;
            margin: 0 auto;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 85vh;
            display: flex;
            flex-direction: column;
            background: transparent;
            border: none;
            position: relative;
            border-radius: 0;
            margin-top: 7.5vh;
            margin-bottom: 7.5vh;
        }
        
        /* 初始状态：输入框居中显示 */
        #deepseek-chatgpt-container.initial-state {
            justify-content: center;
            align-items: center;
        }
        
        #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-messages {
            display: none;
        }
        
        #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-form {
            position: static;
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 40px 30px;
            background: transparent;
            border: none;
            box-sizing: border-box;
        }
        
        #deepseek-chatgpt-container.initial-state .input-container {
            background: #ffffff;
            border: 2px solid #e5e5e5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        #deepseek-chatgpt-container.initial-state .input-container:focus-within {
            border-color: #007cba;
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.2);
        }
        
        #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-input {
            font-size: 18px;
            padding: 20px 50px 20px 25px;
        }
        
        #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-form button {
            width: 40px;
            height: 40px;
            right: 10px;
            bottom: 10px;
            background: #007cba;
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        }
        
        #deepseek-chatgpt-container.initial-state .paper-plane-icon {
            width: 18px;
            height: 18px;
        }
        #deepseek-chatgpt-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            margin-bottom: 0;
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
        }
        .user-message {
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(245, 245, 245, 0.4);
            color: #2c3e50;
            border-radius: 0;
            max-width: 100%;
            margin-left: 0;
            border: none;
            backdrop-filter: blur(5px);
        }
        .ai-message {
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(245, 245, 245, 0.3);
            border: none;
            border-radius: 0;
            max-width: 100%;
            line-height: 1.6;
            backdrop-filter: blur(5px);
        }
        .ai-message .answer {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .ai-message code {
            background: rgba(245, 245, 245, 0.6);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.9em;
            border: 1px solid rgba(229, 229, 229, 0.3);
        }
        .ai-message strong {
            font-weight: 600;
            color: #2c3e50;
        }
        .ai-message em {
            font-style: italic;
            color: #34495e;
        }
        
        /* 加载动画样式 */
        .thinking-message {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #666;
            font-style: italic;
        }
        
        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 124, 186, 0.2);
            border-top: 2px solid #007cba;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* 错误消息样式 */
        .error-content {
            color: #d63638;
            font-style: italic;
        }
        #deepseek-chatgpt-form {
            display: flex;
            align-items: flex-end;
            gap: 0;
            padding: 20px;
            background: rgba(245, 245, 245, 0.3);
            border-top: 1px solid rgba(229, 229, 229, 0.3);
            position: sticky;
            bottom: 0;
            width: 100%;
            box-sizing: border-box;
            backdrop-filter: blur(10px);
        }
        
        .input-container {
            position: relative;
            flex: 1;
            display: flex;
            align-items: flex-end;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(229, 229, 229, 0.6);
            border-radius: 12px;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }
        
        .input-container:focus-within {
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
            background: rgba(255, 255, 255, 0.9);
        }
        
        #deepseek-chatgpt-input {
            flex: 1;
            padding: 15px 50px 15px 20px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: transparent;
            max-height: 120px;
            resize: none;
            outline: none;
        }
        
        #deepseek-chatgpt-form button {
            position: absolute;
            right: 8px;
            bottom: 8px;
            width: 36px;
            height: 36px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }
        
        #deepseek-chatgpt-form button:hover {
            background: #005a87;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        }
        
        #deepseek-chatgpt-form button:disabled {
            background: rgba(204, 204, 204, 0.8);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* 纸飞机图标 */
        .paper-plane-icon {
            width: 16px;
            height: 16px;
            fill: currentColor;
            transform: rotate(-45deg);
        }
        .error-message {
            color: #d63638;
            background: #fef7f1;
            border: 1px solid #d63638;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        /* 响应式设计 */
        @media (max-width: 1200px) {
            #deepseek-chatgpt-container {
                width: 75%;
                height: 90vh;
                margin-top: 5vh;
                margin-bottom: 5vh;
            }
            #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-form {
                width: 100%;
                padding: 35px 25px;
            }
            #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-input {
                font-size: 16px;
                padding: 15px 45px 15px 20px;
            }
            #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-form button {
                width: 36px;
                height: 36px;
                right: 8px;
                bottom: 8px;
            }
            #deepseek-chatgpt-container.initial-state .paper-plane-icon {
                width: 16px;
                height: 16px;
            }
        }
        
        @media (max-width: 768px) {
            #deepseek-chatgpt-container {
                width: 95%;
                height: 100vh;
                margin-top: 0;
                margin-bottom: 0;
            }
            #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-form {
                width: 100%;
                padding: 30px 20px;
            }
            #deepseek-chatgpt-container.initial-state .input-container {
                background: #ffffff;
                border: 2px solid #e5e5e5;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
            #deepseek-chatgpt-container.initial-state .input-container:focus-within {
                border-color: #007cba;
                box-shadow: 0 4px 12px rgba(0, 124, 186, 0.2);
            }
            #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-input {
                font-size: 16px;
                padding: 15px 45px 15px 20px;
            }
            #deepseek-chatgpt-container.initial-state #deepseek-chatgpt-form button {
                width: 32px;
                height: 32px;
                right: 6px;
                bottom: 6px;
                background: #007cba;
                box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
            }
            #deepseek-chatgpt-container.initial-state .paper-plane-icon {
                width: 14px;
                height: 14px;
            }
            #deepseek-chatgpt-messages {
                padding: 15px;
            }
            .user-message, .ai-message {
                padding: 15px;
                margin-bottom: 15px;
                background: rgba(245, 245, 245, 0.4);
            }
            #deepseek-chatgpt-form {
                padding: 15px;
            }
            .input-container {
                border-radius: 10px;
            }
            #deepseek-chatgpt-input {
                padding: 12px 45px 12px 15px;
                font-size: 14px;
            }
            #deepseek-chatgpt-form button {
                width: 32px;
                height: 32px;
                right: 6px;
                bottom: 6px;
            }
            .paper-plane-icon {
                width: 14px;
                height: 14px;
            }
        }
        
        @media (max-width: 480px) {
            #deepseek-chatgpt-container {
                width: 100%;
            }
        }
        
        /* 滚动条样式 */
        #deepseek-chatgpt-messages::-webkit-scrollbar {
            width: 8px;
        }
        #deepseek-chatgpt-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        #deepseek-chatgpt-messages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        #deepseek-chatgpt-messages::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* 确保页面背景 */
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        

    </style>
    
    <div id="deepseek-chatgpt-container" data-responder-name="<?php echo $responder_name; ?>">
        <div id="deepseek-chatgpt-messages"></div>
        <form id="deepseek-chatgpt-form">
            <div class="input-container">
                <textarea id="deepseek-chatgpt-input" placeholder="请输入你的问题..." required rows="1"></textarea>
                <button type="submit">
                    <svg class="paper-plane-icon" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
    <?php
    // 加载前端 JS
    wp_enqueue_script('deepseek-chatgpt-ui', plugin_dir_url(__DIR__) . 'assets/js/chatgpt-ui.js', array('jquery'), '1.0.1', true);
    
    // 传递AJAX URL和回答者名称给JavaScript
    wp_localize_script('deepseek-chatgpt-ui', 'deepseek_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('deepseek_nonce'),
        'responder_name' => $responder_name
    ));
    
    return ob_get_clean();
}
add_shortcode('deepseek_qa', 'deepseek_qa_shortcode');

// 处理 AJAX 请求，包装用户输入并模拟 DeepSeek API 调用
add_action('wp_ajax_deepseek_send_query', 'deepseek_handle_query');
add_action('wp_ajax_nopriv_deepseek_send_query', 'deepseek_handle_query');

// 加载统计模块
require_once plugin_dir_path(__FILE__) . 'statistics.php';

// 添加测试AJAX端点
add_action('wp_ajax_deepseek_test', 'deepseek_test_connection');
add_action('wp_ajax_nopriv_deepseek_test', 'deepseek_test_connection');

// 日志记录函数
function deepseek_log($message) {
    $log_file = plugin_dir_path(__DIR__) . 'debug.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function deepseek_test_connection() {
    deepseek_log('DeepSeek: Test connection requested');
    
    $api_url = get_option('deepseek_api_url', 'https://api.deepseek.com');
    $api_key = get_option('deepseek_api_key', '');
    $model = get_option('deepseek_model', 'deepseek-chat');
    
    $test_data = array(
        'message' => 'DeepSeek插件连接正常',
        'timestamp' => current_time('mysql'),
        'api_url_set' => !empty($api_url),
        'api_key_set' => !empty($api_key),
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION
    );
    
    // 如果API密钥已设置，尝试测试API连接（用POST方式）
    if (!empty($api_key)) {
        $body = json_encode([
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => 'ping']],
            'stream' => false
        ]);
        $test_response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => $body,
            'timeout' => 10
        ));
        
        if (is_wp_error($test_response)) {
            $test_data['api_test'] = '连接失败: ' . $test_response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($test_response);
            $test_data['api_test'] = 'HTTP状态码: ' . $response_code;
            $test_data['api_accessible'] = ($response_code === 200 || $response_code === 401);
        }
    } else {
        $test_data['api_test'] = 'API密钥未设置';
    }
    
    wp_send_json_success($test_data);
}

function deepseek_handle_query() {
    // 添加调试日志
    deepseek_log('DeepSeek: AJAX request received');

    $user_input = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';
    deepseek_log('DeepSeek: User input: ' . $user_input);

    // 统计：记录对话开始
    wp_chatbot_log_stats('conversation_start', array(
        'user_input' => $user_input
    ));

    // 统计：记录用户消息
    wp_chatbot_log_stats('user_message', array(
        'content' => $user_input
    ));
    
    // 获取设置项
    $api_url = get_option('deepseek_api_url', 'https://api.deepseek.com');
    $api_key = get_option('deepseek_api_key', '');
    $model = get_option('deepseek_model', 'deepseek-chat');
    $prefix = get_option('deepseek_prefix', '');
    $suffix = get_option('deepseek_suffix', '');
    $context = get_option('deepseek_context', '');
    $background = get_option('deepseek_background', '');
    $person = get_option('deepseek_person', 'third');
    
    // 检查API密钥是否已设置
    if (empty($api_key)) {
        deepseek_log('DeepSeek: Error - API key not set');
        wp_send_json_error(['answer' => '请先在设置中配置 DeepSeek API 密钥。']);
        return;
    }
    
    deepseek_log('DeepSeek: API Key: ' . substr($api_key, 0, 10) . '...');
    deepseek_log('DeepSeek: Person setting: ' . $person);
    
    // 根据人称设置添加相应的指令
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
    
    // 包装最终 prompt
    $final_prompt = $prefix . "\n" . $context . "\n" . $background . "\n" . $person_instruction . "\n" . $user_input . "\n" . $suffix;

    // DeepSeek API 参数
    $temperature = 0.7;
    $messages = array();
    if (trim($context . $background . $prefix . $suffix) !== '') {
        $messages[] = array('role' => 'system', 'content' => trim($prefix . "\n" . $context . "\n" . $background . "\n" . $suffix));
    }
    $messages[] = array('role' => 'user', 'content' => $user_input);

    $body = json_encode(array(
        'model' => $model,
        'messages' => $messages,
        'temperature' => $temperature
    ));
    deepseek_log('DeepSeek: Sending request to API');
    deepseek_log('DeepSeek: Request body: ' . $body);
    
    // 记录API调用开始时间
    $api_start_time = microtime(true);

    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body' => $body,
        'timeout' => 30,
    ));

    // 计算响应时间
    $api_end_time = microtime(true);
    $response_time = round(($api_end_time - $api_start_time) * 1000, 2); // 毫秒

    deepseek_log('DeepSeek: Response received: ' . print_r($response, true));

    // 统计：记录API调用
    wp_chatbot_log_stats('api_call', array(
        'success' => false, // 先假设失败，成功时更新
        'response_time' => $response_time,
        'model' => $model
    ));

    if (is_wp_error($response)) {
        $error_message = 'DeepSeek API 请求失败：' . $response->get_error_message();
        if (strpos($response->get_error_message(), 'timeout') !== false) {
            $error_message = '请求超时，请检查网络连接后重试。';
        } elseif (strpos($response->get_error_message(), 'curl') !== false) {
            $error_message = '网络连接错误，请稍后重试。';
        }
        deepseek_log('DeepSeek: Error - ' . $error_message);

        // 统计：记录错误
        wp_chatbot_log_stats('error', array(
            'type' => 'network_error',
            'code' => 'wp_error',
            'message' => $response->get_error_message()
        ));

        wp_send_json_error(['answer' => $error_message]);
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = 'API 请求失败，状态码：' . $response_code;
        if ($response_code === 401) {
            $error_message = 'API 密钥无效，请在设置中检查您的 DeepSeek API 密钥。';
        } elseif ($response_code === 429) {
            $error_message = '请求过于频繁，请稍后重试。';
        } elseif ($response_code >= 500) {
            $error_message = '服务器错误，请稍后重试。';
        }
        deepseek_log('DeepSeek: Error - HTTP ' . $response_code . ' - ' . $error_message);

        // 统计：记录HTTP错误
        wp_chatbot_log_stats('error', array(
            'type' => 'http_error',
            'code' => $response_code,
            'message' => $error_message
        ));

        wp_send_json_error(['answer' => $error_message]);
    }
    
    $res_body = wp_remote_retrieve_body($response);
    $data = json_decode($res_body, true);
    
    if (isset($data['choices'][0]['message']['content'])) {
        $answer = $data['choices'][0]['message']['content'];

        // 统计：更新API调用为成功，并记录AI响应
        wp_chatbot_log_stats('api_call', array(
            'success' => true,
            'response_time' => $response_time,
            'model' => $model
        ));

        wp_chatbot_log_stats('ai_response', array(
            'content' => $answer,
            'response_time' => $response_time
        ));

        wp_send_json_success(['answer' => nl2br(esc_html($answer))]);
    } elseif (isset($data['error']['message'])) {
        // 统计：记录API错误
        wp_chatbot_log_stats('error', array(
            'type' => 'api_error',
            'code' => 'api_response_error',
            'message' => $data['error']['message']
        ));

        wp_send_json_error(['answer' => 'DeepSeek API 错误：' . esc_html($data['error']['message'])]);
    } else {
        // 统计：记录无效响应格式错误
        wp_chatbot_log_stats('error', array(
            'type' => 'invalid_response',
            'code' => 'invalid_format',
            'message' => 'Invalid API response format'
        ));

        wp_send_json_error(['answer' => 'DeepSeek API 返回了无效的响应格式。']);
    }
}
