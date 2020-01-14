<?php

namespace Rennokki\ElasticScout\Facades;

use Illuminate\Support\Facades\Facade;

class ElasticClient extends Facade
{
    /**
     * Get the facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticscout.client';
    }
}
