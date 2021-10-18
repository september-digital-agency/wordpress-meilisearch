<?php
namespace SeptemberDigital\Wordpress\Meilisearch;

use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;

class Client extends \MeiliSearch\Client
{

	public function __construct(){
		$options = Settings::getOptions();

		if (defined('MEILISEARCH_HOSTNAME') && !empty(MEILISEARCH_HOSTNAME)) {
			$options['hostname'] = MEILISEARCH_HOSTNAME;
		}
		if (defined('MEILISEARCH_PORT') && !empty(MEILISEARCH_PORT)) {
			$options['port'] = MEILISEARCH_PORT;
		}
		if (defined('MEILISEARCH_MASTERKEY') && !empty(MEILISEARCH_MASTERKEY)) {
			$options['master_key'] = MEILISEARCH_MASTERKEY;
		}

		if (!isset($options['hostname']) || !isset($options['master_key'])) {
			return false;
		}

		if (empty($options['hostname'])) {
			return false;
		}

		$options = apply_filters('meilisearch/client_options', $options);

		//TODO: Betere error handeling
		try {
			parent::__construct($options['hostname'] . ':' . $options['port'], $options['master_key'], new GuzzleHttpClient(['timeout' => 2]));
		} catch (\Exception $e) {
			return false;
		}

		return $this;
	}

	public static function indexName(){
		$options = Settings::getOptions();

		if (defined('MEILISEARCH_INDEX') && !empty(MEILISEARCH_INDEX)) {
			$options['index'] = MEILISEARCH_INDEX;
		}

		if (!isset($options['index'])) {
			$options['index'] = '';
		}

		return apply_filters('meilisearch/index_name', $options['index']);
	}


	public static function getIndexInstance($name = null){

		$client = new Client();

		$indexName = $name ?? static::indexName();

		if (!empty($indexName)) {
			try {
				$index = $client->getOrCreateIndex($indexName);
				return $index;
			}catch(\Exception $e){
				error_log("Error while trying to get meilisearch index instance");
				return null;
			}
		}
	}

}