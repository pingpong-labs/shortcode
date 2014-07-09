<?php

use Pingpong\Shortcode\Shortcode;

class HTMLShortcode
{
    function div($attr, $content = null, $name = null)
    {
        return "<div>{$content}</div>";
    }

    function img($attr, $content = null, $name = null)
    {
        return '<img src="#">';
    }
}

// test function
function boldTagFunction($attr, $content = null, $name = null)
{
	return '<b>'.$content.'</b>';
}

class ShortcodesTests extends PHPUnit_Framework_TestCase
{
    protected $shortcode;

	/**
	 * Set up the tests bench
	 *
	 * @return void
	 */
    function setUp()
    {
    	$this->shortcode = new Shortcode;
    }

	/**
	 * Register new simple shortcode for test
	 *
	 * @return void
	 */
    protected function registerLinkTagShortcode()
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
	public function testRegisterAndCompileUsingFunctionName()
	{
		$this->shortcode->register('b', 'boldTagFunction');

		$boldTag = '<b>Hello</b>';
		$boldTagFromShortcode = $this->shortcode->compile('[b]Hello[/b]');

		$this->assertEquals($boldTag, $boldTagFromShortcode);
	}

	/**
	 * Test the strip functionality
	 *
	 * @return void
	 */
	public function testStrippingShortcodesFromContent()
	{
		$this->shortcode->register('b', 'boldTagFunction');

		$content = 'This is just a shortcode [b]example[/b].';
		$expected = 'This is just a shortcode .';

		$this->assertEquals($expected, $this->shortcode->strip($content));
	}

    function testRegisterShortcodeUsingClasses()
    {
        $shortcode = new Shortcode();

        $shortcode->register('div', 'HTMLShortcode@div');

        $shortcode->register('img', 'HTMLShortcode@img');

        $exceptedDiv = '<div>Hello</div>';
        $exceptedImg = '<img src="#">';

        $this->assertEquals($exceptedDiv, $shortcode->compile('[div]Hello[/div]'));

        $this->assertEquals($exceptedImg, $shortcode->compile('[img src="#"]'));
    }
}
