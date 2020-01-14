<?php

namespace Rennokki\ElasticScout\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\ElasticScout\Contracts\HasElasticScoutIndex;
use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Searchable;
use Rennokki\ElasticScout\Tests\Indexes\PostIndex;

class Post extends Model implements HasElasticScoutIndex
{
    use Searchable;

    protected $fillable = [
        'title', 'body',
    ];

    public function getElasticScoutIndex(): Index
    {
        return new PostIndex($this);
    }
}
