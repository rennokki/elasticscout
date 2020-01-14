<?php

namespace Rennokki\ElasticScout\Tests\Dependencies;

use Rennokki\ElasticScout\Index as ElasticIndex;

trait Index
{
    /**
     * @param array $params Available parameters: name, settings, mapping, methods.
     * @return ElasticIndex
     */
    public function mockIndex(array $params = [])
    {
        $name = $params['name'] ?? 'test';

        $methods = array_merge($params['methods'] ?? [], [
                'getName',
                'getSettings',
                'getMapping',
                'getWriteAlias',
            ]);

        $mock = $this->getMockBuilder(ElasticIndex::class)
                     ->setMethods($methods)->getMock();

        $mock->method('getName')
             ->willReturn($name);

        $mock->method('getSettings')
             ->willReturn($params['settings'] ?? []);

        $mock->method('getMapping')
             ->willReturn($params['mapping'] ?? []);

        $mock->method('getWriteAlias')
             ->willReturn($name.'_write');

        return $mock;
    }
}
