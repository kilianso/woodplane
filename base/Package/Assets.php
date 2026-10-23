<?php

namespace Woodplane\Theme\Package;

/**
 * Assets (CSS, JavaScript etc)
 */
class Assets
{
	public $font_version = '1.0';

	public function run()
	{
		add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
		add_action('admin_enqueue_scripts', [$this, 'registerAdminAssets']);
		add_action('admin_init', [$this, 'editorStyle']);
		add_filter('script_loader_tag', [$this, 'moduleScriptTag'], 10, 2);

		// Vite already ships minified files and loads its chunks relative to the entry file.
		// SiteGround Optimizer must not minify/combine (and thereby relocate) the theme assets.
		if (defined('SiteGround_Optimizer\VERSION')) {
			add_filter('sgo_js_minify_exclude', [$this, 'excludeThemeScripts']);
			add_filter('sgo_javascript_combine_exclude', [$this, 'excludeThemeScripts']);
			add_filter('sgo_css_minify_exclude', [$this, 'excludeThemeStyles']);
			add_filter('sgo_css_combine_exclude', [$this, 'excludeThemeStyles']);
		}
	}

	public function registerAssets()
	{
		// CSS

		$deps = [];

		// $deps = ['wp-block-library'];
		wp_enqueue_style(wdpln_theme()->prefix . '-style', get_template_directory_uri() . '/build/styles/ui' . (wdpln_theme()->debug ? '' : '.min') . '.css', $deps, filemtime(get_template_directory() . '/build/styles/ui' . (wdpln_theme()->debug ? '' : '.min') . '.css'));

		// JavaScript

		// wp_enqueue_script('jquery', get_template_directory_uri() . '/build/scripts/jquery-3.2.1.min.js', [], '3.2.1', false);
		// $deps[] = 'jquery';
		wp_enqueue_script(wdpln_theme()->prefix . '-script', get_template_directory_uri() . '/build/scripts/ui' . (wdpln_theme()->debug ? '' : '.min') . '.js', $deps, filemtime(get_template_directory() . '/build/scripts/ui' . (wdpln_theme()->debug ? '' : '.min') . '.js'), true);
	}

	public function registerAdminAssets()
	{
		// CSS
		$deps = ['wp-edit-blocks'];
		wp_enqueue_style(wdpln_theme()->prefix . '-admin-editor-style', get_template_directory_uri() . '/build/styles/admin-editor' . (wdpln_theme()->debug ? '' : '.min') . '.css', $deps, filemtime(get_template_directory() . '/build/styles/admin-editor' . (wdpln_theme()->debug ? '' : '.min') . '.css'));

		$deps[] = wdpln_theme()->prefix . '-admin-editor-style';
		wp_enqueue_style(wdpln_theme()->prefix . '-admin-style', get_template_directory_uri() . '/build/styles/admin' . (wdpln_theme()->debug ? '' : '.min') . '.css', $deps, filemtime(get_template_directory() . '/build/styles/admin' . (wdpln_theme()->debug ? '' : '.min') . '.css'));

		// Javascript
		$deps = [];
		wp_enqueue_script(wdpln_theme()->prefix . '-admin-script', get_template_directory_uri() . '/build/scripts/admin' . (wdpln_theme()->debug ? '' : '.min') . '.js', $deps, filemtime(get_template_directory() . '/build/scripts/admin' . (wdpln_theme()->debug ? '' : '.min') . '.js'), true);
	}

	public function editorStyle()
	{
		if (file_exists(get_template_directory() . '/build/styles/admin-editor' . (wdpln_theme()->debug ? '' : '.min') . '.css')) {
			add_editor_style(get_template_directory_uri() . '/build/styles/admin-editor' . (wdpln_theme()->debug ? '' : '.min') . '.css');
		}
	}

	/**
	 * Vite outputs ES modules: dynamic imports resolve their chunks via import.meta.url,
	 * which is a syntax error in classic scripts.
	 */
	public function moduleScriptTag($tag, $handle)
	{
		if (!in_array($handle, $this->scriptHandles(), true)) {
			return $tag;
		}
		$tag = preg_replace('/\s+type\s*=\s*([\'"]).*?\1/i', '', $tag);
		return preg_replace('/<script\b/i', '<script type="module"', $tag, 1);
	}

	public function excludeThemeScripts($handles)
	{
		return array_merge((array) $handles, $this->scriptHandles());
	}

	public function excludeThemeStyles($handles)
	{
		$prefix = wdpln_theme()->prefix;
		return array_merge((array) $handles, [$prefix . '-style', $prefix . '-admin-editor-style', $prefix . '-admin-style']);
	}

	private function scriptHandles()
	{
		$prefix = wdpln_theme()->prefix;
		return [$prefix . '-script', $prefix . '-admin-script', $prefix . '-customizer'];
	}
}
