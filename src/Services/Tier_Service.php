<?php
/**
 * Tier Service
 *
 * Handles tier data retrieval from the wp_welp_product_tiers table.
 *
 * @package CartQuoteWooCommerce\Services
 * @author Jerel Yoshida
 * @since 1.0.39
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Services;

use CartQuoteWooCommerce\Admin\Settings;

class Tier_Service
{
    private static $table_name = 'welp_product_tiers';

    public static function get_all_tiers_by_product(int $product_id): array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . self::$table_name;
        
        $tiers = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d AND is_active = 1 ORDER BY tier_level ASC",
            $product_id
        ), ARRAY_A);
        
        return $tiers ?: [];
    }

    public static function get_tier_by_product(int $product_id): ?array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . self::$table_name;
        
        $tier = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d AND is_active = 1 LIMIT 1",
            $product_id
        ), ARRAY_A);
        
        return $tier ?: null;
    }

    public static function get_tier_data_for_cart(int $product_id): ?array
    {
        $debug_enabled = Settings::is_debug_mini_cart_enabled();
        
        $all_tiers = self::get_all_tiers_by_product($product_id);
        
        if ($debug_enabled && defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('============================================================');
            error_log('MINI-CART DEBUG: Tier_Service::get_tier_data_for_cart()');
            error_log('============================================================');
            error_log('  Product ID: ' . $product_id);
            error_log('  All Tiers Found: ' . count($all_tiers));
            
            if (!empty($all_tiers)) {
                foreach ($all_tiers as $i => $tier) {
                    error_log('  Tier [' . $i . ']:');
                    error_log('    tier_id: ' . ($tier['tier_id'] ?? 'N/A'));
                    error_log('    product_id: ' . ($tier['product_id'] ?? 'N/A'));
                    error_log('    tier_level: ' . ($tier['tier_level'] ?? 'N/A'));
                    error_log('    description: ' . ($tier['description'] ?? 'N/A'));
                    error_log('    tier_name: ' . ($tier['tier_name'] ?? 'N/A'));
                    error_log('    monthly_price: ' . ($tier['monthly_price'] ?? 'N/A'));
                    error_log('    hourly_price: ' . ($tier['hourly_price'] ?? 'N/A'));
                    error_log('    is_active: ' . ($tier['is_active'] ?? 'N/A'));
                }
            } else {
                error_log('  WARNING: No tiers found for this product!');
            }
        }
        
        if (empty($all_tiers)) {
            return null;
        }
        
        $tier = $all_tiers[0];
        
        $result = [
            'description'   => $tier['description'] ?? '',
            'tier_name'     => $tier['tier_name'] ?? '',
            'tier_level'    => $tier['tier_level'] ?? '',
            'monthly_price' => isset($tier['monthly_price']) ? (float) $tier['monthly_price'] : 0,
            'hourly_price'  => isset($tier['hourly_price']) ? (float) $tier['hourly_price'] : 0,
            '_debug_all_tiers' => $all_tiers,
        ];
        
        if ($debug_enabled && defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('  Returning first tier:');
            error_log('    tier_level: ' . $result['tier_level']);
            error_log('    description: ' . $result['description']);
            error_log('    tier_name: ' . $result['tier_name']);
        }
        
        return $result;
    }
}
