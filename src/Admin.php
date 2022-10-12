<?php

namespace SeptemberDigital\Wordpress\Meilisearch;

class Admin
{
	public static function init(){
		add_action( 'admin_enqueue_scripts', [static::class, 'scripts']);
		add_action( 'admin_enqueue_scripts', [static::class, 'styles']);

		add_action('admin_menu', [static::class, 'addAdminMenuPages']);
		add_action('admin_init', [static::class, 'registerSettings']);

	}


	/**
	 * Enqueue scripts for admin.
	 *
	 * @return void
	 */
	public static function scripts() {

		wp_enqueue_script(
			'wordpress-meilisearch_plugin_admin',
			Plugin::script_url( 'wordpress-meilisearch-admin', 'admin' ),
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
	public static function styles() {

		wp_enqueue_style(
			'wordpress-meilisearch_plugin_admin',
			Plugin::style_url( 'wordpress-meilisearch-admin', 'admin' ),
			[],
			WP_MELLISEARCH_PLUGIN_VERSION
		);

	}





	/**
	 * Add settings
	 */

	public static function registerSettings()
	{
		register_setting('wordpress_meilisearch_plugin_options', 'wordpress_meilisearch_plugin_options', 'wordpress_meilisearch_plugin_options_validate');

		add_settings_section('server_settings', '', [static::class, 'sectionText'], 'wordpress_meilisearch_plugin');

		$overriddenText = '<br> (overridden by constant)';

		$hostname_settinglabel = 'Hostname*';
		if (defined('MEILISEARCH_HOSTNAME') && !empty(MEILISEARCH_HOSTNAME)) {
			$hostname_settinglabel .= $overriddenText;
		}
		add_settings_field('wordpress_meilisearch_plugin_setting_hostname', $hostname_settinglabel , [static::class, 'settingHostname'], 'wordpress_meilisearch_plugin', 'server_settings');

		$port_settinglabel = 'Port*';
		if (defined('MEILISEARCH_PORT') && !empty(MEILISEARCH_PORT)) {
			$port_settinglabel .= $overriddenText;
		}
		add_settings_field('wordpress_meilisearch_plugin_setting_port', 'Port*', [static::class, 'settingPort'], 'wordpress_meilisearch_plugin', 'server_settings');

		$masterkey_settinglabel = 'Master Key';
		if (defined('MEILISEARCH_MASTERKEY') && !empty(MEILISEARCH_MASTERKEY)) {
			$masterkey_settinglabel .= $overriddenText;
		}
		add_settings_field('wordpress_meilisearch_plugin_setting_master_key', 'Master Key', [static::class, 'settingMasterKey'], 'wordpress_meilisearch_plugin', 'server_settings');

		add_settings_section('index_settings', '', [static::class, 'sectionText'], 'wordpress_meilisearch_plugin');

		$index_settinglabel = 'Index name*';
		if (defined('MEILISEARCH_INDEX') && !empty(MEILISEARCH_INDEX)) {
			$index_settinglabel .= $overriddenText;
		}
		add_settings_field('wordpress_meilisearch_plugin_setting_index', $index_settinglabel, [static::class, 'settingIndex'], 'wordpress_meilisearch_plugin', 'index_settings');

		add_settings_field('wordpress_meilisearch_plugin_setting_types', 'Types', [static::class, 'settingTypes'], 'wordpress_meilisearch_plugin', 'index_settings');
	}


	public static function sectionText(): string
	{
		return '';
	}

	public static function settingHostname()
	{
		$options = Settings::getOptions();
		echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_hostname" name="wordpress_meilisearch_plugin_options[hostname]" type="text" value="' . esc_attr($options["hostname"] ?? "") . '" required />';
	}

	public static function settingPort()
	{
		$options = Settings::getOptions();
		echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_port" name="wordpress_meilisearch_plugin_options[port]" type="text" value="' . esc_attr($options["port"] ?? "") . '" required />';
	}

	public static function settingMasterKey()
	{
		$options = Settings::getOptions();
		echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_master_key" name="wordpress_meilisearch_plugin_options[master_key]" type="text" value="' . esc_attr($options["master_key"] ?? "") . '" />';
	}

	public static function settingIndex()
	{
		$options = Settings::getOptions();
		echo '<input class="regular-text" id="wordpress_meilisearch_plugin_setting_index" name="wordpress_meilisearch_plugin_options[index]" type="text" value="' . esc_attr($options["index"] ?? "") . '" required />';
	}

	public static function settingTypes()
	{
		$options = Settings::getOptions();
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

	public static function addAdminMenuPages()
	{
		add_menu_page(
			__('Search', 'wordpress-meilisearch'),
			__('Search', 'wordpress-meilisearch'),
			'manage_options',
			'wordpress-meilisearch',
			[static::class, 'renderSettingsPage'],
			'dashicons-search'
		);
	}

	public static function ajaxActionUrl($action, $params = []){
		$params['action'] = $action;
		return admin_url('admin-ajax.php?'.http_build_query($params));
	}

	public static function renderSettingsPage()
	{
		$pluginDirPath = WP_MELLISEARCH_PLUGIN_PATH;
		$index = Client::getIndexInstance();

		$statsUrl = static::ajaxActionUrl('meilisearch_stats');

		include($pluginDirPath . 'views/meilisearch-settings.php');
	}


}
