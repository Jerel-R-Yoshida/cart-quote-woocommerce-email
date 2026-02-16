<?php
/**
 * Cache Manager - Handles caching for frequently accessed options
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.9
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

class Cache_Manager
{
    private const CACHE_PREFIX = 'cart_quote_wc_';
    public const CACHE_EXPIRY = 3600;

    private static $instance = null;
    private static $enabled = true;
    private static $cache_stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    ];

    private function __construct()
    {
    }

    public static function get_instance(): Cache_Manager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void
    {
        self::$enabled = (bool) get_option('cart_quote_wc_cache_enabled', true);
    }

    public static function is_enabled(): bool
    {
        return self::$enabled && wp_using_ext_object_cache();
    }

    public static function get_settings(): array
    {
        if (!self::$enabled) {
            return self::get_settings_from_db();
        }

        $cache_key = self::CACHE_PREFIX . 'settings';

        $cached = wp_cache_get($cache_key, 'cart_quote_cache');

        if ($cached !== false) {
            self::$cache_stats['hits']++;
            return $cached;
        }

        self::$cache_stats['misses']++;
        $settings = self::get_settings_from_db();

        wp_cache_set($cache_key, $settings, 'cart_quote_cache', self::CACHE_EXPIRY);
        self::$cache_stats['sets']++;

        return $settings;
    }

    public static function get_time_slots(): array
    {
        if (!self::$enabled) {
            return get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);
        }

        $cache_key = self::CACHE_PREFIX . 'time_slots';

        $cached = wp_cache_get($cache_key, 'cart_quote_cache');

        if ($cached !== false) {
            self::$cache_stats['hits']++;
            return $cached;
        }

        self::$cache_stats['misses']++;
        $time_slots = get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);

        wp_cache_set($cache_key, $time_slots, 'cart_quote_cache', self::CACHE_EXPIRY);
        self::$cache_stats['sets']++;

        return $time_slots;
    }

    public static function get_google_config(): array
    {
        if (!self::$enabled) {
            return [
                'client_id' => get_option('cart_quote_wc_google_client_id', ''),
                'client_secret' => get_option('cart_quote_wc_google_client_secret', ''),
                'connected' => get_option('cart_quote_wc_google_connected', false),
                'access_token' => get_option('cart_quote_wc_google_access_token', ''),
                'refresh_token' => get_option('cart_quote_wc_google_refresh_token', ''),
                'token_expires' => (int) get_option('cart_quote_wc_google_token_expires', 0),
                'calendar_id' => get_option('cart_quote_wc_google_calendar_id', 'primary'),
                'auto_create_event' => (bool) get_option('cart_quote_wc_auto_create_event', false),
                'meeting_duration' => (int) get_option('cart_quote_wc_meeting_duration', 60),
            ];
        }

        $cache_key = self::CACHE_PREFIX . 'google_config';

        $cached = wp_cache_get($cache_key, 'cart_quote_cache');

        if ($cached !== false) {
            self::$cache_stats['hits']++;
            return $cached;
        }

        self::$cache_stats['misses']++;
        $google_config = [
            'client_id' => get_option('cart_quote_wc_google_client_id', ''),
            'client_secret' => get_option('cart_quote_wc_google_client_secret', ''),
            'connected' => get_option('cart_quote_wc_google_connected', false),
            'access_token' => get_option('cart_quote_wc_google_access_token', ''),
            'refresh_token' => get_option('cart_quote_wc_google_refresh_token', ''),
            'token_expires' => (int) get_option('cart_quote_wc_google_token_expires', 0),
            'calendar_id' => get_option('cart_quote_wc_google_calendar_id', 'primary'),
            'auto_create_event' => (bool) get_option('cart_quote_wc_auto_create_event', false),
            'meeting_duration' => (int) get_option('cart_quote_wc_meeting_duration', 60),
        ];

        wp_cache_set($cache_key, $google_config, 'cart_quote_cache', self::CACHE_EXPIRY);
        self::$cache_stats['sets']++;

        return $google_config;
    }

    public static function get(string $key, $default = null)
    {
        if (!self::$enabled) {
            return get_option('cart_quote_wc_' . $key, $default);
        }

        $cache_key = self::CACHE_PREFIX . $key;

        $cached = wp_cache_get($cache_key, 'cart_quote_cache');

        if ($cached !== false) {
            self::$cache_stats['hits']++;
            return $cached;
        }

        self::$cache_stats['misses']++;
        $value = get_option('cart_quote_wc_' . $key, $default);

        wp_cache_set($cache_key, $value, 'cart_quote_cache', self::CACHE_EXPIRY);
        self::$cache_stats['sets']++;

        return $value;
    }

    public static function set(string $key, $value, int $expiry = null): bool
    {
        $cache_key = self::CACHE_PREFIX . $key;
        $expiry = $expiry ?? self::CACHE_EXPIRY;

        $result = wp_cache_set($cache_key, $value, 'cart_quote_cache', $expiry);

        if ($result) {
            self::$cache_stats['sets']++;
        }

        return $result;
    }

    public static function delete(string $key): bool
    {
        $cache_key = self::CACHE_PREFIX . $key;
        $result = wp_cache_delete($cache_key, 'cart_quote_cache');

        if ($result) {
            self::$cache_stats['deletes']++;
        }

        return $result;
    }

    public static function clear_settings_cache(): void
    {
        $cache_keys = [
            self::CACHE_PREFIX . 'settings',
            self::CACHE_PREFIX . 'time_slots',
            self::CACHE_PREFIX . 'google_config',
            self::CACHE_PREFIX . 'quote_prefix',
            self::CACHE_PREFIX . 'quote_start_number',
            self::CACHE_PREFIX . 'admin_email',
            self::CACHE_PREFIX . 'email_subject_admin',
            self::CACHE_PREFIX . 'email_subject_client',
            self::CACHE_PREFIX . 'send_to_admin',
            self::CACHE_PREFIX . 'send_to_client',
        ];

        foreach ($cache_keys as $cache_key) {
            wp_cache_delete($cache_key, 'cart_quote_cache');
            self::$cache_stats['deletes']++;
        }
    }

    public static function clear_all_cache(): void
    {
        wp_cache_flush_group('cart_quote_cache');
        self::$cache_stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
        ];
    }

    public static function warm_cache(): void
    {
        if (!self::$enabled) {
            return;
        }

        self::get_settings();
        self::get_time_slots();
        self::get_google_config();
    }

    public static function get_statistics(): array
    {
        return [
            'enabled' => self::$enabled,
            'has_object_cache' => wp_using_ext_object_cache(),
            'cache_group' => 'cart_quote_cache',
            'stats' => self::$cache_stats,
            'hit_rate' => self::calculate_hit_rate(),
            'total_requests' => self::$cache_stats['hits'] + self::$cache_stats['misses'],
        ];
    }

    private static function calculate_hit_rate(): float
    {
        $total = self::$cache_stats['hits'] + self::$cache_stats['misses'];

        if ($total === 0) {
            return 0.0;
        }

        return (self::$cache_stats['hits'] / $total) * 100;
    }

    private static function get_settings_from_db(): array
    {
        return [
            'quote_prefix' => get_option('cart_quote_wc_quote_prefix', 'Q'),
            'quote_start_number' => get_option('cart_quote_wc_quote_start_number', '1001'),
            'send_to_admin' => (bool) get_option('cart_quote_wc_send_to_admin', true),
            'send_to_client' => (bool) get_option('cart_quote_wc_send_to_client', true),
            'admin_email' => get_option('cart_quote_wc_admin_email', get_option('admin_email')),
            'email_subject_admin' => get_option('cart_quote_wc_email_subject_admin', 'New Quote Submission #{quote_id}'),
            'email_subject_client' => get_option('cart_quote_wc_email_subject_client', 'Thank you for your quote request #{quote_id}'),
            'enable_pdf' => (bool) get_option('cart_quote_wc_enable_pdf', false),
            'meeting_duration' => (int) get_option('cart_quote_wc_meeting_duration', 60),
            'auto_create_event' => (bool) get_option('cart_quote_wc_auto_create_event', false),
        ];
    }
}
