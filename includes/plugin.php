<?php
/**
 * Core plugin functionality.
 *
 * @package WordpressMeilisearchPlugin
 */

/**
 * Default setup routine
 *
 * @return void
 */
function wordpress_meilisearch_setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_scripts' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_styles' ) );

	// Editor styles. add_editor_style() doesn't work outside of a theme.
	// add_filter( 'mce_css', $n( 'mce_css' ) );
	// Hook to allow async or defer on asset loading.
	add_filter( 'script_loader_tag', $n( 'script_loader_tag' ), 10, 2 );

	do_action( 'wordpress-meilisearch_plugin_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wordpress-meilisearch-plugin' );
	load_textdomain( 'wordpress-meilisearch-plugin', WP_LANG_DIR . '/wordpress-meilisearch-plugin/wordpress-meilisearch-plugin-' . $locale . '.mo' );
	load_plugin_textdomain( 'wordpress-meilisearch-plugin', false, plugin_basename( WP_MELLISEARCH_PLUGIN_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {
	do_action( 'wordpress-meilisearch_plugin_init' );
}

/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {
}

/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
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
function script_url( $script, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
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
function style_url( $stylesheet, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WordpressMeilisearchPlugin stylesheet loader.' );
	}

	return WP_MELLISEARCH_PLUGIN_URL . "dist/css/${stylesheet}.css";

}

/**
 * Enqueue scripts for front-end.
 *
 * @return void
 */

function scripts() {

	wp_enqueue_script(
		'wordpress-meilisearch_plugin_admin',
		script_url('admin', 'admin'),
		[],
		WP_MELLISEARCH_PLUGIN_VERSION,
		true
	);

}

/**
 * Enqueue scripts for admin.
 *
 * @return void
 */
function admin_scripts() {

	wp_enqueue_script(
		'wordpress-meilisearch_plugin_admin',
		script_url( 'wordpress-meilisearch-admin', 'admin' ),
		[],
		WP_MELLISEARCH_PLUGIN_VERSION,
		true
	);

}

/**
 * Enqueue styles for admin.
 *
 * @return void
 */
function admin_styles() {

	wp_enqueue_style(
		'wordpress-meilisearch_plugin_admin',
		style_url( 'wordpress-meilisearch-admin', 'admin' ),
		[],
		WP_MELLISEARCH_PLUGIN_VERSION
	);

}

/**
 * Enqueue editor styles. Filters the comma-delimited list of stylesheets to load in TinyMCE.
 *
 * @param string $stylesheets Comma-delimited list of stylesheets.
 * @return string
 */
function mce_css( $stylesheets ) {
	if ( ! empty( $stylesheets ) ) {
		$stylesheets .= ',';
	}

	return $stylesheets . WP_MELLISEARCH_PLUGIN_URL . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
			'assets/css/frontend/editor-style.css' :
			'dist/css/editor-style.min.css' );
}

/**
 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
 *
 * @link https://core.trac.wordpress.org/ticket/12009
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string
 */
function script_loader_tag( $tag, $handle ) {
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

/**
 * Add settings
 */

function wordpress_meilisearch_register_settings()
{
	register_setting('wordpress_meilisearch_plugin_options', 'wordpress_meilisearch_plugin_options', 'wordpress_meilisearch_plugin_options_validate');

	add_settings_section('server_settings', '', 'wordpress_meilisearch_plugin_section_text', 'wordpress_meilisearch_plugin');
	add_settings_field('wordpress_meilisearch_plugin_setting_hostname', 'Hostname', 'wordpress_meilisearch_plugin_setting_hostname', 'wordpress_meilisearch_plugin', 'server_settings');
	add_settings_field('wordpress_meilisearch_plugin_setting_port', 'Port', 'wordpress_meilisearch_plugin_setting_port', 'wordpress_meilisearch_plugin', 'server_settings');
	add_settings_field('wordpress_meilisearch_plugin_setting_master_key', 'Master Key', 'wordpress_meilisearch_plugin_setting_master_key', 'wordpress_meilisearch_plugin', 'server_settings');

	add_settings_section('index_settings', '', 'wordpress_meilisearch_plugin_section_text', 'wordpress_meilisearch_plugin');
	add_settings_field('wordpress_meilisearch_plugin_setting_index', 'Name', 'wordpress_meilisearch_plugin_setting_index', 'wordpress_meilisearch_plugin', 'index_settings');
	add_settings_field('wordpress_meilisearch_plugin_setting_types', 'Types', 'wordpress_meilisearch_plugin_setting_types', 'wordpress_meilisearch_plugin', 'index_settings');
}
add_action('admin_init', 'wordpress_meilisearch_register_settings');

function wordpress_meilisearch_plugin_section_text(): string
{
	return '';
}

function wordpress_meilisearch_plugin_setting_hostname()
{
	$options = get_option('wordpress_meilisearch_plugin_options');
	echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_hostname" name="wordpress_meilisearch_plugin_options[hostname]" type="text" value="' . esc_attr($options["hostname"] ?? "") . '" />';
}

function wordpress_meilisearch_plugin_setting_port()
{
	$options = get_option('wordpress_meilisearch_plugin_options');
	echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_port" name="wordpress_meilisearch_plugin_options[port]" type="text" value="' . esc_attr($options["port"] ?? "") . '" />';
}

function wordpress_meilisearch_plugin_setting_master_key()
{
	$options = get_option('wordpress_meilisearch_plugin_options');
	echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_master_key" name="wordpress_meilisearch_plugin_options[master_key]" type="text" value="' . esc_attr($options["master_key"] ?? "") . '" />';
}

function wordpress_meilisearch_plugin_setting_index()
{
	$options = get_option('wordpress_meilisearch_plugin_options');
	echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_index" name="wordpress_meilisearch_plugin_options[index]" type="text" value="' . esc_attr($options["index"] ?? "") . '" />';
}

function wordpress_meilisearch_plugin_setting_types()
{
	$options = get_option('wordpress_meilisearch_plugin_options');
	$post_types = get_post_types(["public" => true]);

	foreach ($post_types as $post_type) {
		$checked = isset($options["types"][$post_type])
			? "checked" : "";
		echo '<div class="wrap"><input name="wordpress_meilisearch_plugin_options[types][' . $post_type . ']" id="' . $post_type . '" type="checkbox" ' . $checked . '><label for="' . $post_type . '">' . $post_type . '</label></div>';
	}
}

/**
 * Add admin pages
 */

function wordpress_meilisearch_add_admin_menu_pages()
{
	add_menu_page(
		__('Search', 'wordpress-meilisearch'),
		__('Search', 'wordpress-meilisearch'),
		'manage_options',
		'wordpress-meilisearch',
		'wordpress_meilisearch_settings',
		'dashicons-search'
	);
}
add_action('admin_menu', 'wordpress_meilisearch_add_admin_menu_pages');

function wordpress_meilisearch_settings()
{
	$pluginDirPath = plugin_dir_path(__FILE__);
	include($pluginDirPath . '../views/meilisearch-settings.php');
}
