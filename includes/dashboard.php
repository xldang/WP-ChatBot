<?php
// WP-ChatBot Dashboard Module

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 添加仪表盘小部件
function wp_chatbot_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'wp_chatbot_stats_widget',
        'WP-ChatBot 统计',
        'wp_chatbot_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'wp_chatbot_add_dashboard_widgets');

// 仪表盘小部件内容
function wp_chatbot_dashboard_widget_content() {
    $stats = wp_chatbot_get_summary_stats(30); // 30天统计

    ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-bottom: 15px;">
        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: #007cba;"><?php echo number_format($stats['total_conversations']); ?></div>
            <div style="font-size: 12px; color: #666;">总对话数</div>
        </div>
        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: #28a745;"><?php echo number_format($stats['today_conversations']); ?></div>
            <div style="font-size: 12px; color: #666;">今日对话</div>
        </div>
        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: #ffc107;"><?php echo number_format($stats['api_calls']); ?></div>
            <div style="font-size: 12px; color: #666;">API调用</div>
        </div>
        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: <?php echo $stats['api_errors'] > 0 ? '#dc3545' : '#6c757d'; ?>;"><?php echo round(($stats['api_calls'] - $stats['api_errors']) / max($stats['api_calls'], 1) * 100, 1); ?>%</div>
            <div style="font-size: 12px; color: #666;">成功率</div>
        </div>
    </div>

    <div style="border-top: 1px solid #eee; padding-top: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="font-size: 14px; color: #666;">平均响应时间:</span>
                <span style="font-weight: bold; color: #007cba;"><?php echo $stats['avg_response_time']; ?>ms</span>
            </div>
            <a href="<?php echo admin_url('admin.php?page=wp_chatbot_statistics'); ?>" class="button button-small">查看详细统计</a>
        </div>
    </div>
    <?php
}

// 添加统计页面到设置菜单
function wp_chatbot_add_statistics_menu() {
    add_submenu_page(
        'options-general.php',
        'WP-ChatBot 统计',
        'WP-ChatBot 统计',
        'manage_options',
        'wp_chatbot_statistics',
        'wp_chatbot_statistics_page'
    );
}
add_action('admin_menu', 'wp_chatbot_add_statistics_menu');

// 统计页面内容
function wp_chatbot_statistics_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('您没有权限访问此页面。'));
    }

    // 获取时间范围参数
    $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
    $stats = wp_chatbot_get_summary_stats($days);
    $hourly_data = wp_chatbot_get_hourly_distribution($days);
    $popular_keywords = wp_chatbot_get_popular_keywords($days, 10);

    ?>
    <div class="wrap">
        <h1>WP-ChatBot 统计</h1>

        <!-- 时间范围选择器 -->
        <div style="margin-bottom: 20px;">
            <form method="get" style="display: inline;">
                <input type="hidden" name="page" value="wp_chatbot_statistics">
                <select name="days" onchange="this.form.submit()">
                    <option value="7" <?php selected($days, 7); ?>>最近7天</option>
                    <option value="30" <?php selected($days, 30); ?>>最近30天</option>
                    <option value="90" <?php selected($days, 90); ?>>最近90天</option>
                </select>
            </form>
        </div>

        <!-- 关键指标卡片 -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #007cba;">对话统计</h3>
                <div style="font-size: 32px; font-weight: bold; color: #007cba; margin: 10px 0;"><?php echo number_format($stats['total_conversations']); ?></div>
                <div style="color: #666;">总对话数</div>
                <div style="margin-top: 10px;">
                    <span style="color: #28a745;">今日: <?php echo number_format($stats['today_conversations']); ?></span> |
                    <span style="color: #ffc107;">本周: <?php echo number_format($stats['week_conversations']); ?></span> |
                    <span style="color: #17a2b8;">本月: <?php echo number_format($stats['month_conversations']); ?></span>
                </div>
            </div>

            <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #28a745;">API性能</h3>
                <div style="font-size: 32px; font-weight: bold; color: #28a745; margin: 10px 0;"><?php echo round(($stats['api_calls'] - $stats['api_errors']) / max($stats['api_calls'], 1) * 100, 1); ?>%</div>
                <div style="color: #666;">成功率</div>
                <div style="margin-top: 10px;">
                    <span>总调用: <?php echo number_format($stats['api_calls']); ?></span><br>
                    <span>平均响应: <?php echo $stats['avg_response_time']; ?>ms</span>
                </div>
            </div>

            <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #ffc107;">消息统计</h3>
                <div style="font-size: 32px; font-weight: bold; color: #ffc107; margin: 10px 0;"><?php echo number_format($stats['total_messages']); ?></div>
                <div style="color: #666;">总消息数</div>
                <div style="margin-top: 10px;">
                    <span>独立用户: <?php echo number_format($stats['total_users']); ?></span>
                </div>
            </div>

            <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: <?php echo $stats['api_errors'] > 0 ? '#dc3545' : '#6c757d'; ?>;">错误统计</h3>
                <div style="font-size: 32px; font-weight: bold; color: <?php echo $stats['api_errors'] > 0 ? '#dc3545' : '#6c757d'; ?>; margin: 10px 0;"><?php echo number_format($stats['api_errors']); ?></div>
                <div style="color: #666;">API错误数</div>
                <div style="margin-top: 10px;">
                    <span style="color: #dc3545;">错误率: <?php echo round($stats['api_errors'] / max($stats['api_calls'], 1) * 100, 1); ?>%</span>
                </div>
            </div>
        </div>

        <!-- 图表区域 -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <!-- 小时分布图表 -->
            <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">每日活跃时段分布</h3>
                <canvas id="hourlyChart" width="400" height="200"></canvas>
            </div>

            <!-- 热门关键词 -->
            <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">热门问题关键词</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <?php if (!empty($popular_keywords)): ?>
                        <?php foreach ($popular_keywords as $keyword): ?>
                            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                <span style="font-weight: 500;"><?php echo esc_html($keyword->content); ?></span>
                                <span style="color: #007cba; font-weight: bold;"><?php echo $keyword->frequency; ?>次</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #666; font-style: italic;">暂无数据</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 数据管理 -->
        <div class="card" style="padding: 20px; background: white; border: 1px solid #e5e5e5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">数据管理</h3>
            <p style="color: #666; margin-bottom: 15px;">统计数据会自动保留90天，超期数据会被自动清理。</p>
            <button type="button" class="button button-secondary" onclick="wpChatbotRefreshStats()">刷新统计数据</button>
            <span id="refresh-status" style="margin-left: 10px; color: #666;"></span>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 小时分布图表
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(range(0, 23)); ?>,
                datasets: [{
                    label: '对话数',
                    data: <?php echo json_encode($hourly_data); ?>,
                    backgroundColor: 'rgba(0, 124, 186, 0.6)',
                    borderColor: 'rgba(0, 124, 186, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // 刷新统计数据的函数
        function wpChatbotRefreshStats() {
            const statusEl = document.getElementById('refresh-status');
            statusEl.textContent = '正在刷新...';
            statusEl.style.color = '#666';

            // 这里可以添加AJAX调用来刷新统计数据
            setTimeout(() => {
                statusEl.textContent = '统计数据已刷新';
                statusEl.style.color = '#28a745';
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }, 1000);
        }
    </script>

    <style>
        .card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
    </style>
    <?php
}
