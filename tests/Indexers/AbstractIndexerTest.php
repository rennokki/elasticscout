<?php

namespace Rennokki\ElasticScout\Tests\Indexers;

use Rennokki\ElasticScout\Tests\AbstractTestCase;
use Rennokki\ElasticScout\Tests\Dependencies\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractIndexerTest extends AbstractTestCase
{
    use Model;

    /**
     * @var Collection
     */
    protected $models;

    protected function setUp(): void
    {
        $this->models = new Collection([
            $this->mockModel([
                'key' => 1,
                'trashed' => true,
                'searchable_array' => [
                    'name' => 'foo',
                ],
            ]),
            $this->mockModel([
                'key' => 2,
                'trashed' => false,
                'searchable_array' => [
                    'name' => 'bar',
                ],
            ]),
            $this->mockModel([
                'key' => 3,
                'trashed' => false,
                'searchable_array' => [],
            ]),
        ]);
    }
}
