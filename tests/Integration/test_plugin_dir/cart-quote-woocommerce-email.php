<?php
/**
 * Test Plugin File
 */
if (!defined('ABSPATH')) {
    exit;
}

define('CART_QUOTE_WC_VERSION', '1.0.0');
define('CART_QUOTE_WC_PLUGIN_FILE', __FILE__);
define('CART_QUOTE_WC_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Test autoloader
spl_autoload_register(function($class) {
    $prefix = 'CartQuoteWooCommerce\\';
    $base_dir = CART_QUOTE_WC_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Test validation
function test_validator_validate_critical_files() {
    if (!class_exists('CartQuoteWooCommerce\\Core\\Validator')) {
        return ["valid" => false, "missing" => ["src/Core/Validator.php"]];
    }
    return \CartQuoteWooCommerce\Core\Validator::validate_critical_files();
}

// Test activation
function test_activation_with_validation() {
    $validation = test_validator_validate_critical_files();
    if (!$validation['valid']) {
        return false;
    }
    return true;
}

// Test deactivation
function test_deactivation_with_validation() {
    return true;
}

// Hooks
register_activation_hook(__FILE__, 'test_activation_with_validation');
register_deactivation_hook(__FILE__, 'test_deactivation_with_validation');