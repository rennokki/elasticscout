<?php

namespace Rennokki\ElasticScout\Tests\Stubs;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\ElasticScout\Searchable;

class Model extends BaseModel
{
    use Searchable, SoftDeletes;
}
