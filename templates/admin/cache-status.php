<?php
/**
 * Cache Status Admin Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.9
 */

if (!defined('ABSPATH')) {
    exit;
}

$enabled = \CartQuoteWooCommerce\Core\Cache_Manager::is_enabled();
$stats = \CartQuoteWooCommerce\Core\Cache_Manager::get_statistics();
?>
<div class="wrap cart-quote-cache-status">
    <h1>
        <?php esc_html_e('Cache Status', 'cart-quote-woocommerce-email'); ?>
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

    <div class="cache-status-section">
        <h3><?php esc_html_e('Cache Configuration', 'cart-quote-woocommerce-email'); ?></h3>
        
        <div class="cache-config-grid">
            <div class="config-item">
                <div class="config-label">
                    <?php esc_html_e('Cache Enabled', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="config-value <?php echo $enabled ? 'status-enabled' : 'status-disabled'; ?>">
                    <?php echo $enabled ? esc_html__('Yes', 'cart-quote-woocommerce-email') : esc_html__('No', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>

            <div class="config-item">
                <div class="config-label">
                    <?php esc_html_e('Object Cache Available', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="config-value <?php echo $stats['has_object_cache'] ? 'status-enabled' : 'status-disabled'; ?>">
                    <?php echo $stats['has_object_cache'] ? esc_html__('Yes', 'cart-quote-woocommerce-email') : esc_html__('No', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>

            <div class="config-item">
                <div class="config-label">
                    <?php esc_html_e('Cache Group', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="config-value">
                    <code><?php echo esc_html($stats['cache_group']); ?></code>
                </div>
            </div>

            <div class="config-item">
                <div class="config-label">
                    <?php esc_html_e('Default Expiry', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="config-value">
                    <?php echo esc_html(\CartQuoteWooCommerce\Core\Cache_Manager::CACHE_EXPIRY); ?> sec
                    (<?php echo esc_html(round(\CartQuoteWooCommerce\Core\Cache_Manager::CACHE_EXPIRY / 60, 1)); ?> min)
                </div>
            </div>
        </div>

        <div class="cache-actions">
            <button type="button" class="button" id="cart-quote-warm-cache">
                <?php esc_html_e('Warm Cache', 'cart-quote-woocommerce-email'); ?>
            </button>
            <button type="button" class="button" id="cart-quote-clear-cache">
                <?php esc_html_e('Clear Cache', 'cart-quote-woocommerce-email'); ?>
            </button>
            <button type="button" class="button button-primary" id="cart-quote-refresh-stats">
                <?php esc_html_e('Refresh Statistics', 'cart-quote-woocommerce-email'); ?>
            </button>
        </div>
    </div>

    <div class="cache-status-section">
        <h3>
            <?php esc_html_e('Cache Statistics', 'cart-quote-woocommerce-email'); ?>
        </h3>

        <div class="cache-stats-grid">
            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Total Requests', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html(number_format($stats['total_requests'])); ?>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-label">
                    <?php esc_html_e('Cache Hits', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html(number_format($stats['stats']['hits'])); ?>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-label">
                    <?php esc_html_e('Cache Misses', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html(number_format($stats['stats']['misses'])); ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Cache Sets', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html(number_format($stats['stats']['sets'])); ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Cache Deletes', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html(number_format($stats['stats']['deletes'])); ?>
                </div>
            </div>

            <div class="stat-card <?php echo $stats['hit_rate'] >= 80 ? 'stat-success' : ($stats['hit_rate'] >= 60 ? 'stat-info' : 'stat-warning'); ?>">
                <div class="stat-label">
                    <?php esc_html_e('Hit Rate', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html(number_format($stats['hit_rate'], 2)); ?>%
                </div>
            </div>
        </div>

        <?php if ($stats['total_requests'] === 0) : ?>
            <div class="notice notice-info">
                <p>
                    <?php esc_html_e('No cache activity yet. Browse the plugin to generate activity.', 'cart-quote-woocommerce-email'); ?>
                </p>
            </div>
        <?php elseif ($stats['hit_rate'] < 50) : ?>
            <div class="notice notice-warning">
                <p>
                    <?php 
                    printf(
                        esc_html__('Low cache hit rate (%s%%). Consider reviewing cache configuration or enabling object cache for better performance.', 'cart-quote-woocommerce-email'),
                        number_format($stats['hit_rate'], 2)
                    );
                    ?>
                </p>
            </div>
        <?php elseif ($stats['hit_rate'] >= 80) : ?>
            <div class="notice notice-success">
                <p>
                    <?php 
                    printf(
                        esc_html__('Excellent cache hit rate (%s%%). Caching is working effectively!', 'cart-quote-woocommerce-email'),
                        number_format($stats['hit_rate'], 2)
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$enabled) : ?>
        <div class="notice notice-info">
            <p>
                <?php esc_html_e('Caching is disabled. To enable caching, ensure you have an object cache plugin installed (e.g., Redis, Memcached, or a WordPress object cache drop-in).', 'cart-quote-woocommerce-email'); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (!$stats['has_object_cache']) : ?>
        <div class="notice notice-warning">
            <p>
                <?php 
                printf(
                    esc_html__('Object cache is not available. Install an object cache plugin for better performance: %s', 'cart-quote-woocommerce-email'),
                    '<a href="https://wordpress.org/plugins/search/object+cache/" target="_blank">WordPress Plugin Directory</a>'
                );
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<style>
    .cache-status-section {
        margin: 20px 0;
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }

    .cache-config-grid,
    .cache-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .config-item,
    .stat-card {
        padding: 15px;
        background: #f6f7f7;
        border-left: 4px solid #0073aa;
    }

    .stat-card.stat-success {
        border-left-color: #00a32a;
        background: #e7f3e9;
    }

    .stat-card.stat-warning {
        border-left-color: #dba617;
        background: #fdf5e6;
    }

    .stat-card.stat-info {
        border-left-color: #0073aa;
        background: #edf7ff;
    }

    .config-label,
    .stat-label {
        font-size: 0.85em;
        color: #646970;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .config-value,
    .stat-value {
        font-size: 1.5em;
        font-weight: 700;
        margin-top: 5px;
    }

    .config-value.status-enabled {
        color: #00a32a;
    }

    .config-value.status-disabled {
        color: #d63638;
    }

    .cache-actions {
        display: flex;
        gap: 10px;
        margin: 20px 0;
        padding: 15px;
        background: #f6f7f7;
        border-radius: 4px;
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

    code {
        background: #f0f0f1;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 0.9em;
    }
</style>

<script>
    (function() {
        document.getElementById('cart-quote-warm-cache').addEventListener('click', function() {
            if (confirm('<?php esc_attr_e('Are you sure you want to warm the cache? This will load all frequently accessed data into cache.', 'cart-quote-woocommerce-email'); ?>')) {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=cart_quote_warm_cache&nonce=<?php echo esc_attr(wp_create_nonce('cart_quote_warm_cache')); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || '<?php esc_attr_e('Cache warmed successfully.', 'cart-quote-woocommerce-email'); ?>');
                        location.reload();
                    } else {
                        alert(data.message || '<?php esc_attr_e('Failed to warm cache.', 'cart-quote-woocommerce-email'); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php esc_attr_e('An error occurred.', 'cart-quote-woocommerce-email'); ?>');
                });
            }
        });

        document.getElementById('cart-quote-clear-cache').addEventListener('click', function() {
            if (confirm('<?php esc_attr_e('Are you sure you want to clear the cache? This will remove all cached data.', 'cart-quote-woocommerce-email'); ?>')) {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=cart_quote_clear_cache&nonce=<?php echo esc_attr(wp_create_nonce('cart_quote_clear_cache')); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || '<?php esc_attr_e('Cache cleared successfully.', 'cart-quote-woocommerce-email'); ?>');
                        location.reload();
                    } else {
                        alert(data.message || '<?php esc_attr_e('Failed to clear cache.', 'cart-quote-woocommerce-email'); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php esc_attr_e('An error occurred.', 'cart-quote-woocommerce-email'); ?>');
                });
            }
        });

        document.getElementById('cart-quote-refresh-stats').addEventListener('click', function() {
            location.reload();
        });
    })();
</script>
