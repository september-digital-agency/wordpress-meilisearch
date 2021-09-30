<?php

namespace SeptemberDigital\Wordpress\Meilisearch;

use WP_Error;

class Plugin
{

	public static function setup(){
		$n = function( $function ) {
			return [static::class, $function];
		};

		add_action( 'init', $n( 'i18n' ) );
		add_action( 'init', $n( 'init' ) );

		add_filter( 'script_loader_tag', $n( 'script_loader_tag' ), 10, 2 );


		add_action('wp_ajax_stats', $n('ajaxStats'));
		add_action('wp_ajax_nopriv_stats', $n('ajaxStats'));

		do_action( 'meilisearch/plugin_loaded' );

	}


	/**
	 * Registers the default textdomain.
	 *
	 * @return void
	 */
	public static function i18n() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wordpress-meilisearch-plugin' );
		load_textdomain( 'wordpress-meilisearch-plugin', WP_LANG_DIR . '/wordpress-meilisearch-plugin/wordpress-meilisearch-plugin-' . $locale . '.mo' );
		load_plugin_textdomain( 'wordpress-meilisearch-plugin', false, plugin_basename( WP_MELLISEARCH_PLUGIN_PATH ) . '/languages/' );
	}

	/**
	 * Initializes the plugin and fires an action other plugins can hook into.
	 *
	 * @return void
	 */
	public static function init() {
		do_action( 'meilisearch/plugin_init' );
	}

	/**
	 * Activate the plugin
	 *
	 * @return void
	 */
	public static function activate() {
		// First load the init scripts in case any rewrite functionality is being loaded
		self::init();
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 *
	 * Uninstall routines should be in uninstall.php
	 *
	 * @return void
	 */
	public static function deactivate() {
	}

	/**
	 * The list of knows contexts for enqueuing scripts/styles.
	 *
	 * @return array
	 */
	public static function get_enqueue_contexts() {
		return [ 'admin' ];
	}

	/**
	 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $script Script file name (no .js extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 *
	 * @return string|WP_Error URL
	 */
	public static function script_url( $script, $context ) {

		if ( ! in_array( $context, static::get_enqueue_contexts(), true ) ) {
			return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WordpressMeilisearchPlugin script loader.' );
		}

		return WP_MELLISEARCH_PLUGIN_URL . "dist/js/${script}.js";

	}

	/**
	 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $stylesheet Stylesheet file name (no .css extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 *
	 * @return string URL
	 */
	public static function style_url( $stylesheet, $context ) {

		if ( ! in_array( $context, static::get_enqueue_contexts(), true ) ) {
			return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WordpressMeilisearchPlugin stylesheet loader.' );
		}

		return WP_MELLISEARCH_PLUGIN_URL . "dist/css/${stylesheet}.css";

	}

	/**
	 * Enqueue scripts for front-end.
	 *
	 * @return void
	 */

	public static function scripts() {

		wp_enqueue_script(
			'wordpress-meilisearch_plugin_admin',
			static::script_url('admin', 'admin'),
			[],
			WP_MELLISEARCH_PLUGIN_VERSION,
			true
		);

	}


	/**
	 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12009
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @return string
	 */
	public static function script_loader_tag( $tag, $handle ) {
		$script_execution = wp_scripts()->get_data( $handle, 'script_execution' );

		if ( ! $script_execution ) {
			return $tag;
		}

		if ( 'async' !== $script_execution && 'defer' !== $script_execution ) {
			return $tag; // _doing_it_wrong()?
		}

		// Abort adding async/defer for scripts that have this script as a dependency. _doing_it_wrong()?
		foreach ( wp_scripts()->registered as $script ) {
			if ( in_array( $handle, $script->deps, true ) ) {
				return $tag;
			}
		}

		// Add the attribute if it hasn't already been added.
		if ( ! preg_match( ":\s$script_execution(=|>|\s):", $tag ) ) {
			$tag = preg_replace( ':(?=></script>):', " $script_execution", $tag, 1 );
		}

		return $tag;
	}



	public static function ajaxStats()
	{
		$index = Client::getIndexInstance();

		$stats = $index->stats();

		wp_send_json($stats);
		wp_die();
	}

}