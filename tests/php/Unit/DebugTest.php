<?php
namespace DallasEmbroidery\DesignLab\Tests\Unit;

use DallasEmbroidery\DesignLab\Tests\TestCase;

class DebugTest extends TestCase {
    /**
     * Test debug logging
     */
    public function test_debug_logging() {
        // Get upload directory
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/dallas-designer-debug.log';

        // Clear existing log
        if (file_exists($log_file)) {
            unlink($log_file);
        }

        // Create test message
        $test_message = 'Test debug message';
        $test_context = ['test_key' => 'test_value'];

        // Log message
        $this->debug->log($test_message, $test_context, 'info');

        // Verify log file exists
        $this->assertFileExists($log_file);

        // Read log content
        $log_content = file_get_contents($log_file);

        // Verify log content
        $this->assertStringContainsString($test_message, $log_content);
        $this->assertStringContainsString('test_key', $log_content);
        $this->assertStringContainsString('test_value', $log_content);
        $this->assertStringContainsString('INFO', $log_content);
    }

    /**
     * Test error handling
     */
    public function test_error_handling() {
        // Set error handler
        set_error_handler(array($this->debug, 'handle_error'));

        // Trigger test error
        @trigger_error('Test error message', E_USER_WARNING);

        // Restore error handler
        restore_error_handler();

        // Get log content
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/dallas-designer-debug.log';
        $log_content = file_get_contents($log_file);

        // Verify error was logged
        $this->assertStringContainsString('Test error message', $log_content);
        $this->assertStringContainsString('User Warning', $log_content);
    }

    /**
     * Test exception handling
     */
    public function test_exception_handling() {
        // Set exception handler
        set_exception_handler(array($this->debug, 'handle_exception'));

        // Create test exception
        $exception = new \Exception('Test exception message');

        // Handle exception
        $this->debug->handle_exception($exception);

        // Restore exception handler
        restore_exception_handler();

        // Get log content
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/dallas-designer-debug.log';
        $log_content = file_get_contents($log_file);

        // Verify exception was logged
        $this->assertStringContainsString('Test exception message', $log_content);
        $this->assertStringContainsString('Exception', $log_content);
    }

    /**
     * Test debug info display
     */
    public function test_debug_info_display() {
        // Set current user as admin
        $this->setRole('administrator');

        // Enable debug mode
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }

        // Capture output
        ob_start();
        $this->debug->display_debug_info();
        $output = ob_get_clean();

        // Verify debug info content
        $this->assertStringContainsString('Debug Information', $output);
        $this->assertStringContainsString('Memory Usage', $output);
        $this->assertStringContainsString('Database Queries', $output);
        $this->assertStringContainsString('PHP Version', $output);
        $this->assertStringContainsString('WordPress Version', $output);
    }

    /**
     * Test debug log page
     */
    public function test_debug_log_page() {
        // Set current user as admin
        $this->setRole('administrator');

        // Create test log content
        $test_log = "Test log entry\n";
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/dallas-designer-debug.log';
        file_put_contents($log_file, $test_log);

        // Capture output
        ob_start();
        $this->debug->render_debug_page();
        $output = ob_get_clean();

        // Verify debug page content
        $this->assertStringContainsString('Debug Log', $output);
        $this->assertStringContainsString('Test log entry', $output);
        $this->assertStringContainsString('Clear Log', $output);
        $this->assertStringContainsString('Download Log', $output);
    }

    /**
     * Test log actions
     */
    public function test_log_actions() {
        // Set current user as admin
        $this->setRole('administrator');

        // Create test log content
        $test_log = "Test log entry\n";
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/dallas-designer-debug.log';
        file_put_contents($log_file, $test_log);

        // Test clear log action
        $_GET['action'] = 'clear';
        $_GET['_wpnonce'] = wp_create_nonce('clear_debug_log');

        // Capture output to prevent headers already sent warning
        ob_start();
        $this->debug->handle_log_actions();
        ob_end_clean();

        // Verify log was cleared
        $this->assertFileExists($log_file);
        $this->assertEquals('', file_get_contents($log_file));
    }

    /**
     * Helper method to set user role
     */
    private function setRole($role) {
        wp_set_current_user($this->user_id);
        $user = wp_get_current_user();
        $user->set_role($role);
    }
}
