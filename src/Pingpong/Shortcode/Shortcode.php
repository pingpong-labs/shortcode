<?php namespace Pingpong\Shortcode;

use App;
use Closure;
use Countable;
use Illuminate\Support\Str;

class Shortcode implements Countable
{
	/**
	 * @var array 
	 */
	protected $shortcodes = array();

	/**
	 * Get all shortcodes.
	 *
	 * @return void
	 */
	public function all()
	{
		return $this->shortcodes;
	}

	/**
	 * Register new shortcode.
	 *
	 * @param  string  $name
	 * @param  mixed   $callback
	 * @return void
	 */
	public function register($name, $callback)
	{
		$this->shortcodes[$name] = $callback;
	}

	/**
	 * Unregister the specified shortcode by given name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function unregister($name)
	{
		if($this->exists($name))
		{
			unset($this->shortcodes[$name]);
		}
	}	

	/**
	 * Unregister all shortcodes.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function destroy()
	{
		$this->shortcodes = array();
	}	

	/**
	 * Get regex.
	 * 
	 * @copyright Wordpress
	 * @return string
	 */
	protected function getRegex()
	{
		$names = array_keys($this->shortcodes);
		$shortcode = join('|', array_map('preg_quote', $names));
		return
			  '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($shortcode)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			.     '(?:'
			.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
			.     ')*?'
			. ')'
			. '(?:'
			.     '(\\/)'                        // 4: Self closing tag ...
			.     '\\]'                          // ... and closing bracket
			. '|'
			.     '\\]'                          // Closing bracket
			.     '(?:'
			.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.             '[^\\[]*+'             // Not an opening bracket
			.             '(?:'
			.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			.                 '[^\\[]*+'         // Not an opening bracket
			.             ')*+'
			.         ')'
			.         '\\[\\/\\2\\]'             // Closing shortcode tag
			.     ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}

	/**
	 * Parse string to attributes array.
	 *
	 * @return array
	 */
	protected function parseAttr($text)
	{
		$atts = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				elseif (isset($m[7]) and strlen($m[7]))
					$atts[] = stripcslashes($m[7]);
				elseif (isset($m[8]))
					$atts[] = stripcslashes($m[8]);
			}
		} else {
			$atts = ltrim($text);
		}
		return $atts;
	}

	/**
	 * Get count from all shortcodes.
	 *
	 * @return integer
	 */
	public function count()
	{
		return count($this->shortcodes);
	}

	/**
	 * Return true is the given name exist in shortcodes array. 
	 *
	 * @param  string  $name
	 * @return boolean
	 */
	public function exists($name)
	{
		return array_key_exists($name, $this->shortcodes);
	}

	/**
	 * Return true is the given content contain the given name shortcode. 
	 *
	 * @param  string  $content
	 * @param  string  $name
	 * @return boolean
	 */
	public function contains($content, $name)
	{
		if ( $this->exists( $name ) ) {
			preg_match_all( '/' . $this->getRegex() . '/s', $content, $matches, PREG_SET_ORDER );
			if ( empty( $matches ) )
				return false;

			foreach ( $matches as $shortcode ) {
				if ( $name === $shortcode[2] )
					return true;
			}
		}
		return false;
	}

	/**
	 * Compile the gived content. 
	 *
	 * @param  string  $content
	 * @return void
	 */
	public function compile($content)
	{		
		if( ! $this->count())
		{
			return $content;
		}
		$pattern = $this->getRegex();

		return preg_replace_callback("/$pattern/s", [&$this,'render'], $content);
	}

	/**
	 * Render the current calld shortcode.
	 *
	 * @param  array  $matches
	 * @return void
	 */
	public function render($matches)
	{
		$name 		= array_get($matches, 2);
		$content 	= array_get($matches, 5);
		$callback 	= $this->getCallback($name);
		$params		= $this->getParameter($matches);
		$params 	= [$params, $content, $name];	
		return call_user_func_array($callback, $params);
	}

	/**
	 * Get parameters. 
	 *
	 * @param  array $matches
	 * @return array
	 */
	protected function getParameter($matches)
	{
		$params = $this->parseAttr($matches[3]);
		if( ! is_array($params))
		{
			$params = [$params];
		}
		return $params;
	}

	/**
	 * Get callback from given shortcode name.
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	protected function getCallback($name)
	{
		$callback = $this->shortcodes[$name];
		if(is_string($callback))
		{					
			if(str_contains($name, '@'))
			{
				$_callback = Str::parseCallback($callback, 'register');
				return [new $_callback[0], $_callback[1]];
			}
			elseif(class_exists($name))
			{
				return [new $name, 'register'];
			}else
			{
				return $callback;
			}
		}
		return $callback;
	}

}