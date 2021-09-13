<?php
namespace SeptemberDigital\Wordpress\Meilisearch;

use GuzzleHttp\Client as GuzzleHttpClient;

class Client extends \MeiliSearch\Client
{

	public function __construct(){
		$options = Settings::getOptions();

		if (!isset($options['hostname']) || !isset($options['master_key'])) {
			return false;
		}

		if (empty($options['hostname'])) {
			return false;
		}

		$options = apply_filters('meilisearch/client_options', $options);

		try {
			parent::__construct($options['hostname'] . ':' . $options['port'], $options['master_key'], new GuzzleHttpClient(['timeout' => 2]));
		} catch (\Exception $e) {
			return false;
		}

		return $this;
	}

	public static function indexName(){
		$options = Settings::getOptions();

		return apply_filters('meilisearch/index_name', $options['index']);
	}


	public static function getIndexInstance($name = null){

		$client = new Client();

		$indexName = $name ?? static::indexName();
		$index = $client->getOrCreateIndex($indexName);

		return $index;

	}

}