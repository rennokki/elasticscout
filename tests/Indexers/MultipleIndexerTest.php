<?php

namespace Rennokki\ElasticScout\Tests\Indexers;

use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Indexers\MultipleIndexer;
use Rennokki\ElasticScout\Tests\Config;

class MultipleIndexerTest extends AbstractIndexerTest
{
    public function testUpdateWithDisabledSoftDelete()
    {
        Config::set('scout.soft_delete', false);

        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    ['index' => ['_id' => 1]],
                    ['name' => 'foo'],
                    ['index' => ['_id' => 2]],
                    ['name' => 'bar'],
                ],
            ]);

        (new MultipleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithEnabledSoftDelete()
    {
        Config::set('scout.soft_delete', true);

        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    ['index' => ['_id' => 1]],
                    ['name' => 'foo', '__soft_deleted' => 1],
                    ['index' => ['_id' => 2]],
                    ['name' => 'bar', '__soft_deleted' => 0],
                    ['index' => ['_id' => 3]],
                    ['__soft_deleted' => 0],
                ],
            ]);

        (new MultipleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithSpecifiedDocumentRefreshOption()
    {
        Config::set('elasticscout.refresh_document_on_save', 'true');

        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'refresh' => 'true',
                'body' => [
                    ['index' => ['_id' => 1]],
                    ['name' => 'foo'],
                    ['index' => ['_id' => 2]],
                    ['name' => 'bar'],
                ],
            ]);

        (new MultipleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testDelete()
    {
        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    ['delete' => ['_id' => 1]],
                    ['delete' => ['_id' => 2]],
                    ['delete' => ['_id' => 3]],
                ],
                'client' => [
                    'ignore' => 404,
                ],
            ]);

        (new MultipleIndexer())
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}
