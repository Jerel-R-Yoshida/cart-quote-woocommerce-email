<?php
/**
 * Rate Limiting Settings Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.9
 */

if (!defined('ABSPATH')) {
    exit;
}

$enabled = (bool) get_option('cart_quote_wc_rate_limit_enabled', true);
$max_per_minute = (int) get_option('cart_quote_wc_rate_limit_max_per_minute', 5);
$block_duration = (int) get_option('cart_quote_wc_rate_limit_block_duration', 60);
$whitelist = get_option('cart_quote_wc_rate_limit_whitelist_ips', '');
$stats = \CartQuoteWooCommerce\Core\Rate_Limiter::get_rate_limit_statistics();
$blocked_ips = \CartQuoteWooCommerce\Core\Rate_Limiter::get_blocked_ips();
?>
<div class="wrap cart-quote-rate-limiting">
    <h1>
        <?php esc_html_e('Rate Limiting Settings', 'cart-quote-woocommerce-email'); ?>
    </h1>

    <div class="cart-rate-limiting-section">
        <h3><?php esc_html_e('Configuration', 'cart-quote-woocommerce-email'); ?></h3>
        <form method="post" class="cart-rate-limiting-form">
            <?php wp_nonce_field('cart_quote_rate_limiting_settings', 'cart_quote_rate_limiting_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="rate_limit_enabled">
                            <?php esc_html_e('Enable Rate Limiting', 'cart-quote-woocommerce-email'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox"
                               id="rate_limit_enabled"
                               name="rate_limit_enabled"
                               value="1"
                               <?php checked($enabled); ?>>
                        <p class="description">
                            <?php esc_html_e('Enable rate limiting to prevent abuse of the quote submission form.', 'cart-quote-woocommerce-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="max_per_minute">
                            <?php esc_html_e('Max Requests Per Minute', 'cart-quote-woocommerce-email'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number"
                               id="max_per_minute"
                               name="max_per_minute"
                               value="<?php echo esc_attr($max_per_minute); ?>"
                               min="1"
                               max="100"
                               class="small-text">
                        <p class="description">
                            <?php esc_html_e('Maximum number of quote submissions allowed per minute per IP address.', 'cart-quote-woocommerce-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="block_duration">
                            <?php esc_html_e('Block Duration (minutes)', 'cart-quote-woocommerce-email'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number"
                               id="block_duration"
                               name="block_duration"
                               value="<?php echo esc_attr($block_duration); ?>"
                               min="1"
                               max="1440"
                               class="small-text">
                        <p class="description">
                            <?php esc_html_e('Duration in minutes to block an IP address when rate limit is exceeded.', 'cart-quote-woocommerce-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="whitelist_ips">
                            <?php esc_html_e('IP Whitelist', 'cart-quote-woocommerce-email'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="whitelist_ips"
                                  name="whitelist_ips"
                                  rows="5"
                                  class="large-text"
                                  placeholder="<?php esc_attr_e('One IP address per line, e.g.:', 'cart-quote-woocommerce-email'); ?>">
192.168.1.1
10.0.0.5
</textarea>
                        <p class="description">
                            <?php esc_html_e('IP addresses that are exempt from rate limiting. Enter one IP per line.', 'cart-quote-woocommerce-email'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Rate Limiting Settings', 'cart-quote-woocommerce-email')); ?>
        </form>
    </div>

    <div class="cart-rate-limiting-section">
        <h3>
            <?php esc_html_e('Rate Limiting Statistics', 'cart-quote-woocommerce-email'); ?>
        </h3>

        <div class="rate-limiting-stats-grid">
            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Status', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value <?php echo $enabled ? 'status-enabled' : 'status-disabled'; ?>">
                    <?php echo $enabled ? esc_html__('Enabled', 'cart-quote-woocommerce-email') : esc_html__('Disabled', 'cart-quote-woocommerce-email'); ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Max Per Minute', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html($stats['max_per_minute']); ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Block Duration', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value">
                    <?php echo esc_html($stats['block_duration']); ?> min
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    <?php esc_html_e('Currently Blocked IPs', 'cart-quote-woocommerce-email'); ?>
                </div>
                <div class="stat-value <?php echo $blocked_ips > 0 ? 'stat-warning' : ''; ?>">
                    <?php echo esc_html($stats['blocked_ips']); ?>
                </div>
            </div>
        </div>

        <?php if (!empty($blocked_ips)) : ?>
            <div class="blocked-ips-list">
                <h4>
                    <?php esc_html_e('Currently Blocked IP Addresses', 'cart-quote-woocommerce-email'); ?>
                    <button type="button" class="button button-small" id="cart-quote-unblock-all">
                        <?php esc_html_e('Unblock All', 'cart-quote-woocommerce-email'); ?>
                    </button>
                </h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>
                                <?php esc_html_e('IP Hash', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Blocked Until', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Remaining Time', 'cart-quote-woocommerce-email'); ?>
                            </th>
                            <th>
                                <?php esc_html_e('Actions', 'cart-quote-woocommerce-email'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocked_ips as $blocked_ip) : ?>
                            <tr>
                                <td>
                                    <code><?php echo esc_html(substr($blocked_ip['ip_hash'], 0, 16) . '...'); ?></code>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $blocked_ip['blocked_until'])); ?>
                                </td>
                                <td>
                                    <span class="remaining-time <?php echo $blocked_ip['remaining_seconds'] < 300 ? 'warning' : ''; ?>">
                                        <?php 
                                        printf(
                                            esc_html__('%d min %d sec', 'cart-quote-woocommerce-email'),
                                            floor($blocked_ip['remaining_seconds'] / 60),
                                            $blocked_ip['remaining_seconds'] % 60
                                        );
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="button button-small unblock-ip-btn"
                                            data-ip-hash="<?php echo esc_attr($blocked_ip['ip_hash']); ?>">
                                        <?php esc_html_e('Unblock', 'cart-quote-woocommerce-email'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="notice notice-info">
                <p>
                    <?php esc_html_e('No IP addresses are currently blocked.', 'cart-quote-woocommerce-email'); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .cart-rate-limiting-section {
        margin: 20px 0;
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }

    .rate-limiting-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .stat-card {
        padding: 15px;
        background: #f6f7f7;
        border-left: 4px solid #0073aa;
    }

    .stat-card.stat-warning {
        border-left-color: #dc3232;
    }

    .stat-label {
        font-size: 0.85em;
        color: #646970;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .stat-value {
        font-size: 1.5em;
        font-weight: 700;
        margin-top: 5px;
    }

    .stat-value.status-enabled {
        color: #00a32a;
    }

    .stat-value.status-disabled {
        color: #d63638;
    }

    .blocked-ips-list {
        margin-top: 20px;
    }

    .blocked-ips-list h4 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0;
    }

    .remaining-time.warning {
        color: #dc3232;
        font-weight: 600;
    }
</style>

<script>
    (function() {
        // Save rate limiting settings
        document.querySelector('.cart-rate-limiting-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'cart_quote_save_rate_limiting_settings');
            formData.append('nonce', '<?php echo esc_attr(wp_create_nonce('cart_quote_save_rate_limiting')); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?php esc_attr_e('Failed to save settings.', 'cart-quote-woocommerce-email'); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php esc_attr_e('An error occurred.', 'cart-quote-woocommerce-email'); ?>');
            });
        });

        // Unblock single IP
        document.querySelectorAll('.unblock-ip-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const ipHash = this.getAttribute('data-ip-hash');

                if (confirm('<?php esc_attr_e('Are you sure you want to unblock this IP?', 'cart-quote-woocommerce-email'); ?>')) {
                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=cart_quote_unblock_ip&ip_hash=' + encodeURIComponent(ipHash) + '&nonce=<?php echo esc_attr(wp_create_nonce('cart_quote_unblock_ip')); ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || '<?php esc_attr_e('Failed to unblock IP.', 'cart-quote-woocommerce-email'); ?>');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?php esc_attr_e('An error occurred.', 'cart-quote-woocommerce-email'); ?>');
                    });
                }
            });
        });

        // Unblock all IPs
        document.getElementById('cart-quote-unblock-all').addEventListener('click', function() {
            if (confirm('<?php esc_attr_e('Are you sure you want to unblock all IPs?', 'cart-quote-woocommerce-email'); ?>')) {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=cart_quote_unblock_all_ips&nonce=<?php echo esc_attr(wp_create_nonce('cart_quote_unblock_all_ips')); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?php esc_attr_e('Failed to unblock IPs.', 'cart-quote-woocommerce-email'); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php esc_attr_e('An error occurred.', 'cart-quote-woocommerce-email'); ?>');
                });
            }
        });
    })();
</script>
