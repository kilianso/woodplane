<?php

namespace Woodplane\Theme\Package;

/**
 * Jetpack Stuff
 */
class Jetpack
{

	public function run()
	{
		add_action( 'after_setup_theme', [$this, 'setup'] );
	}

	/**
	 * Jetpack setup function.
	 *
	 * See: https://jetpack.com/support/infinite-scroll/
	 * See: https://jetpack.com/support/responsive-videos/
	 * See: https://jetpack.com/support/content-options/
	 */
	public function setup() {
		// Add theme support for Infinite Scroll.
		add_theme_support( 'infinite-scroll', array(
			'container' => 'main',
			'render'    => $this->infiniteScrollRender(),
			'footer'    => 'page',
		) );

		// Add theme support for Responsive Videos.
		add_theme_support( 'jetpack-responsive-videos' );

		// Add theme support for Content Options.
		add_theme_support( 'jetpack-content-options', array(
			'post-details'    => array(
				'stylesheet' => 'wdpln-style',
				'date'       => '.posted-on',
				'categories' => '.cat-links',
				'tags'       => '.tags-links',
				'author'     => '.byline',
				'comment'    => '.comments-link',
			),
			'featured-images' => array(
				'archive'    => true,
				'post'       => true,
				'page'       => true,
			),
		) );
	}

	/**
	 * Custom render function for Infinite Scroll.
	 */
	public function infiniteScrollRender() {
		while ( have_posts() ) {
			the_post();
			if ( is_search() ) :
				get_template_part( 'source/views/content', 'search' );
			else :
				get_template_part( 'source/views/content', get_post_type() );
			endif;
		}
	}

}
