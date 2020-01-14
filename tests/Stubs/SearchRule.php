<?php

namespace Rennokki\ElasticScout\Tests\Stubs;

use Rennokki\ElasticScout\Builders\SearchQueryBuilder;
use Rennokki\ElasticScout\SearchRule as ElasticSearchRule;

class SearchRule extends ElasticSearchRule
{
    protected $variable;

    /**
     * {@inheritdoc}
     */
    public function __construct($variable)
    {
        $this->variable = $variable;
    }

    /**
     * {@inheritdoc}
     */
    public function buildHighlightPayload(SearchQueryBuilder $builder)
    {
        $highlight = null;

        foreach ($builder->select as $field) {
            if (empty($highlight)) {
                $highlight = [
                    'fields' => [],
                ];
            }

            $highlight['fields'][$field] = [
                'type' => 'plain',
            ];
        }

        return $highlight;
    }
}
