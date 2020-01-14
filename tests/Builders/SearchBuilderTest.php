<?php

namespace Rennokki\ElasticScout\Tests\Builders;

use Rennokki\ElasticScout\Builders\SearchQueryBuilder;
use Rennokki\ElasticScout\SearchRule;
use Rennokki\ElasticScout\Tests\AbstractTestCase;
use Rennokki\ElasticScout\Tests\Dependencies\Model;

class SearchBuilderTest extends AbstractTestCase
{
    use Model;

    public function testRule()
    {
        $builder = new SearchQueryBuilder($this->mockModel(), 'qwerty');

        $ruleFunc = function (SearchQueryBuilder $builder) {
            return [
                'must' => [
                    'match' => [
                        'foo' => $builder->query,
                    ],
                ],
            ];
        };

        $builder->rule(new SearchRule('test'))->rule($ruleFunc);

        $this->assertEquals([
            new SearchRule,
            $ruleFunc,
        ], $builder->rules);
    }
}
