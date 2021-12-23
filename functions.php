<?php

// Disable all autoupdates on core, themes and plugins
define( 'AUTOMATIC_UPDATER_DISABLED', true );

// Disallow file edit
if (!defined('DISALLOW_FILE_EDIT')) {
	define('DISALLOW_FILE_EDIT', true);
}

if (!function_exists('dump')) {
	function dump($var, $exit = false)
	{
		echo '<pre>' . print_r($var, true) . '</pre>';
		if ($exit) {
			exit;
		}
	}
}

// Require and initialize Timber - Since the theme cannot work without it, we instantiate this early and before other classes
require_once( __DIR__ . '/vendor/autoload.php' );
$timber = new Timber\Timber();

/*
  The function below auto-loads a class or trait. No need to 
  use require, include or anything to get the class/trait files, as long
  as they are stored in the correct folder and use the correct namespaces.
  But most of Class handling is done in the Theme.php file.
*/
spl_autoload_register(function ($class) {

	// project-specific namespace prefix
	$prefix = 'Woodplane\\Theme\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__ . '/base/';

	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
});

// Returns the Theme Instance
if (!function_exists('wdpln_theme')) {
	function wdpln_theme()
	{		
		return Woodplane\Theme\Theme::getInstance();
	}
}

// Init
wdpln_theme();
wdpln_theme()->run();
