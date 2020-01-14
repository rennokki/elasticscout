<?php

namespace Rennokki\ElasticScout\Tests\Payloads;

use Rennokki\ElasticScout\Payloads\DocumentPayload;
use Rennokki\ElasticScout\Tests\AbstractTestCase;
use Rennokki\ElasticScout\Tests\Dependencies\Model;

class DocumentPayloadTest extends AbstractTestCase
{
    use Model;

    public function testDefault()
    {
        $model = $this->mockModel();

        $payload = new DocumentPayload($model);

        $this->assertEquals(
            [
                'index' => 'test',
                'type' => 'test',
                'id' => 1,
            ],
            $payload->get()
        );
    }

    public function testSet()
    {
        $index = $this->mockIndex([
            'name' => 'foo',
        ]);

        $model = $this->mockModel([
            'searchable_as' => 'bar',
            'index' => $index,
        ]);

        $payload = (new DocumentPayload($model))
            ->set('index', 'test_index')
            ->set('type', 'test_type')
            ->set('id', 2)
            ->set('body', []);

        $this->assertEquals(
            [
                'index' => 'foo',
                'type' => 'bar',
                'id' => 1,
                'body' => [],
            ],
            $payload->get()
        );
    }
}
