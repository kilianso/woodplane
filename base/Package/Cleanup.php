<?php

namespace Woodplane\Theme\Package;

class Cleanup
{
	public function __construct() {

	}

	public function run()
	{


		// remove emoji support
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');

		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );

		// remove rss feed links
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );

		// remove wp-embed
		add_action( 'wp_footer', function(){
			wp_dequeue_script( 'wp-embed' );
		});

		add_action( 'wp_enqueue_scripts', function(){
			// remove jQuery
			wp_deregister_script('jquery');
			// // remove block library css
			wp_dequeue_style( 'wp-block-library' );
			// // remove comment reply JS
			wp_dequeue_script( 'comment-reply' );
		} );

		// Do the stuff below in wp-config.php to keep things clean

		// define( 'CORE_UPGRADE_SKIP_NEW_BUNDLED', true );
		// define('WP_POST_REVISIONS', false);
		// define('WP_POST_REVISIONS', 5);
	}

}
