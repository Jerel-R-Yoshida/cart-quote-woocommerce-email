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

class Tier_Service
{
    private static $table_name = 'welp_product_tiers';

    public static function get_tier_by_product(int $product_id): ?array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . self::$table_name;
        
        $tier = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE product_id = %d LIMIT 1",
            $product_id
        ), ARRAY_A);
        
        return $tier ?: null;
    }

    public static function get_tier_data_for_cart(int $product_id): ?array
    {
        $tier = self::get_tier_by_product($product_id);
        
        if (!$tier) {
            return null;
        }
        
        return [
            'description'   => $tier['description'] ?? '',
            'tier_name'     => $tier['tier_name'] ?? '',
            'tier_level'    => $tier['tier_level'] ?? '',
            'monthly_price' => isset($tier['monthly_price']) ? (float) $tier['monthly_price'] : 0,
            'hourly_price'  => isset($tier['hourly_price']) ? (float) $tier['hourly_price'] : 0,
        ];
    }
}
