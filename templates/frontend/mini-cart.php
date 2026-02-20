<?php
/**
 * Frontend Mini Cart Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use CartQuoteWooCommerce\Admin\Settings;

$cart_count = WC()->cart->get_cart_contents_count();
$cart_subtotal = WC()->cart->get_cart_subtotal();
$is_empty = WC()->cart->is_empty();

$debug_enabled = Settings::is_debug_mini_cart_enabled();
$debug_log = $debug_enabled && defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;

if ($debug_log) {
    error_log('============================================================');
    error_log('MINI-CART DEBUG: Template Rendering Start');
    error_log('============================================================');
    error_log('  Cart Count: ' . $cart_count);
    error_log('  Cart Subtotal: ' . $cart_subtotal);
    error_log('  Is Empty: ' . ($is_empty ? 'YES' : 'NO'));
    error_log('');
    
    error_log('RAW CART DATA (WC()->cart->get_cart()):');
    error_log('  Total Items in Cart: ' . count(WC()->cart->get_cart()));
    
    foreach (WC()->cart->get_cart() as $key => $item) {
        error_log('  ----------------------------------------');
        error_log('  Cart Item Key: ' . $key);
        error_log('    product_id: ' . ($item['product_id'] ?? 'N/A'));
        error_log('    variation_id: ' . ($item['variation_id'] ?? 'N/A'));
        error_log('    Product Name: ' . (isset($item['data']) ? $item['data']->get_name() : 'N/A'));
        error_log('    Quantity: ' . ($item['quantity'] ?? 'N/A'));
        error_log('    Line Total: ' . ($item['line_total'] ?? 'N/A'));
        error_log('    Line Subtotal: ' . ($item['line_subtotal'] ?? 'N/A'));
        error_log('    Has tier_data: ' . (isset($item['tier_data']) ? 'YES' : 'NO'));
        
        if (isset($item['tier_data'])) {
            $td = $item['tier_data'];
            error_log('    TIER DATA:');
            error_log('      tier_level: ' . ($td['tier_level'] ?? 'N/A'));
            error_log('      description: ' . ($td['description'] ?? 'N/A'));
            error_log('      tier_name: ' . ($td['tier_name'] ?? 'N/A'));
            error_log('      monthly_price: ' . ($td['monthly_price'] ?? 'N/A'));
            error_log('      hourly_price: ' . ($td['hourly_price'] ?? 'N/A'));
            
            if (isset($td['_debug_all_tiers'])) {
                error_log('      _debug_all_tiers (count): ' . count($td['_debug_all_tiers']));
                foreach ($td['_debug_all_tiers'] as $i => $t) {
                    error_log('        [' . $i . '] level=' . ($t['tier_level'] ?? 'N/A') . ' desc=' . ($t['description'] ?? 'N/A'));
                }
            }
        }
    }
    error_log('');
}

$parent_items = [];
$tier_items_by_parent = [];

if (!$is_empty) {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['tier_data'])) {
            $parent_id = $cart_item['product_id'];
            $tier_items_by_parent[$parent_id][] = $cart_item;
        } else {
            $parent_items[] = $cart_item;
        }
    }
}

if ($debug_log) {
    error_log('ITEM SEPARATION RESULTS:');
    error_log('  Parent Items (no tier_data): ' . count($parent_items));
    error_log('  Parent IDs with Tiers: ' . count($tier_items_by_parent));
    
    if (!empty($parent_items)) {
        error_log('  Parent Items Detail:');
        foreach ($parent_items as $i => $p) {
            $pid = isset($p['data']) ? $p['data']->get_id() : ($p['product_id'] ?? 'N/A');
            $related_tiers = isset($tier_items_by_parent[$pid]) ? count($tier_items_by_parent[$pid]) : 0;
            error_log('    [' . $i . '] ' . (isset($p['data']) ? $p['data']->get_name() : 'N/A') . ' (ID:' . $pid . ') - Related Tiers: ' . $related_tiers);
        }
    }
    
    if (!empty($tier_items_by_parent)) {
        error_log('  Tier Items by Parent:');
        foreach ($tier_items_by_parent as $pid => $tiers) {
            error_log('    Parent ID ' . $pid . ' has ' . count($tiers) . ' tier items:');
            foreach ($tiers as $j => $t) {
                $td = $t['tier_data'] ?? [];
                error_log('      [' . $j . '] Tier ' . ($td['tier_level'] ?? 'N/A') . ': ' . ($td['description'] ?? 'N/A'));
            }
        }
    }
    error_log('');
    
    error_log('RENDER LOOP START:');
}
?>
<div class="cart-quote-mini-cart-container" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
    <div class="cart-quote-mini-cart">
        <div class="cart-quote-mini-toggle">
            <span class="dashicons dashicons-cart cart-quote-toggle-icon"></span>
            
            <span class="cart-quote-label">
                <?php esc_html_e('Cart', 'cart-quote-woocommerce-email'); ?>
                <span class="cart-count-badge">(<?php echo esc_html($cart_count); ?>)</span>
            </span>
            
            <?php if ($atts['show_subtotal'] === 'true') : ?>
                <span class="cart-quote-mini-subtotal">
                    <?php echo wp_kses_post($cart_subtotal); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$is_empty) : ?>
            <div class="cart-quote-mini-dropdown">
                <?php if (!empty($parent_items)) : ?>
                    <?php foreach ($parent_items as $parent_key => $parent) : ?>
                        <?php 
                        $product = $parent['data'];
                        $parent_id = $product->get_id();
                        $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];
                        
                        // Calculate sum of tier prices and quantities
                        $tier_total = 0;
                        $tier_qty_sum = 0;
                        foreach ($tier_items as $tier) {
                            $tier_total += $tier['line_total'];
                            $tier_qty_sum += $tier['quantity'];
                        }
                        
                        // Parent price = tier sum if tiers exist, otherwise parent's own price
                        $parent_price = !empty($tier_items) ? $tier_total : $parent['line_total'];
                        
                        // Parent quantity = sum of tier quantities if tiers exist, otherwise parent's own quantity
                        $parent_qty = !empty($tier_items) ? $tier_qty_sum : $parent['quantity'];
                        
                        if ($debug_log) {
                            error_log('  Rendering Parent: ' . $product->get_name());
                            error_log('    Parent ID: ' . $parent_id);
                            error_log('    Tier Items Count: ' . count($tier_items));
                            error_log('    Calculated Tier Total: ' . $tier_total);
                            error_log('    Calculated Tier Qty Sum: ' . $tier_qty_sum);
                            error_log('    Display Price: ' . $parent_price);
                            error_log('    Display Qty: X' . $parent_qty);
                        }
                        ?>
                        
                        <div class="cart-quote-mini-item parent-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($parent_qty); ?></span>
                            <span class="item-price"><?php echo wc_price($parent_price); ?></span>
                        </div>
                        
                        <?php foreach ($tier_items as $tier) : ?>
                            <?php 
                            $tier_data = $tier['tier_data'];
                            $tier_label = '';
                            
                            if (!empty($tier_data['tier_level'])) {
                                $tier_label .= esc_html__('Tier', 'cart-quote-woocommerce-email') . ' ' . esc_html($tier_data['tier_level']);
                                $tier_label .= ': ';
                            }
                            if (!empty($tier_data['description'])) {
                                $tier_label .= esc_html($tier_data['description']);
                            } elseif (!empty($tier_data['tier_name'])) {
                                $tier_label .= esc_html($tier_data['tier_name']);
                            }
                            
                            if ($debug_log) {
                                error_log('    Rendering Tier:');
                                error_log('      tier_level: ' . ($tier_data['tier_level'] ?? 'N/A'));
                                error_log('      description: ' . ($tier_data['description'] ?? 'N/A'));
                                error_log('      tier_name: ' . ($tier_data['tier_name'] ?? 'N/A'));
                                error_log('      Display Label: ' . $tier_label);
                                error_log('      Qty: X' . $tier['quantity']);
                                error_log('      Price: ' . $tier['line_total']);
                            }
                            ?>
                            
                            <div class="cart-quote-mini-item tier-item">
                                <span class="item-name">• <?php echo $tier_label; ?></span>
                                <span class="item-qty">X<?php echo esc_html($tier['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($tier['line_total']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($parent_key < count($parent_items) - 1) : ?>
                            <div class="cart-quote-item-separator"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
                        <?php 
                        $product = $cart_item['data'];
                        ?>
                        <div class="cart-quote-mini-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                            <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="cart-quote-mini-total">
                    <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                    <span class="subtotal-amount"><?php echo wp_kses_post($cart_subtotal); ?></span>
                </div>

                <div class="cart-quote-mini-actions">
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-quote-mini-btn view-cart">
                        <?php esc_html_e('View Cart', 'cart-quote-woocommerce-email'); ?>
                    </a>
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cart-quote-mini-btn get-quote">
                        <?php esc_html_e('Get Quote', 'cart-quote-woocommerce-email'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
if ($debug_log) {
    error_log('MINI-CART DEBUG: Template Rendering End');
    error_log('============================================================');
    error_log('');
}
?>
