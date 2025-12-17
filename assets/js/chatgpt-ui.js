jQuery(document).ready(function($){
    console.log('DeepSeek ChatGPT UI loaded - Version 1.0.1');

    // 初始化：添加初始状态类
    $('#deepseek-chatgpt-container').addClass('initial-state');

    // 流式输出函数，只显示最终答案
    function streamDeepSeek(messages, onContent, onEnd) {
        var formData = new FormData();
        formData.append('messages', JSON.stringify(messages));
        var streamUrl = window.location.origin + '/wp-content/plugins/deepseek-chatgpt/deepseek-stream.php';
        fetch(streamUrl, {
            method: 'POST',
            body: formData
        }).then(function(response){
            if(!response.ok) {
                // 如果响应不成功，显示错误信息
                onContent && onContent('抱歉，请求失败，请稍后重试。');
                onEnd && onEnd();
                return;
            }
            if(!response.body) { onEnd && onEnd(); return; }
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            function read() {
                reader.read().then(function(result){
                    if(result.done) { onEnd && onEnd(); return; }
                    buffer += decoder.decode(result.value, {stream:true});
                    var parts = buffer.split('\n\n');
                    buffer = parts.pop();
                    for(var i=0;i<parts.length;i++){
                        var part = parts[i];
                        if(part.startsWith('data: ')){
                            var json = part.replace('data: ','').trim();
                            if(json && json !== '[DONE]'){
                                try{
                                    var obj = JSON.parse(json);
                                    var content = obj.choices?.[0]?.delta?.content;
                                    if(content) onContent(content);
                                }catch(e){}
                            }
                        }
                    }
                    read();
                }).catch(function(error){
                    console.error('Stream read error:', error);
                    onContent && onContent('抱歉，读取响应时出现错误。');
                    onEnd && onEnd();
                });
            }
            read();
        }).catch(function(error){
            console.error('Stream fetch error:', error);
            onContent && onContent('抱歉，网络连接失败，请检查网络后重试。');
            onEnd && onEnd();
        });
    }



    // 自动调整 textarea 高度
    function autoResizeTextarea() {
        var textarea = $('#deepseek-chatgpt-input');
        textarea.css('height', 'auto');
        textarea.css('height', Math.min(textarea[0].scrollHeight, 120) + 'px');
    }
    
    $('#deepseek-chatgpt-input').on('input', autoResizeTextarea);
    
    $('#deepseek-chatgpt-form').on('submit', function(e){
        e.preventDefault();
        console.log('Form submitted - using stream method (answer only)');
        var input = $('#deepseek-chatgpt-input');
        var msg = input.val().trim();
        if(!msg) return;
        
        // 如果是第一次提交，切换到聊天状态
        if ($('#deepseek-chatgpt-container').hasClass('initial-state')) {
            $('#deepseek-chatgpt-container').removeClass('initial-state');
            $('#deepseek-chatgpt-messages').show();
        }
        
        var messages = $('#deepseek-chatgpt-messages');
        var history = messages.data('history') || [];
        history.push({role:'user',content:msg});
        messages.data('history', history);
        // 添加用户消息
        messages.append('<div class="user-message"><b>你：</b> ' + $('<div>').text(msg).html() + '</div>');
        input.val('');
        input.css('height', 'auto'); // 重置 textarea 高度
        
        // 添加AI答案容器，先显示"正在思考中"
        var aiDiv = $('<div class="ai-message"><b>' + (deepseek_ajax.responder_name || 'DEEPSEEK') + '：</b> <span class="answer"><div class="thinking-message"><div class="loading-spinner"></div>正在思考中...</div></span></div>');
        messages.append(aiDiv);
        messages.scrollTop(messages[0].scrollHeight);
        
        // 禁用输入框和按钮
        input.prop('disabled', true);
        $('button[type="submit"]').prop('disabled', true);
        
        // 流式输出（只显示最终答案）
        var answerText = '';
        var hasStartedStreaming = false;
        
        streamDeepSeek(history, function(content){
            // 第一次收到内容时，清除"正在思考中"
            if (!hasStartedStreaming) {
                aiDiv.find('.answer').empty();
                hasStartedStreaming = true;
            }
            
            answerText += content;
            // 使用 html() 而不是 text() 来支持换行，并添加基本格式
            var formattedContent = answerText
                .replace(/\n/g, '<br>')  // 换行符转换为 <br>
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')  // **粗体**
                .replace(/\*(.*?)\*/g, '<em>$1</em>')  // *斜体*
                .replace(/`(.*?)`/g, '<code>$1</code>');  // `代码`
            aiDiv.find('.answer').html(formattedContent);
            messages.scrollTop(messages[0].scrollHeight);
        }, function(){
            // 重新启用输入框和按钮
            input.prop('disabled', false);
            $('button[type="submit"]').prop('disabled', false);
            input.focus();
        });
    });
}); 