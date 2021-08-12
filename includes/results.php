<?php

use MeiliSearch\Client;

function search()
{
	$client = new Client('http://127.0.0.1:7700', 'grantees');

	$index = $client->getIndex('wordpress_meilisearch_posts');
	$hits = $index->search($_GET['q'])->getRaw();

	dd($hits);
	wp_die();
}
add_action('wp_ajax_search', 'search');
add_action( 'wp_ajax_nopriv_search', 'search');