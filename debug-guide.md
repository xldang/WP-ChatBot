# DeepSeek ChatGPT 插件调试指南

## 🔍 如何获取错误信息

### 1. 浏览器开发者工具
1. 在浏览器中按 **F12** 或右键选择"检查"
2. 切换到 **Console** 标签页
3. 输入问题并点击发送
4. 查看是否有红色错误信息

### 2. WordPress 错误日志
- **位置**: `/wp-content/debug.log`
- **启用方法**: 在 `wp-config.php` 中添加：
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 3. 服务器错误日志
- **Apache**: `/var/log/apache2/error.log`
- **Nginx**: `/var/log/nginx/error.log`
- **PHP**: `/var/log/php_errors.log`

## 🐛 常见问题诊断

### 问题1: 点击发送后无反应
**可能原因**:
- JavaScript错误
- AJAX请求失败
- WordPress AJAX URL未定义

**解决方法**:
1. 检查浏览器控制台错误
2. 确认 `ajaxurl` 变量已定义
3. 检查网络请求状态

### 问题2: API请求失败
**可能原因**:
- API密钥无效
- 网络连接问题
- 服务器配置问题

**解决方法**:
1. 验证API密钥是否正确
2. 检查服务器是否能访问外网
3. 确认cURL扩展已启用

### 问题3: 权限问题
**可能原因**:
- WordPress权限设置
- 文件权限问题

**解决方法**:
1. 检查WordPress用户权限
2. 确认插件文件权限为644
3. 确认目录权限为755

## 🔧 快速诊断步骤

### 步骤1: 检查JavaScript
```javascript
// 在浏览器控制台执行
console.log('AJAX URL:', ajaxurl);
console.log('jQuery loaded:', typeof jQuery);
```

### 步骤2: 检查PHP错误
```php
// 在插件文件中临时添加
error_log('DeepSeek API Key: ' . get_option('deepseek_api_key'));
error_log('User input: ' . $_POST['prompt']);
```

### 步骤3: 测试API连接
```php
// 在插件文件中临时添加
$test_response = wp_remote_get('https://api.deepseek.com');
error_log('API test response: ' . print_r($test_response, true));
```

## 📋 检查清单

- [ ] WordPress版本 >= 5.0
- [ ] PHP版本 >= 7.4
- [ ] cURL扩展已启用
- [ ] JSON扩展已启用
- [ ] 插件已激活
- [ ] API密钥已配置
- [ ] 短代码正确插入
- [ ] 浏览器支持JavaScript
- [ ] 网络连接正常

## 🚨 紧急修复

如果问题仍然存在，可以尝试以下紧急修复：

### 1. 重新激活插件
1. 停用插件
2. 删除插件
3. 重新上传并激活

### 2. 检查文件完整性
确保所有文件都已正确上传：
- `deepseek-chatgpt.php`
- `includes/chatgpt-qa-page.php`
- `includes/settings-page.php`
- `assets/js/chatgpt-ui.js`
- `deepseek-stream.php`

### 3. 临时调试模式
在 `wp-config.php` 中启用调试：
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```

## 📞 获取帮助

如果以上方法都无法解决问题，请提供：
1. 错误日志内容
2. 浏览器控制台错误信息
3. WordPress版本
4. PHP版本
5. 服务器环境信息 