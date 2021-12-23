<?php

namespace Woodplane\Theme\Package;

/**
 * Sidebar stuff
 */
class Sidebars
{

	private $sidebars;

	public function __construct()
	{
		$this->sidebars = [
			[
				'name'          => esc_html__( 'Sidebar', 'wdpln' ),
				'id'            => 'sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'wdpln' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			],
		];
	}

	public function run()
	{
		if (count($this->sidebars)) {
			add_action('after_setup_theme', [$this, 'themeSupport']);
			add_action('widgets_init', [$this, 'register']);
		}
	}

	public function themeSupport()
	{
		add_theme_support('sidebars');
	}

	public function register()
	{
		foreach ($this->sidebars as $sidebar) {
			register_sidebar($sidebar);
		}
	}
}
