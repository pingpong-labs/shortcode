<?php

use Pingpong\Shortcode\Shortcode;

// test class
class TestShortcode
{
	public function register($attr, $content = null, $name = null)
	{
		return '<b>'.$content.'</b>';
	}	
}

class ShortcodesTests extends PHPUnit_Framework_TestCase
{
	/**
	 * Set up the tests bench
	 *
	 * @return void
	 */
    protected function setUp()
    {
    	$this->shortcode = new Shortcode;
    }

	/**
	 * Register new simple shortcode for test
	 *
	 * @return void
	 */
    public function registerLinkTagShortcode()
    {
		$this->shortcode->register("a", function($attr, $content = null, $name = null)
		{
			$href = isset($attr['href']) ? $attr['href'] : '#';
			return '<a href="'. $href .'">'.$content.'</a>';
		});
    }

	/**
	 * Test simple shortcode tag.
	 *
	 * @return void
	 */
	public function testRegisterShortcode()
	{
		// setup
		$this->registerLinkTagShortcode();

		// test variable
		$LinkTag = '<a href="#">Click Me!</a>';
		$TestTag = $this->shortcode->compile("[a]Click Me![/a]");
		// first asserting
		$this->assertEquals($LinkTag, $TestTag);

		// the second test data
		$LinkWithUrlTag = '<a href="www.google.com">Go To Google</a>';
		$TestUrlTag = $this->shortcode->compile('[a href="www.google.com"]Go To Google[/a]');
		// second asserting
		$this->assertEquals($LinkWithUrlTag, $TestUrlTag);
	}

	/**
	 * Test simple shortcode tag using array callback.
	 *
	 * @return void
	 */
	public function testRegisterAndCompileUsingClassName()
	{
		$this->shortcode->register('b', ['TestShortcode', 'register']);

		$boldTag = '<b>Hello</b>';
		$boldTagFromShortcode = $this->shortcode->compile('[b]Hello[/b]');

		$this->assertEquals($boldTag, $boldTagFromShortcode);
	}
}