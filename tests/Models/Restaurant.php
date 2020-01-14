<?php

namespace Rennokki\ElasticScout\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\ElasticScout\Contracts\HasElasticScoutIndex;
use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Searchable;
use Rennokki\ElasticScout\Tests\Indexes\RestaurantIndex;

class Restaurant extends Model implements HasElasticScoutIndex
{
    use Searchable;

    protected $fillable = [
        'name', 'location',
    ];

    protected $casts = [
        'location' => 'array',
    ];

    public function getElasticScoutIndex(): Index
    {
        return new RestaurantIndex($this);
    }
}
