<?php

namespace SeptemberDigital\Wordpress\Meilisearch;

use WP_Post;
use WP_Query;

class Indexer
{

	public static function init(){
		add_action('save_post', [static::class, 'onSavePost'], 10, 3);
		add_action('delete_post', [static::class, 'onDeletePost'], 10, 3);

		add_action('admin_post_clearindex', [static::class, 'ajaxClear']);
		add_action('admin_post_reindex', [static::class, 'ajaxReindex']);
	}


	/**
	 * wordpress_meilisearch_document_from_post
	 * @param  [type] $post
	 * @return array [type]
	 */
	public static function prepare($post): array
	{
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

		$document['meta'] = get_post_meta($post->ID, '', true);

		$document = apply_filters('meilisearch/parsed_document', $document, $post);

		return $document;
	}



	/**
	 * wordpress_meilisearch_update_post.
	 *
	 * @param  [type]  $id
	 * @param  WP_Post $post
	 * @param  [type]  $update
	 * @return [type]
	 */
	public static function onSavePost($id, WP_Post $post, $update)
	{

		if (!in_array(get_post_type($post), Settings::relevantPostTypes()) || wp_is_post_revision($id) || wp_is_post_autosave($id)) {
			return $post;
		}

		$document = static::prepare($post);

		if (!$document) {
			return $post;
		}

		$index = Client::getIndexInstance();

		$result = $index->addDocuments([
			$document
		]);

		if ('publish' !== $post->post_status) {
			$index->deleteDocument($document['ID']);
		}

		return $post;
	}





	/**
	 * wordpress_meilisearch_delete_post
	 *
	 * @param  [type]  $id
	 * @param  WP_Post $post
	 * @param  [type]  $update
	 * @return [type]
	 */
	public static function onDeletePost($id, WP_Post $post)
	{

		if (!in_array(get_post_type($post), Settings::relevantPostTypes()) || wp_is_post_revision($id) || wp_is_post_autosave($id)) {
			return $post;
		}

		$document = static::prepare($post);

		if (!$document) {
			return $post;
		}

		$index = Client::getIndexInstance();
		$index->deleteDocument($document['ID']);

		return $post;
	}

	/**
	 * wordpress_meilisearch_re_index
	 * @param  string $index
	 * @return void
	 */
	public static function index($name = null)
	{
		$index = Client::getIndexInstance($name);

		$paged = 1;

		do {
			$posts = new WP_Query([
				'posts_per_page' => 50,
				'paged' => $paged,
				'post_type' => Settings::relevantPostTypes(),
				'post_status' => 'publish',
				'suppress_filters' => true,
			]);

			if (!$posts->have_posts()) {
				break;
			}

			$documents = [];
			foreach ($posts->posts as $post) {
				$document = static::prepare($post);
				if (!$document) {
					continue;
				}

				$documents[] = $document;
			}

			$result = $index->addDocuments($documents);

			$paged++;
		} while (true);
	}

	public static function ajaxReindex()
	{
		static::clear($_REQUEST["index"]);
		static::index($_REQUEST["index"]);

		wp_redirect($_SERVER["HTTP_REFERER"], 302, 'WordPress');
	}

	/**
	 * wordpress_meilisearch_clear_index.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public static function clear(string $name = null): bool
	{
		Client::getIndexInstance($name)->deleteAllDocuments();
		return true;
	}

	public static function ajaxClear()
	{
		static::clear($_REQUEST["index"]);

		wp_redirect($_SERVER["HTTP_REFERER"], 302, 'WordPress');
	}

}