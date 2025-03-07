<?php
/**
 * Base test case for Dallas Designer tests
 */

namespace DallasEmbroidery\DesignLab\Tests;

use WP_UnitTestCase;
use WC_Unit_Test_Case;

abstract class TestCase extends WC_Unit_Test_Case {
    /**
     * Debug instance
     */
    protected $debug;

    /**
     * Test product ID
     */
    protected $product_id;

    /**
     * Test user ID
     */
    protected $user_id;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();

        // Initialize debug instance
        $this->debug = new \Dallas_Designer_Debug();

        // Get test data
        $this->product_id = $GLOBALS['test_product_id'];
        $this->user_id = $GLOBALS['test_user_id'];

        // Set up test environment
        $this->setup_test_environment();
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Clean up test environment
        $this->cleanup_test_environment();

        parent::tearDown();
    }

    /**
     * Set up test environment
     */
    protected function setup_test_environment() {
        // Set up WooCommerce session
        $session = array();
        WC()->session = new \WC_Mock_Session_Handler();

        // Set up WooCommerce cart
        WC()->cart = new \WC_Cart();

        // Set up WooCommerce customer
        WC()->customer = new \WC_Customer($this->user_id);

        // Set current user
        wp_set_current_user($this->user_id);
    }

    /**
     * Clean up test environment
     */
    protected function cleanup_test_environment() {
        // Reset WooCommerce session
        WC()->session = null;

        // Reset WooCommerce cart
        WC()->cart = null;

        // Reset WooCommerce customer
        WC()->customer = null;

        // Reset current user
        wp_set_current_user(0);
    }

    /**
     * Create a test design
     */
    protected function create_test_design($data = array()) {
        $default_data = array(
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'design_data' => array(
                'objects' => array(
                    array(
                        'type' => 'text',
                        'text' => 'Test Design'
                    )
                )
            ),
            'mockup_image' => 'data:image/png;base64,test'
        );

        $data = wp_parse_args($data, $default_data);

        $design_id = wp_insert_post(array(
            'post_type' => 'dallas_saved_design',
            'post_status' => 'publish',
            'post_author' => $data['user_id'],
            'post_title' => 'Test Design'
        ));

        update_post_meta($design_id, '_design_data', $data['design_data']);
        update_post_meta($design_id, '_product_id', $data['product_id']);
        update_post_meta($design_id, '_mockup_image', $data['mockup_image']);

        return $design_id;
    }

    /**
     * Create a test RFQ
     */
    protected function create_test_rfq($data = array()) {
        $default_data = array(
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'design_data' => array(
                'objects' => array(
                    array(
                        'type' => 'text',
                        'text' => 'Test RFQ'
                    )
                )
            ),
            'quantity' => 10,
            'notes' => 'Test notes'
        );

        $data = wp_parse_args($data, $default_data);

        $rfq_id = wp_insert_post(array(
            'post_type' => 'dallas_rfq',
            'post_status' => 'publish',
            'post_author' => $data['user_id'],
            'post_title' => 'Test RFQ'
        ));

        update_post_meta($rfq_id, '_design_data', $data['design_data']);
        update_post_meta($rfq_id, '_product_id', $data['product_id']);
        update_post_meta($rfq_id, '_quantity', $data['quantity']);
        update_post_meta($rfq_id, '_notes', $data['notes']);
        update_post_meta($rfq_id, '_status', 'pending');

        return $rfq_id;
    }

    /**
     * Assert API response
     */
    protected function assertAPIResponse($response, $status_code = 200) {
        $this->assertInstanceOf('WP_REST_Response', $response);
        $this->assertEquals($status_code, $response->get_status());
        $this->assertNotEmpty($response->get_data());
    }

    /**
     * Assert API error
     */
    protected function assertAPIError($response, $code, $status_code = 400) {
        $this->assertInstanceOf('WP_Error', $response);
        $this->assertEquals($code, $response->get_error_code());
        $this->assertEquals($status_code, $response->get_error_data()['status']);
    }

    /**
     * Assert email sent
     */
    protected function assertEmailSent($to, $subject = null) {
        $this->assertArrayHasKey('test_email', $GLOBALS);
        $email = $GLOBALS['test_email'];

        $this->assertEquals($to, $email['to']);
        if ($subject) {
            $this->assertEquals($subject, $email['subject']);
        }
    }

    /**
     * Assert HTTP request made
     */
    protected function assertHTTPRequest($url, $method = 'GET') {
        $this->assertNotEmpty($GLOBALS['http_requests']);

        $request = end($GLOBALS['http_requests']);
        $this->assertEquals($url, $request['url']);

        if (isset($request['args']['method'])) {
            $this->assertEquals($method, $request['args']['method']);
        }
    }

    /**
     * Get test file path
     */
    protected function getTestFile($filename) {
        return DALLAS_DESIGNER_TEST_DIR . '/fixtures/' . $filename;
    }

    /**
     * Get test file contents
     */
    protected function getTestFileContents($filename) {
        return file_get_contents($this->getTestFile($filename));
    }

    /**
     * Create test image
     */
    protected function createTestImage() {
        $upload_dir = wp_upload_dir();
        $filename = 'test-image.png';
        $filepath = $upload_dir['path'] . '/' . $filename;

        // Create a test image
        $image = imagecreatetruecolor(100, 100);
        imagepng($image, $filepath);
        imagedestroy($image);

        return array(
            'file' => $filepath,
            'url' => $upload_dir['url'] . '/' . $filename,
            'type' => 'image/png'
        );
    }
}
