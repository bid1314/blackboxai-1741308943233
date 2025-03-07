<?php
/**
 * PHPUnit bootstrap file
 */

// Load Composer autoloader
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

// Load WordPress test environment
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit(1);
}

// Load WordPress test environment functions
require_once $_tests_dir . '/includes/functions.php';

// Load plugin functions
function _manually_load_plugin() {
    // Load WooCommerce first
    require dirname(dirname(dirname(__DIR__))) . '/woocommerce/woocommerce.php';

    // Load our plugin
    require dirname(dirname(__DIR__)) . '/dallas-embroidery-designer.php';
}

// Load plugins
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Load test case base class
require_once dirname(__DIR__) . '/php/TestCase.php';

// Initialize debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

// Initialize test environment constants
define('DALLAS_DESIGNER_TEST_MODE', true);
define('DALLAS_DESIGNER_TEST_DIR', __DIR__);

// Set up test database
global $wpdb;
$wpdb->show_errors();
$wpdb->suppress_errors(false);

// Clean up test database
function _clean_up_test_db() {
    global $wpdb;

    // Delete test posts
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type IN ('dallas_saved_design', 'dallas_rfq')");

    // Delete test postmeta
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_dallas_designer%'");

    // Delete test options
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'dallas_designer%'");
}
add_action('shutdown', '_clean_up_test_db');

// Set up test email
add_filter('wp_mail', function($args) {
    $GLOBALS['test_email'] = $args;
    return $args;
});

// Mock external API calls
function mock_remote_request($response, $args, $url) {
    // Store the request for assertion
    $GLOBALS['http_requests'][] = array(
        'url' => $url,
        'args' => $args
    );

    // Return mock response
    return array(
        'response' => array('code' => 200),
        'body' => json_encode($response)
    );
}

// Initialize test data
function _init_test_data() {
    // Create test product
    $product_id = wp_insert_post(array(
        'post_title' => 'Test Product',
        'post_type' => 'product',
        'post_status' => 'publish'
    ));

    update_post_meta($product_id, '_regular_price', '19.99');
    wp_set_object_terms($product_id, 'simple', 'product_type');

    // Create test user
    $user_id = wp_create_user('testuser', 'testpass', 'test@example.com');
    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => 'Test',
        'last_name' => 'User'
    ));

    // Store IDs for tests
    $GLOBALS['test_product_id'] = $product_id;
    $GLOBALS['test_user_id'] = $user_id;
}
add_action('plugins_loaded', '_init_test_data');

// Initialize test hooks
function _init_test_hooks() {
    // Add test hooks here
    do_action('dallas_designer_test_init');
}
add_action('init', '_init_test_hooks');

// Set up test environment
function _setup_test_env() {
    // Clear any cached data
    wp_cache_flush();

    // Reset post data
    $GLOBALS['post'] = null;

    // Reset current user
    wp_set_current_user(0);

    // Reset test globals
    $GLOBALS['http_requests'] = array();
    unset($GLOBALS['test_email']);
}
add_action('setup_theme', '_setup_test_env');
