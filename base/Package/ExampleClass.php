<?php

namespace Woodplane\Theme\Package;

/**
 * Example Class
 * To include this class add the ExampleClass in the Theme.php's loadClass
 *
 * $load = [
 *	 Package\ExampleClass::class,
 * ];
 */
class Example
{
	public $variable = '';

	public function __construct() {
		// use the __construct function if you have to do stuff
		// while loading this class
		// we use the run specific run function because the __construct funciton
		// can be called before wordpress is fully loaded
		$this->variable = ['a', 'b', 'c'];
	}

	public function run()
	{
		// add the printExample function to the init hook
		// we do this in the run function because this function gets called while loading this class
		// add_action('init', [$this, 'printExample']);
	}

	// you could also call this function from outside this class like:
	// sht_theme()->Package->Example->printExample();
	public function printExample() {
		print($this->variable); // ['a', 'b', 'c']
	}
}
