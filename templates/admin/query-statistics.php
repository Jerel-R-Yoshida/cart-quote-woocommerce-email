<?php
/**
 * Query Statistics Admin Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.9
 */

if (!defined('ABSPATH')) {
    exit;
}

$stats = \CartQuoteWooCommerce\Core\Query_Logger::get_statistics();
$n_plus_one = \CartQuoteWooCommerce\Core\Query_Logger::identify_n_plus_one();
$slow_queries = \CartQuoteWooCommerce\Core\Query_Logger::get_slow_queries();
$enabled = \CartQuoteWooCommerce\Core\Query_Logger::is_enabled();
?>
<div class="wrap cart-quote-query-stats">
    <h1>
        <?php esc_html_e('Query Statistics', 'cart-quote-woocommerce-email'); ?>
        <?php if ($enabled) : ?>
            <span class="status-badge status-active">
                <?php esc_html_e('Active', 'cart-quote-woocommerce-email'); ?>
            </span>
        <?php else : ?>
            <span class="status-badge status-inactive">
                <?php esc_html_e('Inactive', 'cart-quote-woocommerce-email'); ?>
            </span>
        <?php endif; ?>
    </h1>

    <div class="cart-query-stats-controls">
        <button type="button" class="button button-primary" id="cart-quote-refresh-stats">
            <?php esc_html_e('Refresh Statistics', 'cart-quote-woocommerce-email'); ?>
        </button>
        <button type="button" class="button" id="cart-quote-clear-stats">
            <?php esc_html_e('Clear Logs', 'cart-quote-woocommerce-email'); ?>
        </button>
        <button type="button" class="button" id="cart-quote-export-csv">
            <?php esc_html_e('Export to CSV', 'cart-quote-woocommerce-email'); ?>
        </button>
    </div>

    <?php if (!$enabled) : ?>
        <div class="notice notice-warning">
            <p>
                <?php 
                printf(
                    esc_html__('Query logging is disabled. To enable, add %s to your wp-config.php file.', 'cart-quote-woocommerce-email'),
                    '<code>define("SAVEQUERIES", true); define("CART_QUOTE_WC_DEBUG_QUERIES", true);</code>'
                );
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($enabled && empty($stats['total'])) : ?>
        <div class="notice notice-info">
            <p>
                <?php esc_html_e('No queries have been logged yet. Browse the plugin to generate activity.', 'cart-quote-woocommerce-email'); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($stats['total'] > 0) : ?>
        <div class="cart-query-stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo esc_html($stats['total']); ?></div>
                <div class="stat-label">
                    <?php esc_html_e('Total Queries', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-number">
                    <?php echo esc_html(number_format($stats['total_time_ms'], 2)); ?> ms
                </div>
                <div class="stat-label">
                    <?php esc_html_e('Total Time', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-number">
                    <?php echo esc_html(number_format($stats['avg_time_ms'], 2)); ?> ms
                </div>
                <div class="stat-label">
                    <?php esc_html_e('Average Time', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>

            <div class="stat-card <?php echo $stats['slow_queries'] > 0 ? 'stat-warning' : ''; ?>">
                <div class="stat-number">
                    <?php echo esc_html($stats['slow_queries']); ?>
                </div>
                <div class="stat-label">
                    <?php esc_html_e('Slow Queries', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>
        </div>

        <?php if (!empty($stats['slowest_query'])) : ?>
            <div class="cart-query-slowest">
                <h3>
                    <?php esc_html_e('Slowest Query', 'cart-quote-woocommerce-email'); ?>
                    <span class="query-time">
                        <?php echo esc_html(number_format($stats['slowest_query']['time_ms'], 2)); ?> ms
                    </span>
                </h3>
                <pre class="query-sql"><?php echo esc_html($stats['slowest_query']['sql']); ?></pre>
                <div class="query-meta">
                    <span class="query-rows">
                        <?php 
                        printf(
                            esc_html__('Rows Affected: %d', 'cart-quote-woocommerce-email'),
                            (int) $stats['slowest_query']['rows_affected']
                        );
                        ?>
                    </span>
                    <?php if (!empty($stats['slowest_query']['last_error'])) : ?>
                        <span class="query-error">
                            <?php 
                            printf(
                                esc_html__('Error: %s', 'cart-quote-woocommerce-email'),
                                esc_html($stats['slowest_query']['last_error'])
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($n_plus_one)) : ?>
            <div class="cart-query-n-plus-one">
                <h3>
                    <?php esc_html_e('Potential N+1 Query Problems', 'cart-quote-woocommerce-email'); ?>
                </h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('Query Pattern', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Count', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Avg Time', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Total Time', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Likelihood', 'cart-quote-woocommerce-email'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($n_plus_one as $candidate) : ?>
                            <tr>
                                <td>
                                    <code><?php echo esc_html($candidate['pattern']); ?></code>
                                </td>
                                <td><?php echo esc_html($candidate['count']); ?></td>
                                <td>
                                    <?php echo esc_html(number_format($candidate['avg_time_ms'], 2)); ?> ms
                                </td>
                                <td>
                                    <?php echo esc_html(number_format($candidate['total_time_ms'], 2)); ?> ms
                                </td>
                                <td>
                                    <div class="likelihood-bar">
                                        <div class="likelihood-fill" style="width: <?php echo esc_attr($candidate['likelihood'] * 100); ?>%;"></div>
                                    </div>
                                    <span class="likelihood-text">
                                        <?php echo esc_html(number_format($candidate['likelihood'] * 100, 0)); ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($slow_queries)) : ?>
            <div class="cart-query-slow-list">
                <h3>
                    <?php esc_html_e('Slow Queries List', 'cart-quote-woocommerce-email'); ?>
                    <span class="query-count">
                        (<?php echo esc_html(count($slow_queries)); ?>)
                    </span>
                </h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('Time (ms)', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Query', 'cart-quote-woocommerce-email'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slow_queries as $slow_query) : ?>
                            <tr>
                                <td class="query-time-cell <?php echo $slow_query['time_ms'] > 500 ? 'query-very-slow' : 'query-slow'; ?>">
                                    <?php echo esc_html(number_format($slow_query['time_ms'], 2)); ?> ms
                                </td>
                                <td>
                                    <code class="query-sql"><?php echo esc_html($slow_query['sql']); ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .cart-query-stats-controls {
        margin: 20px 0;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }

    .cart-query-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-left: 4px solid #0073aa;
        box-shadow: 0 1px 3px rgba(0,0,0,.12);
    }

    .stat-card.stat-warning {
        border-left-color: #dc3232;
    }

    .stat-number {
        font-size: 2em;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #646970;
        font-size: 0.9em;
    }

    .cart-query-slowest,
    .cart-query-n-plus-one,
    .cart-query-slow-list {
        margin-top: 30px;
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }

    .query-slowest h3,
    .cart-query-n-plus-one h3,
    .cart-query-slow-list h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .query-time {
        background: #f0f0f1;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.9em;
    }

    .query-sql {
        background: #f6f7f7;
        padding: 15px;
        border-radius: 4px;
        overflow-x: auto;
        font-size: 0.85em;
        line-height: 1.5;
    }

    .query-meta {
        margin-top: 15px;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .query-rows,
    .query-error {
        font-size: 0.9em;
        padding: 5px 10px;
        border-radius: 4px;
    }

    .query-error {
        background: #ffebe8;
        color: #dc3232;
    }

    .query-count {
        color: #646970;
        font-size: 0.8em;
        font-weight: normal;
    }

    .query-time-cell {
        font-weight: 700;
        white-space: nowrap;
        width: 100px;
    }

    .query-slow {
        background: #fff9e6;
    }

    .query-very-slow {
        background: #ffebe8;
    }

    .likelihood-bar {
        width: 100px;
        height: 10px;
        background: #f0f0f1;
        border-radius: 5px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
    }

    .likelihood-fill {
        height: 100%;
        background: #0073aa;
        transition: width 0.3s ease;
    }

    .likelihood-text {
        margin-left: 10px;
        font-size: 0.85em;
        vertical-align: middle;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<script>
    (function() {
        document.getElementById('cart-quote-refresh-stats').addEventListener('click', function() {
            location.reload();
        });

        document.getElementById('cart-quote-clear-stats').addEventListener('click', function() {
            if (confirm('<?php esc_attr_e('Are you sure you want to clear all query logs?', 'cart-quote-woocommerce-email'); ?>')) {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=cart_quote_clear_query_logs&nonce=<?php echo esc_attr(wp_create_nonce('cart_quote_clear_logs')); ?>'
                }).then(function(response) {
                    return response.json();
                }).then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?php esc_attr_e('Failed to clear logs.', 'cart-quote-woocommerce-email'); ?>');
                    }
                }).catch(function(error) {
                    console.error('Error:', error);
                });
            }
        });

        document.getElementById('cart-quote-export-csv').addEventListener('click', function() {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=cart_quote_export_query_csv&nonce=<?php echo esc_attr(wp_create_nonce('cart_quote_export_csv')); ?>'
            }).then(function(response) {
                return response.json();
            }).then(function(data) {
                if (data.success && data.csv) {
                    const blob = new Blob([data.csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'cart-quote-query-log-' + new Date().toISOString().slice(0,10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    alert(data.message || '<?php esc_attr_e('Failed to export CSV.', 'cart-quote-woocommerce-email'); ?>');
                }
            }).catch(function(error) {
                console.error('Error:', error);
            });
        });
    })();
</script>
