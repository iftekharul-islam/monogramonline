<?php

namespace Ptondereau\LaravelUpsApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * This is the Locator facade class.
 *
 * @author Pierre Tondereau <pierre.tondereau@gmail.com>
 */
class UpsLocator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ups.locator';
    }
}
