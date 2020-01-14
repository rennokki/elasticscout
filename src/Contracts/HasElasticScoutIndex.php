<?php

namespace Rennokki\ElasticScout\Contracts;

use Rennokki\ElasticScout\Index;

interface HasElasticScoutIndex
{
    public function getElasticScoutIndex(): Index;
}
