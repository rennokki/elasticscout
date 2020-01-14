<?php

namespace Rennokki\ElasticScout\Tests\Stubs;

use Rennokki\ElasticScout\Searchable;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends BaseModel
{
    use Searchable, SoftDeletes;
}
