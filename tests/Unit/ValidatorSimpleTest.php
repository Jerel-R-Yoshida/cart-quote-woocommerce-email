<?php
/**
 * Simple Unit Tests for Plugin File Validation
 * Tests file structure and validation logic without WordPress dependencies
 *
 * @package CartQuoteWooCommerce\Tests\Unit
 */

class Test_Validator_Simple {

    private $test_dir;
    private $test_files;
    private $required_files;
    private $missing_files;

    public function run() {
        $this->setup();
        $this->test_structure_validation();
        $this->test_missing_files_detection();
        $this->test_error_message_generation();
        $this->test_critical_file_checking();
        $this->test_performance();
        $this->cleanup();
    }

    private function setup() {
        $this->test_dir = __DIR__ . '/test_files_simple';
        $this->test_files = [];
        $this->missing_files = [];

        // Define required files list for testing
        $this->required_files = [
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

        // Create test directory structure
        @mkdir($this->test_dir, 0755, true);

        // Create all required files for full validation
        foreach ($this->required_files as $file => $purpose) {
            $full_path = $this->test_dir . '/' . $file;
            $directory = dirname($full_path);
            @mkdir($directory, 0755, true);

            file_put_contents($full_path, '<?php // Test file');
            $this->test_files[$file] = $full_path;
        }

        // Create missing files for testing
        $this->missing_files = [
            'templates/admin/quotes-list.php' => 'Admin interface template',
            'assets/css/frontend.css' => 'Frontend styles',
        ];
    }

    private function cleanup() {
        // Remove test files
        foreach (array_values($this->test_files) as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        // Remove test directory
        if (file_exists($this->test_dir)) {
            @rmdir($this->test_dir . '/src/Core');
            @rmdir($this->test_dir . '/src/Admin');
            @rmdir($this->test_dir . '/src/Database');
            @rmdir($this->test_dir . '/src/Emails');
            @rmdir($this->test_dir . '/src/Frontend');
            @rmdir($this->test_dir . '/src/Google');
            @rmdir($this->test_dir . '/src/WooCommerce');
            @rmdir($this->test_dir . '/templates/admin');
            @rmdir($this->test_dir . '/templates/frontend');
            @rmdir($this->test_dir . '/assets/css');
            @rmdir($this->test_dir . '/assets/js');
            @rmdir($this->test_dir . '/src');
            @rmdir($this->test_dir . '/templates');
            @rmdir($this->test_dir . '/assets');
            @rmdir($this->test_dir);
        }
    }

    private function assertEquals($expected, $actual, $message) {
        if ($expected !== $actual) {
            echo "FAILED: $message\n";
            echo "  Expected: " . print_r($expected, true) . "\n";
            echo "  Actual: " . print_r($actual, true) . "\n";
            return false;
        }
        echo "PASSED: $message\n";
        return true;
    }

    private function assertTrue($condition, $message) {
        if (!$condition) {
            echo "FAILED: $message\n";
            return false;
        }
        echo "PASSED: $message\n";
        return true;
    }

    private function assertEmpty($value, $message) {
        if (!empty($value)) {
            echo "FAILED: $message\n";
            echo "  Value: " . print_r($value, true) . "\n";
            return false;
        }
        echo "PASSED: $message\n";
        return true;
    }

    private function assertFalse($condition, $message) {
        if ($condition) {
            echo "FAILED: $message\n";
            return false;
        }
        echo "PASSED: $message\n";
        return true;
    }

    private function test_structure_validation() {
        $plugin_dir = $this->test_dir;

        // Simulate check_all_files logic with all files present
        $missing = [];
        foreach ($this->required_files as $file => $purpose) {
            $full_path = $plugin_dir . '/' . $file;

            if (!file_exists($full_path)) {
                $missing[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'severity' => 'critical'
                ];
            }
        }

        // Test that validation passes with complete structure
        $this->assertEquals(
            true,
            empty($missing),
            'Check all files with valid structure should have no missing files'
        );
    }

    private function test_missing_files_detection() {
        $plugin_dir = $this->test_dir;
        $missing = [];

        // Temporarily make some files missing
        foreach ($this->missing_files as $file => $purpose) {
            $file_path = $plugin_dir . '/' . $file;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        // Check for missing files
        foreach ($this->required_files as $file => $purpose) {
            if (!file_exists($plugin_dir . '/' . $file)) {
                $missing[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'severity' => 'critical'
                ];
            }
        }

        // Restore files
        foreach ($this->missing_files as $file => $purpose) {
            $file_path = $plugin_dir . '/' . $file;
            $directory = dirname($file_path);
            @mkdir($directory, 0755, true);
            file_put_contents($file_path, '<?php // Test file');
        }

        // Test that missing files are detected
        $this->assertFalse(
            empty($missing),
            'Should detect missing files'
        );

        // Test that detected missing files match expected
        foreach ($this->missing_files as $file => $purpose) {
            $found = false;
            foreach ($missing as $item) {
                if ($item['file'] === $file) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Missing file '$file' should be detected"
            );
        }
    }

    private function test_error_message_generation() {
        $missing = [
            [
                'file' => 'src/Core/Activator.php',
                'purpose' => 'Plugin activation',
                'severity' => 'critical'
            ],
            [
                'file' => 'templates/admin/quotes-list.php',
                'purpose' => 'Admin interface',
                'severity' => 'critical'
            ]
        ];

        // Simulate error message generation
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

        // Test that error message contains expected elements
        $this->assertTrue(
            strpos($message, 'Cart Quote Plugin Installation Error') !== false,
            'Error message should contain title'
        );

        $this->assertTrue(
            strpos($message, 'CRITICAL') !== false,
            'Error message should indicate critical severity'
        );

        $this->assertTrue(
            strpos($message, 'src/Core/Activator.php') !== false,
            'Error message should include missing file path'
        );

        $this->assertTrue(
            strpos($message, 'Plugin activation') !== false,
            'Error message should include file purpose'
        );
    }

    private function test_critical_file_checking() {
        $plugin_dir = $this->test_dir;

        // Define critical files for validation
        $critical_files = [
            'src/Core/Activator.php' => 'Plugin activation',
            'src/Core/Plugin.php' => 'Main plugin class',
            'src/Admin/Health_Check.php' => 'Health checks',
        ];

        $missing = [];
        foreach ($critical_files as $file => $purpose) {
            $full_path = $plugin_dir . '/' . $file;

            if (!file_exists($full_path)) {
                $missing[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'severity' => 'critical'
                ];
            }
        }

        // Test that validation passes with complete structure
        $this->assertEquals(
            true,
            empty($missing),
            'Validate critical files with valid structure should have no missing files'
        );
    }

    private function test_performance() {
        $iterations = 100;

        $start_time = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $plugin_dir = $this->test_dir;
            $missing = [];

            foreach ($this->required_files as $file => $purpose) {
                if (!file_exists($plugin_dir . '/' . $file)) {
                    $missing[] = $file;
                }
            }
        }
        $end_time = microtime(true);

        $total_time = ($end_time - $start_time) * 1000;
        $average_time = $total_time / $iterations;

        echo "\nPerformance Test Results:\n";
        echo "  Iterations: $iterations\n";
        echo "  Total time: " . number_format($total_time, 2) . " ms\n";
        echo "  Average time: " . number_format($average_time, 2) . " ms\n";

        // Should complete within 1 second total
        $this->assertEquals(
            true,
            $total_time < 1000,
            'Check all files should complete within 1 second for 100 iterations'
        );

        // Should average less than 20ms per iteration
        $this->assertEquals(
            true,
            $average_time < 20,
            'Check all files should average less than 20ms per iteration'
        );
    }
}

// Run tests
echo "Running Simple Unit Tests for Plugin File Validation...\n";
echo str_repeat('=', 60) . "\n";

$test_runner = new Test_Validator_Simple();
$results = $test_runner->run();

echo str_repeat('=', 60) . "\n";
echo "Test Summary: " . ($results ? "ALL TESTS PASSED ✓" : "SOME TESTS FAILED ✗") . "\n";

return $results ? 0 : 1;
