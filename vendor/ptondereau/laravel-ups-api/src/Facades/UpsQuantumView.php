<?php

namespace Ptondereau\LaravelUpsApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * This is the QuantumView facade class.
 *
 * @author Pierre Tondereau <pierre.tondereau@gmail.com>
 */
class UpsQuantumView extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ups.quantum-view';
    }
}
