<?php
/**
 * Unit Tests for Validator Class
 * Tests file validation and error handling logic
 *
 * @package CartQuoteWooCommerce\Tests\Unit
 */

use CartQuoteWooCommerce\Core\Validator;

class Test_Validator {

    private $test_dir;
    private $test_files;
    private $missing_files;
    private $required_files;

    public function run() {
        $this->setup();
        $this->test_check_all_files_with_valid_structure();
        $this->test_check_all_files_with_missing_files();
        $this->test_check_all_files_with_unreadable_files();
        $this->test_validate_critical_files_with_valid_structure();
        $this->test_validate_critical_files_with_missing_files();
        $this->test_validate_critical_files_missing_directory();
        $this->test_validate_critical_files_missing_file();
        $this->test_generate_error_message();
        $this->test_validate_and_stop_if_missing_with_valid_files();
        $this->test_validate_and_stop_if_missing_with_missing_files();
        $this->test_validate_and_stop_if_missing_with_missing_critical_files();
        $this->test_check_all_files_performance();
        $this->cleanup();
    }

    private function setup() {
        $this->test_dir = __DIR__ . '/test_files';
        $this->test_files = [];
        $this->missing_files = [];

        // Create test directory structure
        @mkdir($this->test_dir, 0755, true);

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

        // Create critical files
        $this->test_files['src/Core/Activator.php'] = $this->test_dir . '/src/Core/Activator.php';
        $this->test_files['src/Core/Plugin.php'] = $this->test_dir . '/src/Core/Plugin.php';
        $this->test_files['src/Admin/Health_Check.php'] = $this->test_dir . '/src/Admin/Health_Check.php';

        // Create non-critical files
        $this->test_files['src/Core/Deactivator.php'] = $this->test_dir . '/src/Core/Deactivator.php';
        $this->test_files['src/Core/Uninstaller.php'] = $this->test_dir . '/src/Core/Uninstaller.php';

        // Create all required files for full validation
        foreach ($this->required_files as $file => $purpose) {
            if (!isset($this->test_files[$file])) {
                $full_path = $this->test_dir . '/' . $file;
                $directory = dirname($full_path);
                @mkdir($directory, 0755, true);

                file_put_contents($full_path, '<?php // Test file');
                $this->test_files[$file] = $full_path;
             }
         }
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
            @rmdir($this->test_dir . '/src');
            @rmdir($this->test_dir);
        }
    }

    private function assert($condition, $message) {
        if (!$condition) {
            echo "FAILED: $message\n";
            return false;
        }
        echo "PASSED: $message\n";
        return true;
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

    private function test_check_all_files_with_valid_structure() {
        $result = Validator::check_all_files();

        $this->assertTrue(
            $result['valid'],
            'Check all files with valid structure should return valid: true'
        );

        $this->assertTrue(
            empty($result['missing']),
            'Check all files with valid structure should have no missing files'
        );

        $this->assertTrue(
            empty($result['errors']),
            'Check all files with valid structure should have no errors'
        );
    }

    private function test_check_all_files_with_missing_files() {
        // Temporarily make some files missing
        foreach ($this->missing_files as $file => $purpose) {
            $file_path = $this->test_files[$file];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        $result = Validator::check_all_files();

        $this->assertEquals(
            false,
            $result['valid'],
            'Check all files with missing files should return valid: false'
        );

        $this->assertEquals(
            true,
            empty($result['missing']) === false,
            'Check all files with missing files should have missing files'
        );

        foreach ($this->missing_files as $file => $purpose) {
            $this->assertTrue(
                isset($result['missing'][$file]),
                "Missing file '$file' should be in missing list"
            );
        }

        // Restore files
        foreach ($this->missing_files as $file => $purpose) {
            $file_path = $this->test_dir . '/' . $file;
            $directory = dirname($file_path);
            @mkdir($directory, 0755, true);
            file_put_contents($file_path, '<?php // Test file');
        }
    }

    private function test_check_all_files_with_unreadable_files() {
        // Make a file unreadable
        $unreadable_file = $this->test_files['templates/admin/quotes-list.php'];
        if (file_exists($unreadable_file)) {
            @chmod($unreadable_file, 0000);
        }

        $result = Validator::check_all_files();

        $this->assertEquals(
            false,
            $result['valid'],
            'Check all files with missing files should return valid: false'
        );

        $this->assertEquals(
            true,
            empty($result['missing']) === false,
            'Check all files with missing files should have missing files'
        );

        $this->assertTrue(
            !empty($result['errors']),
            'Check all files with unreadable files should have errors'
        );

        // Restore file permissions
        if (file_exists($unreadable_file)) {
            @chmod($unreadable_file, 0644);
        }
    }

    private function test_validate_critical_files_with_valid_structure() {
        $result = Validator::validate_critical_files();

        $this->assertTrue(
            $result['valid'],
            'Validate critical files with valid structure should return valid: true'
        );

        $this->assertTrue(
            empty($result['missing']),
            'Validate critical files with valid structure should have no missing files'
        );
    }

    private function test_validate_critical_files_with_missing_files() {
        // Temporarily remove critical file
        $critical_file = $this->test_files['src/Core/Activator.php'];
        if (file_exists($critical_file)) {
            @unlink($critical_file);
        }

        $result = Validator::validate_critical_files();

        $this->assertEquals(
            false,
            $result['valid'],
            'Validate critical files with missing critical file should return valid: false'
        );

        $this->assertTrue(
            !empty($result['missing']),
            'Validate critical files with missing file should have missing files'
        );

        // Restore critical file
        $directory = dirname($critical_file);
        @mkdir($directory, 0755, true);
        file_put_contents($critical_file, '<?php // Test file');
    }

    private function test_validate_critical_files_missing_directory() {
        // Remove entire src directory
        $src_dir = $this->test_dir . '/src';
        if (file_exists($src_dir)) {
            @rmdir($src_dir);
        }

        $result = Validator::validate_critical_files();

        $this->assertEquals(
            false,
            $result['valid'],
            'Validate critical files with missing directory should return valid: false'
        );

        // Restore directory
        @mkdir($src_dir, 0755, true);
        @file_put_contents($src_dir . '/Core/Activator.php', '<?php // Test file');
    }

    private function test_validate_critical_files_missing_file() {
        // Remove only the main plugin file
        $plugin_file = $this->test_dir . '/src/Core/Plugin.php';
        if (file_exists($plugin_file)) {
            @unlink($plugin_file);
        }

        $result = Validator::validate_critical_files();

        $this->assertEquals(
            false,
            $result['valid'],
            'Validate critical files with missing file should return valid: false'
        );

        // Restore file
        $directory = dirname($plugin_file);
        @mkdir($directory, 0755, true);
        file_put_contents($plugin_file, '<?php // Test file');
    }

    private function test_generate_error_message() {
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

        $message = Validator::generate_error_message($missing);

        $this->assertTrue(
            strpos($message, 'Cart Quote Plugin Installation Error') !== false,
            'Error message should contain title'
        );

        $this->assertTrue(
            strpos($message, 'src/Core/Activator.php') !== false,
            'Error message should include missing file path'
        );

        $this->assertTrue(
            strpos($message, 'Plugin activation') !== false,
            'Error message should include file purpose'
        );

        $this->assertTrue(
            strpos($message, 'CRITICAL') !== false,
            'Error message should indicate critical severity'
        );
    }

    private function test_validate_and_stop_if_missing_with_valid_files() {
        $validation_result = Validator::validate_and_stop_if_missing();

        $this->assertTrue(
            $validation_result,
            'Validate and stop if missing with valid files should return true'
        );
    }

    private function test_validate_and_stop_if_missing_with_missing_files() {
        // Make a critical file missing
        $critical_file = $this->test_files['src/Core/Activator.php'];
        if (file_exists($critical_file)) {
            @unlink($critical_file);
        }

        // This should output error and return false
        $validation_result = Validator::validate_and_stop_if_missing();

        $this->assertEquals(
            false,
            $validation_result,
            'Validate and stop if missing with missing files should return false'
        );

        // Restore critical file
        $directory = dirname($critical_file);
        @mkdir($directory, 0755, true);
        file_put_contents($critical_file, '<?php // Test file');
    }

    private function test_validate_and_stop_if_missing_with_missing_critical_files() {
        // Remove all critical files
        foreach (['src/Core/Activator.php', 'src/Core/Plugin.php', 'src/Admin/Health_Check.php'] as $file) {
            $file_path = $this->test_files[$file];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        // This should output error and return false
        $validation_result = Validator::validate_and_stop_if_missing();

        $this->assertEquals(
            false,
            $validation_result,
            'Validate and stop if missing with all critical files missing should return false'
        );

        // Restore critical files
        foreach (['src/Core/Activator.php', 'src/Core/Plugin.php', 'src/Admin/Health_Check.php'] as $file) {
            $file_path = $this->test_files[$file];
            $directory = dirname($file_path);
            @mkdir($directory, 0755, true);
            file_put_contents($file_path, '<?php // Test file');
        }
    }

    private function test_check_all_files_performance() {
        $iterations = 100;

        $start_time = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Validator::check_all_files();
        }
        $end_time = microtime(true);

        $total_time = ($end_time - $start_time) * 1000;
        $average_time = $total_time / $iterations;

        echo "\nPerformance Test Results:\n";
        echo "  Iterations: $iterations\n";
        echo "  Total time: " . number_format($total_time, 2) . " ms\n";
        echo "  Average time: " . number_format($average_time, 2) . " ms\n";

        // Should complete within 1 second total
        $this->assertTrue(
            $total_time < 1000,
            'Check all files should complete within 1 second for 100 iterations'
        );

        // Should average less than 20ms per iteration
        $this->assertTrue(
            $average_time < 20,
            'Check all files should average less than 20ms per iteration'
        );
    }
}

// Run tests
echo "Running Unit Tests for Validator Class...\n";
echo str_repeat('=', 60) . "\n";

$test_runner = new Test_Validator();
$results = $test_runner->run();

echo str_repeat('=', 60) . "\n";
echo "Test Summary: " . ($results ? "ALL TESTS PASSED ✓" : "SOME TESTS FAILED ✗") . "\n";

return $results ? 0 : 1;
