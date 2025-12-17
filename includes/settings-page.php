<?php
// 注册 DeepSeek 设置菜单
add_action('admin_menu', function() {
    add_options_page(
        'DeepSeek 设置',
        'DeepSeek 设置',
        'manage_options',
        'deepseek-settings',
        'deepseek_render_settings_page'
    );
});

// 注册设置项
add_action('admin_init', function() {
    register_setting('deepseek_settings_group', 'deepseek_api_url');
    register_setting('deepseek_settings_group', 'deepseek_api_key');
    register_setting('deepseek_settings_group', 'deepseek_model');
    register_setting('deepseek_settings_group', 'deepseek_prefix');
    register_setting('deepseek_settings_group', 'deepseek_suffix');
    register_setting('deepseek_settings_group', 'deepseek_context');
    register_setting('deepseek_settings_group', 'deepseek_background');
    register_setting('deepseek_settings_group', 'deepseek_person');
    register_setting('deepseek_settings_group', 'deepseek_responder_name');
});

// 渲染设置页面
function deepseek_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>DeepSeek ChatGPT 设置</h1>
        
        <?php
        // 检查配置状态
        $api_url = get_option('deepseek_api_url', '');
        $api_key = get_option('deepseek_api_key', '');
        
        if (empty($api_url) || empty($api_key)) {
            echo '<div class="notice notice-warning"><p><strong>⚠️ 配置提醒：</strong>请先配置 API URL 和 API 密钥才能正常使用插件功能。</p></div>';
        } else {
            echo '<div class="notice notice-success"><p><strong>✅ 配置完成：</strong>API 配置已设置，插件可以正常使用。</p></div>';
        }
        ?>
        
        <form method="post" action="options.php">
            <?php settings_fields('deepseek_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">DeepSeek API URL</th>
                    <td>
                        <input type="url" name="deepseek_api_url" value="<?php echo esc_attr(get_option('deepseek_api_url', 'https://api.deepseek.com')); ?>" style="width: 400px;" placeholder="https://api.deepseek.com">
                        <p class="description">DeepSeek API 的完整访问地址</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">DeepSeek API 密钥</th>
                    <td>
                        <input type="password" name="deepseek_api_key" value="<?php echo esc_attr(get_option('deepseek_api_key', '')); ?>" style="width: 400px;" placeholder="sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <p class="description">请输入您的 DeepSeek API 密钥。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">模型选择</th>
                    <td>
                        <select name="deepseek_model" style="width: 200px;">
                            <option value="deepseek-chat" <?php selected(get_option('deepseek_model', 'deepseek-chat'), 'deepseek-chat'); ?>>deepseek-chat</option>
                            <option value="deepseek-reasoner" <?php selected(get_option('deepseek_model', 'deepseek-chat'), 'deepseek-reasoner'); ?>>deepseek-reasoner</option>
                        </select>
                        <p class="description">选择要使用的 DeepSeek 模型。deepseek-chat 适合一般对话，deepseek-reasoner 适合复杂推理任务。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">回答人称</th>
                    <td>
                        <select name="deepseek_person" style="width: 200px;">
                            <option value="first" <?php selected(get_option('deepseek_person', 'third'), 'first'); ?>>第一人称 (我/我们)</option>
                            <option value="second" <?php selected(get_option('deepseek_person', 'third'), 'second'); ?>>第二人称 (你/您)</option>
                            <option value="third" <?php selected(get_option('deepseek_person', 'third'), 'third'); ?>>第三人称 (它/他们)</option>
                        </select>
                        <p class="description">选择 AI 回答时使用的人称口吻。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">回答者名称</th>
                    <td>
                        <input type="text" name="deepseek_responder_name" value="<?php echo esc_attr(get_option('deepseek_responder_name', 'DEEPSEEK')); ?>" style="width: 200px;" placeholder="如：AI助手、智能客服等">
                        <p class="description">设置QA页面中AI回答者的显示名称，默认为“DEEPSEEK”</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">自定义前缀</th>
                    <td><input type="text" name="deepseek_prefix" value="<?php echo esc_attr(get_option('deepseek_prefix', '')); ?>" style="width: 400px;" placeholder="在用户问题前添加的文本"></td>
                </tr>
                <tr>
                    <th scope="row">自定义后缀</th>
                    <td><input type="text" name="deepseek_suffix" value="<?php echo esc_attr(get_option('deepseek_suffix', '')); ?>" style="width: 400px;" placeholder="在用户问题后添加的文本"></td>
                </tr>
                <tr>
                    <th scope="row">上下文/人设说明</th>
                    <td><textarea name="deepseek_context" rows="3" style="width: 400px;" placeholder="定义AI的角色和行为模式"><?php echo esc_textarea(get_option('deepseek_context', '')); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row">背景知识</th>
                    <td><textarea name="deepseek_background" rows="3" style="width: 400px;" placeholder="注入特定领域的知识或信息"><?php echo esc_textarea(get_option('deepseek_background', '')); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
} 