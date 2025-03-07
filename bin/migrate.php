#!/usr/bin/env php
<?php
/**
 * Database Migration Script
 *
 * This script handles database migrations for the Dallas Embroidery Designer plugin.
 * It creates and updates necessary database tables and options.
 */

// Ensure running from CLI
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Load WordPress
$wp_load = dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die('WordPress not found. Please run this script from the plugin directory.');
}
require_once $wp_load;

// Ensure user has permission
if (!current_user_can('manage_options')) {
    die('You do not have sufficient permissions to run migrations.');
}

class DallasDesignerMigration {
    private $version;
    private $migrations = [];

    public function __construct() {
        $this->version = get_option('dallas_designer_db_version', '0.0.0');
        $this->register_migrations();
    }

    private function register_migrations() {
        // Register all migrations here
        $this->migrations = [
            '1.0.0' => [
                'up' => function() {
                    global $wpdb;

                    // Create saved designs table
                    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dallas_saved_designs (
                        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        user_id bigint(20) unsigned NOT NULL,
                        product_id bigint(20) unsigned NOT NULL,
                        title varchar(255) NOT NULL,
                        design_data longtext NOT NULL,
                        mockup_url varchar(255),
                        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        KEY user_id (user_id),
                        KEY product_id (product_id)
                    ) {$wpdb->get_charset_collate()};";

                    // Create RFQ table
                    $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dallas_rfqs (
                        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        user_id bigint(20) unsigned NOT NULL,
                        product_id bigint(20) unsigned NOT NULL,
                        design_id bigint(20) unsigned NOT NULL,
                        quantity int unsigned NOT NULL,
                        notes text,
                        status varchar(50) NOT NULL DEFAULT 'pending',
                        quote_amount decimal(10,2),
                        quote_expiry datetime,
                        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        KEY user_id (user_id),
                        KEY product_id (product_id),
                        KEY design_id (design_id)
                    ) {$wpdb->get_charset_collate()};";

                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    dbDelta($sql);

                    // Add custom post types
                    register_post_type('dallas_saved_design', [
                        'public' => false,
                        'has_archive' => false
                    ]);

                    register_post_type('dallas_rfq', [
                        'public' => false,
                        'has_archive' => false
                    ]);

                    // Add capabilities
                    $role = get_role('administrator');
                    $role->add_cap('manage_dallas_designs');
                    $role->add_cap('manage_dallas_rfqs');

                    flush_rewrite_rules();
                },
                'down' => function() {
                    global $wpdb;

                    // Drop tables
                    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dallas_saved_designs");
                    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dallas_rfqs");

                    // Remove post types
                    unregister_post_type('dallas_saved_design');
                    unregister_post_type('dallas_rfq');

                    // Remove capabilities
                    $role = get_role('administrator');
                    $role->remove_cap('manage_dallas_designs');
                    $role->remove_cap('manage_dallas_rfqs');

                    flush_rewrite_rules();
                }
            ]
        ];
    }

    public function migrate($target = null) {
        if (!$target) {
            $target = array_key_last($this->migrations);
        }

        if (version_compare($this->version, $target, '=')) {
            echo "Already at version {$target}\n";
            return;
        }

        if (version_compare($this->version, $target, '>')) {
            $this->rollback($target);
        } else {
            $this->upgrade($target);
        }
    }

    private function upgrade($target) {
        foreach ($this->migrations as $version => $migration) {
            if (version_compare($version, $this->version, '>') &&
                version_compare($version, $target, '<=')) {
                echo "Migrating to {$version}...\n";

                try {
                    $migration['up']();
                    update_option('dallas_designer_db_version', $version);
                    $this->version = $version;
                    echo "Successfully migrated to {$version}\n";
                } catch (Exception $e) {
                    echo "Error migrating to {$version}: {$e->getMessage()}\n";
                    return;
                }
            }
        }
    }

    private function rollback($target) {
        $versions = array_reverse(array_keys($this->migrations));

        foreach ($versions as $version) {
            if (version_compare($version, $this->version, '<=') &&
                version_compare($version, $target, '>')) {
                echo "Rolling back {$version}...\n";

                try {
                    $this->migrations[$version]['down']();
                    $prev_version = $this->get_previous_version($version);
                    update_option('dallas_designer_db_version', $prev_version);
                    $this->version = $prev_version;
                    echo "Successfully rolled back {$version}\n";
                } catch (Exception $e) {
                    echo "Error rolling back {$version}: {$e->getMessage()}\n";
                    return;
                }
            }
        }
    }

    private function get_previous_version($version) {
        $versions = array_keys($this->migrations);
        $index = array_search($version, $versions);
        return $index > 0 ? $versions[$index - 1] : '0.0.0';
    }

    public function status() {
        echo "Current version: {$this->version}\n";
        echo "Available migrations:\n";

        foreach ($this->migrations as $version => $migration) {
            $status = version_compare($this->version, $version, '>=') ? 'installed' : 'pending';
            echo "- {$version}: {$status}\n";
        }
    }
}

// Parse command line arguments
$action = isset($argv[1]) ? $argv[1] : 'status';
$target = isset($argv[2]) ? $argv[2] : null;

$migration = new DallasDesignerMigration();

switch ($action) {
    case 'migrate':
        $migration->migrate($target);
        break;
    case 'status':
        $migration->status();
        break;
    default:
        echo "Unknown action: {$action}\n";
        echo "Usage: php migrate.php [action] [target]\n";
        echo "Actions:\n";
        echo "  migrate [version] - Migrate to specific version (or latest if not specified)\n";
        echo "  status           - Show migration status\n";
        break;
}
