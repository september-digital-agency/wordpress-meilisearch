<?php

use MeiliSearch\Client;

add_action('wp_ajax_wordpress_meilisearch_debug', 'debug_meilisearch_handler');

function debug_meilisearch_handler()
{
	$client = new Client('http://127.0.0.1:7700', 'grantees');

	$index = $client->getIndex('wordpress_meilisearch_posts');
	$hits = $index->search($_GET['q'])->getRaw();

	dd($hits);
	wp_die();
}
