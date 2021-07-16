<?php

/**
 * Plugin Name:       Wordpress Meilisearch
 * Plugin URI:        https://github.com/septemberdigital/wordpress-meilisearch
 * Description:       A developers plugin to search (custom) posts with Meilisearch.
 * Version:           0.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            September Digital
 * Author URI:        https://september.digital
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wordpress-meilisearch
 * Domain Path:       /languages
 *
 * @package           WordpressMeilisearchS
 */

// Useful global constants.
define( 'WP_MELLISEARCH_PLUGIN_VERSION', '0.1.0' );
define( 'WP_MELLISEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_MELLISEARCH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_MELLISEARCH_PLUGIN_INC', WP_MELLISEARCH_PLUGIN_PATH . 'includes/' );

// Require Composer autoloader if it exists.
if ( file_exists( WP_MELLISEARCH_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once WP_MELLISEARCH_PLUGIN_PATH . 'vendor/autoload.php';
}

// Include files.
require_once WP_MELLISEARCH_PLUGIN_INC . '/plugin.php';
require_once WP_MELLISEARCH_PLUGIN_INC . '/core.php';
require_once WP_MELLISEARCH_PLUGIN_INC . '/results.php';

// Activation/Deactivation.
register_activation_hook( __FILE__, '\WordpressMeilisearchPlugin\Core\activate' );
register_deactivation_hook( __FILE__, '\WordpressMeilisearchPlugin\Core\deactivate' );

// Bootstrap.
wordpress_meilisearch_setup();
