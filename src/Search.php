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

		if (is_admin() && !wp_doing_ajax()) {
			return;
		}

		$search = $query->query_vars['s'];

		if (empty($search)) {
			return;
		}

		$post_types = (array)$query->query_vars['post_type'];

		if(in_array('any', $post_types)){
			$post_types = array_filter($post_types, function($input){
				return $input != 'any';
			});
			foreach(get_post_types() as $post_type){
				$post_types[] = $post_type;
			}
		}

		//if there are unsupported post types queried, bail
		if(count(array_diff($post_types, Settings::relevantPostTypes())) > 0){
			return;
		}

		$index = Client::getIndexInstance();
		if(!$index){
			return;
		}

		$result = $index->search($search);
		$hits = $result->getHits();

		$hitLookup = array_reduce($hits, function($carry, $hit){
			$carry[$hit['ID']] = $hit;
			return $carry;
		}, []);


		$ids = array_keys($hitLookup);

		array_unshift($ids, -1);
		$idsString = implode(",", $ids);

		$search = " AND ID IN (".$idsString.") ";

		add_filter('posts_search_orderby', function() use ($idsString) {
			return ' FIELD(ID,' . $idsString.') ';
		});

		add_filter('posts_results', function($posts) use ( $hitLookup ) {
			return array_map(function($post) use ( $hitLookup ) {
				$post->meilisearch = $hitLookup[$post->ID];
				return $post;
			}, $posts);
		});

		return $search;
	}
}
