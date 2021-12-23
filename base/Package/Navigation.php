<?php

namespace Woodplane\Theme\Package;

/**
 * Everything to do with menus and site navigation
 */
class Navigation
{

	private $menus;

	public function __construct()
	{
		$this->menus = [
			'menu-1' => esc_html__( 'Primary', 'wdpln' ),
		];
	}

	public function run()
	{
		if (count($this->menus)) {
			add_action('after_setup_theme', [$this, 'themeSupport']);
		}
	}

	public function themeSupport()
	{
		add_theme_support('menu');
		register_nav_menus($this->menus);
	}
}
