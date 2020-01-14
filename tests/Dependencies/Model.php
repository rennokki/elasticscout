<?php

namespace Rennokki\ElasticScout\Tests\Dependencies;

use Rennokki\ElasticScout\Searchable;
use Rennokki\ElasticScout\Tests\Stubs\Model as StubModel;

trait Model
{
    use Index;

    /**
     * @param  array  $params  Available parameters: key, searchable_as, searchable_array, index, methods.
     * @return Searchable
     */
    public function mockModel(array $params = [])
    {
        $methods = array_merge(
            $params['methods'] ?? [],
            [
                'getKey',
                'getScoutKey',
                'trashed',
                'searchableAs',
                'toSearchableArray',
                'getIndex',
            ]
        );

        $mock = $this
            ->getMockBuilder(StubModel::class)
            ->setMethods($methods)
            ->getMock();

        $mock
            ->method('getKey')
            ->willReturn($params['key'] ?? 1);

        $mock
            ->method('getScoutKey')
            ->willReturn($params['key'] ?? 1);

        $mock
            ->method('trashed')
            ->willReturn($params['trashed'] ?? false);

        $mock
            ->method('searchableAs')
            ->willReturn($params['searchable_as'] ?? 'test');

        $mock
            ->method('toSearchableArray')
            ->willReturn($params['searchable_array'] ?? []);

        $mock
            ->method('getIndex')
            ->willReturn($params['index'] ?? $this->mockIndex());

        return $mock;
    }
}
