<?php
if (!defined('WPINC')) {
    die;
}

function maspik_render_statistics_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get time range from URL parameter, default to 'month'
    $time_range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : 'month';
    $valid_ranges = ['day', 'week', 'month', 'year'];
    $time_range = in_array($time_range, $valid_ranges) ? $time_range : 'month';

    // Get statistics data
    $stats = maspik_get_advanced_statistics($time_range);
    
    if (isset($stats['error'])) {
        echo '<div class="notice notice-error"><p>' . esc_html($stats['message']) . '</p></div>';
        return;
    }
    ?>
    <div class="wrap maspik-statistics">
        <h1><?php esc_html_e('Maspik Spam Protection Statistics', 'contact-forms-anti-spam'); ?></h1>
        
        <!-- Time Range Selector -->
        <div class="maspik-time-range">
            <select id="timeRange" onchange="window.location.href='?page=maspik-statistics&range=' + this.value">
                <option value="day" <?php selected($time_range, 'day'); ?>><?php esc_html_e('Last 24 Hours', 'contact-forms-anti-spam'); ?></option>
                <option value="week" <?php selected($time_range, 'week'); ?>><?php esc_html_e('Last 7 Days', 'contact-forms-anti-spam'); ?></option>
                <option value="month" <?php selected($time_range, 'month'); ?>><?php esc_html_e('Last 30 Days', 'contact-forms-anti-spam'); ?></option>
                <option value="year" <?php selected($time_range, 'year'); ?>><?php esc_html_e('Last Year', 'contact-forms-anti-spam'); ?></option>
            </select>
        </div>

        <!-- Overview Cards -->
        <div class="maspik-overview">
            <div class="stat-card">
                <h3><?php esc_html_e('Total Blocked', 'contact-forms-anti-spam'); ?></h3>
                <div class="stat-number"><?php echo number_format($stats['total_blocked']); ?></div>
                <?php if (isset($stats['percentage_change'])): ?>
                    <div class="trend <?php echo $stats['percentage_change'] >= 0 ? 'up' : 'down'; ?>">
                        <?php echo sprintf('%+.1f%%', $stats['percentage_change']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Daily Average', 'contact-forms-anti-spam'); ?></h3>
                <div class="stat-number"><?php echo number_format($stats['daily_average'], 1); ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Peak Hour', 'contact-forms-anti-spam'); ?></h3>
                <div class="stat-number"><?php echo $stats['peak_hour']; ?>:00</div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Most Active Country', 'contact-forms-anti-spam'); ?></h3>
                <div class="stat-number"><?php echo esc_html($stats['top_country']); ?></div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="maspik-charts-grid">
            <!-- Timeline Chart -->
            <div class="chart-container full-width">
                <h3><?php esc_html_e('Spam Activity Timeline', 'contact-forms-anti-spam'); ?></h3>
                <canvas id="timelineChart"></canvas>
            </div>

            <!-- Geographic Distribution -->
            <div class="chart-container">
                <h3><?php esc_html_e('Geographic Distribution', 'contact-forms-anti-spam'); ?></h3>
                <canvas id="geoChart"></canvas>
            </div>

            <!-- Spam Sources -->
            <div class="chart-container">
                <h3><?php esc_html_e('Spam Sources', 'contact-forms-anti-spam'); ?></h3>
                <canvas id="sourcesChart"></canvas>
            </div>

            <!-- Block Methods -->
            <div class="chart-container">
                <h3><?php esc_html_e('Blocking Methods', 'contact-forms-anti-spam'); ?></h3>
                <canvas id="methodsChart"></canvas>
            </div>
        </div>

        <!-- Detailed Tables -->
        <div class="maspik-tables">
            <!-- Top IPs Table -->
            <?php if (!empty($stats['top_ips'])): ?>
            <div class="table-container">
                <h3><?php esc_html_e('Top Spam IPs', 'contact-forms-anti-spam'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('IP Address', 'contact-forms-anti-spam'); ?></th>
                            <th><?php esc_html_e('Country', 'contact-forms-anti-spam'); ?></th>
                            <th><?php esc_html_e('Attempts', 'contact-forms-anti-spam'); ?></th>
                            <th><?php esc_html_e('Last Attempt', 'contact-forms-anti-spam'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_ips'] as $ip): ?>
                        <tr>
                            <td><?php echo esc_html($ip['ip']); ?></td>
                            <td><?php echo esc_html($ip['country']); ?></td>
                            <td><?php echo number_format($ip['count']); ?></td>
                            <td><?php echo esc_html($ip['last_attempt']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Top Domains Table -->
            <?php if (!empty($stats['top_domains'])): ?>
            <div class="table-container">
                <h3><?php esc_html_e('Top Spam Email Domains', 'contact-forms-anti-spam'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Domain', 'contact-forms-anti-spam'); ?></th>
                            <th><?php esc_html_e('Attempts', 'contact-forms-anti-spam'); ?></th>
                            <th><?php esc_html_e('Percentage', 'contact-forms-anti-spam'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['top_domains'] as $domain): ?>
                        <tr>
                            <td><?php echo esc_html($domain['domain']); ?></td>
                            <td><?php echo number_format($domain['count']); ?></td>
                            <td><?php echo number_format($domain['percentage'], 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .maspik-statistics {
            padding: 20px;
        }
        .maspik-time-range {
            margin: 20px 0;
        }
        .maspik-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #F48722;
        }
        .trend {
            margin-top: 10px;
            font-size: 14px;
        }
        .trend.up { color: #46b450; }
        .trend.down { color: #dc3232; }
        .maspik-charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container.full-width {
            grid-column: 1 / -1;
        }
        .maspik-tables {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timelineData = <?php echo maspik_safe_json_encode($stats['timeline_data']); ?>;
            const geoData = <?php echo maspik_safe_json_encode($stats['geo_data']); ?>;
            const sourcesData = <?php echo maspik_safe_json_encode($stats['sources_data']); ?>;
            const methodsData = <?php echo maspik_safe_json_encode($stats['methods_data']); ?>;

            // Timeline Chart
            new Chart(document.getElementById('timelineChart'), {
                type: 'line',
                data: {
                    labels: timelineData.labels,
                    datasets: [{
                        label: '<?php esc_html_e('Blocked Spam', 'contact-forms-anti-spam'); ?>',
                        data: timelineData.values,
                        borderColor: '#F48722',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });

            // Geographic Distribution Chart
            new Chart(document.getElementById('geoChart'), {
                type: 'doughnut',
                data: {
                    labels: geoData.labels,
                    datasets: [{
                        data: geoData.values,
                        backgroundColor: [
                            '#F48722',
                            '#e14d43',
                            '#46b450',
                            '#ffb900',
                            '#826eb4'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Sources Chart
            new Chart(document.getElementById('sourcesChart'), {
                type: 'bar',
                data: {
                    labels: sourcesData.labels,
                    datasets: [{
                        label: '<?php esc_html_e('Spam Attempts', 'contact-forms-anti-spam'); ?>',
                        data: sourcesData.values,
                        backgroundColor: '#F48722'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });

            // Methods Chart
            new Chart(document.getElementById('methodsChart'), {
                type: 'pie',
                data: {
                    labels: methodsData.labels,
                    datasets: [{
                        data: methodsData.values,
                        backgroundColor: [
                            '#F48722',
                            '#e14d43',
                            '#46b450',
                            '#ffb900',
                            '#826eb4'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
    <?php
}

function maspik_get_advanced_statistics($time_range) {
    global $wpdb;
    $table = maspik_get_logtable();

    if (!maspik_logtable_exists()) {
        return array(
            'error' => true,
            'message' => __('The spam log table does not exist.', 'contact-forms-anti-spam')
        );
    }

    // Set time interval based on range
    switch ($time_range) {
        case 'day':
            $interval = '1 DAY';
            $group_by = 'HOUR(spam_date)';
            $date_format = '%H:00';
            break;
        case 'week':
            $interval = '7 DAY';
            $group_by = 'DATE(spam_date)';
            $date_format = '%Y-%m-%d';
            break;
        case 'year':
            $interval = '1 YEAR';
            $group_by = 'MONTH(spam_date)';
            $date_format = '%Y-%m';
            break;
        default: // month
            $interval = '30 DAY';
            $group_by = 'DATE(spam_date)';
            $date_format = '%Y-%m-%d';
    }

    try {
        // Get total blocked and timeline data
        $timeline_query = $wpdb->prepare("
            SELECT 
                DATE_FORMAT(spam_date, %s) as date_group,
                COUNT(*) as count
            FROM $table
            WHERE spam_date >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY $group_by
            ORDER BY spam_date ASC
        ", $date_format);

        $timeline_results = $wpdb->get_results($timeline_query);

        // Get geographical data
        $geo_query = "
            SELECT 
                spam_country as country,
                COUNT(*) as count
            FROM $table
            WHERE spam_date >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY spam_country
            ORDER BY count DESC
            LIMIT 5
        ";
        $geo_results = $wpdb->get_results($geo_query);

        // Get spam sources breakdown
        $sources_query = "
            SELECT 
                spam_source as source,
                COUNT(*) as count
            FROM $table
            WHERE spam_date >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY spam_source
            ORDER BY count DESC
            LIMIT 5
        ";
        $sources_results = $wpdb->get_results($sources_query);

        // Get blocking methods breakdown
        $methods_query = "
            SELECT 
                spam_type as method,
                COUNT(*) as count
            FROM $table
            WHERE spam_date >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY spam_type
            ORDER BY count DESC
        ";
        $methods_results = $wpdb->get_results($methods_query);

        // Get top IPs
        $ips_query = "
            SELECT 
                spam_ip as ip,
                spam_country as country,
                COUNT(*) as count,
                MAX(spam_date) as last_attempt
            FROM $table
            WHERE spam_date >= DATE_SUB(NOW(), INTERVAL $interval)
            GROUP BY spam_ip, spam_country
            ORDER BY count DESC
            LIMIT 10
        ";
        $ips_results = $wpdb->get_results($ips_query);

        // Format data for charts
        $timeline_data = array(
            'labels' => array_map(function($item) { return $item->date_group; }, $timeline_results),
            'values' => array_map(function($item) { return $item->count; }, $timeline_results)
        );

        $geo_data = array(
            'labels' => array_map(function($item) { return $item->country; }, $geo_results),
            'values' => array_map(function($item) { return $item->count; }, $geo_results)
        );

        $sources_data = array(
            'labels' => array_map(function($item) { return $item->source; }, $sources_results),
            'values' => array_map(function($item) { return $item->count; }, $sources_results)
        );

        $methods_data = array(
            'labels' => array_map(function($item) { return $item->method; }, $methods_results),
            'values' => array_map(function($item) { return $item->count; }, $methods_results)
        );

        // Calculate totals and averages
        $total_blocked = array_sum($timeline_data['values']);
        $daily_average = $total_blocked / count($timeline_data['values']);

        return array(
            'total_blocked' => $total_blocked,
            'daily_average' => $daily_average,
            'timeline_data' => $timeline_data,
            'geo_data' => $geo_data,
            'sources_data' => $sources_data,
            'methods_data' => $methods_data,
            'top_ips' => $ips_results,
            'peak_hour' => $timeline_data['labels'][array_search(max($timeline_data['values']), $timeline_data['values'])],
            'top_country' => !empty($geo_data['labels']) ? $geo_data['labels'][0] : __('Unknown', 'contact-forms-anti-spam')
        );

    } catch (Exception $e) {
        return array(
            'error' => true,
            'message' => __('An error occurred while fetching statistics.', 'contact-forms-anti-spam')
        );
    }
}
