<?php

namespace Woodplane\Theme\Package;

/**
 * Ordering posts and search results
 */

class Ordering
{

	public function run()
	{
		add_action( 'pre_get_posts', [$this, 'searchResultOrder'] );
		add_filter( 'pre_get_posts', [$this, 'orderPosts'], 99 );
	}

	public function searchResultOrder( $query ) {
		// not an admin page and is the main query
		if ( !is_admin() && $query->is_main_query() ) {
			if ( is_search() ) {
				$query->set( 'orderby', 'date' );
			}
		}
	}

	function orderPosts(){
		if (is_search() || is_tax() || is_category()) {

			if(isset($_GET['order'])) {
				$order = $_GET['order'];
			} else {
				$order = 'DESC';
			}

			if( isset($_GET['popular'])){
				set_query_var('orderby', 'meta_value_num');
				set_query_var('meta_key', '_post_like_count');
				set_query_var('order', $order);
			}else {
				set_query_var('orderby', 'date');
				set_query_var('order', $order);
			}
		}
	}
}
