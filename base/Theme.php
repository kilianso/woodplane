<?php

namespace Woodplane\Theme;
use Timber\Timber;

/**
 * Theme class which gets loaded in functions.php.
 * Defines the Starting point of the Theme and registers Packages.
 */

class Theme
{

	/**
	 * the instance of the object, used for singelton check
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Theme name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Theme version
	 *
	 * @var string
	 */
	public $version = '';

	/**
	 * Theme prefix
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * WP Theme Data
	 *
	 * @var object
	 */
	private $theme;

	/**
	 * Debug mode
	 *
	 * @var bool
	 */
	public $debug = false;

	public function __construct()
	{
		$this->theme = wp_get_theme();

		// Sets the Timber directories to find .twig files
		Timber::$dirname = array( 'templates', 'source/views' );

		// By default, Timber does NOT autoescape values. To enable Twig's autoescape set this to true
		Timber::$autoescape = false;
	}


	public function run()
	{
		$load = [
			Package\Assets::class,
			Package\Comments::class,
			Package\Gutenberg::class,
			Package\Navigation::class,
			Package\Search::class,
			// Package\Sidebars::class, // not needed in this theme
			// Package\Customizer::class, // not wanted to let editors change things via customizer
		];

		if ( ! class_exists( 'Timber' ) ) {

			add_action(
				'admin_notices', function() {
					echo '<div class="error"><p>Timber not found. This theme can\'t work without it.</p></div>';
				}
			);
			
			add_filter(
				'template_include', function( $template ) {
					return get_stylesheet_directory() . '/static/no-timber.html';
				}
			);
			return;
		} else {
			// make custom functions available in twig
			add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		}

		/**
		 * Load Jetpack compatibility file.
		 */
		if ( defined( 'JETPACK__VERSION' ) ) {
			$load[] = Package\Jetpack::class;
		}

		/**
		 * Load WooCommerce compatibility file.
		 */
		if ( class_exists( 'WooCommerce' ) ) {
			$load[] = Package\WooCommerce::class;
		}

		$this->loadClasses($load);

		add_action( 'after_setup_theme', [$this, 'themeSupports'] );
		add_action( 'after_setup_theme', [$this, 'contentWidth'], 0 );

		add_action( 'wp_head', [$this, 'pingbackHeader'] );
		add_filter( 'body_class', [$this, 'bodyClasses'] );

		add_filter('robots_txt', [$this, 'addToRoboText']);
		add_filter('language_attributes', [$this, 'opengraphDoctype']);

		add_action('after_setup_theme', [$this, 'removeAdminBar']);
		add_filter('the_content', [$this, 'externalLinks'], 999);

		// don't hide fields from admin_url
		add_filter( 'is_protected_meta', '__return_false', 999 );

		// no auto wrapping of content into paragraph
		// remove_filter('the_content', 'wpautop');
		remove_filter( 'the_excerpt', 'wpautop' );

		// auto warp link within content
		add_filter( 'the_content', 'make_clickable', 12 );

		add_action( 'init', [$this, 'isFirstTime']);

		add_filter('request', [$this, 'postTypeTagFix']);
		add_filter('next_posts_link_attributes', [$this, 'postLinkAttributes']);
		add_filter('previous_posts_link_attributes', [$this, 'postLinkAttributes']);
		add_filter( 'pre_get_document_title', [$this, 'renameTitle'], 5);
	}

	/**
	 * Creates an instance if one isn't already available,
	 * then return the current instance.
	 *
	 * @return object       The class instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof Theme)) {
			self::$instance = new Theme;

			self::$instance->name    = self::$instance->theme->name;
			self::$instance->version = self::$instance->theme->version;
			self::$instance->prefix  = 'wdpln';
			self::$instance->debug   = true; // debug mode for local development

			if (!isset($_SERVER['HTTP_HOST']) || strpos($_SERVER['HTTP_HOST'], '.local') === false && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
				self::$instance->debug = false; // production mode (.min files will be used)
			}
		}

		return self::$instance;
	}

	/**
	 * Loads and initializes the provided classes.
	 *
	 * @param $classes
	 */

	private function loadClasses($classes)
	{
		foreach ($classes as $class) {
			$class_parts = explode('\\', $class);
			$class_short = end($class_parts);
			$class_set   = $class_parts[count($class_parts) - 2];

			if (!isset(wdpln_theme()->{$class_set}) || !is_object(wdpln_theme()->{$class_set})) {
				wdpln_theme()->{$class_set} = new \stdClass();
			}

			if (property_exists(wdpln_theme()->{$class_set}, $class_short)) {
				wp_die(sprintf(_x('Ein Problem ist geschehen im Theme. Nur eine PHP-Klasse namens «%1$s» darf dem Theme-Objekt «%2$s» zugewiesen werden.', 'Duplicate PHP class assignmment in Theme', 'wdpln'), $class_short, $class_set), 500);
			}

			wdpln_theme()->{$class_set}->{$class_short} = new $class();

			if (method_exists(wdpln_theme()->{$class_set}->{$class_short}, 'run')) {
				wdpln_theme()->{$class_set}->{$class_short}->run();
			}
		}
	}

