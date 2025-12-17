<?php
// test-stream.php - 测试 deepseek-stream.php 的问题
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>测试 DeepSeek Stream</title>
    <meta charset="utf-8">
</head>
<body>
    <h1>DeepSeek Stream 测试</h1>
    
    <div id="status">准备测试...</div>
    <div id="result"></div>
    
    <script>
        async function testStream() {
            const statusDiv = document.getElementById('status');
            const resultDiv = document.getElementById('result');
            
            statusDiv.textContent = '正在测试...';
            
            try {
                const messages = [
                    { role: 'user', content: '你好，请简单介绍一下自己' }
                ];
                
                const formData = new FormData();
                formData.append('messages', JSON.stringify(messages));
                
                const response = await fetch('deepseek-stream.php', {
                    method: 'POST',
                    body: formData
                });
                
                statusDiv.textContent = `状态码: ${response.status}`;
                
                if (!response.ok) {
                    const errorText = await response.text();
                    resultDiv.innerHTML = `<p style="color: red;">错误: ${errorText}</p>`;
                    return;
                }
                
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                
                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;
                    
                    const chunk = decoder.decode(value);
                    const lines = chunk.split('\n');
                    
                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = line.substring(6);
                            if (data !== '[DONE]') {
                                try {
                                    const parsed = JSON.parse(data);
                                    if (parsed.choices && parsed.choices[0].delta.content) {
                                        resultDiv.textContent += parsed.choices[0].delta.content;
                                    }
                                } catch (e) {
                                    // 忽略解析错误
                                }
                            }
                        }
                    }
                }
                
                statusDiv.textContent = '测试完成';
                
            } catch (error) {
                statusDiv.textContent = '测试失败';
                resultDiv.innerHTML = `<p style="color: red;">错误: ${error.message}</p>`;
            }
        }
        
        // 页面加载后自动测试
        window.onload = testStream;
    </script>
</body>
</html> 