Laravel 4 - Simple Shortcode
=========

[![Build Status](https://travis-ci.org/pingpong-labs/shortcode.svg?branch=master)](https://travis-ci.org/pingpong-labs/shortcode)
[![Latest Stable Version](https://poser.pugx.org/pingpong/shortcode/v/stable.png)](https://packagist.org/packages/pingpong/shortcode) [![Total Downloads](https://poser.pugx.org/pingpong/shortcode/downloads.png)](https://packagist.org/packages/pingpong/shortcode) [![Latest Unstable Version](https://poser.pugx.org/pingpong/shortcode/v/unstable.png)](https://packagist.org/packages/pingpong/shortcode) [![License](https://poser.pugx.org/pingpong/shortcode/license.png)](https://packagist.org/packages/pingpong/shortcode)

### Server Requirements

- PHP 5.4.0 or higher.

### Installation
Open your composer.json file, and add the new required package.

```
  "pingpong/shortcode": "1.0.*" 
```

Next, open a terminal and run.

```
  composer update 
```

After the composer updated. Add new service provider in app/config/app.php.

```
  'Pingpong\Shortcode\ShortcodeServiceProvider'
```

Add new Facade alias.
```php
'Shortcode'       => 'Pingpong\Shortcode\Facades\Shortcode',
```

Done.

### Registering Shorcode

Using closure:
```php
Shortcode::register('a', function($attr, $content = null, $name = null)
{
	$text = Shortcode::compile($content);
	return '<a'.HTML::attributes($attr).'>'. $text .'</a>';
});
```

Using class name.
```php

class DivShortcode
{
  public function register($attr, $content = null, $name = null)
  {
  	$text = Shortcode::compile($content);
  	return '<div'.HTML::attributes($attr).'>'. $text .'</div>';
  }
}

Shortcode::register('div', 'DivShortcode');
```

Using class name with the specified method.
```php

class HTMLShortcode
{
  public function img($attr, $content = null, $name = null)
  {
    $src = array_get($attr, 'src');
  	$text = Shortcode::compile($content);
  	return '<img src="'.$src.'" '.HTML::attributes($attr).'/>';
  }
}


Shortcode::register('img', 'HTMLShortcode@img');
```

Using callback array.
```php

class SpanShortcode
{
  
  public function div($attr, $content = null, $name = null)
  {
  	$text = Shortcode::compile($content);
  	return '<span'.HTML::attributes($attr).'>'. $text .'</span>';
  }
}

Shortcode::register('span', array('SpanShortcode', 'span'));
```

Using function name.
```php
function smallTag($attr, $content = null, $name = null)
{
	$text = Shortcode::compile($content);
	return '<small'.HTML::attributes($attr).'>'. $text .'</small>';
}

Shortcode::register('small', 'smallTag');
```

### Compile

```php
$text = '[a href="#"]Click here[/a]';
echo Shortcode::compile($text);

$text = '
[a href="#"]
 [img src="http://placehold.it/140x140"]
 [small]This is small text[/small]
[/a]
';
echo Shortcode::compile($text);
```

### Unregister The Specified Shortcode

```php
Shortcode::unregister('img');
```

### Destroy All Shortcodes

```php
Shortcode::destroy();
```

### License

This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
