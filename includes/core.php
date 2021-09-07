<?php

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * wordpress_meilisearch_get_client.
 *
 * @return \MeiliSearch\Client
 */
function wordpress_meilisearch_get_client()
{
    $options = get_option('wordpress_meilisearch_plugin_options');
    if (!isset($options['hostname']) || !isset($options['master_key'])) {
		return false;
    }

    if (empty($options['hostname'])) {
		return false;
    }

	try {
        $client = new \MeiliSearch\Client($options['hostname'] . ':' . $options['port'], $options['master_key'], new GuzzleHttpClient(['timeout' => 2]));
		return $client;
	} catch (\Exception $e) {
		return false;
	}
}


/**
 * wordpress_meilisearch_get_index.
 *
 * @param \MeiliSearch\Client $client
 * @return index
 */
function wordpress_meilisearch_get_index()
{
	try {
		$client = wordpress_meilisearch_get_client();
	} catch (\Exception $e) {
		return false;
	}

	if (!$client) {
		return false;
	}

	$options = get_option('wordpress_meilisearch_plugin_options');

	if (!isset($options['index']) || empty($options['index'])) {
		return false;
	}

	try {
		return $client->getOrCreateIndex($options['index']);
	} catch (\Exception $e) {
		return false;
	}

}

function wordpress_meilisearch_get_types() {

	$options = get_option('wordpress_meilisearch_plugin_options');
	$types = isset($options["types"]) ? $options["types"] : null;
	return array_keys($types);

}

/**
 * wordpress_meilisearch_update_post.
 *
 * @param  [type]  $id
 * @param  WP_Post $post
 * @param  [type]  $update
 * @return [type]
 */
function wordpress_meilisearch_update_post($id, WP_Post $post, $update)
{

	if (!in_array(get_post_type($post), wordpress_meilisearch_get_types()) || wp_is_post_revision($id) || wp_is_post_autosave($id)) {
		return $post;
	}

	$document = wordpress_meilisearch_document_from_post($post);

	if (!$document) {
		return $post;
	}

	try {
		$client = wordpress_meilisearch_get_client();
	} catch (\Exception $e) {
		return false;
	}

	if (!$client) {
		return $post;
	}

	$index = $client->getOrCreateIndex('wordpress_meilisearch_posts');

	$result = $index->addDocuments([
		$document
	]);

	if ('publish' !== $post->post_status) {
		$index->deleteDocument($document['ID']);
	}

	return $post;
}

add_action('save_post', 'wordpress_meilisearch_update_post', 10, 3);

/**
 * wordpress_meilisearch_document_from_post
 * @param  [type] $post
 * @return [type]
 */
function wordpress_meilisearch_document_from_post($post)
{
	// if (get_post_status($post->ID) !== 'publish') {
	// 	return false;
	// }

	$post = get_post($post->ID);

	$document = [
		'ID' => $post->ID,
		'title' => $post->post_title,
		'permalink' => get_permalink($post->ID),
		'slug' => $post->post_name,
		'type' => $post->post_type,
		'content' => $post->post_content
	];

	if ($featuredImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail')) {
		$document['featured_image'] = [
			'url' => $featuredImage[0],
			'width' => $featuredImage[1],
			'height' => $featuredImage[2],
		];
	}

    if (class_exists('ACF')) {
        $fields = get_fields($post->ID);
        $fields ? $document['acf'] = array($fields) : "";
    }

	return $document;
}

/**
 * wordpress_meilisearch_delete_post
 *
 * @param  [type]  $id
 * @param  WP_Post $post
 * @param  [type]  $update
 * @return [type]
 */
function wordpress_meilisearch_delete_post($id, WP_Post $post)
{

	if (!in_array(get_post_type($post), wordpress_meilisearch_get_types()) || wp_is_post_revision($id) || wp_is_post_autosave($id)) {
		return $post;
	}

	$document = wordpress_meilisearch_document_from_post($post);

	if (!$document) {
		return $post;
	}

	$client = wordpress_meilisearch_get_client();
	if (!$client) {
		return $post;
	}

	$index = $client->getIndex('wordpress_meilisearch_posts');
	$index->deleteDocument($document['ID']);

	return $post;
}
add_action('delete_post', 'wordpress_meilisearch_delete_post', 10, 3);

/**
 * wordpress_meilisearch_re_index
 * @param  string $index
 * @return void
 */
function wordpress_meilisearch_re_index($index)
{
	$client = wordpress_meilisearch_get_client();
	$client->getIndex($index)->deleteAllDocuments();

	$paged = 1;

	do {
		$posts = new WP_Query([
			'posts_per_page' => 10,
			'paged' => $paged,
			'post_type' => wordpress_meilisearch_get_types(),
			'post_status' => 'publish',
			'suppress_filters' => true,
		]);

		if (!$posts->have_posts()) {
			break;
		}

		$documents = [];
		foreach ($posts->posts as $post) {
			$document = wordpress_meilisearch_document_from_post($post);
			if (!$document) {
				continue;
			}

			$documents[] = $document;
		}

		$result = $client->getIndex($index)->addDocuments($documents);

		$paged++;
	} while (true);
}

function wordpress_meilisearch_reindex()
{
	wordpress_meilisearch_clear_index($_REQUEST["index"]);
	wordpress_meilisearch_re_index($_REQUEST["index"]);

	wp_redirect($_SERVER["HTTP_REFERER"], 302, 'WordPress');

}
add_action('admin_post_reindex', 'wordpress_meilisearch_reindex');

/**
 * wordpress_meilisearch_clear_index.
 *
 * @param  string $name
 * @return bool
 */
function wordpress_meilisearch_clear_index(string $name): bool
{
	$client = wordpress_meilisearch_get_client();
	$client->getIndex($name)->deleteAllDocuments();

	return true;
}

function wordpress_meilisearch_clearindex()
{
	wordpress_meilisearch_clear_index($_REQUEST["index"]);

	wp_redirect($_SERVER["HTTP_REFERER"], 302, 'WordPress');
}
add_action('admin_post_clearindex', 'wordpress_meilisearch_clearindex');

function stats()
{
	$index = wordpress_meilisearch_get_index();

	$stats = $index->stats();

	wp_send_json($stats);
	wp_die();

}
add_action('wp_ajax_stats', 'stats');
add_action('wp_ajax_nopriv_stats', 'stats');
