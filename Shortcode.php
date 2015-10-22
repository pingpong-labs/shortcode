<?php
namespace Pingpong\Shortcode;

use Countable;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class Shortcode implements Countable
{
    /** @var HandlerContainer */
    private $handlers;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->handlers = new HandlerContainer();
    }

    /**
     * Get all shortcodes.
     *
     * @return array
     */
    public function all()
    {
        return $this->handlers->getNames();
    }

    /**
     * Register new shortcode.
     *
     * @param string $name
     * @param mixed  $callback
     */
    public function register($name, $callback)
    {
        $this->handlers->add($name, $callback);
    }

    /**
     * Unregister the specified shortcode by given name.
     *
     * @param string $name
     */
    public function unregister($name)
    {
        if ($this->exists($name)) {
            $this->handlers->remove($name);
        }

        return $this;
    }

    /**
     * Unregister all shortcodes.
     *
     * @return self
     */
    public function destroy()
    {
        $this->handlers = new HandlerContainer();

        return $this;
    }

    /**
     * Strip any shortcodes.
     *
     * @param string $content
     *
     * @return string
     */
    public function strip($content)
    {
        $handlers = new HandlerContainer();
        $handlers->setDefault(function(ShortcodeInterface $s) { return $s->getContent(); });
        $processor = new Processor(new RegexParser(), $handlers);

        return $processor->process($content);
    }

    /**
     * Get count from all shortcodes.
     *
     * @return int
     */
    public function count()
    {
        return count($this->handlers->getNames());
    }

    /**
     * Return true is the given name exist in shortcodes array.
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return $this->handlers->has($name);
    }

    /**
     * Return true is the given content contain the given name shortcode.
     *
     * @param string $content
     * @param string $name
     *
     * @return bool
     */
    public function contains($content, $name)
    {
        $hasShortcode = false;

        $handlers = new HandlerContainer();
        $handlers->setDefault(function(ShortcodeInterface $s) use($name, &$hasShortcode) {
            if($s->getName() === $name) {
                $hasShortcode = true;
            }
        });
        $processor = new Processor(new RegexParser(), $handlers);
        $processor->process($content);

        return $hasShortcode;
    }

    /**
     * Parse content and replace parts of it using registered handlers
     *
     * @param $content
     *
     * @return string
     */
    public function parse($content)
    {
        $processor = new Processor(new RegexParser(), $this->handlers);

        return $processor->process($content);
    }
}
