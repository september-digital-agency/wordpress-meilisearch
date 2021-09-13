<?php

namespace SeptemberDigital\Wordpress\Meilisearch;

class Search
{
	public static function init(){
		add_action('wp_ajax_search', [static::class, 'ajax']);
		add_action('wp_ajax_nopriv_search', [static::class, 'ajax']);

		add_filter('posts_search', [static::class, 'searchHook'], 10, 2);

	}

	public static function ajax(){
		$result = self::searchPosts($_GET['q']);
		wp_send_json_success($result);
	}

	public static function searchPosts($string){

		$index = Client::getIndexInstance();

		$params = apply_filters('meilisearch/search_params', [], $string);
		$options = apply_filters('meilisearch/search_options', [], $string);

		$result = $index->search($string, $params, $options);
		$hits = $result->getHits();

		$ids = array_map(function($hit){
			return $hit['ID'];
		}, $hits);

		$posts = get_posts([
			'post__in' => $ids,
			'post_type' => Settings::relevantPostTypes(),
			'posts_per_page' => -1,
		]);

		$postLookup = array_reduce($posts, function($carry, $post){
			$carry[$post->ID] = $post;
			return $carry;
		}, []);

		return array_map(function($hit) use ($postLookup){
			$post = (array)($postLookup[$hit['ID']]);
			$post['meilisearch'] = $hit;
			return $post;
		}, $hits);

	}

	public static function searchHook($search, \WP_Query $query){

		if(!is_admin() ) {

			$search = $query->query_vars['s'];

			$index = Client::getIndexInstance();
			$result = $index->search($search);
			$hits = $result->getHits();

			$ids = array_map(function($hit){
				return $hit['ID'];
			}, $hits);

			array_unshift($ids, -1);
			$idsString = implode(",", $ids);

			$search = "AND ID IN (".$idsString.")";

			add_filter('posts_search_orderby', function() use ($idsString) {
				return 'FIELD(ID,' . $idsString.')';
			});

		}

		return $search;

	}
}