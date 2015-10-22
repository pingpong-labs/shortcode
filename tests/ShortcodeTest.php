<?php
namespace Pingpong\Shortcode\Tests;

use Pingpong\Shortcode\Shortcode;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class ShortcodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTexts
     */
    public function testParse($text, $expect)
    {
        $this->assertSame($expect, $this->getShortcode()->parse($text));
    }

    public function provideTexts()
    {
        return [
            ['[name]', 'name'],
            ['[content]', ''],
            ['[content]thunder[/content]', 'thunder'],
            ['[content][name][/content]', 'name'],
            ['[nc][name][/nc]', 'nc: name'],
        ];
    }

    public function testCount()
    {
        $this->assertSame(3, $this->getShortcode()->count());
    }

    public function testAll()
    {
        $this->assertSame(['name', 'content', 'nc'], $this->getShortcode()->all());
    }

    public function testUnregister()
    {
        $this->assertSame('[name]', $this->getShortcode()->unregister('name')->parse('[name]'));
    }

    public function testDestroy()
    {
        $this->assertSame('[name]', $this->getShortcode()->destroy()->parse('[name]'));
    }

    public function testStrip()
    {
        $this->assertSame('', $this->getShortcode()->strip('[name]'));
        $this->assertSame('x y', $this->getShortcode()->strip('x [name]y'));
        $this->assertSame('x  a  a  y', $this->getShortcode()->strip('x [name] a [content /] a [/name] y'));
    }

    public function testExists()
    {
        $shortcode = $this->getShortcode();

        $this->assertTrue($shortcode->exists('name'));
        $this->assertTrue($shortcode->exists('content'));
        $this->assertTrue($shortcode->exists('nc'));
        $this->assertFalse($shortcode->exists('invalid'));
    }

    public function testContains()
    {
        $shortcode = $this->getShortcode();

        $this->assertTrue($shortcode->contains('[name]', 'name'));
        $this->assertFalse($shortcode->contains('[x]', 'name'));
    }

    private function getShortcode()
    {
        $shortcode = new Shortcode();

        $shortcode->register('name', function(ShortcodeInterface $s) {
            return $s->getName();
        });
        $shortcode->register('content', function(ShortcodeInterface $s) {
            return $s->getContent();
        });
        $shortcode->register('nc', function(ShortcodeInterface $s) {
            return $s->getName().': '.$s->getContent();
        });

        return $shortcode;
    }
}
