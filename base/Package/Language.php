<?php

namespace Woodplane\Theme\Package;

/**
 * Multilingual stuff and translations
 */
class Language
{

	public function run()
	{
		add_action('after_setup_theme', [$this, 'loadTranslations']);
	}

	/**
	 * Load the translation files which are delivered with the Theme
	 * Other files - stored in wp-content/languages - are loaded automatically.
	 *
	 * @return void
	 */
	public function loadTranslations()
	{
		load_theme_textdomain('sbx', get_template_directory() . '/languages'); // Textdomain Frontend
		load_theme_textdomain('sbx_admin', get_template_directory() . '/languages'); // Textdomain Admin
	}
}
