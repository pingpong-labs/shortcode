<?php namespace Pingpong\Shortcode;

use Illuminate\Support\Facades\Facade;

class ShortcodeFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shortcode';
    }
}
