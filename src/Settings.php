<?php

namespace SeptemberDigital\Wordpress\Meilisearch;

class Settings
{

	public static function getOptions()
	{
		$options = get_option('wordpress_meilisearch_plugin_options', []);
		return apply_filters('meilisearch/options', $options);
	}

	public static function relevantPostTypes() {
		$options = static::getOptions();
		$types = array_keys(array_filter($options["types"] ?? []));

		return apply_filters('meilisearch/post_types', $types);
	}
}