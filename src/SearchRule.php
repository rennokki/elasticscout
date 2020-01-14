<?php

namespace Rennokki\ElasticScout;

use Rennokki\ElasticScout\Builders\SearchQueryBuilder;

class SearchRule
{
    /**
     * Check if this is applicable.
     *
     * @return bool
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * Build the highlight payload.
     *
     * @param  \Rennokki\ElasticScout\Builders\SearchQueryBuilder  $builder
     * @return array|null
     */
    public function buildHighlightPayload(SearchQueryBuilder $builder)
    {
        //
    }

    /**
     * Build the query payload.
     *
     * @param  \Rennokki\ElasticScout\Builders\SearchQueryBuilder  $builder
     * @return array
     */
    public function buildQueryPayload(SearchQueryBuilder $builder)
    {
        return [
            'must' => [
                'query_string' => [
                    'query' => $builder->query,
                ],
            ],
        ];
    }
}
