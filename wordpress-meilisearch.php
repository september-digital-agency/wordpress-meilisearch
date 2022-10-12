<?php
/**
 * Plugin Name:       Wordpress Meilisearch
 * Plugin URI:        https://github.com/septemberdigital/wordpress-meilisearch
 * Description:       A developers plugin to search (custom) posts with Meilisearch.
 * Version:           0.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.3
 * Author:            September Digital
 * Author URI:        https://september.digital
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wordpress-meilisearch
 * Domain Path:       /languages
 *
 * @package           WordpressMeilisearchS
 */

use SeptemberDigital\Wordpress\Meilisearch\Admin;
use SeptemberDigital\Wordpress\Meilisearch\Indexer;
use SeptemberDigital\Wordpress\Meilisearch\Plugin;
use SeptemberDigital\Wordpress\Meilisearch\Search;

// Useful global constants.

define( 'WP_MELLISEARCH_PLUGIN_VERSION', '0.4.0' );
define( 'WP_MELLISEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_MELLISEARCH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_MELLISEARCH_PLUGIN_INC', WP_MELLISEARCH_PLUGIN_PATH . 'includes/' );

// Require Composer autoloader if it exists.
if ( file_exists( WP_MELLISEARCH_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once WP_MELLISEARCH_PLUGIN_PATH . 'vendor/autoload.php';
}

// Activation/Deactivation.
register_activation_hook( __FILE__, [Plugin::class, 'activate']);
register_deactivation_hook( __FILE__,  [Plugin::class, 'deactivate']);

// Bootstrap.
Plugin::setup();

Indexer::init();

Search::init();

if(is_admin()){
	Admin::init();
}
