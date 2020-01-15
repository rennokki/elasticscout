<?php

namespace Rennokki\ElasticScout\Contracts;

use Rennokki\ElasticScout\Index;

interface HasElasticScoutIndex
{
    /**
     * Get the index instance class for Elasticsearch.
     *
     * @return \Rennokki\ElasticScout\Index
     */
    public function getElasticScoutIndex(): Index;
}
