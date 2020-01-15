<?php

namespace Rennokki\ElasticScout\Tests\Indexes;

use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Migratable;

class RestaurantIndex extends Index
{
    use Migratable;

    /**
     * The settings applied to this index.
     *
     * @var array
     */
    protected $settings = [
        //
    ];

    /**
     * The mapping for this index.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'location' => [
                'type' => 'geo_point',
            ],
        ],
    ];
}