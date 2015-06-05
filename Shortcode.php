<?php

namespace Pingpong\Shortcode;

use Countable;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class Shortcode implements Countable
{
    /**
     * All registered shortcodes.
     *
     * @var array
     */
    protected $shortcodes = [];

    /**
     * The laravel container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The constructor.
     *
     * @param \Illuminate\Container\Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: new Container();
    }

    /**
     * Get the container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the laravel container instance.
     *
     * @param \Illuminate\Container\Container $container
     *
     * @return self
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get all shortcodes.
     *
     * @return array
     */
    public function all()
    {
        return $this->shortcodes;
    }

    /**
     * Register new shortcode.
     *
     * @param string $name
     * @param mixed  $callback
     */
    public function register($name, $callback)
    {
        $this->shortcodes[$name] = $callback;
    }

    /**
     * Unregister the specified shortcode by given name.
     *
     * @param string $name
     */
    public function unregister($name)
    {
        if ($this->exists($name)) {
            unset($this->shortcodes[$name]);
        }
    }

    /**
     * Unregister all shortcodes.
     *
     * @return self
     */
    public function destroy()
    {
        $this->shortcodes = [];

        return $this;
    }

    /**
     * Get regex.
     *
     * @copyright Wordpress
     *
     * @return string
     */
    protected function getRegex()
    {
        $names = array_keys($this->shortcodes);

        $shortcode = implode('|', array_map('preg_quote', $names));

        return
            '\\['
            .'(\\[?)'
            ."($shortcode)"
            .'(?![\\w-])'
            .'('
            .'[^\\]\\/]*'
            .'(?:'
            .'\\/(?!\\])'
            .'[^\\]\\/]*'
            .')*?'
            .')'
            .'(?:'
            .'(\\/)'
            .'\\]'
            .'|'
            .'\\]'
            .'(?:'
            .'('
            .'[^\\[]*+'
            .'(?:'
            .'\\[(?!\\/\\2\\])'
            .'[^\\[]*+'
            .')*+'
            .')'
            .'\\[\\/\\2\\]'
            .')?'
            .')'
            .'(\\]?)';
    }

    /**
     * Parse string to attributes array.
     *
     * @return array
     */
    protected function parseAttr($text)
    {
        $atts = [];

        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';

        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);

        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) and strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                }
            }
        } else {
            $atts = ltrim($text);
        }

        return $atts;
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
        if (empty($this->shortcodes)) {
            return $content;
        }

        $pattern = $this->getRegex();

        return preg_replace_callback("/{$pattern}/s", function ($m) {
            if ($m[1] == '[' && $m[6] == ']') {
                return substr($m[0], 1, -1);
            }

            return $m[1].$m[6];
        }, $content);
    }

    /**
     * Get count from all shortcodes.
     *
     * @return int
     */
    public function count()
    {
        return count($this->shortcodes);
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
        return array_key_exists($name, $this->shortcodes);
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
        if ($this->exists($name)) {
            preg_match_all('/'.$this->getRegex().'/s', $content, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                return false;
            }

            foreach ($matches as $shortcode) {
                if ($name === $shortcode[2]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Compile the gived content.
     *
     * @param string $content
     */
    public function compile($content)
    {
        if (!$this->count()) {
            return $content;
        }

        $pattern = $this->getRegex();

        return preg_replace_callback("/{$pattern}/s", [&$this, 'render'], $content);
    }

    /**
     * Parse the given content.
     *
     * @param string $content
     */
    public function parse($content)
    {
        return $this->compile($content);
    }

    /**
     * Render the current calld shortcode.
     *
     * @param array $matches
     */
    public function render($matches)
    {
        $name = array_get($matches, 2);

        $content = array_get($matches, 5);

        $callback = $this->getCallback($name);

        $params = $this->getParameter($matches);

        $params = [$params, $content, $name];

        return call_user_func_array($callback, $params);
    }

    /**
     * Get parameters.
     *
     * @param array $matches
     *
     * @return array
     */
    protected function getParameter($matches)
    {
        $params = $this->parseAttr($matches[3]);

        if (!is_array($params)) {
            $params = [$params];
        }

        return $params;
    }

    /**
     * Get callback from given shortcode name.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getCallback($name)
    {
        $callback = $this->shortcodes[$name];

        if (is_string($callback)) {
            if (Str::contains($callback, '@')) {
                $parsedCallback = Str::parseCallback($callback, 'register');

                $instance = $this->container->make($parsedCallback[0]);

                return [$instance, $parsedCallback[1]];
            } elseif (class_exists($callback)) {
                $instance = $this->container->make($callback);

                return [$instance, 'register'];
            } else {
                return $callback;
            }
        }

        return $callback;
    }
}
