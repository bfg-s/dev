<?php

namespace Bfg\Dev;

use Illuminate\Support\Facades\Facade as FacadeIlluminate;

/**
 * Class Facade
 * @package Bfg\Dev
 */
class Facade extends FacadeIlluminate
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Dev::class;
    }
}
