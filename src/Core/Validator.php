<?php
/**
 * Plugin Validation System
 * Validates required files exist before activation
 *
 * @package CartQuoteWooCommerce\Core
 * @since 1.0.15
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

/**
 * Class Validator
 */
class Validator
{
    /**
     * Critical files required for plugin operation
     *
     * @var array
     */
    private const REQUIRED_FILES = [
        'src/Core/Activator.php' => 'Plugin activation',
        'src/Core/Deactivator.php' => 'Plugin deactivation',
        'src/Core/Plugin.php' => 'Main plugin class',
        'src/Core/Uninstaller.php' => 'Plugin uninstallation',
        'src/Admin/Settings.php' => 'Admin settings',
        'src/Admin/Health_Check.php' => 'Health checks',
        'src/Database/Quote_Repository.php' => 'Database operations',
        'src/Emails/Email_Service.php' => 'Email functionality',
        'src/Frontend/Frontend_Manager.php' => 'Frontend functionality',
        'src/Google/Google_Calendar_Service.php' => 'Google Calendar integration',
        'src/WooCommerce/Checkout_Replacement.php' => 'WooCommerce integration',
        'templates/admin/quotes-list.php' => 'Admin interface',
        'templates/frontend/quote-form.php' => 'Quote form template',
        'assets/css/frontend.css' => 'Frontend styles',
        'assets/js/frontend.js' => 'Frontend JavaScript',
    ];

    /**
     * Check all required files exist
     *
     * @return array{valid: bool, missing: array, errors: array}
     */
    public static function check_all_files(): array
    {
        $plugin_dir = plugin_dir_path(CART_QUOTE_WC_PLUGIN_FILE);
        $missing = [];
        $errors = [];
        $valid = true;

        foreach (self::REQUIRED_FILES as $file => $purpose) {
            $full_path = $plugin_dir . $file;

            if (!file_exists($full_path)) {
                $valid = false;
                $missing[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'severity' => 'critical'
                ];
            }

            if (!is_readable($full_path)) {
                $errors[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'message' => 'File exists but is not readable',
                    'severity' => 'warning'
                ];
            }
        }

        return [
            'valid' => $valid,
            'missing' => $missing,
            'errors' => $errors
        ];
    }

    /**
     * Validate critical files (used during activation)
     *
     * @return array{valid: bool, missing: array}
     */
    public static function validate_critical_files(): array
    {
        $plugin_dir = plugin_dir_path(CART_QUOTE_WC_PLUGIN_FILE);
        $missing = [];
        $valid = true;

        // Only check critical files needed for activation
        $critical_files = [
            'src/Core/Activator.php' => 'Plugin activation',
            'src/Core/Plugin.php' => 'Main plugin class',
            'src/Admin/Health_Check.php' => 'Health checks',
        ];

        foreach ($critical_files as $file => $purpose) {
            $full_path = $plugin_dir . $file;

            if (!file_exists($full_path)) {
                $valid = false;
                $missing[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'severity' => 'critical'
                ];
            }
        }

        return [
            'valid' => $valid,
            'missing' => $missing
        ];
    }

    /**
     * Generate error message for missing files
     *
     * @param array $missing Missing files data
     * @return string
     */
    public static function generate_error_message(array $missing): string
    {
        $message = "⚠️  Cart Quote Plugin Installation Error\n\n";
        $message .= "CRITICAL: The following required files are missing:\n\n";

        foreach ($missing as $item) {
            $message .= sprintf(
                "  ❌ %s (%s)\n",
                $item['file'],
                $item['purpose']
            );
        }

        $message .= "\nThis indicates either:\n";
        $message .= "  • Incomplete installation\n";
        $message .= "  • Corrupted ZIP file\n";
        $message .= "  • Incorrect plugin directory structure\n\n";
        $message .= "💡 SOLUTION:\n";
        $message .= "  1. Re-download the plugin from GitHub\n";
        $message .= "  2. Extract to wp-content/plugins/ manually\n";
        $message .= "  3. Ensure cart-quote-woocommerce-email folder exists\n";
        $message .= "  4. Check file permissions (should be 644 for files, 755 for directories)\n";
        $message .= "  5. Check file ownership (should match WordPress files)\n";

        return $message;
    }

    /**
     * Validate critical files and stop activation if missing
     *
     * @return bool True if validation passes, false otherwise
     */
    public static function validate_and_stop_if_missing(): bool
    {
        $result = self::validate_critical_files();

        if (!$result['valid']) {
            // Display error and stop activation
            echo self::generate_error_message($result['missing']);
            die('Plugin activation failed. Please check the errors above.');
        }

        return true;
    }
}
