<?php

namespace Rennokki\ElasticScout\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\ElasticScout\Contracts\HasElasticScoutIndex;
use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Searchable;
use Rennokki\ElasticScout\Tests\Indexes\BookIndex;

class Book extends Model implements HasElasticScoutIndex
{
    use Searchable;

    protected $fillable = [
        'name', 'price',
    ];

    protected $casts = [
        'price' => 'int',
    ];

    public function getElasticScoutIndex(): Index
    {
        return new BookIndex($this);
    }
}