	/**
	 * Allow the Theme to use additional core features
	 */
	public function themeSupports()
	{
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */

		// DISABLED, this is now handled by Tibmer in the html-header.twig partial
		// add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'wdpln_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );

		/**
		 * Set up the WordPress core custom header feature.
		 */
		add_theme_support( 'custom-header', apply_filters( 'wdpln_custom_header_args', array(
			'default-image'          => '',
			'default-text-color'     => '000000',
			'width'                  => 1000,
			'height'                 => 250,
			'flex-height'            => true,
			'wp-head-callback'       => $this->headerStyle(),
		) ) );
	}

	// Add custom functions to Twig
	public function add_to_twig( $twig ) {
		$twig->addFunction(new \Timber\Twig_Function('count_entries', [$this, 'count_entries']));		
		return $twig;
	}

	// Timber custom function to count posts in a collection based on tag name
	public static function count_entries($term_slug, $taxonomy) {
		$term = get_term_by('name', $term_slug, $taxonomy);
		if ($term) {
			return $term->count;
		}
	}

	/**
	 * Set the content width in pixels, based on the theme's design and stylesheet.
	 *
	 * Priority 0 to make it available to lower priority callbacks.
	 *
	 * @global int $content_width
	 */
	public function contentWidth() {
		$GLOBALS['content_width'] = apply_filters( 'wdpln_content_width', 640 );
	}

	/**
	 * Styles the header image and text displayed on the blog.
	 *
	 * @see wdpln_custom_header_setup().
	 */
	public function headerStyle() {
		$header_text_color = get_header_textcolor();

		/*
		 * If no custom options for text are set, let's bail.
		 * get_header_textcolor() options: Any hex value, 'blank' to hide text. Default: add_theme_support( 'custom-header' ).
		 */
		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		// If we get this far, we have custom styles. Let's do this.
		?>
		<style type="text/css">
		<?php
		// Has the text been hidden?
		if ( ! display_header_text() ) :
		?>
			.site-title,
			.site-description {
				position: absolute;
				clip: rect(1px, 1px, 1px, 1px);
			}
		<?php
			// If the user has set a custom color for the text use that.
			else :
		?>
			.site-title a,
			.site-description {
				color: #<?php echo esc_attr( $header_text_color ); ?>;
			}
		<?php endif; ?>
		</style>
		<?php
	}

	/**
	 * Add a pingback url auto-discovery header for singularly identifiable articles.
	 */
	public function pingbackHeader() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
		}
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 * @return array
	 */
	public function bodyClasses( $classes ) {
		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		return $classes;
	}

	/**
	* Add Sitemap to robots.txt
	**/
	public function addToRoboText($robotext) {
		$additions = "Sitemap: https://Woodplane.com/sitemap.xml";
		return $robotext . $additions;
	}

	/**
	 * Tells social networks that there will be open graph tags that describe the content
	**/

	public function opengraphDoctype($output) {
		return $output . '
		xmlns="https://www.w3.org/1999/xhtml"
		xmlns:og="https://ogp.me/ns#"
		xmlns:fb="http://www.facebook.com/2008/fbml"';
	}

	/**
	* Hide Admin Bar for all users except Admins
	**/
	public function removeAdminBar() {
		if (!current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
		}
	}

	/**
	* Add target blank and nofollow to external links
	**/
	public function externalLinks($content) {
		$content = preg_replace_callback(
			'/<a[^>]*href=["|\']([^"|\']*)["|\'][^>]*>([^<]*)<\/a>/i',
			function($m) {
				$hasClass = (bool) preg_match('/class="[^"]*[^"]*"/', $m[0]);

				if (strpos($m[1], home_url()) === false && $hasClass === false)
					return '<a href="'.$m[1].'" rel="nofollow" target="_blank">'.$m[2].'</a>';
				else
					return $m[0];
			},
			$content
		);

		return $content;
	}

	// hide header when returning visitor.
	public function isFirstTime() {
		if (isset($_COOKIE['_wp_first_time']) || is_user_logged_in()) {
			return false;
		} else {
			// expires in 30 days.
			setcookie('_wp_first_time', 1, time() + (WEEK_IN_SECONDS * 4), COOKIEPATH, COOKIE_DOMAIN, false);

			return true;
		}
	}

	/**
	* Include Custom Post Types on Tag-Pages
	**/
	public function postTypeTagFix($request) {
		if ( isset($request['tag']) && !isset($request['post_type']) ) {
			$request['post_type'] = 'any';
		}
		return $request;
	}

	// style that god damn comments and post navigation links
	public function postLinkAttributes() {
		return 'class="btn btn-rounded btn-outline margin--big"';
	}

	// rename title on homepage
	public function renameTitle( $title ) {
		if ( !is_singular()) {
			$title = get_bloginfo('title');
			return $title;
		}
	}
	// get term count in twig
	public function getTermCountinTwig($term_slug, $taxonomy) {
		$term = get_term_by('name', $term_slug, $taxonomy);
		return $term;
	}
}