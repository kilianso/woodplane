<?php

namespace Woodplane\Theme\Package;

/**
 * Customizer Stuff
 */
class Customizer
{

	public function run()
	{
		add_action( 'customize_register', [$this, 'customizeRegister'] );
		add_action( 'customize_preview_init', [$this, 'customizePreviewJs'] );
	}

	/**
	 * Add postMessage support for site title and description for the Theme Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function customizeRegister( $wp_customize ) {
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial( 'blogname', array(
				'selector'        => '.site-title a',
				'render_callback' => $this->customizePartialBlogname(),
			) );
			$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
				'selector'        => '.site-description',
				'render_callback' => $this->customizePartialBlogDescription(),
			) );
		}
	}

	/**
	 * Render the site title for the selective refresh partial.
	 *
	 * @return void
	 */
	function customizePartialBlogname() {
		bloginfo( 'name' );
	}

	/**
	 * Render the site tagline for the selective refresh partial.
	 *
	 * @return void
	 */
	function customizePartialBlogDescription() {
		bloginfo( 'description' );
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	function customizePreviewJs() {
		// Javascript
		$deps = ['customize-preview'];
		wp_enqueue_script(wdpln_theme()->prefix . '-customizer', get_template_directory_uri() . '/assets/scripts/customizer' . (wdpln_theme()->debug ? '' : '.min') . '.js', $deps, filemtime(get_template_directory() . '/assets/scripts/customizer' . (wdpln_theme()->debug ? '' : '.min') . '.js'), true);
	}
}
